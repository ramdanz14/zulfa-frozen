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
});

$routes->group('historybayar', static function ($route) {
    $route->get('', 'HistoryBayar::index');
    $route->post('ajax', 'HistoryBayar::ajax');
    $route->get('show/(:num)', 'HistoryBayar::show/$1');
    $route->patch('', 'HistoryBayar::update');
    $route->delete('', 'HistoryBayar::delete');
});
