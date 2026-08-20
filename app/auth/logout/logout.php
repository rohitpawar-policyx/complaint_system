<?php

declare(strict_types=1);

require_once dirname(__DIR__) . '/authService.php';
require_once dirname(__DIR__, 2) . '/shared/security/csrf.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !verify_csrf_token($_POST['csrf_token'] ?? null)) {
	http_response_code(400);
	exit('Invalid logout request.');
}

logout_user();
header('Location: ../login/login.php');
exit;
