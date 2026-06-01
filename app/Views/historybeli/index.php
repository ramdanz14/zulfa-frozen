<?= $this->extend('layouts/base') ?>

<?= $this->section('content') ?>
<?php
/**
 * @var string $akses_menu
 */
?>
<div class="body-wrapper">
    <div class="container-fluid p-0">
        <div class="card bg-primary-subtle shadow-none position-relative overflow-hidden mb-4">
            <div class="card-body px-4 py-3">
                <div class="row align-items-center g-3">
                    <div class="col-lg-8">
                        <h4 class="fw-semibold mb-2">History Perubahan Harga</h4>
                        <p class="mb-0"><span class="page-pretitle">Total</span> | Riwayat koreksi harga pokok dan harga jual per item.</p>
                    </div>
                    <div class="col-lg-4 text-lg-end">
                        <div class="small text-muted">Gunakan filter jenis perubahan untuk fokus pada harga naik atau turun.</div>
                    </div>
                </div>
            </div>
        </div>

        <div class="card mb-3">
            <div class="card-body">
                <div class="row g-3 align-items-end">
                    <div class="col-lg-8">
                        <label class="form-label d-block">Filter Perubahan Harga</label>
                        <div class="btn-group" role="group" aria-label="Filter perubahan harga">
                            <button type="button" class="btn btn-primary active btn-filter-jenis" data-jenis="">Semua Perubahan Harga</button>
                            <button type="button" class="btn btn-outline-success btn-filter-jenis" data-jenis="naik">
                                <i class="ti ti-trending-up"></i> Harga Naik
                            </button>
                            <button type="button" class="btn btn-outline-danger btn-filter-jenis" data-jenis="turun">
                                <i class="ti ti-trending-down"></i> Harga Turun
                            </button>
                        </div>
                        <input type="hidden" id="filter-jenis" value="">
                    </div>
                    <div class="col-lg-4 text-lg-end">
                        <button type="button" class="btn btn-light" id="btn-reset-filter">Reset Filter</button>
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
    DataTable.Buttons.defaults.dom.button.className = 'btn btn-primary';

    const table = $('#table-data').DataTable({
        layout: {
            topStart: {
                buttons: [{
                    text: '<i class="ti ti-file-type-xls"></i> Excel',
                    extend: 'excelHtml5',
                    title: 'Laporan-History-Perubahan-Harga',
                    exportOptions: {
                        columns: [0, 1, 2, 3, 4, 5, 6, 7],
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
            url: '<?= base_url('/historybeli/ajax') ?>',
            type: 'post',
            data: function(d) {
                d.jenis = $('#filter-jenis').val();
            }
        },
        columns: [{
                data: 'updtime',
                title: 'Waktu',
                render: function(data, type) {
                    if (type !== 'display') {
                        return data;
                    }
                    return data ? new Date(String(data).replace(' ', 'T')).toLocaleString('id-ID') : '-';
                }
            },
            {
                data: 'kode_item',
                title: 'Kode Item',
                render: function(data, type, row) {
                    return `<div class="fw-semibold">${escapeHtml(data || '-')}</div><small class="text-muted">${escapeHtml(row.sat_id || '-')}</small>`;
                }
            },
            {
                data: 'nama_item',
                title: 'Nama Item',
                render: data => escapeHtml(data || '-')
            },
            {
                data: 'jenis',
                title: 'Perubahan',
                className: 'text-center',
                render: function(data, type, row) {
                    if (type !== 'display') {
                        return data;
                    }
                    if (row.jenis === 'naik') {
                        return `<span class="badge bg-success-subtle text-success"><i class="ti ti-arrow-up-right"></i> Harga Naik</span>`;
                    }
                    return `<span class="badge bg-danger-subtle text-danger"><i class="ti ti-arrow-down-right"></i> Harga Turun</span>`;
                }
            },
            {
                data: 'harga_pokok_old',
                title: 'HPP Lama',
                className: 'text-end',
                render: data => formatMoneyValue(data || 0)
            },
            {
                data: 'harga_pokok_new',
                title: 'HPP Baru',
                className: 'text-end',
                render: data => formatMoneyValue(data || 0)
            },
            {
                data: 'harga_jual_old',
                title: 'HJual Lama',
                className: 'text-end',
                render: data => formatMoneyValue(data || 0)
            },
            {
                data: 'harga_jual_new',
                title: 'HJual Baru',
                className: 'text-end',
                render: data => formatMoneyValue(data || 0)
            },
            {
                title: 'Action',
                data: null,
                className: 'text-center',
                responsivePriority: 1,
                render: function(data) {
                    const targetUrl = `<?= base_url('/settingharga') ?>?search_text=${encodeURIComponent(data.kode_item || '')}`;
                    return `<a href="${targetUrl}" class="btn btn-warning btn-sm">
                        <i class="ti ti-settings"></i> Setting Harga
                    </a>`;
                }
            }
        ]
    });

    table.on('xhr.dt', function(e, settings, json) {
        $('.page-pretitle').text(`Total Data : ${json?.recordsFiltered || 0}`);
    });

    $('.btn-filter-jenis').on('click', function() {
        const jenis = $(this).data('jenis') || '';
        setJenisFilter(jenis);
        table.ajax.reload();
    });

    $('#btn-reset-filter').on('click', function() {
        setJenisFilter('');
        table.search('').draw();
    });

    function setJenisFilter(jenis) {
        $('#filter-jenis').val(jenis);
        $('.btn-filter-jenis')
            .removeClass('active btn-primary btn-success btn-danger text-white')
            .addClass('btn-outline-primary');

        $('.btn-filter-jenis[data-jenis="naik"]').removeClass('btn-outline-primary').addClass('btn-outline-success');
        $('.btn-filter-jenis[data-jenis="turun"]').removeClass('btn-outline-primary').addClass('btn-outline-danger');

        const $target = $(`.btn-filter-jenis[data-jenis="${jenis}"]`);
        if (jenis === 'naik') {
            $target.removeClass('btn-outline-success').addClass('active btn-success text-white');
        } else if (jenis === 'turun') {
            $target.removeClass('btn-outline-danger').addClass('active btn-danger text-white');
        } else {
            $target.removeClass('btn-outline-primary').addClass('active btn-primary text-white');
        }
    }

    function escapeHtml(value) {
        return String(value ?? '')
            .replace(/&/g, '&amp;')
            .replace(/</g, '&lt;')
            .replace(/>/g, '&gt;')
            .replace(/"/g, '&quot;')
            .replace(/'/g, '&#039;');
    }
</script>
<?= $this->endSection('javascript') ?>
