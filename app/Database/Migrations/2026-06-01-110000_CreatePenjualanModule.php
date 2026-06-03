<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreatePenjualanModule extends Migration
{
    public function up()
    {
        $this->db->query("
            CREATE TABLE IF NOT EXISTS `penjualan` (
              `jual_id` VARCHAR(20) NOT NULL,
              `toko_id` VARCHAR(5) NOT NULL,
              `tgl` DATETIME NOT NULL,
              `cust_id` VARCHAR(20) NOT NULL DEFAULT 'CUST-GENERAL',
              `gross` DECIMAL(15,2) NOT NULL DEFAULT 0.00,
              `diskon_nota` DECIMAL(15,2) NOT NULL DEFAULT 0.00,
              `redeem_points` INT(11) NOT NULL DEFAULT 0,
              `redeem_nominal` DECIMAL(15,2) NOT NULL DEFAULT 0.00,
              `netto` DECIMAL(15,2) NOT NULL DEFAULT 0.00,
              `is_kredit` ENUM('1','0') NOT NULL DEFAULT '0',
              `status_bayar` ENUM('LUNAS','CICIL','BELUM') NOT NULL DEFAULT 'LUNAS',
              `sisa_piutang` DECIMAL(15,2) NOT NULL DEFAULT 0.00,
              `jatuh_tempo` DATE DEFAULT NULL,
              `cash_received` DECIMAL(15,2) NOT NULL DEFAULT 0.00,
              `cash_change` DECIMAL(15,2) NOT NULL DEFAULT 0.00,
              `earned_points` INT(11) NOT NULL DEFAULT 0,
              `updid` VARCHAR(100) DEFAULT NULL,
              `updtime` DATETIME DEFAULT NULL,
              PRIMARY KEY (`jual_id`,`toko_id`),
              KEY `idx_penjualan_monitoring` (`toko_id`, `cust_id`, `status_bayar`, `tgl`)
            ) ENGINE=InnoDB;
        ");

        $this->db->query("
            CREATE TABLE IF NOT EXISTS `penjualan_detail` (
              `jual_id` VARCHAR(20) NOT NULL,
              `toko_id` VARCHAR(5) NOT NULL,
              `kode_item` VARCHAR(50) NOT NULL,
              `sat_id` VARCHAR(50) NOT NULL,
              `qty_jual` FLOAT NOT NULL DEFAULT 1,
              `qty_konversi` FLOAT NOT NULL DEFAULT 1,
              `qty_stock` FLOAT NOT NULL DEFAULT 0,
              `harga_pokok` DECIMAL(15,2) NOT NULL DEFAULT 0.00,
              `price` DECIMAL(15,2) NOT NULL DEFAULT 0.00,
              `gross` DECIMAL(15,2) NOT NULL DEFAULT 0.00,
              `diskon_item` DECIMAL(15,2) NOT NULL DEFAULT 0.00,
              `netto` DECIMAL(15,2) NOT NULL DEFAULT 0.00,
              PRIMARY KEY (`jual_id`,`toko_id`,`kode_item`,`sat_id`),
              CONSTRAINT `fk_detail_penjualan_pos` FOREIGN KEY (`jual_id`, `toko_id`)
                REFERENCES `penjualan` (`jual_id`, `toko_id`) ON DELETE CASCADE ON UPDATE CASCADE
            ) ENGINE=InnoDB;
        ");

        $this->db->query("
            CREATE TABLE IF NOT EXISTS `penjualan_pembayaran` (
              `bayar_id` BIGINT AUTO_INCREMENT NOT NULL,
              `jual_id` VARCHAR(20) NOT NULL,
              `toko_id` VARCHAR(5) NOT NULL,
              `tgl_bayar` DATETIME NOT NULL,
              `cara_bayar` ENUM('TUNAI','TRANSFER','QRIS','POTONGAN_RETUR') NOT NULL,
              `nominal_bayar` DECIMAL(15,2) NOT NULL DEFAULT 0.00,
              `bank_nama` VARCHAR(50) DEFAULT NULL,
              `rekening_no` VARCHAR(50) DEFAULT NULL,
              `updid` VARCHAR(100) DEFAULT NULL,
              PRIMARY KEY (`bayar_id`),
              KEY `fk_pembayaran_penjualan_pos` (`jual_id`,`toko_id`),
              CONSTRAINT `fk_pembayaran_penjualan_pos` FOREIGN KEY (`jual_id`, `toko_id`)
                REFERENCES `penjualan` (`jual_id`, `toko_id`) ON DELETE CASCADE ON UPDATE CASCADE
            ) ENGINE=InnoDB;
        ");

        $this->db->query("
            CREATE TABLE IF NOT EXISTS `penjualan_void` (
              `void_id` BIGINT AUTO_INCREMENT NOT NULL,
              `toko_id` VARCHAR(5) NOT NULL,
              `username` VARCHAR(100) DEFAULT NULL,
              `payload_json` LONGTEXT DEFAULT NULL,
              `alasan` VARCHAR(255) DEFAULT NULL,
              `created_at` DATETIME NOT NULL,
              PRIMARY KEY (`void_id`),
              KEY `idx_penjualan_void_1` (`toko_id`, `created_at`)
            ) ENGINE=InnoDB;
        ");
    }

    public function down()
    {
        $this->forge->dropTable('penjualan_void', true);
        $this->forge->dropTable('penjualan_pembayaran', true);
        $this->forge->dropTable('penjualan_detail', true);
        $this->forge->dropTable('penjualan', true);
    }
}
