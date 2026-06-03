<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AlterPenjualanDetailSeqNoAndCreatePosRunningText extends Migration
{
    public function up()
    {
        $fields = $this->db->getFieldData('penjualan_detail');
        $fieldNames = array_map(static fn($field) => strtolower($field->name), $fields);

        if (! in_array('seq_no', $fieldNames, true)) {
            $this->db->query("ALTER TABLE `penjualan_detail` ADD COLUMN `seq_no` INT(4) NULL AFTER `toko_id`");

            $this->db->query("
                SET @prev_jual := '', @prev_toko := '', @seq := 0;
            ");
            $this->db->query("
                UPDATE `penjualan_detail` d
                INNER JOIN (
                    SELECT
                        `jual_id`,
                        `toko_id`,
                        `kode_item`,
                        `sat_id`,
                        @seq := IF(@prev_jual = `jual_id` AND @prev_toko = `toko_id`, @seq + 1, 1) AS next_seq,
                        @prev_jual := `jual_id` AS _set_prev_jual,
                        @prev_toko := `toko_id` AS _set_prev_toko
                    FROM `penjualan_detail`
                    ORDER BY `jual_id`, `toko_id`, `kode_item`, `sat_id`
                ) x
                    ON x.jual_id = d.jual_id
                    AND x.toko_id = d.toko_id
                    AND x.kode_item = d.kode_item
                    AND x.sat_id = d.sat_id
                SET d.seq_no = x.next_seq
            ");

            $this->db->query("ALTER TABLE `penjualan_detail` MODIFY COLUMN `seq_no` INT(4) NOT NULL");
            $this->db->query("ALTER TABLE `penjualan_detail` DROP PRIMARY KEY");
            $this->db->query("ALTER TABLE `penjualan_detail` ADD PRIMARY KEY (`jual_id`, `toko_id`, `seq_no`)");
            $this->db->query("ALTER TABLE `penjualan_detail` ADD KEY `idx_penjualan_detail_item` (`toko_id`, `kode_item`, `sat_id`)");
        }

        $this->db->query("
            CREATE TABLE IF NOT EXISTS `pos_running_text` (
              `running_text_id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
              `judul` VARCHAR(120) DEFAULT NULL,
              `isi_pengumuman` VARCHAR(255) NOT NULL,
              `is_active` ENUM('Y','N') NOT NULL DEFAULT 'Y',
              `urutan` INT(4) NOT NULL DEFAULT 0,
              `updid` VARCHAR(100) DEFAULT NULL,
              `updtime` DATETIME DEFAULT NULL,
              PRIMARY KEY (`running_text_id`),
              KEY `idx_pos_running_text_active` (`is_active`, `urutan`, `running_text_id`)
            ) ENGINE=InnoDB;
        ");
    }

    public function down()
    {
        $fields = $this->db->getFieldData('penjualan_detail');
        $fieldNames = array_map(static fn($field) => strtolower($field->name), $fields);

        if (in_array('seq_no', $fieldNames, true)) {
            $this->db->query("ALTER TABLE `penjualan_detail` DROP PRIMARY KEY");
            $this->db->query("ALTER TABLE `penjualan_detail` DROP KEY `idx_penjualan_detail_item`");
            $this->db->query("ALTER TABLE `penjualan_detail` ADD PRIMARY KEY (`jual_id`, `toko_id`, `kode_item`, `sat_id`)");
            $this->db->query("ALTER TABLE `penjualan_detail` DROP COLUMN `seq_no`");
        }

        $this->forge->dropTable('pos_running_text', true);
    }
}
