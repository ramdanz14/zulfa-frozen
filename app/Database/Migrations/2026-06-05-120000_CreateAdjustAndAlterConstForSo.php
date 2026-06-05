<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateAdjustAndAlterConstForSo extends Migration
{
    public function up()
    {
        $this->db->query("
            CREATE TABLE IF NOT EXISTS `adjust` (
              `so_id` INT(11) NOT NULL AUTO_INCREMENT,
              `toko_id` VARCHAR(4) NOT NULL,
              `tanggal` DATETIME DEFAULT NULL,
              `kode_item` VARCHAR(7) DEFAULT NULL,
              `istype` VARCHAR(10) DEFAULT NULL,
              `seq_no` INT(3) DEFAULT NULL,
              `sat_id` VARCHAR(50) DEFAULT NULL,
              `qty_so` FLOAT DEFAULT NULL,
              `qty_konversi` FLOAT DEFAULT NULL,
              `qty_stock` FLOAT DEFAULT NULL,
              `price` INT(11) DEFAULT NULL,
              `gross` INT(11) DEFAULT NULL,
              `keterangan` VARCHAR(100) DEFAULT NULL,
              `updid` VARCHAR(100) DEFAULT NULL,
              PRIMARY KEY (`so_id`),
              KEY `idx_adjust_1` (`toko_id`, `istype`, `tanggal`),
              KEY `idx_adjust_2` (`kode_item`, `sat_id`)
            ) ENGINE=InnoDB;
        ");

        $fieldNames = array_map(
            static fn(array $row): string => strtolower((string) ($row['Field'] ?? '')),
            $this->db->query("SHOW COLUMNS FROM `const`")->getResultArray()
        );

        if (!in_array('toko_id', $fieldNames, true)) {
            $this->db->query("ALTER TABLE `const` ADD COLUMN `toko_id` VARCHAR(4) DEFAULT NULL AFTER `rkey`");
            $this->db->query("CREATE INDEX `idx_const_rkey_toko` ON `const` (`rkey`, `toko_id`)");
        }
    }

    public function down()
    {
        $fieldNames = array_map(
            static fn(array $row): string => strtolower((string) ($row['Field'] ?? '')),
            $this->db->query("SHOW COLUMNS FROM `const`")->getResultArray()
        );

        if (in_array('toko_id', $fieldNames, true)) {
            $indexes = $this->db->query("SHOW INDEX FROM `const` WHERE Key_name='idx_const_rkey_toko'")->getResultArray();
            if (!empty($indexes)) {
                $this->db->query("DROP INDEX `idx_const_rkey_toko` ON `const`");
            }
            $this->db->query("ALTER TABLE `const` DROP COLUMN `toko_id`");
        }

        $this->forge->dropTable('adjust', true);
    }
}
