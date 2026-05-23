<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateToko extends Migration
{

    public function up()
    {
        $sql = "CREATE TABLE `toko` (
  `toko_id` VARCHAR(5) NOT NULL,
  `toko_nama` VARCHAR(50) NOT NULL,
  `toko_alamat` VARCHAR(100) NOT NULL,
  `toko_theme` VARCHAR(100) NOT NULL DEFAULT 'Aqua_Theme',
  `toko_phone` VARCHAR(15) NOT NULL,
  `flag_gudang` boolean,
  `updid` VARCHAR(100) DEFAULT NULL,
  `updtime` DATETIME DEFAULT NULL,
  PRIMARY KEY (`toko_id`)
) ENGINE=INNODB ;";

        $this->db->query($sql);
    }

    public function down()
    {
        $this->forge->dropTable("toko", true);
    }
}
