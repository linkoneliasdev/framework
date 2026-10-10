<?php

require_once __DIR__ . '/../../vendor/autoload.php';

use Dotenv\Dotenv;
$dotenv = Dotenv::createImmutable(__DIR__ . '/../../');
$dotenv->safeLoad();

date_default_timezone_set('America/Sao_Paulo');

define('APP_NAME', $_ENV['APP_NAME'] ?? 'App');
define('APP_URL', rtrim($_ENV['APP_URL'] ?? '', '/'));
define('APP_ICON', rtrim($_ENV['APP_ICON'] ?? ''));

define('APP_ENV', $_ENV['APP_ENV'] ?? 'production');

define(
    'APP_DEBUG',
    filter_var($_ENV['APP_DEBUG'] ?? false, FILTER_VALIDATE_BOOLEAN)
);

if (APP_DEBUG) {
    ini_set('display_errors', 1);
    ini_set('display_startup_errors', 1);
    error_reporting(E_ALL);
} else {
    ini_set('display_errors', 0);
    ini_set('display_startup_errors', 0);
    error_reporting(0);
}