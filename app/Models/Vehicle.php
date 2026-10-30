<?php
class Vehicle
{
    const SORT = [
        'brand' => 'v.brand', 'model' => 'v.model', 'year' => 'v.year', 'plate' => 'v.license_plate',
        'price' => 'v.daily_price', 'mileage' => 'v.mileage_km', 'status' => 'v.status', 'created' => 'v.created_at',
    ];
    const FIELDS = ['category_id', 'brand', 'model', 'year', 'color', 'fuel_type', 'transmission', 'seat_capacity',
        'license_plate', 'daily_price', 'mileage_km', 'next_service_date', 'next_service_km',
        'insurance_expiry', 'registration_expiry', 'status'];

    public static function list(array $f, string $order, int $per, int $off): array
    {
        $w = ['1=1']; $p = [];
        if (!empty($f['like'])) {
            $w[] = '(v.brand LIKE :q1 OR v.model LIKE :q2 OR v.license_plate LIKE :q3 OR v.color LIKE :q4 OR v.year LIKE :q5)';
            foreach (['q1', 'q2', 'q3', 'q4', 'q5'] as $k) { $p[":$k"] = $f['like']; }
        }
        foreach (['category_id' => 'v.category_id', 'fuel_type' => 'v.fuel_type', 'transmission' => 'v.transmission', 'status' => 'v.status'] as $key => $col) {
            if (!empty($f[$key])) { $w[] = "$col = :$key"; $p[":$key"] = $f[$key]; }
        }
        $where = implode(' AND ', $w);
        $total = (int)Database::query("SELECT COUNT(*) FROM vehicles v WHERE $where", $p)->fetchColumn();
        $items = Database::query(
            "SELECT v.*, c.category_name,
                (SELECT image_path FROM vehicle_images i WHERE i.vehicle_id = v.vehicle_id
                 ORDER BY i.is_primary DESC, i.sort_order, i.image_id LIMIT 1) AS primary_image
             FROM vehicles v LEFT JOIN vehicle_categories c ON c.category_id = v.category_id
             WHERE $where ORDER BY $order LIMIT :lim OFFSET :off",
            $p + [':lim' => $per, ':off' => $off]
        )->fetchAll();
        foreach ($items as &$it) { $it['image_url'] = Upload::url($it['primary_image']); }
        return [$items, $total];
    }

    public static function find(int $id): ?array
    {
        $v = Database::query(
            'SELECT v.*, c.category_name FROM vehicles v LEFT JOIN vehicle_categories c ON c.category_id = v.category_id WHERE v.vehicle_id = :id',
            [':id' => $id]
        )->fetch();
        if (!$v) { return null; }
        $v['images'] = VehicleImage::forVehicle($id);
        return $v;
    }

    public static function plateExists(string $plate, ?int $ignoreId): bool
    {
        return (bool)Database::query('SELECT 1 FROM vehicles WHERE license_plate = :p AND vehicle_id <> :id LIMIT 1', [':p' => $plate, ':id' => $ignoreId ?? 0])->fetchColumn();
    }

    public static function categoryExists(int $id): bool
    {
        return (bool)Database::query('SELECT 1 FROM vehicle_categories WHERE category_id = :id', [':id' => $id])->fetchColumn();
    }

    private static function params(array $d): array
    {
        $p = [];
        foreach (self::FIELDS as $f) { $p[":$f"] = $d[$f] ?? null; }
        return $p;
    }

    public static function insert(array $d): int
    {
        $cols = implode(',', self::FIELDS);
        $ph   = ':' . implode(',:', self::FIELDS);
        Database::query("INSERT INTO vehicles ($cols) VALUES ($ph)", self::params($d));
        return Database::lastId();
    }

    public static function update(int $id, array $d): void
    {
        $set = implode(',', array_map(fn($f) => "$f = :$f", self::FIELDS));
        Database::query("UPDATE vehicles SET $set WHERE vehicle_id = :id", self::params($d) + [':id' => $id]);
    }

    public static function dependencies(int $id): int
    {
        return (int)Database::query(
            'SELECT (SELECT COUNT(*) FROM reservations WHERE vehicle_id = :a) + (SELECT COUNT(*) FROM rentals WHERE vehicle_id = :b)
                  + (SELECT COUNT(*) FROM maintenance WHERE vehicle_id = :c)',
            [':a' => $id, ':b' => $id, ':c' => $id]
        )->fetchColumn();
    }

    public static function delete(int $id): void
    {
        Database::query('DELETE FROM vehicles WHERE vehicle_id = :id', [':id' => $id]);
    }
}