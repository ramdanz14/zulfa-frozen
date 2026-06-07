<?php

namespace App\Models;

use CodeIgniter\Model;

class ReturJualModel extends Model
{
    protected $table = 'retur_jual';
    protected $returnType = 'array';
    protected $protectFields = false;

    public function getLimitDays(): int
    {
        $row = $this->db->query("SELECT nilai FROM const WHERE rkey='batas_retur_jual' LIMIT 1")->getRowArray();
        return max(0, (int) ($row['nilai'] ?? 7));
    }

    public function getNextId(string $tokoId): string
    {
        $prefix = 'RJ' . $tokoId . date('ymd');
        $row = $this->db->query(
            "SELECT MAX(CAST(RIGHT(rj_id,4) AS UNSIGNED)) AS nomor
             FROM retur_jual
             WHERE toko_id=:toko_id: AND rj_id LIKE :prefix_like:",
            ['toko_id' => $tokoId, 'prefix_like' => $prefix . '%']
        )->getRowArray();

        return $prefix . sprintf('%04d', ((int) ($row['nomor'] ?? 0)) + 1);
    }

    public function getPageData(string $tokoId, ?string $jualId = null): array
    {
        $sale = null;
        $error = null;
        if ($jualId !== null && trim($jualId) !== '') {
            $result = $this->getSaleReferencePayload($tokoId, trim($jualId));
            if (($result['tipe'] ?? '') === 'success') {
                $sale = $result['data'] ?? null;
            } else {
                $error = (string) ($result['data'] ?? 'Transaksi penjualan tidak ditemukan');
            }
        }

        return [
            'header' => [
                'rj_id' => $this->getNextId($tokoId),
                'tanggal' => date('Y-m-d'),
                'jual_id' => trim((string) $jualId),
                'keterangan' => '',
                'batas_hari' => $this->getLimitDays(),
            ],
            'sale' => $sale,
            'error' => $error,
        ];
    }

    public function getFormData(string $tokoId, ?string $rjId = null, ?string $jualId = null): array
    {
        $header = [
            'rj_id' => $this->getNextId($tokoId),
            'tanggal' => date('Y-m-d'),
            'jual_id' => trim((string) $jualId),
            'keterangan' => '',
            'batas_hari' => $this->getLimitDays(),
        ];
        $sale = null;
        $error = null;

        if ($rjId !== null && trim($rjId) !== '') {
            $document = $this->getReturSummary($tokoId, trim($rjId));
            if (!$document) {
                return ['header' => $header, 'sale' => null, 'error' => 'Retur penjualan tidak ditemukan'];
            }

            $header = [
                'rj_id' => (string) ($document['rj_id'] ?? ''),
                'tanggal' => substr((string) ($document['tanggal'] ?? date('Y-m-d')), 0, 10),
                'jual_id' => (string) ($document['jual_id'] ?? ''),
                'keterangan' => (string) ($document['keterangan'] ?? ''),
                'batas_hari' => $this->getLimitDays(),
            ];

            $result = $this->getSaleReferencePayload($tokoId, (string) ($document['jual_id'] ?? ''), trim($rjId));
            if (($result['tipe'] ?? '') === 'success') {
                $sale = $result['data'] ?? null;
                $savedMap = [];
                foreach (($document['details'] ?? []) as $row) {
                    $savedMap[(string) ($row['seq_no'] ?? '')] = $row;
                }
                foreach (($sale['details'] ?? []) as &$row) {
                    $saved = $savedMap[(string) ($row['seq_no'] ?? '')] ?? null;
                    if ($saved) {
                        $row['qty_retur'] = (float) ($saved['qty_retur'] ?? 0);
                        $row['gross_retur'] = (float) ($saved['gross_retur'] ?? 0);
                        $row['refund_unit'] = (float) ($saved['price'] ?? ($row['refund_unit'] ?? 0));
                    }
                }
                unset($row);
            } else {
                $error = (string) ($result['data'] ?? 'Transaksi penjualan tidak valid');
            }
        } elseif ($jualId !== null && trim($jualId) !== '') {
            $result = $this->getSaleReferencePayload($tokoId, trim($jualId));
            if (($result['tipe'] ?? '') === 'success') {
                $sale = $result['data'] ?? null;
            } else {
                $error = (string) ($result['data'] ?? 'Transaksi penjualan tidak ditemukan');
            }
        }

        return [
            'header' => $header,
            'sale' => $sale,
            'error' => $error,
        ];
    }

    public function ajaxList(array $params, string $tokoId): array
    {
        $start = (int) ($params['start'] ?? 0);
        $length = $params['length'] ?? 25;
        $search = trim((string) ($params['search_value'] ?? ''));
        $queryLimit = $length !== '-1' ? " LIMIT $start, " . (int) $length : '';
        $binds = ['toko_id' => $tokoId];
        $where = " WHERE r.toko_id=:toko_id: ";
        if ($search !== '') {
            $where .= " AND (r.rj_id LIKE :search: OR r.jual_id LIKE :search: OR COALESCE(c.nama,'') LIKE :search: OR COALESCE(r.updid,'') LIKE :search:)";
            $binds['search'] = '%' . $this->db->escapeLikeString($search) . '%';
        }

        $countRow = $this->db->query(
            "SELECT COUNT(*) AS total
             FROM retur_jual r
             LEFT JOIN penjualan j ON j.toko_id=r.toko_id AND j.jual_id=r.jual_id
             LEFT JOIN customer c ON c.cust_id=j.cust_id
             $where",
            $binds
        )->getRowArray();

        $data = $this->db->query(
            "SELECT r.*, COALESCE(c.nama,'Pelanggan Umum') AS customer_nama, COUNT(d.seq_no) AS jml_item
             FROM retur_jual r
             LEFT JOIN penjualan j ON j.toko_id=r.toko_id AND j.jual_id=r.jual_id
             LEFT JOIN customer c ON c.cust_id=j.cust_id
             LEFT JOIN retur_jual_detail d ON d.toko_id=r.toko_id AND d.rj_id=r.rj_id
             $where
             GROUP BY r.toko_id, r.rj_id
             ORDER BY r.tanggal DESC, r.rj_id DESC
             $queryLimit",
            $binds
        )->getResultArray();

        return [
            'data' => $data,
            'total_count' => (int) ($countRow['total'] ?? 0),
            'total_filtered' => (int) ($countRow['total'] ?? 0),
        ];
    }

    public function getSaleReferencePayload(string $tokoId, string $jualId, ?string $excludeRjId = null): array
    {
        $sale = $this->db->query(
            "SELECT j.*, COALESCE(c.nama, 'Pelanggan Umum') AS customer_nama, COALESCE(c.kontak,'') AS customer_kontak,
                    t.toko_nama, t.toko_alamat, t.toko_phone
             FROM penjualan j
             LEFT JOIN customer c ON c.cust_id=j.cust_id
             LEFT JOIN toko t ON t.toko_id=j.toko_id
             WHERE j.toko_id=:toko_id: AND j.jual_id=:jual_id:
             LIMIT 1",
            ['toko_id' => $tokoId, 'jual_id' => $jualId]
        )->getRowArray();

        if (!$sale) {
            return ['tipe' => 'error', 'data' => 'Transaksi penjualan tidak ditemukan'];
        }

        if ($this->hasExistingReturn($tokoId, $jualId, $excludeRjId)) {
            return ['tipe' => 'error', 'data' => 'Transaksi penjualan ini sudah pernah diretur'];
        }

        $saleDate = substr((string) ($sale['tgl'] ?? ''), 0, 10);
        $limitDays = $this->getLimitDays();
        $today = date('Y-m-d');
        $ageDays = (int) floor((strtotime($today . ' 00:00:00') - strtotime($saleDate . ' 00:00:00')) / 86400);
        if ($ageDays > $limitDays) {
            return ['tipe' => 'error', 'data' => 'Retur penjualan melewati batas maksimal ' . $limitDays . ' hari sejak transaksi'];
        }

        if (($sale['status_bayar'] ?? 'LUNAS') !== 'LUNAS') {
            return ['tipe' => 'error', 'data' => 'Retur penjualan hanya diizinkan untuk transaksi yang sudah lunas'];
        }

        $details = $this->db->query(
            "SELECT d.*, p.nama_item
             FROM penjualan_detail d
             LEFT JOIN prodmast p ON p.kode_item=d.kode_item
             WHERE d.toko_id=:toko_id: AND d.jual_id=:jual_id:
             ORDER BY d.seq_no ASC",
            ['toko_id' => $tokoId, 'jual_id' => $jualId]
        )->getResultArray();

        $detailNetTotal = 0.0;
        foreach ($details as $row) {
            $detailNetTotal += (float) ($row['netto'] ?? 0);
        }
        $detailNetTotal = max($detailNetTotal, 0.01);

        foreach ($details as &$row) {
            $shareRatio = (float) ($row['netto'] ?? 0) / $detailNetTotal;
            $allocatedNotaDiscount = round(((float) ($sale['diskon_nota'] ?? 0)) * $shareRatio, 2);
            $allocatedRedeem = round(((float) ($sale['redeem_nominal'] ?? 0)) * $shareRatio, 2);
            $refundableTotal = max(round((float) ($row['netto'] ?? 0) - $allocatedNotaDiscount - $allocatedRedeem, 2), 0);
            $qtyJual = max((float) ($row['qty_jual'] ?? 0), 0.0001);
            $refundUnit = round($refundableTotal / $qtyJual, 2);
            $row['qty_retur'] = 0;
            $row['gross_retur'] = 0;
            $row['refund_unit'] = $refundUnit;
            $row['refundable_total'] = $refundableTotal;
        }
        unset($row);

        $sale['details'] = $details;
        $sale['batas_hari'] = $limitDays;
        $sale['umur_hari'] = max($ageDays, 0);

        return ['tipe' => 'success', 'data' => $sale];
    }

    public function saveReturn(string $tokoId, string $username, array $payload, string $mode = 'create'): array
    {
        $rjId = trim((string) ($payload['rj_id'] ?? ''));
        $tanggal = trim((string) ($payload['tanggal'] ?? ''));
        $jualId = trim((string) ($payload['jual_id'] ?? ''));
        $keterangan = trim((string) ($payload['keterangan'] ?? ''));
        $detailRows = json_decode((string) ($payload['detail_json'] ?? '[]'), true) ?: [];

        $existing = null;
        if ($mode === 'create') {
            $rjId = $this->getNextId($tokoId);
        } else {
            if ($rjId === '') {
                return ['tipe' => 'error', 'data' => 'ID retur penjualan tidak valid'];
            }
            $existing = $this->getReturSummary($tokoId, $rjId);
            if (!$existing) {
                return ['tipe' => 'error', 'data' => 'Retur penjualan tidak ditemukan'];
            }
        }

        if ($tanggal === '' || $jualId === '') {
            return ['tipe' => 'error', 'data' => 'Tanggal retur dan nomor struk wajib diisi'];
        }

        $saleResult = $this->getSaleReferencePayload($tokoId, $jualId, $mode === 'edit' ? $rjId : null);
        if (($saleResult['tipe'] ?? '') !== 'success') {
            return $saleResult;
        }
        $sale = $saleResult['data'] ?? [];

        $detailMap = [];
        foreach (($sale['details'] ?? []) as $row) {
            $detailMap[(string) ($row['seq_no'] ?? '')] = $row;
        }

        $sanitized = [];
        $totalRetur = 0.0;
        foreach ($detailRows as $index => $row) {
            $seqNo = (string) ($row['seq_no'] ?? '');
            $qtyRetur = round((float) ($row['qty_retur'] ?? 0), 4);
            if ($qtyRetur <= 0) {
                continue;
            }

            $source = $detailMap[$seqNo] ?? null;
            if (!$source) {
                return ['tipe' => 'error', 'data' => 'Ada baris retur yang tidak valid'];
            }

            $qtyJual = round((float) ($source['qty_jual'] ?? 0), 4);
            if ($qtyRetur - $qtyJual > 0.0001) {
                return ['tipe' => 'error', 'data' => 'Qty retur item ' . ($source['kode_item'] ?? '-') . ' tidak boleh melebihi qty jual'];
            }

            $qtyKonversi = round((float) ($source['qty_konversi'] ?? 1), 4);
            $qtyStock = round($qtyRetur * $qtyKonversi, 4);
            $refundUnit = round((float) ($row['refund_unit'] ?? $source['refund_unit'] ?? 0), 2);
            $grossRetur = round($qtyRetur * $refundUnit, 2);
            $totalRetur += $grossRetur;

            $sanitized[] = [
                'rj_id' => $rjId,
                'toko_id' => $tokoId,
                'seq_no' => $index + 1,
                'kode_item' => (string) ($source['kode_item'] ?? ''),
                'sat_id' => (string) ($source['sat_id'] ?? ''),
                'qty_jual' => $qtyJual,
                'qty_retur' => $qtyRetur,
                'qty_konversi' => $qtyKonversi,
                'qty_stock' => $qtyStock,
                'price' => $refundUnit,
                'gross_retur' => $grossRetur,
            ];
        }

        if (empty($sanitized)) {
            return ['tipe' => 'error', 'data' => 'Minimal satu item harus diisi qty retur lebih besar dari nol'];
        }

        $returnDate = substr($tanggal, 0, 10);
        $saleDate = substr((string) ($sale['tgl'] ?? ''), 0, 10);
        $maxReturnDate = date('Y-m-d', strtotime($saleDate . ' +' . $this->getLimitDays() . ' days'));
        if ($returnDate < $saleDate || $returnDate > $maxReturnDate) {
            return ['tipe' => 'error', 'data' => 'Tanggal retur harus berada dalam rentang ' . $saleDate . ' sampai ' . $maxReturnDate];
        }

        $karyawanId = $this->resolveKaryawanIdByUsername($username);
        if ($karyawanId === '') {
            return ['tipe' => 'error', 'data' => 'User login tidak memiliki karyawan_id untuk pencatatan kas retur'];
        }

        $timestamp = $returnDate . ' ' . date('H:i:s');
        $this->db->transStart();

        if ($existing) {
            $this->reverseCustomerPointsAfterReturn($tokoId, $existing, $sale, $timestamp, $username);
            $this->db->table('kas_mutasi')
                ->where('toko_id', $tokoId)
                ->where('nama_akun', 'RETUR PENJUALAN')
                ->where('keterangan', $rjId)
                ->delete();
            $this->db->table('retur_jual_detail')
                ->where('toko_id', $tokoId)
                ->where('rj_id', $rjId)
                ->delete();
            $this->db->table('retur_jual')
                ->where('toko_id', $tokoId)
                ->where('rj_id', $rjId)
                ->update([
                    'jual_id' => $jualId,
                    'tanggal' => $timestamp,
                    'gross_retur' => round($totalRetur, 2),
                    'updid' => $username,
                    'keterangan' => $keterangan !== '' ? $keterangan : null,
                ]);
        } else {
            $this->db->table('retur_jual')->insert([
                'rj_id' => $rjId,
                'toko_id' => $tokoId,
                'jual_id' => $jualId,
                'tanggal' => $timestamp,
                'gross_retur' => round($totalRetur, 2),
                'updid' => $username,
                'keterangan' => $keterangan !== '' ? $keterangan : null,
            ]);
        }

        foreach ($sanitized as $row) {
            $this->db->table('retur_jual_detail')->insert($row);
        }

        $this->ensureCashAccount($username);
        $this->db->table('kas_mutasi')->insert([
            'tanggal' => $timestamp,
            'toko_id' => $tokoId,
            'nama_akun' => 'RETUR PENJUALAN',
            'nominal' => (int) round($totalRetur),
            'karyawan_id' => $karyawanId,
            'keterangan' => $rjId,
            'updid' => $username,
        ]);

        $this->syncCustomerPointsAfterReturn($tokoId, $sale, round($totalRetur, 2), $timestamp, $username);
        HitungStock($tokoId);

        $this->db->transComplete();
        if (!$this->db->transStatus()) {
            return ['tipe' => 'error', 'data' => 'Gagal menyimpan retur penjualan'];
        }

        return [
            'tipe' => 'success',
            'data' => $existing ? 'Retur penjualan berhasil diupdate' : 'Retur penjualan berhasil disimpan',
            'rj_id' => $rjId,
        ];
    }

    public function deleteReturn(string $tokoId, string $username, string $rjId): array
    {
        $existing = $this->getReturSummary($tokoId, $rjId);
        if (!$existing) {
            return ['tipe' => 'error', 'data' => 'Retur penjualan tidak ditemukan'];
        }

        $sale = $this->db->query(
            "SELECT * FROM penjualan WHERE toko_id=:toko_id: AND jual_id=:jual_id: LIMIT 1",
            ['toko_id' => $tokoId, 'jual_id' => (string) ($existing['jual_id'] ?? '')]
        )->getRowArray() ?: [];

        $timestamp = date('Y-m-d H:i:s');
        $this->db->transStart();
        $this->reverseCustomerPointsAfterReturn($tokoId, $existing, $sale, $timestamp, $username);
        $this->db->table('kas_mutasi')
            ->where('toko_id', $tokoId)
            ->where('nama_akun', 'RETUR PENJUALAN')
            ->where('keterangan', $rjId)
            ->delete();
        $this->db->table('retur_jual')
            ->where('toko_id', $tokoId)
            ->where('rj_id', $rjId)
            ->delete();
        HitungStock($tokoId);
        $this->db->transComplete();

        if (!$this->db->transStatus()) {
            return ['tipe' => 'error', 'data' => 'Gagal menghapus retur penjualan'];
        }

        return ['tipe' => 'success', 'data' => 'Retur penjualan berhasil dihapus'];
    }

    public function getReturSummary(string $tokoId, string $rjId): ?array
    {
        $header = $this->db->query(
            "SELECT r.*, j.tgl AS tanggal_jual, COALESCE(c.nama,'Pelanggan Umum') AS customer_nama, COALESCE(c.kontak,'') AS customer_kontak,
                    t.toko_nama, t.toko_alamat, t.toko_phone
             FROM retur_jual r
             LEFT JOIN penjualan j ON j.toko_id=r.toko_id AND j.jual_id=r.jual_id
             LEFT JOIN customer c ON c.cust_id=j.cust_id
             LEFT JOIN toko t ON t.toko_id=r.toko_id
             WHERE r.toko_id=:toko_id: AND r.rj_id=:rj_id:
             LIMIT 1",
            ['toko_id' => $tokoId, 'rj_id' => $rjId]
        )->getRowArray();

        if (!$header) {
            return null;
        }

        $header['details'] = $this->db->query(
            "SELECT d.*, p.nama_item
             FROM retur_jual_detail d
             LEFT JOIN prodmast p ON p.kode_item=d.kode_item
             WHERE d.toko_id=:toko_id: AND d.rj_id=:rj_id:
             ORDER BY d.seq_no ASC",
            ['toko_id' => $tokoId, 'rj_id' => $rjId]
        )->getResultArray();

        return $header;
    }

    private function hasExistingReturn(string $tokoId, string $jualId, ?string $excludeRjId = null): bool
    {
        $sql = "SELECT rj_id FROM retur_jual WHERE toko_id=:toko_id: AND jual_id=:jual_id:";
        $binds = ['toko_id' => $tokoId, 'jual_id' => $jualId];
        if ($excludeRjId !== null && $excludeRjId !== '') {
            $sql .= " AND rj_id<>:exclude_rj_id:";
            $binds['exclude_rj_id'] = $excludeRjId;
        }
        $sql .= " LIMIT 1";
        $row = $this->db->query($sql, $binds)->getRowArray();

        return !empty($row);
    }

    private function ensureCashAccount(string $username): void
    {
        $row = $this->db->query("SELECT nama_akun FROM akun_kas WHERE nama_akun='RETUR PENJUALAN' LIMIT 1")->getRowArray();
        if ($row) {
            return;
        }

        $this->db->table('akun_kas')->insert([
            'nama_akun' => 'RETUR PENJUALAN',
            'jenis_akun' => 'KELUAR',
            'updid' => $username,
        ]);
    }

    private function resolveKaryawanIdByUsername(string $username): string
    {
        $row = $this->db->query(
            "SELECT karyawan_id FROM tb_user WHERE username=:username: LIMIT 1",
            ['username' => $username]
        )->getRowArray();
        return trim((string) ($row['karyawan_id'] ?? ''));
    }

    private function syncCustomerPointsAfterReturn(string $tokoId, array $sale, float $totalRetur, string $timestamp, string $username): void
    {
        $custId = (string) ($sale['cust_id'] ?? 'CUST-GENERAL');
        if ($custId === 'CUST-GENERAL' || $totalRetur <= 0) {
            return;
        }

        $nettoSale = max((float) ($sale['netto'] ?? 0), 0.01);
        $earnedPoints = (int) ($sale['earned_points'] ?? 0);
        $redeemPoints = (int) ($sale['redeem_points'] ?? 0);

        $reverseEarned = min($earnedPoints, (int) round(($totalRetur / $nettoSale) * $earnedPoints));
        $restoreRedeem = min($redeemPoints, (int) round(($totalRetur / $nettoSale) * $redeemPoints));

        if ($reverseEarned > 0) {
            $this->mutateCustomerPoints($tokoId, $custId, (string) ($sale['jual_id'] ?? ''), 'kurang', $reverseEarned, $timestamp, $username, 'Rollback poin karena retur penjualan ' . ($sale['jual_id'] ?? ''));
        }
        if ($restoreRedeem > 0) {
            $this->mutateCustomerPoints($tokoId, $custId, (string) ($sale['jual_id'] ?? ''), 'tambah', $restoreRedeem, $timestamp, $username, 'Pengembalian poin redeem karena retur penjualan ' . ($sale['jual_id'] ?? ''));
        }
    }

    private function reverseCustomerPointsAfterReturn(string $tokoId, array $retur, array $sale, string $timestamp, string $username): void
    {
        $custId = (string) ($sale['cust_id'] ?? $retur['cust_id'] ?? 'CUST-GENERAL');
        if ($custId === 'CUST-GENERAL') {
            return;
        }

        $nettoSale = max((float) ($sale['netto'] ?? 0), 0.01);
        $returNominal = (float) ($retur['gross_retur'] ?? 0);
        if ($returNominal <= 0) {
            return;
        }

        $earnedPoints = (int) ($sale['earned_points'] ?? 0);
        $redeemPoints = (int) ($sale['redeem_points'] ?? 0);
        $reverseEarned = min($earnedPoints, (int) round(($returNominal / $nettoSale) * $earnedPoints));
        $restoreRedeem = min($redeemPoints, (int) round(($returNominal / $nettoSale) * $redeemPoints));

        if ($reverseEarned > 0) {
            $this->mutateCustomerPoints($tokoId, $custId, (string) ($sale['jual_id'] ?? ''), 'tambah', $reverseEarned, $timestamp, $username, 'Pembatalan rollback poin karena edit/hapus retur penjualan ' . ($sale['jual_id'] ?? ''));
        }
        if ($restoreRedeem > 0) {
            $this->mutateCustomerPoints($tokoId, $custId, (string) ($sale['jual_id'] ?? ''), 'kurang', $restoreRedeem, $timestamp, $username, 'Pembatalan pengembalian poin redeem karena edit/hapus retur penjualan ' . ($sale['jual_id'] ?? ''));
        }
    }

    private function mutateCustomerPoints(string $tokoId, string $custId, string $trxId, string $jenis, int $points, string $tanggal, string $username, string $keterangan): void
    {
        if ($points <= 0) {
            return;
        }

        $customer = $this->db->query(
            "SELECT poin FROM customer WHERE cust_id=:cust_id: LIMIT 1",
            ['cust_id' => $custId]
        )->getRowArray();
        if (!$customer) {
            return;
        }

        $before = (int) ($customer['poin'] ?? 0);
        $after = $jenis === 'tambah' ? $before + $points : max(0, $before - $points);

        $this->db->table('customer')->where('cust_id', $custId)->update(['poin' => $after]);
        $this->db->table('history_poin_member')->insert([
            'cust_id' => $custId,
            'toko_id' => $tokoId,
            'trx_id' => $trxId,
            'tanggal' => $tanggal,
            'jenis' => $jenis,
            'nominal_transaksi' => 0,
            'nominal_per_poin' => 1,
            'poin_masuk' => $jenis === 'tambah' ? $points : 0,
            'poin_keluar' => $jenis === 'kurang' ? $points : 0,
            'poin_before' => $before,
            'poin_after' => $after,
            'keterangan' => $keterangan,
            'updid' => $username,
        ]);
    }
}
