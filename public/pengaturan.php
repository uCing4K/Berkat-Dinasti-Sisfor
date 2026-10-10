<?php
session_start();

// Proteksi Autentikasi: Hanya user yang sudah login yang diizinkan mengakses halaman Pengaturan
if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true) {
    header("Location: login.php");
    exit();
}

// Coba koneksi database jika config tersedia
$configFile = __DIR__ . "/../config/config.php";
if (file_exists($configFile)) {
    require_once $configFile;
}

// Active Tab (profil atau user)
$active_tab = $_GET['tab'] ?? 'profil';

// Default Master User Sesuai Figma
$users_list = [
    [
        'id' => 1,
        'nama' => 'Admin Utama',
        'subtext' => 'admin@berkatdinasti.id',
        'avatar' => 'AU',
        'avatar_bg' => 'bg-[#3E2B21]',
        'username' => '@admin',
        'role' => 'Pemilik (Owner)',
        'role_class' => 'bg-[#3E2B21] text-white',
        'status' => 'Aktif',
        'status_class' => 'text-[#27AE60]',
        'dot_class' => 'bg-[#27AE60]',
        'is_locked' => true
    ],
    [
        'id' => 2,
        'nama' => 'Andi Susanto',
        'subtext' => 'Kasir Shift Pagi',
        'avatar' => 'AS',
        'avatar_bg' => 'bg-[#FF9B45]',
        'username' => '@andi.s',
        'role' => 'Karyawan',
        'role_class' => 'bg-[#FF9B45]/15 text-[#924C00]',
        'status' => 'Aktif',
        'status_class' => 'text-[#27AE60]',
        'dot_class' => 'bg-[#27AE60]',
        'is_locked' => false
    ],
    [
        'id' => 3,
        'nama' => 'Rina Dewi',
        'subtext' => 'Staf Produksi Bakery',
        'avatar' => 'RD',
        'avatar_bg' => 'bg-[#828282]',
        'username' => '@rina.d',
        'role' => 'Karyawan',
        'role_class' => 'bg-[#FF9B45]/15 text-[#924C00]',
        'status' => 'Nonaktif',
        'status_class' => 'text-[#828282]',
        'dot_class' => 'bg-[#828282]',
        'is_locked' => false
    ]
];
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pengaturan - Berkat Dinasti</title>
    
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
                        mono: ['Courier Prime', 'Courier New', 'monospace']
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

        /* Thermal Receipt Simulation Paper Styling */
        .thermal-paper {
            font-family: 'Courier New', Courier, monospace;
            background: #FFFFFF;
            position: relative;
        }
        .thermal-paper::before {
            content: "";
            position: absolute;
            top: -6px;
            left: 0;
            right: 0;
            height: 6px;
            background: radial-gradient(circle, transparent, transparent 50%, #FFFFFF 50%, #FFFFFF 100%);
            background-size: 10px 10px;
        }
        .thermal-paper::after {
            content: "";
            position: absolute;
            bottom: -6px;
            left: 0;
            right: 0;
            height: 6px;
            background: radial-gradient(circle, transparent, transparent 50%, #FFFFFF 50%, #FFFFFF 100%);
            background-size: 10px 10px;
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

                    <!-- Laporan -->
                    <a href="laporan.php" class="text-white/80 hover:text-white hover:bg-white/10 rounded-lg px-4 py-3 flex items-center gap-3 font-medium text-sm transition-colors">
                        <img src="assets/icons/nav_laporan.svg" alt="Laporan" class="w-5 h-5 opacity-90">
                        <span>Laporan</span>
                    </a>

                    <!-- Pengaturan (Active) -->
                    <a href="pengaturan.php" class="bg-[#FF9B45] text-white rounded-lg px-4 py-3 flex items-center gap-3 font-medium text-sm transition-all shadow-sm">
                        <img src="assets/icons/nav_pengaturan.svg" alt="Pengaturan" class="w-5 h-5">
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
                            <span class="text-[#924C00] font-medium" id="headerBreadcrumb">Pengaturan</span>
                        </div>
                        <!-- Section Heading -->
                        <h1 class="text-[#1B1C1B] font-bold text-base md:text-lg leading-tight mt-0.5" id="headerTitle">Pengaturan</h1>
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
                
                <!-- TOP STATUS CARD (MATCHING FIGMA SCREENSHOT) -->
                <div class="bg-white rounded-2xl border border-[#F0ECE9] p-4.5 mb-6 shadow-card flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
                    <div class="flex items-center gap-3.5">
                        <div class="w-10 h-10 rounded-xl bg-[#FF9B45]/15 flex items-center justify-center text-[#924C00] shrink-0">
                            <svg class="w-5 h-5 text-[#FF9B45]" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"></path></svg>
                        </div>
                        <div>
                            <h3 class="text-sm font-bold text-[#1C1C1C]">Status Operasional Toko: Buka Normal</h3>
                            <p class="text-xs text-[#828282] mt-0.5">Sistem terhubung ke 1 printer POS kasir • Sinkronisasi lokal aktif</p>
                        </div>
                    </div>

                    <!-- Right Status Badges -->
                    <div class="flex items-center gap-2.5">
                        <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-semibold bg-[#27AE60]/15 text-[#1B7A43]">
                            <span class="w-2 h-2 rounded-full bg-[#27AE60]"></span>
                            <span>Database Tersinkron</span>
                        </span>
                        <span class="px-2.5 py-1 rounded-full text-xs font-medium bg-[#F5F3F1] text-[#828282] border border-[#EBE8E5]">
                            Versi POS v2.4.1
                        </span>
                    </div>
                </div>

                <!-- TAB SWITCHER: PROFIL UMKM VS MANAJEMEN USER -->
                <div class="bg-[#F0ECE9]/60 p-1 rounded-xl max-w-fit flex items-center gap-1.5 mb-6">
                    <!-- Tab 1: Profil UMKM -->
                    <button onclick="switchSettingTab('profil')" id="tabBtnProfil" 
                        class="px-4 py-2 rounded-lg text-xs md:text-sm font-semibold flex items-center gap-2 transition-all <?= $active_tab !== 'user' ? 'bg-[#FF9B45] text-white shadow-xs' : 'text-[#828282] hover:text-[#1C1C1C]' ?>">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"></path></svg>
                        <span>Profil UMKM</span>
                    </button>

                    <!-- Tab 2: Manajemen User -->
                    <button onclick="switchSettingTab('user')" id="tabBtnUser" 
                        class="px-4 py-2 rounded-lg text-xs md:text-sm font-semibold flex items-center gap-2 transition-all <?= $active_tab === 'user' ? 'bg-[#FF9B45] text-white shadow-xs' : 'text-[#828282] hover:text-[#1C1C1C]' ?>">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"></path></svg>
                        <span>Manajemen User</span>
                        <span class="text-[10px] font-bold px-1.5 py-0.2 rounded-full <?= $active_tab === 'user' ? 'bg-white/20 text-white' : 'bg-[#E0E0E0] text-[#544337]' ?>">3 User</span>
                    </button>
                </div>

                <!-- ============================================================== -->
                <!-- TAB 1 CONTENT: PROFIL UMKM                                     -->
                <!-- ============================================================== -->
                <div id="tabContentProfil" class="<?= $active_tab === 'user' ? 'hidden' : '' ?>">
                    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                        
                        <!-- LEFT 2 COLS: FORM INFORMASI BISNIS & STRUK PRINTER -->
                        <div class="lg:col-span-2 space-y-6">
                            
                            <!-- CARD 1: INFORMASI BISNIS & TOKO -->
                            <div class="bg-white rounded-2xl border border-[#F0ECE9] p-6 shadow-card">
                                <div class="flex items-center justify-between pb-4 border-b border-[#F0ECE9] mb-5">
                                    <div>
                                        <h3 class="text-base font-bold text-[#1C1C1C]">Informasi Bisnis & Toko</h3>
                                        <p class="text-xs text-[#828282] mt-0.5">Kelola data identitas UMKM dan alamat resmi operasional bakery</p>
                                    </div>
                                    <span class="w-5 h-5 rounded-full bg-[#27AE60]/15 text-[#1B7A43] flex items-center justify-center text-xs font-bold">✓</span>
                                </div>

                                <!-- Logo Berkat Dinasti Box -->
                                <div class="bg-[#FBF9F7] rounded-xl p-4 border border-[#F0ECE9] flex flex-col sm:flex-row sm:items-center gap-4 mb-5">
                                    <div class="w-16 h-16 rounded-xl bg-white p-1 border border-[#F0ECE9] flex items-center justify-center shrink-0 shadow-2xs overflow-hidden">
                                        <img src="assets/images/logo.png" alt="Logo Berkat Dinasti" class="w-full h-full object-contain">
                                    </div>
                                    <div class="flex-1 min-w-0">
                                        <div class="flex items-center gap-2">
                                            <h4 class="text-sm font-bold text-[#1C1C1C]">Logo Berkat Dinasti</h4>
                                            <span class="w-4 h-4 rounded-full bg-[#27AE60] text-white text-[10px] flex items-center justify-center font-bold">✓</span>
                                        </div>
                                        <p class="text-xs text-[#828282] mt-0.5">Format PNG/JPG transparan resolusi minimum 500 × 500 px (Maks. 2MB)</p>
                                        <div class="flex items-center gap-2.5 mt-3">
                                            <button type="button" onclick="alert('Pilih gambar logo baru dari komputer...')" 
                                                class="bg-[#FF9B45] hover:bg-[#E88C3D] text-white text-xs font-semibold px-3 py-1.5 rounded-lg flex items-center gap-1.5 shadow-2xs transition-colors">
                                                <span>⬆</span>
                                                <span>Ubah Logo</span>
                                            </button>
                                            <button type="button" onclick="alert('Logo dikembalikan ke logo default.')" 
                                                class="bg-white hover:bg-gray-100 text-[#544337] text-xs font-medium px-3 py-1.5 rounded-lg border border-[#E0E0E0] transition-colors">
                                                Hapus
                                            </button>
                                        </div>
                                    </div>
                                </div>

                                <!-- Form Fields -->
                                <form onsubmit="handleSaveStoreInfo(event)" class="space-y-4">
                                    <!-- Nama Toko -->
                                    <div>
                                        <div class="flex items-center justify-between mb-1.5">
                                            <label class="block text-xs font-semibold text-[#333333]">Nama Resmi UMKM / Toko <span class="text-[#EB5757]">*</span></label>
                                            <span class="text-[11px] text-[#828282]">Ditampilkan di struk & faktur</span>
                                        </div>
                                        <div class="relative">
                                            <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-[#828282]">
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"></path></svg>
                                            </div>
                                            <input type="text" id="storeName" value="Berkat Dinasti Bakery" 
                                                class="w-full bg-white border border-[#E0E0E0] rounded-xl pl-10 pr-4 py-2.5 text-xs md:text-sm text-[#1C1C1C] font-semibold focus:border-[#FF9B45] focus:outline-none transition-colors">
                                        </div>
                                    </div>

                                    <!-- 2 Cols: Phone & WA -->
                                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                                        <div>
                                            <label class="block text-xs font-semibold text-[#333333] mb-1.5">No. Telepon Toko</label>
                                            <div class="relative">
                                                <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-[#828282]">
                                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"></path></svg>
                                                </div>
                                                <input type="text" id="storePhone" value="022-7218940" 
                                                    class="w-full bg-white border border-[#E0E0E0] rounded-xl pl-10 pr-4 py-2.5 text-xs md:text-sm text-[#1C1C1C] focus:border-[#FF9B45] focus:outline-none transition-colors">
                                            </div>
                                        </div>

                                        <div>
                                            <div class="flex items-center justify-between mb-1.5">
                                                <label class="block text-xs font-semibold text-[#333333]">WhatsApp Bisnis</label>
                                                <span class="text-[10px] font-semibold text-[#1B7A43] bg-[#27AE60]/15 px-1.5 py-0.2 rounded-full">● Aktif Kirim Nota</span>
                                            </div>
                                            <div class="relative">
                                                <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-[#27AE60]">
                                                    <span>📱</span>
                                                </div>
                                                <input type="text" id="storeWa" value="0812-3456-7890" 
                                                    class="w-full bg-white border border-[#E0E0E0] rounded-xl pl-10 pr-4 py-2.5 text-xs md:text-sm text-[#1C1C1C] focus:border-[#FF9B45] focus:outline-none transition-colors">
                                            </div>
                                        </div>
                                    </div>

                                    <!-- Alamat Lengkap Operasional Toko -->
                                    <div>
                                        <label class="block text-xs font-semibold text-[#333333] mb-1.5">Alamat Lengkap Operasional Toko</label>
                                        <textarea id="storeAddress" rows="2" 
                                            class="w-full bg-white border border-[#E0E0E0] rounded-xl p-3 text-xs md:text-sm text-[#1C1C1C] focus:border-[#FF9B45] focus:outline-none transition-colors">Jl. Pelindung Hewan No. 42, RT 03/RW 05, Kel. Pelindung Hewan, Kec. Astanaanyar</textarea>
                                    </div>

                                    <!-- 2 Cols: Provinsi & Kota -->
                                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                                        <div>
                                            <label class="block text-xs font-semibold text-[#333333] mb-1.5">Provinsi</label>
                                            <div class="relative">
                                                <select class="w-full appearance-none bg-white border border-[#E0E0E0] rounded-xl pl-3.5 pr-8 py-2.5 text-xs md:text-sm text-[#1C1C1C] font-medium focus:border-[#FF9B45] focus:outline-none transition-colors cursor-pointer">
                                                    <option>Jawa Barat</option>
                                                    <option>Jawa Timur</option>
                                                    <option>DKI Jakarta</option>
                                                </select>
                                                <div class="absolute inset-y-0 right-0 pr-3 flex items-center pointer-events-none text-[#828282]">
                                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path></svg>
                                                </div>
                                            </div>
                                        </div>

                                        <div>
                                            <label class="block text-xs font-semibold text-[#333333] mb-1.5">Kota / Kabupaten</label>
                                            <div class="relative">
                                                <select class="w-full appearance-none bg-white border border-[#E0E0E0] rounded-xl pl-3.5 pr-8 py-2.5 text-xs md:text-sm text-[#1C1C1C] font-medium focus:border-[#FF9B45] focus:outline-none transition-colors cursor-pointer">
                                                    <option>Kota Bandung</option>
                                                    <option>Kota Cimahi</option>
                                                    <option>Kabupaten Bandung</option>
                                                </select>
                                                <div class="absolute inset-y-0 right-0 pr-3 flex items-center pointer-events-none text-[#828282]">
                                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path></svg>
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                    <!-- 2 Cols: Kode Pos & Email -->
                                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                                        <div>
                                            <label class="block text-xs font-semibold text-[#333333] mb-1.5">Kode Pos</label>
                                            <input type="text" value="40243" 
                                                class="w-full bg-white border border-[#E0E0E0] rounded-xl px-3.5 py-2.5 text-xs md:text-sm text-[#1C1C1C] focus:border-[#FF9B45] focus:outline-none transition-colors">
                                        </div>

                                        <div>
                                            <label class="block text-xs font-semibold text-[#333333] mb-1.5">Email Bisnis</label>
                                            <div class="relative">
                                                <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-[#828282]">
                                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"></path></svg>
                                                </div>
                                                <input type="email" value="berkatdinasti.bakery@gmail.com" 
                                                    class="w-full bg-white border border-[#E0E0E0] rounded-xl pl-10 pr-4 py-2.5 text-xs md:text-sm text-[#1C1C1C] focus:border-[#FF9B45] focus:outline-none transition-colors">
                                            </div>
                                        </div>
                                    </div>
                                </form>
                            </div>

                            <!-- CARD 2: PENGATURAN STRUK PRINTER (THERMAL POS) -->
                            <div class="bg-white rounded-2xl border border-[#F0ECE9] p-6 shadow-card">
                                <div class="flex items-center justify-between pb-4 border-b border-[#F0ECE9] mb-5">
                                    <div class="flex items-center gap-2.5">
                                        <div class="w-6 h-6 rounded-md bg-[#FF9B45]/15 flex items-center justify-center text-[#924C00]">
                                            <span>🖨️</span>
                                        </div>
                                        <div>
                                            <h3 class="text-base font-bold text-[#1C1C1C]">Pengaturan Struk Printer (Thermal POS)</h3>
                                            <p class="text-xs text-[#828282] mt-0.5">Konfigurasi layout nota kasir dan teks footernya untuk printer thermal</p>
                                        </div>
                                    </div>
                                    <span class="bg-[#FF9B45]/15 text-[#924C00] text-xs font-semibold px-2.5 py-1 rounded-full">
                                        Bluetooth & USB
                                    </span>
                                </div>

                                <!-- Toggle Aktifkan Cetak Struk Otomatis -->
                                <div class="bg-[#FBF9F7] rounded-xl p-4 border border-[#F0ECE9] flex items-center justify-between mb-5">
                                    <div>
                                        <h4 class="text-xs font-bold text-[#1C1C1C]">Aktifkan Cetak Struk Otomatis</h4>
                                        <p class="text-[11px] text-[#828282] mt-0.5">Cetak slip pengiriman dan nota kasir langsung saat pesanan diselesaikan di sistem</p>
                                    </div>
                                    <!-- Toggle Switch -->
                                    <label class="relative inline-flex items-center cursor-pointer">
                                        <input type="checkbox" checked class="sr-only peer">
                                        <div class="w-11 h-6 bg-[#E0E0E0] peer-focus:outline-none rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-[#FF9B45]"></div>
                                    </label>
                                </div>

                                <!-- Pilihan Ukuran Kertas Thermal -->
                                <div class="mb-5">
                                    <label class="block text-xs font-semibold text-[#333333] mb-2">Pilihan Ukuran Kertas Thermal:</label>
                                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-3.5">
                                        <!-- Option 1: 58 mm -->
                                        <div class="border border-[#E0E0E0] rounded-xl p-3.5 flex items-center gap-3 cursor-pointer hover:border-[#FF9B45] transition-colors">
                                            <input type="radio" name="paperSize" value="58" class="text-[#FF9B45] focus:ring-[#FF9B45]">
                                            <div>
                                                <p class="text-xs font-bold text-[#1C1C1C]">58 mm (Mini Portable POS)</p>
                                                <p class="text-[11px] text-[#828282] mt-0.5">Lebar 32 karakter per baris</p>
                                            </div>
                                        </div>

                                        <!-- Option 2: 80 mm (Selected) -->
                                        <div class="border-2 border-[#FF9B45] bg-[#FFF8F2] rounded-xl p-3.5 flex items-center justify-between gap-3 cursor-pointer">
                                            <div class="flex items-center gap-3">
                                                <input type="radio" name="paperSize" value="80" checked class="text-[#FF9B45] focus:ring-[#FF9B45]">
                                                <div>
                                                    <p class="text-xs font-bold text-[#924C00]">80 mm (Standar Desktop Kasir)</p>
                                                    <p class="text-[11px] text-[#544337] mt-0.5">Lebar 48 karakter • Terpilih</p>
                                                </div>
                                            </div>
                                            <span class="text-sm">🧾</span>
                                        </div>
                                    </div>
                                </div>

                                <!-- Pesan Penutup (Footer Nota Kasir) -->
                                <div class="mb-5">
                                    <div class="flex items-center justify-between mb-1.5">
                                        <label class="block text-xs font-semibold text-[#333333]">Pesan Penutup (Footer Nota Kasir):</label>
                                        <span class="text-[11px] text-[#828282]">Maks. 140 karakter</span>
                                    </div>
                                    <textarea rows="2" id="footerReceiptInput" 
                                        class="w-full bg-white border border-[#E0E0E0] rounded-xl p-3 text-xs md:text-sm text-[#1C1C1C] focus:border-[#FF9B45] focus:outline-none transition-colors">Terima kasih telah berbelanja di Berkat Dinasti! Simpan struk ini sebagai bukti pembayaran yang sah.</textarea>
                                </div>

                                <!-- Test Print & Reset Actions -->
                                <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3 pt-4 border-t border-[#F0ECE9]">
                                    <div class="flex items-center gap-3">
                                        <button type="button" onclick="alert('Mencetak struk uji coba ke printer thermal (80mm)...')" 
                                            class="bg-[#F5F3F1] hover:bg-[#EBE8E5] text-[#1C1C1C] text-xs font-semibold px-4 py-2.5 rounded-xl border border-[#E0E0E0] flex items-center gap-2 transition-colors">
                                            <span>🖨️</span>
                                            <span>Test Print Nota Struk (80mm)</span>
                                        </button>
                                        <span class="text-[11px] text-[#828282] hidden md:inline">Terakhir diperbarui: Hari ini, 09:30 WIB</span>
                                    </div>

                                    <button type="button" onclick="alert('Pengaturan layout struk direset ke default pabrik.')" 
                                        class="text-[#EB5757] hover:underline text-xs font-medium flex items-center gap-1 self-start sm:self-auto">
                                        <span>↺</span>
                                        <span>Reset ke Default</span>
                                    </button>
                                </div>

                            </div>

                            <!-- Bottom Save Store Settings Button -->
                            <div class="flex justify-end">
                                <button type="button" onclick="handleSaveStoreInfo(event)" 
                                    class="bg-[#FF9B45] hover:bg-[#E88C3D] text-white font-bold text-sm px-6 py-3 rounded-xl flex items-center gap-2 shadow-button-orange active:scale-95 transition-all">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
                                    <span>Simpan Pengaturan Toko</span>
                                </button>
                            </div>

                        </div>

                        <!-- RIGHT 1 COL: SIMULASI CETAK NOTA 80MM & PANDUAN PENGATURAN -->
                        <div class="space-y-6">
                            
                            <!-- CARD 1: SIMULASI CETAK NOTA 80MM -->
                            <div class="bg-white rounded-2xl border border-[#F0ECE9] p-5 shadow-card">
                                <div class="flex items-center justify-between pb-3 border-b border-[#F0ECE9] mb-4">
                                    <div class="flex items-center gap-2 text-xs font-bold text-[#1C1C1C]">
                                        <span>🧾</span>
                                        <span>Simulasi Cetak Nota 80mm</span>
                                    </div>
                                    <span class="bg-[#F5F3F1] text-[#828282] text-[10px] font-bold px-2 py-0.5 rounded uppercase border border-[#EBE8E5]">
                                        THERMAL VIEW
                                    </span>
                                </div>

                                <!-- Receipt Paper Preview -->
                                <div class="thermal-paper bg-[#FCFCFA] border border-[#E0E0E0] rounded-lg p-4 text-[11px] leading-relaxed shadow-xs text-[#1C1C1C]">
                                    
                                    <!-- Store Header -->
                                    <div class="text-center pb-2 border-b border-dashed border-[#828282]">
                                        <p class="font-bold text-xs uppercase tracking-wider">BERKAT DINASTI BAKERY</p>
                                        <p class="text-[10px] text-[#544337]">Jl. Pelindung Hewan No. 42, Astanaanyar</p>
                                        <p class="text-[10px] text-[#544337]">Bandung • WA: 0812-3456-7890</p>
                                    </div>

                                    <!-- Meta Order -->
                                    <div class="py-2 border-b border-dashed border-[#828282] text-[10px] space-y-0.5">
                                        <div class="flex justify-between">
                                            <span>Nota: ORD-20250926-01</span>
                                            <span>26/09/25 10:15</span>
                                        </div>
                                        <div class="flex justify-between">
                                            <span>Kasir: Admin (Pemilik)</span>
                                            <span>Plg: Ibu Sari Dewi</span>
                                        </div>
                                    </div>

                                    <!-- Items Ordered -->
                                    <div class="py-2 border-b border-dashed border-[#828282] space-y-1.5 text-[10px]">
                                        <div class="flex justify-between">
                                            <span>1x Roti Sisir Mentega Spesial</span>
                                            <span class="font-bold">Rp 35.000</span>
                                        </div>
                                        <div class="flex justify-between">
                                            <span>2x Chiffon Pandan Keju</span>
                                            <span class="font-bold">Rp 80.000</span>
                                        </div>
                                        <div class="flex justify-between">
                                            <span>1x Roll Cokelat Mini (10x)</span>
                                            <span class="font-bold">Rp 120.000</span>
                                        </div>
                                    </div>

                                    <!-- Totals Calculation -->
                                    <div class="py-2 border-b border-dashed border-[#828282] text-[10px] space-y-1">
                                        <div class="flex justify-between text-[#828282]">
                                            <span>Subtotal:</span>
                                            <span>Rp 235.000</span>
                                        </div>
                                        <div class="flex justify-between text-[#27AE60]">
                                            <span>Diskon Member:</span>
                                            <span>- Rp 15.000</span>
                                        </div>
                                        <div class="flex justify-between font-bold text-xs pt-1 border-t border-dashed border-[#828282]/50">
                                            <span>TOTAL AKHIR:</span>
                                            <span class="text-[#924C00]">Rp 220.000</span>
                                        </div>
                                        <div class="flex justify-between text-[#544337]">
                                            <span>Bayar (QRIS Mandiri):</span>
                                            <span>Rp 220.000</span>
                                        </div>
                                        <div class="flex justify-between font-bold text-[#27AE60]">
                                            <span>Kembalian:</span>
                                            <span>Rp 0 (LUNAS)</span>
                                        </div>
                                    </div>

                                    <!-- Footer Message & Barcode -->
                                    <div class="pt-3 text-center text-[10px]">
                                        <p class="italic text-[#544337] mb-2 leading-tight">
                                            "Terima kasih telah berbelanja di Berkat Dinasti! Simpan struk ini sebagai bukti pembayaran yang sah."
                                        </p>
                                        <div class="font-mono text-center tracking-widest text-[#828282] text-[10px] my-1">
                                            ||||||||||||||||||||||||||||||||||||||||||||
                                        </div>
                                        <p class="text-[9px] text-[#828282]">www.berkatdinasti.id</p>
                                    </div>

                                </div>

                                <!-- Receipt Footer Action -->
                                <div class="flex items-center justify-between pt-3 mt-3 border-t border-[#F0ECE9] text-xs">
                                    <span class="text-[#828282]">Ukuran kertas: <strong class="text-[#1C1C1C]">80 mm</strong></span>
                                    <button onclick="window.print()" class="text-[#FF9B45] hover:text-[#E88C3D] font-bold">
                                        Cetak Uji Coba →
                                    </button>
                                </div>
                            </div>

                            <!-- CARD 2: PANDUAN PENGATURAN TOKO -->
                            <div class="bg-[#FBF9F7] rounded-2xl border border-[#F0ECE9] p-5 shadow-card">
                                <div class="flex items-center gap-2 mb-2 text-[#924C00] font-bold text-xs">
                                    <span>💡</span>
                                    <span>Panduan Pengaturan Toko</span>
                                </div>
                                <p class="text-xs text-[#544337] leading-relaxed">
                                    Perubahan pada profil UMKM ini langsung mempengaruhi kop faktur digital di menu Pesanan serta header nota transaksi yang dicetak kasir.
                                </p>
                            </div>

                        </div>

                    </div>
                </div>

                <!-- ============================================================== -->
                <!-- TAB 2 CONTENT: MANAJEMEN USER (MATCHING FIGMA SCREENSHOT 2)    -->
                <!-- ============================================================== -->
                <div id="tabContentUser" class="<?= $active_tab !== 'user' ? 'hidden' : '' ?>">
                    
                    <!-- Main Card: Table Pengguna Sistem -->
                    <div class="bg-white rounded-2xl border border-[#F0ECE9] p-6 shadow-card mb-6">
                        
                        <!-- Header with Add User Button -->
                        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 pb-5 border-b border-[#F0ECE9] mb-5">
                            <div>
                                <div class="flex items-center gap-2.5">
                                    <h3 class="text-base font-bold text-[#1C1C1C]">Daftar Pengguna Sistem</h3>
                                    <span class="bg-[#FF9B45]/15 text-[#924C00] text-xs font-semibold px-2.5 py-0.5 rounded-full">
                                        3 Pengguna
                                    </span>
                                </div>
                                <p class="text-xs text-[#828282] mt-0.5">Kelola akun kasir, staf bakery, serta hak akses pengelolaan pesanan pelanggan</p>
                            </div>

                            <!-- Button Tambah User Baru -->
                            <button onclick="openUserModal()" 
                                class="bg-[#FF9B45] hover:bg-[#E88C3D] text-white text-xs md:text-sm font-semibold px-4 py-2.5 rounded-xl flex items-center justify-center gap-2 transition-all shadow-button-orange active:scale-95 shrink-0">
                                <img src="assets/icons/user_plus.svg" alt="Tambah User" class="w-4 h-4">
                                <span>+ Tambah User Baru</span>
                            </button>
                        </div>

                        <!-- Users Table -->
                        <div class="overflow-x-auto">
                            <table class="w-full text-left border-collapse" id="usersTable">
                                <!-- Orange Header (Matching Figma) -->
                                <thead>
                                    <tr class="bg-[#FF9B45] text-white text-xs font-bold uppercase tracking-wider">
                                        <th class="py-3 px-4 min-w-[240px]">PENGGUNA</th>
                                        <th class="py-3 px-4 min-w-[140px]">USERNAME</th>
                                        <th class="py-3 px-4 min-w-[160px]">ROLE / HAK AKSES</th>
                                        <th class="py-3 px-4 min-w-[120px]">STATUS AKUN</th>
                                        <th class="py-3 px-4 text-center min-w-[130px]">AKSI</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-[#F0ECE9] text-xs md:text-sm">
                                    <?php foreach ($users_list as $u): ?>
                                        <tr class="hover:bg-[#FAF8F5] transition-colors">
                                            
                                            <!-- Pengguna (Avatar, Name, Subtext) -->
                                            <td class="py-4 px-4">
                                                <div class="flex items-center gap-3">
                                                    <div class="w-9 h-9 rounded-full <?= $u['avatar_bg'] ?> text-white font-bold text-xs flex items-center justify-center shrink-0 shadow-xs">
                                                        <?= htmlspecialchars($u['avatar']) ?>
                                                    </div>
                                                    <div>
                                                        <h4 class="font-bold text-[#1C1C1C] leading-tight"><?= htmlspecialchars($u['nama']) ?></h4>
                                                        <p class="text-xs text-[#828282] mt-0.5"><?= htmlspecialchars($u['subtext']) ?></p>
                                                    </div>
                                                </div>
                                            </td>

                                            <!-- Username -->
                                            <td class="py-4 px-4 font-mono font-medium text-[#544337]">
                                                <?= htmlspecialchars($u['username']) ?>
                                            </td>

                                            <!-- Role Badge -->
                                            <td class="py-4 px-4">
                                                <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-semibold <?= $u['role_class'] ?>">
                                                    <span class="w-1.5 h-1.5 rounded-full <?= $u['id'] === 1 ? 'bg-white' : 'bg-[#FF9B45]' ?>"></span>
                                                    <span><?= htmlspecialchars($u['role']) ?></span>
                                                </span>
                                            </td>

                                            <!-- Status Akun -->
                                            <td class="py-4 px-4">
                                                <span class="inline-flex items-center gap-1.5 font-semibold text-xs <?= $u['status_class'] ?>">
                                                    <span class="w-2 h-2 rounded-full <?= $u['dot_class'] ?>"></span>
                                                    <span><?= htmlspecialchars($u['status']) ?></span>
                                                </span>
                                            </td>

                                            <!-- Aksi -->
                                            <td class="py-4 px-4 text-center">
                                                <?php if ($u['is_locked']): ?>
                                                    <div class="flex items-center justify-center gap-2 text-xs">
                                                        <button onclick="openUserModal(<?= $u['id'] ?>)" title="Edit Profil" class="p-1.5 text-[#828282] hover:text-[#2F80ED] transition-colors">
                                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path></svg>
                                                        </button>
                                                        <span class="text-xs text-[#828282] italic">Terkunci</span>
                                                    </div>
                                                <?php elseif ($u['status'] === 'Nonaktif'): ?>
                                                    <div class="flex items-center justify-center gap-2 text-xs">
                                                        <button onclick="openUserModal(<?= $u['id'] ?>)" title="Edit" class="p-1.5 text-[#828282] hover:text-[#2F80ED] transition-colors">
                                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path></svg>
                                                        </button>
                                                        <button onclick="toggleUserStatus(<?= $u['id'] ?>, 'aktif')" class="text-xs font-semibold text-[#27AE60] hover:underline">
                                                            Aktifkan
                                                        </button>
                                                    </div>
                                                <?php else: ?>
                                                    <div class="flex items-center justify-center gap-2">
                                                        <button onclick="openUserModal(<?= $u['id'] ?>)" title="Edit Pengguna" class="p-1.5 text-[#828282] hover:text-[#2F80ED] rounded hover:bg-[#F5F3F1] transition-colors">
                                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path></svg>
                                                        </button>
                                                        <button onclick="handleDeleteUser(<?= $u['id'] ?>, '<?= htmlspecialchars($u['nama']) ?>')" title="Hapus Pengguna" class="p-1.5 text-[#828282] hover:text-[#EB5757] rounded hover:bg-[#FFF5F5] transition-colors">
                                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg>
                                                        </button>
                                                    </div>
                                                <?php endif; ?>
                                            </td>

                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>

                    </div>

                    <!-- Bottom 2 Hak Akses Guide Cards (Matching Figma Screenshot 2) -->
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        
                        <!-- Card 1: Hak Akses Pemilik -->
                        <div class="bg-white rounded-2xl border border-[#F0ECE9] p-5 shadow-card">
                            <div class="flex items-center gap-2 mb-2 text-[#3E2B21] font-bold text-sm">
                                <span>🛡️</span>
                                <h4>Hak Akses: Pemilik (Owner)</h4>
                            </div>
                            <p class="text-xs text-[#828282] leading-relaxed">
                                Akses penuh ke seluruh menu: Pengaturan Toko, Laporan Keuangan, Ekspor Data Excel, Manajemen Pengguna, dan Penghapusan Pesanan.
                            </p>
                        </div>

                        <!-- Card 2: Hak Akses Karyawan / Kasir -->
                        <div class="bg-white rounded-2xl border border-[#F0ECE9] p-5 shadow-card">
                            <div class="flex items-center gap-2 mb-2 text-[#D97706] font-bold text-sm">
                                <span>🏷️</span>
                                <h4>Hak Akses: Karyawan / Kasir</h4>
                            </div>
                            <p class="text-xs text-[#828282] leading-relaxed">
                                Hanya dapat membuat pesanan baru, mencetak nota transaksi kasir, serta melihat ketersediaan stok produk harian.
                            </p>
                        </div>

                    </div>

                </div>

            </main>
        </div>
    </div>

    <!-- ============================================================== -->
    <!-- MODAL POPOUT: TAMBAH / EDIT USER BARU                          -->
    <!-- ============================================================== -->
    <div id="userModal" class="fixed inset-0 z-50 hidden flex items-center justify-center p-4">
        <!-- Backdrop with blur -->
        <div onclick="closeUserModal()" class="fixed inset-0 bg-black/50 backdrop-blur-xs transition-opacity duration-200"></div>

        <!-- Modal Card Container -->
        <div class="bg-white rounded-2xl max-w-lg w-full p-6 sm:p-8 shadow-2xl relative z-10 animate-in fade-in zoom-in-95 duration-150 max-h-[92vh] overflow-y-auto">
            
            <!-- Header -->
            <div class="flex items-start justify-between pb-4 border-b border-[#F0ECE9] mb-5">
                <div>
                    <h3 id="userModalTitle" class="text-xl font-bold text-[#1C1C1C]">Tambah User Baru</h3>
                    <p class="text-[#828282] text-xs mt-1">Daftarkan akun kasir atau staf bakery untuk sistem Berkat Dinasti.</p>
                </div>
                <button onclick="closeUserModal()" class="text-[#828282] hover:text-[#1C1C1C] p-1.5 rounded-lg hover:bg-[#F5F3F1] transition-colors" title="Tutup">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                </button>
            </div>

            <!-- Form -->
            <form id="userForm" onsubmit="handleSaveUser(event)" class="space-y-4">
                <input type="hidden" id="editUserId" value="">

                <!-- Nama Lengkap -->
                <div>
                    <label class="block text-xs font-semibold text-[#333333] mb-1.5">Nama Lengkap <span class="text-[#EB5757]">*</span></label>
                    <input type="text" id="userNameInput" required placeholder="Contoh: Andi Susanto" 
                        class="w-full bg-white border border-[#E0E0E0] rounded-xl px-3.5 py-2.5 text-xs md:text-sm text-[#1C1C1C] focus:border-[#FF9B45] focus:outline-none transition-colors">
                </div>

                <!-- Username & Email -->
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3.5">
                    <div>
                        <label class="block text-xs font-semibold text-[#333333] mb-1.5">Username <span class="text-[#EB5757]">*</span></label>
                        <div class="relative">
                            <span class="absolute inset-y-0 left-0 pl-3.5 flex items-center text-[#828282] text-xs">@</span>
                            <input type="text" id="userUsernameInput" required placeholder="andi.s" 
                                class="w-full bg-white border border-[#E0E0E0] rounded-xl pl-8 pr-3.5 py-2.5 text-xs md:text-sm text-[#1C1C1C] font-mono focus:border-[#FF9B45] focus:outline-none transition-colors">
                        </div>
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-[#333333] mb-1.5">Email / Keterangan</label>
                        <input type="text" id="userEmailInput" placeholder="Contoh: Kasir Shift Pagi" 
                            class="w-full bg-white border border-[#E0E0E0] rounded-xl px-3.5 py-2.5 text-xs md:text-sm text-[#1C1C1C] focus:border-[#FF9B45] focus:outline-none transition-colors">
                    </div>
                </div>

                <!-- Password -->
                <div>
                    <label class="block text-xs font-semibold text-[#333333] mb-1.5">Password <span class="text-[#EB5757]">*</span></label>
                    <div class="relative">
                        <input type="password" id="userPasswordInput" placeholder="Minimal 6 karakter" 
                            class="w-full bg-white border border-[#E0E0E0] rounded-xl px-3.5 py-2.5 text-xs md:text-sm text-[#1C1C1C] focus:border-[#FF9B45] focus:outline-none transition-colors">
                        <button type="button" onclick="togglePasswordVisibility('userPasswordInput')" class="absolute inset-y-0 right-0 pr-3.5 flex items-center text-[#828282] hover:text-[#1C1C1C]">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path></svg>
                        </button>
                    </div>
                </div>

                <!-- Role & Status -->
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3.5">
                    <div>
                        <label class="block text-xs font-semibold text-[#333333] mb-1.5">Role / Hak Akses</label>
                        <select id="userRoleInput" class="w-full bg-white border border-[#E0E0E0] rounded-xl px-3.5 py-2.5 text-xs md:text-sm text-[#1C1C1C] font-semibold focus:border-[#FF9B45] focus:outline-none transition-colors cursor-pointer">
                            <option value="Karyawan">Karyawan / Kasir</option>
                            <option value="Pemilik (Owner)">Pemilik (Owner)</option>
                        </select>
                    </div>

                    <div>
                        <label class="block text-xs font-semibold text-[#333333] mb-1.5">Status Akun</label>
                        <select id="userStatusInput" class="w-full bg-white border border-[#E0E0E0] rounded-xl px-3.5 py-2.5 text-xs md:text-sm text-[#1C1C1C] font-semibold focus:border-[#FF9B45] focus:outline-none transition-colors cursor-pointer">
                            <option value="Aktif">Aktif</option>
                            <option value="Nonaktif">Nonaktif</option>
                        </select>
                    </div>
                </div>

                <!-- Footer Buttons -->
                <div class="flex items-center justify-end gap-3 pt-4 border-t border-[#F0ECE9] mt-6">
                    <button type="button" onclick="closeUserModal()" 
                        class="bg-white hover:bg-gray-100 text-[#544337] text-xs font-semibold px-4 py-2.5 rounded-xl border border-[#E0E0E0] transition-colors">
                        Batal
                    </button>
                    <button type="submit" 
                        class="bg-[#FF9B45] hover:bg-[#E88C3D] text-white text-xs font-semibold px-5 py-2.5 rounded-xl flex items-center gap-1.5 transition-all shadow-button-orange active:scale-95">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
                        <span id="userSaveBtnText">Simpan User</span>
                    </button>
                </div>
            </form>
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

        // Switch Setting Tabs (Profil vs Manajemen User)
        function switchSettingTab(tab) {
            const tabContentProfil = document.getElementById('tabContentProfil');
            const tabContentUser = document.getElementById('tabContentUser');
            const tabBtnProfil = document.getElementById('tabBtnProfil');
            const tabBtnUser = document.getElementById('tabBtnUser');
            const headerBreadcrumb = document.getElementById('headerBreadcrumb');
            const headerTitle = document.getElementById('headerTitle');

            if (tab === 'user') {
                tabContentProfil.classList.add('hidden');
                tabContentUser.classList.remove('hidden');

                tabBtnUser.className = 'px-4 py-2 rounded-lg text-xs md:text-sm font-semibold flex items-center gap-2 transition-all bg-[#FF9B45] text-white shadow-xs';
                tabBtnProfil.className = 'px-4 py-2 rounded-lg text-xs md:text-sm font-semibold flex items-center gap-2 transition-all text-[#828282] hover:text-[#1C1C1C]';

                headerBreadcrumb.textContent = 'Pengaturan';
                headerTitle.textContent = 'Manajemen User';
            } else {
                tabContentUser.classList.add('hidden');
                tabContentProfil.classList.remove('hidden');

                tabBtnProfil.className = 'px-4 py-2 rounded-lg text-xs md:text-sm font-semibold flex items-center gap-2 transition-all bg-[#FF9B45] text-white shadow-xs';
                tabBtnUser.className = 'px-4 py-2 rounded-lg text-xs md:text-sm font-semibold flex items-center gap-2 transition-all text-[#828282] hover:text-[#1C1C1C]';

                headerBreadcrumb.textContent = 'Pengaturan';
                headerTitle.textContent = 'Pengaturan Toko';
            }
        }

        // Save Store Info Form Handler
        function handleSaveStoreInfo(e) {
            if (e) e.preventDefault();
            const storeName = document.getElementById('storeName').value;
            alert(`Pengaturan profil "${storeName}" dan layout struk kasir berhasil disimpan!`);
        }

        // User Modal Open / Close
        function openUserModal(id = null) {
            const modal = document.getElementById('userModal');
            const modalTitle = document.getElementById('userModalTitle');
            const saveBtnText = document.getElementById('userSaveBtnText');
            const form = document.getElementById('userForm');

            form.reset();

            if (id === 1) {
                modalTitle.textContent = 'Edit Akun Admin Utama';
                saveBtnText.textContent = 'Perbarui Admin';
                document.getElementById('editUserId').value = 1;
                document.getElementById('userNameInput').value = 'Admin Utama';
                document.getElementById('userUsernameInput').value = 'admin';
                document.getElementById('userEmailInput').value = 'admin@berkatdinasti.id';
                document.getElementById('userRoleInput').value = 'Pemilik (Owner)';
                document.getElementById('userStatusInput').value = 'Aktif';
            } else if (id === 2) {
                modalTitle.textContent = 'Edit Akun Kasir';
                saveBtnText.textContent = 'Perbarui User';
                document.getElementById('editUserId').value = 2;
                document.getElementById('userNameInput').value = 'Andi Susanto';
                document.getElementById('userUsernameInput').value = 'andi.s';
                document.getElementById('userEmailInput').value = 'Kasir Shift Pagi';
                document.getElementById('userRoleInput').value = 'Karyawan';
                document.getElementById('userStatusInput').value = 'Aktif';
            } else if (id === 3) {
                modalTitle.textContent = 'Edit Akun Staf Bakery';
                saveBtnText.textContent = 'Perbarui User';
                document.getElementById('editUserId').value = 3;
                document.getElementById('userNameInput').value = 'Rina Dewi';
                document.getElementById('userUsernameInput').value = 'rina.d';
                document.getElementById('userEmailInput').value = 'Staf Produksi Bakery';
                document.getElementById('userRoleInput').value = 'Karyawan';
                document.getElementById('userStatusInput').value = 'Nonaktif';
            } else {
                modalTitle.textContent = 'Tambah User Baru';
                saveBtnText.textContent = 'Simpan User';
                document.getElementById('editUserId').value = '';
            }

            modal.classList.remove('hidden');
            document.body.style.overflow = 'hidden';
        }

        function closeUserModal() {
            const modal = document.getElementById('userModal');
            modal.classList.add('hidden');
            document.body.style.overflow = '';
        }

        // ESC Close
        document.addEventListener('keydown', function(e) {
            if (e.key === 'Escape') {
                closeUserModal();
            }
        });

        // Toggle Password Visibility
        function togglePasswordVisibility(inputId) {
            const input = document.getElementById(inputId);
            if (input.type === 'password') {
                input.type = 'text';
            } else {
                input.type = 'password';
            }
        }

        // Save User Handler
        function handleSaveUser(e) {
            e.preventDefault();
            const editId = document.getElementById('editUserId').value;
            const nama = document.getElementById('userNameInput').value;

            if (editId) {
                alert(`Data pengguna "${nama}" berhasil diperbarui!`);
            } else {
                alert(`Pengguna baru "${nama}" berhasil didaftarkan ke sistem Berkat Dinasti!`);
            }
            closeUserModal();
        }

        // Toggle User Status
        function toggleUserStatus(id, newStatus) {
            alert(`Status pengguna berhasil diubah menjadi ${newStatus}.`);
            location.reload();
        }

        // Delete User Handler
        function handleDeleteUser(id, nama) {
            if (confirm(`Apakah Anda yakin ingin menghapus akun pengguna "${nama}"?`)) {
                alert(`Pengguna "${nama}" berhasil dihapus dari sistem.`);
            }
        }
    </script>
</body>
</html>
