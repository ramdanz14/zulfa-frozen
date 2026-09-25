<?= $this->extend('layouts/base') ?>

<?= $this->section('content') ?>
<?php
/**
 * @var string $akses_menu
 * @var array $tokoOptions
 */
?>
<style>
    .btn-touch-target {
        min-height: 44px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        font-weight: 500;
    }
    .metric-card-usaha {
        border-radius: 12px;
        transition: transform 0.15s ease, box-shadow 0.15s ease;
    }
    .balance-component-card {
        border-radius: 10px;
        padding: 12px;
        text-align: center;
        position: relative;
        height: 100%;
    }
    .operator-pill {
        width: 32px;
        height: 32px;
        display: flex;
        align-items: center;
        justify-content: center;
        border-radius: 50%;
        background: #e2e8f0;
        color: #475569;
        font-weight: bold;
        font-size: 1.1rem;
        margin: auto;
    }
    @media (max-width: 767.98px) {
        .metric-title {
            font-size: 0.78rem;
            line-height: 1.2;
        }
        .metric-value {
            font-size: 1.15rem !important;
            word-break: break-word;
        }
        .display-6-mobile {
            font-size: 1.65rem !important;
        }
        .operator-pill {
            width: 26px;
            height: 26px;
            font-size: 0.95rem;
            margin: 4px auto;
        }
    }
</style>

<div class="body-wrapper">
    <div class="container-fluid p-0">
        <!-- Header -->
        <div class="card bg-primary-subtle shadow-none position-relative overflow-hidden mb-3">
            <div class="card-body px-3 px-md-4 py-3">
                <div class="row align-items-center">
                    <div class="col-12 col-md-7">
                        <h4 class="fw-semibold mb-1">Saldo Usaha</h4>
                        <p class="mb-0 text-muted small"><span class="page-pretitle">Periode aktif</span> | Pantau laba bersih & posisi uang usaha.</p>
                    </div>
                    <div class="col-12 col-md-5 text-md-end mt-2 mt-md-0">
                        <div id="selected-store-info" class="badge bg-primary text-wrap text-start text-md-end" style="font-weight: 500;"></div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Filter Card -->
        <div class="card mb-3">
            <div class="card-body p-3 p-md-4">
                <div class="row g-2 g-md-3 align-items-end">
                    <div class="col-12 col-sm-6 col-lg-4">
                        <label class="form-label small fw-medium mb-1">Range Tanggal Laba</label>
                        <input type="text" class="form-control" id="filter-range" readonly>
                    </div>
                    <div class="col-12 col-sm-6 col-lg-5" id="filter-toko-wrapper" style="display:none;">
                        <label class="form-label small fw-medium mb-1">Filter Toko</label>
                        <select class="form-select select2" id="filter-toko" multiple>
                            <?php foreach ($tokoOptions as $row) : ?>
                                <option value="<?= esc($row['toko_id']) ?>"><?= esc($row['toko_id']) ?> - <?= esc($row['toko_nama'] ?? $row['toko_id']) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-12 col-lg-<?= !empty($tokoOptions) ? '3' : '8' ?>">
                        <div class="row g-2">
                            <div class="col-6 col-sm-6">
                                <button type="button" class="btn btn-primary w-100 btn-touch-target" id="btn-filter">
                                    <i class="ti ti-filter me-1"></i> Filter
                                </button>
                            </div>
                            <div class="col-6 col-sm-6">
                                <button type="button" class="btn btn-light border w-100 btn-touch-target" id="btn-reset">
                                    <i class="ti ti-rotate me-1"></i> Reset
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- 1. Card Laba Bersih Per Periode -->
        <div class="card mb-3 border-0 shadow-sm">
            <div class="card-body p-3 p-md-4">
                <div id="wrapper-laba-bersih" class="d-flex flex-column flex-sm-row justify-content-between align-items-start align-items-sm-center gap-2 mb-3 p-3 bg-light-subtle border border-light-subtle rounded-3">
                    <div>
                        <div class="text-status-emphasis text-uppercase fw-bold tracking-wider small label-laba">Laba Bersih Per Periode</div>
                        <div class="display-6 display-6-mobile fw-bold text-status mt-1" id="summary-laba-bersih">Rp 0</div>
                    </div>
                    <span class="badge text-white px-3 py-2 fs-7 fw-bold uppercase-tracking-wider bg-secondary align-self-start align-self-sm-center" id="summary-profit-status">
                        <i class="ti ti-trending-down me-1"></i> -
                    </span>
                </div>

                <!-- 3 Kartu Komponen Laba: Sales Net, Laba Kotor, Pengurang -->
                <div class="row g-2 g-md-3 mb-3">
                    <div class="col-12 col-md-4">
                        <div class="border border-light-subtle rounded-3 p-3 h-100 bg-body">
                            <div class="d-flex justify-content-between align-items-center mb-1">
                                <span class="text-muted fw-medium small">Total Sales Net</span>
                                <span class="text-primary bg-primary-subtle p-1.5 rounded-2 lh-1"><i class="ti ti-receipt fs-5"></i></span>
                            </div>
                            <div class="fs-4 fw-bold text-dark metric-value" id="summary-sales-net">Rp 0</div>
                        </div>
                    </div>
                    <div class="col-6 col-md-4">
                        <div class="border border-light-subtle rounded-3 p-3 h-100 bg-body">
                            <div class="d-flex justify-content-between align-items-center mb-1">
                                <span class="text-muted fw-medium small">Laba Kotor</span>
                                <span class="text-success bg-success-subtle p-1.5 rounded-2 lh-1"><i class="ti ti-cash fs-5"></i></span>
                            </div>
                            <div class="fs-4 fw-bold text-success metric-value" id="summary-laba-kotor">Rp 0</div>
                        </div>
                    </div>
                    <div class="col-6 col-md-4">
                        <div class="border border-light-subtle rounded-3 p-3 h-100 bg-body">
                            <div class="d-flex justify-content-between align-items-center mb-1">
                                <span class="text-muted fw-medium small">Beban + Retur</span>
                                <span class="text-danger bg-danger-subtle p-1.5 rounded-2 lh-1"><i class="ti ti-scale fs-5"></i></span>
                            </div>
                            <div class="fs-4 fw-bold text-danger metric-value" id="summary-pengurang">Rp 0</div>
                        </div>
                    </div>
                </div>

                <!-- Accordion/Collapse Breakdown Komponen Laba -->
                <div class="card mb-0 border border-light-subtle">
                    <div class="card-header bg-transparent p-2.5 cursor-pointer" data-bs-toggle="collapse" data-bs-target="#collapseKomponenLaba" aria-expanded="false" style="cursor:pointer;">
                        <div class="d-flex justify-content-between align-items-center">
                            <span class="small fw-bold text-uppercase text-muted"><i class="ti ti-list-details me-1 text-primary"></i> Rincian Breakdown Komponen Laba</span>
                            <span class="badge bg-primary-subtle text-primary small"><i class="ti ti-chevron-down"></i> Buka/Tutup</span>
                        </div>
                    </div>
                    <div class="collapse show" id="collapseKomponenLaba">
                        <div class="card-body p-3 pt-0">
                            <ul class="list-group list-group-flush small bg-transparent" id="komponen-laba"></ul>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- 2. Saldo Usaha Bulan Berjalan (Persamaan Neraca) -->
        <div class="card mb-3 border-0 shadow-sm">
            <div class="card-body p-3 p-md-4">
                <div class="d-flex flex-column flex-md-row justify-content-between gap-1 mb-3">
                    <div>
                        <div class="fw-bold text-dark fs-5">Saldo Usaha Bulan Berjalan</div>
                        <div class="text-muted small">Posisi aset berjalan sampai: <span id="summary-balance-asof" class="fw-semibold text-primary">-</span></div>
                    </div>
                    <div class="badge bg-secondary-subtle text-secondary align-self-start lh-base text-wrap" style="max-width: 320px;">
                        Posisi kas, stok, hutang, dan piutang akumulatif berjalan.
                    </div>
                </div>

                <!-- Tampilan Formula Responsive: Kartu di Mobile & Desktop -->
                <div class="row g-2 align-items-center mb-3">
                    <!-- Kas -->
                    <div class="col-6 col-md">
                        <div class="balance-component-card border border-warning bg-warning-subtle">
                            <div class="small text-warning-emphasis fw-medium mb-1 text-truncate"><i class="ti ti-wallet me-1"></i> Saldo Kas</div>
                            <div class="fw-bold fs-6 text-dark" id="summary-saldo-kas-total">Rp 0</div>
                            <div class="text-muted small mt-1" style="font-size:0.75rem;">Tunai: <span id="summary-kas-tunai">0</span><br>NonTunai: <span id="summary-kas-nontunai">0</span></div>
                        </div>
                    </div>
                    <div class="col-12 col-md-auto d-none d-md-flex align-items-center justify-content-center px-1">
                        <span class="operator-pill">+</span>
                    </div>
                    <!-- Stok -->
                    <div class="col-6 col-md">
                        <div class="balance-component-card border border-success bg-success-subtle">
                            <div class="small text-success fw-medium mb-1 text-truncate"><i class="ti ti-packages me-1"></i> Saldo Stok</div>
                            <div class="fw-bold fs-6 text-success" id="summary-stok">Rp 0</div>
                            <div class="text-muted small mt-1" style="font-size:0.75rem;">Nilai modal gudang</div>
                        </div>
                    </div>
                    <div class="col-12 col-md-auto d-none d-md-flex align-items-center justify-content-center px-1">
                        <span class="operator-pill">-</span>
                    </div>
                    <!-- Hutang -->
                    <div class="col-6 col-md">
                        <div class="balance-component-card border border-danger bg-danger-subtle">
                            <div class="small text-danger fw-medium mb-1 text-truncate"><i class="ti ti-receipt-2 me-1"></i> Saldo Hutang</div>
                            <div class="fw-bold fs-6 text-danger" id="summary-hutang">Rp 0</div>
                            <div class="text-muted small mt-1" style="font-size:0.75rem;">Kewajiban supplier</div>
                        </div>
                    </div>
                    <div class="col-12 col-md-auto d-none d-md-flex align-items-center justify-content-center px-1">
                        <span class="operator-pill">+</span>
                    </div>
                    <!-- Piutang -->
                    <div class="col-6 col-md">
                        <div class="balance-component-card border border-success bg-success-subtle">
                            <div class="small text-success fw-medium mb-1 text-truncate"><i class="ti ti-cash-banknote me-1"></i> Saldo Piutang</div>
                            <div class="fw-bold fs-6 text-success" id="summary-piutang">Rp 0</div>
                            <div class="text-muted small mt-1" style="font-size:0.75rem;">Tagihan customer</div>
                        </div>
                    </div>
                    <div class="col-12 col-md-auto d-none d-md-flex align-items-center justify-content-center px-1">
                        <span class="operator-pill">=</span>
                    </div>
                    <!-- Saldo Akhir -->
                    <div class="col-12 col-md-3">
                        <div class="balance-component-card border border-dark bg-dark text-white shadow-sm">
                            <div class="small opacity-75 fw-medium mb-1"><i class="ti ti-scale me-1"></i> Saldo Akhir Usaha</div>
                            <div class="fs-5 fw-bold text-white" id="summary-saldo-akhir">Rp 0</div>
                            <div class="text-white-50 small mt-1" style="font-size:0.75rem;">Ekuitas bersih usaha</div>
                        </div>
                    </div>
                </div>

                <div class="p-2.5 bg-light rounded-3 text-center text-muted border border-light-subtle small font-monospace text-wrap" id="summary-formula">
                    Rp 0 + Rp 0 - Rp 0 + Rp 0 = Rp 0
                </div>
            </div>
        </div>

        <!-- 3. Rasio Finansial: Kas Ratio & Current Ratio (Stacked / 2 Col) -->
        <div class="row g-2 g-md-3 mb-3">
            <div class="col-12 col-lg-6">
                <div class="card h-100 shadow-sm border-start border-4 border-warning mb-0">
                    <div class="card-body p-3 p-md-4">
                        <div class="d-flex align-items-center gap-2 mb-2">
                            <span class="p-2 bg-warning-subtle text-warning-emphasis rounded-3 lh-1"><i class="ti ti-report-money fs-4"></i></span>
                            <div>
                                <h6 class="fw-bold mb-0 text-dark">Rasio Kas (Cash Ratio)</h6>
                                <span class="text-muted small">Kemampuan kas saat ini untuk melunasi seluruh hutang.</span>
                            </div>
                        </div>

                        <div class="my-2 p-3 bg-light rounded-3 d-flex align-items-center justify-content-between">
                            <span class="small font-monospace text-muted">Kas ÷ Hutang</span>
                            <div class="text-end">
                                <div class="fs-4 fw-bold" id="summary-cash-ratio">0</div>
                                <span class="badge bg-secondary small" id="summary-cash-ratio-psn">0</span>
                            </div>
                        </div>

                        <div class="d-flex gap-2 p-2 bg-warning-subtle border border-warning-subtle rounded-3 text-warning-emphasis small mt-2">
                            <i class="ti ti-info-circle-filled fs-5 mt-0.5 shrink-0"></i>
                            <div>
                                <strong>Indikasi:</strong> Angka 1 atau 100% berarti kas sama persis dengan hutang. Ideal sehat berada di sekitar <span class="fw-semibold">20% - 50%</span>.
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-12 col-lg-6">
                <div class="card h-100 shadow-sm border-start border-4 border-info mb-0">
                    <div class="card-body p-3 p-md-4">
                        <div class="d-flex align-items-center gap-2 mb-2">
                            <span class="p-2 bg-info-subtle text-info rounded-3 align-middle lh-1"><i class="ti ti-activity fs-4"></i></span>
                            <div>
                                <h6 class="fw-bold mb-0 text-dark">Rasio Lancar (Current Ratio)</h6>
                                <span class="text-muted small">Kemampuan aset lancar untuk membayar seluruh hutang.</span>
                            </div>
                        </div>

                        <div class="my-2 p-3 bg-light rounded-3 d-flex align-items-center justify-content-between">
                            <span class="small font-monospace text-muted">(Kas + Stok + Piutang) ÷ Hutang</span>
                            <div class="text-end">
                                <div class="fs-4 fw-bold" id="summary-current-ratio">0</div>
                                <span class="badge bg-secondary small" id="summary-current-ratio-psn">0</span>
                            </div>
                        </div>

                        <div class="d-flex gap-2 p-2 bg-info-subtle border border-info-subtle rounded-3 text-info-emphasis small mt-2">
                            <i class="ti ti-alert-triangle-filled fs-5 mt-0.5 shrink-0"></i>
                            <div>
                                <strong>Tips Keamanan:</strong> Rasio ideal berada di angka <span class="fw-semibold">1.0x hingga 2.0x</span> sebagai penyangga aset lancar terhadap hutang lancar.
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
                $('#summary-formula').html(`(${renderMoneyPlain(saldoKas)}) + (${renderMoneyPlain(stok)}) - (${renderMoneyPlain(hutang)}) + (${renderMoneyPlain(piutang)}) = <strong>${renderMoneyPlain(saldoAkhir)}</strong>`);
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