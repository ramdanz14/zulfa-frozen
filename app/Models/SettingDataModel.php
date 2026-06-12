<?php

namespace App\Models;

use CodeIgniter\Model;

class SettingDataModel extends Model
{
    protected $returnType = 'array';
    protected $protectFields = false;

    private array $definitions = [
        'nominal_per_poin' => [
            'label' => 'Nominal per Poin',
            'default' => '1000',
            'type' => 'number',
            'suffix' => 'rupiah',
            'description' => 'Kelipatan belanja untuk mendapatkan 1 poin member. Contoh 1000 berarti setiap Rp 1.000 nilai belanja mendapat 1 poin.',
        ],
        'satuan_gramasi' => [
            'label' => 'Satuan Gramasi',
            'default' => 'GR;GRAM;ML',
            'type' => 'text',
            'suffix' => 'pisahkan dengan titik koma',
            'description' => 'Daftar satuan yang dianggap gramasi/volume kecil pada transfer gudang. Satuan ini tidak dibulatkan ke kelipatan 50 saat menghitung harga transfer.',
        ],
        'batas_retur_jual' => [
            'label' => 'Batas Retur Jual',
            'default' => '7',
            'type' => 'number',
            'suffix' => 'hari',
            'description' => 'Batas maksimal umur transaksi penjualan yang masih boleh diretur. Contoh 7 berarti retur hanya boleh untuk transaksi maksimal 7 hari terakhir.',
        ],
        'markup_gudang' => [
            'label' => 'Markup Gudang',
            'default' => '2',
            'type' => 'number',
            'suffix' => 'persen',
            'description' => 'Persentase markup dari harga pokok untuk menghitung harga jual transfer dari gudang ke toko.',
        ],
    ];

    public function getSettings(): array
    {
        $keys = array_keys($this->definitions);
        $placeholders = implode(',', array_fill(0, count($keys), '?'));
        $rows = $this->db->query(
            "SELECT rkey, nilai FROM const WHERE rkey IN ({$placeholders}) ORDER BY rkey",
            $keys
        )->getResultArray();

        $values = [];
        foreach ($rows as $row) {
            $key = (string) ($row['rkey'] ?? '');
            if ($key !== '' && !isset($values[$key])) {
                $values[$key] = (string) ($row['nilai'] ?? '');
            }
        }

        $settings = [];
        foreach ($this->definitions as $key => $definition) {
            $settings[$key] = array_merge($definition, [
                'rkey' => $key,
                'nilai' => $values[$key] ?? $definition['default'],
            ]);
        }

        return $settings;
    }

    public function saveSettings(array $payload, string $username): array
    {
        $settings = $this->getSettings();
        $clean = [];

        foreach ($settings as $key => $setting) {
            $value = trim((string) ($payload[$key] ?? $setting['nilai']));
            if (($setting['type'] ?? '') === 'number') {
                $value = (string) (float) str_replace(',', '.', preg_replace('/[^0-9.,-]/', '', $value));
                if ((float) $value < 0) {
                    return ['tipe' => 'error', 'data' => $setting['label'] . ' tidak boleh minus'];
                }
            }
            if ($key === 'satuan_gramasi') {
                $parts = array_filter(array_map(
                    static fn($item): string => strtoupper(trim($item)),
                    preg_split('/[;,]+/', $value) ?: []
                ));
                $value = implode(';', array_values(array_unique($parts)));
                if ($value === '') {
                    return ['tipe' => 'error', 'data' => 'Satuan gramasi wajib diisi'];
                }
            }
            if ($value === '') {
                return ['tipe' => 'error', 'data' => $setting['label'] . ' wajib diisi'];
            }
            $clean[$key] = $value;
        }

        $this->db->transStart();
        foreach ($clean as $key => $value) {
            $this->upsertConst($key, $value);
        }
        $this->db->transComplete();

        if (! $this->db->transStatus()) {
            return ['tipe' => 'error', 'data' => 'Gagal menyimpan setting data'];
        }

        return ['tipe' => 'success', 'data' => 'Setting data berhasil disimpan', 'settings' => $clean, 'updid' => $username];
    }

    private function upsertConst(string $key, string $value): void
    {
        $hasTokoId = $this->db->fieldExists('toko_id', 'const');
        $where = "rkey=:rkey:";
        if ($hasTokoId) {
            $where .= " AND (toko_id IS NULL OR toko_id='')";
        }

        $existing = $this->db->query(
            "SELECT rkey FROM const WHERE {$where} LIMIT 1",
            ['rkey' => $key]
        )->getRowArray();

        if ($existing) {
            $builder = $this->db->table('const')->where('rkey', $key);
            if ($hasTokoId) {
                $builder->groupStart()->where('toko_id', null)->orWhere('toko_id', '')->groupEnd();
            }
            $builder->update(['nilai' => $value]);
            return;
        }

        $row = ['rkey' => $key, 'nilai' => $value];
        if ($hasTokoId) {
            $row['toko_id'] = '';
        }
        $this->db->table('const')->insert($row);
    }
}
