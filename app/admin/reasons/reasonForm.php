<?php

declare(strict_types=1);

require_once dirname(__DIR__, 2) . '/shared/helpers/helpers.php';
require_once dirname(__DIR__, 2) . '/shared/security/csrf.php';
require_once dirname(__DIR__, 2) . '/shared/middleware/authorization.php';
require_once dirname(__DIR__, 2) . '/auth/authService.php';
require_once __DIR__ . '/reasonService.php';

require_admin();
$reasonId = filter_var($_GET['id'] ?? null, FILTER_VALIDATE_INT, [
    'options' => ['min_range' => 1],
]);
$reason = null;

if ($reasonId !== false) {
    try {
        $reason = get_admin_reason($reasonId);
    } catch (Throwable $exception) {
        error_log($exception->getMessage());
        http_response_code(503);
        exit('Complaint reason is temporarily unavailable.');
    }

    if ($reason === null) {
        http_response_code(404);
        exit('Complaint reason not found.');
    }
}
?>
<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?= $reason === null ? 'Create Reason' : 'Edit Reason' ?> | Complaint Management System</title>
    <link rel="stylesheet" href="../../../assets/css/main.css">
</head>
<body>
    <?php require dirname(__DIR__) . '/adminNavigation.php'; ?>
    <main class="page-shell">
        <section class="page-heading">
            <p class="eyebrow">Administration</p>
            <h1><?= $reason === null ? 'Create complaint reason' : 'Edit complaint reason' ?></h1>
            <p><a href="reasonList.php">Back to complaint reasons</a></p>
        </section>
        <section class="content-card admin-form-card">
            <form method="post" action="reasonHandler.php" class="admin-form">
                <input type="hidden" name="csrf_token" value="<?= e(csrf_token()) ?>">
                <input type="hidden" name="action" value="<?= $reason === null ? 'create' : 'update' ?>">
                <?php if ($reason !== null): ?><input type="hidden" name="reason_id" value="<?= e((string) $reason['id']) ?>"><?php endif; ?>
                <label for="name">Name</label>
                <input id="name" name="name" type="text" maxlength="150" value="<?= e($reason['name'] ?? '') ?>" required>
                <label for="description">Description</label>
                <textarea id="description" name="description" rows="5"><?= e($reason['description'] ?? '') ?></textarea>
                <label for="priority">Priority</label>
                <select id="priority" name="priority" required>
                    <?php foreach (COMPLAINT_REASON_PRIORITIES as $priority): ?>
                        <option value="<?= e($priority) ?>"<?= ($reason['priority'] ?? '') === $priority ? ' selected' : '' ?>><?= e($priority) ?></option>
                    <?php endforeach; ?>
                </select>
                <label class="checkbox-label"><input type="checkbox" name="active" value="1"<?= $reason === null || (int) $reason['active'] === 1 ? ' checked' : '' ?>> Active for new complaints</label>
                <button type="submit"><?= $reason === null ? 'Create reason' : 'Save changes' ?></button>
            </form>
        </section>
    </main>
</body>
</html>
