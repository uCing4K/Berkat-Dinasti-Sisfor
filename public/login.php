<?php
session_start();

// Cek auto-login via Cookie 'dinasti_remember' jika belum login dan bukan setelah logout eksplisit (Item 39.2)
$is_logged_out = (isset($_GET['status']) && $_GET['status'] === 'logged_out');
if (!$is_logged_out && (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true)) {
    if (isset($_COOKIE['dinasti_remember'])) {
        $token = $_COOKIE['dinasti_remember'];
        $parts = explode(':', $token);
        if (count($parts) === 3) {
            list($uName, $expiry, $sig) = $parts;
            if (intval($expiry) > time()) {
                $expectedSig = hash_hmac('sha256', $uName . '|' . $expiry, 'berkat_dinasti_auth_key_2026');
                if (hash_equals($expectedSig, $sig)) {
                    $_SESSION['logged_in'] = true;
                    $_SESSION['user_id'] = ($uName === 'admin') ? 1 : 2;
                    $_SESSION['user'] = $uName;
                    $_SESSION['username'] = $uName;
                    $_SESSION['nama_lengkap'] = ($uName === 'admin') ? 'Administrator' : (($uName === 'kasir') ? 'Staf Operasional' : ucfirst($uName));
                    $_SESSION['role'] = ($uName === 'admin') ? 'admin' : 'kasir';
                    $_SESSION['login_time'] = time();
                    header("Location: beranda.php");
                    exit();
                }
            }
        }
    }
}

// Jika sudah login sebelumnya, langsung arahkan ke beranda.php
if (isset($_SESSION['logged_in']) && $_SESSION['logged_in'] === true) {
    header("Location: beranda.php");
    exit();
}

// Coba muat konfigurasi database jika tersedia
$configFile = __DIR__ . '/../config/config.php';
if (file_exists($configFile)) {
    require_once $configFile;
}

$error_message = '';
$success_message = '';
$username_val = '';

if ($is_logged_out && empty($error_message)) {
    $success_message = 'Anda telah berhasil keluar dari sistem.';
}

// Proses Form Login saat tombol Submit ditekan
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username_val = trim($_POST['username'] ?? '');
    $password_val = $_POST['password'] ?? '';
    $remember = isset($_POST['remember']);

    $authenticated = false;
    $userData = null;

    // 1. Verifikasi melalui Database MySQL (jika server MySQL aktif)
    if (defined('DB_HOST') && defined('DB_USER') && defined('DB_PASS') && defined('DB_NAME')) {
        mysqli_report(MYSQLI_REPORT_OFF);
        $koneksi = @mysqli_connect(DB_HOST, DB_USER, DB_PASS, DB_NAME);
        if ($koneksi) {
            $stmt = mysqli_prepare($koneksi, "SELECT * FROM pengguna WHERE username = ? AND status = 'aktif' LIMIT 1");
            if ($stmt) {
                mysqli_stmt_bind_param($stmt, "s", $username_val);
                mysqli_stmt_execute($stmt);
                $result = mysqli_stmt_get_result($stmt);
                if ($row = mysqli_fetch_assoc($result)) {
                    // Cek password hash terenkripsi ATAU kredensial master admin / kasir
                    $masterAdminMatch = ($username_val === 'admin' && $password_val === 'kekuatanadmindinasti');
                    $masterKasirMatch = (($username_val === 'kasir' || $username_val === 'staff') && ($password_val === 'dinasti123' || $password_val === 'kasir123'));
                    if (password_verify($password_val, $row['password']) || $masterAdminMatch || $masterKasirMatch) {
                        $authenticated = true;
                        $userData = $row;

                        // Sinkronkan password hash jika belum diperbarui
                        if ($masterAdminMatch && !password_verify($password_val, $row['password'])) {
                            $newHash = password_hash('kekuatanadmindinasti', PASSWORD_BCRYPT);
                            $upStmt = mysqli_prepare($koneksi, "UPDATE pengguna SET password = ? WHERE id_user = ?");
                            if ($upStmt) {
                                mysqli_stmt_bind_param($upStmt, "si", $newHash, $row['id_user']);
                                mysqli_stmt_execute($upStmt);
                                mysqli_stmt_close($upStmt);
                            }
                        } elseif ($masterKasirMatch && !password_verify($password_val, $row['password'])) {
                            $newHash = password_hash('dinasti123', PASSWORD_BCRYPT);
                            $upStmt = mysqli_prepare($koneksi, "UPDATE pengguna SET password = ? WHERE id_user = ?");
                            if ($upStmt) {
                                mysqli_stmt_bind_param($upStmt, "si", $newHash, $row['id_user']);
                                mysqli_stmt_execute($upStmt);
                                mysqli_stmt_close($upStmt);
                            }
                        }

                        // Perbarui waktu login terakhir
                        $upLogin = mysqli_prepare($koneksi, "UPDATE pengguna SET last_login = NOW() WHERE id_user = ?");
                        if ($upLogin) {
                            mysqli_stmt_bind_param($upLogin, "i", $row['id_user']);
                            mysqli_stmt_execute($upLogin);
                            mysqli_stmt_close($upLogin);
                        }
                    }
                }
                mysqli_stmt_close($stmt);
            }
            mysqli_close($koneksi);
        }
    }

    // 2. Kredensial Master Default (Bekerja selalu, baik saat DB online maupun offline)
    if (!$authenticated) {
        if ($username_val === 'admin' && $password_val === 'kekuatanadmindinasti') {
            $authenticated = true;
            $userData = [
                'id_user' => 1,
                'username' => 'admin',
                'nama_lengkap' => 'Administrator',
                'role' => 'admin',
                'status' => 'aktif'
            ];
        } elseif (($username_val === 'kasir' || $username_val === 'staff') && ($password_val === 'dinasti123' || $password_val === 'kasir123')) {
            $authenticated = true;
            $userData = [
                'id_user' => 2,
                'username' => 'kasir',
                'nama_lengkap' => 'Staf Operasional',
                'role' => 'kasir',
                'status' => 'aktif'
            ];
        }
    }

    // 3. Respon Autentikasi
    if ($authenticated) {
        session_regenerate_id(true);
        $_SESSION['logged_in'] = true;
        $_SESSION['user_id'] = $userData['id_user'] ?? 1;
        $_SESSION['user'] = $userData['username'] ?? 'admin';
        $_SESSION['username'] = $userData['username'] ?? 'admin';
        $_SESSION['nama_lengkap'] = $userData['nama_lengkap'] ?? 'Administrator';
        $_SESSION['role'] = $userData['role'] ?? 'admin';
        $_SESSION['login_time'] = time();

        // Fitur Ingat Saya: 1 Minggu (Item 39.2)
        if ($remember) {
            $remember_duration = 7 * 24 * 60 * 60; // 7 hari = 1 minggu
            $remember_expiry = time() + $remember_duration;
            $remember_sig = hash_hmac('sha256', $username_val . '|' . $remember_expiry, 'berkat_dinasti_auth_key_2026');
            $remember_token = $username_val . ':' . $remember_expiry . ':' . $remember_sig;

            setcookie('dinasti_remember', $remember_token, $remember_expiry, "/");
            setcookie('remember_user', $username_val, $remember_expiry, "/");
        } else {
            if (isset($_COOKIE['dinasti_remember'])) {
                setcookie('dinasti_remember', '', time() - 3600, "/");
            }
            if (isset($_COOKIE['remember_user'])) {
                setcookie('remember_user', '', time() - 3600, "/");
            }
        }

        header("Location: beranda.php");
        exit();
    } else {
        $error_message = 'Username atau password yang Anda masukkan salah. Silakan periksa kembali.';
        $success_message = ''; // Mencegah tumpang tindih banner logout saat login salah
    }
} else {
    if (isset($_COOKIE['remember_user']) && empty($username_val)) {
        $username_val = $_COOKIE['remember_user'];
    }
}
$remember_checked = !empty($username_val) || isset($_COOKIE['dinasti_remember']);
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Berkat Dinasti</title>
    <link rel="icon" type="image/png" href="assets/images/logo.png">
    
    <!-- Font Awesome 5 Free CDN -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css" crossorigin="anonymous" referrerpolicy="no-referrer" />
    <!-- Tailwind CSS CDN -->
    <script src="https://cdn.tailwindcss.com"></script>
    <!-- Google Fonts: Inter -->
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
                            orange: '#D46E15',
                            orangeHover: '#B85207',
                            lightText: '#E8D7CD',
                            bg: '#F5F1EB'
                        }
                    },
                    fontFamily: {
                        sans: ['Inter', 'sans-serif'],
                    }
                }
            }
        }
    </script>

    <style>
        body {
            background-color: #F8ECE1;
            background-image: 
                radial-gradient(ellipse 90% 60% at 70% 40%, rgba(212, 110, 21, 0.12) 0%, rgba(212, 110, 21, 0.03) 60%, transparent 100%),
                radial-gradient(circle at 15% 85%, rgba(255, 155, 69, 0.08) 0%, transparent 50%),
                linear-gradient(135deg, #FAF1E6 0%, #F5E5D3 50%, #EFE0CC 100%);
            font-family: 'Inter', sans-serif;
        }
        .logo-box {
            box-shadow: 0 4px 16px rgba(0, 0, 0, 0.2), 0 1px 3px rgba(0, 0, 0, 0.1);
        }
    </style>
</head>
<body class="h-screen w-full m-0 flex overflow-hidden bg-[#F8ECE1]">
    
    <!-- LEFT PANEL: Branding (Disembunyikan saat di HP, muncul di layar besar) -->
    <div class="hidden md:flex w-[45%] max-w-[512px] bg-[#3E2B21] border-r border-[#2C1E17] relative flex-col justify-between items-center p-8 lg:p-16 overflow-hidden">
        
        <!-- Abstract Circles Background Gradient dari Figma -->
        <div class="absolute inset-0 pointer-events-none" style="background: radial-gradient(ellipse 120.83% 60.42% at 50.00% 45.00%, rgba(212, 110, 21, 0.22) 0%, rgba(212, 110, 21, 0) 70%)"></div>
        
        <!-- Decorative Subtle Background Circles (Bottom Right) -->
        <div class="absolute w-[384px] h-[384px] -right-[120px] -bottom-[120px] rounded-full border border-white/5 pointer-events-none"></div>
        <div class="absolute w-[288px] h-[288px] -right-[72px] -bottom-[72px] rounded-full border border-[#D46E15]/15 pointer-events-none"></div>
        
        <!-- Decorative Subtle Background Circle (Top Left) -->
        <div class="absolute w-[320px] h-[320px] -left-[80px] -top-[80px] rounded-full border border-white/5 pointer-events-none"></div>

        <div class="flex-1 flex flex-col justify-center items-center w-full z-10">
            <!-- Kotak Logo -->
            <div class="relative w-28 h-28 mb-6">
                <div class="relative w-full h-full bg-[#FAF8F5] rounded-2xl flex items-center justify-center p-2.5 logo-box border border-white/20">
                    <img src="assets/images/logo.png" alt="Berkat Dinasti Logo" class="w-full h-full object-contain">
                </div>
            </div>

            <h1 class="text-[#FAF8F5] text-[32px] leading-[38px] font-bold text-center mb-2 tracking-tight">Berkat Dinasti</h1>
            <p class="text-[#D8C7B8] text-[15px] text-center mb-6">Kelola Pesanan Rotimu dengan Mudah</p>
            
            <div class="bg-[#D46E15]/20 border border-[#D46E15]/30 rounded-full px-4 py-1.5 mb-8">
                <span class="text-[#F5A25D] text-xs font-semibold tracking-[0.6px] uppercase">Sistem Manajemen Pesanan</span>
            </div>

            <div class="flex items-center gap-3">
                <div class="w-8 h-px bg-[#D46E15]/40"></div>
                <div class="flex items-center gap-1.5 text-[#F5A25D]/90 text-xs font-medium tracking-[0.6px] uppercase">
                    <!-- Icon Roti FontAwesome 5 -->
                    <i class="fas fa-bread-slice text-xs"></i>
                    BAKERY & PASTRY UMKM
                </div>
                <div class="w-8 h-px bg-[#D46E15]/40"></div>
            </div>
        </div>

        <!-- Footer Kiri -->
        <div class="w-full pt-6 border-t border-white/10 flex justify-between items-center z-10">
            <div class="flex items-center gap-2 text-[#D8C7B8]/80 text-xs">
                <i class="fas fa-shield-alt text-emerald-400"></i>
                Sistem Khusus Internal Admin
            </div>
            <span class="text-[#8E7E73] text-xs font-mono">v1.0.0</span>
        </div>
    </div>

    <!-- RIGHT PANEL: Login Form (Item 42: Warm Orange Bakery Background with Signature Berkat Dinasti Atmosphere) -->
    <div class="flex-1 flex justify-center items-center p-6 lg:p-8 bg-transparent relative overflow-y-auto">
        <!-- Subtle warm orange backdrop aura for right panel -->
        <div class="absolute inset-0 pointer-events-none bg-[radial-gradient(ellipse_at_50%_50%,rgba(212,110,21,0.08)_0%,transparent_75%)]"></div>
        <div class="w-full max-w-[420px] bg-[#3E2B21] border border-[#544337] shadow-[0_20px_50px_rgba(0,0,0,0.35)] rounded-2xl p-8 lg:p-10 my-auto relative overflow-hidden z-10">
            
            <!-- Abstract Glow Subtle Effect -->
            <div class="absolute -right-20 -top-20 w-48 h-48 bg-[#D46E15]/15 rounded-full blur-2xl pointer-events-none"></div>

            <div class="mb-6 relative z-10">
                <!-- Icon Box: Background Putih dengan Logo Berkat Dinasti (Item 39.4) -->
                <div class="w-12 h-12 bg-white rounded-xl flex items-center justify-center mb-4 p-2 shadow-sm border border-white/20">
                    <img src="assets/images/logo.png" alt="Logo Berkat Dinasti" class="w-full h-full object-contain">
                </div>
                <h2 class="text-[28px] leading-[34px] font-bold text-[#FAF8F5] tracking-tight mb-1.5">Selamat Datang</h2>
                <p class="text-[#D8C7B8] text-sm leading-[20px]">Masuk ke akun Anda untuk mengelola pesanan bakery</p>
            </div>

            <!-- Pesan Sukses Logout -->
            <?php if (!empty($success_message)): ?>
                <div class="mb-5 bg-emerald-950/60 border border-emerald-500/40 text-emerald-200 px-4 py-3 rounded-xl flex items-start gap-3 shadow-xs">
                    <i class="fas fa-check-circle text-emerald-400 shrink-0 mt-0.5"></i>
                    <div class="text-sm font-medium leading-relaxed">
                        <?= htmlspecialchars($success_message) ?>
                    </div>
                </div>
            <?php endif; ?>

            <!-- Pesan Error Login -->
            <?php if (!empty($error_message)): ?>
                <div class="mb-5 bg-rose-950/60 border border-rose-500/40 text-rose-200 px-4 py-3 rounded-xl flex items-start gap-3 shadow-xs">
                    <i class="fas fa-exclamation-triangle text-rose-400 shrink-0 mt-0.5"></i>
                    <div class="text-sm font-medium leading-relaxed">
                        <?= htmlspecialchars($error_message) ?>
                    </div>
                </div>
            <?php endif; ?>

            <!-- Form -->
            <form action="" method="POST" class="space-y-4 relative z-10">
                <!-- Username -->
                <div class="space-y-1.5">
                    <label for="username" class="block text-xs font-semibold text-[#D8C7B8] uppercase tracking-wider">Username</label>
                    <div class="relative">
                        <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-[#D8C7B8]/70">
                            <i class="fas fa-user text-xs"></i>
                        </div>
                        <input type="text" id="username" name="username" value="<?= htmlspecialchars($username_val) ?>" placeholder="Masukkan username" required autocomplete="username"
                            class="w-full pl-10 pr-4 py-2.5 text-sm bg-[#2D1E17] border border-[#544337] rounded-xl text-[#FAF8F5] placeholder-[#8E7E73] focus:outline-none focus:border-[#D46E15] focus:ring-1 focus:ring-[#D46E15] transition-all">
                    </div>
                </div>

                <!-- Password -->
                <div class="space-y-1.5">
                    <label for="password" class="block text-xs font-semibold text-[#D8C7B8] uppercase tracking-wider">Password</label>
                    <div class="relative">
                        <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-[#D8C7B8]/70">
                            <i class="fas fa-key text-xs"></i>
                        </div>
                        <input type="password" id="password" name="password" placeholder="Masukkan password" required autocomplete="current-password"
                            class="w-full pl-10 pr-10 py-2.5 text-sm bg-[#2D1E17] border border-[#544337] rounded-xl text-[#FAF8F5] placeholder-[#8E7E73] focus:outline-none focus:border-[#D46E15] focus:ring-1 focus:ring-[#D46E15] transition-all">
                        <button type="button" onclick="togglePasswordVisibility()" class="absolute inset-y-0 right-0 pr-3.5 flex items-center text-[#D8C7B8]/70 hover:text-white transition-colors focus:outline-none" title="Lihat/Sembunyikan password">
                            <i id="eye-icon" class="fas fa-eye text-xs"></i>
                        </button>
                    </div>
                </div>

                <!-- Remember Me (Item 39.1: Lupa password dihilangkan, Item 39.2: Ingat saya aktif 1 minggu) -->
                <div class="flex items-center justify-between pt-1 pb-2">
                    <label class="flex items-center gap-2 cursor-pointer select-none">
                        <input type="checkbox" name="remember" id="rememberMe" value="1" <?= !empty($remember_checked) ? 'checked' : '' ?> 
                            class="w-4 h-4 rounded border-[#544337] bg-[#2D1E17] text-[#D46E15] focus:ring-[#D46E15]/30 cursor-pointer">
                        <span class="text-xs font-medium text-[#D8C7B8]">Ingat saya (1 minggu)</span>
                    </label>
                </div>

                <!-- Submit Button -->
                <button type="submit" class="w-full h-11 text-sm font-semibold rounded-xl bg-[#D46E15] hover:bg-[#B85207] text-white shadow-button-orange flex justify-center items-center gap-2 transition-all duration-200 active:scale-[0.98]">
                    <span>Masuk</span>
                    <i class="fas fa-arrow-right text-xs"></i>
                </button>
            </form>

            <!-- Copyright -->
            <div class="mt-8 pt-6 border-t border-white/10 text-center relative z-10">
                <p class="text-[12px] text-[#A69485] tracking-[0.3px]">© 2026 Berkat Dinasti. All rights reserved.</p>
            </div>
        </div>
    </div>

    <!-- Script toggle password visibility -->
    <script>
        function togglePasswordVisibility() {
            const passwordInput = document.getElementById('password');
            const eyeIcon = document.getElementById('eye-icon');
            if (passwordInput.type === 'password') {
                passwordInput.type = 'text';
                eyeIcon.className = 'fas fa-eye-slash text-xs';
            } else {
                passwordInput.type = 'password';
                eyeIcon.className = 'fas fa-eye text-xs';
            }
        }
    </script>
</body>
</html>
