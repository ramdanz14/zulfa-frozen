<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AlterPembelianReturForSupplierFlow extends Migration
{
    public function up()
    {
        if ($this->db->tableExists('pembelian_retur')) {
            $fk = $this->db->query("
                SELECT CONSTRAINT_NAME
                FROM information_schema.KEY_COLUMN_USAGE
                WHERE TABLE_SCHEMA = DATABASE()
                  AND TABLE_NAME = 'pembelian_retur'
                  AND COLUMN_NAME = 'beli_id'
                  AND REFERENCED_TABLE_NAME = 'pembelian'
                LIMIT 1
            ")->getRowArray();

            if ($fk && !empty($fk['CONSTRAINT_NAME'])) {
                $this->db->query("ALTER TABLE `pembelian_retur` DROP FOREIGN KEY `{$fk['CONSTRAINT_NAME']}`");
            }
        }

        if (!$this->db->fieldExists('supco', 'pembelian_retur')) {
            $this->db->query("ALTER TABLE `pembelian_retur` ADD COLUMN `supco` VARCHAR(10) NULL DEFAULT NULL AFTER `tanggal`");
        }

        if (!$this->db->fieldExists('settlement_mode', 'pembelian_retur')) {
            $this->db->query("ALTER TABLE `pembelian_retur` ADD COLUMN `settlement_mode` ENUM('POTONG_HUTANG','CASHBACK') NOT NULL DEFAULT 'POTONG_HUTANG' AFTER `total_retur`");
        }

        $this->db->query("ALTER TABLE `pembelian_retur` MODIFY COLUMN `beli_id` VARCHAR(15) NULL DEFAULT NULL");
        $this->db->query("
            UPDATE pembelian_retur r
            INNER JOIN pembelian p ON p.toko_id=r.toko_id AND p.beli_id=r.beli_id
            SET r.supco=p.supco
            WHERE r.supco IS NULL OR r.supco=''
        ");

        $this->db->query("ALTER TABLE `pembelian_retur` MODIFY COLUMN `supco` VARCHAR(10) NOT NULL");
        $this->db->query("ALTER TABLE `pembelian_retur` ADD KEY `idx_retur_supplier` (`toko_id`, `supco`, `tanggal`)");
        $this->db->query("
            ALTER TABLE `pembelian_retur`
            ADD CONSTRAINT `fk_retur_ke_pembelian` FOREIGN KEY (`toko_id`, `beli_id`)
            REFERENCES `pembelian` (`toko_id`, `beli_id`) ON DELETE RESTRICT ON UPDATE CASCADE
        ");
    }

    public function down()
    {
        if ($this->db->tableExists('pembelian_retur')) {
            $fk = $this->db->query("
                SELECT CONSTRAINT_NAME
                FROM information_schema.KEY_COLUMN_USAGE
                WHERE TABLE_SCHEMA = DATABASE()
                  AND TABLE_NAME = 'pembelian_retur'
                  AND COLUMN_NAME = 'beli_id'
                  AND REFERENCED_TABLE_NAME = 'pembelian'
                LIMIT 1
            ")->getRowArray();

            if ($fk && !empty($fk['CONSTRAINT_NAME'])) {
                $this->db->query("ALTER TABLE `pembelian_retur` DROP FOREIGN KEY `{$fk['CONSTRAINT_NAME']}`");
            }
        }

        if ($this->db->fieldExists('supco', 'pembelian_retur')) {
            if ($this->db->fieldExists('settlement_mode', 'pembelian_retur')) {
                $this->db->query("ALTER TABLE `pembelian_retur` DROP COLUMN `settlement_mode`");
            }
            $this->db->query("ALTER TABLE `pembelian_retur` DROP COLUMN `supco`");
        }

        $this->db->query("ALTER TABLE `pembelian_retur` MODIFY COLUMN `beli_id` VARCHAR(15) NOT NULL");
        $this->db->query("
            ALTER TABLE `pembelian_retur`
            ADD CONSTRAINT `fk_retur_ke_pembelian` FOREIGN KEY (`toko_id`, `beli_id`)
            REFERENCES `pembelian` (`toko_id`, `beli_id`) ON DELETE RESTRICT ON UPDATE CASCADE
        ");
    }
}
