<?php

namespace App\Models;

use CodeIgniter\Model;

class ReturBeliModel extends Model
{
    protected $table = 'pembelian_retur';
    protected $returnType = 'array';
    protected $protectFields = false;

    public function getNextId(string $toko_id): string
    {
        $row = $this->db->query(
            "SELECT MAX(CAST(RIGHT(retur_id,9) AS UNSIGNED)) AS nomor
             FROM pembelian_retur
             WHERE toko_id=:toko_id: AND retur_id LIKE 'RB%'",
            ['toko_id' => $toko_id]
        )->getRowArray();

        return 'RB' . $toko_id . sprintf('%09d', ((int) ($row['nomor'] ?? 0)) + 1);
    }

    public function getClosingDate(string $toko_id): string
    {
        return GetClosingDateByToko($toko_id);
    }

    public function getEligiblePurchaseOptions(string $toko_id, ?string $includeBeliId = null): array
    {
        $binds = ['toko_id' => $toko_id];
        $where = "WHERE p.toko_id=:toko_id: AND p.status_nota='TERIMA' AND p.is_kredit=1 AND p.status_bayar <> 'LUNAS'";
        if ($includeBeliId) {
            $where .= " OR (p.toko_id=:toko_id: AND p.beli_id=:include_beli_id:)";
            $binds['include_beli_id'] = $includeBeliId;
        }

        return $this->db->query(
            "SELECT p.beli_id, p.tanggal, p.supco, p.invoice, p.total_gross, p.total_bayar, p.sisa_bayar, s.nama AS supplier_nama
             FROM pembelian p
             LEFT JOIN supmast s ON s.supco=p.supco
             $where
             ORDER BY p.tanggal DESC, p.beli_id DESC",
            $binds
        )->getResultArray();
    }

    public function getSupplierOptions(string $toko_id, ?string $includeSupco = null): array
    {
        $binds = ['toko_id' => $toko_id];
        $where = "WHERE p.toko_id=:toko_id: AND p.status_nota='TERIMA'";
        if ($includeSupco) {
            $where .= " OR (p.toko_id=:toko_id: AND p.supco=:include_supco:)";
            $binds['include_supco'] = $includeSupco;
        }

        return $this->db->query(
            "SELECT p.supco, COALESCE(s.nama, p.supco) AS supplier_nama, COUNT(DISTINCT p.beli_id) AS jml_faktur
             FROM pembelian p
             LEFT JOIN supmast s ON s.supco=p.supco
             $where
             GROUP BY p.supco, s.nama
             ORDER BY COALESCE(s.nama, p.supco), p.supco",
            $binds
        )->getResultArray();
    }

    public function getEligibleDebtOptions(string $toko_id, string $supco, ?string $includeBeliId = null, float $ownAddback = 0): array
    {
        if ($supco === '') {
            return [];
        }

        $binds = [
            'toko_id' => $toko_id,
            'supco' => $supco,
        ];
        $where = "WHERE p.toko_id=:toko_id: AND p.supco=:supco: AND p.status_nota='TERIMA' AND p.is_kredit=1 AND p.status_bayar <> 'LUNAS'";
        if ($includeBeliId) {
            $where .= " OR (p.toko_id=:toko_id: AND p.beli_id=:include_beli_id:)";
            $binds['include_beli_id'] = $includeBeliId;
        }

        $rows = $this->db->query(
            "SELECT p.beli_id, p.tanggal, p.invoice, p.total_gross, p.total_bayar, p.sisa_bayar, p.status_bayar
             FROM pembelian p
             $where
             ORDER BY p.tanggal DESC, p.beli_id DESC",
            $binds
        )->getResultArray();

        foreach ($rows as &$row) {
            $row['sisa_bayar_form'] = round((float) ($row['sisa_bayar'] ?? 0) + (($includeBeliId && (string) $row['beli_id'] === $includeBeliId) ? $ownAddback : 0), 2);
        }
        unset($row);

        return $rows;
    }

    public function ajaxList(array $params, string $toko_id): array
    {
        $start = (int) ($params['start'] ?? 0);
        $length = $params['length'] ?? 25;
        $search = trim((string) ($params['search_value'] ?? ''));
        $queryLimit = $length !== '-1' ? " LIMIT $start, " . (int) $length : '';

        $binds = ['toko_id' => $toko_id];
        $where = " WHERE r.toko_id=:toko_id: ";
        if ($search !== '') {
            $where .= " AND (r.retur_id LIKE :search: OR r.beli_id LIKE :search: OR p.invoice LIKE :search: OR r.supco LIKE :search: OR s.nama LIKE :search: OR r.settlement_mode LIKE :search:)";
            $binds['search'] = '%' . $this->db->escapeLikeString($search) . '%';
        }

        $totalRow = $this->db->query(
            "SELECT COUNT(*) total FROM pembelian_retur WHERE toko_id=:toko_id:",
            ['toko_id' => $toko_id]
        )->getRowArray();

        $filtered = $totalRow['total'] ?? 0;
        if ($search !== '') {
            $filteredRow = $this->db->query(
                "SELECT COUNT(*) total
                 FROM pembelian_retur r
                 LEFT JOIN pembelian p ON p.toko_id=r.toko_id AND p.beli_id=r.beli_id
                 LEFT JOIN supmast s ON s.supco=r.supco
                 $where",
                $binds
            )->getRowArray();
            $filtered = $filteredRow['total'] ?? 0;
        }

        $closingDate = $this->getClosingDate($toko_id);
        $data = $this->db->query(
            "SELECT r.*, p.invoice, p.sisa_bayar, p.total_gross, s.nama AS supplier_nama, COUNT(rd.seq_no) AS jml_item
             FROM pembelian_retur r
             LEFT JOIN pembelian p ON p.toko_id=r.toko_id AND p.beli_id=r.beli_id
             LEFT JOIN supmast s ON s.supco=r.supco
             LEFT JOIN pembelian_retur_detail rd ON rd.toko_id=r.toko_id AND rd.retur_id=r.retur_id
             $where
             GROUP BY r.toko_id, r.retur_id
             ORDER BY r.tanggal DESC, r.updtime DESC, r.retur_id DESC
             $queryLimit",
            $binds
        )->getResultArray();

        foreach ($data as &$row) {
            $row['can_edit'] = !(($row['status_retur'] ?? '') === 'SELESAI' && ($row['tanggal'] ?? '') < $closingDate);
            $row['closing_date'] = $closingDate;
        }

        return [
            'data' => $data,
            'total_count' => (int) ($totalRow['total'] ?? 0),
            'total_filtered' => (int) $filtered,
        ];
    }

    public function getFormData(string $toko_id, ?string $retur_id = null): array
    {
        $data = [
            'header' => [
                'retur_id' => $retur_id ? '' : $this->getNextId($toko_id),
                'supco' => '',
                'beli_id' => '',
                'tanggal' => date('Y-m-d'),
                'status_retur' => 'DRAFT',
                'settlement_mode' => 'POTONG_HUTANG',
                'keterangan' => '',
                'total_retur' => 0,
            ],
            'supplier' => null,
            'debt_options' => [],
            'details' => [],
        ];

        if (!$retur_id) {
            return $data;
        }

        $header = $this->db->query(
            "SELECT * FROM pembelian_retur WHERE toko_id=:toko_id: AND retur_id=:retur_id:",
            ['toko_id' => $toko_id, 'retur_id' => $retur_id]
        )->getRowArray();
        if (!$header) {
            return $data;
        }

        $sourcePayload = $this->getSupplierItemPayload($toko_id, (string) ($header['supco'] ?? ''), $retur_id, ($header['status_retur'] ?? '') === 'SELESAI', (string) ($header['beli_id'] ?? ''));
        if (!$sourcePayload) {
            return $data;
        }

        $detailRows = $this->db->query(
            "SELECT * FROM pembelian_retur_detail WHERE toko_id=:toko_id: AND retur_id=:retur_id: ORDER BY seq_no",
            ['toko_id' => $toko_id, 'retur_id' => $retur_id]
        )->getResultArray();
        $byItem = [];
        foreach ($detailRows as $row) {
            $byItem[$row['kode_item']] = $row;
        }

        $details = [];
        foreach ($detailRows as $saved) {
            $itemPayload = $this->getItemPayload($toko_id, (string) ($saved['kode_item'] ?? ''));
            if (!$itemPayload) {
                continue;
            }

            $selectedSatuan = null;
            foreach (($itemPayload['satuan'] ?? []) as $option) {
                if ((string) ($option['sat_id'] ?? '') === (string) ($saved['sat_id'] ?? '')) {
                    $selectedSatuan = $option;
                    break;
                }
            }
            $selectedSatuan = $selectedSatuan ?? (($itemPayload['satuan'][0] ?? null));
            if (!$selectedSatuan) {
                continue;
            }

            $qtyKonversi = (float) ($selectedSatuan['qty_konversi'] ?? 1);
            $basePriceUnit = round(((float) ($selectedSatuan['harga_pokok'] ?? 0)) / max($qtyKonversi, 1), 4);
            $details[] = [
                'kode_item' => $itemPayload['kode_item'],
                'barcode' => $itemPayload['barcode'] ?? '',
                'nama_item' => $itemPayload['nama_item'] ?? $itemPayload['kode_item'],
                'satuan_options' => $itemPayload['satuan'] ?? [],
                'source_sat_id' => $selectedSatuan['sat_id'],
                'source_qty_konversi' => $qtyKonversi,
                'source_price' => (float) ($selectedSatuan['harga_pokok'] ?? 0),
                'base_price_unit' => $basePriceUnit,
                'stok_aktual' => (float) ($itemPayload['stok_aktual'] ?? 0),
                'qty_retur' => (float) ($saved['qty_retur'] ?? 0),
                'sat_id' => $saved['sat_id'],
                'qty_konversi' => (float) ($saved['qty_konversi'] ?? $qtyKonversi),
                'qty_stok' => (float) ($saved['qty_stok'] ?? 0),
                'price' => (float) ($saved['price'] ?? 0),
                'gross_retur' => (float) ($saved['gross_retur'] ?? 0),
            ];
        }

        return [
            'header' => $header,
            'supplier' => $sourcePayload['header'],
            'debt_options' => $sourcePayload['debt_options'],
            'details' => $details,
        ];
    }

    public function searchItems(string $toko_id, string $term): array
    {
        $search = '%' . $this->db->escapeLikeString($term) . '%';
        return $this->db->query(
            "SELECT p.kode_item, p.barcode, p.nama_item,
                    COALESCE(base.sat_id, '-') AS sat_dasar
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
            "SELECT p.kode_item, p.barcode, p.nama_item
             FROM prodmast p
             WHERE p.kode_item=:kode_item:",
            ['kode_item' => $kode_item]
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

        $stok = $this->db->query(
            "SELECT COALESCE(qty,0) AS qty_stok FROM stmast WHERE toko_id=:toko_id: AND kode_item=:kode_item: LIMIT 1",
            ['toko_id' => $toko_id, 'kode_item' => $kode_item]
        )->getRowArray();

        $item['satuan'] = $satuan;
        $item['base_sat_id'] = $satuan[0]['sat_id'];
        $item['stok_aktual'] = (float) ($stok['qty_stok'] ?? 0);
        return $item;
    }

    public function getSupplierItemPayload(string $toko_id, string $supco, ?string $excludeReturId = null, bool $addBackOwnCompleted = false, ?string $selectedBeliId = null): ?array
    {
        if ($supco === '') {
            return null;
        }

        $supplier = $this->db->query(
            "SELECT supco, nama AS supplier_nama
             FROM supmast
             WHERE supco=:supco:
             LIMIT 1",
            ['supco' => $supco]
        )->getRowArray();

        $ownTotalAddback = 0;
        if ($excludeReturId && $addBackOwnCompleted) {
            $ownRows = $this->db->query(
                "SELECT rd.gross_retur
                 FROM pembelian_retur_detail rd
                 INNER JOIN pembelian_retur r ON r.toko_id=rd.toko_id AND r.retur_id=rd.retur_id
                 WHERE r.toko_id=:toko_id: AND r.retur_id=:retur_id: AND r.status_retur='SELESAI'",
                ['toko_id' => $toko_id, 'retur_id' => $excludeReturId]
            )->getResultArray();
            foreach ($ownRows as $row) {
                $ownTotalAddback += (float) ($row['gross_retur'] ?? 0);
            }
        }

        $debtOptions = $this->getEligibleDebtOptions($toko_id, $supco, $selectedBeliId, $ownTotalAddback);
        $totalOutstanding = array_reduce($debtOptions, static fn($carry, $item) => $carry + (float) ($item['sisa_bayar_form'] ?? 0), 0.0);

        return [
            'header' => [
                'supco' => $supco,
                'supplier_nama' => $supplier['supplier_nama'] ?? $supco,
                'total_outstanding_debt' => round($totalOutstanding, 2),
            ],
            'debt_options' => $debtOptions,
            'details' => [],
        ];
    }

    public function getSourcePurchasePayload(string $toko_id, string $beli_id, ?string $excludeReturId = null, bool $addBackOwnCompleted = false): ?array
    {
        $header = $this->db->query(
            "SELECT p.*, s.nama AS supplier_nama
             FROM pembelian p
             LEFT JOIN supmast s ON s.supco=p.supco
             WHERE p.toko_id=:toko_id: AND p.beli_id=:beli_id: AND p.status_nota='TERIMA'",
            ['toko_id' => $toko_id, 'beli_id' => $beli_id]
        )->getRowArray();
        if (!$header) {
            return null;
        }

        $sourceDetails = $this->db->query(
            "SELECT pd.*, p.nama_item, p.barcode, COALESCE(st.qty,0) AS stok_aktual
             FROM pembelian_detail pd
             LEFT JOIN prodmast p ON p.kode_item=pd.kode_item
             LEFT JOIN stmast st ON st.toko_id=pd.toko_id AND st.kode_item=pd.kode_item
             WHERE pd.toko_id=:toko_id: AND pd.beli_id=:beli_id:
             ORDER BY pd.seq_no",
            ['toko_id' => $toko_id, 'beli_id' => $beli_id]
        )->getResultArray();

        if (!$sourceDetails) {
            return null;
        }

        $returnedMap = [];
        $returnedRows = $this->db->query(
            "SELECT rd.kode_item, COALESCE(SUM(rd.qty_stok),0) AS qty_stok
             FROM pembelian_retur_detail rd
             INNER JOIN pembelian_retur r ON r.toko_id=rd.toko_id AND r.retur_id=rd.retur_id
             WHERE r.toko_id=:toko_id: AND r.beli_id=:beli_id: AND r.status_retur='SELESAI'
             " . ($excludeReturId ? " AND r.retur_id <> :exclude_retur_id: " : "") . "
             GROUP BY rd.kode_item",
            array_filter([
                'toko_id' => $toko_id,
                'beli_id' => $beli_id,
                'exclude_retur_id' => $excludeReturId,
            ], static fn($v) => $v !== null)
        )->getResultArray();
        foreach ($returnedRows as $row) {
            $returnedMap[$row['kode_item']] = (float) ($row['qty_stok'] ?? 0);
        }

        $ownAppliedMap = [];
        $ownTotalAddback = 0;
        if ($excludeReturId && $addBackOwnCompleted) {
            $ownRows = $this->db->query(
                "SELECT rd.kode_item, rd.qty_stok, rd.gross_retur
                 FROM pembelian_retur_detail rd
                 INNER JOIN pembelian_retur r ON r.toko_id=rd.toko_id AND r.retur_id=rd.retur_id
                 WHERE r.toko_id=:toko_id: AND r.retur_id=:retur_id: AND r.status_retur='SELESAI'",
                ['toko_id' => $toko_id, 'retur_id' => $excludeReturId]
            )->getResultArray();
            foreach ($ownRows as $row) {
                $ownAppliedMap[$row['kode_item']] = (float) ($row['qty_stok'] ?? 0);
                $ownTotalAddback += (float) ($row['gross_retur'] ?? 0);
            }
        }

        foreach ($sourceDetails as &$row) {
            $satuanOptions = $this->db->query(
                "SELECT ps.sat_id, ps.qty_konversi
                 FROM prodmast_satuan ps
                 WHERE ps.kode_item=:kode_item:
                 ORDER BY ps.qty_konversi, ps.sat_id",
                ['kode_item' => $row['kode_item']]
            )->getResultArray();

            $returnedStock = $returnedMap[$row['kode_item']] ?? 0;
            $ownAppliedStock = $ownAppliedMap[$row['kode_item']] ?? 0;
            $row['satuan_options'] = $satuanOptions;
            $row['source_sat_id'] = $row['sat_id'];
            $row['source_qty_konversi'] = (float) $row['qty_konversi'];
            $row['source_price'] = (float) $row['price'];
            $row['base_price_unit'] = ((float) $row['price']) / max((float) $row['qty_konversi'], 1);
            $row['returned_qty_stock'] = $returnedStock;
            $row['max_qty_stock_source'] = max(((float) $row['qty_stock']) - $returnedStock, 0);
            $row['stok_aktual'] = ((float) $row['stok_aktual']) + $ownAppliedStock;
        }

        $header['sisa_bayar_form'] = ((float) ($header['sisa_bayar'] ?? 0)) + $ownTotalAddback;
        return [
            'header' => $header,
            'details' => $sourceDetails,
        ];
    }

    public function getReturSummary(string $toko_id, string $retur_id): ?array
    {
        $header = $this->db->query(
            "SELECT r.*, p.invoice, COALESCE(r.supco, p.supco) AS supco, s.nama AS supplier_nama
             FROM pembelian_retur r
             LEFT JOIN pembelian p ON p.toko_id=r.toko_id AND p.beli_id=r.beli_id
             LEFT JOIN supmast s ON s.supco=r.supco
             WHERE r.toko_id=:toko_id: AND r.retur_id=:retur_id:",
            ['toko_id' => $toko_id, 'retur_id' => $retur_id]
        )->getRowArray();
        if (!$header) {
            return null;
        }

        $header['details'] = $this->db->query(
            "SELECT rd.*, p.nama_item
             FROM pembelian_retur_detail rd
             LEFT JOIN prodmast p ON p.kode_item=rd.kode_item
             WHERE rd.toko_id=:toko_id: AND rd.retur_id=:retur_id:
             ORDER BY rd.seq_no",
            ['toko_id' => $toko_id, 'retur_id' => $retur_id]
        )->getResultArray();

        return $header;
    }

    public function saveRetur(string $toko_id, string $username, array $input, string $mode = 'create'): array
    {
        $returId = trim((string) ($input['retur_id'] ?? ''));
        $supco = trim((string) ($input['supco'] ?? ''));
        $beliId = trim((string) ($input['beli_id'] ?? ''));
        $tanggal = trim((string) ($input['tanggal'] ?? ''));
        $statusRetur = strtoupper(trim((string) ($input['status_retur'] ?? 'DRAFT')));
        $settlementMode = strtoupper(trim((string) ($input['settlement_mode'] ?? 'POTONG_HUTANG')));
        $keterangan = trim((string) ($input['keterangan'] ?? ''));
        $detailRows = json_decode((string) ($input['detail_json'] ?? '[]'), true) ?: [];

        if ($supco === '' || $tanggal === '') {
            return ['tipe' => 'error', 'data' => 'Supplier dan tanggal retur wajib diisi'];
        }
        if (!in_array($statusRetur, ['DRAFT', 'SELESAI'], true)) {
            return ['tipe' => 'error', 'data' => 'Status retur tidak valid'];
        }
        if (!in_array($settlementMode, ['POTONG_HUTANG', 'CASHBACK'], true)) {
            return ['tipe' => 'error', 'data' => 'Mode penyelesaian retur tidak valid'];
        }

        $existing = null;
        if ($mode === 'create') {
            $returId = $this->getNextId($toko_id);
        } else {
            if ($returId === '') {
                return ['tipe' => 'error', 'data' => 'ID retur tidak valid'];
            }
            $existing = $this->db->query(
                "SELECT * FROM pembelian_retur WHERE toko_id=:toko_id: AND retur_id=:retur_id:",
                ['toko_id' => $toko_id, 'retur_id' => $returId]
            )->getRowArray();
            if (!$existing) {
                return ['tipe' => 'error', 'data' => 'Data retur tidak ditemukan'];
            }
            if (($existing['status_retur'] ?? '') === 'SELESAI' && ($existing['tanggal'] ?? '') < $this->getClosingDate($toko_id)) {
                return ['tipe' => 'error', 'data' => 'Retur SELESAI yang sudah melewati periode closing tidak boleh diedit'];
            }
        }

        $addBackOwnCompleted = ($existing['status_retur'] ?? '') === 'SELESAI';
        $sourcePayload = $this->getSupplierItemPayload($toko_id, $supco, $returId !== '' ? $returId : null, $addBackOwnCompleted, $beliId !== '' ? $beliId : (($existing['beli_id'] ?? '') ?: null));
        if (!$sourcePayload) {
            return ['tipe' => 'error', 'data' => 'Supplier tidak memiliki histori pembelian TERIMA yang valid'];
        }

        $sanitizedDetails = [];
        $totalRetur = 0;
        $seq = 1;
        foreach ($detailRows as $row) {
            $qtyRetur = (float) ($row['qty_retur'] ?? 0);
            if ($qtyRetur <= 0) {
                continue;
            }

            $kodeItem = trim((string) ($row['kode_item'] ?? ''));
            $satId = trim((string) ($row['sat_id'] ?? ''));
            $itemPayload = $this->getItemPayload($toko_id, $kodeItem);
            if (!$itemPayload || $satId === '') {
                return ['tipe' => 'error', 'data' => 'Ada item retur yang tidak valid'];
            }

            $selectedSatuan = null;
            foreach (($itemPayload['satuan'] ?? []) as $option) {
                if ((string) ($option['sat_id'] ?? '') === $satId) {
                    $selectedSatuan = $option;
                    break;
                }
            }
            if (!$selectedSatuan) {
                return ['tipe' => 'error', 'data' => 'Satuan retur item ' . $kodeItem . ' tidak valid'];
            }

            $qtyKonversi = (float) ($selectedSatuan['qty_konversi'] ?? 0);
            if ($qtyKonversi <= 0) {
                return ['tipe' => 'error', 'data' => 'Konversi satuan item ' . $kodeItem . ' tidak valid'];
            }

            $qtyStock = round($qtyRetur * $qtyKonversi, 4);
            $basePriceUnit = round(((float) ($selectedSatuan['harga_pokok'] ?? 0)) / max($qtyKonversi, 1), 4);
            $price = round($basePriceUnit * $qtyKonversi, 2);
            $grossRetur = round($qtyRetur * $price, 2);
            $totalRetur += $grossRetur;

            $sanitizedDetails[] = [
                'toko_id' => $toko_id,
                'retur_id' => $returId,
                'seq_no' => $seq++,
                'kode_item' => $kodeItem,
                'qty_retur' => $qtyRetur,
                'sat_id' => $satId,
                'qty_konversi' => $qtyKonversi,
                'qty_stok' => $qtyStock,
                'price' => $price,
                'gross_retur' => $grossRetur,
            ];
        }

        if (empty($sanitizedDetails)) {
            return ['tipe' => 'error', 'data' => 'Minimal ada satu item dengan qty retur lebih besar dari nol'];
        }

        if ($statusRetur === 'SELESAI') {
            foreach ($sanitizedDetails as $row) {
                $currentDetail = $this->getItemPayload($toko_id, (string) $row['kode_item']);
                $maxByStock = (float) ($currentDetail['stok_aktual'] ?? 0);
                if ((float) $row['qty_stok'] - $maxByStock > 0.0001) {
                    return ['tipe' => 'error', 'data' => 'Stok item ' . $row['kode_item'] . ' berubah selama draft. Silakan cek ulang qty retur.'];
                }
            }

            if ($settlementMode === 'POTONG_HUTANG') {
                if ($beliId === '') {
                    return ['tipe' => 'error', 'data' => 'Faktur hutang target wajib dipilih jika retur diselesaikan dengan potong hutang'];
                }

                $debtOptions = $sourcePayload['debt_options'] ?? [];
                $selectedDebt = null;
                foreach ($debtOptions as $option) {
                    if ((string) ($option['beli_id'] ?? '') === $beliId) {
                        $selectedDebt = $option;
                        break;
                    }
                }
                if (!$selectedDebt) {
                    return ['tipe' => 'error', 'data' => 'Faktur hutang target tidak valid untuk supplier yang dipilih'];
                }

                $maxAllowedRetur = (float) ($selectedDebt['sisa_bayar_form'] ?? 0);
                if ($totalRetur - $maxAllowedRetur > 0.0001) {
                    return ['tipe' => 'error', 'data' => 'Total retur tidak boleh melebihi sisa hutang pada faktur yang dipilih'];
                }
            } else {
                if ($this->resolveKaryawanIdByUsername($username) === '') {
                    return ['tipe' => 'error', 'data' => 'User login tidak memiliki karyawan_id untuk pencatatan kas retur'];
                }
                $beliId = '';
            }
        } elseif ($settlementMode === 'CASHBACK') {
            $beliId = '';
        }

        $closingDate = $this->getClosingDate($toko_id);
        if ($statusRetur === 'SELESAI' && $tanggal < $closingDate) {
            $tanggal = date('Y-m-d');
        }

        $this->db->transStart();

        $headerData = [
            'toko_id' => $toko_id,
            'retur_id' => $returId,
            'supco' => $supco,
            'beli_id' => $beliId !== '' ? $beliId : null,
            'tanggal' => $tanggal,
            'total_retur' => round($totalRetur, 2),
            'settlement_mode' => $settlementMode,
            'status_retur' => $statusRetur,
            'username' => $username,
            'keterangan' => $keterangan !== '' ? $keterangan : null,
        ];

        if ($mode === 'create') {
            $this->db->table('pembelian_retur')->insert($headerData);
        } else {
            $this->db->table('pembelian_retur')
                ->where('toko_id', $toko_id)
                ->where('retur_id', $returId)
                ->update($headerData);
            $this->db->table('pembelian_retur_detail')
                ->where('toko_id', $toko_id)
                ->where('retur_id', $returId)
                ->delete();
        }

        foreach ($sanitizedDetails as $row) {
            $this->db->table('pembelian_retur_detail')->insert($row);
        }

        $this->syncReturSettlement(
            $toko_id,
            $returId,
            $tanggal,
            $statusRetur,
            $settlementMode,
            $beliId,
            round($totalRetur, 2),
            $username
        );
        if (($existing['status_retur'] ?? '') === 'SELESAI' || $statusRetur === 'SELESAI') {
            HitungStock($toko_id);
        }
        if ($beliId !== '') {
            $this->syncPurchasePaymentSummary($toko_id, $beliId);
        }
        if ($existing && ($existing['beli_id'] ?? '') !== '' && ($existing['beli_id'] ?? '') !== $beliId) {
            $this->syncPurchasePaymentSummary($toko_id, (string) $existing['beli_id']);
        }

        $this->db->transComplete();
        if (!$this->db->transStatus()) {
            return ['tipe' => 'error', 'data' => 'Gagal menyimpan retur pembelian'];
        }

        return [
            'tipe' => 'success',
            'data' => 'Retur pembelian berhasil disimpan',
            'retur_id' => $returId,
        ];
    }

    public function deleteRetur(string $toko_id, string $retur_id): array
    {
        $header = $this->db->query(
            "SELECT * FROM pembelian_retur WHERE toko_id=:toko_id: AND retur_id=:retur_id:",
            ['toko_id' => $toko_id, 'retur_id' => $retur_id]
        )->getRowArray();
        if (!$header) {
            return ['tipe' => 'error', 'data' => 'Data retur tidak ditemukan'];
        }
        if (($header['status_retur'] ?? '') === 'SELESAI' && ($header['tanggal'] ?? '') < $this->getClosingDate($toko_id)) {
            return ['tipe' => 'error', 'data' => 'Retur SELESAI yang sudah melewati periode closing tidak boleh dihapus'];
        }

        $this->db->transStart();
        $this->db->table('pembelian_pembayaran')
            ->where('toko_id', $toko_id)
            ->where('retur_id', $retur_id)
            ->delete();
        $this->deleteCashRefundMutation($toko_id, $retur_id);
        $this->db->table('pembelian_retur')
            ->where('toko_id', $toko_id)
            ->where('retur_id', $retur_id)
            ->delete();
        if (($header['status_retur'] ?? '') === 'SELESAI') {
            HitungStock($toko_id);
        }
        if (!empty($header['beli_id'])) {
            $this->syncPurchasePaymentSummary($toko_id, (string) $header['beli_id']);
        }
        $this->db->transComplete();

        if (!$this->db->transStatus()) {
            return ['tipe' => 'error', 'data' => 'Gagal menghapus retur pembelian'];
        }

        return ['tipe' => 'success', 'data' => 'Retur pembelian berhasil dihapus'];
    }

    public function isLockedRetur(string $toko_id, string $retur_id): bool
    {
        $row = $this->db->query(
            "SELECT status_retur, tanggal FROM pembelian_retur WHERE toko_id=:toko_id: AND retur_id=:retur_id:",
            ['toko_id' => $toko_id, 'retur_id' => $retur_id]
        )->getRowArray();

        if (!$row) {
            return false;
        }

        return ($row['status_retur'] ?? '') === 'SELESAI' && ($row['tanggal'] ?? '') < $this->getClosingDate($toko_id);
    }

    private function syncReturSettlement(string $toko_id, string $retur_id, string $tanggal, string $statusRetur, string $settlementMode, string $beli_id, float $totalRetur, string $username): void
    {
        if ($statusRetur !== 'SELESAI') {
            $this->db->table('pembelian_pembayaran')
                ->where('toko_id', $toko_id)
                ->where('retur_id', $retur_id)
                ->delete();
            $this->deleteCashRefundMutation($toko_id, $retur_id);
            return;
        }

        if ($settlementMode === 'CASHBACK') {
            $this->db->table('pembelian_pembayaran')
                ->where('toko_id', $toko_id)
                ->where('retur_id', $retur_id)
                ->delete();
            $this->upsertCashRefundMutation($toko_id, $retur_id, $tanggal, $totalRetur, $username);
            return;
        }

        $this->deleteCashRefundMutation($toko_id, $retur_id);

        $existingPayment = $this->db->query(
            "SELECT bayar_id FROM pembelian_pembayaran WHERE toko_id=:toko_id: AND retur_id=:retur_id:",
            ['toko_id' => $toko_id, 'retur_id' => $retur_id]
        )->getRowArray();

        $payload = [
            'toko_id' => $toko_id,
            'beli_id' => $beli_id,
            'retur_id' => $retur_id,
            'tanggal_bayar' => $tanggal . ' 00:00:00',
            'cara_bayar' => 'POTONGAN RETUR',
            'jumlah_bayar' => round($totalRetur, 2),
            'bank_nama' => null,
            'rekening_no' => null,
            'username' => $username,
        ];

        if ($existingPayment) {
            $this->db->table('pembelian_pembayaran')
                ->where('bayar_id', $existingPayment['bayar_id'])
                ->update($payload);
            return;
        }

        $this->db->table('pembelian_pembayaran')->insert($payload);
    }

    private function upsertCashRefundMutation(string $toko_id, string $retur_id, string $tanggal, float $totalRetur, string $username): void
    {
        $this->ensureReturCashAccount($username);
        $karyawanId = $this->resolveKaryawanIdByUsername($username);
        if ($karyawanId === '') {
            throw new \RuntimeException('User login tidak memiliki karyawan_id untuk pencatatan kas retur');
        }

        $existing = $this->db->query(
            "SELECT kas_id FROM kas_mutasi WHERE toko_id=:toko_id: AND nama_akun='RETUR PEMBELIAN' AND keterangan=:keterangan: LIMIT 1",
            ['toko_id' => $toko_id, 'keterangan' => $retur_id]
        )->getRowArray();

        $payload = [
            'tanggal' => $tanggal . ' 00:00:00',
            'toko_id' => $toko_id,
            'nama_akun' => 'RETUR PEMBELIAN',
            'tipe_mutasi' => 'OPERASIONAL',
            'saldo_channel' => 'CASH',
            'saldo_asal' => null,
            'saldo_tujuan' => null,
            'nominal' => (int) round($totalRetur),
            'karyawan_id' => $karyawanId,
            'keterangan' => $retur_id,
            'updid' => $username,
        ];

        if ($existing) {
            $this->db->table('kas_mutasi')->where('kas_id', $existing['kas_id'])->update($payload);
            return;
        }

        $this->db->table('kas_mutasi')->insert($payload);
    }

    private function deleteCashRefundMutation(string $toko_id, string $retur_id): void
    {
        $this->db->table('kas_mutasi')
            ->where('toko_id', $toko_id)
            ->where('nama_akun', 'RETUR PEMBELIAN')
            ->where('keterangan', $retur_id)
            ->delete();
    }

    private function ensureReturCashAccount(string $username): void
    {
        $row = $this->db->query(
            "SELECT nama_akun FROM akun_kas WHERE nama_akun='RETUR PEMBELIAN' LIMIT 1"
        )->getRowArray();
        if ($row) {
            return;
        }

        $this->db->table('akun_kas')->insert([
            'nama_akun' => 'RETUR PEMBELIAN',
            'jenis_akun' => 'MASUK',
            'flag_beban' => 'N',
            'updid' => $username,
        ]);
    }

    private function resolveKaryawanIdByUsername(string $username): string
    {
        $row = $this->db->query(
            "SELECT karyawan_id FROM tb_user WHERE username=:username: LIMIT 1",
            ['username' => $username]
        )->getRowArray();

        return trim((string) ($row['karyawan_id'] ?? ''));
    }

    private function syncPurchasePaymentSummary(string $toko_id, string $beli_id): void
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
        } elseif ($sisaBayar <= 0.0001) {
            $statusBayar = 'LUNAS';
        } elseif ($totalBayar > 0) {
            $statusBayar = 'CICIL';
            $isKredit = 1;
        } else {
            $statusBayar = 'BELUM';
            $isKredit = 1;
        }

        $this->db->table('pembelian')
            ->where('toko_id', $toko_id)
            ->where('beli_id', $beli_id)
            ->update([
                'total_bayar' => round($totalBayar, 2),
                'sisa_bayar' => round($sisaBayar, 2),
                'status_bayar' => $statusBayar,
                'is_kredit' => $isKredit,
            ]);
    }
}
