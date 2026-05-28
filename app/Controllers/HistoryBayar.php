<?php

namespace App\Controllers;

use App\Controllers\BaseController;
use App\Models\HistoryBayarModel;

class HistoryBayar extends BaseController
{
    protected HistoryBayarModel $historyBayarModel;

    public function __construct()
    {
        $this->historyBayarModel = new HistoryBayarModel();
    }

    public function index()
    {
        $tokoId = (string) session('toko_id');
        $data['title'] = 'History Pembayaran Supplier';
        $data['supplierOptions'] = $this->historyBayarModel->getSupplierOptions($tokoId);
        $data['closingDate'] = $this->historyBayarModel->getClosingDate($tokoId);
        cek_akses_menu('historybayar/index', $data);
    }

    public function ajax()
    {
        $draw = $this->request->getVar('draw');
        $params = [
            'start' => $this->request->getVar('start'),
            'length' => $this->request->getVar('length'),
            'search_value' => $this->request->getVar('search')['value'] ?? '',
            'supco' => $this->request->getVar('supco'),
            'date_start' => $this->request->getVar('date_start'),
            'date_end' => $this->request->getVar('date_end'),
        ];
        $hasil = $this->historyBayarModel->ajax($params, (string) session('toko_id'));
        return $this->response->setJSON([
            'draw' => (int) $draw,
            'recordsTotal' => $hasil['total_count'],
            'recordsFiltered' => $hasil['total_filtered'],
            'data' => $hasil['data'],
        ]);
    }

    public function show(int $bayar_id)
    {
        $row = $this->historyBayarModel->getPayment((string) session('toko_id'), $bayar_id);
        if (!$row) {
            return $this->response->setJSON(['tipe' => 'error', 'data' => 'Data pembayaran tidak ditemukan']);
        }
        return $this->response->setJSON(['tipe' => 'success', 'data' => $row]);
    }

    public function update()
    {
        $payload = $this->request->getVar();
        $bayarId = (int) ($payload['bayar_id'] ?? 0);
        $result = $this->historyBayarModel->updatePayment((string) session('toko_id'), $bayarId, $payload);
        if (($result['tipe'] ?? '') === 'success') {
            tracelog('UPDATE', 'Update history pembayaran bayar_id=' . $bayarId . ' payload=' . json_encode($payload, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES));
        }
        return $this->response->setJSON($result);
    }

    public function delete()
    {
        $payload = $this->request->getVar();
        $bayarId = (int) ($payload['bayar_id'] ?? 0);
        $result = $this->historyBayarModel->deletePayment((string) session('toko_id'), $bayarId);
        if (($result['tipe'] ?? '') === 'success') {
            tracelog('DELETE', 'Delete history pembayaran bayar_id=' . $bayarId . ' payload=' . json_encode($payload, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES));
        }
        return $this->response->setJSON($result);
    }
}
