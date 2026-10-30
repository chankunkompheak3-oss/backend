<?php
// Endpoints (all under /backend/public/api/auth.php):
//   GET  ?action=csrf    -> { data: { token } }          (public)
//   POST ?action=login   -> body {username,password,next} (public + CSRF)
//   POST ?action=logout                                   (needs login + CSRF)
//   GET  ?action=me      -> current user                  (needs login)
define('IS_API', true);
require_once __DIR__ . '/../../config/bootstrap.php';

$action = $_GET['action'] ?? '';
$method = Request::method();
$ctrl   = new AuthController();

if ($action === 'csrf'   && $method === 'GET')  { $ctrl->csrf(); }
if ($action === 'me'     && $method === 'GET')  { $ctrl->me(); }
if ($action === 'login'  && $method === 'POST') { $ctrl->login(); }
if ($action === 'logout' && $method === 'POST') { $ctrl->logout(); }

Response::error('Unknown action or method.', 404);