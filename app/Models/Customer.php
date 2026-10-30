<?php
class Customer
{
    const SORT = ['id' => 'c.customer_id', 'name' => 'c.full_name', 'phone' => 'c.phone', 'created' => 'c.created_at', 'status' => 'c.status'];

    public static function list(array $f, string $order, int $per, int $off): array
    {
        $w = ['1=1']; $p = [];
        if (!empty($f['like'])) {
            $w[] = '(c.full_name LIKE :q1 OR c.phone LIKE :q2 OR c.email LIKE :q3 OR c.license_number LIKE :q4 OR c.customer_id = :qid)';
            foreach (['q1', 'q2', 'q3', 'q4'] as $k) { $p[":$k"] = $f['like']; }
            $p[':qid'] = ctype_digit($f['raw']) ? (int)$f['raw'] : 0;
        }
        if (!empty($f['status'])) { $w[] = 'c.status = :st'; $p[':st'] = $f['status']; }
        if ($f['blacklisted'] !== '') { $w[] = 'c.is_blacklisted = :bl'; $p[':bl'] = (int)$f['blacklisted']; }
        $where = implode(' AND ', $w);
        $total = (int)Database::query("SELECT COUNT(*) FROM customers c WHERE $where", $p)->fetchColumn();
        $items = Database::query(
            "SELECT c.*, (SELECT COUNT(*) FROM reservations r WHERE r.customer_id = c.customer_id) AS reservation_count
             FROM customers c WHERE $where ORDER BY $order LIMIT :lim OFFSET :off",
            $p + [':lim' => $per, ':off' => $off]
        )->fetchAll();
        return [$items, $total];
    }

    public static function find(int $id): ?array
    {
        return Database::query('SELECT * FROM customers WHERE customer_id = :id', [':id' => $id])->fetch() ?: null;
    }

    public static function history(int $id): array
    {
        return Database::query(
            "SELECT r.reservation_id, r.pickup_datetime, r.return_datetime, r.estimated_total, r.status,
                    CONCAT_WS(' ', v.brand, v.model) AS vehicle, v.license_plate
             FROM reservations r JOIN vehicles v ON v.vehicle_id = r.vehicle_id
             WHERE r.customer_id = :id ORDER BY r.pickup_datetime DESC LIMIT 50",
            [':id' => $id]
        )->fetchAll();
    }

    // Returns the first duplicate field found: phone, email or license_number.
    public static function duplicate(array $d, ?int $ignoreId): ?array
    {
        foreach (['phone' => 'Phone number', 'email' => 'Email', 'license_number' => 'License number'] as $col => $label) {
            if (!empty($d[$col])) {
                $hit = Database::query("SELECT 1 FROM customers WHERE $col = :v AND customer_id <> :id LIMIT 1", [':v' => $d[$col], ':id' => $ignoreId ?? 0])->fetchColumn();
                if ($hit) { return [$col => "$label is already used by another customer."]; }
            }
        }
        return null;
    }

    const FIELDS = ['full_name', 'gender', 'phone', 'email', 'address', 'license_number', 'license_expiry', 'is_blacklisted', 'blacklist_reason', 'status'];

    private static function params(array $d): array
    {
        $p = [];
        foreach (self::FIELDS as $f) { $p[":$f"] = $d[$f] ?? null; }
        return $p;
    }

    public static function insert(array $d, int $uid): int
    {
        $cols = implode(',', self::FIELDS) . ', created_by';
        $ph   = ':' . implode(',:', self::FIELDS) . ', :created_by';
        Database::query("INSERT INTO customers ($cols) VALUES ($ph)", self::params($d) + [':created_by' => $uid]);
        return Database::lastId();
    }

    public static function update(int $id, array $d): void
    {
        $set = implode(',', array_map(fn($f) => "$f = :$f", self::FIELDS));
        Database::query("UPDATE customers SET $set WHERE customer_id = :id", self::params($d) + [':id' => $id]);
    }

    public static function hasHistory(int $id): bool
    {
        return (bool)Database::query(
            'SELECT (SELECT COUNT(*) FROM reservations WHERE customer_id = :a) + (SELECT COUNT(*) FROM rentals WHERE customer_id = :b)',
            [':a' => $id, ':b' => $id]
        )->fetchColumn();
    }

    public static function setInactive(int $id): void
    {
        Database::query("UPDATE customers SET status = 'Inactive' WHERE customer_id = :id", [':id' => $id]);
    }

    public static function delete(int $id): void
    {
        Database::query('DELETE FROM customers WHERE customer_id = :id', [':id' => $id]);
    }
}