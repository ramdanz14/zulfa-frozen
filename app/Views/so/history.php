<?= $this->extend('layouts/base') ?>

<?= $this->section('content') ?>
<div class="body-wrapper">
    <div class="container-fluid p-0">
        <div class="card bg-secondary-subtle shadow-none position-relative overflow-hidden mb-4">
            <div class="card-body px-4 py-3">
                <div class="row align-items-center">
                    <div class="col-lg-8">
                        <h4 class="fw-semibold mb-2">History SO</h4>
                        <p class="mb-0"><span class="page-pretitle">Riwayat sesi SO</span> | Menampilkan sesi SO yang pernah dibuat untuk toko aktif.</p>
                    </div>
                    <div class="col-lg-4 text-lg-end mt-3 mt-lg-0">
                        <a href="<?= base_url('/so') ?>" class="btn btn-outline-secondary btn-sm">Kembali ke Menu SO</a>
                    </div>
                </div>
            </div>
        </div>

        <div class="card">
            <div class="card-body p-2">
                <table id="table-history" class="table table-bordered table-hover table-striped table-sm align-middle">
                    <thead>
                        <tr>
                            <th>Tanggal</th>
                            <th>Table</th>
                            <th>Jml Item</th>
                            <th>Sudah Input</th>
                            <th>Belum Input</th>
                            <th>Status</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr><td colspan="7">Memuat data...</td></tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
<?= $this->endSection('content') ?>

<?= $this->section('javascript') ?>
<script>
    $(function() {
        $('#table-history').DataTable({
            destroy: true,
            responsive: true,
            lengthChange: true,
            autoWidth: false,
            searching: true,
            paging: true,
            ajax: {
                url: '<?= base_url('/so/history-data') ?>',
                type: 'POST',
                dataSrc: ''
            },
            columns: [{
                    data: 'tanggal',
                    render: data => data ? new Date(`${data}T00:00:00`).toLocaleDateString('id-ID') : '-'
                },
                {
                    data: 'table_name'
                },
                {
                    data: 'jml_item',
                    className: 'text-center'
                },
                {
                    data: 'jml_input',
                    className: 'text-center'
                },
                {
                    data: 'jml_belum',
                    className: 'text-center'
                },
                {
                    data: 'status',
                    className: 'text-center',
                    render: function(data) {
                        return data === 'AKTIF'
                            ? '<span class="badge bg-danger-subtle text-danger">AKTIF</span>'
                            : '<span class="badge bg-success-subtle text-success">SELESAI</span>';
                    }
                },
                {
                    data: null,
                    className: 'text-center',
                    render: function(row) {
                        return `<a class="btn btn-sm btn-primary" href="<?= base_url('/so/hasil') ?>?tanggal=${encodeURIComponent(row.tanggal || '')}">
                            <i class="ti ti-eye"></i> Lihat Hasil SO
                        </a>`;
                    }
                }
            ],
            layout: {
                topStart: {
                    buttons: [{
                        extend: 'excelHtml5',
                        title: 'History-SO'
                    }, 'pageLength']
                }
            }
        });
    });
</script>
<?= $this->endSection('javascript') ?>
