<?php
// Secure image uploads: real MIME check, size limit, random names, no executables.
class Upload
{
    const MAX_BYTES = 5242880; // 5 MB
    const TYPES = ['image/jpeg' => 'jpg', 'image/png' => 'png', 'image/webp' => 'webp'];

    // Turns $_FILES['images'] (single or multiple) into a simple list of files.
    public static function files(string $field): array
    {
        if (empty($_FILES[$field])) {
            return [];
        }
        $f = $_FILES[$field];
        $list = [];
        if (is_array($f['name'])) {
            foreach ($f['name'] as $i => $name) {
                $list[] = ['name' => $name, 'tmp_name' => $f['tmp_name'][$i], 'error' => $f['error'][$i], 'size' => $f['size'][$i]];
            }
        } else {
            $list[] = $f;
        }
        return array_values(array_filter($list, fn($x) => $x['error'] !== UPLOAD_ERR_NO_FILE));
    }

    // Returns the file extension if the upload is a valid image, otherwise throws.
    public static function check(array $file): string
    {
        $fail = fn(string $m) => throw new ApiException($m, 422, ['images' => $m]);
        if ($file['error'] !== UPLOAD_ERR_OK) { $fail('Upload failed (code ' . $file['error'] . ').'); }
        if ($file['size'] > self::MAX_BYTES)  { $fail('Each image must be 5 MB or smaller.'); }
        if (!is_uploaded_file($file['tmp_name'])) { $fail('Invalid upload.'); }
        $mime = (new finfo(FILEINFO_MIME_TYPE))->file($file['tmp_name']);
        if (!isset(self::TYPES[$mime]) || @getimagesize($file['tmp_name']) === false) {
            $fail('Only JPEG, PNG and WebP images are allowed.');
        }
        return self::TYPES[$mime];
    }

    // Saves the file and returns its path relative to /public (e.g. images/vehicles/ab12.jpg)
    public static function saveImage(array $file): string
    {
        $ext  = self::check($file);
        $name = bin2hex(random_bytes(16)) . '.' . $ext;   // original name is never used
        if (!is_dir(UPLOAD_DIR) && !mkdir(UPLOAD_DIR, 0755, true)) {
            throw new ApiException('Cannot create the upload folder.', 500);
        }
        if (!move_uploaded_file($file['tmp_name'], UPLOAD_DIR . $name)) {
            throw new ApiException('Could not save the uploaded file.', 500);
        }
        return 'images/vehicles/' . $name;
    }

    public static function deleteFile(?string $relative): void
    {
        if (!$relative) { return; }
        $full = realpath(UPLOAD_DIR . basename($relative));  // basename blocks ../ tricks
        $root = realpath(UPLOAD_DIR);
        if ($full && $root && str_starts_with($full, $root) && is_file($full)) {
            @unlink($full);
        }
    }

    // Browser URL for a stored path, with a fallback picture.
    public static function url(?string $relative): string
    {
        return BASE_URL . '/' . ($relative ?: 'images/no-car.svg');
    }
}