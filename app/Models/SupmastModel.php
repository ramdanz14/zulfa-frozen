<?php

namespace App\Models;

use CodeIgniter\Model;

class SupmastModel extends Model
{
    protected $table = 'supmast';
    protected $primaryKey = 'supco';
    protected $returnType = 'object';
    protected $allowedFields = ['supco', 'nama', 'alamat', 'kontak', 'email'];

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

            $total_filter = $this->db->query("SELECT count(*) total FROM supmast  WHERE supco like :search_value: OR nama like :search_value: OR alamat like :search_value: OR kontak like :search_value:  ", array("search_value" => '%' . $this->db->escapeLikeString($search_value) . '%'))->getRow();

            $data = $this->db->query("SELECT *,(SELECT COUNT(*) FROM prodmast WHERE supco=s.supco) AS jml_item  FROM supmast s WHERE supco like :search_value: OR nama like :search_value: OR alamat like :search_value: OR kontak like :search_value:  $queryLimit", array("search_value" => '%' . $this->db->escapeLikeString($search_value) . '%'))->getResult();
        } else {
            $data = $this->db->query("SELECT *,(SELECT COUNT(*) FROM prodmast WHERE supco=s.supco) AS jml_item   FROM supmast s $queryLimit  ")->getResult();
        }

        return array('data' => $data, 'total_count' => $total_count ?? 0, 'total_filtered' => $total_filter->total ?? $total_count);
    }

    public function GetLastID()
    {
        $maxNow =  $this->db->query("SELECT MAX(CAST(MID(supco,3,10) AS DECIMAL)) as kodex FROM supmast;")->getRow();
        $nourut = (int) substr($maxNow->kodex, 3);
        $nourut++;
        $output = "SP" . sprintf("%03s", $nourut);
        return $output;
    }
}
