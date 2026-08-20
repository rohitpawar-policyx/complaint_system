<?php

declare(strict_types=1);

require_once dirname(__DIR__, 2) . '/shared/helpers/helpers.php';
require_once dirname(__DIR__, 2) . '/shared/security/csrf.php';
require_once dirname(__DIR__) . '/authService.php';

$flash = consume_auth_flash();
?><!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Login | Complaint Management System</title>
    <link rel="stylesheet" href="../../../assets/css/main.css">
</head>
<body>
    <main class="auth-page">
        <section class="auth-card">
            <h1>Log in</h1>
            <p>Access your Complaint Management System account.</p>
            <?php if ($flash !== null): ?>
                <p class="auth-message auth-message--<?= e($flash['type']) ?>"><?= e($flash['message']) ?></p>
            <?php endif; ?>
            <form method="post" action="loginHandler.php" class="auth-form">
                <input type="hidden" name="csrf_token" value="<?= e(csrf_token()) ?>">
                <label for="email">Email</label>
                <input id="email" name="email" type="email" maxlength="255" required autocomplete="email">
                <label for="password">Password</label>
                <input id="password" name="password" type="password" required autocomplete="current-password">
                <button type="submit">Log in</button>
            </form>
            <p><a href="../register/register.php">Need an account? Register</a></p>
        </section>
    </main>
</body>
</html>
