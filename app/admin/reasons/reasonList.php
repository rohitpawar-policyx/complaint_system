<?php

declare(strict_types=1);

require_once dirname(__DIR__, 2) . '/shared/helpers/helpers.php';
require_once dirname(__DIR__, 2) . '/shared/security/csrf.php';
require_once dirname(__DIR__, 2) . '/shared/middleware/authorization.php';
require_once dirname(__DIR__, 2) . '/auth/authService.php';
require_once __DIR__ . '/reasonService.php';

require_admin();
$flash = consume_auth_flash();

try {
    $reasons = get_admin_reasons();
} catch (Throwable $exception) {
    error_log($exception->getMessage());
    http_response_code(503);
    exit('Complaint reasons are temporarily unavailable.');
}
?>
<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Complaint Reasons | Complaint Management System</title>
    <link rel="stylesheet" href="../../../assets/css/main.css">
</head>
<body>
    <?php require dirname(__DIR__) . '/adminNavigation.php'; ?>
    <main class="page-shell">
        <section class="page-heading">
            <p class="eyebrow">Administration</p>
            <h1>Complaint reasons</h1>
            <p>Manage the reasons and server-controlled priorities used for new complaints.</p>
        </section>
        <?php if ($flash !== null): ?><p class="auth-message auth-message--<?= e($flash['type']) ?>"><?= e($flash['message']) ?></p><?php endif; ?>
        <p><a class="button-link" href="reasonForm.php">Create reason</a></p>
        <?php if ($reasons === []): ?>
            <section class="content-card empty-state"><h2>No reasons found</h2><p>Create a complaint reason to make it available to users.</p></section>
        <?php else: ?>
            <section class="content-card table-card"><div class="table-wrapper"><table>
                <thead><tr><th>Name</th><th>Description</th><th>Priority</th><th>Active</th><th>Created</th><th>Updated</th><th><span class="visually-hidden">Actions</span></th></tr></thead>
                <tbody>
                <?php foreach ($reasons as $reason): ?>
                    <tr>
                        <td><?= e($reason['name']) ?></td>
                        <td><?= e($reason['description']) ?></td>
                        <td><?= e($reason['priority']) ?></td>
                        <td><?= (int) $reason['active'] === 1 ? 'Active' : 'Inactive' ?></td>
                        <td><?= e($reason['created_at']) ?></td>
                        <td><?= e($reason['updated_at']) ?></td>
                        <td class="table-actions">
                            <a href="reasonForm.php?id=<?= e((string) $reason['id']) ?>">Edit</a>
                            <form method="post" action="reasonHandler.php" data-confirm="Delete this complaint reason if it is not used by complaints?">
                                <input type="hidden" name="csrf_token" value="<?= e(csrf_token()) ?>">
                                <input type="hidden" name="action" value="delete">
                                <input type="hidden" name="reason_id" value="<?= e((string) $reason['id']) ?>">
                                <button type="submit">Delete</button>
                            </form>
                        </td>
                    </tr>
                <?php endforeach; ?>
                </tbody>
            </table></div></section>
        <?php endif; ?>
    </main>
</body>
</html>
