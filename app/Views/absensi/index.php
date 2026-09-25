<?= $this->extend('layouts/base') ?>

<?= $this->section('content') ?>
<?php
/**
 * @var string $akses_menu
 */
$aksesMenuData = json_decode((string) ($akses_menu ?? '{}'), true) ?: [];
$canCreate = ($aksesMenuData['akses_create'] ?? '') === 'Y';
$canUpdate = ($aksesMenuData['akses_update'] ?? '') === 'Y';
?>
<style>
    /* Ergonomic mobile styling untuk Absensi */
    .metric-card-summary {
        border: 1px solid #e2e8f0;
        border-radius: 10px;
        background: #ffffff;
        padding: 0.75rem 1rem;
        height: 100%;
        display: flex;
        flex-direction: column;
        justify-content: center;
        transition: transform 0.15s ease, box-shadow 0.15s ease;
    }

    .metric-card-summary .metric-title {
        font-size: 0.72rem;
        text-transform: uppercase;
        letter-spacing: 0.5px;
        color: #64748b;
        font-weight: 600;
        margin-bottom: 0.25rem;
    }

    .metric-card-summary .metric-value {
        font-size: 1.15rem;
        font-weight: 700;
        line-height: 1.25;
    }

    /* CardView styling untuk data absensi */
    .absensi-summary-card {
        background: #ffffff;
        border: 1px solid #e2e8f0;
        border-radius: 10px;
        padding: 0.85rem 1rem;
        transition: transform 0.15s ease, box-shadow 0.15s ease;
    }

    .absensi-summary-card:hover {
        border-color: #cbd5e1;
        box-shadow: 0 4px 10px rgba(0, 0, 0, 0.04);
    }

    @media (max-width: 767.98px) {
        .metric-card-summary {
            padding: 0.65rem 0.75rem;
        }

        .metric-card-summary .metric-value {
            font-size: 0.98rem;
        }

        .metric-card-summary .metric-title {
            font-size: 0.68rem;
        }

        .btn-touch-target {
            min-height: 44px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
        }
    }
</style>

<div class="body-wrapper">
    <div class="container-fluid p-2 p-md-3">
        <!-- Header Page -->
        <div class="card bg-success-subtle shadow-none position-relative overflow-hidden mb-3">
            <div class="card-body px-3 py-3">
                <div class="d-flex flex-column flex-md-row align-items-start align-items-md-center justify-content-between gap-2">
                    <div>
                        <h4 class="fw-bold mb-1">Absensi Karyawan</h4>
                        <p class="mb-0 small text-muted"><span class="page-pretitle">Total Data</span> &bull; Rekap absensi harian, pembayaran gaji, dan slip per batch.</p>
                    </div>
                    <div class="d-flex gap-2 flex-wrap w-100 w-md-auto">
                        <?php if ($canCreate) : ?>
                            <button type="button" class="btn btn-primary btn-sm flex-fill btn-touch-target" onclick="askTanggalAndGo()">
                                <i class="ti ti-plus me-1"></i> Input Absensi
                            </button>
                        <?php endif; ?>
                        <?php if ($canUpdate) : ?>
                            <a href="<?= base_url('/absensi/pay') ?>" class="btn btn-success btn-sm flex-fill btn-touch-target">
                                <i class="ti ti-cash me-1"></i> Buat Gaji
                            </a>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>

        <!-- Tabel Rekap Harian Absensi -->
        <div class="card border-0 shadow-sm mb-3">
            <div class="card-header bg-white border-bottom py-2 px-3 d-flex align-items-center justify-content-between">
                <div class="d-flex align-items-center gap-2">
                    <i class="ti ti-calendar-event fs-5 text-primary"></i>
                    <h6 class="mb-0 fw-bold">Rekap Absensi Harian</h6>
                </div>
            </div>
            <div class="card-body p-2 p-md-3">
                <table id="table-absensi" class="table table-bordered table-hover table-striped table-sm align-middle w-100 mb-0">
                    <thead></thead>
                    <tbody>
                        <tr>
                            <td>Memuat data...</td>
                        </tr>
                    </tbody>
                </table>

                <!-- CardView Template untuk Rekap Absensi di Mobile (< 768px) -->
                <template id="card-absensi-template">
                    <div class="absensi-summary-card mb-2">
                        <!-- Baris 1: Tanggal & Aksi -->
                        <div class="d-flex justify-content-between align-items-center mb-2 pb-2 border-bottom">
                            <div class="d-flex align-items-center gap-1">
                                <i class="ti ti-calendar text-primary"></i>
                                <span class="fw-bold text-dark fs-6" data-dtcv-field="0"></span>
                            </div>
                            <div class="shrink-0" data-dtcv-field="6"></div>
                        </div>

                        <!-- Baris 2: Total Karyawan & Status Kehadiran -->
                        <div class="d-flex justify-content-between align-items-center mb-2">
                            <div>
                                <span class="text-muted small"><i class="ti ti-users me-1"></i>Total:</span>
                                <span class="fw-semibold text-dark small" data-dtcv-field="1"></span> org
                            </div>
                            <div>
                                <span class="badge bg-success-subtle text-success small px-2 py-1">
                                    <i class="ti ti-check me-1"></i><span data-dtcv-field="2"></span> Hadir
                                </span>
                            </div>
                        </div>

                        <!-- Baris 3: Status Pembayaran (Sudah vs Belum) -->
                        <div class="p-2 bg-light-subtle rounded border mb-2">
                            <div class="d-flex justify-content-between align-items-center mb-1">
                                <span class="text-muted small" style="font-size: 0.75rem;"><i class="ti ti-circle-check text-success me-1"></i>Sudah Dibayar:</span>
                                <span class="fw-semibold text-success small"><span data-dtcv-field="3"></span> org</span>
                            </div>
                            <div class="d-flex justify-content-between align-items-center">
                                <span class="text-muted small" style="font-size: 0.75rem;"><i class="ti ti-clock text-warning me-1"></i>Belum Dibayar:</span>
                                <span class="fw-semibold text-warning small"><span data-dtcv-field="4"></span> org</span>
                            </div>
                        </div>

                        <!-- Baris 4: Total Gaji -->
                        <div class="d-flex justify-content-between align-items-center pt-1 border-top">
                            <span class="text-muted small text-uppercase" style="font-size: 0.72rem; font-weight: 600;">Total Gaji Hari Ini</span>
                            <span class="fw-bold text-dark fs-6" data-dtcv-field="5"></span>
                        </div>
                    </div>
                </template>
            </div>
        </div>

        <!-- Tabel Riwayat Pembayaran Gaji -->
        <div class="card border-0 shadow-sm mb-3">
            <div class="card-header bg-white border-bottom py-2 px-3 d-flex align-items-center justify-content-between">
                <div class="d-flex align-items-center gap-2">
                    <i class="ti ti-receipt-2 fs-5 text-success"></i>
                    <h6 class="mb-0 fw-bold">Riwayat Pembayaran Gaji</h6>
                </div>
            </div>
            <div class="card-body p-2 p-md-3">
                <table id="table-payment" class="table table-bordered table-hover table-striped table-sm align-middle w-100 mb-0">
                    <thead></thead>
                    <tbody>
                        <tr>
                            <td>Memuat data...</td>
                        </tr>
                    </tbody>
                </table>

                <!-- CardView Template untuk Riwayat Pembayaran di Mobile (< 768px) -->
                <template id="card-payment-template">
                    <div class="absensi-summary-card mb-2">
                        <!-- Baris 1: Batch & Aksi -->
                        <div class="d-flex justify-content-between align-items-start mb-2 pb-2 border-bottom">
                            <div style="min-width: 0; flex: 1;">
                                <span class="badge bg-primary-subtle text-primary border text-monospace mb-1" data-dtcv-field="1"></span>
                                <div class="text-muted small">
                                    <i class="ti ti-calendar me-1"></i>Bayar: <span class="fw-semibold text-dark" data-dtcv-field="0"></span>
                                </div>
                            </div>
                            <div class="shrink-0 ms-2" data-dtcv-field="5"></div>
                        </div>

                        <!-- Baris 2: Periode Kerja & Penerima -->
                        <div class="mb-2">
                            <div class="text-muted small" style="font-size: 0.75rem;">Periode Kerja:</div>
                            <div class="fw-semibold text-dark small" data-dtcv-field="2"></div>
                        </div>

                        <div class="d-flex justify-content-between align-items-center p-2 bg-light-subtle rounded border mb-2">
                            <span class="text-muted small"><i class="ti ti-users me-1 text-primary"></i>Penerima Gaji:</span>
                            <span class="fw-bold text-dark small"><span data-dtcv-field="3"></span> orang</span>
                        </div>

                        <!-- Baris 3: Total Nominal Bayar -->
                        <div class="d-flex justify-content-between align-items-center pt-1 border-top">
                            <span class="text-muted small text-uppercase" style="font-size: 0.72rem; font-weight: 600;">Total Dibayarkan</span>
                            <span class="fw-bold text-success fs-6" data-dtcv-field="4"></span>
                        </div>
                    </div>
                </template>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="modal-detail" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-xl modal-dialog-scrollable">
        <div class="modal-content border-0 shadow">
            <div class="modal-header py-2 px-3 bg-light">
                <h6 class="modal-title fw-semibold"><i class="ti ti-eye text-primary me-2"></i>Detail Absensi / Pembayaran</h6>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body p-2 p-md-3" id="detail-content"></div>
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

    DataTable.Buttons.defaults.dom.button.className = 'btn btn-primary btn-sm';
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
                    className: 'btn btn-success btn-sm',
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
        cardView: {
            enable: true,
            breakpoint: 768,
            template: '#card-absensi-template',
            gridClass: 'col-12 col-sm-6 mb-2',
            onCardRender: function($card, data) {
                if (Number(data.total_unpaid || 0) > 0) {
                    $card.addClass('border-start border-warning border-3');
                } else {
                    $card.addClass('border-start border-success border-3');
                }
            }
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
                        `<a class="dropdown-item py-2" href="javascript:void(0)" onclick="showDateDetail('${data.tanggal}')"><i class="ti ti-eye text-info me-1"></i> Detail</a>`
                    ];
                    if (akses_menu?.akses_update === 'Y') {
                        actions.push(`<a class="dropdown-item py-2" href="<?= base_url('/absensi/input') ?>/${data.tanggal}"><i class="ti ti-pencil text-warning me-1"></i> Edit</a>`);
                    }
                    if (akses_menu?.akses_delete === 'Y') {
                        actions.push(data.can_delete ?
                            `<a class="dropdown-item py-2" href="javascript:void(0)" onclick="deleteTanggal('${data.tanggal}')"><i class="ti ti-trash text-danger me-1"></i> Hapus</a>` :
                            `<a class="dropdown-item py-2 text-muted" href="javascript:void(0)" onclick="toastr.error('Absensi yang sudah dibayar tidak boleh dihapus')"><i class="ti ti-lock text-danger me-1"></i> Hapus Terkunci</a>`);
                    }
                    return `<span class="dropdown">
                        <button class="btn dropdown-toggle align-text-top btn-sm btn-touch-target" data-bs-toggle="dropdown">Aksi</button>
                        <div class="dropdown-menu dropdown-menu-end shadow">${actions.join('')}</div>
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
        cardView: {
            enable: true,
            breakpoint: 768,
            template: '#card-payment-template',
            gridClass: 'col-12 col-sm-6 mb-2',
            onCardRender: function($card, data) {
                $card.addClass('border-start border-primary border-3');
            }
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
                        <button class="btn dropdown-toggle align-text-top btn-sm btn-touch-target" data-bs-toggle="dropdown">Aksi</button>
                        <div class="dropdown-menu dropdown-menu-end shadow">
                            <a class="dropdown-item py-2" href="javascript:void(0)" onclick="showPaymentDetail('${data.batch_id}')"><i class="ti ti-eye text-info me-1"></i> Detail / Slip</a>
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
                <div class="border rounded p-2 mb-2 bg-white">
                    <div class="d-flex justify-content-between align-items-start mb-1">
                        <div>
                            <span class="badge bg-light text-secondary border me-1">${idx + 1}</span>
                            <span class="fw-bold text-dark">${row.fullname}</span>
                            <small class="text-muted d-block">${row.karyawan_id} &bull; ${row.kerja_toko_nama || row.toko_id}</small>
                        </div>
                        <div class="text-end">
                            <span class="badge ${row.status_absensi === 'HADIR' ? 'bg-success-subtle text-success' : 'bg-warning-subtle text-warning'}">${row.status_absensi}</span>
                            <div class="fw-bold text-dark mt-1">Rp ${formatMoneyValue(row.nominal_gaji || 0)}</div>
                        </div>
                    </div>
                    <div class="d-flex justify-content-between align-items-center pt-1 border-top small text-muted">
                        <span>Status Bayar: ${row.is_paid === 'Y' ? '<span class="badge bg-success-subtle text-success">SUDAH</span>' : '<span class="badge bg-warning-subtle text-warning">BELUM</span>'}</span>
                        <span>${row.keterangan ? '<i class="ti ti-note me-1"></i>' + row.keterangan : '-'}</span>
                    </div>
                </div>
            `).join('');

            $('#detail-content').html(`
                <div class="row g-2 mb-3">
                    <div class="col-6 col-md-3"><div class="metric-card-summary"><div class="metric-title">Tanggal</div><div class="fw-semibold text-dark">${new Date(data.tanggal).toLocaleDateString('id-ID')}</div></div></div>
                    <div class="col-6 col-md-3"><div class="metric-card-summary"><div class="metric-title">Hadir</div><div class="fw-semibold text-success">${Number(data.total_hadir || 0).toLocaleString('id-ID')} org</div></div></div>
                    <div class="col-6 col-md-3"><div class="metric-card-summary"><div class="metric-title">Sudah Dibayar</div><div class="fw-semibold text-primary">${Number(data.total_paid || 0).toLocaleString('id-ID')} org</div></div></div>
                    <div class="col-6 col-md-3"><div class="metric-card-summary"><div class="metric-title">Total Gaji</div><div class="fw-semibold text-dark">Rp ${formatMoneyValue(data.total_gaji || 0)}</div></div></div>
                </div>
                <div class="fw-bold small text-muted text-uppercase mb-2">Rincian Karyawan:</div>
                <div class="detail-karyawan-container" style="max-height: 400px; overflow-y: auto;">
                    ${rows}
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
                <div class="border rounded p-3 mb-2 bg-white">
                    <div class="d-flex justify-content-between gap-2 flex-wrap">
                        <div>
                            <div class="fw-bold text-dark">${row.fullname}</div>
                            <small class="text-muted">${row.karyawan_id} &bull; ${Array.from(new Set(row.toko_list)).join(', ')}</small>
                            <div class="small text-muted mt-1">Tanggal: ${Array.from(new Set(row.tanggal_list)).map((date) => new Date(date).toLocaleDateString('id-ID')).join(', ')}</div>
                        </div>
                        <div class="text-end">
                            <div class="fw-bold text-success fs-6">Rp ${formatMoneyValue(row.total_nominal || 0)}</div>
                            <a href="<?= base_url('/absensi/struk') ?>/${data.batch_id}/${row.karyawan_id}" target="_blank" class="btn btn-sm btn-success mt-1 btn-touch-target"><i class="ti ti-printer me-1"></i> Cetak Slip</a>
                        </div>
                    </div>
                </div>
            `).join('');

            $('#detail-content').html(`
                <div class="row g-2 mb-3">
                    <div class="col-6 col-md-3"><div class="metric-card-summary"><div class="metric-title">Batch</div><div class="fw-semibold text-primary text-truncate">${data.batch_id}</div></div></div>
                    <div class="col-6 col-md-3"><div class="metric-card-summary"><div class="metric-title">Tgl Bayar</div><div class="fw-semibold text-dark">${new Date(data.tanggal_bayar).toLocaleDateString('id-ID')}</div></div></div>
                    <div class="col-6 col-md-3"><div class="metric-card-summary"><div class="metric-title">Periode</div><div class="fw-semibold text-dark small">${new Date(data.periode_start).toLocaleDateString('id-ID')} - ${new Date(data.periode_end).toLocaleDateString('id-ID')}</div></div></div>
                    <div class="col-6 col-md-3"><div class="metric-card-summary"><div class="metric-title">Total Bayar</div><div class="fw-semibold text-success">Rp ${formatMoneyValue(data.total_nominal || 0)}</div></div></div>
                </div>
                <div class="fw-bold small text-muted text-uppercase mb-2">Penerima Gaji:</div>
                <div style="max-height: 400px; overflow-y: auto;">
                    ${employeeCards}
                </div>
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
                data: {
                    tanggal
                },
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