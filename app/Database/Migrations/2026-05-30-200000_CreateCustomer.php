<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateCustomer extends Migration
{
    public function up()
    {
        $this->db->query("
            CREATE TABLE IF NOT EXISTS `customer` (
              `cust_id` VARCHAR(7) NOT NULL,
              `nama` VARCHAR(200) NOT NULL,
              `alamat` VARCHAR(150) NOT NULL,
              `kontak` VARCHAR(13) NOT NULL,
              `tgl_daftar` DATE NOT NULL,
              `max_faktur` INT(3) NOT NULL DEFAULT 3,
              `poin` INT(6) NOT NULL DEFAULT 0,
              `updid` VARCHAR(50) DEFAULT NULL,
              `updtime` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
              PRIMARY KEY (`cust_id`)
            ) ENGINE=INNODB;
        ");
    }

    public function down()
    {
        $this->forge->dropTable('customer', true);
    }
}
