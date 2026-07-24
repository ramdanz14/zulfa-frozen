<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class DualCashBalance extends Migration
{
    public function up()
    {
        // 1. Add saldo_target column to kas_mutasi
        if (!$this->db->fieldExists('saldo_target', 'kas_mutasi')) {
            $this->forge->addColumn('kas_mutasi', [
                'saldo_target' => [
                    'type'       => 'ENUM',
                    'constraint' => ['TOKO', 'PEMILIK'],
                    'default'    => 'TOKO',
                    'after'      => 'tipe_mutasi',
                ],
            ]);
        }

        // 2. Add saldo_toko and saldo_pemilik columns to saldo_cash
        if (!$this->db->fieldExists('saldo_toko', 'saldo_cash')) {
            $this->forge->addColumn('saldo_cash', [
                'saldo_toko' => [
                    'type'       => 'DECIMAL',
                    'constraint' => '15,2',
                    'default'    => 0,
                    'after'      => 'saldo_all',
                ],
                'saldo_pemilik' => [
                    'type'       => 'DECIMAL',
                    'constraint' => '15,2',
                    'default'    => 0,
                    'after'      => 'saldo_toko',
                ],
            ]);
        }

        // 3. Create saldo_cash_harian table
        if (!$this->db->tableExists('saldo_cash_harian')) {
            $this->forge->addField([
                'id' => [
                    'type'           => 'BIGINT',
                    'unsigned'       => true,
                    'auto_increment' => true,
                ],
                'toko_id' => [
                    'type'       => 'VARCHAR',
                    'constraint' => 10,
                ],
                'tanggal' => [
                    'type' => 'DATE',
                ],
                'saldo_toko_awal' => [
                    'type'       => 'DECIMAL',
                    'constraint' => '15,2',
                    'default'    => 0,
                ],
                'saldo_pemilik_awal' => [
                    'type'       => 'DECIMAL',
                    'constraint' => '15,2',
                    'default'    => 0,
                ],
                // Inflows
                'pos_tunai' => [
                    'type'       => 'DECIMAL',
                    'constraint' => '15,2',
                    'default'    => 0,
                ],
                'pos_transfer' => [
                    'type'       => 'DECIMAL',
                    'constraint' => '15,2',
                    'default'    => 0,
                ],
                'pos_qris' => [
                    'type'       => 'DECIMAL',
                    'constraint' => '15,2',
                    'default'    => 0,
                ],
                'bayar_piutang_tunai' => [
                    'type'       => 'DECIMAL',
                    'constraint' => '15,2',
                    'default'    => 0,
                ],
                'bayar_piutang_transfer' => [
                    'type'       => 'DECIMAL',
                    'constraint' => '15,2',
                    'default'    => 0,
                ],
                'bayar_piutang_qris' => [
                    'type'       => 'DECIMAL',
                    'constraint' => '15,2',
                    'default'    => 0,
                ],
                'kas_masuk_tunai' => [
                    'type'       => 'DECIMAL',
                    'constraint' => '15,2',
                    'default'    => 0,
                ],
                'kas_masuk_noncash' => [
                    'type'       => 'DECIMAL',
                    'constraint' => '15,2',
                    'default'    => 0,
                ],
                'tarik_pemilik_ke_toko' => [
                    'type'       => 'DECIMAL',
                    'constraint' => '15,2',
                    'default'    => 0,
                ],
                // Outflows
                'bayar_hutang_tunai' => [
                    'type'       => 'DECIMAL',
                    'constraint' => '15,2',
                    'default'    => 0,
                ],
                'bayar_hutang_transfer' => [
                    'type'       => 'DECIMAL',
                    'constraint' => '15,2',
                    'default'    => 0,
                ],
                'kas_keluar_tunai' => [
                    'type'       => 'DECIMAL',
                    'constraint' => '15,2',
                    'default'    => 0,
                ],
                'kas_keluar_noncash' => [
                    'type'       => 'DECIMAL',
                    'constraint' => '15,2',
                    'default'    => 0,
                ],
                'deposit_toko_ke_pemilik' => [
                    'type'       => 'DECIMAL',
                    'constraint' => '15,2',
                    'default'    => 0,
                ],
                'tarik_keuntungan_toko' => [
                    'type'       => 'DECIMAL',
                    'constraint' => '15,2',
                    'default'    => 0,
                ],
                'tarik_keuntungan_pemilik' => [
                    'type'       => 'DECIMAL',
                    'constraint' => '15,2',
                    'default'    => 0,
                ],
                // Ending
                'saldo_toko_akhir' => [
                    'type'       => 'DECIMAL',
                    'constraint' => '15,2',
                    'default'    => 0,
                ],
                'saldo_pemilik_akhir' => [
                    'type'       => 'DECIMAL',
                    'constraint' => '15,2',
                    'default'    => 0,
                ],
                // Audit
                'closed_at' => [
                    'type' => 'DATETIME',
                    'null' => true,
                ],
                'closed_by' => [
                    'type'       => 'VARCHAR',
                    'constraint' => 50,
                    'null'       => true,
                ],
                'created_at' => [
                    'type'    => 'TIMESTAMP',
                    'default' => 'CURRENT_TIMESTAMP',
                ],
            ]);
            $this->forge->addKey('id', true);
            $this->forge->addKey(['toko_id', 'tanggal'], false, true, 'uq_toko_tanggal');
            $this->forge->addKey('tanggal', false, false, 'idx_tanggal');
            $this->forge->createTable('saldo_cash_harian', true);
        }

        // 4. Backfill saldo_target = 'TOKO' for existing CASH mutasi
        $this->db->query(
            "UPDATE kas_mutasi 
             SET saldo_target = 'TOKO' 
             WHERE COALESCE(saldo_channel, 'CASH') = 'CASH' 
             AND (saldo_target IS NULL OR saldo_target = '')"
        );

        // 5. Backfill saldo_toko from existing saldo_tunai in saldo_cash
        $this->db->query(
            "UPDATE saldo_cash 
             SET saldo_toko = COALESCE(saldo_tunai, 0), 
                 saldo_pemilik = 0 
             WHERE (saldo_toko IS NULL OR saldo_toko = 0) 
             AND (saldo_pemilik IS NULL OR saldo_pemilik = 0)"
        );
    }

    public function down()
    {
        // Drop saldo_cash_harian table
        if ($this->db->tableExists('saldo_cash_harian')) {
            $this->forge->dropTable('saldo_cash_harian', true);
        }

        // Remove columns from saldo_cash
        if ($this->db->fieldExists('saldo_toko', 'saldo_cash')) {
            $this->forge->dropColumn('saldo_cash', 'saldo_toko');
        }
        if ($this->db->fieldExists('saldo_pemilik', 'saldo_cash')) {
            $this->forge->dropColumn('saldo_cash', 'saldo_pemilik');
        }

        // Remove saldo_target from kas_mutasi
        if ($this->db->fieldExists('saldo_target', 'kas_mutasi')) {
            $this->forge->dropColumn('kas_mutasi', 'saldo_target');
        }
    }
}