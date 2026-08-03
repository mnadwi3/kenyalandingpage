<?php

declare(strict_types=1);

namespace App\Helpers;

use RuntimeException;

final class ImageUploader
{
    private const ALLOWED = [
        'image/jpeg' => 'jpg',
        'image/png'  => 'png',
        'image/webp' => 'webp',
        'image/gif'  => 'gif',
    ];

    private const ALLOWED_DIRECTORIES = ['doctors', 'hospitals', 'treatments'];

    /** @param array<string, mixed> $file */
    public function upload(array $file, string $directory, ?string $oldRelativePath = null): string
    {
        $directory = $this->assertDirectory($directory);

        if (($file['error'] ?? UPLOAD_ERR_NO_FILE) === UPLOAD_ERR_NO_FILE) {
            throw new RuntimeException('No file uploaded.');
        }
        if (($file['error'] ?? UPLOAD_ERR_OK) !== UPLOAD_ERR_OK) {
            throw new RuntimeException('Upload failed.');
        }

        $tmp = (string) ($file['tmp_name'] ?? '');
        if ($tmp === '' || !is_uploaded_file($tmp)) {
            throw new RuntimeException('Invalid upload.');
        }

        $finfo = new \finfo(FILEINFO_MIME_TYPE);
        $mime = $finfo->file($tmp) ?: '';
        if (!isset(self::ALLOWED[$mime])) {
            throw new RuntimeException('Only JPG, PNG, WEBP, or GIF images are allowed.');
        }
        if (@getimagesize($tmp) === false) {
            throw new RuntimeException('Uploaded file is not a valid image.');
        }
        if (($file['size'] ?? 0) > 5 * 1024 * 1024) {
            throw new RuntimeException('Image must be under 5MB.');
        }

        $ext = self::ALLOWED[$mime];
        $filename = bin2hex(random_bytes(16)) . '.' . $ext;
        $relativeDir = 'uploads/' . $directory;
        $absoluteDir = BASE_PATH . '/public/' . $relativeDir;

        if (!is_dir($absoluteDir) && !mkdir($absoluteDir, 0755, true) && !is_dir($absoluteDir)) {
            throw new RuntimeException('Unable to create upload directory.');
        }

        $absolutePath = $absoluteDir . '/' . $filename;
        $relativePath = $relativeDir . '/' . $filename;

        if (!$this->compressAndSave($tmp, $absolutePath, $mime)) {
            throw new RuntimeException('Failed to process uploaded image.');
        }

        if ($oldRelativePath) {
            $this->delete($oldRelativePath);
        }

        return $relativePath;
    }

    public function delete(?string $relativePath): void
    {
        $absolute = $this->resolveSafeUploadPath($relativePath);
        if ($absolute !== null && is_file($absolute)) {
            @unlink($absolute);
        }
    }

    public function copy(string $relativePath, string $directory): ?string
    {
        $directory = $this->assertDirectory($directory);
        $source = $this->resolveSafeUploadPath($relativePath);
        if ($source === null || !is_file($source)) {
            return null;
        }

        $ext = strtolower(pathinfo($source, PATHINFO_EXTENSION) ?: 'jpg');
        if (!in_array($ext, array_values(self::ALLOWED), true)) {
            return null;
        }
        $filename = bin2hex(random_bytes(16)) . '.' . $ext;
        $relativeDir = 'uploads/' . $directory;
        $absoluteDir = BASE_PATH . '/public/' . $relativeDir;
        if (!is_dir($absoluteDir)) {
            mkdir($absoluteDir, 0755, true);
        }
        $destRelative = $relativeDir . '/' . $filename;
        $destAbsolute = BASE_PATH . '/public/' . $destRelative;

        return copy($source, $destAbsolute) ? $destRelative : null;
    }

    private function assertDirectory(string $directory): string
    {
        $directory = trim($directory, '/');
        if (!in_array($directory, self::ALLOWED_DIRECTORIES, true)) {
            throw new RuntimeException('Invalid upload directory.');
        }

        return $directory;
    }

    private function resolveSafeUploadPath(?string $relativePath): ?string
    {
        if ($relativePath === null || $relativePath === '') {
            return null;
        }
        $relativePath = str_replace('\\', '/', $relativePath);
        if (str_contains($relativePath, "\0") || str_contains($relativePath, '..')) {
            return null;
        }
        if (!str_starts_with($relativePath, 'uploads/')) {
            return null;
        }

        $uploadsRoot = realpath(BASE_PATH . '/public/uploads');
        if ($uploadsRoot === false) {
            return null;
        }

        $absolute = BASE_PATH . '/public/' . ltrim($relativePath, '/');
        $real = realpath($absolute);
        if ($real === false) {
            // File may not exist yet for delete no-ops; still reject escape attempts by prefix check.
            $normalized = str_replace('\\', '/', $absolute);
            $rootNormalized = str_replace('\\', '/', $uploadsRoot);
            if (!str_starts_with($normalized, $rootNormalized . '/') && $normalized !== $rootNormalized) {
                return null;
            }

            return $absolute;
        }

        if (!str_starts_with($real, $uploadsRoot . DIRECTORY_SEPARATOR) && $real !== $uploadsRoot) {
            return null;
        }

        return $real;
    }

    private function compressAndSave(string $tmp, string $destination, string $mime): bool
    {
        if (!extension_loaded('gd')) {
            return false;
        }

        $image = match ($mime) {
            'image/jpeg' => @imagecreatefromjpeg($tmp),
            'image/png'  => @imagecreatefrompng($tmp),
            'image/webp' => function_exists('imagecreatefromwebp') ? @imagecreatefromwebp($tmp) : false,
            'image/gif'  => @imagecreatefromgif($tmp),
            default      => false,
        };
        if ($image === false) {
            return false;
        }

        $width = imagesx($image);
        $height = imagesy($image);
        $max = 1200;
        if ($width > $max || $height > $max) {
            $ratio = min($max / $width, $max / $height);
            $newW = (int) max(1, floor($width * $ratio));
            $newH = (int) max(1, floor($height * $ratio));
            $resized = imagecreatetruecolor($newW, $newH);
            if ($mime === 'image/png' || $mime === 'image/webp') {
                imagealphablending($resized, false);
                imagesavealpha($resized, true);
            }
            imagecopyresampled($resized, $image, 0, 0, 0, 0, $newW, $newH, $width, $height);
            imagedestroy($image);
            $image = $resized;
        }

        $ok = match ($mime) {
            'image/jpeg' => imagejpeg($image, $destination, 82),
            'image/png'  => imagepng($image, $destination, 6),
            'image/webp' => function_exists('imagewebp') ? imagewebp($image, $destination, 82) : false,
            'image/gif'  => imagegif($image, $destination),
            default      => false,
        };
        imagedestroy($image);

        return (bool) $ok;
    }
}
