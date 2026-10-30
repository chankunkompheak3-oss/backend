<?php
class VehicleImage
{
    public static function forVehicle(int $vid): array
    {
        $rows = Database::query('SELECT * FROM vehicle_images WHERE vehicle_id = :v ORDER BY is_primary DESC, sort_order, image_id', [':v' => $vid])->fetchAll();
        foreach ($rows as &$r) { $r['url'] = Upload::url($r['image_path']); }
        return $rows;
    }

    public static function add(int $vid, string $path, int $sort): void
    {
        Database::query('INSERT INTO vehicle_images (vehicle_id, image_path, is_primary, sort_order) VALUES (:v, :p, 0, :s)', [':v' => $vid, ':p' => $path, ':s' => $sort]);
    }

    public static function count(int $vid): int
    {
        return (int)Database::query('SELECT COUNT(*) FROM vehicle_images WHERE vehicle_id = :v', [':v' => $vid])->fetchColumn();
    }

    // Deletes the rows that belong to this vehicle and returns their file paths.
    public static function removeIds(int $vid, array $ids): array
    {
        $paths = [];
        foreach ($ids as $id) {
            $id = (int)$id;
            $path = Database::query('SELECT image_path FROM vehicle_images WHERE image_id = :i AND vehicle_id = :v', [':i' => $id, ':v' => $vid])->fetchColumn();
            if ($path !== false) {
                Database::query('DELETE FROM vehicle_images WHERE image_id = :i', [':i' => $id]);
                $paths[] = $path;
            }
        }
        return $paths;
    }

    public static function paths(int $vid): array
    {
        return Database::query('SELECT image_path FROM vehicle_images WHERE vehicle_id = :v', [':v' => $vid])->fetchAll(PDO::FETCH_COLUMN);
    }

    // Makes sure exactly one image is primary (the preferred one if valid, else the first).
    public static function ensurePrimary(int $vid, ?int $preferred): void
    {
        if ($preferred) {
            $ok = Database::query('SELECT 1 FROM vehicle_images WHERE image_id = :i AND vehicle_id = :v', [':i' => $preferred, ':v' => $vid])->fetchColumn();
            if ($ok) {
                Database::query('UPDATE vehicle_images SET is_primary = (image_id = :i) WHERE vehicle_id = :v', [':i' => $preferred, ':v' => $vid]);
                return;
            }
        }
        $n = (int)Database::query('SELECT COUNT(*) FROM vehicle_images WHERE vehicle_id = :v AND is_primary = 1', [':v' => $vid])->fetchColumn();
        if ($n !== 1 && self::count($vid) > 0) {
            Database::query('UPDATE vehicle_images SET is_primary = 0 WHERE vehicle_id = :v', [':v' => $vid]);
            Database::query(
                'UPDATE vehicle_images SET is_primary = 1 WHERE image_id =
                 (SELECT image_id FROM (SELECT image_id FROM vehicle_images WHERE vehicle_id = :v ORDER BY sort_order, image_id LIMIT 1) t)',
                [':v' => $vid]
            );
        }
    }
}