<?php

namespace App\Controllers;

use App\Controllers\BaseController;
use App\Models\HistoryBeliModel;

class Historybeli extends BaseController
{
    protected HistoryBeliModel $historyBeliModel;

    public function __construct()
    {
        $this->historyBeliModel = new HistoryBeliModel();
    }

    public function index()
    {
        $data['title'] = 'History Perubahan Harga';
        cek_akses_menu('historybeli/index', $data);
    }

    public function ajax()
    {
        $draw = $this->request->getVar('draw');
        $params = [
            'start' => $this->request->getVar('start'),
            'length' => $this->request->getVar('length'),
            'search_value' => $this->request->getVar('search')['value'] ?? '',
            'jenis' => $this->request->getVar('jenis') ?? '',
        ];

        $hasil = $this->historyBeliModel->ajaxList($params, (string) session('toko_id'));

        return $this->response->setJSON([
            'draw' => (int) $draw,
            'recordsTotal' => $hasil['total_count'],
            'recordsFiltered' => $hasil['total_filtered'],
            'data' => $hasil['data'],
        ]);
    }
}
