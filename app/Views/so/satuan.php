<?= $this->extend('layouts/base') ?>

<?= $this->section('content') ?>
<style>
    /* Styling responsif mobile untuk SO Satuan & CardView */
    .so-satuan-card {
        background: #ffffff;
        border: 1px solid #e2e8f0;
        border-radius: 10px;
        padding: 0.85rem 1rem;
        transition: transform 0.15s ease, box-shadow 0.15s ease;
    }
    .so-satuan-card:hover {
        border-color: #cbd5e1;
        box-shadow: 0 4px 12px rgba(0,0,0,0.05);
    }
    .so-item-code-badge {
        font-family: var(--bs-font-monospace);
        font-size: 0.75rem;
        background-color: var(--bs-tertiary-bg);
        color: var(--bs-secondary-color);
        padding: 0.15rem 0.45rem;
        border-radius: 0.25rem;
        display: inline-flex;
        align-items: center;
        gap: 0.25rem;
        border: 1px solid var(--bs-border-color);
    }
    .so-card-title {
        font-weight: 700;
        color: var(--bs-heading-color);
        line-height: 1.3;
        font-size: 0.95rem;
    }
    .so-metric-box {
        background-color: #f8fafc;
        border: 1px solid #e2e8f0;
        border-radius: 6px;
        padding: 0.4rem 0.6rem;
    }
</style>

<div class="body-wrapper">
    <div class="container-fluid p-0">
        <div class="card bg-danger-subtle shadow-none position-relative overflow-hidden mb-3">
            <div class="card-body px-4 py-3">
                <div class="row align-items-center">
                    <div class="col-12 col-lg-8">
                        <h4 class="fw-semibold mb-1">Adjust SO Satuan</h4>
                        <p class="mb-0 text-muted small"><span class="page-pretitle fw-semibold text-dark">Closing Aktif: <?= esc($closingDate ?? '-') ?></span> | Adjustment manual satuan langsung tersimpan ke stok inventori.</p>
                    </div>
                    <div class="col-12 col-lg-4 text-start text-lg-end mt-2 mt-lg-0">
                        <a href="<?= base_url('/opname') ?>" class="btn btn-outline-secondary btn-sm px-3"><i class="ti ti-arrow-left me-1"></i> Kembali ke Menu SO</a>
                    </div>
                </div>
            </div>
        </div>

        <div class="card border">
            <div class="card-body p-2 p-md-3">
                <table id="table-data" class="table table-bordered table-hover table-striped table-sm align-middle w-100 mb-0">
                    <thead class="table-light"></thead>
                    <tbody>
                        <tr>
                            <td>No data to show</td>
                        </tr>
                    </tbody>
                </table>

                <!-- CardView Template for SO Satuan (Mobile Reflow) -->
                <template id="card-so-satuan-template">
                    <div class="so-satuan-card mb-2">
                        <!-- Baris 1: Nama Item, Kode & Action -->
                        <div class="d-flex justify-content-between align-items-start gap-2 mb-2 pb-2 border-bottom">
                            <div class="min-w-0 flex-grow-1">
                                <div class="so-card-title text-truncate" data-dtcv-field="2"></div>
                                <div class="d-flex align-items-center gap-1 mt-1 flex-wrap">
                                    <span class="so-item-code-badge"><i class="ti ti-barcode"></i><span data-dtcv-field="1"></span></span>
                                    <span class="badge bg-light text-dark border small" data-dtcv-field="3"></span>
                                    <span class="small text-muted font-monospace"><i class="ti ti-calendar me-1"></i><span data-dtcv-field="0"></span></span>
                                </div>
                            </div>
                            <div class="flex-shrink-0" data-dtcv-field="8"></div>
                        </div>

                        <!-- Baris 2: Detail Qty, Harga & Nilai Gross -->
                        <div class="row g-2 mb-2">
                            <div class="col-4">
                                <div class="so-metric-box text-center">
                                    <div class="text-muted" style="font-size: 0.72rem;">Qty Adjust</div>
                                    <div class="fw-bold font-monospace text-dark" data-dtcv-field="4"></div>
                                </div>
                            </div>
                            <div class="col-4">
                                <div class="so-metric-box text-center">
                                    <div class="text-muted" style="font-size: 0.72rem;">Harga Satuan</div>
                                    <div class="fw-semibold font-monospace small" data-dtcv-field="5"></div>
                                </div>
                            </div>
                            <div class="col-4">
                                <div class="so-metric-box text-center">
                                    <div class="text-muted" style="font-size: 0.72rem;">Gross Total</div>
                                    <div class="fw-bold font-monospace text-dark small" data-dtcv-field="6"></div>
                                </div>
                            </div>
                        </div>

                        <!-- Baris 3: Keterangan -->
                        <div class="d-flex align-items-center justify-content-between pt-2 border-top small">
                            <span class="text-muted">Keterangan:</span>
                            <span class="text-truncate ps-2 text-dark" data-dtcv-field="7"></span>
                        </div>
                    </div>
                </template>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="modal-adjust" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow">
            <div class="modal-header bg-light py-2 px-3">
                <h5 class="modal-title fw-bold fs-4 text-dark"><i class="ti ti-adjustments text-danger me-1"></i> Buat Adjust Satuan</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="form-adjust">
                <div class="modal-body p-3">
                    <input type="hidden" id="kode_item" name="kode_item">
                    <input type="hidden" id="qty_konversi" name="qty_konversi" value="1">
                    <input type="hidden" id="hpp_supplier" name="hpp_supplier">
                    
                    <div class="mb-3">
                        <label class="form-label small fw-semibold">Cari Produk <span class="text-danger">*</span></label>
                        <select class="form-select select2" id="product" style="width: 100%;"></select>
                    </div>

                    <div class="row g-2 mb-3">
                        <div class="col-6">
                            <label class="form-label small fw-semibold">Satuan</label>
                            <input type="text" class="form-control form-control-sm bg-light" id="sat_id" name="sat_id" readonly>
                        </div>
                        <div class="col-6">
                            <label class="form-label small fw-semibold">Stok di Sistem</label>
                            <input type="number" class="form-control form-control-sm bg-light font-monospace fw-semibold" id="qty" readonly>
                        </div>
                    </div>

                    <div class="row g-2 mb-3">
                        <div class="col-6">
                            <label class="form-label small fw-semibold">Stok Fisik Real <span class="text-danger">*</span></label>
                            <input type="number" step="0.01" class="form-control form-control-sm font-monospace fw-bold" id="qty_fisik" placeholder="0.00" required>
                        </div>
                        <div class="col-6">
                            <label class="form-label small fw-semibold">Selisih (+/-)</label>
                            <input type="number" step="0.01" class="form-control form-control-sm bg-light font-monospace fw-bold" id="qty_selisih" name="qty_selisih" readonly>
                        </div>
                    </div>

                    <div class="mb-2">
                        <label class="form-label small fw-semibold">Keterangan / Alasan Adjust</label>
                        <input type="text" class="form-control form-control-sm" id="keterangan" name="keterangan" placeholder="Contoh: Barang rusak, expired, dll">
                    </div>
                </div>
                <div class="modal-footer bg-light py-2 px-3">
                    <button type="button" class="btn btn-light btn-sm px-3" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-primary btn-sm px-4 fw-semibold"><i class="ti ti-device-floppy me-1"></i> Simpan Adjust</button>
                </div>
            </form>
        </div>
    </div>
</div>
<?= $this->endSection('content') ?>

<?= $this->section('javascript') ?>
<script>
    const adjustModal = new bootstrap.Modal(document.getElementById('modal-adjust'));
    $(function() {
        $('#product').select2({
            width: '100%',
            placeholder: 'Ketik nama / barcode produk...',
            dropdownParent: $('#modal-adjust'),
            ajax: {
                url: '<?= base_url('/opname/search-item') ?>',
                type: 'post',
                dataType: 'json',
                delay: 250,
                data: function(params) {
                    return {
                        term: params.term || ''
                    };
                },
                processResults: function(data) {
                    return {
                        results: (data || []).map(function(row) {
                            return {
                                id: row.kode_item,
                                text: `${row.kode_item} - ${row.nama_item}`,
                                payload: row
                            };
                        })
                    };
                }
            }
        }).on('select2:select', function(e) {
            const row = e.params.data.payload || {};
            $('#kode_item').val(row.kode_item || '');
            $('#sat_id').val(row.sat_id || '');
            $('#qty').val(row.qty || 0);
            $('#qty_konversi').val(row.qty_konversi || 1);
            $('#hpp_supplier').val(row.hpp_supplier || 0);
            $('#qty_fisik').val(row.qty || 0);
            calculateDiff();
        }).on('select2:clear', function() {
            $('#kode_item, #sat_id, #qty, #qty_fisik, #qty_selisih, #hpp_supplier').val('');
            $('#qty_konversi').val(1);
        });

        $('#qty_fisik').on('input change', calculateDiff);
    });

    function calculateDiff() {
        const qty = Number($('#qty').val() || 0);
        const fisik = Number($('#qty_fisik').val() || 0);
        const diff = (fisik - qty).toFixed(2);
        $('#qty_selisih').val(diff);

        if (Number(diff) < 0) {
            $('#qty_selisih').removeClass('text-success text-dark').addClass('text-danger');
        } else if (Number(diff) > 0) {
            $('#qty_selisih').removeClass('text-danger text-dark').addClass('text-success');
        } else {
            $('#qty_selisih').removeClass('text-danger text-success').addClass('text-dark');
        }
    }

    DataTable.Buttons.defaults.dom.button.className = 'btn btn-primary';
    const table = $('#table-data').DataTable({
        layout: {
            topStart: {
                buttons: [{
                    text: '<i class="ti ti-plus"></i> Adjust Satuan',
                    className: 'btn btn-primary btn-sm px-3',
                    action: function() {
                        $('#form-adjust')[0].reset();
                        $('#product').val(null).trigger('change');
                        $('#qty_selisih').removeClass('text-danger text-success').addClass('text-dark');
                        adjustModal.show();
                    }
                }, 'pageLength']
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
            url: '<?= base_url('/opname/ajax-adjust') ?>',
            type: 'post'
        },
        cardView: {
            enable: true,
            breakpoint: 768,
            template: '#card-so-satuan-template',
            gridClass: 'col-12 col-sm-6 mb-2',
            onCardRender: function($card, rowData) {
                const qty = Number(rowData.qty_so || 0);
                const $qtyEl = $card.find('[data-dtcv-field="4"]');
                if (qty < 0) {
                    $qtyEl.addClass('text-danger');
                } else if (qty > 0) {
                    $qtyEl.addClass('text-success');
                }
            }
        },
        columns: [{
                data: 'tanggal',
                title: 'Tanggal',
                render: data => data ? new Date(String(data).replace(' ', 'T')).toLocaleString('id-ID') : '-'
            },
            {
                data: 'kode_item',
                title: 'Kode'
            },
            {
                data: 'nama_item',
                title: 'Nama'
            },
            {
                data: 'sat_id',
                title: 'Satuan'
            },
            {
                data: 'qty_so',
                title: 'Qty',
                className: 'text-end',
                render: data => Number(data || 0).toLocaleString('id-ID', {
                    maximumFractionDigits: 2
                })
            },
            {
                data: 'price',
                title: 'Price',
                className: 'text-end',
                render: data => 'Rp ' + formatMoneyValue(data)
            },
            {
                data: 'gross',
                title: 'Gross',
                className: 'text-end',
                render: data => 'Rp ' + formatMoneyValue(data)
            },
            {
                data: 'keterangan',
                title: 'Keterangan'
            },
            {
                title: 'Action',
                data: null,
                className: 'text-center',
                render: function(row) {
                    if (row.can_delete !== true) {
                        return `<a class="btn btn-sm btn-light text-muted px-2 py-1" href="javascript:void(0)" onclick="showLockedNotice('${row.closing_date}')"><i class="ti ti-lock text-danger"></i> Terkunci</a>`;
                    }
                    return `<a class="btn btn-sm btn-danger px-2 py-1" href="javascript:void(0)" onclick="deleteAdjust(${row.so_id})" title="Hapus"><i class="ti ti-trash"></i></a>`;
                }
            }
        ]
    });

    $('#form-adjust').on('submit', function(e) {
        e.preventDefault();
        if (Math.abs(Number($('#qty_selisih').val() || 0)) < 0.0001) {
            toastr.error('Tidak ada selisih stok yang perlu disesuaikan');
            return;
        }
        $.ajax({
            type: 'PUT',
            url: '<?= base_url('/opname/adjust') ?>',
            dataType: 'json',
            data: {
                kode_item: $('#kode_item').val(),
                sat_id: $('#sat_id').val(),
                qty_selisih: $('#qty_selisih').val(),
                qty_konversi: $('#qty_konversi').val(),
                hpp_supplier: $('#hpp_supplier').val(),
                keterangan: $('#keterangan').val()
            },
            success: function(res) {
                if (res.tipe === 'success') {
                    toastr.success(res.data || 'Adjust berhasil disimpan');
                    adjustModal.hide();
                    table.ajax.reload(null, false);
                    return;
                }
                toastr.error(res.data || 'Gagal simpan adjust');
            },
            error: function(xhr) {
                toastr.error(extractErrorMessage(xhr, 'Gagal simpan adjust'));
            }
        });
    });

    function deleteAdjust(soId) {
        Swal.fire({
            title: 'Hapus adjust?',
            text: 'Data penyesuaian stok akan dibatalkan/dihapus dari tabel adjust.',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonText: '<i class="ti ti-trash"></i> Ya, hapus',
            cancelButtonText: 'Batal',
            customClass: {
                confirmButton: 'btn btn-danger px-3 me-2',
                cancelButton: 'btn btn-light px-3'
            },
            buttonsStyling: false
        }).then((result) => {
            if (!result.isConfirmed) return;
            $.ajax({
                type: 'DELETE',
                url: '<?= base_url('/opname/adjust') ?>',
                dataType: 'json',
                data: {
                    so_id: soId
                },
                success: function(res) {
                    if (res.tipe === 'success') {
                        toastr.success(res.data || 'Adjust berhasil dihapus');
                        table.ajax.reload(null, false);
                        return;
                    }
                    toastr.error(res.data || 'Gagal hapus adjust');
                },
                error: function(xhr) {
                    toastr.error(extractErrorMessage(xhr, 'Gagal hapus adjust'));
                }
            });
        });
    }

    function showLockedNotice(closingDate) {
        toastr.error(`Data sebelum periode closing ${closingDate} dikunci.`);
    }
</script>
<?= $this->endSection('javascript') ?>