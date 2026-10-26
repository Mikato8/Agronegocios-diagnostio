<?php

declare(strict_types=1);

namespace App\Core;

final class Upload
{
    private const IMAGE_MIMES = [
        'image/jpeg' => 'jpg',
        'image/png' => 'png',
        'image/webp' => 'webp',
    ];

    private const DOCUMENT_MIMES = [
        'application/pdf' => 'pdf',
        'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet' => 'xlsx',
        'application/vnd.ms-excel' => 'xls',
        'application/vnd.openxmlformats-officedocument.wordprocessingml.document' => 'docx',
        'text/csv' => 'csv',
        'text/plain' => 'csv',
    ];

    /**
     * @param array<string, mixed> $file
     * @return array{path: string, url: string, mime: string, size: int, width: int, height: int}
     */
    public static function image(array $file, string $folder, ?string $root = null, ?string $urlBase = null, int $maxDimension = 2400): array
    {
        self::assertUploaded($file);
        $size = (int) ($file['size'] ?? 0);
        $max = (int) config('app.uploads.max_bytes', 8388608);
        if ($size <= 0 || $size > $max) {
            throw new \RuntimeException('Cada imagen debe pesar menos de ' . round($max / 1048576) . ' MB.');
        }

        $tmp = (string) $file['tmp_name'];
        $finfo = new \finfo(FILEINFO_MIME_TYPE);
        $mime = (string) $finfo->file($tmp);
        if (!isset(self::IMAGE_MIMES[$mime])) {
            throw new \RuntimeException('Formato no permitido. Usa JPG, PNG o WebP.');
        }

        $info = @getimagesize($tmp);
        if ($info === false || $info[0] < 20 || $info[1] < 20) {
            throw new \RuntimeException('La imagen no es válida o es demasiado pequeña.');
        }
        if ($info[0] > 9000 || $info[1] > 9000) {
            throw new \RuntimeException('La imagen excede las dimensiones permitidas.');
        }

        $extension = self::IMAGE_MIMES[$mime];
        $folder = trim($folder, '/');
        $root = $root ?? (string) config('app.uploads.images_path');
        $urlBase = $urlBase ?? (string) config('app.uploads.images_url');
        $directory = rtrim($root, '/') . '/' . $folder;
        self::ensureDirectory($directory);

        $filename = Str::filename($extension);
        $destination = $directory . '/' . $filename;
        if (!self::reencode($tmp, $destination, $mime, $maxDimension) && !@move_uploaded_file($tmp, $destination)) {
            throw new \RuntimeException('No fue posible guardar la imagen.');
        }

        @chmod($destination, 0644);
        $finalSize = (int) (@filesize($destination) ?: $size);
        $finalInfo = @getimagesize($destination) ?: $info;

        return [
            'path' => $folder . '/' . $filename,
            'url' => rtrim($urlBase, '/') . '/' . $folder . '/' . $filename,
            'mime' => $mime,
            'size' => $finalSize,
            'width' => (int) $finalInfo[0],
            'height' => (int) $finalInfo[1],
        ];
    }

    /**
     * @param array<string, mixed> $file
     * @return array{path: string, url: string, mime: string, size: int, original_name: string}
     */
    public static function document(array $file, string $folder = 'general'): array
    {
        self::assertUploaded($file);
        $size = (int) ($file['size'] ?? 0);
        $max = (int) config('app.uploads.max_document_bytes', 12582912);
        if ($size <= 0 || $size > $max) {
            throw new \RuntimeException('Cada archivo debe pesar menos de ' . round($max / 1048576) . ' MB.');
        }

        $tmp = (string) $file['tmp_name'];
        $finfo = new \finfo(FILEINFO_MIME_TYPE);
        $mime = (string) $finfo->file($tmp);
        $original = trim((string) ($file['name'] ?? 'archivo'));
        $originalExtension = mb_strtolower(pathinfo($original, PATHINFO_EXTENSION));

        if (!isset(self::DOCUMENT_MIMES[$mime])) {
            throw new \RuntimeException('Formato no permitido. Usa PDF, XLSX, XLS, CSV o DOCX.');
        }
        $extension = self::DOCUMENT_MIMES[$mime];
        if ($extension === 'csv' && !in_array($originalExtension, ['csv', 'txt'], true)) {
            throw new \RuntimeException('El archivo CSV no tiene una extensión válida.');
        }

        $folder = trim($folder, '/');
        $directory = rtrim((string) config('app.uploads.files_path'), '/') . '/' . $folder;
        self::ensureDirectory($directory);

        $filename = Str::filename($extension);
        $destination = $directory . '/' . $filename;
        if (!@move_uploaded_file($tmp, $destination)) {
            throw new \RuntimeException('No fue posible guardar el archivo.');
        }
        @chmod($destination, 0644);

        return [
            'path' => $folder . '/' . $filename,
            'url' => rtrim((string) config('app.uploads.files_url'), '/') . '/' . $folder . '/' . $filename,
            'mime' => $mime,
            'size' => $size,
            'original_name' => mb_substr($original, 0, 180),
        ];
    }

    /**
     * Constancia de estudios: se guarda fuera del árbol público y sólo la
     * consulta el equipo editorial desde el panel.
     *
     * @param array<string, mixed> $file
     */
    public static function proof(array $file, int $userId): string
    {
        self::assertUploaded($file);
        $size = (int) ($file['size'] ?? 0);
        if ($size <= 0 || $size > 8388608) {
            throw new \RuntimeException('La constancia debe pesar menos de 8 MB.');
        }
        $tmp = (string) $file['tmp_name'];
        $finfo = new \finfo(FILEINFO_MIME_TYPE);
        $mime = (string) $finfo->file($tmp);
        $allowed = ['application/pdf' => 'pdf', 'image/jpeg' => 'jpg', 'image/png' => 'png'];
        if (!isset($allowed[$mime])) {
            throw new \RuntimeException('La constancia debe ser PDF, JPG o PNG.');
        }

        $directory = rtrim((string) config('app.uploads.proofs_path'), '/') . '/' . $userId;
        self::ensureDirectory($directory);
        $filename = Str::filename($allowed[$mime]);
        $destination = $directory . '/' . $filename;
        if (!@move_uploaded_file($tmp, $destination)) {
            throw new \RuntimeException('No fue posible guardar la constancia.');
        }
        @chmod($destination, 0640);

        return $userId . '/' . $filename;
    }

    /** @param array<string, mixed> $file */
    private static function assertUploaded(array $file): void
    {
        $error = (int) ($file['error'] ?? UPLOAD_ERR_NO_FILE);
        if ($error !== UPLOAD_ERR_OK) {
            throw new \RuntimeException(self::errorMessage($error));
        }
        $tmp = (string) ($file['tmp_name'] ?? '');
        if ($tmp === '' || !is_uploaded_file($tmp)) {
            throw new \RuntimeException('No se recibió un archivo válido.');
        }
    }

    private static function ensureDirectory(string $directory): void
    {
        if (!is_dir($directory) && !@mkdir($directory, 0775, true) && !is_dir($directory)) {
            throw new \RuntimeException('No fue posible crear el directorio de archivos.');
        }
    }

    private static function reencode(string $source, string $destination, string $mime, int $maxDimension = 2400): bool
    {
        if (!function_exists('imagecreatefromjpeg')) {
            return false;
        }

        $image = match ($mime) {
            'image/jpeg' => @imagecreatefromjpeg($source),
            'image/png' => @imagecreatefrompng($source),
            'image/webp' => function_exists('imagecreatefromwebp') ? @imagecreatefromwebp($source) : false,
            default => false,
        };
        if (!$image) {
            return false;
        }

        $width = imagesx($image);
        $height = imagesy($image);
        if ($width > $maxDimension || $height > $maxDimension) {
            $ratio = min($maxDimension / $width, $maxDimension / $height);
            $newWidth = max(1, (int) floor($width * $ratio));
            $newHeight = max(1, (int) floor($height * $ratio));
            $resized = imagecreatetruecolor($newWidth, $newHeight);
            if ($mime === 'image/png' || $mime === 'image/webp') {
                imagealphablending($resized, false);
                imagesavealpha($resized, true);
                $transparent = imagecolorallocatealpha($resized, 0, 0, 0, 127);
                imagefilledrectangle($resized, 0, 0, $newWidth, $newHeight, $transparent);
            }
            imagecopyresampled($resized, $image, 0, 0, 0, 0, $newWidth, $newHeight, $width, $height);
            $image = $resized;
        }

        $ok = match ($mime) {
            'image/jpeg' => imagejpeg($image, $destination, 85),
            'image/png' => imagepng($image, $destination, 6),
            'image/webp' => function_exists('imagewebp') ? imagewebp($image, $destination, 85) : false,
            default => false,
        };
        return (bool) $ok;
    }

    private static function errorMessage(int $error): string
    {
        return match ($error) {
            UPLOAD_ERR_INI_SIZE, UPLOAD_ERR_FORM_SIZE => 'El archivo excede el tamaño máximo permitido.',
            UPLOAD_ERR_PARTIAL => 'El archivo se subió de forma incompleta. Intenta de nuevo.',
            UPLOAD_ERR_NO_FILE => 'No seleccionaste ningún archivo.',
            UPLOAD_ERR_NO_TMP_DIR, UPLOAD_ERR_CANT_WRITE => 'El servidor no pudo guardar el archivo.',
            default => 'No fue posible procesar el archivo.',
        };
    }
}
