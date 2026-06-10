<?php

namespace App\Controllers;

use App\Controllers\BaseController;
use App\Models\LapanalisamarginModel;
use App\Models\TokoModel;
use Config\Database;

class Lapanalisamargin extends BaseController
{
    protected LapanalisamarginModel $lapModel;
    protected TokoModel $tokoModel;

    public function __construct()
    {
        $this->lapModel = new LapanalisamarginModel();
        $this->tokoModel = new TokoModel();
    }

    public function index()
    {
        $data['title'] = 'Laporan Analisa Margin';
        $data['tokoOptions'] = $this->hasAccess('akses_delete') ? $this->tokoModel->getSwitcherList() : [];
        cek_akses_menu('lapanalisamargin/index', $data);
    }

    public function detailPage()
    {
        $data['title'] = 'Detail Analisa Margin';
        $data['tokoOptions'] = $this->hasAccess('akses_delete') ? $this->tokoModel->getSwitcherList() : [];
        $data['detailFilter'] = [
            'kat_id' => $this->request->getGet('kat_id') ?? '',
            'date_start' => $this->request->getGet('date_start') ?? '',
            'date_end' => $this->request->getGet('date_end') ?? '',
            'toko_ids' => $this->request->getGet('toko_ids') ?? [],
        ];
        cek_akses_menu('lapanalisamargin/detail', $data);
    }

    public function report()
    {
        $report = $this->lapModel->getReport(
            [
                'date_start' => $this->request->getVar('date_start') ?? '',
                'date_end' => $this->request->getVar('date_end') ?? '',
                'toko_ids' => $this->request->getVar('toko_ids'),
            ],
            (string) session('toko_id'),
            $this->hasAccess('akses_delete')
        );

        return $this->response->setJSON(['tipe' => 'success', 'data' => $report]);
    }

    public function detail()
    {
        $rows = $this->lapModel->getDetail(
            [
                'date_start' => $this->request->getVar('date_start') ?? '',
                'date_end' => $this->request->getVar('date_end') ?? '',
                'toko_ids' => $this->request->getVar('toko_ids'),
                'kat_id' => $this->request->getVar('kat_id') ?? '',
            ],
            (string) session('toko_id'),
            $this->hasAccess('akses_delete')
        );

        return $this->response->setJSON(['tipe' => 'success', 'data' => $rows]);
    }

    private function hasAccess(string $akses): bool
    {
        $db = Database::connect();
        $row = $db->query(
            "SELECT * FROM akses_menu WHERE level_id=:level_id: AND menu_id='lapanalisamargin' LIMIT 1",
            ['level_id' => session('level_id')]
        )->getRowArray();

        return !empty($row[$akses]) && $row[$akses] === 'Y';
    }
}
