<?php

declare(strict_types=1);

require_once dirname(__DIR__, 2) . '/shared/helpers/helpers.php';
require_once dirname(__DIR__, 2) . '/shared/security/csrf.php';
require_once dirname(__DIR__, 2) . '/shared/middleware/auth.php';
require_once dirname(__DIR__, 2) . '/auth/authService.php';
require_once dirname(__DIR__) . '/complaintService.php';

require_authenticated();
$flash = consume_auth_flash();

try {
    $reasons = get_active_complaint_reasons();
} catch (Throwable $exception) {
    error_log($exception->getMessage());
    $reasons = [];
    $flash = ['type' => 'error', 'message' => 'Complaint reasons are temporarily unavailable.'];
}
?><!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Raise Complaint | Complaint Management System</title>
    <link rel="stylesheet" href="../../../assets/css/main.css">
</head>
<body>
    <header class="site-header">
        <a href="../../dashboard/dashboard.php" class="site-brand">Complaint Management System</a>
        <nav aria-label="Main navigation" class="site-nav">
            <a href="../../dashboard/dashboard.php">Dashboard</a>
            <a href="../../profile/profile.php">Profile</a>
            <a href="complaint.php" aria-current="page">Raise complaint</a>
            <form method="post" action="../../auth/logout/logout.php">
                <input type="hidden" name="csrf_token" value="<?= e(csrf_token()) ?>">
                <button type="submit">Log out</button>
            </form>
        </nav>
    </header>
    <main class="page-shell">
        <section class="page-heading">
            <p class="eyebrow">Support</p>
            <h1>Raise a complaint</h1>
            <p>Choose a reason and describe the issue. Your complaint priority is assigned by the system.</p>
        </section>
        <?php if ($flash !== null): ?>
            <p class="auth-message auth-message--<?= e($flash['type']) ?>"><?= e($flash['message']) ?></p>
        <?php endif; ?>
        <section class="content-card complaint-form-card">
            <form method="post" action="complaintHandler.php" enctype="multipart/form-data" class="complaint-form">
                <input type="hidden" name="csrf_token" value="<?= e(csrf_token()) ?>">
                <label for="reason_id">Complaint reason</label>
                <select id="reason_id" name="reason_id" required>
                    <option value="">Select a reason</option>
                    <?php foreach ($reasons as $reason): ?>
                        <option value="<?= e((string) $reason['id']) ?>"><?= e($reason['name']) ?></option>
                    <?php endforeach; ?>
                </select>
                <label for="message">Message</label>
                <textarea id="message" name="message" rows="8" maxlength="10000" required></textarea>
                <label for="attachments">Attachments</label>
                <input id="attachments" name="attachments[]" type="file" accept=".pdf,.jpg,.jpeg,.png" multiple>
                <p class="form-help">Optional: up to 5 PDF, JPG, JPEG, or PNG files, 5 MiB each.</p>
                <button type="submit">Submit complaint</button>
            </form>
        </section>
    </main>
</body>
</html>
