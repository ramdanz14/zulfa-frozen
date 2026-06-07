<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class MoveItemSupplierToProdmastStore extends Migration
{
    public function up()
    {
        if (!$this->db->fieldExists('supco', 'prodmast_store')) {
            $this->db->query("ALTER TABLE `prodmast_store` ADD COLUMN `supco` VARCHAR(10) NULL DEFAULT NULL AFTER `sat_id`");
            $this->db->query("ALTER TABLE `prodmast_store` ADD KEY `idx_store_supplier` (`toko_id`, `supco`)");
        }

        $this->db->query(
            "UPDATE prodmast_store ps
             INNER JOIN prodmast p ON p.kode_item=ps.kode_item
             SET ps.supco=p.supco
             WHERE (ps.supco IS NULL OR ps.supco='') AND COALESCE(p.supco, '') <> ''"
        );

        $this->db->query(
            "UPDATE prodmast_store ps
             SET ps.supco=(
                SELECT p.supco
                FROM pembelian p
                INNER JOIN pembelian_detail d ON d.toko_id=p.toko_id AND d.beli_id=p.beli_id
                WHERE p.toko_id=ps.toko_id
                  AND d.kode_item=ps.kode_item
                  AND p.status_nota='TERIMA'
                  AND COALESCE(p.supco, '') <> ''
                ORDER BY p.tanggal DESC, p.updtime DESC, p.beli_id DESC, d.seq_no DESC
                LIMIT 1
             )
             WHERE EXISTS (
                SELECT 1
                FROM pembelian p
                INNER JOIN pembelian_detail d ON d.toko_id=p.toko_id AND d.beli_id=p.beli_id
                WHERE p.toko_id=ps.toko_id
                  AND d.kode_item=ps.kode_item
                  AND p.status_nota='TERIMA'
                  AND COALESCE(p.supco, '') <> ''
             )"
        );
    }

    public function down()
    {
        if ($this->db->fieldExists('supco', 'prodmast_store')) {
            $this->db->query("ALTER TABLE `prodmast_store` DROP KEY `idx_store_supplier`");
            $this->db->query("ALTER TABLE `prodmast_store` DROP COLUMN `supco`");
        }
    }
}
