<?php

namespace App\Models;

use CodeIgniter\Model;

class CustomerModel extends Model
{
    protected $table = 'customer';
    protected $primaryKey = 'cust_id';
    protected $returnType = 'object';
    protected $allowedFields = ['cust_id', 'nama', 'alamat', 'kontak', 'tgl_daftar', 'max_faktur', 'poin', 'updid'];
    protected $useTimestamps = true;
    protected $dateFormat = 'datetime';
    protected $createdField = 'updtime';
    protected $updatedField = 'updtime';

    protected $beforeInsert = ['beforeInsertSetAudit'];
    protected $beforeUpdate = ['beforeUpdateSetAudit'];

    protected function beforeInsertSetAudit(array $data)
    {
        if (isset($data['data'])) {
            $data['data']['updid'] = session()->username;
            if (empty($data['data']['tgl_daftar'])) {
                $data['data']['tgl_daftar'] = date('Y-m-d');
            }
            if ($data['data']['max_faktur'] === '' || ! isset($data['data']['max_faktur'])) {
                $data['data']['max_faktur'] = 3;
            }
            if ($data['data']['poin'] === '' || ! isset($data['data']['poin'])) {
                $data['data']['poin'] = 0;
            }
        }

        return $data;
    }

    protected function beforeUpdateSetAudit(array $data)
    {
        if (isset($data['data'])) {
            $data['data']['updid'] = session()->username;
        }

        return $data;
    }

    public function ajax($params)
    {
        $start = $params['start'];
        $length = $params['length'];
        $searchValue = $params['search_value'];
        $data = null;
        $totalFilter = null;
        $totalCount = $this->countAll();
        $queryLimit = $length != "-1" ? " limit $start, $length " : "";

        if (! empty($searchValue)) {
            $like = '%' . $this->db->escapeLikeString($searchValue) . '%';
            $totalFilter = $this->db->query("
                SELECT count(*) total
                FROM customer
                WHERE cust_id LIKE :search:
                   OR nama LIKE :search:
                   OR alamat LIKE :search:
                   OR kontak LIKE :search:
            ", ['search' => $like])->getRow();

            $data = $this->db->query("
                SELECT *
                FROM customer
                WHERE cust_id LIKE :search:
                   OR nama LIKE :search:
                   OR alamat LIKE :search:
                   OR kontak LIKE :search:
                ORDER BY cust_id ASC
                $queryLimit
            ", ['search' => $like])->getResult();
        } else {
            $data = $this->db->query("
                SELECT *
                FROM customer
                ORDER BY cust_id ASC
                $queryLimit
            ")->getResult();
        }

        return [
            'data' => $data,
            'total_count' => $totalCount ?? 0,
            'total_filtered' => $totalFilter->total ?? $totalCount,
        ];
    }

    public function getLastId()
    {
        $maxNow = $this->db->query("SELECT MAX(CAST(MID(cust_id,3,10) AS DECIMAL)) as kodex FROM customer;")->getRow();
        $noUrut = (int) ($maxNow->kodex ?? 0);
        $noUrut++;

        return 'KS' . sprintf('%05d', $noUrut);
    }
}
