<?php

namespace App\Controllers;

use App\Controllers\BaseController;
use App\Models\PoinMemberModel;
use Config\Database;

class Poinmember extends BaseController
{
    protected PoinMemberModel $poinMemberModel;

    public function __construct()
    {
        $this->poinMemberModel = new PoinMemberModel();
    }

    public function index()
    {
        $data['title'] = 'Poin Member';
        $data['customerOptions'] = $this->poinMemberModel->getCustomerOptions();
        $data['nominalPerPoin'] = $this->poinMemberModel->getCurrentNominalPerPoin();
        cek_akses_menu('poinmember/index', $data);
    }

    public function ajax()
    {
        $draw = $this->request->getVar('draw');
        $params = [
            'start' => $this->request->getVar('start'),
            'length' => $this->request->getVar('length'),
            'search_value' => $this->request->getVar('search')['value'] ?? '',
            'cust_id' => $this->request->getVar('cust_id') ?? '',
            'date_start' => $this->request->getVar('date_start') ?? '',
            'date_end' => $this->request->getVar('date_end') ?? '',
        ];

        $hasil = $this->poinMemberModel->ajaxList($params);

        return $this->response->setJSON([
            'draw' => (int) $draw,
            'recordsTotal' => $hasil['total_count'],
            'recordsFiltered' => $hasil['total_filtered'],
            'data' => $hasil['data'],
        ]);
    }

    public function setting()
    {
        if (! $this->hasAccess('akses_update')) {
            return $this->response->setJSON(['tipe' => 'error', 'data' => 'Anda tidak memiliki akses update setting poin'])->setStatusCode(403);
        }

        $nominal = (int) ($this->request->getVar('nominal_per_poin') ?? 0);
        if ($nominal <= 0) {
            return $this->response->setJSON(['tipe' => 'error', 'data' => 'Nilai kelipatan rupiah per poin harus lebih besar dari nol'])->setStatusCode(422);
        }

        $ok = $this->poinMemberModel->saveNominalPerPoin($nominal);
        if ($ok) {
            tracelog('UPDATE', 'SETTING POIN MEMBER nominal_per_poin=' . $nominal);
            return $this->response->setJSON([
                'tipe' => 'success',
                'data' => 'Setting poin member berhasil disimpan',
                'nominal_per_poin' => $nominal,
            ]);
        }

        return $this->response->setJSON(['tipe' => 'error', 'data' => 'Gagal menyimpan setting poin member'])->setStatusCode(500);
    }

    public function hardReset()
    {
        if (! $this->hasAccess('akses_delete')) {
            return $this->response->setJSON(['tipe' => 'error', 'data' => 'Anda tidak memiliki akses hard reset poin member'])->setStatusCode(403);
        }

        $result = $this->poinMemberModel->hardResetAllPoints((string) session('toko_id'), (string) session('username'));
        if (($result['tipe'] ?? '') === 'success') {
            tracelog('DELETE', 'HARD RESET POIN MEMBER oleh ' . session('username'));
        }

        return $this->response->setJSON($result);
    }

    private function hasAccess(string $akses): bool
    {
        $db = Database::connect();
        $row = $db->query(
            "SELECT * FROM akses_menu WHERE level_id=:level_id: AND menu_id='poinmember' LIMIT 1",
            ['level_id' => session('level_id')]
        )->getRowArray();

        return ! empty($row[$akses]) && $row[$akses] === 'Y';
    }
}
