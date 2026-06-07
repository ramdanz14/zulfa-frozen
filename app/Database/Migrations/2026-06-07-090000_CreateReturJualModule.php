<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateReturJualModule extends Migration
{
    public function up()
    {
        $this->db->query("
            CREATE TABLE IF NOT EXISTS `retur_jual` (
              `rj_id` VARCHAR(20) NOT NULL,
              `toko_id` VARCHAR(5) NOT NULL,
              `jual_id` VARCHAR(20) NOT NULL,
              `tanggal` DATETIME NOT NULL,
              `gross_retur` DECIMAL(15,2) NOT NULL DEFAULT 0.00,
              `updid` VARCHAR(100) DEFAULT NULL,
              `keterangan` VARCHAR(255) DEFAULT NULL,
              `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
              PRIMARY KEY (`rj_id`, `toko_id`),
              UNIQUE KEY `uniq_retur_jual_once` (`toko_id`, `jual_id`),
              KEY `idx_retur_jual_1` (`toko_id`, `tanggal`),
              CONSTRAINT `fk_retur_jual_penjualan` FOREIGN KEY (`jual_id`, `toko_id`)
                REFERENCES `penjualan` (`jual_id`, `toko_id`) ON DELETE RESTRICT ON UPDATE CASCADE
            ) ENGINE=InnoDB;
        ");

        $this->db->query("
            CREATE TABLE IF NOT EXISTS `retur_jual_detail` (
              `rj_id` VARCHAR(20) NOT NULL,
              `toko_id` VARCHAR(5) NOT NULL,
              `seq_no` INT(3) NOT NULL,
              `kode_item` VARCHAR(50) NOT NULL,
              `sat_id` VARCHAR(50) NOT NULL,
              `qty_jual` FLOAT NOT NULL DEFAULT 0,
              `qty_retur` FLOAT NOT NULL DEFAULT 0,
              `qty_konversi` FLOAT NOT NULL DEFAULT 1,
              `qty_stock` FLOAT NOT NULL DEFAULT 0,
              `price` DECIMAL(15,2) NOT NULL DEFAULT 0.00,
              `gross_retur` DECIMAL(15,2) NOT NULL DEFAULT 0.00,
              PRIMARY KEY (`rj_id`, `toko_id`, `seq_no`),
              CONSTRAINT `fk_retur_jual_detail` FOREIGN KEY (`rj_id`, `toko_id`)
                REFERENCES `retur_jual` (`rj_id`, `toko_id`) ON DELETE CASCADE ON UPDATE CASCADE
            ) ENGINE=InnoDB;
        ");
    }

    public function down()
    {
        $this->forge->dropTable('retur_jual_detail', true);
        $this->forge->dropTable('retur_jual', true);
    }
}
