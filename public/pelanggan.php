<?php
session_start();

// Proteksi Autentikasi: Hanya user yang sudah login yang diizinkan mengakses halaman Pelanggan
if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true) {
    header("Location: login.php");
    exit();
}

// Coba koneksi database jika config tersedia
$configFile = __DIR__ . "/../config/config.php";
if (file_exists($configFile)) {
    require_once $configFile;
}

// Data Master Pelanggan Sesuai Figma
$pelanggan_list = [
    [
        'id' => 1,
        'avatar' => 'SD',
        'nama' => 'Ibu Sari Dewi',
        'no_wa' => '0812-3456-7890',
        'wa_clean' => '6281234567890',
        'alamat' => 'Jl. Mawar No. 5, RT 02/04, Kel. Sukajadi, Kec. Sukajadi',
        'alamat_short' => 'Jl. Mawar No. 5, RT 02/04, Suk...',
        'pesanan_count' => 12,
        'tagihan' => 'Lunas',
        'tagihan_class' => 'bg-[#27AE60]/15 text-[#1B7A43]',
        'total_transaksi' => 'Rp 560.000',
        'total_raw' => 560000,
        'tipe' => 'Pelanggan Reguler',
        'member_since' => 'Sejak Jan 2025',
        'catatan' => 'Pengiriman pagi selalu sebelum jam 10:00 WIB, roti minta diiris tipis.',
        'antrean' => 1,
        'history' => [
            [
                'order_no' => '#ORD-20250926-01',
                'status_bayar' => 'Belum Bayar',
                'status_bayar_class' => 'bg-[#E2B93B]/20 text-[#886C12]',
                'amount' => 'Rp 110.000',
                'delivery_status' => 'Siap Kirim >',
                'delivery_status_class' => 'text-[#924C00] font-semibold',
                'date_time' => '🕒 26 Sep 2025, 14:00',
                'items' => '20 pcs Roti Tawar Kupas Special, 1 mika Donat Coklat'
            ],
            [
                'order_no' => '#ORD-20250918-03',
                'status_bayar' => 'Lunas',
                'status_bayar_class' => 'bg-[#27AE60]/15 text-[#1B7A43]',
                'amount' => 'Rp 175.000',
                'delivery_status' => '✓ Selesai Diterima >',
                'delivery_status_class' => 'text-[#27AE60] font-semibold',
                'date_time' => '📅 18 Sep 2025',
                'items' => '10 pcs Roti Sisir Butter, 2 box Kue Bolu'
            ],
            [
                'order_no' => '#ORD-20250904-02',
                'status_bayar' => 'Lunas',
                'status_bayar_class' => 'bg-[#27AE60]/15 text-[#1B7A43]',
                'amount' => 'Rp 120.000',
                'delivery_status' => '✓ Selesai Diterima >',
                'delivery_status_class' => 'text-[#27AE60] font-semibold',
                'date_time' => '📅 04 Sep 2025',
                'items' => '15 pcs Roti Sobek Keju Coklat'
            ]
        ]
    ],
    [
        'id' => 2,
        'avatar' => 'BS',
        'nama' => 'Pak Budi Santoso',
        'no_wa' => '0823-4567-8901',
        'wa_clean' => '6282345678901',
        'alamat' => 'Jl. Kenanga No. 10, Blok C, Perum Graha Indah',
        'alamat_short' => 'Jl. Kenanga No. 10, Blok C',
        'pesanan_count' => 8,
        'tagihan' => 'Hutang',
        'tagihan_class' => 'bg-[#EB5757]/15 text-[#EB5757]',
        'total_transaksi' => 'Rp 320.000',
        'total_raw' => 320000,
        'tipe' => 'Pelanggan Reguler',
        'member_since' => 'Sejak Mar 2025',
        'catatan' => 'Minta kardus roti sisir diikat tali rafia rapat saat diambil sore.',
        'antrean' => 1,
        'history' => [
            [
                'order_no' => '#ORD-20250926-02',
                'status_bayar' => 'Hutang',
                'status_bayar_class' => 'bg-[#EB5757]/15 text-[#EB5757]',
                'amount' => 'Rp 95.000',
                'delivery_status' => 'Antrean Oven >',
                'delivery_status_class' => 'text-[#D97706] font-semibold',
                'date_time' => '🕒 Besok, 09:00',
                'items' => 'Roti Sisir 2 kardus (24 pcs)'
            ],
            [
                'order_no' => '#ORD-20250915-01',
                'status_bayar' => 'Lunas',
                'status_bayar_class' => 'bg-[#27AE60]/15 text-[#1B7A43]',
                'amount' => 'Rp 225.000',
                'delivery_status' => '✓ Selesai Diterima >',
                'delivery_status_class' => 'text-[#27AE60] font-semibold',
                'date_time' => '📅 15 Sep 2025',
                'items' => '25 pcs Roti Manis Coklat Keju'
            ]
        ]
    ],
    [
        'id' => 3,
        'avatar' => 'MJ',
        'nama' => 'Toko Maju Jaya',
        'no_wa' => '0834-5678-9012',
        'wa_clean' => '6283456789012',
        'alamat' => 'Jl. Raya Industri No. 20, Ruko Sentosa No. 4, Cimahi',
        'alamat_short' => 'Jl. Raya Industri No. 20, Ruko S...',
        'pesanan_count' => 25,
        'tagihan' => 'Lunas',
        'tagihan_class' => 'bg-[#27AE60]/15 text-[#1B7A43]',
        'total_transaksi' => 'Rp 1.250.000',
        'total_raw' => 1250000,
        'tipe' => 'Mitra Reseller',
        'member_since' => 'Sejak Nov 2024',
        'catatan' => 'Pengiriman via armada roda tiga langsung ke ruko depan.',
        'antrean' => 0,
        'history' => [
            [
                'order_no' => '#ORD-20250925-04',
                'status_bayar' => 'Lunas',
                'status_bayar_class' => 'bg-[#27AE60]/15 text-[#1B7A43]',
                'amount' => 'Rp 450.000',
                'delivery_status' => '✓ Selesai Diterima >',
                'delivery_status_class' => 'text-[#27AE60] font-semibold',
                'date_time' => '📅 25 Sep 2025',
                'items' => 'Kue Bolu 15pcs, Roti Sobek 5 box'
            ],
            [
                'order_no' => '#ORD-20250910-02',
                'status_bayar' => 'Lunas',
                'status_bayar_class' => 'bg-[#27AE60]/15 text-[#1B7A43]',
                'amount' => 'Rp 800.000',
                'delivery_status' => '✓ Selesai Diterima >',
                'delivery_status_class' => 'text-[#27AE60] font-semibold',
                'date_time' => '📅 10 Sep 2025',
                'items' => '50 pcs Roti Manis Aneka Rasa'
            ]
        ]
    ],
    [
        'id' => 4,
        'avatar' => 'PH',
        'nama' => 'Warung Pak Haji',
        'no_wa' => '0856-7890-1234',
        'wa_clean' => '6285678901234',
        'alamat' => 'Jl. Pesantren No. 3, RT 01/02',
        'alamat_short' => 'Jl. Pesantren No. 3',
        'pesanan_count' => 5,
        'tagihan' => 'Belum Bayar',
        'tagihan_class' => 'bg-[#E2B93B]/20 text-[#886C12]',
        'total_transaksi' => 'Rp 180.000',
        'total_raw' => 180000,
        'tipe' => 'Pelanggan Reguler',
        'member_since' => 'Sejak Agu 2025',
        'catatan' => 'Ambil langsung sendiri di toko jam 07:00 pagi.',
        'antrean' => 1,
        'history' => [
            [
                'order_no' => '#ORD-20250925-02',
                'status_bayar' => 'Belum Bayar',
                'status_bayar_class' => 'bg-[#E2B93B]/20 text-[#886C12]',
                'amount' => 'Rp 180.000',
                'delivery_status' => 'Siap Diambil >',
                'delivery_status_class' => 'text-[#D97706] font-semibold',
                'date_time' => '📅 25 Sep 2025',
                'items' => 'Roti Tawar 50pcs (Kemasan Mika)'
            ]
        ]
    ],
    [
        'id' => 5,
        'avatar' => 'KS',
        'nama' => 'Kafe Kopi Seduh',
        'no_wa' => '0812-9876-5432',
        'wa_clean' => '6281298765432',
        'alamat' => 'Jl. Anggrek No. 14, Sentra Kuliner',
        'alamat_short' => 'Jl. Anggrek No. 14',
        'pesanan_count' => 18,
        'tagihan' => 'Lunas',
        'tagihan_class' => 'bg-[#27AE60]/15 text-[#1B7A43]',
        'total_transaksi' => 'Rp 890.000',
        'total_raw' => 890000,
        'tipe' => 'Mitra Reseller',
        'member_since' => 'Sejak Feb 2025',
        'catatan' => 'Sertakan nota fisik rangkap dua berstempel toko.',
        'antrean' => 0,
        'history' => [
            [
                'order_no' => '#ORD-20250920-05',
                'status_bayar' => 'Lunas',
                'status_bayar_class' => 'bg-[#27AE60]/15 text-[#1B7A43]',
                'amount' => 'Rp 350.000',
                'delivery_status' => '✓ Selesai Diterima >',
                'delivery_status_class' => 'text-[#27AE60] font-semibold',
                'date_time' => '📅 20 Sep 2025',
                'items' => 'Croissant Mini 30pcs, Donat Salju 20pcs'
            ],
            [
                'order_no' => '#ORD-20250905-01',
                'status_bayar' => 'Lunas',
                'status_bayar_class' => 'bg-[#27AE60]/15 text-[#1B7A43]',
                'amount' => 'Rp 540.000',
                'delivery_status' => '✓ Selesai Diterima >',
                'delivery_status_class' => 'text-[#27AE60] font-semibold',
                'date_time' => '📅 05 Sep 2025',
                'items' => 'Roti Tawar Gandum 35 loaf'
            ]
        ]
    ]
];
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pelanggan - Berkat Dinasti</title>
    
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

                    <!-- Pesanan -->
                    <a href="pesanan.php" class="text-white/80 hover:text-white hover:bg-white/10 rounded-lg px-4 py-3 flex items-center gap-3 font-medium text-sm transition-colors">
                        <img src="assets/icons/nav_pesanan.svg" alt="Pesanan" class="w-5 h-5 opacity-90">
                        <span>Pesanan</span>
                    </a>

                    <!-- Pelanggan (Active) -->
                    <a href="pelanggan.php" class="bg-[#FF9B45] text-white rounded-lg px-4 py-3 flex items-center gap-3 font-medium text-sm transition-all shadow-sm">
                        <img src="assets/icons/nav_pelanggan.svg" alt="Pelanggan" class="w-5 h-5">
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
                            <span class="text-[#924C00] font-medium">Pelanggan</span>
                        </div>
                        <!-- Section Heading -->
                        <h1 class="text-[#1B1C1B] font-bold text-base md:text-lg leading-tight mt-0.5">Data Pelanggan</h1>
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
                <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 mb-6">
                    
                    <!-- Left: Search & Filter Dropdown -->
                    <div class="flex flex-wrap items-center gap-3 flex-1 max-w-2xl">
                        <!-- Search Box -->
                        <div class="relative flex-1 min-w-[240px]">
                            <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-[#828282]">
                                <img src="assets/icons/search.svg" alt="Cari" class="w-4 h-4 opacity-70">
                            </div>
                            <input type="text" id="customerSearchInput" onkeyup="filterCustomers()" placeholder="Cari nama pelanggan, no. telp..." 
                                class="w-full bg-white border border-[#E0E0E0] rounded-xl pl-10 pr-4 py-2.5 text-xs md:text-sm text-[#1C1C1C] placeholder-[#828282] focus:border-[#FF9B45] focus:outline-none shadow-2xs transition-colors">
                        </div>

                        <!-- Dropdown Filter Tagihan -->
                        <div class="relative">
                            <select id="statusFilter" onchange="filterCustomers()" 
                                class="appearance-none bg-white border border-[#E0E0E0] rounded-xl pl-3.5 pr-8 py-2.5 text-xs md:text-sm text-[#1C1C1C] font-medium focus:border-[#FF9B45] focus:outline-none cursor-pointer shadow-2xs">
                                <option value="all">Status: Semua Tagihan</option>
                                <option value="Lunas">Status: Lunas</option>
                                <option value="Hutang">Status: Hutang</option>
                                <option value="Belum Bayar">Status: Belum Bayar</option>
                            </select>
                            <div class="absolute inset-y-0 right-0 pr-3 flex items-center pointer-events-none text-[#828282]">
                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path></svg>
                            </div>
                        </div>

                        <!-- Badge Pelanggan Count -->
                        <span class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-full text-xs font-semibold bg-[#FEF6EE] text-[#D97706] border border-[#FDE6D2] shrink-0">
                            <span class="w-2 h-2 rounded-full bg-[#FF9B45]"></span>
                            <span id="customerCountBadge">48 Pelanggan</span>
                        </span>
                    </div>

                    <!-- Right: Button Tambah Pelanggan -->
                    <button onclick="openCustomerModal()" 
                        class="bg-[#FF9B45] hover:bg-[#E88C3D] text-white text-sm font-semibold px-4 py-2.5 rounded-xl flex items-center justify-center gap-2 transition-all shadow-button-orange active:scale-95 shrink-0">
                        <img src="assets/icons/user_plus.svg" alt="Tambah Pelanggan" class="w-4 h-4">
                        <span>+ Tambah Pelanggan</span>
                    </button>
                </div>

                <!-- SECTION 1: DAFTAR KONTAK TABLE CARD -->
                <div class="bg-white rounded-2xl border border-[#F0ECE9] shadow-card overflow-hidden mb-6">
                    
                    <!-- Card Top Header -->
                    <div class="px-5 py-4 border-b border-[#F0ECE9] flex items-center justify-between">
                        <div class="flex items-center gap-2.5">
                            <div class="w-6 h-6 rounded-md bg-[#FF9B45]/15 flex items-center justify-center">
                                <img src="assets/icons/users.svg" alt="Kontak" class="w-3.5 h-3.5">
                            </div>
                            <h3 class="text-[#1C1C1C] font-bold text-sm md:text-base">Daftar Kontak</h3>
                        </div>
                        <span class="text-xs text-[#828282]">Diperbarui: Hari ini, 15:40</span>
                    </div>

                    <!-- Responsive Table -->
                    <div class="overflow-x-auto">
                        <table class="w-full text-left border-collapse" id="customersTable">
                            <!-- Table Header with Solid Orange Background (Matching Figma) -->
                            <thead>
                                <tr class="bg-[#FF9B45] text-white text-xs font-bold uppercase tracking-wider">
                                    <th class="py-3 px-4 text-center w-12">No</th>
                                    <th class="py-3 px-4 min-w-[200px]">Nama Pelanggan</th>
                                    <th class="py-3 px-4 min-w-[220px]">Alamat Pengiriman</th>
                                    <th class="py-3 px-4 text-center min-w-[110px]">Pesanan</th>
                                    <th class="py-3 px-4 text-center min-w-[110px]">Tagihan</th>
                                    <th class="py-3 px-4 text-right min-w-[130px]">Total Transaksi</th>
                                    <th class="py-3 px-4 text-center min-w-[110px]">Aksi</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-[#F0ECE9] text-xs md:text-sm">
                                <?php foreach ($pelanggan_list as $idx => $cust): ?>
                                    <tr id="custRow-<?= $cust['id'] ?>" onclick="selectCustomer(<?= $cust['id'] ?>)" 
                                        class="cursor-pointer transition-colors <?= $idx === 0 ? 'bg-[#FFF8F2] border-l-4 border-l-[#FF9B45]' : 'hover:bg-[#FAF8F5]' ?>">
                                        
                                        <!-- No Column -->
                                        <td class="py-3.5 px-4 text-center">
                                            <span class="w-6 h-6 rounded-full <?= $idx === 0 ? 'bg-[#924C00] text-white' : 'bg-[#F0ECE9] text-[#544337]' ?> font-bold text-xs inline-flex items-center justify-center">
                                                <?= $idx + 1 ?>
                                            </span>
                                        </td>

                                        <!-- Nama & Phone -->
                                        <td class="py-3.5 px-4">
                                            <div class="flex items-center gap-3">
                                                <div class="w-9 h-9 rounded-full bg-[#FF9B45]/20 text-[#924C00] font-bold text-xs flex items-center justify-center shrink-0">
                                                    <?= htmlspecialchars($cust['avatar']) ?>
                                                </div>
                                                <div class="truncate">
                                                    <p class="font-bold text-[#1C1C1C] leading-tight customer-name truncate"><?= htmlspecialchars($cust['nama']) ?></p>
                                                    <div class="flex items-center gap-1 text-[#828282] text-[11px] mt-0.5">
                                                        <span>📞</span>
                                                        <span class="customer-phone"><?= htmlspecialchars($cust['no_wa']) ?></span>
                                                    </div>
                                                </div>
                                            </div>
                                        </td>

                                        <!-- Alamat -->
                                        <td class="py-3.5 px-4 text-[#544337] truncate max-w-xs">
                                            <?= htmlspecialchars($cust['alamat_short']) ?>
                                        </td>

                                        <!-- Pesanan Count Badge -->
                                        <td class="py-3.5 px-4 text-center">
                                            <span class="bg-[#F0ECE9] text-[#544337] font-medium text-xs px-2.5 py-1 rounded-full inline-block">
                                                <?= $cust['pesanan_count'] ?> pesanan
                                            </span>
                                        </td>

                                        <!-- Status Tagihan Badge -->
                                        <td class="py-3.5 px-4 text-center">
                                            <span class="font-semibold text-xs px-2.5 py-1 rounded-full customer-status <?= $cust['tagihan_class'] ?> inline-block">
                                                <?= htmlspecialchars($cust['tagihan']) ?>
                                            </span>
                                        </td>

                                        <!-- Total Transaksi -->
                                        <td class="py-3.5 px-4 text-right font-bold text-[#1C1C1C]">
                                            <?= htmlspecialchars($cust['total_transaksi']) ?>
                                        </td>

                                        <!-- Action Buttons -->
                                        <td class="py-3.5 px-4 text-center" onclick="event.stopPropagation()">
                                            <div class="flex items-center justify-center gap-2">
                                                <!-- View -->
                                                <button onclick="selectCustomer(<?= $cust['id'] ?>)" title="Lihat Profil" 
                                                    class="p-1.5 text-[#828282] hover:text-[#FF9B45] hover:bg-[#F5F3F1] rounded-lg transition-colors">
                                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path></svg>
                                                </button>
                                                <!-- Edit -->
                                                <button onclick="openCustomerModal(<?= $cust['id'] ?>)" title="Edit Pelanggan" 
                                                    class="p-1.5 text-[#828282] hover:text-[#2F80ED] hover:bg-[#F5F3F1] rounded-lg transition-colors">
                                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path></svg>
                                                </button>
                                                <!-- Delete -->
                                                <button onclick="handleDeleteCustomer(<?= $cust['id'] ?>, '<?= htmlspecialchars($cust['nama']) ?>')" title="Hapus Pelanggan" 
                                                    class="p-1.5 text-[#828282] hover:text-[#EB5757] hover:bg-[#FFF5F5] rounded-lg transition-colors">
                                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg>
                                                </button>
                                            </div>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>

                    <!-- Pagination Footer -->
                    <div class="px-5 py-4 border-t border-[#F0ECE9] flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3 text-xs text-[#828282]">
                        <span>Menampilkan 1-5 dari 48 pelanggan</span>
                        
                        <div class="flex items-center gap-1.5">
                            <button class="px-2.5 py-1 text-[#828282] hover:text-[#1C1C1C] hover:bg-[#F5F3F1] rounded-md transition-colors font-medium">Prev</button>
                            <button class="w-7 h-7 rounded-md bg-[#FF9B45] text-white font-bold text-xs flex items-center justify-center shadow-xs">1</button>
                            <button class="w-7 h-7 rounded-md text-[#544337] hover:bg-[#F5F3F1] font-medium text-xs flex items-center justify-center transition-colors">2</button>
                            <button class="w-7 h-7 rounded-md text-[#544337] hover:bg-[#F5F3F1] font-medium text-xs flex items-center justify-center transition-colors">3</button>
                            <span class="px-1 text-[#828282]">...</span>
                            <button class="w-7 h-7 rounded-md text-[#544337] hover:bg-[#F5F3F1] font-medium text-xs flex items-center justify-center transition-colors">10</button>
                            <button class="px-2.5 py-1 text-[#828282] hover:text-[#1C1C1C] hover:bg-[#F5F3F1] rounded-md transition-colors font-medium">Next</button>
                        </div>
                    </div>

                </div>

                <!-- SECTION 2: BOTTOM DETAILS (2 COLUMNS: DETAIL PELANGGAN & RIWAYAT PESANAN) -->
                <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                    
                    <!-- LEFT CARD: DETAIL PELANGGAN TERPILIH -->
                    <div class="bg-white rounded-2xl border border-[#F0ECE9] p-6 shadow-card flex flex-col justify-between">
                        <div>
                            <!-- Header with Avatar & Status -->
                            <div class="flex items-start justify-between pb-4 border-b border-[#F0ECE9] mb-4">
                                <div class="flex items-center gap-3.5">
                                    <div id="detailAvatar" class="w-12 h-12 rounded-full bg-[#FF9B45] text-white font-bold text-base flex items-center justify-center shadow-sm">
                                        SD
                                    </div>
                                    <div>
                                        <h3 id="detailNama" class="text-[#1C1C1C] font-bold text-base md:text-lg">Ibu Sari Dewi</h3>
                                        <div class="flex items-center gap-2 text-xs text-[#828282] mt-0.5">
                                            <span id="detailTipe" class="font-medium text-[#544337]">Pelanggan Reguler</span>
                                            <span>•</span>
                                            <span id="detailMemberSince">Sejak Jan 2025</span>
                                        </div>
                                    </div>
                                </div>
                                <button onclick="resetCustomerSelection()" class="text-[#828282] hover:text-[#1C1C1C] p-1 rounded-lg hover:bg-[#F5F3F1] transition-colors" title="Tutup Detail">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                                </button>
                            </div>

                            <!-- Action Buttons Row -->
                            <div class="grid grid-cols-2 gap-3 mb-4">
                                <!-- WhatsApp Chat Button -->
                                <a id="detailWaLink" href="https://wa.me/6281234567890" target="_blank" 
                                    class="bg-[#27AE60]/10 hover:bg-[#27AE60]/20 text-[#1B7A43] border border-[#27AE60]/30 text-xs font-semibold py-2.5 px-3 rounded-xl flex items-center justify-center gap-2 transition-colors">
                                    <svg class="w-4 h-4 fill-current" viewBox="0 0 24 24"><path d="M.057 24l1.687-6.163c-1.041-1.804-1.588-3.849-1.587-5.946.003-6.556 5.338-11.891 11.893-11.891 3.181.001 6.167 1.24 8.413 3.488 2.245 2.248 3.481 5.236 3.48 8.414-.003 6.557-5.338 11.892-11.893 11.892-1.99-.001-3.951-.5-5.688-1.448l-6.305 1.654zm6.597-3.807c1.676.995 3.276 1.591 5.392 1.592 5.448 0 9.886-4.434 9.889-9.885.002-5.462-4.415-9.89-9.881-9.892-5.452 0-9.887 4.434-9.889 9.884-.001 2.225.651 3.891 1.746 5.634l-.999 3.648 3.742-.981zm11.387-5.464c-.074-.124-.272-.198-.57-.347-.297-.149-1.758-.868-2.031-.967-.272-.099-.47-.149-.669.149-.198.297-.768.967-.941 1.165-.173.198-.347.223-.644.074-.297-.149-1.255-.462-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.297-.347.446-.521.151-.172.2-.296.3-.495.099-.198.05-.372-.025-.521-.075-.148-.669-1.611-.916-2.206-.242-.579-.487-.501-.669-.51l-.57-.01c-.198 0-.52.074-.792.372s-1.04 1.016-1.04 2.479 1.065 2.876 1.213 3.074c.149.198 2.095 3.2 5.076 4.487.709.306 1.263.489 1.694.626.712.226 1.36.194 1.872.118.571-.085 1.758-.719 2.006-1.413.248-.695.248-1.29.173-1.414z"/></svg>
                                    <span>Chat WhatsApp</span>
                                </a>

                                <!-- Tambah Pesanan Baru Button -->
                                <a href="pesanan.php" 
                                    class="bg-[#FF9B45] hover:bg-[#E88C3D] text-white text-xs font-semibold py-2.5 px-3 rounded-xl flex items-center justify-center gap-2 transition-all shadow-button-orange active:scale-95">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z"></path></svg>
                                    <span>Order Baru</span>
                                </a>
                            </div>

                            <!-- Info Box (Bordered, Highlighting Phone, Address, Notes) -->
                            <div class="border border-[#0288D1]/30 bg-[#F4FAFD]/50 rounded-xl p-4 text-xs space-y-2.5 mb-5">
                                <div class="flex items-center gap-2 text-[#1C1C1C]">
                                    <span class="text-sm">📞</span>
                                    <span id="detailNoWa" class="font-bold">0812-3456-7890</span>
                                </div>
                                <div class="flex items-start gap-2 text-[#544337]">
                                    <span class="text-sm shrink-0">📍</span>
                                    <span id="detailAlamat" class="leading-relaxed">Jl. Mawar No. 5, RT 02/04, Kel. Sukajadi, Kec. Sukajadi</span>
                                </div>
                                <div class="flex items-start gap-2 text-[#544337] pt-2 border-t border-[#0288D1]/15">
                                    <span class="text-sm shrink-0">📝</span>
                                    <span id="detailCatatan" class="leading-relaxed italic">Catatan: Pengiriman pagi selalu sebelum jam 10:00 WIB, roti minta diiris tipis.</span>
                                </div>
                            </div>
                        </div>

                        <!-- 3 Metric Badges at Bottom -->
                        <div class="grid grid-cols-3 gap-3 pt-4 border-t border-[#F0ECE9] text-center">
                            <div class="bg-[#FBF9F7] rounded-xl p-2.5 border border-[#F0ECE9]">
                                <h4 id="detailTotalOrder" class="text-base font-bold text-[#1C1C1C]">12</h4>
                                <p class="text-[11px] text-[#828282] mt-0.5 font-medium">Total Order</p>
                            </div>
                            <div class="bg-[#FBF9F7] rounded-xl p-2.5 border border-[#F0ECE9]">
                                <h4 id="detailAkumulasi" class="text-base font-bold text-[#1C1C1C]">Rp 560k</h4>
                                <p class="text-[11px] text-[#828282] mt-0.5 font-medium">Akumulasi</p>
                            </div>
                            <div class="bg-[#FBF9F7] rounded-xl p-2.5 border border-[#F0ECE9]">
                                <h4 id="detailAntrean" class="text-base font-bold text-[#27AE60]">1</h4>
                                <p class="text-[11px] text-[#828282] mt-0.5 font-medium">Antrean</p>
                            </div>
                        </div>
                    </div>

                    <!-- RIGHT CARD: RIWAYAT PESANAN -->
                    <div class="bg-white rounded-2xl border border-[#F0ECE9] p-6 shadow-card flex flex-col justify-between">
                        <div>
                            <!-- Header with Badge & View All Link -->
                            <div class="flex items-center justify-between pb-4 border-b border-[#F0ECE9] mb-4">
                                <div class="flex items-center gap-2">
                                    <h3 class="text-[#1C1C1C] font-bold text-sm md:text-base">Riwayat Pesanan</h3>
                                    <span id="historyCountBadge" class="bg-[#FF9B45]/15 text-[#924C00] text-xs font-bold px-2 py-0.5 rounded-full">
                                        12
                                    </span>
                                </div>
                                <a href="pesanan.php" id="viewAllOrdersLink" class="text-[#FF9B45] hover:text-[#E88C3D] text-xs font-semibold transition-colors flex items-center gap-1">
                                    <span>Lihat Semua 12 Pesanan</span>
                                    <span>→</span>
                                </a>
                            </div>

                            <!-- List of Order History Items -->
                            <div id="orderHistoryContainer" class="space-y-3.5">
                                <!-- Order Item 1 -->
                                <div class="bg-[#FBF9F7] rounded-xl p-3.5 border border-[#F0ECE9]">
                                    <div class="flex items-center justify-between text-xs mb-1">
                                        <div class="flex items-center gap-2">
                                            <span class="font-mono font-bold text-[#1C1C1C]">#ORD-20250926-01</span>
                                            <span class="bg-[#E2B93B]/20 text-[#886C12] text-[10px] font-semibold px-2 py-0.5 rounded-full">Belum Bayar</span>
                                        </div>
                                        <div class="text-right">
                                            <span class="font-bold text-[#1C1C1C] block">Rp 110.000</span>
                                            <span class="text-[#924C00] font-semibold text-[11px]">Siap Kirim &gt;</span>
                                        </div>
                                    </div>
                                    <p class="text-[11px] text-[#828282]">🕒 26 Sep 2025, 14:00</p>
                                    <p class="text-xs text-[#544337] mt-1 font-medium">20 pcs Roti Tawar Kupas Special, 1 mika Donat Coklat</p>
                                </div>

                                <!-- Order Item 2 -->
                                <div class="bg-[#FBF9F7] rounded-xl p-3.5 border border-[#F0ECE9]">
                                    <div class="flex items-center justify-between text-xs mb-1">
                                        <div class="flex items-center gap-2">
                                            <span class="font-mono font-bold text-[#1C1C1C]">#ORD-20250918-03</span>
                                            <span class="bg-[#27AE60]/15 text-[#1B7A43] text-[10px] font-semibold px-2 py-0.5 rounded-full">Lunas</span>
                                        </div>
                                        <div class="text-right">
                                            <span class="font-bold text-[#1C1C1C] block">Rp 175.000</span>
                                            <span class="text-[#27AE60] font-semibold text-[11px]">✓ Selesai Diterima &gt;</span>
                                        </div>
                                    </div>
                                    <p class="text-[11px] text-[#828282]">📅 18 Sep 2025</p>
                                    <p class="text-xs text-[#544337] mt-1 font-medium">10 pcs Roti Sisir Butter, 2 box Kue Bolu</p>
                                </div>

                                <!-- Order Item 3 -->
                                <div class="bg-[#FBF9F7] rounded-xl p-3.5 border border-[#F0ECE9]">
                                    <div class="flex items-center justify-between text-xs mb-1">
                                        <div class="flex items-center gap-2">
                                            <span class="font-mono font-bold text-[#1C1C1C]">#ORD-20250904-02</span>
                                            <span class="bg-[#27AE60]/15 text-[#1B7A43] text-[10px] font-semibold px-2 py-0.5 rounded-full">Lunas</span>
                                        </div>
                                        <div class="text-right">
                                            <span class="font-bold text-[#1C1C1C] block">Rp 120.000</span>
                                            <span class="text-[#27AE60] font-semibold text-[11px]">✓ Selesai Diterima &gt;</span>
                                        </div>
                                    </div>
                                    <p class="text-[11px] text-[#828282]">📅 04 Sep 2025</p>
                                    <p class="text-xs text-[#544337] mt-1 font-medium">15 pcs Roti Sobek Keju Coklat</p>
                                </div>
                            </div>
                        </div>

                        <!-- Footer Note -->
                        <div class="pt-4 mt-4 border-t border-[#F0ECE9] flex items-center justify-between text-xs text-[#828282]">
                            <span>Menampilkan 3 pesanan terbaru</span>
                            <span class="text-[#27AE60] font-medium">Sinkronisasi otomatis</span>
                        </div>
                    </div>

                </div>

            </main>
        </div>
    </div>

    <!-- ============================================================== -->
    <!-- MODAL POPOUT: TAMBAH / EDIT PELANGGAN BARU                     -->
    <!-- ============================================================== -->
    <div id="customerModal" class="fixed inset-0 z-50 hidden flex items-center justify-center p-4">
        <!-- Backdrop with blur -->
        <div onclick="closeCustomerModal()" class="fixed inset-0 bg-black/50 backdrop-blur-xs transition-opacity duration-200"></div>

        <!-- Modal Card Container -->
        <div class="bg-white rounded-2xl max-w-lg w-full p-6 sm:p-8 shadow-2xl relative z-10 animate-in fade-in zoom-in-95 duration-150 max-h-[92vh] overflow-y-auto">
            
            <!-- Header -->
            <div class="flex items-start justify-between pb-4 border-b border-[#F0ECE9] mb-5">
                <div>
                    <h3 id="modalTitle" class="text-xl font-bold text-[#1C1C1C]">Tambah Pelanggan Baru</h3>
                    <p class="text-[#828282] text-xs mt-1">Daftarkan profil kontak dan alamat pengiriman mitra roti.</p>
                </div>
                <button onclick="closeCustomerModal()" class="text-[#828282] hover:text-[#1C1C1C] p-1.5 rounded-lg hover:bg-[#F5F3F1] transition-colors" title="Tutup">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                </button>
            </div>

            <!-- Form -->
            <form id="customerForm" onsubmit="handleSaveCustomer(event)" class="space-y-4">
                <input type="hidden" id="editCustomerId" value="">

                <!-- Nama Lengkap -->
                <div>
                    <label class="block text-xs font-semibold text-[#333333] mb-1.5">Nama Lengkap Pelanggan <span class="text-[#EB5757]">*</span></label>
                    <input type="text" id="custNameInput" required placeholder="Contoh: Ibu Sari Dewi" 
                        class="w-full bg-white border border-[#E0E0E0] rounded-xl px-3.5 py-2.5 text-xs md:text-sm text-[#1C1C1C] focus:border-[#FF9B45] focus:outline-none transition-colors">
                </div>

                <!-- No WhatsApp & Tipe -->
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3.5">
                    <div>
                        <label class="block text-xs font-semibold text-[#333333] mb-1.5">No. WhatsApp / Telepon <span class="text-[#EB5757]">*</span></label>
                        <input type="text" id="custWaInput" required placeholder="Contoh: 0812-3456-7890" 
                            class="w-full bg-white border border-[#E0E0E0] rounded-xl px-3.5 py-2.5 text-xs md:text-sm text-[#1C1C1C] focus:border-[#FF9B45] focus:outline-none transition-colors">
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-[#333333] mb-1.5">Tipe Pelanggan</label>
                        <select id="custTypeInput" class="w-full bg-white border border-[#E0E0E0] rounded-xl px-3.5 py-2.5 text-xs md:text-sm text-[#1C1C1C] font-medium focus:border-[#FF9B45] focus:outline-none transition-colors cursor-pointer">
                            <option value="Pelanggan Reguler">Pelanggan Reguler</option>
                            <option value="Mitra Reseller">Mitra Reseller</option>
                        </select>
                    </div>
                </div>

                <!-- Alamat Pengiriman -->
                <div>
                    <label class="block text-xs font-semibold text-[#333333] mb-1.5">Alamat Pengiriman Lengkap <span class="text-[#EB5757]">*</span></label>
                    <textarea id="custAddressInput" rows="2" required placeholder="Contoh: Jl. Mawar No. 5, RT 02/04, Kel. Sukajadi, Kec. Sukajadi" 
                        class="w-full bg-white border border-[#E0E0E0] rounded-xl p-3 text-xs md:text-sm text-[#1C1C1C] focus:border-[#FF9B45] focus:outline-none transition-colors"></textarea>
                </div>

                <!-- Catatan Khusus Pengiriman / Preferensi -->
                <div>
                    <label class="block text-xs font-semibold text-[#333333] mb-1.5">Catatan Khusus Pengiriman / Preferensi</label>
                    <textarea id="custNotesInput" rows="2" placeholder="Contoh: Pengiriman pagi selalu sebelum jam 10:00 WIB, roti minta diiris tipis..." 
                        class="w-full bg-white border border-[#E0E0E0] rounded-xl p-3 text-xs md:text-sm text-[#1C1C1C] focus:border-[#FF9B45] focus:outline-none transition-colors"></textarea>
                </div>

                <!-- Footer Buttons -->
                <div class="flex items-center justify-end gap-3 pt-4 border-t border-[#F0ECE9] mt-6">
                    <button type="button" onclick="closeCustomerModal()" 
                        class="bg-white hover:bg-gray-100 text-[#544337] text-xs font-semibold px-4 py-2.5 rounded-xl border border-[#E0E0E0] transition-colors">
                        Batal
                    </button>
                    <button type="submit" 
                        class="bg-[#FF9B45] hover:bg-[#E88C3D] text-white text-xs font-semibold px-5 py-2.5 rounded-xl flex items-center gap-1.5 transition-all shadow-button-orange active:scale-95">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
                        <span id="saveBtnText">Simpan Pelanggan</span>
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- JAVASCRIPT LOGIC -->
    <script>
        // Master Dataset in JavaScript
        const customersData = <?= json_encode($pelanggan_list, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES) ?>;
        let selectedCustomerId = 1;

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

        // Live Filter Customers by Search Input and Status Dropdown
        function filterCustomers() {
            const query = document.getElementById('customerSearchInput').value.toLowerCase().trim();
            const statusFilter = document.getElementById('statusFilter').value;
            const rows = document.querySelectorAll('#customersTable tbody tr');
            let visibleCount = 0;

            rows.forEach(row => {
                const nameEl = row.querySelector('.customer-name');
                const phoneEl = row.querySelector('.customer-phone');
                const statusEl = row.querySelector('.customer-status');

                const nameText = nameEl ? nameEl.textContent.toLowerCase() : '';
                const phoneText = phoneEl ? phoneEl.textContent.toLowerCase() : '';
                const statusText = statusEl ? statusEl.textContent.trim() : '';

                const matchesQuery = nameText.includes(query) || phoneText.includes(query);
                const matchesStatus = (statusFilter === 'all') || (statusText === statusFilter);

                if (matchesQuery && matchesStatus) {
                    row.style.display = '';
                    visibleCount++;
                } else {
                    row.style.display = 'none';
                }
            });

            document.getElementById('customerCountBadge').textContent = visibleCount + ' Pelanggan';
        }

        // Select Customer and Update Bottom Panels
        function selectCustomer(id) {
            selectedCustomerId = id;
            const customer = customersData.find(c => c.id === id);
            if (!customer) return;

            // Highlight table row
            document.querySelectorAll('#customersTable tbody tr').forEach(r => {
                r.className = 'cursor-pointer transition-colors hover:bg-[#FAF8F5]';
                const noBadge = r.querySelector('td:first-child span');
                if (noBadge) {
                    noBadge.className = 'w-6 h-6 rounded-full bg-[#F0ECE9] text-[#544337] font-bold text-xs inline-flex items-center justify-center';
                }
            });

            const activeRow = document.getElementById('custRow-' + id);
            if (activeRow) {
                activeRow.className = 'cursor-pointer transition-colors bg-[#FFF8F2] border-l-4 border-l-[#FF9B45]';
                const noBadge = activeRow.querySelector('td:first-child span');
                if (noBadge) {
                    noBadge.className = 'w-6 h-6 rounded-full bg-[#924C00] text-white font-bold text-xs inline-flex items-center justify-center';
                }
            }

            // Update Left Customer Profile Card
            document.getElementById('detailAvatar').textContent = customer.avatar;
            document.getElementById('detailNama').textContent = customer.nama;
            document.getElementById('detailTipe').textContent = customer.tipe;
            document.getElementById('detailMemberSince').textContent = customer.member_since;
            document.getElementById('detailNoWa').textContent = customer.no_wa;
            document.getElementById('detailAlamat').textContent = customer.alamat;
            document.getElementById('detailCatatan').textContent = 'Catatan: ' + customer.catatan;
            document.getElementById('detailTotalOrder').textContent = customer.pesanan_count;
            document.getElementById('detailAkumulasi').textContent = 'Rp ' + Math.round(customer.total_raw / 1000) + 'k';
            document.getElementById('detailAntrean').textContent = customer.antrean;

            // Update WhatsApp Link
            document.getElementById('detailWaLink').href = 'https://wa.me/' + customer.wa_clean;

            // Update Right Card: Riwayat Pesanan
            document.getElementById('historyCountBadge').textContent = customer.pesanan_count;
            document.getElementById('viewAllOrdersLink').innerHTML = `<span>Lihat Semua ${customer.pesanan_count} Pesanan</span><span>→</span>`;

            const historyContainer = document.getElementById('orderHistoryContainer');
            historyContainer.innerHTML = '';

            if (customer.history && customer.history.length > 0) {
                customer.history.forEach(ord => {
                    const itemHtml = `
                        <div class="bg-[#FBF9F7] rounded-xl p-3.5 border border-[#F0ECE9]">
                            <div class="flex items-center justify-between text-xs mb-1">
                                <div class="flex items-center gap-2">
                                    <span class="font-mono font-bold text-[#1C1C1C]">${ord.order_no}</span>
                                    <span class="${ord.status_bayar_class} text-[10px] font-semibold px-2 py-0.5 rounded-full">${ord.status_bayar}</span>
                                </div>
                                <div class="text-right">
                                    <span class="font-bold text-[#1C1C1C] block">${ord.amount}</span>
                                    <span class="${ord.delivery_status_class} text-[11px]">${ord.delivery_status}</span>
                                </div>
                            </div>
                            <p class="text-[11px] text-[#828282]">${ord.date_time}</p>
                            <p class="text-xs text-[#544337] mt-1 font-medium">${ord.items}</p>
                        </div>
                    `;
                    historyContainer.insertAdjacentHTML('beforeend', itemHtml);
                });
            } else {
                historyContainer.innerHTML = `
                    <div class="p-6 text-center text-[#828282] text-xs">
                        Belum ada riwayat pesanan tercatat untuk pelanggan ini.
                    </div>
                `;
            }
        }

        // Reset Selection (defaults to first customer)
        function resetCustomerSelection() {
            selectCustomer(1);
        }

        // Modal Open / Close
        function openCustomerModal(id = null) {
            const modal = document.getElementById('customerModal');
            const modalTitle = document.getElementById('modalTitle');
            const saveBtnText = document.getElementById('saveBtnText');
            const form = document.getElementById('customerForm');

            form.reset();

            if (id) {
                const customer = customersData.find(c => c.id === id);
                if (customer) {
                    modalTitle.textContent = 'Edit Data Pelanggan';
                    saveBtnText.textContent = 'Perbarui Pelanggan';
                    document.getElementById('editCustomerId').value = customer.id;
                    document.getElementById('custNameInput').value = customer.nama;
                    document.getElementById('custWaInput').value = customer.no_wa;
                    document.getElementById('custTypeInput').value = customer.tipe;
                    document.getElementById('custAddressInput').value = customer.alamat;
                    document.getElementById('custNotesInput').value = customer.catatan;
                }
            } else {
                modalTitle.textContent = 'Tambah Pelanggan Baru';
                saveBtnText.textContent = 'Simpan Pelanggan';
                document.getElementById('editCustomerId').value = '';
            }

            modal.classList.remove('hidden');
            document.body.style.overflow = 'hidden';
        }

        function closeCustomerModal() {
            const modal = document.getElementById('customerModal');
            modal.classList.add('hidden');
            document.body.style.overflow = '';
        }

        // ESC Close
        document.addEventListener('keydown', function(e) {
            if (e.key === 'Escape') {
                closeCustomerModal();
            }
        });

        // Save Customer Form Handler
        function handleSaveCustomer(e) {
            e.preventDefault();
            const editId = document.getElementById('editCustomerId').value;
            const nama = document.getElementById('custNameInput').value;

            if (editId) {
                alert(`Data pelanggan "${nama}" berhasil diperbarui!`);
            } else {
                alert(`Pelanggan baru "${nama}" berhasil didaftarkan ke sistem!`);
            }
            closeCustomerModal();
        }

        // Delete Customer Handler
        function handleDeleteCustomer(id, nama) {
            if (confirm(`Apakah Anda yakin ingin menghapus pelanggan "${nama}" dari sistem?`)) {
                alert(`Pelanggan "${nama}" berhasil dihapus.`);
            }
        }
    </script>
</body>
</html>
