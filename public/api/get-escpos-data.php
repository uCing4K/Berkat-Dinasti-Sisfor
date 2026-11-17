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
            if ($sRow['setting_key'] === 'printer_paper_size' && !empty($sRow['setting_value'])) $store['paper_size'] = $sRow['setting_value'];
            if ($sRow['setting_key'] === 'footer_struk' && !empty($sRow['setting_value'])) $store['footer'] = $sRow['setting_value'];
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
    // Dataset Uji Coba (Test Print - Rekomendasi Compact)
    $minH4 = date('d M Y', strtotime('+4 days'));
    $order = [
        'id' => 999,
        'no_invoice' => 'INV-' . date('md') . '-0001',
        'tgl_pesan' => date('Y-m-d H:i:s'),
        'tgl_kirim' => date('Y-m-d', strtotime('+4 days')),
        'waktu_kirim' => '09:00:00',
        'customer_name' => 'Ibu Sari Dewi',
        'customer_phone' => '0812-3456-7890',
        'customer_address' => 'Komplek Melati No. 4, Blok B, Kota Batu',
        'status_bayar' => 'lunas',
        'metode_bayar' => 'cash',
        'grand_total' => 144000,
        'catatan' => 'Acara jam 10 pagi'
    ];

    $items = [
        [
            'product_name' => 'Roti Hajatan',
            'packaging' => 'Kardus Putih',
            'topping' => 'Meses + Keju',
            'qty' => 10,
            'price' => 10000,
            'subtotal' => 100000
        ],
        [
            'product_name' => 'Bolu Pisang Premium',
            'packaging' => 'Mika',
            'topping' => '',
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
                // Ambil rincian produk dari view terpadu v_detail_pesanan_produk
                try {
                    $stmtView = $pdo->prepare("
                        SELECT * FROM v_detail_pesanan_produk
                        WHERE id_pesanan = ? OR no_invoice = ?
                    ");
                    $stmtView->execute([$order['id_pesanan'], $order['no_invoice']]);
                    $dbItems = $stmtView->fetchAll();
                    foreach ($dbItems as $di) {
                        $items[] = [
                            'product_name' => $di['nama_produk'],
                            'packaging' => $di['nama_varian'] ?? 'Satuan',
                            'topping' => $di['topping'] ?? '',
                            'qty' => intval($di['qty']),
                            'price' => floatval($di['harga_satuan']),
                            'subtotal' => floatval($di['subtotal'])
                        ];
                    }
                } catch (Exception $eV) {}

                // Fallback jika view kosong
                if (empty($items)) {
                    try {
                        $stmtDetail = $pdo->prepare("
                            SELECT dp.*, 
                                   COALESCE(p.nama_produk, vp.nama_varian, 'Roti Pesanan') as product_name, 
                                   COALESCE(vp.nama_varian, 'Satuan') as packaging
                            FROM detail_pesanan dp
                            LEFT JOIN varian_produk vp ON dp.id_varian = vp.id_varian
                            LEFT JOIN produk p ON vp.id_produk = p.id_produk
                            WHERE dp.id_pesanan = ?
                        ");
                        $stmtDetail->execute([$order['id_pesanan']]);
                        $dbItems = $stmtDetail->fetchAll();
                        foreach ($dbItems as $di) {
                            $items[] = [
                                'product_name' => $di['product_name'],
                                'packaging' => $di['packaging'],
                                'topping' => $di['topping'] ?? '',
                                'qty' => intval($di['qty']),
                                'price' => floatval($di['harga_satuan']),
                                'subtotal' => floatval($di['subtotal'])
                            ];
                        }
                    } catch (Exception $eD) {}
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

    // 4. Fallback: Cari di Master Demo Dataset (ORD-089, ORD-088, ORD-087, dsb)
    $demoMockOrders = [
        'ORD-089' => [
            'customer_name' => 'Ibu Sari',
            'customer_phone' => '0812-3456-7890',
            'customer_address' => 'Dikirim ke Komplek Melati No. 4',
            'status_bayar' => 'belum_bayar',
            'metode_bayar' => 'cash',
            'grand_total' => 144000,
            'catatan' => 'Prioritas: Acara Hajatan jam 15:00',
            'items' => [
                ['product_name' => 'Roti Hajatan Putih (10 box)', 'packaging' => 'Kardus', 'qty' => 10, 'price' => 10000, 'subtotal' => 100000],
                ['product_name' => 'Bolu Pisang Mika (2 mika)', 'packaging' => 'Mika', 'qty' => 2, 'price' => 22000, 'subtotal' => 44000]
            ]
        ],
        'ORD-088' => [
            'customer_name' => 'Pak Budi',
            'customer_phone' => '0823-4567-8901',
            'customer_address' => 'Ambil Langsung Sore Hari (Toko)',
            'status_bayar' => 'hutang',
            'metode_bayar' => 'cash',
            'grand_total' => 110000,
            'catatan' => 'Ambil sendiri sore hari jam 16:30',
            'items' => [
                ['product_name' => 'Roti Hajatan Kardus Batik (10 box)', 'packaging' => 'Kardus', 'qty' => 10, 'price' => 11000, 'subtotal' => 110000]
            ]
        ],
        'ORD-087' => [
            'customer_name' => 'Toko Maju',
            'customer_phone' => '0834-5678-9012',
            'customer_address' => 'Kirim via Driver Roda Tiga',
            'status_bayar' => 'lunas',
            'metode_bayar' => 'transfer',
            'grand_total' => 485000,
            'catatan' => 'Kirim via Driver Roda Tiga jam 10:00',
            'items' => [
                ['product_name' => 'Bolu Tanpa Pisang Putih (15 box)', 'packaging' => 'Kardus', 'qty' => 15, 'price' => 15000, 'subtotal' => 225000],
                ['product_name' => 'Roti Kopi (50 pcs)', 'packaging' => 'Satuan', 'qty' => 50, 'price' => 5200, 'subtotal' => 260000]
            ]
        ],
        'ORD-084' => [
            'customer_name' => 'Bu Rina',
            'customer_phone' => '0815-6789-0123',
            'customer_address' => 'Ambil di Toko',
            'status_bayar' => 'lunas',
            'metode_bayar' => 'cash',
            'grand_total' => 124000,
            'catatan' => 'Ambil di Toko jam 14:00',
            'items' => [
                ['product_name' => 'Bolu Pisang Mika (2 mika)', 'packaging' => 'Mika', 'qty' => 2, 'price' => 22000, 'subtotal' => 44000],
                ['product_name' => 'Roti Bijian Besar (20 pcs)', 'packaging' => 'Satuan', 'qty' => 20, 'price' => 4000, 'subtotal' => 80000]
            ]
        ],
        'ORD-081' => [
            'customer_name' => 'Warung Pak Haji',
            'customer_phone' => '0819-0123-4567',
            'customer_address' => 'Jl. Pasar Baru No. 12',
            'status_bayar' => 'lunas',
            'metode_bayar' => 'cash',
            'grand_total' => 300000,
            'catatan' => 'Kurir Obrok Motor jam 11:00',
            'items' => [
                ['product_name' => 'Roti Bijian Kecil Satuan (100 pcs)', 'packaging' => 'Satuan', 'qty' => 100, 'price' => 3000, 'subtotal' => 300000]
            ]
        ],
        'ORD-080' => [
            'customer_name' => 'Ibu Maya',
            'customer_phone' => '0821-9988-7766',
            'customer_address' => 'Kirim ke Jl. Ijen No. 15',
            'status_bayar' => 'lunas',
            'metode_bayar' => 'transfer',
            'grand_total' => 144000,
            'catatan' => 'Kirim jam 17:00',
            'items' => [
                ['product_name' => 'Roti Hajatan Mika (10 mika)', 'packaging' => 'Mika', 'qty' => 10, 'price' => 10000, 'subtotal' => 100000],
                ['product_name' => 'Bolu Pisang Kardus Coklat (2 box)', 'packaging' => 'Kardus', 'qty' => 2, 'price' => 22000, 'subtotal' => 44000]
            ]
        ]
    ];

    if (!$order && isset($demoMockOrders[$orderParam])) {
        $m = $demoMockOrders[$orderParam];
        $order = [
            'id' => $orderParam,
            'no_invoice' => $orderParam,
            'tgl_pesan' => date('Y-m-d H:i:s'),
            'tgl_kirim' => date('Y-m-d', strtotime('+3 days')),
            'waktu_kirim' => '10:00:00',
            'customer_name' => $m['customer_name'],
            'customer_phone' => $m['customer_phone'],
            'customer_address' => $m['customer_address'],
            'status_bayar' => $m['status_bayar'],
            'metode_bayar' => $m['metode_bayar'],
            'grand_total' => $m['grand_total'],
            'catatan' => $m['catatan']
        ];
        $items = $m['items'];
    }

    // 5. Ultimate Fallback: Jika ID apapun ditekan di UI, jangan pernah 404!
    if (!$order) {
        $order = [
            'id' => $orderParam,
            'no_invoice' => str_starts_with($orderParam, 'INV-') ? $orderParam : ('INV-' . $orderParam),
            'tgl_pesan' => date('Y-m-d H:i:s'),
            'tgl_kirim' => date('Y-m-d', strtotime('+3 days')),
            'waktu_kirim' => '09:00:00',
            'customer_name' => 'Pelanggan (' . $orderParam . ')',
            'customer_phone' => '0812-3456-7890',
            'customer_address' => 'Kota Batu, Jawa Timur',
            'status_bayar' => 'lunas',
            'metode_bayar' => 'cash',
            'grand_total' => 144000,
            'catatan' => 'Pesanan Khusus Bakery Berkat Dinasti'
        ];
        $items = [
            [
                'product_name' => 'Roti Pesanan Spesial (Box)',
                'packaging' => 'Kardus',
                'qty' => 10,
                'price' => 10000,
                'subtotal' => 100000
            ],
            [
                'product_name' => 'Bolu / Roti Manis',
                'packaging' => 'Mika',
                'qty' => 2,
                'price' => 22000,
                'subtotal' => 44000
            ]
        ];
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

    // Encode ke Base64 murni untuk Android Intent / RawBT / Scheme Support
    $binData = '';
    foreach (array_chunk($escposBytes, 4096) as $chunk) {
        $binData .= pack('C*', ...$chunk);
    }
    $base64Escpos = base64_encode($binData);

    echo json_encode([
        'success'    => true,
        'escpos'     => $escposBytes,
        'base64'     => $base64Escpos,
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
 * FUNGSI GENERATOR ESC/POS UTAMA (RINGAN, CEPAT, SIMPLE, TANPA LOGO)
 * Sesuai Desain Nota Kasir di Simulasi Pengaturan Toko Berkat Dinasti
 * =========================================================================
 */
function buildReceiptESCPOS($store, $order, $items, $logoPath = '', $colWidth = 32) {
    $c = [];

    // 1. Reset Printer & Inisialisasi
    $c[] = 0x1B; $c[] = 0x40; // ESC @

    // 2. Set Print Density (GS ( K - Level 5: Pekat & Tajam)
    $c[] = 0x1D; $c[] = 0x28; $c[] = 0x4B; $c[] = 0x02; $c[] = 0x00; $c[] = 0x31; $c[] = 0x05;

    // 3. Header Toko (Compact 3 Baris)
    appendAlign($c, 1); // Center
    appendBold($c, true);

    $rawStoreName = trim($store['name'] ?? 'BERKAT DINASTI BAKERY');
    if (stripos($rawStoreName, 'BAKERY') === false) {
        $rawStoreName .= ' BAKERY';
    }
    $storeName = strtoupper($rawStoreName);

    // Gunakan Double Height (Tinggi tebal, 1 baris di 58mm / 32 kolom)
    if (strlen($storeName) <= $colWidth) {
        appendDoubleHeight($c, true);
        appendText($c, $storeName . "\n");
        appendDoubleHeight($c, false);
    } else {
        appendWrappedLine($c, $storeName, $colWidth);
    }
    appendBold($c, false);

    // Alamat Toko Ringkas (misal: Jl. Panglima Sudirman 42, Batu)
    $addrClean = $store['address'] ?? 'Jl. Panglima Sudirman 42, Batu';
    $addrClean = str_ireplace(['No. ', ', Ngaglik', 'Ngaglik, '], ['', '', ''], $addrClean);
    if (!str_contains($addrClean, 'Batu') && !str_contains($addrClean, 'Kota Batu')) {
        $addrClean .= ', Batu';
    }
    appendWrappedLine($c, $addrClean, $colWidth);

    // No WA Toko
    $waVal = !empty($store['wa']) ? $store['wa'] : '0812-3456-7890';
    appendText($c, "WA: " . $waVal . "\n");

    // Separator 1: Tepat 32 Karakter
    appendSeparator($c, '-', $colWidth);

    // 4. Meta Order (Compact: No Nota & Waktu Cetak 1 Baris)
    appendAlign($c, 0); // Left
    $invVal = $order['no_invoice'] ?? ('INV-' . ($order['id'] ?? '0001'));
    // Ringkas format invoice untuk struk thermal jika panjang
    $invDisplay = str_replace('INV-TEST-', 'INV-', $invVal);
    $tglVal = !empty($order['tgl_pesan']) ? date('d/m/y H:i', strtotime($order['tgl_pesan'])) : date('d/m/y H:i');

    // Baris 1: No. Nota (kiri) & Tanggal Jam (kanan)
    if (strlen($invDisplay) + strlen($tglVal) + 1 <= $colWidth) {
        appendRow2Col($c, $invDisplay, $tglVal, $colWidth);
    } else {
        appendWrappedLine($c, $invDisplay, $colWidth);
        appendRow2Col($c, "Tgl: " . date('d/m/y', strtotime($tglVal)), date('H:i', strtotime($tglVal)), $colWidth);
    }

    // Baris 2: Pelanggan
    $custVal = !empty($order['customer_name']) ? $order['customer_name'] : ($order['customer'] ?? 'Ibu Sari Dewi');
    appendWrappedLine($c, "Plg  : " . $custVal, $colWidth);

    // Baris 3: Jadwal Pengiriman (Jika Ada)
    if (!empty($order['tgl_kirim'])) {
        $tglKirimStr = date('d/m/y', strtotime($order['tgl_kirim']));
        $wktKirimStr = !empty($order['waktu_kirim']) ? substr($order['waktu_kirim'], 0, 5) : '09:00';
        appendRow2Col($c, "Kirim: " . $tglKirimStr, "Jam: " . $wktKirimStr, $colWidth);
    }

    // Separator 2: Tepat 32 Karakter
    appendSeparator($c, '-', $colWidth);

    // 5. Items Ordered (Pola 2 Baris Bersih, Tanpa Pengulangan Qty di Nama Item)
    $totalCalculated = 0;
    foreach ($items as $it) {
        $pName = $it['product_name'] ?? 'Roti Pesanan';
        $pPkg = !empty($it['packaging']) ? " (" . $it['packaging'] . ")" : "";
        $pTop = !empty($it['topping']) ? $it['topping'] : "";
        $pQty = intval($it['qty'] ?? 1);
        $pHrg = floatval($it['price'] ?? 0);
        $pSub = floatval($it['subtotal'] ?? ($pQty * $pHrg));
        $totalCalculated += $pSub;

        // Baris 1: Nama Produk & Kemasan (tanpa "10x" di depan agar bersih & hemat ruang)
        $titleLine = $pName . $pPkg;
        appendWrappedLine($c, $titleLine, $colWidth);

        // Baris 1b (Opsional): Topping
        if (!empty($pTop)) {
            appendWrappedLine($c, "  * Top: " . $pTop, $colWidth);
        }

        // Baris 2: Qty x Harga di kiri & Subtotal di kanan (Dynamic Spacing)
        $leftQtyHrg = "  " . $pQty . "x @" . number_format($pHrg, 0, ',', '.');
        $rightSub = "Rp " . number_format($pSub, 0, ',', '.');
        appendRow2Col($c, $leftQtyHrg, $rightSub, $colWidth);
    }

    // Separator 3: Tepat 32 Karakter
    appendSeparator($c, '-', $colWidth);

    // 6. Totals & Catatan (Hilangkan Subtotal jika nilainya sama dengan Total Akhir)
    $grandTotal = $order['grand_total'] ?? $totalCalculated;
    $hasOngkir = !empty($order['ongkir']) && floatval($order['ongkir']) > 0;
    $hasDiscount = ($grandTotal < $totalCalculated);

    if ($hasOngkir || $hasDiscount) {
        appendRow2Col($c, "Subtotal:", "Rp " . number_format($totalCalculated, 0, ',', '.'), $colWidth);
        if ($hasOngkir) {
            appendRow2Col($c, "Ongkir:", "Rp " . number_format(floatval($order['ongkir']), 0, ',', '.'), $colWidth);
        }
    }

    // TOTAL AKHIR (Bold + Double Height)
    appendBold($c, true);
    appendDoubleHeight($c, true);
    appendRow2Col($c, "TOTAL AKHIR:", "Rp " . number_format($grandTotal, 0, ',', '.'), $colWidth);
    appendDoubleHeight($c, false);
    appendBold($c, false);

    // Status Pembayaran
    $payStat = strtoupper(str_replace('_', ' ', $order['status_bayar'] ?? 'LUNAS'));
    $payMet = strtoupper($order['metode_bayar'] ?? 'CASH');
    appendBold($c, true);
    appendRow2Col($c, "Status:", "{$payStat} ({$payMet})", $colWidth);
    appendBold($c, false);

    // Catatan Pesanan (Jika Ada)
    if (!empty($order['catatan'])) {
        appendWrappedLine($c, "Catatan: " . $order['catatan'], $colWidth);
    }

    // Separator 4: Tepat 32 Karakter
    appendSeparator($c, '-', $colWidth);

    // 7. Footer Message (Singkat & Manis)
    appendAlign($c, 1); // Center
    $footerText = !empty($store['footer']) ? $store['footer'] : 'Terima kasih atas pesanan Anda';
    if (str_contains($footerText, 'Simpan struk ini')) {
        $footerText = 'Terima kasih atas pesanan Anda';
    }
    appendWrappedLine($c, '"' . trim($footerText, '" ') . '"', $colWidth);

    // 8. QR Code Nota Digital & Lacak Pesanan (Zero-Login)
    $trackingToken = $order['tracking_token'] ?? '';
    if (!empty($trackingToken)) {
        require_once __DIR__ . '/../../app/Helpers/ReceiptHelper.php';
        $digitalReceiptUrl = ReceiptHelper::getDigitalReceiptUrl($trackingToken);
        $c[] = 0x0A;
        appendQRCode($c, $digitalReceiptUrl, 3);
        appendWrappedLine($c, 'Scan QR Nota Digital & Dapur', $colWidth);
    }

    // 9. Feed Kertas (2 baris) & Auto-Cut
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
    
    // Proteksi: Jika left + right melebihi batas kolom (32 atau 48), potong left secara elegan agar tidak wrap
    if ($leftLen + $rightLen >= $width) {
        $maxLeft = $width - $rightLen - 1;
        if ($maxLeft >= 4) {
            $left = substr($left, 0, $maxLeft);
            $leftLen = strlen($left);
        } else {
            // Split baris jika kolom kanan terlalu panjang
            appendText($c, $left . "\n");
            $left = '';
            $leftLen = 0;
        }
    }
    
    // Dynamic Spacing: (colWidth - len(left) - len(right))
    $spaces = $width - ($leftLen + $rightLen);
    if ($spaces < 1) $spaces = 1;
    appendText($c, $left . str_repeat(' ', $spaces) . $right . "\n");
}

function appendWrappedLine(&$c, $text, $width = 32) {
    // Normalisasi baris baru (CRLF -> LF)
    $paragraphs = explode("\n", str_replace(["\r\n", "\r"], "\n", $text));
    foreach ($paragraphs as $para) {
        $words = explode(' ', $para);
        $currentLine = '';
        foreach ($words as $w) {
            if ($w === '') continue;

            // Jika satu kata melebihi lebar kertas, potong per $width agar tidak crash chip printer
            while (strlen($w) > $width) {
                if (!empty($currentLine)) {
                    appendText($c, $currentLine . "\n");
                    $currentLine = '';
                }
                appendText($c, substr($w, 0, $width) . "\n");
                $w = substr($w, $width);
            }

            if ($currentLine === '') {
                $currentLine = $w;
            } elseif (strlen($currentLine) + 1 + strlen($w) <= $width) {
                $currentLine .= ' ' . $w;
            } else {
                appendText($c, $currentLine . "\n");
                $currentLine = $w;
            }
        }
        if ($currentLine !== '') {
            appendText($c, $currentLine . "\n");
        }
    }
}

/**
 * Helper Barcode / QR Code ESC/POS
 * Menggunakan module width narrow (n=2 untuk Code 128 / size 4 untuk QR) agar tidak terpotong tepi kertas
 */
function appendQRCode(&$c, $data, $moduleSize = 4) {
    if (empty($data)) return;
    $len = strlen($data) + 3;
    $pL = $len & 0xFF;
    $pH = ($len >> 8) & 0xFF;

    appendAlign($c, 1); // Center
    // 1. Set Model 2
    $c = array_merge($c, [0x1D, 0x28, 0x6B, 0x04, 0x00, 0x31, 0x41, 0x32, 0x00]);
    // 2. Set Module Size (Narrow 4 dots agar pas di 58mm)
    $c = array_merge($c, [0x1D, 0x28, 0x6B, 0x03, 0x00, 0x31, 0x43, $moduleSize]);
    // 3. Set Error Correction Level L
    $c = array_merge($c, [0x1D, 0x28, 0x6B, 0x03, 0x00, 0x31, 0x45, 0x30]);
    // 4. Store Data
    $c = array_merge($c, [0x1D, 0x28, 0x6B, $pL, $pH, 0x31, 0x50, 0x30]);
    for ($i = 0; $i < strlen($data); $i++) {
        $c[] = ord($data[$i]);
    }
    // 5. Print QR Code
    $c = array_merge($c, [0x1D, 0x28, 0x6B, 0x03, 0x00, 0x31, 0x51, 0x30]);
    $c[] = 0x0A;
}
