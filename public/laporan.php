<?php
session_start();

// Proteksi Autentikasi: Hanya user yang sudah login yang diizinkan mengakses halaman Laporan
if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true) {
    header("Location: login.php");
    exit();
}

// Coba koneksi database jika config tersedia
$configFile = __DIR__ . "/../config/config.php";
if (file_exists($configFile)) {
    require_once $configFile;
}

// Dataset Riwayat Pesanan Laporan Sesuai Figma
$orders_report = [
    [
        'no' => 1,
        'tanggal' => '26 Sep 2025',
        'jam' => '14:00 WIB',
        'order_id' => '#ORD-089',
        'customer' => 'Ibu Sari Dewi',
        'items' => 'Roti Tawar Kupas Special (20 pcs), Donat Mika (1)',
        'total' => 'Rp 110.000',
        'total_raw' => 110000,
        'status_bayar' => 'Belum Bayar',
        'status_bayar_class' => 'bg-[#828282]/15 text-[#544337]'
    ],
    [
        'no' => 2,
        'tanggal' => '26 Sep 2025',
        'jam' => '10:15 WIB',
        'order_id' => '#ORD-088',
        'customer' => 'Pak Budi Santoso',
        'items' => 'Roti Sisir Butter (2 kardus / 24 pcs)',
        'total' => 'Rp 175.000',
        'total_raw' => 175000,
        'status_bayar' => 'Hutang',
        'status_bayar_label' => 'Hutang / Piutang',
        'status_bayar_class' => 'bg-[#E2B93B]/20 text-[#886C12]'
    ],
    [
        'no' => 3,
        'tanggal' => '25 Sep 2025',
        'jam' => '16:30 WIB',
        'order_id' => '#ORD-084',
        'customer' => 'Bu Rina',
        'items' => 'Donat Coklat Meises (2 mika / 12 pcs)',
        'total' => 'Rp 50.000',
        'total_raw' => 50000,
        'status_bayar' => 'Lunas',
        'status_bayar_class' => 'bg-[#27AE60]/15 text-[#1B7A43]'
    ],
    [
        'no' => 4,
        'tanggal' => '25 Sep 2025',
        'jam' => '11:00 WIB',
        'order_id' => '#ORD-081',
        'customer' => 'Warung Pak Haji',
        'items' => 'Roti Tawar 50 pcs (Kemasan Mika)',
        'total' => 'Rp 325.000',
        'total_raw' => 325000,
        'status_bayar' => 'Lunas',
        'status_bayar_class' => 'bg-[#27AE60]/15 text-[#1B7A43]'
    ],
    [
        'no' => 5,
        'tanggal' => '24 Sep 2025',
        'jam' => '08:20 WIB',
        'order_id' => '#ORD-079',
        'customer' => 'Toko Maju Jaya',
        'items' => 'Kue Bolu Gulung (15 pcs), Roti Sobek Keju (5)',
        'total' => 'Rp 450.000',
        'total_raw' => 450000,
        'status_bayar' => 'Lunas',
        'status_bayar_class' => 'bg-[#27AE60]/15 text-[#1B7A43]'
    ]
];
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Laporan - Berkat Dinasti</title>
    
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

        @media print {
            aside, header, #filterBar, #reportActions, .no-print {
                display: none !important;
            }
            main {
                padding: 0 !important;
                margin: 0 !important;
                width: 100% !important;
            }
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

                    <!-- Laporan (Active) -->
                    <a href="laporan.php" class="bg-[#FF9B45] text-white rounded-lg px-4 py-3 flex items-center gap-3 font-medium text-sm transition-all shadow-sm">
                        <img src="assets/icons/nav_laporan.svg" alt="Laporan" class="w-5 h-5">
                        <span>Laporan</span>
                    </a>

                    <!-- Pengaturan -->
                    <a href="#" class="text-white/80 hover:text-white hover:bg-white/10 rounded-lg px-4 py-3 flex items-center gap-3 font-medium text-sm transition-colors">
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
                            <p class="text-[#828282] text-xs leading-tight mt-0.5"><?= htmlspecialchars(ucfirst($_SESSION['role'] ?? 'Pemilik')) ?></p>
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
                            <a href="beranda.php" class="text-[#544337] hover:underline">Beranda</a>
                            <span class="text-[#DBC2B2]">/</span>
                            <span class="text-[#924C00] font-medium">Laporan</span>
                        </div>
                        <!-- Section Heading -->
                        <h1 class="text-[#1B1C1B] font-bold text-base md:text-lg leading-tight mt-0.5">Laporan & Keuangan</h1>
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
                
                <!-- TOP FILTER & ACTION BAR (MATCHING FIGMA SCREENSHOT) -->
                <div id="filterBar" class="flex flex-col lg:flex-row lg:items-center lg:justify-between gap-4 mb-6">
                    
                    <!-- Left: Periode Filter Controls -->
                    <div class="flex flex-wrap items-center gap-3">
                        <div class="flex items-center gap-2 text-xs md:text-sm font-semibold text-[#1C1C1C]">
                            <svg class="w-4 h-4 text-[#D97706]" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 7.293A1 1 0 013 6.586V4z"></path></svg>
                            <span>Periode:</span>
                        </div>

                        <!-- Dropdown Periode -->
                        <div class="relative">
                            <select id="periodeSelect" class="appearance-none bg-white border border-[#E0E0E0] rounded-xl pl-3.5 pr-8 py-2 text-xs md:text-sm text-[#1C1C1C] font-semibold focus:border-[#FF9B45] focus:outline-none cursor-pointer shadow-2xs">
                                <option value="sep2025">Bulan ini (Sep 2025)</option>
                                <option value="agu2025">Bulan lalu (Agu 2025)</option>
                                <option value="q3">Kuartal 3 (Jul - Sep)</option>
                                <option value="ytd">Tahun 2025 (YTD)</option>
                            </select>
                            <div class="absolute inset-y-0 right-0 pr-2.5 flex items-center pointer-events-none text-[#828282]">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path></svg>
                            </div>
                        </div>

                        <!-- Date Range Display Box -->
                        <div class="bg-white border border-[#E0E0E0] rounded-xl px-3.5 py-2 text-xs md:text-sm text-[#544337] font-medium flex items-center gap-2 shadow-2xs">
                            <img src="assets/icons/header_calendar.svg" alt="Kalender" class="w-4 h-4 opacity-70">
                            <span>01/09/2025 — 26/09/2025</span>
                        </div>

                        <!-- Button Terapkan Filter -->
                        <button onclick="applyPeriodFilter()" class="bg-[#FF9B45] hover:bg-[#E88C3D] text-white text-xs md:text-sm font-semibold px-4 py-2 rounded-xl flex items-center gap-1.5 transition-all shadow-button-orange active:scale-95">
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
                            <span>Terapkan Filter</span>
                        </button>
                    </div>

                    <!-- Right: Unduh Excel & Cetak Laporan -->
                    <div id="reportActions" class="flex items-center gap-3">
                        <button onclick="handleExportExcel()" class="bg-white hover:bg-[#F5F3F1] border border-[#E0E0E0] text-[#1B1C1B] text-xs md:text-sm font-medium px-3.5 py-2 rounded-xl flex items-center gap-2 transition-colors shadow-2xs">
                            <img src="assets/icons/download.svg" alt="Unduh" class="w-4 h-4">
                            <span>Unduh Excel</span>
                        </button>

                        <button onclick="window.print()" class="bg-white hover:bg-[#F5F3F1] border border-[#E0E0E0] text-[#1B1C1B] text-xs md:text-sm font-medium px-3.5 py-2 rounded-xl flex items-center gap-2 transition-colors shadow-2xs">
                            <svg class="w-4 h-4 text-[#544337]" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"></path></svg>
                            <span>Cetak Laporan</span>
                        </button>
                    </div>

                </div>

                <!-- ROW 1: 4 KPI CARDS (MATCHING FIGMA) -->
                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-5 mb-6">
                    
                    <!-- KPI 1: Total Pemasukan -->
                    <div class="bg-white rounded-2xl p-5 border border-[#F0ECE9] border-l-4 border-l-[#27AE60] shadow-card flex flex-col justify-between">
                        <div>
                            <div class="flex items-center justify-between mb-2">
                                <span class="text-xs text-[#828282] font-semibold">Total Pemasukan</span>
                                <div class="w-8 h-8 rounded-lg bg-[#27AE60]/15 flex items-center justify-center text-[#1B7A43]">
                                    <img src="assets/icons/wallet.svg" alt="Pemasukan" class="w-4 h-4">
                                </div>
                            </div>
                            <h3 class="text-2xl font-bold text-[#1C1C1C] tracking-tight">Rp 4.250.000</h3>
                        </div>
                        <div class="mt-4">
                            <span class="inline-flex items-center gap-1 text-[11px] font-semibold px-2 py-0.5 rounded-full bg-[#27AE60]/15 text-[#1B7A43]">
                                <span>+12% vs bln lalu</span>
                            </span>
                        </div>
                    </div>

                    <!-- KPI 2: Total Pesanan -->
                    <div class="bg-white rounded-2xl p-5 border border-[#F0ECE9] border-l-4 border-l-[#FF9B45] shadow-card flex flex-col justify-between">
                        <div>
                            <div class="flex items-center justify-between mb-2">
                                <span class="text-xs text-[#828282] font-semibold">Total Pesanan</span>
                                <div class="w-8 h-8 rounded-lg bg-[#FF9B45]/15 flex items-center justify-center text-[#924C00]">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"></path></svg>
                                </div>
                            </div>
                            <h3 class="text-2xl font-bold text-[#1C1C1C] tracking-tight">48 Pesanan</h3>
                        </div>
                        <div class="mt-4">
                            <span class="text-xs text-[#828282]">Rata-rata 1.6 order/hari</span>
                        </div>
                    </div>

                    <!-- KPI 3: Pesanan Selesai -->
                    <div class="bg-white rounded-2xl p-5 border border-[#F0ECE9] border-l-4 border-l-[#27AE60] shadow-card flex flex-col justify-between">
                        <div>
                            <div class="flex items-center justify-between mb-2">
                                <span class="text-xs text-[#828282] font-semibold">Pesanan Selesai</span>
                                <div class="w-8 h-8 rounded-lg bg-[#27AE60]/15 flex items-center justify-center text-[#1B7A43]">
                                    <img src="assets/icons/check_circle.svg" alt="Selesai" class="w-4 h-4">
                                </div>
                            </div>
                            <h3 class="text-2xl font-bold text-[#27AE60] tracking-tight">42 Order</h3>
                        </div>
                        <div class="mt-4">
                            <span class="text-xs text-[#27AE60] font-medium">Tingkat pemenuhan 87.5%</span>
                        </div>
                    </div>

                    <!-- KPI 4: Pesanan Pending -->
                    <div class="bg-white rounded-2xl p-5 border border-[#F0ECE9] border-l-4 border-l-[#E2B93B] shadow-card flex flex-col justify-between">
                        <div>
                            <div class="flex items-center justify-between mb-2">
                                <span class="text-xs text-[#828282] font-semibold">Pesanan Pending</span>
                                <div class="w-8 h-8 rounded-lg bg-[#E2B93B]/20 flex items-center justify-center text-[#886C12]">
                                    <img src="assets/icons/hourglass.svg" alt="Pending" class="w-4 h-4">
                                </div>
                            </div>
                            <h3 class="text-2xl font-bold text-[#E2B93B] tracking-tight">6 Order</h3>
                        </div>
                        <div class="mt-4">
                            <span class="text-xs text-[#828282]">5 antrean, 1 tertunda</span>
                        </div>
                    </div>

                </div>

                <!-- ROW 2: CHARTS & ANALYTICS (GRAFIK TREN PENJUALAN & RINGKASAN PEMBAYARAN) -->
                <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 mb-6">
                    
                    <!-- LEFT 2 COLS: GRAFIK TREN PENJUALAN -->
                    <div class="lg:col-span-2 bg-white rounded-2xl border border-[#F0ECE9] p-6 shadow-card flex flex-col justify-between">
                        <div>
                            <!-- Chart Header & Mode Toggle -->
                            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3 mb-6">
                                <div>
                                    <h3 class="text-base font-bold text-[#1C1C1C]">Grafik Tren Penjualan</h3>
                                    <p class="text-xs text-[#828282] mt-0.5">Analisis omset per bulan dalam juta rupiah (Apr – Sep 2025)</p>
                                </div>
                                <div class="bg-[#F5F3F1] p-1 rounded-xl flex items-center gap-1 self-start sm:self-auto">
                                    <button onclick="toggleChartMode('omset')" id="btnChartOmset" 
                                        class="px-3 py-1.5 rounded-lg text-xs font-semibold bg-[#FF9B45] text-white shadow-2xs transition-all">
                                        Pemasukan
                                    </button>
                                    <button onclick="toggleChartMode('order')" id="btnChartOrder" 
                                        class="px-3 py-1.5 rounded-lg text-xs font-semibold text-[#828282] hover:text-[#1C1C1C] transition-all">
                                        Jumlah Pesanan
                                    </button>
                                </div>
                            </div>

                            <!-- Bar Chart Visual Container -->
                            <div class="pt-4 pb-2">
                                <div class="h-56 flex items-end justify-between gap-3 md:gap-6 px-2 relative border-b border-[#F0ECE9]">
                                    
                                    <!-- Y-Axis Background Grid Lines -->
                                    <div class="absolute inset-0 flex flex-col justify-between pointer-events-none text-[10px] text-[#BDBDBD]">
                                        <div class="border-b border-dashed border-[#F0ECE9] pb-0.5 flex justify-between"><span>5 jt</span></div>
                                        <div class="border-b border-dashed border-[#F0ECE9] pb-0.5 flex justify-between"><span>4 jt</span></div>
                                        <div class="border-b border-dashed border-[#F0ECE9] pb-0.5 flex justify-between"><span>3 jt</span></div>
                                        <div class="border-b border-dashed border-[#F0ECE9] pb-0.5 flex justify-between"><span>2 jt</span></div>
                                        <div class="flex justify-between"><span>0</span></div>
                                    </div>

                                    <!-- Bar 1: Apr -->
                                    <div class="flex-1 flex flex-col items-center gap-2 h-full justify-end z-10 group cursor-pointer">
                                        <div class="w-full max-w-[48px] bg-[#FFB87A] hover:bg-[#FF9B45] rounded-t-lg transition-all" style="height: 68%;"></div>
                                        <span class="text-xs text-[#828282] font-medium">Apr</span>
                                    </div>

                                    <!-- Bar 2: Mei -->
                                    <div class="flex-1 flex flex-col items-center gap-2 h-full justify-end z-10 group cursor-pointer">
                                        <div class="w-full max-w-[48px] bg-[#FFB87A] hover:bg-[#FF9B45] rounded-t-lg transition-all" style="height: 75%;"></div>
                                        <span class="text-xs text-[#828282] font-medium">Mei</span>
                                    </div>

                                    <!-- Bar 3: Jun -->
                                    <div class="flex-1 flex flex-col items-center gap-2 h-full justify-end z-10 group cursor-pointer">
                                        <div class="w-full max-w-[48px] bg-[#FFB87A] hover:bg-[#FF9B45] rounded-t-lg transition-all" style="height: 84%;"></div>
                                        <span class="text-xs text-[#828282] font-medium">Jun</span>
                                    </div>

                                    <!-- Bar 4: Jul -->
                                    <div class="flex-1 flex flex-col items-center gap-2 h-full justify-end z-10 group cursor-pointer">
                                        <div class="w-full max-w-[48px] bg-[#FFB87A] hover:bg-[#FF9B45] rounded-t-lg transition-all" style="height: 72%;"></div>
                                        <span class="text-xs text-[#828282] font-medium">Jul</span>
                                    </div>

                                    <!-- Bar 5: Agu -->
                                    <div class="flex-1 flex flex-col items-center gap-2 h-full justify-end z-10 group cursor-pointer">
                                        <div class="w-full max-w-[48px] bg-[#FFB87A] hover:bg-[#FF9B45] rounded-t-lg transition-all" style="height: 88%;"></div>
                                        <span class="text-xs text-[#828282] font-medium">Agu</span>
                                    </div>

                                    <!-- Bar 6: Sep (Aktif / Highest) -->
                                    <div class="flex-1 flex flex-col items-center gap-2 h-full justify-end z-10 relative cursor-pointer">
                                        <!-- Tooltip Tag Pill -->
                                        <div class="absolute -top-7 bg-[#5C3D2A] text-white text-[10px] font-bold py-0.5 px-2 rounded-full shadow-sm flex items-center gap-1 shrink-0 whitespace-nowrap">
                                            <span>Tertinggi ★</span>
                                            <span class="text-[#FF9B45]">4.25 jt</span>
                                        </div>
                                        <div class="w-full max-w-[48px] bg-[#E88C3D] rounded-t-lg transition-all shadow-xs" style="height: 94%;"></div>
                                        <span class="text-xs text-[#924C00] font-bold">Sep (Aktif)</span>
                                    </div>

                                </div>
                            </div>
                        </div>

                        <!-- Bottom Insight Banner -->
                        <div class="pt-4 mt-3 border-t border-[#F0ECE9] flex flex-col sm:flex-row sm:items-center sm:justify-between gap-2 text-xs">
                            <div class="flex items-center gap-1.5 text-[#27AE60] font-semibold">
                                <span>▲</span>
                                <span>Pertumbuhan omset rata-rata 8.4% per bulan</span>
                            </div>
                            <span class="text-[#828282]">Update otomatis per pesanan selesai</span>
                        </div>
                    </div>

                    <!-- RIGHT 1 COL: RINGKASAN PEMBAYARAN -->
                    <div class="bg-white rounded-2xl border border-[#F0ECE9] p-6 shadow-card flex flex-col justify-between">
                        <div>
                            <!-- Header -->
                            <div class="flex items-start justify-between pb-3 border-b border-[#F0ECE9] mb-4">
                                <div>
                                    <h3 class="text-base font-bold text-[#1C1C1C]">Ringkasan Pembayaran</h3>
                                    <p class="text-xs text-[#828282] mt-0.5">Distribusi pesanan bulan September</p>
                                </div>
                                <div class="text-[#828282]">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 3.055A9.001 9.001 0 1020.945 13H11V3.055z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20.488 9H15V3.512A9.025 9.025 0 0120.488 9z"></path></svg>
                                </div>
                            </div>

                            <!-- Donut Chart Representation -->
                            <div class="flex items-center justify-center my-4">
                                <div class="relative w-40 h-40 flex items-center justify-center">
                                    <!-- Donut Chart SVG -->
                                    <svg class="w-full h-full -rotate-90" viewBox="0 0 100 100">
                                        <!-- Background Circle -->
                                        <circle cx="50" cy="50" r="38" fill="none" stroke="#F0ECE9" stroke-width="12"></circle>
                                        <!-- Green: Lunas (75% = 179 circumference) -->
                                        <circle cx="50" cy="50" r="38" fill="none" stroke="#27AE60" stroke-width="12"
                                            stroke-dasharray="238.76" stroke-dashoffset="59.69" stroke-linecap="round" class="transition-all"></circle>
                                        <!-- Yellow: Hutang (16.7% = 39.88) -->
                                        <circle cx="50" cy="50" r="38" fill="none" stroke="#E2B93B" stroke-width="12"
                                            stroke-dasharray="238.76" stroke-dashoffset="198.88" stroke-linecap="round" class="transition-all"></circle>
                                        <!-- Gray: Belum Bayar (8.3% = 19.8) -->
                                        <circle cx="50" cy="50" r="38" fill="none" stroke="#828282" stroke-width="12"
                                            stroke-dasharray="238.76" stroke-dashoffset="218.96" stroke-linecap="round" class="transition-all"></circle>
                                    </svg>
                                    
                                    <!-- Center Counter Text -->
                                    <div class="absolute inset-0 flex flex-col items-center justify-center text-center">
                                        <span class="text-3xl font-bold text-[#1C1C1C] leading-none">48</span>
                                        <span class="text-[10px] text-[#828282] uppercase tracking-wider font-semibold mt-1">Total Pesanan</span>
                                    </div>
                                </div>
                            </div>

                            <!-- Legend Breakdown Items -->
                            <div class="space-y-3 pt-2">
                                <!-- Row 1: Lunas -->
                                <div class="bg-[#FBF9F7] rounded-xl p-2.5 flex items-center justify-between border border-[#F0ECE9]">
                                    <div class="flex items-center gap-2">
                                        <span class="w-2.5 h-2.5 rounded-full bg-[#27AE60]"></span>
                                        <div>
                                            <div class="flex items-center gap-1.5">
                                                <span class="text-xs font-bold text-[#1C1C1C]">Lunas</span>
                                                <span class="text-[10px] font-semibold bg-[#27AE60]/15 text-[#1B7A43] px-1.5 py-0.2 rounded">75%</span>
                                            </div>
                                            <p class="text-[11px] text-[#828282]">36 pesanan</p>
                                        </div>
                                    </div>
                                    <span class="text-xs font-bold text-[#1C1C1C]">Rp 3.420.000</span>
                                </div>

                                <!-- Row 2: Hutang -->
                                <div class="bg-[#FBF9F7] rounded-xl p-2.5 flex items-center justify-between border border-[#F0ECE9]">
                                    <div class="flex items-center gap-2">
                                        <span class="w-2.5 h-2.5 rounded-full bg-[#E2B93B]"></span>
                                        <div>
                                            <div class="flex items-center gap-1.5">
                                                <span class="text-xs font-bold text-[#1C1C1C]">Hutang</span>
                                                <span class="text-[10px] font-semibold bg-[#E2B93B]/20 text-[#886C12] px-1.5 py-0.2 rounded">16.7%</span>
                                            </div>
                                            <p class="text-[11px] text-[#828282]">8 pesanan</p>
                                        </div>
                                    </div>
                                    <span class="text-xs font-bold text-[#1C1C1C]">Rp 610.000</span>
                                </div>

                                <!-- Row 3: Belum Bayar -->
                                <div class="bg-[#FBF9F7] rounded-xl p-2.5 flex items-center justify-between border border-[#F0ECE9]">
                                    <div class="flex items-center gap-2">
                                        <span class="w-2.5 h-2.5 rounded-full bg-[#828282]"></span>
                                        <div>
                                            <div class="flex items-center gap-1.5">
                                                <span class="text-xs font-bold text-[#1C1C1C]">Belum Bayar</span>
                                                <span class="text-[10px] font-semibold bg-[#828282]/15 text-[#544337] px-1.5 py-0.2 rounded">8.3%</span>
                                            </div>
                                            <p class="text-[11px] text-[#828282]">4 pesanan</p>
                                        </div>
                                    </div>
                                    <span class="text-xs font-bold text-[#1C1C1C]">Rp 220.000</span>
                                </div>
                            </div>
                        </div>
                    </div>

                </div>

                <!-- ROW 3: RIWAYAT SEMUA PESANAN TABLE CARD -->
                <div class="bg-white rounded-2xl border border-[#F0ECE9] shadow-card overflow-hidden">
                    
                    <!-- Table Card Header & Segmented Status Filters -->
                    <div class="px-6 py-4 border-b border-[#F0ECE9] flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
                        <div class="flex items-center gap-2.5">
                            <h3 class="text-base font-bold text-[#1C1C1C]">Riwayat Semua Pesanan</h3>
                            <span class="bg-[#FF9B45]/15 text-[#924C00] text-xs font-semibold px-2.5 py-0.5 rounded-full">
                                48 Data
                            </span>
                        </div>

                        <!-- Segmented Filter Pills -->
                        <div class="flex flex-wrap items-center gap-1.5 text-xs font-semibold">
                            <button onclick="filterReportTable('all', this)" id="filterBtnAll" 
                                class="px-3 py-1.5 rounded-lg bg-[#3E2B21] text-white shadow-2xs transition-colors">
                                Semua (48)
                            </button>
                            <button onclick="filterReportTable('Lunas', this)" id="filterBtnLunas" 
                                class="px-3 py-1.5 rounded-lg bg-white border border-[#E0E0E0] text-[#544337] hover:bg-[#F5F3F1] transition-colors">
                                Lunas (36)
                            </button>
                            <button onclick="filterReportTable('Hutang', this)" id="filterBtnHutang" 
                                class="px-3 py-1.5 rounded-lg bg-white border border-[#E0E0E0] text-[#544337] hover:bg-[#F5F3F1] transition-colors">
                                Hutang (8)
                            </button>
                            <button onclick="filterReportTable('Belum Bayar', this)" id="filterBtnBelumBayar" 
                                class="px-3 py-1.5 rounded-lg bg-white border border-[#E0E0E0] text-[#544337] hover:bg-[#F5F3F1] transition-colors">
                                Belum Bayar (4)
                            </button>
                        </div>
                    </div>

                    <!-- Responsive Orders Table -->
                    <div class="overflow-x-auto">
                        <table class="w-full text-left border-collapse" id="ordersReportTable">
                            <!-- Orange Header (Matching Figma) -->
                            <thead>
                                <tr class="bg-[#FF9B45] text-white text-xs font-bold uppercase tracking-wider">
                                    <th class="py-3 px-4 text-center w-12">No</th>
                                    <th class="py-3 px-4 min-w-[140px]">Tanggal & Jam</th>
                                    <th class="py-3 px-4 min-w-[220px]">No. Pesanan & Pelanggan</th>
                                    <th class="py-3 px-4 min-w-[280px]">Rincian Produk</th>
                                    <th class="py-3 px-4 text-right min-w-[130px]">Total Tagihan</th>
                                    <th class="py-3 px-4 text-center min-w-[130px]">Status Bayar</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-[#F0ECE9] text-xs md:text-sm">
                                <?php foreach ($orders_report as $ord): ?>
                                    <tr class="hover:bg-[#FFF8F2]/60 transition-colors order-row" data-status="<?= htmlspecialchars($ord['status_bayar']) ?>">
                                        
                                        <!-- No -->
                                        <td class="py-3.5 px-4 text-center text-[#828282] font-semibold">
                                            <?= $ord['no'] ?>
                                        </td>

                                        <!-- Tanggal & Jam -->
                                        <td class="py-3.5 px-4">
                                            <p class="font-bold text-[#1C1C1C]"><?= htmlspecialchars($ord['tanggal']) ?></p>
                                            <p class="text-[11px] text-[#828282]"><?= htmlspecialchars($ord['jam']) ?></p>
                                        </td>

                                        <!-- No. Pesanan & Pelanggan -->
                                        <td class="py-3.5 px-4">
                                            <div class="flex items-center gap-1.5">
                                                <span class="font-mono font-bold text-[#924C00]"><?= htmlspecialchars($ord['order_id']) ?></span>
                                                <span class="text-[#828282]">—</span>
                                                <span class="font-bold text-[#1C1C1C]"><?= htmlspecialchars($ord['customer']) ?></span>
                                            </div>
                                        </td>

                                        <!-- Rincian Produk -->
                                        <td class="py-3.5 px-4 text-[#544337] truncate max-w-sm">
                                            <?= htmlspecialchars($ord['items']) ?>
                                        </td>

                                        <!-- Total Tagihan -->
                                        <td class="py-3.5 px-4 text-right font-bold text-[#1C1C1C]">
                                            <?= htmlspecialchars($ord['total']) ?>
                                        </td>

                                        <!-- Status Bayar Badge -->
                                        <td class="py-3.5 px-4 text-center">
                                            <span class="inline-block px-2.5 py-1 rounded-full text-xs font-semibold <?= $ord['status_bayar_class'] ?>">
                                                <?= htmlspecialchars($ord['status_bayar_label'] ?? $ord['status_bayar']) ?>
                                            </span>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>

                    <!-- Pagination Footer -->
                    <div class="px-6 py-4 border-t border-[#F0ECE9] flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3 text-xs text-[#828282]">
                        <span>Menampilkan 1-5 dari 48 pesanan</span>
                        
                        <div class="flex items-center gap-1.5">
                            <button class="w-7 h-7 rounded-md text-[#828282] hover:text-[#1C1C1C] hover:bg-[#F5F3F1] flex items-center justify-center transition-colors">‹</button>
                            <button class="w-7 h-7 rounded-md bg-[#FF9B45] text-white font-bold text-xs flex items-center justify-center shadow-xs">1</button>
                            <button class="w-7 h-7 rounded-md text-[#544337] hover:bg-[#F5F3F1] font-medium text-xs flex items-center justify-center transition-colors">2</button>
                            <button class="w-7 h-7 rounded-md text-[#544337] hover:bg-[#F5F3F1] font-medium text-xs flex items-center justify-center transition-colors">3</button>
                            <span class="px-1 text-[#828282]">...</span>
                            <button class="w-7 h-7 rounded-md text-[#544337] hover:bg-[#F5F3F1] font-medium text-xs flex items-center justify-center transition-colors">10</button>
                            <button class="w-7 h-7 rounded-md text-[#828282] hover:text-[#1C1C1C] hover:bg-[#F5F3F1] flex items-center justify-center transition-colors">›</button>
                        </div>
                    </div>

                </div>

            </main>
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

        // Apply Period Filter
        function applyPeriodFilter() {
            const periode = document.getElementById('periodeSelect').value;
            alert('Memuat data laporan untuk periode: ' + document.getElementById('periodeSelect').selectedOptions[0].text);
        }

        // Export Excel Action
        function handleExportExcel() {
            alert('Menyiapkan dan mengunduh berkas laporan rekap penjualan (Excel .xlsx)...');
        }

        // Switch Chart Mode (Omset vs Jumlah Pesanan)
        function toggleChartMode(mode) {
            const btnOmset = document.getElementById('btnChartOmset');
            const btnOrder = document.getElementById('btnChartOrder');

            if (mode === 'omset') {
                btnOmset.className = 'px-3 py-1.5 rounded-lg text-xs font-semibold bg-[#FF9B45] text-white shadow-2xs transition-all';
                btnOrder.className = 'px-3 py-1.5 rounded-lg text-xs font-semibold text-[#828282] hover:text-[#1C1C1C] transition-all';
            } else {
                btnOrder.className = 'px-3 py-1.5 rounded-lg text-xs font-semibold bg-[#FF9B45] text-white shadow-2xs transition-all';
                btnOmset.className = 'px-3 py-1.5 rounded-lg text-xs font-semibold text-[#828282] hover:text-[#1C1C1C] transition-all';
            }
        }

        // Filter Report Table by Payment Status
        function filterReportTable(status, activeBtn) {
            const buttons = [
                document.getElementById('filterBtnAll'),
                document.getElementById('filterBtnLunas'),
                document.getElementById('filterBtnHutang'),
                document.getElementById('filterBtnBelumBayar')
            ];

            buttons.forEach(btn => {
                btn.className = 'px-3 py-1.5 rounded-lg bg-white border border-[#E0E0E0] text-[#544337] hover:bg-[#F5F3F1] transition-colors';
            });
            activeBtn.className = 'px-3 py-1.5 rounded-lg bg-[#3E2B21] text-white shadow-2xs transition-colors';

            const rows = document.querySelectorAll('#ordersReportTable tbody tr');
            rows.forEach(row => {
                const rowStatus = row.getAttribute('data-status');
                if (status === 'all' || rowStatus === status) {
                    row.style.display = '';
                } else {
                    row.style.display = 'none';
                }
            });
        }
    </script>
</body>
</html>
