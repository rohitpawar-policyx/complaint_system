<?php
// echo '</pre>';
// echo 'hello';
// echo '</pre';
// die;
declare(strict_types=1);

require_once dirname(__DIR__, 2) . '/shared/helpers/helpers.php';
require_once dirname(__DIR__, 2) . '/shared/security/csrf.php';
require_once dirname(__DIR__) . '/authService.php';

$flash = consume_auth_flash();
start_secure_session();
?><!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Register | Complaint Management System</title>
    <link rel="stylesheet" href="../../../assets/css/main.css">
</head>
<body>
    <main class="auth-page">
        <section class="auth-card">
            <h1>Create an account</h1>
            <p>Register to use the Complaint Management System.</p>
            <?php if ($flash !== null): ?>
                <p class="auth-message auth-message--<?= e($flash['type']) ?>"><?= e($flash['message']) ?></p>
            <?php endif; ?>
            <form method="post" action="registerHandler.php" class="auth-form">
                <input type="hidden" name="csrf_token" value="<?= e(csrf_token()) ?>">
                <label for="name">Name</label>
                <input id="name" name="name" type="text" maxlength="150" required autocomplete="name">
                <label for="email">Email</label>
                <input id="email" name="email" type="email" maxlength="255" required autocomplete="email">
                <label for="password">Password</label>
                <input id="password" name="password" type="password" minlength="8" required autocomplete="new-password">
                <label for="password_confirmation">Confirm password</label>
                <input id="password_confirmation" name="password_confirmation" type="password" minlength="8" required autocomplete="new-password">
                <button type="submit">Register</button>
            </form>
            <p><a href="../login/login.php">Already have an account? Log in</a></p>
        </section>
    </main>
</body>
</html>
