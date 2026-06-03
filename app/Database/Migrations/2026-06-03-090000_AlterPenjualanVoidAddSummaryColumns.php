<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AlterPenjualanVoidAddSummaryColumns extends Migration
{
    public function up()
    {
        $fields = $this->db->getFieldData('penjualan_void');
        $fieldNames = array_map(static fn($field) => strtolower($field->name), $fields);

        if (! in_array('jumlah_item', $fieldNames, true)) {
            $this->db->query("ALTER TABLE `penjualan_void` ADD COLUMN `jumlah_item` INT NOT NULL DEFAULT 0 AFTER `alasan`");
        }

        if (! in_array('jumlah_qty', $fieldNames, true)) {
            $this->db->query("ALTER TABLE `penjualan_void` ADD COLUMN `jumlah_qty` DECIMAL(15,4) NOT NULL DEFAULT 0.0000 AFTER `jumlah_item`");
        }

        if (! in_array('total_gross', $fieldNames, true)) {
            $this->db->query("ALTER TABLE `penjualan_void` ADD COLUMN `total_gross` DECIMAL(15,2) NOT NULL DEFAULT 0.00 AFTER `jumlah_qty`");
        }
    }

    public function down()
    {
        $fields = $this->db->getFieldData('penjualan_void');
        $fieldNames = array_map(static fn($field) => strtolower($field->name), $fields);

        if (in_array('total_gross', $fieldNames, true)) {
            $this->db->query("ALTER TABLE `penjualan_void` DROP COLUMN `total_gross`");
        }

        if (in_array('jumlah_qty', $fieldNames, true)) {
            $this->db->query("ALTER TABLE `penjualan_void` DROP COLUMN `jumlah_qty`");
        }

        if (in_array('jumlah_item', $fieldNames, true)) {
            $this->db->query("ALTER TABLE `penjualan_void` DROP COLUMN `jumlah_item`");
        }
    }
}
