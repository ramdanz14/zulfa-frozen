<?= $this->extend('layouts/base') ?>

<?= $this->section('content') ?>
<div class="body-wrapper">
    <div class="container-fluid p-0">
        <div class="card bg-danger-subtle shadow-none position-relative overflow-hidden mb-4">
            <div class="card-body px-4 py-3">
                <div class="row align-items-center">
                    <div class="col-lg-8">
                        <h4 class="fw-semibold mb-2">Monitoring Hutang Supplier</h4>
                        <p class="mb-0"><span class="page-pretitle">Total</span> | Pantau hutang, overdue, histori cicilan, dan input pembayaran baru.</p>
                    </div>
                    <div class="col-lg-4 text-lg-end mt-3 mt-lg-0">
                        <div class="d-inline-flex gap-2 align-items-center">
                            <label for="status_filter" class="mb-0 text-muted">Filter</label>
                            <select class="form-select form-select-sm" id="status_filter" style="min-width: 180px;">
                                <option value="BELUM">Belum / Cicil</option>
                                <option value="LUNAS">Lunas</option>
                                <option value="ALL">Semua Kredit</option>
                            </select>
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

<div class="modal fade" id="modal-hutang" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog  modal-fullscreen modal-dialog-scrollable">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Detail Hutang Supplier</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div id="hutang-detail" class="mb-3"></div>
                <div class="row g-3">
                    <div class="col-lg-7">
                        <div class="card mb-0">
                            <div class="card-header">
                                <h6 class="mb-0">Histori Pembayaran</h6>
                            </div>
                            <div class="card-body p-2">
                                <div class="table-responsive">
                                    <table class="table table-sm table-bordered align-middle mb-0">
                                        <thead class="table-light">
                                            <tr>
                                                <th>Tanggal</th>
                                                <th>Metode</th>
                                                <th>Bank</th>
                                                <th>No Rekening</th>
                                                <th>Jumlah</th>
                                            </tr>
                                        </thead>
                                        <tbody id="history-body"></tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-lg-5">
                        <div class="card mb-0">
                            <div class="card-header d-flex justify-content-between align-items-center">
                                <h6 class="mb-0">Bayar Hutang</h6>
                                <button type="button" class="btn btn-sm btn-primary" id="btn-add-modal-payment">Tambah</button>
                            </div>
                            <div class="card-body p-2">
                                <form id="form-pay-hutang">
                                    <input type="hidden" id="modal-beli-id">
                                    <div class="table-responsive">
                                        <table class="table table-sm table-bordered align-middle mb-0" id="modal-payment-table">
                                            <thead class="table-light">
                                                <tr>
                                                    <th>Metode</th>
                                                    <th>Tgl/Jam</th>
                                                    <th>Nominal</th>
                                                    <th>Bank / Rek</th>
                                                    <th>Aksi</th>
                                                </tr>
                                            </thead>
                                            <tbody></tbody>
                                        </table>
                                    </div>
                                    <div class="d-flex justify-content-between mt-3">
                                        <span class="text-muted">Sisa Hutang</span>
                                        <span class="fw-semibold text-danger" id="modal-sisa-hutang">Rp 0</span>
                                    </div>
                                    <div class="d-grid mt-3">
                                        <button type="submit" class="btn btn-success">Simpan Cicilan</button>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<?= $this->endSection('content') ?>

<?= $this->section('javascript') ?>
<script>
    let currentDebt = null;
    let modalPayments = [];
    const hutangModal = new bootstrap.Modal(document.getElementById('modal-hutang'));
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
            url: '<?= base_url('/hutang/ajax') ?>',
            type: 'post',
            data: function(d) {
                d.status_filter = $('#status_filter').val();
            }
        },
        columns: [{
                data: 'supplier_nama',
                title: 'Supplier',
                render: function(data, type, row) {
                    return `<div class="fw-semibold">${data || row.supco}</div><small class="text-muted">${row.invoice}</small>`;
                }
            },
            {
                data: 'beli_id',
                title: 'ID / Tgl',
                render: function(data, type, row) {
                    return `<div class="fw-semibold">${data}</div><small class="text-muted">${new Date(row.tanggal).toLocaleDateString('id-ID')}</small>`;
                }
            },
            {
                data: 'total_gross',
                title: 'Total Hutang',
                className: 'text-end',
                render: data => 'Rp ' + formatMoneyValue(data)
            },
            {
                data: 'total_bayar',
                title: 'Terbayar',
                className: 'text-end',
                render: data => 'Rp ' + formatMoneyValue(data)
            },
            {
                data: 'sisa_bayar',
                title: 'Sisa',
                className: 'text-end',
                render: data => 'Rp ' + formatMoneyValue(data)
            },
            {
                data: 'jatuh_tempo',
                title: 'Jatuh Tempo',
                render: function(data, type, row) {
                    if (!data) return '-';
                    const overdue = row.status_bayar !== 'LUNAS' && data < '<?= date('Y-m-d') ?>';
                    const cls = overdue ? 'text-danger fw-semibold' : '';
                    const label = overdue ? `<div class="small text-danger">Overdue ${Math.max(Number(row.hari_lewat || 0), 0)} hari</div>` : humanizeDate(data);
                    return `<div class="${cls}">${new Date(data).toLocaleDateString('id-ID')}</div>${label}`;
                }
            },
            {
                data: 'status_bayar',
                title: 'Status',
                className: 'text-center',
                render: function(data) {
                    if (data === 'LUNAS') return '<span class="badge bg-success-subtle text-success">LUNAS</span>';
                    if (data === 'CICIL') return '<span class="badge bg-info-subtle text-info">CICIL</span>';
                    return '<span class="badge bg-danger-subtle text-danger">BELUM</span>';
                }
            },
            {
                title: 'Action',
                data: null,
                className: 'text-center',
                responsivePriority: 1,
                render: function(data) {
                    return `<span class="dropdown">
                        <button class="btn dropdown-toggle align-text-top btn-sm" data-bs-toggle="dropdown">Actions</button>
                        <div class="dropdown-menu dropdown-menu-end">
                            <a class="dropdown-item" href="javascript:void(0)" onclick="openDebtModal('${data.beli_id}')"><i class="ti ti-cash text-success"></i> Bayar Hutang</a>
                            <a class="dropdown-item" href="javascript:void(0)" onclick="openDebtModal('${data.beli_id}')"><i class="ti ti-history text-info"></i> Lihat History</a>
                        </div>
                    </span>`;
                }
            }
        ]
    });

    table.on('xhr.dt', function(e, settings, json) {
        $('.page-pretitle').text(`Total Data : ${json?.recordsFiltered || 0}`);
    });

    $('#status_filter').on('change', function() {
        table.ajax.reload();
    });

    $('#btn-add-modal-payment').on('click', function() {
        modalPayments.push({
            cara_bayar: 'TUNAI',
            tanggal_bayar: nowLocalValue(),
            jumlah_bayar: 0,
            bank_nama: '',
            rekening_no: ''
        });
        renderModalPayments();
    });

    $('#form-pay-hutang').on('submit', function(e) {
        e.preventDefault();
        submitDebtPayment();
    });

    function openDebtModal(beliId) {
        currentDebt = null;
        modalPayments = [{
            cara_bayar: 'TUNAI',
            tanggal_bayar: nowLocalValue(),
            jumlah_bayar: 0,
            bank_nama: '',
            rekening_no: ''
        }];
        $('#hutang-detail').html('<div class="text-center py-5 text-muted">Memuat data hutang...</div>');
        $('#history-body').html('<tr><td colspan="5" class="text-center text-muted">Memuat histori...</td></tr>');
        $('#modal-beli-id').val(beliId);
        renderModalPayments();
        hutangModal.show();

        $.getJSON(`<?= base_url('/pembelian/show') ?>/${beliId}`, function(res) {
            if (res.tipe !== 'success') {
                $('#hutang-detail').html(`<div class="alert alert-danger mb-0">${res.data || 'Data hutang tidak ditemukan'}</div>`);
                return;
            }
            currentDebt = res.data;
            renderDebtHeader();
            renderHistory(currentDebt.payments || []);
            renderModalPayments();
        }).fail(function(xhr) {
            $('#hutang-detail').html(`<div class="alert alert-danger mb-0">${extractErrorMessage(xhr, 'Gagal memuat data hutang')}</div>`);
        });
    }

    function renderDebtHeader() {
        if (!currentDebt) return;
        const overdue = currentDebt.status_bayar !== 'LUNAS' && currentDebt.jatuh_tempo && currentDebt.jatuh_tempo < '<?= date('Y-m-d') ?>';
        const itemDetail = (currentDebt.details || []).map((row) => `
            <li>${row.kode_item} - ${row.nama_item || '-'} | ${Number(row.qty_beli || 0).toLocaleString('id-ID')} ${row.sat_id} | Rp ${formatMoneyValue(row.gross)}</li>
        `).join('');

        $('#hutang-detail').html(`
            <div class="row g-3">
                <div class="col-md-3"><div class="border rounded p-3 h-100"><small class="text-muted">Supplier</small><div class="fw-semibold">${currentDebt.supplier_nama || currentDebt.supco}</div></div></div>
                <div class="col-md-3"><div class="border rounded p-3 h-100"><small class="text-muted">Invoice</small><div class="fw-semibold">${currentDebt.invoice}</div></div></div>
                <div class="col-md-3"><div class="border rounded p-3 h-100"><small class="text-muted">Total Hutang</small><div class="fw-semibold">Rp ${formatMoneyValue(currentDebt.total_gross)}</div></div></div>
                <div class="col-md-3"><div class="border rounded p-3 h-100"><small class="text-muted">Sisa</small><div class="fw-semibold text-danger">Rp ${formatMoneyValue(currentDebt.sisa_bayar)}</div></div></div>
                <div class="col-md-6"><div class="border rounded p-3 h-100"><small class="text-muted">Jatuh Tempo</small><div class="fw-semibold ${overdue ? 'text-danger' : ''}">${currentDebt.jatuh_tempo ? new Date(currentDebt.jatuh_tempo).toLocaleDateString('id-ID') : '-'}</div></div></div>
                <div class="col-md-6"><div class="border rounded p-3 h-100"><small class="text-muted">Detail Invoice Pembelian</small><ul class="mb-0 ps-3">${itemDetail || '<li>-</li>'}</ul></div></div>
            </div>
        `);
    }

    function renderHistory(rows) {
        const $body = $('#history-body');
        $body.empty();
        if (!rows.length) {
            $body.html('<tr><td colspan="5" class="text-center text-muted">Belum ada histori pembayaran</td></tr>');
            return;
        }
        rows.forEach((row) => {
            $body.append(`
                <tr>
                    <td>${new Date(row.tanggal_bayar).toLocaleString('id-ID')}</td>
                    <td>${row.cara_bayar}</td>
                    <td>${row.bank_nama || '-'}</td>
                    <td>${row.rekening_no || '-'}</td>
                    <td class="text-end">Rp ${formatMoneyValue(row.jumlah_bayar)}</td>
                </tr>
            `);
        });
    }

    function renderModalPayments() {
        const $tbody = $('#modal-payment-table tbody');
        $tbody.empty();
        if (!modalPayments.length) {
            $tbody.html('<tr><td colspan="5" class="text-center text-muted">Belum ada data cicilan baru</td></tr>');
            updateModalRemaining();
            return;
        }

        modalPayments.forEach((row, idx) => {
            const transferBox = row.cara_bayar === 'TRANSFER' ?
                `<input type="text" class="form-control form-control-sm mb-1 modal-bank" placeholder="Bank" value="${row.bank_nama || ''}">
                   <input type="text" class="form-control form-control-sm modal-rekening" placeholder="Rekening" value="${row.rekening_no || ''}">` :
                '<small class="text-muted">Tunai</small>';
            $tbody.append(`
                <tr data-idx="${idx}">
                    <td>
                        <select class="form-select form-select-sm modal-method">
                            <option value="TUNAI" ${row.cara_bayar === 'TUNAI' ? 'selected' : ''}>TUNAI</option>
                            <option value="TRANSFER" ${row.cara_bayar === 'TRANSFER' ? 'selected' : ''}>TRANSFER</option>
                        </select>
                    </td>
                    <td><input type="datetime-local" class="form-control form-control-sm modal-date" value="${toDatetimeLocal(row.tanggal_bayar)}"></td>
                    <td><input type="text" class="form-control form-control-sm money modal-amount" value="${row.jumlah_bayar}"></td>
                    <td>${transferBox}</td>
                    <td class="text-center"><button type="button" class="btn btn-sm btn-danger modal-delete"><i class="ti ti-trash"></i></button></td>
                </tr>
            `);
        });

        bindModalPaymentEvents();
        updateModalRemaining();
    }

    function bindModalPaymentEvents() {
        $('#modal-payment-table .modal-method').off('change').on('change', function() {
            const idx = Number($(this).closest('tr').data('idx'));
            modalPayments[idx].cara_bayar = $(this).val();
            if (modalPayments[idx].cara_bayar === 'TUNAI') {
                modalPayments[idx].bank_nama = '';
                modalPayments[idx].rekening_no = '';
            }
            renderModalPayments();
        });

        $('#modal-payment-table .modal-date').off('change').on('change', function() {
            const idx = Number($(this).closest('tr').data('idx'));
            modalPayments[idx].tanggal_bayar = $(this).val();
        });

        $('#modal-payment-table .modal-amount').off('input blur').on('input blur', function() {
            const idx = Number($(this).closest('tr').data('idx'));
            modalPayments[idx].jumlah_bayar = Number(normalizeMoneyValue($(this).val() || 0));
            updateModalRemaining();
            applyMoneyMask('#modal-payment-table');

        });

        $('#modal-payment-table .modal-bank').off('input').on('input', function() {
            const idx = Number($(this).closest('tr').data('idx'));
            modalPayments[idx].bank_nama = $(this).val();
        });

        $('#modal-payment-table .modal-rekening').off('input').on('input', function() {
            const idx = Number($(this).closest('tr').data('idx'));
            modalPayments[idx].rekening_no = $(this).val();
        });

        $('#modal-payment-table .modal-delete').off('click').on('click', function() {
            const idx = Number($(this).closest('tr').data('idx'));
            modalPayments.splice(idx, 1);
            renderModalPayments();
        });
    }

    function updateModalRemaining() {
        const originalRemaining = Number(currentDebt?.sisa_bayar || 0);
        const incoming = modalPayments.reduce((sum, row) => sum + Number(row.jumlah_bayar || 0), 0);
        const nextRemaining = Math.max(originalRemaining - incoming, 0);
        $('#modal-sisa-hutang').text(`Rp ${formatMoneyValue(nextRemaining)}`);
    }

    function submitDebtPayment() {
        normalizeMoneyInputs('#form-pay-hutang');
        if (!currentDebt) {
            toastr.error('Data hutang belum siap');
            return;
        }

        const cleaned = modalPayments
            .filter(row => Number(row.jumlah_bayar || 0) > 0)
            .map(row => ({
                cara_bayar: row.cara_bayar,
                tanggal_bayar: row.tanggal_bayar ? normalizeDateTime(row.tanggal_bayar) : '',
                jumlah_bayar: Number(row.jumlah_bayar || 0),
                bank_nama: row.bank_nama || '',
                rekening_no: row.rekening_no || ''
            }));

        if (!cleaned.length) {
            toastr.error('Masukkan minimal satu cicilan');
            return;
        }

        const totalPay = cleaned.reduce((sum, row) => sum + row.jumlah_bayar, 0);
        if (totalPay > Number(currentDebt.sisa_bayar || 0)) {
            toastr.error('Nominal cicilan melebihi sisa hutang');
            return;
        }

        for (const row of cleaned) {
            if (row.cara_bayar === 'TRANSFER' && (!row.bank_nama || !row.rekening_no)) {
                toastr.error('Transfer wajib isi bank dan rekening');
                return;
            }
        }

        $.ajax({
            type: 'POST',
            url: `<?= base_url('/pembelian/pay') ?>/${$('#modal-beli-id').val()}`,
            dataType: 'json',
            data: {
                payment_json: JSON.stringify(cleaned)
            },
            success: function(res) {
                if (res.tipe === 'success') {
                    toastr.success(res.data || 'Pembayaran tersimpan');
                    hutangModal.hide();
                    table.ajax.reload(null, false);
                    return;
                }
                toastr.error(res.data || 'Gagal menyimpan pembayaran');
            },
            error: function(xhr) {
                toastr.error(extractErrorMessage(xhr, 'Gagal menyimpan pembayaran'));
            }
        });
    }

    function nowLocalValue() {
        const now = new Date();
        const tzOffset = now.getTimezoneOffset() * 60000;
        return new Date(now - tzOffset).toISOString().slice(0, 16);
    }

    function toDatetimeLocal(value) {
        if (!value) return nowLocalValue();
        const dt = new Date(String(value).replace(' ', 'T'));
        if (Number.isNaN(dt.getTime())) return nowLocalValue();
        const tzOffset = dt.getTimezoneOffset() * 60000;
        return new Date(dt - tzOffset).toISOString().slice(0, 16);
    }

    function normalizeDateTime(value) {
        return value ? value.replace('T', ' ') + ':00' : '';
    }
</script>
<?= $this->endSection('javascript') ?>