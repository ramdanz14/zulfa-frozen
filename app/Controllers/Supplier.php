<?php

namespace App\Controllers;

use App\Controllers\BaseController;
use CodeIgniter\HTTP\ResponseInterface;

class Supplier extends BaseController
{
    public $supmastModel;
    protected $db;
    public function __construct()
    {
        $this->supmastModel = new \App\Models\SupmastModel();
        $this->db = \Config\Database::connect();
    }
    public function index()
    {
        $data["title"] = "Supplier";
        cek_akses_menu('supplier', $data);
    }
    public function lastid()
    {
        return $this->response->setJSON([
            'tipe' => 'success',
            'data' => $this->supmastModel->GetLastID()
        ]);
    }
    public function ajax()
    {
        $draw = $this->request->getVar('draw');
        $params['start'] = $this->request->getVar('start');
        $params['length'] = $this->request->getVar('length');
        $params['search_value'] = $this->request->getVar('search')['value'];

        $hasil = $this->supmastModel->ajax($params);


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
        $cek =  $this->supmastModel->insert($input, false);

        if ($cek) {
            $hasil  = array("tipe" => "success", "data" => "Data berhasil ditambahkan!!!!");
        } else {
            $hasil  = array("tipe" => "error", "data" => "Gagal Tambah data!!!!");
        }
        tracelog('CREATE', 'Create supplier dengan ID : ' . $this->request->getVar('supco'));
        return json_encode($hasil);
    }

    public function update()
    {
        $primarykey = $this->request->getVar('supco');
        $input = $this->request->getVar();
        $upd =  $this->supmastModel->where('supco', $primarykey)
            ->set($input)
            ->update();


        if ($upd) {
            $hasil  = array("tipe" => "success", "data" => "Data berhasil diupdate!!!!");
        } else {
            $hasil  = array("tipe" => "error", "data" => "Gagal Tambah data!!!!");
        }
        tracelog('Update', "Update supplier dengan ID : $primarykey  ");
        return json_encode($hasil);
    }

    public function delete()
    {
        $primarykey = $this->request->getVar('supco');


        $sisa_hutang =  $this->db->query("SELECT IFNULL(SUM(sisa_bayar),0) AS total FROM pembelian WHERE supco='$primarykey' AND is_kredit=1 AND status_bayar IN('BELUM','CICIL');")->getRow();
        if ($sisa_hutang->total == '0') {
            $cek =  $this->db->query("DELETE FROM  supmast WHERE supco='$primarykey' ;");
            if ($cek) {
                $hasil  = array("tipe" => "success", "data" => "Data dihapus!!!!");
            } else {
                $hasil  = array("tipe" => "error", "data" => "Gagal hapus data!!!!");
            }
            tracelog('DELETE', 'Delete Supplier dengan ID : ' . $primarykey);
        } else {
            $total = number_format($sisa_hutang->total);
            $hasil = array("tipe" => "error", "data" => "Tidak bisa hapus supplier masih memiliki hutang sebesar Rp.$total !!!!");
        }

        return json_encode($hasil);
    }
}
