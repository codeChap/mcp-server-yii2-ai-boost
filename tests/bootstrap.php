<?php

declare(strict_types=1);

/**
 * Bootstrap file for PHPUnit tests
 *
 * Sets up the test environment and autoloading
 */

// Define the base path for the application
define('BASE_PATH', __DIR__ . '/..');

// Load the Composer autoloader
require_once BASE_PATH . '/vendor/autoload.php';

// Set error reporting
error_reporting(E_ALL);
ini_set('display_errors', '1');
