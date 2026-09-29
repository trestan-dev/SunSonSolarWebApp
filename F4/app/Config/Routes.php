<?php

use CodeIgniter\Router\RouteCollection;

/** @var RouteCollection $routes */
$routes->get('/', 'AuthController::loginPage');
$routes->get('home', 'Home::index');
$routes->get('employee-registration', 'AuthController::employeeRegistration');
$routes->get('login', 'AuthController::loginPage');
$routes->get('register', 'AuthController::registrationPage');
$routes->get('logout', 'AuthController::logout');

$routes->post('login', 'AuthController::handleLogin');
$routes->post('register', 'AuthController::handleRegister');
