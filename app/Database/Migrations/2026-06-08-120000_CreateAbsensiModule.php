<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateAbsensiModule extends Migration
{
    public function up()
    {
        if (!$this->db->fieldExists('gaji', 'tb_user')) {
            $this->db->query("ALTER TABLE `tb_user` ADD COLUMN `gaji` DECIMAL(15,2) NOT NULL DEFAULT 0.00 AFTER `absensi`");
        }

        $this->db->query("
            CREATE TABLE IF NOT EXISTS `absensi_karyawan` (
              `absensi_id` BIGINT NOT NULL AUTO_INCREMENT,
              `tanggal` DATE NOT NULL,
              `karyawan_id` CHAR(20) NOT NULL,
              `toko_id` VARCHAR(5) NOT NULL,
              `status_absensi` ENUM('HADIR','MANGKIR','LIBUR') NOT NULL,
              `nominal_gaji` DECIMAL(15,2) NOT NULL DEFAULT 0.00,
              `keterangan` VARCHAR(150) DEFAULT NULL,
              `is_paid` ENUM('Y','N') NOT NULL DEFAULT 'N',
              `batch_id` VARCHAR(20) DEFAULT NULL,
              `paid_at` DATETIME DEFAULT NULL,
              `updid` VARCHAR(100) DEFAULT NULL,
              `updtime` DATETIME DEFAULT NULL,
              PRIMARY KEY (`absensi_id`),
              UNIQUE KEY `uniq_absensi_harian` (`tanggal`,`karyawan_id`),
              KEY `idx_absensi_tanggal` (`tanggal`,`is_paid`),
              KEY `idx_absensi_toko` (`toko_id`,`tanggal`),
              CONSTRAINT `fk_absensi_user` FOREIGN KEY (`karyawan_id`) REFERENCES `tb_user` (`karyawan_id`) ON UPDATE CASCADE,
              CONSTRAINT `fk_absensi_toko` FOREIGN KEY (`toko_id`) REFERENCES `toko` (`toko_id`) ON UPDATE CASCADE
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
        ");

        $this->db->query("
            CREATE TABLE IF NOT EXISTS `absensi_pembayaran` (
              `batch_id` VARCHAR(20) NOT NULL,
              `tanggal_bayar` DATE NOT NULL,
              `periode_start` DATE NOT NULL,
              `periode_end` DATE NOT NULL,
              `total_nominal` DECIMAL(15,2) NOT NULL DEFAULT 0.00,
              `total_karyawan` INT NOT NULL DEFAULT 0,
              `updid` VARCHAR(100) DEFAULT NULL,
              `created_at` DATETIME DEFAULT NULL,
              PRIMARY KEY (`batch_id`),
              KEY `idx_absensi_pembayaran_tanggal` (`tanggal_bayar`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
        ");

        $this->db->query("
            CREATE TABLE IF NOT EXISTS `absensi_pembayaran_detail` (
              `batch_id` VARCHAR(20) NOT NULL,
              `absensi_id` BIGINT NOT NULL,
              `kas_id` INT(11) DEFAULT NULL,
              PRIMARY KEY (`batch_id`,`absensi_id`),
              KEY `idx_absensi_payment_detail_kas` (`kas_id`),
              CONSTRAINT `fk_absensi_payment_header` FOREIGN KEY (`batch_id`) REFERENCES `absensi_pembayaran` (`batch_id`) ON DELETE CASCADE ON UPDATE CASCADE,
              CONSTRAINT `fk_absensi_payment_absensi` FOREIGN KEY (`absensi_id`) REFERENCES `absensi_karyawan` (`absensi_id`) ON DELETE CASCADE ON UPDATE CASCADE,
              CONSTRAINT `fk_absensi_payment_kas` FOREIGN KEY (`kas_id`) REFERENCES `kas_mutasi` (`kas_id`) ON UPDATE CASCADE
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
        ");

        if ($this->db->tableExists('akun_kas')) {
            $this->db->query("INSERT IGNORE INTO `akun_kas` (`nama_akun`,`jenis_akun`,`updid`) VALUES ('GAJI','KELUAR','SYSTEM')");
        }

        if ($this->db->tableExists('tb_menu')) {
            $this->db->query("
                INSERT IGNORE INTO `tb_menu` (`menu_id`,`menu_name`,`link`,`icon`,`header_menu`,`urutan`)
                VALUES ('absensi','Absensi','absensi','ti ti-calendar-user','SDM',10)
            ");
        }
    }

    public function down()
    {
        if ($this->db->tableExists('tb_menu')) {
            $this->db->query("DELETE FROM `tb_menu` WHERE `menu_id`='absensi'");
        }

        $this->forge->dropTable('absensi_pembayaran_detail', true);
        $this->forge->dropTable('absensi_pembayaran', true);
        $this->forge->dropTable('absensi_karyawan', true);

        if ($this->db->fieldExists('gaji', 'tb_user')) {
            $this->db->query("ALTER TABLE `tb_user` DROP COLUMN `gaji`");
        }
    }
}
