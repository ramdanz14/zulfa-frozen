<?= $this->extend('layouts/base') ?>

<?= $this->section('content') ?>
<?php
/**
 * @var string $akses_menu
 * @var string $defaultStartDate
 * @var string $defaultEndDate
 */
?>
<div class="body-wrapper">
    <div class="container-fluid p-0">
        <div class="card bg-info-subtle shadow-none position-relative overflow-hidden mb-4">
            <div class="card-body px-4 py-3">
                <div class="row align-items-center">
                    <div class="col-9">
                        <h4 class="fw-semibold mb-2">Monitoring Penjualan</h4>
                        <p class="mb-0"><span class="page-pretitle">Total</span> | Daftar transaksi penjualan untuk edit, hapus, reprint, dan buat retur.</p>
                        <small class="text-muted d-block mt-1">Edit dan hapus hanya diizinkan untuk transaksi dengan tanggal hari ini.</small>
                    </div>
                    <div class="col-3">
                        <div class="text-center mb-n5">
                            <img src="<?= base_url(); ?>/assets/images/breadcrumb/ChatBc.png" alt="modernize-img" class="img-fluid mb-n4" />
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="card mb-3">
            <div class="card-body">
                <div class="row g-3 align-items-end">
                    <div class="col-md-3">
                        <label for="filter-start-date" class="form-label">Tanggal Awal</label>
                        <input type="date" class="form-control" id="filter-start-date" value="<?= esc($defaultStartDate ?? date('Y-m-d')) ?>">
                    </div>
                    <div class="col-md-3">
                        <label for="filter-end-date" class="form-label">Tanggal Akhir</label>
                        <input type="date" class="form-control" id="filter-end-date" value="<?= esc($defaultEndDate ?? date('Y-m-d')) ?>">
                    </div>
                    <div class="col-md-6 d-flex gap-2 flex-wrap">
                        <button type="button" class="btn btn-primary" id="btn-apply-filter"><i class="ti ti-filter"></i> Terapkan Filter</button>
                        <button type="button" class="btn btn-light" id="btn-reset-filter"><i class="ti ti-refresh"></i> Hari Ini</button>
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
                <h5 class="modal-title">Detail Penjualan</h5>
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
    const todayFilter = '<?= esc(date('Y-m-d')) ?>';

    <?php if (session()->getFlashdata('error')) : ?>
        toastr.error("<?= session()->getFlashdata('error') ?>");
    <?php endif; ?>

    DataTable.Buttons.defaults.dom.button.className = 'btn btn-primary';
    const table = $('#table-data').DataTable({
        layout: {
            topStart: {
                buttons: ['pageLength']
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
            url: '<?= base_url('/listjual/ajax') ?>',
            type: 'post',
            data: function(d) {
                d.start_date = $('#filter-start-date').val();
                d.end_date = $('#filter-end-date').val();
            }
        },
        columns: [{
                data: 'tgl',
                title: 'Tanggal',
                render: data => data ? new Date(data).toLocaleString('id-ID') : '-'
            },
            {
                data: 'jual_id',
                title: 'ID Jual'
            },
            {
                data: 'customer_nama',
                title: 'Customer',
                render: function(data, type, row) {
                    return `<div class="fw-semibold">${data || 'Pelanggan Umum'}</div><small class="text-muted">${row.cust_id || 'CUST-GENERAL'}</small>`;
                }
            },
            {
                data: 'jml_item',
                title: 'Jenis',
                className: 'text-center'
            },
            {
                data: 'total_qty',
                title: 'Qty',
                className: 'text-end',
                render: data => Number(data || 0).toLocaleString('id-ID')
            },
            {
                data: 'gross',
                title: 'Gross',
                className: 'text-end',
                render: data => 'Rp ' + formatMoneyValue(data || 0)
            },
            {
                data: 'netto',
                title: 'Netto',
                className: 'text-end',
                render: data => 'Rp ' + formatMoneyValue(data || 0)
            },
            {
                data: 'status_bayar',
                title: 'Status',
                className: 'text-center',
                render: function(data, type, row) {
                    const kredit = row.is_kredit === '1' ? 'KREDIT' : 'TUNAI';
                    let badge = '<span class="badge bg-success-subtle text-success">LUNAS</span>';
                    if (data === 'CICIL') {
                        badge = '<span class="badge bg-warning-subtle text-warning">CICIL</span>';
                    } else if (data === 'BELUM') {
                        badge = '<span class="badge bg-danger-subtle text-danger">BELUM</span>';
                    }
                    return `${badge}<div><small class="text-muted">${kredit}</small></div>`;
                }
            },
            {
                data: 'reprint_count',
                title: 'Reprint',
                className: 'text-center'
            },
            {
                title: 'Action',
                data: null,
                className: 'text-center',
                responsivePriority: 1,
                render: function(data) {
                    const editBtn = akses_menu?.akses_update === 'Y' ?
                        (data.can_edit ?
                            `<a class="dropdown-item" href="<?= base_url('/listjual/edit') ?>/${data.jual_id}"><i class="ti ti-pencil text-warning"></i> Edit</a>` :
                            `<a class="dropdown-item text-muted" href="javascript:void(0)" onclick="showLockedNotice()"><i class="ti ti-lock text-danger"></i> Edit Terkunci</a>`) :
                        '';
                    const deleteBtn = akses_menu?.akses_delete === 'Y' ?
                        (data.can_edit ?
                            `<a class="dropdown-item" href="javascript:void(0)" onclick="deleteSale('${data.jual_id}')"><i class="ti ti-trash text-danger"></i> Hapus</a>` :
                            `<a class="dropdown-item text-muted" href="javascript:void(0)" onclick="showLockedNotice()"><i class="ti ti-lock text-danger"></i> Hapus Terkunci</a>`) :
                        '';
                    const returBtn = akses_menu?.akses_create === 'Y' ?
                        `<a class="dropdown-item" href="<?= base_url('/returjual') ?>?jual_id=${encodeURIComponent(data.jual_id)}"><i class="ti ti-repeat text-primary"></i> Buat Retur</a>` :
                        '';
                    return `<span class="dropdown">
                        <button class="btn dropdown-toggle align-text-top btn-sm" data-bs-toggle="dropdown">Actions</button>
                        <div class="dropdown-menu dropdown-menu-end">
                            <a class="dropdown-item" href="javascript:void(0)" onclick="showDetail('${data.jual_id}')"><i class="ti ti-eye text-info"></i> Detail</a>
                            <a class="dropdown-item" href="<?= base_url('/listjual/reprint') ?>/${data.jual_id}" target="_blank"><i class="ti ti-printer text-success"></i> Reprint Struk</a>
                            ${returBtn}
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

    $('#btn-apply-filter').on('click', function() {
        table.ajax.reload();
    });

    $('#btn-reset-filter').on('click', function() {
        $('#filter-start-date').val(todayFilter);
        $('#filter-end-date').val(todayFilter);
        table.ajax.reload();
    });

    function showDetail(jualId) {
        $('#detail-content').html('<div class="text-center py-5 text-muted">Memuat detail...</div>');
        detailModal.show();
        $.getJSON(`<?= base_url('/listjual/show') ?>/${jualId}`, function(res) {
            if (res.tipe !== 'success') {
                $('#detail-content').html(`<div class="alert alert-danger mb-0">${res.data || 'Detail tidak ditemukan'}</div>`);
                return;
            }

            const data = res.data;
            const detailRows = (data.details || []).map((row, idx) => `
                <tr>
                    <td class="text-center">${idx + 1}</td>
                    <td>${row.nama_item || row.kode_item}<br><small class="text-muted">${row.kode_item || '-'}</small></td>
                    <td class="text-end">${Number(row.qty_jual || 0).toLocaleString('id-ID')}</td>
                    <td>${row.sat_id || '-'}</td>
                    <td class="text-end">Rp ${formatMoneyValue(row.price || 0)}</td>
                    <td class="text-end">Rp ${formatMoneyValue(row.diskon_item || 0)}</td>
                    <td class="text-end">Rp ${formatMoneyValue(row.netto || 0)}</td>
                </tr>
            `).join('');

            const payRows = (data.payments || []).length ? (data.payments || []).map((row, idx) => `
                <tr>
                    <td class="text-center">${idx + 1}</td>
                    <td>${row.cara_bayar || '-'}</td>
                    <td>${row.bank_nama || '-'}</td>
                    <td>${row.rekening_no || '-'}</td>
                    <td class="text-end">Rp ${formatMoneyValue(row.nominal_bayar || 0)}</td>
                </tr>
            `).join('') : '<tr><td colspan="5" class="text-center text-muted">Belum ada pembayaran</td></tr>';

            $('#detail-content').html(`
                <div class="row g-3 mb-3">
                    <div class="col-md-3"><div class="border rounded p-3 h-100"><small class="text-muted">ID Jual</small><div class="fw-semibold">${data.jual_id || '-'}</div></div></div>
                    <div class="col-md-3"><div class="border rounded p-3 h-100"><small class="text-muted">Tanggal</small><div class="fw-semibold">${data.tgl ? new Date(data.tgl).toLocaleString('id-ID') : '-'}</div></div></div>
                    <div class="col-md-3"><div class="border rounded p-3 h-100"><small class="text-muted">Customer</small><div class="fw-semibold">${data.customer_nama || 'Pelanggan Umum'}</div></div></div>
                    <div class="col-md-3"><div class="border rounded p-3 h-100"><small class="text-muted">Kasir</small><div class="fw-semibold">${data.updid || '-'}</div></div></div>
                </div>
                <div class="row g-3 mb-3">
                    <div class="col-md-3"><div class="border rounded p-3 h-100"><small class="text-muted">Gross</small><div class="fw-semibold">Rp ${formatMoneyValue(data.gross || 0)}</div></div></div>
                    <div class="col-md-3"><div class="border rounded p-3 h-100"><small class="text-muted">Diskon Nota</small><div class="fw-semibold">Rp ${formatMoneyValue(data.diskon_nota || 0)}</div></div></div>
                    <div class="col-md-3"><div class="border rounded p-3 h-100"><small class="text-muted">Netto</small><div class="fw-semibold">Rp ${formatMoneyValue(data.netto || 0)}</div></div></div>
                    <div class="col-md-3"><div class="border rounded p-3 h-100"><small class="text-muted">Reprint Count</small><div class="fw-semibold">${Number(data.reprint_count || 0).toLocaleString('id-ID')}</div></div></div>
                </div>
                <div class="table-responsive mb-3">
                    <table class="table table-sm table-bordered align-middle mb-0">
                        <thead>
                            <tr>
                                <th>No</th>
                                <th>Item</th>
                                <th>Qty</th>
                                <th>Satuan</th>
                                <th>Price</th>
                                <th>Diskon</th>
                                <th>Netto</th>
                            </tr>
                        </thead>
                        <tbody>${detailRows || '<tr><td colspan="7" class="text-center text-muted">Tidak ada detail</td></tr>'}</tbody>
                    </table>
                </div>
                <h6 class="mb-2">Pembayaran</h6>
                <div class="table-responsive">
                    <table class="table table-sm table-bordered align-middle mb-0">
                        <thead>
                            <tr>
                                <th>No</th>
                                <th>Metode</th>
                                <th>Bank/E-Wallet</th>
                                <th>Rekening</th>
                                <th>Nominal</th>
                            </tr>
                        </thead>
                        <tbody>${payRows}</tbody>
                    </table>
                </div>
            `);
        }).fail(function(xhr) {
            $('#detail-content').html(`<div class="alert alert-danger mb-0">${extractErrorMessage(xhr, 'Gagal memuat detail')}</div>`);
        });
    }

    function deleteSale(jualId) {
        Swal.fire({
            title: 'Hapus transaksi penjualan ini?',
            text: 'Stok barang akan dikembalikan dan poin customer akan disesuaikan ulang.',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonText: 'Ya, hapus',
            cancelButtonText: 'Batal'
        }).then((result) => {
            if (!result.isConfirmed) return;
            $.ajax({
                type: 'POST',
                url: '<?= base_url('/listjual') ?>',
                dataType: 'json',
                data: {
                    _method: 'DELETE',
                    jual_id: jualId
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

    function showLockedNotice() {
        toastr.error('Transaksi ini bukan tanggal hari ini sehingga tidak bisa diedit atau dihapus.');
    }
</script>
<?= $this->endSection('javascript') ?>
