<?php

require_once __DIR__ . "/../includes/functions.php";

$url = readEnv("DATABASE_URL");

$db = parse_url($url);

$dsn = sprintf(
    "mysql:host=%s;port=%d;dbname=%s;charset=utf8mb4",
    $db['host'],
    $db['port'] ?? 3306,
    ltrim($db['path'], '/')
);

$pdo = new PDO($dsn, $db['user'], $db['pass'] ?? '', [
    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
]);