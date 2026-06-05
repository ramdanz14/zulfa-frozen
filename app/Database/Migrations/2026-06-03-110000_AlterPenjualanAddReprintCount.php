<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AlterPenjualanAddReprintCount extends Migration
{
    public function up()
    {
        $fields = $this->db->getFieldData('penjualan');
        $fieldNames = array_map(static fn($field) => strtolower($field->name), $fields);

        if (! in_array('reprint_count', $fieldNames, true)) {
            $this->db->query("ALTER TABLE `penjualan` ADD COLUMN `reprint_count` INT NOT NULL DEFAULT 0 AFTER `earned_points`");
        }
    }

    public function down()
    {
        $fields = $this->db->getFieldData('penjualan');
        $fieldNames = array_map(static fn($field) => strtolower($field->name), $fields);

        if (in_array('reprint_count', $fieldNames, true)) {
            $this->db->query("ALTER TABLE `penjualan` DROP COLUMN `reprint_count`");
        }
    }
}
