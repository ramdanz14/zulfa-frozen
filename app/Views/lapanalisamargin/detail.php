<?= $this->extend('layouts/base') ?>

<?= $this->section('content') ?>
<?php
/**
 * @var array $detailFilter
 */
$detailFilter = $detailFilter ?? [];
?>
<div class="body-wrapper">
    <div class="container-fluid p-0">
        <div class="card bg-primary-subtle shadow-none position-relative overflow-hidden mb-4">
            <div class="card-body px-4 py-3">
                <div class="row align-items-center">
                    <div class="col-lg-8">
                        <h4 class="fw-semibold mb-2">Detail Analisa Margin</h4>
                        <p class="mb-0"><span id="detail-period">Periode aktif</span> | Kategori <span id="detail-kat-title">-</span></p>
                    </div>
                    <div class="col-lg-4 text-lg-end mt-3 mt-lg-0">
                        <a href="<?= base_url('/lapanalisamargin') ?>" class="btn btn-light">Kembali</a>
                    </div>
                </div>
            </div>
        </div>

        <div class="row g-3 mb-3">
            <div class="col-md-6 col-xl-3">
                <div class="card h-100 mb-0">
                    <div class="card-body">
                        <div class="text-muted small">Sales Qty</div>
                        <div class="fs-6 fw-semibold mt-2" id="summary-qty">0</div>
                    </div>
                </div>
            </div>
            <div class="col-md-6 col-xl-3">
                <div class="card h-100 mb-0">
                    <div class="card-body">
                        <div class="text-muted small">Jml Struk</div>
                        <div class="fs-6 fw-semibold mt-2" id="summary-struk">0</div>
                    </div>
                </div>
            </div>
            <div class="col-md-6 col-xl-3">
                <div class="card h-100 mb-0">
                    <div class="card-body">
                        <div class="text-muted small">Sales Rp</div>
                        <div class="fs-6 fw-semibold mt-2" id="summary-sales">Rp 0</div>
                    </div>
                </div>
            </div>
            <div class="col-md-6 col-xl-3">
                <div class="card h-100 mb-0">
                    <div class="card-body">
                        <div class="text-muted small">Margin %</div>
                        <div class="fs-6 fw-semibold mt-2" id="summary-margin-percent">0%</div>
                    </div>
                </div>
            </div>
        </div>

        <div class="card">
            <div class="card-body p-2">
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
                }, 'pageLength']
            }
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
