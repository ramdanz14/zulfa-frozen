<?php

namespace App\Models;

use CodeIgniter\Model;

class UserModel extends Model
{
    protected $table            = 'tb_user';
    protected $primaryKey       = 'karyawan_id';
    protected $allowedFields    = ['karyawan_id', 'username', 'fullname', 'password', 'email', 'phone', 'level_id', 'active', 'avatar', 'alamat', 'absensi', 'gaji', 'toko_id', 'updid', 'updtime'];

    protected bool $allowEmptyInserts = false;

    // Dates
    protected $useTimestamps = true;
    protected $dateFormat    = 'datetime';
    protected $createdField  = 'updtime';
    protected $updatedField  = 'updtime';

    protected $beforeInsert = ['beforeInsertSetUsername'];
    protected $beforeUpdate = ['beforeUpdateSetUsername'];

    protected function beforeInsertSetUsername(array $data)
    {
        if (isset($data['data'])) {
            $data['data']['updid'] = session()->username;
        }

        return $data;
    }

    protected function beforeUpdateSetUsername(array $data)
    {
        if (isset($data['data'])) {
            $data['data']['updid'] = session()->username;
        }

        return $data;
    }


    public function ajax($params)
    {
        $start = $params['start'];
        $length = $params['length'];
        $search_value = $params['search_value'];
        $data = null;
        $total_filter = null;
        $total_count = $this->countAll();
        $queryLimit = $length != "-1" ? " limit $start, $length " : "";
        if (!empty($search_value)) {

            $total_filter = $this->db->query("SELECT count(*) total FROM tb_user  WHERE karyawan_id like :search_value: OR username like :search_value: OR fullname like :search_value: OR level_id like :search_value: OR toko_id like :search_value:  ", array("search_value" => '%' . $this->db->escapeLikeString($search_value) . '%'))->getRow();

            $data = $this->db->query("SELECT * FROM tb_user WHERE karyawan_id like :search_value: OR username like :search_value: OR fullname like :search_value: OR level_id like :search_value: OR toko_id like :search_value:   $queryLimit", array("search_value" => '%' . $this->db->escapeLikeString($search_value) . '%'))->getResult();
        } else {
            $data = $this->db->query("SELECT *  FROM tb_user  $queryLimit  ")->getResult();
        }

        return array('data' => $data, 'total_count' => $total_count ?? 0, 'total_filtered' => $total_filter->total ?? $total_count);
    }

    public function GetLastID()
    {

        $maxNow =  $this->db->query("SELECT  MAX(CAST(MID(karyawan_id,3,10) AS DECIMAL)) as kodex FROM tb_user;")->getRow();
        $nourut = (int) $maxNow->kodex ?? 0;
        $nourut++;
        $output = "KY" . sprintf("%03s", $nourut);
        return $output;
    }

    public function GetDetail(string $karyawan_id)
    {
        return $this->db->query("SELECT * FROM tb_user LEFT JOIN role_user USING(level_id)  WHERE karyawan_id= ? ", [$karyawan_id])
            ->getRow();
    }
    public function GetKasbon(string $karyawan_id)
    {
        return $this->db->query("SELECT sisa FROM sisa_kasbon  WHERE karyawan_id= ? ", [$karyawan_id])
            ->getRow();
    }
    public function GetGaji(string $karyawan_id)
    {
        return $this->db->query("SELECT EXTRACT(YEAR_MONTH FROM tgl_bayar) bulan,SUM(jml_hari) hari, SUM(gapok) gapok, SUM(insentif) insentif, SUM(potongan) potongan, SUM(gaji_bersih) gaji_bersih FROM rekap_gaji WHERE tgl_bayar>=DATE_SUB(CURDATE(), INTERVAL 1 year) AND karyawan_id= ? GROUP BY EXTRACT(YEAR_MONTH FROM tgl_bayar)", [$karyawan_id])
            ->getResultObject();
    }

    public function ResetPassword(string $karyawan_id)
    {
        return $this->db->query("UPDATE tb_user SET `password`=PASSWORD(lower(username)) WHERE karyawan_id= ? ", [$karyawan_id]);
    }

    public function getAbsensiUsers(): array
    {
        return $this->db->query(
            "SELECT karyawan_id, fullname, toko_id, COALESCE(gaji, 0) AS gaji
             FROM tb_user
             WHERE active='Y' AND absensi='Y'
             ORDER BY fullname, karyawan_id"
        )->getResultArray();
    }

    public function getProfileByUsername(string $username): ?array
    {
        $row = $this->db->query(
            "SELECT u.karyawan_id, u.username, u.fullname, u.email, u.phone, u.avatar, u.alamat,
                    r.level_name
             FROM tb_user u
             LEFT JOIN role_user r USING(level_id)
             WHERE u.username=:username:
             LIMIT 1",
            ['username' => $username]
        )->getRowArray();

        return $row ?: null;
    }

    public function verifyPassword(string $username, string $password): bool
    {
        $row = $this->db->query(
            "SELECT karyawan_id
             FROM tb_user
             WHERE username=:username: AND `password`=PASSWORD(:password:)
             LIMIT 1",
            ['username' => $username, 'password' => $password]
        )->getRowArray();

        return !empty($row['karyawan_id']);
    }

    public function updatePasswordByUsername(string $username, string $newPassword): bool
    {
        return (bool) $this->db->query(
            "UPDATE tb_user
             SET `password`=PASSWORD(:password:)
             WHERE username=:username:",
            ['password' => $newPassword, 'username' => $username]
        );
    }

    public function updateAvatarByUsername(string $username, string $avatar): bool
    {
        return (bool) $this->db->query(
            "UPDATE tb_user
             SET avatar=:avatar:
             WHERE username=:username:",
            ['avatar' => $avatar, 'username' => $username]
        );
    }
}
