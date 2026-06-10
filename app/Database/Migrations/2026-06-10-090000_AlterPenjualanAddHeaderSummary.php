<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AlterPenjualanAddHeaderSummary extends Migration
{
    public function up()
    {
        $fields = $this->db->getFieldData('penjualan');
        $fieldNames = array_map(static fn($field) => strtolower($field->name), $fields);

        if (!in_array('margin_bruto', $fieldNames, true)) {
            $this->db->query("ALTER TABLE `penjualan` ADD COLUMN `margin_bruto` DECIMAL(15,2) NOT NULL DEFAULT 0.00 AFTER `netto`");
        }
        if (!in_array('total_hpp', $fieldNames, true)) {
            $this->db->query("ALTER TABLE `penjualan` ADD COLUMN `total_hpp` DECIMAL(15,2) NOT NULL DEFAULT 0.00 AFTER `margin_bruto`");
        }
        if (!in_array('total_qty', $fieldNames, true)) {
            $this->db->query("ALTER TABLE `penjualan` ADD COLUMN `total_qty` DECIMAL(15,4) NOT NULL DEFAULT 0.0000 AFTER `total_hpp`");
        }
        if (!in_array('total_item', $fieldNames, true)) {
            $this->db->query("ALTER TABLE `penjualan` ADD COLUMN `total_item` INT NOT NULL DEFAULT 0 AFTER `total_qty`");
        }
        if (!in_array('total_diskon_item', $fieldNames, true)) {
            $this->db->query("ALTER TABLE `penjualan` ADD COLUMN `total_diskon_item` DECIMAL(15,2) NOT NULL DEFAULT 0.00 AFTER `total_item`");
        }

        $this->db->query("
            UPDATE penjualan j
            LEFT JOIN (
                SELECT toko_id,
                       jual_id,
                       COALESCE(SUM(qty_jual * harga_pokok), 0) AS total_hpp,
                       COALESCE(SUM(netto - (qty_jual * harga_pokok)), 0) AS margin_bruto,
                       COALESCE(SUM(qty_jual), 0) AS total_qty,
                       COUNT(DISTINCT kode_item) AS total_item,
                       COALESCE(SUM(diskon_item), 0) AS total_diskon_item
                FROM penjualan_detail
                GROUP BY toko_id, jual_id
            ) d ON d.toko_id=j.toko_id AND d.jual_id=j.jual_id
            SET j.total_hpp=COALESCE(d.total_hpp,0),
                j.margin_bruto=COALESCE(d.margin_bruto,0),
                j.total_qty=COALESCE(d.total_qty,0),
                j.total_item=COALESCE(d.total_item,0),
                j.total_diskon_item=COALESCE(d.total_diskon_item,0)
        ");
    }

    public function down()
    {
        $fields = $this->db->getFieldData('penjualan');
        $fieldNames = array_map(static fn($field) => strtolower($field->name), $fields);

        if (in_array('total_diskon_item', $fieldNames, true)) {
            $this->db->query("ALTER TABLE `penjualan` DROP COLUMN `total_diskon_item`");
        }
        if (in_array('total_item', $fieldNames, true)) {
            $this->db->query("ALTER TABLE `penjualan` DROP COLUMN `total_item`");
        }
        if (in_array('total_qty', $fieldNames, true)) {
            $this->db->query("ALTER TABLE `penjualan` DROP COLUMN `total_qty`");
        }
        if (in_array('total_hpp', $fieldNames, true)) {
            $this->db->query("ALTER TABLE `penjualan` DROP COLUMN `total_hpp`");
        }
        if (in_array('margin_bruto', $fieldNames, true)) {
            $this->db->query("ALTER TABLE `penjualan` DROP COLUMN `margin_bruto`");
        }
    }
}
