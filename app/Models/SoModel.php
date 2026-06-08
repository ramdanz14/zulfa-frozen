<?php

namespace App\Models;

use CodeIgniter\Model;

class SoModel extends Model
{
    protected $table = 'adjust';
    protected $returnType = 'array';
    protected $protectFields = false;

    public function getKategoriOptions(): array
    {
        return $this->db->query(
            "SELECT k.kat_id AS id,
                    CONCAT(k.kat_id, ' - ', COUNT(p.kode_item), ' item') AS text
             FROM kategori k
             LEFT JOIN prodmast p ON p.kat_id=k.kat_id
             GROUP BY k.kat_id
             ORDER BY k.kat_id"
        )->getResultArray();
    }

    public function getClosingDate(string $tokoId): string
    {
        return GetClosingDateByToko($tokoId);
    }

    public function getActiveSo(string $tokoId): ?array
    {
        $row = $this->db->query(
            "SELECT nilai
             FROM const
             WHERE rkey='so_aktif' AND toko_id=:toko_id:
             LIMIT 1",
            ['toko_id' => $tokoId]
        )->getRowArray();

        if (!$row || empty($row['nilai'])) {
            return null;
        }

        $tanggal = (string) $row['nilai'];
        return [
            'tanggal' => $tanggal,
            'so_table' => $this->getSoTableName($tanggal, $tokoId),
            'sod_table' => $this->getSodTableName($tanggal, $tokoId),
        ];
    }

    public function createSoSession(string $tokoId, string $username, array $kategoriIds = []): array
    {
        if ($this->getActiveSo($tokoId)) {
            return ['tipe' => 'error', 'data' => 'Masih ada SO aktif di toko ini'];
        }

        $tanggal = date('Y-m-d');
        $soTable = $this->getSoTableName($tanggal, $tokoId);
        $sodTable = $this->getSodTableName($tanggal, $tokoId);
        if ($this->tableExists($soTable)) {
            return ['tipe' => 'error', 'data' => 'SO tanggal hari ini untuk toko ini sudah pernah dibuat'];
        }

        $kategoriIds = array_values(array_filter(array_map(
            static fn($value): string => trim((string) $value),
            $kategoriIds
        )));

        $this->db->transBegin();
        $this->createSoTables($soTable, $sodTable);
        $this->seedSoTable($soTable, $tokoId, $kategoriIds);
        $inserted = $this->db->query("SELECT COUNT(*) AS total FROM `$soTable` WHERE toko_id=:toko_id:", ['toko_id' => $tokoId])->getRowArray();
        if ((int) ($inserted['total'] ?? 0) === 0) {
            $this->db->transRollback();
            if ($this->tableExists($sodTable)) {
                $this->db->query("DROP TABLE `$sodTable`");
            }
            if ($this->tableExists($soTable)) {
                $this->db->query("DROP TABLE `$soTable`");
            }
            return ['tipe' => 'error', 'data' => 'Tidak ada item aktif yang bisa dimasukkan ke sesi SO'];
        }
        $this->db->table('const')->insert([
            'rkey' => 'so_aktif',
            'toko_id' => $tokoId,
            'nilai' => $tanggal,
        ]);
        if (!$this->db->transStatus()) {
            $this->db->transRollback();
            if ($this->tableExists($sodTable)) {
                $this->db->query("DROP TABLE `$sodTable`");
            }
            if ($this->tableExists($soTable)) {
                $this->db->query("DROP TABLE `$soTable`");
            }
            return ['tipe' => 'error', 'data' => 'Gagal membuat sesi SO'];
        }
        $this->db->transCommit();

        return [
            'tipe' => 'success',
            'data' => empty($kategoriIds)
                ? 'Berhasil load data SO semua produk'
                : 'Berhasil load data SO kategori: ' . implode(', ', $kategoriIds),
            'so_table' => $soTable,
        ];
    }

    public function ajaxInputList(array $params, string $tokoId, string $statusInput, string $katId): array
    {
        $active = $this->getActiveSo($tokoId);
        if (!$active || !$this->tableExists($active['so_table'])) {
            return ['error' => 'Tidak ada data SO aktif. Silakan buat SO terlebih dahulu.'];
        }

        $start = (int) ($params['start'] ?? 0);
        $length = $params['length'] ?? 25;
        $search = trim((string) ($params['search_value'] ?? ''));
        $queryLimit = $length !== '-1' ? " LIMIT $start, " . (int) $length : '';
        $binds = ['toko_id' => $tokoId];
        $where = " WHERE toko_id=:toko_id: ";
        $where .= $statusInput === 'sudah' ? " AND soid<>'' " : " AND soid='' ";
        if ($katId !== '' && $katId !== 'all') {
            $where .= " AND kat_id=:kat_id: ";
            $binds['kat_id'] = $katId;
        }
        if ($search !== '') {
            $where .= " AND (kode_item LIKE :search: OR nama_item LIKE :search: OR kat_id LIKE :search: OR updid LIKE :search:) ";
            $binds['search'] = '%' . $this->db->escapeLikeString($search) . '%';
        }

        $countRow = $this->db->query(
            "SELECT COUNT(*) AS total FROM `{$active['so_table']}` $where",
            $binds
        )->getRowArray();

        $data = $this->db->query(
            "SELECT * FROM `{$active['so_table']}`
             $where
             ORDER BY kat_id, nama_item, kode_item
             $queryLimit",
            $binds
        )->getResultArray();

        return [
            'data' => $data,
            'total_count' => (int) ($countRow['total'] ?? 0),
            'total_filtered' => (int) ($countRow['total'] ?? 0),
        ];
    }

    public function saveInputQty(string $tokoId, string $username, string $kodeItem, float $qtyFisik): array
    {
        $active = $this->getActiveSo($tokoId);
        if (!$active || !$this->tableExists($active['so_table'])) {
            return ['tipe' => 'error', 'data' => 'Tidak ada data SO aktif'];
        }
        if ($qtyFisik < 0) {
            return ['tipe' => 'error', 'data' => 'Qty fisik tidak boleh lebih kecil dari nol'];
        }

        $row = $this->db->query(
            "SELECT * FROM `{$active['so_table']}` WHERE toko_id=:toko_id: AND kode_item=:kode_item: LIMIT 1",
            ['toko_id' => $tokoId, 'kode_item' => $kodeItem]
        )->getRowArray();

        if (!$row) {
            return ['tipe' => 'error', 'data' => 'Item SO tidak ditemukan'];
        }

        $this->db->transStart();
        $this->db->query(
            "UPDATE `{$active['so_table']}`
             SET ttl=:ttl:, soid='I', updid=:updid:, updtime=NOW()
             WHERE toko_id=:toko_id: AND kode_item=:kode_item:",
            [
                'ttl' => $qtyFisik,
                'updid' => $username,
                'toko_id' => $tokoId,
                'kode_item' => $kodeItem,
            ]
        );
        $this->db->query(
            "INSERT INTO `{$active['sod_table']}` (toko_id, kode_item, ttl, sat_dasar, updid, updtime)
             VALUES (:toko_id:, :kode_item:, :ttl:, :sat_dasar:, :updid:, NOW())",
            [
                'toko_id' => $tokoId,
                'kode_item' => $kodeItem,
                'ttl' => $qtyFisik,
                'sat_dasar' => (string) ($row['sat_dasar'] ?? ''),
                'updid' => $username,
            ]
        );
        $this->db->transComplete();

        if (!$this->db->transStatus()) {
            return ['tipe' => 'error', 'data' => 'Gagal menyimpan input SO'];
        }

        return ['tipe' => 'success', 'data' => 'Data SO berhasil disimpan'];
    }

    public function getInputHistory(string $tokoId, string $kodeItem): array
    {
        $active = $this->getActiveSo($tokoId);
        if (!$active || !$this->tableExists($active['sod_table'])) {
            return [];
        }

        return $this->db->query(
            "SELECT * FROM `{$active['sod_table']}`
             WHERE toko_id=:toko_id: AND kode_item=:kode_item:
             ORDER BY updtime DESC",
            ['toko_id' => $tokoId, 'kode_item' => $kodeItem]
        )->getResultArray();
    }

    public function ajaxHasilList(array $params, string $tokoId, string $tanggal): array
    {
        $table = $this->getSoTableName($tanggal, $tokoId);
        if (!$this->tableExists($table)) {
            return ['data' => [], 'total_count' => 0, 'total_filtered' => 0];
        }

        $start = (int) ($params['start'] ?? 0);
        $length = $params['length'] ?? 25;
        $search = trim((string) ($params['search_value'] ?? ''));
        $queryLimit = $length !== '-1' ? " LIMIT $start, " . (int) $length : '';
        $binds = ['toko_id' => $tokoId];
        $where = " WHERE toko_id=:toko_id: ";
        if ($search !== '') {
            $where .= " AND (kode_item LIKE :search: OR nama_item LIKE :search: OR kat_id LIKE :search: OR updid LIKE :search:) ";
            $binds['search'] = '%' . $this->db->escapeLikeString($search) . '%';
        }

        $countRow = $this->db->query("SELECT COUNT(*) AS total FROM `$table` $where", $binds)->getRowArray();
        $data = $this->db->query(
            "SELECT kode_item, nama_item, sat_dasar, com, ttl,
                    IF(soid='', 'Belum', 'Sudah') AS status_input,
                    IF(soid!='', (ttl-com), 0) AS selisih,
                    IF(soid!='', ROUND((ttl-com)*hpp_dasar), 0) AS selisih_rp
             FROM `$table`
             $where
             ORDER BY selisih_rp, nama_item, kode_item
             $queryLimit",
            $binds
        )->getResultArray();

        return [
            'data' => $data,
            'total_count' => (int) ($countRow['total'] ?? 0),
            'total_filtered' => (int) ($countRow['total'] ?? 0),
        ];
    }

    public function getHasilSummary(string $tokoId, string $tanggal): array
    {
        $table = $this->getSoTableName($tanggal, $tokoId);
        if (!$this->tableExists($table)) {
            return [
                'sum_periode' => $tanggal,
                'sum_sudah_input' => 0,
                'sum_belum_input' => 0,
                'sum_nk_qty' => 0,
                'sum_nk_rp' => 0,
                'sum_nl_qty' => 0,
                'sum_nl_rp' => 0,
                'sum_nkl_qty' => 0,
                'sum_nkl_rp' => 0,
            ];
        }

        return $this->db->query(
            "SELECT :tanggal: AS sum_periode,
                    SUM(IF(soid!='',1,0)) AS sum_sudah_input,
                    SUM(IF(soid='',1,0)) AS sum_belum_input,
                    SUM(IF(soid!='' AND ttl<com, ttl-com, 0)) AS sum_nk_qty,
                    ROUND(SUM(IF(soid!='' AND ttl<com, (ttl-com)*hpp_dasar, 0))) AS sum_nk_rp,
                    SUM(IF(soid!='' AND ttl>com, ttl-com, 0)) AS sum_nl_qty,
                    ROUND(SUM(IF(soid!='' AND ttl>com, (ttl-com)*hpp_dasar, 0))) AS sum_nl_rp,
                    SUM(IF(soid!='', ttl-com, 0)) AS sum_nkl_qty,
                    ROUND(SUM(IF(soid!='', (ttl-com)*hpp_dasar, 0))) AS sum_nkl_rp
             FROM `$table`
             WHERE toko_id=:toko_id:",
            ['tanggal' => $tanggal, 'toko_id' => $tokoId]
        )->getRowArray() ?: [];
    }

    public function adjustAll(string $tokoId, string $username): array
    {
        $active = $this->getActiveSo($tokoId);
        if (!$active || !$this->tableExists($active['so_table'])) {
            return ['tipe' => 'error', 'data' => 'Tidak ada data SO aktif'];
        }

        $rows = $this->db->query(
            "SELECT * FROM `{$active['so_table']}`
             WHERE toko_id=:toko_id: AND soid='I' AND ROUND(ttl-com,4) <> 0",
            ['toko_id' => $tokoId]
        )->getResultArray();
        if (empty($rows)) {
            return ['tipe' => 'error', 'data' => 'Tidak ada selisih SO yang siap di-adjust'];
        }

        $this->db->transStart();
        $seq = 1;
        foreach ($rows as $row) {
            $qtySelisih = (float) ($row['ttl'] ?? 0) - (float) ($row['com'] ?? 0);
            $price = (int) round((float) ($row['hpp_dasar'] ?? 0));
            $this->db->table('adjust')->insert([
                'toko_id' => $tokoId,
                'tanggal' => date('Y-m-d H:i:s'),
                'kode_item' => (string) ($row['kode_item'] ?? ''),
                'istype' => 'SO',
                'seq_no' => $seq++,
                'sat_id' => (string) ($row['sat_dasar'] ?? ''),
                'qty_so' => $qtySelisih,
                'qty_konversi' => 1,
                'qty_stock' => $qtySelisih,
                'price' => $price,
                'gross' => (int) round($qtySelisih * $price),
                'keterangan' => 'SO ' . $active['tanggal'],
                'updid' => $username,
            ]);

            $this->db->table('prodmast_store')
                ->where('toko_id', $tokoId)
                ->where('kode_item', $row['kode_item'])
                ->set('last_so', 'NOW()', false)
                ->update();
        }
        $this->db->query(
            "UPDATE `{$active['so_table']}`
             SET soid='A', updid=:updid:, updtime=NOW()
             WHERE toko_id=:toko_id: AND soid='I' AND ROUND(ttl-com,4) <> 0",
            ['updid' => $username, 'toko_id' => $tokoId]
        );
        $this->db->table('const')->where('rkey', 'so_aktif')->where('toko_id', $tokoId)->delete();
        $this->db->transComplete();

        if (!$this->db->transStatus()) {
            return ['tipe' => 'error', 'data' => 'Gagal adjust SO'];
        }

        HitungStock($tokoId);
        return ['tipe' => 'success', 'data' => 'Berhasil adjust SO'];
    }

    public function searchBaseItems(string $tokoId, string $term): array
    {
        $search = '%' . $this->db->escapeLikeString($term) . '%';
        return $this->db->query(
            "SELECT p.kode_item, p.nama_item,
                    base.sat_id, base.qty_konversi,
                    COALESCE(st.qty, 0) AS qty,
                    COALESCE(store.harga_pokok, 0) AS hpp_supplier
             FROM prodmast p
             INNER JOIN (
                SELECT ps1.kode_item, ps1.sat_id, ps1.qty_konversi
                FROM prodmast_satuan ps1
                INNER JOIN (
                    SELECT kode_item, MIN(qty_konversi) AS min_qty
                    FROM prodmast_satuan
                    GROUP BY kode_item
                ) x ON x.kode_item=ps1.kode_item AND x.min_qty=ps1.qty_konversi
             ) base ON base.kode_item=p.kode_item
             LEFT JOIN stmast st ON st.toko_id=:toko_id: AND st.kode_item=p.kode_item
             LEFT JOIN prodmast_store store
                ON store.toko_id=:toko_id:
                AND store.kode_item=p.kode_item
                AND store.sat_id=base.sat_id
             WHERE EXISTS (
                SELECT 1
                FROM prodmast_store pst
                WHERE pst.toko_id=:toko_id:
                    AND pst.kode_item=p.kode_item
                    AND pst.status_item='Y'
             )
             AND (p.kode_item LIKE :search: OR p.nama_item LIKE :search:)
             ORDER BY p.nama_item
             LIMIT 30",
            ['toko_id' => $tokoId, 'search' => $search]
        )->getResultArray();
    }

    public function ajaxAdjustList(array $params, string $tokoId): array
    {
        $start = (int) ($params['start'] ?? 0);
        $length = $params['length'] ?? 25;
        $search = trim((string) ($params['search_value'] ?? ''));
        $queryLimit = $length !== '-1' ? " LIMIT $start, " . (int) $length : '';
        $binds = ['toko_id' => $tokoId];
        $where = " WHERE a.toko_id=:toko_id: AND a.istype='SO' ";
        if ($search !== '') {
            $where .= " AND (a.kode_item LIKE :search: OR p.nama_item LIKE :search: OR a.sat_id LIKE :search: OR a.keterangan LIKE :search:) ";
            $binds['search'] = '%' . $this->db->escapeLikeString($search) . '%';
        }

        $countRow = $this->db->query(
            "SELECT COUNT(*) AS total
             FROM `adjust` a
             LEFT JOIN prodmast p ON p.kode_item=a.kode_item
             $where",
            $binds
        )->getRowArray();

        $data = $this->db->query(
            "SELECT a.*, p.nama_item
             FROM `adjust` a
             LEFT JOIN prodmast p ON p.kode_item=a.kode_item
             $where
             ORDER BY a.tanggal DESC, a.so_id DESC
             $queryLimit",
            $binds
        )->getResultArray();

        $closingDate = $this->getClosingDate($tokoId);
        foreach ($data as &$row) {
            $row['can_delete'] = substr((string) ($row['tanggal'] ?? ''), 0, 7) === substr($closingDate, 0, 7);
            $row['closing_date'] = $closingDate;
        }
        unset($row);

        return [
            'data' => $data,
            'total_count' => (int) ($countRow['total'] ?? 0),
            'total_filtered' => (int) ($countRow['total'] ?? 0),
        ];
    }

    public function createSatuanAdjust(string $tokoId, string $username, array $input): array
    {
        $kodeItem = trim((string) ($input['kode_item'] ?? ''));
        $satId = trim((string) ($input['sat_id'] ?? ''));
        $qtySelisih = (float) ($input['qty_selisih'] ?? 0);
        $qtyKonversi = (float) ($input['qty_konversi'] ?? 1);
        $price = (int) round((float) ($input['hpp_supplier'] ?? 0));
        $keterangan = trim((string) ($input['keterangan'] ?? ''));

        if ($kodeItem === '' || $satId === '') {
            return ['tipe' => 'error', 'data' => 'Produk dan satuan wajib diisi'];
        }
        if (abs($qtySelisih) < 0.0001) {
            return ['tipe' => 'error', 'data' => 'Tidak ada selisih stok'];
        }
        if ($qtyKonversi <= 0) {
            return ['tipe' => 'error', 'data' => 'Qty konversi tidak valid'];
        }

        $qtyStock = round($qtySelisih * $qtyKonversi, 4);
        $this->db->table('adjust')->insert([
            'toko_id' => $tokoId,
            'tanggal' => date('Y-m-d H:i:s'),
            'kode_item' => $kodeItem,
            'istype' => 'SO',
            'seq_no' => 1,
            'sat_id' => $satId,
            'qty_so' => $qtySelisih,
            'qty_konversi' => $qtyKonversi,
            'qty_stock' => $qtyStock,
            'price' => $price,
            'gross' => (int) round($qtyStock * $price),
            'keterangan' => $keterangan !== '' ? $keterangan : 'SO satuan',
            'updid' => $username,
        ]);

        $this->db->table('prodmast_store')
            ->where('toko_id', $tokoId)
            ->where('kode_item', $kodeItem)
            ->set('last_so', 'NOW()', false)
            ->update();

        HitungStock($tokoId);
        return ['tipe' => 'success', 'data' => 'Adjust satuan berhasil ditambahkan'];
    }

    public function deleteAdjust(string $tokoId, int $soId): array
    {
        $row = $this->db->query(
            "SELECT * FROM `adjust` WHERE so_id=:so_id: AND toko_id=:toko_id: AND istype='SO' LIMIT 1",
            ['so_id' => $soId, 'toko_id' => $tokoId]
        )->getRowArray();

        if (!$row) {
            return ['tipe' => 'error', 'data' => 'Data adjust tidak ditemukan'];
        }

        $this->db->table('adjust')->where('so_id', $soId)->where('toko_id', $tokoId)->delete();
        HitungStock($tokoId);
        return ['tipe' => 'success', 'data' => 'Data adjust berhasil dihapus'];
    }

    public function getHistorySessions(string $tokoId): array
    {
        $suffix = $this->getTokoSuffix($tokoId);
        $rows = $this->db->query(
            "SELECT table_name
             FROM information_schema.tables
             WHERE table_schema = DATABASE()
               AND table_name LIKE :pattern:
             ORDER BY table_name DESC",
            ['pattern' => 'so\_%\_' . $suffix]
        )->getResultArray();

        $active = $this->getActiveSo($tokoId);
        $history = [];
        foreach ($rows as $row) {
            $table = (string) ($row['table_name'] ?? '');
            if ($table === '') {
                continue;
            }
            $tanggal = $this->parseDateFromTableName($table);
            $summary = $this->db->query(
                "SELECT COUNT(*) AS jml_item,
                        SUM(IF(soid!='',1,0)) AS jml_input,
                        SUM(IF(soid='',1,0)) AS jml_belum
                 FROM `$table`
                 WHERE toko_id=:toko_id:",
                ['toko_id' => $tokoId]
            )->getRowArray() ?: [];

            $history[] = [
                'table_name' => $table,
                'tanggal' => $tanggal,
                'jml_item' => (int) ($summary['jml_item'] ?? 0),
                'jml_input' => (int) ($summary['jml_input'] ?? 0),
                'jml_belum' => (int) ($summary['jml_belum'] ?? 0),
                'status' => $active && $active['so_table'] === $table ? 'AKTIF' : 'SELESAI',
            ];
        }

        return $history;
    }

    private function createSoTables(string $soTable, string $sodTable): void
    {
        $this->db->query("
            CREATE TABLE `$soTable` (
              `toko_id` VARCHAR(4) NOT NULL,
              `kode_item` VARCHAR(7) NOT NULL,
              `nama_item` VARCHAR(100) NOT NULL,
              `kat_id` VARCHAR(50) NOT NULL,
              `soid` CHAR(1) NOT NULL DEFAULT '',
              `com` FLOAT NOT NULL DEFAULT 0,
              `ttl` FLOAT NOT NULL DEFAULT 0,
              `sat_dasar` VARCHAR(50) NOT NULL,
              `hpp_dasar` INT(11) NOT NULL DEFAULT 0,
              `stok_konversi` VARCHAR(255) NOT NULL,
              `updid` VARCHAR(100) DEFAULT NULL,
              `updtime` DATETIME DEFAULT NULL,
              PRIMARY KEY (`toko_id`, `kode_item`)
            ) ENGINE=InnoDB;
        ");

        $this->db->query("
            CREATE TABLE `$sodTable` (
              `id` BIGINT NOT NULL AUTO_INCREMENT,
              `toko_id` VARCHAR(4) NOT NULL,
              `kode_item` VARCHAR(7) NOT NULL,
              `ttl` FLOAT NOT NULL DEFAULT 0,
              `sat_dasar` VARCHAR(50) NOT NULL,
              `updid` VARCHAR(100) DEFAULT NULL,
              `updtime` DATETIME DEFAULT NULL,
              PRIMARY KEY (`id`),
              KEY `idx_sod_1` (`toko_id`, `kode_item`, `updtime`)
            ) ENGINE=InnoDB;
        ");
    }

    private function seedSoTable(string $soTable, string $tokoId, array $kategoriIds): void
    {
        $binds = ['toko_id' => $tokoId];
        $filterKategori = '';
        if (!empty($kategoriIds)) {
            $placeholders = [];
            foreach ($kategoriIds as $idx => $katId) {
                $key = 'kat_' . $idx;
                $placeholders[] = ':' . $key . ':';
                $binds[$key] = $katId;
            }
            $filterKategori = ' AND p.kat_id IN (' . implode(',', $placeholders) . ') ';
        }

        $this->db->query(
            "INSERT INTO `$soTable` (`toko_id`,`kode_item`,`nama_item`,`kat_id`,`com`,`sat_dasar`,`hpp_dasar`,`stok_konversi`)
             SELECT
                :toko_id: AS toko_id,
                p.kode_item,
                p.nama_item,
                p.kat_id,
                COALESCE(st.qty, 0) AS com,
                base.sat_id AS sat_dasar,
                ROUND(COALESCE(store.harga_pokok, 0)) AS hpp_dasar,
                COALESCE(GROUP_CONCAT(
                    CONCAT(
                        ROUND(
                            CASE
                                WHEN COALESCE(ps.qty_konversi, 0) <= 0 THEN 0
                                ELSE COALESCE(st.qty, 0) / ps.qty_konversi
                            END
                        , 2),
                        ' ',
                        ps.sat_id
                    )
                    ORDER BY ps.qty_konversi, ps.sat_id
                    SEPARATOR ' , '
                ), '0') AS stok_konversi
             FROM prodmast p
             INNER JOIN (
                SELECT ps1.kode_item, ps1.sat_id, ps1.qty_konversi
                FROM prodmast_satuan ps1
                INNER JOIN (
                    SELECT kode_item, MIN(qty_konversi) AS min_qty
                    FROM prodmast_satuan
                    GROUP BY kode_item
                ) x ON x.kode_item=ps1.kode_item AND x.min_qty=ps1.qty_konversi
             ) base ON base.kode_item=p.kode_item
             LEFT JOIN prodmast_satuan ps ON ps.kode_item=p.kode_item
             LEFT JOIN stmast st ON st.toko_id=:toko_id: AND st.kode_item=p.kode_item
             LEFT JOIN prodmast_store store
                ON store.toko_id=:toko_id:
                AND store.kode_item=p.kode_item
                AND store.sat_id=base.sat_id
             WHERE EXISTS (
                SELECT 1
                FROM prodmast_store pst
                WHERE pst.toko_id=:toko_id:
                    AND pst.kode_item=p.kode_item
                    AND pst.status_item='Y'
             )
             $filterKategori
             GROUP BY p.kode_item, p.nama_item, p.kat_id, st.qty, base.sat_id, store.harga_pokok
             ORDER BY p.kat_id, p.nama_item, p.kode_item",
            $binds
        );
    }

    private function getSoTableName(string $tanggal, string $tokoId): string
    {
        return 'so_' . date('ymd', strtotime($tanggal)) . '_' . $this->getTokoSuffix($tokoId);
    }

    private function getSodTableName(string $tanggal, string $tokoId): string
    {
        return 'sod_' . date('ymd', strtotime($tanggal)) . '_' . $this->getTokoSuffix($tokoId);
    }

    private function getTokoSuffix(string $tokoId): string
    {
        return strtolower(preg_replace('/[^a-zA-Z0-9]/', '', $tokoId));
    }

    private function tableExists(string $tableName): bool
    {
        $row = $this->db->query(
            "SELECT COUNT(*) AS total
             FROM information_schema.tables
             WHERE table_schema = DATABASE()
               AND table_name = :table_name:",
            ['table_name' => $tableName]
        )->getRowArray();

        return (int) ($row['total'] ?? 0) > 0;
    }

    private function parseDateFromTableName(string $tableName): string
    {
        if (preg_match('/^so_(\d{6})_/', $tableName, $matches) !== 1) {
            return '';
        }
        $parsed = \DateTime::createFromFormat('ymd', $matches[1]);
        return $parsed ? $parsed->format('Y-m-d') : '';
    }
}
