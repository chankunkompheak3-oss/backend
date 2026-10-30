<?php
class CustomerController extends ApiController
{
    public function handle(): never
    {
        $this->guard();
        $this->run(function () {
            $m = Request::method();
            if ($m === 'GET') {
                if ($id = Request::id()) {
                    $c = Customer::find($id) ?? throw new ApiException('Customer not found.', 404);
                    $c['reservations'] = Customer::history($id);      // reservation history
                    Response::success($c);
                }
                [$page, $per, $off] = Request::paging();
                $f = ['like' => Request::like(), 'raw' => Request::get('q'), 'status' => Request::get('status'), 'blacklisted' => Request::get('blacklisted')];
                [$items, $total] = Customer::list($f, $this->order(Customer::SORT, 'created', 'DESC'), $per, $off);
                $this->paged($items, $total, $page, $per);
            }
            if ($m === 'POST') {
                $d  = $this->fields(null);
                $id = Customer::insert($d, Auth::user()['id']);
                Logger::log('CREATE', 'customers', $id, $d['full_name']);
                Response::success(Customer::find($id), 'Customer registered.', 201);
            }
            if ($m === 'PUT') {
                $id = $this->needId();
                if (!Customer::find($id)) { throw new ApiException('Customer not found.', 404); }
                $d = $this->fields($id);
                Customer::update($id, $d);
                Logger::log('UPDATE', 'customers', $id, $d['full_name']);
                Response::success(Customer::find($id), 'Customer updated.');
            }
            if ($m === 'DELETE') {
                $this->admin();
                $id = $this->needId();
                $c  = Customer::find($id) ?? throw new ApiException('Customer not found.', 404);
                if (Customer::hasHistory($id)) {
                    Customer::setInactive($id);                       // keep history, hide customer
                    Logger::log('DEACTIVATE', 'customers', $id, $c['full_name']);
                    Response::success(null, 'This customer has reservation history, so they were deactivated instead of deleted.');
                }
                Customer::delete($id);
                Logger::log('DELETE', 'customers', $id, $c['full_name']);
                Response::success(null, 'Customer deleted.');
            }
            throw new ApiException('Method not allowed.', 405);
        });
    }

    private function fields(?int $ignoreId): array
    {
        $v = new Validator(Request::input());
        $d = [
            'full_name'      => $v->str('full_name', true, 100),
            'gender'         => $v->choice('gender', ['Male', 'Female']),
            'phone'          => $v->phone('phone', true),
            'email'          => $v->email('email'),
            'address'        => $v->str('address', false, 2000),
            'license_number' => $v->str('license_number', false, 50),
            'license_expiry' => $v->day('license_expiry'),
            'is_blacklisted' => $v->flag('is_blacklisted'),
            'blacklist_reason' => $v->str('blacklist_reason', false, 255),
            'status'         => $v->choice('status', ['Active', 'Inactive'], false, 'Active'),
        ];
        if ($d['is_blacklisted'] && !$d['blacklist_reason']) { $v->errors['blacklist_reason'] = 'Give a reason for blacklisting.'; }
        if (!$d['is_blacklisted']) { $d['blacklist_reason'] = null; }
        $v->fail();
        if ($dup = Customer::duplicate($d, $ignoreId)) {
            throw new ApiException('Duplicate customer detected.', 409, $dup);
        }
        return $d;
    }
}