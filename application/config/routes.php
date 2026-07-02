<?php
defined('BASEPATH') OR exit('No direct script access allowed');

// Default -> login
$route['default_controller'] = 'auth';
$route['404_override'] = '';
$route['translate_uri_dashes'] = FALSE;

// Custom routes
$route['login']  = 'auth';
$route['logout'] = 'auth/logout';

// Media routes
$route['media/upload']    = 'media/upload';
$route['media/delete/(:num)'] = 'media/delete/$1';
$route['media/edit/(:num)']   = 'media/edit/$1';
$route['media/serve/(:num)']  = 'media/serve/$1';
$route['media']          = 'media/index';
