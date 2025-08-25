<?php
declare(strict_types=1);
session_start();

// Error reporting (adjust for production)
ini_set('display_errors', '1');
error_reporting(E_ALL);

// Security headers
header('X-Content-Type-Options: nosniff');
header('X-Frame-Options: SAMEORIGIN');
header('Referrer-Policy: no-referrer-when-downgrade');
// Allow inline scripts because the app uses inline JS in views/layouts; limit external to jsDelivr only
header("Content-Security-Policy: default-src 'self'; script-src 'self' https://cdn.jsdelivr.net 'unsafe-inline'; style-src 'self' https://cdn.jsdelivr.net 'unsafe-inline'; img-src 'self' data: https://via.placeholder.com; font-src 'self' https://cdn.jsdelivr.net; connect-src 'self';");

// Paths
define('BASE_PATH', dirname(__DIR__));
define('APP_PATH', BASE_PATH . DIRECTORY_SEPARATOR . 'app');

require APP_PATH . '/config/config.php';
require APP_PATH . '/config/database.php';
require APP_PATH . '/core/Router.php';
require APP_PATH . '/core/Controller.php';
require APP_PATH . '/core/Model.php';
require APP_PATH . '/core/View.php';
require APP_PATH . '/helpers/Security.php';
require APP_PATH . '/helpers/Auth.php';

// Simple PSR-4 autoloader for classes under App\
spl_autoload_register(function (string $class): void {
    if (strpos($class, 'App\\') !== 0) return;
    $relative = str_replace(['App\\', '\\'], ['', DIRECTORY_SEPARATOR], $class) . '.php';
    $file = APP_PATH . DIRECTORY_SEPARATOR . $relative;
    if (is_file($file)) {
        require $file;
    }
});

use App\Core\Router;

$router = new Router(BASE_URL);

// Auth routes
$router->get('/', 'AuthController@login');
$router->get('/auth/login', 'AuthController@login');
$router->post('/auth/login', 'AuthController@doLogin');
$router->get('/auth/logout', 'AuthController@logout');

// Dashboard
$router->get('/dashboard', 'DashboardController@index');

// Users (admin only)
$router->get('/users', 'UsersController@index');
$router->get('/users/create', 'UsersController@create');
$router->post('/users/store', 'UsersController@store');
$router->get('/users/edit/{id}', 'UsersController@edit');
$router->post('/users/update/{id}', 'UsersController@update');
$router->post('/users/delete/{id}', 'UsersController@destroy');

// Products
$router->get('/products', 'ProductsController@index');
$router->get('/products/create', 'ProductsController@create');
$router->post('/products/store', 'ProductsController@store');
$router->get('/products/edit/{id}', 'ProductsController@edit');
$router->post('/products/update/{id}', 'ProductsController@update');
$router->post('/products/delete/{id}', 'ProductsController@destroy');
$router->post('/products/delete-expired', 'ProductsController@destroyExpired');
$router->get('/products/expired', 'ProductsController@expired');
$router->get('/products/expiring-30', 'ProductsController@expiring30');
$router->post('/products/retire-expired', 'ProductsController@retireExpired');
$router->get('/products/retired', 'ProductsController@retired');
$router->post('/products/reactivate/{id}', 'ProductsController@reactivate');

// Profile
$router->get('/profile', 'ProfileController@index');
$router->post('/profile/avatar', 'ProfileController@uploadAvatar');
$router->post('/profile/update', 'ProfileController@update');

// Sales
$router->get('/sales', 'SalesController@index');
$router->get('/sales/all', 'SalesController@all');
$router->get('/sales/create', 'SalesController@create');
$router->post('/sales/store', 'SalesController@store');
$router->get('/sales/invoice/{id}', 'SalesController@invoice');

$router->dispatch();
