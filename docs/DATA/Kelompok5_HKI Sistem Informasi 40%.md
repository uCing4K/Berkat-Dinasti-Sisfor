1. Judul   
   PERANCANGAN USER INTERFACE / USER EXPERIENCE (UI/UX) BERKAT DINASTI UNTUK MENDUKUNG PENGELOLAAN PESANAN DAN INFORMASI OPERASIONAL UMKM 

2. Latar Belakang   
   Berkat Dinasti merupakan UMKM produsen dan penjual roti berbasis pre-order yang berlokasi di Desa Songokerto, Kota Batu. Berdasarkan hasil identifikasi kebutuhan pada tahap awal pengembangan, proses penerimaan pesanan dilakukan melalui WhatsApp dan tatap muka. Sementara itu, pencatatan pesanan, pembayaran, serta rekap informasi penjualan sebelumnya masih banyak dilakukan secara manual. Kondisi tersebut menimbulkan kebutuhan akan suatu rancangan antarmuka digital yang dapat membantu admin atau pemilik dalam mengelola informasi operasional secara lebih terstruktur, sederhana, dan mudah digunakan.  
   Berdasarkan kebutuhan tersebut, dikembangkan rancangan User Interface/User Experience (UI/UX) yang berfokus pada aktivitas internal admin atau pemilik. Perancangan tidak ditujukan untuk menggantikan mekanisme pemesanan pelanggan yang telah berjalan, karena pelanggan tetap melakukan pemesanan melalui WhatsApp maupun secara langsung. Oleh karena itu, rancangan UI/UX diarahkan untuk mendukung aktivitas internal, khususnya dalam melihat dan menambahkan pesanan, memantau jadwal atau deadline pengiriman, memperbarui status pesanan, melihat informasi produk, serta memantau informasi pemasukan.  
   Dalam proses pengembangan sebelumnya terdapat rancangan basis data untuk mendukung sistem. Namun, basis data tersebut pada tahap pengembangan saat ini sudah tidak digunakan dan tidak menjadi bagian utama dari karya yang diajukan. Fokus karya diarahkan sepenuhnya pada hasil perancangan UI/UX, meliputi struktur informasi, arsitektur navigasi, alur interaksi pengguna, wireframe, prototype low-fidelity dan high-fidelity, komponen antarmuka, serta keputusan desain yang dibangun berdasarkan kebutuhan pengguna.  
   Perancangan UI/UX dilakukan dengan pendekatan Design Thinking melalui tahapan identifikasi kebutuhan pengguna, perumusan permasalahan, pengembangan ide, pembuatan prototype, dan pengujian terhadap rancangan. Hasil pengujian digunakan untuk mengevaluasi kemudahan penggunaan dan kesesuaian rancangan dengan kebutuhan admin atau pemilik.  
   Perancangan ini juga memiliki keterkaitan dengan Sustainable Development Goals (SDGs), khususnya SDG 8: Decent Work and Economic Growth, melalui upaya mendukung produktivitas dan penguatan aktivitas operasional UMKM dengan pemanfaatan rancangan teknologi digital yang sesuai dengan kebutuhan pengguna. Karya ini juga mendukung SDG 9: Industry, Innovation and Infrastructure melalui pengembangan inovasi digital dalam bentuk rancangan UI/UX yang dirancang secara sistematis berdasarkan kebutuhan pengguna.  
   Dengan demikian, karya ini menghasilkan suatu rancangan UI/UX yang menitikberatkan pada kemudahan akses informasi, kesederhanaan navigasi, serta efektivitas pengelolaan pesanan dan informasi operasional. Hasil rancangan tersebut menjadi objek utama karya yang diajukan dalam pengurusan Hak Kekayaan Intelektual (HKI), sekaligus menjadi bentuk kontribusi pada pemanfaatan inovasi digital untuk mendukung penguatan operasional UMKM.  
3. Tujuan   
1. Menghasilkan rancangan UI/UX yang sesuai dengan kebutuhan admin/pemilik Berkat Dinasti dalam mendukung pengelolaan pesanan dan informasi operasional UMKM.  
2. Merancang struktur informasi dan alur interaksi yang sederhana, jelas, dan mudah dipahami sehingga pengguna dapat mengakses informasi operasional secara lebih efektif.  
3. Menyediakan prototype UI/UX dalam bentuk rancangan low-fidelity dan high-fidelity sebagai representasi visual dan interaktif dari rancangan yang dikembangkan.  
4. Mempermudah pengelolaan informasi pesanan, khususnya dalam aktivitas menambahkan pesanan, melihat daftar pesanan, memantau deadline pengiriman, serta memperbarui status pesanan menjadi Proses atau Selesai.  
5. Mempermudah pemantauan informasi operasional, meliputi data jenis roti/produk, informasi pelanggan, serta informasi pemasukan yang dibutuhkan oleh admin/pemilik.  
6. Menerapkan pendekatan Design Thinking dalam proses perancangan UI/UX dengan mempertimbangkan kebutuhan, permasalahan, dan hasil pengujian dari pengguna.  
7. Mendukung proses digitalisasi internal UMKM melalui rancangan antarmuka yang sederhana, informatif, dan sesuai dengan aktivitas operasional Berkat Dinasti, tanpa mengubah mekanisme pemesanan pelanggan yang tetap dilakukan melalui WhatsApp maupun secara offline.  
8. Mendukung SDG 8 (Decent Work and Economic Growth) melalui perancangan UI/UX yang membantu meningkatkan efektivitas pengelolaan informasi dan aktivitas operasional UMKM sebagai bagian dari penguatan produktivitas usaha.  
9. Mendukung SDG 9 (Industry, Innovation and Infrastructure) melalui pengembangan inovasi digital berupa rancangan UI/UX yang berorientasi pada kebutuhan pengguna dan dapat menjadi dasar pengembangan layanan digital bagi aktivitas operasional UMKM.

IV. Rancangan UI/UX yang Dikembangkan

Rancangan antarmuka pengguna (*User Interface*) dan pengalaman pengguna (*User Experience*) Berkat Dinasti dirancang dengan menitikberatkan pada kemudahan penggunaan (*usability*), efisiensi alur kerja operasional internal UMKM, dan konsistensi visual identitas usaha bakery berbasis *pre-order*. Komponen antarmuka yang dikembangkan dan diwujudkan ke dalam implementasi program komputer berbasis web meliputi:

1. **Identitas Visual dan Sistem Desain (*Design System*)**
   - **Palet Warna Tematik Bakery**: Menggunakan kombinasi warna *Brand Brown* (`#3E2B21`) untuk panel identitas utama, *Brand Orange* (`#FF9B45` & `#E88C3D`) sebagai warna aksen interaktif dan tombol aksi utama (*Call to Action*), serta *Soft Neutral Background* (`#F9F7F5`) sebagai latar belakang kerja yang bersih dan ramah di mata.
   - **Tipografi Modern**: Menggunakan jenis huruf sans-serif modern *Inter* dari Google Fonts untuk memastikan keterbacaan (*readability*) optimal pada layar komputer desktop maupun perangkat bergerak.
   - **Tata Letak Responsif (*Split-Screen Layout*)**: Memisahkan area *branding display* informatif di sisi kiri (desktop) dan formulir interaksi di sisi kanan untuk menjaga fokus pengguna.

2. **Rancangan Antarmuka Halaman Login (`public/login.php`)**
   - Berfungsi sebagai gerbang autentikasi utama bagi admin/pemilik UMKM untuk mengakses sistem manajemen internal.
   - Dilengkapi kartu logo Berkat Dinasti dengan efek visual *glow*, label penanda sistem manajemen pesanan, indikator status sistem internal yang terverifikasi, formulir input username dan password dengan ikon pemandu, tombol interaktif *toggle* visibilitas kata sandi (*show/hide password*), fitur persistensi sesi (*Remember Me*), serta tautan navigasi menuju pemulihan sandi.

3. **Rancangan Antarmuka Halaman Reset Password (`public/reset-password.php`)**
   - Berfungsi sebagai alur penanganan pemulihan kredensial akun staf/admin secara terkendali.
   - Dilengkapi tombol navigasi kembali (*Kembali ke Login*), formulir pengajuan pemulihan username, serta kartu notifikasi operasional (*Service Level Agreement* 1x24 jam oleh Super Admin) yang memberikan kejelasan proses pemulihan akun bagi pengguna internal.

4. **Modul Pengarah Navigasi Sistem (`index.php`)**
   - Bertindak sebagai pengarah jalur otomatis (*HTTP redirector*) dari direktori utama sistem ke halaman login secara terpusat, konsisten, dan aman.

---

V. Kode Sumber (*Source Code*) Program

Berikut adalah tabel ringkasan berkas kode sumber program komputer yang diimplementasikan pada antarmuka sistem:

| No | Nama Berkas | Direktori (*Path*) | Bahasa Pemrograman | Peran & Deskripsi Fungsional |
|:---:|---|---|---|---|
| 1 | `index.php` | `/index.php` | PHP | Entry Point & Router Redirect ke Antarmuka Login |
| 2 | `login.php` | `/public/login.php` | PHP, HTML5, Tailwind CSS, JS | Antarmuka Autentikasi Pengguna & Branding Bakery |
| 3 | `reset-password.php` | `/public/reset-password.php` | PHP, HTML5, Tailwind CSS | Antarmuka Pemulihan Password Admin Internal |

---

### 5.1 Kode Sumber File: `index.php`
- **Lokasi Berkas**: `/index.php`
- **Bahasa Pemrograman**: PHP
- **Deskripsi Fungsional**: Modul *entry point* navigasi yang secara otomatis mengarahkan akses pengguna ke antarmuka login.

```php
<?php
// Script sederhana untuk otomatis mengarahkan pengunjung ke halaman login
header("Location: public/login.php");
exit();
?>
```

---

### 5.2 Kode Sumber File: `public/login.php`
- **Lokasi Berkas**: `/public/login.php`
- **Bahasa Pemrograman**: PHP, HTML5, Tailwind CSS, JavaScript
- **Deskripsi Fungsional**: Modul antarmuka halaman login admin lengkap dengan sistem desain Tailwind CSS, responsive split-screen layout, dan kontrol formulir interaktif.

```php
<?php
session_start();
// Hapus atau komen include config jika belum ada koneksi db di halaman ini
// require_once "../config/config.php"; 
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
                    <a href="#" class="text-[14px] font-medium text-brand-orange hover:text-brand-orangeHover transition-colors">Lupa Password?</a>
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
```

---

### 5.3 Kode Sumber File: `public/reset-password.php`
- **Lokasi Berkas**: `/public/reset-password.php`
- **Bahasa Pemrograman**: PHP, HTML5, Tailwind CSS
- **Deskripsi Fungsional**: Modul antarmuka reset password untuk alur pengajuan pemulihan akun admin internal beserta kartu informasi kebijakan operasional.

```php
<?php
session_start();
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reset Password - Berkat Dinasti</title>
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
        .logo-glow {
            box-shadow: 0px 8px 10px -6px rgba(0, 0, 0, 0.10), 0px 20px 25px -5px rgba(0, 0, 0, 0.10), 0 0 35px rgba(255, 155, 69, 0.35);
        }
    </style>
</head>
<body class="h-screen w-full m-0 flex overflow-hidden">
    
    <!-- LEFT PANEL: Branding -->
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

    <!-- RIGHT PANEL: Reset Password Form -->
    <div class="flex-1 flex justify-center items-center p-6 lg:p-8 bg-brand-bg relative">
        
        <!-- Kembali ke Login (Absolute Top Left in Right Panel) -->
        <a href="login.php" class="absolute top-8 left-8 flex items-center gap-2 text-[#828282] hover:text-brand-orange transition-colors font-medium text-[14px]">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path></svg>
            Kembali ke Login
        </a>

        <div class="w-full max-w-[440px] bg-white shadow-[0_8px_24px_rgba(0,0,0,0.12)] rounded-2xl p-8 lg:p-12">
            
            <div class="mb-8">
                <div class="w-10 h-10 bg-brand-orange/10 rounded-xl flex items-center justify-center mb-4">
                    <!-- Key Icon -->
                    <svg class="w-5 h-5 text-brand-orange" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 7a2 2 0 012 2m4 0a6 6 0 01-7.743 5.743L11 17H9v2H7v2H4a1 1 0 01-1-1v-2.586a1 1 0 01.293-.707l5.964-5.964A6 6 0 1121 9z"></path></svg>
                </div>
                <h2 class="text-[32px] leading-[40px] font-bold text-[#1D1D1D] mb-2">Reset Password</h2>
                <p class="text-[#828282] text-sm leading-[20px]">Masukkan username Anda dan kami akan mengirim tautan reset ke admin.</p>
            </div>

            <!-- Form -->
            <form action="" method="POST" class="space-y-6">
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

                <!-- Info Message -->
                <div class="bg-[#F2F2F2] rounded-lg p-3 flex items-start gap-3">
                    <svg class="w-5 h-5 text-[#828282] mt-0.5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                    <p class="text-[#828282] text-[12px] leading-[18px]">Tautan reset akan diproses oleh Super Admin dalam 1x24 jam.</p>
                </div>

                <!-- Submit Button -->
                <button type="submit" class="w-full h-12 bg-brand-orange hover:bg-brand-orangeHover text-white font-semibold rounded-lg flex justify-center items-center transition-colors text-[16px]">
                    Kirim Permintaan Reset
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
```
