<?php

namespace App\Models;

use CodeIgniter\Model;

class StockModel extends Model
{
    protected $table = 'stmast';
    protected $returnType = 'array';
    protected $protectFields = false;

    public function ajaxList(array $params, string $tokoId, string $jenis = 'rupiah', string $urutan = 'saldo'): array
    {
        $start = (int) ($params['start'] ?? 0);
        $length = $params['length'] ?? 25;
        $search = trim((string) ($params['search_value'] ?? ''));
        $queryLimit = $length !== '-1' ? ' LIMIT ' . $start . ', ' . (int) $length : '';

        $binds = ['toko_id' => $tokoId];
        $outerWhere = '';
        if ($search !== '') {
            $outerWhere = " WHERE (kode_item LIKE :search: OR nama_item LIKE :search: OR kat_id LIKE :search: OR stok_konversi LIKE :search:) ";
            $binds['search'] = '%' . $this->db->escapeLikeString($search) . '%';
        }

        $baseSql = $this->buildBaseStockSql($jenis, $urutan);

        $totalRow = $this->db->query(
            "SELECT COUNT(*) AS total FROM ({$this->buildBaseStockSql($jenis,$urutan, false)}) stock_rows",
            ['toko_id' => $tokoId]
        )->getRowArray();

        $filtered = (int) ($totalRow['total'] ?? 0);
        if ($outerWhere !== '') {
            $filteredRow = $this->db->query(
                "SELECT COUNT(*) AS total FROM ({$this->buildBaseStockSql($jenis,$urutan, false)}) stock_rows $outerWhere",
                $binds
            )->getRowArray();
            $filtered = (int) ($filteredRow['total'] ?? 0);
        }

        $data = $this->db->query(
            "SELECT * FROM ({$baseSql}) stock_rows
             $outerWhere
             $queryLimit",
            $binds
        )->getResultArray();

        return [
            'data' => $data,
            'total_count' => (int) ($totalRow['total'] ?? 0),
            'total_filtered' => $filtered,
            'summary' => $this->getSummary($tokoId, $jenis),
        ];
    }

    public function getSummary(string $tokoId, string $jenis = 'rupiah'): array
    {
        helper('number');

        $row = $this->db->query(
            "SELECT COALESCE(SUM(qty), 0) AS total_qty,
                    COALESCE(SUM(rp_saldo_akh), 0) AS total_rp
             FROM stmast
             WHERE toko_id=:toko_id:",
            ['toko_id' => $tokoId]
        )->getRowArray() ?: [];

        $totalQty = (float) ($row['total_qty'] ?? 0);
        $totalRp = (float) ($row['total_rp'] ?? 0);
        $jenis = $jenis === 'qty' ? 'qty' : 'rupiah';

        return [
            'jenis' => $jenis,
            'total_qty' => $totalQty,
            'total_rp' => $totalRp,
            'label' => $jenis === 'qty'
                ? 'Total Stock Qty ' . number_format($totalQty, 0, ',', '.')
                : 'Total Stock Rp.' . number_to_amount($totalRp, 3, 'id'),
        ];
    }

    public function getItemHistory(string $tokoId, string $kodeItem): array
    {
        $item = $this->db->query(
            "SELECT p.kode_item, p.nama_item, p.kat_id,
                    COALESCE(st.begbal, 0) AS begbal,
                    COALESCE(st.qty, 0) AS qty_akhir,
                    COALESCE(st.acost, 0) AS acost,
                    COALESCE(base.sat_id, '') AS sat_dasar
             FROM prodmast p
             LEFT JOIN stmast st ON st.toko_id=:toko_id: AND st.kode_item=p.kode_item
             LEFT JOIN prodmast_satuan base ON base.kode_item=p.kode_item AND base.qty_konversi=1
             WHERE p.kode_item=:kode_item:
             LIMIT 1",
            [
                'toko_id' => $tokoId,
                'kode_item' => $kodeItem,
            ]
        )->getRowArray();

        $lso = $this->db->query(
            "SELECT last_so from  prodmast_store WHERE toko_id=:toko_id: AND kode_item=:kode_item:
             LIMIT 1",
            [
                'toko_id' => $tokoId,
                'kode_item' => $kodeItem,
            ]
        )->getRowArray();

        if (!$item) {
            return [];
        }

        $history = $this->db->query(
            "SELECT tanggal,
                    SUM(beli) AS beli,
                    SUM(retur_beli) AS retur_beli,
                    SUM(jual) AS jual,
                    SUM(retur_jual) AS retur_jual,
                    SUM(adj) AS adj
             FROM (
                SELECT DATE(p.tanggal) AS tanggal,
                       SUM(pd.qty_stock) AS beli,
                       0 AS retur_beli,
                       0 AS jual,
                       0 AS retur_jual,
                       0 AS adj
                FROM pembelian_detail pd
                INNER JOIN pembelian p ON p.toko_id=pd.toko_id AND p.beli_id=pd.beli_id
                WHERE pd.toko_id=:toko_id:
                  AND pd.kode_item=:kode_item:
                  AND p.status_nota='TERIMA'
                  AND EXTRACT(YEAR_MONTH FROM p.tanggal)=EXTRACT(YEAR_MONTH FROM CURDATE())
                GROUP BY DATE(p.tanggal)

                UNION ALL

                SELECT DATE(r.tanggal) AS tanggal,
                       0 AS beli,
                       SUM(rd.qty_stok) AS retur_beli,
                       0 AS jual,
                       0 AS retur_jual,
                       0 AS adj
                FROM pembelian_retur_detail rd
                INNER JOIN pembelian_retur r ON r.toko_id=rd.toko_id AND r.retur_id=rd.retur_id
                WHERE rd.toko_id=:toko_id:
                  AND rd.kode_item=:kode_item:
                  AND r.status_retur='SELESAI'
                  AND EXTRACT(YEAR_MONTH FROM r.tanggal)=EXTRACT(YEAR_MONTH FROM CURDATE())
                GROUP BY DATE(r.tanggal)

                UNION ALL

                SELECT DATE(j.tgl) AS tanggal,
                       0 AS beli,
                       0 AS retur_beli,
                       SUM(d.qty_stock) AS jual,
                       0 AS retur_jual,
                       0 AS adj
                FROM penjualan_detail d
                INNER JOIN penjualan j ON j.toko_id=d.toko_id AND j.jual_id=d.jual_id
                WHERE d.toko_id=:toko_id:
                  AND d.kode_item=:kode_item:
                  AND EXTRACT(YEAR_MONTH FROM j.tgl)=EXTRACT(YEAR_MONTH FROM CURDATE())
                GROUP BY DATE(j.tgl)

                UNION ALL

                SELECT DATE(rj.tanggal) AS tanggal,
                       0 AS beli,
                       0 AS retur_beli,
                       0 AS jual,
                       SUM(d.qty_stock) AS retur_jual,
                       0 AS adj
                FROM retur_jual_detail d
                INNER JOIN retur_jual rj ON rj.toko_id=d.toko_id AND rj.rj_id=d.rj_id
                WHERE d.toko_id=:toko_id:
                  AND d.kode_item=:kode_item:
                  AND EXTRACT(YEAR_MONTH FROM rj.tanggal)=EXTRACT(YEAR_MONTH FROM CURDATE())
                GROUP BY DATE(rj.tanggal)

                UNION ALL

                SELECT DATE(a.tanggal) AS tanggal,
                       0 AS beli,
                       0 AS retur_beli,
                       0 AS jual,
                       0 AS retur_jual,
                       SUM(a.qty_stock) AS adj
                FROM `adjust` a
                WHERE a.toko_id=:toko_id:
                  AND a.kode_item=:kode_item:
                  AND EXTRACT(YEAR_MONTH FROM a.tanggal)=EXTRACT(YEAR_MONTH FROM CURDATE())
                GROUP BY DATE(a.tanggal)
             ) movement
             GROUP BY tanggal
             ORDER BY tanggal ASC",
            [
                'toko_id' => $tokoId,
                'kode_item' => $kodeItem,
            ]
        )->getResultArray();

        $runningQty = (float) ($item['begbal'] ?? 0);
        $rows = [[
            'tanggal' => date('Y-m-01'),
            'label' => 'Saldo Awal',
            'beli' => 0,
            'retur_beli' => 0,
            'jual' => 0,
            'retur_jual' => 0,
            'adj' => 0,
            'saldo_akhir' => $runningQty,
            'detail' => 'Saldo awal periode',
        ]];

        foreach ($history as $row) {
            $beli = (float) ($row['beli'] ?? 0);
            $returBeli = (float) ($row['retur_beli'] ?? 0);
            $jual = (float) ($row['jual'] ?? 0);
            $returJual = (float) ($row['retur_jual'] ?? 0);
            $adj = (float) ($row['adj'] ?? 0);
            $runningQty += $beli - $returBeli - $jual + $returJual + $adj;

            $detail = [];
            if ($jual != 0) {
                $detail[] = 'Sales: ' . $this->formatPlainNumber($jual);
            }
            if ($beli != 0) {
                $detail[] = 'Beli: ' . $this->formatPlainNumber($beli);
            }
            if ($returBeli != 0) {
                $detail[] = 'Retur Beli: ' . $this->formatPlainNumber($returBeli);
            }
            if ($returJual != 0) {
                $detail[] = 'Retur Jual: ' . $this->formatPlainNumber($returJual);
            }
            if ($adj != 0) {
                $detail[] = 'Adj: ' . $this->formatPlainNumber($adj);
            }

            $rows[] = [
                'tanggal' => $row['tanggal'],
                'label' => 'Mutasi Harian',
                'beli' => $beli,
                'retur_beli' => $returBeli,
                'jual' => $jual,
                'retur_jual' => $returJual,
                'adj' => $adj,
                'saldo_akhir' => $runningQty,
                'detail' => implode(' | ', $detail),
            ];
        }

        return [
            'item' => [
                'kode_item' => $item['kode_item'],
                'nama_item' => $item['nama_item'],
                'last_so' => $lso['last_so'],
                'kat_id' => $item['kat_id'],
                'sat_dasar' => $item['sat_dasar'],
                'begbal' => (float) ($item['begbal'] ?? 0),
                'qty_akhir' => (float) ($item['qty_akhir'] ?? 0),
                'acost' => (float) ($item['acost'] ?? 0),
            ],
            'rows' => $rows,
        ];
    }

    private function buildBaseStockSql(string $jenis, string $urutan, bool $includeOrder = true): string
    {
        $metricSaldo = $jenis === 'qty' ? 'qty' : 'rp_saldo_akh';
        $multiplier = $jenis === 'qty' ? '1' : 'acost';

        $orderBy = ' ORDER BY kat_id, nama_item, kode_item ';
        if ($urutan === 'saldo') {
            $orderBy = " ORDER BY {$metricSaldo} DESC, nama_item, kode_item ";
        }

        $sql = "SELECT kode_item,
                       nama_item,
                       kat_id,
                       begbal * {$multiplier} AS begbal,
                       beli * {$multiplier} AS beli,
                       (retur_beli * -1) * {$multiplier} AS retur_beli,
                       (jual * -1) * {$multiplier} AS jual,
                       retur_jual * {$multiplier} AS retur_jual,
                       adj * {$multiplier} AS adj,
                       qty AS qty_raw,
                       acost,
                       rp_saldo_akh,
                       {$metricSaldo} AS saldo_akhir,
                       MAX(sat_dasar) AS satuan_dasar,
                       REPLACE(GROUP_CONCAT(CONCAT(qty_konv, ' ', sat_id) SEPARATOR ' , '), '.0', '') AS stok_konversi
                FROM (
                    SELECT p.kode_item,
                           p.nama_item,
                           p.kat_id,
                           COALESCE(st.qty, 0) AS qty,
                           COALESCE(st.begbal, 0) AS begbal,
                           COALESCE(st.beli, 0) AS beli,
                           COALESCE(st.retur_beli, 0) AS retur_beli,
                           COALESCE(st.jual, 0) AS jual,
                           COALESCE(st.retur_jual, 0) AS retur_jual,
                           COALESCE(st.adj, 0) AS adj,
                           COALESCE(st.acost, 0) AS acost,
                           COALESCE(st.rp_saldo_akh, 0) AS rp_saldo_akh,
                           IF(ps.qty_konversi=1, ps.sat_id, '') AS sat_dasar,
                           ps.sat_id,
                           ROUND(COALESCE(st.qty, 0) / NULLIF(ps.qty_konversi, 0), 1) AS qty_konv
                    FROM stmast st
                    LEFT JOIN prodmast p ON p.kode_item=st.kode_item
                    LEFT JOIN prodmast_satuan ps ON ps.kode_item=st.kode_item
                    WHERE st.toko_id=:toko_id:
                ) stock_base
                GROUP BY kode_item, nama_item, kat_id, qty, acost, rp_saldo_akh";

        if ($includeOrder) {
            $sql .= $orderBy;
        }

        return $sql;
    }

    private function formatPlainNumber(float $value): string
    {
        $rounded = round($value, 2);
        if (floor($rounded) == $rounded) {
            return number_format($rounded, 0, ',', '.');
        }

        return number_format($rounded, 2, ',', '.');
    }
}
