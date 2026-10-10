<?php
session_start();

// Proteksi Autentikasi: Hanya user yang sudah login yang diizinkan mengakses halaman Produk
if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true) {
    header("Location: login.php");
    exit();
}

// Coba koneksi database jika config tersedia
$configFile = __DIR__ . "/../config/config.php";
if (file_exists($configFile)) {
    require_once $configFile;
}

$db_connected = false;
if (defined('DB_HOST') && defined('DB_USER') && defined('DB_PASS') && defined('DB_NAME')) {
    mysqli_report(MYSQLI_REPORT_OFF);
    $koneksi = @mysqli_connect(DB_HOST, DB_USER, DB_PASS, DB_NAME);
    if ($koneksi) {
        $db_connected = true;
    }
}

// Daftar Produk Default Sesuai Desain Figma (Katalog Bakery Berkat Dinasti)
$initial_products = [
    [
        'id' => 1,
        'sku' => 'BD-RT-01',
        'name' => 'Roti Tawar Kupas Special',
        'category' => 'Roti',
        'packaging_type' => 'Satuan',
        'packaging_label' => 'Satuan',
        'packaging_badge_class' => 'bg-[#F5F3F1] text-[#544337] border-[#EBE8E5]',
        'packaging_icon' => 'satuan',
        'min_order' => '5 pcs',
        'price' => 5000,
        'price_unit' => 'pcs',
        'status' => 'Aktif',
        'description' => 'Roti tawar kupas lembut dengan aroma butter harum.'
    ],
    [
        'id' => 2,
        'sku' => 'BD-DN-01',
        'name' => 'Donat Coklat Meises',
        'category' => 'Donat',
        'packaging_type' => 'Mika',
        'packaging_label' => 'Mika (12 pcs)',
        'packaging_badge_class' => 'bg-[#EBF3FC] text-[#2F80ED] border-[#D5E6F9]',
        'packaging_icon' => 'mika',
        'min_order' => '1 mika (12 pcs)',
        'price' => 45000,
        'price_unit' => 'mika',
        'status' => 'Aktif',
        'description' => 'Donat empuk bertabur meises coklat premium.'
    ],
    [
        'id' => 3,
        'sku' => 'BD-RT-02',
        'name' => 'Roti Sisir Mentega Special',
        'category' => 'Roti',
        'packaging_type' => 'Kardus',
        'packaging_label' => 'Kardus (20 pcs)',
        'packaging_badge_class' => 'bg-[#FEF6EE] text-[#D97706] border-[#FDE6D2]',
        'packaging_icon' => 'kardus',
        'min_order' => '1 kardus',
        'price' => 80000,
        'price_unit' => 'kardus',
        'status' => 'Aktif',
        'description' => 'Roti sisir legendaris dengan olesan mentega manis gurih.'
    ],
    [
        'id' => 4,
        'sku' => 'BD-KP-01',
        'name' => 'Kue Bolu Gulung Pandan',
        'category' => 'Kue & Pastry',
        'packaging_type' => 'Satuan',
        'packaging_label' => 'Satuan',
        'packaging_badge_class' => 'bg-[#F5F3F1] text-[#544337] border-[#EBE8E5]',
        'packaging_icon' => 'satuan',
        'min_order' => '10 pcs',
        'price' => 8500,
        'price_unit' => 'pcs',
        'status' => 'Nonaktif',
        'description' => 'Bolu gulung aroma pandan asli dengan selai srikaya.'
    ],
    [
        'id' => 5,
        'sku' => 'BD-KP-02',
        'name' => 'Chiffon Cake Keju (16cm)',
        'category' => 'Kue & Pastry',
        'packaging_type' => 'Mika',
        'packaging_label' => 'Mika (1 pcs)',
        'packaging_badge_class' => 'bg-[#EBF3FC] text-[#2F80ED] border-[#D5E6F9]',
        'packaging_icon' => 'mika',
        'min_order' => '2 box',
        'price' => 45000,
        'price_unit' => 'box',
        'status' => 'Aktif',
        'description' => 'Chiffon cake ekstra lembut dengan taburan parutan keju cheddar.'
    ],
    [
        'id' => 6,
        'sku' => 'BD-RT-03',
        'name' => 'Roti Sobek Manis 3 Rasa',
        'category' => 'Roti',
        'packaging_type' => 'Kardus',
        'packaging_label' => 'Kardus (10 pcs)',
        'packaging_badge_class' => 'bg-[#FEF6EE] text-[#D97706] border-[#FDE6D2]',
        'packaging_icon' => 'kardus',
        'min_order' => '2 kardus',
        'price' => 65000,
        'price_unit' => 'kardus',
        'status' => 'Aktif',
        'description' => 'Roti sobek isi cokelat, keju, dan selai nanas segar.'
    ],
    [
        'id' => 7,
        'sku' => 'BD-SB-01',
        'name' => 'Croissant Butter Flaky',
        'category' => 'Snack Box',
        'packaging_type' => 'Satuan',
        'packaging_label' => 'Satuan',
        'packaging_badge_class' => 'bg-[#F5F3F1] text-[#544337] border-[#EBE8E5]',
        'packaging_icon' => 'satuan',
        'min_order' => '15 pcs',
        'price' => 12000,
        'price_unit' => 'pcs',
        'status' => 'Aktif',
        'description' => 'Pastry croissant renyah berlapis dengan butter New Zealand.'
    ]
];

// Inisialisasi ke session agar produk yang baru ditambah dapat langsung muncul
if (!isset($_SESSION['product_list'])) {
    $_SESSION['product_list'] = $initial_products;
}

// Handle Form POST Tambah Produk
$toast_message = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'add_product') {
    $nama = trim($_POST['nama_produk'] ?? '');
    $kategori = trim($_POST['kategori'] ?? 'Roti');
    $harga_raw = str_replace(['.', ',', ' '], '', $_POST['harga'] ?? '0');
    $harga = (float)$harga_raw;
    $kemasan = trim($_POST['kemasan'] ?? 'Mika');
    $isi = (int)($_POST['isi'] ?? 1);
    $moq = (int)($_POST['moq'] ?? 1);
    $status = isset($_POST['status']) && $_POST['status'] === 'on' ? 'Aktif' : 'Nonaktif';
    $deskripsi = trim($_POST['deskripsi'] ?? '');

    $packaging_label = $kemasan;
    $packaging_badge_class = 'bg-[#F5F3F1] text-[#544337] border-[#EBE8E5]';
    $packaging_icon = 'satuan';
    $unit = 'pcs';

    if ($kemasan === 'Mika') {
        $packaging_label = "Mika ({$isi} pcs)";
        $packaging_badge_class = 'bg-[#EBF3FC] text-[#2F80ED] border-[#D5E6F9]';
        $packaging_icon = 'mika';
        $unit = 'mika';
    } elseif ($kemasan === 'Kardus') {
        $packaging_label = "Kardus ({$isi} pcs)";
        $packaging_badge_class = 'bg-[#FEF6EE] text-[#D97706] border-[#FDE6D2]';
        $packaging_icon = 'kardus';
        $unit = 'kardus';
    }

    $new_item = [
        'id' => count($_SESSION['product_list']) + 1,
        'sku' => 'BD-' . strtoupper(substr($kategori, 0, 2)) . '-' . sprintf('%02d', count($_SESSION['product_list']) + 1),
        'name' => $nama,
        'category' => $kategori,
        'packaging_type' => $kemasan,
        'packaging_label' => $packaging_label,
        'packaging_badge_class' => $packaging_badge_class,
        'packaging_icon' => $packaging_icon,
        'min_order' => $moq . ' ' . $unit,
        'price' => $harga,
        'price_unit' => $unit,
        'status' => $status,
        'description' => $deskripsi
    ];

    // Tambah di awal list
    array_unshift($_SESSION['product_list'], $new_item);
    $toast_message = 'Produk "' . htmlspecialchars($nama) . '" berhasil ditambahkan ke katalog!';
}

$products = $_SESSION['product_list'];

// Hitung data ringkasan KPI
$total_katalog = count($products);
$produk_aktif = count(array_filter($products, fn($p) => $p['status'] === 'Aktif'));
$varian_kemasan = count(array_unique(array_column($products, 'packaging_type')));
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Produk - Berkat Dinasti</title>
    
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

                    <!-- Produk (Active) -->
                    <a href="produk.php" class="bg-[#FF9B45] text-white rounded-lg px-4 py-3 flex items-center gap-3 font-medium text-sm transition-all shadow-sm">
                        <img src="assets/icons/nav_produk.svg" alt="Produk" class="w-5 h-5">
                        <span>Produk</span>
                    </a>

                    <!-- Pesanan -->
                    <a href="#" class="text-white/80 hover:text-white hover:bg-white/10 rounded-lg px-4 py-3 flex items-center gap-3 font-medium text-sm transition-colors">
                        <img src="assets/icons/nav_pesanan.svg" alt="Pesanan" class="w-5 h-5 opacity-90">
                        <span>Pesanan</span>
                    </a>

                    <!-- Pelanggan -->
                    <a href="#" class="text-white/80 hover:text-white hover:bg-white/10 rounded-lg px-4 py-3 flex items-center gap-3 font-medium text-sm transition-colors">
                        <img src="assets/icons/nav_pelanggan.svg" alt="Pelanggan" class="w-5 h-5 opacity-90">
                        <span>Pelanggan</span>
                    </a>

                    <!-- Laporan -->
                    <a href="#" class="text-white/80 hover:text-white hover:bg-white/10 rounded-lg px-4 py-3 flex items-center gap-3 font-medium text-sm transition-colors">
                        <img src="assets/icons/nav_laporan.svg" alt="Laporan" class="w-5 h-5 opacity-90">
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
                            <a href="produk.php" class="text-[#544337] hover:underline">Produk</a>
                            <span class="text-[#DBC2B2]">/</span>
                            <span class="text-[#924C00] font-medium">Daftar Produk</span>
                        </div>
                        <!-- Section Heading -->
                        <h1 class="text-[#1B1C1B] font-bold text-base md:text-lg leading-tight mt-0.5">Daftar Produk</h1>
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
                
                <!-- Notifikasi Toast Sukses Tambah Produk -->
                <?php if (!empty($toast_message)): ?>
                    <div id="toastNotification" class="mb-6 bg-emerald-50 border border-emerald-200 text-emerald-800 px-4 py-3 rounded-xl flex items-center justify-between shadow-xs animate-in fade-in slide-in-from-top-2">
                        <div class="flex items-center gap-2.5">
                            <svg class="w-5 h-5 text-emerald-600 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                            <span class="text-sm font-medium"><?= $toast_message ?></span>
                        </div>
                        <button onclick="document.getElementById('toastNotification').remove()" class="text-emerald-700 hover:text-emerald-900 p-1">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                        </button>
                    </div>
                <?php endif; ?>

                <!-- TOP SECTION: GREETING & 3 STAT CARDS -->
                <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between gap-6 mb-6">
                    <div>
                        <h2 class="text-[#1B1C1B] text-2xl font-bold tracking-tight">Daftar Produk Bakery</h2>
                        <p class="text-[#828282] text-sm mt-1 max-w-xl">Kelola katalog roti, donat, kue, varian kemasan, dan batas minimal pemesanan produksi.</p>
                    </div>

                    <!-- 3 KPI Cards Cluster -->
                    <div class="grid grid-cols-1 sm:grid-cols-3 gap-3.5 shrink-0">
                        <!-- Card 1: Total Produk -->
                        <div class="bg-white rounded-xl p-3.5 shadow-card border border-[#F0ECE9] relative overflow-hidden flex items-center justify-between min-w-[150px]">
                            <div class="absolute left-0 top-0 bottom-0 w-1 bg-[#FF9B45]"></div>
                            <div class="pl-2">
                                <p class="text-[#828282] text-[11px] font-semibold uppercase tracking-wider">TOTAL PRODUK</p>
                                <p class="text-[#1C1C1C] text-lg font-bold leading-tight mt-0.5"><?= $total_katalog ?> Katalog</p>
                            </div>
                            <div class="w-9 h-9 rounded-lg bg-[#FF9B45]/15 flex items-center justify-center text-[#FF9B45] shrink-0 ml-3">
                                <img src="assets/icons/orders_clipboard.svg" alt="Total" class="w-4 h-4">
                            </div>
                        </div>

                        <!-- Card 2: Produk Aktif -->
                        <div class="bg-white rounded-xl p-3.5 shadow-card border border-[#F0ECE9] relative overflow-hidden flex items-center justify-between min-w-[150px]">
                            <div class="absolute left-0 top-0 bottom-0 w-1 bg-[#27AE60]"></div>
                            <div class="pl-2">
                                <p class="text-[#828282] text-[11px] font-semibold uppercase tracking-wider">PRODUK AKTIF</p>
                                <p class="text-[#1C1C1C] text-lg font-bold leading-tight mt-0.5"><?= $produk_aktif ?> Siap Pesan</p>
                            </div>
                            <div class="w-9 h-9 rounded-lg bg-[#27AE60]/15 flex items-center justify-center text-[#27AE60] shrink-0 ml-3">
                                <img src="assets/icons/check_circle.svg" alt="Aktif" class="w-4 h-4">
                            </div>
                        </div>

                        <!-- Card 3: Varian Kemasan -->
                        <div class="bg-white rounded-xl p-3.5 shadow-card border border-[#F0ECE9] relative overflow-hidden flex items-center justify-between min-w-[150px]">
                            <div class="absolute left-0 top-0 bottom-0 w-1 bg-[#E2B93B]"></div>
                            <div class="pl-2">
                                <p class="text-[#828282] text-[11px] font-semibold uppercase tracking-wider">VARIAN KEMASAN</p>
                                <p class="text-[#1C1C1C] text-lg font-bold leading-tight mt-0.5"><?= $varian_kemasan ?> Tipe Box/Pcs</p>
                            </div>
                            <div class="w-9 h-9 rounded-lg bg-[#E2B93B]/15 flex items-center justify-center text-[#E2B93B] shrink-0 ml-3">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"></path></svg>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- CONTROLS & FILTER BAR -->
                <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between gap-4 mb-6">
                    <!-- Left Filters: Search, Kategori, Status -->
                    <div class="flex flex-wrap items-center gap-3">
                        <!-- Search Box -->
                        <div class="relative w-full sm:w-64 md:w-72">
                            <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none">
                                <img src="assets/icons/search.svg" alt="Cari" class="w-4 h-4 opacity-70">
                            </div>
                            <input type="text" id="searchInput" onkeyup="filterProducts()" placeholder="Cari nama produk, SKU..." 
                                class="w-full bg-white border border-[#E0E0E0] rounded-lg pl-10 pr-3.5 py-2.5 text-sm text-[#1C1C1C] placeholder-[#828282] focus:outline-none focus:border-[#FF9B45] focus:ring-1 focus:ring-[#FF9B45] transition-colors shadow-2xs">
                        </div>

                        <!-- Dropdown Kategori -->
                        <div class="relative">
                            <select id="categoryFilter" onchange="filterProducts()" 
                                class="appearance-none bg-white border border-[#E0E0E0] rounded-lg pl-3.5 pr-9 py-2.5 text-sm text-[#1C1C1C] font-medium focus:outline-none focus:border-[#FF9B45] cursor-pointer transition-colors shadow-2xs">
                                <option value="">Semua Kategori</option>
                                <option value="Roti">Roti</option>
                                <option value="Donat">Donat</option>
                                <option value="Kue & Pastry">Kue & Pastry</option>
                                <option value="Snack Box">Snack Box</option>
                            </select>
                            <div class="absolute inset-y-0 right-0 pr-3 flex items-center pointer-events-none text-[#828282]">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path></svg>
                            </div>
                        </div>

                        <!-- Dropdown Status -->
                        <div class="relative">
                            <select id="statusFilter" onchange="filterProducts()" 
                                class="appearance-none bg-white border border-[#E0E0E0] rounded-lg pl-3.5 pr-9 py-2.5 text-sm text-[#1C1C1C] font-medium focus:outline-none focus:border-[#FF9B45] cursor-pointer transition-colors shadow-2xs">
                                <option value="">Semua Status</option>
                                <option value="Aktif">Aktif</option>
                                <option value="Nonaktif">Nonaktif</option>
                            </select>
                            <div class="absolute inset-y-0 right-0 pr-3 flex items-center pointer-events-none text-[#828282]">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path></svg>
                            </div>
                        </div>
                    </div>

                    <!-- Right Buttons: Ekspor Data & Tambah Produk -->
                    <div class="flex items-center gap-3 shrink-0">
                        <!-- Ekspor Data -->
                        <button onclick="handleExportData()" class="bg-white hover:bg-[#F5F3F1] border border-[#E0E0E0] text-[#1B1C1B] text-sm font-medium px-4 py-2.5 rounded-lg flex items-center gap-2 transition-colors shadow-2xs">
                            <img src="assets/icons/download.svg" alt="Ekspor" class="w-4 h-4">
                            <span>Ekspor Data</span>
                        </button>

                        <!-- Tambah Produk Button (Triggers Modal) -->
                        <button onclick="openProductModal()" class="bg-[#FF9B45] hover:bg-[#E88C3D] text-white text-sm font-semibold px-4 py-2.5 rounded-lg flex items-center gap-2 transition-all shadow-button-orange active:scale-95">
                            <img src="assets/icons/plus.svg" alt="Tambah" class="w-4 h-4">
                            <span>+ Tambah Produk</span>
                        </button>
                    </div>
                </div>

                <!-- TABLE CARD (FULL WIDTH) -->
                <div class="bg-white rounded-xl shadow-card border border-[#F0ECE9] overflow-hidden mb-6">
                    <div class="overflow-x-auto">
                        <table class="w-full text-left border-collapse" id="productsTable">
                            <thead>
                                <tr class="bg-[#FF9B45] text-white text-xs font-bold uppercase tracking-wider">
                                    <th class="px-5 py-4 w-14 text-center">NO</th>
                                    <th class="px-5 py-4">NAMA PRODUK</th>
                                    <th class="px-5 py-4">KATEGORI</th>
                                    <th class="px-5 py-4">KEMASAN</th>
                                    <th class="px-5 py-4">MINIMAL ORDER</th>
                                    <th class="px-5 py-4">HARGA JUAL</th>
                                    <th class="px-5 py-4">STATUS</th>
                                    <th class="px-5 py-4 text-center">AKSI</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-[#F0ECE9] text-sm" id="productsTableBody">
                                <?php foreach ($products as $idx => $prod): ?>
                                    <tr class="<?= $idx % 2 === 1 ? 'bg-[#FFF8F2]' : 'bg-white' ?> hover:bg-[#FFF0E0] transition-colors product-row"
                                        data-name="<?= strtolower(htmlspecialchars($prod['name'])) ?>"
                                        data-sku="<?= strtolower(htmlspecialchars($prod['sku'])) ?>"
                                        data-category="<?= htmlspecialchars($prod['category']) ?>"
                                        data-status="<?= htmlspecialchars($prod['status']) ?>">
                                        
                                        <!-- No -->
                                        <td class="px-5 py-4 text-center text-[#828282] font-medium row-number">
                                            <?= $idx + 1 ?>
                                        </td>

                                        <!-- Nama Produk -->
                                        <td class="px-5 py-4 text-[#1B1C1B] font-semibold whitespace-nowrap">
                                            <?= htmlspecialchars($prod['name']) ?>
                                        </td>

                                        <!-- Kategori -->
                                        <td class="px-5 py-4 text-[#544337] whitespace-nowrap">
                                            <div class="flex items-center gap-1.5">
                                                <?php if ($prod['category'] === 'Roti'): ?>
                                                    <!-- Icon Bakery / Roti -->
                                                    <svg class="w-4 h-4 text-[#B86B28]" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 2C9.5 2 7.7 4 7 6.3C5 6.7 3.5 8.5 3.5 10.8C3.5 13.1 5.4 15 7.7 15H16.3C18.6 15 20.5 13.1 20.5 10.8C20.5 8.5 19 6.7 17 6.3C16.3 4 14.5 2 12 2Z"></path><path d="M7 19H17M7 22H17"></path></svg>
                                                <?php elseif ($prod['category'] === 'Donat'): ?>
                                                    <!-- Icon Donat -->
                                                    <svg class="w-4 h-4 text-[#D97706]" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="9"></circle><circle cx="12" cy="12" r="3"></circle></svg>
                                                <?php elseif ($prod['category'] === 'Kue & Pastry'): ?>
                                                    <!-- Icon Cake -->
                                                    <svg class="w-4 h-4 text-[#C05621]" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M20 21v-8a2 2 0 00-2-2H6a2 2 0 00-2 2v8"></path><path d="M4 16s.5-1 2-1 2.5 2 4 2 2.5-2 4-2 2.5 2 4 2 2-1 2-1"></path><path d="M12 7V4"></path></svg>
                                                <?php else: ?>
                                                    <!-- Icon Box -->
                                                    <svg class="w-4 h-4 text-[#828282]" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="3" width="18" height="18" rx="2"></rect><path d="M3 9h18"></path></svg>
                                                <?php endif; ?>
                                                <span class="font-medium"><?= htmlspecialchars($prod['category']) ?></span>
                                            </div>
                                        </td>

                                        <!-- Kemasan Badge -->
                                        <td class="px-5 py-4 whitespace-nowrap">
                                            <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-semibold border <?= $prod['packaging_badge_class'] ?>">
                                                <?php if ($prod['packaging_icon'] === 'mika'): ?>
                                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><rect x="3" y="4" width="18" height="16" rx="2" stroke-width="2"></rect><path d="M3 10h18" stroke-width="2"></path></svg>
                                                <?php elseif ($prod['packaging_icon'] === 'kardus'): ?>
                                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"></path></svg>
                                                <?php else: ?>
                                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><circle cx="12" cy="12" r="8" stroke-width="2"></circle></svg>
                                                <?php endif; ?>
                                                <?= htmlspecialchars($prod['packaging_label']) ?>
                                            </span>
                                        </td>

                                        <!-- Minimal Order -->
                                        <td class="px-5 py-4 text-[#333333] font-medium whitespace-nowrap">
                                            <?= htmlspecialchars($prod['min_order']) ?>
                                        </td>

                                        <!-- Harga Jual -->
                                        <td class="px-5 py-4 whitespace-nowrap">
                                            <span class="text-[#1B1C1B] font-bold">Rp <?= number_format($prod['price'], 0, ',', '.') ?></span>
                                            <span class="text-[#828282] text-xs">/ <?= htmlspecialchars($prod['price_unit']) ?></span>
                                        </td>

                                        <!-- Status -->
                                        <td class="px-5 py-4 whitespace-nowrap">
                                            <?php if ($prod['status'] === 'Aktif'): ?>
                                                <span class="inline-flex items-center gap-1.5 text-xs font-semibold text-[#27AE60]">
                                                    <span class="w-1.5 h-1.5 rounded-full bg-[#27AE60]"></span>
                                                    Aktif
                                                </span>
                                            <?php else: ?>
                                                <span class="inline-flex items-center gap-1.5 text-xs font-semibold text-[#828282]">
                                                    <span class="w-1.5 h-1.5 rounded-full bg-[#828282]"></span>
                                                    Nonaktif
                                                </span>
                                            <?php endif; ?>
                                        </td>

                                        <!-- Aksi (Edit & Delete) -->
                                        <td class="px-5 py-4 text-center whitespace-nowrap">
                                            <div class="flex items-center justify-center gap-2">
                                                <!-- Edit Button -->
                                                <button onclick="editProductRow(<?= htmlspecialchars(json_encode($prod)) ?>)" class="p-1.5 text-[#FF9B45] hover:bg-[#FF9B45]/10 rounded-md transition-colors" title="Edit Produk">
                                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path></svg>
                                                </button>
                                                <!-- Delete Button -->
                                                <button onclick="deleteProductRow(<?= $prod['id'] ?>, '<?= addslashes($prod['name']) ?>')" class="p-1.5 text-[#EB5757] hover:bg-[#EB5757]/10 rounded-md transition-colors" title="Hapus Produk">
                                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg>
                                                </button>
                                            </div>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>

                    <!-- Table Footer: Record count & Pagination -->
                    <div class="px-6 py-4 border-t border-[#F0ECE9] flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3 bg-white">
                        <p class="text-xs text-[#828282]" id="tableSummary">
                            Menampilkan <span id="visibleCount" class="font-semibold text-[#1C1C1C]"><?= count($products) ?></span> dari <span class="font-semibold text-[#1C1C1C]"><?= count($products) ?></span> produk bakery
                        </p>

                        <!-- Pagination Buttons -->
                        <div class="flex items-center gap-1.5 text-xs font-semibold">
                            <button class="w-8 h-8 rounded-lg flex items-center justify-center text-[#828282] hover:bg-[#F5F3F1] transition-colors" title="Sebelumnya">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"></path></svg>
                            </button>
                            <button class="w-8 h-8 rounded-lg bg-[#FF9B45] text-white flex items-center justify-center shadow-xs">1</button>
                            <button class="w-8 h-8 rounded-lg text-[#1C1C1C] hover:bg-[#F5F3F1] flex items-center justify-center transition-colors">2</button>
                            <button class="w-8 h-8 rounded-lg text-[#1C1C1C] hover:bg-[#F5F3F1] flex items-center justify-center transition-colors">3</button>
                            <button class="w-8 h-8 rounded-lg flex items-center justify-center text-[#828282] hover:bg-[#F5F3F1] transition-colors" title="Berikutnya">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path></svg>
                            </button>
                        </div>
                    </div>
                </div>

                <!-- BOTTOM INFO CALLOUT BANNER -->
                <div class="bg-[#FFF8F2] border border-[#FF9B45]/20 rounded-xl p-4 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3 shadow-2xs">
                    <div class="flex items-center gap-3">
                        <div class="w-9 h-9 rounded-full bg-[#FF9B45]/15 flex items-center justify-center text-[#FF9B45] shrink-0">
                            <!-- Lamp icon -->
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.663 17h4.673M12 3v1m6.364 1.636l-.707.707M21 12h-1M4 12H3m3.343-5.657l-.707-.707m2.828 9.9a5 5 0 117.072 0l-.548.547A3.374 3.374 0 0014 18.469V19a2 2 0 11-4 0v-.531c0-.895-.356-1.754-.988-2.386l-.548-.547z"></path></svg>
                        </div>
                        <div>
                            <h4 class="text-[#1C1C1C] text-sm font-semibold">Aturan Batas Minimal Order (MOQ) Produksi</h4>
                            <p class="text-[#828282] text-xs mt-0.5">Pesanan dari pelanggan di kasir atau WhatsApp akan otomatis diverifikasi sesuai batas minimal per varian kemasan ini.</p>
                        </div>
                    </div>
                    <a href="#" class="text-[#924C00] hover:text-[#FF9B45] font-medium text-xs flex items-center gap-1.5 transition-colors shrink-0">
                        <span>Pelajari Alur Produksi</span>
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3"></path></svg>
                    </a>
                </div>

            </main>
        </div>
    </div>

    <!-- ============================================================== -->
    <!-- MODAL POPOUT: TAMBAH PRODUK BARU (SESUAI FIGMA SCREENSHOT 2)  -->
    <!-- ============================================================== -->
    <div id="productModal" class="fixed inset-0 z-50 hidden flex items-center justify-center p-4">
        <!-- Backdrop with blur -->
        <div onclick="closeProductModal()" class="fixed inset-0 bg-black/50 backdrop-blur-xs transition-opacity duration-200"></div>

        <!-- Modal Card Container -->
        <div class="bg-white rounded-2xl max-w-[560px] w-full p-6 sm:p-8 shadow-2xl relative z-10 animate-in fade-in zoom-in-95 duration-150 max-h-[90vh] overflow-y-auto">
            
            <!-- Header -->
            <div class="flex items-start justify-between pb-4 border-b border-[#F0ECE9] mb-5">
                <div>
                    <h3 class="text-xl font-bold text-[#1C1C1C]" id="modalTitle">Tambah Produk Baru</h3>
                    <p class="text-[#828282] text-xs mt-1">Tambahkan item roti atau pastry baru ke katalog toko</p>
                </div>
                <button onclick="closeProductModal()" class="text-[#828282] hover:text-[#1C1C1C] p-1.5 rounded-lg hover:bg-[#F5F3F1] transition-colors" title="Tutup">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                </button>
            </div>

            <!-- Form -->
            <form action="" method="POST" class="space-y-4">
                <input type="hidden" name="action" value="add_product">

                <!-- Nama Produk -->
                <div>
                    <label for="prod_nama" class="block text-xs font-semibold text-[#333333] mb-1.5">Nama Produk <span class="text-[#EB5757]">*</span></label>
                    <input type="text" id="prod_nama" name="nama_produk" required placeholder="Contoh: Roti Tawar Kupas Special" 
                        class="w-full bg-white border border-[#E0E0E0] rounded-lg px-3.5 py-2.5 text-sm text-[#1C1C1C] placeholder-[#828282] focus:border-[#FF9B45] focus:ring-1 focus:ring-[#FF9B45] focus:outline-none transition-colors">
                </div>

                <!-- Row 1: Kategori & Harga Jual Satuan -->
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label for="prod_kategori" class="block text-xs font-semibold text-[#333333] mb-1.5">Kategori <span class="text-[#EB5757]">*</span></label>
                        <div class="relative">
                            <select id="prod_kategori" name="kategori" required 
                                class="appearance-none w-full bg-white border border-[#E0E0E0] rounded-lg pl-3.5 pr-9 py-2.5 text-sm text-[#1C1C1C] focus:border-[#FF9B45] focus:ring-1 focus:ring-[#FF9B45] focus:outline-none cursor-pointer transition-colors">
                                <option value="" disabled selected>Pilih Kategori</option>
                                <option value="Roti">Roti</option>
                                <option value="Donat">Donat</option>
                                <option value="Kue & Pastry">Kue & Pastry</option>
                                <option value="Snack Box">Snack Box</option>
                            </select>
                            <div class="absolute inset-y-0 right-0 pr-3 flex items-center pointer-events-none text-[#828282]">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path></svg>
                            </div>
                        </div>
                    </div>

                    <div>
                        <label for="prod_harga" class="block text-xs font-semibold text-[#333333] mb-1.5">Harga Jual Satuan <span class="text-[#EB5757]">*</span></label>
                        <div class="relative flex rounded-lg shadow-2xs">
                            <span class="inline-flex items-center px-3.5 rounded-l-lg border border-r-0 border-[#E0E0E0] bg-[#F5F3F1] text-[#544337] text-xs font-semibold">Rp</span>
                            <input type="text" id="prod_harga" name="harga" required placeholder="15.000" onkeyup="formatCurrency(this)" 
                                class="w-full bg-white border border-[#E0E0E0] rounded-r-lg px-3.5 py-2.5 text-sm text-[#1C1C1C] placeholder-[#828282] focus:border-[#FF9B45] focus:ring-1 focus:ring-[#FF9B45] focus:outline-none transition-colors">
                        </div>
                    </div>
                </div>

                <!-- Row 2: Tipe Kemasan & Jumlah Isi -->
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label for="prod_kemasan" class="block text-xs font-semibold text-[#333333] mb-1.5">Tipe Kemasan</label>
                        <div class="relative">
                            <select id="prod_kemasan" name="kemasan" onchange="handleKemasanChange()" 
                                class="appearance-none w-full bg-white border border-[#E0E0E0] rounded-lg pl-3.5 pr-9 py-2.5 text-sm text-[#1C1C1C] focus:border-[#FF9B45] focus:ring-1 focus:ring-[#FF9B45] focus:outline-none cursor-pointer transition-colors">
                                <option value="Mika" selected>Mika</option>
                                <option value="Kardus">Kardus</option>
                                <option value="Satuan">Satuan</option>
                            </select>
                            <div class="absolute inset-y-0 right-0 pr-3 flex items-center pointer-events-none text-[#828282]">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path></svg>
                            </div>
                        </div>
                    </div>

                    <div>
                        <div class="flex items-center justify-between mb-1.5">
                            <label for="prod_isi" class="block text-xs font-semibold text-[#333333]">Jumlah Isi</label>
                            <span class="text-[10px] font-medium text-[#D97706] bg-[#FEF6EE] px-1.5 py-0.5 rounded border border-[#FDE6D2]">[Khusus Mika/Kardus]</span>
                        </div>
                        <div class="relative flex rounded-lg shadow-2xs">
                            <input type="number" id="prod_isi" name="isi" value="12" min="1" 
                                class="w-full bg-white border border-[#E0E0E0] rounded-l-lg px-3.5 py-2.5 text-sm text-[#1C1C1C] focus:border-[#FF9B45] focus:ring-1 focus:ring-[#FF9B45] focus:outline-none transition-colors">
                            <span class="inline-flex items-center px-3 rounded-r-lg border border-l-0 border-[#E0E0E0] bg-[#F5F3F1] text-[#828282] text-xs">pcs</span>
                        </div>
                    </div>
                </div>

                <!-- Row 3: Minimal Order (MOQ) & Status Produk -->
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label for="prod_moq" class="block text-xs font-semibold text-[#333333] mb-1.5">Minimal Order (MOQ)</label>
                        <div class="relative flex rounded-lg shadow-2xs">
                            <input type="number" id="prod_moq" name="moq" value="1" min="1" 
                                class="w-full bg-white border border-[#E0E0E0] rounded-l-lg px-3.5 py-2.5 text-sm text-[#1C1C1C] focus:border-[#FF9B45] focus:ring-1 focus:ring-[#FF9B45] focus:outline-none transition-colors">
                            <span id="moq_unit" class="inline-flex items-center px-3.5 rounded-r-lg border border-l-0 border-[#E0E0E0] bg-[#F5F3F1] text-[#544337] text-xs font-medium">mika</span>
                        </div>
                        <p class="text-[11px] text-[#828282] mt-1">Batas terendah pesanan diproses</p>
                    </div>

                    <div>
                        <label class="block text-xs font-semibold text-[#333333] mb-1.5">Status Produk</label>
                        <div class="flex items-center justify-between p-2 border border-[#E0E0E0] rounded-lg bg-white h-[42px]">
                            <div class="flex items-center gap-2">
                                <span class="text-xs font-medium text-[#1C1C1C]" id="status_label">Aktif Siap Pesan</span>
                                <span class="bg-[#27AE60]/15 text-[#27AE60] text-[10px] font-semibold px-2 py-0.5 rounded-full" id="status_badge">Aktif</span>
                            </div>
                            <!-- Toggle Switch -->
                            <label class="relative inline-flex items-center cursor-pointer">
                                <input type="checkbox" id="prod_status" name="status" checked onchange="handleStatusToggle(this)" class="sr-only peer">
                                <div class="w-11 h-6 bg-gray-200 peer-focus:outline-none rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-[#FF9B45]"></div>
                            </label>
                        </div>
                        <p class="text-[11px] text-[#828282] mt-1">Item muncul di formulir kasir</p>
                    </div>
                </div>

                <!-- Deskripsi Produk -->
                <div>
                    <label for="prod_deskripsi" class="block text-xs font-semibold text-[#333333] mb-1.5">Deskripsi Produk (Opsional)</label>
                    <textarea id="prod_deskripsi" name="deskripsi" rows="3" placeholder="Tuliskan keterangan rasa, komposisi utama, atau instruksi simpan..." 
                        class="w-full bg-white border border-[#E0E0E0] rounded-lg px-3.5 py-2.5 text-sm text-[#1C1C1C] placeholder-[#828282] focus:border-[#FF9B45] focus:ring-1 focus:ring-[#FF9B45] focus:outline-none transition-colors"></textarea>
                </div>

                <!-- Info Box -->
                <div class="bg-[#FFF8F2] border border-[#FF9B45]/20 rounded-xl p-3 flex items-start gap-2.5">
                    <svg class="w-4 h-4 text-[#D97706] mt-0.5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                    <p class="text-xs text-[#828282] leading-relaxed">Produk akan otomatis disinkronkan ke rekap kebutuhan bahan harian dapur.</p>
                </div>

                <!-- Action Buttons -->
                <div class="flex items-center justify-end gap-3 pt-4 border-t border-[#F0ECE9]">
                    <button type="button" onclick="closeProductModal()" 
                        class="bg-white hover:bg-gray-100 text-[#544337] text-sm font-medium px-5 py-2.5 rounded-lg border border-[#E0E0E0] transition-colors">
                        Batal
                    </button>
                    <button type="submit" 
                        class="bg-[#FF9B45] hover:bg-[#E88C3D] text-white text-sm font-semibold px-5 py-2.5 rounded-lg flex items-center gap-2 transition-all shadow-button-orange active:scale-95">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
                        <span>Simpan Produk</span>
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

        // Modal Popout Open / Close
        function openProductModal() {
            const modal = document.getElementById('productModal');
            modal.classList.remove('hidden');
            document.body.style.overflow = 'hidden';
            document.getElementById('prod_nama').focus();
        }

        function closeProductModal() {
            const modal = document.getElementById('productModal');
            modal.classList.add('hidden');
            document.body.style.overflow = '';
        }

        // Keyboard ESC untuk tutup modal
        document.addEventListener('keydown', function(e) {
            if (e.key === 'Escape') {
                closeProductModal();
            }
        });

        // Dynamic Kemasan Unit Handling
        function handleKemasanChange() {
            const kemasan = document.getElementById('prod_kemasan').value;
            const moqUnit = document.getElementById('moq_unit');
            const isiInput = document.getElementById('prod_isi');
            
            if (kemasan === 'Mika') {
                moqUnit.textContent = 'mika';
                isiInput.disabled = false;
                isiInput.value = isiInput.value > 1 ? isiInput.value : 12;
            } else if (kemasan === 'Kardus') {
                moqUnit.textContent = 'kardus';
                isiInput.disabled = false;
                isiInput.value = isiInput.value > 1 ? isiInput.value : 20;
            } else {
                moqUnit.textContent = 'pcs';
                isiInput.disabled = true;
                isiInput.value = 1;
            }
        }

        // Status Toggle Switch
        function handleStatusToggle(checkbox) {
            const label = document.getElementById('status_label');
            const badge = document.getElementById('status_badge');
            if (checkbox.checked) {
                label.textContent = 'Aktif Siap Pesan';
                badge.textContent = 'Aktif';
                badge.className = 'bg-[#27AE60]/15 text-[#27AE60] text-[10px] font-semibold px-2 py-0.5 rounded-full';
            } else {
                label.textContent = 'Nonaktif';
                badge.textContent = 'Nonaktif';
                badge.className = 'bg-gray-100 text-[#828282] text-[10px] font-semibold px-2 py-0.5 rounded-full';
            }
        }

        // Format Currency Helper
        function formatCurrency(input) {
            let val = input.value.replace(/\D/g, '');
            if (val === '') {
                input.value = '';
                return;
            }
            input.value = new Intl.NumberFormat('id-ID').format(val);
        }

        // Live Filter Function (Search, Category, Status)
        function filterProducts() {
            const searchVal = document.getElementById('searchInput').value.toLowerCase().trim();
            const categoryVal = document.getElementById('categoryFilter').value;
            const statusVal = document.getElementById('statusFilter').value;

            const rows = document.querySelectorAll('.product-row');
            let visibleCount = 0;

            rows.forEach((row, index) => {
                const name = row.getAttribute('data-name');
                const sku = row.getAttribute('data-sku');
                const category = row.getAttribute('data-category');
                const status = row.getAttribute('data-status');

                const matchesSearch = !searchVal || name.includes(searchVal) || sku.includes(searchVal);
                const matchesCategory = !categoryVal || category === categoryVal;
                const matchesStatus = !statusVal || status === statusVal;

                if (matchesSearch && matchesCategory && matchesStatus) {
                    row.style.display = '';
                    visibleCount++;
                    row.querySelector('.row-number').textContent = visibleCount;
                } else {
                    row.style.display = 'none';
                }
            });

            document.getElementById('visibleCount').textContent = visibleCount;
        }

        // Edit row (Pre-fill modal)
        function editProductRow(prod) {
            openProductModal();
            document.getElementById('modalTitle').textContent = 'Edit Produk: ' + prod.name;
            document.getElementById('prod_nama').value = prod.name;
            document.getElementById('prod_kategori').value = prod.category;
            document.getElementById('prod_harga').value = new Intl.NumberFormat('id-ID').format(prod.price);
            document.getElementById('prod_kemasan').value = prod.packaging_type;
            document.getElementById('prod_deskripsi').value = prod.description || '';
            handleKemasanChange();
        }

        // Delete row
        function deleteProductRow(id, name) {
            if (confirm(`Apakah Anda yakin ingin menghapus produk "${name}" dari katalog?`)) {
                // Sederhana: sembunyikan baris atau request hapus
                const row = event.target.closest('tr');
                if (row) {
                    row.remove();
                    filterProducts();
                }
            }
        }

        // Ekspor data
        function handleExportData() {
            alert('Fitur Ekspor Data: Mengunduh katalog produk dalam format CSV/Excel...');
        }
    </script>
</body>
</html>
