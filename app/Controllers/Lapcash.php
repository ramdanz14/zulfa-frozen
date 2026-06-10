<?php

namespace App\Controllers;

use App\Controllers\BaseController;
use App\Models\LapcashModel;
use App\Models\TokoModel;
use Config\Database;

class Lapcash extends BaseController
{
    protected LapcashModel $lapcashModel;
    protected TokoModel $tokoModel;

    public function __construct()
    {
        $this->lapcashModel = new LapcashModel();
        $this->tokoModel = new TokoModel();
    }

    public function index()
    {
        $data['title'] = 'Laporan Cash Flow Per Bulan';
        $data['tokoOptions'] = $this->hasAccess('akses_delete') ? $this->tokoModel->getSwitcherList() : [];
        cek_akses_menu('lapcash/index', $data);
    }

    public function report()
    {
        $result = $this->lapcashModel->getReport(
            [
                'periode' => $this->request->getVar('periode') ?? '',
                'toko_ids' => $this->request->getVar('toko_ids'),
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
            "SELECT * FROM akses_menu WHERE level_id=:level_id: AND menu_id='lapcash' LIMIT 1",
            ['level_id' => session('level_id')]
        )->getRowArray();

        return !empty($row[$akses]) && $row[$akses] === 'Y';
    }
}
