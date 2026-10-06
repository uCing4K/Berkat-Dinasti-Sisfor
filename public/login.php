<?php
session_start();
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $_SESSION['user'] = $_POST['username'] ?? 'Admin';
    header("Location: beranda.php");
    exit();
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Berkat Dinasti</title>
    <!-- Kita pakai Tailwind CSS CDN agar codingan rapi dan sesuai persis dengan Figma tanpa file CSS eksternal panjang -->
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
        /* Efek Glow pada logo seperti di figma */
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
                    <!-- Mengambil logo dari folder public/assets/images yang baru kucopy -->
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
                    <!-- Icon Topi Koki (Custom SVG mirip Bakery) -->
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
    <div class="flex-1 flex justify-center items-center p-6 lg:p-8 bg-brand-bg">
        <div class="w-full max-w-[440px] bg-white shadow-[0_8px_24px_rgba(0,0,0,0.12)] rounded-2xl p-8 lg:p-12">
            
            <div class="mb-8">
                <div class="w-10 h-10 bg-brand-orange/10 rounded-xl flex items-center justify-center mb-4">
                    <svg class="w-5 h-5 text-brand-orange" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"></path></svg>
                </div>
                <h2 class="text-[32px] leading-[40px] font-bold text-[#1D1D1D] mb-2">Selamat Datang</h2>
                <p class="text-[#828282] text-sm leading-[20px]">Masuk ke akun Anda untuk mengelola pesanan bakery</p>
            </div>

            <!-- Form -->
            <form action="" method="POST" class="space-y-5">
                <!-- Username -->
                <div class="space-y-1.5">
                    <label for="username" class="block text-[14px] font-semibold text-[#333333]">Username</label>
                    <div class="relative">
                        <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none">
                            <svg class="w-5 h-5 text-[#828282]" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path></svg>
                        </div>
                        <input type="text" id="username" name="username" placeholder="Masukkan username" required
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
                        <input type="password" id="password" name="password" placeholder="Masukkan password" required
                            class="w-full pl-11 pr-11 py-3 bg-white border border-[#E0E0E0] rounded-lg text-[16px] text-[#1D1D1D] placeholder-[#828282] focus-brand-orange transition-colors">
                        <button type="button" class="absolute inset-y-0 right-0 pr-4 flex items-center text-[#828282] hover:text-[#333333]">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path></svg>
                        </button>
                    </div>
                </div>

                <!-- Remember Me & Lupa Password -->
                <div class="flex items-center justify-between pt-1 pb-3">
                    <label class="flex items-center gap-2 cursor-pointer">
                        <input type="checkbox" name="remember" class="w-4 h-4 rounded border-[#767676] text-brand-orange focus:ring-brand-orange">
                        <span class="text-[12px] text-[#4F4F4F]">Ingat saya</span>
                    </label>
                    <a href="reset-password.php" class="text-[14px] font-medium text-brand-orange hover:text-brand-orangeHover transition-colors">Lupa Password?</a>
                </div>

                <!-- Submit Button -->
                <button type="submit" class="w-full h-12 bg-brand-orange hover:bg-brand-orangeHover text-white font-semibold rounded-lg flex justify-center items-center transition-colors text-[16px]">
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
</body>
</html>
