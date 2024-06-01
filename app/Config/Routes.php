<?php

use CodeIgniter\Router\RouteCollection;
use App\Controllers\Books;
use App\Controllers\Users;
use App\Controllers\Carts;
use App\Controllers\Home;

/**
 * @var RouteCollection $routes
 */

// Logging Filter - path adjustment (api).
$routes->group('api', ['filter' => 'logging'], function ($routes) {

    // For testing purposes.
    $routes->get('home', [Home::class, 'index']);

    // Books Routers.
    $routes->get('index', [Books::class, 'index']);
    $routes->get('books', [Books::class, 'allBooks']);
    $routes->get('book/(:num)', [Books::class, 'singleBook/$1']);
    $routes->get('status/(:num)', [Books::class, 'bookAvailability/$1']);
    $routes->get('search/(:any)', [Books::class, 'search/$1']);
    $routes->get('search_by/(:any)', [Books::class, 'searchBy/$1']);

    // Cart Routers.
    $routes->post('add_item', [Carts::class, 'addItem']);
    $routes->delete('remove_item/(:num)', [Carts::class, 'removeItem/$1']);
    $routes->get('view_cart', [Carts::class, 'viewCart']);
    //-------------------------------------------------------------------------

    // Group accessible for users who didn't login / register yet.
    $routes->group('', ['filter' => 'quest'], function ($routes) {

        // Users Routers.
        $routes->post('register', [Users::class, 'register']);
        $routes->post('login', [Users::class, 'login']);
    });
    //-------------------------------------------------------------------------

    // Group accessible for authenticated users only.
    $routes->group('', ['filter' => 'auth'], function ($routes) {

        // Books Routers.
        $routes->post('add', 'Books::add');
        $routes->put('update/(:num)', 'Books::update/$1');
        $routes->put('update_book_quantity/(:num)', 'Books::updateBookQuantity/$1');
        $routes->delete('delete/(:num)', 'Books::destroy/$1');

        // Orders Routers.
        $routes->post('checkout', [Carts::class, 'checkout']);

        // User Routers.
        $routes->post('logout', [Users::class, 'logout']);
    });
});
