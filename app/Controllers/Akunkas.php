<?php

namespace App\Controllers;

use App\Controllers\BaseController;
use App\Models\AkunkasModel;

class Akunkas extends BaseController
{
    protected AkunkasModel $akunkasModel;

    public function __construct()
    {
        $this->akunkasModel = new AkunkasModel();
    }

    public function index()
    {
        $data['title'] = 'Akun Kas';
        cek_akses_menu('akunkas/index', $data);
    }

    public function ajax()
    {
        $result = $this->akunkasModel->ajax([
            'start' => $this->request->getVar('start'),
            'length' => $this->request->getVar('length'),
            'search_value' => $this->request->getVar('search')['value'] ?? '',
        ]);

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
        $result = $this->akunkasModel->saveAccount((string) session('username'), $payload, 'create');
        if (($result['tipe'] ?? '') === 'success') {
            tracelog('CREATE', 'CREATE AKUN KAS ' . strtoupper(trim((string) ($payload['nama_akun'] ?? ''))));
        }
        return $this->response->setJSON($result);
    }

    public function update()
    {
        $payload = $this->request->getRawInput();
        $result = $this->akunkasModel->saveAccount((string) session('username'), $payload, 'edit');
        if (($result['tipe'] ?? '') === 'success') {
            tracelog('UPDATE', 'UPDATE AKUN KAS ' . strtoupper(trim((string) ($payload['old_nama_akun'] ?? ''))) . ' -> ' . strtoupper(trim((string) ($payload['nama_akun'] ?? ''))));
        }
        return $this->response->setJSON($result);
    }

    public function delete()
    {
        $payload = $this->request->getRawInput();
        $namaAkun = trim((string) ($payload['nama_akun'] ?? ''));
        $result = $this->akunkasModel->deleteAccount($namaAkun);
        if (($result['tipe'] ?? '') === 'success') {
            tracelog('DELETE', 'DELETE AKUN KAS ' . strtoupper($namaAkun));
        }
        return $this->response->setJSON($result);
    }
}
