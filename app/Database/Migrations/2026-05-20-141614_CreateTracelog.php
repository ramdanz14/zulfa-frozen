<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateTracelog extends Migration
{
    public function up()
    {
        $this->forge->addField('traceid INT(15) NOT NULL AUTO_INCREMENT');
        $this->forge->addField('toko_id VARCHAR(5) DEFAULT NULL');
        $this->forge->addField('tgl DATETIME DEFAULT NULL');
        $this->forge->addField('username VARCHAR(50) DEFAULT NULL');
        $this->forge->addField('action VARCHAR(50) DEFAULT NULL');
        $this->forge->addField('detail TEXT DEFAULT NULL');
        $this->forge->addKey('traceid', true);
        $this->forge->createTable('tracelog', true);
    }

    public function down()
    {
        $this->forge->dropTable('tracelog', true);
    }
}
