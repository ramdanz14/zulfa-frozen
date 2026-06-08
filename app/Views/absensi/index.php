<?= $this->extend('layouts/base') ?>

<?= $this->section('content') ?>
<?php
/**
 * @var string $akses_menu
 */
?>
<div class="body-wrapper">
    <div class="container-fluid p-0">
        <div class="card bg-success-subtle shadow-none position-relative overflow-hidden mb-4">
            <div class="card-body px-4 py-3">
                <div class="row align-items-center">
                    <div class="col-9">
                        <h4 class="fw-semibold mb-2">Absensi Karyawan</h4>
                        <p class="mb-0"><span class="page-pretitle">Total</span> | Rekap absensi harian, pembayaran gaji fleksibel, dan slip gaji per tanggal bayar.</p>
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
            <div class="card-body p-2">
                <table id="table-absensi" class="table table-bordered table-hover table-striped table-sm align-middle">
                    <thead></thead>
                    <tbody><tr><td>No data to show</td></tr></tbody>
                </table>
            </div>
        </div>

        <div class="card">
            <div class="card-header bg-light">
                <h5 class="mb-0">Riwayat Pembayaran Gaji</h5>
            </div>
            <div class="card-body p-2">
                <table id="table-payment" class="table table-bordered table-hover table-striped table-sm align-middle">
                    <thead></thead>
                    <tbody><tr><td>No data to show</td></tr></tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="modal-detail" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-xl modal-dialog-scrollable">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Detail Absensi / Pembayaran</h5>
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

    function askTanggalAndGo() {
        Swal.fire({
            title: 'Pilih tanggal absensi',
            input: 'date',
            inputValue: new Date().toISOString().slice(0, 10),
            showCancelButton: true,
            confirmButtonText: 'Buka form',
            cancelButtonText: 'Batal'
        }).then((result) => {
            if (!result.isConfirmed || !result.value) return;
            window.location.href = `<?= base_url('/absensi/input') ?>/${result.value}`;
        });
    }

    DataTable.Buttons.defaults.dom.button.className = 'btn btn-primary';
    const tableAbsensi = $('#table-absensi').DataTable({
        layout: {
            topStart: {
                buttons: [{
                    text: '<i class="ti ti-plus"></i> Input Absensi',
                    action: function() {
                        if (akses_menu?.akses_create === 'Y') {
                            askTanggalAndGo();
                            return;
                        }
                        toastr.error('Anda tidak memiliki akses untuk ini!');
                    }
                }, {
                    text: '<i class="ti ti-cash"></i> Buat Gaji',
                    action: function() {
                        if (akses_menu?.akses_update === 'Y') {
                            window.location.href = '<?= base_url('/absensi/pay') ?>';
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
            url: '<?= base_url('/absensi/ajax') ?>',
            type: 'post'
        },
        columns: [{
                data: 'tanggal',
                title: 'Tanggal',
                render: data => data ? new Date(data).toLocaleDateString('id-ID') : '-'
            },
            {
                data: 'total_row',
                title: 'Total Karyawan',
                className: 'text-center'
            },
            {
                data: 'total_hadir',
                title: 'Hadir',
                className: 'text-center'
            },
            {
                data: 'total_paid',
                title: 'Sudah Dibayar',
                className: 'text-center'
            },
            {
                data: 'total_unpaid',
                title: 'Belum Dibayar',
                className: 'text-center'
            },
            {
                data: 'total_gaji',
                title: 'Total Gaji',
                className: 'text-end',
                render: data => 'Rp ' + formatMoneyValue(data || 0)
            },
            {
                data: null,
                title: 'Action',
                className: 'text-center',
                render: function(data) {
                    const actions = [
                        `<a class="dropdown-item" href="javascript:void(0)" onclick="showDateDetail('${data.tanggal}')"><i class="ti ti-eye text-info"></i> Detail</a>`
                    ];
                    if (akses_menu?.akses_update === 'Y') {
                        actions.push(`<a class="dropdown-item" href="<?= base_url('/absensi/input') ?>/${data.tanggal}"><i class="ti ti-pencil text-warning"></i> Edit</a>`);
                    }
                    if (akses_menu?.akses_delete === 'Y') {
                        actions.push(data.can_delete
                            ? `<a class="dropdown-item" href="javascript:void(0)" onclick="deleteTanggal('${data.tanggal}')"><i class="ti ti-trash text-danger"></i> Hapus</a>`
                            : `<a class="dropdown-item text-muted" href="javascript:void(0)" onclick="toastr.error('Absensi yang sudah dibayar tidak boleh dihapus')"><i class="ti ti-lock text-danger"></i> Hapus Terkunci</a>`);
                    }
                    return `<span class="dropdown">
                        <button class="btn dropdown-toggle align-text-top btn-sm" data-bs-toggle="dropdown">Actions</button>
                        <div class="dropdown-menu dropdown-menu-end">${actions.join('')}</div>
                    </span>`;
                }
            }
        ]
    });

    tableAbsensi.on('xhr.dt', function(e, settings, json) {
        $('.page-pretitle').text(`Total Data : ${json?.recordsTotal || 0}`);
    });

    const tablePayment = $('#table-payment').DataTable({
        responsive: true,
        lengthChange: false,
        autoWidth: false,
        processing: true,
        serverSide: true,
        ordering: false,
        ajax: {
            url: '<?= base_url('/absensi/ajax-payment') ?>',
            type: 'post'
        },
        columns: [{
                data: 'tanggal_bayar',
                title: 'Tanggal Bayar',
                render: data => data ? new Date(data).toLocaleDateString('id-ID') : '-'
            },
            {
                data: 'batch_id',
                title: 'Batch ID'
            },
            {
                data: 'periode_start',
                title: 'Periode',
                render: function(data, type, row) {
                    return `${new Date(data).toLocaleDateString('id-ID')} s/d ${new Date(row.periode_end).toLocaleDateString('id-ID')}`;
                }
            },
            {
                data: 'total_karyawan',
                title: 'Penerima',
                className: 'text-center'
            },
            {
                data: 'total_nominal',
                title: 'Total Bayar',
                className: 'text-end',
                render: data => 'Rp ' + formatMoneyValue(data || 0)
            },
            {
                data: null,
                title: 'Action',
                className: 'text-center',
                render: function(data) {
                    return `<span class="dropdown">
                        <button class="btn dropdown-toggle align-text-top btn-sm" data-bs-toggle="dropdown">Actions</button>
                        <div class="dropdown-menu dropdown-menu-end">
                            <a class="dropdown-item" href="javascript:void(0)" onclick="showPaymentDetail('${data.batch_id}')"><i class="ti ti-eye text-info"></i> Detail / Slip</a>
                        </div>
                    </span>`;
                }
            }
        ]
    });

    function showDateDetail(tanggal) {
        $('#detail-content').html('<div class="text-center py-5 text-muted">Memuat detail...</div>');
        detailModal.show();
        $.getJSON(`<?= base_url('/absensi/show') ?>/${tanggal}`, function(res) {
            if (res.tipe !== 'success') {
                $('#detail-content').html(`<div class="alert alert-danger mb-0">${res.data || 'Data tidak ditemukan'}</div>`);
                return;
            }
            const data = res.data;
            const rows = (data.details || []).map((row, idx) => `
                <tr>
                    <td class="text-center">${idx + 1}</td>
                    <td>${row.fullname}<br><small class="text-muted">${row.karyawan_id}</small></td>
                    <td>${row.status_absensi}</td>
                    <td>${row.kerja_toko_nama || row.toko_id}</td>
                    <td class="text-end">Rp ${formatMoneyValue(row.nominal_gaji || 0)}</td>
                    <td>${row.is_paid === 'Y' ? '<span class="badge bg-success-subtle text-success">SUDAH</span>' : '<span class="badge bg-warning-subtle text-warning">BELUM</span>'}</td>
                    <td>${row.keterangan || '-'}</td>
                </tr>
            `).join('');

            $('#detail-content').html(`
                <div class="row g-3 mb-3">
                    <div class="col-md-3"><div class="border rounded p-3 h-100"><small class="text-muted">Tanggal</small><div class="fw-semibold">${new Date(data.tanggal).toLocaleDateString('id-ID')}</div></div></div>
                    <div class="col-md-3"><div class="border rounded p-3 h-100"><small class="text-muted">Hadir</small><div class="fw-semibold">${Number(data.total_hadir || 0).toLocaleString('id-ID')}</div></div></div>
                    <div class="col-md-3"><div class="border rounded p-3 h-100"><small class="text-muted">Sudah Dibayar</small><div class="fw-semibold">${Number(data.total_paid || 0).toLocaleString('id-ID')}</div></div></div>
                    <div class="col-md-3"><div class="border rounded p-3 h-100"><small class="text-muted">Total Gaji</small><div class="fw-semibold">Rp ${formatMoneyValue(data.total_gaji || 0)}</div></div></div>
                </div>
                <div class="table-responsive">
                    <table class="table table-sm table-bordered align-middle mb-0">
                        <thead>
                            <tr>
                                <th>No</th>
                                <th>Karyawan</th>
                                <th>Status</th>
                                <th>Lokasi</th>
                                <th>Gaji</th>
                                <th>Bayar</th>
                                <th>Keterangan</th>
                            </tr>
                        </thead>
                        <tbody>${rows}</tbody>
                    </table>
                </div>
            `);
        }).fail(function(xhr) {
            $('#detail-content').html(`<div class="alert alert-danger mb-0">${extractErrorMessage(xhr, 'Gagal memuat detail absensi')}</div>`);
        });
    }

    function showPaymentDetail(batchId) {
        $('#detail-content').html('<div class="text-center py-5 text-muted">Memuat detail...</div>');
        detailModal.show();
        $.getJSON(`<?= base_url('/absensi/show-payment') ?>/${batchId}`, function(res) {
            if (res.tipe !== 'success') {
                $('#detail-content').html(`<div class="alert alert-danger mb-0">${res.data || 'Data tidak ditemukan'}</div>`);
                return;
            }
            const data = res.data;
            const grouped = {};
            (data.details || []).forEach((row) => {
                const key = `${row.karyawan_id}`;
                if (!grouped[key]) {
                    grouped[key] = {
                        karyawan_id: row.karyawan_id,
                        fullname: row.fullname,
                        total_nominal: 0,
                        tanggal_list: [],
                        toko_list: []
                    };
                }
                grouped[key].total_nominal += Number(row.nominal_gaji || 0);
                grouped[key].tanggal_list.push(row.tanggal);
                grouped[key].toko_list.push(row.toko_nama || row.toko_id);
            });

            const employeeCards = Object.values(grouped).map((row) => `
                <div class="border rounded p-3 mb-2">
                    <div class="d-flex justify-content-between gap-2 flex-wrap">
                        <div>
                            <div class="fw-semibold">${row.fullname}</div>
                            <small class="text-muted">${row.karyawan_id} | ${Array.from(new Set(row.toko_list)).join(', ')}</small>
                            <div class="small text-muted mt-1">Tanggal: ${Array.from(new Set(row.tanggal_list)).map((date) => new Date(date).toLocaleDateString('id-ID')).join(', ')}</div>
                        </div>
                        <div class="text-end">
                            <div class="fw-semibold">Rp ${formatMoneyValue(row.total_nominal || 0)}</div>
                            <a href="<?= base_url('/absensi/struk') ?>/${data.batch_id}/${row.karyawan_id}" target="_blank" class="btn btn-sm btn-success mt-1"><i class="ti ti-printer"></i> Cetak Slip</a>
                        </div>
                    </div>
                </div>
            `).join('');

            $('#detail-content').html(`
                <div class="row g-3 mb-3">
                    <div class="col-md-3"><div class="border rounded p-3 h-100"><small class="text-muted">Batch</small><div class="fw-semibold">${data.batch_id}</div></div></div>
                    <div class="col-md-3"><div class="border rounded p-3 h-100"><small class="text-muted">Tanggal Bayar</small><div class="fw-semibold">${new Date(data.tanggal_bayar).toLocaleDateString('id-ID')}</div></div></div>
                    <div class="col-md-3"><div class="border rounded p-3 h-100"><small class="text-muted">Periode</small><div class="fw-semibold">${new Date(data.periode_start).toLocaleDateString('id-ID')} - ${new Date(data.periode_end).toLocaleDateString('id-ID')}</div></div></div>
                    <div class="col-md-3"><div class="border rounded p-3 h-100"><small class="text-muted">Total Bayar</small><div class="fw-semibold">Rp ${formatMoneyValue(data.total_nominal || 0)}</div></div></div>
                </div>
                ${employeeCards}
            `);
        }).fail(function(xhr) {
            $('#detail-content').html(`<div class="alert alert-danger mb-0">${extractErrorMessage(xhr, 'Gagal memuat detail pembayaran')}</div>`);
        });
    }

    function deleteTanggal(tanggal) {
        Swal.fire({
            title: 'Hapus absensi tanggal ini?',
            text: 'Semua baris absensi pada tanggal tersebut akan dihapus.',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonText: 'Ya, hapus',
            cancelButtonText: 'Batal'
        }).then((result) => {
            if (!result.isConfirmed) return;
            $.ajax({
                type: 'DELETE',
                url: '<?= base_url('/absensi') ?>',
                dataType: 'json',
                data: { tanggal },
                success: function(res) {
                    if (res.tipe === 'success') {
                        toastr.success(res.data || 'Berhasil');
                        tableAbsensi.ajax.reload(null, false);
                        return;
                    }
                    toastr.error(res.data || 'Gagal');
                },
                error: function(xhr) {
                    toastr.error(extractErrorMessage(xhr, 'Gagal menghapus absensi'));
                }
            });
        });
    }
</script>
<?= $this->endSection('javascript') ?>
