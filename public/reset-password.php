<?php
session_start();
$success_msg = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $requested_user = trim($_POST['username'] ?? '');
    if (!empty($requested_user)) {
        $success_msg = "Permintaan reset password untuk akun \"{$requested_user}\" telah berhasil dikirimkan ke Super Admin. Silakan hubungi admin operasional untuk mendapatkan kredensial baru.";
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reset Password - Berkat Dinasti</title>
    <link rel="icon" type="image/png" href="assets/images/logo.png">
    
    <!-- Font Awesome 5 Free CDN -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css" crossorigin="anonymous" referrerpolicy="no-referrer" />
    <!-- Tailwind CSS CDN -->
    <script src="https://cdn.tailwindcss.com"></script>
    <!-- Google Fonts: Inter -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
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
    
    <!-- LEFT PANEL: Branding -->
    <div class="hidden md:flex w-[45%] max-w-[512px] bg-[#3E2B21] border-r border-[#2C1E17] relative flex-col justify-between items-center p-8 lg:p-16 overflow-hidden">
        
        <!-- Abstract Circles Background Gradient dari Figma -->
        <div class="absolute inset-0 pointer-events-none" style="background: radial-gradient(ellipse 120.83% 60.42% at 50.00% 45.00%, rgba(212, 110, 21, 0.22) 0%, rgba(212, 110, 21, 0) 70%)"></div>
        
        <!-- Decorative Subtle Background Circles (Bottom Right) -->
        <div class="absolute w-[384px] h-[384px] -right-[120px] -bottom-[120px] rounded-full border border-white/5 pointer-events-none"></div>
        <div class="absolute w-[288px] h-[288px] -right-[72px] -bottom-[72px] rounded-full border border-[#D46E15]/15 pointer-events-none"></div>
        
        <!-- Decorative Subtle Background Circle (Top Left) -->
        <div class="absolute w-[320px] h-[320px] -left-[80px] -top-[80px] rounded-full border border-white/5 pointer-events-none"></div>

        <div class="flex-1 flex flex-col justify-center items-center w-full z-10">
            <!-- Kotak Logo (Artisan Craft Style) -->
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

    <!-- RIGHT PANEL: Reset Password Form -->
    <div class="flex-1 flex justify-center items-center p-6 lg:p-8 bg-[#F5F1EB] overflow-y-auto">
        <div class="w-full max-w-[440px] bg-[#FDFBF7] border border-[#E2D7C8] shadow-[0_12px_32px_rgba(37,26,20,0.06),0_2px_4px_rgba(37,26,20,0.02)] rounded-2xl p-8 lg:p-10 my-auto">
            
            <div class="mb-8">
                <div class="w-10 h-10 bg-[#3E2B21] border border-white/10 rounded-xl flex items-center justify-center mb-4 text-[#D46E15]">
                    <!-- Key Icon -->
                    <i class="fas fa-key text-base"></i>
                </div>
                <h2 class="text-[28px] leading-[34px] font-bold text-[#251A14] tracking-tight mb-2">Reset Password</h2>
                <p class="text-[#8E7E73] text-sm leading-[20px]">Masukkan username Anda dan kami akan mengirim tautan reset ke admin.</p>
            </div>

            <?php if (!empty($success_msg)): ?>
                <!-- Alert Sukses Permintaan Reset -->
                <div class="mb-6 p-4 rounded-xl bg-emerald-50 border border-emerald-200 flex items-start gap-3 text-sm text-emerald-800 shadow-xs">
                    <i class="fas fa-check-circle text-emerald-600 mt-0.5 shrink-0 text-base"></i>
                    <div>
                        <p class="font-bold">Permintaan Terkirim!</p>
                        <p class="text-xs mt-0.5 text-emerald-700 leading-relaxed"><?= htmlspecialchars($success_msg) ?></p>
                    </div>
                </div>
            <?php endif; ?>

            <!-- Form -->
            <form action="" method="POST" class="space-y-5">
                <!-- Username -->
                <div class="space-y-1.5">
                    <label for="username" class="block text-xs font-semibold text-[#5E4D42] uppercase tracking-wider">Username</label>
                    <div class="relative">
                        <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-[#8E7E73]">
                            <i class="fas fa-user text-xs"></i>
                        </div>
                        <input type="text" id="username" name="username" placeholder="Masukkan username" required
                            class="w-full pl-10 pr-4 py-2.5 text-sm bg-[#EFE8DE] border border-[#DDD3C5] rounded-xl text-[#251A14] placeholder-[#8E7E73] focus:outline-none focus:border-[#D46E15] focus:ring-1 focus:ring-[#D46E15] transition-all">
                    </div>
                </div>

                <!-- Info Message -->
                <div class="bg-[#EFE8DE] border border-[#DDD3C5] rounded-xl p-3 flex items-start gap-3">
                    <i class="fas fa-info-circle text-[#8E7E73] mt-0.5 shrink-0 text-sm"></i>
                    <p class="text-[#5E4D42] text-xs leading-[18px]">Tautan reset akan diproses oleh Super Admin dalam 1x24 jam.</p>
                </div>

                <!-- Submit Button -->
                <button type="submit" class="w-full h-11 text-sm font-semibold rounded-xl bg-[#D46E15] hover:bg-[#B85207] text-white shadow-button-orange flex justify-center items-center gap-2 transition-all duration-200">
                    <span>Kirim Permintaan Reset</span>
                    <i class="fas fa-paper-plane text-xs"></i>
                </button>

                <div class="text-center pt-2">
                    <a href="login.php" class="text-xs font-medium text-[#D46E15] hover:text-[#B85207] inline-flex items-center gap-1.5 transition-colors">
                        <i class="fas fa-arrow-left text-xs"></i>
                        Kembali ke Halaman Login
                    </a>
                </div>
            </form>

            <!-- Copyright -->
            <div class="mt-8 pt-6 border-t border-[#E2D7C8] text-center">
                <p class="text-[12px] text-[#8E7E73] tracking-[0.3px]">© 2026 Berkat Dinasti. All rights reserved.</p>
            </div>
        </div>
    </div>
</body>
</html>
