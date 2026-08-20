<?php

declare(strict_types=1);

require_once dirname(__DIR__, 2) . '/shared/helpers/helpers.php';
require_once dirname(__DIR__, 2) . '/shared/security/csrf.php';
require_once dirname(__DIR__, 2) . '/shared/middleware/authorization.php';
require_once dirname(__DIR__, 2) . '/auth/authService.php';
require_once __DIR__ . '/roleService.php';

require_admin();
$roleId = filter_var($_GET['id'] ?? null, FILTER_VALIDATE_INT, [
    'options' => ['min_range' => 1],
]);
$role = null;

if ($roleId !== false) {
    try {
        $role = get_admin_role($roleId);
    } catch (Throwable $exception) {
        error_log($exception->getMessage());
        http_response_code(503);
        exit('Role is temporarily unavailable.');
    }

    if ($role === null) {
        http_response_code(404);
        exit('Role not found.');
    }
}
?>
<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?= $role === null ? 'Create Role' : 'Edit Role' ?> | Complaint Management System</title>
    <link rel="stylesheet" href="../../../assets/css/main.css">
</head>
<body>
    <?php require dirname(__DIR__) . '/adminNavigation.php'; ?>
    <main class="page-shell">
        <section class="page-heading">
            <p class="eyebrow">Administration</p>
            <h1><?= $role === null ? 'Create role' : 'Edit role' ?></h1>
            <p><a href="roleList.php">Back to roles</a></p>
        </section>
        <section class="content-card admin-form-card">
            <form method="post" action="roleHandler.php" class="admin-form">
                <input type="hidden" name="csrf_token" value="<?= e(csrf_token()) ?>">
                <input type="hidden" name="action" value="<?= $role === null ? 'create' : 'update' ?>">
                <?php if ($role !== null): ?><input type="hidden" name="role_id" value="<?= e((string) $role['id']) ?>"><?php endif; ?>
                <label for="name">Name</label>
                <input id="name" name="name" type="text" maxlength="50" value="<?= e($role['name'] ?? '') ?>" required>
                <label for="description">Description</label>
                <textarea id="description" name="description" rows="5" maxlength="255"><?= e($role['description'] ?? '') ?></textarea>
                <button type="submit"><?= $role === null ? 'Create role' : 'Save changes' ?></button>
            </form>
        </section>
    </main>
</body>
</html>
