<?php

namespace App\Controllers;

use App\Controllers\BaseController;
use App\Models\KasModel;
use App\Models\LapharianModel;
use App\Models\TokoModel;
use Config\Database;

class Lapharian extends BaseController
{
    protected LapharianModel $lapModel;
    protected TokoModel $tokoModel;
    protected KasModel $kasModel;

    public function __construct()
    {
        $this->lapModel = new LapharianModel();
        $this->tokoModel = new TokoModel();
        $this->kasModel = new KasModel();
    }

    public function index()
    {
        $data['title'] = 'Laporan Harian Kasir';
        $data['tokoOptions'] = $this->hasAccess('akses_delete') ? $this->tokoModel->getSwitcherList() : [];
        cek_akses_menu('lapharian/index', $data);
    }

    public function report()
    {
        $report = $this->lapModel->getReport(
            [
                'tanggal' => $this->request->getVar('tanggal') ?? '',
                'toko_ids' => $this->request->getVar('toko_ids'),
            ],
            (string) session('toko_id'),
            $this->hasAccess('akses_delete')
        );

        return $this->response->setJSON(['tipe' => 'success', 'data' => $report]);
    }

    public function struk()
    {
        $data['title'] = 'Struk Laporan Harian Kasir';
        $data['isMobile'] = cekMobile();
        $data['report'] = $this->lapModel->getReport(
            [
                'tanggal' => $this->request->getGet('tanggal') ?? '',
                'toko_ids' => $this->request->getGet('toko_ids') ?? [],
            ],
            (string) session('toko_id'),
            $this->hasAccess('akses_delete')
        );

        return view('lapharian/struk', $data);
    }

    public function deposit()
    {
        $nominal = (float) ($this->request->getVar('nominal') ?? 0);
        $keterangan = (string) ($this->request->getVar('keterangan') ?? 'Setoran tunai dari toko ke pemilik');
        $tokoId = (string) ($this->request->getVar('toko_id') ?? session('toko_id'));
        $tanggal = (string) ($this->request->getVar('tanggal') ?? date('Y-m-d'));

        if ($nominal <= 0) {
            return $this->response->setJSON(['tipe' => 'error', 'data' => 'Nominal harus lebih dari 0']);
        }

        $result = $this->kasModel->depositToOwner((string) session('username'), $tokoId, $nominal, $tanggal, $keterangan);
        if (($result['tipe'] ?? '') === 'success') {
            tracelog('CREATE', 'SETORAN HARIAN ' . ($result['kas_id'] ?? '') . ' payload=' . json_encode([
                'toko_id' => $tokoId, 'nominal' => $nominal, 'tanggal' => $tanggal,
            ], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES));
        }
        return $this->response->setJSON($result);
    }

    public function withdrawProfit()
    {
        $nominal = (float) ($this->request->getVar('nominal') ?? 0);
        $channel = strtoupper(trim((string) ($this->request->getVar('channel') ?? 'CASH')));
        $target = strtoupper(trim((string) ($this->request->getVar('target') ?? 'TOKO')));
        $keterangan = (string) ($this->request->getVar('keterangan') ?? 'Penarikan keuntungan dari pemilik');
        $tokoId = (string) ($this->request->getVar('toko_id') ?? session('toko_id'));
        $tanggal = (string) ($this->request->getVar('tanggal') ?? date('Y-m-d'));

        if ($nominal <= 0) {
            return $this->response->setJSON(['tipe' => 'error', 'data' => 'Nominal harus lebih dari 0']);
        }
        if (!$this->hasAccess('akses_delete')) {
            return $this->response->setJSON(['tipe' => 'error', 'data' => 'Tidak memiliki akses untuk penarikan keuntungan']);
        }

        $result = $this->kasModel->withdrawProfit((string) session('username'), $tokoId, $nominal, $channel, $target, $tanggal, $keterangan);
        if (($result['tipe'] ?? '') === 'success') {
            tracelog('CREATE', 'TARIK KEUNTUNGAN ' . ($result['kas_id'] ?? '') . ' payload=' . json_encode([
                'toko_id' => $tokoId, 'nominal' => $nominal, 'channel' => $channel, 'target' => $target, 'tanggal' => $tanggal,
            ], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES));
        }
        return $this->response->setJSON($result);
    }

    private function hasAccess(string $akses): bool
    {
        $db = Database::connect();
        $row = $db->query(
            "SELECT * FROM akses_menu WHERE level_id=:level_id: AND menu_id='lapharian' LIMIT 1",
            ['level_id' => session('level_id')]
        )->getRowArray();

        return !empty($row[$akses]) && $row[$akses] === 'Y';
    }
}
