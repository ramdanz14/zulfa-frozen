<?php

namespace App\Controllers;

use App\Models\CustomerModel;

class Customer extends BaseController
{
    protected CustomerModel $customerModel;

    public function __construct()
    {
        $this->customerModel = new CustomerModel();
    }

    public function index()
    {
        $data['title'] = 'Customer';
        cek_akses_menu('customer', $data);
    }

    public function lastid()
    {
        return $this->response->setJSON([
            'tipe' => 'success',
            'data' => $this->customerModel->getLastId(),
        ]);
    }

    public function ajax()
    {
        $draw = $this->request->getVar('draw');
        $params['start'] = $this->request->getVar('start');
        $params['length'] = $this->request->getVar('length');
        $params['search_value'] = $this->request->getVar('search')['value'] ?? '';

        $hasil = $this->customerModel->ajax($params);

        return $this->response->setJSON([
            'draw' => intval($draw),
            'recordsTotal' => $hasil['total_count'],
            'recordsFiltered' => $hasil['total_filtered'],
            'data' => $hasil['data'],
        ]);
    }

    public function store()
    {
        $input = $this->request->getVar();
        unset($input['_method'], $input['primarykey']);

        $input['tgl_daftar'] = date('Y-m-d');
        $input['max_faktur'] = (int) ($input['max_faktur'] ?? 3);
        $input['poin'] = (int) ($input['poin'] ?? 0);

        $cek = $this->customerModel->insert($input, false);
        $hasil = $cek
            ? ['tipe' => 'success', 'data' => 'Data customer berhasil ditambahkan.']
            : ['tipe' => 'error', 'data' => 'Gagal menambah data customer.'];

        tracelog('CREATE', 'Create customer dengan ID : ' . ($input['cust_id'] ?? '') . json_encode($input));
        return $this->response->setJSON($hasil);
    }

    public function update()
    {
        $primarykey = $this->request->getVar('primarykey');
        $input = $this->request->getVar();
        unset($input['_method'], $input['primarykey'], $input['cust_id'], $input['tgl_daftar']);

        $input['max_faktur'] = (int) ($input['max_faktur'] ?? 3);
        $input['poin'] = (int) ($input['poin'] ?? 0);

        $upd = $this->customerModel->where('cust_id', $primarykey)->set($input)->update();
        $hasil = $upd
            ? ['tipe' => 'success', 'data' => 'Data customer berhasil diupdate.']
            : ['tipe' => 'error', 'data' => 'Gagal update data customer.'];

        tracelog('UPDATE', 'Update customer dengan ID : ' . $primarykey . json_encode($input));
        return $this->response->setJSON($hasil);
    }

    public function delete()
    {
        $primarykey = $this->request->getVar('primarykey');
        $customer = $this->customerModel->find($primarykey);
        if ($customer->poin != "") {
            return $this->response->setJSON(['tipe' => 'error', 'data' => 'Customer masih memiliki poin tidak boleh di hapus.']);
        }
        $cek = $this->customerModel->delete($primarykey);
        $hasil = $cek
            ? ['tipe' => 'success', 'data' => 'Data customer berhasil dihapus.']
            : ['tipe' => 'error', 'data' => 'Gagal hapus data customer.'];

        tracelog('DELETE', 'Delete customer dengan ID : ' . $primarykey);
        return $this->response->setJSON($hasil);
    }
}
