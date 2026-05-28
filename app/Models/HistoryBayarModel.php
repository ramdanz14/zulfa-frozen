<?php

namespace App\Models;

use CodeIgniter\Model;

class HistoryBayarModel extends Model
{
    protected $table = 'pembelian_pembayaran';
    protected $primaryKey = 'bayar_id';
    protected $returnType = 'array';
    protected $protectFields = false;

    public function getSupplierOptions(string $toko_id): array
    {
        return $this->db->query(
            "SELECT DISTINCT p.supco, s.nama
             FROM pembelian_pembayaran pp
             INNER JOIN pembelian p ON p.toko_id=pp.toko_id AND p.beli_id=pp.beli_id
             LEFT JOIN supmast s ON s.supco=p.supco
             WHERE pp.toko_id=:toko_id:
             ORDER BY s.nama, p.supco",
            ['toko_id' => $toko_id]
        )->getResultArray();
    }

    public function ajax(array $params, string $toko_id): array
    {
        $start = (int) ($params['start'] ?? 0);
        $length = $params['length'] ?? 25;
        $search = trim((string) ($params['search_value'] ?? ''));
        $supco = trim((string) ($params['supco'] ?? ''));
        $dateStart = trim((string) ($params['date_start'] ?? ''));
        $dateEnd = trim((string) ($params['date_end'] ?? ''));
        $queryLimit = $length !== '-1' ? " LIMIT $start, " . (int) $length : '';

        $binds = ['toko_id' => $toko_id];
        $where = " WHERE pp.toko_id = :toko_id: ";

        if ($supco !== '') {
            $where .= " AND p.supco = :supco: ";
            $binds['supco'] = $supco;
        }
        if ($dateStart !== '' && $dateEnd !== '') {
            $where .= " AND DATE(pp.tanggal_bayar) BETWEEN :date_start: AND :date_end: ";
            $binds['date_start'] = $dateStart;
            $binds['date_end'] = $dateEnd;
        }
        if ($search !== '') {
            $where .= " AND (p.beli_id LIKE :search: OR p.invoice LIKE :search: OR p.supco LIKE :search: OR s.nama LIKE :search: OR pp.cara_bayar LIKE :search: OR pp.bank_nama LIKE :search: OR pp.rekening_no LIKE :search:)";
            $binds['search'] = '%' . $this->db->escapeLikeString($search) . '%';
        }

        $totalRow = $this->db->query(
            "SELECT COUNT(*) total FROM pembelian_pembayaran WHERE toko_id=:toko_id:",
            ['toko_id' => $toko_id]
        )->getRowArray();
        $filtered = $totalRow['total'] ?? 0;
        if ($supco !== '' || ($dateStart !== '' && $dateEnd !== '') || $search !== '') {
            $filteredRow = $this->db->query(
                "SELECT COUNT(*) total
                 FROM pembelian_pembayaran pp
                 INNER JOIN pembelian p ON p.toko_id=pp.toko_id AND p.beli_id=pp.beli_id
                 LEFT JOIN supmast s ON s.supco=p.supco
                 $where",
                $binds
            )->getRowArray();
            $filtered = $filteredRow['total'] ?? 0;
        }

        $closingDate = $this->getClosingDate($toko_id);
        $data = $this->db->query(
            "SELECT pp.*, p.tanggal, p.supco, p.invoice, p.status_nota, p.status_bayar, p.total_gross, p.total_bayar, p.sisa_bayar,
                    s.nama AS supplier_nama
             FROM pembelian_pembayaran pp
             INNER JOIN pembelian p ON p.toko_id=pp.toko_id AND p.beli_id=pp.beli_id
             LEFT JOIN supmast s ON s.supco=p.supco
             $where
             ORDER BY pp.tanggal_bayar DESC, pp.bayar_id DESC
             $queryLimit",
            $binds
        )->getResultArray();

        foreach ($data as &$row) {
            $row['can_modify'] = substr((string) $row['tanggal_bayar'], 0, 10) >= $closingDate;
            $row['closing_date'] = $closingDate;
        }

        return [
            'data' => $data,
            'total_count' => (int) ($totalRow['total'] ?? 0),
            'total_filtered' => (int) $filtered,
        ];
    }

    public function updatePayment(string $toko_id, int $bayar_id, array $input): array
    {
        $payment = $this->getPayment($toko_id, $bayar_id);
        if (!$payment) {
            return ['tipe' => 'error', 'data' => 'Data pembayaran tidak ditemukan'];
        }
        if (!$this->canModifyByClosing($toko_id, (string) $payment['tanggal_bayar'])) {
            return ['tipe' => 'error', 'data' => 'Pembayaran yang sudah melewati periode closing tidak boleh diedit'];
        }

        $caraBayar = strtoupper(trim((string) ($input['cara_bayar'] ?? '')));
        $tanggalBayar = trim((string) ($input['tanggal_bayar'] ?? ''));
        $jumlahBayar = (float) ($input['jumlah_bayar'] ?? 0);
        $bankNama = trim((string) ($input['bank_nama'] ?? ''));
        $rekeningNo = trim((string) ($input['rekening_no'] ?? ''));

        if (!in_array($caraBayar, ['TUNAI', 'TRANSFER'], true) || $tanggalBayar === '' || $jumlahBayar <= 0) {
            return ['tipe' => 'error', 'data' => 'Data pembayaran tidak valid'];
        }
        if ($caraBayar === 'TRANSFER' && ($bankNama === '' || $rekeningNo === '')) {
            return ['tipe' => 'error', 'data' => 'Transfer wajib isi nama bank dan no rekening'];
        }
        if (!$this->canModifyByClosing($toko_id, $tanggalBayar)) {
            return ['tipe' => 'error', 'data' => 'Tanggal pembayaran baru sudah melewati periode closing'];
        }

        $header = $this->getHeaderByPayment($toko_id, $bayar_id);
        if (!$header) {
            return ['tipe' => 'error', 'data' => 'Header pembelian tidak ditemukan'];
        }

        $otherPaidRow = $this->db->query(
            "SELECT COALESCE(SUM(jumlah_bayar),0) AS total
             FROM pembelian_pembayaran
             WHERE toko_id=:toko_id: AND beli_id=:beli_id: AND bayar_id <> :bayar_id:",
            ['toko_id' => $toko_id, 'beli_id' => $header['beli_id'], 'bayar_id' => $bayar_id]
        )->getRowArray();
        $otherPaid = (float) ($otherPaidRow['total'] ?? 0);
        if (($otherPaid + $jumlahBayar) - (float) $header['total_gross'] > 0.0001) {
            return ['tipe' => 'error', 'data' => 'Nominal pembayaran melebihi total gross pembelian'];
        }

        $this->db->transStart();
        $this->db->table('pembelian_pembayaran')
            ->where('toko_id', $toko_id)
            ->where('bayar_id', $bayar_id)
            ->update([
                'tanggal_bayar' => $tanggalBayar,
                'cara_bayar' => $caraBayar,
                'jumlah_bayar' => round($jumlahBayar, 2),
                'bank_nama' => $caraBayar === 'TRANSFER' ? $bankNama : null,
                'rekening_no' => $caraBayar === 'TRANSFER' ? $rekeningNo : null,
            ]);
        $this->syncPurchasePaymentSummary($toko_id, $header['beli_id']);
        $this->db->transComplete();

        if (!$this->db->transStatus()) {
            return ['tipe' => 'error', 'data' => 'Gagal update pembayaran'];
        }

        return ['tipe' => 'success', 'data' => 'Pembayaran berhasil diupdate'];
    }

    public function deletePayment(string $toko_id, int $bayar_id): array
    {
        $payment = $this->getPayment($toko_id, $bayar_id);
        if (!$payment) {
            return ['tipe' => 'error', 'data' => 'Data pembayaran tidak ditemukan'];
        }
        if (!$this->canModifyByClosing($toko_id, (string) $payment['tanggal_bayar'])) {
            return ['tipe' => 'error', 'data' => 'Pembayaran yang sudah melewati periode closing tidak boleh dihapus'];
        }

        $this->db->transStart();
        $this->db->table('pembelian_pembayaran')
            ->where('toko_id', $toko_id)
            ->where('bayar_id', $bayar_id)
            ->delete();
        $this->syncPurchasePaymentSummary($toko_id, (string) $payment['beli_id']);
        $this->db->transComplete();

        if (!$this->db->transStatus()) {
            return ['tipe' => 'error', 'data' => 'Gagal hapus pembayaran'];
        }

        return ['tipe' => 'success', 'data' => 'Pembayaran berhasil dihapus'];
    }

    public function getPayment(string $toko_id, int $bayar_id): ?array
    {
        $row = $this->db->query(
            "SELECT pp.*, p.supco, p.invoice, s.nama AS supplier_nama
             FROM pembelian_pembayaran pp
             INNER JOIN pembelian p ON p.toko_id=pp.toko_id AND p.beli_id=pp.beli_id
             LEFT JOIN supmast s ON s.supco=p.supco
             WHERE pp.toko_id=:toko_id: AND pp.bayar_id=:bayar_id:",
            ['toko_id' => $toko_id, 'bayar_id' => $bayar_id]
        )->getRowArray();

        return $row ?: null;
    }

    public function getClosingDate(string $toko_id): string
    {
        $row = $this->db->query(
            "SELECT nilai FROM const WHERE rkey=:rkey:",
            ['rkey' => 'closing-' . $toko_id]
        )->getRowArray();
        return $row['nilai'] ?? date('Y-m-01');
    }

    private function getHeaderByPayment(string $toko_id, int $bayar_id): ?array
    {
        $row = $this->db->query(
            "SELECT p.*
             FROM pembelian p
             INNER JOIN pembelian_pembayaran pp ON pp.toko_id=p.toko_id AND pp.beli_id=p.beli_id
             WHERE pp.toko_id=:toko_id: AND pp.bayar_id=:bayar_id:",
            ['toko_id' => $toko_id, 'bayar_id' => $bayar_id]
        )->getRowArray();

        return $row ?: null;
    }

    private function canModifyByClosing(string $toko_id, string $tanggalBayar): bool
    {
        return substr($tanggalBayar, 0, 10) >= $this->getClosingDate($toko_id);
    }

    private function syncPurchasePaymentSummary(string $toko_id, string $beli_id): void
    {
        $header = $this->db->query(
            "SELECT total_gross, status_nota
             FROM pembelian
             WHERE toko_id=:toko_id: AND beli_id=:beli_id:",
            ['toko_id' => $toko_id, 'beli_id' => $beli_id]
        )->getRowArray();

        if (!$header) {
            return;
        }

        $payRow = $this->db->query(
            "SELECT COALESCE(SUM(jumlah_bayar),0) AS total_bayar
             FROM pembelian_pembayaran
             WHERE toko_id=:toko_id: AND beli_id=:beli_id:",
            ['toko_id' => $toko_id, 'beli_id' => $beli_id]
        )->getRowArray();

        $totalGross = (float) ($header['total_gross'] ?? 0);
        $totalBayar = (float) ($payRow['total_bayar'] ?? 0);
        $sisaBayar = max($totalGross - $totalBayar, 0);
        $statusNota = $header['status_nota'] ?? 'PO';
        $isKredit = 0;

        if ($statusNota === 'PO') {
            $statusBayar = 'BELUM';
        } elseif ($sisaBayar <= 0.0001) {
            $statusBayar = 'LUNAS';
        } elseif ($totalBayar > 0) {
            $statusBayar = 'CICIL';
            $isKredit = 1;
        } else {
            $statusBayar = 'BELUM';
            $isKredit = 1;
        }

        $this->db->table('pembelian')
            ->where('toko_id', $toko_id)
            ->where('beli_id', $beli_id)
            ->update([
                'total_bayar' => round($totalBayar, 2),
                'sisa_bayar' => round($sisaBayar, 2),
                'status_bayar' => $statusBayar,
                'is_kredit' => $isKredit,
            ]);
    }
}
