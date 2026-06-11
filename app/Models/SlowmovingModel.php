<?php

namespace App\Models;

use CodeIgniter\Model;

class SlowmovingModel extends Model
{
    protected $returnType = 'array';
    protected $protectFields = false;

    public function getReport(array $params, string $sessionTokoId, bool $allowStoreSelect): array
    {
        $tokoId = $this->resolveStoreId($params['toko_id'] ?? '', $sessionTokoId, $allowStoreSelect);
        $rows = $this->queryRows($tokoId);

        return [
            'as_of' => date('Y-m-d H:i:s'),
            'toko' => $this->getStore($tokoId),
            'summary' => $this->buildSummary($rows),
            'rows' => $rows,
        ];
    }

    private function queryRows(string $tokoId): array
    {
        $rows = $this->db->query(
            "SELECT st.kode_item,
                    COALESCE(p.nama_item, st.kode_item) AS nama_item,
                    COALESCE(p.kat_id, '-') AS kat_id,
                    COALESCE(store.supco, p.supco, '') AS supco,
                    COALESCE(store.sat_id, '') AS sat_id,
                    COALESCE(store.status_item, 'Y') AS status_item,
                    COALESCE(st.qty,0) AS qty,
                    COALESCE(st.spd,0) AS spd,
                    COALESCE(st.acost,0) AS acost,
                    COALESCE(st.rp_saldo_akh,0) AS nilai_stok,
                    COALESCE(st.beli,0) AS beli_bulan_ini,
                    COALESCE(st.jual,0) AS jual_bulan_ini,
                    COALESCE(st.retur_jual,0) AS retur_jual_bulan_ini,
                    COALESCE(store.harga_pokok, st.acost, 0) AS harga_pokok,
                    COALESCE(store.harga_jual, 0) AS harga_jual,
                    COALESCE(store.target_psn_margin, 0) AS target_psn_margin,
                    COALESCE(store.last_so, '') AS last_so,
                    COALESCE(store.last_beli, '') AS last_beli,
                    COALESCE(store.last_jual, '') AS last_jual,
                    CASE
                        WHEN COALESCE(st.spd,0) > 0
                        THEN ROUND(COALESCE(st.qty,0) / COALESCE(st.spd,0), 1)
                        WHEN COALESCE(st.qty,0) > 0
                        THEN 999999
                        ELSE 0
                    END AS cover_hari
             FROM stmast st
             LEFT JOIN prodmast p ON p.kode_item=st.kode_item
             LEFT JOIN (
                SELECT ps.toko_id, ps.kode_item, ps.sat_id, ps.supco, ps.harga_pokok, ps.harga_jual,
                       ps.target_psn_margin, ps.status_item, ps.last_so, ps.last_beli, ps.last_jual
                FROM prodmast_store ps
                INNER JOIN prodmast_satuan satuan
                    ON satuan.kode_item=ps.kode_item
                   AND satuan.sat_id=ps.sat_id
                   AND satuan.qty_konversi=1
             ) store ON store.toko_id=st.toko_id AND store.kode_item=st.kode_item
             WHERE st.toko_id=:toko_id:
               AND COALESCE(store.status_item, 'Y')='Y'
             ORDER BY
                CASE
                    WHEN COALESCE(st.qty,0) > 0 AND COALESCE(st.spd,0) <= 0 THEN 1
                    WHEN COALESCE(st.spd,0) > 0 AND (COALESCE(st.qty,0) / COALESCE(st.spd,0)) > 30 THEN 2
                    WHEN COALESCE(st.qty,0) <= 0 AND COALESCE(st.spd,0) > 0 THEN 3
                    WHEN COALESCE(st.spd,0) > 0 AND (COALESCE(st.qty,0) / COALESCE(st.spd,0)) <= 7 THEN 4
                    ELSE 5
                END,
                COALESCE(st.rp_saldo_akh,0) DESC,
                p.nama_item",
            ['toko_id' => $tokoId]
        )->getResultArray();

        foreach ($rows as &$row) {
            foreach ([
                'qty',
                'spd',
                'acost',
                'nilai_stok',
                'beli_bulan_ini',
                'jual_bulan_ini',
                'retur_jual_bulan_ini',
                'harga_pokok',
                'harga_jual',
                'target_psn_margin',
                'cover_hari',
            ] as $key) {
                $row[$key] = (float) ($row[$key] ?? 0);
            }

            $row['kategori_moving'] = $this->classifyRow($row);
            $row['rekomendasi'] = $this->recommendation($row);
        }

        return $rows;
    }

    private function buildSummary(array $rows): array
    {
        $summary = [
            'total_item' => count($rows),
            'fast_count' => 0,
            'normal_count' => 0,
            'slow_count' => 0,
            'dead_count' => 0,
            'stockout_count' => 0,
            'kosong_count' => 0,
            'total_qty' => 0.0,
            'total_nilai_stok' => 0.0,
            'slow_dead_nilai_stok' => 0.0,
            'avg_spd' => 0.0,
        ];

        foreach ($rows as $row) {
            $category = (string) ($row['kategori_moving'] ?? '');
            if ($category === 'FAST MOVING') {
                $summary['fast_count']++;
            } elseif ($category === 'NORMAL') {
                $summary['normal_count']++;
            } elseif ($category === 'SLOW MOVING') {
                $summary['slow_count']++;
                $summary['slow_dead_nilai_stok'] += (float) ($row['nilai_stok'] ?? 0);
            } elseif ($category === 'DEAD STOCK') {
                $summary['dead_count']++;
                $summary['slow_dead_nilai_stok'] += (float) ($row['nilai_stok'] ?? 0);
            } elseif ($category === 'POTENSI STOCKOUT') {
                $summary['stockout_count']++;
            } elseif ($category === 'STOK KOSONG') {
                $summary['kosong_count']++;
            }

            $summary['total_qty'] += (float) ($row['qty'] ?? 0);
            $summary['total_nilai_stok'] += (float) ($row['nilai_stok'] ?? 0);
            $summary['avg_spd'] += (float) ($row['spd'] ?? 0);
        }

        if (!empty($rows)) {
            $summary['avg_spd'] = round($summary['avg_spd'] / count($rows), 2);
        }

        return $summary;
    }

    private function classifyRow(array $row): string
    {
        $qty = (float) ($row['qty'] ?? 0);
        $spd = (float) ($row['spd'] ?? 0);
        $coverHari = (float) ($row['cover_hari'] ?? 0);

        if ($qty <= 0 && $spd > 0) {
            return 'POTENSI STOCKOUT';
        }
        if ($qty <= 0) {
            return 'STOK KOSONG';
        }
        if ($spd <= 0) {
            return 'DEAD STOCK';
        }
        if ($coverHari <= 7) {
            return 'FAST MOVING';
        }
        if ($coverHari <= 30) {
            return 'NORMAL';
        }

        return 'SLOW MOVING';
    }

    private function recommendation(array $row): string
    {
        $category = (string) ($row['kategori_moving'] ?? '');

        if ($category === 'POTENSI STOCKOUT') {
            return 'Prioritaskan restock, item masih bergerak tetapi stok kosong.';
        }
        if ($category === 'STOK KOSONG') {
            return 'Review kebutuhan restock sebelum pembelian berikutnya.';
        }
        if ($category === 'DEAD STOCK') {
            return 'Evaluasi promo, bundling, retur supplier, atau stop pembelian sementara.';
        }
        if ($category === 'SLOW MOVING') {
            return 'Tahan pembelian baru, cek harga jual, promo, dan ruang display.';
        }
        if ($category === 'FAST MOVING') {
            return 'Jaga minimum stock agar tidak kehabisan.';
        }

        return 'Monitor sesuai siklus pembelian normal.';
    }

    private function getStore(string $tokoId): array
    {
        $row = $this->db->query(
            "SELECT toko_id, toko_nama FROM toko WHERE toko_id=:toko_id: LIMIT 1",
            ['toko_id' => $tokoId]
        )->getRowArray();

        return $row ?: ['toko_id' => $tokoId, 'toko_nama' => $tokoId];
    }

    private function resolveStoreId($rawTokoId, string $sessionTokoId, bool $allowStoreSelect): string
    {
        if (!$allowStoreSelect) {
            return $sessionTokoId;
        }

        $tokoId = trim((string) $rawTokoId);
        return $tokoId !== '' ? $tokoId : $sessionTokoId;
    }
}
