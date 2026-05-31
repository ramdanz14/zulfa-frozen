<?php

namespace App\Models;

use CodeIgniter\Model;

class SettingHargaModel extends Model
{
    protected $table = 'prodmast_store';
    protected $returnType = 'array';
    protected $protectFields = false;

    public function getRecentInvoices(string $toko_id): array
    {
        return $this->db->query(
            "SELECT p.beli_id, p.invoice, p.tanggal, s.nama AS supplier_nama, COUNT(pd.seq_no) AS jml_item
             FROM pembelian p
             INNER JOIN pembelian_detail pd ON pd.toko_id=p.toko_id AND pd.beli_id=p.beli_id
             LEFT JOIN supmast s ON s.supco=p.supco
             WHERE p.toko_id=:toko_id: AND p.status_nota='TERIMA'
             GROUP BY p.toko_id, p.beli_id, p.invoice, p.tanggal, s.nama
             ORDER BY p.tanggal DESC, p.updtime DESC, p.beli_id DESC
             LIMIT 10",
            ['toko_id' => $toko_id]
        )->getResultArray();
    }

    public function ajaxList(array $params, string $toko_id, string $beli_id = ''): array
    {
        $start = (int) ($params['start'] ?? 0);
        $length = $params['length'] ?? 25;
        $search = trim((string) ($params['search_value'] ?? ''));
        $queryLimit = $length !== '-1' ? " LIMIT $start, " . (int) $length : '';

        $binds = ['toko_id' => $toko_id];
        $where = " WHERE ps.toko_id=:toko_id: AND ps.status_item='Y' ";

        if ($beli_id !== '') {
            $where .= " AND ps.kode_item IN (
                SELECT pd.kode_item
                FROM pembelian_detail pd
                WHERE pd.toko_id=:toko_id: AND pd.beli_id=:beli_id:
            )";
            $binds['beli_id'] = $beli_id;
        }

        $baseSql = "
            FROM prodmast_store ps
            INNER JOIN prodmast p ON p.kode_item=ps.kode_item
            LEFT JOIN prodmast_satuan st ON st.kode_item=ps.kode_item AND st.sat_id=ps.sat_id
            $where
        ";

        $searchSql = '';
        if ($search !== '') {
            $searchSql = " AND (ps.kode_item LIKE :search: OR p.nama_item LIKE :search: OR ps.sat_id LIKE :search:)";
            $binds['search'] = '%' . $this->db->escapeLikeString($search) . '%';
        }

        $totalRow = $this->db->query(
            "SELECT COUNT(*) total $baseSql",
            $binds
        )->getRowArray();

        $filtered = $totalRow['total'] ?? 0;
        if ($search !== '') {
            $filteredRow = $this->db->query(
                "SELECT COUNT(*) total $baseSql $searchSql",
                $binds
            )->getRowArray();
            $filtered = $filteredRow['total'] ?? 0;
        }

        $data = $this->db->query(
            "SELECT ps.kode_item, p.nama_item, ps.sat_id, COALESCE(st.qty_konversi, 0) AS qty_konversi,
                    ps.harga_pokok, ps.harga_jual, ps.target_psn_margin
             $baseSql
             $searchSql
             ORDER BY p.nama_item, ps.kode_item, COALESCE(st.qty_konversi, 0), ps.sat_id
             $queryLimit",
            $binds
        )->getResultArray();


        $cekgram = $this->db->query("SELECT * FROM CONST WHERE rkey='satuan_gramasi'")->getRow();
        $satGramasiRaw = $cekgram->nilai ?? "GR;GRAM;ML";
        $satuanGramasi = explode(';', $satGramasiRaw);

        foreach ($data as &$row) {
            $row['can_round_50'] = !$this->isGramasiUnit($satuanGramasi, (string) ($row['sat_id'] ?? ''));
        }

        return [
            'data' => $data,
            'total_count' => (int) ($totalRow['total'] ?? 0),
            'total_filtered' => (int) $filtered,
        ];
    }

    public function saveCorrections(string $toko_id, string $username, array $rows, string $source_beli_id = 'KOREKSI'): array
    {
        if (isset($rows['kode_item']) && isset($rows['sat_id'])) {
            $rows = [$rows];
        }

        if (empty($rows)) {
            return ['tipe' => 'error', 'data' => 'Tidak ada perubahan harga yang dikirim'];
        }

        $saved = 0;
        $this->db->transStart();

        foreach ($rows as $row) {
            $kodeItem = trim((string) ($row['kode_item'] ?? ''));
            $satId = trim((string) ($row['sat_id'] ?? ''));
            $hargaPokokNew = (int) ($row['harga_pokok'] ?? 0);
            $hargaJualNew = (int) ($row['harga_jual'] ?? 0);

            if ($kodeItem === '' || $satId === '') {
                $this->db->transRollback();
                return ['tipe' => 'error', 'data' => 'Item koreksi tidak valid'];
            }
            if ($hargaPokokNew <= 0 || $hargaJualNew <= 0) {
                $this->db->transRollback();
                return ['tipe' => 'error', 'data' => "Harga item $kodeItem / $satId harus lebih besar dari nol"];
            }
            if ($hargaJualNew < $hargaPokokNew) {
                $this->db->transRollback();
                return ['tipe' => 'error', 'data' => "Harga jual item $kodeItem / $satId tidak boleh lebih kecil dari harga pokok"];
            }

            $current = $this->db->query(
                "SELECT harga_pokok, harga_jual, target_psn_margin
                 FROM prodmast_store
                 WHERE toko_id=:toko_id: AND kode_item=:kode_item: AND sat_id=:sat_id: AND status_item='Y'",
                [
                    'toko_id' => $toko_id,
                    'kode_item' => $kodeItem,
                    'sat_id' => $satId,
                ]
            )->getRowArray();

            if (!$current) {
                $this->db->transRollback();
                return ['tipe' => 'error', 'data' => "Data store untuk item $kodeItem / $satId tidak ditemukan"];
            }

            $hargaPokokOld = (int) ($current['harga_pokok'] ?? 0);
            $hargaJualOld = (int) ($current['harga_jual'] ?? 0);
            if ($hargaPokokOld === $hargaPokokNew && $hargaJualOld === $hargaJualNew) {
                continue;
            }

            $targetMargin = $hargaPokokNew > 0
                ? round((($hargaJualNew - $hargaPokokNew) / $hargaPokokNew) * 100, 1)
                : 0;

            $this->db->table('prodmast_store')
                ->where('toko_id', $toko_id)
                ->where('kode_item', $kodeItem)
                ->where('sat_id', $satId)
                ->update([
                    'harga_pokok' => $hargaPokokNew,
                    'harga_jual' => $hargaJualNew,
                    'target_psn_margin' => $targetMargin,
                    'updid' => $username,
                    'updtime' => date('Y-m-d H:i:s'),
                ]);

            $this->db->table('history_harga_beli')->insert([
                'toko_id' => $toko_id,
                'beli_id' => $source_beli_id !== '' ? $source_beli_id : 'KOREKSI',
                'kode_item' => $kodeItem,
                'sat_id' => $satId,
                'harga_pokok_old' => $hargaPokokOld,
                'harga_pokok_new' => $hargaPokokNew,
                'harga_jual_old' => $hargaJualOld,
                'harga_jual_new' => $hargaJualNew,
            ]);

            $saved++;
        }

        $this->db->transComplete();

        if (!$this->db->transStatus()) {
            return ['tipe' => 'error', 'data' => 'Gagal menyimpan koreksi harga'];
        }
        if ($saved === 0) {
            return ['tipe' => 'error', 'data' => 'Tidak ada nominal harga yang berubah'];
        }

        return ['tipe' => 'success', 'data' => "Koreksi harga berhasil disimpan untuk {$saved} baris"];
    }

    private function isGramasiUnit(array $arrayGramasi, string $sat_id): bool
    {
        $normalized = strtoupper(trim($sat_id));
        foreach ($arrayGramasi as $keyword) {
            if (str_contains($normalized, $keyword)) {
                return true;
            }
        }

        return false;
    }
}
