<?php
declare(strict_types=1);
use App\Core\Csrf;
use App\Core\Session;
use App\Core\View;
function e(?string $value): string
{
    return htmlspecialchars((string) $value, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
}
function config(array $config, string $key, mixed $default = null): mixed
{
    $segments = explode('.', $key);
    $value = $config;
    foreach ($segments as $segment) {
        if (!is_array($value) || !array_key_exists($segment, $value)) {
            return $default;
        }
        $value = $value[$segment];
    }
    return $value;
}
function base_url(array $config, string $path = ''): string
{
    $base = rtrim((string) config($config, 'app.base_url', ''), '/');
    $path = ltrim($path, '/');
    return $path === '' ? $base : $base . '/' . $path;
}
function redirect(string $url, int $statusCode = 302): never
{
    header('Location: ' . $url, true, $statusCode);
    exit;
}
function old(string $key, mixed $default = ''): mixed
{
    return Session::getFlash('old.' . $key, $default);
}
function flash(string $key, mixed $value): void
{
    Session::flash($key, $value);
}
function csrf_token(): string
{
    return Csrf::token();
}
function csrf_field(): string
{
    return '<input type="hidden" name="_token" value="' . e(Csrf::token()) . '">';
}
function asset(string $path): string
{
    return '/' . ltrim($path, '/');
}
function render(string $view, array $data = [], string $layout = 'views/partials/layout.php'): void
{
    View::render($view, $data, BASE_PATH . '/' . $layout);
}
function render_admin(string $view, array $data = []): void
{
    View::render($view, $data, BASE_PATH . '/admin/views/partials/layout.php');
}
function back_with_input(string $errorKey, string $message, string $redirectUrl): never
{
    flash('error', $message);
    foreach ($_POST as $key => $value) {
        if (is_scalar($value)) {
            flash('old.' . $key, (string) $value);
        }
    }
    redirect($redirectUrl);
}
function now_string(): string
{
    return (new DateTimeImmutable('now'))->format('Г-м-д Ч:и:с');
}
function current_full_url(): string
{
    $scheme = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
    $host = $_SERVER['HTTP_HOST'] ?? 'localhost';
    $uri = $_SERVER['REQUEST_URI'] ?? '/';
    return $scheme . '://' . $host . $uri;
}
function is_post(): bool
{
    return strtoupper($_SERVER['REQUEST_METHOD'] ?? 'GET') === 'POST';
}
function method_is(string $method): bool
{
    return strtoupper($_SERVER['REQUEST_METHOD'] ?? 'GET') === strtoupper($method);
}
function slugify(string $value): string
{
    $value = mb_strtolower(trim($value), 'UTF-8');
    $transliterated = iconv('UTF-8', 'ASCII//TRANSLIT//IGNORE', $value);
    $transliterated = $transliterated !== false ? $transliterated : $value;
    $slug = preg_replace('/[^a-z0-9]+/i', '-', $transliterated);
    $slug = trim((string) $slug, '-');
    return $slug !== '' ? strtolower($slug) : 'test-' . substr(md5((string) microtime(true)), 0, 8);
}
function format_datetime(?string $value): string
{
    if (!$value) {
        return '—';
    }
    try {
        return (new DateTimeImmutable($value))->format('д.м.Г Ч:я');
    } catch (Throwable) {
        return $value;
    }
}