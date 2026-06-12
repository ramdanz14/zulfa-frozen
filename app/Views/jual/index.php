<?php

/**
 * @var string $akses_menu
 * @var array $initialData
 */
$toko = $initialData['toko'] ?? [];
$runningText = array_map(
    static fn(array $row): string => trim((string) ($row['isi_pengumuman'] ?? '')),
    $initialData['running_text'] ?? []
);
$marqueeText = implode('   ::::    ', array_values(array_filter($runningText)));
if ($marqueeText === '') {
    $marqueeText = 'POS aktif. Scan barcode, cari item, pilih member, lalu lanjutkan ke pembayaran dan cetak struk.';
}
?>
<!DOCTYPE html>
<html lang="id" dir="ltr" data-bs-theme="light" data-color-theme="<?= session('toko_theme') ?>">

<head>
    <meta charset="UTF-8" />
    <meta http-equiv="X-UA-Compatible" content="IE=edge" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <link rel="shortcut icon" type="image/png" href="<?= base_url(APP_LOGO_PATH); ?>" />
    <link rel="stylesheet" href="<?= base_url(); ?>/assets/css/styles.css" />
    <link rel="stylesheet" href="<?= base_url(); ?>/assets/libs/sweetalert2/dist/sweetalert2.min.css">
    <link rel="stylesheet" href="<?= base_url(); ?>/assets/libs/select2/dist/css/select2.min.css" />
    <title><?= esc(APP_NAME); ?> | <?= esc($title ?? 'POS Kasir'); ?></title>
    <style>
        *,
        *::before,
        *::after {
            box-sizing: border-box;
        }

        :root {
            --pos-bg: #eef3ea;
            --pos-panel: #ffffff;
            --pos-border: rgba(15, 23, 42, 0.08);
            --pos-shadow: 0 18px 36px rgba(15, 23, 42, 0.08);
            --pos-accent: #173f35;
            --pos-accent-soft: #edf7f2;
            --pos-warm: #fff4d8;
            --pos-text-soft: #64748b;
            --pos-danger-soft: #fff0ef;
        }

        html,
        body {
            height: 100%;
        }

        body {
            margin: 0;
            background:
                radial-gradient(circle at top left, rgba(255, 214, 153, 0.35), transparent 28%),
                linear-gradient(180deg, #f7fbf4 0%, var(--pos-bg) 100%);
            color: #0f172a;
            overflow-x: hidden;
        }

        .pos-app {
            height: 100vh;
            display: flex;
            flex-direction: column;
            gap: 6px;
            padding: 6px;
            width: 100%;
            overflow-x: hidden;
        }

        .pos-panel {
            background: var(--pos-panel);
            border: 1px solid var(--pos-border);
            border-radius: 22px;
            box-shadow: var(--pos-shadow);
        }

        .pos-header {
            display: grid;
            grid-template-columns: 280px 1fr;
            gap: 12px;
            align-items: center;
            padding: 6px 18px;
            /* background: linear-gradient(135deg, var(--pos-warm) 0%, #fffdf7 100%); */
        }

        .pos-header-brand {
            display: flex;
            align-items: center;
            gap: 12px;
            flex-wrap: wrap;
        }

        .pos-header-brand .store-badge {
            background: #0f172a;
            color: #fff;
            border-radius: 999px;
            padding: 6px 10px;
            font-size: 12px;
            letter-spacing: 0.08em;
        }

        .pos-header-brand h1 {
            margin: 0;
            font-size: 1.2rem;
            font-weight: 800;
        }

        .pos-marquee {
            min-width: 0;
            color: #5b4420;
            font-weight: 600;
        }

        .pos-body {
            flex: 1;
            min-height: 0;
            display: grid;
            grid-template-columns: minmax(320px, 1.5fr) minmax(0, 1.5fr);
            grid-template-areas:
                "summary customer"
                "search search"
                "cart cart";
            grid-template-rows: auto auto minmax(0, 1fr);
            gap: 6px;
        }

        .layout-summary {
            grid-area: summary;
        }

        .layout-customer {
            grid-area: customer;
        }

        .layout-search {
            grid-area: search;
        }

        .layout-cart {
            grid-area: cart;
            min-height: 0;
        }

        .pos-customer-panel {
            padding: 12px;
        }

        .pos-customer-grid {
            display: grid;
            grid-template-columns: minmax(0, 1.8fr) minmax(100px, 0.5fr);
            gap: 14px;
            align-items: start;
        }

        .member-actions {
            display: flex;
            gap: 10px;
            flex-wrap: wrap;
            justify-content: flex-end;
        }

        .member-summary {
            display: grid;
            grid-template-columns: minmax(0, 1fr) minmax(100px, 1fr);
            margin-top: 10px;
            border-radius: 16px;
            background: linear-gradient(145deg, #f8fbff 0%, #f7faf8 100%);
            border: 1px dashed rgba(15, 23, 42, 0.12);
            padding: 12px 14px;
            min-height: 86px;
        }

        .pos-search-panel {
            padding: 12px;
            position: relative;
        }

        .pos-search-row {
            display: grid;
            grid-template-columns: 1fr auto auto;
            gap: 10px;
        }

        .search-result-list {
            margin-top: 10px;
            border: 1px solid rgba(15, 23, 42, 0.08);
            border-radius: 16px;
            max-height: 320px;
            overflow: auto;
            background: #fff;
        }

        .search-result-item {
            padding: 12px 14px;
            border-bottom: 1px solid rgba(15, 23, 42, 0.06);
            cursor: pointer;
        }

        .search-result-item:last-child {
            border-bottom: 0;
        }

        .search-result-item:hover {
            background: #f9fcf7;
        }

        .search-result-item.is-blocked {
            background: var(--pos-danger-soft);
        }

        .shortcut-text {
            display: inline-block;
            margin-left: 6px;
            font-size: 11px;
            font-weight: 700;
            letter-spacing: 0.04em;
            opacity: 0.74;
        }

        .shortcut-note {
            color: var(--pos-text-soft);
            font-size: 12px;
        }

        .shortcut-note strong {
            color: #1f2937;
        }

        .cart-panel {
            flex: 1;
            min-height: 0;
            padding: 10px;
            display: flex;
            flex-direction: column;
        }

        .cart-scroll {
            flex: 1;
            min-height: 0;
            overflow: auto;
        }

        .cart-list {
            display: flex;
            flex-direction: column;
        }

        /* ROW UTAMA */
        .cart-row {
            display: grid;
            grid-template-columns:
                minmax(260px, 1.2fr) minmax(520px, 2fr) minmax(120px, 0.5fr) 42px;
            align-items: center;
            gap: 10px;

            padding: 0px 10px;
            min-height: 64px;
            border: 1px solid rgba(15, 23, 42, 0.08);
            border-radius: 14px;
            background: #fff;
        }

        /* KOLOM NAMA ITEM */
        .cart-row-main {
            min-width: 0;
        }

        .cart-row-main .d-flex {
            align-items: center !important;
            gap: 8px;
        }

        .cart-item-name {
            min-width: 0;
            font-size: 13px;
            font-weight: 800;
            color: #15212f;
            line-height: 1.2;

            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }

        .cart-item-name small {
            font-size: 11px;
            font-weight: 600;
        }

        .cart-item-price {
            margin-top: 3px;
            font-size: 12px;
            font-weight: 800;
            color: #173f35;
        }

        /* TOMBOL HAPUS */
        .btn-remove-row {
            grid-column: 4;
            grid-row: 1;

            justify-self: center;
            align-self: center;

            width: 34px;
            height: 34px;
            min-width: 34px;
            min-height: 34px;
            padding: 0;
            border-radius: 10px;
        }

        .btn-remove-row .fs-5 {
            font-size: 16px !important;
        }

        /* KOLOM CONTROL */
        .cart-row-controls {
            min-width: 0;
            display: grid;
            grid-template-columns: 130px minmax(120px, 1fr) 120px;
            gap: 8px;
            align-items: end;
        }

        .cart-control-block {
            min-width: 0;
        }

        .cart-control-block label {
            display: block;
            font-size: 10px;
            line-height: 1;
            text-transform: uppercase;
            letter-spacing: 0.05em;
            color: var(--pos-text-soft);
            margin-bottom: 4px;
            font-weight: 700;
        }

        /* QTY */
        .qty-control {
            display: grid;
            grid-template-columns: 34px minmax(44px, 1fr) 34px;
            gap: 4px;
            align-items: center;
        }

        .qty-control .btn {
            height: 34px;
            padding: 0;
            font-size: 16px;
            font-weight: 800;
            border-radius: 9px;
        }

        .qty-control .cart-qty {
            height: 34px;
            text-align: center;
            font-size: 13px;
            font-weight: 700;
            padding: 4px 6px;
        }

        /* SATUAN */
        .unit-static {
            min-height: 34px;
            border: 1px solid rgba(15, 23, 42, 0.14);
            border-radius: 9px;
            padding: 4px 8px;
            background: #fff;

            display: flex;
            flex-direction: column;
            justify-content: center;
            gap: 0;

            font-size: 12px;
            font-weight: 800;
            line-height: 1.1;
        }

        .unit-static small {
            font-size: 10px;
            font-weight: 600;
            line-height: 1.1;
        }

        .cart-control-block .form-select,
        .cart-control-block .form-control {
            height: 34px;
            min-height: 34px;
            font-size: 12px;
            font-weight: 700;
            padding: 4px 8px;
            border-radius: 9px;
        }

        /* KOLOM NETTO */
        .cart-row-summary {
            min-width: 0;
            text-align: right;
        }

        .cart-netto {
            min-height: 34px;
            display: flex;
            align-items: center;
            justify-content: flex-end;

            font-size: 13px;
            font-weight: 900;
            color: #173f35;
            white-space: nowrap;
        }

        /* DESKTOP KECIL / TABLET LANDSCAPE */
        @media (min-width: 992px) and (max-width: 1280px) {
            .cart-row {
                grid-template-columns: minmax(220px, 1.4fr) minmax(370px, 1.3fr) minmax(90px, 0.4fr);
                gap: 8px;
                padding: 7px 8px;
            }

            .cart-row-controls {
                grid-template-columns: 118px minmax(105px, 1fr) 110px;
                gap: 6px;
            }

            .cart-item-name {
                font-size: 12.5px;
            }

            .cart-item-price,
            .cart-netto {
                font-size: 12.5px;
            }

            .qty-control {
                grid-template-columns: 32px minmax(40px, 1fr) 32px;
            }

            .qty-control .btn,
            .qty-control .cart-qty,
            .cart-control-block .form-select,
            .cart-control-block .form-control,
            .unit-static {
                height: 32px;
                min-height: 32px;
            }

            .btn-remove-row {
                width: 32px;
                height: 32px;
                min-width: 32px;
                min-height: 32px;
            }
        }

        .cart-empty-state {
            text-align: center;
            color: var(--pos-text-soft);
            padding: 32px 16px;
            border: 1px dashed rgba(15, 23, 42, 0.12);
            border-radius: 18px;
        }

        .summary-card {

            padding: 16px;
            color: #fff;
            background: linear-gradient(160deg, #183d34 0%, #246655 55%, #2e806a 100%);
        }

        .summary-card .label {
            font-size: 12px;
            letter-spacing: 0.12em;
            text-transform: uppercase;
            opacity: 0.84;
        }

        .summary-card .amount {
            font-size: clamp(2rem, 3.8vw, 3.15rem);
            font-weight: 800;
            line-height: 1.05;
            margin-top: 8px;
        }

        .summary-grid {
            display: grid;
            grid-template-columns: 0.5fr 0.5fr 1fr 1fr;
            gap: 10px;
            margin-top: 16px;
        }

        .summary-grid>div {
            border-radius: 14px;
            padding: 10px 12px;
            background: rgba(255, 255, 255, 0.1);
        }

        .summary-grid span {
            display: block;
            font-size: 12px;
            opacity: 0.8;
        }

        .summary-grid strong {
            display: block;
            margin-top: 4px;
            font-size: 15px;
        }

        .pos-footer {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 12px;
            padding: 14px 16px;
        }

        .footer-left,
        .footer-right {
            display: flex;
            align-items: center;
            gap: 10px;
            flex-wrap: wrap;
        }

        .payment-total-card {
            border-radius: 18px;
            padding: 18px;
            background: linear-gradient(145deg, #f5f7ff 0%, #fff 100%);
            border: 1px solid rgba(13, 110, 253, 0.12);
        }

        .payment-status-panel {
            border-radius: 16px;
            padding: 14px;
            background: #f8f9fa;
            min-height: 76px;
        }

        .quick-cash-grid {
            display: grid;
            grid-template-columns: repeat(3, minmax(0, 1fr));
            gap: 8px;
        }

        .payment-member-box {
            border-radius: 16px;
            background: var(--pos-accent-soft);
            padding: 12px 14px;
        }

        .payment-left-stack,
        .payment-right-stack {
            display: flex;
            flex-direction: column;
            gap: 14px;
        }

        .cash-received-card {
            border-radius: 18px;
            padding: 18px;
            background: #fff;
            border: 1px solid rgba(15, 23, 42, 0.08);
        }

        .change-display {
            border-radius: 18px;
            padding: 18px;
            background: linear-gradient(160deg, #f0fdf4 0%, #e7f8ec 100%);
            border: 1px solid rgba(22, 163, 74, 0.18);
        }

        .change-display.is-warning {
            background: linear-gradient(160deg, #fff7ed 0%, #ffedd5 100%);
            border-color: rgba(234, 88, 12, 0.18);
        }

        .change-display.is-danger {
            background: linear-gradient(160deg, #fef2f2 0%, #fee2e2 100%);
            border-color: rgba(220, 38, 38, 0.18);
        }

        .change-amount {
            font-size: clamp(2rem, 4vw, 3rem);
            line-height: 1;
            font-weight: 900;
            color: #15803d;
        }

        .change-display.is-warning .change-amount {
            color: #c2410c;
        }

        .change-display.is-danger .change-amount {
            color: #b91c1c;
        }

        .payment-shortcut-list {
            display: flex;
            flex-wrap: wrap;
            gap: 8px 12px;
            font-size: 12px;
            color: var(--pos-text-soft);
        }

        .payment-shortcut-list strong {
            color: #1f2937;
        }

        .select2-container--default .select2-selection--single {
            height: calc(3rem + 2px);
            border-radius: 14px;
            border-color: rgba(15, 23, 42, 0.12);
            padding: 9px 12px;
        }

        .select2-container--default .select2-selection--single .select2-selection__rendered {
            line-height: 28px;
            padding-left: 0;
        }

        .select2-container--default .select2-selection--single .select2-selection__arrow {
            height: 100%;
            right: 8px;
        }

        @media (max-width: 991.98px) {
            body {
                overflow: auto;
            }

            .pos-app {
                height: auto;
                min-height: 100vh;
            }

            .pos-header,
            .pos-customer-grid {
                grid-template-columns: 1fr;
            }

            .pos-body {
                grid-template-columns: 1fr;
                grid-template-areas:
                    "summary"
                    "customer"
                    "search"
                    "cart";
                grid-template-rows: auto;
            }

            .member-actions {
                justify-content: flex-start;
            }

            .cart-panel,
            .cart-scroll {
                min-height: unset;
            }

            .cart-row {
                grid-template-columns: 1fr;
            }

            .cart-row-controls {
                grid-template-columns: repeat(3, minmax(0, 1fr));
            }
        }

        @media (max-width: 767.98px) {
            .pos-app {
                padding: 10px;
                gap: 10px;
            }

            .pos-header {
                grid-template-columns: 1fr;
                padding: 14px;
            }

            .pos-search-row {
                grid-template-columns: 1fr auto auto;
            }

            .pos-footer {
                flex-direction: column;
                align-items: stretch;
            }

            .footer-right .btn,
            .member-actions .btn {
                flex: 1;
            }

            .quick-cash-grid {
                grid-template-columns: repeat(2, minmax(0, 1fr));
            }

            .pos-customer-panel,
            .pos-search-panel,
            .cart-panel,
            .summary-card,
            .pos-footer {
                padding-left: 12px;
                padding-right: 12px;
            }

            .member-actions {
                gap: 8px;
            }

            .member-actions .btn,
            .footer-right .btn {
                min-width: 0;
                font-size: 0.95rem;
                padding-left: 10px;
                padding-right: 10px;
            }

            .cart-row {
                grid-template-columns: minmax(0, 1fr) minmax(0, 1fr) 40px;
                align-items: start;
                gap: 8px;
                padding: 8px 10px;
            }

            .cart-row-controls {
                display: contents;
            }

            .cart-row-summary {
                grid-column: 2;
                grid-row: 3;
                text-align: right;
            }

            .qty-control {
                grid-template-columns: 44px minmax(0, 1fr) 44px;
            }

            .cart-row-main {
                grid-column: 1 / 4;
                grid-row: 1;
            }

            .cart-row-main .d-flex {
                flex-wrap: wrap;
            }

            .cart-item-name {
                white-space: normal;
                overflow: visible;
                text-overflow: unset;
            }

            .cart-row-controls .cart-control-block:nth-child(1) {
                grid-column: 1;
                grid-row: 2;
            }

            .cart-row-controls .cart-control-block:nth-child(2) {
                grid-column: 2 / 4;
                grid-row: 2;
            }

            .cart-row-controls .cart-control-block:nth-child(3) {
                grid-column: 1;
                grid-row: 3;
            }

            .btn-remove-row {
                grid-column: 3;
                grid-row: 3;
                justify-self: end;
                align-self: end;
            }

            .shortcut-note {
                line-height: 1.45;
            }
        }
    </style>
</head>

<body>
    <div class="pos-app">
        <header class="pos-panel pos-header bg-primary">
            <div class="pos-header-brand">
                <span class="store-badge"><?= esc($toko['toko_id'] ?? session('toko_id')) ?></span>
                <div>
                    <h1 class="text-light"><?= esc($toko['toko_nama'] ?? 'POS Kasir') ?></h1>
                </div>
            </div>
            <div class="pos-marquee">
                <marquee behavior="scroll" direction="left" class="text-light"><?= esc($marqueeText) ?></marquee>
            </div>
        </header>

        <div class="pos-body">
            <div class="pos-panel summary-card layout-summary">
                <div class="label">Total Belanja</div>
                <div class="amount" id="summary-netto">Rp 0</div>
                <div class="summary-grid">
                    <div><span>Jenis Barang</span><strong id="summary-item-kind">0</strong></div>
                    <div><span>Total Qty</span><strong id="summary-total-qty">0</strong></div>
                    <div><span>Gross</span><strong id="summary-gross">Rp 0</strong></div>
                    <div><span>Total Diskon</span><strong id="summary-discount">Rp 0</strong></div>
                </div>
            </div>

            <div class="pos-panel pos-customer-panel layout-customer">
                <div class="pos-customer-grid">
                    <div>
                        <label class="form-label mb-2">Member: cari dengan no HP / nama / cust_id</label>
                        <select class="form-select" id="customer-select"></select>
                        <div class="member-summary" id="member-summary"></div>
                    </div>
                    <div>
                        <div class="member-actions">
                            <button type="button" class="btn btn-outline-primary w-100" id="btn-register-member"><i class="ti ti-user-plus d-none d-xl-block"></i> Member Baru</button>
                            <button type="button" class="btn btn-outline-secondary w-100" id="btn-hold-cart"><i class="ti ti-bookmark d-none d-xl-block"></i> Pending </button>
                            <button type="button" class="btn btn-outline-secondary w-100" id="btn-recall-cart"><i class="ti ti-history d-none d-xl-block"></i> Recall </button>
                        </div>
                    </div>
                </div>
            </div>

            <div class="pos-panel pos-search-panel layout-search">
                <div class="pos-search-row">
                    <input type="text" class="form-control form-control-lg" id="item-search" placeholder="Scan barcode / kode item / nama produk lalu tekan Enter">
                    <button type="button" class="btn btn-dark" id="btn-item-search"><i class="ti ti-search"></i></button>
                    <button type="button" class="btn btn-outline-secondary d-none d-lg-block" id="btn-focus-search"><i class="ti ti-focus-2"></i></button>
                </div>
                <div class="shortcut-note mt-2">Shortcut: <strong>F2</strong> hold cart, <strong>F5</strong> reset, <strong>F12</strong> bayar, <strong>Esc</strong> tutup modal/reset hasil cari.</div>
                <div id="item-search-result" class="search-result-list d-none"></div>
            </div>

            <div class="pos-panel cart-panel layout-cart">
                <div class="cart-scroll">
                    <div id="cart-body" class="cart-list">
                        <div class="cart-empty-state">Keranjang masih kosong</div>
                    </div>
                </div>
            </div>


        </div>

        <footer class="pos-panel pos-footer">
            <div class="footer-left">
                <span class="badge bg-light text-dark" id="current-customer-badge">CUST-GENERAL</span>
            </div>
            <div class="footer-right">
                <button type="button" class="btn btn-danger" id="btn-reset-cart"><i class="ti ti-trash"></i> Reset Keranjang </button>
                <button type="button" class="btn btn-outline-dark" id="btn-exit-pos"><i class="ti ti-door-exit"></i> Keluar POS</button>
                <button type="button" class="btn btn-success" id="btn-pay"><i class="ti ti-cash"></i> Bayar <span class="shortcut-text">F12</span></button>
            </div>
        </footer>
    </div>

    <div class="modal fade" id="modal-quick-member" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Daftar Member Baru</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form id="form-quick-member">
                    <div class="modal-body">
                        <div class="form-group mb-3">
                            <label class="form-label">Nama Member</label>
                            <input type="text" class="form-control" id="member-nama" name="nama" required>
                        </div>
                        <div class="form-group">
                            <label class="form-label">No HP</label>
                            <input type="text" class="form-control" id="member-kontak" name="kontak" required>
                        </div>
                    </div>
                    <div class="modal-footer justify-content-between">
                        <button type="submit" class="btn btn-primary">Simpan & Pilih Member</button>
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Tutup</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <div class="modal fade" id="modal-payment" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-fullscreen-sm-down modal-xl modal-dialog-scrollable">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Konfirmasi Pembayaran</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="row g-3">
                        <div class="col-lg-5">
                            <div class="payment-left-stack">
                                <div class="payment-total-card">
                                    <div class="small text-muted">Total Tagihan Akhir</div>
                                    <div class="display-6 fw-bold" id="payment-total-netto">Rp 0</div>
                                </div>

                                <div class="payment-member-box">
                                    <div class="fw-semibold" id="payment-member-name">Pelanggan Umum</div>
                                    <div class="small text-muted" id="payment-member-extra">CUST-GENERAL</div>
                                    <div class="small text-muted mt-2" id="payment-credit-note">Non-member wajib lunas. Member boleh kredit jika pembayaran kurang.</div>
                                </div>

                                <div>
                                    <label class="form-label">Diskon Nota</label>
                                    <input type="text" class="form-control money" id="diskon-nota" value="0">
                                </div>

                                <div>
                                    <label class="form-label">Redeem Poin Member</label>
                                    <input type="number" class="form-control" id="redeem-points" min="0" value="0">
                                    <small class="text-muted">Default POS ini memakai asumsi 1 poin = Rp 1.</small>
                                </div>

                                <div class="payment-status-panel" id="payment-status-panel"></div>


                                <div id="payment-credit-deadline-wrap">
                                    <label class="form-label">Jatuh Tempo Kredit Member</label>
                                    <input type="date" class="form-control" id="payment-jatuh-tempo" value="<?= esc(date('Y-m-d', strtotime('+30 days'))) ?>">
                                    <small class="text-muted">Input ini dipakai jika pembayaran kurang dan akan dicatat sebagai piutang.</small>
                                </div>
                            </div>
                        </div>
                        <div class="col-lg-7">
                            <div class="payment-right-stack">
                                <div class="table-responsive">
                                    <table class="table table-bordered table-sm align-middle" id="payment-table">
                                        <thead>
                                            <tr>
                                                <th>Metode</th>
                                                <th>Nominal Bayar</th>
                                                <th>Bank/E-Wallet</th>
                                                <th>Rekening</th>
                                                <th></th>
                                            </tr>
                                        </thead>
                                        <tbody></tbody>
                                    </table>
                                </div>
                                <div class="d-flex gap-2">
                                    <button type="button" class="btn btn-outline-primary btn-sm" id="btn-add-payment-row"><i class="ti ti-plus"></i> Tambah Metode</button>
                                </div>

                                <div class="cash-received-card">
                                    <label class="form-label">Uang Tunai Diterima</label>
                                    <input type="text" class="form-control money form-control-lg" id="payment-cash-received" value="0">
                                    <small class="text-muted d-block mt-2">Kolom ini otomatis mengikuti nominal metode TUNAI agar tidak terjadi double input.</small>
                                    <div class="quick-cash-grid mt-3" id="quick-cash-buttons"></div>
                                </div>

                                <div class="change-display" id="payment-change-display">
                                    <div class="small text-muted">Kembalian / Kekurangan</div>
                                    <div class="change-amount" id="payment-change-amount">Rp 0</div>
                                    <div class="small mt-2" id="payment-change-caption">Uang pas.</div>
                                </div>


                                <div class="payment-shortcut-list">
                                    <span><strong>F12</strong> simpan transaksi</span>
                                    <span><strong>Esc</strong> tutup modal</span>
                                    <span><strong>Alt+1..5</strong> pilih tombol uang cepat</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer justify-content-between">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Tutup</button>
                    <button type="button" class="btn btn-success" id="btn-save-sale">Simpan & Cetak Struk</button>
                </div>
            </div>
        </div>
    </div>

    <script src="<?= base_url(); ?>/assets/js/vendor.min.js"></script>
    <script src="<?= base_url(); ?>/assets/libs/bootstrap/dist/js/bootstrap.bundle.min.js"></script>
    <script src="<?= base_url(); ?>/assets/libs/simplebar/dist/simplebar.min.js"></script>
    <script src="<?= base_url(); ?>/assets/js/theme/app.init.js"></script>
    <script src="<?= base_url(); ?>/assets/js/theme/theme.js"></script>
    <script src="<?= base_url(); ?>/assets/js/theme/app.min.js"></script>
    <script src="<?= base_url(); ?>/assets/libs/sweetalert2/dist/sweetalert2.min.js"></script>
    <script src="<?= base_url(); ?>/assets/js/plugins/toastr-init.js"></script>
    <script src="<?= base_url(); ?>/assets/libs/select2/dist/js/select2.min.js"></script>
    <script>
        function normalizeMoneyValue(value) {
            const raw = String(value ?? '').replace(/[^\d-]/g, '');
            if (raw === '' || raw === '-') return '0';
            return raw;
        }

        function formatMoneyValue(value) {
            const raw = String(value ?? '').trim();
            const numeric = Number(raw.replace(/,/g, ''));
            if (raw !== '' && Number.isFinite(numeric)) {
                return Math.round(numeric).toLocaleString('en-US');
            }
            const normalized = normalizeMoneyValue(raw);
            const number = parseInt(normalized, 10) || 0;
            return number.toLocaleString('en-US');
        }

        function applyMoneyMask(scope) {
            const $scope = scope ? $(scope) : $(document);
            $scope.find('input.money').each(function() {
                const $input = $(this);
                if ($input.data('money-init') === true) return;
                $input.data('money-init', true);
                $input.val(formatMoneyValue($input.val()));
                $input.on('input', function() {
                    const caret = this.selectionStart;
                    const beforeLen = this.value.length;
                    this.value = formatMoneyValue(this.value);
                    const afterLen = this.value.length;
                    const delta = afterLen - beforeLen;
                    const nextPos = Math.max(0, (caret || 0) + delta);
                    this.setSelectionRange(nextPos, nextPos);
                });
            });
        }

        function extractErrorMessage(xhr, fallback) {
            if (!xhr) return fallback;
            if (xhr.responseJSON && xhr.responseJSON.data) return xhr.responseJSON.data;
            if (xhr.responseText) {
                try {
                    const parsed = JSON.parse(xhr.responseText);
                    if (parsed && parsed.data) return parsed.data;
                } catch (err) {
                    return xhr.responseText;
                }
            }
            return fallback;
        }
    </script>
    <script>
        const akses_menu = <?= $akses_menu ?>;
        const initialData = <?= json_encode($initialData, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) ?>;
        const quickMemberModal = new bootstrap.Modal(document.getElementById('modal-quick-member'));
        const paymentModal = new bootstrap.Modal(document.getElementById('modal-payment'));
        const holdStorageKey = `pos_hold_cart_${initialData?.toko?.toko_id || 'default'}`;
        const defaultJatuhTempo = '<?= esc(date('Y-m-d', strtotime('+30 days'))) ?>';
        const quickCashHotkeys = ['1', '2', '3', '4', '5'];
        const isEditMode = initialData?.mode === 'edit';
        const editSale = initialData?.edit_sale || null;
        const saleSaveUrl = initialData?.save_url || '<?= base_url('/jual') ?>';
        const exitUrl = initialData?.exit_url || '<?= base_url('/main') ?>';

        let cartRows = [];
        let paymentRows = [];
        let selectedCustomer = initialData?.customer_general || {
            cust_id: 'CUST-GENERAL',
            nama: 'Pelanggan Umum',
            kontak: '',
            poin: 0,
            outstanding_piutang: 0
        };

        $(function() {
            $('#customer-select').select2({
                width: '100%',
                placeholder: 'Cari member berdasarkan HP / nama / cust_id',
                ajax: {
                    url: '<?= base_url('/jual/search-customer') ?>',
                    dataType: 'json',
                    delay: 200,
                    data: function(params) {
                        return {
                            term: params.term || ''
                        };
                    },
                    processResults: function(data) {
                        return data;
                    }
                }
            });

            const generalOption = new Option('CUST-GENERAL - Pelanggan Umum', 'CUST-GENERAL', true, true);
            $('#customer-select').append(generalOption).trigger('change');
            $('#customer-select').on('select2:select', function(e) {
                selectedCustomer = e.params.data?.payload || initialData.customer_general;
                refreshMemberSummary();
                recalcSummary();
            });

            if (isEditMode) {
                $('#btn-save-sale').text('Update & Cetak Struk');
                $('#btn-hold-cart, #btn-recall-cart').addClass('d-none');
            }

            $('#btn-item-search').on('click', searchItem);
            $('#btn-focus-search').on('click', function() {
                $('#item-search').trigger('focus');
            });
            $('#item-search').on('keydown', function(e) {
                if (e.key === 'Enter') {
                    e.preventDefault();
                    searchItem();
                }
            });

            $('#btn-register-member').on('click', function() {
                $('#form-quick-member')[0].reset();
                quickMemberModal.show();
            });
            $('#form-quick-member').on('submit', function(e) {
                e.preventDefault();
                registerQuickMember();
            });

            $('#btn-reset-cart').on('click', resetCartWithConfirm);
            $('#btn-hold-cart').on('click', holdCart);
            $('#btn-recall-cart').on('click', recallCart);
            $('#btn-exit-pos').on('click', tryExitPos);
            $('#btn-pay').on('click', openPaymentModal);
            $('#btn-add-payment-row').on('click', function() {
                paymentRows.push(buildPaymentRow());
                syncCashReceivedToTunaiAllocation();
                renderPaymentRows();
            });
            $('#btn-save-sale').on('click', saveSale);

            $('#diskon-nota').on('input blur', function() {
                recalcSummary();
                renderPaymentStatus();
            });
            $('#redeem-points').on('input', function() {
                recalcSummary();
                renderPaymentStatus();
            });
            $('#payment-cash-received').on('input blur', function() {
                // 1. Jalankan sinkronisasi dulu
                syncTunaiAllocationFromCashReceived();

                // 2. Baru jalankan render setelahnya
                renderPaymentStatus();
            });
            $(document).on('keydown', handleGlobalShortcut);
            $('#modal-payment').on('hidden.bs.modal', focusSearch);

            let qtyTimer;
            $(document).on('input', '.cart-qty', function() {
                const idx = Number($(this).data('idx'));
                if (!cartRows[idx]) return;
                cartRows[idx].qty_jual = Number($(this).val() || 0);
                clearTimeout(qtyTimer);
                qtyTimer = setTimeout(function() {
                    recalcCartRow(idx);
                }, 250);
            });

            $(document).on('blur change', '.cart-qty', function() {
                const idx = Number($(this).data('idx'));
                const row = cartRows[idx];
                if (!row) return;
                let qty = Number($(this).val() || 0);
                const max = Number(row.max_qty || 0);
                if (qty > max) {
                    qty = max;
                    toastr.error('Stok tidak mencukupi');
                }
                if (qty <= 0) {
                    qty = max > 0 ? Math.min(1, max) : 0;
                }
                row.qty_jual = qty;
                recalcCartRow(idx);
                renderCart();
            });

            $(document).on('change', '.cart-satuan', function() {
                changeCartUnit(Number($(this).data('idx')), $(this).val());
            });

            $(document).on('blur', '.cart-diskon', function() {
                const idx = Number($(this).data('idx'));
                if (!cartRows[idx]) return;
                let diskon = Number(normalizeMoneyValue($(this).val() || 0));
                const maxDiskon = Number(cartRows[idx].max_diskon || 0);
                if (diskon > maxDiskon) {
                    diskon = maxDiskon;
                    toastr.error('Nilai diskon melebihi margin keuntungan produk');
                }
                if (diskon < 0) {
                    diskon = 0;
                }
                cartRows[idx].diskon_item = diskon;
                recalcCartRow(idx);
                renderCart();
            });

            $(document).on('click', '.btn-remove-row', function() {
                const idx = Number($(this).data('idx'));
                cartRows.splice(idx, 1);
                renderCart();
            });

            $(document).on('click', '.btn-qty-minus', function() {
                adjustQty(Number($(this).data('idx')), -1);
            });

            $(document).on('click', '.btn-qty-plus', function() {
                adjustQty(Number($(this).data('idx')), 1);
            });

            $(document).on('click', '.search-result-item', function() {
                const kodeItem = $(this).data('kode-item');
                if (!kodeItem || $(this).data('blocked') === 1) {
                    return;
                }
                pickItem(kodeItem);
            });

            $(document).on('input blur', '.payment-amount', function() {
                const idx = Number($(this).data('idx'));
                if (!paymentRows[idx]) return;
                paymentRows[idx].nominal_bayar = Number(normalizeMoneyValue($(this).val() || 0));
                renderPaymentStatus();
            });

            $(document).on('change', '.payment-method', function() {
                const idx = Number($(this).data('idx'));
                if (!paymentRows[idx]) return;
                paymentRows[idx].cara_bayar = $(this).val();
                renderPaymentRows();
                renderPaymentStatus();
            });

            $(document).on('input', '.payment-bank', function() {
                const idx = Number($(this).data('idx'));
                if (paymentRows[idx]) paymentRows[idx].bank_nama = $(this).val();
            });

            $(document).on('input', '.payment-rek', function() {
                const idx = Number($(this).data('idx'));
                if (paymentRows[idx]) paymentRows[idx].rekening_no = $(this).val();
            });

            $(document).on('click', '.btn-remove-payment', function() {
                const idx = Number($(this).data('idx'));
                paymentRows.splice(idx, 1);
                if (!paymentRows.length) {
                    paymentRows = [buildPaymentRow()];
                }
                renderPaymentRows();
                renderPaymentStatus();
            });

            $(document).on('click', '.btn-quick-cash', function() {
                $('#payment-cash-received').val($(this).data('amount'));
                applyMoneyMask('#payment-cash-received');
                renderPaymentStatus();
            });

            refreshMemberSummary();
            renderCart();
            applyMoneyMask();
            hydrateEditSale();
            focusSearch();
        });

        function searchItem() {
            const term = ($('#item-search').val() || '').trim();
            if (!term) return;

            $.getJSON('<?= base_url('/jual/search-item') ?>', {
                term
            }, function(res) {
                if (res.tipe !== 'success') {
                    toastr.error(res.data || 'Gagal mencari item');
                    return;
                }

                const rows = res.data || [];
                if (res.auto_pick && rows.length === 1) {
                    pickItem(rows[0].kode_item);
                    return;
                }

                renderSearchResults(rows);
            }).fail(function(xhr) {
                toastr.error(extractErrorMessage(xhr, 'Gagal mencari item'));
            });
        }

        function renderSearchResults(rows) {
            const $box = $('#item-search-result');
            if (!rows.length) {
                $box.html('<div class="p-3 text-center text-muted">Item tidak ditemukan</div>').removeClass('d-none');
                return;
            }

            const html = rows.map((row) => {
                const blocked = getPriceErrorMessage(row.harga_default || 0, row.harga_jual || 0);
                const blockedNote = blocked ? `<div class="small text-danger mt-1">Blok jual: ${escapeHtml(blocked)}</div>` : '';
                return `
                    <div class="search-result-item ${blocked ? 'is-blocked' : ''}" data-kode-item="${escapeHtml(row.kode_item)}" data-blocked="${blocked ? 1 : 0}">
                        <div class="fw-semibold">${escapeHtml(row.kode_item)} - ${escapeHtml(row.nama_item || '-')}</div>
                        <div class="small text-muted">Barcode: ${escapeHtml(row.barcode || '-')} | Stok: ${Number(row.stok || 0).toLocaleString('id-ID')} ${escapeHtml(row.sat_dasar || '')}</div>
                        <div class="small text-muted">Harga: Rp ${formatMoneyValue(row.harga_jual || 0)}</div>
                        ${blockedNote}
                    </div>
                `;
            }).join('');

            $box.html(html).removeClass('d-none');
        }

        function pickItem(kodeItem) {
            $('#item-search-result').addClass('d-none').empty();
            $('#item-search').val('');

            $.getJSON(`<?= base_url('/jual/item-detail') ?>/${encodeURIComponent(kodeItem)}`, function(res) {
                if (res.tipe !== 'success') {
                    toastr.error(res.data || 'Gagal mengambil detail item');
                    return;
                }
                addItemToCart(res.data);
            }).fail(function(xhr) {
                toastr.error(extractErrorMessage(xhr, 'Gagal mengambil detail item'));
            });
        }

        function addItemToCart(item) {
            const options = (item.satuan_options || []).map((option) => ({
                sat_id: option.sat_id,
                qty_konversi: Number(option.qty_konversi || 1),
                harga_pokok: Number(option.harga_pokok || 0),
                harga_jual: Number(option.harga_jual || 0),
                stok_maksimal: Number(option.stok_maksimal || 0),
                price_error: option.price_error || ''
            }));

            const defaultOption = options.find((option) => option.sat_id === item.default_sat_id) || options[0];
            if (!defaultOption) {
                toastr.error('Item belum memiliki satuan aktif');
                return;
            }

            if (defaultOption.price_error) {
                toastr.error(`Item ${item.kode_item} tidak bisa dijual: ${defaultOption.price_error}`);
                return;
            }

            const existingIdx = cartRows.findIndex((row) => row.kode_item === item.kode_item && row.sat_id === defaultOption.sat_id);
            if (existingIdx >= 0) {
                const nextQty = Number(cartRows[existingIdx].qty_jual || 0) + 1;
                cartRows[existingIdx].qty_jual = Math.min(nextQty, Number(cartRows[existingIdx].max_qty || 0));
                if (nextQty > Number(cartRows[existingIdx].max_qty || 0)) {
                    toastr.error('Stok tidak mencukupi');
                }
                recalcCartRow(existingIdx);
                renderCart();
                return;
            }

            if (defaultOption.stok_maksimal <= 0) {
                toastr.error('Stok tidak mencukupi');
                return;
            }

            const cartRow = {
                kode_item: item.kode_item,
                barcode: item.barcode || '',
                nama_item: item.nama_item || item.kode_item,
                sat_id: defaultOption.sat_id,
                qty_jual: 1,
                qty_konversi: defaultOption.qty_konversi,
                harga_pokok: defaultOption.harga_pokok,
                price: defaultOption.harga_jual,
                diskon_item: 0,
                max_qty: defaultOption.stok_maksimal,
                satuan_options: options
            };

            cartRows.push(cartRow);
            recalcCartRow(cartRows.length - 1);
            renderCart();
        }

        function changeCartUnit(idx, satId) {
            const row = cartRows[idx];
            if (!row) return;
            const selected = (row.satuan_options || []).find((option) => option.sat_id === satId);
            if (!selected) return;

            if (selected.price_error) {
                toastr.error(`Item ${row.kode_item} tidak bisa dijual: ${selected.price_error}`);
                renderCart();
                return;
            }

            row.sat_id = selected.sat_id;
            row.qty_konversi = selected.qty_konversi;
            row.harga_pokok = selected.harga_pokok;
            row.price = selected.harga_jual;
            row.max_qty = selected.stok_maksimal;
            if (Number(row.qty_jual || 0) > row.max_qty) {
                row.qty_jual = row.max_qty;
                toastr.error('Qty otomatis disesuaikan ke stok maksimal satuan ini');
            }
            recalcCartRow(idx);
            renderCart();
        }

        function recalcCartRow(idx) {
            const row = cartRows[idx];
            if (!row) return;
            row.gross = round2(Number(row.qty_jual || 0) * Number(row.price || 0));
            row.max_diskon = Math.max(row.gross - (Number(row.qty_jual || 0) * Number(row.harga_pokok || 0)), 0);
            if (Number(row.diskon_item || 0) > row.max_diskon) {
                row.diskon_item = row.max_diskon;
            }
            row.netto = round2(row.gross - Number(row.diskon_item || 0));
            row.qty_stock = round4(Number(row.qty_jual || 0) * Number(row.qty_konversi || 1));
        }

        function renderCart() {
            const $body = $('#cart-body');
            $body.empty();

            if (!cartRows.length) {
                $body.html('<div class="cart-empty-state">Keranjang masih kosong</div>');
                recalcSummary();
                return;
            }

            cartRows.forEach((row, idx) => {
                const satOptions = (row.satuan_options || []).map((option) => {
                    const note = option.price_error ? ' | blok jual' : '';
                    return `<option value="${escapeHtml(option.sat_id)}" ${option.sat_id === row.sat_id ? 'selected' : ''}>${escapeHtml(option.sat_id)} | stok ${Number(option.stok_maksimal || 0).toLocaleString('id-ID')}${note}</option>`;
                }).join('');
                const hasMultiUnit = (row.satuan_options || []).length > 1;
                const unitCell = hasMultiUnit ?
                    `<select class="form-select form-select-sm cart-satuan" data-idx="${idx}">${satOptions}</select>` :
                    `<div class="unit-static"><span>${escapeHtml(row.sat_id)}</span><small class="text-muted">stok ${Number(row.max_qty || 0).toLocaleString('id-ID')}</small></div>`;

                $body.append(`
                    <div class="cart-row">
                        <div class="cart-row-main">
                            <div class="cart-item-name">
                                ${idx + 1}. ${escapeHtml(row.nama_item || '-')} <small class="text-muted" >${escapeHtml(row.kode_item)}</small>
                            </div>
                             <div class="cart-item-price">@ Rp ${formatMoneyValue(row.price || 0)}</div>
                        </div>
                           
                        <div class="cart-row-controls">
                            <div class="cart-control-block">
                                <label>Qty</label>
                                <div class="qty-control">
                                    <button type="button" class="btn btn-outline-secondary btn-qty-minus" data-idx="${idx}">-</button>
                                    <input type="number" class="form-control form-control-sm cart-qty" min="0" step="0.01" data-idx="${idx}" value="${row.qty_jual}">
                                    <button type="button" class="btn btn-outline-secondary btn-qty-plus" data-idx="${idx}">+</button>
                                </div>
                            </div>

                            <div class="cart-control-block">
                                <label>Satuan</label>
                                ${unitCell}
                            </div>

                            <div class="cart-control-block">
                                <label>Diskon</label>
                                <input type="text" class="form-control form-control-sm money cart-diskon" data-idx="${idx}" value="${formatMoneyValue(row.diskon_item || 0)}">
                            </div>
                        </div>
                        <div class="cart-row-summary">
                            <div class="cart-control-block">
                                 <label>Netto</label>
                                <div class="cart-netto d-block">Rp ${formatMoneyValue(row.netto || 0)}</div>
                            </div>
                        </div>
                        <button type="button" class="btn btn-outline-danger btn-sm btn-remove-row" data-idx="${idx}"><i class="ti ti-trash fs-5"></i></button>
                    </div>
                `);
            });

            applyMoneyMask('#cart-body');
            recalcSummary();
        }

        function recalcSummary() {
            const gross = round2(cartRows.reduce((sum, row) => sum + Number(row.gross || 0), 0));
            const itemDiscount = round2(cartRows.reduce((sum, row) => sum + Number(row.diskon_item || 0), 0));
            const diskonNota = Number(normalizeMoneyValue($('#diskon-nota').val() || 0));
            let redeemPoints = Number($('#redeem-points').val() || 0);

            if (selectedCustomer.cust_id === 'CUST-GENERAL') {
                redeemPoints = 0;
                $('#redeem-points').val(0);
            }
            if (redeemPoints > Number(selectedCustomer.poin || 0)) {
                redeemPoints = Number(selectedCustomer.poin || 0);
                $('#redeem-points').val(redeemPoints);
            }

            const detailNetto = round2(cartRows.reduce((sum, row) => sum + Number(row.netto || 0), 0));
            const netto = Math.max(round2(detailNetto - diskonNota - redeemPoints), 0);
            const totalQty = round4(cartRows.reduce((sum, row) => sum + Number(row.qty_jual || 0), 0));

            $('#summary-gross').text(`Rp ${formatMoneyValue(gross)}`);
            $('#summary-discount').text(`Rp ${formatMoneyValue(itemDiscount + diskonNota + redeemPoints)}`);
            $('#summary-netto').text(`Rp ${formatMoneyValue(netto)}`);
            $('#summary-item-kind').text(cartRows.length);
            $('#summary-total-qty').text(Number(totalQty).toLocaleString('id-ID'));
            $('#current-customer-badge').text(`${selectedCustomer.cust_id} | ${selectedCustomer.nama || 'Pelanggan Umum'}`);

            return {
                gross,
                itemDiscount,
                diskonNota,
                redeemPoints,
                detailNetto,
                netto,
                totalQty
            };
        }

        function refreshMemberSummary() {
            const poin = Number(selectedCustomer.poin || 0);
            const piutang = Number(selectedCustomer.outstanding_piutang || 0);
            const html = `
                <div class="fw-semibold">${escapeHtml(selectedCustomer.nama || 'Pelanggan Umum')}</div>
                <div class="small text-muted">${escapeHtml(selectedCustomer.cust_id || 'CUST-GENERAL')}${selectedCustomer.kontak ? ' | ' + escapeHtml(selectedCustomer.kontak) : ''}</div>
                <div class="small mt">Saldo Poin: <strong>${Number(poin).toLocaleString('id-ID')}</strong></div>
                <div class="small">Piutang : <strong>Rp ${formatMoneyValue(piutang)}</strong></div>
            `;
            $('#member-summary').html(html);
        }

        function buildPaymentRow() {
            const summary = recalcSummary();
            const totalAllocated = round2(paymentRows.reduce((sum, row) => sum + Number(row.nominal_bayar || 0), 0));
            const remain = Math.max(round2(summary.netto - totalAllocated), 0);
            return {
                cara_bayar: 'TUNAI',
                nominal_bayar: remain,
                bank_nama: '',
                rekening_no: ''
            };
        }

        function openPaymentModal() {
            if (!cartRows.length) {
                toastr.error('Keranjang masih kosong');
                return;
            }

            const summary = recalcSummary();
            if (summary.netto <= 0) {
                toastr.error('Total tagihan tidak valid');
                return;
            }

            if (!paymentRows.length) {
                paymentRows = [{
                    cara_bayar: 'TUNAI',
                    nominal_bayar: summary.netto,
                    bank_nama: '',
                    rekening_no: ''
                }];
            }

            if (!Number(normalizeMoneyValue($('#payment-cash-received').val() || 0))) {
                $('#payment-cash-received').val(summary.netto);
            }
            $('#payment-total-netto').text(`Rp ${formatMoneyValue(summary.netto)}`);
            $('#payment-member-name').text(selectedCustomer.nama || 'Pelanggan Umum');
            $('#payment-member-extra').text(`${selectedCustomer.cust_id || 'CUST-GENERAL'}${selectedCustomer.kontak ? ' | ' + selectedCustomer.kontak : ''}`);
            $('#payment-credit-note').text(
                selectedCustomer.cust_id === 'CUST-GENERAL' ?
                'Pelanggan umum wajib lunas. Pembayaran kurang akan diblokir.' :
                `Member aktif. Saldo poin: ${Number(selectedCustomer.poin || 0).toLocaleString('id-ID')} | Piutang lama: Rp ${formatMoneyValue(selectedCustomer.outstanding_piutang || 0)}`
            );
            if (!$('#payment-jatuh-tempo').val()) {
                $('#payment-jatuh-tempo').val(defaultJatuhTempo);
            }
            renderQuickCashButtons(summary.netto);
            renderPaymentRows();
            renderPaymentStatus();
            applyMoneyMask('#payment-cash-received');
            paymentModal.show();
            focusCashReceived();
        }

        function renderQuickCashButtons(netto) {
            const amounts = buildQuickCashAmounts(netto);
            const html = amounts.map((amount, idx) => `<button type="button" class="btn btn-outline-dark btn-sm btn-quick-cash" data-amount="${amount}" data-hotkey="${quickCashHotkeys[idx] || ''}">${amount === netto ? 'UANG PAS' : 'Rp ' + formatMoneyValue(amount)}</button>`).join('');
            $('#quick-cash-buttons').html(html);
        }

        function buildQuickCashAmounts(netto) {
            const amount = Math.max(Number(netto || 0), 0);
            const ceil5 = Math.ceil(amount / 5000) * 5000;
            const second5 = ceil5 + 5000;
            const fifty = amount < 50000 ? 50000 : Math.ceil(amount / 50000) * 50000;
            const hundred = amount < 100000 ? 100000 : Math.ceil(amount / 100000) * 100000;
            return [amount, ceil5, second5, fifty, hundred].filter((value, idx, arr) => arr.indexOf(value) === idx);
        }

        function renderPaymentRows() {
            const $body = $('#payment-table tbody');
            $body.empty();
            const hasCash = paymentRows.some((row) => row.cara_bayar === 'TUNAI');

            paymentRows.forEach((row, idx) => {
                const isNonCash = row.cara_bayar === 'TRANSFER' || row.cara_bayar === 'QRIS';
                $body.append(`
                    <tr>
                        <td>
                            <select class="form-select form-select-sm payment-method" data-idx="${idx}">
                                <option value="TUNAI" ${row.cara_bayar === 'TUNAI' ? 'selected' : ''}>TUNAI</option>
                                <option value="TRANSFER" ${row.cara_bayar === 'TRANSFER' ? 'selected' : ''}>TRANSFER</option>
                                <option value="QRIS" ${row.cara_bayar === 'QRIS' ? 'selected' : ''}>QRIS</option>
                            </select>
                        </td>
                        <td><input type="text" class="form-control form-control-sm money payment-amount" data-idx="${idx}" value="${formatMoneyValue(row.nominal_bayar || 0)}"></td>
                        <td><input type="text" class="form-control form-control-sm payment-bank" data-idx="${idx}" value="${escapeHtml(row.bank_nama || '')}" ${isNonCash ? '' : 'disabled'}></td>
                        <td><input type="text" class="form-control form-control-sm payment-rek" data-idx="${idx}" value="${escapeHtml(row.rekening_no || '')}" ${row.cara_bayar === 'TRANSFER' ? '' : 'disabled'}></td>
                        <td class="text-center"><button type="button" class="btn btn-outline-danger btn-sm btn-remove-payment" data-idx="${idx}"><i class="ti ti-trash"></i></button></td>
                    </tr>
                `);
            });

            $('#payment-cash-received').prop('disabled', !hasCash);
            $('#quick-cash-buttons').find('button').prop('disabled', !hasCash);
            if (!hasCash) {
                $('#payment-cash-received').val(0);
            } else {
                syncCashReceivedToTunaiAllocation();
            }
            renderQuickCashButtons(getTunaiDueAmount());

            applyMoneyMask('#payment-table');
        }

        function syncTunaiAllocationFromCashReceived() {
            const tunaiIndexes = paymentRows
                .map((row, idx) => row.cara_bayar === 'TUNAI' ? idx : -1)
                .filter((idx) => idx >= 0);

            if (tunaiIndexes.length !== 1) {
                return;
            }

            const idx = tunaiIndexes[0];
            const summary = recalcSummary();
            const nonCashTotal = round2(paymentRows.reduce((sum, row, rowIdx) => {
                return rowIdx === idx ? sum : sum + Number(row.nominal_bayar || 0);
            }, 0));
            const cashReceived = Number(normalizeMoneyValue($('#payment-cash-received').val() || 0));
            const targetTunai = Math.max(Math.min(round2(summary.netto - nonCashTotal), cashReceived), 0);

            if (Number(paymentRows[idx].nominal_bayar || 0) !== targetTunai) {
                paymentRows[idx].nominal_bayar = targetTunai;
                renderPaymentRows();
            }
        }

        function renderPaymentStatus() {
            const summary = recalcSummary();
            const totalAllocated = round2(paymentRows.reduce((sum, row) => sum + Number(row.nominal_bayar || 0), 0));
            const tunaiAllocated = round2(paymentRows.filter((row) => row.cara_bayar === 'TUNAI').reduce((sum, row) => sum + Number(row.nominal_bayar || 0), 0));
            const cashReceived = Number(normalizeMoneyValue($('#payment-cash-received').val() || 0));
            const cashChange = Math.max(round2(cashReceived - tunaiAllocated), 0);
            const remain = Math.max(round2(summary.netto - totalAllocated), 0);
            const isGeneral = selectedCustomer.cust_id === 'CUST-GENERAL';
            const canSave = !(isGeneral && remain > 0.0001) && !(cashReceived > 0 && cashReceived + 0.0001 < tunaiAllocated) && totalAllocated <= summary.netto + 0.0001;
            const kurangTunai = Math.max(round2(tunaiAllocated - cashReceived), 0);

            let panelClass = 'alert alert-success mb-0';
            let text = `Total alokasi bayar Rp ${formatMoneyValue(totalAllocated)}. `;
            if (remain > 0.0001) {
                if (isGeneral) {
                    panelClass = 'alert alert-danger mb-0';
                    text += `Uang kurang Rp ${formatMoneyValue(remain)}. Non-member wajib lunas.`;
                } else {
                    panelClass = 'alert alert-warning mb-0';
                    text += `Nominal kredit Rp ${formatMoneyValue(remain)} akan dicatat sebagai piutang.`;
                }
            } else {
                text += `Kembalian tunai Rp ${formatMoneyValue(cashChange)}.`;
            }

            if (cashReceived > 0 && cashReceived + 0.0001 < tunaiAllocated) {
                panelClass = 'alert alert-danger mb-0';
                text = 'Uang tunai diterima lebih kecil dari alokasi pembayaran tunai.';
            }
            if (totalAllocated - summary.netto > 0.0001) {
                panelClass = 'alert alert-danger mb-0';
                text = 'Total alokasi pembayaran tidak boleh melebihi total tagihan.';
            }

            $('#payment-total-netto').text(`Rp ${formatMoneyValue(summary.netto)}`);
            $('#payment-status-panel').html(`<div class="${panelClass}">${text}</div>`);
            $('#btn-save-sale').prop('disabled', !canSave);
            renderQuickCashButtons(getTunaiDueAmount());
            updateChangeDisplay({
                remain,
                cashChange,
                kurangTunai,
                isGeneral
            });
            $('#payment-credit-deadline-wrap').toggle(remain > 0.0001 && !isGeneral);
        }

        function saveSale() {
            const payload = {
                cust_id: selectedCustomer.cust_id || 'CUST-GENERAL',
                diskon_nota: Number(normalizeMoneyValue($('#diskon-nota').val() || 0)),
                redeem_points: Number($('#redeem-points').val() || 0),
                cash_received: Number(normalizeMoneyValue($('#payment-cash-received').val() || 0)),
                jatuh_tempo: $('#payment-jatuh-tempo').val() || '',
                detail_json: JSON.stringify(cartRows.map((row) => ({
                    kode_item: row.kode_item,
                    sat_id: row.sat_id,
                    qty_jual: row.qty_jual,
                    diskon_item: row.diskon_item
                }))),
                payment_json: JSON.stringify(paymentRows)
            };

            if (isEditMode && editSale?.jual_id) {
                payload._method = 'PATCH';
                payload.jual_id = editSale.jual_id;
            }

            $.ajax({
                type: 'POST',
                url: saleSaveUrl,
                dataType: 'json',
                data: payload,
                success: function(res) {
                    if (res.tipe !== 'success') {
                        toastr.error(res.data || (isEditMode ? 'Gagal mengupdate transaksi penjualan' : 'Gagal menyimpan transaksi penjualan'));
                        return;
                    }

                    toastr.success(res.data || (isEditMode ? 'Transaksi penjualan berhasil diupdate' : 'Transaksi penjualan berhasil disimpan'));
                    paymentModal.hide();
                    if (res.receipt_url) {
                        window.open(res.receipt_url, '_blank', 'noopener');
                    }
                    if (isEditMode && res.redirect_url) {
                        window.location.href = res.redirect_url;
                        return;
                    }
                    resetCartState(true);
                    localStorage.removeItem(holdStorageKey);
                },
                error: function(xhr) {
                    toastr.error(extractErrorMessage(xhr, isEditMode ? 'Gagal mengupdate transaksi penjualan' : 'Gagal menyimpan transaksi penjualan'));
                }
            });
        }

        function resetCartWithConfirm() {
            if (!cartRows.length) {
                toastr.error('Keranjang sudah kosong');
                return;
            }

            Swal.fire({
                title: 'Reset seluruh isi keranjang?',
                text: 'Aksi ini akan menghapus semua item yang sedang diantrikan.',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonText: 'Ya, reset',
                cancelButtonText: 'Batal'
            }).then((result) => {
                if (!result.isConfirmed) return;

                $.ajax({
                    type: 'POST',
                    url: '<?= base_url('/jual/void-cart') ?>',
                    dataType: 'json',
                    data: {
                        cart_snapshot: JSON.stringify(buildSnapshot())
                    },
                    complete: function() {
                        resetCartState(true);
                        toastr.success('Keranjang berhasil dikosongkan');
                    }
                });
            });
        }

        function holdCart(exitAfter = false) {
            if (isEditMode) {
                toastr.error('Hold cart tidak tersedia saat edit transaksi');
                return;
            }
            if (!cartRows.length) {
                toastr.error('Tidak ada transaksi untuk di-hold');
                return;
            }

            localStorage.setItem(holdStorageKey, JSON.stringify(buildSnapshot()));
            resetCartState(true);
            toastr.success('Keranjang disimpan sementara');

            if (exitAfter) {
                window.location.href = exitUrl;
            }
        }

        function recallCart() {
            if (isEditMode) {
                toastr.error('Recall cart tidak tersedia saat edit transaksi');
                return;
            }
            const raw = localStorage.getItem(holdStorageKey);
            if (!raw) {
                toastr.error('Belum ada hold cart yang tersimpan');
                return;
            }

            try {
                const snapshot = JSON.parse(raw);
                cartRows = snapshot.cartRows || [];
                selectedCustomer = snapshot.customer || initialData.customer_general;
                $('#diskon-nota').val(snapshot.diskonNota || 0);
                $('#redeem-points').val(snapshot.redeemPoints || 0);
                setCustomerSelection(selectedCustomer);
                renderCart();
                toastr.success('Hold cart berhasil dimuat');
            } catch (error) {
                toastr.error('Data hold cart rusak');
            }
        }

        function hydrateEditSale() {
            if (!isEditMode || !editSale) {
                return;
            }

            cartRows = Array.isArray(editSale.cart_rows) ? editSale.cart_rows : [];
            paymentRows = Array.isArray(editSale.payment_rows) && editSale.payment_rows.length ? editSale.payment_rows : [buildPaymentRow()];
            selectedCustomer = editSale.customer || initialData.customer_general;

            $('#diskon-nota').val(editSale.diskon_nota || 0);
            $('#redeem-points').val(editSale.redeem_points || 0);
            $('#payment-cash-received').val(editSale.cash_received || 0);
            $('#payment-jatuh-tempo').val(editSale.jatuh_tempo || defaultJatuhTempo);
            setCustomerSelection(selectedCustomer);
            renderCart();
            applyMoneyMask();
        }

        function tryExitPos() {
            if (!hasPendingTransaction()) {
                window.location.href = exitUrl;
                return;
            }

            Swal.fire({
                title: 'Transaksi belum selesai',
                text: isEditMode ? 'Perubahan edit transaksi belum disimpan. Keluar sekarang akan membuang perubahan pada layar ini.' : 'POS tidak bisa ditinggalkan selama keranjang masih berisi. Simpan dulu ke HOLD CART jika ingin keluar.',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonText: isEditMode ? 'Keluar' : 'Hold Cart & Keluar',
                cancelButtonText: 'Tetap di POS'
            }).then((result) => {
                if (result.isConfirmed) {
                    if (isEditMode) {
                        window.location.href = exitUrl;
                        return;
                    }
                    holdCart(true);
                }
            });
        }

        function hasPendingTransaction() {
            return cartRows.length > 0;
        }

        function buildSnapshot() {
            return {
                customer: selectedCustomer,
                cartRows,
                diskonNota: $('#diskon-nota').val() || 0,
                redeemPoints: $('#redeem-points').val() || 0
            };
        }

        function resetCartState(resetCustomer = false) {
            cartRows = [];
            paymentRows = [];
            $('#diskon-nota').val(0);
            $('#redeem-points').val(0);
            $('#payment-cash-received').val(0);
            $('#payment-jatuh-tempo').val(defaultJatuhTempo);
            $('#item-search-result').addClass('d-none').empty();
            $('#item-search').val('');

            if (resetCustomer) {
                selectedCustomer = initialData.customer_general || {
                    cust_id: 'CUST-GENERAL',
                    nama: 'Pelanggan Umum',
                    kontak: '',
                    poin: 0,
                    outstanding_piutang: 0
                };
                setCustomerSelection(selectedCustomer);
            } else {
                refreshMemberSummary();
            }

            renderCart();
            applyMoneyMask('#diskon-nota');
            applyMoneyMask('#payment-cash-received');
            focusSearch();
        }

        function setCustomerSelection(customer) {
            const option = new Option(`${customer.cust_id} - ${customer.nama || 'Pelanggan Umum'}`, customer.cust_id, true, true);
            $('#customer-select').append(option).trigger('change');
            refreshMemberSummary();
        }

        function registerQuickMember() {
            $.ajax({
                type: 'POST',
                url: '<?= base_url('/jual/register-member') ?>',
                dataType: 'json',
                data: $('#form-quick-member').serialize(),
                success: function(res) {
                    if (res.tipe !== 'success') {
                        toastr.error(res.data || 'Gagal mendaftarkan member baru');
                        return;
                    }

                    selectedCustomer = res.data;
                    setCustomerSelection(selectedCustomer);
                    quickMemberModal.hide();
                    toastr.success('Member baru berhasil didaftarkan dan langsung dipilih');
                },
                error: function(xhr) {
                    toastr.error(extractErrorMessage(xhr, 'Gagal mendaftarkan member baru'));
                }
            });
        }

        function getPriceErrorMessage(hargaPokok, hargaJual) {
            const hpp = Number(hargaPokok || 0);
            const hjual = Number(hargaJual || 0);
            if (hpp <= 0) return 'harga pokok masih 0';
            if (hjual <= 0) return 'harga jual masih 0';
            if (hjual < hpp) return 'harga jual lebih kecil dari harga pokok';
            return '';
        }

        function adjustQty(idx, delta) {
            const row = cartRows[idx];
            if (!row) return;
            let nextQty = round4(Number(row.qty_jual || 0) + delta);
            if (nextQty > Number(row.max_qty || 0)) {
                nextQty = Number(row.max_qty || 0);
                toastr.error('Stok tidak mencukupi');
            }
            if (nextQty <= 0) {
                nextQty = 0;
            }
            row.qty_jual = nextQty;
            recalcCartRow(idx);
            renderCart();
        }

        function syncCashReceivedToTunaiAllocation() {
            const tunaiAllocated = round2(paymentRows.filter((row) => row.cara_bayar === 'TUNAI').reduce((sum, row) => sum + Number(row.nominal_bayar || 0), 0));
            const current = Number(normalizeMoneyValue($('#payment-cash-received').val() || 0));
            if (current < tunaiAllocated || current === 0) {
                $('#payment-cash-received').val(tunaiAllocated);
                applyMoneyMask('#payment-cash-received');
            }
        }

        function getTunaiDueAmount() {
            const summary = recalcSummary();
            const nonCashTotal = round2(paymentRows
                .filter((row) => row.cara_bayar !== 'TUNAI')
                .reduce((sum, row) => sum + Number(row.nominal_bayar || 0), 0));

            return Math.max(round2(summary.netto - nonCashTotal), 0);
        }

        function updateChangeDisplay(state) {
            const $box = $('#payment-change-display');
            const $amount = $('#payment-change-amount');
            const $caption = $('#payment-change-caption');
            $box.removeClass('is-warning is-danger');

            if (state.kurangTunai > 0.0001) {
                $box.addClass('is-danger');
                $amount.text(`- Rp ${formatMoneyValue(state.kurangTunai)}`);
                $caption.text('Uang tunai diterima masih kurang dari alokasi pembayaran tunai.');
                return;
            }

            if (state.remain > 0.0001) {
                $box.addClass('is-warning');
                $amount.text(`Rp ${formatMoneyValue(state.remain)}`);
                $caption.text(state.isGeneral ? 'Kekurangan pembayaran. Customer umum wajib lunas.' : 'Akan dicatat sebagai nominal kredit / piutang.');
                return;
            }

            $amount.text(`Rp ${formatMoneyValue(state.cashChange)}`);
            $caption.text(state.cashChange > 0 ? 'Kembalian yang harus diberikan ke customer.' : 'Uang pas.');
        }

        function focusSearch() {
            setTimeout(function() {
                $('#item-search').trigger('focus').select();
            }, 60);
        }

        function focusCashReceived() {
            setTimeout(function() {
                $('#payment-cash-received').trigger('focus').select();
            }, 80);
        }

        function handleGlobalShortcut(e) {
            if (e.key === 'F2') {
                e.preventDefault();
                holdCart();
                return;
            }

            if (e.key === 'F5') {
                e.preventDefault();
                resetCartWithConfirm();
                return;
            }

            if (e.key === 'F12') {
                e.preventDefault();
                if ($('#modal-payment').hasClass('show')) {
                    saveSale();
                } else {
                    openPaymentModal();
                }
                return;
            }

            if (e.key === 'Escape') {
                if ($('#modal-payment').hasClass('show')) {
                    e.preventDefault();
                    paymentModal.hide();
                    focusSearch();
                    return;
                }

                if (!$('#item-search-result').hasClass('d-none')) {
                    e.preventDefault();
                    $('#item-search-result').addClass('d-none').empty();
                    focusSearch();
                }
                return;
            }

            if (e.altKey) {
                const idx = quickCashHotkeys.indexOf(e.key);
                if (idx >= 0) {
                    const $btn = $('#quick-cash-buttons .btn-quick-cash').eq(idx);
                    if ($btn.length && !$btn.prop('disabled')) {
                        e.preventDefault();
                        $btn.trigger('click');
                    }
                }
            }
        }

        function round2(value) {
            return Math.round((Number(value || 0) + Number.EPSILON) * 100) / 100;
        }

        function round4(value) {
            return Math.round((Number(value || 0) + Number.EPSILON) * 10000) / 10000;
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
</body>

</html>
