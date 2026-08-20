<?php

declare(strict_types=1);

require_once dirname(__DIR__, 2) . '/shared/helpers/helpers.php';
require_once dirname(__DIR__, 2) . '/shared/security/csrf.php';
require_once dirname(__DIR__, 2) . '/shared/middleware/authorization.php';
require_once dirname(__DIR__, 2) . '/auth/authService.php';
require_once __DIR__ . '/dashboardService.php';

require_admin();

try {
    $counts = get_admin_dashboard_counts();
} catch (Throwable $exception) {
    error_log($exception->getMessage());
    http_response_code(503);
    exit('Admin dashboard is temporarily unavailable.');
}
?>
<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Admin Dashboard | Complaint Management System</title>
    <link rel="stylesheet" href="../../../assets/css/main.css">
</head>
<body>
    <?php require dirname(__DIR__) . '/adminNavigation.php'; ?>
    <main class="page-shell">
        <section class="page-heading">
            <p class="eyebrow">Administration</p>
            <h1>Admin dashboard</h1>
            <p>Monitor account and complaint activity.</p>
        </section>
        <section class="admin-section">
            <h2>Users</h2>
            <div class="dashboard-grid admin-count-grid">
                <article class="content-card dashboard-card"><span class="dashboard-label">Total users</span><strong><?= e((string) $counts['users']['total']) ?></strong></article>
                <article class="content-card dashboard-card"><span class="dashboard-label">Pending</span><strong><?= e((string) $counts['users']['pending']) ?></strong></article>
                <article class="content-card dashboard-card"><span class="dashboard-label">Approved</span><strong><?= e((string) $counts['users']['approved']) ?></strong></article>
                <article class="content-card dashboard-card"><span class="dashboard-label">Blocked</span><strong><?= e((string) $counts['users']['blocked']) ?></strong></article>
            </div>
        </section>
        <section class="admin-section">
            <h2>Complaints</h2>
            <div class="dashboard-grid admin-count-grid">
                <article class="content-card dashboard-card"><span class="dashboard-label">Total complaints</span><strong><?= e((string) $counts['complaints']['total']) ?></strong></article>
                <article class="content-card dashboard-card"><span class="dashboard-label">Pending</span><strong><?= e((string) $counts['complaints']['pending']) ?></strong></article>
                <article class="content-card dashboard-card"><span class="dashboard-label">In progress</span><strong><?= e((string) $counts['complaints']['in_progress']) ?></strong></article>
                <article class="content-card dashboard-card"><span class="dashboard-label">Resolved</span><strong><?= e((string) $counts['complaints']['resolved']) ?></strong></article>
                <article class="content-card dashboard-card"><span class="dashboard-label">High priority</span><strong><?= e((string) $counts['complaints']['high_priority']) ?></strong></article>
            </div>
        </section>
        <section class="content-card admin-quick-links">
            <h2>Quick links</h2>
            <a class="button-link" href="../dashboard/dashboard.php">Dashboard</a>
            <a class="button-link" href="../users/userList.php">Users</a>
            <a class="button-link" href="../roles/roleList.php">Roles</a>
            <a class="button-link" href="../reasons/reasonList.php">Complaint reasons</a>
            <a class="button-link" href="../complaints/complaintList.php">Complaints</a>
            <a class="button-link" href="../complaint-history/historyList.php">Complaint history</a>
        </section>
    </main>
</body>
</html>
