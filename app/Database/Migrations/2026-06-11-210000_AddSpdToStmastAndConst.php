<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddSpdToStmastAndConst extends Migration
{
    public function up()
    {
        if (!$this->db->fieldExists('spd', 'stmast')) {
            $this->db->query("ALTER TABLE `stmast` ADD COLUMN `spd` DECIMAL(15,4) NOT NULL DEFAULT 0.0000 AFTER `pkm`");
        }

        $exists = $this->db->query(
            "SELECT COUNT(*) AS total FROM `const` WHERE `rkey`='bulan_spd' AND `toko_id`=''"
        )->getRowArray();

        if ((int) ($exists['total'] ?? 0) === 0) {
            $this->db->table('const')->insert([
                'rkey' => 'bulan_spd',
                'toko_id' => '',
                'nilai' => '3',
            ]);
        }
    }

    public function down()
    {
        $this->db->table('const')
            ->where('rkey', 'bulan_spd')
            ->where('toko_id', '')
            ->delete();

        if ($this->db->fieldExists('spd', 'stmast')) {
            $this->db->query("ALTER TABLE `stmast` DROP COLUMN `spd`");
        }
    }
}
