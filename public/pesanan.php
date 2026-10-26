<?php
session_start();

// Proteksi Autentikasi: Hanya user yang sudah login yang diizinkan mengakses halaman Pesanan
if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true) {
    header("Location: login.php");
    exit();
}

// Coba koneksi database jika config tersedia
$configFile = __DIR__ . "/../config/config.php";
if (file_exists($configFile)) {
    require_once $configFile;
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
            'text' => 'Roti tawar butuh dikirim jam 15:00. Dahulukan baking & packing mika terlebih dahulu.'
        ],
        [
            'id' => 2,
            'type' => 'order',
            'tag' => '🕒 Catatan #ORD-088',
            'tag_class' => 'text-[#D97706]',
            'time' => '09:30',
            'text' => 'Pak Budi minta kardus roti sisir diikat tali rafia rapat saat diambil sore.'
        ],
        [
            'id' => 3,
            'type' => 'stock',
            'tag' => '📋 Catatan Stok Bahan',
            'tag_class' => 'text-[#544337]',
            'time' => '08:00',
            'text' => 'Coklat mika sisa 4 bungkus untuk persiapan pesanan besok pagi.'
        ]
    ];
}

// POST Action Handlers (AJAX & Form)
$toast_message = null;
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    
    if ($action === 'update_order_status') {
        $order_id = $_POST['order_id'] ?? '';
        $new_status = $_POST['status'] ?? '';
        if ($order_id && $new_status) {
            $_SESSION['order_status_overrides'][$order_id] = $new_status;
            
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
    } elseif ($action === 'add_order') {
        $custName = trim($_POST['customer_name'] ?? 'Pelanggan');
        $itemsDesc = trim($_POST['items_desc'] ?? 'Roti Aneka');
        $newId = 'ORD-' . date('ymd') . '-' . rand(10, 99);
        $newOrder = [
            'id' => $newId,
            'customer' => $custName,
            'items' => $itemsDesc,
            'destination' => trim($_POST['destination'] ?? 'Ambil di Toko'),
            'time' => 'Hari Ini, ' . date('H:i'),
            'date' => date('Y-m-d'),
            'status' => 'menunggu',
            'is_urgent' => false,
            'status_bayar' => $_POST['payment_status'] ?? 'Belum Bayar',
            'bayar_class' => 'bg-[#828282]/15 text-[#544337]',
            'courier_info' => 'Kurir Toko',
            'total' => intval($_POST['grand_total'] ?? 110000)
        ];
        if (!isset($_SESSION['custom_orders'])) {
            $_SESSION['custom_orders'] = [];
        }
        $_SESSION['custom_orders'][] = $newOrder;
        $toast_message = "Pesanan baru {$newId} berhasil dibuat dan masuk antrean!";
    }
}

// Data Pesanan Kanban & Kalender
$active_tab = $_GET['tab'] ?? 'kanban';

// Master Dataset Pesanan
$master_orders = [
    [
        'id' => 'ORD-089',
        'customer' => 'Ibu Sari',
        'phone' => '0812-3456-7890',
        'items' => 'Roti Tawar 20pcs, Donat 1 mika',
        'destination' => 'Dikirim ke Komplek Melati No. 4',
        'time' => 'Hari Ini, 15:00',
        'date' => date('Y-m-d'),
        'default_status' => 'menunggu', // menunggu, proses, kirim, selesai
        'is_urgent' => true,
        'status_bayar' => 'Belum Bayar',
        'bayar_class' => 'bg-[#828282]/15 text-[#544337]',
        'courier_info' => 'Kurir Toko (Siap Antar 14:00)',
        'total' => 110000
    ],
    [
        'id' => 'ORD-088',
        'customer' => 'Pak Budi',
        'phone' => '0823-4567-8901',
        'items' => 'Roti Sisir 2 kardus (24 pcs)',
        'destination' => 'Ambil Langsung Sore Hari',
        'time' => 'Hari Ini, 16:30',
        'date' => date('Y-m-d'),
        'default_status' => 'proses',
        'is_urgent' => false,
        'status_bayar' => 'Hutang',
        'bayar_class' => 'bg-[#E2B93B]/20 text-[#886C12]',
        'courier_info' => 'Ambil Sendiri di Toko',
        'total' => 95000
    ],
    [
        'id' => 'ORD-087',
        'customer' => 'Toko Maju',
        'phone' => '0834-5678-9012',
        'items' => 'Kue Bolu 15pcs, Roti Sobek 5 box',
        'destination' => 'Kirim via Driver Roda Tiga',
        'time' => 'Hari Ini, 10:00',
        'date' => date('Y-m-d'),
        'default_status' => 'kirim',
        'is_urgent' => false,
        'status_bayar' => 'Lunas',
        'bayar_class' => 'bg-[#27AE60]/15 text-[#1B7A43]',
        'courier_info' => 'Driver Roda Tiga',
        'total' => 450000
    ],
    [
        'id' => 'ORD-084',
        'customer' => 'Bu Rina',
        'phone' => '0815-6789-0123',
        'items' => 'Donat Coklat 2 mika (12 pcs)',
        'destination' => 'Ambil di Toko',
        'courier_info' => 'Ambil di Toko (14:00)',
        'time' => 'Kemarin, 14:00',
        'date' => date('Y-m-d', strtotime('-1 day')),
        'default_status' => 'selesai',
        'is_urgent' => false,
        'status_bayar' => 'Lunas',
        'bayar_class' => 'bg-[#27AE60]/15 text-[#1B7A43]',
        'total' => 50000
    ],
    [
        'id' => 'ORD-081',
        'customer' => 'Warung Pak Haji',
        'phone' => '0819-0123-4567',
        'items' => 'Roti Tawar 50pcs (Kemasan Mika)',
        'destination' => 'Jl. Pasar Baru No. 12',
        'courier_info' => 'Kurir Obrok Motor',
        'time' => 'Kemarin, 11:00',
        'date' => date('Y-m-d', strtotime('-1 day')),
        'default_status' => 'selesai',
        'is_urgent' => false,
        'status_bayar' => 'Lunas',
        'bayar_class' => 'bg-[#27AE60]/15 text-[#1B7A43]',
        'total' => 325000
    ],
    [
        'id' => 'ORD-080',
        'customer' => 'Ibu Maya',
        'phone' => '0821-9988-7766',
        'items' => 'Roll Cokelat Mini (10x), Chiffon Pandan',
        'destination' => 'Kirim ke Jl. Ijen No. 15',
        'courier_info' => 'Kurir Toko',
        'time' => 'Hari Ini, 17:00',
        'date' => date('Y-m-d'),
        'default_status' => 'menunggu',
        'is_urgent' => false,
        'status_bayar' => 'Belum Bayar',
        'bayar_class' => 'bg-[#828282]/15 text-[#544337]',
        'total' => 145000
    ]
];

// Gabungkan dengan custom orders dari session
if (!empty($_SESSION['custom_orders'])) {
    foreach ($_SESSION['custom_orders'] as $co) {
        $master_orders[] = $co;
    }
}

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
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
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

        .kanban-column.drag-over {
            background-color: #FFF3E8 !important;
            border: 2px dashed #D46E15 !important;
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
                    <button onclick="toggleSidebar()" class="ml-auto text-white/70 hover:text-white lg:hidden p-1" aria-label="Tutup Menu">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                    </button>
                </div>

                <!-- Navigation List -->
                <nav class="p-4 space-y-1.5 flex-1">
                    <!-- Beranda -->
                    <a href="beranda.php" class="text-white/80 hover:text-white hover:bg-white/10 rounded-lg px-4 py-3 flex items-center gap-3 font-medium text-sm transition-colors">
                        <img src="assets/icons/nav_beranda.svg" alt="Beranda" class="w-5 h-5 opacity-90">
                        <span>Beranda</span>
                    </a>

                    <!-- Produk -->
                    <a href="produk.php" class="text-white/80 hover:text-white hover:bg-white/10 rounded-lg px-4 py-3 flex items-center gap-3 font-medium text-sm transition-colors">
                        <img src="assets/icons/nav_produk.svg" alt="Produk" class="w-5 h-5 opacity-90">
                        <span>Produk</span>
                    </a>

                    <!-- Pesanan (Active) -->
                    <a href="pesanan.php" class="bg-[#FF9B45] text-white rounded-lg px-4 py-3 flex items-center gap-3 font-medium text-sm transition-all shadow-sm">
                        <img src="assets/icons/nav_pesanan.svg" alt="Pesanan" class="w-5 h-5">
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
                    <button onclick="toggleSidebar()" class="lg:hidden p-1.5 text-[#544337] hover:bg-[#F5F3F1] rounded-lg" aria-label="Buka Menu">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"></path></svg>
                    </button>

                    <div>
                        <!-- Breadcrumbs -->
                        <div class="flex items-center gap-1.5 text-xs">
                            <a href="pesanan.php" class="text-[#544337] hover:underline">Pesanan</a>
                            <span class="text-[#DBC2B2]">/</span>
                            <span class="text-[#924C00] font-medium" id="headerBreadcrumb">Manajemen</span>
                        </div>
                        <!-- Section Heading -->
                        <h1 class="text-[#1B1C1B] font-bold text-base md:text-lg leading-tight mt-0.5" id="headerTitle">Manage Pesanan</h1>
                    </div>
                </div>

                <!-- Right: Date Indicator, Notification Bell, User Avatar -->
                <div class="flex items-center gap-3 md:gap-5 relative">
                    
                    <!-- Date Button (Dinamis) -->
                    <div class="hidden sm:flex items-center gap-2 bg-[#F5F3F1] text-[#544337] px-3.5 py-1.5 rounded-lg text-xs md:text-sm font-medium border border-[#EBE8E5]">
                        <img src="assets/icons/header_calendar.svg" alt="Kalender" class="w-4 h-4">
                        <span><?= $todayFormatted ?></span>
                    </div>

                    <!-- Notification Bell with Orange Dot -->
                    <div class="relative">
                        <button onclick="toggleNotificationDropdown()" class="relative p-2 text-[#544337] hover:bg-[#F5F3F1] rounded-lg transition-colors" title="Notifikasi" id="notificationBtn">
                            <img src="assets/icons/bell.svg" alt="Notifikasi" class="w-5 h-5">
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
                                        <p class="text-[11px] text-[#5E4D42] mt-0.5">Bu Rina telah mengambil donat di toko.</p>
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
                        <div onclick="toggleUserDropdown()" class="w-8 h-8 rounded-full bg-[#924C00] flex items-center justify-center text-white shadow-xs cursor-pointer hover:opacity-95 transition-all active:scale-95" title="Profil Pengguna" id="userAvatarBtn">
                            <img src="assets/icons/header_user.svg" alt="User" class="w-4 h-4">
                        </div>

                        <!-- User Dropdown Popover -->
                        <div id="userDropdown" class="hidden absolute right-0 mt-2.5 w-56 craft-dropdown py-1.5 z-50 text-xs">
                            <div class="px-4 py-2.5 border-b border-[#ECE5DD]">
                                <p class="text-xs font-semibold text-[#251A14] truncate"><?= htmlspecialchars($_SESSION['nama_lengkap'] ?? $_SESSION['user'] ?? 'Admin') ?></p>
                                <p class="text-[11px] text-[#8E7E73] mt-0.5"><?= htmlspecialchars(ucfirst($_SESSION['role'] ?? 'Admin')) ?> Toko</p>
                            </div>
                            <div class="py-1">
                                <a href="pengaturan.php" class="craft-dropdown-item">
                                    <span>⚙️</span>
                                    <span>Pengaturan Toko</span>
                                </a>
                                <a href="pengaturan.php?tab=user" class="craft-dropdown-item">
                                    <span>👥</span>
                                    <span>Manajemen User</span>
                                </a>
                            </div>
                            <div class="border-t border-[#ECE5DD] pt-1">
                                <button type="button" onclick="openLogoutModal()" class="w-full text-left craft-dropdown-item text-[#991B1B] hover:text-[#7F1D1D] hover:bg-rose-50/70">
                                    <span>🚪</span>
                                    <span>Keluar dari Akun</span>
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
                            <svg class="w-5 h-5 text-emerald-600 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                            <span class="text-sm font-medium"><?= htmlspecialchars($toast_message) ?></span>
                        </div>
                        <button onclick="document.getElementById('toastNotification').remove()" class="text-emerald-700 hover:text-emerald-900 p-1">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                        </button>
                    </div>
                <?php endif; ?>

                <!-- Dynamic In-Page Toast for Drag & Drop / Actions -->
                <div id="dynamicToast" class="hidden fixed bottom-6 right-6 z-50 bg-[#1C1C1C] text-white px-5 py-3.5 rounded-xl shadow-2xl flex items-center gap-3 text-xs font-semibold animate-in slide-in-from-bottom-3 duration-200">
                    <span id="dynamicToastIcon">✨</span>
                    <span id="dynamicToastText">Aksi berhasil dijalankan</span>
                </div>
                
                <!-- TOP BANNER: TITLE, STATS, & OMSET -->
                <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between gap-4 mb-6">
                    <div>
                        <div class="flex items-center gap-2.5">
                            <h2 class="text-[#1B1C1B] text-2xl font-bold tracking-tight">Manajemen Pesanan</h2>
                            <span class="bg-[#FF9B45]/15 text-[#924C00] text-xs font-semibold px-2.5 py-1 rounded-full border border-[#FF9B45]/20" id="totalOrdersBadge">
                                <?= $totalOrdersCount ?> Total Pesanan
                            </span>
                        </div>
                        <p class="text-[#828282] text-sm mt-1" id="pageSubtitle">
                            Kelola status dan alur pengiriman pesanan roti secara real-time
                        </p>
                    </div>

                    <!-- Right Stats Indicator Pills -->
                    <div class="flex flex-wrap items-center gap-3">
                        <!-- Menunggu & Proses: count -->
                        <div class="bg-white border border-[#E0E0E0] rounded-xl px-3.5 py-2 flex items-center gap-2 text-xs font-medium shadow-2xs">
                            <span class="w-2 h-2 rounded-full bg-[#E2B93B]"></span>
                            <span class="text-[#828282]">Aktif Dapur:</span>
                            <span class="font-bold text-[#1C1C1C]" id="statMenunggu"><?= $totalMenungguCount ?></span>
                        </div>

                        <!-- Selesai: count -->
                        <div class="bg-white border border-[#E0E0E0] rounded-xl px-3.5 py-2 flex items-center gap-2 text-xs font-medium shadow-2xs">
                            <span class="w-2 h-2 rounded-full bg-[#27AE60]"></span>
                            <span class="text-[#828282]">Selesai:</span>
                            <span class="font-bold text-[#1C1C1C]" id="statSelesai"><?= $totalSelesaiCount ?></span>
                        </div>

                        <!-- Omset Card -->
                        <div class="bg-[#6B3E1F] text-white rounded-xl px-4 py-2 flex items-center gap-2.5 text-xs shadow-xs">
                            <div class="w-6 h-6 rounded-md bg-white/20 flex items-center justify-center shrink-0">
                                <svg class="w-3.5 h-3.5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z"></path></svg>
                            </div>
                            <div>
                                <span class="text-white/80 text-[10px] block leading-none">Total Omset</span>
                                <span class="font-bold text-sm tracking-tight leading-tight">Rp <?= number_format($totalOmset, 0, ',', '.') ?></span>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- TAB SWITCHER BAR (KANBAN BOARD VS KALENDER PENGIRIMAN) -->
                <div class="bg-white rounded-xl border border-[#F0ECE9] p-1.5 flex items-center gap-2 mb-6 shadow-2xs max-w-fit">
                    <!-- Tab 1: Kanban Board -->
                    <button onclick="switchTab('kanban')" id="tabBtnKanban" 
                        class="px-4 py-2 rounded-lg text-sm font-semibold flex items-center gap-2 transition-all <?= $active_tab !== 'kalender' ? 'bg-[#FF9B45] text-white shadow-xs' : 'text-[#828282] hover:text-[#1C1C1C] hover:bg-[#F5F3F1]' ?>">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 17V7m0 10a2 2 0 01-2 2H5a2 2 0 01-2-2V7a2 2 0 012-2h2a2 2 0 012 2m0 10a2 2 0 002 2h2a2 2 0 002-2M9 7a2 2 0 012-2h2a2 2 0 012 2m0 10V7m0 10a2 2 0 002 2h2a2 2 0 002-2V7a2 2 0 00-2-2h-2a2 2 0 00-2 2"></path></svg>
                        <span>Kanban Board</span>
                    </button>

                    <!-- Tab 2: Kalender Pengiriman -->
                    <button onclick="switchTab('kalender')" id="tabBtnKalender" 
                        class="px-4 py-2 rounded-lg text-sm font-semibold flex items-center gap-2 transition-all <?= $active_tab === 'kalender' ? 'bg-[#FF9B45] text-white shadow-xs' : 'text-[#828282] hover:text-[#1C1C1C] hover:bg-[#F5F3F1]' ?>">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path></svg>
                        <span>Kalender Pengiriman</span>
                    </button>
                </div>

                <!-- ============================================================== -->
                <!-- TAB CONTENT 1: KANBAN BOARD (4 SWIMLANES + CATATAN DAPUR)       -->
                <!-- ============================================================== -->
                <div id="kanbanView" class="<?= $active_tab === 'kalender' ? 'hidden' : '' ?>">
                    
                    <!-- Action Bar: Unduh Rekap & Tambah Pesanan -->
                    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3 mb-6">
                        <div class="flex items-center gap-2 text-xs text-[#828282]">
                            <span class="inline-block w-2 h-2 rounded-full bg-[#27AE60]"></span>
                            <span>Tarik kartu untuk memindahkan status alur kerja produksi & pengiriman secara langsung.</span>
                        </div>

                        <div class="flex items-center gap-3 self-end sm:self-auto">
                            <!-- Tombol Unduh Rekap (Ekspor CSV Riil) -->
                            <button onclick="handleDownloadRecap()" class="bg-white hover:bg-[#F5F3F1] border border-[#E0E0E0] text-[#1B1C1B] text-sm font-medium px-4 py-2.5 rounded-lg flex items-center gap-2 transition-colors shadow-2xs">
                                <img src="assets/icons/download.svg" alt="Unduh" class="w-4 h-4">
                                <span>Unduh Rekap</span>
                            </button>

                            <!-- Tombol Tambah Pesanan -->
                            <button onclick="openOrderModal()" class="bg-[#FF9B45] hover:bg-[#E88C3D] text-white text-sm font-semibold px-4 py-2.5 rounded-lg flex items-center gap-2 transition-all shadow-button-orange active:scale-95">
                                <img src="assets/icons/plus.svg" alt="Tambah" class="w-4 h-4">
                                <span>+ Tambah Pesanan</span>
                            </button>
                        </div>
                    </div>

                    <!-- 4 Swimlanes Kanban Board Grid (Menunggu -> Oven -> Siap Kirim -> Selesai) -->
                    <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-4 gap-5 mb-8">
                        
                        <!-- SWIMLANE 1: MENUNGGU KONFIRMASI -->
                        <div class="kanban-column bg-[#F9F7F5] border border-[#F0ECE9] rounded-2xl p-4 flex flex-col justify-between min-h-[520px] transition-colors"
                             id="col-menunggu" ondragover="allowDrop(event)" ondragleave="dragLeave(event)" ondrop="drop(event, 'menunggu')">
                            <div>
                                <div class="flex items-center justify-between mb-1">
                                    <div class="flex items-center gap-2">
                                        <span class="bg-[#E2B93B]/20 text-[#886C12] text-xs font-bold px-2.5 py-0.5 rounded-full">Menunggu Konfirmasi</span>
                                        <span class="w-5 h-5 rounded-full bg-[#E2B93B] text-white text-[11px] font-bold flex items-center justify-center count-badge" id="count-menunggu"><?= count($orders_by_status['menunggu']) ?></span>
                                    </div>
                                </div>
                                <p class="text-[#828282] text-xs mb-4">Pesanan baru masuk kasir</p>

                                <!-- Kartu Pesanan Container -->
                                <div class="space-y-3.5 order-cards-container" id="container-menunggu">
                                    <?php foreach ($orders_by_status['menunggu'] as $ord): ?>
                                        <?php include __DIR__ . '/../views/partials/kanban_card.php'; if (!file_exists(__DIR__ . '/../views/partials/kanban_card.php')): ?>
                                        <div id="card-<?= $ord['id'] ?>" draggable="true" ondragstart="drag(event, '<?= $ord['id'] ?>')" 
                                             class="bg-white rounded-xl p-4 border border-[#F0ECE9] border-l-4 border-l-[#E2B93B] shadow-card hover:shadow-md transition-shadow relative cursor-grab active:cursor-grabbing"
                                             onclick="openOrderDetail(<?= htmlspecialchars(json_encode($ord)) ?>)">
                                            <div class="flex items-start justify-between">
                                                <div>
                                                    <h4 class="text-[#1C1C1C] font-bold text-sm leading-tight"><?= htmlspecialchars($ord['customer']) ?></h4>
                                                    <span class="text-[#828282] text-xs font-mono"><?= htmlspecialchars($ord['id']) ?></span>
                                                </div>
                                                <span class="text-[#BDBDBD] hover:text-[#828282]">
                                                    <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20"><path d="M7 2a2 2 0 100 4 2 2 0 000-4zm6 0a2 2 0 100 4 2 2 0 000-4zm-6 6a2 2 0 100 4 2 2 0 000-4zm6 0a2 2 0 100 4 2 2 0 000-4zm-6 6a2 2 0 100 4 2 2 0 000-4zm6 0a2 2 0 100 4 2 2 0 000-4z"></path></svg>
                                                </span>
                                            </div>
                                            <div class="flex items-center gap-1.5 text-xs text-[#544337] font-medium mt-2.5">
                                                <span>🛍️</span>
                                                <span class="truncate"><?= htmlspecialchars($ord['items']) ?></span>
                                            </div>
                                            <div class="flex items-center gap-1.5 text-xs text-[#828282] mt-1">
                                                <span>🛵</span>
                                                <span class="truncate"><?= htmlspecialchars($ord['destination']) ?></span>
                                            </div>
                                            <div class="flex items-center justify-between pt-3 mt-3 border-t border-[#F0ECE9]">
                                                <div class="flex items-center gap-1 text-xs <?= $ord['is_urgent'] ? 'text-[#EB5757] font-semibold' : 'text-[#828282]' ?>">
                                                    <img src="assets/icons/clock.svg" alt="Waktu" class="w-3.5 h-3.5">
                                                    <span><?= htmlspecialchars($ord['time']) ?></span>
                                                </div>
                                                <span class="text-[11px] font-semibold px-2 py-0.5 rounded-full <?= $ord['bayar_class'] ?>">
                                                    <?= htmlspecialchars($ord['status_bayar']) ?>
                                                </span>
                                            </div>
                                        </div>
                                        <?php endif; ?>
                                    <?php endforeach; ?>
                                </div>
                            </div>

                            <button onclick="openOrderModal()" class="w-full py-2.5 mt-4 border border-dashed border-[#D8D2CB] hover:border-[#FF9B45] text-[#828282] hover:text-[#FF9B45] text-xs font-semibold rounded-xl flex items-center justify-center gap-1.5 transition-colors">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path></svg>
                                <span>+ Tambah Kartu</span>
                            </button>
                        </div>

                        <!-- SWIMLANE 2: PROSES DAPUR (OVEN & BAKING) -->
                        <div class="kanban-column bg-[#F9F7F5] border border-[#F0ECE9] rounded-2xl p-4 flex flex-col justify-between min-h-[520px] transition-colors"
                             id="col-proses" ondragover="allowDrop(event)" ondragleave="dragLeave(event)" ondrop="drop(event, 'proses')">
                            <div>
                                <div class="flex items-center justify-between mb-1">
                                    <div class="flex items-center gap-2">
                                        <span class="bg-[#FF9B45]/20 text-[#924C00] text-xs font-bold px-2.5 py-0.5 rounded-full">Proses Dapur</span>
                                        <span class="w-5 h-5 rounded-full bg-[#FF9B45] text-white text-[11px] font-bold flex items-center justify-center count-badge" id="count-proses"><?= count($orders_by_status['proses']) ?></span>
                                    </div>
                                </div>
                                <p class="text-[#828282] text-xs mb-4">Adonan, oven & pendinginan</p>

                                <div class="space-y-3.5 order-cards-container" id="container-proses">
                                    <?php foreach ($orders_by_status['proses'] as $ord): ?>
                                        <div id="card-<?= $ord['id'] ?>" draggable="true" ondragstart="drag(event, '<?= $ord['id'] ?>')" 
                                             class="bg-white rounded-xl p-4 border border-[#F0ECE9] border-l-4 border-l-[#FF9B45] shadow-card hover:shadow-md transition-shadow relative cursor-grab active:cursor-grabbing"
                                             onclick="openOrderDetail(<?= htmlspecialchars(json_encode($ord)) ?>)">
                                            <div class="flex items-start justify-between">
                                                <div>
                                                    <h4 class="text-[#1C1C1C] font-bold text-sm leading-tight"><?= htmlspecialchars($ord['customer']) ?></h4>
                                                    <span class="text-[#828282] text-xs font-mono"><?= htmlspecialchars($ord['id']) ?></span>
                                                </div>
                                                <span class="text-[#BDBDBD] hover:text-[#828282]">
                                                    <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20"><path d="M7 2a2 2 0 100 4 2 2 0 000-4zm6 0a2 2 0 100 4 2 2 0 000-4zm-6 6a2 2 0 100 4 2 2 0 000-4zm6 0a2 2 0 100 4 2 2 0 000-4zm-6 6a2 2 0 100 4 2 2 0 000-4zm6 0a2 2 0 100 4 2 2 0 000-4z"></path></svg>
                                                </span>
                                            </div>
                                            <div class="flex items-center gap-1.5 text-xs text-[#544337] font-medium mt-2.5">
                                                <span>🛍️</span>
                                                <span class="truncate"><?= htmlspecialchars($ord['items']) ?></span>
                                            </div>
                                            <div class="flex items-center gap-1.5 text-xs text-[#828282] mt-1">
                                                <span>🛵</span>
                                                <span class="truncate"><?= htmlspecialchars($ord['destination']) ?></span>
                                            </div>
                                            <div class="flex items-center justify-between pt-3 mt-3 border-t border-[#F0ECE9]">
                                                <div class="flex items-center gap-1 text-xs text-[#828282]">
                                                    <img src="assets/icons/clock.svg" alt="Waktu" class="w-3.5 h-3.5">
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
                        <div class="kanban-column bg-[#F9F7F5] border border-[#F0ECE9] rounded-2xl p-4 flex flex-col justify-between min-h-[520px] transition-colors"
                             id="col-kirim" ondragover="allowDrop(event)" ondragleave="dragLeave(event)" ondrop="drop(event, 'kirim')">
                            <div>
                                <div class="flex items-center justify-between mb-1">
                                    <div class="flex items-center gap-2">
                                        <span class="bg-[#2F80ED]/20 text-[#1B57A6] text-xs font-bold px-2.5 py-0.5 rounded-full">Siap Kirim</span>
                                        <span class="w-5 h-5 rounded-full bg-[#2F80ED] text-white text-[11px] font-bold flex items-center justify-center count-badge" id="count-kirim"><?= count($orders_by_status['kirim']) ?></span>
                                    </div>
                                </div>
                                <p class="text-[#828282] text-xs mb-4">Sudah dipacking mika / kardus</p>

                                <div class="space-y-3.5 order-cards-container" id="container-kirim">
                                    <?php foreach ($orders_by_status['kirim'] as $ord): ?>
                                        <div id="card-<?= $ord['id'] ?>" draggable="true" ondragstart="drag(event, '<?= $ord['id'] ?>')" 
                                             class="bg-white rounded-xl p-4 border border-[#F0ECE9] border-l-4 border-l-[#2F80ED] shadow-card hover:shadow-md transition-shadow relative cursor-grab active:cursor-grabbing"
                                             onclick="openOrderDetail(<?= htmlspecialchars(json_encode($ord)) ?>)">
                                            <div class="flex items-start justify-between">
                                                <div>
                                                    <h4 class="text-[#1C1C1C] font-bold text-sm leading-tight"><?= htmlspecialchars($ord['customer']) ?></h4>
                                                    <span class="text-[#828282] text-xs font-mono"><?= htmlspecialchars($ord['id']) ?></span>
                                                </div>
                                                <span class="text-[#BDBDBD] hover:text-[#828282]">
                                                    <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20"><path d="M7 2a2 2 0 100 4 2 2 0 000-4zm6 0a2 2 0 100 4 2 2 0 000-4zm-6 6a2 2 0 100 4 2 2 0 000-4zm6 0a2 2 0 100 4 2 2 0 000-4zm-6 6a2 2 0 100 4 2 2 0 000-4zm6 0a2 2 0 100 4 2 2 0 000-4z"></path></svg>
                                                </span>
                                            </div>
                                            <div class="flex items-center gap-1.5 text-xs text-[#544337] font-medium mt-2.5">
                                                <span>🛍️</span>
                                                <span class="truncate"><?= htmlspecialchars($ord['items']) ?></span>
                                            </div>
                                            <div class="inline-flex items-center gap-1.5 text-xs font-medium text-[#544337] bg-[#F5F3F1] px-2.5 py-1 rounded-md mt-2 border border-[#EBE8E5]">
                                                <span>🛵</span>
                                                <span><?= htmlspecialchars($ord['courier_info'] ?? 'Kurir Toko') ?></span>
                                            </div>
                                            <div class="flex items-center justify-between pt-3 mt-3 border-t border-[#F0ECE9]">
                                                <div class="flex items-center gap-1 text-xs text-[#828282]">
                                                    <img src="assets/icons/clock.svg" alt="Waktu" class="w-3.5 h-3.5">
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
                        <div class="kanban-column bg-[#F9F7F5] border border-[#F0ECE9] rounded-2xl p-4 flex flex-col justify-between min-h-[520px] transition-colors"
                             id="col-selesai" ondragover="allowDrop(event)" ondragleave="dragLeave(event)" ondrop="drop(event, 'selesai')">
                            <div>
                                <div class="flex items-center justify-between mb-1">
                                    <div class="flex items-center gap-2">
                                        <span class="bg-[#27AE60]/20 text-[#1B7A43] text-xs font-bold px-2.5 py-0.5 rounded-full">Selesai</span>
                                        <span class="w-5 h-5 rounded-full bg-[#27AE60] text-white text-[11px] font-bold flex items-center justify-center count-badge" id="count-selesai"><?= count($orders_by_status['selesai']) ?></span>
                                    </div>
                                </div>
                                <p class="text-[#828282] text-xs mb-4">Diterima pelanggan / lunas</p>

                                <div class="space-y-3.5 order-cards-container" id="container-selesai">
                                    <?php foreach ($orders_by_status['selesai'] as $ord): ?>
                                        <div id="card-<?= $ord['id'] ?>" draggable="true" ondragstart="drag(event, '<?= $ord['id'] ?>')" 
                                             class="bg-white rounded-xl p-4 border border-[#F0ECE9] border-l-4 border-l-[#27AE60] shadow-card hover:shadow-md transition-shadow relative cursor-grab active:cursor-grabbing"
                                             onclick="openOrderDetail(<?= htmlspecialchars(json_encode($ord)) ?>)">
                                            <div class="flex items-start justify-between">
                                                <div>
                                                    <h4 class="text-[#1C1C1C] font-bold text-sm leading-tight"><?= htmlspecialchars($ord['customer']) ?></h4>
                                                    <span class="text-[#828282] text-xs font-mono"><?= htmlspecialchars($ord['id']) ?></span>
                                                </div>
                                                <span class="text-[#BDBDBD] hover:text-[#828282]">
                                                    <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20"><path d="M7 2a2 2 0 100 4 2 2 0 000-4zm6 0a2 2 0 100 4 2 2 0 000-4zm-6 6a2 2 0 100 4 2 2 0 000-4zm6 0a2 2 0 100 4 2 2 0 000-4zm-6 6a2 2 0 100 4 2 2 0 000-4zm6 0a2 2 0 100 4 2 2 0 000-4z"></path></svg>
                                                </span>
                                            </div>
                                            <div class="flex items-center gap-1.5 text-xs text-[#544337] font-medium mt-2.5">
                                                <span>🛍️</span>
                                                <span class="truncate"><?= htmlspecialchars($ord['items']) ?></span>
                                            </div>
                                            <div class="inline-flex items-center gap-1.5 text-xs font-medium text-[#544337] bg-[#F5F3F1] px-2.5 py-1 rounded-md mt-2 border border-[#EBE8E5]">
                                                <span>✓</span>
                                                <span><?= htmlspecialchars($ord['courier_info'] ?? 'Pesanan Diterima') ?></span>
                                            </div>
                                            <div class="flex items-center justify-between pt-3 mt-3 border-t border-[#F0ECE9]">
                                                <div class="flex items-center gap-1 text-xs text-[#828282]">
                                                    <img src="assets/icons/header_calendar.svg" alt="Kalender" class="w-3.5 h-3.5">
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
                    <div class="bg-white border border-[#F0ECE9] rounded-2xl p-6 shadow-card">
                        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3 pb-4 border-b border-[#F0ECE9] mb-5">
                            <div class="flex items-center gap-2.5">
                                <span class="text-xl">📝</span>
                                <div>
                                    <h3 class="text-[#1C1C1C] font-bold text-base">Catatan Produksi & Memo Dapur</h3>
                                    <p class="text-xs text-[#828282]">Instruksi khusus bahan, request irisan roti, dan memo kurir trip obrok</p>
                                </div>
                            </div>
                            <button onclick="openNoteModal()" 
                                class="bg-[#FF9B45] hover:bg-[#E88C3D] text-white text-xs font-semibold px-4 py-2 rounded-xl flex items-center gap-1.5 shadow-2xs transition-colors self-start sm:self-auto">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path></svg>
                                <span>+ Tambah Catatan</span>
                            </button>
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-3 gap-4" id="kitchenNotesList">
                            <?php foreach ($kitchen_notes as $note): ?>
                                <div class="bg-[#FBF9F7] rounded-xl p-4 border border-[#F0ECE9]">
                                    <div class="flex items-center justify-between text-xs font-bold mb-1.5">
                                        <span class="<?= $note['tag_class'] ?>"><?= htmlspecialchars($note['tag']) ?></span>
                                        <span class="text-[#828282] font-normal"><?= htmlspecialchars($note['time']) ?></span>
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
                                <select id="calendarMonthSelect" onchange="filterCalendarByMonth(this.value)" class="appearance-none bg-white border border-[#E0E0E0] rounded-lg pl-3.5 pr-9 py-2 text-sm text-[#1C1C1C] font-semibold focus:outline-none focus:border-[#FF9B45] cursor-pointer shadow-2xs">
                                    <option value="<?= date('Y-m') ?>"><?= $currentMonthIndo ?></option>
                                    <option value="<?= date('Y-m', strtotime('-1 month')) ?>"><?= ($monthsIndoFull[date('m', strtotime('-1 month'))] ?? '') . ' ' . date('Y', strtotime('-1 month')) ?></option>
                                    <option value="<?= date('Y-m', strtotime('+1 month')) ?>"><?= ($monthsIndoFull[date('m', strtotime('+1 month'))] ?? '') . ' ' . date('Y', strtotime('+1 month')) ?></option>
                                </select>
                                <div class="absolute inset-y-0 right-0 pr-3 flex items-center pointer-events-none text-[#828282]">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path></svg>
                                </div>
                            </div>

                            <!-- Dropdown Tipe Pengiriman -->
                            <div class="relative">
                                <select id="shippingTypeSelect" onchange="filterShippingType(this.value)" class="appearance-none bg-white border border-[#E0E0E0] rounded-lg pl-3.5 pr-9 py-2 text-sm text-[#1C1C1C] font-medium focus:outline-none focus:border-[#FF9B45] cursor-pointer shadow-2xs">
                                    <option value="all">Semua Pengiriman</option>
                                    <option value="kurir">Kurir Toko (Obrok)</option>
                                    <option value="toko">Ambil di Toko</option>
                                </select>
                                <div class="absolute inset-y-0 right-0 pr-3 flex items-center pointer-events-none text-[#828282]">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path></svg>
                                </div>
                            </div>

                            <!-- Badge 3 Hari Pengiriman Aktif -->
                            <span class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-full text-xs font-semibold bg-[#FEF6EE] text-[#D97706] border border-[#FDE6D2]">
                                <span class="w-2 h-2 rounded-full bg-[#FF9B45]"></span>
                                <span>Kapasitas Trip: Maks. 150 pcs/Motor</span>
                            </span>
                        </div>

                        <!-- Button Tambah Pesanan -->
                        <button onclick="openOrderModal()" class="bg-[#FF9B45] hover:bg-[#E88C3D] text-white text-sm font-semibold px-4 py-2.5 rounded-lg flex items-center gap-2 transition-all shadow-button-orange active:scale-95 shrink-0">
                            <img src="assets/icons/plus.svg" alt="Tambah" class="w-4 h-4">
                            <span>+ Tambah Pesanan</span>
                        </button>
                    </div>

                    <!-- Main Big Calendar Card -->
                    <div class="bg-white rounded-2xl border border-[#F0ECE9] p-6 shadow-card mb-6">
                        <!-- Calendar Header: Month Title & Legend -->
                        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 pb-4 border-b border-[#F0ECE9] mb-4">
                            <div class="flex items-center gap-3">
                                <h3 class="text-[#1C1C1C] font-bold text-lg" id="calendarGridTitle"><?= $currentMonthIndo ?></h3>
                                <div class="flex items-center gap-1">
                                    <button onclick="prevCalendarMonth()" class="p-1 hover:bg-[#F5F3F1] rounded text-[#828282] transition-colors" title="Bulan Sebelumnya">
                                        <img src="assets/icons/chevron_left.svg" alt="Prev" class="w-4 h-4">
                                    </button>
                                    <button onclick="nextCalendarMonth()" class="p-1 hover:bg-[#F5F3F1] rounded text-[#828282] transition-colors" title="Bulan Berikutnya">
                                        <img src="assets/icons/chevron_right_calendar.svg" alt="Next" class="w-4 h-4">
                                    </button>
                                </div>
                            </div>

                            <!-- Legend Pills -->
                            <div class="flex items-center gap-4 text-xs">
                                <div class="flex items-center gap-1.5">
                                    <span class="w-2.5 h-2.5 rounded-full bg-[#FF9B45]"></span>
                                    <span class="text-[#544337] font-medium">Aktif</span>
                                </div>
                                <div class="flex items-center gap-1.5">
                                    <span class="w-2.5 h-2.5 rounded-full border-2 border-[#FF9B45] bg-white"></span>
                                    <span class="text-[#544337] font-medium">Hari Ini</span>
                                </div>
                                <div class="flex items-center gap-1.5">
                                    <span class="w-2.5 h-2.5 rounded-full bg-[#27AE60]"></span>
                                    <span class="text-[#544337] font-medium">Selesai</span>
                                </div>
                            </div>
                        </div>

                        <!-- Day Names Header (SEN - MIN) -->
                        <div class="grid grid-cols-7 text-center text-xs font-bold text-[#828282] uppercase tracking-wider py-2 border-b border-[#F0ECE9]">
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
                        <div class="pt-4 mt-4 border-t border-[#F0ECE9] flex items-center gap-2 text-xs text-[#828282]">
                            <svg class="w-4 h-4 text-[#D97706]" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                            <span>Klik tanggal mana saja pada kalender untuk melihat daftar pengiriman roti dan status kurir.</span>
                        </div>
                    </div>

                    <!-- Bottom Delivery Schedule (Interactive Filtered View) -->
                    <div class="bg-white rounded-2xl border border-[#F0ECE9] p-6 shadow-card" id="deliveryScheduleSection">
                        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-2 pb-4 border-b border-[#F0ECE9] mb-5">
                            <div class="flex items-center gap-2.5">
                                <h3 class="text-[#1C1C1C] font-bold text-base" id="deliveryScheduleTitle">Jadwal Pengiriman: <?= $todayFormatted ?></h3>
                                <span class="bg-[#27AE60]/15 text-[#1B7A43] text-xs font-semibold px-2.5 py-0.5 rounded-full" id="deliveryBatchBadge">
                                    2 Batch Siap
                                </span>
                            </div>
                            <span class="text-xs text-[#828282]" id="deliveryCountLabel">2 Pengiriman Terjadwal</span>
                        </div>

                        <!-- 3 Cards Grid -->
                        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-5" id="deliveryCardsGrid">
                            
                            <!-- Card 1: Ibu Sari -->
                            <div class="bg-[#FBF9F7] rounded-xl p-4 border border-[#F0ECE9] flex flex-col justify-between delivery-card" data-type="kurir">
                                <div>
                                    <div class="flex items-center justify-between text-xs mb-2">
                                        <span class="font-mono text-[#D97706] font-semibold">#ORD-089</span>
                                        <div class="flex items-center gap-1.5">
                                            <span class="text-[#828282]">🕒 14:00 WIB</span>
                                            <span class="bg-[#828282]/15 text-[#544337] px-2 py-0.5 rounded-full text-[10px] font-semibold">Belum Bayar</span>
                                        </div>
                                    </div>
                                    <h4 class="text-[#1C1C1C] font-bold text-sm">Ibu Sari</h4>
                                    <p class="text-xs text-[#544337] mt-1">Roti Tawar 20 pcs, Donat 1 mika</p>
                                </div>
                                <div class="flex items-center justify-between pt-3 mt-3 border-t border-[#EBE8E5] text-xs">
                                    <span class="bg-[#FF9B45]/15 text-[#924C00] font-semibold px-2.5 py-1 rounded-md text-[11px]">🛵 Kurir Toko (Siap Antar)</span>
                                    <button type="button" onclick="openOrderDetailById('ORD-089')" class="text-[#924C00] hover:text-[#FF9B45] font-semibold flex items-center gap-1">
                                        <span>Detail</span>
                                        <span>&rarr;</span>
                                    </button>
                                </div>
                            </div>

                            <!-- Card 2: Bu Rina -->
                            <div class="bg-[#FBF9F7] rounded-xl p-4 border border-[#F0ECE9] flex flex-col justify-between delivery-card" data-type="toko">
                                <div>
                                    <div class="flex items-center justify-between text-xs mb-2">
                                        <span class="font-mono text-[#27AE60] font-semibold">#ORD-084</span>
                                        <div class="flex items-center gap-1.5">
                                            <span class="text-[#828282]">🕒 16:30 WIB</span>
                                            <span class="bg-[#27AE60]/15 text-[#1B7A43] px-2 py-0.5 rounded-full text-[10px] font-semibold">Lunas</span>
                                        </div>
                                    </div>
                                    <h4 class="text-[#1C1C1C] font-bold text-sm">Bu Rina</h4>
                                    <p class="text-xs text-[#544337] mt-1">Donat Coklat 2 mika (12 pcs)</p>
                                </div>
                                <div class="flex items-center justify-between pt-3 mt-3 border-t border-[#EBE8E5] text-xs">
                                    <span class="bg-gray-100 text-[#544337] font-semibold px-2.5 py-1 rounded-md text-[11px]">🏪 Ambil di Toko</span>
                                    <button type="button" onclick="openOrderDetailById('ORD-084')" class="text-[#924C00] hover:text-[#FF9B45] font-semibold flex items-center gap-1">
                                        <span>Detail</span>
                                        <span>&rarr;</span>
                                    </button>
                                </div>
                            </div>

                            <!-- Card 3: Action Panel -->
                            <div class="bg-[#FBF9F7] rounded-xl p-4 border border-[#F0ECE9] flex flex-col justify-between">
                                <div>
                                    <h4 class="text-[#1C1C1C] font-bold text-sm mb-1">Aksi Cepat Pengiriman</h4>
                                    <p class="text-xs text-[#828282] leading-relaxed">Perbarui status semua pesanan hari ini atau cetak dokumen surat jalan untuk kurir.</p>
                                </div>
                                <div class="space-y-2 mt-4">
                                    <!-- Konfirmasi Semua Siap Kirim Riil -->
                                    <form method="POST" action="pesanan.php">
                                        <input type="hidden" name="action" value="batch_siap_kirim">
                                        <button type="submit" class="w-full bg-[#FF9B45] hover:bg-[#E88C3D] text-white font-semibold text-xs py-2.5 rounded-lg flex items-center justify-center gap-1.5 shadow-xs transition-colors">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
                                            <span>Konfirmasi Semua Siap Kirim</span>
                                        </button>
                                    </form>
                                    
                                    <!-- Cetak Rekap Surat Jalan (Hari Ini) -->
                                    <button type="button" onclick="printDeliverySlip()" 
                                        class="w-full bg-white hover:bg-gray-100 text-[#544337] font-medium text-xs py-2.5 rounded-lg border border-[#E0E0E0] flex items-center justify-center gap-1.5 transition-colors">
                                        <span>🖨️</span>
                                        <span>Cetak Rekap Surat Jalan (Hari Ini)</span>
                                    </button>
                                </div>
                            </div>

                        </div>
                    </div>

                </div>

            </main>
        </div>
    </div>

    <!-- ============================================================== -->
    <!-- MODAL POPOUT: TAMBAH PESANAN BARU (STICKY HEADER & FOOTER)     -->
    <!-- ============================================================== -->
    <div id="orderModal" class="fixed inset-0 z-50 hidden flex items-center justify-center p-4">
        <!-- Backdrop with blur -->
        <div onclick="closeOrderModal()" class="fixed inset-0 bg-black/50 backdrop-blur-xs transition-opacity duration-200"></div>

        <!-- Modal Card Container (Structured Header, Scroll Body, Sticky Footer) -->
        <div class="bg-white rounded-2xl max-w-[720px] w-full shadow-2xl relative z-10 animate-in fade-in zoom-in-95 duration-150 flex flex-col max-h-[90vh] overflow-hidden">
            
            <!-- Sticky Header -->
            <div class="px-6 py-4 border-b border-[#F0ECE9] flex items-center justify-between shrink-0 bg-white">
                <div>
                    <h3 class="text-lg font-bold text-[#1C1C1C]">Tambah Pesanan Baru</h3>
                    <p class="text-[#828282] text-xs mt-0.5">Isi detail pesanan, rincian produk bakery, dan jadwal pengiriman.</p>
                </div>
                <button onclick="closeOrderModal()" class="text-[#828282] hover:text-[#1C1C1C] p-1.5 rounded-lg hover:bg-[#F5F3F1] transition-colors" title="Tutup" aria-label="Tutup Modal">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                </button>
            </div>

            <!-- Scrollable Body -->
            <form id="orderForm" method="POST" action="pesanan.php" onsubmit="handleSaveOrder(event)" class="flex-1 overflow-y-auto p-6 space-y-6">
                <input type="hidden" name="action" value="add_order">
                <input type="hidden" name="grand_total" id="hiddenGrandTotal" value="110000">
                <input type="hidden" name="items_desc" id="hiddenItemsDesc" value="Roti Tawar Kupas (4 pcs), Donat Coklat (2 mika)">
                <input type="hidden" name="payment_status" id="selectedPaymentStatus" value="Belum Bayar">

                <!-- SECTION 1: DATA PELANGGAN -->
                <div>
                    <label for="custSearchInput" class="text-xs font-bold text-[#1C1C1C] uppercase tracking-wider block mb-2">Data Pelanggan</label>
                    
                    <!-- Search Input Pelanggan -->
                    <div class="relative mb-2">
                        <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-[#828282]">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path></svg>
                        </div>
                        <input type="text" id="custSearchInput" name="customer_name" value="Ibu Sari" 
                            class="w-full bg-white border border-[#E0E0E0] rounded-lg pl-10 pr-4 py-2 text-sm text-[#1C1C1C] focus:border-[#FF9B45] focus:ring-1 focus:ring-[#FF9B45] focus:outline-none transition-colors">
                    </div>

                    <!-- Detail Pelanggan Box (3 Columns) -->
                    <div class="bg-[#F9F7F5] border border-[#F0ECE9] rounded-xl p-3.5 grid grid-cols-1 sm:grid-cols-3 gap-3 text-xs">
                        <div>
                            <p class="text-[#828282] uppercase text-[10px] font-semibold">Nama Pelanggan</p>
                            <p class="font-bold text-[#1C1C1C] mt-0.5" id="previewCustName">Ibu Sari</p>
                        </div>
                        <div>
                            <p class="text-[#828282] uppercase text-[10px] font-semibold">No. WhatsApp</p>
                            <p class="font-bold text-[#1C1C1C] mt-0.5">0812-3456-7890</p>
                        </div>
                        <div>
                            <p class="text-[#828282] uppercase text-[10px] font-semibold">Alamat Pengiriman</p>
                            <p class="font-medium text-[#1C1C1C] mt-0.5">Komplek Melati No. 4, Blok B</p>
                        </div>
                    </div>
                </div>

                <!-- SECTION 2: DETAIL PESANAN -->
                <div>
                    <div class="flex items-center justify-between mb-3">
                        <h4 class="text-xs font-bold text-[#1C1C1C] uppercase tracking-wider">Detail Produk & Minimal Order</h4>
                        <span class="text-xs text-[#828282]" id="itemCountLabel">2 Item Dipilih</span>
                    </div>

                    <!-- Items Table -->
                    <div class="border border-[#F0ECE9] rounded-xl overflow-hidden mb-3">
                        <table class="w-full text-left text-xs">
                            <thead class="bg-[#FBF9F7] text-[#828282] font-semibold uppercase border-b border-[#F0ECE9]">
                                <tr>
                                    <th class="px-3.5 py-2.5">Produk</th>
                                    <th class="px-3.5 py-2.5">Kemasan</th>
                                    <th class="px-3.5 py-2.5 w-24">Jumlah</th>
                                    <th class="px-3.5 py-2.5">Harga Satuan</th>
                                    <th class="px-3.5 py-2.5">Subtotal</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-[#F0ECE9] bg-white">
                                <!-- Row 1: Roti Tawar (Initial qty 5 meets MOQ) -->
                                <tr id="orderRow1">
                                    <td class="px-3.5 py-3">
                                        <p class="font-bold text-[#1C1C1C]">Roti Tawar Kupas Special</p>
                                        <p class="text-[10px] text-[#27AE60] font-semibold">Min. 5 pcs</p>
                                    </td>
                                    <td class="px-3.5 py-3 text-[#544337]">Mika (10 pcs)</td>
                                    <td class="px-3.5 py-3">
                                        <input type="number" id="qtyItem1" name="qty_item_1" value="5" min="1" onchange="calculateOrderTotal()" 
                                            class="w-16 px-2 py-1 border border-[#27AE60] text-[#1C1C1C] font-bold rounded text-center focus:outline-none transition-colors">
                                    </td>
                                    <td class="px-3.5 py-3 text-[#544337]">Rp 15.000</td>
                                    <td class="px-3.5 py-3 font-bold text-[#1C1C1C]" id="subtotal1">Rp 75.000</td>
                                </tr>

                                <!-- Row 2: Donat Kentang Coklat -->
                                <tr id="orderRow2">
                                    <td class="px-3.5 py-3">
                                        <p class="font-bold text-[#1C1C1C]">Donat Kentang Coklat Meises</p>
                                        <p class="text-[10px] text-[#828282]">Ready Batch Pagi</p>
                                    </td>
                                    <td class="px-3.5 py-3 text-[#544337]">Mika (6 pcs)</td>
                                    <td class="px-3.5 py-3">
                                        <input type="number" id="qtyItem2" name="qty_item_2" value="2" min="1" onchange="calculateOrderTotal()" 
                                            class="w-16 px-2 py-1 border border-[#E0E0E0] text-[#1C1C1C] font-semibold rounded text-center focus:outline-none focus:border-[#FF9B45] transition-colors">
                                    </td>
                                    <td class="px-3.5 py-3 text-[#544337]">Rp 25.000</td>
                                    <td class="px-3.5 py-3 font-bold text-[#1C1C1C]" id="subtotal2">Rp 50.000</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>

                    <!-- Validation Callout Alert (MOQ Warning) -->
                    <div id="moqAlertBox" class="bg-[#FFF5F5] border border-[#EB5757]/30 rounded-xl p-3 flex items-start gap-2.5 hidden">
                        <span class="text-base text-[#EB5757] shrink-0">🔒</span>
                        <p class="text-xs text-[#EB5757] leading-relaxed">
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
                                <label for="deliveryDateInput" class="block text-xs font-semibold text-[#333333] mb-1">Tanggal Pengiriman</label>
                                <input type="date" id="deliveryDateInput" name="delivery_date" value="<?= date('Y-m-d') ?>" 
                                    class="w-full bg-white border border-[#E0E0E0] rounded-lg px-3 py-2 text-xs text-[#1C1C1C] focus:border-[#FF9B45] focus:outline-none">
                            </div>

                            <div>
                                <label for="deliveryDestinationInput" class="block text-xs font-semibold text-[#333333] mb-1">Alamat Tujuan Pengiriman</label>
                                <input type="text" id="deliveryDestinationInput" name="destination" value="Dikirim ke Komplek Melati No. 4" 
                                    class="w-full bg-white border border-[#E0E0E0] rounded-lg px-3 py-2 text-xs text-[#1C1C1C] focus:border-[#FF9B45] focus:outline-none">
                            </div>

                            <div>
                                <label for="orderNotesTextarea" class="block text-xs font-semibold text-[#333333] mb-1">Catatan Dapur / Driver</label>
                                <textarea id="orderNotesTextarea" name="notes" rows="2" 
                                    class="w-full bg-white border border-[#E0E0E0] rounded-lg p-3 text-xs text-[#1C1C1C] focus:border-[#FF9B45] focus:outline-none">Roti tawar diiris rapi, kurir motor obrok tiba sebelum 15:00.</textarea>
                            </div>
                        </div>

                        <!-- Right: Total Tagihan & Status Bayar -->
                        <div class="bg-[#F9F7F5] border border-[#F0ECE9] rounded-2xl p-4 flex flex-col justify-between">
                            <div>
                                <p class="text-[11px] font-bold text-[#828282] uppercase tracking-wider">TOTAL TAGIHAN</p>
                                <h3 class="text-2xl font-bold text-[#FF9B45] mt-0.5 tracking-tight" id="orderGrandTotal">Rp 125.000</h3>
                                
                                <div class="mt-4">
                                    <label class="block text-xs font-semibold text-[#333333] mb-2">Status Pembayaran</label>
                                    <div class="grid grid-cols-3 gap-2">
                                        <button type="button" onclick="selectPaymentStatus('Belum Bayar', this)" id="payBtn1" 
                                            class="py-1.5 px-2 rounded-lg text-xs font-semibold bg-[#544337] text-white shadow-2xs transition-colors">
                                            Belum Bayar
                                        </button>
                                        <button type="button" onclick="selectPaymentStatus('Hutang', this)" id="payBtn2" 
                                            class="py-1.5 px-2 rounded-lg text-xs font-semibold bg-white border border-[#E0E0E0] text-[#544337] hover:bg-gray-50 transition-colors">
                                            Hutang
                                        </button>
                                        <button type="button" onclick="selectPaymentStatus('Lunas', this)" id="payBtn3" 
                                            class="py-1.5 px-2 rounded-lg text-xs font-semibold bg-white border border-[#E0E0E0] text-[#544337] hover:bg-gray-50 transition-colors">
                                            Lunas
                                        </button>
                                    </div>
                                </div>
                            </div>

                            <div class="mt-4">
                                <label for="paymentNoteInput" class="block text-xs font-semibold text-[#333333] mb-1">Metode Pembayaran</label>
                                <input type="text" id="paymentNoteInput" name="payment_note" value="Transfer BCA / QRIS" 
                                    class="w-full bg-white border border-[#E0E0E0] rounded-lg px-3 py-2 text-xs text-[#1C1C1C] focus:border-[#FF9B45] focus:outline-none">
                            </div>
                        </div>

                    </div>
                </div>

                <!-- Sticky Footer Embedded in Form -->
                <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3 pt-4 border-t border-[#F0ECE9]">
                    <div id="footerMoqWarning" class="flex items-center gap-1.5 text-xs text-[#27AE60] font-medium">
                        <span>✓</span>
                        <span>Minimum order terpenuhi</span>
                    </div>

                    <div class="flex items-center gap-3 ml-auto">
                        <button type="button" onclick="closeOrderModal()" 
                            class="bg-white hover:bg-gray-100 text-[#544337] text-xs font-semibold px-4 py-2.5 rounded-lg border border-[#E0E0E0] transition-colors">
                            Batal
                        </button>

                        <button type="submit" id="submitOrderBtn" 
                            class="bg-[#FF9B45] hover:bg-[#E88C3D] text-white text-xs font-semibold px-5 py-2.5 rounded-lg flex items-center gap-1.5 transition-all shadow-button-orange active:scale-95">
                            <span id="submitLockIcon">✓</span>
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

        <div class="bg-white rounded-2xl max-w-lg w-full p-6 shadow-2xl relative z-10 animate-in fade-in zoom-in-95 duration-150">
            <div class="flex items-start justify-between pb-3 border-b border-[#F0ECE9] mb-4">
                <div>
                    <div class="flex items-center gap-2">
                        <span class="font-mono text-[#FF9B45] font-bold text-base" id="modalDetailOrderId">#ORD-000</span>
                        <span class="text-xs px-2.5 py-0.5 rounded-full font-semibold" id="modalDetailStatusBadge">Menunggu</span>
                    </div>
                    <h3 class="text-lg font-bold text-[#1C1C1C] mt-1" id="modalDetailCustomer">Nama Pelanggan</h3>
                </div>
                <button onclick="closeOrderDetailModal()" class="text-[#828282] hover:text-[#1C1C1C] p-1.5 rounded-lg hover:bg-[#F5F3F1]">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                </button>
            </div>

            <div class="space-y-3.5 text-xs">
                <div class="bg-[#FBF9F7] p-3 rounded-xl border border-[#F0ECE9] space-y-2">
                    <div class="flex justify-between">
                        <span class="text-[#828282]">Rincian Item:</span>
                        <span class="font-semibold text-[#1C1C1C]" id="modalDetailItems">-</span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-[#828282]">Tujuan Pengiriman:</span>
                        <span class="font-semibold text-[#1C1C1C]" id="modalDetailDestination">-</span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-[#828282]">Waktu Pengiriman:</span>
                        <span class="font-semibold text-[#1C1C1C]" id="modalDetailTime">-</span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-[#828282]">Status Bayar:</span>
                        <span class="font-bold text-[#1C1C1C]" id="modalDetailPayStatus">-</span>
                    </div>
                </div>

                <!-- Update Status Alur Kerja -->
                <div>
                    <label class="block font-semibold text-[#333333] mb-2">Ubah Status Alur Kerja:</label>
                    <div class="grid grid-cols-4 gap-2">
                        <button type="button" onclick="changeModalOrderStatus('menunggu')" class="py-2 px-1 text-center font-bold rounded-lg border border-[#E2B93B] text-[#886C12] bg-[#E2B93B]/10 hover:bg-[#E2B93B]/20 text-[11px]">
                            Menunggu
                        </button>
                        <button type="button" onclick="changeModalOrderStatus('proses')" class="py-2 px-1 text-center font-bold rounded-lg border border-[#FF9B45] text-[#924C00] bg-[#FF9B45]/10 hover:bg-[#FF9B45]/20 text-[11px]">
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

            <div class="pt-4 mt-5 border-t border-[#F0ECE9] flex justify-end">
                <button type="button" onclick="closeOrderDetailModal()" class="bg-[#F5F3F1] hover:bg-[#EBE8E5] text-[#544337] px-4 py-2 rounded-lg text-xs font-semibold">
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

        <div class="bg-white rounded-2xl max-w-md w-full p-6 shadow-2xl relative z-10 animate-in fade-in zoom-in-95 duration-150">
            <div class="flex items-center justify-between pb-3 border-b border-[#F0ECE9] mb-4">
                <h3 class="text-base font-bold text-[#1C1C1C]">Tambah Catatan / Memo Dapur</h3>
                <button onclick="closeNoteModal()" class="text-[#828282] hover:text-[#1C1C1C] p-1">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                </button>
            </div>

            <form method="POST" action="pesanan.php" class="space-y-4">
                <input type="hidden" name="action" value="add_kitchen_note">
                <div>
                    <label for="noteTagInput" class="block text-xs font-semibold text-[#333333] mb-1">Judul / Tag Catatan</label>
                    <input type="text" id="noteTagInput" name="note_tag" placeholder="Contoh: ! Prioritas #ORD-089 atau Stok Coklat" required
                        class="w-full bg-white border border-[#E0E0E0] rounded-lg px-3 py-2 text-xs text-[#1C1C1C] focus:border-[#FF9B45] focus:outline-none">
                </div>

                <div>
                    <label for="noteTextInput" class="block text-xs font-semibold text-[#333333] mb-1">Isi Memo Dapur</label>
                    <textarea id="noteTextInput" name="note_text" rows="3" placeholder="Tulis instruksi khusus untuk tim produksi dan kurir..." required
                        class="w-full bg-white border border-[#E0E0E0] rounded-lg p-3 text-xs text-[#1C1C1C] focus:border-[#FF9B45] focus:outline-none"></textarea>
                </div>

                <div class="flex items-center justify-end gap-2 pt-3 border-t border-[#F0ECE9]">
                    <button type="button" onclick="closeNoteModal()" class="px-4 py-2 text-xs font-semibold text-[#544337] bg-[#F5F3F1] hover:bg-[#EBE8E5] rounded-lg">
                        Batal
                    </button>
                    <button type="submit" class="px-4 py-2 text-xs font-semibold text-white bg-[#FF9B45] hover:bg-[#E88C3D] rounded-lg shadow-button-orange">
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
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"></path>
                </svg>
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
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"></path>
                    </svg>
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
        // Data Pesanan untuk Frontend Interactivity
        const ordersData = <?= json_encode($master_orders) ?>;
        let selectedDetailOrderId = null;

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

                tabBtnKalender.className = 'px-4 py-2 rounded-lg text-sm font-semibold flex items-center gap-2 transition-all bg-[#FF9B45] text-white shadow-xs';
                tabBtnKanban.className = 'px-4 py-2 rounded-lg text-sm font-semibold flex items-center gap-2 transition-all text-[#828282] hover:text-[#1C1C1C] hover:bg-[#F5F3F1]';

                headerTitle.textContent = 'Pengiriman';
                headerBreadcrumb.textContent = 'Kalendar';
                pageSubtitle.textContent = 'Pantau penjadwalan produksi dapur, rute kurir, dan pengiriman harian.';
                renderInteractiveCalendar();
            } else {
                calendarView.classList.add('hidden');
                kanbanView.classList.remove('hidden');

                tabBtnKanban.className = 'px-4 py-2 rounded-lg text-sm font-semibold flex items-center gap-2 transition-all bg-[#FF9B45] text-white shadow-xs';
                tabBtnKalender.className = 'px-4 py-2 rounded-lg text-sm font-semibold flex items-center gap-2 transition-all text-[#828282] hover:text-[#1C1C1C] hover:bg-[#F5F3F1]';

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
                
                let dayHtml = '';
                if (isToday) {
                    dayHtml = `
                        <div onclick="selectDeliveryDate(this, '${dateStr}', ${d})" 
                             class="cal-day-cell p-2.5 rounded-xl bg-[#FFF8F2] border-2 border-[#FF9B45] flex flex-col items-center justify-between min-h-[64px] shadow-sm cursor-pointer hover:scale-105 transition-transform">
                            <span class="font-bold text-sm text-[#924C00]">${d}</span>
                            <span class="text-[10px] font-bold text-white bg-[#FF9B45] px-1.5 py-0.5 rounded shadow-2xs">Hari Ini</span>
                        </div>
                    `;
                } else if (d === 25) {
                    dayHtml = `
                        <div onclick="selectDeliveryDate(this, '${dateStr}', ${d})" 
                             class="cal-day-cell p-2.5 rounded-xl bg-[#F5F3F1] border border-[#EBE8E5] flex flex-col items-center justify-between min-h-[64px] cursor-pointer hover:bg-[#FFF8F2] transition-colors">
                            <span class="font-bold text-sm text-[#1C1C1C]">${d}</span>
                            <span class="text-[10px] font-semibold text-[#1B7A43] bg-[#27AE60]/15 px-1 py-0.5 rounded">✓ Selesai</span>
                        </div>
                    `;
                } else if (d === 27 || d === 29) {
                    dayHtml = `
                        <div onclick="selectDeliveryDate(this, '${dateStr}', ${d})" 
                             class="cal-day-cell p-2.5 rounded-xl bg-[#FEF6EE] border border-[#FDE6D2] flex flex-col items-center justify-between min-h-[64px] cursor-pointer hover:bg-[#FFF8F2] transition-colors">
                            <span class="font-bold text-sm text-[#D97706]">${d}</span>
                            <span class="text-[10px] font-medium text-[#D97706] bg-white px-1 py-0.5 rounded">1 Pesanan</span>
                        </div>
                    `;
                } else {
                    dayHtml = `
                        <div onclick="selectDeliveryDate(this, '${dateStr}', ${d})" 
                             class="cal-day-cell p-3 text-sm font-medium text-[#1C1C1C] rounded-xl hover:bg-[#FFF8F2] transition-colors cursor-pointer">
                            ${d}
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
            document.querySelectorAll('.cal-day-cell').forEach(c => c.classList.remove('ring-2', 'ring-[#FF9B45]'));
            cell.classList.add('ring-2', 'ring-[#FF9B45]');

            document.getElementById('deliveryScheduleTitle').textContent = `Jadwal Pengiriman: ${dayNum} ${document.getElementById('calendarGridTitle').textContent}`;
            document.getElementById('deliveryBatchBadge').textContent = 'Terpilih';
            document.getElementById('deliveryCountLabel').textContent = 'Filter tanggal aktif';
            showToast(`Menampilkan jadwal pengiriman tanggal ${dayNum}`);
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

        // Modal Open / Close Order Modal
        function openOrderModal() {
            const modal = document.getElementById('orderModal');
            modal.classList.remove('hidden');
            document.body.style.overflow = 'hidden';
            calculateOrderTotal();
        }

        function closeOrderModal() {
            const modal = document.getElementById('orderModal');
            modal.classList.add('hidden');
            document.body.style.overflow = '';
        }

        // Calculation & MOQ Validation in Modal
        function calculateOrderTotal() {
            const q1 = parseInt(document.getElementById('qtyItem1').value) || 0;
            const q2 = parseInt(document.getElementById('qtyItem2').value) || 0;

            const sub1 = q1 * 15000;
            const sub2 = q2 * 25000;
            const total = sub1 + sub2;

            document.getElementById('subtotal1').textContent = 'Rp ' + new Intl.NumberFormat('id-ID').format(sub1);
            document.getElementById('subtotal2').textContent = 'Rp ' + new Intl.NumberFormat('id-ID').format(sub2);
            document.getElementById('orderGrandTotal').textContent = 'Rp ' + new Intl.NumberFormat('id-ID').format(total);
            document.getElementById('hiddenGrandTotal').value = total;

            const qtyInput1 = document.getElementById('qtyItem1');
            const alertBox = document.getElementById('moqAlertBox');
            const submitBtn = document.getElementById('submitOrderBtn');
            const lockIcon = document.getElementById('submitLockIcon');
            const footerWarning = document.getElementById('footerMoqWarning');
            const currentMoqQty = document.getElementById('currentMoqQty');

            currentMoqQty.textContent = q1;

            if (q1 < 5) {
                qtyInput1.className = 'w-16 px-2 py-1 border-2 border-[#EB5757] text-[#EB5757] font-bold rounded text-center focus:outline-none transition-colors';
                alertBox.classList.remove('hidden');
                submitBtn.disabled = true;
                submitBtn.className = 'bg-[#D8D2CB] text-white cursor-not-allowed text-xs font-semibold px-5 py-2.5 rounded-lg flex items-center gap-1.5 transition-all shadow-xs';
                lockIcon.textContent = '🔒';
                footerWarning.innerHTML = '<span class="text-[#EB5757]">ⓘ Penuhi minimal order roti tawar (5 pcs)</span>';
            } else {
                qtyInput1.className = 'w-16 px-2 py-1 border border-[#27AE60] text-[#1C1C1C] font-bold rounded text-center focus:outline-none transition-colors';
                alertBox.classList.add('hidden');
                submitBtn.disabled = false;
                submitBtn.className = 'bg-[#FF9B45] hover:bg-[#E88C3D] text-white cursor-pointer text-xs font-semibold px-5 py-2.5 rounded-lg flex items-center gap-1.5 transition-all shadow-button-orange active:scale-95';
                lockIcon.textContent = '✓';
                footerWarning.innerHTML = '<span class="text-[#27AE60]">✓ Minimum order terpenuhi</span>';
            }
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
            calculateOrderTotal();
            renderInteractiveCalendar();
        });
    </script>
</body>
</html>
