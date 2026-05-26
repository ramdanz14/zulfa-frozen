<?php

namespace App\Controllers;

use App\Controllers\BaseController;
use CodeIgniter\HTTP\ResponseInterface;
use CodeIgniter\I18n\Time;

class Role extends BaseController
{
    public $roleModel;
    public $constModel;
    public function __construct()
    {
        $this->roleModel = new \App\Models\RoleUserModel();
    }
    public function index()
    {
        $data["title"] = "Jabatan";
        cek_akses_menu('role/index_role', $data);
    }
    public function indexAkses($level_id)
    {

        $data['akses'] = $this->roleModel->getListAkses($level_id);
        $data['level_id'] = $level_id;
        $data['title'] = "Akses Menu";
        cek_akses_menu('role/akses_menu', $data);
    }

    public function ajax()
    {
        $draw = $this->request->getVar('draw');
        $params['start'] = $this->request->getVar('start');
        $params['length'] = $this->request->getVar('length');
        $params['search_value'] = $this->request->getVar('search')['value'];

        $hasil = $this->roleModel->ajax($params);


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
        $input = $this->request->getVar();
        unset($input['_method']);

        $cek =  $this->roleModel->insert($input, false);

        if ($cek) {
            $hasil  = array("tipe" => "success", "data" => "Data berhasil ditambahkan!!!!");
        } else {
            $hasil  = array("tipe" => "error", "data" => "Gagal Tambah data!!!!");
        }
        tracelog('CREATE', 'Create Role dengan ID : ' . json_encode($input));
        return json_encode($hasil);
    }

    public function update()
    {
        $primarykey = $this->request->getVar('level_id');
        $input = $this->request->getVar();
        unset($input['_method']);
        $upd =  $this->roleModel->where('level_id', $primarykey)
            ->set($input)
            ->update();


        if ($upd) {
            $hasil  = array("tipe" => "success", "data" => "Data berhasil diupdate!!!!");
        } else {
            $hasil  = array("tipe" => "error", "data" => "Gagal Tambah data!!!!");
        }
        tracelog('UPDATE', "Update Role dengan ID :   " . json_encode($input));
        return json_encode($hasil);
    }

    public function delete()
    {
        $primarykey = $this->request->getVar('level_id');

        $upd =  $this->roleModel->delete($primarykey);


        if ($upd) {
            $hasil  = array("tipe" => "success", "data" => "Data berhasil dihapus!!!!");
        } else {
            $hasil  = array("tipe" => "error", "data" => "Gagal hapus data!!!!");
        }
        tracelog('DELETE', "Hapus User dengan ID : $primarykey ");
        return json_encode($hasil);
    }


    public function updateAkses()
    {
        $input = $this->request->getRawInput();
        $upd =  $this->roleModel->ChangeAkses($input);
        if ($upd) {
            if ($input['nilai'] == "Y") {

                $hasil  = array("tipe" => "success", "data" => "Akses berhasil ditambahkan!!!!");
            } else {
                $hasil  = array("tipe" => "warning", "data" => "Akses berhasil dihapus!!!!");
            }
        } else {
            $hasil  = array("tipe" => "error", "data" => "Gagal update akses!!!!");
        }
        tracelog('UPDATE', "Update Akses User dengan ID : " . json_encode($this->request->getVar()));
        return json_encode($hasil);
    }
}
