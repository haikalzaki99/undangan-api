<?php

$keys = [
    'APP_NAME', 'APP_KEY', 'BASEURL', 'DEBUG', 'LOG', 'TIMEZONE',
    'DB_DRIV', 'DB_HOST', 'DB_PORT', 'DB_NAME', 'DB_USER', 'DB_PASS',
    'DB_OPTIONS', 'PDO_MYSQL_ATTR_SSL_CA',
    'JWT_EXP', 'JWT_KEY', 'OWNER_KEY',
    'MONGODB_URI', 'MONGODB_DB', 'MONGODB_COLLECTION',
    'RATE_LIMIT', 'RATE_LIMIT_WINDOW',
];

$lines = [];
foreach ($keys as $key) {
    $value = getenv($key);
    if ($value !== false) {
        $lines[] = $key . '=' . $value;
    }
}

if ($lines) {
    file_put_contents(__DIR__ . '/.env', implode("\n", $lines) . "\n");
    echo "Generated .env\n";
} else {
    fwrite(STDERR, "No env vars found\n");
    exit(1);
}