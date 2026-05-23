<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateTbUser extends Migration
{

    public function up()
    {
        $sql = "CREATE TABLE `tb_user` (
  `karyawan_id` CHAR(20) NOT NULL,
  `username` CHAR(50) NOT NULL,
  `fullname` VARCHAR(100) NOT NULL,
  `password` VARCHAR(50) DEFAULT '*23AE809DDACAF96AF0FD78ED04B6A265E05AA257',
  `email` VARCHAR(100) DEFAULT NULL,
  `phone` VARCHAR(15) DEFAULT NULL,
  `level_id` VARCHAR(100) NOT NULL,
  `active` ENUM('Y','N') DEFAULT 'Y',
  `avatar` VARCHAR(100) NOT NULL DEFAULT 'avatar.png',
  `alamat` VARCHAR(500) DEFAULT NULL,
  `absensi` ENUM('Y','N') NOT NULL,
  `toko_id` VARCHAR(5) DEFAULT NULL,
  `updid` VARCHAR(200) DEFAULT NULL,
  `updtime` DATETIME DEFAULT NULL,
  PRIMARY KEY (`karyawan_id`),
  KEY `tb_user_level_foreign` (`level_id`),
  CONSTRAINT `tb_user_level_foreign` FOREIGN KEY (`level_id`) REFERENCES `role_user` (`level_id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=INNODB ";

        $this->db->query($sql);
    }

    public function down()
    {
        $this->forge->dropTable("tb_user", true);
    }
}
