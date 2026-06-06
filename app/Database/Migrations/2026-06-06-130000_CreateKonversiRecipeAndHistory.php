<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateKonversiRecipeAndHistory extends Migration
{
    public function up()
    {
        $this->db->query("
            CREATE TABLE IF NOT EXISTS `konversi_recipe` (
              `recipe_id` BIGINT NOT NULL AUTO_INCREMENT,
              `kode_item_asal` VARCHAR(7) NOT NULL,
              `sat_asal` VARCHAR(50) NOT NULL,
              `qty_asal` FLOAT NOT NULL DEFAULT 0,
              `kode_item_hasil` VARCHAR(7) NOT NULL,
              `sat_hasil` VARCHAR(50) NOT NULL,
              `qty_hasil` FLOAT NOT NULL DEFAULT 0,
              `updid` VARCHAR(100) DEFAULT NULL,
              `updtime` DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
              PRIMARY KEY (`recipe_id`),
              KEY `idx_recipe_hasil` (`kode_item_hasil`, `sat_hasil`),
              KEY `idx_recipe_asal` (`kode_item_asal`, `sat_asal`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
        ");

        $this->db->query("
            CREATE TABLE IF NOT EXISTS `his_konversi` (
              `his_id` BIGINT NOT NULL AUTO_INCREMENT,
              `toko_id` VARCHAR(5) NOT NULL,
              `konversi_id` VARCHAR(20) NOT NULL,
              `tanggal` DATETIME NOT NULL,
              `kode_item` VARCHAR(7) NOT NULL,
              `sat_id` VARCHAR(50) NOT NULL,
              `role_item` VARCHAR(10) NOT NULL,
              `qty_formula` FLOAT DEFAULT NULL,
              `qty_transaksi` FLOAT DEFAULT NULL,
              `qty_hasil` FLOAT DEFAULT NULL,
              `qty_konversi` FLOAT DEFAULT NULL,
              `qty_stock` FLOAT DEFAULT NULL,
              `hpp_satuan` DECIMAL(15,2) DEFAULT NULL,
              `total_hpp` DECIMAL(15,2) DEFAULT NULL,
              `hpp_base_before` DECIMAL(15,4) DEFAULT NULL,
              `hpp_base_after` DECIMAL(15,4) DEFAULT NULL,
              `hpp_sat_before` DECIMAL(15,2) DEFAULT NULL,
              `hpp_sat_after` DECIMAL(15,2) DEFAULT NULL,
              `formula_text` TEXT DEFAULT NULL,
              `keterangan` VARCHAR(255) DEFAULT NULL,
              `updid` VARCHAR(100) DEFAULT NULL,
              PRIMARY KEY (`his_id`),
              KEY `idx_his_konversi_1` (`toko_id`, `konversi_id`, `tanggal`),
              KEY `idx_his_konversi_2` (`toko_id`, `kode_item`, `role_item`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
        ");
    }

    public function down()
    {
        $this->forge->dropTable('his_konversi', true);
        $this->forge->dropTable('konversi_recipe', true);
    }
}
