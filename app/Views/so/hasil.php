<?= $this->extend('layouts/base') ?>

<?= $this->section('content') ?>
<?php
/**
 * @var array  $soAktif
 * @var array  $kategoriOptions
 */
$defaultTanggal = $tanggalAcuan ?? (!empty($soAktif['tanggal'] ?? '') ? $soAktif['tanggal'] : date('Y-m-d')); ?>
<style>
    /* Styling responsif mobile untuk Hasil SO & CardView */
    .so-hasil-card {
        background: #ffffff;
        border: 1px solid #e2e8f0;
        border-radius: 10px;
        padding: 0.85rem 1rem;
        transition: transform 0.15s ease, box-shadow 0.15s ease;
    }
    .so-hasil-card:hover {
        border-color: #cbd5e1;
        box-shadow: 0 4px 12px rgba(0,0,0,0.05);
    }
    .so-item-code-badge {
        font-family: var(--bs-font-monospace);
        font-size: 0.75rem;
        background-color: var(--bs-tertiary-bg);
        color: var(--bs-secondary-color);
        padding: 0.15rem 0.45rem;
        border-radius: 0.25rem;
        display: inline-flex;
        align-items: center;
        gap: 0.25rem;
        border: 1px solid var(--bs-border-color);
    }
    .so-card-title {
        font-weight: 700;
        color: var(--bs-heading-color);
        line-height: 1.3;
        font-size: 0.95rem;
    }
    .so-kpi-tile {
        background-color: #ffffff;
        border: 1px solid #e2e8f0;
        border-radius: 8px;
        padding: 0.65rem 0.85rem;
    }
</style>

<div class="body-wrapper">
    <div class="container-fluid p-0">
        <div class="card bg-success-subtle shadow-none position-relative overflow-hidden mb-3">
            <div class="card-body px-4 py-3">
                <div class="row align-items-center">
                    <div class="col-12 col-lg-8">
                        <h4 class="fw-semibold mb-1">Hasil Stock Opname</h4>
                        <p class="mb-0 text-muted small"><span class="page-pretitle fw-semibold text-dark"><?= esc($defaultTanggal) ?></span> | Analisis selisih stok, evaluasi finansial NK/NL, dan rekapitulasi.</p>
                    </div>
                    <div class="col-12 col-lg-4 text-start text-lg-end mt-2 mt-lg-0">
                        <a href="<?= base_url('/opname') ?>" class="btn btn-outline-secondary btn-sm px-3"><i class="ti ti-arrow-left me-1"></i> Kembali ke Menu SO</a>
                    </div>
                </div>
            </div>
        </div>

        <input type="hidden" id="tanggal" value="<?= esc($defaultTanggal) ?>">

        <!-- KPI Ringkasan SO Hasil -->
        <div class="card border mb-3">
            <div class="card-body p-3">
                <div class="row g-2">
                    <div class="col-6 col-md-4 col-xl-2">
                        <div class="so-kpi-tile">
                            <small class="text-muted d-block">Periode SO</small>
                            <div class="fw-bold font-monospace text-dark text-truncate" id="sum_periode">-</div>
                        </div>
                    </div>
                    <div class="col-6 col-md-4 col-xl-2">
                        <div class="so-kpi-tile">
                            <small class="text-muted d-block">Sudah Diinput</small>
                            <div class="fw-bold text-success font-monospace" id="sum_sudah_input">0</div>
                        </div>
                    </div>
                    <div class="col-6 col-md-4 col-xl-2">
                        <div class="so-kpi-tile">
                            <small class="text-muted d-block">Belum Diinput</small>
                            <div class="fw-bold text-warning font-monospace" id="sum_belum_input">0</div>
                        </div>
                    </div>
                    <div class="col-6 col-md-4 col-xl-2">
                        <div class="so-kpi-tile border-danger-subtle bg-danger-subtle">
                            <small class="text-danger fw-semibold d-block">NK (Kurang/Minus)</small>
                            <div class="fw-bold text-danger font-monospace small">
                                <span id="sum_nk_qty">0</span> | Rp <span id="sum_nk_rp">0</span>
                            </div>
                        </div>
                    </div>
                    <div class="col-6 col-md-4 col-xl-2">
                        <div class="so-kpi-tile border-success-subtle bg-success-subtle">
                            <small class="text-success fw-semibold d-block">NL (Lebih/Plus)</small>
                            <div class="fw-bold text-success font-monospace small">
                                +<span id="sum_nl_qty">0</span> | Rp <span id="sum_nl_rp">0</span>
                            </div>
                        </div>
                    </div>
                    <div class="col-6 col-md-4 col-xl-2">
                        <div class="so-kpi-tile border-primary-subtle bg-primary-subtle" id="sum_nkl_box">
                            <small class="text-primary fw-semibold d-block" id="sum_nkl_label">NKL (Netto)</small>
                            <div class="fw-bolder font-monospace small text-primary" id="sum_nkl_text">
                                <span id="sum_nkl_qty">0</span> | Rp <span id="sum_nkl_rp">0</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="card border">
            <div class="card-body p-2 p-md-3">
                <table id="table-data" class="table table-bordered table-hover table-striped table-sm align-middle w-100 mb-0">
                    <thead class="table-light"></thead>
                    <tbody>
                        <tr>
                            <td>No data to show</td>
                        </tr>
                    </tbody>
                </table>

                <!-- CardView Template for Hasil SO (Mobile Reflow) -->
                <template id="card-so-hasil-template">
                    <div class="so-hasil-card mb-2">
                        <!-- Baris 1: Nama Item, Kode & Status Input -->
                        <div class="d-flex justify-content-between align-items-start gap-2 mb-2 pb-2 border-bottom">
                            <div class="min-w-0 flex-grow-1">
                                <div class="so-card-title text-truncate" data-dtcv-field="1"></div>
                                <div class="d-flex align-items-center gap-1 mt-1 flex-wrap">
                                    <span class="so-item-code-badge"><i class="ti ti-barcode"></i><span data-dtcv-field="0"></span></span>
                                    <span class="badge bg-light text-dark border small" data-dtcv-field="2"></span>
                                </div>
                            </div>
                            <div class="flex-shrink-0" data-dtcv-field="3"></div>
                        </div>

                        <!-- Baris 2: Stok Data vs Fisik -->
                        <div class="row g-2 mb-2">
                            <div class="col-6">
                                <div class="p-2 rounded bg-light border text-center">
                                    <div class="text-muted" style="font-size: 0.72rem;">Stok di Sistem</div>
                                    <div class="fw-semibold font-monospace text-dark" data-dtcv-field="4"></div>
                                </div>
                            </div>
                            <div class="col-6">
                                <div class="p-2 rounded bg-light border text-center">
                                    <div class="text-muted" style="font-size: 0.72rem;">Stok Fisik Real</div>
                                    <div class="fw-semibold font-monospace text-dark" data-dtcv-field="5"></div>
                                </div>
                            </div>
                        </div>

                        <!-- Baris 3: Selisih Qty & Selisih Nilai Rp -->
                        <div class="p-2 rounded border d-flex justify-content-between align-items-center card-selisih-box">
                            <div>
                                <span class="text-muted small">Selisih Qty:</span>
                                <strong class="font-monospace ms-1 card-selisih-qty" data-dtcv-field="6"></strong>
                            </div>
                            <div>
                                <span class="text-muted small">Selisih Finansial:</span>
                                <strong class="font-monospace ms-1 card-selisih-rp" data-dtcv-field="7"></strong>
                            </div>
                        </div>
                    </div>
                </template>
            </div>
        </div>
    </div>
</div>
<?= $this->endSection('content') ?>

<?= $this->section('javascript') ?>
<script>
    function refreshSummary() {
        $.post('<?= base_url('/opname/summary') ?>', {
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

            // Styling highlight NKL Minus (Merah) vs Plus (Hijau)
            const nklRp = Number(res?.sum_nkl_rp ?? 0);
            const nklQty = Number(res?.sum_nkl_qty ?? 0);
            const $nklBox = $('#sum_nkl_box');
            const $nklLabel = $('#sum_nkl_label');
            const $nklText = $('#sum_nkl_text');

            $nklBox.removeClass('border-danger-subtle bg-danger-subtle border-success-subtle bg-success-subtle border-primary-subtle bg-primary-subtle');
            $nklLabel.removeClass('text-danger text-success text-primary');
            $nklText.removeClass('text-danger text-success text-primary');

            if (nklRp < 0 || nklQty < 0) {
                $nklBox.addClass('border-danger-subtle bg-danger-subtle');
                $nklLabel.addClass('text-danger').text('NKL (Minus/Rugi)');
                $nklText.addClass('text-danger');
            } else if (nklRp > 0 || nklQty > 0) {
                $nklBox.addClass('border-success-subtle bg-success-subtle');
                $nklLabel.addClass('text-success').text('NKL (Plus/Lebih)');
                $nklText.addClass('text-success');
            } else {
                $nklBox.addClass('border-primary-subtle bg-primary-subtle');
                $nklLabel.addClass('text-primary').text('NKL (Impas)');
                $nklText.addClass('text-primary');
            }
        }, 'json');
    }

    DataTable.Buttons.defaults.dom.button.className = 'btn btn-primary';
    const table = $('#table-data').DataTable({
        layout: {
            topStart: {
                buttons: [{
                    text: '<i class="ti ti-file-type-xls"></i> Excel',
                    className: 'btn btn-primary btn-sm px-3',
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
        responsive: false,
        lengthChange: false,
        autoWidth: false,
        processing: true,
        serverSide: true,
        ordering: false,
        ajax: {
            url: '<?= base_url('/opname/ajax-hasil') ?>',
            type: 'post',
            data: function(d) {
                d.tanggal = $('#tanggal').val();
            }
        },
        cardView: {
            enable: true,
            breakpoint: 768,
            template: '#card-so-hasil-template',
            gridClass: 'col-12 col-sm-6 mb-2',
            onCardRender: function($card, rowData) {
                const selisih = Number(rowData.selisih || 0);
                const $box = $card.find('.card-selisih-box');
                const $qty = $card.find('.card-selisih-qty');
                const $rp = $card.find('.card-selisih-rp');

                if (selisih < 0) {
                    $box.addClass('border-danger-subtle bg-danger-subtle');
                    $qty.addClass('text-danger');
                    $rp.addClass('text-danger');
                } else if (selisih > 0) {
                    $box.addClass('border-success-subtle bg-success-subtle');
                    $qty.addClass('text-success');
                    $rp.addClass('text-success');
                } else {
                    $box.addClass('bg-light');
                }
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
                className: 'text-center',
                render: function(data) {
                    return data === 'Sudah' ?
                        '<span class="badge bg-success-subtle text-success border border-success-subtle">Sudah</span>' :
                        '<span class="badge bg-warning-subtle text-warning border border-warning-subtle">Belum</span>';
                }
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
                render: function(data) {
                    const val = Number(data || 0);
                    const formatted = val.toLocaleString('id-ID', { maximumFractionDigits: 2 });
                    if (val < 0) return `<span class="text-danger fw-semibold">${formatted}</span>`;
                    if (val > 0) return `<span class="text-success fw-semibold">+${formatted}</span>`;
                    return formatted;
                }
            },
            {
                data: 'selisih_rp',
                title: 'Selisih Rp',
                className: 'text-end',
                render: function(data) {
                    const val = Number(data || 0);
                    const formatted = 'Rp ' + formatMoneyValue(data);
                    if (val < 0) return `<span class="text-danger fw-bold">${formatted}</span>`;
                    if (val > 0) return `<span class="text-success fw-bold">+${formatted}</span>`;
                    return formatted;
                }
            }
        ]
    });

    $(function() {
        refreshSummary();
    });
</script>
<?= $this->endSection('javascript') ?>