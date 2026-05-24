<?= $this->extend('layouts/base') ?>

<?= $this->section('content') ?>
<div class="body-wrapper">
  <div class="page-header d-print-none" aria-label="Page header">
    <div class="container-xl">
      <div class="row g-2 align-items-center">
        <div class="col">
          <h2 class="page-title">Pengaturan Akses</h2>
          <div class="page-pretitle">Level: <?= esc($level_id) ?></div>
        </div>
        <div class="col-auto ms-auto d-print-none">
          <a href="<?= base_url('/jabatan') ?>" class="btn btn-primary "><i class="ti ti-arrow-left"></i> Kembali</a>
        </div>
      </div>
    </div>
  </div>
  <div class="page-body">
    <div class="container-xl">
      <div class="card">
        <div class="card-header">
          <div class="row g-2 align-items-center flex-grow-1">
            <div class="col">
              <h3 class="card-title mb-0">Daftar Menu</h3>
            </div>
            <div class="col-auto ms-auto">
              <div class="input-icon">
                <input type="text" class="form-control form-control-sm" placeholder="Cari menu..." id="akses-menu-search" aria-label="Cari menu" />
              </div>
            </div>
          </div>
        </div>
        <div class="card-body p-2">
          <div class="table-responsive" id="akses-menu-list">
            <table class="table table-sm table-striped table-hover align-middle">
              <thead>
                <tr>
                  <th>Menu</th>
                  <th class="text-center">Read</th>
                  <th class="text-center">Create</th>
                  <th class="text-center">Update</th>
                  <th class="text-center">Delete</th>
                </tr>
              </thead>
              <tbody class="list">
                <?php foreach ($akses as $row): ?>
                  <tr>
                    <td>
                      <span class="menu-name"><?= esc($row->menu_name) ?></span>
                      <span class="text-secondary menu-id ms-1">(<?= esc($row->menu_id) ?>)</span>
                    </td>
                    <?php foreach (['read', 'create', 'update', 'delete'] as $tipe):
                      $field = 'akses_' . $tipe;
                      $checked = ($row->$field ?? 'N') === 'Y' ? 'checked' : '';
                    ?>
                      <td class="text-center">
                        <label class="form-check form-switch m-0">
                          <input class="form-check-input js-akses-switch" type="checkbox" data-menu-id="<?= esc($row->menu_id) ?>" data-tipe="<?= $tipe ?>" <?= $checked ?> />
                        </label>
                      </td>
                    <?php endforeach; ?>
                  </tr>
                <?php endforeach; ?>
              </tbody>
            </table>
          </div>
        </div>
      </div>
    </div>
  </div>
</div>
<?= $this->endSection('content') ?>

<?= $this->section('javascript') ?>
<script src="https://cdn.jsdelivr.net/npm/list.js@2.3.1/dist/list.min.js" crossorigin="anonymous"></script>

<script>
  $(function() {
    const listContainer = document.getElementById('akses-menu-list');
    const aksesMenuList = (listContainer && typeof List === 'function') ? new List(listContainer, {
      listClass: 'list',
      valueNames: ['menu-name', 'menu-id']
    }) : null;

    $('#akses-menu-search').on('input', function() {
      if (aksesMenuList) {
        aksesMenuList.search(this.value);
      }
    });

    if (typeof toastr !== 'undefined') {
      toastr.options = Object.assign({}, toastr.options || {}, {
        closeButton: true,
        newestOnTop: true,
        progressBar: true,
        timeOut: 3000,
        positionClass: 'toast-top-right'
      });
    }

    $(document).on('change', '.js-akses-switch', function() {
      const $el = $(this);
      const menuId = $el.data('menu-id');
      const tipe = $el.data('tipe');
      const isChecked = $el.is(':checked');
      const nilai = isChecked ? 'Y' : 'N';
      const previousState = !isChecked;

      $.ajax({
        type: 'POST',
        url: '<?= base_url('/jabatan/akses') ?>',
        dataType: 'json',
        data: {
          level_id: '<?= esc($level_id) ?>',
          menu_id: menuId,
          tipe,
          nilai
        },
        success: function(res) {
          showToastr(res.tipe || 'success', res.data || 'Berhasil rubah akses.');
        },
        error: function(xhr) {
          $el.prop('checked', previousState);
          showToastr('error', extractErrorMessage(xhr, 'Gagal rubah akses.'));
        }
      });
    });



  });
</script>
<?= $this->endSection('javascript') ?>