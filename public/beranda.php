<?php
session_start();

// Proteksi Autentikasi: Hanya user yang sudah login yang diizinkan mengakses Beranda
if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true) {
    header("Location: login.php");
    exit();
}

// Waktu & Tanggal Dinamis (Bahasa Indonesia)
$hariIndonesia = ['Sunday' => 'Minggu', 'Monday' => 'Senin', 'Tuesday' => 'Selasa', 'Wednesday' => 'Rabu', 'Thursday' => 'Kamis', 'Friday' => 'Jumat', 'Saturday' => 'Sabtu'];
$bulanIndonesia = [1 => 'Jan', 2 => 'Feb', 3 => 'Mar', 4 => 'Apr', 5 => 'Mei', 6 => 'Jun', 7 => 'Jul', 8 => 'Agu', 9 => 'Sep', 10 => 'Okt', 11 => 'Nov', 12 => 'Des'];
$todayFormatted = $hariIndonesia[date('l')] . ', ' . date('d') . ' ' . $bulanIndonesia[intval(date('m'))] . ' ' . date('Y');
$currentMonthIndo = [
    'January' => 'Januari', 'February' => 'Februari', 'March' => 'Maret', 'April' => 'April',
    'May' => 'Mei', 'June' => 'Juni', 'July' => 'Juli', 'August' => 'Agustus',
    'September' => 'September', 'October' => 'Oktober', 'November' => 'November', 'December' => 'Desember'
][date('F')] . ' ' . date('Y');

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
                SELECT p.no_invoice, p.tgl_pesan, pel.nama as nama_pelanggan, pel.no_wa, 
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
        fputcsv($output, ['#ORD-20250926-01', $today, 'Ibu Ratna Sari', '0812-3456-7890', 'Hari Ini, 14:00', 'Rp 100.000', 'BELUM BAYAR', 'PROSES', 'Roti Hajatan Putih (10 box)']);
        fputcsv($output, ['#ORD-20250926-02', $today, 'Pak Hendra Wijaya', '0813-8899-7711', 'Hari Ini, 16:30', 'Rp 220.000', 'HUTANG', 'PROSES', 'Roti Hajatan Batik (20 box)']);
        fputcsv($output, ['#ORD-20250926-03', $today, 'Toko Berkah Jaya', '0821-4455-6677', 'Besok, 09:00', 'Rp 440.000', 'LUNAS', 'PROSES', 'Bolu Pisang Mika (20 mika)']);
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
            $p_kemasan = trim($it['kemasan'] ?? 'Kardus Putih');
            $p_qty = max(1, intval($it['qty'] ?? 1));
            $p_harga = max(0, floatval($it['harga'] ?? 10000));
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
            'nama' => 'Roti Hajatan (9 Pilihan Topping)',
            'id_varian' => 1,
            'kemasan' => 'Kardus Putih',
            'qty' => 10,
            'harga' => 10000,
            'subtotal' => 100000
        ];
        $grand_total = 100000;
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
                $stmtC = $pdo->prepare("INSERT INTO pelanggan (nama, no_wa, alamat, tipe) VALUES (?, ?, ?, 'retail')");
                $stmtC->execute([$nama_pelanggan_baru, $no_wa_baru, $alamat_baru]);
                $id_pelanggan = $pdo->lastInsertId();
                $customer_display = $nama_pelanggan_baru;
                $customer_wa = $no_wa_baru;
                $customer_alamat = $alamat_baru;
            } elseif ($id_pelanggan > 0) {
                $stmtC = $pdo->prepare("SELECT nama, no_wa, alamat FROM pelanggan WHERE id_pelanggan = ?");
                $stmtC->execute([$id_pelanggan]);
                $cRow = $stmtC->fetch();
                if ($cRow) {
                    $customer_display = $cRow['nama'];
                    $customer_wa = $cRow['no_wa'];
                    $customer_alamat = $cRow['alamat'];
                }
            } else {
                $stmtC = $pdo->query("SELECT id_pelanggan, nama, no_wa, alamat FROM pelanggan LIMIT 1");
                $cRow = $stmtC->fetch();
                if ($cRow) {
                    $id_pelanggan = $cRow['id_pelanggan'];
                    $customer_display = $cRow['nama'];
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
    
    // Simpan ke sesi agar tampil instan di Beranda
    if (!isset($_SESSION['session_orders'])) {
        $_SESSION['session_orders'] = [];
    }
    $itemSummaryStr = implode(', ', array_map(fn($p) => "{$p['nama']} ({$p['qty']} pcs)", $processed_items));
    $p_class = match($status_bayar) {
        'lunas' => 'bg-[#27AE60]/15 text-[#1B7A43]',
        'hutang', 'dp' => 'bg-[#E2B93B]/20 text-[#886C12]',
        default => 'bg-[#828282]/15 text-[#544337]'
    };
    
    $isToday = ($tgl_kirim == date('Y-m-d'));
    $deadlineFormatted = $isToday ? ('Hari Ini, ' . substr($waktu_kirim, 0, 5)) : (date('d M Y, ', strtotime($tgl_kirim)) . substr($waktu_kirim, 0, 5));
    
    array_unshift($_SESSION['session_orders'], [
        'id' => $no_invoice,
        'customer' => $customer_display,
        'product' => $itemSummaryStr,
        'deadline' => $deadlineFormatted,
        'is_urgent' => $isToday,
        'status' => 'pending',
        'payment_status' => ucfirst(str_replace('_', ' ', $status_bayar)),
        'payment_class' => $p_class,
        'total' => 'Rp ' . number_format($grand_total, 0, ',', '.'),
        'no_wa' => $customer_wa,
        'alamat' => $customer_alamat
    ]);
    
    // Sinkronkan juga ke custom_orders untuk page Pesanan (Kanban & Kalender)
    if (!isset($_SESSION['custom_orders'])) {
        $_SESSION['custom_orders'] = [];
    }
    array_unshift($_SESSION['custom_orders'], [
        'id' => $no_invoice,
        'customer' => $customer_display,
        'phone' => $customer_wa,
        'items' => $itemSummaryStr,
        'destination' => $customer_alamat,
        'time' => 'Pukul ' . substr($waktu_kirim, 0, 5),
        'date' => $tgl_kirim,
        'default_status' => 'menunggu',
        'is_urgent' => $isToday,
        'status_bayar' => ucfirst(str_replace('_', ' ', $status_bayar)),
        'bayar_class' => $p_class,
        'courier_info' => 'Kurir Toko (Siap Antar ' . substr($waktu_kirim, 0, 5) . ')',
        'total' => $grand_total
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
    
    if (!isset($_SESSION['order_status_overrides'])) {
        $_SESSION['order_status_overrides'] = [];
    }
    if (!empty($order_id)) {
        $_SESSION['order_status_overrides'][$order_id] = $new_status;
    }
    
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
        'pending' => 'Menunggu Konfirmasi',
        'proses' => 'Sedang Diproses / Oven',
        'kirim' => 'Siap Kirim / Driver',
        'selesai' => 'Selesai',
        default => ucfirst($new_status)
    };
    
    // Deteksi Request AJAX
    $is_ajax = (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest') || (isset($_POST['is_ajax']) && $_POST['is_ajax'] == '1');
    if ($is_ajax) {
        header('Content-Type: application/json');
        echo json_encode([
            'success' => true,
            'order_id' => $order_id,
            'new_status' => $new_status,
            'status_label' => $statusLabel,
            'message' => "Status pesanan {$order_id} berhasil diubah menjadi: {$statusLabel}!"
        ]);
        exit();
    }
    
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
        $stmtC = $pdo->query("SELECT id_pelanggan as id, nama, no_wa, alamat FROM pelanggan ORDER BY nama ASC");
        $dbCusts = $stmtC->fetchAll();
        if (!empty($dbCusts)) {
            $customers_for_select = $dbCusts;
        }
    } catch (Exception $e) {}
}

$products_for_select = [
    ['id' => 1, 'id_varian' => 1, 'nama' => 'Roti Hajatan (9 Pilihan Topping)', 'kemasan' => 'Kardus Putih', 'harga' => 10000, 'min_order' => 1],
    ['id' => 1, 'id_varian' => 2, 'nama' => 'Roti Hajatan (9 Pilihan Topping)', 'kemasan' => 'Kardus Coklat', 'harga' => 10000, 'min_order' => 1],
    ['id' => 1, 'id_varian' => 3, 'nama' => 'Roti Hajatan (9 Pilihan Topping)', 'kemasan' => 'Kardus Batik', 'harga' => 11000, 'min_order' => 1],
    ['id' => 1, 'id_varian' => 4, 'nama' => 'Roti Hajatan (9 Pilihan Topping)', 'kemasan' => 'Mika', 'harga' => 11000, 'min_order' => 1],
    ['id' => 2, 'id_varian' => 6, 'nama' => 'Bolu Pisang Premium', 'kemasan' => 'Kardus Coklat', 'harga' => 21000, 'min_order' => 1],
    ['id' => 2, 'id_varian' => 7, 'nama' => 'Bolu Pisang Premium', 'kemasan' => 'Mika', 'harga' => 22000, 'min_order' => 1],
    ['id' => 3, 'id_varian' => 8, 'nama' => 'Bolu Tanpa Pisang Premium', 'kemasan' => 'Kardus Putih', 'harga' => 19000, 'min_order' => 1],
    ['id' => 3, 'id_varian' => 9, 'nama' => 'Bolu Tanpa Pisang Premium', 'kemasan' => 'Mika', 'harga' => 20000, 'min_order' => 1],
    ['id' => 4, 'id_varian' => 10, 'nama' => 'Roti Kopi', 'kemasan' => 'Satuan', 'harga' => 4000, 'min_order' => 50],
    ['id' => 5, 'id_varian' => 11, 'nama' => 'Roti Bijian Besar', 'kemasan' => 'Satuan', 'harga' => 4000, 'min_order' => 20],
    ['id' => 6, 'id_varian' => 5, 'nama' => 'Roti Bijian Kecil', 'kemasan' => 'Satuan', 'harga' => 3000, 'min_order' => 50]
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
        'product' => 'Roti Hajatan Kardus Batik (50 box)',
        'deadline' => 'Hari Ini, 14:00',
        'is_urgent' => true,
        'status' => 'proses',
        'payment_status' => 'Belum Bayar',
        'payment_class' => 'bg-[#828282]/15 text-[#544337]',
        'total' => 'Rp 550.000',
        'no_wa' => '0812-3456-7890',
        'alamat' => 'Komplek Melati No. 4, Blok B'
    ],
    [
        'id' => 'ORD-20250926-02',
        'customer' => 'Pak Hendra Wijaya',
        'product' => 'Bolu Pisang Premium Mika (10 mika)',
        'deadline' => 'Hari Ini, 16:30',
        'is_urgent' => true,
        'status' => 'proses',
        'payment_status' => 'Hutang',
        'payment_class' => 'bg-[#E2B93B]/20 text-[#886C12]',
        'total' => 'Rp 220.000',
        'no_wa' => '0813-8899-7711',
        'alamat' => 'Jl. Anggrek No. 12, Malang'
    ],
    [
        'id' => 'ORD-20250926-03',
        'customer' => 'Toko Berkah Jaya',
        'product' => 'Roti Hajatan Kardus Putih (100 box)',
        'deadline' => 'Besok, 09:00',
        'is_urgent' => false,
        'status' => 'pending',
        'payment_status' => 'Lunas',
        'payment_class' => 'bg-[#27AE60]/15 text-[#1B7A43]',
        'total' => 'Rp 1.000.000',
        'no_wa' => '0821-4455-6677',
        'alamat' => 'Ruko Harmoni Blok C-02'
    ],
    [
        'id' => 'ORD-20250926-04',
        'customer' => 'Kafe Kopi Seduh',
        'product' => 'Roti Kopi Satuan (50 pcs)',
        'deadline' => '28 Sep 2025',
        'is_urgent' => false,
        'status' => 'kirim',
        'payment_status' => 'Belum Bayar',
        'payment_class' => 'bg-[#828282]/15 text-[#544337]',
        'total' => 'Rp 200.000',
        'no_wa' => '0812-9876-5432',
        'alamat' => 'Jl. Ijen No. 8, Malang'
    ],
    [
        'id' => 'ORD-20250926-05',
        'customer' => 'Bu Siti Rahayu',
        'product' => 'Bolu Tanpa Pisang Kardus Putih (10 box)',
        'deadline' => '29 Sep 2025',
        'is_urgent' => false,
        'status' => 'selesai',
        'payment_status' => 'Lunas',
        'payment_class' => 'bg-[#27AE60]/15 text-[#1B7A43]',
        'total' => 'Rp 190.000',
        'no_wa' => '0856-1122-3344',
        'alamat' => 'Komplek Griya Asri No. 9'
    ]
];

if ($db_connected && $pdo) {
    try {
        $stmtOrders = $pdo->query("
            SELECT p.id_pesanan, p.no_invoice, p.tgl_pesan, p.tgl_kirim, p.waktu_kirim, p.grand_total, p.status, p.status_bayar, p.catatan,
                   pel.nama as nama_pelanggan, pel.no_wa, pel.alamat,
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
                $dl = $is_urgent ? 'Hari Ini, ' . substr($o['waktu_kirim'] ?? '14:00', 0, 5) : date('d M Y, ', strtotime($o['tgl_kirim'] ?? $o['tgl_pesan'])) . substr($o['waktu_kirim'] ?? '14:00', 0, 5);
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

// Sinkronkan juga jika ada pesanan baru dari page Pesanan
if (!empty($_SESSION['custom_orders'])) {
    $existingIds = array_column($pending_orders, 'id');
    foreach ($_SESSION['custom_orders'] as $co) {
        if (!in_array($co['id'], $existingIds)) {
            $existingIds[] = $co['id'];
            $isTodayCo = ($co['date'] == date('Y-m-d'));
            $coDeadline = $isTodayCo ? ('Hari Ini, ' . str_replace('Pukul ', '', $co['time'])) : (date('d M Y, ', strtotime($co['date'])) . str_replace('Pukul ', '', $co['time']));
            array_unshift($pending_orders, [
                'id' => $co['id'],
                'customer' => $co['customer'],
                'product' => $co['items'],
                'deadline' => $coDeadline,
                'is_urgent' => $isTodayCo,
                'status' => $co['default_status'] === 'menunggu' ? 'pending' : $co['default_status'],
                'payment_status' => $co['status_bayar'],
                'payment_class' => $co['bayar_class'],
                'total' => 'Rp ' . number_format($co['total'], 0, ',', '.'),
                'no_wa' => $co['phone'],
                'alamat' => $co['destination']
            ]);
        }
    }
}

// Sinkronkan status pesanan yang baru diubah secara live
if (!empty($_SESSION['order_status_overrides'])) {
    foreach ($pending_orders as &$po) {
        if (isset($_SESSION['order_status_overrides'][$po['id']])) {
            $po['status'] = $_SESSION['order_status_overrides'][$po['id']];
        }
    }
    unset($po);
}

// -------------------------------------------------------------
// 7. JADWAL PENGIRIMAN HARI INI
// -------------------------------------------------------------
$deliveries = [
    [
        'time' => '14:00',
        'customer' => 'Ibu Ratna Sari',
        'items' => '10 box Roti Hajatan Putih',
        'status' => 'Siap Kirim',
        'status_class' => 'bg-[#27AE60]/15 text-[#1B7A43]'
    ],
    [
        'time' => '16:30',
        'customer' => 'Pak Hendra Wijaya',
        'items' => '20 box Roti Hajatan Batik',
        'status' => 'Sedang Dikemas',
        'status_class' => 'bg-[#E2B93B]/20 text-[#886C12]'
    ]
];

if ($db_connected && $pdo) {
    try {
        $stmtD = $pdo->query("
            SELECT p.waktu_kirim, pel.nama as nama_pelanggan, 
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

// Sinkronkan pengiriman hari ini dari session
$deliveryCustList = array_column($deliveries, 'customer');
if (!empty($_SESSION['session_orders'])) {
    foreach ($_SESSION['session_orders'] as $so) {
        if (!empty($so['is_urgent']) || str_contains($so['deadline'] ?? '', 'Hari Ini')) {
            if (!in_array($so['customer'], $deliveryCustList)) {
                $deliveryCustList[] = $so['customer'];
                array_unshift($deliveries, [
                    'time' => preg_match('/\d{2}:\d{2}/', $so['deadline'] ?? '', $m) ? $m[0] : '14:00',
                    'customer' => $so['customer'] ?? 'Pelanggan',
                    'items' => $so['product'] ?? 'Pesanan Bakery',
                    'status' => 'Menunggu Dapur',
                    'status_class' => 'bg-[#828282]/15 text-[#544337]'
                ]);
            }
        }
    }
}
if (!empty($_SESSION['custom_orders'])) {
    foreach ($_SESSION['custom_orders'] as $co) {
        if (!empty($co['is_urgent']) || ($co['date'] ?? '') == date('Y-m-d')) {
            if (!in_array($co['customer'], $deliveryCustList)) {
                $deliveryCustList[] = $co['customer'];
                array_unshift($deliveries, [
                    'time' => preg_match('/\d{2}:\d{2}/', $co['time'] ?? '', $m) ? $m[0] : '14:00',
                    'customer' => $co['customer'] ?? 'Pelanggan',
                    'items' => $co['items'] ?? 'Pesanan Bakery',
                    'status' => 'Menunggu Dapur',
                    'status_class' => 'bg-[#828282]/15 text-[#544337]'
                ]);
            }
        }
    }
}

// -------------------------------------------------------------
// 8. AKTIVITAS TERBARU (LIVE AUDIT LOG)
// -------------------------------------------------------------
$activities = [
    [
        'dot_color' => 'bg-[#FF9B45]',
        'text' => '<span class="text-[#FF9B45] font-medium">Pesanan baru <a href="javascript:void(0)" onclick="viewOrderDetail(\'ORD-20250926-01\', \'Ibu Ratna Sari\', \'Roti Hajatan Putih (10 box)\', \'Hari Ini, 14:00\', \'Belum Bayar\', \'proses\', \'Rp 100.000\', \'0812-3456-7890\', \'Komplek Melati No. 4, Blok B\')" class="font-semibold hover:underline">#ORD-20250926-01</a> dibuat oleh Ibu Ratna Sari (10 box Roti Hajatan Putih)</span>',
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
    <link rel="icon" type="image/png" href="assets/images/logo.png">
    
    <!-- Tailwind CSS CDN -->
    <script src="https://cdn.tailwindcss.com"></script>
    <!-- Google Fonts: Inter -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <!-- Font Awesome 5 Free CDN -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css" integrity="sha512-1ycn6IcaQQ40/MKBW2W4Rhis/DbILU74C1vSrLJxCq57o941Ym01SwNsOMqvEBFlcgUa6xLiPY/NS5R+E6ztJQ==" crossorigin="anonymous" referrerpolicy="no-referrer" />
    <!-- Modern Craft Design System -->
    <link rel="stylesheet" href="css/modern-craft.css">

    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        brand: {
                            brown: '#3E2B21',
                            brownDark: '#2C1E17',
                            orange: '#D46E15',
                            orangeHover: '#B85207',
                            lightText: '#E8D7CD',
                            bg: '#F5F1EB',
                            surface: '#FDFBF7',
                            border: '#E2D7C8',
                            muted: '#8E7E73',
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
                        'button-orange': '0px 2px 4px rgba(212, 110, 21, 0.25)',
                    }
                }
            }
        }
    </script>

    <style>
        body {
            font-family: 'Inter', sans-serif;
            background-color: #F5F1EB;
            color: #251A14;
        }

        /* Continuous Vertical Timeline line */
        .timeline-container::before {
            content: '';
            position: absolute;
            left: 7px;
            top: 14px;
            bottom: 14px;
            width: 2px;
            background-color: #DDD3C5;
        }
    </style>
</head>
<body class="min-h-screen bg-[#F5F1EB] flex flex-col antialiased selection:bg-[#D46E15] selection:text-white">

    <!-- OVERALL WRAPPER -->
    <div class="flex flex-1 w-full relative">

        <!-- MOBILE SIDEBAR OVERLAY -->
        <div id="sidebar-overlay" onclick="toggleSidebar()" class="fixed inset-0 bg-black/40 z-40 lg:hidden hidden transition-opacity duration-300"></div>

        <!-- ASIDE / SIDEBAR (Width 240px) -->
        <aside id="main-sidebar" class="fixed top-0 bottom-0 left-0 w-[240px] bg-[#3E2B21] border-r border-[#2C1E17] z-50 flex flex-col justify-between -translate-x-full lg:translate-x-0 transition-transform duration-300 ease-in-out shadow-xl lg:shadow-none">
            
            <!-- Top Logo & Navigation -->
            <div class="flex flex-col flex-1 overflow-y-auto">
                
                <!-- Logo & Brand Header (Height 64px - Aligned with Topbar) -->
                <div class="h-16 px-4 flex items-center gap-3 border-b border-white/10 shrink-0">
                    <div class="w-8 h-8 rounded-full bg-white p-0.5 flex items-center justify-center shrink-0 shadow-sm overflow-hidden">
                        <img src="assets/images/logo.png" alt="Berkat Dinasti Logo" class="w-full h-full object-contain">
                    </div>
                    <span class="text-white font-bold text-lg tracking-tight">Berkat Dinasti</span>
                    
                    <!-- Mobile Close Button -->
                    <button onclick="toggleSidebar()" class="ml-auto text-white/70 hover:text-white lg:hidden p-1">
                        <i class="fas fa-times text-base"></i>
                    </button>
                </div>

                <!-- Navigation List -->
                <nav class="p-4 space-y-1.5 flex-1">
                    <!-- Beranda (Active) -->
                    <a href="beranda.php" class="bg-[#D46E15] text-white rounded-lg px-4 py-3 flex items-center gap-3 font-semibold text-sm transition-all shadow-sm">
                        <i class="fas fa-th-large w-5 text-center text-sm"></i>
                        <span>Beranda</span>
                    </a>

                    <!-- Produk -->
                    <a href="produk.php" class="text-white/80 hover:text-white hover:bg-white/10 rounded-lg px-4 py-3 flex items-center gap-3 font-medium text-sm transition-colors">
                        <i class="fas fa-box-open w-5 text-center text-sm opacity-90"></i>
                        <span>Produk</span>
                    </a>

                    <!-- Pesanan -->
                    <a href="pesanan.php" class="text-white/80 hover:text-white hover:bg-white/10 rounded-lg px-4 py-3 flex items-center gap-3 font-medium text-sm transition-colors">
                        <i class="fas fa-shopping-bag w-5 text-center text-sm opacity-90"></i>
                        <span>Pesanan</span>
                    </a>

                    <!-- Pelanggan -->
                    <a href="pelanggan.php" class="text-white/80 hover:text-white hover:bg-white/10 rounded-lg px-4 py-3 flex items-center gap-3 font-medium text-sm transition-colors">
                        <i class="fas fa-users w-5 text-center text-sm opacity-90"></i>
                        <span>Pelanggan</span>
                    </a>

                    <!-- Laporan -->
                    <a href="laporan.php" class="text-white/80 hover:text-white hover:bg-white/10 rounded-lg px-4 py-3 flex items-center gap-3 font-medium text-sm transition-colors">
                        <i class="fas fa-chart-line w-5 text-center text-sm opacity-90"></i>
                        <span>Laporan</span>
                    </a>

                    <!-- Pengaturan -->
                    <a href="pengaturan.php" class="text-white/80 hover:text-white hover:bg-white/10 rounded-lg px-4 py-3 flex items-center gap-3 font-medium text-sm transition-colors">
                        <i class="fas fa-cog w-5 text-center text-sm opacity-90"></i>
                        <span>Pengaturan</span>
                    </a>
                </nav>
            </div>

            <!-- Bottom User Profile Card -->
            <div class="p-4 border-t border-white/10 shrink-0">
                <div class="bg-black/25 border border-white/5 rounded-xl p-2.5 flex items-center justify-between">
                    <div class="flex items-center gap-3 overflow-hidden">
                        <div class="w-9 h-9 rounded-full bg-[#D46E15] text-white font-bold text-xs flex items-center justify-center shrink-0 shadow-sm">
                            BD
                        </div>
                        <div class="truncate">
                            <p class="text-white font-semibold text-sm leading-tight truncate"><?= htmlspecialchars($_SESSION['nama_lengkap'] ?? $_SESSION['user'] ?? 'Admin') ?></p>
                            <p class="text-[#D8C7B8] text-xs leading-tight mt-0.5"><?= htmlspecialchars(ucfirst($_SESSION['role'] ?? 'Admin')) ?></p>
                        </div>
                    </div>
                    <button type="button" onclick="openLogoutModal()" title="Keluar / Logout" class="p-1.5 text-white/70 hover:text-white transition-colors shrink-0">
                        <i class="fas fa-sign-out-alt text-base"></i>
                    </button>
                </div>
            </div>
        </aside>

        <!-- MAIN LAYOUT WRAPPER (Offset 240px on lg screens) -->
        <div class="flex-1 flex flex-col min-w-0 lg:ml-[240px]">

            <!-- TOP HEADER (Height 64px) -->
            <header class="h-16 bg-[#FDFBF7]/95 backdrop-blur-md border-b border-[#E2D7C8] sticky top-0 z-30 px-4 md:px-8 flex items-center justify-between">
                
                <!-- Left: Hamburger (Mobile) + Breadcrumb & Title -->
                <div class="flex items-center gap-3 md:gap-4">
                    <button onclick="toggleSidebar()" class="lg:hidden p-1.5 text-[#3E2B21] hover:bg-[#EFE8DE] rounded-lg">
                        <i class="fas fa-bars text-lg text-[#3E2B21]"></i>
                    </button>

                    <div>
                        <!-- Breadcrumbs -->
                        <div class="flex items-center gap-1.5 text-xs">
                            <span class="text-[#8E7E73]">Beranda</span>
                            <span class="text-[#D8C7B8]">/</span>
                            <span class="text-[#3E2B21] font-semibold">Ringkasan</span>
                        </div>
                        <!-- Section Heading -->
                        <h1 class="text-[#251A14] font-bold text-base md:text-lg leading-tight mt-0.5">Operasional Toko</h1>
                    </div>
                </div>

                <!-- Right: Date Indicator, Notification Bell, User Avatar -->
                <div class="flex items-center gap-3 md:gap-5">
                    
                    <!-- Date Button -->
                    <div class="hidden sm:flex items-center gap-2 bg-[#EFE8DE] text-[#3E2B21] px-3.5 py-1.5 rounded-lg text-xs md:text-sm font-semibold border border-[#DDD3C5]">
                        <i class="far fa-calendar-alt text-[#D46E15] text-xs"></i>
                        <span><?= $todayFormatted ?></span>
                    </div>

                    <!-- Notification Bell with Popover -->
                    <div class="relative">
                        <button onclick="toggleNotificationDropdown()" class="relative p-2 text-[#544337] hover:bg-[#F5F3F1] rounded-lg transition-colors" title="Notifikasi">
                            <i class="far fa-bell text-[#3E2B21] text-base"></i>
                            <span class="absolute top-1.5 right-1.5 w-2 h-2 bg-[#D46E15] rounded-full ring-2 ring-white"></span>
                        </button>
                        <div id="notificationDropdown" class="hidden absolute right-0 mt-2.5 w-80 craft-dropdown py-2 z-50">
                            <div class="px-4 py-2 border-b border-[#ECE5DD] flex items-center justify-between">
                                <h4 class="font-semibold text-xs text-[#251A14]">Notifikasi Operasional</h4>
                                <span class="text-[10px] craft-badge-warning font-medium px-2 py-0.5 rounded-full">3 Baru</span>
                            </div>
                            <div class="divide-y divide-[#F0ECE9]/70 text-xs py-1">
                                <div class="px-3.5 py-2.5 hover:bg-[#F8F5F1] transition-colors cursor-pointer rounded-lg mx-1">
                                    <p class="font-medium text-[#251A14]">Pesanan #ORD-089 Butuh Konfirmasi</p>
                                    <p class="text-[11px] text-[#8E7E73] mt-0.5">Ibu Sari • Deadline 15:00 hari ini</p>
                                </div>
                                <div class="px-3.5 py-2.5 hover:bg-[#F8F5F1] transition-colors cursor-pointer rounded-lg mx-1">
                                    <p class="font-medium text-[#251A14]">2 Batch Siap Diantar Driver</p>
                                    <p class="text-[11px] text-[#8E7E73] mt-0.5">Roti Hajatan & Bolu siap kirim</p>
                                </div>
                                <div class="px-3.5 py-2.5 hover:bg-[#F8F5F1] transition-colors cursor-pointer rounded-lg mx-1">
                                    <p class="font-medium text-[#251A14]">Stok Kardus Roti Hampir Habis</p>
                                    <p class="text-[11px] text-[#8E7E73] mt-0.5">Sisa 12 kardus di gudang kemasan</p>
                                </div>
                            </div>
                            <div class="px-3 pt-2 text-center border-t border-[#ECE5DD]">
                                <a href="pesanan.php" class="text-[11px] font-semibold text-[#D46E15] hover:underline">Buka Semua di Pesanan →</a>
                            </div>
                        </div>
                    </div>

                    <!-- User Circle Icon with Dropdown -->
                    <div class="relative">
                        <div onclick="toggleUserDropdown()" class="w-8 h-8 rounded-full bg-[#3E2B21] border border-white/20 flex items-center justify-center text-white shadow-xs cursor-pointer hover:opacity-95 transition-all active:scale-95" title="Profil Pengguna">
                            <i class="fas fa-user text-xs text-[#FAF8F5]"></i>
                        </div>
                        <div id="userDropdown" class="hidden absolute right-0 mt-2.5 w-56 craft-dropdown py-1.5 z-50 text-xs">
                            <div class="px-4 py-2.5 border-b border-[#ECE5DD]">
                                <p class="font-semibold text-[#251A14]"><?= htmlspecialchars($_SESSION['nama_lengkap'] ?? 'Administrator') ?></p>
                                <p class="text-[#8E7E73] text-[11px] mt-0.5"><?= htmlspecialchars(ucfirst($_SESSION['role'] ?? 'admin')) ?> @ Berkat Dinasti</p>
                            </div>
                            <div class="py-1">
                                <a href="pengaturan.php" class="craft-dropdown-item">
                                    <i class="fas fa-cog text-xs text-[#8E7E73]"></i>
                                    <span>Pengaturan Akun & Toko</span>
                                </a>
                                <a href="reset-password.php" class="craft-dropdown-item">
                                    <i class="fas fa-key text-xs text-[#8E7E73]"></i>
                                    <span>Ganti Password</span>
                                </a>
                            </div>
                            <div class="border-t border-[#ECE5DD] pt-1">
                                <button onclick="openLogoutModal()" class="w-full text-left craft-dropdown-item text-[#991B1B] hover:text-[#7F1D1D] hover:bg-rose-50/70">
                                    <i class="fas fa-sign-out-alt text-xs text-[#991B1B]"></i>
                                    <span>Keluar (Logout)</span>
                                </button>
                            </div>
                        </div>
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
                                <i class="fas fa-check-circle text-[#27AE60] text-base"></i>
                            </div>
                            <span class="font-medium"><?= htmlspecialchars($_SESSION['flash_msg']) ?></span>
                        </div>
                        <button onclick="document.getElementById('flashAlert').remove()" class="text-[#1B7A43]/70 hover:text-[#1B7A43] p-1.5 rounded-lg hover:bg-[#27AE60]/10 transition-colors">
                            <i class="fas fa-times text-xs"></i>
                        </button>
                    </div>
                    <?php unset($_SESSION['flash_msg']); ?>
                <?php endif; ?>
                
                <!-- TOP BANNER: Greeting & Operational Actions -->
                <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 mb-6">
                    <div>
                        <p class="text-[#924C00] text-xs font-bold tracking-wider uppercase mb-1">RINGKASAN OPERASIONAL TOKO</p>
                        <h2 class="text-[#251A14] text-2xl font-bold tracking-tight">Beranda Manajemen Pesanan</h2>
                    </div>

                    <!-- Action Buttons -->
                    <div class="flex items-center gap-2.5 shrink-0 flex-wrap">
                        <!-- Unduh Rekap Harian Button (Live CSV Export) -->
                        <a href="beranda.php?action=export_rekap" 
                            title="Unduh rekap penjualan harian format CSV/Excel" 
                            class="bg-[#EFE8DE] hover:bg-[#E4DCCE] text-[#251A14] text-xs md:text-sm font-semibold px-3.5 py-2.5 rounded-lg border border-[#DDD3C5] flex items-center gap-2 transition-colors shadow-2xs">
                            <i class="fas fa-file-download text-xs text-[#544337]"></i>
                            <span>Unduh Rekap Harian</span>
                        </a>

                        <!-- Lihat Pesanan Link to Kanban -->
                        <a href="pesanan.php" 
                            title="Buka daftar dan alur manajemen pesanan" 
                            class="hidden md:flex bg-[#FFF6ED] hover:bg-[#FFEEDD] text-[#924C00] text-xs md:text-sm font-bold px-3.5 py-2.5 rounded-lg border border-[#F7D4B2] items-center gap-2 transition-colors shadow-2xs">
                            <i class="fas fa-clipboard-list text-xs text-[#D46E15]"></i>
                            <span>Lihat Pesanan</span>
                        </a>

                        <!-- Tambah Pesanan Baru Button -->
                        <button onclick="openOrderModal()" class="bg-[#D46E15] hover:bg-[#B85207] text-white text-xs md:text-sm font-semibold px-4 py-2.5 rounded-lg flex items-center gap-2 transition-all shadow-button-orange active:scale-95">
                            <i class="fas fa-plus text-xs"></i>
                            <span>Tambah Pesanan Baru</span>
                        </button>
                    </div>
                </div>

                <!-- ROW 1: 4 KPI STAT CARDS (ALL UNIFIED DARK CHOCOLATE / ESPRESSO CARDS) -->
                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-5 mb-6">
                    
                    <!-- Card 1: Total Pesanan Hari Ini -->
                    <div class="craft-card-espresso rounded-xl p-5 relative overflow-hidden flex flex-col justify-between transition-transform hover:-translate-y-0.5 duration-200">
                        <div class="absolute -right-4 -bottom-4 w-24 h-24 rounded-full bg-white/5 pointer-events-none"></div>
                        <div class="flex items-start justify-between relative z-10">
                            <div>
                                <p class="text-[#D8C7B8] text-xs font-bold uppercase tracking-wider">Total Pesanan</p>
                                <p class="text-xs text-[#B5A89E] mt-0.5">Hari Ini</p>
                                <p class="text-[#FAF8F5] text-3xl font-bold mt-2 tracking-tight"><?= htmlspecialchars($kpi_data['total_orders']) ?></p>
                            </div>
                            <div class="w-11 h-11 rounded-xl bg-amber-500/20 border border-amber-500/30 flex items-center justify-center shrink-0 shadow-inner">
                                <i class="fas fa-clipboard-check text-amber-300 text-lg"></i>
                            </div>
                        </div>
                        <div class="flex items-center gap-1.5 mt-3 pt-2 border-t border-white/10 relative z-10">
                            <i class="fas fa-arrow-up text-[#4ADE80] text-xs"></i>
                            <span class="text-[#86EFAC] text-xs font-medium"><?= htmlspecialchars($kpi_data['orders_diff']) ?></span>
                        </div>
                    </div>

                    <!-- Card 2: Pesanan Antrean / Pending -->
                    <div class="craft-card-espresso rounded-xl p-5 relative overflow-hidden flex flex-col justify-between transition-transform hover:-translate-y-0.5 duration-200">
                        <div class="absolute -right-4 -bottom-4 w-24 h-24 rounded-full bg-white/5 pointer-events-none"></div>
                        <div class="flex items-start justify-between relative z-10">
                            <div>
                                <p class="text-[#D8C7B8] text-xs font-bold uppercase tracking-wider">Pesanan Antrean</p>
                                <p class="text-xs text-[#B5A89E] mt-0.5">Perlu Konfirmasi</p>
                                <p class="text-[#FAF8F5] text-3xl font-bold mt-2 tracking-tight"><?= htmlspecialchars($kpi_data['pending_orders']) ?></p>
                            </div>
                            <div class="w-11 h-11 rounded-xl bg-amber-500/20 border border-amber-500/30 flex items-center justify-center shrink-0 shadow-inner">
                                <i class="fas fa-hourglass-half text-amber-300 text-lg"></i>
                            </div>
                        </div>
                        <div class="flex items-center gap-1.5 mt-3 pt-2 border-t border-white/10 relative z-10">
                            <i class="fas fa-exclamation-circle text-amber-400 text-xs"></i>
                            <span class="text-amber-200 text-xs font-medium"><?= htmlspecialchars($kpi_data['pending_note']) ?></span>
                        </div>
                    </div>

                    <!-- Card 3: Total Pemasukan Bulan Ini (HERO DARK CHOCOLATE / ESPRESSO CARD) -->
                    <div class="craft-card-espresso rounded-xl p-5 relative overflow-hidden flex flex-col justify-between transition-transform hover:-translate-y-0.5 duration-200">
                        <div class="absolute -right-4 -bottom-4 w-24 h-24 rounded-full bg-white/5 pointer-events-none"></div>
                        <div class="flex items-start justify-between relative z-10">
                            <div>
                                <p class="text-[#D8C7B8] text-xs font-bold uppercase tracking-wider">Total Pemasukan</p>
                                <p class="text-xs text-[#B5A89E] mt-0.5">Bulan Ini (Akumulasi)</p>
                                <p class="text-[#FAF8F5] text-2xl font-bold mt-2 tracking-tight"><?= htmlspecialchars($kpi_data['total_revenue']) ?></p>
                            </div>
                            <div class="w-11 h-11 rounded-xl bg-emerald-500/20 border border-emerald-500/30 flex items-center justify-center shrink-0 shadow-inner">
                                <i class="fas fa-wallet text-emerald-300 text-lg"></i>
                            </div>
                        </div>
                        <div class="flex items-center gap-1.5 mt-3 pt-2 border-t border-white/10 relative z-10">
                            <span class="w-2 h-2 rounded-full bg-[#4ADE80]"></span>
                            <span class="text-[#86EFAC] text-xs font-medium"><?= htmlspecialchars($kpi_data['revenue_target']) ?></span>
                        </div>
                    </div>

                    <!-- Card 4: Pelanggan Baru -->
                    <div class="craft-card-espresso rounded-xl p-5 relative overflow-hidden flex flex-col justify-between transition-transform hover:-translate-y-0.5 duration-200">
                        <div class="absolute -right-4 -bottom-4 w-24 h-24 rounded-full bg-white/5 pointer-events-none"></div>
                        <div class="flex items-start justify-between relative z-10">
                            <div>
                                <p class="text-[#D8C7B8] text-xs font-bold uppercase tracking-wider">Pelanggan Baru</p>
                                <p class="text-xs text-[#B5A89E] mt-0.5">Bulan Ini</p>
                                <p class="text-[#FAF8F5] text-3xl font-bold mt-2 tracking-tight"><?= htmlspecialchars($kpi_data['new_customers']) ?></p>
                            </div>
                            <div class="w-11 h-11 rounded-xl bg-sky-500/20 border border-sky-500/30 flex items-center justify-center shrink-0 shadow-inner">
                                <i class="fas fa-user-plus text-sky-300 text-lg"></i>
                            </div>
                        </div>
                        <div class="flex items-center gap-1.5 mt-3 pt-2 border-t border-white/10 relative z-10">
                            <i class="far fa-calendar-check text-sky-400 text-xs"></i>
                            <span class="text-sky-200 text-xs font-medium"><?= htmlspecialchars($kpi_data['customers_note']) ?></span>
                        </div>
                    </div>
                </div>

                <!-- ROW 2: FULL-WIDTH PESANAN PERLU TINDAKAN CARD -->
                <div class="bg-[#FDFBF7] rounded-xl p-6 shadow-xs border border-[#E2D7C8] mb-6">
                    
                    <!-- Table Header Controls -->
                    <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4 mb-4">
                        <div class="flex items-center gap-3">
                            <h3 class="text-[#251A14] text-base md:text-lg font-bold">Pesanan Perlu Tindakan</h3>
                            <span id="pending-count-badge" class="bg-[#EFE8DE] text-[#92400E] border border-[#FDE68A] text-xs font-bold px-2.5 py-0.5 rounded-full">
                                <?= count($pending_orders) ?> Pesanan
                            </span>
                        </div>

                        <!-- Right Controls: Status Filter Pills + Live Search -->
                        <div class="flex flex-wrap items-center gap-2.5">
                            <!-- Filter Pills -->
                            <div class="flex items-center bg-[#EFE8DE] p-1 rounded-lg text-xs font-medium border border-[#E2D7C8]">
                                <button type="button" onclick="filterOrdersByStatus('all', this)" id="tabFilterAll" 
                                    class="px-3 py-1.5 rounded-md bg-[#3E2B21] text-[#FAF8F5] font-semibold shadow-xs transition-all status-filter-btn">
                                    Semua
                                </button>
                                <button type="button" onclick="filterOrdersByStatus('proses', this)" 
                                    class="px-3 py-1.5 rounded-md text-[#5E4D42] hover:text-[#251A14] transition-all status-filter-btn font-medium">
                                    Perlu Diproses
                                </button>
                                <button type="button" onclick="filterOrdersByStatus('kirim', this)" 
                                    class="px-3 py-1.5 rounded-md text-[#5E4D42] hover:text-[#251A14] transition-all status-filter-btn font-medium">
                                    Siap Kirim
                                </button>
                                <button type="button" onclick="filterOrdersByStatus('selesai', this)" 
                                    class="px-3 py-1.5 rounded-md text-[#5E4D42] hover:text-[#251A14] transition-all status-filter-btn font-medium">
                                    Selesai
                                </button>
                            </div>

                            <!-- Live Search Input -->
                            <div class="relative w-full sm:w-52">
                                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-[#8E7E73]">
                                    <i class="fas fa-search text-xs"></i>
                                </div>
                                <input type="text" id="orderSearchInput" onkeyup="filterOrdersTable()" placeholder="Cari pesanan..."
                                    class="w-full bg-[#EFE8DE] border border-[#DDD3C5] rounded-lg pl-9 pr-3 py-1.5 text-xs text-[#251A14] placeholder-[#8E7E73] focus:border-[#D46E15] focus:bg-white focus:outline-none transition-all">
                            </div>
                        </div>
                    </div>

                    <!-- Table Container -->
                    <div class="overflow-x-auto rounded-xl border border-[#E2D7C8] shadow-xs">
                        <table class="w-full text-left border-collapse" id="ordersTable">
                            <thead>
                                <tr class="bg-[#3E2B21] text-[#FAF8F5] text-xs font-bold uppercase tracking-wider">
                                    <th class="px-5 py-3.5">Nama Pelanggan</th>
                                    <th class="px-5 py-3.5">Produk</th>
                                    <th class="px-5 py-3.5">Deadline</th>
                                    <th class="px-5 py-3.5">Status Pesanan</th>
                                    <th class="px-5 py-3.5">Pembayaran</th>
                                    <th class="px-5 py-3.5 text-center">Aksi</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-[#EAE1D5] text-sm">
                                <?php foreach ($pending_orders as $idx => $order): ?>
                                    <tr class="<?= $idx % 2 === 1 ? 'bg-[#F8F5F0]' : 'bg-[#FDFBF7]' ?> hover:bg-[#F0E9DF] transition-colors order-row" 
                                        data-search="<?= strtolower($order['customer'] . ' ' . $order['product'] . ' ' . $order['payment_status'] . ' ' . $order['id']) ?>"
                                        data-status="<?= htmlspecialchars($order['status'] ?? 'pending') ?>">
                                        <!-- Nama Pelanggan -->
                                        <td class="px-5 py-4 text-[#251A14] font-medium whitespace-nowrap">
                                            <div class="font-bold text-[#251A14]"><?= htmlspecialchars($order['customer']) ?></div>
                                            <div class="text-[11px] text-[#8E7E73] font-mono mt-0.5"><?= htmlspecialchars($order['id']) ?></div>
                                        </td>
                                        
                                        <!-- Produk -->
                                        <td class="px-5 py-4 text-[#5E4D42] max-w-xs">
                                            <p class="truncate font-medium" title="<?= htmlspecialchars($order['product']) ?>"><?= htmlspecialchars($order['product']) ?></p>
                                        </td>

                                        <!-- Deadline -->
                                        <td class="px-5 py-4 whitespace-nowrap">
                                            <?php if ($order['is_urgent']): ?>
                                                <div class="flex items-center gap-1.5 text-[#991B1B] font-semibold">
                                                    <i class="far fa-clock text-xs"></i>
                                                    <span><?= htmlspecialchars($order['deadline']) ?></span>
                                                </div>
                                            <?php else: ?>
                                                <span class="text-[#5E4D42] font-medium"><?= htmlspecialchars($order['deadline']) ?></span>
                                            <?php endif; ?>
                                        </td>

                                        <!-- Status Pesanan Badge -->
                                        <td class="px-5 py-4 whitespace-nowrap">
                                            <?php
                                             $ordStatus = $order['status'] ?? 'pending';
                                             $statusPill = match($ordStatus) {
                                                 'proses' => ['Sedang Diproses', 'bg-[#FFF8F2] text-[#D97706] border border-[#FF9B45]/30'],
                                                 'kirim' => ['Siap Kirim', 'bg-[#F0F7FF] text-[#2F80ED] border border-[#2F80ED]/30'],
                                                 'selesai' => ['Selesai', 'bg-[#F2FBF6] text-[#1B7A43] border border-[#27AE60]/30'],
                                                 default => ['Pending', 'bg-[#EFE8DE] text-[#5E4D42] border border-[#DDD3C5]']
                                             };
                                             ?>
                                            <span class="<?= $statusPill[1] ?> px-2.5 py-1 rounded-full text-xs font-semibold">
                                                <?= $statusPill[0] ?>
                                            </span>
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
                                                class="text-[#D46E15] hover:text-[#B85207] font-bold text-xs inline-flex items-center gap-1 transition-colors group">
                                                <span>Lihat Detail</span>
                                                <i class="fas fa-chevron-right text-[10px] group-hover:translate-x-0.5 transition-transform"></i>
                                            </button>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>

                    <!-- Table Footer Summary -->
                    <div class="mt-4 bg-[#EFE8DE] rounded-xl px-5 py-3.5 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-2 border border-[#E2D7C8]">
                        <span id="table-footer-text" class="text-[#5E4D42] text-xs md:text-sm font-medium">
                            Menampilkan 1-<?= count($pending_orders) ?> dari <?= count($pending_orders) ?> pesanan pending
                        </span>
                        <a href="pesanan.php" class="text-[#D46E15] hover:text-[#B85207] font-bold text-xs md:text-sm flex items-center gap-1.5 transition-colors self-end sm:self-auto">
                            <span>Lihat Semua Pesanan</span>
                            <i class="fas fa-arrow-right text-xs"></i>
                        </a>
                    </div>
                </div>

                <!-- ROW 3: DUA WIDGET SIDE-BY-SIDE (PENGIRIMAN & KALENDER) -->
                <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-6">
                    
                    <!-- Left Widget: Pengiriman Hari Ini -->
                    <div class="bg-[#FDFBF7] rounded-xl p-6 shadow-xs border border-[#E2D7C8] flex flex-col justify-between">
                        <div>
                            <!-- Header -->
                            <div class="flex items-center justify-between pb-3 border-b border-[#EFE8DE] mb-4">
                                <div class="flex items-center gap-3">
                                    <div class="w-10 h-10 rounded-xl bg-[#FFF6EC] border border-[#F7D4B2] flex items-center justify-center shrink-0">
                                        <i class="fas fa-truck text-[#D46E15] text-base"></i>
                                    </div>
                                    <div>
                                        <h3 class="text-[#251A14] text-base font-bold leading-tight">Pengiriman Hari Ini</h3>
                                        <p class="text-[#8E7E73] text-xs mt-0.5"><?= $todayFormatted ?></p>
                                    </div>
                                </div>
                                <span class="bg-[#F0FDF4] text-[#166534] border border-[#BBF7D0] text-xs font-semibold px-2.5 py-1 rounded-full">
                                    2 Batch Siap
                                </span>
                            </div>

                            <!-- Batches List -->
                            <div class="space-y-3">
                                <?php foreach ($deliveries as $deliv): ?>
                                    <div class="bg-[#F5EFE6] rounded-xl p-3.5 flex items-center justify-between border border-[#E5DDD2] hover:border-[#D46E15]/50 transition-colors">
                                        <div class="flex items-center gap-3">
                                            <!-- Time Badge -->
                                            <div class="bg-[#FDFBF7] border border-[#DDD3C5] rounded-lg px-2.5 py-1 text-center min-w-[56px] shadow-2xs">
                                                <p class="text-[#D46E15] font-bold text-xs leading-tight"><?= htmlspecialchars($deliv['time']) ?></p>
                                                <p class="text-[#8E7E73] text-[10px] font-semibold leading-none mt-0.5">WIB</p>
                                            </div>
                                            <!-- Info -->
                                            <div>
                                                <h4 class="text-[#251A14] font-bold text-sm leading-tight"><?= htmlspecialchars($deliv['customer']) ?></h4>
                                                <p class="text-[#5E4D42] text-xs mt-0.5"><?= htmlspecialchars($deliv['items']) ?></p>
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
                        <div class="flex items-center justify-between pt-4 mt-4 border-t border-[#EFE8DE]">
                            <span class="text-[#5E4D42] text-xs font-medium">Total 30 box disiapkan hari ini</span>
                            <a href="pesanan.php?tab=kalender" class="text-[#D46E15] hover:text-[#B85207] font-bold text-xs flex items-center gap-1 transition-colors">
                                <span>Kelola Pengiriman</span>
                                <i class="fas fa-chevron-right text-[10px]"></i>
                            </a>
                        </div>
                    </div>

                    <!-- Right Widget: Jadwal Pengiriman (Mini Calendar) -->
                    <div class="bg-[#FDFBF7] rounded-xl p-6 shadow-xs border border-[#E2D7C8] flex flex-col justify-between">
                        <div>
                            <!-- Header -->
                            <div class="flex items-center justify-between pb-3 border-b border-[#EFE8DE] mb-4">
                                <div class="flex items-center gap-3">
                                    <div class="w-10 h-10 rounded-xl bg-[#FFF6EC] border border-[#F7D4B2] flex items-center justify-center shrink-0">
                                        <i class="far fa-calendar-alt text-[#D46E15] text-base"></i>
                                    </div>
                                    <h3 class="text-[#251A14] text-base font-bold">Jadwal Pengiriman</h3>
                                </div>
                                <div class="flex items-center gap-2">
                                    <span id="miniCalTitle" class="text-[#251A14] text-sm font-bold"><?= $currentMonthIndo ?></span>
                                    <div class="flex items-center gap-0.5">
                                        <button type="button" onclick="prevMiniMonth()" class="p-1 hover:bg-[#EFE8DE] rounded text-[#5E4D42] transition-colors" title="Bulan Sebelumnya">
                                            <i class="fas fa-chevron-left text-xs text-[#5E4D42]"></i>
                                        </button>
                                        <button type="button" onclick="nextMiniMonth()" class="p-1 hover:bg-[#EFE8DE] rounded text-[#5E4D42] transition-colors" title="Bulan Berikutnya">
                                            <i class="fas fa-chevron-right text-xs text-[#5E4D42]"></i>
                                        </button>
                                    </div>
                                </div>
                            </div>

                            <!-- Calendar Day Names -->
                            <div class="grid grid-cols-7 text-center text-xs font-semibold text-[#7A695D] mb-2.5">
                                <div>Min</div>
                                <div>Sen</div>
                                <div>Sel</div>
                                <div>Rab</div>
                                <div>Kam</div>
                                <div>Jum</div>
                                <div>Sab</div>
                            </div>

                            <!-- Calendar Dates Grid -->
                            <div id="miniCalGrid" class="grid grid-cols-7 gap-y-2 text-center text-xs">
                                
                                <!-- Week 1 -->
                                <div class="text-[#B5A89E] py-1">31</div>
                                <div class="text-[#251A14] font-medium py-1">1</div>
                                <div class="text-[#251A14] font-medium py-1">2</div>
                                <div class="text-[#251A14] font-medium py-1">3</div>
                                <div class="text-[#251A14] font-medium py-1">4</div>
                                <div class="text-[#251A14] font-medium py-1">5</div>
                                <div class="text-[#251A14] font-medium py-1">6</div>

                                <!-- Week 2 -->
                                <div class="text-[#251A14] font-medium py-1">7</div>
                                <div class="text-[#251A14] font-medium py-1">8</div>
                                <div class="text-[#251A14] font-medium py-1">9</div>
                                <div class="text-[#251A14] font-medium py-1">10</div>
                                <div class="text-[#251A14] font-medium py-1">11</div>
                                <div class="text-[#251A14] font-medium py-1">12</div>
                                <div class="text-[#251A14] font-medium py-1">13</div>

                                <!-- Week 3 -->
                                <div class="text-[#251A14] font-medium py-1">14</div>
                                <div class="text-[#251A14] font-medium py-1">15</div>
                                <div class="text-[#251A14] font-medium py-1">16</div>
                                <div class="text-[#251A14] font-medium py-1">17</div>
                                <div class="text-[#251A14] font-medium py-1">18</div>
                                <div class="text-[#251A14] font-medium py-1">19</div>
                                <!-- 20 has delivery dot -->
                                <div onclick="openDeliveryScheduleModal(20)" class="flex flex-col items-center justify-center p-1 rounded-lg cursor-pointer hover:bg-[#EFE8DE] transition-colors" title="Lihat Jadwal Kirim Tgl 20">
                                    <span class="text-[#251A14] font-medium">20</span>
                                    <span class="w-1.5 h-1.5 rounded-full bg-[#D46E15] mt-0.5"></span>
                                </div>

                                <!-- Week 4 -->
                                <div onclick="openDeliveryScheduleModal(21)" class="text-[#251A14] font-medium py-1 p-1 rounded-lg cursor-pointer hover:bg-[#EFE8DE] transition-colors">21</div>
                                <div onclick="openDeliveryScheduleModal(22)" class="text-[#251A14] font-medium py-1 p-1 rounded-lg cursor-pointer hover:bg-[#EFE8DE] transition-colors">22</div>
                                <div onclick="openDeliveryScheduleModal(23)" class="text-[#251A14] font-medium py-1 p-1 rounded-lg cursor-pointer hover:bg-[#EFE8DE] transition-colors">23</div>
                                <!-- 24 has delivery dot -->
                                <div onclick="openDeliveryScheduleModal(24)" class="flex flex-col items-center justify-center p-1 rounded-lg cursor-pointer hover:bg-[#EFE8DE] transition-colors" title="Lihat Jadwal Kirim Tgl 24">
                                    <span class="text-[#251A14] font-medium">24</span>
                                    <span class="w-1.5 h-1.5 rounded-full bg-[#D46E15] mt-0.5"></span>
                                </div>
                                <!-- 25 past highlight -->
                                <div onclick="openDeliveryScheduleModal(25)" class="flex items-center justify-center p-1 rounded-lg cursor-pointer hover:bg-[#EFE8DE] transition-colors" title="Lihat Jadwal Kirim Tgl 25">
                                    <span class="w-7 h-7 rounded-full bg-[#EFE8DE] text-[#5E4D42] font-medium flex items-center justify-center">25</span>
                                </div>
                                <!-- 26 TODAY ACTIVE HIGHLIGHT (Signature Espresso Circle) -->
                                <div onclick="openDeliveryScheduleModal(26)" class="flex flex-col items-center justify-center p-1 rounded-lg cursor-pointer hover:bg-[#EFE8DE] transition-colors" title="Jadwal Hari Ini (Tgl 26)">
                                    <span class="w-7 h-7 rounded-full bg-[#3E2B21] text-[#FAF8F5] font-bold flex items-center justify-center shadow-xs">26</span>
                                    <span class="w-1.5 h-1.5 rounded-full bg-[#D46E15] mt-0.5"></span>
                                </div>
                                <!-- 27 has delivery dot -->
                                <div onclick="openDeliveryScheduleModal(27)" class="flex flex-col items-center justify-center p-1 rounded-lg cursor-pointer hover:bg-[#EFE8DE] transition-colors" title="Lihat Jadwal Kirim Tgl 27">
                                    <span class="text-[#251A14] font-medium">27</span>
                                    <span class="w-1.5 h-1.5 rounded-full bg-[#D46E15] mt-0.5"></span>
                                </div>

                                <!-- Week 5 -->
                                <!-- 28 has delivery dot -->
                                <div onclick="openDeliveryScheduleModal(28)" class="flex flex-col items-center justify-center p-1 rounded-lg cursor-pointer hover:bg-[#EFE8DE] transition-colors" title="Lihat Jadwal Kirim Tgl 28">
                                    <span class="text-[#251A14] font-medium">28</span>
                                    <span class="w-1.5 h-1.5 rounded-full bg-[#D46E15] mt-0.5"></span>
                                </div>
                                <div onclick="openDeliveryScheduleModal(29)" class="text-[#251A14] font-medium py-1 p-1 rounded-lg cursor-pointer hover:bg-[#EFE8DE] transition-colors">29</div>
                                <!-- 30 has delivery dot -->
                                <div onclick="openDeliveryScheduleModal(30)" class="flex flex-col items-center justify-center p-1 rounded-lg cursor-pointer hover:bg-[#EFE8DE] transition-colors" title="Lihat Jadwal Kirim Tgl 30">
                                    <span class="text-[#251A14] font-medium">30</span>
                                    <span class="w-1.5 h-1.5 rounded-full bg-[#D46E15] mt-0.5"></span>
                                </div>
                                <div class="text-[#B5A89E] py-1">1</div>
                                <div class="text-[#B5A89E] py-1">2</div>
                                <div class="text-[#B5A89E] py-1">3</div>
                                <div class="text-[#B5A89E] py-1">4</div>
                            </div>
                        </div>

                        <!-- Footer Legend -->
                        <div class="flex items-center justify-between pt-3 mt-4 border-t border-[#EFE8DE] text-xs">
                            <div class="flex items-center gap-1.5">
                                <span class="w-2 h-2 rounded-full bg-[#D46E15]"></span>
                                <span class="text-[#5E4D42]">Ada jadwal kirim</span>
                            </div>
                            <div class="flex items-center gap-1.5">
                                <span class="w-2.5 h-2.5 rounded-full bg-[#3E2B21]"></span>
                                <span class="text-[#251A14] font-semibold">Hari ini</span>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- ROW 4: AKTIVITAS TERBARU (FULL WIDTH CARD) -->
                <div class="bg-[#FDFBF7] rounded-xl p-6 shadow-xs border border-[#E2D7C8]">
                    
                    <!-- Header -->
                    <div class="flex items-center justify-between mb-6">
                        <div class="flex items-center gap-2.5">
                            <div class="w-8 h-8 rounded-lg bg-[#FFF6EC] border border-[#F7D4B2] flex items-center justify-center">
                                <i class="fas fa-history text-xs text-[#D46E15]"></i>
                            </div>
                            <h3 class="text-[#251A14] text-base font-bold">Aktivitas Operasional Terbaru</h3>
                        </div>
                        <span class="bg-[#EFE8DE] text-[#5E4D42] text-xs font-semibold px-3 py-1 rounded-full border border-[#DDD3C5]">
                            Hari Ini
                        </span>
                    </div>

                    <!-- Continuous Vertical Line Timeline -->
                    <div class="relative timeline-container pl-6 space-y-6">
                        <?php foreach ($activities as $act): ?>
                            <div class="relative flex flex-col sm:flex-row sm:items-center sm:justify-between gap-1 group">
                                <!-- Dot with canvas matching ring -->
                                <div class="absolute -left-6 top-1 sm:top-1.5 w-3.5 h-3.5 rounded-full <?= $act['dot_color'] ?> ring-4 ring-[#FDFBF7] shadow-xs"></div>
                                
                                <!-- Activity Content -->
                                <div class="text-sm pr-4 text-[#251A14]">
                                    <?= $act['text'] ?>
                                </div>

                                <!-- Timestamp -->
                                <span class="text-[#8E7E73] text-xs whitespace-nowrap shrink-0 font-medium">
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
    <div id="orderDetailModal" class="fixed inset-0 craft-modal-overlay z-50 flex items-center justify-center p-4 hidden">
        <div class="craft-modal-dialog max-w-lg w-full p-6 relative max-h-[92vh] overflow-y-auto">
            <!-- Header -->
            <div class="flex items-center justify-between pb-4 border-b border-[#ECE5DD]">
                <div class="flex items-center gap-2.5">
                    <span class="w-2.5 h-2.5 rounded-full bg-[#D46E15]"></span>
                    <div>
                        <h3 class="font-bold text-lg text-[#251A14]">Detail Pesanan</h3>
                        <p id="modalOrderId" class="text-xs font-bold text-[#D46E15] tabular-nums leading-tight">-</p>
                    </div>
                </div>
                <div class="flex items-center gap-2">
                    <span id="modalStatusBadge" class="px-2.5 py-1 rounded-full text-xs font-semibold bg-amber-50 text-amber-800 border border-amber-200">
                        Proses
                    </span>
                    <button onclick="closeModal('orderDetailModal')" class="text-[#8E7E73] hover:text-[#251A14] p-1.5 rounded-lg hover:bg-[#F5F2ED] transition-colors">
                        <i class="fas fa-times text-base"></i>
                    </button>
                </div>
            </div>

            <!-- Content -->
            <div class="py-5 space-y-4">
                <!-- Customer Info Card -->
                <div class="bg-[#FAF8F5] border border-[#ECE5DD] rounded-xl p-3.5 space-y-2">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-[10px] text-[#8E7E73] uppercase font-semibold">Nama Pelanggan</p>
                            <p id="modalCustomer" class="text-sm font-bold text-[#251A14]">-</p>
                        </div>
                        <a id="modalWaLink" href="#" target="_blank" class="inline-flex items-center gap-1.5 px-2.5 py-1 bg-emerald-50 hover:bg-emerald-100 text-emerald-800 border border-emerald-200 rounded-lg text-xs font-semibold transition-colors">
                            <i class="fab fa-whatsapp text-sm text-emerald-600"></i>
                            <span id="modalWa" class="tabular-nums">-</span>
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
                    <div class="bg-[#FFF8F2] border border-[#D46E15]/20 rounded-xl p-3">
                        <p class="text-[10px] text-[#828282] uppercase font-semibold">Batas Waktu (Deadline)</p>
                        <p id="modalDeadline" class="text-xs font-bold text-[#EB5757] mt-1">-</p>
                    </div>
                    <div class="bg-[#F9F7F5] border border-[#F0ECE9] rounded-xl p-3">
                        <p class="text-[10px] text-[#828282] uppercase font-semibold">Total Tagihan</p>
                        <p id="modalTotal" class="text-sm font-bold text-[#D46E15] mt-1">-</p>
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
                        class="px-2.5 py-2 bg-[#FFF8F2] hover:bg-[#FFEEDD] border border-[#D46E15]/40 text-[#D97706] rounded-xl text-xs font-semibold transition-colors flex items-center justify-center gap-1">
                        <i class="fas fa-fire mr-1 text-xs"></i>
                        <span>Oven/Proses</span>
                    </button>
                    <button type="button" onclick="submitStatusChange('kirim')" 
                        class="px-2.5 py-2 bg-[#F0F7FF] hover:bg-[#E0EFFF] border border-[#2F80ED]/40 text-[#2F80ED] rounded-xl text-xs font-semibold transition-colors flex items-center justify-center gap-1">
                        <i class="fas fa-truck mr-1 text-xs"></i>
                        <span>Siap Kirim</span>
                    </button>
                    <button type="button" onclick="submitStatusChange('selesai')" 
                        class="px-2.5 py-2 bg-[#F2FBF6] hover:bg-[#E2F7EB] border border-[#27AE60]/40 text-[#1B7A43] rounded-xl text-xs font-semibold transition-colors flex items-center justify-center gap-1">
                        <i class="fas fa-check mr-1 text-xs"></i>
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
        <!-- Backdrop with subtle blur -->
        <div onclick="closeOrderModal()" class="fixed inset-0 craft-modal-overlay"></div>

        <!-- Modal Card Container -->
        <div class="craft-modal-dialog max-w-[740px] w-full relative z-10 max-h-[90vh] flex flex-col overflow-hidden bg-[#FDFBF7] border border-[#E2D7C8]">
            
            <!-- Header (Sticky Top - Compact) -->
            <div class="flex items-center justify-between px-6 py-4 border-b border-[#E2D7C8] shrink-0 bg-[#FDFBF7]">
                <div>
                    <h3 class="text-lg font-bold text-[#251A14] tracking-tight">Tambah Pesanan Baru</h3>
                    <p class="text-[#8E7E73] text-xs mt-0.5">Isi detail pesanan, rincian produk bakery, dan jadwal pengiriman.</p>
                </div>
                <button onclick="closeOrderModal()" class="text-[#8E7E73] hover:text-[#251A14] p-1.5 rounded-lg hover:bg-[#EFE8DE] transition-colors" title="Tutup">
                    <i class="fas fa-times text-base"></i>
                </button>
            </div>

            <!-- Form -->
            <form id="orderForm" method="POST" action="beranda.php" class="flex flex-col flex-1 overflow-hidden">
                <input type="hidden" name="action" value="tambah_pesanan">
                
                <!-- Scrollable Body Content -->
                <div class="px-6 py-4 overflow-y-auto flex-1 space-y-4">
                    
                    <!-- SECTION 1: DATA PELANGGAN -->
                    <div>
                        <div class="flex items-center justify-between mb-2">
                            <label class="text-xs font-bold text-[#251A14] uppercase tracking-wider">Data Pelanggan</label>
                            <button type="button" onclick="toggleNewCustomerForm()" id="btnToggleNewCust" 
                                class="text-xs font-semibold text-[#D46E15] hover:text-[#B85207] flex items-center gap-1.5 transition-colors">
                                <i class="fas fa-user-plus text-xs"></i>
                                <span id="labelToggleNewCust">+ Tambah Pelanggan Baru</span>
                            </button>
                        </div>
                        
                        <!-- Search Bar with Live Autocomplete -->
                        <div id="custSearchWrapper" class="relative mb-2.5">
                            <div class="relative">
                                <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-[#8E7E73]">
                                    <i class="fas fa-search text-xs"></i>
                                </div>
                                <input type="text" id="custSearchInput" placeholder="Ketik nama atau No. WhatsApp pelanggan..." autocomplete="off"
                                    oninput="handleCustomerSearch(this.value)" onfocus="handleCustomerSearchFocus()"
                                    value="<?= htmlspecialchars($customers_for_select[0]['nama'] ?? '') ?>"
                                    class="w-full bg-[#EFE8DE] border border-[#DDD3C5] rounded-xl pl-9 pr-9 py-2 text-xs text-[#251A14] font-medium placeholder-[#8E7E73] focus:border-[#D46E15] focus:outline-none transition-colors">
                                <div class="absolute inset-y-0 right-0 pr-3 flex items-center text-[#8E7E73] pointer-events-none">
                                    <i class="fas fa-chevron-down text-[10px]"></i>
                                </div>
                            </div>
                            
                            <!-- Floating Dropdown Matches -->
                            <div id="custSearchResults" class="hidden absolute left-0 right-0 top-full mt-1 bg-[#FDFBF7] border border-[#DDD3C5] rounded-xl shadow-xl max-h-48 overflow-y-auto z-40 divide-y divide-[#EFE8DE]">
                                <!-- Populated dynamically by JS -->
                            </div>
                            <input type="hidden" name="id_pelanggan" id="selectedCustomerId" value="<?= $customers_for_select[0]['id'] ?? 1 ?>">
                        </div>

                        <!-- New Customer Custom Inputs (Toggleable) -->
                        <div id="newCustomerFields" class="hidden bg-[#FFF8F2] border border-[#D46E15]/30 rounded-xl p-3.5 space-y-2.5 mb-2.5">
                            <div class="flex items-center justify-between">
                                <p class="text-xs font-bold text-[#924C00]">Form Input Pelanggan Baru</p>
                                <button type="button" onclick="cancelNewCustomerForm()" class="text-[11px] text-[#8E7E73] hover:text-[#D46E15] font-medium">Batal / Gunakan Data Yang Ada</button>
                            </div>
                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-2.5">
                                <input type="text" id="inputNamaBaru" name="nama_pelanggan_baru" placeholder="Nama Lengkap Pelanggan *" class="w-full bg-white border border-[#DDD3C5] rounded-lg px-3 py-2 text-xs outline-none focus:border-[#D46E15]" oninput="syncNewCustomerPreview()">
                                <input type="text" id="inputWaBaru" name="no_wa_baru" placeholder="Nomor WhatsApp (08xxx) *" class="w-full bg-white border border-[#DDD3C5] rounded-lg px-3 py-2 text-xs outline-none focus:border-[#D46E15]" oninput="syncNewCustomerPreview()">
                            </div>
                            <input type="text" id="inputAlamatBaru" name="alamat_baru" placeholder="Alamat Pengiriman Lengkap *" class="w-full bg-white border border-[#DDD3C5] rounded-lg px-3 py-2 text-xs outline-none focus:border-[#D46E15]" oninput="syncNewCustomerPreview()">
                        </div>

                        <!-- Detail Pelanggan Box (3 Columns Preview) -->
                        <div class="bg-[#EFE8DE] border border-[#DDD3C5] rounded-xl p-3 grid grid-cols-1 sm:grid-cols-3 gap-3 text-xs">
                            <div>
                                <p class="text-[#8E7E73] uppercase text-[10px] font-semibold">Nama Pelanggan</p>
                                <p id="previewCustName" class="font-bold text-[#251A14] mt-0.5 truncate"><?= htmlspecialchars($customers_for_select[0]['nama'] ?? 'Ibu Sari Dewi') ?></p>
                            </div>
                            <div>
                                <p class="text-[#8E7E73] uppercase text-[10px] font-semibold">No. WhatsApp</p>
                                <p id="previewCustWa" class="font-bold text-[#251A14] mt-0.5 truncate"><?= htmlspecialchars($customers_for_select[0]['no_wa'] ?? '0812-3456-7890') ?></p>
                            </div>
                            <div>
                                <p class="text-[#8E7E73] uppercase text-[10px] font-semibold">Alamat Pengiriman</p>
                                <p id="previewCustAddress" class="font-medium text-[#251A14] mt-0.5 truncate"><?= htmlspecialchars($customers_for_select[0]['alamat'] ?? 'Komplek Melati No. 4, Blok B') ?></p>
                            </div>
                        </div>
                    </div>

                <!-- SECTION 2: DETAIL PESANAN -->
                <div>
                    <div class="flex items-center justify-between mb-3">
                        <h4 class="text-xs font-bold text-[#251A14] uppercase tracking-wider">Detail Pesanan</h4>
                        <span class="text-xs text-[#8E7E73] font-medium" id="itemCountLabel">2 Item Dipilih</span>
                    </div>

                    <!-- Items Table -->
                    <div class="border border-[#E2D7C8] rounded-xl overflow-x-auto mb-3 shadow-2xs">
                        <table class="w-full text-left text-xs min-w-[560px]">
                            <thead class="bg-[#3E2B21] text-[#FAF8F5] font-semibold uppercase text-xs">
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
                                <!-- Default Row 1: Roti Hajatan Kardus Putih -->
                                <tr class="order-product-row" data-min-order="1" data-price="10000">
                                    <td class="px-3.5 py-3">
                                        <input type="hidden" name="items[0][nama]" value="Roti Hajatan (9 Pilihan Topping)" class="item-name-input">
                                        <input type="hidden" name="items[0][id_varian]" value="1" class="item-varian-input">
                                        <p class="font-bold text-[#1C1C1C] item-title">Roti Hajatan (9 Pilihan Topping)</p>
                                        <p class="text-[10px] text-[#27AE60] font-semibold item-moq-label">Ready Stock (Min. 1 box)</p>
                                    </td>
                                    <td class="px-3.5 py-3 text-[#544337]">
                                        <span class="item-kemasan-text">Kardus Putih</span>
                                        <input type="hidden" name="items[0][kemasan]" value="Kardus Putih" class="item-kemasan-input">
                                    </td>
                                    <td class="px-3.5 py-3">
                                        <input type="number" name="items[0][qty]" value="10" min="1" oninput="calculateOrderTotal()" 
                                            class="item-qty-input w-16 px-2 py-1 border border-[#E0E0E0] text-[#1C1C1C] font-semibold rounded text-center focus:outline-none focus:border-[#D46E15] transition-colors">
                                    </td>
                                    <td class="px-3.5 py-3 text-[#544337]">
                                        <span class="item-price-text">Rp 10.000</span>
                                        <input type="hidden" name="items[0][harga]" value="10000" class="item-price-input">
                                    </td>
                                    <td class="px-3.5 py-3 font-bold text-[#1C1C1C] item-subtotal-text">Rp 100.000</td>
                                    <td class="px-3 py-3 text-center">
                                        <button type="button" onclick="removeOrderProductRow(this)" class="text-[#8E7E73] hover:text-[#EB5757] p-1 transition-colors" title="Hapus Item">
                                            <i class="far fa-trash-alt text-xs"></i>
                                        </button>
                                    </td>
                                </tr>

                                <!-- Default Row 2: Bolu Pisang Premium Mika -->
                                <tr class="order-product-row" data-min-order="1" data-price="22000">
                                    <td class="px-3.5 py-3">
                                        <input type="hidden" name="items[1][nama]" value="Bolu Pisang Premium" class="item-name-input">
                                        <input type="hidden" name="items[1][id_varian]" value="7" class="item-varian-input">
                                        <p class="font-bold text-[#1C1C1C] item-title">Bolu Pisang Premium</p>
                                        <p class="text-[10px] text-[#27AE60] font-semibold item-moq-label">Ready Batch Pagi</p>
                                    </td>
                                    <td class="px-3.5 py-3 text-[#544337]">
                                        <span class="item-kemasan-text">Mika</span>
                                        <input type="hidden" name="items[1][kemasan]" value="Mika" class="item-kemasan-input">
                                    </td>
                                    <td class="px-3.5 py-3">
                                        <input type="number" name="items[1][qty]" value="2" min="1" oninput="calculateOrderTotal()" 
                                            class="item-qty-input w-16 px-2 py-1 border border-[#E0E0E0] text-[#1C1C1C] font-semibold rounded text-center focus:outline-none focus:border-[#D46E15] transition-colors">
                                    </td>
                                    <td class="px-3.5 py-3 text-[#544337]">
                                        <span class="item-price-text">Rp 22.000</span>
                                        <input type="hidden" name="items[1][harga]" value="22000" class="item-price-input">
                                    </td>
                                    <td class="px-3.5 py-3 font-bold text-[#1C1C1C] item-subtotal-text">Rp 44.000</td>
                                    <td class="px-3 py-3 text-center">
                                        <button type="button" onclick="removeOrderProductRow(this)" class="text-[#8E7E73] hover:text-[#EB5757] p-1 transition-colors" title="Hapus Item">
                                            <i class="far fa-trash-alt text-xs"></i>
                                        </button>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>

                    <!-- Tambah Produk Button -->
                    <button type="button" onclick="addOrderProductRow()" 
                        class="w-full py-2.5 border border-dashed border-[#D46E15] text-[#D46E15] hover:bg-[#D46E15]/10 text-xs font-semibold rounded-xl flex items-center justify-center gap-1.5 transition-colors mb-3">
                        <i class="fas fa-plus text-xs"></i>
                        <span>Tambah Produk</span>
                    </button>

                    <!-- Validation Callout Alert (MOQ Warning) -->
                    <div id="moqAlertBox" class="bg-[#FFF5F5] border border-[#EB5757]/30 rounded-xl p-3 flex items-start gap-2.5 hidden">
                        <span class="text-base text-[#EB5757] shrink-0">🔒</span>
                        <p class="text-xs text-[#EB5757] leading-relaxed" id="moqAlertMessage">
                            <strong>Roti Kopi</strong> — Jumlah pesanan kurang dari minimum order. Pesanan dikunci dan tidak dapat disimpan sebelum memenuhi jumlah minimum.
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
                                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-[#8E7E73]">
                                        <i class="far fa-calendar-alt text-xs"></i>
                                    </div>
                                    <input type="date" name="tgl_kirim" value="<?= date('Y-m-d') ?>" 
                                        class="w-full bg-white border border-[#E0E0E0] rounded-lg pl-9 pr-3.5 py-2 text-xs text-[#1C1C1C] focus:border-[#D46E15] focus:outline-none">
                                </div>
                            </div>

                            <div>
                                <label class="block text-xs font-semibold text-[#333333] mb-1">Waktu / Jam Pengiriman</label>
                                <div class="relative">
                                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-[#8E7E73]">
                                        <i class="far fa-clock text-xs"></i>
                                    </div>
                                    <input type="time" name="waktu_kirim" value="14:00" 
                                        class="w-full bg-white border border-[#E0E0E0] rounded-lg pl-9 pr-3.5 py-2 text-xs text-[#1C1C1C] focus:border-[#D46E15] focus:outline-none">
                                </div>
                            </div>

                            <div>
                                <label class="block text-xs font-semibold text-[#333333] mb-1">Catatan Dapur / Driver</label>
                                <textarea name="catatan" rows="2" placeholder="Catatan khusus: roti hajatan kardus putih diikat rapi, antar sebelum jam 3 sore..." 
                                    class="w-full bg-white border border-[#E0E0E0] rounded-lg p-3 text-xs text-[#1C1C1C] focus:border-[#D46E15] focus:outline-none"></textarea>
                            </div>
                        </div>

                        <!-- Right: Total Tagihan & Status Bayar -->
                        <div class="bg-[#EFE8DE] border border-[#DDD3C5] rounded-2xl p-4 flex flex-col justify-between">
                            <div>
                                <p class="text-[11px] font-bold text-[#828282] uppercase tracking-wider">TOTAL TAGIHAN</p>
                                <h3 class="text-2xl font-bold text-[#D46E15] mt-0.5 tracking-tight" id="orderGrandTotal">Rp 144.000</h3>
                                
                                <div class="mt-4">
                                    <label class="block text-xs font-semibold text-[#333333] mb-2">Status Pembayaran</label>
                                    <div class="grid grid-cols-3 gap-2">
                                        <button type="button" onclick="selectPaymentStatus('belum_bayar', this)" id="payBtn1" 
                                            class="py-1.5 px-2 rounded-lg text-xs font-semibold bg-[#3E2B21] text-white shadow-2xs transition-colors pay-status-btn">
                                            Belum Bayar
                                        </button>
                                        <button type="button" onclick="selectPaymentStatus('hutang', this)" id="payBtn2" 
                                            class="py-1.5 px-2 rounded-lg text-xs font-semibold bg-white border border-[#DDD3C5] text-[#3E2B21] hover:bg-[#F5F1EB] transition-colors pay-status-btn">
                                            Hutang
                                        </button>
                                        <button type="button" onclick="selectPaymentStatus('lunas', this)" id="payBtn3" 
                                            class="py-1.5 px-2 rounded-lg text-xs font-semibold bg-white border border-[#DDD3C5] text-[#3E2B21] hover:bg-[#F5F1EB] transition-colors pay-status-btn">
                                            Lunas
                                        </button>
                                    </div>
                                    <input type="hidden" name="status_bayar" id="selectedPaymentStatus" value="belum_bayar">
                                </div>
                            </div>

                            <div class="mt-4">
                                <label class="block text-xs font-semibold text-[#333333] mb-1">Metode Pembayaran</label>
                                <select name="metode_bayar" class="w-full bg-white border border-[#E0E0E0] rounded-lg px-3 py-2 text-xs text-[#1C1C1C] focus:border-[#D46E15] focus:outline-none">
                                    <option value="cash">Tunai / Cash saat Kurir Tiba</option>
                                    <option value="transfer">Transfer Bank (BCA / Mandiri)</option>
                                </select>
                            </div>
                        </div>

                    </div>
                </div>

                </div>
                <!-- /Scrollable Body Content -->

                <!-- STICKY MODAL FOOTER -->
                <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3 p-4 sm:px-8 border-t border-[#E2D7C8] bg-[#F5F1EB] shrink-0">
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
    <!-- MODAL 3: DETAIL JADWAL PENGIRIMAN KALENDER                    -->
    <!-- ============================================================== -->
    <div id="deliveryScheduleModal" class="fixed inset-0 z-50 hidden flex items-center justify-center p-4">
        <div onclick="closeDeliveryScheduleModal()" class="fixed inset-0 craft-modal-overlay"></div>
        <div class="craft-modal-dialog max-w-lg w-full relative z-10 bg-[#FDFBF7] border border-[#E2D7C8] rounded-2xl overflow-hidden shadow-2xl animate-in fade-in zoom-in-95 duration-150">
            <!-- Header -->
            <div class="px-6 py-4 border-b border-[#E2D7C8] flex items-center justify-between bg-[#FDFBF7]">
                <div class="flex items-center gap-2.5">
                    <div class="w-8 h-8 rounded-lg bg-[#D46E15]/15 text-[#D46E15] flex items-center justify-center">
                        <i class="far fa-calendar-alt text-sm"></i>
                    </div>
                    <div>
                        <h3 class="text-base font-bold text-[#251A14]" id="delivModalTitle">Jadwal Pengiriman Roti</h3>
                        <p class="text-xs text-[#8E7E73]" id="delivModalSub">Informasi armada & pesanan tanggal terpilih</p>
                    </div>
                </div>
                <button onclick="closeDeliveryScheduleModal()" class="text-[#8E7E73] hover:text-[#251A14] p-1.5 rounded-lg hover:bg-[#EFE8DE] transition-colors">
                    <i class="fas fa-times text-base"></i>
                </button>
            </div>
            <!-- Body -->
            <div class="p-6 space-y-3.5 max-h-[65vh] overflow-y-auto" id="delivModalBody">
                <!-- Delivery Content Dynamic -->
            </div>
            <!-- Footer -->
            <div class="px-6 py-3.5 border-t border-[#E2D7C8] bg-[#F5F1EB] flex items-center justify-between">
                <a href="pesanan.php?tab=kalender" class="text-xs font-semibold text-[#D46E15] hover:text-[#B85207] flex items-center gap-1.5">
                    <span>Lihat di Kalender Pesanan</span>
                    <i class="fas fa-arrow-right text-[10px]"></i>
                </a>
                <button type="button" onclick="closeDeliveryScheduleModal()" class="craft-btn-secondary text-xs px-4 py-2 rounded-lg">
                    Tutup
                </button>
            </div>
        </div>
    </div>

    <!-- ============================================================== -->
    <!-- MODAL POPOUT: LOGOUT / KELUAR AKUN (SESUAI FIGMA SCREENSHOT)   -->
    <!-- ============================================================== -->
    <div id="logoutModal" class="fixed inset-0 z-50 hidden flex items-center justify-center p-4">
        <!-- Backdrop with subtle blur -->
        <div onclick="closeLogoutModal()" class="fixed inset-0 craft-modal-overlay"></div>

        <!-- Modal Card Container -->
        <div class="craft-modal-dialog max-w-sm w-full p-6 sm:p-7 relative z-10 text-center">
            <!-- Top Icon -->
            <div class="w-12 h-12 rounded-xl bg-rose-50 border border-rose-200/60 text-rose-600 flex items-center justify-center mx-auto mb-4">
                <i class="fas fa-sign-out-alt text-2xl text-rose-600"></i>
            </div>

            <!-- Title -->
            <h3 class="text-base sm:text-lg font-bold text-[#251A14] tracking-tight">Keluar dari Akun?</h3>
            
            <!-- Description -->
            <p class="text-xs text-[#8E7E73] mt-1.5 mb-6 leading-relaxed max-w-[280px] mx-auto">
                Anda akan keluar dari sesi ini. Pastikan semua perubahan transaksi sudah tersimpan.
            </p>

            <!-- Action Buttons -->
            <div class="space-y-2">
                <a href="logout.php" 
                    class="w-full bg-rose-600 hover:bg-rose-700 active:scale-[0.98] text-white text-xs sm:text-sm font-medium py-2.5 px-4 rounded-lg flex items-center justify-center gap-2 shadow-xs transition-all">
                    <i class="fas fa-sign-out-alt text-sm"></i>
                    <span>Ya, Keluar</span>
                </a>

                <button type="button" onclick="closeLogoutModal()" 
                    class="w-full craft-btn-secondary text-xs sm:text-sm py-2 px-4 rounded-lg">
                    Batal
                </button>
            </div>

            <!-- Encrypted Session Note -->
            <div class="mt-5 flex items-center justify-center gap-1.5 text-[11px] text-[#A89C92]">
                <i class="fas fa-lock text-xs text-[#A89C92]"></i>
                <span>Sesi aman terenkripsi Berkat Dinasti</span>
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
            btn.className = 'px-3 py-1.5 rounded-md bg-[#3E2B21] text-[#FAF8F5] font-semibold shadow-xs transition-all status-filter-btn';

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

        // Status advance submit from Order Detail Modal (via AJAX with seamless UI update)
        function submitStatusChange(newStatus) {
            const orderId = document.getElementById('modalUpdateOrderId').value;
            if (!orderId) return;

            const formData = new FormData();
            formData.append('action', 'update_status');
            formData.append('order_id', orderId);
            formData.append('new_status', newStatus);
            formData.append('is_ajax', '1');

            const statusMap = {
                'pending': { label: 'Menunggu Konfirmasi', badgeClass: 'bg-amber-50 text-amber-800 border-amber-200' },
                'proses': { label: 'Oven/Proses', badgeClass: 'bg-orange-50 text-orange-800 border-orange-200' },
                'kirim': { label: 'Siap Kirim', badgeClass: 'bg-blue-50 text-blue-800 border-blue-200' },
                'selesai': { label: 'Selesai', badgeClass: 'bg-emerald-50 text-emerald-800 border-emerald-200' }
            };
            const targetInfo = statusMap[newStatus] || { label: newStatus, badgeClass: 'bg-gray-100 text-gray-800 border-gray-200' };

            fetch('beranda.php', {
                method: 'POST',
                headers: { 'X-Requested-With': 'XMLHttpRequest' },
                body: formData
            })
            .then(res => res.json())
            .then(data => {
                if (data.success) {
                    // Update Modal Status Badge
                    const modalBadge = document.getElementById('modalStatusBadge');
                    if (modalBadge) {
                        modalBadge.textContent = targetInfo.label;
                        modalBadge.className = `px-2.5 py-1 rounded-full text-xs font-semibold border ${targetInfo.badgeClass}`;
                    }

                    // Update Table Row Status Badge if present
                    document.querySelectorAll('.order-row').forEach(row => {
                        const invCell = row.querySelector('.order-invoice');
                        if (invCell && invCell.textContent.trim() === orderId) {
                            const badge = row.querySelector('.order-status-badge');
                            if (badge) {
                                badge.textContent = targetInfo.label;
                                badge.className = `order-status-badge px-2.5 py-1 rounded-full text-xs font-semibold border ${targetInfo.badgeClass}`;
                            }
                        }
                    });

                    // Update memory array
                    const found = orders.find(o => o.id === orderId);
                    if (found) found.status = newStatus;

                    showToast(data.message || `Status berhasil diubah menjadi ${targetInfo.label}!`);
                } else {
                    showToast('Gagal memperbarui status');
                }
            })
            .catch(() => {
                // Fallback traditional form submit
                document.getElementById('modalNewStatusInput').value = newStatus;
                document.getElementById('updateStatusForm').submit();
            });
        }

        // =========================================================================
        // CUSTOMER AUTOCOMPLETE & NEW CUSTOMER LOGIC
        // =========================================================================
        let isNewCustomerMode = false;

        function handleCustomerSearch(query) {
            const resultsBox = document.getElementById('custSearchResults');
            if (!resultsBox) return;

            const q = (query || '').toLowerCase().trim();
            const matched = CUSTOMERS_DATA.filter(c => 
                c.nama.toLowerCase().includes(q) || (c.no_wa && c.no_wa.includes(q))
            );

            if (matched.length === 0) {
                resultsBox.innerHTML = `
                    <div class="p-3 text-xs text-[#8E7E73] text-center">
                        <p>Pelanggan tidak ditemukan.</p>
                        <button type="button" onclick="toggleNewCustomerForm(true)" class="mt-1 text-xs text-[#D46E15] font-semibold hover:underline">
                            + Daftarkan sebagai Pelanggan Baru
                        </button>
                    </div>
                `;
            } else {
                resultsBox.innerHTML = matched.map(c => `
                    <div onclick="selectCustomerItem(${c.id})" class="px-3.5 py-2.5 hover:bg-[#FFF8F2] cursor-pointer flex items-center justify-between text-xs transition-colors">
                        <div>
                            <p class="font-bold text-[#251A14]">${escapeHtml(c.nama)}</p>
                            <p class="text-[11px] text-[#8E7E73] mt-0.5"><i class="fab fa-whatsapp text-emerald-600 mr-1"></i>${escapeHtml(c.no_wa)}</p>
                        </div>
                        <span class="text-[10px] text-[#D46E15] font-semibold bg-[#D46E15]/10 px-2 py-0.5 rounded">Pilih</span>
                    </div>
                `).join('');
            }
            resultsBox.classList.remove('hidden');
        }

        function handleCustomerSearchFocus() {
            const input = document.getElementById('custSearchInput');
            handleCustomerSearch(input.value);
        }

        function selectCustomerItem(id) {
            const cust = CUSTOMERS_DATA.find(c => c.id == id);
            if (!cust) return;

            document.getElementById('selectedCustomerId').value = cust.id;
            document.getElementById('custSearchInput').value = cust.nama;
            document.getElementById('previewCustName').textContent = cust.nama;
            document.getElementById('previewCustWa').textContent = cust.no_wa;
            document.getElementById('previewCustAddress').textContent = cust.alamat;

            // Hide results
            const resultsBox = document.getElementById('custSearchResults');
            if (resultsBox) resultsBox.classList.add('hidden');

            // Hide new customer fields if open
            cancelNewCustomerForm();
        }

        function toggleNewCustomerForm(forceOpen = false) {
            const fields = document.getElementById('newCustomerFields');
            const searchWrap = document.getElementById('custSearchWrapper');
            const label = document.getElementById('labelToggleNewCust');

            if (forceOpen || fields.classList.contains('hidden')) {
                fields.classList.remove('hidden');
                searchWrap.classList.add('opacity-50', 'pointer-events-none');
                document.getElementById('selectedCustomerId').value = '0';
                label.textContent = 'Batal / Cari Data Yang Ada';
                isNewCustomerMode = true;
                syncNewCustomerPreview();
            } else {
                cancelNewCustomerForm();
            }
        }

        function cancelNewCustomerForm() {
            const fields = document.getElementById('newCustomerFields');
            const searchWrap = document.getElementById('custSearchWrapper');
            const label = document.getElementById('labelToggleNewCust');

            fields.classList.add('hidden');
            searchWrap.classList.remove('opacity-50', 'pointer-events-none');
            label.textContent = '+ Tambah Pelanggan Baru';
            isNewCustomerMode = false;

            // Restore preview to selected customer
            const selId = document.getElementById('selectedCustomerId').value;
            const cust = CUSTOMERS_DATA.find(c => c.id == selId) || CUSTOMERS_DATA[0];
            if (cust) {
                document.getElementById('selectedCustomerId').value = cust.id;
                document.getElementById('previewCustName').textContent = cust.nama;
                document.getElementById('previewCustWa').textContent = cust.no_wa;
                document.getElementById('previewCustAddress').textContent = cust.alamat;
                document.getElementById('custSearchInput').value = cust.nama;
            }
        }

        function syncNewCustomerPreview() {
            const name = (document.getElementById('inputNamaBaru').value || '').trim();
            const wa = (document.getElementById('inputWaBaru').value || '').trim();
            const alamat = (document.getElementById('inputAlamatBaru').value || '').trim();

            document.getElementById('previewCustName').textContent = name || 'Pelanggan Baru';
            document.getElementById('previewCustWa').textContent = wa || '-';
            document.getElementById('previewCustAddress').textContent = alamat || '-';
        }

        // Close dropdown when clicking outside
        document.addEventListener('click', function(e) {
            const wrap = document.getElementById('custSearchWrapper');
            const resultsBox = document.getElementById('custSearchResults');
            if (wrap && resultsBox && !wrap.contains(e.target)) {
                resultsBox.classList.add('hidden');
            }
        });

        // =========================================================================
        // DELIVERY SCHEDULE CALENDAR MODAL
        // =========================================================================
        const SCHEDULE_BY_DATE = {
            20: [
                { id: 'INV-20261020-001', cust: 'Toko Berkah Snack', phone: '0812-9988-1122', items: 'Roti Hajatan Putih (40 box)', time: '09:00 WIB', address: 'Jl. Ahmad Yani No. 12', courier: 'Kurir Obrok 1', status: 'Selesai' }
            ],
            24: [
                { id: 'INV-20261024-002', cust: 'Ibu Ratna Sari', phone: '0813-7766-5544', items: 'Bolu Pisang Mika (10 mika), Roti Manis (30 pcs)', time: '13:30 WIB', address: 'Komplek Permata Hijau C-2', courier: 'Kurir Obrok 1', status: 'Selesai' }
            ],
            26: [
                { id: 'INV-20261026-001', cust: 'Ibu Sari Dewi', phone: '0812-3456-7890', items: 'Roti Hajatan Putih (10 box), Bolu Pisang (2 mika)', time: '14:00 WIB', address: 'Komplek Melati No. 4, Blok B', courier: 'Kurir Obrok 1', status: 'Siap Kirim' },
                { id: 'INV-20261026-002', cust: 'Pak Budi Santoso', phone: '0823-4567-8901', items: 'Roti Hajatan Batik (10 box)', time: '16:30 WIB', address: 'Jl. Kenanga No. 10, Blok C', courier: 'Kurir Obrok 2', status: 'Oven/Proses' }
            ],
            27: [
                { id: 'INV-20261027-001', cust: 'Toko Maju Jaya', phone: '0834-5678-9012', items: 'Bolu Tanpa Pisang Putih (15 box), Roti Kopi (50 pcs)', time: '10:00 WIB', address: 'Jl. Raya Industri No. 20', courier: 'Kurir Obrok 1', status: 'Menunggu' }
            ],
            28: [
                { id: 'INV-20261028-001', cust: 'Warung Pak Haji', phone: '0856-7890-1234', items: 'Roti Bijian Kecil (100 pcs)', time: '11:00 WIB', address: 'Jl. Pesantren No. 3', courier: 'Kurir Obrok 2', status: 'Menunggu' }
            ],
            30: [
                { id: 'INV-20261030-001', cust: 'Ibu Maya Catering', phone: '0821-9988-7766', items: 'Roti Hajatan Mika (20 mika), Bolu Pisang (5 box)', time: '15:00 WIB', address: 'Jl. Ijen No. 15', courier: 'Kurir Obrok 1', status: 'Menunggu' }
            ]
        };

        function openDeliveryScheduleModal(dayNum) {
            const modal = document.getElementById('deliveryScheduleModal');
            if (!modal) return;

            document.getElementById('delivModalTitle').textContent = `Jadwal Pengiriman: ${dayNum} ${document.getElementById('miniCalTitle').textContent}`;
            const body = document.getElementById('delivModalBody');
            const items = SCHEDULE_BY_DATE[dayNum] || [];

            if (items.length === 0) {
                body.innerHTML = `
                    <div class="p-6 text-center text-xs text-[#8E7E73]">
                        <div class="w-12 h-12 rounded-full bg-[#EFE8DE] text-[#D46E15] flex items-center justify-center mx-auto mb-3">
                            <i class="far fa-calendar-times text-xl"></i>
                        </div>
                        <p class="font-bold text-[#251A14]">Belum Ada Pengiriman Terjadwal</p>
                        <p class="mt-1 max-w-xs mx-auto">Tidak ada jadwal pengiriman roti untuk tanggal ${dayNum}. Semua armada kurir stand-by.</p>
                        <button type="button" onclick="closeDeliveryScheduleModal(); openOrderModal();" class="mt-4 craft-btn-primary px-4 py-2 text-xs">
                            + Tambah Pesanan untuk Tanggal Ini
                        </button>
                    </div>
                `;
            } else {
                body.innerHTML = `
                    <div class="flex items-center justify-between pb-2 border-b border-[#E2D7C8] text-xs">
                        <span class="font-semibold text-[#544337]"><i class="fas fa-boxes mr-1 text-[#D46E15]"></i> Total: ${items.length} Pesanan Terjadwal</span>
                        <span class="px-2.5 py-0.5 rounded-full text-[11px] font-semibold bg-[#27AE60]/15 text-[#1B7A43]">Kapasitas Trip Aman</span>
                    </div>
                    <div class="space-y-3">
                        ${items.map(it => `
                            <div class="p-3.5 bg-[#EFE8DE] rounded-xl border border-[#DDD3C5] space-y-2 text-xs">
                                <div class="flex items-center justify-between">
                                    <span class="font-mono font-bold text-[#D46E15]">${escapeHtml(it.id)}</span>
                                    <span class="px-2 py-0.5 rounded-full text-[10px] font-semibold ${it.status === 'Selesai' ? 'bg-[#27AE60]/15 text-[#1B7A43]' : (it.status === 'Siap Kirim' ? 'bg-[#2F80ED]/15 text-[#2F80ED]' : 'bg-[#E2B93B]/20 text-[#886C12]')}">${escapeHtml(it.status)}</span>
                                </div>
                                <div>
                                    <p class="font-bold text-[#251A14]">${escapeHtml(it.cust)} <span class="text-[#8E7E73] font-normal text-[11px]">(${escapeHtml(it.phone)})</span></p>
                                    <p class="text-[#544337] mt-0.5 font-medium">${escapeHtml(it.items)}</p>
                                </div>
                                <div class="pt-2 border-t border-[#DDD3C5] flex items-center justify-between text-[11px] text-[#786E65]">
                                    <span><i class="far fa-clock mr-1"></i>${escapeHtml(it.time)}</span>
                                    <span><i class="fas fa-motorcycle mr-1 text-[#D46E15]"></i>${escapeHtml(it.courier)}</span>
                                </div>
                            </div>
                        `).join('')}
                    </div>
                `;
            }

            modal.classList.remove('hidden');
            document.body.style.overflow = 'hidden';
        }

        function closeDeliveryScheduleModal() {
            const modal = document.getElementById('deliveryScheduleModal');
            if (modal) {
                modal.classList.add('hidden');
                document.body.style.overflow = '';
            }
        }

        function escapeHtml(text) {
            const div = document.createElement('div');
            div.textContent = text || '';
            return div.innerHTML;
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

        // Add Product Row into Modal Table
        let productRowCounter = 2;
        function addOrderProductRow() {
            const tbody = document.getElementById('orderItemsTableBody');
            const rowIndex = productRowCounter++;
            
            // Default product: 3rd in catalog or 1st
            const defaultProd = PRODUCTS_DATA[2] || PRODUCTS_DATA[0] || {
                id_varian: 1, nama: 'Roti Hajatan (9 Pilihan Topping)', kemasan: 'Kardus Putih', harga: 10000, min_order: 1
            };

            const tr = document.createElement('tr');
            tr.className = 'order-product-row';
            tr.setAttribute('data-min-order', defaultProd.min_order || 1);
            tr.setAttribute('data-price', defaultProd.harga || 10000);

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
                        class="w-full bg-white border border-[#E0E0E0] rounded px-2 py-1 text-xs font-semibold text-[#1C1C1C] focus:border-[#D46E15] focus:outline-none mb-1">
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
                        class="item-qty-input w-16 px-2 py-1 border border-[#E0E0E0] text-[#1C1C1C] font-semibold rounded text-center focus:outline-none focus:border-[#D46E15] transition-colors">
                </td>
                <td class="px-3.5 py-3 text-[#544337]">
                    <span class="item-price-text">Rp ${new Intl.NumberFormat('id-ID').format(defaultProd.harga)}</span>
                    <input type="hidden" name="items[${rowIndex}][harga]" value="${defaultProd.harga}" class="item-price-input">
                </td>
                <td class="px-3.5 py-3 font-bold text-[#1C1C1C] item-subtotal-text">Rp ${new Intl.NumberFormat('id-ID').format(defaultProd.harga)}</td>
                <td class="px-3 py-3 text-center">
                    <button type="button" onclick="removeOrderProductRow(this)" class="text-[#8E7E73] hover:text-[#EB5757] p-1 transition-colors" title="Hapus Item">
                        <i class="far fa-trash-alt text-xs"></i>
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
                    qtyInput.className = 'item-qty-input w-16 px-2 py-1 border border-[#E0E0E0] text-[#1C1C1C] font-semibold rounded text-center focus:outline-none focus:border-[#D46E15] transition-colors';
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
                lockIcon.innerHTML = '<i class="fas fa-lock text-xs mr-1"></i>';
                footerWarning.classList.remove('hidden');
            } else {
                alertBox.classList.add('hidden');
                submitBtn.disabled = false;
                submitBtn.className = 'bg-[#D46E15] hover:bg-[#B85207] text-white cursor-pointer text-xs font-semibold px-5 py-2.5 rounded-lg flex items-center gap-1.5 transition-all shadow-button-orange active:scale-95';
                lockIcon.innerHTML = '<i class="fas fa-check text-xs mr-1"></i>';
                footerWarning.classList.add('hidden');
            }
        }

        // Payment status segmented button
        function selectPaymentStatus(status, btn) {
            document.getElementById('selectedPaymentStatus').value = status;
            const buttons = document.querySelectorAll('.pay-status-btn');
            buttons.forEach(b => {
                b.className = 'py-1.5 px-2 rounded-lg text-xs font-semibold bg-white border border-[#DDD3C5] text-[#3E2B21] hover:bg-[#F5F1EB] transition-colors pay-status-btn';
            });
            btn.className = 'py-1.5 px-2 rounded-lg text-xs font-semibold bg-[#3E2B21] text-white shadow-2xs transition-colors pay-status-btn';
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

        // Dropdown Toggle Handlers (Header)
        function toggleNotificationDropdown() {
            const d = document.getElementById('notificationDropdown');
            const u = document.getElementById('userDropdown');
            if (u) u.classList.add('hidden');
            if (d) d.classList.toggle('hidden');
        }

        function toggleUserDropdown() {
            const u = document.getElementById('userDropdown');
            const d = document.getElementById('notificationDropdown');
            if (d) d.classList.add('hidden');
            if (u) u.classList.toggle('hidden');
        }

        // Close Dropdowns on Click Outside
        document.addEventListener('click', function(e) {
            const notifBtn = e.target.closest('[onclick="toggleNotificationDropdown()"]');
            const notifDrop = e.target.closest('#notificationDropdown');
            if (!notifBtn && !notifDrop) {
                const d = document.getElementById('notificationDropdown');
                if (d) d.classList.add('hidden');
            }

            const userBtn = e.target.closest('[onclick="toggleUserDropdown()"]');
            const userDrop = e.target.closest('#userDropdown');
            if (!userBtn && !userDrop) {
                const u = document.getElementById('userDropdown');
                if (u) u.classList.add('hidden');
            }
        });

        // Mini Calendar Dynamic Navigation
        let calCurrentDate = new Date();
        const indMonths = ['Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni', 'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember'];

        function renderMiniCalendar() {
            const year = calCurrentDate.getFullYear();
            const month = calCurrentDate.getMonth();
            const titleEl = document.getElementById('miniCalTitle');
            if (titleEl) {
                titleEl.textContent = `${indMonths[month]} ${year}`;
            }

            const grid = document.getElementById('miniCalGrid');
            if (!grid) return;

            const firstDay = new Date(year, month, 1).getDay();
            const totalDays = new Date(year, month + 1, 0).getDate();
            const prevMonthDays = new Date(year, month, 0).getDate();

            const today = new Date();
            const isThisMonth = (today.getFullYear() === year && today.getMonth() === month);

            let html = '';
            for (let i = firstDay - 1; i >= 0; i--) {
                html += `<div class="text-[#828282]/40 py-1">${prevMonthDays - i}</div>`;
            }
            for (let day = 1; day <= totalDays; day++) {
                const isToday = isThisMonth && today.getDate() === day;
                const hasDelivery = [2, 5, 9, 14, 18, 22, 26, 28].includes(day);

                if (isToday) {
                    html += `<div class="flex flex-col items-center justify-center">
                        <span class="w-6 h-6 rounded-full bg-[#3E2B21] text-[#FAF8F5] font-bold flex items-center justify-center shadow-xs">${day}</span>
                        <span class="w-1.5 h-1.5 rounded-full bg-[#D46E15] mt-0.5"></span>
                    </div>`;
                } else if (hasDelivery) {
                    html += `<div class="flex flex-col items-center justify-center">
                        <span class="text-[#251A14] font-medium">${day}</span>
                        <span class="w-1.5 h-1.5 rounded-full bg-[#D46E15] mt-0.5"></span>
                    </div>`;
                } else {
                    html += `<div class="text-[#1B1C1B] font-medium py-1">${day}</div>`;
                }
            }
            const currentCells = firstDay + totalDays;
            const remainingCells = (currentCells <= 35 ? 35 : 42) - currentCells;
            for (let nextDay = 1; nextDay <= remainingCells; nextDay++) {
                html += `<div class="text-[#828282]/40 py-1">${nextDay}</div>`;
            }

            grid.innerHTML = html;
        }

        function prevMiniMonth() {
            calCurrentDate.setMonth(calCurrentDate.getMonth() - 1);
            renderMiniCalendar();
        }

        function nextMiniMonth() {
            calCurrentDate.setMonth(calCurrentDate.getMonth() + 1);
            renderMiniCalendar();
        }
    </script>
</body>
</html>
