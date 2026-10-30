<?php
// Expects: $page (menu key), $title, $subtitle (optional), $needCharts (optional)
$user = Auth::user();
if ($user['must_change_password'] && $page !== 'settings') {
    header('Location: ' . BASE_URL . '/setting.php?force=1');
    exit;
}
$e = fn($s) => htmlspecialchars((string)$s, ENT_QUOTES, 'UTF-8');
$settings  = Setting::get();
$roleLabel = $user['role'] === 'Admin' ? 'Administrator' : 'Staff';
$appJs = json_encode([
    'role' => $user['role'], 'isAdmin' => $user['role'] === 'Admin',
    'currency' => $settings['currency'], 'taxRate' => (float)$settings['tax_rate_percent'],
], JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_AMP | JSON_HEX_QUOT);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="<?= $e(Auth::csrfToken()) ?>">
    <meta name="base-url" content="<?= $e(BASE_URL) ?>">
    <title><?= $e($title) ?> - Car Rental Management</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.7/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="<?= BASE_URL ?>/css/dashboard.css">
    <link rel="stylesheet" href="<?= BASE_URL ?>/css/app.css">
    <script>window.APP = <?= $appJs ?>;</script>
</head>
<body>
<?php require __DIR__ . '/sidebar.php'; ?>
<main class="contain">
    <header class="top-header">
        <div class="dashboard-intro">
            <nav class="crumbs">Home / <?= $e($title) ?></nav>
            <h2><?= $e($title) ?></h2>
            <?php if (!empty($subtitle)): ?><small><?= $e($subtitle) ?></small><?php endif; ?>
        </div>
        <div class="header-right">
            <button type="button" class="menu-toggle" id="menuToggle" aria-label="Menu"><i class="fa-solid fa-bars"></i></button>
            <span class="header-date"><i class="fa-regular fa-calendar me-1"></i><?= date('d M Y') ?></span>
            <button type="button" class="notification-btn" aria-label="Notifications"><i class="fa-regular fa-bell"></i></button>
        </div>
    </header>