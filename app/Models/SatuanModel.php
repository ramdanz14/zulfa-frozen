<?php

namespace App\Models;

use CodeIgniter\Model;

class SatuanModel extends Model
{
    protected $table = 'satuan';
    protected $primaryKey = 'sat_id';
    protected $protectFields = false;

    public function ajax($params)
    {

        $start = $params['start'];
        $length = $params['length'];
        $search_value = $params['search_value'];
        $data = null;
        $total_filter = null;

        // how to call this model
        $total_count = $this->countAll();
        $queryLimit = $length != "-1" ? " limit $start, $length " : "";
        if (!empty($search_value)) {

            $total_filter = $this->db->query("SELECT count(*) total FROM satuan  WHERE sat_id like ? ", array('%' . $this->db->escapeLikeString($search_value) . '%'))->getRow();

            $data = $this->db->query("SELECT sat_id,COUNT(kode_item) jml_item FROM satuan LEFT JOIN konversi USING(sat_id)   WHERE sat_id like ? GROUP BY sat_id $queryLimit", array('%' . $this->db->escapeLikeString($search_value) . '%'))->getResult();
        } else {
            $data = $this->db->query("SELECT sat_id,COUNT(kode_item) jml_item FROM satuan LEFT JOIN konversi USING(sat_id) GROUP BY sat_id $queryLimit  ")->getResult();
        }

        return array('data' => $data, 'total_count' => $total_count ?? 0, 'total_filtered' => $total_filter->total ?? $total_count);
    }
}
