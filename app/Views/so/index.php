<?= $this->extend('layouts/base') ?>

<?= $this->section('content') ?>
<?php
/**
 * @var array  $soAktif
 * @var array  $kategoriOptions
 * @var string $akses_menu
 */
$soLabel = !empty($soAktif['tanggal'] ?? '') ? 'SO Aktif: ' . $soAktif['tanggal'] : 'Tidak Ada SO Aktif';
$hasSoAktif = !empty($soAktif['tanggal'] ?? '');
$aksesMenuData = json_decode((string) ($akses_menu ?? '{}'), true) ?: [];
$canBuatSo = ($aksesMenuData['akses_delete'] ?? '') === 'Y';
?>
<style>
    .so-nav-card {
        border: 1px solid #e2e8f0;
        border-radius: 10px;
        transition: transform 0.15s ease, box-shadow 0.15s ease, border-color 0.15s ease;
        background: #fff;
    }
    .so-nav-card:hover {
        border-color: #cbd5e1;
        box-shadow: 0 4px 12px rgba(0,0,0,0.06);
        transform: translateY(-2px);
    }
    .so-icon-circle {
        width: 44px;
        height: 44px;
        border-radius: 10px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1.35rem;
        flex-shrink: 0;
    }
    .modal-so-summary-card {
        border-radius: 8px;
        padding: 0.65rem 0.85rem;
        background: #f8fafc;
        border: 1px solid #e2e8f0;
        text-align: left;
    }
    @media (max-width: 767.98px) {
        .so-nav-card .card-body {
            padding: 0.85rem 1rem !important;
        }
    }
</style>

<div class="body-wrapper">
    <div class="container-fluid p-0">
        <div class="card bg-warning-subtle shadow-none position-relative overflow-hidden mb-3">
            <div class="card-body px-4 py-3">
                <div class="row align-items-center">
                    <div class="col-12 col-lg-8">
                        <h4 class="fw-semibold mb-1">Stock Opname</h4>
                        <p class="mb-0 text-muted small"><span class="page-pretitle fw-semibold text-dark"><?= esc($soLabel) ?></span> | Buat sesi SO, input fisik, review hasil, dan adjust stok.</p>
                    </div>
                    <div class="col-12 col-lg-4 text-start text-lg-end mt-2 mt-lg-0">
                        <span class="badge <?= $hasSoAktif ? 'bg-danger-subtle text-danger border border-danger-subtle' : 'bg-success-subtle text-success border border-success-subtle' ?> fs-2 px-3 py-2">
                            <i class="ti <?= $hasSoAktif ? 'ti-alert-circle' : 'ti-circle-check' ?> me-1"></i><?= esc($soLabel) ?>
                        </span>
                    </div>
                </div>
            </div>
        </div>

        <div class="row g-2 g-md-3">
            <?php if ($canBuatSo): ?>
                <div class="col-12 col-sm-6 col-xl-3">
                    <a href="javascript:void(0)" class="card h-100 text-decoration-none so-nav-card" onclick="createSoAll()">
                        <div class="card-body d-flex align-items-center gap-3">
                            <div class="so-icon-circle bg-primary-subtle text-primary">
                                <i class="ti ti-database-plus"></i>
                            </div>
                            <div class="min-w-0">
                                <div class="fw-bold text-dark mb-1">Buat SO All</div>
                                <div class="text-muted small lh-sm">Snapshot semua item aktif toko ke sesi SO baru.</div>
                            </div>
                        </div>
                    </a>
                </div>
                <div class="col-12 col-sm-6 col-xl-3">
                    <a href="javascript:void(0)" class="card h-100 text-decoration-none so-nav-card" onclick="openKategoriModal()">
                        <div class="card-body d-flex align-items-center gap-3">
                            <div class="so-icon-circle bg-info-subtle text-info">
                                <i class="ti ti-category"></i>
                            </div>
                            <div class="min-w-0">
                                <div class="fw-bold text-dark mb-1">Buat SO Kategori</div>
                                <div class="text-muted small lh-sm">Load snapshot item berdasarkan kategori tertentu.</div>
                            </div>
                        </div>
                    </a>
                </div>
            <?php endif; ?>
            <div class="col-12 col-sm-6 col-xl-3">
                <a href="<?= base_url('/opname/input') ?>" class="card h-100 text-decoration-none so-nav-card">
                    <div class="card-body d-flex align-items-center gap-3">
                        <div class="so-icon-circle bg-warning-subtle text-warning">
                            <i class="ti ti-edit"></i>
                        </div>
                        <div class="min-w-0">
                            <div class="fw-bold text-dark mb-1">Input SO</div>
                            <div class="text-muted small lh-sm">Input stok fisik per item pada sesi SO aktif.</div>
                        </div>
                    </div>
                </a>
            </div>
            <div class="col-12 col-sm-6 col-xl-3">
                <a href="<?= base_url('/opname/hasil') ?>" class="card h-100 text-decoration-none so-nav-card">
                    <div class="card-body d-flex align-items-center gap-3">
                        <div class="so-icon-circle bg-success-subtle text-success">
                            <i class="ti ti-file-analytics"></i>
                        </div>
                        <div class="min-w-0">
                            <div class="fw-bold text-dark mb-1">Hasil SO</div>
                            <div class="text-muted small lh-sm">Lihat selisih stok, ringkasan NK/NL, dan progres.</div>
                        </div>
                    </div>
                </a>
            </div>
            <?php if ($canBuatSo): ?>
                <div class="col-12 col-sm-6 col-xl-3">
                    <a href="<?= base_url('/opname/satuan') ?>" class="card h-100 text-decoration-none so-nav-card">
                        <div class="card-body d-flex align-items-center gap-3">
                            <div class="so-icon-circle bg-danger-subtle text-danger">
                                <i class="ti ti-adjustments"></i>
                            </div>
                            <div class="min-w-0">
                                <div class="fw-bold text-dark mb-1">SO Satuan</div>
                                <div class="text-muted small lh-sm">Adjustment manual satuan langsung ke stok.</div>
                            </div>
                        </div>
                    </a>
                </div>
            <?php endif; ?>
            <div class="col-12 col-sm-6 col-xl-3">
                <a href="<?= base_url('/opname/history') ?>" class="card h-100 text-decoration-none so-nav-card">
                    <div class="card-body d-flex align-items-center gap-3">
                        <div class="so-icon-circle bg-secondary-subtle text-secondary">
                            <i class="ti ti-history"></i>
                        </div>
                        <div class="min-w-0">
                            <div class="fw-bold text-dark mb-1">History SO</div>
                            <div class="text-muted small lh-sm">Riwayat dan arsip sesi SO toko ini.</div>
                        </div>
                    </div>
                </a>
            </div>
            <div class="col-12 col-sm-6 col-xl-3">
                <a href="javascript:void(0)" class="card h-100 text-decoration-none so-nav-card border-danger-subtle" onclick="adjustSoAll()">
                    <div class="card-body d-flex align-items-center gap-3">
                        <div class="so-icon-circle bg-danger text-white">
                            <i class="ti ti-checkup-list"></i>
                        </div>
                        <div class="min-w-0">
                            <div class="fw-bold text-danger mb-1">Adjust SO All</div>
                            <div class="text-muted small lh-sm">Eksekusi selisih SO aktif ke stok inventori.</div>
                        </div>
                    </div>
                </a>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="modal-kategori" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow">
            <div class="modal-header bg-light py-2 px-3">
                <h5 class="modal-title fw-bold fs-4 text-dark"><i class="ti ti-category text-info me-1"></i> Buat SO Kategori</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body p-3">
                <label class="form-label small fw-semibold">Pilih Kategori Produk <span class="text-danger">*</span></label>
                <select class="form-select select2" id="kat_id" multiple>
                    <?php foreach ($kategoriOptions as $row): ?>
                        <option value="<?= esc($row['id']) ?>"><?= esc($row['text']) ?></option>
                    <?php endforeach; ?>
                </select>
                <div class="form-text small text-muted mt-2">
                    Item yang akan dimuat hanya produk yang terdaftar pada kategori yang dipilih.
                </div>
            </div>
            <div class="modal-footer bg-light py-2 px-3">
                <button type="button" class="btn btn-light btn-sm px-3" data-bs-dismiss="modal">Batal</button>
                <button type="button" class="btn btn-primary btn-sm px-4 fw-semibold" onclick="createSoKategori()"><i class="ti ti-device-floppy me-1"></i> Buat Sesi SO</button>
            </div>
        </div>
    </div>
</div>
<?= $this->endSection('content') ?>

<?= $this->section('javascript') ?>
<script>
    const kategoriModal = new bootstrap.Modal(document.getElementById('modal-kategori'));
    $(function() {
        $('#kat_id').select2({
            width: '100%',
            placeholder: 'Pilih satu atau lebih kategori',
            dropdownParent: $('#modal-kategori')
        });

        <?php if (session()->getFlashdata('so_error')): ?>
            Swal.fire({
                icon: 'error',
                title: 'Perhatian',
                text: '<?= esc((string) session()->getFlashdata('so_error')) ?>'
            });
        <?php endif; ?>
    });

    function openKategoriModal() {
        $('#kat_id').val(null).trigger('change');
        kategoriModal.show();
    }

    function createSoAll() {
        Swal.fire({
            title: 'Buat SO semua produk?',
            text: 'Snapshot stok toko aktif saat ini akan dibuat untuk sesi SO baru. Pastikan tidak ada transaksi tertunda.',
            icon: 'question',
            showCancelButton: true,
            confirmButtonText: '<i class="ti ti-check"></i> Ya, buat sekarang',
            cancelButtonText: 'Batal',
            customClass: {
                confirmButton: 'btn btn-primary px-3 me-2',
                cancelButton: 'btn btn-light px-3'
            },
            buttonsStyling: false
        }).then((result) => {
            if (!result.isConfirmed) return;
            $.post('<?= base_url('/opname/create-all') ?>', function(res) {
                if (res.tipe === 'success') {
                    toastr.success(res.data || 'SO berhasil dibuat');
                    window.location.reload();
                    return;
                }
                toastr.error(res.data || 'Gagal membuat SO');
            }, 'json').fail(function(xhr) {
                toastr.error(extractErrorMessage(xhr, 'Gagal membuat SO'));
            });
        });
    }

    function createSoKategori() {
        const katId = $('#kat_id').val() || [];
        if (!katId.length) {
            toastr.error('Pilih minimal satu kategori');
            return;
        }
        $.ajax({
            type: 'POST',
            url: '<?= base_url('/opname/create-kategori') ?>',
            dataType: 'json',
            traditional: true,
            data: {
                kat_id: katId
            },
            success: function(res) {
                if (res.tipe === 'success') {
                    toastr.success(res.data || 'SO kategori berhasil dibuat');
                    kategoriModal.hide();
                    window.location.reload();
                    return;
                }
                toastr.error(res.data || 'Gagal membuat SO kategori');
            },
            error: function(xhr) {
                toastr.error(extractErrorMessage(xhr, 'Gagal membuat SO kategori'));
            }
        });
    }

    function adjustSoAll() {
        $.post('<?= base_url('/opname/summary') ?>', {
            tanggal: 'aktif'
        }, function(summary) {
            const periode = summary.sum_periode || '-';
            const sudahInput = Number(summary.sum_sudah_input || 0);
            const belumInput = Number(summary.sum_belum_input || 0);
            const totalItem = sudahInput + belumInput;

            const nkQty = Number(summary.sum_nk_qty || 0);
            const nkRp = Number(summary.sum_nk_rp || 0);

            const nlQty = Number(summary.sum_nl_qty || 0);
            const nlRp = Number(summary.sum_nl_rp || 0);

            const nklQty = Number(summary.sum_nkl_qty || 0);
            const nklRp = Number(summary.sum_nkl_rp || 0);

            // Tentukan status NKL Netto (Minus = Merah, Plus = Hijau, Nol = Netral)
            let nklBadgeClass = 'text-secondary border-secondary-subtle bg-light';
            let nklIcon = 'ti-arrows-diff';
            let nklPrefix = '';

            if (nklRp < 0 || nklQty < 0) {
                nklBadgeClass = 'text-danger border-danger-subtle bg-danger-subtle';
                nklIcon = 'ti-trending-down';
            } else if (nklRp > 0 || nklQty > 0) {
                nklBadgeClass = 'text-success border-success-subtle bg-success-subtle';
                nklIcon = 'ti-trending-up';
                nklPrefix = '+';
            }

            const htmlContent = `
                <div class="text-start">
                    <div class="p-2 mb-3 bg-light rounded border small">
                        <div class="d-flex justify-content-between mb-1">
                            <span class="text-muted">Periode SO:</span>
                            <span class="fw-bold font-monospace text-dark">${periode}</span>
                        </div>
                        <div class="d-flex justify-content-between mb-1">
                            <span class="text-muted">Progres Input:</span>
                            <span><strong>${sudahInput}</strong> sudah / <strong>${belumInput}</strong> belum (${totalItem} total)</span>
                        </div>
                    </div>

                    <div class="row g-2 mb-3">
                        <!-- NK (Kurang / Minus) -->
                        <div class="col-6">
                            <div class="modal-so-summary-card border-danger-subtle bg-danger-subtle">
                                <div class="d-flex align-items-center gap-1 text-danger fw-bold small mb-1">
                                    <i class="ti ti-minus"></i> NK (Stok Hilang/Kurang)
                                </div>
                                <div class="small text-danger mb-1">
                                    Qty: <strong class="font-monospace">${formatMoneyValue(nkQty)}</strong>
                                </div>
                                <div class="fs-3 fw-bolder text-danger font-monospace">
                                    Rp ${formatMoneyValue(nkRp)}
                                </div>
                            </div>
                        </div>

                        <!-- NL (Lebih / Plus) -->
                        <div class="col-6">
                            <div class="modal-so-summary-card border-success-subtle bg-success-subtle">
                                <div class="d-flex align-items-center gap-1 text-success fw-bold small mb-1">
                                    <i class="ti ti-plus"></i> NL (Stok Berlebih)
                                </div>
                                <div class="small text-success mb-1">
                                    Qty: <strong class="font-monospace">+${formatMoneyValue(nlQty)}</strong>
                                </div>
                                <div class="fs-3 fw-bolder text-success font-monospace">
                                    Rp ${formatMoneyValue(nlRp)}
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- NKL (Net Kurang Lebih) -->
                    <div class="p-3 rounded-3 border ${nklBadgeClass} mb-2">
                        <div class="d-flex justify-content-between align-items-center mb-1">
                            <span class="fw-bold small d-flex align-items-center gap-1">
                                <i class="ti ${nklIcon}"></i> Nilai NKL (Net Kurang / Lebih):
                            </span>
                            <span class="small font-monospace fw-semibold">
                                Qty: ${nklPrefix}${formatMoneyValue(nklQty)}
                            </span>
                        </div>
                        <div class="d-flex justify-content-between align-items-baseline">
                            <span class="small text-muted">Total Nilai Finansial</span>
                            <span class="fs-5 fw-bolder font-monospace">
                                ${nklPrefix}Rp ${formatMoneyValue(nklRp)}
                            </span>
                        </div>
                    </div>

                    ${belumInput > 0 ? `
                        <div class="alert alert-warning py-2 px-3 small d-flex align-items-center gap-2 mb-0">
                            <i class="ti ti-alert-triangle fs-5 flex-shrink-0"></i>
                            <div>Masih terdapat <strong>${belumInput} item</strong> yang belum diinput fisiknya! Item yang belum diinput tidak akan mengalami perubahan stok.</div>
                        </div>
                    ` : ''}
                </div>
            `;

            Swal.fire({
                title: 'Konfirmasi Adjust SO All',
                html: htmlContent,
                icon: 'warning',
                width: 540,
                showCancelButton: true,
                confirmButtonText: '<i class="ti ti-check"></i> Ya, Adjust Semua',
                cancelButtonText: 'Batal',
                customClass: {
                    confirmButton: 'btn btn-danger px-4 me-2',
                    cancelButton: 'btn btn-light px-3'
                },
                buttonsStyling: false
            }).then((result) => {
                if (!result.isConfirmed) return;
                $.post('<?= base_url('/opname/adjust-all') ?>', function(res) {
                    if (res.tipe === 'success') {
                        toastr.success(res.data || 'Adjust SO berhasil');
                        window.location.reload();
                        return;
                    }
                    toastr.error(res.data || 'Gagal adjust SO');
                }, 'json').fail(function(xhr) {
                    toastr.error(extractErrorMessage(xhr, 'Gagal adjust SO'));
                });
            });
        }, 'json').fail(function(xhr) {
            toastr.error(extractErrorMessage(xhr, 'Gagal membaca summary SO'));
        });
    }
</script>
<?= $this->endSection('javascript') ?>