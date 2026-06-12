<?php

namespace App\Controllers;

use App\Controllers\BaseController;
use App\Models\JualModel;

class Listjual extends BaseController
{
    protected JualModel $jualModel;

    public function __construct()
    {
        $this->jualModel = new JualModel();
    }

    public function index()
    {
        $data['title'] = 'Monitoring Penjualan';
        $data['defaultStartDate'] = date('Y-m-d');
        $data['defaultEndDate'] = date('Y-m-d');
        cek_akses_menu('listjual/index', $data);
    }

    public function ajax()
    {
        $draw = (int) ($this->request->getVar('draw') ?? 0);
        $params = [
            'start' => $this->request->getVar('start'),
            'length' => $this->request->getVar('length'),
            'search_value' => $this->request->getVar('search')['value'] ?? '',
            'start_date' => $this->request->getVar('start_date') ?? '',
            'end_date' => $this->request->getVar('end_date') ?? '',
        ];

        $hasil = $this->jualModel->ajaxList($params, (string) session('toko_id'));

        return $this->response->setJSON([
            'draw' => $draw,
            'recordsTotal' => $hasil['total_count'],
            'recordsFiltered' => $hasil['total_filtered'],
            'data' => $hasil['data'],
        ]);
    }

    public function show(string $jual_id)
    {
        $data = $this->jualModel->getReceiptData((string) session('toko_id'), trim($jual_id));
        if (! $data) {
            return $this->response->setJSON(['tipe' => 'error', 'data' => 'Transaksi penjualan tidak ditemukan']);
        }

        return $this->response->setJSON(['tipe' => 'success', 'data' => $data]);
    }

    public function edit(string $jual_id)
    {
        $initialData = $this->jualModel->getInitialData((string) session('toko_id'));
        $editSale = $this->jualModel->getSaleEditPayload((string) session('toko_id'), trim($jual_id));
        if (! $editSale) {
            session()->setFlashdata('error', 'Transaksi penjualan tidak ditemukan atau tidak bisa diedit');
            return redirect()->to('/listjual');
        }

        if (! $this->jualModel->canModifySaleDate($editSale['tgl'] ?? null)) {
            session()->setFlashdata('error', 'Hanya transaksi penjualan tanggal hari ini yang bisa diedit');
            return redirect()->to('/listjual');
        }

        $initialData['mode'] = 'edit';
        $initialData['save_url'] = base_url('/listjual');
        $initialData['exit_url'] = base_url('/listjual');
        $initialData['after_save_redirect'] = base_url('/listjual');
        $initialData['edit_sale'] = $editSale;

        $data['title'] = 'Edit Penjualan';
        $data['initialData'] = $initialData;
        cek_akses_menu('jual/index', $data, 'akses_update');
    }

    public function update()
    {
        $jualId = trim((string) $this->request->getVar('jual_id'));
        $result = $this->jualModel->updateSale(
            (string) session('toko_id'),
            (string) session('username'),
            $jualId,
            $this->request->getVar()
        );

        if (($result['tipe'] ?? '') === 'success') {
            tracelog('UPDATE', 'EDIT TRANSAKSI POS ' . $jualId . ' payload=' . json_encode($this->request->getVar(), JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES));
        }

        return $this->response->setJSON($result);
    }

    public function delete()
    {
        $jualId = trim((string) $this->request->getVar('jual_id'));
        $result = $this->jualModel->deleteSale(
            (string) session('toko_id'),
            (string) session('username'),
            $jualId
        );

        if (($result['tipe'] ?? '') === 'success') {
            tracelog('DELETE', 'HAPUS TRANSAKSI POS ' . $jualId);
        }

        return $this->response->setJSON($result);
    }

    public function reprint(string $jual_id)
    {
        $jualId = trim($jual_id);
        $format = strtolower(trim((string) ($this->request->getGet('format') ?? 'struk')));
        $format = $format === 'faktur' ? 'faktur' : 'struk';
        $ok = $this->jualModel->incrementReprintCount((string) session('toko_id'), $jualId);
        if (! $ok) {
            session()->setFlashdata('error', 'Transaksi penjualan tidak ditemukan');
            return redirect()->to('/listjual');
        }

        tracelog('PRINT', 'REPRINT ' . strtoupper($format) . ' POS ' . $jualId);
        return redirect()->to('/jual/' . $format . '/' . $jualId);
    }
}
