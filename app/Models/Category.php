<?php
class Category
{
    public static function list(?string $like, ?string $status, string $order, int $per, int $off): array
    {
        $where = ['1=1'];
        $p = [];
        if ($like)   { $where[] = '(c.category_name LIKE :q1 OR c.description LIKE :q2)'; $p[':q1'] = $like; $p[':q2'] = $like; }
        if ($status) { $where[] = 'c.status = :st'; $p[':st'] = $status; }
        $w = implode(' AND ', $where);

        $total = (int)Database::query("SELECT COUNT(*) FROM vehicle_categories c WHERE $w", $p)->fetchColumn();
        $items = Database::query(
            "SELECT c.*, (SELECT COUNT(*) FROM vehicles v WHERE v.category_id = c.category_id) AS vehicle_count
             FROM vehicle_categories c WHERE $w ORDER BY $order LIMIT :lim OFFSET :off",
            $p + [':lim' => $per, ':off' => $off]
        )->fetchAll();
        return [$items, $total];
    }

    public static function activeList(): array
    {
        return Database::query("SELECT category_id, category_name FROM vehicle_categories WHERE status = 'Active' ORDER BY category_name")->fetchAll();
    }

    public static function find(int $id): ?array
    {
        return Database::query('SELECT * FROM vehicle_categories WHERE category_id = :id', [':id' => $id])->fetch() ?: null;
    }

    public static function nameExists(string $name, ?int $ignoreId): bool
    {
        return (bool)Database::query(
            'SELECT 1 FROM vehicle_categories WHERE category_name = :n AND category_id <> :id LIMIT 1',
            [':n' => $name, ':id' => $ignoreId ?? 0]
        )->fetchColumn();
    }

    public static function create(array $d): int
    {
        Database::query('INSERT INTO vehicle_categories (category_name, description, status) VALUES (:n, :d, :s)',
            [':n' => $d['category_name'], ':d' => $d['description'], ':s' => $d['status']]);
        return Database::lastId();
    }

    public static function update(int $id, array $d): void
    {
        Database::query('UPDATE vehicle_categories SET category_name = :n, description = :d, status = :s WHERE category_id = :id',
            [':n' => $d['category_name'], ':d' => $d['description'], ':s' => $d['status'], ':id' => $id]);
    }

    public static function usage(int $id): int
    {
        return (int)Database::query('SELECT COUNT(*) FROM vehicles WHERE category_id = :id', [':id' => $id])->fetchColumn();
    }

    public static function delete(int $id): void
    {
        Database::query('DELETE FROM vehicle_categories WHERE category_id = :id', [':id' => $id]);
    }
}