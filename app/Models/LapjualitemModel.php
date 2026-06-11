<?php

namespace App\Models;

use CodeIgniter\Model;

class LapjualitemModel extends Model
{
    protected $returnType = 'array';
    protected $protectFields = false;

    public function getReport(array $params, string $sessionTokoId, bool $allowMultiStore): array
    {
        $dateStart = $this->validDate((string) ($params['date_start'] ?? ''), date('Y-m-01'));
        $dateEnd = $this->validDate((string) ($params['date_end'] ?? ''), date('Y-m-t'));
        if ($dateEnd < $dateStart) {
            [$dateStart, $dateEnd] = [$dateEnd, $dateStart];
        }

        $tokoId = $this->resolveStoreId($params['toko_id'] ?? '', $sessionTokoId, $allowMultiStore);
        $totalStruk = $this->countTotalStruk($dateStart, $dateEnd, $tokoId);
        $rows = $this->queryRows($dateStart, $dateEnd, $tokoId, $totalStruk);

        return [
            'date_start' => $dateStart,
            'date_end' => $dateEnd,
            'toko' => $this->getStore($tokoId),
            'summary' => $this->buildSummary($rows, $totalStruk),
            'rows' => $rows,
        ];
    }

    private function countTotalStruk(string $dateStart, string $dateEnd, string $tokoId): int
    {
        $row = $this->db->query(
            "SELECT COUNT(DISTINCT j.jual_id) AS total_struk
             FROM penjualan j
             WHERE j.toko_id=:toko_id:
               AND DATE(j.tgl) BETWEEN :date_start: AND :date_end:",
            [
                'toko_id' => $tokoId,
                'date_start' => $dateStart,
                'date_end' => $dateEnd,
            ]
        )->getRowArray();

        return (int) ($row['total_struk'] ?? 0);
    }

    private function queryRows(string $dateStart, string $dateEnd, string $tokoId, int $totalStruk): array
    {
        $rows = $this->db->query(
            "SELECT d.kode_item,
                    COALESCE(p.nama_item, d.kode_item) AS nama_item,
                    COALESCE(p.kat_id, '-') AS kat_id,
                    COUNT(DISTINCT j.jual_id) AS jumlah_struk_item,
                    COALESCE(SUM(d.qty_stock),0) AS total_qty,
                    COALESCE(SUM(d.qty_jual * d.harga_pokok),0) AS total_hpp,
                    COALESCE(SUM(d.gross),0) AS total_gross,
                    COALESCE(SUM(d.diskon_item),0) AS total_diskon,
                    COALESCE(SUM(d.netto - (d.qty_jual * d.harga_pokok)),0) AS total_margin,
                    CASE
                        WHEN :total_struk: > 0
                        THEN ROUND((COUNT(DISTINCT j.jual_id) / :total_struk:) * 100, 2)
                        ELSE 0
                    END AS attach_rate,
                    CASE
                        WHEN COUNT(DISTINCT j.jual_id) > 0
                        THEN ROUND(COALESCE(SUM(d.qty_stock),0) / COUNT(DISTINCT j.jual_id), 2)
                        ELSE 0
                    END AS qty_per_struk
             FROM penjualan j
             INNER JOIN penjualan_detail d ON d.toko_id=j.toko_id AND d.jual_id=j.jual_id
             LEFT JOIN prodmast p ON p.kode_item=d.kode_item
             WHERE j.toko_id=:toko_id:
               AND DATE(j.tgl) BETWEEN :date_start: AND :date_end:
             GROUP BY d.kode_item, COALESCE(p.nama_item, d.kode_item), COALESCE(p.kat_id, '-')
             ORDER BY total_margin DESC, total_gross DESC, d.kode_item",
            [
                'total_struk' => $totalStruk,
                'toko_id' => $tokoId,
                'date_start' => $dateStart,
                'date_end' => $dateEnd,
            ]
        )->getResultArray();

        foreach ($rows as &$row) {
            $row['jumlah_struk_item'] = (int) ($row['jumlah_struk_item'] ?? 0);
            foreach ([
                'total_qty',
                'total_hpp',
                'total_gross',
                'total_diskon',
                'total_margin',
                'attach_rate',
                'qty_per_struk',
            ] as $key) {
                $row[$key] = (float) ($row[$key] ?? 0);
            }
        }

        return $rows;
    }

    private function buildSummary(array $rows, int $totalStruk): array
    {
        $summary = [
            'total_struk' => $totalStruk,
            'total_item' => count($rows),
            'jumlah_struk_item' => 0,
            'total_qty' => 0.0,
            'total_hpp' => 0.0,
            'total_gross' => 0.0,
            'total_diskon' => 0.0,
            'total_margin' => 0.0,
            'avg_attach_rate' => 0.0,
            'avg_qty_per_struk' => 0.0,
        ];

        foreach ($rows as $row) {
            $summary['jumlah_struk_item'] += (int) ($row['jumlah_struk_item'] ?? 0);
            $summary['total_qty'] += (float) ($row['total_qty'] ?? 0);
            $summary['total_hpp'] += (float) ($row['total_hpp'] ?? 0);
            $summary['total_gross'] += (float) ($row['total_gross'] ?? 0);
            $summary['total_diskon'] += (float) ($row['total_diskon'] ?? 0);
            $summary['total_margin'] += (float) ($row['total_margin'] ?? 0);
            $summary['avg_attach_rate'] += (float) ($row['attach_rate'] ?? 0);
            $summary['avg_qty_per_struk'] += (float) ($row['qty_per_struk'] ?? 0);
        }

        if (!empty($rows)) {
            $summary['avg_attach_rate'] = round($summary['avg_attach_rate'] / count($rows), 2);
            $summary['avg_qty_per_struk'] = round($summary['avg_qty_per_struk'] / count($rows), 2);
        }

        return $summary;
    }

    private function getStore(string $tokoId): array
    {
        $row = $this->db->query(
            "SELECT toko_id, toko_nama FROM toko WHERE toko_id=:toko_id: LIMIT 1",
            ['toko_id' => $tokoId]
        )->getRowArray();

        return $row ?: ['toko_id' => $tokoId, 'toko_nama' => $tokoId];
    }

    private function resolveStoreId($rawTokoId, string $sessionTokoId, bool $allowMultiStore): string
    {
        if (!$allowMultiStore) {
            return $sessionTokoId;
        }

        $tokoId = trim((string) $rawTokoId);
        return $tokoId !== '' ? $tokoId : $sessionTokoId;
    }

    private function validDate(string $date, string $fallback): string
    {
        $timestamp = strtotime($date);
        return $timestamp ? date('Y-m-d', $timestamp) : $fallback;
    }
}
