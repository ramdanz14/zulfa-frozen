<?php

namespace App\Controllers;

use App\Controllers\BaseController;
use App\Models\LapharianModel;
use App\Models\TokoModel;
use Config\Database;

class Lapharian extends BaseController
{
    protected LapharianModel $lapModel;
    protected TokoModel $tokoModel;

    public function __construct()
    {
        $this->lapModel = new LapharianModel();
        $this->tokoModel = new TokoModel();
    }

    public function index()
    {
        $data['title'] = 'Laporan Harian Kasir';
        $data['tokoOptions'] = $this->hasAccess('akses_delete') ? $this->tokoModel->getSwitcherList() : [];
        cek_akses_menu('lapharian/index', $data);
    }

    public function report()
    {
        $report = $this->lapModel->getReport(
            [
                'tanggal' => $this->request->getVar('tanggal') ?? '',
                'toko_ids' => $this->request->getVar('toko_ids'),
            ],
            (string) session('toko_id'),
            $this->hasAccess('akses_delete')
        );

        return $this->response->setJSON(['tipe' => 'success', 'data' => $report]);
    }

    public function struk()
    {
        $data['title'] = 'Struk Laporan Harian Kasir';
        $data['isMobile'] = cekMobile();
        $data['report'] = $this->lapModel->getReport(
            [
                'tanggal' => $this->request->getGet('tanggal') ?? '',
                'toko_ids' => $this->request->getGet('toko_ids') ?? [],
            ],
            (string) session('toko_id'),
            $this->hasAccess('akses_delete')
        );

        return view('lapharian/struk', $data);
    }

    private function hasAccess(string $akses): bool
    {
        $db = Database::connect();
        $row = $db->query(
            "SELECT * FROM akses_menu WHERE level_id=:level_id: AND menu_id='lapharian' LIMIT 1",
            ['level_id' => session('level_id')]
        )->getRowArray();

        return !empty($row[$akses]) && $row[$akses] === 'Y';
    }
}
