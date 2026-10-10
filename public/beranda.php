<?php
session_start();

// Proteksi Autentikasi: Hanya user yang sudah login yang diizinkan mengakses Beranda
if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true) {
    header("Location: login.php");
    exit();
}

// Coba koneksi database jika config tersedia
$db_connected = false;
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

$pending_orders = [
    [
        'id' => 'ORD-20250926-01',
        'customer' => 'Ibu Ratna Sari',
        'product' => 'Roti Sisir Butter (50 pcs)',
        'deadline' => 'Hari Ini, 14:00',
        'is_urgent' => true,
        'payment_status' => 'Belum Bayar',
        'payment_class' => 'bg-[#828282]/15 text-[#544337]'
    ],
    [
        'id' => 'ORD-20250926-02',
        'customer' => 'Pak Hendra Wijaya',
        'product' => 'Roti Sobek Cokelat Keju (30 box)',
        'deadline' => 'Hari Ini, 16:30',
        'is_urgent' => true,
        'payment_status' => 'Hutang',
        'payment_class' => 'bg-[#E2B93B]/20 text-[#886C12]'
    ],
    [
        'id' => 'ORD-20250926-03',
        'customer' => 'Toko Berkah Jaya',
        'product' => 'Donat Kentang Tabur Salju (100 pcs)',
        'deadline' => 'Besok, 09:00',
        'is_urgent' => false,
        'payment_status' => 'Lunas',
        'payment_class' => 'bg-[#27AE60]/15 text-[#1B7A43]'
    ],
    [
        'id' => 'ORD-20250926-04',
        'customer' => 'Kafe Kopi Seduh',
        'product' => 'Baguette & Croissant Mini (40 pcs)',
        'deadline' => '28 Sep 2025',
        'is_urgent' => false,
        'payment_status' => 'Belum Bayar',
        'payment_class' => 'bg-[#828282]/15 text-[#544337]'
    ],
    [
        'id' => 'ORD-20250926-05',
        'customer' => 'Bu Siti Rahayu',
        'product' => 'Roti Tawar Gandum Spesial (25 pack)',
        'deadline' => '29 Sep 2025',
        'is_urgent' => false,
        'payment_status' => 'Lunas',
        'payment_class' => 'bg-[#27AE60]/15 text-[#1B7A43]'
    ]
];

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

// Opsi query database bila aktif
if (file_exists("../config/config.php")) {
    try {
        @require_once "../config/config.php";
        if (defined('DB_HOST') && defined('DB_NAME') && defined('DB_USER')) {
            $pdo = new PDO("mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=utf8mb4", DB_USER, DB_PASS, [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_SILENT,
                PDO::ATTR_TIMEOUT => 2
            ]);
            $db_connected = true;
            // Jika ada data riil di DB, hitung agregasi sederhana (opsional):
            $stmt = $pdo->query("SELECT COUNT(*) FROM pesanan WHERE DATE(tanggal_pesan) = CURDATE()");
            if ($stmt) {
                $countToday = $stmt->fetchColumn();
                if ($countToday > 0) {
                    $kpi_data['total_orders'] = $countToday;
                }
            }
        }
    } catch (Exception $e) {
        $db_connected = false;
    }
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
                    <a href="logout.php" title="Keluar / Logout" class="p-1.5 text-white/60 hover:text-white transition-colors shrink-0">
                        <img src="assets/icons/logout.svg" alt="Logout" class="w-5 h-5">
                    </a>
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
                
                <!-- TOP BANNER: Greeting & Operational Actions -->
                <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 mb-6">
                    <div>
                        <p class="text-[#924C00] text-xs font-semibold tracking-wider uppercase mb-1">RINGKASAN OPERASIONAL TOKO</p>
                        <h2 class="text-[#1B1C1B] text-2xl font-bold tracking-tight">Beranda Manajemen Pesanan</h2>
                    </div>

                    <!-- Action Buttons -->
                    <div class="flex items-center gap-3 shrink-0">
                        <!-- Unduh Rekap Harian Button -->
                        <button onclick="handleDownloadRecap()" class="bg-[#F5F3F1] hover:bg-[#ECE9E6] text-[#1B1C1B] text-sm font-medium px-4 py-2.5 rounded-lg border border-[#E0E0E0]/60 flex items-center gap-2 transition-colors shadow-sm">
                            <img src="assets/icons/download.svg" alt="Unduh" class="w-4 h-4">
                            <span>Unduh Rekap Harian</span>
                        </button>

                        <!-- Tambah Pesanan Baru Button -->
                        <button onclick="openOrderModal()" class="bg-[#FF9B45] hover:bg-[#E88C3D] text-white text-sm font-semibold px-4 py-2.5 rounded-lg flex items-center gap-2 transition-all shadow-button-orange active:scale-95">
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
                    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3 mb-4">
                        <div class="flex items-center gap-3">
                            <h3 class="text-[#1C1C1C] text-base md:text-lg font-bold">Pesanan Perlu Tindakan</h3>
                            <span id="pending-count-badge" class="bg-[#E2B93B]/15 text-[#886C12] text-xs font-semibold px-2.5 py-0.5 rounded-full">
                                <?= count($pending_orders) ?> Pesanan
                            </span>
                        </div>

                        <!-- Live Search Input -->
                        <div class="relative w-full sm:w-60 md:w-64">
                            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                <img src="assets/icons/search.svg" alt="Cari" class="w-4 h-4 opacity-70">
                            </div>
                            <input type="text" id="orderSearchInput" onkeyup="filterOrdersTable()" placeholder="Cari pesanan..."
                                class="w-full bg-[#F5F3F1] border-0 rounded-lg pl-9 pr-3 py-1.5 text-xs md:text-sm text-[#1C1C1C] placeholder-[#9CA3AF] focus:ring-1 focus:ring-[#FF9B45] focus:outline-none transition-all">
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
                                    <tr class="<?= $idx % 2 === 1 ? 'bg-[#FFF8F2]' : 'bg-white' ?> hover:bg-[#FFF3E8] transition-colors order-row" data-search="<?= strtolower($order['customer'] . ' ' . $order['product'] . ' ' . $order['payment_status']) ?>">
                                        <!-- Nama Pelanggan -->
                                        <td class="px-5 py-4 text-[#1B1C1B] font-medium whitespace-nowrap">
                                            <?= htmlspecialchars($order['customer']) ?>
                                        </td>
                                        
                                        <!-- Produk -->
                                        <td class="px-5 py-4 text-[#544337]">
                                            <?= htmlspecialchars($order['product']) ?>
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
                                            <button onclick="viewOrderDetail('<?= htmlspecialchars($order['id']) ?>', '<?= htmlspecialchars($order['customer']) ?>', '<?= htmlspecialchars($order['product']) ?>', '<?= htmlspecialchars($order['deadline']) ?>', '<?= htmlspecialchars($order['payment_status']) ?>')"
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

    <!-- MODAL: ORDER DETAIL MODAL -->
    <div id="orderDetailModal" class="fixed inset-0 bg-black/50 z-50 flex items-center justify-center p-4 hidden backdrop-blur-xs transition-opacity duration-200">
        <div class="bg-white rounded-2xl max-w-lg w-full p-6 shadow-2xl relative animate-in fade-in zoom-in-95 duration-150">
            <div class="flex items-center justify-between pb-4 border-b border-[#F0ECE9]">
                <div class="flex items-center gap-2">
                    <span class="w-3 h-3 rounded-full bg-[#FF9B45]"></span>
                    <h3 class="font-bold text-lg text-[#1C1C1C]">Detail Pesanan</h3>
                </div>
                <button onclick="closeModal('orderDetailModal')" class="text-[#828282] hover:text-[#1C1C1C] p-1 rounded-lg">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                </button>
            </div>

            <div class="py-5 space-y-4">
                <div>
                    <p class="text-xs text-[#828282] uppercase font-semibold">Nomor Invoice / ID</p>
                    <p id="modalOrderId" class="text-base font-bold text-[#FF9B45]">-</p>
                </div>
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <p class="text-xs text-[#828282] uppercase font-semibold">Nama Pelanggan</p>
                        <p id="modalCustomer" class="text-sm font-semibold text-[#1C1C1C]">-</p>
                    </div>
                    <div>
                        <p class="text-xs text-[#828282] uppercase font-semibold">Status Pembayaran</p>
                        <p id="modalPayment" class="text-sm font-semibold text-[#1B7A43]">-</p>
                    </div>
                </div>
                <div>
                    <p class="text-xs text-[#828282] uppercase font-semibold">Rincian Produk</p>
                    <p id="modalProduct" class="text-sm text-[#544337] bg-[#F5F3F1] p-3 rounded-lg border border-[#EBE8E5] mt-1">-</p>
                </div>
                <div>
                    <p class="text-xs text-[#828282] uppercase font-semibold">Batas Waktu (Deadline)</p>
                    <p id="modalDeadline" class="text-sm font-semibold text-[#EB5757] mt-1">-</p>
                </div>
            </div>

            <div class="flex items-center justify-end gap-3 pt-4 border-t border-[#F0ECE9]">
                <button onclick="closeModal('orderDetailModal')" class="px-4 py-2 bg-[#F5F3F1] text-[#1B1C1B] rounded-lg text-sm font-medium hover:bg-[#EAE8E6] transition-colors">
                    Tutup
                </button>
                <button onclick="alert('Membuka halaman manajemen pesanan...'); closeModal('orderDetailModal');" class="px-4 py-2 bg-[#FF9B45] text-white rounded-lg text-sm font-semibold hover:bg-[#E88C3D] transition-colors">
                    Proses Pesanan
                </button>
            </div>
        </div>
    </div>

    <!-- MODAL: TAMBAH PESANAN CEPAT -->
    <div id="quickOrderModal" class="fixed inset-0 bg-black/50 z-50 flex items-center justify-center p-4 hidden backdrop-blur-xs transition-opacity duration-200">
        <div class="bg-white rounded-2xl max-w-md w-full p-6 shadow-2xl relative">
            <div class="flex items-center justify-between pb-4 border-b border-[#F0ECE9]">
                <div class="flex items-center gap-2">
                    <img src="assets/icons/plus.svg" class="w-5 h-5 p-1 bg-[#FF9B45] rounded-full text-white">
                    <h3 class="font-bold text-lg text-[#1C1C1C]">Tambah Pesanan Baru</h3>
                </div>
                <button onclick="closeModal('quickOrderModal')" class="text-[#828282] hover:text-[#1C1C1C] p-1 rounded-lg">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                </button>
            </div>

            <form onsubmit="handleQuickOrderSubmit(event)" class="py-4 space-y-4">
                <div>
                    <label class="block text-xs font-semibold text-[#333333] mb-1">Nama Pelanggan</label>
                    <input type="text" id="quickCustomerName" required placeholder="Contoh: Bu Ratna" class="w-full bg-white border border-[#E0E0E0] rounded-lg px-3 py-2 text-sm focus:border-[#FF9B45] focus:ring-1 focus:ring-[#FF9B45] outline-none">
                </div>
                <div>
                    <label class="block text-xs font-semibold text-[#333333] mb-1">Produk Pesanan</label>
                    <input type="text" id="quickProduct" required placeholder="Contoh: Roti Sisir Butter (50 pcs)" class="w-full bg-white border border-[#E0E0E0] rounded-lg px-3 py-2 text-sm focus:border-[#FF9B45] focus:ring-1 focus:ring-[#FF9B45] outline-none">
                </div>
                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="block text-xs font-semibold text-[#333333] mb-1">Batas Waktu</label>
                        <input type="text" id="quickDeadline" required placeholder="Hari Ini, 15:00" class="w-full bg-white border border-[#E0E0E0] rounded-lg px-3 py-2 text-sm focus:border-[#FF9B45] focus:ring-1 focus:ring-[#FF9B45] outline-none">
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-[#333333] mb-1">Status Bayar</label>
                        <select id="quickPayment" class="w-full bg-white border border-[#E0E0E0] rounded-lg px-3 py-2 text-sm focus:border-[#FF9B45] focus:ring-1 focus:ring-[#FF9B45] outline-none">
                            <option value="Belum Bayar">Belum Bayar</option>
                            <option value="Lunas">Lunas</option>
                            <option value="Hutang">Hutang</option>
                        </select>
                    </div>
                </div>

                <div class="flex items-center justify-end gap-3 pt-3 border-t border-[#F0ECE9]">
                    <button type="button" onclick="closeModal('quickOrderModal')" class="px-4 py-2 bg-[#F5F3F1] text-[#1B1C1B] rounded-lg text-sm font-medium hover:bg-[#EAE8E6]">
                        Batal
                    </button>
                    <button type="submit" class="px-5 py-2 bg-[#FF9B45] text-white rounded-lg text-sm font-semibold hover:bg-[#E88C3D] shadow-button-orange">
                        Simpan Pesanan
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- JAVASCRIPT LOGIC -->
    <script>
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

        // Live Table Search Filtering
        function filterOrdersTable() {
            const input = document.getElementById('orderSearchInput').value.toLowerCase();
            const rows = document.querySelectorAll('.order-row');
            let visibleCount = 0;

            rows.forEach(row => {
                const searchData = row.getAttribute('data-search') || '';
                if (searchData.includes(input)) {
                    row.style.display = '';
                    visibleCount++;
                } else {
                    row.style.display = 'none';
                }
            });

            // Update footer info
            const footerText = document.getElementById('table-footer-text');
            if (footerText) {
                footerText.textContent = `Menampilkan ${visibleCount} dari ${rows.length} pesanan`;
            }
        }

        // View Order Detail Modal
        function viewOrderDetail(id, customer, product, deadline, payment) {
            document.getElementById('modalOrderId').textContent = '#' + id;
            document.getElementById('modalCustomer').textContent = customer;
            document.getElementById('modalProduct').textContent = product;
            document.getElementById('modalDeadline').textContent = deadline;
            document.getElementById('modalPayment').textContent = payment;

            const modal = document.getElementById('orderDetailModal');
            modal.classList.remove('hidden');
        }

        // Open Add Order Modal
        function openOrderModal() {
            document.getElementById('quickOrderModal').classList.remove('hidden');
        }

        // Close Modals
        function closeModal(modalId) {
            document.getElementById(modalId).classList.add('hidden');
        }

        // Handle Download Daily Recap
        function handleDownloadRecap() {
            // Visual notification / print preview
            const btn = event.currentTarget;
            const originalHtml = btn.innerHTML;
            btn.innerHTML = `
                <svg class="animate-spin -ml-1 mr-2 h-4 w-4 text-[#1B1C1B]" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8H4z"></path>
                </svg>
                <span>Menyiapkan Rekap...</span>
            `;
            setTimeout(() => {
                btn.innerHTML = originalHtml;
                alert('Rekap Harian Berkat Dinasti (Jumat, 26 Sep 2025) berhasil diunduh dalam format PDF/Excel!');
            }, 800);
        }

        // Handle Quick Order Submission
        function handleQuickOrderSubmit(e) {
            e.preventDefault();
            const customer = document.getElementById('quickCustomerName').value;
            const product = document.getElementById('quickProduct').value;
            const deadline = document.getElementById('quickDeadline').value;
            const payment = document.getElementById('quickPayment').value;

            alert(`Pesanan baru berhasil ditambahkan untuk ${customer}!\nProduk: ${product}`);
            closeModal('quickOrderModal');
        }

        // Close modal on Escape key
        document.addEventListener('keydown', (e) => {
            if (e.key === 'Escape') {
                closeModal('orderDetailModal');
                closeModal('quickOrderModal');
            }
        });
    </script>
</body>
</html>
