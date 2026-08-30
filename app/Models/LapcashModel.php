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

        foreach ($this->queryOperasionalLabels($storeIds, $start, $end) as $row) {
            $tanggal = (string) ($row['tanggal'] ?? '');
            $label = (string) ($row['label'] ?? '');
            $total = (float) ($row['total'] ?? 0);
            if ($tanggal === '' || $label === '' || $total == 0.0) {
                continue;
            }
            $movements[$tanggal]['labels'][$label] = ($movements[$tanggal]['labels'][$label] ?? 0) + $total;
        }

        foreach ($this->queryTransferLabels($storeIds, $start, $end) as $row) {
            $tanggal = (string) ($row['tanggal'] ?? '');
            $label = (string) ($row['label'] ?? '');
            $total = (float) ($row['total'] ?? 0);
            if ($tanggal === '' || $label === '' || $total == 0.0) {
                continue;
            }
            $movements[$tanggal]['labels'][$label] = ($movements[$tanggal]['labels'][$label] ?? 0) + $total;
        }

        ksort($movements);
        return $movements;
    }

    private function queryPosPayments(array $storeIds, string $start, string $end): array
    {
        [$storeWhere, $binds] = $this->buildStoreWhere('pp.toko_id', $storeIds);
        return $this->db->query(
            "SELECT DATE(pp.tgl_bayar) AS tanggal,
                    CASE WHEN pp.cara_bayar='TUNAI' THEN 'cash_toko' ELSE 'noncash' END AS channel,
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
                    CASE WHEN pp.cara_bayar='TUNAI' THEN 'cash_toko' ELSE 'noncash' END AS channel,
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
                    CASE WHEN cara_bayar='TUNAI' THEN 'cash_toko' ELSE 'noncash' END AS channel,
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
            "SELECT tanggal, bucket, direction, SUM(total) AS total
             FROM (
                SELECT DATE(km.tanggal) AS tanggal,
                        CASE
                            WHEN COALESCE(km.saldo_channel, 'CASH')='NONCASH' THEN 'noncash'
                            WHEN COALESCE(km.saldo_target, 'TOKO')='PEMILIK' THEN 'cash_pemilik'
                            ELSE 'cash_toko'
                        END AS bucket,
                        CASE WHEN ak.jenis_akun='MASUK' THEN 'in' ELSE 'out' END AS direction,
                        COALESCE(km.nominal,0) AS total
                 FROM kas_mutasi km
                 INNER JOIN akun_kas ak ON ak.nama_akun=km.nama_akun
                 WHERE km.tanggal BETWEEN ? AND ?
                   AND COALESCE(km.tipe_mutasi, 'OPERASIONAL')='OPERASIONAL'
                   AND {$storeWhere}
                UNION ALL
                SELECT DATE(km.tanggal) AS tanggal,
                        CASE
                            WHEN km.saldo_asal='NONCASH' THEN 'noncash'
                            WHEN COALESCE(km.saldo_asal_target, 'TOKO')='PEMILIK' THEN 'cash_pemilik'
                            ELSE 'cash_toko'
                        END AS bucket,
                        'out' AS direction,
                        COALESCE(km.nominal,0) AS total
                 FROM kas_mutasi km
                 WHERE km.tanggal BETWEEN ? AND ?
                   AND km.tipe_mutasi='PINDAH_SALDO'
                   AND {$storeWhere}
                UNION ALL
                SELECT DATE(km.tanggal) AS tanggal,
                        CASE
                            WHEN km.saldo_tujuan='NONCASH' THEN 'noncash'
                            WHEN COALESCE(km.saldo_tujuan_target, 'TOKO')='PEMILIK' THEN 'cash_pemilik'
                            ELSE 'cash_toko'
                        END AS bucket,
                        'in' AS direction,
                        COALESCE(km.nominal,0) AS total
                 FROM kas_mutasi km
                 WHERE km.tanggal BETWEEN ? AND ?
                   AND km.tipe_mutasi='PINDAH_SALDO'
                   AND {$storeWhere}
             ) x
             GROUP BY tanggal, bucket, direction",
            array_merge([$start, $end], $binds, [$start, $end], $binds, [$start, $end], $binds)
        )->getResultArray();
    }

    private function queryOperasionalLabels(array $storeIds, string $start, string $end): array
    {
        [$storeWhere, $binds] = $this->buildStoreWhere('km.toko_id', $storeIds);
        return $this->db->query(
            "SELECT DATE(km.tanggal) AS tanggal,
                    CASE
                        WHEN ak.jenis_akun='MASUK' AND COALESCE(km.saldo_channel, 'CASH')='NONCASH' THEN 'Total Pemasukan Non Tunai'
                        WHEN ak.jenis_akun='MASUK' THEN 'Total Pemasukan Kas'
                        WHEN km.nama_akun='TARIK_KEUNTUNGAN' AND COALESCE(km.saldo_channel, 'CASH')='CASH'
                            THEN CONCAT('Tarik Keuntungan Saldo ', LOWER(COALESCE(km.saldo_target, 'TOKO')))
                        WHEN COALESCE(km.saldo_channel, 'CASH')='NONCASH' AND ak.flag_beban='Y' THEN 'Total Pengeluaran Non Tunai Beban'
                        WHEN COALESCE(km.saldo_channel, 'CASH')='NONCASH' THEN 'Total Pengeluaran Non Tunai Non Beban'
                        WHEN ak.flag_beban='Y' THEN 'Total Pengeluaran Kas Beban'
                        ELSE 'Total Pengeluaran Kas Non Beban'
                    END AS label,
                    SUM(COALESCE(km.nominal,0)) AS total
             FROM kas_mutasi km
             INNER JOIN akun_kas ak ON ak.nama_akun=km.nama_akun
             WHERE km.tanggal BETWEEN ? AND ?
               AND COALESCE(km.tipe_mutasi, 'OPERASIONAL')='OPERASIONAL'
               AND {$storeWhere}
             GROUP BY tanggal, label",
            array_merge([$start, $end], $binds)
        )->getResultArray();
    }

    private function queryTransferLabels(array $storeIds, string $start, string $end): array
    {
        [$storeWhere, $binds] = $this->buildStoreWhere('km.toko_id', $storeIds);
        return $this->db->query(
            "SELECT DATE(km.tanggal) AS tanggal,
                    CASE
                        WHEN km.saldo_asal='CASH' AND km.saldo_tujuan='CASH'
                             AND COALESCE(km.saldo_asal_target, 'TOKO')='TOKO'
                             AND COALESCE(km.saldo_tujuan_target, 'PEMILIK')='PEMILIK' THEN 'Setoran Toko ke Pemilik'
                        WHEN km.saldo_asal='CASH' AND km.saldo_tujuan='CASH'
                             AND COALESCE(km.saldo_asal_target, 'TOKO')='PEMILIK'
                             AND COALESCE(km.saldo_tujuan_target, 'TOKO')='TOKO' THEN 'Tarik Saldo Pemilik ke Toko'
                        WHEN km.saldo_asal='CASH' THEN 'Mutasi Keluar ke Non Tunai'
                        ELSE 'Mutasi Masuk dari Non Tunai'
                    END AS label,
                    SUM(COALESCE(km.nominal,0)) AS total
             FROM kas_mutasi km
             WHERE km.tanggal BETWEEN ? AND ?
               AND km.tipe_mutasi='PINDAH_SALDO'
               AND {$storeWhere}
             GROUP BY tanggal, label",
            array_merge([$start, $end], $binds)
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
        $tokoBalance = (float) ($opening['toko'] ?? 0);
        $pemilikBalance = (float) ($opening['pemilik'] ?? 0);
        $noncashBalance = (float) ($opening['noncash'] ?? 0);
        $cashBalance = $tokoBalance + $pemilikBalance;
        $openingDate = date('Y-m-d', strtotime($period . ' -1 day'));
        $rows[] = [
            'tanggal' => $openingDate,
            'in_cash' => $cashBalance,
            'out_cash' => 0.0,
            'saldo_cash' => $cashBalance,
            'saldo_toko' => $tokoBalance,
            'saldo_pemilik' => $pemilikBalance,
            'in_noncash' => $noncashBalance,
            'out_noncash' => 0.0,
            'saldo_noncash' => $noncashBalance,
            'saldo_all' => $cashBalance + $noncashBalance,
            'detail' => $this->buildDetailRows([], $tokoBalance, $pemilikBalance, $noncashBalance),
            'is_opening' => true,
        ];

        $cursor = strtotime($period);
        $end = strtotime(date('Y-m-t', strtotime($period)));
        while ($cursor <= $end) {
            $date = date('Y-m-d', $cursor);
            $move = $movements[$date] ?? [];
            $inToko = (float) ($move['cash_toko']['in'] ?? 0);
            $outToko = (float) ($move['cash_toko']['out'] ?? 0);
            $inPemilik = (float) ($move['cash_pemilik']['in'] ?? 0);
            $outPemilik = (float) ($move['cash_pemilik']['out'] ?? 0);
            $inNoncash = (float) ($move['noncash']['in'] ?? 0);
            $outNoncash = (float) ($move['noncash']['out'] ?? 0);

            $tokoBalance += $inToko - $outToko;
            $pemilikBalance += $inPemilik - $outPemilik;
            $noncashBalance += $inNoncash - $outNoncash;
            $cashBalance = $tokoBalance + $pemilikBalance;
            $rows[] = [
                'tanggal' => $date,
                'in_cash' => $inToko + $inPemilik,
                'out_cash' => $outToko + $outPemilik,
                'saldo_cash' => $cashBalance,
                'saldo_toko' => $tokoBalance,
                'saldo_pemilik' => $pemilikBalance,
                'in_noncash' => $inNoncash,
                'out_noncash' => $outNoncash,
                'saldo_noncash' => $noncashBalance,
                'saldo_all' => $cashBalance + $noncashBalance,
                'detail' => $this->buildDetailRows((array) ($move['labels'] ?? []), $tokoBalance, $pemilikBalance, $noncashBalance),
                'is_opening' => false,
            ];

            $cursor = strtotime('+1 day', $cursor);
        }

        return $rows;
    }

    private function buildSummary(array $opening, array $movements, array $dailyRows): array
    {
        $summary = [
            'saldo_awal_toko' => (float) ($opening['toko'] ?? 0),
            'saldo_awal_pemilik' => (float) ($opening['pemilik'] ?? 0),
            'saldo_awal_cash' => (float) ($opening['cash'] ?? 0),
            'saldo_awal_noncash' => (float) ($opening['noncash'] ?? 0),
            'pemasukan_cash' => 0.0,
            'pengeluaran_cash' => 0.0,
            'pemasukan_noncash' => 0.0,
            'pengeluaran_noncash' => 0.0,
            'breakdown' => $this->emptyBreakdown(),
        ];

        foreach ($movements as $move) {
            $summary['pemasukan_cash'] += (float) ($move['cash_toko']['in'] ?? 0) + (float) ($move['cash_pemilik']['in'] ?? 0);
            $summary['pengeluaran_cash'] += (float) ($move['cash_toko']['out'] ?? 0) + (float) ($move['cash_pemilik']['out'] ?? 0);
            $summary['pemasukan_noncash'] += (float) ($move['noncash']['in'] ?? 0);
            $summary['pengeluaran_noncash'] += (float) ($move['noncash']['out'] ?? 0);
            foreach (($move['labels'] ?? []) as $label => $total) {
                $summary['breakdown'][$label] = ($summary['breakdown'][$label] ?? 0) + (float) $total;
            }
        }

        $last = end($dailyRows) ?: [];
        $summary['saldo_akhir_toko'] = (float) ($last['saldo_toko'] ?? 0);
        $summary['saldo_akhir_pemilik'] = (float) ($last['saldo_pemilik'] ?? 0);
        $summary['saldo_akhir_cash'] = (float) ($last['saldo_cash'] ?? 0);
        $summary['saldo_akhir_noncash'] = (float) ($last['saldo_noncash'] ?? 0);
        $summary['saldo_akhir_all'] = (float) ($last['saldo_all'] ?? 0);

        return $summary;
    }

    private function buildDetailRows(array $labels, float $tokoBalance, float $pemilikBalance, float $noncashBalance): array
    {
        $breakdown = $this->emptyBreakdown();
        foreach ($labels as $label => $total) {
            $breakdown[$label] = ($breakdown[$label] ?? 0) + (float) $total;
        }

        $rows = [];
        foreach ($breakdown as $label => $amount) {
            if ((float) $amount == 0.0) {
                continue;
            }
            $rows[] = [
                'label' => $label,
                'amount' => (float) $amount,
                'type' => $this->labelType($label),
            ];
        }

        $rows[] = ['label' => 'Sisa Saldo Cash Toko', 'amount' => $tokoBalance, 'type' => 'total'];
        $rows[] = ['label' => 'Sisa Saldo Cash Pemilik', 'amount' => $pemilikBalance, 'type' => 'total'];
        $rows[] = ['label' => 'Sisa Saldo Non Tunai', 'amount' => $noncashBalance, 'type' => 'total'];
        $rows[] = ['label' => 'Sisa Saldo Akumulasi', 'amount' => $tokoBalance + $pemilikBalance + $noncashBalance, 'type' => 'total'];

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
            'Setoran Toko ke Pemilik',
            'Tarik Saldo Pemilik ke Toko',
            'Mutasi Keluar ke Non Tunai',
            'Mutasi Masuk dari Non Tunai',
            'Tarik Keuntungan Saldo toko',
            'Tarik Keuntungan Saldo pemilik',
        ];
    }

    private function labelType(string $label): string
    {
        if (str_contains($label, 'Pengeluaran') || str_contains($label, 'Pembayaran Supplier') || str_contains($label, 'Keluar')) {
            return 'out';
        }
        if (str_contains($label, 'Tarik Keuntungan')) {
            return 'out';
        }

        return 'in';
    }

    private function getOpeningBalance(array $storeIds, string $period): array
    {
        [$storeWhere, $binds] = $this->buildStoreWhere('toko_id', $storeIds);
        $previous = date('Y-m-01', strtotime($period . ' -1 month'));
        $row = $this->db->query(
            "SELECT COALESCE(SUM(COALESCE(saldo_toko, saldo_tunai, 0)),0) AS toko,
                    COALESCE(SUM(COALESCE(saldo_pemilik, 0)),0) AS pemilik,
                    COALESCE(SUM(saldo_transfer + saldo_qris),0) AS noncash
             FROM saldo_cash
             WHERE tahun=? AND bulan=? AND {$storeWhere}",
            array_merge([(int) date('Y', strtotime($previous)), (int) date('m', strtotime($previous))], $binds)
        )->getRowArray() ?: [];

        $toko = (float) ($row['toko'] ?? 0);
        $pemilik = (float) ($row['pemilik'] ?? 0);

        return [
            'toko' => $toko,
            'pemilik' => $pemilik,
            'cash' => $toko + $pemilik,
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
