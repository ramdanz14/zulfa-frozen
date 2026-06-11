<?php

namespace App\Controllers;

use App\Controllers\BaseController;
use App\Models\LaphutangModel;
use App\Models\TokoModel;
use Config\Database;

class Laphutang extends BaseController
{
    protected LaphutangModel $lapModel;
    protected TokoModel $tokoModel;

    public function __construct()
    {
        $this->lapModel = new LaphutangModel();
        $this->tokoModel = new TokoModel();
    }

    public function index()
    {
        $data['title'] = 'Laporan Hutang Supplier';
        $data['tokoOptions'] = $this->hasAccess('akses_delete') ? $this->tokoModel->getSwitcherList() : [];
        cek_akses_menu('laphutang/index', $data);
    }

    public function report()
    {
        $result = $this->lapModel->getReport(
            [
                'date_start' => $this->request->getVar('date_start') ?? '',
                'date_end' => $this->request->getVar('date_end') ?? '',
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
            "SELECT * FROM akses_menu WHERE level_id=:level_id: AND menu_id='laphutang' LIMIT 1",
            ['level_id' => session('level_id')]
        )->getRowArray();

        return !empty($row[$akses]) && $row[$akses] === 'Y';
    }
}
