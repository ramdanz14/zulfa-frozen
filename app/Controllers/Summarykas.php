<?php

namespace App\Controllers;

use App\Controllers\BaseController;
use App\Models\KasModel;
use App\Models\TokoModel;
use Config\Database;

class Summarykas extends BaseController
{
    protected KasModel $kasModel;
    protected TokoModel $tokoModel;

    public function __construct()
    {
        $this->kasModel = new KasModel();
        $this->tokoModel = new TokoModel();
    }

    public function index()
    {
        $data['title'] = 'Summary Kas';
        $data['tokoOptions'] = $this->hasAccess('akses_update') ? $this->tokoModel->getSwitcherList() : [];
        cek_akses_menu('summarykas/index', $data);
    }

    public function ajax()
    {
        $result = $this->kasModel->getSummary(
            (string) session('toko_id'),
            [
                'date_start' => $this->request->getVar('date_start'),
                'date_end' => $this->request->getVar('date_end'),
                'toko_ids' => $this->request->getVar('toko_ids'),
            ],
            $this->hasAccess('akses_update')
        );

        return $this->response->setJSON([
            'draw' => (int) ($this->request->getVar('draw') ?? 0),
            'recordsTotal' => count($result['rows']),
            'recordsFiltered' => count($result['rows']),
            'data' => $result['rows'],
        ]);
    }

    public function summary()
    {
        $result = $this->kasModel->getSummary(
            (string) session('toko_id'),
            [
                'date_start' => $this->request->getVar('date_start'),
                'date_end' => $this->request->getVar('date_end'),
                'toko_ids' => $this->request->getVar('toko_ids'),
            ],
            $this->hasAccess('akses_update')
        );

        return $this->response->setJSON([
            'tipe' => 'success',
            'data' => [
                'summary' => $result['summary'],
                'chart_rows' => $result['chart_rows'],
            ],
        ]);
    }

    private function hasAccess(string $akses): bool
    {
        $db = Database::connect();
        $row = $db->query(
            "SELECT * FROM akses_menu WHERE level_id=:level_id: AND menu_id='summarykas' LIMIT 1",
            ['level_id' => session('level_id')]
        )->getRowArray();

        return !empty($row[$akses]) && $row[$akses] === 'Y';
    }
}
