<?= $this->extend('layouts/base') ?>

<?= $this->section('content') ?>
<?php
$tanggal = $formData['tanggal'] ?? date('Y-m-d');
$rows = $formData['rows'] ?? [];
$tokoOptions = $formData['tokoOptions'] ?? [];
?>
<style>
    /* Styling mobile layout untuk form absensi */
    .emp-absensi-card {
        background: #ffffff;
        border: 1px solid #e2e8f0;
        border-radius: 10px;
        padding: 0.85rem;
        transition: border-color 0.15s ease, box-shadow 0.15s ease;
    }
    .emp-absensi-card:hover {
        border-color: #cbd5e1;
        box-shadow: 0 3px 8px rgba(0,0,0,0.04);
    }
    .emp-absensi-card.status-hadir {
        border-left: 4px solid #198754;
    }
    .emp-absensi-card.status-mangkir {
        border-left: 4px solid #dc3545;
    }
    .emp-absensi-card.status-libur {
        border-left: 4px solid #ffc107;
    }

    .metric-card-summary {
        border: 1px solid #e2e8f0;
        border-radius: 10px;
        background: #ffffff;
        padding: 0.75rem 1rem;
        height: 100%;
        display: flex;
        flex-direction: column;
        justify-content: center;
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

    /* Quick status pill buttons */
    .status-btn-group .btn {
        padding: 0.35rem 0.5rem;
        font-size: 0.78rem;
        font-weight: 600;
    }

    /* Floating sticky action bar at bottom of mobile view */
    .sticky-bottom-summary {
        position: sticky;
        bottom: 0;
        z-index: 1020;
        background: rgba(255, 255, 255, 0.95);
        backdrop-filter: blur(8px);
        border-top: 1px solid #e2e8f0;
        padding: 0.75rem 1rem;
        box-shadow: 0 -4px 12px rgba(0,0,0,0.06);
    }

    @media (max-width: 767.98px) {
        .btn-touch-target {
            min-height: 44px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
        }
        .metric-card-summary {
            padding: 0.6rem 0.75rem;
        }
        .metric-card-summary .metric-value {
            font-size: 1rem;
        }
    }
</style>

<div class="body-wrapper pb-5">
    <div class="container-fluid p-2 p-md-3">
        <!-- Header Page -->
        <div class="card bg-info-subtle shadow-none position-relative overflow-hidden mb-3">
            <div class="card-body px-3 py-3">
                <div class="d-flex flex-column flex-md-row align-items-start align-items-md-center justify-content-between gap-2">
                    <div>
                        <h4 class="fw-bold mb-1">Input Absensi Harian</h4>
                        <p class="mb-0 small text-muted">Isi kehadiran, lokasi penugasan toko, dan nominal gaji karyawan.</p>
                    </div>
                    <div>
                        <a href="<?= base_url('/absensi') ?>" class="btn btn-outline-secondary btn-sm btn-touch-target">
                            <i class="ti ti-arrow-left me-1"></i> Kembali ke List
                        </a>
                    </div>
                </div>
            </div>
        </div>

        <form id="form-absensi">
            <!-- Filter Tanggal & Info -->
            <div class="card border-0 shadow-sm mb-3">
                <div class="card-body p-3">
                    <div class="row g-2 align-items-center">
                        <div class="col-12 col-md-4">
                            <label class="form-label small fw-semibold text-muted text-uppercase mb-1">Tanggal Absensi</label>
                            <div class="input-group">
                                <span class="input-group-text bg-white"><i class="ti ti-calendar text-primary"></i></span>
                                <input type="date" class="form-control" id="tanggal" name="tanggal" value="<?= esc($tanggal) ?>" required>
                            </div>
                        </div>
                        <div class="col-12 col-md-8">
                            <div class="alert alert-warning border-warning-subtle mb-0 py-2 px-3 small d-flex align-items-center gap-2">
                                <i class="ti ti-info-circle fs-5 flex-shrink-0"></i>
                                <span>Status kosong tidak disimpan. Karyawan yang sudah dibayar otomatis terkunci demi integritas kas.</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- List Karyawan Card Container -->
            <div class="card border-0 shadow-sm mb-3">
                <div class="card-header bg-white border-bottom py-2 px-3 d-flex align-items-center justify-content-between">
                    <div class="d-flex align-items-center gap-2">
                        <i class="ti ti-users fs-5 text-primary"></i>
                        <h6 class="mb-0 fw-bold">Daftar Karyawan Aktif</h6>
                    </div>
                    <div class="small text-muted" id="count-employees">0 Karyawan</div>
                </div>
                <div class="card-body p-2 p-md-3">
                    <div id="employee-list" class="d-grid gap-2"></div>
                </div>
            </div>

            <!-- Ringkasan Absensi: Grid 3 kolom responsif -->
            <div class="row g-2 mb-3">
                <div class="col-4">
                    <div class="metric-card-summary">
                        <div class="metric-title"><i class="ti ti-check-circle me-1 text-primary"></i>Terisi</div>
                        <div class="metric-value text-dark" id="sum-filled">0</div>
                    </div>
                </div>
                <div class="col-4">
                    <div class="metric-card-summary">
                        <div class="metric-title"><i class="ti ti-user-check me-1 text-success"></i>Hadir</div>
                        <div class="metric-value text-success" id="sum-hadir">0</div>
                    </div>
                </div>
                <div class="col-4">
                    <div class="metric-card-summary">
                        <div class="metric-title"><i class="ti ti-wallet me-1 text-info"></i>Total Gaji</div>
                        <div class="metric-value text-primary fs-6" id="sum-gaji-display">Rp 0</div>
                        <input type="hidden" class="money" id="sum-gaji" value="0">
                    </div>
                </div>
            </div>

            <!-- Sticky Bottom Action Bar untuk Mobile & Desktop -->
            <div class="sticky-bottom-summary rounded-top">
                <div class="d-flex justify-content-between align-items-center gap-2">
                    <div>
                        <small class="text-muted d-block" style="font-size: 0.72rem; text-transform: uppercase;">Total Estimasi Gaji</small>
                        <span class="fw-bold fs-5 text-dark" id="sticky-total-gaji">Rp 0</span>
                    </div>
                    <div class="d-flex gap-2">
                        <button type="button" class="btn btn-light btn-touch-target border" onclick="window.location.href='<?= base_url('/absensi') ?>'">
                            Batal
                        </button>
                        <button type="submit" class="btn btn-primary btn-touch-target px-3">
                            <i class="ti ti-device-floppy me-1"></i> Simpan Absensi
                        </button>
                    </div>
                </div>
            </div>
        </form>
    </div>
</div>
<?= $this->endSection('content') ?>

<?= $this->section('javascript') ?>
<script>
    const tokoOptions = <?= json_encode($tokoOptions, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) ?>;
    let rows = <?= json_encode($rows, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) ?>;

    $(function() {
        $('#count-employees').text(`${rows.length} Karyawan`);
        renderRows();
        recalcSummary();
        applyMoneyMask('#form-absensi');
    });

    $('#tanggal').on('change', function() {
        const value = $(this).val();
        if (!value) return;
        window.location.href = `<?= base_url('/absensi/input') ?>/${value}`;
    });

    function renderRows() {
        const wrapper = $('#employee-list');
        wrapper.empty();

        if (!rows.length) {
            wrapper.html('<div class="text-center text-muted py-4">Belum ada karyawan dengan absensi aktif.</div>');
            return;
        }

        rows.forEach((row, idx) => {
            const tokoHtml = tokoOptions.map((toko) => `<option value="${toko.toko_id}" ${String(row.toko_id || row.home_toko_id) === String(toko.toko_id) ? 'selected' : ''}>${toko.toko_id} - ${toko.toko_nama}</option>`).join('');
            const locked = row.is_paid === 'Y';
            const statusClass = row.status_absensi === 'HADIR' ? 'status-hadir' : (row.status_absensi === 'MANGKIR' ? 'status-mangkir' : (row.status_absensi === 'LIBUR' ? 'status-libur' : ''));

            wrapper.append(`
                <div class="emp-absensi-card ${statusClass}" data-index="${idx}">
                    <!-- Baris Atas: Nama Karyawan & Status Bayar -->
                    <div class="d-flex justify-content-between align-items-start mb-2 pb-1 border-bottom">
                        <div style="min-width:0; flex:1;">
                            <span class="fw-bold text-dark fs-6">${row.fullname}</span>
                            <div class="small text-muted">
                                <span><i class="ti ti-id-badge me-1"></i>${row.karyawan_id}</span> &bull; 
                                <span>Home: <strong>${row.home_toko_id || '-'}</strong></span>
                            </div>
                        </div>
                        <div class="flex-shrink-0 ms-2">
                            ${locked ? '<span class="badge bg-success-subtle text-success border border-success"><i class="ti ti-lock me-1"></i>SUDAH DIBAYAR</span>' : '<span class="badge bg-light text-muted border">Belum Bayar</span>'}
                        </div>
                    </div>

                    <!-- Input Grid: Status, Lokasi Kerja, Gaji, Keterangan -->
                    <div class="row g-2 align-items-center">
                        <div class="col-12 col-sm-6 col-md-3">
                            <label class="form-label small fw-semibold text-muted text-uppercase mb-1">Status Kehadiran</label>
                            <select class="form-select form-select-sm row-status" ${locked ? 'disabled' : ''}>
                                <option value="">-- Pilih Status --</option>
                                <option value="HADIR" ${row.status_absensi === 'HADIR' ? 'selected' : ''}>Hadir</option>
                                <option value="MANGKIR" ${row.status_absensi === 'MANGKIR' ? 'selected' : ''}>Mangkir</option>
                                <option value="LIBUR" ${row.status_absensi === 'LIBUR' ? 'selected' : ''}>Libur</option>
                            </select>
                        </div>
                        <div class="col-12 col-sm-6 col-md-3">
                            <label class="form-label small fw-semibold text-muted text-uppercase mb-1">Lokasi Kerja</label>
                            <select class="form-select form-select-sm row-toko" ${locked ? 'disabled' : ''}>${tokoHtml}</select>
                        </div>
                        <div class="col-6 col-md-3">
                            <label class="form-label small fw-semibold text-muted text-uppercase mb-1">Nominal Gaji</label>
                            <div class="input-group input-group-sm">
                                <span class="input-group-text bg-light text-muted">Rp</span>
                                <input type="text" class="form-control form-control-sm money text-end row-gaji" value="${row.nominal_gaji || 0}" ${locked ? 'disabled' : ''}>
                            </div>
                        </div>
                        <div class="col-6 col-md-3">
                            <label class="form-label small fw-semibold text-muted text-uppercase mb-1">Keterangan</label>
                            <input type="text" class="form-control form-control-sm row-keterangan" value="${row.keterangan || ''}" maxlength="150" placeholder="Opsional" ${locked ? 'disabled' : ''}>
                        </div>
                    </div>
                </div>
            `);
        });

        applyMoneyMask('#employee-list');
    }

    $('#employee-list').on('change', '.row-status', function() {
        const card = $(this).closest('[data-index]');
        const idx = Number(card.data('index'));
        const row = rows[idx];
        const oldStatus = row.status_absensi || '';
        row.status_absensi = $(this).val();

        card.removeClass('status-hadir status-mangkir status-libur');
        if (row.status_absensi === 'HADIR') card.addClass('status-hadir');
        else if (row.status_absensi === 'MANGKIR') card.addClass('status-mangkir');
        else if (row.status_absensi === 'LIBUR') card.addClass('status-libur');

        if ((row.status_absensi === 'MANGKIR' || row.status_absensi === 'LIBUR') && oldStatus !== row.status_absensi && Number(row.nominal_gaji || 0) === Number(getDefaultGaji(row))) {
            row.nominal_gaji = 0;
            card.find('.row-gaji').val(0);
            applyMoneyMask(card);
        }
        if (row.status_absensi === 'HADIR' && Number(row.nominal_gaji || 0) <= 0) {
            row.nominal_gaji = getDefaultGaji(row);
            card.find('.row-gaji').val(row.nominal_gaji);
            applyMoneyMask(card);
        }
        recalcSummary();
    });

    $('#employee-list').on('change', '.row-toko', function() {
        const idx = Number($(this).closest('[data-index]').data('index'));
        rows[idx].toko_id = $(this).val();
    });

    $('#employee-list').on('input', '.row-gaji', function() {
        const idx = Number($(this).closest('[data-index]').data('index'));
        rows[idx].nominal_gaji = normalizeMoneyValue($(this).val());
        recalcSummary();
    });

    $('#employee-list').on('input', '.row-keterangan', function() {
        const idx = Number($(this).closest('[data-index]').data('index'));
        rows[idx].keterangan = $(this).val();
    });

    function getDefaultGaji(row) {
        return Number(row.nominal_gaji_default || row.nominal_gaji_awal || row.nominal_gaji || 0);
    }

    function recalcSummary() {
        let totalFilled = 0;
        let totalHadir = 0;
        let totalGaji = 0;
        rows.forEach((row) => {
            if (row.status_absensi) {
                totalFilled += 1;
                if (row.status_absensi === 'HADIR') {
                    totalHadir += 1;
                }
                totalGaji += Number(row.nominal_gaji || 0);
            }
        });
        $('#sum-filled').text(totalFilled.toLocaleString('id-ID'));
        $('#sum-hadir').text(totalHadir.toLocaleString('id-ID'));
        $('#sum-gaji').val(totalGaji);
        $('#sum-gaji-display').text(`Rp ${formatMoneyValue(totalGaji)}`);
        $('#sticky-total-gaji').text(`Rp ${formatMoneyValue(totalGaji)}`);
        applyMoneyMask('#sum-gaji');
    }

    $('#form-absensi').on('submit', function(e) {
        e.preventDefault();
        const payloadRows = rows.map((row, idx) => {
            const card = $(`#employee-list [data-index="${idx}"]`);
            const isPaid = row.is_paid === 'Y';
            return {
                absensi_id: row.absensi_id,
                karyawan_id: row.karyawan_id,
                status_absensi: isPaid ? row.status_absensi : card.find('.row-status').val(),
                toko_id: isPaid ? row.toko_id : card.find('.row-toko').val(),
                nominal_gaji: isPaid ? Number(row.nominal_gaji || 0) : normalizeMoneyValue(card.find('.row-gaji').val()),
                keterangan: isPaid ? (row.keterangan || '') : card.find('.row-keterangan').val()
            };
        });

        if (!payloadRows.some((row) => row.status_absensi)) {
            toastr.error('Pilih minimal satu status absensi');
            return;
        }

        $.ajax({
            type: 'PUT',
            url: '<?= base_url('/absensi') ?>',
            dataType: 'json',
            data: {
                tanggal: $('#tanggal').val(),
                rows_json: JSON.stringify(payloadRows)
            },
            success: function(res) {
                if (res.tipe === 'success') {
                    toastr.success(res.data || 'Absensi berhasil disimpan');
                    window.location.href = '<?= base_url('/absensi') ?>';
                    return;
                }
                toastr.error(res.data || 'Gagal menyimpan absensi');
            },
            error: function(xhr) {
                toastr.error(extractErrorMessage(xhr, 'Gagal menyimpan absensi'));
            }
        });
    });
</script>
<?= $this->endSection('javascript') ?>
