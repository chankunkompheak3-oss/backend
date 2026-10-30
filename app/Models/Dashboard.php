<?php
class Dashboard
{
    // "Rented" = status Rented, OR an active rental, OR an approved reservation that covers right now.
    private const RENTED_IDS = "SELECT vehicle_id FROM vehicles WHERE status = 'Rented'
        UNION SELECT vehicle_id FROM rentals WHERE status = 'Active'
        UNION SELECT vehicle_id FROM reservations WHERE status = 'Approved' AND NOW() BETWEEN pickup_datetime AND return_datetime";

    public static function stats(): array
    {
        $rented = self::RENTED_IDS;
        $sql = "SELECT
            (SELECT COUNT(*) FROM vehicles) AS total_vehicles,
            (SELECT COUNT(*) FROM vehicles WHERE status = 'Available' AND vehicle_id NOT IN ($rented)) AS available_vehicles,
            (SELECT COUNT(*) FROM ($rented) x) AS rented_vehicles,
            (SELECT COUNT(*) FROM vehicles WHERE status = 'Maintenance') AS maintenance_vehicles,
            (SELECT COUNT(*) FROM customers WHERE status = 'Active') AS total_customers,
            (SELECT COUNT(*) FROM reservations) AS total_reservations,
            (SELECT COUNT(*) FROM reservations WHERE status = 'Approved' AND return_datetime >= NOW()) AS active_reservations,
            (SELECT COALESCE(SUM(" . Payment::NET . "), 0) FROM payments WHERE " . Payment::COUNTED . ") AS total_revenue";
        return Database::query($sql)->fetch();
    }

    // Last N months of reservations created and revenue received.
    public static function monthly(int $months = 6): array
    {
        $start = new DateTime('first day of this month 00:00:00');
        $start->modify('-' . ($months - 1) . ' months');
        $map = [];
        for ($i = 0; $i < $months; $i++) {
            $d = (clone $start)->modify("+$i months");
            $map[$d->format('Y-m')] = ['label' => $d->format('M Y'), 'reservations' => 0, 'revenue' => 0.0];
        }
        $from = $start->format('Y-m-d');
        $q1 = Database::query("SELECT DATE_FORMAT(created_at, '%Y-%m') AS m, COUNT(*) AS n FROM reservations WHERE created_at >= :f GROUP BY m", [':f' => $from]);
        foreach ($q1->fetchAll() as $r) { if (isset($map[$r['m']])) { $map[$r['m']]['reservations'] = (int)$r['n']; } }
        $q2 = Database::query("SELECT DATE_FORMAT(payment_date, '%Y-%m') AS m, SUM(" . Payment::NET . ") AS s FROM payments WHERE " . Payment::COUNTED . " AND payment_date >= :f GROUP BY m", [':f' => $from]);
        foreach ($q2->fetchAll() as $r) { if (isset($map[$r['m']])) { $map[$r['m']]['revenue'] = (float)$r['s']; } }

        $rows = array_values($map);
        return [
            'labels'       => array_column($rows, 'label'),
            'reservations' => array_column($rows, 'reservations'),
            'revenue'      => array_column($rows, 'revenue'),
        ];
    }

    public static function vehicleStatus(): array
    {
        return Database::query('SELECT status, COUNT(*) AS total FROM vehicles GROUP BY status')->fetchAll();
    }

    public static function recentReservations(int $n = 5): array
    {
        $n = max(1, $n);
        return Database::query(
            "SELECT r.reservation_id, c.full_name AS customer_name, CONCAT_WS(' ', v.brand, v.model) AS vehicle,
                    r.pickup_datetime, r.estimated_total, r.status
             FROM reservations r
             JOIN customers c ON c.customer_id = r.customer_id
             JOIN vehicles v ON v.vehicle_id = r.vehicle_id
             ORDER BY r.created_at DESC, r.reservation_id DESC LIMIT $n"
        )->fetchAll();
    }

    public static function recentVehicles(int $n = 5): array
    {
        $n = max(1, $n);
        $rows = Database::query(
            "SELECT v.vehicle_id, v.brand, v.model, v.year, v.license_plate, v.daily_price, v.status,
                    (SELECT image_path FROM vehicle_images i WHERE i.vehicle_id = v.vehicle_id
                     ORDER BY i.is_primary DESC, i.sort_order, i.image_id LIMIT 1) AS primary_image
             FROM vehicles v ORDER BY v.created_at DESC, v.vehicle_id DESC LIMIT $n"
        )->fetchAll();
        foreach ($rows as &$r) { $r['image_url'] = Upload::url($r['primary_image']); }
        return $rows;
    }
}