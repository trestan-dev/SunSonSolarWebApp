<?php

use CodeIgniter\Router\RouteCollection;

/** @var RouteCollection $routes */
$routes->get('/', 'Home::index');
$routes->get('home', 'Home::index');
$routes->get('customer/(:segment)', 'Home::index/$1');
$routes->get('employee/(:segment)', 'Home::index/$1');
$routes->get('admin/(:segment)', 'Home::index/$1');
$routes->get('employee-registration', 'AuthController::employeeRegistration');
$routes->get('login', 'AuthController::loginPage');
$routes->get('register', 'AuthController::registrationPage');
$routes->get('logout', 'AuthController::logout');

$routes->post('login', 'AuthController::handleLogin');
$routes->post('register', 'AuthController::handleRegister');
$routes->post('portal/employees', 'PortalController::createEmployee');
$routes->post('portal/users/(:num)/toggle', 'PortalController::toggleUser/$1');
$routes->post('portal/users/(:num)/update', 'PortalController::updateUser/$1');
$routes->post('portal/bookings', 'PortalController::createBooking');
$routes->post('portal/bookings/(:num)/cancel', 'PortalController::cancelBooking/$1');
$routes->post('portal/bookings/(:num)/update', 'PortalController::updateBooking/$1');
$routes->post('portal/profile', 'PortalController::updateProfile');
$routes->post('portal/password', 'PortalController::changePassword');
$routes->post('portal/preferences', 'PortalController::updatePreferences');
$routes->post('portal/tickets', 'PortalController::createTicket');
$routes->post('portal/tickets/(:num)/reply', 'PortalController::replyTicket/$1');
$routes->post('portal/announcements', 'PortalController::announce');
$routes->post('portal/stories', 'PortalController::addCustomerStory');
$routes->post('portal/stories/(:num)/toggle', 'PortalController::toggleCustomerStory/$1');
$routes->post('portal/services/(:num)', 'PortalController::updateService/$1');
$routes->post('portal/payments', 'PortalController::recordPayment');
$routes->post('portal/check-in', 'PortalController::checkIn');
$routes->post('portal/reports', 'PortalController::submitReport');
