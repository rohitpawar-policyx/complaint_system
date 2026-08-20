<?php

declare(strict_types=1);
?>
<header class="admin-header">
    <a href="../dashboard/dashboard.php" class="site-brand">Complaint Management System</a>
    <nav aria-label="Admin navigation" class="site-nav">
        <a href="../dashboard/dashboard.php">Admin dashboard</a>
        <a href="../users/userList.php">Users</a>
        <a href="../roles/roleList.php">Roles</a>
        <a href="../reasons/reasonList.php">Complaint reasons</a>
        <a href="../complaints/complaintList.php">Complaints</a>
        <a href="../complaint-history/historyList.php">Complaint history</a>
        <form method="post" action="../../auth/logout/logout.php">
            <input type="hidden" name="csrf_token" value="<?= e(csrf_token()) ?>">
            <button type="submit">Log out</button>
        </form>
    </nav>
</header>
<script src="../../../assets/js/main.js" defer></script>
