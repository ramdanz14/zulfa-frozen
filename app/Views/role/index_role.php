<?= $this->extend('layouts/base') ?>

<?= $this->section('content') ?>
<div class="body-wrapper">
  <div class="page-header d-print-none" aria-label="Page header">
    <div class="container-xl">
      <div class="row g-2 align-items-center">
        <div class="col">
          <h2 class="page-title">Fluid layout</h2>
          <div class="page-pretitle">Overview</div>
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

<div class="modal fade" id="modal-web" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog">
    <div class="modal-content">
      <div id="loadingOverlay" class="d-flex justify-content-center align-items-center" style="position: absolute; top: 0; left: 0; width: 100%; height: 100%; background-color: rgba(255,255,255,.7); z-index:1051;">
        <i class="fas fa-2x fa-sync fa-spin text-primary"></i>
      </div>
      <div class="modal-header">
        <h5 class="modal-title" id="modal-title">Modal title</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <form id="modal-form">
        <div class="modal-body"></div>
        <div class="modal-footer justify-content-between">
          <button type="submit" class="btn btn-primary" id="btn-aksi">Save changes</button>
          <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
        </div>
      </form>
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
            if (akses_menu?.akses_create === 'Y') {
              showModal('tambah');
            } else {
              toastr.error('Anda tidak memiliki akses untuk ini!');
            }
          }
        }, {
          text: '<i class="ti ti-file-type-xls"></i> Excel',
          extend: 'excelHtml5',
          title: 'Laporan-Role',
          exportOptions: {
            columns: [0, 1, 2],
            orthogonal: 'export'
          }
        }, 'pageLength']
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
      url: '<?= base_url('/jabatan/ajax') ?>',
      type: 'post',
      data: {}
    },
    columns: [{
        data: 'level_id',
        title: 'Level ID'
      },
      {
        data: 'level_name',
        title: 'Nama Jabatan'
      },
      {
        data: 'jml_user',
        title: 'Jumlah User',
        className: 'dt-right',
        render: (d) => new Intl.NumberFormat('id-ID').format(d || 0)
      },
      {
        title: 'Action',
        class: 'dt-center',
        responsivePriority: 1,
        data: null,
        render: function(data) {
          const akses = `<a class='dropdown-item' href='<?= base_url('/jabatan/akses') ?>/${encodeURIComponent(data.level_id)}'><i class='ti ti-shield text-primary'></i> Akses Menu</a>`;
          const editMenu = akses_menu?.akses_update === 'Y' ? `<a class='dropdown-item' onclick='showModal("edit",${JSON.stringify(data)})'><i class='ti ti-pencil text-warning'></i> Edit</a>` : '';
          const deleteMenu = akses_menu?.akses_delete === 'Y' ? `<a class='dropdown-item' onclick='showModal("delete",${JSON.stringify(data)})'><i class='ti ti-trash-x text-danger'></i> Hapus</a>` : '';
          return `<span class="dropdown">
                    <button class="btn dropdown-toggle align-text-top btn-sm" data-bs-boundary="viewport" data-bs-toggle="dropdown">Actions</button>
                    <div class="dropdown-menu dropdown-menu-end">
                      ${akses}
                      ${editMenu}
                      ${deleteMenu}
                    </div>
                  </span>`;
        }
      }
    ]
  });

  table.on('xhr.dt', function(e, settings, json) {
    $(".page-pretitle").text('Total Data : ' + (json?.recordsTotal || 0));
    $(".page-title").text('Daftar Jabatan');
  });

  $('#modal-form').validate({
    rules: {
      level_id: 'required',
      level_name: 'required'
    },
    errorElement: 'span',
    errorPlacement: function(error, element) {
      error.addClass('invalid-feedback');
      element.closest('.form-group').append(error);
    },
    highlight: function(el) {
      $(el).addClass('is-invalid');
    },
    unhighlight: function(el) {
      $(el).removeClass('is-invalid');
    },
    submitHandler: function() {
      saveAjax();
    }
  });

  function showModal(action, data) {
    $("#modal-form > .modal-body").empty();
    const list = ['level_id', 'level_name'];
    list.forEach((item) => {
      $("#modal-form > .modal-body").append(`<div class="form-group mb-1">
        <label for="${item}" class="form-label">${item.toUpperCase()}</label>
        <input type="text" class="form-control" name="${item}" id="${item}" />
      </div>`);
    });
    $("#modal-form > .modal-body").append(`<input type="hidden" id="_method" name="_method">`);
    $("#btn-aksi").removeAttr('class');

    switch (action) {
      case 'tambah':
        $("#_method").val('PUT');
        $("#modal-title").html('Tambah Jabatan');
        $("#btn-aksi").html('Save');
        $("#btn-aksi").addClass('btn btn-success');
        break;
      case 'edit':
        $("#_method").val('PATCH');
        $("#modal-title").html('Edit Jabatan');
        $("#btn-aksi").html('Update');
        $("#btn-aksi").addClass('btn btn-warning');
        $('#level_id').prop('readonly', true);
        break;
      case 'delete':
        $("#_method").val('DELETE');
        $("#modal-title").html('Delete Jabatan');
        $("#btn-aksi").html('Delete');
        $("#btn-aksi").addClass('btn btn-danger');
        $("#modal-web input").attr('readonly', true);
        $("#modal-web select").attr('disabled', true);
        $("#btn-aksi").prop('disabled', false);
        break;
    }
    if (data) {
      for (const k in data) {
        $('#' + k).val(data[k]);
      }
    }
    $("#loadingOverlay").addClass('d-none');
    $("#modal-web").modal('show');
  }

  function saveAjax() {
    const formData = $("#modal-form").serializeArray();
    const method = formData.find(x => x.name === '_method')?.value;
    const url = '<?= base_url('/jabatan') ?>';
    $("#loadingOverlay").removeClass('d-none');
    $.ajax({
      type: 'POST',
      url: url,
      dataType: 'json',
      data: formData,
      success: function(res) {
        $("#loadingOverlay").addClass('d-none');
        $("#modal-web").modal('hide');
        if (res.tipe === "success") {
          toastr.success(res.data || "Berhasil");
        } else {
          toastr.error(res.data || "Gagal");
        }
        table.ajax.reload(null, false);
      },
      error: function(xhr) {
        $("#loadingOverlay").addClass('d-none');
        $("#modal-web").modal('hide');
        alert(xhr.responseText);
      }
    });
  }
</script>
<?= $this->endSection('javascript') ?>