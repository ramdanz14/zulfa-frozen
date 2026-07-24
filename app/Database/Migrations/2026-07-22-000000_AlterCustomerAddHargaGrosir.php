<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AlterCustomerAddHargaGrosir extends Migration
{
    public function up()
    {
        $this->db->query("
            ALTER TABLE `customer`
            ADD COLUMN `harga_grosir` CHAR(1) NOT NULL DEFAULT 'N' AFTER `poin`,
            ADD COLUMN `margin_grosir` DECIMAL(5,2) NOT NULL DEFAULT 0 AFTER `harga_grosir`;
        ");
    }

    public function down()
    {
        $this->db->query("
            ALTER TABLE `customer`
            DROP COLUMN `margin_grosir`,
            DROP COLUMN `harga_grosir`;
        ");
    }
}
