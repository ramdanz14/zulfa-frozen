<?= $this->extend('layouts/base') ?>

<?= $this->section('content') ?>
<style>
    /* Styling responsif mobile untuk History SO & CardView */
    .so-history-card {
        background: #ffffff;
        border: 1px solid #e2e8f0;
        border-radius: 10px;
        padding: 0.85rem 1rem;
        transition: transform 0.15s ease, box-shadow 0.15s ease;
    }
    .so-history-card:hover {
        border-color: #cbd5e1;
        box-shadow: 0 4px 12px rgba(0,0,0,0.05);
    }
    .so-table-name-badge {
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
    .so-history-title {
        font-weight: 700;
        color: var(--bs-heading-color);
        line-height: 1.3;
        font-size: 0.95rem;
    }
    .so-history-metric {
        background-color: #f8fafc;
        border: 1px solid #e2e8f0;
        border-radius: 6px;
        padding: 0.4rem 0.55rem;
        text-align: center;
    }
</style>

<div class="body-wrapper">
    <div class="container-fluid p-0">
        <div class="card bg-secondary-subtle shadow-none position-relative overflow-hidden mb-3">
            <div class="card-body px-4 py-3">
                <div class="row align-items-center">
                    <div class="col-12 col-lg-8">
                        <h4 class="fw-semibold mb-1">History Stock Opname</h4>
                        <p class="mb-0 text-muted small"><span class="page-pretitle fw-semibold text-dark">Riwayat SO Toko</span> | Arsip dan tracking progres sesi stock opname toko aktif.</p>
                    </div>
                    <div class="col-12 col-lg-4 text-start text-lg-end mt-2 mt-lg-0">
                        <a href="<?= base_url('/opname') ?>" class="btn btn-outline-secondary btn-sm px-3"><i class="ti ti-arrow-left me-1"></i> Kembali ke Menu SO</a>
                    </div>
                </div>
            </div>
        </div>

        <div class="card border">
            <div class="card-body p-2 p-md-3">
                <table id="table-history" class="table table-bordered table-hover table-striped table-sm align-middle w-100 mb-0">
                    <thead class="table-light">
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
                        <tr>
                            <td colspan="7">Memuat data...</td>
                        </tr>
                    </tbody>
                </table>

                <!-- CardView Template for History SO (Mobile Reflow) -->
                <template id="card-so-history-template">
                    <div class="so-history-card mb-2">
                        <!-- Baris 1: Tanggal SO, Tabel & Status Badge -->
                        <div class="d-flex justify-content-between align-items-start gap-2 mb-2 pb-2 border-bottom">
                            <div class="min-w-0 flex-grow-1">
                                <div class="so-history-title">
                                    <i class="ti ti-calendar me-1 text-primary"></i><span data-dtcv-field="0"></span>
                                </div>
                                <div class="mt-1">
                                    <span class="so-table-name-badge"><i class="ti ti-table"></i><span data-dtcv-field="1"></span></span>
                                </div>
                            </div>
                            <!-- data-dtcv-field="5" telah dirender sebagai HTML badge oleh DataTable, jadi di template tidak dibungkus tag badge lagi -->
                            <div class="flex-shrink-0" data-dtcv-field="5"></div>
                        </div>

                        <!-- Baris 2: Metrik Total Item, Sudah & Belum Input -->
                        <div class="row g-2 mb-3">
                            <div class="col-4">
                                <div class="so-history-metric">
                                    <div class="text-muted" style="font-size: 0.72rem;">Total Item</div>
                                    <div class="fw-bold font-monospace text-dark" data-dtcv-field="2"></div>
                                </div>
                            </div>
                            <div class="col-4">
                                <div class="so-history-metric border-success-subtle bg-success-subtle">
                                    <div class="text-success" style="font-size: 0.72rem;">Sudah Input</div>
                                    <div class="fw-bold font-monospace text-success" data-dtcv-field="3"></div>
                                </div>
                            </div>
                            <div class="col-4">
                                <div class="so-history-metric border-warning-subtle bg-warning-subtle">
                                    <div class="text-warning" style="font-size: 0.72rem;">Belum Input</div>
                                    <div class="fw-bold font-monospace text-warning" data-dtcv-field="4"></div>
                                </div>
                            </div>
                        </div>

                        <!-- Baris 3: Tombol Aksi Hasil SO -->
                        <div class="d-grid pt-2 border-top" data-dtcv-field="6"></div>
                    </div>
                </template>
            </div>
        </div>
    </div>
</div>
<?= $this->endSection('content') ?>

<?= $this->section('javascript') ?>
<script>
    DataTable.Buttons.defaults.dom.button.className = 'btn btn-primary';
    $(function() {
        $('#table-history').DataTable({
            destroy: true,
            responsive: false,
            lengthChange: true,
            autoWidth: false,
            searching: true,
            paging: true,
            ajax: {
                url: '<?= base_url('/opname/history-data') ?>',
                type: 'POST',
                dataSrc: ''
            },
            cardView: {
                enable: true,
                breakpoint: 768,
                template: '#card-so-history-template',
                gridClass: 'col-12 col-sm-6 mb-2',
                onCardRender: function($card) {
                    $card.find('[data-dtcv-field="6"] a').addClass('w-100 text-center py-2');
                }
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
                    className: 'text-center font-monospace'
                },
                {
                    data: 'jml_input',
                    className: 'text-center font-monospace text-success fw-semibold'
                },
                {
                    data: 'jml_belum',
                    className: 'text-center font-monospace text-warning fw-semibold'
                },
                {
                    data: 'status',
                    className: 'text-center',
                    render: function(data) {
                        return data === 'AKTIF' ?
                            '<span class="badge bg-danger-subtle text-danger border border-danger-subtle">AKTIF</span>' :
                            '<span class="badge bg-success-subtle text-success border border-success-subtle">SELESAI</span>';
                    }
                },
                {
                    data: null,
                    className: 'text-center',
                    render: function(row) {
                        return `<a class="btn btn-sm btn-primary px-3" href="<?= base_url('/opname/hasil') ?>?tanggal=${encodeURIComponent(row.tanggal || '')}">
                            <i class="ti ti-file-analytics me-1"></i> Lihat Hasil SO
                        </a>`;
                    }
                }
            ],
            layout: {
                topStart: {
                    buttons: [{
                        extend: 'excelHtml5',
                        className: 'btn btn-primary btn-sm px-3',
                        title: 'History-SO'
                    }, 'pageLength']
                }
            }
        });
    });
</script>
<?= $this->endSection('javascript') ?>