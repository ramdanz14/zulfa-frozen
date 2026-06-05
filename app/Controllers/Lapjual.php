<?php

namespace App\Controllers;

use App\Controllers\BaseController;
use App\Models\JualModel;
use App\Models\TokoModel;
use Config\Database;

class Lapjual extends BaseController
{
    protected JualModel $jualModel;
    protected TokoModel $tokoModel;

    public function __construct()
    {
        $this->jualModel = new JualModel();
        $this->tokoModel = new TokoModel();
    }

    public function index()
    {
        $data['title'] = 'Laporan Penjualan Per Tanggal';
        $data['tokoOptions'] = $this->hasAccess('akses_delete') ? $this->tokoModel->getSwitcherList() : [];
        cek_akses_menu('lapjual/index', $data);
    }

    public function ajax()
    {
        $draw = (int) ($this->request->getVar('draw') ?? 0);
        $params = [
            'start' => $this->request->getVar('start'),
            'length' => $this->request->getVar('length'),
            'search_value' => $this->request->getVar('search')['value'] ?? '',
            'date_start' => $this->request->getVar('date_start') ?? '',
            'date_end' => $this->request->getVar('date_end') ?? '',
            'toko_ids' => $this->request->getVar('toko_ids'),
        ];

        $hasil = $this->jualModel->ajaxLaporanPenjualan(
            $params,
            (string) session('toko_id'),
            $this->hasAccess('akses_delete')
        );

        return $this->response->setJSON([
            'draw' => $draw,
            'recordsTotal' => $hasil['total_count'],
            'recordsFiltered' => $hasil['total_filtered'],
            'data' => $hasil['data'],
        ]);
    }

    public function summary()
    {
        $params = [
            'date_start' => $this->request->getVar('date_start') ?? '',
            'date_end' => $this->request->getVar('date_end') ?? '',
            'toko_ids' => $this->request->getVar('toko_ids'),
        ];

        $hasil = $this->jualModel->getLaporanPenjualanSummary(
            $params,
            (string) session('toko_id'),
            $this->hasAccess('akses_delete')
        );

        return $this->response->setJSON(['tipe' => 'success', 'data' => $hasil]);
    }

    private function hasAccess(string $akses): bool
    {
        $db = Database::connect();
        $row = $db->query(
            "SELECT * FROM akses_menu WHERE level_id=:level_id: AND menu_id='lapjual' LIMIT 1",
            ['level_id' => session('level_id')]
        )->getRowArray();

        return !empty($row[$akses]) && $row[$akses] === 'Y';
    }
}
