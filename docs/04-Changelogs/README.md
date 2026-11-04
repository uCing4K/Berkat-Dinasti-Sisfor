# 📝 Catatan Perubahan & History (Changelog) - Berkat Dinasti

Seluruh perubahan rancangan, pembaruan fitur, serta revisi dokumentasi proyek **Sistem Informasi Berkat Dinasti** dicatat secara berkala di file ini.

---

## [v1.2.0] - 2026-11-04

### 🧁 Fitur Pemilihan 9 Topping Roti Hajatan & Integrasi Cetak Struk Thermal ESC/POS
- **Integrasi 9 Varian Topping Resmi (Sesuai Foto Katalog Fisik Mitra):**
  1. `Coco Chip + Keju`
  2. `Coco Chip + Wijen`
  3. `Meses + Keju`
  4. `Meses + Wijen`
  5. `Wijen + Keju`
  6. `Meses + Sprinkle`
  7. `Coco Chip + Sprinkle`
  8. `Wijen`
  9. `Wijen + Sprinkle`
  *(Disertai opsi fleksibel: `Campur / Mix (Aneka Topping)` dan `Tanpa Topping (Polos / Original)`).*
- **Perubahan Skema Database & Migrasi Idempoten:**
  - Penambahan kolom `topping VARCHAR(150) NULL AFTER id_varian` pada tabel `detail_pesanan`.
  - Pembaruan view `v_detail_pesanan_produk` dengan menyertakan field `dp.topping`.
  - Pembuatan file migrasi SQL `database/migration_add_topping.sql`.
  - Pembaruan file baseline `database/berkat_dinasti_final.sql` dan `database/rodd1157_berkat_dinasti_db.sql`.
  - Mekanisme **self-healing migration** otomatis pada backend PHP (`pesanan.php` & `beranda.php`) untuk kompatibilitas server live hosting cPanel.
- **Frontend & Antarmuka POS Interaktif:**
  - **Modal Pop-up Visual Foto Katalog Topping** (`toppingCatalogModal`) yang menampilkan foto katalog fisik (`public/assets/images/9_pilihan_topping_roti_hajatan.jpg`) dan daftar 9 nomor topping.
  - **Modal Pemilih Topping Interaktif POS** (`toppingPickerModal`) dengan tombol nomor varian, catatan topping khusus, dan kontrol kuantitas.
  - **Papan POS & Keranjang Belanja (`public/pesanan.php` & `public/beranda.php`)**:
    - Kartu produk Roti Hajatan dilengkapi penanda varian topping dan tombol cepat foto katalog.
    - Keranjang belanja menampilkan badge topping, tombol ubah topping langsung, dan validasi MOQ per varian.
    - Mendukung pesanan hajatan dengan berbagai kombinasi topping berbeda dalam satu invoice.
  - **Modal Form Pesanan Tradisional**: Menambahkan kolom `Pilihan Topping` dengan select dropdown dinamis.
  - **Master Produk (`public/produk.php`)**: Menampilkan badge dan tombol pop-up foto katalog 9 topping pada tabel master dan kartu mobile.
- **Pencetakan Struk Kasir Thermal Bluetooth ESC/POS (`public/api/get-escpos-data.php`):**
  - Mesin ESC/POS secara otomatis menyertakan baris topping (`  * Top: <nama_topping>`) di bawah nama produk saat dicetak ke printer Bluetooth thermal 58mm / 80mm agar bagian produksi dapur dan packing tidak salah varian.
- **Agregasi Data Laporan & Pelanggan:**
  - Pembaruan query subquery agregasi di `pesanan.php`, `beranda.php`, `laporan.php`, dan `pelanggan.php` agar ringkasan item menyertakan label `[Top: ...]`.

---

## [v1.1.0] - 2026-10-26

### 🍞 Sinkronisasi Katalog Master Produk & Pricelist Resmi Berkat Dinasti
- **Penyelarasan Data Master Produk & Varian Kemasan 1:1:**
  - **Roti Hajatan (9 Pilihan Topping):**
    - Topping: Coco Chip + Keju, Coco Chip + Wijen, Meses + Keju, Meses + Wijen, Wijen + Keju, Meses + Sprinkle, Coco Chip + Sprinkle, Wijen, Wijen + Sprinkle.
    - Kemasan: Kardus Putih (Rp 10.000), Kardus Coklat (Rp 10.000), Kardus Batik (Rp 11.000), Mika (Rp 11.000).
  - **Bolu Premium:**
    - Bolu Pisang: Kemasan Kardus Coklat (Rp 21.000), Kemasan Mika (Rp 22.000).
    - Bolu Tanpa Pisang: Kemasan Kardus Putih (Rp 19.000), Kemasan Mika (Rp 20.000).
  - **Roti Manis:**
    - Roti Kopi: Satuan Rp 4.000 (Min. Order 50 pcs).
    - Roti Bijian Besar: Satuan Rp 4.000 (Min. Order 20 pcs).
    - Roti Bijian Kecil: Satuan Rp 3.000 (Min. Order 50 pcs).
- **Pembaruan Database:**
  - Pembaruan skema baseline di `database/berkat_dinasti_final.sql` dan `database/rodd1157_berkat_dinasti_db.sql`.
  - Pembuatan skrip migrasi idempotensial di `database/migration_update.sql`.
- **Penyelarasan Frontend & Controller:**
  - `public/produk.php`: Sinkronisasi katalog, helper kemasan `getBerkatPackagingMeta()`, filter kategori dinamis, dan auto-sync database.
  - `public/beranda.php`: Modal pesanan, kalkulator real-time, audit log, dan ekspor CSV disinkronkan ke produk resmi.
  - `public/pesanan.php`: Modal form pesanan baru, validasi MOQ, papan Kanban, dan kartu rincian kirim.
  - `public/pelanggan.php`: Riwayat pesanan dan preferensi kemasan pelanggan.
  - `public/laporan.php`: Rekap riwayat transaksi dan omset.
  - `public/pengaturan.php`: Preview struk kasir termal.
- **Arsip Bukti Dokumen Pricelist:**
  - Dokumentasi foto fisik disimpan di `docs/DATA/pricelist/`.

---

## [v1.0.0-final] - 2026-10-01

### 🚀 Perilisan Rancangan Final (Disetujui Mitra & Tim)
- **Restrukturisasi Dokumentasi Proyek:**
  - Pemisahan dokumen rancangan mentah (`docs/DATA/`) dan rancangan final yang disetujui (`docs/Rancangan Final/`).
  - Pembuatan 4 folder standar dokumentasi terstruktur:
    - `docs/01-Architecture/` (DFD Level 0-2, Kamus Data Store T1-T13, RBAC Matrix).
    - `docs/02-Features/` (Spesifikasi Fitur, Site Map, Auto Invoice `INV-YYYYMMDD-XXXX`, User Flows).
    - `docs/03-UI-UX-Guidelines/` (Panduan UI/UX, Font Inter, Hasil Usability Testing, Link Figma Prototype).
    - `docs/04-Changelogs/` (Catatan rilis ini).
- **Finalisasi Arsitektur & DFD:**
  - Penetapan 7 proses utama dan 13 tabel database fisik (`D1` s/d `D7`).
  - Kebijakan **Gratis Ongkir** untuk wilayah Kota Malang & Kota Batu tanpa pembagian zonasi tarif.
  - Pembatasan muatan armada sepeda motor obrok (maksimal 150 pcs/trip).
- **Finalisasi UI/UX:**
  - Penyesuaian antarmuka khusus **Internal Admin Dashboard** (tanpa website customer publik).
  - Penyederhanaan papan status pesanan dan penambahan kalender deadline pengiriman harian.

---
*Changelog ini akan di-update seiring pengembangan kode aplikasi.*
