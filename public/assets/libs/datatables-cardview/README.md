# DataTables CardView Plugin Documentation

> **Plugin Location:**  
> - JavaScript: `public/plugins/datatables-cardview/dataTables.cardView.js`  
> - CSS: `public/plugins/datatables-cardview/dataTables.cardView.css`  
> 
> **Dependencies:** jQuery 1.10+, DataTables 1.10+ / 2.x, Bootstrap 4 / AdminLTE 3 (or any CSS framework supporting grid columns).

---

## 1. Overview & Purpose

**DataTables CardView** adalah ekstensi/plugin DataTables kustom yang secara otomatis atau manual mengubah tampilan tabel tabular standar (`<table>`) menjadi grid kartu (*cards*) responsif pada layar sempit (mobile/tablet).

Plugin ini dirancang untuk menyelesaikan masalah umum di mobile browser:
1. Menghilangkan scroll horizontal (*overflow leak*) tabel di layar kecil.
2. Mempertahankan seluruh fitur inti DataTables: server-side processing, client-side searching, pagination, sorting, dan AJAX reload.
3. Memberikan fleksibilitas penuh untuk templating kartu menggunakan elemen standar HTML `<template id="...">` atau fungsi JavaScript callback.
4. Mendukung integrasi tombol bawaan DataTables Buttons (`cardView`, `tableView`, `cardViewToggle`).

---

## 2. Installation & Setup

### A. Load CSS & JS Assets
Letakkan file CSS sebelum `</head>` dan JS setelah jQuery & DataTables:

```html
<!-- DataTables CardView CSS (setelah CSS DataTables & Bootstrap) -->
<link rel="stylesheet" href="/plugins/datatables-cardview/dataTables.cardView.css">

<!-- DataTables Core & Extensions -->
<script src="/plugins/jquery/jquery.min.js"></script>
<script src="/plugins/datatables/jquery.dataTables.min.js"></script>
<script src="/plugins/datatables-bs4/js/dataTables.bootstrap4.min.js"></script>

<!-- DataTables CardView JS (setelah jQuery & DataTables JS) -->
<script src="/plugins/datatables-cardview/dataTables.cardView.js"></script>
```

> **Catatan Proyek CodeIgniter 4 / AdminLTE 3:**  
> Plugin ini sudah di-include secara global di `app/Views/layouts/base_app.php`. Semua view yang meng-*extend* `layouts/base_app` dapat langsung menggunakannya tanpa import ulang.

---

## 3. Quick Start (Built-In Default Template)

Secara otomatis beralih ke kartu pada layar $\le 768\text{px}$ tanpa template kustom:

```javascript
const table = $('#table-data').DataTable({
  cardView: true // atau cardView: { enable: true, breakpoint: 768 }
});
```

Format kartu default akan me-render setiap kolom baris per baris (`Label: Nilai`), dan mendeteksi kolom aksi/tombol di baris paling bawah.

---

## 4. Advanced Usage: Custom HTML `<template>` (Direkomendasikan)

Untuk desain UI yang rapi, *thumb-friendly*, dan spesifik untuk kartu mobile (misal: badge status, teks primer besar, tombol aksi horizontal), gunakan tag `<template>`.

### Step 1. Definisikan `<template>` di dalam HTML View
Letakkan `<template>` di dekat tabel data:

```html
<table id="table-customer" class="table table-bordered table-striped w-100">
  <thead>
    <tr>
      <th>Kode</th>
      <th>Nama Toko</th>
      <th>PIC</th>
      <th>Kontak</th>
      <th>Area</th>
      <th class="text-center">Aksi</th>
    </tr>
  </thead>
  <tbody></tbody>
</table>

<!-- Card Template untuk Mobile Screen -->
<template id="card-customer-template">
  <div class="card shadow-sm border mb-2 h-100">
    <div class="card-body p-3">
      <!-- Header Kartu -->
      <div class="d-flex justify-content-between align-items-start mb-2 pb-2 border-bottom">
        <div class="pr-2" style="flex: 1; min-width: 0;">
          <span class="badge badge-primary text-monospace mb-1" data-dtcv-field="cust_id"></span>
          <span class="badge badge-light border" data-dtcv-field="area_id"></span>
          <h5 class="font-weight-bold text-dark mb-0 text-truncate" data-dtcv-field="toko"></h5>
        </div>
        <!-- Kolom Aksi (index 5) -->
        <div class="flex-shrink-0 ml-2" data-dtcv-field="5"></div>
      </div>

      <!-- Detail Kartu -->
      <div class="text-sm">
        <div class="mb-1">
          <i class="fas fa-user text-muted mr-1"></i>
          <span class="font-weight-bold" data-dtcv-field="nama"></span>
        </div>
        <div>
          <i class="fas fa-phone text-muted mr-1"></i>
          <span data-dtcv-field="kontak"></span>
        </div>
      </div>
    </div>
  </div>
</template>
```

### Step 2. Inisialisasi DataTable dengan Opsi `cardView`
```javascript
$('#table-customer').DataTable({
  cardView: {
    enable: true,
    breakpoint: 768,                              // Beralih ke kartu jika lebar viewport <= 768px
    template: '#card-customer-template',          // Selector ke template
    gridClass: 'col-12 col-sm-6 col-lg-4 mb-2'   // Grid Bootstrap untuk tiap card
  },
  responsive: true,
  serverSide: true,
  ajax: {
    url: '/customer/ajax',
    type: 'POST'
  },
  columns: [
    { data: 'cust_id' },
    { data: 'toko' },
    { data: 'nama' },
    { data: 'kontak' },
    { data: 'area_id' },
    {
      data: null,
      render: function(data, type, row) {
        return `<button class="btn btn-sm btn-warning" onclick='editData("${row.cust_id}")'><i class="fas fa-edit"></i></button>`;
      }
    }
  ]
});
```

---

## 5. Cara Kerja Binding Data ke Template

Plugin menyediakan 2 metode binding dalam template HTML:

### A. Menggunakan Atribut Data Element (Direkomendasikan)
Gunakan atribut `data-dtcv-field` atau `data-dtcv-dataSrc`:
- **Berdasarkan Key Data:** `data-dtcv-field="nama_item"` (mengambil nilai row data properti `nama_item` atau kolom dengan `mData: "nama_item"`).
- **Berdasarkan Index Kolom:** `data-dtcv-field="0"` atau `data-dtcv-field="5"` (mengambil output rendered HTML dari `<td>` index ke-N).
- **Binding Title Kolom:** `data-dtcv-title="0"` atau `data-dtcv-title="nama_item"` (mengisi header/title kolom).

**Catatan Khusus Elemen Input:**
- Jika elemen berupa `<div>` / `<span>` / `<p>`, plugin mengisi `.html(value)`.
- Jika elemen berupa `<input>` / `<select>` / `<textarea>`, plugin mengisi `.val(value)`.
- Jika elemen berupa `<img>`, plugin mengisi atribut `.attr('src', value)`.

### B. Menggunakan Placeholder String
Template mendukung placeholder `{field}` atau `{{field}}`:
```html
<template id="card-item">
  <div class="p-2 border">
    <h5>{{nama_item}}</h5>
    <p>Kode: {{kode_item}}</p>
  </div>
</template>
```

---

## 6. Opsi Konfigurasi Lengkap (Configuration Options)

| Properti | Tipe Data | Default | Keterangan |
| :--- | :--- | :--- | :--- |
| `enable` | `boolean` | `true` | Mengaktifkan/menonaktifkan plugin CardView pada tabel tersebut. |
| `breakpoint` | `number \| null` | `768` | Batas resolusi layar (px). Jika `window.width <= breakpoint`, otomatis beralih ke CardView. Jika diisi `null` atau `false`, hanya beralih lewat tombol / API manual. |
| `template` | `string \| function` | `null` | Selector CSS elemen template (`'#card-template'`) atau fungsi callback JS `function(rowData, rowIdx, rowNode, settings) { return html; }`. Jika `null`, menggunakan tampilan kartu standar. |
| `gridClass` | `string` | `'col-12 mb-2'` | Class kolom pembungkus setiap kartu (contoh Bootstrap: `'col-12 col-md-6 col-xl-4 mb-3'`). |
| `containerClass`| `string` | `'row dt-cardview-container'` | Class container pembungkus kumpulan kartu. |
| `emptyMessage` | `string \| null` | `null` | Pesan ketika data kosong. Default mengambil `sEmptyTable` dari DataTables. |
| `active` | `boolean \| null` | `null` | Memaksa status awal kartu (`true` = selalu kartu, `false` = selalu tabel). |
| `onCardRender` | `function \| null` | `null` | Hook callback `function($card, rowData, rowIdx, rowNode)` setelah kartu di-generate untuk manipulasi DOM dinamis tambahan. |

### Detail & Penggunaan `onCardRender` Callback

Callback `onCardRender` dipanggil tepat setelah kartu selesai di-render dari template dan sebelum kartu dimasukkan ke DOM container. Fungsi ini sangat berguna untuk:
- Mengubah badge warna/status secara dinamis berdasarkan nilai field data.
- Menambahkan class kondisional ke kartu (misal: background merah jika pesanan sudah lewat jatuh tempo / expired).
- Mengikat event listener custom langsung ke elemen kartu.
- Menghitung nilai atau memformat elemen khusus yang tidak dicakup oleh binding otomatis.

#### Parameter Callback:
1. `$card` (*jQuery Object*): Elemen kartu yang baru dibuat (`$(cardElement)`).
2. `rowData` (*Object*): Data mentah baris dari server/DataTables untuk baris tersebut.
3. `rowIdx` (*Number*): Index baris saat ini dalam DataTables.
4. `rowNode` (*HTMLElement*): Elemen baris `<tr>` asli dari DataTables (opsional).

#### Contoh Implementasi `onCardRender`:

```javascript
$('#table-data').DataTable({
  cardView: {
    enable: true,
    breakpoint: 768,
    template: '#card-template',
    gridClass: 'col-12 col-sm-6 col-lg-4 mb-2',
    
    // Hook manipulasi DOM dinamis setelah kartu di-generate:
    onCardRender: function(card, data, rowIdx, rowNode) {
      // 1. Customisasi status badge
      var $status = card.find('.status-badge');
      var val = $status.text().trim();
      if (val === 'Y' || val === '1' || val === 'Aktif') {
        $status.html('<span class="badge badge-success px-2 py-1"><i class="fas fa-check-circle mr-1"></i> Aktif</span>');
      } else if (val === 'N' || val === '0' || val === 'Nonaktif') {
        $status.html('<span class="badge badge-secondary px-2 py-1"><i class="fas fa-times-circle mr-1"></i> Nonaktif</span>');
      }

      // 2. Memberikan styling border khusus jika kondisi tertentu terpenuhi
      if (data.is_priority === '1' || data.is_priority === true) {
        card.addClass('border-primary shadow');
      }

      // 3. Highlight jika ada tagihan jatuh tempo
      if (data.lewat_jatuh_tempo > 0) {
        card.addClass('border-danger');
        card.find('.due-warning').removeClass('d-none');
      }
    }
  }
});
```

---

## 7. JavaScript API & Methods

Anda dapat mengontrol CardView secara dinamis melalui instance DataTables:

```javascript
const dt = $('#myTable').DataTable({ ... });

// 1. Cek apakah CardView sedang aktif
const isCard = dt.cardView.active(); // return boolean

// 2. Aktifkan CardView secara paksa (override breakpoint)
dt.cardView.enable();

// 3. Matikan CardView dan kembali ke tampilan tabel biasa
dt.cardView.disable();

// 4. Toggle antara tabel dan kartu
dt.cardView.toggle();

// 5. Render ulang kartu saat ini (misal setelah manipulasi data DOM manual)
dt.cardView.redraw();
```

### Event Listener
Tabel memicu event jQuery yang bisa didengarkan:
```javascript
$('#myTable').on('cardView.active', function(e, cardViewInstance) {
  console.log('Tampilan kartu aktif!');
});

$('#myTable').on('cardView.inactive', function(e, cardViewInstance) {
  console.log('Tampilan tabel aktif!');
});
```

---

## 8. Integrasi dengan DataTables Buttons Extension

Jika project menggunakan extension DataTables Buttons (`buttons: [...]`), plugin menyediakan tipe button bawaan:

```javascript
$('#table-data').DataTable({
  cardView: {
    enable: true,
    breakpoint: 768,
    template: '#card-template'
  },
  dom: 'Bfrtip',
  buttons: [
    'cardViewToggle', // Tombol toggle 1 tombol (Tampilan Kartu / Tabel)
    'cardView',       // Tombol khusus beralih ke Kartu
    'tableView',      // Tombol khusus beralih ke Tabel
    'excelHtml5',
    'pageLength'
  ]
});
```

---

## 9. Best Practices untuk Pengembang & AI Agent (Antislop Guidelines)

Bagi AI Agent atau Frontend Engineer yang mengimplementasikan CardView:
1. **Target Sentuh Tombol / Interaktif (44px Minimum):**
   - Pastikan tombol pada footer atau header card memiliki min-height $\ge 38\text{px} - 44\text{px}$ dan padding yang cukup agar mudah ditekan di smartphone.
2. **Hindari Text Overflow:**
   - Gunakan utility class `.text-truncate` pada judul kartu atau nama item/customer agar tidak merusak grid.
3. **Z-Index & Dropdown Handling:**
   - File CSS `dataTables.cardView.css` telah dilengkapi rule `:has(.dropdown.show)` dan `overflow: visible !important;`. Pastikan tidak membungkus kartu dengan parent yang memiliki `overflow: hidden`.
4. **Binding Kolom Aksi / Formatter Kolom:**
   - Kolom yang menggunakan fungsi `render` (seperti tombol aksi atau format Rupiah) paling baik diikat menggunakan **indeks kolom numerik** (contoh: `data-dtcv-field="5"`), karena plugin akan otomatis mengambil HTML yang telah selesai di-render oleh DataTables dari node `<td>`.
5. **Penempatan `<template>`:**
   - Selalu letakkan `<template>` di luar elemen `<table>` dan di dalam container view Blade/PHP/HTML agar tidak dihapus oleh parser DOM DataTables.

---

## 10. Contoh Implementasi Lengkap (Single File View)

```html
<!-- Table Wrapper -->
<div class="card">
  <div class="card-body p-2">
    <table id="table-barang" class="table table-bordered table-hover w-100">
      <thead>
        <tr>
          <th>Kode</th>
          <th data-priority="1">Nama Barang</th>
          <th>Kategori</th>
          <th>Harga</th>
          <th data-priority="1" class="text-center">Aksi</th>
        </tr>
      </thead>
      <tbody></tbody>
    </table>

    <!-- Template CardView -->
    <template id="card-barang-template">
      <div class="card shadow-sm border mb-2 h-100">
        <div class="card-body p-3">
          <div class="d-flex justify-content-between align-items-start mb-2">
            <div>
              <span class="badge badge-secondary px-2 py-1 text-monospace" data-dtcv-field="kode"></span>
              <h6 class="font-weight-bold text-dark mt-1 mb-0" data-dtcv-field="nama_barang"></h6>
            </div>
            <div data-dtcv-field="4"></div>
          </div>
          <div class="d-flex justify-content-between align-items-center pt-2 border-top text-sm">
            <span class="text-muted"><i class="fas fa-tag mr-1"></i> <span data-dtcv-field="kategori"></span></span>
            <span class="font-weight-bold text-success" data-dtcv-field="harga"></span>
          </div>
        </div>
      </div>
    </template>
  </div>
</div>

<script>
  $(document).ready(function() {
    $('#table-barang').DataTable({
      cardView: {
        enable: true,
        breakpoint: 768,
        template: '#card-barang-template',
        gridClass: 'col-12 col-sm-6 col-lg-4 mb-2'
      },
      responsive: true,
      serverSide: true,
      ajax: '/barang/ajax',
      columns: [
        { data: 'kode' },
        { data: 'nama_barang' },
        { data: 'kategori' },
        { 
          data: 'harga',
          render: function(data) { return 'Rp ' + Number(data).toLocaleString('id-ID'); }
        },
        {
          data: null,
          className: 'text-center',
          render: function(data, type, row) {
            return `<button class="btn btn-primary btn-sm" onclick="pilih('${row.kode}')">Pilih</button>`;
          }
        }
      ]
    });
  });
</script>
```
