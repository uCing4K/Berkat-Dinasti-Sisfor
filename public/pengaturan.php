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

// Inisialisasi Session Store Settings
if (!isset($_SESSION['store_settings'])) {
    $_SESSION['store_settings'] = [
        'name' => 'Berkat Dinasti Bakery',
        'phone' => '0341-551234',
        'wa' => '0812-3456-7890',
        'address' => 'Jl. Terusan Ijen No. 42, Klojen, Kota Malang',
        'province' => 'Jawa Timur',
        'city' => 'Kota Malang',
        'postal' => '65115',
        'email' => 'admin@berkatdinasti.id',
        'footer' => 'Terima kasih telah berbelanja di Berkat Dinasti! Simpan struk ini sebagai bukti pembayaran yang sah.',
        'paper_size' => '80',
        'auto_print' => true,
        'logo' => 'assets/images/logo.png'
    ];
}

// Inisialisasi Users List
if (!isset($_SESSION['users_list'])) {
    $_SESSION['users_list'] = [
        [
            'id' => 1,
            'nama' => 'Admin Utama',
            'subtext' => 'admin@berkatdinasti.id',
            'avatar' => 'AU',
            'avatar_bg' => 'bg-[#3E2B21]',
            'username' => 'admin',
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
            'username' => 'andi.s',
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
            'username' => 'rina.d',
            'role' => 'Karyawan',
            'role_class' => 'bg-[#FF9B45]/15 text-[#924C00]',
            'status' => 'Nonaktif',
            'status_class' => 'text-[#828282]',
            'dot_class' => 'bg-[#828282]',
            'is_locked' => false
        ]
    ];
}

// POST Action Handler
$toast_message = null;
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';

    if ($action === 'save_store_settings') {
        $_SESSION['store_settings']['name'] = trim($_POST['store_name'] ?? 'Berkat Dinasti Bakery');
        $_SESSION['store_settings']['phone'] = trim($_POST['store_phone'] ?? '');
        $_SESSION['store_settings']['wa'] = trim($_POST['store_wa'] ?? '');
        $_SESSION['store_settings']['address'] = trim($_POST['store_address'] ?? '');
        $_SESSION['store_settings']['province'] = trim($_POST['store_province'] ?? 'Jawa Timur');
        $_SESSION['store_settings']['city'] = trim($_POST['store_city'] ?? 'Kota Malang');
        $_SESSION['store_settings']['postal'] = trim($_POST['store_postal'] ?? '');
        $_SESSION['store_settings']['email'] = trim($_POST['store_email'] ?? '');
        $_SESSION['store_settings']['footer'] = trim($_POST['store_footer'] ?? '');
        $_SESSION['store_settings']['paper_size'] = trim($_POST['paper_size'] ?? '80');
        $_SESSION['store_settings']['auto_print'] = isset($_POST['auto_print']);

        $toast_message = "Pengaturan profil toko & format struk kasir berhasil disimpan!";
    } elseif ($action === 'save_user') {
        $user_id = intval($_POST['user_id'] ?? 0);
        $nama = trim($_POST['nama'] ?? '');
        $username = trim($_POST['username'] ?? '');
        $subtext = trim($_POST['subtext'] ?? 'Staf Operasional');
        $role = trim($_POST['role'] ?? 'Karyawan');
        $status = trim($_POST['status'] ?? 'Aktif');

        // Initial avatar
        $avatar = strtoupper(substr($nama, 0, 1) . substr($username, 0, 1));

        if ($user_id > 0) {
            foreach ($_SESSION['users_list'] as &$u) {
                if ($u['id'] === $user_id) {
                    $u['nama'] = $nama;
                    $u['username'] = $username;
                    $u['subtext'] = $subtext;
                    $u['role'] = $role;
                    $u['status'] = $status;
                    $u['status_class'] = ($status === 'Aktif') ? 'text-[#27AE60]' : 'text-[#828282]';
                    $u['dot_class'] = ($status === 'Aktif') ? 'bg-[#27AE60]' : 'bg-[#828282]';
                    break;
                }
            }
            unset($u);
            $toast_message = "Data pengguna \"{$nama}\" berhasil diperbarui!";
        } else {
            $new_user = [
                'id' => time(),
                'nama' => $nama,
                'subtext' => $subtext,
                'avatar' => $avatar,
                'avatar_bg' => 'bg-[#FF9B45]',
                'username' => $username,
                'role' => $role,
                'role_class' => ($role === 'Pemilik (Owner)') ? 'bg-[#3E2B21] text-white' : 'bg-[#FF9B45]/15 text-[#924C00]',
                'status' => $status,
                'status_class' => ($status === 'Aktif') ? 'text-[#27AE60]' : 'text-[#828282]',
                'dot_class' => ($status === 'Aktif') ? 'bg-[#27AE60]' : 'bg-[#828282]',
                'is_locked' => false
            ];
            $_SESSION['users_list'][] = $new_user;
            $toast_message = "Pengguna baru \"{$nama}\" berhasil didaftarkan!";
        }
    } elseif ($action === 'delete_user') {
        $del_id = intval($_POST['user_id'] ?? 0);
        if ($del_id > 1) { // Proteksi admin id 1
            $_SESSION['users_list'] = array_values(array_filter($_SESSION['users_list'], function($u) use ($del_id) {
                return $u['id'] !== $del_id;
            }));
            $toast_message = "Pengguna berhasil dihapus.";
        }
    }
}

$store = $_SESSION['store_settings'];
$users_list = $_SESSION['users_list'];
$active_tab = $_GET['tab'] ?? 'profil';

// Helper Tanggal Dinamis Bahasa Indonesia
$daysIndo = ['Sunday' => 'Minggu', 'Monday' => 'Senin', 'Tuesday' => 'Selasa', 'Wednesday' => 'Rabu', 'Thursday' => 'Kamis', 'Friday' => 'Jumat', 'Saturday' => 'Sabtu'];
$monthsIndo = ['01' => 'Jan', '02' => 'Feb', '03' => 'Mar', '04' => 'Apr', '05' => 'Mei', '06' => 'Jun', '07' => 'Jul', '08' => 'Agu', '09' => 'Sep', '10' => 'Okt', '11' => 'Nov', '12' => 'Des'];
$dayName = $daysIndo[date('l')] ?? date('l');
$dayNum = date('d');
$monthNum = date('m');
$yearNum = date('Y');
$todayFormatted = $dayName . ", " . $dayNum . " " . ($monthsIndo[$monthNum] ?? date('M')) . " " . $yearNum;
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pengaturan - Berkat Dinasti</title>
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

        /* Thermal Struk Print Style */
        @media print {
            body * {
                visibility: hidden;
            }
            #printableThermalReceipt, #printableThermalReceipt * {
                visibility: visible;
            }
            #printableThermalReceipt {
                position: absolute;
                left: 0;
                top: 0;
                width: 80mm;
                margin: 0;
                padding: 10px;
                background: white;
                color: black;
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
                            <a href="pengaturan.php" class="text-[#544337] hover:underline" id="headerBreadcrumb">Pengaturan</a>
                            <span class="text-[#DBC2B2]">/</span>
                            <span class="text-[#924C00] font-medium" id="headerSubtitle"><?= $active_tab === 'user' ? 'Manajemen User' : 'Profil Toko' ?></span>
                        </div>
                        <!-- Section Heading -->
                        <h1 class="text-[#1B1C1B] font-bold text-base md:text-lg leading-tight mt-0.5" id="headerTitle">
                            <?= $active_tab === 'user' ? 'Manajemen Pengguna' : 'Pengaturan Toko' ?>
                        </h1>
                    </div>
                </div>

                <!-- Right: Date Indicator, Notification Bell, User Avatar -->
                <div class="flex items-center gap-3 md:gap-5 relative">
                    
                    <!-- Date Button (Dinamis) -->
                    <div class="hidden sm:flex items-center gap-2 bg-[#F5F3F1] text-[#544337] px-3.5 py-1.5 rounded-lg text-xs md:text-sm font-medium border border-[#EBE8E5]">
                        <img src="assets/icons/header_calendar.svg" alt="Kalender" class="w-4 h-4">
                        <span><?= $todayFormatted ?></span>
                    </div>

                    <!-- Notification Bell with Popover -->
                    <div class="relative">
                        <button onclick="toggleNotificationDropdown()" class="relative p-2 text-[#544337] hover:bg-[#F5F3F1] rounded-lg transition-colors" title="Notifikasi" id="notificationBtn">
                            <img src="assets/icons/bell.svg" alt="Notifikasi" class="w-5 h-5">
                            <span class="absolute top-1.5 right-1.5 w-2 h-2 bg-[#D46E15] rounded-full ring-2 ring-white"></span>
                        </button>

                        <div id="notificationDropdown" class="hidden absolute right-0 mt-2.5 w-80 craft-dropdown py-2 z-50">
                            <div class="px-4 py-2 border-b border-[#ECE5DD] flex items-center justify-between">
                                <span class="font-semibold text-xs text-[#251A14]">Notifikasi Sistem</span>
                                <span class="text-[10px] craft-badge-success font-medium px-2 py-0.5 rounded-full">Normal</span>
                            </div>
                            <div class="p-3.5 text-xs text-[#5E4D42] space-y-1.5">
                                <p>• Profil toko tersinkronisasi otomatis.</p>
                                <p>• Printer thermal siap mencetak (80mm).</p>
                            </div>
                        </div>
                    </div>

                    <!-- User Circle Icon with Popover -->
                    <div class="relative">
                        <div onclick="toggleUserDropdown()" class="w-8 h-8 rounded-full bg-[#924C00] flex items-center justify-center text-white shadow-xs cursor-pointer hover:opacity-95 transition-all active:scale-95" title="Profil Pengguna" id="userAvatarBtn">
                            <img src="assets/icons/header_user.svg" alt="User" class="w-4 h-4">
                        </div>

                        <div id="userDropdown" class="hidden absolute right-0 mt-2.5 w-56 craft-dropdown py-1.5 z-50 text-xs">
                            <div class="px-4 py-2.5 border-b border-[#ECE5DD]">
                                <p class="text-xs font-semibold text-[#251A14] truncate"><?= htmlspecialchars($_SESSION['nama_lengkap'] ?? $_SESSION['user'] ?? 'Admin') ?></p>
                                <p class="text-[11px] text-[#8E7E73] mt-0.5"><?= htmlspecialchars(ucfirst($_SESSION['role'] ?? 'Pemilik')) ?> Toko</p>
                            </div>
                            <div class="py-1">
                                <a href="pengaturan.php" class="craft-dropdown-item">
                                    <span>⚙️</span>
                                    <span>Pengaturan Toko</span>
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

                <!-- TAB SWITCHER BAR (PROFIL TOKO VS MANAJEMEN USER) -->
                <div class="bg-white rounded-xl border border-[#F0ECE9] p-1.5 flex items-center gap-2 mb-6 shadow-2xs max-w-fit">
                    <!-- Tab 1: Profil UMKM -->
                    <button onclick="switchSettingTab('profil')" id="tabBtnProfil" 
                        class="px-4 py-2 rounded-lg text-xs md:text-sm font-semibold flex items-center gap-2 transition-all <?= $active_tab !== 'user' ? 'bg-[#FF9B45] text-white shadow-xs' : 'text-[#828282] hover:text-[#1C1C1C]' ?>">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"></path></svg>
                        <span>Profil Toko & Printer</span>
                    </button>

                    <!-- Tab 2: Manajemen User -->
                    <button onclick="switchSettingTab('user')" id="tabBtnUser" 
                        class="px-4 py-2 rounded-lg text-xs md:text-sm font-semibold flex items-center gap-2 transition-all <?= $active_tab === 'user' ? 'bg-[#FF9B45] text-white shadow-xs' : 'text-[#828282] hover:text-[#1C1C1C]' ?>">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"></path></svg>
                        <span>Manajemen Pengguna</span>
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

                                <!-- Logo Berkat Dinasti Box with Real File Input Preview -->
                                <div class="bg-[#FBF9F7] rounded-xl p-4 border border-[#F0ECE9] flex flex-col sm:flex-row sm:items-center gap-4 mb-5">
                                    <div class="w-16 h-16 rounded-xl bg-white p-1 border border-[#F0ECE9] flex items-center justify-center shrink-0 shadow-2xs overflow-hidden">
                                        <img src="<?= htmlspecialchars($store['logo'] ?? 'assets/images/logo.png') ?>" id="storeLogoPreview" alt="Logo Berkat Dinasti" class="w-full h-full object-contain">
                                    </div>
                                    <div class="flex-1 min-w-0">
                                        <div class="flex items-center gap-2">
                                            <h4 class="text-sm font-bold text-[#1C1C1C]">Logo Toko Bakery</h4>
                                            <span class="w-4 h-4 rounded-full bg-[#27AE60] text-white text-[10px] flex items-center justify-center font-bold">✓</span>
                                        </div>
                                        <p class="text-xs text-[#828282] mt-0.5">Format PNG/JPG transparan resolusi minimum 500 × 500 px (Maks. 2MB)</p>
                                        
                                        <!-- Hidden File Input for Logo -->
                                        <input type="file" id="logoFileInput" accept="image/*" class="hidden" onchange="previewLogo(this)">

                                        <div class="flex items-center gap-2.5 mt-3">
                                            <button type="button" onclick="document.getElementById('logoFileInput').click()" 
                                                class="bg-[#FF9B45] hover:bg-[#E88C3D] text-white text-xs font-semibold px-3 py-1.5 rounded-lg flex items-center gap-1.5 shadow-2xs transition-colors">
                                                <span>⬆</span>
                                                <span>Ubah Logo</span>
                                            </button>
                                            <button type="button" onclick="resetLogo()" 
                                                class="bg-white hover:bg-gray-100 text-[#544337] text-xs font-medium px-3 py-1.5 rounded-lg border border-[#E0E0E0] transition-colors">
                                                Reset
                                            </button>
                                        </div>
                                    </div>
                                </div>

                                <!-- Form Fields (Equipped with Label & ID for Accessibility) -->
                                <form id="storeSettingsForm" method="POST" action="pengaturan.php" class="space-y-4">
                                    <input type="hidden" name="action" value="save_store_settings">

                                    <!-- Nama Toko -->
                                    <div>
                                        <div class="flex items-center justify-between mb-1.5">
                                            <label for="storeNameInput" class="block text-xs font-semibold text-[#333333]">Nama Resmi UMKM / Toko <span class="text-[#EB5757]">*</span></label>
                                            <span class="text-[11px] text-[#828282]">Ditampilkan di struk & faktur</span>
                                        </div>
                                        <div class="relative">
                                            <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-[#828282]">
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"></path></svg>
                                            </div>
                                            <input type="text" id="storeNameInput" name="store_name" value="<?= htmlspecialchars($store['name']) ?>" required
                                                onkeyup="updateReceiptPreview()"
                                                class="w-full bg-white border border-[#E0E0E0] rounded-xl pl-10 pr-4 py-2.5 text-xs md:text-sm text-[#1C1C1C] font-semibold focus:border-[#FF9B45] focus:outline-none transition-colors">
                                        </div>
                                    </div>

                                    <!-- 2 Cols: Phone & WA -->
                                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                                        <div>
                                            <label for="storePhoneInput" class="block text-xs font-semibold text-[#333333] mb-1.5">No. Telepon Toko</label>
                                            <input type="text" id="storePhoneInput" name="store_phone" value="<?= htmlspecialchars($store['phone']) ?>" 
                                                class="w-full bg-white border border-[#E0E0E0] rounded-xl px-3.5 py-2.5 text-xs md:text-sm text-[#1C1C1C] focus:border-[#FF9B45] focus:outline-none transition-colors">
                                        </div>

                                        <div>
                                            <label for="storeWaInput" class="block text-xs font-semibold text-[#333333] mb-1.5">WhatsApp Bisnis</label>
                                            <input type="text" id="storeWaInput" name="store_wa" value="<?= htmlspecialchars($store['wa']) ?>" 
                                                onkeyup="updateReceiptPreview()"
                                                class="w-full bg-white border border-[#E0E0E0] rounded-xl px-3.5 py-2.5 text-xs md:text-sm text-[#1C1C1C] focus:border-[#FF9B45] focus:outline-none transition-colors">
                                        </div>
                                    </div>

                                    <!-- Alamat Lengkap Operasional Toko -->
                                    <div>
                                        <label for="storeAddressInput" class="block text-xs font-semibold text-[#333333] mb-1.5">Alamat Lengkap Operasional Toko</label>
                                        <textarea id="storeAddressInput" name="store_address" rows="2" 
                                            onkeyup="updateReceiptPreview()"
                                            class="w-full bg-white border border-[#E0E0E0] rounded-xl p-3 text-xs md:text-sm text-[#1C1C1C] focus:border-[#FF9B45] focus:outline-none transition-colors"><?= htmlspecialchars($store['address']) ?></textarea>
                                    </div>

                                    <!-- 2 Cols: Provinsi & Kota -->
                                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                                        <div>
                                            <label for="storeProvinceSelect" class="block text-xs font-semibold text-[#333333] mb-1.5">Provinsi</label>
                                            <select id="storeProvinceSelect" name="store_province" class="w-full appearance-none bg-white border border-[#E0E0E0] rounded-xl pl-3.5 pr-8 py-2.5 text-xs md:text-sm text-[#1C1C1C] font-medium focus:border-[#FF9B45] focus:outline-none transition-colors cursor-pointer">
                                                <option value="Jawa Timur" <?= ($store['province'] === 'Jawa Timur') ? 'selected' : '' ?>>Jawa Timur</option>
                                                <option value="Jawa Barat" <?= ($store['province'] === 'Jawa Barat') ? 'selected' : '' ?>>Jawa Barat</option>
                                                <option value="DKI Jakarta" <?= ($store['province'] === 'DKI Jakarta') ? 'selected' : '' ?>>DKI Jakarta</option>
                                            </select>
                                        </div>

                                        <div>
                                            <label for="storeCitySelect" class="block text-xs font-semibold text-[#333333] mb-1.5">Kota / Kabupaten</label>
                                            <select id="storeCitySelect" name="store_city" class="w-full appearance-none bg-white border border-[#E0E0E0] rounded-xl pl-3.5 pr-8 py-2.5 text-xs md:text-sm text-[#1C1C1C] font-medium focus:border-[#FF9B45] focus:outline-none transition-colors cursor-pointer">
                                                <option value="Kota Malang" <?= ($store['city'] === 'Kota Malang') ? 'selected' : '' ?>>Kota Malang</option>
                                                <option value="Kota Batu" <?= ($store['city'] === 'Kota Batu') ? 'selected' : '' ?>>Kota Batu</option>
                                                <option value="Kabupaten Malang" <?= ($store['city'] === 'Kabupaten Malang') ? 'selected' : '' ?>>Kabupaten Malang</option>
                                            </select>
                                        </div>
                                    </div>

                                    <!-- 2 Cols: Kode Pos & Email -->
                                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                                        <div>
                                            <label for="storePostalInput" class="block text-xs font-semibold text-[#333333] mb-1.5">Kode Pos</label>
                                            <input type="text" id="storePostalInput" name="store_postal" value="<?= htmlspecialchars($store['postal']) ?>" 
                                                class="w-full bg-white border border-[#E0E0E0] rounded-xl px-3.5 py-2.5 text-xs md:text-sm text-[#1C1C1C] focus:border-[#FF9B45] focus:outline-none transition-colors">
                                        </div>

                                        <div>
                                            <label for="storeEmailInput" class="block text-xs font-semibold text-[#333333] mb-1.5">Email Bisnis</label>
                                            <input type="email" id="storeEmailInput" name="store_email" value="<?= htmlspecialchars($store['email']) ?>" 
                                                class="w-full bg-white border border-[#E0E0E0] rounded-xl px-3.5 py-2.5 text-xs md:text-sm text-[#1C1C1C] focus:border-[#FF9B45] focus:outline-none transition-colors">
                                        </div>
                                    </div>

                                    <!-- CARD 2: PENGATURAN STRUK PRINTER -->
                                    <div class="pt-6 mt-6 border-t border-[#F0ECE9]">
                                        <div class="flex items-center justify-between pb-3 mb-4">
                                            <div class="flex items-center gap-2">
                                                <span>🖨️</span>
                                                <h4 class="text-sm font-bold text-[#1C1C1C]">Pengaturan Struk Kasir (Thermal POS)</h4>
                                            </div>
                                            <span class="bg-[#FF9B45]/15 text-[#924C00] text-xs font-semibold px-2.5 py-0.5 rounded-full">
                                                Bluetooth & USB
                                            </span>
                                        </div>

                                        <!-- Pilihan Ukuran Kertas Thermal -->
                                        <div class="mb-4">
                                            <label class="block text-xs font-semibold text-[#333333] mb-2">Pilihan Ukuran Kertas Thermal:</label>
                                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-3.5">
                                                <label class="border border-[#E0E0E0] rounded-xl p-3.5 flex items-center gap-3 cursor-pointer hover:border-[#FF9B45] transition-colors">
                                                    <input type="radio" id="storePaperSize58" name="paper_size" value="58" <?= ($store['paper_size'] === '58') ? 'checked' : '' ?> class="text-[#FF9B45] focus:ring-[#FF9B45]">
                                                    <div>
                                                        <p class="text-xs font-bold text-[#1C1C1C]">58 mm (Mini Portable POS)</p>
                                                        <p class="text-[11px] text-[#828282] mt-0.5">Lebar 32 karakter per baris</p>
                                                    </div>
                                                </label>

                                                <label class="border-2 border-[#FF9B45] bg-[#FFF8F2] rounded-xl p-3.5 flex items-center justify-between gap-3 cursor-pointer">
                                                    <div class="flex items-center gap-3">
                                                        <input type="radio" id="storePaperSize80" name="paper_size" value="80" <?= ($store['paper_size'] === '80') ? 'checked' : '' ?> class="text-[#FF9B45] focus:ring-[#FF9B45]">
                                                        <div>
                                                            <p class="text-xs font-bold text-[#924C00]">80 mm (Standar Desktop Kasir)</p>
                                                            <p class="text-[11px] text-[#544337] mt-0.5">Lebar 48 karakter • Terpilih</p>
                                                        </div>
                                                    </div>
                                                    <span class="text-sm">🧾</span>
                                                </label>
                                            </div>
                                        </div>

                                        <!-- Pesan Penutup (Footer Nota Kasir) -->
                                        <div class="mb-5">
                                            <div class="flex items-center justify-between mb-1.5">
                                                <label for="storeFooterInput" class="block text-xs font-semibold text-[#333333]">Pesan Penutup (Footer Nota Kasir):</label>
                                                <span class="text-[11px] text-[#828282]">Maks. 140 karakter</span>
                                            </div>
                                            <textarea rows="2" id="storeFooterInput" name="store_footer" onkeyup="updateReceiptPreview()"
                                                class="w-full bg-white border border-[#E0E0E0] rounded-xl p-3 text-xs md:text-sm text-[#1C1C1C] focus:border-[#FF9B45] focus:outline-none transition-colors"><?= htmlspecialchars($store['footer']) ?></textarea>
                                        </div>

                                        <!-- Test Print Actions -->
                                        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3 pt-4 border-t border-[#F0ECE9]">
                                            <button type="button" onclick="printThermalReceipt()" 
                                                class="bg-[#F5F3F1] hover:bg-[#EBE8E5] text-[#1C1C1C] text-xs font-semibold px-4 py-2.5 rounded-xl border border-[#E0E0E0] flex items-center gap-2 transition-colors">
                                                <span>🖨️</span>
                                                <span>Test Print Nota Struk (80mm)</span>
                                            </button>

                                            <button type="submit" 
                                                class="bg-[#FF9B45] hover:bg-[#E88C3D] text-white font-bold text-xs md:text-sm px-6 py-2.5 rounded-xl flex items-center justify-center gap-2 shadow-button-orange active:scale-95 transition-all">
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
                                                <span>Simpan Pengaturan Toko</span>
                                            </button>
                                        </div>
                                    </div>
                                </form>
                            </div>

                        </div>

                        <!-- RIGHT 1 COL: SIMULASI CETAK NOTA 80MM -->
                        <div class="space-y-6">
                            
                            <!-- CARD: SIMULASI CETAK NOTA 80MM -->
                            <div class="bg-white rounded-2xl border border-[#F0ECE9] p-5 shadow-card" id="printableThermalReceipt">
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
                                        <p class="font-bold text-xs uppercase tracking-wider" id="previewReceiptName"><?= htmlspecialchars($store['name']) ?></p>
                                        <p class="text-[10px] text-[#544337]" id="previewReceiptAddress"><?= htmlspecialchars($store['address']) ?></p>
                                        <p class="text-[10px] text-[#544337]">Malang • WA: <span id="previewReceiptWa"><?= htmlspecialchars($store['wa']) ?></span></p>
                                    </div>

                                    <!-- Meta Order -->
                                    <div class="py-2 border-b border-dashed border-[#828282] text-[10px] space-y-0.5">
                                        <div class="flex justify-between">
                                            <span>Nota: INV-<?= date('Ymd') ?>-0001</span>
                                            <span><?= date('d/m/y H:i') ?></span>
                                        </div>
                                        <div class="flex justify-between">
                                            <span>Kasir: Admin</span>
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
                                    </div>

                                    <!-- Totals Calculation -->
                                    <div class="py-2 border-b border-dashed border-[#828282] text-[10px] space-y-1">
                                        <div class="flex justify-between text-[#828282]">
                                            <span>Subtotal:</span>
                                            <span>Rp 115.000</span>
                                        </div>
                                        <div class="flex justify-between font-bold text-xs pt-1 border-t border-dashed border-[#828282]/50">
                                            <span>TOTAL AKHIR:</span>
                                            <span class="text-[#924C00]">Rp 115.000</span>
                                        </div>
                                        <div class="flex justify-between font-bold text-[#27AE60]">
                                            <span>Status:</span>
                                            <span>LUNAS</span>
                                        </div>
                                    </div>

                                    <!-- Footer Message & Barcode -->
                                    <div class="pt-3 text-center text-[10px]">
                                        <p class="italic text-[#544337] mb-2 leading-tight" id="previewReceiptFooter">
                                            "<?= htmlspecialchars($store['footer']) ?>"
                                        </p>
                                        <div class="font-mono text-center tracking-widest text-[#828282] text-[10px] my-1">
                                            ||||||||||||||||||||||||||||||||||||||||||||
                                        </div>
                                        <p class="text-[9px] text-[#828282]">Berkat Dinasti — Kota Malang</p>
                                    </div>

                                </div>

                                <div class="flex items-center justify-between pt-3 mt-3 border-t border-[#F0ECE9] text-xs">
                                    <span class="text-[#828282]">Ukuran kertas: <strong class="text-[#1C1C1C]">80 mm</strong></span>
                                    <button type="button" onclick="printThermalReceipt()" class="text-[#FF9B45] hover:text-[#E88C3D] font-bold">
                                        Cetak Uji Coba &rarr;
                                    </button>
                                </div>
                            </div>

                        </div>

                    </div>
                </div>

                <!-- ============================================================== -->
                <!-- TAB 2 CONTENT: MANAJEMEN USER                                  -->
                <!-- ============================================================== -->
                <div id="tabContentUser" class="<?= $active_tab !== 'user' ? 'hidden' : '' ?>">
                    <div class="bg-white rounded-2xl border border-[#F0ECE9] p-6 shadow-card mb-6">
                        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 pb-5 border-b border-[#F0ECE9] mb-5">
                            <div>
                                <div class="flex items-center gap-2.5">
                                    <h3 class="text-base font-bold text-[#1C1C1C]">Daftar Pengguna Sistem</h3>
                                    <span class="bg-[#FF9B45]/15 text-[#924C00] text-xs font-semibold px-2.5 py-0.5 rounded-full">
                                        <?= count($users_list) ?> Pengguna
                                    </span>
                                </div>
                                <p class="text-xs text-[#828282] mt-0.5">Kelola akun kasir, staf bakery, serta hak akses pengelolaan pesanan pelanggan</p>
                            </div>

                            <button onclick="openUserModal()" 
                                class="bg-[#FF9B45] hover:bg-[#E88C3D] text-white text-xs md:text-sm font-semibold px-4 py-2.5 rounded-xl flex items-center justify-center gap-2 transition-all shadow-button-orange active:scale-95 shrink-0">
                                <img src="assets/icons/user_plus.svg" alt="Tambah User" class="w-4 h-4">
                                <span>+ Tambah User Baru</span>
                            </button>
                        </div>

                        <div class="overflow-x-auto">
                            <table class="w-full text-left border-collapse" id="usersTable">
                                <thead>
                                    <tr class="bg-[#FF9B45] text-white text-xs font-bold uppercase tracking-wider">
                                        <th class="py-3 px-4 min-w-[200px]">PENGGUNA</th>
                                        <th class="py-3 px-4 min-w-[140px]">USERNAME</th>
                                        <th class="py-3 px-4 min-w-[160px]">ROLE / HAK AKSES</th>
                                        <th class="py-3 px-4 min-w-[120px]">STATUS AKUN</th>
                                        <th class="py-3 px-4 text-center min-w-[120px]">AKSI</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-[#F0ECE9] text-xs md:text-sm">
                                    <?php foreach ($users_list as $u): ?>
                                        <tr class="hover:bg-[#FAF8F5] transition-colors">
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

                                            <td class="py-4 px-4 font-mono font-medium text-[#544337]">
                                                @<?= htmlspecialchars($u['username']) ?>
                                            </td>

                                            <td class="py-4 px-4">
                                                <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-semibold <?= $u['role_class'] ?>">
                                                    <span class="w-1.5 h-1.5 rounded-full <?= $u['id'] === 1 ? 'bg-white' : 'bg-[#FF9B45]' ?>"></span>
                                                    <span><?= htmlspecialchars($u['role']) ?></span>
                                                </span>
                                            </td>

                                            <td class="py-4 px-4">
                                                <span class="inline-flex items-center gap-1.5 font-semibold text-xs <?= $u['status_class'] ?>">
                                                    <span class="w-2 h-2 rounded-full <?= $u['dot_class'] ?>"></span>
                                                    <span><?= htmlspecialchars($u['status']) ?></span>
                                                </span>
                                            </td>

                                            <td class="py-4 px-4 text-center">
                                                <?php if ($u['is_locked'] ?? false): ?>
                                                    <span class="text-xs text-[#828282] italic">Terkunci (Owner)</span>
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
                </div>

            </main>
        </div>
    </div>

    <!-- Hidden Form for Delete User -->
    <form id="deleteUserForm" method="POST" action="pengaturan.php" class="hidden">
        <input type="hidden" name="action" value="delete_user">
        <input type="hidden" name="user_id" id="deleteUserIdInput">
    </form>

    <!-- MODAL POPOUT: TAMBAH / EDIT USER -->
    <div id="userModal" class="fixed inset-0 z-50 hidden flex items-center justify-center p-4">
        <div onclick="closeUserModal()" class="fixed inset-0 bg-black/50 backdrop-blur-xs transition-opacity duration-200"></div>

        <div class="bg-white rounded-2xl max-w-lg w-full p-6 sm:p-8 shadow-2xl relative z-10 animate-in fade-in zoom-in-95 duration-150">
            <div class="flex items-center justify-between pb-4 border-b border-[#F0ECE9] mb-5">
                <h3 class="text-base sm:text-lg font-bold text-[#1C1C1C]" id="userModalTitle">Tambah User Baru</h3>
                <button onclick="closeUserModal()" class="text-[#828282] hover:text-[#1C1C1C] p-1.5 rounded-lg hover:bg-[#F5F3F1]">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                </button>
            </div>

            <form id="userForm" method="POST" action="pengaturan.php" class="space-y-4">
                <input type="hidden" name="action" value="save_user">
                <input type="hidden" id="editUserId" name="user_id" value="">

                <div>
                    <label for="userNameInput" class="block text-xs font-semibold text-[#333333] mb-1.5">Nama Lengkap <span class="text-[#EB5757]">*</span></label>
                    <input type="text" id="userNameInput" name="nama" required placeholder="Contoh: Budi Prasetyo" 
                        class="w-full bg-white border border-[#E0E0E0] rounded-xl px-3.5 py-2.5 text-xs md:text-sm text-[#1C1C1C] focus:border-[#FF9B45] focus:outline-none">
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3.5">
                    <div>
                        <label for="userUsernameInput" class="block text-xs font-semibold text-[#333333] mb-1.5">Username Login <span class="text-[#EB5757]">*</span></label>
                        <input type="text" id="userUsernameInput" name="username" required placeholder="Contoh: budi.p" 
                            class="w-full bg-white border border-[#E0E0E0] rounded-xl px-3.5 py-2.5 text-xs md:text-sm text-[#1C1C1C] focus:border-[#FF9B45] focus:outline-none">
                    </div>

                    <div>
                        <label for="userSubtextInput" class="block text-xs font-semibold text-[#333333] mb-1.5">Keterangan / Posisi</label>
                        <input type="text" id="userSubtextInput" name="subtext" placeholder="Contoh: Kasir Shift Pagi" 
                            class="w-full bg-white border border-[#E0E0E0] rounded-xl px-3.5 py-2.5 text-xs md:text-sm text-[#1C1C1C] focus:border-[#FF9B45] focus:outline-none">
                    </div>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3.5">
                    <div>
                        <label for="userRoleInput" class="block text-xs font-semibold text-[#333333] mb-1.5">Role / Hak Akses</label>
                        <select id="userRoleInput" name="role" class="w-full bg-white border border-[#E0E0E0] rounded-xl px-3.5 py-2.5 text-xs md:text-sm text-[#1C1C1C] focus:border-[#FF9B45] focus:outline-none">
                            <option value="Karyawan">Karyawan / Kasir</option>
                            <option value="Pemilik (Owner)">Pemilik (Owner)</option>
                        </select>
                    </div>

                    <div>
                        <label for="userStatusInput" class="block text-xs font-semibold text-[#333333] mb-1.5">Status Akun</label>
                        <select id="userStatusInput" name="status" class="w-full bg-white border border-[#E0E0E0] rounded-xl px-3.5 py-2.5 text-xs md:text-sm text-[#1C1C1C] focus:border-[#FF9B45] focus:outline-none">
                            <option value="Aktif">Aktif</option>
                            <option value="Nonaktif">Nonaktif</option>
                        </select>
                    </div>
                </div>

                <div class="flex items-center justify-end gap-3 pt-4 border-t border-[#F0ECE9] mt-6">
                    <button type="button" onclick="closeUserModal()" class="bg-white hover:bg-gray-100 text-[#544337] text-xs font-semibold px-4 py-2.5 rounded-xl border border-[#E0E0E0]">
                        Batal
                    </button>
                    <button type="submit" class="bg-[#FF9B45] hover:bg-[#E88C3D] text-white text-xs font-semibold px-5 py-2.5 rounded-xl shadow-button-orange">
                        Simpan Pengguna
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- MODAL POPOUT: LOGOUT -->
    <div id="logoutModal" class="fixed inset-0 z-50 hidden flex items-center justify-center p-4">
        <!-- Backdrop with subtle blur -->
        <div onclick="closeLogoutModal()" class="fixed inset-0 craft-modal-overlay"></div>

        <!-- Modal Card Container -->
        <div class="craft-modal-dialog max-w-sm w-full p-6 sm:p-7 relative z-10 text-center">
            <!-- Top Icon -->
            <div class="w-12 h-12 rounded-xl bg-rose-50 border border-rose-200/60 text-rose-600 flex items-center justify-center mx-auto mb-4">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"></path></svg>
            </div>

            <!-- Title -->
            <h3 class="text-base sm:text-lg font-bold text-[#251A14] tracking-tight">Keluar dari Akun?</h3>
            <p class="text-xs text-[#8E7E73] mt-1.5 mb-6 leading-relaxed max-w-[280px] mx-auto">
                Anda akan keluar dari sesi ini. Pastikan semua perubahan sudah tersimpan.
            </p>

            <!-- Action Buttons -->
            <div class="space-y-2">
                <a href="logout.php" class="w-full bg-rose-600 hover:bg-rose-700 active:scale-[0.98] text-white text-xs sm:text-sm font-medium py-2.5 px-4 rounded-lg flex items-center justify-center gap-2 shadow-xs transition-all">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013 3v1"></path>
                    </svg>
                    <span>Ya, Keluar</span>
                </a>
                <button type="button" onclick="closeLogoutModal()" class="w-full craft-btn-secondary text-xs sm:text-sm py-2 px-4 rounded-lg">
                    Batal
                </button>
            </div>
        </div>
    </div>

    <!-- JAVASCRIPT LOGIC -->
    <script>
        const usersData = <?= json_encode($users_list) ?>;

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

        function switchSettingTab(tab) {
            const tabContentProfil = document.getElementById('tabContentProfil');
            const tabContentUser = document.getElementById('tabContentUser');
            const tabBtnProfil = document.getElementById('tabBtnProfil');
            const tabBtnUser = document.getElementById('tabBtnUser');
            const headerSubtitle = document.getElementById('headerSubtitle');
            const headerTitle = document.getElementById('headerTitle');

            if (tab === 'user') {
                tabContentProfil.classList.add('hidden');
                tabContentUser.classList.remove('hidden');
                tabBtnUser.className = 'px-4 py-2 rounded-lg text-xs md:text-sm font-semibold flex items-center gap-2 transition-all bg-[#FF9B45] text-white shadow-xs';
                tabBtnProfil.className = 'px-4 py-2 rounded-lg text-xs md:text-sm font-semibold flex items-center gap-2 transition-all text-[#828282] hover:text-[#1C1C1C]';
                headerSubtitle.textContent = 'Manajemen User';
                headerTitle.textContent = 'Manajemen Pengguna';
            } else {
                tabContentUser.classList.add('hidden');
                tabContentProfil.classList.remove('hidden');
                tabBtnProfil.className = 'px-4 py-2 rounded-lg text-xs md:text-sm font-semibold flex items-center gap-2 transition-all bg-[#FF9B45] text-white shadow-xs';
                tabBtnUser.className = 'px-4 py-2 rounded-lg text-xs md:text-sm font-semibold flex items-center gap-2 transition-all text-[#828282] hover:text-[#1C1C1C]';
                headerSubtitle.textContent = 'Profil Toko';
                headerTitle.textContent = 'Pengaturan Toko';
            }
        }

        // Live Receipt Preview
        function updateReceiptPreview() {
            const name = document.getElementById('storeNameInput').value;
            const address = document.getElementById('storeAddressInput').value;
            const wa = document.getElementById('storeWaInput').value;
            const footer = document.getElementById('storeFooterInput').value;

            document.getElementById('previewReceiptName').textContent = name;
            document.getElementById('previewReceiptAddress').textContent = address;
            document.getElementById('previewReceiptWa').textContent = wa;
            document.getElementById('previewReceiptFooter').textContent = `"${footer}"`;
        }

        // Logo Upload Preview
        function previewLogo(input) {
            if (input.files && input.files[0]) {
                const reader = new FileReader();
                reader.onload = function(e) {
                    document.getElementById('storeLogoPreview').src = e.target.result;
                };
                reader.readAsDataURL(input.files[0]);
            }
        }

        function resetLogo() {
            document.getElementById('storeLogoPreview').src = 'assets/images/logo.png';
            document.getElementById('logoFileInput').value = '';
        }

        // Thermal Receipt Print
        function printThermalReceipt() {
            window.print();
        }

        // User Modal Open/Close
        function openUserModal(id = null) {
            const modal = document.getElementById('userModal');
            const title = document.getElementById('userModalTitle');
            const form = document.getElementById('userForm');
            form.reset();

            if (id) {
                const user = usersData.find(u => u.id === id);
                if (user) {
                    title.textContent = 'Edit Data Pengguna';
                    document.getElementById('editUserId').value = user.id;
                    document.getElementById('userNameInput').value = user.nama;
                    document.getElementById('userUsernameInput').value = user.username;
                    document.getElementById('userSubtextInput').value = user.subtext;
                    document.getElementById('userRoleInput').value = user.role;
                    document.getElementById('userStatusInput').value = user.status;
                }
            } else {
                title.textContent = 'Tambah User Baru';
                document.getElementById('editUserId').value = '';
            }

            modal.classList.remove('hidden');
            document.body.style.overflow = 'hidden';
        }

        function closeUserModal() {
            document.getElementById('userModal').classList.add('hidden');
            document.body.style.overflow = '';
        }

        function handleDeleteUser(id, nama) {
            if (confirm(`Apakah Anda yakin ingin menghapus akun "${nama}"?`)) {
                document.getElementById('deleteUserIdInput').value = id;
                document.getElementById('deleteUserForm').submit();
            }
        }

        function openLogoutModal() {
            document.getElementById('logoutModal').classList.remove('hidden');
            document.body.style.overflow = 'hidden';
        }

        function closeLogoutModal() {
            document.getElementById('logoutModal').classList.add('hidden');
            document.body.style.overflow = '';
        }

        document.addEventListener('keydown', function(e) {
            if (e.key === 'Escape') {
                closeLogoutModal();
                closeUserModal();
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
