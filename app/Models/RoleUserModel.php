<?php

namespace App\Models;

use CodeIgniter\Model;

class RoleUserModel extends Model
{
    protected $table = 'role_user';
    protected $primaryKey = 'level_id';
    protected $allowedFields = ['level_id', 'level_name'];

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

            $total_filter = $this->db->query("SELECT count(*) total FROM role_user  WHERE level_id like :search_value:  OR level_name like :search_value:   ", array("search_value" => '%' . $this->db->escapeLikeString($search_value) . '%'))->getRow();

            $data = $this->db->query("SELECT *,(SELECT COUNT(*) FROM tb_user WHERE `level_id`=r.level_id AND active='Y') jml_user FROM role_user r   WHERE level_id like :search_value:  OR level_name like :search_value:   ", array("search_value" => '%' . $this->db->escapeLikeString($search_value) . '%'))->getResult();
        } else {
            $data = $this->db->query("SELECT *,(SELECT COUNT(*) FROM tb_user WHERE `level_id`=r.level_id AND active='Y') jml_user FROM role_user r   $queryLimit  ")->getResult();
        }

        return array('data' => $data, 'total_count' => $total_count ?? 0, 'total_filtered' => $total_filter->total ?? $total_count);
    }

    public function ChangeAkses($data)
    {
        $level_id = $data['level_id'];
        $tipe = $data['tipe'];
        $menu_id = $data['menu_id'];
        $nilai = $data['nilai'];
        return  $this->db->query("UPDATE  akses_menu set akses_$tipe= :nilai: WHERE level_id= :level_id: and menu_id= :menu_id: ;", array(
            "nilai" => $nilai,
            "level_id" => $level_id,
            "menu_id" => $menu_id
        ));
    }
    public function getListAkses($level_id)
    {
        $this->db->query("INSERT IGNORE INTO akses_menu
                  SELECT menu_id, level_id, 'N', 'N', 'N', 'N'
                  FROM tb_menu
                  LEFT JOIN role_user ON 1 = 1 where level_id= ?;", [$level_id]);
        $data = $this->db->query("SELECT menu_id,menu_name,akses_read,akses_create,akses_update,akses_delete FROM tb_menu LEFT JOIN akses_menu USING(menu_id) WHERE `level_id`=? ORDER BY header_menu,urutan;", [$level_id])->getResult();
        return $data;
    }
}
