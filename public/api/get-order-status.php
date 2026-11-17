<?php
/**
 * Public Order Status API Endpoint
 * Sistem Informasi Berkat Dinasti (Bakery & Pastry UMKM)
 * 
 * Mengembalikan data status pesanan, rincian item, dan timeline dapur
 * berbasis capability token (Zero-Login). Dilindungi rate limiter dan PII masking.
 */

header('Content-Type: application/json; charset=utf-8');
header('X-Robots-Tag: noindex, nofollow');
header('Referrer-Policy: no-referrer');
header('X-Content-Type-Options: nosniff');
header('X-Frame-Options: DENY');

$configFile = __DIR__ . "/../../config/config.php";
if (file_exists($configFile)) {
    require_once $configFile;
}
require_once __DIR__ . "/../../app/Helpers/ReceiptHelper.php";

// Rate Limiting (50 requests / 60 seconds)
ReceiptHelper::enforceRateLimit(50, 60);

$token = isset($_GET['token']) ? trim($_GET['token']) : '';
if (!ReceiptHelper::isValidTrackingToken($token)) {
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'message' => 'Format token tidak valid. Harus 32 karakter hex.'
    ]);
    exit();
}

try {
    if (!defined('DB_HOST') || !defined('DB_NAME') || !defined('DB_USER')) {
        throw new Exception("Konfigurasi database tidak ditemukan.");
    }

    $pdo = new PDO("mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=utf8mb4", DB_USER, DB_PASS, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
    ]);

    // Ambil Data Pesanan
    $stmt = $pdo->prepare("
        SELECT 
            p.id_pesanan,
            p.no_invoice,
            p.tgl_pesan,
            p.tgl_kirim,
            p.waktu_kirim,
            p.grand_total,
            p.status,
            p.status_bayar,
            p.catatan,
            p.tracking_token,
            p.created_at,
            pel.nama_pelanggan,
            pel.no_wa,
            pel.alamat
        FROM pesanan p
        LEFT JOIN pelanggan pel ON p.id_pelanggan = pel.id_pelanggan
        WHERE p.tracking_token = ?
        LIMIT 1
    ");
    $stmt->execute([$token]);
    $order = $stmt->fetch();

    if (!$order) {
        http_response_code(404);
        echo json_encode([
            'success' => false,
            'message' => 'Pesanan tidak ditemukan.'
        ]);
        exit();
    }

    // Ambil Rincian Item
    $stmtItems = $pdo->prepare("
        SELECT 
            dp.qty,
            dp.harga_satuan,
            dp.subtotal,
            dp.topping,
            vp.nama_varian,
            prod.nama_produk
        FROM detail_pesanan dp
        LEFT JOIN varian_produk vp ON dp.id_varian = vp.id_varian
        LEFT JOIN produk prod ON vp.id_produk = prod.id_produk
        WHERE dp.id_pesanan = ?
    ");
    $stmtItems->execute([$order['id_pesanan']]);
    $items = $stmtItems->fetchAll();

    // Timeline History
    $timeline = [];
    try {
        $stmtLogs = $pdo->prepare("SELECT status, keterangan, created_at FROM tracking_log WHERE id_pesanan = ? ORDER BY created_at ASC");
        $stmtLogs->execute([$order['id_pesanan']]);
        $timeline = $stmtLogs->fetchAll();
    } catch (Exception $e) {}

    echo json_encode([
        'success' => true,
        'data' => [
            'order_number' => $order['no_invoice'],
            'customer_name' => htmlspecialchars($order['nama_pelanggan'] ?? 'Pelanggan'),
            'customer_phone_masked' => ReceiptHelper::maskPhoneNumber($order['no_wa']),
            'customer_address_masked' => ReceiptHelper::maskAddress($order['alamat']),
            'order_date' => $order['tgl_pesan'],
            'delivery_date' => $order['tgl_kirim'],
            'delivery_time' => $order['waktu_kirim'],
            'grand_total' => (float)$order['grand_total'],
            'grand_total_formatted' => 'Rp ' . number_format((float)$order['grand_total'], 0, ',', '.'),
            'order_status' => $order['status'],
            'payment_status' => $order['status_bayar'],
            'notes' => $order['catatan'],
            'items' => $items,
            'timeline' => $timeline,
            'receipt_url' => ReceiptHelper::getDigitalReceiptUrl($order['tracking_token'])
        ]
    ]);

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Terjadi kesalahan sistem internal.'
    ]);
}
