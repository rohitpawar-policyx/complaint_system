<?php

declare(strict_types=1);

require_once dirname(__DIR__, 2) . '/shared/middleware/auth.php';
require_once dirname(__DIR__, 2) . '/shared/helpers/helpers.php';
require_once dirname(__DIR__) . '/complaintQueryService.php';

function attachment_not_found(): never
{
    http_response_code(404);
    exit('Attachment not found.');
}

require_authenticated();
$userId = authenticated_user_id();
$attachmentId = filter_var($_GET['id'] ?? null, FILTER_VALIDATE_INT, [
    'options' => ['min_range' => 1],
]);

if ($attachmentId === false) {
    attachment_not_found();
}

$attachment = get_user_attachment($attachmentId, $userId);
if ($attachment === null) {
    attachment_not_found();
}

$projectRoot = dirname(__DIR__, 3);
$uploadRoot = realpath($projectRoot . '/storage/uploads');
$relativePath = str_replace("\\", '/', (string) $attachment['file_path']);
$expectedPrefix = 'storage/uploads/';

if ($uploadRoot === false || !str_starts_with($relativePath, $expectedPrefix)) {
    attachment_not_found();
}

$filePath = realpath($projectRoot . '/' . $relativePath);
if ($filePath === false || !is_file($filePath) || !is_readable($filePath)) {
    attachment_not_found();
}

$uploadRootPrefix = rtrim($uploadRoot, DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR;
if (!str_starts_with($filePath, $uploadRootPrefix)) {
    attachment_not_found();
}

$downloadName = preg_replace('/[^A-Za-z0-9._ -]/', '_', basename((string) $attachment['original_name']));
$downloadName = trim((string) $downloadName, '. ');
if ($downloadName === '') {
    $downloadName = 'attachment';
}

$mimeType = (new finfo(FILEINFO_MIME_TYPE))->file($filePath);
if (!is_string($mimeType) || $mimeType === '') {
    $mimeType = 'application/octet-stream';
}

header('Content-Type: ' . $mimeType);
header('Content-Length: ' . (string) filesize($filePath));
header('Content-Disposition: attachment; filename="' . $downloadName . '"');
header('X-Content-Type-Options: nosniff');
readfile($filePath);
exit;
