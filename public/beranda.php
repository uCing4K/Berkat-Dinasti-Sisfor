<?php
session_start();

// Proteksi Autentikasi: Hanya user yang sudah login yang diizinkan mengakses Beranda
if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true) {
    header("Location: login.php");
    exit();
}

// Inisialisasi Database Connection & Handler Backend
$db_connected = false;
$pdo = null;
$configFile = __DIR__ . "/../config/config.php";
if (file_exists($configFile)) {
    try {
        @require_once $configFile;
        if (defined('DB_HOST') && defined('DB_NAME') && defined('DB_USER')) {
            $pdo = new PDO("mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=utf8mb4", DB_USER, DB_PASS, [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_SILENT,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_TIMEOUT => 2
            ]);
            $db_connected = true;
        }
    } catch (Exception $e) {
        $db_connected = false;
    }
}

// -------------------------------------------------------------
// 1. BACKEND HANDLER: EXPORT REKAP PENJUALAN HARIAN (CSV)
// -------------------------------------------------------------
if (isset($_GET['action']) && $_GET['action'] === 'export_rekap') {
    $today = date('Y-m-d');
    $filename = "rekap-harian-berkat-dinasti-" . $today . ".csv";
    header('Content-Type: text/csv; charset=utf-8');
    header('Content-Disposition: attachment; filename="' . $filename . '"');
    
    // Output UTF-8 BOM for Microsoft Excel compatibility
    echo "\xEF\xBB\xBF";
    $output = fopen('php://output', 'w');
    fputcsv($output, ['No Invoice', 'Tanggal Pesan', 'Nama Pelanggan', 'No WA', 'Deadline Kirim', 'Total Belanja', 'Status Bayar', 'Status Pesanan', 'Catatan']);
    
    $exported = false;
    if ($db_connected && $pdo) {
        try {
            $stmt = $pdo->prepare("
                SELECT p.no_invoice, p.tgl_pesan, pel.nama_pelanggan, pel.no_wa, 
                       CONCAT(COALESCE(p.tgl_kirim, p.tgl_pesan), ' ', COALESCE(p.waktu_kirim, '')) as deadline, 
                       p.grand_total, p.status_bayar, p.status, p.catatan 
                FROM pesanan p 
                LEFT JOIN pelanggan pel ON p.id_pelanggan = pel.id_pelanggan 
                WHERE p.tgl_pesan = CURDATE() OR p.tgl_kirim = CURDATE()
                ORDER BY p.id_pesanan DESC
            ");
            $stmt->execute();
            $rows = $stmt->fetchAll();
            foreach ($rows as $r) {
                fputcsv($output, [
                    $r['no_invoice'],
                    $r['tgl_pesan'],
                    $r['nama_pelanggan'] ?? 'Pelanggan Walk-in',
                    $r['no_wa'] ?? '-',
                    $r['deadline'],
                    'Rp ' . number_format($r['grand_total'], 0, ',', '.'),
                    strtoupper(str_replace('_', ' ', $r['status_bayar'])),
                    strtoupper($r['status']),
                    $r['catatan'] ?? ''
                ]);
            }
            if (count($rows) > 0) $exported = true;
        } catch (Exception $e) {}
    }
    
    if (!$exported) {
        // Fallback default sample data
        fputcsv($output, ['#ORD-20250926-01', $today, 'Ibu Ratna Sari', '0812-3456-7890', 'Hari Ini, 14:00', 'Rp 110.000', 'BELUM BAYAR', 'PROSES', 'Roti Sisir Butter (50 pcs)']);
        fputcsv($output, ['#ORD-20250926-02', $today, 'Pak Hendra Wijaya', '0813-8899-7711', 'Hari Ini, 16:30', 'Rp 270.000', 'HUTANG', 'PROSES', 'Roti Sobek Cokelat Keju (30 box)']);
        fputcsv($output, ['#ORD-20250926-03', $today, 'Toko Berkah Jaya', '0821-4455-6677', 'Besok, 09:00', 'Rp 650.000', 'LUNAS', 'PROSES', 'Donat Kentang Tabur Salju (100 pcs)']);
    }
    
    fclose($output);
    exit();
}

// -------------------------------------------------------------
// 2. BACKEND HANDLER: TAMBAH PESANAN BARU (FORM SUBMIT)
// -------------------------------------------------------------
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'tambah_pesanan') {
    $id_pelanggan = intval($_POST['id_pelanggan'] ?? 0);
    $nama_pelanggan_baru = trim($_POST['nama_pelanggan_baru'] ?? '');
    $no_wa_baru = trim($_POST['no_wa_baru'] ?? '');
    $alamat_baru = trim($_POST['alamat_baru'] ?? '');
    
    $tgl_kirim = !empty($_POST['tgl_kirim']) ? $_POST['tgl_kirim'] : date('Y-m-d');
    $waktu_kirim = !empty($_POST['waktu_kirim']) ? $_POST['waktu_kirim'] : '14:00:00';
    $status_bayar = in_array($_POST['status_bayar'] ?? '', ['belum_bayar', 'hutang', 'lunas', 'dp']) ? $_POST['status_bayar'] : 'belum_bayar';
    $metode_bayar = in_array($_POST['metode_bayar'] ?? '', ['cash', 'transfer']) ? $_POST['metode_bayar'] : 'cash';
    $catatan = trim($_POST['catatan'] ?? '');
    
    $raw_items = $_POST['items'] ?? [];
    $processed_items = [];
    $grand_total = 0;
    
    if (!empty($raw_items) && is_array($raw_items)) {
        foreach ($raw_items as $it) {
            $p_name = trim($it['nama'] ?? 'Roti Bakery');
            $p_var_id = intval($it['id_varian'] ?? 1);
            $p_kemasan = trim($it['kemasan'] ?? 'Satuan');
            $p_qty = max(1, intval($it['qty'] ?? 1));
            $p_harga = max(0, floatval($it['harga'] ?? 15000));
            $sub = $p_qty * $p_harga;
            $grand_total += $sub;
            $processed_items[] = [
                'nama' => $p_name,
                'id_varian' => $p_var_id,
                'kemasan' => $p_kemasan,
                'qty' => $p_qty,
                'harga' => $p_harga,
                'subtotal' => $sub
            ];
        }
    }
    
    if (empty($processed_items)) {
        $processed_items[] = [
            'nama' => 'Roti Tawar Kupas Special',
            'id_varian' => 1,
            'kemasan' => 'Mika (10 pcs)',
            'qty' => 5,
            'harga' => 15000,
            'subtotal' => 75000
        ];
        $grand_total = 75000;
    }
    
    $dateCode = date('Ymd');
    $randNum = rand(100, 999);
    $no_invoice = "INV-{$dateCode}-{$randNum}";
    $customer_display = 'Ibu Sari Dewi';
    $customer_wa = '0812-3456-7890';
    $customer_alamat = 'Komplek Melati No. 4, Blok B';
    
    if ($db_connected && $pdo) {
        try {
            $pdo->beginTransaction();
            
            if ($id_pelanggan <= 0 && !empty($nama_pelanggan_baru)) {
                $stmtC = $pdo->prepare("INSERT INTO pelanggan (nama_pelanggan, no_wa, alamat, tipe_pelanggan) VALUES (?, ?, ?, 'retail')");
                $stmtC->execute([$nama_pelanggan_baru, $no_wa_baru, $alamat_baru]);
                $id_pelanggan = $pdo->lastInsertId();
                $customer_display = $nama_pelanggan_baru;
                $customer_wa = $no_wa_baru;
                $customer_alamat = $alamat_baru;
            } elseif ($id_pelanggan > 0) {
                $stmtC = $pdo->prepare("SELECT nama_pelanggan, no_wa, alamat FROM pelanggan WHERE id_pelanggan = ?");
                $stmtC->execute([$id_pelanggan]);
                $cRow = $stmtC->fetch();
                if ($cRow) {
                    $customer_display = $cRow['nama_pelanggan'];
                    $customer_wa = $cRow['no_wa'];
                    $customer_alamat = $cRow['alamat'];
                }
            } else {
                $stmtC = $pdo->query("SELECT id_pelanggan, nama_pelanggan, no_wa, alamat FROM pelanggan LIMIT 1");
                $cRow = $stmtC->fetch();
                if ($cRow) {
                    $id_pelanggan = $cRow['id_pelanggan'];
                    $customer_display = $cRow['nama_pelanggan'];
                    $customer_wa = $cRow['no_wa'];
                    $customer_alamat = $cRow['alamat'];
                } else {
                    $id_pelanggan = 1;
                }
            }
            
            // Insert Pesanan
            $stmtOrder = $pdo->prepare("
                INSERT INTO pesanan (id_pelanggan, no_invoice, tgl_pesan, tgl_kirim, waktu_kirim, total_harga, grand_total, status, status_bayar, metode_bayar, catatan)
                VALUES (?, ?, CURDATE(), ?, ?, ?, ?, 'pending', ?, ?, ?)
            ");
            $stmtOrder->execute([
                $id_pelanggan,
                $no_invoice,
                $tgl_kirim,
                $waktu_kirim,
                $grand_total,
                $grand_total,
                $status_bayar,
                $metode_bayar,
                $catatan
            ]);
            $new_order_id = $pdo->lastInsertId();
            
            // Insert Detail Pesanan
            $stmtDetail = $pdo->prepare("
                INSERT INTO detail_pesanan (id_pesanan, id_varian, qty, harga_satuan, subtotal)
                VALUES (?, ?, ?, ?, ?)
            ");
            foreach ($processed_items as $pi) {
                $stmtDetail->execute([
                    $new_order_id,
                    $pi['id_varian'] > 0 ? $pi['id_varian'] : 1,
                    $pi['qty'],
                    $pi['harga'],
                    $pi['subtotal']
                ]);
            }
            
            // Triggers automatically handle tracking_log & pengiriman
            try {
                $stmtLog = $pdo->prepare("INSERT INTO tracking_log (id_pesanan, status, keterangan) VALUES (?, 'pending', ?)");
                $stmtLog->execute([$new_order_id, "Pesanan #{$no_invoice} berhasil dibuat dari Beranda"]);
            } catch (Exception $eL) {}
            
            $pdo->commit();
        } catch (Exception $e) {
            if ($pdo->inTransaction()) $pdo->rollBack();
        }
    }
    
    // Simpan ke sesi agar tampil instan
    if (!isset($_SESSION['session_orders'])) {
        $_SESSION['session_orders'] = [];
    }
    $itemSummaryStr = implode(', ', array_map(fn($p) => "{$p['nama']} ({$p['qty']} pcs)", $processed_items));
    $p_class = match($status_bayar) {
        'lunas' => 'bg-[#27AE60]/15 text-[#1B7A43]',
        'hutang', 'dp' => 'bg-[#E2B93B]/20 text-[#886C12]',
        default => 'bg-[#828282]/15 text-[#544337]'
    };
    
    array_unshift($_SESSION['session_orders'], [
        'id' => $no_invoice,
        'customer' => $customer_display,
        'product' => $itemSummaryStr,
        'deadline' => 'Hari Ini, ' . substr($waktu_kirim, 0, 5),
        'is_urgent' => true,
        'status' => 'pending',
        'payment_status' => ucfirst(str_replace('_', ' ', $status_bayar)),
        'payment_class' => $p_class,
        'total' => 'Rp ' . number_format($grand_total, 0, ',', '.'),
        'no_wa' => $customer_wa,
        'alamat' => $customer_alamat
    ]);
    
    $_SESSION['flash_msg'] = "Pesanan baru #{$no_invoice} berhasil disimpan dan otomatis masuk ke antrean oven & jadwal pengiriman!";
    header("Location: beranda.php");
    exit();
}

// -------------------------------------------------------------
// 3. BACKEND HANDLER: UPDATE STATUS PESANAN (DARI MODAL DETAIL)
// -------------------------------------------------------------
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'update_status') {
    $order_id = trim($_POST['order_id'] ?? '');
    $new_status = in_array($_POST['new_status'] ?? '', ['pending', 'proses', 'kirim', 'selesai', 'batal']) ? $_POST['new_status'] : 'proses';
    
    if ($db_connected && $pdo && !empty($order_id)) {
        try {
            $stmtU = $pdo->prepare("UPDATE pesanan SET status = ? WHERE id_pesanan = ? OR no_invoice = ?");
            $stmtU->execute([$new_status, $order_id, $order_id]);
            
            $stmtLog = $pdo->prepare("INSERT INTO tracking_log (id_pesanan, status, keterangan) SELECT id_pesanan, ?, CONCAT('Status diubah ke ', ?) FROM pesanan WHERE id_pesanan = ? OR no_invoice = ? LIMIT 1");
            $stmtLog->execute([$new_status, $new_status, $order_id, $order_id]);
        } catch (Exception $e) {}
    }
    
    if (!empty($_SESSION['session_orders'])) {
        foreach ($_SESSION['session_orders'] as &$so) {
            if ($so['id'] == $order_id) {
                $so['status'] = $new_status;
            }
        }
    }
    
    $statusLabel = match($new_status) {
        'proses' => 'Sedang Diproses / Oven',
        'kirim' => 'Siap Kirim / Driver',
        'selesai' => 'Selesai',
        default => ucfirst($new_status)
    };
    
    $_SESSION['flash_msg'] = "Status pesanan {$order_id} berhasil diubah menjadi: {$statusLabel}!";
    header("Location: beranda.php");
    exit();
}

// -------------------------------------------------------------
// 4. DATA PELANGGAN & PRODUK MASTER UNTUK MODAL TAMBAH PESANAN
// -------------------------------------------------------------
$customers_for_select = [
    ['id' => 1, 'nama' => 'Ibu Sari Dewi', 'no_wa' => '0812-3456-7890', 'alamat' => 'Komplek Melati No. 4, Blok B, Sukajadi'],
    ['id' => 2, 'nama' => 'Pak Hendra Wijaya', 'no_wa' => '0813-8899-7711', 'alamat' => 'Jl. Anggrek No. 12, Bandung'],
    ['id' => 3, 'nama' => 'Toko Berkah Jaya', 'no_wa' => '0821-4455-6677', 'alamat' => 'Ruko Harmoni Blok C-02, Cibiru'],
    ['id' => 4, 'nama' => 'Kafe Kopi Seduh', 'no_wa' => '0812-9876-5432', 'alamat' => 'Jl. Progo No. 8, Riau, Bandung'],
    ['id' => 5, 'nama' => 'Bu Siti Rahayu', 'no_wa' => '0856-1122-3344', 'alamat' => 'Komplek Griya Asri No. 9, Antapani']
];

if ($db_connected && $pdo) {
    try {
        $stmtC = $pdo->query("SELECT id_pelanggan as id, nama_pelanggan as nama, no_wa, alamat FROM pelanggan ORDER BY nama_pelanggan ASC");
        $dbCusts = $stmtC->fetchAll();
        if (!empty($dbCusts)) {
            $customers_for_select = $dbCusts;
        }
    } catch (Exception $e) {}
}

$products_for_select = [
    ['id' => 1, 'id_varian' => 1, 'nama' => 'Roti Tawar Kupas Special', 'kemasan' => 'Mika (10 pcs)', 'harga' => 15000, 'min_order' => 5],
    ['id' => 2, 'id_varian' => 2, 'nama' => 'Donat Kentang Coklat Meises', 'kemasan' => 'Mika (6 pcs)', 'harga' => 25000, 'min_order' => 1],
    ['id' => 3, 'id_varian' => 3, 'nama' => 'Roti Sisir Butter Mentega', 'kemasan' => 'Kardus (20 pcs)', 'harga' => 80000, 'min_order' => 1],
    ['id' => 4, 'id_varian' => 4, 'nama' => 'Roti Sobek Cokelat Keju', 'kemasan' => 'Box (1 loyang)', 'harga' => 35000, 'min_order' => 2],
    ['id' => 5, 'id_varian' => 5, 'nama' => 'Donat Kentang Tabur Salju', 'kemasan' => 'Mika (6 pcs)', 'harga' => 25000, 'min_order' => 1],
    ['id' => 6, 'id_varian' => 6, 'nama' => 'Baguette & Croissant Mini', 'kemasan' => 'Pack (5 pcs)', 'harga' => 30000, 'min_order' => 1]
];

if ($db_connected && $pdo) {
    try {
        $stmtP = $pdo->query("
            SELECT v.id_varian, v.id_produk, p.nama_produk as nama, v.nama_varian as kemasan, v.harga, v.min_order 
            FROM varian_produk v 
            JOIN produk p ON v.id_produk = p.id_produk 
            WHERE p.status = 'tersedia' 
            ORDER BY p.nama_produk ASC
        ");
        $dbProds = $stmtP->fetchAll();
        if (!empty($dbProds)) {
            $products_for_select = $dbProds;
        }
    } catch (Exception $e) {}
}

// -------------------------------------------------------------
// 5. QUERY AGREGASI METRIK BISNIS (KARTU KPI UTAMA)
// -------------------------------------------------------------
$kpi_data = [
    'total_orders' => 12,
    'orders_diff' => '+2 dari kemarin',
    'pending_orders' => 5,
    'pending_note' => 'Perlu konfirmasi & proses',
    'total_revenue' => 'Rp 4.250.000',
    'revenue_target' => 'Target tercapai 85%',
    'new_customers' => 3,
    'customers_note' => 'Minggu ini'
];

if ($db_connected && $pdo) {
    try {
        // Total Pesanan Hari Ini
        $s1 = $pdo->query("SELECT COUNT(*) FROM pesanan WHERE DATE(tgl_pesan) = CURDATE()");
        $cToday = $s1->fetchColumn();
        if ($cToday > 0) {
            $kpi_data['total_orders'] = $cToday;
            $kpi_data['orders_diff'] = 'Terbaru hari ini';
        }
        
        // Pesanan Perlu Tindakan / Proses
        $s2 = $pdo->query("SELECT COUNT(*) FROM pesanan WHERE status IN ('pending', 'proses')");
        $cPending = $s2->fetchColumn();
        if ($cPending > 0) {
            $kpi_data['pending_orders'] = $cPending;
            $kpi_data['pending_note'] = 'Perlu proses & oven';
        }
        
        // Total Pemasukan Bulan Berjalan
        $s3 = $pdo->query("SELECT COALESCE(SUM(grand_total), 0) FROM pesanan WHERE status_bayar = 'lunas' AND MONTH(tgl_pesan) = MONTH(CURDATE()) AND YEAR(tgl_pesan) = YEAR(CURDATE())");
        $cRev = $s3->fetchColumn();
        if ($cRev > 0) {
            $kpi_data['total_revenue'] = 'Rp ' . number_format($cRev, 0, ',', '.');
            $kpi_data['revenue_target'] = 'Bulan ini (Lunas)';
        }
        
        // Pelanggan Baru 7 Hari Terakhir
        $s4 = $pdo->query("SELECT COUNT(*) FROM pelanggan WHERE created_at >= DATE_SUB(NOW(), INTERVAL 7 DAY)");
        $cCust = $s4->fetchColumn();
        if ($cCust > 0) {
            $kpi_data['new_customers'] = $cCust;
            $kpi_data['customers_note'] = '7 hari terakhir';
        }
    } catch (Exception $e) {}
}

if (!empty($_SESSION['session_orders'])) {
    $kpi_data['total_orders'] += count($_SESSION['session_orders']);
    $kpi_data['pending_orders'] += count($_SESSION['session_orders']);
}

// -------------------------------------------------------------
// 6. DAFTAR PESANAN BERJALAN & ANTREAN DAPUR
// -------------------------------------------------------------
$pending_orders = [
    [
        'id' => 'ORD-20250926-01',
        'customer' => 'Ibu Ratna Sari',
        'product' => 'Roti Sisir Butter (50 pcs)',
        'deadline' => 'Hari Ini, 14:00',
        'is_urgent' => true,
        'status' => 'proses',
        'payment_status' => 'Belum Bayar',
        'payment_class' => 'bg-[#828282]/15 text-[#544337]',
        'total' => 'Rp 110.000',
        'no_wa' => '0812-3456-7890',
        'alamat' => 'Komplek Melati No. 4, Blok B'
    ],
    [
        'id' => 'ORD-20250926-02',
        'customer' => 'Pak Hendra Wijaya',
        'product' => 'Roti Sobek Cokelat Keju (30 box)',
        'deadline' => 'Hari Ini, 16:30',
        'is_urgent' => true,
        'status' => 'proses',
        'payment_status' => 'Hutang',
        'payment_class' => 'bg-[#E2B93B]/20 text-[#886C12]',
        'total' => 'Rp 270.000',
        'no_wa' => '0813-8899-7711',
        'alamat' => 'Jl. Anggrek No. 12, Bandung'
    ],
    [
        'id' => 'ORD-20250926-03',
        'customer' => 'Toko Berkah Jaya',
        'product' => 'Donat Kentang Tabur Salju (100 pcs)',
        'deadline' => 'Besok, 09:00',
        'is_urgent' => false,
        'status' => 'pending',
        'payment_status' => 'Lunas',
        'payment_class' => 'bg-[#27AE60]/15 text-[#1B7A43]',
        'total' => 'Rp 650.000',
        'no_wa' => '0821-4455-6677',
        'alamat' => 'Ruko Harmoni Blok C-02'
    ],
    [
        'id' => 'ORD-20250926-04',
        'customer' => 'Kafe Kopi Seduh',
        'product' => 'Baguette & Croissant Mini (40 pcs)',
        'deadline' => '28 Sep 2025',
        'is_urgent' => false,
        'status' => 'kirim',
        'payment_status' => 'Belum Bayar',
        'payment_class' => 'bg-[#828282]/15 text-[#544337]',
        'total' => 'Rp 240.000',
        'no_wa' => '0812-9876-5432',
        'alamat' => 'Jl. Progo No. 8, Riau'
    ],
    [
        'id' => 'ORD-20250926-05',
        'customer' => 'Bu Siti Rahayu',
        'product' => 'Roti Tawar Gandum Spesial (25 pack)',
        'deadline' => '29 Sep 2025',
        'is_urgent' => false,
        'status' => 'selesai',
        'payment_status' => 'Lunas',
        'payment_class' => 'bg-[#27AE60]/15 text-[#1B7A43]',
        'total' => 'Rp 180.000',
        'no_wa' => '0856-1122-3344',
        'alamat' => 'Komplek Griya Asri No. 9'
    ]
];

if ($db_connected && $pdo) {
    try {
        $stmtOrders = $pdo->query("
            SELECT p.id_pesanan, p.no_invoice, p.tgl_pesan, p.tgl_kirim, p.waktu_kirim, p.grand_total, p.status, p.status_bayar, p.catatan,
                   pel.nama_pelanggan, pel.no_wa, pel.alamat,
                   (SELECT GROUP_CONCAT(CONCAT(v.nama_varian, ' (', dp.qty, ' pcs)') SEPARATOR ', ')
                    FROM detail_pesanan dp 
                    JOIN varian_produk v ON dp.id_varian = v.id_varian 
                    WHERE dp.id_pesanan = p.id_pesanan) as item_summary
            FROM pesanan p
            LEFT JOIN pelanggan pel ON p.id_pelanggan = pel.id_pelanggan
            ORDER BY p.id_pesanan DESC
            LIMIT 15
        ");
        $dbOrders = $stmtOrders->fetchAll();
        if (!empty($dbOrders)) {
            $formattedOrders = [];
            foreach ($dbOrders as $o) {
                $is_urgent = ($o['tgl_kirim'] == date('Y-m-d'));
                $dl = $is_urgent ? 'Hari Ini, ' . substr($o['waktu_kirim'] ?? '14:00', 0, 5) : date('d M Y', strtotime($o['tgl_kirim'] ?? $o['tgl_pesan']));
                $p_status = strtolower($o['status_bayar'] ?? 'belum_bayar');
                $p_class = match($p_status) {
                    'lunas' => 'bg-[#27AE60]/15 text-[#1B7A43]',
                    'hutang', 'dp' => 'bg-[#E2B93B]/20 text-[#886C12]',
                    default => 'bg-[#828282]/15 text-[#544337]'
                };
                
                $formattedOrders[] = [
                    'id' => $o['no_invoice'],
                    'customer' => $o['nama_pelanggan'] ?? 'Pelanggan Walk-in',
                    'product' => !empty($o['item_summary']) ? $o['item_summary'] : 'Pesanan Bakery Berkat Dinasti',
                    'deadline' => $dl,
                    'is_urgent' => $is_urgent,
                    'status' => $o['status'] ?? 'pending',
                    'payment_status' => ucfirst(str_replace('_', ' ', $p_status)),
                    'payment_class' => $p_class,
                    'total' => 'Rp ' . number_format($o['grand_total'], 0, ',', '.'),
                    'no_wa' => $o['no_wa'] ?? '-',
                    'alamat' => $o['alamat'] ?? '-'
                ];
            }
            $pending_orders = $formattedOrders;
        }
    } catch (Exception $e) {}
}

if (!empty($_SESSION['session_orders'])) {
    $pending_orders = array_merge($_SESSION['session_orders'], $pending_orders);
}

// -------------------------------------------------------------
// 7. JADWAL PENGIRIMAN HARI INI
// -------------------------------------------------------------
$deliveries = [
    [
        'time' => '14:00',
        'customer' => 'Ibu Ratna Sari',
        'items' => '50 pcs Roti Sisir Butter',
        'status' => 'Siap Kirim',
        'status_class' => 'bg-[#27AE60]/15 text-[#1B7A43]'
    ],
    [
        'time' => '16:30',
        'customer' => 'Pak Hendra Wijaya',
        'items' => '30 box Roti Sobek Cokelat',
        'status' => 'Sedang Dikemas',
        'status_class' => 'bg-[#E2B93B]/20 text-[#886C12]'
    ]
];

if ($db_connected && $pdo) {
    try {
        $stmtD = $pdo->query("
            SELECT p.waktu_kirim, pel.nama_pelanggan, 
                   COALESCE((SELECT GROUP_CONCAT(CONCAT(dp.qty, ' pcs ', v.nama_varian) SEPARATOR ', ')
                    FROM detail_pesanan dp 
                    JOIN varian_produk v ON dp.id_varian = v.id_varian 
                    WHERE dp.id_pesanan = p.id_pesanan), 'Roti Pesanan') as items,
                   p.status
            FROM pesanan p
            LEFT JOIN pelanggan pel ON p.id_pelanggan = pel.id_pelanggan
            WHERE p.tgl_kirim = CURDATE() AND p.status != 'batal'
            ORDER BY p.waktu_kirim ASC
            LIMIT 4
        ");
        $dRows = $stmtD->fetchAll();
        if (!empty($dRows)) {
            $formattedD = [];
            foreach ($dRows as $dr) {
                $statusBadge = match($dr['status']) {
                    'kirim' => ['Siap Kirim', 'bg-[#27AE60]/15 text-[#1B7A43]'],
                    'proses' => ['Sedang Dikemas / Oven', 'bg-[#E2B93B]/20 text-[#886C12]'],
                    'selesai' => ['Terkirim', 'bg-[#2F80ED]/15 text-[#2F80ED]'],
                    default => ['Menunggu Dapur', 'bg-[#828282]/15 text-[#544337]']
                };
                $formattedD[] = [
                    'time' => !empty($dr['waktu_kirim']) ? substr($dr['waktu_kirim'], 0, 5) : '14:00',
                    'customer' => $dr['nama_pelanggan'] ?? 'Pelanggan',
                    'items' => $dr['items'],
                    'status' => $statusBadge[0],
                    'status_class' => $statusBadge[1]
                ];
            }
            $deliveries = $formattedD;
        }
    } catch (Exception $e) {}
}

// -------------------------------------------------------------
// 8. AKTIVITAS TERBARU (LIVE AUDIT LOG)
// -------------------------------------------------------------
$activities = [
    [
        'dot_color' => 'bg-[#FF9B45]',
        'text' => '<span class="text-[#FF9B45] font-medium">Pesanan baru <a href="#" class="font-semibold hover:underline">#ORD-20250926-01</a> dibuat oleh Ibu Ratna Sari (50 pcs Roti Sisir Butter)</span>',
        'time' => '15 menit yang lalu'
    ],
    [
        'dot_color' => 'bg-[#27AE60]',
        'text' => '<span class="text-[#27AE60] font-medium">Pembayaran lunas diterima sebesar Rp 650.000 dari Toko Berkah Jaya via Transfer BCA</span>',
        'time' => '42 menit yang lalu'
    ],
    [
        'dot_color' => 'bg-[#E2B93B]',
        'text' => '<span class="text-[#1C1C1C]">Pesanan <span class="font-medium">#ORD-20250925-04</span> diubah ke status <span class="bg-[#E2B93B]/20 text-[#886C12] text-xs font-semibold px-2 py-0.5 rounded-full ml-1">Dalam Oven / Produksi</span></span>',
        'time' => '1 jam yang lalu'
    ],
    [
        'dot_color' => 'bg-[#2F80ED]',
        'text' => '<span class="text-[#2F80ED] font-medium">Pelanggan baru terdaftar: Kafe Kopi Seduh (Kontak: 0812-9876-5432)</span>',
        'time' => '2 jam yang lalu'
    ],
    [
        'dot_color' => 'bg-[#27AE60]',
        'text' => '<span class="text-[#1C1C1C]">Pesanan <span class="font-medium">#ORD-20250925-02</span> telah selesai diserahkan ke kurir pengantar</span>',
        'time' => '3 jam yang lalu'
    ]
];

if ($db_connected && $pdo) {
    try {
        $stmtAct = $pdo->query("
            SELECT tl.*, p.no_invoice 
            FROM tracking_log tl 
            LEFT JOIN pesanan p ON tl.id_pesanan = p.id_pesanan 
            ORDER BY tl.id_log DESC 
            LIMIT 5
        ");
        $aRows = $stmtAct->fetchAll();
        if (!empty($aRows)) {
            $formattedA = [];
            foreach ($aRows as $ar) {
                $dotColor = match($ar['status']) {
                    'selesai', 'lunas' => 'bg-[#27AE60]',
                    'proses' => 'bg-[#E2B93B]',
                    'kirim' => 'bg-[#2F80ED]',
                    default => 'bg-[#FF9B45]'
                };
                $inv = htmlspecialchars($ar['no_invoice'] ?? '');
                $ket = htmlspecialchars($ar['keterangan'] ?? 'Aktivitas pesanan');
                $formattedA[] = [
                    'dot_color' => $dotColor,
                    'text' => "<span class='text-[#1C1C1C]'>Pesanan <span class='font-semibold text-[#FF9B45]'>#{$inv}</span>: {$ket}</span>",
                    'time' => date('H:i', strtotime($ar['created_at'] ?? 'now')) . ' WIB'
                ];
            }
            $activities = $formattedA;
        }
    } catch (Exception $e) {}
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Beranda Operasional - Berkat Dinasti</title>
    
    <!-- Tailwind CSS CDN -->
    <script src="https://cdn.tailwindcss.com"></script>
    <!-- Google Fonts: Inter -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">

    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        brand: {
                            brown: '#3E2B21',
                            brownDark: '#2C1E17',
                            orange: '#FF9B45',
                            orangeHover: '#E88C3D',
                            lightText: '#E8D7CD',
                            bg: '#FBF9F7',
                            surface: '#FFFFFF',
                            border: '#F0ECE9',
                            muted: '#828282',
                            dark: '#1C1C1C',
                            darkText: '#1B1C1B',
                            brownText: '#544337',
                            brownDeep: '#924C00',
                            accentGreen: '#27AE60',
                            accentYellow: '#E2B93B',
                            accentBlue: '#2F80ED',
                            accentRed: '#EB5757'
                        }
                    },
                    fontFamily: {
                        sans: ['Inter', 'sans-serif'],
                    },
                    boxShadow: {
                        'card': '0px 4px 12px rgba(0, 0, 0, 0.06)',
                        'button-orange': '0px 2px 4px rgba(255, 155, 69, 0.25)',
                    }
                }
            }
        }
    </script>

    <style>
        body {
            font-family: 'Inter', sans-serif;
            background-color: #FBF9F7;
            color: #1C1C1C;
        }

        /* Custom Scrollbar */
        ::-webkit-scrollbar {
            width: 6px;
            height: 6px;
        }
        ::-webkit-scrollbar-track {
            background: #F1EFEA;
        }
        ::-webkit-scrollbar-thumb {
            background: #D8D2CB;
            border-radius: 9999px;
        }
        ::-webkit-scrollbar-thumb:hover {
            background: #FF9B45;
        }

        /* Continuous Vertical Timeline line */
        .timeline-container::before {
            content: '';
            position: absolute;
            left: 7px;
            top: 14px;
            bottom: 14px;
            width: 2px;
            background-color: #E4E2E0;
        }
    </style>
</head>
<body class="min-h-screen bg-[#FBF9F7] flex flex-col antialiased selection:bg-[#FF9B45] selection:text-white">

    <!-- OVERALL WRAPPER -->
    <div class="flex flex-1 w-full relative">

        <!-- MOBILE SIDEBAR OVERLAY -->
        <div id="sidebar-overlay" onclick="toggleSidebar()" class="fixed inset-0 bg-black/40 z-40 lg:hidden hidden transition-opacity duration-300"></div>

        <!-- ASIDE / SIDEBAR (Width 240px) -->
        <aside id="main-sidebar" class="fixed top-0 bottom-0 left-0 w-[240px] bg-[#3E2B21] z-50 flex flex-col justify-between -translate-x-full lg:translate-x-0 transition-transform duration-300 ease-in-out shadow-xl lg:shadow-none">
            
            <!-- Top Logo & Navigation -->
            <div class="flex flex-col flex-1 overflow-y-auto">
                
                <!-- Logo & Brand Header (Height 80px) -->
                <div class="h-20 px-4 flex items-center gap-3 border-b border-white/10 shrink-0">
                    <div class="w-8 h-8 rounded-full bg-white p-0.5 flex items-center justify-center shrink-0 shadow-sm overflow-hidden">
                        <img src="assets/images/logo.png" alt="Berkat Dinasti Logo" class="w-full h-full object-contain">
                    </div>
                    <span class="text-white font-bold text-lg tracking-tight">Berkat Dinasti</span>
                    
                    <!-- Mobile Close Button -->
                    <button onclick="toggleSidebar()" class="ml-auto text-white/70 hover:text-white lg:hidden p-1">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                    </button>
                </div>

                <!-- Navigation List -->
                <nav class="p-4 space-y-1.5 flex-1">
                    <!-- Beranda (Active) -->
                    <a href="beranda.php" class="bg-[#FF9B45] text-white rounded-lg px-4 py-3 flex items-center gap-3 font-medium text-sm transition-all shadow-sm">
                        <img src="assets/icons/nav_beranda.svg" alt="Beranda" class="w-5 h-5">
                        <span>Beranda</span>
                    </a>

                    <!-- Produk -->
                    <a href="produk.php" class="text-white/80 hover:text-white hover:bg-white/10 rounded-lg px-4 py-3 flex items-center gap-3 font-medium text-sm transition-colors">
                        <img src="assets/icons/nav_produk.svg" alt="Produk" class="w-5 h-5 opacity-90">
                        <span>Produk</span>
                    </a>

                    <!-- Pesanan -->
                    <a href="pesanan.php" class="text-white/80 hover:text-white hover:bg-white/10 rounded-lg px-4 py-3 flex items-center gap-3 font-medium text-sm transition-colors">
                        <img src="assets/icons/nav_pesanan.svg" alt="Pesanan" class="w-5 h-5 opacity-90">
                        <span>Pesanan</span>
                    </a>

                    <!-- Pelanggan -->
                    <a href="pelanggan.php" class="text-white/80 hover:text-white hover:bg-white/10 rounded-lg px-4 py-3 flex items-center gap-3 font-medium text-sm transition-colors">
                        <img src="assets/icons/nav_pelanggan.svg" alt="Pelanggan" class="w-5 h-5 opacity-90">
                        <span>Pelanggan</span>
                    </a>

                    <!-- Laporan -->
                    <a href="laporan.php" class="text-white/80 hover:text-white hover:bg-white/10 rounded-lg px-4 py-3 flex items-center gap-3 font-medium text-sm transition-colors">
                        <img src="assets/icons/nav_laporan.svg" alt="Laporan" class="w-5 h-5 opacity-90">
                        <span>Laporan</span>
                    </a>

                    <!-- Pengaturan -->
                    <a href="pengaturan.php" class="text-white/80 hover:text-white hover:bg-white/10 rounded-lg px-4 py-3 flex items-center gap-3 font-medium text-sm transition-colors">
                        <img src="assets/icons/nav_pengaturan.svg" alt="Pengaturan" class="w-5 h-5 opacity-90">
                        <span>Pengaturan</span>
                    </a>
                </nav>
            </div>

            <!-- Bottom User Profile Card -->
            <div class="p-4 border-t border-white/10 shrink-0">
                <div class="bg-black/15 rounded-xl p-2.5 flex items-center justify-between">
                    <div class="flex items-center gap-3 overflow-hidden">
                        <div class="w-9 h-9 rounded-full bg-[#FF9B45] text-white font-bold text-xs flex items-center justify-center shrink-0 shadow-sm">
                            BD
                        </div>
                        <div class="truncate">
                            <p class="text-white font-semibold text-sm leading-tight truncate"><?= htmlspecialchars($_SESSION['nama_lengkap'] ?? $_SESSION['user'] ?? 'Admin') ?></p>
                            <p class="text-[#828282] text-xs leading-tight mt-0.5"><?= htmlspecialchars(ucfirst($_SESSION['role'] ?? 'Admin')) ?></p>
                        </div>
                    </div>
                    <button type="button" onclick="openLogoutModal()" title="Keluar / Logout" class="p-1.5 text-white/60 hover:text-white transition-colors shrink-0">
                        <img src="assets/icons/logout.svg" alt="Logout" class="w-5 h-5">
                    </button>
                </div>
            </div>
        </aside>

        <!-- MAIN LAYOUT WRAPPER (Offset 240px on lg screens) -->
        <div class="flex-1 flex flex-col min-w-0 lg:ml-[240px]">

            <!-- TOP HEADER (Height 64px) -->
            <header class="h-16 bg-white border-b border-[#F0ECE9] sticky top-0 z-30 px-4 md:px-8 flex items-center justify-between">
                
                <!-- Left: Hamburger (Mobile) + Breadcrumb & Title -->
                <div class="flex items-center gap-3 md:gap-4">
                    <button onclick="toggleSidebar()" class="lg:hidden p-1.5 text-[#544337] hover:bg-[#F5F3F1] rounded-lg">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"></path></svg>
                    </button>

                    <div>
                        <!-- Breadcrumbs -->
                        <div class="flex items-center gap-1.5 text-xs">
                            <span class="text-[#544337]">Beranda</span>
                            <span class="text-[#DBC2B2]">/</span>
                            <span class="text-[#924C00] font-medium">Ringkasan</span>
                        </div>
                        <!-- Section Heading -->
                        <h1 class="text-[#1B1C1B] font-bold text-base md:text-lg leading-tight mt-0.5">Operasional</h1>
                    </div>
                </div>

                <!-- Right: Date Indicator, Notification Bell, User Avatar -->
                <div class="flex items-center gap-3 md:gap-5">
                    
                    <!-- Date Button -->
                    <div class="hidden sm:flex items-center gap-2 bg-[#F5F3F1] text-[#544337] px-3.5 py-1.5 rounded-lg text-xs md:text-sm font-medium border border-[#EBE8E5]">
                        <img src="assets/icons/header_calendar.svg" alt="Kalender" class="w-4 h-4">
                        <span>Jumat, 26 Sep 2025</span>
                    </div>

                    <!-- Notification Bell with Orange Dot -->
                    <button class="relative p-2 text-[#544337] hover:bg-[#F5F3F1] rounded-lg transition-colors" title="Notifikasi">
                        <img src="assets/icons/bell.svg" alt="Notifikasi" class="w-5 h-5">
                        <span class="absolute top-1.5 right-1.5 w-2 h-2 bg-[#FF9B45] rounded-full ring-2 ring-white"></span>
                    </button>

                    <!-- User Circle Icon -->
                    <div class="w-8 h-8 rounded-full bg-[#924C00] flex items-center justify-center text-white shadow-sm cursor-pointer hover:opacity-95 transition-opacity" title="Profil Pengguna">
                        <img src="assets/icons/header_user.svg" alt="User" class="w-4 h-4">
                    </div>
                </div>
            </header>

            <!-- MAIN CONTENT CONTAINER -->
            <main class="flex-1 p-4 md:p-8 w-full min-w-0">

                <?php if (isset($_SESSION['flash_msg'])): ?>
                    <!-- FLASH NOTIFICATION ALERT -->
                    <div id="flashAlert" class="bg-[#27AE60]/10 border border-[#27AE60]/30 rounded-2xl p-4 mb-6 flex items-center justify-between text-sm text-[#1B7A43] shadow-xs animate-in fade-in slide-in-from-top-2">
                        <div class="flex items-center gap-3">
                            <div class="w-8 h-8 rounded-full bg-[#27AE60]/20 flex items-center justify-center shrink-0">
                                <svg class="w-5 h-5 text-[#27AE60]" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
                            </div>
                            <span class="font-medium"><?= htmlspecialchars($_SESSION['flash_msg']) ?></span>
                        </div>
                        <button onclick="document.getElementById('flashAlert').remove()" class="text-[#1B7A43]/70 hover:text-[#1B7A43] p-1.5 rounded-lg hover:bg-[#27AE60]/10 transition-colors">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                        </button>
                    </div>
                    <?php unset($_SESSION['flash_msg']); ?>
                <?php endif; ?>
                
                <!-- TOP BANNER: Greeting & Operational Actions -->
                <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 mb-6">
                    <div>
                        <p class="text-[#924C00] text-xs font-semibold tracking-wider uppercase mb-1">RINGKASAN OPERASIONAL TOKO</p>
                        <h2 class="text-[#1B1C1B] text-2xl font-bold tracking-tight">Beranda Manajemen Pesanan</h2>
                    </div>

                    <!-- Action Buttons -->
                    <div class="flex items-center gap-2.5 shrink-0 flex-wrap">
                        <!-- Unduh Rekap Harian Button (Live CSV Export) -->
                        <a href="beranda.php?action=export_rekap" 
                            title="Unduh rekap penjualan harian format CSV/Excel" 
                            class="bg-[#F5F3F1] hover:bg-[#ECE9E6] text-[#1B1C1B] text-xs md:text-sm font-medium px-3.5 py-2.5 rounded-lg border border-[#E0E0E0]/70 flex items-center gap-2 transition-colors shadow-2xs">
                            <img src="assets/icons/download.svg" alt="Unduh" class="w-4 h-4">
                            <span>Unduh Rekap Harian</span>
                        </a>

                        <!-- Antrean Oven Link to Kanban -->
                        <a href="pesanan.php?tab=kanban" 
                            title="Buka alur produksi dan antrean oven" 
                            class="hidden md:flex bg-[#FFF8F2] hover:bg-[#FFEEDD] text-[#D97706] text-xs md:text-sm font-semibold px-3.5 py-2.5 rounded-lg border border-[#FF9B45]/30 items-center gap-2 transition-colors shadow-2xs">
                            <svg class="w-4 h-4 text-[#D97706]" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 18.657A8 8 0 016.343 7.343S7 9 9 10c0-2 .5-5 2.986-7C14 5 16.09 5.777 17.656 7.343A7.975 7.975 0 0120 13a7.975 7.975 0 01-2.343 5.657z"></path></svg>
                            <span>Antrean Oven</span>
                        </a>

                        <!-- Tambah Pesanan Baru Button -->
                        <button onclick="openOrderModal()" class="bg-[#FF9B45] hover:bg-[#E88C3D] text-white text-xs md:text-sm font-semibold px-4 py-2.5 rounded-lg flex items-center gap-2 transition-all shadow-button-orange active:scale-95">
                            <img src="assets/icons/plus.svg" alt="Tambah" class="w-4 h-4">
                            <span>Tambah Pesanan Baru</span>
                        </button>
                    </div>
                </div>

                <!-- ROW 1: 4 KPI STAT CARDS -->
                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6 mb-6">
                    
                    <!-- Card 1: Total Pesanan Hari Ini -->
                    <div class="bg-white rounded-lg p-6 shadow-card border border-[#F0ECE9] relative overflow-hidden flex flex-col justify-between transition-transform hover:-translate-y-0.5 duration-200">
                        <div class="absolute left-0 top-0 bottom-0 w-1 bg-[#FF9B45]"></div>
                        <div class="flex items-start justify-between">
                            <div>
                                <p class="text-[#828282] text-sm leading-snug">Total Pesanan<br>Hari Ini</p>
                                <p class="text-[#1C1C1C] text-3xl font-bold mt-2 tracking-tight"><?= htmlspecialchars($kpi_data['total_orders']) ?></p>
                            </div>
                            <div class="w-12 h-12 rounded-xl bg-[#FF9B45]/15 flex items-center justify-center shrink-0">
                                <img src="assets/icons/orders_clipboard.svg" alt="Pesanan Hari Ini" class="w-6 h-6">
                            </div>
                        </div>
                        <div class="flex items-center gap-1.5 mt-3 pt-1">
                            <img src="assets/icons/trend_up.svg" alt="Trend" class="w-3.5 h-3.5">
                            <span class="text-[#27AE60] text-xs font-medium"><?= htmlspecialchars($kpi_data['orders_diff']) ?></span>
                        </div>
                    </div>

                    <!-- Card 2: Pesanan Pending -->
                    <div class="bg-white rounded-lg p-6 shadow-card border border-[#F0ECE9] relative overflow-hidden flex flex-col justify-between transition-transform hover:-translate-y-0.5 duration-200">
                        <div class="absolute left-0 top-0 bottom-0 w-1 bg-[#E2B93B]"></div>
                        <div class="flex items-start justify-between">
                            <div>
                                <p class="text-[#828282] text-sm leading-snug">Pesanan<br>Pending</p>
                                <p class="text-[#1C1C1C] text-3xl font-bold mt-2 tracking-tight"><?= htmlspecialchars($kpi_data['pending_orders']) ?></p>
                            </div>
                            <div class="w-12 h-12 rounded-xl bg-[#E2B93B]/15 flex items-center justify-center shrink-0">
                                <img src="assets/icons/hourglass.svg" alt="Pesanan Pending" class="w-6 h-6">
                            </div>
                        </div>
                        <div class="flex items-center gap-1.5 mt-3 pt-1">
                            <img src="assets/icons/warning_exclamation.svg" alt="Perlu Konfirmasi" class="w-3.5 h-3.5">
                            <span class="text-[#E2B93B] text-xs font-medium"><?= htmlspecialchars($kpi_data['pending_note']) ?></span>
                        </div>
                    </div>

                    <!-- Card 3: Total Pemasukan Bulan Ini -->
                    <div class="bg-white rounded-lg p-6 shadow-card border border-[#F0ECE9] relative overflow-hidden flex flex-col justify-between transition-transform hover:-translate-y-0.5 duration-200">
                        <div class="absolute left-0 top-0 bottom-0 w-1 bg-[#27AE60]"></div>
                        <div class="flex items-start justify-between">
                            <div>
                                <p class="text-[#828282] text-sm leading-snug">Total Pemasukan<br>Bulan Ini</p>
                                <p class="text-[#1C1C1C] text-2xl font-bold mt-2 tracking-tight"><?= htmlspecialchars($kpi_data['total_revenue']) ?></p>
                            </div>
                            <div class="w-12 h-12 rounded-xl bg-[#27AE60]/15 flex items-center justify-center shrink-0">
                                <img src="assets/icons/wallet.svg" alt="Total Pemasukan" class="w-6 h-6">
                            </div>
                        </div>
                        <div class="flex items-center gap-1.5 mt-3 pt-1">
                            <img src="assets/icons/check_circle.svg" alt="Target" class="w-3.5 h-3.5">
                            <span class="text-[#27AE60] text-xs font-medium"><?= htmlspecialchars($kpi_data['revenue_target']) ?></span>
                        </div>
                    </div>

                    <!-- Card 4: Pelanggan Baru -->
                    <div class="bg-white rounded-lg p-6 shadow-card border border-[#F0ECE9] relative overflow-hidden flex flex-col justify-between transition-transform hover:-translate-y-0.5 duration-200">
                        <div class="absolute left-0 top-0 bottom-0 w-1 bg-[#2F80ED]"></div>
                        <div class="flex items-start justify-between">
                            <div>
                                <p class="text-[#828282] text-sm leading-snug">Pelanggan Baru</p>
                                <p class="text-[#1C1C1C] text-3xl font-bold mt-2 tracking-tight"><?= htmlspecialchars($kpi_data['new_customers']) ?></p>
                            </div>
                            <div class="w-12 h-12 rounded-xl bg-[#2F80ED]/15 flex items-center justify-center shrink-0">
                                <img src="assets/icons/users.svg" alt="Pelanggan Baru" class="w-6 h-6">
                            </div>
                        </div>
                        <div class="flex items-center gap-1.5 mt-3 pt-1">
                            <img src="assets/icons/user_plus.svg" alt="User Baru" class="w-3.5 h-3.5">
                            <span class="text-[#2F80ED] text-xs font-medium"><?= htmlspecialchars($kpi_data['customers_note']) ?></span>
                        </div>
                    </div>
                </div>

                <!-- ROW 2: FULL-WIDTH PESANAN PERLU TINDAKAN CARD -->
                <div class="bg-white rounded-lg p-6 shadow-card border border-[#F0ECE9] mb-6">
                    
                    <!-- Table Header Controls -->
                    <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4 mb-4">
                        <div class="flex items-center gap-3">
                            <h3 class="text-[#1C1C1C] text-base md:text-lg font-bold">Pesanan Perlu Tindakan</h3>
                            <span id="pending-count-badge" class="bg-[#E2B93B]/15 text-[#886C12] text-xs font-semibold px-2.5 py-0.5 rounded-full">
                                <?= count($pending_orders) ?> Pesanan
                            </span>
                        </div>

                        <!-- Right Controls: Status Filter Pills + Live Search -->
                        <div class="flex flex-wrap items-center gap-2.5">
                            <!-- Filter Pills -->
                            <div class="flex items-center bg-[#F5F3F1] p-1 rounded-lg text-xs font-medium">
                                <button type="button" onclick="filterOrdersByStatus('all', this)" id="tabFilterAll" 
                                    class="px-3 py-1.5 rounded-md bg-[#FF9B45] text-white font-semibold shadow-2xs transition-all status-filter-btn">
                                    Semua
                                </button>
                                <button type="button" onclick="filterOrdersByStatus('proses', this)" 
                                    class="px-3 py-1.5 rounded-md text-[#544337] hover:text-[#1C1C1C] transition-all status-filter-btn">
                                    Perlu Diproses
                                </button>
                                <button type="button" onclick="filterOrdersByStatus('kirim', this)" 
                                    class="px-3 py-1.5 rounded-md text-[#544337] hover:text-[#1C1C1C] transition-all status-filter-btn">
                                    Siap Kirim
                                </button>
                                <button type="button" onclick="filterOrdersByStatus('selesai', this)" 
                                    class="px-3 py-1.5 rounded-md text-[#544337] hover:text-[#1C1C1C] transition-all status-filter-btn">
                                    Selesai
                                </button>
                            </div>

                            <!-- Live Search Input -->
                            <div class="relative w-full sm:w-52">
                                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                    <img src="assets/icons/search.svg" alt="Cari" class="w-4 h-4 opacity-70">
                                </div>
                                <input type="text" id="orderSearchInput" onkeyup="filterOrdersTable()" placeholder="Cari pesanan..."
                                    class="w-full bg-[#F5F3F1] border border-transparent rounded-lg pl-9 pr-3 py-1.5 text-xs text-[#1C1C1C] placeholder-[#9CA3AF] focus:border-[#FF9B45] focus:bg-white focus:outline-none transition-all">
                            </div>
                        </div>
                    </div>

                    <!-- Table Container -->
                    <div class="overflow-x-auto rounded-lg border border-[#F0ECE9] shadow-sm">
                        <table class="w-full text-left border-collapse" id="ordersTable">
                            <thead>
                                <tr class="bg-[#FF9B45] text-white text-sm font-bold">
                                    <th class="px-5 py-3.5">Nama Pelanggan</th>
                                    <th class="px-5 py-3.5">Produk</th>
                                    <th class="px-5 py-3.5">Deadline</th>
                                    <th class="px-5 py-3.5">Pembayaran</th>
                                    <th class="px-5 py-3.5 text-center">Aksi</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-[#F0ECE9] text-sm">
                                <?php foreach ($pending_orders as $idx => $order): ?>
                                    <tr class="<?= $idx % 2 === 1 ? 'bg-[#FFF8F2]' : 'bg-white' ?> hover:bg-[#FFF3E8] transition-colors order-row" 
                                        data-search="<?= strtolower($order['customer'] . ' ' . $order['product'] . ' ' . $order['payment_status'] . ' ' . $order['id']) ?>"
                                        data-status="<?= htmlspecialchars($order['status'] ?? 'pending') ?>">
                                        <!-- Nama Pelanggan -->
                                        <td class="px-5 py-4 text-[#1B1C1B] font-medium whitespace-nowrap">
                                            <div class="font-semibold text-[#1C1C1C]"><?= htmlspecialchars($order['customer']) ?></div>
                                            <div class="text-[11px] text-[#828282] mt-0.5"><?= htmlspecialchars($order['id']) ?></div>
                                        </td>
                                        
                                        <!-- Produk -->
                                        <td class="px-5 py-4 text-[#544337] max-w-xs">
                                            <p class="truncate" title="<?= htmlspecialchars($order['product']) ?>"><?= htmlspecialchars($order['product']) ?></p>
                                        </td>

                                        <!-- Deadline -->
                                        <td class="px-5 py-4 whitespace-nowrap">
                                            <?php if ($order['is_urgent']): ?>
                                                <div class="flex items-center gap-1.5 text-[#EB5757] font-medium">
                                                    <img src="assets/icons/clock.svg" alt="Deadline" class="w-3.5 h-3.5">
                                                    <span><?= htmlspecialchars($order['deadline']) ?></span>
                                                </div>
                                            <?php else: ?>
                                                <span class="text-[#333333]"><?= htmlspecialchars($order['deadline']) ?></span>
                                            <?php endif; ?>
                                        </td>

                                        <!-- Pembayaran -->
                                        <td class="px-5 py-4 whitespace-nowrap">
                                            <span class="<?= $order['payment_class'] ?> px-3 py-1 rounded-full text-xs font-semibold">
                                                <?= htmlspecialchars($order['payment_status']) ?>
                                            </span>
                                        </td>

                                        <!-- Aksi -->
                                        <td class="px-5 py-4 text-center whitespace-nowrap">
                                            <button onclick="viewOrderDetail('<?= htmlspecialchars($order['id']) ?>', '<?= htmlspecialchars(addslashes($order['customer'])) ?>', '<?= htmlspecialchars(addslashes($order['product'])) ?>', '<?= htmlspecialchars($order['deadline']) ?>', '<?= htmlspecialchars($order['payment_status']) ?>', '<?= htmlspecialchars($order['status'] ?? 'pending') ?>', '<?= htmlspecialchars($order['total'] ?? 'Rp 0') ?>', '<?= htmlspecialchars(addslashes($order['no_wa'] ?? '-')) ?>', '<?= htmlspecialchars(addslashes($order['alamat'] ?? '-')) ?>')"
                                                class="text-[#FF9B45] hover:text-[#E88C3D] font-semibold text-sm inline-flex items-center gap-1 transition-colors group">
                                                <span>Lihat Detail</span>
                                                <img src="assets/icons/chevron_right.svg" alt="Detail" class="w-2 h-3.5 group-hover:translate-x-0.5 transition-transform">
                                            </button>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>

                    <!-- Table Footer Summary -->
                    <div class="mt-4 bg-[#F5F3F1] rounded-lg px-5 py-3.5 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-2">
                        <span id="table-footer-text" class="text-[#828282] text-xs md:text-sm">
                            Menampilkan 1-<?= count($pending_orders) ?> dari <?= count($pending_orders) ?> pesanan pending
                        </span>
                        <a href="#" class="text-[#FF9B45] hover:text-[#E88C3D] font-semibold text-xs md:text-sm flex items-center gap-1.5 transition-colors self-end sm:self-auto">
                            <span>Lihat Semua Pesanan</span>
                            <img src="assets/icons/arrow_right.svg" alt="Semua Pesanan" class="w-4 h-4">
                        </a>
                    </div>
                </div>

                <!-- ROW 3: DUA WIDGET SIDE-BY-SIDE (PENGIRIMAN & KALENDER) -->
                <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-6">
                    
                    <!-- Left Widget: Pengiriman Hari Ini -->
                    <div class="bg-white rounded-lg p-6 shadow-card border border-[#F0ECE9] flex flex-col justify-between">
                        <div>
                            <!-- Header -->
                            <div class="flex items-center justify-between pb-3 border-b border-[#F0ECE9] mb-4">
                                <div class="flex items-center gap-3">
                                    <div class="w-10 h-10 rounded-xl bg-[#FF9B45]/15 flex items-center justify-center shrink-0">
                                        <img src="assets/icons/truck.svg" alt="Pengiriman" class="w-5 h-5">
                                    </div>
                                    <div>
                                        <h3 class="text-[#1C1C1C] text-base font-bold leading-tight">Pengiriman Hari Ini</h3>
                                        <p class="text-[#828282] text-xs mt-0.5">Jumat, 26 Sep 2025</p>
                                    </div>
                                </div>
                                <span class="bg-[#27AE60]/15 text-[#1B7A43] text-xs font-semibold px-2.5 py-1 rounded-full">
                                    2 Batch Siap
                                </span>
                            </div>

                            <!-- Batches List -->
                            <div class="space-y-3">
                                <?php foreach ($deliveries as $deliv): ?>
                                    <div class="bg-[#F5F3F1] rounded-lg p-3.5 flex items-center justify-between border border-[#EBE8E5] hover:border-[#FF9B45]/40 transition-colors">
                                        <div class="flex items-center gap-3">
                                            <!-- Time Badge -->
                                            <div class="bg-white border border-[#E5E0DC] rounded-lg px-2.5 py-1 text-center min-w-[56px] shadow-2xs">
                                                <p class="text-[#EB5757] font-bold text-xs leading-tight"><?= htmlspecialchars($deliv['time']) ?></p>
                                                <p class="text-[#828282] text-[10px] font-medium leading-none mt-0.5">WIB</p>
                                            </div>
                                            <!-- Info -->
                                            <div>
                                                <h4 class="text-[#1C1C1C] font-semibold text-sm leading-tight"><?= htmlspecialchars($deliv['customer']) ?></h4>
                                                <p class="text-[#828282] text-xs mt-0.5"><?= htmlspecialchars($deliv['items']) ?></p>
                                            </div>
                                        </div>
                                        <!-- Status Badge -->
                                        <span class="<?= $deliv['status_class'] ?> text-xs font-semibold px-2.5 py-1 rounded-full whitespace-nowrap">
                                            <?= htmlspecialchars($deliv['status']) ?>
                                        </span>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        </div>

                        <!-- Footer -->
                        <div class="flex items-center justify-between pt-4 mt-4 border-t border-[#F0ECE9]">
                            <span class="text-[#828282] text-xs">Total 80 item disiapkan hari ini</span>
                            <a href="#" class="text-[#FF9B45] hover:text-[#E88C3D] font-semibold text-xs flex items-center gap-1 transition-colors">
                                <span>Kelola Pengiriman</span>
                                <img src="assets/icons/chevron_right_orange.svg" alt="Kelola" class="w-2.5 h-3">
                            </a>
                        </div>
                    </div>

                    <!-- Right Widget: Jadwal Pengiriman (Mini Calendar) -->
                    <div class="bg-white rounded-lg p-6 shadow-card border border-[#F0ECE9] flex flex-col justify-between">
                        <div>
                            <!-- Header -->
                            <div class="flex items-center justify-between pb-3 border-b border-[#F0ECE9] mb-4">
                                <div class="flex items-center gap-3">
                                    <div class="w-10 h-10 rounded-xl bg-[#FF9B45]/15 flex items-center justify-center shrink-0">
                                        <img src="assets/icons/calendar.svg" alt="Kalender" class="w-5 h-5">
                                    </div>
                                    <h3 class="text-[#1C1C1C] text-base font-bold">Jadwal Pengiriman</h3>
                                </div>
                                <div class="flex items-center gap-2">
                                    <span class="text-[#1C1C1C] text-sm font-semibold">September 2025</span>
                                    <div class="flex items-center gap-0.5">
                                        <button class="p-1 hover:bg-[#F5F3F1] rounded text-[#828282] transition-colors" title="Bulan Sebelumnya">
                                            <img src="assets/icons/chevron_left.svg" alt="Prev" class="w-3 h-3">
                                        </button>
                                        <button class="p-1 hover:bg-[#F5F3F1] rounded text-[#828282] transition-colors" title="Bulan Berikutnya">
                                            <img src="assets/icons/chevron_right_calendar.svg" alt="Next" class="w-3 h-3">
                                        </button>
                                    </div>
                                </div>
                            </div>

                            <!-- Calendar Day Names -->
                            <div class="grid grid-cols-7 text-center text-xs font-semibold text-[#828282] mb-2.5">
                                <div>Min</div>
                                <div>Sen</div>
                                <div>Sel</div>
                                <div>Rab</div>
                                <div>Kam</div>
                                <div>Jum</div>
                                <div>Sab</div>
                            </div>

                            <!-- Calendar Dates Grid -->
                            <div class="grid grid-cols-7 gap-y-2 text-center text-xs">
                                
                                <!-- Week 1 -->
                                <div class="text-[#828282]/40 py-1">31</div>
                                <div class="text-[#1B1C1B] font-medium py-1">1</div>
                                <div class="text-[#1B1C1B] font-medium py-1">2</div>
                                <div class="text-[#1B1C1B] font-medium py-1">3</div>
                                <div class="text-[#1B1C1B] font-medium py-1">4</div>
                                <div class="text-[#1B1C1B] font-medium py-1">5</div>
                                <div class="text-[#1B1C1B] font-medium py-1">6</div>

                                <!-- Week 2 -->
                                <div class="text-[#1B1C1B] font-medium py-1">7</div>
                                <div class="text-[#1B1C1B] font-medium py-1">8</div>
                                <div class="text-[#1B1C1B] font-medium py-1">9</div>
                                <div class="text-[#1B1C1B] font-medium py-1">10</div>
                                <div class="text-[#1B1C1B] font-medium py-1">11</div>
                                <div class="text-[#1B1C1B] font-medium py-1">12</div>
                                <div class="text-[#1B1C1B] font-medium py-1">13</div>

                                <!-- Week 3 -->
                                <div class="text-[#1B1C1B] font-medium py-1">14</div>
                                <div class="text-[#1B1C1B] font-medium py-1">15</div>
                                <div class="text-[#1B1C1B] font-medium py-1">16</div>
                                <div class="text-[#1B1C1B] font-medium py-1">17</div>
                                <div class="text-[#1B1C1B] font-medium py-1">18</div>
                                <div class="text-[#1B1C1B] font-medium py-1">19</div>
                                <!-- 20 has delivery dot -->
                                <div class="flex flex-col items-center justify-center">
                                    <span class="text-[#1B1C1B] font-medium">20</span>
                                    <span class="w-1.5 h-1.5 rounded-full bg-[#FF9B45] mt-0.5"></span>
                                </div>

                                <!-- Week 4 -->
                                <div class="text-[#1B1C1B] font-medium py-1">21</div>
                                <div class="text-[#1B1C1B] font-medium py-1">22</div>
                                <div class="text-[#1B1C1B] font-medium py-1">23</div>
                                <!-- 24 has delivery dot -->
                                <div class="flex flex-col items-center justify-center">
                                    <span class="text-[#1B1C1B] font-medium">24</span>
                                    <span class="w-1.5 h-1.5 rounded-full bg-[#FF9B45] mt-0.5"></span>
                                </div>
                                <!-- 25 past highlight -->
                                <div class="flex items-center justify-center">
                                    <span class="w-7 h-7 rounded-full bg-[#F5F3F1] text-[#828282] flex items-center justify-center">25</span>
                                </div>
                                <!-- 26 TODAY ACTIVE HIGHLIGHT (Orange Circle + Dot) -->
                                <div class="flex flex-col items-center justify-center">
                                    <span class="w-7 h-7 rounded-full bg-[#FF9B45] text-white font-bold flex items-center justify-center shadow-sm">26</span>
                                    <span class="w-1.5 h-1.5 rounded-full bg-[#FF9B45] mt-0.5"></span>
                                </div>
                                <!-- 27 has delivery dot -->
                                <div class="flex flex-col items-center justify-center">
                                    <span class="text-[#1B1C1B] font-medium">27</span>
                                    <span class="w-1.5 h-1.5 rounded-full bg-[#FF9B45] mt-0.5"></span>
                                </div>

                                <!-- Week 5 -->
                                <!-- 28 has delivery dot -->
                                <div class="flex flex-col items-center justify-center">
                                    <span class="text-[#1B1C1B] font-medium">28</span>
                                    <span class="w-1.5 h-1.5 rounded-full bg-[#FF9B45] mt-0.5"></span>
                                </div>
                                <div class="text-[#1B1C1B] font-medium py-1">29</div>
                                <!-- 30 has delivery dot -->
                                <div class="flex flex-col items-center justify-center">
                                    <span class="text-[#1B1C1B] font-medium">30</span>
                                    <span class="w-1.5 h-1.5 rounded-full bg-[#FF9B45] mt-0.5"></span>
                                </div>
                                <div class="text-[#828282]/40 py-1">1</div>
                                <div class="text-[#828282]/40 py-1">2</div>
                                <div class="text-[#828282]/40 py-1">3</div>
                                <div class="text-[#828282]/40 py-1">4</div>
                            </div>
                        </div>

                        <!-- Footer Legend -->
                        <div class="flex items-center justify-between pt-3 mt-4 border-t border-[#F0ECE9] text-xs">
                            <div class="flex items-center gap-1.5">
                                <span class="w-2 h-2 rounded-full bg-[#FF9B45]"></span>
                                <span class="text-[#828282]">Ada jadwal kirim</span>
                            </div>
                            <div class="flex items-center gap-1.5">
                                <span class="w-2 h-2 rounded-full bg-[#FF9B45]"></span>
                                <span class="text-[#1C1C1C] font-medium">Hari ini</span>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- ROW 4: AKTIVITAS TERBARU (FULL WIDTH CARD) -->
                <div class="bg-white rounded-lg p-6 shadow-card border border-[#F0ECE9]">
                    
                    <!-- Header -->
                    <div class="flex items-center justify-between mb-6">
                        <div class="flex items-center gap-2.5">
                            <img src="assets/icons/history.svg" alt="Aktivitas" class="w-5 h-5">
                            <h3 class="text-[#1C1C1C] text-base font-bold">Aktivitas Terbaru</h3>
                        </div>
                        <span class="bg-[#EAE8E6] text-[#828282] text-xs font-medium px-3 py-1 rounded-full">
                            Hari Ini
                        </span>
                    </div>

                    <!-- Continuous Vertical Line Timeline -->
                    <div class="relative timeline-container pl-6 space-y-6">
                        <?php foreach ($activities as $act): ?>
                            <div class="relative flex flex-col sm:flex-row sm:items-center sm:justify-between gap-1 group">
                                <!-- Dot with white ring -->
                                <div class="absolute -left-6 top-1 sm:top-1.5 w-3.5 h-3.5 rounded-full <?= $act['dot_color'] ?> ring-4 ring-white shadow-xs"></div>
                                
                                <!-- Activity Content -->
                                <div class="text-sm pr-4">
                                    <?= $act['text'] ?>
                                </div>

                                <!-- Timestamp -->
                                <span class="text-[#828282] text-xs whitespace-nowrap shrink-0">
                                    <?= htmlspecialchars($act['time']) ?>
                                </span>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            </main>
        </div>
    </div>

    <!-- ============================================================== -->
    <!-- MODAL 1: ORDER DETAIL MODAL (INTERAKTIF DENGAN AKSI STATUS)    -->
    <!-- ============================================================== -->
    <div id="orderDetailModal" class="fixed inset-0 bg-black/50 z-50 flex items-center justify-center p-4 hidden backdrop-blur-xs transition-opacity duration-200">
        <div class="bg-white rounded-2xl max-w-lg w-full p-6 shadow-2xl relative animate-in fade-in zoom-in-95 duration-150 max-h-[92vh] overflow-y-auto">
            <!-- Header -->
            <div class="flex items-center justify-between pb-4 border-b border-[#F0ECE9]">
                <div class="flex items-center gap-2.5">
                    <span class="w-3 h-3 rounded-full bg-[#FF9B45]"></span>
                    <div>
                        <h3 class="font-bold text-lg text-[#1C1C1C]">Detail Pesanan</h3>
                        <p id="modalOrderId" class="text-xs font-bold text-[#FF9B45] leading-tight">-</p>
                    </div>
                </div>
                <div class="flex items-center gap-2">
                    <span id="modalStatusBadge" class="px-2.5 py-1 rounded-full text-xs font-semibold bg-[#E2B93B]/20 text-[#886C12]">
                        Proses
                    </span>
                    <button onclick="closeModal('orderDetailModal')" class="text-[#828282] hover:text-[#1C1C1C] p-1 rounded-lg">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                    </button>
                </div>
            </div>

            <!-- Content -->
            <div class="py-5 space-y-4">
                <!-- Customer Info Card -->
                <div class="bg-[#F9F7F5] border border-[#F0ECE9] rounded-xl p-3.5 space-y-2">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-[10px] text-[#828282] uppercase font-semibold">Nama Pelanggan</p>
                            <p id="modalCustomer" class="text-sm font-bold text-[#1C1C1C]">-</p>
                        </div>
                        <a id="modalWaLink" href="#" target="_blank" class="inline-flex items-center gap-1.5 px-2.5 py-1 bg-[#27AE60]/10 hover:bg-[#27AE60]/20 text-[#1B7A43] rounded-lg text-xs font-semibold transition-colors">
                            <svg class="w-3.5 h-3.5" fill="currentColor" viewBox="0 0 24 24"><path d="M.057 24l1.687-6.163c-1.041-1.804-1.588-3.849-1.587-5.946.003-6.556 5.338-11.891 11.893-11.891 3.181.001 6.167 1.24 8.413 3.488 2.245 2.248 3.481 5.236 3.48 8.414-.003 6.557-5.338 11.892-11.893 11.892-1.99-.001-3.951-.5-5.688-1.448l-6.305 1.654zm6.597-3.807c1.676.995 3.276 1.591 5.392 1.592 5.448 0 9.886-4.434 9.889-9.885.002-5.462-4.415-9.89-9.881-9.892-5.452 0-9.887 4.434-9.889 9.884-.001 2.225.651 3.891 1.746 5.634l-.999 3.648 3.742-.981z"/></svg>
                            <span id="modalWa">-</span>
                        </a>
                    </div>
                    <div>
                        <p class="text-[10px] text-[#828282] uppercase font-semibold">Alamat Pengiriman</p>
                        <p id="modalAddress" class="text-xs text-[#544337] mt-0.5">-</p>
                    </div>
                </div>

                <!-- Products Summary -->
                <div>
                    <p class="text-xs text-[#828282] uppercase font-semibold mb-1">Rincian Produk</p>
                    <div id="modalProduct" class="text-xs text-[#544337] bg-[#F5F3F1] p-3 rounded-xl border border-[#EBE8E5] leading-relaxed">
                        -
                    </div>
                </div>

                <!-- Timing & Financials Grid -->
                <div class="grid grid-cols-2 gap-3">
                    <div class="bg-[#FFF8F2] border border-[#FF9B45]/20 rounded-xl p-3">
                        <p class="text-[10px] text-[#828282] uppercase font-semibold">Batas Waktu (Deadline)</p>
                        <p id="modalDeadline" class="text-xs font-bold text-[#EB5757] mt-1">-</p>
                    </div>
                    <div class="bg-[#F9F7F5] border border-[#F0ECE9] rounded-xl p-3">
                        <p class="text-[10px] text-[#828282] uppercase font-semibold">Total Tagihan</p>
                        <p id="modalTotal" class="text-sm font-bold text-[#FF9B45] mt-1">-</p>
                    </div>
                </div>

                <div>
                    <p class="text-[10px] text-[#828282] uppercase font-semibold mb-1">Status Pembayaran</p>
                    <span id="modalPayment" class="inline-block px-3 py-1 rounded-full text-xs font-semibold bg-[#27AE60]/15 text-[#1B7A43]">
                        -
                    </span>
                </div>
            </div>

            <!-- Quick Status Advance Actions Form -->
            <form id="updateStatusForm" method="POST" action="beranda.php" class="pt-4 border-t border-[#F0ECE9] space-y-3">
                <input type="hidden" name="action" value="update_status">
                <input type="hidden" name="order_id" id="modalUpdateOrderId" value="">
                <input type="hidden" name="new_status" id="modalNewStatusInput" value="proses">

                <p class="text-[11px] font-semibold text-[#828282] uppercase tracking-wider">Perbarui Status Operasional:</p>
                <div class="grid grid-cols-3 gap-2">
                    <button type="button" onclick="submitStatusChange('proses')" 
                        class="px-2.5 py-2 bg-[#FFF8F2] hover:bg-[#FFEEDD] border border-[#FF9B45]/40 text-[#D97706] rounded-xl text-xs font-semibold transition-colors flex items-center justify-center gap-1">
                        <span>🔥</span>
                        <span>Oven/Proses</span>
                    </button>
                    <button type="button" onclick="submitStatusChange('kirim')" 
                        class="px-2.5 py-2 bg-[#F0F7FF] hover:bg-[#E0EFFF] border border-[#2F80ED]/40 text-[#2F80ED] rounded-xl text-xs font-semibold transition-colors flex items-center justify-center gap-1">
                        <span>🚚</span>
                        <span>Siap Kirim</span>
                    </button>
                    <button type="button" onclick="submitStatusChange('selesai')" 
                        class="px-2.5 py-2 bg-[#F2FBF6] hover:bg-[#E2F7EB] border border-[#27AE60]/40 text-[#1B7A43] rounded-xl text-xs font-semibold transition-colors flex items-center justify-center gap-1">
                        <span>✓</span>
                        <span>Selesai</span>
                    </button>
                </div>

                <div class="flex items-center justify-end pt-2">
                    <button type="button" onclick="closeModal('orderDetailModal')" class="px-4 py-2 bg-[#F5F3F1] text-[#1B1C1B] rounded-lg text-xs font-medium hover:bg-[#EAE8E6] transition-colors">
                        Tutup
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- ============================================================== -->
    <!-- MODAL 2: TAMBAH PESANAN BARU (LENGKAP SESUAI FIGMA & PESANAN.PHP) -->
    <!-- ============================================================== -->
    <div id="orderModal" class="fixed inset-0 z-50 hidden flex items-center justify-center p-4">
        <!-- Backdrop with blur -->
        <div onclick="closeOrderModal()" class="fixed inset-0 bg-black/50 backdrop-blur-xs transition-opacity duration-200"></div>

        <!-- Modal Card Container -->
        <div class="bg-white rounded-2xl max-w-[720px] w-full p-6 sm:p-8 shadow-2xl relative z-10 animate-in fade-in zoom-in-95 duration-150 max-h-[92vh] overflow-y-auto">
            
            <!-- Header -->
            <div class="flex items-start justify-between pb-4 border-b border-[#F0ECE9] mb-6">
                <div>
                    <h3 class="text-xl font-bold text-[#1C1C1C]">Tambah Pesanan Baru</h3>
                    <p class="text-[#828282] text-xs mt-1">Isi detail pesanan, rincian produk bakery, dan jadwal pengiriman.</p>
                </div>
                <button onclick="closeOrderModal()" class="text-[#828282] hover:text-[#1C1C1C] p-1.5 rounded-lg hover:bg-[#F5F3F1] transition-colors" title="Tutup">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                </button>
            </div>

            <!-- Form -->
            <form id="orderForm" method="POST" action="beranda.php" class="space-y-6">
                <input type="hidden" name="action" value="tambah_pesanan">
                
                <!-- SECTION 1: DATA PELANGGAN -->
                <div>
                    <h4 class="text-xs font-bold text-[#1C1C1C] uppercase tracking-wider mb-2.5">Data Pelanggan</h4>
                    
                    <!-- Customer Selector Dropdown -->
                    <div class="relative mb-2">
                        <select id="custSelect" name="id_pelanggan" onchange="onSelectCustomerChange(this)"
                            class="w-full bg-white border border-[#E0E0E0] rounded-lg px-3.5 py-2.5 text-xs text-[#1C1C1C] font-medium focus:border-[#FF9B45] focus:ring-1 focus:ring-[#FF9B45] focus:outline-none transition-colors">
                            <?php foreach ($customers_for_select as $cust): ?>
                                <option value="<?= $cust['id'] ?>">
                                    <?= htmlspecialchars($cust['nama']) ?> (<?= htmlspecialchars($cust['no_wa']) ?>)
                                </option>
                            <?php endforeach; ?>
                            <option value="new">+ Tambah Pelanggan Baru</option>
                        </select>
                    </div>

                    <!-- New Customer Custom Inputs (Hidden by default) -->
                    <div id="newCustomerFields" class="hidden bg-[#FFF8F2] border border-[#FF9B45]/30 rounded-xl p-3.5 space-y-2.5 mb-3">
                        <p class="text-xs font-bold text-[#924C00]">Detail Pelanggan Baru</p>
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-2.5">
                            <input type="text" name="nama_pelanggan_baru" placeholder="Nama Lengkap Pelanggan" class="w-full bg-white border border-[#E0E0E0] rounded-lg px-3 py-2 text-xs outline-none focus:border-[#FF9B45]">
                            <input type="text" name="no_wa_baru" placeholder="Nomor WhatsApp (08xxx)" class="w-full bg-white border border-[#E0E0E0] rounded-lg px-3 py-2 text-xs outline-none focus:border-[#FF9B45]">
                        </div>
                        <input type="text" name="alamat_baru" placeholder="Alamat Pengiriman Lengkap" class="w-full bg-white border border-[#E0E0E0] rounded-lg px-3 py-2 text-xs outline-none focus:border-[#FF9B45]">
                    </div>

                    <!-- Detail Pelanggan Box (3 Columns Preview) -->
                    <div class="bg-[#F9F7F5] border border-[#F0ECE9] rounded-xl p-3.5 grid grid-cols-1 sm:grid-cols-3 gap-3 text-xs">
                        <div>
                            <p class="text-[#828282] uppercase text-[10px] font-semibold">Nama Pelanggan</p>
                            <p id="previewCustName" class="font-bold text-[#1C1C1C] mt-0.5"><?= htmlspecialchars($customers_for_select[0]['nama'] ?? 'Ibu Sari Dewi') ?></p>
                        </div>
                        <div>
                            <p class="text-[#828282] uppercase text-[10px] font-semibold">No. WhatsApp</p>
                            <p id="previewCustWa" class="font-bold text-[#1C1C1C] mt-0.5"><?= htmlspecialchars($customers_for_select[0]['no_wa'] ?? '0812-3456-7890') ?></p>
                        </div>
                        <div>
                            <p class="text-[#828282] uppercase text-[10px] font-semibold">Alamat Pengiriman</p>
                            <p id="previewCustAddress" class="font-medium text-[#1C1C1C] mt-0.5 truncate"><?= htmlspecialchars($customers_for_select[0]['alamat'] ?? 'Komplek Melati No. 4, Blok B') ?></p>
                        </div>
                    </div>
                </div>

                <!-- SECTION 2: DETAIL PESANAN -->
                <div>
                    <div class="flex items-center justify-between mb-3">
                        <h4 class="text-xs font-bold text-[#1C1C1C] uppercase tracking-wider">Detail Pesanan</h4>
                        <span class="text-xs text-[#828282]" id="itemCountLabel">2 Item Dipilih</span>
                    </div>

                    <!-- Items Table -->
                    <div class="border border-[#F0ECE9] rounded-xl overflow-x-auto mb-3">
                        <table class="w-full text-left text-xs min-w-[560px]">
                            <thead class="bg-[#FBF9F7] text-[#828282] font-semibold uppercase border-b border-[#F0ECE9]">
                                <tr>
                                    <th class="px-3.5 py-2.5">Produk</th>
                                    <th class="px-3.5 py-2.5">Kemasan</th>
                                    <th class="px-3.5 py-2.5 w-24">Jumlah</th>
                                    <th class="px-3.5 py-2.5">Harga Satuan</th>
                                    <th class="px-3.5 py-2.5">Subtotal</th>
                                    <th class="px-3 py-2.5 text-center w-10">Aksi</th>
                                </tr>
                            </thead>
                            <tbody id="orderItemsTableBody" class="divide-y divide-[#F0ECE9] bg-white">
                                <!-- Default Row 1: Roti Tawar Kupas Special (Initial qty 4 triggers MOQ alert!) -->
                                <tr class="order-product-row" data-min-order="5" data-price="15000">
                                    <td class="px-3.5 py-3">
                                        <input type="hidden" name="items[0][nama]" value="Roti Tawar Kupas Special" class="item-name-input">
                                        <input type="hidden" name="items[0][id_varian]" value="1" class="item-varian-input">
                                        <p class="font-bold text-[#1C1C1C] item-title">Roti Tawar Kupas Special</p>
                                        <p class="text-[10px] text-[#EB5757] font-semibold item-moq-label">Min. 5 pcs</p>
                                    </td>
                                    <td class="px-3.5 py-3 text-[#544337]">
                                        <span class="item-kemasan-text">Mika (10 pcs)</span>
                                        <input type="hidden" name="items[0][kemasan]" value="Mika (10 pcs)" class="item-kemasan-input">
                                    </td>
                                    <td class="px-3.5 py-3">
                                        <input type="number" name="items[0][qty]" value="4" min="1" oninput="calculateOrderTotal()" 
                                            class="item-qty-input w-16 px-2 py-1 border-2 border-[#EB5757] text-[#EB5757] font-bold rounded text-center focus:outline-none transition-colors">
                                    </td>
                                    <td class="px-3.5 py-3 text-[#544337]">
                                        <span class="item-price-text">Rp 15.000</span>
                                        <input type="hidden" name="items[0][harga]" value="15000" class="item-price-input">
                                    </td>
                                    <td class="px-3.5 py-3 font-bold text-[#1C1C1C] item-subtotal-text">Rp 60.000</td>
                                    <td class="px-3 py-3 text-center">
                                        <button type="button" onclick="removeOrderProductRow(this)" class="text-[#828282] hover:text-[#EB5757]" title="Hapus Item">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg>
                                        </button>
                                    </td>
                                </tr>

                                <!-- Default Row 2: Donat Kentang Coklat -->
                                <tr class="order-product-row" data-min-order="1" data-price="25000">
                                    <td class="px-3.5 py-3">
                                        <input type="hidden" name="items[1][nama]" value="Donat Kentang Coklat Meises" class="item-name-input">
                                        <input type="hidden" name="items[1][id_varian]" value="2" class="item-varian-input">
                                        <p class="font-bold text-[#1C1C1C] item-title">Donat Kentang Coklat Meises</p>
                                        <p class="text-[10px] text-[#828282] item-moq-label">Ready Batch Pagi</p>
                                    </td>
                                    <td class="px-3.5 py-3 text-[#544337]">
                                        <span class="item-kemasan-text">Mika (6 pcs)</span>
                                        <input type="hidden" name="items[1][kemasan]" value="Mika (6 pcs)" class="item-kemasan-input">
                                    </td>
                                    <td class="px-3.5 py-3">
                                        <input type="number" name="items[1][qty]" value="2" min="1" oninput="calculateOrderTotal()" 
                                            class="item-qty-input w-16 px-2 py-1 border border-[#E0E0E0] text-[#1C1C1C] font-semibold rounded text-center focus:outline-none focus:border-[#FF9B45] transition-colors">
                                    </td>
                                    <td class="px-3.5 py-3 text-[#544337]">
                                        <span class="item-price-text">Rp 25.000</span>
                                        <input type="hidden" name="items[1][harga]" value="25000" class="item-price-input">
                                    </td>
                                    <td class="px-3.5 py-3 font-bold text-[#1C1C1C] item-subtotal-text">Rp 50.000</td>
                                    <td class="px-3 py-3 text-center">
                                        <button type="button" onclick="removeOrderProductRow(this)" class="text-[#828282] hover:text-[#EB5757]" title="Hapus Item">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg>
                                        </button>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>

                    <!-- Tambah Produk Button -->
                    <button type="button" onclick="addOrderProductRow()" 
                        class="w-full py-2.5 border border-dashed border-[#FF9B45] text-[#FF9B45] hover:bg-[#FF9B45]/10 text-xs font-semibold rounded-xl flex items-center justify-center gap-1.5 transition-colors mb-3">
                        <span>+ Tambah Produk</span>
                    </button>

                    <!-- Validation Callout Alert (MOQ Warning) -->
                    <div id="moqAlertBox" class="bg-[#FFF5F5] border border-[#EB5757]/30 rounded-xl p-3 flex items-start gap-2.5">
                        <span class="text-base text-[#EB5757] shrink-0">🔒</span>
                        <p class="text-xs text-[#EB5757] leading-relaxed" id="moqAlertMessage">
                            <strong>Roti Tawar Kupas Special</strong> — Jumlah pesanan <span id="currentMoqQty">4</span> pcs (Minimum order 5 pcs). Pesanan dikunci dan tidak dapat disimpan sebelum memenuhi jumlah minimum.
                        </p>
                    </div>
                </div>

                <!-- SECTION 3: PENGIRIMAN & PEMBAYARAN -->
                <div>
                    <h4 class="text-xs font-bold text-[#1C1C1C] uppercase tracking-wider mb-3">Pengiriman & Pembayaran</h4>
                    
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                        
                        <!-- Left: Tanggal & Waktu & Catatan -->
                        <div class="space-y-3.5">
                            <div>
                                <label class="block text-xs font-semibold text-[#333333] mb-1">Tanggal Pengiriman</label>
                                <div class="relative">
                                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-[#828282]">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path></svg>
                                    </div>
                                    <input type="date" name="tgl_kirim" value="<?= date('Y-m-d') ?>" 
                                        class="w-full bg-white border border-[#E0E0E0] rounded-lg pl-9 pr-3.5 py-2 text-xs text-[#1C1C1C] focus:border-[#FF9B45] focus:outline-none">
                                </div>
                            </div>

                            <div>
                                <label class="block text-xs font-semibold text-[#333333] mb-1">Waktu / Jam Pengiriman</label>
                                <div class="relative">
                                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-[#828282]">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                                    </div>
                                    <input type="time" name="waktu_kirim" value="14:00" 
                                        class="w-full bg-white border border-[#E0E0E0] rounded-lg pl-9 pr-3.5 py-2 text-xs text-[#1C1C1C] focus:border-[#FF9B45] focus:outline-none">
                                </div>
                            </div>

                            <div>
                                <label class="block text-xs font-semibold text-[#333333] mb-1">Catatan Dapur / Driver</label>
                                <textarea name="catatan" rows="2" placeholder="Catatan khusus: roti tawar diiris tipis, antar sebelum jam 3 sore..." 
                                    class="w-full bg-white border border-[#E0E0E0] rounded-lg p-3 text-xs text-[#1C1C1C] focus:border-[#FF9B45] focus:outline-none"></textarea>
                            </div>
                        </div>

                        <!-- Right: Total Tagihan & Status Bayar -->
                        <div class="bg-[#F9F7F5] border border-[#F0ECE9] rounded-2xl p-4 flex flex-col justify-between">
                            <div>
                                <p class="text-[11px] font-bold text-[#828282] uppercase tracking-wider">TOTAL TAGIHAN</p>
                                <h3 class="text-2xl font-bold text-[#FF9B45] mt-0.5 tracking-tight" id="orderGrandTotal">Rp 110.000</h3>
                                
                                <div class="mt-4">
                                    <label class="block text-xs font-semibold text-[#333333] mb-2">Status Pembayaran</label>
                                    <div class="grid grid-cols-3 gap-2">
                                        <button type="button" onclick="selectPaymentStatus('belum_bayar', this)" id="payBtn1" 
                                            class="py-1.5 px-2 rounded-lg text-xs font-semibold bg-[#544337] text-white shadow-2xs transition-colors pay-status-btn">
                                            Belum Bayar
                                        </button>
                                        <button type="button" onclick="selectPaymentStatus('hutang', this)" id="payBtn2" 
                                            class="py-1.5 px-2 rounded-lg text-xs font-semibold bg-white border border-[#E0E0E0] text-[#544337] hover:bg-gray-50 transition-colors pay-status-btn">
                                            Hutang
                                        </button>
                                        <button type="button" onclick="selectPaymentStatus('lunas', this)" id="payBtn3" 
                                            class="py-1.5 px-2 rounded-lg text-xs font-semibold bg-white border border-[#E0E0E0] text-[#544337] hover:bg-gray-50 transition-colors pay-status-btn">
                                            Lunas
                                        </button>
                                    </div>
                                    <input type="hidden" name="status_bayar" id="selectedPaymentStatus" value="belum_bayar">
                                </div>
                            </div>

                            <div class="mt-4">
                                <label class="block text-xs font-semibold text-[#333333] mb-1">Metode Pembayaran</label>
                                <select name="metode_bayar" class="w-full bg-white border border-[#E0E0E0] rounded-lg px-3 py-2 text-xs text-[#1C1C1C] focus:border-[#FF9B45] focus:outline-none">
                                    <option value="cash">Tunai / Cash saat Kurir Tiba</option>
                                    <option value="transfer">Transfer Bank (BCA / Mandiri)</option>
                                </select>
                            </div>
                        </div>

                    </div>
                </div>

                <!-- MODAL FOOTER -->
                <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3 pt-4 border-t border-[#F0ECE9]">
                    <div id="footerMoqWarning" class="flex items-center gap-1.5 text-xs text-[#EB5757] font-medium">
                        <span>ⓘ</span>
                        <span>Penuhi minimum order untuk menyimpan</span>
                    </div>

                    <div class="flex items-center gap-3 ml-auto">
                        <button type="button" onclick="closeOrderModal()" 
                            class="bg-white hover:bg-gray-100 text-[#544337] text-xs font-semibold px-4 py-2.5 rounded-lg border border-[#E0E0E0] transition-colors">
                            Batal
                        </button>

                        <button type="submit" id="submitOrderBtn" disabled 
                            class="bg-[#D8D2CB] text-white cursor-not-allowed text-xs font-semibold px-5 py-2.5 rounded-lg flex items-center gap-1.5 transition-all shadow-xs">
                            <span id="submitLockIcon">🔒</span>
                            <span>Simpan Pesanan</span>
                        </button>
                    </div>
                </div>

            </form>
        </div>
    </div>

    <!-- ============================================================== -->
    <!-- MODAL POPOUT: LOGOUT / KELUAR AKUN (SESUAI FIGMA SCREENSHOT)   -->
    <!-- ============================================================== -->
    <div id="logoutModal" class="fixed inset-0 z-50 hidden flex items-center justify-center p-4">
        <!-- Backdrop with blur -->
        <div onclick="closeLogoutModal()" class="fixed inset-0 bg-black/50 backdrop-blur-xs transition-opacity duration-200"></div>

        <!-- Modal Card Container -->
        <div class="bg-white rounded-3xl max-w-sm w-full p-6 sm:p-7 shadow-2xl relative z-10 animate-in fade-in zoom-in-95 duration-150 text-center">
            <!-- Top Icon -->
            <div class="w-14 h-14 rounded-2xl bg-[#FFF0F0] text-[#EB5757] flex items-center justify-center mx-auto mb-4 shadow-2xs">
                <svg class="w-7 h-7" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"></path>
                </svg>
            </div>

            <!-- Title -->
            <h3 class="text-base sm:text-lg font-bold text-[#1C1C1C]">Keluar dari Akun?</h3>
            
            <!-- Description -->
            <p class="text-xs text-[#828282] mt-1.5 mb-6 leading-relaxed max-w-[280px] mx-auto">
                Anda akan keluar dari sesi ini. Pastikan semua perubahan sudah tersimpan.
            </p>

            <!-- Action Buttons -->
            <div class="space-y-2.5">
                <a href="logout.php" 
                    class="w-full bg-[#EB5757] hover:bg-[#D32F2F] text-white text-xs sm:text-sm font-semibold py-3 px-4 rounded-xl flex items-center justify-center gap-2 shadow-xs transition-colors">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013 3v1"></path>
                    </svg>
                    <span>Ya, Keluar</span>
                </a>

                <button type="button" onclick="closeLogoutModal()" 
                    class="w-full bg-white hover:bg-gray-50 text-[#544337] border border-[#E0E0E0] text-xs sm:text-sm font-semibold py-2.5 px-4 rounded-xl transition-colors">
                    Batal
                </button>
            </div>

            <!-- Encrypted Session Note -->
            <div class="mt-5 flex items-center justify-center gap-1.5 text-[11px] text-[#A0A0A0]">
                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"></path>
                </svg>
                <span>Sesi login terenkripsi Berkat Dinasti</span>
            </div>
        </div>
    </div>

    <!-- JAVASCRIPT LOGIC -->
    <script>
        // Master Data injected from PHP
        const CUSTOMERS_DATA = <?= json_encode($customers_for_select, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP) ?>;
        const PRODUCTS_DATA = <?= json_encode($products_for_select, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP) ?>;

        let currentStatusFilter = 'all';

        // Toggle Mobile Sidebar
        function toggleSidebar() {
            const sidebar = document.getElementById('main-sidebar');
            const overlay = document.getElementById('sidebar-overlay');
            const isHidden = sidebar.classList.contains('-translate-x-full');

            if (isHidden) {
                sidebar.classList.remove('-translate-x-full');
                overlay.classList.remove('hidden');
            } else {
                sidebar.classList.add('-translate-x-full');
                overlay.classList.add('hidden');
            }
        }

        // Filter Orders by Status Pills
        function filterOrdersByStatus(status, btn) {
            currentStatusFilter = status;
            
            // Update button styles
            const buttons = document.querySelectorAll('.status-filter-btn');
            buttons.forEach(b => {
                b.className = 'px-3 py-1.5 rounded-md text-[#544337] hover:text-[#1C1C1C] transition-all status-filter-btn';
            });
            btn.className = 'px-3 py-1.5 rounded-md bg-[#FF9B45] text-white font-semibold shadow-2xs transition-all status-filter-btn';

            applyOrdersFilter();
        }

        // Live Table Search Filtering
        function filterOrdersTable() {
            applyOrdersFilter();
        }

        // Combined Filter Engine
        function applyOrdersFilter() {
            const searchInput = document.getElementById('orderSearchInput');
            const query = searchInput ? searchInput.value.toLowerCase().trim() : '';
            const rows = document.querySelectorAll('.order-row');
            let visibleCount = 0;

            rows.forEach(row => {
                const searchData = row.getAttribute('data-search') || '';
                const rowStatus = row.getAttribute('data-status') || '';

                const matchesQuery = !query || searchData.includes(query);
                
                let matchesStatus = false;
                if (currentStatusFilter === 'all') {
                    matchesStatus = true;
                } else if (currentStatusFilter === 'proses') {
                    matchesStatus = (rowStatus === 'proses' || rowStatus === 'pending');
                } else if (currentStatusFilter === 'kirim') {
                    matchesStatus = (rowStatus === 'kirim');
                } else if (currentStatusFilter === 'selesai') {
                    matchesStatus = (rowStatus === 'selesai');
                }

                if (matchesQuery && matchesStatus) {
                    row.style.display = '';
                    visibleCount++;
                } else {
                    row.style.display = 'none';
                }
            });

            // Update footer count text
            const footerText = document.getElementById('table-footer-text');
            if (footerText) {
                footerText.textContent = `Menampilkan ${visibleCount} dari ${rows.length} pesanan`;
            }
        }

        // View Order Detail Modal (Populated with live data)
        function viewOrderDetail(id, customer, product, deadline, payment, status, total, wa, alamat) {
            document.getElementById('modalOrderId').textContent = '#' + id;
            document.getElementById('modalCustomer').textContent = customer || '-';
            document.getElementById('modalProduct').textContent = product || '-';
            document.getElementById('modalDeadline').textContent = deadline || '-';
            document.getElementById('modalTotal').textContent = total || '-';
            document.getElementById('modalAddress').textContent = alamat || '-';
            
            // Format phone & WhatsApp direct link
            const waElem = document.getElementById('modalWa');
            const waLink = document.getElementById('modalWaLink');
            const phone = wa || '-';
            waElem.textContent = phone;
            if (phone && phone !== '-') {
                let cleanPhone = phone.replace(/[^0-9]/g, '');
                if (cleanPhone.startsWith('0')) cleanPhone = '62' + cleanPhone.substring(1);
                waLink.href = 'https://wa.me/' + cleanPhone;
                waLink.classList.remove('hidden');
            } else {
                waLink.classList.add('hidden');
            }

            // Payment badge
            const payElem = document.getElementById('modalPayment');
            payElem.textContent = payment || 'Belum Bayar';
            if (payment && payment.toLowerCase().includes('lunas')) {
                payElem.className = 'inline-block px-3 py-1 rounded-full text-xs font-semibold bg-[#27AE60]/15 text-[#1B7A43]';
            } else if (payment && payment.toLowerCase().includes('hutang')) {
                payElem.className = 'inline-block px-3 py-1 rounded-full text-xs font-semibold bg-[#E2B93B]/20 text-[#886C12]';
            } else {
                payElem.className = 'inline-block px-3 py-1 rounded-full text-xs font-semibold bg-[#828282]/15 text-[#544337]';
            }

            // Status Badge
            const statusBadge = document.getElementById('modalStatusBadge');
            const st = (status || 'pending').toLowerCase();
            if (st === 'selesai') {
                statusBadge.textContent = 'Selesai';
                statusBadge.className = 'px-2.5 py-1 rounded-full text-xs font-semibold bg-[#27AE60]/15 text-[#1B7A43]';
            } else if (st === 'kirim') {
                statusBadge.textContent = 'Siap Kirim';
                statusBadge.className = 'px-2.5 py-1 rounded-full text-xs font-semibold bg-[#2F80ED]/15 text-[#2F80ED]';
            } else {
                statusBadge.textContent = 'Perlu Diproses';
                statusBadge.className = 'px-2.5 py-1 rounded-full text-xs font-semibold bg-[#E2B93B]/20 text-[#886C12]';
            }

            // Bind order_id for status update form
            document.getElementById('modalUpdateOrderId').value = id;

            const modal = document.getElementById('orderDetailModal');
            modal.classList.remove('hidden');
            document.body.style.overflow = 'hidden';
        }

        // Status advance submit from Order Detail Modal
        function submitStatusChange(newStatus) {
            document.getElementById('modalNewStatusInput').value = newStatus;
            document.getElementById('updateStatusForm').submit();
        }

        // Open Add Order Modal
        function openOrderModal() {
            const modal = document.getElementById('orderModal');
            if (modal) {
                modal.classList.remove('hidden');
                document.body.style.overflow = 'hidden';
                calculateOrderTotal();
            }
        }

        // Close Modals
        function closeOrderModal() {
            const modal = document.getElementById('orderModal');
            if (modal) {
                modal.classList.add('hidden');
                document.body.style.overflow = '';
            }
        }

        function closeModal(modalId) {
            const modal = document.getElementById(modalId);
            if (modal) {
                modal.classList.add('hidden');
                document.body.style.overflow = '';
            }
        }

        // Customer Selection Change Listener
        function onSelectCustomerChange(selectElem) {
            const val = selectElem.value;
            const newFields = document.getElementById('newCustomerFields');
            const previewName = document.getElementById('previewCustName');
            const previewWa = document.getElementById('previewCustWa');
            const previewAddress = document.getElementById('previewCustAddress');

            if (val === 'new') {
                newFields.classList.remove('hidden');
                previewName.textContent = 'Pelanggan Baru';
                previewWa.textContent = '-';
                previewAddress.textContent = '-';
            } else {
                newFields.classList.add('hidden');
                const cust = CUSTOMERS_DATA.find(c => c.id == val);
                if (cust) {
                    previewName.textContent = cust.nama;
                    previewWa.textContent = cust.no_wa;
                    previewAddress.textContent = cust.alamat;
                }
            }
        }

        // Add Product Row into Modal Table
        let productRowCounter = 2;
        function addOrderProductRow() {
            const tbody = document.getElementById('orderItemsTableBody');
            const rowIndex = productRowCounter++;
            
            // Default product: 3rd in catalog or 1st
            const defaultProd = PRODUCTS_DATA[2] || PRODUCTS_DATA[0] || {
                id_varian: 1, nama: 'Roti Manis', kemasan: 'Pcs', harga: 15000, min_order: 1
            };

            const tr = document.createElement('tr');
            tr.className = 'order-product-row';
            tr.setAttribute('data-min-order', defaultProd.min_order || 1);
            tr.setAttribute('data-price', defaultProd.harga || 15000);

            let optionsHtml = '';
            PRODUCTS_DATA.forEach(p => {
                const isSelected = p.id_varian == defaultProd.id_varian ? 'selected' : '';
                optionsHtml += `<option value="${p.id_varian}" data-price="${p.harga}" data-kemasan="${p.kemasan}" data-name="${p.nama}" data-min-order="${p.min_order || 1}" ${isSelected}>${p.nama} (${p.kemasan})</option>`;
            });

            tr.innerHTML = `
                <td class="px-3.5 py-3">
                    <input type="hidden" name="items[${rowIndex}][nama]" value="${defaultProd.nama}" class="item-name-input">
                    <input type="hidden" name="items[${rowIndex}][id_varian]" value="${defaultProd.id_varian}" class="item-varian-input">
                    <select onchange="onProductSelectRowChange(this, ${rowIndex})" 
                        class="w-full bg-white border border-[#E0E0E0] rounded px-2 py-1 text-xs font-semibold text-[#1C1C1C] focus:border-[#FF9B45] focus:outline-none mb-1">
                        ${optionsHtml}
                    </select>
                    <p class="text-[10px] text-[#828282] item-moq-label">${defaultProd.min_order > 1 ? 'Min. ' + defaultProd.min_order + ' pcs' : 'Ready Stock'}</p>
                </td>
                <td class="px-3.5 py-3 text-[#544337]">
                    <span class="item-kemasan-text">${defaultProd.kemasan}</span>
                    <input type="hidden" name="items[${rowIndex}][kemasan]" value="${defaultProd.kemasan}" class="item-kemasan-input">
                </td>
                <td class="px-3.5 py-3">
                    <input type="number" name="items[${rowIndex}][qty]" value="1" min="1" oninput="calculateOrderTotal()" 
                        class="item-qty-input w-16 px-2 py-1 border border-[#E0E0E0] text-[#1C1C1C] font-semibold rounded text-center focus:outline-none focus:border-[#FF9B45] transition-colors">
                </td>
                <td class="px-3.5 py-3 text-[#544337]">
                    <span class="item-price-text">Rp ${new Intl.NumberFormat('id-ID').format(defaultProd.harga)}</span>
                    <input type="hidden" name="items[${rowIndex}][harga]" value="${defaultProd.harga}" class="item-price-input">
                </td>
                <td class="px-3.5 py-3 font-bold text-[#1C1C1C] item-subtotal-text">Rp ${new Intl.NumberFormat('id-ID').format(defaultProd.harga)}</td>
                <td class="px-3 py-3 text-center">
                    <button type="button" onclick="removeOrderProductRow(this)" class="text-[#828282] hover:text-[#EB5757]" title="Hapus Item">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg>
                    </button>
                </td>
            `;

            tbody.appendChild(tr);
            calculateOrderTotal();
        }

        // Product selection changed in a row
        function onProductSelectRowChange(selectElem, rowIndex) {
            const tr = selectElem.closest('tr');
            const selectedOpt = selectElem.options[selectElem.selectedIndex];
            const price = parseFloat(selectedOpt.getAttribute('data-price')) || 0;
            const kemasan = selectedOpt.getAttribute('data-kemasan') || '';
            const name = selectedOpt.getAttribute('data-name') || '';
            const minOrder = parseInt(selectedOpt.getAttribute('data-min-order')) || 1;
            const idVarian = selectElem.value;

            tr.setAttribute('data-min-order', minOrder);
            tr.setAttribute('data-price', price);

            tr.querySelector('.item-name-input').value = name;
            tr.querySelector('.item-varian-input').value = idVarian;
            tr.querySelector('.item-kemasan-input').value = kemasan;
            tr.querySelector('.item-kemasan-text').textContent = kemasan;
            tr.querySelector('.item-price-input').value = price;
            tr.querySelector('.item-price-text').textContent = 'Rp ' + new Intl.NumberFormat('id-ID').format(price);
            
            const moqLabel = tr.querySelector('.item-moq-label');
            if (minOrder > 1) {
                moqLabel.textContent = `Min. ${minOrder} pcs`;
                moqLabel.className = 'text-[10px] text-[#EB5757] font-semibold item-moq-label';
            } else {
                moqLabel.textContent = 'Ready Stock';
                moqLabel.className = 'text-[10px] text-[#828282] item-moq-label';
            }

            calculateOrderTotal();
        }

        // Remove Product Row
        function removeOrderProductRow(btn) {
            const tbody = document.getElementById('orderItemsTableBody');
            const rows = tbody.querySelectorAll('.order-product-row');
            if (rows.length <= 1) {
                alert('Pesanan harus memiliki minimal 1 produk bakery.');
                return;
            }
            btn.closest('tr').remove();
            calculateOrderTotal();
        }

        // Calculation & MOQ Validation in Modal
        function calculateOrderTotal() {
            const rows = document.querySelectorAll('#orderItemsTableBody .order-product-row');
            let grandTotal = 0;
            let moqViolations = [];

            rows.forEach(tr => {
                const qtyInput = tr.querySelector('.item-qty-input');
                const qty = parseInt(qtyInput.value) || 0;
                const price = parseFloat(tr.getAttribute('data-price')) || 0;
                const minOrder = parseInt(tr.getAttribute('data-min-order')) || 1;
                const subtotal = qty * price;
                grandTotal += subtotal;

                // Update subtotal text
                const subText = tr.querySelector('.item-subtotal-text');
                if (subText) subText.textContent = 'Rp ' + new Intl.NumberFormat('id-ID').format(subtotal);

                // MOQ Check
                const nameInput = tr.querySelector('.item-name-input');
                const prodName = nameInput ? nameInput.value : 'Produk';

                if (minOrder > 1 && qty < minOrder) {
                    moqViolations.push({
                        name: prodName,
                        qty: qty,
                        minOrder: minOrder
                    });
                    qtyInput.className = 'item-qty-input w-16 px-2 py-1 border-2 border-[#EB5757] text-[#EB5757] font-bold rounded text-center focus:outline-none transition-colors';
                } else {
                    qtyInput.className = 'item-qty-input w-16 px-2 py-1 border border-[#E0E0E0] text-[#1C1C1C] font-semibold rounded text-center focus:outline-none focus:border-[#FF9B45] transition-colors';
                }
            });

            // Update item count & grand total text
            const countLabel = document.getElementById('itemCountLabel');
            if (countLabel) countLabel.textContent = `${rows.length} Item Dipilih`;

            const grandTotalElem = document.getElementById('orderGrandTotal');
            if (grandTotalElem) grandTotalElem.textContent = 'Rp ' + new Intl.NumberFormat('id-ID').format(grandTotal);

            // Handle MOQ Alert Box & Submit Lock
            const alertBox = document.getElementById('moqAlertBox');
            const alertMsg = document.getElementById('moqAlertMessage');
            const submitBtn = document.getElementById('submitOrderBtn');
            const lockIcon = document.getElementById('submitLockIcon');
            const footerWarning = document.getElementById('footerMoqWarning');

            if (moqViolations.length > 0) {
                const firstV = moqViolations[0];
                alertBox.classList.remove('hidden');
                alertMsg.innerHTML = `<strong>${firstV.name}</strong> — Jumlah pesanan <span id="currentMoqQty">${firstV.qty}</span> pcs (Minimum order ${firstV.minOrder} pcs). Pesanan dikunci dan tidak dapat disimpan sebelum memenuhi jumlah minimum.`;
                
                submitBtn.disabled = true;
                submitBtn.className = 'bg-[#D8D2CB] text-white cursor-not-allowed text-xs font-semibold px-5 py-2.5 rounded-lg flex items-center gap-1.5 transition-all shadow-xs';
                lockIcon.textContent = '🔒';
                footerWarning.classList.remove('hidden');
            } else {
                alertBox.classList.add('hidden');
                submitBtn.disabled = false;
                submitBtn.className = 'bg-[#FF9B45] hover:bg-[#E88C3D] text-white cursor-pointer text-xs font-semibold px-5 py-2.5 rounded-lg flex items-center gap-1.5 transition-all shadow-button-orange active:scale-95';
                lockIcon.textContent = '✓';
                footerWarning.classList.add('hidden');
            }
        }

        // Payment status segmented button
        function selectPaymentStatus(status, btn) {
            document.getElementById('selectedPaymentStatus').value = status;
            const buttons = document.querySelectorAll('.pay-status-btn');
            buttons.forEach(b => {
                b.className = 'py-1.5 px-2 rounded-lg text-xs font-semibold bg-white border border-[#E0E0E0] text-[#544337] hover:bg-gray-50 transition-colors pay-status-btn';
            });
            btn.className = 'py-1.5 px-2 rounded-lg text-xs font-semibold bg-[#544337] text-white shadow-2xs transition-colors pay-status-btn';
        }

        // Logout Modal Open / Close
        function openLogoutModal() {
            const modal = document.getElementById('logoutModal');
            if (modal) {
                modal.classList.remove('hidden');
                document.body.style.overflow = 'hidden';
            }
        }

        function closeLogoutModal() {
            const modal = document.getElementById('logoutModal');
            if (modal) {
                modal.classList.add('hidden');
                document.body.style.overflow = '';
            }
        }

        // Close on Escape key
        document.addEventListener('keydown', function(e) {
            if (e.key === 'Escape') {
                closeLogoutModal();
                closeModal('orderDetailModal');
                closeOrderModal();
            }
        });
    </script>
</body>
</html>
