<?php
class SettingsController extends ApiController
{
    public function handle(): never
    {
        $this->guard();
        $this->run(function () {
            $m = Request::method(); $act = Request::get('action'); $u = Auth::user();

            if ($m === 'GET') {
                $row = User::findById($u['id']);
                Response::success([
                    'profile' => ['username' => $row['username'], 'role' => $row['role'], 'email' => $row['email'], 'phone' => $row['phone'], 'last_login_at' => $row['last_login_at']],
                    'system'  => Setting::get(),
                    'is_admin' => $u['role'] === 'Admin',
                ]);
            }
            if ($m === 'PUT' && $act === 'profile')  { $this->profile($u['id']); }
            if ($m === 'PUT' && $act === 'password') { $this->password($u['id']); }
            if ($m === 'PUT' && $act === 'system')   { $this->admin(); $this->system(); }
            throw new ApiException('Method not allowed.', 405);
        });
    }

    private function profile(int $uid): never
    {
        $v = new Validator(Request::input());
        $email = $v->email('email'); $phone = $v->phone('phone');
        $v->fail();
        if ($email && User::emailTaken($email, $uid)) {
            throw new ApiException('Please fix the highlighted fields.', 409, ['email' => 'This email is used by another account.']);
        }
        User::updateProfile($uid, $email, $phone);
        Logger::log('UPDATE', 'users', $uid, 'profile');
        Response::success(null, 'Profile updated.');
    }

    private function password(int $uid): never
    {
        $in = Request::input();
        $current = (string)($in['current_password'] ?? ''); $new = (string)($in['new_password'] ?? ''); $confirm = (string)($in['confirm_password'] ?? '');
        $errors = [];
        $row = User::findById($uid);
        if (!password_verify($current, $row['password'])) { $errors['current_password'] = 'Current password is incorrect.'; }
        if (strlen($new) < 8 || strlen($new) > 72 || !preg_match('/[A-Za-z]/', $new) || !preg_match('/\d/', $new)) {
            $errors['new_password'] = 'Use 8-72 characters with at least one letter and one number.';
        } elseif ($new === $current) {
            $errors['new_password'] = 'The new password must be different.';
        }
        if ($new !== $confirm) { $errors['confirm_password'] = 'Passwords do not match.'; }
        if ($errors) { throw new ApiException('Please fix the highlighted fields.', 422, $errors); }

        User::setPassword($uid, password_hash($new, PASSWORD_DEFAULT), false);
        $_SESSION['user']['must_change_password'] = false;
        session_regenerate_id(true);
        Logger::log('PASSWORD', 'users', $uid);
        Response::success(null, 'Password changed.');
    }

    private function system(): never
    {
        $v = new Validator(Request::input());
        $d = [
            'store_name'    => $v->str('store_name', true, 100),
            'contact_email' => $v->email('contact_email'),
            'phone'         => $v->phone('phone'),
            'address'       => $v->str('address', false, 2000),
            'currency'      => strtoupper((string)$v->str('currency', true, 3)),
            'tax_rate_percent'    => $v->money('tax_rate_percent', true, 100),
            'late_fee_multiplier' => $v->money('late_fee_multiplier', true, 10),
            'default_deposit'     => $v->money('default_deposit', true),
        ];
        if ($d['currency'] !== '' && !preg_match('/^[A-Z]{3}$/', $d['currency'])) { $v->errors['currency'] = 'Use a 3-letter code such as USD.'; }
        $v->fail();
        Setting::save($d);
        Logger::log('UPDATE', 'settings', 1);
        Response::success(null, 'Settings saved.');
    }
}