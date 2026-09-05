<?php

namespace App\Controllers;

use App\Controllers\BaseController;
use CodeIgniter\HTTP\ResponseInterface;

class Login extends BaseController
{
    protected \CodeIgniter\Database\BaseConnection $db;
    public function __construct()
    {
        $this->db = \Config\Database::connect();
    }
    public function index()
    {
        $data['const'] = $this->db->query("SELECT * FROM const")->getResultArray();
        return view("login", $data);
    }
    public function check()
    {
        $username = $this->request->getPost('username');
        $pass = $this->request->getPost('password');
        $str = "SELECT *,PASSWORD(:pass:) AS cek_pass FROM tb_user LEFT JOIN role_user USING(level_id) left join toko using (toko_id) WHERE username=:username: and active='Y';";
        $query = $this->db->query($str, ['username' => $username, 'pass' => $pass]);

        $row = $query->getRow();
        if (isset($row)) {
            if ($row->password == $row->cek_pass) {
                session()->set('fullname', $row->fullname);
                session()->set('level_name', $row->level_name);
                session()->set('username', $row->username);
                session()->set('level_id', $row->level_id);
                session()->set('level_name', $row->level_name);
                session()->set('toko_id', $row->toko_id);
                session()->set('toko_nama', $row->toko_nama);
                session()->set('toko_theme', $row->toko_theme);
                session()->set('avatar', $row->avatar);
                session()->set('karyawan_id', $row->karyawan_id);
                tracelog('LOGIN', 'Login dengan user : ' . $row->fullname);
                $redirect = $this->request->getGet('redirect') ?? "";
                if ($redirect != "") {
                    return redirect()->to($redirect);
                } else {

                    return redirect()->to('/main');
                }
            } else {
                session()->setFlashdata('warning', '<div class="alert alert-danger" role="alert">
                ALERT!! Password salah
              </div>');
            }
        } else {
            session()->setFlashdata('warning', '<div class="alert alert-danger" role="alert">
            ALERT!! Username tidak terdaftar 
          </div>');
        }
        $data['const'] = $this->db->query("SELECT * FROM const")->getResultArray();
        return view('login', $data);
    }
    public function out()
    {
        session()->setFlashdata('warning', '<div class="alert alert-warning alert-dismissible" role="alert">
        <button type="button" class="close" data-dismiss="alert" aria-hidden="true">×</button>
        INFO!! You have been logged out 
      </div>');
        session()->destroy();
        $data['const'] = $this->db->query("SELECT * FROM const")->getResultArray();
        return redirect()->to("login");
    }
}
