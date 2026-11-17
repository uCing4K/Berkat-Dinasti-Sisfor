<?php
/**
 * Public Track Order Page (Zero-Login Order Tracking)
 * Sistem Informasi Berkat Dinasti (Bakery & Pastry UMKM)
 * 
 * Memungkinkan pelanggan melacak status pembuatan roti dan pengiriman secara instan
 * tanpa login menggunakan capability token.
 */

$configFile = __DIR__ . "/../config/config.php";
if (file_exists($configFile)) {
    require_once $configFile;
}
require_once __DIR__ . "/../app/Helpers/ReceiptHelper.php";

header("X-Robots-Tag: noindex, nofollow");
header("Referrer-Policy: no-referrer");

$token = isset($_GET['token']) ? trim($_GET['token']) : '';
if (!empty($token) && ReceiptHelper::isValidTrackingToken($token)) {
    // Redirect langsung ke nota digital yang sudah memiliki live tracking terintegrasi
    header("Location: nota-digital.php?token=" . urlencode($token));
    exit();
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Lacak Pesanan Roti - Berkat Dinasti Bakery</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&family=JetBrains+Mono:wght@500;600&display=swap" rel="stylesheet">
    <style>
        body {
            font-family: 'Inter', sans-serif;
            background-color: #F8ECE1;
            background-image: 
                radial-gradient(ellipse 90% 60% at 70% 30%, rgba(212, 110, 21, 0.12) 0%, transparent 60%),
                linear-gradient(135deg, #FAF1E6 0%, #F5E5D3 50%, #EFE0CC 100%);
        }
        .font-mono-code { font-family: 'JetBrains Mono', monospace; }
    </style>
</head>
<body class="min-h-screen flex flex-col items-center justify-center p-4 text-[#3E2B21]">

    <div class="w-full max-w-md bg-white rounded-3xl shadow-2xl border border-[#EADBCE] p-8 text-center relative overflow-hidden">
        
        <!-- Abstract Glow -->
        <div class="absolute -right-16 -top-16 w-36 h-36 bg-[#D46E15]/15 rounded-full blur-2xl pointer-events-none"></div>

        <!-- Logo -->
        <div class="w-20 h-20 bg-[#FAF8F5] rounded-2xl p-2.5 flex items-center justify-center mx-auto mb-5 shadow-md border border-[#EADBCE]">
            <img src="assets/images/logo.png" alt="Berkat Dinasti Logo" class="w-full h-full object-contain" onerror="this.onerror=null; this.src='https://ui-avatars.com/api/?name=BD&background=3E2B21&color=FAF8F5';">
        </div>

        <h1 class="text-2xl font-bold text-[#3E2B21] mb-1.5 tracking-tight">Lacak Pesanan Roti</h1>
        <p class="text-xs text-[#786558] mb-6 leading-relaxed">
            Masukkan kode pelacakan nota (32 digit) yang Anda terima melalui WhatsApp untuk melihat status oven & pengiriman kurir.
        </p>

        <!-- Form Input Token -->
        <form onsubmit="handleTrack(event)" class="space-y-4 text-left">
            <div>
                <label for="tokenInput" class="block text-xs font-bold uppercase tracking-wider text-[#786558] mb-2">
                    Kode Pelacakan Token:
                </label>
                <div class="relative">
                    <span class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-[#A8988D]">
                        <i class="fas fa-key text-xs"></i>
                    </span>
                    <input type="text" id="tokenInput" placeholder="Contoh: a1b2c3d4e5f6..." required maxlength="64"
                        class="w-full pl-9 pr-3.5 py-3 bg-[#FAF8F5] border border-[#D8C7B8] focus:border-[#D46E15] focus:ring-2 focus:ring-[#D46E15]/20 rounded-xl text-xs font-mono-code text-[#3E2B21] transition outline-none">
                </div>
                <p id="errNotice" class="text-[11px] text-rose-600 mt-1.5 hidden flex items-center gap-1">
                    <i class="fas fa-exclamation-circle"></i> <span>Format token tidak valid.</span>
                </p>
            </div>

            <button type="submit" class="w-full py-3.5 px-4 bg-[#D46E15] hover:bg-[#B85C10] text-white font-bold rounded-xl transition shadow-md flex items-center justify-center gap-2 text-sm active:scale-[0.98]">
                <i class="fas fa-magnifying-glass"></i>
                <span>Lacak Status Sekarang</span>
            </button>
        </form>

        <div class="mt-6 pt-5 border-t border-[#F0E6DC] text-xs text-[#8E7E73]">
            <p>Butuh bantuan dengan pesanan Anda?</p>
            <a href="https://wa.me/6281234567890" target="_blank" class="inline-flex items-center gap-1.5 text-[#25D366] font-semibold hover:underline mt-1">
                <i class="fab fa-whatsapp"></i> Hubungi Customer Service Dinasti
            </a>
        </div>
    </div>

    <script>
        function handleTrack(e) {
            e.preventDefault();
            const token = document.getElementById('tokenInput').value.trim();
            const err = document.getElementById('errNotice');
            
            if (!/^[a-f0-9]{32,64}$/i.test(token)) {
                err.classList.remove('hidden');
                return;
            }
            err.classList.add('hidden');
            window.location.href = 'nota-digital.php?token=' + encodeURIComponent(token);
        }
    </script>
</body>
</html>
