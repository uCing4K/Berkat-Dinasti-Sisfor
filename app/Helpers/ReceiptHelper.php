<?php
/**
 * ReceiptHelper & Digital Tracking Engine
 * Sistem Informasi Berkat Dinasti (Bakery & Pastry UMKM)
 * 
 * Mengelola Zero-Login Capability URL, pembentukan pesan WhatsApp otomatis,
 * sanitasi PII (Privacy Minimization), serta validasi token kriptografis (128-bit CSPRNG).
 */

class ReceiptHelper {
    
    /**
     * Menghasilkan unguessable 128-bit capability tracking token (32 karakter hex)
     */
    public static function generateTrackingToken(): string {
        try {
            return bin2hex(random_bytes(16));
        } catch (\Exception $e) {
            // Fallback aman jika CSPRNG entropy pool sedang sibuk
            return md5(uniqid((string)mt_rand(), true) . microtime(true));
        }
    }

    /**
     * Memvalidasi format tracking token (32 - 64 hex characters)
     */
    public static function isValidTrackingToken(?string $token): bool {
        if (empty($token)) {
            return false;
        }
        return (bool) preg_match('/^[a-f0-9]{32,64}$/i', trim($token));
    }

    /**
     * Mendapatkan URL lengkap Nota Digital berdasarkan tracking token
     */
    public static function getDigitalReceiptUrl(string $token): string {
        $baseUrl = defined('BASE_URL') ? BASE_URL : '/';
        $baseUrl = rtrim($baseUrl, '/');
        // Jika BASE_URL sudah berakhiran /public, sesuaikan
        if (str_ends_with($baseUrl, '/public')) {
            return $baseUrl . '/nota-digital.php?token=' . urlencode($token);
        }
        return $baseUrl . '/public/nota-digital.php?token=' . urlencode($token);
    }

    /**
     * Masking nomor telepon untuk proteksi PII pada halaman publik
     * Contoh: 081234567890 -> 0812-****-7890
     */
    public static function maskPhoneNumber(?string $phone): string {
        if (empty($phone)) return '-';
        $clean = preg_replace('/[^0-9]/', '', $phone);
        $len = strlen($clean);
        if ($len <= 6) return $phone;
        $start = substr($clean, 0, 4);
        $end = substr($clean, -4);
        return $start . '-****-' . $end;
    }

    /**
     * Masking alamat spesifik pelanggan untuk privasi pada URL publik
     */
    public static function maskAddress(?string $address): string {
        if (empty($address)) return 'Kota Malang / Kota Batu';
        $lines = explode(',', $address);
        if (count($lines) > 1) {
            // Ambil area kelurahan/kecamatan/kota saja
            return trim(end($lines));
        }
        $words = explode(' ', trim($address));
        if (count($words) > 3) {
            return 'Jl. ***, ' . implode(' ', array_slice($words, -2));
        }
        return $address;
    }

    /**
     * Ucapan salam berbasis waktu lokal Indonesia (WIB)
     */
    public static function getTimeGreeting(): string {
        $hour = (int) date('H');
        if ($hour >= 4 && $hour < 11) {
            return 'Selamat pagi';
        } elseif ($hour >= 11 && $hour < 15) {
            return 'Selamat siang';
        } elseif ($hour >= 15 && $hour < 18) {
            return 'Selamat sore';
        } else {
            return 'Selamat malam';
        }
    }

    /**
     * Menyusun format pesan WhatsApp profesional siap kirim ke pelanggan
     */
    public static function buildWhatsAppMessage(array $order, string $token): string {
        $greeting = self::getTimeGreeting();
        $custName = htmlspecialchars_decode($order['nama_pelanggan'] ?? 'Pelanggan Setia');
        $invoice = $order['no_invoice'] ?? '-';
        $grandTotal = 'Rp ' . number_format((float)($order['grand_total'] ?? 0), 0, ',', '.');
        $tglKirim = !empty($order['tgl_kirim']) ? date('d-m-Y', strtotime($order['tgl_kirim'])) : '-';
        $waktuKirim = !empty($order['waktu_kirim']) ? substr($order['waktu_kirim'], 0, 5) . ' WIB' : 'Pagi/Siang';
        $notaUrl = self::getDigitalReceiptUrl($token);

        // Status bayar terformat
        $statusBayar = 'Belum Lunas';
        if (($order['status_bayar'] ?? '') === 'lunas') {
            $statusBayar = 'LUNAS (Terima Kasih)';
        } elseif (($order['status_bayar'] ?? '') === 'dp') {
            $statusBayar = 'Uang Muka (DP)';
        }

        $msg = "Halo Kak *{$custName}*, {$greeting}! 🍞✨\n\n";
        $msg .= "Terima kasih telah mempercayakan pesanan bakery & pastry kepada *Berkat Dinasti*.\n\n";
        $msg .= "📋 *DETAIL PESANAN:*\n";
        $msg .= "• No. Invoice: *#{$invoice}*\n";
        $msg .= "• Total Tagihan: *{$grandTotal}*\n";
        $msg .= "• Status Bayar: *{$statusBayar}*\n";
        $msg .= "• Jadwal Kirim: *{$tglKirim}* (Pukul {$waktuKirim})\n";
        $msg .= "• Ongkir: *Rp 0 (Bebas Ongkir Malang & Batu)*\n\n";
        $msg .= "🧾 *NOTA DIGITAL & STATUS PESANAN:*\n";
        $msg .= "Kakak dapat melihat rincian item, memantau proses dapur, serta mengunduh nota resmi melalui tautan berikut:\n";
        $msg .= "👉 {$notaUrl}\n\n";
        $msg .= "Jika ada catatan tambahan atau pertanyaan, silakan langsung balas pesan ini ya Kak. Selamat beraktivitas! 🙏❤️\n";
        $msg .= "_— Layanan Pelanggan UMKM Berkat Dinasti Bakery_";

        return $msg;
    }

    /**
     * Membangun direct deep link WhatsApp
     */
    public static function buildWhatsAppUrl(string $phone, string $message): string {
        $cleanPhone = preg_replace('/[^0-9]/', '', $phone);
        // Konversi format lokal 08xx ke standar internasional 628xx
        if (str_starts_with($cleanPhone, '0')) {
            $cleanPhone = '62' . substr($cleanPhone, 1);
        }
        return 'https://wa.me/' . $cleanPhone . '?text=' . rawurlencode($message);
    }

    /**
     * Self-healing migration: Memastikan kolom tracking_token ada pada tabel pesanan
     * serta melakukan backfill token untuk pesanan lama yang masih kosong
     */
    public static function ensureTrackingTokenSupport(\PDO $pdo): void {
        try {
            // 1. Cek apakah kolom tracking_token sudah ada
            $checkCol = $pdo->query("SHOW COLUMNS FROM pesanan LIKE 'tracking_token'")->fetch();
            if (!$checkCol) {
                try {
                    $pdo->exec("ALTER TABLE pesanan ADD COLUMN tracking_token VARCHAR(64) UNIQUE NULL AFTER catatan");
                } catch (\Exception $eAlter) {
                    // Beberapa versi MySQL tidak mendukung UNIQUE langsung pada ADD COLUMN jika ada data null ganda
                    $pdo->exec("ALTER TABLE pesanan ADD COLUMN tracking_token VARCHAR(64) NULL AFTER catatan");
                    try {
                        $pdo->exec("ALTER TABLE pesanan ADD UNIQUE INDEX idx_tracking_token (tracking_token)");
                    } catch (\Exception $eIdx) {}
                }
            }

            // 2. Backfill pesanan eksisting yang belum memiliki tracking_token
            $stmtMissing = $pdo->query("SELECT id_pesanan FROM pesanan WHERE tracking_token IS NULL OR tracking_token = '' LIMIT 200");
            $rows = $stmtMissing->fetchAll(\PDO::FETCH_COLUMN);
            if (!empty($rows)) {
                $stmtUpdate = $pdo->prepare("UPDATE pesanan SET tracking_token = ? WHERE id_pesanan = ?");
                foreach ($rows as $orderId) {
                    $token = self::generateTrackingToken();
                    $stmtUpdate->execute([$token, $orderId]);
                }
            }
        } catch (\Exception $e) {
            // Log error silently without breaking request flow
            error_log("ReceiptHelper::ensureTrackingTokenSupport exception: " . $e->getMessage());
        }
    }

    /**
     * Rate limiter sederhana berbasis IP untuk memproteksi endpoint publik
     * Max 60 requests per 60 seconds
     */
    public static function enforceRateLimit(int $maxRequests = 60, int $windowSeconds = 60): void {
        $ip = $_SERVER['REMOTE_ADDR'] ?? '127.0.0.1';
        $safeIpKey = preg_replace('/[^a-zA-Z0-9_]/', '_', $ip);
        $tempDir = sys_get_temp_dir() . DIRECTORY_SEPARATOR . 'dinasti_ratelimit';
        
        if (!is_dir($tempDir)) {
            @mkdir($tempDir, 0777, true);
        }

        $file = $tempDir . DIRECTORY_SEPARATOR . 'rl_' . $safeIpKey . '.json';
        $now = time();
        $data = ['count' => 0, 'start' => $now];

        if (file_exists($file)) {
            $content = @file_get_contents($file);
            $parsed = json_decode($content, true);
            if (is_array($parsed) && isset($parsed['count'], $parsed['start'])) {
                if ($now - $parsed['start'] < $windowSeconds) {
                    $data = $parsed;
                }
            }
        }

        $data['count']++;

        if ($data['count'] > $maxRequests) {
            http_response_code(429);
            header('Content-Type: text/html; charset=UTF-8');
            header('Retry-After: ' . ($windowSeconds - ($now - $data['start'])));
            echo '<!DOCTYPE html><html><head><title>Too Many Requests</title><style>body{font-family:sans-serif;text-align:center;padding:50px;background:#FAF1E6;color:#3E2B21;}</style></head><body><h2>Terlalu Banyak Permintaan (429)</h2><p>Mohon tunggu beberapa saat sebelum memuat ulang nota digital ini kembali.</p></body></html>';
            exit();
        }

        @file_put_contents($file, json_encode($data), LOCK_EX);
    }
}
