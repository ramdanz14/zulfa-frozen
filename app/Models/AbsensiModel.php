<?php

namespace App\Models;

use CodeIgniter\Model;

class AbsensiModel extends Model
{
    protected $table = 'absensi_karyawan';
    protected $primaryKey = 'absensi_id';
    protected $returnType = 'array';
    protected $protectFields = false;

    public function getTokoOptions(): array
    {
        return $this->db->query(
            "SELECT toko_id, toko_nama
             FROM toko
             ORDER BY toko_id"
        )->getResultArray();
    }

    public function getFormData(string $tanggal): array
    {
        $userModel = new UserModel();
        $users = $userModel->getAbsensiUsers();
        $existingRows = $this->db->query(
            "SELECT *
             FROM absensi_karyawan
             WHERE tanggal=:tanggal:",
            ['tanggal' => $tanggal]
        )->getResultArray();

        $existingMap = [];
        foreach ($existingRows as $row) {
            $existingMap[(string) ($row['karyawan_id'] ?? '')] = $row;
        }

        $rows = [];
        foreach ($users as $user) {
            $existing = $existingMap[$user['karyawan_id']] ?? null;
            $rows[] = [
                'absensi_id' => $existing['absensi_id'] ?? null,
                'karyawan_id' => $user['karyawan_id'],
                'fullname' => $user['fullname'],
                'home_toko_id' => $user['toko_id'],
                'toko_id' => $existing['toko_id'] ?? $user['toko_id'],
                'status_absensi' => $existing['status_absensi'] ?? '',
                'nominal_gaji' => $existing['nominal_gaji'] ?? $user['gaji'],
                'nominal_gaji_default' => $user['gaji'],
                'keterangan' => $existing['keterangan'] ?? '',
                'is_paid' => $existing['is_paid'] ?? 'N',
                'batch_id' => $existing['batch_id'] ?? null,
            ];
        }

        return [
            'tanggal' => $tanggal,
            'rows' => $rows,
            'tokoOptions' => $this->getTokoOptions(),
        ];
    }

    public function saveEntries(string $tanggal, string $username, array $rows): array
    {
        if ($tanggal === '') {
            return ['tipe' => 'error', 'data' => 'Tanggal absensi wajib diisi'];
        }

        $userModel = new UserModel();
        $allowedUsers = [];
        foreach ($userModel->getAbsensiUsers() as $user) {
            $allowedUsers[(string) $user['karyawan_id']] = $user;
        }

        $existingRows = $this->db->query(
            "SELECT *
             FROM absensi_karyawan
             WHERE tanggal=:tanggal:",
            ['tanggal' => $tanggal]
        )->getResultArray();
        $existingMap = [];
        foreach ($existingRows as $row) {
            $existingMap[(string) ($row['karyawan_id'] ?? '')] = $row;
        }

        $hasAnyInput = false;
        $this->db->transStart();

        foreach ($rows as $row) {
            $karyawanId = trim((string) ($row['karyawan_id'] ?? ''));
            $status = strtoupper(trim((string) ($row['status_absensi'] ?? '')));
            $tokoId = trim((string) ($row['toko_id'] ?? ''));
            $keterangan = trim((string) ($row['keterangan'] ?? ''));
            $nominal = round((float) ($row['nominal_gaji'] ?? 0), 2);

            if ($karyawanId === '' || !isset($allowedUsers[$karyawanId])) {
                continue;
            }

            $existing = $existingMap[$karyawanId] ?? null;
            if (($existing['is_paid'] ?? 'N') === 'Y') {
                continue;
            }

            if ($status === '') {
                if ($existing) {
                    $this->db->table('absensi_karyawan')->where('absensi_id', $existing['absensi_id'])->delete();
                }
                continue;
            }

            if (!in_array($status, ['HADIR', 'MANGKIR', 'LIBUR'], true)) {
                $this->db->transRollback();
                return ['tipe' => 'error', 'data' => 'Status absensi tidak valid untuk ' . $karyawanId];
            }
            if ($tokoId === '') {
                $this->db->transRollback();
                return ['tipe' => 'error', 'data' => 'Lokasi kerja wajib dipilih untuk ' . $karyawanId];
            }

            $hasAnyInput = true;
            $payload = [
                'tanggal' => $tanggal,
                'karyawan_id' => $karyawanId,
                'toko_id' => $tokoId,
                'status_absensi' => $status,
                'nominal_gaji' => $nominal,
                'keterangan' => $keterangan !== '' ? $keterangan : null,
                'is_paid' => 'N',
                'batch_id' => null,
                'paid_at' => null,
                'updid' => $username,
                'updtime' => date('Y-m-d H:i:s'),
            ];

            if ($existing) {
                $this->db->table('absensi_karyawan')
                    ->where('absensi_id', $existing['absensi_id'])
                    ->update($payload);
            } else {
                $this->db->table('absensi_karyawan')->insert($payload);
            }
        }

        $this->db->transComplete();

        if (!$this->db->transStatus()) {
            return ['tipe' => 'error', 'data' => 'Gagal menyimpan absensi'];
        }
        if (!$hasAnyInput) {
            return ['tipe' => 'error', 'data' => 'Pilih minimal satu status absensi'];
        }

        return ['tipe' => 'success', 'data' => 'Absensi berhasil disimpan'];
    }

    public function ajaxSummary(array $params): array
    {
        $start = (int) ($params['start'] ?? 0);
        $length = $params['length'] ?? 25;
        $search = trim((string) ($params['search_value'] ?? ''));
        $queryLimit = $length !== '-1' ? " LIMIT $start, " . (int) $length : '';
        $binds = [];
        $where = " WHERE 1=1 ";
        if ($search !== '') {
            $where .= " AND (a.tanggal LIKE :search: OR u.fullname LIKE :search: OR a.karyawan_id LIKE :search:) ";
            $binds['search'] = '%' . $this->db->escapeLikeString($search) . '%';
        }

        $countRow = $this->db->query(
            "SELECT COUNT(*) AS total
             FROM (
                SELECT a.tanggal
                FROM absensi_karyawan a
                LEFT JOIN tb_user u ON u.karyawan_id=a.karyawan_id
                $where
                GROUP BY a.tanggal
             ) x",
            $binds
        )->getRowArray();

        $data = $this->db->query(
            "SELECT a.tanggal,
                    COUNT(*) AS total_row,
                    SUM(CASE WHEN a.status_absensi='HADIR' THEN 1 ELSE 0 END) AS total_hadir,
                    SUM(CASE WHEN a.is_paid='Y' THEN 1 ELSE 0 END) AS total_paid,
                    SUM(CASE WHEN a.is_paid='N' THEN 1 ELSE 0 END) AS total_unpaid,
                    COALESCE(SUM(a.nominal_gaji), 0) AS total_gaji
             FROM absensi_karyawan a
             LEFT JOIN tb_user u ON u.karyawan_id=a.karyawan_id
             $where
             GROUP BY a.tanggal
             ORDER BY a.tanggal DESC
             $queryLimit",
            $binds
        )->getResultArray();

        foreach ($data as &$row) {
            $row['can_delete'] = (int) ($row['total_paid'] ?? 0) === 0;
        }
        unset($row);

        return [
            'data' => $data,
            'total_count' => (int) ($countRow['total'] ?? 0),
            'total_filtered' => (int) ($countRow['total'] ?? 0),
        ];
    }

    public function getDateSummary(string $tanggal): ?array
    {
        $header = $this->db->query(
            "SELECT a.tanggal,
                    COUNT(*) AS total_row,
                    SUM(CASE WHEN a.status_absensi='HADIR' THEN 1 ELSE 0 END) AS total_hadir,
                    SUM(CASE WHEN a.is_paid='Y' THEN 1 ELSE 0 END) AS total_paid,
                    SUM(CASE WHEN a.is_paid='N' THEN 1 ELSE 0 END) AS total_unpaid,
                    COALESCE(SUM(a.nominal_gaji), 0) AS total_gaji
             FROM absensi_karyawan a
             WHERE a.tanggal=:tanggal:
             GROUP BY a.tanggal",
            ['tanggal' => $tanggal]
        )->getRowArray();

        if (!$header) {
            return null;
        }

        $header['details'] = $this->db->query(
            "SELECT a.*, u.fullname, home.toko_nama AS home_toko_nama, kerja.toko_nama AS kerja_toko_nama
             FROM absensi_karyawan a
             INNER JOIN tb_user u ON u.karyawan_id=a.karyawan_id
             LEFT JOIN toko home ON home.toko_id=u.toko_id
             LEFT JOIN toko kerja ON kerja.toko_id=a.toko_id
             WHERE a.tanggal=:tanggal:
             ORDER BY u.fullname, a.karyawan_id",
            ['tanggal' => $tanggal]
        )->getResultArray();

        return $header;
    }

    public function deleteDate(string $tanggal): array
    {
        $paidCount = (int) ($this->db->query(
            "SELECT COUNT(*) AS total
             FROM absensi_karyawan
             WHERE tanggal=:tanggal: AND is_paid='Y'",
            ['tanggal' => $tanggal]
        )->getRowArray()['total'] ?? 0);

        if ($paidCount > 0) {
            return ['tipe' => 'error', 'data' => 'Absensi yang sudah dibayar tidak boleh dihapus karena sudah mempengaruhi kas'];
        }

        $this->db->table('absensi_karyawan')->where('tanggal', $tanggal)->delete();
        return ['tipe' => 'success', 'data' => 'Absensi harian berhasil dihapus'];
    }

    public function getPaymentCandidates(string $startDate, string $endDate): array
    {
        return [
            'period_start' => $startDate,
            'period_end' => $endDate,
            'rows' => $this->db->query(
                "SELECT a.absensi_id, a.tanggal, a.karyawan_id, u.fullname, a.toko_id, t.toko_nama,
                        a.status_absensi, a.nominal_gaji, a.keterangan
                 FROM absensi_karyawan a
                 INNER JOIN tb_user u ON u.karyawan_id=a.karyawan_id
                 LEFT JOIN toko t ON t.toko_id=a.toko_id
                 WHERE a.tanggal BETWEEN :start_date: AND :end_date:
                   AND a.is_paid='N'
                   AND a.nominal_gaji > 0
                 ORDER BY a.tanggal ASC, u.fullname ASC, a.karyawan_id ASC",
                ['start_date' => $startDate, 'end_date' => $endDate]
            )->getResultArray(),
        ];
    }

    public function createPaymentBatch(string $username, string $tanggalBayar, string $periodStart, string $periodEnd, array $selectedIds, string $saldoChannel = 'CASH'): array
    {
        $selectedIds = array_values(array_unique(array_map('intval', $selectedIds)));
        $saldoChannel = strtoupper(trim($saldoChannel));
        if (!in_array($saldoChannel, ['CASH', 'NONCASH'], true)) {
            $saldoChannel = 'CASH';
        }
        if ($tanggalBayar === '' || $periodStart === '' || $periodEnd === '') {
            return ['tipe' => 'error', 'data' => 'Tanggal bayar dan periode wajib diisi'];
        }
        if (empty($selectedIds)) {
            return ['tipe' => 'error', 'data' => 'Pilih minimal satu absensi untuk dibayar'];
        }

        $rows = $this->db->table('absensi_karyawan')
            ->whereIn('absensi_id', $selectedIds)
            ->where('is_paid', 'N')
            ->get()
            ->getResultArray();

        if (count($rows) !== count($selectedIds)) {
            return ['tipe' => 'error', 'data' => 'Sebagian data absensi sudah tidak valid atau sudah dibayar'];
        }

        $this->ensureAkunGaji();
        $batchId = $this->getNextBatchId();
        $grouped = [];
        $totalNominal = 0;

        foreach ($rows as $row) {
            $groupKey = $row['toko_id'] . '|' . $row['karyawan_id'];
            if (!isset($grouped[$groupKey])) {
                $grouped[$groupKey] = [
                    'toko_id' => $row['toko_id'],
                    'karyawan_id' => $row['karyawan_id'],
                    'nominal' => 0,
                    'tanggal_list' => [],
                ];
            }
            $grouped[$groupKey]['nominal'] += (float) ($row['nominal_gaji'] ?? 0);
            $grouped[$groupKey]['tanggal_list'][] = (string) ($row['tanggal'] ?? '');
            $totalNominal += (float) ($row['nominal_gaji'] ?? 0);
        }

        $this->db->transStart();
        $this->db->table('absensi_pembayaran')->insert([
            'batch_id' => $batchId,
            'tanggal_bayar' => $tanggalBayar,
            'periode_start' => $periodStart,
            'periode_end' => $periodEnd,
            'total_nominal' => round($totalNominal, 2),
            'total_karyawan' => count($grouped),
            'updid' => $username,
            'created_at' => date('Y-m-d H:i:s'),
        ]);

        $kasMap = [];
        foreach ($grouped as $group) {
            $uniqueDates = array_values(array_unique(array_filter($group['tanggal_list'])));
            sort($uniqueDates);
            $keterangan = 'GAJI ' . implode(', ', $uniqueDates);
            $this->db->table('kas_mutasi')->insert([
                'tanggal' => $tanggalBayar . ' 00:00:00',
                'toko_id' => $group['toko_id'],
                'nama_akun' => 'GAJI',
                'tipe_mutasi' => 'OPERASIONAL',
                'saldo_channel' => $saldoChannel,
                'saldo_asal' => null,
                'saldo_tujuan' => null,
                'nominal' => (int) round($group['nominal'], 0),
                'karyawan_id' => $group['karyawan_id'],
                'keterangan' => substr($keterangan, 0, 150),
                'updid' => $username,
            ]);
            $kasMap[$group['toko_id'] . '|' . $group['karyawan_id']] = $this->db->insertID();
        }

        foreach ($rows as $row) {
            $groupKey = $row['toko_id'] . '|' . $row['karyawan_id'];
            $kasId = $kasMap[$groupKey] ?? null;
            $this->db->table('absensi_karyawan')
                ->where('absensi_id', $row['absensi_id'])
                ->update([
                    'is_paid' => 'Y',
                    'batch_id' => $batchId,
                    'paid_at' => $tanggalBayar . ' 00:00:00',
                    'updid' => $username,
                    'updtime' => date('Y-m-d H:i:s'),
                ]);
            $this->db->table('absensi_pembayaran_detail')->insert([
                'batch_id' => $batchId,
                'absensi_id' => $row['absensi_id'],
                'kas_id' => $kasId,
            ]);
        }

        $this->db->transComplete();

        if (!$this->db->transStatus()) {
            return ['tipe' => 'error', 'data' => 'Gagal memproses pembayaran gaji'];
        }

        return [
            'tipe' => 'success',
            'data' => 'Pembayaran gaji berhasil diproses',
            'batch_id' => $batchId,
        ];
    }

    public function ajaxPaymentHistory(array $params): array
    {
        $start = (int) ($params['start'] ?? 0);
        $length = $params['length'] ?? 25;
        $search = trim((string) ($params['search_value'] ?? ''));
        $queryLimit = $length !== '-1' ? " LIMIT $start, " . (int) $length : '';
        $binds = [];
        $where = " WHERE 1=1 ";
        if ($search !== '') {
            $where .= " AND (p.batch_id LIKE :search: OR p.tanggal_bayar LIKE :search:) ";
            $binds['search'] = '%' . $this->db->escapeLikeString($search) . '%';
        }

        $count = $this->db->query(
            "SELECT COUNT(*) AS total
             FROM absensi_pembayaran p
             $where",
            $binds
        )->getRowArray();

        $data = $this->db->query(
            "SELECT p.*
             FROM absensi_pembayaran p
             $where
             ORDER BY p.tanggal_bayar DESC, p.batch_id DESC
             $queryLimit",
            $binds
        )->getResultArray();

        return [
            'data' => $data,
            'total_count' => (int) ($count['total'] ?? 0),
            'total_filtered' => (int) ($count['total'] ?? 0),
        ];
    }

    public function getPaymentDetail(string $batchId): ?array
    {
        $header = $this->db->query(
            "SELECT *
             FROM absensi_pembayaran
             WHERE batch_id=:batch_id:
             LIMIT 1",
            ['batch_id' => $batchId]
        )->getRowArray();
        if (!$header) {
            return null;
        }

        $header['details'] = $this->db->query(
            "SELECT a.absensi_id, a.tanggal, a.karyawan_id, u.fullname, a.toko_id, t.toko_nama,
                    a.status_absensi, a.nominal_gaji, a.keterangan, d.kas_id
             FROM absensi_pembayaran_detail d
             INNER JOIN absensi_karyawan a ON a.absensi_id=d.absensi_id
             INNER JOIN tb_user u ON u.karyawan_id=a.karyawan_id
             LEFT JOIN toko t ON t.toko_id=a.toko_id
             WHERE d.batch_id=:batch_id:
             ORDER BY u.fullname, a.tanggal",
            ['batch_id' => $batchId]
        )->getResultArray();

        return $header;
    }

    public function getSlipData(string $batchId, string $karyawanId): ?array
    {
        $header = $this->db->query(
            "SELECT p.batch_id, p.tanggal_bayar, p.periode_start, p.periode_end,
                    u.karyawan_id, u.fullname, u.phone
             FROM absensi_pembayaran p
             INNER JOIN absensi_pembayaran_detail d ON d.batch_id=p.batch_id
             INNER JOIN absensi_karyawan a ON a.absensi_id=d.absensi_id
             INNER JOIN tb_user u ON u.karyawan_id=a.karyawan_id
             WHERE p.batch_id=:batch_id: AND u.karyawan_id=:karyawan_id:
             LIMIT 1",
            ['batch_id' => $batchId, 'karyawan_id' => $karyawanId]
        )->getRowArray();
        if (!$header) {
            return null;
        }

        $detailRows = $this->db->query(
            "SELECT a.tanggal, a.toko_id, t.toko_nama, a.status_absensi, a.nominal_gaji
             FROM absensi_pembayaran_detail d
             INNER JOIN absensi_karyawan a ON a.absensi_id=d.absensi_id
             LEFT JOIN toko t ON t.toko_id=a.toko_id
             WHERE d.batch_id=:batch_id: AND a.karyawan_id=:karyawan_id:
             ORDER BY a.tanggal ASC",
            ['batch_id' => $batchId, 'karyawan_id' => $karyawanId]
        )->getResultArray();

        $storeRows = [];
        $totalNominal = 0;
        foreach ($detailRows as $row) {
            $key = (string) ($row['toko_id'] ?? '');
            if (!isset($storeRows[$key])) {
                $storeRows[$key] = [
                    'toko_id' => $row['toko_id'],
                    'toko_nama' => $row['toko_nama'] ?? $row['toko_id'],
                    'nominal' => 0,
                ];
            }
            $storeRows[$key]['nominal'] += (float) ($row['nominal_gaji'] ?? 0);
            $totalNominal += (float) ($row['nominal_gaji'] ?? 0);
        }

        $header['details'] = $detailRows;
        $header['store_rows'] = array_values($storeRows);
        $header['total_nominal'] = $totalNominal;
        return $header;
    }

    private function getNextBatchId(): string
    {
        $prefix = 'GB' . date('ymd');
        $row = $this->db->query(
            "SELECT MAX(CAST(RIGHT(batch_id,4) AS UNSIGNED)) AS nomor
             FROM absensi_pembayaran
             WHERE batch_id LIKE :prefix_like:",
            ['prefix_like' => $prefix . '%']
        )->getRowArray();

        return $prefix . sprintf('%04d', ((int) ($row['nomor'] ?? 0)) + 1);
    }

    private function ensureAkunGaji(): void
    {
        $exists = $this->db->query(
            "SELECT nama_akun, flag_beban
             FROM akun_kas
             WHERE nama_akun='GAJI'
             LIMIT 1"
        )->getRowArray();

        if ($exists) {
            if (($exists['flag_beban'] ?? 'N') !== 'Y') {
                $this->db->table('akun_kas')
                    ->where('nama_akun', 'GAJI')
                    ->update(['jenis_akun' => 'KELUAR', 'flag_beban' => 'Y', 'updid' => 'SYSTEM']);
            }
            return;
        }

        $this->db->table('akun_kas')->insert([
            'nama_akun' => 'GAJI',
            'jenis_akun' => 'KELUAR',
            'flag_beban' => 'Y',
            'updid' => 'SYSTEM',
        ]);
    }
}
