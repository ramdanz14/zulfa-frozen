<?php

namespace App\Controllers;

use App\Controllers\BaseController;
use App\Models\StockModel;

class Stock extends BaseController
{
    protected StockModel $stockModel;

    public function __construct()
    {
        $this->stockModel = new StockModel();
    }

    public function index()
    {
        $jenis = trim((string) ($this->request->getGet('jenis') ?? 'qty'));
        $jenis = $jenis === 'qty' ? 'qty' : 'rupiah';

        $data['title'] = 'Laporan Stock';
        $data['initialJenis'] = $jenis;
        $data['initialUrutan'] = trim((string) ($this->request->getGet('urutan') ?? 'saldo')) === 'kategori' ? 'kategori' : 'saldo';
        $data['summary'] = $this->stockModel->getSummary((string) session('toko_id'), $jenis);

        cek_akses_menu('stock/index', $data);
    }

    public function ajax()
    {
        if (!$this->hasMenuAccess('akses_read')) {
            return $this->response->setJSON([
                'data' => [],
                'draw' => (int) ($this->request->getVar('draw') ?? 0),
                'recordsTotal' => 0,
                'recordsFiltered' => 0,
                'summary' => $this->stockModel->getSummary((string) session('toko_id')),
                'message' => 'Tidak memiliki akses membaca laporan stock',
            ])->setStatusCode(400);
        }

        $jenis = trim((string) ($this->request->getVar('jenis') ?? 'rupiah'));
        $urutan = trim((string) ($this->request->getVar('urutan') ?? 'saldo'));

        $result = $this->stockModel->ajaxList(
            [
                'start' => $this->request->getVar('start'),
                'length' => $this->request->getVar('length'),
                'search_value' => $this->request->getVar('search')['value'] ?? '',
            ],
            (string) session('toko_id'),
            $jenis,
            $urutan
        );

        return $this->response->setJSON([
            'draw' => (int) ($this->request->getVar('draw') ?? 0),
            'recordsTotal' => $result['total_count'],
            'recordsFiltered' => $result['total_filtered'],
            'data' => $result['data'],
            'summary' => $result['summary'],
        ]);
    }

    public function history(string $kodeItem)
    {
        if (!$this->hasMenuAccess('akses_read')) {
            return $this->response->setJSON([
                'tipe' => 'error',
                'data' => 'Tidak memiliki akses membaca history stock',
            ])->setStatusCode(400);
        }

        $kodeItem = trim($kodeItem);
        if ($kodeItem === '') {
            return $this->response->setJSON([
                'tipe' => 'error',
                'data' => 'Kode item tidak valid',
            ])->setStatusCode(400);
        }

        return $this->response->setJSON([
            'tipe' => 'success',
            'data' => $this->stockModel->getItemHistory((string) session('toko_id'), $kodeItem),
        ]);
    }

    public function recalculate()
    {
        if (!$this->hasMenuAccess('akses_update')) {
            return $this->response->setJSON([
                'tipe' => 'error',
                'data' => 'Tidak memiliki akses hitung ulang stock',
            ])->setStatusCode(400);
        }

        if ((session('toko_id') ?? '') === '') {
            return $this->response->setJSON([
                'tipe' => 'error',
                'data' => 'Toko aktif tidak ditemukan',
            ])->setStatusCode(400);
        }

        HitungStock((string) session('toko_id'));
        HitungSpd((string) session('toko_id'));
        tracelog('UPDATE', 'HITUNG ULANG STOCK TOKO ' . (string) session('toko_id'));

        return $this->response->setJSON([
            'tipe' => 'success',
            'data' => 'Hitung ulang stock dan SPD berhasil dijalankan',
        ]);
    }

    private function hasMenuAccess(string $akses): bool
    {
        $aksesMenu = GetAkseMenu((string) session('level_id'), 'stock');
        if (!$aksesMenu) {
            return false;
        }

        return (($aksesMenu->$akses ?? 'N') === 'Y');
    }
}
