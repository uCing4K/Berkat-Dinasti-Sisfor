<?php
session_start();

// Proteksi Autentikasi: Hanya user yang sudah login yang diizinkan mengakses halaman Pesanan
if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true) {
    header("Location: login.php");
    exit();
}

// Inisialisasi Database Connection (PDO) & Handler Backend
$db_connected = false;
$pdo = null;
$configFile = __DIR__ . "/../config/config.php";
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

// Inisialisasi Session Overrides jika belum ada
if (!isset($_SESSION['order_status_overrides'])) {
    $_SESSION['order_status_overrides'] = [];
}

// Inisialisasi Kitchen Notes di session
if (!isset($_SESSION['kitchen_notes'])) {
    $_SESSION['kitchen_notes'] = [
        [
            'id' => 1,
            'type' => 'priority',
            'tag' => '! Prioritas #ORD-089',
            'tag_class' => 'text-[#EB5757]',
            'time' => '10:15',
            'text' => 'Roti hajatan 10 box butuh dikirim jam 15:00. Dahulukan baking & packing kardus putih terlebih dahulu.'
        ],
        [
            'id' => 2,
            'type' => 'order',
            'tag' => '🕒 Catatan #ORD-088',
            'tag_class' => 'text-[#D97706]',
            'time' => '09:30',
            'text' => 'Pak Budi minta kardus roti hajatan batik diikat tali rafia rapat saat diambil sore.'
        ],
        [
            'id' => 3,
            'type' => 'stock',
            'tag' => '📋 Catatan Stok Bahan',
            'tag_class' => 'text-[#544337]',
            'time' => '08:00',
            'text' => 'Bolu pisang mika sisa 4 box untuk persiapan pesanan besok pagi.'
        ]
    ];
}

// POST Action Handlers (AJAX & Form)
$toast_message = $_SESSION['flash_msg'] ?? null;
unset($_SESSION['flash_msg']);
$error_message = $_SESSION['flash_error'] ?? null;
unset($_SESSION['flash_error']);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    
    if ($action === 'update_order_status') {
        $order_id = $_POST['order_id'] ?? '';
        $new_status = $_POST['status'] ?? '';
        if ($order_id && $new_status) {
            $_SESSION['order_status_overrides'][$order_id] = $new_status;
            
            if ($db_connected && $pdo) {
                try {
                    $stmtU = $pdo->prepare("UPDATE pesanan SET status = ? WHERE no_invoice = ? OR id_pesanan = ?");
                    $stmtU->execute([$new_status === 'menunggu' ? 'pending' : $new_status, $order_id, $order_id]);
                } catch (Exception $eU) {}
            }
            
            // Jika AJAX
            if (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest') {
                header('Content-Type: application/json');
                echo json_encode(['success' => true, 'order_id' => $order_id, 'status' => $new_status]);
                exit();
            }
            $toast_message = "Status pesanan {$order_id} berhasil diperbarui menjadi " . ucfirst($new_status);
        }
    } elseif ($action === 'batch_siap_kirim') {
        $_SESSION['order_status_overrides']['ORD-089'] = 'kirim';
        $_SESSION['order_status_overrides']['ORD-088'] = 'kirim';
        $_SESSION['order_status_overrides']['ORD-087'] = 'kirim';
        $toast_message = "Semua pesanan aktif berhasil dikonfirmasi Siap Kirim!";
    } elseif ($action === 'add_kitchen_note') {
        $tag = trim($_POST['note_tag'] ?? 'Catatan Baru');
        $text = trim($_POST['note_text'] ?? '');
        if ($text) {
            array_unshift($_SESSION['kitchen_notes'], [
                'id' => time(),
                'type' => 'order',
                'tag' => '📝 ' . htmlspecialchars($tag),
                'tag_class' => 'text-[#924C00]',
                'time' => date('H:i'),
                'text' => htmlspecialchars($text)
            ]);
            $toast_message = "Catatan dapur baru berhasil ditambahkan!";
        }
    } elseif ($action === 'add_order' || $action === 'tambah_pesanan') {
        $id_pelanggan = intval($_POST['id_pelanggan'] ?? 0);
        $nama_pelanggan_baru = trim($_POST['nama_pelanggan_baru'] ?? '');
        $no_wa_baru = trim($_POST['no_wa_baru'] ?? '');
        $alamat_baru = trim($_POST['alamat_baru'] ?? '');

        $custName = !empty($nama_pelanggan_baru) ? $nama_pelanggan_baru : trim($_POST['customer_name'] ?? 'Pelanggan');
        $custWa = !empty($no_wa_baru) ? $no_wa_baru : '0812-3456-7890';
        $custAddress = !empty($alamat_baru) ? $alamat_baru : trim($_POST['destination'] ?? 'Ambil di Toko');

        $raw_items = $_POST['items'] ?? [];
        $processed_items = [];
        $calculated_total = 0;

        if (!empty($raw_items) && is_array($raw_items)) {
            foreach ($raw_items as $it) {
                $nama_it = trim($it['nama'] ?? '');
                $qty_it = max(0, intval($it['qty'] ?? 0));
                $hrg_it = max(0, floatval($it['harga'] ?? 0));
                $kemasan_it = trim($it['kemasan'] ?? '');
                $id_var = intval($it['id_varian'] ?? 1);
                if ($qty_it > 0) {
                    $sub = $qty_it * $hrg_it;
                    $calculated_total += $sub;
                    $processed_items[] = [
                        'nama' => $nama_it ?: 'Roti Pesanan',
                        'id_varian' => $id_var,
                        'kemasan' => $kemasan_it,
                        'qty' => $qty_it,
                        'harga' => $hrg_it,
                        'subtotal' => $sub
                    ];
                }
            }
        }

        // Fallback untuk backward compatibility modal lama
        if (empty($processed_items)) {
            $qty1 = max(0, intval($_POST['qty_item_1'] ?? 0));
            $qty2 = max(0, intval($_POST['qty_item_2'] ?? 0));
            if ($qty1 > 0) {
                $processed_items[] = [
                    'nama' => 'Roti Hajatan (9 Pilihan Topping)',
                    'id_varian' => 1,
                    'kemasan' => 'Kardus Putih',
                    'qty' => $qty1,
                    'harga' => 10000,
                    'subtotal' => $qty1 * 10000
                ];
                $calculated_total += ($qty1 * 10000);
            }
            if ($qty2 > 0) {
                $processed_items[] = [
                    'nama' => 'Bolu Pisang Premium',
                    'id_varian' => 7,
                    'kemasan' => 'Mika',
                    'qty' => $qty2,
                    'harga' => 22000,
                    'subtotal' => $qty2 * 22000
                ];
                $calculated_total += ($qty2 * 22000);
            }
        }

        if (empty($processed_items)) {
            $_SESSION['flash_error'] = "Gagal menyimpan pesanan: Belum ada produk yang dipilih! Silakan pilih minimal 1 produk dari katalog.";
            header("Location: pesanan.php?view=tambah_pesanan");
            exit();
        }

        $grand_total = intval($_POST['grand_total'] ?? $calculated_total);
        if ($grand_total <= 0) $grand_total = $calculated_total;

        $itemsDesc = trim($_POST['items_desc'] ?? '');
        if (empty($itemsDesc)) {
            $itemsDesc = implode(', ', array_map(fn($p) => "{$p['nama']} ({$p['qty']} pcs)", $processed_items));
        }

        $tglKirim = !empty($_POST['tgl_kirim']) ? $_POST['tgl_kirim'] : (!empty($_POST['delivery_date']) ? $_POST['delivery_date'] : date('Y-m-d'));
        
        // Validasi Tanggal Pre-Order (PO): Tidak boleh memilih tanggal lampau / mundur ke belakang
        $today = date('Y-m-d');
        if (strtotime($tglKirim) < strtotime($today)) {
            $_SESSION['flash_error'] = "Tanggal pengiriman (" . date('d M Y', strtotime($tglKirim)) . ") tidak valid! Sistem roti Berkat Dinasti berbasis Pre-Order (PO) sehingga tanggal selesai/kirim tidak boleh tanggal yang sudah lewat (minimal hari ini).";
            header("Location: pesanan.php?view=tambah_pesanan");
            exit();
        }

        $waktuKirim = !empty($_POST['waktu_kirim']) ? $_POST['waktu_kirim'] : (!empty($_POST['delivery_time']) ? $_POST['delivery_time'] : '09:00');
        $waktuKirimSql = strlen($waktuKirim) === 5 ? ($waktuKirim . ':00') : $waktuKirim;

        $catatan = trim($_POST['catatan'] ?? $_POST['notes'] ?? '');
        $payStatus = trim($_POST['status_bayar'] ?? $_POST['payment_status'] ?? 'belum_bayar');
        $dbStatusBayar = match(strtolower(str_replace(' ', '_', $payStatus))) {
            'lunas' => 'lunas',
            'hutang' => 'hutang',
            'dp' => 'dp',
            default => 'belum_bayar'
        };

        $metodeBayar = in_array(strtolower($_POST['metode_bayar'] ?? $_POST['payment_method'] ?? ''), ['cash', 'transfer']) ? strtolower($_POST['metode_bayar'] ?? $_POST['payment_method']) : 'cash';

        $bayarClass = match($dbStatusBayar) {
            'lunas' => 'bg-[#27AE60]/15 text-[#1B7A43]',
            'hutang', 'dp' => 'bg-[#E2B93B]/20 text-[#886C12]',
            default => 'bg-[#828282]/15 text-[#544337]'
        };

        // Penomoran invoice otomatis standar: INV-YYYYMMDD-XXXX (4 digit urut per hari)
        $dateCode = date('Ymd');
        $seq = 1;
        if ($db_connected && $pdo) {
            try {
                $stmtInv = $pdo->prepare("SELECT COUNT(*) FROM pesanan WHERE DATE(tgl_pesan) = CURDATE() OR no_invoice LIKE ?");
                $stmtInv->execute(["INV-{$dateCode}-%"]);
                $seq = intval($stmtInv->fetchColumn()) + 1;
            } catch (Exception $e) {}
        }
        $no_invoice = "INV-{$dateCode}-" . str_pad($seq, 4, '0', STR_PAD_LEFT);

        if ($db_connected && $pdo) {
            try {
                $pdo->beginTransaction();

                if ($id_pelanggan <= 0 && !empty($nama_pelanggan_baru)) {
                    $stmtC = $pdo->prepare("INSERT INTO pelanggan (nama, no_wa, alamat, tipe) VALUES (?, ?, ?, 'retail')");
                    $stmtC->execute([$nama_pelanggan_baru, $no_wa_baru, $alamat_baru]);
                    $id_pelanggan = $pdo->lastInsertId();
                    $custName = $nama_pelanggan_baru;
                    $custWa = $no_wa_baru;
                    $custAddress = $alamat_baru;
                } elseif ($id_pelanggan > 0) {
                    $stmtC = $pdo->prepare("SELECT nama, no_wa, alamat FROM pelanggan WHERE id_pelanggan = ?");
                    $stmtC->execute([$id_pelanggan]);
                    $cRow = $stmtC->fetch();
                    if ($cRow) {
                        $custName = $cRow['nama'];
                        $custWa = $cRow['no_wa'];
                        $custAddress = $cRow['alamat'];
                    }
                } else {
                    $stmtC = $pdo->query("SELECT id_pelanggan, nama, no_wa, alamat FROM pelanggan LIMIT 1");
                    $cRow = $stmtC->fetch();
                    if ($cRow) {
                        $id_pelanggan = $cRow['id_pelanggan'];
                        $custName = $cRow['nama'];
                        $custWa = $cRow['no_wa'];
                        $custAddress = $cRow['alamat'];
                    } else {
                        $id_pelanggan = 1;
                    }
                }

                $stmtOrder = $pdo->prepare("
                    INSERT INTO pesanan (id_pelanggan, no_invoice, tgl_pesan, tgl_kirim, waktu_kirim, total_harga, grand_total, status, status_bayar, metode_bayar, catatan)
                    VALUES (?, ?, CURDATE(), ?, ?, ?, ?, 'pending', ?, ?, ?)
                ");
                $stmtOrder->execute([
                    $id_pelanggan,
                    $no_invoice,
                    $tglKirim,
                    $waktuKirimSql,
                    $grand_total,
                    $grand_total,
                    $dbStatusBayar,
                    $metodeBayar,
                    $catatan
                ]);
                $newOrderId = $pdo->lastInsertId();

                $stmtDetail = $pdo->prepare("
                    INSERT INTO detail_pesanan (id_pesanan, id_varian, qty, harga_satuan, subtotal)
                    VALUES (?, ?, ?, ?, ?)
                ");
                foreach ($processed_items as $pi) {
                    $stmtDetail->execute([
                        $newOrderId,
                        $pi['id_varian'],
                        $pi['qty'],
                        $pi['harga'],
                        $pi['subtotal']
                    ]);
                }

                try {
                    $stmtLog = $pdo->prepare("INSERT INTO tracking_log (id_pesanan, status, keterangan) VALUES (?, 'pending', ?)");
                    $stmtLog->execute([$newOrderId, "Pesanan #{$no_invoice} berhasil dibuat dari Pesanan"]);
                } catch (Exception $eL) {}

                $pdo->commit();
            } catch (Exception $e) {
                if ($pdo->inTransaction()) $pdo->rollBack();
            }
        }

        $isToday = ($tglKirim == date('Y-m-d'));

        // Push to custom_orders for pesanan.php (Kanban & Kalender)
        if (!isset($_SESSION['custom_orders'])) {
            $_SESSION['custom_orders'] = [];
        }
        array_unshift($_SESSION['custom_orders'], [
            'id' => $no_invoice,
            'customer' => $custName,
            'phone' => $custWa,
            'items' => $itemsDesc,
            'destination' => $custAddress,
            'time' => 'Pukul ' . substr($waktuKirim, 0, 5),
            'date' => $tglKirim,
            'default_status' => 'menunggu',
            'is_urgent' => $isToday,
            'status_bayar' => ucfirst(str_replace('_', ' ', $dbStatusBayar)),
            'bayar_class' => $bayarClass,
            'courier_info' => 'Kurir Toko (Siap Antar ' . substr($waktuKirim, 0, 5) . ')',
            'total' => $grand_total
        ]);

        // Push to session_orders for beranda.php (Antrean & Pengiriman Hari Ini)
        if (!isset($_SESSION['session_orders'])) {
            $_SESSION['session_orders'] = [];
        }
        $deadlineFormatted = $isToday ? ('Hari Ini, ' . substr($waktuKirim, 0, 5)) : (date('d M Y, ', strtotime($tglKirim)) . substr($waktuKirim, 0, 5));
        array_unshift($_SESSION['session_orders'], [
            'id' => $no_invoice,
            'customer' => $custName,
            'product' => $itemsDesc,
            'deadline' => $deadlineFormatted,
            'is_urgent' => $isToday,
            'status' => 'pending',
            'payment_status' => ucfirst(str_replace('_', ' ', $dbStatusBayar)),
            'payment_class' => $bayarClass,
            'total' => 'Rp ' . number_format($grand_total, 0, ',', '.'),
            'no_wa' => $custWa,
            'alamat' => $custAddress
        ]);

        $_SESSION['flash_msg'] = "Pesanan baru #{$no_invoice} untuk {$custName} berhasil dibuat dan otomatis masuk ke antrean!";
        header("Location: pesanan.php");
        exit();
    }
}

// Master Pelanggan untuk Select & Autocomplete Pesanan Baru
$customers_for_select = [
    ['id' => 1, 'nama' => 'Ibu Sari Dewi', 'no_wa' => '0812-3456-7890', 'alamat' => 'Komplek Melati No. 4, Blok B'],
    ['id' => 2, 'nama' => 'Pak Budi Santoso', 'no_wa' => '0823-4567-8901', 'alamat' => 'Jl. Kenanga No. 10, Blok C'],
    ['id' => 3, 'nama' => 'Toko Maju Jaya', 'no_wa' => '0834-5678-9012', 'alamat' => 'Jl. Raya Industri No. 20, Ruko Sentra'],
    ['id' => 4, 'nama' => 'Warung Pak Haji', 'no_wa' => '0856-7890-1234', 'alamat' => 'Jl. Pesantren No. 3'],
    ['id' => 5, 'nama' => 'Ibu Ratna Sari', 'no_wa' => '0812-9988-7766', 'alamat' => 'Jl. Pandanwangi No. 12']
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

// Master Produk untuk Order Items Dinamis
$products_for_select = [
    ['id' => 1, 'id_varian' => 1, 'nama' => 'Roti Hajatan (9 Pilihan Topping)', 'kemasan' => 'Kardus Putih', 'harga' => 10000, 'min_order' => 1, 'kategori' => 'Roti Hajatan'],
    ['id' => 1, 'id_varian' => 2, 'nama' => 'Roti Hajatan (9 Pilihan Topping)', 'kemasan' => 'Kardus Coklat', 'harga' => 10000, 'min_order' => 1, 'kategori' => 'Roti Hajatan'],
    ['id' => 1, 'id_varian' => 3, 'nama' => 'Roti Hajatan (9 Pilihan Topping)', 'kemasan' => 'Kardus Batik', 'harga' => 11000, 'min_order' => 1, 'kategori' => 'Roti Hajatan'],
    ['id' => 1, 'id_varian' => 4, 'nama' => 'Roti Hajatan (9 Pilihan Topping)', 'kemasan' => 'Mika', 'harga' => 11000, 'min_order' => 1, 'kategori' => 'Roti Hajatan'],
    ['id' => 2, 'id_varian' => 6, 'nama' => 'Bolu Pisang Premium', 'kemasan' => 'Kardus Coklat', 'harga' => 21000, 'min_order' => 1, 'kategori' => 'Bolu Premium'],
    ['id' => 2, 'id_varian' => 7, 'nama' => 'Bolu Pisang Premium', 'kemasan' => 'Mika', 'harga' => 22000, 'min_order' => 1, 'kategori' => 'Bolu Premium'],
    ['id' => 3, 'id_varian' => 8, 'nama' => 'Bolu Tanpa Pisang Premium', 'kemasan' => 'Kardus Putih', 'harga' => 19000, 'min_order' => 1, 'kategori' => 'Bolu Premium'],
    ['id' => 3, 'id_varian' => 9, 'nama' => 'Bolu Tanpa Pisang Premium', 'kemasan' => 'Mika', 'harga' => 20000, 'min_order' => 1, 'kategori' => 'Bolu Premium'],
    ['id' => 4, 'id_varian' => 10, 'nama' => 'Roti Kopi', 'kemasan' => 'Satuan', 'harga' => 4000, 'min_order' => 50, 'kategori' => 'Roti Manis'],
    ['id' => 5, 'id_varian' => 11, 'nama' => 'Roti Bijian Besar', 'kemasan' => 'Satuan', 'harga' => 4000, 'min_order' => 20, 'kategori' => 'Roti Manis'],
    ['id' => 6, 'id_varian' => 5, 'nama' => 'Roti Bijian Kecil', 'kemasan' => 'Satuan', 'harga' => 3000, 'min_order' => 50, 'kategori' => 'Roti Manis']
];

$categories_for_select = ['Semua', 'Roti Hajatan', 'Bolu Premium', 'Roti Manis'];

if ($db_connected && $pdo) {
    try {
        $stmtP = $pdo->query("
            SELECT v.id_varian, v.id_produk as id, p.nama_produk as nama, v.nama_varian as kemasan, v.harga, v.min_order, COALESCE(k.nama_kategori, 'Roti Manis') as kategori
            FROM varian_produk v 
            JOIN produk p ON v.id_produk = p.id_produk 
            LEFT JOIN kategori k ON p.id_kategori = k.id_kategori
            WHERE p.status = 'tersedia' 
            ORDER BY k.id_kategori ASC, p.nama_produk ASC, v.harga ASC
        ");
        $dbProds = $stmtP->fetchAll();
        if (!empty($dbProds)) {
            $products_for_select = $dbProds;
        }

        $stmtK = $pdo->query("SELECT nama_kategori FROM kategori ORDER BY id_kategori ASC");
        $dbCats = $stmtK->fetchAll(PDO::FETCH_COLUMN);
        if (!empty($dbCats)) {
            $categories_for_select = array_merge(['Semua'], $dbCats);
        }
    } catch (Exception $e) {}
}

$is_tambah_view = (isset($_GET['view']) && $_GET['view'] === 'tambah_pesanan') || (isset($_GET['action']) && $_GET['action'] === 'tambah_pesanan');

// Data Pesanan Kanban & Kalender
$active_tab = $_GET['tab'] ?? 'kanban';

// Master Dataset Pesanan
$master_orders = [
    [
        'id' => 'ORD-089',
        'customer' => 'Ibu Sari',
        'phone' => '0812-3456-7890',
        'items' => 'Roti Hajatan Putih (10 box), Bolu Pisang Mika (2 mika)',
        'destination' => 'Dikirim ke Komplek Melati No. 4',
        'time' => 'Hari Ini, 15:00',
        'date' => date('Y-m-d'),
        'default_status' => 'menunggu', // menunggu, proses, kirim, selesai
        'is_urgent' => true,
        'status_bayar' => 'Belum Bayar',
        'bayar_class' => 'bg-[#828282]/15 text-[#544337]',
        'courier_info' => 'Kurir Toko (Siap Antar 14:00)',
        'total' => 144000
    ],
    [
        'id' => 'ORD-088',
        'customer' => 'Pak Budi',
        'phone' => '0823-4567-8901',
        'items' => 'Roti Hajatan Kardus Batik (10 box)',
        'destination' => 'Ambil Langsung Sore Hari',
        'time' => 'Hari Ini, 16:30',
        'date' => date('Y-m-d'),
        'default_status' => 'proses',
        'is_urgent' => false,
        'status_bayar' => 'Hutang',
        'bayar_class' => 'bg-[#E2B93B]/20 text-[#886C12]',
        'courier_info' => 'Ambil Sendiri di Toko',
        'total' => 110000
    ],
    [
        'id' => 'ORD-087',
        'customer' => 'Toko Maju',
        'phone' => '0834-5678-9012',
        'items' => 'Bolu Tanpa Pisang Putih (15 box), Roti Kopi (50 pcs)',
        'destination' => 'Kirim via Driver Roda Tiga',
        'time' => 'Hari Ini, 10:00',
        'date' => date('Y-m-d'),
        'default_status' => 'kirim',
        'is_urgent' => false,
        'status_bayar' => 'Lunas',
        'bayar_class' => 'bg-[#27AE60]/15 text-[#1B7A43]',
        'courier_info' => 'Driver Roda Tiga',
        'total' => 485000
    ],
    [
        'id' => 'ORD-084',
        'customer' => 'Bu Rina',
        'phone' => '0815-6789-0123',
        'items' => 'Bolu Pisang Mika (2 mika), Roti Bijian Besar (20 pcs)',
        'destination' => 'Ambil di Toko',
        'courier_info' => 'Ambil di Toko (14:00)',
        'time' => 'Kemarin, 14:00',
        'date' => date('Y-m-d', strtotime('-1 day')),
        'default_status' => 'selesai',
        'is_urgent' => false,
        'status_bayar' => 'Lunas',
        'bayar_class' => 'bg-[#27AE60]/15 text-[#1B7A43]',
        'total' => 124000
    ],
    [
        'id' => 'ORD-081',
        'customer' => 'Warung Pak Haji',
        'phone' => '0819-0123-4567',
        'items' => 'Roti Bijian Kecil Satuan (100 pcs)',
        'destination' => 'Jl. Pasar Baru No. 12',
        'courier_info' => 'Kurir Obrok Motor',
        'time' => 'Kemarin, 11:00',
        'date' => date('Y-m-d', strtotime('-1 day')),
        'default_status' => 'selesai',
        'is_urgent' => false,
        'status_bayar' => 'Lunas',
        'bayar_class' => 'bg-[#27AE60]/15 text-[#1B7A43]',
        'total' => 300000
    ],
    [
        'id' => 'ORD-080',
        'customer' => 'Ibu Maya',
        'phone' => '0821-9988-7766',
        'items' => 'Roti Hajatan Mika (10 mika), Bolu Pisang Kardus Coklat (2 box)',
        'destination' => 'Kirim ke Jl. Ijen No. 15',
        'courier_info' => 'Kurir Toko',
        'time' => 'Hari Ini, 17:00',
        'date' => date('Y-m-d'),
        'default_status' => 'menunggu',
        'is_urgent' => false,
        'status_bayar' => 'Belum Bayar',
        'bayar_class' => 'bg-[#828282]/15 text-[#544337]',
        'total' => 152000
    ]
];

// Ambil pesanan dari MySQL database jika terhubung
$db_orders = [];
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
            LIMIT 30
        ");
        $rows = $stmtOrders->fetchAll();
        foreach ($rows as $r) {
            $is_urgent = ($r['tgl_kirim'] == date('Y-m-d'));
            $wTime = !empty($r['waktu_kirim']) ? substr($r['waktu_kirim'], 0, 5) : '14:00';
            $tStr = $is_urgent ? 'Hari Ini, ' . $wTime : 'Pukul ' . $wTime;
            $payStat = strtolower($r['status_bayar'] ?? 'belum_bayar');
            $bayarCls = match($payStat) {
                'lunas' => 'bg-[#27AE60]/15 text-[#1B7A43]',
                'hutang', 'dp' => 'bg-[#E2B93B]/20 text-[#886C12]',
                default => 'bg-[#828282]/15 text-[#544337]'
            };
            $st = match(strtolower($r['status'] ?? 'pending')) {
                'proses' => 'proses',
                'kirim' => 'kirim',
                'selesai' => 'selesai',
                default => 'menunggu'
            };
            $db_orders[] = [
                'id' => $r['no_invoice'],
                'customer' => $r['nama_pelanggan'] ?? 'Pelanggan Walk-in',
                'phone' => $r['no_wa'] ?? '-',
                'items' => !empty($r['item_summary']) ? $r['item_summary'] : 'Pesanan Bakery Berkat Dinasti',
                'destination' => $r['alamat'] ?? 'Ambil di Toko',
                'time' => $tStr,
                'date' => $r['tgl_kirim'] ?? $r['tgl_pesan'],
                'default_status' => $st,
                'is_urgent' => $is_urgent,
                'status_bayar' => ucfirst(str_replace('_', ' ', $payStat)),
                'bayar_class' => $bayarCls,
                'courier_info' => 'Kurir Toko (Siap Antar ' . $wTime . ')',
                'total' => floatval($r['grand_total'])
            ];
        }
    } catch (Exception $e) {}
}

// Gabungkan dataset secara terpadu: session custom, session beranda, database, dan default demo
$seen_ids = [];
$unified_master_orders = [];

// 1. Session custom orders (paling baru di atas)
if (!empty($_SESSION['custom_orders'])) {
    foreach ($_SESSION['custom_orders'] as $co) {
        if (!in_array($co['id'], $seen_ids)) {
            $seen_ids[] = $co['id'];
            $unified_master_orders[] = $co;
        }
    }
}

// 2. Session orders dari Beranda
if (!empty($_SESSION['session_orders'])) {
    foreach ($_SESSION['session_orders'] as $so) {
        if (!in_array($so['id'], $seen_ids)) {
            $seen_ids[] = $so['id'];
            $unified_master_orders[] = [
                'id' => $so['id'],
                'customer' => $so['customer'] ?? 'Pelanggan',
                'phone' => $so['no_wa'] ?? '-',
                'items' => $so['product'] ?? 'Pesanan Bakery',
                'destination' => $so['alamat'] ?? 'Ambil di Toko',
                'time' => $so['deadline'] ?? '14:00',
                'date' => date('Y-m-d'),
                'default_status' => ($so['status'] ?? 'pending') === 'pending' ? 'menunggu' : ($so['status'] ?? 'menunggu'),
                'is_urgent' => !empty($so['is_urgent']),
                'status_bayar' => $so['payment_status'] ?? 'Belum Bayar',
                'bayar_class' => $so['payment_class'] ?? 'bg-[#828282]/15 text-[#544337]',
                'courier_info' => 'Kurir Toko',
                'total' => intval(preg_replace('/[^0-9]/', '', $so['total'] ?? '0'))
            ];
        }
    }
}

// 3. Database orders
foreach ($db_orders as $dbo) {
    if (!in_array($dbo['id'], $seen_ids)) {
        $seen_ids[] = $dbo['id'];
        $unified_master_orders[] = $dbo;
    }
}

// 4. Default dataset mock
foreach ($master_orders as $dmo) {
    if (!in_array($dmo['id'], $seen_ids)) {
        $seen_ids[] = $dmo['id'];
        $unified_master_orders[] = $dmo;
    }
}

$master_orders = $unified_master_orders;

// Terapkan session status override
$orders_by_status = [
    'menunggu' => [],
    'proses' => [],
    'kirim' => [],
    'selesai' => []
];

foreach ($master_orders as &$ord) {
    $currentStatus = $_SESSION['order_status_overrides'][$ord['id']] ?? ($ord['default_status'] ?? $ord['status'] ?? 'menunggu');
    $ord['current_status'] = $currentStatus;
    if (isset($orders_by_status[$currentStatus])) {
        $orders_by_status[$currentStatus][] = $ord;
    } else {
        $orders_by_status['menunggu'][] = $ord;
    }
}
unset($ord);

// Total dan Omset
$totalOrdersCount = count($master_orders);
$totalMenungguCount = count($orders_by_status['menunggu']) + count($orders_by_status['proses']) + count($orders_by_status['kirim']);
$totalSelesaiCount = count($orders_by_status['selesai']);
$totalOmset = 0;
foreach ($master_orders as $o) {
    $totalOmset += $o['total'] ?? 0;
}

// Kitchen notes dari session
$kitchen_notes = $_SESSION['kitchen_notes'];

// Helper Tanggal Dinamis Bahasa Indonesia
$daysIndo = ['Sunday' => 'Minggu', 'Monday' => 'Senin', 'Tuesday' => 'Selasa', 'Wednesday' => 'Rabu', 'Thursday' => 'Kamis', 'Friday' => 'Jumat', 'Saturday' => 'Sabtu'];
$monthsIndo = ['01' => 'Jan', '02' => 'Feb', '03' => 'Mar', '04' => 'Apr', '05' => 'Mei', '06' => 'Jun', '07' => 'Jul', '08' => 'Agu', '09' => 'Sep', '10' => 'Okt', '11' => 'Nov', '12' => 'Des'];
$monthsIndoFull = ['01' => 'Januari', '02' => 'Februari', '03' => 'Maret', '04' => 'April', '05' => 'Mei', '06' => 'Juni', '07' => 'Juli', '08' => 'Agustus', '09' => 'September', '10' => 'Oktober', '11' => 'November', '12' => 'Desember'];

$dayName = $daysIndo[date('l')] ?? date('l');
$dayNum = date('d');
$monthNum = date('m');
$yearNum = date('Y');
$todayFormatted = $dayName . ", " . $dayNum . " " . ($monthsIndo[$monthNum] ?? date('M')) . " " . $yearNum;
$currentMonthIndo = ($monthsIndoFull[$monthNum] ?? date('F')) . " " . $yearNum;
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pesanan - Berkat Dinasti</title>
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

        .kanban-column.drag-over {
            background-color: #EFE8DE !important;
            border: 2px dashed #D46E15 !important;
        }

        .craft-scrollbar::-webkit-scrollbar {
            width: 5px;
        }
        .craft-scrollbar::-webkit-scrollbar-track {
            background: transparent;
        }
        .craft-scrollbar::-webkit-scrollbar-thumb {
            background: #DDD3C5;
            border-radius: 9999px;
        }
        .craft-scrollbar::-webkit-scrollbar-thumb:hover {
            background: #D46E15;
        }
        .craft-scrollbar {
            scrollbar-width: thin;
            scrollbar-color: #DDD3C5 transparent;
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
                    <button onclick="toggleSidebar()" class="ml-auto text-white/70 hover:text-white lg:hidden p-1" aria-label="Tutup Menu">
                        <i class="fas fa-times text-base"></i>
                    </button>
                </div>

                <!-- Navigation List -->
                <nav class="p-4 space-y-1.5 flex-1">
                    <!-- Beranda -->
                    <a href="beranda.php" class="text-white/80 hover:text-white hover:bg-white/10 rounded-lg px-4 py-3 flex items-center gap-3 font-medium text-sm transition-colors">
                        <i class="fas fa-th-large w-5 text-center text-sm opacity-90"></i>
                        <span>Beranda</span>
                    </a>

                    <!-- Produk -->
                    <a href="produk.php" class="text-white/80 hover:text-white hover:bg-white/10 rounded-lg px-4 py-3 flex items-center gap-3 font-medium text-sm transition-colors">
                        <i class="fas fa-box-open w-5 text-center text-sm opacity-90"></i>
                        <span>Produk</span>
                    </a>

                    <!-- Pesanan (Active) -->
                    <a href="pesanan.php" class="bg-[#D46E15] text-white rounded-lg px-4 py-3 flex items-center gap-3 font-semibold text-sm transition-all shadow-sm">
                        <i class="fas fa-shopping-bag w-5 text-center text-sm"></i>
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
                    <button onclick="toggleSidebar()" class="lg:hidden p-1.5 text-[#3E2B21] hover:bg-[#EFE8DE] rounded-lg" aria-label="Buka Menu">
                        <i class="fas fa-bars text-lg text-[#3E2B21]"></i>
                    </button>

                    <div>
                        <!-- Breadcrumbs -->
                        <div class="flex items-center gap-1.5 text-xs">
                            <a href="beranda.php" class="text-[#8E7E73] hover:text-[#D46E15] transition-colors">Beranda</a>
                            <span class="text-[#D8C7B8]">/</span>
                            <a href="pesanan.php" class="text-[#8E7E73] hover:text-[#D46E15] transition-colors">Pesanan</a>
                            <span class="text-[#D8C7B8]">/</span>
                            <span class="text-[#3E2B21] font-semibold" id="headerBreadcrumb"><?= $is_tambah_view ? 'Tambah Pesanan Baru' : 'Manajemen' ?></span>
                        </div>
                        <!-- Section Heading -->
                        <h1 class="text-[#251A14] font-bold text-base md:text-lg leading-tight mt-0.5" id="headerTitle">
                            <?= $is_tambah_view ? 'Tambah Pesanan Baru' : 'Manage Pesanan' ?>
                        </h1>
                    </div>
                </div>

                <!-- Right: Date Indicator, Notification Bell, User Avatar -->
                <div class="flex items-center gap-3 md:gap-5">
                    
                    <!-- Date Button (Dinamis) -->
                    <div class="hidden sm:flex items-center gap-2 bg-[#EFE8DE] text-[#3E2B21] px-3.5 py-1.5 rounded-lg text-xs md:text-sm font-semibold border border-[#DDD3C5]">
                        <i class="far fa-calendar-alt text-[#D46E15] text-xs"></i>
                        <span><?= $todayFormatted ?></span>
                    </div>

                    <!-- Notification Bell with Orange Dot -->
                    <div class="relative">
                        <button onclick="toggleNotificationDropdown()" class="relative p-2 text-[#544337] hover:bg-[#F5F3F1] rounded-lg transition-colors cursor-pointer" title="Notifikasi" id="notificationBtn">
                            <i class="far fa-bell text-[#3E2B21] text-base"></i>
                            <span class="absolute top-1.5 right-1.5 w-2 h-2 bg-[#D46E15] rounded-full ring-2 ring-white"></span>
                        </button>

                        <!-- Notification Dropdown Popover -->
                        <div id="notificationDropdown" class="hidden absolute right-0 mt-2.5 w-80 sm:w-96 craft-dropdown py-2 z-50">
                            <div class="px-4 py-2 border-b border-[#ECE5DD] flex items-center justify-between">
                                <span class="font-semibold text-xs text-[#251A14]">Notifikasi Operasional</span>
                                <span class="text-[10px] craft-badge-warning font-medium px-2 py-0.5 rounded-full">3 Baru</span>
                            </div>
                            <div class="divide-y divide-[#F0ECE9]/70 max-h-72 overflow-y-auto py-1">
                                <div class="px-3.5 py-2.5 hover:bg-[#F8F5F1] transition-colors flex items-start gap-3 rounded-lg mx-1 cursor-pointer">
                                    <span class="w-2 h-2 rounded-full bg-rose-500 mt-1.5 shrink-0"></span>
                                    <div>
                                        <p class="text-xs font-medium text-[#251A14]">Pesanan Prioritas #ORD-089</p>
                                        <p class="text-[11px] text-[#5E4D42] mt-0.5">Ibu Sari - Siap diantar jam 15:00 WIB.</p>
                                        <span class="text-[10px] text-[#8E7E73] tabular-nums">10 menit lalu</span>
                                    </div>
                                </div>
                                <div class="px-3.5 py-2.5 hover:bg-[#F8F5F1] transition-colors flex items-start gap-3 rounded-lg mx-1 cursor-pointer">
                                    <span class="w-2 h-2 rounded-full bg-emerald-500 mt-1.5 shrink-0"></span>
                                    <div>
                                        <p class="text-xs font-medium text-[#251A14]">Pesanan #ORD-084 Selesai</p>
                                        <p class="text-[11px] text-[#5E4D42] mt-0.5">Bu Rina telah mengambil pesanan bolu pisang di toko.</p>
                                        <span class="text-[10px] text-[#8E7E73] tabular-nums">45 menit lalu</span>
                                    </div>
                                </div>
                                <div class="px-3.5 py-2.5 hover:bg-[#F8F5F1] transition-colors flex items-start gap-3 rounded-lg mx-1 cursor-pointer">
                                    <span class="w-2 h-2 rounded-full bg-amber-500 mt-1.5 shrink-0"></span>
                                    <div>
                                        <p class="text-xs font-medium text-[#251A14]">Sisa Kapasitas Obrok Motor</p>
                                        <p class="text-[11px] text-[#5E4D42] mt-0.5">Trip siang terisi 80/150 pcs roti.</p>
                                        <span class="text-[10px] text-[#8E7E73] tabular-nums">2 jam lalu</span>
                                    </div>
                                </div>
                            </div>
                            <div class="px-4 pt-2 border-t border-[#ECE5DD] text-center">
                                <a href="pesanan.php" class="text-xs text-[#D46E15] font-semibold hover:underline">Lihat Semua Pesanan &rarr;</a>
                            </div>
                        </div>
                    </div>

                    <!-- User Circle Icon with Dropdown -->
                    <div class="relative">
                        <div onclick="toggleUserDropdown()" class="w-8 h-8 rounded-full bg-[#3E2B21] border border-white/20 flex items-center justify-center text-white shadow-xs cursor-pointer hover:opacity-95 transition-all active:scale-95" title="Profil Pengguna" id="userAvatarBtn">
                            <i class="fas fa-user text-xs text-[#FAF8F5]"></i>
                        </div>

                        <!-- User Dropdown Popover -->
                        <div id="userDropdown" class="hidden absolute right-0 mt-2.5 w-56 craft-dropdown py-1.5 z-50 text-xs">
                            <div class="px-4 py-2.5 border-b border-[#ECE5DD]">
                                <p class="text-xs font-semibold text-[#251A14] truncate"><?= htmlspecialchars($_SESSION['nama_lengkap'] ?? $_SESSION['user'] ?? 'Admin') ?></p>
                                <p class="text-[11px] text-[#8E7E73] mt-0.5"><?= htmlspecialchars(ucfirst($_SESSION['role'] ?? 'Admin')) ?> @ Berkat Dinasti</p>
                            </div>
                            <div class="py-1">
                                <a href="pengaturan.php" class="craft-dropdown-item flex items-center gap-2">
                                    <i class="fas fa-cog text-xs text-[#8E7E73]"></i>
                                    <span>Pengaturan Akun & Toko</span>
                                </a>
                                <a href="pengaturan.php?tab=user" class="craft-dropdown-item flex items-center gap-2">
                                    <i class="fas fa-users-cog text-xs text-[#8E7E73]"></i>
                                    <span>Manajemen User</span>
                                </a>
                            </div>
                            <div class="border-t border-[#ECE5DD] pt-1">
                                <button type="button" onclick="openLogoutModal()" class="w-full text-left craft-dropdown-item text-[#991B1B] hover:text-[#7F1D1D] hover:bg-rose-50/70 flex items-center gap-2">
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
                
                <!-- Toast Notification Banner -->
                <?php if ($toast_message): ?>
                    <div id="toastNotification" class="mb-6 bg-emerald-50 border border-emerald-200 text-emerald-800 px-4 py-3 rounded-xl flex items-center justify-between shadow-xs animate-in fade-in">
                        <div class="flex items-center gap-2.5">
                            <i class="fas fa-check-circle text-emerald-600 text-base"></i>
                            <span class="text-sm font-medium"><?= htmlspecialchars($toast_message) ?></span>
                        </div>
                        <button onclick="document.getElementById('toastNotification').remove()" class="text-emerald-700 hover:text-emerald-900 p-1">
                            <i class="fas fa-times text-xs"></i>
                        </button>
                    </div>
                <?php endif; ?>

                <!-- Error Notification Banner -->
                <?php if ($error_message): ?>
                    <div id="errorNotification" class="mb-6 bg-rose-50 border border-rose-200 text-rose-800 px-4 py-3 rounded-xl flex items-center justify-between shadow-xs animate-in fade-in">
                        <div class="flex items-center gap-2.5">
                            <i class="fas fa-exclamation-triangle text-rose-600 text-base"></i>
                            <span class="text-sm font-medium"><?= htmlspecialchars($error_message) ?></span>
                        </div>
                        <button onclick="document.getElementById('errorNotification').remove()" class="text-rose-700 hover:text-rose-900 p-1">
                            <i class="fas fa-times text-xs"></i>
                        </button>
                    </div>
                <?php endif; ?>

                <!-- Dynamic In-Page Toast for Drag & Drop / Actions -->
                <div id="dynamicToast" class="hidden fixed bottom-6 right-6 z-50 bg-[#3E2B21] text-[#FAF8F5] border border-white/10 px-5 py-3.5 rounded-xl shadow-2xl flex items-center gap-3 text-xs font-semibold animate-in slide-in-from-bottom-3 duration-200">
                    <span id="dynamicToastIcon"><i class="fas fa-check-circle text-[#D46E15]"></i></span>
                    <span id="dynamicToastText">Aksi berhasil dijalankan</span>
                </div>
                
                <?php if ($is_tambah_view): ?>
                <!-- ============================================================== -->
                <!-- FULL PAGE: TAMBAH PESANAN BARU (POS KASIR BAKERY)             -->
                <!-- ============================================================== -->
                <div class="space-y-6">
                    <!-- Top Navigation Bar & Action -->
                    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 bg-[#FDFBF7] p-4 sm:p-5 rounded-2xl border border-[#E2D7C8] shadow-card">
                        <div class="flex items-center gap-3">
                            <a href="pesanan.php" class="w-10 h-10 rounded-xl bg-[#EFE8DE] hover:bg-[#E5DDD0] text-[#3E2B21] flex items-center justify-center border border-[#DDD3C5] transition-all hover:scale-105 active:scale-95 shadow-2xs" title="Kembali ke Manajemen Pesanan">
                                <i class="fas fa-arrow-left text-sm"></i>
                            </a>
                            <div>
                                <div class="flex items-center gap-2">
                                    <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-[#D46E15]/10 text-[#D46E15] uppercase tracking-wider">POS Kasir Bakery</span>
                                    <span class="text-xs text-[#8E7E73]">• Pre-Order Form</span>
                                </div>
                                <h2 class="text-xl sm:text-2xl font-bold text-[#251A14] tracking-tight mt-0.5">Tambah Pesanan Baru</h2>
                            </div>
                        </div>
                        <div class="flex items-center gap-2">
                            <a href="pesanan.php" class="px-3.5 py-2 rounded-xl text-xs font-semibold text-[#544337] bg-white border border-[#DDD3C5] hover:bg-[#EFE8DE] transition-colors flex items-center gap-1.5 shadow-2xs">
                                <i class="fas fa-columns text-xs text-[#D46E15]"></i>
                                <span>Kembali ke Kanban</span>
                            </a>
                        </div>
                    </div>

                    <!-- Main Grid: 2 Columns (Catalog on Left, Order Cart on Right) -->
                    <div class="grid grid-cols-1 lg:grid-cols-12 gap-6 items-start">
                        
                        <!-- LEFT: KATALOG PRODUK (KOTAK KATEGORI & PRODUK) -->
                        <div class="lg:col-span-7 xl:col-span-8 space-y-4">
                            
                            <!-- Category Filter Boxes & Search -->
                            <div class="bg-[#FDFBF7] rounded-2xl border border-[#E2D7C8] p-4 sm:p-5 shadow-card space-y-4">
                                <!-- Search Input -->
                                <div class="relative">
                                    <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-[#8E7E73]">
                                        <i class="fas fa-search text-xs"></i>
                                    </div>
                                    <input type="text" id="posProductSearch" oninput="filterPosProducts()" placeholder="Cari nama roti, topping, atau kemasan..." 
                                        class="w-full bg-[#EFE8DE] border border-[#DDD3C5] rounded-xl pl-9 pr-4 py-2.5 text-xs sm:text-sm text-[#251A14] placeholder-[#8E7E73] focus:border-[#D46E15] focus:ring-1 focus:ring-[#D46E15] focus:outline-none transition-colors">
                                </div>

                                <!-- Category Kotak-Kotak (Category Cards Grid) -->
                                <div>
                                    <div class="flex items-center justify-between mb-2">
                                        <label class="block text-xs font-bold text-[#544337] uppercase tracking-wider">Kategori Produk:</label>
                                        <span class="text-[11px] text-[#8E7E73]" id="posVisibleCount">Menampilkan semua item</span>
                                    </div>
                                    <div class="grid grid-cols-2 sm:grid-cols-4 gap-2.5" id="posCategoryCards">
                                        <?php 
                                        $catIcons = [
                                            'Semua' => 'fas fa-th-large',
                                            'Roti Hajatan' => 'fas fa-box-open',
                                            'Bolu Premium' => 'fas fa-birthday-cake',
                                            'Roti Manis' => 'fas fa-bread-slice'
                                        ];
                                        foreach ($categories_for_select as $cat): 
                                            $ico = $catIcons[$cat] ?? 'fas fa-cookie-bite';
                                            $isActive = ($cat === 'Semua');
                                        ?>
                                            <button type="button" onclick="selectPosCategory('<?= htmlspecialchars($cat) ?>')" 
                                                data-category="<?= htmlspecialchars($cat) ?>"
                                                class="pos-category-btn p-3 rounded-xl border text-left flex flex-col justify-between transition-all duration-150 <?= $isActive ? 'bg-[#D46E15] text-white border-[#D46E15] shadow-sm' : 'bg-white text-[#3E2B21] border-[#E2D7C8] hover:border-[#D46E15]/60 hover:bg-[#FDFBF7]' ?>">
                                                <div class="flex items-center justify-between mb-2">
                                                    <i class="<?= $ico ?> text-sm <?= $isActive ? 'text-white' : 'text-[#D46E15]' ?>"></i>
                                                    <span class="cat-count-badge text-[10px] font-bold px-1.5 py-0.5 rounded <?= $isActive ? 'bg-white/20 text-white' : 'bg-[#EFE8DE] text-[#8E7E73]' ?>">
                                                        0
                                                    </span>
                                                </div>
                                                <span class="text-xs font-bold leading-tight"><?= htmlspecialchars($cat) ?></span>
                                            </button>
                                        <?php endforeach; ?>
                                    </div>
                                </div>
                            </div>

                            <!-- Product Grid Cards (Kotak-Kotak Produk) -->
                            <div id="posProductsGrid" class="grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-3 gap-3.5">
                                <!-- Populated dynamically via JavaScript -->
                            </div>

                            <!-- Empty Filter State -->
                            <div id="posProductsEmpty" class="hidden bg-[#FDFBF7] rounded-2xl border border-dashed border-[#DDD3C5] p-8 text-center">
                                <i class="fas fa-cookie text-3xl text-[#8E7E73]/40 mb-3"></i>
                                <h4 class="text-sm font-bold text-[#251A14]">Tidak Ada Produk Ditemukan</h4>
                                <p class="text-xs text-[#8E7E73] mt-1">Coba sesuaikan kata kunci pencarian atau pilih kategori lain.</p>
                            </div>
                        </div>

                        <!-- RIGHT: KERANJANG PESANAN & FORM CHECKOUT -->
                        <div class="lg:col-span-5 xl:col-span-4 lg:sticky lg:top-20 space-y-4">
                            <form id="posOrderForm" method="POST" action="pesanan.php" class="bg-[#FDFBF7] rounded-2xl border border-[#E2D7C8] shadow-card flex flex-col overflow-hidden">
                                <input type="hidden" name="action" value="tambah_pesanan">
                                
                                <!-- Panel Header -->
                                <div class="p-4 border-b border-[#E2D7C8] bg-white flex items-center justify-between">
                                    <div class="flex items-center gap-2">
                                        <i class="fas fa-shopping-basket text-[#D46E15] text-base"></i>
                                        <h3 class="text-sm font-bold text-[#251A14]">Keranjang Pesanan</h3>
                                        <span id="posCartItemBadge" class="text-[11px] font-bold px-2 py-0.5 rounded-full bg-[#EFE8DE] text-[#544337]">0 Produk</span>
                                    </div>
                                    <button type="button" onclick="clearPosCart()" id="btnClearPosCart" class="text-xs text-rose-600 hover:text-rose-700 font-semibold hidden transition-colors">
                                        Kosongkan
                                    </button>
                                </div>

                                <!-- Cart Items List (Starts with 0 items) -->
                                <div class="p-4 max-h-[300px] overflow-y-auto space-y-2.5 border-b border-[#E2D7C8]" id="posCartList">
                                    <!-- Populated dynamically by JavaScript -->
                                </div>

                                <!-- Hidden Input Container for POST items -->
                                <div id="posHiddenItemsInputs"></div>

                                <!-- Order Details Form -->
                                <div class="p-4 space-y-3.5 bg-[#FAF7F2] text-xs border-b border-[#E2D7C8]">
                                    <!-- Customer Section -->
                                    <div>
                                        <div class="flex items-center justify-between mb-1.5">
                                            <label class="font-bold text-[#251A14] uppercase tracking-wider text-[11px]">Data Pelanggan</label>
                                            <button type="button" onclick="togglePosNewCustomer()" id="btnTogglePosNewCust" class="text-[#D46E15] hover:text-[#B85207] font-semibold text-[11px] flex items-center gap-1 transition-colors">
                                                <i class="fas fa-user-plus text-[10px]"></i>
                                                <span id="lblPosToggleCust">Tambah Pelanggan Baru</span>
                                            </button>
                                        </div>

                                        <!-- Existing Customer Select -->
                                        <div id="posCustSelectWrapper">
                                            <select id="pos_id_pelanggan" name="id_pelanggan" onchange="syncPosCustomerInfo()" 
                                                class="w-full bg-white border border-[#DDD3C5] rounded-xl px-3 py-2 text-xs text-[#251A14] focus:border-[#D46E15] focus:outline-none">
                                                <?php foreach ($customers_for_select as $c): ?>
                                                    <option value="<?= $c['id_pelanggan'] ?>" data-wa="<?= htmlspecialchars($c['no_wa']) ?>" data-address="<?= htmlspecialchars($c['alamat']) ?>">
                                                        <?= htmlspecialchars($c['nama']) ?> (<?= htmlspecialchars($c['no_wa']) ?>)
                                                    </option>
                                                <?php endforeach; ?>
                                            </select>
                                        </div>

                                        <!-- New Customer Form (Hidden by default) -->
                                        <div id="posNewCustBox" class="hidden space-y-2 p-3 bg-white border border-[#DDD3C5] rounded-xl">
                                            <div>
                                                <label class="block text-[10px] font-bold text-[#544337] mb-1">Nama Lengkap</label>
                                                <input type="text" id="pos_nama_baru" name="nama_pelanggan_baru" placeholder="Cth: Ibu Dewi Lestari" 
                                                    class="w-full bg-[#EFE8DE] border border-[#DDD3C5] rounded-lg px-2.5 py-1.5 text-xs text-[#251A14] focus:border-[#D46E15] focus:outline-none">
                                            </div>
                                            <div>
                                                <label class="block text-[10px] font-bold text-[#544337] mb-1">No WhatsApp</label>
                                                <input type="text" id="pos_wa_baru" name="no_wa_baru" placeholder="Cth: 0812-3456-7890" 
                                                    class="w-full bg-[#EFE8DE] border border-[#DDD3C5] rounded-lg px-2.5 py-1.5 text-xs text-[#251A14] focus:border-[#D46E15] focus:outline-none">
                                            </div>
                                            <div>
                                                <label class="block text-[10px] font-bold text-[#544337] mb-1">Alamat Pengiriman</label>
                                                <textarea id="pos_alamat_baru" name="alamat_baru" rows="2" placeholder="Cth: Jl. Panglima Sudirman No. 12, Kota Batu" 
                                                    class="w-full bg-[#EFE8DE] border border-[#DDD3C5] rounded-lg px-2.5 py-1.5 text-xs text-[#251A14] focus:border-[#D46E15] focus:outline-none"></textarea>
                                            </div>
                                        </div>
                                    </div>

                                    <!-- Delivery Date & Time (PO Rule: Min Today) -->
                                    <div class="grid grid-cols-2 gap-2">
                                        <div>
                                            <label class="block font-bold text-[#251A14] text-[11px] mb-1">Tgl Kirim / PO</label>
                                            <input type="date" id="pos_tgl_kirim" name="tgl_kirim" 
                                                min="<?= date('Y-m-d') ?>" 
                                                value="<?= htmlspecialchars($_GET['date'] ?? $_GET['tgl_kirim'] ?? date('Y-m-d', strtotime('+1 day'))) ?>" required
                                                class="w-full bg-white border border-[#DDD3C5] rounded-xl px-2.5 py-1.5 text-xs text-[#251A14] focus:border-[#D46E15] focus:outline-none">
                                        </div>
                                        <div>
                                            <label class="block font-bold text-[#251A14] text-[11px] mb-1">Waktu Kirim</label>
                                            <input type="time" id="pos_waktu_kirim" name="waktu_kirim" value="09:00" required
                                                class="w-full bg-white border border-[#DDD3C5] rounded-xl px-2.5 py-1.5 text-xs text-[#251A14] focus:border-[#D46E15] focus:outline-none">
                                        </div>
                                    </div>

                                    <!-- Payment Status & Method -->
                                    <div class="grid grid-cols-2 gap-2">
                                        <div>
                                            <label class="block font-bold text-[#251A14] text-[11px] mb-1">Status Bayar</label>
                                            <select id="pos_status_bayar" name="status_bayar" class="w-full bg-white border border-[#DDD3C5] rounded-xl px-2.5 py-1.5 text-xs text-[#251A14] focus:border-[#D46E15] focus:outline-none">
                                                <option value="lunas">Lunas</option>
                                                <option value="dp">DP / Uang Muka</option>
                                                <option value="belum_bayar">Belum Bayar</option>
                                            </select>
                                        </div>
                                        <div>
                                            <label class="block font-bold text-[#251A14] text-[11px] mb-1">Metode Bayar</label>
                                            <select id="pos_metode_bayar" name="metode_bayar" class="w-full bg-white border border-[#DDD3C5] rounded-xl px-2.5 py-1.5 text-xs text-[#251A14] focus:border-[#D46E15] focus:outline-none">
                                                <option value="cash">Tunai (Cash)</option>
                                                <option value="transfer">Transfer Bank</option>
                                            </select>
                                        </div>
                                    </div>

                                    <!-- Notes -->
                                    <div>
                                        <label class="block font-bold text-[#251A14] text-[11px] mb-1">Catatan Khusus (Opsional)</label>
                                        <input type="text" id="pos_catatan" name="catatan" placeholder="Cth: Topping keju lebih tebal, packing rapi..." 
                                            class="w-full bg-white border border-[#DDD3C5] rounded-xl px-2.5 py-1.5 text-xs text-[#251A14] focus:border-[#D46E15] focus:outline-none">
                                    </div>
                                </div>

                                <!-- Panel Summary & Submit Button -->
                                <div class="p-4 bg-white space-y-3">
                                    <div class="space-y-1.5 text-xs">
                                        <div class="flex justify-between text-[#8E7E73]">
                                            <span>Subtotal:</span>
                                            <span id="posSummarySubtotal" class="font-mono font-semibold text-[#251A14]">Rp 0</span>
                                        </div>
                                        <div class="flex justify-between text-[#8E7E73]">
                                            <span>Ongkir (Malang & Batu):</span>
                                            <span class="font-semibold text-emerald-600">Rp 0 (Gratis)</span>
                                        </div>
                                        <div class="flex justify-between text-sm font-bold pt-2 border-t border-[#E2D7C8]">
                                            <span class="text-[#251A14]">Grand Total:</span>
                                            <span id="posSummaryGrandTotal" class="text-base text-[#D46E15] font-mono font-bold">Rp 0</span>
                                        </div>
                                    </div>

                                    <!-- MOQ Alert Box -->
                                    <div id="posMoqWarning" class="hidden p-2.5 rounded-xl bg-rose-50 border border-rose-200 text-rose-800 text-[11px] leading-relaxed">
                                        <div class="flex items-start gap-1.5">
                                            <i class="fas fa-exclamation-circle text-rose-600 mt-0.5 shrink-0"></i>
                                            <span id="posMoqWarningText">Minimal order belum tercapai untuk beberapa produk.</span>
                                        </div>
                                    </div>

                                    <!-- Submit Button: DISABLED IF NO PRODUCTS -->
                                    <button type="submit" id="btnPosSubmitOrder" disabled 
                                        class="w-full bg-[#D46E15] hover:bg-[#B85207] text-white font-bold text-xs sm:text-sm py-3 px-4 rounded-xl flex items-center justify-center gap-2 shadow-button-orange active:scale-95 transition-all disabled:opacity-50 disabled:cursor-not-allowed disabled:bg-[#C9BFB5]">
                                        <i class="fas fa-check"></i>
                                        <span id="btnPosSubmitText">Pilih Produk Terlebih Dahulu</span>
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
                <?php else: ?>
                
                <!-- TOP BANNER: TITLE, STATS, & OMSET (Dark Chocolate Espresso Cards) -->
                <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between gap-4 mb-6">
                    <div>
                        <div class="flex items-center gap-2.5">
                            <h2 class="text-[#251A14] text-2xl font-bold tracking-tight">Manajemen Pesanan</h2>
                            <span class="bg-[#EFE8DE] text-[#D46E15] text-xs font-semibold px-2.5 py-1 rounded-full border border-[#DDD3C5]" id="totalOrdersBadge">
                                <?= $totalOrdersCount ?> Total Pesanan
                            </span>
                        </div>
                        <p class="text-[#8E7E73] text-sm mt-1" id="pageSubtitle">
                            Kelola status dan alur pengiriman pesanan roti secara real-time
                        </p>
                    </div>

                    <!-- Right Stats Indicator Pills (Dark Chocolate Espresso Cluster) -->
                    <div class="grid grid-cols-1 sm:grid-cols-3 gap-3 shrink-0">
                        <!-- Menunggu & Proses: count -->
                        <div class="craft-card-espresso rounded-xl p-3 border border-white/10 shadow-lg flex items-center justify-between min-w-[140px]">
                            <div class="pr-2">
                                <span class="text-[#D8C7B8] text-[10px] font-semibold uppercase tracking-wider block">AKTIF DAPUR</span>
                                <span class="font-bold text-base text-[#FAF8F5]" id="statMenunggu"><?= $totalMenungguCount ?> Pesanan</span>
                            </div>
                            <div class="w-8 h-8 rounded-lg bg-amber-500/20 ring-1 ring-amber-400/30 flex items-center justify-center text-amber-400 shrink-0">
                                <i class="fas fa-fire text-xs"></i>
                            </div>
                        </div>

                        <!-- Selesai: count -->
                        <div class="craft-card-espresso rounded-xl p-3 border border-white/10 shadow-lg flex items-center justify-between min-w-[140px]">
                            <div class="pr-2">
                                <span class="text-[#D8C7B8] text-[10px] font-semibold uppercase tracking-wider block">SELESAI</span>
                                <span class="font-bold text-base text-[#FAF8F5]" id="statSelesai"><?= $totalSelesaiCount ?> Pesanan</span>
                            </div>
                            <div class="w-8 h-8 rounded-lg bg-emerald-500/20 ring-1 ring-emerald-400/30 flex items-center justify-center text-emerald-400 shrink-0">
                                <i class="fas fa-check-double text-xs"></i>
                            </div>
                        </div>

                        <!-- Omset Card -->
                        <div class="craft-card-espresso rounded-xl p-3 border border-white/10 shadow-lg flex items-center justify-between min-w-[170px]">
                            <div class="pr-2">
                                <span class="text-[#D8C7B8] text-[10px] font-semibold uppercase tracking-wider block">TOTAL OMSET</span>
                                <span class="font-bold text-base text-[#FAF8F5]">Rp <?= number_format($totalOmset, 0, ',', '.') ?></span>
                            </div>
                            <div class="w-8 h-8 rounded-lg bg-[#D46E15]/20 ring-1 ring-[#D46E15]/30 flex items-center justify-center text-[#D46E15] shrink-0">
                                <i class="fas fa-wallet text-xs"></i>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- TAB SWITCHER BAR (KANBAN BOARD VS KALENDER PENGIRIMAN) -->
                <div class="bg-[#EFE8DE] rounded-xl border border-[#DDD3C5] p-1.5 flex items-center gap-2 mb-6 shadow-2xs max-w-fit">
                    <!-- Tab 1: Kanban Board -->
                    <button onclick="switchTab('kanban')" id="tabBtnKanban" 
                        class="px-4 py-2 rounded-lg text-sm font-semibold flex items-center gap-2 transition-all <?= $active_tab !== 'kalender' ? 'bg-[#D46E15] text-white shadow-sm font-semibold' : 'text-[#544337] hover:text-[#251A14] hover:bg-[#E5DDD0]' ?>">
                        <i class="fas fa-columns text-xs"></i>
                        <span>Kanban Board</span>
                    </button>

                    <!-- Tab 2: Kalender Pengiriman -->
                    <button onclick="switchTab('kalender')" id="tabBtnKalender" 
                        class="px-4 py-2 rounded-lg text-sm font-semibold flex items-center gap-2 transition-all <?= $active_tab === 'kalender' ? 'bg-[#D46E15] text-white shadow-sm font-semibold' : 'text-[#544337] hover:text-[#251A14] hover:bg-[#E5DDD0]' ?>">
                        <i class="far fa-calendar-alt text-xs"></i>
                        <span>Kalender Pengiriman</span>
                    </button>
                </div>

                <!-- ============================================================== -->
                <!-- TAB CONTENT 1: KANBAN BOARD (4 SWIMLANES + CATATAN DAPUR)       -->
                <!-- ============================================================== -->
                <div id="kanbanView" class="<?= $active_tab === 'kalender' ? 'hidden' : '' ?>">
                    
                    <!-- Action Bar: Unduh Rekap & Tambah Pesanan -->
                    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3 mb-6">
                        <div class="flex items-center gap-2 text-xs text-[#8E7E73]">
                            <span class="inline-block w-2 h-2 rounded-full bg-[#27AE60]"></span>
                            <span>Tarik kartu untuk memindahkan status alur kerja produksi & pengiriman secara langsung.</span>
                        </div>

                        <div class="flex items-center gap-3 self-end sm:self-auto">
                            <!-- Tombol Unduh Rekap (Ekspor CSV Riil) -->
                            <button onclick="handleDownloadRecap()" class="bg-[#EFE8DE] hover:bg-[#E5DDD0] border border-[#DDD3C5] text-[#3E2B21] text-sm font-medium px-4 py-2.5 rounded-lg flex items-center gap-2 transition-colors shadow-2xs">
                                <i class="fas fa-file-download text-xs text-[#D46E15]"></i>
                                <span>Unduh Rekap</span>
                            </button>

                            <!-- Tombol Tambah Pesanan -->
                            <a href="pesanan.php?view=tambah_pesanan" class="bg-[#D46E15] hover:bg-[#B85207] text-white text-sm font-semibold px-4 py-2.5 rounded-lg flex items-center gap-2 transition-all shadow-button-orange active:scale-95">
                                <i class="fas fa-plus text-xs"></i>
                                <span>Tambah Pesanan</span>
                            </a>
                        </div>
                    </div>

                    <!-- 4 Swimlanes Kanban Board Grid (Menunggu -> Oven -> Siap Kirim -> Selesai) -->
                    <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-4 gap-5 mb-8">
                        
                        <!-- SWIMLANE 1: MENUNGGU KONFIRMASI -->
                        <div class="kanban-column bg-[#EFE8DE]/60 border border-[#DDD3C5] rounded-2xl p-4 flex flex-col justify-between min-h-[520px] transition-colors"
                             id="col-menunggu" ondragover="allowDrop(event)" ondragleave="dragLeave(event)" ondrop="drop(event, 'menunggu')">
                            <div>
                                <div class="flex items-center justify-between mb-1">
                                    <div class="flex items-center gap-2">
                                        <span class="bg-[#E2B93B]/20 text-[#886C12] text-xs font-bold px-2.5 py-0.5 rounded-full">Menunggu Konfirmasi</span>
                                        <span class="w-5 h-5 rounded-full bg-[#E2B93B] text-white text-[11px] font-bold flex items-center justify-center count-badge" id="count-menunggu"><?= count($orders_by_status['menunggu']) ?></span>
                                    </div>
                                </div>
                                <p class="text-[#8E7E73] text-xs mb-4">Pesanan baru masuk kasir</p>

                                <!-- Kartu Pesanan Container (Maks. 5 Kartu, Scrollable jika lebih) -->
                                <div class="space-y-3.5 order-cards-container max-h-[620px] overflow-y-auto pr-1.5 craft-scrollbar" id="container-menunggu">
                                    <?php foreach ($orders_by_status['menunggu'] as $ord): ?>
                                        <div id="card-<?= $ord['id'] ?>" draggable="true" ondragstart="drag(event, '<?= $ord['id'] ?>')" 
                                             class="bg-[#FDFBF7] rounded-xl p-4 border border-[#E2D7C8] border-l-4 border-l-[#E2B93B] shadow-sm hover:shadow-md hover:border-[#D46E15] transition-all relative cursor-grab active:cursor-grabbing"
                                             onclick="openOrderDetail(<?= htmlspecialchars(json_encode($ord)) ?>)">
                                            <div class="flex items-start justify-between">
                                                <div>
                                                    <h4 class="text-[#251A14] font-bold text-sm leading-tight"><?= htmlspecialchars($ord['customer']) ?></h4>
                                                    <span class="text-[#8E7E73] text-xs font-mono"><?= htmlspecialchars($ord['id']) ?></span>
                                                </div>
                                                <span class="text-[#A89C92] hover:text-[#251A14]">
                                                    <i class="fas fa-ellipsis-v text-xs"></i>
                                                </span>
                                            </div>
                                            <div class="flex items-center gap-1.5 text-xs text-[#544337] font-medium mt-2.5">
                                                <i class="fas fa-bread-slice text-[#D46E15] text-xs"></i>
                                                <span class="truncate"><?= htmlspecialchars($ord['items']) ?></span>
                                            </div>
                                            <div class="flex items-center gap-1.5 text-xs text-[#8E7E73] mt-1">
                                                <i class="fas fa-map-marker-alt text-[#8E7E73] text-xs"></i>
                                                <span class="truncate"><?= htmlspecialchars($ord['destination']) ?></span>
                                            </div>
                                            <div class="flex items-center justify-between pt-3 mt-3 border-t border-[#EAE1D5]">
                                                <div class="flex items-center gap-1 text-xs <?= $ord['is_urgent'] ? 'text-[#EB5757] font-semibold' : 'text-[#8E7E73]' ?>">
                                                    <i class="far fa-clock text-xs"></i>
                                                    <span><?= htmlspecialchars($ord['time']) ?></span>
                                                </div>
                                                <span class="text-[11px] font-semibold px-2 py-0.5 rounded-full <?= $ord['bayar_class'] ?>">
                                                    <?= htmlspecialchars($ord['status_bayar']) ?>
                                                </span>
                                            </div>
                                        </div>
                                    <?php endforeach; ?>
                                </div>
                            </div>

                            <a href="pesanan.php?view=tambah_pesanan" class="w-full py-2.5 mt-4 border border-dashed border-[#DDD3C5] hover:border-[#D46E15] text-[#8E7E73] hover:text-[#D46E15] text-xs font-semibold rounded-xl flex items-center justify-center gap-1.5 transition-colors">
                                <i class="fas fa-plus text-xs"></i>
                                <span>Tambah Kartu</span>
                            </a>
                        </div>

                        <!-- SWIMLANE 2: PROSES DAPUR (OVEN & BAKING) -->
                        <div class="kanban-column bg-[#EFE8DE]/60 border border-[#DDD3C5] rounded-2xl p-4 flex flex-col justify-between min-h-[520px] transition-colors"
                             id="col-proses" ondragover="allowDrop(event)" ondragleave="dragLeave(event)" ondrop="drop(event, 'proses')">
                            <div>
                                <div class="flex items-center justify-between mb-1">
                                    <div class="flex items-center gap-2">
                                        <span class="bg-[#D46E15]/20 text-[#B85207] text-xs font-bold px-2.5 py-0.5 rounded-full">Proses Dapur</span>
                                        <span class="w-5 h-5 rounded-full bg-[#D46E15] text-white text-[11px] font-bold flex items-center justify-center count-badge" id="count-proses"><?= count($orders_by_status['proses']) ?></span>
                                    </div>
                                </div>
                                <p class="text-[#8E7E73] text-xs mb-4">Adonan, oven & pendinginan</p>

                                <div class="space-y-3.5 order-cards-container max-h-[620px] overflow-y-auto pr-1.5 craft-scrollbar" id="container-proses">
                                    <?php foreach ($orders_by_status['proses'] as $ord): ?>
                                        <div id="card-<?= $ord['id'] ?>" draggable="true" ondragstart="drag(event, '<?= $ord['id'] ?>')" 
                                             class="bg-[#FDFBF7] rounded-xl p-4 border border-[#E2D7C8] border-l-4 border-l-[#D46E15] shadow-sm hover:shadow-md hover:border-[#D46E15] transition-all relative cursor-grab active:cursor-grabbing"
                                             onclick="openOrderDetail(<?= htmlspecialchars(json_encode($ord)) ?>)">
                                            <div class="flex items-start justify-between">
                                                <div>
                                                    <h4 class="text-[#251A14] font-bold text-sm leading-tight"><?= htmlspecialchars($ord['customer']) ?></h4>
                                                    <span class="text-[#8E7E73] text-xs font-mono"><?= htmlspecialchars($ord['id']) ?></span>
                                                </div>
                                                <span class="text-[#A89C92] hover:text-[#251A14]">
                                                    <i class="fas fa-ellipsis-v text-xs"></i>
                                                </span>
                                            </div>
                                            <div class="flex items-center gap-1.5 text-xs text-[#544337] font-medium mt-2.5">
                                                <i class="fas fa-bread-slice text-[#D46E15] text-xs"></i>
                                                <span class="truncate"><?= htmlspecialchars($ord['items']) ?></span>
                                            </div>
                                            <div class="flex items-center gap-1.5 text-xs text-[#8E7E73] mt-1">
                                                <i class="fas fa-map-marker-alt text-[#8E7E73] text-xs"></i>
                                                <span class="truncate"><?= htmlspecialchars($ord['destination']) ?></span>
                                            </div>
                                            <div class="flex items-center justify-between pt-3 mt-3 border-t border-[#EAE1D5]">
                                                <div class="flex items-center gap-1 text-xs text-[#8E7E73]">
                                                    <i class="far fa-clock text-xs"></i>
                                                    <span><?= htmlspecialchars($ord['time']) ?></span>
                                                </div>
                                                <span class="text-[11px] font-semibold px-2 py-0.5 rounded-full <?= $ord['bayar_class'] ?>">
                                                    <?= htmlspecialchars($ord['status_bayar']) ?>
                                                </span>
                                            </div>
                                        </div>
                                    <?php endforeach; ?>
                                </div>
                            </div>
                        </div>

                        <!-- SWIMLANE 3: SIAP KIRIM (PACKING SELESAI / DRIVER TRIP) -->
                        <div class="kanban-column bg-[#EFE8DE]/60 border border-[#DDD3C5] rounded-2xl p-4 flex flex-col justify-between min-h-[520px] transition-colors"
                             id="col-kirim" ondragover="allowDrop(event)" ondragleave="dragLeave(event)" ondrop="drop(event, 'kirim')">
                            <div>
                                <div class="flex items-center justify-between mb-1">
                                    <div class="flex items-center gap-2">
                                        <span class="bg-[#2F80ED]/20 text-[#1B57A6] text-xs font-bold px-2.5 py-0.5 rounded-full">Siap Kirim</span>
                                        <span class="w-5 h-5 rounded-full bg-[#2F80ED] text-white text-[11px] font-bold flex items-center justify-center count-badge" id="count-kirim"><?= count($orders_by_status['kirim']) ?></span>
                                    </div>
                                </div>
                                <p class="text-[#8E7E73] text-xs mb-4">Sudah dipacking mika / kardus</p>

                                <div class="space-y-3.5 order-cards-container max-h-[620px] overflow-y-auto pr-1.5 craft-scrollbar" id="container-kirim">
                                    <?php foreach ($orders_by_status['kirim'] as $ord): ?>
                                        <div id="card-<?= $ord['id'] ?>" draggable="true" ondragstart="drag(event, '<?= $ord['id'] ?>')" 
                                             class="bg-[#FDFBF7] rounded-xl p-4 border border-[#E2D7C8] border-l-4 border-l-[#2F80ED] shadow-sm hover:shadow-md hover:border-[#D46E15] transition-all relative cursor-grab active:cursor-grabbing"
                                             onclick="openOrderDetail(<?= htmlspecialchars(json_encode($ord)) ?>)">
                                            <div class="flex items-start justify-between">
                                                <div>
                                                    <h4 class="text-[#251A14] font-bold text-sm leading-tight"><?= htmlspecialchars($ord['customer']) ?></h4>
                                                    <span class="text-[#8E7E73] text-xs font-mono"><?= htmlspecialchars($ord['id']) ?></span>
                                                </div>
                                                <span class="text-[#A89C92] hover:text-[#251A14]">
                                                    <i class="fas fa-ellipsis-v text-xs"></i>
                                                </span>
                                            </div>
                                            <div class="flex items-center gap-1.5 text-xs text-[#544337] font-medium mt-2.5">
                                                <i class="fas fa-bread-slice text-[#D46E15] text-xs"></i>
                                                <span class="truncate"><?= htmlspecialchars($ord['items']) ?></span>
                                            </div>
                                            <div class="inline-flex items-center gap-1.5 text-xs font-medium text-[#544337] bg-[#EFE8DE] px-2.5 py-1 rounded-md mt-2 border border-[#DDD3C5]">
                                                <i class="fas fa-motorcycle text-xs text-[#D46E15]"></i>
                                                <span><?= htmlspecialchars($ord['courier_info'] ?? 'Kurir Toko') ?></span>
                                            </div>
                                            <div class="flex items-center justify-between pt-3 mt-3 border-t border-[#EAE1D5]">
                                                <div class="flex items-center gap-1 text-xs text-[#8E7E73]">
                                                    <i class="far fa-clock text-xs"></i>
                                                    <span><?= htmlspecialchars($ord['time']) ?></span>
                                                </div>
                                                <span class="text-[11px] font-semibold px-2 py-0.5 rounded-full <?= $ord['bayar_class'] ?>">
                                                    <?= htmlspecialchars($ord['status_bayar']) ?>
                                                </span>
                                            </div>
                                        </div>
                                    <?php endforeach; ?>
                                </div>
                            </div>
                        </div>

                        <!-- SWIMLANE 4: SELESAI -->
                        <div class="kanban-column bg-[#EFE8DE]/60 border border-[#DDD3C5] rounded-2xl p-4 flex flex-col justify-between min-h-[520px] transition-colors"
                             id="col-selesai" ondragover="allowDrop(event)" ondragleave="dragLeave(event)" ondrop="drop(event, 'selesai')">
                            <div>
                                <div class="flex items-center justify-between mb-1">
                                    <div class="flex items-center gap-2">
                                        <span class="bg-[#27AE60]/20 text-[#1B7A43] text-xs font-bold px-2.5 py-0.5 rounded-full">Selesai</span>
                                        <span class="w-5 h-5 rounded-full bg-[#27AE60] text-white text-[11px] font-bold flex items-center justify-center count-badge" id="count-selesai"><?= count($orders_by_status['selesai']) ?></span>
                                    </div>
                                </div>
                                <p class="text-[#8E7E73] text-xs mb-4">Diterima pelanggan / lunas</p>

                                <div class="space-y-3.5 order-cards-container max-h-[620px] overflow-y-auto pr-1.5 craft-scrollbar" id="container-selesai">
                                    <?php foreach ($orders_by_status['selesai'] as $ord): ?>
                                        <div id="card-<?= $ord['id'] ?>" draggable="true" ondragstart="drag(event, '<?= $ord['id'] ?>')" 
                                             class="bg-[#FDFBF7] rounded-xl p-4 border border-[#E2D7C8] border-l-4 border-l-[#27AE60] shadow-sm hover:shadow-md hover:border-[#D46E15] transition-all relative cursor-grab active:cursor-grabbing"
                                             onclick="openOrderDetail(<?= htmlspecialchars(json_encode($ord)) ?>)">
                                            <div class="flex items-start justify-between">
                                                <div>
                                                    <h4 class="text-[#251A14] font-bold text-sm leading-tight"><?= htmlspecialchars($ord['customer']) ?></h4>
                                                    <span class="text-[#8E7E73] text-xs font-mono"><?= htmlspecialchars($ord['id']) ?></span>
                                                </div>
                                                <span class="text-[#A89C92] hover:text-[#251A14]">
                                                    <i class="fas fa-ellipsis-v text-xs"></i>
                                                </span>
                                            </div>
                                            <div class="flex items-center gap-1.5 text-xs text-[#544337] font-medium mt-2.5">
                                                <i class="fas fa-bread-slice text-[#D46E15] text-xs"></i>
                                                <span class="truncate"><?= htmlspecialchars($ord['items']) ?></span>
                                            </div>
                                            <div class="inline-flex items-center gap-1.5 text-xs font-medium text-[#1B7A43] bg-emerald-50 px-2.5 py-1 rounded-md mt-2 border border-emerald-200">
                                                <i class="fas fa-check text-xs"></i>
                                                <span><?= htmlspecialchars($ord['courier_info'] ?? 'Pesanan Diterima') ?></span>
                                            </div>
                                            <div class="flex items-center justify-between pt-3 mt-3 border-t border-[#EAE1D5]">
                                                <div class="flex items-center gap-1 text-xs text-[#8E7E73]">
                                                    <i class="far fa-calendar-alt text-xs"></i>
                                                    <span><?= htmlspecialchars($ord['time']) ?></span>
                                                </div>
                                                <span class="text-[11px] font-semibold px-2 py-0.5 rounded-full <?= $ord['bayar_class'] ?>">
                                                    <?= htmlspecialchars($ord['status_bayar']) ?>
                                                </span>
                                            </div>
                                        </div>
                                    <?php endforeach; ?>
                                </div>
                            </div>
                        </div>

                    </div>

                    <!-- BOTTOM SECTION: CATATAN DAPUR / MEMO KOKI -->
                    <div class="bg-[#FDFBF7] border border-[#E2D7C8] rounded-2xl p-6 shadow-sm">
                        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3 pb-4 border-b border-[#E2D7C8] mb-5">
                            <div class="flex items-center gap-2.5">
                                <div class="w-9 h-9 rounded-xl bg-[#EFE8DE] text-[#D46E15] flex items-center justify-center">
                                    <i class="fas fa-clipboard-list text-sm"></i>
                                </div>
                                <div>
                                    <h3 class="text-[#251A14] font-bold text-base">Catatan Produksi & Memo Dapur</h3>
                                    <p class="text-xs text-[#8E7E73]">Instruksi khusus bahan, request irisan roti, dan memo kurir trip obrok</p>
                                </div>
                            </div>
                            <button onclick="openNoteModal()" 
                                class="bg-[#D46E15] hover:bg-[#B85207] text-white text-xs font-semibold px-4 py-2.5 rounded-xl flex items-center gap-1.5 shadow-button-orange transition-colors self-start sm:self-auto">
                                <i class="fas fa-plus text-xs"></i>
                                <span>Tambah Catatan</span>
                            </button>
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-3 gap-4" id="kitchenNotesList">
                            <?php foreach ($kitchen_notes as $note): ?>
                                <div class="bg-[#EFE8DE] rounded-xl p-4 border border-[#DDD3C5]">
                                    <div class="flex items-center justify-between text-xs font-bold mb-1.5">
                                        <span class="<?= $note['tag_class'] ?>"><?= htmlspecialchars($note['tag']) ?></span>
                                        <span class="text-[#8E7E73] font-normal"><?= htmlspecialchars($note['time']) ?></span>
                                    </div>
                                    <p class="text-xs text-[#544337] leading-relaxed">
                                        <?= htmlspecialchars($note['text']) ?>
                                    </p>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    </div>

                </div>

                <!-- ============================================================== -->
                <!-- TAB CONTENT 2: KALENDER PENGIRIMAN                             -->
                <!-- ============================================================== -->
                <div id="calendarView" class="<?= $active_tab !== 'kalender' ? 'hidden' : '' ?>">
                    
                    <!-- Controls Row: Month Selector & Filters -->
                    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 mb-6">
                        <div class="flex flex-wrap items-center gap-3">
                            <!-- Dropdown Bulan Dinamis -->
                            <div class="relative">
                                <select id="calendarMonthSelect" onchange="filterCalendarByMonth(this.value)" class="appearance-none bg-[#EFE8DE] border border-[#DDD3C5] rounded-lg pl-3.5 pr-9 py-2 text-sm text-[#251A14] font-semibold focus:outline-none focus:border-[#D46E15] cursor-pointer shadow-2xs">
                                    <option value="<?= date('Y-m') ?>"><?= $currentMonthIndo ?></option>
                                    <option value="<?= date('Y-m', strtotime('-1 month')) ?>"><?= ($monthsIndoFull[date('m', strtotime('-1 month'))] ?? '') . ' ' . date('Y', strtotime('-1 month')) ?></option>
                                    <option value="<?= date('Y-m', strtotime('+1 month')) ?>"><?= ($monthsIndoFull[date('m', strtotime('+1 month'))] ?? '') . ' ' . date('Y', strtotime('+1 month')) ?></option>
                                </select>
                                <div class="absolute inset-y-0 right-0 pr-3 flex items-center pointer-events-none text-[#8E7E73]">
                                    <i class="fas fa-chevron-down text-xs"></i>
                                </div>
                            </div>

                            <!-- Dropdown Tipe Pengiriman -->
                            <div class="relative">
                                <select id="shippingTypeSelect" onchange="filterShippingType(this.value)" class="appearance-none bg-[#EFE8DE] border border-[#DDD3C5] rounded-lg pl-3.5 pr-9 py-2 text-sm text-[#251A14] font-medium focus:outline-none focus:border-[#D46E15] cursor-pointer shadow-2xs">
                                    <option value="all">Semua Pengiriman</option>
                                    <option value="kurir">Kurir Toko (Obrok)</option>
                                    <option value="toko">Ambil di Toko</option>
                                </select>
                                <div class="absolute inset-y-0 right-0 pr-3 flex items-center pointer-events-none text-[#8E7E73]">
                                    <i class="fas fa-chevron-down text-xs"></i>
                                </div>
                            </div>

                            <!-- Badge 3 Hari Pengiriman Aktif -->
                            <span class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-full text-xs font-semibold bg-[#EFE8DE] text-[#D46E15] border border-[#DDD3C5]">
                                <i class="fas fa-motorcycle text-xs"></i>
                                <span>Kapasitas Trip: Maks. 150 pcs/Motor</span>
                            </span>
                        </div>

                        <!-- Button Tambah Pesanan -->
                        <a href="pesanan.php?view=tambah_pesanan" class="bg-[#D46E15] hover:bg-[#B85207] text-white text-sm font-semibold px-4 py-2.5 rounded-lg flex items-center gap-2 transition-all shadow-button-orange active:scale-95 shrink-0">
                            <i class="fas fa-plus text-xs"></i>
                            <span>Tambah Pesanan</span>
                        </a>
                    </div>

                    <!-- Main Big Calendar Card -->
                    <div class="bg-[#FDFBF7] rounded-2xl border border-[#E2D7C8] p-6 shadow-sm mb-6">
                        <!-- Calendar Header: Month Title & Legend -->
                        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 pb-4 border-b border-[#E2D7C8] mb-4">
                            <div class="flex items-center gap-3">
                                <h3 class="text-[#251A14] font-bold text-lg" id="calendarGridTitle"><?= $currentMonthIndo ?></h3>
                                <div class="flex items-center gap-1">
                                    <button onclick="prevCalendarMonth()" class="p-1.5 hover:bg-[#EFE8DE] rounded-lg text-[#8E7E73] hover:text-[#251A14] transition-colors" title="Bulan Sebelumnya">
                                        <i class="fas fa-chevron-left text-xs"></i>
                                    </button>
                                    <button onclick="nextCalendarMonth()" class="p-1.5 hover:bg-[#EFE8DE] rounded-lg text-[#8E7E73] hover:text-[#251A14] transition-colors" title="Bulan Berikutnya">
                                        <i class="fas fa-chevron-right text-xs"></i>
                                    </button>
                                </div>
                            </div>

                            <!-- Legend Pills -->
                            <div class="flex items-center gap-4 text-xs">
                                <div class="flex items-center gap-1.5">
                                    <span class="w-2.5 h-2.5 rounded-full bg-[#D46E15]"></span>
                                    <span class="text-[#544337] font-medium">Aktif</span>
                                </div>
                                <div class="flex items-center gap-1.5">
                                    <span class="w-2.5 h-2.5 rounded-full border-2 border-[#D46E15] bg-[#FDFBF7]"></span>
                                    <span class="text-[#544337] font-medium">Hari Ini</span>
                                </div>
                                <div class="flex items-center gap-1.5">
                                    <span class="w-2.5 h-2.5 rounded-full bg-[#27AE60]"></span>
                                    <span class="text-[#544337] font-medium">Selesai</span>
                                </div>
                            </div>
                        </div>

                        <!-- Day Names Header (SEN - MIN) -->
                        <div class="grid grid-cols-7 text-center text-xs font-bold text-[#8E7E73] uppercase tracking-wider py-2 border-b border-[#E2D7C8]">
                            <div>SEN</div>
                            <div>SEL</div>
                            <div>RAB</div>
                            <div>KAM</div>
                            <div>JUM</div>
                            <div>SAB</div>
                            <div>MIN</div>
                        </div>

                        <!-- Calendar Dates Grid (Interactive on click) -->
                        <div class="grid grid-cols-7 gap-2 pt-3 text-center" id="calendarDaysGrid">
                            <!-- Generated dynamically by JS -->
                        </div>

                        <!-- Calendar Footer Note -->
                        <div class="pt-4 mt-4 border-t border-[#E2D7C8] flex items-center gap-2 text-xs text-[#8E7E73]">
                            <i class="fas fa-info-circle text-[#D46E15] text-xs"></i>
                            <span>Klik tanggal mana saja pada kalender untuk melihat daftar pengiriman roti dan status kurir.</span>
                        </div>
                    </div>

                    <!-- Bottom Delivery Schedule (Interactive Filtered View) -->
                    <div class="bg-[#FDFBF7] rounded-2xl border border-[#E2D7C8] p-6 shadow-sm" id="deliveryScheduleSection">
                        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-2 pb-4 border-b border-[#E2D7C8] mb-5">
                            <div class="flex items-center gap-2.5">
                                <h3 class="text-[#251A14] font-bold text-base" id="deliveryScheduleTitle">Jadwal Pengiriman: <?= $todayFormatted ?></h3>
                                <span class="bg-[#27AE60]/15 text-[#1B7A43] text-xs font-semibold px-2.5 py-0.5 rounded-full" id="deliveryBatchBadge">
                                    2 Batch Siap
                                </span>
                            </div>
                            <span class="text-xs text-[#8E7E73]" id="deliveryCountLabel">2 Pengiriman Terjadwal</span>
                        </div>

                        <!-- 3 Cards Grid -->
                        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-5" id="deliveryCardsGrid">
                            
                            <!-- Card 1: Ibu Sari -->
                            <div class="bg-[#EFE8DE] rounded-xl p-4 border border-[#DDD3C5] flex flex-col justify-between delivery-card" data-type="kurir">
                                <div>
                                    <div class="flex items-center justify-between text-xs mb-2">
                                        <span class="font-mono text-[#D46E15] font-semibold">#ORD-089</span>
                                        <div class="flex items-center gap-1.5">
                                            <span class="text-[#8E7E73]"><i class="far fa-clock text-[10px]"></i> 14:00 WIB</span>
                                            <span class="bg-[#8E7E73]/15 text-[#544337] px-2 py-0.5 rounded-full text-[10px] font-semibold">Belum Bayar</span>
                                        </div>
                                    </div>
                                    <h4 class="text-[#251A14] font-bold text-sm">Ibu Sari</h4>
                                    <p class="text-xs text-[#544337] mt-1">Roti Hajatan Putih 10 box, Bolu Pisang 2 mika</p>
                                </div>
                                <div class="flex items-center justify-between pt-3 mt-3 border-t border-[#DDD3C5] text-xs">
                                    <span class="bg-[#D46E15]/15 text-[#B85207] font-semibold px-2.5 py-1 rounded-md text-[11px]"><i class="fas fa-motorcycle text-xs mr-1"></i> Kurir Toko (Siap Antar)</span>
                                    <button type="button" onclick="openOrderDetailById('ORD-089')" class="text-[#D46E15] hover:text-[#B85207] font-semibold flex items-center gap-1">
                                        <span>Detail</span>
                                        <i class="fas fa-arrow-right text-xs"></i>
                                    </button>
                                </div>
                            </div>

                            <!-- Card 2: Bu Rina -->
                            <div class="bg-[#EFE8DE] rounded-xl p-4 border border-[#DDD3C5] flex flex-col justify-between delivery-card" data-type="toko">
                                <div>
                                    <div class="flex items-center justify-between text-xs mb-2">
                                        <span class="font-mono text-[#27AE60] font-semibold">#ORD-084</span>
                                        <div class="flex items-center gap-1.5">
                                            <span class="text-[#8E7E73]"><i class="far fa-clock text-[10px]"></i> 16:30 WIB</span>
                                            <span class="bg-[#27AE60]/15 text-[#1B7A43] px-2 py-0.5 rounded-full text-[10px] font-semibold">Lunas</span>
                                        </div>
                                    </div>
                                    <h4 class="text-[#251A14] font-bold text-sm">Bu Rina</h4>
                                    <p class="text-xs text-[#544337] mt-1">Bolu Pisang Mika 2 mika, Roti Bijian Besar 20 pcs</p>
                                </div>
                                <div class="flex items-center justify-between pt-3 mt-3 border-t border-[#DDD3C5] text-xs">
                                    <span class="bg-[#E5DDD0] text-[#544337] font-semibold px-2.5 py-1 rounded-md text-[11px]"><i class="fas fa-store text-xs mr-1"></i> Ambil di Toko</span>
                                    <button type="button" onclick="openOrderDetailById('ORD-084')" class="text-[#D46E15] hover:text-[#B85207] font-semibold flex items-center gap-1">
                                        <span>Detail</span>
                                        <i class="fas fa-arrow-right text-xs"></i>
                                    </button>
                                </div>
                            </div>

                            <!-- Card 3: Action Panel -->
                            <div class="bg-[#EFE8DE] rounded-xl p-4 border border-[#DDD3C5] flex flex-col justify-between">
                                <div>
                                    <h4 class="text-[#251A14] font-bold text-sm mb-1">Aksi Cepat Pengiriman</h4>
                                    <p class="text-xs text-[#8E7E73] leading-relaxed">Perbarui status semua pesanan hari ini atau cetak dokumen surat jalan untuk kurir.</p>
                                </div>
                                <div class="space-y-2 mt-4">
                                    <!-- Konfirmasi Semua Siap Kirim Riil -->
                                    <form method="POST" action="pesanan.php">
                                        <input type="hidden" name="action" value="batch_siap_kirim">
                                        <button type="submit" class="w-full bg-[#D46E15] hover:bg-[#B85207] text-white font-semibold text-xs py-2.5 rounded-lg flex items-center justify-center gap-1.5 shadow-button-orange transition-colors">
                                            <i class="fas fa-check-double text-xs"></i>
                                            <span>Konfirmasi Semua Siap Kirim</span>
                                        </button>
                                    </form>
                                    
                                    <!-- Cetak Rekap Surat Jalan (Hari Ini) -->
                                    <button type="button" onclick="printDeliverySlip()" 
                                        class="w-full bg-[#FDFBF7] hover:bg-[#E5DDD0] text-[#3E2B21] font-medium text-xs py-2.5 rounded-lg border border-[#DDD3C5] flex items-center justify-center gap-1.5 transition-colors">
                                        <i class="fas fa-print text-xs text-[#D46E15]"></i>
                                        <span>Cetak Rekap Surat Jalan (Hari Ini)</span>
                                    </button>
                                </div>
                            </div>

                        </div>
                    </div>

                </div>

                <?php endif; ?>

            </main>
        </div>
    </div>

    <!-- ============================================================== -->
    <!-- ============================================================== -->
    <!-- MODAL POPOUT: TAMBAH PESANAN BARU (COMPACT & STICKY FOOTER)   -->
    <!-- ============================================================== -->
    <div id="orderModal" class="fixed inset-0 z-50 hidden flex items-center justify-center p-4">
        <!-- Backdrop with blur -->
        <div onclick="closeOrderModal()" class="fixed inset-0 bg-black/50 backdrop-blur-xs transition-opacity duration-200"></div>

        <!-- Modal Card Container -->
        <div class="bg-[#FDFBF7] rounded-2xl max-w-[740px] w-full shadow-2xl relative z-10 animate-in fade-in zoom-in-95 duration-150 flex flex-col max-h-[90vh] overflow-hidden border border-[#E2D7C8]">
            
            <!-- Sticky Header (Compact) -->
            <div class="px-6 py-4 border-b border-[#E2D7C8] flex items-center justify-between shrink-0 bg-[#FDFBF7]">
                <div>
                    <h3 class="text-lg font-bold text-[#251A14] tracking-tight">Tambah Pesanan Baru</h3>
                    <p class="text-[#8E7E73] text-xs mt-0.5">Isi detail pesanan, rincian produk bakery, dan jadwal pengiriman.</p>
                </div>
                <button onclick="closeOrderModal()" class="text-[#8E7E73] hover:text-[#251A14] p-1.5 rounded-lg hover:bg-[#EFE8DE] transition-colors" title="Tutup" aria-label="Tutup Modal">
                    <i class="fas fa-times text-base"></i>
                </button>
            </div>

            <!-- Form with flex structure -->
            <form id="orderForm" method="POST" action="pesanan.php" onsubmit="handleSaveOrder(event)" class="flex flex-col flex-1 overflow-hidden">
                <input type="hidden" name="action" value="add_order">
                <input type="hidden" name="grand_total" id="hiddenGrandTotal" value="144000">
                <input type="hidden" name="items_desc" id="hiddenItemsDesc" value="Roti Hajatan Putih (10 box), Bolu Pisang Mika (2 mika)">
                <input type="hidden" name="payment_status" id="selectedPaymentStatus" value="Belum Bayar">

                <!-- Scrollable Body Content -->
                <div class="px-6 py-4 overflow-y-auto flex-1 space-y-4">

                    <!-- SECTION 1: DATA PELANGGAN -->
                    <div>
                        <div class="flex items-center justify-between mb-2">
                            <label class="text-xs font-bold text-[#251A14] uppercase tracking-wider">Data Pelanggan</label>
                            <button type="button" onclick="toggleNewCustomerForm()" id="btnToggleNewCust" 
                                class="text-xs font-semibold text-[#D46E15] hover:text-[#B85207] flex items-center gap-1.5 transition-colors">
                                <i class="fas fa-user-plus text-xs"></i>
                                <span id="labelToggleNewCust">Tambah Pelanggan Baru</span>
                            </button>
                        </div>
                        
                        <!-- Search Bar with Live Autocomplete -->
                        <div id="custSearchWrapper" class="relative mb-2.5">
                            <div class="relative">
                                <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-[#8E7E73]">
                                    <i class="fas fa-search text-xs"></i>
                                </div>
                                <input type="text" id="custSearchInput" name="customer_name" placeholder="Ketik nama atau No. WhatsApp pelanggan..." autocomplete="off"
                                    oninput="handleCustomerSearch(this.value)" onfocus="handleCustomerSearchFocus()"
                                    value="<?= htmlspecialchars($customers_for_select[0]['nama'] ?? 'Ibu Sari Dewi') ?>"
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
                            <h4 class="text-xs font-bold text-[#251A14] uppercase tracking-wider">Detail Produk & Minimal Order</h4>
                            <span class="text-xs text-[#8E7E73]" id="itemCountLabel">2 Item Dipilih</span>
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
                                <tbody id="orderItemsTableBody" class="divide-y divide-[#EAE1D5] bg-[#FDFBF7]">
                                    <!-- Row 1: Roti Hajatan (Pricelist 10k) -->
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
                                                class="item-qty-input w-16 px-2 py-1 bg-[#EFE8DE] border border-[#DDD3C5] text-[#1C1C1C] font-semibold rounded text-center focus:outline-none focus:border-[#D46E15] transition-colors">
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

                                    <!-- Row 2: Bolu Pisang Premium (Pricelist 22k) -->
                                    <tr class="order-product-row" data-min-order="1" data-price="22000">
                                        <td class="px-3.5 py-3">
                                            <input type="hidden" name="items[1][nama]" value="Bolu Pisang Premium" class="item-name-input">
                                            <input type="hidden" name="items[1][id_varian]" value="7" class="item-varian-input">
                                            <p class="font-bold text-[#1C1C1C] item-title">Bolu Pisang Premium</p>
                                            <p class="text-[10px] text-[#8E7E73] item-moq-label">Ready Fresh</p>
                                        </td>
                                        <td class="px-3.5 py-3 text-[#544337]">
                                            <span class="item-kemasan-text">Mika</span>
                                            <input type="hidden" name="items[1][kemasan]" value="Mika" class="item-kemasan-input">
                                        </td>
                                        <td class="px-3.5 py-3">
                                            <input type="number" name="items[1][qty]" value="2" min="1" oninput="calculateOrderTotal()" 
                                                class="item-qty-input w-16 px-2 py-1 bg-[#EFE8DE] border border-[#DDD3C5] text-[#1C1C1C] font-semibold rounded text-center focus:outline-none focus:border-[#D46E15] transition-colors">
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

                        <!-- Button Tambah Produk Baru Dinamis -->
                        <div class="flex items-center justify-between mb-4">
                            <button type="button" onclick="addOrderProductRow()" 
                                class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg border border-dashed border-[#D46E15] text-[#D46E15] hover:bg-[#D46E15]/10 text-xs font-semibold transition-all">
                                <i class="fas fa-plus text-[10px]"></i>
                                <span>Tambah Produk</span>
                            </button>
                            <span class="text-[11px] text-[#8E7E73]">Pilih varian atau produk lain dari katalog</span>
                        </div>

                        <!-- Validation Callout Alert (MOQ Warning) -->
                        <div id="moqAlertBox" class="bg-rose-50 border border-rose-200 text-rose-800 rounded-xl p-3 flex items-start gap-2.5 hidden">
                            <i class="fas fa-lock text-rose-600 mt-0.5 shrink-0"></i>
                            <p class="text-xs text-rose-800 leading-relaxed" id="moqAlertMessage">
                                <strong>Pesanan Kosong</strong> — Masukkan minimal 1 box produk sebelum menyimpan pesanan.
                            </p>
                        </div>
                    </div>

                    <!-- SECTION 3: PENGIRIMAN & PEMBAYARAN -->
                    <div>
                        <h4 class="text-xs font-bold text-[#251A14] uppercase tracking-wider mb-3">Pengiriman & Pembayaran</h4>
                        
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                            
                            <!-- Left: Tanggal & Waktu & Catatan -->
                            <div class="space-y-3.5">
                                <div class="grid grid-cols-2 gap-2.5">
                                    <div>
                                        <div class="flex items-center justify-between mb-1">
                                            <label for="deliveryDateInput" class="block text-xs font-semibold text-[#3E2B21]">Tanggal Kirim (PO)</label>
                                            <span class="text-[10px] text-[#D46E15] font-medium"><i class="fas fa-info-circle mr-0.5"></i> Min. Hari Ini</span>
                                        </div>
                                        <input type="date" id="deliveryDateInput" name="delivery_date" value="<?= date('Y-m-d') ?>" min="<?= date('Y-m-d') ?>" onchange="validatePesananDeliveryDate(this)"
                                            class="w-full bg-[#EFE8DE] border border-[#DDD3C5] rounded-lg px-3 py-2 text-xs text-[#251A14] focus:border-[#D46E15] focus:outline-none">
                                        <p id="pesananDateWarning" class="text-[10px] text-[#EB5757] mt-1 hidden"><i class="fas fa-exclamation-circle mr-1"></i>Tanggal tidak boleh lampau!</p>
                                    </div>
                                    <div>
                                        <label for="deliveryTimeInput" class="block text-xs font-semibold text-[#3E2B21] mb-1">Waktu Kirim</label>
                                        <input type="time" id="deliveryTimeInput" name="delivery_time" value="14:00" 
                                            class="w-full bg-[#EFE8DE] border border-[#DDD3C5] rounded-lg px-3 py-2 text-xs text-[#251A14] focus:border-[#D46E15] focus:outline-none">
                                    </div>
                                </div>

                                <div>
                                    <label for="deliveryDestinationInput" class="block text-xs font-semibold text-[#3E2B21] mb-1">Alamat Tujuan Pengiriman</label>
                                    <input type="text" id="deliveryDestinationInput" name="destination" value="Dikirim ke Komplek Melati No. 4" 
                                        class="w-full bg-[#EFE8DE] border border-[#DDD3C5] rounded-lg px-3 py-2 text-xs text-[#251A14] focus:border-[#D46E15] focus:outline-none">
                                </div>

                                <div>
                                    <label for="orderNotesTextarea" class="block text-xs font-semibold text-[#3E2B21] mb-1">Catatan Dapur / Driver</label>
                                    <textarea id="orderNotesTextarea" name="notes" rows="2" 
                                        class="w-full bg-[#EFE8DE] border border-[#DDD3C5] rounded-lg p-3 text-xs text-[#251A14] focus:border-[#D46E15] focus:outline-none">Roti hajatan kardus putih diikat rapi, kurir motor obrok tiba sebelum 15:00.</textarea>
                                </div>
                            </div>

                            <!-- Right: Total Tagihan & Status Bayar -->
                            <div class="bg-[#EFE8DE] border border-[#DDD3C5] rounded-2xl p-4 flex flex-col justify-between">
                                <div>
                                    <p class="text-[11px] font-bold text-[#8E7E73] uppercase tracking-wider">TOTAL TAGIHAN</p>
                                    <h3 class="text-2xl font-bold text-[#D46E15] mt-0.5 tracking-tight" id="orderGrandTotal">Rp 144.000</h3>
                                    
                                    <div class="mt-4">
                                        <label class="block text-xs font-semibold text-[#3E2B21] mb-2">Status Pembayaran</label>
                                        <div class="grid grid-cols-3 gap-2">
                                            <button type="button" onclick="selectPaymentStatus('Belum Bayar', this)" id="payBtn1" 
                                                class="py-1.5 px-2 rounded-lg text-xs font-semibold bg-[#3E2B21] text-[#FAF8F5] shadow-2xs transition-colors">
                                                Belum Bayar
                                            </button>
                                            <button type="button" onclick="selectPaymentStatus('Hutang', this)" id="payBtn2" 
                                                class="py-1.5 px-2 rounded-lg text-xs font-semibold bg-[#FDFBF7] border border-[#DDD3C5] text-[#544337] hover:bg-[#E5DDD0] transition-colors">
                                                Hutang
                                            </button>
                                            <button type="button" onclick="selectPaymentStatus('Lunas', this)" id="payBtn3" 
                                                class="py-1.5 px-2 rounded-lg text-xs font-semibold bg-[#FDFBF7] border border-[#DDD3C5] text-[#544337] hover:bg-[#E5DDD0] transition-colors">
                                                Lunas
                                            </button>
                                        </div>
                                    </div>
                                </div>

                                <div class="mt-4">
                                    <label for="paymentNoteInput" class="block text-xs font-semibold text-[#3E2B21] mb-1">Metode Pembayaran</label>
                                    <input type="text" id="paymentNoteInput" name="payment_note" value="Transfer BCA / QRIS" 
                                        class="w-full bg-[#FDFBF7] border border-[#DDD3C5] rounded-lg px-3 py-2 text-xs text-[#251A14] focus:border-[#D46E15] focus:outline-none">
                                </div>
                            </div>

                        </div>
                    </div>

                </div>
                <!-- /Scrollable Body Content -->

                <!-- STICKY MODAL FOOTER (PINNED TO BOTTOM) -->
                <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3 px-6 py-3.5 border-t border-[#E2D7C8] bg-[#F5F1EB] shrink-0">
                    <div id="footerMoqWarning" class="flex items-center gap-1.5 text-xs text-[#27AE60] font-medium">
                        <i class="fas fa-check-circle text-xs"></i>
                        <span>Minimum order terpenuhi</span>
                    </div>

                    <div class="flex items-center gap-3 ml-auto">
                        <button type="button" onclick="closeOrderModal()" 
                            class="bg-[#EFE8DE] hover:bg-[#E5DDD0] text-[#3E2B21] text-xs font-semibold px-4 py-2.5 rounded-lg border border-[#DDD3C5] transition-colors">
                            Batal
                        </button>

                        <button type="submit" id="submitOrderBtn" 
                            class="bg-[#D46E15] hover:bg-[#B85207] text-white text-xs font-semibold px-5 py-2.5 rounded-lg flex items-center gap-1.5 transition-all shadow-button-orange active:scale-95">
                            <i class="fas fa-check text-xs"></i>
                            <span>Simpan Pesanan</span>
                        </button>
                    </div>
                </div>

            </form>
        </div>
    </div>

    <!-- ============================================================== -->
    <!-- MODAL POPOUT: DETAIL PESANAN & STATUS WORKFLOW                 -->
    <!-- ============================================================== -->
    <div id="orderDetailModal" class="fixed inset-0 z-50 hidden flex items-center justify-center p-4">
        <div onclick="closeOrderDetailModal()" class="fixed inset-0 bg-black/50 backdrop-blur-xs transition-opacity duration-200"></div>

        <div class="bg-[#FDFBF7] rounded-2xl max-w-lg w-full p-6 shadow-2xl relative z-10 animate-in fade-in zoom-in-95 duration-150 border border-[#E2D7C8]">
            <div class="flex items-start justify-between pb-3 border-b border-[#E2D7C8] mb-4">
                <div>
                    <div class="flex items-center gap-2">
                        <span class="font-mono text-[#D46E15] font-bold text-base" id="modalDetailOrderId">#ORD-000</span>
                        <span class="text-xs px-2.5 py-0.5 rounded-full font-semibold" id="modalDetailStatusBadge">Menunggu</span>
                    </div>
                    <h3 class="text-lg font-bold text-[#251A14] mt-1" id="modalDetailCustomer">Nama Pelanggan</h3>
                </div>
                <button onclick="closeOrderDetailModal()" class="text-[#8E7E73] hover:text-[#251A14] p-1.5 rounded-lg hover:bg-[#EFE8DE]">
                    <i class="fas fa-times text-base"></i>
                </button>
            </div>

            <div class="space-y-3.5 text-xs">
                <div class="bg-[#EFE8DE] p-3.5 rounded-xl border border-[#DDD3C5] space-y-2">
                    <div class="flex justify-between">
                        <span class="text-[#8E7E73]">Rincian Item:</span>
                        <span class="font-semibold text-[#251A14]" id="modalDetailItems">-</span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-[#8E7E73]">Tujuan Pengiriman:</span>
                        <span class="font-semibold text-[#251A14]" id="modalDetailDestination">-</span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-[#8E7E73]">Waktu Pengiriman:</span>
                        <span class="font-semibold text-[#251A14]" id="modalDetailTime">-</span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-[#8E7E73]">Status Bayar:</span>
                        <span class="font-bold text-[#251A14]" id="modalDetailPayStatus">-</span>
                    </div>
                </div>

                <!-- Update Status Alur Kerja -->
                <div>
                    <label class="block font-semibold text-[#3E2B21] mb-2">Ubah Status Alur Kerja:</label>
                    <div class="grid grid-cols-4 gap-2">
                        <button type="button" onclick="changeModalOrderStatus('menunggu')" class="py-2 px-1 text-center font-bold rounded-lg border border-[#E2B93B] text-[#886C12] bg-[#E2B93B]/10 hover:bg-[#E2B93B]/20 text-[11px]">
                            Menunggu
                        </button>
                        <button type="button" onclick="changeModalOrderStatus('proses')" class="py-2 px-1 text-center font-bold rounded-lg border border-[#D46E15] text-[#B85207] bg-[#D46E15]/10 hover:bg-[#D46E15]/20 text-[11px]">
                            Oven/Dapur
                        </button>
                        <button type="button" onclick="changeModalOrderStatus('kirim')" class="py-2 px-1 text-center font-bold rounded-lg border border-[#2F80ED] text-[#1B57A6] bg-[#2F80ED]/10 hover:bg-[#2F80ED]/20 text-[11px]">
                            Siap Kirim
                        </button>
                        <button type="button" onclick="changeModalOrderStatus('selesai')" class="py-2 px-1 text-center font-bold rounded-lg border border-[#27AE60] text-[#1B7A43] bg-[#27AE60]/10 hover:bg-[#27AE60]/20 text-[11px]">
                            Selesai
                        </button>
                    </div>
                </div>
            </div>

            <div class="pt-4 mt-5 border-t border-[#E2D7C8] flex justify-end">
                <button type="button" onclick="closeOrderDetailModal()" class="bg-[#EFE8DE] hover:bg-[#E5DDD0] text-[#3E2B21] px-4 py-2 rounded-lg text-xs font-semibold">
                    Tutup
                </button>
            </div>
        </div>
    </div>

    <!-- ============================================================== -->
    <!-- MODAL POPOUT: TAMBAH CATATAN DAPUR                             -->
    <!-- ============================================================== -->
    <div id="kitchenNoteModal" class="fixed inset-0 z-50 hidden flex items-center justify-center p-4">
        <div onclick="closeNoteModal()" class="fixed inset-0 bg-black/50 backdrop-blur-xs transition-opacity duration-200"></div>

        <div class="bg-[#FDFBF7] rounded-2xl max-w-md w-full p-6 shadow-2xl relative z-10 animate-in fade-in zoom-in-95 duration-150 border border-[#E2D7C8]">
            <div class="flex items-center justify-between pb-3 border-b border-[#E2D7C8] mb-4">
                <h3 class="text-base font-bold text-[#251A14]">Tambah Catatan / Memo Dapur</h3>
                <button onclick="closeNoteModal()" class="text-[#8E7E73] hover:text-[#251A14] p-1">
                    <i class="fas fa-times text-base"></i>
                </button>
            </div>

            <form method="POST" action="pesanan.php" class="space-y-4">
                <input type="hidden" name="action" value="add_kitchen_note">
                <div>
                    <label for="noteTagInput" class="block text-xs font-semibold text-[#3E2B21] mb-1">Judul / Tag Catatan</label>
                    <input type="text" id="noteTagInput" name="note_tag" placeholder="Contoh: ! Prioritas #ORD-089 atau Stok Coklat" required
                        class="w-full bg-[#EFE8DE] border border-[#DDD3C5] rounded-lg px-3 py-2 text-xs text-[#251A14] focus:border-[#D46E15] focus:outline-none">
                </div>

                <div>
                    <label for="noteTextInput" class="block text-xs font-semibold text-[#3E2B21] mb-1">Isi Memo Dapur</label>
                    <textarea id="noteTextInput" name="note_text" rows="3" placeholder="Tulis instruksi khusus untuk tim produksi dan kurir..." required
                        class="w-full bg-[#EFE8DE] border border-[#DDD3C5] rounded-lg p-3 text-xs text-[#251A14] focus:border-[#D46E15] focus:outline-none"></textarea>
                </div>

                <div class="flex items-center justify-end gap-2 pt-3 border-t border-[#E2D7C8]">
                    <button type="button" onclick="closeNoteModal()" class="px-4 py-2 text-xs font-semibold text-[#3E2B21] bg-[#EFE8DE] hover:bg-[#E5DDD0] rounded-lg border border-[#DDD3C5]">
                        Batal
                    </button>
                    <button type="submit" class="px-4 py-2 text-xs font-semibold text-white bg-[#D46E15] hover:bg-[#B85207] rounded-lg shadow-button-orange">
                        Simpan Catatan
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- ============================================================== -->
    <!-- MODAL POPOUT: LOGOUT / KELUAR AKUN                             -->
    <!-- ============================================================== -->
    <div id="logoutModal" class="fixed inset-0 z-50 hidden flex items-center justify-center p-4">
        <!-- Backdrop with subtle blur -->
        <div onclick="closeLogoutModal()" class="fixed inset-0 craft-modal-overlay"></div>

        <!-- Modal Card Container -->
        <div class="craft-modal-dialog max-w-sm w-full p-6 sm:p-7 relative z-10 text-center">
            <!-- Top Icon -->
            <div class="w-12 h-12 rounded-xl bg-rose-50 border border-rose-200/60 text-rose-600 flex items-center justify-center mx-auto mb-4">
                <i class="fas fa-sign-out-alt text-xl"></i>
            </div>

            <!-- Title -->
            <h3 class="text-base sm:text-lg font-bold text-[#251A14] tracking-tight">Keluar dari Akun?</h3>
            <p class="text-xs text-[#8E7E73] mt-1.5 mb-6 leading-relaxed max-w-[280px] mx-auto">
                Anda akan keluar dari sesi ini. Pastikan semua perubahan sudah tersimpan.
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
        </div>
    </div>

    <!-- JAVASCRIPT LOGIC -->
    <script>
        // Data Pesanan & Master Pelanggan untuk Frontend Interactivity
        const ordersData = <?= json_encode($master_orders) ?>;
        const CUSTOMERS_DATA = <?= json_encode($customers_for_select, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP) ?>;
        const PRODUCTS_DATA = <?= json_encode($products_for_select, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP) ?>;
        let productRowCounter = 2;
        let selectedDetailOrderId = null;

        function escapeHtml(str) {
            if (!str) return '';
            return String(str).replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;').replace(/"/g, '&quot;').replace(/'/g, '&#039;');
        }

        // Customer Search & Autocomplete
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
            if (input) handleCustomerSearch(input.value);
        }

        function selectCustomerItem(id) {
            const cust = CUSTOMERS_DATA.find(c => c.id == id);
            if (!cust) return;

            document.getElementById('selectedCustomerId').value = cust.id;
            document.getElementById('custSearchInput').value = cust.nama;
            document.getElementById('previewCustName').textContent = cust.nama;
            document.getElementById('previewCustWa').textContent = cust.no_wa || '-';
            document.getElementById('previewCustAddress').textContent = cust.alamat || '-';
            document.getElementById('deliveryDestinationInput').value = cust.alamat || '';
            document.getElementById('custSearchResults').classList.add('hidden');
        }

        function toggleNewCustomerForm(forceOpen = false) {
            const box = document.getElementById('newCustomerFields');
            const btn = document.getElementById('labelToggleNewCust');
            const searchWrap = document.getElementById('custSearchWrapper');
            const resultsBox = document.getElementById('custSearchResults');
            if (resultsBox) resultsBox.classList.add('hidden');

            const willOpen = forceOpen ? true : box.classList.contains('hidden');
            if (willOpen) {
                box.classList.remove('hidden');
                btn.textContent = 'Gunakan Pelanggan Terdaftar';
                searchWrap.classList.add('opacity-40', 'pointer-events-none');
                syncNewCustomerPreview();
            } else {
                box.classList.add('hidden');
                btn.textContent = 'Tambah Pelanggan Baru';
                searchWrap.classList.remove('opacity-40', 'pointer-events-none');
                const defaultCust = CUSTOMERS_DATA[0] || { nama: 'Ibu Sari Dewi', no_wa: '0812-3456-7890', alamat: 'Komplek Melati No. 4, Blok B' };
                document.getElementById('previewCustName').textContent = defaultCust.nama;
                document.getElementById('previewCustWa').textContent = defaultCust.no_wa;
                document.getElementById('previewCustAddress').textContent = defaultCust.alamat;
                document.getElementById('deliveryDestinationInput').value = defaultCust.alamat;
            }
        }

        function cancelNewCustomerForm() {
            toggleNewCustomerForm(false);
        }

        function syncNewCustomerPreview() {
            const nama = document.getElementById('inputNamaBaru').value.trim() || 'Nama Pelanggan Baru';
            const wa = document.getElementById('inputWaBaru').value.trim() || '08xx-xxxx-xxxx';
            const alamat = document.getElementById('inputAlamatBaru').value.trim() || 'Alamat Pengiriman Lengkap';

            document.getElementById('previewCustName').textContent = nama;
            document.getElementById('previewCustWa').textContent = wa;
            document.getElementById('previewCustAddress').textContent = alamat;
            document.getElementById('deliveryDestinationInput').value = alamat;
        }

        document.addEventListener('click', function(e) {
            const searchWrapper = document.getElementById('custSearchWrapper');
            const resultsBox = document.getElementById('custSearchResults');
            if (searchWrapper && resultsBox && !searchWrapper.contains(e.target)) {
                resultsBox.classList.add('hidden');
            }
        });

        // Toggle Sidebar Mobile
        function toggleSidebar() {
            const sidebar = document.getElementById('main-sidebar');
            const overlay = document.getElementById('sidebar-overlay');
            if (sidebar.classList.contains('-translate-x-full')) {
                sidebar.classList.remove('-translate-x-full');
                overlay.classList.remove('hidden');
            } else {
                sidebar.classList.add('-translate-x-full');
                overlay.classList.add('hidden');
            }
        }

        // Header Notification & Profile Popovers
        function toggleNotificationDropdown() {
            const notif = document.getElementById('notificationDropdown');
            const user = document.getElementById('userDropdown');
            if (user) user.classList.add('hidden');
            notif.classList.toggle('hidden');
        }

        function toggleUserDropdown() {
            const user = document.getElementById('userDropdown');
            const notif = document.getElementById('notificationDropdown');
            if (notif) notif.classList.add('hidden');
            user.classList.toggle('hidden');
        }

        // Switch View Tabs (Kanban vs Kalender)
        function switchTab(tab) {
            const kanbanView = document.getElementById('kanbanView');
            const calendarView = document.getElementById('calendarView');
            const tabBtnKanban = document.getElementById('tabBtnKanban');
            const tabBtnKalender = document.getElementById('tabBtnKalender');
            const headerTitle = document.getElementById('headerTitle');
            const headerBreadcrumb = document.getElementById('headerBreadcrumb');
            const pageSubtitle = document.getElementById('pageSubtitle');

            if (tab === 'kalender') {
                kanbanView.classList.add('hidden');
                calendarView.classList.remove('hidden');

                tabBtnKalender.className = 'px-4 py-2 rounded-lg text-sm font-semibold flex items-center gap-2 transition-all bg-[#D46E15] text-white shadow-sm';
                tabBtnKanban.className = 'px-4 py-2 rounded-lg text-sm font-semibold flex items-center gap-2 transition-all text-[#544337] hover:text-[#251A14] hover:bg-[#E5DDD0]';

                headerTitle.textContent = 'Pengiriman';
                headerBreadcrumb.textContent = 'Kalendar';
                pageSubtitle.textContent = 'Pantau penjadwalan produksi dapur, rute kurir, dan pengiriman harian.';
                renderInteractiveCalendar();
            } else {
                calendarView.classList.add('hidden');
                kanbanView.classList.remove('hidden');

                tabBtnKanban.className = 'px-4 py-2 rounded-lg text-sm font-semibold flex items-center gap-2 transition-all bg-[#D46E15] text-white shadow-sm';
                tabBtnKalender.className = 'px-4 py-2 rounded-lg text-sm font-semibold flex items-center gap-2 transition-all text-[#544337] hover:text-[#251A14] hover:bg-[#E5DDD0]';

                headerTitle.textContent = 'Manage Pesanan';
                headerBreadcrumb.textContent = 'Manajemen';
                pageSubtitle.textContent = 'Kelola status dan alur pengiriman pesanan roti secara real-time';
            }
        }

        // =========================================================================
        // HTML5 DRAG & DROP FOR KANBAN SWIMLANES
        // =========================================================================
        let draggedOrderId = null;

        function drag(e, orderId) {
            draggedOrderId = orderId;
            e.dataTransfer.setData('text/plain', orderId);
            e.dataTransfer.effectAllowed = 'move';
        }

        function allowDrop(e) {
            e.preventDefault();
            e.currentTarget.classList.add('drag-over');
        }

        function dragLeave(e) {
            e.currentTarget.classList.remove('drag-over');
        }

        function drop(e, targetStatus) {
            e.preventDefault();
            e.currentTarget.classList.remove('drag-over');
            const orderId = e.dataTransfer.getData('text/plain') || draggedOrderId;
            if (!orderId) return;

            const card = document.getElementById('card-' + orderId);
            const targetContainer = document.getElementById('container-' + targetStatus);

            if (card && targetContainer) {
                targetContainer.appendChild(card);
                updateKanbanStatusBadge(card, targetStatus);
                recalculateKanbanCounts();
                saveStatusToServer(orderId, targetStatus);
                showToast(`Status #${orderId} dipindahkan ke ${targetStatus.toUpperCase()}!`);
            }
        }

        function updateKanbanStatusBadge(card, status) {
            card.classList.remove('border-l-[#E2B93B]', 'border-l-[#FF9B45]', 'border-l-[#2F80ED]', 'border-l-[#27AE60]');
            if (status === 'menunggu') card.classList.add('border-l-[#E2B93B]');
            else if (status === 'proses') card.classList.add('border-l-[#FF9B45]');
            else if (status === 'kirim') card.classList.add('border-l-[#2F80ED]');
            else if (status === 'selesai') card.classList.add('border-l-[#27AE60]');
        }

        function recalculateKanbanCounts() {
            const m = document.getElementById('container-menunggu').children.length;
            const p = document.getElementById('container-proses').children.length;
            const k = document.getElementById('container-kirim').children.length;
            const s = document.getElementById('container-selesai').children.length;

            document.getElementById('count-menunggu').textContent = m;
            document.getElementById('count-proses').textContent = p;
            document.getElementById('count-kirim').textContent = k;
            document.getElementById('count-selesai').textContent = s;

            document.getElementById('statMenunggu').textContent = m + p + k;
            document.getElementById('statSelesai').textContent = s;
        }

        function saveStatusToServer(orderId, status) {
            const formData = new FormData();
            formData.append('action', 'update_order_status');
            formData.append('order_id', orderId);
            formData.append('status', status);

            fetch('pesanan.php', {
                method: 'POST',
                headers: { 'X-Requested-With': 'XMLHttpRequest' },
                body: formData
            }).catch(err => console.error('Status sync error:', err));
        }

        function showToast(text, icon = '✓') {
            const toast = document.getElementById('dynamicToast');
            document.getElementById('dynamicToastText').textContent = text;
            document.getElementById('dynamicToastIcon').textContent = icon;
            toast.classList.remove('hidden');
            setTimeout(() => {
                toast.classList.add('hidden');
            }, 3000);
        }

        // =========================================================================
        // ORDER DETAIL MODAL
        // =========================================================================
        function openOrderDetail(ord) {
            selectedDetailOrderId = ord.id;
            document.getElementById('modalDetailOrderId').textContent = '#' + ord.id;
            document.getElementById('modalDetailCustomer').textContent = ord.customer;
            document.getElementById('modalDetailItems').textContent = ord.items;
            document.getElementById('modalDetailDestination').textContent = ord.destination;
            document.getElementById('modalDetailTime').textContent = ord.time;
            document.getElementById('modalDetailPayStatus').textContent = ord.status_bayar;
            
            const badge = document.getElementById('modalDetailStatusBadge');
            badge.textContent = (ord.current_status || 'Menunggu').toUpperCase();
            badge.className = 'text-xs px-2.5 py-0.5 rounded-full font-semibold bg-[#FF9B45]/15 text-[#924C00]';

            document.getElementById('orderDetailModal').classList.remove('hidden');
            document.body.style.overflow = 'hidden';
        }

        function openOrderDetailById(orderId) {
            const ord = ordersData.find(o => o.id === orderId);
            if (ord) openOrderDetail(ord);
        }

        function closeOrderDetailModal() {
            document.getElementById('orderDetailModal').classList.add('hidden');
            document.body.style.overflow = '';
        }

        function changeModalOrderStatus(status) {
            if (!selectedDetailOrderId) return;
            const card = document.getElementById('card-' + selectedDetailOrderId);
            const targetContainer = document.getElementById('container-' + status);

            if (card && targetContainer) {
                targetContainer.appendChild(card);
                updateKanbanStatusBadge(card, status);
                recalculateKanbanCounts();
            }
            saveStatusToServer(selectedDetailOrderId, status);
            showToast(`Status pesanan #${selectedDetailOrderId} diubah ke ${status.toUpperCase()}!`);
            closeOrderDetailModal();
        }

        // =========================================================================
        // INTERACTIVE CALENDAR
        // =========================================================================
        let currentCalDate = new Date();

        function renderInteractiveCalendar() {
            const grid = document.getElementById('calendarDaysGrid');
            if (!grid) return;
            grid.innerHTML = '';

            const year = currentCalDate.getFullYear();
            const month = currentCalDate.getMonth();
            const today = new Date();

            const monthNames = ['Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni', 'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember'];
            document.getElementById('calendarGridTitle').textContent = monthNames[month] + ' ' + year;

            const firstDay = new Date(year, month, 1).getDay();
            // Senin as first column (0 = Senin, 6 = Minggu)
            const startCol = (firstDay + 6) % 7;
            const totalDays = new Date(year, month + 1, 0).getDate();

            // Blanks
            for (let i = 0; i < startCol; i++) {
                grid.insertAdjacentHTML('beforeend', '<div class="p-2.5 text-sm text-[#D8D2CB] pointer-events-none"></div>');
            }

            // Days
            for (let d = 1; d <= totalDays; d++) {
                const isToday = (d === today.getDate() && month === today.getMonth() && year === today.getFullYear());
                const dateStr = `${year}-${String(month+1).padStart(2,'0')}-${String(d).padStart(2,'0')}`;
                
                // Cek pesanan pada tanggal ini dari ordersData
                const ordersOnDate = ordersData.filter(o => {
                    if (!o.date) return false;
                    return o.date === dateStr || (o.date.endsWith(`-${String(d).padStart(2, '0')}`) && o.date.includes(`-${String(month+1).padStart(2,'0')}-`));
                });
                const count = ordersOnDate.length;
                let dayTotalPcs = 0;
                ordersOnDate.forEach(ord => {
                    const matches = (ord.items || '').matchAll(/(\d+)\s*(pcs|box|mika|biji)/gi);
                    let pcs = 0;
                    for (const m of matches) pcs += parseInt(m[1], 10);
                    if (pcs === 0 && ord.total_qty) pcs = parseInt(ord.total_qty, 10);
                    dayTotalPcs += (pcs > 0 ? pcs : 10);
                });
                const isOverload = (dayTotalPcs > 150);

                let dayHtml = '';
                if (isToday) {
                    dayHtml = `
                        <div onclick="selectDeliveryDate(this, '${dateStr}', ${d})" 
                             class="cal-day-cell p-2.5 rounded-xl bg-[#FFF8F2] border-2 border-[#D46E15] flex flex-col items-center justify-between min-h-[64px] shadow-sm cursor-pointer hover:scale-105 transition-transform">
                            <span class="font-bold text-sm text-[#924C00]">${d}</span>
                            <span class="text-[10px] font-bold ${isOverload ? 'text-amber-950 bg-amber-300' : 'text-white bg-[#D46E15]'} px-1.5 py-0.5 rounded shadow-2xs">${isOverload ? '⚠️ ' + dayTotalPcs + ' pcs' : (count > 0 ? count + ' Pesanan' : 'Hari Ini')}</span>
                        </div>
                    `;
                } else if (count > 0) {
                    const allSelesai = ordersOnDate.every(o => o.current_status === 'selesai' || o.status === 'selesai');
                    if (allSelesai) {
                        dayHtml = `
                            <div onclick="selectDeliveryDate(this, '${dateStr}', ${d})" 
                                 class="cal-day-cell p-2.5 rounded-xl bg-[#F5F3F1] border border-[#EBE8E5] flex flex-col items-center justify-between min-h-[64px] cursor-pointer hover:bg-[#FFF8F2] transition-colors">
                                <span class="font-bold text-sm text-[#1C1C1C]">${d}</span>
                                <span class="text-[10px] font-semibold text-[#1B7A43] bg-[#27AE60]/15 px-1 py-0.5 rounded">✓ ${count} Selesai</span>
                            </div>
                        `;
                    } else if (isOverload) {
                        dayHtml = `
                            <div onclick="selectDeliveryDate(this, '${dateStr}', ${d})" 
                                 class="cal-day-cell p-2.5 rounded-xl bg-amber-50 border border-amber-300 flex flex-col items-center justify-between min-h-[64px] cursor-pointer hover:bg-[#FFF8F2] transition-colors">
                                <span class="font-bold text-sm text-amber-900">${d}</span>
                                <span class="text-[10px] font-bold text-amber-900 bg-amber-200 px-1.5 py-0.5 rounded shadow-2xs">⚠️ ${dayTotalPcs} pcs</span>
                            </div>
                        `;
                    } else {
                        dayHtml = `
                            <div onclick="selectDeliveryDate(this, '${dateStr}', ${d})" 
                                 class="cal-day-cell p-2.5 rounded-xl bg-[#FEF6EE] border border-[#FDE6D2] flex flex-col items-center justify-between min-h-[64px] cursor-pointer hover:bg-[#FFF8F2] transition-colors">
                                <span class="font-bold text-sm text-[#D97706]">${d}</span>
                                <span class="text-[10px] font-semibold text-[#D97706] bg-white px-1.5 py-0.5 rounded shadow-2xs">${count} Pesanan</span>
                            </div>
                        `;
                    }
                } else {
                    dayHtml = `
                        <div onclick="selectDeliveryDate(this, '${dateStr}', ${d})" 
                             class="cal-day-cell p-3 text-sm font-medium text-[#1C1C1C] rounded-xl hover:bg-[#FFF8F2] transition-colors cursor-pointer flex flex-col items-center justify-center min-h-[64px]">
                            <span>${d}</span>
                        </div>
                    `;
                }
                grid.insertAdjacentHTML('beforeend', dayHtml);
            }
        }

        function prevCalendarMonth() {
            currentCalDate.setMonth(currentCalDate.getMonth() - 1);
            renderInteractiveCalendar();
        }

        function nextCalendarMonth() {
            currentCalDate.setMonth(currentCalDate.getMonth() + 1);
            renderInteractiveCalendar();
        }

        function selectDeliveryDate(cell, dateStr, dayNum) {
            document.querySelectorAll('.cal-day-cell').forEach(c => c.classList.remove('ring-2', 'ring-[#D46E15]', 'border-[#D46E15]'));
            if (cell) {
                cell.classList.add('ring-2', 'ring-[#D46E15]');
            }

            const monthTitle = document.getElementById('calendarGridTitle').textContent;
            document.getElementById('deliveryScheduleTitle').textContent = `Jadwal Pengiriman: ${dayNum} ${monthTitle}`;
            
            // Filter matching orders from ordersData
            const matchingOrders = ordersData.filter(o => {
                if (!o.date) return false;
                return o.date === dateStr || (o.date.endsWith(`-${String(dayNum).padStart(2, '0')}`) && o.date.includes(`-${String(currentCalDate.getMonth()+1).padStart(2,'0')}-`));
            });

            let totalPcs = 0;
            matchingOrders.forEach(ord => {
                const matches = (ord.items || '').matchAll(/(\d+)\s*(pcs|box|mika|biji)/gi);
                let pcs = 0;
                for (const m of matches) pcs += parseInt(m[1], 10);
                if (pcs === 0 && ord.total_qty) pcs = parseInt(ord.total_qty, 10);
                totalPcs += (pcs > 0 ? pcs : 10);
            });
            const isDayOverload = (totalPcs > 150);

            const grid = document.getElementById('deliveryCardsGrid');
            const badge = document.getElementById('deliveryBatchBadge');
            const countLabel = document.getElementById('deliveryCountLabel');

            if (matchingOrders.length > 0) {
                if (isDayOverload) {
                    badge.textContent = `⚠️ Overload (${totalPcs}/150 pcs)`;
                    badge.className = 'bg-amber-100 text-amber-900 border border-amber-300 text-xs font-bold px-2.5 py-0.5 rounded-full';
                } else {
                    badge.textContent = `${matchingOrders.length} Batch Aktif (${totalPcs} pcs)`;
                    badge.className = 'bg-[#27AE60]/15 text-[#1B7A43] text-xs font-semibold px-2.5 py-0.5 rounded-full';
                }
                countLabel.textContent = `${matchingOrders.length} Pengiriman Terjadwal (${totalPcs} pcs)`;

                let cardsHtml = matchingOrders.map(ord => {
                    const isLunas = (ord.status_bayar === 'Lunas');
                    const bayarBadge = isLunas 
                        ? '<span class="bg-[#27AE60]/15 text-[#1B7A43] px-2 py-0.5 rounded-full text-[10px] font-semibold">Lunas</span>'
                        : '<span class="bg-[#8E7E73]/15 text-[#544337] px-2 py-0.5 rounded-full text-[10px] font-semibold">' + escapeHtml(ord.status_bayar || 'Belum Bayar') + '</span>';

                    const courierBadge = ord.courier_info 
                        ? `<span class="bg-[#D46E15]/15 text-[#B85207] font-semibold px-2.5 py-1 rounded-md text-[11px]"><i class="fas fa-motorcycle text-xs mr-1"></i> ${escapeHtml(ord.courier_info)}</span>`
                        : `<span class="bg-[#E5DDD0] text-[#544337] font-semibold px-2.5 py-1 rounded-md text-[11px]"><i class="fas fa-store text-xs mr-1"></i> Ambil di Toko</span>`;

                    return `
                        <div class="bg-[#EFE8DE] rounded-xl p-4 border border-[#DDD3C5] flex flex-col justify-between delivery-card" data-type="${ord.courier_info && ord.courier_info.toLowerCase().includes('ambil') ? 'toko' : 'kurir'}">
                            <div>
                                <div class="flex items-center justify-between text-xs mb-2">
                                    <span class="font-mono text-[#D46E15] font-semibold">#${escapeHtml(ord.id)}</span>
                                    <div class="flex items-center gap-1.5">
                                        <span class="text-[#8E7E73]"><i class="far fa-clock text-[10px]"></i> ${escapeHtml(ord.time || '14:00')}</span>
                                        ${bayarBadge}
                                    </div>
                                </div>
                                <h4 class="text-[#251A14] font-bold text-sm">${escapeHtml(ord.customer || 'Pelanggan')}</h4>
                                <p class="text-xs text-[#544337] mt-1">${escapeHtml(ord.items || '-')}</p>
                            </div>
                            <div class="flex items-center justify-between pt-3 mt-3 border-t border-[#DDD3C5] text-xs">
                                ${courierBadge}
                                <button type="button" onclick="openOrderDetailById('${escapeHtml(ord.id)}')" class="text-[#D46E15] hover:text-[#B85207] font-semibold flex items-center gap-1">
                                    <span>Detail</span>
                                    <i class="fas fa-arrow-right text-xs"></i>
                                </button>
                            </div>
                        </div>
                    `;
                }).join('');

                // Action panel card
                cardsHtml += `
                    <div class="bg-[#EFE8DE] rounded-xl p-4 border border-[#DDD3C5] flex flex-col justify-between">
                        <div>
                            <h4 class="text-[#251A14] font-bold text-sm mb-1">Aksi Cepat Pengiriman</h4>
                            <p class="text-xs text-[#8E7E73] leading-relaxed">Perbarui status semua pesanan tanggal ini atau cetak dokumen surat jalan untuk kurir obrok motor.</p>
                        </div>
                        <div class="space-y-2 mt-4">
                            <form method="POST" action="pesanan.php">
                                <input type="hidden" name="action" value="batch_siap_kirim">
                                <button type="submit" class="w-full bg-[#D46E15] hover:bg-[#B85207] text-white font-semibold text-xs py-2.5 rounded-lg flex items-center justify-center gap-1.5 shadow-button-orange transition-colors">
                                    <i class="fas fa-check-double text-xs"></i>
                                    <span>Konfirmasi Semua Siap Kirim</span>
                                </button>
                            </form>
                            <button type="button" onclick="printDeliverySlip()" class="w-full bg-[#FDFBF7] hover:bg-[#E5DDD0] text-[#3E2B21] font-medium text-xs py-2.5 rounded-lg border border-[#DDD3C5] flex items-center justify-center gap-1.5 transition-colors">
                                <i class="fas fa-print text-xs text-[#D46E15]"></i>
                                <span>Cetak Surat Jalan (${dayNum} ${monthTitle})</span>
                            </button>
                        </div>
                    </div>
                `;

                let overloadBanner = '';
                if (isDayOverload) {
                    const extraTrips = Math.ceil(totalPcs / 150);
                    overloadBanner = `
                        <div class="col-span-1 md:col-span-2 lg:col-span-3 bg-amber-50 border border-amber-300 rounded-xl p-3.5 mb-2 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3 text-xs text-amber-900 shadow-2xs">
                            <div class="flex items-center gap-3">
                                <div class="w-9 h-9 rounded-xl bg-amber-200 text-amber-800 flex items-center justify-center shrink-0 shadow-inner">
                                    <i class="fas fa-motorcycle text-base"></i>
                                </div>
                                <div>
                                    <p class="font-bold text-sm text-[#924C00]">Kapasitas Armada Kurir Obrok Overload!</p>
                                    <p class="text-amber-800/90 mt-0.5">Muatan tanggal ini mencapai <strong>${totalPcs} pcs</strong> (batas standar <strong>150 pcs per trip motor obrok</strong>). Diperlukan <strong>${extraTrips} trip perjalanan</strong> atau armada kurir tambahan.</p>
                                </div>
                            </div>
                            <span class="bg-amber-200 text-amber-900 font-bold px-3 py-1.5 rounded-lg text-xs uppercase tracking-wider shrink-0 text-center">Butuh ${extraTrips} Trip Motor</span>
                        </div>
                    `;
                }

                grid.innerHTML = overloadBanner + cardsHtml;
            } else {
                badge.textContent = 'Tidak Ada Pengiriman';
                badge.className = 'bg-[#8E7E73]/15 text-[#544337] text-xs font-semibold px-2.5 py-0.5 rounded-full';
                countLabel.textContent = '0 Pengiriman Terjadwal';

                grid.innerHTML = `
                    <div class="col-span-1 md:col-span-2 lg:col-span-3 bg-[#EFE8DE]/60 border border-dashed border-[#DDD3C5] rounded-2xl p-8 text-center flex flex-col items-center justify-center">
                        <div class="w-12 h-12 rounded-full bg-[#DDD3C5]/50 flex items-center justify-center text-[#8E7E73] mb-3 text-lg">
                            <i class="far fa-calendar-times"></i>
                        </div>
                        <h4 class="text-[#251A14] font-bold text-sm">Tidak Ada Jadwal Pengiriman</h4>
                        <p class="text-xs text-[#8E7E73] mt-1 max-w-md">Belum ada pesanan bakery yang dijadwalkan untuk dikirim pada tanggal ${dayNum} ${monthTitle}.</p>
                        <a href="pesanan.php?view=tambah_pesanan&date=${dateStr}" class="mt-4 bg-[#D46E15] hover:bg-[#B85207] text-white text-xs font-semibold px-4 py-2.5 rounded-lg inline-flex items-center gap-2 shadow-button-orange transition-colors">
                            <i class="fas fa-plus text-xs"></i>
                            <span>Tambah Pesanan Tanggal ${dayNum}</span>
                        </a>
                    </div>
                `;
            }

            showToast(`Jadwal pengiriman tanggal ${dayNum} ditampilkan`);
        }

        function filterShippingType(type) {
            const cards = document.querySelectorAll('.delivery-card');
            cards.forEach(c => {
                if (type === 'all' || c.getAttribute('data-type') === type) {
                    c.style.display = '';
                } else {
                    c.style.display = 'none';
                }
            });
        }

        // =========================================================================
        // REAL ACTIONS: EXPORT CSV & PRINT SURAT JALAN
        // =========================================================================
        function handleDownloadRecap() {
            let csv = "ID Pesanan,Pelanggan,Produk,Tujuan,Waktu,Status Bayar,Total\n";
            ordersData.forEach(o => {
                csv += `"${o.id}","${o.customer}","${o.items}","${o.destination}","${o.time}","${o.status_bayar}","${o.total}"\n`;
            });
            const blob = new Blob([csv], { type: 'text/csv;charset=utf-8;' });
            const url = URL.createObjectURL(blob);
            const link = document.createElement('a');
            link.setAttribute('href', url);
            link.setAttribute('download', `Rekap_Pesanan_Berkat_Dinasti_${new Date().toISOString().slice(0,10)}.csv`);
            document.body.appendChild(link);
            link.click();
            document.body.removeChild(link);
            showToast('Berkas rekap pesanan (CSV) berhasil diunduh!');
        }

        function printDeliverySlip() {
            window.print();
        }

        // Modal Open / Close Kitchen Note
        function openNoteModal() {
            document.getElementById('kitchenNoteModal').classList.remove('hidden');
            document.body.style.overflow = 'hidden';
        }

        function closeNoteModal() {
            document.getElementById('kitchenNoteModal').classList.add('hidden');
            document.body.style.overflow = '';
        }

        // Modal Open / Close Order Modal (Redirects to Full Page POS)
        function openOrderModal() {
            window.location.href = 'pesanan.php?view=tambah_pesanan';
        }

        function closeOrderModal() {
            const modal = document.getElementById('orderModal');
            if (modal) {
                modal.classList.add('hidden');
                document.body.style.overflow = '';
            }
        }

        function addOrderProductRow() {
            const tbody = document.getElementById('orderItemsTableBody');
            if (!tbody) return;
            const rowIndex = productRowCounter++;
            
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
                        class="w-full bg-[#EFE8DE] border border-[#DDD3C5] rounded px-2 py-1 text-xs font-semibold text-[#1C1C1C] focus:border-[#D46E15] focus:outline-none mb-1">
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
                        class="item-qty-input w-16 px-2 py-1 bg-[#EFE8DE] border border-[#DDD3C5] text-[#1C1C1C] font-semibold rounded text-center focus:outline-none focus:border-[#D46E15] transition-colors">
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

        // Calculation & MOQ Validation in Modal (Dynamic items)
        function calculateOrderTotal() {
            const rows = document.querySelectorAll('#orderItemsTableBody .order-product-row');
            let grandTotal = 0;
            let moqViolations = [];
            let itemDescriptions = [];

            rows.forEach(tr => {
                const qtyInput = tr.querySelector('.item-qty-input');
                const qty = parseInt(qtyInput ? qtyInput.value : 0) || 0;
                const price = parseFloat(tr.getAttribute('data-price')) || 0;
                const minOrder = parseInt(tr.getAttribute('data-min-order')) || 1;
                const subtotal = qty * price;
                grandTotal += subtotal;

                const subText = tr.querySelector('.item-subtotal-text');
                if (subText) subText.textContent = 'Rp ' + new Intl.NumberFormat('id-ID').format(subtotal);

                const nameInput = tr.querySelector('.item-name-input');
                const prodName = nameInput ? nameInput.value : 'Produk';

                if (qty > 0) {
                    itemDescriptions.push(`${prodName} (${qty} pcs)`);
                }

                if (minOrder > 1 && qty < minOrder) {
                    moqViolations.push({
                        name: prodName,
                        qty: qty,
                        minOrder: minOrder
                    });
                    if (qtyInput) qtyInput.className = 'item-qty-input w-16 px-2 py-1 border-2 border-[#EB5757] text-[#EB5757] font-bold rounded text-center focus:outline-none transition-colors';
                } else if (qty < 1) {
                    if (qtyInput) qtyInput.className = 'item-qty-input w-16 px-2 py-1 border-2 border-[#EB5757] text-[#EB5757] font-bold rounded text-center focus:outline-none transition-colors';
                } else {
                    if (qtyInput) qtyInput.className = 'item-qty-input w-16 px-2 py-1 bg-[#EFE8DE] border border-[#DDD3C5] text-[#1C1C1C] font-semibold rounded text-center focus:outline-none focus:border-[#D46E15] transition-colors';
                }
            });

            const countLabel = document.getElementById('itemCountLabel');
            if (countLabel) countLabel.textContent = `${rows.length} Item Dipilih`;

            const grandTotalElem = document.getElementById('orderGrandTotal');
            if (grandTotalElem) grandTotalElem.textContent = 'Rp ' + new Intl.NumberFormat('id-ID').format(grandTotal);

            const hiddenGrandTotal = document.getElementById('hiddenGrandTotal');
            if (hiddenGrandTotal) hiddenGrandTotal.value = grandTotal;

            const hiddenItemsDesc = document.getElementById('hiddenItemsDesc');
            if (hiddenItemsDesc) hiddenItemsDesc.value = itemDescriptions.join(', ');

            const alertBox = document.getElementById('moqAlertBox');
            const alertMsg = document.getElementById('moqAlertMessage');
            const submitBtn = document.getElementById('submitOrderBtn');
            const lockIcon = document.getElementById('submitLockIcon');
            const footerWarning = document.getElementById('footerMoqWarning');

            if (grandTotal <= 0) {
                if (alertBox) alertBox.classList.remove('hidden');
                if (alertMsg) alertMsg.innerHTML = '<strong>Pesanan Kosong</strong> — Masukkan minimal 1 box produk sebelum menyimpan pesanan.';
                if (submitBtn) {
                    submitBtn.disabled = true;
                    submitBtn.className = 'bg-[#D8D2CB] text-white cursor-not-allowed text-xs font-semibold px-5 py-2.5 rounded-lg flex items-center gap-1.5 transition-all shadow-xs';
                }
                if (lockIcon) lockIcon.textContent = '🔒';
                if (footerWarning) footerWarning.innerHTML = '<span class="text-[#EB5757]">ⓘ Minimal pesan 1 box roti / bolu</span>';
            } else if (moqViolations.length > 0) {
                const firstV = moqViolations[0];
                if (alertBox) alertBox.classList.remove('hidden');
                if (alertMsg) alertMsg.innerHTML = `<strong>${firstV.name}</strong> — Jumlah pesanan ${firstV.qty} pcs (Minimum order ${firstV.minOrder} pcs). Pesanan tidak dapat disimpan sebelum memenuhi jumlah minimum.`;
                if (submitBtn) {
                    submitBtn.disabled = true;
                    submitBtn.className = 'bg-[#D8D2CB] text-white cursor-not-allowed text-xs font-semibold px-5 py-2.5 rounded-lg flex items-center gap-1.5 transition-all shadow-xs';
                }
                if (lockIcon) lockIcon.textContent = '🔒';
                if (footerWarning) footerWarning.innerHTML = `<span class="text-[#EB5757]">ⓘ Minimum order ${firstV.minOrder} pcs belum terpenuhi</span>`;
            } else {
                if (alertBox) alertBox.classList.add('hidden');
                if (submitBtn) {
                    submitBtn.disabled = false;
                    submitBtn.className = 'bg-[#D46E15] hover:bg-[#B85207] text-white cursor-pointer text-xs font-semibold px-5 py-2.5 rounded-lg flex items-center gap-1.5 transition-all shadow-button-orange active:scale-95';
                }
                if (lockIcon) lockIcon.textContent = '✓';
                if (footerWarning) footerWarning.innerHTML = '<span class="text-[#27AE60]">✓ Minimum order terpenuhi</span>';
            }

            // Validasi tanggal PO di modal pesanan
            const dateInput = document.getElementById('deliveryDateInput');
            if (dateInput && dateInput.value) {
                const todayStr = new Date().toISOString().split('T')[0];
                if (dateInput.value < todayStr) {
                    if (submitBtn) {
                        submitBtn.disabled = true;
                        submitBtn.className = 'bg-[#D8D2CB] text-white cursor-not-allowed text-xs font-semibold px-5 py-2.5 rounded-lg flex items-center gap-1.5 transition-all shadow-xs';
                    }
                    if (lockIcon) lockIcon.textContent = '🔒';
                    if (footerWarning) footerWarning.innerHTML = '<span class="text-[#EB5757]">ⓘ Tanggal PO tidak boleh lewat</span>';
                }
            }
        }

        // Live validation tanggal PO di pesanan.php
        function validatePesananDeliveryDate(input) {
            const todayStr = new Date().toISOString().split('T')[0];
            const warningEl = document.getElementById('pesananDateWarning');
            if (input.value && input.value < todayStr) {
                if (warningEl) warningEl.classList.remove('hidden');
                input.classList.add('border-[#EB5757]', 'bg-rose-50');
                input.classList.remove('border-[#DDD3C5]', 'bg-[#EFE8DE]');
            } else {
                if (warningEl) warningEl.classList.add('hidden');
                input.classList.remove('border-[#EB5757]', 'bg-rose-50');
                input.classList.add('border-[#DDD3C5]', 'bg-[#EFE8DE]');
            }
            calculateOrderTotal();
        }

        function selectPaymentStatus(status, btn) {
            document.getElementById('selectedPaymentStatus').value = status;
            const buttons = [document.getElementById('payBtn1'), document.getElementById('payBtn2'), document.getElementById('payBtn3')];
            buttons.forEach(b => {
                b.className = 'py-1.5 px-2 rounded-lg text-xs font-semibold bg-white border border-[#E0E0E0] text-[#544337] hover:bg-gray-50 transition-colors';
            });
            btn.className = 'py-1.5 px-2 rounded-lg text-xs font-semibold bg-[#544337] text-white shadow-2xs transition-colors';
        }

        function handleSaveOrder(e) {
            // Form submitting naturally to backend POST
        }

        // ==============================================================
        // FULL PAGE POS ORDER LOGIC (DYNAMIC CATEGORIES & PRODUCTS GRID)
        // ==============================================================
        let posActiveCategory = 'Semua';
        let posCart = []; // Starts strictly with 0 products!

        function initPos() {
            updatePosCategoryCounts();
            renderPosProducts();
            renderPosCart();
        }

        function updatePosCategoryCounts() {
            const counts = { 'Semua': PRODUCTS_DATA.length };
            PRODUCTS_DATA.forEach(p => {
                const cat = p.kategori || 'Roti Manis';
                counts[cat] = (counts[cat] || 0) + 1;
            });

            document.querySelectorAll('.pos-category-btn').forEach(btn => {
                const cat = btn.getAttribute('data-category');
                const badge = btn.querySelector('.cat-count-badge');
                if (badge && counts[cat] !== undefined) {
                    badge.textContent = counts[cat];
                } else if (badge) {
                    badge.textContent = '0';
                }
            });
        }

        function selectPosCategory(cat) {
            posActiveCategory = cat;
            document.querySelectorAll('.pos-category-btn').forEach(btn => {
                const isSelected = btn.getAttribute('data-category') === cat;
                const icon = btn.querySelector('i');
                const title = btn.querySelector('span.text-xs');
                const badge = btn.querySelector('.cat-count-badge');

                if (isSelected) {
                    btn.className = 'pos-category-btn p-3 rounded-xl border text-left flex flex-col justify-between transition-all duration-150 bg-[#D46E15] text-white border-[#D46E15] shadow-sm';
                    if (icon) icon.className = icon.className.replace('text-[#D46E15]', 'text-white');
                    if (title) title.className = 'text-xs font-bold leading-tight text-white';
                    if (badge) badge.className = 'cat-count-badge text-[10px] font-bold px-1.5 py-0.5 rounded bg-white/20 text-white';
                } else {
                    btn.className = 'pos-category-btn p-3 rounded-xl border text-left flex flex-col justify-between transition-all duration-150 bg-white text-[#3E2B21] border-[#E2D7C8] hover:border-[#D46E15]/60 hover:bg-[#FDFBF7]';
                    if (icon) icon.className = icon.className.replace('text-white', 'text-[#D46E15]');
                    if (title) title.className = 'text-xs font-bold leading-tight text-[#3E2B21]';
                    if (badge) badge.className = 'cat-count-badge text-[10px] font-bold px-1.5 py-0.5 rounded bg-[#EFE8DE] text-[#8E7E73]';
                }
            });
            renderPosProducts();
        }

        function filterPosProducts() {
            renderPosProducts();
        }

        function renderPosProducts() {
            const grid = document.getElementById('posProductsGrid');
            const empty = document.getElementById('posProductsEmpty');
            const visibleCountEl = document.getElementById('posVisibleCount');
            const searchInput = document.getElementById('posProductSearch');
            const search = (searchInput ? searchInput.value : '').toLowerCase().trim();

            if (!grid) return;

            const filtered = PRODUCTS_DATA.filter(p => {
                const matchCat = (posActiveCategory === 'Semua') || (p.kategori === posActiveCategory);
                const matchSearch = !search || 
                    (p.nama && p.nama.toLowerCase().includes(search)) || 
                    (p.kemasan && p.kemasan.toLowerCase().includes(search)) || 
                    (p.kategori && p.kategori.toLowerCase().includes(search));
                return matchCat && matchSearch;
            });

            if (visibleCountEl) {
                visibleCountEl.textContent = `Menampilkan ${filtered.length} produk`;
            }

            if (filtered.length === 0) {
                grid.innerHTML = '';
                if (empty) empty.classList.remove('hidden');
                return;
            }

            if (empty) empty.classList.add('hidden');

            grid.innerHTML = filtered.map(p => {
                const inCart = posCart.find(c => c.id_varian === p.id_varian);
                const inCartQty = inCart ? inCart.qty : 0;
                const minOrder = parseInt(p.min_order, 10) || 1;
                const moqText = minOrder > 1 ? `Min. ${minOrder} pcs` : 'Ready Stock';

                let badgeStyle = 'bg-[#F5F3F1] text-[#544337] border-[#EBE8E5]';
                const kemLower = (p.kemasan || '').toLowerCase();
                if (kemLower.includes('mika')) badgeStyle = 'bg-[#EBF3FC] text-[#2F80ED] border-[#D5E6F9]';
                else if (kemLower.includes('batik')) badgeStyle = 'bg-[#FFF0E6] text-[#924C00] border-[#FFD8BF]';
                else if (kemLower.includes('coklat')) badgeStyle = 'bg-[#FEF6EE] text-[#D97706] border-[#FDE6D2]';

                return `
                    <div onclick="addPosItem(${p.id_varian})" 
                        class="group bg-white rounded-xl border ${inCartQty > 0 ? 'border-[#D46E15] ring-2 ring-[#D46E15]/20 shadow-xs' : 'border-[#E2D7C8]'} p-3.5 hover:border-[#D46E15] hover:shadow-card active:scale-[0.98] transition-all cursor-pointer flex flex-col justify-between">
                        <div>
                            <div class="flex items-center justify-between gap-1.5 mb-1.5">
                                <span class="text-[10px] font-semibold text-[#8E7E73] truncate">${escapeHtml(p.kategori || 'Bakery')}</span>
                                <span class="text-[10px] font-bold px-2 py-0.5 rounded-full border ${badgeStyle} shrink-0">
                                    ${escapeHtml(p.kemasan || 'Kemasan')}
                                </span>
                            </div>
                            <h4 class="font-bold text-xs sm:text-sm text-[#251A14] group-hover:text-[#D46E15] transition-colors leading-snug line-clamp-2">
                                ${escapeHtml(p.nama)}
                            </h4>
                            <p class="text-[10px] text-[#8E7E73] mt-1 flex items-center gap-1">
                                <i class="fas fa-box text-[9px] text-[#D46E15]"></i>
                                <span>${moqText}</span>
                            </p>
                        </div>

                        <div class="pt-3 mt-3 border-t border-[#F2ECE4] flex items-center justify-between">
                            <div>
                                <span class="text-[10px] text-[#8E7E73] block leading-none">Harga</span>
                                <span class="font-mono font-bold text-xs sm:text-sm text-[#D46E15]">Rp ${formatRupiahNumber(p.harga)}</span>
                            </div>

                            ${inCartQty > 0 ? `
                                <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-lg text-[11px] font-bold bg-[#27AE60] text-white shadow-2xs">
                                    <i class="fas fa-check text-[9px]"></i>
                                    <span>${inCartQty} di pesanan</span>
                                </span>
                            ` : `
                                <button type="button" class="inline-flex items-center gap-1 px-2.5 py-1 rounded-lg text-xs font-semibold bg-[#D46E15]/10 text-[#D46E15] group-hover:bg-[#D46E15] group-hover:text-white transition-all shadow-2xs">
                                    <i class="fas fa-plus text-[10px]"></i>
                                    <span>Pilih</span>
                                </button>
                            `}
                        </div>
                    </div>
                `;
            }).join('');
        }

        function addPosItem(id_varian) {
            const prod = PRODUCTS_DATA.find(p => p.id_varian === id_varian);
            if (!prod) return;

            const existing = posCart.find(c => c.id_varian === id_varian);
            if (existing) {
                existing.qty += 1;
            } else {
                const minOrder = parseInt(prod.min_order, 10) || 1;
                posCart.push({
                    id_varian: prod.id_varian,
                    nama: prod.nama,
                    kemasan: prod.kemasan,
                    harga: parseFloat(prod.harga) || 10000,
                    min_order: minOrder,
                    qty: minOrder > 1 ? minOrder : 1
                });
            }

            renderPosProducts();
            renderPosCart();
        }

        function removePosItem(id_varian) {
            posCart = posCart.filter(c => c.id_varian !== id_varian);
            renderPosProducts();
            renderPosCart();
        }

        function updatePosQty(id_varian, newQty) {
            const item = posCart.find(c => c.id_varian === id_varian);
            if (!item) return;
            const qty = parseInt(newQty, 10);
            if (isNaN(qty) || qty <= 0) {
                removePosItem(id_varian);
                return;
            }
            item.qty = qty;
            renderPosProducts();
            renderPosCart();
        }

        function clearPosCart() {
            if (posCart.length === 0) return;
            posCart = [];
            renderPosProducts();
            renderPosCart();
        }

        function renderPosCart() {
            const listEl = document.getElementById('posCartList');
            const badgeEl = document.getElementById('posCartItemBadge');
            const clearBtn = document.getElementById('btnClearPosCart');
            const subtotalEl = document.getElementById('posSummarySubtotal');
            const grandTotalEl = document.getElementById('posSummaryGrandTotal');
            const submitBtn = document.getElementById('btnPosSubmitOrder');
            const submitText = document.getElementById('btnPosSubmitText');
            const moqWarning = document.getElementById('posMoqWarning');
            const moqWarningText = document.getElementById('posMoqWarningText');
            const hiddenInputs = document.getElementById('posHiddenItemsInputs');

            if (!listEl) return;

            let subtotal = 0;
            let moqViolations = [];

            posCart.forEach(item => {
                subtotal += (item.qty * item.harga);
                if (item.qty < item.min_order) {
                    moqViolations.push(`${item.nama} (${item.kemasan}) min. ${item.min_order} pcs`);
                }
            });

            const totalQty = posCart.reduce((acc, it) => acc + it.qty, 0);

            if (badgeEl) badgeEl.textContent = `${posCart.length} Produk (${totalQty} pcs)`;
            if (clearBtn) {
                if (posCart.length > 0) clearBtn.classList.remove('hidden');
                else clearBtn.classList.add('hidden');
            }

            if (subtotalEl) subtotalEl.textContent = `Rp ${formatRupiahNumber(subtotal)}`;
            if (grandTotalEl) grandTotalEl.textContent = `Rp ${formatRupiahNumber(subtotal)}`;

            // If empty:
            if (posCart.length === 0) {
                listEl.innerHTML = `
                    <div id="posCartEmpty" class="py-8 px-4 text-center">
                        <div class="w-12 h-12 rounded-full bg-[#EFE8DE] text-[#8E7E73] flex items-center justify-center mx-auto mb-2.5">
                            <i class="fas fa-shopping-basket text-lg"></i>
                        </div>
                        <p class="text-xs font-bold text-[#251A14]">Keranjang Masih Kosong</p>
                        <p class="text-[11px] text-[#8E7E73] mt-1 max-w-[220px] mx-auto">
                            Klik produk pada katalog di sebelah kiri untuk memasukkannya ke dalam pesanan.
                        </p>
                    </div>
                `;
                if (submitBtn) {
                    submitBtn.disabled = true;
                    submitBtn.className = 'w-full bg-[#D46E15] text-white font-bold text-xs sm:text-sm py-3 px-4 rounded-xl flex items-center justify-center gap-2 shadow-button-orange transition-all disabled:opacity-50 disabled:cursor-not-allowed disabled:bg-[#C9BFB5]';
                }
                if (submitText) submitText.textContent = 'Pilih Produk Terlebih Dahulu';
                if (moqWarning) moqWarning.classList.add('hidden');
                if (hiddenInputs) hiddenInputs.innerHTML = '';
                return;
            }

            // Render items list
            listEl.innerHTML = posCart.map(item => {
                const itemSubtotal = item.qty * item.harga;
                const isMoqErr = item.qty < item.min_order;

                return `
                    <div class="bg-white rounded-xl border ${isMoqErr ? 'border-rose-300 bg-rose-50/20' : 'border-[#E2D7C8]'} p-3 space-y-2">
                        <div class="flex items-start justify-between gap-2">
                            <div class="min-w-0">
                                <div class="flex items-center gap-1.5 flex-wrap">
                                    <h5 class="text-xs font-bold text-[#251A14] truncate">${escapeHtml(item.nama)}</h5>
                                    <span class="text-[9px] font-semibold px-1.5 py-0.2 rounded bg-[#EFE8DE] text-[#544337]">${escapeHtml(item.kemasan)}</span>
                                </div>
                                <p class="text-[10px] text-[#8E7E73] mt-0.5">Rp ${formatRupiahNumber(item.harga)} / item</p>
                            </div>
                            <button type="button" onclick="removePosItem(${item.id_varian})" title="Hapus produk ini" class="text-[#8E7E73] hover:text-rose-600 p-1 rounded-lg hover:bg-rose-50 transition-colors">
                                <i class="fas fa-trash-alt text-xs"></i>
                            </button>
                        </div>

                        <div class="flex items-center justify-between pt-1.5 border-t border-[#F5F1EB]">
                            <!-- Stepper -->
                            <div class="flex items-center rounded-lg border border-[#DDD3C5] bg-[#FAF7F2] overflow-hidden">
                                <button type="button" onclick="updatePosQty(${item.id_varian}, ${item.qty - 1})" class="w-6 h-6 flex items-center justify-center text-[#544337] hover:bg-[#EFE8DE] active:scale-95 transition-all text-xs font-bold">−</button>
                                <input type="number" min="1" value="${item.qty}" onchange="updatePosQty(${item.id_varian}, this.value)" class="w-10 h-6 text-center text-xs font-bold bg-transparent text-[#251A14] border-x border-[#DDD3C5] focus:outline-none">
                                <button type="button" onclick="updatePosQty(${item.id_varian}, ${item.qty + 1})" class="w-6 h-6 flex items-center justify-center text-[#544337] hover:bg-[#EFE8DE] active:scale-95 transition-all text-xs font-bold">+</button>
                            </div>

                            <div class="text-right">
                                ${isMoqErr ? `<p class="text-[9px] font-bold text-rose-600">Min. ${item.min_order} pcs</p>` : ''}
                                <span class="font-mono font-bold text-xs text-[#251A14]">Rp ${formatRupiahNumber(itemSubtotal)}</span>
                            </div>
                        </div>
                    </div>
                `;
            }).join('');

            // MOQ check & button state
            if (moqViolations.length > 0) {
                if (moqWarning) {
                    moqWarning.classList.remove('hidden');
                    moqWarningText.innerHTML = `<strong>Batas Minimal Order:</strong> ${moqViolations.join(', ')}`;
                }
                if (submitBtn) {
                    submitBtn.disabled = true;
                    submitBtn.className = 'w-full bg-[#D46E15] text-white font-bold text-xs sm:text-sm py-3 px-4 rounded-xl flex items-center justify-center gap-2 shadow-button-orange transition-all disabled:opacity-50 disabled:cursor-not-allowed disabled:bg-[#C9BFB5]';
                }
                if (submitText) submitText.textContent = 'Penuhi Batas Minimal Order (MOQ)';
            } else {
                if (moqWarning) moqWarning.classList.add('hidden');
                if (submitBtn) {
                    submitBtn.disabled = false;
                    submitBtn.className = 'w-full bg-[#D46E15] hover:bg-[#B85207] text-white font-bold text-xs sm:text-sm py-3 px-4 rounded-xl flex items-center justify-center gap-2 shadow-button-orange active:scale-95 transition-all cursor-pointer';
                }
                if (submitText) submitText.textContent = `Simpan Pesanan • Rp ${formatRupiahNumber(subtotal)}`;
            }

            // Hidden inputs for PHP $_POST['items']
            if (hiddenInputs) {
                hiddenInputs.innerHTML = posCart.map((item, idx) => `
                    <input type="hidden" name="items[${idx}][id_varian]" value="${item.id_varian}">
                    <input type="hidden" name="items[${idx}][nama]" value="${escapeHtml(item.nama)}">
                    <input type="hidden" name="items[${idx}][kemasan]" value="${escapeHtml(item.kemasan)}">
                    <input type="hidden" name="items[${idx}][harga]" value="${item.harga}">
                    <input type="hidden" name="items[${idx}][qty]" value="${item.qty}">
                    <input type="hidden" name="items[${idx}][subtotal]" value="${item.qty * item.harga}">
                `).join('');
            }
        }

        function togglePosNewCustomer() {
            const box = document.getElementById('posNewCustBox');
            const selectWrap = document.getElementById('posCustSelectWrapper');
            const lbl = document.getElementById('lblPosToggleCust');
            if (!box || !selectWrap) return;

            const isHidden = box.classList.contains('hidden');
            if (isHidden) {
                box.classList.remove('hidden');
                selectWrap.classList.add('opacity-40', 'pointer-events-none');
                if (lbl) lbl.textContent = 'Pilih Pelanggan Terdaftar';
                const idPelangganSelect = document.getElementById('pos_id_pelanggan');
                if (idPelangganSelect) idPelangganSelect.value = '0';
            } else {
                box.classList.add('hidden');
                selectWrap.classList.remove('opacity-40', 'pointer-events-none');
                if (lbl) lbl.textContent = 'Tambah Pelanggan Baru';
                const idPelangganSelect = document.getElementById('pos_id_pelanggan');
                if (idPelangganSelect && CUSTOMERS_DATA.length > 0) {
                    idPelangganSelect.value = CUSTOMERS_DATA[0].id_pelanggan;
                }
            }
        }

        function syncPosCustomerInfo() {
            // Synced via HTML data attributes
        }

        function formatRupiahNumber(num) {
            return Math.round(num).toString().replace(/\B(?=(\d{3})+(?!\d))/g, ".");
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

        // Close on Escape & Outside Click
        document.addEventListener('keydown', function(e) {
            if (e.key === 'Escape') {
                closeLogoutModal();
                closeOrderModal();
                closeOrderDetailModal();
                closeNoteModal();
            }
        });

        document.addEventListener('click', function(e) {
            const notif = document.getElementById('notificationDropdown');
            const notifBtn = document.getElementById('notificationBtn');
            const user = document.getElementById('userDropdown');
            const userBtn = document.getElementById('userAvatarBtn');

            if (notif && !notif.contains(e.target) && !notifBtn.contains(e.target)) {
                notif.classList.add('hidden');
            }
            if (user && !user.contains(e.target) && !userBtn.contains(e.target)) {
                user.classList.add('hidden');
            }
        });

        // Initial setup
        document.addEventListener('DOMContentLoaded', function() {
            if (document.getElementById('posProductsGrid')) {
                initPos();
            }
            if (typeof calculateOrderTotal === 'function') calculateOrderTotal();
            if (typeof renderInteractiveCalendar === 'function') renderInteractiveCalendar();
        });
    </script>
</body>
</html>
