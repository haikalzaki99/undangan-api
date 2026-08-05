<?php

/**
 * Redirect request to public.
 *
 * Vercel + PHP.
 *
 * Muat env vars Vercel (dari getenv) ke $_ENV sebelum framework dimuat,
 * karena framework Kamu hanya membaca $_ENV / file .env.
 */

foreach (getenv() as $key => $value) {
    if (is_string($value)) {
        $_ENV[$key] = $value;
    }
}

if (isset($_ENV['PDO_MYSQL_ATTR_SSL_CA']) && !str_starts_with($_ENV['PDO_MYSQL_ATTR_SSL_CA'], DIRECTORY_SEPARATOR)) {
    $_ENV['PDO_MYSQL_ATTR_SSL_CA'] = dirname(__DIR__) . DIRECTORY_SEPARATOR . $_ENV['PDO_MYSQL_ATTR_SSL_CA'];
}

require_once __DIR__ . '/../vendor/autoload.php';

\Core\Routing\Route::$route = [];

require_once __DIR__ . '/../public/index.php';
