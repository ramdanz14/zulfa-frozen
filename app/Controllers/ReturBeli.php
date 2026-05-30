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
        $data['purchaseOptions'] = $this->returBeliModel->getEligiblePurchaseOptions($tokoId);
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
        $data['purchaseOptions'] = $this->returBeliModel->getEligiblePurchaseOptions($tokoId, (string) ($formData['header']['beli_id'] ?? ''));
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

    public function source(string $beli_id)
    {
        $returId = trim((string) $this->request->getGet('retur_id'));
        $statusRetur = strtoupper(trim((string) $this->request->getGet('status_retur')));
        $data = $this->returBeliModel->getSourcePurchasePayload(
            (string) session('toko_id'),
            $beli_id,
            $returId !== '' ? $returId : null,
            $statusRetur === 'SELESAI'
        );
        if (!$data) {
            return $this->response->setJSON(['tipe' => 'error', 'data' => 'Data pembelian asal tidak ditemukan']);
        }
        return $this->response->setJSON(['tipe' => 'success', 'data' => $data]);
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
