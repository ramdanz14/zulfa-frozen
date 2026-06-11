<?php

namespace App\Models;

use CodeIgniter\Model;

class SaldousahaModel extends Model
{
    protected $returnType = 'array';
    protected $protectFields = false;

    public function getReport(array $params, string $sessionTokoId, bool $allowMultiStore): array
    {
        $startDate = $this->normalizeDate($params['date_start'] ?? '') ?: date('Y-m-01');
        $endDate = $this->normalizeDate($params['date_end'] ?? '') ?: date('Y-m-d');
        if ($startDate > $endDate) {
            [$startDate, $endDate] = [$endDate, $startDate];
        }

        $storeIds = $this->resolveStoreIds($params['toko_ids'] ?? [], $sessionTokoId, $allowMultiStore);
        $start = $startDate . ' 00:00:00';
        $end = $endDate . ' 23:59:59';

        $sales = $this->getSalesSummary($storeIds, $start, $end);
        $returPenjualan = $this->getReturPenjualan($storeIds, $start, $end);
        $beban = $this->getBebanSummary($storeIds, $start, $end);
        $balanceAsOfDate = date('Y-m-d');
        $kas = $this->getCashPosition($storeIds, $balanceAsOfDate);
        $hutang = $this->getTotalHutang($storeIds);
        $piutang = $this->getTotalPiutang($storeIds);
        $stok = $this->getTotalStokRupiah($storeIds);

        $labaBersih = $sales['laba_kotor'] - $returPenjualan - $beban['total_beban'];
        $saldoKas = $kas['saldo_tunai'] + $kas['saldo_nontunai'];
        $saldoAkhir = $saldoKas - $hutang + $piutang - $stok;
        $cashRatio = $hutang > 0 ? $saldoKas / $hutang : null;
        $currentRatio = $hutang > 0 ? ($saldoKas + $stok + $piutang) / $hutang : null;

        return [
            'period' => [
                'date_start' => $startDate,
                'date_end' => $endDate,
                'balance_as_of' => $balanceAsOfDate,
                'balance_period' => date('Y-m-01', strtotime($balanceAsOfDate)),
            ],
            'summary' => [
                'total_penjualan' => $sales['total_penjualan'],
                'total_diskon' => $sales['total_diskon'],
                'total_sales_net' => $sales['total_sales_net'],
                'penjualan_hpp' => $sales['penjualan_hpp'],
                'laba_kotor' => $sales['laba_kotor'],
                'retur_penjualan' => $returPenjualan,
                'beban_tunai' => $beban['beban_tunai'],
                'beban_nontunai' => $beban['beban_nontunai'],
                'total_beban' => $beban['total_beban'],
                'laba_bersih' => $labaBersih,
                'saldo_kas_tunai' => $kas['saldo_tunai'],
                'saldo_kas_nontunai' => $kas['saldo_nontunai'],
                'saldo_kas_total' => $saldoKas,
                'total_hutang' => $hutang,
                'total_piutang' => $piutang,
                'total_stok_rupiah' => $stok,
                'saldo_akhir' => $saldoAkhir,
                'cash_ratio' => $cashRatio,
                'current_ratio' => $currentRatio,
            ],
            'profit_rows' => [
                ['label' => 'Total Penjualan', 'amount' => $sales['total_penjualan'], 'type' => 'in'],
                ['label' => 'Total Diskon', 'amount' => $sales['total_diskon'], 'type' => 'out'],
                ['label' => 'Total Sales Net', 'amount' => $sales['total_sales_net'], 'type' => 'in'],
                ['label' => 'Penjualan HPP', 'amount' => $sales['penjualan_hpp'], 'type' => 'out'],
                ['label' => 'Laba Kotor', 'amount' => $sales['laba_kotor'], 'type' => 'total'],
                ['label' => 'Retur Penjualan', 'amount' => $returPenjualan, 'type' => 'out'],
                ['label' => 'Kas Pengeluaran Beban Tunai', 'amount' => $beban['beban_tunai'], 'type' => 'out'],
                ['label' => 'Kas Pengeluaran Beban Non Tunai', 'amount' => $beban['beban_nontunai'], 'type' => 'out'],
                ['label' => 'Laba Bersih', 'amount' => $labaBersih, 'type' => 'total'],
            ],
            'balance_rows' => [
                ['label' => 'Saldo Kas Tunai', 'amount' => $kas['saldo_tunai'], 'type' => 'asset'],
                ['label' => 'Saldo Kas Non Tunai', 'amount' => $kas['saldo_nontunai'], 'type' => 'asset'],
                ['label' => 'Total Hutang', 'amount' => $hutang, 'type' => 'liability'],
                ['label' => 'Total Piutang', 'amount' => $piutang, 'type' => 'asset'],
                ['label' => 'Total Stok Rupiah', 'amount' => $stok, 'type' => 'asset'],
                ['label' => 'Saldo Akhir', 'amount' => $saldoAkhir, 'type' => 'total'],
            ],
        ];
    }

    private function getSalesSummary(array $storeIds, string $start, string $end): array
    {
        [$storeWhere, $binds] = $this->buildStoreWhere('j.toko_id', $storeIds);
        $row = $this->db->query(
            "SELECT
                COALESCE(SUM(j.gross),0) AS total_penjualan,
                COALESCE(SUM(j.diskon_nota + j.total_diskon_item),0) AS total_diskon,
                COALESCE(SUM(j.netto),0) AS total_sales_net,
                COALESCE(SUM(j.total_hpp),0) AS penjualan_hpp,
                COALESCE(SUM(j.margin_bruto),0) AS laba_kotor
             FROM penjualan j
             WHERE j.tgl BETWEEN ? AND ? AND {$storeWhere}",
            array_merge([$start, $end], $binds)
        )->getRowArray() ?: [];

        return [
            'total_penjualan' => (float) ($row['total_penjualan'] ?? 0),
            'total_diskon' => (float) ($row['total_diskon'] ?? 0),
            'total_sales_net' => (float) ($row['total_sales_net'] ?? 0),
            'penjualan_hpp' => (float) ($row['penjualan_hpp'] ?? 0),
            'laba_kotor' => (float) ($row['laba_kotor'] ?? 0),
        ];
    }

    private function getReturPenjualan(array $storeIds, string $start, string $end): float
    {
        [$storeWhere, $binds] = $this->buildStoreWhere('km.toko_id', $storeIds);
        $row = $this->db->query(
            "SELECT COALESCE(SUM(km.nominal),0) AS total
             FROM kas_mutasi km
             WHERE km.tanggal BETWEEN ? AND ?
               AND km.nama_akun='RETUR PENJUALAN'
               AND COALESCE(km.tipe_mutasi, 'OPERASIONAL')='OPERASIONAL'
               AND {$storeWhere}",
            array_merge([$start, $end], $binds)
        )->getRowArray() ?: [];

        return (float) ($row['total'] ?? 0);
    }

    private function getBebanSummary(array $storeIds, string $start, string $end): array
    {
        [$storeWhere, $binds] = $this->buildStoreWhere('km.toko_id', $storeIds);
        $row = $this->db->query(
            "SELECT
                COALESCE(SUM(CASE WHEN COALESCE(km.saldo_channel, 'CASH')='CASH' THEN km.nominal ELSE 0 END),0) AS beban_tunai,
                COALESCE(SUM(CASE WHEN COALESCE(km.saldo_channel, 'CASH')='NONCASH' THEN km.nominal ELSE 0 END),0) AS beban_nontunai
             FROM kas_mutasi km
             INNER JOIN akun_kas ak ON ak.nama_akun=km.nama_akun
             WHERE km.tanggal BETWEEN ? AND ?
               AND ak.jenis_akun='KELUAR'
               AND ak.flag_beban='Y'
               AND COALESCE(km.tipe_mutasi, 'OPERASIONAL')='OPERASIONAL'
               AND {$storeWhere}",
            array_merge([$start, $end], $binds)
        )->getRowArray() ?: [];

        $cash = (float) ($row['beban_tunai'] ?? 0);
        $noncash = (float) ($row['beban_nontunai'] ?? 0);

        return [
            'beban_tunai' => $cash,
            'beban_nontunai' => $noncash,
            'total_beban' => $cash + $noncash,
        ];
    }

    private function getCashPosition(array $storeIds, string $asOfDate): array
    {
        $period = date('Y-m-01', strtotime($asOfDate));
        $start = $period . ' 00:00:00';
        $end = $asOfDate . ' 23:59:59';
        $opening = $this->getOpeningBalance($storeIds, $period);
        $payments = $this->getPaymentMovements($storeIds, $start, $end);
        $kas = $this->getKasMovements($storeIds, $start, $end);

        return [
            'saldo_tunai' => $opening['cash'] + $payments['cash_in'] + $kas['cash_in'] - $payments['cash_out'] - $kas['cash_out'],
            'saldo_nontunai' => $opening['noncash'] + $payments['noncash_in'] + $kas['noncash_in'] - $payments['noncash_out'] - $kas['noncash_out'],
        ];
    }

    private function getOpeningBalance(array $storeIds, string $period): array
    {
        [$storeWhere, $binds] = $this->buildStoreWhere('toko_id', $storeIds);
        $previous = date('Y-m-01', strtotime($period . ' -1 month'));
        $row = $this->db->query(
            "SELECT COALESCE(SUM(saldo_tunai),0) AS cash,
                    COALESCE(SUM(saldo_transfer + saldo_qris),0) AS noncash
             FROM saldo_cash
             WHERE tahun=? AND bulan=? AND {$storeWhere}",
            array_merge([(int) date('Y', strtotime($previous)), (int) date('m', strtotime($previous))], $binds)
        )->getRowArray() ?: [];

        return [
            'cash' => (float) ($row['cash'] ?? 0),
            'noncash' => (float) ($row['noncash'] ?? 0),
        ];
    }

    private function getPaymentMovements(array $storeIds, string $start, string $end): array
    {
        $map = ['cash_in' => 0.0, 'cash_out' => 0.0, 'noncash_in' => 0.0, 'noncash_out' => 0.0];
        $periodStart = substr($start, 0, 10);

        [$saleWhere, $saleBinds] = $this->buildStoreWhere('pp.toko_id', $storeIds);
        $saleRows = $this->db->query(
            "SELECT pp.cara_bayar, COALESCE(SUM(pp.nominal_bayar),0) AS total
             FROM penjualan_pembayaran pp
             INNER JOIN penjualan j ON j.toko_id=pp.toko_id AND j.jual_id=pp.jual_id
             WHERE pp.tgl_bayar BETWEEN ? AND ?
               AND DATE(j.tgl) >= ?
               AND pp.cara_bayar IN ('TUNAI','TRANSFER','QRIS')
               AND {$saleWhere}
             GROUP BY pp.cara_bayar",
            array_merge([$start, $end, $periodStart], $saleBinds)
        )->getResultArray();
        $this->mergePaymentRows($map, $saleRows, 'in');

        $piutangRows = $this->db->query(
            "SELECT pp.cara_bayar, COALESCE(SUM(pp.nominal_bayar),0) AS total
             FROM penjualan_pembayaran pp
             INNER JOIN penjualan j ON j.toko_id=pp.toko_id AND j.jual_id=pp.jual_id
             WHERE pp.tgl_bayar BETWEEN ? AND ?
               AND DATE(j.tgl) < ?
               AND j.is_kredit='1'
               AND pp.cara_bayar IN ('TUNAI','TRANSFER','QRIS')
               AND {$saleWhere}
             GROUP BY pp.cara_bayar",
            array_merge([$start, $end, $periodStart], $saleBinds)
        )->getResultArray();
        $this->mergePaymentRows($map, $piutangRows, 'in');

        [$supplierWhere, $supplierBinds] = $this->buildStoreWhere('toko_id', $storeIds);
        $supplierRows = $this->db->query(
            "SELECT cara_bayar, COALESCE(SUM(jumlah_bayar),0) AS total
             FROM pembelian_pembayaran
             WHERE tanggal_bayar BETWEEN ? AND ?
               AND cara_bayar IN ('TUNAI','TRANSFER')
               AND {$supplierWhere}
             GROUP BY cara_bayar",
            array_merge([$start, $end], $supplierBinds)
        )->getResultArray();
        $this->mergePaymentRows($map, $supplierRows, 'out');

        return $map;
    }

    private function getKasMovements(array $storeIds, string $start, string $end): array
    {
        [$storeWhere, $binds] = $this->buildStoreWhere('km.toko_id', $storeIds);
        $rows = $this->db->query(
            "SELECT channel, direction, COALESCE(SUM(total),0) AS total
             FROM (
                SELECT CASE WHEN COALESCE(km.saldo_channel, 'CASH')='NONCASH' THEN 'noncash' ELSE 'cash' END AS channel,
                        CASE WHEN ak.jenis_akun='MASUK' THEN 'in' ELSE 'out' END AS direction,
                        COALESCE(km.nominal,0) AS total
                 FROM kas_mutasi km
                 INNER JOIN akun_kas ak ON ak.nama_akun=km.nama_akun
                 WHERE km.tanggal BETWEEN ? AND ?
                   AND COALESCE(km.tipe_mutasi, 'OPERASIONAL')='OPERASIONAL'
                   AND {$storeWhere}
                UNION ALL
                SELECT CASE WHEN km.saldo_asal='NONCASH' THEN 'noncash' ELSE 'cash' END AS channel,
                        'out' AS direction,
                        COALESCE(km.nominal,0) AS total
                 FROM kas_mutasi km
                 WHERE km.tanggal BETWEEN ? AND ?
                   AND km.tipe_mutasi='PINDAH_SALDO'
                   AND {$storeWhere}
                UNION ALL
                SELECT CASE WHEN km.saldo_tujuan='NONCASH' THEN 'noncash' ELSE 'cash' END AS channel,
                        'in' AS direction,
                        COALESCE(km.nominal,0) AS total
                 FROM kas_mutasi km
                 WHERE km.tanggal BETWEEN ? AND ?
                   AND km.tipe_mutasi='PINDAH_SALDO'
                   AND {$storeWhere}
             ) x
             GROUP BY channel, direction",
            array_merge([$start, $end], $binds, [$start, $end], $binds, [$start, $end], $binds)
        )->getResultArray();

        $map = ['cash_in' => 0.0, 'cash_out' => 0.0, 'noncash_in' => 0.0, 'noncash_out' => 0.0];
        foreach ($rows as $row) {
            $key = strtolower((string) ($row['channel'] ?? 'cash')) . '_' . strtolower((string) ($row['direction'] ?? 'in'));
            if (isset($map[$key])) {
                $map[$key] += (float) ($row['total'] ?? 0);
            }
        }

        return $map;
    }

    private function getTotalHutang(array $storeIds): float
    {
        [$storeWhere, $binds] = $this->buildStoreWhere('toko_id', $storeIds);
        $row = $this->db->query(
            "SELECT COALESCE(SUM(sisa_bayar),0) AS total
             FROM pembelian
             WHERE is_kredit=1
               AND status_bayar IN ('BELUM','CICIL')
               AND {$storeWhere}",
            $binds
        )->getRowArray() ?: [];

        return (float) ($row['total'] ?? 0);
    }

    private function getTotalPiutang(array $storeIds): float
    {
        [$storeWhere, $binds] = $this->buildStoreWhere('toko_id', $storeIds);
        $row = $this->db->query(
            "SELECT COALESCE(SUM(sisa_piutang),0) AS total
             FROM penjualan
             WHERE is_kredit='1'
               AND status_bayar IN ('BELUM','CICIL')
               AND {$storeWhere}",
            $binds
        )->getRowArray() ?: [];

        return (float) ($row['total'] ?? 0);
    }

    private function getTotalStokRupiah(array $storeIds): float
    {
        [$storeWhere, $binds] = $this->buildStoreWhere('toko_id', $storeIds);
        $row = $this->db->query(
            "SELECT COALESCE(SUM(RP_SALDO_AKH),0) AS total
             FROM stmast
             WHERE {$storeWhere}",
            $binds
        )->getRowArray() ?: [];

        return (float) ($row['total'] ?? 0);
    }

    private function mergePaymentRows(array &$map, array $rows, string $direction): void
    {
        foreach ($rows as $row) {
            $method = strtoupper((string) ($row['cara_bayar'] ?? ''));
            $channel = $method === 'TUNAI' ? 'cash' : 'noncash';
            $key = $channel . '_' . $direction;
            if (isset($map[$key])) {
                $map[$key] += (float) ($row['total'] ?? 0);
            }
        }
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
        return array_values(array_filter(array_map(static fn(array $row): string => (string) ($row['toko_id'] ?? ''), $rows)));
    }

    private function buildStoreWhere(string $column, array $storeIds): array
    {
        $placeholders = [];
        $binds = [];
        foreach ($storeIds as $storeId) {
            $placeholders[] = '?';
            $binds[] = $storeId;
        }

        if (empty($placeholders)) {
            $placeholders[] = '?';
            $binds[] = (string) session('toko_id');
        }

        return [$column . ' IN (' . implode(',', $placeholders) . ')', $binds];
    }

    private function normalizeDate($value): string
    {
        $timestamp = strtotime((string) $value);
        return $timestamp ? date('Y-m-d', $timestamp) : '';
    }
}
