<?php

namespace App\Controllers;

use App\Controllers\BaseController;
use App\Models\SaldousahaModel;
use App\Models\TokoModel;
use Config\Database;

class Saldousaha extends BaseController
{
    protected SaldousahaModel $saldoModel;
    protected TokoModel $tokoModel;

    public function __construct()
    {
        $this->saldoModel = new SaldousahaModel();
        $this->tokoModel = new TokoModel();
    }

    public function index()
    {
        $data['title'] = 'Saldo Usaha';
        $data['tokoOptions'] = $this->hasAccess('akses_delete') ? $this->tokoModel->getSwitcherList() : [];
        cek_akses_menu('saldousaha/index', $data);
    }

    public function report()
    {
        $result = $this->saldoModel->getReport(
            [
                'date_start' => $this->request->getVar('date_start') ?? '',
                'date_end' => $this->request->getVar('date_end') ?? '',
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
            "SELECT * FROM akses_menu WHERE level_id=:level_id: AND menu_id='saldousaha' LIMIT 1",
            ['level_id' => session('level_id')]
        )->getRowArray();

        return !empty($row[$akses]) && $row[$akses] === 'Y';
    }
}
