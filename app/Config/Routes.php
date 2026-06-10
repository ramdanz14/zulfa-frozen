<?php

use CodeIgniter\Router\RouteCollection;



/**
 * @var RouteCollection $routes
 */

$routes->setDefaultNamespace('App\Controllers');
$routes->setDefaultController('Main');
$routes->setDefaultMethod('index');
$routes->setTranslateURIDashes(false);
$routes->set404Override();

$routes->get('/', 'Main::index');
$routes->get('/main', 'Main::index');
$routes->get('/login', 'Login::index');
$routes->get('/logout', 'Login::out');
$routes->match(['post'], '/login', 'Login::check');
$routes->get('/profile', 'Profile::index');
$routes->post('/profile/change-password', 'Profile::changePassword');
$routes->post('/profile/change-avatar', 'Profile::changeAvatar');


$routes->group('user', static function ($route) {
    $route->get('', 'User::index');
    $route->post('ajax', 'User::ajax');
    $route->get('lastid', 'User::lastid');
    $route->get('(:segment)', 'User::Read/$1');
    $route->post('reset', 'User::ResetPassword');
    $route->put('/', 'User::Create');
    $route->patch('/', 'User::Update');
    $route->delete('/', 'User::Delete');
});

$routes->group('absensi', static function ($route) {
    $route->get('', 'Absensi::index');
    $route->get('input', 'Absensi::input');
    $route->get('input/(:segment)', 'Absensi::input/$1');
    $route->get('pay', 'Absensi::pay');
    $route->post('ajax', 'Absensi::ajax');
    $route->post('ajax-payment', 'Absensi::ajaxPayment');
    $route->get('show/(:segment)', 'Absensi::show/$1');
    $route->get('show-payment/(:segment)', 'Absensi::showPayment/$1');
    $route->post('process-payment', 'Absensi::processPayment');
    $route->get('struk/(:segment)/(:segment)', 'Absensi::struk/$1/$2');
    $route->put('', 'Absensi::store');
    $route->delete('', 'Absensi::delete');
});

$routes->group('menu', static function ($route) {
    $route->get('', 'Menu::index');
    $route->post('ajax', 'Menu::ajax');
    $route->put('', 'Menu::store');
    $route->patch('', 'Menu::update');
    $route->delete('', 'Menu::delete');
});


$routes->group('jabatan', static function ($route) {
    $route->get('', 'Role::index');
    $route->post('ajax', 'Role::ajax');
    $route->put('', 'Role::store');
    $route->patch('', 'Role::update');
    $route->delete('', 'Role::delete');
    $route->get('akses/(:segment)', 'Role::indexAkses/$1');
    $route->post('akses', 'Role::updateAkses');
});

$routes->group('listoko', static function ($route) {
    $route->get('', 'Toko::index');
    $route->post('ajax', 'Toko::ajax');
    $route->get('lastid', 'Toko::lastid');
    $route->put('', 'Toko::store');
    $route->patch('', 'Toko::update');
    $route->delete('', 'Toko::delete');
    $route->get('getid', 'Toko::GetId');
});

$routes->group('tokoaktif', static function ($route) {
    $route->get('', 'Tokoaktif::index');
    $route->post('switch', 'Tokoaktif::switch');
});


$routes->group('satuan',  static function ($route) {
    $route->get('', 'Satuan::index');
    $route->post('ajax', 'Satuan::ajax');
    $route->post('', 'Satuan::show');
    $route->put('', 'Satuan::store');
    $route->patch('', 'Satuan::update');
    $route->delete('', 'Satuan::delete');
});


$routes->group('kategori', static function ($route) {
    $route->get('', 'Kategori::index');
    $route->post('ajax', 'Kategori::ajax');
    $route->post('', 'Kategori::show');
    $route->put('', 'Kategori::store');
    $route->patch('', 'Kategori::update');
    $route->delete('', 'Kategori::delete');
});


$routes->group('supplier', static function ($route) {
    $route->get('', 'Supplier::index');
    $route->post('ajax', 'Supplier::ajax');
    $route->get('lastid', 'Supplier::lastid');
    $route->put('', 'Supplier::store');
    $route->patch('', 'Supplier::update');
    $route->delete('', 'Supplier::delete');
    $route->get('getid', 'Supplier::GetId');
});

$routes->group('customer', static function ($route) {
    $route->get('', 'Customer::index');
    $route->post('ajax', 'Customer::ajax');
    $route->get('lastid', 'Customer::lastid');
    $route->put('', 'Customer::store');
    $route->patch('', 'Customer::update');
    $route->delete('', 'Customer::delete');
});

$routes->group('item', static function ($route) {
    $route->get('', 'Item::index');
    $route->post('ajax', 'Item::ajax');
    $route->get('create', 'Item::create');
    $route->get('edit/(:segment)', 'Item::edit/$1');
    $route->get('view/(:segment)', 'Item::view/$1');
    $route->get('lastid', 'Item::lastid');
    $route->put('', 'Item::store');
    $route->patch('', 'Item::update');
});

$routes->group('settingharga', static function ($route) {
    $route->get('', 'Settingharga::index');
    $route->post('ajax', 'Settingharga::ajax');
    $route->get('history/(:segment)', 'Settingharga::history/$1');
    $route->patch('', 'Settingharga::save');
});

$routes->group('stock', static function ($route) {
    $route->get('', 'Stock::index');
    $route->post('ajax', 'Stock::ajax');
    $route->get('history/(:segment)', 'Stock::history/$1');
    $route->post('recalculate', 'Stock::recalculate');
});

$routes->group('historybeli', static function ($route) {
    $route->get('', 'Historybeli::index');
    $route->post('ajax', 'Historybeli::ajax');
});

$routes->group('poinmember', static function ($route) {
    $route->get('', 'Poinmember::index');
    $route->post('ajax', 'Poinmember::ajax');
    $route->post('setting', 'Poinmember::setting');
    $route->delete('hard-reset', 'Poinmember::hardReset');
    $route->post('hard-reset', 'Poinmember::hardReset');
});

$routes->group('so', static function ($route) {
    $route->get('', 'So::index');
    $route->get('input', 'So::input');
    $route->get('hasil', 'So::hasil');
    $route->get('satuan', 'So::satuan');
    $route->get('history', 'So::history');
    $route->post('create-all', 'So::createAll');
    $route->post('create-kategori', 'So::createKategori');
    $route->post('ajax-input', 'So::ajaxInput');
    $route->patch('input-save', 'So::saveInput');
    $route->post('history-input', 'So::historyInput');
    $route->post('ajax-hasil', 'So::ajaxHasil');
    $route->post('summary', 'So::summary');
    $route->post('adjust-all', 'So::adjustAll');
    $route->post('search-item', 'So::searchItem');
    $route->post('ajax-adjust', 'So::ajaxAdjust');
    $route->put('adjust', 'So::storeAdjust');
    $route->delete('adjust', 'So::deleteAdjust');
    $route->post('history-data', 'So::historyData');
});

$routes->group('jual', static function ($route) {
    $route->get('', 'Jual::index');
    $route->get('search-item', 'Jual::searchItem');
    $route->get('item-detail/(:segment)', 'Jual::itemDetail/$1');
    $route->get('search-customer', 'Jual::searchCustomer');
    $route->post('register-member', 'Jual::registerMember');
    $route->post('void-cart', 'Jual::voidCart');
    $route->get('struk/(:segment)', 'Jual::struk/$1');
    $route->post('', 'Jual::save');
});

$routes->group('listjual', static function ($route) {
    $route->get('', 'Listjual::index');
    $route->post('ajax', 'Listjual::ajax');
    $route->get('show/(:segment)', 'Listjual::show/$1');
    $route->get('edit/(:segment)', 'Listjual::edit/$1');
    $route->get('reprint/(:segment)', 'Listjual::reprint/$1');
    $route->patch('', 'Listjual::update');
    $route->delete('', 'Listjual::delete');
});

$routes->group('lapjual', static function ($route) {
    $route->get('', 'Lapjual::index');
    $route->post('ajax', 'Lapjual::ajax');
    $route->post('summary', 'Lapjual::summary');
});

$routes->group('lapcash', static function ($route) {
    $route->get('', 'Lapcash::index');
    $route->post('report', 'Lapcash::report');
});

$routes->group('lapharian', static function ($route) {
    $route->get('', 'Lapharian::index');
    $route->post('report', 'Lapharian::report');
    $route->get('struk', 'Lapharian::struk');
});

$routes->group('closing', static function ($route) {
    $route->get('', 'Closing::index');
    $route->get('dashboard', 'Closing::dashboard');
    $route->post('process', 'Closing::process');
    $route->post('reclose', 'Closing::reclose');
});
$routes->cli('closing/cli', 'Closing::cli');

$routes->group('bap', static function ($route) {
    $route->get('', 'Bap::index');
    $route->get('add', 'Bap::add');
    $route->get('edit/(:segment)', 'Bap::edit/$1');
    $route->get('show/(:segment)', 'Bap::show/$1');
    $route->get('print/(:segment)', 'Bap::print/$1');
    $route->get('search-item', 'Bap::searchItem');
    $route->get('item-detail/(:segment)', 'Bap::itemDetail/$1');
    $route->post('ajax', 'Bap::ajax');
    $route->put('', 'Bap::store');
    $route->patch('', 'Bap::update');
    $route->delete('', 'Bap::delete');
});

$routes->group('konversi', static function ($route) {
    $route->get('', 'Konversi::index');
    $route->get('add', 'Konversi::add');
    $route->get('show/(:segment)', 'Konversi::show/$1');
    $route->get('recipe', 'Konversi::recipe');
    $route->get('search-result', 'Konversi::searchResult');
    $route->get('result-recipe/(:segment)', 'Konversi::resultRecipe/$1');
    $route->get('search-item', 'Konversi::searchItem');
    $route->get('item-detail/(:segment)', 'Konversi::itemDetail/$1');
    $route->post('ajax', 'Konversi::ajax');
    $route->post('recipe-ajax', 'Konversi::recipeAjax');
    $route->put('', 'Konversi::store');
    $route->delete('', 'Konversi::delete');
    $route->put('recipe', 'Konversi::recipeStore');
    $route->patch('recipe', 'Konversi::recipeUpdate');
    $route->delete('recipe', 'Konversi::recipeDelete');
});

$routes->group('akunkas', static function ($route) {
    $route->get('', 'Akunkas::index');
    $route->post('ajax', 'Akunkas::ajax');
    $route->put('', 'Akunkas::store');
    $route->patch('', 'Akunkas::update');
    $route->delete('', 'Akunkas::delete');
});

$routes->group('kas', static function ($route) {
    $route->get('', 'Kas::index');
    $route->post('ajax', 'Kas::ajax');
    $route->put('', 'Kas::store');
    $route->patch('', 'Kas::update');
    $route->delete('', 'Kas::delete');
});

$routes->group('summarykas', static function ($route) {
    $route->get('', 'Summarykas::index');
    $route->post('ajax', 'Summarykas::ajax');
    $route->post('summary', 'Summarykas::summary');
});

$routes->group('pembelian', static function ($route) {
    $route->get('', 'Pembelian::index');
    $route->post('ajax', 'Pembelian::ajax');
    $route->get('add', 'Pembelian::add');
    $route->get('edit/(:segment)', 'Pembelian::edit/$1');
    $route->get('show/(:segment)', 'Pembelian::show/$1');
    $route->get('history/(:segment)', 'Pembelian::history/$1');
    $route->get('search-item', 'Pembelian::searchItem');
    $route->get('item-detail/(:segment)', 'Pembelian::itemDetail/$1');
    $route->post('pay/(:segment)', 'Pembelian::pay/$1');
    $route->put('', 'Pembelian::store');
    $route->patch('', 'Pembelian::update');
    $route->delete('', 'Pembelian::delete');
});

$routes->group('hutang', static function ($route) {
    $route->get('', 'Hutang::index');
    $route->post('ajax', 'Hutang::ajax');
    $route->get('saldo-form', 'Hutang::saldoForm');
    $route->get('saldo-form/(:segment)', 'Hutang::saldoForm/$1');
    $route->put('saldo', 'Hutang::storeSaldo');
    $route->patch('saldo', 'Hutang::updateSaldo');
    $route->delete('saldo', 'Hutang::deleteSaldo');
});

$routes->group('piutang', static function ($route) {
    $route->get('', 'Piutang::index');
    $route->post('ajax', 'Piutang::ajax');
    $route->get('show/(:segment)', 'Piutang::show/$1');
    $route->post('pay/(:segment)', 'Piutang::pay/$1');
});

$routes->group('historybayar', static function ($route) {
    $route->get('', 'HistoryBayar::index');
    $route->post('ajax', 'HistoryBayar::ajax');
    $route->get('show/(:num)', 'HistoryBayar::show/$1');
    $route->patch('', 'HistoryBayar::update');
    $route->delete('', 'HistoryBayar::delete');
});

$routes->group('returbeli', static function ($route) {
    $route->get('', 'ReturBeli::index');
    $route->post('ajax', 'ReturBeli::ajax');
    $route->get('add', 'ReturBeli::add');
    $route->get('edit/(:segment)', 'ReturBeli::edit/$1');
    $route->get('search-item', 'ReturBeli::searchItem');
    $route->get('item-detail/(:segment)', 'ReturBeli::itemDetail/$1');
    $route->get('source/(:segment)', 'ReturBeli::source/$1');
    $route->get('show/(:segment)', 'ReturBeli::show/$1');
    $route->put('', 'ReturBeli::store');
    $route->patch('', 'ReturBeli::update');
    $route->delete('', 'ReturBeli::delete');
});

$routes->group('returjual', static function ($route) {
    $route->get('', 'Returjual::index');
    $route->get('add', 'Returjual::add');
    $route->get('edit/(:segment)', 'Returjual::edit/$1');
    $route->post('ajax', 'Returjual::ajax');
    $route->get('sale/(:segment)', 'Returjual::sale/$1');
    $route->get('show/(:segment)', 'Returjual::show/$1');
    $route->get('struk/(:segment)', 'Returjual::struk/$1');
    $route->put('', 'Returjual::store');
    $route->patch('', 'Returjual::update');
    $route->delete('', 'Returjual::delete');
});

$routes->group('transfer', static function ($route) {
    $route->get('', 'Transfer::index');
    $route->post('ajax-po', 'Transfer::ajaxPo');
    $route->post('ajax', 'Transfer::ajax');
    $route->get('add/(:segment)/(:segment)', 'Transfer::add/$1/$2');
    $route->get('edit/(:segment)', 'Transfer::edit/$1');
    $route->get('show/(:segment)', 'Transfer::show/$1');
    $route->get('search-item', 'Transfer::searchItem');
    $route->get('item-detail/(:segment)', 'Transfer::itemDetail/$1');
    $route->post('send/(:segment)', 'Transfer::send/$1');
    $route->post('approve/(:segment)', 'Transfer::approve/$1');
    $route->post('reject/(:segment)', 'Transfer::reject/$1');
    $route->put('', 'Transfer::store');
    $route->patch('', 'Transfer::update');
});
