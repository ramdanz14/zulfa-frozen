<?php

namespace App\Models;

use CodeIgniter\Model;

class PoinMemberModel extends Model
{
    protected $table = 'history_poin_member';
    protected $returnType = 'array';
    protected $protectFields = false;

    public function getCustomerOptions(): array
    {
        return $this->db->query(
            "SELECT cust_id, nama, poin
             FROM customer
             ORDER BY nama, cust_id"
        )->getResultArray();
    }

    public function getCurrentNominalPerPoin(): int
    {
        $row = $this->db->query(
            "SELECT nilai FROM const WHERE rkey='nominal_per_poin' LIMIT 1"
        )->getRowArray();

        return max(1, (int) ($row['nilai'] ?? 1000));
    }

    public function ajaxList(array $params): array
    {
        $start = (int) ($params['start'] ?? 0);
        $length = $params['length'] ?? 25;
        $search = trim((string) ($params['search_value'] ?? ''));
        $custId = trim((string) ($params['cust_id'] ?? ''));
        $dateStart = trim((string) ($params['date_start'] ?? ''));
        $dateEnd = trim((string) ($params['date_end'] ?? ''));
        $queryLimit = $length !== '-1' ? " LIMIT $start, " . (int) $length : '';

        $binds = [];
        $where = " WHERE 1=1 ";

        if ($custId !== '') {
            $where .= " AND h.cust_id=:cust_id: ";
            $binds['cust_id'] = $custId;
        }
        if ($dateStart !== '') {
            $where .= " AND DATE(h.tanggal) >= :date_start: ";
            $binds['date_start'] = $dateStart;
        }
        if ($dateEnd !== '') {
            $where .= " AND DATE(h.tanggal) <= :date_end: ";
            $binds['date_end'] = $dateEnd;
        }

        $baseSql = "
            FROM history_poin_member h
            LEFT JOIN customer c ON c.cust_id=h.cust_id
            $where
        ";

        $searchSql = '';
        if ($search !== '') {
            $searchSql = " AND (
                h.cust_id LIKE :search:
                OR c.nama LIKE :search:
                OR COALESCE(h.trx_id,'') LIKE :search:
                OR COALESCE(h.keterangan,'') LIKE :search:
                OR h.jenis LIKE :search:
            )";
            $binds['search'] = '%' . $this->db->escapeLikeString($search) . '%';
        }

        $totalRow = $this->db->query(
            "SELECT COUNT(*) total $baseSql",
            $binds
        )->getRowArray();

        $filtered = $totalRow['total'] ?? 0;
        if ($search !== '') {
            $filteredRow = $this->db->query(
                "SELECT COUNT(*) total $baseSql $searchSql",
                $binds
            )->getRowArray();
            $filtered = $filteredRow['total'] ?? 0;
        }

        $data = $this->db->query(
            "SELECT h.*, c.nama AS customer_nama, c.poin AS saldo_poin_terkini
             $baseSql
             $searchSql
             ORDER BY h.tanggal DESC, h.history_id DESC
             $queryLimit",
            $binds
        )->getResultArray();

        return [
            'data' => $data,
            'total_count' => (int) ($totalRow['total'] ?? 0),
            'total_filtered' => (int) $filtered,
        ];
    }

    public function saveNominalPerPoin(int $nominal): bool
    {
        return (bool) $this->db->query(
            "REPLACE INTO const SET rkey='nominal_per_poin', nilai=:nilai:",
            ['nilai' => (string) $nominal]
        );
    }

    public function hardResetAllPoints(string $tokoId, string $username): array
    {
        $customers = $this->db->query(
            "SELECT cust_id, poin
             FROM customer
             WHERE poin > 0"
        )->getResultArray();

        if (empty($customers)) {
            return ['tipe' => 'error', 'data' => 'Tidak ada saldo poin customer yang perlu di-reset'];
        }

        $timestamp = date('Y-m-d H:i:s');
        $trxId = 'RESET-' . date('YmdHis');
        $nominalPerPoin = $this->getCurrentNominalPerPoin();
        $this->db->transStart();

        foreach ($customers as $row) {
            $before = (int) ($row['poin'] ?? 0);

            $this->db->table('history_poin_member')->insert([
                'toko_id' => $tokoId,
                'cust_id' => $row['cust_id'],
                'trx_id' => $trxId,
                'tanggal' => $timestamp,
                'jenis' => 'reset',
                'nominal_transaksi' => 0,
                'nominal_per_poin' => $nominalPerPoin,
                'poin_masuk' => 0,
                'poin_keluar' => $before,
                'poin_before' => $before,
                'poin_after' => 0,
                'keterangan' => 'Hard reset poin semua member',
                'updid' => $username,
            ]);
        }

        $this->db->table('customer')->where('poin >', 0)->update([
            'poin' => 0,
            'updid' => $username,
            'updtime' => $timestamp,
        ]);

        $this->db->transComplete();

        if (! $this->db->transStatus()) {
            return ['tipe' => 'error', 'data' => 'Gagal melakukan hard reset poin member'];
        }

        return ['tipe' => 'success', 'data' => 'Hard reset berhasil. Semua saldo poin customer menjadi nol'];
    }

    public function addPointsFromSale(string $tokoId, string $custId, string $trxId, float $nominalBelanja, ?string $tanggal = null, string $username = 'system'): int
    {
        $nominalPerPoin = $this->getCurrentNominalPerPoin();
        $points = $nominalPerPoin > 0 ? (int) floor($nominalBelanja / $nominalPerPoin) : 0;
        if ($points <= 0) {
            return 0;
        }

        $this->applyPointMutation(
            $tokoId,
            $custId,
            $trxId,
            'tambah',
            $points,
            $nominalBelanja,
            $nominalPerPoin,
            $tanggal,
            $username,
            'Poin dari transaksi penjualan'
        );

        return $points;
    }

    public function deductPointsFromRedeem(string $tokoId, string $custId, string $trxId, int $pointsUsed, float $nominalDiskon = 0, ?string $tanggal = null, string $username = 'system'): int
    {
        $pointsUsed = max(0, $pointsUsed);
        if ($pointsUsed <= 0) {
            return 0;
        }

        $this->applyPointMutation(
            $tokoId,
            $custId,
            $trxId,
            'kurang',
            $pointsUsed,
            $nominalDiskon,
            $this->getCurrentNominalPerPoin(),
            $tanggal,
            $username,
            'Penukaran poin menjadi diskon belanja'
        );

        return $pointsUsed;
    }

    private function applyPointMutation(
        string $tokoId,
        string $custId,
        string $trxId,
        string $jenis,
        int $points,
        float $nominalTransaksi,
        int $nominalPerPoin,
        ?string $tanggal,
        string $username,
        string $keterangan
    ): void {
        $customer = $this->db->query(
            "SELECT poin FROM customer WHERE cust_id=:cust_id: LIMIT 1",
            ['cust_id' => $custId]
        )->getRowArray();

        if (! $customer) {
            return;
        }

        $before = (int) ($customer['poin'] ?? 0);
        $after = $jenis === 'tambah'
            ? $before + $points
            : max(0, $before - $points);
        $effectiveOut = $jenis === 'kurang' ? $before - $after : 0;

        $this->db->transStart();

        $this->db->table('customer')
            ->where('cust_id', $custId)
            ->update([
                'poin' => $after,
                'updid' => $username,
                'updtime' => $tanggal ?: date('Y-m-d H:i:s'),
            ]);

        $this->db->table('history_poin_member')->insert([
            'toko_id' => $tokoId,
            'cust_id' => $custId,
            'trx_id' => $trxId,
            'tanggal' => $tanggal ?: date('Y-m-d H:i:s'),
            'jenis' => $jenis,
            'nominal_transaksi' => $nominalTransaksi,
            'nominal_per_poin' => $nominalPerPoin,
            'poin_masuk' => $jenis === 'tambah' ? $points : 0,
            'poin_keluar' => $jenis === 'kurang' ? $effectiveOut : 0,
            'poin_before' => $before,
            'poin_after' => $after,
            'keterangan' => $keterangan,
            'updid' => $username,
        ]);

        $this->db->transComplete();
    }
}
