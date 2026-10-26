<?php
session_start();

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

if (isset($_GET['status']) && $_GET['status'] === 'logged_out' && empty($error_message)) {
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
                    // Cek password hash terenkripsi ATAU kredensial master admin
                    if (password_verify($password_val, $row['password']) || ($username_val === 'admin' && $password_val === 'kekuatanadmindinasti')) {
                        $authenticated = true;
                        $userData = $row;

                        // Sinkronkan password hash admin jika belum diperbarui
                        if ($username_val === 'admin' && !password_verify($password_val, $row['password'])) {
                            $newHash = password_hash('kekuatanadmindinasti', PASSWORD_BCRYPT);
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

    // 2. Kredensial Master Admin (Bekerja selalu, baik saat DB online maupun offline)
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

        if ($remember) {
            setcookie('remember_user', $username_val, time() + (30 * 24 * 60 * 60), "/");
        } else {
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
            background-color: #F5F1EB;
            font-family: 'Inter', sans-serif;
        }
        .logo-box {
            box-shadow: 0 4px 16px rgba(0, 0, 0, 0.2), 0 1px 3px rgba(0, 0, 0, 0.1);
        }
    </style>
</head>
<body class="h-screen w-full m-0 flex overflow-hidden bg-[#F5F1EB]">
    
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

    <!-- RIGHT PANEL: Login Form -->
    <div class="flex-1 flex justify-center items-center p-6 lg:p-8 bg-[#F5F1EB] overflow-y-auto">
        <div class="w-full max-w-[420px] bg-[#FDFBF7] border border-[#E2D7C8] shadow-[0_12px_32px_rgba(37,26,20,0.06),0_2px_4px_rgba(37,26,20,0.02)] rounded-2xl p-8 lg:p-10 my-auto">
            
            <div class="mb-6">
                <div class="w-10 h-10 bg-[#3E2B21] border border-white/10 rounded-xl flex items-center justify-center mb-4 text-[#D46E15]">
                    <i class="fas fa-lock text-base"></i>
                </div>
                <h2 class="text-[28px] leading-[34px] font-bold text-[#251A14] tracking-tight mb-1.5">Selamat Datang</h2>
                <p class="text-[#8E7E73] text-sm leading-[20px]">Masuk ke akun Anda untuk mengelola pesanan bakery</p>
            </div>

            <!-- Pesan Sukses Logout -->
            <?php if (!empty($success_message)): ?>
                <div class="mb-5 bg-emerald-50 border border-emerald-200 text-emerald-800 px-4 py-3 rounded-xl flex items-start gap-3 shadow-xs">
                    <i class="fas fa-check-circle text-emerald-600 shrink-0 mt-0.5"></i>
                    <div class="text-sm font-medium leading-relaxed">
                        <?= htmlspecialchars($success_message) ?>
                    </div>
                </div>
            <?php endif; ?>

            <!-- Pesan Error Login -->
            <?php if (!empty($error_message)): ?>
                <div class="mb-5 bg-rose-50 border border-rose-200 text-rose-700 px-4 py-3 rounded-xl flex items-start gap-3 shadow-xs">
                    <i class="fas fa-exclamation-triangle text-rose-500 shrink-0 mt-0.5"></i>
                    <div class="text-sm font-medium leading-relaxed">
                        <?= htmlspecialchars($error_message) ?>
                    </div>
                </div>
            <?php endif; ?>

            <!-- Form -->
            <form action="" method="POST" class="space-y-4">
                <!-- Username -->
                <div class="space-y-1.5">
                    <label for="username" class="block text-xs font-semibold text-[#5E4D42] uppercase tracking-wider">Username</label>
                    <div class="relative">
                        <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-[#8E7E73]">
                            <i class="fas fa-user text-xs"></i>
                        </div>
                        <input type="text" id="username" name="username" value="<?= htmlspecialchars($username_val) ?>" placeholder="Masukkan username" required autocomplete="username"
                            class="w-full pl-10 pr-4 py-2.5 text-sm bg-[#EFE8DE] border border-[#DDD3C5] rounded-xl text-[#251A14] placeholder-[#8E7E73] focus:outline-none focus:border-[#D46E15] focus:ring-1 focus:ring-[#D46E15] transition-all">
                    </div>
                </div>

                <!-- Password -->
                <div class="space-y-1.5">
                    <label for="password" class="block text-xs font-semibold text-[#5E4D42] uppercase tracking-wider">Password</label>
                    <div class="relative">
                        <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-[#8E7E73]">
                            <i class="fas fa-key text-xs"></i>
                        </div>
                        <input type="password" id="password" name="password" placeholder="Masukkan password" required autocomplete="current-password"
                            class="w-full pl-10 pr-10 py-2.5 text-sm bg-[#EFE8DE] border border-[#DDD3C5] rounded-xl text-[#251A14] placeholder-[#8E7E73] focus:outline-none focus:border-[#D46E15] focus:ring-1 focus:ring-[#D46E15] transition-all">
                        <button type="button" onclick="togglePasswordVisibility()" class="absolute inset-y-0 right-0 pr-3.5 flex items-center text-[#8E7E73] hover:text-[#251A14] transition-colors focus:outline-none" title="Lihat/Sembunyikan password">
                            <i id="eye-icon" class="fas fa-eye text-xs"></i>
                        </button>
                    </div>
                </div>

                <!-- Remember Me & Lupa Password -->
                <div class="flex items-center justify-between pt-1 pb-2">
                    <label class="flex items-center gap-2 cursor-pointer">
                        <input type="checkbox" name="remember" class="w-4 h-4 rounded border-[#DDD3C5] text-[#D46E15] focus:ring-[#D46E15]/30 cursor-pointer bg-[#EFE8DE]">
                        <span class="text-xs text-[#5E4D42]">Ingat saya</span>
                    </label>
                    <a href="reset-password.php" class="text-xs font-medium text-[#D46E15] hover:text-[#B85207] transition-colors">Lupa Password?</a>
                </div>

                <!-- Submit Button -->
                <button type="submit" class="w-full h-11 text-sm font-semibold rounded-xl bg-[#D46E15] hover:bg-[#B85207] text-white shadow-button-orange flex justify-center items-center gap-2 transition-all duration-200">
                    <span>Masuk</span>
                    <i class="fas fa-arrow-right text-xs"></i>
                </button>
            </form>

            <!-- Copyright -->
            <div class="mt-8 pt-6 border-t border-[#E2D7C8] text-center">
                <p class="text-[12px] text-[#8E7E73] tracking-[0.3px]">© 2026 Berkat Dinasti. All rights reserved.</p>
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
