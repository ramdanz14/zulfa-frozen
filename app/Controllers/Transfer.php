<?php

namespace App\Controllers;

use App\Controllers\BaseController;
use App\Models\TransferModel;

class Transfer extends BaseController
{
    protected TransferModel $transferModel;

    public function __construct()
    {
        $this->transferModel = new TransferModel();
    }

    public function index()
    {
        $tokoId = (string) session('toko_id');
        $context = $this->transferModel->getStoreContext($tokoId);
        $data['title'] = 'Transfer Antar Toko';
        $data['context'] = $context;
        cek_akses_menu('transfer/index', $data);
    }

    public function ajaxPo()
    {
        $params = [
            'start' => $this->request->getVar('start'),
            'length' => $this->request->getVar('length'),
            'search_value' => $this->request->getVar('search')['value'] ?? '',
        ];

        $hasil = $this->transferModel->ajaxPendingPo($params, (string) session('toko_id'));
        return $this->response->setJSON([
            'draw' => (int) ($this->request->getVar('draw') ?? 0),
            'recordsTotal' => $hasil['total_count'],
            'recordsFiltered' => $hasil['total_filtered'],
            'data' => $hasil['data'],
        ]);
    }

    public function ajax()
    {
        $tokoId = (string) session('toko_id');
        $context = $this->transferModel->getStoreContext($tokoId);
        $params = [
            'start' => $this->request->getVar('start'),
            'length' => $this->request->getVar('length'),
            'search_value' => $this->request->getVar('search')['value'] ?? '',
        ];

        $hasil = $this->transferModel->ajaxTransfers($params, $tokoId, (bool) ($context['is_gudang'] ?? false));
        return $this->response->setJSON([
            'draw' => (int) ($this->request->getVar('draw') ?? 0),
            'recordsTotal' => $hasil['total_count'],
            'recordsFiltered' => $hasil['total_filtered'],
            'data' => $hasil['data'],
        ]);
    }

    public function add(string $poTokoId, string $poBeliId)
    {
        $data['title'] = 'Draft Kirim Transfer';
        $data['mode'] = 'create';
        $data['formData'] = $this->transferModel->getDraftFromPo((string) session('toko_id'), $poTokoId, $poBeliId);
        if (empty($data['formData']['header']['transfer_id'])) {
            return redirect()->to('/transfer')->with('error', 'PO cabang tidak ditemukan atau sudah diproses');
        }
        cek_akses_menu('transfer/form', $data, 'akses_create');
    }

    public function edit(string $transferId)
    {
        $data['title'] = 'Edit Draft Kirim Transfer';
        $data['mode'] = 'edit';
        $data['formData'] = $this->transferModel->getFormData((string) session('toko_id'), $transferId);
        if (empty($data['formData']['header']['transfer_id'])) {
            return redirect()->to('/transfer')->with('error', 'Draft transfer tidak ditemukan');
        }
        cek_akses_menu('transfer/form', $data, 'akses_update');
    }

    public function show(string $transferId)
    {
        $tokoId = (string) session('toko_id');
        $context = $this->transferModel->getStoreContext($tokoId);
        $data = $this->transferModel->getTransferSummary($transferId, $tokoId, (bool) ($context['is_gudang'] ?? false));
        if (! $data) {
            return $this->response->setJSON(['tipe' => 'error', 'data' => 'Data transfer tidak ditemukan']);
        }
        return $this->response->setJSON(['tipe' => 'success', 'data' => $data]);
    }

    public function searchItem()
    {
        $term = trim((string) $this->request->getGet('term'));
        if ($term === '') {
            return $this->response->setJSON(['results' => []]);
        }

        $items = $this->transferModel->searchItems((string) session('toko_id'), $term);
        $results = array_map(static function ($row) {
            return [
                'id' => $row['kode_item'],
                'text' => trim($row['kode_item'] . ' - ' . $row['nama_item']),
            ];
        }, $items);

        return $this->response->setJSON(['results' => $results]);
    }

    public function itemDetail(string $kodeItem)
    {
        $item = $this->transferModel->getItemPayload((string) session('toko_id'), $kodeItem);
        if (! $item) {
            return $this->response->setJSON(['tipe' => 'error', 'data' => 'Item gudang tidak ditemukan']);
        }
        return $this->response->setJSON(['tipe' => 'success', 'data' => $item]);
    }

    public function store()
    {
        $payload = $this->request->getVar();
        $result = $this->transferModel->saveDraft((string) session('toko_id'), (string) session('username'), $payload, 'create');
        if (($result['tipe'] ?? '') === 'success') {
            tracelog('CREATE', 'CREATE DRAFT TRANSFER ' . ($result['transfer_id'] ?? '') . ' payload=' . json_encode($payload, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES));
        }
        return $this->response->setJSON($result);
    }

    public function update()
    {
        $payload = $this->request->getVar();
        $result = $this->transferModel->saveDraft((string) session('toko_id'), (string) session('username'), $payload, 'edit');
        if (($result['tipe'] ?? '') === 'success') {
            tracelog('UPDATE', 'UPDATE DRAFT TRANSFER ' . ($result['transfer_id'] ?? ($payload['transfer_id'] ?? '')) . ' payload=' . json_encode($payload, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES));
        }
        return $this->response->setJSON($result);
    }

    public function send(string $transferId)
    {
        $result = $this->transferModel->sendTransfer((string) session('toko_id'), (string) session('username'), $transferId);
        if (($result['tipe'] ?? '') === 'success') {
            tracelog('UPDATE', 'KIRIM TRANSFER ANTAR TOKO ' . $transferId . ' jual_id=' . ($result['jual_id'] ?? ''));
        }
        return $this->response->setJSON($result);
    }

    public function approve(string $transferId)
    {
        $checkedSeqs = json_decode((string) $this->request->getVar('checked_seqs'), true) ?: [];
        $result = $this->transferModel->approveTransfer((string) session('toko_id'), (string) session('username'), $transferId, $checkedSeqs);
        if (($result['tipe'] ?? '') === 'success') {
            tracelog('UPDATE', 'APPROVE TRANSFER ANTAR TOKO ' . $transferId . ' beli_id=' . ($result['beli_id'] ?? ''));
        }
        return $this->response->setJSON($result);
    }

    public function reject(string $transferId)
    {
        $result = $this->transferModel->rejectTransfer((string) session('toko_id'), (string) session('username'), $transferId);
        if (($result['tipe'] ?? '') === 'success') {
            tracelog('DELETE', 'REJECT TRANSFER ANTAR TOKO ' . $transferId);
        }
        return $this->response->setJSON($result);
    }
}
