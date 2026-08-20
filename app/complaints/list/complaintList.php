<?php

declare(strict_types=1);

require_once dirname(__DIR__, 2) . '/shared/helpers/helpers.php';
require_once dirname(__DIR__, 2) . '/shared/security/csrf.php';
require_once dirname(__DIR__, 2) . '/shared/middleware/auth.php';
require_once dirname(__DIR__, 2) . '/auth/authService.php';
require_once dirname(__DIR__) . '/complaintQueryService.php';

require_authenticated();
$userId = authenticated_user_id();
$complaints = get_user_complaints($userId);
?><!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>My Complaints | Complaint Management System</title>
    <link rel="stylesheet" href="../../../assets/css/main.css">
</head>
<body>
    <header class="site-header">
        <a href="../../dashboard/dashboard.php" class="site-brand">Complaint Management System</a>
        <nav aria-label="Main navigation" class="site-nav">
            <a href="../../dashboard/dashboard.php">Dashboard</a>
            <a href="../../profile/profile.php">Profile</a>
            <a href="../create/complaint.php">Raise complaint</a>
            <a href="complaintList.php" aria-current="page">My complaints</a>
            <form method="post" action="../../auth/logout/logout.php">
                <input type="hidden" name="csrf_token" value="<?= e(csrf_token()) ?>">
                <button type="submit">Log out</button>
            </form>
        </nav>
    </header>
    <main class="page-shell">
        <section class="page-heading">
            <p class="eyebrow">Complaints</p>
            <h1>My complaints</h1>
            <p>Review complaints submitted from your account.</p>
        </section>
        <?php if ($complaints === []): ?>
            <section class="content-card empty-state">
                <h2>No complaints yet</h2>
                <p>When you submit a complaint, it will appear here.</p>
                <a class="button-link" href="../create/complaint.php">Raise a complaint</a>
            </section>
        <?php else: ?>
            <section class="content-card table-card">
                <div class="table-wrapper">
                    <table>
                        <thead>
                            <tr>
                                <th scope="col">ID</th>
                                <th scope="col">Reason</th>
                                <th scope="col">Priority</th>
                                <th scope="col">Status</th>
                                <th scope="col">Created</th>
                                <th scope="col">Updated</th>
                                <th scope="col"><span class="visually-hidden">Actions</span></th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($complaints as $complaint): ?>
                                <tr>
                                    <td>#<?= e((string) $complaint['id']) ?></td>
                                    <td><?= e($complaint['reason_name']) ?></td>
                                    <td><?= e($complaint['priority']) ?></td>
                                    <td><?= e($complaint['status']) ?></td>
                                    <td><?= e($complaint['created_at']) ?></td>
                                    <td><?= e($complaint['updated_at']) ?></td>
                                    <td><a href="../details/complaintDetails.php?id=<?= e((string) $complaint['id']) ?>">View details</a></td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </section>
        <?php endif; ?>
    </main>
</body>
</html>
