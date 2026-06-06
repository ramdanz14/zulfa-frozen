<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AlterAdjustAddRefNoForBap extends Migration
{
    public function up()
    {
        $fieldNames = array_map('strtolower', $this->db->getFieldNames('adjust'));

        if (!in_array('ref_no', $fieldNames, true)) {
            $this->db->query("ALTER TABLE `adjust` ADD COLUMN `ref_no` VARCHAR(20) DEFAULT NULL AFTER `istype`");
        }

        $indexes = $this->db->query("SHOW INDEX FROM `adjust` WHERE Key_name='idx_adjust_ref_no'")->getResultArray();
        if (empty($indexes)) {
            $this->db->query("CREATE INDEX `idx_adjust_ref_no` ON `adjust` (`toko_id`, `istype`, `ref_no`)");
        }
    }

    public function down()
    {
        $indexes = $this->db->query("SHOW INDEX FROM `adjust` WHERE Key_name='idx_adjust_ref_no'")->getResultArray();
        if (!empty($indexes)) {
            $this->db->query("DROP INDEX `idx_adjust_ref_no` ON `adjust`");
        }

        $fieldNames = array_map('strtolower', $this->db->getFieldNames('adjust'));
        if (in_array('ref_no', $fieldNames, true)) {
            $this->db->query("ALTER TABLE `adjust` DROP COLUMN `ref_no`");
        }
    }
}
