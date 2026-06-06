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
                        <h4 class="fw-semibold mb-2">Manajemen Retur Pembelian</h4>
                        <p class="mb-0"><span class="page-pretitle">Total</span> | Daftar retur pembelian supplier dan potongan hutang dari retur.</p>
                        <small class="text-muted d-block mt-1">Closing aktif: <?= esc($closingDate ?? '-') ?>. Retur `SELESAI` sebelum tanggal ini dikunci dari edit dan hapus.</small>
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
    <div class="modal-dialog modal-xl modal-dialog-scrollable">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Detail Retur Pembelian</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body" id="detail-content"></div>
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
                            window.location.href = '<?= base_url('/returbeli/add') ?>';
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
            url: '<?= base_url('/returbeli/ajax') ?>',
            type: 'post'
        },
        columns: [{
                data: 'tanggal',
                title: 'Tanggal',
                render: data => data ? new Date(data).toLocaleDateString('id-ID') : '-'
            },
            {
                data: 'retur_id',
                title: 'ID Retur'
            },
            {
                data: 'supplier_nama',
                title: 'Supplier / Settlement',
                render: function(data, type, row) {
                    const settlementText = row.settlement_mode === 'CASHBACK'
                        ? 'Cashback Supplier'
                        : `${row.beli_id || '-'} / ${row.invoice || '-'}`;
                    return `<div class="fw-semibold">${data || row.supco}</div><small class="text-muted">${settlementText}</small>`;
                }
            },
            {
                data: 'jml_item',
                title: 'Item',
                className: 'text-center'
            },
            {
                data: 'total_retur',
                title: 'Total Retur',
                className: 'text-end',
                render: data => 'Rp ' + formatMoneyValue(data)
            },
            {
                data: 'status_retur',
                title: 'Status',
                className: 'text-center',
                render: data => data === 'SELESAI' ?
                    '<span class="badge bg-success-subtle text-success">SELESAI</span>' :
                    '<span class="badge bg-warning-subtle text-warning">DRAFT</span>'
            },
            {
                title: 'Action',
                data: null,
                className: 'text-center',
                render: function(data) {
                    const isLocked = data.can_edit === false;
                    const editBtn = akses_menu?.akses_update === 'Y' ?
                        (isLocked ?
                            `<a class="dropdown-item text-muted" href="javascript:void(0)" onclick="showLockedNotice('${data.closing_date}','edit')"><i class="ti ti-lock text-danger"></i> Edit Terkunci</a>` :
                            `<a class="dropdown-item" href="<?= base_url('/returbeli/edit') ?>/${data.retur_id}"><i class="ti ti-pencil text-warning"></i> Edit</a>`) : '';
                    const deleteBtn = akses_menu?.akses_delete === 'Y' ?
                        (isLocked ?
                            `<a class="dropdown-item text-muted" href="javascript:void(0)" onclick="showLockedNotice('${data.closing_date}','hapus')"><i class="ti ti-lock text-danger"></i> Hapus Terkunci</a>` :
                            `<a class="dropdown-item" href="javascript:void(0)" onclick="deleteRetur('${data.retur_id}')"><i class="ti ti-trash text-danger"></i> Hapus</a>`) : '';
                    return `<span class="dropdown">
                        <button class="btn dropdown-toggle align-text-top btn-sm" data-bs-toggle="dropdown">Actions</button>
                        <div class="dropdown-menu dropdown-menu-end">
                            <a class="dropdown-item" href="javascript:void(0)" onclick="showDetail('${data.retur_id}')"><i class="ti ti-eye text-info"></i> Detail</a>
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

    function showDetail(returId) {
        $('#detail-content').html('<div class="text-center py-5 text-muted">Memuat detail...</div>');
        detailModal.show();
        $.getJSON(`<?= base_url('/returbeli/show') ?>/${returId}`, function(res) {
            if (res.tipe !== 'success') {
                $('#detail-content').html(`<div class="alert alert-danger mb-0">${res.data || 'Detail tidak ditemukan'}</div>`);
                return;
            }
            const data = res.data;
            const detailRows = (data.details || []).map((row, idx) => `
                <tr>
                    <td class="text-center">${idx + 1}</td>
                    <td>${row.nama_item || row.kode_item}<br><small class="text-muted">${row.kode_item}</small></td>
                    <td class="text-end">${Number(row.qty_retur || 0).toLocaleString('id-ID')}</td>
                    <td>${row.sat_id}</td>
                    <td class="text-end">${Number(row.qty_stok || 0).toLocaleString('id-ID')}</td>
                    <td class="text-end">Rp ${formatMoneyValue(row.price)}</td>
                    <td class="text-end">Rp ${formatMoneyValue(row.gross_retur)}</td>
                </tr>
            `).join('');

            $('#detail-content').html(`
                <div class="row g-3 mb-3">
                    <div class="col-md-3"><div class="border rounded p-3 h-100"><small class="text-muted">ID Retur</small><div class="fw-semibold">${data.retur_id}</div></div></div>
                    <div class="col-md-3"><div class="border rounded p-3 h-100"><small class="text-muted">Tanggal</small><div class="fw-semibold">${new Date(data.tanggal).toLocaleDateString('id-ID')}</div></div></div>
                    <div class="col-md-3"><div class="border rounded p-3 h-100"><small class="text-muted">Supplier</small><div class="fw-semibold">${data.supplier_nama || data.supco}</div></div></div>
                    <div class="col-md-3"><div class="border rounded p-3 h-100"><small class="text-muted">Penyelesaian</small><div class="fw-semibold">${data.settlement_mode === 'CASHBACK' ? 'Cashback Supplier' : `${data.beli_id || '-'} / ${data.invoice || '-'}`}</div></div></div>
                </div>
                <div class="row g-3 mb-3">
                    <div class="col-md-3"><div class="border rounded p-3 h-100"><small class="text-muted">Status</small><div class="fw-semibold">${data.status_retur}</div></div></div>
                    <div class="col-md-3"><div class="border rounded p-3 h-100"><small class="text-muted">Total Retur</small><div class="fw-semibold">Rp ${formatMoneyValue(data.total_retur)}</div></div></div>
                    <div class="col-md-6"><div class="border rounded p-3 h-100"><small class="text-muted">Keterangan</small><div class="fw-semibold">${data.keterangan || '-'}</div></div></div>
                </div>
                <div class="table-responsive">
                    <table class="table table-sm table-bordered align-middle mb-0">
                        <thead>
                            <tr>
                                <th>No</th>
                                <th>Item</th>
                                <th>Qty Retur</th>
                                <th>Satuan</th>
                                <th>Qty Stock</th>
                                <th>Price</th>
                                <th>Gross Retur</th>
                            </tr>
                        </thead>
                        <tbody>${detailRows}</tbody>
                    </table>
                </div>
            `);
        }).fail(function(xhr) {
            $('#detail-content').html(`<div class="alert alert-danger mb-0">${extractErrorMessage(xhr, 'Gagal memuat detail')}</div>`);
        });
    }

    function deleteRetur(returId) {
        Swal.fire({
            title: 'Hapus retur ini?',
            text: 'Jika status retur selesai, stok dan potongan hutang akan dikembalikan.',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonText: 'Ya, hapus',
            cancelButtonText: 'Batal'
        }).then((result) => {
            if (!result.isConfirmed) return;
            $.ajax({
                type: 'POST',
                url: '<?= base_url('/returbeli') ?>',
                dataType: 'json',
                data: {
                    _method: 'DELETE',
                    retur_id: returId
                },
                success: function(res) {
                    if (res.tipe === 'success') {
                        toastr.success(res.data || 'Berhasil');
                        table.ajax.reload(null, false);
                        return;
                    }
                    toastr.error(res.data || 'Gagal');
                },
                error: function(xhr) {
                    toastr.error(extractErrorMessage(xhr, 'Gagal menghapus retur pembelian'));
                }
            });
        });
    }

    function showLockedNotice(closingDate, jenis) {
        toastr.error(`Retur SELESAI sebelum ${new Date(closingDate).toLocaleDateString('id-ID')} sudah melewati periode closing dan tidak bisa di${jenis}.`);
    }
</script>
<?= $this->endSection('javascript') ?>
