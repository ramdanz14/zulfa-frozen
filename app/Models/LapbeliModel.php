<?php

namespace App\Models;

use CodeIgniter\Model;

class LapbeliModel extends Model
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
        $binds = [
            'toko_id' => $tokoId,
            'date_start' => $dateStart,
            'date_end' => $dateEnd,
        ];

        $rows = $this->db->query(
            "SELECT h.supco,
                    COALESCE(s.nama, h.supco) AS supplier_nama,
                    h.total_invoice,
                    h.total_nominal,
                    h.invoice_kredit,
                    h.nominal_kredit,
                    h.invoice_non_kredit,
                    h.nominal_non_kredit,
                    COALESCE(i.total_jenis_item,0) AS total_jenis_item,
                    h.total_frekuensi_datang,
                    h.kiriman_pertama,
                    h.kiriman_terakhir,
                    CASE
                        WHEN h.total_frekuensi_datang > 1
                        THEN ROUND(DATEDIFF(h.kiriman_terakhir, h.kiriman_pertama) / (h.total_frekuensi_datang - 1))
                        ELSE 0
                    END AS rata_rata_jarak_kirim_hari
             FROM (
                SELECT p.supco,
                        COUNT(*) AS total_invoice,
                        COALESCE(SUM(p.total_gross),0) AS total_nominal,
                        SUM(CASE WHEN p.is_kredit=1 THEN 1 ELSE 0 END) AS invoice_kredit,
                        COALESCE(SUM(CASE WHEN p.is_kredit=1 THEN p.total_gross ELSE 0 END),0) AS nominal_kredit,
                        SUM(CASE WHEN p.is_kredit=1 THEN 0 ELSE 1 END) AS invoice_non_kredit,
                        COALESCE(SUM(CASE WHEN p.is_kredit=1 THEN 0 ELSE p.total_gross END),0) AS nominal_non_kredit,
                        COUNT(DISTINCT p.tanggal) AS total_frekuensi_datang,
                        MIN(p.tanggal) AS kiriman_pertama,
                        MAX(p.tanggal) AS kiriman_terakhir
                 FROM pembelian p
                 WHERE p.toko_id=:toko_id:
                   AND p.tanggal BETWEEN :date_start: AND :date_end:
                   AND p.status_nota='TERIMA'
                 GROUP BY p.supco
             ) h
             LEFT JOIN supmast s ON s.supco=h.supco
             LEFT JOIN (
                SELECT p.supco, COUNT(DISTINCT pd.kode_item) AS total_jenis_item
                FROM pembelian p
                INNER JOIN pembelian_detail pd ON pd.toko_id=p.toko_id AND pd.beli_id=p.beli_id
                WHERE p.toko_id=:toko_id:
                  AND p.tanggal BETWEEN :date_start: AND :date_end:
                  AND p.status_nota='TERIMA'
                GROUP BY p.supco
             ) i ON i.supco=h.supco
             ORDER BY s.nama, h.supco",
            $binds
        )->getResultArray();

        foreach ($rows as &$row) {
            foreach ([
                'total_invoice',
                'invoice_kredit',
                'invoice_non_kredit',
                'total_jenis_item',
                'total_frekuensi_datang',
                'rata_rata_jarak_kirim_hari',
            ] as $key) {
                $row[$key] = (int) ($row[$key] ?? 0);
            }
            foreach ([
                'total_nominal',
                'nominal_kredit',
                'nominal_non_kredit',
            ] as $key) {
                $row[$key] = (float) ($row[$key] ?? 0);
            }
        }

        return $rows;
    }

    private function buildSummary(array $rows): array
    {
        $summary = [
            'supplier_count' => count($rows),
            'total_invoice' => 0,
            'total_nominal' => 0.0,
            'invoice_kredit' => 0,
            'nominal_kredit' => 0.0,
            'invoice_non_kredit' => 0,
            'nominal_non_kredit' => 0.0,
            'total_jenis_item' => 0,
            'total_frekuensi_datang' => 0,
            'rata_rata_jarak_kirim_hari' => 0.0,
        ];
        $frequencyTotal = 0;
        $distanceTotal = 0.0;

        foreach ($rows as $row) {
            $summary['total_invoice'] += (int) ($row['total_invoice'] ?? 0);
            $summary['total_nominal'] += (float) ($row['total_nominal'] ?? 0);
            $summary['invoice_kredit'] += (int) ($row['invoice_kredit'] ?? 0);
            $summary['nominal_kredit'] += (float) ($row['nominal_kredit'] ?? 0);
            $summary['invoice_non_kredit'] += (int) ($row['invoice_non_kredit'] ?? 0);
            $summary['nominal_non_kredit'] += (float) ($row['nominal_non_kredit'] ?? 0);
            $summary['total_jenis_item'] += (int) ($row['total_jenis_item'] ?? 0);
            $summary['total_frekuensi_datang'] += (int) ($row['total_frekuensi_datang'] ?? 0);

            if ((int) ($row['total_frekuensi_datang'] ?? 0) > 1) {
                $frequencyTotal++;
                $distanceTotal += (float) ($row['rata_rata_jarak_kirim_hari'] ?? 0);
            }
        }

        if ($frequencyTotal > 0) {
            $summary['rata_rata_jarak_kirim_hari'] = round($distanceTotal / $frequencyTotal, 1);
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
