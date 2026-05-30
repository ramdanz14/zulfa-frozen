Draft aplikasi Zulfa frozen

- Master user di buat dengan beberapa kategori level user, Owner, admin gudang, admin toko, kasir dengan penentuan customisasi akses menu yang dapat di atur
- Skema multi toko tapi ada satu toko yang di flag sebagai gudang yang memiliki menu tambhan penjualan khusus ke toko lain cabang internal dengan perhitungan harga jual ambil margin 2% dari hpp dan toko non gudang memiliki menu PO pengiriman ke gudang
  Master toko, setiap toko memiliki :
  toko_id = kode otomatis setiap toko menjadi acuan identitas toko
  toko_nama = untuk di tampilkan di menu web indikator sedang aktif di toko mana
  toko_alamat = untuk pencetakan ke struk /faktur penjualan
  toko_theme = untuk indikator warna tema aplikasi mempermudha membedakan lokasi toko aktif
  toko_phone = untuk pencetakan ke struk /faktur penjualan
  flag_gudang = untuk penanda merupakan toko gudang atau bukan yang bisa melayani PO dan kiriman ke toko lain

Master User
karyawan_id = aut generated oleh sistem
username = di gunakan untuk login usahakan satu kata tanpa spasi
fullname = nama lengkap
password = di enkripsi agar tidak di ketahui orang lain
email =
phone
level_id = jabatan /role user yang akan menentukan akses menu apa saja
active = status aktif atau tidak karyawan tersebut
avatar
alamat
absensi = penanda apakah user masuk ke list yang di cek absen atau tidak
toko_id = toko penempatan yang akan menentukan lokasi toko aktif di aplikasi
updid
updtime

Role/Jabatan
level_id level_name

---

admin Administrasi  
ceo Pemilik  
kasir Kasir  
root IT Staff

Setiap role/jabatan bisa di customisasi mengenai menu mana yang boleh tampil, menu mana yang boleh tambah,edit atau hapus data

Semua menu transaksi ada jagaan tanggal closing artinya untuk transaksi bulan lalu di kunci tidak bisa di edit di hapus karena perhitungan stok ada perhitungan saldo awal bulan sehingga data stok bisa konsisten dan mudah di audit

Menu Produk :
berikut sub menu :

- Kategori : untuk mengatur kategori produk untuk pengelompokan produk untuk analisa report ke depannya
- Satuan : menu untuk mengatur satuan yang tersedia karena satu produk bisa memiliki beberapa satuan
- Data Barang : menu produk dan satuan memiliki kode_item satuan id dan nilai satuan yang sama untuk semua toko contoh produk TELUR miliki kode BR0001 satuan GR nilai =1 dan satuan KG nilai =100 ini berlaku sama di semua store karena untuk menjaga konsistensi data saat ada pengiriman data antar store sehingga tidak ada kekeliruan data jika antara toko A dan B memiliki kode yang berbeda untuk item yang sama
  - menu produk per toko memiliki setting harga yang berlaku hanya untuk toko tersebut jadi setiap toko harga pokok dan harga jual atau status item aktif nya bisa berbeda, ada kolom target_psn_margin yang di hitung dari persentasi selisih margin harga_jual-harga_pokok yang nanti nya di jadikan acuan untuk kenaikan harga otomatis
  - di setiap produk di tambahkan informasi transaksi terakhir seperti tanggal pembelian terakhir, tanggal penjualan terakhir dan tanggal so terakhir untuk kepentingan analisa report
- Atur Harga : menu khusus setting harga yang menampilkan histori harga pokok pembelian dari supplier dan bisa setting koreksi harga_pokok dan harga_jual, jika harga_pokok di rubah maka harga_jual di hitung otomatis dari target_psn_margin sedangkan jika harga jual yang di rubah maka target_psn_margin yang di rubah sesuai harga_jual baru

Menu Pembelian & Hutang

- Data supplier : menu untuk pengaturan supplier yang mengirim barang ke toko dan jadi acuan pencatatan hutang per supplier ke depannya, menu ini berlaku global jadi semua toko memiliki master supplier yang sama,, tapi pencatatan hutang per toko per supplier
- Transaksi Pembelian : untuk input pembelian ke supplier dengan status PO/Draft dan Terima/Barang Masuk, untuk status DRAFT maka belum masuk perhitungan stok, sedangkan jika sudah TERIMA akan masuk perhitungan stok, jika rubah status TERIMA maka wajib input data pembayaran atas faktur bisa pilih TUNAI/TRANSFER atau di campur, jika TRANSFER wajib menginput nama bank dan no rekening jika tidak ada input pembayaran maka akan di catat sebagai hutang ke supplier
  - saat input pembelian wajib memilik supplier, input no invoice dari supplier (jika tidak ada bisa di input tanggal surat jalan suuplier), input kode item dan satuan yang sesuai di menu penginputan ada informasi penambahan stok yang bertambah berapa untuk mempermudah user. contoh jika input gram qty_beli =1 maka hanya bertambah 1 gram tapi pilih satuan KG maka stok akan bertambah 1000, untuk kolom gross bisa di edit untuk HPP per satuannya akan di hitung otomatis mempermudah saat user terima faktur dari supplier yang tidak ada harga satuan hanya total gross.
  - saat status di TERIMA maka stok akan bertambah lalu kolom last_beli akan di update sesuai tanggal faktur. jika ada kenaikan harga dari supplier maka harga_jual akan otomatis naik sesuai dengan target_psn_margin, tapi jika harga turun tidak di rubah hanya di catat history perubahan harga supplier nya
- History Bayar : untuk memonitor history pembayaran ke supplier baik yang tunai ,transfer maupun pembayaran kredit, ada filter tampilkan data supplier tertentu dan filter periode tanggal untuk mempermudah rekonsiliasi data pembayaran ke supplier
- Kelola Hutang : menampilkan semua transaksi pembelian yang statusnya kredit baik BELUM LUNAS/CICIL/SUDAH LUNAS di menu ini user bisa menambahkan bayar ke supplier dan melihat history per faktur untuk status TOtal Pembelian, status HUtang, sisa hutang dan history bayar untuk satu faktur tersebut.
  - ada menu untuk tambah SALDO HUTANG untuk pencatatan hutang lama sebelum menggunakan aplikasi jadi yang di catat hanya hutang tidak ada penambahan stok dari pembelian
- Retur Pembelian : Untuk retur ada status DRAFT dan SELESAI, di tampilkan list faktur hanya yang status kredit saja.. karena retur ke supplier hanya berlaku untuk faktur dengan pembelian kredit dan statusnya belum lunas karena untuk retur dengan STATUS selesai nanti akan menambah pembayaran ke supplier dengan metode POTONGAN RETUR sehingga saat penginputan item akan di tampilkan semua item berdasarkan faktur pembelian nya dan user bisa isi qty_retur nya jika tidak di retur maka cukup di biarkan qty_returnya nol, untuk pengisian qty_retur ini ada jegatan tidak boleh lebih besar dari qty_beli nya dari supplier dan tidak boleh lebih besar dari stok eksisting.. case jika beli item BAKSO 15 pcs, ternyata sudah terjual 5 maka yang bisa di retur maksimal hanya 10. untuk menghindari stok minus setelah di retur
- Laporan Hutang : untuk menampilkan nominal total hutang per supplier, jumlah faktur yang belum lunas dan ada popup detail info faktur yang belum selesai
- Riwayat Pembelian : Menampilkan histori data pembelian ke supplier per item yang ada kenaikan/turun harga saja
- PO Gudang : khusus untuk toko yang non gudang melakukan input permintaan pemenuhan barang ke toko

Menu Penjualan & Piutang

- Data Customer : untuk pencatatan data supplier yang terdaftar ini berlaku global di semua toko, untuk perhitungan poin customer juga berlaku secara global artinya jika customer yang sama belanja di toko A dan toko B maka poinnya akan di akumulasi. ada opsi untuk mencetak kartu member atau extract jadi file gambar agar konsumen yang belanja bisa mengacu pada cust_id atau phone number nya.
- Poin Member : untuk monitoring history penambahan dan pengurangan poin member di sini ada setting nominal penjualan berapa yang dapet poin misalkan setiap 1000 dapat satu poin, poin bertambah dari transaksi penjualan dan berkurang jika user menukar poin sebagai diskon transaksi. dan ada tombol untuk reset poin semua member ini bisa manual di trigger oleh pemilik misalkan setiap setahun sekali
- Transaksi Penjualan : ini adalah menu POS untuk menjalankan transaksi penjualan ke customer, ada opsi untuk pilih customer bisa berdasarkan cust_id, nama atau phone bisa juga input transaksi non member. ada search input box yang bisa cari berdasarkan barcode atau nama produk atau kode_item, ada jegatan tidak bisa input item yang sama dua kali dalam satu struk , ada jegatan qty tidak boleh lebih besar dari stok, saat pilih item nanti per item user bisa memiliki combo satuan jadi bisa pilih satuan.. saat pilih satuan berbeda maka perhitungan hpp, stok, harga jual di sesuaikan sesuai dengan satuan yang di pilih ,, di sisi user hpp tidak di tampilkan,ada inputan diskon per item diskon menjadi pengurang total bukan harga satuan. tapi ada jegatan nilai diskon tidak boleh besar dari total hpp misalkan item hpp 5000 harga_jual 6000 qty_jual =3 maka maksimal diskon adalah 3000. untuk input promosi manual di pos kasir untuk kebutuhan promosi dan loyalty customer, menu pembayaran ada opsi bisa bayar dengan TUNAI, TRANSFER, QRIS dan bisa campur,, jika user memiliki piutang belum lunas di tampilkan info nilai piutang di form pembayaran agar kasir bisa reminder ke konsumen dan saat di simpan ke database ada penanda transaksi menggunakan kredit akan menjadi piutang konsumen dan jatuh tempo secara default 1 bulan tidak perlu ada pilihan input tanggal jatuh tempo seperti di pembelian otomatis di hitung di backend, saat pembayaran disiapkan 4 opsi quick action inputan tunai contoh saat transaksi senilai 13000 maka ada quic action pilihan input tunai 15000, 20000, 50000 dan 100000 quick action ini bersifat dinamis berdasarkan total gross belanja pilihannya opsi terdekat kedua kelipatan 5000 sisanya 50000 dan 100000 contoh untuk transaksi 63rb maka opsi nya 65rb,70rb,100rb jika transaksi 98rb opsinya 100rb saja, jika transaksi 107rb, maka opsi 110rb, 120rb, 150rb dan 200rb sesuai dengan pecahan rupiah tapi tetap user bisa input nominal manual untuk transaksi tunai
  transaksi penjualan hanya boleh di edit di hari H lewat hari di kunci
- Retur Penjualan : ada date input pilih tanggal dulu, lalu pilih struk penjualan nya dari opsi di tampilkan juga customer dan nominal belanja untuk mempermudah kasir mencari lalu nanti di tampilkan detail barang user input qty_retur, harga tidak boleh di edit, jika ada diskon perlu di retur seluruhnya agar nilai yang di kembalikan tetap sesuai ,, untuk retur yang di simpan maka akan menambah stok dan mengurangi uang cash di pembayaran penjualan
- Kelola Piutang : untuk memantau status piutang lunas,cicil,overdue dan pembayaran piutang manual jika tidak di input saat transaksi penjualan
- Transfer Antar Toko : ada dua menu Kirim dan Terima, menu kirim hanya untuk toko Gudang customernya adalah toko yang meminta sesuai dengan PO yang di buat .. tapi tetap bisa di edit tambah atau kurang item nya oleh pengirim sesuai dengan ketersediaan stok, tapi untuk harga_jual tidak mengikuti prodmast tapi di hitung sesuai dengan target margin yang setting di const saat ini 2% dan di toko pengirim ketika di pilih kirim maka sudah di catat sebagai transaksi penjualan dengan cust_id toko tujuan dan sudah mengurangi stok pengirim, untuk toko penerima perlu konfirmasi jika sudah diapprove maka akan di catat sebagai pembelian ke supplier dengan supco gudang mekanisme update hpp last beli dll tetap berlaku dan baru bertambah stok di toko penerima jika sudah approve, jika di reject maka stok di kembalikan ke gudang dan transaksi penjualan nya di batalkan
- Laporan Penjualan : analisa per tanggal, jumlah customer, transaksi, margin
- Penjualan Per Item : analisa penjualan per item
- Laporan Piutang : untuk monitoring piutang per customer yang belum lunas

Menu Stok

- Laporan Stok : untuk cek saldo item dengan histori LPP di tampilkan qty (saldo_akhir), begbal(beginning balance), BELI,RETUR_BELI,JUAL,RETUR_JUAL,ADJ bisa menampilkan history per tanggal jika di klik detail per item
- Stock Opname : untuk melakukan stok opname ada opsi SO SATUAN, BUAT SO ALL, BUAT SO KATEGORI, INPUT SO ALL,HASIL SO ALL, ADJUST SO ALL,HISTORY SO, ada acuan so_aktif_TK01 di table const sebagai bacaan so yang sedang aktif di toko tersebut
  - SO SATUAN : untuk buat adjust so satuan dengan acuan qty_konversi=1 sebagai satuan terkecil yang di so
  - BUAT SO ALL : untuk create table so untuk semua item aktif di toko tersebut
  - BUAT SO KATEGORI : untuk create so item aktif per kategori kategori, bisa beberapa kategori sekaligus
  - INPUT SO ALL : membaca ke table yang di buat berdasarkan tanggal so aktif misalkan toko TK01 tanggal 24 Mei 2026 maka table so_tk01_260524 , di menu ini bisa filter kategori dan item dengan status sudah di input atau belum
  - HASIL SO ALL untuk melihat hasil inputan dan selisih dari SO
  - ADJUST ALL untuk finalisasi proses stok opname jika di rasa sudah selesai di input semua item yang di adjust atas item yang sudah di input dan memiliki selisih
  - HISTORY SO untuk menampilkan data HISTORY SO per kateogri tanggal last so seabgai acuan kapan terakhir di SO
    data hasil SO di simpan di table adjust dengan istype = SO
- Berita Acara Pemusnahan : Untuk input item yang sudah tidak layak jual agar jadi pengurang stok dan bisa di lihat sebagai beban barang rusak yang tidak bisa di jual data inputan di simpan ke table adjust dengan istype BAP qty yang di input sesuai dengan satuan terkecil nya
- Produksi/Konversi : Menu untuk handling case perubahan item misalkan di toko menjual ITEM SEAFOOD BEKU CURAH dengan kode item BR005 tapi ini adalah mixed dari kode_item BR003 BAKSO IKAN 500 gr, CIKUA 500GR jadi saat input produksi plu sumber BAKSO IKAN 500gr di input 1pcs maka menambah plu SEAFOOD BEKU CURAH 500gr , ada menu untuk setting master recipe perubahan setiap itemnya data di bentuk dengan istype KO pengurang plu asal dan penambah plu tujuan

Menu Operasional dan Kas

- Akun Kas : untuk setting akun akun yang masuk kategori pemasukan dan pengeluaran, misalkan pengeluaran Token Listrik, Uang makan, uang bensin dll berlaku untuk semua toko
- Kas Masuk/Keluar : untuk input kas hanya bisa di input di hari H lewat hari di kunci, ada opsi karyawan_id penerima nya siapa jika dalam satu store ada dua orang yang terima taransaksi di catat per store
- Summary Kas : Rekap pengeluaran dan pemasukan dalam satu bulan ada filter opsi date range jika login akses pemilik maka di tampilkan combo untuk lihat laporan semua store dna store lain
- Absensi Karyawan : untuk input status karyawan yang masuk,ijin atau mangkir untuk rekap data absensi
- CLosing bulanan : untuk melakukan closing bulanan untuk perubahan saldo_akhir menjadi begbal bulan baru stok dan pencatatan saldo kas,serta rekap data yang lain
- Laporan Harian: Untuk report harian karyawan store dengan mencatat jumlah uang terima, uang keluar dan sisa uang yang harus di setor, di dalamnya di tambahkan report transksi secara summary diskon, transaksi tunai, transfer maupun qris
- Analisa Margin/Laba : Report analisa margin per tanggal, per store, date-range
- Laporan cash FLow: report muasi keuangan baik tunai dan transfer, qris
- Saldo & Neraca : sebagai saldo usaha perhitungan summary untuk melihat apakah saldo usaha aman atau tidak , SALDO_KAS + TOTAL STOK RUPIAH + TOTAL PIUTANG - TOTAL HUTANG = SALDO

Menu Pengaturan Sistem
