<?= $this->extend('layouts/base') ?>

<?= $this->section('content') ?>
<?php
/**
 * @var string $akses_menu
 * @var array $recentInvoices
 */
?>
<div class="body-wrapper">
    <div class="container-fluid p-0">
        <div class="card bg-warning-subtle shadow-none position-relative overflow-hidden mb-4">
            <div class="card-body px-4 py-3">
                <div class="row align-items-center g-3">
                    <div class="col-lg-8">
                        <h4 class="fw-semibold mb-2">Setting Harga</h4>
                        <p class="mb-0"><span class="page-pretitle">Total</span> | Koreksi cepat HPP dan harga jual per item/satuan aktif.</p>
                    </div>
                    <div class="col-lg-4 text-lg-end">
                        <div class="small text-muted">Perubahan manual akan tercatat ke histori harga dan tracelog.</div>
                    </div>
                </div>
            </div>
        </div>

        <div class="card mb-3">
            <div class="card-body">
                <div class="row g-3 align-items-end">
                    <div class="col-lg-6">
                        <label for="invoice_filter" class="form-label">Filter Faktur Pembelian</label>
                        <select class="form-select" id="invoice_filter">
                            <option value="">Semua item aktif</option>
                            <?php foreach (($recentInvoices ?? []) as $row): ?>
                                <option value="<?= esc($row['beli_id']) ?>">
                                    <?= esc($row['beli_id']) ?> | <?= esc($row['invoice']) ?> | <?= esc($row['supplier_nama'] ?? '-') ?> | <?= esc($row['tanggal']) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                        <small class="text-muted">Hanya 10 faktur `TERIMA` terbaru. Jika dipilih, tabel menampilkan item yang ada di faktur itu saja.</small>
                    </div>
                    <div class="col-lg-6">
                        <div class="d-flex flex-wrap gap-2 justify-content-lg-end">
                            <button type="button" class="btn btn-outline-secondary" id="btn-reset-filter">Reset Filter</button>
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
<?= $this->endSection('content') ?>

<?= $this->section('javascript') ?>
<script>
    const akses_menu = <?= $akses_menu ?>;
    const pendingChanges = {};
    DataTable.Buttons.defaults.dom.button.className = 'btn btn-primary';

    const table = $('#table-data').DataTable({
        layout: {
            topStart: {
                buttons: [{
                    text: '<i class="ti ti-file-type-xls"></i> Excel',
                    extend: 'excelHtml5',
                    title: 'Laporan-Harga',
                    exportOptions: {
                        columns: [0, 1, 2, 3, 4, 5],
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
            url: '<?= base_url('/settingharga/ajax') ?>',
            type: 'post',
            data: function(d) {
                d.beli_id = $('#invoice_filter').val();
            }
        },
        columns: [{
                data: 'kode_item',
                title: 'Kode Item',
                className: "not-mobile",
            },
            {
                data: 'nama_item',
                title: 'Nama Item'
            },
            {
                data: 'sat_id',
                title: 'Sat'
            },
            {
                data: 'harga_pokok',
                title: 'Harga Pokok',
                className: "not-mobile",
                render: function(data, type, row) {
                    if (type !== 'display') {
                        return data;
                    }
                    const state = getRowState(row);
                    return `<input type="text" class="form-control form-control-sm money input-hpp" value="${state.harga_pokok}" ${akses_menu?.akses_update === 'Y' ? '' : 'disabled'}>`;
                }
            },
            {
                data: 'harga_jual',
                title: 'Harga Jual',
                className: "not-mobile",
                render: function(data, type, row) {
                    if (type !== 'display') {
                        return data;
                    }
                    const state = getRowState(row);
                    const note = row.can_round_50 ?
                        `<div class="small text-muted mt-1">Auto bulat ke 50 saat HPP dikoreksi.</div>` :
                        `<div class="small text-muted mt-1">Satuan gramasi, tanpa pembulatan 50.</div>`;
                    return `<input type="text" class="form-control form-control-sm money input-hjual" value="${state.harga_jual}" ${akses_menu?.akses_update === 'Y' ? '' : 'disabled'}>`;
                }
            },
            {
                data: 'target_psn_margin',
                title: 'Target Margin %',
                className: 'text-end not-mobile',

                render: function(data, type, row) {
                    const state = getRowState(row);
                    return type === 'display' ?
                        `<input type="text" class="form-control form-control-sm text-end input-margin" value="${Number(state.target_psn_margin || 0).toFixed(1)}" readonly>` :
                        state.target_psn_margin;
                }
            },
            {
                title: 'Action',
                className: 'text-center',
                data: null,
                responsivePriority: 1,
                render: function(data) {
                    if (akses_menu?.akses_update !== 'Y') {
                        return '<span class="text-muted small">Readonly</span>';
                    }
                    return `<button type="button" class="btn btn-success btn-sm rounded-circle btn-save-row">
                        <i class="fs-5 ti ti-device-floppy"></i>
                    </button>`;
                }
            }
        ],
        rowCallback: function(row, data) {
            $(row).attr('data-key', getRowKey(data));
        },
        drawCallback: function() {
            applyMoneyMask('#table-data');
        }
    });

    table.on('xhr.dt', function(e, settings, json) {
        $('.page-pretitle').text(`Total Data : ${json?.recordsFiltered || 0}`);
    });

    $('#invoice_filter').on('change', function() {
        table.ajax.reload();
    });

    $('#btn-reset-filter').on('click', function() {
        $('#invoice_filter').val('');
        table.ajax.reload();
    });

    $('#table-data tbody').on('input blur', '.input-hpp', function() {
        const rowData = table.row($(this).closest('tr')).data();
        const state = ensurePendingState(rowData);
        state.harga_pokok = Number(normalizeMoneyValue($(this).val() || 0));
        recalcFromHpp(rowData, state);
        syncStateToRow($(this).closest('tr'), rowData, state);
    });

    $('#table-data tbody').on('input blur', '.input-hjual', function() {
        const rowData = table.row($(this).closest('tr')).data();
        const state = ensurePendingState(rowData);
        state.harga_jual = Number(normalizeMoneyValue($(this).val() || 0));
        recalcMargin(state);
        syncStateToRow($(this).closest('tr'), rowData, state);
    });

    $('#table-data tbody').on('click', '.btn-save-row', function() {
        const rowData = table.row($(this).closest('tr')).data();
        submitRowCorrection(rowData);
    });

    function getRowKey(row) {
        return `${row.kode_item}__${row.sat_id}`;
    }

    function getRowState(row) {
        const key = getRowKey(row);
        return pendingChanges[key] || {
            kode_item: row.kode_item,
            sat_id: row.sat_id,
            harga_pokok: Number(row.harga_pokok || 0),
            harga_jual: Number(row.harga_jual || 0),
            target_psn_margin: Number(row.target_psn_margin || 0)
        };
    }

    function ensurePendingState(row) {
        const key = getRowKey(row);
        if (!pendingChanges[key]) {
            pendingChanges[key] = getRowState(row);
        }
        return pendingChanges[key];
    }

    function recalcFromHpp(row, state) {
        const hargaPokok = Number(state.harga_pokok || 0);
        const margin = Number(state.target_psn_margin || row.target_psn_margin || 0);
        let hargaJual = Math.round(hargaPokok + (hargaPokok * margin / 100));
        if (row.can_round_50) {
            hargaJual = roundUpTo50(hargaJual);
        }
        state.harga_jual = hargaJual;
        //recalcMargin(state);
    }

    function recalcMargin(state) {
        const hargaPokok = Number(state.harga_pokok || 0);
        const hargaJual = Number(state.harga_jual || 0);
        state.target_psn_margin = hargaPokok > 0 ? (((hargaJual - hargaPokok) / hargaPokok) * 100) : 0;
    }

    function syncStateToRow($tr, row, state) {
        $tr.find('.input-hpp').val(formatMoneyValue(state.harga_pokok));
        $tr.find('.input-hjual').val(formatMoneyValue(state.harga_jual));
        $tr.find('.input-margin').val(Number(state.target_psn_margin || 0).toFixed(1));
        applyMoneyMask($tr);
        cleanupPendingState(row, state);
    }

    function cleanupPendingState(row, state) {
        const key = getRowKey(row);
        const hargaPokokOld = Number(row.harga_pokok || 0);
        const hargaJualOld = Number(row.harga_jual || 0);
        if (hargaPokokOld === Number(state.harga_pokok || 0) && hargaJualOld === Number(state.harga_jual || 0)) {
            delete pendingChanges[key];
        } else {
            pendingChanges[key] = state;
        }
    }

    function submitRowCorrection(row) {
        if (akses_menu?.akses_update !== 'Y') {
            toastr.error('Anda tidak memiliki akses untuk simpan koreksi harga');
            return;
        }

        const key = getRowKey(row);
        const state = pendingChanges[key] || getRowState(row);
        const payloadRow = {
            kode_item: row.kode_item,
            sat_id: row.sat_id,
            harga_pokok: Number(state.harga_pokok || 0),
            harga_jual: Number(state.harga_jual || 0)
        };

        if (Number(row.harga_pokok || 0) === payloadRow.harga_pokok && Number(row.harga_jual || 0) === payloadRow.harga_jual) {
            toastr.error('Belum ada perubahan harga pada baris ini');
            return;
        }

        if (payloadRow.harga_pokok <= 0 || payloadRow.harga_jual <= 0) {
            toastr.error(`Harga item ${payloadRow.kode_item} / ${payloadRow.sat_id} harus lebih besar dari nol`);
            return;
        }
        if (payloadRow.harga_jual < payloadRow.harga_pokok) {
            toastr.error(`Harga jual item ${payloadRow.kode_item} / ${payloadRow.sat_id} tidak boleh lebih kecil dari harga pokok`);
            return;
        }

        $.ajax({
            type: 'PATCH',
            url: '<?= base_url('/settingharga') ?>',
            dataType: 'json',
            data: {
                source_beli_id: $('#invoice_filter').val() || 'KOREKSI',
                kode_item: payloadRow.kode_item,
                sat_id: payloadRow.sat_id,
                harga_pokok: payloadRow.harga_pokok,
                harga_jual: payloadRow.harga_jual
            },
            success: function(res) {
                if (res.tipe === 'success') {
                    toastr.success(res.data || 'Koreksi harga berhasil disimpan');
                    delete pendingChanges[key];
                    table.ajax.reload(null, false);
                    return;
                }
                toastr.error(res.data || 'Gagal menyimpan koreksi harga');
            },
            error: function(xhr) {
                toastr.error(extractErrorMessage(xhr, 'Gagal menyimpan koreksi harga'));
            }
        });
    }

    function roundUpTo50(value) {
        const number = Number(value || 0);
        if (number <= 0) {
            return 0;
        }
        return Math.ceil(number / 50) * 50;
    }
</script>
<?= $this->endSection('javascript') ?>