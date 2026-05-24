<?php

namespace App\Models;

use CodeIgniter\Model;

class TracelogModel extends Model
{
    protected $table = 'tracelog';
    protected $primaryKey = 'traceid';
    protected $allowedFields = ['toko_id', 'tgl', 'username', 'action', 'detail'];
    protected $useTimestamps = true;
    protected $dateFormat    = 'datetime';
    protected $createdField  = 'tgl';
    protected $updatedField  = 'tgl';

    protected $beforeInsert = ['beforeInsertSetUsername'];
    protected $beforeUpdate = ['beforeUpdateSetUsername'];

    protected function beforeInsertSetUsername(array $data)
    {
        if (isset($data['data'])) {
            $data['data']['username'] = session()->username;
            $data['data']['toko_id'] = session()->toko_id;
        }

        return $data;
    }

    protected function beforeUpdateSetUsername(array $data)
    {
        if (isset($data['data'])) {
            $data['data']['username'] = session()->username;
            $data['data']['toko_id'] = session()->toko_id;
        }

        return $data;
    }

    // Add any additional configurations or methods here
}
