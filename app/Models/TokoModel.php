<?php

namespace App\Models;

use CodeIgniter\Model;

class TokoModel extends Model
{
    protected $table = 'toko';
    protected $primaryKey = 'toko_id';
    protected $allowedFields = ['toko_id', 'toko_nama', 'toko_alamat', 'toko_theme', 'toko_phone', 'flag_gudang'];
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

    public function GetLastID()
    {

        $maxNow =  $this->db->query("SELECT  MAX(CAST(MID(toko_id,3,10) AS DECIMAL)) as kodex FROM toko;")->getRow();
        $nourut = (int) $maxNow->kodex ?? 0;
        $nourut++;
        $output = "TK" . sprintf("%02s", $nourut);
        return $output;
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
            $like = '%' . $this->db->escapeLikeString($search_value) . '%';
            $total_filter = $this->db->query("SELECT count(*) total FROM toko WHERE toko_id like :s: OR toko_nama like :s: OR toko_alamat like :s: OR toko_phone like :s: OR toko_theme like :s: ", ["s" => $like])->getRow();
            $data = $this->db->query("SELECT * FROM toko  WHERE toko_id like :s: OR toko_nama like :s: OR toko_alamat like :s: OR toko_phone like :s: OR toko_theme like :s: ", ["s" => $like])->getResult();
        } else {
            $data = $this->db->query("SELECT * FROM toko  $queryLimit")->getResult();
        }
        return ['data' => $data, 'total_count' => $total_count ?? 0, 'total_filtered' => $total_filter->total ?? $total_count];
    }
}
