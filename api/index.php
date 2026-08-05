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

require_once __DIR__ . '/../vendor/autoload.php';

\Core\Routing\Route::$route = [];

require_once __DIR__ . '/../public/index.php';
