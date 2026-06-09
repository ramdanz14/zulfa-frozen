<?php

namespace App\Commands;

use App\Models\ClosingModel;
use CodeIgniter\CLI\BaseCommand;
use CodeIgniter\CLI\CLI;

class ClosingAuto extends BaseCommand
{
    protected $group = 'Zulfa';
    protected $name = 'closing:auto';
    protected $description = 'Menjalankan closing bulanan otomatis untuk semua toko yang periode closing-nya sudah lewat.';

    public function run(array $params)
    {
        $model = new ClosingModel();
        $results = $model->closeAllDueStores('CLI');

        foreach ($results as $row) {
            $type = ($row['tipe'] ?? '') === 'error' ? 'red' : ((($row['tipe'] ?? '') === 'skip') ? 'yellow' : 'green');
            CLI::write(($row['toko_id'] ?? '-') . ' : ' . ($row['data'] ?? '-'), $type);
        }
    }
}
