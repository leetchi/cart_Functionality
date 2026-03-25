<?php

use CodeIgniter\Router\RouteCollection;

/**
 * @var RouteCollection $routes
 */
$routes->get('/', 'Home::index');
$routes->post('add-to-cart', 'Home::addToCart');
$routes->get('cart', 'Home::cart');
$routes->get('cart/remove/(\d+)', 'Home::removeCartItem/$1');
