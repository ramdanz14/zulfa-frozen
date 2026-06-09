<?php

namespace App\Controllers;

use App\Controllers\BaseController;
use App\Models\ClosingModel;
use Config\Database;

class Closing extends BaseController
{
    protected ClosingModel $closingModel;

    public function __construct()
    {
        $this->closingModel = new ClosingModel();
    }

    public function index()
    {
        $data['title'] = 'Closing Bulanan';
        $data['dashboard'] = $this->closingModel->getDashboard((string) session('toko_id'));
        cek_akses_menu('closing/index', $data);
    }

    public function dashboard()
    {
        if (!$this->hasAccess('akses_read')) {
            return $this->response->setJSON(['tipe' => 'error', 'data' => 'Tidak memiliki akses closing'])->setStatusCode(400);
        }

        return $this->response->setJSON([
            'tipe' => 'success',
            'data' => $this->closingModel->getDashboard((string) session('toko_id')),
        ]);
    }

    public function process()
    {
        if (!$this->hasAccess('akses_create')) {
            return $this->response->setJSON(['tipe' => 'error', 'data' => 'Tidak memiliki akses proses closing'])->setStatusCode(400);
        }

        $result = $this->closingModel->closeStore((string) session('toko_id'), (string) session('username'), 'WEB');
        if (($result['tipe'] ?? '') === 'success') {
            tracelog('CREATE', 'CLOSING BULANAN WEB ' . json_encode($result, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES));
        }

        return $this->response->setJSON($result);
    }

    public function reclose()
    {
        if (!$this->hasAccess('akses_delete')) {
            return $this->response->setJSON(['tipe' => 'error', 'data' => 'Closing ulang hanya untuk user IT'])->setStatusCode(400);
        }

        $period = trim((string) ($this->request->getVar('periode') ?? ''));
        if ($period === '') {
            return $this->response->setJSON(['tipe' => 'error', 'data' => 'Periode closing ulang wajib diisi'])->setStatusCode(400);
        }

        $result = $this->closingModel->recloseFrom((string) session('toko_id'), $period, (string) session('username'));
        if (($result['tipe'] ?? '') === 'success') {
            tracelog('UPDATE', 'CLOSING ULANG ' . $period . ' ' . json_encode($result, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES));
        }

        return $this->response->setJSON($result);
    }

    public function cli()
    {
        if (!is_cli()) {
            return $this->response->setJSON(['tipe' => 'error', 'data' => 'Endpoint ini hanya untuk CLI'])->setStatusCode(404);
        }

        $results = $this->closingModel->closeAllDueStores('CLI');
        foreach ($results as $row) {
            echo ($row['toko_id'] ?? '-') . ' : ' . ($row['tipe'] ?? '-') . ' - ' . ($row['data'] ?? '-') . PHP_EOL;
        }
    }

    private function hasAccess(string $akses): bool
    {
        $db = Database::connect();
        $row = $db->query(
            "SELECT * FROM akses_menu WHERE level_id=:level_id: AND menu_id='closing' LIMIT 1",
            ['level_id' => session('level_id')]
        )->getRowArray();

        return !empty($row[$akses]) && $row[$akses] === 'Y';
    }
}
