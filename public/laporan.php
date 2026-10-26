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
        'tanggal' => date('d M Y'),
        'jam' => '14:00 WIB',
        'order_id' => '#ORD-089',
        'customer' => 'Ibu Sari Dewi',
        'items' => 'Roti Hajatan Putih (10 box), Bolu Pisang Mika (2 mika)',
        'total' => 'Rp 144.000',
        'total_raw' => 144000,
        'status_bayar' => 'Belum Bayar',
        'status_bayar_class' => 'bg-[#828282]/15 text-[#544337]'
    ],
    [
        'no' => 2,
        'tanggal' => date('d M Y'),
        'jam' => '10:15 WIB',
        'order_id' => '#ORD-088',
        'customer' => 'Pak Budi Santoso',
        'items' => 'Roti Hajatan Batik (10 box)',
        'total' => 'Rp 110.000',
        'total_raw' => 110000,
        'status_bayar' => 'Hutang',
        'status_bayar_label' => 'Hutang / Piutang',
        'status_bayar_class' => 'bg-[#E2B93B]/20 text-[#886C12]'
    ],
    [
        'no' => 3,
        'tanggal' => date('d M Y', strtotime('-1 day')),
        'jam' => '16:30 WIB',
        'order_id' => '#ORD-084',
        'customer' => 'Bu Rina',
        'items' => 'Bolu Pisang Mika (2 mika), Roti Bijian Besar (20 pcs)',
        'total' => 'Rp 124.000',
        'total_raw' => 124000,
        'status_bayar' => 'Lunas',
        'status_bayar_class' => 'bg-[#27AE60]/15 text-[#1B7A43]'
    ],
    [
        'no' => 4,
        'tanggal' => date('d M Y', strtotime('-2 days')),
        'jam' => '11:00 WIB',
        'order_id' => '#ORD-081',
        'customer' => 'Warung Pak Haji',
        'items' => 'Roti Bijian Kecil Satuan (100 pcs)',
        'total' => 'Rp 300.000',
        'total_raw' => 300000,
        'status_bayar' => 'Lunas',
        'status_bayar_class' => 'bg-[#27AE60]/15 text-[#1B7A43]'
    ],
    [
        'no' => 5,
        'tanggal' => date('d M Y', strtotime('-3 days')),
        'jam' => '08:20 WIB',
        'order_id' => '#ORD-079',
        'customer' => 'Toko Maju Jaya',
        'items' => 'Bolu Tanpa Pisang Putih (15 box), Roti Kopi (50 pcs)',
        'total' => 'Rp 485.000',
        'total_raw' => 485000,
        'status_bayar' => 'Lunas',
        'status_bayar_class' => 'bg-[#27AE60]/15 text-[#1B7A43]'
    ]
];

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
    <title>Laporan - Berkat Dinasti</title>
    <link rel="icon" type="image/png" href="assets/images/logo.png">
    
    <!-- Font Awesome 5 Free CDN -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css" crossorigin="anonymous" referrerpolicy="no-referrer" />
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
                            orange: '#D46E15',
                            orangeHover: '#B85207',
                            lightText: '#E8D7CD',
                            bg: '#F5F1EB',
                            surface: '#FDFBF7',
                            border: '#E2D7C8',
                            muted: '#8E7E73',
                            dark: '#251A14',
                            darkText: '#251A14',
                            brownText: '#5E4D42',
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
                        'card': '0px 4px 16px rgba(62, 43, 33, 0.06)',
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
    </style>
</head>
<body class="min-h-screen bg-[#F5F1EB] text-[#251A14] flex flex-col antialiased selection:bg-[#D46E15] selection:text-white">

    <!-- OVERALL WRAPPER -->
    <div class="flex flex-1 w-full relative">

        <!-- MOBILE SIDEBAR OVERLAY -->
        <div id="sidebar-overlay" onclick="toggleSidebar()" class="fixed inset-0 bg-black/50 backdrop-blur-xs z-40 lg:hidden hidden transition-opacity duration-300"></div>

        <!-- ASIDE / SIDEBAR (Width 240px) -->
        <aside id="main-sidebar" class="fixed top-0 bottom-0 left-0 w-[240px] bg-[#3E2B21] border-r border-[#2C1E17] z-50 flex flex-col justify-between -translate-x-full lg:translate-x-0 transition-transform duration-300 ease-in-out shadow-2xl lg:shadow-none">
            
            <!-- Top Logo & Navigation -->
            <div class="flex flex-col flex-1 overflow-y-auto">
                
                <!-- Logo & Brand Header (Height 80px) -->
                <div class="h-20 px-5 flex items-center gap-3 border-b border-white/10 shrink-0">
                    <div class="w-9 h-9 rounded-full bg-white p-0.5 flex items-center justify-center shrink-0 shadow-sm overflow-hidden">
                        <img src="assets/images/logo.png" alt="Berkat Dinasti Logo" class="w-full h-full object-contain">
                    </div>
                    <div class="flex flex-col">
                        <span class="text-white font-bold text-base tracking-tight leading-none">Berkat Dinasti</span>
                        <span class="text-[#D8C7B8] text-[10px] tracking-wider uppercase font-semibold mt-1">Bakery & Pastry</span>
                    </div>
                    
                    <!-- Mobile Close Button -->
                    <button onclick="toggleSidebar()" class="ml-auto text-white/70 hover:text-white lg:hidden p-1" aria-label="Tutup Menu">
                        <i class="fas fa-times text-base"></i>
                    </button>
                </div>

                <!-- Navigation List -->
                <nav class="p-4 space-y-1.5 flex-1">
                    <!-- Beranda -->
                    <a href="beranda.php" class="text-white/80 hover:text-white hover:bg-white/10 rounded-lg px-4 py-3 flex items-center gap-3 font-medium text-sm transition-colors">
                        <i class="fas fa-home w-5 text-center text-base"></i>
                        <span>Beranda</span>
                    </a>

                    <!-- Produk -->
                    <a href="produk.php" class="text-white/80 hover:text-white hover:bg-white/10 rounded-lg px-4 py-3 flex items-center gap-3 font-medium text-sm transition-colors">
                        <i class="fas fa-bread-slice w-5 text-center text-base"></i>
                        <span>Produk</span>
                    </a>

                    <!-- Pesanan -->
                    <a href="pesanan.php" class="text-white/80 hover:text-white hover:bg-white/10 rounded-lg px-4 py-3 flex items-center gap-3 font-medium text-sm transition-colors">
                        <i class="fas fa-clipboard-list w-5 text-center text-base"></i>
                        <span>Pesanan</span>
                    </a>

                    <!-- Pelanggan -->
                    <a href="pelanggan.php" class="text-white/80 hover:text-white hover:bg-white/10 rounded-lg px-4 py-3 flex items-center gap-3 font-medium text-sm transition-colors">
                        <i class="fas fa-users w-5 text-center text-base"></i>
                        <span>Pelanggan</span>
                    </a>

                    <!-- Laporan (Active) -->
                    <a href="laporan.php" class="bg-[#D46E15] text-white rounded-lg px-4 py-3 flex items-center gap-3 font-semibold text-sm transition-all shadow-sm">
                        <i class="fas fa-chart-line w-5 text-center text-base"></i>
                        <span>Laporan</span>
                    </a>

                    <!-- Pengaturan -->
                    <a href="pengaturan.php" class="text-white/80 hover:text-white hover:bg-white/10 rounded-lg px-4 py-3 flex items-center gap-3 font-medium text-sm transition-colors">
                        <i class="fas fa-cog w-5 text-center text-base"></i>
                        <span>Pengaturan</span>
                    </a>
                </nav>
            </div>

            <!-- Bottom User Profile Card -->
            <div class="p-4 border-t border-white/10 shrink-0">
                <div class="bg-black/25 rounded-xl p-2.5 flex items-center justify-between border border-white/5">
                    <div class="flex items-center gap-3 overflow-hidden">
                        <div class="w-9 h-9 rounded-full bg-[#D46E15] text-white font-bold text-xs flex items-center justify-center shrink-0 shadow-sm">
                            BD
                        </div>
                        <div class="truncate">
                            <p class="text-white font-semibold text-sm leading-tight truncate"><?= htmlspecialchars($_SESSION['nama_lengkap'] ?? $_SESSION['user'] ?? 'Admin') ?></p>
                            <p class="text-[#D8C7B8] text-xs leading-tight mt-0.5"><?= htmlspecialchars(ucfirst($_SESSION['role'] ?? 'Admin')) ?></p>
                        </div>
                    </div>
                    <button type="button" onclick="openLogoutModal()" title="Keluar / Logout" class="p-1.5 text-white/60 hover:text-white transition-colors shrink-0">
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
                        <i class="fas fa-bars text-lg"></i>
                    </button>

                    <div>
                        <!-- Breadcrumbs -->
                        <div class="flex items-center gap-1.5 text-xs">
                            <a href="beranda.php" class="text-[#8E7E73] hover:text-[#3E2B21] transition-colors">Beranda</a>
                            <span class="text-[#8E7E73]">/</span>
                            <span class="text-[#D46E15] font-semibold">Laporan</span>
                        </div>
                        <!-- Section Heading -->
                        <h1 class="text-[#251A14] font-bold text-base md:text-lg leading-tight mt-0.5">Laporan & Keuangan</h1>
                    </div>
                </div>

                <!-- Right: Date Indicator, Notification Bell, User Avatar -->
                <div class="flex items-center gap-3 md:gap-4 relative">
                    
                    <!-- Date Button (Dinamis) -->
                    <div class="hidden sm:flex items-center gap-2 bg-[#EFE8DE] text-[#3E2B21] px-3.5 py-1.5 rounded-lg text-xs md:text-sm font-medium border border-[#DDD3C5]">
                        <i class="far fa-calendar-alt text-[#D46E15] text-sm"></i>
                        <span><?= $todayFormatted ?></span>
                    </div>

                    <!-- Notification Bell with Popover -->
                    <div class="relative">
                        <button onclick="toggleNotificationDropdown()" class="relative p-2 text-[#3E2B21] hover:bg-[#EFE8DE] rounded-lg transition-colors" title="Notifikasi" id="notificationBtn">
                            <i class="far fa-bell text-[#3E2B21] text-lg"></i>
                            <span class="absolute top-1.5 right-1.5 w-2 h-2 bg-[#D46E15] rounded-full ring-2 ring-[#FDFBF7]"></span>
                        </button>

                        <div id="notificationDropdown" class="hidden absolute right-0 mt-2.5 w-80 sm:w-96 craft-dropdown py-2 z-50">
                            <div class="px-4 py-2 border-b border-[#ECE5DD] flex items-center justify-between">
                                <span class="font-semibold text-xs text-[#251A14]">Notifikasi Keuangan</span>
                                <span class="text-[10px] craft-badge-warning font-medium px-2 py-0.5 rounded-full">Ringkasan</span>
                            </div>
                            <div class="p-3.5 text-xs text-[#5E4D42] space-y-2 tabular-nums">
                                <p>• Total omset bulan ini: <strong class="text-[#251A14]">Rp 4.250.000</strong></p>
                                <p>• Piutang menunggu pelunasan: <strong class="text-rose-700">Rp 850.000</strong></p>
                                <p>• Persentase transaksi lunas: <strong class="text-emerald-700">83%</strong></p>
                            </div>
                        </div>
                    </div>

                    <!-- User Circle Icon with Popover -->
                    <div class="relative">
                        <div onclick="toggleUserDropdown()" class="w-9 h-9 rounded-full bg-[#D46E15] flex items-center justify-center text-white shadow-xs cursor-pointer hover:bg-[#B85207] transition-all active:scale-95" title="Profil Pengguna" id="userAvatarBtn">
                            <i class="fas fa-user text-white text-xs"></i>
                        </div>

                        <div id="userDropdown" class="hidden absolute right-0 mt-2.5 w-56 craft-dropdown py-1.5 z-50 text-xs">
                            <div class="px-4 py-2.5 border-b border-[#ECE5DD]">
                                <p class="text-xs font-semibold text-[#251A14] truncate"><?= htmlspecialchars($_SESSION['nama_lengkap'] ?? $_SESSION['user'] ?? 'Admin') ?></p>
                                <p class="text-[11px] text-[#8E7E73] mt-0.5"><?= htmlspecialchars(ucfirst($_SESSION['role'] ?? 'Admin')) ?> Toko</p>
                            </div>
                            <div class="py-1">
                                <a href="pengaturan.php" class="craft-dropdown-item flex items-center gap-2">
                                    <i class="fas fa-cog text-[#8E7E73]"></i>
                                    <span>Pengaturan Toko</span>
                                </a>
                            </div>
                            <div class="border-t border-[#ECE5DD] pt-1">
                                <button type="button" onclick="openLogoutModal()" class="w-full text-left craft-dropdown-item text-[#991B1B] hover:text-[#7F1D1D] hover:bg-rose-50/70 flex items-center gap-2">
                                    <i class="fas fa-sign-out-alt"></i>
                                    <span>Keluar dari Akun</span>
                                </button>
                            </div>
                        </div>
                    </div>

                </div>
            </header>

            <!-- MAIN CONTENT CONTAINER -->
            <main class="flex-1 p-4 md:p-8 w-full min-w-0">
                
                <!-- Toast In-Page -->
                <div id="dynamicToast" class="hidden fixed bottom-6 right-6 z-50 bg-[#3E2B21] text-white px-5 py-3.5 rounded-xl shadow-2xl flex items-center gap-3 text-xs font-semibold animate-in slide-in-from-bottom-3 duration-200 border border-white/10">
                    <span id="dynamicToastIcon">✨</span>
                    <span id="dynamicToastText">Aksi berhasil dijalankan</span>
                </div>

                <!-- TOP FILTER & ACTION BAR -->
                <div id="filterBar" class="flex flex-col lg:flex-row lg:items-center lg:justify-between gap-4 mb-6">
                    
                    <!-- Left: Periode Filter Controls -->
                    <div class="flex flex-wrap items-center gap-3">
                        <div class="flex items-center gap-2 text-xs md:text-sm font-semibold text-[#251A14]">
                            <i class="fas fa-filter text-[#D46E15]"></i>
                            <span>Periode:</span>
                        </div>

                        <!-- Dropdown Periode -->
                        <div class="relative">
                            <select id="periodeSelect" class="appearance-none bg-[#EFE8DE] border border-[#DDD3C5] rounded-xl pl-3.5 pr-8 py-2 text-xs md:text-sm text-[#251A14] font-semibold focus:border-[#D46E15] focus:outline-none cursor-pointer shadow-2xs">
                                <option value="bulan_ini">Bulan Ini (<?= $currentMonthIndo ?>)</option>
                                <option value="bulan_lalu">Bulan Lalu (<?= ($monthsIndoFull[date('m', strtotime('-1 month'))] ?? '') . ' ' . date('Y', strtotime('-1 month')) ?>)</option>
                                <option value="kuartal">Kuartal Berjalan</option>
                                <option value="ytd">Tahun <?= date('Y') ?> (YTD)</option>
                            </select>
                            <div class="absolute inset-y-0 right-0 pr-2.5 flex items-center pointer-events-none text-[#8E7E73]">
                                <i class="fas fa-chevron-down text-[10px]"></i>
                            </div>
                        </div>

                        <!-- Date Range Display Box -->
                        <div class="bg-[#EFE8DE] border border-[#DDD3C5] rounded-xl px-3.5 py-2 text-xs md:text-sm text-[#3E2B21] font-semibold flex items-center gap-2 shadow-2xs">
                            <i class="far fa-calendar-alt text-[#D46E15] text-xs"></i>
                            <span id="dateRangeText">01/<?= date('m/Y') ?> — <?= date('d/m/Y') ?></span>
                        </div>

                        <!-- Button Terapkan Filter -->
                        <button onclick="applyPeriodFilter()" class="bg-[#D46E15] hover:bg-[#B85207] text-white text-xs md:text-sm font-semibold px-4 py-2 rounded-xl flex items-center gap-1.5 transition-all shadow-button-orange active:scale-95">
                            <i class="fas fa-check text-xs"></i>
                            <span>Terapkan Filter</span>
                        </button>
                    </div>

                    <!-- Right: Unduh Excel & Cetak Laporan -->
                    <div id="reportActions" class="flex items-center gap-3 self-end sm:self-auto">
                        <button onclick="handleExportExcel()" class="bg-[#EFE8DE] hover:bg-[#E5DDD0] border border-[#DDD3C5] text-[#3E2B21] text-xs md:text-sm font-semibold px-3.5 py-2 rounded-xl flex items-center gap-2 transition-colors shadow-2xs">
                            <i class="fas fa-file-excel text-xs text-emerald-700"></i>
                            <span>Unduh Excel (CSV)</span>
                        </button>

                        <button onclick="window.print()" class="bg-[#EFE8DE] hover:bg-[#E5DDD0] border border-[#DDD3C5] text-[#3E2B21] text-xs md:text-sm font-semibold px-3.5 py-2 rounded-xl flex items-center gap-2 transition-colors shadow-2xs">
                            <i class="fas fa-print text-xs text-[#5E4D42]"></i>
                            <span>Cetak Laporan</span>
                        </button>
                    </div>

                </div>

                <!-- ROW 1: 4 KPI STATS (Dark Chocolate Espresso Cards) -->
                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-5 mb-6">
                    
                    <!-- KPI 1: Total Pemasukan -->
                    <div class="craft-card-espresso rounded-xl p-5 border border-white/10 shadow-lg text-[#FAF8F5] relative overflow-hidden flex flex-col justify-between">
                        <div>
                            <div class="flex items-center justify-between mb-2">
                                <span class="text-xs text-[#D8C7B8] font-semibold tracking-wider uppercase">Total Pemasukan</span>
                                <div class="w-9 h-9 rounded-lg bg-[#27AE60]/20 text-[#27AE60] flex items-center justify-center ring-1 ring-[#27AE60]/30">
                                    <i class="fas fa-wallet text-sm"></i>
                                </div>
                            </div>
                            <h3 class="text-2xl font-bold text-[#FAF8F5] tracking-tight mt-1" id="kpiPemasukan">Rp 4.250.000</h3>
                        </div>
                        <div class="mt-4">
                            <span class="inline-flex items-center gap-1 text-[11px] font-semibold px-2.5 py-0.5 rounded-full bg-[#27AE60]/20 text-[#27AE60] border border-[#27AE60]/30">
                                <i class="fas fa-arrow-up text-[9px]"></i>
                                <span>+12% vs bln lalu</span>
                            </span>
                        </div>
                    </div>

                    <!-- KPI 2: Total Pesanan -->
                    <div class="craft-card-espresso rounded-xl p-5 border border-white/10 shadow-lg text-[#FAF8F5] relative overflow-hidden flex flex-col justify-between">
                        <div>
                            <div class="flex items-center justify-between mb-2">
                                <span class="text-xs text-[#D8C7B8] font-semibold tracking-wider uppercase">Total Pesanan</span>
                                <div class="w-9 h-9 rounded-lg bg-[#D46E15]/20 text-[#D46E15] flex items-center justify-center ring-1 ring-[#D46E15]/30">
                                    <i class="fas fa-clipboard-check text-sm"></i>
                                </div>
                            </div>
                            <h3 class="text-2xl font-bold text-[#FAF8F5] tracking-tight mt-1" id="kpiPesanan">48 Order</h3>
                        </div>
                        <div class="mt-4">
                            <span class="text-xs text-[#D8C7B8]">Rata-rata 1.6 order/hari</span>
                        </div>
                    </div>

                    <!-- KPI 3: Total Piutang (Hutang) -->
                    <div class="craft-card-espresso rounded-xl p-5 border border-white/10 shadow-lg text-[#FAF8F5] relative overflow-hidden flex flex-col justify-between">
                        <div>
                            <div class="flex items-center justify-between mb-2">
                                <span class="text-xs text-[#D8C7B8] font-semibold tracking-wider uppercase">Sisa Piutang</span>
                                <div class="w-9 h-9 rounded-lg bg-[#EB5757]/20 text-[#EB5757] flex items-center justify-center ring-1 ring-[#EB5757]/30">
                                    <i class="fas fa-exclamation-triangle text-sm"></i>
                                </div>
                            </div>
                            <h3 class="text-2xl font-bold text-[#FF8585] tracking-tight mt-1" id="kpiPiutang">Rp 850.000</h3>
                        </div>
                        <div class="mt-4">
                            <span class="text-xs text-[#D8C7B8]">Dari 8 pesanan aktif</span>
                        </div>
                    </div>

                    <!-- KPI 4: Pesanan Pending -->
                    <div class="craft-card-espresso rounded-xl p-5 border border-white/10 shadow-lg text-[#FAF8F5] relative overflow-hidden flex flex-col justify-between">
                        <div>
                            <div class="flex items-center justify-between mb-2">
                                <span class="text-xs text-[#D8C7B8] font-semibold tracking-wider uppercase">Pesanan Pending</span>
                                <div class="w-9 h-9 rounded-lg bg-[#E2B93B]/20 text-[#E2B93B] flex items-center justify-center ring-1 ring-[#E2B93B]/30">
                                    <i class="fas fa-hourglass-half text-sm"></i>
                                </div>
                            </div>
                            <h3 class="text-2xl font-bold text-[#E2B93B] tracking-tight mt-1">6 Order</h3>
                        </div>
                        <div class="mt-4">
                            <span class="text-xs text-[#D8C7B8]">5 antrean oven, 1 tertunda</span>
                        </div>
                    </div>

                </div>

                <!-- ROW 2: CHARTS & ANALYTICS -->
                <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 mb-6">
                    
                    <!-- LEFT 2 COLS: GRAFIK TREN PENJUALAN -->
                    <div class="lg:col-span-2 bg-[#FDFBF7] rounded-2xl border border-[#E2D7C8] p-6 shadow-card flex flex-col justify-between">
                        <div>
                            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3 mb-6">
                                <div>
                                    <h3 class="text-base font-bold text-[#251A14]">Grafik Tren Penjualan</h3>
                                    <p class="text-xs text-[#8E7E73] mt-0.5">Analisis omset per bulan dalam juta rupiah (Tahun <?= date('Y') ?>)</p>
                                </div>
                                <div class="bg-[#EFE8DE] p-1 rounded-xl flex items-center gap-1 self-start sm:self-auto border border-[#DDD3C5]">
                                    <button onclick="toggleChartMode('omset')" id="btnChartOmset" 
                                        class="px-3 py-1.5 rounded-lg text-xs font-semibold bg-[#D46E15] text-white shadow-2xs transition-all">
                                        Pemasukan
                                    </button>
                                    <button onclick="toggleChartMode('order')" id="btnChartOrder" 
                                        class="px-3 py-1.5 rounded-lg text-xs font-semibold text-[#8E7E73] hover:text-[#251A14] transition-all">
                                        Jumlah Pesanan
                                    </button>
                                </div>
                            </div>

                            <!-- Bar Chart Visual Container -->
                            <div class="pt-4 pb-2">
                                <div class="h-56 flex items-end justify-between gap-3 md:gap-6 px-2 relative border-b border-[#E2D7C8]">
                                    
                                    <!-- Y-Axis Background Grid Lines -->
                                    <div class="absolute inset-0 flex flex-col justify-between pointer-events-none text-[10px] text-[#A6978A]">
                                        <div class="border-b border-dashed border-[#E2D7C8] pb-0.5 flex justify-between"><span>5 jt</span></div>
                                        <div class="border-b border-dashed border-[#E2D7C8] pb-0.5 flex justify-between"><span>4 jt</span></div>
                                        <div class="border-b border-dashed border-[#E2D7C8] pb-0.5 flex justify-between"><span>3 jt</span></div>
                                        <div class="border-b border-dashed border-[#E2D7C8] pb-0.5 flex justify-between"><span>2 jt</span></div>
                                        <div class="flex justify-between"><span>0</span></div>
                                    </div>

                                    <!-- Bars -->
                                    <div class="flex-1 flex flex-col items-center gap-2 h-full justify-end z-10 group cursor-pointer">
                                        <div class="w-full max-w-[48px] bg-[#E8D7CD] hover:bg-[#D46E15] rounded-t-lg transition-all" style="height: 68%;"></div>
                                        <span class="text-xs text-[#8E7E73] font-medium">Mei</span>
                                    </div>

                                    <div class="flex-1 flex flex-col items-center gap-2 h-full justify-end z-10 group cursor-pointer">
                                        <div class="w-full max-w-[48px] bg-[#E8D7CD] hover:bg-[#D46E15] rounded-t-lg transition-all" style="height: 75%;"></div>
                                        <span class="text-xs text-[#8E7E73] font-medium">Jun</span>
                                    </div>

                                    <div class="flex-1 flex flex-col items-center gap-2 h-full justify-end z-10 group cursor-pointer">
                                        <div class="w-full max-w-[48px] bg-[#E8D7CD] hover:bg-[#D46E15] rounded-t-lg transition-all" style="height: 84%;"></div>
                                        <span class="text-xs text-[#8E7E73] font-medium">Jul</span>
                                    </div>

                                    <div class="flex-1 flex flex-col items-center gap-2 h-full justify-end z-10 group cursor-pointer">
                                        <div class="w-full max-w-[48px] bg-[#E8D7CD] hover:bg-[#D46E15] rounded-t-lg transition-all" style="height: 72%;"></div>
                                        <span class="text-xs text-[#8E7E73] font-medium">Agu</span>
                                    </div>

                                    <div class="flex-1 flex flex-col items-center gap-2 h-full justify-end z-10 group cursor-pointer">
                                        <div class="w-full max-w-[48px] bg-[#E8D7CD] hover:bg-[#D46E15] rounded-t-lg transition-all" style="height: 88%;"></div>
                                        <span class="text-xs text-[#8E7E73] font-medium">Sep</span>
                                    </div>

                                    <!-- Bar Aktif -->
                                    <div class="flex-1 flex flex-col items-center gap-2 h-full justify-end z-10 relative cursor-pointer">
                                        <div class="absolute -top-7 bg-[#3E2B21] text-white text-[10px] font-bold py-0.5 px-2.5 rounded-full shadow-sm flex items-center gap-1 shrink-0 whitespace-nowrap border border-white/10">
                                            <span>Tertinggi ★</span>
                                            <span class="text-[#D46E15]">4.25 jt</span>
                                        </div>
                                        <div class="w-full max-w-[48px] bg-[#D46E15] rounded-t-lg transition-all shadow-xs" style="height: 94%;"></div>
                                        <span class="text-xs text-[#924C00] font-bold">Okt (Aktif)</span>
                                    </div>

                                </div>
                            </div>
                        </div>

                        <!-- Bottom Insight Banner -->
                        <div class="pt-4 mt-3 border-t border-[#E2D7C8] flex flex-col sm:flex-row sm:items-center sm:justify-between gap-2 text-xs">
                            <div class="flex items-center gap-1.5 text-[#27AE60] font-semibold">
                                <i class="fas fa-chart-line"></i>
                                <span>Pertumbuhan omset rata-rata 8.4% per bulan</span>
                            </div>
                            <span class="text-[#8E7E73]">Update otomatis per pesanan selesai</span>
                        </div>
                    </div>

                    <!-- RIGHT 1 COL: RINGKASAN PEMBAYARAN -->
                    <div class="bg-[#FDFBF7] rounded-2xl border border-[#E2D7C8] p-6 shadow-card flex flex-col justify-between">
                        <div>
                            <div class="flex items-start justify-between pb-3 border-b border-[#E2D7C8] mb-4">
                                <div>
                                    <h3 class="text-base font-bold text-[#251A14]">Ringkasan Pembayaran</h3>
                                    <p class="text-xs text-[#8E7E73] mt-0.5">Distribusi pesanan aktif</p>
                                </div>
                                <span class="bg-[#27AE60]/15 text-[#1B7A43] text-xs font-semibold px-2.5 py-0.5 rounded-full border border-[#27AE60]/20">
                                    83% Lunas
                                </span>
                            </div>

                            <div class="space-y-4 pt-2">
                                <!-- Item 1: Lunas -->
                                <div>
                                    <div class="flex justify-between text-xs font-bold mb-1">
                                        <span class="text-[#1B7A43] flex items-center gap-1.5"><i class="fas fa-check-circle text-xs"></i> Lunas (36 Pesanan)</span>
                                        <span class="text-[#251A14]">Rp 3.400.000</span>
                                    </div>
                                    <div class="w-full bg-[#EFE8DE] h-2.5 rounded-full overflow-hidden">
                                        <div class="bg-[#27AE60] h-full rounded-full" style="width: 80%;"></div>
                                    </div>
                                </div>

                                <!-- Item 2: Hutang / DP -->
                                <div>
                                    <div class="flex justify-between text-xs font-bold mb-1">
                                        <span class="text-[#886C12] flex items-center gap-1.5"><i class="fas fa-clock text-xs"></i> Hutang / DP (8 Pesanan)</span>
                                        <span class="text-[#251A14]">Rp 620.000</span>
                                    </div>
                                    <div class="w-full bg-[#EFE8DE] h-2.5 rounded-full overflow-hidden">
                                        <div class="bg-[#E2B93B] h-full rounded-full" style="width: 15%;"></div>
                                    </div>
                                </div>

                                <!-- Item 3: Belum Bayar -->
                                <div>
                                    <div class="flex justify-between text-xs font-bold mb-1">
                                        <span class="text-[#5E4D42] flex items-center gap-1.5"><i class="fas fa-times-circle text-xs"></i> Belum Bayar (4 Pesanan)</span>
                                        <span class="text-[#251A14]">Rp 230.000</span>
                                    </div>
                                    <div class="w-full bg-[#EFE8DE] h-2.5 rounded-full overflow-hidden">
                                        <div class="bg-[#8E7E73] h-full rounded-full" style="width: 5%;"></div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="pt-4 mt-4 border-t border-[#E2D7C8] text-[11px] text-[#5E4D42] leading-relaxed bg-[#EFE8DE] p-3 rounded-xl border border-[#DDD3C5]">
                            <i class="fas fa-lightbulb text-[#D46E15] mr-1"></i> Hutang otomatis diakumulasikan ke profil pelanggan dan ditagihkan saat kurir mengantar pesanan berikutnya.
                        </div>
                    </div>

                </div>

                <!-- ROW 3: RIWAYAT SEMUA PESANAN TABLE CARD -->
                <div class="bg-[#FDFBF7] rounded-2xl border border-[#E2D7C8] shadow-card overflow-hidden">
                    
                    <!-- Table Card Header & Segmented Status Filters -->
                    <div class="px-6 py-4 border-b border-[#E2D7C8] flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
                        <div class="flex items-center gap-2.5">
                            <h3 class="text-base font-bold text-[#251A14]">Riwayat Transaksi Pesanan</h3>
                            <span class="bg-[#D46E15]/15 text-[#924C00] text-xs font-semibold px-2.5 py-0.5 rounded-full" id="tableRecordCountBadge">
                                <?= count($orders_report) ?> Data
                            </span>
                        </div>

                        <!-- Segmented Filter Pills -->
                        <div class="flex flex-wrap items-center gap-1.5 text-xs font-semibold">
                            <button onclick="filterReportTable('all', this)" id="filterBtnAll" 
                                class="px-3 py-1.5 rounded-lg bg-[#3E2B21] text-white shadow-2xs transition-colors">
                                Semua (<?= count($orders_report) ?>)
                            </button>
                            <button onclick="filterReportTable('Lunas', this)" id="filterBtnLunas" 
                                class="px-3 py-1.5 rounded-lg bg-[#EFE8DE] border border-[#DDD3C5] text-[#5E4D42] hover:bg-[#E5DDD0] transition-colors">
                                Lunas (3)
                            </button>
                            <button onclick="filterReportTable('Hutang', this)" id="filterBtnHutang" 
                                class="px-3 py-1.5 rounded-lg bg-[#EFE8DE] border border-[#DDD3C5] text-[#5E4D42] hover:bg-[#E5DDD0] transition-colors">
                                Hutang (1)
                            </button>
                            <button onclick="filterReportTable('Belum Bayar', this)" id="filterBtnBelumBayar" 
                                class="px-3 py-1.5 rounded-lg bg-[#EFE8DE] border border-[#DDD3C5] text-[#5E4D42] hover:bg-[#E5DDD0] transition-colors">
                                Belum Bayar (1)
                            </button>
                        </div>
                    </div>

                    <!-- Responsive Orders Table (Lebar Pas & Tidak Terpotong) -->
                    <div class="overflow-x-auto w-full">
                        <table class="w-full text-left border-collapse" id="ordersReportTable">
                            <thead>
                                <tr class="bg-[#3E2B21] text-[#FAF8F5] text-xs font-bold uppercase tracking-wider">
                                    <th class="py-3 px-3.5 text-center w-12">No</th>
                                    <th class="py-3 px-3.5 min-w-[110px]">Tanggal & Jam</th>
                                    <th class="py-3 px-3.5 min-w-[170px]">No. Pesanan & Pelanggan</th>
                                    <th class="py-3 px-3.5">Rincian Produk</th>
                                    <th class="py-3 px-3.5 text-right min-w-[110px]">Total Tagihan</th>
                                    <th class="py-3 px-3.5 text-center min-w-[120px]">Status Bayar</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-[#E2D7C8] text-xs md:text-sm">
                                <?php foreach ($orders_report as $idx => $ord): ?>
                                    <tr class="transition-colors <?= $idx % 2 === 0 ? 'bg-[#FDFBF7]' : 'bg-[#F8F5F0]' ?> hover:bg-[#F0E9DF] order-row" data-status="<?= htmlspecialchars($ord['status_bayar']) ?>">
                                        
                                        <!-- No -->
                                        <td class="py-3 px-3.5 text-center text-[#8E7E73] font-semibold">
                                            <?= $ord['no'] ?>
                                        </td>

                                        <!-- Tanggal & Jam -->
                                        <td class="py-3 px-3.5">
                                            <p class="font-bold text-[#251A14]"><?= htmlspecialchars($ord['tanggal']) ?></p>
                                            <p class="text-[11px] text-[#8E7E73]"><?= htmlspecialchars($ord['jam']) ?></p>
                                        </td>

                                        <!-- No. Pesanan & Pelanggan -->
                                        <td class="py-3 px-3.5">
                                            <div class="flex items-center gap-1.5">
                                                <span class="font-mono font-bold text-[#924C00]"><?= htmlspecialchars($ord['order_id']) ?></span>
                                                <span class="text-[#8E7E73]">—</span>
                                                <span class="font-bold text-[#251A14]"><?= htmlspecialchars($ord['customer']) ?></span>
                                            </div>
                                        </td>

                                        <!-- Rincian Produk -->
                                        <td class="py-3 px-3.5 text-[#5E4D42] truncate max-w-xs">
                                            <?= htmlspecialchars($ord['items']) ?>
                                        </td>

                                        <!-- Total Tagihan -->
                                        <td class="py-3 px-3.5 text-right font-bold text-[#251A14]">
                                            <?= htmlspecialchars($ord['total']) ?>
                                        </td>

                                        <!-- Status Bayar Badge (Pastikan selalu terlihat jelas) -->
                                        <td class="py-3 px-3.5 text-center">
                                            <span class="inline-block px-2.5 py-1 rounded-full text-xs font-semibold <?= $ord['status_bayar_class'] ?>">
                                                <?= htmlspecialchars($ord['status_bayar_label'] ?? $ord['status_bayar']) ?>
                                            </span>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>

                    <!-- Pagination Footer (Akurat & Dinamis) -->
                    <div class="px-6 py-4 border-t border-[#E2D7C8] flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3 text-xs text-[#8E7E73] bg-[#FDFBF7]">
                        <span>Menampilkan 1-<?= count($orders_report) ?> dari <?= count($orders_report) ?> pesanan</span>
                        
                        <div class="flex items-center gap-1.5">
                            <button class="w-7 h-7 rounded-md text-[#8E7E73] opacity-50 cursor-default flex items-center justify-center font-bold">‹</button>
                            <button class="w-7 h-7 rounded-md bg-[#D46E15] text-white font-bold text-xs flex items-center justify-center shadow-xs">1</button>
                            <button class="w-7 h-7 rounded-md text-[#8E7E73] opacity-50 cursor-default flex items-center justify-center font-bold">›</button>
                        </div>
                    </div>

                </div>

            </main>
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
                    <i class="fas fa-sign-out-alt"></i>
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
        const reportData = <?= json_encode($orders_report) ?>;

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

        // Header Popovers
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

        function showToast(text) {
            const toast = document.getElementById('dynamicToast');
            document.getElementById('dynamicToastText').textContent = text;
            toast.classList.remove('hidden');
            setTimeout(() => {
                toast.classList.add('hidden');
            }, 3000);
        }

        // Apply Period Filter
        function applyPeriodFilter() {
            const select = document.getElementById('periodeSelect');
            const periodText = select.options[select.selectedIndex].text;
            showToast(`Laporan difilter untuk: ${periodText}`);
        }

        // Real CSV / Excel Export Action
        function handleExportExcel() {
            let csv = "No,Tanggal,Jam,ID Pesanan,Pelanggan,Rincian Produk,Total Tagihan,Status Bayar\n";
            reportData.forEach(r => {
                csv += `"${r.no}","${r.tanggal}","${r.jam}","${r.order_id}","${r.customer}","${r.items}","${r.total}","${r.status_bayar}"\n`;
            });
            const blob = new Blob([csv], { type: 'text/csv;charset=utf-8;' });
            const url = URL.createObjectURL(blob);
            const link = document.createElement('a');
            link.setAttribute('href', url);
            link.setAttribute('download', `Laporan_Penjualan_Berkat_Dinasti_${new Date().toISOString().slice(0,10)}.csv`);
            document.body.appendChild(link);
            link.click();
            document.body.removeChild(link);
            showToast('Berkas Excel/CSV laporan berhasil diunduh!');
        }

        // Switch Chart Mode (Omset vs Jumlah Pesanan)
        function toggleChartMode(mode) {
            const btnOmset = document.getElementById('btnChartOmset');
            const btnOrder = document.getElementById('btnChartOrder');

            if (mode === 'omset') {
                btnOmset.className = 'px-3 py-1.5 rounded-lg text-xs font-semibold bg-[#D46E15] text-white shadow-2xs transition-all';
                btnOrder.className = 'px-3 py-1.5 rounded-lg text-xs font-semibold text-[#8E7E73] hover:text-[#251A14] transition-all';
            } else {
                btnOrder.className = 'px-3 py-1.5 rounded-lg text-xs font-semibold bg-[#D46E15] text-white shadow-2xs transition-all';
                btnOmset.className = 'px-3 py-1.5 rounded-lg text-xs font-semibold text-[#8E7E73] hover:text-[#251A14] transition-all';
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
                if (btn) btn.className = 'px-3 py-1.5 rounded-lg bg-[#EFE8DE] border border-[#DDD3C5] text-[#5E4D42] hover:bg-[#E5DDD0] transition-colors';
            });
            activeBtn.className = 'px-3 py-1.5 rounded-lg bg-[#3E2B21] text-white shadow-2xs transition-colors';

            const rows = document.querySelectorAll('#ordersReportTable tbody tr');
            let count = 0;
            rows.forEach(row => {
                const rowStatus = row.getAttribute('data-status');
                if (status === 'all' || rowStatus === status) {
                    row.style.display = '';
                    count++;
                } else {
                    row.style.display = 'none';
                }
            });
            document.getElementById('tableRecordCountBadge').textContent = count + ' Data';
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

        // Close on Escape key & Outside Click
        document.addEventListener('keydown', function(e) {
            if (e.key === 'Escape') {
                closeLogoutModal();
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
    </script>
</body>
</html>
