<?php

namespace App\Controllers;

use App\Controllers\BaseController;
use App\Models\KasModel;

class Kas extends BaseController
{
    protected KasModel $kasModel;

    public function __construct()
    {
        $this->kasModel = new KasModel();
    }

    public function index()
    {
        $data['title'] = 'Kas Masuk / Keluar';
        $data['akunOptions'] = $this->kasModel->getAkunOptions();
        $data['karyawanOptions'] = $this->kasModel->getKaryawanOptions((string) session('toko_id'));
        cek_akses_menu('kas/index', $data);
    }

    public function ajax()
    {
        $result = $this->kasModel->ajax([
            'start' => $this->request->getVar('start'),
            'length' => $this->request->getVar('length'),
            'search_value' => $this->request->getVar('search')['value'] ?? '',
        ], (string) session('toko_id'));

        return $this->response->setJSON([
            'draw' => (int) ($this->request->getVar('draw') ?? 0),
            'recordsTotal' => $result['total_count'],
            'recordsFiltered' => $result['total_filtered'],
            'data' => $result['data'],
        ]);
    }

    public function store()
    {
        $payload = $this->request->getRawInput();
        $result = $this->kasModel->saveMutation((string) session('toko_id'), (string) session('username'), $payload, 'create');
        if (($result['tipe'] ?? '') === 'success') {
            tracelog('CREATE', 'CREATE KAS MUTASI payload=' . json_encode($payload, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES));
        }
        return $this->response->setJSON($result);
    }

    public function update()
    {
        $payload = $this->request->getRawInput();
        $result = $this->kasModel->saveMutation((string) session('toko_id'), (string) session('username'), $payload, 'edit');
        if (($result['tipe'] ?? '') === 'success') {
            tracelog('UPDATE', 'UPDATE KAS MUTASI payload=' . json_encode($payload, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES));
        }
        return $this->response->setJSON($result);
    }

    public function delete()
    {
        $payload = $this->request->getRawInput();
        $kasId = (int) ($payload['kas_id'] ?? 0);
        $result = $this->kasModel->deleteMutation((string) session('toko_id'), $kasId);
        if (($result['tipe'] ?? '') === 'success') {
            tracelog('DELETE', 'DELETE KAS MUTASI kas_id=' . $kasId);
        }
        return $this->response->setJSON($result);
    }
}
