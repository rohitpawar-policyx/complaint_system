<?php

declare(strict_types=1);

const MAX_COMPLAINT_ATTACHMENTS = 5;
const MAX_COMPLAINT_ATTACHMENT_SIZE = 5242880;

function complaint_attachment_rules(): array
{
    return [
        'pdf' => ['application/pdf'],
        'jpg' => ['image/jpeg'],
        'jpeg' => ['image/jpeg'],
        'png' => ['image/png'],
    ];
}

function validate_complaint_attachments(array $files): array
{
    if ($files === [] || !isset($files['name'])) {
        return [];
    }

    $names = is_array($files['name']) ? $files['name'] : [$files['name']];
    $tmpNames = is_array($files['tmp_name']) ? $files['tmp_name'] : [$files['tmp_name']];
    $errors = is_array($files['error']) ? $files['error'] : [$files['error']];
    $sizes = is_array($files['size']) ? $files['size'] : [$files['size']];

    $uploadedFiles = [];
    foreach ($errors as $index => $error) {
        if ($error === UPLOAD_ERR_NO_FILE) {
            continue;
        }

        if ($error !== UPLOAD_ERR_OK) {
            throw new RuntimeException('An attachment could not be uploaded.');
        }

        $uploadedFiles[] = [
            'name' => (string) ($names[$index] ?? ''),
            'tmp_name' => (string) ($tmpNames[$index] ?? ''),
            'size' => (int) ($sizes[$index] ?? 0),
        ];
    }

    if (count($uploadedFiles) > MAX_COMPLAINT_ATTACHMENTS) {
        throw new RuntimeException('Too many attachments.');
    }

    $rules = complaint_attachment_rules();
    $fileInfo = new finfo(FILEINFO_MIME_TYPE);
    $validatedFiles = [];

    foreach ($uploadedFiles as $file) {
        if ($file['size'] < 1 || $file['size'] > MAX_COMPLAINT_ATTACHMENT_SIZE) {
            throw new RuntimeException('Each attachment must be 5 MiB or smaller.');
        }

        if (!is_uploaded_file($file['tmp_name'])) {
            throw new RuntimeException('An attachment could not be verified.');
        }

        $extension = strtolower(pathinfo(basename($file['name']), PATHINFO_EXTENSION));
        if (!isset($rules[$extension])) {
            throw new RuntimeException('One or more attachment types are not supported.');
        }

        $mimeType = $fileInfo->file($file['tmp_name']);
        if (!is_string($mimeType) || !in_array($mimeType, $rules[$extension], true)) {
            throw new RuntimeException('One or more attachments failed type validation.');
        }

        $validatedFiles[] = [
            'tmp_name' => $file['tmp_name'],
            'original_name' => mb_substr(basename($file['name']), 0, 255),
            'extension' => $extension,
            'mime_type' => $mimeType,
            'file_size' => $file['size'],
        ];
    }

    return $validatedFiles;
}

function move_complaint_attachment(array $attachment): array
{
    $directory = dirname(__DIR__, 2) . '/storage/uploads';
    if (!is_dir($directory) && !mkdir($directory, 0750, true) && !is_dir($directory)) {
        throw new RuntimeException('Attachment storage is unavailable.');
    }

    if (!is_writable($directory)) {
        error_log('Complaint attachment directory is not writable: ' . $directory);
        throw new RuntimeException('Attachment storage is unavailable.');
    }

    $storedName = bin2hex(random_bytes(16)) . '.' . $attachment['extension'];
    $absolutePath = $directory . DIRECTORY_SEPARATOR . $storedName;

    if (!move_uploaded_file($attachment['tmp_name'], $absolutePath)) {
        $lastError = error_get_last();
        error_log(sprintf(
            'Complaint attachment move failed: source=%s destination=%s error=%s',
            $attachment['tmp_name'],
            $absolutePath,
            $lastError['message'] ?? 'unknown error'
        ));
        throw new RuntimeException('An attachment could not be stored.');
    }

    return [
        'original_name' => $attachment['original_name'],
        'stored_name' => $storedName,
        'file_path' => 'storage/uploads/' . $storedName,
        'absolute_path' => $absolutePath,
        'mime_type' => $attachment['mime_type'],
        'file_size' => $attachment['file_size'],
    ];
}
