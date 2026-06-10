<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class UpdateKasDefaultsForPayrollAndRetur extends Migration
{
    public function up()
    {
        $this->db->query("
            UPDATE `akun_kas`
            SET `jenis_akun`='KELUAR', `flag_beban`='Y', `updid`='SYSTEM'
            WHERE `nama_akun`='GAJI'
        ");

        $this->db->query("
            UPDATE `akun_kas`
            SET `flag_beban`='N'
            WHERE `nama_akun` IN ('RETUR PEMBELIAN', 'RETUR PENJUALAN')
        ");

        $this->db->query("
            UPDATE `kas_mutasi`
            SET `tipe_mutasi`='OPERASIONAL',
                `saldo_channel`='CASH',
                `saldo_asal`=NULL,
                `saldo_tujuan`=NULL
            WHERE `nama_akun` IN ('GAJI', 'RETUR PEMBELIAN', 'RETUR PENJUALAN')
        ");
    }

    public function down()
    {
        $this->db->query("UPDATE `akun_kas` SET `flag_beban`='N' WHERE `nama_akun`='GAJI'");
    }
}
