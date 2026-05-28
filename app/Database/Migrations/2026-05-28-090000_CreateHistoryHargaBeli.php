<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateHistoryHargaBeli extends Migration
{
    public function up()
    {
        $this->db->query("
            CREATE TABLE IF NOT EXISTS `history_harga_beli` (
              `history_id` BIGINT AUTO_INCREMENT NOT NULL,
              `toko_id` VARCHAR(5) NOT NULL,
              `beli_id` VARCHAR(15) NOT NULL,
              `kode_item` VARCHAR(7) NOT NULL,
              `sat_id` VARCHAR(20) NOT NULL,
              `updtime` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
              `harga_pokok_old` INT(11) NOT NULL DEFAULT 0,
              `harga_pokok_new` INT(11) NOT NULL DEFAULT 0,
              `harga_jual_old` INT(11) NOT NULL DEFAULT 0,
              `harga_jual_new` INT(11) NOT NULL DEFAULT 0,
              PRIMARY KEY (`history_id`),
              KEY `idx_history_harga_beli` (`toko_id`, `kode_item`, `sat_id`, `updtime`)
            ) ENGINE=INNODB;
        ");
    }

    public function down()
    {
        $this->forge->dropTable('history_harga_beli', true);
    }
}
