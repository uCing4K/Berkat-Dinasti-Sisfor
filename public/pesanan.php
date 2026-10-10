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

// Data Pesanan Kanban & Kalender
$active_tab = $_GET['tab'] ?? 'kanban';

// Default Sample Data Sesuai Figma
$pending_orders = [
    [
        'id' => 'ORD-089',
        'customer' => 'Ibu Sari',
        'items' => 'Roti Tawar 20pcs, Donat 1 mika',
        'destination' => 'Dikirim ke Komplek Melati No. 4',
        'time' => 'Hari Ini, 15:00',
        'is_urgent' => true,
        'status_bayar' => 'Belum Bayar',
        'bayar_class' => 'bg-[#828282]/15 text-[#544337]'
    ],
    [
        'id' => 'ORD-088',
        'customer' => 'Pak Budi',
        'items' => 'Roti Sisir 2 kardus (24 pcs)',
        'destination' => 'Ambil Langsung Sore Hari',
        'time' => 'Besok, 09:00',
        'is_urgent' => false,
        'status_bayar' => 'Hutang',
        'bayar_class' => 'bg-[#E2B93B]/20 text-[#886C12]'
    ],
    [
        'id' => 'ORD-087',
        'customer' => 'Toko Maju',
        'items' => 'Kue Bolu 15pcs, Roti Sobek 5 box',
        'destination' => 'Kirim via Driver Roda Tiga',
        'time' => '28 Sep, 10:00',
        'is_urgent' => false,
        'status_bayar' => 'Lunas',
        'bayar_class' => 'bg-[#27AE60]/15 text-[#1B7A43]'
    ]
];

$selesai_orders = [
    [
        'id' => 'ORD-084',
        'customer' => 'Bu Rina',
        'items' => 'Donat Coklat 2 mika (12 pcs)',
        'courier_info' => 'Kurir: Siap Antar 14:00',
        'time' => '25 Sep',
        'status_bayar' => 'Lunas',
        'bayar_class' => 'bg-[#27AE60]/15 text-[#1B7A43]'
    ],
    [
        'id' => 'ORD-081',
        'customer' => 'Warung Pak Haji',
        'items' => 'Roti Tawar 50pcs (Kemasan Mika)',
        'courier_info' => 'Ambil di Toko',
        'time' => '25 Sep',
        'status_bayar' => 'Lunas',
        'bayar_class' => 'bg-[#27AE60]/15 text-[#1B7A43]'
    ]
];

$kitchen_notes = [
    [
        'type' => 'priority',
        'tag' => '! Prioritas #ORD-089',
        'tag_class' => 'text-[#EB5757]',
        'time' => '10:15',
        'text' => 'Roti tawar butuh dikirim jam 15:00. Dahulukan baking & packing mika terlebih dahulu.'
    ],
    [
        'type' => 'order',
        'tag' => '🕒 Catatan #ORD-088',
        'tag_class' => 'text-[#D97706]',
        'time' => '09:30',
        'text' => 'Pak Budi minta kardus roti sisir diikat tali rafia rapat saat diambil sore.'
    ],
    [
        'type' => 'stock',
        'tag' => '📋 Catatan Stok Bahan',
        'tag_class' => 'text-[#544337]',
        'time' => '08:00',
        'text' => 'Coklat mika sisa 4 bungkus untuk persiapan pesanan besok pagi.'
    ]
];
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pesanan - Berkat Dinasti</title>
    
    <!-- Tailwind CSS CDN -->
    <script src="https://cdn.tailwindcss.com"></script>
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
                    <button onclick="toggleSidebar()" class="lg:hidden p-1.5 text-[#544337] hover:bg-[#F5F3F1] rounded-lg">
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
                
                <!-- TOP BANNER: TITLE, STATS, & OMSET (MATCHING FIGMA) -->
                <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between gap-4 mb-6">
                    <div>
                        <div class="flex items-center gap-2.5">
                            <h2 class="text-[#1B1C1B] text-2xl font-bold tracking-tight">Manajemen Pesanan</h2>
                            <span class="bg-[#FF9B45]/15 text-[#924C00] text-xs font-semibold px-2.5 py-1 rounded-full border border-[#FF9B45]/20">
                                13 Total Pesanan
                            </span>
                        </div>
                        <p class="text-[#828282] text-sm mt-1" id="pageSubtitle">
                            Kelola status dan alur pengiriman pesanan roti secara real-time
                        </p>
                    </div>

                    <!-- Right Stats Indicator Pills -->
                    <div class="flex flex-wrap items-center gap-3">
                        <!-- Menunggu: 5 -->
                        <div class="bg-white border border-[#E0E0E0] rounded-xl px-3.5 py-2 flex items-center gap-2 text-xs font-medium shadow-2xs">
                            <span class="w-2 h-2 rounded-full bg-[#E2B93B]"></span>
                            <span class="text-[#828282]">Menunggu:</span>
                            <span class="font-bold text-[#1C1C1C]">5</span>
                        </div>

                        <!-- Selesai: 8 -->
                        <div class="bg-white border border-[#E0E0E0] rounded-xl px-3.5 py-2 flex items-center gap-2 text-xs font-medium shadow-2xs">
                            <span class="w-2 h-2 rounded-full bg-[#27AE60]"></span>
                            <span class="text-[#828282]">Selesai:</span>
                            <span class="font-bold text-[#1C1C1C]">8</span>
                        </div>

                        <!-- Omset Card -->
                        <div class="bg-[#6B3E1F] text-white rounded-xl px-4 py-2 flex items-center gap-2.5 text-xs shadow-xs">
                            <div class="w-6 h-6 rounded-md bg-white/20 flex items-center justify-center shrink-0">
                                <svg class="w-3.5 h-3.5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z"></path></svg>
                            </div>
                            <div>
                                <span class="text-white/80 text-[10px] block leading-none">Total Omset</span>
                                <span class="font-bold text-sm tracking-tight leading-tight">Rp 1.450.000</span>
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
                <!-- TAB CONTENT 1: KANBAN BOARD                                    -->
                <!-- ============================================================== -->
                <div id="kanbanView" class="<?= $active_tab === 'kalender' ? 'hidden' : '' ?>">
                    
                    <!-- Action Bar: Unduh Rekap & Tambah Pesanan -->
                    <div class="flex items-center justify-end gap-3 mb-6">
                        <button onclick="handleDownloadRecap()" class="bg-white hover:bg-[#F5F3F1] border border-[#E0E0E0] text-[#1B1C1B] text-sm font-medium px-4 py-2.5 rounded-lg flex items-center gap-2 transition-colors shadow-2xs">
                            <img src="assets/icons/download.svg" alt="Unduh" class="w-4 h-4">
                            <span>Unduh Rekap</span>
                        </button>

                        <button onclick="openOrderModal()" class="bg-[#FF9B45] hover:bg-[#E88C3D] text-white text-sm font-semibold px-4 py-2.5 rounded-lg flex items-center gap-2 transition-all shadow-button-orange active:scale-95">
                            <img src="assets/icons/plus.svg" alt="Tambah" class="w-4 h-4">
                            <span>+ Tambah Pesanan</span>
                        </button>
                    </div>

                    <!-- 3-Column Kanban Grid (Pending, Selesai, Catatan Dapur) -->
                    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                        
                        <!-- COLUMN 1: PENDING (5) -->
                        <div class="bg-[#F9F7F5] border border-[#F0ECE9] rounded-2xl p-4 flex flex-col justify-between min-h-[560px]">
                            <div>
                                <!-- Header Kolom -->
                                <div class="flex items-center justify-between mb-1">
                                    <div class="flex items-center gap-2">
                                        <span class="bg-[#E2B93B]/20 text-[#886C12] text-xs font-bold px-2.5 py-0.5 rounded-full">Pending</span>
                                        <span class="w-5 h-5 rounded-full bg-[#E2B93B] text-white text-[11px] font-bold flex items-center justify-center">5</span>
                                    </div>
                                </div>
                                <p class="text-[#828282] text-xs mb-4">Perlu konfirmasi & proses oven</p>

                                <!-- List Kartu Pesanan Pending -->
                                <div class="space-y-3.5">
                                    <?php foreach ($pending_orders as $ord): ?>
                                        <div class="bg-white rounded-xl p-4 border border-[#F0ECE9] border-l-4 border-l-[#E2B93B] shadow-card hover:shadow-md transition-shadow relative cursor-grab">
                                            <!-- Top Row -->
                                            <div class="flex items-start justify-between">
                                                <div>
                                                    <h4 class="text-[#1C1C1C] font-bold text-sm leading-tight"><?= htmlspecialchars($ord['customer']) ?></h4>
                                                    <span class="text-[#828282] text-xs font-mono"><?= htmlspecialchars($ord['id']) ?></span>
                                                </div>
                                                <!-- Drag Handle Icon -->
                                                <span class="text-[#BDBDBD] hover:text-[#828282] cursor-grab">
                                                    <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20"><path d="M7 2a2 2 0 100 4 2 2 0 000-4zm6 0a2 2 0 100 4 2 2 0 000-4zm-6 6a2 2 0 100 4 2 2 0 000-4zm6 0a2 2 0 100 4 2 2 0 000-4zm-6 6a2 2 0 100 4 2 2 0 000-4zm6 0a2 2 0 100 4 2 2 0 000-4z"></path></svg>
                                                </span>
                                            </div>

                                            <!-- Items -->
                                            <div class="flex items-center gap-1.5 text-xs text-[#544337] font-medium mt-2.5">
                                                <span>🛍️</span>
                                                <span class="truncate"><?= htmlspecialchars($ord['items']) ?></span>
                                            </div>

                                            <!-- Destination -->
                                            <div class="flex items-center gap-1.5 text-xs text-[#828282] mt-1">
                                                <span>🛵</span>
                                                <span class="truncate"><?= htmlspecialchars($ord['destination']) ?></span>
                                            </div>

                                            <!-- Bottom Info -->
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
                                    <?php endforeach; ?>
                                </div>
                            </div>

                            <!-- Bottom Tambah Kartu Button -->
                            <button onclick="openOrderModal()" class="w-full py-2.5 mt-4 border border-dashed border-[#D8D2CB] hover:border-[#FF9B45] text-[#828282] hover:text-[#FF9B45] text-xs font-semibold rounded-xl flex items-center justify-center gap-1.5 transition-colors">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path></svg>
                                <span>+ Tambah Kartu</span>
                            </button>
                        </div>

                        <!-- COLUMN 2: SELESAI (8) -->
                        <div class="bg-[#F9F7F5] border border-[#F0ECE9] rounded-2xl p-4 flex flex-col justify-between min-h-[560px]">
                            <div>
                                <!-- Header Kolom -->
                                <div class="flex items-center justify-between mb-1">
                                    <div class="flex items-center gap-2">
                                        <span class="bg-[#27AE60]/20 text-[#1B7A43] text-xs font-bold px-2.5 py-0.5 rounded-full">Selesai</span>
                                        <span class="w-5 h-5 rounded-full bg-[#27AE60] text-white text-[11px] font-bold flex items-center justify-center">8</span>
                                    </div>
                                </div>
                                <p class="text-[#828282] text-xs mb-4">Sudah dipacking / siap diambil</p>

                                <!-- List Kartu Pesanan Selesai -->
                                <div class="space-y-3.5">
                                    <?php foreach ($selesai_orders as $ord): ?>
                                        <div class="bg-white rounded-xl p-4 border border-[#F0ECE9] border-l-4 border-l-[#27AE60] shadow-card hover:shadow-md transition-shadow relative cursor-grab">
                                            <!-- Top Row -->
                                            <div class="flex items-start justify-between">
                                                <div>
                                                    <h4 class="text-[#1C1C1C] font-bold text-sm leading-tight"><?= htmlspecialchars($ord['customer']) ?></h4>
                                                    <span class="text-[#828282] text-xs font-mono"><?= htmlspecialchars($ord['id']) ?></span>
                                                </div>
                                                <span class="text-[#BDBDBD] hover:text-[#828282] cursor-grab">
                                                    <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20"><path d="M7 2a2 2 0 100 4 2 2 0 000-4zm6 0a2 2 0 100 4 2 2 0 000-4zm-6 6a2 2 0 100 4 2 2 0 000-4zm6 0a2 2 0 100 4 2 2 0 000-4zm-6 6a2 2 0 100 4 2 2 0 000-4zm6 0a2 2 0 100 4 2 2 0 000-4z"></path></svg>
                                                </span>
                                            </div>

                                            <!-- Items -->
                                            <div class="flex items-center gap-1.5 text-xs text-[#544337] font-medium mt-2.5">
                                                <span>🛍️</span>
                                                <span class="truncate"><?= htmlspecialchars($ord['items']) ?></span>
                                            </div>

                                            <!-- Courier / Ambil Info -->
                                            <div class="inline-flex items-center gap-1.5 text-xs font-medium text-[#544337] bg-[#F5F3F1] px-2.5 py-1 rounded-md mt-2 border border-[#EBE8E5]">
                                                <span>🛵</span>
                                                <span><?= htmlspecialchars($ord['courier_info']) ?></span>
                                            </div>

                                            <!-- Bottom Info -->
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

                        <!-- COLUMN 3: CATATAN DAPUR -->
                        <div class="bg-white border border-[#F0ECE9] rounded-2xl p-5 shadow-card flex flex-col justify-between min-h-[560px]">
                            <div>
                                <!-- Header Kolom -->
                                <div class="flex items-center justify-between pb-3 border-b border-[#F0ECE9] mb-4">
                                    <div class="flex items-center gap-2">
                                        <span class="text-base">📝</span>
                                        <h3 class="text-[#1C1C1C] font-bold text-sm">Catatan Dapur</h3>
                                    </div>
                                    <span class="bg-[#FF9B45]/15 text-[#924C00] text-[11px] font-bold px-2 py-0.5 rounded-full">
                                        3 Catatan
                                    </span>
                                </div>

                                <!-- List Catatan Dapur -->
                                <div class="space-y-3.5">
                                    <?php foreach ($kitchen_notes as $note): ?>
                                        <div class="bg-[#FBF9F7] rounded-xl p-3.5 border border-[#F0ECE9]">
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

                            <!-- Tambah Catatan Button -->
                            <button onclick="alert('Fitur Tambah Catatan Dapur: Membuka dialog memo koki dapur...')" 
                                class="w-full py-2.5 mt-4 border border-[#FF9B45] text-[#FF9B45] hover:bg-[#FF9B45]/10 text-xs font-semibold rounded-xl flex items-center justify-center gap-1.5 transition-colors">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path></svg>
                                <span>+ Tambah Catatan</span>
                            </button>
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
                            <!-- Dropdown Bulan -->
                            <div class="relative">
                                <select class="appearance-none bg-white border border-[#E0E0E0] rounded-lg pl-3.5 pr-9 py-2 text-sm text-[#1C1C1C] font-semibold focus:outline-none focus:border-[#FF9B45] cursor-pointer shadow-2xs">
                                    <option>September 2025</option>
                                    <option>Oktober 2025</option>
                                    <option>November 2025</option>
                                </select>
                                <div class="absolute inset-y-0 right-0 pr-3 flex items-center pointer-events-none text-[#828282]">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path></svg>
                                </div>
                            </div>

                            <!-- Dropdown Tipe Pengiriman -->
                            <div class="relative">
                                <select class="appearance-none bg-white border border-[#E0E0E0] rounded-lg pl-3.5 pr-9 py-2 text-sm text-[#1C1C1C] font-medium focus:outline-none focus:border-[#FF9B45] cursor-pointer shadow-2xs">
                                    <option>Semua Pengiriman</option>
                                    <option>Kurir Toko</option>
                                    <option>Ambil di Toko</option>
                                </select>
                                <div class="absolute inset-y-0 right-0 pr-3 flex items-center pointer-events-none text-[#828282]">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path></svg>
                                </div>
                            </div>

                            <!-- Badge 3 Hari Pengiriman Aktif -->
                            <span class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-full text-xs font-semibold bg-[#FEF6EE] text-[#D97706] border border-[#FDE6D2]">
                                <span class="w-2 h-2 rounded-full bg-[#FF9B45]"></span>
                                3 Hari Pengiriman Aktif
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
                                <h3 class="text-[#1C1C1C] font-bold text-lg">September 2025</h3>
                                <div class="flex items-center gap-1">
                                    <button class="p-1 hover:bg-[#F5F3F1] rounded text-[#828282] transition-colors" title="Bulan Sebelumnya">
                                        <img src="assets/icons/chevron_left.svg" alt="Prev" class="w-4 h-4">
                                    </button>
                                    <button class="p-1 hover:bg-[#F5F3F1] rounded text-[#828282] transition-colors" title="Bulan Berikutnya">
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
                                    <span class="w-2.5 h-2.5 rounded-full bg-[#EB5757]"></span>
                                    <span class="text-[#544337] font-medium">Overdue</span>
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

                        <!-- Calendar Dates Grid -->
                        <div class="grid grid-cols-7 gap-2 pt-3 text-center">
                            <!-- Week 1: 1 - 7 -->
                            <div class="p-3 text-sm font-medium text-[#1C1C1C] rounded-xl hover:bg-[#FFF8F2] transition-colors">1</div>
                            <div class="p-3 text-sm font-medium text-[#1C1C1C] rounded-xl hover:bg-[#FFF8F2] transition-colors">2</div>
                            <div class="p-3 text-sm font-medium text-[#1C1C1C] rounded-xl hover:bg-[#FFF8F2] transition-colors">3</div>
                            <div class="p-3 text-sm font-medium text-[#1C1C1C] rounded-xl hover:bg-[#FFF8F2] transition-colors">4</div>
                            <div class="p-3 text-sm font-medium text-[#1C1C1C] rounded-xl hover:bg-[#FFF8F2] transition-colors">5</div>
                            <div class="p-3 text-sm font-medium text-[#1C1C1C] rounded-xl hover:bg-[#FFF8F2] transition-colors">6</div>
                            <div class="p-3 text-sm font-medium text-[#1C1C1C] rounded-xl hover:bg-[#FFF8F2] transition-colors">7</div>

                            <!-- Week 2: 8 - 14 -->
                            <div class="p-3 text-sm font-medium text-[#1C1C1C] rounded-xl hover:bg-[#FFF8F2] transition-colors">8</div>
                            <div class="p-3 text-sm font-medium text-[#1C1C1C] rounded-xl hover:bg-[#FFF8F2] transition-colors">9</div>
                            <div class="p-3 text-sm font-medium text-[#1C1C1C] rounded-xl hover:bg-[#FFF8F2] transition-colors">10</div>
                            <div class="p-3 text-sm font-medium text-[#1C1C1C] rounded-xl hover:bg-[#FFF8F2] transition-colors">11</div>
                            <div class="p-3 text-sm font-medium text-[#1C1C1C] rounded-xl hover:bg-[#FFF8F2] transition-colors">12</div>
                            <div class="p-3 text-sm font-medium text-[#1C1C1C] rounded-xl hover:bg-[#FFF8F2] transition-colors">13</div>
                            <div class="p-3 text-sm font-medium text-[#1C1C1C] rounded-xl hover:bg-[#FFF8F2] transition-colors">14</div>

                            <!-- Week 3: 15 - 21 -->
                            <div class="p-3 text-sm font-medium text-[#1C1C1C] rounded-xl hover:bg-[#FFF8F2] transition-colors">15</div>
                            <div class="p-3 text-sm font-medium text-[#1C1C1C] rounded-xl hover:bg-[#FFF8F2] transition-colors">16</div>
                            <div class="p-3 text-sm font-medium text-[#1C1C1C] rounded-xl hover:bg-[#FFF8F2] transition-colors">17</div>
                            <div class="p-3 text-sm font-medium text-[#1C1C1C] rounded-xl hover:bg-[#FFF8F2] transition-colors">18</div>
                            <div class="p-3 text-sm font-medium text-[#1C1C1C] rounded-xl hover:bg-[#FFF8F2] transition-colors">19</div>
                            <div class="p-3 text-sm font-medium text-[#1C1C1C] rounded-xl hover:bg-[#FFF8F2] transition-colors">20</div>
                            <div class="p-3 text-sm font-medium text-[#1C1C1C] rounded-xl hover:bg-[#FFF8F2] transition-colors">21</div>

                            <!-- Week 4: 22 - 28 -->
                            <div class="p-3 text-sm font-medium text-[#1C1C1C] rounded-xl hover:bg-[#FFF8F2] transition-colors">22</div>
                            <div class="p-3 text-sm font-medium text-[#1C1C1C] rounded-xl hover:bg-[#FFF8F2] transition-colors">23</div>
                            <div class="p-3 text-sm font-medium text-[#1C1C1C] rounded-xl hover:bg-[#FFF8F2] transition-colors">24</div>
                            
                            <!-- 25 Sep: 3 Selesai -->
                            <div class="p-2.5 rounded-xl bg-[#F5F3F1] border border-[#EBE8E5] flex flex-col items-center justify-between min-h-[64px]">
                                <span class="font-bold text-sm text-[#1C1C1C]">25</span>
                                <span class="text-[10px] font-semibold text-[#1B7A43] bg-[#27AE60]/15 px-1.5 py-0.5 rounded-md">✓ 3 Selesai</span>
                            </div>

                            <!-- 26 Sep: TODAY ACTIVE -->
                            <div class="p-2.5 rounded-xl bg-[#FFF8F2] border-2 border-[#FF9B45] flex flex-col items-center justify-between min-h-[64px] shadow-sm">
                                <span class="font-bold text-sm text-[#924C00]">26</span>
                                <span class="text-[10px] font-bold text-white bg-[#FF9B45] px-2 py-0.5 rounded-md shadow-2xs">2 Pesanan</span>
                            </div>

                            <!-- 27 Sep: 1 Pesanan -->
                            <div class="p-2.5 rounded-xl bg-[#FEF6EE] border border-[#FDE6D2] flex flex-col items-center justify-between min-h-[64px]">
                                <span class="font-bold text-sm text-[#D97706]">27</span>
                                <span class="text-[10px] font-medium text-[#D97706] bg-white px-1.5 py-0.5 rounded-md">1 Pesanan</span>
                            </div>

                            <div class="p-3 text-sm font-medium text-[#1C1C1C] rounded-xl hover:bg-[#FFF8F2] transition-colors">28</div>

                            <!-- Week 5: 29, 30 -->
                            <!-- 29 Sep: 1 Pesanan -->
                            <div class="p-2.5 rounded-xl bg-[#FEF6EE] border border-[#FDE6D2] flex flex-col items-center justify-between min-h-[64px]">
                                <span class="font-bold text-sm text-[#D97706]">29</span>
                                <span class="text-[10px] font-medium text-[#D97706] bg-white px-1.5 py-0.5 rounded-md">1 Pesanan</span>
                            </div>

                            <div class="p-3 text-sm font-medium text-[#1C1C1C] rounded-xl hover:bg-[#FFF8F2] transition-colors">30</div>
                            <div class="p-3 text-sm text-[#BDBDBD] pointer-events-none">1</div>
                            <div class="p-3 text-sm text-[#BDBDBD] pointer-events-none">2</div>
                            <div class="p-3 text-sm text-[#BDBDBD] pointer-events-none">3</div>
                            <div class="p-3 text-sm text-[#BDBDBD] pointer-events-none">4</div>
                            <div class="p-3 text-sm text-[#BDBDBD] pointer-events-none">5</div>
                        </div>

                        <!-- Calendar Footer Note -->
                        <div class="pt-4 mt-4 border-t border-[#F0ECE9] flex items-center gap-2 text-xs text-[#828282]">
                            <svg class="w-4 h-4 text-[#D97706]" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                            <span>Klik tanggal untuk melihat daftar pengiriman roti dan status kurir.</span>
                        </div>
                    </div>

                    <!-- Bottom Delivery Schedule (26 Sep 2025) -->
                    <div class="bg-white rounded-2xl border border-[#F0ECE9] p-6 shadow-card">
                        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-2 pb-4 border-b border-[#F0ECE9] mb-5">
                            <div class="flex items-center gap-2.5">
                                <h3 class="text-[#1C1C1C] font-bold text-base">Jadwal Pengiriman: 26 Sep 2025</h3>
                                <span class="bg-[#27AE60]/15 text-[#1B7A43] text-xs font-semibold px-2.5 py-0.5 rounded-full">
                                    2 Batch Siap
                                </span>
                            </div>
                            <span class="text-xs text-[#828282]">2 Pengiriman Terjadwal Hari Ini</span>
                        </div>

                        <!-- 3 Cards Grid -->
                        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-5">
                            
                            <!-- Card 1: Ibu Sari -->
                            <div class="bg-[#FBF9F7] rounded-xl p-4 border border-[#F0ECE9] flex flex-col justify-between">
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
                                    <a href="#" class="text-[#924C00] hover:text-[#FF9B45] font-semibold flex items-center gap-1">
                                        <span>Detail</span>
                                        <span>→</span>
                                    </a>
                                </div>
                            </div>

                            <!-- Card 2: Bu Rina -->
                            <div class="bg-[#FBF9F7] rounded-xl p-4 border border-[#F0ECE9] flex flex-col justify-between">
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
                                    <a href="#" class="text-[#924C00] hover:text-[#FF9B45] font-semibold flex items-center gap-1">
                                        <span>Detail</span>
                                        <span>→</span>
                                    </a>
                                </div>
                            </div>

                            <!-- Card 3: Action Panel -->
                            <div class="bg-[#FBF9F7] rounded-xl p-4 border border-[#F0ECE9] flex flex-col justify-between">
                                <div>
                                    <h4 class="text-[#1C1C1C] font-bold text-sm mb-1">Aksi Cepat Pengiriman</h4>
                                    <p class="text-xs text-[#828282] leading-relaxed">Perbarui status semua pesanan hari ini atau cetak dokumen pengiriman untuk kurir.</p>
                                </div>
                                <div class="space-y-2 mt-4">
                                    <button onclick="alert('Semua pesanan berhasil dikonfirmasi Siap Kirim!')" 
                                        class="w-full bg-[#FF9B45] hover:bg-[#E88C3D] text-white font-semibold text-xs py-2.5 rounded-lg flex items-center justify-center gap-1.5 shadow-xs transition-colors">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
                                        <span>Konfirmasi Semua Siap Kirim</span>
                                    </button>
                                    <button onclick="alert('Mencetak rekap surat jalan kurir...')" 
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
    <!-- MODAL POPOUT: TAMBAH PESANAN BARU (SESUAI FIGMA SCREENSHOT 3)  -->
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
            <form id="orderForm" onsubmit="handleSaveOrder(event)" class="space-y-6">
                
                <!-- SECTION 1: DATA PELANGGAN -->
                <div>
                    <h4 class="text-xs font-bold text-[#1C1C1C] uppercase tracking-wider mb-2.5">Data Pelanggan</h4>
                    
                    <!-- Search Input Pelanggan -->
                    <div class="relative mb-2">
                        <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-[#828282]">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path></svg>
                        </div>
                        <input type="text" id="custSearch" value="Ibu Sari (Komplek Melati No. 4 - 08123456789)" 
                            class="w-full bg-white border border-[#E0E0E0] rounded-lg pl-10 pr-4 py-2.5 text-sm text-[#1C1C1C] focus:border-[#FF9B45] focus:ring-1 focus:ring-[#FF9B45] focus:outline-none transition-colors">
                    </div>

                    <div class="flex items-center justify-between text-xs mb-3">
                        <span class="text-[#828282]">Pelanggan terdaftar</span>
                        <a href="#" onclick="alert('Buka dialog tambah pelanggan baru')" class="text-[#FF9B45] hover:underline font-semibold">+ Tambah Pelanggan Baru</a>
                    </div>

                    <!-- Detail Pelanggan Box (3 Columns) -->
                    <div class="bg-[#F9F7F5] border border-[#F0ECE9] rounded-xl p-3.5 grid grid-cols-1 sm:grid-cols-3 gap-3 text-xs">
                        <div>
                            <p class="text-[#828282] uppercase text-[10px] font-semibold">Nama Pelanggan</p>
                            <p class="font-bold text-[#1C1C1C] mt-0.5">Ibu Sari</p>
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
                        <h4 class="text-xs font-bold text-[#1C1C1C] uppercase tracking-wider">Detail Pesanan</h4>
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
                                    <th class="px-3 py-2.5 text-center w-10">Aksi</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-[#F0ECE9] bg-white">
                                <!-- Row 1: Roti Tawar (Initial qty 4 triggers MOQ alert!) -->
                                <tr id="orderRow1">
                                    <td class="px-3.5 py-3">
                                        <p class="font-bold text-[#1C1C1C]">Roti Tawar Kupas Special</p>
                                        <p class="text-[10px] text-[#EB5757] font-semibold">Min. 5 pcs</p>
                                    </td>
                                    <td class="px-3.5 py-3 text-[#544337]">Mika (10 pcs)</td>
                                    <td class="px-3.5 py-3">
                                        <input type="number" id="qtyItem1" value="4" min="1" onchange="calculateOrderTotal()" 
                                            class="w-16 px-2 py-1 border-2 border-[#EB5757] text-[#EB5757] font-bold rounded text-center focus:outline-none transition-colors">
                                    </td>
                                    <td class="px-3.5 py-3 text-[#544337]">Rp 15.000</td>
                                    <td class="px-3.5 py-3 font-bold text-[#1C1C1C]" id="subtotal1">Rp 60.000</td>
                                    <td class="px-3 py-3 text-center">
                                        <button type="button" onclick="alert('Hapus item')" class="text-[#828282] hover:text-[#EB5757]">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg>
                                        </button>
                                    </td>
                                </tr>

                                <!-- Row 2: Donat Kentang Coklat -->
                                <tr id="orderRow2">
                                    <td class="px-3.5 py-3">
                                        <p class="font-bold text-[#1C1C1C]">Donat Kentang Coklat Meises</p>
                                        <p class="text-[10px] text-[#828282]">Ready Batch Pagi</p>
                                    </td>
                                    <td class="px-3.5 py-3 text-[#544337]">Mika (6 pcs)</td>
                                    <td class="px-3.5 py-3">
                                        <input type="number" id="qtyItem2" value="2" min="1" onchange="calculateOrderTotal()" 
                                            class="w-16 px-2 py-1 border border-[#E0E0E0] text-[#1C1C1C] font-semibold rounded text-center focus:outline-none focus:border-[#FF9B45] transition-colors">
                                    </td>
                                    <td class="px-3.5 py-3 text-[#544337]">Rp 25.000</td>
                                    <td class="px-3.5 py-3 font-bold text-[#1C1C1C]" id="subtotal2">Rp 50.000</td>
                                    <td class="px-3 py-3 text-center">
                                        <button type="button" onclick="alert('Hapus item')" class="text-[#828282] hover:text-[#EB5757]">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg>
                                        </button>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>

                    <!-- Tambah Produk Button -->
                    <button type="button" onclick="alert('Tambah baris produk baru ke pesanan')" 
                        class="w-full py-2.5 border border-dashed border-[#FF9B45] text-[#FF9B45] hover:bg-[#FF9B45]/10 text-xs font-semibold rounded-xl flex items-center justify-center gap-1.5 transition-colors mb-3">
                        <span>+ + Tambah Produk</span>
                    </button>

                    <!-- Validation Callout Alert (MOQ Warning) -->
                    <div id="moqAlertBox" class="bg-[#FFF5F5] border border-[#EB5757]/30 rounded-xl p-3 flex items-start gap-2.5">
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
                                <label class="block text-xs font-semibold text-[#333333] mb-1">Tanggal Pengiriman</label>
                                <div class="relative">
                                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-[#828282]">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path></svg>
                                    </div>
                                    <input type="text" value="Sabtu, 27 Sep 2025" 
                                        class="w-full bg-white border border-[#E0E0E0] rounded-lg pl-9 pr-3.5 py-2 text-xs text-[#1C1C1C] focus:border-[#FF9B45] focus:outline-none">
                                </div>
                            </div>

                            <div>
                                <label class="block text-xs font-semibold text-[#333333] mb-1">Waktu / Jam Pengiriman</label>
                                <div class="relative">
                                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-[#828282]">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                                    </div>
                                    <input type="text" value="14:00 WIB" 
                                        class="w-full bg-white border border-[#E0E0E0] rounded-lg pl-9 pr-3.5 py-2 text-xs text-[#1C1C1C] focus:border-[#FF9B45] focus:outline-none">
                                </div>
                            </div>

                            <div>
                                <label class="block text-xs font-semibold text-[#333333] mb-1">Catatan Dapur / Driver</label>
                                <textarea rows="2" placeholder="Catatan khusus: roti tawar diiris tipis, antar sebelum jam 3 sore..." 
                                    class="w-full bg-white border border-[#E0E0E0] rounded-lg p-3 text-xs text-[#1C1C1C] focus:border-[#FF9B45] focus:outline-none">Catatan khusus: roti tawar diiris tipis, antar sebelum jam 3 sore...</textarea>
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
                                    <input type="hidden" id="selectedPaymentStatus" value="Belum Bayar">
                                </div>
                            </div>

                            <div class="mt-4">
                                <label class="block text-xs font-semibold text-[#333333] mb-1">Catatan Pembayaran</label>
                                <input type="text" placeholder="Contoh: Bayar tunai saat kurir tiba" 
                                    class="w-full bg-white border border-[#E0E0E0] rounded-lg px-3 py-2 text-xs text-[#1C1C1C] focus:border-[#FF9B45] focus:outline-none">
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
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"></path>
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

        // Modal Open / Close
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

        // ESC Close
        document.addEventListener('keydown', function(e) {
            if (e.key === 'Escape') {
                closeOrderModal();
            }
        });

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

            const qtyInput1 = document.getElementById('qtyItem1');
            const alertBox = document.getElementById('moqAlertBox');
            const submitBtn = document.getElementById('submitOrderBtn');
            const lockIcon = document.getElementById('submitLockIcon');
            const footerWarning = document.getElementById('footerMoqWarning');
            const currentMoqQty = document.getElementById('currentMoqQty');

            currentMoqQty.textContent = q1;

            // MOQ untuk Roti Tawar adalah 5 pcs
            if (q1 < 5) {
                // Warning state
                qtyInput1.className = 'w-16 px-2 py-1 border-2 border-[#EB5757] text-[#EB5757] font-bold rounded text-center focus:outline-none transition-colors';
                alertBox.classList.remove('hidden');
                submitBtn.disabled = true;
                submitBtn.className = 'bg-[#D8D2CB] text-white cursor-not-allowed text-xs font-semibold px-5 py-2.5 rounded-lg flex items-center gap-1.5 transition-all shadow-xs';
                lockIcon.textContent = '🔒';
                footerWarning.classList.remove('hidden');
            } else {
                // Valid state!
                qtyInput1.className = 'w-16 px-2 py-1 border border-[#27AE60] text-[#1C1C1C] font-bold rounded text-center focus:outline-none transition-colors';
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
            const buttons = [document.getElementById('payBtn1'), document.getElementById('payBtn2'), document.getElementById('payBtn3')];
            buttons.forEach(b => {
                b.className = 'py-1.5 px-2 rounded-lg text-xs font-semibold bg-white border border-[#E0E0E0] text-[#544337] hover:bg-gray-50 transition-colors';
            });
            btn.className = 'py-1.5 px-2 rounded-lg text-xs font-semibold bg-[#544337] text-white shadow-2xs transition-colors';
        }

        // Save Order Handler
        function handleSaveOrder(e) {
            e.preventDefault();
            alert('Pesanan baru berhasil disimpan ke sistem dan otomatis disinkronkan ke daftar antrean oven & jadwal pengiriman!');
            closeOrderModal();
        }

        // Download Recap handler
        function handleDownloadRecap() {
            alert('Mengunduh dokumen rekap pesanan harian (PDF/Excel)...');
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
                closeOrderModal();
            }
        });
    </script>
</body>
</html>
