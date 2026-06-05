<?php

namespace App\Controllers;

use App\Controllers\BaseController;
use App\Models\SoModel;

class So extends BaseController
{
    protected SoModel $soModel;

    public function __construct()
    {
        $this->soModel = new SoModel();
    }

    public function index()
    {
        $data['title'] = 'Stock Opname';
        $data['soAktif'] = $this->soModel->getActiveSo((string) session('toko_id'));
        $data['kategoriOptions'] = $this->soModel->getKategoriOptions();
        cek_akses_menu('so/index', $data);
    }

    public function input()
    {
        $data['title'] = 'Input SO';
        $data['kategoriOptions'] = $this->soModel->getKategoriOptions();
        $data['soAktif'] = $this->soModel->getActiveSo((string) session('toko_id'));
        cek_akses_menu('so/input', $data);
    }

    public function hasil()
    {
        $tanggal = trim((string) ($this->request->getGet('tanggal') ?? ''));
        $soAktif = $this->soModel->getActiveSo((string) session('toko_id'));
        if ($tanggal === '' && !$soAktif) {
            session()->setFlashdata('so_error', 'Tidak ada SO aktif');
            return redirect()->to('/so');
        }

        $data['title'] = 'Hasil SO';
        $data['soAktif'] = $soAktif;
        $data['tanggalAcuan'] = $tanggal !== '' ? $tanggal : (string) ($soAktif['tanggal'] ?? date('Y-m-d'));
        cek_akses_menu('so/hasil', $data);
    }

    public function satuan()
    {
        $data['title'] = 'Adjust SO Satuan';
        $data['closingDate'] = $this->soModel->getClosingDate((string) session('toko_id'));
        cek_akses_menu('so/satuan', $data);
    }

    public function history()
    {
        $data['title'] = 'History SO';
        cek_akses_menu('so/history', $data);
    }

    public function ajaxInput()
    {
        $result = $this->soModel->ajaxInputList(
            [
                'start' => $this->request->getVar('start'),
                'length' => $this->request->getVar('length'),
                'search_value' => $this->request->getVar('search')['value'] ?? '',
            ],
            (string) session('toko_id'),
            trim((string) ($this->request->getVar('status_input') ?? 'belum')),
            trim((string) ($this->request->getVar('kat_id') ?? 'all'))
        );

        if (!empty($result['error'])) {
            return $this->response->setJSON([
                'tipe' => 'error',
                'message' => $result['error'],
                'draw' => (int) ($this->request->getVar('draw') ?? 0),
                'recordsTotal' => 0,
                'recordsFiltered' => 0,
                'data' => [],
            ]);
        }

        return $this->response->setJSON([
            'draw' => (int) ($this->request->getVar('draw') ?? 0),
            'recordsTotal' => $result['total_count'],
            'recordsFiltered' => $result['total_filtered'],
            'data' => $result['data'],
        ]);
    }

    public function ajaxHasil()
    {
        $tanggal = trim((string) ($this->request->getVar('tanggal') ?? ''));
        $tanggal = $tanggal !== '' ? $tanggal : (string) (($this->soModel->getActiveSo((string) session('toko_id'))['tanggal'] ?? date('Y-m-d')));
        $result = $this->soModel->ajaxHasilList(
            [
                'start' => $this->request->getVar('start'),
                'length' => $this->request->getVar('length'),
                'search_value' => $this->request->getVar('search')['value'] ?? '',
            ],
            (string) session('toko_id'),
            $tanggal
        );

        return $this->response->setJSON([
            'draw' => (int) ($this->request->getVar('draw') ?? 0),
            'recordsTotal' => $result['total_count'],
            'recordsFiltered' => $result['total_filtered'],
            'data' => $result['data'],
        ]);
    }

    public function ajaxAdjust()
    {
        $result = $this->soModel->ajaxAdjustList(
            [
                'start' => $this->request->getVar('start'),
                'length' => $this->request->getVar('length'),
                'search_value' => $this->request->getVar('search')['value'] ?? '',
            ],
            (string) session('toko_id')
        );

        return $this->response->setJSON([
            'draw' => (int) ($this->request->getVar('draw') ?? 0),
            'recordsTotal' => $result['total_count'],
            'recordsFiltered' => $result['total_filtered'],
            'data' => $result['data'],
        ]);
    }

    public function summary()
    {
        $tanggal = trim((string) ($this->request->getVar('tanggal') ?? ''));
        if ($tanggal === 'aktif' || $tanggal === '') {
            $tanggal = (string) (($this->soModel->getActiveSo((string) session('toko_id'))['tanggal'] ?? date('Y-m-d')));
        }

        return $this->response->setJSON(
            $this->soModel->getHasilSummary((string) session('toko_id'), $tanggal)
        );
    }

    public function createAll()
    {
        $result = $this->soModel->createSoSession((string) session('toko_id'), (string) session('username'));
        if (($result['tipe'] ?? '') === 'success') {
            tracelog('CREATE', 'CREATE SO ALL ' . ($result['so_table'] ?? ''));
        }
        return $this->response->setJSON($result);
    }

    public function createKategori()
    {
        $kategoriIds = $this->request->getVar('kat_id');
        $kategoriIds = is_array($kategoriIds) ? $kategoriIds : [$kategoriIds];
        $result = $this->soModel->createSoSession((string) session('toko_id'), (string) session('username'), $kategoriIds);
        if (($result['tipe'] ?? '') === 'success') {
            tracelog('CREATE', 'CREATE SO KATEGORI ' . json_encode($kategoriIds, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE));
        }
        return $this->response->setJSON($result);
    }

    public function saveInput()
    {
        $input = $this->request->getRawInput();
        $result = $this->soModel->saveInputQty(
            (string) session('toko_id'),
            (string) session('username'),
            trim((string) ($input['kode_item'] ?? '')),
            (float) ($input['qty_fisik'] ?? 0)
        );
        if (($result['tipe'] ?? '') === 'success') {
            tracelog('UPDATE', 'INPUT SO ' . (string) ($input['kode_item'] ?? ''));
        }
        return $this->response->setJSON($result);
    }

    public function historyInput()
    {
        return $this->response->setJSON(
            $this->soModel->getInputHistory((string) session('toko_id'), trim((string) $this->request->getVar('kode_item')))
        );
    }

    public function adjustAll()
    {
        $result = $this->soModel->adjustAll((string) session('toko_id'), (string) session('username'));
        if (($result['tipe'] ?? '') === 'success') {
            tracelog('CREATE', 'ADJUST SO ALL TOKO ' . (string) session('toko_id'));
        }
        return $this->response->setJSON($result);
    }

    public function searchItem()
    {
        return $this->response->setJSON(
            $this->soModel->searchBaseItems((string) session('toko_id'), trim((string) $this->request->getVar('term')))
        );
    }

    public function storeAdjust()
    {
        $input = $this->request->getRawInput();
        $result = $this->soModel->createSatuanAdjust((string) session('toko_id'), (string) session('username'), $input);
        if (($result['tipe'] ?? '') === 'success') {
            tracelog('CREATE', 'ADJUST SO SATUAN ' . (string) ($input['kode_item'] ?? ''));
        }
        return $this->response->setJSON($result);
    }

    public function deleteAdjust()
    {
        $input = $this->request->getRawInput();
        $result = $this->soModel->deleteAdjust((string) session('toko_id'), (int) ($input['so_id'] ?? 0));
        if (($result['tipe'] ?? '') === 'success') {
            tracelog('DELETE', 'DELETE ADJUST SO ' . (int) ($input['so_id'] ?? 0));
        }
        return $this->response->setJSON($result);
    }

    public function historyData()
    {
        return $this->response->setJSON(
            $this->soModel->getHistorySessions((string) session('toko_id'))
        );
    }
}
