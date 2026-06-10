<?= $this->extend('layouts/base') ?>

<?= $this->section('content') ?>
<?php
/**
 * @var string $akses_menu
 * @var array $akunOptions
 * @var array $karyawanOptions
 */
?>
<div class="body-wrapper">
    <div class="container-fluid p-0">
        <div class="card bg-warning-subtle shadow-none position-relative overflow-hidden mb-4">
            <div class="card-body px-4 py-3">
                <div class="row align-items-center">
                    <div class="col-9">
                        <h4 class="fw-semibold mb-2">Kas Masuk / Keluar</h4>
                        <p class="mb-0"><span class="page-pretitle">Total</span> | Pencatatan kas kecil operasional harian untuk toko aktif.</p>
                        <small class="text-muted d-block mt-1">Input, edit, dan hapus hanya diizinkan pada Hari H.</small>
                    </div>
                    <div class="col-3">
                        <div class="text-center mb-n5">
                            <img src="<?= base_url(); ?>/assets/images/breadcrumb/ChatBc.png" alt="modernize-img" class="img-fluid mb-n4" />
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="card">
            <div class="card-body p-2">
                <table id="table-data" class="table table-bordered table-hover table-striped table-sm align-middle">
                    <thead></thead>
                    <tbody><tr><td>No data to show</td></tr></tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="modal-kas" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Kas Masuk / Keluar</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="form-kas">
                <div class="modal-body">
                    <input type="hidden" id="kas_id">
                    <div class="mb-2">
                        <label class="form-label">Tanggal / Jam</label>
                        <input type="datetime-local" class="form-control" id="tanggal" required>
                    </div>
                    <div class="mb-2">
                        <label class="form-label">Jenis Transaksi</label>
                        <select class="form-select" id="tipe_mutasi" required>
                            <option value="OPERASIONAL">Operasional</option>
                            <option value="PINDAH_SALDO">Mutasi Saldo</option>
                        </select>
                    </div>
                    <div class="mb-2 operational-field">
                        <label class="form-label">Akun Kas</label>
                        <select class="form-select" id="nama_akun" required>
                            <option value="">Pilih akun kas</option>
                            <?php foreach ($akunOptions as $row) : ?>
                                <option value="<?= esc($row['nama_akun']) ?>"><?= esc($row['jenis_akun']) ?> - <?= esc($row['nama_akun']) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="mb-2 operational-field">
                        <label class="form-label">Saldo</label>
                        <select class="form-select" id="saldo_channel">
                            <option value="CASH">Tunai</option>
                            <option value="NONCASH">Non Tunai</option>
                        </select>
                    </div>
                    <div class="mb-2 transfer-field d-none">
                        <label class="form-label">Arah Mutasi Saldo</label>
                        <select class="form-select" id="arah_saldo">
                            <option value="CASH_TO_NONCASH">Tunai ke Non Tunai</option>
                            <option value="NONCASH_TO_CASH">Non Tunai ke Tunai</option>
                        </select>
                    </div>
                    <div class="mb-2">
                        <label class="form-label">Nominal</label>
                        <input type="text" class="form-control money" id="nominal" required>
                    </div>
                    <div class="mb-2">
                        <label class="form-label">Karyawan Penanggung Jawab</label>
                        <select class="form-select" id="karyawan_id" required>
                            <option value="">Pilih karyawan</option>
                            <?php foreach ($karyawanOptions as $row) : ?>
                                <option value="<?= esc($row['karyawan_id']) ?>"><?= esc($row['karyawan_id']) ?> - <?= esc($row['fullname']) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="mb-2">
                        <label class="form-label">Keterangan</label>
                        <input type="text" class="form-control" id="keterangan" maxlength="150">
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-light" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-primary" id="btn-save">Simpan</button>
                </div>
            </form>
        </div>
    </div>
</div>
<?= $this->endSection('content') ?>

<?= $this->section('javascript') ?>
<script>
    const akses_menu = <?= $akses_menu ?>;
    const kasModal = new bootstrap.Modal(document.getElementById('modal-kas'));
    let modalMode = 'create';

    function getCurrentDateTimeLocal() {
        const now = new Date();
        const tzOffset = now.getTimezoneOffset() * 60000;
        return new Date(now - tzOffset).toISOString().slice(0, 16);
    }

    DataTable.Buttons.defaults.dom.button.className = 'btn btn-primary';
    const table = $('#table-data').DataTable({
        layout: {
            topStart: {
                buttons: [{
                    text: '<i class="ti ti-plus"></i> Tambah',
                    action: function() {
                        if (akses_menu?.akses_create === 'Y') {
                            openModal('create');
                            return;
                        }
                        toastr.error('Anda tidak memiliki akses untuk ini!');
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
            url: '<?= base_url('/kas/ajax') ?>',
            type: 'post'
        },
        columns: [{
                data: 'tanggal',
                title: 'Tanggal',
                render: data => data ? new Date(String(data).replace(' ', 'T')).toLocaleString('id-ID') : '-'
            },
            {
                data: 'nama_akun',
                title: 'Akun',
                render: function(data, type, row) {
                    if (row.tipe_mutasi === 'PINDAH_SALDO') {
                        const from = row.saldo_asal === 'NONCASH' ? 'Non Tunai' : 'Tunai';
                        const to = row.saldo_tujuan === 'NONCASH' ? 'Non Tunai' : 'Tunai';
                        return `<span class="badge bg-info-subtle text-info">MUTASI SALDO</span><div class="fw-semibold mt-1">${from} ke ${to}</div>`;
                    }
                    const badge = row.jenis_akun === 'MASUK' ?
                        '<span class="badge bg-success-subtle text-success">MASUK</span>' :
                        '<span class="badge bg-danger-subtle text-danger">KELUAR</span>';
                    const channel = row.saldo_channel === 'NONCASH' ? 'Non Tunai' : 'Tunai';
                    return `${badge}<div class="fw-semibold mt-1">${data || '-'}</div><small class="text-muted">${channel}</small>`;
                }
            },
            {
                data: 'nominal',
                title: 'Nominal',
                className: 'text-end',
                render: data => 'Rp ' + formatMoneyValue(data || 0)
            },
            {
                data: 'karyawan_id',
                title: 'Karyawan',
                render: function(data, type, row) {
                    return `<div class="fw-semibold">${row.fullname || data || '-'}</div><small class="text-muted">${data || '-'}</small>`;
                }
            },
            {
                data: 'keterangan',
                title: 'Keterangan'
            },
            {
                title: 'Action',
                data: null,
                className: 'text-center',
                responsivePriority: 1,
                render: function(row) {
                    const editMenu = akses_menu?.akses_update === 'Y'
                        ? (row.can_mutate
                            ? `<a class="dropdown-item" href="javascript:void(0)" onclick='openModal("edit", ${JSON.stringify(row)})'><i class="ti ti-pencil text-warning"></i> Edit</a>`
                            : `<a class="dropdown-item text-muted" href="javascript:void(0)" onclick="toastr.error('Transaksi kas yang lewat hari sudah dikunci')"><i class="ti ti-lock text-danger"></i> Edit Terkunci</a>`)
                        : '';
                    const deleteMenu = akses_menu?.akses_delete === 'Y'
                        ? (row.can_mutate
                            ? `<a class="dropdown-item" href="javascript:void(0)" onclick='deleteKas(${row.kas_id})'><i class="ti ti-trash text-danger"></i> Hapus</a>`
                            : `<a class="dropdown-item text-muted" href="javascript:void(0)" onclick="toastr.error('Transaksi kas yang lewat hari sudah dikunci')"><i class="ti ti-lock text-danger"></i> Hapus Terkunci</a>`)
                        : '';
                    return `<span class="dropdown">
                        <button class="btn dropdown-toggle align-text-top btn-sm" data-bs-toggle="dropdown">Actions</button>
                        <div class="dropdown-menu dropdown-menu-end">${editMenu}${deleteMenu}</div>
                    </span>`;
                }
            }
        ]
    });

    table.on('xhr.dt', function(e, settings, json) {
        $('.page-pretitle').text(`Total Data : ${json?.recordsTotal || 0}`);
    });

    function openModal(mode, row = null) {
        modalMode = mode;
        $('#form-kas')[0].reset();
        $('#kas_id').val('');
        $('#tanggal').val(getCurrentDateTimeLocal());
        $('#tipe_mutasi').val('OPERASIONAL');
        $('#saldo_channel').val('CASH');
        $('#arah_saldo').val('CASH_TO_NONCASH');
        $('#btn-save').text(mode === 'create' ? 'Simpan' : 'Update');
        $('.modal-title').text(mode === 'create' ? 'Tambah Kas Masuk / Keluar' : 'Edit Kas Masuk / Keluar');

        if (mode === 'edit' && row) {
            $('#kas_id').val(row.kas_id || '');
            $('#tanggal').val(row.tanggal ? String(row.tanggal).replace(' ', 'T').slice(0, 16) : getCurrentDateTimeLocal());
            $('#tipe_mutasi').val(row.tipe_mutasi || 'OPERASIONAL');
            $('#nama_akun').val(row.nama_akun || '');
            $('#saldo_channel').val(row.saldo_channel || 'CASH');
            $('#arah_saldo').val(row.saldo_asal === 'NONCASH' ? 'NONCASH_TO_CASH' : 'CASH_TO_NONCASH');
            $('#nominal').val(formatMoneyValue(row.nominal || 0));
            $('#karyawan_id').val(row.karyawan_id || '');
            $('#keterangan').val(row.keterangan || '');
        }

        toggleMutationMode();
        applyMoneyMask('#modal-kas');
        kasModal.show();
    }

    function toggleMutationMode() {
        const isTransfer = $('#tipe_mutasi').val() === 'PINDAH_SALDO';
        $('.operational-field').toggleClass('d-none', isTransfer);
        $('.transfer-field').toggleClass('d-none', !isTransfer);
        $('#nama_akun').prop('required', !isTransfer);
        if (isTransfer) {
            $('#nama_akun').val('');
        }
    }

    $('#tipe_mutasi').on('change', toggleMutationMode);

    $('#form-kas').on('submit', function(e) {
        e.preventDefault();
        const arahSaldo = $('#arah_saldo').val();
        const saldoAsal = arahSaldo === 'NONCASH_TO_CASH' ? 'NONCASH' : 'CASH';
        const saldoTujuan = arahSaldo === 'NONCASH_TO_CASH' ? 'CASH' : 'NONCASH';
        $.ajax({
            type: modalMode === 'create' ? 'PUT' : 'PATCH',
            url: '<?= base_url('/kas') ?>',
            dataType: 'json',
            data: {
                kas_id: $('#kas_id').val(),
                tanggal: $('#tanggal').val().replace('T', ' '),
                tipe_mutasi: $('#tipe_mutasi').val(),
                nama_akun: $('#nama_akun').val(),
                saldo_channel: $('#saldo_channel').val(),
                saldo_asal: saldoAsal,
                saldo_tujuan: saldoTujuan,
                nominal: $('#nominal').val(),
                karyawan_id: $('#karyawan_id').val(),
                keterangan: $('#keterangan').val()
            },
            success: function(res) {
                if (res.tipe === 'success') {
                    toastr.success(res.data || 'Berhasil');
                    kasModal.hide();
                    table.ajax.reload(null, false);
                    return;
                }
                toastr.error(res.data || 'Gagal');
            },
            error: function(xhr) {
                toastr.error(extractErrorMessage(xhr, 'Gagal menyimpan mutasi kas'));
            }
        });
    });

    function deleteKas(kasId) {
        Swal.fire({
            title: 'Hapus mutasi kas ini?',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonText: 'Ya, hapus',
            cancelButtonText: 'Batal'
        }).then((result) => {
            if (!result.isConfirmed) return;
            $.ajax({
                type: 'DELETE',
                url: '<?= base_url('/kas') ?>',
                dataType: 'json',
                data: { kas_id: kasId },
                success: function(res) {
                    if (res.tipe === 'success') {
                        toastr.success(res.data || 'Berhasil');
                        table.ajax.reload(null, false);
                        return;
                    }
                    toastr.error(res.data || 'Gagal');
                },
                error: function(xhr) {
                    toastr.error(extractErrorMessage(xhr, 'Gagal menghapus mutasi kas'));
                }
            });
        });
    }
</script>
<?= $this->endSection('javascript') ?>
