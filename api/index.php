<?php
/*
 * Vercel entry point (not used on XAMPP, where Apache serves each page directly).
 * Every request that isn't a static asset arrives here; it maps the URL to the
 * matching page file, applies the old-URL redirects and keeps private folders private.
 */

$root = dirname(__DIR__);

// Hosted demo: use the self-seeding SQLite database unless a MySQL host is configured.
if (getenv('DB_DRIVER') === false && getenv('DB_HOST') === false) {
    putenv('DB_DRIVER=sqlite');
}

$path = rawurldecode(parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH) ?: '/');
$path = ltrim($path, '/');

$redirects = [
    'Home_Page.html'     => '/',
    'home_page.html'     => '/',
    'About.html'         => '/about.php',
    'about.html'         => '/about.php',
    'links.html'         => '/links.php',
    'Entrance_Page.html' => '/entrance.php',
    'Stylesheet.css'     => '/assets/css/base.css',
];
if (isset($redirects[$path])) {
    header('Location: ' . $redirects[$path], true, 301);
    exit;
}

if ($path === '' || substr($path, -1) === '/') {
    $path .= 'index.php';
}

$blocked = preg_match('#(^|/)\.|^(includes|database|api|tools)(/|$)#', $path) || strpos($path, '..') !== false;
$file = $blocked ? false : realpath($root . '/' . $path);

if ($file === false || strpos($file, $root . '/') !== 0 || substr($file, -4) !== '.php' || !is_file($file)) {
    if ($file !== false && is_dir($file) && is_file($file . '/index.php')) {
        header('Location: /' . rtrim($path, '/') . '/', true, 301);
        exit;
    }
    $file = $root . '/404.php';
    $path = '404.php';
    http_response_code(404);
}

$_SERVER['SCRIPT_FILENAME'] = $file;
$_SERVER['SCRIPT_NAME']     = '/' . $path;
$_SERVER['PHP_SELF']        = '/' . $path;
chdir(dirname($file));
require $file;
