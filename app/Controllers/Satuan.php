<?php

namespace App\Controllers;

use App\Controllers\BaseController;
use App\Models\SatuanModel;

class Satuan extends BaseController
{
    public $satuanModel;
    public function __construct()
    {
        $this->satuanModel = new SatuanModel();
    }
    public function index()
    {
        cek_akses_menu('satuan');
    }
    public function ajax()
    {
        $draw = $this->request->getVar('draw');
        $params['start'] = $this->request->getVar('start');
        $params['length'] = $this->request->getVar('length');
        $params['search_value'] = $this->request->getVar('search')['value'];

        $hasil = $this->satuanModel->ajax($params);


        $json_data = array(
            "draw" => intval($draw),
            "recordsTotal" => $hasil['total_count'],
            "recordsFiltered" => $hasil['total_filtered'],
            "data" => $hasil['data']   // total data array
        );
        echo json_encode($json_data);
    }
    public function store()
    {
        $sat_id = $this->request->getVar('sat_id');
        $cek =  $this->satuanModel->insert([
            'sat_id' => $sat_id
        ], false);

        if ($cek) {
            $hasil  = array("tipe" => "success", "data" => "Data berhasil ditambahkan!!!!");
        } else {
            $hasil  = array("tipe" => "error", "data" => "Gagal Tambah data!!!!");
        }
        tracelog('CREATE', 'Create Satuan dengan ID : ' . $sat_id);
        return json_encode($hasil);
    }

    public function update()
    {
        $primarykey = $this->request->getVar('primarykey');
        $sat_id = $this->request->getVar('sat_id');
        $upd =  $this->satuanModel->where('sat_id', $primarykey)
            ->set(['sat_id' => $sat_id])
            ->update();


        if ($upd) {
            $hasil  = array("tipe" => "success", "data" => "Data berhasil diupdate!!!!");
        } else {
            $hasil  = array("tipe" => "error", "data" => "Gagal Tambah data!!!!");
        }
        tracelog('Update', "Update Satuan dengan ID : $primarykey menjadi $sat_id ");
        return json_encode($hasil);
    }

    public function delete()
    {
        $primarykey = $this->request->getVar('primarykey');
        $upd =  $this->satuanModel->delete($primarykey);


        if ($upd) {
            $hasil  = array("tipe" => "success", "data" => "Data berhasil dihapus!!!!");
        } else {
            $hasil  = array("tipe" => "error", "data" => "Gagal hapus data!!!!");
        }
        tracelog('Hapus', "Hapus Satuan dengan ID : $primarykey ");
        return json_encode($hasil);
    }
}
