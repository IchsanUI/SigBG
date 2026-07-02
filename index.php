<?php
/**
 * CodeIgniter
 *
 * An open source application development framework for PHP
 *
 * @package     CodeIgniter
 * @author      EllisLab Dev Team
 * @copyright   Copyright (c) 2008 - 2014, EllisLab, Inc. (https://ellislab.com/)
 * @copyright   Copyright (c) 2014 - 2019, British Columbia Institute of Technology (https://bcit.ca/)
 * @license     http://opensource.org/licenses/MIT	MIT License
 * @link        https://codeigniter.com
 * @since       Version 1.0.0
 * @filesource
 */

/*
 * ---------------------------------------------------------------
 * SYSTEM FOLDER NAME
 * ---------------------------------------------------------------
 */
$system_path = 'system';

/*
 * ---------------------------------------------------------------
 * APPLICATION FOLDER NAME
 * ---------------------------------------------------------------
 */
$application_folder = 'application';

/*
 * ---------------------------------------------------------------
 * APPLICATION ENVIRONMENT
 * ---------------------------------------------------------------
 */
if (isset($_SERVER['CI_ENV']) === FALSE) {
    $_SERVER['CI_ENV'] = 'development';
}

defined('ENVIRONMENT') or define('ENVIRONMENT', $_SERVER['CI_ENV']);

if (defined('ENVIRONMENT')) {
    switch (ENVIRONMENT) {
        case 'development':
            error_reporting(-1);
            ini_set('display_errors', 1);
            break;
        case 'testing':
        case 'production':
            error_reporting(0);
            if (ini_get('display_errors')) {
                ini_set('display_errors', 0);
            }
            break;
        default:
            exit('The application environment is not correctly set.');
    }
}

/*
 * ---------------------------------------------------------------
 * SET THE SYSTEM FOLDER PATH
 * ---------------------------------------------------------------
 */
$system_path = rtrim($system_path, '/') . '/';

// A real system directory exists, and it is NOT in the same directory
// as this script. Try to resolve relative paths.
if (strpos($system_path, '/') === FALSE) {
    if (strpos($system_path, DIRECTORY_SEPARATOR) === FALSE) {
        $system_path = dirname(__FILE__) . DIRECTORY_SEPARATOR . $system_path . DIRECTORY_SEPARATOR;
    } else {
        $system_path = preg_match('#^(.+)/$#', $system_path, $matches) ? $matches[1] . DIRECTORY_SEPARATOR : $system_path;
    }
} else {
    $system_path = str_replace('/', DIRECTORY_SEPARATOR, $system_path);
}

// Is the system path correct?
if (!is_dir($system_path)) {
    header($_SERVER['SERVER_PROTOCOL'] . ' 503 Service Unavailable.', TRUE, 503);
    echo 'Your system folder path does not appear to be set correctly. Please open the file and this troubleshoot this issue.';
    exit('The system folder is missing.');
}

/*
 * ---------------------------------------------------------------
 * APPLICATION FOLDER OPERATIONS
 * ---------------------------------------------------------------
 */
if (isset($application_folder) === FALSE) {
    $application_folder = dirname(__FILE__) . '/application';
}

if (is_dir($application_folder)) {
    $application_folder = rtrim($application_folder, '/') . DIRECTORY_SEPARATOR;
} else {
    $application_folder = dirname(__FILE__) . DIRECTORY_SEPARATOR . 'application' . DIRECTORY_SEPARATOR;
}

// --------------
// Now load the bootstrap
// --------------
defined('BASEPATH') or define('BASEPATH', realpath($system_path) . DIRECTORY_SEPARATOR);

$application_paths = $application_folder;
$current_path = realpath(dirname(__FILE__)) . DIRECTORY_SEPARATOR;

// Get the system path
$real_system_path = realpath(BASEPATH) . DIRECTORY_SEPARATOR;

// Remove any trailing separators and then triple-check we still have a single separator
$system_path = str_replace('\\', '/', $system_path);
if (substr($system_path, -1) !== '/') {
    $system_path .= DIRECTORY_SEPARATOR;
}

// Are the errors being displayed to the user or not?
if ((bool) ini_get('display_errors') === TRUE) {
    error_reporting(-1);
    ini_set('display_errors', 1);
} else {
    error_reporting(-1);
    ini_set('display_errors', '0');
}

// Fix for PHP CLI mode
if (php_sapi_name() === 'cli') {
    define('STDIN', fopen('php://stdin', 'r'));
    define('STDOUT', fopen('php://stdout', 'w'));
    define('STDERR', fopen('php://stderr', 'r'));
}

// LOAD THE FRONT CONTROLLER
if (!is_file($real_system_path . 'core/CodeIgniter.php')) {
    header($_SERVER['SERVER_PROTOCOL'] . ' 503 Service Unavailable.', TRUE, 503);
    echo 'The system/core/CodeIgniter.php file is missing. You may have copied only part of the system folder.';
    exit('Badly constructed core/CodeIgniter.php — it does not include CI_Base class. It may have been stripped during an incomplete copy.');
}

require_once BASEPATH . 'core/CodeIgniter.php';

/*
 * ---------------------------------------------------------------
 * LOAD THE APPLICATION FOLDER OPERATIONS
 * ---------------------------------------------------------------
 *
 * We need to perform some operations on the application folder.
 */
if (is_dir($application_paths)) {
    $CI =& get_instance();
    $CI->load->helper('url');
    $CI->load->driver('cache');
}
