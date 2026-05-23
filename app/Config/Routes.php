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
