<?php

namespace App\Controllers;

use App\Controllers\BaseController;
use App\Models\PembelianModel;

class Pembelian extends BaseController
{
    protected PembelianModel $pembelianModel;

    public function __construct()
    {
        $this->pembelianModel = new PembelianModel();
    }

    public function index()
    {
        $data['title'] = 'Pembelian';
        $data['closingDate'] = $this->pembelianModel->getClosingDate((string) session('toko_id'));
        cek_akses_menu('pembelian/index', $data);
    }

    public function add()
    {
        $data['title'] = 'Tambah Pembelian';
        $data['mode'] = 'create';
        $data['supplierOptions'] = $this->pembelianModel->getSupplierOptions();
        $data['formData'] = $this->pembelianModel->getFormData((string) session('toko_id'));
        cek_akses_menu('pembelian/form', $data, 'akses_create');
    }

    public function edit(string $beli_id)
    {
        $tokoId = (string) session('toko_id');
        if ($this->pembelianModel->isLockedTerima($tokoId, $beli_id)) {
            return redirect()->to('/pembelian')->with('error', 'Transaksi TERIMA yang sudah melewati periode closing tidak boleh diedit');
        }
        $data['title'] = 'Edit Pembelian';
        $data['mode'] = 'edit';
        $data['supplierOptions'] = $this->pembelianModel->getSupplierOptions();
        $data['formData'] = $this->pembelianModel->getFormData($tokoId, $beli_id);
        if (empty($data['formData']['header']['beli_id'])) {
            return redirect()->to('/pembelian');
        }
        cek_akses_menu('pembelian/form', $data, 'akses_update');
    }

    public function ajax()
    {
        $draw = $this->request->getVar('draw');
        $params = [
            'start' => $this->request->getVar('start'),
            'length' => $this->request->getVar('length'),
            'search_value' => $this->request->getVar('search')['value'] ?? '',
        ];
        $hasil = $this->pembelianModel->ajaxList($params, (string) session('toko_id'));
        return $this->response->setJSON([
            'draw' => (int) $draw,
            'recordsTotal' => $hasil['total_count'],
            'recordsFiltered' => $hasil['total_filtered'],
            'data' => $hasil['data'],
        ]);
    }

    public function searchItem()
    {
        $term = trim((string) $this->request->getGet('term'));
        if ($term === '') {
            return $this->response->setJSON(['results' => []]);
        }
        $items = $this->pembelianModel->searchItems((string) session('toko_id'), $term);
        $results = array_map(static function ($row) {
            return [
                'id' => $row['kode_item'],
                'text' => trim($row['kode_item'] . ' - ' . $row['nama_item']),
            ];
        }, $items);

        return $this->response->setJSON(['results' => $results]);
    }

    public function itemDetail(string $kode_item)
    {
        $item = $this->pembelianModel->getItemPayload((string) session('toko_id'), $kode_item);
        if (!$item) {
            return $this->response->setJSON(['tipe' => 'error', 'data' => 'Item tidak ditemukan']);
        }
        return $this->response->setJSON(['tipe' => 'success', 'data' => $item]);
    }

    public function store()
    {
        $payload = $this->request->getVar();
        if ($payload['tanggal'] < $this->pembelianModel->getClosingDate(session('toko_id'))) {
            return $this->response->setJSON(['tipe' => 'error', 'data' => "Tanggal yang di input {$payload['tanggal']} sudah melewati periode closing"]);
        }
        $result = $this->pembelianModel->savePurchase(
            (string) session('toko_id'),
            (string) session('username'),
            $payload,
            'create'
        );
        if (($result['tipe'] ?? '') === 'success') {
            tracelog('CREATE', 'Create pembelian dengan ID : ' . ($result['beli_id'] ?? '') . ' payload=' . json_encode($payload, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES));
        }
        return $this->response->setJSON($result);
    }

    public function update()
    {
        $payload = $this->request->getVar();
        $result = $this->pembelianModel->savePurchase(
            (string) session('toko_id'),
            (string) session('username'),
            $payload,
            'edit'
        );
        if (($result['tipe'] ?? '') === 'success') {
            tracelog('UPDATE', 'Update pembelian dengan ID : ' . ($result['beli_id'] ?? $this->request->getVar('beli_id')) . ' payload=' . json_encode($payload, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES));
        }
        return $this->response->setJSON($result);
    }

    public function delete()
    {
        $tokoId = (string) session('toko_id');
        $beli_id = $this->request->getVar('beli_id');
        if ($this->pembelianModel->isLockedTerima($tokoId, $beli_id)) {
            return $this->response->setJSON(['tipe' => 'error', 'data' => 'Transaksi pembelian dengan status TERIMA yang melewati periode closing tidak boleh di hapus']);
        }
        $result = $this->pembelianModel->deletePurchase(
            (string) session('toko_id'),
            trim((string) $this->request->getVar('beli_id'))
        );
        if (($result['tipe'] ?? '') === 'success') {
            tracelog('DELETE', 'Delete pembelian dengan ID : ' . $this->request->getVar('beli_id'));
        }
        return $this->response->setJSON($result);
    }

    public function show(string $beli_id)
    {
        $data = $this->pembelianModel->getPurchaseSummary((string) session('toko_id'), $beli_id);
        if (!$data) {
            return $this->response->setJSON(['tipe' => 'error', 'data' => 'Data pembelian tidak ditemukan']);
        }
        return $this->response->setJSON(['tipe' => 'success', 'data' => $data]);
    }

    public function history(string $beli_id)
    {
        $data = $this->pembelianModel->getPaymentHistory((string) session('toko_id'), $beli_id);
        return $this->response->setJSON(['tipe' => 'success', 'data' => $data]);
    }

    public function pay(string $beli_id)
    {
        $payments = json_decode((string) $this->request->getVar('payment_json'), true) ?: [];
        $result = $this->pembelianModel->addPayment(
            (string) session('toko_id'),
            $beli_id,
            (string) session('username'),
            $payments
        );
        if (($result['tipe'] ?? '') === 'success') {
            tracelog('UPDATE', 'Tambah pembayaran pembelian : ' . $beli_id);
        }
        return $this->response->setJSON($result);
    }
}
