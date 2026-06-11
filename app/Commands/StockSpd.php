<?php

namespace App\Commands;

use CodeIgniter\CLI\BaseCommand;
use CodeIgniter\CLI\CLI;
use Config\Database;

class StockSpd extends BaseCommand
{
    protected $group = 'Stock';
    protected $name = 'stock:spd';
    protected $description = 'Menghitung ulang SPD stock per toko.';
    protected $usage = 'stock:spd [toko_id|all] [periode_yyyymm]';
    protected $arguments = [
        'toko_id' => 'Kode toko yang dihitung. Gunakan all atau kosong untuk semua toko.',
        'periode_yyyymm' => 'Periode acuan SPD. Default periode berjalan.',
    ];

    public function run(array $params)
    {
        helper('custom');

        $db = Database::connect();
        $tokoParam = trim((string) ($params[0] ?? 'all'));
        $periodYm = preg_replace('/[^0-9]/', '', (string) ($params[1] ?? date('Ym')));
        if (strlen($periodYm) !== 6) {
            $periodYm = date('Ym');
        }

        $stores = [];
        if ($tokoParam === '' || strtolower($tokoParam) === 'all') {
            $rows = $db->query("SELECT toko_id FROM toko ORDER BY toko_id")->getResultArray();
            foreach ($rows as $row) {
                $tokoId = trim((string) ($row['toko_id'] ?? ''));
                if ($tokoId !== '') {
                    $stores[] = $tokoId;
                }
            }
        } else {
            $stores[] = $tokoParam;
        }

        if (empty($stores)) {
            CLI::error('Tidak ada toko yang dapat dihitung.');
            return;
        }

        foreach ($stores as $tokoId) {
            HitungSpd($tokoId, $periodYm);
            CLI::write("SPD {$tokoId} periode {$periodYm} selesai.", 'green');
        }
    }
}
