<?php

namespace App\Models;

use CodeIgniter\Model;

class BapModel extends Model
{
    protected $table = 'adjust';
    protected $returnType = 'array';
    protected $protectFields = false;

    public function getClosingDate(string $tokoId): string
    {
        return GetClosingDateByToko($tokoId);
    }

    public function ajaxList(array $params, string $tokoId): array
    {
        $start = (int) ($params['start'] ?? 0);
        $length = $params['length'] ?? 25;
        $search = trim((string) ($params['search_value'] ?? ''));
        $queryLimit = $length !== '-1' ? " LIMIT $start, " . (int) $length : '';
        $binds = ['toko_id' => $tokoId];
        $where = " WHERE a.toko_id=:toko_id: AND a.istype='BAP' AND IFNULL(a.ref_no,'')<>'' ";
        if ($search !== '') {
            $where .= " AND (a.ref_no LIKE :search: OR a.keterangan LIKE :search: OR a.updid LIKE :search: OR t.toko_nama LIKE :search:) ";
            $binds['search'] = '%' . $this->db->escapeLikeString($search) . '%';
        }

        $groupSql = "FROM `adjust` a
                     LEFT JOIN toko t ON t.toko_id=a.toko_id
                     $where
                     GROUP BY a.toko_id, a.ref_no";

        $countRow = $this->db->query(
            "SELECT COUNT(*) AS total FROM (SELECT a.ref_no $groupSql) x",
            $binds
        )->getRowArray();

        $data = $this->db->query(
            "SELECT a.ref_no AS bap_id,
                    MIN(a.tanggal) AS tanggal,
                    COUNT(*) AS jml_item,
                    SUM(a.qty_so) AS total_qty,
                    SUM(a.gross) AS total_gross,
                    MAX(a.updid) AS updid,
                    MAX(a.keterangan) AS keterangan,
                    MAX(t.toko_nama) AS toko_nama
             $groupSql
             ORDER BY MIN(a.tanggal) DESC, a.ref_no DESC
             $queryLimit",
            $binds
        )->getResultArray();

        $closingDate = $this->getClosingDate($tokoId);
        foreach ($data as &$row) {
            $row['can_edit'] = substr((string) ($row['tanggal'] ?? ''), 0, 10) >= $closingDate;
            $row['closing_date'] = $closingDate;
        }
        unset($row);

        return [
            'data' => $data,
            'total_count' => (int) ($countRow['total'] ?? 0),
            'total_filtered' => (int) ($countRow['total'] ?? 0),
        ];
    }

    public function searchItems(string $tokoId, string $term): array
    {
        if ($term === '') {
            return [];
        }

        $search = '%' . $this->db->escapeLikeString($term) . '%';
        return $this->db->query(
            "SELECT p.kode_item, p.nama_item
             FROM prodmast p
             WHERE EXISTS (
                SELECT 1
                FROM prodmast_store pst
                WHERE pst.toko_id=:toko_id:
                  AND pst.kode_item=p.kode_item
                  AND pst.status_item='Y'
             )
             AND (p.kode_item LIKE :search: OR p.nama_item LIKE :search:)
             ORDER BY p.nama_item, p.kode_item
             LIMIT 30",
            ['toko_id' => $tokoId, 'search' => $search]
        )->getResultArray();
    }

    public function getItemPayload(string $tokoId, string $kodeItem): ?array
    {
        $units = $this->db->query(
            "SELECT p.kode_item,
                    p.nama_item,
                    COALESCE(st.qty, 0) AS stock_base,
                    ps.sat_id,
                    COALESCE(ps.qty_konversi, 1) AS qty_konversi,
                    ROUND(COALESCE(store.harga_pokok, 0)) AS harga_pokok
             FROM prodmast p
             INNER JOIN prodmast_satuan ps ON ps.kode_item=p.kode_item
             LEFT JOIN prodmast_store store
                ON store.toko_id=:toko_id:
                AND store.kode_item=ps.kode_item
                AND store.sat_id=ps.sat_id
             LEFT JOIN stmast st
                ON st.toko_id=:toko_id:
                AND st.kode_item=p.kode_item
             WHERE p.kode_item=:kode_item:
               AND EXISTS (
                    SELECT 1
                    FROM prodmast_store pst
                    WHERE pst.toko_id=:toko_id:
                      AND pst.kode_item=p.kode_item
                      AND pst.status_item='Y'
               )
             ORDER BY ps.qty_konversi, ps.sat_id",
            ['toko_id' => $tokoId, 'kode_item' => $kodeItem]
        )->getResultArray();

        if (empty($units)) {
            return null;
        }

        $first = $units[0];
        return [
            'kode_item' => (string) ($first['kode_item'] ?? ''),
            'nama_item' => (string) ($first['nama_item'] ?? ''),
            'stock_base' => (float) ($first['stock_base'] ?? 0),
            'unit_options' => array_map(static function (array $row): array {
                $qtyKonversi = (float) ($row['qty_konversi'] ?? 1);
                $stockBase = (float) ($row['stock_base'] ?? 0);
            return [
                'sat_id' => (string) ($row['sat_id'] ?? ''),
                'qty_konversi' => $qtyKonversi,
                'harga_pokok' => (float) ($row['harga_pokok'] ?? 0),
                'stock_base' => $stockBase,
                'stock_hint' => $qtyKonversi > 0 ? round($stockBase / $qtyKonversi, 2) : 0,
            ];
        }, $units),
    ];
    }

    public function getFormData(string $tokoId, ?string $bapId = null): array
    {
        $header = [
            'bap_id' => $this->generateBapId(),
            'tanggal' => date('Y-m-d'),
            'keterangan' => '',
            'toko_id' => $tokoId,
            'toko_nama' => (string) GetToko('toko_nama'),
            'closing_date' => $this->getClosingDate($tokoId),
        ];

        if ($bapId === null || trim($bapId) === '') {
            return ['header' => $header, 'details' => []];
        }

        $document = $this->getDocumentSummary($tokoId, $bapId);
        if (!$document) {
            return ['header' => [], 'details' => []];
        }

        $header = [
            'bap_id' => $document['bap_id'],
            'tanggal' => substr((string) ($document['tanggal'] ?? date('Y-m-d')), 0, 10),
            'keterangan' => (string) ($document['keterangan'] ?? ''),
            'toko_id' => (string) ($document['toko_id'] ?? $tokoId),
            'toko_nama' => (string) ($document['toko_nama'] ?? GetToko('toko_nama')),
            'closing_date' => $this->getClosingDate($tokoId),
        ];

        $details = [];
        foreach (($document['details'] ?? []) as $row) {
            $payload = $this->getItemPayload($tokoId, (string) ($row['kode_item'] ?? ''));
            $unitOptions = $payload['unit_options'] ?? [];
            $selected = $this->findUnitOption($unitOptions, (string) ($row['sat_id'] ?? ''));
            $details[] = [
                'kode_item' => (string) ($row['kode_item'] ?? ''),
                'nama_item' => (string) ($row['nama_item'] ?? ''),
                'sat_id' => (string) ($row['sat_id'] ?? ''),
                'qty_bap' => (float) ($row['qty_so'] ?? 0),
                'qty_konversi' => (float) ($row['qty_konversi'] ?? 1),
                'price' => (float) ($row['price'] ?? 0),
                'gross' => (float) ($row['gross'] ?? 0),
                'stock_hint' => (float) ($selected['stock_hint'] ?? 0),
                'unit_options' => $unitOptions,
            ];
        }

        return ['header' => $header, 'details' => $details];
    }

    public function saveDocument(string $tokoId, string $username, array $payload, string $mode): array
    {
        $tanggal = trim((string) ($payload['tanggal'] ?? ''));
        $bapId = trim((string) ($payload['bap_id'] ?? ''));
        $keterangan = trim((string) ($payload['keterangan'] ?? ''));
        $details = json_decode((string) ($payload['detail_json'] ?? '[]'), true) ?: [];

        if ($tanggal === '') {
            return ['tipe' => 'error', 'data' => 'Tanggal wajib diisi'];
        }
        if ($tanggal < $this->getClosingDate($tokoId)) {
            return ['tipe' => 'error', 'data' => "Tanggal yang di input {$tanggal} sudah melewati periode closing"];
        }
        if ($mode === 'edit' && ($bapId === '' || !$this->documentExists($tokoId, $bapId))) {
            return ['tipe' => 'error', 'data' => 'Dokumen BAP tidak ditemukan'];
        }
        if ($mode === 'edit' && $this->isLocked($tokoId, $bapId)) {
            return ['tipe' => 'error', 'data' => 'Dokumen BAP yang sudah melewati periode closing tidak boleh diedit'];
        }
        if ($mode === 'create' && $bapId !== '' && $this->documentExists($tokoId, $bapId)) {
            $bapId = $this->generateBapId();
        }

        $sanitizedDetails = [];
        $timestamp = $tanggal . ' ' . date('H:i:s');
        foreach ($details as $index => $row) {
            $kodeItem = trim((string) ($row['kode_item'] ?? ''));
            $satId = trim((string) ($row['sat_id'] ?? ''));
            $qtyBap = (float) ($row['qty_bap'] ?? 0);
            $price = $this->parseNumeric($row['price'] ?? 0);

            if ($kodeItem === '' || $satId === '') {
                return ['tipe' => 'error', 'data' => 'Item dan satuan wajib diisi pada semua baris'];
            }
            if ($qtyBap <= 0) {
                return ['tipe' => 'error', 'data' => 'Qty pemusnahan harus lebih besar dari nol'];
            }

            $item = $this->getItemPayload($tokoId, $kodeItem);
            if (!$item) {
                return ['tipe' => 'error', 'data' => "Item {$kodeItem} tidak ditemukan"];
            }
            $unit = $this->findUnitOption($item['unit_options'] ?? [], $satId);
            if (!$unit) {
                return ['tipe' => 'error', 'data' => "Satuan {$satId} untuk item {$kodeItem} tidak valid"];
            }

            $qtyKonversi = (float) ($unit['qty_konversi'] ?? 1);
            $stockBase = (float) ($unit['stock_base'] ?? 0);
            if (($qtyBap * $qtyKonversi) - $stockBase > 0.0001) {
                return ['tipe' => 'error', 'data' => "Qty pemusnahan {$kodeItem} melebihi stok tersedia"];
            }
            $resolvedPrice = $price > 0 ? $price : (float) ($unit['harga_pokok'] ?? 0);
            $qtyStock = round($qtyBap * $qtyKonversi * -1, 4);
            $sanitizedDetails[] = [
                'toko_id' => $tokoId,
                'tanggal' => $timestamp,
                'kode_item' => $kodeItem,
                'istype' => 'BAP',
                'ref_no' => $bapId,
                'seq_no' => $index + 1,
                'sat_id' => $satId,
                'qty_so' => round($qtyBap, 4),
                'qty_konversi' => $qtyKonversi,
                'qty_stock' => $qtyStock,
                'price' => (int) round($resolvedPrice),
                'gross' => (int) round($qtyBap * $resolvedPrice),
                'keterangan' => $keterangan,
                'updid' => $username,
            ];
        }

        if (empty($sanitizedDetails)) {
            return ['tipe' => 'error', 'data' => 'Minimal satu item BAP wajib diisi'];
        }

        if ($mode === 'create') {
            $bapId = $bapId !== '' ? $bapId : $this->generateBapId();
        }

        foreach ($sanitizedDetails as &$row) {
            $row['ref_no'] = $bapId;
        }
        unset($row);

        $this->db->transStart();
        if ($mode === 'edit') {
            $this->db->table('adjust')
                ->where('toko_id', $tokoId)
                ->where('istype', 'BAP')
                ->where('ref_no', $bapId)
                ->delete();
        }

        foreach ($sanitizedDetails as $row) {
            $this->db->table('adjust')->insert($row);
        }
        $this->db->transComplete();

        if (!$this->db->transStatus()) {
            return ['tipe' => 'error', 'data' => 'Gagal menyimpan dokumen BAP'];
        }

        HitungStock($tokoId);
        return ['tipe' => 'success', 'data' => 'Dokumen BAP berhasil disimpan', 'bap_id' => $bapId];
    }

    public function deleteDocument(string $tokoId, string $bapId): array
    {
        if ($bapId === '' || !$this->documentExists($tokoId, $bapId)) {
            return ['tipe' => 'error', 'data' => 'Dokumen BAP tidak ditemukan'];
        }
        if ($this->isLocked($tokoId, $bapId)) {
            return ['tipe' => 'error', 'data' => 'Dokumen BAP yang sudah melewati periode closing tidak boleh dihapus'];
        }

        $this->db->table('adjust')
            ->where('toko_id', $tokoId)
            ->where('istype', 'BAP')
            ->where('ref_no', $bapId)
            ->delete();

        HitungStock($tokoId);
        return ['tipe' => 'success', 'data' => 'Dokumen BAP berhasil dihapus'];
    }

    public function getDocumentSummary(string $tokoId, string $bapId): ?array
    {
        $header = $this->db->query(
            "SELECT a.toko_id,
                    a.ref_no AS bap_id,
                    MIN(a.tanggal) AS tanggal,
                    MAX(a.updid) AS updid,
                    MAX(a.keterangan) AS keterangan,
                    SUM(a.gross) AS total_gross,
                    SUM(a.qty_so) AS total_qty,
                    COUNT(*) AS jml_item,
                    MAX(t.toko_nama) AS toko_nama,
                    MAX(t.toko_alamat) AS toko_alamat
             FROM adjust a
             LEFT JOIN toko t ON t.toko_id=a.toko_id
             WHERE a.toko_id=:toko_id:
               AND a.istype='BAP'
               AND a.ref_no=:ref_no:
             GROUP BY a.toko_id, a.ref_no",
            ['toko_id' => $tokoId, 'ref_no' => $bapId]
        )->getRowArray();

        if (!$header) {
            return null;
        }

        $details = $this->db->query(
            "SELECT a.*, p.nama_item
             FROM adjust a
             LEFT JOIN prodmast p ON p.kode_item=a.kode_item
             WHERE a.toko_id=:toko_id:
               AND a.istype='BAP'
               AND a.ref_no=:ref_no:
             ORDER BY a.seq_no, a.so_id",
            ['toko_id' => $tokoId, 'ref_no' => $bapId]
        )->getResultArray();

        $header['details'] = $details;
        return $header;
    }

    public function isLocked(string $tokoId, string $bapId): bool
    {
        $row = $this->db->query(
            "SELECT MIN(tanggal) AS tanggal
             FROM adjust
             WHERE toko_id=:toko_id:
               AND istype='BAP'
               AND ref_no=:ref_no:",
            ['toko_id' => $tokoId, 'ref_no' => $bapId]
        )->getRowArray();

        if (!$row || empty($row['tanggal'])) {
            return false;
        }

        return substr((string) $row['tanggal'], 0, 10) < $this->getClosingDate($tokoId);
    }

    private function generateBapId(): string
    {
        $row = $this->db->query(
            "SELECT MAX(CAST(SUBSTRING(ref_no, 3, 10) AS UNSIGNED)) AS nomor
             FROM adjust
             WHERE istype='BAP' AND ref_no LIKE 'BA%'"
        )->getRowArray();

        return 'BA' . sprintf('%07d', ((int) ($row['nomor'] ?? 0)) + 1);
    }

    private function documentExists(string $tokoId, string $bapId): bool
    {
        $row = $this->db->query(
            "SELECT COUNT(*) AS total
             FROM adjust
             WHERE toko_id=:toko_id:
               AND istype='BAP'
               AND ref_no=:ref_no:",
            ['toko_id' => $tokoId, 'ref_no' => $bapId]
        )->getRowArray();

        return (int) ($row['total'] ?? 0) > 0;
    }

    private function findUnitOption(array $unitOptions, string $satId): ?array
    {
        foreach ($unitOptions as $unit) {
            if ((string) ($unit['sat_id'] ?? '') === $satId) {
                return $unit;
            }
        }

        return null;
    }

    private function parseNumeric($value): float
    {
        if (is_numeric($value)) {
            return (float) $value;
        }

        $normalized = preg_replace('/[^0-9.\-]/', '', (string) $value);
        return is_numeric($normalized) ? (float) $normalized : 0.0;
    }
}
