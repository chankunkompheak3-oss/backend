<?php
class Payment
{
    // REVENUE RULE (used by the dashboard AND reports):
    //  + Rental, Late Fee and Damage payments that are Paid
    //  - Refund payments
    //  Deposits are NOT revenue (they are refundable), so they count as 0 here.
    //  Payments with status Pending are never counted, and nothing is counted without a payment_date.
    const NET     = "CASE WHEN payment_type = 'Refund' THEN -amount WHEN payment_type = 'Deposit' THEN 0 ELSE amount END";
    const COUNTED = "status IN ('Paid','Refunded') AND payment_date IS NOT NULL";

    public static function forReservation(int $reservationId): array
    {
        return Database::query(
            'SELECT p.*, u.username AS received_by_name FROM payments p
             LEFT JOIN users u ON u.user_id = p.received_by
             WHERE p.reservation_id = :id ORDER BY p.payment_id DESC',
            [':id' => $reservationId]
        )->fetchAll();
    }

    // Records a payment inside a transaction so the balance check cannot be raced.
    public static function addToReservation(int $rid, string $type, float $amount, ?string $method, ?string $note, int $uid): array
    {
        $pdo = Database::connect();
        $pdo->beginTransaction();
        try {
            $r = Database::query('SELECT status, estimated_total FROM reservations WHERE reservation_id = :id FOR UPDATE', [':id' => $rid])->fetch();
            if (!$r) { throw new ApiException('Reservation not found.', 404); }
            if ($r['status'] === 'Rejected') { throw new ApiException('Payments cannot be recorded for a rejected reservation.', 409); }
            if ($r['status'] === 'Cancelled' && $type !== 'Refund') { throw new ApiException('A cancelled reservation can only receive refunds.', 409); }

            $paid = (float)Database::query(
                "SELECT COALESCE(SUM(CASE payment_type WHEN 'Rental' THEN amount WHEN 'Refund' THEN -amount ELSE 0 END),0)
                 FROM payments WHERE reservation_id = :id AND status IN ('Paid','Refunded')", [':id' => $rid]
            )->fetchColumn();
            $total = (float)($r['estimated_total'] ?? 0);

            if ($type === 'Rental' && round($paid + $amount, 2) > round($total, 2)) {
                throw new ApiException('Amount is more than the remaining balance (' . number_format($total - $paid, 2) . ').', 422, ['amount' => 'Exceeds the balance.']);
            }
            if ($type === 'Refund' && round($amount, 2) > round($paid, 2)) {
                throw new ApiException('Refund is more than the amount paid (' . number_format($paid, 2) . ').', 422, ['amount' => 'Exceeds the amount paid.']);
            }

            $invoice = null;
            for ($i = 0; $i < 5 && $invoice === null; $i++) {
                $candidate = 'INV-' . date('Ymd') . '-' . strtoupper(bin2hex(random_bytes(3)));
                try {
                    Database::query(
                        'INSERT INTO payments (rental_id, reservation_id, invoice_number, payment_type, amount, payment_method, payment_date, status, note, received_by)
                         VALUES (NULL, :rid, :inv, :type, :amt, :method, NOW(), :status, :note, :uid)',
                        [':rid' => $rid, ':inv' => $candidate, ':type' => $type, ':amt' => number_format($amount, 2, '.', ''),
                         ':method' => $method, ':status' => $type === 'Refund' ? 'Refunded' : 'Paid', ':note' => $note, ':uid' => $uid]
                    );
                    $invoice = $candidate;
                } catch (PDOException $e) {
                    if ($e->getCode() !== '23000' || $i === 4) { throw $e; }
                }
            }
            $id = Database::lastId();
            $pdo->commit();
            return ['payment_id' => $id, 'invoice_number' => $invoice];
        } catch (Throwable $e) {
            if ($pdo->inTransaction()) { $pdo->rollBack(); }
            throw $e;
        }
    }
}