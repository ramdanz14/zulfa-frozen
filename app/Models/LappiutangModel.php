<?php

namespace App\Models;

use CodeIgniter\Model;

class LappiutangModel extends Model
{
    protected $returnType = 'array';
    protected $protectFields = false;

    public function getReport(array $params, string $sessionTokoId, bool $allowMultiStore): array
    {
        $dateStart = $this->validDate((string) ($params['date_start'] ?? ''), date('Y-m-01'));
        $dateEnd = $this->validDate((string) ($params['date_end'] ?? ''), date('Y-m-t'));
        if ($dateEnd < $dateStart) {
            [$dateStart, $dateEnd] = [$dateEnd, $dateStart];
        }

        $tokoId = $this->resolveStoreId($params['toko_id'] ?? '', $sessionTokoId, $allowMultiStore);
        $rows = $this->queryRows($dateStart, $dateEnd, $tokoId);

        return [
            'date_start' => $dateStart,
            'date_end' => $dateEnd,
            'toko' => $this->getStore($tokoId),
            'summary' => $this->buildSummary($rows),
            'rows' => $rows,
        ];
    }

    private function queryRows(string $dateStart, string $dateEnd, string $tokoId): array
    {
        $rows = $this->db->query(
            "SELECT j.cust_id,
                    COALESCE(c.nama, j.cust_id) AS customer_nama,
                    COALESCE(c.kontak, '') AS customer_kontak,
                    COUNT(*) AS total_invoice,
                    COALESCE(SUM(j.netto),0) AS total_nominal,
                    COALESCE(SUM(j.netto - j.sisa_piutang),0) AS total_bayar,
                    COALESCE(SUM(j.sisa_piutang),0) AS sisa_piutang,
                    SUM(CASE WHEN j.status_bayar='BELUM' THEN 1 ELSE 0 END) AS invoice_belum,
                    COALESCE(SUM(CASE WHEN j.status_bayar='BELUM' THEN j.netto ELSE 0 END),0) AS nominal_belum,
                    COALESCE(SUM(CASE WHEN j.status_bayar='BELUM' THEN j.sisa_piutang ELSE 0 END),0) AS sisa_belum,
                    SUM(CASE WHEN j.status_bayar='CICIL' THEN 1 ELSE 0 END) AS invoice_cicil,
                    COALESCE(SUM(CASE WHEN j.status_bayar='CICIL' THEN j.netto ELSE 0 END),0) AS nominal_cicil,
                    COALESCE(SUM(CASE WHEN j.status_bayar='CICIL' THEN j.sisa_piutang ELSE 0 END),0) AS sisa_cicil,
                    SUM(CASE WHEN j.status_bayar='LUNAS' THEN 1 ELSE 0 END) AS invoice_lunas,
                    COALESCE(SUM(CASE WHEN j.status_bayar='LUNAS' THEN j.netto ELSE 0 END),0) AS nominal_lunas,
                    ROUND(AVG(CASE
                        WHEN j.status_bayar='LUNAS' AND paid.last_paid_at IS NOT NULL
                        THEN DATEDIFF(DATE(paid.last_paid_at), DATE(j.tgl))
                        ELSE NULL
                    END), 1) AS avg_durasi_lunas_hari
             FROM penjualan j
             LEFT JOIN customer c ON c.cust_id=j.cust_id
             LEFT JOIN (
                SELECT toko_id, jual_id, MAX(tgl_bayar) AS last_paid_at
                FROM penjualan_pembayaran
                GROUP BY toko_id, jual_id
             ) paid ON paid.toko_id=j.toko_id AND paid.jual_id=j.jual_id
             WHERE j.toko_id=:toko_id:
               AND DATE(j.tgl) BETWEEN :date_start: AND :date_end:
               AND j.is_kredit='1'
             GROUP BY j.cust_id, COALESCE(c.nama, j.cust_id), COALESCE(c.kontak, '')
             ORDER BY c.nama, j.cust_id",
            [
                'toko_id' => $tokoId,
                'date_start' => $dateStart,
                'date_end' => $dateEnd,
            ]
        )->getResultArray();

        foreach ($rows as &$row) {
            foreach ([
                'total_invoice',
                'invoice_belum',
                'invoice_cicil',
                'invoice_lunas',
            ] as $key) {
                $row[$key] = (int) ($row[$key] ?? 0);
            }
            foreach ([
                'total_nominal',
                'total_bayar',
                'sisa_piutang',
                'nominal_belum',
                'sisa_belum',
                'nominal_cicil',
                'sisa_cicil',
                'nominal_lunas',
                'avg_durasi_lunas_hari',
            ] as $key) {
                $row[$key] = (float) ($row[$key] ?? 0);
            }
        }

        return $rows;
    }

    private function buildSummary(array $rows): array
    {
        $summary = [
            'customer_count' => count($rows),
            'total_invoice' => 0,
            'total_nominal' => 0.0,
            'total_bayar' => 0.0,
            'sisa_piutang' => 0.0,
            'invoice_belum' => 0,
            'nominal_belum' => 0.0,
            'invoice_cicil' => 0,
            'nominal_cicil' => 0.0,
            'invoice_lunas' => 0,
            'nominal_lunas' => 0.0,
            'avg_durasi_lunas_hari' => 0.0,
        ];
        $durationTotal = 0.0;
        $durationWeight = 0;

        foreach ($rows as $row) {
            $summary['total_invoice'] += (int) ($row['total_invoice'] ?? 0);
            $summary['total_nominal'] += (float) ($row['total_nominal'] ?? 0);
            $summary['total_bayar'] += (float) ($row['total_bayar'] ?? 0);
            $summary['sisa_piutang'] += (float) ($row['sisa_piutang'] ?? 0);
            $summary['invoice_belum'] += (int) ($row['invoice_belum'] ?? 0);
            $summary['nominal_belum'] += (float) ($row['nominal_belum'] ?? 0);
            $summary['invoice_cicil'] += (int) ($row['invoice_cicil'] ?? 0);
            $summary['nominal_cicil'] += (float) ($row['nominal_cicil'] ?? 0);
            $summary['invoice_lunas'] += (int) ($row['invoice_lunas'] ?? 0);
            $summary['nominal_lunas'] += (float) ($row['nominal_lunas'] ?? 0);

            $paidInvoices = (int) ($row['invoice_lunas'] ?? 0);
            if ($paidInvoices > 0 && (float) ($row['avg_durasi_lunas_hari'] ?? 0) > 0) {
                $durationTotal += (float) $row['avg_durasi_lunas_hari'] * $paidInvoices;
                $durationWeight += $paidInvoices;
            }
        }

        if ($durationWeight > 0) {
            $summary['avg_durasi_lunas_hari'] = round($durationTotal / $durationWeight, 1);
        }

        return $summary;
    }

    private function getStore(string $tokoId): array
    {
        $row = $this->db->query(
            "SELECT toko_id, toko_nama FROM toko WHERE toko_id=:toko_id: LIMIT 1",
            ['toko_id' => $tokoId]
        )->getRowArray();

        return $row ?: ['toko_id' => $tokoId, 'toko_nama' => $tokoId];
    }

    private function resolveStoreId($rawTokoId, string $sessionTokoId, bool $allowMultiStore): string
    {
        if (!$allowMultiStore) {
            return $sessionTokoId;
        }

        $tokoId = trim((string) $rawTokoId);
        return $tokoId !== '' ? $tokoId : $sessionTokoId;
    }

    private function validDate(string $date, string $fallback): string
    {
        $timestamp = strtotime($date);
        return $timestamp ? date('Y-m-d', $timestamp) : $fallback;
    }
}
