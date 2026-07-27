<?= $this->extend('layouts/base') ?>

<?= $this->section('content') ?>
<?php
/**
 * @var string $akses_menu
 * @var array $tokoOptions
 */
?>
<div class="body-wrapper">
    <div class="container-fluid p-0">
        <div class="card bg-primary-subtle shadow-none position-relative overflow-hidden mb-4">
            <div class="card-body px-4 py-3">
                <div class="row align-items-center">
                    <div class="col-lg-8">
                        <h4 class="fw-semibold mb-2">Laporan Harian Kasir</h4>
                        <p class="mb-0"><span id="report-subtitle">Pertanggungjawaban kasir akhir shift / akhir hari</span></p>
                    </div>
                    <div class="col-lg-4 text-lg-end mt-3 mt-lg-0">
                        <div id="selected-store-info" class="text-muted small"></div>
                    </div>
                </div>
            </div>
        </div>

        <div class="card mb-3">
            <div class="card-body">
                <div class="row g-3 align-items-end">
                    <div class="col-lg-3">
                        <label class="form-label">Tanggal Transaksi</label>
                        <input type="date" class="form-control" id="filter-tanggal" value="<?= date('Y-m-d') ?>">
                    </div>
                    <div class="col-lg-5" id="filter-toko-wrapper" style="display:none;">
                        <label class="form-label">Filter Toko</label>
                        <select class="form-select select2" id="filter-toko" multiple>
                            <?php foreach ($tokoOptions as $row) : ?>
                                <option value="<?= esc($row['toko_id']) ?>"><?= esc($row['toko_id']) ?> - <?= esc($row['toko_nama'] ?? $row['toko_id']) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-lg-4 d-grid d-lg-flex gap-2">
                        <button type="button" class="btn btn-primary w-100" id="btn-view"><i class="ti ti-search"></i> View</button>
                        <button type="button" class="btn btn-success w-100" id="btn-print"><i class="ti ti-printer"></i> Cetak</button>
                        <button type="button" class="btn btn-info w-100 text-white" id="btn-wa"><i class="ti ti-brand-whatsapp"></i> Share</button>
                    </div>
                </div>
            </div>
        </div>

        <div class="row g-3 mb-3">
            <div class="col-md-6 col-xl-3">
                <div class="card h-100 mb-0"><div class="card-body"><div class="text-muted small">Total Pendapatan POS</div><div class="fs-6 fw-semibold mt-2" id="sum-pos">Rp 0</div><small id="sum-pos-detail" class="text-muted"></small></div></div>
            </div>
            <div class="col-md-6 col-xl-3">
                <div class="card h-100 mb-0"><div class="card-body"><div class="text-muted small">Total Diskon Item</div><div class="fs-6 fw-semibold mt-2" id="sum-diskon">Rp 0</div><small id="sum-diskon-detail" class="text-muted"></small></div></div>
            </div>
            <div class="col-md-6 col-xl-3">
                <div class="card h-100 mb-0"><div class="card-body"><div class="text-muted small">Arus Kas Kecil</div><div class="fs-6 fw-semibold mt-2" id="sum-kas">Rp 0</div><small id="sum-kas-detail" class="text-muted"></small></div></div>
            </div>
            <div class="col-md-6 col-xl-3">
                <div class="card h-100 mb-0"><div class="card-body"><div class="text-muted small">Uang Harus Disetor</div><div class="fs-6 fw-semibold mt-2" id="sum-setor">Rp 0</div><small class="text-muted">Saldo kas komputer</small></div></div>
            </div>
        </div>

        <div class="row g-3">
            <div class="col-12">
                <div class="card h-100 mb-0">
                    <div class="card-body">
                        <div class="fw-semibold mb-3">Summary Per Toko</div>
                        <div class="table-responsive">
                            <table class="table table-sm table-bordered align-middle mb-0">
                                <thead>
                                    <tr>
                                        <th>Toko</th>
                                        <th class="text-end">POS Tunai</th>
                                        <th class="text-end">POS Non Tunai</th>
                                        <th class="text-end">Kas Bersih</th>
                                        <th class="text-end">Supplier Tunai</th>
                                        <th class="text-end">Piutang Tunai</th>
                                        <th class="text-end">Uang Harus Disetor</th>
                                    </tr>
                                </thead>
                                <tbody id="table-store-summary"></tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-12">
                <div class="card h-100 mb-0">
                    <div class="card-body">
                        <div class="fw-semibold mb-3">Pertanggungjawaban Per Kasir</div>
                        <div class="table-responsive">
                            <table class="table table-sm table-bordered align-middle mb-0">
                                <thead>
                                    <tr>
                                        <th>Toko</th>
                                        <th>Kasir</th>
                                        <th class="text-end">Trx POS</th>
                                        <th class="text-end">POS Tunai</th>
                                        <th class="text-end">POS Non Tunai</th>
                                        <th class="text-end">Kas Bersih</th>
                                        <th class="text-end">Supplier Tunai</th>
                                        <th class="text-end">Piutang Tunai</th>
                                        <th class="text-end">Uang Harus Disetor</th>
                                    </tr>
                                </thead>
                                <tbody id="table-cashier-summary"></tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-lg-6">
                <div class="card h-100 mb-0">
                    <div class="card-body">
                        <div class="fw-semibold mb-3">Pendapatan POS per Metode Bayar</div>
                        <div class="table-responsive">
                            <table class="table table-sm table-bordered align-middle mb-0">
                                <tbody id="table-pos"></tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-lg-6">
                <div class="card h-100 mb-0">
                    <div class="card-body">
                        <div class="fw-semibold mb-3">Arus Kas Kecil per Akun</div>
                        <div class="table-responsive">
                            <table class="table table-sm table-bordered align-middle mb-0">
                                <thead><tr><th>Akun</th><th>Jenis</th><th class="text-end">Nominal</th></tr></thead>
                                <tbody id="table-kas"></tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-lg-6">
                <div class="card h-100 mb-0">
                    <div class="card-body">
                        <div class="fw-semibold mb-3">Rekap Pembayaran Hutang ke Supplier</div>
                        <div class="table-responsive">
                            <table class="table table-sm table-bordered align-middle mb-0">
                                <thead><tr><th>Supplier</th><th>Metode</th><th class="text-end">Nominal</th></tr></thead>
                                <tbody id="table-supplier"></tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-lg-6">
                <div class="card h-100 mb-0">
                    <div class="card-body">
                        <div class="fw-semibold mb-3">Rekap Pembayaran Piutang dari Customer</div>
                        <div class="table-responsive">
                            <table class="table table-sm table-bordered align-middle mb-0">
                                <thead><tr><th>Customer</th><th>Metode</th><th class="text-end">Nominal</th></tr></thead>
                                <tbody id="table-customer"></tbody>
                            </table>
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
    const canMultiStore = akses_menu?.akses_delete === 'Y';
    const sessionTokoId = '<?= esc((string) session('toko_id')) ?>';
    let currentReport = null;

    $(function() {
        if (canMultiStore) {
            $('#filter-toko-wrapper').show();
            $('#filter-toko').select2({
                width: '100%',
                placeholder: 'Pilih satu atau banyak toko'
            });
        }
        updateStoreInfo();
        loadReport();
    });

    $('#btn-view').on('click', loadReport);
    $('#btn-print').on('click', function() {
        window.open(buildPrintUrl(), '_blank');
    });
    $('#btn-wa').on('click', function() {
        if (!currentReport) {
            return;
        }
        window.open('https://wa.me/?text=' + encodeURIComponent(buildWhatsappText(currentReport)), '_blank');
    });
    $('#filter-toko').on('change', updateStoreInfo);

    function selectedStores() {
        if (!canMultiStore) {
            return [sessionTokoId];
        }
        return ($('#filter-toko').val() || []).filter(Boolean);
    }

    function updateStoreInfo() {
        const stores = selectedStores();
        if (!canMultiStore) {
            $('#selected-store-info').text(`Toko aktif: ${sessionTokoId}`);
            return;
        }
        $('#selected-store-info').text(stores.length ? `Toko dipilih: ${stores.join(', ')}` : 'Toko: semua toko');
    }

    function loadReport() {
        updateStoreInfo();
        $.ajax({
            type: 'POST',
            url: '<?= base_url('/lapharian/report') ?>',
            dataType: 'json',
            data: {
                tanggal: $('#filter-tanggal').val(),
                toko_ids: selectedStores()
            },
            success: function(res) {
                currentReport = res?.data || {};
                renderReport(currentReport);
            },
            error: function(xhr) {
                toastr.error(extractErrorMessage(xhr, 'Gagal memuat laporan harian'));
            }
        });
    }

    function renderReport(report) {
        const pos = report.pos || {};
        const diskon = report.discount || {};
        const kas = report.kas || {};
        $('#report-subtitle').text(`${formatDate(report.tanggal)} | Dicetak ${report.printed_at || '-'}`);
        $('#sum-pos').text(rp(pos.total || 0));
        $('#sum-pos-detail').text(`Tunai ${rp(pos.tunai || 0)} + Transfer ${rp(pos.transfer || 0)} + QRIS ${rp(pos.qris || 0)}`);
        $('#sum-diskon').text(rp(diskon.item || 0));
        $('#sum-diskon-detail').text(`Nota ${rp(diskon.nota || 0)} | Poin ${rp(diskon.redeem || 0)}`);
        $('#sum-kas').text(rp(kas.bersih || 0));
        $('#sum-kas-detail').text(`Masuk ${rp(kas.masuk || 0)} - Keluar ${rp(kas.keluar || 0)}`);
        $('#sum-setor').text(rp(report.uang_harus_disetor || 0));
        $('#table-store-summary').html(summaryRows(report.store_summaries || [], false));
        $('#table-cashier-summary').html(summaryRows(report.cashier_groups || [], true));

        $('#table-pos').html([
            rowHtml('Tunai', pos.tunai || 0),
            rowHtml('Transfer', pos.transfer || 0),
            rowHtml('QRIS', pos.qris || 0),
            rowHtml('Total POS', pos.total || 0, true)
        ].join(''));
        $('#table-kas').html(tableRows(kas.rows || [], row => `<tr><td>${esc(row.nama_akun)}</td><td>${esc(row.jenis_akun)}</td><td class="text-end">${rp(row.total || 0)}</td></tr>`));
        $('#table-supplier').html(tableRows(report.supplier?.rows || [], row => `<tr><td>${esc(row.nama_supplier)}</td><td>${esc(row.cara_bayar)}</td><td class="text-end">${rp(row.total || 0)}</td></tr>`));
        $('#table-customer').html(tableRows(report.customer?.rows || [], row => `<tr><td>${esc(row.nama_customer)}</td><td>${esc(row.cara_bayar)}</td><td class="text-end">${rp(row.total || 0)}</td></tr>`));
    }

    function rowHtml(label, amount, strong = false) {
        const left = strong ? `<strong>${label}</strong>` : label;
        const right = strong ? `<strong>${rp(amount)}</strong>` : rp(amount);
        return `<tr><td>${left}</td><td class="text-end">${right}</td></tr>`;
    }

    function tableRows(rows, renderer) {
        if (!rows.length) {
            return '<tr><td colspan="3" class="text-center text-muted">Tidak ada data</td></tr>';
        }
        return rows.map(renderer).join('');
    }

    function summaryRows(rows, withCashier) {
        if (!rows.length) {
            return `<tr><td colspan="${withCashier ? 9 : 7}" class="text-center text-muted">Tidak ada data</td></tr>`;
        }
        return rows.map(row => {
            const nonCash = Number(row.pos_transfer || 0) + Number(row.pos_qris || 0);
            if (withCashier) {
                return `<tr>
                    <td>${esc(row.toko_nama || row.toko_id)}</td>
                    <td>${esc(row.nama_kasir || row.kasir)}<br><small class="text-muted">${esc(row.kasir || '-')}</small></td>
                    <td class="text-end">${Number(row.total_transaksi || 0).toLocaleString('id-ID')}</td>
                    <td class="text-end">${rp(row.pos_tunai || 0)}</td>
                    <td class="text-end">${rp(nonCash)}</td>
                    <td class="text-end">${rp(row.kas_bersih || 0)}</td>
                    <td class="text-end">${rp(row.supplier_tunai || 0)}</td>
                    <td class="text-end">${rp(row.customer_tunai || 0)}</td>
                    <td class="text-end fw-semibold">${rp(row.uang_harus_disetor || 0)}</td>
                </tr>`;
            }
            return `<tr>
                <td>${esc(row.toko_nama || row.toko_id)}</td>
                <td class="text-end">${rp(row.pos_tunai || 0)}</td>
                <td class="text-end">${rp(nonCash)}</td>
                <td class="text-end">${rp(row.kas_bersih || 0)}</td>
                <td class="text-end">${rp(row.supplier_tunai || 0)}</td>
                <td class="text-end">${rp(row.customer_tunai || 0)}</td>
                <td class="text-end fw-semibold">${rp(row.uang_harus_disetor || 0)}</td>
            </tr>`;
        }).join('');
    }

    function buildPrintUrl() {
        const params = new URLSearchParams();
        params.set('tanggal', $('#filter-tanggal').val());
        selectedStores().forEach(store => params.append('toko_ids[]', store));
        return `<?= base_url('/lapharian/struk') ?>?${params.toString()}`;
    }

    function buildWhatsappText(report) {
        const pos = report.pos || {};
        const kas = report.kas || {};
        const supplier = report.supplier || {};
        const customer = report.customer || {};
        return [
            'LAPORAN HARIAN KASIR',
            `Tanggal: ${formatDate(report.tanggal)}`,
            `Dicetak: ${report.printed_at || '-'}`,
            `Toko: ${(report.stores || []).map(row => row.toko_nama || row.toko_id).join(', ') || '-'}`,
            '',
            'SUMMARY PER TOKO',
            ...(report.store_summaries || []).map(row => `${row.toko_nama || row.toko_id}: ${rp(row.uang_harus_disetor || 0)} (Tunai POS ${rp(row.pos_tunai || 0)}, Kas ${rp(row.kas_bersih || 0)})`),
            '',
            'PERTANGGUNGJAWABAN PER KASIR',
            ...(report.cashier_groups || []).map(row => `${row.toko_nama || row.toko_id} - ${row.nama_kasir || row.kasir}: ${rp(row.uang_harus_disetor || 0)} (Trx ${Number(row.total_transaksi || 0).toLocaleString('id-ID')}, POS Tunai ${rp(row.pos_tunai || 0)})`),
            '',
            `POS Tunai: ${rp(pos.tunai || 0)}`,
            `POS Transfer: ${rp(pos.transfer || 0)}`,
            `POS QRIS: ${rp(pos.qris || 0)}`,
            `Total POS: ${rp(pos.total || 0)}`,
            `Diskon Item: ${rp(report.discount?.item || 0)}`,
            `Kas Masuk: ${rp(kas.masuk || 0)}`,
            `Kas Keluar: ${rp(kas.keluar || 0)}`,
            `Bayar Supplier Tunai: ${rp(supplier.tunai || 0)}`,
            `Terima Piutang Tunai: ${rp(customer.tunai || 0)}`,
            '',
            `UANG HARUS DISETOR: ${rp(report.uang_harus_disetor || 0)}`
        ].join('\n');
    }

    function rp(value) {
        return 'Rp ' + formatMoneyValue(value || 0);
    }

    function formatDate(value) {
        return value ? moment(value, 'YYYY-MM-DD').format('DD/MM/YYYY') : '-';
    }

    function esc(value) {
        return $('<div>').text(value || '-').html();
    }
</script>
<?= $this->endSection('javascript') ?>
