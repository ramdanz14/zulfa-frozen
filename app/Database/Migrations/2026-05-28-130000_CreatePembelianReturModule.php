<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreatePembelianReturModule extends Migration
{
    public function up()
    {
        $this->db->query("
            CREATE TABLE IF NOT EXISTS `pembelian_retur` (
              `toko_id` VARCHAR(5) NOT NULL,
              `retur_id` VARCHAR(15) NOT NULL,
              `beli_id` VARCHAR(15) NOT NULL,
              `tanggal` DATE NOT NULL,
              `total_retur` DECIMAL(15,2) NOT NULL DEFAULT 0.00,
              `status_retur` ENUM('DRAFT', 'SELESAI') NOT NULL DEFAULT 'DRAFT',
              `username` VARCHAR(50) NOT NULL,
              `keterangan` VARCHAR(250) DEFAULT NULL,
              `updtime` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
              PRIMARY KEY (`toko_id`, `retur_id`),
              CONSTRAINT `fk_retur_ke_pembelian` FOREIGN KEY (`toko_id`, `beli_id`)
                REFERENCES `pembelian` (`toko_id`, `beli_id`) ON DELETE RESTRICT ON UPDATE CASCADE
            ) ENGINE=INNODB;
        ");

        $this->db->query("
            CREATE TABLE IF NOT EXISTS `pembelian_retur_detail` (
              `toko_id` VARCHAR(5) NOT NULL,
              `retur_id` VARCHAR(15) NOT NULL,
              `seq_no` INT(3) NOT NULL,
              `kode_item` VARCHAR(7) NOT NULL,
              `qty_retur` FLOAT NOT NULL DEFAULT 0,
              `sat_id` VARCHAR(20) NOT NULL,
              `qty_konversi` FLOAT NOT NULL DEFAULT 1,
              `qty_stok` FLOAT NOT NULL,
              `price` DECIMAL(15,2) NOT NULL,
              `gross_retur` DECIMAL(15,2) NOT NULL,
              PRIMARY KEY (`toko_id`, `retur_id`, `seq_no`),
              CONSTRAINT `fk_retur_detail` FOREIGN KEY (`toko_id`, `retur_id`)
                REFERENCES `pembelian_retur` (`toko_id`, `retur_id`) ON DELETE CASCADE ON UPDATE CASCADE
            ) ENGINE=INNODB;
        ");

        $paymentField = $this->db->query("SHOW COLUMNS FROM pembelian_pembayaran LIKE 'cara_bayar'")->getRowArray();
        if ($paymentField && strpos((string) ($paymentField['Type'] ?? ''), 'POTONGAN RETUR') === false) {
            $this->db->query("
                ALTER TABLE `pembelian_pembayaran`
                MODIFY COLUMN `cara_bayar` ENUM('TUNAI', 'TRANSFER', 'POTONGAN RETUR') NOT NULL
            ");
        }

        if (!$this->db->fieldExists('retur_id', 'pembelian_pembayaran')) {
            $this->db->query("
                ALTER TABLE `pembelian_pembayaran`
                ADD COLUMN `retur_id` VARCHAR(15) NULL DEFAULT NULL AFTER `beli_id`,
                ADD KEY `idx_pembayaran_retur` (`toko_id`, `retur_id`)
            ");
        }
    }

    public function down()
    {
        if ($this->db->fieldExists('retur_id', 'pembelian_pembayaran')) {
            $this->db->query("ALTER TABLE `pembelian_pembayaran` DROP COLUMN `retur_id`");
        }

        $paymentField = $this->db->query("SHOW COLUMNS FROM pembelian_pembayaran LIKE 'cara_bayar'")->getRowArray();
        if ($paymentField && strpos((string) ($paymentField['Type'] ?? ''), 'POTONGAN RETUR') !== false) {
            $this->db->query("
                ALTER TABLE `pembelian_pembayaran`
                MODIFY COLUMN `cara_bayar` ENUM('TUNAI', 'TRANSFER') NOT NULL
            ");
        }

        $this->forge->dropTable('pembelian_retur_detail', true);
        $this->forge->dropTable('pembelian_retur', true);
    }
}
