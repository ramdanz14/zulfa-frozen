<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateRoleUser extends Migration
{
    public function up()
    {
        $sql = "CREATE TABLE `role_user` (
  `level_id` VARCHAR(100) NOT NULL,
  `level_name` VARCHAR(100) NOT NULL,
  PRIMARY KEY (`level_id`)
) ENGINE=INNODB ;";

        $this->db->query($sql);
    }

    public function down()
    {
        $this->forge->dropTable("role_user", true);
    }
}
