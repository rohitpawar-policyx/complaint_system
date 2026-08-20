<?php

declare(strict_types=1);

function database(): PDO
{
    static $connection;

    if ($connection instanceof PDO) {
        return $connection;
    }

    $host = getenv('CMS_DB_HOST') ?: '127.0.0.1';
    $port = getenv('CMS_DB_PORT') ?: '3306';
    $database = getenv('CMS_DB_NAME') ?: '';
    $username = getenv('CMS_DB_USER') ?: '';
    $password = getenv('CMS_DB_PASSWORD') ?: '';

    if ($database === '' || $username === '') {
        throw new RuntimeException('Database configuration is incomplete. Set CMS_DB_NAME and CMS_DB_USER.');
    }

    $dsn = "mysql:host={$host};port={$port};dbname={$database};charset=utf8mb4";

    $connection = new PDO($dsn, $username, $password, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES => false,
    ]);

    return $connection;
}
