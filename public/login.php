<?php
require_once __DIR__ . '/../config/bootstrap.php';

// Already logged in? Go to the dashboard.
if (Auth::check()) {
    header('Location: ' . BASE_URL . '/index.php');
    exit;
}
$next = htmlspecialchars($_GET['next'] ?? '', ENT_QUOTES, 'UTF-8');
$csrfToken = Auth::csrfToken();   // created in the same session as this page
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.7/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-LN+7fdVzj6u52u30Kp6M/trliBMCMKTyK833zpbD+pXdCLuTusPj697FH4R/5mcr" crossorigin="anonymous">
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.7/dist/js/bootstrap.bundle.min.js" integrity="sha384-ndDqU0Gzau9qJ1lfW4pNLlhNTkCfHzAVBReH9diLvGRem5+R9g2FzA8ZGN954O5Q" crossorigin="anonymous"></script>
    <link rel="stylesheet" href="css/login.css">
</head>

<body>
    <div class="main">
        <h1>Login</h1>
        <hr>
        <div id="alert" class="alert alert-danger d-none" role="alert"></div>
        <form id="loginForm" novalidate>
            <input type="hidden" id="next" value="<?= $next ?>">
            <input type="text" id="username" name="username" class="form-control" placeholder="Username..." autocomplete="username" maxlength="50">
            <input type="password" id="password" name="password" class="form-control" placeholder="Password..." autocomplete="current-password">
            <input type="submit" id="btn" value="Login" class="btn btn-primary">
        </form>
    </div>

    <script>
        const API  = '<?= BASE_URL ?>/api/auth.php';
        const csrf = '<?= $csrfToken ?>';     // no extra request needed any more

        function showAlert(msg) {
            const a = document.getElementById('alert');
            a.textContent = msg;               // textContent is safe against XSS
            a.classList.remove('d-none');
        }

        document.getElementById('loginForm').addEventListener('submit', async (e) => {
            e.preventDefault();
            document.getElementById('alert').classList.add('d-none');

            const username = document.getElementById('username').value.trim();
            const password = document.getElementById('password').value;
            if (!username || !password) {
                showAlert('Enter your username and password.');
                return;
            }

            const btn = document.getElementById('btn');
            btn.disabled = true;
            try {
                const res = await fetch(API + '?action=login', {
                    method: 'POST',
                    credentials: 'same-origin',
                    headers: { 'Content-Type': 'application/json', 'X-CSRF-Token': csrf },
                    body: JSON.stringify({ username, password, next: document.getElementById('next').value })
                });
                let json;
                try { json = await res.json(); } catch { throw new Error('Invalid server response.'); }
                if (!res.ok || !json.success) {
                    showAlert(json.message || 'Login failed.');
                } else {
                    window.location.href = json.data.redirect;
                }
            } catch (err) {
                showAlert(err.message || 'Network error.');
            } finally {
                btn.disabled = false;
            }
        });
    </script>
</body>

</html>