<?= $this->extend('layouts/base') ?>

<?= $this->section('content') ?>
<?php
$tanggal = $formData['tanggal'] ?? date('Y-m-d');
$rows = $formData['rows'] ?? [];
$tokoOptions = $formData['tokoOptions'] ?? [];
?>
<div class="body-wrapper">
    <div class="container-fluid p-0">
        <div class="card bg-info-subtle shadow-none position-relative overflow-hidden mb-4">
            <div class="card-body px-4 py-3">
                <div class="row align-items-center">
                    <div class="col-lg-8">
                        <h4 class="fw-semibold mb-2">Input Absensi</h4>
                        <p class="mb-0">Pilih status hadir, lokasi kerja, dan nominal gaji per hari. Data yang sudah dibayar akan otomatis terkunci dari edit dan hapus.</p>
                    </div>
                    <div class="col-lg-4 text-lg-end mt-3 mt-lg-0">
                        <a href="<?= base_url('/absensi') ?>" class="btn btn-outline-secondary btn-sm">Kembali ke List Absensi</a>
                    </div>
                </div>
            </div>
        </div>

        <form id="form-absensi">
            <div class="card mb-3">
                <div class="card-body">
                    <div class="row g-3 align-items-end">
                        <div class="col-md-4">
                            <label class="form-label">Tanggal</label>
                            <input type="date" class="form-control" id="tanggal" name="tanggal" value="<?= esc($tanggal) ?>" required>
                        </div>
                        <div class="col-md-8">
                            <div class="alert alert-warning border-warning-subtle mb-0">Status kosong akan dianggap tidak disimpan. Jika hari yang sama ingin diinput ulang, buka tanggalnya lagi dan ubah hanya baris yang diperlukan.</div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="card mb-3">
                <div class="card-header bg-light">
                    <h5 class="mb-0">Daftar Karyawan Absensi Aktif</h5>
                </div>
                <div class="card-body p-2">
                    <div id="employee-list" class="d-grid gap-2"></div>
                </div>
            </div>

            <div class="row g-3 mb-3">
                <div class="col-lg-4">
                    <div class="border rounded p-3 h-100">
                        <small class="text-muted">Total Baris Diisi</small>
                        <div class="fw-semibold fs-5" id="sum-filled">0</div>
                    </div>
                </div>
                <div class="col-lg-4">
                    <div class="border rounded p-3 h-100">
                        <small class="text-muted">Total Hadir</small>
                        <div class="fw-semibold fs-5" id="sum-hadir">0</div>
                    </div>
                </div>
                <div class="col-lg-4">
                    <div class="border rounded p-3 h-100">
                        <small class="text-muted">Total Gaji Hari Ini</small>
                        <input type="text" class="form-control form-control-lg money text-end fw-semibold border-0 p-0 bg-transparent" id="sum-gaji" value="0" readonly>
                    </div>
                </div>
            </div>

            <div class="d-flex gap-2 justify-content-end pb-4">
                <button type="button" class="btn btn-light" onclick="window.location.href='<?= base_url('/absensi') ?>'">Batal</button>
                <button type="submit" class="btn btn-primary">Simpan Absensi</button>
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
            wrapper.append(`
                <div class="border rounded p-2" data-index="${idx}">
                    <div class="row g-2 align-items-end">
                        <div class="col-12 col-lg-3">
                            <div class="fw-semibold">${row.fullname}</div>
                            <small class="text-muted d-block">${row.karyawan_id}</small>
                            <small class="text-muted d-block">Home: ${row.home_toko_id || '-'}</small>
                            ${locked ? '<span class="badge bg-success-subtle text-success mt-1">SUDAH DIBAYAR</span>' : ''}
                        </div>
                        <div class="col-6 col-md-3 col-lg-2">
                            <label class="form-label">Status</label>
                            <select class="form-select form-select-sm row-status" ${locked ? 'disabled' : ''}>
                                <option value="">Pilih</option>
                                <option value="HADIR" ${row.status_absensi === 'HADIR' ? 'selected' : ''}>Hadir</option>
                                <option value="MANGKIR" ${row.status_absensi === 'MANGKIR' ? 'selected' : ''}>Mangkir</option>
                                <option value="LIBUR" ${row.status_absensi === 'LIBUR' ? 'selected' : ''}>Libur</option>
                            </select>
                        </div>
                        <div class="col-6 col-md-3 col-lg-3">
                            <label class="form-label">Lokasi Kerja</label>
                            <select class="form-select form-select-sm row-toko" ${locked ? 'disabled' : ''}>${tokoHtml}</select>
                        </div>
                        <div class="col-6 col-md-3 col-lg-2">
                            <label class="form-label">Gaji</label>
                            <input type="text" class="form-control form-control-sm money text-end row-gaji" value="${row.nominal_gaji || 0}" ${locked ? 'disabled' : ''}>
                        </div>
                        <div class="col-6 col-md-3 col-lg-2">
                            <label class="form-label">Keterangan</label>
                            <input type="text" class="form-control form-control-sm row-keterangan" value="${row.keterangan || ''}" maxlength="150" ${locked ? 'disabled' : ''}>
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
