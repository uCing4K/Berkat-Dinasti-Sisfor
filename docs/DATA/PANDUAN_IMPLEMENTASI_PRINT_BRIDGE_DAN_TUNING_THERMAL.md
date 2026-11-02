# 🖨️ Panduan Lengkap Implementasi Print Bridge & Tuning Thermal Printer

Dokumen ini adalah **spesifikasi teknis, arsitektur, dan panduan implementasi produksi** untuk menghubungkan aplikasi POS (Web / PWA / PHP) ke **Thermal Printer (58mm / 80mm)** melalui perantara **Aplikasi Print Bridge** (Android / Desktop Daemon).

Panduan ini mencakup:
1. **Arsitektur & Solusi Komunikasi Data (Mengatasi Browser Mixed Content & Private Network Access)**
2. **Kontrak Data (Payload Contract) & Strategi Rendering Raw ESC/POS**
3. **Implementasi Aplikasi Print Bridge (Kotlin Android & Node.js/Desktop Daemon)**
4. **Tuning Teks Thermal: Cetak Bersih, Tajam, dan Jernih (Tanpa Teks Buram/Pecah)**
5. **Tuning & Dukungan Logo: Konversi 1-Bit Monochrome, Dithering vs Thresholding, & Anti-Garis**
6. **Checklist & Langkah Kerja Developer (Frontend, Backend, & Bridge App)**

---

## 📑 Daftar Isi
- [1. Arsitektur Alur Data & Solusi Mixed-Content](#1-arsitektur-alur-data--solusi-mixed-content)
- [2. Kontrak Payload Data: Mengapa Server-Side ESC/POS Lebih Unggul](#2-kontrak-payload-data-mengapa-server-side-escpos-lebih-unggul)
- [3. Implementasi Print Bridge (Android & Desktop)](#3-implementasi-print-bridge-android--desktop)
  - [3.1 Android Print Bridge (Kotlin + NanoHTTPD / Ktor)](#31-android-print-bridge-kotlin)
  - [3.2 Fitur Krusial: Buffer Chunking (Cegah Crash / Missing Data)](#32-fitur-krusial-buffer-chunking-cegah-crash--missing-data)
  - [3.3 Menjadikan Android Background Service Kebal Mati (Foreground Service)](#33-menjadikan-android-background-service-kebal-mati)
- [4. Tuning Cetak Teks: Super Bersih & Jernih (Clean & Crisp)](#4-tuning-cetak-teks-super-bersih--jernih-clean--crisp)
  - [4.1 Perbedaan Fatal: Native ESC/POS Font vs Bitmap Rendering](#41-perbedaan-fatal-native-escpos-font-vs-bitmap-rendering)
  - [4.2 Perhitungan Dot & Karakter (58mm vs 80mm)](#42-perhitungan-dot--karakter-58mm-vs-80mm)
  - [4.3 Tabel Perintah ESC/POS untuk Tuning Teks](#43-tabel-perintah-escpos-untuk-tuning-teks)
  - [4.4 Solusi Cetak Pudar atau Gosong (Thermal Density Control)](#44-solusi-cetak-pudar-atau-gosong-thermal-density-control)
- [5. Implementasi & Tuning Cetak Logo Tajam (Support Logo)](#5-implementasi--tuning-cetak-logo-tajam-support-logo)
  - [5.1 Prinsip Kerja Gambar pada Printer Thermal](#51-prinsip-kerja-gambar-pada-printer-thermal)
  - [5.2 Preprocessing Gambar: Thresholding vs Dithering](#52-preprocessing-gambar-thresholding-vs-dithering)
  - [5.3 Metode Cetak Logo: ESC * (Column) vs GS v 0 (Raster)](#53-metode-cetak-logo-esc--column-vs-gs-v-0-raster)
  - [5.4 Mengatasi Masalah Garis Putih Horisontal pada Logo](#54-mengatasi-masalah-garis-putih-horisontal-pada-logo)
- [6. Implementasi Sisi Web & POS (Frontend & Backend PHP)](#6-implementasi-sisi-web--pos-frontend--backend-php)
  - [6.1 Backend Generator ESC/POS Lengkap (PHP)](#61-backend-generator-escpos-lengkap-php)
  - [6.2 Frontend JavaScript Client (`bluetooth-print.js`)](#62-frontend-javascript-client-bluetooth-printjs)
- [7. Checklist Kerja Pengembang (Action Plan)](#7-checklist-kerja-pengembang-action-plan)
- [8. Matriks Troubleshooting Masalah Umum](#8-matriks-troubleshooting-masalah-umum)

---

## 1. Arsitektur Alur Data & Solusi Mixed-Content

### 1.1 Diagram Alur Komunikasi

```
┌─────────────────────────────────────────────────────────────────┐
│                    1. PWA / WEB POS (Browser)                  │
│  User klik "Cetak Struk" pada halaman Order / Kasir            │
└────────────────────────────────┬────────────────────────────────┘
                                 │
                                 │ HTTP GET / POST (Same-Origin HTTPS)
                                 ▼
┌─────────────────────────────────────────────────────────────────┐
│                    2. BACKEND API (PHP Server)                  │
│  - Ambil data transaksi dari Database (Order, Items, Setting)   │
│  - Lakukan kompilasi layout struk ke array Raw Bytes ESC/POS   │
│  - Binarisasi logo (1-bit monochrome)                           │
│  - Response JSON: { success: true, escpos: [27, 64, 27, ...] }   │
└────────────────────────────────┬────────────────────────────────┘
                                 │
                                 │ JSON Response
                                 ▼
┌─────────────────────────────────────────────────────────────────┐
│                 3. JAVASCRIPT PRINT CLIENT (Browser)            │
│  Menerima raw bytes, lalu meneruskan ke Print Bridge            │
└────────────────────────────────┬────────────────────────────────┘
                                 │
                                 │ HTTP POST / WebSocket ke Bridge
                                 ▼
┌─────────────────────────────────────────────────────────────────┐
│            4. PRINT BRIDGE APP (Android / Desktop Daemon)       │
│  - Local HTTP Server (Port 8080) / WebSocket Server             │
│  - Menerima raw byte array                                      │
│  - Memecah data menjadi chunk (512 bytes) dengan delay           │
└────────────────────────────────┬────────────────────────────────┘
                                 │
                                 │ Bluetooth SPP / USB ESC-POS / LAN Raw 9100
                                 ▼
┌─────────────────────────────────────────────────────────────────┐
│                 5. THERMAL PRINTER (58mm / 80mm)                │
│  (Xprinter XP58IIZ, Epson TM-T82, Panda, Iware, Sunmi, dll)     │
│  Mencetak nota jernih dengan logo tajam & melakukan auto-cut     │
└─────────────────────────────────────────────────────────────────┘
```

---

### 1.2 Mengatasi Kendala Browser: Mixed-Content & Private Network Access (PNA)

Jika aplikasi web di-host pada domain **HTTPS** (contoh: `https://rodeolaundry.my.id`), peramban modern (Chrome, Edge, Firefox) secara default memblokir request langsung ke `http://localhost:8080` (HTTP lokal) karena aturan:
1. **Mixed Content Policy**: Halaman HTTPS dilarang memuat resource HTTP tidak aman.
2. **Private Network Access (PNA)**: Situs web publik dilarang menembus IP private/loopback lokal tanpa izin khusus.

#### Solusi yang Terbukti Berhasil di Lingkungan Produksi:

| Pendekatan | Cara Kerja | Kelebihan | Rekomendasi |
| :--- | :--- | :--- | :--- |
| **A. Header CORS + PNA Lengkap di Bridge** | Tambahkan header `Access-Control-Allow-Private-Network: true` dan respon `OPTIONS` preflight di Bridge HTTP server. | Transparan untuk browser standar Chrome di Android/PC. | ⭐ **Wajib Dipasang** |
| **B. Proxy Iframe `postMessage`** | Menggunakan iframe khusus (`bridge-proxy.html`) untuk menjembatani pesan antara HTTPS dan HTTP. | Berhasil melewati blokir mixed content di beberapa versi browser lama. | ⭐ **Sudah terbukti di project** |
| **C. WebSocket Bridge (`ws://localhost:8080`)** | Browser mengizinkan koneksi WebSocket dari halaman HTTPS ke `ws://127.0.0.1` tanpa strict mixed-content block. | Lebih cepat, real-time status printer (kertas habis, offline). | 🚀 **Solusi Modern Terbaik** |
| **D. Android WebView (Wrapper App / PWA TWA)** | Jika kasir memakai tablet/HP dengan WebView wrapper Android, setel konfigurasi: `settings.mixedContentMode = WebSettings.MIXED_CONTENT_ALWAYS_ALLOW`. | 100% bebas dari blokir browser. | ⭐ **Ideal untuk POS Tablet Dedikasi** |
| **E. Custom Intent / Deep Link Scheme** | Web membuka URL `printbridge://print?data=...` yang otomatis ditangkap aplikasi bridge di Android. | Tidak terpengaruh koneksi HTTP sama sekali. | 🔄 Alternatif tanpa socket lokal |

---

## 2. Kontrak Payload Data: Mengapa Server-Side ESC/POS Lebih Unggul

Terdapat 2 pendekatan dalam pengiriman data:

### Pendekatan 1: Client Mengirim Raw ESC/POS Byte Array (SANGAT DIREKOMENDASIKAN)
```json
{
  "type": "raw_escpos",
  "escpos": [27, 64, 27, 97, 1, 27, 42, 33, ...],
  "timestamp": 1718000000
}
```
* **Kelebihan**:
  - **Single Source of Truth**: Format nota, layout baris, font besar/kecil, barcode, dan penyesuaian logo diatur sepenuhnya di sisi Backend Web (PHP).
  - **Zero Maintenance di Bridge**: Aplikasi Android/Desktop Bridge hanya bertindak sebagai "pipa transfer" (*dumb forwarder*). Jika besok pemilik toko ingin mengubah letak nomor telepon atau diskon, pengembang **TIDAK PERLU** mengedit kode Kotlin dan tidak perlu re-build APK!
  - **Akurasi Tuning Maksimal**: PHP dapat memproses piksel logo, padding teks, dan encoding karakter secara presisi.

### Pendekatan 2: Client Mengirim Structured JSON (Format Alternatif)
```json
{
  "order_id": "ORD-2026-001",
  "customer_name": "Dzauq",
  "items": [
    { "name": "Cuci Kering Setrika", "qty": 3.5, "unit": "kg", "price": 8000, "subtotal": 28000 }
  ],
  "total": 28000
}
```
* **Kekurangan**: Bridge harus memiliki generator ESC/POS sendiri. Setiap perubahan format memerlukan update APK/software bridge di seluruh perangkat kasir.

> **Keputusan Arsitektur**: Standarisasi payload utama menggunakan **Raw Byte Array (Pendekatan 1)**, dengan fallback JSON sederhana untuk kebutuhan tes diagnostik.

---

## 3. Implementasi Print Bridge (Android & Desktop)

Aplikasi Bridge bertindak sebagai server lokal yang mendengarkan request dari web POS dan menulis data ke printer fisik.

### 3.1 Android Print Bridge (Kotlin)

Aplikasi Android menggunakan server HTTP ringan seperti **NanoHTTPD** atau **Ktor Embedded Server**.

#### A. Konfigurasi `PrintWebServer.kt` (Wajib Header CORS & PNA)
```kotlin
package com.rodeolaundry.printbridge

import fi.iki.elonen.NanoHTTPD
import org.json.JSONObject

class PrintWebServer(
    private val mainActivity: MainActivity,
    port: Int = 8080
) : NanoHTTPD(port) {

    override fun serve(session: IHTTPSession): Response {
        // Headers wajib untuk bypass Mixed Content & Private Network Access
        val headers = mutableMapOf(
            "Access-Control-Allow-Origin" to "*",
            "Access-Control-Allow-Methods" to "POST, GET, OPTIONS",
            "Access-Control-Allow-Headers" to "Content-Type, Authorization, X-Requested-With",
            "Access-Control-Allow-Private-Network" to "true" // PENTING: Untuk Chrome Private Network Access
        )

        // Tangani preflight OPTIONS
        if (session.method == Method.OPTIONS) {
            return newFixedLengthResponse(Response.Status.OK, "text/plain", "OK").apply {
                headers.forEach { (k, v) -> addHeader(k, v) }
            }
        }

        return when (session.uri) {
            "/status" -> {
                val statusJson = JSONObject().apply {
                    put("status", "online")
                    put("printer_connected", mainActivity.isPrinterConnected())
                    put("device_name", mainActivity.getConnectedDeviceName())
                }
                newFixedLengthResponse(Response.Status.OK, "application/json", statusJson.toString()).apply {
                    headers.forEach { (k, v) -> addHeader(k, v) }
                }
            }

            "/print" -> {
                if (session.method != Method.POST) {
                    return newFixedLengthResponse(Response.Status.METHOD_NOT_ALLOWED, "application/json", "{\"error\":\"Only POST allowed\"}").apply {
                        headers.forEach { (k, v) -> addHeader(k, v) }
                    }
                }

                try {
                    val bodyMap = HashMap<String, String>()
                    session.parseBody(bodyMap)
                    val rawBody = bodyMap["postData"] ?: ""

                    val success = mainActivity.printReceipt(rawBody)

                    val response = if (success) {
                        """{"success":true,"message":"Data berhasil dikirim ke printer"}"""
                    } else {
                        """{"success":false,"message":"Gagal mencetak. Cek koneksi printer."}"""
                    }

                    newFixedLengthResponse(Response.Status.OK, "application/json", response).apply {
                        headers.forEach { (k, v) -> addHeader(k, v) }
                    }
                } catch (e: Exception) {
                    newFixedLengthResponse(Response.Status.INTERNAL_ERROR, "application/json", """{"success":false,"message":"${e.localizedMessage}"}""").apply {
                        headers.forEach { (k, v) -> addHeader(k, v) }
                    }
                }
            }

            else -> newFixedLengthResponse(Response.Status.NOT_FOUND, "text/plain", "404 Not Found").apply {
                headers.forEach { (k, v) -> addHeader(k, v) }
            }
        }
    }
}
```

---

### 3.2 Fitur Krusial: Buffer Chunking (Cegah Crash / Missing Data)

> [!WARNING]
> **Masalah Tersembunyi Bluetooth Thermal Printer:**
> Thermal printer berbiaya terjangkau (Xprinter XP58IIZ, GOOJPRT, Panda, Eppos, dsb.) memiliki chip microcontroller dengan RAM buffer internal sangat kecil (seringkali hanya 2KB - 4KB).
> Jika Anda mengirim data struk yang memuat **Logo Bitmap (berukuran 10KB - 30KB)** secara serentak (`outputStream.write(bytes)`), buffer printer akan seketika meluap (**Buffer Overflow**).
> Akibatnya:
> - Logo terpotong di tengah jalan.
> - Printer mencetak karakter acak/sampah (`???@@@x!x`).
> - Koneksi Bluetooth SPP terputus tiba-tiba (*Broken pipe*).

#### Solusi: Kirim Data Menggunakan Chunking (Potongan 512 Bytes + Sleep)
Tambahkan fungsi berikut pada pengiriman Bluetooth di Android:

```kotlin
fun sendBytesChunked(bytes: ByteArray, chunkSize: Int = 512, delayMs: Long = 25): Boolean {
    val stream = outputStream ?: return false
    return try {
        var offset = 0
        while (offset < bytes.size) {
            val length = Math.min(chunkSize, bytes.size - offset)
            stream.write(bytes, offset, length)
            stream.flush()
            offset += length
            
            // Beri jeda kecil agar mikroprosesor printer sempat membakar kertas
            if (offset < bytes.size && delayMs > 0) {
                Thread.sleep(delayMs)
            }
        }
        true
    } catch (e: Exception) {
        e.printStackTrace()
        false
    }
}
```

---

### 3.3 Menjadikan Android Background Service Kebal Mati

Android versi modern (Android 9 hingga Android 14+) memiliki fitur agresif *Battery Optimization (Doze Mode)* yang akan membunuh socket server lokal jika aplikasi diminimalkan ke background.

**Solusi:**
1. Daftarkan dan jalankan aplikasi sebagai **Android Foreground Service** dengan notifikasi permanen di status bar.
2. Tambahkan izin di `AndroidManifest.xml`:
   ```xml
   <uses-permission android:name="android.permission.FOREGROUND_SERVICE" />
   <uses-permission android:name="android.permission.FOREGROUND_SERVICE_CONNECTED_DEVICE" />
   <uses-permission android:name="android.permission.WAKE_LOCK" />
   <uses-permission android:name="android.permission.REQUEST_IGNORE_BATTERY_OPTIMIZATIONS" />
   ```
3. Minta user menonaktifkan optimasi baterai untuk aplikasi bridge (*Don't Optimize / Unrestricted*).

---

## 4. Tuning Cetak Teks: Super Bersih & Jernih (Clean & Crisp)

### 4.1 Perbedaan Fatal: Native ESC/POS Font vs Bitmap Rendering

Banyak pengembang melakukan kesalahan dengan **meng-convert seluruh struk HTML menjadi gambar/canvas via html2canvas lalu mencetak gambar tersebut**.

| Kriteria | Native ESC/POS Font | Render Gambar / Canvas (Bitmap) |
| :--- | :--- | :--- |
| **Kerapian Huruf** | 🟢 **Sangat Tajam & Jernih**: Menggunakan font vektor/matriks bawaan hardware printer pada resolusi native (203 DPI). | 🔴 **Buram & Pecah**: Teks dikonversi menjadi pixel raster yang bergerigi (*anti-aliasing fuzziness*). |
| **Kecepatan Cetak** | ⚡ **Instan (0.3 - 0.8 detik)**: Hanya mengirim deretan kode ASCII (~500 bytes). | 🐢 **Lambat (3 - 8 detik)**: Mengirim ribuan bytes data gambar hitam-putih. |
| **Konsumsi Kertas & Listrik** | 🟢 Efisien dan dingin. | 🔴 Kepala thermal panas karena harus membakar area hitam luas terus-menerus. |

> **Prinsip Utama:**
> Gunakan **Native Text Command ESC/POS** untuk seluruh teks, angka, tabel, dan garis. Gunakan mode bitmap/gambar **HANYA KHUSUS untuk LOGO**!

---

### 4.2 Perhitungan Dot & Karakter (58mm vs 80mm)

Thermal head memiliki resolusi standar **203 DPI (8 dots per millimeter)**:

| Ukuran Kertas | Lebar Efektif Cetak | Total Dots Horisontal | Font A (12 x 24 dots) | Font B (9 x 17 dots) |
| :--- | :--- | :--- | :--- | :--- |
| **58 mm** | 48 mm | **384 dots** | **32 karakter / baris** | **42 - 48 karakter / baris** |
| **80 mm** | 72 mm | **576 dots** | **48 karakter / baris** | **64 karakter / baris** |

> [!NOTE]
> Pada kertas 58mm (seperti Xprinter XP58IIZ):
> Format teks 2-kolom (Nama Item di kiri, Harga di kanan) memiliki batas total **32 karakter per baris**. Jika total karakter melebihi 32, teks akan terlipat ke baris baru (*word-wrap*).

---

### 4.3 Tabel Perintah ESC/POS untuk Tuning Teks

| Fungsi | Kode Hex | Kode Decimal | Keterangan & Penggunaan |
| :--- | :--- | :--- | :--- |
| **Inisialisasi Printer** | `1B 40` | `27, 64` | `ESC @` — Wajib di awal setiap nota untuk mereset font, margin, dan buffer. |
| **Pilih Font A (12x24)** | `1B 4D 00` | `27, 77, 0` | Standar tajam untuk teks biasa & item (32 kolom pada 58mm). |
| **Pilih Font B (9x17)** | `1B 4D 01` | `27, 77, 1` | Font lebih kecil dan rapat. Bagus untuk keterangan catatan, syarat & ketentuan / disclaimer. |
| **Bold ON / OFF** | `1B 45 01` / `1B 45 00` | `27, 69, 1` / `27, 69, 0` | Membuat teks tebal (tegas) pada judul toko, total belanja, nomor nota. |
| **Rata Kiri** | `1B 61 00` | `27, 97, 0` | Untuk item belanja dan detail pembayaran. |
| **Rata Tengah (Center)** | `1B 61 01` | `27, 97, 1` | Untuk logo, nama toko, alamat, footer, dan QR Code. |
| **Rata Kanan** | `1B 61 02` | `27, 97, 2` | Digunakan jika diperlukan penulisan angka tertentu di kanan. |
| **Ukuran Dobel Tinggi & Lebar** | `1D 21 11` | `29, 33, 17` | `GS ! 0x11` — Nama toko di header terlihat gagah dan menonjol. |
| **Ukuran Dobel Tinggi Saja** | `1D 21 01` | `29, 33, 1` | `GS ! 0x01` — Total Belanja & Nama Pelanggan terlihat jelas tanpa memakan lebar kolom. |
| **Reset Ukuran Normal** | `1D 21 00` | `29, 33, 0` | Mengembalikan ukuran font ke 1x1. |
| **Line Spacing Rapat (24 dots)** | `1B 33 18` | `27, 51, 24` | `ESC 3 24` — Memperpendek jarak antar baris, menghemat kertas & tampilan rapi. |
| **Line Spacing Default** | `1B 32` | `27, 50` | `ESC 2` — Mengembalikan jarak antar baris ke pengaturan pabrik. |
| **Invert (White on Black)** | `1D 42 01` / `1D 42 00` | `29, 66, 1` / `29, 66, 0` | `GS B 1` — Mencetak background hitam dengan teks putih (efektif untuk nomor antrean / label). |
| **Potong Kertas (Cut)** | `1D 56 42 00` | `29, 86, 66, 0` | `GS V 66 0` — Partial cut dengan auto feed (khusus printer dengan auto-cutter). |

---

### 4.4 Solusi Cetak Pudar atau Gosong (Thermal Density Control)

Jika teks terlihat tipis/pudar (*faint*) atau sebaliknya gosong (*bleeding/too dark*):
1. **Periksa Kualitas Kertas Thermal**: Kertas thermal murah atau yang terkena udara lembap/sinar matahari tidak akan menghitam maksimal meskipun suhu head dinaikkan.
2. **Perintah ESC/POS Penyetelan Panas (Strobe / Heating Time)**:
   Beberapa produsen menyediakan perintah khusus pengatur kepekatan.
   Contoh untuk Xprinter / ESC/POS kompatibel:
   ```php
   // Set Print Density (GS ( K)
   // Density level: 1 (tipis) s/d 8 (paling pekat). Nilai standar rekomendasi: 4 atau 5.
   $commands[] = 0x1D;
   $commands[] = 0x28;
   $commands[] = 0x4B;
   $commands[] = 0x02;
   $commands[] = 0x00;
   $commands[] = 0x31;
   $commands[] = 0x05; // Level 5
   ```
3. **Penyetelan Jarak Baris Antar Teks**: Jangan biarkan baris teks menempel tanpa feed (`0x0A`), karena thermal head akan memanaskan titik yang sama berulang kali dan memicu noda gosong.

---

## 5. Implementasi & Tuning Cetak Logo Tajam (Support Logo)

### 5.1 Prinsip Kerja Gambar pada Printer Thermal

Thermal printer **TIDAK BISA** mencetak warna, tidak punya skala abu-abu (*grayscale*), dan tidak mendukung transparansi (*alpha channel*). 
Sebuah piksel hanya memiliki 2 status:
- **0 (Bit 0)**: Putih (Thermal element mati, kertas tetap putih).
- **1 (Bit 1)**: Hitam (Thermal element menyala, memanaskan kertas menjadi hitam).

---

### 5.2 Preprocessing Gambar: Thresholding vs Dithering

Sebelum data dikirim ke printer, gambar asli (PNG/JPG) harus diproses secara digital:

```
[Logo Asli (PNG Berwarna/Transparan)]
                 │
                 ▼
[Isi Area Transparan dengan Warna Putih Murni (#FFFFFF)]
                 │
                 ▼
[Resize Proporsional ke Lebar Target: 280px - 384px]
                 │
                 ▼
[Konversi ke Grayscale (Luminance Y = 0.299R + 0.587G + 0.114B)]
                 │
                 ▼
       ┌─────────────────────────┴─────────────────────────┐
       │                                                   │
  (A) Thresholding (Ambang Batas)             (B) Floyd-Steinberg Dithering
  - Jika Gray < 140 -> Hitam (1)             - Menyebarkan error ke tetangga
  - Jika Gray >= 140 -> Putih (0)             - Membentuk pola titik halus
  - HASIL: Tepi tajam, teks logo jernih       - HASIL: Baik untuk foto,
  - COCOK UNTUK: Logo Vektor & Teks           - BURUK untuk logo karena berbintik
```

> **Rekomendasi Mutlak untuk Logo:**
> Gunakan **Fixed Thresholding (Ambang 130 - 145)** setelah sedikit menaikkan kontras (+15% s/d +20%). Logo akan tercetak bersih, bebas dari bintik-bintik pasir (*noise*) yang mengotori tepi logo.

---

### 5.3 Metode Cetak Logo: ESC * (Column) vs GS v 0 (Raster)

#### Metode 1: Mode Kolom 24-Dot Double Density (`ESC * 33`)
- **Kompatibilitas**: Mendukung 99.9% printer thermal murah dan lama (termasuk Xprinter XP58IIZ).
- **Struktur**: Gambar dipotong menjadi barisan vertikal setinggi 24 piksel. Tiap kolom terdiri dari 3 byte (24 bit).
- **Syarat Mutlak**:
  - Tinggi gambar total harus merupakan **kelipatan 24** (misal: 72, 96, 120, 144, dst). Jika tidak pas, tambahkan padding putih di bawahnya.
  - Lebar gambar maksimal 384 pixel untuk kertas 58mm (direkomendasikan 280-320 pixel agar ada margin samping).

#### Metode 2: Mode Raster (`GS v 0`)
- **Kompatibilitas**: Printer modern (Epson, Sunmi, Xprinter seri baru).
- **Struktur**: Data disusun horizontal baris per baris. 8 piksel horizontal dipadatkan menjadi 1 byte.
- **Kelebihan**: Format data lebih sederhana dan waktu kalkulasi lebih cepat.

---

### 5.4 Mengatasi Masalah Garis Putih Horisontal pada Logo

> [!IMPORTANT]
> **Penyebab Garis Putih Horisontal yang Sering Terjadi pada Logo:**
> Jika Anda menggunakan perintah `ESC * 33` (Mode 24-dot), printer secara default memiliki spasi baris pabrik sebesar **30 s/d 33 dots**. 
> Karena tiap strip gambar tingginya 24 dots sedangkan spasi baris printer 30 dots, maka muncul **selisih 6 dots kertas kosong berupa garis putih horisontal setiap strip 24 piksel!**

#### Cara Mengatasinya:
1. Sebelum mengirim strip gambar, setel line spacing tepat ke 24 dots:
   ```
   1B 33 18  (ESC 3 24)
   ```
2. Cetak seluruh strip gambar.
3. Setelah seluruh potongan logo selesai dicetak, kembalikan line spacing ke default:
   ```
   1B 32     (ESC 2)
   ```
Dengan trik ini, logo yang dihasilkan akan **mulus menyatu tanpa celah garis putih horizontal sedikit pun!**

---

## 6. Implementasi Sisi Web & POS (Frontend & Backend PHP)

### 6.1 Backend Generator ESC/POS Lengkap (PHP)

Buat file di `api/get-escpos-data.php`:

```php
<?php
/**
 * API Generator ESC/POS dengan Tuning Teks Tajam & Support Logo
 */

session_start();
header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Private-Network');
header('Access-Control-Allow-Private-Network: true');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}

// Lokasi Logo Monochrome
$logoPath = __DIR__ . '/../assets/images/logo-for-printer-fix-1.bmp'; 
if (!file_exists($logoPath)) {
    $logoPath = __DIR__ . '/../assets/images/logo-rodeo.png';
}

$orderId = intval($_GET['id'] ?? $_POST['id'] ?? 0);
if ($orderId <= 0) {
    http_response_code(400);
    echo json_encode(['success' => false, 'error' => 'Order ID tidak valid']);
    exit;
}

try {
    // Ambil data order dari database (disesuaikan dengan skema Anda)
    require_once __DIR__ . '/../config/database.php';
    $db = getDB();

    $stmt = $db->prepare("
        SELECT o.*, c.name as customer_name, c.phone as customer_phone 
        FROM orders o 
        JOIN customers c ON o.customer_id = c.id 
        WHERE o.id = ?
    ");
    $stmt->execute([$orderId]);
    $order = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$order) {
        throw new Exception("Order tidak ditemukan");
    }

    $stmtItems = $db->prepare("
        SELECT oi.*, p.name as product_name, p.unit 
        FROM order_items oi 
        JOIN products p ON oi.product_id = p.id 
        WHERE oi.order_id = ?
    ");
    $stmtItems->execute([$orderId]);
    $items = $stmtItems->fetchAll(PDO::FETCH_ASSOC);

    // Bangun ESC/POS commands
    $escposBytes = buildReceiptESCPOS($order, $items, $logoPath, 32); // 32 kolom untuk 58mm

    echo json_encode([
        'success' => true,
        'escpos'  => $escposBytes,
        'order'   => [
            'id'           => $order['id'],
            'order_number' => $order['order_number'] ?? ('#' . $order['id']),
            'total'        => $order['grand_total'] ?? $order['total_amount']
        ]
    ]);

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'error' => $e->getMessage()]);
}

/**
 * Kompilasi Keseluruhan Nota menjadi Raw Bytes
 */
function buildReceiptESCPOS($order, $items, $logoPath, $colWidth = 32) {
    $c = [];

    // 1. Reset Printer
    $c[] = 0x1B; $c[] = 0x40; // ESC @

    // 2. Cetak Logo (Jika Ada)
    if (file_exists($logoPath)) {
        appendOptimizedLogo($c, $logoPath, 280); // 280px lebar logo
    }

    // 3. Header Toko (Center, Bold, Double Size)
    appendAlign($c, 1); // Center
    appendBold($c, true);
    appendDoubleSize($c, true);
    appendText($c, "RODEO LAUNDRY\n");
    appendDoubleSize($c, false);
    appendBold($c, false);

    appendText($c, "Point of Sale System\n");
    appendText($c, "Telp: 0812-3456-7890\n");
    appendSeparator($c, '=', $colWidth);

    // 4. Informasi Transaksi (Left)
    appendAlign($c, 0);
    appendRow2Col($c, "No. Order:", $order['order_number'] ?? ('#' . $order['id']), $colWidth);
    appendRow2Col($c, "Tanggal  :", date('d/m/Y H:i'), $colWidth);
    appendSeparator($c, '-', $colWidth);

    // 5. Informasi Pelanggan (Bold Timbul)
    appendAlign($c, 1); // Center
    appendText($c, "Pelanggan:\n");
    appendBold($c, true);
    appendDoubleHeight($c, true);
    appendText($c, ($order['customer_name'] ?? 'Umum') . "\n");
    appendDoubleHeight($c, false);
    appendBold($c, false);

    appendAlign($c, 0); // Kembali Left
    appendRow2Col($c, "No. Telp :", $order['customer_phone'] ?? '-', $colWidth);
    appendSeparator($c, '-', $colWidth);

    // 6. Daftar Layanan/Item
    appendText($c, "LAYANAN:\n");
    foreach ($items as $item) {
        // Nama Produk Tebal
        appendBold($c, true);
        appendText($c, $item['product_name'] . "\n");
        appendBold($c, false);

        // Kuantitas x Harga = Subtotal
        $qtyText = number_format($item['quantity'], 1) . ' ' . ($item['unit'] ?? 'kg');
        $priceText = 'Rp ' . number_format($item['unit_price'] ?? ($item['price'] ?? 0), 0, ',', '.');
        $subtotalText = 'Rp ' . number_format($item['subtotal'] ?? 0, 0, ',', '.');

        $left = "  " . $qtyText . " x " . $priceText;
        appendRow2Col($c, $left, $subtotalText, $colWidth);
    }

    appendSeparator($c, '-', $colWidth);

    // 7. Total Belanja (Double Height, Bold)
    appendBold($c, true);
    appendDoubleHeight($c, true);
    $totalAmount = 'Rp ' . number_format($order['grand_total'] ?? $order['total_amount'], 0, ',', '.');
    appendRow2Col($c, "TOTAL:", $totalAmount, $colWidth);
    appendDoubleHeight($c, false);
    appendBold($c, false);

    appendSeparator($c, '=', $colWidth);

    // 8. Footer & Ucapan
    appendAlign($c, 1); // Center
    appendText($c, "Terima Kasih Atas\n");
    appendText($c, "Kepercayaan Anda!\n\n");

    // 9. Feed & Auto-Cut
    $c[] = 0x0A;
    $c[] = 0x0A;
    $c[] = 0x1D; $c[] = 0x56; $c[] = 0x42; $c[] = 0x00; // GS V 66 0 (Cut)

    return $c;
}

/**
 * Konversi Gambar Logo ke ESC * 33 (Anti Garis Putih & Tepi Tajam)
 */
function appendOptimizedLogo(&$c, $imagePath, $targetWidth = 280) {
    $info = @getimagesize($imagePath);
    if (!$info) return;

    $mime = $info['mime'];
    if ($mime === 'image/png') {
        $src = @imagecreatefrompng($imagePath);
    } elseif ($mime === 'image/jpeg') {
        $src = @imagecreatefromjpeg($imagePath);
    } elseif ($mime === 'image/bmp' || $mime === 'image/x-ms-bmp') {
        $src = @imagecreatefrombmp($imagePath);
    } else {
        return;
    }

    if (!$src) return;

    $srcW = imagesx($src);
    $srcH = imagesy($src);

    // Hitung perbandingan aspek
    $ratio = $targetWidth / $srcW;
    $targetHeight = intval(ceil(($srcH * $ratio) / 24) * 24); // Wajib kelipatan 24

    // Buat canvas putih
    $canvas = imagecreatetruecolor($targetWidth, $targetHeight);
    $white = imagecolorallocate($canvas, 255, 255, 255);
    imagefilledrectangle($canvas, 0, 0, $targetWidth, $targetHeight, $white);

    // Salin gambar di tengah kanvas
    $scaledH = intval($srcH * $ratio);
    $offsetY = intval(($targetHeight - $scaledH) / 2);
    imagecopyresampled($canvas, $src, 0, $offsetY, 0, 0, $targetWidth, $scaledH, $srcW, $srcH);
    imagedestroy($src);

    // Grayscale + Peningkatan Kontras
    imagefilter($canvas, IMG_FILTER_GRAYSCALE);
    imagefilter($canvas, IMG_FILTER_CONTRAST, -20); // -20 di PHP = menaikkan kontras

    // KRUSIAL: Set Line Spacing ke 24 dots (Mencegah garis horisontal putih)
    $c[] = 0x1B; $c[] = 0x33; $c[] = 24; // ESC 3 24
    appendAlign($c, 1); // Center logo

    // Iterasi strip vertikal 24-dot
    for ($y = 0; $y < $targetHeight; $y += 24) {
        $c[] = 0x1B; $c[] = 0x2A; $c[] = 33; // ESC * 33 (24-dot double density)
        $c[] = $targetWidth & 0xFF;         // nL (Low byte)
        $c[] = ($targetWidth >> 8) & 0xFF;  // nH (High byte)

        for ($x = 0; $x < $targetWidth; $x++) {
            for ($k = 0; $k < 3; $k++) {
                $byte = 0;
                for ($bit = 0; $bit < 8; $bit++) {
                    $py = $y + ($k * 8) + $bit;
                    if ($py < $targetHeight) {
                        $rgb = imagecolorat($canvas, $x, $py);
                        $gray = $rgb & 0xFF;
                        // Ambang threshold 140
                        if ($gray < 140) {
                            $byte |= (0x80 >> $bit);
                        }
                    }
                }
                $c[] = $byte;
            }
        }
        $c[] = 0x0A; // Feed 1 strip
    }

    imagedestroy($canvas);

    // Reset Line Spacing ke Default
    $c[] = 0x1B; $c[] = 0x32; // ESC 2
}

// Helper Functions
function appendText(&$c, $str) {
    $ascii = iconv('UTF-8', 'ASCII//TRANSLIT//IGNORE', $str);
    for ($i = 0; $i < strlen($ascii); $i++) {
        $c[] = ord($ascii[$i]);
    }
}

function appendAlign(&$c, $mode) {
    // 0: Left, 1: Center, 2: Right
    $c[] = 0x1B; $c[] = 0x61; $c[] = $mode;
}

function appendBold(&$c, $on) {
    $c[] = 0x1B; $c[] = 0x45; $c[] = ($on ? 1 : 0);
}

function appendDoubleSize(&$c, $on) {
    $c[] = 0x1D; $c[] = 0x21; $c[] = ($on ? 0x11 : 0x00);
}

function appendDoubleHeight(&$c, $on) {
    $c[] = 0x1D; $c[] = 0x21; $c[] = ($on ? 0x01 : 0x00);
}

function appendSeparator(&$c, $char = '-', $width = 32) {
    appendText($c, str_repeat($char, $width) . "\n");
}

function appendRow2Col(&$c, $left, $right, $width = 32) {
    $leftLen = strlen($left);
    $rightLen = strlen($right);
    $spaces = $width - ($leftLen + $rightLen);
    if ($spaces < 1) $spaces = 1;
    appendText($c, $left . str_repeat(' ', $spaces) . $right . "\n");
}
```

---

### 6.2 Frontend JavaScript Client (`bluetooth-print.js`)

Berikut adalah script client yang di-load di halaman detail order (`order-detail.php` / kasir):

```javascript
/**
 * Rodeo Laundry - Bluetooth Print Bridge Client
 */
class PrintBridgeClient {
    constructor() {
        this.bridgeUrl = 'http://localhost:8080';
        this.timeoutMs = 12000;
    }

    /**
     * Cek status kesiapan printer bridge
     */
    async checkBridgeStatus() {
        try {
            const controller = new AbortController();
            const timer = setTimeout(() => controller.abort(), 2500);

            const res = await fetch(`${this.bridgeUrl}/status`, {
                method: 'GET',
                signal: controller.signal
            });
            clearTimeout(timer);

            if (!res.ok) return false;
            const data = await res.json();
            return data.status === 'online';
        } catch (e) {
            console.warn('Bridge offline atau terblokir:', e.message);
            return false;
        }
    }

    /**
     * Kirim perintah cetak berdasarkan Order ID
     */
    async printOrder(orderId) {
        if (!orderId) {
            throw new Error("Order ID tidak valid");
        }

        // 1. Ambil data raw ESC/POS dari PHP Server (Same-Origin)
        console.log(`[Print] Mengambil data ESC/POS untuk Order #${orderId}...`);
        const apiResponse = await fetch(`/api/get-escpos-data.php?id=${orderId}`, {
            method: 'GET',
            headers: { 'Accept': 'application/json' }
        });

        if (!apiResponse.ok) {
            const errText = await apiResponse.text();
            throw new Error(`Gagal membuat data struk di server: ${errText}`);
        }

        const data = await apiResponse.json();
        if (!data.success || !Array.isArray(data.escpos)) {
            throw new Error(data.error || "Format data ESC/POS tidak valid");
        }

        console.log(`[Print] ESC/POS berhasil dibuat. Ukuran: ${data.escpos.length} bytes. Mengirim ke bridge...`);

        // 2. Kirim ke Bridge lokal via HTTP POST
        const payload = {
            escpos: data.escpos,
            order_id: orderId,
            timestamp: Date.now()
        };

        const controller = new AbortController();
        const timer = setTimeout(() => controller.abort(), this.timeoutMs);

        try {
            const bridgeResponse = await fetch(`${this.bridgeUrl}/print`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify(payload),
                signal: controller.signal
            });
            clearTimeout(timer);

            if (!bridgeResponse.ok) {
                const bridgeErr = await bridgeResponse.text();
                throw new Error(`Bridge Error (${bridgeResponse.status}): ${bridgeErr}`);
            }

            const result = await bridgeResponse.json();
            if (!result.success) {
                throw new Error(result.message || "Printer gagal mencetak");
            }

            return result;
        } catch (fetchErr) {
            if (fetchErr.name === 'AbortError') {
                throw new Error("Timeout: Print Bridge tidak merespons dalam 12 detik. Periksa koneksi Bluetooth.");
            }
            if (fetchErr.message.includes('Failed to fetch') || fetchErr.message.includes('NetworkError')) {
                throw new Error(
                    "Tidak dapat terhubung ke Aplikasi Print Bridge.\n\n" +
                    "Pastikan:\n" +
                    "1. Aplikasi 'Rodeo Print Bridge' di Android sedang AKTIF.\n" +
                    "2. Status printer menunjukkan 'Connected'.\n" +
                    "3. Anda membuka web kasir dari perangkat yang SAMA dengan aplikasi bridge."
                );
            }
            throw fetchErr;
        }
    }
}

// Inisialisasi Global
window.printBridge = new PrintBridgeClient();

// Fungsi Trigger untuk Tombol di UI
async function handlePrintReceipt(orderId, btnElement = null) {
    let originalText = '';
    if (btnElement) {
        originalText = btnElement.innerHTML;
        btnElement.disabled = true;
        btnElement.innerHTML = '⏳ Mencetak...';
    }

    try {
        const res = await window.printBridge.printOrder(orderId);
        if (typeof Swal !== 'undefined') {
            Swal.fire({
                icon: 'success',
                title: 'Berhasil Dicetak',
                text: res.message || 'Nota telah diteruskan ke thermal printer.',
                timer: 2000,
                showConfirmButton: false
            });
        } else {
            alert('✅ Struk berhasil dicetak!');
        }
    } catch (err) {
        console.error('[Print Error]', err);
        if (typeof Swal !== 'undefined') {
            Swal.fire({
                icon: 'error',
                title: 'Gagal Mencetak',
                text: err.message
            });
        } else {
            alert('❌ ' + err.message);
        }
    } finally {
        if (btnElement) {
            btnElement.disabled = false;
            btnElement.innerHTML = originalText;
        }
    }
}
```

---

## 7. Checklist Kerja Pengembang (Action Plan)

Gunakan checklist ini sebagai panduan langkah demi langkah bagi tim pengembang:

### 👨‍💻 Sisi Backend (PHP)
- [ ] Buat / Perbarui endpoint `api/get-escpos-data.php` dengan fungsi `buildReceiptESCPOS()`.
- [ ] Siapkan file logo berlatar belakang putih solid (resolusi 280 x 120 px) di `assets/images/logo-for-printer-fix-1.bmp`.
- [ ] Pastikan fungsi binarisasi menyetel `ESC 3 24` sebelum strip dan `ESC 2` setelah selesai.
- [ ] Lakukan uji mandiri endpoint melalui browser: `https://domain/api/get-escpos-data.php?id=1`. Pastikan output JSON valid dan array `escpos` berisi deretan angka integer bytes `[27, 64, ...]`.

### 📱 Sisi Bridge App (Android)
- [ ] Tambahkan header `Access-Control-Allow-Private-Network: true` pada `PrintWebServer.kt`.
- [ ] Ubah metode pengiriman output stream Bluetooth menggunakan **Buffer Chunking (512 bytes + sleep 25ms)**.
- [ ] Bungkus koneksi dalam `Foreground Service` agar server port 8080 tetap responsif saat layar HP mati.
- [ ] Tambahkan tombol "Test Print ESC/POS" langsung di UI aplikasi Android untuk memudahkan diagnosa mandiri tanpa web.

### 🌐 Sisi Frontend POS (Web / PWA)
- [ ] Pasang script `assets/js/bluetooth-print.js` pada halaman nota (`pos/order-detail.php`, `pos/print-receipt.php`, dsb).
- [ ] Pasang event listener pada tombol cetak: `<button onclick="handlePrintReceipt(123, this)">🖨️ Cetak Struk</button>`.
- [ ] Tambahkan indikator status visual (misal icon printer hijau/merah) yang mengecek `window.printBridge.checkBridgeStatus()` saat halaman dimuat.

---

## 8. Matriks Troubleshooting Masalah Umum

| Gejala Masalah | Penyebab Utama | Solusi Pasti |
| :--- | :--- | :--- |
| **"Failed to fetch" saat klik cetak di website HTTPS** | Aturan Mixed Content atau Private Network Access browser memblokir request ke `http://localhost:8080`. | 1. Tambahkan header PNA di bridge.<br>2. Gunakan `bridge-proxy.html` atau WebView wrapper dengan `MIXED_CONTENT_ALWAYS_ALLOW`.<br>3. Buka web via `http://` lokal jika dalam jaringan lokal. |
| **Printer mencetak kertas kosong panjang** | Perintah `ESC @` (Reset) tidak terpanggil atau gambar logo corrupt/kosong. | Pastikan `0x1B, 0x40` selalu berada di indeks paling pertama dan file gambar logo benar-benar terbaca oleh fungsi GD PHP. |
| **Muncul garis-garis putih horisontal membelah logo** | Line spacing printer default (30-33 dots) lebih besar daripada tinggi strip gambar (24 dots). | Pasang perintah `1B 33 18` (`ESC 3 24`) tepat sebelum iterasi gambar, lalu kembalikan dengan `1B 32` (`ESC 2`) setelah selesai. |
| **Printer nge-hang / mencetak karakter aneh saat ada logo** | Buffer overflow pada microcontroller printer thermal akibat pengiriman data gambar besar sekaligus. | Terapkan **Buffer Chunking** di Android: kirim data maksimal 512 bytes per paket dengan jeda `Thread.sleep(25)`. |
| **Teks struk terlihat tebal/bloated dan melipat tidak rapi** | Jumlah karakter melebihi kapasitas kolom printer (32 kolom untuk 58mm). | Gunakan fungsi pembatas baris (`appendRow2Col`) dengan batas kolom maksimal 32 karakter. Gunakan Font A ukuran normal. |
| **Koneksi Bluetooth sering putus sendiri** | Sistem baterai Android mematikan proses di background (*Doze Mode*). | Jalankan bridge sebagai **Foreground Service** dengan notifikasi permanen dan kecualikan dari *Battery Optimization*. |
| **Pemotong kertas (cutter) tidak merespons** | Printer tidak memiliki hardware cutter fisik atau kode cut salah. | Printer tipe 58mm (seperti Xprinter XP58IIZ) umumnya adalah *tear-bar manual* (tanpa auto-cutter elektrik). Berikan 3x feed line (`0x0A, 0x0A, 0x0A`) agar teks terakhir melewati pisau sobek. |

---

## 💡 Ringkasan Inti untuk Pengembang

1. **Jadikan Web/PHP sebagai Pusat Desain Struk**: Jangan menggambar struk di Android. Biarkan PHP menghasilkan array byte ESC/POS murni.
2. **Kunci Teks Bersih**: Gunakan font native printer (`ESC M 0`, `ESC ! 0`), bukan gambar canvas HTML.
3. **Kunci Logo Bersih**: Ubah background transparan menjadi putih, gunakan ambang thresholding kontras 140, potong per kelipatan 24 dots, dan atur line spacing ke 24 dots (`ESC 3 24`).
4. **Kunci Keandalan Hardware**: Selalu pecah data pengiriman Bluetooth menjadi potongan 512 bytes agar chip printer tidak crash.

