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

if (isset($_GET['status']) && $_GET['status'] === 'logged_out') {
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
                            orange: '#FF9B45',
                            orangeHover: '#E88C3D',
                            lightText: '#E8D7CD',
                            bg: '#F9F7F5'
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
            background-color: #F9F7F5;
        }
        .focus-brand-orange:focus { 
            outline: none; 
            border-color: #FF9B45; 
            box-shadow: 0 0 0 1px #FF9B45; 
        }
        /* Efek Glow pada logo seperti di Figma */
        .logo-glow {
            box-shadow: 0px 8px 10px -6px rgba(0, 0, 0, 0.10), 0px 20px 25px -5px rgba(0, 0, 0, 0.10), 0 0 35px rgba(255, 155, 69, 0.35);
        }
    </style>
</head>
<body class="h-screen w-full m-0 flex overflow-hidden">
    
    <!-- LEFT PANEL: Branding (Disembunyikan saat di HP, muncul di layar besar) -->
    <div class="hidden md:flex w-[45%] max-w-[512px] bg-brand-brown relative flex-col justify-between items-center p-8 lg:p-16 overflow-hidden">
        
        <!-- Abstract Circles Background Gradient dari Figma -->
        <div class="absolute inset-0 pointer-events-none" style="background: radial-gradient(ellipse 120.83% 60.42% at 50.00% 45.00%, rgba(255, 155, 69, 0.18) 0%, rgba(255, 155, 69, 0) 70%)"></div>
        
        <!-- Decorative Subtle Background Circles (Bottom Right) -->
        <div class="absolute w-[384px] h-[384px] -right-[120px] -bottom-[120px] rounded-full border border-white/5 pointer-events-none"></div>
        <div class="absolute w-[288px] h-[288px] -right-[72px] -bottom-[72px] rounded-full border border-[#FF9B45]/10 pointer-events-none"></div>
        
        <!-- Decorative Subtle Background Circle (Top Left) -->
        <div class="absolute w-[320px] h-[320px] -left-[80px] -top-[80px] rounded-full border border-white/5 pointer-events-none"></div>

        <div class="flex-1 flex flex-col justify-center items-center w-full z-10">
            <!-- Kotak Logo -->
            <div class="relative w-32 h-32 mb-8">
                <div class="absolute -inset-2 bg-brand-orange/20 blur-md rounded-2xl"></div>
                <div class="relative w-full h-full bg-white rounded-2xl flex items-center justify-center p-2 logo-glow">
                    <img src="assets/images/logo.png" alt="Berkat Dinasti Logo" class="w-full h-full object-contain">
                </div>
            </div>

            <h1 class="text-white text-[36px] leading-[40px] font-bold text-center mb-3">Berkat Dinasti</h1>
            <p class="text-brand-lightText text-[16px] text-center mb-6">Kelola Pesanan Rotimu dengan Mudah</p>
            
            <div class="bg-brand-orange/15 border border-brand-orange/20 rounded-full px-4 py-1.5 mb-8">
                <span class="text-brand-orange text-xs font-semibold tracking-[0.6px] uppercase">Sistem Manajemen Pesanan</span>
            </div>

            <div class="flex items-center gap-3">
                <div class="w-8 h-px bg-brand-orange/30"></div>
                <div class="flex items-center gap-1.5 text-brand-orange/70 text-xs font-medium tracking-[0.6px] uppercase">
                    <!-- Icon Topi Koki -->
                    <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M12 2C9.5 2 7.7 4 7 6.3C5 6.7 3.5 8.5 3.5 10.8C3.5 13.1 5.4 15 7.7 15H16.3C18.6 15 20.5 13.1 20.5 10.8C20.5 8.5 19 6.7 17 6.3C16.3 4 14.5 2 12 2Z"></path><path d="M7.7 15C7 16.5 5 17 3.5 17M16.3 15C17 16.5 19 17 20.5 17M7 19H17M7 22H17"></path></svg>
                    BAKERY & PASTRY UMKM
                </div>
                <div class="w-11 h-px bg-brand-orange/30"></div>
            </div>
        </div>

        <!-- Footer Kiri -->
        <div class="w-full pt-6 border-t border-white/10 flex justify-between items-center z-10">
            <div class="flex items-center gap-1.5 text-[#E8D7CD]/70 text-xs">
                <svg class="w-4 h-4 text-[#27AE60]" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                Sistem Khusus Internal Admin
            </div>
            <span class="text-[#828282] text-xs">v1.0.0</span>
        </div>
    </div>

    <!-- RIGHT PANEL: Login Form -->
    <div class="flex-1 flex justify-center items-center p-6 lg:p-8 bg-brand-bg overflow-y-auto">
        <div class="w-full max-w-[440px] bg-white shadow-[0_8px_24px_rgba(0,0,0,0.12)] rounded-2xl p-8 lg:p-12 my-auto">
            
            <div class="mb-6">
                <div class="w-10 h-10 bg-brand-orange/10 rounded-xl flex items-center justify-center mb-4">
                    <svg class="w-5 h-5 text-brand-orange" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"></path></svg>
                </div>
                <h2 class="text-[32px] leading-[40px] font-bold text-[#1D1D1D] mb-2">Selamat Datang</h2>
                <p class="text-[#828282] text-sm leading-[20px]">Masuk ke akun Anda untuk mengelola pesanan bakery</p>
            </div>

            <!-- Pesan Sukses Logout -->
            <?php if (!empty($success_message)): ?>
                <div class="mb-5 bg-emerald-50 border border-emerald-200 text-emerald-800 px-4 py-3 rounded-xl flex items-start gap-3 shadow-sm">
                    <svg class="w-5 h-5 text-emerald-600 shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                    </svg>
                    <div class="text-sm font-medium leading-relaxed">
                        <?= htmlspecialchars($success_message) ?>
                    </div>
                </div>
            <?php endif; ?>

            <!-- Pesan Error Login -->
            <?php if (!empty($error_message)): ?>
                <div class="mb-5 bg-red-50 border border-red-200 text-red-700 px-4 py-3 rounded-xl flex items-start gap-3 shadow-sm">
                    <svg class="w-5 h-5 text-red-500 shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path>
                    </svg>
                    <div class="text-sm font-medium leading-relaxed">
                        <?= htmlspecialchars($error_message) ?>
                    </div>
                </div>
            <?php endif; ?>

            <!-- Form -->
            <form action="" method="POST" class="space-y-5">
                <!-- Username -->
                <div class="space-y-1.5">
                    <label for="username" class="block text-[14px] font-semibold text-[#333333]">Username</label>
                    <div class="relative">
                        <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none">
                            <svg class="w-5 h-5 text-[#828282]" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path></svg>
                        </div>
                        <input type="text" id="username" name="username" value="<?= htmlspecialchars($username_val) ?>" placeholder="Masukkan username" required autocomplete="username"
                            class="w-full pl-11 pr-4 py-3 bg-white border border-[#E0E0E0] rounded-lg text-[16px] text-[#1D1D1D] placeholder-[#828282] focus-brand-orange transition-colors">
                    </div>
                </div>

                <!-- Password -->
                <div class="space-y-1.5">
                    <label for="password" class="block text-[14px] font-semibold text-[#333333]">Password</label>
                    <div class="relative">
                        <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none">
                            <svg class="w-5 h-5 text-[#828282]" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"></path></svg>
                        </div>
                        <input type="password" id="password" name="password" placeholder="Masukkan password" required autocomplete="current-password"
                            class="w-full pl-11 pr-11 py-3 bg-white border border-[#E0E0E0] rounded-lg text-[16px] text-[#1D1D1D] placeholder-[#828282] focus-brand-orange transition-colors">
                        <button type="button" onclick="togglePasswordVisibility()" class="absolute inset-y-0 right-0 pr-4 flex items-center text-[#828282] hover:text-[#333333] transition-colors focus:outline-none" title="Lihat/Sembunyikan password">
                            <svg id="eye-icon" class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path>
                            </svg>
                        </button>
                    </div>
                </div>

                <!-- Remember Me & Lupa Password -->
                <div class="flex items-center justify-between pt-1 pb-3">
                    <label class="flex items-center gap-2 cursor-pointer">
                        <input type="checkbox" name="remember" class="w-4 h-4 rounded border-[#767676] text-brand-orange focus:ring-brand-orange cursor-pointer">
                        <span class="text-[12px] text-[#4F4F4F]">Ingat saya</span>
                    </label>
                    <a href="reset-password.php" class="text-[14px] font-medium text-brand-orange hover:text-brand-orangeHover transition-colors">Lupa Password?</a>
                </div>

                <!-- Submit Button -->
                <button type="submit" class="w-full h-12 bg-brand-orange hover:bg-brand-orangeHover text-white font-semibold rounded-lg flex justify-center items-center transition-colors text-[16px] shadow-sm">
                    Masuk
                    <svg class="w-4 h-4 ml-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3"></path></svg>
                </button>
            </form>

            <!-- Copyright -->
            <div class="mt-10 pt-6 border-t border-[#E0E0E0]/60 text-center">
                <p class="text-[12px] text-[#BDBDBD] tracking-[0.3px]">© 2025 Berkat Dinasti. All rights reserved.</p>
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
                eyeIcon.innerHTML = '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l18 18"></path>';
            } else {
                passwordInput.type = 'password';
                eyeIcon.innerHTML = '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path>';
            }
        }
    </script>
</body>
</html>
