<?php

namespace App\Controllers;

use App\Controllers\BaseController;
use App\Models\MenuModel;

class Menu extends BaseController
{
    public $menuModel;
    public function __construct()
    {
        $this->menuModel = new MenuModel();
    }

    public function index()
    {
        $data["title"] = "Menu";
        cek_akses_menu('menu', $data);
    }

    public function ajax()
    {
        $draw = $this->request->getVar('draw');
        $params['start'] = $this->request->getVar('start');
        $params['length'] = $this->request->getVar('length');
        $params['search_value'] = $this->request->getVar('search')['value'] ?? '';
        $hasil = $this->menuModel->ajax($params);
        $json_data = array(
            'draw' => intval($draw),
            'recordsTotal' => $hasil['total_count'],
            'recordsFiltered' => $hasil['total_filtered'],
            'data' => $hasil['data']
        );
        return $this->response->setJSON($json_data);
    }

    public function store()
    {
        $input = $this->request->getRawInput();
        unset($input['_method']);
        unset($input['primarykey']);
        $cek = $this->menuModel->insert($input, false);
        $hasil = $cek ? ['tipe' => 'success', 'data' => 'Data berhasil ditambahkan!!!!'] : ['tipe' => 'error', 'data' => 'Gagal Tambah data!!!!'];
        tracelog('CREATE', 'Create Menu : ' . json_encode($input));
        return $this->response->setJSON($hasil);
    }

    public function update()
    {
        $primarykey = $this->request->getVar('primarykey');
        $input = $this->request->getRawInput();
        unset($input['_method']);
        unset($input['primarykey']);
        $upd = $this->menuModel->where('menu_id', $primarykey)->set($input)->update();
        $hasil = $upd ? ['tipe' => 'success', 'data' => 'Data berhasil diupdate!!!!'] : ['tipe' => 'error', 'data' => 'Gagal update data!!!!'];
        tracelog('UPDATE', 'Update Menu : ' . $primarykey . ' => ' . json_encode($input));
        return $this->response->setJSON($hasil);
    }

    public function delete()
    {
        $primarykey = $this->request->getVar('primarykey');
        $upd = $this->menuModel->delete($primarykey);
        $hasil = $upd ? ['tipe' => 'success', 'data' => 'Data berhasil dihapus!!!!'] : ['tipe' => 'error', 'data' => 'Gagal hapus data!!!!'];
        tracelog('DELETE', 'Hapus Menu : ' . $primarykey);
        return $this->response->setJSON($hasil);
    }
}
