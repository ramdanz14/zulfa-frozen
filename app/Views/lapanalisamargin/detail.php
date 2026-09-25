<?= $this->extend('layouts/base') ?>

<?= $this->section('content') ?>
<?php
/**
 * @var array $detailFilter
 */
$detailFilter = $detailFilter ?? [];
?>
<style>
    .metric-card-detail {
        border-radius: 12px;
        transition: transform 0.15s ease, box-shadow 0.15s ease;
    }

    .btn-touch-target {
        min-height: 44px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        font-weight: 500;
    }

    @media (max-width: 767.98px) {
        .metric-title {
            font-size: 0.78rem;
            line-height: 1.2;
        }

        .metric-value {
            font-size: 1.15rem !important;
            word-break: break-word;
        }
    }

    .item-margin-card {
        border: 1px solid rgba(0, 0, 0, 0.08);
        border-radius: 10px;
        transition: all 0.2s ease;
    }

    .item-margin-card .field-row {
        display: flex;
        justify-content: space-between;
        align-items: center;
        padding: 3px 0;
        font-size: 0.85rem;
    }

    .item-margin-card .field-row-total {
        border-top: 1px dashed rgba(0, 0, 0, 0.12);
        margin-top: 6px;
        padding-top: 6px;
        font-weight: 600;
    }
</style>

<div class="body-wrapper">
    <div class="container-fluid p-0">
        <!-- Header -->
        <div class="card bg-primary-subtle shadow-none position-relative overflow-hidden mb-3">
            <div class="card-body px-3 px-md-4 py-3">
                <div class="row align-items-center">
                    <div class="col-8 col-md-9">
                        <h4 class="fw-semibold mb-1">Detail Analisa Margin</h4>
                        <p class="mb-0 text-muted small"><span id="detail-period">Periode aktif</span> | Kategori <strong id="detail-kat-title" class="text-primary">-</strong></p>
                    </div>
                    <div class="col-4 col-md-3 text-end">
                        <a href="<?= base_url('/lapanalisamargin') ?>" class="btn btn-outline-primary btn-touch-target">
                            <i class="ti ti-arrow-left me-1"></i> Kembali
                        </a>
                    </div>
                </div>
            </div>
        </div>

        <!-- KPI Grid: 2x2 di Mobile, 4 kolom di Desktop -->
        <div class="row g-2 g-md-3 mb-3">
            <!-- 1. Sales Qty -->
            <div class="col-6 col-md-6 col-xl-3">
                <div class="card h-100 mb-0 border-secondary border-start border-3 metric-card-detail">
                    <div class="card-body p-3">
                        <div class="text-muted metric-title text-truncate">Sales Qty</div>
                        <div class="fs-6 fw-bold mt-1 text-dark metric-value" id="summary-qty">0</div>
                        <div class="text-muted small mt-1">Total unit terjual</div>
                    </div>
                </div>
            </div>
            <!-- 2. Jml Struk -->
            <div class="col-6 col-md-6 col-xl-3">
                <div class="card h-100 mb-0 border-info border-start border-3 metric-card-detail">
                    <div class="card-body p-3">
                        <div class="text-muted metric-title text-truncate">Jml Struk</div>
                        <div class="fs-6 fw-bold mt-1 text-info metric-value" id="summary-struk">0</div>
                        <div class="text-muted small mt-1">Frekuensi transaksi</div>
                    </div>
                </div>
            </div>
            <!-- 3. Sales Rp -->
            <div class="col-6 col-md-6 col-xl-3">
                <div class="card h-100 mb-0 border-primary border-start border-3 metric-card-detail">
                    <div class="card-body p-3">
                        <div class="text-muted metric-title text-truncate">Sales Rp</div>
                        <div class="fs-6 fw-bold mt-1 text-primary metric-value" id="summary-sales">Rp 0</div>
                        <div class="text-muted small mt-1">Total omset kotor</div>
                    </div>
                </div>
            </div>
            <!-- 4. Margin % -->
            <div class="col-6 col-md-6 col-xl-3">
                <div class="card h-100 mb-0 border-success border-start border-3 metric-card-detail">
                    <div class="card-body p-3">
                        <div class="text-muted metric-title text-truncate">Gross Margin %</div>
                        <div class="fs-6 fw-bold mt-1 text-success metric-value" id="summary-margin-percent">0%</div>
                        <div class="text-muted small mt-1">Rata-rata margin</div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Table / CardView Data -->
        <div class="card mb-3">
            <div class="card-body p-2 p-md-3">
                <table id="table-detail" class="table table-bordered table-hover table-striped table-sm align-middle w-100">
                    <thead></thead>
                    <tbody>
                        <tr>
                            <td>No data to show</td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<!-- Template CardView Mobile untuk Item Margin Detail -->
<template id="card-item-template">
    <div class="card item-margin-card shadow-sm mb-2 border-start border-3 border-success">
        <div class="card-body p-3">
            <div class="d-flex justify-content-between align-items-start mb-2 pb-2 border-bottom">
                <div style="flex: 1; min-width: 0;" class="pe-2">
                    <div class="badge bg-light text-dark font-monospace mb-1" data-dtcv-field="0"></div>
                    <div class="fw-bold text-dark text-truncate fs-5" data-dtcv-field="1"></div>
                    <div class="small text-muted" data-dtcv-field="2"></div>
                </div>
                <div class="badge bg-success-subtle text-success fs-6 fw-bold shrink-0" data-dtcv-field="8"></div>
            </div>
            <div class="field-row">
                <span class="text-muted">Sales Qty</span>
                <span class="fw-medium" data-dtcv-field="3"></span>
            </div>
            <div class="field-row">
                <span class="text-muted">Jml Struk</span>
                <span class="fw-medium" data-dtcv-field="4"></span>
            </div>
            <div class="field-row">
                <span class="text-muted">Sales Rp</span>
                <span class="fw-bold" data-dtcv-field="5"></span>
            </div>
            <div class="field-row">
                <span class="text-muted">Sales HPP</span>
                <span class="fw-medium text-danger" data-dtcv-field="6"></span>
            </div>
            <div class="field-row field-row-total">
                <span class="text-dark">Total Margin</span>
                <span class="fw-bold text-success fs-6" data-dtcv-field="7"></span>
            </div>
        </div>
    </div>
</template>

<?= $this->endSection('content') ?>

<?= $this->section('javascript') ?>
<script>
    const initialFilter = <?= json_encode($detailFilter, JSON_UNESCAPED_SLASHES) ?>;
    const katId = String(initialFilter.kat_id || '');
    const dateStart = initialFilter.date_start || moment().startOf('month').format('YYYY-MM-DD');
    const dateEnd = initialFilter.date_end || moment().endOf('month').format('YYYY-MM-DD');
    const tokoIds = Array.isArray(initialFilter.toko_ids) ? initialFilter.toko_ids : (initialFilter.toko_ids ? [initialFilter.toko_ids] : []);

    $('#detail-kat-title').text(katId || '-');
    $('#detail-period').text(`${moment(dateStart, 'YYYY-MM-DD').format('DD/MM/YYYY')} - ${moment(dateEnd, 'YYYY-MM-DD').format('DD/MM/YYYY')}`);

    DataTable.Buttons.defaults.dom.button.className = 'btn btn-primary';
    const detailTable = $('#table-detail').DataTable({
        layout: {
            topStart: {
                buttons: [{
                    text: '<i class="ti ti-file-type-xls"></i> Excel',
                    extend: 'excelHtml5',
                    title: `Detail-Margin-${katId || 'Kategori'}`,
                    exportOptions: {
                        columns: ':visible'
                    }
                }, 'cardViewToggle', 'pageLength']
            }
        },
        cardView: {
            enable: true,
            breakpoint: 768,
            columns: {
                xs: 1,
                sm: 1,
                md: 2,
                lg: 3
            },
            template: '#card-item-template'
        },
        data: [],
        lengthMenu: [
            [25, 50, 100, -1],
            ['25 rows', '50 rows', '100 rows', 'Show all']
        ],
        pageLength: 25,
        responsive: true,
        autoWidth: false,
        order: [
            [8, 'asc']
        ],
        columns: [{
                data: 'kode_item',
                title: 'Kode Item'
            },
            {
                data: 'nama_item',
                title: 'Nama Item'
            },
            {
                data: 'daftar_toko',
                title: 'Toko',
                render: data => data || '-'
            },
            {
                data: 'total_qty',
                title: 'Sales Qty',
                className: 'text-end',
                render: renderQty
            },
            {
                data: 'jumlah_transaksi',
                title: 'Jml Struk',
                className: 'text-end',
                render: function(data, type) {
                    if (type !== 'display') {
                        return Number(data || 0);
                    }
                    return Number(data || 0).toLocaleString('id-ID');
                }
            },
            {
                data: 'total_gross_sales',
                title: 'Sales Rp',
                className: 'text-end',
                render: renderMoney
            },
            {
                data: 'total_sales_hpp',
                title: 'Sales HPP',
                className: 'text-end',
                render: renderMoney
            },
            {
                data: 'total_margin',
                title: 'Margin',
                className: 'text-end',
                render: renderMoney
            },
            {
                data: 'gross_margin_percent',
                title: 'Margin %',
                className: 'text-end',
                render: renderPercent
            }
        ]
    });

    loadDetail();

    function loadDetail() {
        $.ajax({
            type: 'POST',
            url: '<?= base_url('/lapanalisamargin/detail') ?>',
            dataType: 'json',
            data: {
                kat_id: katId,
                date_start: dateStart,
                date_end: dateEnd,
                toko_ids: tokoIds
            },
            success: function(res) {
                const rows = res?.data || [];
                detailTable.clear().rows.add(rows).draw();
                refreshSummary(rows);
            },
            error: function(xhr) {
                toastr.error(extractErrorMessage(xhr, 'Gagal memuat detail kategori'));
            }
        });
    }

    function refreshSummary(rows) {
        const summary = rows.reduce((acc, row) => {
            acc.qty += Number(row.total_qty || 0);
            acc.struk += Number(row.jumlah_transaksi || 0);
            acc.sales += Number(row.total_gross_sales || 0);
            acc.hpp += Number(row.total_sales_hpp || 0);
            acc.margin += Number(row.total_margin || 0);
            return acc;
        }, {
            qty: 0,
            struk: 0,
            sales: 0,
            hpp: 0,
            margin: 0
        });
        const marginPercent = summary.sales === 0 ? 0 : (summary.margin / summary.sales) * 100;

        $('#summary-qty').text(formatQty(summary.qty));
        $('#summary-struk').text(summary.struk.toLocaleString('id-ID'));
        $('#summary-sales').text(`Rp ${formatMoneyValue(summary.sales)}`);
        $('#summary-margin-percent').text(formatPercent(marginPercent));
    }

    function renderMoney(data, type) {
        if (type !== 'display') {
            return Number(data || 0);
        }
        return 'Rp ' + formatMoneyValue(data);
    }

    function renderQty(data, type) {
        if (type !== 'display') {
            return Number(data || 0);
        }
        return formatQty(data);
    }

    function renderPercent(data, type) {
        if (type !== 'display') {
            return Number(data || 0);
        }
        return formatPercent(data);
    }

    function formatQty(value) {
        return Number(value || 0).toLocaleString('id-ID', {
            minimumFractionDigits: 0,
            maximumFractionDigits: 2
        });
    }

    function formatPercent(value) {
        return `${Number(value || 0).toLocaleString('id-ID', {
            minimumFractionDigits: 2,
            maximumFractionDigits: 2
        })}%`;
    }
</script>
<?= $this->endSection('javascript') ?>