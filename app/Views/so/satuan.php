<?= $this->extend('layouts/base') ?>

<?= $this->section('content') ?>
<div class="body-wrapper">
    <div class="container-fluid p-0">
        <div class="card bg-danger-subtle shadow-none position-relative overflow-hidden mb-4">
            <div class="card-body px-4 py-3">
                <div class="row align-items-center">
                    <div class="col-lg-8">
                        <h4 class="fw-semibold mb-2">Adjust SO Satuan</h4>
                        <p class="mb-0"><span class="page-pretitle">Closing aktif: <?= esc($closingDate ?? '-') ?></span> | Adjustment manual akan disimpan ke tabel `adjust` dengan `istype=SO`.</p>
                    </div>
                    <div class="col-lg-4 text-lg-end mt-3 mt-lg-0">
                        <a href="<?= base_url('/so') ?>" class="btn btn-outline-secondary btn-sm">Kembali ke Menu SO</a>
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

<div class="modal fade" id="modal-adjust" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Buat Adjust Satuan</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="form-adjust">
                <div class="modal-body">
                    <input type="hidden" id="kode_item" name="kode_item">
                    <input type="hidden" id="qty_konversi" name="qty_konversi" value="1">
                    <input type="hidden" id="hpp_supplier" name="hpp_supplier">
                    <div class="mb-2">
                        <label class="form-label">Produk</label>
                        <select class="form-select select2" id="product" style="width: 100%;"></select>
                    </div>
                    <div class="mb-2">
                        <label class="form-label">Satuan</label>
                        <input type="text" class="form-control" id="sat_id" name="sat_id" readonly>
                    </div>
                    <div class="mb-2">
                        <label class="form-label">Stok Data</label>
                        <input type="number" class="form-control" id="qty" readonly>
                    </div>
                    <div class="mb-2">
                        <label class="form-label">Stok Fisik</label>
                        <input type="number" step="0.01" class="form-control" id="qty_fisik">
                    </div>
                    <div class="mb-2">
                        <label class="form-label">Selisih</label>
                        <input type="number" step="0.01" class="form-control" id="qty_selisih" name="qty_selisih" readonly>
                    </div>
                    <div class="mb-2">
                        <label class="form-label">Keterangan</label>
                        <input type="text" class="form-control" id="keterangan" name="keterangan">
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-light" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-primary">Simpan</button>
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
            placeholder: 'Pilih produk',
            dropdownParent: $('#modal-adjust'),
            ajax: {
                url: '<?= base_url('/so/search-item') ?>',
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
        $('#qty_selisih').val((fisik - qty).toFixed(2));
    }

    DataTable.Buttons.defaults.dom.button.className = 'btn btn-primary';
    const table = $('#table-data').DataTable({
        layout: {
            topStart: {
                buttons: [{
                    text: '<i class="ti ti-plus"></i> Adjust Satuan',
                    action: function() {
                        $('#form-adjust')[0].reset();
                        $('#product').val(null).trigger('change');
                        adjustModal.show();
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
            url: '<?= base_url('/so/ajax-adjust') ?>',
            type: 'post'
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
                        return `<a class="btn btn-sm btn-light text-muted" href="javascript:void(0)" onclick="showLockedNotice('${row.closing_date}')"><i class="ti ti-lock text-danger"></i> Terkunci</a>`;
                    }
                    return `<a class="btn btn-sm btn-danger" href="javascript:void(0)" onclick="deleteAdjust(${row.so_id})"><i class="ti ti-trash"></i></a>`;
                }
            }
        ]
    });

    $('#form-adjust').on('submit', function(e) {
        e.preventDefault();
        if (Math.abs(Number($('#qty_selisih').val() || 0)) < 0.0001) {
            toastr.error('Tidak ada selisih stok');
            return;
        }
        $.ajax({
            type: 'PUT',
            url: '<?= base_url('/so/adjust') ?>',
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
            text: 'Data adjust akan dihapus dari tabel adjust.',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonText: 'Ya, hapus',
            cancelButtonText: 'Batal'
        }).then((result) => {
            if (!result.isConfirmed) return;
            $.ajax({
                type: 'DELETE',
                url: '<?= base_url('/so/adjust') ?>',
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