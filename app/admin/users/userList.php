<?php

declare(strict_types=1);

require_once dirname(__DIR__, 2) . '/shared/helpers/helpers.php';
require_once dirname(__DIR__, 2) . '/shared/security/csrf.php';
require_once dirname(__DIR__, 2) . '/shared/middleware/authorization.php';
require_once dirname(__DIR__, 2) . '/auth/authService.php';
require_once __DIR__ . '/userService.php';

require_admin();

try {
    $users = get_admin_users();
} catch (Throwable $exception) {
    error_log($exception->getMessage());
    http_response_code(503);
    exit('User list is temporarily unavailable.');
}
?>
<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Users | Complaint Management System</title>
    <link rel="stylesheet" href="../../../assets/css/main.css">
</head>
<body>
    <?php require dirname(__DIR__) . '/adminNavigation.php'; ?>
    <main class="page-shell">
        <section class="page-heading">
            <p class="eyebrow">Administration</p>
            <h1>Users</h1>
            <p>Review accounts and manage their status.</p>
        </section>
        <section class="content-card table-card">
            <div class="table-wrapper">
                <table>
                    <thead>
                        <tr>
                            <th scope="col">ID</th>
                            <th scope="col">Name</th>
                            <th scope="col">Email</th>
                            <th scope="col">Role</th>
                            <th scope="col">Status</th>
                            <th scope="col">Created</th>
                            <th scope="col">Updated</th>
                            <th scope="col"><span class="visually-hidden">Actions</span></th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($users as $user): ?>
                            <tr>
                                <td>#<?= e((string) $user['id']) ?></td>
                                <td><?= e($user['name']) ?></td>
                                <td><?= e($user['email']) ?></td>
                                <td><?= e($user['role_name']) ?></td>
                                <td><?= e($user['status']) ?></td>
                                <td><?= e($user['created_at']) ?></td>
                                <td><?= e($user['updated_at']) ?></td>
                                <td><a href="userDetails.php?id=<?= e((string) $user['id']) ?>">View details</a></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </section>
    </main>
</body>
</html>
