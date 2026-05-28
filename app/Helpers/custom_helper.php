<?php

function GetFromArray(array $arraylist, string $key, string $value, string $keyfilter)
{
    foreach ($arraylist as $object) {
        if ($object[$key] == $keyfilter) {
            $taglineValue = $object[$value];
            return $taglineValue;
        }
    }
}

function GetConst(string $rkey)
{
    $db = \Config\Database::connect();
    $const = $db->query("SELECT * FROM const WHERE rkey='$rkey'")->getRow();
    return $const->nilai;
}

function GetToko(string $col)
{
    $db = \Config\Database::connect();
    $toko_id = session('toko_id');
    $const = $db->query("SELECT * FROM toko WHERE toko_id='$toko_id'")->getRowArray();
    return $const[$col];
}

function GetAkseMenu(string $level_id, string $menu_id)
{
    $db = \Config\Database::connect();
    $akses_menu =  $db->query("SELECT * FROM akses_menu WHERE level_id= :level_id: and menu_id= :menu_id:", ['level_id' => $level_id, 'menu_id' => $menu_id])->getRow();

    return $akses_menu;
}
function GetMenu()
{
    $db = \Config\Database::connect();
    $level_id = session('level_id');
    $menu = $db->query("SELECT b.* FROM akses_menu a LEFT JOIN tb_menu b USING(menu_id) WHERE level_id=:level_id: AND akses_read='Y' ORDER  BY header_menu,urutan;", ['level_id' => $level_id])->getResultArray();
    return $menu;
}

function tracelog(string $action, string $detail)
{
    $db = \Config\Database::connect();
    $username = session()->username;
    $toko_id = session()->toko_id;
    $cek =  $db->query("INSERT INTO tracelog set  toko_id='$toko_id',username='$username', tgl=NOW(), action=UPPER('$action'), detail='$detail';");
    return $cek;
}

function HitungStock(string $toko_id)
{
    $db = \Config\Database::connect();

    $cek = $db->query("INSERT IGNORE  INTO  stmast(toko_id,kode_item) SELECT toko_id,kode_item FROM prodmast_satuan JOIN prodmast_store USING(kode_item,sat_id) WHERE toko_id=:toko_id: AND qty_konversi=1 AND status_item='Y'; ", ['toko_id' => $toko_id]);

    // cari table stmast dari periode $bln sebelumnya

    $prd_lama = date("Ym", strtotime("-1 month"));
    $table_lama = "stmast_$prd_lama$toko_id";
    $cek_table = $db->query("SELECT count(*) hasil from information_schema.tables where table_name='$table_lama'")->getRow();
    if ($cek_table->hasil != "0") {
        $db->query("UPDATE stmast  set begbal=0 where toko_id='$toko_id'");
        $db->query("UPDATE stmast a JOIN $table_lama  b using(toko_id,kode_item) set a.begbal=b.qty where a.toko_id='$toko_id'");
    }
    $cek =  $db->query("UPDATE stmast a  LEFT JOIN (SELECT toko_id,kode_item,SUM(qty_stock) AS jml FROM pembelian_detail LEFT JOIN pembelian USING(toko_id,beli_id) WHERE status_nota='TERIMA' and toko_id='$toko_id' and EXTRACT(YEAR_MONTH FROM tanggal)=EXTRACT(YEAR_MONTH FROM CURDATE()) GROUP BY toko_id,kode_item) b USING(toko_id,kode_item) SET a.beli=IFNULL(b.jml,0) WHERE toko_id='$toko_id';");
    // $cek =  $db->query("UPDATE stmast a  JOIN (SELECT toko_id,kode_item,IFNULL(jml_jual,0)+IFNULL(jml_pesan,0) jml FROM (
    //     SELECT toko_id,kode_item,SUM(qty_stock) AS jml_jual FROM penjualan_detail  LEFT JOIN penjualan USING(trx_id) WHERE  toko_id='$toko_id' and EXTRACT(YEAR_MONTH FROM tanggal)=EXTRACT(YEAR_MONTH FROM CURDATE()) GROUP BY toko_id,kode_item) pj LEFT JOIN (
    //     SELECT toko_id,kode_item,SUM(qty_stock) AS jml_pesan FROM pesanan_detail  LEFT JOIN pesanan USING(pesanan_id) WHERE  toko_id='$toko_id' and EXTRACT(YEAR_MONTH FROM tanggal)=EXTRACT(YEAR_MONTH FROM CURDATE()) AND status_pesanan='pesan' GROUP BY toko_id,kode_item ) ps USING (toko_id,kode_item)) b USING(toko_id,kode_item) SET a.jual=b.jml WHERE toko_id='$toko_id';");
    // $cek =  $db->query("UPDATE stmast a  JOIN (SELECT toko_id,kode_item,SUM(qty_stock) AS jml FROM retur_jual_detail LEFT JOIN retur_jual USING(rj_id) WHERE  toko_id='$toko_id' and EXTRACT(YEAR_MONTH FROM tanggal)=EXTRACT(YEAR_MONTH FROM CURDATE()) GROUP BY toko_id,kode_item) b USING(toko_id,kode_item) SET a.retur_jual=b.jml WHERE toko_id='$toko_id';");
    // $cek =  $db->query("UPDATE stmast a  JOIN (SELECT toko_id,kode_item,SUM(qty_stock) AS jml FROM retur_beli_detail LEFT JOIN retur_beli USING(rb_id) WHERE toko_id='$toko_id' and EXTRACT(YEAR_MONTH FROM tanggal)=EXTRACT(YEAR_MONTH FROM CURDATE()) GROUP BY toko_id,kode_item) b USING(toko_id,kode_item) SET a.retur_beli=b.jml WHERE toko_id='$toko_id';");
    // $cek =  $db->query("UPDATE stmast a  JOIN (SELECT kode_item,SUM(qty_so) AS adj_so FROM adj_so  WHERE  EXTRACT(YEAR_MONTH FROM tanggal)=EXTRACT(YEAR_MONTH FROM CURDATE()) and toko_id='$toko_id' GROUP BY kode_item) b USING(kode_item) 
    // LEFT JOIN (SELECT kode_item,SUM(qty_stock) AS tukar_poin FROM penukaran_poin  WHERE  EXTRACT(YEAR_MONTH FROM tanggal)=EXTRACT(YEAR_MONTH FROM CURDATE()) and toko_id='$toko_id' GROUP BY kode_item)c USING(kode_item)
    // SET a.adj=IFNULL(b.adj_so,0)-IFNULL(c.tukar_poin,0) WHERE toko_id='$toko_id';");
    // $cek =  $db->query("UPDATE stmast a  LEFT JOIN (SELECT kode_item,SUM(qty_stock) AS trf_out FROM mutasi_transfer  WHERE toko_kirim='$toko_id' AND  EXTRACT(YEAR_MONTH FROM tanggal)=EXTRACT(YEAR_MONTH FROM CURDATE()) GROUP BY kode_item) b USING(kode_item) 
    // LEFT JOIN (SELECT kode_item,SUM(qty_stock) AS trf_in FROM mutasi_transfer WHERE  toko_terima='$toko_id' AND EXTRACT(YEAR_MONTH FROM tanggal)=EXTRACT(YEAR_MONTH FROM CURDATE()) GROUP BY kode_item)c USING(kode_item)
    // SET a.trf=IFNULL(c.trf_in,0)-IFNULL(b.trf_out,0) WHERE toko_id='$toko_id';");
    $cek = $db->query("UPDATE stmast SET qty=begbal+beli-retur_beli-jual+retur_jual+adj WHERE toko_id='$toko_id'");
    $cek = $db->query("UPDATE stmast a  JOIN (SELECT * FROM prodmast_satuan JOIN prodmast_store USING(kode_item,sat_id) WHERE toko_id='{$toko_id}' AND qty_konversi=1) b USING(toko_id,kode_item ) SET a.`ACOST`=b.harga_pokok WHERE toko_id='$toko_id'; ");
    $cek = $db->query("UPDATE stmast SET rp_saldo_akh=qty*acost WHERE toko_id='$toko_id'");
    return $cek;
}

function HitungStockBulan($toko_id, $bln)
{
    $db = \Config\Database::connect();

    $cek = $db->query("INSERT IGNORE  INTO  stmast(toko_id,kode_item) select '$toko_id',kode_item from prodmast ");
    // cari table stmast dari periode $bln sebelumnya
    $dt = \DateTime::createFromFormat("Ym", $bln);
    $prd_lama = date("Ym", strtotime("-1 month", strtotime($dt->format("Y-m-d"))));
    $table_lama = "stmast_$prd_lama$toko_id";
    $cek_table = $db->query("SELECT count(*) hasil from information_schema.tables where table_name='$table_lama'")->getRow();
    if ($cek_table->hasil != "0") {

        $db->query("UPDATE stmast  set begbal=0 where toko_id='$toko_id'");
        $db->query("UPDATE stmast a JOIN $table_lama  b using(toko_id,kode_item) set a.begbal=b.qty where a.toko_id='$toko_id'");
    }


    $cek =  $db->query("UPDATE stmast a  JOIN (SELECT toko_id,kode_item,SUM(qty_stock) AS jml FROM pembelian_detail LEFT JOIN pembelian USING(beli_id) WHERE status_nota='TERIMA' and toko_id='$toko_id' and EXTRACT(YEAR_MONTH FROM tanggal)=$bln GROUP BY toko_id,kode_item) b USING(toko_id,kode_item) SET a.beli=b.jml WHERE toko_id='$toko_id';");
    $cek =  $db->query("UPDATE stmast a  JOIN (SELECT toko_id,kode_item,IFNULL(jml_jual,0)+IFNULL(jml_pesan,0) jml FROM (
        SELECT toko_id,kode_item,SUM(qty_stock) AS jml_jual FROM penjualan_detail  LEFT JOIN penjualan USING(trx_id) WHERE  toko_id='$toko_id' and EXTRACT(YEAR_MONTH FROM tanggal)=$bln GROUP BY toko_id,kode_item) pj LEFT JOIN (
        SELECT toko_id,kode_item,SUM(qty_stock) AS jml_pesan FROM pesanan_detail  LEFT JOIN pesanan USING(pesanan_id) WHERE  toko_id='$toko_id' and EXTRACT(YEAR_MONTH FROM tanggal)=$bln AND status_pesanan='pesan' GROUP BY toko_id,kode_item ) ps USING (toko_id,kode_item)) b USING(toko_id,kode_item) SET a.jual=b.jml WHERE toko_id='$toko_id';");
    $cek =  $db->query("UPDATE stmast a  JOIN (SELECT toko_id,kode_item,SUM(qty_stock) AS jml FROM retur_jual_detail LEFT JOIN retur_jual USING(rj_id) WHERE  toko_id='$toko_id' and EXTRACT(YEAR_MONTH FROM tanggal)=$bln GROUP BY toko_id,kode_item) b USING(toko_id,kode_item) SET a.retur_jual=b.jml WHERE toko_id='$toko_id';");
    $cek =  $db->query("UPDATE stmast a  JOIN (SELECT toko_id,kode_item,SUM(qty_stock) AS jml FROM retur_beli_detail LEFT JOIN retur_beli USING(rb_id) WHERE toko_id='$toko_id' and EXTRACT(YEAR_MONTH FROM tanggal)=$bln GROUP BY toko_id,kode_item) b USING(toko_id,kode_item) SET a.retur_beli=b.jml WHERE toko_id='$toko_id';");
    $cek =  $db->query("UPDATE stmast a  JOIN (SELECT kode_item,SUM(qty_so) AS adj_so FROM adj_so  WHERE  EXTRACT(YEAR_MONTH FROM tanggal)=$bln and toko_id='$toko_id' GROUP BY kode_item) b USING(kode_item) 
    LEFT JOIN (SELECT kode_item,SUM(qty_stock) AS tukar_poin FROM penukaran_poin  WHERE  EXTRACT(YEAR_MONTH FROM tanggal)=$bln and toko_id='$toko_id' GROUP BY kode_item)c USING(kode_item)
    SET a.adj=IFNULL(b.adj_so,0)-IFNULL(c.tukar_poin,0) WHERE toko_id='$toko_id';");
    $cek =  $db->query("UPDATE stmast a  LEFT JOIN (SELECT kode_item,SUM(qty_stock) AS trf_out FROM mutasi_transfer  WHERE toko_kirim='$toko_id' AND  EXTRACT(YEAR_MONTH FROM tanggal)=$bln GROUP BY kode_item) b USING(kode_item) 
    LEFT JOIN (SELECT kode_item,SUM(qty_stock) AS trf_in FROM mutasi_transfer WHERE  toko_terima='$toko_id' AND EXTRACT(YEAR_MONTH FROM tanggal)=$bln GROUP BY kode_item)c USING(kode_item)
    SET a.trf=IFNULL(c.trf_in,0)-IFNULL(b.trf_out,0) WHERE toko_id='$toko_id';");
    $cek = $db->query("UPDATE stmast SET qty=begbal+trf+beli-retur_beli-jual+retur_jual+adj WHERE toko_id='$toko_id'");
    $cek = $db->query("UPDATE stmast a  JOIN (SELECT * FROM konversi WHERE qty_konversi=1) b USING(kode_item ) SET a.`ACOST`=b.harga_pokok WHERE toko_id='$toko_id'; ");
    $cek = $db->query("UPDATE stmast SET rp_saldo_akh=qty*acost WHERE toko_id='$toko_id'");

    // $cek = $db->query("INSERT IGNORE  INTO  stmast(kode_item) select kode_item from prodmast ");
    // $cek =  $db->query("UPDATE stmast a LEFT JOIN (SELECT kode_item,SUM(qty_stock) AS jml FROM pembelian_detail LEFT JOIN pembelian USING(beli_id) WHERE status_beli='Terima' and EXTRACT(YEAR_MONTH FROM tanggal)=$bln GROUP BY kode_item) b USING(kode_item) SET a.beli=b.jml;");
    // $cek =  $db->query("UPDATE stmast a LEFT JOIN (SELECT kode_item,SUM(qty_stock) AS jml FROM penjualan_detail  LEFT JOIN penjualan USING(trx_id) WHERE EXTRACT(YEAR_MONTH FROM tanggal)=$bln GROUP BY kode_item) b USING(kode_item) SET a.jual=b.jml;");
    // $cek =  $db->query("UPDATE stmast a LEFT JOIN (SELECT kode_item,SUM(qty_stock) AS jml FROM retur_jual_detail LEFT JOIN retur_jual USING(rj_id) WHERE  EXTRACT(YEAR_MONTH FROM tanggal)=$bln GROUP BY kode_item) b USING(kode_item) SET a.retur_jual=b.jml;");
    // $cek =  $db->query("UPDATE stmast a LEFT JOIN (SELECT kode_item,SUM(qty_stock) AS jml FROM retur_beli_detail LEFT JOIN retur_beli USING(rb_id) WHERE  EXTRACT(YEAR_MONTH FROM tanggal)=$bln GROUP BY kode_item) b USING(kode_item) SET a.retur_beli=b.jml;");
    // $cek =  $db->query("UPDATE stmast a LEFT JOIN (SELECT kode_item,SUM(qty_so) AS jml FROM adj_so  WHERE  EXTRACT(YEAR_MONTH FROM tanggal)=$bln GROUP BY kode_item) b USING(kode_item) SET a.adj=b.jml;");
    // $cek = $db->query("UPDATE stmast SET qty=begbal+beli-retur_beli-jual+retur_jual+adj");
    // $cek = $db->query("UPDATE stmast a LEFT JOIN (SELECT * FROM konversi WHERE qty_konversi=1) b USING(kode_item ) SET a.`ACOST`=b.harga_pokok;");
    // $cek = $db->query("UPDATE stmast SET rp_saldo_akh=qty*acost");
    return $cek;
}

function HitungStockAll()
{
    $db = \Config\Database::connect();
    $cek =  $db->query("UPDATE stmast a LEFT JOIN (SELECT kode_item,SUM(qty_stock) AS jml FROM pembelian_detail LEFT JOIN pembelian USING(beli_id) WHERE status_nota='TERIMA' GROUP BY kode_item) b USING(kode_item) SET a.beli=b.jml;");
    $cek =  $db->query("UPDATE stmast a LEFT JOIN (SELECT kode_item,SUM(qty_stock) AS jml FROM penjualan_detail GROUP BY kode_item) b USING(kode_item) SET a.jual=b.jml;");
    $cek = $db->query("UPDATE stmast SET qty=begbal+beli-retur_beli-jual+retur_jual+adj");
    $cek = $db->query("UPDATE stmast SET rp_saldo_akh=qty*acost");
    return $cek;
}

function HitungHutang($hutang_id)
{
    $db = \Config\Database::connect();
    $cek =  $db->query("UPDATE hutang a LEFT JOIN (SELECT hutang_id,SUM(jml_bayar) total_bayar FROM bayar_hutang GROUP BY hutang_id) b USING (hutang_id) SET a.`total_bayar` = b.total_bayar WHERE hutang_id='$hutang_id';");
    $cek =  $db->query("UPDATE hutang SET sisa_hutang=jml_hutang-total_bayar WHERE hutang_id='$hutang_id';");
    tracelog("UPDATE", "Hitung Hutang dengan ID $hutang_id");
    return $cek;
}




function HitungHutangAll()
{
    $db = \Config\Database::connect();
    $cek =  $db->query("UPDATE hutang a LEFT JOIN (SELECT hutang_id,SUM(jml_bayar) total_bayar FROM bayar_hutang GROUP BY hutang_id) b USING (hutang_id) SET a.`total_bayar` = b.total_bayar ;");
    $cek =  $db->query("UPDATE hutang SET sisa_hutang=jml_hutang-total_bayar;");
    tracelog("UPDATE", "Hitung Hutang All");
    return $cek;
}

function HitungPiutang($piutang_id)
{
    $db = \Config\Database::connect();
    $cek =  $db->query("UPDATE piutang a LEFT JOIN (SELECT piutang_id,SUM(jml_bayar) total_bayar FROM bayar_piutang GROUP BY piutang_id) b USING (piutang_id) SET a.`total_bayar` = b.total_bayar WHERE piutang_id='$piutang_id';");
    $cek =  $db->query("UPDATE piutang SET sisa_piutang=jml_piutang-total_bayar WHERE piutang_id='$piutang_id';");
    tracelog("UPDATE", "Hitung Piutang dengan Id  $piutang_id");
    return $cek;
}





function rupiah($nilai)
{
    return  number_to_currency($nilai, 'Rp.', 'id_ID', 0);
}

function digit_group($nilai)
{
    return  number_format($nilai, 0, ".", ",");
}

function cekMobile()
{

    //  $request= CodeIgniter\HTTP\Request;
    $request = \Config\Services::request();
    $agent = $request->getUserAgent();

    if ($agent->getMobile()) {
        return 'true';
    } else {
        return 'false';
    }
}

function terbilang($angka)
{
    $angka = abs($angka);
    $huruf = array("", "satu", "dua", "tiga", "empat", "lima", "enam", "tujuh", "delapan", "sembilan", "sepuluh", "sebelas");
    $terbilang = "";
    if ($angka < 12) {
        $terbilang = $huruf[intval($angka)];
    } elseif ($angka < 20) {
        $terbilang = terbilang(intval($angka) - 10) . " belas";
    } elseif (intval($angka) < 100) {
        $terbilang = terbilang(intval($angka) / 10) . " puluh " . terbilang(intval($angka) % 10);
    } elseif (intval($angka) < 200) {
        $terbilang = "seratus " . terbilang(intval($angka) - 100);
    } elseif (intval($angka) < 1000) {
        $terbilang = terbilang(intval($angka) / 100) . " ratus " . terbilang(intval($angka) % 100);
    } elseif (intval($angka) < 2000) {
        $terbilang = "seribu " . terbilang(intval($angka) - 1000);
    } elseif (intval($angka) < 1000000) {
        $terbilang = terbilang(intval($angka) / 1000) . " ribu " . terbilang(intval($angka) % 1000);
    } elseif (intval($angka) < 1000000000) {
        $terbilang = terbilang(intval($angka) / 1000000) . " juta " . terbilang(intval($angka) % 1000000);
    } else {
        $terbilang = "Angka terlalu besar";
    }
    return strtoupper($terbilang);
}



function cekClosing()
{
    $db = \Config\Database::connect();
    $toko_id = session()->toko_id;
    $const_closing = $db->query("SELECT * FROM const WHERE rkey='closing-$toko_id'")->getRow();
    $prd = $const_closing->nilai ?? "";
    $datenow = date('Y-m-01');
    if ($prd == "") {
        $db->query("INSERT INTO const set rkey='closing-$toko_id', nilai='$datenow' ");
        return true;
    } else {
        if ($datenow != $prd) {
            return false;
            //echo  view('errors/html/error_closing');
        } else {
            return true;
        }
    }
}

function cek_akses_menu(string $viewtpl, $data = null, string $akses = "akses_read")
{
    $db = \Config\Database::connect();
    $request = \Config\Services::request();
    $menu_id =  $request->getUri()->getSegment(1) == '' ? 'main' : $request->getUri()->getSegment(1);
    $level_id = session()->level_id;
    $akses_menu =  $db->query("SELECT * FROM akses_menu WHERE level_id= :level_id: and menu_id= :menu_id:", ['level_id' => $level_id, 'menu_id' => $menu_id])->getRowArray();

    $data['akses_menu'] =  json_encode($akses_menu);
    $data['isMobile'] = cekMobile();
    $menu = GetMenu();
    $data['menu'] = $menu;
    $data['menuJson'] = json_encode($menu, JSON_UNESCAPED_SLASHES);
    if ($akses_menu) {
        if ($akses_menu[$akses] == 'Y') {

            echo view($viewtpl, $data);
        } else {
            echo view('errors/html/error_401');
        }
    } else {
        echo view('errors/html/error_401');
    }
}
