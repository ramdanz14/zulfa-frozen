<?php

namespace App\Controllers;

use App\Controllers\BaseController;
use App\Models\JualModel;

class Jual extends BaseController
{
    protected JualModel $jualModel;

    public function __construct()
    {
        $this->jualModel = new JualModel();
    }

    public function index()
    {
        $data['title'] = 'POS Kasir';
        $data['initialData'] = $this->jualModel->getInitialData((string) session('toko_id'));
        $data['initialData']['mode'] = 'create';
        $data['initialData']['save_url'] = base_url('/jual');
        $data['initialData']['exit_url'] = base_url('/main');
        $data['initialData']['after_save_redirect'] = null;
        cek_akses_menu('jual/index', $data);
    }

    public function searchItem()
    {
        $term = trim((string) $this->request->getGet('term'));
        if ($term === '') {
            return $this->response->setJSON(['tipe' => 'success', 'data' => [], 'auto_pick' => false]);
        }

        $rows = $this->jualModel->searchItems((string) session('toko_id'), $term);

        return $this->response->setJSON([
            'tipe' => 'success',
            'data' => $rows,
            'auto_pick' => count($rows) === 1,
        ]);
    }

    public function itemDetail(string $kode_item)
    {
        $item = $this->jualModel->getItemPayload((string) session('toko_id'), $kode_item);
        if (! $item) {
            return $this->response->setJSON(['tipe' => 'error', 'data' => 'Item tidak ditemukan']);
        }

        return $this->response->setJSON(['tipe' => 'success', 'data' => $item]);
    }

    public function searchCustomer()
    {
        $term = trim((string) $this->request->getGet('term'));
        $rows = $term === '' ? [] : $this->jualModel->searchCustomers($term);
        $results = [[
            'id' => 'CUST-GENERAL',
            'text' => 'CUST-GENERAL - Pelanggan Umum',
            'payload' => $this->jualModel->getCustomerPayload('CUST-GENERAL'),
        ]];

        foreach ($rows as $row) {
            $results[] = [
                'id' => $row['cust_id'],
                'text' => trim(($row['cust_id'] ?? '') . ' - ' . ($row['nama'] ?? '')),
                'payload' => $row,
            ];
        }

        return $this->response->setJSON(['results' => $results]);
    }

    public function registerMember()
    {
        $result = $this->jualModel->registerQuickMember(
            (string) session('username'),
            (string) $this->request->getVar('nama'),
            (string) $this->request->getVar('kontak')
        );

        if (($result['tipe'] ?? '') === 'success') {
            tracelog('CREATE', 'DAFTAR MEMBER INSTAN POS ' . json_encode($result['data'], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES));
        }

        return $this->response->setJSON($result);
    }

    public function save()
    {
        $result = $this->jualModel->saveSale(
            (string) session('toko_id'),
            (string) session('username'),
            $this->request->getVar()
        );

        if (($result['tipe'] ?? '') === 'success') {
            $customer = $this->jualModel->getCustomerPayload(trim((string) ($this->request->getVar('cust_id') ?? 'CUST-GENERAL')));
            $tracePayload = $this->request->getVar();
            if (is_array($tracePayload)) {
                $tracePayload['customer'] = $customer;
            }
            tracelog('CREATE', 'TRANSAKSI POS ' . ($result['jual_id'] ?? '') . ' payload=' . json_encode($tracePayload, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES));
        }

        return $this->response->setJSON($result);
    }

    public function voidCart()
    {
        $payload = json_decode((string) $this->request->getVar('cart_snapshot'), true) ?: [];
        $reason = trim((string) $this->request->getVar('reason'));
        $this->jualModel->logVoidCart(
            (string) session('toko_id'),
            (string) session('username'),
            $payload,
            $reason !== '' ? $reason : 'Reset keranjang dari POS'
        );

        tracelog('DELETE', 'VOID CART POS payload=' . json_encode($payload, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES));
        return $this->response->setJSON(['tipe' => 'success', 'data' => 'Keranjang berhasil dikosongkan']);
    }

    public function struk(string $jual_id)
    {
        $data['title'] = 'Struk POS';
        $data['isMobile'] = cekMobile();

        $data['receipt'] = $this->jualModel->getReceiptData((string) session('toko_id'), trim($jual_id));
        if (! $data['receipt']) {
            return redirect()->to('/jual');
        }

        return view('jual/struk', $data);
    }

    public function faktur(string $jual_id)
    {
        $data['title'] = 'Faktur Penjualan';
        $data['isMobile'] = cekMobile();

        $data['receipt'] = $this->jualModel->getReceiptData((string) session('toko_id'), trim($jual_id));
        if (! $data['receipt']) {
            return redirect()->to('/jual');
        }

        return view('jual/faktur', $data);
    }
}
