<?php
class Reservation
{
    const SORT = ['id' => 'r.reservation_id', 'customer' => 'c.full_name', 'pickup' => 'r.pickup_datetime', 'return' => 'r.return_datetime',
                  'total' => 'r.estimated_total', 'status' => 'r.status', 'created' => 'r.created_at'];

    private static function baseSelect(): string
    {
        return "SELECT r.*, c.full_name AS customer_name, c.phone AS customer_phone, v.brand, v.model, v.license_plate,
                    COALESCE(p.paid, 0) AS paid_amount,
                    CASE WHEN COALESCE(p.paid, 0) <= 0 THEN 'Unpaid'
                         WHEN COALESCE(p.paid, 0) < COALESCE(r.estimated_total, 0) THEN 'Partial' ELSE 'Paid' END AS payment_status
                FROM reservations r
                JOIN customers c ON c.customer_id = r.customer_id
                JOIN vehicles v ON v.vehicle_id = r.vehicle_id
                LEFT JOIN (SELECT reservation_id, SUM(CASE payment_type WHEN 'Rental' THEN amount WHEN 'Refund' THEN -amount ELSE 0 END) AS paid
                           FROM payments WHERE reservation_id IS NOT NULL AND status IN ('Paid','Refunded') GROUP BY reservation_id) p
                       ON p.reservation_id = r.reservation_id";
    }

    public static function list(array $f, string $order, int $per, int $off): array
    {
        $w = ['1=1']; $p = [];
        if (!empty($f['like'])) {
            $w[] = "(c.full_name LIKE :q1 OR c.phone LIKE :q2 OR v.license_plate LIKE :q3 OR CONCAT_WS(' ', v.brand, v.model) LIKE :q4 OR r.reservation_id = :qid)";
            foreach (['q1', 'q2', 'q3', 'q4'] as $k) { $p[":$k"] = $f['like']; }
            $p[':qid'] = ctype_digit($f['raw']) ? (int)$f['raw'] : 0;
        }
        if (!empty($f['status']))      { $w[] = 'r.status = :st'; $p[':st'] = $f['status']; }
        if (!empty($f['customer_id'])) { $w[] = 'r.customer_id = :cid'; $p[':cid'] = (int)$f['customer_id']; }
        if (!empty($f['vehicle_id']))  { $w[] = 'r.vehicle_id = :vid'; $p[':vid'] = (int)$f['vehicle_id']; }
        if (!empty($f['date_from']))   { $w[] = 'DATE(r.pickup_datetime) >= :df'; $p[':df'] = $f['date_from']; }
        if (!empty($f['date_to']))     { $w[] = 'DATE(r.pickup_datetime) <= :dt'; $p[':dt'] = $f['date_to']; }
        $where = implode(' AND ', $w);

        $total = (int)Database::query("SELECT COUNT(*) FROM reservations r JOIN customers c ON c.customer_id = r.customer_id JOIN vehicles v ON v.vehicle_id = r.vehicle_id WHERE $where", $p)->fetchColumn();
        $items = Database::query(self::baseSelect() . " WHERE $where ORDER BY $order LIMIT :lim OFFSET :off", $p + [':lim' => $per, ':off' => $off])->fetchAll();
        return [$items, $total];
    }

    public static function detail(int $id): ?array
    {
        $r = Database::query(self::baseSelect() . ' WHERE r.reservation_id = :id', [':id' => $id])->fetch();
        if (!$r) { return null; }
        $r['payments'] = Payment::forReservation($id);
        $r['balance']  = max(0, round((float)$r['estimated_total'] - (float)$r['paid_amount'], 2));
        $r['deposit_paid'] = (float)Database::query("SELECT COALESCE(SUM(amount),0) FROM payments WHERE reservation_id = :id AND payment_type = 'Deposit' AND status = 'Paid'", [':id' => $id])->fetchColumn();
        return $r;
    }

    public static function options(): array
    {
        return [
            'customers' => Database::query("SELECT customer_id AS id, CONCAT(full_name, ' (', COALESCE(phone,'-'), ')') AS label FROM customers WHERE status = 'Active' AND is_blacklisted = 0 ORDER BY full_name")->fetchAll(),
            'vehicles'  => Database::query("SELECT vehicle_id AS id, CONCAT_WS(' ', brand, model, CONCAT('- ', license_plate)) AS label, daily_price FROM vehicles WHERE status NOT IN ('Maintenance','Out Of Service') ORDER BY brand, model")->fetchAll(),
        ];
    }

    // ---- pricing (single source of truth) ----
    public static function calculate(float $rate, string $pickup, string $return, float $discount, float $extra): array
    {
        $taxRate  = (float)Setting::get()['tax_rate_percent'];
        $days     = max(1, (int)ceil((strtotime($return) - strtotime($pickup)) / 86400));
        $subtotal = round($days * $rate, 2);
        $taxable  = max(0, $subtotal - $discount);
        $tax      = round($taxable * $taxRate / 100, 2);
        return ['days' => $days, 'daily_rate' => $rate, 'subtotal' => $subtotal, 'discount' => $discount, 'tax' => $tax,
                'tax_rate' => $taxRate, 'extra_fees' => $extra, 'total' => round($taxable + $tax + $extra, 2)];
    }

    // ---- availability ----
    // Returns a message if the vehicle is NOT free for these dates, otherwise null.
    public static function conflict(int $vehicleId, string $pickup, string $return, ?int $excludeId = null): ?string
    {
        $r = Database::query(
            "SELECT reservation_id FROM reservations WHERE vehicle_id = :v AND status IN ('Pending','Approved') AND reservation_id <> :ex
               AND :pu < return_datetime AND :re > pickup_datetime LIMIT 1",
            [':v' => $vehicleId, ':ex' => $excludeId ?? 0, ':pu' => $pickup, ':re' => $return]
        )->fetchColumn();
        if ($r) { return 'This vehicle is already reserved (reservation #R' . str_pad((string)$r, 5, '0', STR_PAD_LEFT) . ') during these dates.'; }

        $t = Database::query(
            "SELECT rental_id FROM rentals WHERE vehicle_id = :v AND status = 'Active'
               AND :pu < COALESCE(actual_return_datetime, expected_return_datetime) AND :re > start_datetime LIMIT 1",
            [':v' => $vehicleId, ':pu' => $pickup, ':re' => $return]
        )->fetchColumn();
        return $t ? 'This vehicle is on an active rental during these dates.' : null;
    }

    // Locks the vehicle row and checks it can be rented. Must be called inside a transaction.
    private static function lockVehicle(int $vehicleId): array
    {
        $veh = Database::query('SELECT vehicle_id, daily_price, status FROM vehicles WHERE vehicle_id = :id FOR UPDATE', [':id' => $vehicleId])->fetch();
        if (!$veh) { throw new ApiException('Vehicle not found.', 422, ['vehicle_id' => 'Vehicle not found.']); }
        if (in_array($veh['status'], ['Maintenance', 'Out Of Service'], true)) {
            throw new ApiException('This vehicle is not available for rental (status: ' . $veh['status'] . ').', 409, ['vehicle_id' => 'Vehicle is ' . $veh['status'] . '.']);
        }
        return $veh;
    }

    private static function checkCustomer(int $customerId, string $return): void
    {
        $c = Database::query('SELECT status, is_blacklisted, license_expiry FROM customers WHERE customer_id = :id', [':id' => $customerId])->fetch();
        if (!$c) { throw new ApiException('Customer not found.', 422, ['customer_id' => 'Customer not found.']); }
        if ($c['status'] !== 'Active') { throw new ApiException('This customer is inactive.', 409, ['customer_id' => 'Customer is inactive.']); }
        if ((int)$c['is_blacklisted'] === 1) { throw new ApiException('This customer is blacklisted.', 409, ['customer_id' => 'Customer is blacklisted.']); }
        if ($c['license_expiry'] && $c['license_expiry'] < substr($return, 0, 10)) {
            throw new ApiException("The customer's driving license expires before the return date.", 409, ['customer_id' => 'License expires before the return date.']);
        }
    }

    private static function checkMoney(array $d, array $q): void
    {
        if ($d['discount_amount'] > $q['subtotal']) {
            throw new ApiException('Please fix the highlighted fields.', 422, ['discount_amount' => 'Discount cannot exceed the subtotal (' . number_format($q['subtotal'], 2) . ').']);
        }
    }

    // ---- create ----
    public static function create(array $d, int $uid): int
    {
        if (strtotime($d['pickup_datetime']) < time() - 3600) {
            throw new ApiException('Please fix the highlighted fields.', 422, ['pickup_datetime' => 'Pickup cannot be in the past.']);
        }
        $pdo = Database::connect();
        $pdo->beginTransaction();
        try {
            $veh = self::lockVehicle($d['vehicle_id']);                // lock = two staff cannot book the same car at once
            self::checkCustomer($d['customer_id'], $d['return_datetime']);
            if ($msg = self::conflict($d['vehicle_id'], $d['pickup_datetime'], $d['return_datetime'])) {
                throw new ApiException($msg, 409, ['vehicle_id' => $msg]);
            }
            $q = self::calculate((float)$veh['daily_price'], $d['pickup_datetime'], $d['return_datetime'], $d['discount_amount'], $d['extra_fees']);
            self::checkMoney($d, $q);

            Database::query(
                "INSERT INTO reservations (customer_id, vehicle_id, pickup_location, return_location, pickup_datetime, return_datetime,
                    daily_rate, estimated_total, discount_amount, extra_fees, status, note, created_by)
                 VALUES (:c, :v, :pl, :rl, :pu, :re, :rate, :tot, :disc, :extra, 'Pending', :note, :uid)",
                [':c' => $d['customer_id'], ':v' => $d['vehicle_id'], ':pl' => $d['pickup_location'], ':rl' => $d['return_location'],
                 ':pu' => $d['pickup_datetime'], ':re' => $d['return_datetime'], ':rate' => number_format($q['daily_rate'], 2, '.', ''),
                 ':tot' => number_format($q['total'], 2, '.', ''), ':disc' => number_format($d['discount_amount'], 2, '.', ''),
                 ':extra' => number_format($d['extra_fees'], 2, '.', ''), ':note' => $d['note'], ':uid' => $uid]
            );
            $id = Database::lastId();
            $pdo->commit();
            return $id;
        } catch (Throwable $e) {
            if ($pdo->inTransaction()) { $pdo->rollBack(); }
            throw $e;
        }
    }

    // ---- edit (only Pending / Approved) ----
    public static function update(int $id, array $d): void
    {
        $pdo = Database::connect();
        $pdo->beginTransaction();
        try {
            $cur = Database::query('SELECT * FROM reservations WHERE reservation_id = :id FOR UPDATE', [':id' => $id])->fetch();
            if (!$cur) { throw new ApiException('Reservation not found.', 404); }
            if (!in_array($cur['status'], ['Pending', 'Approved'], true)) {
                throw new ApiException('Only Pending or Approved reservations can be edited.', 409);
            }
            $veh = self::lockVehicle($d['vehicle_id']);
            self::checkCustomer($d['customer_id'], $d['return_datetime']);
            if ($msg = self::conflict($d['vehicle_id'], $d['pickup_datetime'], $d['return_datetime'], $id)) {
                throw new ApiException($msg, 409, ['vehicle_id' => $msg]);
            }
            // keep the original price snapshot unless the vehicle was changed
            $rate = (int)$cur['vehicle_id'] === $d['vehicle_id'] ? (float)$cur['daily_rate'] : (float)$veh['daily_price'];
            $q = self::calculate($rate, $d['pickup_datetime'], $d['return_datetime'], $d['discount_amount'], $d['extra_fees']);
            self::checkMoney($d, $q);

            Database::query(
                'UPDATE reservations SET customer_id = :c, vehicle_id = :v, pickup_location = :pl, return_location = :rl,
                    pickup_datetime = :pu, return_datetime = :re, daily_rate = :rate, estimated_total = :tot,
                    discount_amount = :disc, extra_fees = :extra, note = :note WHERE reservation_id = :id',
                [':c' => $d['customer_id'], ':v' => $d['vehicle_id'], ':pl' => $d['pickup_location'], ':rl' => $d['return_location'],
                 ':pu' => $d['pickup_datetime'], ':re' => $d['return_datetime'], ':rate' => number_format($q['daily_rate'], 2, '.', ''),
                 ':tot' => number_format($q['total'], 2, '.', ''), ':disc' => number_format($d['discount_amount'], 2, '.', ''),
                 ':extra' => number_format($d['extra_fees'], 2, '.', ''), ':note' => $d['note'], ':id' => $id]
            );
            $pdo->commit();
        } catch (Throwable $e) {
            if ($pdo->inTransaction()) { $pdo->rollBack(); }
            throw $e;
        }
    }

    // ---- status workflow ----
    const TRANSITIONS = [
        'Pending'  => ['Approved', 'Rejected', 'Cancelled'],
        'Approved' => ['Completed', 'Cancelled'],
    ];

    public static function changeStatus(int $id, string $new, ?string $reason, int $uid): void
    {
        $pdo = Database::connect();
        $pdo->beginTransaction();
        try {
            $r = Database::query('SELECT * FROM reservations WHERE reservation_id = :id FOR UPDATE', [':id' => $id])->fetch();
            if (!$r) { throw new ApiException('Reservation not found.', 404); }
            if (!in_array($new, self::TRANSITIONS[$r['status']] ?? [], true)) {
                throw new ApiException("A {$r['status']} reservation cannot be changed to $new.", 409);
            }
            if ($new === 'Rejected' && !$reason) {
                throw new ApiException('A reason is required to reject a reservation.', 422, ['reason' => 'Required.']);
            }
            if ($new === 'Approved') {
                if (strtotime($r['return_datetime']) <= time()) { throw new ApiException('This reservation has already ended and cannot be approved.', 409); }
                self::lockVehicle((int)$r['vehicle_id']);
                self::checkCustomer((int)$r['customer_id'], $r['return_datetime']);
                if ($msg = self::conflict((int)$r['vehicle_id'], $r['pickup_datetime'], $r['return_datetime'], $id)) { throw new ApiException($msg, 409); }
            }
            if ($new === 'Completed' && strtotime($r['pickup_datetime']) > time()) {
                throw new ApiException('A reservation cannot be completed before its pickup time.', 409);
            }
            $approver = in_array($new, ['Approved', 'Rejected'], true);
            Database::query(
                'UPDATE reservations SET status = :s, rejection_reason = :rr, approved_by = :ab, approved_at = :aa WHERE reservation_id = :id',
                [':s' => $new, ':rr' => $new === 'Rejected' ? $reason : $r['rejection_reason'],
                 ':ab' => $approver ? $uid : $r['approved_by'], ':aa' => $approver ? date('Y-m-d H:i:s') : $r['approved_at'], ':id' => $id]
            );
            $pdo->commit();
        } catch (Throwable $e) {
            if ($pdo->inTransaction()) { $pdo->rollBack(); }
            throw $e;
        }
    }

    public static function delete(int $id): void
    {
        $r = Database::query('SELECT status FROM reservations WHERE reservation_id = :id', [':id' => $id])->fetch();
        if (!$r) { throw new ApiException('Reservation not found.', 404); }
        if (!in_array($r['status'], ['Cancelled', 'Rejected'], true)) {
            throw new ApiException('Only Cancelled or Rejected reservations can be deleted. Cancel it first.', 409);
        }
        if (Database::query('SELECT COUNT(*) FROM payments WHERE reservation_id = :id', [':id' => $id])->fetchColumn() > 0) {
            throw new ApiException('This reservation has payment records and cannot be deleted.', 409);
        }
        Database::query('DELETE FROM reservations WHERE reservation_id = :id', [':id' => $id]);
    }
}