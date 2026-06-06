<?php

namespace App\Controllers;

use App\Controllers\BaseController;
use App\Models\BapModel;

class Bap extends BaseController
{
    protected BapModel $bapModel;

    public function __construct()
    {
        $this->bapModel = new BapModel();
    }

    public function index()
    {
        $data['title'] = 'Berita Acara Pemusnahan';
        $data['closingDate'] = $this->bapModel->getClosingDate((string) session('toko_id'));
        cek_akses_menu('bap/index', $data);
    }

    public function add()
    {
        $data['title'] = 'Tambah BAP';
        $data['mode'] = 'create';
        $data['formData'] = $this->bapModel->getFormData((string) session('toko_id'));
        cek_akses_menu('bap/form', $data, 'akses_create');
    }

    public function edit(string $bapId)
    {
        $tokoId = (string) session('toko_id');
        if ($this->bapModel->isLocked($tokoId, $bapId)) {
            return redirect()->to('/bap')->with('error', 'Dokumen BAP yang sudah melewati periode closing tidak boleh diedit');
        }

        $data['title'] = 'Edit BAP';
        $data['mode'] = 'edit';
        $data['formData'] = $this->bapModel->getFormData($tokoId, $bapId);
        if (empty($data['formData']['header']['bap_id'])) {
            return redirect()->to('/bap')->with('error', 'Dokumen BAP tidak ditemukan');
        }
        cek_akses_menu('bap/form', $data, 'akses_update');
    }

    public function ajax()
    {
        $result = $this->bapModel->ajaxList(
            [
                'start' => $this->request->getVar('start'),
                'length' => $this->request->getVar('length'),
                'search_value' => $this->request->getVar('search')['value'] ?? '',
            ],
            (string) session('toko_id')
        );

        return $this->response->setJSON([
            'draw' => (int) ($this->request->getVar('draw') ?? 0),
            'recordsTotal' => $result['total_count'],
            'recordsFiltered' => $result['total_filtered'],
            'data' => $result['data'],
        ]);
    }

    public function searchItem()
    {
        $term = trim((string) $this->request->getGet('term'));
        return $this->response->setJSON([
            'results' => array_map(static function (array $row): array {
                return [
                    'id' => $row['kode_item'],
                    'text' => trim(($row['kode_item'] ?? '') . ' - ' . ($row['nama_item'] ?? '')),
                ];
            }, $this->bapModel->searchItems((string) session('toko_id'), $term)),
        ]);
    }

    public function itemDetail(string $kodeItem)
    {
        $item = $this->bapModel->getItemPayload((string) session('toko_id'), $kodeItem);
        if (!$item) {
            return $this->response->setJSON(['tipe' => 'error', 'data' => 'Item tidak ditemukan']);
        }

        return $this->response->setJSON(['tipe' => 'success', 'data' => $item]);
    }

    public function store()
    {
        $payload = $this->request->getRawInput();
        $result = $this->bapModel->saveDocument((string) session('toko_id'), (string) session('username'), $payload, 'create');
        if (($result['tipe'] ?? '') === 'success') {
            tracelog('CREATE', 'CREATE BAP ' . ($result['bap_id'] ?? '') . ' payload=' . json_encode($payload, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES));
        }
        return $this->response->setJSON($result);
    }

    public function update()
    {
        $payload = $this->request->getRawInput();
        $result = $this->bapModel->saveDocument((string) session('toko_id'), (string) session('username'), $payload, 'edit');
        if (($result['tipe'] ?? '') === 'success') {
            tracelog('UPDATE', 'UPDATE BAP ' . ($result['bap_id'] ?? ($payload['bap_id'] ?? '')) . ' payload=' . json_encode($payload, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES));
        }
        return $this->response->setJSON($result);
    }

    public function delete()
    {
        $payload = $this->request->getRawInput();
        $result = $this->bapModel->deleteDocument((string) session('toko_id'), trim((string) ($payload['bap_id'] ?? '')));
        if (($result['tipe'] ?? '') === 'success') {
            tracelog('DELETE', 'DELETE BAP ' . trim((string) ($payload['bap_id'] ?? '')));
        }
        return $this->response->setJSON($result);
    }

    public function show(string $bapId)
    {
        $data = $this->bapModel->getDocumentSummary((string) session('toko_id'), $bapId);
        if (!$data) {
            return $this->response->setJSON(['tipe' => 'error', 'data' => 'Dokumen BAP tidak ditemukan']);
        }

        return $this->response->setJSON(['tipe' => 'success', 'data' => $data]);
    }

    public function print(string $bapId)
    {
        $data['title'] = 'Cetak BAP';
        $data['document'] = $this->bapModel->getDocumentSummary((string) session('toko_id'), $bapId);
        if (!$data['document']) {
            return redirect()->to('/bap')->with('error', 'Dokumen BAP tidak ditemukan');
        }
        $data['isMobile'] = cekMobile();
        return view('bap/print', $data);
    }
}
