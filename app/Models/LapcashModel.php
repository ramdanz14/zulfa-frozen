<?php

namespace App\Models;

use CodeIgniter\Model;

class LapcashModel extends Model
{
    protected $returnType = 'array';
    protected $protectFields = false;

    public function getReport(array $params, string $sessionTokoId, bool $allowMultiStore): array
    {
        $period = $this->monthStart((string) ($params['periode'] ?? date('Y-m-01')));
        $storeIds = $this->resolveStoreIds($params['toko_ids'] ?? [], $sessionTokoId, $allowMultiStore);
        $stores = $this->getStores($storeIds);
        $start = $period . ' 00:00:00';
        $end = date('Y-m-t 23:59:59', strtotime($period));

        $opening = $this->getOpeningBalance($storeIds, $period);
        $dailyMovements = $this->getDailyMovements($storeIds, $start, $end);
        $dailyRows = $this->buildDailyRows($period, $opening, $dailyMovements);
        $summary = $this->buildSummary($opening, $dailyMovements, $dailyRows);

        return [
            'periode' => $period,
            'period_label' => $this->periodLabel($period),
            'stores' => $stores,
            'summary' => $summary,
            'rows' => $dailyRows,
        ];
    }

    private function getDailyMovements(array $storeIds, string $start, string $end): array
    {
        $movements = [];
        $this->mergeMovementRows($movements, $this->queryPosPayments($storeIds, $start, $end));
        $this->mergeMovementRows($movements, $this->queryPiutangPayments($storeIds, $start, $end));
        $this->mergeMovementRows($movements, $this->querySupplierPayments($storeIds, $start, $end));
        $this->mergeMovementRows($movements, $this->queryKasMutasi($storeIds, $start, $end));

        ksort($movements);
        return $movements;
    }

    private function queryPosPayments(array $storeIds, string $start, string $end): array
    {
        [$storeWhere, $binds] = $this->buildStoreWhere('pp.toko_id', $storeIds);
        return $this->db->query(
            "SELECT DATE(pp.tgl_bayar) AS tanggal,
                    CASE WHEN pp.cara_bayar='TUNAI' THEN 'cash' ELSE 'noncash' END AS channel,
                    'in' AS direction,
                    CASE
                        WHEN pp.cara_bayar='TUNAI' THEN 'Total Penjualan Cash'
                        WHEN pp.cara_bayar='QRIS' THEN 'Total Penjualan QRIS'
                        ELSE 'Total Penjualan Transfer'
                    END AS label,
                    COALESCE(SUM(pp.nominal_bayar),0) AS total
             FROM penjualan_pembayaran pp
             INNER JOIN penjualan j ON j.toko_id=pp.toko_id AND j.jual_id=pp.jual_id
             WHERE pp.tgl_bayar BETWEEN ? AND ?
               AND DATE(j.tgl) >= ?
               AND pp.cara_bayar IN ('TUNAI','TRANSFER','QRIS')
               AND {$storeWhere}
             GROUP BY DATE(pp.tgl_bayar), pp.cara_bayar",
            array_merge([$start, $end, substr($start, 0, 10)], $binds)
        )->getResultArray();
    }

    private function queryPiutangPayments(array $storeIds, string $start, string $end): array
    {
        [$storeWhere, $binds] = $this->buildStoreWhere('pp.toko_id', $storeIds);
        return $this->db->query(
            "SELECT DATE(pp.tgl_bayar) AS tanggal,
                    CASE WHEN pp.cara_bayar='TUNAI' THEN 'cash' ELSE 'noncash' END AS channel,
                    'in' AS direction,
                    CASE
                        WHEN pp.cara_bayar='TUNAI' THEN 'Total Bayar Piutang Cash'
                        WHEN pp.cara_bayar='QRIS' THEN 'Total Bayar Piutang QRIS'
                        ELSE 'Total Bayar Piutang Transfer'
                    END AS label,
                    COALESCE(SUM(pp.nominal_bayar),0) AS total
             FROM penjualan_pembayaran pp
             INNER JOIN penjualan j ON j.toko_id=pp.toko_id AND j.jual_id=pp.jual_id
             WHERE pp.tgl_bayar BETWEEN ? AND ?
               AND DATE(j.tgl) < ?
               AND j.is_kredit='1'
               AND pp.cara_bayar IN ('TUNAI','TRANSFER','QRIS')
               AND {$storeWhere}
             GROUP BY DATE(pp.tgl_bayar), pp.cara_bayar",
            array_merge([$start, $end, substr($start, 0, 10)], $binds)
        )->getResultArray();
    }

    private function querySupplierPayments(array $storeIds, string $start, string $end): array
    {
        [$storeWhere, $binds] = $this->buildStoreWhere('toko_id', $storeIds);
        return $this->db->query(
            "SELECT DATE(tanggal_bayar) AS tanggal,
                    CASE WHEN cara_bayar='TUNAI' THEN 'cash' ELSE 'noncash' END AS channel,
                    'out' AS direction,
                    CASE WHEN cara_bayar='TUNAI' THEN 'Total Pembayaran Supplier Tunai' ELSE 'Total Pembayaran Supplier Transfer' END AS label,
                    COALESCE(SUM(jumlah_bayar),0) AS total
             FROM pembelian_pembayaran
             WHERE tanggal_bayar BETWEEN ? AND ?
               AND cara_bayar IN ('TUNAI','TRANSFER')
               AND {$storeWhere}
             GROUP BY DATE(tanggal_bayar), cara_bayar",
            array_merge([$start, $end], $binds)
        )->getResultArray();
    }

    private function queryKasMutasi(array $storeIds, string $start, string $end): array
    {
        [$storeWhere, $binds] = $this->buildStoreWhere('km.toko_id', $storeIds);
        return $this->db->query(
            "SELECT tanggal, channel, direction, label, SUM(total) AS total
             FROM (
                SELECT DATE(km.tanggal) AS tanggal,
                        CASE WHEN COALESCE(km.saldo_channel, 'CASH')='NONCASH' THEN 'noncash' ELSE 'cash' END AS channel,
                        CASE WHEN ak.jenis_akun='MASUK' THEN 'in' ELSE 'out' END AS direction,
                        CASE
                            WHEN ak.jenis_akun='MASUK' AND COALESCE(km.saldo_channel, 'CASH')='NONCASH' THEN 'Total Pemasukan Non Tunai'
                            WHEN ak.jenis_akun='MASUK' THEN 'Total Pemasukan Kas'
                            WHEN COALESCE(km.saldo_channel, 'CASH')='NONCASH' AND ak.flag_beban='Y' THEN 'Total Pengeluaran Non Tunai Beban'
                            WHEN COALESCE(km.saldo_channel, 'CASH')='NONCASH' THEN 'Total Pengeluaran Non Tunai Non Beban'
                            WHEN ak.flag_beban='Y' THEN 'Total Pengeluaran Kas Beban'
                            ELSE 'Total Pengeluaran Kas Non Beban'
                        END AS label,
                        COALESCE(km.nominal,0) AS total
                 FROM kas_mutasi km
                 INNER JOIN akun_kas ak ON ak.nama_akun=km.nama_akun
                 WHERE km.tanggal BETWEEN ? AND ?
                   AND COALESCE(km.tipe_mutasi, 'OPERASIONAL')='OPERASIONAL'
                   AND {$storeWhere}
                UNION ALL
                SELECT DATE(km.tanggal) AS tanggal,
                        CASE WHEN km.saldo_asal='NONCASH' THEN 'noncash' ELSE 'cash' END AS channel,
                        'out' AS direction,
                        'Mutasi Saldo Keluar' AS label,
                        COALESCE(km.nominal,0) AS total
                 FROM kas_mutasi km
                 WHERE km.tanggal BETWEEN ? AND ?
                   AND km.tipe_mutasi='PINDAH_SALDO'
                   AND {$storeWhere}
                UNION ALL
                SELECT DATE(km.tanggal) AS tanggal,
                        CASE WHEN km.saldo_tujuan='NONCASH' THEN 'noncash' ELSE 'cash' END AS channel,
                        'in' AS direction,
                        'Mutasi Saldo Masuk' AS label,
                        COALESCE(km.nominal,0) AS total
                 FROM kas_mutasi km
                 WHERE km.tanggal BETWEEN ? AND ?
                   AND km.tipe_mutasi='PINDAH_SALDO'
                   AND {$storeWhere}
             ) x
             GROUP BY tanggal, channel, direction, label",
            array_merge([$start, $end], $binds, [$start, $end], $binds, [$start, $end], $binds)
        )->getResultArray();
    }

    private function mergeMovementRows(array &$movements, array $rows): void
    {
        foreach ($rows as $row) {
            $tanggal = (string) ($row['tanggal'] ?? '');
            $channel = (string) ($row['channel'] ?? '');
            $direction = (string) ($row['direction'] ?? '');
            $label = (string) ($row['label'] ?? '');
            $total = (float) ($row['total'] ?? 0);
            if ($tanggal === '' || $channel === '' || $direction === '' || $total == 0.0) {
                continue;
            }

            $movements[$tanggal][$channel][$direction] = ($movements[$tanggal][$channel][$direction] ?? 0) + $total;
            $movements[$tanggal]['labels'][$label] = ($movements[$tanggal]['labels'][$label] ?? 0) + $total;
        }
    }

    private function buildDailyRows(string $period, array $opening, array $movements): array
    {
        $rows = [];
        $cashBalance = (float) ($opening['cash'] ?? 0);
        $noncashBalance = (float) ($opening['noncash'] ?? 0);
        $openingDate = date('Y-m-d', strtotime($period . ' -1 day'));
        $rows[] = [
            'tanggal' => $openingDate,
            'in_cash' => $cashBalance,
            'out_cash' => 0.0,
            'saldo_cash' => $cashBalance,
            'in_noncash' => $noncashBalance,
            'out_noncash' => 0.0,
            'saldo_noncash' => $noncashBalance,
            'saldo_all' => $cashBalance + $noncashBalance,
            'detail' => $this->buildDetailRows([], $cashBalance, $noncashBalance),
            'is_opening' => true,
        ];

        $cursor = strtotime($period);
        $end = strtotime(date('Y-m-t', strtotime($period)));
        while ($cursor <= $end) {
            $date = date('Y-m-d', $cursor);
            $move = $movements[$date] ?? [];
            $inCash = (float) ($move['cash']['in'] ?? 0);
            $outCash = (float) ($move['cash']['out'] ?? 0);
            $inNoncash = (float) ($move['noncash']['in'] ?? 0);
            $outNoncash = (float) ($move['noncash']['out'] ?? 0);

            $cashBalance += $inCash - $outCash;
            $noncashBalance += $inNoncash - $outNoncash;
            $rows[] = [
                'tanggal' => $date,
                'in_cash' => $inCash,
                'out_cash' => $outCash,
                'saldo_cash' => $cashBalance,
                'in_noncash' => $inNoncash,
                'out_noncash' => $outNoncash,
                'saldo_noncash' => $noncashBalance,
                'saldo_all' => $cashBalance + $noncashBalance,
                'detail' => $this->buildDetailRows((array) ($move['labels'] ?? []), $cashBalance, $noncashBalance),
                'is_opening' => false,
            ];

            $cursor = strtotime('+1 day', $cursor);
        }

        return $rows;
    }

    private function buildSummary(array $opening, array $movements, array $dailyRows): array
    {
        $summary = [
            'saldo_awal_cash' => (float) ($opening['cash'] ?? 0),
            'saldo_awal_noncash' => (float) ($opening['noncash'] ?? 0),
            'pemasukan_cash' => 0.0,
            'pengeluaran_cash' => 0.0,
            'pemasukan_noncash' => 0.0,
            'pengeluaran_noncash' => 0.0,
            'breakdown' => $this->emptyBreakdown(),
        ];

        foreach ($movements as $move) {
            $summary['pemasukan_cash'] += (float) ($move['cash']['in'] ?? 0);
            $summary['pengeluaran_cash'] += (float) ($move['cash']['out'] ?? 0);
            $summary['pemasukan_noncash'] += (float) ($move['noncash']['in'] ?? 0);
            $summary['pengeluaran_noncash'] += (float) ($move['noncash']['out'] ?? 0);
            foreach (($move['labels'] ?? []) as $label => $total) {
                $summary['breakdown'][$label] = ($summary['breakdown'][$label] ?? 0) + (float) $total;
            }
        }

        $last = end($dailyRows) ?: [];
        $summary['saldo_akhir_cash'] = (float) ($last['saldo_cash'] ?? 0);
        $summary['saldo_akhir_noncash'] = (float) ($last['saldo_noncash'] ?? 0);
        $summary['saldo_akhir_all'] = (float) ($last['saldo_all'] ?? 0);

        return $summary;
    }

    private function buildDetailRows(array $labels, float $cashBalance, float $noncashBalance): array
    {
        $breakdown = $this->emptyBreakdown();
        foreach ($labels as $label => $total) {
            $breakdown[$label] = ($breakdown[$label] ?? 0) + (float) $total;
        }

        $rows = [];
        foreach ($breakdown as $label => $amount) {
            $rows[] = [
                'label' => $label,
                'amount' => (float) $amount,
                'type' => $this->labelType($label),
            ];
        }

        $rows[] = ['label' => 'Sisa Saldo Cash', 'amount' => $cashBalance, 'type' => 'total'];
        $rows[] = ['label' => 'Sisa Saldo Non Tunai', 'amount' => $noncashBalance, 'type' => 'total'];
        $rows[] = ['label' => 'Sisa Saldo Akumulasi', 'amount' => $cashBalance + $noncashBalance, 'type' => 'total'];

        return $rows;
    }

    private function emptyBreakdown(): array
    {
        return array_fill_keys($this->breakdownLabels(), 0.0);
    }

    private function breakdownLabels(): array
    {
        return [
            'Total Bayar Piutang Cash',
            'Total Bayar Piutang Transfer',
            'Total Bayar Piutang QRIS',
            'Total Penjualan Cash',
            'Total Penjualan Transfer',
            'Total Penjualan QRIS',
            'Total Pembayaran Supplier Tunai',
            'Total Pembayaran Supplier Transfer',
            'Total Pengeluaran Kas Beban',
            'Total Pengeluaran Kas Non Beban',
            'Total Pemasukan Kas',
            'Total Pengeluaran Non Tunai Beban',
            'Total Pengeluaran Non Tunai Non Beban',
            'Total Pemasukan Non Tunai',
            'Mutasi Saldo Keluar',
            'Mutasi Saldo Masuk',
        ];
    }

    private function labelType(string $label): string
    {
        return (str_contains($label, 'Pengeluaran') || str_contains($label, 'Pembayaran Supplier') || str_contains($label, 'Mutasi Saldo Keluar')) ? 'out' : 'in';
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

    private function getStores(array $storeIds): array
    {
        [$storeWhere, $binds] = $this->buildStoreWhere('toko_id', $storeIds);
        return $this->db->query(
            "SELECT toko_id, toko_nama
             FROM toko
             WHERE {$storeWhere}
             ORDER BY toko_id",
            $binds
        )->getResultArray();
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

    private function monthStart(string $date): string
    {
        $timestamp = strtotime($date);
        return date('Y-m-01', $timestamp ?: time());
    }

    private function periodLabel(string $period): string
    {
        $months = [
            '01' => 'Januari',
            '02' => 'Februari',
            '03' => 'Maret',
            '04' => 'April',
            '05' => 'Mei',
            '06' => 'Juni',
            '07' => 'Juli',
            '08' => 'Agustus',
            '09' => 'September',
            '10' => 'Oktober',
            '11' => 'November',
            '12' => 'Desember',
        ];

        return ($months[date('m', strtotime($period))] ?? date('F', strtotime($period))) . ' ' . date('Y', strtotime($period));
    }
}
