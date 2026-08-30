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
            if (strtoupper((string) ($row['tipe_mutasi'] ?? '')) === 'PINDAH_SALDO') {
                $row['label_detail'] = $this->humanizeBucket(
                    (string) ($row['saldo_asal'] ?? ''),
                    (string) ($row['saldo_asal_target'] ?? '')
                ) . ' ke ' . $this->humanizeBucket(
                    (string) ($row['saldo_tujuan'] ?? ''),
                    (string) ($row['saldo_tujuan_target'] ?? '')
                );
            } else {
                $channel = strtoupper((string) ($row['saldo_channel'] ?? 'CASH')) === 'NONCASH' ? '' : ' · ' . strtolower((string) ($row['saldo_target'] ?? 'TOKO'));
                $row['label_detail'] = strtoupper((string) ($row['saldo_channel'] ?? 'CASH')) === 'NONCASH' ? 'Non Tunai' : 'Tunai' . $channel;
            }
        }
        unset($row);

        return [
            'data' => $data,
            'total_count' => $total,
            'total_filtered' => $filtered,
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
        $saldoTarget = strtoupper(trim((string) ($payload['saldo_target'] ?? '')));
        $saldoAsalTarget = strtoupper(trim((string) ($payload['saldo_asal_target'] ?? '')));
        $saldoTujuanTarget = strtoupper(trim((string) ($payload['saldo_tujuan_target'] ?? '')));
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
        if ($nominal <= 0 || $karyawanId === '') {
            return ['tipe' => 'error', 'data' => 'Tanggal, nominal, dan karyawan wajib diisi'];
        }
        if (!in_array($karyawanId, array_column($this->getKaryawanOptions($tokoId), 'karyawan_id'), true)) {
            $karyawan = $this->db->query("SELECT karyawan_id FROM tb_user WHERE karyawan_id=:karyawan_id: LIMIT 1", ['karyawan_id' => $karyawanId])->getRowArray();
            if (!$karyawan) {
                return ['tipe' => 'error', 'data' => 'Karyawan tidak ditemukan'];
            }
        }

        if ($tipeMutasi === 'OPERASIONAL') {
            if ($namaAkun === '') {
                return ['tipe' => 'error', 'data' => 'Akun kas wajib diisi untuk mutasi operasional'];
            }
            $akun = $this->db->query("SELECT * FROM akun_kas WHERE nama_akun=:nama_akun: LIMIT 1", ['nama_akun' => $namaAkun])->getRowArray();
            if (!$akun) {
                return ['tipe' => 'error', 'data' => 'Akun kas tidak ditemukan'];
            }

            $privilegedAkun = in_array($namaAkun, ['TAMBAH_MODAL_KAS', 'TARIK_KEUNTUNGAN'], true);
            if ($saldoChannel === 'CASH') {
                if ($saldoTarget === '') {
                    $saldoTarget = 'TOKO';
                }
                if (!in_array($saldoTarget, ['TOKO', 'PEMILIK'], true)) {
                    return ['tipe' => 'error', 'data' => 'Saldo target tidak valid'];
                }
            } else {
                $saldoTarget = '';
            }
            if (($privilegedAkun || $saldoTarget === 'PEMILIK') && !$this->canDeleteAkses('kas')) {
                return ['tipe' => 'error', 'data' => 'Akses mutasi ini hanya untuk akun dengan hak delete'];
            }

            $saldoAsal = '';
            $saldoTujuan = '';
            $saldoAsalTarget = '';
            $saldoTujuanTarget = '';
        } else {
            if (!$this->canDeleteAkses('kas')) {
                return ['tipe' => 'error', 'data' => 'Akses Mutasi Saldo hanya untuk akun dengan hak delete'];
            }
            if (!in_array($saldoAsal, ['CASH', 'NONCASH'], true) || !in_array($saldoTujuan, ['CASH', 'NONCASH'], true)) {
                return ['tipe' => 'error', 'data' => 'Arah mutasi saldo wajib valid'];
            }
            $saldoAsalTarget = $saldoAsal === 'CASH' ? ($saldoAsalTarget !== '' ? $saldoAsalTarget : 'TOKO') : '';
            $saldoTujuanTarget = $saldoTujuan === 'CASH' ? ($saldoTujuanTarget !== '' ? $saldoTujuanTarget : 'TOKO') : '';
            if ($saldoAsal === 'CASH' && !in_array($saldoAsalTarget, ['TOKO', 'PEMILIK'], true)) {
                return ['tipe' => 'error', 'data' => 'Saldo asal tidak valid'];
            }
            if ($saldoTujuan === 'CASH' && !in_array($saldoTujuanTarget, ['TOKO', 'PEMILIK'], true)) {
                return ['tipe' => 'error', 'data' => 'Saldo tujuan tidak valid'];
            }
            if ($saldoAsal === $saldoTujuan && $saldoAsalTarget === $saldoTujuanTarget) {
                return ['tipe' => 'error', 'data' => 'Saldo asal dan tujuan tidak boleh sama'];
            }

            $namaAkun = '';
            $saldoChannel = 'CASH';
            $saldoTarget = $saldoTujuan === 'CASH' ? $saldoTujuanTarget : $saldoAsalTarget;

            $sourceError = $this->validateSourceBalance($tokoId, $saldoAsal, $saldoAsalTarget, $nominal, $mode === 'edit' ? $kasId : 0);
            if ($sourceError !== null) {
                return $sourceError;
            }
        }

        $fields = [
            'tanggal' => $tanggal,
            'nama_akun' => $namaAkun !== '' ? $namaAkun : null,
            'tipe_mutasi' => $tipeMutasi,
            'saldo_channel' => $saldoChannel,
            'saldo_target' => $saldoTarget !== '' ? $saldoTarget : 'TOKO',
            'saldo_asal' => $saldoAsal !== '' ? $saldoAsal : null,
            'saldo_tujuan' => $saldoTujuan !== '' ? $saldoTujuan : null,
            'saldo_asal_target' => $saldoAsalTarget !== '' ? $saldoAsalTarget : null,
            'saldo_tujuan_target' => $saldoTujuanTarget !== '' ? $saldoTujuanTarget : null,
            'nominal' => $nominal,
            'karyawan_id' => $karyawanId,
            'keterangan' => $keterangan !== '' ? $keterangan : null,
            'updid' => $username,
        ];

        if ($mode === 'create') {
            $fields['toko_id'] = $tokoId;
            $this->insert($fields);
            return ['tipe' => 'success', 'data' => 'Mutasi kas berhasil ditambahkan'];
        }

        $existing = $this->find($kasId);
        if (!$existing || (string) ($existing['toko_id'] ?? '') !== $tokoId) {
            return ['tipe' => 'error', 'data' => 'Data mutasi kas tidak ditemukan'];
        }
        if (!$this->canMutateRow($existing)) {
            return ['tipe' => 'error', 'data' => 'Transaksi kas yang lewat hari sudah dikunci'];
        }

        $this->update($kasId, $fields);

        return ['tipe' => 'success', 'data' => 'Mutasi kas berhasil diupdate'];
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

        $isPrivileged = strtoupper((string) ($existing['tipe_mutasi'] ?? '')) === 'PINDAH_SALDO'
            || in_array(strtoupper((string) ($existing['nama_akun'] ?? '')), ['TAMBAH_MODAL_KAS', 'TARIK_KEUNTUNGAN'], true)
            || strtoupper((string) ($existing['saldo_target'] ?? '')) === 'PEMILIK';
        if ($isPrivileged && !$this->canDeleteAkses('kas')) {
            return ['tipe' => 'error', 'data' => 'Akses hapus mutasi ini hanya untuk akun dengan hak delete'];
        }

        $this->delete($kasId);
        return ['tipe' => 'success', 'data' => 'Mutasi kas berhasil dihapus'];
    }

    public function getRealtimeCash(array $storeIds, ?string $untilDate = null): array
    {
        $storeIds = array_values(array_filter(array_map('strval', $storeIds)));
        if (empty($storeIds)) {
            $storeIds = [(string) session('toko_id')];
        }
        $until = $untilDate ? date('Y-m-d 23:59:59', strtotime($untilDate)) : date('Y-m-d 23:59:59');

        $total = ['saldo_toko' => 0.0, 'saldo_pemilik' => 0.0, 'saldo_noncash' => 0.0];
        foreach ($storeIds as $storeId) {
            $row = $this->getRealtimeCashForStore($storeId, $until);
            foreach (array_keys($total) as $key) {
                $total[$key] += $row[$key];
            }
        }

        $total['total_cash'] = $total['saldo_toko'] + $total['saldo_pemilik'];
        $total['total_all'] = $total['total_cash'] + $total['saldo_noncash'];
        $total['as_of'] = substr($until, 0, 10);

        return $total;
    }

    public function getRealtimeCashPerStore(array $storeIds, ?string $untilDate = null): array
    {
        $storeIds = array_values(array_filter(array_map('strval', $storeIds)));
        if (empty($storeIds)) {
            $storeIds = [(string) session('toko_id')];
        }
        $until = $untilDate ? date('Y-m-d 23:59:59', strtotime($untilDate)) : date('Y-m-d 23:59:59');

        $result = [];
        foreach ($storeIds as $storeId) {
            $row = $this->getRealtimeCashForStore($storeId, $until);
            $row['total_cash'] = $row['saldo_toko'] + $row['saldo_pemilik'];
            $result[$storeId] = $row;
        }

        return $result;
    }

    private function getRealtimeCashForStore(string $tokoId, string $until): array
    {
        $snapshot = $this->db->query(
            "SELECT tahun, bulan,
                    COALESCE(saldo_toko, saldo_tunai, 0) AS saldo_toko,
                    COALESCE(saldo_pemilik, 0) AS saldo_pemilik,
                    COALESCE(saldo_transfer, 0) AS saldo_transfer,
                    COALESCE(saldo_qris, 0) AS saldo_qris
             FROM saldo_cash
             WHERE toko_id=:toko_id:
               AND CONCAT(LPAD(tahun,4,'0'), LPAD(bulan,2,'0')) < :current_ym:
             ORDER BY tahun DESC, bulan DESC
             LIMIT 1",
            [
                'toko_id' => $tokoId,
                'current_ym' => date('Ym'),
            ]
        )->getRowArray();

        if ($snapshot) {
            $snapshotEnd = date('Y-m-t 23:59:59', mktime(0, 0, 0, (int) $snapshot['bulan'], 1, (int) $snapshot['tahun']));
            $periodStart = date('Y-m-01 00:00:00', strtotime(substr($snapshotEnd, 0, 10) . ' +1 day'));
            $openingToko = (float) $snapshot['saldo_toko'];
            $openingPemilik = (float) $snapshot['saldo_pemilik'];
            $openingNoncash = (float) $snapshot['saldo_transfer'] + (float) $snapshot['saldo_qris'];
        } else {
            $periodStart = '1970-01-01 00:00:00';
            $openingToko = 0.0;
            $openingPemilik = 0.0;
            $openingNoncash = 0.0;
        }

        $paymentRows = $this->db->query(
            "SELECT pp.cara_bayar, COALESCE(SUM(pp.nominal_bayar),0) AS total
             FROM penjualan_pembayaran pp
             INNER JOIN penjualan j ON j.toko_id=pp.toko_id AND j.jual_id=pp.jual_id
             WHERE pp.toko_id=:toko_id: AND pp.tgl_bayar BETWEEN :start: AND :end:
               AND pp.cara_bayar IN ('TUNAI','TRANSFER','QRIS')
             GROUP BY pp.cara_bayar",
            ['toko_id' => $tokoId, 'start' => $periodStart, 'end' => $until]
        )->getResultArray();

        $supplierRows = $this->db->query(
            "SELECT cara_bayar, COALESCE(SUM(jumlah_bayar),0) AS total
             FROM pembelian_pembayaran
             WHERE toko_id=:toko_id: AND tanggal_bayar BETWEEN :start: AND :end:
               AND cara_bayar IN ('TUNAI','TRANSFER')
             GROUP BY cara_bayar",
            ['toko_id' => $tokoId, 'start' => $periodStart, 'end' => $until]
        )->getResultArray();

        $buckets = $this->sumBucketsForStore($tokoId, $periodStart, $until);

        $tunaiIn = 0.0;
        $noncashIn = 0.0;
        foreach ($paymentRows as $row) {
            if (strtoupper((string) ($row['cara_bayar'] ?? '')) === 'TUNAI') {
                $tunaiIn += (float) ($row['total'] ?? 0);
            } else {
                $noncashIn += (float) ($row['total'] ?? 0);
            }
        }

        $supplierTunaiOut = 0.0;
        $supplierNoncashOut = 0.0;
        foreach ($supplierRows as $row) {
            if (strtoupper((string) ($row['cara_bayar'] ?? '')) === 'TUNAI') {
                $supplierTunaiOut += (float) ($row['total'] ?? 0);
            } else {
                $supplierNoncashOut += (float) ($row['total'] ?? 0);
            }
        }

        return [
            'saldo_toko' => round($openingToko + $tunaiIn - $supplierTunaiOut + $buckets['cash_toko']['in'] - $buckets['cash_toko']['out'], 2),
            'saldo_pemilik' => round($openingPemilik + $buckets['cash_pemilik']['in'] - $buckets['cash_pemilik']['out'], 2),
            'saldo_noncash' => round($openingNoncash + $noncashIn - $supplierNoncashOut + $buckets['noncash']['in'] - $buckets['noncash']['out'], 2),
        ];
    }

    private function sumBucketsForStore(string $tokoId, string $start, string $end): array
    {
        $rows = $this->db->query(
            "SELECT bucket, direction, COALESCE(SUM(total),0) AS total
             FROM (
                SELECT CASE
                            WHEN COALESCE(km.saldo_channel, 'CASH')='NONCASH' THEN 'noncash'
                            WHEN COALESCE(km.saldo_target, 'TOKO')='PEMILIK' THEN 'cash_pemilik'
                            ELSE 'cash_toko'
                        END AS bucket,
                        CASE WHEN ak.jenis_akun='MASUK' THEN 'in' ELSE 'out' END AS direction,
                        COALESCE(km.nominal,0) AS total
                 FROM kas_mutasi km
                 INNER JOIN akun_kas ak ON ak.nama_akun=km.nama_akun
                 WHERE km.toko_id=:toko_id:
                   AND km.tanggal BETWEEN :start: AND :end:
                   AND COALESCE(km.tipe_mutasi, 'OPERASIONAL')='OPERASIONAL'
                UNION ALL
                SELECT CASE
                            WHEN km.saldo_asal='NONCASH' THEN 'noncash'
                            WHEN COALESCE(km.saldo_asal_target, 'TOKO')='PEMILIK' THEN 'cash_pemilik'
                            ELSE 'cash_toko'
                        END AS bucket,
                        'out' AS direction,
                        COALESCE(km.nominal,0) AS total
                 FROM kas_mutasi km
                 WHERE km.toko_id=:toko_id:
                   AND km.tanggal BETWEEN :start: AND :end:
                   AND km.tipe_mutasi='PINDAH_SALDO'
                UNION ALL
                SELECT CASE
                            WHEN km.saldo_tujuan='NONCASH' THEN 'noncash'
                            WHEN COALESCE(km.saldo_tujuan_target, 'TOKO')='PEMILIK' THEN 'cash_pemilik'
                            ELSE 'cash_toko'
                        END AS bucket,
                        'in' AS direction,
                        COALESCE(km.nominal,0) AS total
                 FROM kas_mutasi km
                 WHERE km.toko_id=:toko_id:
                   AND km.tanggal BETWEEN :start: AND :end:
                   AND km.tipe_mutasi='PINDAH_SALDO'
             ) x
             GROUP BY bucket, direction",
            ['toko_id' => $tokoId, 'start' => $start, 'end' => $end]
        )->getResultArray();

        $map = [
            'cash_toko' => ['in' => 0.0, 'out' => 0.0],
            'cash_pemilik' => ['in' => 0.0, 'out' => 0.0],
            'noncash' => ['in' => 0.0, 'out' => 0.0],
        ];
        foreach ($rows as $row) {
            $bucket = (string) ($row['bucket'] ?? '');
            $direction = (string) ($row['direction'] ?? '');
            if (isset($map[$bucket][$direction])) {
                $map[$bucket][$direction] = (float) ($row['total'] ?? 0);
            }
        }

        return $map;
    }

    public function depositToOwner(string $tokoId, string $username, $amount, string $note = '', ?string $karyawanId = null): array
    {
        $nominal = (int) preg_replace('/[^0-9\-]/', '', (string) $amount);
        if ($nominal <= 0) {
            return ['tipe' => 'error', 'data' => 'Nominal setoran harus lebih dari 0'];
        }

        $balance = $this->getRealtimeCash([$tokoId]);
        if ($balance['saldo_toko'] < $nominal) {
            return ['tipe' => 'error', 'data' => 'Saldo Toko tidak cukup. Tersedia: Rp ' . number_format((float) $balance['saldo_toko'], 0, ',', '.')];
        }

        $resolvedKaryawan = $karyawanId ?: $this->resolveKaryawanIdByUsername($username);

        $this->insert([
            'tanggal' => date('Y-m-d H:i:s'),
            'toko_id' => $tokoId,
            'nama_akun' => null,
            'tipe_mutasi' => 'PINDAH_SALDO',
            'saldo_channel' => 'CASH',
            'saldo_target' => 'TOKO',
            'saldo_asal' => 'CASH',
            'saldo_tujuan' => 'CASH',
            'saldo_asal_target' => 'TOKO',
            'saldo_tujuan_target' => 'PEMILIK',
            'nominal' => $nominal,
            'karyawan_id' => $resolvedKaryawan,
            'keterangan' => trim($note) !== '' ? mb_substr(trim($note), 0, 150) : 'Setoran toko ke pemilik',
            'updid' => $username,
        ]);

        return ['tipe' => 'success', 'data' => 'Setoran ke pemilik berhasil dicatat'];
    }

    public function withdrawProfit(string $tokoId, string $username, $amount, string $sourceTarget = 'TOKO', string $channel = 'CASH', string $note = '', ?string $karyawanId = null): array
    {
        if (!$this->canDeleteAkses('kas')) {
            return ['tipe' => 'error', 'data' => 'Akses tarik keuntungan hanya untuk akun dengan hak delete'];
        }

        $nominal = (int) preg_replace('/[^0-9\-]/', '', (string) $amount);
        if ($nominal <= 0) {
            return ['tipe' => 'error', 'data' => 'Nominal harus lebih dari 0'];
        }

        $sourceTarget = strtoupper(trim($sourceTarget));
        $channel = strtoupper(trim($channel));
        if (!in_array($sourceTarget, ['TOKO', 'PEMILIK'], true)) {
            $sourceTarget = 'TOKO';
        }
        if (!in_array($channel, ['CASH', 'NONCASH'], true)) {
            $channel = 'CASH';
        }

        $balance = $this->getRealtimeCash([$tokoId]);
        $available = $channel === 'CASH'
            ? ($sourceTarget === 'PEMILIK' ? $balance['saldo_pemilik'] : $balance['saldo_toko'])
            : $balance['saldo_noncash'];
        if ($available < $nominal) {
            return ['tipe' => 'error', 'data' => 'Saldo sumber tidak cukup. Tersedia: Rp ' . number_format((float) $available, 0, ',', '.')];
        }

        $this->ensureTarikKeuntunganAkun();
        $resolvedKaryawan = $karyawanId ?: $this->resolveKaryawanIdByUsername($username);

        $this->insert([
            'tanggal' => date('Y-m-d H:i:s'),
            'toko_id' => $tokoId,
            'nama_akun' => 'TARIK_KEUNTUNGAN',
            'tipe_mutasi' => 'OPERASIONAL',
            'saldo_channel' => $channel,
            'saldo_target' => $channel === 'CASH' ? $sourceTarget : 'TOKO',
            'saldo_asal' => null,
            'saldo_tujuan' => null,
            'saldo_asal_target' => null,
            'saldo_tujuan_target' => null,
            'nominal' => $nominal,
            'karyawan_id' => $resolvedKaryawan,
            'keterangan' => trim($note) !== '' ? mb_substr(trim($note), 0, 150) : 'Tarik keuntungan ' . strtolower($sourceTarget),
            'updid' => $username,
        ]);

        return ['tipe' => 'success', 'data' => 'Tarik keuntungan berhasil dicatat'];
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
                COALESCE(SUM(CASE WHEN ak.jenis_akun='MASUK' AND COALESCE(km.saldo_channel, 'CASH')='CASH' THEN km.nominal ELSE 0 END),0) AS total_tunai_masuk,
                COALESCE(SUM(CASE WHEN ak.jenis_akun='KELUAR' AND COALESCE(km.saldo_channel, 'CASH')='CASH' THEN km.nominal ELSE 0 END),0) AS total_tunai_keluar,
                COALESCE(SUM(CASE WHEN ak.jenis_akun='MASUK' AND COALESCE(km.saldo_channel, 'CASH')='NONCASH' THEN km.nominal ELSE 0 END),0) AS total_nontunai_masuk,
                COALESCE(SUM(CASE WHEN ak.jenis_akun='KELUAR' AND COALESCE(km.saldo_channel, 'CASH')='NONCASH' THEN km.nominal ELSE 0 END),0) AS total_nontunai_keluar,
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
                    km.toko_id,
                    MAX(t.toko_nama) AS toko_nama,
                    COUNT(*) AS total_transaksi,
                    SUM(km.nominal) AS total_nominal,
                    COALESCE(SUM(CASE WHEN COALESCE(km.saldo_channel, 'CASH')='CASH' THEN km.nominal ELSE 0 END),0) AS total_tunai,
                    COALESCE(SUM(CASE WHEN COALESCE(km.saldo_channel, 'CASH')='NONCASH' THEN km.nominal ELSE 0 END),0) AS total_nontunai
             FROM kas_mutasi km
             INNER JOIN akun_kas ak ON ak.nama_akun=km.nama_akun
             LEFT JOIN toko t ON t.toko_id=km.toko_id
             $where
             GROUP BY DATE(km.tanggal), ak.jenis_akun, km.nama_akun, km.toko_id
             ORDER BY DATE(km.tanggal) DESC, ak.jenis_akun, km.nama_akun, km.toko_id",
            $binds
        )->getResultArray();

        $chartRows = $this->db->query(
            "SELECT km.toko_id, km.nama_akun, ak.jenis_akun, SUM(km.nominal) AS total_nominal
             FROM kas_mutasi km
             INNER JOIN akun_kas ak ON ak.nama_akun=km.nama_akun
             $where
             GROUP BY km.toko_id, km.nama_akun, ak.jenis_akun
             ORDER BY ak.jenis_akun, km.nama_akun, km.toko_id",
            $binds
        )->getResultArray();

        return [
            'summary' => [
                'total_masuk' => (int) ($summary['total_masuk'] ?? 0),
                'total_keluar' => (int) ($summary['total_keluar'] ?? 0),
                'total_tunai_masuk' => (int) ($summary['total_tunai_masuk'] ?? 0),
                'total_tunai_keluar' => (int) ($summary['total_tunai_keluar'] ?? 0),
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

    private function humanizeBucket(string $channel, string $target): string
    {
        if (strtoupper($channel) === 'NONCASH') {
            return 'Non Tunai';
        }

        return strtoupper($target) === 'PEMILIK' ? 'Tunai Pemilik' : 'Tunai Toko';
    }

    private function canMutateRow(array $row): bool
    {
        return substr((string) ($row['tanggal'] ?? ''), 0, 10) === date('Y-m-d');
    }

    private function validateSourceBalance(string $tokoId, string $channel, string $target, int $nominal, int $excludeKasId): ?array
    {
        $balance = $this->getRealtimeCash([$tokoId]);
        if ($channel === 'CASH') {
            $available = strtoupper($target) === 'PEMILIK' ? $balance['saldo_pemilik'] : $balance['saldo_toko'];
        } else {
            $available = $balance['saldo_noncash'];
        }

        if ($excludeKasId > 0) {
            $existing = $this->db->query(
                "SELECT km.*, ak.jenis_akun FROM kas_mutasi km LEFT JOIN akun_kas ak ON ak.nama_akun=km.nama_akun WHERE km.kas_id=:kas_id: LIMIT 1",
                ['kas_id' => $excludeKasId]
            )->getRowArray();
            if ($existing) {
                $effect = $this->rowBucketEffect($existing);
                $bucketKey = $channel === 'CASH' ? (strtoupper($target) === 'PEMILIK' ? 'cash_pemilik' : 'cash_toko') : 'noncash';
                $available += -1 * ($effect[$bucketKey] ?? 0.0);
            }
        }

        if ($nominal > $available) {
            return ['tipe' => 'error', 'data' => 'Saldo sumber tidak cukup. Tersedia: Rp ' . number_format((float) $available, 0, ',', '.')];
        }

        return null;
    }

    private function rowBucketEffect(array $row): array
    {
        $nominal = (float) ($row['nominal'] ?? 0);
        $effect = ['cash_toko' => 0.0, 'cash_pemilik' => 0.0, 'noncash' => 0.0];

        if (strtoupper((string) ($row['tipe_mutasi'] ?? 'OPERASIONAL')) !== 'PINDAH_SALDO') {
            $channel = strtoupper((string) ($row['saldo_channel'] ?? 'CASH')) === 'NONCASH'
                ? 'noncash'
                : (strtoupper((string) ($row['saldo_target'] ?? 'TOKO')) === 'PEMILIK' ? 'cash_pemilik' : 'cash_toko');
            $effect[$channel] += strtoupper((string) ($row['jenis_akun'] ?? '')) === 'MASUK' ? $nominal : -$nominal;
            return $effect;
        }

        $sourceChannel = strtoupper((string) ($row['saldo_asal'] ?? '')) === 'NONCASH'
            ? 'noncash'
            : (strtoupper((string) ($row['saldo_asal_target'] ?? 'TOKO')) === 'PEMILIK' ? 'cash_pemilik' : 'cash_toko');
        $targetChannel = strtoupper((string) ($row['saldo_tujuan'] ?? '')) === 'NONCASH'
            ? 'noncash'
            : (strtoupper((string) ($row['saldo_tujuan_target'] ?? 'TOKO')) === 'PEMILIK' ? 'cash_pemilik' : 'cash_toko');
        $effect[$sourceChannel] -= $nominal;
        $effect[$targetChannel] += $nominal;

        return $effect;
    }

    private function canDeleteAkses(string $menuId): bool
    {
        $row = $this->db->query(
            "SELECT akses_delete FROM akses_menu WHERE level_id=:level_id: AND menu_id=:menu_id: LIMIT 1",
            [
                'level_id' => (string) session('level_id'),
                'menu_id' => $menuId,
            ]
        )->getRowArray();

        return ($row['akses_delete'] ?? '') === 'Y';
    }

    public function canMutateSaldo(): bool
    {
        return $this->canDeleteAkses('kas');
    }

    private function resolveKaryawanIdByUsername(string $username): string
    {
        $row = $this->db->query(
            "SELECT karyawan_id FROM tb_user WHERE username=:username: LIMIT 1",
            ['username' => $username]
        )->getRowArray();

        return (string) ($row['karyawan_id'] ?? $username);
    }

    private function ensureTarikKeuntunganAkun(): void
    {
        $exists = $this->db->query(
            "SELECT nama_akun FROM akun_kas WHERE nama_akun='TARIK_KEUNTUNGAN' LIMIT 1"
        )->getRowArray();
        if (!$exists) {
            $this->db->table('akun_kas')->insert([
                'nama_akun' => 'TARIK_KEUNTUNGAN',
                'jenis_akun' => 'KELUAR',
                'flag_beban' => 'N',
                'updid' => 'SYSTEM',
            ]);
        }
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
