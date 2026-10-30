<?php
class StaffController extends ApiController
{
    public function handle(): never
    {
        $this->guard();
        $this->admin();   // server-side: Staff-role users get 403 on every staff endpoint
        $this->run(function () {
            $m = Request::method(); $act = Request::get('action');
            if ($m === 'GET') {
                if ($id = Request::id()) { Response::success(Staff::find($id) ?? throw new ApiException('Staff member not found.', 404)); }
                [$page, $per, $off] = Request::paging();
                $f = ['like' => Request::like(), 'role' => Request::get('role'), 'status' => Request::get('status')];
                [$items, $total] = Staff::list($f, $this->order(Staff::SORT, 'created', 'DESC'), $per, $off);
                $this->paged($items, $total, $page, $per);
            }
            if ($m === 'POST') {
                [$d, $pw] = $this->fields(null, true);
                $id = Staff::create($d, password_hash($pw, PASSWORD_DEFAULT));
                Logger::log('CREATE', 'users', $id, $d['username']);
                Response::success(Staff::find($id), 'Staff account created. They must change the password at first login.', 201);
            }
            if ($m === 'PUT') {
                $id = $this->needId();
                $cur = Staff::find($id) ?? throw new ApiException('Staff member not found.', 404);
                if ($act === 'status') {
                    $v = new Validator(Request::input());
                    $s = $v->choice('status', ['Active', 'Inactive'], true); $v->fail();
                    $this->guardLastAdmin($cur, $cur['role'], $s);
                    Staff::setStatus($id, $s);
                    Logger::log('STATUS', 'users', $id, $s);
                    Response::success(Staff::find($id), "Account set to $s.");
                }
                [$d, $pw] = $this->fields($id, false);
                $this->guardLastAdmin($cur, $d['role'], $d['status']);
                Staff::update($id, $d, $pw ? password_hash($pw, PASSWORD_DEFAULT) : null);
                Logger::log('UPDATE', 'users', $id, $d['username']);
                Response::success(Staff::find($id), 'Staff updated.');
            }
            if ($m === 'DELETE') {          // "delete" = deactivate, so audit history stays intact
                $id = $this->needId();
                $cur = Staff::find($id) ?? throw new ApiException('Staff member not found.', 404);
                $this->guardLastAdmin($cur, $cur['role'], 'Inactive');
                Staff::setStatus($id, 'Inactive');
                Logger::log('DEACTIVATE', 'users', $id, $cur['username']);
                Response::success(null, 'Account deactivated.');
            }
            throw new ApiException('Method not allowed.', 405);
        });
    }

    // You cannot lock yourself out, and the system must always keep one active Admin.
    private function guardLastAdmin(array $cur, string $newRole, string $newStatus): void
    {
        $me = Auth::user()['id'];
        if ((int)$cur['user_id'] === $me && ($newRole !== $cur['role'] || $newStatus !== 'Active')) {
            throw new ApiException('You cannot deactivate or change the role of your own account.', 409);
        }
        $losesAdmin = $cur['role'] === 'Admin' && $cur['status'] === 'Active' && ($newRole !== 'Admin' || $newStatus !== 'Active');
        if ($losesAdmin && Staff::activeAdmins() <= 1) {
            throw new ApiException('At least one active Administrator must remain.', 409);
        }
    }

    private function fields(?int $id, bool $creating): array
    {
        $in = Request::input();
        $v = new Validator($in);
        $d = [
            'username'      => $v->str('username', true, 50),
            'role'          => $v->choice('role', ['Admin', 'Staff'], true),
            'status'        => $v->choice('status', ['Active', 'Inactive'], false, 'Active'),
            'email'         => $v->email('email'),
            'phone'         => $v->phone('phone'),
            'full_name'     => $v->str('full_name', true, 100),
            'gender'        => $v->choice('gender', ['Male', 'Female']),
            'position'      => $v->str('position', false, 50),
            'salary'        => $v->money('salary', false, 99999999.99),
            'hire_date'     => $v->day('hire_date'),
            'employee_code' => $v->str('employee_code', false, 20),
        ];
        if ($d['username'] && !preg_match('/^[A-Za-z0-9_.-]{3,50}$/', $d['username'])) { $v->errors['username'] = '3-50 letters, numbers, dot, dash or underscore.'; }
        $pw = (string)($in['password'] ?? '');
        if ($creating || $pw !== '') {
            if (strlen($pw) < 8 || strlen($pw) > 72 || !preg_match('/[A-Za-z]/', $pw) || !preg_match('/\d/', $pw)) {
                $v->errors['password'] = 'Use 8-72 characters with at least one letter and one number.';
            }
        }
        $v->fail();
        $dup = [];
        if (Staff::usernameTaken($d['username'], $id)) { $dup['username'] = 'This username is taken.'; }
        if ($d['email'] && Staff::emailTaken($d['email'], $id)) { $dup['email'] = 'This email is already used.'; }
        if ($d['employee_code'] && Staff::codeTaken($d['employee_code'], $id)) { $dup['employee_code'] = 'This employee code is already used.'; }
        if ($dup) { throw new ApiException('Please fix the highlighted fields.', 409, $dup); }
        return [$d, $pw];
    }
}