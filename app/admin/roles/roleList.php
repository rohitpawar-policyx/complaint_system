<?php

declare(strict_types=1);

require_once dirname(__DIR__, 2) . '/shared/helpers/helpers.php';
require_once dirname(__DIR__, 2) . '/shared/security/csrf.php';
require_once dirname(__DIR__, 2) . '/shared/middleware/authorization.php';
require_once dirname(__DIR__, 2) . '/auth/authService.php';
require_once __DIR__ . '/roleService.php';

require_admin();
$flash = consume_auth_flash();

try {
    $roles = get_admin_roles();
} catch (Throwable $exception) {
    error_log($exception->getMessage());
    http_response_code(503);
    exit('Roles are temporarily unavailable.');
}
?>
<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Roles | Complaint Management System</title>
    <link rel="stylesheet" href="../../../assets/css/main.css">
</head>
<body>
    <?php require dirname(__DIR__) . '/adminNavigation.php'; ?>
    <main class="page-shell">
        <section class="page-heading">
            <p class="eyebrow">Administration</p>
            <h1>Roles</h1>
            <p>Manage the role definitions used by the system.</p>
        </section>
        <?php if ($flash !== null): ?>
            <p class="auth-message auth-message--<?= e($flash['type']) ?>"><?= e($flash['message']) ?></p>
        <?php endif; ?>
        <p><a class="button-link" href="roleForm.php">Create role</a></p>
        <?php if ($roles === []): ?>
            <section class="content-card empty-state"><h2>No roles found</h2><p>Create a role to make it available for future administration.</p></section>
        <?php else: ?>
            <section class="content-card table-card">
                <div class="table-wrapper">
                    <table>
                        <thead><tr><th>Name</th><th>Description</th><th>Created</th><th>Updated</th><th><span class="visually-hidden">Actions</span></th></tr></thead>
                        <tbody>
                        <?php foreach ($roles as $role): ?>
                            <tr>
                                <td><?= e($role['name']) ?></td>
                                <td><?= e($role['description']) ?></td>
                                <td><?= e($role['created_at']) ?></td>
                                <td><?= e($role['updated_at']) ?></td>
                                <td class="table-actions">
                                    <a href="roleForm.php?id=<?= e((string) $role['id']) ?>">Edit</a>
                                    <form method="post" action="roleHandler.php" data-confirm="Delete this role if it is not protected or in use?">
                                        <input type="hidden" name="csrf_token" value="<?= e(csrf_token()) ?>">
                                        <input type="hidden" name="action" value="delete">
                                        <input type="hidden" name="role_id" value="<?= e((string) $role['id']) ?>">
                                        <button type="submit">Delete</button>
                                    </form>
                                </td>
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
