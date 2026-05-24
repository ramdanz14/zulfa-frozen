<?php

namespace App\Models;

use CodeIgniter\Model;

class MenuModel extends Model
{
    protected $table = 'tb_menu';
    protected $primaryKey = 'menu_id';
    protected $allowedFields = ['menu_id', 'menu_name', 'link', 'icon', 'header_menu', 'urutan'];
    protected $useTimestamps = false;

    public function getHeaderMenu($level_id)
    {
        return $this->db->query("SELECT * FROM tb_menu JOIN akses_menu USING(menu_id) WHERE `level_id`= ? AND  akses_read='Y'   AND header_menu='' ORDER BY header_menu,urutan", array($level_id))->getResultObject();
    }

    public function getSubMenu($level_id)
    {
        return $this->db->query("SELECT * FROM tb_menu JOIN akses_menu USING(menu_id) WHERE `level_id`= ?  AND  akses_read='Y'   AND header_menu!='' ORDER BY header_menu,urutan", array($level_id))->getResultObject();
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
            $total_filter = $this->db->query("SELECT count(*) total FROM tb_menu WHERE menu_id like :s: OR menu_name like :s: OR link like :s: OR icon like :s: OR header_menu like :s: OR urutan like :s:", ["s" => $like])->getRow();
            $data = $this->db->query("SELECT * FROM tb_menu WHERE menu_id like :s: OR menu_name like :s: OR link like :s: OR icon like :s: OR header_menu like :s: OR urutan like :s:", ["s" => $like])->getResult();
        } else {
            $data = $this->db->query("SELECT * FROM tb_menu ORDER BY header_menu, urutan $queryLimit")->getResult();
        }
        return ['data' => $data, 'total_count' => $total_count ?? 0, 'total_filtered' => $total_filter->total ?? $total_count];
    }
}
