<?php
// Writes to activity_logs. A logging problem never breaks the real request.
class Logger
{
    public static function log(string $action, ?string $table = null, ?int $recordId = null, ?string $details = null): void
    {
        try {
            Database::query(
                'INSERT INTO activity_logs (user_id, action, table_name, record_id, details, ip_address)
                 VALUES (:u, :a, :t, :r, :d, :ip)',
                [
                    ':u'  => Auth::user()['id'] ?? null,
                    ':a'  => $action,
                    ':t'  => $table,
                    ':r'  => $recordId,
                    ':d'  => $details,
                    ':ip' => substr($_SERVER['REMOTE_ADDR'] ?? '', 0, 45),
                ]
            );
        } catch (Throwable $e) {
            error_log('Logger: ' . $e->getMessage());
        }
    }
}