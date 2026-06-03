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

    public function saveSale(string $toko_id, string $username, array $input): array
    {
        $custId = trim((string) ($input['cust_id'] ?? 'CUST-GENERAL'));
        $detailRows = json_decode((string) ($input['detail_json'] ?? '[]'), true) ?: [];
        $paymentRows = json_decode((string) ($input['payment_json'] ?? '[]'), true) ?: [];
        $diskonNota = round((float) ($input['diskon_nota'] ?? 0), 2);
        $redeemPoints = max(0, (int) ($input['redeem_points'] ?? 0));
        $jatuhTempo = trim((string) ($input['jatuh_tempo'] ?? ''));
        $cashReceived = round((float) ($input['cash_received'] ?? 0), 2);

        if (empty($detailRows)) {
            return ['tipe' => 'error', 'data' => 'Keranjang belanja masih kosong'];
        }

        $customer = $this->getCustomerPayload($custId);
        if (! $customer) {
            return ['tipe' => 'error', 'data' => 'Customer tidak ditemukan'];
        }

        $isGeneral = $custId === 'CUST-GENERAL';
        if ($redeemPoints > 0 && $isGeneral) {
            return ['tipe' => 'error', 'data' => 'Penukaran poin hanya berlaku untuk member terdaftar'];
        }
        if ($redeemPoints > (int) ($customer['poin'] ?? 0)) {
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
                return ['tipe' => 'error', 'data' => 'Satuan ' . $satId . ' untuk item ' . $kodeItem . ' tidak valid'];
            }

            $qtyJual = round((float) ($row['qty_jual'] ?? 0), 4);
            if ($qtyJual <= 0) {
                return ['tipe' => 'error', 'data' => 'Qty jual item ' . $kodeItem . ' harus lebih besar dari nol'];
            }

            $stokMax = (float) ($selected['stok_maksimal'] ?? 0);
            if ($qtyJual - $stokMax > 0.0001) {
                return ['tipe' => 'error', 'data' => 'Stok tidak mencukupi untuk item ' . $kodeItem . ' / ' . $satId];
            }

            $qtyKonversi = (float) ($selected['qty_konversi'] ?? 0);
            $hargaPokok = round((float) ($selected['harga_pokok'] ?? 0), 2);
            $price = round((float) ($selected['harga_jual'] ?? 0), 2);
            $priceError = $this->getPriceValidationMessage($hargaPokok, $price);
            if ($qtyKonversi <= 0) {
                return ['tipe' => 'error', 'data' => 'Konversi satuan item ' . $kodeItem . ' belum valid'];
            }
            if ($priceError !== '') {
                return ['tipe' => 'error', 'data' => 'Item ' . $kodeItem . ' tidak bisa dijual: ' . $priceError];
            }

            $rowGross = round($qtyJual * $price, 2);
            $maxDiskon = max($rowGross - ($qtyJual * $hargaPokok), 0);
            $diskonItem = round((float) ($row['diskon_item'] ?? 0), 2);
            if ($diskonItem - $maxDiskon > 0.0001) {
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
            return ['tipe' => 'error', 'data' => 'Diskon nota tidak boleh negatif'];
        }

        $netto = round($detailNetto - $diskonNota - $redeemNominal, 2);
        if ($netto <= 0) {
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
                return ['tipe' => 'error', 'data' => 'Metode pembayaran tidak valid'];
            }
            if (in_array($caraBayar, ['TRANSFER', 'QRIS'], true) && $bankNama === '') {
                return ['tipe' => 'error', 'data' => 'Nama bank/e-wallet wajib diisi untuk pembayaran non tunai'];
            }
            if ($caraBayar === 'TRANSFER' && $rekeningNo === '') {
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
            return ['tipe' => 'error', 'data' => 'Total alokasi pembayaran tidak boleh melebihi total tagihan'];
        }

        if ($cashReceived > 0 && $tunaiAllocated <= 0) {
            return ['tipe' => 'error', 'data' => 'Uang tunai diterima diisi tetapi alokasi pembayaran tunai belum ada'];
        }
        if ($cashReceived + 0.0001 < $tunaiAllocated) {
            return ['tipe' => 'error', 'data' => 'Uang tunai diterima tidak boleh lebih kecil dari alokasi pembayaran tunai'];
        }

        $sisaPiutang = round(max($netto - $totalPaymentAllocated, 0), 2);
        if ($isGeneral && $sisaPiutang > 0.0001) {
            return ['tipe' => 'error', 'data' => 'Pelanggan umum wajib melakukan pembayaran lunas'];
        }
        if ($sisaPiutang > 0.0001 && $jatuhTempo === '') {
            return ['tipe' => 'error', 'data' => 'Tanggal jatuh tempo wajib diisi untuk transaksi kredit member'];
        }
        if ($sisaPiutang <= 0.0001) {
            $jatuhTempo = null;
        }

        $cashChange = round(max($cashReceived - $tunaiAllocated, 0), 2);
        $isKredit = $sisaPiutang > 0.0001 ? '1' : '0';
        $statusBayar = $sisaPiutang <= 0.0001 ? 'LUNAS' : ($totalPaymentAllocated > 0 ? 'CICIL' : 'BELUM');
        $jualId = $this->getNextId($toko_id);
        $timestamp = date('Y-m-d H:i:s');
        $earnedPoints = 0;

        $this->db->transStart();

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
            $poinModel = new PoinMemberModel();
            $poinModel->deductPointsFromRedeem($toko_id, $custId, $jualId, $redeemPoints, $redeemNominal, $timestamp, $username);
        }
        if (! $isGeneral) {
            $poinModel = isset($poinModel) ? $poinModel : new PoinMemberModel();
            $earnedPoints = $poinModel->addPointsFromSale($toko_id, $custId, $jualId, $netto, $timestamp, $username);
            $this->db->table('penjualan')
                ->where('jual_id', $jualId)
                ->where('toko_id', $toko_id)
                ->update(['earned_points' => $earnedPoints]);
        }

        $this->db->transComplete();

        if (! $this->db->transStatus()) {
            return ['tipe' => 'error', 'data' => 'Gagal menyimpan transaksi penjualan'];
        }

        return [
            'tipe' => 'success',
            'data' => 'Transaksi penjualan berhasil disimpan',
            'jual_id' => $jualId,
            'receipt_url' => base_url('/jual/struk/' . $jualId),
            'earned_points' => $earnedPoints,
        ];
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
