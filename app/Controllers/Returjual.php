<?php

namespace App\Controllers;

use App\Controllers\BaseController;
use App\Models\ReturJualModel;

class Returjual extends BaseController
{
    protected ReturJualModel $returJualModel;

    public function __construct()
    {
        $this->returJualModel = new ReturJualModel();
    }

    public function index()
    {
        $data['title'] = 'Retur Penjualan';
        cek_akses_menu('returjual/index', $data);
    }

    public function add()
    {
        $jualId = trim((string) $this->request->getGet('jual_id'));
        $data['title'] = 'Retur Penjualan';
        $data['mode'] = 'create';
        $data['formData'] = $this->returJualModel->getFormData((string) session('toko_id'), null, $jualId !== '' ? $jualId : null);
        cek_akses_menu('returjual/form', $data, 'akses_create');
    }

    public function edit(string $rjId)
    {
        $data['title'] = 'Edit Retur Penjualan';
        $data['mode'] = 'edit';
        $data['formData'] = $this->returJualModel->getFormData((string) session('toko_id'), trim($rjId), null);
        cek_akses_menu('returjual/form', $data, 'akses_update');
    }

    public function ajax()
    {
        $result = $this->returJualModel->ajaxList(
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

    public function sale(string $jualId)
    {
        return $this->response->setJSON(
            $this->returJualModel->getSaleReferencePayload((string) session('toko_id'), trim($jualId))
        );
    }

    public function store()
    {
        $payload = $this->request->getRawInput();
        $result = $this->returJualModel->saveReturn((string) session('toko_id'), (string) session('username'), $payload, 'create');
        if (($result['tipe'] ?? '') === 'success') {
            tracelog('CREATE', 'CREATE RETUR JUAL ' . ($result['rj_id'] ?? '') . ' payload=' . json_encode($payload, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES));
        }
        return $this->response->setJSON($result);
    }

    public function update()
    {
        $payload = $this->request->getRawInput();
        $result = $this->returJualModel->saveReturn((string) session('toko_id'), (string) session('username'), $payload, 'edit');
        if (($result['tipe'] ?? '') === 'success') {
            tracelog('UPDATE', 'UPDATE RETUR JUAL ' . ($result['rj_id'] ?? ($payload['rj_id'] ?? '')) . ' payload=' . json_encode($payload, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES));
        }
        return $this->response->setJSON($result);
    }

    public function delete()
    {
        $payload = $this->request->getRawInput();
        $rjId = trim((string) ($payload['rj_id'] ?? ''));
        $result = $this->returJualModel->deleteReturn((string) session('toko_id'), (string) session('username'), $rjId);
        if (($result['tipe'] ?? '') === 'success') {
            tracelog('DELETE', 'DELETE RETUR JUAL ' . $rjId);
        }
        return $this->response->setJSON($result);
    }

    public function show(string $rjId)
    {
        $data = $this->returJualModel->getReturSummary((string) session('toko_id'), trim($rjId));
        if (!$data) {
            return $this->response->setJSON(['tipe' => 'error', 'data' => 'Retur penjualan tidak ditemukan']);
        }
        return $this->response->setJSON(['tipe' => 'success', 'data' => $data]);
    }

    public function struk(string $rjId)
    {
        $data['title'] = 'Struk Retur Penjualan';
        $data['receipt'] = $this->returJualModel->getReturSummary((string) session('toko_id'), trim($rjId));
        if (!$data['receipt']) {
            return redirect()->to('/returjual')->with('error', 'Retur penjualan tidak ditemukan');
        }
        $data['isMobile'] = cekMobile();
        return view('returjual/struk', $data);
    }
}
