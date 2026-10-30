<?php
/*
 HOW REVENUE IS CALCULATED
  Revenue = money actually received, by payment date:
     + payments of type Rental, Late Fee, Damage with status Paid
     - payments of type Refund
  * Deposits are NOT revenue (refundable); they are shown separately.
  * Pending (unpaid) reservations add NOTHING until a payment is recorded.
  * Cancelled/Rejected reservations add nothing; money already received for them stays in revenue
    until a Refund payment is recorded, which then reduces revenue.
*/
class ReportController extends ApiController
{
    const SECTIONS = ['summary', 'revenue', 'reservations', 'utilization', 'top_vehicles', 'customers', 'payments', 'fleet'];

    public function handle(): never
    {
        $this->guard();
        $this->run(function () {
            if (Request::method() !== 'GET') { throw new ApiException('Method not allowed.', 405); }
            $section = Request::get('section') ?: 'summary';
            if (!in_array($section, self::SECTIONS, true)) { throw new ApiException('Unknown report section.', 400); }

            $v = new Validator($_GET);
            $to   = $v->day('to')   ?? date('Y-m-d');
            $from = $v->day('from') ?? date('Y-m-d', strtotime($to . ' -29 days'));
            $v->fail();
            if ($from > $to) { throw new ApiException('"From" date must not be after "to" date.', 422, ['from' => 'Must be on or before the end date.']); }
            if (strtotime($to) - strtotime($from) > 366 * 5 * 86400) { throw new ApiException('Date range is limited to 5 years.', 422); }

            $data = $this->{'section_' . $section}($from . ' 00:00:00', $to . ' 23:59:59', $from, $to);
            $data['from'] = $from; $data['to'] = $to; $data['section'] = $section;

            if (Request::get('format') === 'csv') { $this->csv($data, "report_{$section}_{$from}_{$to}"); }
            Response::success($data);
        });
    }

    private function col(string $key, string $label, string $type = 'text'): array { return ['key' => $key, 'label' => $label, 'type' => $type]; }

    private function section_summary(string $f, string $t, string $df, string $dt): array
    {
        $q = fn(string $sql, array $p = []) => Database::query($sql, $p)->fetchColumn();
        $range = [':f' => $f, ':t' => $t];
        $rows = [
            ['key' => 'revenue',  'label' => 'Net rental revenue',  'type' => 'money', 'value' => $q('SELECT COALESCE(SUM(' . Payment::NET . "),0) FROM payments WHERE " . Payment::COUNTED . ' AND payment_date BETWEEN :f AND :t', $range)],
            ['key' => 'deposits', 'label' => 'Deposits received',   'type' => 'money', 'value' => $q("SELECT COALESCE(SUM(amount),0) FROM payments WHERE payment_type='Deposit' AND status='Paid' AND payment_date BETWEEN :f AND :t", $range)],
            ['key' => 'refunds',  'label' => 'Refunds issued',      'type' => 'money', 'value' => $q("SELECT COALESCE(SUM(amount),0) FROM payments WHERE payment_type='Refund' AND payment_date BETWEEN :f AND :t", $range)],
            ['key' => 'reservations', 'label' => 'Reservations (pickup in range)', 'type' => 'int', 'value' => $q('SELECT COUNT(*) FROM reservations WHERE pickup_datetime BETWEEN :f AND :t', $range)],
            ['key' => 'confirmed', 'label' => 'Approved / completed', 'type' => 'int', 'value' => $q("SELECT COUNT(*) FROM reservations WHERE status IN ('Approved','Completed') AND pickup_datetime BETWEEN :f AND :t", $range)],
            ['key' => 'lost', 'label' => 'Cancelled / rejected', 'type' => 'int', 'value' => $q("SELECT COUNT(*) FROM reservations WHERE status IN ('Cancelled','Rejected') AND pickup_datetime BETWEEN :f AND :t", $range)],
            ['key' => 'new_customers', 'label' => 'New customers', 'type' => 'int', 'value' => $q('SELECT COUNT(*) FROM customers WHERE created_at BETWEEN :f AND :t', $range)],
        ];
        return ['title' => 'Summary', 'columns' => [$this->col('label', 'Metric'), $this->col('value', 'Value')], 'rows' => $rows];
    }

    private function section_revenue(string $f, string $t): array
    {
        $rows = Database::query(
            'SELECT DATE(payment_date) AS day, COUNT(*) AS payments, SUM(' . Payment::NET . ') AS revenue FROM payments
             WHERE ' . Payment::COUNTED . ' AND payment_date BETWEEN :f AND :t GROUP BY DATE(payment_date) ORDER BY day',
            [':f' => $f, ':t' => $t]
        )->fetchAll();
        return ['title' => 'Revenue by date', 'columns' => [$this->col('day', 'Date', 'date'), $this->col('payments', 'Payments', 'int'), $this->col('revenue', 'Net revenue', 'money')], 'rows' => $rows];
    }

    private function section_reservations(string $f, string $t): array
    {
        $rows = Database::query(
            "SELECT CONCAT('R', LPAD(r.reservation_id, 5, '0')) AS ref, c.full_name AS customer, CONCAT_WS(' ', v.brand, v.model) AS vehicle,
                    r.pickup_datetime AS pickup, r.return_datetime AS returned, r.status, r.estimated_total AS total,
                    COALESCE((SELECT SUM(CASE payment_type WHEN 'Rental' THEN amount WHEN 'Refund' THEN -amount ELSE 0 END)
                              FROM payments p WHERE p.reservation_id = r.reservation_id AND p.status IN ('Paid','Refunded')), 0) AS paid
             FROM reservations r JOIN customers c ON c.customer_id = r.customer_id JOIN vehicles v ON v.vehicle_id = r.vehicle_id
             WHERE r.pickup_datetime BETWEEN :f AND :t ORDER BY r.pickup_datetime DESC LIMIT 5000",
            [':f' => $f, ':t' => $t]
        )->fetchAll();
        return ['title' => 'Reservations', 'columns' => [$this->col('ref', 'ID'), $this->col('customer', 'Customer'), $this->col('vehicle', 'Vehicle'), $this->col('pickup', 'Pickup', 'datetime'),
            $this->col('returned', 'Return', 'datetime'), $this->col('status', 'Status', 'badge'), $this->col('total', 'Total', 'money'), $this->col('paid', 'Paid', 'money')], 'rows' => $rows];
    }

    private function section_utilization(string $f, string $t, string $df, string $dt): array
    {
        $days = max(1, (int)round((strtotime($dt) - strtotime($df)) / 86400) + 1);
        $rows = Database::query(
            "SELECT CONCAT_WS(' ', v.brand, v.model) AS vehicle, v.license_plate AS plate, COUNT(r.reservation_id) AS reservations,
                    ROUND(COALESCE(SUM(GREATEST(0, TIMESTAMPDIFF(MINUTE, GREATEST(r.pickup_datetime, :f1), LEAST(r.return_datetime, :t1)))), 0) / 1440, 1) AS booked_days
             FROM vehicles v LEFT JOIN reservations r ON r.vehicle_id = v.vehicle_id AND r.status IN ('Approved','Completed')
                  AND r.pickup_datetime < :t2 AND r.return_datetime > :f2
             GROUP BY v.vehicle_id, v.brand, v.model, v.license_plate ORDER BY booked_days DESC",
            [':f1' => $f, ':t1' => $t, ':f2' => $f, ':t2' => $t]
        )->fetchAll();
        foreach ($rows as &$r) { $r['utilization'] = min(100, round((float)$r['booked_days'] / $days * 100, 1)); }
        return ['title' => "Vehicle utilization ($days days in range)", 'columns' => [$this->col('vehicle', 'Vehicle'), $this->col('plate', 'Plate'), $this->col('reservations', 'Reservations', 'int'),
            $this->col('booked_days', 'Booked days'), $this->col('utilization', 'Utilization', 'percent')], 'rows' => $rows];
    }

    private function section_top_vehicles(string $f, string $t): array
    {
        $rows = Database::query(
            "SELECT CONCAT_WS(' ', v.brand, v.model) AS vehicle, v.license_plate AS plate, COUNT(*) AS reservations, SUM(r.estimated_total) AS booked_value
             FROM reservations r JOIN vehicles v ON v.vehicle_id = r.vehicle_id
             WHERE r.status IN ('Approved','Completed') AND r.pickup_datetime BETWEEN :f AND :t
             GROUP BY v.vehicle_id, v.brand, v.model, v.license_plate ORDER BY reservations DESC, booked_value DESC LIMIT 20",
            [':f' => $f, ':t' => $t]
        )->fetchAll();
        return ['title' => 'Most frequently rented vehicles', 'columns' => [$this->col('vehicle', 'Vehicle'), $this->col('plate', 'Plate'), $this->col('reservations', 'Reservations', 'int'), $this->col('booked_value', 'Booked value', 'money')], 'rows' => $rows];
    }

    private function section_customers(string $f, string $t): array
    {
        $rows = Database::query(
            "SELECT c.full_name AS customer, c.phone, COUNT(r.reservation_id) AS reservations,
                    COALESCE(SUM(CASE WHEN r.status IN ('Approved','Completed') THEN r.estimated_total END), 0) AS total_value, MAX(r.pickup_datetime) AS last_pickup
             FROM customers c JOIN reservations r ON r.customer_id = c.customer_id AND r.pickup_datetime BETWEEN :f AND :t
             GROUP BY c.customer_id, c.full_name, c.phone ORDER BY reservations DESC, total_value DESC LIMIT 100",
            [':f' => $f, ':t' => $t]
        )->fetchAll();
        return ['title' => 'Customer activity', 'columns' => [$this->col('customer', 'Customer'), $this->col('phone', 'Phone'), $this->col('reservations', 'Reservations', 'int'), $this->col('total_value', 'Booked value', 'money'), $this->col('last_pickup', 'Last pickup', 'datetime')], 'rows' => $rows];
    }

    private function section_payments(string $f, string $t): array
    {
        $rows = Database::query(
            "SELECT payment_type AS type, COALESCE(payment_method, '-') AS method, COUNT(*) AS payments, SUM(amount) AS total
             FROM payments WHERE status IN ('Paid','Refunded') AND payment_date BETWEEN :f AND :t GROUP BY payment_type, payment_method ORDER BY payment_type, payment_method",
            [':f' => $f, ':t' => $t]
        )->fetchAll();
        return ['title' => 'Payment summary', 'columns' => [$this->col('type', 'Type'), $this->col('method', 'Method'), $this->col('payments', 'Payments', 'int'), $this->col('total', 'Total', 'money')], 'rows' => $rows];
    }

    private function section_fleet(): array
    {
        $s = Dashboard::stats();
        $out = Database::query("SELECT COUNT(*) FROM vehicles WHERE status = 'Out Of Service'")->fetchColumn();
        $rows = [['status' => 'Available', 'total' => $s['available_vehicles']], ['status' => 'Rented / reserved now', 'total' => $s['rented_vehicles']],
                 ['status' => 'Maintenance', 'total' => $s['maintenance_vehicles']], ['status' => 'Out of service', 'total' => $out]];
        return ['title' => 'Available vs rented vehicles (right now)', 'columns' => [$this->col('status', 'Status'), $this->col('total', 'Vehicles', 'int')], 'rows' => $rows];
    }

    private function csv(array $sec, string $name): never
    {
        header('Content-Type: text/csv; charset=utf-8');
        header('Content-Disposition: attachment; filename="' . preg_replace('/[^A-Za-z0-9_.-]/', '_', $name) . '.csv"');
        $out = fopen('php://output', 'w');
        fwrite($out, "\xEF\xBB\xBF");   // BOM so Excel reads UTF-8
        fputcsv($out, array_column($sec['columns'], 'label'), ',', '"', '\\');
        foreach ($sec['rows'] as $row) {
            $line = [];
            foreach ($sec['columns'] as $c) {
                $val = $row[$c['key']] ?? '';
                // stop spreadsheet formula injection from user-entered text
                if (is_string($val) && $val !== '' && !is_numeric($val) && strpbrk($val[0], '=+-@') !== false) { $val = "'" . $val; }
                $line[] = $val;
            }
            fputcsv($out, $line, ',', '"', '\\');
        }
        fclose($out);
        exit;
    }
}