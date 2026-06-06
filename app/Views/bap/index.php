<?= $this->extend('layouts/base') ?>

<?= $this->section('content') ?>
<?php
/**
 * @var string $akses_menu
 */
?>
<div class="body-wrapper">
    <div class="container-fluid p-0">
        <div class="card bg-danger-subtle shadow-none position-relative overflow-hidden mb-4">
            <div class="card-body px-4 py-3">
                <div class="row align-items-center">
                    <div class="col-9">
                        <h4 class="fw-semibold mb-2">Berita Acara Pemusnahan</h4>
                        <p class="mb-0"><span class="page-pretitle">Total</span> | Input dan monitoring item tidak layak jual yang dimusnahkan.</p>
                        <small class="text-muted d-block mt-1">Closing aktif: <?= esc($closingDate ?? '-') ?>. Dokumen sebelum tanggal ini dikunci dari edit dan hapus.</small>
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
                <h5 class="modal-title">Detail BAP</h5>
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
    const table = $('#table-data').DataTable({
        layout: {
            topStart: {
                buttons: [{
                    text: '<i class="ti ti-plus"></i> Tambah',
                    action: function() {
                        if (akses_menu?.akses_create === 'Y') {
                            window.location.href = '<?= base_url('/bap/add') ?>';
                            return;
                        }
                        toastr.error('Anda tidak memiliki akses untuk ini!');
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
            url: '<?= base_url('/bap/ajax') ?>',
            type: 'post'
        },
        columns: [{
                data: 'tanggal',
                title: 'Tanggal',
                render: data => data ? new Date(String(data).replace(' ', 'T')).toLocaleDateString('id-ID') : '-'
            },
            {
                data: 'bap_id',
                title: 'No. BAP'
            },
            {
                data: 'toko_nama',
                title: 'Toko',
                render: data => data || '-'
            },
            {
                data: 'jml_item',
                title: 'Item',
                className: 'text-center'
            },
            {
                data: 'total_qty',
                title: 'Qty',
                className: 'text-end',
                render: data => Number(data || 0).toLocaleString('id-ID', {
                    maximumFractionDigits: 2
                })
            },
            {
                data: 'total_gross',
                title: 'Nilai',
                className: 'text-end',
                render: data => 'Rp ' + formatMoneyValue(data || 0)
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
                    const editBtn = akses_menu?.akses_update === 'Y' ?
                        (data.can_edit ?
                            `<a class="dropdown-item" href="<?= base_url('/bap/edit') ?>/${data.bap_id}"><i class="ti ti-pencil text-warning"></i> Edit</a>` :
                            `<a class="dropdown-item text-muted" href="javascript:void(0)" onclick="showLockedNotice('${data.closing_date}','edit')"><i class="ti ti-lock text-danger"></i> Edit Terkunci</a>`) :
                        '';
                    const deleteBtn = akses_menu?.akses_delete === 'Y' ?
                        (data.can_edit ?
                            `<a class="dropdown-item" href="javascript:void(0)" onclick="deleteBap('${data.bap_id}')"><i class="ti ti-trash text-danger"></i> Hapus</a>` :
                            `<a class="dropdown-item text-muted" href="javascript:void(0)" onclick="showLockedNotice('${data.closing_date}','hapus')"><i class="ti ti-lock text-danger"></i> Hapus Terkunci</a>`) :
                        '';

                    return `<span class="dropdown">
                        <button class="btn dropdown-toggle align-text-top btn-sm" data-bs-toggle="dropdown">Actions</button>
                        <div class="dropdown-menu dropdown-menu-end">
                            <a class="dropdown-item" href="javascript:void(0)" onclick="showDetail('${data.bap_id}')"><i class="ti ti-eye text-info"></i> Detail</a>
                            <a class="dropdown-item" href="<?= base_url('/bap/print') ?>/${data.bap_id}" target="_blank"><i class="ti ti-printer text-success"></i> Cetak BAP</a>
                            ${editBtn}
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

    function showDetail(bapId) {
        $('#detail-content').html('<div class="text-center py-5 text-muted">Memuat detail...</div>');
        detailModal.show();
        $.getJSON(`<?= base_url('/bap/show') ?>/${bapId}`, function(res) {
            if (res.tipe !== 'success') {
                $('#detail-content').html(`<div class="alert alert-danger mb-0">${res.data || 'Detail tidak ditemukan'}</div>`);
                return;
            }

            const data = res.data || {};
            const detailRows = (data.details || []).map((row, idx) => `
                <tr>
                    <td class="text-center">${idx + 1}</td>
                    <td>${row.nama_item || row.kode_item}<br><small class="text-muted">${row.kode_item || '-'}</small></td>
                    <td>${row.sat_id || '-'}</td>
                    <td class="text-end">${Number(row.qty_so || 0).toLocaleString('id-ID', { maximumFractionDigits: 2 })}</td>
                    <td class="text-end">Rp ${formatMoneyValue(row.price || 0)}</td>
                    <td class="text-end">Rp ${formatMoneyValue(row.gross || 0)}</td>
                </tr>
            `).join('');

            $('#detail-content').html(`
                <div class="row g-3 mb-3">
                    <div class="col-md-3"><div class="border rounded p-3 h-100"><small class="text-muted">No. BAP</small><div class="fw-semibold">${data.bap_id || '-'}</div></div></div>
                    <div class="col-md-3"><div class="border rounded p-3 h-100"><small class="text-muted">Tanggal</small><div class="fw-semibold">${data.tanggal ? new Date(String(data.tanggal).replace(' ', 'T')).toLocaleDateString('id-ID') : '-'}</div></div></div>
                    <div class="col-md-3"><div class="border rounded p-3 h-100"><small class="text-muted">Admin</small><div class="fw-semibold">${data.updid || '-'}</div></div></div>
                    <div class="col-md-3"><div class="border rounded p-3 h-100"><small class="text-muted">Toko</small><div class="fw-semibold">${data.toko_nama || '-'}</div></div></div>
                </div>
                <div class="row g-3 mb-3">
                    <div class="col-md-3"><div class="border rounded p-3 h-100"><small class="text-muted">Jumlah Item</small><div class="fw-semibold">${Number(data.jml_item || 0).toLocaleString('id-ID')}</div></div></div>
                    <div class="col-md-3"><div class="border rounded p-3 h-100"><small class="text-muted">Total Qty</small><div class="fw-semibold">${Number(data.total_qty || 0).toLocaleString('id-ID', { maximumFractionDigits: 2 })}</div></div></div>
                    <div class="col-md-3"><div class="border rounded p-3 h-100"><small class="text-muted">Total Nilai</small><div class="fw-semibold">Rp ${formatMoneyValue(data.total_gross || 0)}</div></div></div>
                    <div class="col-md-3"><div class="border rounded p-3 h-100"><small class="text-muted">Keterangan</small><div class="fw-semibold">${data.keterangan || '-'}</div></div></div>
                </div>
                <div class="table-responsive">
                    <table class="table table-sm table-bordered align-middle mb-0">
                        <thead>
                            <tr>
                                <th>No</th>
                                <th>Item</th>
                                <th>Satuan</th>
                                <th>Qty</th>
                                <th>Harga</th>
                                <th>Total</th>
                            </tr>
                        </thead>
                        <tbody>${detailRows || '<tr><td colspan="6" class="text-center text-muted">Tidak ada detail</td></tr>'}</tbody>
                    </table>
                </div>
            `);
        }).fail(function(xhr) {
            $('#detail-content').html(`<div class="alert alert-danger mb-0">${extractErrorMessage(xhr, 'Gagal memuat detail')}</div>`);
        });
    }

    function deleteBap(bapId) {
        Swal.fire({
            title: 'Hapus dokumen BAP ini?',
            text: 'Seluruh item pemusnahan pada dokumen ini akan dihapus dan stok dihitung ulang.',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonText: 'Ya, hapus',
            cancelButtonText: 'Batal'
        }).then((result) => {
            if (!result.isConfirmed) return;
            $.ajax({
                type: 'DELETE',
                url: '<?= base_url('/bap') ?>',
                dataType: 'json',
                data: {
                    bap_id: bapId
                },
                success: function(res) {
                    if (res.tipe === 'success') {
                        toastr.success(res.data || 'Berhasil');
                        table.ajax.reload(null, false);
                        return;
                    }
                    toastr.error(res.data || 'Gagal menghapus dokumen');
                },
                error: function(xhr) {
                    toastr.error(extractErrorMessage(xhr, 'Gagal menghapus dokumen'));
                }
            });
        });
    }

    function showLockedNotice(closingDate, jenis) {
        toastr.error(`Dokumen BAP sebelum ${new Date(closingDate).toLocaleDateString('id-ID')} sudah melewati periode closing dan tidak bisa di${jenis}.`);
    }
</script>
<?= $this->endSection('javascript') ?>
