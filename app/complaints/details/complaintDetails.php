<?php

declare(strict_types=1);

require_once dirname(__DIR__, 2) . '/shared/helpers/helpers.php';
require_once dirname(__DIR__, 2) . '/shared/security/csrf.php';
require_once dirname(__DIR__, 2) . '/shared/middleware/auth.php';
require_once dirname(__DIR__, 2) . '/auth/authService.php';
require_once dirname(__DIR__) . '/complaintQueryService.php';

require_authenticated();
$userId = authenticated_user_id();
$complaintId = filter_var($_GET['id'] ?? null, FILTER_VALIDATE_INT, [
    'options' => ['min_range' => 1],
]);

if ($complaintId === false) {
    http_response_code(404);
    exit('Complaint not found.');
}

$complaint = get_user_complaint($complaintId, $userId);
if ($complaint === null) {
    http_response_code(404);
    exit('Complaint not found.');
}

$attachments = get_user_complaint_attachments($complaintId, $userId);
$history = get_user_complaint_history($complaintId, $userId);
?><!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Complaint #<?= e((string) $complaint['id']) ?> | Complaint Management System</title>
    <link rel="stylesheet" href="../../../assets/css/main.css">
</head>
<body>
    <header class="site-header">
        <a href="../../dashboard/dashboard.php" class="site-brand">Complaint Management System</a>
        <nav aria-label="Main navigation" class="site-nav">
            <a href="../../dashboard/dashboard.php">Dashboard</a>
            <a href="../../profile/profile.php">Profile</a>
            <a href="../create/complaint.php">Raise complaint</a>
            <a href="../list/complaintList.php" aria-current="page">My complaints</a>
            <form method="post" action="../../auth/logout/logout.php">
                <input type="hidden" name="csrf_token" value="<?= e(csrf_token()) ?>">
                <button type="submit">Log out</button>
            </form>
        </nav>
    </header>
    <main class="page-shell">
        <section class="page-heading">
            <p class="eyebrow">Complaint details</p>
            <h1>Complaint #<?= e((string) $complaint['id']) ?></h1>
            <p><a href="../list/complaintList.php">Back to my complaints</a></p>
        </section>
        <div class="details-layout">
            <section class="content-card">
                <h2>Complaint information</h2>
                <dl class="account-details">
                    <div><dt>Reason</dt><dd><?= e($complaint['reason_name']) ?></dd></div>
                    <div><dt>Priority</dt><dd><?= e($complaint['priority']) ?></dd></div>
                    <div><dt>Status</dt><dd><?= e($complaint['status']) ?></dd></div>
                    <div><dt>Created</dt><dd><?= e($complaint['created_at']) ?></dd></div>
                    <div><dt>Updated</dt><dd><?= e($complaint['updated_at']) ?></dd></div>
                </dl>
                <h2>Message</h2>
                <p class="complaint-message"><?= nl2br(e($complaint['message'])) ?></p>
            </section>
            <section class="content-card">
                <h2>Attachments</h2>
                <?php if ($attachments === []): ?>
                    <p>No attachments were submitted.</p>
                <?php else: ?>
                    <ul class="attachment-list">
                        <?php foreach ($attachments as $attachment): ?>
                            <li>
                                <a href="attachmentDownload.php?id=<?= e((string) $attachment['id']) ?>">
                                    <?= e($attachment['original_name']) ?>
                                </a>
                                <span><?= e(number_format((int) $attachment['file_size'])) ?> bytes</span>
                            </li>
                        <?php endforeach; ?>
                    </ul>
                <?php endif; ?>
            </section>
        </div>
        <section class="content-card history-card">
            <h2>History</h2>
            <?php if ($history === []): ?>
                <p>No history is available.</p>
            <?php else: ?>
                <ol class="history-list">
                    <?php foreach ($history as $entry): ?>
                        <li>
                            <div class="history-entry-heading">
                                <strong><?= e($entry['action']) ?></strong>
                                <time><?= e($entry['created_at']) ?></time>
                            </div>
                            <p><?= e($entry['description']) ?></p>
                            <?php if ($entry['old_status'] !== null || $entry['new_status'] !== null): ?>
                                <p>Status: <?= e($entry['old_status'] ?? 'none') ?> to <?= e($entry['new_status'] ?? 'none') ?></p>
                            <?php endif; ?>
                            <p>Performed by <?= e($entry['performed_by_name']) ?></p>
                        </li>
                    <?php endforeach; ?>
                </ol>
            <?php endif; ?>
        </section>
    </main>
</body>
</html>
