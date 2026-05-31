<?php

namespace App\Controllers;

use App\Controllers\BaseController;
use App\Models\SettingHargaModel;

class Settingharga extends BaseController
{
    protected SettingHargaModel $settingHargaModel;

    public function __construct()
    {
        $this->settingHargaModel = new SettingHargaModel();
    }

    public function index()
    {
        $data['title'] = 'Setting Harga';
        $data['recentInvoices'] = $this->settingHargaModel->getRecentInvoices((string) session('toko_id'));
        cek_akses_menu('settingharga/index', $data);
    }

    public function ajax()
    {
        $draw = $this->request->getVar('draw');
        $params = [
            'start' => $this->request->getVar('start'),
            'length' => $this->request->getVar('length'),
            'search_value' => $this->request->getVar('search')['value'] ?? '',
        ];
        $beliId = trim((string) $this->request->getVar('beli_id'));

        $hasil = $this->settingHargaModel->ajaxList($params, (string) session('toko_id'), $beliId);
        return $this->response->setJSON([
            'draw' => (int) $draw,
            'recordsTotal' => $hasil['total_count'],
            'recordsFiltered' => $hasil['total_filtered'],
            'data' => $hasil['data'],
        ]);
    }

    public function save()
    {
        $payload = $this->request->getRawInput();
        if (empty($payload)) {
            $payload = $this->request->getVar() ?? [];
        }

        $rowsJson = (string) ($payload['rows_json'] ?? '');
        $rows = $rowsJson !== '' ? (json_decode($rowsJson, true) ?: []) : [];
        if (empty($rows)) {
            $singleRow = [
                'kode_item' => trim((string) ($payload['kode_item'] ?? '')),
                'sat_id' => trim((string) ($payload['sat_id'] ?? '')),
                'harga_pokok' => $payload['harga_pokok'] ?? 0,
                'harga_jual' => $payload['harga_jual'] ?? 0,
            ];

            if ($singleRow['kode_item'] !== '' && $singleRow['sat_id'] !== '') {
                $rows = [$singleRow];
            }
        }

        $sourceBeliId = trim((string) ($payload['source_beli_id'] ?? ''));

        $result = $this->settingHargaModel->saveCorrections(
            (string) session('toko_id'),
            (string) session('username'),
            $rows,
            $sourceBeliId !== '' ? $sourceBeliId : 'KOREKSI'
        );

        if (($result['tipe'] ?? '') === 'success') {
            tracelog(
                'UPDATE',
                'KOREKSI HARGA payload=' . json_encode([
                    'source_beli_id' => $sourceBeliId !== '' ? $sourceBeliId : 'KOREKSI',
                    'rows' => $rows,
                ], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES)
            );
        }

        return $this->response->setJSON($result);
    }
}
