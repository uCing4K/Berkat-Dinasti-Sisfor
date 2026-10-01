# Mermaid Diagrams — SISFOR BERKAT DINASTI
## Section 4.3 – 4.8

---

## 4.3 Site Map

```mermaid
mindmap
  root((SISFOR
    BERKAT DINASTI))
    (🔐 LOGIN)
      (Form Login)
      (Toggle Password)
      (Ingat Saya)
      (Lupa Password)
        (Reset Password)
    (🏠 BERANDA)
      (KPI Cards)
        (Total Pesanan Hari Ini)
        (Pesanan Pending)
        (Total Pemasukan Bulan Ini)
        (Pelanggan Baru)
      (Pesanan Perlu Tindakan)
      (Kitchen Widget)
        (Siap Kirim)
        (Dalam Proses)
        (Menunggu Dapur)
      (Inventory Alert)
      (Quick Actions)
        (Unduh Rekap Harian)
        (Tambah Pesanan Baru)
    (📦 PRODUK)
      (KPI Ketersediaan)
      (Filter Kategori & Status)
      (Tabel Produk)
      (Modal Tambah/Edit Produk)
    (📋 PESANAN)
      (Header Ringkasan)
      (Menunggu Konfirmasi)
      (Proses Dapur / Produksi)
      (Siap Kirim / Diambil)
      (Selesai)
      (Modal Tambah Pesanan)
        (Customer Lookup)
        (MOQ Validation)
    (📅 KALENDER PENGIRIMAN)
      (Grid Kalender Bulanan)
      (Jadwal Produksi Harian)
      (Rute Kurir)
      (Highlight Tanggal Aktif)
    (👥 PELANGGAN)
      (Customer List)
        (VIP Member)
        (Mitra Reseller)
        (Pelanggan Reguler)
      (Detail Panel)
        (Tombol WhatsApp)
        (Tombol Order Baru)
        (Ringkasan Transaksi)
      (Sub-tabs)
        (Riwayat Pesanan)
        (Info Kontak)
    (📊 LAPORAN & KEUANGAN)
      (Filter Periode)
      (Cards Ringkasan)
        (Total Pemasukan)
        (Total Pesanan)
        (Completion Rate)
      (Bar Chart Tren Penjualan)
      (Donut Chart Status Bayar)
      (Unduh Excel / Cetak)
    (⚙️ PENGATURAN)
      (Profil UMKM)
        (Info Toko & Logo)
        (WhatsApp Business)
      (POS Thermal Simulator)
        (Preview Struk 80mm)
        (QRIS Mandiri)
      (Manajemen User & Akses)
        (Daftar Pengguna)
        (Perbandingan Hak Akses)
```

---

## 4.4 User Flow — Tambah Pesanan

```mermaid
flowchart TD
    A([🔐 Login]) --> B[🏠 Dashboard / Beranda]
    B --> C[Klik '+ Tambah Pesanan Baru']
    C --> D[/Modal Form Pesanan Muncul/]

    D --> E[Customer Lookup\ncari / pilih pelanggan]
    E --> F[Pilih Produk & Varian Roti]
    F --> G{Validasi MOQ\nReal-time}

    G -- MOQ Terpenuhi --> H[Tentukan Tanggal Pengiriman]
    G -- MOQ Tidak Terpenuhi --> G2[⚠️ Tampil Warning MOQ]
    G2 --> F

    H --> I[Masukkan Info Pembayaran]
    I --> J[Klik Simpan]
    J --> K[(Pesanan Tersimpan\nke Database)]

    K --> L[📋 Kanban Board\nSwimlane: Menunggu Konfirmasi]
    L --> M{Admin Konfirmasi?}

    M -- Ya --> N[📋 Proses Dapur / Produksi]
    M -- Tidak --> O[❌ Batalkan Pesanan]

    N --> P{Produksi Selesai?}
    P -- Ya --> Q[📋 Siap Kirim / Diambil]
    P -- Belum --> N

    Q --> R{Pesanan Terkirim?}
    R -- Ya --> S[✅ Selesai]
    R -- Gagal --> T[🔄 Coba Kirim Ulang]
    T --> Q

    style A fill:#2B231F,color:#fff
    style S fill:#22c55e,color:#fff
    style O fill:#ef4444,color:#fff
    style G2 fill:#f59e0b,color:#fff
    style L fill:#F08223,color:#fff
    style N fill:#F08223,color:#fff
    style Q fill:#F08223,color:#fff
```

---

## 4.5 User Flow — Cek Deadline Pengiriman

```mermaid
flowchart TD
    A([🏠 Dashboard / Beranda]) --> B[Klik Menu 'Kalender' di Sidebar]
    B --> C[📅 Tampil Grid Kalender Bulanan]

    C --> D{Pilih Tanggal}
    D -- Tanggal dengan pesanan --> E[📋 Daftar Pesanan\npada Tanggal Tersebut]
    D -- Tanggal kosong --> F[ℹ️ Tidak ada pesanan\npada tanggal ini]
    F --> D

    E --> G{Aksi Admin}
    G -- Buka Detail --> H[📄 Detail Pesanan]
    G -- Kembali ke Kalender --> C

    H --> I{Update Diperlukan?}
    I -- Ya --> J[🔄 Update Status Pesanan]
    J --> K[✅ Perubahan Tersimpan]
    K --> C
    I -- Tidak --> C

    style A fill:#2B231F,color:#fff
    style C fill:#F08223,color:#fff
    style K fill:#22c55e,color:#fff
    style F fill:#94a3b8,color:#fff
```

---

## 4.6 User Flow — Cek Pemasukan & Laporan

```mermaid
flowchart TD
    A([🏠 Dashboard / Beranda]) --> B[Klik Menu 'Laporan' di Sidebar]
    B --> C[📊 Halaman Laporan & Keuangan]

    C --> D{Pilih Filter Periode}
    D -- Bulanan --> E[Tampil Data Bulan Berjalan]
    D -- Date Range --> F[Tampil Data Rentang Tanggal]

    E --> G
    F --> G

    G[📈 Sistem Menampilkan Ringkasan]
    G --> G1[🟦 Cards: Total Pemasukan\n+ Total Pesanan + Completion Rate]
    G --> G2[📊 Bar Chart:\nTren Penjualan Bulanan]
    G --> G3[🍩 Donut Chart:\nStatus Pembayaran\n75% Lunas - 16.7% DP - 8.3% Belum Bayar]

    G1 --> H{Aksi Admin}
    G2 --> H
    G3 --> H

    H -- Unduh Excel --> I[⬇️ Download File .xlsx]
    H -- Cetak Laporan --> J[🖨️ Print Preview]
    H -- Buka Transaksi --> K[📄 Detail Transaksi / Pesanan]
    H -- Filter Ulang --> D

    I --> L([✅ Selesai])
    J --> L
    K --> L

    style A fill:#2B231F,color:#fff
    style C fill:#F08223,color:#fff
    style L fill:#22c55e,color:#fff
    style G1 fill:#3b82f6,color:#fff
    style G2 fill:#8b5cf6,color:#fff
    style G3 fill:#f59e0b,color:#fff
```

---

## 4.7 User Flow — Kelola Pelanggan

```mermaid
flowchart TD
    A([🏠 Dashboard / Beranda]) --> B[Klik Menu 'Pelanggan' di Sidebar]
    B --> C[👥 Daftar Pelanggan CRM]

    C --> D{Filter Tipe Pelanggan}
    D -- VIP Member --> E1[🌟 Daftar VIP Member]
    D -- Mitra Reseller --> E2[🏪 Daftar Mitra Reseller]
    D -- Reguler --> E3[👤 Daftar Pelanggan Reguler]
    D -- Semua --> E4[📋 Semua Pelanggan]

    E1 --> F
    E2 --> F
    E3 --> F
    E4 --> F

    F[Klik Salah Satu Pelanggan]
    F --> G[/Detail Panel Pelanggan Muncul/]

    G --> G1[ℹ️ Info Pelanggan\nNama, Alamat, No. WA, Tipe]
    G --> G2[📊 Ringkasan Transaksi]

    G --> H{Aksi Admin}
    H -- WhatsApp --> I[💬 Buka WhatsApp\nKontak Langsung]
    H -- Order Baru --> J[📋 Modal Tambah Pesanan\ndengan Pelanggan Ini]
    H -- Riwayat Pesanan --> K[📄 Tab: Riwayat Pesanan\nTanggal, Invoice, Total, Status]
    H -- Info Kontak --> L[📞 Tab: Info Kontak Lengkap]
    H -- Kembali --> C

    J --> M[✅ Pesanan Baru Tersimpan]
    M --> C

    style A fill:#2B231F,color:#fff
    style C fill:#F08223,color:#fff
    style I fill:#22c55e,color:#fff
    style M fill:#22c55e,color:#fff
    style E1 fill:#f59e0b,color:#fff
    style E2 fill:#3b82f6,color:#fff
```

---

## 4.8 Wireflow — Navigasi Antar Halaman

```mermaid
flowchart LR
    LOGIN([🔐 LOGIN]) -->|Login berhasil| DASHBOARD

    subgraph MAIN ["🏠 BERANDA (Dashboard)"]
        DASHBOARD[Dashboard\nKPI + Quick Actions]
    end

    DASHBOARD -->|Klik + Tambah Pesanan| MODAL_PESANAN
    DASHBOARD -->|Klik card pesanan| DETAIL_PESANAN
    DASHBOARD -->|Sidebar: Produk| PRODUK
    DASHBOARD -->|Sidebar: Pesanan| KANBAN
    DASHBOARD -->|Sidebar: Kalender| KALENDER
    DASHBOARD -->|Sidebar: Pelanggan| PELANGGAN
    DASHBOARD -->|Sidebar: Laporan| LAPORAN
    DASHBOARD -->|Sidebar: Pengaturan| PENGATURAN

    subgraph PESANAN_MODULE ["📋 MODUL PESANAN"]
        KANBAN[Kanban Board\n4 Swimlane]
        MODAL_PESANAN[/Modal Tambah\nPesanan/]
        DETAIL_PESANAN[Detail Pesanan]
        MODAL_PESANAN -->|Simpan| KANBAN
        KANBAN -->|Klik card| DETAIL_PESANAN
    end

    subgraph PRODUK_MODULE ["📦 MODUL PRODUK"]
        PRODUK[Tabel Produk\n+ KPI]
        MODAL_PRODUK[/Modal Tambah\nProduk/]
        PRODUK -->|Klik + Tambah Produk| MODAL_PRODUK
    end

    subgraph KALENDER_MODULE ["📅 MODUL KALENDER"]
        KALENDER[Grid Kalender\nBulanan]
        KALENDER -->|Klik tanggal aktif| DETAIL_PESANAN
    end

    subgraph PELANGGAN_MODULE ["👥 MODUL PELANGGAN"]
        PELANGGAN[Daftar Pelanggan\nVIP / Reseller / Reguler]
        DETAIL_PELANGGAN[Detail Panel\nPelanggan]
        PELANGGAN -->|Klik pelanggan| DETAIL_PELANGGAN
    end

    subgraph LAPORAN_MODULE ["📊 MODUL LAPORAN"]
        LAPORAN[Laporan & Keuangan\nFilter + Charts]
        LAPORAN -->|Unduh Excel / Cetak| EXPORT[⬇️ Download / Print]
    end

    subgraph PENGATURAN_MODULE ["⚙️ MODUL PENGATURAN"]
        PENGATURAN[Pengaturan]
        PROFIL[Profil UMKM]
        THERMAL[POS Thermal\nSimulator]
        USERS[Manajemen\nUser & Akses]
        PENGATURAN --> PROFIL
        PENGATURAN --> THERMAL
        PENGATURAN --> USERS
    end

    style LOGIN fill:#2B231F,color:#fff
    style DASHBOARD fill:#F08223,color:#fff
    style KANBAN fill:#F08223,color:#fff
    style EXPORT fill:#22c55e,color:#fff
    style MODAL_PESANAN fill:#f59e0b,color:#fff
    style MODAL_PRODUK fill:#f59e0b,color:#fff
```
