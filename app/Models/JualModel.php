<?php

namespace App\Models;

use CodeIgniter\Model;

class JualModel extends Model
{
    protected $table = 'penjualan';
    protected $returnType = 'array';
    protected $protectFields = false;

    public function getInitialData(string $toko_id): array
    {
        $toko = $this->db->query(
            "SELECT toko_id, toko_nama, toko_theme
             FROM toko
             WHERE toko_id=:toko_id:
             LIMIT 1",
            ['toko_id' => $toko_id]
        )->getRowArray() ?: [];

        return [
            'toko' => $toko,
            'nominal_per_poin' => $this->getNominalPerPoin(),
            'running_text' => $this->getActiveRunningText(),
            'customer_general' => [
                'cust_id' => 'CUST-GENERAL',
                'nama' => 'Pelanggan Umum',
                'poin' => 0,
                'outstanding_piutang' => 0,
            ],
        ];
    }

    public function getNextId(string $toko_id): string
    {
        $prefix = 'JL' . $toko_id . date('ymd');
        $row = $this->db->query(
            "SELECT MAX(CAST(RIGHT(jual_id,4) AS UNSIGNED)) AS nomor
             FROM penjualan
             WHERE toko_id=:toko_id: AND jual_id LIKE :prefix_like:",
            [
                'toko_id' => $toko_id,
                'prefix_like' => $prefix . '%',
            ]
        )->getRowArray();

        return $prefix . sprintf('%04d', ((int) ($row['nomor'] ?? 0)) + 1);
    }

    public function getNominalPerPoin(): int
    {
        $row = $this->db->query(
            "SELECT nilai FROM const WHERE rkey='nominal_per_poin' LIMIT 1"
        )->getRowArray();

        return max(1, (int) ($row['nilai'] ?? 1000));
    }

    public function getActiveRunningText(): array
    {
        return $this->db->query(
            "SELECT running_text_id, judul, isi_pengumuman
             FROM pos_running_text
             WHERE is_active='Y'
             ORDER BY urutan ASC, running_text_id DESC"
        )->getResultArray();
    }

    public function searchItems(string $toko_id, string $term): array
    {
        $search = '%' . $this->db->escapeLikeString($term) . '%';

        return $this->db->query(
            "SELECT p.kode_item, p.barcode, p.nama_item, p.supco,
                    COALESCE(base.sat_id, '-') AS sat_dasar,
                    COALESCE(base.qty_konversi, 1) AS qty_dasar,
                    COALESCE(store.harga_pokok, 0) AS harga_default,
                    COALESCE(store.harga_jual, 0) AS harga_jual,
                    CASE
                        WHEN COALESCE(base.qty_konversi, 0) <= 0 THEN 0
                        ELSE COALESCE(st.qty, 0) / base.qty_konversi
                    END AS stok
             FROM prodmast p
             LEFT JOIN (
                SELECT ps1.kode_item, ps1.sat_id, ps1.qty_konversi
                FROM prodmast_satuan ps1
                INNER JOIN (
                    SELECT kode_item, MIN(qty_konversi) AS min_qty
                    FROM prodmast_satuan
                    GROUP BY kode_item
                ) x ON x.kode_item=ps1.kode_item AND x.min_qty=ps1.qty_konversi
             ) base ON base.kode_item=p.kode_item
             LEFT JOIN prodmast_store store
                ON store.kode_item=p.kode_item
                AND store.toko_id=:toko_id:
                AND store.sat_id=base.sat_id
             LEFT JOIN stmast st
                ON st.toko_id=store.toko_id
                AND st.kode_item=p.kode_item
             WHERE store.status_item='Y' AND (
                    p.kode_item LIKE :search:
                    OR p.barcode LIKE :search:
                    OR p.nama_item LIKE :search:
                )
             ORDER BY
                CASE
                    WHEN p.barcode = :exact_term: THEN 0
                    WHEN p.kode_item = :exact_term: THEN 1
                    ELSE 2
                END,
                p.nama_item
             LIMIT 30",
            [
                'toko_id' => $toko_id,
                'search' => $search,
                'exact_term' => trim($term),
            ]
        )->getResultArray();
    }

    public function getItemPayload(string $toko_id, string $kode_item): ?array
    {
        $item = $this->db->query(
            "SELECT p.kode_item, p.barcode, p.nama_item, p.supco,
                    COALESCE(st.qty, 0) AS stok_base
             FROM prodmast p
             LEFT JOIN stmast st ON st.toko_id=:toko_id: AND st.kode_item=p.kode_item
             WHERE p.kode_item=:kode_item:
             LIMIT 1",
            [
                'toko_id' => $toko_id,
                'kode_item' => $kode_item,
            ]
        )->getRowArray();

        if (! $item) {
            return null;
        }

        $options = $this->db->query(
            "SELECT ps.sat_id, ps.qty_konversi,
                    COALESCE(store.harga_pokok, 0) AS harga_pokok,
                    COALESCE(store.harga_jual, 0) AS harga_jual,
                    CASE
                        WHEN COALESCE(ps.qty_konversi, 0) <= 0 THEN 0
                        ELSE COALESCE(st.qty, 0) / ps.qty_konversi
                    END AS stok_maksimal
             FROM prodmast_satuan ps
             INNER JOIN prodmast_store store
                ON store.kode_item=ps.kode_item
                AND store.sat_id=ps.sat_id
                AND store.toko_id=:toko_id:
                AND store.status_item='Y'
             LEFT JOIN stmast st
                ON st.toko_id=store.toko_id
                AND st.kode_item=ps.kode_item
             WHERE ps.kode_item=:kode_item:
             ORDER BY ps.qty_konversi, ps.sat_id",
            [
                'toko_id' => $toko_id,
                'kode_item' => $kode_item,
            ]
        )->getResultArray();

        if (empty($options)) {
            return null;
        }

        foreach ($options as &$option) {
            $option['price_error'] = $this->getPriceValidationMessage(
                (float) ($option['harga_pokok'] ?? 0),
                (float) ($option['harga_jual'] ?? 0)
            );
        }
        unset($option);

        $defaultOption = null;
        foreach ($options as $option) {
            if (($option['price_error'] ?? '') === '') {
                $defaultOption = $option;
                break;
            }
        }
        if (! $defaultOption) {
            $defaultOption = $options[0];
        }

        $item['satuan_options'] = $options;
        $item['default_sat_id'] = $defaultOption['sat_id'];
        $item['price_error'] = $defaultOption['price_error'] ?? '';
        return $item;
    }

    public function searchCustomers(string $term): array
    {
        $search = '%' . $this->db->escapeLikeString($term) . '%';

        return $this->db->query(
            "SELECT c.cust_id, c.nama, c.kontak, c.poin,
                    COALESCE(pi.total_piutang, 0) AS outstanding_piutang
             FROM customer c
             LEFT JOIN (
                SELECT cust_id, SUM(sisa_piutang) AS total_piutang
                FROM penjualan
                WHERE cust_id <> 'CUST-GENERAL' AND status_bayar IN ('BELUM','CICIL')
                GROUP BY cust_id
             ) pi ON pi.cust_id=c.cust_id
             WHERE c.cust_id LIKE :search:
                OR c.nama LIKE :search:
                OR c.kontak LIKE :search:
             ORDER BY c.nama, c.cust_id
             LIMIT 30",
            ['search' => $search]
        )->getResultArray();
    }

    public function getCustomerPayload(string $cust_id): ?array
    {
        if ($cust_id === 'CUST-GENERAL') {
            return [
                'cust_id' => 'CUST-GENERAL',
                'nama' => 'Pelanggan Umum',
                'kontak' => '',
                'poin' => 0,
                'outstanding_piutang' => 0,
            ];
        }

        return $this->db->query(
            "SELECT c.cust_id, c.nama, c.kontak, c.poin,
                    COALESCE(pi.total_piutang, 0) AS outstanding_piutang
             FROM customer c
             LEFT JOIN (
                SELECT cust_id, SUM(sisa_piutang) AS total_piutang
                FROM penjualan
                WHERE cust_id <> 'CUST-GENERAL' AND status_bayar IN ('BELUM','CICIL')
                GROUP BY cust_id
             ) pi ON pi.cust_id=c.cust_id
             WHERE c.cust_id=:cust_id:
             LIMIT 1",
            ['cust_id' => $cust_id]
        )->getRowArray() ?: null;
    }

    public function registerQuickMember(string $username, string $nama, string $kontak): array
    {
        $nama = trim($nama);
        $kontak = trim($kontak);
        if ($nama === '' || $kontak === '') {
            return ['tipe' => 'error', 'data' => 'Nama dan nomor HP wajib diisi'];
        }

        $customerModel = new CustomerModel();
        $custId = $customerModel->getLastId();
        $ok = $customerModel->insert([
            'cust_id' => $custId,
            'nama' => $nama,
            'alamat' => '-',
            'kontak' => $kontak,
            'tgl_daftar' => date('Y-m-d'),
            'max_faktur' => 3,
            'poin' => 0,
            'updid' => $username,
        ], false);

        if (! $ok) {
            return ['tipe' => 'error', 'data' => 'Gagal mendaftarkan member baru'];
        }

        $member = $this->getCustomerPayload($custId);
        return ['tipe' => 'success', 'data' => $member];
    }

    public function logVoidCart(string $toko_id, string $username, array $payload, string $reason = 'Reset keranjang dari POS'): void
    {
        $summary = $this->buildVoidCartSummary($payload);

        $this->db->table('penjualan_void')->insert([
            'toko_id' => $toko_id,
            'username' => $username,
            'payload_json' => json_encode($payload, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES),
            'alasan' => $reason,
            'jumlah_item' => $summary['jumlah_item'],
            'jumlah_qty' => $summary['jumlah_qty'],
            'total_gross' => $summary['total_gross'],
            'created_at' => date('Y-m-d H:i:s'),
        ]);
    }

    private function buildVoidCartSummary(array $payload): array
    {
        $rows = is_array($payload['cartRows'] ?? null) ? $payload['cartRows'] : [];
        $jumlahItem = count($rows);
        $jumlahQty = 0.0;
        $totalGross = 0.0;

        foreach ($rows as $row) {
            $qtyJual = round((float) ($row['qty_jual'] ?? 0), 4);
            $gross = round((float) ($row['gross'] ?? 0), 2);

            if ($gross <= 0) {
                $gross = round($qtyJual * (float) ($row['price'] ?? 0), 2);
            }

            $jumlahQty += $qtyJual;
            $totalGross += $gross;
        }

        return [
            'jumlah_item' => $jumlahItem,
            'jumlah_qty' => round($jumlahQty, 4),
            'total_gross' => round($totalGross, 2),
        ];
    }

    public function ajaxList(array $params, string $toko_id): array
    {
        $start = (int) ($params['start'] ?? 0);
        $length = $params['length'] ?? 25;
        $search = trim((string) ($params['search_value'] ?? ''));
        $startDate = $this->normalizeDateFilter($params['start_date'] ?? '') ?: date('Y-m-d');
        $endDate = $this->normalizeDateFilter($params['end_date'] ?? '') ?: $startDate;
        if ($startDate > $endDate) {
            [$startDate, $endDate] = [$endDate, $startDate];
        }

        $queryLimit = $length !== '-1' ? " LIMIT $start, " . (int) $length : '';
        $binds = [
            'toko_id' => $toko_id,
            'start_date' => $startDate . ' 00:00:00',
            'end_date' => $endDate . ' 23:59:59',
        ];
        $where = " WHERE j.toko_id=:toko_id: AND j.tgl BETWEEN :start_date: AND :end_date: ";
        if ($search !== '') {
            $where .= " AND (
                j.jual_id LIKE :search:
                OR j.cust_id LIKE :search:
                OR COALESCE(c.nama, '') LIKE :search:
                OR COALESCE(j.updid, '') LIKE :search:
            )";
            $binds['search'] = '%' . $this->db->escapeLikeString($search) . '%';
        }

        $countQuery = "
            SELECT COUNT(*) AS total
            FROM penjualan j
            LEFT JOIN customer c ON c.cust_id=j.cust_id
            $where
        ";
        $filteredRow = $this->db->query($countQuery, $binds)->getRowArray();
        $filtered = (int) ($filteredRow['total'] ?? 0);

        $data = $this->db->query(
            "SELECT j.*,
                    COALESCE(c.nama, 'Pelanggan Umum') AS customer_nama,
                    COUNT(d.seq_no) AS jml_item,
                    COALESCE(SUM(d.qty_jual), 0) AS total_qty
             FROM penjualan j
             LEFT JOIN customer c ON c.cust_id=j.cust_id
             LEFT JOIN penjualan_detail d ON d.toko_id=j.toko_id AND d.jual_id=j.jual_id
             $where
             GROUP BY j.toko_id, j.jual_id
             ORDER BY j.tgl DESC, j.jual_id DESC
             $queryLimit",
            $binds
        )->getResultArray();

        foreach ($data as &$row) {
            $row['can_edit'] = $this->canModifySaleDate($row['tgl'] ?? null);
        }
        unset($row);

        return [
            'data' => $data,
            'total_count' => $filtered,
            'total_filtered' => $filtered,
        ];
    }

    public function canModifySaleDate(?string $tgl): bool
    {
        return $tgl !== null && substr((string) $tgl, 0, 10) === date('Y-m-d');
    }

    public function getSaleEditPayload(string $toko_id, string $jual_id): ?array
    {
        $sale = $this->getSaleAggregate($toko_id, $jual_id);
        if (! $sale) {
            return null;
        }

        $customer = $this->getCustomerPayload((string) ($sale['cust_id'] ?? 'CUST-GENERAL')) ?? $this->getCustomerPayload('CUST-GENERAL');
        if (($sale['cust_id'] ?? 'CUST-GENERAL') !== 'CUST-GENERAL') {
            $customer['poin'] = (int) ($customer['poin'] ?? 0) + (int) ($sale['redeem_points'] ?? 0);
        }
        $cartRows = [];

        foreach ($sale['details'] as $detail) {
            $itemPayload = $this->getItemPayload($toko_id, (string) ($detail['kode_item'] ?? ''));
            $qtyStockExisting = round((float) ($detail['qty_stock'] ?? 0), 4);

            if ($itemPayload) {
                foreach ($itemPayload['satuan_options'] as &$option) {
                    $stokBase = round((float) ($itemPayload['stok_base'] ?? 0) + $qtyStockExisting, 4);
                    $qtyKonversi = (float) ($option['qty_konversi'] ?? 0);
                    $option['stok_maksimal'] = $qtyKonversi > 0 ? round($stokBase / $qtyKonversi, 4) : 0;
                }
                unset($option);
            }

            $selectedOption = null;
            if ($itemPayload) {
                foreach (($itemPayload['satuan_options'] ?? []) as $option) {
                    if (($option['sat_id'] ?? '') === ($detail['sat_id'] ?? '')) {
                        $selectedOption = $option;
                        break;
                    }
                }
            }

            if (! $itemPayload || ! $selectedOption) {
                $itemPayload = [
                    'kode_item' => $detail['kode_item'],
                    'barcode' => '',
                    'nama_item' => $detail['nama_item'] ?? $detail['kode_item'],
                    'satuan_options' => [[
                        'sat_id' => $detail['sat_id'],
                        'qty_konversi' => (float) ($detail['qty_konversi'] ?? 1),
                        'harga_pokok' => (float) ($detail['harga_pokok'] ?? 0),
                        'harga_jual' => (float) ($detail['price'] ?? 0),
                        'stok_maksimal' => (float) ($detail['qty_jual'] ?? 0),
                        'price_error' => $this->getPriceValidationMessage(
                            (float) ($detail['harga_pokok'] ?? 0),
                            (float) ($detail['price'] ?? 0)
                        ),
                    ]],
                ];
                $selectedOption = $itemPayload['satuan_options'][0];
            }

            $cartRows[] = [
                'kode_item' => $detail['kode_item'],
                'barcode' => $itemPayload['barcode'] ?? '',
                'nama_item' => $detail['nama_item'] ?? $itemPayload['nama_item'] ?? $detail['kode_item'],
                'sat_id' => $detail['sat_id'],
                'qty_jual' => (float) ($detail['qty_jual'] ?? 0),
                'qty_konversi' => (float) ($detail['qty_konversi'] ?? 1),
                'harga_pokok' => (float) ($detail['harga_pokok'] ?? 0),
                'price' => (float) ($detail['price'] ?? 0),
                'gross' => (float) ($detail['gross'] ?? 0),
                'diskon_item' => (float) ($detail['diskon_item'] ?? 0),
                'netto' => (float) ($detail['netto'] ?? 0),
                'qty_stock' => (float) ($detail['qty_stock'] ?? 0),
                'max_qty' => (float) ($selectedOption['stok_maksimal'] ?? $detail['qty_jual'] ?? 0),
                'satuan_options' => array_map(static function ($option): array {
                    return [
                        'sat_id' => $option['sat_id'],
                        'qty_konversi' => (float) ($option['qty_konversi'] ?? 1),
                        'harga_pokok' => (float) ($option['harga_pokok'] ?? 0),
                        'harga_jual' => (float) ($option['harga_jual'] ?? 0),
                        'stok_maksimal' => (float) ($option['stok_maksimal'] ?? 0),
                        'price_error' => $option['price_error'] ?? '',
                    ];
                }, $itemPayload['satuan_options'] ?? []),
            ];
        }

        return [
            'jual_id' => $sale['jual_id'],
            'tgl' => $sale['tgl'],
            'customer' => $customer,
            'diskon_nota' => (float) ($sale['diskon_nota'] ?? 0),
            'redeem_points' => (int) ($sale['redeem_points'] ?? 0),
            'cash_received' => (float) ($sale['cash_received'] ?? 0),
            'jatuh_tempo' => ! empty($sale['jatuh_tempo']) ? $sale['jatuh_tempo'] : date('Y-m-d', strtotime('+30 days')),
            'cart_rows' => $cartRows,
            'payment_rows' => array_map(static function ($row): array {
                return [
                    'cara_bayar' => strtoupper((string) ($row['cara_bayar'] ?? 'TUNAI')),
                    'nominal_bayar' => (float) ($row['nominal_bayar'] ?? 0),
                    'bank_nama' => (string) ($row['bank_nama'] ?? ''),
                    'rekening_no' => (string) ($row['rekening_no'] ?? ''),
                ];
            }, $sale['payments']),
        ];
    }

    public function saveSale(string $toko_id, string $username, array $input): array
    {
        return $this->persistSale($toko_id, $username, $input);
    }

    public function updateSale(string $toko_id, string $username, string $jual_id, array $input): array
    {
        $existingSale = $this->getSaleAggregate($toko_id, $jual_id);
        if (! $existingSale) {
            return ['tipe' => 'error', 'data' => 'Transaksi penjualan tidak ditemukan'];
        }
        if (! $this->canModifySaleDate($existingSale['tgl'] ?? null)) {
            return ['tipe' => 'error', 'data' => 'Hanya transaksi penjualan tanggal hari ini yang bisa diedit'];
        }

        return $this->persistSale($toko_id, $username, $input, $existingSale);
    }

    public function deleteSale(string $toko_id, string $username, string $jual_id): array
    {
        $existingSale = $this->getSaleAggregate($toko_id, $jual_id);
        if (! $existingSale) {
            return ['tipe' => 'error', 'data' => 'Transaksi penjualan tidak ditemukan'];
        }
        if (! $this->canModifySaleDate($existingSale['tgl'] ?? null)) {
            return ['tipe' => 'error', 'data' => 'Hanya transaksi penjualan tanggal hari ini yang bisa dihapus'];
        }

        $timestamp = date('Y-m-d H:i:s');
        $this->db->transBegin();

        $this->reverseSaleEffects($existingSale, $toko_id, $username, $timestamp, 'Hapus transaksi penjualan');
        $this->db->table('penjualan')
            ->where('toko_id', $toko_id)
            ->where('jual_id', $jual_id)
            ->delete();

        if (! $this->db->transStatus()) {
            $this->db->transRollback();
            return ['tipe' => 'error', 'data' => 'Gagal menghapus transaksi penjualan'];
        }

        $this->db->transCommit();
        return ['tipe' => 'success', 'data' => 'Transaksi penjualan berhasil dihapus'];
    }

    public function incrementReprintCount(string $toko_id, string $jual_id): bool
    {
        $exists = $this->db->query(
            "SELECT jual_id FROM penjualan WHERE toko_id=:toko_id: AND jual_id=:jual_id: LIMIT 1",
            ['toko_id' => $toko_id, 'jual_id' => $jual_id]
        )->getRowArray();
        if (! $exists) {
            return false;
        }

        $this->db->table('penjualan')
            ->where('toko_id', $toko_id)
            ->where('jual_id', $jual_id)
            ->set('reprint_count', 'IFNULL(reprint_count,0)+1', false)
            ->update();

        return true;
    }

    private function persistSale(string $toko_id, string $username, array $input, ?array $existingSale = null): array
    {
        $custId = trim((string) ($input['cust_id'] ?? 'CUST-GENERAL'));
        $detailRows = json_decode((string) ($input['detail_json'] ?? '[]'), true) ?: [];
        $paymentRows = json_decode((string) ($input['payment_json'] ?? '[]'), true) ?: [];
        $diskonNota = round((float) ($input['diskon_nota'] ?? 0), 2);
        $redeemPoints = max(0, (int) ($input['redeem_points'] ?? 0));
        $jatuhTempo = trim((string) ($input['jatuh_tempo'] ?? ''));
        $cashReceived = round((float) ($input['cash_received'] ?? 0), 2);
        $timestamp = date('Y-m-d H:i:s');
        $earnedPoints = 0;

        $this->db->transBegin();

        if ($existingSale) {
            $this->reverseSaleEffects($existingSale, $toko_id, $username, $timestamp, 'Reversal edit transaksi penjualan');
        }

        if (empty($detailRows)) {
            $this->db->transRollback();
            return ['tipe' => 'error', 'data' => 'Keranjang belanja masih kosong'];
        }

        $customer = $this->getCustomerPayload($custId);
        if (! $customer) {
            $this->db->transRollback();
            return ['tipe' => 'error', 'data' => 'Customer tidak ditemukan'];
        }

        $isGeneral = $custId === 'CUST-GENERAL';
        if ($redeemPoints > 0 && $isGeneral) {
            $this->db->transRollback();
            return ['tipe' => 'error', 'data' => 'Penukaran poin hanya berlaku untuk member terdaftar'];
        }
        if ($redeemPoints > (int) ($customer['poin'] ?? 0)) {
            $this->db->transRollback();
            return ['tipe' => 'error', 'data' => 'Poin customer tidak mencukupi untuk ditukarkan'];
        }

        $sanitizedDetails = [];
        $gross = 0;
        $detailNetto = 0;
        $seq = 1;
        foreach ($detailRows as $row) {
            $kodeItem = trim((string) ($row['kode_item'] ?? ''));
            $satId = trim((string) ($row['sat_id'] ?? ''));
            if ($kodeItem === '' || $satId === '') {
                continue;
            }

            $payload = $this->getItemPayload($toko_id, $kodeItem);
            if (! $payload) {
                $this->db->transRollback();
                return ['tipe' => 'error', 'data' => 'Item ' . $kodeItem . ' tidak ditemukan atau tidak aktif'];
            }

            $selected = null;
            foreach (($payload['satuan_options'] ?? []) as $option) {
                if (($option['sat_id'] ?? '') === $satId) {
                    $selected = $option;
                    break;
                }
            }
            if (! $selected) {
                $this->db->transRollback();
                return ['tipe' => 'error', 'data' => 'Satuan ' . $satId . ' untuk item ' . $kodeItem . ' tidak valid'];
            }

            $qtyJual = round((float) ($row['qty_jual'] ?? 0), 4);
            if ($qtyJual <= 0) {
                $this->db->transRollback();
                return ['tipe' => 'error', 'data' => 'Qty jual item ' . $kodeItem . ' harus lebih besar dari nol'];
            }

            $stokMax = (float) ($selected['stok_maksimal'] ?? 0);
            if ($qtyJual - $stokMax > 0.0001) {
                $this->db->transRollback();
                return ['tipe' => 'error', 'data' => 'Stok tidak mencukupi untuk item ' . $kodeItem . ' / ' . $satId];
            }

            $qtyKonversi = (float) ($selected['qty_konversi'] ?? 0);
            $hargaPokok = round((float) ($selected['harga_pokok'] ?? 0), 2);
            $price = round((float) ($selected['harga_jual'] ?? 0), 2);
            $priceError = $this->getPriceValidationMessage($hargaPokok, $price);
            if ($qtyKonversi <= 0) {
                $this->db->transRollback();
                return ['tipe' => 'error', 'data' => 'Konversi satuan item ' . $kodeItem . ' belum valid'];
            }
            if ($priceError !== '') {
                $this->db->transRollback();
                return ['tipe' => 'error', 'data' => 'Item ' . $kodeItem . ' tidak bisa dijual: ' . $priceError];
            }

            $rowGross = round($qtyJual * $price, 2);
            $maxDiskon = max($rowGross - ($qtyJual * $hargaPokok), 0);
            $diskonItem = round((float) ($row['diskon_item'] ?? 0), 2);
            if ($diskonItem - $maxDiskon > 0.0001) {
                $this->db->transRollback();
                return ['tipe' => 'error', 'data' => 'Nilai diskon item ' . $kodeItem . ' melebihi margin keuntungan produk'];
            }

            $rowNetto = round($rowGross - $diskonItem, 2);
            $qtyStock = round($qtyJual * $qtyKonversi, 4);

            $sanitizedDetails[] = [
                'seq_no' => $seq++,
                'kode_item' => $kodeItem,
                'sat_id' => $satId,
                'qty_jual' => $qtyJual,
                'qty_konversi' => $qtyKonversi,
                'qty_stock' => $qtyStock,
                'harga_pokok' => $hargaPokok,
                'price' => $price,
                'gross' => $rowGross,
                'diskon_item' => $diskonItem,
                'netto' => $rowNetto,
            ];
            $gross += $rowGross;
            $detailNetto += $rowNetto;
        }

        $redeemNominal = round((float) $redeemPoints, 2);
        if ($diskonNota < 0) {
            $this->db->transRollback();
            return ['tipe' => 'error', 'data' => 'Diskon nota tidak boleh negatif'];
        }

        $netto = round($detailNetto - $diskonNota - $redeemNominal, 2);
        if ($netto <= 0) {
            $this->db->transRollback();
            return ['tipe' => 'error', 'data' => 'Total pembayaran setelah diskon harus lebih besar dari nol'];
        }

        $sanitizedPayments = [];
        $totalPaymentAllocated = 0;
        $tunaiAllocated = 0;
        foreach ($paymentRows as $row) {
            $caraBayar = strtoupper(trim((string) ($row['cara_bayar'] ?? '')));
            $nominal = round((float) ($row['nominal_bayar'] ?? 0), 2);
            $bankNama = trim((string) ($row['bank_nama'] ?? ''));
            $rekeningNo = trim((string) ($row['rekening_no'] ?? ''));

            if ($nominal <= 0) {
                continue;
            }
            if (! in_array($caraBayar, ['TUNAI', 'TRANSFER', 'QRIS'], true)) {
                $this->db->transRollback();
                return ['tipe' => 'error', 'data' => 'Metode pembayaran tidak valid'];
            }
            if (in_array($caraBayar, ['TRANSFER', 'QRIS'], true) && $bankNama === '') {
                $this->db->transRollback();
                return ['tipe' => 'error', 'data' => 'Nama bank/e-wallet wajib diisi untuk pembayaran non tunai'];
            }
            if ($caraBayar === 'TRANSFER' && $rekeningNo === '') {
                $this->db->transRollback();
                return ['tipe' => 'error', 'data' => 'Nomor rekening wajib diisi untuk transfer'];
            }

            $sanitizedPayments[] = [
                'cara_bayar' => $caraBayar,
                'nominal_bayar' => $nominal,
                'bank_nama' => in_array($caraBayar, ['TRANSFER', 'QRIS'], true) ? $bankNama : null,
                'rekening_no' => $caraBayar === 'TRANSFER' ? $rekeningNo : null,
            ];
            $totalPaymentAllocated += $nominal;
            if ($caraBayar === 'TUNAI') {
                $tunaiAllocated += $nominal;
            }
        }

        if (empty($sanitizedPayments) && ! $isGeneral) {
            $sanitizedPayments[] = [
                'cara_bayar' => 'TUNAI',
                'nominal_bayar' => 0,
                'bank_nama' => null,
                'rekening_no' => null,
            ];
        }

        if ($totalPaymentAllocated - $netto > 0.0001) {
            $this->db->transRollback();
            return ['tipe' => 'error', 'data' => 'Total alokasi pembayaran tidak boleh melebihi total tagihan'];
        }

        if ($cashReceived > 0 && $tunaiAllocated <= 0) {
            $this->db->transRollback();
            return ['tipe' => 'error', 'data' => 'Uang tunai diterima diisi tetapi alokasi pembayaran tunai belum ada'];
        }
        if ($cashReceived + 0.0001 < $tunaiAllocated) {
            $this->db->transRollback();
            return ['tipe' => 'error', 'data' => 'Uang tunai diterima tidak boleh lebih kecil dari alokasi pembayaran tunai'];
        }

        $sisaPiutang = round(max($netto - $totalPaymentAllocated, 0), 2);
        if ($isGeneral && $sisaPiutang > 0.0001) {
            $this->db->transRollback();
            return ['tipe' => 'error', 'data' => 'Pelanggan umum wajib melakukan pembayaran lunas'];
        }
        if ($sisaPiutang > 0.0001 && $jatuhTempo === '') {
            $this->db->transRollback();
            return ['tipe' => 'error', 'data' => 'Tanggal jatuh tempo wajib diisi untuk transaksi kredit member'];
        }
        if ($sisaPiutang <= 0.0001) {
            $jatuhTempo = null;
        }

        $cashChange = round(max($cashReceived - $tunaiAllocated, 0), 2);
        $isKredit = $sisaPiutang > 0.0001 ? '1' : '0';
        $statusBayar = $sisaPiutang <= 0.0001 ? 'LUNAS' : ($totalPaymentAllocated > 0 ? 'CICIL' : 'BELUM');
        $jualId = $existingSale['jual_id'] ?? $this->getNextId($toko_id);

        if ($existingSale) {
            $this->db->table('penjualan_detail')
                ->where('toko_id', $toko_id)
                ->where('jual_id', $jualId)
                ->delete();
            $this->db->table('penjualan_pembayaran')
                ->where('toko_id', $toko_id)
                ->where('jual_id', $jualId)
                ->delete();
            $this->db->table('penjualan')
                ->where('toko_id', $toko_id)
                ->where('jual_id', $jualId)
                ->update([
                    'cust_id' => $custId,
                    'gross' => round($gross, 2),
                    'diskon_nota' => round($diskonNota, 2),
                    'redeem_points' => $redeemPoints,
                    'redeem_nominal' => round($redeemNominal, 2),
                    'netto' => round($netto, 2),
                    'is_kredit' => $isKredit,
                    'status_bayar' => $statusBayar,
                    'sisa_piutang' => $sisaPiutang,
                    'jatuh_tempo' => $jatuhTempo,
                    'cash_received' => round($cashReceived, 2),
                    'cash_change' => round($cashChange, 2),
                    'earned_points' => 0,
                    'updid' => $username,
                    'updtime' => $timestamp,
                ]);
        } else {
            $this->db->table('penjualan')->insert([
                'jual_id' => $jualId,
                'toko_id' => $toko_id,
                'tgl' => $timestamp,
                'cust_id' => $custId,
                'gross' => round($gross, 2),
                'diskon_nota' => round($diskonNota, 2),
                'redeem_points' => $redeemPoints,
                'redeem_nominal' => round($redeemNominal, 2),
                'netto' => round($netto, 2),
                'is_kredit' => $isKredit,
                'status_bayar' => $statusBayar,
                'sisa_piutang' => $sisaPiutang,
                'jatuh_tempo' => $jatuhTempo,
                'cash_received' => round($cashReceived, 2),
                'cash_change' => round($cashChange, 2),
                'earned_points' => 0,
                'updid' => $username,
                'updtime' => $timestamp,
            ]);
        }

        foreach ($sanitizedDetails as $row) {
            $this->db->table('penjualan_detail')->insert([
                'jual_id' => $jualId,
                'toko_id' => $toko_id,
                'seq_no' => $row['seq_no'],
                'kode_item' => $row['kode_item'],
                'sat_id' => $row['sat_id'],
                'qty_jual' => $row['qty_jual'],
                'qty_konversi' => $row['qty_konversi'],
                'qty_stock' => $row['qty_stock'],
                'harga_pokok' => $row['harga_pokok'],
                'price' => $row['price'],
                'gross' => $row['gross'],
                'diskon_item' => $row['diskon_item'],
                'netto' => $row['netto'],
            ]);

            $this->db->query(
                "INSERT IGNORE INTO stmast(toko_id, kode_item) VALUES(:toko_id:, :kode_item:)",
                [
                    'toko_id' => $toko_id,
                    'kode_item' => $row['kode_item'],
                ]
            );

            $this->db->table('stmast')
                ->where('toko_id', $toko_id)
                ->where('kode_item', $row['kode_item'])
                ->set('jual', 'IFNULL(jual,0)+' . (float) $row['qty_stock'], false)
                ->set('qty', 'IFNULL(qty,0)-' . (float) $row['qty_stock'], false)
                ->set('rp_saldo_akh', '(IFNULL(qty,0)-' . (float) $row['qty_stock'] . ')*IFNULL(acost,0)', false)
                ->update();
        }

        foreach ($sanitizedPayments as $row) {
            if ($row['nominal_bayar'] <= 0) {
                continue;
            }
            $this->db->table('penjualan_pembayaran')->insert([
                'jual_id' => $jualId,
                'toko_id' => $toko_id,
                'tgl_bayar' => $timestamp,
                'cara_bayar' => $row['cara_bayar'],
                'nominal_bayar' => $row['nominal_bayar'],
                'bank_nama' => $row['bank_nama'],
                'rekening_no' => $row['rekening_no'],
                'updid' => $username,
            ]);
        }

        if (! $isGeneral && $redeemPoints > 0) {
            $this->mutateCustomerPoints(
                $toko_id,
                $custId,
                $jualId,
                'kurang',
                $redeemPoints,
                $redeemNominal,
                $this->getNominalPerPoin(),
                $timestamp,
                $username,
                'Penukaran poin menjadi diskon belanja'
            );
        }
        if (! $isGeneral) {
            $earnedPoints = $this->awardPointsFromSale($toko_id, $custId, $jualId, $netto, $timestamp, $username);
            $this->db->table('penjualan')
                ->where('jual_id', $jualId)
                ->where('toko_id', $toko_id)
                ->update(['earned_points' => $earnedPoints]);
        }

        if (! $this->db->transStatus()) {
            $this->db->transRollback();
            return ['tipe' => 'error', 'data' => 'Gagal menyimpan transaksi penjualan'];
        }

        $this->db->transCommit();

        return [
            'tipe' => 'success',
            'data' => $existingSale ? 'Transaksi penjualan berhasil diupdate' : 'Transaksi penjualan berhasil disimpan',
            'jual_id' => $jualId,
            'receipt_url' => base_url('/jual/struk/' . $jualId),
            'earned_points' => $earnedPoints,
            'redirect_url' => $existingSale ? base_url('/listjual') : null,
        ];
    }

    private function normalizeDateFilter($value): ?string
    {
        $value = trim((string) $value);
        if ($value === '') {
            return null;
        }

        $date = date_create($value);
        return $date ? $date->format('Y-m-d') : null;
    }

    private function getSaleAggregate(string $toko_id, string $jual_id): ?array
    {
        $header = $this->db->query(
            "SELECT *
             FROM penjualan
             WHERE toko_id=:toko_id: AND jual_id=:jual_id:
             LIMIT 1",
            ['toko_id' => $toko_id, 'jual_id' => $jual_id]
        )->getRowArray();

        if (! $header) {
            return null;
        }

        $header['details'] = $this->db->query(
            "SELECT d.*, p.nama_item
             FROM penjualan_detail d
             LEFT JOIN prodmast p ON p.kode_item=d.kode_item
             WHERE d.toko_id=:toko_id: AND d.jual_id=:jual_id:
             ORDER BY d.seq_no ASC",
            ['toko_id' => $toko_id, 'jual_id' => $jual_id]
        )->getResultArray();

        $header['payments'] = $this->db->query(
            "SELECT *
             FROM penjualan_pembayaran
             WHERE toko_id=:toko_id: AND jual_id=:jual_id:
             ORDER BY bayar_id ASC",
            ['toko_id' => $toko_id, 'jual_id' => $jual_id]
        )->getResultArray();

        return $header;
    }

    private function reverseSaleEffects(array $sale, string $toko_id, string $username, string $timestamp, string $reason): void
    {
        foreach (($sale['details'] ?? []) as $row) {
            $qtyStock = round((float) ($row['qty_stock'] ?? 0), 4);
            if ($qtyStock <= 0) {
                continue;
            }

            $this->db->query(
                "INSERT IGNORE INTO stmast(toko_id, kode_item) VALUES(:toko_id:, :kode_item:)",
                [
                    'toko_id' => $toko_id,
                    'kode_item' => $row['kode_item'],
                ]
            );

            $this->db->table('stmast')
                ->where('toko_id', $toko_id)
                ->where('kode_item', $row['kode_item'])
                ->set('jual', 'GREATEST(IFNULL(jual,0)-' . $qtyStock . ',0)', false)
                ->set('qty', 'IFNULL(qty,0)+' . $qtyStock, false)
                ->set('rp_saldo_akh', '(IFNULL(qty,0)+' . $qtyStock . ')*IFNULL(acost,0)', false)
                ->update();
        }

        $custId = (string) ($sale['cust_id'] ?? 'CUST-GENERAL');
        if ($custId === 'CUST-GENERAL') {
            return;
        }

        $nominalPerPoin = $this->getNominalPerPoin();
        $earnedPoints = (int) ($sale['earned_points'] ?? 0);
        $redeemPoints = (int) ($sale['redeem_points'] ?? 0);

        if ($earnedPoints > 0) {
            $this->mutateCustomerPoints(
                $toko_id,
                $custId,
                (string) $sale['jual_id'],
                'kurang',
                $earnedPoints,
                (float) ($sale['netto'] ?? 0),
                $nominalPerPoin,
                $timestamp,
                $username,
                $reason . ' - rollback poin hasil belanja'
            );
        }

        if ($redeemPoints > 0) {
            $this->mutateCustomerPoints(
                $toko_id,
                $custId,
                (string) $sale['jual_id'],
                'tambah',
                $redeemPoints,
                (float) ($sale['redeem_nominal'] ?? $redeemPoints),
                $nominalPerPoin,
                $timestamp,
                $username,
                $reason . ' - pengembalian poin redeem'
            );
        }
    }

    private function awardPointsFromSale(string $toko_id, string $custId, string $jualId, float $nominalBelanja, string $timestamp, string $username): int
    {
        $nominalPerPoin = $this->getNominalPerPoin();
        $points = $nominalPerPoin > 0 ? (int) floor($nominalBelanja / $nominalPerPoin) : 0;
        if ($points <= 0) {
            return 0;
        }

        $this->mutateCustomerPoints(
            $toko_id,
            $custId,
            $jualId,
            'tambah',
            $points,
            $nominalBelanja,
            $nominalPerPoin,
            $timestamp,
            $username,
            'Poin dari transaksi penjualan'
        );

        return $points;
    }

    private function mutateCustomerPoints(
        string $tokoId,
        string $custId,
        string $trxId,
        string $jenis,
        int $points,
        float $nominalTransaksi,
        int $nominalPerPoin,
        string $tanggal,
        string $username,
        string $keterangan
    ): int {
        $points = max(0, $points);
        if ($custId === 'CUST-GENERAL' || $points <= 0) {
            return 0;
        }

        $customer = $this->db->query(
            "SELECT poin FROM customer WHERE cust_id=:cust_id: LIMIT 1",
            ['cust_id' => $custId]
        )->getRowArray();
        if (! $customer) {
            return 0;
        }

        $before = (int) ($customer['poin'] ?? 0);
        $after = $jenis === 'tambah'
            ? $before + $points
            : max(0, $before - $points);
        $effectiveIn = $jenis === 'tambah' ? $after - $before : 0;
        $effectiveOut = $jenis === 'kurang' ? $before - $after : 0;

        $this->db->table('customer')
            ->where('cust_id', $custId)
            ->update([
                'poin' => $after,
                'updid' => $username,
                'updtime' => $tanggal,
            ]);

        $this->db->table('history_poin_member')->insert([
            'toko_id' => $tokoId,
            'cust_id' => $custId,
            'trx_id' => $trxId,
            'tanggal' => $tanggal,
            'jenis' => $jenis,
            'nominal_transaksi' => $nominalTransaksi,
            'nominal_per_poin' => $nominalPerPoin,
            'poin_masuk' => $effectiveIn,
            'poin_keluar' => $effectiveOut,
            'poin_before' => $before,
            'poin_after' => $after,
            'keterangan' => $keterangan,
            'updid' => $username,
        ]);

        return $jenis === 'tambah' ? $effectiveIn : $effectiveOut;
    }

    public function getReceiptData(string $toko_id, string $jual_id): ?array
    {
        $header = $this->db->query(
            "SELECT j.*, COALESCE(c.nama, 'Pelanggan Umum') AS customer_nama, COALESCE(c.kontak, '') AS customer_kontak,
                    t.toko_nama, t.toko_alamat, t.toko_phone
             FROM penjualan j
             LEFT JOIN customer c ON c.cust_id=j.cust_id
             LEFT JOIN toko t ON t.toko_id=j.toko_id
             WHERE j.toko_id=:toko_id: AND j.jual_id=:jual_id:
             LIMIT 1",
            [
                'toko_id' => $toko_id,
                'jual_id' => $jual_id,
            ]
        )->getRowArray();

        if (! $header) {
            return null;
        }

        $header['details'] = $this->db->query(
            "SELECT d.*, p.nama_item
             FROM penjualan_detail d
             LEFT JOIN prodmast p ON p.kode_item=d.kode_item
             WHERE d.toko_id=:toko_id: AND d.jual_id=:jual_id:
             ORDER BY d.seq_no ASC",
            [
                'toko_id' => $toko_id,
                'jual_id' => $jual_id,
            ]
        )->getResultArray();

        $header['payments'] = $this->db->query(
            "SELECT *
             FROM penjualan_pembayaran
             WHERE toko_id=:toko_id: AND jual_id=:jual_id:
             ORDER BY bayar_id ASC",
            [
                'toko_id' => $toko_id,
                'jual_id' => $jual_id,
            ]
        )->getResultArray();

        $header['customer_total_piutang'] = 0;
        if (($header['cust_id'] ?? 'CUST-GENERAL') !== 'CUST-GENERAL') {
            $row = $this->db->query(
                "SELECT COALESCE(SUM(sisa_piutang), 0) AS total_piutang
                 FROM penjualan
                 WHERE cust_id=:cust_id:
                    AND status_bayar IN ('BELUM', 'CICIL')",
                ['cust_id' => $header['cust_id']]
            )->getRowArray();
            $header['customer_total_piutang'] = (float) ($row['total_piutang'] ?? 0);
        }

        return $header;
    }

    public function ajaxPiutang(array $params, string $toko_id, string $filter): array
    {
        $start = (int) ($params['start'] ?? 0);
        $length = $params['length'] ?? 25;
        $search = trim((string) ($params['search_value'] ?? ''));
        $queryLimit = $length !== '-1' ? " LIMIT $start, " . (int) $length : '';

        $binds = ['toko_id' => $toko_id];
        $where = " WHERE j.toko_id = :toko_id: AND j.is_kredit = '1' ";

        if ($filter === 'LUNAS') {
            $where .= " AND j.status_bayar = 'LUNAS' ";
        } elseif ($filter === 'BELUM') {
            $where .= " AND j.status_bayar IN ('BELUM','CICIL') ";
        }

        if ($search !== '') {
            $where .= " AND (j.jual_id LIKE :search: OR j.cust_id LIKE :search: OR c.nama LIKE :search: OR c.kontak LIKE :search:)";
            $binds['search'] = '%' . $this->db->escapeLikeString($search) . '%';
        }

        $totalRow = $this->db->query(
            "SELECT COUNT(*) total FROM penjualan j WHERE j.toko_id=:toko_id: AND j.is_kredit='1'",
            ['toko_id' => $toko_id]
        )->getRowArray();
        $filtered = $totalRow['total'] ?? 0;
        if ($search !== '' || $filter !== 'BELUM') {
            $filteredRow = $this->db->query(
                "SELECT COUNT(*) total
                 FROM penjualan j
                 LEFT JOIN customer c ON c.cust_id=j.cust_id
                 $where",
                $binds
            )->getRowArray();
            $filtered = $filteredRow['total'] ?? 0;
        }

        $data = $this->db->query(
            "SELECT j.*, c.nama AS customer_nama, c.kontak AS customer_kontak,
                    (j.netto - j.sisa_piutang) AS total_bayar,
                    DATEDIFF(CURDATE(), j.jatuh_tempo) AS hari_lewat
             FROM penjualan j
             LEFT JOIN customer c ON c.cust_id=j.cust_id
             $where
             ORDER BY
                CASE
                    WHEN j.status_bayar <> 'LUNAS' AND j.jatuh_tempo IS NOT NULL AND j.jatuh_tempo < CURDATE() THEN 0
                    ELSE 1
                END ASC,
                j.jatuh_tempo ASC,
                j.updtime DESC,
                j.tgl DESC
             $queryLimit",
            $binds
        )->getResultArray();

        return [
            'data' => $data,
            'total_count' => (int) ($totalRow['total'] ?? 0),
            'total_filtered' => (int) $filtered,
        ];
    }

    public function ajaxLaporanPenjualan(array $params, string $sessionTokoId, bool $allowMultiStore = false): array
    {
        $start = (int) ($params['start'] ?? 0);
        $length = $params['length'] ?? 25;
        $search = trim((string) ($params['search_value'] ?? ''));
        $startDate = $this->normalizeDateFilter($params['date_start'] ?? '') ?: date('Y-m-01');
        $endDate = $this->normalizeDateFilter($params['date_end'] ?? '') ?: date('Y-m-d');
        if ($startDate > $endDate) {
            [$startDate, $endDate] = [$endDate, $startDate];
        }

        $storeIds = $this->resolveStoreIds($params['toko_ids'] ?? [], $sessionTokoId, $allowMultiStore);
        $queryLimit = $length !== '-1' ? " LIMIT $start, " . (int) $length : '';

        $binds = [
            'start_date' => $startDate . ' 00:00:00',
            'end_date' => $endDate . ' 23:59:59',
        ];
        $storePlaceholders = [];
        foreach ($storeIds as $idx => $storeId) {
            $key = 'toko_id_' . $idx;
            $storePlaceholders[] = ':' . $key . ':';
            $binds[$key] = $storeId;
        }

        $where = " WHERE j.tgl BETWEEN :start_date: AND :end_date: AND j.toko_id IN (" . implode(',', $storePlaceholders) . ")";
        if ($search !== '') {
            $where .= " AND (
                DATE(j.tgl) LIKE :search:
                OR j.toko_id LIKE :search:
                OR COALESCE(c.nama, '') LIKE :search:
                OR j.cust_id LIKE :search:
            )";
            $binds['search'] = '%' . $this->db->escapeLikeString($search) . '%';
        }

        $headerSql = "
            SELECT
                DATE(j.tgl) AS tanggal,
                GROUP_CONCAT(DISTINCT j.toko_id ORDER BY j.toko_id SEPARATOR ', ') AS daftar_toko,
                COUNT(DISTINCT CASE
                    WHEN j.cust_id = 'CUST-GENERAL' THEN CONCAT('G-', j.jual_id)
                    ELSE CONCAT('C-', j.cust_id)
                END) AS jumlah_customer,
                COUNT(DISTINCT j.jual_id) AS jumlah_transaksi,
                COALESCE(SUM(j.netto), 0) AS omset
            FROM penjualan j
            LEFT JOIN customer c ON c.cust_id=j.cust_id
            $where
            GROUP BY DATE(j.tgl)
        ";
        $detailSql = "
            SELECT
                DATE(j.tgl) AS tanggal,
                COALESCE(SUM(d.qty_jual), 0) AS total_qty,
                COALESCE(SUM(d.netto - (d.qty_jual * d.harga_pokok)), 0) AS margin_bruto
            FROM penjualan j
            INNER JOIN penjualan_detail d ON d.toko_id=j.toko_id AND d.jual_id=j.jual_id
            $where
            GROUP BY DATE(j.tgl)
        ";
        $baseSql = "
            SELECT
                h.tanggal,
                h.daftar_toko,
                h.jumlah_customer,
                h.jumlah_transaksi,
                COALESCE(d.total_qty, 0) AS total_qty,
                h.omset,
                COALESCE(d.margin_bruto, 0) AS margin_bruto
            FROM ($headerSql) h
            LEFT JOIN ($detailSql) d ON d.tanggal = h.tanggal
        ";

        $countRow = $this->db->query(
            "SELECT COUNT(*) AS total FROM ($baseSql) x",
            $binds
        )->getRowArray();

        $data = $this->db->query(
            "SELECT tanggal, daftar_toko, jumlah_customer, jumlah_transaksi, total_qty, omset, margin_bruto
             FROM ($baseSql) x
             ORDER BY tanggal DESC
             $queryLimit",
            $binds
        )->getResultArray();

        return [
            'data' => $data,
            'total_count' => (int) ($countRow['total'] ?? 0),
            'total_filtered' => (int) ($countRow['total'] ?? 0),
        ];
    }

    public function getLaporanPenjualanSummary(array $params, string $sessionTokoId, bool $allowMultiStore = false): array
    {
        $startDate = $this->normalizeDateFilter($params['date_start'] ?? '') ?: date('Y-m-01');
        $endDate = $this->normalizeDateFilter($params['date_end'] ?? '') ?: date('Y-m-d');
        if ($startDate > $endDate) {
            [$startDate, $endDate] = [$endDate, $startDate];
        }

        $storeIds = $this->resolveStoreIds($params['toko_ids'] ?? [], $sessionTokoId, $allowMultiStore);
        $binds = [
            'start_date' => $startDate . ' 00:00:00',
            'end_date' => $endDate . ' 23:59:59',
        ];
        $storePlaceholders = [];
        foreach ($storeIds as $idx => $storeId) {
            $key = 'toko_id_' . $idx;
            $storePlaceholders[] = ':' . $key . ':';
            $binds[$key] = $storeId;
        }

        $where = " WHERE j.tgl BETWEEN :start_date: AND :end_date: AND j.toko_id IN (" . implode(',', $storePlaceholders) . ")";

        $summary = $this->db->query(
            "SELECT
                COUNT(DISTINCT CASE
                    WHEN j.cust_id = 'CUST-GENERAL' THEN CONCAT('G-', j.jual_id)
                    ELSE CONCAT('C-', j.cust_id)
                END) AS total_customer,
                COUNT(DISTINCT j.jual_id) AS total_transaksi,
                COALESCE(SUM(j.netto), 0) AS total_omset_raw
             FROM penjualan j
             $where",
            $binds
        )->getRowArray() ?: [];

        $marginRow = $this->db->query(
            "SELECT COALESCE(SUM(d.netto - (d.qty_jual * d.harga_pokok)), 0) AS total_margin
             FROM penjualan j
             INNER JOIN penjualan_detail d ON d.toko_id=j.toko_id AND d.jual_id=j.jual_id
             $where",
            $binds
        )->getRowArray() ?: [];

        $dailyRows = $this->db->query(
            "SELECT
                DATE(j.tgl) AS tanggal,
                COALESCE(SUM(j.netto), 0) AS omset_raw
             FROM penjualan j
             $where
             GROUP BY DATE(j.tgl)
             ORDER BY DATE(j.tgl) ASC",
            $binds
        )->getResultArray();

        $marginDailyRows = $this->db->query(
            "SELECT
                DATE(j.tgl) AS tanggal,
                COALESCE(SUM(d.netto - (d.qty_jual * d.harga_pokok)), 0) AS margin_bruto
             FROM penjualan j
             INNER JOIN penjualan_detail d ON d.toko_id=j.toko_id AND d.jual_id=j.jual_id
             $where
             GROUP BY DATE(j.tgl)
             ORDER BY DATE(j.tgl) ASC",
            $binds
        )->getResultArray();

        $marginDailyMap = [];
        foreach ($marginDailyRows as $row) {
            $marginDailyMap[(string) ($row['tanggal'] ?? '')] = (float) ($row['margin_bruto'] ?? 0);
        }

        $daily = [];
        foreach ($dailyRows as $row) {
            $tanggal = (string) ($row['tanggal'] ?? '');
            $daily[] = [
                'tanggal' => $tanggal,
                'label_tanggal' => date('d/m', strtotime($tanggal)),
                'omset' => (float) ($row['omset_raw'] ?? 0),
                'margin_bruto' => (float) ($marginDailyMap[$tanggal] ?? 0),
            ];
        }

        $omsetByStoreRows = $this->db->query(
            "SELECT
                DATE(j.tgl) AS tanggal,
                j.toko_id,
                COALESCE(SUM(j.netto), 0) AS omset
             FROM penjualan j
             $where
             GROUP BY DATE(j.tgl), j.toko_id
             ORDER BY DATE(j.tgl) ASC, j.toko_id ASC",
            $binds
        )->getResultArray();

        $marginByStoreRows = $this->db->query(
            "SELECT
                DATE(j.tgl) AS tanggal,
                j.toko_id,
                COALESCE(SUM(d.netto - (d.qty_jual * d.harga_pokok)), 0) AS margin_bruto
             FROM penjualan j
             INNER JOIN penjualan_detail d ON d.toko_id=j.toko_id AND d.jual_id=j.jual_id
             $where
             GROUP BY DATE(j.tgl), j.toko_id
             ORDER BY DATE(j.tgl) ASC, j.toko_id ASC",
            $binds
        )->getResultArray();

        return [
            'total_customer' => (int) ($summary['total_customer'] ?? 0),
            'total_transaksi' => (int) ($summary['total_transaksi'] ?? 0),
            'total_omset' => (float) ($summary['total_omset_raw'] ?? 0),
            'total_margin' => (float) ($marginRow['total_margin'] ?? 0),
            'daily' => $daily,
            'daily_omset_by_store' => $omsetByStoreRows,
            'daily_margin_by_store' => $marginByStoreRows,
        ];
    }

    public function getPiutangSummary(string $toko_id, string $jual_id): ?array
    {
        return $this->getReceiptData($toko_id, $jual_id);
    }

    public function addPiutangPayment(string $toko_id, string $jual_id, string $username, array $payments): array
    {
        $header = $this->db->query(
            "SELECT * FROM penjualan WHERE toko_id=:toko_id: AND jual_id=:jual_id:",
            ['toko_id' => $toko_id, 'jual_id' => $jual_id]
        )->getRowArray();

        if (!$header) {
            return ['tipe' => 'error', 'data' => 'Data penjualan tidak ditemukan'];
        }
        if (($header['is_kredit'] ?? '0') !== '1') {
            return ['tipe' => 'error', 'data' => 'Pembayaran hanya bisa ditambahkan pada transaksi piutang'];
        }

        $sanitized = [];
        $incoming = 0;
        foreach ($payments as $row) {
            $caraBayar = strtoupper(trim((string) ($row['cara_bayar'] ?? '')));
            $nominalBayar = (float) ($row['nominal_bayar'] ?? 0);
            $bankNama = trim((string) ($row['bank_nama'] ?? ''));
            $rekeningNo = trim((string) ($row['rekening_no'] ?? ''));
            $tglBayar = trim((string) ($row['tgl_bayar'] ?? ''));

            if (!in_array($caraBayar, ['TUNAI', 'TRANSFER', 'QRIS'], true) || $nominalBayar <= 0) {
                return ['tipe' => 'error', 'data' => 'Data cicilan tidak valid'];
            }
            if (in_array($caraBayar, ['TRANSFER', 'QRIS'], true) && $bankNama === '') {
                return ['tipe' => 'error', 'data' => 'Pembayaran non tunai wajib mengisi bank atau e-wallet'];
            }
            if ($caraBayar === 'TRANSFER' && $rekeningNo === '') {
                return ['tipe' => 'error', 'data' => 'Pembayaran transfer wajib mengisi nomor rekening'];
            }

            $incoming += $nominalBayar;
            $sanitized[] = [
                'tgl_bayar' => $tglBayar !== '' ? $tglBayar : date('Y-m-d H:i:s'),
                'cara_bayar' => $caraBayar,
                'nominal_bayar' => round($nominalBayar, 2),
                'bank_nama' => in_array($caraBayar, ['TRANSFER', 'QRIS'], true) ? $bankNama : null,
                'rekening_no' => $caraBayar === 'TRANSFER' ? $rekeningNo : null,
            ];
        }

        if (empty($sanitized)) {
            return ['tipe' => 'error', 'data' => 'Minimal satu cicilan harus diisi'];
        }

        $remaining = (float) ($header['sisa_piutang'] ?? 0);
        if ($incoming - $remaining > 0.0001) {
            return ['tipe' => 'error', 'data' => 'Total cicilan melebihi sisa piutang'];
        }

        $this->db->transStart();
        foreach ($sanitized as $payment) {
            $this->db->table('penjualan_pembayaran')->insert([
                'jual_id' => $jual_id,
                'toko_id' => $toko_id,
                'tgl_bayar' => $payment['tgl_bayar'],
                'cara_bayar' => $payment['cara_bayar'],
                'nominal_bayar' => $payment['nominal_bayar'],
                'bank_nama' => $payment['bank_nama'],
                'rekening_no' => $payment['rekening_no'],
                'updid' => $username,
            ]);
        }
        $this->syncSalePaymentSummary($toko_id, $jual_id);
        $this->db->transComplete();

        if (!$this->db->transStatus()) {
            return ['tipe' => 'error', 'data' => 'Gagal menyimpan cicilan'];
        }

        return ['tipe' => 'success', 'data' => 'Pembayaran piutang berhasil disimpan'];
    }

    private function syncSalePaymentSummary(string $toko_id, string $jual_id): void
    {
        $header = $this->db->query(
            "SELECT netto
             FROM penjualan
             WHERE toko_id=:toko_id: AND jual_id=:jual_id:",
            ['toko_id' => $toko_id, 'jual_id' => $jual_id]
        )->getRowArray();

        if (!$header) {
            return;
        }

        $payRow = $this->db->query(
            "SELECT COALESCE(SUM(nominal_bayar),0) AS total_bayar
             FROM penjualan_pembayaran
             WHERE toko_id=:toko_id: AND jual_id=:jual_id:",
            ['toko_id' => $toko_id, 'jual_id' => $jual_id]
        )->getRowArray();

        $netto = (float) ($header['netto'] ?? 0);
        $totalBayar = (float) ($payRow['total_bayar'] ?? 0);
        $sisaPiutang = max($netto - $totalBayar, 0);

        if ($sisaPiutang <= 0.0001) {
            $statusBayar = 'LUNAS';
            $isKredit = '1';
        } elseif ($totalBayar > 0) {
            $statusBayar = 'CICIL';
            $isKredit = '1';
        } else {
            $statusBayar = 'BELUM';
            $isKredit = '1';
        }

        $this->db->table('penjualan')
            ->where('toko_id', $toko_id)
            ->where('jual_id', $jual_id)
            ->update([
                'status_bayar' => $statusBayar,
                'is_kredit' => $isKredit,
                'sisa_piutang' => round($sisaPiutang, 2),
            ]);
    }

    private function resolveStoreIds($rawStoreIds, string $sessionTokoId, bool $allowMultiStore): array
    {
        if (!$allowMultiStore) {
            return [$sessionTokoId];
        }

        $storeIds = is_array($rawStoreIds) ? $rawStoreIds : [$rawStoreIds];
        $filtered = array_values(array_unique(array_filter(array_map(
            static fn($value): string => trim((string) $value),
            $storeIds
        ))));

        return !empty($filtered) ? $filtered : $this->getAllStoreIds();
    }

    private function getAllStoreIds(): array
    {
        $rows = $this->db->query("SELECT toko_id FROM toko ORDER BY toko_id ASC")->getResultArray();
        $storeIds = array_values(array_filter(array_map(
            static fn(array $row): string => trim((string) ($row['toko_id'] ?? '')),
            $rows
        )));

        return !empty($storeIds) ? $storeIds : [(string) session('toko_id')];
    }

    private function getPriceValidationMessage(float $hargaPokok, float $hargaJual): string
    {
        if ($hargaPokok <= 0) {
            return 'harga pokok masih 0';
        }
        if ($hargaJual <= 0) {
            return 'harga jual masih 0';
        }
        if ($hargaJual < $hargaPokok) {
            return 'harga jual lebih kecil dari harga pokok';
        }

        return '';
    }
}
