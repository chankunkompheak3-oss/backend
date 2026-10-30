<?php
class Staff
{
    const SORT = ['name' => 'e.full_name', 'username' => 'u.username', 'role' => 'u.role', 'status' => 'u.status', 'last_login' => 'u.last_login_at', 'created' => 'u.created_at'];
    const SELECT = "SELECT u.user_id, u.username, u.email, u.phone, u.role, u.status, u.last_login_at, u.created_at,
                           e.employee_id, e.employee_code, e.full_name, e.gender, e.position, e.salary, e.hire_date
                    FROM users u LEFT JOIN employees e ON e.user_id = u.user_id";

    public static function list(array $f, string $order, int $per, int $off): array
    {
        $w = ['1=1']; $p = [];
        if (!empty($f['like'])) {
            $w[] = '(u.username LIKE :q1 OR e.full_name LIKE :q2 OR u.email LIKE :q3 OR u.phone LIKE :q4 OR e.employee_code LIKE :q5)';
            foreach (['q1', 'q2', 'q3', 'q4', 'q5'] as $k) { $p[":$k"] = $f['like']; }
        }
        if (!empty($f['role']))   { $w[] = 'u.role = :role'; $p[':role'] = $f['role']; }
        if (!empty($f['status'])) { $w[] = 'u.status = :st'; $p[':st'] = $f['status']; }
        $where = implode(' AND ', $w);
        $total = (int)Database::query("SELECT COUNT(*) FROM users u LEFT JOIN employees e ON e.user_id = u.user_id WHERE $where", $p)->fetchColumn();
        $items = Database::query(self::SELECT . " WHERE $where ORDER BY $order LIMIT :lim OFFSET :off", $p + [':lim' => $per, ':off' => $off])->fetchAll();
        return [$items, $total];
    }

    public static function find(int $id): ?array
    {
        return Database::query(self::SELECT . ' WHERE u.user_id = :id', [':id' => $id])->fetch() ?: null;
    }

    public static function usernameTaken(string $u, ?int $ignore): bool
    {
        return (bool)Database::query('SELECT 1 FROM users WHERE username = :u AND user_id <> :id', [':u' => $u, ':id' => $ignore ?? 0])->fetchColumn();
    }

    public static function emailTaken(string $e, ?int $ignore): bool
    {
        return (bool)Database::query('SELECT 1 FROM users WHERE email = :e AND user_id <> :id', [':e' => $e, ':id' => $ignore ?? 0])->fetchColumn();
    }

    public static function codeTaken(string $c, ?int $ignoreUser): bool
    {
        return (bool)Database::query('SELECT 1 FROM employees WHERE employee_code = :c AND (user_id <> :id OR user_id IS NULL)', [':c' => $c, ':id' => $ignoreUser ?? 0])->fetchColumn();
    }

    public static function activeAdmins(): int
    {
        return (int)Database::query("SELECT COUNT(*) FROM users WHERE role = 'Admin' AND status = 'Active'")->fetchColumn();
    }

    // Creates the login (users) and the HR record (employees) together.
    public static function create(array $d, string $passwordHash): int
    {
        $pdo = Database::connect();
        $pdo->beginTransaction();
        try {
            Database::query('INSERT INTO users (username, password, email, phone, role, status, must_change_password) VALUES (:u, :p, :e, :ph, :r, :s, 1)',
                [':u' => $d['username'], ':p' => $passwordHash, ':e' => $d['email'], ':ph' => $d['phone'], ':r' => $d['role'], ':s' => $d['status']]);
            $uid = Database::lastId();
            self::saveEmployee($uid, $d, false);
            $pdo->commit();
            return $uid;
        } catch (Throwable $e) {
            if ($pdo->inTransaction()) { $pdo->rollBack(); }
            throw $e;
        }
    }

    public static function update(int $uid, array $d, ?string $passwordHash): void
    {
        $pdo = Database::connect();
        $pdo->beginTransaction();
        try {
            Database::query('UPDATE users SET username = :u, email = :e, phone = :ph, role = :r, status = :s WHERE user_id = :id',
                [':u' => $d['username'], ':e' => $d['email'], ':ph' => $d['phone'], ':r' => $d['role'], ':s' => $d['status'], ':id' => $uid]);
            if ($passwordHash) {   // admin reset: user must pick a new password at next login
                Database::query('UPDATE users SET password = :p, must_change_password = 1, failed_login_attempts = 0, locked_until = NULL WHERE user_id = :id', [':p' => $passwordHash, ':id' => $uid]);
            }
            $exists = Database::query('SELECT 1 FROM employees WHERE user_id = :id', [':id' => $uid])->fetchColumn();
            self::saveEmployee($uid, $d, (bool)$exists);
            $pdo->commit();
        } catch (Throwable $e) {
            if ($pdo->inTransaction()) { $pdo->rollBack(); }
            throw $e;
        }
    }

    private static function saveEmployee(int $uid, array $d, bool $exists): void
    {
        $p = [':uid' => $uid, ':code' => $d['employee_code'], ':name' => $d['full_name'], ':gender' => $d['gender'], ':phone' => $d['phone'],
              ':email' => $d['email'], ':pos' => $d['position'], ':sal' => $d['salary'] === null ? null : number_format($d['salary'], 2, '.', ''), ':hire' => $d['hire_date'], ':st' => $d['status']];
        if ($exists) {
            Database::query('UPDATE employees SET employee_code = :code, full_name = :name, gender = :gender, phone = :phone, email = :email,
                             position = :pos, salary = :sal, hire_date = :hire, status = :st WHERE user_id = :uid', $p);
        } else {
            Database::query('INSERT INTO employees (user_id, employee_code, full_name, gender, phone, email, position, salary, hire_date, status)
                             VALUES (:uid, :code, :name, :gender, :phone, :email, :pos, :sal, :hire, :st)', $p);
        }
    }

    public static function setStatus(int $uid, string $status): void
    {
        Database::query('UPDATE users SET status = :s WHERE user_id = :id', [':s' => $status, ':id' => $uid]);
        Database::query('UPDATE employees SET status = :s WHERE user_id = :id', [':s' => $status, ':id' => $uid]);
    }
}