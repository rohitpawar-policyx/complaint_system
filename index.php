<?php

declare(strict_types=1);

require_once __DIR__ . '/app/shared/helpers/helpers.php';
require_once __DIR__ . '/app/shared/security/session.php';
require_once __DIR__ . '/app/shared/security/csrf.php';
require_once __DIR__ . '/app/shared/middleware/auth.php';

start_secure_session();
?><!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Complaint Management System</title>
    <link rel="stylesheet" href="assets/css/main.css">
</head>
<body>
    <main>
        <h1>Complaint Management System</h1>
        <p>Policy guidance and complaint support in one place.</p>
        <section class="content-card home-policy-card">
            <h2>Policy guidance</h2>
            <p>Review the relevant policy information before raising a complaint.</p>
            <ul>
                <li>Provide clear, accurate information about the issue.</li>
                <li>Use the complaint reason that best describes your concern.</li>
                <li>Attach supporting documents only when they are relevant and safe to share.</li>
            </ul>
        </section>
        <?php if (authenticated_user_id() !== null): ?>
            <p>You are logged in.</p>
            <p><a href="app/dashboard/dashboard.php">Dashboard</a> | <a href="app/profile/profile.php">Profile</a> | <a href="app/complaints/create/complaint.php">Raise complaint</a> | <a href="app/complaints/list/complaintList.php">My complaints</a><?php if (($_SESSION['role_name'] ?? null) === 'admin'): ?> | <a href="app/admin/dashboard/dashboard.php">Admin dashboard</a><?php endif; ?></p>
            <form method="post" action="app/auth/logout/logout.php">
                <input type="hidden" name="csrf_token" value="<?= e(csrf_token()) ?>">
                <button type="submit">Log out</button>
            </form>
        <?php else: ?>
            <p><a href="app/auth/register/register.php">Register</a> or <a href="app/auth/login/login.php">log in</a>.</p>
        <?php endif; ?>
    </main>
    <script src="assets/js/main.js"></script>
</body>
</html>
