<?= $this->extend('layouts/base') ?>

<?= $this->section('content') ?>
<?php
/**
 * @var string $akses_menu
 */
$storeContext = $context ?? [];
$isGudang = (bool) ($storeContext['is_gudang'] ?? false);
$storeName = $storeContext['toko']['toko_nama'] ?? session('toko_id');
?>
<div class="body-wrapper">
    <div class="container-fluid p-0">
        <div class="card bg-primary-subtle shadow-none position-relative overflow-hidden mb-4">
            <div class="card-body px-4 py-3">
                <div class="row align-items-center">
                    <div class="col-lg-9">
                        <h4 class="fw-semibold mb-2">Transfer Antar Toko - <?= $isGudang ? 'KIRIM' : 'TERIMA' ?></h4>
                        <p class="mb-0"><?= $isGudang ? 'Gudang membaca PO cabang, menyiapkan draft kirim, lalu final kirim sebagai penjualan kredit ke toko tujuan.' : 'Cabang menerima transfer kirim dari gudang, cek fisik seluruh item, lalu approve sebagai pembelian kredit atau reject untuk membatalkan penjualan gudang.' ?></p>
                        <small class="text-muted d-block mt-1">Toko aktif: <?= esc($storeName) ?>.</small>
                    </div>
                    <div class="col-lg-3 text-lg-end mt-3 mt-lg-0">
                        <span class="badge bg-light text-dark border px-3 py-2">Mode <?= $isGudang ? 'Gudang Kirim' : 'Cabang Terima' ?></span>
                    </div>
                </div>
            </div>
        </div>

        <?php if ($isGudang) : ?>
            <div class="card mb-3">
                <div class="card-header bg-light">
                    <h5 class="mb-0">PO Cabang Belum Dipenuhi</h5>
                </div>
                <div class="card-body p-2">
                    <table id="table-po" class="table table-bordered table-hover table-striped table-sm align-middle">
                        <thead></thead>
                        <tbody>
                            <tr>
                                <td>No data to show</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        <?php endif; ?>

        <div class="card">
            <div class="card-header bg-light">
                <h5 class="mb-0"><?= $isGudang ? 'Draft / Riwayat Kirim Gudang' : 'Transfer Masuk Cabang' ?></h5>
            </div>
            <div class="card-body p-2">
                <table id="table-transfer" class="table table-bordered table-hover table-striped table-sm align-middle">
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
                <h5 class="modal-title">Detail Transfer Antar Toko</h5>
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
    const isGudang = <?= $isGudang ? 'true' : 'false' ?>;
    const detailModal = new bootstrap.Modal(document.getElementById('modal-detail'));

    <?php if (session()->getFlashdata('error')) : ?>
        toastr.error("<?= session()->getFlashdata('error') ?>");
    <?php endif; ?>

    if (isGudang) {
        $('#table-po').DataTable({
            responsive: true,
            autoWidth: false,
            processing: true,
            serverSide: true,
            ordering: false,
            ajax: {
                url: '<?= base_url('/transfer/ajax-po') ?>',
                type: 'post'
            },
            columns: [{
                    data: 'tanggal',
                    title: 'Tanggal PO',
                    render: data => data ? new Date(data).toLocaleDateString('id-ID') : '-'
                },
                {
                    data: 'beli_id',
                    title: 'PO Cabang'
                },
                {
                    data: 'tujuan_toko_nama',
                    title: 'Cabang Tujuan',
                    render: (data, type, row) => `<div class="fw-semibold">${data || row.tujuan_toko_id}</div><small class="text-muted">${row.tujuan_toko_id} | ${row.invoice || '-'}</small>`
                },
                {
                    data: 'jml_item',
                    title: 'Item',
                    className: 'text-center'
                },
                {
                    data: 'total_gross',
                    title: 'Nilai PO',
                    className: 'text-end',
                    render: data => 'Rp ' + formatMoneyValue(data)
                },
                {
                    data: null,
                    title: 'Action',
                    className: 'text-center',
                    render: function(data) {
                        if (akses_menu?.akses_create !== 'Y') {
                            return '<span class="text-muted">Tidak ada akses</span>';
                        }
                        return `<a href="<?= base_url('/transfer/add') ?>/${data.tujuan_toko_id}/${data.beli_id}" class="btn btn-sm btn-primary">Buat Draft Kirim</a>`;
                    }
                }
            ]
        });
    }

    const transferTable = $('#table-transfer').DataTable({
        responsive: true,
        autoWidth: false,
        processing: true,
        serverSide: true,
        ordering: false,
        ajax: {
            url: '<?= base_url('/transfer/ajax') ?>',
            type: 'post'
        },
        columns: [{
                data: 'tanggal_transfer',
                title: 'Tanggal Draft',
                render: data => data ? new Date(data).toLocaleDateString('id-ID') : '-'
            },
            {
                data: 'transfer_id',
                title: 'Transfer ID',
                render: (data, type, row) => `<div class="fw-semibold">${data}</div><small class="text-muted">PO ${row.po_beli_id || '-'}</small>`
            },
            {
                data: null,
                title: isGudang ? 'Cabang Tujuan' : 'Gudang Asal',
                render: function(data) {
                    const name = isGudang ? data.tujuan_toko_nama : data.gudang_toko_nama;
                    const code = isGudang ? data.tujuan_toko_id : data.gudang_toko_id;
                    return `<div class="fw-semibold">${name || code}</div><small class="text-muted">${code}</small>`;
                }
            },
            {
                data: 'jml_item',
                title: 'Item',
                className: 'text-center'
            },
            {
                data: 'status_transfer',
                title: 'Status',
                className: 'text-center',
                render: function(data) {
                    if (data === 'APPROVED') return '<span class="badge bg-success-subtle text-success">APPROVED</span>';
                    if (data === 'REJECTED') return '<span class="badge bg-danger-subtle text-danger">REJECTED</span>';
                    if (data === 'KIRIM') return '<span class="badge bg-info-subtle text-info">KIRIM</span>';
                    return '<span class="badge bg-warning-subtle text-warning">DRAFT</span>';
                }
            },
            {
                data: null,
                title: 'Ref',
                render: data => `
                    <small class="d-block text-muted">Jual: ${data.jual_id || '-'}</small>
                    <small class="d-block text-muted">Beli: ${data.beli_id || '-'}</small>
                `
            },
            {
                data: null,
                title: 'Action',
                className: 'text-center',
                render: function(data) {
                    const actions = [`<a class="dropdown-item" href="javascript:void(0)" onclick="showDetail('${data.transfer_id}')"><i class="ti ti-eye text-info"></i> Detail</a>`];

                    if (isGudang && data.status_transfer === 'DRAFT') {
                        if (akses_menu?.akses_update === 'Y') {
                            actions.push(`<a class="dropdown-item" href="<?= base_url('/transfer/edit') ?>/${data.transfer_id}"><i class="ti ti-pencil text-warning"></i> Edit Draft</a>`);
                            actions.push(`<a class="dropdown-item" href="javascript:void(0)" onclick="sendTransfer('${data.transfer_id}')"><i class="ti ti-truck-delivery text-primary"></i> Kirim</a>`);
                        }
                    }

                    if (!isGudang && data.status_transfer === 'KIRIM') {
                        if (akses_menu?.akses_update === 'Y') {
                            actions.push(`<a class="dropdown-item" href="javascript:void(0)" onclick="showDetail('${data.transfer_id}', true)"><i class="ti ti-checklist text-success"></i> Cek & Approve</a>`);
                            actions.push(`<a class="dropdown-item" href="javascript:void(0)" onclick="rejectTransfer('${data.transfer_id}')"><i class="ti ti-x text-danger"></i> Reject</a>`);
                        }
                    }

                    return `<span class="dropdown">
                        <button class="btn dropdown-toggle align-text-top btn-sm" data-bs-toggle="dropdown">Actions</button>
                        <div class="dropdown-menu dropdown-menu-end">${actions.join('')}</div>
                    </span>`;
                }
            }
        ]
    });

    function showDetail(transferId, enableApprove = false) {
        $('#detail-content').html('<div class="text-center py-5 text-muted">Memuat detail...</div>');
        detailModal.show();

        $.getJSON(`<?= base_url('/transfer/show') ?>/${transferId}`, function(res) {
            if (res.tipe !== 'success') {
                $('#detail-content').html(`<div class="alert alert-danger mb-0">${res.data || 'Detail transfer tidak ditemukan'}</div>`);
                return;
            }

            const data = res.data;
            const canApprove = !isGudang && enableApprove && data.status_transfer === 'KIRIM';
            const detailRows = (data.details || []).map((row, idx) => `
                <tr>
                    <td class="text-center">${idx + 1}</td>
                    ${canApprove ? `<td class="text-center"><input type="checkbox" class="form-check-input approve-check" value="${row.seq_no}" ${Number(row.qty_kirim || 0) <= 0 ? 'checked disabled' : ''}></td>` : ''}
                    <td>${row.nama_item || row.kode_item}<br><small class="text-muted">${row.kode_item}</small></td>
                    <td class="text-end">${Number(row.qty_po || 0).toLocaleString('id-ID')}</td>
                    <td class="text-end">${Number(row.qty_kirim || 0).toLocaleString('id-ID')}</td>
                    <td>${row.sat_id}</td>
                    <td class="text-end">Rp ${formatMoneyValue(row.harga_pokok || 0)}</td>
                    <td class="text-end">Rp ${formatMoneyValue(row.harga_jual || 0)}</td>
                    <td class="text-end">Rp ${formatMoneyValue(row.gross || 0)}</td>
                </tr>
            `).join('');

            $('#detail-content').html(`
                <div class="row g-3 mb-3">
                    <div class="col-md-3"><div class="border rounded p-3 h-100"><small class="text-muted">Transfer ID</small><div class="fw-semibold">${data.transfer_id}</div></div></div>
                    <div class="col-md-3"><div class="border rounded p-3 h-100"><small class="text-muted">PO Cabang</small><div class="fw-semibold">${data.po_beli_id || '-'}</div></div></div>
                    <div class="col-md-3"><div class="border rounded p-3 h-100"><small class="text-muted">Gudang</small><div class="fw-semibold">${data.gudang_toko_nama || data.gudang_toko_id}</div></div></div>
                    <div class="col-md-3"><div class="border rounded p-3 h-100"><small class="text-muted">Cabang</small><div class="fw-semibold">${data.tujuan_toko_nama || data.tujuan_toko_id}</div></div></div>
                </div>
                <div class="row g-3 mb-3">
                    <div class="col-md-3"><div class="border rounded p-3 h-100"><small class="text-muted">Status</small><div class="fw-semibold">${data.status_transfer}</div></div></div>
                    <div class="col-md-3"><div class="border rounded p-3 h-100"><small class="text-muted">Jual Gudang</small><div class="fw-semibold">${data.jual_id || '-'}</div></div></div>
                    <div class="col-md-3"><div class="border rounded p-3 h-100"><small class="text-muted">Beli Cabang</small><div class="fw-semibold">${data.beli_id || '-'}</div></div></div>
                    <div class="col-md-3"><div class="border rounded p-3 h-100"><small class="text-muted">Keterangan</small><div class="fw-semibold">${data.keterangan || '-'}</div></div></div>
                </div>
                ${canApprove ? `<div class="alert alert-warning border-warning-subtle">Centang semua item yang fisiknya sudah diperiksa. Approve akan menambah stok cabang, update HPP, dan menambah hutang ke gudang. Reject akan membatalkan penjualan di sisi gudang.</div>` : ''}
                <div class="table-responsive">
                    <table class="table table-sm table-bordered align-middle mb-0">
                        <thead>
                            <tr>
                                <th>No</th>
                                ${canApprove ? '<th>Cek</th>' : ''}
                                <th>Item</th>
                                <th>Qty PO</th>
                                <th>Qty Kirim</th>
                                <th>Satuan</th>
                                <th>HPP Gudang</th>
                                <th>Harga Transfer</th>
                                <th>Gross</th>
                            </tr>
                        </thead>
                        <tbody>${detailRows}</tbody>
                    </table>
                </div>
                ${canApprove ? `
                    <div class="d-flex justify-content-end gap-2 mt-3">
                        <button type="button" class="btn btn-outline-danger" onclick="rejectTransfer('${data.transfer_id}')">Reject</button>
                        <button type="button" class="btn btn-success" onclick="approveTransfer('${data.transfer_id}')">Approve Transfer</button>
                    </div>
                ` : ''}
            `);
        }).fail(function(xhr) {
            $('#detail-content').html(`<div class="alert alert-danger mb-0">${extractErrorMessage(xhr, 'Gagal memuat detail transfer')}</div>`);
        });
    }

    function sendTransfer(transferId) {
        Swal.fire({
            title: 'Kirim transfer ini?',
            text: 'Stok gudang akan langsung berkurang dan transaksi dicatat sebagai penjualan kredit ke toko cabang.',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonText: 'Ya, kirim',
            cancelButtonText: 'Batal'
        }).then((result) => {
            if (!result.isConfirmed) return;
            $.post(`<?= base_url('/transfer/send') ?>/${transferId}`, function(res) {
                if (res.tipe === 'success') {
                    toastr.success(res.data || 'Transfer berhasil dikirim');
                    transferTable.ajax.reload(null, false);
                    $('#table-po').DataTable().ajax.reload(null, false);
                    return;
                }
                toastr.error(res.data || 'Gagal mengirim transfer');
            }, 'json').fail(function(xhr) {
                toastr.error(extractErrorMessage(xhr, 'Gagal mengirim transfer'));
            });
        });
    }

    function approveTransfer(transferId) {
        const checkedSeqs = $('.approve-check:checked').map(function() {
            return Number($(this).val());
        }).get();
        const allRequired = $('.approve-check').filter(':not(:disabled)').length;
        if (checkedSeqs.length !== allRequired) {
            toastr.error('Semua item harus dicentang sebelum approve');
            return;
        }

        Swal.fire({
            title: 'Approve transfer ini?',
            text: 'Approve akan menambah stok cabang, update HPP cabang, dan menambah hutang pembelian ke gudang.',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonText: 'Ya, approve',
            cancelButtonText: 'Batal'
        }).then((result) => {
            if (!result.isConfirmed) return;
            $.post(`<?= base_url('/transfer/approve') ?>/${transferId}`, {
                checked_seqs: JSON.stringify(checkedSeqs)
            }, function(res) {
                if (res.tipe === 'success') {
                    toastr.success(res.data || 'Transfer berhasil di-approve');
                    detailModal.hide();
                    transferTable.ajax.reload(null, false);
                    return;
                }
                toastr.error(res.data || 'Gagal approve transfer');
            }, 'json').fail(function(xhr) {
                toastr.error(extractErrorMessage(xhr, 'Gagal approve transfer'));
            });
        });
    }

    function rejectTransfer(transferId) {
        Swal.fire({
            title: 'Reject transfer ini?',
            text: 'Reject akan membatalkan penjualan transfer di sisi gudang dan mengembalikan stok gudang.',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonText: 'Ya, reject',
            cancelButtonText: 'Batal'
        }).then((result) => {
            if (!result.isConfirmed) return;
            $.post(`<?= base_url('/transfer/reject') ?>/${transferId}`, function(res) {
                if (res.tipe === 'success') {
                    toastr.success(res.data || 'Transfer berhasil direject');
                    detailModal.hide();
                    transferTable.ajax.reload(null, false);
                    return;
                }
                toastr.error(res.data || 'Gagal reject transfer');
            }, 'json').fail(function(xhr) {
                toastr.error(extractErrorMessage(xhr, 'Gagal reject transfer'));
            });
        });
    }
</script>
<?= $this->endSection('javascript') ?>