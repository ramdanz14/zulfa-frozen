<?= $this->extend('layouts/base') ?>

<?= $this->section('content') ?>
<?php
/**
 * @var string $akses_menu
 */
?>
<div class="body-wrapper">
    <div class="container-fluid p-0">
        <div class="card bg-info-subtle shadow-none position-relative overflow-hidden mb-4">
            <div class="card-body px-4 py-3">
                <div class="row align-items-center">
                    <div class="col-9">
                        <h4 class="fw-semibold mb-2">Data Barang</h4>
                        <p class="mb-0"><span class="page-pretitle">Total</span> | Manajemen Data Barang.</p>
                    </div>
                    <div class="col-3">
                        <div class="text-center mb-n5">
                            <img src="<?= base_url(); ?>/assets/images/breadcrumb/ChatBc.png" alt="modernize-img" class="img-fluid mb-n4" />
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="page-body">
            <div class="container-xl">
                <div class="row">
                    <div class="col-lg-12">
                        <div class="card">
                            <div class="card-body p-2">
                                <table id="table-data" class="table table-bordered table-hover table-striped table-sm table-head-fixed">
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
            </div>
        </div>
    </div>
</div>
<?= $this->endSection('content') ?>

<?= $this->section('javascript') ?>
<script>
    const akses_menu = <?= $akses_menu ?>;
    DataTable.Buttons.defaults.dom.button.className = 'btn btn-primary';
    const table = $("#table-data").DataTable({
        layout: {
            topStart: {
                buttons: [{
                    text: '<i class="ti ti-plus"></i> Tambah',
                    action: function() {
                        if (akses_menu?.akses_create === "Y") {
                            window.open('<?= base_url('/item/create') ?>', '_blank');
                        } else {
                            toastr.error('Anda tidak memiliki akses untuk ini!');
                        }
                    }
                }, {
                    text: '<i class="ti ti-file-type-xls"></i> Excel',
                    extend: 'excelHtml5',
                    title: 'Laporan-Item',
                    exportOptions: {
                        columns: [0, 1, 2, 3],
                        orthogonal: 'export'
                    },
                }, "pageLength"]
            }
        },
        lengthMenu: [
            [25, 50, 100, -1],
            ["25 rows", "50 rows", "100 rows", "Show all"]
        ],
        responsive: true,
        lengthChange: false,
        autoWidth: false,
        bProcessing: true,
        ordering: false,
        serverSide: true,
        ajax: {
            url: '<?= base_url('/item/ajax') ?>',
            type: 'post',
            data: {}
        },
        columns: [{
                data: 'kode_item',
                title: 'Kode Item'
            },
            {
                data: 'nama_item',
                title: 'Nama Item'
            },
            {
                data: 'kat_id',
                title: 'Kategori'
            },
            {
                data: 'status_item',
                title: 'Status',
                class: 'dt-center',
                render: function(data) {
                    return data === 'Y' ? '<span class="badge bg-success-subtle text-success">Aktif</span>' : '<span class="badge bg-danger-subtle text-danger">Nonaktif</span>';
                }
            },
            {
                title: 'Action',
                class: 'dt-center',
                responsivePriority: 1,
                data: null,
                render: function(data) {
                    const viewBtn = `<a class='dropdown-item' href='<?= base_url('/item/view') ?>/${data.kode_item}' target='_blank' rel='noopener noreferrer'><i class='ti ti-eye text-info'></i> View</a>`;
                    const editBtn = akses_menu?.akses_update === 'Y' ? `<a class='dropdown-item' href='<?= base_url('/item/edit') ?>/${data.kode_item}' target='_blank' rel='noopener noreferrer'><i class='ti ti-pencil text-warning'></i> Edit</a>` : '';
                    return `<span class="dropdown">
                          <button class="btn dropdown-toggle align-text-top btn-sm" data-bs-boundary="viewport" data-bs-toggle="dropdown">Actions</button>
                          <div class="dropdown-menu dropdown-menu-end">
                            ${viewBtn}
                            ${editBtn}
                          </div>
                        </span>`;
                }
            }
        ]
    });

    table.on('xhr.dt', function(e, settings, json) {
        $(".page-pretitle").text(`Total Data : ` + (json?.recordsTotal || 0));
    });
</script>
<?= $this->endSection('javascript') ?>