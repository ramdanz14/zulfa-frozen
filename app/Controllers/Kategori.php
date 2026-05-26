<?php

namespace App\Controllers;

use App\Controllers\BaseController;

class Kategori extends BaseController
{
    public $kategoriModel;
    public function __construct()
    {
        $this->kategoriModel = new \App\Models\KategoriModel();
    }
    public function index()
    {
        $data["title"] = "Kategori";
        cek_akses_menu("kategori", $data);
    }

    public function ajax()
    {
        $draw = $this->request->getVar('draw');
        $params['start'] = $this->request->getVar('start');
        $params['length'] = $this->request->getVar('length');
        $params['search_value'] = $this->request->getVar('search')['value'];

        $hasil = $this->kategoriModel->ajax($params);


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
        $kat_id = $this->request->getVar('kat_id');
        $cek =  $this->kategoriModel->insert([
            'kat_id' => $kat_id
        ], false);

        if ($cek) {
            $hasil  = array("tipe" => "success", "data" => "Data berhasil ditambahkan!!!!");
        } else {
            $hasil  = array("tipe" => "error", "data" => "Gagal Tambah data!!!!");
        }
        tracelog('CREATE', 'Create kategori dengan ID : ' . $kat_id);
        return json_encode($hasil);
    }

    public function update()
    {
        $primarykey = $this->request->getVar('primarykey');
        $kat_id = $this->request->getVar('kat_id');
        $upd =  $this->kategoriModel->where('kat_id', $primarykey)
            ->set(['kat_id' => $kat_id])
            ->update();


        if ($upd) {
            $hasil  = array("tipe" => "success", "data" => "Data berhasil diupdate!!!!");
        } else {
            $hasil  = array("tipe" => "error", "data" => "Gagal Tambah data!!!!");
        }
        tracelog('Update', "Update kategori dengan ID : $primarykey menjadi $kat_id ");
        return json_encode($hasil);
    }

    public function delete()
    {
        $primarykey = $this->request->getVar('primarykey');
        $upd =  $this->kategoriModel->delete($primarykey);


        if ($upd) {
            $hasil  = array("tipe" => "success", "data" => "Data berhasil dihapus!!!!");
        } else {
            $hasil  = array("tipe" => "error", "data" => "Gagal hapus data!!!!");
        }
        tracelog('Hapus', "Hapus kategori dengan ID : $primarykey ");
        return json_encode($hasil);
    }
}
