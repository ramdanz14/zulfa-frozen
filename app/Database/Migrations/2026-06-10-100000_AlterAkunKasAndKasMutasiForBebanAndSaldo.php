<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AlterAkunKasAndKasMutasiForBebanAndSaldo extends Migration
{
    public function up()
    {
        $this->db->query("
            ALTER TABLE `akun_kas`
            ADD COLUMN `flag_beban` ENUM('Y','N') NOT NULL DEFAULT 'N' AFTER `jenis_akun`
        ");

        $this->db->query("
            ALTER TABLE `kas_mutasi`
            MODIFY COLUMN `nama_akun` VARCHAR(50) NULL,
            ADD COLUMN `tipe_mutasi` ENUM('OPERASIONAL','PINDAH_SALDO') NOT NULL DEFAULT 'OPERASIONAL' AFTER `nama_akun`,
            ADD COLUMN `saldo_channel` ENUM('CASH','NONCASH') NOT NULL DEFAULT 'CASH' AFTER `tipe_mutasi`,
            ADD COLUMN `saldo_asal` ENUM('CASH','NONCASH') NULL AFTER `saldo_channel`,
            ADD COLUMN `saldo_tujuan` ENUM('CASH','NONCASH') NULL AFTER `saldo_asal`,
            ADD KEY `idx_kas_mutasi_4` (`tipe_mutasi`, `saldo_channel`, `tanggal`)
        ");
    }

    public function down()
    {
        $this->db->query("ALTER TABLE `kas_mutasi` DROP KEY `idx_kas_mutasi_4`");
        $this->db->query("
            ALTER TABLE `kas_mutasi`
            DROP COLUMN `saldo_tujuan`,
            DROP COLUMN `saldo_asal`,
            DROP COLUMN `saldo_channel`,
            DROP COLUMN `tipe_mutasi`,
            MODIFY COLUMN `nama_akun` VARCHAR(50) NOT NULL
        ");
        $this->db->query("ALTER TABLE `akun_kas` DROP COLUMN `flag_beban`");
    }
}
