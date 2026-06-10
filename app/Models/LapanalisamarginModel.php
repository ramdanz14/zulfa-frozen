<?php

namespace App\Models;

use CodeIgniter\Model;

class LapanalisamarginModel extends Model
{
    public function getReport(array $params, string $sessionTokoId, bool $allowMultiStore = false): array
    {
        [$where, $binds] = $this->buildFilter($params, $sessionTokoId, $allowMultiStore);

        $summary = $this->db->query(
            "SELECT
                COALESCE(SUM(j.netto), 0) AS total_gross_sales,
                COALESCE(SUM(j.total_hpp), 0) AS total_sales_hpp,
                COALESCE(SUM(j.margin_bruto), 0) AS total_margin,
                CASE
                    WHEN COALESCE(SUM(j.netto), 0) = 0 THEN 0
                    ELSE ROUND((COALESCE(SUM(j.margin_bruto), 0) / COALESCE(SUM(j.netto), 0)) * 100, 2)
                END AS gross_margin_percent
             FROM penjualan j
             $where",
            $binds
        )->getRowArray() ?: [];

        $categoryRows = $this->db->query(
            "SELECT
                COALESCE(NULLIF(p.kat_id, ''), 'TANPA-KATEGORI') AS kat_id,
                COUNT(DISTINCT CONCAT(j.toko_id, '|', j.jual_id)) AS jumlah_transaksi,
                COALESCE(SUM(d.qty_jual), 0) AS total_qty,
                COALESCE(SUM(d.netto), 0) AS total_gross_sales,
                COALESCE(SUM(d.qty_jual * d.harga_pokok), 0) AS total_sales_hpp,
                COALESCE(SUM(d.netto - (d.qty_jual * d.harga_pokok)), 0) AS total_margin,
                CASE
                    WHEN COALESCE(SUM(d.netto), 0) = 0 THEN 0
                    ELSE ROUND((COALESCE(SUM(d.netto - (d.qty_jual * d.harga_pokok)), 0) / COALESCE(SUM(d.netto), 0)) * 100, 2)
                END AS gross_margin_percent
             FROM penjualan j
             INNER JOIN penjualan_detail d ON d.toko_id=j.toko_id AND d.jual_id=j.jual_id
             LEFT JOIN prodmast p ON p.kode_item=d.kode_item
             $where
             GROUP BY COALESCE(NULLIF(p.kat_id, ''), 'TANPA-KATEGORI')
             ORDER BY gross_margin_percent ASC, total_margin ASC, kat_id ASC",
            $binds
        )->getResultArray();

        return [
            'summary' => [
                'total_gross_sales' => (float) ($summary['total_gross_sales'] ?? 0),
                'total_sales_hpp' => (float) ($summary['total_sales_hpp'] ?? 0),
                'total_margin' => (float) ($summary['total_margin'] ?? 0),
                'gross_margin_percent' => (float) ($summary['gross_margin_percent'] ?? 0),
            ],
            'categories' => array_map([$this, 'normalizeMoneyRow'], $categoryRows),
        ];
    }

    public function getDetail(array $params, string $sessionTokoId, bool $allowMultiStore = false): array
    {
        $katId = trim((string) ($params['kat_id'] ?? ''));
        [$where, $binds] = $this->buildFilter($params, $sessionTokoId, $allowMultiStore);

        if ($katId === 'TANPA-KATEGORI') {
            $where .= " AND COALESCE(NULLIF(p.kat_id, ''), 'TANPA-KATEGORI') = :kat_id:";
            $binds['kat_id'] = $katId;
        } else {
            $where .= " AND p.kat_id = :kat_id:";
            $binds['kat_id'] = $katId;
        }

        $rows = $this->db->query(
            "SELECT
                COALESCE(NULLIF(p.kat_id, ''), 'TANPA-KATEGORI') AS kat_id,
                d.kode_item,
                COALESCE(p.nama_item, d.kode_item) AS nama_item,
                GROUP_CONCAT(DISTINCT d.toko_id ORDER BY d.toko_id SEPARATOR ', ') AS daftar_toko,
                COALESCE(SUM(d.qty_jual), 0) AS total_qty,
                COUNT(DISTINCT CONCAT(j.toko_id, '|', j.jual_id)) AS jumlah_transaksi,
                COALESCE(SUM(d.netto), 0) AS total_gross_sales,
                COALESCE(SUM(d.qty_jual * d.harga_pokok), 0) AS total_sales_hpp,
                COALESCE(SUM(d.netto - (d.qty_jual * d.harga_pokok)), 0) AS total_margin,
                CASE
                    WHEN COALESCE(SUM(d.netto), 0) = 0 THEN 0
                    ELSE ROUND((COALESCE(SUM(d.netto - (d.qty_jual * d.harga_pokok)), 0) / COALESCE(SUM(d.netto), 0)) * 100, 2)
                END AS gross_margin_percent
             FROM penjualan j
             INNER JOIN penjualan_detail d ON d.toko_id=j.toko_id AND d.jual_id=j.jual_id
             LEFT JOIN prodmast p ON p.kode_item=d.kode_item
             $where
             GROUP BY COALESCE(NULLIF(p.kat_id, ''), 'TANPA-KATEGORI'), d.kode_item, COALESCE(p.nama_item, d.kode_item)
             ORDER BY gross_margin_percent ASC, total_margin ASC, d.kode_item ASC",
            $binds
        )->getResultArray();

        return array_map([$this, 'normalizeMoneyRow'], $rows);
    }

    private function buildFilter(array $params, string $sessionTokoId, bool $allowMultiStore): array
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

        return [
            " WHERE j.tgl BETWEEN :start_date: AND :end_date: AND j.toko_id IN (" . implode(',', $storePlaceholders) . ")",
            $binds,
        ];
    }

    private function resolveStoreIds($rawStoreIds, string $sessionTokoId, bool $allowMultiStore): array
    {
        if (!$allowMultiStore) {
            return [$sessionTokoId];
        }

        $storeIds = is_array($rawStoreIds) ? $rawStoreIds : [$rawStoreIds];
        $storeIds = array_values(array_unique(array_filter(array_map(static function ($storeId) {
            return trim((string) $storeId);
        }, $storeIds))));

        if (empty($storeIds)) {
            $rows = $this->db->query("SELECT toko_id FROM toko ORDER BY toko_id")->getResultArray();
            $storeIds = array_map(static fn($row) => (string) ($row['toko_id'] ?? ''), $rows);
            $storeIds = array_values(array_filter($storeIds));
        }

        return !empty($storeIds) ? $storeIds : [$sessionTokoId];
    }

    private function normalizeDateFilter($value): string
    {
        $value = trim((string) $value);
        if ($value === '') {
            return '';
        }

        $timestamp = strtotime($value);
        return $timestamp ? date('Y-m-d', $timestamp) : '';
    }

    private function normalizeMoneyRow(array $row): array
    {
        foreach (['total_qty', 'jumlah_transaksi', 'total_gross_sales', 'total_sales_hpp', 'total_margin', 'gross_margin_percent'] as $key) {
            if (array_key_exists($key, $row)) {
                $row[$key] = (float) $row[$key];
            }
        }

        return $row;
    }
}
