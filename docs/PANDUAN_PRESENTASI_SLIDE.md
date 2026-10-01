# 📊 PANDUAN PRESENTASI SLIDE BERKAT DINASTI

**Nama Proyek:** Rancangan Database Aplikasi Manajemen Pesanan Roti & Pelanggan (Berkat Dinasti / ForHomie)  
**Durasi Presentasi:** ~15-20 menit  
**Total Slide:** 10-12 slide

---

## SLIDE 1: JUDUL & IDENTITAS

**Teks Slide:**
```
RANCANGAN DATABASE APLIKASI MANAJEMEN PESANAN ROTI
& PELANGGAN (BERKAT DINASTI)

Tim Pengembang:
- [Nama Ketua] - NIM
- [Nama Anggota 1] - NIM
- [Nama Anggota 2] - NIM
- [Nama Anggota 3] - NIM
- [Nama Anggota 4] - NIM

Dosen Pembimbing: [Nama]
Tanggal: [Tanggal Presentasi]
```

**Penjelasan:**
Slide pertama adalah pengenalan proyek dan tim. Fungsinya untuk memberi "first impression" profesional dan memperkenalkan siapa yang mengerjakan.

**Narasi (10 detik):**
"Assalamualaikum, saya dari tim yang mengerjakan proyek Rancangan Database Aplikasi Berkat Dinasti. Hari ini kami akan mempersentasikan hasil kerja yang fokus pada sistem manajemen pesanan roti dan pelanggan. Mari kita mulai."

---

## SLIDE 2: PROFIL MITRA

**Teks Slide:**
```
PROFIL MITRA: BERKAT DINASTI

Bidang Usaha:
• Produsen dan penjual roti pre-order (sistem PO)
• Produk: Roti Hajatan (6 rasa), Roti Kopi, Roti Bijian

Lokasi Operasional:
• Wilayah Malang Raya dan Batu
• Pengiriman gratis ke zona layanan

Kapasitas:
• ~150 pcs roti besar per trip pengiriman
• Metode pembayaran: Cash & Transfer

Tantangan Utama yang Dihadapi:
• Pencatatan manual (buku/nota)
• Tidak ada database pelanggan
• Order via WhatsApp (tidak terrekap otomatis)
• Tidak ada invoice digital
• Status pengiriman sulit dilacak
```

**Penjelasan:**
Slide ini menjelaskan siapa mitra dan konteks bisnis mereka. Penting untuk dosen/audiens memahami masalah yang nyata di lapangan.

**Narasi (20-25 detik):**
"Berkat Dinasti adalah usaha roti yang beroperasi di wilayah Malang Raya dengan sistem pre-order. Mereka memproduksi 3 jenis roti utama dan kapasitas pengiriman sekitar 150 pcs roti per trip. Meski usaha ini sudah berjalan, tantangan utama mereka masih pada pencatatan manual, tidak ada database pelanggan yang terstruktur, dan order yang hanya via WhatsApp tidak punya rekap otomatis. Karena itu, proyek ini hadir untuk solusi tersebut."

---

## SLIDE 3: RUMUSAN MASALAH

**Teks Slide:**
```
RUMUSAN MASALAH

1. Bagaimana data operasional mitra dapat dikelola 
   secara terstruktur dalam database?

2. Bagaimana sistem dapat membantu tracking pesanan 
   dan pembayaran secara otomatis?

3. Bagaimana sistem dapat mengurangi kesalahan 
   dan mempermudah laporan operasional?
```

**Penjelasan:**
Rumusan masalah adalah jantung dari proyek. Ini menunjukkan bahwa kita sudah mengidentifikasi apa yang perlu dipecahkan dan kenapa penting untuk dipecahkan.

**Narasi (15 detik):**
"Berdasarkan observasi, kami merumuskan 3 pertanyaan utama: pertama, bagaimana data operasional dapat dikelola terstruktur; kedua, bagaimana sistem dapat melakukan tracking otomatis; dan ketiga, bagaimana mengurangi kesalahan serta mempermudah laporan. Ketiga pertanyaan ini menjadi panduan kami dalam merancang database."

---

## SLIDE 4: TUJUAN PENELITIAN

**Teks Slide:**
```
TUJUAN PENELITIAN

1. Merancang basis data terstruktur untuk mengelola 
   data pelanggan, produk, pesanan, pembayaran, 
   dan pengiriman

2. Memudahkan mitra dalam tracking pesanan 
   dan pembayaran secara akurat dan konsisten

3. Mengurangi kesalahan input, duplikasi data, 
   dan ketidaksesuaian stok

4. Mengotomatisasi proses invoice, tracking log, 
   dan transaksi kas

5. Menyediakan laporan yang mudah dan akurat 
   untuk pengambilan keputusan
```

**Penjelasan:**
Tujuan adalah jawaban dari rumusan masalah. Tujuan yang jelas menunjukkan bahwa proyek ini punya arah dan ukuran kesuksesan yang jelas.

**Narasi (20 detik):**
"Untuk menjawab rumusan masalah tadi, kami menetapkan 5 tujuan utama: pertama, merancang basis data yang terstruktur; kedua, memudahkan tracking; ketiga, mengurangi kesalahan; keempat, mengotomatisasi proses; dan kelima, menyediakan laporan yang akurat. Kelima tujuan ini akan kami validasi di akhir presentasi."

---

## SLIDE 5: ALUR PROSES BISNIS END-TO-END

**Teks Slide:**
```
ALUR PROSES BISNIS BERKAT DINASTI

┌─────────────────────────────────────┐
│  1. PESANAN MASUK                   │
│  Pelanggan memesan via WA/Direct    │
└──────────────┬──────────────────────┘
               │
┌──────────────▼──────────────────────┐
│  2. INPUT PESANAN & CEK STOK        │
│  • Admin input data pesanan         │
│  • Sistem validasi stok otomatis    │
└──────────────┬──────────────────────┘
               │
        ┌──────┴──────┐
        │ Stok Cukup? │
        └──────┬──────┘
       Ya /    │    \ Tidak
           ┌───▼────┐
           │ REVISI │
           └────────┘
               │
┌──────────────▼──────────────────────┐
│  3. HITUNG TOTAL & BUAT INVOICE     │
│  • Total harga + ongkir otomatis   │
│  • Invoice #dibuat auto (INV-...)  │
└──────────────┬──────────────────────┘
               │
┌──────────────▼──────────────────────┐
│  4. PRODUKSI & PENGIRIMAN           │
│  • Status: Pending → Proses → Kirim│
│  • Tracking tercatat di sistem      │
└──────────────┬──────────────────────┘
               │
┌──────────────▼──────────────────────┐
│  5. PEMBAYARAN (DP/Cicilan/Lunas)  │
│  • Validasi pembayaran otomatis    │
│  • Status bayar diupdate: DP/Lunas │
└──────────────┬──────────────────────┘
               │
┌──────────────▼──────────────────────┐
│  6. LAPORAN & KAS                   │
│  • Transaksi masuk ke kas otomatis  │
│  • Laporan penjualan & piutang      │
└─────────────────────────────────────┘
```

**Penjelasan:**
Alur ini menunjukkan perjalanan data dari awal (pesanan) sampai akhir (laporan). Setiap tahap memiliki validasi dan otomatisasi yang mengurangi kesalahan manual.

**Narasi (30 detik):**
"Proses dimulai dari pesanan yang masuk dari pelanggan. Admin mencatat pesanan dan sistem otomatis mengecek stok. Jika cukup, sistem menghitung total dan membuat nomor invoice. Pesanan kemudian diproses dan dikirim dengan status yang tercatat. Untuk pembayaran, mitra bisa menerima DP atau cicilan, dan sistem akan validasi agar tidak overpayment. Terakhir, semua transaksi otomatis masuk ke kas dan laporan, sehingga mitra bisa melihat ringkasan penjualan dan piutang dengan mudah."

---

## SLIDE 6: ENTITY RELATIONSHIP DIAGRAM (ERD) & PENJELASAN

**Teks Slide:**
```
ENTITAS UTAMA DALAM DATABASE

┌────────────────┐
│    PELANGGAN   │
├────────────────┤
│ • id_pelanggan │
│ • nama         │
│ • no_wa        │
│ • tipe         │
│ • total_hutang │
└────────────────┘
        │
        │ memiliki
        │
┌───────▼────────────┐
│   PESANAN          │
├────────────────────┤
│ • id_pesanan       │
│ • no_invoice (auto)│
│ • tgl_pesan        │
│ • grand_total      │
│ • status           │
│ • status_bayar     │
└────────────────────┘
    ├── terdiri dari
    │
┌───▼─────────────────┐
│  DETAIL_PESANAN     │
├─────────────────────┤
│ • id_detail         │
│ • id_varian         │
│ • qty               │
│ • subtotal          │
└─────────────────────┘
        │
        │ merujuk ke
        │
┌───────▼──────────────┐
│  VARIAN_PRODUK       │
├──────────────────────┤
│ • id_varian          │
│ • nama_varian        │
│ • harga              │
│ • stok (berkurang)   │
└──────────────────────┘

Entitas Lainnya:
• PEMBAYARAN (track pembayaran bertahap)
• PENGIRIMAN (track status kirim)
• TRACKING_LOG (rekam setiap perubahan status)
• TRANSAKSI_KAS (pencatatan kas otomatis)
```

**Penjelasan:**
ERD menunjukkan bagaimana data saling terhubung. Relasi ini memastikan integritas data dan memudahkan query laporan.

**Penjelasan Singkat Entitas 8-16:**

**Entitas 8 - Pembayaran:** Mencatat pembayaran DP/cicilan dari pelanggan dengan validasi agar tidak overpayment.

**Entitas 9 - Pengiriman:** Melacak status pengiriman dan memberikan transparansi kepada pelanggan.

**Entitas 10 - Tracking Log:** Riwayat audit setiap perubahan status pesanan dan pembayaran.

**Entitas 11 - Pengguna:** Akun staff dengan role berbeda (admin, kasir, driver) untuk kontrol akses.

**Entitas 12 - Pengeluaran:** Mencatat biaya operasional yang otomatis masuk ke transaksi kas.

**Entitas 13 - Transaksi Kas:** Buku kas digital yang merekam semua arus uang masuk dan keluar.

**Entitas 14 - Setting:** Konfigurasi sistem agar fleksibel dan mudah disesuaikan.

**Entitas 15 - Relasi 1N:** Relasi induk-anak yang menjamin data terorganisir tanpa duplikasi.

**Entitas 16 - Relasi 1I:** Relasi satu-ke-satu antara pesanan dan pengiriman untuk konsistensi tracking.

**Narasi Singkat (15-20 detik):**
"Database kami punya 16 entitas terintegrasi. Entitas inti: Pelanggan, Pesanan, Varian. Pendukung: Pembayaran, Pengiriman, Tracking Log. Finansial: Pengeluaran, Kas. Sistem: Pengguna, Setting. Semua terhubung dengan relasi 1N dan 1I yang menjamin integritas dan konsistensi data penuh."

---

## SLIDE 7: FITUR OTOMATISASI & VALIDASI

**Teks Slide:**
```
FITUR OTOMATISASI & VALIDASI

✓ AUTO-GENERATE INVOICE
  Format: INV-YYYYMMDD-XXXX (otomatis saat order dibuat)

✓ VALIDASI STOK
  • Cek stok saat add item ke pesanan
  • Tolak jika stok kurang

✓ HITUNG OTOMATIS
  • Total harga = SUM(qty × harga_satuan)
  • Grand total = total_harga + ongkir

✓ STATUS PESANAN BERUNTUN
  Pending → Proses → Kirim → Selesai (atau Batal)
  • Validasi transisi status
  • Tidak boleh lompat status

✓ PEMBAYARAN BERTAHAP
  • Validasi: jangan bayar lebih dari grand_total
  • Status bayar otomatis: belum_bayar → dp → lunas

✓ TRACKING LOG OTOMATIS
  • Setiap perubahan status tercatat
  • Audit trail untuk laporan

✓ TRANSAKSI KAS OTOMATIS
  • Pembayaran masuk → kas masuk
  • Pengeluaran → kas keluar
  • Refund → penyesuaian kas
```

**Penjelasan:**
Fitur ini adalah "jantung" dari sistem yang membedakan database manual dari terstruktur. Otomatisasi mengurangi kesalahan manusia.

**Narasi (30 detik):**
"Sistem kami punya 7 fitur otomatisasi utama. Pertama, nomor invoice dibuat otomatis saat pesanan baru dibuat. Kedua, stok divalidasi saat item ditambah, sehingga tidak bisa oversell. Ketiga, total harga dan ongkir dihitung otomatis. Keempat, status pesanan harus beruntun, tidak boleh lompat status. Kelima, pembayaran divalidasi agar tidak ada overpayment. Keenam, setiap perubahan status tercatat di tracking log untuk audit. Ketujuh, transaksi kas otomatis terupdate dari pembayaran dan pengeluaran. Semua ini mengurangi pekerjaan manual dan risiko kesalahan."

---

## SLIDE 8: SKENARIO PENGUJIAN & HASIL

**Teks Slide:**
```
SKENARIO PENGUJIAN DATABASE

SKENARIO 1: PESANAN NORMAL (Lunas)
├─ Input: Pelanggan order 2 varian, qty 10
├─ Expected: Stok berkurang, invoice terbuat, total hitung benar
└─ Hasil: ✓ BERHASIL

SKENARIO 2: STOK TIDAK CUKUP
├─ Input: Pelanggan order qty lebih dari stok
├─ Expected: Sistem tolak order, stok tidak berkurang
└─ Hasil: ✓ BERHASIL (Error: Stok tidak mencukupi)

SKENARIO 3: PEMBAYARAN BERTAHAP (DP)
├─ Input: Order Rp 100.000, bayar DP Rp 50.000
├─ Expected: Status bayar = dp, sisa hutang Rp 50.000
└─ Hasil: ✓ BERHASIL

SKENARIO 4: UPDATE STATUS PESANAN
├─ Input: Status pending → proses → kirim → selesai
├─ Expected: Tracking log tercatat, status valid
└─ Hasil: ✓ BERHASIL

SKENARIO 5: PEMBATALAN PESANAN
├─ Input: Pesanan status pending → batal
├─ Expected: Stok direstore, kas diupdate
└─ Hasil: ✓ BERHASIL

KESIMPULAN PENGUJIAN:
Semua skenario berjalan sesuai expektasi.
Database valid dan siap digunakan.
```

**Penjelasan:**
Pengujian menunjukkan bahwa database tidak hanya "bagus di teori", tapi juga "berfungsi di praktik". Ini adalah bukti nyata bahwa solusi valid.

**Narasi (30 detik):**
"Kami melakukan 5 skenario pengujian untuk memvalidasi sistem. Pertama, pesanan normal dengan pembayaran lunas berhasil terekam dengan benar. Kedua, ketika stok tidak cukup, sistem menolak order dan stok tetap aman. Ketiga, pembayaran DP tercatat dengan status bayar = dp, dan sisa hutang otomatis dihitung. Keempat, transisi status pesanan berjalan sesuai alur, dan tracking log tercatat. Kelima, pembatalan pesanan berhasil restore stok dan update kas. Semua skenario berjalan sesuai ekspektasi, membuktikan database valid dan siap digunakan."

---

## SLIDE 9: DAMPAK SEBELUM vs SESUDAH

**Teks Slide:**
```
DAMPAK IMPLEMENTASI: SEBELUM vs SESUDAH

╔════════════════════╦════════════════════╗
║     SEBELUM        ║     SESUDAH        ║
╠════════════════════╬════════════════════╣
║ Pencatatan manual  ║ Terstruktur di DB  ║
║ Rawan duplikasi    ║ Integritas terjamin║
║ Stok sulit tracking║ Stok real-time     ║
║ Invoice manual     ║ Auto-generate      ║
║ Invoice manual     ║ Auto-generate      ║
║ Hutang tidak jelas ║ Piutang terukur    ║
║ Laporan manual/tdk ║ Laporan otomatis   ║
║ Status pesanan?    ║ Tracking lengkap   ║
╚════════════════════╩════════════════════╝

INDIKATOR KEBERHASILAN:
✓ Waktu input pesanan berkurang
✓ Risiko kesalahan input minimal
✓ Laporan dapat digenerate instant
✓ Piutang dapat dipantau dengan mudah
✓ Status pesanan transparan untuk pelanggan
```

**Penjelasan:**
Slide ini menunjukkan ROI (Return on Investment) dari proyek. Ini menjawab pertanyaan "kenapa penting proyek ini?"

**Narasi (25 detik):**
"Dengan sistem yang baru, mitra mengalami perubahan signifikan. Sebelumnya pencatatan manual dan rawan duplikasi, sekarang terstruktur di database dengan integritas terjamin. Stok yang sulit dilacak kini bisa dipantau real-time. Invoice yang dulu manual sekarang otomatis dibuat. Hutang yang tidak jelas kini piutang terukur. Laporan yang dulu manual atau tidak ada, sekarang bisa otomatis. Terakhir, status pesanan yang sebelumnya tidak transparan, sekarang pelanggan bisa tracking lengkap. Ini adalah dampak nyata dari implementasi sistem."

---

## SLIDE 10: KESIMPULAN

**Teks Slide:**
```
KESIMPULAN

1. Sistem basis data Berkat Dinasti berhasil dirancang 
   untuk mengelola data pelanggan, produk, pesanan, 
   pembayaran, dan pengiriman secara terstruktur.

2. Penerapan relasi tabel, validasi stok, tracking 
   status, dan otomatisasi membuat operasional lebih 
   rapi, akurat, dan efisien.

3. Sistem memudahkan mitra dalam monitoring transaksi, 
   piutang, serta penyusunan laporan penjualan dan kas.

4. Database yang dibangun menjadi solusi konkret untuk 
   menggantikan pencatatan manual yang selama ini rawan 
   kesalahan.

5. Rekomendasi: Lanjutkan dengan implementasi di server 
   dan training untuk user akhir.
```

**Penjelasan:**
Kesimpulan meringkas hasil dan validasi bahwa tujuan sudah tercapai. Ini adalah "closing statement" yang kuat.

**Narasi (20 detik):**
"Melalui penelitian ini, kami telah merancang database yang tidak hanya terstruktur, tetapi juga terintegrasi penuh dengan operasional Berkat Dinasti. Dengan relasi tabel yang tepat, validasi data yang ketat, dan otomatisasi proses, sistem ini memberikan solusi nyata untuk menggantikan pencatatan manual yang selama ini rawan kesalahan. Hasilnya, mitra mendapatkan kemudahan dalam tracking pesanan, monitoring piutang, dan penyusunan laporan. Kami yakin database ini siap implementasi dan merekomendasikan untuk segera dijalankan bersama pelatihan pengguna. Terima kasih."

---

## SLIDE 11: SARAN & PENGEMBANGAN LANJUTAN (OPSIONAL)

**Teks Slide:**
```
PENGEMBANGAN LANJUTAN (FUTURE)

FASE 2 - FITUR TAMBAHAN:
✓ Dashboard analytics (grafik penjualan, trend)
✓ Mobile app untuk customer tracking pesanan
✓ Integrasi WhatsApp Bot untuk order automation
✓ Email/SMS notifikasi pembayaran & pengiriman
✓ Sistem role-based (admin, kasir, driver)
✓ Backup & recovery otomatis

FASE 3 - INTEGRASI LANJUTAN:
✓ Integrasi payment gateway (Stripe, GCash)
✓ Laporan pajak otomatis
✓ Prediksi demand dengan ML
✓ Multi-lokasi support
```

**Penjelasan:**
Slide ini menunjukkan bahwa proyek tidak "selesai", tapi punya roadmap jangka panjang. Ini meninggalkan kesan profesional dan strategis.

**Narasi (15 detik):**
"Untuk pengembangan ke depan, kami punya roadmap Fase 2 dan Fase 3. Fase 2 fokus pada dashboard, mobile app, dan notifikasi. Fase 3 akan mengintegrasikan payment gateway dan machine learning untuk prediksi demand. Ini adalah visi jangka panjang untuk membuat Berkat Dinasti semakin kompetitif."

---

## SLIDE 12: TERIMA KASIH & Q&A

**Teks Slide:**
```
TERIMA KASIH

Pertanyaan & Saran?

Kontak:
📧 Email: [email tim]
📱 WhatsApp: [nomor]

Dokumentasi Lengkap:
📄 GitHub: [link repo]
🔗 Database: [link hosting]
📊 Presentasi: [link slide]
```

**Penjelasan:**
Slide terakhir untuk menutup presentasi dengan baik dan membuka diskusi.

**Narasi (10 detik):**
"Terima kasih atas perhatian dan waktu Anda. Kami siap menjawab pertanyaan dan menerima saran untuk perbaikan. Silakan hubungi kami melalui kontak yang tersedia di slide ini."

---

## 📝 TIPS PRESENTASI

### Sebelum Presentasi:
- [ ] Pastikan urutan slide lancar
- [ ] Latih narasi 2-3x hingga smooth
- [ ] Siapkan backup (USB + cloud)
- [ ] Test koneksi internet jika ada link
- [ ] Siapkan contoh screenshot/demo jika perlu

### Saat Presentasi:
- [ ] Berbicara jelas dan perlahan
- [ ] Jaga kontak mata dengan audiens
- [ ] Gunakan laser pointer untuk highlight
- [ ] Pauses untuk beri waktu audiens mencerna
- [ ] Ready untuk improvisasi jika ada pertanyaan

### Tips Menjawab Pertanyaan:
**Pertanyaan Teknis:**
- Jelaskan dengan sederhana, jangan jargon berlebihan
- Gunakan contoh konkret dari use case mitra

**Pertanyaan Desain:**
- Jelaskan "kenapa" di balik setiap keputusan
- Tunjuk ke slide ERD atau skenario pengujian

**Pertanyaan Implementasi:**
- Jujur jika belum tahu, tapi tawarkan untuk follow-up
- Jangan membuat janji yang tidak bisa dipenuhi

---

**Good Luck dengan Presentasi! 🎤✨**
