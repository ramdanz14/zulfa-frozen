<?php

namespace App\Controllers;

use App\Controllers\BaseController;
use App\Models\JualModel;

class Piutang extends BaseController
{
    protected JualModel $jualModel;

    public function __construct()
    {
        $this->jualModel = new JualModel();
    }

    public function index()
    {
        $data['title'] = 'Monitoring Piutang Customer';
        cek_akses_menu('piutang/index', $data);
    }

    public function ajax()
    {
        $draw = (int) ($this->request->getVar('draw') ?? 0);
        $params = [
            'start' => $this->request->getVar('start'),
            'length' => $this->request->getVar('length'),
            'search_value' => $this->request->getVar('search')['value'] ?? '',
        ];
        $filter = strtoupper(trim((string) $this->request->getVar('status_filter')));
        if (!in_array($filter, ['BELUM', 'LUNAS', 'ALL'], true)) {
            $filter = 'BELUM';
        }

        $hasil = $this->jualModel->ajaxPiutang($params, (string) session('toko_id'), $filter);
        return $this->response->setJSON([
            'draw' => $draw,
            'recordsTotal' => $hasil['total_count'],
            'recordsFiltered' => $hasil['total_filtered'],
            'data' => $hasil['data'],
        ]);
    }

    public function show(string $jual_id)
    {
        $data = $this->jualModel->getPiutangSummary((string) session('toko_id'), trim($jual_id));
        if (!$data) {
            return $this->response->setJSON(['tipe' => 'error', 'data' => 'Data piutang customer tidak ditemukan']);
        }

        return $this->response->setJSON(['tipe' => 'success', 'data' => $data]);
    }

    public function pay(string $jual_id)
    {
        $payments = json_decode((string) $this->request->getVar('payment_json'), true) ?: [];
        $result = $this->jualModel->addPiutangPayment(
            (string) session('toko_id'),
            trim($jual_id),
            (string) session('username'),
            $payments
        );

        if (($result['tipe'] ?? '') === 'success') {
            tracelog('UPDATE', 'PEMBAYARAN PIUTANG ' . trim($jual_id) . ' payload=' . json_encode($payments, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES));
        }

        return $this->response->setJSON($result);
    }
}
