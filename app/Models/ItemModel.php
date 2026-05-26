<?php

namespace App\Models;

use CodeIgniter\Model;

class ItemModel extends Model
{
    protected $table = 'prodmast';
    protected $primaryKey = 'kode_item';
    protected $returnType = 'array';
    protected $protectFields = false;

    public function ajax(array $params, string $toko_id): array
    {
        $start = (int) ($params['start'] ?? 0);
        $length = $params['length'] ?? 25;
        $search_value = trim((string) ($params['search_value'] ?? ''));
        $queryLimit = $length != "-1" ? " LIMIT $start, " . (int) $length : "";

        $where = "";
        $binds = ['toko_id' => $toko_id];
        if ($search_value !== '') {
            $where = " AND (p.kode_item LIKE :search: OR p.nama_item LIKE :search: OR p.kat_id LIKE :search:)";
            $binds['search'] = '%' . $this->db->escapeLikeString($search_value) . '%';
        }

        $total = $this->db->query("SELECT COUNT(*) total FROM prodmast")->getRow()->total ?? 0;
        $filtered = $total;
        if ($search_value !== '') {
            $filtered = $this->db->query("SELECT COUNT(*) total FROM prodmast p WHERE 1=1 $where", $binds)->getRow()->total ?? 0;
        }

        $data = $this->db->query("
            SELECT p.kode_item,p.nama_item,p.kat_id,
                CASE WHEN SUM(CASE WHEN ps.status_item='Y' THEN 1 ELSE 0 END) > 0 THEN 'Y' ELSE 'N' END status_item
            FROM prodmast p
            LEFT JOIN prodmast_store ps ON ps.kode_item=p.kode_item AND ps.toko_id=:toko_id:
            WHERE 1=1 $where
            GROUP BY p.kode_item,p.nama_item,p.kat_id
            $queryLimit
        ", $binds)->getResultArray();

        return [
            'data' => $data,
            'total_count' => $total,
            'total_filtered' => $filtered
        ];
    }

    public function getLastID(): string
    {
        $maxNow = $this->db->query("SELECT MAX(CAST(MID(kode_item,3,5) AS UNSIGNED)) as kodex FROM prodmast")->getRow();
        $noUrut = (int) ($maxNow->kodex ?? 0);
        $noUrut++;
        return "BR" . sprintf("%05s", $noUrut);
    }

    public function getKategoriOptions(): array
    {
        return $this->db->query("SELECT kat_id FROM kategori ORDER BY kat_id")->getResultArray();
    }

    public function getSupplierOptions(): array
    {
        return $this->db->query("SELECT supco,nama FROM supmast ORDER BY nama")->getResultArray();
    }

    public function getSatuanOptions(): array
    {
        return $this->db->query("SELECT sat_id FROM satuan ORDER BY sat_id")->getResultArray();
    }

    public function getEditData(string $kode_item, string $toko_id): array
    {
        $prodmast = $this->db->query("SELECT * FROM prodmast WHERE kode_item=:kode_item:", ['kode_item' => $kode_item])->getRowArray();
        $satuan = $this->db->query("SELECT sat_id,qty_konversi FROM prodmast_satuan WHERE kode_item=:kode_item: ORDER BY qty_konversi,sat_id", ['kode_item' => $kode_item])->getResultArray();
        $store = $this->db->query("SELECT sat_id,harga_pokok,harga_jual,target_psn_margin,status_item FROM prodmast_store WHERE kode_item=:kode_item: AND toko_id=:toko_id: ORDER BY sat_id", ['kode_item' => $kode_item, 'toko_id' => $toko_id])->getResultArray();
        return ['prodmast' => $prodmast, 'satuan' => $satuan, 'store' => $store];
    }

    public function getDetailData(string $kode_item): array
    {
        $toko_id = session('toko_id');
        $prodmast = $this->db->query("SELECT * FROM prodmast WHERE kode_item=:kode_item:", ['kode_item' => $kode_item])->getRowArray();
        $satuan = $this->db->query("SELECT * FROM prodmast_satuan WHERE kode_item=:kode_item: ORDER BY qty_konversi,sat_id", ['kode_item' => $kode_item])->getResultArray();
        $store = $this->db->query("SELECT * FROM prodmast_store WHERE kode_item=:kode_item: and toko_id=:toko_id: ORDER BY toko_id,sat_id", ['kode_item' => $kode_item, 'toko_id' => $toko_id])->getResultArray();
        return ['prodmast' => $prodmast, 'satuan' => $satuan, 'store' => $store];
    }
}
