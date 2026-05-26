<?php

namespace App\Models;

use CodeIgniter\Model;

class UserModel extends Model
{
    protected $table            = 'tb_user';
    protected $primaryKey       = 'karyawan_id';
    protected $allowedFields    = ['karyawan_id', 'username', 'fullname', 'password', 'email', 'phone', 'level_id', 'active', 'avatar', 'alamat', 'absensi',  'toko_id', 'updid', 'updtime'];

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

            $total_filter = $this->db->query("SELECT count(*) total FROM tb_user  WHERE karyawan_id like :search_value: OR username like :search_value: OR fullname like :search_value: OR level_id like :search_value:  ", array("search_value" => '%' . $this->db->escapeLikeString($search_value) . '%'))->getRow();

            $data = $this->db->query("SELECT * FROM tb_user WHERE karyawan_id like :search_value: OR username like :search_value: OR fullname like :search_value: OR level_id like :search_value:   $queryLimit", array("search_value" => '%' . $this->db->escapeLikeString($search_value) . '%'))->getResult();
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
}
