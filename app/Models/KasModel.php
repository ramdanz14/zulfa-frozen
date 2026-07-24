<?php

namespace App\Models;

use CodeIgniter\Model;

class KasModel extends Model
{
    protected $table = 'kas_mutasi';
    protected $primaryKey = 'kas_id';
    protected $returnType = 'array';
    protected $protectFields = false;

    public function ajax(array $params, string $tokoId): array
    {
        $start = (int) ($params['start'] ?? 0);
        $length = $params['length'] ?? 25;
        $search = trim((string) ($params['search_value'] ?? ''));
        $queryLimit = $length !== '-1' ? " LIMIT $start, " . (int) $length : '';
        $binds = ['toko_id' => $tokoId];
        $where = " WHERE km.toko_id=:toko_id: ";
        if ($search !== '') {
            $where .= " AND (km.nama_akun LIKE :search: OR km.tipe_mutasi LIKE :search: OR km.karyawan_id LIKE :search: OR u.fullname LIKE :search: OR km.keterangan LIKE :search:) ";
            $binds['search'] = '%' . $this->db->escapeLikeString($search) . '%';
        }

        $total = (int) ($this->db->query("SELECT COUNT(*) AS total FROM kas_mutasi WHERE toko_id=:toko_id:", ['toko_id' => $tokoId])->getRowArray()['total'] ?? 0);
        $filtered = $total;
        if ($search !== '') {
            $filtered = (int) ($this->db->query(
                "SELECT COUNT(*) AS total
                 FROM kas_mutasi km
                 LEFT JOIN akun_kas ak ON ak.nama_akun=km.nama_akun
                 LEFT JOIN tb_user u ON u.karyawan_id=km.karyawan_id
                 $where",
                $binds
            )->getRowArray()['total'] ?? 0);
        }

        $data = $this->db->query(
            "SELECT km.*, ak.jenis_akun, u.fullname
             FROM kas_mutasi km
             LEFT JOIN akun_kas ak ON ak.nama_akun=km.nama_akun
             LEFT JOIN tb_user u ON u.karyawan_id=km.karyawan_id
             $where
             ORDER BY km.tanggal DESC, km.kas_id DESC
             $queryLimit",
            $binds
        )->getResultArray();

        $today = date('Y-m-d');
        foreach ($data as &$row) {
            $row['can_mutate'] = substr((string) ($row['tanggal'] ?? ''), 0, 10) === $today;
        }
        unset($row);

        return [
            'data' => $data,
            'total_count' => $total,
            'total_filtered' => $filtered,
        ];
    }

    /**
     * Get real-time cash balances (saldo_toko, saldo_pemilik)
     * Calculates from latest saldo_cash + kas_mutasi movements up to $untilDate (default today)
     */
    public function getCashBalances(string $tokoId, ?string $untilDate = null): array
    {
        $untilDate = $untilDate ?? date('Y-m-d');
        $endDateTime = $untilDate . ' 23:59:59';

        // Get latest saldo_cash record (monthly snapshot)
        $saldoCash = $this->db->query(
            "SELECT *
             FROM saldo_cash
             WHERE toko_id=:toko_id:
             ORDER BY tahun DESC, bulan DESC
             LIMIT 1",
            ['toko_id' => $tokoId]
        )->getRowArray() ?: [
            'saldo_toko' => 0,
            'saldo_pemilik' => 0,
            'saldo_tunai' => 0,
            'saldo_transfer' => 0,
            'saldo_qris' => 0,
        ];

        // Calculate movements from kas_mutasi after the snapshot period
        // We use the snapshot's period end as baseline
        $snapshotPeriod = sprintf('%04d-%02d-01', (int)($saldoCash['tahun'] ?? date('Y')), (int)($saldoCash['bulan'] ?? date('m')));
        $snapshotEnd = date('Y-m-t 23:59:59', strtotime($snapshotPeriod));

        $movements = $this->db->query(
            "SELECT
                COALESCE(SUM(CASE WHEN COALESCE(saldo_channel,'CASH')='CASH' AND COALESCE(saldo_target,'TOKO')='TOKO' AND ak.jenis_akun='MASUK' THEN nominal ELSE 0 END),0) AS toko_in,
                COALESCE(SUM(CASE WHEN COALESCE(saldo_channel,'CASH')='CASH' AND COALESCE(saldo_target,'TOKO')='TOKO' AND ak.jenis_akun='KELUAR' THEN nominal ELSE 0 END),0) AS toko_out,
                COALESCE(SUM(CASE WHEN COALESCE(saldo_channel,'CASH')='CASH' AND COALESCE(saldo_target,'TOKO')='PEMILIK' AND ak.jenis_akun='MASUK' THEN nominal ELSE 0 END),0) AS pemilik_in,
                COALESCE(SUM(CASE WHEN COALESCE(saldo_channel,'CASH')='CASH' AND COALESCE(saldo_target,'TOKO')='PEMILIK' AND ak.jenis_akun='KELUAR' THEN nominal ELSE 0 END),0) AS pemilik_out,
                COALESCE(SUM(CASE WHEN tipe_mutasi='PINDAH_SALDO' AND saldo_asal='CASH' AND saldo_target='TOKO' THEN nominal ELSE 0 END),0) AS pindah_toko_out,
                COALESCE(SUM(CASE WHEN tipe_mutasi='PINDAH_SALDO' AND saldo_tujuan='CASH' AND saldo_target='TOKO' THEN nominal ELSE 0 END),0) AS pindah_toko_in,
                COALESCE(SUM(CASE WHEN tipe_mutasi='PINDAH_SALDO' AND saldo_asal='CASH' AND saldo_target='PEMILIK' THEN nominal ELSE 0 END),0) AS pindah_pemilik_out,
                COALESCE(SUM(CASE WHEN tipe_mutasi='PINDAH_SALDO' AND saldo_tujuan='CASH' AND saldo_target='PEMILIK' THEN nominal ELSE 0 END),0) AS pindah_pemilik_in
             FROM kas_mutasi km
             LEFT JOIN akun_kas ak ON ak.nama_akun=km.nama_akun
             WHERE km.toko_id=:toko_id:
               AND km.tanggal > :snapshot_end:
               AND km.tanggal <= :until_date:",
            [
                'toko_id' => $tokoId,
                'snapshot_end' => $snapshotEnd,
                'until_date' => $endDateTime,
            ]
        )->getRowArray() ?: [];

        $m = array_merge([
            'toko_in' => 0, 'toko_out' => 0,
            'pemilik_in' => 0, 'pemilik_out' => 0,
            'pindah_toko_out' => 0, 'pindah_toko_in' => 0,
            'pindah_pemilik_out' => 0, 'pindah_pemilik_in' => 0,
        ], $movements);

        $saldoToko = (float)($saldoCash['saldo_toko'] ?? 0) + $m['toko_in'] - $m['toko_out'] - $m['pindah_toko_out'] + $m['pindah_toko_in'];
        $saldoPemilik = (float)($saldoCash['saldo_pemilik'] ?? 0) + $m['pemilik_in'] - $m['pemilik_out'] - $m['pindah_pemilik_out'] + $m['pindah_pemilik_in'];

        // Handle deposit (TOKO -> PEMILIK) and withdrawal (PEMILIK -> TOKO) in PINDAH_SALDO
        // Deposit: asal=CASH, target=TOKO -> out from toko; tujuan=CASH, target=PEMILIK -> in to pemilik
        // This is already captured in pindah_toko_out and pindah_pemilik_in

        return [
            'saldo_toko' => round($saldoToko, 2),
            'saldo_pemilik' => round($saldoPemilik, 2),
            'total' => round($saldoToko + $saldoPemilik, 2),
            'snapshot_period' => $snapshotPeriod,
        ];
    }

    public function saveMutation(string $tokoId, string $username, array $payload, string $mode): array
    {
        $tanggal = trim((string) ($payload['tanggal'] ?? ''));
        $tipeMutasi = strtoupper(trim((string) ($payload['tipe_mutasi'] ?? 'OPERASIONAL')));
        $namaAkun = strtoupper(trim((string) ($payload['nama_akun'] ?? '')));
        $saldoChannel = strtoupper(trim((string) ($payload['saldo_channel'] ?? 'CASH')));
        $saldoAsal = strtoupper(trim((string) ($payload['saldo_asal'] ?? '')));
        $saldoTujuan = strtoupper(trim((string) ($payload['saldo_tujuan'] ?? '')));
        $saldoTarget = strtoupper(trim((string) ($payload['saldo_target'] ?? 'TOKO')));
        $saldoTargetAsal = strtoupper(trim((string) ($payload['saldo_target_asal'] ?? '')));
        $saldoTargetTujuan = strtoupper(trim((string) ($payload['saldo_target_tujuan'] ?? '')));
        $nominal = (int) preg_replace('/[^0-9\-]/', '', (string) ($payload['nominal'] ?? 0));
        $karyawanId = trim((string) ($payload['karyawan_id'] ?? ''));
        $keterangan = trim((string) ($payload['keterangan'] ?? ''));
        $kasId = (int) ($payload['kas_id'] ?? 0);

        if ($tanggal === '' || substr($tanggal, 0, 10) !== date('Y-m-d')) {
            return ['tipe' => 'error', 'data' => 'Transaksi kas hanya boleh diinput pada tanggal hari ini'];
        }
        if (!in_array($tipeMutasi, ['OPERASIONAL', 'PINDAH_SALDO'], true)) {
            return ['tipe' => 'error', 'data' => 'Tipe mutasi tidak valid'];
        }
        if (!in_array($saldoChannel, ['CASH', 'NONCASH'], true)) {
            $saldoChannel = 'CASH';
        }
        if (!in_array($saldoTarget, ['TOKO', 'PEMILIK'], true)) {
            $saldoTarget = 'TOKO';
        }
        if ($nominal <= 0 || $karyawanId === '') {
            return ['tipe' => 'error', 'data' => 'Tanggal, nominal, dan karyawan wajib diisi'];
        }

        // Validation for OPERASIONAL
        if ($tipeMutasi === 'OPERASIONAL') {
            if ($namaAkun === '') {
                return ['tipe' => 'error', 'data' => 'Akun kas wajib diisi untuk mutasi operasional'];
            }
            $akun = $this->db->query("SELECT * FROM akun_kas WHERE nama_akun=:nama_akun: LIMIT 1", ['nama_akun' => $namaAkun])->getRowArray();
            if (!$akun) {
                return ['tipe' => 'error', 'data' => 'Akun kas tidak ditemukan'];
            }
            $saldoAsal = '';
            $saldoTujuan = '';
            $saldoTargetAsal = '';
            $saldoTargetTujuan = '';

            // Check balance availability for CASH KELUAR
            if ($saldoChannel === 'CASH' && $akun['jenis_akun'] === 'KELUAR') {
                $balances = $this->getCashBalances($tokoId);
                $available = $saldoTarget === 'TOKO' ? $balances['saldo_toko'] : $balances['saldo_pemilik'];
                if ($available + 0.0001 < $nominal) {
                    return ['tipe' => 'error', 'data' => 'Saldo ' . $saldoTarget . ' tidak mencukupi (tersedia: ' . number_format($available, 0, ',', '.') . ')'];
                }
            }
        } else {
            // PINDAH_SALDO validation
            if (!in_array($saldoAsal, ['CASH', 'NONCASH'], true) || !in_array($saldoTujuan, ['CASH', 'NONCASH'], true) || $saldoAsal === $saldoTujuan) {
                return ['tipe' => 'error', 'data' => 'Arah mutasi saldo (channel) wajib valid'];
            }
            if (!in_array($saldoTargetAsal, ['TOKO', 'PEMILIK'], true) || !in_array($saldoTargetTujuan, ['TOKO', 'PEMILIK'], true) || $saldoTargetAsal === $saldoTargetTujuan) {
                return ['tipe' => 'error', 'data' => 'Arah mutasi saldo (target) wajib valid'];
            }
            $namaAkun = '';
            $saldoChannel = 'CASH'; // PINDAH_SALDO only for CASH

            // Check balance availability for source
            $balances = $this->getCashBalances($tokoId);
            $available = $saldoTargetAsal === 'TOKO' ? $balances['saldo_toko'] : $balances['saldo_pemilik'];
            if ($available + 0.0001 < $nominal) {
                return ['tipe' => 'error', 'data' => 'Saldo ' . $saldoTargetAsal . ' tidak mencukupi untuk pindah saldo (tersedia: ' . number_format($available, 0, ',', '.') . ')'];
            }
        }

        $karyawan = $this->db->query("SELECT karyawan_id FROM tb_user WHERE karyawan_id=:karyawan_id: LIMIT 1", ['karyawan_id' => $karyawanId])->getRowArray();
        if (!$karyawan) {
            return ['tipe' => 'error', 'data' => 'Karyawan tidak ditemukan'];
        }

        if ($mode === 'create') {
            $this->db->transStart();

            if ($tipeMutasi === 'OPERASIONAL') {
                $this->insert([
                    'tanggal' => $tanggal,
                    'toko_id' => $tokoId,
                    'nama_akun' => $namaAkun !== '' ? $namaAkun : null,
                    'tipe_mutasi' => $tipeMutasi,
                    'saldo_target' => $saldoTarget,
                    'saldo_channel' => $saldoChannel,
                    'saldo_asal' => null,
                    'saldo_tujuan' => null,
                    'nominal' => $nominal,
                    'karyawan_id' => $karyawanId,
                    'keterangan' => $keterangan !== '' ? $keterangan : null,
                    'updid' => $username,
                ]);
            } else {
                // PINDAH_SALDO creates two entries: OUT from asal+target_asal, IN to tujuan+target_tujuan
                // OUT entry
                $this->insert([
                    'tanggal' => $tanggal,
                    'toko_id' => $tokoId,
                    'nama_akun' => null,
                    'tipe_mutasi' => $tipeMutasi,
                    'saldo_target' => $saldoTargetAsal,
                    'saldo_channel' => $saldoAsal,
                    'saldo_asal' => $saldoAsal,
                    'saldo_tujuan' => null,
                    'nominal' => $nominal,
                    'karyawan_id' => $karyawanId,
                    'keterangan' => $keterangan !== '' ? $keterangan . ' (Keluar)' : 'Pindah Saldo Keluar',
                    'updid' => $username,
                ]);

                // IN entry
                $this->insert([
                    'tanggal' => $tanggal,
                    'toko_id' => $tokoId,
                    'nama_akun' => null,
                    'tipe_mutasi' => $tipeMutasi,
                    'saldo_target' => $saldoTargetTujuan,
                    'saldo_channel' => $saldoTujuan,
                    'saldo_asal' => null,
                    'saldo_tujuan' => $saldoTujuan,
                    'nominal' => $nominal,
                    'karyawan_id' => $karyawanId,
                    'keterangan' => $keterangan !== '' ? $keterangan . ' (Masuk)' : 'Pindah Saldo Masuk',
                    'updid' => $username,
                ]);
            }

            $this->db->transComplete();

            if (!$this->db->transStatus()) {
                return ['tipe' => 'error', 'data' => 'Gagal menyimpan mutasi kas'];
            }

            return ['tipe' => 'success', 'data' => 'Mutasi kas berhasil ditambahkan'];
        }

        // Update mode
        $existing = $this->find($kasId);
        if (!$existing || (string) ($existing['toko_id'] ?? '') !== $tokoId) {
            return ['tipe' => 'error', 'data' => 'Data mutasi kas tidak ditemukan'];
        }
        if (!$this->canMutateRow($existing)) {
            return ['tipe' => 'error', 'data' => 'Transaksi kas yang lewat hari sudah dikunci'];
        }

        // For update, we don't allow changing tipe_mutasi or saldo_target structure
        // Just update basic fields
        if ($tipeMutasi === 'OPERASIONAL') {
            $this->update($kasId, [
                'tanggal' => $tanggal,
                'nama_akun' => $namaAkun !== '' ? $namaAkun : null,
                'tipe_mutasi' => $tipeMutasi,
                'saldo_target' => $saldoTarget,
                'saldo_channel' => $saldoChannel,
                'saldo_asal' => null,
                'saldo_tujuan' => null,
                'nominal' => $nominal,
                'karyawan_id' => $karyawanId,
                'keterangan' => $keterangan !== '' ? $keterangan : null,
                'updid' => $username,
            ]);
        } else {
            // For PINDAH_SALDO updates, we'd need to update both entries
            // Simplified: just update the main entry (OUT)
            $this->update($kasId, [
                'tanggal' => $tanggal,
                'nama_akun' => null,
                'tipe_mutasi' => $tipeMutasi,
                'saldo_target' => $saldoTargetAsal,
                'saldo_channel' => $saldoAsal,
                'saldo_asal' => $saldoAsal,
                'saldo_tujuan' => null,
                'nominal' => $nominal,
                'karyawan_id' => $karyawanId,
                'keterangan' => $keterangan !== '' ? $keterangan . ' (Keluar)' : 'Pindah Saldo Keluar',
                'updid' => $username,
            ]);
            // Note: The corresponding IN entry should also be updated
            // Find and update the paired IN entry
            $pairedIn = $this->db->query(
                "SELECT kas_id FROM kas_mutasi 
                 WHERE toko_id=:toko_id: AND tipe_mutasi='PINDAH_SALDO' 
                 AND karyawan_id=:karyawan_id: AND nominal=:nominal: 
                 AND saldo_target=:target_tujuan: AND saldo_asal IS NULL AND saldo_tujuan IS NOT NULL
                 AND kas_id != :kas_id:
                 ORDER BY kas_id DESC LIMIT 1",
                [
                    'toko_id' => $tokoId,
                    'karyawan_id' => $karyawanId,
                    'nominal' => $nominal,
                    'target_tujuan' => $saldoTargetTujuan,
                    'kas_id' => $kasId,
                ]
            )->getRowArray();

            if ($pairedIn) {
                $this->update($pairedIn['kas_id'], [
                    'tanggal' => $tanggal,
                    'saldo_target' => $saldoTargetTujuan,
                    'saldo_channel' => $saldoTujuan,
                    'saldo_asal' => null,
                    'saldo_tujuan' => $saldoTujuan,
                    'nominal' => $nominal,
                    'keterangan' => $keterangan !== '' ? $keterangan . ' (Masuk)' : 'Pindah Saldo Masuk',
                    'updid' => $username,
                ]);
            }
        }

        return ['tipe' => 'success', 'data' => 'Mutasi kas berhasil diupdate'];
    }

    /**
     * Deposit cash from TOKO to PEMILIK (end of day setoran)
     */
    public function depositToOwner(string $tokoId, string $username, float $amount, string $keterangan = ''): array
    {
        if ($amount <= 0) {
            return ['tipe' => 'error', 'data' => 'Nominal setoran harus lebih dari 0'];
        }

        $balances = $this->getCashBalances($tokoId);
        if ($balances['saldo_toko'] + 0.0001 < $amount) {
            return ['tipe' => 'error', 'data' => 'Saldo toko tidak mencukupi untuk setoran (tersedia: ' . number_format($balances['saldo_toko'], 0, ',', '.') . ')'];
        }

        $tanggal = date('Y-m-d H:i:s');
        $note = $keterangan !== '' ? $keterangan : 'Setoran harian ke pemilik';

        $this->db->transStart();

        // OUT from TOKO
        $this->insert([
            'tanggal' => $tanggal,
            'toko_id' => $tokoId,
            'nama_akun' => null,
            'tipe_mutasi' => 'PINDAH_SALDO',
            'saldo_target' => 'TOKO',
            'saldo_channel' => 'CASH',
            'saldo_asal' => 'CASH',
            'saldo_tujuan' => null,
            'nominal' => round($amount, 2),
            'karyawan_id' => $username,
            'keterangan' => $note . ' (Keluar)',
            'updid' => $username,
        ]);

        // IN to PEMILIK
        $this->insert([
            'tanggal' => $tanggal,
            'toko_id' => $tokoId,
            'nama_akun' => null,
            'tipe_mutasi' => 'PINDAH_SALDO',
            'saldo_target' => 'PEMILIK',
            'saldo_channel' => 'CASH',
            'saldo_asal' => null,
            'saldo_tujuan' => 'CASH',
            'nominal' => round($amount, 2),
            'karyawan_id' => $username,
            'keterangan' => $note . ' (Masuk)',
            'updid' => $username,
        ]);

        $this->db->transComplete();

        if (!$this->db->transStatus()) {
            return ['tipe' => 'error', 'data' => 'Gagal memproses setoran ke pemilik'];
        }

        return ['tipe' => 'success', 'data' => 'Setoran ke pemilik berhasil'];
    }

    /**
     * Withdraw profit from TOKO or PEMILIK
     */
    public function withdrawProfit(string $tokoId, string $username, float $amount, string $sourceTarget, string $keterangan = ''): array
    {
        if ($amount <= 0) {
            return ['tipe' => 'error', 'data' => 'Nominal tarik keuntungan harus lebih dari 0'];
        }
        if (!in_array($sourceTarget, ['TOKO', 'PEMILIK'], true)) {
            return ['tipe' => 'error', 'data' => 'Sumber saldo tidak valid'];
        }

        $balances = $this->getCashBalances($tokoId);
        $available = $sourceTarget === 'TOKO' ? $balances['saldo_toko'] : $balances['saldo_pemilik'];
        if ($available + 0.0001 < $amount) {
            return ['tipe' => 'error', 'data' => 'Saldo ' . $sourceTarget . ' tidak mencukupi (tersedia: ' . number_format($available, 0, ',', '.') . ')'];
        }

        $tanggal = date('Y-m-d H:i:s');
        $note = $keterangan !== '' ? $keterangan : 'Tarik keuntungan dari ' . $sourceTarget;

        $this->insert([
            'tanggal' => $tanggal,
            'toko_id' => $tokoId,
            'nama_akun' => 'TARIK_KEUNTUNGAN',
            'tipe_mutasi' => 'OPERASIONAL',
            'saldo_target' => $sourceTarget,
            'saldo_channel' => 'CASH',
            'saldo_asal' => null,
            'saldo_tujuan' => null,
            'nominal' => round($amount, 2),
            'karyawan_id' => $username,
            'keterangan' => $note,
            'updid' => $username,
        ]);

        return ['tipe' => 'success', 'data' => 'Tarik keuntungan berhasil'];
    }

    public function deleteMutation(string $tokoId, int $kasId): array
    {
        $existing = $this->find($kasId);
        if (!$existing || (string) ($existing['toko_id'] ?? '') !== $tokoId) {
            return ['tipe' => 'error', 'data' => 'Data mutasi kas tidak ditemukan'];
        }
        if (!$this->canMutateRow($existing)) {
            return ['tipe' => 'error', 'data' => 'Transaksi kas yang lewat hari sudah dikunci'];
        }

        // If this is a PINDAH_SALDO OUT entry, also delete the paired IN entry
        if ($existing['tipe_mutasi'] === 'PINDAH_SALDO' && $existing['saldo_asal'] !== null) {
            $pairedIn = $this->db->query(
                "SELECT kas_id FROM kas_mutasi 
                 WHERE toko_id=:toko_id: AND tipe_mutasi='PINDAH_SALDO' 
                 AND karyawan_id=:karyawan_id: AND nominal=:nominal: 
                 AND saldo_target != :target_asal: AND saldo_asal IS NULL AND saldo_tujuan IS NOT NULL
                 AND kas_id != :kas_id:
                 ORDER BY kas_id DESC LIMIT 1",
                [
                    'toko_id' => $tokoId,
                    'karyawan_id' => $existing['karyawan_id'],
                    'nominal' => $existing['nominal'],
                    'target_asal' => $existing['saldo_target'],
                    'kas_id' => $kasId,
                ]
            )->getRowArray();

            if ($pairedIn) {
                $this->delete($pairedIn['kas_id']);
            }
        }

        $this->delete($kasId);
        return ['tipe' => 'success', 'data' => 'Mutasi kas berhasil dihapus'];
    }

    public function getAkunOptions(): array
    {
        return $this->db->query("SELECT nama_akun, jenis_akun, flag_beban FROM akun_kas ORDER BY jenis_akun, nama_akun")->getResultArray();
    }

    public function getKaryawanOptions(string $tokoId): array
    {
        return $this->db->query(
            "SELECT karyawan_id, fullname
             FROM tb_user
             WHERE active='Y' AND toko_id=:toko_id:
             ORDER BY fullname, karyawan_id",
            ['toko_id' => $tokoId]
        )->getResultArray();
    }

    public function getSummary(string $tokoId, array $params, bool $canSeeAllStores): array
    {
        $dateStart = trim((string) ($params['date_start'] ?? date('Y-m-01')));
        $dateEnd = trim((string) ($params['date_end'] ?? date('Y-m-d')));
        $selectedStores = $params['toko_ids'] ?? [];
        $selectedStores = is_array($selectedStores) ? array_values(array_filter(array_map('trim', $selectedStores))) : [];

        [$storeWhere, $binds, $effectiveStores] = $this->buildStoreScope($tokoId, $selectedStores, $canSeeAllStores);
        $binds['date_start'] = $dateStart . ' 00:00:00';
        $binds['date_end'] = $dateEnd . ' 23:59:59';
        $where = " WHERE km.tanggal BETWEEN :date_start: AND :date_end: $storeWhere ";

        $summary = $this->db->query(
            "SELECT
                COALESCE(SUM(CASE WHEN ak.jenis_akun='MASUK' THEN km.nominal ELSE 0 END),0) AS total_masuk,
                COALESCE(SUM(CASE WHEN ak.jenis_akun='KELUAR' THEN km.nominal ELSE 0 END),0) AS total_keluar,
                COALESCE(SUM(CASE WHEN ak.jenis_akun='MASUK' AND COALESCE(km.saldo_channel,'CASH')='CASH' AND COALESCE(km.saldo_target,'TOKO')='TOKO' THEN km.nominal ELSE 0 END),0) AS toko_tunai_masuk,
                COALESCE(SUM(CASE WHEN ak.jenis_akun='KELUAR' AND COALESCE(km.saldo_channel,'CASH')='CASH' AND COALESCE(km.saldo_target,'TOKO')='TOKO' THEN km.nominal ELSE 0 END),0) AS toko_tunai_keluar,
                COALESCE(SUM(CASE WHEN ak.jenis_akun='MASUK' AND COALESCE(km.saldo_channel,'CASH')='CASH' AND COALESCE(km.saldo_target,'TOKO')='PEMILIK' THEN km.nominal ELSE 0 END),0) AS pemilik_tunai_masuk,
                COALESCE(SUM(CASE WHEN ak.jenis_akun='KELUAR' AND COALESCE(km.saldo_channel,'CASH')='CASH' AND COALESCE(km.saldo_target,'TOKO')='PEMILIK' THEN km.nominal ELSE 0 END),0) AS pemilik_tunai_keluar,
                COALESCE(SUM(CASE WHEN ak.jenis_akun='MASUK' AND COALESCE(km.saldo_channel,'CASH')='NONCASH' THEN km.nominal ELSE 0 END),0) AS total_nontunai_masuk,
                COALESCE(SUM(CASE WHEN ak.jenis_akun='KELUAR' AND COALESCE(km.saldo_channel,'CASH')='NONCASH' THEN km.nominal ELSE 0 END),0) AS total_nontunai_keluar,
                COUNT(*) AS total_transaksi
             FROM kas_mutasi km
             INNER JOIN akun_kas ak ON ak.nama_akun=km.nama_akun
             $where",
            $binds
        )->getRowArray() ?: [];

        $tableRows = $this->db->query(
            "SELECT DATE(km.tanggal) AS tanggal,
                    ak.jenis_akun,
                    km.nama_akun,
                    km.saldo_target,
                    COALESCE(km.saldo_channel,'CASH') AS saldo_channel,
                    km.toko_id,
                    MAX(t.toko_nama) AS toko_nama,
                    COUNT(*) AS total_transaksi,
                    SUM(km.nominal) AS total_nominal,
                    COALESCE(SUM(CASE WHEN COALESCE(km.saldo_channel,'CASH')='CASH' AND COALESCE(km.saldo_target,'TOKO')='TOKO' THEN km.nominal ELSE 0 END),0) AS toko_tunai,
                    COALESCE(SUM(CASE WHEN COALESCE(km.saldo_channel,'CASH')='CASH' AND COALESCE(km.saldo_target,'TOKO')='PEMILIK' THEN km.nominal ELSE 0 END),0) AS pemilik_tunai,
                    COALESCE(SUM(CASE WHEN COALESCE(km.saldo_channel,'CASH')='NONCASH' THEN km.nominal ELSE 0 END),0) AS total_nontunai
             FROM kas_mutasi km
             INNER JOIN akun_kas ak ON ak.nama_akun=km.nama_akun
             LEFT JOIN toko t ON t.toko_id=km.toko_id
             $where
             GROUP BY DATE(km.tanggal), ak.jenis_akun, km.nama_akun, km.saldo_target, COALESCE(km.saldo_channel,'CASH'), km.toko_id
             ORDER BY DATE(km.tanggal) DESC, ak.jenis_akun, km.nama_akun, km.toko_id",
            $binds
        )->getResultArray();

        $chartRows = $this->db->query(
            "SELECT km.toko_id, km.nama_akun, km.saldo_target, ak.jenis_akun, SUM(km.nominal) AS total_nominal
             FROM kas_mutasi km
             INNER JOIN akun_kas ak ON ak.nama_akun=km.nama_akun
             $where
             GROUP BY km.toko_id, km.nama_akun, km.saldo_target, ak.jenis_akun
             ORDER BY ak.jenis_akun, km.nama_akun, km.toko_id",
            $binds
        )->getResultArray();

        return [
            'summary' => [
                'total_masuk' => (int) ($summary['total_masuk'] ?? 0),
                'total_keluar' => (int) ($summary['total_keluar'] ?? 0),
                'toko_tunai_masuk' => (int) ($summary['toko_tunai_masuk'] ?? 0),
                'toko_tunai_keluar' => (int) ($summary['toko_tunai_keluar'] ?? 0),
                'pemilik_tunai_masuk' => (int) ($summary['pemilik_tunai_masuk'] ?? 0),
                'pemilik_tunai_keluar' => (int) ($summary['pemilik_tunai_keluar'] ?? 0),
                'total_nontunai_masuk' => (int) ($summary['total_nontunai_masuk'] ?? 0),
                'total_nontunai_keluar' => (int) ($summary['total_nontunai_keluar'] ?? 0),
                'saldo_bersih' => (int) (($summary['total_masuk'] ?? 0) - ($summary['total_keluar'] ?? 0)),
                'total_transaksi' => (int) ($summary['total_transaksi'] ?? 0),
                'stores' => $effectiveStores,
            ],
            'rows' => $tableRows,
            'chart_rows' => $chartRows,
        ];
    }

    /**
     * Get daily cash summary for EOD report (Laporan Harian)
     */
    public function getDailyCashSummary(string $tokoId, string $tanggal): array
    {
        $start = $tanggal . ' 00:00:00';
        $end = $tanggal . ' 23:59:59';

        $movements = $this->db->query(
            "SELECT
                COALESCE(SUM(CASE WHEN COALESCE(saldo_channel,'CASH')='CASH' AND COALESCE(saldo_target,'TOKO')='TOKO' AND ak.jenis_akun='MASUK' THEN nominal ELSE 0 END),0) AS toko_tunai_masuk,
                COALESCE(SUM(CASE WHEN COALESCE(saldo_channel,'CASH')='CASH' AND COALESCE(saldo_target,'TOKO')='TOKO' AND ak.jenis_akun='KELUAR' THEN nominal ELSE 0 END),0) AS toko_tunai_keluar,
                COALESCE(SUM(CASE WHEN COALESCE(saldo_channel,'CASH')='CASH' AND COALESCE(saldo_target,'TOKO')='PEMILIK' AND ak.jenis_akun='MASUK' THEN nominal ELSE 0 END),0) AS pemilik_tunai_masuk,
                COALESCE(SUM(CASE WHEN COALESCE(saldo_channel,'CASH')='CASH' AND COALESCE(saldo_target,'TOKO')='PEMILIK' AND ak.jenis_akun='KELUAR' THEN nominal ELSE 0 END),0) AS pemilik_tunai_keluar,
                COALESCE(SUM(CASE WHEN tipe_mutasi='PINDAH_SALDO' AND saldo_asal='CASH' AND saldo_target='TOKO' THEN nominal ELSE 0 END),0) AS deposit_toko_ke_pemilik,
                COALESCE(SUM(CASE WHEN tipe_mutasi='PINDAH_SALDO' AND saldo_tujuan='CASH' AND saldo_target='TOKO' THEN nominal ELSE 0 END),0) AS tarik_pemilik_ke_toko,
                COALESCE(SUM(CASE WHEN tipe_mutasi='OPERASIONAL' AND nama_akun='TARIK_KEUNTUNGAN' AND COALESCE(saldo_target,'TOKO')='TOKO' THEN nominal ELSE 0 END),0) AS tarik_keuntungan_toko,
                COALESCE(SUM(CASE WHEN tipe_mutasi='OPERASIONAL' AND nama_akun='TARIK_KEUNTUNGAN' AND COALESCE(saldo_target,'TOKO')='PEMILIK' THEN nominal ELSE 0 END),0) AS tarik_keuntungan_pemilik,
                COALESCE(SUM(CASE WHEN COALESCE(saldo_channel,'CASH')='NONCASH' AND ak.jenis_akun='MASUK' THEN nominal ELSE 0 END),0) AS nontunai_masuk,
                COALESCE(SUM(CASE WHEN COALESCE(saldo_channel,'CASH')='NONCASH' AND ak.jenis_akun='KELUAR' THEN nominal ELSE 0 END),0) AS nontunai_keluar
             FROM kas_mutasi km
             LEFT JOIN akun_kas ak ON ak.nama_akun=km.nama_akun
             WHERE km.toko_id=:toko_id:
               AND km.tanggal BETWEEN :start: AND :end:",
            ['toko_id' => $tokoId, 'start' => $start, 'end' => $end]
        )->getRowArray() ?: [];

        $m = array_merge([
            'toko_tunai_masuk' => 0, 'toko_tunai_keluar' => 0,
            'pemilik_tunai_masuk' => 0, 'pemilik_tunai_keluar' => 0,
            'deposit_toko_ke_pemilik' => 0, 'tarik_pemilik_ke_toko' => 0,
            'tarik_keuntungan_toko' => 0, 'tarik_keuntungan_pemilik' => 0,
            'nontunai_masuk' => 0, 'nontunai_keluar' => 0,
        ], $movements);

        return [
            'toko_tunai_masuk' => (float)$m['toko_tunai_masuk'],
            'toko_tunai_keluar' => (float)$m['toko_tunai_keluar'],
            'pemilik_tunai_masuk' => (float)$m['pemilik_tunai_masuk'],
            'pemilik_tunai_keluar' => (float)$m['pemilik_tunai_keluar'],
            'deposit_toko_ke_pemilik' => (float)$m['deposit_toko_ke_pemilik'],
            'tarik_pemilik_ke_toko' => (float)$m['tarik_pemilik_ke_toko'],
            'tarik_keuntungan_toko' => (float)$m['tarik_keuntungan_toko'],
            'tarik_keuntungan_pemilik' => (float)$m['tarik_keuntungan_pemilik'],
            'nontunai_masuk' => (float)$m['nontunai_masuk'],
            'nontunai_keluar' => (float)$m['nontunai_keluar'],
        ];
    }

    private function canMutateRow(array $row): bool
    {
        return substr((string) ($row['tanggal'] ?? ''), 0, 10) === date('Y-m-d');
    }

    private function buildStoreScope(string $sessionTokoId, array $selectedStores, bool $canSeeAllStores): array
    {
        if (!$canSeeAllStores) {
            return [' AND km.toko_id=:session_toko_id: ', ['session_toko_id' => $sessionTokoId], [$sessionTokoId]];
        }

        if (empty($selectedStores)) {
            return ['', [], []];
        }

        $placeholders = [];
        $binds = [];
        foreach ($selectedStores as $idx => $storeId) {
            $key = 'store_' . $idx;
            $placeholders[] = ':' . $key . ':';
            $binds[$key] = $storeId;
        }

        return [' AND km.toko_id IN (' . implode(',', $placeholders) . ') ', $binds, $selectedStores];
    }
}