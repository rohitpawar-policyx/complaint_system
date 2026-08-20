<?php

declare(strict_types=1);

require_once dirname(__DIR__, 2) . '/shared/helpers/helpers.php';
require_once dirname(__DIR__, 2) . '/shared/security/csrf.php';
require_once dirname(__DIR__, 2) . '/shared/middleware/authorization.php';
require_once dirname(__DIR__, 2) . '/auth/authService.php';
require_once __DIR__ . '/complaintService.php';

require_admin();
$complaintId = filter_var($_GET['id'] ?? null, FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]);
if ($complaintId === false) {
    http_response_code(404);
    exit('Complaint not found.');
}

try {
    $complaint = get_admin_complaint($complaintId);
} catch (Throwable $exception) {
    error_log($exception->getMessage());
    http_response_code(503);
    exit('Complaint details are temporarily unavailable.');
}
if ($complaint === null) {
    http_response_code(404);
    exit('Complaint not found.');
}

try {
    $attachments = get_admin_complaint_attachments($complaintId);
    $history = get_admin_complaint_history($complaintId);
    $assignees = get_eligible_assignees();
} catch (Throwable $exception) {
    error_log($exception->getMessage());
    http_response_code(503);
    exit('Complaint details are temporarily unavailable.');
}
$flash = consume_auth_flash();
?>
<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Complaint #<?= e((string) $complaint['id']) ?> | Complaint Management System</title>
    <link rel="stylesheet" href="../../../assets/css/main.css">
</head>
<body>
    <?php require dirname(__DIR__) . '/adminNavigation.php'; ?>
    <main class="page-shell">
        <section class="page-heading">
            <p class="eyebrow">Complaint management</p>
            <h1>Complaint #<?= e((string) $complaint['id']) ?></h1>
            <p><a href="complaintList.php">Back to complaints</a></p>
        </section>
        <?php if ($flash !== null): ?><p class="auth-message auth-message--<?= e($flash['type']) ?>"><?= e($flash['message']) ?></p><?php endif; ?>
        <div class="details-layout">
            <section class="content-card">
                <h2>Complaint information</h2>
                <dl class="account-details">
                    <div><dt>Complainant</dt><dd><?= e($complaint['complainant_name']) ?></dd></div>
                    <div><dt>Email</dt><dd><?= e($complaint['complainant_email']) ?></dd></div>
                    <div><dt>Reason</dt><dd><?= e($complaint['reason_name']) ?></dd></div>
                    <div><dt>Priority</dt><dd><?= e($complaint['priority']) ?></dd></div>
                    <div><dt>Status</dt><dd><?= e($complaint['status']) ?></dd></div>
                    <div><dt>Assignee</dt><dd><?= e($complaint['assignee_name'] ?? 'Unassigned') ?></dd></div>
                    <div><dt>Created</dt><dd><?= e($complaint['created_at']) ?></dd></div>
                    <div><dt>Updated</dt><dd><?= e($complaint['updated_at']) ?></dd></div>
                </dl>
                <h2>Message</h2>
                <p class="complaint-message"><?= nl2br(e($complaint['message'])) ?></p>
            </section>
            <section class="content-card">
                <h2>Manage complaint</h2>
                <form method="post" action="complaintHandler.php" class="admin-form">
                    <input type="hidden" name="csrf_token" value="<?= e(csrf_token()) ?>">
                    <input type="hidden" name="action" value="assign">
                    <input type="hidden" name="complaint_id" value="<?= e((string) $complaint['id']) ?>">
                    <label for="assignee_id">Assign to</label>
                    <select id="assignee_id" name="assignee_id" required>
                        <option value="">Select approved user</option>
                        <?php foreach ($assignees as $assignee): ?><option value="<?= e((string) $assignee['id']) ?>"<?= (int) ($complaint['assignee_id'] ?? 0) === (int) $assignee['id'] ? ' selected' : '' ?>><?= e($assignee['name'] . ' (' . $assignee['email'] . ')') ?></option><?php endforeach; ?>
                    </select>
                    <button type="submit">Save assignment</button>
                </form>
                <hr>
                <form method="post" action="complaintHandler.php" class="admin-form">
                    <input type="hidden" name="csrf_token" value="<?= e(csrf_token()) ?>">
                    <input type="hidden" name="action" value="status">
                    <input type="hidden" name="complaint_id" value="<?= e((string) $complaint['id']) ?>">
                    <label for="new_status">Change status</label>
                    <select id="new_status" name="status" required>
                        <?php foreach (ADMIN_COMPLAINT_STATUSES as $option): ?><option value="<?= e($option) ?>"<?= $complaint['status'] === $option ? ' selected' : '' ?>><?= e($option) ?></option><?php endforeach; ?>
                    </select>
                    <button type="submit">Save status</button>
                </form>
            </section>
        </div>
        <section class="content-card history-card">
            <h2>Attachments</h2>
            <?php if ($attachments === []): ?><p>No attachments were submitted.</p><?php else: ?><ul class="attachment-list"><?php foreach ($attachments as $attachment): ?><li><a href="attachmentDownload.php?id=<?= e((string) $attachment['id']) ?>"><?= e($attachment['original_name']) ?></a><span><?= e(number_format((int) $attachment['file_size'])) ?> bytes</span></li><?php endforeach; ?></ul><?php endif; ?>
        </section>
        <section class="content-card history-card">
            <h2>History</h2>
            <?php if ($history === []): ?><p>No history is available.</p><?php else: ?><ol class="history-list"><?php foreach ($history as $entry): ?><li><div class="history-entry-heading"><strong><?= e($entry['action']) ?></strong><time><?= e($entry['created_at']) ?></time></div><p><?= e($entry['description']) ?></p><p>Performed by <?= e($entry['performed_by_name']) ?></p><?php if ($entry['old_status'] !== null || $entry['new_status'] !== null): ?><p>Status: <?= e($entry['old_status'] ?? 'none') ?> to <?= e($entry['new_status'] ?? 'none') ?></p><?php endif; ?><?php if ($entry['assigned_from_name'] !== null || $entry['assigned_to_name'] !== null): ?><p>Assignment: <?= e($entry['assigned_from_name'] ?? 'Unassigned') ?> to <?= e($entry['assigned_to_name'] ?? 'Unassigned') ?></p><?php endif; ?></li><?php endforeach; ?></ol><?php endif; ?>
        </section>
    </main>
</body>
</html>
