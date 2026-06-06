<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateAkunKasAndKasMutasi extends Migration
{
    public function up()
    {
        $this->db->query("
            CREATE TABLE IF NOT EXISTS `akun_kas` (
              `nama_akun` VARCHAR(50) NOT NULL,
              `jenis_akun` ENUM('MASUK','KELUAR') NOT NULL,
              `updid` VARCHAR(100) DEFAULT NULL,
              PRIMARY KEY (`nama_akun`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
        ");

        $this->db->query("
            CREATE TABLE IF NOT EXISTS `kas_mutasi` (
              `kas_id` INT(11) NOT NULL AUTO_INCREMENT,
              `tanggal` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP(),
              `toko_id` VARCHAR(5) NOT NULL,
              `nama_akun` VARCHAR(50) NOT NULL,
              `nominal` INT(11) NOT NULL DEFAULT 0,
              `karyawan_id` VARCHAR(20) NOT NULL,
              `keterangan` VARCHAR(150) DEFAULT NULL,
              `updid` VARCHAR(100) DEFAULT NULL,
              PRIMARY KEY (`kas_id`),
              KEY `idx_kas_mutasi_1` (`toko_id`, `tanggal`),
              KEY `idx_kas_mutasi_2` (`nama_akun`, `tanggal`),
              KEY `idx_kas_mutasi_3` (`karyawan_id`, `tanggal`),
              CONSTRAINT `fk_kas_toko` FOREIGN KEY (`toko_id`) REFERENCES `toko` (`toko_id`) ON UPDATE CASCADE,
              CONSTRAINT `fk_kas_akun` FOREIGN KEY (`nama_akun`) REFERENCES `akun_kas` (`nama_akun`) ON UPDATE CASCADE,
              CONSTRAINT `fk_kas_user` FOREIGN KEY (`karyawan_id`) REFERENCES `tb_user` (`karyawan_id`) ON UPDATE CASCADE
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
        ");
    }

    public function down()
    {
        $this->forge->dropTable('kas_mutasi', true);
        $this->forge->dropTable('akun_kas', true);
    }
}
