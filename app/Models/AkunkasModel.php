<?php

namespace App\Models;

use CodeIgniter\Model;

class AkunkasModel extends Model
{
    protected $table = 'akun_kas';
    protected $primaryKey = 'nama_akun';
    protected $returnType = 'array';
    protected $protectFields = false;

    public function ajax(array $params): array
    {
        $start = (int) ($params['start'] ?? 0);
        $length = $params['length'] ?? 25;
        $search = trim((string) ($params['search_value'] ?? ''));
        $queryLimit = $length !== '-1' ? " LIMIT $start, " . (int) $length : '';
        $binds = [];
        $where = '';
        if ($search !== '') {
            $where = " WHERE nama_akun LIKE :search: OR jenis_akun LIKE :search: ";
            $binds['search'] = '%' . $this->db->escapeLikeString($search) . '%';
        }

        $total = (int) ($this->db->query("SELECT COUNT(*) AS total FROM akun_kas")->getRowArray()['total'] ?? 0);
        $filtered = $total;
        if ($where !== '') {
            $filtered = (int) ($this->db->query("SELECT COUNT(*) AS total FROM akun_kas $where", $binds)->getRowArray()['total'] ?? 0);
        }

        $data = $this->db->query(
            "SELECT a.nama_akun, a.jenis_akun, a.updid,
                    EXISTS(SELECT 1 FROM kas_mutasi km WHERE km.nama_akun=a.nama_akun LIMIT 1) AS is_locked
             FROM akun_kas a
             $where
             ORDER BY a.jenis_akun, a.nama_akun
             $queryLimit",
            $binds
        )->getResultArray();

        return [
            'data' => $data,
            'total_count' => $total,
            'total_filtered' => $filtered,
        ];
    }

    public function saveAccount(string $username, array $payload, string $mode): array
    {
        $namaAkun = strtoupper(trim((string) ($payload['nama_akun'] ?? '')));
        $jenisAkun = strtoupper(trim((string) ($payload['jenis_akun'] ?? '')));
        $oldNamaAkun = strtoupper(trim((string) ($payload['old_nama_akun'] ?? '')));

        if ($namaAkun === '' || !in_array($jenisAkun, ['MASUK', 'KELUAR'], true)) {
            return ['tipe' => 'error', 'data' => 'Nama akun dan jenis akun wajib valid'];
        }

        if ($mode === 'create') {
            $exists = $this->where('nama_akun', $namaAkun)->first();
            if ($exists) {
                return ['tipe' => 'error', 'data' => 'Nama akun sudah digunakan'];
            }
            $this->insert([
                'nama_akun' => $namaAkun,
                'jenis_akun' => $jenisAkun,
                'updid' => $username,
            ]);
            return ['tipe' => 'success', 'data' => 'Akun kas berhasil ditambahkan'];
        }

        if ($oldNamaAkun === '' || !$this->where('nama_akun', $oldNamaAkun)->first()) {
            return ['tipe' => 'error', 'data' => 'Data akun kas tidak ditemukan'];
        }

        if ($oldNamaAkun !== $namaAkun && $this->where('nama_akun', $namaAkun)->first()) {
            return ['tipe' => 'error', 'data' => 'Nama akun baru sudah digunakan'];
        }

        if ($oldNamaAkun !== $namaAkun && $this->isUsed($oldNamaAkun)) {
            return ['tipe' => 'error', 'data' => 'Akun kas yang sudah dipakai transaksi tidak boleh ganti nama'];
        }

        $this->db->transStart();
        if ($oldNamaAkun !== $namaAkun) {
            $this->db->query(
                "UPDATE akun_kas SET nama_akun=:nama_akun:, jenis_akun=:jenis_akun:, updid=:updid: WHERE nama_akun=:old_nama_akun:",
                [
                    'nama_akun' => $namaAkun,
                    'jenis_akun' => $jenisAkun,
                    'updid' => $username,
                    'old_nama_akun' => $oldNamaAkun,
                ]
            );
        } else {
            $this->update($oldNamaAkun, [
                'jenis_akun' => $jenisAkun,
                'updid' => $username,
            ]);
        }
        $this->db->transComplete();

        if (!$this->db->transStatus()) {
            return ['tipe' => 'error', 'data' => 'Gagal menyimpan akun kas'];
        }

        return ['tipe' => 'success', 'data' => 'Akun kas berhasil diupdate'];
    }

    public function deleteAccount(string $namaAkun): array
    {
        $namaAkun = strtoupper(trim($namaAkun));
        if ($namaAkun === '' || !$this->where('nama_akun', $namaAkun)->first()) {
            return ['tipe' => 'error', 'data' => 'Data akun kas tidak ditemukan'];
        }
        if ($this->isUsed($namaAkun)) {
            return ['tipe' => 'error', 'data' => 'Akun kas sudah dipakai transaksi dan tidak boleh dihapus'];
        }

        $this->delete($namaAkun);
        return ['tipe' => 'success', 'data' => 'Akun kas berhasil dihapus'];
    }

    public function getOptions(?string $jenis = null): array
    {
        $builder = $this->orderBy('jenis_akun')->orderBy('nama_akun');
        if ($jenis !== null && $jenis !== '') {
            $builder->where('jenis_akun', strtoupper($jenis));
        }
        return $builder->findAll();
    }

    private function isUsed(string $namaAkun): bool
    {
        $row = $this->db->query(
            "SELECT COUNT(*) AS total FROM kas_mutasi WHERE nama_akun=:nama_akun:",
            ['nama_akun' => $namaAkun]
        )->getRowArray();

        return (int) ($row['total'] ?? 0) > 0;
    }
}
