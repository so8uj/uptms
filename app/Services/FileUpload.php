<?php

namespace App\Services;

/**
 * Saves uploaded files safely. One class for every upload in the project (photos now, documents later).
 *
 *   $result = FileUpload::save($_FILES['photo'], 'photos', 'image');
 *
 *   if ($result['success']) {
 *       $path = $result['path'];      // for example photos/2026/9f2c...a1.jpg  -> store this in the database
 *   } else {
 *       $message = $result['error'];  // show it under the field
 *   }
 *
 * Later, to show the file:  url('uploads/' . $path)
 */
class FileUpload
{
    // What each type of upload allows. To allow a new kind of file, add a line here.
    private const TYPES = [
        'image' => [
            'extensions' => ['jpg', 'jpeg', 'png', 'webp'],
            'mimes'      => ['image/jpeg' => 'jpg', 'image/png' => 'png', 'image/webp' => 'webp'],
            'max_mb'     => 2,
        ],
        'document' => [
            'extensions' => ['pdf'],
            'mimes'      => ['application/pdf' => 'pdf'],
            'max_mb'     => 5,
        ],
    ];

    private const UPLOAD_ERRORS = [
        UPLOAD_ERR_INI_SIZE   => 'The file is too large.',
        UPLOAD_ERR_FORM_SIZE  => 'The file is too large.',
        UPLOAD_ERR_PARTIAL    => 'The file was only partly uploaded. Please try again.',
        UPLOAD_ERR_NO_TMP_DIR => 'The server has no temporary folder for uploads.',
        UPLOAD_ERR_CANT_WRITE => 'The server could not save the file.',
        UPLOAD_ERR_EXTENSION  => 'The upload was stopped by the server.',
    ];

    /**
     * $file   one entry of $_FILES, for example $_FILES['photo']
     * $folder where to keep it inside /uploads, for example 'photos'
     * $type   'image' or 'document'
     * Returns ['success' => bool, 'path' => relative path or null, 'error' => message or null]
     */
    public static function save(array $file, string $folder, string $type = 'image'): array
    {
        if (!isset(self::TYPES[$type]) || !preg_match('/^[a-z_]+$/', $folder)) {
            throw new \InvalidArgumentException('Unknown upload type or folder.');
        }
        $rules = self::TYPES[$type];

        // Step 1: did the upload itself work?
        $code = $file['error'] ?? UPLOAD_ERR_NO_FILE;
        if ($code !== UPLOAD_ERR_OK) {
            return self::fail(self::UPLOAD_ERRORS[$code] ?? 'The file could not be uploaded.');
        }

        // Step 2: is it small enough? (PHP's own limit on this server is very high, so we check too)
        if ($file['size'] > $rules['max_mb'] * 1024 * 1024) {
            return self::fail('The file must be smaller than ' . $rules['max_mb'] . ' MB.');
        }

        // Step 3: is the extension allowed? (the name comes from the user, so this alone is not enough)
        $extension = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
        if (!in_array($extension, $rules['extensions'], true)) {
            return self::fail('Allowed file types: ' . strtoupper(implode(', ', $rules['extensions'])) . '.');
        }

        // Step 4: what is the file really? Read it from the file content, not from the name or the browser.
        $mime = (new \finfo(FILEINFO_MIME_TYPE))->file($file['tmp_name']);
        if (!isset($rules['mimes'][$mime])) {
            return self::fail('This file is not a valid ' . $type . '.');
        }

        // Step 5: an image must open as an image (stops a script renamed to .jpg)
        if ($type === 'image' && @getimagesize($file['tmp_name']) === false) {
            return self::fail('This file is not a valid image.');
        }

        // Step 6: save it under a random name, never under the name the user gave it.
        $relativeFolder = $folder . '/' . date('Y');
        $directory = self::uploadsPath() . '/' . $relativeFolder;

        if (!is_dir($directory) && !mkdir($directory, 0755, true)) {
            return self::fail('The server could not create the upload folder.');
        }

        // The saved extension comes from the real file type, so a PNG named photo.jpg is saved as .png
        $name = bin2hex(random_bytes(16)) . '.' . $rules['mimes'][$mime];

        if (!move_uploaded_file($file['tmp_name'], $directory . '/' . $name)) {
            return self::fail('The server could not save the file.');
        }

        return ['success' => true, 'path' => $relativeFolder . '/' . $name, 'error' => null];
    }

    /**
     * Deletes a saved file (for example the old photo after a new one was uploaded).
     * $path is the value stored in the database. Only files inside /uploads can be deleted.
     */
    public static function delete(?string $path): bool
    {
        if ($path === null || $path === '') {
            return false;
        }

        $root = realpath(self::uploadsPath());
        $file = realpath(self::uploadsPath() . '/' . $path);

        // realpath() resolves things like ../ so a tampered path cannot leave the uploads folder.
        if ($root === false || $file === false || !str_starts_with($file, $root . DIRECTORY_SEPARATOR) || !is_file($file)) {
            return false;
        }

        return unlink($file);
    }

    private static function uploadsPath(): string
    {
        return base_path('uploads');
    }

    private static function fail(string $message): array
    {
        return ['success' => false, 'path' => null, 'error' => $message];
    }
}
