<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateTransferAntarTokoModule extends Migration
{
    public function up()
    {
        $this->db->query("
            CREATE TABLE IF NOT EXISTS `transfer_toko` (
              `transfer_id` VARCHAR(20) NOT NULL,
              `gudang_toko_id` VARCHAR(5) NOT NULL,
              `tujuan_toko_id` VARCHAR(5) NOT NULL,
              `po_toko_id` VARCHAR(5) NOT NULL,
              `po_beli_id` VARCHAR(15) NOT NULL,
              `jual_id` VARCHAR(20) DEFAULT NULL,
              `beli_id` VARCHAR(15) DEFAULT NULL,
              `tanggal_po` DATE DEFAULT NULL,
              `tanggal_transfer` DATE NOT NULL,
              `tanggal_kirim` DATETIME DEFAULT NULL,
              `tanggal_approve` DATETIME DEFAULT NULL,
              `tanggal_reject` DATETIME DEFAULT NULL,
              `status_transfer` ENUM('DRAFT','KIRIM','APPROVED','REJECTED') NOT NULL DEFAULT 'DRAFT',
              `keterangan` VARCHAR(250) DEFAULT NULL,
              `created_by` VARCHAR(50) DEFAULT NULL,
              `approved_by` VARCHAR(50) DEFAULT NULL,
              `rejected_by` VARCHAR(50) DEFAULT NULL,
              `updtime` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
              PRIMARY KEY (`transfer_id`),
              KEY `idx_transfer_toko_1` (`gudang_toko_id`, `status_transfer`, `tanggal_transfer`),
              KEY `idx_transfer_toko_2` (`tujuan_toko_id`, `status_transfer`, `tanggal_transfer`),
              KEY `idx_transfer_toko_3` (`po_toko_id`, `po_beli_id`)
            ) ENGINE=InnoDB;
        ");

        $this->db->query("
            CREATE TABLE IF NOT EXISTS `transfer_toko_detail` (
              `transfer_id` VARCHAR(20) NOT NULL,
              `seq_no` INT(4) NOT NULL,
              `kode_item` VARCHAR(50) NOT NULL,
              `sat_id` VARCHAR(50) NOT NULL,
              `qty_po` FLOAT NOT NULL DEFAULT 0,
              `qty_kirim` FLOAT NOT NULL DEFAULT 0,
              `qty_konversi` FLOAT NOT NULL DEFAULT 1,
              `qty_stock` FLOAT NOT NULL DEFAULT 0,
              `harga_pokok` DECIMAL(15,2) NOT NULL DEFAULT 0.00,
              `harga_jual` DECIMAL(15,2) NOT NULL DEFAULT 0.00,
              `gross` DECIMAL(15,2) NOT NULL DEFAULT 0.00,
              PRIMARY KEY (`transfer_id`, `seq_no`),
              KEY `idx_transfer_toko_detail_item` (`kode_item`, `sat_id`),
              CONSTRAINT `fk_transfer_toko_detail_header` FOREIGN KEY (`transfer_id`)
                REFERENCES `transfer_toko` (`transfer_id`) ON DELETE CASCADE ON UPDATE CASCADE
            ) ENGINE=InnoDB;
        ");

        $this->db->query("REPLACE INTO `const` (`rkey`,`nilai`) VALUES ('markup_gudang','2')");
    }

    public function down()
    {
        if ($this->db->tableExists('akses_menu')) {
            $this->db->query("DELETE FROM `akses_menu` WHERE `menu_id`='transfer'");
        }

        if ($this->db->tableExists('tb_menu')) {
            $this->db->query("DELETE FROM `tb_menu` WHERE `menu_id`='transfer'");
        }

        $this->forge->dropTable('transfer_toko_detail', true);
        $this->forge->dropTable('transfer_toko', true);
    }
}
