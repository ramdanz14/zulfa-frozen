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
        $data['supplierOptions'] = $this->pembelianModel->getSupplierOptions();
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

    public function saldoForm(?string $beli_id = null)
    {
        $data = $this->pembelianModel->getSaldoHutangFormData((string) session('toko_id'), $beli_id);
        if ($beli_id !== null && empty($data['header']['beli_id'])) {
            return $this->response->setJSON(['tipe' => 'error', 'data' => 'Data saldo hutang awal tidak ditemukan']);
        }

        return $this->response->setJSON(['tipe' => 'success', 'data' => $data]);
    }

    public function storeSaldo()
    {
        $payload = $this->request->getRawInput();
        $result = $this->pembelianModel->saveSaldoHutangAwal(
            (string) session('toko_id'),
            (string) session('username'),
            $payload,
            'create'
        );

        if (($result['tipe'] ?? '') === 'success') {
            tracelog('CREATE', 'Create saldo hutang awal dengan ID : ' . ($result['beli_id'] ?? '') . ' payload=' . json_encode($payload, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES));
        }

        return $this->response->setJSON($result);
    }

    public function updateSaldo()
    {
        $payload = $this->request->getRawInput();
        $result = $this->pembelianModel->saveSaldoHutangAwal(
            (string) session('toko_id'),
            (string) session('username'),
            $payload,
            'edit'
        );

        if (($result['tipe'] ?? '') === 'success') {
            tracelog('UPDATE', 'Update saldo hutang awal dengan ID : ' . ($result['beli_id'] ?? $this->request->getVar('beli_id')) . ' payload=' . json_encode($payload, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES));
        }

        return $this->response->setJSON($result);
    }

    public function deleteSaldo()
    {
        $input = $this->request->getRawInput();
        $beliId = trim((string) $input['beli_id']);
        $result = $this->pembelianModel->deleteSaldoHutangAwal(
            (string) session('toko_id'),
            $beliId
        );

        if (($result['tipe'] ?? '') === 'success') {
            tracelog('DELETE', 'Delete saldo hutang awal dengan ID : ' . $beliId);
        }

        return $this->response->setJSON($result);
    }
}
