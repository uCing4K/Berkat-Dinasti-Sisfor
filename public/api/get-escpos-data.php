<?php
/**
 * API Generator ESC/POS dengan Tuning Teks Tajam & Support Logo
 * Sistem Informasi Berkat Dinasti (Bakery & Pastry)
 * 
 * Sesuai Dokumen Panduan: PANDUAN_IMPLEMENTASI_PRINT_BRIDGE_DAN_TUNING_THERMAL.md
 */

session_start();
header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Private-Network, Authorization, X-Requested-With');
header('Access-Control-Allow-Private-Network: true');

if (isset($_SERVER['REQUEST_METHOD']) && $_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}

// Lokasi Logo Bakery
$logoPath = __DIR__ . '/../assets/images/logo.png';
if (!file_exists($logoPath)) {
    $altLogo = __DIR__ . '/../../docs/DATA/LOGO DINASTI_NEW.png';
    if (file_exists($altLogo)) {
        $logoPath = $altLogo;
    }
}

// Parameter Request
$orderParam = trim($_GET['id'] ?? $_POST['id'] ?? $_GET['order_id'] ?? $_POST['order_id'] ?? '');
$paperSize = trim($_GET['paper_size'] ?? $_POST['paper_size'] ?? '');

// Inisialisasi Database Connection
$db_connected = false;
$pdo = null;
$configFile = __DIR__ . "/../../config/config.php";
if (file_exists($configFile)) {
    require_once $configFile;
    try {
        if (defined('DB_HOST') && defined('DB_NAME') && defined('DB_USER') && defined('DB_PASS')) {
            $pdo = new PDO("mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=utf8mb4", DB_USER, DB_PASS, [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
            ]);
            $db_connected = true;
        }
    } catch (Exception $e) {
        $db_connected = false;
    }
}

// Ambil Pengaturan Toko
$store = [
    'name' => 'BERKAT DINASTI',
    'tagline' => 'Spesialis Roti & Pastry Pesanan',
    'address' => 'Jl. Panglima Sudirman No. 42, Ngaglik',
    'city' => 'Kota Batu, Jawa Timur',
    'wa' => '0812-3456-7890',
    'footer' => 'Terima kasih atas pesanan Anda di Berkat Dinasti! Simpan struk ini sebagai bukti pemesanan yang sah.',
    'paper_size' => '58'
];

if (isset($_SESSION['store_settings']) && is_array($_SESSION['store_settings'])) {
    $store['name'] = $_SESSION['store_settings']['name'] ?? $store['name'];
    $store['address'] = $_SESSION['store_settings']['address'] ?? $store['address'];
    $store['city'] = $_SESSION['store_settings']['city'] ?? $store['city'];
    $store['wa'] = $_SESSION['store_settings']['wa'] ?? $store['wa'];
    $store['footer'] = $_SESSION['store_settings']['footer'] ?? $store['footer'];
    $store['paper_size'] = $_SESSION['store_settings']['paper_size'] ?? $store['paper_size'];
}

if ($db_connected && $pdo) {
    try {
        $stmtS = $pdo->query("SELECT setting_key, setting_value FROM setting");
        while ($sRow = $stmtS->fetch()) {
            if ($sRow['setting_key'] === 'nama_toko' && !empty($sRow['setting_value'])) $store['name'] = $sRow['setting_value'];
            if ($sRow['setting_key'] === 'alamat_toko' && !empty($sRow['setting_value'])) $store['address'] = $sRow['setting_value'];
            if ($sRow['setting_key'] === 'no_wa_toko' && !empty($sRow['setting_value'])) $store['wa'] = $sRow['setting_value'];
        }
    } catch (Exception $e) {}
}

if (empty($paperSize)) {
    $paperSize = $store['paper_size'];
}
$colWidth = ($paperSize === '80') ? 48 : 32;

// Data Order yang akan dicetak
$order = null;
$items = [];

$isTest = empty($orderParam) || strtolower($orderParam) === 'test' || strtolower($orderParam) === 'uji_coba';

if ($isTest) {
    // Dataset Uji Coba (Test Print)
    $minH4 = date('d M Y', strtotime('+4 days'));
    $order = [
        'id' => 999,
        'no_invoice' => 'INV-TEST-' . date('md') . '-0001',
        'tgl_pesan' => date('Y-m-d H:i:s'),
        'tgl_kirim' => date('Y-m-d', strtotime('+4 days')),
        'waktu_kirim' => '09:00:00',
        'customer_name' => 'Ibu Sari Dewi',
        'customer_phone' => '0812-3456-7890',
        'customer_address' => 'Komplek Melati No. 4, Blok B, Kota Batu',
        'status_bayar' => 'lunas',
        'metode_bayar' => 'cash',
        'grand_total' => 144000,
        'catatan' => 'Uji coba cetak thermal printer Bluetooth & tuning teks jernih.'
    ];

    $items = [
        [
            'product_name' => 'Roti Hajatan (9 Topping)',
            'packaging' => 'Kardus Putih',
            'qty' => 10,
            'price' => 10000,
            'subtotal' => 100000
        ],
        [
            'product_name' => 'Bolu Pisang Premium',
            'packaging' => 'Mika',
            'qty' => 2,
            'price' => 22000,
            'subtotal' => 44000
        ]
    ];
} else {
    // 1. Cari di Database MySQL jika ada
    if ($db_connected && $pdo) {
        try {
            $stmt = $pdo->prepare("
                SELECT p.*, pel.nama as customer_name, pel.no_wa as customer_phone, pel.alamat as customer_address
                FROM pesanan p
                LEFT JOIN pelanggan pel ON p.id_pelanggan = pel.id_pelanggan
                WHERE p.id_pesanan = ? OR p.no_invoice = ?
                LIMIT 1
            ");
            $stmt->execute([$orderParam, $orderParam]);
            $order = $stmt->fetch();

            if ($order) {
                $stmtDetail = $pdo->prepare("
                    SELECT dp.*, COALESCE(vp.nama_varian, 'Roti Pesanan') as product_name, COALESCE(vp.kemasan, 'Satuan') as packaging
                    FROM detail_pesanan dp
                    LEFT JOIN varian_produk vp ON dp.id_varian = vp.id_varian
                    WHERE dp.id_pesanan = ?
                ");
                $stmtDetail->execute([$order['id_pesanan']]);
                $dbItems = $stmtDetail->fetchAll();
                foreach ($dbItems as $di) {
                    $items[] = [
                        'product_name' => $di['product_name'],
                        'packaging' => $di['packaging'],
                        'qty' => intval($di['qty']),
                        'price' => floatval($di['harga_satuan']),
                        'subtotal' => floatval($di['subtotal'])
                    ];
                }
            }
        } catch (Exception $e) {}
    }

    // 2. Fallback: Cari di Session (custom_orders / session_orders)
    if (!$order && isset($_SESSION['custom_orders'])) {
        foreach ($_SESSION['custom_orders'] as $co) {
            if ($co['id'] == $orderParam) {
                $order = [
                    'id' => $co['id'],
                    'no_invoice' => $co['id'],
                    'tgl_pesan' => date('Y-m-d H:i:s'),
                    'tgl_kirim' => $co['date'] ?? date('Y-m-d', strtotime('+4 days')),
                    'waktu_kirim' => $co['time'] ?? '09:00:00',
                    'customer_name' => $co['customer'] ?? 'Pelanggan',
                    'customer_phone' => $co['phone'] ?? '-',
                    'customer_address' => $co['destination'] ?? 'Ambil di Toko',
                    'status_bayar' => $co['status_bayar'] ?? 'lunas',
                    'metode_bayar' => 'cash',
                    'grand_total' => $co['total'] ?? 0,
                    'catatan' => $co['items'] ?? ''
                ];
                $items[] = [
                    'product_name' => $co['items'] ?? 'Roti Pesanan',
                    'packaging' => 'Kardus',
                    'qty' => 1,
                    'price' => $co['total'] ?? 0,
                    'subtotal' => $co['total'] ?? 0
                ];
                break;
            }
        }
    }

    // 3. Fallback: Cari di Session session_orders
    if (!$order && isset($_SESSION['session_orders'])) {
        foreach ($_SESSION['session_orders'] as $so) {
            if ($so['id'] == $orderParam) {
                $cleanTotal = intval(preg_replace('/[^0-9]/', '', $so['total'] ?? '0'));
                $order = [
                    'id' => $so['id'],
                    'no_invoice' => $so['id'],
                    'tgl_pesan' => date('Y-m-d H:i:s'),
                    'tgl_kirim' => $so['date'] ?? date('Y-m-d', strtotime('+4 days')),
                    'waktu_kirim' => $so['time'] ?? '09:00:00',
                    'customer_name' => $so['customer'] ?? 'Pelanggan',
                    'customer_phone' => $so['no_wa'] ?? '-',
                    'customer_address' => $so['alamat'] ?? 'Ambil di Toko',
                    'status_bayar' => strtolower($so['payment_status'] ?? 'lunas'),
                    'metode_bayar' => 'cash',
                    'grand_total' => $cleanTotal,
                    'catatan' => $so['product'] ?? ''
                ];
                $items[] = [
                    'product_name' => $so['product'] ?? 'Roti Pesanan',
                    'packaging' => 'Kardus',
                    'qty' => 1,
                    'price' => $cleanTotal,
                    'subtotal' => $cleanTotal
                ];
                break;
            }
        }
    }
}

if (!$order) {
    http_response_code(404);
    echo json_encode([
        'success' => false,
        'error' => "Pesanan dengan ID / Nomor Invoice '{$orderParam}' tidak ditemukan."
    ]);
    exit;
}

// Generate Raw Bytes ESC/POS
try {
    $escposBytes = buildReceiptESCPOS($store, $order, $items, $logoPath, $colWidth);

    echo json_encode([
        'success'    => true,
        'escpos'     => $escposBytes,
        'byte_count' => count($escposBytes),
        'paper_size' => $paperSize . 'mm',
        'col_width'  => $colWidth,
        'order'      => [
            'id'           => $order['id'] ?? $order['no_invoice'],
            'no_invoice'   => $order['no_invoice'] ?? ('#' . $order['id']),
            'customer'     => $order['customer_name'] ?? 'Pelanggan',
            'grand_total'  => $order['grand_total'] ?? 0
        ]
    ]);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'error'   => 'Gagal menyusun data ESC/POS: ' . $e->getMessage()
    ]);
}

/**
 * =========================================================================
 * FUNGSI GENERATOR ESC/POS UTAMA
 * Sesuai Standar Panduan Tuning Thermal (Native Font, ESC 3 24, Anti-White-Line)
 * =========================================================================
 */
function buildReceiptESCPOS($store, $order, $items, $logoPath, $colWidth = 32) {
    $c = [];

    // 1. Reset Printer & Inisialisasi
    $c[] = 0x1B; $c[] = 0x40; // ESC @

    // 2. Set Print Density (GS ( K - Level 5: Pekat & Tajam)
    $c[] = 0x1D; $c[] = 0x28; $c[] = 0x4B; $c[] = 0x02; $c[] = 0x00; $c[] = 0x31; $c[] = 0x05;

    // 3. Cetak Logo Toko (Jika Ada & GD Tersedia)
    if (file_exists($logoPath) && function_exists('imagecreatefrompng')) {
        $logoWidth = ($colWidth === 48) ? 360 : 280; // Sesuaikan lebar logo dengan kertas
        appendOptimizedLogo($c, $logoPath, $logoWidth);
    }

    // 4. Header Identitas Toko (Center, Bold, Double Size)
    appendAlign($c, 1); // Center
    appendBold($c, true);
    appendDoubleSize($c, true);
    appendText($c, ($store['name'] ?? 'BERKAT DINASTI') . "\n");
    appendDoubleSize($c, false);
    appendBold($c, false);

    if (!empty($store['tagline'])) {
        appendBold($c, true);
        appendText($c, $store['tagline'] . "\n");
        appendBold($c, false);
    }
    if (!empty($store['address'])) {
        appendText($c, $store['address'] . "\n");
    }
    if (!empty($store['city'])) {
        appendText($c, $store['city'] . "\n");
    }
    if (!empty($store['wa'])) {
        appendText($c, "WhatsApp: " . $store['wa'] . "\n");
    }

    appendSeparator($c, '=', $colWidth);

    // 5. Informasi Nota & Transaksi (Left)
    appendAlign($c, 0); // Left
    appendRow2Col($c, "No. Invoice:", $order['no_invoice'] ?? ('#' . $order['id']), $colWidth);
    
    $tglPesanStr = !empty($order['tgl_pesan']) ? date('d/m/Y H:i', strtotime($order['tgl_pesan'])) : date('d/m/Y H:i');
    appendRow2Col($c, "Tgl Pesan  :", $tglPesanStr, $colWidth);

    // Status & Metode Bayar
    $sb = strtoupper(str_replace('_', ' ', $order['status_bayar'] ?? 'LUNAS'));
    $mb = strtoupper($order['metode_bayar'] ?? 'CASH');
    appendRow2Col($c, "Pembayaran :", "{$sb} ({$mb})", $colWidth);

    appendSeparator($c, '-', $colWidth);

    // 6. Data Pelanggan (Bold & Double Height agar Kurir / Kasir Langsung Terbaca)
    appendAlign($c, 1); // Center
    appendText($c, "DATA PELANGGAN & PRE-ORDER:\n");
    appendBold($c, true);
    appendDoubleHeight($c, true);
    appendText($c, ($order['customer_name'] ?? 'Pelanggan Umum') . "\n");
    appendDoubleHeight($c, false);
    appendBold($c, false);

    appendAlign($c, 0); // Kembali Left
    if (!empty($order['customer_phone'])) {
        appendRow2Col($c, "No. Telepon:", $order['customer_phone'], $colWidth);
    }
    if (!empty($order['customer_address'])) {
        appendWrappedLine($c, "Alamat: " . $order['customer_address'], $colWidth);
    }

    // Informasi Deadline / Kirim (Pre-Order)
    $tglKirimStr = !empty($order['tgl_kirim']) ? date('d M Y', strtotime($order['tgl_kirim'])) : date('d M Y', strtotime('+4 days'));
    $wktKirimStr = !empty($order['waktu_kirim']) ? substr($order['waktu_kirim'], 0, 5) : '09:00';
    appendBold($c, true);
    appendRow2Col($c, "TGL KIRIM/PO:", "{$tglKirimStr} {$wktKirimStr}", $colWidth);
    appendBold($c, false);

    appendSeparator($c, '-', $colWidth);

    // 7. Daftar Rincian Roti / Produk
    appendBold($c, true);
    appendText($c, "RINCIAN ITEM PESANAN:\n");
    appendBold($c, false);

    $totalCalculated = 0;
    foreach ($items as $it) {
        $pName = $it['product_name'] ?? 'Roti Pesanan';
        $pPkg = !empty($it['packaging']) ? " (" . $it['packaging'] . ")" : "";
        $pQty = intval($it['qty'] ?? 1);
        $pHrg = floatval($it['price'] ?? 0);
        $pSub = floatval($it['subtotal'] ?? ($pQty * $pHrg));
        $totalCalculated += $pSub;

        // Nama Roti Tebal
        appendBold($c, true);
        appendWrappedLine($c, $pName . $pPkg, $colWidth);
        appendBold($c, false);

        // Kuantitas x Harga -> Subtotal
        $left = "  " . $pQty . " pcs x Rp " . number_format($pHrg, 0, ',', '.');
        $right = "Rp " . number_format($pSub, 0, ',', '.');
        appendRow2Col($c, $left, $right, $colWidth);
    }

    appendSeparator($c, '-', $colWidth);

    // 8. Ringkasan Biaya & Grand Total
    $grandTotal = $order['grand_total'] ?? $totalCalculated;
    appendRow2Col($c, "Subtotal", "Rp " . number_format($totalCalculated, 0, ',', '.'), $colWidth);
    appendRow2Col($c, "Ongkir (Malang & Batu)", "Rp 0 (Bebas)", $colWidth);

    appendSeparator($c, '-', $colWidth);

    // Grand Total (Bold, Double Height)
    appendBold($c, true);
    appendDoubleHeight($c, true);
    $totalText = "Rp " . number_format($grandTotal, 0, ',', '.');
    appendRow2Col($c, "TOTAL AKHIR:", $totalText, $colWidth);
    appendDoubleHeight($c, false);
    appendBold($c, false);

    appendSeparator($c, '=', $colWidth);

    // 9. Catatan Khusus (Jika Ada)
    if (!empty($order['catatan'])) {
        appendAlign($c, 0);
        appendBold($c, true);
        appendText($c, "Catatan Khusus:\n");
        appendBold($c, false);
        appendWrappedLine($c, $order['catatan'], $colWidth);
        appendSeparator($c, '-', $colWidth);
    }

    // 10. Footer Pesan & Terima Kasih
    appendAlign($c, 1); // Center
    if (!empty($store['footer'])) {
        appendWrappedLine($c, $store['footer'], $colWidth);
        appendText($c, "\n");
    }
    appendText($c, "Dapur Berkat Dinasti\n");
    appendText($c, "Kualitas Terjamin, Roti Fresh Setiap Hari\n\n");

    // 11. Feed Kertas & Auto-Cut
    $c[] = 0x0A;
    $c[] = 0x0A;
    $c[] = 0x0A;
    $c[] = 0x1D; $c[] = 0x56; $c[] = 0x42; $c[] = 0x00; // GS V 66 0 (Cut with feed)

    return $c;
}

/**
 * =========================================================================
 * KONVERSI GAMBAR LOGO KE ESC * 33 (ANTI GARIS PUTIH & TEPI TAJAM)
 * Sesuai Bab 5.3 & 5.4 Panduan Tuning
 * =========================================================================
 */
function appendOptimizedLogo(&$c, $imagePath, $targetWidth = 280) {
    if (!function_exists('getimagesize')) return;
    $info = @getimagesize($imagePath);
    if (!$info) return;

    $mime = $info['mime'];
    $src = null;
    if ($mime === 'image/png' && function_exists('imagecreatefrompng')) {
        $src = @imagecreatefrompng($imagePath);
    } elseif (($mime === 'image/jpeg' || $mime === 'image/jpg') && function_exists('imagecreatefromjpeg')) {
        $src = @imagecreatefromjpeg($imagePath);
    } elseif (($mime === 'image/bmp' || $mime === 'image/x-ms-bmp') && function_exists('imagecreatefrombmp')) {
        $src = @imagecreatefrombmp($imagePath);
    }

    if (!$src) return;

    $srcW = imagesx($src);
    $srcH = imagesy($src);
    if ($srcW <= 0 || $srcH <= 0) {
        @imagedestroy($src);
        return;
    }

    // Rasio aspek
    $ratio = $targetWidth / $srcW;
    $scaledH = intval($srcH * $ratio);
    // Tinggi total wajib kelipatan 24 dots
    $targetHeight = intval(ceil($scaledH / 24) * 24);
    if ($targetHeight <= 0) $targetHeight = 24;

    // Canvas latar putih bersih (menghapus transparansi PNG)
    $canvas = imagecreatetruecolor($targetWidth, $targetHeight);
    $white = imagecolorallocate($canvas, 255, 255, 255);
    imagefilledrectangle($canvas, 0, 0, $targetWidth, $targetHeight, $white);

    // Salin gambar ke tengah canvas
    $offsetY = intval(($targetHeight - $scaledH) / 2);
    imagecopyresampled($canvas, $src, 0, $offsetY, 0, 0, $targetWidth, $scaledH, $srcW, $srcH);
    @imagedestroy($src);

    // Grayscale + Naikkan Kontras (+20%)
    if (function_exists('imagefilter')) {
        imagefilter($canvas, IMG_FILTER_GRAYSCALE);
        imagefilter($canvas, IMG_FILTER_CONTRAST, -20); // -20 di PHP = meningkatkan kontras
    }

    // KRUSIAL PANDUAN: Set Line Spacing ke 24 dots (Mencegah garis horisontal putih)
    $c[] = 0x1B; $c[] = 0x33; $c[] = 24; // ESC 3 24
    appendAlign($c, 1); // Center logo

    // Iterasi strip vertikal 24-dot
    for ($y = 0; $y < $targetHeight; $y += 24) {
        $c[] = 0x1B; $c[] = 0x2A; $c[] = 33; // ESC * 33 (24-dot double density)
        $c[] = $targetWidth & 0xFF;         // nL (Low byte)
        $c[] = ($targetWidth >> 8) & 0xFF;  // nH (High byte)

        for ($x = 0; $x < $targetWidth; $x++) {
            for ($k = 0; $k < 3; $k++) {
                $byte = 0;
                for ($bit = 0; $bit < 8; $bit++) {
                    $py = $y + ($k * 8) + $bit;
                    if ($py < $targetHeight) {
                        $rgb = imagecolorat($canvas, $x, $py);
                        $gray = $rgb & 0xFF; // Ambil nilai grayscale
                        // Ambang thresholding 140 (Tajam, tanpa noise)
                        if ($gray < 140) {
                            $byte |= (0x80 >> $bit);
                        }
                    }
                }
                $c[] = $byte;
            }
        }
        $c[] = 0x0A; // Feed 1 strip 24-dots
    }

    @imagedestroy($canvas);

    // KRUSIAL: Reset Line Spacing ke Default (ESC 2)
    $c[] = 0x1B; $c[] = 0x32; // ESC 2
}

/**
 * =========================================================================
 * HELPER PENYUSUN RAW BYTES ESC/POS
 * =========================================================================
 */
function appendText(&$c, $str) {
    // Sanitasi ke ASCII murni agar tidak merusak chip printer thermal
    $ascii = iconv('UTF-8', 'ASCII//TRANSLIT//IGNORE', $str);
    for ($i = 0; $i < strlen($ascii); $i++) {
        $c[] = ord($ascii[$i]);
    }
}

function appendAlign(&$c, $mode) {
    // 0: Left, 1: Center, 2: Right
    $c[] = 0x1B; $c[] = 0x61; $c[] = $mode;
}

function appendBold(&$c, $on) {
    $c[] = 0x1B; $c[] = 0x45; $c[] = ($on ? 1 : 0);
}

function appendDoubleSize(&$c, $on) {
    $c[] = 0x1D; $c[] = 0x21; $c[] = ($on ? 0x11 : 0x00);
}

function appendDoubleHeight(&$c, $on) {
    $c[] = 0x1D; $c[] = 0x21; $c[] = ($on ? 0x01 : 0x00);
}

function appendSeparator(&$c, $char = '-', $width = 32) {
    appendText($c, str_repeat($char, $width) . "\n");
}

function appendRow2Col(&$c, $left, $right, $width = 32) {
    $leftLen = strlen($left);
    $rightLen = strlen($right);
    $spaces = $width - ($leftLen + $rightLen);
    if ($spaces < 1) $spaces = 1;
    appendText($c, $left . str_repeat(' ', $spaces) . $right . "\n");
}

function appendWrappedLine(&$c, $text, $width = 32) {
    $words = explode(' ', $text);
    $currentLine = '';
    foreach ($words as $w) {
        if (strlen($currentLine) + strlen($w) + 1 <= $width) {
            $currentLine .= ($currentLine === '' ? '' : ' ') . $w;
        } else {
            if (!empty($currentLine)) {
                appendText($c, $currentLine . "\n");
            }
            $currentLine = $w;
        }
    }
    if (!empty($currentLine)) {
        appendText($c, $currentLine . "\n");
    }
}
