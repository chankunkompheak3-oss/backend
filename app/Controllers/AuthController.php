<?php
class AuthController
{
    public function csrf(): never
    {
        Response::success(['token' => Auth::csrfToken()]);
    }

    public function me(): never
    {
        Auth::requireLogin();
        Response::success(Auth::user());
    }

    public function login(): never
    {
        Auth::requireCsrf();
        $in       = Request::json();
        $username = trim((string)($in['username'] ?? ''));
        $password = (string)($in['password'] ?? '');

        // Validation
        $errors = [];
        if ($username === '' || strlen($username) > 50) { $errors['username'] = 'Enter your username.'; }
        if ($password === '' || strlen($password) > 255) { $errors['password'] = 'Enter your password.'; }
        if ($errors) {
            Response::error('Please fix the errors.', 422, $errors);
        }

        $generic = 'Invalid username or password.';   // same message for every failure
        $user = User::findByUsername($username);

        if (!$user || $user['status'] !== 'Active') {
            Response::error($generic, 401);
        }
        if ($user['locked_until'] && strtotime($user['locked_until']) > time()) {
            Response::error('Account temporarily locked. Try again later.', 423);
        }
        if (!password_verify($password, $user['password'])) {
            User::registerFailure((int)$user['user_id']);
            Response::error($generic, 401);
        }

        User::registerSuccess((int)$user['user_id']);
        Auth::login($user);

        Response::success([
            'user'     => Auth::user(),
            'csrf'     => Auth::csrfToken(),
            'redirect' => Auth::safeRedirect($in['next'] ?? null),
        ], 'Login successful.');
    }

    public function logout(): never
    {
        Auth::requireLogin();
        Auth::requireCsrf();
        Auth::logout();
        Response::success(null, 'Logged out.');
    }
}