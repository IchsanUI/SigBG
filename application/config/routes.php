<?php
defined('BASEPATH') OR exit('No direct script access allowed');

// Default -> login
$route['default_controller'] = 'auth';
$route['404_override'] = '';
$route['translate_uri_dashes'] = FALSE;

// Custom routes
$route['login']  = 'auth';
$route['logout'] = 'auth/logout';
