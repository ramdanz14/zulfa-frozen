<?php

namespace App\Controllers;

use App\Controllers\BaseController;
use App\Models\AbsensiModel;

class Absensi extends BaseController
{
    protected AbsensiModel $absensiModel;

    public function __construct()
    {
        $this->absensiModel = new AbsensiModel();
    }

    public function index()
    {
        $data['title'] = 'Absensi Karyawan';
        cek_akses_menu('absensi/index', $data);
    }

    public function input(?string $tanggal = null)
    {
        $resolvedDate = $this->normalizeDate($tanggal ?: (string) ($this->request->getGet('tanggal') ?? '')) ?: date('Y-m-d');
        $data['title'] = 'Input Absensi';
        $data['formData'] = $this->absensiModel->getFormData($resolvedDate);
        $hasExisting = false;
        foreach (($data['formData']['rows'] ?? []) as $row) {
            if (!empty($row['absensi_id'])) {
                $hasExisting = true;
                break;
            }
        }
        cek_akses_menu('absensi/form', $data, $hasExisting ? 'akses_update' : 'akses_create');
    }

    public function pay()
    {
        $periodStart = $this->normalizeDate((string) ($this->request->getGet('period_start') ?? '')) ?: date('Y-m-01');
        $periodEnd = $this->normalizeDate((string) ($this->request->getGet('period_end') ?? '')) ?: date('Y-m-d');
        if ($periodStart > $periodEnd) {
            [$periodStart, $periodEnd] = [$periodEnd, $periodStart];
        }

        $data['title'] = 'Pembayaran Gaji';
        $data['payData'] = $this->absensiModel->getPaymentCandidates($periodStart, $periodEnd);
        cek_akses_menu('absensi/pay', $data, 'akses_update');
    }

    public function ajax()
    {
        $params = [
            'start' => $this->request->getVar('start'),
            'length' => $this->request->getVar('length'),
            'search_value' => $this->request->getVar('search')['value'] ?? '',
        ];

        $hasil = $this->absensiModel->ajaxSummary($params);
        return $this->response->setJSON([
            'draw' => (int) ($this->request->getVar('draw') ?? 0),
            'recordsTotal' => $hasil['total_count'],
            'recordsFiltered' => $hasil['total_filtered'],
            'data' => $hasil['data'],
        ]);
    }

    public function ajaxPayment()
    {
        $params = [
            'start' => $this->request->getVar('start'),
            'length' => $this->request->getVar('length'),
            'search_value' => $this->request->getVar('search')['value'] ?? '',
        ];

        $hasil = $this->absensiModel->ajaxPaymentHistory($params);
        return $this->response->setJSON([
            'draw' => (int) ($this->request->getVar('draw') ?? 0),
            'recordsTotal' => $hasil['total_count'],
            'recordsFiltered' => $hasil['total_filtered'],
            'data' => $hasil['data'],
        ]);
    }

    public function show(string $tanggal)
    {
        $resolvedDate = $this->normalizeDate($tanggal);
        $data = $resolvedDate ? $this->absensiModel->getDateSummary($resolvedDate) : null;
        if (!$data) {
            return $this->response->setJSON(['tipe' => 'error', 'data' => 'Data absensi tidak ditemukan']);
        }
        return $this->response->setJSON(['tipe' => 'success', 'data' => $data]);
    }

    public function showPayment(string $batchId)
    {
        $data = $this->absensiModel->getPaymentDetail($batchId);
        if (!$data) {
            return $this->response->setJSON(['tipe' => 'error', 'data' => 'Riwayat pembayaran tidak ditemukan']);
        }
        return $this->response->setJSON(['tipe' => 'success', 'data' => $data]);
    }

    public function store()
    {
        $input = $this->request->getRawInput();
        $tanggal = $this->normalizeDate((string) $input['tanggal']);
        $rows = json_decode((string) ($input['rows_json'] ?? '[]'), true) ?: [];
        $result = $this->absensiModel->saveEntries($tanggal ?: '', (string) session('username'), $rows);
        if (($result['tipe'] ?? '') === 'success') {
            tracelog('CREATE', 'INPUT ABSENSI ' . ($tanggal ?: '') . ' payload=' . json_encode($rows, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES));
        }
        return $this->response->setJSON($result);
    }

    public function delete()
    {
        $input = $this->request->getRawInput();
        $tanggal = $this->normalizeDate((string) $input['tanggal']);
        if (!$tanggal) {
            return $this->response->setJSON(['tipe' => 'error', 'data' => 'Tanggal absensi tidak valid']);
        }
        $result = $this->absensiModel->deleteDate($tanggal);
        if (($result['tipe'] ?? '') === 'success') {
            tracelog('DELETE', 'HAPUS ABSENSI ' . $tanggal);
        }
        return $this->response->setJSON($result);
    }

    public function processPayment()
    {
        $tanggalBayar = $this->normalizeDate((string) $this->request->getVar('tanggal_bayar'));
        $periodStart = $this->normalizeDate((string) $this->request->getVar('period_start'));
        $periodEnd = $this->normalizeDate((string) $this->request->getVar('period_end'));
        $saldoChannel = strtoupper(trim((string) ($this->request->getVar('saldo_channel') ?? 'CASH')));
        $selectedIds = json_decode((string) ($this->request->getVar('selected_ids') ?? '[]'), true) ?: [];

        $result = $this->absensiModel->createPaymentBatch(
            (string) session('username'),
            $tanggalBayar ?: '',
            $periodStart ?: '',
            $periodEnd ?: '',
            $selectedIds,
            $saldoChannel
        );
        if (($result['tipe'] ?? '') === 'success') {
            tracelog('CREATE', 'BAYAR GAJI ' . ($result['batch_id'] ?? '') . ' payload=' . json_encode([
                'tanggal_bayar' => $tanggalBayar,
                'period_start' => $periodStart,
                'period_end' => $periodEnd,
                'saldo_channel' => $saldoChannel,
                'selected_ids' => $selectedIds,
            ], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES));
        }
        return $this->response->setJSON($result);
    }

    public function struk(string $batchId, string $karyawanId)
    {
        $data['title'] = 'Slip Gaji';
        $data['isMobile'] = cekMobile();
        $data['slip'] = $this->absensiModel->getSlipData($batchId, $karyawanId);
        if (!$data['slip']) {
            return redirect()->to('/absensi');
        }
        return view('absensi/struk', $data);
    }

    private function normalizeDate(string $value): ?string
    {
        $value = trim($value);
        if ($value === '') {
            return null;
        }
        $date = date_create($value);
        return $date ? $date->format('Y-m-d') : null;
    }
}
