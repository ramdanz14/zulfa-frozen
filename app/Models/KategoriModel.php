<?php

namespace App\Models;

use CodeIgniter\Model;

class KategoriModel extends Model
{
    protected $table            = 'kategori';
    protected $primaryKey       = 'kat_id';
    protected $protectFields    = false;

    public function ajax($params)
    {
        // dd($params);
        $start = $params['start'];
        $length = $params['length'];
        $search_value = $params['search_value'];
        $data = null;
        $total_filter = null;
        $total_count = $this->countAll();
        $queryLimit = $length != "-1" ? " limit $start, $length " : "";
        if (!empty($search_value)) {

            $total_filter = $this->db->query("SELECT count(*) total FROM kategori  WHERE kat_id like ? ", array('%' . $this->db->escapeLikeString($search_value) . '%'))->getRow();

            $data = $this->db->query("SELECT kat_id,COUNT(kode_item) jml_item FROM kategori LEFT JOIN prodmast USING(kat_id)   WHERE kat_id like ? GROUP BY kat_id $queryLimit", array('%' . $this->db->escapeLikeString($search_value) . '%'))->getResult();
        } else {
            $data = $this->db->query("SELECT kat_id,COUNT(kode_item) jml_item FROM kategori LEFT JOIN prodmast USING(kat_id) GROUP BY kat_id $queryLimit  ")->getResult();
        }

        return array('data' => $data, 'total_count' => $total_count ?? 0, 'total_filtered' => $total_filter->total ?? $total_count);
    }
}
