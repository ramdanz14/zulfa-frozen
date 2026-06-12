<?php

namespace App\Controllers;

use App\Controllers\BaseController;
use App\Models\SettingDataModel;
use Config\Database;

class Settingdata extends BaseController
{
    protected SettingDataModel $settingModel;

    public function __construct()
    {
        $this->settingModel = new SettingDataModel();
    }

    public function index()
    {
        $data['title'] = 'Setting Data';
        $data['settings'] = $this->settingModel->getSettings();
        $data['logos'] = $this->getLogoInfo();
        cek_akses_menu('settingdata/index', $data);
    }

    public function save()
    {
        if (! $this->hasAccess('akses_update')) {
            return $this->response->setJSON(['tipe' => 'error', 'data' => 'Anda tidak memiliki akses update setting data'])->setStatusCode(403);
        }

        $result = $this->settingModel->saveSettings($this->request->getVar(), (string) session('username'));
        if (($result['tipe'] ?? '') === 'success') {
            tracelog('UPDATE', 'SETTING DATA ' . json_encode($result['settings'] ?? [], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES));
        }

        return $this->response->setJSON($result)->setStatusCode(($result['tipe'] ?? '') === 'success' ? 200 : 422);
    }

    public function uploadLogo()
    {
        if (! $this->hasAccess('akses_update')) {
            return $this->response->setJSON(['tipe' => 'error', 'data' => 'Anda tidak memiliki akses upload logo'])->setStatusCode(403);
        }

        $targets = [
            'logo_color' => 'zulfa-logo-color.png',
            'logo_bw' => 'zulfa-logo-bw.png',
        ];
        $saved = [];
        $targetDir = FCPATH . 'assets/images/logos';
        if (! is_dir($targetDir)) {
            mkdir($targetDir, 0775, true);
        }

        foreach ($targets as $field => $filename) {
            $file = $this->request->getFile($field);
            if (! $file || $file->getError() === UPLOAD_ERR_NO_FILE) {
                continue;
            }
            if (! $file->isValid()) {
                return $this->response->setJSON(['tipe' => 'error', 'data' => 'Upload ' . $filename . ' tidak valid'])->setStatusCode(422);
            }
            if ($file->getMimeType() !== 'image/png') {
                return $this->response->setJSON(['tipe' => 'error', 'data' => 'Logo wajib format PNG'])->setStatusCode(422);
            }

            $file->move($targetDir, $filename, true);
            $saved[] = $filename;
        }

        if (empty($saved)) {
            return $this->response->setJSON(['tipe' => 'error', 'data' => 'Pilih minimal satu file logo untuk diupload'])->setStatusCode(422);
        }

        tracelog('UPDATE', 'UPLOAD LOGO SETTING DATA ' . implode(', ', $saved));
        return $this->response->setJSON([
            'tipe' => 'success',
            'data' => 'Logo berhasil diupload',
            'logos' => $this->getLogoInfo(),
        ]);
    }

    private function getLogoInfo(): array
    {
        $items = [
            'logo_color' => [
                'label' => 'Logo Zulfa Colour',
                'filename' => 'zulfa-logo-color.png',
                'description' => 'Logo berwarna untuk tampilan umum, dokumen, atau kebutuhan brand berwarna.',
            ],
            'logo_bw' => [
                'label' => 'Logo Zulfa BW',
                'filename' => 'zulfa-logo-bw.png',
                'description' => 'Logo hitam putih untuk struk thermal dan cetakan yang butuh kontras sederhana.',
            ],
        ];

        foreach ($items as &$item) {
            $path = FCPATH . 'assets/images/logos/' . $item['filename'];
            $item['exists'] = is_file($path);
            $item['updated_at'] = is_file($path) ? date('Y-m-d H:i:s', filemtime($path)) : null;
            $item['url'] = base_url('/assets/images/logos/' . $item['filename']) . '?v=' . (is_file($path) ? filemtime($path) : time());
        }
        unset($item);

        return $items;
    }

    private function hasAccess(string $akses): bool
    {
        $db = Database::connect();
        $row = $db->query(
            "SELECT * FROM akses_menu WHERE level_id=:level_id: AND menu_id='setting-data' LIMIT 1",
            ['level_id' => session('level_id')]
        )->getRowArray();

        return ! empty($row[$akses]) && $row[$akses] === 'Y';
    }
}
