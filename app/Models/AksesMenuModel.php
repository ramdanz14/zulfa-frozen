<?php

namespace App\Models;

use CodeIgniter\Model;

class AksesMenuModel extends Model
{
    protected $table = 'akses_menu';
    protected $primaryKey = ['menu_id', 'level_id'];
    protected $allowedFields = ['menu_id', 'level_id', 'akses_create', 'akses_read', 'akses_update', 'akses_delete'];

    // Add any additional configurations or methods here
    public function getAksesMenu($menu_id, $level_id)
    {
        return $this->db->table('akses_menu')
            ->where('menu_id', $menu_id)
            ->where('level_id', $level_id)
            ->get()->getRow();
    }
}
