<?php

namespace App\Models;

use CodeIgniter\Model;

class TransferModel extends Model
{
    protected $table = 'transfer_toko';
    protected $returnType = 'array';
    protected $protectFields = false;

    protected PembelianModel $pembelianModel;
    protected JualModel $jualModel;
    protected TokoModel $tokoModel;

    public function __construct()
    {
        parent::__construct();
        $this->pembelianModel = new PembelianModel();
        $this->jualModel = new JualModel();
        $this->tokoModel = new TokoModel();
    }

    public function getStoreContext(string $tokoId): array
    {
        $toko = $this->tokoModel->getById($tokoId) ?: [];
        $isGudang = strtoupper((string) ($toko['flag_gudang'] ?? 'N')) === 'Y';

        return [
            'toko' => $toko,
            'is_gudang' => $isGudang,
            'mode_label' => $isGudang ? 'KIRIM' : 'TERIMA',
        ];
    }

    public function getNextId(string $gudangTokoId): string
    {
        $prefix = 'TR' . $gudangTokoId . date('ymd');
        $row = $this->db->query(
            "SELECT MAX(CAST(RIGHT(transfer_id,4) AS UNSIGNED)) AS nomor
             FROM transfer_toko
             WHERE gudang_toko_id=:gudang_toko_id: AND transfer_id LIKE :prefix_like:",
            [
                'gudang_toko_id' => $gudangTokoId,
                'prefix_like' => $prefix . '%',
            ]
        )->getRowArray();

        return $prefix . sprintf('%04d', ((int) ($row['nomor'] ?? 0)) + 1);
    }

    public function ajaxPendingPo(array $params, string $gudangTokoId): array
    {
        $start = (int) ($params['start'] ?? 0);
        $length = $params['length'] ?? 25;
        $search = trim((string) ($params['search_value'] ?? ''));
        $queryLimit = $length !== '-1' ? " LIMIT $start, " . (int) $length : '';

        $binds = ['gudang_toko_id' => $gudangTokoId];
        $where = "
            WHERE p.status_nota='PO'
              AND tujuan.flag_gudang!='Y'
              AND p.supco=:gudang_toko_id:
              AND NOT EXISTS (
                SELECT 1
                FROM transfer_toko t
                WHERE t.po_toko_id=p.toko_id
                  AND t.po_beli_id=p.beli_id
                  AND t.status_transfer IN ('DRAFT','KIRIM','APPROVED')
              )
        ";

        if ($search !== '') {
            $where .= " AND (
                p.beli_id LIKE :search:
                OR p.invoice LIKE :search:
                OR tujuan.toko_nama LIKE :search:
                OR p.toko_id LIKE :search:
            )";
            $binds['search'] = '%' . $this->db->escapeLikeString($search) . '%';
        }

        $total = $this->db->query(
            "SELECT COUNT(*) AS total
             FROM pembelian p
             INNER JOIN toko tujuan ON tujuan.toko_id=p.toko_id
             $where",
            $binds
        )->getRowArray();

        $data = $this->db->query(
            "SELECT p.toko_id AS tujuan_toko_id,
                    tujuan.toko_nama AS tujuan_toko_nama,
                    p.beli_id,
                    p.tanggal,
                    p.invoice,
                    p.total_gross,
                    COUNT(pd.seq_no) AS jml_item
             FROM pembelian p
             INNER JOIN toko tujuan ON tujuan.toko_id=p.toko_id
             LEFT JOIN pembelian_detail pd ON pd.toko_id=p.toko_id AND pd.beli_id=p.beli_id
             $where
             GROUP BY p.toko_id, p.beli_id
             ORDER BY p.tanggal ASC, p.beli_id ASC
             $queryLimit",
            $binds
        )->getResultArray();

        return [
            'data' => $data,
            'total_count' => (int) ($total['total'] ?? 0),
            'total_filtered' => (int) ($total['total'] ?? 0),
        ];
    }

    public function ajaxTransfers(array $params, string $tokoId, bool $isGudang): array
    {
        $start = (int) ($params['start'] ?? 0);
        $length = $params['length'] ?? 25;
        $search = trim((string) ($params['search_value'] ?? ''));
        $queryLimit = $length !== '-1' ? " LIMIT $start, " . (int) $length : '';

        $binds = ['toko_id' => $tokoId];
        $where = $isGudang
            ? " WHERE t.gudang_toko_id=:toko_id: "
            : " WHERE t.tujuan_toko_id=:toko_id: ";

        if ($search !== '') {
            $where .= " AND (
                t.transfer_id LIKE :search:
                OR t.po_beli_id LIKE :search:
                OR COALESCE(t.jual_id, '') LIKE :search:
                OR COALESCE(t.beli_id, '') LIKE :search:
                OR tujuan.toko_nama LIKE :search:
                OR gudang.toko_nama LIKE :search:
            )";
            $binds['search'] = '%' . $this->db->escapeLikeString($search) . '%';
        }

        $count = $this->db->query(
            "SELECT COUNT(*) AS total
             FROM transfer_toko t
             LEFT JOIN toko tujuan ON tujuan.toko_id=t.tujuan_toko_id
             LEFT JOIN toko gudang ON gudang.toko_id=t.gudang_toko_id
             $where",
            $binds
        )->getRowArray();

        $data = $this->db->query(
            "SELECT t.*,
                    tujuan.toko_nama AS tujuan_toko_nama,
                    gudang.toko_nama AS gudang_toko_nama,
                    COUNT(td.seq_no) AS jml_item
             FROM transfer_toko t
             LEFT JOIN toko tujuan ON tujuan.toko_id=t.tujuan_toko_id
             LEFT JOIN toko gudang ON gudang.toko_id=t.gudang_toko_id
             LEFT JOIN transfer_toko_detail td ON td.transfer_id=t.transfer_id
             $where
             GROUP BY t.transfer_id
             ORDER BY t.updtime DESC, t.transfer_id DESC
             $queryLimit",
            $binds
        )->getResultArray();

        return [
            'data' => $data,
            'total_count' => (int) ($count['total'] ?? 0),
            'total_filtered' => (int) ($count['total'] ?? 0),
        ];
    }

    public function getDraftFromPo(string $gudangTokoId, string $poTokoId, string $poBeliId): ?array
    {
        $po = $this->db->query(
            "SELECT p.*, tujuan.toko_nama AS tujuan_toko_nama
             FROM pembelian p
             INNER JOIN toko tujuan ON tujuan.toko_id=p.toko_id
             WHERE p.toko_id=:po_toko_id:
               AND p.beli_id=:po_beli_id:
               AND p.status_nota='PO'
               AND p.supco=:gudang_toko_id:
             LIMIT 1",
            [
                'po_toko_id' => $poTokoId,
                'po_beli_id' => $poBeliId,
                'gudang_toko_id' => $gudangTokoId,
            ]
        )->getRowArray();

        if (! $po) {
            return null;
        }

        $details = $this->db->query(
            "SELECT d.*, p.nama_item, p.barcode
             FROM pembelian_detail d
             LEFT JOIN prodmast p ON p.kode_item=d.kode_item
             WHERE d.toko_id=:po_toko_id: AND d.beli_id=:po_beli_id:
             ORDER BY d.seq_no ASC",
            [
                'po_toko_id' => $poTokoId,
                'po_beli_id' => $poBeliId,
            ]
        )->getResultArray();

        $mappedDetails = [];
        foreach ($details as $idx => $detail) {
            $payload = $this->getItemPayload($gudangTokoId, (string) ($detail['kode_item'] ?? ''));
            if ($payload) {
                $selected = $payload['satuan'][0] ?? null;
                foreach (($payload['satuan'] ?? []) as $option) {
                    if (($option['sat_id'] ?? '') === ($detail['sat_id'] ?? '')) {
                        $selected = $option;
                        break;
                    }
                }

                $mappedDetails[] = [
                    'seq_no' => $idx + 1,
                    'kode_item' => $detail['kode_item'],
                    'barcode' => $payload['barcode'] ?? ($detail['barcode'] ?? ''),
                    'nama_item' => $payload['nama_item'] ?? ($detail['nama_item'] ?? $detail['kode_item']),
                    'qty_po' => (float) ($detail['qty_beli'] ?? 0),
                    'stok_base' => (float) ($payload['stok_base'] ?? 0),
                    'sat_id' => $selected['sat_id'] ?? ($detail['sat_id'] ?? ''),
                    'qty_konversi' => (float) ($selected['qty_konversi'] ?? ($detail['qty_konversi'] ?? 1)),
                    'qty_kirim' => (float) ($detail['qty_beli'] ?? 0),
                    'qty_stock' => round((float) ($detail['qty_beli'] ?? 0) * (float) ($selected['qty_konversi'] ?? ($detail['qty_konversi'] ?? 1)), 4),
                    'harga_pokok' => (float) ($selected['harga_pokok'] ?? 0),
                    'harga_jual' => (float) ($selected['harga_jual_transfer'] ?? 0),
                    'gross' => round((float) ($detail['qty_beli'] ?? 0) * (float) ($selected['harga_jual_transfer'] ?? 0), 2),
                    'satuan_options' => $payload['satuan'],
                    'item_error' => '',
                ];
                continue;
            }

            $mappedDetails[] = [
                'seq_no' => $idx + 1,
                'kode_item' => $detail['kode_item'],
                'barcode' => $detail['barcode'] ?? '',
                'nama_item' => $detail['nama_item'] ?? $detail['kode_item'],
                'qty_po' => (float) ($detail['qty_beli'] ?? 0),
                'stok_base' => 0,
                'sat_id' => $detail['sat_id'] ?? '',
                'qty_konversi' => (float) ($detail['qty_konversi'] ?? 1),
                'qty_kirim' => 0,
                'qty_stock' => 0,
                'harga_pokok' => 0,
                'harga_jual' => 0,
                'gross' => 0,
                'satuan_options' => [],
                'item_error' => 'Item tidak aktif atau belum tersedia di gudang',
            ];
        }

        return [
            'header' => [
                'transfer_id' => $this->getNextId($gudangTokoId),
                'po_toko_id' => $poTokoId,
                'po_beli_id' => $poBeliId,
                'tujuan_toko_id' => $poTokoId,
                'tujuan_toko_nama' => $po['tujuan_toko_nama'] ?? $poTokoId,
                'gudang_toko_id' => $gudangTokoId,
                'tanggal_transfer' => date('Y-m-d'),
                'keterangan' => '',
                'invoice_po' => $po['invoice'] ?? '',
            ],
            'details' => $mappedDetails,
        ];
    }

    public function getFormData(string $gudangTokoId, string $transferId): ?array
    {
        $header = $this->db->query(
            "SELECT t.*, tujuan.toko_nama AS tujuan_toko_nama
             FROM transfer_toko t
             LEFT JOIN toko tujuan ON tujuan.toko_id=t.tujuan_toko_id
             WHERE t.transfer_id=:transfer_id: AND t.gudang_toko_id=:gudang_toko_id:
             LIMIT 1",
            ['transfer_id' => $transferId, 'gudang_toko_id' => $gudangTokoId]
        )->getRowArray();

        if (! $header) {
            return null;
        }

        $details = $this->db->query(
            "SELECT td.*, p.nama_item, p.barcode
             FROM transfer_toko_detail td
             LEFT JOIN prodmast p ON p.kode_item=td.kode_item
             WHERE td.transfer_id=:transfer_id:
             ORDER BY td.seq_no ASC",
            ['transfer_id' => $transferId]
        )->getResultArray();

        foreach ($details as &$row) {
            $payload = $this->getItemPayload($gudangTokoId, (string) ($row['kode_item'] ?? ''));
            $row['stok_base'] = (float) ($payload['stok_base'] ?? 0);
            $row['satuan_options'] = $payload['satuan'] ?? [];
            $row['item_error'] = $payload ? '' : 'Item tidak aktif atau belum tersedia di gudang';
        }
        unset($row);

        return ['header' => $header, 'details' => $details];
    }

    public function saveDraft(string $gudangTokoId, string $username, array $input, string $mode = 'create'): array
    {
        $transferId = trim((string) ($input['transfer_id'] ?? ''));
        $poTokoId = trim((string) ($input['po_toko_id'] ?? ''));
        $poBeliId = trim((string) ($input['po_beli_id'] ?? ''));
        $tujuanTokoId = trim((string) ($input['tujuan_toko_id'] ?? ''));
        $tanggalTransfer = trim((string) ($input['tanggal_transfer'] ?? date('Y-m-d')));
        $keterangan = trim((string) ($input['keterangan'] ?? ''));
        $detailRows = json_decode((string) ($input['detail_json'] ?? '[]'), true) ?: [];

        if ($poTokoId === '' || $poBeliId === '' || $tujuanTokoId === '') {
            return ['tipe' => 'error', 'data' => 'Referensi PO transfer tidak lengkap'];
        }
        if (empty($detailRows)) {
            return ['tipe' => 'error', 'data' => 'Detail transfer belum diisi'];
        }

        $po = $this->db->query(
            "SELECT tanggal
             FROM pembelian
             WHERE toko_id=:po_toko_id:
               AND beli_id=:po_beli_id:
               AND status_nota='PO'
               AND supco=:gudang_toko_id:
             LIMIT 1",
            [
                'po_toko_id' => $poTokoId,
                'po_beli_id' => $poBeliId,
                'gudang_toko_id' => $gudangTokoId,
            ]
        )->getRowArray();

        if (! $po) {
            return ['tipe' => 'error', 'data' => 'PO cabang tidak ditemukan atau sudah tidak aktif'];
        }

        $sanitizedDetails = [];
        $seq = 1;
        foreach ($detailRows as $row) {
            $kodeItem = trim((string) ($row['kode_item'] ?? ''));
            $satId = trim((string) ($row['sat_id'] ?? ''));
            $qtyKirim = (float) ($row['qty_kirim'] ?? 0);
            $qtyPo = (float) ($row['qty_po'] ?? 0);
            $qtyKonversi = (float) ($row['qty_konversi'] ?? 0);
            $qtyStock = (float) ($row['qty_stock'] ?? 0);
            $hargaPokok = (float) ($row['harga_pokok'] ?? 0);
            $hargaJual = (float) ($row['harga_jual'] ?? 0);
            $gross = (float) ($row['gross'] ?? 0);

            if ($kodeItem === '' || $satId === '' || $qtyKonversi <= 0) {
                return ['tipe' => 'error', 'data' => 'Ada baris transfer yang belum lengkap'];
            }

            $qtyKirim = max($qtyKirim, 0);
            $qtyStock = $qtyKirim > 0 ? round($qtyKirim * $qtyKonversi, 4) : 0;
            $gross = $qtyKirim > 0 ? round($qtyKirim * $hargaJual, 2) : 0;
            $sanitizedDetails[] = [
                'transfer_id' => '',
                'seq_no' => $seq++,
                'kode_item' => $kodeItem,
                'sat_id' => $satId,
                'qty_po' => round($qtyPo, 4),
                'qty_kirim' => round($qtyKirim, 4),
                'qty_konversi' => round($qtyKonversi, 4),
                'qty_stock' => $qtyStock,
                'harga_pokok' => round($hargaPokok, 2),
                'harga_jual' => round($hargaJual, 2),
                'gross' => $gross,
            ];
        }

        if ($mode === 'create') {
            $transferId = $this->getNextId($gudangTokoId);
        } else {
            $header = $this->db->query(
                "SELECT status_transfer
                 FROM transfer_toko
                 WHERE transfer_id=:transfer_id: AND gudang_toko_id=:gudang_toko_id:
                 LIMIT 1",
                ['transfer_id' => $transferId, 'gudang_toko_id' => $gudangTokoId]
            )->getRowArray();
            if (! $header) {
                return ['tipe' => 'error', 'data' => 'Draft transfer tidak ditemukan'];
            }
            if (($header['status_transfer'] ?? '') !== 'DRAFT') {
                return ['tipe' => 'error', 'data' => 'Hanya draft transfer yang bisa diedit'];
            }
        }

        $this->db->transStart();

        $headerData = [
            'transfer_id' => $transferId,
            'gudang_toko_id' => $gudangTokoId,
            'tujuan_toko_id' => $tujuanTokoId,
            'po_toko_id' => $poTokoId,
            'po_beli_id' => $poBeliId,
            'tanggal_po' => $po['tanggal'] ?? date('Y-m-d'),
            'tanggal_transfer' => $tanggalTransfer,
            'status_transfer' => 'DRAFT',
            'keterangan' => $keterangan !== '' ? $keterangan : null,
            'created_by' => $username,
        ];

        if ($mode === 'create') {
            $this->db->table('transfer_toko')->insert($headerData);
        } else {
            $this->db->table('transfer_toko')
                ->where('transfer_id', $transferId)
                ->where('gudang_toko_id', $gudangTokoId)
                ->update($headerData);
            $this->db->table('transfer_toko_detail')
                ->where('transfer_id', $transferId)
                ->delete();
        }

        foreach ($sanitizedDetails as $row) {
            $row['transfer_id'] = $transferId;
            $this->db->table('transfer_toko_detail')->insert($row);
        }

        $this->db->transComplete();

        if (! $this->db->transStatus()) {
            return ['tipe' => 'error', 'data' => 'Gagal menyimpan draft transfer'];
        }

        return [
            'tipe' => 'success',
            'data' => 'Draft transfer berhasil disimpan',
            'transfer_id' => $transferId,
        ];
    }

    public function sendTransfer(string $gudangTokoId, string $username, string $transferId): array
    {
        $transfer = $this->getTransferSummary($transferId, $gudangTokoId, true);
        if (! $transfer) {
            return ['tipe' => 'error', 'data' => 'Draft transfer tidak ditemukan'];
        }
        if (($transfer['status_transfer'] ?? '') !== 'DRAFT') {
            return ['tipe' => 'error', 'data' => 'Hanya draft transfer yang bisa dikirim'];
        }

        $detailRows = [];
        foreach (($transfer['details'] ?? []) as $row) {
            $qtyKirim = (float) ($row['qty_kirim'] ?? 0);
            if ($qtyKirim <= 0) {
                continue;
            }
            $detailRows[] = [
                'kode_item' => $row['kode_item'],
                'sat_id' => $row['sat_id'],
                'qty_jual' => $qtyKirim,
                'qty_konversi' => (float) ($row['qty_konversi'] ?? 1),
                'harga_pokok' => (float) ($row['harga_pokok'] ?? 0),
                'price' => (float) ($row['harga_jual'] ?? 0),
                'gross' => (float) ($row['gross'] ?? 0),
            ];
        }

        if (empty($detailRows)) {
            return ['tipe' => 'error', 'data' => 'Minimal satu item harus memiliki qty kirim lebih besar dari nol'];
        }

        $this->syncBranchCustomer();

        $saleResult = $this->jualModel->saveInterbranchTransferSale(
            $gudangTokoId,
            $username,
            (string) ($transfer['tujuan_toko_id'] ?? ''),
            $detailRows,
            date('Y-m-d', strtotime('+30 days')),
            'Transfer antar toko ' . $transferId
        );
        if (($saleResult['tipe'] ?? '') !== 'success') {
            return $saleResult;
        }

        $deletePoResult = $this->pembelianModel->deletePurchase(
            (string) ($transfer['po_toko_id'] ?? ''),
            (string) ($transfer['po_beli_id'] ?? '')
        );
        if (($deletePoResult['tipe'] ?? '') !== 'success') {
            $this->jualModel->cancelTransferSale(
                $gudangTokoId,
                $username,
                (string) ($saleResult['jual_id'] ?? ''),
                'Rollback kirim transfer antar toko'
            );
            return ['tipe' => 'error', 'data' => 'Gagal menghapus draft PO cabang setelah kirim'];
        }

        $this->db->table('transfer_toko')
            ->where('transfer_id', $transferId)
            ->where('gudang_toko_id', $gudangTokoId)
            ->update([
                'jual_id' => $saleResult['jual_id'] ?? null,
                'status_transfer' => 'KIRIM',
                'tanggal_kirim' => date('Y-m-d H:i:s'),
                'created_by' => $username,
            ]);

        return [
            'tipe' => 'success',
            'data' => 'Transfer berhasil dikirim ke cabang',
            'jual_id' => $saleResult['jual_id'] ?? null,
        ];
    }

    public function approveTransfer(string $tujuanTokoId, string $username, string $transferId, array $checkedSeqs): array
    {
        $transfer = $this->getTransferSummary($transferId, $tujuanTokoId, false);
        if (! $transfer) {
            return ['tipe' => 'error', 'data' => 'Transfer masuk tidak ditemukan'];
        }
        if (($transfer['status_transfer'] ?? '') !== 'KIRIM') {
            return ['tipe' => 'error', 'data' => 'Transfer ini tidak dalam status menunggu approve'];
        }

        $positiveSeqs = [];
        $purchaseDetails = [];
        foreach (($transfer['details'] ?? []) as $row) {
            if ((float) ($row['qty_kirim'] ?? 0) <= 0) {
                continue;
            }
            $positiveSeqs[] = (int) ($row['seq_no'] ?? 0);
            $purchaseDetails[] = [
                'kode_item' => $row['kode_item'],
                'qty_beli' => (float) ($row['qty_kirim'] ?? 0),
                'sat_id' => $row['sat_id'],
                'qty_konversi' => (float) ($row['qty_konversi'] ?? 1),
                'price' => (float) ($row['harga_jual'] ?? 0),
                'gross' => (float) ($row['gross'] ?? 0),
            ];
        }

        sort($positiveSeqs);
        $checkedSeqs = array_values(array_unique(array_map('intval', $checkedSeqs)));
        sort($checkedSeqs);
        if ($positiveSeqs !== $checkedSeqs) {
            return ['tipe' => 'error', 'data' => 'Semua item harus dicentang sebelum approve transfer'];
        }

        $this->pembelianModel->syncGudangSuppliers();

        $payload = [
            'tanggal' => date('Y-m-d'),
            'supco' => (string) ($transfer['gudang_toko_id'] ?? ''),
            'invoice' => 'TRF-' . $transferId,
            'status_nota' => 'TERIMA',
            'keterangan' => 'Approve transfer antar toko ' . $transferId,
            'jatuh_tempo' => date('Y-m-d', strtotime('+30 days')),
            'detail_json' => json_encode($purchaseDetails, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES),
            'payment_json' => json_encode([], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES),
            'deleted_payment_ids' => json_encode([], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES),
        ];

        $result = $this->pembelianModel->savePurchase($tujuanTokoId, $username, $payload, 'create');
        if (($result['tipe'] ?? '') !== 'success') {
            return $result;
        }

        $this->db->table('transfer_toko')
            ->where('transfer_id', $transferId)
            ->where('tujuan_toko_id', $tujuanTokoId)
            ->update([
                'status_transfer' => 'APPROVED',
                'beli_id' => $result['beli_id'] ?? null,
                'tanggal_approve' => date('Y-m-d H:i:s'),
                'approved_by' => $username,
            ]);

        return [
            'tipe' => 'success',
            'data' => 'Transfer berhasil di-approve dan diproses sebagai pembelian kredit',
            'beli_id' => $result['beli_id'] ?? null,
        ];
    }

    public function rejectTransfer(string $tujuanTokoId, string $username, string $transferId): array
    {
        $transfer = $this->getTransferSummary($transferId, $tujuanTokoId, false);
        if (! $transfer) {
            return ['tipe' => 'error', 'data' => 'Transfer masuk tidak ditemukan'];
        }
        if (($transfer['status_transfer'] ?? '') !== 'KIRIM') {
            return ['tipe' => 'error', 'data' => 'Transfer ini tidak bisa direject'];
        }

        $cancelResult = $this->jualModel->cancelTransferSale(
            (string) ($transfer['gudang_toko_id'] ?? ''),
            $username,
            (string) ($transfer['jual_id'] ?? ''),
            'Reject transfer antar toko ' . $transferId
        );
        if (($cancelResult['tipe'] ?? '') !== 'success') {
            return $cancelResult;
        }

        $this->db->table('transfer_toko')
            ->where('transfer_id', $transferId)
            ->where('tujuan_toko_id', $tujuanTokoId)
            ->update([
                'status_transfer' => 'REJECTED',
                'tanggal_reject' => date('Y-m-d H:i:s'),
                'rejected_by' => $username,
            ]);

        return ['tipe' => 'success', 'data' => 'Transfer berhasil direject dan penjualan gudang dibatalkan'];
    }

    public function getTransferSummary(string $transferId, string $tokoId, bool $isGudang): ?array
    {
        $where = $isGudang
            ? 't.transfer_id=:transfer_id: AND t.gudang_toko_id=:toko_id:'
            : 't.transfer_id=:transfer_id: AND t.tujuan_toko_id=:toko_id:';

        $header = $this->db->query(
            "SELECT t.*,
                    tujuan.toko_nama AS tujuan_toko_nama,
                    gudang.toko_nama AS gudang_toko_nama
             FROM transfer_toko t
             LEFT JOIN toko tujuan ON tujuan.toko_id=t.tujuan_toko_id
             LEFT JOIN toko gudang ON gudang.toko_id=t.gudang_toko_id
             WHERE $where
             LIMIT 1",
            ['transfer_id' => $transferId, 'toko_id' => $tokoId]
        )->getRowArray();

        if (! $header) {
            return null;
        }

        $header['details'] = $this->db->query(
            "SELECT td.*, p.nama_item
             FROM transfer_toko_detail td
             LEFT JOIN prodmast p ON p.kode_item=td.kode_item
             WHERE td.transfer_id=:transfer_id:
             ORDER BY td.seq_no ASC",
            ['transfer_id' => $transferId]
        )->getResultArray();

        return $header;
    }

    public function searchItems(string $gudangTokoId, string $term): array
    {
        $search = '%' . $this->db->escapeLikeString($term) . '%';

        return $this->db->query(
            "SELECT p.kode_item, p.barcode, p.nama_item
             FROM prodmast p
             INNER JOIN prodmast_store ps ON ps.kode_item=p.kode_item AND ps.toko_id=:toko_id: AND ps.status_item='Y'
             WHERE p.kode_item LIKE :search:
                OR p.barcode LIKE :search:
                OR p.nama_item LIKE :search:
             GROUP BY p.kode_item, p.barcode, p.nama_item
             ORDER BY
                CASE
                    WHEN p.barcode = :exact_term: THEN 0
                    WHEN p.kode_item = :exact_term: THEN 1
                    ELSE 2
                END,
                p.nama_item
             LIMIT 30",
            [
                'toko_id' => $gudangTokoId,
                'search' => $search,
                'exact_term' => trim($term),
            ]
        )->getResultArray();
    }

    public function getItemPayload(string $gudangTokoId, string $kodeItem): ?array
    {
        $item = $this->db->query(
            "SELECT p.kode_item, p.barcode, p.nama_item, COALESCE(st.qty, 0) AS stok_base
             FROM prodmast p
             LEFT JOIN stmast st ON st.toko_id=:toko_id: AND st.kode_item=p.kode_item
             WHERE p.kode_item=:kode_item:
             LIMIT 1",
            ['toko_id' => $gudangTokoId, 'kode_item' => $kodeItem]
        )->getRowArray();

        if (! $item) {
            return null;
        }

        $options = $this->db->query(
            "SELECT ps.sat_id, ps.qty_konversi, COALESCE(store.harga_pokok, 0) AS harga_pokok,
                    CASE
                        WHEN COALESCE(ps.qty_konversi, 0) <= 0 THEN 0
                        ELSE COALESCE(st.qty, 0) / ps.qty_konversi
                    END AS stok_maksimal
             FROM prodmast_satuan ps
             INNER JOIN prodmast_store store
                ON store.kode_item=ps.kode_item
                AND store.sat_id=ps.sat_id
                AND store.toko_id=:toko_id:
                AND store.status_item='Y'
             LEFT JOIN stmast st
                ON st.toko_id=store.toko_id
                AND st.kode_item=ps.kode_item
             WHERE ps.kode_item=:kode_item:
             ORDER BY ps.qty_konversi, ps.sat_id",
            ['toko_id' => $gudangTokoId, 'kode_item' => $kodeItem]
        )->getResultArray();

        if (empty($options)) {
            return null;
        }

        foreach ($options as &$option) {
            $option['harga_jual_transfer'] = $this->calculateTransferPrice(
                (float) ($option['harga_pokok'] ?? 0),
                (string) ($option['sat_id'] ?? '')
            );
        }
        unset($option);

        $item['satuan'] = $options;
        return $item;
    }

    private function getMarkupGudang(): float
    {
        $row = $this->db->query("SELECT nilai FROM const WHERE rkey='markup_gudang' LIMIT 1")->getRowArray();
        return (float) ($row['nilai'] ?? 2);
    }

    private function getGramasiUnits(): array
    {
        $row = $this->db->query("SELECT nilai FROM const WHERE rkey='satuan_gramasi' LIMIT 1")->getRowArray();
        $raw = (string) ($row['nilai'] ?? 'GR;GRAM;ML');
        return array_map('strtoupper', array_filter(array_map('trim', explode(';', $raw))));
    }

    private function calculateTransferPrice(float $hargaPokok, string $satId): float
    {
        $markup = $this->getMarkupGudang();
        $hargaJual = round($hargaPokok + ($hargaPokok * $markup / 100), 2);
        if ($hargaJual <= 0) {
            return 0;
        }

        if (! in_array(strtoupper($satId), $this->getGramasiUnits(), true)) {
            return (float) (ceil($hargaJual / 50) * 50);
        }

        return $hargaJual;
    }

    private function syncBranchCustomer(): void
    {
        $this->db->query("
            REPLACE INTO customer(cust_id,nama,alamat,kontak,tgl_daftar,max_faktur,poin,updid)
            SELECT toko_id,
                   CONCAT('TOKO ', toko_nama),
                   COALESCE(toko_alamat, '-'),
                   LEFT(COALESCE(toko_phone, ''), 13),
                   CURDATE(),
                   999,
                   0,
                   'SYSTEM-TRANSFER'
            FROM toko
            WHERE flag_gudang!='Y'
        ");
    }
}
