<?php

namespace App\Models;

use CodeIgniter\Model;

class LapharianModel extends Model
{
    protected $returnType = 'array';
    protected $protectFields = false;

    public function getReport(array $params, string $sessionTokoId, bool $allowMultiStore): array
    {
        $tanggal = $this->normalizeDate($params['tanggal'] ?? '') ?: date('Y-m-d');
        $storeIds = $this->resolveStoreIds($params['toko_ids'] ?? [], $sessionTokoId, $allowMultiStore);
        [$storeSql, $binds] = $this->buildStoreFilter('toko_id', $storeIds);
        $dateStart = $tanggal . ' 00:00:00';
        $dateEnd = $tanggal . ' 23:59:59';

        $stores = $this->db->query(
            "SELECT toko_id, toko_nama, toko_alamat, toko_phone
             FROM toko
             WHERE toko_id IN (" . implode(',', array_fill(0, count($binds), '?')) . ")
             ORDER BY toko_id",
            array_values($binds)
        )->getResultArray();

        $posPayments = $this->db->query(
            "SELECT pp.cara_bayar, COALESCE(SUM(pp.nominal_bayar), 0) AS total
             FROM penjualan j
             INNER JOIN penjualan_pembayaran pp ON pp.toko_id=j.toko_id AND pp.jual_id=j.jual_id
             WHERE j.tgl BETWEEN ? AND ? AND j.$storeSql
             GROUP BY pp.cara_bayar",
            array_merge([$dateStart, $dateEnd], array_values($binds))
        )->getResultArray();

        $pos = ['TUNAI' => 0.0, 'TRANSFER' => 0.0, 'QRIS' => 0.0, 'POTONGAN_RETUR' => 0.0];
        foreach ($posPayments as $row) {
            $method = strtoupper((string) ($row['cara_bayar'] ?? ''));
            $pos[$method] = (float) ($row['total'] ?? 0);
        }

        $salesSummary = $this->db->query(
            "SELECT COUNT(DISTINCT j.jual_id) AS total_transaksi,
                    COALESCE(SUM(j.netto), 0) AS total_netto,
                    COALESCE(SUM(j.gross), 0) AS total_gross,
                    COALESCE(SUM(j.total_diskon_item), 0) AS total_diskon_item,
                    COALESCE(SUM(j.diskon_nota), 0) AS total_diskon_nota,
                    COALESCE(SUM(j.redeem_nominal), 0) AS total_redeem
             FROM penjualan j
             WHERE j.tgl BETWEEN ? AND ? AND j.$storeSql",
            array_merge([$dateStart, $dateEnd], array_values($binds))
        )->getRowArray() ?: [];

        $kasRows = $this->db->query(
            "SELECT ak.jenis_akun, km.nama_akun, COUNT(*) AS total_transaksi, COALESCE(SUM(km.nominal), 0) AS total
             FROM kas_mutasi km
             INNER JOIN akun_kas ak ON ak.nama_akun=km.nama_akun
             WHERE km.tanggal BETWEEN ? AND ? AND km.$storeSql
             GROUP BY ak.jenis_akun, km.nama_akun
             ORDER BY ak.jenis_akun, km.nama_akun",
            array_merge([$dateStart, $dateEnd], array_values($binds))
        )->getResultArray();

        $kasMasuk = 0.0;
        $kasKeluar = 0.0;
        foreach ($kasRows as &$row) {
            $row['total'] = (float) ($row['total'] ?? 0);
            if (($row['jenis_akun'] ?? '') === 'MASUK') {
                $kasMasuk += $row['total'];
            } else {
                $kasKeluar += $row['total'];
            }
        }
        unset($row);

        $supplierRows = $this->db->query(
            "SELECT pb.cara_bayar, p.supco, COALESCE(s.nama, p.supco) AS nama_supplier,
                    COUNT(*) AS total_transaksi, COALESCE(SUM(pb.jumlah_bayar), 0) AS total
             FROM pembelian_pembayaran pb
             INNER JOIN pembelian p ON p.toko_id=pb.toko_id AND p.beli_id=pb.beli_id
             LEFT JOIN supmast s ON s.supco=p.supco
             WHERE pb.tanggal_bayar BETWEEN ? AND ? AND pb.$storeSql
             GROUP BY pb.cara_bayar, p.supco, COALESCE(s.nama, p.supco)
             ORDER BY pb.cara_bayar, nama_supplier",
            array_merge([$dateStart, $dateEnd], array_values($binds))
        )->getResultArray();

        $supplierCash = 0.0;
        $supplierTotal = 0.0;
        foreach ($supplierRows as &$row) {
            $row['total'] = (float) ($row['total'] ?? 0);
            $supplierTotal += $row['total'];
            if (($row['cara_bayar'] ?? '') === 'TUNAI') {
                $supplierCash += $row['total'];
            }
        }
        unset($row);

        $customerRows = $this->db->query(
            "SELECT pp.cara_bayar, j.cust_id, COALESCE(c.nama, 'Pelanggan Umum') AS nama_customer,
                    COUNT(*) AS total_transaksi, COALESCE(SUM(pp.nominal_bayar), 0) AS total
             FROM penjualan_pembayaran pp
             INNER JOIN penjualan j ON j.toko_id=pp.toko_id AND j.jual_id=pp.jual_id
             LEFT JOIN customer c ON c.cust_id=j.cust_id
             WHERE pp.tgl_bayar BETWEEN ? AND ?
                AND DATE(j.tgl) <> ?
                AND j.is_kredit='1'
                AND pp.$storeSql
             GROUP BY pp.cara_bayar, j.cust_id, COALESCE(c.nama, 'Pelanggan Umum')
             ORDER BY pp.cara_bayar, nama_customer",
            array_merge([$dateStart, $dateEnd, $tanggal], array_values($binds))
        )->getResultArray();

        $customerCash = 0.0;
        $customerTotal = 0.0;
        foreach ($customerRows as &$row) {
            $row['total'] = (float) ($row['total'] ?? 0);
            $customerTotal += $row['total'];
            if (($row['cara_bayar'] ?? '') === 'TUNAI') {
                $customerCash += $row['total'];
            }
        }
        unset($row);

        $uangHarusDisetor = $pos['TUNAI'] + $kasMasuk - $kasKeluar - $supplierCash + $customerCash;

        return [
            'tanggal' => $tanggal,
            'printed_at' => date('Y-m-d H:i:s'),
            'stores' => $stores,
            'is_multi_store' => count($storeIds) > 1,
            'pos' => [
                'tunai' => $pos['TUNAI'],
                'transfer' => $pos['TRANSFER'],
                'qris' => $pos['QRIS'],
                'potongan_retur' => $pos['POTONGAN_RETUR'],
                'total' => $pos['TUNAI'] + $pos['TRANSFER'] + $pos['QRIS'],
                'total_transaksi' => (int) ($salesSummary['total_transaksi'] ?? 0),
                'total_netto' => (float) ($salesSummary['total_netto'] ?? 0),
                'total_gross' => (float) ($salesSummary['total_gross'] ?? 0),
            ],
            'discount' => [
                'item' => (float) ($salesSummary['total_diskon_item'] ?? 0),
                'nota' => (float) ($salesSummary['total_diskon_nota'] ?? 0),
                'redeem' => (float) ($salesSummary['total_redeem'] ?? 0),
            ],
            'kas' => [
                'rows' => $kasRows,
                'masuk' => $kasMasuk,
                'keluar' => $kasKeluar,
                'bersih' => $kasMasuk - $kasKeluar,
            ],
            'supplier' => [
                'rows' => $supplierRows,
                'total' => $supplierTotal,
                'tunai' => $supplierCash,
            ],
            'customer' => [
                'rows' => $customerRows,
                'total' => $customerTotal,
                'tunai' => $customerCash,
            ],
            'uang_harus_disetor' => $uangHarusDisetor,
        ];
    }

    private function normalizeDate($value): ?string
    {
        $value = trim((string) $value);
        if ($value === '') {
            return null;
        }
        $timestamp = strtotime($value);
        return $timestamp ? date('Y-m-d', $timestamp) : null;
    }

    private function resolveStoreIds($rawStoreIds, string $sessionTokoId, bool $allowMultiStore): array
    {
        if (!$allowMultiStore) {
            return [$sessionTokoId];
        }

        $storeIds = is_array($rawStoreIds) ? $rawStoreIds : [$rawStoreIds];
        $filtered = array_values(array_unique(array_filter(array_map(
            static fn($value): string => trim((string) $value),
            $storeIds
        ))));

        if (!empty($filtered)) {
            return $filtered;
        }

        $rows = $this->db->query("SELECT toko_id FROM toko ORDER BY toko_id")->getResultArray();
        $all = array_values(array_filter(array_map(static fn($row): string => (string) ($row['toko_id'] ?? ''), $rows)));
        return !empty($all) ? $all : [$sessionTokoId];
    }

    private function buildStoreFilter(string $column, array $storeIds): array
    {
        $placeholders = [];
        $binds = [];
        foreach ($storeIds as $idx => $storeId) {
            $placeholders[] = '?';
            $binds[] = $storeId;
        }

        return [$column . ' IN (' . implode(',', $placeholders) . ')', $binds];
    }
}
