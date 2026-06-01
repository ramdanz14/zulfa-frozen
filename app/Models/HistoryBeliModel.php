<?php

namespace App\Models;

use CodeIgniter\Model;

class HistoryBeliModel extends Model
{
    protected $table = 'history_harga_beli';
    protected $returnType = 'array';
    protected $protectFields = false;

    public function ajaxList(array $params, string $toko_id): array
    {
        $start = (int) ($params['start'] ?? 0);
        $length = $params['length'] ?? 25;
        $search = trim((string) ($params['search_value'] ?? ''));
        $jenis = trim((string) ($params['jenis'] ?? ''));
        $queryLimit = $length !== '-1' ? " LIMIT $start, " . (int) $length : '';

        $binds = ['toko_id' => $toko_id];
        $where = " WHERE a.toko_id=:toko_id: ";

        if (in_array($jenis, ['naik', 'turun'], true)) {
            $where .= " AND IF(a.harga_pokok_old<a.harga_pokok_new,'naik','turun')=:jenis: ";
            $binds['jenis'] = $jenis;
        }

        $baseSql = "
            FROM history_harga_beli a
            LEFT JOIN prodmast b ON b.kode_item=a.kode_item
            $where
        ";

        $searchSql = '';
        if ($search !== '') {
            $searchSql = " AND (
                a.kode_item LIKE :search:
                OR b.nama_item LIKE :search:
                OR a.sat_id LIKE :search:
                OR a.beli_id LIKE :search:
            )";
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
            "SELECT a.*, b.nama_item,
                    IF(a.harga_pokok_old<a.harga_pokok_new,'naik','turun') AS jenis
             $baseSql
             $searchSql
             ORDER BY a.updtime DESC, a.history_id DESC
             $queryLimit",
            $binds
        )->getResultArray();

        return [
            'data' => $data,
            'total_count' => (int) ($totalRow['total'] ?? 0),
            'total_filtered' => (int) $filtered,
        ];
    }
}
