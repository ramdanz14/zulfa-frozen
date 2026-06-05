<?= $this->extend('layouts/base') ?>

<?= $this->section('content') ?>
<?php
/**
 * @var array $kategoriOptions
 * @var array $soAktif
 */
?>
<div class="body-wrapper">
    <div class="container-fluid p-0">
        <div class="card bg-info-subtle shadow-none position-relative overflow-hidden mb-4">
            <div class="card-body px-4 py-3">
                <div class="row align-items-center">
                    <div class="col-lg-8">
                        <h4 class="fw-semibold mb-2">Input SO</h4>
                        <p class="mb-0"><span class="page-pretitle"><?= !empty($soAktif['tanggal'] ?? '') ? esc($soAktif['tanggal']) : 'Tidak ada SO aktif' ?></span> | Input stok fisik per item.</p>
                    </div>
                    <div class="col-lg-4 text-lg-end mt-3 mt-lg-0">
                        <a href="<?= base_url('/so') ?>" class="btn btn-outline-secondary btn-sm">Kembali ke Menu SO</a>
                    </div>
                </div>
            </div>
        </div>

        <div class="card mb-3">
            <div class="card-body">
                <div class="row g-3 align-items-end">
                    <div class="col-lg-3">
                        <label class="form-label">Status Input</label>
                        <select class="form-select" id="status_input">
                            <option value="belum">Belum Input</option>
                            <option value="sudah">Sudah Input</option>
                        </select>
                    </div>
                    <div class="col-lg-6">
                        <label class="form-label">Kategori</label>
                        <select class="form-select select2" id="kat_id">
                            <option value="all">Semua</option>
                            <?php foreach ($kategoriOptions as $row): ?>
                                <option value="<?= esc($row['id']) ?>"><?= esc($row['text']) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-lg-3 d-grid">
                        <button type="button" class="btn btn-primary" id="btn-filter">Terapkan Filter</button>
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

<div class="modal fade" id="modal-edit" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Input SO</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="form-edit">
                <div class="modal-body">
                    <input type="hidden" id="kode_item" name="kode_item">
                    <div class="mb-2">
                        <label class="form-label">Produk</label>
                        <input type="text" class="form-control" id="nama_item" readonly>
                    </div>
                    <div class="mb-2">
                        <label class="form-label">Stok Data</label>
                        <input type="text" class="form-control" id="stok_konversi" readonly>
                    </div>
                    <div class="mb-2">
                        <label class="form-label">Satuan Dasar</label>
                        <input type="text" class="form-control" id="sat_dasar" readonly>
                    </div>
                    <div class="mb-2">
                        <label class="form-label">Qty Fisik</label>
                        <input type="number" step="0.01" class="form-control" id="qty_fisik" name="qty_fisik" required>
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

<div class="modal fade" id="modal-history" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">History Input SO</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="mb-2"><strong id="history-title"></strong></div>
                <div class="table-responsive">
                    <table class="table table-bordered table-sm mb-0">
                        <thead>
                            <tr>
                                <th>Waktu</th>
                                <th>User</th>
                                <th>Input</th>
                            </tr>
                        </thead>
                        <tbody id="history-body"></tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
<?= $this->endSection('content') ?>

<?= $this->section('javascript') ?>
<script>
    const editModal = new bootstrap.Modal(document.getElementById('modal-edit'));
    const historyModal = new bootstrap.Modal(document.getElementById('modal-history'));
    $(function() {
        $('#kat_id').select2({
            width: '100%'
        });
    });

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
            url: '<?= base_url('/so/ajax-input') ?>',
            type: 'post',
            data: function(d) {
                d.status_input = $('#status_input').val();
                d.kat_id = $('#kat_id').val();
            },
            dataSrc: function(json) {
                if (json?.tipe === 'error') {
                    toastr.error(json.message || 'Tidak ada SO aktif');
                    return [];
                }
                return json.data || [];
            }
        },
        columns: [{
                data: 'kode_item',
                title: 'Kode'
            },
            {
                data: 'nama_item',
                title: 'Nama'
            },
            {
                data: 'stok_konversi',
                title: 'Stok Konversi'
            },
            {
                data: 'com',
                title: 'Stok Data',
                className: 'text-end',
                render: data => Number(data || 0).toLocaleString('id-ID', {
                    maximumFractionDigits: 2
                })
            },
            {
                data: 'ttl',
                title: 'Stok Fisik',
                className: 'text-end',
                render: data => Number(data || 0).toLocaleString('id-ID', {
                    maximumFractionDigits: 2
                })
            },
            {
                title: 'Action',
                data: null,
                className: 'text-center',
                render: function(row) {
                    const encoded = encodeURIComponent(JSON.stringify(row));
                    return `<span class="dropdown">
                        <button class="btn dropdown-toggle align-text-top btn-sm" data-bs-toggle="dropdown">Actions</button>
                        <div class="dropdown-menu dropdown-menu-end">
                            <a class="dropdown-item" href="javascript:void(0)" onclick="openEdit('${encoded}')"><i class="ti ti-pencil text-warning"></i> Input SO</a>
                            <a class="dropdown-item" href="javascript:void(0)" onclick="showHistory('${row.kode_item}','${row.nama_item}')"><i class="ti ti-history text-info"></i> History Input</a>
                        </div>
                    </span>`;
                }
            }
        ]
    });

    $('#btn-filter, #status_input').on('click change', function() {
        table.ajax.reload();
    });
    $('#kat_id').on('change', function() {
        table.ajax.reload();
    });

    function openEdit(encodedRow) {
        const row = JSON.parse(decodeURIComponent(encodedRow));
        $('#kode_item').val(row.kode_item || '');
        $('#nama_item').val(row.nama_item || '');
        $('#stok_konversi').val(row.stok_konversi || '');
        $('#sat_dasar').val(row.sat_dasar || '');
        let qty = Number(row.com || 0);
        const ttl = Number(row.ttl || 0);
        if (ttl !== 0 || String(row.soid || '') !== '') {
            qty = ttl;
        }
        $('#qty_fisik').val(qty);
        editModal.show();
    }

    $('#form-edit').on('submit', function(e) {
        e.preventDefault();
        $.ajax({
            type: 'PATCH',
            url: '<?= base_url('/so/input-save') ?>',
            dataType: 'json',
            data: $(this).serialize(),
            success: function(res) {
                if (res.tipe === 'success') {
                    toastr.success(res.data || 'Input SO berhasil disimpan');
                    editModal.hide();
                    table.ajax.reload(null, false);
                    return;
                }
                toastr.error(res.data || 'Gagal simpan input SO');
            },
            error: function(xhr) {
                toastr.error(extractErrorMessage(xhr, 'Gagal simpan input SO'));
            }
        });
    });

    function showHistory(kodeItem, namaItem) {
        $('#history-title').text(`${kodeItem} - ${namaItem}`);
        $('#history-body').html('<tr><td colspan="3" class="text-center text-muted">Memuat data...</td></tr>');
        historyModal.show();
        $.post('<?= base_url('/so/history-input') ?>', {
            kode_item: kodeItem
        }, function(res) {
            if (!Array.isArray(res) || !res.length) {
                $('#history-body').html('<tr><td colspan="3" class="text-center text-muted">Belum ada input</td></tr>');
                return;
            }
            const rows = res.map((row) => `
                <tr>
                    <td>${row.updtime || '-'}</td>
                    <td>${row.updid || '-'}</td>
                    <td>${Number(row.ttl || 0).toLocaleString('id-ID', { maximumFractionDigits: 2 })} ${row.sat_dasar || ''}</td>
                </tr>
            `).join('');
            $('#history-body').html(rows);
        }, 'json').fail(function(xhr) {
            $('#history-body').html(`<tr><td colspan="3" class="text-center text-danger">${extractErrorMessage(xhr, 'Gagal memuat history')}</td></tr>`);
        });
    }
</script>
<?= $this->endSection('javascript') ?>