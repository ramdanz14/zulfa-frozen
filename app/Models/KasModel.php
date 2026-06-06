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
            $where .= " AND (km.nama_akun LIKE :search: OR km.karyawan_id LIKE :search: OR u.fullname LIKE :search: OR km.keterangan LIKE :search:) ";
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

    public function saveMutation(string $tokoId, string $username, array $payload, string $mode): array
    {
        $tanggal = trim((string) ($payload['tanggal'] ?? ''));
        $namaAkun = strtoupper(trim((string) ($payload['nama_akun'] ?? '')));
        $nominal = (int) preg_replace('/[^0-9\-]/', '', (string) ($payload['nominal'] ?? 0));
        $karyawanId = trim((string) ($payload['karyawan_id'] ?? ''));
        $keterangan = trim((string) ($payload['keterangan'] ?? ''));
        $kasId = (int) ($payload['kas_id'] ?? 0);

        if ($tanggal === '' || substr($tanggal, 0, 10) !== date('Y-m-d')) {
            return ['tipe' => 'error', 'data' => 'Transaksi kas hanya boleh diinput pada tanggal hari ini'];
        }
        if ($namaAkun === '' || $nominal <= 0 || $karyawanId === '') {
            return ['tipe' => 'error', 'data' => 'Tanggal, akun kas, nominal, dan karyawan wajib diisi'];
        }

        $akun = $this->db->query("SELECT * FROM akun_kas WHERE nama_akun=:nama_akun: LIMIT 1", ['nama_akun' => $namaAkun])->getRowArray();
        if (!$akun) {
            return ['tipe' => 'error', 'data' => 'Akun kas tidak ditemukan'];
        }
        $karyawan = $this->db->query("SELECT karyawan_id FROM tb_user WHERE karyawan_id=:karyawan_id: LIMIT 1", ['karyawan_id' => $karyawanId])->getRowArray();
        if (!$karyawan) {
            return ['tipe' => 'error', 'data' => 'Karyawan tidak ditemukan'];
        }

        if ($mode === 'create') {
            $this->insert([
                'tanggal' => $tanggal,
                'toko_id' => $tokoId,
                'nama_akun' => $namaAkun,
                'nominal' => $nominal,
                'karyawan_id' => $karyawanId,
                'keterangan' => $keterangan !== '' ? $keterangan : null,
                'updid' => $username,
            ]);
            return ['tipe' => 'success', 'data' => 'Mutasi kas berhasil ditambahkan'];
        }

        $existing = $this->find($kasId);
        if (!$existing || (string) ($existing['toko_id'] ?? '') !== $tokoId) {
            return ['tipe' => 'error', 'data' => 'Data mutasi kas tidak ditemukan'];
        }
        if (!$this->canMutateRow($existing)) {
            return ['tipe' => 'error', 'data' => 'Transaksi kas yang lewat hari sudah dikunci'];
        }

        $this->update($kasId, [
            'tanggal' => $tanggal,
            'nama_akun' => $namaAkun,
            'nominal' => $nominal,
            'karyawan_id' => $karyawanId,
            'keterangan' => $keterangan !== '' ? $keterangan : null,
            'updid' => $username,
        ]);

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

        $this->delete($kasId);
        return ['tipe' => 'success', 'data' => 'Mutasi kas berhasil dihapus'];
    }

    public function getAkunOptions(): array
    {
        return $this->db->query("SELECT nama_akun, jenis_akun FROM akun_kas ORDER BY jenis_akun, nama_akun")->getResultArray();
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
                    SUM(km.nominal) AS total_nominal
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
                'saldo_bersih' => (int) (($summary['total_masuk'] ?? 0) - ($summary['total_keluar'] ?? 0)),
                'total_transaksi' => (int) ($summary['total_transaksi'] ?? 0),
                'stores' => $effectiveStores,
            ],
            'rows' => $tableRows,
            'chart_rows' => $chartRows,
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
