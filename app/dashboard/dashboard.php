<?php

declare(strict_types=1);

require_once dirname(__DIR__) . '/shared/helpers/helpers.php';
require_once dirname(__DIR__) . '/shared/security/csrf.php';
require_once dirname(__DIR__) . '/shared/middleware/auth.php';
require_once dirname(__DIR__) . '/auth/authService.php';
require_once dirname(__DIR__) . '/profile/profileService.php';
require_once __DIR__ . '/dashboardService.php';

require_authenticated();
$userId = authenticated_user_id();
$profile = get_user_profile($userId);
$complaintCounts = get_user_dashboard_counts($userId);

if ($profile === null) {
    http_response_code(404);
    exit('Account not found.');
}
?><!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Dashboard | Complaint Management System</title>
    <link rel="stylesheet" href="../../assets/css/main.css">
</head>
<body>
    <header class="site-header">
        <a href="dashboard.php" class="site-brand">Complaint Management System</a>
        <nav aria-label="Main navigation" class="site-nav">
            <a href="dashboard.php" aria-current="page">Dashboard</a>
            <a href="../profile/profile.php">Profile</a>
            <a href="../complaints/create/complaint.php">Raise complaint</a>
            <a href="../complaints/list/complaintList.php">My complaints</a>
            <form method="post" action="../auth/logout/logout.php">
                <input type="hidden" name="csrf_token" value="<?= e(csrf_token()) ?>">
                <button type="submit">Log out</button>
            </form>
        </nav>
    </header>
    <main class="page-shell">
        <section class="page-heading">
            <p class="eyebrow">Overview</p>
            <h1>Welcome, <?= e($profile['name']) ?></h1>
            <p>Your account is ready for the next stage of the complaint system.</p>
        </section>
        <section class="dashboard-grid" aria-label="Account summary">
            <article class="content-card dashboard-card">
                <span class="dashboard-label">Account status</span>
                <strong><?= e($profile['status']) ?></strong>
            </article>
            <article class="content-card dashboard-card">
                <span class="dashboard-label">Role</span>
                <strong><?= e($profile['role_name']) ?></strong>
            </article>
            <article class="content-card dashboard-card">
                <span class="dashboard-label">Registered</span>
                <strong><?= e($profile['created_at']) ?></strong>
            </article>
        </section>
        <section class="admin-section">
            <h2>My complaint summary</h2>
            <div class="dashboard-grid">
                <article class="content-card dashboard-card"><span class="dashboard-label">Total complaints</span><strong><?= e((string) $complaintCounts['total']) ?></strong></article>
                <article class="content-card dashboard-card"><span class="dashboard-label">Pending</span><strong><?= e((string) $complaintCounts['pending']) ?></strong></article>
                <article class="content-card dashboard-card"><span class="dashboard-label">In progress</span><strong><?= e((string) $complaintCounts['in_progress']) ?></strong></article>
                <article class="content-card dashboard-card"><span class="dashboard-label">Resolved</span><strong><?= e((string) $complaintCounts['resolved']) ?></strong></article>
            </div>
        </section>
        <section class="content-card dashboard-section">
            <h2>Account details</h2>
            <p><strong>Email:</strong> <?= e($profile['email']) ?></p>
            <p>Review your submitted complaints and their current status.</p>
            <a class="button-link" href="../complaints/list/complaintList.php">View my complaints</a>
            <a class="button-link" href="../complaints/create/complaint.php">Raise complaint</a>
            <a class="button-link" href="../profile/profile.php">Manage profile</a>
        </section>
    </main>
</body>
</html>
