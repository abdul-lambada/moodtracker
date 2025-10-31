<?php

declare(strict_types=1);

if (!function_exists('str_starts_with')) {
    function str_starts_with(string $haystack, string $needle): bool
    {
        return $needle === '' || strncmp($haystack, $needle, strlen($needle)) === 0;
    }
}

if (!function_exists('str_ends_with')) {
    function str_ends_with(string $haystack, string $needle): bool
    {
        if ($needle === '') {
            return true;
        }

        $length = strlen($needle);
        return substr($haystack, -$length) === $needle;
    }
}

function base_path(string $path = ''): string
{
    $base = dirname(__DIR__);
    return $path !== '' ? $base . DIRECTORY_SEPARATOR . str_replace(['\\', '/'], DIRECTORY_SEPARATOR, $path) : $base;
}

function env(string $key, $default = null)
{
    $value = $_ENV[$key] ?? $_SERVER[$key] ?? getenv($key);
    if ($value === false || $value === null) {
        return value($default);
    }

    $lower = strtolower($value);
    if (in_array($lower, ['true', '(true)'], true)) {
        return true;
    }
    if (in_array($lower, ['false', '(false)'], true)) {
        return false;
    }
    if (in_array($lower, ['empty', '(empty)'], true)) {
        return '';
    }
    if (in_array($lower, ['null', '(null)'], true)) {
        return null;
    }

    if (strlen($value) > 1 && str_starts_with($value, '"') && str_ends_with($value, '"')) {
        return substr($value, 1, -1);
    }

    return $value;
}

function value($value)
{
    return $value instanceof \Closure ? $value() : $value;
}

function config(string $key, $default = null)
{
    static $repository = [];

    $segments = explode('.', $key);
    $file = array_shift($segments);
    if ($file === null) {
        return $default;
    }

    if (!array_key_exists($file, $repository)) {
        $path = base_path('config' . DIRECTORY_SEPARATOR . $file . '.php');
        $repository[$file] = file_exists($path) ? require $path : null;
    }

    $value = $repository[$file];
    foreach ($segments as $segment) {
        if (!is_array($value) || !array_key_exists($segment, $value)) {
            return value($default);
        }
        $value = $value[$segment];
    }

    return $value ?? value($default);
}

function redirect(string $path, int $status = 302): void
{
    if (!headers_sent()) {
        header('Location: ' . $path, true, $status);
    }
    exit;
}

function session_get(string $key, $default = null)
{
    return $_SESSION[$key] ?? value($default);
}

function session_put(string $key, $value): void
{
    $_SESSION[$key] = $value;
}

function session_forget(string $key): void
{
    unset($_SESSION[$key]);
}

function flash(string $key, $value = null)
{
    if ($value === null) {
        $flashData = session_get('__flash', []);
        $data = $flashData[$key] ?? null;
        if ($data !== null) {
            unset($flashData[$key]);
            session_put('__flash', $flashData);
        }
        return $data;
    }

    $flashData = session_get('__flash', []);
    $flashData[$key] = $value;
    session_put('__flash', $flashData);
}

function old(string $key, $default = '')
{
    $old = session_get('__old', []);
    return $old[$key] ?? value($default);
}

function store_old(array $input): void
{
    session_put('__old', $input);
}

function clear_old(): void
{
    session_forget('__old');
}

function csrf_token(): string
{
    return \App\Security\Csrf::token();
}

function verify_csrf(string $token): bool
{
    return \App\Security\Csrf::verify($token);
}

function csrf_field(): string
{
    return '<input type="hidden" name="_token" value="' . htmlspecialchars(csrf_token(), ENT_QUOTES, 'UTF-8') . '">';
}

function abort(int $status = 500, string $context = 'public', array $data = []): void
{
    if (!headers_sent()) {
        http_response_code($status);
    }

    $view = $context === 'admin'
        ? base_path('public' . DIRECTORY_SEPARATOR . 'admin' . DIRECTORY_SEPARATOR . 'error.php')
        : base_path('public' . DIRECTORY_SEPARATOR . 'errors' . DIRECTORY_SEPARATOR . 'error.php');

    $statusText = [
        403 => 'Akses Ditolak',
        404 => 'Halaman Tidak Ditemukan',
        500 => 'Terjadi Kesalahan',
    ][$status] ?? 'Terjadi Kesalahan';

    $title = $data['title'] ?? $statusText;
    $message = $data['message'] ?? 'Silakan kembali ke halaman sebelumnya atau hubungi administrator.';
    $statusCode = $status;

    if (file_exists($view)) {
        $title = (string) $title;
        $message = (string) $message;
        $statusCode = (int) $statusCode;
        include $view;
    } else {
        echo htmlspecialchars((string) $title, ENT_QUOTES, 'UTF-8');
    }

    exit;
}
