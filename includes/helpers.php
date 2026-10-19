<?php
/*
 * Loaded first by every page: configuration, session, database and the
 * small helpers used across the site.
 */

define('APP_ROOT', dirname(__DIR__));

/* ---------- Configuration ---------- */

function config(string $key)
{
    static $config = null;
    if ($config === null) {
        $file = is_file(__DIR__ . '/config.php') ? __DIR__ . '/config.php' : __DIR__ . '/config.example.php';
        $config = require $file;
    }
    $env = getenv($key);
    if ($env !== false && $env !== '') {
        return $env === 'false' ? false : ($env === 'true' ? true : $env);
    }
    return $config[$key] ?? null;
}

ini_set('display_errors', config('DEBUG') ? '1' : '0');
ini_set('log_errors', '1');
error_reporting(E_ALL);
date_default_timezone_set('Europe/London');

/* ---------- Session ---------- */

function is_https(): bool
{
    return (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off')
        || (($_SERVER['HTTP_X_FORWARDED_PROTO'] ?? '') === 'https');
}

if (session_status() === PHP_SESSION_NONE) {
    if (!is_writable((string) session_save_path())) {
        session_save_path(sys_get_temp_dir());
    }
    session_name('novaspark');
    session_set_cookie_params([
        'lifetime' => 0,
        'path'     => '/',
        'secure'   => is_https(),
        'httponly' => true,
        'samesite' => 'Lax',
    ]);
    session_start();
}

require __DIR__ . '/db.php';

/* ---------- Output and formatting ---------- */

function e($value): string
{
    return htmlspecialchars((string) $value, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
}

function money($amount): string
{
    return '£' . number_format((float) $amount, 2);
}

function fmt_date(?string $value): string
{
    if (!$value) {
        return '';
    }
    $ts = strtotime($value);
    return $ts ? date('j M Y', $ts) : '';
}

function fmt_time(?string $value): string
{
    $ts = $value ? strtotime($value) : false;
    return $ts ? date('H:i', $ts) : '';
}

function order_no($id): string
{
    return '#' . str_pad((string) (int) $id, 4, '0', STR_PAD_LEFT);
}

function slugify(string $text): string
{
    $slug = strtolower(trim(preg_replace('/[^A-Za-z0-9]+/', '-', $text), '-'));
    return $slug !== '' ? $slug : 'game';
}

/* ---------- URLs ---------- */

function base_url(): string
{
    static $base = null;
    if ($base !== null) {
        return $base;
    }
    $configured = (string) config('BASE_URL');
    if ($configured !== '') {
        return $base = rtrim($configured, '/') . '/';
    }
    // Strip the script's path inside the app from SCRIPT_NAME to find where the app is mounted.
    $script = realpath($_SERVER['SCRIPT_FILENAME'] ?? '') ?: '';
    $root   = realpath(APP_ROOT) ?: APP_ROOT;
    $name   = $_SERVER['SCRIPT_NAME'] ?? '/';
    if ($script !== '' && strpos($script, $root) === 0) {
        $inside = str_replace('\\', '/', substr($script, strlen($root)));
        if ($inside !== '' && substr($name, -strlen($inside)) === $inside) {
            return $base = rtrim(substr($name, 0, -strlen($inside)), '/') . '/';
        }
    }
    return $base = rtrim(dirname($name), '/\\') . '/';
}

function url(string $path = ''): string
{
    return base_url() . ltrim($path, '/');
}

function asset(string $path): string
{
    $file = APP_ROOT . '/assets/' . $path;
    $version = is_file($file) ? '?v=' . filemtime($file) : '';
    return url('assets/' . $path) . $version;
}

function img_src(?string $src): string
{
    $src = trim((string) $src);
    if ($src === '') {
        return url('assets/img/games/fallback.svg');
    }
    return preg_match('#^https?://#i', $src) ? $src : url($src);
}

/* The current page as a path relative to the site root, for ?next= links. */
function current_path(): string
{
    $uri  = $_SERVER['REQUEST_URI'] ?? '/';
    $base = base_url();
    if (strpos($uri, $base) === 0) {
        $uri = substr($uri, strlen($base));
    }
    return ltrim($uri, '/');
}

function redirect(string $path, int $status = 303): void
{
    header('Location: ' . url($path), true, $status);
    exit;
}

/* Accept only relative paths on this site for ?next= values. */
function safe_next(?string $next, string $default = 'games.php'): string
{
    $next = trim((string) $next);
    if ($next === '' || preg_match('#^[a-z][a-z0-9+.-]*:#i', $next) || strpos($next, '//') !== false
        || strpos($next, '\\') !== false || preg_match('/[\x00-\x1f]/', $next)) {
        return $default;
    }
    $next = ltrim($next, '/');
    $base = ltrim(base_url(), '/');
    if ($base !== '' && strpos($next, $base) === 0) {
        $next = substr($next, strlen($base));
    }
    if (preg_match('#^(login|register|logout)\.php#', $next)) {
        return $default;
    }
    return $next;
}

/* ---------- Flash messages (shown once, on the next page) ---------- */

function flash(string $key, $value = null)
{
    if (func_num_args() === 2) {
        $_SESSION['flash'][$key] = $value;
        return null;
    }
    $stored = $_SESSION['flash'][$key] ?? null;
    unset($_SESSION['flash'][$key]);
    return $stored;
}

/* ---------- CSRF (one token per session) ---------- */

function csrf_token(): string
{
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

function csrf_field(): string
{
    return '<input type="hidden" name="csrf_token" value="' . e(csrf_token()) . '">';
}

function csrf_check(): bool
{
    $sent = $_POST['csrf_token'] ?? '';
    return is_string($sent) && $sent !== '' && hash_equals(csrf_token(), $sent);
}

function is_post(): bool
{
    return ($_SERVER['REQUEST_METHOD'] ?? 'GET') === 'POST';
}

function post(string $key): string
{
    $value = $_POST[$key] ?? '';
    return is_string($value) ? trim($value) : '';
}

/* ---------- Accounts ---------- */

function current_user(): ?array
{
    if (empty($_SESSION['user_id'])) {
        return null;
    }
    return [
        'id'       => (int) $_SESSION['user_id'],
        'username' => $_SESSION['username'] ?? '',
        'email'    => $_SESSION['email'] ?? '',
        'role'     => $_SESSION['role'] ?? 'customer',
    ];
}

function is_admin(): bool
{
    return (current_user()['role'] ?? '') === 'admin';
}

function log_in(array $user): void
{
    session_regenerate_id(true);
    $_SESSION['user_id']  = (int) $user['id'];
    $_SESSION['username'] = $user['username'];
    $_SESSION['email']    = $user['email'];
    $_SESSION['role']     = $user['role'];
}

function require_login(): array
{
    $user = current_user();
    if (!$user) {
        redirect('login.php?next=' . rawurlencode(current_path()));
    }
    return $user;
}

function require_admin(): array
{
    $user = require_login();
    if ($user['role'] !== 'admin') {
        http_response_code(403);
        require APP_ROOT . '/404.php';
        exit;
    }
    return $user;
}

/* ---------- Errors and mail ---------- */

const MSG_SERVER_ERROR = "Something went wrong on our side. Your details are kept; try again in a moment.";

function log_error(Throwable $e): void
{
    error_log('[Novaspark] ' . get_class($e) . ': ' . $e->getMessage() . ' in ' . $e->getFile() . ':' . $e->getLine());
}

function send_mail(string $to, string $subject, string $body): bool
{
    if (!function_exists('mail')) {
        return false;
    }
    $headers = 'From: Novaspark Games <' . config('MAIL_FROM') . ">\r\n"
             . "Content-Type: text/plain; charset=UTF-8\r\n";
    $sent = @mail($to, $subject, $body, $headers);
    if (!$sent) {
        error_log('[Novaspark] mail() could not send "' . $subject . '" to ' . $to);
    }
    return $sent;
}

/* ---------- Catalogue helpers ---------- */

const GAME_STATUSES = [
    'available'   => 'Available',
    'coming_soon' => 'Coming soon',
    'beta'        => 'Beta',
    'hidden'      => 'Hidden',
];

function can_buy(array $game): bool
{
    return in_array($game['status'], ['available', 'beta'], true);
}

function find_game(int $id, bool $includeHidden = false): ?array
{
    $sql = 'SELECT * FROM games WHERE id = ?' . ($includeHidden ? '' : " AND status <> 'hidden'");
    $game = q($sql, [$id])->fetch();
    return $game ?: null;
}

function visible_genres(): array
{
    return q("SELECT DISTINCT genre FROM games WHERE status <> 'hidden' ORDER BY genre")->fetchAll(PDO::FETCH_COLUMN);
}

/* Class and aria-current for a navigation link. */
function nav_attrs(string $key, string $active, string $class): string
{
    return 'class="' . $class . ($key === $active ? ' is-current' : '') . '"' . ($key === $active ? ' aria-current="page"' : '');
}

require __DIR__ . '/icons.php';
foreach (glob(__DIR__ . '/components/*.php') as $component) {
    require $component;
}
