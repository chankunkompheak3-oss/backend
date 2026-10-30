<?php
class User
{
    public static function findByUsername(string $username): ?array
    {
        return Database::query('SELECT * FROM users WHERE username = :u LIMIT 1', [':u' => $username])->fetch() ?: null;
    }

    public static function findById(int $id): ?array
    {
        return Database::query('SELECT * FROM users WHERE user_id = :id', [':id' => $id])->fetch() ?: null;
    }

    // Counts a failed login; locks the account when the limit is reached.
    // (MySQL evaluates SET left to right, so the second line already sees the new count.)
    public static function registerFailure(int $userId): void
    {
        $max = (int)MAX_LOGIN_ATTEMPTS; $mins = (int)LOCK_MINUTES;
        Database::query(
            "UPDATE users SET failed_login_attempts = failed_login_attempts + 1,
                 locked_until = IF(failed_login_attempts >= $max, NOW() + INTERVAL $mins MINUTE, locked_until)
             WHERE user_id = :id", [':id' => $userId]
        );
    }

    public static function registerSuccess(int $userId): void
    {
        Database::query('UPDATE users SET failed_login_attempts = 0, locked_until = NULL, last_login_at = NOW() WHERE user_id = :id', [':id' => $userId]);
    }

    public static function emailTaken(string $email, int $ignoreId): bool
    {
        return (bool)Database::query('SELECT 1 FROM users WHERE email = :e AND user_id <> :id LIMIT 1', [':e' => $email, ':id' => $ignoreId])->fetchColumn();
    }

    public static function updateProfile(int $id, ?string $email, ?string $phone): void
    {
        Database::query('UPDATE users SET email = :e, phone = :p WHERE user_id = :id', [':e' => $email, ':p' => $phone, ':id' => $id]);
    }

    public static function setPassword(int $id, string $hash, bool $mustChange): void
    {
        Database::query('UPDATE users SET password = :h, must_change_password = :m WHERE user_id = :id', [':h' => $hash, ':m' => (int)$mustChange, ':id' => $id]);
    }
}