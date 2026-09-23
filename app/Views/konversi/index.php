<?= $this->extend('layouts/base') ?>

<?= $this->section('content') ?>
<?php
/**
 * @var string $akses_menu
 */
?>
<style>
    /* Styling responsif mobile untuk Konversi Produksi & CardView */
    .konversi-card {
        background: #ffffff;
        border: 1px solid #e2e8f0;
        border-radius: 10px;
        padding: 0.85rem 1rem;
        transition: transform 0.15s ease, box-shadow 0.15s ease;
    }

    .konversi-card:hover {
        border-color: #cbd5e1;
        box-shadow: 0 4px 12px rgba(0, 0, 0, 0.05);
    }

    .konversi-id-badge {
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

    .konversi-item-title {
        font-weight: 700;
        color: var(--bs-heading-color);
        line-height: 1.3;
        font-size: 0.95rem;
    }

    .konversi-metric-box {
        background-color: #f8fafc;
        border: 1px solid #e2e8f0;
        border-radius: 6px;
        padding: 0.45rem 0.65rem;
    }

    /* Reflow card detail modal pada layar kecil */
    @media (max-width: 767.98px) {
        .modal-body-scrollable-mobile {
            padding: 0.75rem !important;
        }
    }
</style>

<div class="body-wrapper">
    <div class="container-fluid p-0">
        <div class="card bg-warning-subtle shadow-none position-relative overflow-hidden mb-3">
            <div class="card-body px-4 py-3">
                <div class="row align-items-center">
                    <div class="col-12 col-lg-8">
                        <h4 class="fw-semibold mb-1">Produksi & Konversi Barang</h4>
                        <p class="mb-0 text-muted small"><span class="page-pretitle fw-semibold text-dark">Total</span> | Bundling & repacking produk (misal: Cedea Fish Roll menjadi Frozen Mix Curah).</p>
                        <small class="text-muted d-block mt-1">Closing aktif: <span class="fw-bold font-monospace text-dark"><?= esc($closingDate ?? '-') ?></span>. Transaksi sebelum tanggal closing dikunci.</small>
                    </div>
                    <div class="col-12 col-lg-4 text-start text-lg-end mt-2 mt-lg-0">
                        <span class="badge bg-warning-subtle text-dark border border-warning-subtle fs-2 px-3 py-2">
                            <i class="ti ti-transform me-1"></i> Mode Konversi
                        </span>
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

                <!-- CardView Template for Konversi (Mobile Reflow) -->
                <template id="card-konversi-template">
                    <div class="konversi-card mb-2">
                        <!-- Baris 1: Item Hasil, ID & Dropdown Aksi -->
                        <div class="d-flex justify-content-between align-items-start gap-2 mb-2 pb-2 border-bottom">
                            <div class="min-w-0 flex-grow-1">
                                <div class="konversi-item-title text-truncate" data-dtcv-field="2"></div>
                                <div class="d-flex align-items-center gap-1 mt-1 flex-wrap">
                                    <span class="konversi-id-badge"><i class="ti ti-hash"></i><span data-dtcv-field="1"></span></span>
                                    <span class="small text-muted font-monospace"><i class="ti ti-calendar me-1"></i><span data-dtcv-field="0"></span></span>
                                </div>
                            </div>
                            <div class="flex-shrink-0" data-dtcv-field="6"></div>
                        </div>

                        <!-- Baris 2: Qty Bahan Asal vs Qty Produk Hasil -->
                        <div class="row g-2 mb-2">
                            <div class="col-6">
                                <div class="konversi-metric-box border-warning-subtle bg-warning-subtle">
                                    <div class="text-muted small" style="font-size: 0.72rem;">Bahan Asal Digunakan:</div>
                                    <div class="fw-bold font-monospace fs-3 text-dark d-flex align-items-center gap-1">
                                        <i class="ti ti-arrow-right-tail text-warning"></i>
                                        <span data-dtcv-field="3"></span>
                                    </div>
                                </div>
                            </div>
                            <div class="col-6">
                                <div class="konversi-metric-box border-success-subtle bg-success-subtle">
                                    <div class="text-muted small" style="font-size: 0.72rem;">Produk Hasil Jadi:</div>
                                    <div class="fw-bolder font-monospace fs-3 text-success d-flex align-items-center gap-1">
                                        <i class="ti ti-circle-check text-success"></i>
                                        <span data-dtcv-field="4"></span>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Baris 3: Operator / Admin & Tombol Detail -->
                        <div class="d-flex align-items-center justify-content-between pt-2 border-top small">
                            <span class="text-muted">Admin: <strong class="text-dark font-monospace ms-1" data-dtcv-field="5"></strong></span>
                            <button type="button" class="btn btn-sm btn-outline-primary px-3 card-btn-detail">
                                <i class="ti ti-eye me-1"></i> Detail
                            </button>
                        </div>
                    </div>
                </template>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="modal-detail" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-xl modal-dialog-scrollable modal-fullscreen-lg-down">
        <div class="modal-content border-0 shadow">
            <div class="modal-header bg-light py-2 px-3">
                <h5 class="modal-title fw-bold fs-4 text-dark"><i class="ti ti-transform text-warning me-1"></i> Detail Konversi & Produksi</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body p-3 modal-body-scrollable-mobile">
                <div id="detail-content"></div>
            </div>
            <div class="modal-footer bg-light py-2 px-3">
                <button type="button" class="btn btn-secondary btn-sm px-3" data-bs-dismiss="modal">Tutup</button>
            </div>
        </div>
    </div>
</div>
<?= $this->endSection('content') ?>

<?= $this->section('javascript') ?>
<script>
    const akses_menu = <?= $akses_menu ?>;
    const detailModal = new bootstrap.Modal(document.getElementById('modal-detail'));

    <?php if (session()->getFlashdata('error')) : ?>
        toastr.error("<?= session()->getFlashdata('error') ?>");
    <?php endif; ?>

    DataTable.Buttons.defaults.dom.button.className = 'btn btn-primary';
    const buttons = [{
        text: '<i class="ti ti-plus"></i> Konversi Baru',
        className: 'btn btn-primary btn-sm px-3',
        action: function() {
            if (akses_menu?.akses_create === 'Y') {
                window.location.href = '<?= base_url('/konversi/add') ?>';
                return;
            }
            toastr.error('Anda tidak memiliki akses untuk ini!');
        }
    }];

    if (akses_menu?.akses_update === 'Y') {
        buttons.push({
            text: '<i class="ti ti-settings"></i> Setting Recipe',
            className: 'btn btn-primary btn-sm px-3',
            action: function() {
                window.location.href = '<?= base_url('/konversi/recipe') ?>';
            }
        });
    }
    buttons.push('pageLength');

    const table = $('#table-data').DataTable({
        layout: {
            topStart: {
                buttons
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
            url: '<?= base_url('/konversi/ajax') ?>',
            type: 'post'
        },
        cardView: {
            enable: true,
            breakpoint: 768,
            template: '#card-konversi-template',
            gridClass: 'col-12 col-sm-6 mb-2',
            onCardRender: function($card, rowData) {
                $card.find('.card-btn-detail').on('click', function() {
                    showDetail(rowData.konversi_id);
                });
            }
        },
        columns: [{
                data: 'tanggal',
                title: 'Tanggal',
                render: data => data ? new Date(String(data).replace(' ', 'T')).toLocaleDateString('id-ID') : '-'
            },
            {
                data: 'konversi_id',
                title: 'ID Konversi',
                className: 'font-monospace'
            },
            {
                data: 'nama_item_hasil',
                title: 'Item Hasil',
                render: function(data, type, row) {
                    return `<div class="fw-semibold text-dark">${data || row.kode_item_hasil || '-'}</div><small class="text-muted font-monospace">${row.kode_item_hasil || '-'}</small>`;
                }
            },
            {
                data: 'total_qty_asal',
                title: 'Qty Asal',
                className: 'text-end',
                render: data => Number(data || 0).toLocaleString('id-ID', {
                    maximumFractionDigits: 2
                })
            },
            {
                data: 'total_qty_hasil',
                title: 'Qty Hasil',
                className: 'text-end',
                render: data => Number(data || 0).toLocaleString('id-ID', {
                    maximumFractionDigits: 2
                })
            },
            {
                data: 'updid',
                title: 'Admin'
            },
            {
                title: 'Action',
                data: null,
                className: 'text-center',
                render: function(data) {
                    const deleteBtn = akses_menu?.akses_delete === 'Y' ?
                        (data.can_delete ?
                            `<a class="dropdown-item py-2" href="javascript:void(0)" onclick="deleteKonversi('${data.konversi_id}')"><i class="ti ti-trash text-danger me-2"></i> Hapus</a>` :
                            `<a class="dropdown-item py-2 text-muted" href="javascript:void(0)" onclick="showLockedNotice('${data.closing_date}')"><i class="ti ti-lock text-danger me-2"></i> Hapus Terkunci</a>`) :
                        '';

                    return `<span class="dropdown">
                        <button class="btn dropdown-toggle align-text-top btn-sm btn-light border px-2 py-1" data-bs-toggle="dropdown">Aksi</button>
                        <div class="dropdown-menu dropdown-menu-end">
                            <a class="dropdown-item py-2" href="javascript:void(0)" onclick="showDetail('${data.konversi_id}')"><i class="ti ti-eye text-info me-2"></i> Detail</a>
                            ${deleteBtn}
                        </div>
                    </span>`;
                }
            }
        ]
    });

    table.on('xhr.dt', function(e, settings, json) {
        $('.page-pretitle').text(`Total Data : ${json?.recordsTotal || 0}`);
    });

    function showDetail(konversiId) {
        $('#detail-content').html('<div class="text-center py-5 text-muted"><div class="spinner-border spinner-border-sm text-primary me-2"></div>Memuat detail...</div>');
        detailModal.show();
        $.getJSON(`<?= base_url('/konversi/show') ?>/${konversiId}`, function(res) {
            if (res.tipe !== 'success') {
                $('#detail-content').html(`<div class="alert alert-danger mb-0">${res.data || 'Detail tidak ditemukan'}</div>`);
                return;
            }

            const data = res.data || {};
            const sourceRows = (data.details || []).filter(row => row.role_item === 'ASAL').map((row, idx) => `
                <tr>
                    <td class="text-center">${idx + 1}</td>
                    <td><div class="fw-semibold">${row.nama_item || row.kode_item}</div><small class="text-muted font-monospace">${row.kode_item || '-'}</small></td>
                    <td><span class="badge bg-light text-dark border">${row.sat_id || '-'}</span></td>
                    <td class="text-end font-monospace fw-semibold">${Number(row.qty_transaksi || 0).toLocaleString('id-ID', { maximumFractionDigits: 2 })}</td>
                    <td class="text-end font-monospace">Rp ${formatMoneyValue(row.hpp_satuan || 0)}</td>
                    <td class="text-end font-monospace fw-bold text-danger">Rp ${formatMoneyValue(row.total_hpp || 0)}</td>
                </tr>
            `).join('');

            const resultRows = (data.details || []).filter(row => row.role_item === 'HASIL').map((row, idx) => `
                <tr>
                    <td class="text-center">${idx + 1}</td>
                    <td><div class="fw-semibold text-primary">${row.nama_item || row.kode_item}</div><small class="text-muted font-monospace">${row.kode_item || '-'}</small></td>
                    <td><span class="badge bg-light text-dark border">${row.sat_id || '-'}</span></td>
                    <td class="text-end font-monospace fw-bold text-success">${Number(row.qty_transaksi || 0).toLocaleString('id-ID', { maximumFractionDigits: 2 })}</td>
                    <td class="text-end font-monospace">Rp ${formatMoneyValue(row.hpp_sat_before || 0)}</td>
                    <td class="text-end font-monospace fw-bold text-primary">Rp ${formatMoneyValue(row.hpp_sat_after || 0)}</td>
                    <td class="text-end font-monospace fw-bold">Rp ${formatMoneyValue(row.total_hpp || 0)}</td>
                </tr>
            `).join('');

            const formula = (data.details || []).find(row => row.formula_text)?.formula_text || '-';

            $('#detail-content').html(`
                <div class="row g-2 mb-3">
                    <div class="col-6 col-md-3">
                        <div class="border rounded p-2 bg-light">
                            <small class="text-muted d-block" style="font-size: 0.72rem;">ID Konversi</small>
                            <div class="fw-bold font-monospace text-dark text-truncate">${data.konversi_id || '-'}</div>
                        </div>
                    </div>
                    <div class="col-6 col-md-3">
                        <div class="border rounded p-2 bg-light">
                            <small class="text-muted d-block" style="font-size: 0.72rem;">Tanggal Transaksi</small>
                            <div class="fw-semibold text-dark">${data.tanggal ? new Date(String(data.tanggal).replace(' ', 'T')).toLocaleDateString('id-ID') : '-'}</div>
                        </div>
                    </div>
                    <div class="col-6 col-md-3">
                        <div class="border rounded p-2 bg-light">
                            <small class="text-muted d-block" style="font-size: 0.72rem;">Admin / Operator</small>
                            <div class="fw-semibold text-dark font-monospace">${data.updid || '-'}</div>
                        </div>
                    </div>
                    <div class="col-6 col-md-3">
                        <div class="border rounded p-2 bg-light">
                            <small class="text-muted d-block" style="font-size: 0.72rem;">Lokasi Toko</small>
                            <div class="fw-semibold text-dark text-truncate">${data.toko_nama || '-'}</div>
                        </div>
                    </div>
                </div>

                <div class="alert alert-light border mb-3 small py-2 px-3">
                    <i class="ti ti-calculator text-primary me-1"></i><strong>Formula Trace HPP:</strong> <span class="font-monospace text-dark">${formula}</span>
                </div>

                <div class="card border mb-3">
                    <div class="card-header bg-warning-subtle py-2 px-3">
                        <h6 class="mb-0 fw-bold text-dark"><i class="ti ti-package-export text-warning me-1"></i> Bahan Baku Asal (Dikonsumsi)</h6>
                    </div>
                    <div class="card-body p-0">
                        <div class="table-responsive">
                            <table class="table table-sm table-bordered table-striped align-middle mb-0">
                                <thead class="table-light">
                                    <tr>
                                        <th style="width: 40px;" class="text-center">No</th>
                                        <th>Item Bahan</th>
                                        <th>Satuan</th>
                                        <th class="text-end">Qty Pakai</th>
                                        <th class="text-end">HPP Satuan</th>
                                        <th class="text-end">Total Nilai HPP</th>
                                    </tr>
                                </thead>
                                <tbody>${sourceRows || '<tr><td colspan="6" class="text-center text-muted py-3">Tidak ada detail bahan asal</td></tr>'}</tbody>
                            </table>
                        </div>
                    </div>
                </div>

                <div class="card border mb-0">
                    <div class="card-header bg-success-subtle py-2 px-3">
                        <h6 class="mb-0 fw-bold text-success"><i class="ti ti-package-import me-1"></i> Produk Jadi Hasil Konversi</h6>
                    </div>
                    <div class="card-body p-0">
                        <div class="table-responsive">
                            <table class="table table-sm table-bordered table-striped align-middle mb-0">
                                <thead class="table-light">
                                    <tr>
                                        <th style="width: 40px;" class="text-center">No</th>
                                        <th>Item Hasil</th>
                                        <th>Satuan</th>
                                        <th class="text-end">Qty Jadi</th>
                                        <th class="text-end">HPP Before</th>
                                        <th class="text-end">HPP After</th>
                                        <th class="text-end">Total HPP Bahan</th>
                                    </tr>
                                </thead>
                                <tbody>${resultRows || '<tr><td colspan="7" class="text-center text-muted py-3">Tidak ada detail produk hasil</td></tr>'}</tbody>
                            </table>
                        </div>
                    </div>
                </div>
            `);
        }).fail(function(xhr) {
            $('#detail-content').html(`<div class="alert alert-danger mb-0">${extractErrorMessage(xhr, 'Gagal memuat detail')}</div>`);
        });
    }

    function deleteKonversi(konversiId) {
        Swal.fire({
            title: 'Hapus transaksi konversi?',
            text: 'Stok bahan akan dikembalikan, histori konversi dihapus, dan kalkulasi HPP produk hasil akan direstore.',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonText: '<i class="ti ti-trash"></i> Ya, hapus',
            cancelButtonText: 'Batal',
            customClass: {
                confirmButton: 'btn btn-danger px-3 me-2',
                cancelButton: 'btn btn-light px-3'
            },
            buttonsStyling: false
        }).then((result) => {
            if (!result.isConfirmed) return;
            $.ajax({
                type: 'DELETE',
                url: '<?= base_url('/konversi') ?>',
                dataType: 'json',
                data: {
                    konversi_id: konversiId
                },
                success: function(res) {
                    if (res.tipe === 'success') {
                        toastr.success(res.data || 'Transaksi konversi berhasil dihapus');
                        table.ajax.reload(null, false);
                        return;
                    }
                    toastr.error(res.data || 'Gagal menghapus transaksi');
                },
                error: function(xhr) {
                    toastr.error(extractErrorMessage(xhr, 'Gagal menghapus transaksi'));
                }
            });
        });
    }

    function showLockedNotice(closingDate) {
        toastr.error(`Transaksi konversi sebelum ${new Date(closingDate).toLocaleDateString('id-ID')} sudah melewati periode closing dan tidak bisa dihapus.`);
    }
</script>
<?= $this->endSection('javascript') ?>