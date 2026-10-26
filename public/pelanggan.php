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

// Default Dataset Pelanggan jika belum ada di Session
if (!isset($_SESSION['pelanggan_list'])) {
    $_SESSION['pelanggan_list'] = [
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
                    'amount' => 'Rp 144.000',
                    'delivery_status' => 'Siap Kirim >',
                    'delivery_status_class' => 'text-[#924C00] font-semibold',
                    'date_time' => '🕒 Hari Ini, 14:00',
                    'items' => '10 box Roti Hajatan Putih, 2 mika Bolu Pisang'
                ],
                [
                    'order_no' => '#ORD-20250918-03',
                    'status_bayar' => 'Lunas',
                    'status_bayar_class' => 'bg-[#27AE60]/15 text-[#1B7A43]',
                    'amount' => 'Rp 150.000',
                    'delivery_status' => '✓ Selesai Diterima >',
                    'delivery_status_class' => 'text-[#27AE60] font-semibold',
                    'date_time' => '📅 18 Sep 2025',
                    'items' => '10 box Roti Hajatan Batik, 2 box Bolu Tanpa Pisang'
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
            'catatan' => 'Minta kardus roti hajatan batik diikat tali rafia rapat saat diambil sore.',
            'antrean' => 1,
            'history' => [
                [
                    'order_no' => '#ORD-20250926-02',
                    'status_bayar' => 'Hutang',
                    'status_bayar_class' => 'bg-[#EB5757]/15 text-[#EB5757]',
                    'amount' => 'Rp 110.000',
                    'delivery_status' => 'Antrean Oven >',
                    'delivery_status_class' => 'text-[#D97706] font-semibold',
                    'date_time' => '🕒 Hari Ini, 16:30',
                    'items' => 'Roti Hajatan Kardus Batik (10 box)'
                ]
            ]
        ],
        [
            'id' => 3,
            'avatar' => 'MJ',
            'nama' => 'Toko Maju Jaya',
            'no_wa' => '0834-5678-9012',
            'wa_clean' => '6283456789012',
            'alamat' => 'Jl. Raya Industri No. 20, Ruko Sentosa No. 4, Malang',
            'alamat_short' => 'Jl. Raya Industri No. 20, Ruko S...',
            'pesanan_count' => 25,
            'tagihan' => 'Lunas',
            'tagihan_class' => 'bg-[#27AE60]/15 text-[#1B7A43]',
            'total_transaksi' => 'Rp 1.250.000',
            'total_raw' => 1250000,
            'tipe' => 'Mitra Reseller',
            'member_since' => 'Sejak Nov 2024',
            'catatan' => 'Kirim menggunakan mobil bak / driver roda tiga.',
            'antrean' => 0,
            'history' => []
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
            'history' => []
        ],
        [
            'id' => 5,
            'avatar' => 'KS',
            'nama' => 'Kafe Kopi Seduh',
            'no_wa' => '0812-9876-5432',
            'wa_clean' => '6281298765432',
            'alamat' => 'Jl. Anggrek No. 14, Sentra Kuliner Batu',
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
            'history' => []
        ]
    ];
}

// POST Action Handlers (CRUD Backend)
$toast_message = null;
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';

    if ($action === 'save_customer') {
        $edit_id = intval($_POST['customer_id'] ?? 0);
        $nama = trim($_POST['nama'] ?? '');
        $no_wa = trim($_POST['no_wa'] ?? '');
        $tipe = trim($_POST['tipe'] ?? 'Pelanggan Reguler');
        $alamat = trim($_POST['alamat'] ?? '');
        $catatan = trim($_POST['catatan'] ?? '');

        // Bersihkan WA untuk wa.me link
        $wa_clean = preg_replace('/[^0-9]/', '', $no_wa);
        if (substr($wa_clean, 0, 1) === '0') {
            $wa_clean = '62' . substr($wa_clean, 1);
        }

        // Generate avatar initials
        $words = explode(' ', $nama);
        $avatar = '';
        foreach ($words as $w) {
            if (!empty($w)) $avatar .= strtoupper(substr($w, 0, 1));
            if (strlen($avatar) >= 2) break;
        }
        if (empty($avatar)) $avatar = 'PL';

        $alamat_short = strlen($alamat) > 30 ? substr($alamat, 0, 27) . '...' : $alamat;

        if ($edit_id > 0) {
            // Update customer
            foreach ($_SESSION['pelanggan_list'] as &$c) {
                if ($c['id'] === $edit_id) {
                    $c['nama'] = $nama;
                    $c['no_wa'] = $no_wa;
                    $c['wa_clean'] = $wa_clean;
                    $c['tipe'] = $tipe;
                    $c['alamat'] = $alamat;
                    $c['alamat_short'] = $alamat_short;
                    $c['catatan'] = $catatan;
                    $c['avatar'] = $avatar;
                    break;
                }
            }
            unset($c);
            $toast_message = "Data pelanggan \"{$nama}\" berhasil diperbarui!";
        } else {
            // Tambah pelanggan baru
            $new_id = time();
            $new_cust = [
                'id' => $new_id,
                'avatar' => $avatar,
                'nama' => $nama,
                'no_wa' => $no_wa,
                'wa_clean' => $wa_clean,
                'alamat' => $alamat,
                'alamat_short' => $alamat_short,
                'pesanan_count' => 0,
                'tagihan' => 'Lunas',
                'tagihan_class' => 'bg-[#27AE60]/15 text-[#1B7A43]',
                'total_transaksi' => 'Rp 0',
                'total_raw' => 0,
                'tipe' => $tipe,
                'member_since' => 'Sejak ' . date('M Y'),
                'catatan' => $catatan ?: 'Pelanggan baru terdaftar.',
                'antrean' => 0,
                'history' => []
            ];
            array_unshift($_SESSION['pelanggan_list'], $new_cust);
            $toast_message = "Pelanggan baru \"{$nama}\" berhasil didaftarkan ke sistem!";
        }
    } elseif ($action === 'delete_customer') {
        $del_id = intval($_POST['customer_id'] ?? 0);
        if ($del_id > 0) {
            $_SESSION['pelanggan_list'] = array_values(array_filter($_SESSION['pelanggan_list'], function($c) use ($del_id) {
                return $c['id'] !== $del_id;
            }));
            $toast_message = "Data pelanggan berhasil dihapus dari sistem.";
        }
    }
}

$pelanggan_list = $_SESSION['pelanggan_list'];

// Helper Tanggal Dinamis Bahasa Indonesia
$daysIndo = ['Sunday' => 'Minggu', 'Monday' => 'Senin', 'Tuesday' => 'Selasa', 'Wednesday' => 'Rabu', 'Thursday' => 'Kamis', 'Friday' => 'Jumat', 'Saturday' => 'Sabtu'];
$monthsIndo = ['01' => 'Jan', '02' => 'Feb', '03' => 'Mar', '04' => 'Apr', '05' => 'Mei', '06' => 'Jun', '07' => 'Jul', '08' => 'Agu', '09' => 'Sep', '10' => 'Okt', '11' => 'Nov', '12' => 'Des'];
$dayName = $daysIndo[date('l')] ?? date('l');
$dayNum = date('d');
$monthNum = date('m');
$yearNum = date('Y');
$todayFormatted = $dayName . ", " . $dayNum . " " . ($monthsIndo[$monthNum] ?? date('M')) . " " . $yearNum;

// Summary KPI counts for Pelanggan
$totalCust = count($pelanggan_list);
$totalReseller = count(array_filter($pelanggan_list, fn($c) => ($c['tipe'] ?? '') === 'Mitra Reseller'));
$totalOrders = array_sum(array_column($pelanggan_list, 'pesanan_count'));
$totalHutang = count(array_filter($pelanggan_list, fn($c) => in_array($c['tagihan'] ?? '', ['Hutang', 'Belum Bayar'])));
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pelanggan - Berkat Dinasti</title>
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

                    <!-- Pelanggan (Active) -->
                    <a href="pelanggan.php" class="bg-[#D46E15] text-white rounded-lg px-4 py-3 flex items-center gap-3 font-semibold text-sm transition-all shadow-sm">
                        <i class="fas fa-users w-5 text-center text-base"></i>
                        <span>Pelanggan</span>
                    </a>

                    <!-- Laporan -->
                    <a href="laporan.php" class="text-white/80 hover:text-white hover:bg-white/10 rounded-lg px-4 py-3 flex items-center gap-3 font-medium text-sm transition-colors">
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
                            <span class="text-[#D46E15] font-semibold">Pelanggan</span>
                        </div>
                        <!-- Section Heading -->
                        <h1 class="text-[#251A14] font-bold text-base md:text-lg leading-tight mt-0.5">Data Pelanggan & Mitra</h1>
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
                                <span class="font-semibold text-xs text-[#251A14]">Notifikasi Operasional</span>
                                <span class="text-[10px] craft-badge-warning font-medium px-2 py-0.5 rounded-full">2 Info</span>
                            </div>
                            <div class="divide-y divide-[#F0ECE9]/70 max-h-72 overflow-y-auto py-1">
                                <div class="px-3.5 py-2.5 hover:bg-[#F8F5F1] transition-colors flex items-start gap-3 rounded-lg mx-1 cursor-pointer">
                                    <span class="w-2 h-2 rounded-full bg-amber-500 mt-1.5 shrink-0"></span>
                                    <div>
                                        <p class="text-xs font-medium text-[#251A14]">Piutang Pak Budi Santoso</p>
                                        <p class="text-[11px] text-[#5E4D42] mt-0.5">Tagihan Rp 95.000 menunggu pelunasan saat ambil barang.</p>
                                        <span class="text-[10px] text-[#8E7E73]">Hari Ini</span>
                                    </div>
                                </div>
                                <div class="px-3.5 py-2.5 hover:bg-[#F8F5F1] transition-colors flex items-start gap-3 rounded-lg mx-1 cursor-pointer">
                                    <span class="w-2 h-2 rounded-full bg-emerald-500 mt-1.5 shrink-0"></span>
                                    <div>
                                        <p class="text-xs font-medium text-[#251A14]">Mitra Toko Maju Jaya</p>
                                        <p class="text-[11px] text-[#5E4D42] mt-0.5">Total transaksi mencapai Rp 1.250.000 (25 pesanan).</p>
                                        <span class="text-[10px] text-[#8E7E73]">Kemarin</span>
                                    </div>
                                </div>
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
                
                <!-- Toast Notification Banner -->
                <?php if ($toast_message): ?>
                    <div id="toastNotification" class="mb-6 bg-emerald-50 border border-emerald-200 text-emerald-800 px-4 py-3 rounded-xl flex items-center justify-between shadow-xs animate-in fade-in">
                        <div class="flex items-center gap-2.5">
                            <i class="fas fa-check-circle text-emerald-600"></i>
                            <span class="text-sm font-medium"><?= htmlspecialchars($toast_message) ?></span>
                        </div>
                        <button onclick="document.getElementById('toastNotification').remove()" class="text-emerald-700 hover:text-emerald-900 p-1">
                            <i class="fas fa-times text-xs"></i>
                        </button>
                    </div>
                <?php endif; ?>

                <!-- TOP 4 KPI STATS (Dark Chocolate Espresso Cards) -->
                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
                    <!-- Stat 1: Total Pelanggan -->
                    <div class="craft-card-espresso rounded-xl p-5 border border-white/10 shadow-lg text-[#FAF8F5] relative overflow-hidden">
                        <div class="flex items-center justify-between">
                            <span class="text-[#D8C7B8] text-xs font-semibold tracking-wider uppercase">Total Pelanggan</span>
                            <div class="w-10 h-10 rounded-lg bg-[#D46E15]/20 text-[#D46E15] flex items-center justify-center ring-1 ring-[#D46E15]/30">
                                <i class="fas fa-users text-base"></i>
                            </div>
                        </div>
                        <p class="text-2xl font-bold text-[#FAF8F5] mt-3"><?= $totalCust ?> <span class="text-xs font-normal text-[#D8C7B8]">Kontak</span></p>
                        <p class="text-xs text-[#D8C7B8] mt-1">Pelanggan terdaftar aktif</p>
                    </div>

                    <!-- Stat 2: Mitra Reseller -->
                    <div class="craft-card-espresso rounded-xl p-5 border border-white/10 shadow-lg text-[#FAF8F5] relative overflow-hidden">
                        <div class="flex items-center justify-between">
                            <span class="text-[#D8C7B8] text-xs font-semibold tracking-wider uppercase">Mitra Reseller</span>
                            <div class="w-10 h-10 rounded-lg bg-[#27AE60]/20 text-[#27AE60] flex items-center justify-center ring-1 ring-[#27AE60]/30">
                                <i class="fas fa-store text-base"></i>
                            </div>
                        </div>
                        <p class="text-2xl font-bold text-[#FAF8F5] mt-3"><?= $totalReseller ?> <span class="text-xs font-normal text-[#D8C7B8]">Mitra</span></p>
                        <p class="text-xs text-[#D8C7B8] mt-1">Distributor & toko reseller</p>
                    </div>

                    <!-- Stat 3: Total Transaksi / Repeat Orders -->
                    <div class="craft-card-espresso rounded-xl p-5 border border-white/10 shadow-lg text-[#FAF8F5] relative overflow-hidden">
                        <div class="flex items-center justify-between">
                            <span class="text-[#D8C7B8] text-xs font-semibold tracking-wider uppercase">Total Pesanan</span>
                            <div class="w-10 h-10 rounded-lg bg-[#2F80ED]/20 text-[#2F80ED] flex items-center justify-center ring-1 ring-[#2F80ED]/30">
                                <i class="fas fa-shopping-bag text-base"></i>
                            </div>
                        </div>
                        <p class="text-2xl font-bold text-[#FAF8F5] mt-3"><?= $totalOrders ?> <span class="text-xs font-normal text-[#D8C7B8]">Pesanan</span></p>
                        <p class="text-xs text-[#D8C7B8] mt-1">Akumulasi transaksi bakery</p>
                    </div>

                    <!-- Stat 4: Tagihan Piutang -->
                    <div class="craft-card-espresso rounded-xl p-5 border border-white/10 shadow-lg text-[#FAF8F5] relative overflow-hidden">
                        <div class="flex items-center justify-between">
                            <span class="text-[#D8C7B8] text-xs font-semibold tracking-wider uppercase">Status Piutang</span>
                            <div class="w-10 h-10 rounded-lg bg-[#EB5757]/20 text-[#EB5757] flex items-center justify-center ring-1 ring-[#EB5757]/30">
                                <i class="fas fa-file-invoice-dollar text-base"></i>
                            </div>
                        </div>
                        <p class="text-2xl font-bold text-[#FAF8F5] mt-3"><?= $totalHutang ?> <span class="text-xs font-normal text-[#D8C7B8]">Tertagih</span></p>
                        <p class="text-xs text-[#D8C7B8] mt-1">Perlu pelunasan saat kirim</p>
                    </div>
                </div>

                <!-- TOP FILTER & ACTION BAR -->
                <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 mb-6">
                    
                    <!-- Left: Search & Filter Dropdown -->
                    <div class="flex flex-wrap items-center gap-3 flex-1 max-w-2xl">
                        <!-- Search Box -->
                        <div class="relative flex-1 min-w-[240px]">
                            <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-[#8E7E73]">
                                <i class="fas fa-search text-xs"></i>
                            </div>
                            <input type="text" id="customerSearchInput" onkeyup="filterCustomers()" placeholder="Cari nama pelanggan, no. telp..." 
                                class="w-full bg-[#EFE8DE] border border-[#DDD3C5] rounded-xl pl-10 pr-4 py-2.5 text-xs md:text-sm text-[#251A14] placeholder-[#8E7E73] focus:border-[#D46E15] focus:ring-1 focus:ring-[#D46E15] focus:outline-none shadow-2xs transition-colors">
                        </div>

                        <!-- Dropdown Filter Tagihan -->
                        <div class="relative">
                            <select id="statusFilter" onchange="filterCustomers()" 
                                class="appearance-none bg-[#EFE8DE] border border-[#DDD3C5] rounded-xl pl-3.5 pr-8 py-2.5 text-xs md:text-sm text-[#251A14] font-medium focus:border-[#D46E15] focus:outline-none cursor-pointer shadow-2xs">
                                <option value="all">Status: Semua Tagihan</option>
                                <option value="Lunas">Status: Lunas</option>
                                <option value="Hutang">Status: Hutang</option>
                                <option value="Belum Bayar">Status: Belum Bayar</option>
                            </select>
                            <div class="absolute inset-y-0 right-0 pr-3 flex items-center pointer-events-none text-[#8E7E73]">
                                <i class="fas fa-chevron-down text-[10px]"></i>
                            </div>
                        </div>

                        <!-- Badge Pelanggan Count -->
                        <span class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-full text-xs font-semibold bg-[#EFE8DE] text-[#924C00] border border-[#DDD3C5] shrink-0">
                            <span class="w-2 h-2 rounded-full bg-[#D46E15]"></span>
                            <span id="customerCountBadge"><?= count($pelanggan_list) ?> Pelanggan</span>
                        </span>
                    </div>

                    <!-- Right: Button Ekspor & Tambah Pelanggan -->
                    <div class="flex items-center gap-2.5 self-end sm:self-auto">
                        <button onclick="exportCustomersCSV()" 
                            class="bg-[#EFE8DE] hover:bg-[#E5DDD0] border border-[#DDD3C5] text-[#3E2B21] text-xs md:text-sm font-semibold px-3.5 py-2.5 rounded-xl flex items-center gap-2 transition-colors shadow-2xs">
                            <i class="fas fa-file-export text-xs"></i>
                            <span>Ekspor Data</span>
                        </button>

                        <button onclick="openCustomerModal()" 
                            class="bg-[#D46E15] hover:bg-[#B85207] text-white text-xs md:text-sm font-semibold px-4 py-2.5 rounded-xl flex items-center justify-center gap-2 transition-all shadow-button-orange active:scale-95 shrink-0">
                            <i class="fas fa-user-plus text-xs"></i>
                            <span>+ Tambah Pelanggan</span>
                        </button>
                    </div>
                </div>

                <!-- SECTION 1: DAFTAR KONTAK TABLE CARD -->
                <div class="bg-[#FDFBF7] rounded-2xl border border-[#E2D7C8] shadow-card overflow-hidden mb-6">
                    
                    <!-- Card Top Header -->
                    <div class="px-5 py-4 border-b border-[#E2D7C8] flex items-center justify-between">
                        <div class="flex items-center gap-2.5">
                            <div class="w-7 h-7 rounded-lg bg-[#D46E15]/15 text-[#D46E15] flex items-center justify-center">
                                <i class="fas fa-address-book text-xs"></i>
                            </div>
                            <h3 class="text-[#251A14] font-bold text-sm md:text-base">Daftar Kontak & Pelanggan</h3>
                        </div>
                        <span class="text-xs text-[#8E7E73]">Total Kontak: <?= count($pelanggan_list) ?> terdaftar</span>
                    </div>

                    <!-- Responsive Table -->
                    <div class="overflow-x-auto">
                        <table class="w-full text-left border-collapse" id="customersTable">
                            <!-- Table Header with Dark Chocolate Theme -->
                            <thead>
                                <tr class="bg-[#3E2B21] text-[#FAF8F5] text-xs font-bold uppercase tracking-wider">
                                    <th class="py-3 px-4 text-center w-12">No</th>
                                    <th class="py-3 px-4 min-w-[200px]">Nama Pelanggan</th>
                                    <th class="py-3 px-4 min-w-[220px]">Alamat Pengiriman</th>
                                    <th class="py-3 px-4 text-center min-w-[110px]">Pesanan</th>
                                    <th class="py-3 px-4 text-center min-w-[110px]">Tagihan</th>
                                    <th class="py-3 px-4 text-right min-w-[130px]">Total Transaksi</th>
                                    <th class="py-3 px-4 text-center min-w-[110px]">Aksi</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-[#E2D7C8] text-xs md:text-sm">
                                <?php foreach ($pelanggan_list as $idx => $cust): ?>
                                    <tr id="custRow-<?= $cust['id'] ?>" onclick="selectCustomer(<?= $cust['id'] ?>)" 
                                        class="cursor-pointer transition-colors <?= $idx === 0 ? 'bg-[#EFE8DE] border-l-4 border-l-[#D46E15]' : ($idx % 2 === 0 ? 'bg-[#FDFBF7]' : 'bg-[#F8F5F0]') ?> hover:bg-[#F0E9DF]">
                                        
                                        <!-- No Column -->
                                        <td class="py-3.5 px-4 text-center">
                                            <span class="w-6 h-6 rounded-full <?= $idx === 0 ? 'bg-[#D46E15] text-white' : 'bg-[#E2D7C8] text-[#5E4D42]' ?> font-bold text-xs inline-flex items-center justify-center">
                                                <?= $idx + 1 ?>
                                            </span>
                                        </td>

                                        <!-- Nama & Phone -->
                                        <td class="py-3.5 px-4">
                                            <div class="flex items-center gap-3">
                                                <div class="w-9 h-9 rounded-full bg-[#D46E15]/20 text-[#924C00] font-bold text-xs flex items-center justify-center shrink-0">
                                                    <?= htmlspecialchars($cust['avatar']) ?>
                                                </div>
                                                <div class="truncate">
                                                    <p class="font-bold text-[#251A14] leading-tight customer-name truncate"><?= htmlspecialchars($cust['nama']) ?></p>
                                                    <div class="flex items-center gap-1.5 text-[#8E7E73] text-[11px] mt-0.5">
                                                        <i class="fas fa-phone-alt text-[10px]"></i>
                                                        <span class="customer-phone"><?= htmlspecialchars($cust['no_wa']) ?></span>
                                                    </div>
                                                </div>
                                            </div>
                                        </td>

                                        <!-- Alamat -->
                                        <td class="py-3.5 px-4 text-[#5E4D42] truncate max-w-xs">
                                            <?= htmlspecialchars($cust['alamat_short']) ?>
                                        </td>

                                        <!-- Pesanan Count Badge -->
                                        <td class="py-3.5 px-4 text-center">
                                            <span class="bg-[#EFE8DE] text-[#3E2B21] font-semibold text-xs px-2.5 py-1 rounded-full inline-block border border-[#DDD3C5]">
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
                                        <td class="py-3.5 px-4 text-right font-bold text-[#251A14]">
                                            <?= htmlspecialchars($cust['total_transaksi']) ?>
                                        </td>

                                        <!-- Action Buttons -->
                                        <td class="py-3.5 px-4 text-center" onclick="event.stopPropagation()">
                                            <div class="flex items-center justify-center gap-2">
                                                <!-- View -->
                                                <button onclick="selectCustomer(<?= $cust['id'] ?>)" title="Lihat Profil" 
                                                    class="p-1.5 text-[#8E7E73] hover:text-[#D46E15] hover:bg-[#EFE8DE] rounded-lg transition-colors">
                                                    <i class="fas fa-eye text-xs"></i>
                                                </button>
                                                <!-- Edit -->
                                                <button onclick="openCustomerModal(<?= $cust['id'] ?>)" title="Edit Pelanggan" 
                                                    class="p-1.5 text-[#8E7E73] hover:text-[#2F80ED] hover:bg-[#EFE8DE] rounded-lg transition-colors">
                                                    <i class="fas fa-edit text-xs"></i>
                                                </button>
                                                <!-- Delete -->
                                                <button onclick="handleDeleteCustomer(<?= $cust['id'] ?>, '<?= htmlspecialchars($cust['nama']) ?>')" title="Hapus Pelanggan" 
                                                    class="p-1.5 text-[#8E7E73] hover:text-[#EB5757] hover:bg-rose-50 rounded-lg transition-colors">
                                                    <i class="fas fa-trash-alt text-xs"></i>
                                                </button>
                                            </div>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>

                    <!-- Pagination Footer (Akurat & Dinamis) -->
                    <div class="px-5 py-4 border-t border-[#E2D7C8] flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3 text-xs text-[#8E7E73] bg-[#FDFBF7]">
                        <span>Menampilkan 1-<?= count($pelanggan_list) ?> dari <?= count($pelanggan_list) ?> pelanggan</span>
                        
                        <div class="flex items-center gap-1.5">
                            <button class="px-2.5 py-1 text-[#8E7E73] rounded-md font-medium cursor-default opacity-50">Prev</button>
                            <button class="w-7 h-7 rounded-md bg-[#D46E15] text-white font-bold text-xs flex items-center justify-center shadow-xs">1</button>
                            <button class="px-2.5 py-1 text-[#8E7E73] rounded-md font-medium cursor-default opacity-50">Next</button>
                        </div>
                    </div>

                </div>

                <!-- SECTION 2: BOTTOM DETAILS (2 COLUMNS: DETAIL PELANGGAN & RIWAYAT PESANAN) -->
                <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                    
                    <!-- LEFT CARD: DETAIL PELANGGAN TERPILIH -->
                    <div class="bg-[#FDFBF7] rounded-2xl border border-[#E2D7C8] p-6 shadow-card flex flex-col justify-between">
                        <div>
                            <!-- Header with Avatar & Status -->
                            <div class="flex items-start justify-between pb-4 border-b border-[#E2D7C8] mb-4">
                                <div class="flex items-center gap-3">
                                    <div class="w-12 h-12 rounded-full bg-[#D46E15] text-white font-bold text-base flex items-center justify-center shadow-sm" id="detailAvatar">
                                        <?= htmlspecialchars($pelanggan_list[0]['avatar'] ?? 'SD') ?>
                                    </div>
                                    <div>
                                        <h3 class="text-[#251A14] font-bold text-base" id="detailNama"><?= htmlspecialchars($pelanggan_list[0]['nama'] ?? 'Ibu Sari Dewi') ?></h3>
                                        <p class="text-xs text-[#8E7E73]" id="detailTipe"><?= htmlspecialchars($pelanggan_list[0]['tipe'] ?? 'Pelanggan Reguler') ?></p>
                                    </div>
                                </div>
                                <span class="bg-[#EFE8DE] text-[#3E2B21] border border-[#DDD3C5] text-[11px] font-semibold px-2.5 py-1 rounded-full" id="detailMemberSince">
                                    <?= htmlspecialchars($pelanggan_list[0]['member_since'] ?? 'Sejak Jan 2025') ?>
                                </span>
                            </div>

                            <!-- Detail Information List -->
                            <div class="space-y-3 text-xs">
                                <div>
                                    <span class="text-[#8E7E73] block text-[11px] font-semibold uppercase tracking-wider">Nomor WhatsApp:</span>
                                    <div class="flex items-center gap-2 mt-0.5">
                                        <span class="font-bold text-[#251A14]" id="detailNoWa"><?= htmlspecialchars($pelanggan_list[0]['no_wa'] ?? '0812-3456-7890') ?></span>
                                        <a href="https://wa.me/<?= htmlspecialchars($pelanggan_list[0]['wa_clean'] ?? '6281234567890') ?>" id="detailWaLink" target="_blank" 
                                            class="text-[#27AE60] hover:underline font-semibold flex items-center gap-1.5 text-[11px]">
                                            <i class="fab fa-whatsapp text-sm"></i>
                                            <span>Chat WA</span>
                                            <span>&rarr;</span>
                                        </a>
                                    </div>
                                </div>

                                <div>
                                    <span class="text-[#8E7E73] block text-[11px] font-semibold uppercase tracking-wider">Alamat Lengkap:</span>
                                    <p class="text-[#5E4D42] mt-0.5 leading-relaxed font-medium" id="detailAlamat">
                                        <?= htmlspecialchars($pelanggan_list[0]['alamat'] ?? 'Jl. Mawar No. 5, RT 02/04') ?>
                                    </p>
                                </div>

                                <div class="bg-[#EFE8DE] p-3 rounded-xl border border-[#DDD3C5]">
                                    <p class="text-xs text-[#5E4D42] leading-relaxed italic" id="detailCatatan">
                                        Catatan: <?= htmlspecialchars($pelanggan_list[0]['catatan'] ?? '-') ?>
                                    </p>
                                </div>
                            </div>
                        </div>

                        <!-- 3 Stats Cluster in Customer Card -->
                        <div class="grid grid-cols-3 gap-3 pt-5 mt-5 border-t border-[#E2D7C8] text-center">
                            <div class="bg-[#EFE8DE] p-2.5 rounded-xl border border-[#DDD3C5]">
                                <span class="text-[10px] text-[#8E7E73] uppercase font-semibold block">Total Order</span>
                                <span class="text-sm font-bold text-[#251A14]" id="detailTotalOrder"><?= $pelanggan_list[0]['pesanan_count'] ?? 0 ?></span>
                            </div>
                            <div class="bg-[#EFE8DE] p-2.5 rounded-xl border border-[#DDD3C5]">
                                <span class="text-[10px] text-[#8E7E73] uppercase font-semibold block">Akumulasi</span>
                                <span class="text-sm font-bold text-[#27AE60]" id="detailAkumulasi">Rp <?= number_format(($pelanggan_list[0]['total_raw'] ?? 0) / 1000, 0) ?>k</span>
                            </div>
                            <div class="bg-[#EFE8DE] p-2.5 rounded-xl border border-[#DDD3C5]">
                                <span class="text-[10px] text-[#8E7E73] uppercase font-semibold block">Antrean</span>
                                <span class="text-sm font-bold text-[#D46E15]" id="detailAntrean"><?= $pelanggan_list[0]['antrean'] ?? 0 ?></span>
                            </div>
                        </div>

                    </div>

                    <!-- RIGHT CARD: RIWAYAT PESANAN PELANGGAN TERPILIH -->
                    <div class="bg-[#FDFBF7] rounded-2xl border border-[#E2D7C8] p-6 shadow-card flex flex-col justify-between">
                        <div>
                            <!-- Header -->
                            <div class="flex items-center justify-between pb-4 border-b border-[#E2D7C8] mb-4">
                                <div class="flex items-center gap-2">
                                    <h3 class="text-[#251A14] font-bold text-base">Riwayat Pesanan</h3>
                                    <span class="bg-[#D46E15]/15 text-[#924C00] text-xs font-semibold px-2 py-0.5 rounded-full" id="historyCountBadge">
                                        <?= $pelanggan_list[0]['pesanan_count'] ?? 0 ?>
                                    </span>
                                </div>
                                <a href="pesanan.php" class="text-xs text-[#924C00] hover:text-[#D46E15] font-semibold flex items-center gap-1" id="viewAllOrdersLink">
                                    <span>Lihat di Pesanan</span>
                                    <span>&rarr;</span>
                                </a>
                            </div>

                            <!-- List Orders -->
                            <div class="space-y-3" id="orderHistoryContainer">
                                <?php if (!empty($pelanggan_list[0]['history'])): ?>
                                    <?php foreach ($pelanggan_list[0]['history'] as $ord): ?>
                                        <div class="bg-[#EFE8DE] rounded-xl p-3.5 border border-[#DDD3C5]">
                                            <div class="flex items-center justify-between text-xs mb-1">
                                                <div class="flex items-center gap-2">
                                                    <span class="font-mono font-bold text-[#251A14]"><?= htmlspecialchars($ord['order_no']) ?></span>
                                                    <span class="<?= $ord['status_bayar_class'] ?> text-[10px] font-semibold px-2 py-0.5 rounded-full"><?= htmlspecialchars($ord['status_bayar']) ?></span>
                                                </div>
                                                <div class="text-right">
                                                    <span class="font-bold text-[#251A14] block"><?= htmlspecialchars($ord['amount']) ?></span>
                                                    <span class="<?= $ord['delivery_status_class'] ?> text-[11px]"><?= htmlspecialchars($ord['delivery_status']) ?></span>
                                                </div>
                                            </div>
                                            <p class="text-[11px] text-[#8E7E73]"><?= htmlspecialchars($ord['date_time']) ?></p>
                                            <p class="text-xs text-[#5E4D42] mt-1 font-medium"><?= htmlspecialchars($ord['items']) ?></p>
                                        </div>
                                    <?php endforeach; ?>
                                <?php else: ?>
                                    <div class="p-6 text-center text-[#8E7E73] text-xs">
                                        Belum ada riwayat transaksi tercatat untuk pelanggan ini.
                                    </div>
                                <?php endif; ?>
                            </div>
                        </div>

                        <!-- Card Action: Buat Pesanan untuk Pelanggan Ini -->
                        <div class="pt-4 mt-4 border-t border-[#E2D7C8]">
                            <a href="pesanan.php" class="w-full bg-[#D46E15] hover:bg-[#B85207] text-white text-xs font-semibold py-2.5 rounded-xl flex items-center justify-center gap-2 transition-colors shadow-2xs">
                                <i class="fas fa-plus text-xs"></i>
                                <span>Buat Pesanan Baru untuk Pelanggan Ini</span>
                            </a>
                        </div>
                    </div>

                </div>

            </main>
        </div>
    </div>

    <!-- ============================================================== -->
    <!-- MODAL POPOUT: TAMBAH / EDIT PELANGGAN (DILENGKAPI LABEL & ID)   -->
    <!-- ============================================================== -->
    <div id="customerModal" class="fixed inset-0 z-50 hidden flex items-center justify-center p-4">
        <!-- Backdrop with blur -->
        <div onclick="closeCustomerModal()" class="fixed inset-0 craft-modal-overlay"></div>

        <!-- Modal Card Container -->
        <div class="craft-modal-dialog max-w-lg w-full p-6 sm:p-8 relative z-10 animate-in fade-in zoom-in-95 duration-150">
            <!-- Header -->
            <div class="flex items-center justify-between pb-4 border-b border-[#E2D7C8] mb-5">
                <div>
                    <h3 class="text-base sm:text-lg font-bold text-[#251A14]" id="modalTitle">Tambah Pelanggan Baru</h3>
                    <p class="text-xs text-[#8E7E73] mt-0.5">Lengkapi data kontak untuk pengiriman pesanan bakery</p>
                </div>
                <button onclick="closeCustomerModal()" class="text-[#8E7E73] hover:text-[#251A14] p-1.5 rounded-lg hover:bg-[#EFE8DE] transition-colors" title="Tutup" aria-label="Tutup Modal">
                    <i class="fas fa-times text-base"></i>
                </button>
            </div>

            <!-- Form -->
            <form id="customerForm" method="POST" action="pelanggan.php" class="space-y-4">
                <input type="hidden" name="action" value="save_customer">
                <input type="hidden" id="editCustomerId" name="customer_id" value="">

                <!-- Nama Pelanggan -->
                <div>
                    <label for="custNameInput" class="block text-xs font-semibold text-[#251A14] mb-1.5">Nama Lengkap Pelanggan <span class="text-[#EB5757]">*</span></label>
                    <input type="text" id="custNameInput" name="nama" required placeholder="Contoh: Ibu Sari Dewi" 
                        class="w-full bg-[#EFE8DE] border border-[#DDD3C5] rounded-xl px-3.5 py-2.5 text-xs md:text-sm text-[#251A14] placeholder-[#8E7E73] focus:border-[#D46E15] focus:ring-1 focus:ring-[#D46E15] focus:outline-none transition-colors">
                </div>

                <!-- No. WhatsApp & Tipe Pelanggan (2 Columns) -->
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3.5">
                    <div>
                        <label for="custWaInput" class="block text-xs font-semibold text-[#251A14] mb-1.5">No. WhatsApp <span class="text-[#EB5757]">*</span></label>
                        <input type="text" id="custWaInput" name="no_wa" required placeholder="Contoh: 0812-3456-7890" 
                            class="w-full bg-[#EFE8DE] border border-[#DDD3C5] rounded-xl px-3.5 py-2.5 text-xs md:text-sm text-[#251A14] placeholder-[#8E7E73] focus:border-[#D46E15] focus:ring-1 focus:ring-[#D46E15] focus:outline-none transition-colors">
                    </div>

                    <div>
                        <label for="custTypeInput" class="block text-xs font-semibold text-[#251A14] mb-1.5">Tipe Pelanggan</label>
                        <select id="custTypeInput" name="tipe" 
                            class="w-full bg-[#EFE8DE] border border-[#DDD3C5] rounded-xl px-3.5 py-2.5 text-xs md:text-sm text-[#251A14] focus:border-[#D46E15] focus:outline-none transition-colors">
                            <option value="Pelanggan Reguler">Pelanggan Reguler</option>
                            <option value="Mitra Reseller">Mitra Reseller</option>
                            <option value="Instansi / Kantor">Instansi / Kantor</option>
                        </select>
                    </div>
                </div>

                <!-- Alamat Pengiriman -->
                <div>
                    <label for="custAddressInput" class="block text-xs font-semibold text-[#251A14] mb-1.5">Alamat Pengiriman Lengkap <span class="text-[#EB5757]">*</span></label>
                    <textarea id="custAddressInput" name="alamat" rows="2" required placeholder="Contoh: Jl. Mawar No. 5, RT 02/04, Sukajadi, Malang" 
                        class="w-full bg-[#EFE8DE] border border-[#DDD3C5] rounded-xl p-3 text-xs md:text-sm text-[#251A14] placeholder-[#8E7E73] focus:border-[#D46E15] focus:ring-1 focus:ring-[#D46E15] focus:outline-none transition-colors"></textarea>
                </div>

                <!-- Catatan Khusus Pengiriman / Preferensi -->
                <div>
                    <label for="custNotesInput" class="block text-xs font-semibold text-[#251A14] mb-1.5">Catatan Khusus Pengiriman / Preferensi</label>
                    <textarea id="custNotesInput" name="catatan" rows="2" placeholder="Contoh: Pengiriman pagi sebelum jam 10:00 WIB, roti minta diiris tipis..." 
                        class="w-full bg-[#EFE8DE] border border-[#DDD3C5] rounded-xl p-3 text-xs md:text-sm text-[#251A14] placeholder-[#8E7E73] focus:border-[#D46E15] focus:ring-1 focus:ring-[#D46E15] focus:outline-none transition-colors"></textarea>
                </div>

                <!-- Footer Buttons -->
                <div class="flex items-center justify-end gap-3 pt-4 border-t border-[#E2D7C8] mt-6">
                    <button type="button" onclick="closeCustomerModal()" 
                        class="craft-btn-secondary text-xs font-semibold px-4 py-2.5 rounded-xl transition-colors">
                        Batal
                    </button>
                    <button type="submit" 
                        class="bg-[#D46E15] hover:bg-[#B85207] text-white text-xs font-semibold px-5 py-2.5 rounded-xl flex items-center gap-2 transition-all shadow-button-orange active:scale-95">
                        <i class="fas fa-check text-xs"></i>
                        <span id="saveBtnText">Simpan Pelanggan</span>
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- Hidden Form for Delete Customer -->
    <form id="deleteCustomerForm" method="POST" action="pelanggan.php" class="hidden">
        <input type="hidden" name="action" value="delete_customer">
        <input type="hidden" name="customer_id" id="deleteCustomerIdInput">
    </form>

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
        // Master Dataset in JavaScript
        const customersData = <?= json_encode($pelanggan_list, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES) ?>;
        let selectedCustomerId = <?= !empty($pelanggan_list) ? $pelanggan_list[0]['id'] : 1 ?>;

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
            document.querySelectorAll('#customersTable tbody tr').forEach((r, idx) => {
                r.className = 'cursor-pointer transition-colors ' + (idx % 2 === 0 ? 'bg-[#FDFBF7]' : 'bg-[#F8F5F0]') + ' hover:bg-[#F0E9DF]';
                const noBadge = r.querySelector('td:first-child span');
                if (noBadge) {
                    noBadge.className = 'w-6 h-6 rounded-full bg-[#E2D7C8] text-[#5E4D42] font-bold text-xs inline-flex items-center justify-center';
                }
            });

            const activeRow = document.getElementById('custRow-' + id);
            if (activeRow) {
                activeRow.className = 'cursor-pointer transition-colors bg-[#EFE8DE] border-l-4 border-l-[#D46E15]';
                const noBadge = activeRow.querySelector('td:first-child span');
                if (noBadge) {
                    noBadge.className = 'w-6 h-6 rounded-full bg-[#D46E15] text-white font-bold text-xs inline-flex items-center justify-center';
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
            document.getElementById('detailAkumulasi').textContent = 'Rp ' + Math.round((customer.total_raw || 0) / 1000) + 'k';
            document.getElementById('detailAntrean').textContent = customer.antrean;

            // Update WhatsApp Link
            document.getElementById('detailWaLink').href = 'https://wa.me/' + customer.wa_clean;

            // Update Right Card: Riwayat Pesanan
            document.getElementById('historyCountBadge').textContent = customer.pesanan_count;

            const historyContainer = document.getElementById('orderHistoryContainer');
            historyContainer.innerHTML = '';

            if (customer.history && customer.history.length > 0) {
                customer.history.forEach(ord => {
                    const itemHtml = `
                        <div class="bg-[#EFE8DE] rounded-xl p-3.5 border border-[#DDD3C5]">
                            <div class="flex items-center justify-between text-xs mb-1">
                                <div class="flex items-center gap-2">
                                    <span class="font-mono font-bold text-[#251A14]">${ord.order_no}</span>
                                    <span class="${ord.status_bayar_class} text-[10px] font-semibold px-2 py-0.5 rounded-full">${ord.status_bayar}</span>
                                </div>
                                <div class="text-right">
                                    <span class="font-bold text-[#251A14] block">${ord.amount}</span>
                                    <span class="${ord.delivery_status_class} text-[11px]">${ord.delivery_status}</span>
                                </div>
                            </div>
                            <p class="text-[11px] text-[#8E7E73]">${ord.date_time}</p>
                            <p class="text-xs text-[#5E4D42] mt-1 font-medium">${ord.items}</p>
                        </div>
                    `;
                    historyContainer.insertAdjacentHTML('beforeend', itemHtml);
                });
            } else {
                historyContainer.innerHTML = `
                    <div class="p-6 text-center text-[#8E7E73] text-xs">
                        Belum ada riwayat pesanan tercatat untuk pelanggan ini.
                    </div>
                `;
            }
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

        // Delete Customer Handler
        function handleDeleteCustomer(id, nama) {
            if (confirm(`Apakah Anda yakin ingin menghapus pelanggan "${nama}" dari sistem?`)) {
                document.getElementById('deleteCustomerIdInput').value = id;
                document.getElementById('deleteCustomerForm').submit();
            }
        }

        // Export Customers to CSV
        function exportCustomersCSV() {
            let csv = "ID,Nama,WhatsApp,Alamat,Tipe,Total Transaksi,Pesanan,Tagihan\n";
            customersData.forEach(c => {
                csv += `"${c.id}","${c.nama}","${c.no_wa}","${c.alamat}","${c.tipe}","${c.total_transaksi}","${c.pesanan_count}","${c.tagihan}"\n`;
            });
            const blob = new Blob([csv], { type: 'text/csv;charset=utf-8;' });
            const url = URL.createObjectURL(blob);
            const link = document.createElement('a');
            link.setAttribute('href', url);
            link.setAttribute('download', `Daftar_Pelanggan_Berkat_Dinasti_${new Date().toISOString().slice(0,10)}.csv`);
            document.body.appendChild(link);
            link.click();
            document.body.removeChild(link);
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

        // Close on Escape & Outside click
        document.addEventListener('keydown', function(e) {
            if (e.key === 'Escape') {
                closeLogoutModal();
                closeCustomerModal();
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
