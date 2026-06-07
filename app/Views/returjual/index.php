<?= $this->extend('layouts/base') ?>

<?= $this->section('content') ?>
<div class="body-wrapper">
    <div class="container-fluid p-0">
        <div class="card bg-warning-subtle shadow-none position-relative overflow-hidden mb-4">
            <div class="card-body px-4 py-3">
                <div class="row align-items-center">
                    <div class="col-9">
                        <h4 class="fw-semibold mb-2">History Retur Penjualan</h4>
                        <p class="mb-0"><span class="page-pretitle">Total</span> | Daftar transaksi retur penjualan, cetak struk, edit, dan hapus.</p>
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
                    <tbody><tr><td>No data to show</td></tr></tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="modal-detail" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-fullscreen modal-dialog-scrollable">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Detail Retur Penjualan</h5>
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
                            window.location.href = '<?= base_url('/returjual/add') ?>';
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
            url: '<?= base_url('/returjual/ajax') ?>',
            type: 'post'
        },
        columns: [{
                data: 'tanggal',
                title: 'Tanggal',
                render: data => data ? new Date(data).toLocaleString('id-ID') : '-'
            },
            {
                data: 'rj_id',
                title: 'ID Retur'
            },
            {
                data: 'jual_id',
                title: 'No Struk'
            },
            {
                data: 'customer_nama',
                title: 'Customer'
            },
            {
                data: 'jml_item',
                title: 'Item',
                className: 'text-center'
            },
            {
                data: 'gross_retur',
                title: 'Refund',
                className: 'text-end',
                render: data => 'Rp ' + formatMoneyValue(data || 0)
            },
            {
                title: 'Action',
                data: null,
                className: 'text-center',
                render: function(data) {
                    const editBtn = akses_menu?.akses_update === 'Y'
                        ? `<a class="dropdown-item" href="<?= base_url('/returjual/edit') ?>/${data.rj_id}"><i class="ti ti-pencil text-warning"></i> Edit</a>`
                        : '';
                    const deleteBtn = akses_menu?.akses_delete === 'Y'
                        ? `<a class="dropdown-item" href="javascript:void(0)" onclick="deleteRetur('${data.rj_id}')"><i class="ti ti-trash text-danger"></i> Hapus</a>`
                        : '';
                    return `<span class="dropdown">
                        <button class="btn dropdown-toggle align-text-top btn-sm" data-bs-toggle="dropdown">Actions</button>
                        <div class="dropdown-menu dropdown-menu-end">
                            <a class="dropdown-item" href="javascript:void(0)" onclick="showDetail('${data.rj_id}')"><i class="ti ti-eye text-info"></i> Detail</a>
                            <a class="dropdown-item" href="<?= base_url('/returjual/struk') ?>/${data.rj_id}" target="_blank"><i class="ti ti-printer text-success"></i> Cetak Struk</a>
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

    function showDetail(rjId) {
        $('#detail-content').html('<div class="text-center py-5 text-muted">Memuat detail...</div>');
        detailModal.show();
        $.getJSON(`<?= base_url('/returjual/show') ?>/${rjId}`, function(res) {
            if (res.tipe !== 'success') {
                $('#detail-content').html(`<div class="alert alert-danger mb-0">${res.data || 'Detail tidak ditemukan'}</div>`);
                return;
            }

            const data = res.data || {};
            const detailRowsHtml = (data.details || []).map((row, idx) => `
                <tr>
                    <td class="text-center">${idx + 1}</td>
                    <td>${escapeHtml(row.nama_item || row.kode_item)}<br><small class="text-muted">${escapeHtml(row.kode_item || '-')}</small></td>
                    <td class="text-end">${Number(row.qty_retur || 0).toLocaleString('id-ID')}</td>
                    <td>${escapeHtml(row.sat_id || '-')}</td>
                    <td class="text-end">Rp ${formatMoneyValue(row.price || 0)}</td>
                    <td class="text-end">Rp ${formatMoneyValue(row.gross_retur || 0)}</td>
                </tr>
            `).join('');

            $('#detail-content').html(`
                <div class="row g-3 mb-3">
                    <div class="col-md-3"><div class="border rounded p-3 h-100"><small class="text-muted">ID Retur</small><div class="fw-semibold">${data.rj_id || '-'}</div></div></div>
                    <div class="col-md-3"><div class="border rounded p-3 h-100"><small class="text-muted">No Struk</small><div class="fw-semibold">${data.jual_id || '-'}</div></div></div>
                    <div class="col-md-3"><div class="border rounded p-3 h-100"><small class="text-muted">Tanggal Retur</small><div class="fw-semibold">${data.tanggal ? new Date(data.tanggal).toLocaleString('id-ID') : '-'}</div></div></div>
                    <div class="col-md-3"><div class="border rounded p-3 h-100"><small class="text-muted">Customer</small><div class="fw-semibold">${data.customer_nama || 'Pelanggan Umum'}</div></div></div>
                </div>
                <div class="row g-3 mb-3">
                    <div class="col-md-4"><div class="border rounded p-3 h-100"><small class="text-muted">Refund</small><div class="fw-semibold">Rp ${formatMoneyValue(data.gross_retur || 0)}</div></div></div>
                    <div class="col-md-4"><div class="border rounded p-3 h-100"><small class="text-muted">Kasir/Input</small><div class="fw-semibold">${data.updid || '-'}</div></div></div>
                    <div class="col-md-4"><div class="border rounded p-3 h-100"><small class="text-muted">Keterangan</small><div class="fw-semibold">${escapeHtml(data.keterangan || '-')}</div></div></div>
                </div>
                <div class="table-responsive">
                    <table class="table table-sm table-bordered align-middle mb-0">
                        <thead>
                            <tr>
                                <th>No</th>
                                <th>Item</th>
                                <th>Qty Retur</th>
                                <th>Satuan</th>
                                <th>Refund/Satuan</th>
                                <th>Total Refund</th>
                            </tr>
                        </thead>
                        <tbody>${detailRowsHtml || '<tr><td colspan="6" class="text-center text-muted">Tidak ada detail</td></tr>'}</tbody>
                    </table>
                </div>
            `);
        }).fail(function(xhr) {
            $('#detail-content').html(`<div class="alert alert-danger mb-0">${extractErrorMessage(xhr, 'Gagal memuat detail')}</div>`);
        });
    }

    function deleteRetur(rjId) {
        Swal.fire({
            title: 'Hapus retur penjualan ini?',
            text: 'Stok, mutasi kas, dan histori poin akan disesuaikan ulang.',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonText: 'Ya, hapus',
            cancelButtonText: 'Batal'
        }).then((result) => {
            if (!result.isConfirmed) return;
            $.ajax({
                type: 'DELETE',
                url: '<?= base_url('/returjual') ?>',
                dataType: 'json',
                data: {
                    rj_id: rjId
                },
                success: function(res) {
                    if (res.tipe === 'success') {
                        toastr.success(res.data || 'Berhasil');
                        table.ajax.reload(null, false);
                        return;
                    }
                    toastr.error(res.data || 'Gagal menghapus retur penjualan');
                },
                error: function(xhr) {
                    toastr.error(extractErrorMessage(xhr, 'Gagal menghapus retur penjualan'));
                }
            });
        });
    }

    function escapeHtml(value) {
        return String(value ?? '')
            .replace(/&/g, '&amp;')
            .replace(/</g, '&lt;')
            .replace(/>/g, '&gt;')
            .replace(/"/g, '&quot;')
            .replace(/'/g, '&#039;');
    }
</script>
<?= $this->endSection('javascript') ?>
