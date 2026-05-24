<?php

namespace App\Controllers;

use App\Controllers\BaseController;
use App\Models\UserModel;

class User extends BaseController
{
    public $userModel;
    private $db;
    public function __construct()
    {
        $this->db = \Config\Database::connect();
        $this->userModel = new UserModel();
    }
    public function index()
    {
        $list_role =  $this->db->query("SELECT * FROM role_user ;")->getResult();
        $data['list_role'] = $list_role;
        $list_toko =  $this->db->query("SELECT * FROM toko ;")->getResult();
        $data['list_toko'] = $list_toko;
        cek_akses_menu('user/index_user', $data);
    }

    public function lastid()
    {
        return $this->response->setJSON([
            'tipe' => 'success',
            'data' => $this->userModel->GetLastID(),
        ]);
    }

    public function ajax()
    {
        $draw = $this->request->getVar('draw');
        $params['start'] = $this->request->getVar('start');
        $params['length'] = $this->request->getVar('length');
        $params['search_value'] = $this->request->getVar('search')['value'];

        $hasil = $this->userModel->ajax($params);


        $json_data = array(
            "draw" => intval($draw),
            "recordsTotal" => $hasil['total_count'],
            "recordsFiltered" => $hasil['total_filtered'],
            "data" => $hasil['data']   // total data array
        );
        return $this->response->setJSON($json_data);
    }

    public function GetId()
    {
        $karyawanId = $this->request->getGet('karyawan_id');
        if (!$karyawanId) {
            return $this->response->setJSON([
                'tipe' => 'error',
                'data' => 'karyawan_id wajib diisi',
            ]);
        }

        $user = $this->userModel->where('karyawan_id', $karyawanId)->first();
        if (!$user) {
            return $this->response->setJSON([
                'tipe' => 'error',
                'data' => 'Data user tidak ditemukan',
            ]);
        }

        return $this->response->setJSON([
            'tipe' => 'success',
            'data' => $user,
        ]);
    }

    private function getPayload(): array
    {
        $json = $this->request->getJSON(true);
        if (is_array($json) && !empty($json)) {
            return $json;
        }
        return $this->request->getRawInput();
    }

    private function validatePayload(array $payload): array
    {
        $payload = array_map(static function ($value) {
            return is_string($value) ? trim($value) : $value;
        }, $payload);

        $rules = [
            'karyawan_id' => 'required',
            'username' => 'required',
            'fullname' => 'required',
            'phone' => 'required',
            'email' => 'required|valid_email',
            'level_id' => 'required',
            'toko_id' => 'required',
        ];

        if (!$this->validateData($payload, $rules)) {
            return [
                'valid' => false,
                'errors' => $this->validator->getErrors(),
            ];
        }

        return ['valid' => true, 'errors' => []];
    }

    private function normalizeUserPayload(array $payload): array
    {
        $payload['active'] = ($payload['active'] ?? 'N') === 'Y' ? 'Y' : 'N';
        $payload['absensi'] = ($payload['absensi'] ?? 'N') === 'Y' ? 'Y' : 'N';
        return $payload;
    }

    public function Create()
    {
        $karyawan = $this->getPayload();
        $karyawan = $this->normalizeUserPayload($karyawan);
        $validation = $this->validatePayload($karyawan);
        if (!$validation['valid']) {
            return $this->response->setJSON([
                "tipe" => "error",
                "data" => "Validasi gagal",
                "errors" => $validation['errors'],
            ]);
        }

        $cek = $this->userModel->insert($karyawan);
        $cek = $this->userModel->ResetPassword($karyawan["karyawan_id"]);
        if ($cek) {
            $hasil  = array("tipe" => "success", "data" => "Data ditambahkan!!!!");
        } else {
            $hasil  = array("tipe" => "error", "data" => "Gagal Tambah data!!!!");
        }
        tracelog('Create', 'Create User dengan ID : ' . $karyawan["karyawan_id"] . json_encode($karyawan));

        return $this->response->setJSON($hasil);
    }

    public function Update()
    {
        $karyawan = $this->getPayload();
        $karyawan = $this->normalizeUserPayload($karyawan);
        $validation = $this->validatePayload($karyawan);
        if (!$validation['valid']) {
            return $this->response->setJSON([
                "tipe" => "error",
                "data" => "Validasi gagal",
                "errors" => $validation['errors'],
            ]);
        }

        $cek =  $this->userModel->where("karyawan_id", $karyawan['karyawan_id'])->set($karyawan)->update();
        if ($cek) {
            $hasil  = array("tipe" => "success", "data" => "Data diupdate!!!!");
        } else {
            $hasil  = array("tipe" => "error", "data" => "Gagal update data!!!!");
        }
        tracelog('Update', 'Update User dengan ID : ' . $karyawan['karyawan_id'] . json_encode($karyawan));

        return $this->response->setJSON($hasil);
    }

    public function Read($karyawan_id)
    {
        $data['user'] = $this->userModel->GetDetail($karyawan_id);
        $data['gaji'] = $this->userModel->GetGaji($karyawan_id);

        return view('user/profile_user', $data);
    }

    public function Delete()
    {
        $karyawan = $this->getPayload();

        $cek =  $this->userModel->where("karyawan_id", $karyawan['karyawan_id'])->delete();
        if ($cek) {
            $hasil  = array("tipe" => "success", "data" => "Data dihapus!!!!");
        } else {
            $hasil  = array("tipe" => "error", "data" => "Gagal hapus data!!!!");
        }
        tracelog('Delete', 'Delete User dengan ID : ' . $karyawan['karyawan_id']);

        return $this->response->setJSON($hasil);
    }

    public function ResetPassword()
    {
        $payload = $this->getPayload();
        $karyawanId = $payload['karyawan_id'] ?? null;

        if (!$karyawanId) {
            return $this->response->setJSON(array("tipe" => "error", "data" => "Karyawan tidak ditemukan"));
        }

        $cek = $this->userModel->ResetPassword($karyawanId);
        if ($cek) {
            $hasil  = array("tipe" => "success", "data" => "Password berhasil direset");
            tracelog('Update', 'Reset Password User dengan ID : ' . $karyawanId);
        } else {
            $hasil  = array("tipe" => "error", "data" => "Gagal reset password");
        }

        return $this->response->setJSON($hasil);
    }
}
