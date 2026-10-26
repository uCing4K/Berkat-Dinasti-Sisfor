# 📝 Catatan Perubahan & History (Changelog) - Berkat Dinasti

Seluruh perubahan rancangan, pembaruan fitur, serta revisi dokumentasi proyek **Sistem Informasi Berkat Dinasti** dicatat secara berkala di file ini.

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
