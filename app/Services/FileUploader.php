<?php
declare(strict_types=1);
namespace App\Services;
final class FileUploader
{
    public function __construct(
        private readonly string $basePath,
        private readonly array $config
    ) {
    }
    public function upload(array $file, string $type): ?string
    {
        if (($file['error'] ?? UPLOAD_ERR_NO_FILE) === UPLOAD_ERR_NO_FILE) {
            return null;
        }
        if (($file['error'] ?? UPLOAD_ERR_OK) !== UPLOAD_ERR_OK) {
            throw new \RuntimeException('Не удалось загрузить файл.');
        }
        if (($file['size'] ?? 0) > (int) $this->config['max_size']) {
            throw new \RuntimeException('Файл слишком большой. Максимум 2 МБ.');
        }
        $extension = strtolower((string) pathinfo((string) $file['name'], PATHINFO_EXTENSION));
        if (!in_array($extension, $this->config['allowed_extensions'], true)) {
            throw new \RuntimeException('Недопустимое расширение файла.');
        }
        $finfo = new \finfo(FILEINFO_MIME_TYPE);
        $mimeType = (string) $finfo->file((string) $file['tmp_name']);
        if (!in_array($mimeType, $this->config['allowed_mime_types'], true)) {
            throw new \RuntimeException('Недопустимый MIME-тип файла.');
        }
        $relativeDir = $this->config['directories'][$type] ?? null;
        if (!$relativeDir) {
            throw new \RuntimeException('Неверный тип загрузки.');
        }
        $absoluteDir = $this->basePath . $relativeDir;
        if (!is_dir($absoluteDir) && !mkdir($absoluteDir, 0775, true) && !is_dir($absoluteDir)) {
            throw new \RuntimeException('Не удалось создать папку для загрузки.');
        }
        $filename = sprintf('%s_%s.%s', $type, bin2hex(random_bytes(8)), $extension);
        $destination = $absoluteDir . DIRECTORY_SEPARATOR . $filename;
        if (!move_uploaded_file((string) $file['tmp_name'], $destination)) {
            throw new \RuntimeException('Не удалось сохранить загруженный файл.');
        }
        return $relativeDir . '/' . $filename;
    }
    public function delete(?string $relativePath): void
    {
        if (!$relativePath) {
            return;
        }
        $absolutePath = $this->basePath . '/' . ltrim($relativePath, '/');
        if (is_file($absolutePath)) {
            @unlink($absolutePath);
        }
    }
}