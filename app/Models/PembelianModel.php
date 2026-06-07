<?php

namespace App\Models;

use CodeIgniter\Model;

class PembelianModel extends Model
{
    protected $table = 'pembelian';
    protected $returnType = 'array';
    protected $protectFields = false;

    public function getNextId(string $toko_id): string
    {
        $row = $this->db->query(
            "SELECT MAX(CAST(RIGHT(beli_id,9) AS UNSIGNED)) AS nomor
             FROM pembelian
             WHERE toko_id = :toko_id: AND beli_id like 'PB%'",
            ['toko_id' => $toko_id]
        )->getRowArray();

        $next = (int) ($row['nomor'] ?? 0) + 1;
        return 'PB' . $toko_id . sprintf("%09s", $next);;
    }
    public function getSHNextId(string $toko_id): string
    {
        $row = $this->db->query(
            "SELECT MAX(CAST(RIGHT(beli_id,9) AS UNSIGNED)) AS nomor
             FROM pembelian
             WHERE toko_id = :toko_id: AND beli_id like 'SH%'",
            ['toko_id' => $toko_id]
        )->getRowArray();

        $next = (int) ($row['nomor'] ?? 0) + 1;
        return 'SH' . $toko_id . sprintf("%09s", $next);;
    }

    public function getSupplierOptions(): array
    {
        return $this->db->query("SELECT supco,nama FROM supmast ORDER BY nama")->getResultArray();
    }

    public function getSaldoHutangFormData(string $toko_id, ?string $beli_id = null): array
    {
        $data = [
            'header' => [
                'beli_id' => $beli_id ? '' : $this->getSHNextId($toko_id),
                'tanggal' => date('Y-m-d'),
                'supco' => '',
                'invoice' => '',
                'keterangan' => '',
                'jatuh_tempo' => date('Y-m-d', strtotime('+1 month')),
                'total_gross' => 0,
            ],
        ];

        if (!$beli_id) {
            return $data;
        }

        $header = $this->db->query(
            "SELECT * FROM pembelian WHERE toko_id=:toko_id: AND beli_id=:beli_id:",
            ['toko_id' => $toko_id, 'beli_id' => $beli_id]
        )->getRowArray();

        if (!$header || !$this->isSaldoHutangId($header['beli_id'] ?? null)) {
            return $data;
        }

        return ['header' => $header];
    }

    public function ajaxList(array $params, string $toko_id): array
    {
        $start = (int) ($params['start'] ?? 0);
        $length = $params['length'] ?? 25;
        $search = trim((string) ($params['search_value'] ?? ''));
        $queryLimit = $length !== '-1' ? " LIMIT $start, " . (int) $length : '';

        $binds = ['toko_id' => $toko_id];
        $where = " WHERE p.toko_id = :toko_id: ";
        if ($search !== '') {
            $where .= " AND (p.beli_id LIKE :search: OR p.invoice LIKE :search: OR s.nama LIKE :search: OR p.supco LIKE :search:)";
            $binds['search'] = '%' . $this->db->escapeLikeString($search) . '%';
        }

        $total = $this->db->query("SELECT COUNT(*) total FROM pembelian WHERE toko_id=:toko_id:", ['toko_id' => $toko_id])->getRowArray();
        $filtered = $total['total'] ?? 0;
        if ($search !== '') {
            $filteredRow = $this->db->query(
                "SELECT COUNT(*) total
                 FROM pembelian p
                 LEFT JOIN supmast s ON s.supco=p.supco
                 $where",
                $binds
            )->getRowArray();
            $filtered = $filteredRow['total'] ?? 0;
        }

        $closingDate = $this->getClosingDate($toko_id);
        $data = $this->db->query(
            "SELECT p.*, s.nama AS supplier_nama, COUNT(pd.seq_no) AS jml_item
             FROM pembelian p
             LEFT JOIN supmast s ON s.supco=p.supco
             LEFT JOIN pembelian_detail pd ON pd.toko_id=p.toko_id AND pd.beli_id=p.beli_id
             $where
             GROUP BY p.toko_id,p.beli_id
             ORDER BY p.tanggal DESC, p.updtime DESC, p.beli_id DESC
             $queryLimit",
            $binds
        )->getResultArray();

        foreach ($data as &$row) {
            $row['can_edit'] = !(($row['status_nota'] ?? '') === 'TERIMA' && ($row['tanggal'] ?? '') < $closingDate);
            $row['closing_date'] = $closingDate;
        }

        return [
            'data' => $data,
            'total_count' => (int) ($total['total'] ?? 0),
            'total_filtered' => (int) $filtered,
        ];
    }

    public function ajaxHutang(array $params, string $toko_id, string $filter): array
    {
        $start = (int) ($params['start'] ?? 0);
        $length = $params['length'] ?? 25;
        $search = trim((string) ($params['search_value'] ?? ''));
        $queryLimit = $length !== '-1' ? " LIMIT $start, " . (int) $length : '';

        $binds = ['toko_id' => $toko_id];
        $where = " WHERE p.toko_id = :toko_id: AND p.is_kredit = 1 ";

        if ($filter === 'LUNAS') {
            $where .= " AND p.status_bayar = 'LUNAS' ";
        } elseif ($filter === 'BELUM') {
            $where .= " AND p.status_bayar IN ('BELUM','CICIL') ";
        }

        if ($search !== '') {
            $where .= " AND (p.beli_id LIKE :search: OR p.invoice LIKE :search: OR s.nama LIKE :search: OR p.supco LIKE :search:)";
            $binds['search'] = '%' . $this->db->escapeLikeString($search) . '%';
        }

        $totalRow = $this->db->query(
            "SELECT COUNT(*) total FROM pembelian p WHERE p.toko_id=:toko_id: AND p.is_kredit=1",
            ['toko_id' => $toko_id]
        )->getRowArray();
        $filtered = $totalRow['total'] ?? 0;
        if ($search !== '' || $filter !== 'BELUM') {
            $filteredRow = $this->db->query(
                "SELECT COUNT(*) total
                 FROM pembelian p
                 LEFT JOIN supmast s ON s.supco=p.supco
                 $where",
                $binds
            )->getRowArray();
            $filtered = $filteredRow['total'] ?? 0;
        }

        $data = $this->db->query(
            "SELECT p.*, s.nama AS supplier_nama,
                    DATEDIFF(CURDATE(), p.jatuh_tempo) AS hari_lewat
             FROM pembelian p
             LEFT JOIN supmast s ON s.supco=p.supco
             $where
             ORDER BY
                CASE
                    WHEN p.status_bayar <> 'LUNAS' AND p.jatuh_tempo IS NOT NULL AND p.jatuh_tempo < CURDATE() THEN 0
                    ELSE 1
                END ASC,
                p.jatuh_tempo ASC,
                p.updtime DESC
             $queryLimit",
            $binds
        )->getResultArray();

        return [
            'data' => $data,
            'total_count' => (int) ($totalRow['total'] ?? 0),
            'total_filtered' => (int) $filtered,
        ];
    }

    public function searchItems(string $toko_id, string $term): array
    {
        $search = '%' . $this->db->escapeLikeString($term) . '%';
        return $this->db->query(
            "SELECT p.kode_item, p.barcode, p.nama_item, store.supco,
                    COALESCE(base.sat_id, '-') AS sat_dasar,
                    COALESCE(base.qty_konversi, 1) AS qty_dasar,
                    COALESCE(store.harga_pokok, 0) AS harga_default
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
            "SELECT p.kode_item, p.barcode, p.nama_item, base_store.supco
             FROM prodmast p
             LEFT JOIN (
                SELECT kode_item, MAX(COALESCE(supco, '')) AS supco
                FROM prodmast_store
                WHERE toko_id=:toko_id:
                GROUP BY kode_item
             ) base_store ON base_store.kode_item=p.kode_item
             WHERE p.kode_item=:kode_item:
             LIMIT 1",
            ['toko_id' => $toko_id, 'kode_item' => $kode_item]
        )->getRowArray();

        if (!$item) {
            return null;
        }

        $satuan = $this->db->query(
            "SELECT ps.sat_id, ps.qty_konversi, COALESCE(store.harga_pokok, 0) AS harga_pokok
             FROM prodmast_satuan ps
             LEFT JOIN prodmast_store store
                ON store.kode_item=ps.kode_item
                AND store.toko_id=:toko_id:
                AND store.sat_id=ps.sat_id
             WHERE ps.kode_item=:kode_item:
             ORDER BY ps.qty_konversi, ps.sat_id",
            ['toko_id' => $toko_id, 'kode_item' => $kode_item]
        )->getResultArray();

        if (!$satuan) {
            return null;
        }

        $item['satuan'] = $satuan;
        $item['base_sat_id'] = $satuan[0]['sat_id'];
        return $item;
    }

    public function getFormData(string $toko_id, ?string $beli_id = null): array
    {
        $data = [
            'header' => [
                'beli_id' => $beli_id ? '' : $this->getNextId($toko_id),
                'tanggal' => date('Y-m-d'),
                'supco' => '',
                'invoice' => '',
                'status_nota' => 'PO',
                'keterangan' => '',
                'jatuh_tempo' => date('Y-m-d', strtotime('+1 month')),
                'is_kredit' => 0,
                'status_bayar' => 'BELUM',
                'total_gross' => 0,
                'total_bayar' => 0,
                'sisa_bayar' => 0,
            ],
            'details' => [],
            'payments' => [],
        ];

        if (!$beli_id) {
            return $data;
        }

        $header = $this->db->query(
            "SELECT * FROM pembelian WHERE toko_id=:toko_id: AND beli_id=:beli_id:",
            ['toko_id' => $toko_id, 'beli_id' => $beli_id]
        )->getRowArray();

        if (!$header) {
            return $data;
        }

        $details = $this->db->query(
            "SELECT pd.*, p.nama_item, p.barcode
             FROM pembelian_detail pd
             LEFT JOIN prodmast p ON p.kode_item=pd.kode_item
             WHERE pd.toko_id=:toko_id: AND pd.beli_id=:beli_id:
             ORDER BY pd.seq_no",
            ['toko_id' => $toko_id, 'beli_id' => $beli_id]
        )->getResultArray();

        foreach ($details as &$row) {
            $row['satuan_options'] = $this->db->query(
                "SELECT ps.sat_id, ps.qty_konversi, COALESCE(store.harga_pokok, 0) AS harga_pokok
                 FROM prodmast_satuan ps
                 LEFT JOIN prodmast_store store
                    ON store.kode_item=ps.kode_item
                    AND store.toko_id=:toko_id:
                    AND store.sat_id=ps.sat_id
                 WHERE ps.kode_item=:kode_item:
                 ORDER BY ps.qty_konversi, ps.sat_id",
                ['toko_id' => $toko_id, 'kode_item' => $row['kode_item']]
            )->getResultArray();
        }

        $payments = $this->getPaymentHistory($toko_id, $beli_id);

        return [
            'header' => $header,
            'details' => $details,
            'payments' => $payments,
        ];
    }

    public function getPurchaseSummary(string $toko_id, string $beli_id): ?array
    {
        $header = $this->db->query(
            "SELECT p.*, s.nama AS supplier_nama
             FROM pembelian p
             LEFT JOIN supmast s ON s.supco=p.supco
             WHERE p.toko_id=:toko_id: AND p.beli_id=:beli_id:",
            ['toko_id' => $toko_id, 'beli_id' => $beli_id]
        )->getRowArray();

        if (!$header) {
            return null;
        }

        $header['details'] = $this->db->query(
            "SELECT pd.*, p.nama_item
             FROM pembelian_detail pd
             LEFT JOIN prodmast p ON p.kode_item=pd.kode_item
             WHERE pd.toko_id=:toko_id: AND pd.beli_id=:beli_id:
             ORDER BY pd.seq_no",
            ['toko_id' => $toko_id, 'beli_id' => $beli_id]
        )->getResultArray();

        $header['payments'] = $this->getPaymentHistory($toko_id, $beli_id);
        return $header;
    }

    public function saveSaldoHutangAwal(string $toko_id, string $username, array $input, string $mode = 'create'): array
    {
        $headerId = trim((string) ($input['beli_id'] ?? ''));
        $tanggal = trim((string) ($input['tanggal'] ?? ''));
        $supco = trim((string) ($input['supco'] ?? ''));
        $invoice = trim((string) ($input['invoice'] ?? ''));
        $keterangan = trim((string) ($input['keterangan'] ?? ''));
        $jatuhTempo = trim((string) ($input['jatuh_tempo'] ?? ''));
        $totalGross = (float) ($input['total_gross'] ?? 0);

        if ($tanggal === '' || $supco === '' || $invoice === '') {
            return ['tipe' => 'error', 'data' => 'Tanggal, supplier, dan invoice wajib diisi'];
        }
        if ($totalGross <= 0) {
            return ['tipe' => 'error', 'data' => 'Nominal saldo hutang harus lebih besar dari nol'];
        }
        if ($jatuhTempo === '') {
            return ['tipe' => 'error', 'data' => 'Tanggal jatuh tempo wajib diisi'];
        }
        if ($jatuhTempo < $tanggal) {
            return ['tipe' => 'error', 'data' => 'Tanggal jatuh tempo tidak boleh lebih kecil dari tanggal transaksi'];
        }

        if ($mode === 'create') {
            $headerId = $this->getSHNextId($toko_id);
        } elseif ($headerId === '' || !$this->isSaldoHutangId($headerId)) {
            return ['tipe' => 'error', 'data' => 'ID saldo hutang tidak valid'];
        }

        if ($mode !== 'create') {
            $existing = $this->db->query(
                "SELECT * FROM pembelian WHERE toko_id=:toko_id: AND beli_id=:beli_id:",
                ['toko_id' => $toko_id, 'beli_id' => $headerId]
            )->getRowArray();

            if (!$existing || !$this->isSaldoHutangId($existing['beli_id'] ?? null)) {
                return ['tipe' => 'error', 'data' => 'Data saldo hutang awal tidak ditemukan'];
            }

            if ((float) ($existing['total_bayar'] ?? 0) - $totalGross > 0.0001) {
                return ['tipe' => 'error', 'data' => 'Nominal saldo hutang tidak boleh lebih kecil dari total pembayaran yang sudah tercatat'];
            }
        }

        $this->db->transStart();

        $headerData = [
            'toko_id' => $toko_id,
            'beli_id' => $headerId,
            'tanggal' => $tanggal,
            'supco' => $supco,
            'invoice' => $invoice,
            'total_gross' => round($totalGross, 2),
            'total_bayar' => 0,
            'sisa_bayar' => round($totalGross, 2),
            'is_kredit' => 1,
            'status_nota' => 'TERIMA',
            'status_bayar' => 'BELUM',
            'jatuh_tempo' => $jatuhTempo,
            'username' => $username,
            'keterangan' => $keterangan !== '' ? $keterangan : null,
        ];

        if ($mode === 'create') {
            $this->db->table('pembelian')->insert($headerData);
        } else {
            $this->db->table('pembelian')
                ->where('toko_id', $toko_id)
                ->where('beli_id', $headerId)
                ->update($headerData);
        }

        $this->syncPaymentSummary($toko_id, $headerId);
        $this->db->transComplete();

        if (!$this->db->transStatus()) {
            return ['tipe' => 'error', 'data' => 'Gagal menyimpan saldo hutang awal'];
        }

        return [
            'tipe' => 'success',
            'data' => 'Saldo hutang awal berhasil disimpan',
            'beli_id' => $headerId,
        ];
    }

    public function getPaymentHistory(string $toko_id, string $beli_id): array
    {
        return $this->db->query(
            "SELECT *
             FROM pembelian_pembayaran
             WHERE toko_id=:toko_id: AND beli_id=:beli_id:
             ORDER BY tanggal_bayar ASC, bayar_id ASC",
            ['toko_id' => $toko_id, 'beli_id' => $beli_id]
        )->getResultArray();
    }

    public function savePurchase(string $toko_id, string $username, array $input, string $mode = 'create'): array
    {
        $headerId = trim((string) ($input['beli_id'] ?? ''));
        $tanggal = trim((string) ($input['tanggal'] ?? ''));
        $supco = trim((string) ($input['supco'] ?? ''));
        $invoice = trim((string) ($input['invoice'] ?? ''));
        $statusNota = strtoupper(trim((string) ($input['status_nota'] ?? 'PO')));
        $keterangan = trim((string) ($input['keterangan'] ?? ''));
        $jatuhTempo = trim((string) ($input['jatuh_tempo'] ?? ''));
        $detailRows = json_decode((string) ($input['detail_json'] ?? '[]'), true) ?: [];
        $paymentRows = json_decode((string) ($input['payment_json'] ?? '[]'), true) ?: [];
        $deletedPaymentIds = json_decode((string) ($input['deleted_payment_ids'] ?? '[]'), true) ?: [];

        if ($tanggal === '' || $supco === '' || $invoice === '') {
            return ['tipe' => 'error', 'data' => 'Tanggal, supplier, dan invoice wajib diisi'];
        }
        if (!in_array($statusNota, ['PO', 'TERIMA'], true)) {
            return ['tipe' => 'error', 'data' => 'Status nota tidak valid'];
        }
        if (empty($detailRows)) {
            return ['tipe' => 'error', 'data' => 'Minimal 1 item pembelian harus diisi'];
        }

        $sanitizedDetails = [];
        $seq = 1;
        $totalGross = 0;
        foreach ($detailRows as $row) {
            $kodeItem = trim((string) ($row['kode_item'] ?? ''));
            $qtyBeli = (float) ($row['qty_beli'] ?? 0);
            $satId = trim((string) ($row['sat_id'] ?? ''));
            $qtyKonversi = (float) ($row['qty_konversi'] ?? 0);
            $price = (float) ($row['price'] ?? 0);
            $gross = (float) ($row['gross'] ?? 0);

            if ($kodeItem === '' || $satId === '' || $qtyBeli <= 0 || $qtyKonversi <= 0) {
                return ['tipe' => 'error', 'data' => 'Ada baris item yang belum lengkap'];
            }

            $gross = $gross > 0 ? $gross : round($qtyBeli * $price, 2);
            $price = $price > 0 ? $price : round($gross / max($qtyBeli, 1), 2);
            if ($gross <= 0 || $price <= 0) {
                return ['tipe' => 'error', 'data' => 'Price dan gross harus lebih besar dari nol'];
            }

            $qtyStock = round($qtyBeli * $qtyKonversi, 4);
            $totalGross += $gross;
            $sanitizedDetails[] = [
                'toko_id' => $toko_id,
                'beli_id' => '',
                'seq_no' => $seq++,
                'kode_item' => $kodeItem,
                'qty_beli' => $qtyBeli,
                'sat_id' => $satId,
                'qty_konversi' => $qtyKonversi,
                'qty_stock' => $qtyStock,
                'price' => round($price, 2),
                'gross' => round($gross, 2),
            ];
        }

        if ($statusNota === 'PO') {
            $paymentRows = [];
            $jatuhTempo = '';
        }

        $sanitizedPayments = [];
        $totalBayarInput = 0;
        foreach ($paymentRows as $row) {
            $caraBayar = strtoupper(trim((string) ($row['cara_bayar'] ?? '')));
            $jumlahBayar = (float) ($row['jumlah_bayar'] ?? 0);
            $bankNama = trim((string) ($row['bank_nama'] ?? ''));
            $rekeningNo = trim((string) ($row['rekening_no'] ?? ''));
            $tanggalBayar = trim((string) ($row['tanggal_bayar'] ?? ''));

            if (!in_array($caraBayar, ['TUNAI', 'TRANSFER'], true) || $jumlahBayar <= 0) {
                return ['tipe' => 'error', 'data' => 'Data pembayaran tidak valid'];
            }
            if ($caraBayar === 'TRANSFER' && ($bankNama === '' || $rekeningNo === '')) {
                return ['tipe' => 'error', 'data' => 'Transfer wajib mengisi nama bank dan nomor rekening'];
            }
            $totalBayarInput += $jumlahBayar;
            $sanitizedPayments[] = [
                'tanggal_bayar' => $tanggalBayar !== '' ? $tanggalBayar : date('Y-m-d H:i:s'),
                'cara_bayar' => $caraBayar,
                'jumlah_bayar' => round($jumlahBayar, 2),
                'bank_nama' => $caraBayar === 'TRANSFER' ? $bankNama : null,
                'rekening_no' => $caraBayar === 'TRANSFER' ? $rekeningNo : null,
            ];
        }

        if ($mode === 'create') {
            $headerId = $this->getNextId($toko_id);
        } elseif ($headerId === '') {
            return ['tipe' => 'error', 'data' => 'ID pembelian tidak valid'];
        }

        $existing = null;
        $existingPaymentsTotal = 0;
        $closingDate = $this->getClosingDate($toko_id);
        if ($mode !== 'create') {
            $existing = $this->db->query(
                "SELECT * FROM pembelian WHERE toko_id=:toko_id: AND beli_id=:beli_id:",
                ['toko_id' => $toko_id, 'beli_id' => $headerId]
            )->getRowArray();
            if (!$existing) {
                return ['tipe' => 'error', 'data' => 'Data pembelian tidak ditemukan'];
            }

            if (($existing['status_nota'] ?? '') === 'TERIMA' && ($existing['tanggal'] ?? '') < $closingDate) {
                return ['tipe' => 'error', 'data' => 'Transaksi TERIMA yang sudah melewati periode closing tidak boleh diedit'];
            }

            $paymentRowsExisting = $this->db->query(
                "SELECT bayar_id, jumlah_bayar
                 FROM pembelian_pembayaran
                 WHERE toko_id=:toko_id: AND beli_id=:beli_id:",
                ['toko_id' => $toko_id, 'beli_id' => $headerId]
            )->getResultArray();
            foreach ($paymentRowsExisting as $paymentRow) {
                if (in_array((int) $paymentRow['bayar_id'], array_map('intval', $deletedPaymentIds), true)) {
                    continue;
                }
                $existingPaymentsTotal += (float) ($paymentRow['jumlah_bayar'] ?? 0);
            }

            if (($existing['status_nota'] ?? '') === 'PO' && $statusNota === 'TERIMA') {
                $tanggal = date('Y-m-d');
            }

            if ($statusNota === 'PO' && $existingPaymentsTotal > 0) {
                return ['tipe' => 'error', 'data' => 'Tidak bisa mengubah ke PO karena pembayaran sudah ada'];
            }
            if ($totalGross + 0.0001 < $existingPaymentsTotal) {
                return ['tipe' => 'error', 'data' => 'Total gross baru lebih kecil dari total pembayaran yang sudah tercatat'];
            }
        }

        $totalBayarEffective = $totalBayarInput + $existingPaymentsTotal;
        if ($totalBayarEffective > $totalGross) {
            return ['tipe' => 'error', 'data' => 'Total pembayaran tidak boleh melebihi total gross'];
        }

        $isKredit = $statusNota === 'TERIMA' && ($totalGross - $totalBayarEffective) > 0.0001 ? 1 : 0;
        if ($isKredit) {
            if ($jatuhTempo === '') {
                return ['tipe' => 'error', 'data' => 'Tanggal jatuh tempo wajib diisi untuk pembelian kredit'];
            }
            if ($jatuhTempo < date('Y-m-d')) {
                return ['tipe' => 'error', 'data' => 'Tanggal jatuh tempo tidak boleh mundur'];
            }
        } else {
            $jatuhTempo = null;
        }

        $this->db->transStart();

        $headerData = [
            'toko_id' => $toko_id,
            'beli_id' => $headerId,
            'tanggal' => $tanggal,
            'supco' => $supco,
            'invoice' => $invoice,
            'total_gross' => round($totalGross, 2),
            'total_bayar' => 0,
            'sisa_bayar' => round($totalGross, 2),
            'is_kredit' => $isKredit,
            'status_nota' => $statusNota,
            'status_bayar' => 'BELUM',
            'jatuh_tempo' => $jatuhTempo,
            'username' => $username,
            'keterangan' => $keterangan !== '' ? $keterangan : null,
        ];

        if ($mode === 'create') {
            $this->db->table('pembelian')->insert($headerData);
        } else {
            $this->db->table('pembelian')
                ->where('toko_id', $toko_id)
                ->where('beli_id', $headerId)
                ->update($headerData);
            $this->db->table('pembelian_detail')
                ->where('toko_id', $toko_id)
                ->where('beli_id', $headerId)
                ->delete();
        }

        if (!empty($deletedPaymentIds)) {
            $this->db->table('pembelian_pembayaran')
                ->where('toko_id', $toko_id)
                ->where('beli_id', $headerId)
                ->whereIn('bayar_id', array_map('intval', $deletedPaymentIds))
                ->delete();
        }

        foreach ($sanitizedDetails as $row) {
            $row['beli_id'] = $headerId;
            $this->db->table('pembelian_detail')->insert($row);
        }

        if (!empty($sanitizedPayments)) {
            foreach ($sanitizedPayments as $payment) {
                $this->db->table('pembelian_pembayaran')->insert([
                    'toko_id' => $toko_id,
                    'beli_id' => $headerId,
                    'tanggal_bayar' => $payment['tanggal_bayar'],
                    'cara_bayar' => $payment['cara_bayar'],
                    'jumlah_bayar' => $payment['jumlah_bayar'],
                    'bank_nama' => $payment['bank_nama'],
                    'rekening_no' => $payment['rekening_no'],
                    'username' => $username,
                ]);
            }
        }

        $this->syncPaymentSummary($toko_id, $headerId);
        if ($statusNota === 'TERIMA') {
            $this->applyStoreCostUpdates($toko_id, $headerId, $tanggal, $supco, $sanitizedDetails);
        }
        if ($statusNota === 'TERIMA' || (($existing['status_nota'] ?? '') === 'TERIMA')) {
            HitungStock($toko_id);
        }
        $this->db->transComplete();

        if (!$this->db->transStatus()) {
            return ['tipe' => 'error', 'data' => 'Gagal menyimpan transaksi pembelian'];
        }

        return [
            'tipe' => 'success',
            'data' => 'Transaksi pembelian berhasil disimpan',
            'beli_id' => $headerId,
        ];
    }

    public function deletePurchase(string $toko_id, string $beli_id): array
    {
        $header = $this->db->query(
            "SELECT * FROM pembelian WHERE toko_id=:toko_id: AND beli_id=:beli_id:",
            ['toko_id' => $toko_id, 'beli_id' => $beli_id]
        )->getRowArray();

        if (!$header) {
            return ['tipe' => 'error', 'data' => 'Data pembelian tidak ditemukan'];
        }

        $this->db->transStart();
        $this->db->table('pembelian')
            ->where('toko_id', $toko_id)
            ->where('beli_id', $beli_id)
            ->delete();
        if (($header['status_nota'] ?? '') === 'TERIMA') {
            HitungStock($toko_id);
        }
        $this->db->transComplete();

        if (!$this->db->transStatus()) {
            return ['tipe' => 'error', 'data' => 'Gagal menghapus transaksi pembelian'];
        }

        return ['tipe' => 'success', 'data' => 'Transaksi pembelian berhasil dihapus'];
    }

    public function deleteSaldoHutangAwal(string $toko_id, string $beli_id): array
    {
        $header = $this->db->query(
            "SELECT * FROM pembelian WHERE toko_id=:toko_id: AND beli_id=:beli_id:",
            ['toko_id' => $toko_id, 'beli_id' => $beli_id]
        )->getRowArray();

        if (!$header || !$this->isSaldoHutangId($header['beli_id'] ?? null)) {
            return ['tipe' => 'error', 'data' => 'Data saldo hutang awal tidak ditemukan'];
        }

        $this->db->transStart();
        $this->db->table('pembelian')
            ->where('toko_id', $toko_id)
            ->where('beli_id', $beli_id)
            ->delete();
        $this->db->transComplete();

        if (!$this->db->transStatus()) {
            return ['tipe' => 'error', 'data' => 'Gagal menghapus saldo hutang awal'];
        }

        return ['tipe' => 'success', 'data' => 'Saldo hutang awal berhasil dihapus'];
    }

    public function addPayment(string $toko_id, string $beli_id, string $username, array $payments): array
    {
        $header = $this->db->query(
            "SELECT * FROM pembelian WHERE toko_id=:toko_id: AND beli_id=:beli_id:",
            ['toko_id' => $toko_id, 'beli_id' => $beli_id]
        )->getRowArray();

        if (!$header) {
            return ['tipe' => 'error', 'data' => 'Data pembelian tidak ditemukan'];
        }
        if (($header['status_nota'] ?? '') !== 'TERIMA') {
            return ['tipe' => 'error', 'data' => 'Pembayaran hanya bisa ditambahkan pada nota TERIMA'];
        }

        $sanitized = [];
        $incoming = 0;
        foreach ($payments as $row) {
            $caraBayar = strtoupper(trim((string) ($row['cara_bayar'] ?? '')));
            $jumlahBayar = (float) ($row['jumlah_bayar'] ?? 0);
            $bankNama = trim((string) ($row['bank_nama'] ?? ''));
            $rekeningNo = trim((string) ($row['rekening_no'] ?? ''));
            $tanggalBayar = trim((string) ($row['tanggal_bayar'] ?? ''));

            if (!in_array($caraBayar, ['TUNAI', 'TRANSFER'], true) || $jumlahBayar <= 0) {
                return ['tipe' => 'error', 'data' => 'Data cicilan tidak valid'];
            }
            if ($caraBayar === 'TRANSFER' && ($bankNama === '' || $rekeningNo === '')) {
                return ['tipe' => 'error', 'data' => 'Pembayaran transfer wajib mengisi bank dan rekening'];
            }
            $incoming += $jumlahBayar;
            $sanitized[] = [
                'tanggal_bayar' => $tanggalBayar !== '' ? $tanggalBayar : date('Y-m-d H:i:s'),
                'cara_bayar' => $caraBayar,
                'jumlah_bayar' => round($jumlahBayar, 2),
                'bank_nama' => $caraBayar === 'TRANSFER' ? $bankNama : null,
                'rekening_no' => $caraBayar === 'TRANSFER' ? $rekeningNo : null,
            ];
        }

        if (empty($sanitized)) {
            return ['tipe' => 'error', 'data' => 'Minimal satu cicilan harus diisi'];
        }

        $remaining = (float) ($header['sisa_bayar'] ?? 0);
        if ($incoming - $remaining > 0.0001) {
            return ['tipe' => 'error', 'data' => 'Total cicilan melebihi sisa hutang'];
        }

        $this->db->transStart();
        foreach ($sanitized as $payment) {
            $this->db->table('pembelian_pembayaran')->insert([
                'toko_id' => $toko_id,
                'beli_id' => $beli_id,
                'tanggal_bayar' => $payment['tanggal_bayar'],
                'cara_bayar' => $payment['cara_bayar'],
                'jumlah_bayar' => $payment['jumlah_bayar'],
                'bank_nama' => $payment['bank_nama'],
                'rekening_no' => $payment['rekening_no'],
                'username' => $username,
            ]);
        }
        $this->syncPaymentSummary($toko_id, $beli_id, false);
        $this->db->transComplete();

        if (!$this->db->transStatus()) {
            return ['tipe' => 'error', 'data' => 'Gagal menyimpan cicilan'];
        }

        return ['tipe' => 'success', 'data' => 'Pembayaran hutang berhasil disimpan'];
    }

    private function syncPaymentSummary(string $toko_id, string $beli_id, bool $update_status_kredit = true): void
    {
        $header = $this->db->query(
            "SELECT total_gross, status_nota
             FROM pembelian
             WHERE toko_id=:toko_id: AND beli_id=:beli_id:",
            ['toko_id' => $toko_id, 'beli_id' => $beli_id]
        )->getRowArray();

        if (!$header) {
            return;
        }

        $payRow = $this->db->query(
            "SELECT COALESCE(SUM(jumlah_bayar),0) AS total_bayar
             FROM pembelian_pembayaran
             WHERE toko_id=:toko_id: AND beli_id=:beli_id:",
            ['toko_id' => $toko_id, 'beli_id' => $beli_id]
        )->getRowArray();

        $totalGross = (float) ($header['total_gross'] ?? 0);
        $totalBayar = (float) ($payRow['total_bayar'] ?? 0);
        $sisaBayar = max($totalGross - $totalBayar, 0);
        $statusNota = $header['status_nota'] ?? 'PO';
        $isKredit = 0;
        if ($statusNota === 'PO') {
            $statusBayar = 'BELUM';
            $isKredit = 0;
        } elseif ($sisaBayar <= 0.0001) {
            $statusBayar = 'LUNAS';
            $isKredit = $update_status_kredit ? 0 : 1;
        } elseif ($totalBayar > 0) {
            $statusBayar = 'CICIL';
            $isKredit = 1;
        } else {
            $statusBayar = 'BELUM';
            $isKredit = 1;
        }
        $dataUpdate = [
            'total_bayar'  => round($totalBayar, 2),
            'sisa_bayar'   => round($sisaBayar, 2),
            'status_bayar' => $statusBayar,
            'is_kredit'    => $isKredit
        ];

        $this->db->table('pembelian')
            ->where('toko_id', $toko_id)
            ->where('beli_id', $beli_id)
            ->update($dataUpdate);
    }

    private function applyStoreCostUpdates(string $toko_id, string $beli_id, string $tanggal, string $supco, array $details): void
    {
        $cekgram = $this->db->query("SELECT * FROM CONST WHERE rkey='satuan_gramasi'")->getRow();
        $satGramasiRaw = $cekgram->nilai ?? "GR;GRAM;ML";
        $satuanGramasi = explode(';', $satGramasiRaw);
        foreach ($details as $detail) {
            $qtyKonversiBeli = (float) ($detail['qty_konversi'] ?? 0);
            $hargaPokokBeli = (float) ($detail['price'] ?? 0);
            if ($qtyKonversiBeli <= 0 || $hargaPokokBeli <= 0) {
                continue;
            }

            $hargaDasar = $hargaPokokBeli / $qtyKonversiBeli;
            $stores = $this->db->query(
                "SELECT ps.sat_id, ps.qty_konversi, store.harga_pokok, store.harga_jual, store.target_psn_margin
                 FROM prodmast_satuan ps
                 INNER JOIN prodmast_store store
                    ON store.kode_item=ps.kode_item
                    AND store.sat_id=ps.sat_id
                    AND store.toko_id=:toko_id:
                 WHERE ps.kode_item=:kode_item:
                 ORDER BY ps.qty_konversi, ps.sat_id",
                [
                    'toko_id' => $toko_id,
                    'kode_item' => $detail['kode_item'],
                ]
            )->getResultArray();

            if (!$stores) {
                continue;
            }

            $this->db->table('prodmast_store')
                ->where('toko_id', $toko_id)
                ->where('kode_item', $detail['kode_item'])
                ->update([
                    'supco' => $supco !== '' ? $supco : null,
                    'updid' => $beli_id,
                    'updtime' => date('Y-m-d H:i:s'),
                ]);

            foreach ($stores as $store) {
                $hargaPokokOld = (float) ($store['harga_pokok'] ?? 0);
                $hargaJualOld = (float) ($store['harga_jual'] ?? 0);
                $targetMargin = (float) ($store['target_psn_margin'] ?? 0);
                $qtyKonversiSat = (float) ($store['qty_konversi'] ?? 0);
                $hargaPokokNew = round($hargaDasar * $qtyKonversiSat, 2);
                $hargaJualNew = $hargaJualOld;

                if ($hargaPokokNew > $hargaPokokOld) {
                    $hargaJualbase = round($hargaPokokNew + ($hargaPokokNew * $targetMargin / 100));
                    if (!in_array(strtoupper($store['sat_id']), $satuanGramasi)) {
                        $hargaJualNew = ceil($hargaJualbase / 50) * 50;
                    } else {
                        $hargaJualNew = $hargaJualbase;
                    }
                }

                $this->db->table('prodmast_store')
                    ->where('toko_id', $toko_id)
                    ->where('kode_item', $detail['kode_item'])
                    ->where('sat_id', $store['sat_id'])
                    ->update([
                        'harga_pokok' => $hargaPokokNew,
                        'harga_jual' => $hargaJualNew,
                        'last_beli' => $tanggal,
                        'updid' => $beli_id,
                        'updtime' => date('Y-m-d H:i:s')
                    ]);

                if (abs($hargaPokokNew - $hargaPokokOld) > 0.0001) {
                    $this->db->table('history_harga_beli')->insert([
                        'toko_id' => $toko_id,
                        'beli_id' => $beli_id,
                        'kode_item' => $detail['kode_item'],
                        'sat_id' => $store['sat_id'],
                        'harga_pokok_old' => $hargaPokokOld,
                        'harga_pokok_new' => $hargaPokokNew,
                        'harga_jual_old' => $hargaJualOld,
                        'harga_jual_new' => $hargaJualNew,
                    ]);
                }
            }
        }
    }

    public function getClosingDate(string $toko_id): string
    {
        return GetClosingDateByToko($toko_id);
    }

    public function isLockedTerima(string $toko_id, string $beli_id): bool
    {
        $row = $this->db->query(
            "SELECT status_nota, tanggal FROM pembelian WHERE toko_id=:toko_id: AND beli_id=:beli_id:",
            ['toko_id' => $toko_id, 'beli_id' => $beli_id]
        )->getRowArray();

        if (!$row) {
            return false;
        }

        return ($row['status_nota'] ?? '') === 'TERIMA' && ($row['tanggal'] ?? '') < $this->getClosingDate($toko_id);
    }

    public function isSaldoHutangId(?string $beli_id): bool
    {
        return is_string($beli_id) && str_starts_with($beli_id, 'SH');
    }
}
