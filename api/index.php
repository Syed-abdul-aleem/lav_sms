<?php
// LAV_SMS on Vercel (serverless) entry point.
// Vercel's filesystem is read-only except /tmp, so prepare writable copies there.
$tmpStorage = '/tmp/laravel-storage';
foreach ([
    $tmpStorage . '/app/public',
    $tmpStorage . '/framework/cache/data',
    $tmpStorage . '/framework/sessions',
    $tmpStorage . '/framework/views',
    $tmpStorage . '/logs',
    '/tmp/views',
] as $dir) {
    if (!is_dir($dir)) {
        @mkdir($dir, 0777, true);
    }
}

// Seeded SQLite database: copy the bundled demo DB to /tmp on cold start.
$tmpDb = '/tmp/database.sqlite';
if (!file_exists($tmpDb)) {
    $srcDb = __DIR__ . '/../database/database.sqlite';
    if (file_exists($srcDb)) {
        @copy($srcDb, $tmpDb);
        @chmod($tmpDb, 0666);
    } else {
        @touch($tmpDb);
    }
}

// Production database: if a DATABASE_URL is set in the Vercel project
// environment (e.g. a Supabase Postgres connection string — see
// SUPABASE_GUIDE.md), use it. Otherwise fall back to the bundled
// SQLite demo database in /tmp (data resets on cold start).
$externalDbUrl = $_SERVER['DATABASE_URL'] ?? getenv('DATABASE_URL');
$useExternalDb = !empty($externalDbUrl);

// Environment overrides (real env wins over .env in Laravel).
$envs = [
    'APP_ENV' => 'production',
    'APP_VERCEL' => '1',
    'APP_DEBUG' => 'false',
    'DB_CONNECTION' => $useExternalDb ? 'pgsql' : 'sqlite',
    'DB_DATABASE' => $useExternalDb ? '' : $tmpDb,
    'CACHE_DRIVER' => 'array',
    'SESSION_DRIVER' => 'cookie',
    'QUEUE_CONNECTION' => 'sync',
    'LOG_CHANNEL' => 'stderr',
    'VIEW_COMPILED_PATH' => '/tmp/views',
    'APP_SERVICES_CACHE' => '/tmp/services.php',
    'APP_PACKAGES_CACHE' => '/tmp/packages.php',
    'APP_CONFIG_CACHE' => '/tmp/config.php',
    'APP_ROUTES_CACHE' => '/tmp/routes.php',
    'APP_EVENTS_CACHE' => '/tmp/events.php',
];
foreach ($envs as $k => $v) {
    $_ENV[$k] = $v;
    $_SERVER[$k] = $v;
    putenv($k . '=' . $v);
}

// Serve static files from public/ through PHP only if Vercel's static
// routing did not already handle them.
$uri = urldecode(parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH));
if ($uri !== '/' && $uri !== '/index.php') {
    $file = __DIR__ . '/../public' . $uri;
    if (is_file($file)) {
        $ext = strtolower(pathinfo($file, PATHINFO_EXTENSION));
        $mimes = [
            'css' => 'text/css', 'js' => 'application/javascript', 'json' => 'application/json',
            'png' => 'image/png', 'jpg' => 'image/jpeg', 'jpeg' => 'image/jpeg', 'gif' => 'image/gif',
            'svg' => 'image/svg+xml', 'ico' => 'image/x-icon', 'woff' => 'font/woff', 'woff2' => 'font/woff2',
            'ttf' => 'font/ttf', 'eot' => 'application/vnd.ms-fontobject', 'txt' => 'text/plain',
            'map' => 'application/json', 'pdf' => 'application/pdf',
        ];
        header('Content-Type: ' . ($mimes[$ext] ?? 'application/octet-stream'));
        header('Content-Length: ' . filesize($file));
        readfile($file);
        exit;
    }
}

require __DIR__ . '/../public/index.php';
