<?php

namespace App\Controllers;

use App\Controllers\BaseController;
use App\Models\PembelianModel;

class Hutang extends BaseController
{
    protected PembelianModel $pembelianModel;

    public function __construct()
    {
        $this->pembelianModel = new PembelianModel();
    }

    public function index()
    {
        $data['title'] = 'Monitoring Hutang Supplier';
        cek_akses_menu('hutang/index', $data);
    }

    public function ajax()
    {
        $draw = $this->request->getVar('draw');
        $params = [
            'start' => $this->request->getVar('start'),
            'length' => $this->request->getVar('length'),
            'search_value' => $this->request->getVar('search')['value'] ?? '',
        ];
        $filter = strtoupper(trim((string) $this->request->getVar('status_filter')));
        if (!in_array($filter, ['BELUM', 'LUNAS', 'ALL'], true)) {
            $filter = 'BELUM';
        }

        $hasil = $this->pembelianModel->ajaxHutang($params, (string) session('toko_id'), $filter);
        return $this->response->setJSON([
            'draw' => (int) $draw,
            'recordsTotal' => $hasil['total_count'],
            'recordsFiltered' => $hasil['total_filtered'],
            'data' => $hasil['data'],
        ]);
    }
}
