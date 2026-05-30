<?php

namespace App\Controllers;

use App\Models\TokoModel;
use App\Models\UserModel;

class Tokoaktif extends BaseController
{
    protected TokoModel $tokoModel;
    protected UserModel $userModel;

    public function __construct()
    {
        $this->tokoModel = new TokoModel();
        $this->userModel = new UserModel();
    }

    public function index()
    {
        $data = [
            'title' => 'Toko Aktif',
            'tokoList' => $this->tokoModel->getSwitcherList(),
            'activeTokoId' => (string) session('toko_id'),
        ];

        cek_akses_menu('tokoaktif', $data);
    }

    public function switch()
    {
        if (! session('username')) {
            return $this->response->setStatusCode(401)->setJSON([
                'tipe' => 'error',
                'data' => 'Session login tidak ditemukan.',
            ]);
        }

        $tokoId = trim((string) ($this->request->getPost('toko_id') ?? ''));
        $activeTokoId = (string) session('toko_id');

        if ($tokoId === '') {
            return $this->response->setStatusCode(422)->setJSON([
                'tipe' => 'error',
                'data' => 'Toko yang dipilih tidak valid.',
            ]);
        }

        if ($tokoId === $activeTokoId) {
            return $this->response->setStatusCode(422)->setJSON([
                'tipe' => 'error',
                'data' => 'Toko tersebut sudah aktif.',
            ]);
        }

        $toko = $this->tokoModel->getById($tokoId);
        if ($toko === null) {
            return $this->response->setStatusCode(404)->setJSON([
                'tipe' => 'error',
                'data' => 'Data toko tidak ditemukan.',
            ]);
        }

        $username = (string) session('username');
        $db = \Config\Database::connect();
        $db->transStart();

        $updated = $this->userModel
            ->where('username', $username)
            ->set(['toko_id' => $toko['toko_id']])
            ->update();

        if ($updated) {
            tracelog('UPDATE', 'Switch toko user ' . $username . ' ke ' . $toko['toko_id'] . ' - ' . $toko['toko_nama']);
        }

        $db->transComplete();

        if (! $updated || $db->transStatus() === false) {
            return $this->response->setStatusCode(500)->setJSON([
                'tipe' => 'error',
                'data' => 'Gagal mengubah toko aktif.',
            ]);
        }

        session()->set([
            'toko_id' => $toko['toko_id'],
            'toko_nama' => $toko['toko_nama'],
            'toko_theme' => $toko['toko_theme'],
        ]);

        return $this->response->setJSON([
            'tipe' => 'success',
            'data' => 'Toko aktif berhasil diubah.',
        ]);
    }
}
