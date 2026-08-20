<?php

declare(strict_types=1);

require_once dirname(__DIR__, 2) . '/shared/helpers/helpers.php';
require_once dirname(__DIR__, 2) . '/shared/security/csrf.php';
require_once dirname(__DIR__, 2) . '/shared/middleware/authorization.php';
require_once dirname(__DIR__, 2) . '/auth/authService.php';
require_once __DIR__ . '/complaintService.php';

require_admin();
$status = (string) ($_GET['status'] ?? '');
$priority = (string) ($_GET['priority'] ?? '');
$search = trim((string) ($_GET['search'] ?? ''));

if (!in_array($status, ADMIN_COMPLAINT_STATUSES, true)) {
    $status = '';
}
if (!in_array($priority, ADMIN_COMPLAINT_PRIORITIES, true)) {
    $priority = '';
}
$search = mb_substr($search, 0, 100);

try {
    $complaints = get_admin_complaints($status, $priority, $search);
} catch (Throwable $exception) {
    error_log($exception->getMessage());
    http_response_code(503);
    exit('Complaints are temporarily unavailable.');
}
?>
<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Complaints | Complaint Management System</title>
    <link rel="stylesheet" href="../../../assets/css/main.css">
</head>
<body>
    <?php require dirname(__DIR__) . '/adminNavigation.php'; ?>
    <main class="page-shell">
        <section class="page-heading">
            <p class="eyebrow">Administration</p>
            <h1>Complaint management</h1>
            <p>Review complaints, assignments, status, and history.</p>
        </section>
        <section class="content-card filter-card">
            <form method="get" class="admin-filter-form">
                <label for="status">Status</label>
                <select id="status" name="status">
                    <option value="">All statuses</option>
                    <?php foreach (ADMIN_COMPLAINT_STATUSES as $option): ?><option value="<?= e($option) ?>"<?= $status === $option ? ' selected' : '' ?>><?= e($option) ?></option><?php endforeach; ?>
                </select>
                <label for="priority">Priority</label>
                <select id="priority" name="priority">
                    <option value="">All priorities</option>
                    <?php foreach (ADMIN_COMPLAINT_PRIORITIES as $option): ?><option value="<?= e($option) ?>"<?= $priority === $option ? ' selected' : '' ?>><?= e($option) ?></option><?php endforeach; ?>
                </select>
                <label for="search">Search</label>
                <input id="search" name="search" type="search" maxlength="100" value="<?= e($search) ?>" placeholder="Name, email, reason, or ID">
                <button type="submit">Filter</button>
                <a href="complaintList.php">Clear</a>
            </form>
        </section>
        <?php if ($complaints === []): ?>
            <section class="content-card empty-state"><h2>No complaints found</h2><p>Try changing the filters or wait for a complaint to be submitted.</p></section>
        <?php else: ?>
            <section class="content-card table-card"><div class="table-wrapper"><table>
                <thead><tr><th>ID</th><th>Complainant</th><th>Reason</th><th>Priority</th><th>Status</th><th>Assignee</th><th>Created</th><th>Updated</th><th><span class="visually-hidden">Actions</span></th></tr></thead>
                <tbody>
                <?php foreach ($complaints as $complaint): ?>
                    <tr>
                        <td>#<?= e((string) $complaint['id']) ?></td>
                        <td><?= e($complaint['complainant_name']) ?></td>
                        <td><?= e($complaint['reason_name']) ?></td>
                        <td><?= e($complaint['priority']) ?></td>
                        <td><?= e($complaint['status']) ?></td>
                        <td><?= e($complaint['assignee_name'] ?? 'Unassigned') ?></td>
                        <td><?= e($complaint['created_at']) ?></td>
                        <td><?= e($complaint['updated_at']) ?></td>
                        <td><a href="complaintDetails.php?id=<?= e((string) $complaint['id']) ?>">View details</a></td>
                    </tr>
                <?php endforeach; ?>
                </tbody>
            </table></div></section>
        <?php endif; ?>
    </main>
</body>
</html>
