<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreatePembelianModule extends Migration
{
    public function up()
    {
        $this->db->query("
            CREATE TABLE IF NOT EXISTS `pembelian` (
              `toko_id` VARCHAR(5) NOT NULL,
              `beli_id` VARCHAR(15) NOT NULL,
              `tanggal` DATE NOT NULL,
              `supco` VARCHAR(10) NOT NULL,
              `invoice` VARCHAR(50) NOT NULL,
              `total_gross` DECIMAL(15,2) NOT NULL DEFAULT 0.00,
              `total_bayar` DECIMAL(15,2) NOT NULL DEFAULT 0.00,
              `sisa_bayar` DECIMAL(15,2) NOT NULL DEFAULT 0.00,
              `is_kredit` TINYINT(1) NOT NULL DEFAULT 0,
              `status_nota` ENUM('PO', 'TERIMA') NOT NULL DEFAULT 'PO',
              `status_bayar` ENUM('BELUM', 'CICIL', 'LUNAS') NOT NULL DEFAULT 'BELUM',
              `jatuh_tempo` DATE DEFAULT NULL,
              `username` VARCHAR(50) NOT NULL,
              `keterangan` VARCHAR(250) DEFAULT NULL,
              `updtime` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
              PRIMARY KEY (`toko_id`, `beli_id`),
              KEY `idx_monitoring_hutang` (`toko_id`, `is_kredit`, `status_bayar`, `jatuh_tempo`)
            ) ENGINE=INNODB;
        ");

        $this->db->query("
            CREATE TABLE IF NOT EXISTS `pembelian_detail` (
              `toko_id` VARCHAR(5) NOT NULL,
              `beli_id` VARCHAR(15) NOT NULL,
              `seq_no` INT(3) NOT NULL,
              `kode_item` VARCHAR(7) NOT NULL,
              `qty_beli` FLOAT NOT NULL,
              `sat_id` VARCHAR(20) NOT NULL,
              `qty_konversi` FLOAT NOT NULL DEFAULT 1,
              `qty_stock` FLOAT NOT NULL,
              `price` DECIMAL(15,2) NOT NULL,
              `gross` DECIMAL(15,2) NOT NULL,
              PRIMARY KEY (`toko_id`, `beli_id`, `seq_no`),
              CONSTRAINT `fk_pembelian_detail` FOREIGN KEY (`toko_id`, `beli_id`)
                REFERENCES `pembelian` (`toko_id`, `beli_id`) ON DELETE CASCADE ON UPDATE CASCADE
            ) ENGINE=INNODB;
        ");

        $this->db->query("
            CREATE TABLE IF NOT EXISTS `pembelian_pembayaran` (
              `bayar_id` INT AUTO_INCREMENT NOT NULL,
              `toko_id` VARCHAR(5) NOT NULL,
              `beli_id` VARCHAR(15) NOT NULL,
              `tanggal_bayar` DATETIME NOT NULL,
              `cara_bayar` ENUM('TUNAI', 'TRANSFER') NOT NULL,
              `jumlah_bayar` DECIMAL(15,2) NOT NULL,
              `bank_nama` VARCHAR(50) DEFAULT NULL,
              `rekening_no` VARCHAR(50) DEFAULT NULL,
              `username` VARCHAR(50) NOT NULL,
              PRIMARY KEY (`bayar_id`),
              CONSTRAINT `fk_pembayaran_beli` FOREIGN KEY (`toko_id`, `beli_id`)
                REFERENCES `pembelian` (`toko_id`, `beli_id`) ON DELETE CASCADE ON UPDATE CASCADE
            ) ENGINE=INNODB;
        ");
    }

    public function down()
    {
        $this->forge->dropTable('pembelian_pembayaran', true);
        $this->forge->dropTable('pembelian_detail', true);
        $this->forge->dropTable('pembelian', true);
    }
}
