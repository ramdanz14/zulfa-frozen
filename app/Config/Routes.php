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
    $route->get('getid', 'User::GetId');
});
