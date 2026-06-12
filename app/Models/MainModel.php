<?php

namespace App\Models;

use CodeIgniter\Model;

class MainModel extends Model
{
    protected $returnType = 'array';
    protected $protectFields = false;

    public function getDashboard(string $tokoId): array
    {
        $today = date('Y-m-d');
        $monthStart = date('Y-m-01');
        $monthEnd = date('Y-m-t');

        $salesMonth = $this->getSalesSummary($tokoId, $monthStart . ' 00:00:00', $today . ' 23:59:59');
        $salesToday = $this->getSalesSummary($tokoId, $today . ' 00:00:00', $today . ' 23:59:59');
        $cashMonth = $this->getCashSummary($tokoId, $monthStart, $today);
        $shiftCash = $this->getShiftCashSummary($tokoId, $today);
        $receivable = $this->getReceivableSummary($tokoId, $today);
        $payable = $this->getPayableSummary($tokoId, $today);
        $product = $this->getProductSummary($tokoId, $monthStart, $today);
        $tasks = $this->getTaskSummary($tokoId, $today);

        return [
            'generated_at' => date('Y-m-d H:i:s'),
            'today' => $today,
            'period' => [
                'month_start' => $monthStart,
                'month_end' => $monthEnd,
                'label' => date('F Y'),
            ],
            'store' => $this->getStore($tokoId),
            'owner' => [
                'sales' => $salesMonth,
                'profit' => [
                    'laba_kotor' => $salesMonth['laba_kotor'],
                    'margin_pct' => $salesMonth['margin_pct'],
                ],
                'cash' => $cashMonth,
                'receivable' => $receivable,
                'payable' => $payable,
                'product' => $product,
                'alerts' => $this->getOwnerAlerts($salesMonth, $cashMonth, $receivable, $payable, $product, $tasks, $tokoId),
            ],
            'staff' => [
                'shift' => $this->getShiftSummary($tokoId, $today, $salesToday),
                'cash' => $shiftCash,
                'shift_cash_users' => $this->getShiftCashByUser($tokoId, $today),
                'tasks' => $tasks,
                'receivable_due_rows' => $this->getReceivableDueRows($tokoId, $today, 6),
                'stock_check_rows' => $this->getStockCheckRows($tokoId, 6),
                'last_transactions' => $this->getLastTransactions($tokoId, 5),
            ],
        ];
    }

    private function getStore(string $tokoId): array
    {
        $row = $this->db->query(
            "SELECT toko_id, toko_nama, toko_alamat, toko_phone FROM toko WHERE toko_id=:toko_id: LIMIT 1",
            ['toko_id' => $tokoId]
        )->getRowArray();

        return $row ?: ['toko_id' => $tokoId, 'toko_nama' => $tokoId, 'toko_alamat' => '', 'toko_phone' => ''];
    }

    private function getSalesSummary(string $tokoId, string $start, string $end): array
    {
        $row = $this->db->query(
            "SELECT COUNT(DISTINCT jual_id) AS transaksi,
                    COALESCE(SUM(netto),0) AS omzet,
                    COALESCE(SUM(total_hpp),0) AS hpp,
                    COALESCE(SUM(margin_bruto),0) AS laba_kotor,
                    COALESCE(SUM(total_qty),0) AS qty
             FROM penjualan
             WHERE toko_id=:toko_id: AND tgl BETWEEN :start: AND :end:",
            ['toko_id' => $tokoId, 'start' => $start, 'end' => $end]
        )->getRowArray() ?: [];

        $omzet = (float) ($row['omzet'] ?? 0);
        $transaksi = (int) ($row['transaksi'] ?? 0);
        $labaKotor = (float) ($row['laba_kotor'] ?? 0);

        return [
            'omzet' => $omzet,
            'transaksi' => $transaksi,
            'rata_transaksi' => $transaksi > 0 ? $omzet / $transaksi : 0.0,
            'hpp' => (float) ($row['hpp'] ?? 0),
            'laba_kotor' => $labaKotor,
            'margin_pct' => $omzet > 0 ? ($labaKotor / $omzet) * 100 : 0.0,
            'qty' => (float) ($row['qty'] ?? 0),
        ];
    }

    private function getCashSummary(string $tokoId, string $periodStart, string $asOfDate): array
    {
        $opening = $this->getOpeningCash($tokoId, $periodStart);
        $start = $periodStart . ' 00:00:00';
        $end = $asOfDate . ' 23:59:59';
        $payments = $this->getPaymentMovements($tokoId, $start, $end, $periodStart);
        $kas = $this->getKasMovements($tokoId, $start, $end);

        $kasMasuk = $payments['cash_in'] + $payments['noncash_in'] + $kas['cash_in'] + $kas['noncash_in'];
        $kasKeluar = $payments['cash_out'] + $payments['noncash_out'] + $kas['cash_out'] + $kas['noncash_out'];
        $saldoAwal = $opening['cash'] + $opening['noncash'];
        $saldoAkhir = $saldoAwal + $kasMasuk - $kasKeluar;

        return [
            'saldo_awal' => $saldoAwal,
            'kas_masuk' => $kasMasuk,
            'kas_keluar' => $kasKeluar,
            'saldo_akhir' => $saldoAkhir,
            'tunai_akhir' => $opening['cash'] + $payments['cash_in'] + $kas['cash_in'] - $payments['cash_out'] - $kas['cash_out'],
            'non_tunai_akhir' => $opening['noncash'] + $payments['noncash_in'] + $kas['noncash_in'] - $payments['noncash_out'] - $kas['noncash_out'],
        ];
    }

    private function getShiftCashSummary(string $tokoId, string $date): array
    {
        $start = $date . ' 00:00:00';
        $end = $date . ' 23:59:59';
        $payments = $this->getPaymentMovements($tokoId, $start, $end, $date);
        $kas = $this->getKasMovements($tokoId, $start, $end);
        $saldoSistem = $payments['cash_in'] + $kas['cash_in'] - $payments['cash_out'] - $kas['cash_out'];

        return [
            'saldo_sistem' => $saldoSistem,
            'kas_fisik' => null,
            'selisih' => null,
            'kas_masuk_tunai' => $payments['cash_in'] + $kas['cash_in'],
            'kas_keluar_tunai' => $payments['cash_out'] + $kas['cash_out'],
        ];
    }

    private function getShiftCashByUser(string $tokoId, string $date): array
    {
        $start = $date . ' 00:00:00';
        $end = $date . ' 23:59:59';
        $users = [];

        $this->mergeShiftCashUsers($users, $this->db->query(
            "SELECT COALESCE(NULLIF(pp.updid, ''), NULLIF(j.updid, ''), '-') AS username,
                    COALESCE(u.fullname, NULLIF(pp.updid, ''), NULLIF(j.updid, ''), '-') AS fullname,
                    COALESCE(SUM(pp.nominal_bayar),0) AS pos_tunai
             FROM penjualan_pembayaran pp
             INNER JOIN penjualan j ON j.toko_id=pp.toko_id AND j.jual_id=pp.jual_id
             LEFT JOIN tb_user u ON u.username=COALESCE(NULLIF(pp.updid, ''), NULLIF(j.updid, ''))
             WHERE pp.toko_id=:toko_id:
               AND pp.tgl_bayar BETWEEN :start: AND :end:
               AND DATE(j.tgl)=:date:
               AND pp.cara_bayar='TUNAI'
             GROUP BY COALESCE(NULLIF(pp.updid, ''), NULLIF(j.updid, ''), '-'), COALESCE(u.fullname, NULLIF(pp.updid, ''), NULLIF(j.updid, ''), '-')",
            ['toko_id' => $tokoId, 'start' => $start, 'end' => $end, 'date' => $date]
        )->getResultArray());

        $this->mergeShiftCashUsers($users, $this->db->query(
            "SELECT COALESCE(NULLIF(pp.updid, ''), NULLIF(j.updid, ''), '-') AS username,
                    COALESCE(u.fullname, NULLIF(pp.updid, ''), NULLIF(j.updid, ''), '-') AS fullname,
                    COALESCE(SUM(pp.nominal_bayar),0) AS kas_masuk
             FROM penjualan_pembayaran pp
             INNER JOIN penjualan j ON j.toko_id=pp.toko_id AND j.jual_id=pp.jual_id
             LEFT JOIN tb_user u ON u.username=COALESCE(NULLIF(pp.updid, ''), NULLIF(j.updid, ''))
             WHERE pp.toko_id=:toko_id:
               AND pp.tgl_bayar BETWEEN :start: AND :end:
               AND DATE(j.tgl)<>:date:
               AND j.is_kredit='1'
               AND pp.cara_bayar='TUNAI'
             GROUP BY COALESCE(NULLIF(pp.updid, ''), NULLIF(j.updid, ''), '-'), COALESCE(u.fullname, NULLIF(pp.updid, ''), NULLIF(j.updid, ''), '-')",
            ['toko_id' => $tokoId, 'start' => $start, 'end' => $end, 'date' => $date]
        )->getResultArray());

        $this->mergeShiftCashUsers($users, $this->db->query(
            "SELECT COALESCE(NULLIF(km.updid, ''), '-') AS username,
                    COALESCE(u.fullname, NULLIF(km.updid, ''), '-') AS fullname,
                    COALESCE(SUM(CASE WHEN ak.jenis_akun='MASUK' THEN km.nominal ELSE 0 END),0) AS kas_masuk,
                    COALESCE(SUM(CASE WHEN ak.jenis_akun='KELUAR' THEN km.nominal ELSE 0 END),0) AS kas_keluar
             FROM kas_mutasi km
             INNER JOIN akun_kas ak ON ak.nama_akun=km.nama_akun
             LEFT JOIN tb_user u ON u.username=km.updid
             WHERE km.toko_id=:toko_id:
               AND km.tanggal BETWEEN :start: AND :end:
               AND COALESCE(km.saldo_channel, 'CASH')='CASH'
               AND COALESCE(km.tipe_mutasi, 'OPERASIONAL')='OPERASIONAL'
             GROUP BY COALESCE(NULLIF(km.updid, ''), '-'), COALESCE(u.fullname, NULLIF(km.updid, ''), '-')",
            ['toko_id' => $tokoId, 'start' => $start, 'end' => $end]
        )->getResultArray());

        $this->mergeShiftCashUsers($users, $this->db->query(
            "SELECT COALESCE(NULLIF(pb.username, ''), '-') AS username,
                    COALESCE(u.fullname, NULLIF(pb.username, ''), '-') AS fullname,
                    COALESCE(SUM(jumlah_bayar),0) AS bayar_hutang_tunai
             FROM pembelian_pembayaran pb
             LEFT JOIN tb_user u ON u.username=pb.username
             WHERE pb.toko_id=:toko_id:
               AND pb.tanggal_bayar BETWEEN :start: AND :end:
               AND pb.cara_bayar='TUNAI'
             GROUP BY COALESCE(NULLIF(pb.username, ''), '-'), COALESCE(u.fullname, NULLIF(pb.username, ''), '-')",
            ['toko_id' => $tokoId, 'start' => $start, 'end' => $end]
        )->getResultArray());

        $rows = array_values($users);
        foreach ($rows as &$row) {
            $row['pengeluaran_kas'] = (float) $row['kas_keluar'] + (float) $row['bayar_hutang_tunai'];
            $row['saldo_sistem'] = (float) $row['pos_tunai'] + (float) $row['kas_masuk'] - (float) $row['pengeluaran_kas'];
        }
        unset($row);

        usort($rows, static fn(array $a, array $b): int => strcmp((string) $a['fullname'], (string) $b['fullname']));
        return $rows;
    }

    private function mergeShiftCashUsers(array &$users, array $rows): void
    {
        foreach ($rows as $row) {
            $username = (string) ($row['username'] ?? '-');
            if (!isset($users[$username])) {
                $users[$username] = [
                    'username' => $username,
                    'fullname' => (string) ($row['fullname'] ?? $username),
                    'pos_tunai' => 0.0,
                    'kas_masuk' => 0.0,
                    'kas_keluar' => 0.0,
                    'bayar_hutang_tunai' => 0.0,
                    'pengeluaran_kas' => 0.0,
                    'saldo_sistem' => 0.0,
                ];
            }

            foreach (['pos_tunai', 'kas_masuk', 'kas_keluar', 'bayar_hutang_tunai'] as $field) {
                $users[$username][$field] += (float) ($row[$field] ?? 0);
            }
        }
    }

    private function getOpeningCash(string $tokoId, string $periodStart): array
    {
        $previous = date('Y-m-01', strtotime($periodStart . ' -1 month'));
        $row = $this->db->query(
            "SELECT COALESCE(SUM(saldo_tunai),0) AS cash,
                    COALESCE(SUM(saldo_transfer + saldo_qris),0) AS noncash
             FROM saldo_cash
             WHERE toko_id=:toko_id: AND tahun=:tahun: AND bulan=:bulan:",
            [
                'toko_id' => $tokoId,
                'tahun' => (int) date('Y', strtotime($previous)),
                'bulan' => (int) date('m', strtotime($previous)),
            ]
        )->getRowArray() ?: [];

        return [
            'cash' => (float) ($row['cash'] ?? 0),
            'noncash' => (float) ($row['noncash'] ?? 0),
        ];
    }

    private function getPaymentMovements(string $tokoId, string $start, string $end, string $periodStart): array
    {
        $map = ['cash_in' => 0.0, 'cash_out' => 0.0, 'noncash_in' => 0.0, 'noncash_out' => 0.0];
        $saleRows = $this->db->query(
            "SELECT pp.cara_bayar, COALESCE(SUM(pp.nominal_bayar),0) AS total
             FROM penjualan_pembayaran pp
             INNER JOIN penjualan j ON j.toko_id=pp.toko_id AND j.jual_id=pp.jual_id
             WHERE pp.toko_id=:toko_id:
               AND pp.tgl_bayar BETWEEN :start: AND :end:
               AND DATE(j.tgl) >= :period_start:
               AND pp.cara_bayar IN ('TUNAI','TRANSFER','QRIS')
             GROUP BY pp.cara_bayar",
            ['toko_id' => $tokoId, 'start' => $start, 'end' => $end, 'period_start' => $periodStart]
        )->getResultArray();
        $this->mergePaymentRows($map, $saleRows, 'in');

        $piutangRows = $this->db->query(
            "SELECT pp.cara_bayar, COALESCE(SUM(pp.nominal_bayar),0) AS total
             FROM penjualan_pembayaran pp
             INNER JOIN penjualan j ON j.toko_id=pp.toko_id AND j.jual_id=pp.jual_id
             WHERE pp.toko_id=:toko_id:
               AND pp.tgl_bayar BETWEEN :start: AND :end:
               AND DATE(j.tgl) < :period_start:
               AND j.is_kredit='1'
               AND pp.cara_bayar IN ('TUNAI','TRANSFER','QRIS')
             GROUP BY pp.cara_bayar",
            ['toko_id' => $tokoId, 'start' => $start, 'end' => $end, 'period_start' => $periodStart]
        )->getResultArray();
        $this->mergePaymentRows($map, $piutangRows, 'in');

        $supplierRows = $this->db->query(
            "SELECT cara_bayar, COALESCE(SUM(jumlah_bayar),0) AS total
             FROM pembelian_pembayaran
             WHERE toko_id=:toko_id:
               AND tanggal_bayar BETWEEN :start: AND :end:
               AND cara_bayar IN ('TUNAI','TRANSFER')
             GROUP BY cara_bayar",
            ['toko_id' => $tokoId, 'start' => $start, 'end' => $end]
        )->getResultArray();
        $this->mergePaymentRows($map, $supplierRows, 'out');

        return $map;
    }

    private function getKasMovements(string $tokoId, string $start, string $end): array
    {
        $rows = $this->db->query(
            "SELECT channel, direction, COALESCE(SUM(total),0) AS total
             FROM (
                SELECT CASE WHEN COALESCE(km.saldo_channel, 'CASH')='NONCASH' THEN 'noncash' ELSE 'cash' END AS channel,
                       CASE WHEN ak.jenis_akun='MASUK' THEN 'in' ELSE 'out' END AS direction,
                       COALESCE(km.nominal,0) AS total
                FROM kas_mutasi km
                INNER JOIN akun_kas ak ON ak.nama_akun=km.nama_akun
                WHERE km.toko_id=:toko_id:
                  AND km.tanggal BETWEEN :start: AND :end:
                  AND COALESCE(km.tipe_mutasi, 'OPERASIONAL')='OPERASIONAL'
                UNION ALL
                SELECT CASE WHEN km.saldo_asal='NONCASH' THEN 'noncash' ELSE 'cash' END AS channel,
                       'out' AS direction,
                       COALESCE(km.nominal,0) AS total
                FROM kas_mutasi km
                WHERE km.toko_id=:toko_id:
                  AND km.tanggal BETWEEN :start: AND :end:
                  AND km.tipe_mutasi='PINDAH_SALDO'
                UNION ALL
                SELECT CASE WHEN km.saldo_tujuan='NONCASH' THEN 'noncash' ELSE 'cash' END AS channel,
                       'in' AS direction,
                       COALESCE(km.nominal,0) AS total
                FROM kas_mutasi km
                WHERE km.toko_id=:toko_id:
                  AND km.tanggal BETWEEN :start: AND :end:
                  AND km.tipe_mutasi='PINDAH_SALDO'
             ) x
             GROUP BY channel, direction",
            ['toko_id' => $tokoId, 'start' => $start, 'end' => $end]
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

    private function getReceivableSummary(string $tokoId, string $today): array
    {
        $row = $this->db->query(
            "SELECT COALESCE(SUM(sisa_piutang),0) AS total,
                    COALESCE(SUM(CASE WHEN jatuh_tempo=:today: THEN sisa_piutang ELSE 0 END),0) AS jatuh_tempo,
                    COALESCE(SUM(CASE WHEN jatuh_tempo<:today: THEN sisa_piutang ELSE 0 END),0) AS lewat_tempo,
                    COUNT(*) AS total_nota
             FROM penjualan
             WHERE toko_id=:toko_id:
               AND is_kredit='1'
               AND status_bayar IN ('BELUM','CICIL')
               AND sisa_piutang>0",
            ['toko_id' => $tokoId, 'today' => $today]
        )->getRowArray() ?: [];

        return [
            'total' => (float) ($row['total'] ?? 0),
            'jatuh_tempo' => (float) ($row['jatuh_tempo'] ?? 0),
            'lewat_tempo' => (float) ($row['lewat_tempo'] ?? 0),
            'total_nota' => (int) ($row['total_nota'] ?? 0),
        ];
    }

    private function getPayableSummary(string $tokoId, string $today): array
    {
        $row = $this->db->query(
            "SELECT COALESCE(SUM(sisa_bayar),0) AS total,
                    COALESCE(SUM(CASE WHEN jatuh_tempo=:today: THEN sisa_bayar ELSE 0 END),0) AS jatuh_tempo,
                    COALESCE(SUM(CASE WHEN jatuh_tempo<:today: THEN sisa_bayar ELSE 0 END),0) AS lewat_tempo,
                    COUNT(*) AS total_nota
             FROM pembelian
             WHERE toko_id=:toko_id:
               AND is_kredit=1
               AND status_bayar IN ('BELUM','CICIL')
               AND sisa_bayar>0",
            ['toko_id' => $tokoId, 'today' => $today]
        )->getRowArray() ?: [];

        return [
            'total' => (float) ($row['total'] ?? 0),
            'jatuh_tempo' => (float) ($row['jatuh_tempo'] ?? 0),
            'lewat_tempo' => (float) ($row['lewat_tempo'] ?? 0),
            'total_nota' => (int) ($row['total_nota'] ?? 0),
        ];
    }

    private function getProductSummary(string $tokoId, string $monthStart, string $today): array
    {
        return [
            'terlaris' => $this->getBestSellers($tokoId, $monthStart, $today, 5),
            'margin_tinggi' => $this->getHighMarginItems($tokoId, $monthStart, $today, 5),
            'stok_lambat' => $this->getSlowStockRows($tokoId, 5),
            'stok_minus_count' => $this->countStockMinus($tokoId),
            'salah_harga_count' => $this->countBadPriceSettings($tokoId),
        ];
    }

    private function getBestSellers(string $tokoId, string $startDate, string $endDate, int $limit): array
    {
        return $this->db->query(
            "SELECT d.kode_item, COALESCE(p.nama_item, d.kode_item) AS nama_item,
                    COALESCE(SUM(d.qty_jual),0) AS qty,
                    COALESCE(SUM(d.netto),0) AS omzet
             FROM penjualan_detail d
             INNER JOIN penjualan j ON j.toko_id=d.toko_id AND j.jual_id=d.jual_id
             LEFT JOIN prodmast p ON p.kode_item=d.kode_item
             WHERE d.toko_id=:toko_id: AND j.tgl BETWEEN :start: AND :end:
             GROUP BY d.kode_item, COALESCE(p.nama_item, d.kode_item)
             ORDER BY qty DESC, omzet DESC
             LIMIT " . (int) $limit,
            ['toko_id' => $tokoId, 'start' => $startDate . ' 00:00:00', 'end' => $endDate . ' 23:59:59']
        )->getResultArray();
    }

    private function getHighMarginItems(string $tokoId, string $startDate, string $endDate, int $limit): array
    {
        return $this->db->query(
            "SELECT d.kode_item, COALESCE(p.nama_item, d.kode_item) AS nama_item,
                    COALESCE(SUM(d.netto - (d.qty_jual * d.harga_pokok)),0) AS margin,
                    CASE WHEN COALESCE(SUM(d.netto),0)>0
                         THEN (COALESCE(SUM(d.netto - (d.qty_jual * d.harga_pokok)),0) / COALESCE(SUM(d.netto),0)) * 100
                         ELSE 0 END AS margin_pct
             FROM penjualan_detail d
             INNER JOIN penjualan j ON j.toko_id=d.toko_id AND j.jual_id=d.jual_id
             LEFT JOIN prodmast p ON p.kode_item=d.kode_item
             WHERE d.toko_id=:toko_id: AND j.tgl BETWEEN :start: AND :end:
             GROUP BY d.kode_item, COALESCE(p.nama_item, d.kode_item)
             HAVING margin > 0
             ORDER BY margin DESC
             LIMIT " . (int) $limit,
            ['toko_id' => $tokoId, 'start' => $startDate . ' 00:00:00', 'end' => $endDate . ' 23:59:59']
        )->getResultArray();
    }

    private function getSlowStockRows(string $tokoId, int $limit): array
    {
        return $this->db->query(
            "SELECT st.kode_item, COALESCE(p.nama_item, st.kode_item) AS nama_item,
                    COALESCE(st.qty,0) AS qty,
                    COALESCE(st.spd,0) AS spd,
                    CASE WHEN COALESCE(st.spd,0)>0 THEN COALESCE(st.qty,0) / COALESCE(st.spd,0) ELSE 999999 END AS cover_hari,
                    COALESCE(st.rp_saldo_akh,0) AS nilai_stok
             FROM stmast st
             LEFT JOIN prodmast p ON p.kode_item=st.kode_item
             WHERE st.toko_id=:toko_id:
               AND COALESCE(st.qty,0)>0
               AND (COALESCE(st.spd,0)<=0 OR COALESCE(st.qty,0) / NULLIF(st.spd,0)>30)
             ORDER BY nilai_stok DESC, cover_hari DESC
             LIMIT " . (int) $limit,
            ['toko_id' => $tokoId]
        )->getResultArray();
    }

    private function countStockMinus(string $tokoId): int
    {
        $row = $this->db->query(
            "SELECT COUNT(*) AS total FROM stmast WHERE toko_id=:toko_id: AND COALESCE(qty,0)<0",
            ['toko_id' => $tokoId]
        )->getRowArray() ?: [];
        return (int) ($row['total'] ?? 0);
    }

    private function countBadPriceSettings(string $tokoId): int
    {
        $row = $this->db->query(
            "SELECT COUNT(*) AS total
             FROM prodmast_store ps
             INNER JOIN prodmast_satuan satuan ON satuan.kode_item=ps.kode_item AND satuan.sat_id=ps.sat_id AND satuan.qty_konversi=1
             WHERE ps.toko_id=:toko_id:
               AND COALESCE(ps.status_item, 'Y')='Y'
               AND (
                    COALESCE(ps.harga_jual,0)<=COALESCE(ps.harga_pokok,0)
                   OR ps.harga_pokok<=0 OR ps.harga_jual<=0 
               )",
            ['toko_id' => $tokoId]
        )->getRowArray() ?: [];
        return (int) ($row['total'] ?? 0);
    }

    private function getOwnerAlerts(array $sales, array $cash, array $receivable, array $payable, array $product, array $tasks, string $tokoId): array
    {
        $alerts = [];
        if (($product['stok_minus_count'] ?? 0) > 0) {
            $alerts[] = ['level' => 'danger', 'title' => 'Stok minus', 'message' => ($product['stok_minus_count'] ?? 0) . ' item memiliki stok minus.'];
        }
        if (($product['salah_harga_count'] ?? 0) > 0) {
            $alerts[] = ['level' => 'warning', 'title' => 'Salah setting harga', 'message' => ($product['salah_harga_count'] ?? 0) . ' item harga jualnya di bawah HPP atau target margin.'];
        }
        if (($receivable['lewat_tempo'] ?? 0) > 0) {
            $alerts[] = ['level' => 'warning', 'title' => 'Piutang lewat tempo', 'message' => 'Piutang lewat tempo ' . $this->formatRp($receivable['lewat_tempo']) . '.'];
        }
        if (($payable['jatuh_tempo'] ?? 0) > 0 || ($payable['lewat_tempo'] ?? 0) > 0) {
            $alerts[] = ['level' => 'danger', 'title' => 'Hutang perlu dibayar', 'message' => 'Hutang jatuh/lewat tempo ' . $this->formatRp(($payable['jatuh_tempo'] ?? 0) + ($payable['lewat_tempo'] ?? 0)) . '.'];
        }
        if (($sales['margin_pct'] ?? 0) < $this->getPreviousMonthMarginPct($tokoId) && ($sales['omzet'] ?? 0) > 0) {
            $alerts[] = ['level' => 'warning', 'title' => 'Margin turun', 'message' => 'Margin bulan ini lebih rendah dari bulan lalu.'];
        }
        if (!empty($tasks['belum_closing'])) {
            $alerts[] = ['level' => 'info', 'title' => 'Belum closing', 'message' => 'Periode closing toko belum sampai bulan berjalan.'];
        }
        if (($cash['saldo_akhir'] ?? 0) < 0) {
            $alerts[] = ['level' => 'danger', 'title' => 'Saldo kas negatif', 'message' => 'Saldo akhir kas sistem bernilai negatif.'];
        }
        if (empty($alerts)) {
            $alerts[] = ['level' => 'success', 'title' => 'Tidak ada alert kritis', 'message' => 'Belum ada masalah utama dari indikator dashboard.'];
        }
        return $alerts;
    }

    private function getPreviousMonthMarginPct(string $tokoId): float
    {
        $previousStart = date('Y-m-01', strtotime('first day of previous month'));
        $previousEnd = date('Y-m-t', strtotime($previousStart));
        $sales = $this->getSalesSummary($tokoId, $previousStart . ' 00:00:00', $previousEnd . ' 23:59:59');
        return (float) ($sales['margin_pct'] ?? 0);
    }

    private function getShiftSummary(string $tokoId, string $today, array $salesToday): array
    {
        $rows = $this->db->query(
            "SELECT pp.cara_bayar, COALESCE(SUM(pp.nominal_bayar),0) AS total
             FROM penjualan_pembayaran pp
             INNER JOIN penjualan j ON j.toko_id=pp.toko_id AND j.jual_id=pp.jual_id
             WHERE pp.toko_id=:toko_id:
               AND pp.tgl_bayar BETWEEN :start: AND :end:
               AND DATE(j.tgl)=:today:
             GROUP BY pp.cara_bayar",
            ['toko_id' => $tokoId, 'start' => $today . ' 00:00:00', 'end' => $today . ' 23:59:59', 'today' => $today]
        )->getResultArray();

        $map = ['tunai' => 0.0, 'transfer' => 0.0, 'qris' => 0.0, 'piutang' => 0.0];
        foreach ($rows as $row) {
            $method = strtoupper((string) ($row['cara_bayar'] ?? ''));
            if ($method === 'TUNAI') {
                $map['tunai'] += (float) ($row['total'] ?? 0);
            } elseif ($method === 'TRANSFER') {
                $map['transfer'] += (float) ($row['total'] ?? 0);
            } elseif ($method === 'QRIS') {
                $map['qris'] += (float) ($row['total'] ?? 0);
            }
        }

        $piutang = $this->db->query(
            "SELECT COALESCE(SUM(sisa_piutang),0) AS total
             FROM penjualan
             WHERE toko_id=:toko_id:
               AND DATE(tgl)=:today:
               AND is_kredit='1'
               AND sisa_piutang>0",
            ['toko_id' => $tokoId, 'today' => $today]
        )->getRowArray() ?: [];
        $map['piutang'] = (float) ($piutang['total'] ?? 0);

        return array_merge($map, [
            'transaksi' => (int) ($salesToday['transaksi'] ?? 0),
            'omzet' => (float) ($salesToday['omzet'] ?? 0),
        ]);
    }

    private function getTaskSummary(string $tokoId, string $today): array
    {
        $unpaid = $this->db->query(
            "SELECT COUNT(*) AS total
             FROM penjualan
             WHERE toko_id=:toko_id:
               AND is_kredit='1'
               AND status_bayar IN ('BELUM','CICIL')
               AND sisa_piutang>0",
            ['toko_id' => $tokoId]
        )->getRowArray() ?: [];
        $po = $this->db->query(
            "SELECT COUNT(*) AS total
             FROM pembelian
             WHERE toko_id=:toko_id: AND status_nota='PO'",
            ['toko_id' => $tokoId]
        )->getRowArray() ?: [];
        $closing = $this->db->query(
            "SELECT nilai FROM const WHERE rkey='closing' AND toko_id=:toko_id: LIMIT 1",
            ['toko_id' => $tokoId]
        )->getRowArray() ?: [];

        $closingPeriod = !empty($closing['nilai']) ? date('Y-m-01', strtotime((string) $closing['nilai'])) : date('Y-m-01');
        return [
            'belum_lunas' => (int) ($unpaid['total'] ?? 0),
            'belum_closing' => $closingPeriod < date('Y-m-01'),
            'closing_period' => $closingPeriod,
            'perlu_validasi' => (int) ($po['total'] ?? 0) + $this->countBadPriceSettings($tokoId),
            'po_belum_terima' => (int) ($po['total'] ?? 0),
            'transfer_pending_approve' => $this->countTransferPendingApprove($tokoId),
        ];
    }

    private function countTransferPendingApprove(string $tokoId): int
    {
        $row = $this->db->query(
            "SELECT COUNT(*) AS total
             FROM transfer_toko
             WHERE tujuan_toko_id=:toko_id:
               AND status_transfer='KIRIM'",
            ['toko_id' => $tokoId]
        )->getRowArray() ?: [];

        return (int) ($row['total'] ?? 0);
    }

    private function getReceivableDueRows(string $tokoId, string $today, int $limit): array
    {
        return $this->db->query(
            "SELECT j.jual_id, j.tgl, j.jatuh_tempo, j.sisa_piutang,
                    COALESCE(c.nama, 'Pelanggan Umum') AS nama_customer
             FROM penjualan j
             LEFT JOIN customer c ON c.cust_id=j.cust_id
             WHERE j.toko_id=:toko_id:
               AND j.is_kredit='1'
               AND j.status_bayar IN ('BELUM','CICIL')
               AND j.sisa_piutang>0
               AND j.jatuh_tempo<=:today:
             ORDER BY j.jatuh_tempo ASC, j.tgl ASC
             LIMIT " . (int) $limit,
            ['toko_id' => $tokoId, 'today' => $today]
        )->getResultArray();
    }

    private function getStockCheckRows(string $tokoId, int $limit): array
    {
        return $this->db->query(
            "SELECT st.kode_item, COALESCE(p.nama_item, st.kode_item) AS nama_item,
                    COALESCE(st.qty,0) AS qty, COALESCE(st.spd,0) AS spd
             FROM stmast st
             LEFT JOIN prodmast p ON p.kode_item=st.kode_item
             WHERE st.toko_id=:toko_id:
               AND COALESCE(st.spd,0)>0
               AND (COALESCE(st.qty,0)<0 OR COALESCE(st.qty,0)<=COALESCE(st.spd,0)*3)
             ORDER BY st.qty ASC, st.spd DESC
             LIMIT " . (int) $limit,
            ['toko_id' => $tokoId]
        )->getResultArray();
    }

    private function getLastTransactions(string $tokoId, int $limit): array
    {
        return $this->db->query(
            "SELECT j.jual_id, j.tgl, j.netto, j.status_bayar, j.is_kredit,
                    COALESCE(c.nama, 'Pelanggan Umum') AS nama_customer
             FROM penjualan j
             LEFT JOIN customer c ON c.cust_id=j.cust_id
             WHERE j.toko_id=:toko_id:
             ORDER BY j.tgl DESC, j.jual_id DESC
             LIMIT " . (int) $limit,
            ['toko_id' => $tokoId]
        )->getResultArray();
    }

    private function formatRp(float $value): string
    {
        return 'Rp ' . number_format($value, 0, ',', '.');
    }
}
