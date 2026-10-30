<?php
$_SESSION['csrf'] = bin2hex(random_bytes(32));
$_SESSION['username'] = $dbUser['username'];   // temporary: old pages still read this
class Auth
{
    // ---------- Session state ----------
    //check on account at every requests
    public static function check(): bool
    {
        if (empty($_SESSION['user'])) {
            return false;
        }
        static $verified = false;
        if (!$verified) {
            $stmt = Database::connect()->prepare(
                'SELECT status, role, must_change_password FROM users WHERE user_id = :id'
            );
            $stmt->execute([':id' => $_SESSION['user']['id']]);
            $row = $stmt->fetch();
            if (!$row || $row['status'] !== 'Active') {
                $_SESSION = [];
                return false;
            }
            $_SESSION['user']['role'] = $row['role'];
            $_SESSION['user']['must_change_password'] = (bool)$row['must_change_password'];
            $verified = true;
        }
        return true;
    }

    public static function user(): ?array
    {
        return $_SESSION['user'] ?? null;
    }

    public static function login(array $dbUser): void
    {
        session_regenerate_id(true);   // stops session fixation
        $_SESSION['user'] = [
            'id'       => (int)$dbUser['user_id'],
            'username' => $dbUser['username'],
            'role'     => $dbUser['role'],
            'must_change_password' => (bool)$dbUser['must_change_password'],
        ];
        $_SESSION['csrf'] = bin2hex(random_bytes(32));
    }

    public static function logout(): void
    {
        $_SESSION = [];
        if (ini_get('session.use_cookies')) {
            $p = session_get_cookie_params();
            setcookie(session_name(), '', time() - 3600, $p['path'], $p['domain'], $p['secure'], $p['httponly']);
        }
        session_destroy();
    }

    // ---------- Guards (used at the top of every protected API/page) ----------
    public static function requireLogin(): void
    {
        if (!self::check()) {
            Response::error('Please log in.', 401);
        }
    }

    public static function requireRole(string ...$roles): void
    {
        self::requireLogin();
        if (!in_array($_SESSION['user']['role'], $roles, true)) {
            Response::error('You do not have permission to do this.', 403);
        }
    }

    // For normal HTML pages: send the visitor to the login page.
    public static function requirePage(): void
    {
        if (!self::check()) {
            header('Location: ' . BASE_URL . '/login.php?next=' . urlencode($_SERVER['REQUEST_URI'] ?? ''));
            exit;
        }
    }

    // ---------- CSRF ----------
    public static function csrfToken(): string
    {
        if (empty($_SESSION['csrf'])) {
            $_SESSION['csrf'] = bin2hex(random_bytes(32));
        }
        return $_SESSION['csrf'];
    }

    // Call on every POST/PUT/PATCH/DELETE. The JS sends the token in a header.
    public static function requireCsrf(): void
    {
        $sent = $_SERVER['HTTP_X_CSRF_TOKEN'] ?? '';
        if (!is_string($sent) || $sent === '' || !hash_equals(self::csrfToken(), $sent)) {
            $why = (!is_string($sent) || $sent === '')
                ? 'no token header received'
                : 'token does not match the session';
            Response::error(
                'Invalid or missing security token. Reload the page.' . (APP_DEBUG ? " ($why)" : ''),
                419
            );
        }
    }

    // ---------- Safe redirect after login ----------
    // Only same-site paths inside our app are allowed (no "//evil.com", no full URLs).
    public static function safeRedirect(?string $next): string
    {
        $default = BASE_URL . '/index.php';
        if (!is_string($next) || $next === '') {
            return $default;
        }
        if (!str_starts_with($next, BASE_URL . '/')
            || str_contains($next, '//')
            || str_contains($next, '\\')
            || preg_match('/[\r\n]/', $next)) {
            return $default;
        }
        return $next;
    }
}