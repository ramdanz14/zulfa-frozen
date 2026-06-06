<?php

namespace App\Controllers;

use App\Controllers\BaseController;
use App\Models\KonversiModel;

class Konversi extends BaseController
{
    protected KonversiModel $konversiModel;

    public function __construct()
    {
        $this->konversiModel = new KonversiModel();
    }

    public function index()
    {
        $data['title'] = 'Produksi / Konversi';
        $data['closingDate'] = $this->konversiModel->getClosingDate((string) session('toko_id'));
        cek_akses_menu('konversi/index', $data);
    }

    public function add()
    {
        $data['title'] = 'Tambah Konversi';
        $data['formData'] = $this->konversiModel->getFormData((string) session('toko_id'));
        cek_akses_menu('konversi/form', $data, 'akses_create');
    }

    public function show(string $konversiId)
    {
        $data = $this->konversiModel->getDocumentSummary((string) session('toko_id'), $konversiId);
        if (!$data) {
            return $this->response->setJSON(['tipe' => 'error', 'data' => 'Dokumen konversi tidak ditemukan']);
        }
        return $this->response->setJSON(['tipe' => 'success', 'data' => $data]);
    }

    public function recipe()
    {
        $data['title'] = 'Setting Recipe Konversi';
        cek_akses_menu('konversi/recipe', $data, 'akses_update');
    }

    public function ajax()
    {
        $result = $this->konversiModel->ajaxList(
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

    public function recipeAjax()
    {
        $result = $this->konversiModel->ajaxRecipeList([
            'start' => $this->request->getVar('start'),
            'length' => $this->request->getVar('length'),
            'search_value' => $this->request->getVar('search')['value'] ?? '',
        ]);

        return $this->response->setJSON([
            'draw' => (int) ($this->request->getVar('draw') ?? 0),
            'recordsTotal' => $result['total_count'],
            'recordsFiltered' => $result['total_filtered'],
            'data' => $result['data'],
        ]);
    }

    public function searchResult()
    {
        $term = trim((string) $this->request->getGet('term'));
        return $this->response->setJSON([
            'results' => array_map(static function (array $row): array {
                return [
                    'id' => $row['kode_item_hasil'],
                    'text' => trim(($row['kode_item_hasil'] ?? '') . ' - ' . ($row['nama_item'] ?? '')),
                ];
            }, $this->konversiModel->searchResultItems($term)),
        ]);
    }

    public function resultRecipe(string $kodeItemHasil)
    {
        $data = $this->konversiModel->getRecipeExecutionPayload((string) session('toko_id'), $kodeItemHasil);
        if (!$data) {
            return $this->response->setJSON(['tipe' => 'error', 'data' => 'Recipe untuk item hasil tidak ditemukan']);
        }
        return $this->response->setJSON(['tipe' => 'success', 'data' => $data]);
    }

    public function searchItem()
    {
        $term = trim((string) $this->request->getGet('term'));
        return $this->response->setJSON([
            'results' => array_map(static function (array $row): array {
                return [
                    'id' => $row['kode_item'],
                    'text' => trim(($row['kode_item'] ?? '') . ' - ' . ($row['nama_item'] ?? '')),
                ];
            }, $this->konversiModel->searchItems($term)),
        ]);
    }

    public function itemDetail(string $kodeItem)
    {
        $item = $this->konversiModel->getItemPayload((string) session('toko_id'), $kodeItem);
        if (!$item) {
            return $this->response->setJSON(['tipe' => 'error', 'data' => 'Item tidak ditemukan']);
        }
        return $this->response->setJSON(['tipe' => 'success', 'data' => $item]);
    }

    public function store()
    {
        $payload = $this->request->getRawInput();
        $result = $this->konversiModel->saveConversion((string) session('toko_id'), (string) session('username'), $payload);
        if (($result['tipe'] ?? '') === 'success') {
            tracelog('CREATE', 'CREATE KONVERSI ' . ($result['konversi_id'] ?? '') . ' rumus=' . ($result['trace_formula'] ?? ''));
        }
        return $this->response->setJSON($result);
    }

    public function delete()
    {
        $payload = $this->request->getRawInput();
        $result = $this->konversiModel->deleteConversion((string) session('toko_id'), trim((string) ($payload['konversi_id'] ?? '')));
        if (($result['tipe'] ?? '') === 'success') {
            tracelog('DELETE', 'DELETE KONVERSI ' . trim((string) ($payload['konversi_id'] ?? '')) . ' note=' . ($result['trace_formula'] ?? ''));
        }
        return $this->response->setJSON($result);
    }

    public function recipeStore()
    {
        $payload = $this->request->getRawInput();
        $result = $this->konversiModel->saveRecipe((string) session('username'), $payload, 'create');
        if (($result['tipe'] ?? '') === 'success') {
            tracelog('CREATE', 'CREATE RECIPE KONVERSI ' . json_encode($payload, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES));
        }
        return $this->response->setJSON($result);
    }

    public function recipeUpdate()
    {
        $payload = $this->request->getRawInput();
        $result = $this->konversiModel->saveRecipe((string) session('username'), $payload, 'edit');
        if (($result['tipe'] ?? '') === 'success') {
            tracelog('UPDATE', 'UPDATE RECIPE KONVERSI ' . json_encode($payload, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES));
        }
        return $this->response->setJSON($result);
    }

    public function recipeDelete()
    {
        $payload = $this->request->getRawInput();
        $result = $this->konversiModel->deleteRecipe((int) ($payload['recipe_id'] ?? 0));
        if (($result['tipe'] ?? '') === 'success') {
            tracelog('DELETE', 'DELETE RECIPE KONVERSI ' . (int) ($payload['recipe_id'] ?? 0));
        }
        return $this->response->setJSON($result);
    }
}
