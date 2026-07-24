<?php

namespace App\Controllers;

use App\Controllers\BaseController;
use App\Models\KasModel;

class Kas extends BaseController
{
    protected KasModel $kasModel;

    public function __construct()
    {
        $this->kasModel = new KasModel();
    }

    public function index()
    {
        $data['title'] = 'Kas Masuk / Keluar';
        $data['akunOptions'] = $this->kasModel->getAkunOptions();
        $data['karyawanOptions'] = $this->kasModel->getKaryawanOptions((string) session('toko_id'));
        cek_akses_menu('kas/index', $data);
    }

    public function ajax()
    {
        $result = $this->kasModel->ajax([
            'start' => $this->request->getVar('start'),
            'length' => $this->request->getVar('length'),
            'search_value' => $this->request->getVar('search')['value'] ?? '',
        ], (string) session('toko_id'));

        return $this->response->setJSON([
            'draw' => (int) ($this->request->getVar('draw') ?? 0),
            'recordsTotal' => $result['total_count'],
            'recordsFiltered' => $result['total_filtered'],
            'data' => $result['data'],
        ]);
    }

    /**
     * Get real-time cash balances (saldo_toko, saldo_pemilik)
     */
    public function balances()
    {
        $untilDate = $this->request->getVar('until_date') ?? null;
        $balances = $this->kasModel->getCashBalances((string) session('toko_id'), $untilDate);

        return $this->response->setJSON(['tipe' => 'success', 'data' => $balances]);
    }

    /**
     * Get daily cash summary for EOD report
     */
    public function dailySummary()
    {
        $tanggal = $this->request->getVar('tanggal') ?? date('Y-m-d');
        $summary = $this->kasModel->getDailyCashSummary((string) session('toko_id'), $tanggal);

        return $this->response->setJSON(['tipe' => 'success', 'data' => $summary]);
    }

    /**
     * Get summary with dual balances for date range
     */
    public function summary()
    {
        $result = $this->kasModel->getSummary(
            (string) session('toko_id'),
            [
                'date_start' => $this->request->getVar('date_start') ?? date('Y-m-01'),
                'date_end' => $this->request->getVar('date_end') ?? date('Y-m-d'),
                'toko_ids' => $this->request->getVar('toko_ids') ?? [],
            ],
            $this->hasAccess('akses_delete')
        );

        // Add current balances to summary
        $currentBalances = $this->kasModel->getCashBalances((string) session('toko_id'));
        $result['summary']['saldo_toko'] = $currentBalances['saldo_toko'];
        $result['summary']['saldo_pemilik'] = $currentBalances['saldo_pemilik'];
        $result['summary']['saldo_total_cash'] = $currentBalances['total'];

        return $this->response->setJSON(['tipe' => 'success', 'data' => $result]);
    }

    public function store()
    {
        $payload = $this->request->getRawInput();
        $result = $this->kasModel->saveMutation((string) session('toko_id'), (string) session('username'), $payload, 'create');
        if (($result['tipe'] ?? '') === 'success') {
            tracelog('CREATE', 'CREATE KAS MUTASI payload=' . json_encode($payload, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES));
        }
        return $this->response->setJSON($result);
    }

    public function update()
    {
        $payload = $this->request->getRawInput();
        $result = $this->kasModel->saveMutation((string) session('toko_id'), (string) session('username'), $payload, 'edit');
        if (($result['tipe'] ?? '') === 'success') {
            tracelog('UPDATE', 'UPDATE KAS MUTASI payload=' . json_encode($payload, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES));
        }
        return $this->response->setJSON($result);
    }

    public function delete()
    {
        $payload = $this->request->getRawInput();
        $kasId = (int) ($payload['kas_id'] ?? 0);
        $result = $this->kasModel->deleteMutation((string) session('toko_id'), $kasId);
        if (($result['tipe'] ?? '') === 'success') {
            tracelog('DELETE', 'DELETE KAS MUTASI kas_id=' . $kasId);
        }
        return $this->response->setJSON($result);
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
            tracelog('CREATE', 'DEPOSIT TO OWNER payload=' . json_encode([
                'amount' => $amount,
                'note' => $note,
            ], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES));
        }

        return $this->response->setJSON($result);
    }

    /**
     * Withdraw profit from TOKO or PEMILIK
     * Only accessible by users with akses_delete (owner/admin)
     */
    public function withdrawProfit()
    {
        // Check access
        if (!$this->hasAccess('akses_delete')) {
            return $this->response->setJSON(['tipe' => 'error', 'data' => 'Akses ditolak: hanya pemilik/admin yang bisa tarik keuntungan']);
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
            tracelog('CREATE', 'WITHDRAW PROFIT payload=' . json_encode([
                'amount' => $amount,
                'source_target' => $sourceTarget,
                'note' => $note,
            ], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES));
        }

        return $this->response->setJSON($result);
    }

    private function hasAccess(string $akses): bool
    {
        $db = \Config\Database::connect();
        $row = $db->query(
            "SELECT * FROM akses_menu WHERE level_id=:level_id: AND menu_id='kas' LIMIT 1",
            ['level_id' => session('level_id')]
        )->getRowArray();

        return !empty($row[$akses]) && $row[$akses] === 'Y';
    }
}