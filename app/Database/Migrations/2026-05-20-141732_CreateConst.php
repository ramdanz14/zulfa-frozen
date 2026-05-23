<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateConst extends Migration
{
    public function up()
    {
        $this->forge->addField([
            'rkey' => [
                'type' => 'VARCHAR',
                'constraint' => 50,
                'null' => false,
            ],
            'nilai' => [
                'type' => 'VARCHAR',
                'constraint' => 100,
                'null' => true,
            ],
        ]);
        $this->forge->addKey('rkey', true);
        $this->forge->createTable('const', true);
    }

    public function down()
    {
        $this->forge->dropTable('const', true);
    }
}
