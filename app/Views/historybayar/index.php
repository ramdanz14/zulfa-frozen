<?= $this->extend('layouts/base') ?>

<?= $this->section('content') ?>
<?php
/**
 * @var string $akses_menu
 * @var array  $supplierOptions
 */
?>
<div class="body-wrapper">
    <div class="container-fluid p-0">
        <div class="card bg-info-subtle shadow-none position-relative overflow-hidden mb-4">
            <div class="card-body px-4 py-3">
                <div class="row align-items-center">
                    <div class="col-lg-8">
                        <h4 class="fw-semibold mb-2">History Pembayaran Supplier</h4>
                        <p class="mb-0"><span class="page-pretitle">Total</span> | Riwayat semua pembayaran pembelian, tidak terbatas hanya transaksi kredit.</p>
                        <small class="text-muted d-block mt-1">Closing aktif: <?= esc($closingDate ?? '-') ?>. Pembayaran sebelum tanggal ini dikunci dari edit dan hapus.</small>
                    </div>
                    <div class="col-lg-4 text-lg-end mt-3 mt-lg-0">
                        <a href="<?= base_url('/hutang') ?>" class="btn btn-outline-danger btn-sm">Tambah Bayar via Menu Hutang</a>
                    </div>
                </div>
            </div>
        </div>

        <div class="card mb-3">
            <div class="card-body">
                <div class="row g-3 align-items-end">
                    <div class="col-lg-4">
                        <label class="form-label">Filter Supplier</label>
                        <select class="form-select select2" id="filter-supplier">
                            <option value="">Semua Supplier</option>
                            <?php foreach ($supplierOptions as $row) : ?>
                                <option value="<?= esc($row['supco']) ?>"><?= esc($row['supco']) ?> - <?= esc($row['nama'] ?? $row['supco']) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-lg-5">
                        <label class="form-label">Range Tanggal Bayar</label>
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

<div class="modal fade" id="modal-web" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div id="loadingOverlay" class="d-flex justify-content-center align-items-center" style="position:absolute;top:0;left:0;width:100%;height:100%;background-color:rgba(255,255,255,.7);z-index:1051;">
                <i class="fas fa-2x fa-sync fa-spin text-primary"></i>
            </div>
            <div class="modal-header">
                <h5 class="modal-title" id="modal-title">Modal title</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="modal-form">
                <div class="modal-body"></div>
                <div class="modal-footer justify-content-between">
                    <button type="submit" class="btn btn-primary" id="btn-aksi">Save changes</button>
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
    });

    DataTable.Buttons.defaults.dom.button.className = 'btn btn-primary';
    const table = $('#table-data').DataTable({
        layout: {
            topStart: {
                buttons: [{
                    text: '<i class="ti ti-file-type-xls"></i> Excel',
                    extend: 'excelHtml5',
                    title: 'Laporan-History-Pembayaran',
                    exportOptions: {
                        columns: [0, 1, 2, 3, 4, 5, 6],
                        orthogonal: 'export'
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
            url: '<?= base_url('/historybayar/ajax') ?>',
            type: 'post',
            data: function(d) {
                d.supco = $('#filter-supplier').val();
                d.date_start = filterStart.format('YYYY-MM-DD');
                d.date_end = filterEnd.format('YYYY-MM-DD');
            }
        },
        columns: [{
                data: 'tanggal_bayar',
                title: 'Tanggal Bayar',
                render: data => data ? new Date(String(data).replace(' ', 'T')).toLocaleString('id-ID') : '-'
            },
            {
                data: 'supplier_nama',
                title: 'Supplier',
                render: function(data, type, row) {
                    return `<div class="fw-semibold">${data || row.supco}</div><small class="text-muted">inv : ${row.invoice || '-'}</small>`;
                }
            },
            {
                data: 'beli_id',
                title: 'ID Beli/TGL Faktur',
                render: function(data, type, row) {
                    return `<div class="fw-semibold">${data || row.beli_id}</div><small class="text-muted">${row.tanggal || '-'}</small>`;
                }
            },
            {
                data: 'cara_bayar',
                title: 'Metode',
                className: 'text-center'
            },
            {
                data: 'bank_nama',
                title: 'Bank / Rekening',
                render: function(data, type, row) {
                    return row.cara_bayar === 'TRANSFER' ? `${data || '-'}<br><small class="text-muted">${row.rekening_no || '-'}</small>` : '-';
                }
            },
            {
                data: 'jumlah_bayar',
                title: 'Nominal',
                className: 'text-end',
                render: data => 'Rp ' + formatMoneyValue(data)
            },
            {
                data: 'status_bayar',
                title: 'Status Bayar',
                className: 'text-center',
                render: function(data, type, row) {
                    return `<span class="badge ${row.status_bayar === 'LUNAS' ? 'bg-success-subtle text-success' : 'bg-warning-subtle text-warning'}">${row.status_bayar}</span>`;
                }
            },
            {
                title: 'Action',
                data: null,
                className: 'text-center',
                responsivePriority: 1,
                render: function(data) {
                    const isLocked = data.can_modify === false;
                    const editBtn = akses_menu?.akses_update === 'Y' ?
                        (isLocked ?
                            `<a class="dropdown-item text-muted" href="javascript:void(0)" onclick="showLockMessage('${data.closing_date}','${data.cara_bayar}')"><i class="ti ti-lock text-danger"></i> ${data.cara_bayar === 'POTONGAN RETUR' ? 'Kelola dari Retur' : 'Edit Terkunci'}</a>` :
                            `<a class="dropdown-item" href="javascript:void(0)" onclick="showModal('edit', ${data.bayar_id})"><i class="ti ti-pencil text-warning"></i> Edit</a>`) :
                        '';
                    const deleteBtn = akses_menu?.akses_delete === 'Y' ?
                        (isLocked ?
                            `<a class="dropdown-item text-muted" href="javascript:void(0)" onclick="showLockMessage('${data.closing_date}','${data.cara_bayar}')"><i class="ti ti-lock text-danger"></i> ${data.cara_bayar === 'POTONGAN RETUR' ? 'Hapus via Retur' : 'Hapus Terkunci'}</a>` :
                            `<a class="dropdown-item" href="javascript:void(0)" onclick="showModal('delete', ${data.bayar_id})"><i class="ti ti-trash text-danger"></i> Hapus</a>`) :
                        '';
                    return `<span class="dropdown">
                        <button class="btn dropdown-toggle align-text-top btn-sm" data-bs-toggle="dropdown">Actions</button>
                        <div class="dropdown-menu dropdown-menu-end">
                            ${editBtn}
                            ${deleteBtn}
                        </div>
                    </span>`;
                }
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
        $('#filter-supplier').val('').trigger('change');
        filterStart = moment().startOf('month');
        filterEnd = moment().endOf('month');
        $('#filter-range').data('daterangepicker').setStartDate(filterStart);
        $('#filter-range').data('daterangepicker').setEndDate(filterEnd);
        $('#filter-range').val(`${filterStart.format('DD/MM/YYYY')} - ${filterEnd.format('DD/MM/YYYY')}`);
        table.ajax.reload();
    });

    $('#modal-form').validate({
        rules: {
            cara_bayar: 'required',
            tanggal_bayar: 'required',
            jumlah_bayar: 'required'
        },
        errorElement: 'span',
        errorPlacement: function(error, element) {
            error.addClass('invalid-feedback');
            element.closest('.form-group').append(error);
        },
        highlight: function(el) {
            $(el).addClass('is-invalid');
        },
        unhighlight: function(el) {
            $(el).removeClass('is-invalid');
        },
        submitHandler: function() {
            saveAjax();
        }
    });

    function showModal(action, bayarId) {
        $('#loadingOverlay').removeClass('d-none');
        $('#modal-form > .modal-body').empty();
        $('#btn-aksi').removeAttr('class').prop('disabled', false);
        $('#modal-web input, #modal-web select').prop('disabled', false).prop('readonly', false);

        $.getJSON(`<?= base_url('/historybayar/show') ?>/${bayarId}`, function(res) {
            $('#loadingOverlay').addClass('d-none');
            if (res.tipe !== 'success') {
                toastr.error(res.data || 'Data pembayaran tidak ditemukan');
                return;
            }
            const data = res.data;

            $('#modal-form > .modal-body').append(`
                <input type="hidden" name="bayar_id" id="bayar_id" value="${data.bayar_id}">
                <input type="hidden" name="_method" id="_method">
                <div class="alert alert-info py-2">
                    <div class="fw-semibold">${data.supplier_nama || data.supco}</div>
                    <div>${data.beli_id} / ${data.invoice || '-'}</div>
                </div>
                <div class="form-group mb-2">
                    <label class="form-label">Cara Bayar</label>
                    <select class="form-select" name="cara_bayar" id="cara_bayar">
                        <option value="TUNAI" ${data.cara_bayar === 'TUNAI' ? 'selected' : ''}>TUNAI</option>
                        <option value="TRANSFER" ${data.cara_bayar === 'TRANSFER' ? 'selected' : ''}>TRANSFER</option>
                    </select>
                </div>
                <div class="form-group mb-2">
                    <label class="form-label">Tanggal Bayar</label>
                    <input type="datetime-local" class="form-control" name="tanggal_bayar" id="tanggal_bayar" value="${toDatetimeLocal(data.tanggal_bayar)}">
                </div>
                <div class="form-group mb-2">
                    <label class="form-label">Nominal</label>
                    <input type="text" class="form-control money" name="jumlah_bayar" id="jumlah_bayar" value="${data.jumlah_bayar}">
                </div>
                <div class="form-group mb-2 transfer-only">
                    <label class="form-label">Nama Bank</label>
                    <input type="text" class="form-control" name="bank_nama" id="bank_nama" value="${data.bank_nama || ''}">
                </div>
                <div class="form-group mb-2 transfer-only">
                    <label class="form-label">No Rekening</label>
                    <input type="text" class="form-control" name="rekening_no" id="rekening_no" value="${data.rekening_no || ''}">
                </div>
            `);

            if (action === 'edit') {
                $('#_method').val('PATCH');
                $('#modal-title').text('Edit History Pembayaran');
                $('#btn-aksi').text('Update').addClass('btn btn-warning');
            } else {
                $('#_method').val('DELETE');
                $('#modal-title').text('Hapus History Pembayaran');
                $('#btn-aksi').text('Delete').addClass('btn btn-danger');
                $('#modal-web input, #modal-web select').prop('disabled', true).prop('readonly', true);
                $('#bayar_id, #_method').prop('disabled', false).prop('readonly', false);
            }

            applyMoneyMask('#modal-form');
            bindTransferVisibility();
            $('#modal-web').modal('show');
        }).fail(function(xhr) {
            $('#loadingOverlay').addClass('d-none');
            toastr.error(extractErrorMessage(xhr, 'Gagal memuat data pembayaran'));
        });
    }

    function bindTransferVisibility() {
        const toggle = () => {
            const isTransfer = $('#cara_bayar').val() === 'TRANSFER';
            $('.transfer-only').toggleClass('d-none', !isTransfer);
        };
        $('#cara_bayar').off('change').on('change', toggle);
        toggle();
    }

    function saveAjax() {
        normalizeMoneyInputs('#modal-form');
        const formData = $('#modal-form').serializeArray();
        $('#loadingOverlay').removeClass('d-none');
        $.ajax({
            type: 'POST',
            url: '<?= base_url('/historybayar') ?>',
            dataType: 'json',
            data: formData,
            success: function(res) {
                $('#loadingOverlay').addClass('d-none');
                $('#modal-web').modal('hide');
                if (res.tipe === 'success') {
                    toastr.success(res.data || 'Berhasil');
                    table.ajax.reload(null, false);
                    return;
                }
                toastr.error(res.data || 'Gagal');
            },
            error: function(xhr) {
                $('#loadingOverlay').addClass('d-none');
                toastr.error(extractErrorMessage(xhr, 'Gagal simpan perubahan'));
            }
        });
    }

    function showLockMessage(closingDate, caraBayar = '') {
        if (caraBayar === 'POTONGAN RETUR') {
            toastr.error('Pembayaran POTONGAN RETUR hanya bisa dikelola dari menu retur pembelian.');
            return;
        }
        toastr.error(`Pembayaran sebelum ${new Date(closingDate).toLocaleDateString('id-ID')} sudah melewati closing dan dikunci.`);
    }

    function toDatetimeLocal(value) {
        if (!value) return '';
        const dt = new Date(String(value).replace(' ', 'T'));
        if (Number.isNaN(dt.getTime())) return '';
        const tzOffset = dt.getTimezoneOffset() * 60000;
        return new Date(dt - tzOffset).toISOString().slice(0, 16);
    }
</script>
<?= $this->endSection('javascript') ?>
