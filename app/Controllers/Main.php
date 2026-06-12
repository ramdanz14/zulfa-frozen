<?php

namespace App\Controllers;

use App\Models\MainModel;
use Config\Database;

class Main extends BaseController
{
    protected MainModel $mainModel;

    public function __construct()
    {
        $this->mainModel = new MainModel();
    }

    public function index()
    {
        $tokoId = (string) session('toko_id');
        $isOwner = $this->hasAccess('akses_delete');
        $data = [
            'title' => 'Dashboard',
            'dashboard' => $this->mainModel->getDashboard($tokoId),
            'isOwnerDashboard' => $isOwner,
        ];

        cek_akses_menu($isOwner ? 'main/owner' : 'main/staff', $data);
    }

    private function hasAccess(string $akses): bool
    {
        $db = Database::connect();
        $row = $db->query(
            "SELECT * FROM akses_menu WHERE level_id=:level_id: AND menu_id='main' LIMIT 1",
            ['level_id' => session('level_id')]
        )->getRowArray();

        return !empty($row[$akses]) && $row[$akses] === 'Y';
    }
}
