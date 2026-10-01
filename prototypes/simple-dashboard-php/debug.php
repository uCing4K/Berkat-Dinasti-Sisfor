<?php
// Debug file - HAPUS SETELAH SELESAI TESTING DI PRODUCTION!

// TAMPILKAN SEMUA ERROR KE BROWSER
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

header('Content-Type: text/plain; charset=utf-8');

echo "=== SIMPLE DASHBOARD PHP - DEBUG ===\n\n";

// Cek file db.php
echo "1. Cek file includes/db.php:\n";
if (!file_exists(__DIR__ . '/includes/db.php')) {
    echo "   ERROR: File includes/db.php TIDAK ADA!\n";
    echo "   Path dicari: " . __DIR__ . '/includes/db.php' . "\n";
    exit;
} else {
    echo "   ✓ File ditemukan\n";
}

// Load db config dengan error handling
echo "\n2. Load file db.php:\n";
try {
    require __DIR__ . '/includes/db.php';
    echo "   ✓ File berhasil di-load\n";
} catch (Throwable $e) {
    echo "   ERROR saat load: " . $e->getMessage() . "\n";
    echo "   File: " . $e->getFile() . "\n";
    echo "   Line: " . $e->getLine() . "\n";
    exit;
}

// Cek variabel database
echo "\n3. Cek konfigurasi database:\n";
echo "   - Host: " . (isset($DB_HOST) ? $DB_HOST : 'UNDEFINED') . "\n";
echo "   - Port: " . (isset($DB_PORT) ? $DB_PORT : 'UNDEFINED') . "\n";
echo "   - Database: " . (isset($DB_NAME) ? $DB_NAME : 'UNDEFINED') . "\n";
echo "   - User: " . (isset($DB_USER) ? $DB_USER : 'UNDEFINED') . "\n";
echo "   - Password: " . (isset($DB_PASS) ? '[***SET***]' : 'UNDEFINED') . "\n";

// Cek PHP
echo "\n4. Cek PHP Environment:\n";
echo "   - PHP Version: " . phpversion() . "\n";
echo "   - MySQLi Extension: " . (extension_loaded('mysqli') ? 'YES ✓' : 'NO ✗') . "\n";

// Test koneksi
echo "\n5. Test koneksi database:\n";
if (!isset($mysqli)) {
    echo "   ERROR: Variable \$mysqli tidak di-set dari db.php\n";
    exit;
}

if ($mysqli->connect_errno) {
    echo "   ERROR: " . $mysqli->connect_error . " (errno: " . $mysqli->connect_errno . ")\n";
    echo "\n   Troubleshooting:\n";
    echo "   - Cek DB_HOST (harus 'localhost')\n";
    echo "   - Cek DB_NAME (harus 'rodd1157_dinasti_test')\n";
    echo "   - Cek DB_USER (harus 'dinasti_test')\n";
    echo "   - Cek DB_PASS (harus 'dinasti_test')\n";
} else {
    echo "   ✓ Koneksi BERHASIL!\n";
    
    // Test query
    echo "\n6. Test query:\n";
    $result = $mysqli->query('SELECT 1 AS ok');
    if ($result) {
        $row = $result->fetch_assoc();
        echo "   ✓ Query SELECT berhasil\n";
        $result->free();
    } else {
        echo "   ERROR query: " . $mysqli->error . "\n";
    }
    
    // Cek tabel
    $result = $mysqli->query('SELECT COUNT(*) AS cnt FROM pelanggan');
    if ($result) {
        $row = $result->fetch_assoc();
        echo "   ✓ Tabel pelanggan ada, " . $row['cnt'] . " data\n";
        $result->free();
    } else {
        echo "   ERROR: Tabel pelanggan tidak ada atau error query\n";
        echo "   Detail: " . $mysqli->error . "\n";
    }
}

echo "\n=== STATUS ===\n";
echo "Jika semua ✓, maka dashboard siap diakses: index.php\n";
echo "Jika masih ada ERROR, share output ini untuk debug lebih lanjut.\n";
