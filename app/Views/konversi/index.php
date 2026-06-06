<?= $this->extend('layouts/base') ?>

<?= $this->section('content') ?>
<?php
/**
 * @var string $akses_menu
 */
?>
<div class="body-wrapper">
    <div class="container-fluid p-0">
        <div class="card bg-warning-subtle shadow-none position-relative overflow-hidden mb-4">
            <div class="card-body px-4 py-3">
                <div class="row align-items-center">
                    <div class="col-9">
                        <h4 class="fw-semibold mb-2">Produksi / Konversi</h4>
                        <p class="mb-0"><span class="page-pretitle">Total</span> | Rekam bundling dan repacking toko dengan recipe global.</p>
                        <small class="text-muted d-block mt-1">Closing aktif: <?= esc($closingDate ?? '-') ?>. Hanya tersedia create dan delete, delete akan terkunci bila melewati periode closing.</small>
                    </div>
                    <div class="col-3">
                        <div class="text-center mb-n5">
                            <img src="<?= base_url(); ?>/assets/images/breadcrumb/ChatBc.png" alt="modernize-img" class="img-fluid mb-n4" />
                        </div>
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

<div class="modal fade" id="modal-detail" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-fullscreen modal-dialog-scrollable">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Detail Konversi</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div id="detail-content"></div>
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
        text: '<i class="ti ti-plus"></i> Tambah',
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
        responsive: true,
        lengthChange: false,
        autoWidth: false,
        processing: true,
        serverSide: true,
        ordering: false,
        ajax: {
            url: '<?= base_url('/konversi/ajax') ?>',
            type: 'post'
        },
        columns: [{
                data: 'tanggal',
                title: 'Tanggal',
                render: data => data ? new Date(String(data).replace(' ', 'T')).toLocaleDateString('id-ID') : '-'
            },
            {
                data: 'konversi_id',
                title: 'ID'
            },
            {
                data: 'nama_item_hasil',
                title: 'Item Hasil',
                render: function(data, type, row) {
                    return `<div class="fw-semibold">${data || row.kode_item_hasil || '-'}</div><small class="text-muted">${row.kode_item_hasil || '-'}</small>`;
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
                responsivePriority: 1,
                render: function(data) {
                    const deleteBtn = akses_menu?.akses_delete === 'Y' ?
                        (data.can_delete ?
                            `<a class="dropdown-item" href="javascript:void(0)" onclick="deleteKonversi('${data.konversi_id}')"><i class="ti ti-trash text-danger"></i> Hapus</a>` :
                            `<a class="dropdown-item text-muted" href="javascript:void(0)" onclick="showLockedNotice('${data.closing_date}')"><i class="ti ti-lock text-danger"></i> Hapus Terkunci</a>`) :
                        '';

                    return `<span class="dropdown">
                        <button class="btn dropdown-toggle align-text-top btn-sm" data-bs-toggle="dropdown">Actions</button>
                        <div class="dropdown-menu dropdown-menu-end">
                            <a class="dropdown-item" href="javascript:void(0)" onclick="showDetail('${data.konversi_id}')"><i class="ti ti-eye text-info"></i> Detail</a>
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
        $('#detail-content').html('<div class="text-center py-5 text-muted">Memuat detail...</div>');
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
                    <td>${row.nama_item || row.kode_item}<br><small class="text-muted">${row.kode_item || '-'}</small></td>
                    <td>${row.sat_id || '-'}</td>
                    <td class="text-end">${Number(row.qty_transaksi || 0).toLocaleString('id-ID', { maximumFractionDigits: 2 })}</td>
                    <td class="text-end">Rp ${formatMoneyValue(row.hpp_satuan || 0)}</td>
                    <td class="text-end">Rp ${formatMoneyValue(row.total_hpp || 0)}</td>
                </tr>
            `).join('');

            const resultRows = (data.details || []).filter(row => row.role_item === 'HASIL').map((row, idx) => `
                <tr>
                    <td class="text-center">${idx + 1}</td>
                    <td>${row.nama_item || row.kode_item}<br><small class="text-muted">${row.kode_item || '-'}</small></td>
                    <td>${row.sat_id || '-'}</td>
                    <td class="text-end">${Number(row.qty_transaksi || 0).toLocaleString('id-ID', { maximumFractionDigits: 2 })}</td>
                    <td class="text-end">Rp ${formatMoneyValue(row.hpp_sat_before || 0)}</td>
                    <td class="text-end">Rp ${formatMoneyValue(row.hpp_sat_after || 0)}</td>
                    <td class="text-end">Rp ${formatMoneyValue(row.total_hpp || 0)}</td>
                </tr>
            `).join('');

            const formula = (data.details || []).find(row => row.formula_text)?.formula_text || '-';

            $('#detail-content').html(`
                <div class="row g-3 mb-3">
                    <div class="col-md-3"><div class="border rounded p-3 h-100"><small class="text-muted">ID Konversi</small><div class="fw-semibold">${data.konversi_id || '-'}</div></div></div>
                    <div class="col-md-3"><div class="border rounded p-3 h-100"><small class="text-muted">Tanggal</small><div class="fw-semibold">${data.tanggal ? new Date(String(data.tanggal).replace(' ', 'T')).toLocaleDateString('id-ID') : '-'}</div></div></div>
                    <div class="col-md-3"><div class="border rounded p-3 h-100"><small class="text-muted">Admin</small><div class="fw-semibold">${data.updid || '-'}</div></div></div>
                    <div class="col-md-3"><div class="border rounded p-3 h-100"><small class="text-muted">Toko</small><div class="fw-semibold">${data.toko_nama || '-'}</div></div></div>
                </div>
                <div class="alert alert-light border mb-3"><strong>Trace HPP:</strong> ${formula}</div>
                <h6 class="mb-2">Bahan Asal</h6>
                <div class="table-responsive mb-3">
                    <table class="table table-sm table-bordered align-middle mb-0">
                        <thead>
                            <tr>
                                <th>No</th>
                                <th>Item</th>
                                <th>Satuan</th>
                                <th>Qty Pakai</th>
                                <th>HPP Satuan</th>
                                <th>Total HPP</th>
                            </tr>
                        </thead>
                        <tbody>${sourceRows || '<tr><td colspan="6" class="text-center text-muted">Tidak ada detail asal</td></tr>'}</tbody>
                    </table>
                </div>
                <h6 class="mb-2">Produk Hasil</h6>
                <div class="table-responsive">
                    <table class="table table-sm table-bordered align-middle mb-0">
                        <thead>
                            <tr>
                                <th>No</th>
                                <th>Item</th>
                                <th>Satuan</th>
                                <th>Qty Hasil</th>
                                <th>HPP Before</th>
                                <th>HPP After</th>
                                <th>Total HPP Bahan</th>
                            </tr>
                        </thead>
                        <tbody>${resultRows || '<tr><td colspan="7" class="text-center text-muted">Tidak ada detail hasil</td></tr>'}</tbody>
                    </table>
                </div>
            `);
        }).fail(function(xhr) {
            $('#detail-content').html(`<div class="alert alert-danger mb-0">${extractErrorMessage(xhr, 'Gagal memuat detail')}</div>`);
        });
    }

    function deleteKonversi(konversiId) {
        Swal.fire({
            title: 'Hapus transaksi konversi ini?',
            text: 'Stok akan dikembalikan, histori konversi dihapus, dan HPP hasil akan dicoba dikembalikan sesuai histori.',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonText: 'Ya, hapus',
            cancelButtonText: 'Batal'
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
                        toastr.success(res.data || 'Berhasil');
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
