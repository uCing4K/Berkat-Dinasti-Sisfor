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
        'address' => 'Jl. Panglima Sudirman No. 42, Ngaglik',
        'province' => 'Jawa Timur',
        'city' => 'Kota Batu',
        'postal' => '65311',
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
<body class="min-h-screen bg-[#F5F1EB] text-[#251A14] flex flex-col antialiased selection:bg-[#D46E15] selection:text-white">

    <!-- OVERALL WRAPPER -->
    <div class="flex flex-1 w-full relative">

        <!-- MOBILE SIDEBAR OVERLAY -->
        <div id="sidebar-overlay" onclick="toggleSidebar()" class="fixed inset-0 bg-black/40 z-40 lg:hidden hidden transition-opacity duration-300"></div>

        <!-- ASIDE / SIDEBAR (Width 240px) -->
        <aside id="main-sidebar" class="fixed top-0 bottom-0 left-0 w-[240px] bg-[#3E2B21] border-r border-[#2C1E17] z-50 flex flex-col justify-between -translate-x-full lg:translate-x-0 transition-transform duration-300 ease-in-out shadow-xl lg:shadow-none">
            
            <!-- Top Logo & Navigation -->
            <div class="flex flex-col flex-1 overflow-y-auto">
                
                <!-- Logo & Brand Header (Height 64px - Aligned with Topbar) -->
                <div class="h-16 px-4 flex items-center gap-3 border-b border-white/10 shrink-0">
                    <div class="w-8 h-8 rounded-full bg-white p-0.5 flex items-center justify-center shrink-0 shadow-sm overflow-hidden">
                        <img src="assets/images/logo.png" alt="Berkat Dinasti Logo" class="w-full h-full object-contain">
                    </div>
                    <span class="text-white font-bold text-lg tracking-tight">Berkat Dinasti</span>
                    
                    <!-- Mobile Close Button -->
                    <button onclick="toggleSidebar()" class="ml-auto text-white/70 hover:text-white lg:hidden p-1">
                        <i class="fas fa-times text-base"></i>
                    </button>
                </div>

                <!-- Navigation List -->
                <nav class="p-4 space-y-1.5 flex-1">
                    <!-- Beranda -->
                    <a href="beranda.php" class="text-white/80 hover:text-white hover:bg-white/10 rounded-lg px-4 py-3 flex items-center gap-3 font-medium text-sm transition-colors">
                        <i class="fas fa-th-large w-5 text-center text-sm opacity-90"></i>
                        <span>Beranda</span>
                    </a>

                    <!-- Produk -->
                    <a href="produk.php" class="text-white/80 hover:text-white hover:bg-white/10 rounded-lg px-4 py-3 flex items-center gap-3 font-medium text-sm transition-colors">
                        <i class="fas fa-box-open w-5 text-center text-sm opacity-90"></i>
                        <span>Produk</span>
                    </a>

                    <!-- Pesanan -->
                    <a href="pesanan.php" class="text-white/80 hover:text-white hover:bg-white/10 rounded-lg px-4 py-3 flex items-center gap-3 font-medium text-sm transition-colors">
                        <i class="fas fa-shopping-bag w-5 text-center text-sm opacity-90"></i>
                        <span>Pesanan</span>
                    </a>

                    <!-- Pelanggan -->
                    <a href="pelanggan.php" class="text-white/80 hover:text-white hover:bg-white/10 rounded-lg px-4 py-3 flex items-center gap-3 font-medium text-sm transition-colors">
                        <i class="fas fa-users w-5 text-center text-sm opacity-90"></i>
                        <span>Pelanggan</span>
                    </a>

                    <!-- Laporan -->
                    <a href="laporan.php" class="text-white/80 hover:text-white hover:bg-white/10 rounded-lg px-4 py-3 flex items-center gap-3 font-medium text-sm transition-colors">
                        <i class="fas fa-chart-line w-5 text-center text-sm opacity-90"></i>
                        <span>Laporan</span>
                    </a>

                    <!-- Pengaturan (Active) -->
                    <a href="pengaturan.php" class="bg-[#D46E15] text-white rounded-lg px-4 py-3 flex items-center gap-3 font-semibold text-sm transition-all shadow-sm">
                        <i class="fas fa-cog w-5 text-center text-sm"></i>
                        <span>Pengaturan</span>
                    </a>
                </nav>
            </div>

            <!-- Bottom User Profile Card -->
            <div class="p-4 border-t border-white/10 shrink-0">
                <div class="bg-black/25 border border-white/5 rounded-xl p-2.5 flex items-center justify-between">
                    <div class="flex items-center gap-3 overflow-hidden">
                        <div class="w-9 h-9 rounded-full bg-[#D46E15] text-white font-bold text-xs flex items-center justify-center shrink-0 shadow-sm">
                            BD
                        </div>
                        <div class="truncate">
                            <p class="text-white font-semibold text-sm leading-tight truncate"><?= htmlspecialchars($_SESSION['nama_lengkap'] ?? $_SESSION['user'] ?? 'Admin') ?></p>
                            <p class="text-[#D8C7B8] text-xs leading-tight mt-0.5"><?= htmlspecialchars(ucfirst($_SESSION['role'] ?? 'Admin')) ?></p>
                        </div>
                    </div>
                    <button type="button" onclick="openLogoutModal()" title="Keluar / Logout" class="p-1.5 text-white/70 hover:text-white transition-colors shrink-0">
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
                            <a href="pengaturan.php" class="text-[#8E7E73] hover:text-[#3E2B21] transition-colors" id="headerBreadcrumb">Pengaturan</a>
                            <span class="text-[#8E7E73]">/</span>
                            <span class="text-[#D46E15] font-semibold" id="headerSubtitle"><?= $active_tab === 'user' ? 'Manajemen User' : 'Profil Toko' ?></span>
                        </div>
                        <!-- Section Heading -->
                        <h1 class="text-[#251A14] font-bold text-base md:text-lg leading-tight mt-0.5" id="headerTitle">
                            <?= $active_tab === 'user' ? 'Manajemen Pengguna' : 'Pengaturan Toko' ?>
                        </h1>
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
                        <div onclick="toggleUserDropdown()" class="w-9 h-9 rounded-full bg-[#D46E15] flex items-center justify-center text-white shadow-xs cursor-pointer hover:bg-[#B85207] transition-all active:scale-95" title="Profil Pengguna" id="userAvatarBtn">
                            <i class="fas fa-user text-white text-xs"></i>
                        </div>

                        <div id="userDropdown" class="hidden absolute right-0 mt-2.5 w-56 craft-dropdown py-1.5 z-50 text-xs">
                            <div class="px-4 py-2.5 border-b border-[#ECE5DD]">
                                <p class="text-xs font-semibold text-[#251A14] truncate"><?= htmlspecialchars($_SESSION['nama_lengkap'] ?? $_SESSION['user'] ?? 'Admin') ?></p>
                                <p class="text-[11px] text-[#8E7E73] mt-0.5"><?= htmlspecialchars(ucfirst($_SESSION['role'] ?? 'Pemilik')) ?> Toko</p>
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
                <!-- Toast In-Page Dinamis -->
                <div id="dynamicToast" class="hidden fixed bottom-6 right-6 z-50 bg-[#3E2B21] text-white px-5 py-3.5 rounded-xl shadow-2xl flex items-center gap-3 text-xs font-semibold animate-in slide-in-from-bottom-3 duration-200 border border-white/10">
                    <span id="dynamicToastIcon" class="flex items-center text-[#D46E15] text-sm"><i class="fas fa-info-circle"></i></span>
                    <span id="dynamicToastText">Fitur segera diimplementasi</span>
                </div>

                <!-- TAB SWITCHER BAR (PROFIL TOKO VS MANAJEMEN USER) -->
                <div class="bg-[#EFE8DE] rounded-xl border border-[#DDD3C5] p-1.5 flex items-center gap-2 mb-6 shadow-2xs max-w-fit">
                    <!-- Tab 1: Profil UMKM -->
                    <button onclick="switchSettingTab('profil')" id="tabBtnProfil" 
                        class="px-4 py-2 rounded-lg text-xs md:text-sm font-semibold flex items-center gap-2 transition-all <?= $active_tab !== 'user' ? 'bg-[#D46E15] text-white shadow-xs' : 'text-[#8E7E73] hover:text-[#251A14]' ?>">
                        <i class="fas fa-store text-xs"></i>
                        <span>Profil Toko & Printer</span>
                    </button>

                    <!-- Tab 2: Manajemen User -->
                    <button onclick="switchSettingTab('user')" id="tabBtnUser" 
                        class="px-4 py-2 rounded-lg text-xs md:text-sm font-semibold flex items-center gap-2 transition-all <?= $active_tab === 'user' ? 'bg-[#D46E15] text-white shadow-xs' : 'text-[#8E7E73] hover:text-[#251A14]' ?>">
                        <i class="fas fa-users-cog text-xs"></i>
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
                            <div class="bg-[#FDFBF7] rounded-2xl border border-[#E2D7C8] p-6 shadow-card">
                                <div class="flex items-center justify-between pb-4 border-b border-[#E2D7C8] mb-5">
                                    <div>
                                        <h3 class="text-base font-bold text-[#251A14]">Informasi Bisnis & Toko</h3>
                                        <p class="text-xs text-[#8E7E73] mt-0.5">Kelola data identitas UMKM dan alamat resmi operasional bakery</p>
                                    </div>
                                    <span class="w-6 h-6 rounded-full bg-[#27AE60]/15 text-[#1B7A43] flex items-center justify-center text-xs font-bold"><i class="fas fa-check text-[10px]"></i></span>
                                </div>

                                <!-- Logo Berkat Dinasti Box with Real File Input Preview -->
                                <div class="bg-[#EFE8DE] rounded-xl p-4 border border-[#DDD3C5] flex flex-col sm:flex-row sm:items-center gap-4 mb-5">
                                    <div class="w-16 h-16 rounded-xl bg-white p-1 border border-[#DDD3C5] flex items-center justify-center shrink-0 shadow-2xs overflow-hidden">
                                        <img src="<?= htmlspecialchars($store['logo'] ?? 'assets/images/logo.png') ?>" id="storeLogoPreview" alt="Logo Berkat Dinasti" class="w-full h-full object-contain">
                                    </div>
                                    <div class="flex-1 min-w-0">
                                        <div class="flex items-center gap-2">
                                            <h4 class="text-sm font-bold text-[#251A14]">Logo Toko Bakery</h4>
                                            <span class="w-4 h-4 rounded-full bg-[#27AE60] text-white text-[9px] flex items-center justify-center font-bold">✓</span>
                                        </div>
                                        <p class="text-xs text-[#8E7E73] mt-0.5">Format PNG/JPG transparan resolusi minimum 500 × 500 px (Maks. 2MB)</p>
                                        
                                        <!-- Hidden File Input for Logo -->
                                        <input type="file" id="logoFileInput" accept="image/*" class="hidden" onchange="previewLogo(this)">

                                        <div class="flex items-center gap-2.5 mt-3">
                                            <button type="button" onclick="document.getElementById('logoFileInput').click()" 
                                                class="bg-[#D46E15] hover:bg-[#B85207] text-white text-xs font-semibold px-3 py-1.5 rounded-lg flex items-center gap-1.5 shadow-2xs transition-colors">
                                                <i class="fas fa-upload text-[10px]"></i>
                                                <span>Ubah Logo</span>
                                            </button>
                                            <button type="button" onclick="resetLogo()" 
                                                class="bg-[#EFE8DE] hover:bg-[#E5DDD0] text-[#3E2B21] text-xs font-semibold px-3 py-1.5 rounded-lg border border-[#DDD3C5] transition-colors">
                                                Reset
                                            </button>
                                        </div>
                                    </div>
                                </div>

                                <!-- Form Fields -->
                                <form id="storeSettingsForm" method="POST" action="pengaturan.php" class="space-y-4">
                                    <input type="hidden" name="action" value="save_store_settings">

                                    <!-- Nama Toko -->
                                    <div>
                                        <div class="flex items-center justify-between mb-1.5">
                                            <label for="storeNameInput" class="block text-xs font-semibold text-[#251A14]">Nama Resmi UMKM / Toko <span class="text-[#EB5757]">*</span></label>
                                            <span class="text-[11px] text-[#8E7E73]">Ditampilkan di struk & faktur</span>
                                        </div>
                                        <div class="relative">
                                            <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-[#8E7E73]">
                                                <i class="fas fa-store text-xs"></i>
                                            </div>
                                            <input type="text" id="storeNameInput" name="store_name" value="<?= htmlspecialchars($store['name']) ?>" required
                                                onkeyup="updateReceiptPreview()"
                                                class="w-full bg-[#EFE8DE] border border-[#DDD3C5] rounded-xl pl-10 pr-4 py-2.5 text-xs md:text-sm text-[#251A14] font-semibold focus:border-[#D46E15] focus:ring-1 focus:ring-[#D46E15] focus:outline-none transition-colors">
                                        </div>
                                    </div>

                                    <!-- 2 Cols: Phone & WA -->
                                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                                        <div>
                                            <label for="storePhoneInput" class="block text-xs font-semibold text-[#251A14] mb-1.5">No. Telepon Toko</label>
                                            <input type="text" id="storePhoneInput" name="store_phone" value="<?= htmlspecialchars($store['phone']) ?>" 
                                                class="w-full bg-[#EFE8DE] border border-[#DDD3C5] rounded-xl px-3.5 py-2.5 text-xs md:text-sm text-[#251A14] focus:border-[#D46E15] focus:ring-1 focus:ring-[#D46E15] focus:outline-none transition-colors">
                                        </div>

                                        <div>
                                            <label for="storeWaInput" class="block text-xs font-semibold text-[#251A14] mb-1.5">WhatsApp Bisnis</label>
                                            <input type="text" id="storeWaInput" name="store_wa" value="<?= htmlspecialchars($store['wa']) ?>" 
                                                onkeyup="updateReceiptPreview()"
                                                class="w-full bg-[#EFE8DE] border border-[#DDD3C5] rounded-xl px-3.5 py-2.5 text-xs md:text-sm text-[#251A14] focus:border-[#D46E15] focus:ring-1 focus:ring-[#D46E15] focus:outline-none transition-colors">
                                        </div>
                                    </div>

                                    <!-- Alamat Lengkap Operasional Toko -->
                                    <div>
                                        <label for="storeAddressInput" class="block text-xs font-semibold text-[#251A14] mb-1.5">Alamat Lengkap Operasional Toko</label>
                                        <textarea id="storeAddressInput" name="store_address" rows="2" 
                                            onkeyup="updateReceiptPreview()"
                                            class="w-full bg-[#EFE8DE] border border-[#DDD3C5] rounded-xl p-3 text-xs md:text-sm text-[#251A14] focus:border-[#D46E15] focus:ring-1 focus:ring-[#D46E15] focus:outline-none transition-colors"><?= htmlspecialchars($store['address']) ?></textarea>
                                    </div>

                                    <!-- 2 Cols: Provinsi & Kota -->
                                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                                        <div>
                                            <label for="storeProvinceSelect" class="block text-xs font-semibold text-[#251A14] mb-1.5">Provinsi</label>
                                            <select id="storeProvinceSelect" name="store_province" class="w-full appearance-none bg-[#EFE8DE] border border-[#DDD3C5] rounded-xl pl-3.5 pr-8 py-2.5 text-xs md:text-sm text-[#251A14] font-medium focus:border-[#D46E15] focus:outline-none transition-colors cursor-pointer">
                                                <option value="Jawa Timur" <?= ($store['province'] === 'Jawa Timur') ? 'selected' : '' ?>>Jawa Timur</option>
                                                <option value="Jawa Barat" <?= ($store['province'] === 'Jawa Barat') ? 'selected' : '' ?>>Jawa Barat</option>
                                                <option value="DKI Jakarta" <?= ($store['province'] === 'DKI Jakarta') ? 'selected' : '' ?>>DKI Jakarta</option>
                                            </select>
                                        </div>

                                        <div>
                                            <label for="storeCitySelect" class="block text-xs font-semibold text-[#251A14] mb-1.5">Kota / Kabupaten</label>
                                            <select id="storeCitySelect" name="store_city" class="w-full appearance-none bg-[#EFE8DE] border border-[#DDD3C5] rounded-xl pl-3.5 pr-8 py-2.5 text-xs md:text-sm text-[#251A14] font-medium focus:border-[#D46E15] focus:outline-none transition-colors cursor-pointer">
                                                <option value="Kota Malang" <?= ($store['city'] === 'Kota Malang') ? 'selected' : '' ?>>Kota Malang</option>
                                                <option value="Kota Batu" <?= ($store['city'] === 'Kota Batu') ? 'selected' : '' ?>>Kota Batu</option>
                                                <option value="Kabupaten Malang" <?= ($store['city'] === 'Kabupaten Malang') ? 'selected' : '' ?>>Kabupaten Malang</option>
                                            </select>
                                        </div>
                                    </div>

                                    <!-- 2 Cols: Kode Pos & Email -->
                                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                                        <div>
                                            <label for="storePostalInput" class="block text-xs font-semibold text-[#251A14] mb-1.5">Kode Pos</label>
                                            <input type="text" id="storePostalInput" name="store_postal" value="<?= htmlspecialchars($store['postal']) ?>" 
                                                class="w-full bg-[#EFE8DE] border border-[#DDD3C5] rounded-xl px-3.5 py-2.5 text-xs md:text-sm text-[#251A14] focus:border-[#D46E15] focus:outline-none transition-colors">
                                        </div>

                                        <div>
                                            <label for="storeEmailInput" class="block text-xs font-semibold text-[#251A14] mb-1.5">Email Bisnis</label>
                                            <input type="email" id="storeEmailInput" name="store_email" value="<?= htmlspecialchars($store['email']) ?>" 
                                                class="w-full bg-[#EFE8DE] border border-[#DDD3C5] rounded-xl px-3.5 py-2.5 text-xs md:text-sm text-[#251A14] focus:border-[#D46E15] focus:outline-none transition-colors">
                                        </div>
                                    </div>

                                    <!-- CARD 2: PENGATURAN STRUK PRINTER -->
                                    <div class="pt-6 mt-6 border-t border-[#E2D7C8]">
                                        <div class="flex items-center justify-between pb-3 mb-4">
                                            <div class="flex items-center gap-2">
                                                <i class="fas fa-print text-[#D46E15]"></i>
                                                <h4 class="text-sm font-bold text-[#251A14]">Pengaturan Struk Kasir (Thermal POS)</h4>
                                            </div>
                                            <span class="bg-[#D46E15]/15 text-[#924C00] text-xs font-semibold px-2.5 py-0.5 rounded-full">
                                                Bluetooth & USB
                                            </span>
                                        </div>

                                        <!-- Pilihan Ukuran Kertas Thermal -->
                                        <div class="mb-4">
                                            <label class="block text-xs font-semibold text-[#251A14] mb-2">Pilihan Ukuran Kertas Thermal:</label>
                                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-3.5">
                                                <label id="cardPaper58" onclick="setPaperSize('58')" 
                                                    class="<?= ($store['paper_size'] === '58') ? 'border-2 border-[#D46E15] bg-[#EFE8DE]' : 'border border-[#DDD3C5] bg-[#EFE8DE]/50 hover:border-[#D46E15]' ?> rounded-xl p-3.5 flex items-center justify-between gap-3 cursor-pointer transition-all">
                                                    <div class="flex items-center gap-3">
                                                        <input type="radio" id="storePaperSize58" name="paper_size" value="58" <?= ($store['paper_size'] === '58') ? 'checked' : '' ?> class="text-[#D46E15] focus:ring-[#D46E15]">
                                                        <div>
                                                            <p class="text-xs font-bold text-[#251A14]">58 mm (Mini Portable POS)</p>
                                                            <p class="text-[11px] text-[#8E7E73] mt-0.5">Lebar 32 karakter per baris</p>
                                                        </div>
                                                    </div>
                                                    <i class="fas fa-receipt text-[#D46E15] text-base"></i>
                                                </label>

                                                <label id="cardPaper80" onclick="setPaperSize('80')" 
                                                    class="<?= ($store['paper_size'] !== '58') ? 'border-2 border-[#D46E15] bg-[#EFE8DE]' : 'border border-[#DDD3C5] bg-[#EFE8DE]/50 hover:border-[#D46E15]' ?> rounded-xl p-3.5 flex items-center justify-between gap-3 cursor-pointer transition-all">
                                                    <div class="flex items-center gap-3">
                                                        <input type="radio" id="storePaperSize80" name="paper_size" value="80" <?= ($store['paper_size'] !== '58') ? 'checked' : '' ?> class="text-[#D46E15] focus:ring-[#D46E15]">
                                                        <div>
                                                            <p class="text-xs font-bold text-[#924C00]">80 mm (Standar Desktop Kasir)</p>
                                                            <p class="text-[11px] text-[#5E4D42] mt-0.5">Lebar 48 karakter • Rekomendasi</p>
                                                        </div>
                                                    </div>
                                                    <i class="fas fa-receipt text-[#D46E15] text-base"></i>
                                                </label>
                                            </div>
                                        </div>

                                        <!-- Pesan Penutup (Footer Nota Kasir) -->
                                        <div class="mb-5">
                                            <div class="flex items-center justify-between mb-1.5">
                                                <label for="storeFooterInput" class="block text-xs font-semibold text-[#251A14]">Pesan Penutup (Footer Nota Kasir):</label>
                                                <span class="text-[11px] text-[#8E7E73]">Maks. 140 karakter</span>
                                            </div>
                                            <textarea rows="2" id="storeFooterInput" name="store_footer" onkeyup="updateReceiptPreview()"
                                                class="w-full bg-[#EFE8DE] border border-[#DDD3C5] rounded-xl p-3 text-xs md:text-sm text-[#251A14] focus:border-[#D46E15] focus:outline-none transition-colors"><?= htmlspecialchars($store['footer']) ?></textarea>
                                        </div>

                                        <!-- Test Print Actions -->
                                        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3 pt-4 border-t border-[#E2D7C8]">
                                            <button type="button" onclick="testPrintThermal()" id="btnTestPrintForm"
                                                class="bg-[#EFE8DE] hover:bg-[#E5DDD0] text-[#3E2B21] text-xs font-semibold px-4 py-2.5 rounded-xl border border-[#DDD3C5] flex items-center gap-2 transition-colors">
                                                <i class="fas fa-print text-xs"></i>
                                                <span id="lblTestPrintForm">Test Print Nota Struk (<?= ($store['paper_size'] === '58') ? '58mm' : '80mm' ?>)</span>
                                            </button>

                                            <button type="submit" 
                                                class="bg-[#D46E15] hover:bg-[#B85207] text-white font-bold text-xs md:text-sm px-6 py-2.5 rounded-xl flex items-center justify-center gap-2 shadow-button-orange active:scale-95 transition-all">
                                                <i class="fas fa-check text-xs"></i>
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
                            <div class="bg-[#FDFBF7] rounded-2xl border border-[#E2D7C8] p-5 shadow-card" id="printableThermalReceipt">
                                <div class="flex items-center justify-between pb-3 border-b border-[#E2D7C8] mb-4">
                                    <div class="flex items-center gap-2 text-xs font-bold text-[#251A14]">
                                        <i class="fas fa-receipt text-[#D46E15]"></i>
                                        <span id="lblSimulasiHeader"><?= ($store['paper_size'] === '58') ? 'Simulasi Cetak Nota 58mm' : 'Simulasi Cetak Nota 80mm' ?></span>
                                    </div>
                                    <span class="bg-[#EFE8DE] text-[#8E7E73] text-[10px] font-bold px-2 py-0.5 rounded uppercase border border-[#DDD3C5]">
                                        THERMAL VIEW
                                    </span>
                                </div>

                                <!-- Receipt Paper Preview (Dinamis 58mm vs 80mm) -->
                                <div id="thermalPaperBox" class="thermal-paper bg-[#FFFDF9] border border-[#DDD3C5] rounded-lg p-4 <?= ($store['paper_size'] === '58') ? 'text-[10px] max-w-[250px]' : 'text-[11px] max-w-[340px]' ?> leading-relaxed shadow-xs text-[#251A14] transition-all mx-auto">
                                    
                                    <!-- Store Header -->
                                    <div class="text-center pb-2 border-b border-dashed border-[#8E7E73]">
                                        <p class="font-bold text-xs uppercase tracking-wider" id="previewReceiptName"><?= htmlspecialchars($store['name']) ?></p>
                                        <p class="text-[10px] text-[#5E4D42]" id="previewReceiptAddress"><?= htmlspecialchars($store['address']) ?></p>
                                        <p class="text-[10px] text-[#5E4D42]">Kota Batu • WA: <span id="previewReceiptWa"><?= htmlspecialchars($store['wa']) ?></span></p>
                                    </div>

                                    <!-- Meta Order -->
                                    <div class="py-2 border-b border-dashed border-[#8E7E73] text-[10px] space-y-0.5">
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
                                    <div class="py-2 border-b border-dashed border-[#8E7E73] space-y-1.5 text-[10px]">
                                        <div class="flex justify-between">
                                            <span>10x Roti Hajatan (Kardus Putih)</span>
                                            <span class="font-bold">Rp 100.000</span>
                                        </div>
                                        <div class="flex justify-between">
                                            <span>2x Bolu Pisang (Mika)</span>
                                            <span class="font-bold">Rp 44.000</span>
                                        </div>
                                    </div>

                                    <!-- Totals Calculation -->
                                    <div class="py-2 border-b border-dashed border-[#8E7E73] text-[10px] space-y-1">
                                        <div class="flex justify-between text-[#8E7E73]">
                                            <span>Subtotal:</span>
                                            <span>Rp 144.000</span>
                                        </div>
                                        <div class="flex justify-between font-bold text-xs pt-1 border-t border-dashed border-[#8E7E73]/50">
                                            <span>TOTAL AKHIR:</span>
                                            <span class="text-[#924C00]">Rp 144.000</span>
                                        </div>
                                        <div class="flex justify-between font-bold text-[#27AE60]">
                                            <span>Status:</span>
                                            <span>LUNAS</span>
                                        </div>
                                    </div>

                                    <!-- Footer Message -->
                                    <div class="pt-3 text-center text-[10px]">
                                        <p class="italic text-[#5E4D42] mb-1.5 leading-tight" id="previewReceiptFooter">
                                            "<?= htmlspecialchars($store['footer']) ?>"
                                        </p>
                                        <p class="text-[9px] text-[#8E7E73] font-medium tracking-wide">Berkat Dinasti — Kota Batu</p>
                                    </div>

                                </div>

                                <div class="flex items-center justify-between pt-3 mt-3 border-t border-[#E2D7C8] text-xs">
                                    <span class="text-[#8E7E73]">Ukuran kertas: <strong class="text-[#251A14]" id="lblUkuranKertasFooter"><?= ($store['paper_size'] === '58') ? '58 mm' : '80 mm' ?></strong></span>
                                    <button type="button" onclick="testPrintThermal()" class="text-[#D46E15] hover:text-[#B85207] font-bold">
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
                    <div class="bg-[#FDFBF7] rounded-2xl border border-[#E2D7C8] p-6 shadow-card mb-6">
                        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 pb-5 border-b border-[#E2D7C8] mb-5">
                            <div>
                                <div class="flex items-center gap-2.5">
                                    <h3 class="text-base font-bold text-[#251A14]">Daftar Pengguna Sistem</h3>
                                    <span class="bg-[#D46E15]/15 text-[#924C00] text-xs font-semibold px-2.5 py-0.5 rounded-full">
                                        <?= count($users_list) ?> Pengguna
                                    </span>
                                </div>
                                <p class="text-xs text-[#8E7E73] mt-0.5">Kelola akun kasir, staf bakery, serta hak akses pengelolaan pesanan pelanggan</p>
                            </div>

                            <button onclick="openUserModal()" 
                                class="bg-[#D46E15] hover:bg-[#B85207] text-white text-xs md:text-sm font-semibold px-4 py-2.5 rounded-xl flex items-center justify-center gap-2 transition-all shadow-button-orange active:scale-95 shrink-0">
                                <i class="fas fa-user-plus text-xs"></i>
                                <span>Tambah User Baru</span>
                            </button>
                        </div>

                        <div class="overflow-x-auto">
                            <table class="w-full text-left border-collapse" id="usersTable">
                                <thead>
                                    <tr class="bg-[#3E2B21] text-[#FAF8F5] text-xs font-bold uppercase tracking-wider">
                                        <th class="py-3 px-4 min-w-[200px]">PENGGUNA</th>
                                        <th class="py-3 px-4 min-w-[140px]">USERNAME</th>
                                        <th class="py-3 px-4 min-w-[160px]">ROLE / HAK AKSES</th>
                                        <th class="py-3 px-4 min-w-[120px]">STATUS AKUN</th>
                                        <th class="py-3 px-4 text-center min-w-[120px]">AKSI</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-[#E2D7C8] text-xs md:text-sm">
                                    <?php foreach ($users_list as $idx => $u): ?>
                                        <tr class="transition-colors <?= $idx % 2 === 0 ? 'bg-[#FDFBF7]' : 'bg-[#F8F5F0]' ?> hover:bg-[#F0E9DF]">
                                            <td class="py-4 px-4">
                                                <div class="flex items-center gap-3">
                                                    <div class="w-9 h-9 rounded-full <?= $u['id'] === 1 ? 'bg-[#3E2B21]' : 'bg-[#D46E15]' ?> text-white font-bold text-xs flex items-center justify-center shrink-0 shadow-xs">
                                                        <?= htmlspecialchars($u['avatar']) ?>
                                                    </div>
                                                    <div>
                                                        <h4 class="font-bold text-[#251A14] leading-tight"><?= htmlspecialchars($u['nama']) ?></h4>
                                                        <p class="text-xs text-[#8E7E73] mt-0.5"><?= htmlspecialchars($u['subtext']) ?></p>
                                                    </div>
                                                </div>
                                            </td>

                                            <td class="py-4 px-4 font-mono font-medium text-[#5E4D42]">
                                                @<?= htmlspecialchars($u['username']) ?>
                                            </td>

                                            <td class="py-4 px-4">
                                                <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-semibold <?= $u['id'] === 1 ? 'bg-[#3E2B21] text-white' : 'bg-[#D46E15]/15 text-[#924C00]' ?>">
                                                    <span class="w-1.5 h-1.5 rounded-full <?= $u['id'] === 1 ? 'bg-white' : 'bg-[#D46E15]' ?>"></span>
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
                                                    <span class="text-xs text-[#8E7E73] italic">Terkunci (Owner)</span>
                                                <?php else: ?>
                                                    <div class="flex items-center justify-center gap-2">
                                                        <button onclick="openUserModal(<?= $u['id'] ?>)" title="Edit Pengguna" class="p-1.5 text-[#8E7E73] hover:text-[#2F80ED] rounded hover:bg-[#EFE8DE] transition-colors">
                                                            <i class="fas fa-edit text-xs"></i>
                                                        </button>
                                                        <button onclick="handleDeleteUser(<?= $u['id'] ?>, '<?= htmlspecialchars($u['nama']) ?>')" title="Hapus Pengguna" class="p-1.5 text-[#8E7E73] hover:text-[#EB5757] rounded hover:bg-rose-50 transition-colors">
                                                            <i class="fas fa-trash-alt text-xs"></i>
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
        <div onclick="closeUserModal()" class="fixed inset-0 craft-modal-overlay"></div>

        <div class="craft-modal-dialog max-w-lg w-full p-6 sm:p-8 relative z-10 animate-in fade-in zoom-in-95 duration-150">
            <div class="flex items-center justify-between pb-4 border-b border-[#E2D7C8] mb-5">
                <h3 class="text-base sm:text-lg font-bold text-[#251A14]" id="userModalTitle">Tambah User Baru</h3>
                <button onclick="closeUserModal()" class="text-[#8E7E73] hover:text-[#251A14] p-1.5 rounded-lg hover:bg-[#EFE8DE] transition-colors">
                    <i class="fas fa-times text-base"></i>
                </button>
            </div>

            <form id="userForm" method="POST" action="pengaturan.php" class="space-y-4">
                <input type="hidden" name="action" value="save_user">
                <input type="hidden" id="editUserId" name="user_id" value="">

                <div>
                    <label for="userNameInput" class="block text-xs font-semibold text-[#251A14] mb-1.5">Nama Lengkap <span class="text-[#EB5757]">*</span></label>
                    <input type="text" id="userNameInput" name="nama" required placeholder="Contoh: Budi Prasetyo" 
                        class="w-full bg-[#EFE8DE] border border-[#DDD3C5] rounded-xl px-3.5 py-2.5 text-xs md:text-sm text-[#251A14] placeholder-[#8E7E73] focus:border-[#D46E15] focus:ring-1 focus:ring-[#D46E15] focus:outline-none">
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3.5">
                    <div>
                        <label for="userUsernameInput" class="block text-xs font-semibold text-[#251A14] mb-1.5">Username Login <span class="text-[#EB5757]">*</span></label>
                        <input type="text" id="userUsernameInput" name="username" required placeholder="Contoh: budi.p" 
                            class="w-full bg-[#EFE8DE] border border-[#DDD3C5] rounded-xl px-3.5 py-2.5 text-xs md:text-sm text-[#251A14] placeholder-[#8E7E73] focus:border-[#D46E15] focus:ring-1 focus:ring-[#D46E15] focus:outline-none">
                    </div>

                    <div>
                        <label for="userSubtextInput" class="block text-xs font-semibold text-[#251A14] mb-1.5">Keterangan / Posisi</label>
                        <input type="text" id="userSubtextInput" name="subtext" placeholder="Contoh: Kasir Shift Pagi" 
                            class="w-full bg-[#EFE8DE] border border-[#DDD3C5] rounded-xl px-3.5 py-2.5 text-xs md:text-sm text-[#251A14] placeholder-[#8E7E73] focus:border-[#D46E15] focus:ring-1 focus:ring-[#D46E15] focus:outline-none">
                    </div>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3.5">
                    <div>
                        <label for="userRoleInput" class="block text-xs font-semibold text-[#251A14] mb-1.5">Role / Hak Akses</label>
                        <select id="userRoleInput" name="role" class="w-full bg-[#EFE8DE] border border-[#DDD3C5] rounded-xl px-3.5 py-2.5 text-xs md:text-sm text-[#251A14] focus:border-[#D46E15] focus:outline-none">
                            <option value="Karyawan">Karyawan / Kasir</option>
                            <option value="Pemilik (Owner)">Pemilik (Owner)</option>
                        </select>
                    </div>

                    <div>
                        <label for="userStatusInput" class="block text-xs font-semibold text-[#251A14] mb-1.5">Status Akun</label>
                        <select id="userStatusInput" name="status" class="w-full bg-[#EFE8DE] border border-[#DDD3C5] rounded-xl px-3.5 py-2.5 text-xs md:text-sm text-[#251A14] focus:border-[#D46E15] focus:outline-none">
                            <option value="Aktif">Aktif</option>
                            <option value="Nonaktif">Nonaktif</option>
                        </select>
                    </div>
                </div>

                <div class="flex items-center justify-end gap-3 pt-4 border-t border-[#E2D7C8] mt-6">
                    <button type="button" onclick="closeUserModal()" class="craft-btn-secondary text-xs font-semibold px-4 py-2.5 rounded-xl">
                        Batal
                    </button>
                    <button type="submit" class="bg-[#D46E15] hover:bg-[#B85207] text-white text-xs font-semibold px-5 py-2.5 rounded-xl shadow-button-orange active:scale-95 transition-all">
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
                <i class="fas fa-sign-out-alt text-xl"></i>
            </div>

            <!-- Title -->
            <h3 class="text-base sm:text-lg font-bold text-[#251A14] tracking-tight">Keluar dari Akun?</h3>
            <p class="text-xs text-[#8E7E73] mt-1.5 mb-6 leading-relaxed max-w-[280px] mx-auto">
                Anda akan keluar dari sesi ini. Pastikan semua perubahan sudah tersimpan.
            </p>

            <!-- Action Buttons -->
            <div class="space-y-2">
                <a href="logout.php" class="w-full bg-rose-600 hover:bg-rose-700 active:scale-[0.98] text-white text-xs sm:text-sm font-medium py-2.5 px-4 rounded-lg flex items-center justify-center gap-2 shadow-xs transition-all">
                    <i class="fas fa-sign-out-alt"></i>
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
                tabBtnUser.className = 'px-4 py-2 rounded-lg text-xs md:text-sm font-semibold flex items-center gap-2 transition-all bg-[#D46E15] text-white shadow-xs';
                tabBtnProfil.className = 'px-4 py-2 rounded-lg text-xs md:text-sm font-semibold flex items-center gap-2 transition-all text-[#8E7E73] hover:text-[#251A14]';
                headerSubtitle.textContent = 'Manajemen User';
                headerTitle.textContent = 'Manajemen Pengguna';
            } else {
                tabContentUser.classList.add('hidden');
                tabContentProfil.classList.remove('hidden');
                tabBtnProfil.className = 'px-4 py-2 rounded-lg text-xs md:text-sm font-semibold flex items-center gap-2 transition-all bg-[#D46E15] text-white shadow-xs';
                tabBtnUser.className = 'px-4 py-2 rounded-lg text-xs md:text-sm font-semibold flex items-center gap-2 transition-all text-[#8E7E73] hover:text-[#251A14]';
                headerSubtitle.textContent = 'Profil Toko';
                headerTitle.textContent = 'Pengaturan Toko';
            }
        }

        // Ukuran Kertas Thermal Dinamis (58mm vs 80mm)
        function setPaperSize(size) {
            const paper58 = document.getElementById('storePaperSize58');
            const paper80 = document.getElementById('storePaperSize80');
            const card58 = document.getElementById('cardPaper58');
            const card80 = document.getElementById('cardPaper80');
            const receiptBox = document.getElementById('thermalPaperBox');
            const headerTitle = document.getElementById('lblSimulasiHeader');
            const formBtnLabel = document.getElementById('lblTestPrintForm');
            const footerPaperLabel = document.getElementById('lblUkuranKertasFooter');

            if (size === '58') {
                if (paper58) paper58.checked = true;
                if (card58) {
                    card58.className = 'border-2 border-[#D46E15] bg-[#EFE8DE] rounded-xl p-3.5 flex items-center justify-between gap-3 cursor-pointer transition-all shadow-xs';
                }
                if (card80) {
                    card80.className = 'border border-[#DDD3C5] bg-[#EFE8DE]/50 rounded-xl p-3.5 flex items-center justify-between gap-3 cursor-pointer hover:border-[#D46E15] transition-all';
                }
                if (receiptBox) {
                    receiptBox.className = 'thermal-paper bg-[#FFFDF9] border border-[#DDD3C5] rounded-lg p-3 text-[10px] leading-relaxed shadow-xs text-[#251A14] transition-all max-w-[250px] mx-auto';
                }
                if (headerTitle) headerTitle.textContent = 'Simulasi Cetak Nota 58mm';
                if (formBtnLabel) formBtnLabel.textContent = 'Test Print Nota Struk (58mm)';
                if (footerPaperLabel) footerPaperLabel.textContent = '58 mm';
            } else {
                if (paper80) paper80.checked = true;
                if (card80) {
                    card80.className = 'border-2 border-[#D46E15] bg-[#EFE8DE] rounded-xl p-3.5 flex items-center justify-between gap-3 cursor-pointer transition-all shadow-xs';
                }
                if (card58) {
                    card58.className = 'border border-[#DDD3C5] bg-[#EFE8DE]/50 rounded-xl p-3.5 flex items-center justify-between gap-3 cursor-pointer hover:border-[#D46E15] transition-all';
                }
                if (receiptBox) {
                    receiptBox.className = 'thermal-paper bg-[#FFFDF9] border border-[#DDD3C5] rounded-lg p-4 text-[11px] leading-relaxed shadow-xs text-[#251A14] transition-all max-w-[340px] mx-auto';
                }
                if (headerTitle) headerTitle.textContent = 'Simulasi Cetak Nota 80mm';
                if (formBtnLabel) formBtnLabel.textContent = 'Test Print Nota Struk (80mm)';
                if (footerPaperLabel) footerPaperLabel.textContent = '80 mm';
            }
        }

        // Test Print Toast Notification
        function testPrintThermal() {
            showToast('Fitur segera diimplementasi', '<i class="fas fa-info-circle text-[#D46E15]"></i>');
        }

        function showToast(text, iconHtml = '<i class="fas fa-info-circle text-[#D46E15]"></i>') {
            const toast = document.getElementById('dynamicToast');
            const iconEl = document.getElementById('dynamicToastIcon');
            const textEl = document.getElementById('dynamicToastText');
            if (textEl) textEl.textContent = text;
            if (iconEl) iconEl.innerHTML = iconHtml;
            if (toast) {
                toast.classList.remove('hidden');
                setTimeout(() => {
                    toast.classList.add('hidden');
                }, 3000);
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
            testPrintThermal();
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

        document.addEventListener('DOMContentLoaded', function() {
            const urlParams = new URLSearchParams(window.location.search);
            if (urlParams.get('tab') === 'user') {
                switchSettingTab('user');
            }
        });
    </script>
</body>
</html>
