<?php

declare(strict_types=1);

require_once dirname(__DIR__) . '/shared/helpers/helpers.php';
require_once dirname(__DIR__) . '/shared/security/csrf.php';
require_once dirname(__DIR__) . '/shared/middleware/auth.php';
require_once dirname(__DIR__) . '/auth/authService.php';
require_once __DIR__ . '/profileService.php';

require_authenticated();
$userId = authenticated_user_id();
$profile = get_user_profile($userId);
$flash = consume_auth_flash();

if ($profile === null) {
    http_response_code(404);
    exit('Profile not found.');
}
?><!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Profile | Complaint Management System</title>
    <link rel="stylesheet" href="../../assets/css/main.css">
</head>
<body>
    <header class="site-header">
        <a href="../dashboard/dashboard.php" class="site-brand">Complaint Management System</a>
        <nav aria-label="Main navigation" class="site-nav">
            <a href="../dashboard/dashboard.php">Dashboard</a>
            <a href="profile.php" aria-current="page">Profile</a>
            <a href="../complaints/create/complaint.php">Raise complaint</a>
            <form method="post" action="../auth/logout/logout.php">
                <input type="hidden" name="csrf_token" value="<?= e(csrf_token()) ?>">
                <button type="submit">Log out</button>
            </form>
        </nav>
    </header>
    <main class="page-shell">
        <section class="page-heading">
            <p class="eyebrow">Account</p>
            <h1>Your profile</h1>
            <p>Review your account details and update your contact information.</p>
        </section>
        <?php if ($flash !== null): ?>
            <p class="auth-message auth-message--<?= e($flash['type']) ?>"><?= e($flash['message']) ?></p>
        <?php endif; ?>
        <div class="profile-layout">
            <section class="content-card">
                <h2>Account information</h2>
                <dl class="account-details">
                    <div><dt>Name</dt><dd><?= e($profile['name']) ?></dd></div>
                    <div><dt>Email</dt><dd><?= e($profile['email']) ?></dd></div>
                    <div><dt>Role</dt><dd><?= e($profile['role_name']) ?></dd></div>
                    <div><dt>Status</dt><dd><?= e($profile['status']) ?></dd></div>
                    <div><dt>Registered</dt><dd><?= e($profile['created_at']) ?></dd></div>
                </dl>
            </section>
            <section class="content-card">
                <h2>Update profile</h2>
                <form method="post" action="profileHandler.php" class="profile-form">
                    <input type="hidden" name="csrf_token" value="<?= e(csrf_token()) ?>">
                    <label for="name">Name</label>
                    <input id="name" name="name" type="text" maxlength="150" value="<?= e($profile['name']) ?>" required autocomplete="name">
                    <label for="email">Email</label>
                    <input id="email" name="email" type="email" maxlength="255" value="<?= e($profile['email']) ?>" required autocomplete="email">
                    <button type="submit">Save changes</button>
                </form>
            </section>
        </div>
    </main>
</body>
</html>
