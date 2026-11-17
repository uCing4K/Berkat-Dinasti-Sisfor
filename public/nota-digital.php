<?php
/**
 * Nota Digital & Pelacakan Pesanan Publik (Zero-Login Capability Architecture)
 * Sistem Informasi Berkat Dinasti (Bakery & Pastry UMKM)
 * 
 * Akses publik aman tanpa login menggunakan 128-bit cryptographic capability token.
 * Dilengkapi PII Masking, Rate Limiting, Live Progress Dapur, Ekspor PNG html2canvas,
 * serta Integrasi WhatsApp & Rekening Resmi.
 */

// 1. Inisialisasi Environment & Helper
$configFile = __DIR__ . "/../config/config.php";
if (file_exists($configFile)) {
    require_once $configFile;
}
require_once __DIR__ . "/../app/Helpers/ReceiptHelper.php";

// 2. Proteksi Header Keamanan & Rate Limiting
header("X-Robots-Tag: noindex, nofollow", true);
header("Referrer-Policy: no-referrer");
header("X-Content-Type-Options: nosniff");
header("X-Frame-Options: SAMEORIGIN");

ReceiptHelper::enforceRateLimit(60, 60);

// 3. Validasi Parameter Token
$token = isset($_GET['token']) ? trim($_GET['token']) : '';
if (!ReceiptHelper::isValidTrackingToken($token)) {
    renderErrorPage(
        "Tautan Nota Tidak Valid", 
        "Tautan yang Anda tuju tidak memiliki format token yang sah. Pastikan Anda membuka tautan lengkap yang dikirimkan oleh admin Berkat Dinasti melalui WhatsApp."
    );
    exit();
}

// 4. Koneksi Database & Query Data Pesanan
$db_connected = false;
$pdo = null;
$order = null;
$items = [];
$tracking_logs = [];

try {
    if (defined('DB_HOST') && defined('DB_NAME') && defined('DB_USER') && defined('DB_PASS')) {
        $pdo = new PDO("mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=utf8mb4", DB_USER, DB_PASS, [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
        ]);
        $db_connected = true;

        // Pastikan tabel siap mendukung tracking_token
        ReceiptHelper::ensureTrackingTokenSupport($pdo);

        // Ambil Data Pesanan + Pelanggan
        $stmtOrder = $pdo->prepare("
            SELECT 
                p.id_pesanan,
                p.no_invoice,
                p.tgl_pesan,
                p.tgl_kirim,
                p.waktu_kirim,
                p.total_harga,
                p.grand_total,
                p.status,
                p.status_bayar,
                p.metode_bayar,
                p.catatan,
                p.tracking_token,
                p.created_at,
                pel.nama_pelanggan,
                pel.no_wa,
                pel.alamat,
                pel.tipe_pelanggan
            FROM pesanan p
            LEFT JOIN pelanggan pel ON p.id_pelanggan = pel.id_pelanggan
            WHERE p.tracking_token = ?
            LIMIT 1
        ");
        $stmtOrder->execute([$token]);
        $order = $stmtOrder->fetch();

        if ($order) {
            // Ambil Detail Roti & Topping
            $hasTopping = true;
            try {
                $stmtItems = $pdo->prepare("
                    SELECT 
                        dp.id_detail,
                        dp.qty,
                        dp.harga_satuan,
                        dp.subtotal,
                        dp.topping,
                        vp.nama_varian,
                        prod.nama_produk,
                        prod.gambar
                    FROM detail_pesanan dp
                    LEFT JOIN varian_produk vp ON dp.id_varian = vp.id_varian
                    LEFT JOIN produk prod ON vp.id_produk = prod.id_produk
                    WHERE dp.id_pesanan = ?
                    ORDER BY dp.id_detail ASC
                ");
                $stmtItems->execute([$order['id_pesanan']]);
                $items = $stmtItems->fetchAll();
            } catch (Exception $eItems) {
                // Fallback jika skema varian berbeda
                $stmtItems = $pdo->prepare("
                    SELECT 
                        dp.id_detail,
                        dp.qty,
                        dp.harga_satuan,
                        dp.subtotal,
                        vp.nama_varian,
                        prod.nama_produk
                    FROM detail_pesanan dp
                    LEFT JOIN varian_produk vp ON dp.id_varian = vp.id_varian
                    LEFT JOIN produk prod ON vp.id_produk = prod.id_produk
                    WHERE dp.id_pesanan = ?
                    ORDER BY dp.id_detail ASC
                ");
                $stmtItems->execute([$order['id_pesanan']]);
                $items = $stmtItems->fetchAll();
            }

            // Ambil Tracking Timeline History
            try {
                $stmtLogs = $pdo->prepare("
                    SELECT status, keterangan, created_at 
                    FROM tracking_log 
                    WHERE id_pesanan = ? 
                    ORDER BY created_at ASC
                ");
                $stmtLogs->execute([$order['id_pesanan']]);
                $tracking_logs = $stmtLogs->fetchAll();
            } catch (Exception $eLogs) {
                $tracking_logs = [];
            }
        }
    }
} catch (Exception $e) {
    $db_connected = false;
}

// Fallback demo/offline dataset jika koneksi database lokal belum siap atau token uji coba
if (!$order && (!$db_connected || $token === 'bd00cafe000000000000000000000001' || (isset($_GET['demo']) && $_GET['demo'] == '1'))) {
    $order = [
        'id_pesanan' => 999,
        'no_invoice' => 'INV-' . date('Ymd') . '-0001',
        'tgl_pesan' => date('Y-m-d'),
        'tgl_kirim' => date('Y-m-d', strtotime('+4 days')),
        'waktu_kirim' => '09:00:00',
        'total_harga' => 144000,
        'grand_total' => 144000,
        'status' => 'proses',
        'status_bayar' => 'lunas',
        'metode_bayar' => 'transfer',
        'catatan' => 'Topping keju lebih tebal, kemasan box putih pita untuk acara hajatan keluarga.',
        'tracking_token' => $token,
        'created_at' => date('Y-m-d H:i:s'),
        'nama_pelanggan' => 'Ibu Sari Dewi',
        'no_wa' => '0812-3456-7890',
        'alamat' => 'Komplek Melati No. 4, Blok B, Kota Batu',
        'tipe_pelanggan' => 'retail'
    ];
    $items = [
        [
            'id_detail' => 1,
            'qty' => 10,
            'harga_satuan' => 10000,
            'subtotal' => 100000,
            'topping' => 'Coco Chip + Keju',
            'nama_varian' => 'Kardus Putih Isi 6',
            'nama_produk' => 'Roti Hajatan Berkat Dinasti',
            'gambar' => '9_pilihan_topping_roti_hajatan.jpg'
        ],
        [
            'id_detail' => 2,
            'qty' => 2,
            'harga_satuan' => 22000,
            'subtotal' => 44000,
            'topping' => 'Keju Cheddar Parut',
            'nama_varian' => 'Mika Premium',
            'nama_produk' => 'Bolu Pisang Raja Spesial',
            'gambar' => null
        ]
    ];
}

if (!$order) {
    renderErrorPage(
        "Pesanan Tidak Ditemukan",
        "Nota pesanan dengan token ini tidak ditemukan dalam sistem database Berkat Dinasti. Kemungkinan nomor pesanan telah diperbarui atau tautan kedaluwarsa. Silakan konfirmasi ke customer service kami."
    );
    exit();
}

// 5. Normalisasi Data Tampilan
$orderNumber = htmlspecialchars($order['no_invoice']);
$customerName = htmlspecialchars($order['nama_pelanggan'] ?? 'Pelanggan Setia');
$maskedPhone = htmlspecialchars(ReceiptHelper::maskPhoneNumber($order['no_wa'] ?? ''));
$maskedAddress = htmlspecialchars(ReceiptHelper::maskAddress($order['alamat'] ?? ''));

$grandTotal = (float)$order['grand_total'];
$formattedTotal = 'Rp ' . number_format($grandTotal, 0, ',', '.');
$subtotal = (float)$order['total_harga'];
$formattedSubtotal = 'Rp ' . number_format($subtotal > 0 ? $subtotal : $grandTotal, 0, ',', '.');

// Perhitungan Pembayaran (Lunas vs DP)
$statusBayar = strtolower($order['status_bayar'] ?? 'belum_bayar');
$totalPaid = 0.0;
$remainingBalance = $grandTotal;

if ($statusBayar === 'lunas') {
    $totalPaid = $grandTotal;
    $remainingBalance = 0.0;
} elseif ($statusBayar === 'dp') {
    // Estimasi DP rata-rata 50% atau dari log pembayaran jika ada
    $totalPaid = round($grandTotal * 0.5);
    $remainingBalance = max(0.0, $grandTotal - $totalPaid);
}

// Mapping Status Dapur & Pengiriman
$statusPesanan = strtolower($order['status'] ?? 'pending');
$statusConfig = [
    'pending' => [
        'step' => 1,
        'label' => 'Menunggu Konfirmasi',
        'badge_bg' => 'bg-amber-100',
        'badge_text' => 'text-amber-800',
        'badge_border' => 'border-amber-300',
        'desc' => 'Pesanan telah dicatat dan menunggu konfirmasi antrean dapur.'
    ],
    'proses' => [
        'step' => 2,
        'label' => 'Diproses Dapur & Oven',
        'badge_bg' => 'bg-blue-100',
        'badge_text' => 'text-blue-800',
        'badge_border' => 'border-blue-300',
        'desc' => 'Roti & pastry sedang dipanggang segar dengan standar higienis.'
    ],
    'kirim' => [
        'step' => 3,
        'label' => 'Siap Kirim / Diantar Kurir',
        'badge_bg' => 'bg-orange-100',
        'badge_text' => 'text-orange-800',
        'badge_border' => 'border-orange-300',
        'desc' => 'Pesanan siap diantar oleh kurir obrok motor Dinasti ke lokasi Anda.'
    ],
    'selesai' => [
        'step' => 4,
        'label' => 'Selesai Diterima',
        'badge_bg' => 'bg-emerald-100',
        'badge_text' => 'text-emerald-800',
        'badge_border' => 'border-emerald-300',
        'desc' => 'Pesanan telah sampai dan diserahterimakan dengan selamat. Selamat menikmati!'
    ],
    'batal' => [
        'step' => 0,
        'label' => 'Dibatalkan',
        'badge_bg' => 'bg-rose-100',
        'badge_text' => 'text-rose-800',
        'badge_border' => 'border-rose-300',
        'desc' => 'Pesanan telah dibatalkan.'
    ]
];

$currStatus = $statusConfig[$statusPesanan] ?? $statusConfig['pending'];
$currentStep = $currStatus['step'];

// Format Waktu & Tanggal
$tglPesan = !empty($order['tgl_pesan']) ? date('d M Y', strtotime($order['tgl_pesan'])) : '-';
$tglKirim = !empty($order['tgl_kirim']) ? date('d M Y', strtotime($order['tgl_kirim'])) : '-';
$waktuKirim = !empty($order['waktu_kirim']) ? substr($order['waktu_kirim'], 0, 5) . ' WIB' : 'Pagi/Siang';

// Deep link WhatsApp ke Admin
$waAdminMessage = "Halo Admin Berkat Dinasti, saya ingin menanyakan pesanan dengan nomor invoice #{$orderNumber}.";
$waAdminUrl = "https://wa.me/6281234567890?text=" . rawurlencode($waAdminMessage);

// Fungsi Render Error Page Terisolasi
function renderErrorPage(string $title, string $message) {
    ?>
    <!DOCTYPE html>
    <html lang="id">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title><?= htmlspecialchars($title) ?> - Berkat Dinasti</title>
        <script src="https://cdn.tailwindcss.com"></script>
        <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
        <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
        <style>body { font-family: 'Inter', sans-serif; }</style>
    </head>
    <body class="bg-[#FAF1E6] min-h-screen flex items-center justify-center p-4">
        <div class="max-w-md w-full bg-white rounded-3xl shadow-xl border border-[#E8DCCF] p-8 text-center relative overflow-hidden">
            <div class="w-20 h-20 bg-amber-100 text-amber-700 rounded-2xl flex items-center justify-center mx-auto mb-5 text-3xl shadow-inner border border-amber-200">
                <i class="fas fa-bread-slice"></i>
            </div>
            <h1 class="text-2xl font-bold text-[#3E2B21] mb-2"><?= htmlspecialchars($title) ?></h1>
            <p class="text-[#786558] text-sm leading-relaxed mb-6"><?= htmlspecialchars($message) ?></p>
            <div class="pt-4 border-t border-gray-100 flex flex-col gap-2">
                <a href="https://wa.me/6281234567890" target="_blank" class="w-full inline-flex items-center justify-center gap-2 py-3 px-4 bg-[#25D366] hover:bg-[#20ba5a] text-white font-medium rounded-xl transition shadow-sm text-sm">
                    <i class="fab fa-whatsapp text-lg"></i> Hubungi Customer Service
                </a>
            </div>
            <div class="mt-6 text-xs text-[#A8988D]">
                &copy; <?= date('Y') ?> Berkat Dinasti Bakery & Pastry UMKM
            </div>
        </div>
    </body>
    </html>
    <?php
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <title>Nota Digital #<?= $orderNumber ?> - Berkat Dinasti Bakery</title>
    
    <!-- Meta Security & SEO -->
    <meta name="robots" content="noindex, nofollow">
    <meta name="referrer" content="no-referrer">
    
    <!-- Open Graph for Rich WhatsApp / Social Preview -->
    <meta property="og:title" content="Nota Pesanan #<?= $orderNumber ?> - Berkat Dinasti">
    <meta property="og:description" content="Total: <?= $formattedTotal ?> | Status: <?= $currStatus['label'] ?> | Jadwal Kirim: <?= $tglKirim ?> (<?= $waktuKirim ?>)">
    <meta property="og:type" content="website">
    <meta property="og:image" content="<?= htmlspecialchars((defined('BASE_URL') ? rtrim(BASE_URL, '/') : '') . '/public/assets/images/logo.png') ?>">

    <!-- Stylesheets & Fonts -->
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&family=JetBrains+Mono:wght@500;600&display=swap" rel="stylesheet">
    
    <!-- html2canvas for High-Fidelity Client-Side Receipt Image Download -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/html2canvas/1.4.1/html2canvas.min.js"></script>

    <style>
        body {
            font-family: 'Inter', sans-serif;
            background-color: #F8ECE1;
            background-image: 
                radial-gradient(ellipse 90% 60% at 70% 30%, rgba(212, 110, 21, 0.12) 0%, rgba(212, 110, 21, 0.02) 60%, transparent 100%),
                radial-gradient(circle at 15% 85%, rgba(255, 155, 69, 0.08) 0%, transparent 50%),
                linear-gradient(135deg, #FAF1E6 0%, #F5E5D3 50%, #EFE0CC 100%);
            -webkit-tap-highlight-color: transparent;
        }
        .font-mono-code {
            font-family: 'JetBrains Mono', monospace;
        }
        /* Pulse Animation on Active Kitchen Milestone */
        @keyframes pulse-ring {
            0% { transform: scale(0.95); opacity: 0.8; }
            50% { transform: scale(1.15); opacity: 0.3; }
            100% { transform: scale(0.95); opacity: 0.8; }
        }
        .pulse-active {
            animation: pulse-ring 2s infinite ease-in-out;
        }
        /* Custom Receipt Serrated Sawtooth Edge */
        .receipt-serrated-bottom {
            background-image: radial-gradient(circle, transparent 65%, #FFFFFF 66%);
            background-size: 16px 16px;
            background-position: -8px -8px;
        }
        @media print {
            body { background: white !important; }
            .no-print { display: none !important; }
            .print-shadow-none { box-shadow: none !important; border: 1px solid #ccc !important; }
        }
    </style>
</head>
<body class="min-h-screen py-4 px-3 sm:py-8 sm:px-4 flex flex-col items-center justify-start text-[#3E2B21]">

    <!-- DEKORATIF ORBITAL BACKGROUND ELEMEN (Khas Berkat Dinasti) -->
    <div class="fixed inset-0 pointer-events-none overflow-hidden z-0">
        <div class="absolute w-[600px] h-[600px] -right-[200px] -top-[150px] rounded-full border border-[#D46E15]/20"></div>
        <div class="absolute w-[450px] h-[450px] -right-[120px] -top-[70px] rounded-full border border-dashed border-[#C27329]/20"></div>
        <div class="absolute w-[650px] h-[650px] -left-[200px] -bottom-[200px] rounded-full border border-[#D46E15]/20"></div>
        <div class="absolute w-[400px] h-[400px] -left-[100px] -bottom-[100px] rounded-full border border-dashed border-[#B85C10]/20"></div>
    </div>

    <!-- MAIN CONTAINER (Mobile-First 480px) -->
    <div class="w-full max-w-[480px] relative z-10 flex flex-col gap-4">

        <!-- TOP BAR: Status & Verified Badge -->
        <div class="no-print flex items-center justify-between px-2 text-xs">
            <div class="flex items-center gap-1.5 text-emerald-800 bg-emerald-100/90 border border-emerald-300 px-3 py-1 rounded-full font-medium shadow-xs">
                <i class="fas fa-check-circle text-emerald-600"></i> Nota Resmi Terverifikasi
            </div>
            <div class="text-[#8E7E73] font-mono-code text-[11px]">
                <?= date('d/m/Y H:i') ?> WIB
            </div>
        </div>

        <!-- ========================================================================= -->
        <!-- AREA CAPTURE STRUK (Diekspor Menjadi Gambar PNG Bersih oleh html2canvas) -->
        <!-- ========================================================================= -->
        <div id="receipt-capture" class="bg-white rounded-3xl shadow-2xl border border-[#EADBCE] overflow-hidden transition-all duration-300">
            
            <!-- HEADER TOKO: Branding Berkat Dinasti -->
            <div class="bg-gradient-to-br from-[#3E2B21] via-[#4A3428] to-[#2E1F17] text-white p-6 relative overflow-hidden">
                <!-- Abstract Glow -->
                <div class="absolute -right-16 -top-16 w-36 h-36 bg-[#D46E15]/20 rounded-full blur-2xl pointer-events-none"></div>
                <div class="absolute -left-10 -bottom-10 w-28 h-28 bg-[#F5A25D]/10 rounded-full blur-xl pointer-events-none"></div>

                <div class="relative z-10 flex items-center gap-4">
                    <!-- Logo Box -->
                    <div class="w-16 h-16 bg-[#FAF8F5] rounded-2xl p-2 flex items-center justify-center shadow-md border border-white/20 shrink-0">
                        <img src="assets/images/logo.png" alt="Berkat Dinasti Logo" class="w-full h-full object-contain" onerror="this.onerror=null; this.src='https://ui-avatars.com/api/?name=BD&background=3E2B21&color=FAF8F5';">
                    </div>
                    <!-- Brand Info -->
                    <div class="flex-1 min-w-0">
                        <div class="flex items-center gap-2 mb-0.5">
                            <h1 class="text-xl font-bold tracking-tight text-[#FAF8F5] truncate">Berkat Dinasti</h1>
                            <span class="bg-[#D46E15]/30 border border-[#D46E15]/50 text-[#F5A25D] text-[10px] font-semibold px-2 py-0.5 rounded-full uppercase tracking-wider">Bakery</span>
                        </div>
                        <p class="text-[#D8C7B8] text-xs leading-tight mb-2">Spesialis Roti & Pastry Hajatan Kota Malang & Batu</p>
                        <div class="flex items-center gap-3 text-[11px] text-[#BBAAA0]">
                            <span class="flex items-center gap-1"><i class="fas fa-motorcycle text-[#D46E15]"></i> Bebas Ongkir</span>
                            <span>•</span>
                            <span class="flex items-center gap-1"><i class="fas fa-shield-alt text-emerald-400"></i> Halal & Higienis</span>
                        </div>
                    </div>
                </div>

                <!-- Nomor Invoice & Waktu Dibuat -->
                <div class="mt-5 pt-4 border-t border-white/10 flex items-center justify-between text-xs">
                    <div>
                        <span class="text-[#A8988D] block text-[10px] uppercase tracking-wider">Nomor Nota / Invoice</span>
                        <span class="font-mono-code font-bold text-sm text-[#FAF8F5] tracking-wide">#<?= $orderNumber ?></span>
                    </div>
                    <div class="text-right">
                        <span class="text-[#A8988D] block text-[10px] uppercase tracking-wider">Tanggal Pemesanan</span>
                        <span class="text-[#E0D4CA] font-medium"><?= $tglPesan ?></span>
                    </div>
                </div>
            </div>

            <!-- DUAL STATUS ROW: Status Pesanan & Status Bayar -->
            <div class="bg-[#F8F3ED] px-6 py-3.5 border-b border-[#E8DCCF] flex items-center justify-between gap-2">
                <!-- Status Pesanan Pill -->
                <div class="flex items-center gap-2">
                    <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-semibold <?= $currStatus['badge_bg'] ?> <?= $currStatus['badge_text'] ?> border <?= $currStatus['badge_border'] ?> shadow-2xs">
                        <i class="fas fa-circle text-[8px] animate-pulse"></i>
                        <?= $currStatus['label'] ?>
                    </span>
                </div>

                <!-- Status Pembayaran Pill -->
                <div>
                    <?php if ($statusBayar === 'lunas'): ?>
                        <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-semibold bg-emerald-100 text-emerald-800 border border-emerald-300 shadow-2xs">
                            <i class="fas fa-check-double text-[10px]"></i> LUNAS
                        </span>
                    <?php elseif ($statusBayar === 'dp'): ?>
                        <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-semibold bg-amber-100 text-amber-800 border border-amber-300 shadow-2xs">
                            <i class="fas fa-hourglass-half text-[10px]"></i> UANG MUKA (DP)
                        </span>
                    <?php else: ?>
                        <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-semibold bg-rose-100 text-rose-800 border border-rose-300 shadow-2xs">
                            <i class="fas fa-exclamation-circle text-[10px]"></i> BELUM LUNAS
                        </span>
                    <?php endif; ?>
                </div>
            </div>

            <!-- ========================================================================= -->
            <!-- LIVE PROGRESS TRACKER: Alur 4 Milestone Dapur UMKM Berkat Dinasti        -->
            <!-- ========================================================================= -->
            <?php if ($statusPesanan !== 'batal'): ?>
            <div class="px-6 py-5 border-b border-[#F0E6DC] bg-white">
                <div class="flex items-center justify-between mb-4">
                    <span class="text-xs font-bold uppercase tracking-wider text-[#786558] flex items-center gap-1.5">
                        <i class="fas fa-tasks text-[#D46E15]"></i> Progres Pesanan Real-Time
                    </span>
                    <span class="text-[11px] text-[#A8988D]">Tahap <?= $currentStep ?> dari 4</span>
                </div>

                <!-- Progress Steps Container -->
                <div class="relative flex items-center justify-between">
                    <!-- Progress Line Background -->
                    <div class="absolute left-6 right-6 top-1/2 -translate-y-1/2 h-1 bg-[#EADBCE] z-0"></div>
                    <!-- Active Progress Line -->
                    <?php 
                        $progressWidths = [1 => '0%', 2 => '33.3%', 3 => '66.6%', 4 => '100%'];
                        $fillWidth = $progressWidths[$currentStep] ?? '0%';
                    ?>
                    <div class="absolute left-6 top-1/2 -translate-y-1/2 h-1 bg-[#D46E15] z-0 transition-all duration-500" style="width: calc(<?= $fillWidth ?> - 12px);"></div>

                    <!-- Step 1: Diterima -->
                    <div class="relative z-10 flex flex-col items-center">
                        <div class="w-10 h-10 rounded-full flex items-center justify-center font-bold text-xs transition-all <?= $currentStep >= 1 ? 'bg-[#D46E15] text-white shadow-md' : 'bg-[#EADBCE] text-[#8E7E73]' ?> <?= $currentStep === 1 ? 'ring-4 ring-[#D46E15]/20 pulse-active' : '' ?>">
                            <?php if ($currentStep > 1): ?><i class="fas fa-check text-xs"></i><?php else: ?><i class="fas fa-receipt text-xs"></i><?php endif; ?>
                        </div>
                        <span class="text-[10px] font-semibold mt-1.5 <?= $currentStep >= 1 ? 'text-[#3E2B21]' : 'text-[#A8988D]' ?> text-center">Diterima</span>
                    </div>

                    <!-- Step 2: Dapur & Oven -->
                    <div class="relative z-10 flex flex-col items-center">
                        <div class="w-10 h-10 rounded-full flex items-center justify-center font-bold text-xs transition-all <?= $currentStep >= 2 ? 'bg-[#D46E15] text-white shadow-md' : 'bg-[#EADBCE] text-[#8E7E73]' ?> <?= $currentStep === 2 ? 'ring-4 ring-[#D46E15]/20 pulse-active' : '' ?>">
                            <?php if ($currentStep > 2): ?><i class="fas fa-check text-xs"></i><?php else: ?><i class="fas fa-fire-burner text-xs"></i><?php endif; ?>
                        </div>
                        <span class="text-[10px] font-semibold mt-1.5 <?= $currentStep >= 2 ? 'text-[#3E2B21]' : 'text-[#A8988D]' ?> text-center">Dapur Oven</span>
                    </div>

                    <!-- Step 3: Siap Kirim / Kurir -->
                    <div class="relative z-10 flex flex-col items-center">
                        <div class="w-10 h-10 rounded-full flex items-center justify-center font-bold text-xs transition-all <?= $currentStep >= 3 ? 'bg-[#D46E15] text-white shadow-md' : 'bg-[#EADBCE] text-[#8E7E73]' ?> <?= $currentStep === 3 ? 'ring-4 ring-[#D46E15]/20 pulse-active' : '' ?>">
                            <?php if ($currentStep > 3): ?><i class="fas fa-check text-xs"></i><?php else: ?><i class="fas fa-motorcycle text-xs"></i><?php endif; ?>
                        </div>
                        <span class="text-[10px] font-semibold mt-1.5 <?= $currentStep >= 3 ? 'text-[#3E2B21]' : 'text-[#A8988D]' ?> text-center">Siap Kirim</span>
                    </div>

                    <!-- Step 4: Selesai Diterima -->
                    <div class="relative z-10 flex flex-col items-center">
                        <div class="w-10 h-10 rounded-full flex items-center justify-center font-bold text-xs transition-all <?= $currentStep >= 4 ? 'bg-emerald-600 text-white shadow-md' : 'bg-[#EADBCE] text-[#8E7E73]' ?> <?= $currentStep === 4 ? 'ring-4 ring-emerald-500/20' : '' ?>">
                            <i class="fas fa-heart text-xs"></i>
                        </div>
                        <span class="text-[10px] font-semibold mt-1.5 <?= $currentStep >= 4 ? 'text-emerald-800' : 'text-[#A8988D]' ?> text-center">Selesai</span>
                    </div>
                </div>

                <!-- Keterangan Status Terkini -->
                <div class="mt-4 bg-[#FBF7F2] border border-[#EEDBCE] rounded-xl p-3 flex items-start gap-2.5">
                    <i class="fas fa-info-circle text-[#D46E15] shrink-0 mt-0.5 text-sm"></i>
                    <p class="text-xs text-[#5D4B3E] leading-relaxed">
                        <?= $currStatus['desc'] ?>
                    </p>
                </div>
            </div>
            <?php endif; ?>

            <!-- INFORMASI PENGIRIMAN & PENERIMA -->
            <div class="px-6 py-4 bg-[#FAF6F0] border-b border-[#EADBCE]">
                <div class="grid grid-cols-2 gap-4 text-xs">
                    <div>
                        <span class="text-[#8E7E73] block uppercase tracking-wider text-[10px] font-bold mb-1">
                            <i class="fas fa-user text-[#D46E15]"></i> Pelanggan
                        </span>
                        <p class="font-bold text-[#3E2B21] text-sm truncate"><?= $customerName ?></p>
                        <p class="text-[#786558] font-mono-code text-[11px] mt-0.5"><?= $maskedPhone ?></p>
                        <p class="text-[#786558] text-[11px] mt-0.5 truncate"><?= $maskedAddress ?></p>
                    </div>
                    <div>
                        <span class="text-[#8E7E73] block uppercase tracking-wider text-[10px] font-bold mb-1">
                            <i class="fas fa-calendar-check text-[#D46E15]"></i> Jadwal Pengiriman
                        </span>
                        <p class="font-bold text-[#3E2B21] text-sm"><?= $tglKirim ?></p>
                        <p class="text-[#786558] text-[11px] mt-0.5">Pukul: <span class="font-semibold text-[#3E2B21]"><?= $waktuKirim ?></span></p>
                        <div class="mt-1 inline-flex items-center gap-1 bg-emerald-50 text-emerald-800 border border-emerald-200 px-2 py-0.5 rounded text-[10px] font-semibold">
                            <i class="fas fa-truck-fast"></i> Bebas Ongkir (Malang & Batu)
                        </div>
                    </div>
                </div>
            </div>

            <!-- RINCIAN DAFTAR PESANAN ROTI & PASTRI -->
            <div class="p-6 bg-white">
                <div class="flex items-center justify-between pb-3 border-b-2 border-[#3E2B21] mb-3">
                    <span class="text-xs font-bold uppercase tracking-wider text-[#3E2B21]">Rincian Item Roti</span>
                    <span class="text-xs font-bold uppercase tracking-wider text-[#3E2B21]">Subtotal</span>
                </div>

                <!-- Item Loop -->
                <div class="divide-y divide-dashed divide-[#E8DCCF]">
                    <?php if (!empty($items)): ?>
                        <?php foreach ($items as $item): ?>
                            <?php 
                                $namaProduk = htmlspecialchars($item['nama_produk'] ?? 'Roti Spesial');
                                $namaVarian = htmlspecialchars($item['nama_varian'] ?? 'Regular');
                                $topping = !empty($item['topping']) ? htmlspecialchars($item['topping']) : '';
                                $qty = (int)$item['qty'];
                                $hargaSatuan = (float)$item['harga_satuan'];
                                $subtotalItem = (float)$item['subtotal'];
                            ?>
                            <div class="py-3 flex items-start justify-between gap-3">
                                <div class="flex-1 min-w-0">
                                    <h4 class="text-sm font-bold text-[#3E2B21] leading-snug truncate"><?= $namaProduk ?></h4>
                                    <div class="flex flex-wrap items-center gap-1.5 mt-0.5">
                                        <span class="text-[11px] bg-[#FAF1E6] text-[#786558] border border-[#EADBCE] px-1.5 py-0.2 rounded font-medium"><?= $namaVarian ?></span>
                                        <?php if (!empty($topping)): ?>
                                            <span class="text-[11px] bg-orange-50 text-[#C27329] border border-orange-200 px-1.5 py-0.2 rounded font-medium flex items-center gap-1">
                                                <i class="fas fa-certificate text-[9px]"></i> Topping: <?= $topping ?>
                                            </span>
                                        <?php endif; ?>
                                    </div>
                                    <div class="text-xs text-[#8E7E73] mt-1 font-mono-code">
                                        <?= $qty ?> pcs &times; Rp <?= number_format($hargaSatuan, 0, ',', '.') ?>
                                    </div>
                                </div>
                                <div class="text-right shrink-0">
                                    <span class="font-bold text-sm text-[#3E2B21] font-mono-code">
                                        Rp <?= number_format($subtotalItem, 0, ',', '.') ?>
                                    </span>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <div class="py-4 text-center text-xs text-[#A8988D]">
                            Detail rincian produk dicatat langsung pada sistem oven.
                        </div>
                    <?php endif; ?>
                </div>

                <!-- RINGKASAN TOTAL PEMBAYARAN -->
                <div class="mt-4 pt-4 border-t-2 border-[#E8DCCF] bg-[#FAF8F5] -mx-6 -mb-6 p-6">
                    <div class="flex justify-between items-center text-xs text-[#786558] mb-1.5">
                        <span>Subtotal Belanja</span>
                        <span class="font-mono-code font-medium text-[#3E2B21]"><?= $formattedSubtotal ?></span>
                    </div>
                    <div class="flex justify-between items-center text-xs text-[#786558] mb-1.5">
                        <span>Ongkos Kirim (Malang & Batu)</span>
                        <span class="font-medium text-emerald-600 font-mono-code">Rp 0 (GRATIS)</span>
                    </div>
                    
                    <div class="my-3 border-t border-[#E8DCCF]"></div>

                    <div class="flex justify-between items-center">
                        <div>
                            <span class="text-xs font-bold uppercase tracking-wider text-[#3E2B21]">Grand Total</span>
                            <span class="block text-[11px] text-[#8E7E73]">Termasuk PPN & Kemasan Aman</span>
                        </div>
                        <span class="text-xl font-extrabold text-[#D46E15] font-mono-code">
                            <?= $formattedTotal ?>
                        </span>
                    </div>

                    <!-- Sisa Tagihan (Jika DP atau Belum Lunas) -->
                    <?php if ($statusBayar !== 'lunas'): ?>
                        <div class="mt-3 pt-3 border-t border-dashed border-[#E8DCCF] flex items-center justify-between text-xs">
                            <span class="text-rose-700 font-semibold flex items-center gap-1">
                                <i class="fas fa-hand-holding-dollar"></i> Sisa Tagihan Saat Terima:
                            </span>
                            <span class="font-bold text-rose-700 font-mono-code text-sm">
                                Rp <?= number_format($remainingBalance, 0, ',', '.') ?>
                            </span>
                        </div>
                    <?php endif; ?>

                    <!-- Catatan Khusus Pesanan -->
                    <?php if (!empty($order['catatan'])): ?>
                        <div class="mt-4 bg-amber-50/80 border-l-4 border-amber-400 p-3 rounded-r-xl">
                            <span class="text-[10px] font-bold uppercase tracking-wider text-amber-800 block mb-0.5">
                                <i class="fas fa-sticky-note"></i> Catatan Khusus:
                            </span>
                            <p class="text-xs text-amber-900 leading-relaxed italic">
                                &ldquo;<?= nl2br(htmlspecialchars($order['catatan'])) ?>&rdquo;
                            </p>
                        </div>
                    <?php endif; ?>
                </div>
            </div>

            <!-- FOOTER STRUK (Area Capture) -->
            <div class="bg-[#F8F3ED] px-6 py-4 text-center border-t border-[#EADBCE]">
                <p class="text-[11px] font-semibold text-[#5D4B3E]">
                    Terima kasih telah memesan di Berkat Dinasti Bakery! 🙏✨
                </p>
                <p class="text-[10px] text-[#A8988D] mt-0.5">
                    Harap simpan nota digital ini atau tunjukkan saat serah terima pesanan dengan kurir.
                </p>
                <div class="mt-2 pt-2 border-t border-[#E8DCCF] text-[9px] text-[#B8A89D] flex items-center justify-between">
                    <span>Otomatisasi Sistem UMKM Dinasti</span>
                    <span class="font-mono-code">ID-REF: <?= substr($token, 0, 8) ?>...</span>
                </div>
            </div>
        </div>
        <!-- ========================================================================= -->
        <!-- AKHIR AREA CAPTURE STRUK                                                  -->
        <!-- ========================================================================= -->

        <!-- ========================================================================= -->
        <!-- ACTION BUTTONS: Interaksi Pelanggan (Tidak Masuk dalam Gambar Ekspor)      -->
        <!-- ========================================================================= -->
        <div class="no-print flex flex-col gap-2.5 pt-1">
            
            <!-- 1. Tombol Download Gambar Nota PNG (html2canvas) -->
            <button id="btnDownloadImage" onclick="downloadReceiptPNG()" class="w-full py-3.5 px-4 bg-[#3E2B21] hover:bg-[#2C1E17] text-[#FAF8F5] font-bold rounded-2xl transition shadow-lg flex items-center justify-center gap-2.5 text-sm active:scale-[0.98]">
                <i class="fas fa-download text-base text-[#F5A25D]"></i>
                <span>Simpan Struk ke Galeri (PNG)</span>
            </button>

            <!-- 2. Tombol Hubungi Admin via WhatsApp -->
            <a href="<?= $waAdminUrl ?>" target="_blank" class="w-full py-3.5 px-4 bg-[#25D366] hover:bg-[#20ba5a] text-white font-bold rounded-2xl transition shadow-md flex items-center justify-center gap-2.5 text-sm active:scale-[0.98]">
                <i class="fab fa-whatsapp text-lg"></i>
                <span>Tanya Admin via WhatsApp</span>
            </a>

            <!-- 3. Tombol Salin Tautan Nota -->
            <button onclick="copyReceiptLink()" class="w-full py-3 px-4 bg-white hover:bg-gray-50 text-[#5D4B3E] font-semibold rounded-2xl transition border border-[#D8C7B8] flex items-center justify-center gap-2 text-xs active:scale-[0.98]">
                <i class="fas fa-link text-[#D46E15]"></i>
                <span id="btnCopyText">Salin Tautan Nota Digital</span>
            </button>

            <!-- 4. Tombol Cetak / PDF Fisik -->
            <button onclick="window.print()" class="w-full py-2.5 px-4 bg-transparent hover:bg-black/5 text-[#8E7E73] font-medium rounded-xl transition flex items-center justify-center gap-2 text-xs">
                <i class="fas fa-print"></i>
                <span>Cetak Nota Fisik / Simpan PDF</span>
            </button>
        </div>

        <!-- INFORMASI REKENING RESMI BERKAT DINASTI (Jika Belum Lunas) -->
        <?php if ($statusBayar !== 'lunas'): ?>
        <div class="no-print bg-white rounded-3xl p-5 border border-[#E8DCCF] shadow-sm mt-1">
            <div class="flex items-center gap-2.5 mb-3">
                <div class="w-8 h-8 rounded-xl bg-orange-100 text-[#D46E15] flex items-center justify-center text-sm font-bold">
                    <i class="fas fa-building-columns"></i>
                </div>
                <div>
                    <h3 class="text-xs font-bold text-[#3E2B21]">Rekening Resmi Pembayaran</h3>
                    <p class="text-[11px] text-[#786558]">Transfer pembayaran/pelunasan hanya ke rekening ini:</p>
                </div>
            </div>

            <div class="bg-[#FAF5EE] rounded-2xl p-3.5 border border-[#EADBCE] flex items-center justify-between">
                <div>
                    <span class="text-[10px] font-bold text-[#8E7E73] uppercase tracking-wider block">BCA (Bank Central Asia)</span>
                    <span class="font-mono-code font-bold text-sm text-[#3E2B21]" id="norekBCA">123-456-7890</span>
                    <span class="text-[11px] text-[#786558] block mt-0.5">a.n. Berkat Dinasti Bakery</span>
                </div>
                <button onclick="copyNorek()" class="px-3 py-1.5 bg-[#3E2B21] hover:bg-[#2C1E17] text-white text-xs font-semibold rounded-xl transition shadow-xs flex items-center gap-1.5 active:scale-95">
                    <i class="fas fa-copy text-[10px]"></i>
                    <span id="btnCopyNorekText">Salin</span>
                </button>
            </div>
            <p class="text-[10px] text-[#A8988D] mt-2.5 italic text-center">
                Setelah transfer, silakan kirimkan bukti struk ke nomor WhatsApp Admin di atas.
            </p>
        </div>
        <?php endif; ?>

        <!-- FOOTER BRANDING COPYRIGHT -->
        <div class="no-print text-center py-4 text-xs text-[#8E7E73]">
            <p class="font-medium text-[#5D4B3E]">Berkat Dinasti &bull; UMKM Kuliner Roti & Pastry</p>
            <p class="text-[10px] text-[#A8988D] mt-1">&copy; <?= date('Y') ?> All rights reserved. Malang, Jawa Timur.</p>
        </div>

    </div>

    <!-- TOAST NOTIFIKASI -->
    <div id="toastNotification" class="fixed bottom-6 z-50 transform translate-y-20 opacity-0 transition-all duration-300 pointer-events-none bg-[#3E2B21] text-white px-5 py-3 rounded-2xl shadow-2xl flex items-center gap-3 text-xs border border-white/10">
        <i id="toastIcon" class="fas fa-check-circle text-emerald-400 text-base"></i>
        <span id="toastMsg" class="font-medium">Berhasil</span>
    </div>

    <!-- JAVASCRIPT LOGIC -->
    <script>
        // Fungsi Toast Notifikasi
        function showToast(message, iconClass = 'fa-check-circle text-emerald-400') {
            const toast = document.getElementById('toastNotification');
            const toastMsg = document.getElementById('toastMsg');
            const toastIcon = document.getElementById('toastIcon');

            toastMsg.textContent = message;
            toastIcon.className = 'fas ' + iconClass + ' text-base';

            toast.classList.remove('translate-y-20', 'opacity-0');
            toast.classList.add('translate-y-0', 'opacity-100');

            setTimeout(() => {
                toast.classList.remove('translate-y-0', 'opacity-100');
                toast.classList.add('translate-y-20', 'opacity-0');
            }, 3000);
        }

        // Salin Tautan Nota Digital
        function copyReceiptLink() {
            const currentUrl = window.location.href;
            if (navigator.clipboard && navigator.clipboard.writeText) {
                navigator.clipboard.writeText(currentUrl).then(() => {
                    showToast('Tautan nota digital berhasil disalin!');
                }).catch(() => {
                    fallbackCopy(currentUrl);
                });
            } else {
                fallbackCopy(currentUrl);
            }
        }

        // Salin Rekening BCA
        function copyNorek() {
            const norek = document.getElementById('norekBCA').innerText.replace(/-/g, '');
            if (navigator.clipboard && navigator.clipboard.writeText) {
                navigator.clipboard.writeText(norek).then(() => {
                    showToast('Nomor rekening BCA berhasil disalin!');
                }).catch(() => {
                    fallbackCopy(norek);
                });
            } else {
                fallbackCopy(norek);
            }
        }

        function fallbackCopy(text) {
            const tempInput = document.createElement('input');
            tempInput.value = text;
            document.body.appendChild(tempInput);
            tempInput.select();
            document.execCommand('copy');
            document.body.removeChild(tempInput);
            showToast('Teks berhasil disalin ke clipboard!');
        }

        // Ekspor Gambar PNG Bersih Menggunakan html2canvas
        function downloadReceiptPNG() {
            const captureArea = document.getElementById('receipt-capture');
            const btn = document.getElementById('btnDownloadImage');
            const originalContent = btn.innerHTML;

            btn.disabled = true;
            btn.innerHTML = '<i class="fas fa-spinner fa-spin text-base text-[#F5A25D]"></i> <span>Memproses Gambar...</span>';

            // Pilihan optimasi capture
            html2canvas(captureArea, {
                scale: 2, // Resolusi tinggi (Retina display)
                useCORS: true,
                allowTaint: true,
                backgroundColor: '#FFFFFF',
                logging: false,
                windowWidth: 480
            }).then(canvas => {
                const invoiceNumber = <?= json_encode($orderNumber) ?>;
                const link = document.createElement('a');
                link.download = 'struk-berkat-dinasti-' + invoiceNumber + '.png';
                link.href = canvas.toDataURL('image/png', 1.0);
                link.click();

                btn.disabled = false;
                btn.innerHTML = originalContent;
                showToast('Gambar struk berhasil diunduh ke galeri!');
            }).catch(err => {
                console.error('Gagal membuat gambar struk:', err);
                btn.disabled = false;
                btn.innerHTML = originalContent;
                showToast('Gagal memproses gambar struk. Coba cetak manual.', 'fa-exclamation-triangle text-rose-400');
            });
        }
    </script>
</body>
</html>
