<?php

// ===== KONFIGURASI DATABASE =====
// EDIT BAGIAN INI SESUAI HOSTING ANDA
$DB_HOST = 'localhost';
$DB_PORT = 3306;
$DB_NAME = 'rodd1157_dinasti_test';
$DB_USER = 'dinasti_test';
$DB_PASS = 'dinasti_test';
$DB_CHARSET = 'utf8mb4';

// Jika ingin override via environment variable cPanel:
$DB_HOST = getenv('DB_HOST') ?: $DB_HOST;
$DB_PORT = getenv('DB_PORT') ?: $DB_PORT;
$DB_NAME = getenv('DB_NAME') ?: $DB_NAME;
$DB_USER = getenv('DB_USER') ?: $DB_USER;
$DB_PASS = getenv('DB_PASS') ?: $DB_PASS;
// ===== AKHIR KONFIGURASI =====


$mysqli = new mysqli($DB_HOST, $DB_USER, $DB_PASS, $DB_NAME, (int) $DB_PORT);

if ($mysqli->connect_errno) {
    http_response_code(500);
    die('DB Error: ' . $mysqli->connect_error);
}

$mysqli->set_charset($DB_CHARSET);

function db_fetch_all(mysqli $db, string $sql): array
{
    $result = $db->query($sql);
    if (!$result) {
        return [];
    }

    $rows = [];
    while ($row = $result->fetch_assoc()) {
        $rows[] = $row;
    }
    $result->free();

    return $rows;
}

function db_fetch_one(mysqli $db, string $sql): ?array
{
    $result = $db->query($sql);
    if (!$result) {
        return null;
    }

    $row = $result->fetch_assoc();
    $result->free();

    return $row ?: null;
}

function redirect_with_message(string $page, string $message, string $type = 'success'): void
{
    $url = 'index.php?page=' . urlencode($page) . '&msg=' . urlencode($message) . '&type=' . urlencode($type);
    header('Location: ' . $url);
    exit;
}

function esc(?string $value): string
{
    return htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8');
}
