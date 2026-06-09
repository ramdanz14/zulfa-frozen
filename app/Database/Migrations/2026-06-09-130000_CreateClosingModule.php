<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateClosingModule extends Migration
{
    public function up()
    {
        $columns = array_map(
            static fn(array $row): string => strtolower((string) ($row['Field'] ?? '')),
            $this->db->query("SHOW COLUMNS FROM `const`")->getResultArray()
        );

        if (in_array('toko_id', $columns, true)) {
            $primaryRows = $this->db->query("SHOW INDEX FROM `const` WHERE Key_name='PRIMARY'")->getResultArray();
            if (count($primaryRows) === 1) {
                $this->db->query("ALTER TABLE `const` DROP PRIMARY KEY");
                $this->db->query("ALTER TABLE `const` ADD UNIQUE KEY `uniq_const_rkey_toko` (`rkey`, `toko_id`)");
            }
        }

        $this->db->query("
            CREATE TABLE IF NOT EXISTS `saldo_cash` (
              `toko_id` VARCHAR(5) NOT NULL,
              `tahun` INT NOT NULL,
              `bulan` INT NOT NULL,
              `periode` DATE NOT NULL,
              `saldo_awal_tunai` DECIMAL(15,2) NOT NULL DEFAULT 0.00,
              `saldo_awal_transfer` DECIMAL(15,2) NOT NULL DEFAULT 0.00,
              `saldo_awal_qris` DECIMAL(15,2) NOT NULL DEFAULT 0.00,
              `pos_tunai` DECIMAL(15,2) NOT NULL DEFAULT 0.00,
              `pos_transfer` DECIMAL(15,2) NOT NULL DEFAULT 0.00,
              `pos_qris` DECIMAL(15,2) NOT NULL DEFAULT 0.00,
              `bayar_piutang_tunai` DECIMAL(15,2) NOT NULL DEFAULT 0.00,
              `bayar_piutang_transfer` DECIMAL(15,2) NOT NULL DEFAULT 0.00,
              `bayar_piutang_qris` DECIMAL(15,2) NOT NULL DEFAULT 0.00,
              `bayar_hutang_tunai` DECIMAL(15,2) NOT NULL DEFAULT 0.00,
              `bayar_hutang_transfer` DECIMAL(15,2) NOT NULL DEFAULT 0.00,
              `kas_masuk` DECIMAL(15,2) NOT NULL DEFAULT 0.00,
              `kas_keluar` DECIMAL(15,2) NOT NULL DEFAULT 0.00,
              `saldo_tunai` DECIMAL(15,2) NOT NULL DEFAULT 0.00,
              `saldo_transfer` DECIMAL(15,2) NOT NULL DEFAULT 0.00,
              `saldo_qris` DECIMAL(15,2) NOT NULL DEFAULT 0.00,
              `saldo_all` DECIMAL(15,2) NOT NULL DEFAULT 0.00,
              `closed_at` DATETIME DEFAULT NULL,
              `closed_by` VARCHAR(100) DEFAULT NULL,
              PRIMARY KEY (`toko_id`, `tahun`, `bulan`),
              KEY `idx_saldo_cash_periode` (`periode`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
        ");

        $this->db->query("
            CREATE TABLE IF NOT EXISTS `closing_log` (
              `closing_id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
              `toko_id` VARCHAR(5) NOT NULL,
              `periode` DATE NOT NULL,
              `mode` ENUM('WEB','CLI','RECLOSE') NOT NULL DEFAULT 'WEB',
              `status` ENUM('SUCCESS','ERROR') NOT NULL,
              `message` VARCHAR(255) DEFAULT NULL,
              `payload_json` LONGTEXT DEFAULT NULL,
              `created_by` VARCHAR(100) DEFAULT NULL,
              `created_at` DATETIME NOT NULL,
              PRIMARY KEY (`closing_id`),
              KEY `idx_closing_log_1` (`toko_id`, `periode`, `created_at`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
        ");
    }

    public function down()
    {
        $this->forge->dropTable('closing_log', true);
        $this->forge->dropTable('saldo_cash', true);
    }
}
