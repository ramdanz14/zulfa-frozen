<?php

namespace App\Controllers;

use App\Controllers\BaseController;
use App\Models\ReturBeliModel;

class ReturBeli extends BaseController
{
    protected ReturBeliModel $returBeliModel;

    public function __construct()
    {
        $this->returBeliModel = new ReturBeliModel();
    }

    public function index()
    {
        $data['title'] = 'Retur Pembelian';
        $data['closingDate'] = $this->returBeliModel->getClosingDate((string) session('toko_id'));
        cek_akses_menu('returbeli/index', $data);
    }

    public function add()
    {
        $tokoId = (string) session('toko_id');
        $data['title'] = 'Tambah Retur Pembelian';
        $data['mode'] = 'create';
        $data['supplierOptions'] = $this->returBeliModel->getSupplierOptions($tokoId);
        $data['formData'] = $this->returBeliModel->getFormData($tokoId);
        cek_akses_menu('returbeli/form', $data, 'akses_create');
    }

    public function edit(string $retur_id)
    {
        $tokoId = (string) session('toko_id');
        if ($this->returBeliModel->isLockedRetur($tokoId, $retur_id)) {
            return redirect()->to('/returbeli')->with('error', 'Retur SELESAI yang sudah melewati periode closing tidak boleh diedit');
        }

        $formData = $this->returBeliModel->getFormData($tokoId, $retur_id);
        if (empty($formData['header']['retur_id'])) {
            return redirect()->to('/returbeli');
        }

        $data['title'] = 'Edit Retur Pembelian';
        $data['mode'] = 'edit';
        $data['supplierOptions'] = $this->returBeliModel->getSupplierOptions($tokoId, (string) ($formData['header']['supco'] ?? ''));
        $data['formData'] = $formData;
        cek_akses_menu('returbeli/form', $data, 'akses_update');
    }

    public function ajax()
    {
        $draw = $this->request->getVar('draw');
        $params = [
            'start' => $this->request->getVar('start'),
            'length' => $this->request->getVar('length'),
            'search_value' => $this->request->getVar('search')['value'] ?? '',
        ];
        $hasil = $this->returBeliModel->ajaxList($params, (string) session('toko_id'));
        return $this->response->setJSON([
            'draw' => (int) $draw,
            'recordsTotal' => $hasil['total_count'],
            'recordsFiltered' => $hasil['total_filtered'],
            'data' => $hasil['data'],
        ]);
    }

    public function source(string $supco)
    {
        $returId = trim((string) $this->request->getGet('retur_id'));
        $statusRetur = strtoupper(trim((string) $this->request->getGet('status_retur')));
        $beliId = trim((string) $this->request->getGet('beli_id'));
        $data = $this->returBeliModel->getSupplierItemPayload(
            (string) session('toko_id'),
            $supco,
            $returId !== '' ? $returId : null,
            $statusRetur === 'SELESAI',
            $beliId !== '' ? $beliId : null
        );
        if (!$data) {
            return $this->response->setJSON(['tipe' => 'error', 'data' => 'Data supplier retur tidak ditemukan']);
        }
        return $this->response->setJSON(['tipe' => 'success', 'data' => $data]);
    }

    public function searchItem()
    {
        $term = trim((string) $this->request->getGet('term'));
        if ($term === '') {
            return $this->response->setJSON(['results' => []]);
        }
        $items = $this->returBeliModel->searchItems((string) session('toko_id'), $term);
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
        $item = $this->returBeliModel->getItemPayload((string) session('toko_id'), $kode_item);
        if (!$item) {
            return $this->response->setJSON(['tipe' => 'error', 'data' => 'Item tidak ditemukan']);
        }
        return $this->response->setJSON(['tipe' => 'success', 'data' => $item]);
    }

    public function show(string $retur_id)
    {
        $data = $this->returBeliModel->getReturSummary((string) session('toko_id'), $retur_id);
        if (!$data) {
            return $this->response->setJSON(['tipe' => 'error', 'data' => 'Data retur tidak ditemukan']);
        }
        return $this->response->setJSON(['tipe' => 'success', 'data' => $data]);
    }

    public function store()
    {
        $payload = $this->request->getVar();
        $result = $this->returBeliModel->saveRetur(
            (string) session('toko_id'),
            (string) session('username'),
            $payload,
            'create'
        );
        if (($result['tipe'] ?? '') === 'success') {
            tracelog('CREATE', 'Create retur pembelian dengan ID : ' . ($result['retur_id'] ?? '') . ' payload=' . json_encode($payload, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES));
        }
        return $this->response->setJSON($result);
    }

    public function update()
    {
        $payload = $this->request->getVar();
        $result = $this->returBeliModel->saveRetur(
            (string) session('toko_id'),
            (string) session('username'),
            $payload,
            'edit'
        );
        if (($result['tipe'] ?? '') === 'success') {
            tracelog('UPDATE', 'Update retur pembelian dengan ID : ' . ($result['retur_id'] ?? ($payload['retur_id'] ?? '')) . ' payload=' . json_encode($payload, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES));
        }
        return $this->response->setJSON($result);
    }

    public function delete()
    {
        $returId = trim((string) $this->request->getVar('retur_id'));
        if ($this->returBeliModel->isLockedRetur((string) session('toko_id'), $returId)) {
            return $this->response->setJSON(['tipe' => 'error', 'data' => 'Retur SELESAI yang melewati periode closing tidak boleh dihapus']);
        }
        $result = $this->returBeliModel->deleteRetur((string) session('toko_id'), $returId);
        if (($result['tipe'] ?? '') === 'success') {
            tracelog('DELETE', 'Delete retur pembelian dengan ID : ' . $returId);
        }
        return $this->response->setJSON($result);
    }
}
