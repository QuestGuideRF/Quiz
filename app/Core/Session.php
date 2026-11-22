<?php
declare(strict_types=1);
namespace App\Core;
final class Session
{
    public static function start(array $config): void
    {
        if (session_status() === PHP_SESSION_ACTIVE) {
            return;
        }
        session_name($config['name'] ?? 'app_session');
        session_set_cookie_params([
            'lifetime' => (int) ($config['lifetime'] ?? 7200),
            'path' => '/',
            'secure' => (bool) ($config['secure'] ?? false),
            'httponly' => (bool) ($config['httponly'] ?? true),
            'samesite' => $config['samesite'] ?? 'Lax',
        ]);
        ini_set('session.use_strict_mode', '1');
        ini_set('session.cookie_httponly', '1');
        session_start();
        if (!isset($_SESSION['_initiated'])) {
            session_regenerate_id(true);
            $_SESSION['_initiated'] = true;
        }
    }
    public static function get(string $key, mixed $default = null): mixed
    {
        return $_SESSION[$key] ?? $default;
    }
    public static function put(string $key, mixed $value): void
    {
        $_SESSION[$key] = $value;
    }
    public static function forget(string $key): void
    {
        unset($_SESSION[$key]);
    }
    public static function flash(string $key, mixed $value): void
    {
        $_SESSION['_flash'][$key] = $value;
    }
    public static function getFlash(string $key, mixed $default = null): mixed
    {
        $value = $_SESSION['_flash'][$key] ?? $default;
        unset($_SESSION['_flash'][$key]);
        return $value;
    }
    public static function has(string $key): bool
    {
        return isset($_SESSION[$key]);
    }
    public static function destroy(): void
    {
        $_SESSION = [];
        if (ini_get('session.use_cookies')) {
            $params = session_get_cookie_params();
            setcookie(session_name(), '', time() - 42000, $params['path'], $params['domain'] ?? '', $params['secure'], $params['httponly']);
        }
        session_destroy();
    }
}