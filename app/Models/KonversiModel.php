<?php

namespace App\Models;

use CodeIgniter\Model;

class KonversiModel extends Model
{
    protected $table = 'adjust';
    protected $returnType = 'array';
    protected $protectFields = false;

    public function getClosingDate(string $tokoId): string
    {
        return GetClosingDateByToko($tokoId);
    }

    public function getFormData(string $tokoId): array
    {
        return [
            'header' => [
                'konversi_id' => $this->generateKonversiId(),
                'tanggal' => date('Y-m-d'),
                'keterangan' => '',
                'toko_id' => $tokoId,
                'toko_nama' => (string) GetToko('toko_nama'),
                'closing_date' => $this->getClosingDate($tokoId),
            ],
        ];
    }

    public function ajaxList(array $params, string $tokoId): array
    {
        $start = (int) ($params['start'] ?? 0);
        $length = $params['length'] ?? 25;
        $search = trim((string) ($params['search_value'] ?? ''));
        $queryLimit = $length !== '-1' ? " LIMIT $start, " . (int) $length : '';
        $binds = ['toko_id' => $tokoId];
        $where = " WHERE a.toko_id=:toko_id: AND a.istype='KO' AND IFNULL(a.ref_no,'')<>'' ";
        if ($search !== '') {
            $where .= " AND (a.ref_no LIKE :search: OR a.keterangan LIKE :search: OR a.updid LIKE :search: OR h.kode_item LIKE :search: OR p.nama_item LIKE :search:) ";
            $binds['search'] = '%' . $this->db->escapeLikeString($search) . '%';
        }

        $groupSql = "FROM adjust a
                     LEFT JOIN his_konversi h ON h.toko_id=a.toko_id AND h.konversi_id=a.ref_no AND h.role_item='HASIL'
                     LEFT JOIN prodmast p ON p.kode_item=h.kode_item
                     $where
                     GROUP BY a.toko_id, a.ref_no";

        $countRow = $this->db->query(
            "SELECT COUNT(*) AS total FROM (SELECT a.ref_no $groupSql) x",
            $binds
        )->getRowArray();

        $data = $this->db->query(
            "SELECT a.ref_no AS konversi_id,
                    MIN(a.tanggal) AS tanggal,
                    MAX(a.updid) AS updid,
                    MAX(a.keterangan) AS keterangan,
                    SUM(CASE WHEN a.qty_stock > 0 THEN a.qty_so ELSE 0 END) AS total_qty_hasil,
                    SUM(CASE WHEN a.qty_stock < 0 THEN ABS(a.qty_so) ELSE 0 END) AS total_qty_asal,
                    MAX(p.nama_item) AS nama_item_hasil,
                    MAX(h.kode_item) AS kode_item_hasil
             $groupSql
             ORDER BY MIN(a.tanggal) DESC, a.ref_no DESC
             $queryLimit",
            $binds
        )->getResultArray();

        $closingDate = $this->getClosingDate($tokoId);
        foreach ($data as &$row) {
            $row['can_delete'] = substr((string) ($row['tanggal'] ?? ''), 0, 10) >= $closingDate;
            $row['closing_date'] = $closingDate;
        }
        unset($row);

        return [
            'data' => $data,
            'total_count' => (int) ($countRow['total'] ?? 0),
            'total_filtered' => (int) ($countRow['total'] ?? 0),
        ];
    }

    public function ajaxRecipeList(array $params): array
    {
        $start = (int) ($params['start'] ?? 0);
        $length = $params['length'] ?? 25;
        $search = trim((string) ($params['search_value'] ?? ''));
        $queryLimit = $length !== '-1' ? " LIMIT $start, " . (int) $length : '';
        $binds = [];
        $where = ' WHERE 1=1 ';
        if ($search !== '') {
            $where .= " AND (r.kode_item_asal LIKE :search: OR pa.nama_item LIKE :search: OR r.kode_item_hasil LIKE :search: OR ph.nama_item LIKE :search: OR r.sat_asal LIKE :search: OR r.sat_hasil LIKE :search:) ";
            $binds['search'] = '%' . $this->db->escapeLikeString($search) . '%';
        }

        $countRow = $this->db->query(
            "SELECT COUNT(*) AS total
             FROM konversi_recipe r
             LEFT JOIN prodmast pa ON pa.kode_item=r.kode_item_asal
             LEFT JOIN prodmast ph ON ph.kode_item=r.kode_item_hasil
             $where",
            $binds
        )->getRowArray();

        $data = $this->db->query(
            "SELECT r.*,
                    pa.nama_item AS nama_item_asal,
                    ph.nama_item AS nama_item_hasil
             FROM konversi_recipe r
             LEFT JOIN prodmast pa ON pa.kode_item=r.kode_item_asal
             LEFT JOIN prodmast ph ON ph.kode_item=r.kode_item_hasil
             $where
             ORDER BY r.kode_item_hasil, r.sat_hasil, r.kode_item_asal, r.recipe_id
             $queryLimit",
            $binds
        )->getResultArray();

        return [
            'data' => $data,
            'total_count' => (int) ($countRow['total'] ?? 0),
            'total_filtered' => (int) ($countRow['total'] ?? 0),
        ];
    }

    public function searchResultItems(string $term): array
    {
        $search = '%' . $this->db->escapeLikeString($term) . '%';
        return $this->db->query(
            "SELECT DISTINCT r.kode_item_hasil, p.nama_item
             FROM konversi_recipe r
             LEFT JOIN prodmast p ON p.kode_item=r.kode_item_hasil
             WHERE r.kode_item_hasil LIKE :search: OR p.nama_item LIKE :search:
             ORDER BY p.nama_item, r.kode_item_hasil
             LIMIT 30",
            ['search' => $search]
        )->getResultArray();
    }

    public function searchItems(string $term): array
    {
        if ($term === '') {
            return [];
        }

        $search = '%' . $this->db->escapeLikeString($term) . '%';
        return $this->db->query(
            "SELECT kode_item, nama_item
             FROM prodmast
             WHERE kode_item LIKE :search: OR nama_item LIKE :search:
             ORDER BY nama_item, kode_item
             LIMIT 30",
            ['search' => $search]
        )->getResultArray();
    }

    public function getItemPayload(string $tokoId, string $kodeItem): ?array
    {
        $item = $this->db->query(
            "SELECT kode_item, nama_item
             FROM prodmast
             WHERE kode_item=:kode_item:",
            ['kode_item' => $kodeItem]
        )->getRowArray();

        if (!$item) {
            return null;
        }

        $units = $this->db->query(
            "SELECT ps.sat_id,
                    ps.qty_konversi,
                    COALESCE(store.harga_pokok, 0) AS harga_pokok
             FROM prodmast_satuan ps
             LEFT JOIN prodmast_store store
                ON store.kode_item=ps.kode_item
                AND store.toko_id=:toko_id:
                AND store.sat_id=ps.sat_id
             WHERE ps.kode_item=:kode_item:
             ORDER BY ps.qty_konversi, ps.sat_id",
            ['toko_id' => $tokoId, 'kode_item' => $kodeItem]
        )->getResultArray();

        if (empty($units)) {
            return null;
        }

        $item['satuan'] = $units;
        return $item;
    }

    public function getRecipeExecutionPayload(string $tokoId, string $kodeItemHasil): ?array
    {
        $recipes = $this->db->query(
            "SELECT r.*,
                    pa.nama_item AS nama_item_asal,
                    ph.nama_item AS nama_item_hasil,
                    psa.qty_konversi AS qty_konversi_asal,
                    psh.qty_konversi AS qty_konversi_hasil,
                    COALESCE(sa.harga_pokok, 0) AS hpp_asal,
                    COALESCE(sh.harga_pokok, 0) AS hpp_hasil,
                    COALESCE(st_asal.qty, 0) / NULLIF(psa.qty_konversi, 0) AS stok_asal_satuan,
                    COALESCE(st_hasil.qty, 0) AS stok_hasil_base,
                    COALESCE(st_hasil.rp_saldo_akh, 0) AS rp_saldo_hasil
             FROM konversi_recipe r
             LEFT JOIN prodmast pa ON pa.kode_item=r.kode_item_asal
             LEFT JOIN prodmast ph ON ph.kode_item=r.kode_item_hasil
             LEFT JOIN prodmast_satuan psa ON psa.kode_item=r.kode_item_asal AND psa.sat_id=r.sat_asal
             LEFT JOIN prodmast_satuan psh ON psh.kode_item=r.kode_item_hasil AND psh.sat_id=r.sat_hasil
             LEFT JOIN prodmast_store sa ON sa.toko_id=:toko_id: AND sa.kode_item=r.kode_item_asal AND sa.sat_id=r.sat_asal
             LEFT JOIN prodmast_store sh ON sh.toko_id=:toko_id: AND sh.kode_item=r.kode_item_hasil AND sh.sat_id=r.sat_hasil
             LEFT JOIN stmast st_asal ON st_asal.toko_id=:toko_id: AND st_asal.kode_item=r.kode_item_asal
             LEFT JOIN stmast st_hasil ON st_hasil.toko_id=:toko_id: AND st_hasil.kode_item=r.kode_item_hasil
             WHERE r.kode_item_hasil=:kode_item_hasil:
             ORDER BY r.recipe_id",
            ['toko_id' => $tokoId, 'kode_item_hasil' => $kodeItemHasil]
        )->getResultArray();

        if (empty($recipes)) {
            return null;
        }

        $first = $recipes[0];
        return [
            'kode_item_hasil' => (string) ($first['kode_item_hasil'] ?? ''),
            'nama_item_hasil' => (string) ($first['nama_item_hasil'] ?? ''),
            'sat_hasil' => (string) ($first['sat_hasil'] ?? ''),
            'qty_hasil_default' => (float) ($first['qty_hasil'] ?? 0),
            'qty_konversi_hasil' => (float) ($first['qty_konversi_hasil'] ?? 1),
            'hpp_hasil' => (float) ($first['hpp_hasil'] ?? 0),
            'stok_hasil_base' => (float) ($first['stok_hasil_base'] ?? 0),
            'rp_saldo_hasil' => (float) ($first['rp_saldo_hasil'] ?? 0),
            'recipe_lines' => array_map(static function (array $row): array {
                return [
                    'recipe_id' => (int) ($row['recipe_id'] ?? 0),
                    'kode_item_asal' => (string) ($row['kode_item_asal'] ?? ''),
                    'nama_item_asal' => (string) ($row['nama_item_asal'] ?? ''),
                    'sat_asal' => (string) ($row['sat_asal'] ?? ''),
                    'qty_asal' => (float) ($row['qty_asal'] ?? 0),
                    'kode_item_hasil' => (string) ($row['kode_item_hasil'] ?? ''),
                    'sat_hasil' => (string) ($row['sat_hasil'] ?? ''),
                    'qty_hasil' => (float) ($row['qty_hasil'] ?? 0),
                    'qty_konversi_asal' => (float) ($row['qty_konversi_asal'] ?? 1),
                    'qty_konversi_hasil' => (float) ($row['qty_konversi_hasil'] ?? 1),
                    'hpp_asal' => (float) ($row['hpp_asal'] ?? 0),
                    'stok_asal_satuan' => (float) ($row['stok_asal_satuan'] ?? 0),
                ];
            }, $recipes),
        ];
    }

    public function saveConversion(string $tokoId, string $username, array $payload): array
    {
        $tanggal = trim((string) ($payload['tanggal'] ?? ''));
        $konversiId = trim((string) ($payload['konversi_id'] ?? ''));
        $keterangan = trim((string) ($payload['keterangan'] ?? ''));
        $kodeItemHasil = trim((string) ($payload['kode_item_hasil'] ?? ''));
        $lines = json_decode((string) ($payload['lines_json'] ?? '[]'), true) ?: [];

        if ($tanggal === '') {
            return ['tipe' => 'error', 'data' => 'Tanggal wajib diisi'];
        }
        if ($tanggal < $this->getClosingDate($tokoId)) {
            return ['tipe' => 'error', 'data' => "Tanggal yang di input {$tanggal} sudah melewati periode closing"];
        }
        if ($kodeItemHasil === '') {
            return ['tipe' => 'error', 'data' => 'Item hasil wajib dipilih'];
        }
        if ($konversiId === '' || $this->documentExists($tokoId, $konversiId)) {
            $konversiId = $this->generateKonversiId();
        }

        $recipePayload = $this->getRecipeExecutionPayload($tokoId, $kodeItemHasil);
        if (!$recipePayload) {
            return ['tipe' => 'error', 'data' => 'Recipe untuk item hasil tidak ditemukan'];
        }

        $recipeMap = [];
        foreach (($recipePayload['recipe_lines'] ?? []) as $recipe) {
            $recipeMap[(int) ($recipe['recipe_id'] ?? 0)] = $recipe;
        }

        $consumedLines = [];
        $totalSourceHpp = 0.0;
        $totalQtyHasilSat = 0.0;
        $formulaParts = [];

        foreach ($lines as $row) {
            $recipeId = (int) ($row['recipe_id'] ?? 0);
            $qtyPakai = (float) ($row['qty_pakai'] ?? 0);
            if ($qtyPakai <= 0) {
                continue;
            }

            $recipe = $recipeMap[$recipeId] ?? null;
            if (!$recipe) {
                return ['tipe' => 'error', 'data' => 'Ada baris recipe yang tidak valid'];
            }

            $stokTersedia = (float) ($recipe['stok_asal_satuan'] ?? 0);
            $qtyAsalFormula = (float) ($recipe['qty_asal'] ?? 0);
            $qtyHasilFormula = (float) ($recipe['qty_hasil'] ?? 0);
            $qtyKonvAsal = (float) ($recipe['qty_konversi_asal'] ?? 1);

            if ($qtyAsalFormula <= 0 || $qtyHasilFormula <= 0 || $qtyKonvAsal <= 0) {
                return ['tipe' => 'error', 'data' => 'Formula recipe tidak valid'];
            }
            if ($qtyPakai - $stokTersedia > 0.0001) {
                return ['tipe' => 'error', 'data' => 'Bahan baku pembuatan resep tidak mencukupi!'];
            }

            $batchRatio = $qtyPakai / $qtyAsalFormula;
            if ($batchRatio < 1 || abs($batchRatio - round($batchRatio)) > 0.0001) {
                return ['tipe' => 'error', 'data' => 'Qty bahan asal harus memenuhi formula utuh untuk menghasilkan produk jadi'];
            }

            $jumlahBatch = (int) round($batchRatio);
            $qtyHasilLine = $jumlahBatch * $qtyHasilFormula;
            $lineHpp = $qtyPakai * (float) ($recipe['hpp_asal'] ?? 0);
            $consumedLines[] = [
                'recipe' => $recipe,
                'qty_pakai' => $qtyPakai,
                'jumlah_batch' => $jumlahBatch,
                'qty_hasil_line' => $qtyHasilLine,
                'line_hpp' => $lineHpp,
                'qty_stock_asal' => round($qtyPakai * $qtyKonvAsal, 4),
            ];
            $totalSourceHpp += $lineHpp;
            $totalQtyHasilSat += $qtyHasilLine;
            $formulaParts[] = sprintf(
                '%s %s %s -> %s %s %s',
                number_format($qtyPakai, 2, '.', ''),
                $recipe['sat_asal'],
                $recipe['kode_item_asal'],
                number_format($qtyHasilLine, 2, '.', ''),
                $recipe['sat_hasil'],
                $recipe['kode_item_hasil']
            );
        }

        if (empty($consumedLines) || $totalQtyHasilSat <= 0) {
            return ['tipe' => 'error', 'data' => 'Minimal satu bahan asal harus diisi'];
        }

        $qtyKonvHasil = (float) ($recipePayload['qty_konversi_hasil'] ?? 1);
        if ($qtyKonvHasil <= 0) {
            return ['tipe' => 'error', 'data' => 'Satuan hasil recipe tidak valid'];
        }

        $totalQtyHasilBase = round($totalQtyHasilSat * $qtyKonvHasil, 4);
        $hppSatuanKonversi = $totalSourceHpp / $totalQtyHasilSat;
        $hppBaseKonversi = $totalSourceHpp / max($totalQtyHasilBase, 0.0001);
        $stokHasilBaseBefore = (float) ($recipePayload['stok_hasil_base'] ?? 0);
        $rpSaldoHasilBefore = (float) ($recipePayload['rp_saldo_hasil'] ?? 0);
        $hppBaseBefore = $stokHasilBaseBefore > 0 ? ($rpSaldoHasilBefore / $stokHasilBaseBefore) : ((float) ($recipePayload['hpp_hasil'] ?? 0) / $qtyKonvHasil);
        $hppBaseAfter = ($rpSaldoHasilBefore + $totalSourceHpp) / max($stokHasilBaseBefore + $totalQtyHasilBase, 0.0001);
        $hppSatBefore = (float) ($recipePayload['hpp_hasil'] ?? 0);
        $hppSatAfter = $hppBaseAfter * $qtyKonvHasil;
        $formulaText = sprintf(
            'hpp_base_baru=(%.2f+%.2f)/(%.4f+%.4f)=%.4f | hpp_satuan_baru=%.4f*%.4f=%.2f',
            $rpSaldoHasilBefore,
            $totalSourceHpp,
            $stokHasilBaseBefore,
            $totalQtyHasilBase,
            $hppBaseAfter,
            $hppBaseAfter,
            $qtyKonvHasil,
            $hppSatAfter
        );

        $timestamp = $tanggal . ' ' . date('H:i:s');
        $resultSeq = count($consumedLines) + 1;

        $this->db->transStart();
        $seq = 1;
        foreach ($consumedLines as $line) {
            $recipe = $line['recipe'];
            $this->db->table('adjust')->insert([
                'toko_id' => $tokoId,
                'tanggal' => $timestamp,
                'kode_item' => $recipe['kode_item_asal'],
                'istype' => 'KO',
                'ref_no' => $konversiId,
                'seq_no' => $seq++,
                'sat_id' => $recipe['sat_asal'],
                'qty_so' => $line['qty_pakai'],
                'qty_konversi' => $recipe['qty_konversi_asal'],
                'qty_stock' => $line['qty_stock_asal'] * -1,
                'price' => (int) round($recipe['hpp_asal']),
                'gross' => (int) round($line['line_hpp']),
                'keterangan' => $keterangan !== '' ? $keterangan : 'Konversi ' . $konversiId,
                'updid' => $username,
            ]);

            $this->db->table('his_konversi')->insert([
                'toko_id' => $tokoId,
                'konversi_id' => $konversiId,
                'tanggal' => $timestamp,
                'kode_item' => $recipe['kode_item_asal'],
                'sat_id' => $recipe['sat_asal'],
                'role_item' => 'ASAL',
                'qty_formula' => $recipe['qty_asal'],
                'qty_transaksi' => $line['qty_pakai'],
                'qty_hasil' => $line['qty_hasil_line'],
                'qty_konversi' => $recipe['qty_konversi_asal'],
                'qty_stock' => $line['qty_stock_asal'] * -1,
                'hpp_satuan' => $recipe['hpp_asal'],
                'total_hpp' => $line['line_hpp'],
                'formula_text' => $formulaText,
                'keterangan' => $keterangan,
                'updid' => $username,
            ]);
        }

        $this->db->table('adjust')->insert([
            'toko_id' => $tokoId,
            'tanggal' => $timestamp,
            'kode_item' => $recipePayload['kode_item_hasil'],
            'istype' => 'KO',
            'ref_no' => $konversiId,
            'seq_no' => $resultSeq,
            'sat_id' => $recipePayload['sat_hasil'],
            'qty_so' => round($totalQtyHasilSat, 4),
            'qty_konversi' => $qtyKonvHasil,
            'qty_stock' => $totalQtyHasilBase,
            'price' => (int) round($hppSatAfter),
            'gross' => (int) round($totalSourceHpp),
            'keterangan' => $keterangan !== '' ? $keterangan : 'Konversi ' . $konversiId,
            'updid' => $username,
        ]);

        $this->db->table('his_konversi')->insert([
            'toko_id' => $tokoId,
            'konversi_id' => $konversiId,
            'tanggal' => $timestamp,
            'kode_item' => $recipePayload['kode_item_hasil'],
            'sat_id' => $recipePayload['sat_hasil'],
            'role_item' => 'HASIL',
            'qty_formula' => $recipePayload['qty_hasil_default'],
            'qty_transaksi' => $totalQtyHasilSat,
            'qty_hasil' => $totalQtyHasilSat,
            'qty_konversi' => $qtyKonvHasil,
            'qty_stock' => $totalQtyHasilBase,
            'hpp_satuan' => $hppSatuanKonversi,
            'total_hpp' => $totalSourceHpp,
            'hpp_base_before' => $hppBaseBefore,
            'hpp_base_after' => $hppBaseAfter,
            'hpp_sat_before' => $hppSatBefore,
            'hpp_sat_after' => $hppSatAfter,
            'formula_text' => $formulaText,
            'keterangan' => $keterangan,
            'updid' => $username,
        ]);

        $this->applyResultBaseCost($tokoId, $recipePayload['kode_item_hasil'], $hppBaseAfter, $username, $konversiId);
        $this->db->transComplete();

        if (!$this->db->transStatus()) {
            return ['tipe' => 'error', 'data' => 'Gagal menyimpan konversi'];
        }

        HitungStock($tokoId);
        return [
            'tipe' => 'success',
            'data' => 'Konversi berhasil disimpan',
            'konversi_id' => $konversiId,
            'trace_formula' => $formulaText . ' | sumber=' . implode('; ', $formulaParts),
        ];
    }

    public function deleteConversion(string $tokoId, string $konversiId): array
    {
        if ($konversiId === '' || !$this->documentExists($tokoId, $konversiId)) {
            return ['tipe' => 'error', 'data' => 'Dokumen konversi tidak ditemukan'];
        }
        if ($this->isLocked($tokoId, $konversiId)) {
            return ['tipe' => 'error', 'data' => 'Dokumen konversi yang sudah melewati periode closing tidak boleh dihapus'];
        }

        $resultRows = $this->db->query(
            "SELECT *
             FROM his_konversi
             WHERE toko_id=:toko_id:
               AND konversi_id=:konversi_id:
               AND role_item='HASIL'",
            ['toko_id' => $tokoId, 'konversi_id' => $konversiId]
        )->getResultArray();

        $this->db->transStart();
        $this->db->table('adjust')
            ->where('toko_id', $tokoId)
            ->where('istype', 'KO')
            ->where('ref_no', $konversiId)
            ->delete();
        $this->db->table('his_konversi')
            ->where('toko_id', $tokoId)
            ->where('konversi_id', $konversiId)
            ->delete();

        foreach ($resultRows as $row) {
            $this->restoreResultBaseCostAfterDelete($tokoId, $row);
        }
        $this->db->transComplete();

        if (!$this->db->transStatus()) {
            return ['tipe' => 'error', 'data' => 'Gagal menghapus konversi'];
        }

        HitungStock($tokoId);
        return [
            'tipe' => 'success',
            'data' => 'Konversi berhasil dihapus',
            'trace_formula' => implode('; ', array_map(static fn(array $row): string => (string) ($row['formula_text'] ?? ''), $resultRows)),
        ];
    }

    public function getDocumentSummary(string $tokoId, string $konversiId): ?array
    {
        $header = $this->db->query(
            "SELECT a.ref_no AS konversi_id,
                    a.toko_id,
                    MIN(a.tanggal) AS tanggal,
                    MAX(a.keterangan) AS keterangan,
                    MAX(a.updid) AS updid,
                    MAX(t.toko_nama) AS toko_nama
             FROM adjust a
             LEFT JOIN toko t ON t.toko_id=a.toko_id
             WHERE a.toko_id=:toko_id:
               AND a.istype='KO'
               AND a.ref_no=:ref_no:
             GROUP BY a.toko_id, a.ref_no",
            ['toko_id' => $tokoId, 'ref_no' => $konversiId]
        )->getRowArray();

        if (!$header) {
            return null;
        }

        $history = $this->db->query(
            "SELECT h.*, p.nama_item
             FROM his_konversi h
             LEFT JOIN prodmast p ON p.kode_item=h.kode_item
             WHERE h.toko_id=:toko_id:
               AND h.konversi_id=:konversi_id:
             ORDER BY FIELD(h.role_item,'ASAL','HASIL'), h.his_id",
            ['toko_id' => $tokoId, 'konversi_id' => $konversiId]
        )->getResultArray();

        $header['details'] = $history;
        return $header;
    }

    public function saveRecipe(string $username, array $payload, string $mode): array
    {
        $recipeId = (int) ($payload['recipe_id'] ?? 0);
        $data = [
            'kode_item_asal' => trim((string) ($payload['kode_item_asal'] ?? '')),
            'sat_asal' => trim((string) ($payload['sat_asal'] ?? '')),
            'qty_asal' => (float) ($payload['qty_asal'] ?? 0),
            'kode_item_hasil' => trim((string) ($payload['kode_item_hasil'] ?? '')),
            'sat_hasil' => trim((string) ($payload['sat_hasil'] ?? '')),
            'qty_hasil' => (float) ($payload['qty_hasil'] ?? 0),
            'updid' => $username,
            'updtime' => date('Y-m-d H:i:s'),
        ];

        if ($data['kode_item_asal'] === '' || $data['kode_item_hasil'] === '' || $data['sat_asal'] === '' || $data['sat_hasil'] === '') {
            return ['tipe' => 'error', 'data' => 'Item asal, item hasil, dan satuan wajib diisi'];
        }
        if ($data['qty_asal'] <= 0 || $data['qty_hasil'] <= 0) {
            return ['tipe' => 'error', 'data' => 'Qty formula wajib lebih besar dari nol'];
        }

        $duplicate = $this->db->query(
            "SELECT recipe_id
             FROM konversi_recipe
             WHERE kode_item_asal=:kode_item_asal:
               AND sat_asal=:sat_asal:
               AND kode_item_hasil=:kode_item_hasil:
               AND sat_hasil=:sat_hasil:
               AND recipe_id<>:recipe_id:
             LIMIT 1",
            $data + ['recipe_id' => $recipeId]
        )->getRowArray();
        if ($duplicate) {
            return ['tipe' => 'error', 'data' => 'Formula recipe yang sama sudah ada'];
        }

        if ($mode === 'create') {
            $this->db->table('konversi_recipe')->insert($data);
            return ['tipe' => 'success', 'data' => 'Recipe berhasil ditambahkan'];
        }

        if ($recipeId <= 0) {
            return ['tipe' => 'error', 'data' => 'Recipe tidak ditemukan'];
        }

        $this->db->table('konversi_recipe')
            ->where('recipe_id', $recipeId)
            ->update($data);

        return ['tipe' => 'success', 'data' => 'Recipe berhasil diupdate'];
    }

    public function deleteRecipe(int $recipeId): array
    {
        if ($recipeId <= 0) {
            return ['tipe' => 'error', 'data' => 'Recipe tidak ditemukan'];
        }

        $this->db->table('konversi_recipe')->where('recipe_id', $recipeId)->delete();
        return ['tipe' => 'success', 'data' => 'Recipe berhasil dihapus'];
    }

    private function generateKonversiId(): string
    {
        $row = $this->db->query(
            "SELECT MAX(CAST(SUBSTRING(ref_no, 3, 10) AS UNSIGNED)) AS nomor
             FROM adjust
             WHERE istype='KO' AND ref_no LIKE 'KO%'"
        )->getRowArray();

        return 'KO' . sprintf('%07d', ((int) ($row['nomor'] ?? 0)) + 1);
    }

    private function documentExists(string $tokoId, string $konversiId): bool
    {
        $row = $this->db->query(
            "SELECT COUNT(*) AS total
             FROM adjust
             WHERE toko_id=:toko_id:
               AND istype='KO'
               AND ref_no=:ref_no:",
            ['toko_id' => $tokoId, 'ref_no' => $konversiId]
        )->getRowArray();

        return (int) ($row['total'] ?? 0) > 0;
    }

    private function isLocked(string $tokoId, string $konversiId): bool
    {
        $row = $this->db->query(
            "SELECT MIN(tanggal) AS tanggal
             FROM adjust
             WHERE toko_id=:toko_id:
               AND istype='KO'
               AND ref_no=:ref_no:",
            ['toko_id' => $tokoId, 'ref_no' => $konversiId]
        )->getRowArray();

        if (!$row || empty($row['tanggal'])) {
            return false;
        }

        return substr((string) $row['tanggal'], 0, 10) < $this->getClosingDate($tokoId);
    }

    private function applyResultBaseCost(string $tokoId, string $kodeItem, float $hppBaseAfter, string $username, string $konversiId): void
    {
        $satuanRows = $this->db->query(
            "SELECT sat_id, qty_konversi
             FROM prodmast_satuan
             WHERE kode_item=:kode_item:
             ORDER BY qty_konversi, sat_id",
            ['kode_item' => $kodeItem]
        )->getResultArray();

        foreach ($satuanRows as $row) {
            $hargaPokok = round($hppBaseAfter * (float) ($row['qty_konversi'] ?? 1));
            $this->db->table('prodmast_store')
                ->where('toko_id', $tokoId)
                ->where('kode_item', $kodeItem)
                ->where('sat_id', (string) ($row['sat_id'] ?? ''))
                ->update([
                    'harga_pokok' => $hargaPokok,
                    'updid' => 'KO ' . $konversiId . ' ' . $username,
                    'updtime' => date('Y-m-d H:i:s'),
                ]);
        }
    }

    private function restoreResultBaseCostAfterDelete(string $tokoId, array $deletedResultRow): void
    {
        $kodeItem = (string) ($deletedResultRow['kode_item'] ?? '');
        if ($kodeItem === '') {
            return;
        }

        $baseSat = $this->db->query(
            "SELECT ps.sat_id, ps.qty_konversi, COALESCE(store.harga_pokok, 0) AS harga_pokok
             FROM prodmast_satuan ps
             LEFT JOIN prodmast_store store
                ON store.toko_id=:toko_id:
                AND store.kode_item=ps.kode_item
                AND store.sat_id=ps.sat_id
             WHERE ps.kode_item=:kode_item:
             ORDER BY ps.qty_konversi, ps.sat_id
             LIMIT 1",
            ['toko_id' => $tokoId, 'kode_item' => $kodeItem]
        )->getRowArray();

        if (!$baseSat) {
            return;
        }

        $currentBaseCost = (float) ($baseSat['harga_pokok'] ?? 0) / max((float) ($baseSat['qty_konversi'] ?? 1), 1);
        $deletedAfter = (float) ($deletedResultRow['hpp_base_after'] ?? 0);
        if (abs($currentBaseCost - $deletedAfter) > 1) {
            return;
        }

        $latestRemaining = $this->db->query(
            "SELECT hpp_base_after
             FROM his_konversi
             WHERE toko_id=:toko_id:
               AND kode_item=:kode_item:
               AND role_item='HASIL'
             ORDER BY tanggal DESC, his_id DESC
             LIMIT 1",
            ['toko_id' => $tokoId, 'kode_item' => $kodeItem]
        )->getRowArray();

        $targetBase = $latestRemaining
            ? (float) ($latestRemaining['hpp_base_after'] ?? 0)
            : (float) ($deletedResultRow['hpp_base_before'] ?? 0);

        if ($targetBase <= 0) {
            return;
        }

        $this->applyResultBaseCost($tokoId, $kodeItem, $targetBase, 'rollback', (string) ($deletedResultRow['konversi_id'] ?? ''));
    }
}
