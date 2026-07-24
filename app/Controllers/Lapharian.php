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

    /**
     * Deposit cash from TOKO to PEMILIK (end of day setoran)
     */
    public function deposit()
    {
        $amount = (float) $this->request->getVar('amount');
        $note = (string) $this->request->getVar('note');

        $result = $this->kasModel->depositToOwner(
            (string) session('toko_id'),
            (string) session('username'),
            $amount,
            $note
        );

        if (($result['tipe'] ?? '') === 'success') {
            tracelog('CREATE', 'LAPHARIAN DEPOSIT TO OWNER payload=' . json_encode([
                'amount' => $amount,
                'note' => $note,
            ], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES));
        }

        return $this->response->setJSON($result);
    }

    /**
     * Withdraw profit (only for owner/admin)
     */
    public function withdrawProfit()
    {
        if (!$this->hasAccess('akses_delete')) {
            return $this->response->setJSON(['tipe' => 'error', 'data' => 'Akses ditolak']);
        }

        $amount = (float) $this->request->getVar('amount');
        $sourceTarget = strtoupper(trim((string) $this->request->getVar('source_target')));
        $note = (string) $this->request->getVar('note');

        $result = $this->kasModel->withdrawProfit(
            (string) session('toko_id'),
            (string) session('username'),
            $amount,
            $sourceTarget,
            $note
        );

        if (($result['tipe'] ?? '') === 'success') {
            tracelog('CREATE', 'LAPHARIAN WITHDRAW PROFIT payload=' . json_encode([
                'amount' => $amount,
                'source_target' => $sourceTarget,
                'note' => $note,
            ], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES));
        }

        return $this->response->setJSON($result);
    }

    /**
     * Get current cash balances
     */
    public function balances()
    {
        $untilDate = $this->request->getVar('until_date') ?? null;
        $balances = $this->kasModel->getCashBalances((string) session('toko_id'), $untilDate);

        return $this->response->setJSON(['tipe' => 'success', 'data' => $balances]);
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