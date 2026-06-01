<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateHistoryPoinMember extends Migration
{
    public function up()
    {
        $this->db->query("
            CREATE TABLE IF NOT EXISTS `history_poin_member` (
              `history_id` BIGINT AUTO_INCREMENT NOT NULL,
              `toko_id` VARCHAR(5) NOT NULL,
              `cust_id` VARCHAR(7) NOT NULL,
              `trx_id` VARCHAR(30) DEFAULT NULL,
              `tanggal` DATETIME NOT NULL,
              `jenis` VARCHAR(10) NOT NULL,
              `nominal_transaksi` DECIMAL(14,2) NOT NULL DEFAULT 0,
              `nominal_per_poin` INT(11) NOT NULL DEFAULT 0,
              `poin_masuk` INT(11) NOT NULL DEFAULT 0,
              `poin_keluar` INT(11) NOT NULL DEFAULT 0,
              `poin_before` INT(11) NOT NULL DEFAULT 0,
              `poin_after` INT(11) NOT NULL DEFAULT 0,
              `keterangan` VARCHAR(255) DEFAULT NULL,
              `updid` VARCHAR(50) DEFAULT NULL,
              `updtime` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
              PRIMARY KEY (`history_id`),
              KEY `idx_history_poin_member_1` (`cust_id`, `tanggal`),
              KEY `idx_history_poin_member_2` (`toko_id`, `jenis`, `tanggal`)
            ) ENGINE=INNODB;
        ");
    }

    public function down()
    {
        $this->forge->dropTable('history_poin_member', true);
    }
}
