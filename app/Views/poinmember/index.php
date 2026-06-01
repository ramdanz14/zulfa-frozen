<?= $this->extend('layouts/base') ?>

<?= $this->section('content') ?>
<?php
/**
 * @var string $akses_menu
 * @var array  $customerOptions
 * @var int    $nominalPerPoin
 */
?>
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
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="modal-setting-poin" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Setting Poin Member</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="form-setting-poin">
                <div class="modal-body">
                    <div class="alert alert-info py-2">
                        Tentukan kelipatan belanja dalam rupiah untuk mendapatkan 1 poin loyalty member.
                    </div>
                    <div class="form-group">
                        <label for="nominal_per_poin" class="form-label">Nominal Rupiah per 1 Poin</label>
                        <input type="text" class="form-control money" id="nominal_per_poin" name="nominal_per_poin" value="<?= (int) $nominalPerPoin ?>" required>
                    </div>
                </div>
                <div class="modal-footer justify-content-between">
                    <button type="submit" class="btn btn-primary">Simpan Setting</button>
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
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
        responsive: true,
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
        columns: [{
                data: 'tanggal',
                title: 'Tanggal',
                render: data => data ? new Date(String(data).replace(' ', 'T')).toLocaleString('id-ID') : '-'
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
                    return `<div class="fw-semibold">${escapeHtml(data || '-')}</div><small class="text-muted">${escapeHtml(row.customer_nama || '-')}</small>`;
                }
            },
            {
                data: 'trx_id',
                title: 'Transaksi',
                render: function(data, type, row) {
                    return `<div>${escapeHtml(data || '-')}</div><small class="text-muted">${escapeHtml(row.keterangan || '-')}</small>`;
                }
            },
            {
                data: 'jenis',
                title: 'Jenis',
                className: 'text-center',
                render: function(data) {
                    if (data === 'tambah') {
                        return `<span class="badge bg-success-subtle text-success"><i class="ti ti-plus"></i> Tambah</span>`;
                    }
                    if (data === 'kurang') {
                        return `<span class="badge bg-warning-subtle text-warning"><i class="ti ti-minus"></i> Kurang</span>`;
                    }
                    return `<span class="badge bg-danger-subtle text-danger"><i class="ti ti-refresh-alert"></i> Reset</span>`;
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
                className: 'text-end',
                render: function(data, type, row) {
                    if (row.jenis === 'tambah') {
                        return `+${formatMoneyValue(row.poin_masuk || 0)}`;
                    }
                    if (row.jenis === 'kurang') {
                        return `-${formatMoneyValue(row.poin_keluar || 0)}`;
                    }
                    return `-${formatMoneyValue(row.poin_before || 0)}`;
                }
            },
            {
                data: null,
                title: 'Saldo Poin',
                className: 'text-end',
                render: function(data, type, row) {
                    return `${formatMoneyValue(row.poin_before || 0)} -> ${formatMoneyValue(row.poin_after || 0)}`;
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

    function openSettingPoin() {
        $('#nominal_per_poin').val(normalizeMoneyValue($('#current-nominal-per-poin').text()) || <?= (int) $nominalPerPoin ?>);
        applyMoneyMask('#form-setting-poin');
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
