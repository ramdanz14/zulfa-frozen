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
        $storeSummaries = $this->buildStoreSummaries($dateStart, $dateEnd, $tanggal, $storeSql, array_values($binds));
        $cashierGroups = $this->buildCashierGroups($dateStart, $dateEnd, $tanggal, $storeSql, array_values($binds));

        return [
            'tanggal' => $tanggal,
            'printed_at' => date('Y-m-d H:i:s'),
            'stores' => $stores,
            'is_multi_store' => count($storeIds) > 1,
            'store_summaries' => $storeSummaries,
            'cashier_groups' => $cashierGroups,
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

    private function buildStoreSummaries(string $dateStart, string $dateEnd, string $tanggal, string $storeSql, array $binds): array
    {
        $summaries = [];
        $this->mergeStoreTotals($summaries, $this->db->query(
            "SELECT j.toko_id, COALESCE(t.toko_nama, j.toko_id) AS toko_nama,
                    COALESCE(SUM(CASE WHEN pp.cara_bayar='TUNAI' THEN pp.nominal_bayar ELSE 0 END), 0) AS pos_tunai,
                    COALESCE(SUM(CASE WHEN pp.cara_bayar='TRANSFER' THEN pp.nominal_bayar ELSE 0 END), 0) AS pos_transfer,
                    COALESCE(SUM(CASE WHEN pp.cara_bayar='QRIS' THEN pp.nominal_bayar ELSE 0 END), 0) AS pos_qris
             FROM penjualan j
             INNER JOIN penjualan_pembayaran pp ON pp.toko_id=j.toko_id AND pp.jual_id=j.jual_id
             LEFT JOIN toko t ON t.toko_id=j.toko_id
             WHERE j.tgl BETWEEN ? AND ? AND j.$storeSql
             GROUP BY j.toko_id, COALESCE(t.toko_nama, j.toko_id)
             ORDER BY j.toko_id",
            array_merge([$dateStart, $dateEnd], $binds)
        )->getResultArray());

        $this->mergeStoreTotals($summaries, $this->db->query(
            "SELECT j.toko_id, COALESCE(t.toko_nama, j.toko_id) AS toko_nama,
                    COUNT(DISTINCT j.jual_id) AS total_transaksi,
                    COALESCE(SUM(j.total_diskon_item), 0) AS diskon_item,
                    COALESCE(SUM(j.diskon_nota), 0) AS diskon_nota,
                    COALESCE(SUM(j.redeem_nominal), 0) AS redeem
             FROM penjualan j
             LEFT JOIN toko t ON t.toko_id=j.toko_id
             WHERE j.tgl BETWEEN ? AND ? AND j.$storeSql
             GROUP BY j.toko_id, COALESCE(t.toko_nama, j.toko_id)
             ORDER BY j.toko_id",
            array_merge([$dateStart, $dateEnd], $binds)
        )->getResultArray());

        $this->mergeStoreTotals($summaries, $this->db->query(
            "SELECT km.toko_id, COALESCE(t.toko_nama, km.toko_id) AS toko_nama,
                    COALESCE(SUM(CASE WHEN ak.jenis_akun='MASUK' THEN km.nominal ELSE 0 END), 0) AS kas_masuk,
                    COALESCE(SUM(CASE WHEN ak.jenis_akun='KELUAR' THEN km.nominal ELSE 0 END), 0) AS kas_keluar
             FROM kas_mutasi km
             INNER JOIN akun_kas ak ON ak.nama_akun=km.nama_akun
             LEFT JOIN toko t ON t.toko_id=km.toko_id
             WHERE km.tanggal BETWEEN ? AND ? AND km.$storeSql
             GROUP BY km.toko_id, COALESCE(t.toko_nama, km.toko_id)
             ORDER BY km.toko_id",
            array_merge([$dateStart, $dateEnd], $binds)
        )->getResultArray());

        $this->mergeStoreTotals($summaries, $this->db->query(
            "SELECT pb.toko_id, COALESCE(t.toko_nama, pb.toko_id) AS toko_nama,
                    COALESCE(SUM(pb.jumlah_bayar), 0) AS supplier_total,
                    COALESCE(SUM(CASE WHEN pb.cara_bayar='TUNAI' THEN pb.jumlah_bayar ELSE 0 END), 0) AS supplier_tunai
             FROM pembelian_pembayaran pb
             LEFT JOIN toko t ON t.toko_id=pb.toko_id
             WHERE pb.tanggal_bayar BETWEEN ? AND ? AND pb.$storeSql
             GROUP BY pb.toko_id, COALESCE(t.toko_nama, pb.toko_id)
             ORDER BY pb.toko_id",
            array_merge([$dateStart, $dateEnd], $binds)
        )->getResultArray());

        $this->mergeStoreTotals($summaries, $this->db->query(
            "SELECT pp.toko_id, COALESCE(t.toko_nama, pp.toko_id) AS toko_nama,
                    COALESCE(SUM(pp.nominal_bayar), 0) AS customer_total,
                    COALESCE(SUM(CASE WHEN pp.cara_bayar='TUNAI' THEN pp.nominal_bayar ELSE 0 END), 0) AS customer_tunai
             FROM penjualan_pembayaran pp
             INNER JOIN penjualan j ON j.toko_id=pp.toko_id AND j.jual_id=pp.jual_id
             LEFT JOIN toko t ON t.toko_id=pp.toko_id
             WHERE pp.tgl_bayar BETWEEN ? AND ?
                AND DATE(j.tgl) <> ?
                AND j.is_kredit='1'
                AND pp.$storeSql
             GROUP BY pp.toko_id, COALESCE(t.toko_nama, pp.toko_id)
             ORDER BY pp.toko_id",
            array_merge([$dateStart, $dateEnd, $tanggal], $binds)
        )->getResultArray());

        return array_values(array_map([$this, 'finalizeAccountabilitySummary'], $summaries));
    }

    private function buildCashierGroups(string $dateStart, string $dateEnd, string $tanggal, string $storeSql, array $binds): array
    {
        $groups = [];
        $this->mergeCashierTotals($groups, $this->db->query(
            "SELECT j.toko_id, COALESCE(t.toko_nama, j.toko_id) AS toko_nama,
                    COALESCE(NULLIF(j.updid, ''), '-') AS kasir,
                    COALESCE(u.fullname, NULLIF(j.updid, ''), '-') AS nama_kasir,
                    COALESCE(SUM(CASE WHEN pp.cara_bayar='TUNAI' THEN pp.nominal_bayar ELSE 0 END), 0) AS pos_tunai,
                    COALESCE(SUM(CASE WHEN pp.cara_bayar='TRANSFER' THEN pp.nominal_bayar ELSE 0 END), 0) AS pos_transfer,
                    COALESCE(SUM(CASE WHEN pp.cara_bayar='QRIS' THEN pp.nominal_bayar ELSE 0 END), 0) AS pos_qris
             FROM penjualan j
             INNER JOIN penjualan_pembayaran pp ON pp.toko_id=j.toko_id AND pp.jual_id=j.jual_id
             LEFT JOIN toko t ON t.toko_id=j.toko_id
             LEFT JOIN tb_user u ON u.username=j.updid
             WHERE j.tgl BETWEEN ? AND ? AND j.$storeSql
             GROUP BY j.toko_id, COALESCE(t.toko_nama, j.toko_id), COALESCE(NULLIF(j.updid, ''), '-'), COALESCE(u.fullname, NULLIF(j.updid, ''), '-')
             ORDER BY j.toko_id, kasir",
            array_merge([$dateStart, $dateEnd], $binds)
        )->getResultArray());

        $this->mergeCashierTotals($groups, $this->db->query(
            "SELECT j.toko_id, COALESCE(t.toko_nama, j.toko_id) AS toko_nama,
                    COALESCE(NULLIF(j.updid, ''), '-') AS kasir,
                    COALESCE(u.fullname, NULLIF(j.updid, ''), '-') AS nama_kasir,
                    COUNT(DISTINCT j.jual_id) AS total_transaksi,
                    COALESCE(SUM(j.total_diskon_item), 0) AS diskon_item,
                    COALESCE(SUM(j.diskon_nota), 0) AS diskon_nota,
                    COALESCE(SUM(j.redeem_nominal), 0) AS redeem
             FROM penjualan j
             LEFT JOIN toko t ON t.toko_id=j.toko_id
             LEFT JOIN tb_user u ON u.username=j.updid
             WHERE j.tgl BETWEEN ? AND ? AND j.$storeSql
             GROUP BY j.toko_id, COALESCE(t.toko_nama, j.toko_id), COALESCE(NULLIF(j.updid, ''), '-'), COALESCE(u.fullname, NULLIF(j.updid, ''), '-')
             ORDER BY j.toko_id, kasir",
            array_merge([$dateStart, $dateEnd], $binds)
        )->getResultArray());

        $this->mergeCashierTotals($groups, $this->db->query(
            "SELECT km.toko_id, COALESCE(t.toko_nama, km.toko_id) AS toko_nama,
                    COALESCE(NULLIF(km.updid, ''), '-') AS kasir,
                    COALESCE(u.fullname, NULLIF(km.updid, ''), '-') AS nama_kasir,
                    COALESCE(SUM(CASE WHEN ak.jenis_akun='MASUK' THEN km.nominal ELSE 0 END), 0) AS kas_masuk,
                    COALESCE(SUM(CASE WHEN ak.jenis_akun='KELUAR' THEN km.nominal ELSE 0 END), 0) AS kas_keluar
             FROM kas_mutasi km
             INNER JOIN akun_kas ak ON ak.nama_akun=km.nama_akun
             LEFT JOIN toko t ON t.toko_id=km.toko_id
             LEFT JOIN tb_user u ON u.username=km.updid
             WHERE km.tanggal BETWEEN ? AND ? AND km.$storeSql
             GROUP BY km.toko_id, COALESCE(t.toko_nama, km.toko_id), COALESCE(NULLIF(km.updid, ''), '-'), COALESCE(u.fullname, NULLIF(km.updid, ''), '-')
             ORDER BY km.toko_id, kasir",
            array_merge([$dateStart, $dateEnd], $binds)
        )->getResultArray());

        $this->mergeCashierTotals($groups, $this->db->query(
            "SELECT pb.toko_id, COALESCE(t.toko_nama, pb.toko_id) AS toko_nama,
                    COALESCE(NULLIF(pb.username, ''), '-') AS kasir,
                    COALESCE(u.fullname, NULLIF(pb.username, ''), '-') AS nama_kasir,
                    COALESCE(SUM(pb.jumlah_bayar), 0) AS supplier_total,
                    COALESCE(SUM(CASE WHEN pb.cara_bayar='TUNAI' THEN pb.jumlah_bayar ELSE 0 END), 0) AS supplier_tunai
             FROM pembelian_pembayaran pb
             LEFT JOIN toko t ON t.toko_id=pb.toko_id
             LEFT JOIN tb_user u ON u.username=pb.username
             WHERE pb.tanggal_bayar BETWEEN ? AND ? AND pb.$storeSql
             GROUP BY pb.toko_id, COALESCE(t.toko_nama, pb.toko_id), COALESCE(NULLIF(pb.username, ''), '-'), COALESCE(u.fullname, NULLIF(pb.username, ''), '-')
             ORDER BY pb.toko_id, kasir",
            array_merge([$dateStart, $dateEnd], $binds)
        )->getResultArray());

        $this->mergeCashierTotals($groups, $this->db->query(
            "SELECT pp.toko_id, COALESCE(t.toko_nama, pp.toko_id) AS toko_nama,
                    COALESCE(NULLIF(pp.updid, ''), '-') AS kasir,
                    COALESCE(u.fullname, NULLIF(pp.updid, ''), '-') AS nama_kasir,
                    COALESCE(SUM(pp.nominal_bayar), 0) AS customer_total,
                    COALESCE(SUM(CASE WHEN pp.cara_bayar='TUNAI' THEN pp.nominal_bayar ELSE 0 END), 0) AS customer_tunai
             FROM penjualan_pembayaran pp
             INNER JOIN penjualan j ON j.toko_id=pp.toko_id AND j.jual_id=pp.jual_id
             LEFT JOIN toko t ON t.toko_id=pp.toko_id
             LEFT JOIN tb_user u ON u.username=pp.updid
             WHERE pp.tgl_bayar BETWEEN ? AND ?
                AND DATE(j.tgl) <> ?
                AND j.is_kredit='1'
                AND pp.$storeSql
             GROUP BY pp.toko_id, COALESCE(t.toko_nama, pp.toko_id), COALESCE(NULLIF(pp.updid, ''), '-'), COALESCE(u.fullname, NULLIF(pp.updid, ''), '-')
             ORDER BY pp.toko_id, kasir",
            array_merge([$dateStart, $dateEnd, $tanggal], $binds)
        )->getResultArray());

        return array_values(array_map([$this, 'finalizeAccountabilitySummary'], $groups));
    }

    private function mergeStoreTotals(array &$target, array $rows): void
    {
        foreach ($rows as $row) {
            $key = (string) ($row['toko_id'] ?? '-');
            if (!isset($target[$key])) {
                $target[$key] = $this->emptyAccountabilitySummary($row);
            }
            $this->addAccountabilityValues($target[$key], $row);
        }
    }

    private function mergeCashierTotals(array &$target, array $rows): void
    {
        foreach ($rows as $row) {
            $key = (string) ($row['toko_id'] ?? '-') . '|' . (string) ($row['kasir'] ?? '-');
            if (!isset($target[$key])) {
                $target[$key] = $this->emptyAccountabilitySummary($row);
            }
            $target[$key]['kasir'] = (string) ($row['kasir'] ?? '-');
            $target[$key]['nama_kasir'] = (string) ($row['nama_kasir'] ?? $row['kasir'] ?? '-');
            $this->addAccountabilityValues($target[$key], $row);
        }
    }

    private function emptyAccountabilitySummary(array $row): array
    {
        return [
            'toko_id' => (string) ($row['toko_id'] ?? '-'),
            'toko_nama' => (string) ($row['toko_nama'] ?? $row['toko_id'] ?? '-'),
            'kasir' => (string) ($row['kasir'] ?? ''),
            'nama_kasir' => (string) ($row['nama_kasir'] ?? $row['kasir'] ?? ''),
            'total_transaksi' => 0,
            'pos_tunai' => 0.0,
            'pos_transfer' => 0.0,
            'pos_qris' => 0.0,
            'pos_total' => 0.0,
            'diskon_item' => 0.0,
            'diskon_nota' => 0.0,
            'redeem' => 0.0,
            'kas_masuk' => 0.0,
            'kas_keluar' => 0.0,
            'kas_bersih' => 0.0,
            'supplier_total' => 0.0,
            'supplier_tunai' => 0.0,
            'customer_total' => 0.0,
            'customer_tunai' => 0.0,
            'uang_harus_disetor' => 0.0,
        ];
    }

    private function addAccountabilityValues(array &$target, array $row): void
    {
        foreach (['pos_tunai', 'pos_transfer', 'pos_qris', 'diskon_item', 'diskon_nota', 'redeem', 'kas_masuk', 'kas_keluar', 'supplier_total', 'supplier_tunai', 'customer_total', 'customer_tunai'] as $field) {
            $target[$field] += (float) ($row[$field] ?? 0);
        }
        $target['total_transaksi'] += (int) ($row['total_transaksi'] ?? 0);
    }

    private function finalizeAccountabilitySummary(array $row): array
    {
        $row['pos_total'] = (float) $row['pos_tunai'] + (float) $row['pos_transfer'] + (float) $row['pos_qris'];
        $row['kas_bersih'] = (float) $row['kas_masuk'] - (float) $row['kas_keluar'];
        $row['uang_harus_disetor'] = (float) $row['pos_tunai'] + (float) $row['kas_masuk'] - (float) $row['kas_keluar'] - (float) $row['supplier_tunai'] + (float) $row['customer_tunai'];
        return $row;
    }
}
