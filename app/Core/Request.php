<?php
class Request
{
    // Browsers/forms can send POST with ?_method=PUT or DELETE. Real PUT/DELETE also work.
    public static function method(): string
    {
        $m = strtoupper($_SERVER['REQUEST_METHOD'] ?? 'GET');
        if ($m === 'POST') {
            $o = strtoupper((string)($_GET['_method'] ?? ''));
            if (in_array($o, ['PUT', 'PATCH', 'DELETE'], true)) {
                return $o;
            }
        }
        return $m;
    }

    public static function json(): array
    {
        $raw = file_get_contents('php://input');
        if ($raw === '' || $raw === false) {
            return [];
        }
        $data = json_decode($raw, true);
        if (!is_array($data)) {
            Response::error('Invalid JSON body.', 400);
        }
        return $data;
    }

    // JSON body or multipart/form fields, whichever the client sent.
    public static function input(): array
    {
        $type = $_SERVER['CONTENT_TYPE'] ?? '';
        return stripos($type, 'application/json') !== false ? self::json() : $_POST;
    }

    public static function id(): int
    {
        return (int)($_GET['id'] ?? 0);
    }

    public static function get(string $key): string
    {
        $v = $_GET[$key] ?? '';
        return is_string($v) ? trim($v) : '';
    }

    // ?q=abc becomes "%abc%" with LIKE wildcards in the user text escaped.
    public static function like(string $key = 'q'): ?string
    {
        $q = self::get($key);
        return $q === '' ? null : '%' . addcslashes($q, '%_\\') . '%';
    }

    // returns [page, perPage, offset]
    public static function paging(int $default = 10): array
    {
        $page = max(1, (int)($_GET['page'] ?? 1));
        $per  = min(100, max(1, (int)($_GET['per_page'] ?? $default)));
        return [$page, $per, ($page - 1) * $per];
    }
}