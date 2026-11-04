# 📦 Spesifikasi Fitur & Workflow Modul - Berkat Dinasti

Dokumen ini menjelaskan alur kerja fungsional (*workflow*), fitur-fitur utama, serta format data pada aplikasi **Sistem Informasi Berkat Dinasti**.

---

## 1. Peta Situs & Modul Utama (Site Map)

Aplikasi dibangun sebagai **Dashboard Internal Admin/Pemilik** (tanpa portal customer publik). Menu utama terbagi menjadi 7 modul:

1. **Dashboard (Beranda Utama)**
   - KPI Cards: Total Pesanan Hari Ini, Pesanan Pending, Total Pemasukan Bulan Ini, Pelanggan Baru.
   - Quick Action CTA: `+ Tambah Pesanan Baru`.
   - Widget Pesanan Perlu Tindakan & Ringkasan Dapur.
2. **Katalog Produk (`/produk`)**
   - KPI Cards Varian & Status Ketersediaan.
   - Tabel Master Produk & Varian Kemasan (Kardus, Mika, Satuan) + Minimum Order (MOQ).
   - Modal Tambah/Edit Produk.
3. **Manajemen Pesanan (`/pesanan`)**
   - Kanban Board 4 Swimlanes: `Menunggu Konfirmasi` ➔ `Proses Dapur` ➔ `Siap Kirim` ➔ `Selesai`.
   - Form Modal Input Pesanan dengan Customer Lookup & Auto Invoice Generator.
4. **Kalender Pengiriman (`/kalender`)**
   - Visualisasi Grid Kalender Pengiriman Harian/Bulanan.
   - Filter tanggal pesanan terdekat untuk pencegahan terlewatnya *deadline*.
5. **Manajemen Pelanggan / CRM (`/pelanggan`)**
   - Daftar Pelanggan (Retail vs Reseller) dengan saldo kewajiban `total_hutang`.
   - Tautan langsung WhatsApp & Riwayat Transaksi.
6. **Laporan & Keuangan (`/laporan`)**
   - Filter Periode (Bulanan & Custom Date Range).
   - Cards Ringkasan Omset, Completion Rate, Donut Chart Status Pembayaran (Lunas/Hutang).
   - Ekspor Laporan ke format PDF / Excel.
7. **Pengaturan & Manajemen User (`/pengaturan`)**
   - Profil UMKM & Uploader Logo.
   - POS Thermal Simulator (Preview Struk 80mm).
   - Manajemen User (Tambah Admin/Staf, Reset Password, Hapus User).

---

## 2. Format Penomoran & Aturan Data

### A. Generator Invoice Otomatis
Setiap pesanan baru akan secara otomatis mendapatkan nomor invoice unik dengan format standar:
$$\text{INV-} \mathbf{YYYYMMDD} \text{-} \mathbf{XXXX}$$
*Contoh:* `INV-20261001-0001` (Tanggal 1 Oktober 2026, urutan ke-1 hari itu).

### B. Kebijakan Pengiriman & Obrok
- **Bebas Ongkir:** Seluruh area pengiriman Kota Malang & Kota Batu disetel Rp 0 (tanpa zonasi).
- **Kapasitas Armada:** Sepeda motor obrok dibatasi **maksimal 150 pcs roti besar per perjalanan (trip)**.

### C. Pilihan 9 Topping Resmi Roti Hajatan
Pada pesanan produk Roti Hajatan (isi 7 kelopak), admin/staf kasir dapat memilih varian topping resmi:
1. `Coco Chip + Keju`
2. `Coco Chip + Wijen`
3. `Meses + Keju`
4. `Meses + Wijen`
5. `Wijen + Keju`
6. `Meses + Sprinkle`
7. `Coco Chip + Sprinkle`
8. `Wijen`
9. `Wijen + Sprinkle`
*(Serta opsi `Campur / Mix (Aneka Topping)` dan `Tanpa Topping (Polos / Original)`).*

Topping tersimpan pada kolom `detail_pesanan.topping`, tampil interaktif pada keranjang POS, tabel pesanan, kartu Kanban, modal detail pesanan, serta tercetak otomatis pada struk kasir Bluetooth printer thermal ESC/POS (`* Top: <nama_topping>`).

---

## 3. Alur Kerja Pengguna (User Flows)

### Flow 1: Input Pesanan Baru
```mermaid
sequenceDiagram
    autonumber
    actor Admin as Admin / Staf
    participant UI as Dashboard UI
    participant System as Core Engine
    participant DB as Database (D2/D3)

    Admin->>UI: Klik "+ Tambah Pesanan Baru"
    UI->>Admin: Tampilkan Modal Form Pesanan
    Admin->>UI: Input Customer Lookup / Tambah Pelanggan
    Admin->>UI: Pilih Varian Roti & Kuantitas (Validasi MOQ)
    Admin->>UI: Tentukan Tanggal Kirim & DP/Metode Bayar
    Admin->>UI: Klik "Simpan Pesanan"
    UI->>System: Submit Form Data
    System->>System: Generate Nomor INV-YYYYMMDD-XXXX
    System->>DB: Insert Data (pesanan, detail_pesanan, pembayaran)
    DB-->>UI: Sukses Simpan
    UI-->>Admin: Pesanan Masuk ke Swimlane "Menunggu Konfirmasi"
```

### Flow 2: Pemantauan Deadline Pengiriman
1. Admin membuka menu **Kalender Pengiriman**.
2. Tanggal dengan antrean pesanan aktif di-highlight dengan indikator jumlah pcs.
3. Admin mengklik tanggal tertentu untuk melihat daftar rincian pesanan yang harus dikirim hari itu.
4. Admin dapat langsung memperbarui status pengiriman dari kalender.

---
*Dokumen ini merupakan panduan spesifikasi fitur aplikasi Berkat Dinasti.*
