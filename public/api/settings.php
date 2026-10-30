<?php
define('IS_API', true);
require_once __DIR__ . '/../../config/bootstrap.php';
(new SettingsController())->handle();