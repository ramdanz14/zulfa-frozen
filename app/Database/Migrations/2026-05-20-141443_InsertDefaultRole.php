<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class InsertDefaultRole extends Migration
{
    public function up()
    {
        $data = [
            [
                'level_id' => 'admin',
                'level_name' => 'Administrasi'
            ],
            [
                'level_id' => 'ceo',
                'level_name' => 'Pemilik'
            ],
            [
                'level_id' => 'kasir',
                'level_name' => 'Kasir'
            ],
            [
                'level_id' => 'root',
                'level_name' => 'IT Staff'
            ]

        ];
        $this->db->table('role_user')->insertBatch($data);
    }

    public function down()
    {
        $this->db->query("SET FOREIGN_KEY_CHECKS = 0;");
        $this->db->table('role_user')->truncate();
        $this->db->query("SET FOREIGN_KEY_CHECKS = 1;");
    }
}
