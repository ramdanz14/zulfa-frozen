<?php

namespace App\Controllers;

use App\Controllers\BaseController;
use App\Models\ItemModel;

class Item extends BaseController
{
    protected $itemModel;
    protected $db;

    public function __construct()
    {
        $this->itemModel = new ItemModel();
        $this->db = \Config\Database::connect();
    }

    public function index()
    {
        $data['title'] = 'Data Barang';
        cek_akses_menu('item/index_item', $data);
    }

    public function ajax()
    {
        $draw = $this->request->getVar('draw');
        $params['start'] = $this->request->getVar('start');
        $params['length'] = $this->request->getVar('length');
        $params['search_value'] = $this->request->getVar('search')['value'] ?? '';
        $toko_id = session('toko_id');

        $hasil = $this->itemModel->ajax($params, $toko_id);
        return $this->response->setJSON([
            'draw' => intval($draw),
            'recordsTotal' => $hasil['total_count'],
            'recordsFiltered' => $hasil['total_filtered'],
            'data' => $hasil['data']
        ]);
    }

    public function create()
    {
        $data['title'] = 'Tambah Item';
        $data['mode'] = 'create';
        $data['formData'] = [
            'prodmast' => ['kode_item' => $this->itemModel->getLastID()],
            'satuan' => [],
            'store' => []
        ];
        $data['kategoriOptions'] = $this->itemModel->getKategoriOptions();
        $data['supplierOptions'] = $this->itemModel->getSupplierOptions();
        $data['satuanOptions'] = $this->itemModel->getSatuanOptions();
        cek_akses_menu('item/form_item', $data, "akses_create");
    }

    public function edit(string $kode_item)
    {
        $data['title'] = 'Edit Item';
        $data['mode'] = 'edit';
        $data['formData'] = $this->itemModel->getEditData($kode_item, session('toko_id'));
        if (empty($data['formData']['prodmast'])) {
            return redirect()->to('/item');
        }
        $data['kategoriOptions'] = $this->itemModel->getKategoriOptions();
        $data['supplierOptions'] = $this->itemModel->getSupplierOptions();
        $data['satuanOptions'] = $this->itemModel->getSatuanOptions();
        cek_akses_menu('item/form_item', $data, "akses_update");
    }

    public function view(string $kode_item)
    {
        $data['title'] = 'Detail Item';
        $data['detail'] = $this->itemModel->getDetailData($kode_item);
        if (empty($data['detail']['prodmast'])) {
            return redirect()->to('/item');
        }
        cek_akses_menu('item/view_item', $data);
    }

    public function lastid()
    {
        return $this->response->setJSON(['tipe' => 'success', 'data' => $this->itemModel->getLastID()]);
    }

    public function store()
    {
        return $this->saveItem('create');
    }

    public function update()
    {
        return $this->saveItem('edit');
    }

    private function saveItem(string $mode)
    {
        $kode_item = trim((string) $this->request->getVar('kode_item'));
        $nama_item = trim((string) $this->request->getVar('nama_item'));
        $barcode = trim((string) $this->request->getVar('barcode'));
        $kat_id = trim((string) $this->request->getVar('kat_id'));
        $supco = trim((string) $this->request->getVar('supco'));
        $keterangan = trim((string) $this->request->getVar('keterangan'));
        $status_item = $this->request->getVar('status_item') === 'Y' ? 'Y' : 'N';
        $satuanJson = (string) $this->request->getVar('satuan_json');
        $storeJson = (string) $this->request->getVar('store_json');
        $username = session('username') ?? '';
        $toko_id = session('toko_id');

        if ($kode_item === '' || $nama_item === '' || $kat_id === '') {
            return $this->response->setJSON(['tipe' => 'error', 'data' => 'Data utama wajib diisi']);
        }

        $satuanData = json_decode($satuanJson, true) ?: [];
        $storeData = json_decode($storeJson, true) ?: [];
        if (empty($satuanData) || empty($storeData)) {
            return $this->response->setJSON(['tipe' => 'error', 'data' => 'Satuan dan harga wajib diisi']);
        }

        $satIds = array_column($satuanData, 'sat_id');
        if (count(array_unique($satIds)) !== count($satIds)) {
            return $this->response->setJSON(['tipe' => 'error', 'data' => 'Satuan tidak boleh duplikat']);
        }

        $baseExists = false;
        foreach ($satuanData as $row) {
            if (($row['sat_id'] ?? '') === '' || !is_numeric($row['qty_konversi'] ?? null)) {
                return $this->response->setJSON(['tipe' => 'error', 'data' => 'Data satuan tidak valid']);
            }
            if ((float) $row['qty_konversi'] == 1.0) {
                $baseExists = true;
            }
        }
        if (!$baseExists) {
            return $this->response->setJSON(['tipe' => 'error', 'data' => 'Wajib ada satuan dasar dengan qty_konversi = 1']);
        }

        $storeMap = [];
        foreach ($storeData as $row) {
            $sid = (string) ($row['sat_id'] ?? '');
            if ($sid === '' || !in_array($sid, $satIds, true)) {
                return $this->response->setJSON(['tipe' => 'error', 'data' => 'Harga harus mengikuti satuan yang dipilih']);
            }
            $hargaPokok = (int) ($row['harga_pokok'] ?? 0);
            $hargaJual = (int) ($row['harga_jual'] ?? 0);
            $margin = $hargaPokok > 0 ? round((($hargaJual - $hargaPokok) / $hargaPokok) * 100, 1) : 0;
            $storeMap[$sid] = [
                'harga_pokok' => $hargaPokok,
                'harga_jual' => $hargaJual,
                'target_psn_margin' => $margin
            ];
        }

        foreach ($satIds as $sid) {
            if (!isset($storeMap[$sid])) {
                return $this->response->setJSON(['tipe' => 'error', 'data' => 'Harga semua satuan harus diisi']);
            }
        }

        $this->db->transStart();
        if ($mode === 'create') {
            $this->db->table('prodmast')->insert([
                'kode_item' => $kode_item,
                'barcode' => $barcode,
                'nama_item' => $nama_item,
                'kat_id' => $kat_id,
                'keterangan' => $keterangan,
                'supco' => $supco !== '' ? $supco : null,
                'updid' => $username,
                'updtime' => date('Y-m-d H:i:s')
            ]);
        } else {
            $this->db->table('prodmast')->where('kode_item', $kode_item)->update([
                'barcode' => $barcode,
                'nama_item' => $nama_item,
                'kat_id' => $kat_id,
                'keterangan' => $keterangan,
                'supco' => $supco !== '' ? $supco : null,
                'updid' => $username,
                'updtime' => date('Y-m-d H:i:s')
            ]);
            $this->db->table('prodmast_satuan')->where('kode_item', $kode_item)->delete();
            $this->db->table('prodmast_store')->where('kode_item', $kode_item)->where('toko_id', $toko_id)->delete();
        }

        foreach ($satuanData as $row) {
            $this->db->table('prodmast_satuan')->insert([
                'kode_item' => $kode_item,
                'sat_id' => $row['sat_id'],
                'qty_konversi' => (float) $row['qty_konversi']
            ]);
        }

        foreach ($satIds as $sid) {
            $price = $storeMap[$sid];
            $this->db->table('prodmast_store')->insert([
                'toko_id' => $toko_id,
                'kode_item' => $kode_item,
                'sat_id' => $sid,
                'harga_pokok' => $price['harga_pokok'],
                'harga_jual' => $price['harga_jual'],
                'target_psn_margin' => $price['target_psn_margin'],
                'status_item' => $status_item,
                'updid' => $username,
                'updtime' => date('Y-m-d H:i:s')
            ]);
        }
        $this->db->transComplete();

        if (!$this->db->transStatus()) {
            return $this->response->setJSON(['tipe' => 'error', 'data' => 'Gagal simpan data item']);
        }

        tracelog(strtoupper($mode), strtoupper($mode) . " item $kode_item " . json_encode($this->request->getRawInput()));
        return $this->response->setJSON(['tipe' => 'success', 'data' => 'Data item berhasil disimpan']);
    }
}
