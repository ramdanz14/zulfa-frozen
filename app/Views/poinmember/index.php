<?= $this->extend('layouts/base') ?>

<?= $this->section('content') ?>
<?php
/**
 * @var string $akses_menu
 * @var array  $customerOptions
 * @var int    $nominalPerPoin
 */
?>
<style>
    /* =========================================================
       POIN MEMBER CARDVIEW (Mobile/Tablet Reflow)
       ========================================================= */
    .poin-history-card {
        background: #ffffff;
        border: 1px solid #e2e8f0;
        border-radius: 12px;
        transition: transform 0.15s ease, box-shadow 0.15s ease;
    }

    .poin-history-card:hover {
        transform: translateY(-2px);
        box-shadow: 0 6px 16px rgba(0, 0, 0, 0.07);
    }

    .poin-card-mutation {
        font-size: 1.15rem;
        font-weight: 800;
        letter-spacing: -0.02em;
    }

    .poin-card-cust-id {
        font-family: var(--bs-font-monospace);
        font-size: 0.8rem;
        background: #f8fafc;
        color: #334155;
        border: 1px solid #cbd5e1;
        border-radius: 5px;
        padding: 0.15rem 0.45rem;
        font-weight: 700;
    }

    .poin-card-customer {
        font-weight: 700;
        color: #1e293b;
        font-size: 0.95rem;
        line-height: 1.25;
    }

    .poin-simulation-box {
        background: linear-gradient(135deg, #f0fdf4 0%, #ecfdf5 100%);
        border: 1px solid #bbf7d0;
        border-radius: 10px;
        padding: 0.85rem 1rem;
    }

    @media (max-width: 767.98px) {
        .dt-container .dt-search {
            width: 100% !important;
            text-align: left !important;
            margin-bottom: 0.5rem;
        }

        .dt-container .dt-search label {
            font-weight: 600;
            color: #475569;
            font-size: 0.85rem;
        }

        .dt-container .dt-search input[type="search"],
        .dt-container .dt-search input.form-control {
            width: 100% !important;
            height: 42px !important;
            margin-top: 4px !important;
            margin-left: 0 !important;
            padding: 6px 12px !important;
            font-size: 14px !important;
            border: 1px solid #cbd5e1 !important;
            border-radius: 6px !important;
            box-sizing: border-box !important;
        }

        #btn-filter,
        #btn-reset {
            min-height: 44px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
        }
    }
</style>
<div class="body-wrapper">
    <div class="container-fluid p-0">
        <div class="card bg-warning-subtle shadow-none position-relative overflow-hidden mb-4">
            <div class="card-body px-4 py-3">
                <div class="row align-items-center">
                    <div class="col-lg-8">
                        <h4 class="fw-semibold mb-2">Poin Member</h4>
                        <p class="mb-0"><span class="page-pretitle">Total</span> | Riwayat pendapatan dan penggunaan poin loyalty member lintas toko.</p>
                        <small class="text-muted d-block mt-1">
                            Setting saat ini:
                            setiap belanja kelipatan <span class="fw-semibold text-dark" id="current-nominal-per-poin">Rp <?= number_format((int) $nominalPerPoin, 0, ',', '.') ?></span>
                            mendapatkan 1 poin. Akumulasi poin bersifat global dan tersimpan di saldo customer.poin.
                        </small>
                    </div>
                    <div class="col-lg-4 text-lg-end mt-3 mt-lg-0">
                        <div class="small text-muted">
                            Jenis transaksi: tambah dari belanja, kurang dari penukaran diskon, dan reset untuk reset saldo poin.
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="card mb-3">
            <div class="card-body">
                <div class="row g-3 align-items-end">
                    <div class="col-lg-4">
                        <label class="form-label">Filter Customer</label>
                        <select class="form-select select2" id="filter-customer">
                            <option value="">Semua Customer</option>
                            <?php foreach ($customerOptions as $row) : ?>
                                <option value="<?= esc($row['cust_id']) ?>"><?= esc($row['cust_id']) ?> - <?= esc($row['nama'] ?? '-') ?> | Poin: <?= number_format((int) ($row['poin'] ?? 0), 0, ',', '.') ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-lg-5">
                        <label class="form-label">Range Tanggal Transaksi</label>
                        <input type="text" class="form-control" id="filter-range" readonly>
                    </div>
                    <div class="col-lg-3 d-grid d-lg-flex gap-2">
                        <button type="button" class="btn btn-primary w-100" id="btn-filter">Terapkan Filter</button>
                        <button type="button" class="btn btn-light w-100" id="btn-reset">Reset</button>
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

                <!-- CardView Template for Poin Member (Mobile/Tablet Reflow) -->
                <template id="card-poinmember-template">
                    <div class="poin-history-card p-3 h-100 shadow-sm">
                        <!-- Top: Customer Name, Cust ID, Toko & Jenis Badge -->
                        <div class="d-flex justify-content-between align-items-start gap-2 mb-2 pb-2 border-bottom">
                            <div style="flex: 1; min-width: 0;">
                                <div class="poin-card-customer text-truncate" data-dtcv-field="2"></div>
                                <div class="mt-1 d-flex flex-wrap align-items-center gap-1">
                                    <span class="badge bg-light text-secondary border px-2 py-1" style="font-size: 0.75rem;">
                                        <i class="ti ti-building-store me-1"></i>Toko <span data-dtcv-field="1"></span>
                                    </span>
                                    <span data-dtcv-field="4"></span>
                                </div>
                            </div>
                            <div class="text-end flex-shrink-0">
                                <small class="text-muted d-block text-uppercase" style="font-size: 0.7rem; font-weight: 600;">Mutasi</small>
                                <span class="poin-card-mutation" data-dtcv-field="6"></span>
                            </div>
                        </div>

                        <!-- Mid: ID Transaksi & Waktu -->
                        <div class="row g-2 mb-2 text-muted small">
                            <div class="col-7 text-truncate">
                                <span data-dtcv-field="3"></span>
                            </div>
                            <div class="col-5 text-end text-truncate" style="font-size: 0.78rem;">
                                <i class="ti ti-calendar me-1"></i><span data-dtcv-field="0"></span>
                            </div>
                        </div>

                        <!-- Bottom: Nominal Belanja, Perubahan Saldo & Setting -->
                        <div class="d-flex justify-content-between align-items-center pt-2 border-top text-muted small" style="font-size: 0.8rem;">
                            <div>
                                <span>Belanja: </span><span class="fw-bold text-dark" data-dtcv-field="5"></span>
                            </div>
                            <div class="text-end">
                                <span class="badge bg-primary-subtle text-primary border border-primary-subtle px-2 py-1" style="font-size: 0.78rem;">
                                    <i class="ti ti-coin me-1"></i><span data-dtcv-field="7"></span>
                                </span>
                            </div>
                        </div>
                    </div>
                </template>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="modal-setting-poin" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow">
            <div class="modal-header py-3 px-3 px-md-4 border-bottom">
                <div class="d-flex align-items-center gap-2">
                    <i class="ti ti-settings text-primary fs-5"></i>
                    <h5 class="modal-title fw-bold text-dark mb-0">Setting Poin Member</h5>
                </div>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="form-setting-poin">
                <div class="modal-body p-3 p-md-4">
                    <div class="alert alert-info py-2 px-3 small d-flex align-items-center gap-2 mb-3">
                        <i class="ti ti-info-circle fs-4 flex-shrink-0"></i>
                        <div>Tentukan kelipatan belanja dalam rupiah untuk mendapatkan 1 poin loyalty member.</div>
                    </div>

                    <div class="form-group mb-3">
                        <label for="nominal_per_poin" class="form-label fw-bold text-dark small text-uppercase">Nominal Rupiah per 1 Poin</label>
                        <div class="input-group input-group-lg">
                            <span class="input-group-text bg-light fw-bold text-muted">Rp</span>
                            <input type="text" class="form-control money fs-4 fw-bold text-dark" id="nominal_per_poin" name="nominal_per_poin" value="<?= (int) $nominalPerPoin ?>" required autocomplete="off">
                        </div>
                        <small class="text-muted mt-1 d-block">Contoh: Rp 1.000 berarti setiap kelipatan Rp 1.000 mendapatkan 1 poin.</small>
                    </div>

                    <!-- Kotak Simulasi Belanja Rp 100.000 -->
                    <div class="poin-simulation-box">
                        <div class="d-flex align-items-center justify-content-between mb-1">
                            <span class="fw-bold text-success small text-uppercase d-inline-flex align-items-center gap-1">
                                <i class="ti ti-calculator fs-4"></i> Simulasi Belanja Rp 100.000
                            </span>
                            <span class="badge bg-success text-white px-2 py-1" id="simulasi-poin-badge">0 Poin</span>
                        </div>
                        <div class="text-muted small mb-0" id="simulasi-poin-text">
                            Jika pelanggan berbelanja sebesar <strong>Rp 100.000</strong>, maka member akan mendapatkan <strong class="text-success fs-5 fw-bold" id="simulasi-poin-val">0</strong> poin loyalty.
                        </div>
                    </div>
                </div>
                <div class="modal-footer justify-content-between p-3 border-top">
                    <button type="button" class="btn btn-secondary px-3 py-2" data-bs-dismiss="modal">Tutup</button>
                    <button type="submit" class="btn btn-primary px-4 py-2 fw-semibold" id="btn-save-setting"><i class="ti ti-device-floppy me-1"></i> Simpan Setting</button>
                </div>
            </form>
        </div>
    </div>
</div>
<?= $this->endSection('content') ?>

<?= $this->section('javascript') ?>
<script>
    const akses_menu = <?= $akses_menu ?>;
    const settingPoinModal = new bootstrap.Modal(document.getElementById('modal-setting-poin'));
    let filterStart = moment().startOf('month');
    let filterEnd = moment().endOf('month');

    $(function() {
        $('.select2').select2({
            width: '100%'
        });

        $('#filter-range').daterangepicker({
            startDate: filterStart,
            endDate: filterEnd,
            autoApply: true,
            opens: 'left',
            locale: {
                format: 'DD/MM/YYYY',
                separator: ' - ',
                applyLabel: 'Terapkan',
                cancelLabel: 'Batal',
                fromLabel: 'Dari',
                toLabel: 'Sampai',
                customRangeLabel: 'Pilih Sendiri',
                weekLabel: 'M',
                daysOfWeek: ['Min', 'Sen', 'Sel', 'Rab', 'Kam', 'Jum', 'Sab'],
                monthNames: ['Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni', 'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember'],
                firstDay: 1
            },
            ranges: {
                'Hari Ini': [moment(), moment()],
                '7 Hari Terakhir': [moment().subtract(6, 'days'), moment()],
                '30 Hari Terakhir': [moment().subtract(29, 'days'), moment()],
                'Bulan Ini': [moment().startOf('month'), moment().endOf('month')],
                'Bulan Lalu': [moment().subtract(1, 'month').startOf('month'), moment().subtract(1, 'month').endOf('month')]
            }
        }, function(start, end) {
            filterStart = start;
            filterEnd = end;
        });

        $('#filter-range').val(`${filterStart.format('DD/MM/YYYY')} - ${filterEnd.format('DD/MM/YYYY')}`);
        applyMoneyMask('#form-setting-poin');
    });

    DataTable.Buttons.defaults.dom.button.className = 'btn btn-primary';
    const datatableButtons = [{
        text: '<i class="ti ti-file-type-xls"></i> Excel',
        extend: 'excelHtml5',
        title: 'Laporan-Poin-Member',
        exportOptions: {
            columns: [0, 1, 2, 3, 4, 5, 6, 7, 8],
            orthogonal: 'export'
        }
    }];

    if (akses_menu?.akses_update === 'Y') {
        datatableButtons.push({
            text: '<i class="ti ti-settings"></i> Setting Poin',
            className: 'btn btn-warning',
            action: function() {
                openSettingPoin();
            }
        });
    }

    if (akses_menu?.akses_delete === 'Y') {
        datatableButtons.push({
            text: '<i class="ti ti-alert-triangle"></i> Hard Reset Poin',
            className: 'btn btn-danger',
            action: function() {
                confirmHardReset();
            }
        });
    }

    datatableButtons.push('pageLength');

    const table = $('#table-data').DataTable({
        layout: {
            topStart: {
                buttons: datatableButtons
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
            url: '<?= base_url('/poinmember/ajax') ?>',
            type: 'post',
            data: function(d) {
                d.cust_id = $('#filter-customer').val();
                d.date_start = filterStart.format('YYYY-MM-DD');
                d.date_end = filterEnd.format('YYYY-MM-DD');
            }
        },
        cardView: {
            enable: true,
            breakpoint: 768,
            template: '#card-poinmember-template',
            gridClass: 'col-12 col-sm-6 col-lg-4 mb-3',
            onCardRender: function($card, rowData, rowIdx, rowNode) {
                const $mutasi = $card.find('.poin-card-mutation');
                if (rowData.jenis === 'tambah') {
                    $mutasi.addClass('text-success');
                } else if (rowData.jenis === 'kurang') {
                    $mutasi.addClass('text-warning');
                } else {
                    $mutasi.addClass('text-danger');
                }
            }
        },
        columns: [{
                data: 'tanggal',
                title: 'Tanggal',
                render: function(data, type) {
                    if (type === 'display') {
                        if (!data) return '-';
                        const d = new Date(String(data).replace(' ', 'T'));
                        const dateStr = d.toLocaleDateString('id-ID', { day: '2-digit', month: 'short', year: 'numeric' });
                        const timeStr = d.toLocaleTimeString('id-ID', { hour: '2-digit', minute: '2-digit' });
                        return `<span title="${data}">${dateStr} <small class="text-muted">${timeStr}</small></span>`;
                    }
                    return data || '';
                }
            },
            {
                data: 'toko_id',
                title: 'Toko',
                className: 'text-center'
            },
            {
                data: 'cust_id',
                title: 'Customer',
                render: function(data, type, row) {
                    return `<div class="fw-semibold text-dark">${escapeHtml(data || '-')}</div><small class="text-muted font-monospace">${escapeHtml(row.customer_nama || '-')}</small>`;
                }
            },
            {
                data: 'trx_id',
                title: 'Transaksi',
                render: function(data, type, row) {
                    return `<div class="font-monospace fw-medium">${escapeHtml(data || '-')}</div><small class="text-muted text-truncate d-inline-block" style="max-width: 200px;">${escapeHtml(row.keterangan || '-')}</small>`;
                }
            },
            {
                data: 'jenis',
                title: 'Jenis',
                className: 'text-center',
                render: function(data) {
                    if (data === 'tambah') {
                        return `<span class="badge bg-success-subtle text-success border border-success-subtle"><i class="ti ti-plus"></i> Tambah</span>`;
                    }
                    if (data === 'kurang') {
                        return `<span class="badge bg-warning-subtle text-warning border border-warning-subtle"><i class="ti ti-minus"></i> Kurang</span>`;
                    }
                    return `<span class="badge bg-danger-subtle text-danger border border-danger-subtle"><i class="ti ti-refresh-alert"></i> Reset</span>`;
                }
            },
            {
                data: 'nominal_transaksi',
                title: 'Nominal',
                className: 'text-end',
                render: data => 'Rp ' + formatMoneyValue(data || 0)
            },
            {
                data: null,
                title: 'Mutasi Poin',
                className: 'text-end fw-bold',
                render: function(data, type, row) {
                    if (row.jenis === 'tambah') {
                        return `<span class="text-success">+${formatMoneyValue(row.poin_masuk || 0)}</span>`;
                    }
                    if (row.jenis === 'kurang') {
                        return `<span class="text-warning">-${formatMoneyValue(row.poin_keluar || 0)}</span>`;
                    }
                    return `<span class="text-danger">-${formatMoneyValue(row.poin_before || 0)}</span>`;
                }
            },
            {
                data: null,
                title: 'Saldo Poin',
                className: 'text-end',
                render: function(data, type, row) {
                    return `<span class="text-muted">${formatMoneyValue(row.poin_before || 0)}</span> <i class="ti ti-arrow-right text-muted" style="font-size:10px;"></i> <span class="fw-bold text-dark">${formatMoneyValue(row.poin_after || 0)}</span>`;
                }
            },
            {
                data: 'nominal_per_poin',
                title: 'Setting/Poin',
                className: 'text-end',
                render: data => 'Rp ' + formatMoneyValue(data || 0)
            }
        ]
    });

    table.on('xhr.dt', function(e, settings, json) {
        $('.page-pretitle').text(`Total Data : ${json?.recordsFiltered || 0}`);
    });

    $('#btn-filter').on('click', function() {
        table.ajax.reload();
    });

    $('#btn-reset').on('click', function() {
        $('#filter-customer').val('').trigger('change');
        filterStart = moment().startOf('month');
        filterEnd = moment().endOf('month');
        $('#filter-range').data('daterangepicker').setStartDate(filterStart);
        $('#filter-range').data('daterangepicker').setEndDate(filterEnd);
        $('#filter-range').val(`${filterStart.format('DD/MM/YYYY')} - ${filterEnd.format('DD/MM/YYYY')}`);
        table.ajax.reload();
    });

    $('#form-setting-poin').on('submit', function(e) {
        e.preventDefault();
        saveSettingPoin();
    });

    function updatePoinSimulation() {
        const nominal = Number(normalizeMoneyValue($('#nominal_per_poin').val() || 0));
        let poin = 0;
        if (nominal > 0) {
            poin = Math.floor(100000 / nominal);
        }
        $('#simulasi-poin-badge').text(`${Number(poin).toLocaleString('id-ID')} Poin`);
        $('#simulasi-poin-val').text(Number(poin).toLocaleString('id-ID'));
        if (nominal <= 0) {
            $('#simulasi-poin-text').html('Masukkan nominal rupiah per poin yang valid untuk melihat simulasi.');
        } else {
            $('#simulasi-poin-text').html(`Jika pelanggan berbelanja sebesar <strong>Rp 100.000</strong> dengan rasio <strong>Rp ${formatMoneyValue(nominal)}</strong> per poin, maka member akan mendapatkan <strong class="text-success fs-5 fw-bold">${Number(poin).toLocaleString('id-ID')}</strong> poin loyalty.`);
        }
    }

    $('#nominal_per_poin').on('input keyup change', function() {
        updatePoinSimulation();
    });

    function openSettingPoin() {
        $('#nominal_per_poin').val(normalizeMoneyValue($('#current-nominal-per-poin').text()) || <?= (int) $nominalPerPoin ?>);
        applyMoneyMask('#form-setting-poin');
        updatePoinSimulation();
        settingPoinModal.show();
    }

    function saveSettingPoin() {
        const nominal = Number(normalizeMoneyValue($('#nominal_per_poin').val() || 0));
        if (nominal <= 0) {
            toastr.error('Nominal rupiah per poin harus lebih besar dari nol');
            return;
        }

        $.ajax({
            type: 'POST',
            url: '<?= base_url('/poinmember/setting') ?>',
            dataType: 'json',
            data: {
                nominal_per_poin: nominal
            },
            success: function(res) {
                if (res.tipe === 'success') {
                    $('#current-nominal-per-poin').text(`Rp ${formatMoneyValue(res.nominal_per_poin || nominal)}`);
                    toastr.success(res.data || 'Setting poin member berhasil disimpan');
                    settingPoinModal.hide();
                    table.ajax.reload(null, false);
                    return;
                }
                toastr.error(res.data || 'Gagal menyimpan setting poin member');
            },
            error: function(xhr) {
                toastr.error(extractErrorMessage(xhr, 'Gagal menyimpan setting poin member'));
            }
        });
    }

    function confirmHardReset() {
        Swal.fire({
            title: 'Hard reset semua poin member?',
            text: 'Aksi ini akan membuat saldo poin seluruh customer menjadi nol dan tidak bisa dibatalkan.',
            icon: 'error',
            showCancelButton: true,
            confirmButtonText: 'Ya, reset semua',
            cancelButtonText: 'Batal',
            confirmButtonColor: '#d33'
        }).then((result) => {
            if (!result.isConfirmed) {
                return;
            }

            $.ajax({
                type: 'POST',
                url: '<?= base_url('/poinmember/hard-reset') ?>',
                dataType: 'json',
                data: {
                    _method: 'DELETE'
                },
                success: function(res) {
                    if (res.tipe === 'success') {
                        toastr.success(res.data || 'Hard reset poin member berhasil');
                        table.ajax.reload();
                        return;
                    }
                    toastr.error(res.data || 'Gagal melakukan hard reset poin member');
                },
                error: function(xhr) {
                    toastr.error(extractErrorMessage(xhr, 'Gagal melakukan hard reset poin member'));
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
