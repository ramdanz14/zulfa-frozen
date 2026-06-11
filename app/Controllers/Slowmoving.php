<?php

namespace App\Controllers;

use App\Controllers\BaseController;
use App\Models\SlowmovingModel;
use App\Models\TokoModel;
use Config\Database;

class Slowmoving extends BaseController
{
    protected SlowmovingModel $lapModel;
    protected TokoModel $tokoModel;

    public function __construct()
    {
        $this->lapModel = new SlowmovingModel();
        $this->tokoModel = new TokoModel();
    }

    public function index()
    {
        $data['title'] = 'Laporan Slow Moving';
        $data['tokoOptions'] = $this->hasAccess('akses_delete') ? $this->tokoModel->getSwitcherList() : [];
        cek_akses_menu('slowmoving/index', $data);
    }

    public function report()
    {
        $result = $this->lapModel->getReport(
            [
                'toko_id' => $this->request->getVar('toko_id') ?? '',
            ],
            (string) session('toko_id'),
            $this->hasAccess('akses_delete')
        );

        return $this->response->setJSON(['tipe' => 'success', 'data' => $result]);
    }

    private function hasAccess(string $akses): bool
    {
        $db = Database::connect();
        $row = $db->query(
            "SELECT * FROM akses_menu WHERE level_id=:level_id: AND menu_id='slowmoving' LIMIT 1",
            ['level_id' => session('level_id')]
        )->getRowArray();

        return !empty($row[$akses]) && $row[$akses] === 'Y';
    }
}
