<?php
declare(strict_types=1);
namespace App\Core;
final class View
{
    public static function render(string $view, array $data, string $layoutPath): void
    {
        extract($data, EXTR_SKIP);
        $viewPath = BASE_PATH . '/' . ltrim($view, '/');
        if (!is_file($viewPath)) {
            throw new \RuntimeException('Просмотр не найден: ' . $viewPath);
        }
        require $layoutPath;
    }
}