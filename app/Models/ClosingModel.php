<?php

namespace App\Models;

use CodeIgniter\Model;
use Throwable;

class ClosingModel extends Model
{
    protected $returnType = 'array';
    protected $protectFields = false;

    public function getDashboard(string $tokoId): array
    {
        $closingDate = $this->ensureClosingDate($tokoId);
        $periode = $this->monthStart($closingDate);
        $periodEnd = date('Y-m-t', strtotime($periode));

        return [
            'closing_date' => $periode,
            'period_label' => date('F Y', strtotime($periode)),
            'period_end' => $periodEnd,
            'stock_summary' => $this->getStockSummary($tokoId),
            'cash_flow' => $this->buildCashFlow($tokoId, $periode, (string) session('username')),
            'last_cash' => $this->getSaldoCash($tokoId, $periode),
            'logs' => $this->getLogs($tokoId),
        ];
    }

    public function closeStore(string $tokoId, string $createdBy, string $mode = 'WEB'): array
    {
        $periode = $this->ensureClosingDate($tokoId);
        return $this->closePeriod($tokoId, $periode, $createdBy, $mode, true);
    }

    public function closeAllDueStores(string $createdBy = 'CLI'): array
    {
        $rows = $this->db->query("SELECT toko_id FROM toko ORDER BY toko_id")->getResultArray();
        $results = [];
        $currentMonth = date('Y-m-01');

        foreach ($rows as $row) {
            $tokoId = (string) ($row['toko_id'] ?? '');
            if ($tokoId === '') {
                continue;
            }

            $periode = $this->ensureClosingDate($tokoId);
            if ($periode >= $currentMonth) {
                $message = 'Belum perlu closing. Periode aktif ' . $periode;
                $this->writeLog($tokoId, $periode, 'CLI', 'SUCCESS', $message, [], $createdBy);
                $results[] = ['toko_id' => $tokoId, 'tipe' => 'skip', 'data' => $message];
                continue;
            }

            $results[] = array_merge(['toko_id' => $tokoId], $this->closePeriod($tokoId, $periode, $createdBy, 'CLI', true));
        }

        return $results;
    }

    public function recloseFrom(string $tokoId, string $startPeriod, string $createdBy): array
    {
        $startPeriod = $this->monthStart($startPeriod);
        $activePeriod = $this->ensureClosingDate($tokoId);
        if ($startPeriod > $activePeriod) {
            return ['tipe' => 'error', 'data' => 'Periode closing ulang tidak boleh lebih besar dari periode aktif'];
        }

        $period = $startPeriod;
        $results = [];
        while ($period < $activePeriod) {
            $results[] = $this->closePeriod($tokoId, $period, $createdBy, 'RECLOSE', false);
            $period = date('Y-m-01', strtotime($period . ' +1 month'));
        }

        $this->rebuildOpeningFromPreviousSnapshot($tokoId, $activePeriod);
            $this->calculateStockForPeriod($tokoId, date('Ym', strtotime($activePeriod)));
        $this->writeLog($tokoId, $startPeriod, 'RECLOSE', 'SUCCESS', 'Closing ulang selesai sampai periode aktif ' . $activePeriod, $results, $createdBy);

        return ['tipe' => 'success', 'data' => 'Closing ulang berhasil dijalankan dari ' . $startPeriod . ' sampai ' . $activePeriod];
    }

    public function getLogs(string $tokoId, int $limit = 10): array
    {
        return $this->db->query(
            "SELECT *
             FROM closing_log
             WHERE toko_id=:toko_id:
             ORDER BY created_at DESC, closing_id DESC
             LIMIT " . (int) $limit,
            ['toko_id' => $tokoId]
        )->getResultArray();
    }

    private function closePeriod(string $tokoId, string $periode, string $createdBy, string $mode, bool $advanceConst): array
    {
        $periode = $this->monthStart($periode);
        $nextPeriod = date('Y-m-01', strtotime($periode . ' +1 month'));
        $periodYm = date('Ym', strtotime($periode));

        try {
            $this->db->transStart();
            $this->rebuildOpeningFromPreviousSnapshot($tokoId, $periode);
            $this->calculateStockForPeriod($tokoId, $periodYm);
            $snapshotTable = $this->snapshotTable($periodYm, $tokoId);
            $this->replaceStockSnapshot($snapshotTable, $tokoId);
            $cashFlow = $this->replaceSaldoCash($tokoId, $periode, $createdBy);
            $this->prepareNextOpening($tokoId, $snapshotTable);

            if ($advanceConst) {
                $this->setClosingDate($tokoId, $nextPeriod);
                $this->calculateStockForPeriod($tokoId, date('Ym', strtotime($nextPeriod)));
            }

            $this->db->transComplete();

            if (!$this->db->transStatus()) {
                throw new \RuntimeException('Transaksi database closing gagal');
            }

            $message = 'Closing periode ' . $periode . ' berhasil';
            $this->writeLog($tokoId, $periode, $mode, 'SUCCESS', $message, ['cash_flow' => $cashFlow], $createdBy);
            return ['tipe' => 'success', 'data' => $message, 'next_period' => $nextPeriod];
        } catch (Throwable $e) {
            if ($this->db->transDepth() > 0) {
                $this->db->transRollback();
            }
            $this->writeLog($tokoId, $periode, $mode, 'ERROR', $e->getMessage(), [], $createdBy);
            return ['tipe' => 'error', 'data' => $e->getMessage()];
        }
    }

    private function rebuildOpeningFromPreviousSnapshot(string $tokoId, string $periode): void
    {
        $previousYm = date('Ym', strtotime($periode . ' -1 month'));
        $previousTable = $this->snapshotTable($previousYm, $tokoId);

        $this->db->query("DELETE FROM stmast WHERE toko_id=:toko_id:", ['toko_id' => $tokoId]);
        $this->insertStoreStockRows($tokoId);

        if ($this->tableExists($previousTable)) {
            $this->db->query(
                "UPDATE stmast a
                 INNER JOIN `{$previousTable}` b ON b.toko_id=a.toko_id AND b.kode_item=a.kode_item
                 SET a.begbal=b.qty, a.acost=b.acost
                 WHERE a.toko_id=:toko_id:",
                ['toko_id' => $tokoId]
            );
        }
    }

    private function prepareNextOpening(string $tokoId, string $snapshotTable): void
    {
        $this->db->query("DELETE FROM stmast WHERE toko_id=:toko_id:", ['toko_id' => $tokoId]);
        $this->insertStoreStockRows($tokoId);
        $this->db->query(
            "UPDATE stmast a
             INNER JOIN `{$snapshotTable}` b ON b.toko_id=a.toko_id AND b.kode_item=a.kode_item
             SET a.begbal=b.qty, a.acost=b.acost
             WHERE a.toko_id=:toko_id:",
            ['toko_id' => $tokoId]
        );
        $this->db->query("UPDATE stmast SET qty=begbal, beli=0, retur_beli=0, jual=0, retur_jual=0, adj=0, rp_saldo_akh=begbal*acost WHERE toko_id=:toko_id:", ['toko_id' => $tokoId]);
    }

    private function insertStoreStockRows(string $tokoId): void
    {
        $this->db->query(
            "INSERT IGNORE INTO stmast(toko_id, kode_item)
             SELECT :toko_id:, kode_item
             FROM prodmast",
            ['toko_id' => $tokoId]
        );
    }

    private function replaceStockSnapshot(string $tableName, string $tokoId): void
    {
        $this->db->query("CREATE TABLE IF NOT EXISTS `{$tableName}` LIKE stmast");
        $this->db->query("DELETE FROM `{$tableName}` WHERE toko_id=:toko_id:", ['toko_id' => $tokoId]);
        $this->db->query("INSERT INTO `{$tableName}` SELECT * FROM stmast WHERE toko_id=:toko_id:", ['toko_id' => $tokoId]);
    }

    private function calculateStockForPeriod(string $tokoId, string $periodYm): void
    {
        $periodYm = preg_replace('/[^0-9]/', '', $periodYm);
        if (strlen($periodYm) !== 6) {
            throw new \InvalidArgumentException('Periode hitung stock tidak valid');
        }

        $this->insertStoreStockRows($tokoId);
        $this->db->query(
            "UPDATE stmast
             SET beli=0, retur_beli=0, jual=0, retur_jual=0, adj=0
             WHERE toko_id=:toko_id:",
            ['toko_id' => $tokoId]
        );

        $this->db->query(
            "UPDATE stmast a
             LEFT JOIN (
                SELECT pd.toko_id, pd.kode_item, SUM(pd.qty_stock) AS jml
                FROM pembelian_detail pd
                INNER JOIN pembelian p ON p.toko_id=pd.toko_id AND p.beli_id=pd.beli_id
                WHERE pd.toko_id=:toko_id:
                  AND p.status_nota='TERIMA'
                  AND EXTRACT(YEAR_MONTH FROM p.tanggal)={$periodYm}
                GROUP BY pd.toko_id, pd.kode_item
             ) b USING(toko_id, kode_item)
             SET a.beli=IFNULL(b.jml,0)
             WHERE a.toko_id=:toko_id:",
            ['toko_id' => $tokoId]
        );

        $this->db->query(
            "UPDATE stmast a
             LEFT JOIN (
                SELECT rd.toko_id, rd.kode_item, SUM(rd.qty_stok) AS jml
                FROM pembelian_retur_detail rd
                INNER JOIN pembelian_retur r ON r.toko_id=rd.toko_id AND r.retur_id=rd.retur_id
                WHERE rd.toko_id=:toko_id:
                  AND r.status_retur='SELESAI'
                  AND EXTRACT(YEAR_MONTH FROM r.tanggal)={$periodYm}
                GROUP BY rd.toko_id, rd.kode_item
             ) b USING(toko_id, kode_item)
             SET a.retur_beli=IFNULL(b.jml,0)
             WHERE a.toko_id=:toko_id:",
            ['toko_id' => $tokoId]
        );

        $this->db->query(
            "UPDATE stmast a
             LEFT JOIN (
                SELECT d.toko_id, d.kode_item, SUM(d.qty_stock) AS jml
                FROM penjualan_detail d
                INNER JOIN penjualan j ON j.toko_id=d.toko_id AND j.jual_id=d.jual_id
                WHERE d.toko_id=:toko_id:
                  AND EXTRACT(YEAR_MONTH FROM j.tgl)={$periodYm}
                GROUP BY d.toko_id, d.kode_item
             ) b USING(toko_id, kode_item)
             SET a.jual=IFNULL(b.jml,0)
             WHERE a.toko_id=:toko_id:",
            ['toko_id' => $tokoId]
        );

        $this->db->query(
            "UPDATE stmast a
             LEFT JOIN (
                SELECT d.toko_id, d.kode_item, SUM(d.qty_stock) AS jml
                FROM retur_jual_detail d
                INNER JOIN retur_jual h ON h.toko_id=d.toko_id AND h.rj_id=d.rj_id
                WHERE d.toko_id=:toko_id:
                  AND EXTRACT(YEAR_MONTH FROM h.tanggal)={$periodYm}
                GROUP BY d.toko_id, d.kode_item
             ) b USING(toko_id, kode_item)
             SET a.retur_jual=IFNULL(b.jml,0)
             WHERE a.toko_id=:toko_id:",
            ['toko_id' => $tokoId]
        );

        $this->db->query(
            "UPDATE stmast a
             LEFT JOIN (
                SELECT toko_id, kode_item, SUM(qty_stock) AS jml
                FROM `adjust`
                WHERE toko_id=:toko_id:
                  AND EXTRACT(YEAR_MONTH FROM tanggal)={$periodYm}
                GROUP BY toko_id, kode_item
             ) b USING(toko_id, kode_item)
             SET a.adj=IFNULL(b.jml,0)
             WHERE a.toko_id=:toko_id:",
            ['toko_id' => $tokoId]
        );

        $this->db->query(
            "UPDATE stmast
             SET qty=begbal+beli-retur_beli-jual+retur_jual+adj
             WHERE toko_id=:toko_id:",
            ['toko_id' => $tokoId]
        );

        $this->db->query(
            "UPDATE stmast a
             INNER JOIN (
                SELECT ps.toko_id, ps.kode_item, ps.harga_pokok
                FROM prodmast_store ps
                INNER JOIN prodmast_satuan satuan
                    ON satuan.kode_item=ps.kode_item
                   AND satuan.sat_id=ps.sat_id
                   AND satuan.qty_konversi=1
                WHERE ps.toko_id=:toko_id:
             ) b ON b.toko_id=a.toko_id AND b.kode_item=a.kode_item
             SET a.acost=b.harga_pokok
             WHERE a.toko_id=:toko_id:",
            ['toko_id' => $tokoId]
        );

        $this->db->query(
            "UPDATE stmast
             SET rp_saldo_akh=qty*acost
             WHERE toko_id=:toko_id:",
            ['toko_id' => $tokoId]
        );
    }

    private function replaceSaldoCash(string $tokoId, string $periode, string $createdBy): array
    {
        $flow = $this->buildCashFlow($tokoId, $periode, $createdBy);

        $this->db->table('saldo_cash')->replace($flow);
        return $flow;
    }

    private function buildCashFlow(string $tokoId, string $periode, string $createdBy): array
    {
        $periode = $this->monthStart($periode);
        $start = $periode . ' 00:00:00';
        $end = date('Y-m-t 23:59:59', strtotime($periode));
        $prev = $this->getPreviousSaldoCash($tokoId, $periode);

        $pos = $this->sumSalePayments($tokoId, $start, $end, true);
        $piutang = $this->sumSalePayments($tokoId, $start, $end, false);
        $supplier = $this->sumSupplierPayments($tokoId, $start, $end);
        $kas = $this->sumKasMutasi($tokoId, $start, $end);

        $saldoTunai = (float) ($prev['saldo_tunai'] ?? 0) + $pos['TUNAI'] + $piutang['TUNAI'] + $kas['MASUK'] - $supplier['TUNAI'] - $kas['KELUAR'];
        $saldoTransfer = (float) ($prev['saldo_transfer'] ?? 0) + $pos['TRANSFER'] + $piutang['TRANSFER'] - $supplier['TRANSFER'];
        $saldoQris = (float) ($prev['saldo_qris'] ?? 0) + $pos['QRIS'] + $piutang['QRIS'];

        return [
            'toko_id' => $tokoId,
            'tahun' => (int) date('Y', strtotime($periode)),
            'bulan' => (int) date('m', strtotime($periode)),
            'periode' => $periode,
            'saldo_awal_tunai' => (float) ($prev['saldo_tunai'] ?? 0),
            'saldo_awal_transfer' => (float) ($prev['saldo_transfer'] ?? 0),
            'saldo_awal_qris' => (float) ($prev['saldo_qris'] ?? 0),
            'pos_tunai' => $pos['TUNAI'],
            'pos_transfer' => $pos['TRANSFER'],
            'pos_qris' => $pos['QRIS'],
            'bayar_piutang_tunai' => $piutang['TUNAI'],
            'bayar_piutang_transfer' => $piutang['TRANSFER'],
            'bayar_piutang_qris' => $piutang['QRIS'],
            'bayar_hutang_tunai' => $supplier['TUNAI'],
            'bayar_hutang_transfer' => $supplier['TRANSFER'],
            'kas_masuk' => $kas['MASUK'],
            'kas_keluar' => $kas['KELUAR'],
            'saldo_tunai' => $saldoTunai,
            'saldo_transfer' => $saldoTransfer,
            'saldo_qris' => $saldoQris,
            'saldo_all' => $saldoTunai + $saldoTransfer + $saldoQris,
            'closed_at' => date('Y-m-d H:i:s'),
            'closed_by' => $createdBy,
        ];
    }

    private function sumSalePayments(string $tokoId, string $start, string $end, bool $samePeriodSale): array
    {
        $periodStart = substr($start, 0, 10);
        $operator = $samePeriodSale ? '>=' : '<';
        $rows = $this->db->query(
            "SELECT pp.cara_bayar, COALESCE(SUM(pp.nominal_bayar),0) AS total
             FROM penjualan_pembayaran pp
             INNER JOIN penjualan j ON j.toko_id=pp.toko_id AND j.jual_id=pp.jual_id
             WHERE pp.toko_id=:toko_id:
               AND pp.tgl_bayar BETWEEN :start: AND :end:
               AND DATE(j.tgl) {$operator} :period_start:
             GROUP BY pp.cara_bayar",
            ['toko_id' => $tokoId, 'start' => $start, 'end' => $end, 'period_start' => $periodStart]
        )->getResultArray();

        return $this->paymentMap($rows);
    }

    private function sumSupplierPayments(string $tokoId, string $start, string $end): array
    {
        $rows = $this->db->query(
            "SELECT cara_bayar, COALESCE(SUM(jumlah_bayar),0) AS total
             FROM pembelian_pembayaran
             WHERE toko_id=:toko_id:
               AND tanggal_bayar BETWEEN :start: AND :end:
             GROUP BY cara_bayar",
            ['toko_id' => $tokoId, 'start' => $start, 'end' => $end]
        )->getResultArray();

        return $this->paymentMap($rows);
    }

    private function paymentMap(array $rows): array
    {
        $map = ['TUNAI' => 0.0, 'TRANSFER' => 0.0, 'QRIS' => 0.0];
        foreach ($rows as $row) {
            $method = strtoupper((string) ($row['cara_bayar'] ?? ''));
            if (isset($map[$method])) {
                $map[$method] += (float) ($row['total'] ?? 0);
            }
        }
        return $map;
    }

    private function sumKasMutasi(string $tokoId, string $start, string $end): array
    {
        $rows = $this->db->query(
            "SELECT ak.jenis_akun, COALESCE(SUM(km.nominal),0) AS total
             FROM kas_mutasi km
             INNER JOIN akun_kas ak ON ak.nama_akun=km.nama_akun
             WHERE km.toko_id=:toko_id:
               AND km.tanggal BETWEEN :start: AND :end:
             GROUP BY ak.jenis_akun",
            ['toko_id' => $tokoId, 'start' => $start, 'end' => $end]
        )->getResultArray();

        $map = ['MASUK' => 0.0, 'KELUAR' => 0.0];
        foreach ($rows as $row) {
            $type = strtoupper((string) ($row['jenis_akun'] ?? ''));
            if (isset($map[$type])) {
                $map[$type] = (float) ($row['total'] ?? 0);
            }
        }

        return $map;
    }

    private function getPreviousSaldoCash(string $tokoId, string $periode): array
    {
        $prev = date('Y-m-01', strtotime($periode . ' -1 month'));
        return $this->getSaldoCash($tokoId, $prev) ?: [];
    }

    private function getSaldoCash(string $tokoId, string $periode): ?array
    {
        return $this->db->query(
            "SELECT *
             FROM saldo_cash
             WHERE toko_id=:toko_id: AND tahun=:tahun: AND bulan=:bulan:
             LIMIT 1",
            [
                'toko_id' => $tokoId,
                'tahun' => (int) date('Y', strtotime($periode)),
                'bulan' => (int) date('m', strtotime($periode)),
            ]
        )->getRowArray() ?: null;
    }

    private function getStockSummary(string $tokoId): array
    {
        return $this->db->query(
            "SELECT COUNT(*) AS total_item,
                    COALESCE(SUM(begbal),0) AS awal_qty,
                    COALESCE(SUM(beli),0) AS beli_qty,
                    COALESCE(SUM(retur_beli),0) AS retur_beli_qty,
                    COALESCE(SUM(jual),0) AS jual_qty,
                    COALESCE(SUM(retur_jual),0) AS retur_jual_qty,
                    COALESCE(SUM(adj),0) AS adj_qty,
                    COALESCE(SUM(qty),0) AS akhir_qty,
                    COALESCE(SUM(begbal*acost),0) AS awal_rp,
                    COALESCE(SUM(rp_saldo_akh),0) AS akhir_rp
             FROM stmast
             WHERE toko_id=:toko_id:",
            ['toko_id' => $tokoId]
        )->getRowArray() ?: [];
    }

    private function ensureClosingDate(string $tokoId): string
    {
        $row = $this->db->query(
            "SELECT nilai FROM const WHERE rkey='closing' AND toko_id=:toko_id: LIMIT 1",
            ['toko_id' => $tokoId]
        )->getRowArray();

        if ($row && !empty($row['nilai'])) {
            return $this->monthStart((string) $row['nilai']);
        }

        $periode = date('Y-m-01');
        $this->db->table('const')->insert([
            'rkey' => 'closing',
            'toko_id' => $tokoId,
            'nilai' => $periode,
        ]);

        return $periode;
    }

    private function setClosingDate(string $tokoId, string $periode): void
    {
        $exists = $this->db->query(
            "SELECT rkey FROM const WHERE rkey='closing' AND toko_id=:toko_id: LIMIT 1",
            ['toko_id' => $tokoId]
        )->getRowArray();

        if ($exists) {
            $this->db->table('const')
                ->where('rkey', 'closing')
                ->where('toko_id', $tokoId)
                ->update(['nilai' => $this->monthStart($periode)]);
            return;
        }

        $this->db->table('const')->insert([
            'rkey' => 'closing',
            'toko_id' => $tokoId,
            'nilai' => $this->monthStart($periode),
        ]);
    }

    private function writeLog(string $tokoId, string $periode, string $mode, string $status, string $message, array $payload, string $createdBy): void
    {
        if (!$this->db->tableExists('closing_log')) {
            return;
        }

        $this->db->table('closing_log')->insert([
            'toko_id' => $tokoId,
            'periode' => $this->monthStart($periode),
            'mode' => $mode,
            'status' => $status,
            'message' => mb_substr($message, 0, 255),
            'payload_json' => json_encode($payload, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES),
            'created_by' => $createdBy,
            'created_at' => date('Y-m-d H:i:s'),
        ]);
    }

    private function snapshotTable(string $periodYm, string $tokoId): string
    {
        return 'stmast_' . preg_replace('/[^0-9]/', '', $periodYm) . preg_replace('/[^A-Za-z0-9_]/', '', $tokoId);
    }

    private function tableExists(string $tableName): bool
    {
        $row = $this->db->query(
            "SELECT COUNT(*) AS total
             FROM information_schema.tables
             WHERE table_schema=DATABASE() AND table_name=:table_name:",
            ['table_name' => $tableName]
        )->getRowArray();

        return (int) ($row['total'] ?? 0) > 0;
    }

    private function monthStart(string $date): string
    {
        $timestamp = strtotime($date);
        return date('Y-m-01', $timestamp ?: time());
    }
}
