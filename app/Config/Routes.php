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

$routes->get('/', 'Main::index', ['filter' => 'sudahlogin']);
$routes->get('/main', 'Main::index', ['filter' => 'sudahlogin']);
$routes->get('/login', 'Login::index', ['filter' => 'belumlogin']);
$routes->get('/logout', 'Login::out');
$routes->match(['post'], '/login', 'Login::check');


$routes->group('user', ['filter' => 'sudahlogin'], static function ($routes) {
    $routes->get('', 'User::index');
    $routes->post('ajax', 'User::ajax');
    $routes->get('lastid', 'User::lastid');
    $routes->get('getid', 'User::GetId');
    $routes->get('(:segment)', 'User::Read/$1');
    $routes->post('reset', 'User::ResetPassword');
    $routes->put('/', 'User::Create');
    $routes->patch('/', 'User::Update');
    $routes->delete('/', 'User::Delete');
});

$routes->group('menu', static function ($route) {
    $route->get('', 'Menu::index');
    $route->post('ajax', 'Menu::ajax');
    $route->put('', 'Menu::store');
    $route->patch('', 'Menu::update');
    $route->delete('', 'Menu::delete');
});
