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
        $adjustmentBeban = $this->getAdjustmentBebanSummary($storeIds, $start, $end);
        $balanceAsOfDate = date('Y-m-d');
        $kas = $this->getCashPosition($storeIds, $balanceAsOfDate);
        $hutang = $this->getTotalHutang($storeIds);
        $piutang = $this->getTotalPiutang($storeIds);
        $stok = $this->getTotalStokRupiah($storeIds);

        $totalBeban = $beban['total_beban'] + $adjustmentBeban['beban_hilang'] + $adjustmentBeban['beban_rusak'];
        $labaBersih = $sales['laba_kotor'] - $returPenjualan - $totalBeban;
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
                'beban_hilang' => $adjustmentBeban['beban_hilang'],
                'beban_rusak' => $adjustmentBeban['beban_rusak'],
                'total_beban' => $totalBeban,
                'laba_bersih' => $labaBersih,
                'saldo_kas_tunai' => $kas['saldo_tunai'],
                'saldo_kas_nontunai' => $kas['saldo_nontunai'],
                'saldo_kas_toko' => $kas['saldo_toko'],
                'saldo_kas_pemilik' => $kas['saldo_pemilik'],
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
                ['label' => 'Beban Hilang', 'amount' => $adjustmentBeban['beban_hilang'], 'type' => 'out'],
                ['label' => 'Beban Rusak', 'amount' => $adjustmentBeban['beban_rusak'], 'type' => 'out'],
                ['label' => 'Laba Bersih', 'amount' => $labaBersih, 'type' => 'total'],
            ],
            'balance_rows' => [
                ['label' => 'Saldo Kas Tunai Toko', 'amount' => $kas['saldo_toko'], 'type' => 'asset'],
                ['label' => 'Saldo Kas Tunai Pemilik', 'amount' => $kas['saldo_pemilik'], 'type' => 'asset'],
                ['label' => 'Saldo Kas Non Tunai', 'amount' => $kas['saldo_nontunai'], 'type' => 'asset'],
                ['label' => 'Total Hutang', 'amount' => $hutang, 'type' => 'liability'],
                ['label' => 'Total Piutang', 'amount' => $piutang, 'type' => 'asset'],
                ['label' => 'Total Stok Rupiah', 'amount' => $stok, 'type' => 'asset'],
                ['label' => 'Saldo Akhir', 'amount' => $saldoAkhir, 'type' => 'total'],
            ],
        ];
    }

    private function getAdjustmentBebanSummary(array $storeIds, string $start, string $end): array
    {
        [$storeWhere, $binds] = $this->buildStoreWhere('a.toko_id', $storeIds);
        $row = $this->db->query(
            "SELECT
                COALESCE(SUM(CASE WHEN a.istype='SO' THEN COALESCE(a.gross,0) ELSE 0 END),0) AS beban_hilang,
                COALESCE(SUM(CASE WHEN a.istype='BAP' THEN COALESCE(a.gross,0) ELSE 0 END),0) AS beban_rusak
             FROM `adjust` a
             WHERE a.tanggal BETWEEN ? AND ?
               AND a.istype IN ('SO','BAP')
               AND {$storeWhere}",
            array_merge([$start, $end], $binds)
        )->getRowArray() ?: [];

        return [
            'beban_hilang' => (float) ($row['beban_hilang'] ?? 0),
            'beban_rusak' => (float) ($row['beban_rusak'] ?? 0),
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
        $realtime = (new KasModel())->getRealtimeCash($storeIds, $asOfDate);

        return [
            'saldo_tunai' => $realtime['total_cash'],
            'saldo_nontunai' => $realtime['saldo_noncash'],
            'saldo_toko' => $realtime['saldo_toko'],
            'saldo_pemilik' => $realtime['saldo_pemilik'],
        ];
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
