<?= $this->extend('layouts/base') ?>

<?= $this->section('content') ?>
<?php
/**
 * @var array  $soAktif
 * @var array  $kategoriOptions
 */
$defaultTanggal = $tanggalAcuan ?? (!empty($soAktif['tanggal'] ?? '') ? $soAktif['tanggal'] : date('Y-m-d')); ?>
<div class="body-wrapper">
    <div class="container-fluid p-0">
        <div class="card bg-success-subtle shadow-none position-relative overflow-hidden mb-4">
            <div class="card-body px-4 py-3">
                <div class="row align-items-center">
                    <div class="col-lg-8">
                        <h4 class="fw-semibold mb-2">Hasil SO</h4>
                        <p class="mb-0"><span class="page-pretitle"><?= esc($defaultTanggal) ?></span> | Ringkasan dan detail selisih hasil stock opname.</p>
                    </div>
                    <div class="col-lg-4 text-lg-end mt-3 mt-lg-0">
                        <a href="<?= base_url('/so') ?>" class="btn btn-outline-secondary btn-sm">Kembali ke Menu SO</a>
                    </div>
                </div>
            </div>
        </div>

        <input type="hidden" id="tanggal" value="<?= esc($defaultTanggal) ?>">

        <div class="card mb-3">
            <div class="card-body">
                <div class="row g-3">
                    <div class="col-md-6 col-xl-4"><small class="text-muted">Periode</small>
                        <div class="fw-semibold" id="sum_periode">-</div>
                    </div>
                    <div class="col-md-6 col-xl-4"><small class="text-muted">Sudah Input</small>
                        <div class="fw-semibold" id="sum_sudah_input">0</div>
                    </div>
                    <div class="col-md-6 col-xl-4"><small class="text-muted">Belum Input</small>
                        <div class="fw-semibold" id="sum_belum_input">0</div>
                    </div>
                    <div class="col-md-6 col-xl-4"><small class="text-muted">Total NK Qty / Rp</small>
                        <div class="fw-semibold"><span id="sum_nk_qty">0</span> / Rp <span id="sum_nk_rp">0</span></div>
                    </div>
                    <div class="col-md-6 col-xl-4"><small class="text-muted">Total NL Qty / Rp</small>
                        <div class="fw-semibold"><span id="sum_nl_qty">0</span> / Rp <span id="sum_nl_rp">0</span></div>
                    </div>
                    <div class="col-md-6 col-xl-4"><small class="text-muted">Total NKL Qty / Rp</small>
                        <div class="fw-semibold"><span id="sum_nkl_qty">0</span> / Rp <span id="sum_nkl_rp">0</span></div>
                    </div>
                </div>
            </div>
        </div>

        <div class="card">
            <div class="card-body p-2">
                <table id="table-data" class="table table-bordered table-hover table-striped table-sm align-middle">
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
    function refreshSummary() {
        $.post('<?= base_url('/so/summary') ?>', {
            tanggal: $('#tanggal').val()
        }, function(res) {
            ['sum_periode', 'sum_sudah_input', 'sum_belum_input', 'sum_nk_qty', 'sum_nk_rp', 'sum_nl_qty', 'sum_nl_rp', 'sum_nkl_qty', 'sum_nkl_rp'].forEach((key) => {
                const value = res?.[key] ?? 0;
                if (key.includes('_rp') || key.includes('_qty')) {
                    $(`#${key}`).text(formatMoneyValue(value));
                } else {
                    $(`#${key}`).text(value);
                }
            });
        }, 'json');
    }

    DataTable.Buttons.defaults.dom.button.className = 'btn btn-primary';
    const table = $('#table-data').DataTable({
        layout: {
            topStart: {
                buttons: [{
                    text: '<i class="ti ti-file-type-xls"></i> Excel',
                    extend: 'excelHtml5',
                    title: 'Laporan-Hasil-SO',
                    exportOptions: {
                        columns: [0, 1, 2, 3, 4, 5, 6, 7]
                    }
                }, 'pageLength']
            }
        },
        lengthMenu: [
            [25, 50, 100, -1],
            ['25 rows', '50 rows', '100 rows', 'Show all']
        ],
        responsive: true,
        lengthChange: false,
        autoWidth: false,
        processing: true,
        serverSide: true,
        ordering: false,
        ajax: {
            url: '<?= base_url('/so/ajax-hasil') ?>',
            type: 'post',
            data: function(d) {
                d.tanggal = $('#tanggal').val();
            }
        },
        columns: [{
                data: 'kode_item',
                title: 'Kode Item'
            },
            {
                data: 'nama_item',
                title: 'Nama'
            },
            {
                data: 'sat_dasar',
                title: 'Satuan'
            },
            {
                data: 'status_input',
                title: 'Input',
                className: 'text-center'
            },
            {
                data: 'com',
                title: 'Stok',
                className: 'text-end',
                render: data => Number(data || 0).toLocaleString('id-ID', {
                    maximumFractionDigits: 2
                })
            },
            {
                data: 'ttl',
                title: 'Fisik',
                className: 'text-end',
                render: data => Number(data || 0).toLocaleString('id-ID', {
                    maximumFractionDigits: 2
                })
            },
            {
                data: 'selisih',
                title: 'Selisih',
                className: 'text-end',
                render: data => Number(data || 0).toLocaleString('id-ID', {
                    maximumFractionDigits: 2
                })
            },
            {
                data: 'selisih_rp',
                title: 'Selisih Rp',
                className: 'text-end',
                render: data => 'Rp ' + formatMoneyValue(data)
            }
        ]
    });

    $(function() {
        refreshSummary();
    });
</script>
<?= $this->endSection('javascript') ?>