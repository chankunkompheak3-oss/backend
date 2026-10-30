<?php
class ReservationController extends ApiController
{
    public function handle(): never
    {
        $this->guard();
        $this->run(function () {
            $m = Request::method(); $act = Request::get('action'); $id = Request::id();

            if ($m === 'GET') {
                if ($act === 'options') { Response::success(Reservation::options()); }
                if ($act === 'quote')   { $this->quote(); }
                if ($id) { Response::success(Reservation::detail($id) ?? throw new ApiException('Reservation not found.', 404)); }
                [$page, $per, $off] = Request::paging();
                $f = ['like' => Request::like(), 'raw' => Request::get('q'), 'status' => Request::get('status'), 'customer_id' => Request::get('customer_id'),
                      'vehicle_id' => Request::get('vehicle_id'), 'date_from' => Request::get('date_from'), 'date_to' => Request::get('date_to')];
                [$items, $total] = Reservation::list($f, $this->order(Reservation::SORT, 'created', 'DESC'), $per, $off);
                $this->paged($items, $total, $page, $per);
            }
            if ($m === 'POST') {
                if ($act === 'payment') { $this->payment($this->needId()); }
                $d  = $this->fields();
                $nid = Reservation::create($d, Auth::user()['id']);
                Logger::log('CREATE', 'reservations', $nid);
                Response::success(Reservation::detail($nid), 'Reservation created (status: Pending).', 201);
            }
            if ($m === 'PUT') {
                $id = $this->needId();
                if ($act === 'status') { $this->status($id); }
                Reservation::update($id, $this->fields());
                Logger::log('UPDATE', 'reservations', $id);
                Response::success(Reservation::detail($id), 'Reservation updated.');
            }
            if ($m === 'DELETE') {
                $this->admin();
                $id = $this->needId();
                Reservation::delete($id);
                Logger::log('DELETE', 'reservations', $id);
                Response::success(null, 'Reservation deleted.');
            }
            throw new ApiException('Method not allowed.', 405);
        });
    }

    private function fields(): array
    {
        $v = new Validator(Request::input());
        $d = [
            'customer_id'     => $v->whole('customer_id', true, 1),
            'vehicle_id'      => $v->whole('vehicle_id', true, 1),
            'pickup_datetime' => $v->moment('pickup_datetime', true),
            'return_datetime' => $v->moment('return_datetime', true),
            'pickup_location' => $v->str('pickup_location', false, 150),
            'return_location' => $v->str('return_location', false, 150),
            'discount_amount' => $v->money('discount_amount') ?? 0.0,
            'extra_fees'      => $v->money('extra_fees') ?? 0.0,
            'note'            => $v->str('note', false, 2000),
        ];
        if (!$v->errors && $d['return_datetime'] <= $d['pickup_datetime']) { $v->errors['return_datetime'] = 'Return must be after pickup.'; }
        $v->fail();
        return $d;
    }

    // Live price + availability preview for the booking form.
    private function quote(): never
    {
        $vid = (int)Request::get('vehicle_id');
        $pu  = (new Validator($_GET))->moment('pickup_datetime');
        $re  = (new Validator($_GET))->moment('return_datetime');
        $veh = $vid ? Database::query('SELECT daily_price FROM vehicles WHERE vehicle_id = :id', [':id' => $vid])->fetch() : null;
        if (!$veh || !$pu || !$re || $re <= $pu) { Response::success(['ready' => false]); }

        $disc  = (new Validator($_GET))->money('discount_amount') ?? 0.0;
        $extra = (new Validator($_GET))->money('extra_fees') ?? 0.0;
        $ex    = (int)Request::get('exclude_id') ?: null;
        $rate  = (float)$veh['daily_price'];
        if ($ex) {   // editing: keep the saved rate if the vehicle is unchanged
            $cur = Database::query('SELECT vehicle_id, daily_rate FROM reservations WHERE reservation_id = :id', [':id' => $ex])->fetch();
            if ($cur && (int)$cur['vehicle_id'] === $vid) { $rate = (float)$cur['daily_rate']; }
        }
        $q = Reservation::calculate($rate, $pu, $re, $disc, $extra);
        $q['ready'] = true;
        $q['conflict'] = Reservation::conflict($vid, $pu, $re, $ex);
        Response::success($q);
    }

    private function status(int $id): never
    {
        $v = new Validator(Request::input());
        $new    = $v->choice('status', ['Approved', 'Rejected', 'Cancelled', 'Completed'], true);
        $reason = $v->str('reason', false, 255);
        $v->fail();
        Reservation::changeStatus($id, $new, $reason, Auth::user()['id']);
        Logger::log('STATUS', 'reservations', $id, $new);
        Response::success(Reservation::detail($id), "Reservation $new.");
    }

    private function payment(int $id): never
    {
        $v = new Validator(Request::input());
        $type   = $v->choice('payment_type', ['Rental', 'Deposit', 'Refund'], true);
        $amount = $v->money('amount', true);
        $method = $v->choice('payment_method', ['Cash', 'Card', 'Bank Transfer'], true);
        $note   = $v->str('note', false, 255);
        if ($amount !== null && $amount <= 0) { $v->errors['amount'] = 'Amount must be greater than zero.'; }
        $v->fail();
        $res = Payment::addToReservation($id, $type, $amount, $method, $note, Auth::user()['id']);
        Logger::log('PAYMENT', 'reservations', $id, "$type $amount {$res['invoice_number']}");
        Response::success(Reservation::detail($id), 'Payment recorded: ' . $res['invoice_number'], 201);
    }
}