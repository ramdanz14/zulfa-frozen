<?= $this->extend('layouts/base') ?>

<?= $this->section('content') ?>
<?php
/**
 * @var string $akses_menu
 * @var array $tokoOptions
 */
?>
<div class="body-wrapper">
    <div class="container-fluid p-0">
        <div class="card bg-primary-subtle shadow-none position-relative overflow-hidden mb-4">
            <div class="card-body px-4 py-3">
                <div class="row align-items-center">
                    <div class="col-lg-8">
                        <h4 class="fw-semibold mb-2">Saldo Usaha</h4>
                        <p class="mb-0"><span class="page-pretitle">Periode aktif</span> | Pantau laba bersih dan posisi uang usaha.</p>
                    </div>
                    <div class="col-lg-4 text-lg-end mt-3 mt-lg-0">
                        <div id="selected-store-info" class="text-muted small"></div>
                    </div>
                </div>
            </div>
        </div>

        <div class="card mb-3">
            <div class="card-body">
                <div class="row g-3 align-items-end">
                    <div class="col-lg-4">
                        <label class="form-label">Range Tanggal</label>
                        <input type="text" class="form-control" id="filter-range" readonly>
                    </div>
                    <div class="col-lg-5" id="filter-toko-wrapper" style="display:none;">
                        <label class="form-label">Filter Toko</label>
                        <select class="form-select select2" id="filter-toko" multiple>
                            <?php foreach ($tokoOptions as $row) : ?>
                                <option value="<?= esc($row['toko_id']) ?>"><?= esc($row['toko_id']) ?> - <?= esc($row['toko_nama'] ?? $row['toko_id']) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-lg-<?= !empty($tokoOptions) ? '3' : '8' ?> d-grid d-lg-flex gap-2">
                        <button type="button" class="btn btn-primary w-100" id="btn-filter">Terapkan Filter</button>
                        <button type="button" class="btn btn-light w-100" id="btn-reset">Reset</button>
                    </div>
                </div>
            </div>
        </div>
        <div class="card mb-4 border-0 shadow-sm">
            <div class="card-body p-4">
                <div id="wrapper-laba-bersih" class="d-flex flex-column flex-sm-row justify-content-between align-items-start align-items-sm-center gap-3 mb-4 p-3 bg-light-subtle border border-light-subtle rounded-3">
                    <div>
                        <div class="text-status-emphasis text-uppercase fw-bold tracking-wider small-9 label-laba">Laba Bersih Per Periode</div>
                        <div class="display-6 fw-bold text-status mt-1" id="summary-laba-bersih">Rp </div>
                    </div>
                    <span class="badge text-white px-3 py-2 fs-7 fw-bold uppercase-tracking-wider bg-secondary" id="summary-profit-status">
                        <i class="ti ti-trending-down me-1"></i> -
                    </span>
                </div>

                <div class="row g-3 mb-4">
                    <div class="col-md-4">
                        <div class="border border-light-subtle rounded-3 p-3 h-100 bg-body">
                            <div class="d-flex justify-content-between align-items-center mb-2">
                                <span class="text-muted fw-medium small">Total Sales Net</span>
                                <span class="text-primary bg-primary-subtle p-2 rounded-2 lh-1"><i class="ti ti-receipt fs-5"></i></span>
                            </div>
                            <div class="fs-4 fw-bold text-dark" id="summary-sales-net">Rp 0</div>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="border border-light-subtle rounded-3 p-3 h-100 bg-body">
                            <div class="d-flex justify-content-between align-items-center mb-2">
                                <span class="text-muted fw-medium small">Laba Kotor</span>
                                <span class="text-success bg-success-subtle p-2 rounded-2 lh-1"><i class="ti ti-cash fs-5"></i></span>
                            </div>
                            <div class="fs-4 fw-bold text-success" id="summary-laba-kotor">Rp 0</div>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="border border-light-subtle rounded-3 p-3 h-100 bg-body">
                            <div class="d-flex justify-content-between align-items-center mb-2">
                                <span class="text-muted fw-medium small">Beban + Retur</span>
                                <span class="text-danger bg-danger-subtle p-2 rounded-2 lh-1"><i class="ti ti-scale fs-5"></i></span>
                            </div>
                            <div class="fs-4 fw-bold text-danger" id="summary-pengurang">Rp 0</div>
                        </div>
                    </div>
                </div>

                <div class="bg-light rounded-3 p-3">
                    <div class="text-muted fw-bold small text-uppercase mb-2 tracking-wider">Breakdown Komponen Laba</div>
                    <ul class="list-group list-group-flush small bg-transparent" id="komponen-laba">

                    </ul>
                </div>
            </div>
        </div>

        <div class="card mb-4 border-0 shadow-sm">
            <div class="card-body p-4">
                <div class="d-flex flex-column flex-lg-row justify-content-between gap-2 mb-4">
                    <div>
                        <div class="fw-bold text-dark fs-5">Saldo Usaha Bulan Berjalan</div>
                        <div class="text-muted small">Posisi berjalan sampai <span id="summary-balance-asof" class="fw-semibold text-secondary">-</span></div>
                    </div>
                    <div class="badge bg-secondary-subtle text-secondary align-self-start lh-base text-wrap max-w-sm">Range tanggal hanya berlaku untuk filter Laba Bersih.</div>
                </div>

                <div class="row g-2 align-items-stretch text-center mb-4">
                    <div class="col-md">
                        <div class="p-3 border border-warning rounded-3 bg-warning-subtle h-100">
                            <div class="small text-warning-emphasis fw-medium mb-1"><i class="ti ti-wallet me-1"></i> Saldo Kas</div>
                            <div class="fw-bold" id="summary-saldo-kas-total">-Rp 0</div>
                            <div class="text-muted small-9 mt-1">Tunai: <span id="summary-kas-tunai">0</span> | NonTunai: <span id="summary-kas-nontunai">0</span></div>
                        </div>
                    </div>
                    <div class="col-auto d-flex align-items-center justify-content-center px-1"><span class="fs-4 fw-bold text-muted">-</span></div>
                    <div class="col-md">
                        <div class="p-3 border border-danger rounded-3 bg-danger-subtle h-100">
                            <div class="small text-danger fw-medium mb-1"><i class="ti ti-receipt-2 me-1"></i> Saldo Hutang</div>
                            <div class="fw-bold fs-5 text-danger" id="summary-hutang">Rp 0</div>
                        </div>
                    </div>
                    <div class="col-auto d-flex align-items-center justify-content-center px-1"><span class="fs-4 fw-bold text-muted">+</span></div>
                    <div class="col-md">
                        <div class="p-3 border border-success rounded-3 bg-success-subtle h-100">
                            <div class="small text-success fw-medium mb-1"><i class="ti ti-cash-banknote me-1"></i> Saldo Piutang</div>
                            <div class="fw-bold fs-5 text-success" id="summary-piutang">Rp 0</div>
                        </div>
                    </div>
                    <div class="col-auto d-flex align-items-center justify-content-center px-1"><span class="fs-4 fw-bold text-muted">-</span></div>
                    <div class="col-md">
                        <div class="p-3 border border-success rounded-3 bg-success-subtle h-100">
                            <div class="small text-success fw-medium mb-1"><i class="ti ti-packages me-1"></i> Saldo Stok</div>
                            <div class="fw-bold fs-5 text-success" id="summary-stok">Rp 0</div>
                        </div>
                    </div>
                    <div class="col-auto d-flex align-items-center justify-content-center px-1"><span class="fs-4 fw-bold text-muted">=</span></div>
                    <div class="col-md-3">
                        <div class="p-3 border border-secondary rounded-3 bg-dark text-white h-100 shadow-sm">
                            <div class="small opacity-75 fw-medium mb-1"><i class="ti ti-scale me-1"></i> Saldo Akhir</div>
                            <div class="fs-6 fw-bold" id="summary-saldo-akhir">-Rp 0</div>
                        </div>
                    </div>
                </div>


                <div class="p-2.5 bg-light rounded-3 text-center text-muted border border-light-subtle small font-monospace" id="summary-formula">
                    Rp 0 - Rp 0 + Rp 0 - Rp 0 = Rp 0
                </div>
            </div>
        </div>

        <div class="row g-3">
            <div class="col-lg-6">
                <div class="card h-100  shadow-sm border-start border-4 border-warning">
                    <div class="card-body p-4">
                        <div class="d-flex align-items-center gap-2 mb-2">
                            <span class="p-2 bg-warning-subtle text-warning-emphasis rounded-3 lh-1"><i class="ti ti-report-money fs-4"></i></span>
                            <div>
                                <h6 class="fw-bold mb-0 text-dark">Rasio Kas (Cash Ratio)</h6>
                                <span class="text-muted small-9">Mengukur kemampuan kas saat ini untuk melunasi seluruh hutang.</span>
                            </div>
                        </div>

                        <div class="my-3 p-3 bg-light rounded-3 d-flex align-items-center justify-content-between">
                            <span class="small font-monospace text-muted">Rasio = Kas ÷ Hutang</span>
                            <div class="text-end">
                                <div class="fs-4 fw-bold " id="summary-cash-ratio">0</div>
                                <span class="badge bg-secondary small-9" id="summary-cash-ratio-psn">0</span>
                            </div>
                        </div>

                        <div class="d-flex gap-2 p-2.5 bg-warning-subtle border border-warning-subtle rounded-3 text-warning-emphasis small">
                            <i class="ti ti-info-circle-filled fs-5 mt-0.5 shrink-0"></i>
                            <div>
                                <strong>Indikasi:</strong> angka 1 atau 100% berarti kas sama persis dengan hutang. Ideal sehat sering berada di sekitar<span class="fw-semibold"> 20% - 50% </span>karena piutang dan barang juga bisa menjadi sumber dana.
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-lg-6">
                <div class="card h-100 shadow-sm border-start border-4 border-info">
                    <div class="card-body p-4">
                        <div class="d-flex align-items-center gap-2 mb-2">
                            <span class="p-2 bg-info-subtle text-info rounded-3 align-middle lh-1"><i class="ti ti-activity fs-4"></i></span>
                            <div>
                                <h6 class="fw-bold mb-0 text-dark">Rasio Lancar (Current Ratio)</h6>
                                <span class="text-muted small-9">Mengukur kemampuan aset lancar untuk membayar seluruh hutang.</span>
                            </div>
                        </div>

                        <div class="my-3 p-3 bg-light rounded-3 d-flex align-items-center justify-content-between">
                            <span class="small font-monospace text-muted">Rasio = (Kas + Stok + Piutang) ÷ Hutang</span>
                            <div class="text-end">
                                <div class="fs-4 fw-bold " id="summary-current-ratio">0</div>
                                <span class="badge bg-secondary small-9" id="summary-current-ratio-psn">0</span>
                            </div>
                        </div>

                        <div class="d-flex gap-2 p-2.5 bg-info-subtle border border-info-subtle rounded-3 text-info-emphasis small">
                            <i class="ti ti-alert-triangle-filled fs-5 mt-0.5 shrink-0"></i>
                            <div>
                                <strong>Tips Keamanan:</strong> Rasio ideal berada di angka <span class="fw-semibold">1.0x hingga 2.0x</span>. Semakin tinggi nilainya, semakin besar penyangga aset lancar terhadap hutang. Nilai terlalu tinggi tetap perlu dibaca bersama perputaran stok dan piutang.
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
    const akses_menu = <?= $akses_menu ?>;
    const canMultiStore = akses_menu?.akses_delete === 'Y';
    const sessionTokoId = '<?= esc((string) session('toko_id')) ?>';
    let filterStart = moment().startOf('month');
    let filterEnd = moment().endOf('month');

    $(function() {
        if (canMultiStore) {
            $('#filter-toko-wrapper').show();
            $('#filter-toko').select2({
                width: '100%',
                placeholder: 'Pilih satu atau banyak toko'
            });
        }

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
                'Kemarin': [moment().subtract(1, 'days'), moment().subtract(1, 'days')],
                '1 Minggu': [moment().subtract(6, 'days'), moment()],
                '1 Bulan': [moment().subtract(1, 'month').add(1, 'days'), moment()],
                'Minggu Ini': [moment().startOf('week'), moment().endOf('week')],
                'Minggu Lalu': [moment().subtract(1, 'week').startOf('week'), moment().subtract(1, 'week').endOf('week')],
                'Bulan Ini': [moment().startOf('month'), moment().endOf('month')],
                'Bulan Lalu': [moment().subtract(1, 'month').startOf('month'), moment().subtract(1, 'month').endOf('month')]
            }
        }, function(start, end) {
            filterStart = start;
            filterEnd = end;
        });

        $('#filter-range').val(`${filterStart.format('DD/MM/YYYY')} - ${filterEnd.format('DD/MM/YYYY')}`);
        updateStoreInfo();
        refreshReport();
    });

    function getSelectedStoreIds() {
        if (!canMultiStore) {
            return [sessionTokoId];
        }
        return ($('#filter-toko').val() || []).filter(Boolean);
    }

    function updateStoreInfo() {
        const selected = getSelectedStoreIds();
        if (!canMultiStore) {
            $('#selected-store-info').text(`Toko aktif: ${sessionTokoId}`);
            return;
        }
        $('#selected-store-info').text(selected.length ? `Toko dipilih: ${selected.join(', ')}` : 'Toko: semua toko');
    }



    $('#btn-filter').on('click', function() {
        updateStoreInfo();
        refreshReport();
    });

    $('#btn-reset').on('click', function() {
        filterStart = moment().startOf('month');
        filterEnd = moment().endOf('month');
        $('#filter-range').data('daterangepicker').setStartDate(filterStart);
        $('#filter-range').data('daterangepicker').setEndDate(filterEnd);
        $('#filter-range').val(`${filterStart.format('DD/MM/YYYY')} - ${filterEnd.format('DD/MM/YYYY')}`);
        if (canMultiStore) {
            $('#filter-toko').val(null).trigger('change');
        }
        updateStoreInfo();
        refreshReport();
    });

    if (canMultiStore) {
        $('#filter-toko').on('change', updateStoreInfo);
    }

    function refreshReport() {
        $.ajax({
            type: 'POST',
            url: '<?= base_url('/saldousaha/report') ?>',
            dataType: 'json',
            data: {
                date_start: filterStart.format('YYYY-MM-DD'),
                date_end: filterEnd.format('YYYY-MM-DD'),
                toko_ids: getSelectedStoreIds()
            },
            success: function(res) {
                const data = res?.data || {};
                const summary = data.summary || {};
                const period = data.period || {};
                const labaBersih = Number(summary.laba_bersih || 0);
                const saldoKas = Number(summary.saldo_kas_total || 0);
                const hutang = Number(summary.total_hutang || 0);
                const piutang = Number(summary.total_piutang || 0);
                const stok = Number(summary.total_stok_rupiah || 0);
                const saldoAkhir = Number(summary.saldo_akhir || 0);

                $('#summary-laba-bersih').text(`Rp ${formatMoneyValue(labaBersih)}`);
                $('#summary-laba-kotor').text(`Rp ${formatMoneyValue(summary.laba_kotor || 0)}`);
                $('#summary-pengurang').text(`Rp ${formatMoneyValue((Number(summary.retur_penjualan || 0) + Number(summary.total_beban || 0)))}`);
                $('#summary-saldo-akhir').text(renderMoneyPlain(saldoAkhir));
                $('#summary-saldo-kas-total').text(renderMoneyPlain(saldoKas));
                $('#summary-kas-tunai').text(`Rp ${formatMoneyValue(summary.saldo_kas_tunai || 0)}`);
                $('#summary-kas-nontunai').text(`Rp ${formatMoneyValue(summary.saldo_kas_nontunai || 0)}`);
                $('#summary-sales-net').text(`Rp ${formatMoneyValue(summary.total_sales_net || 0)}`);
                $('#summary-hutang').text(renderMoneyPlain(hutang));
                $('#summary-piutang').text(renderMoneyPlain(piutang));
                $('#summary-stok').text(renderMoneyPlain(stok));
                $('#summary-balance-asof').text(period.balance_as_of ? moment(period.balance_as_of, 'YYYY-MM-DD').format('DD/MM/YYYY') : '-');
                $('#summary-formula').html(`(${renderMoneyPlain(saldoKas)}) - (${renderMoneyPlain(hutang)}) + (${renderMoneyPlain(piutang)}) - (${renderMoneyPlain(stok)}) = <strong>${renderMoneyPlain(saldoAkhir)}</strong>`);
                $('#summary-cash-ratio').text(formatRatio(summary.cash_ratio));
                $('#summary-cash-ratio-psn').text(formatRatioPsn(summary.cash_ratio));
                $('#summary-current-ratio').text(formatRatio(summary.current_ratio));
                $('#summary-current-ratio-psn').text(formatRatioPsn(summary.current_ratio));

                updateProfitStatus(labaBersih);
                $("#komponen-laba").empty();
                data.profit_rows.map((x) => {
                    let classText = '';
                    switch (x.type) {
                        case 'in':
                            classText = 'text-success'
                            break;

                        case 'out':
                            classText = 'text-danger'
                            break;

                        default:
                            classText = 'text-dark'
                            break;
                    }
                    $("#komponen-laba").append(`<li class="list-group-item d-flex justify-content-between align-items-center bg-transparent px-0 py-2">
                            <span>${x.label}</span> <span class="fw-semibold ${classText}">Rp ${formatMoneyValue(x.amount || 0)}</span>
                        </li>`);

                });

                $('.page-pretitle').text(`${filterStart.format('DD/MM/YYYY')} - ${filterEnd.format('DD/MM/YYYY')}`);
            },
            error: function(xhr) {
                toastr.error(extractErrorMessage(xhr, 'Gagal memuat saldo usaha'));
            }
        });
    }

    function updateProfitStatus(value) {
        const $wrapper = $('#wrapper-laba-bersih');
        const $label = $wrapper.find('.label-laba');
        const $angka = $('#summary-laba-bersih');
        const $badge = $('#summary-profit-status');

        // 1. Bersihkan semua class warna bawaan Bootstrap sebelumnya
        $wrapper.removeClass('bg-success-subtle border-success-subtle bg-danger-subtle border-danger-subtle bg-light border-light-subtle');
        $label.removeClass('text-success-emphasis text-danger-emphasis text-muted');
        $angka.removeClass('text-success text-danger text-dark');
        $badge.removeClass('bg-success bg-danger bg-secondary');

        // 2. Terapkan class baru berdasarkan kondisi nilai keuangan
        if (value > 0) {
            // Kondisi UNTUNG (Hijau)
            $wrapper.addClass('bg-success-subtle border-success-subtle');
            $label.addClass('text-success-emphasis');
            $angka.addClass('text-success');
            $badge.addClass('bg-success').html('<i class="ti ti-trending-up me-1"></i> UNTUNG');

        } else if (value < 0) {
            // Kondisi RUGI (Merah)
            $wrapper.addClass('bg-danger-subtle border-danger-subtle');
            $label.addClass('text-danger-emphasis');
            $angka.addClass('text-danger');
            $badge.addClass('bg-danger').html('<i class="ti ti-trending-down me-1"></i> RUGI');

        } else {
            // Kondisi IMPAS / Nol (Abu-abu / Netral)
            $wrapper.addClass('bg-light border-light-subtle');
            $label.addClass('text-muted');
            $angka.addClass('text-dark');
            $badge.addClass('bg-secondary').html('<i class="ti ti-minus me-1"></i> IMPAS');
        }
    }

    function renderMoney(data) {
        const amount = Number(data || 0);
        const prefix = amount < 0 ? '-Rp ' : 'Rp ';
        return prefix + formatMoneyValue(Math.abs(amount));
    }

    function renderMoneyPlain(value) {
        const amount = Number(value || 0);
        const prefix = amount < 0 ? '-Rp ' : 'Rp ';
        return prefix + formatMoneyValue(Math.abs(amount));
    }

    function formatRatio(value) {
        if (value === null || value === undefined || value === '') {
            return 'Tidak ada hutang';
        }
        const ratio = Number(value || 0);
        return `${ratio.toLocaleString('id-ID', {
            minimumFractionDigits: 2,
            maximumFractionDigits: 2
        })}x `;
    }

    function formatRatioPsn(value) {
        if (value === null || value === undefined || value === '') {
            return 'Tidak ada hutang';
        }
        const ratio = Number(value || 0);
        return `${(ratio * 100).toLocaleString('id-ID', {
            minimumFractionDigits: 2,
            maximumFractionDigits: 2
        })}%`;
    }
</script>
<?= $this->endSection('javascript') ?>