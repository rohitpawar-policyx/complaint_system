<?php

declare(strict_types=1);

require_once dirname(__DIR__, 2) . '/shared/helpers/helpers.php';
require_once dirname(__DIR__, 2) . '/shared/security/csrf.php';
require_once dirname(__DIR__, 2) . '/shared/middleware/authorization.php';
require_once dirname(__DIR__, 2) . '/auth/authService.php';
require_once __DIR__ . '/userService.php';

require_admin();
$userId = filter_var($_GET['id'] ?? null, FILTER_VALIDATE_INT, [
    'options' => ['min_range' => 1],
]);

if ($userId === false) {
    http_response_code(404);
    exit('User not found.');
}

try {
    $user = get_admin_user($userId);
} catch (Throwable $exception) {
    error_log($exception->getMessage());
    http_response_code(503);
    exit('User details are temporarily unavailable.');
}

if ($user === null) {
    http_response_code(404);
    exit('User not found.');
}

$adminId = authenticated_user_id();
$flash = consume_auth_flash();
?>
<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>User #<?= e((string) $user['id']) ?> | Complaint Management System</title>
    <link rel="stylesheet" href="../../../assets/css/main.css">
</head>
<body>
    <?php require dirname(__DIR__) . '/adminNavigation.php'; ?>
    <main class="page-shell">
        <section class="page-heading">
            <p class="eyebrow">User management</p>
            <h1>User #<?= e((string) $user['id']) ?></h1>
            <p><a href="userList.php">Back to users</a></p>
        </section>
        <?php if ($flash !== null): ?>
            <p class="auth-message auth-message--<?= e($flash['type']) ?>"><?= e($flash['message']) ?></p>
        <?php endif; ?>
        <section class="content-card user-details-card">
            <h2>Account information</h2>
            <dl class="account-details">
                <div><dt>Name</dt><dd><?= e($user['name']) ?></dd></div>
                <div><dt>Email</dt><dd><?= e($user['email']) ?></dd></div>
                <div><dt>Role</dt><dd><?= e($user['role_name']) ?></dd></div>
                <div><dt>Status</dt><dd><?= e($user['status']) ?></dd></div>
                <div><dt>Created</dt><dd><?= e($user['created_at']) ?></dd></div>
                <div><dt>Updated</dt><dd><?= e($user['updated_at']) ?></dd></div>
            </dl>
        </section>
        <section class="content-card status-card">
            <h2>Account status</h2>
            <?php if ($adminId === (int) $user['id']): ?>
                <p>Your own account status cannot be changed here.</p>
            <?php else: ?>
                <form method="post" action="userStatusHandler.php" class="status-form">
                    <input type="hidden" name="csrf_token" value="<?= e(csrf_token()) ?>">
                    <input type="hidden" name="user_id" value="<?= e((string) $user['id']) ?>">
                    <label for="status">Set status</label>
                    <select id="status" name="status" required>
                        <?php foreach (ADMIN_USER_STATUSES as $status): ?>
                            <option value="<?= e($status) ?>"<?= $user['status'] === $status ? ' selected' : '' ?>><?= e($status) ?></option>
                        <?php endforeach; ?>
                    </select>
                    <button type="submit">Update status</button>
                </form>
            <?php endif; ?>
        </section>
    </main>
</body>
</html>
