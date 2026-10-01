# 🎨 KUMPULAN KODE LENGKAP DIAGRAM DFD (MERMAID.JS)
## Sistem Informasi Manajemen Pesanan Roti — Berkat Dinasti

File ini berisi seluruh kode diagram Mermaid dari **Bab 1 sampai Bab 4**.
Tinggal copy kode blok yang kamu inginkan, lalu paste ke **[mermaid.live](https://mermaid.live)** -> Klik **Actions** -> **Copy Image** atau **Download PNG**.

---

# 📑 DAFTAR ISI DIAGRAM:
1. **BAB 1:** [Diagram 1.1] DFD Level 0 — Context Diagram
2. **BAB 2:** [Diagram 2.1] DFD Level 1 — Overview Diagram (7 Proses Utama & Data Store)
3. **BAB 3 (BAGIAN 1):**
   - [Diagram 3.1] DFD Level 2 — Proses 1.0 (Manajemen Katalog Produk)
   - [Diagram 3.2] DFD Level 2 — Proses 2.0 (Manajemen Pelanggan)
   - [Diagram 3.3] DFD Level 2 — Proses 3.0 (Manajemen Pesanan)
   - [Diagram 3.3B] Siklus Hidup Status Pesanan
4. **BAB 3 (BAGIAN 2):**
   - [Diagram 3.4] DFD Level 2 — Proses 4.0 (Manajemen Pembayaran)
   - [Diagram 3.5] DFD Level 2 — Proses 5.0 (Manajemen Pengiriman)
   - [Diagram 3.6] DFD Level 2 — Proses 6.0 (Pelaporan Keuangan)
   - [Diagram 3.7] DFD Level 2 — Proses 7.0 (Manajemen Pengguna)
5. **BAB 4:**
   - [Diagram 4.1] Ringkasan Arus Data Utama Sistem (Global Summary)

---

## 📌 BAB 1 — DIAGRAM 1.1: DFD LEVEL 0 (CONTEXT DIAGRAM)
> **Untuk:** Bab 1 Pendahuluan & Context Diagram

```mermaid
flowchart TD
    classDef entity fill:#E3F2FD,stroke:#1565C0,stroke-width:2px,color:#0D47A1,font-weight:bold;
    classDef process fill:#FFF8E1,stroke:#F57F17,stroke-width:3px,color:#E65100,font-weight:bold;

    PELANGGAN["👤 PELANGGAN<br/>(Retail & Reseller)"]:::entity
    ADMIN["👑 ADMIN / PEMILIK"]:::entity
    STAFF["👨‍💼 STAFF OPERASIONAL"]:::entity

    SISTEM(["📊 SISTEM INFORMASI<br/>MANAJEMEN PESANAN ROTI<br/>'BERKAT DINASTI'<br/><i>(Free Ongkir Malang Raya)</i>"]):::process

    PELANGGAN -->|"1. Info Pesanan, Data Diri,<br/>Konfirmasi Bayar"| SISTEM
    SISTEM -->|"2. Invoice Digital (INV-...),<br/>Status Order, Katalog & Struk"| PELANGGAN

    ADMIN -->|"3. Data Produk & Varian Harga,<br/>Jadwal Kirim, Update Pengeluaran"| SISTEM
    SISTEM -->|"4. Laporan Penjualan, Rekap Piutang,<br/>Arus Kas & Kalender Produksi"| ADMIN

    STAFF -->|"5. Input Pesanan Baru, Catat Bayar<br/>(Cash/Transfer), Update Status"| SISTEM
    SISTEM -->|"6. Lembar Invoice, Daftar Tagihan,<br/>Jadwal Kirim Harian"| STAFF
```

---

## 📌 BAB 2 — DIAGRAM 2.1: DFD LEVEL 1 (OVERVIEW DIAGRAM)
> **Untuk:** Bab 2 Overview Diagram & Kamus Data Store

```mermaid
flowchart TB
    classDef entity fill:#E3F2FD,stroke:#1565C0,stroke-width:2px,color:#0D47A1,font-weight:bold;
    classDef process fill:#FFF8E1,stroke:#F57F17,stroke-width:2px,color:#E65100,font-weight:bold;
    classDef datastore fill:#E8F5E9,stroke:#2E7D32,stroke-width:2px,color:#1B5E20,font-weight:bold;

    PELANGGAN["👤 PELANGGAN"]:::entity
    ADMIN["👑 ADMIN / PEMILIK"]:::entity
    STAFF["👨‍💼 STAFF"]:::entity

    P1(["1.0<br/>Manajemen<br/>Katalog Produk"]):::process
    P2(["2.0<br/>Manajemen<br/>Pelanggan"]):::process
    P3(["3.0<br/>Manajemen<br/>Pesanan"]):::process
    P4(["4.0<br/>Manajemen<br/>Pembayaran"]):::process
    P5(["5.0<br/>Manajemen<br/>Pengiriman"]):::process
    P6(["6.0<br/>Pelaporan<br/>Keuangan"]):::process
    P7(["7.0<br/>Manajemen<br/>Pengguna"]):::process

    D1[("💾 D1: Produk & Varian")]:::datastore
    D2[("💾 D2: Pelanggan (Tanpa Zona)")]:::datastore
    D3[("💾 D3: Pesanan & Detail")]:::datastore
    D4[("💾 D4: Pembayaran & Tracking")]:::datastore
    D5[("💾 D5: Pengiriman (Free Ongkir)")]:::datastore
    D6[("💾 D6: Kas & Pengeluaran")]:::datastore
    D7[("💾 D7: Akun Pengguna")]:::datastore

    PELANGGAN -.->|Lihat Katalog| P1
    PELANGGAN -->|Data Diri| P2
    PELANGGAN -->|Order Roti| P3
    PELANGGAN -->|Bukti Bayar| P4

    P3 -->|Invoice INV-...| PELANGGAN
    P4 -->|Struk Digital WA| PELANGGAN
    P5 -->|Status Sampai| PELANGGAN

    P1 <---> D1
    P2 <---> D2
    P3 <---> D3
    P4 <---> D4
    P5 <---> D5
    P6 <---> D6
    P7 <---> D7

    P3 -->|Total Tagihan| P4
    P3 -->|Jadwal Kirim| P5
    P4 -->|Kas Masuk| P6

    ADMIN -->|Kelola Produk| P1
    ADMIN -->|Kelola User| P7
    P6 -->|Laporan Omset & Piutang| ADMIN

    STAFF -->|Input Pesanan| P3
    STAFF -->|Input Bayar| P4
    STAFF -->|Update Kurir| P5
```

---

## 📌 BAB 3 (BAGIAN 1) — DIAGRAM 3.1: DFD LEVEL 2 (PROSES 1.0 KATALOG PRODUK)
> **Untuk:** Bab 3 Sub-bab 1 — Manajemen Katalog Produk

```mermaid
flowchart TD
    classDef entity fill:#E3F2FD,stroke:#1565C0,stroke-width:2px,color:#0D47A1;
    classDef process fill:#FFF8E1,stroke:#F57F17,stroke-width:2px,color:#E65100;
    classDef datastore fill:#E8F5E9,stroke:#2E7D32,stroke-width:2px,color:#1B5E20;

    ADMIN["👑 ADMIN / PEMILIK"]:::entity
    CUST["👤 PELANGGAN"]:::entity

    P11(["1.1 Kelola Kategori Produk"]):::process
    P12(["1.2 Kelola Data Produk<br/>(Foto, Shelf Life, Deskripsi)"]):::process
    P13(["1.3 Kelola Varian & Harga<br/>(Kardus / Mika / Satuan)"]):::process
    P14(["1.4 Kelola Status Ketersediaan<br/>(Tersedia / Habis)"]):::process

    D11[("💾 D1.1: Kategori")]:::datastore
    D12[("💾 D1.2: Produk")]:::datastore
    D13[("💾 D1.3: Varian Produk")]:::datastore

    ADMIN -->|Input Kategori| P11
    P11 <---> D11

    P11 -->|Kategori Valid| P12
    ADMIN -->|Input Data Roti| P12
    P12 <---> D12

    P12 -->|ID Produk| P13
    ADMIN -->|Harga Kemasan Kardus/Mika| P13
    P13 <---> D13

    ADMIN -->|Update Status Stok| P14
    P14 -->|Update Status| D12

    P14 -->|Katalog Produk Aktif & Harga| CUST
```

---

## 📌 BAB 3 (BAGIAN 1) — DIAGRAM 3.2: DFD LEVEL 2 (PROSES 2.0 MANAJEMEN PELANGGAN)
> **Untuk:** Bab 3 Sub-bab 2 — Manajemen Pelanggan (Retail/Reseller Tanpa Zona)

```mermaid
flowchart TD
    classDef entity fill:#E3F2FD,stroke:#1565C0,stroke-width:2px,color:#0D47A1;
    classDef process fill:#FFF8E1,stroke:#F57F17,stroke-width:2px,color:#E65100;
    classDef datastore fill:#E8F5E9,stroke:#2E7D32,stroke-width:2px,color:#1B5E20;

    ADMIN["👑 ADMIN / STAFF"]:::entity

    P21(["2.1 Registrasi & Input Data Pelanggan<br/>(Nama, WA, Alamat Lengkap)"]):::process
    P22(["2.2 Klasifikasi Pelanggan<br/>(Retail / Reseller)"]):::process
    P23(["2.3 Lihat Riwayat Pesanan"]):::process
    P24(["2.4 Tracking Piutang Pelanggan<br/>(Status Lunas / Hutang)"]):::process

    D2[("💾 D2: Pelanggan (Tanpa Zona)")]:::datastore
    D3[("💾 D3: Pesanan")]:::datastore
    D4[("💾 D4: Pembayaran")]:::datastore

    ADMIN -->|Input Data Pelanggan| P21
    P21 <---> D2

    P21 --> P22
    ADMIN -->|Set Tipe Akun| P22
    P22 -->|Update Tipe| D2

    ADMIN -->|Cari ID Pelanggan| P23
    D3 -->|Tarik Histori Pesanan| P23
    P23 -->|Tampilkan Histori Order| ADMIN

    ADMIN -->|Cek Tagihan Piutang| P24
    D4 -->|Data Pembayaran| P24
    P24 -->|Update Total Hutang| D2
    P24 -->|Daftar Piutang Aktif| ADMIN
```

---

## 📌 BAB 3 (BAGIAN 1) — DIAGRAM 3.3: DFD LEVEL 2 (PROSES 3.0 MANAJEMEN PESANAN)
> **Untuk:** Bab 3 Sub-bab 3 — Manajemen Pesanan & Auto Invoice

```mermaid
flowchart TD
    classDef entity fill:#E3F2FD,stroke:#1565C0,stroke-width:2px,color:#0D47A1;
    classDef process fill:#FFF8E1,stroke:#F57F17,stroke-width:2px,color:#E65100;
    classDef datastore fill:#E8F5E9,stroke:#2E7D32,stroke-width:2px,color:#1B5E20;

    USER["👨‍💼 ADMIN / STAFF"]:::entity
    CUST["👤 PELANGGAN"]:::entity

    P31(["3.1 Input Pesanan Baru"]):::process
    P32(["3.2 Auto-Generate Invoice<br/>(INV-YYYYMMDD-XXXX)"]):::process
    P33(["3.3 Input Detail Item<br/>(Hitung Subtotal Otomatis)"]):::process
    P34(["3.4 Kelola Status Pesanan<br/>(Pending -> Selesai)"]):::process
    P35(["3.5 Kalender Produksi & Kirim"]):::process

    D1[("💾 D1.3: Varian & Harga")]:::datastore
    D2[("💾 D2: Pelanggan")]:::datastore
    D3[("💾 D3: Pesanan")]:::datastore
    D3Detail[("💾 D3.2: Detail Pesanan")]:::datastore
    D4Log[("💾 D4: Tracking Log")]:::datastore

    USER -->|Input Data Order| P31
    D2 -->|Data Profil Pemesan| P31
    P31 -->|Simpan Header Pesanan| D3
    P31 --> P32

    P32 -->|Nomor Invoice| D3
    P32 -->|Kirim Invoice Digital| CUST

    P32 --> P33
    D1 -->|Harga Kemasan Satuan| P33
    P33 -->|Simpan Item & Qty| D3Detail
    P33 -->|Update Grand Total| D3

    USER -->|Update Status Tahapan| P34
    P34 -->|Update Status Pesanan| D3
    P34 -->|Rekam Audit Trail| D4Log
    P34 -->|Notifikasi Status| CUST

    D3 -->|Jadwal Kirim Roti| P35
    P35 -->|Tampilan Kalender Kerja| USER
```

---

## 📌 BAB 3 (BAGIAN 1) — DIAGRAM 3.3B: ALUR STATUS PESANAN (STATE FLOW)
> **Untuk:** Bab 3 — Diagram Alur Siklus Hidup Pesanan

```mermaid
stateDiagram-v2
    [*] --> PENDING: Pesanan Masuk
    PENDING --> PROSES: Dikonfirmasi & Masuk Antrean Dapur
    PROSES --> KIRIM: Roti Matang Siap Kirim (Obrok)
    KIRIM --> SELESAI: Diterima Pelanggan & Pembayaran Tuntas
    PROSES --> BATAL: Dibatalkan Pelanggan / Stok Bahan Habis
    KIRIM --> BATAL: Gagal Kirim / Ditolak
    SELESAI --> [*]
    BATAL --> [*]
```

---

## 📌 BAB 3 (BAGIAN 2) — DIAGRAM 3.4: DFD LEVEL 2 (PROSES 4.0 MANAJEMEN PEMBAYARAN)
> **Untuk:** Bab 3 Sub-bab 4 — Manajemen Pembayaran (Lunas / Hutang)

```mermaid
flowchart TD
    classDef entity fill:#E3F2FD,stroke:#1565C0,stroke-width:2px,color:#0D47A1;
    classDef process fill:#FFF8E1,stroke:#F57F17,stroke-width:2px,color:#E65100;
    classDef datastore fill:#E8F5E9,stroke:#2E7D32,stroke-width:2px,color:#1B5E20;

    USER["👨‍💼 ADMIN / STAFF"]:::entity
    CUST["👤 PELANGGAN"]:::entity

    P41(["4.1 Catat Pembayaran<br/>(Cash / Transfer)"]):::process
    P42(["4.2 Update Status Bayar Otomatis<br/>(Lunas / Hutang)"]):::process
    P43(["4.3 Update Piutang Pelanggan"]):::process
    P44(["4.4 Cetak Struk Digital<br/>(Share ke WhatsApp)"]):::process

    D3[("💾 D3: Pesanan (Grand Total)")]:::datastore
    D4[("💾 D4: Pembayaran")]:::datastore
    D2[("💾 D2: Pelanggan (Total Hutang)")]:::datastore
    D6[("💾 D6: Transaksi Kas")]:::datastore

    USER -->|Input Jumlah & Bukti Bayar| P41
    D3 -->|Ambil Nilai Grand Total| P41
    P41 -->|Simpan Data Pembayaran| D4
    P41 --> P42

    P42 -->|Set Status 'Lunas' atau 'Hutang'| D3
    P42 --> P43

    P43 -->|Hitung & Simpan Sisa Tagihan| D2
    P43 --> P44

    P44 -->|Struk / Nota Digital| CUST
    P41 -->|Catat Arus Kas Masuk| D6
```

---

## 📌 BAB 3 (BAGIAN 2) — DIAGRAM 3.5: DFD LEVEL 2 (PROSES 5.0 MANAJEMEN PENGIRIMAN)
> **Untuk:** Bab 3 Sub-bab 5 — Manajemen Pengiriman (Free Ongkir Malang Raya, Obrok 150 pcs)

```mermaid
flowchart TD
    classDef entity fill:#E3F2FD,stroke:#1565C0,stroke-width:2px,color:#0D47A1;
    classDef process fill:#FFF8E1,stroke:#F57F17,stroke-width:2px,color:#E65100;
    classDef datastore fill:#E8F5E9,stroke:#2E7D32,stroke-width:2px,color:#1B5E20;

    USER["👨‍💼 ADMIN / STAFF"]:::entity
    CUST["👤 PELANGGAN"]:::entity

    P51(["5.1 Buat Jadwal Pengiriman<br/>(Free Ongkir Malang Raya)"]):::process
    P52(["5.2 Update Status Pengiriman<br/>(Menunggu -> Sampai)"]):::process

    D3[("💾 D3: Pesanan")]:::datastore
    D5[("💾 D5: Pengiriman")]:::datastore
    D4Log[("💾 D4: Tracking Log")]:::datastore

    USER -->|Input Tanggal & Jam Kirim| P51
    D3 -->|Data Alamat & Penerima| P51
    P51 -->|Simpan Jadwal Logistik| D5

    USER -->|"Update Kurir Obrok (Max 150 pcs)"| P52
    D5 -->|Ambil Data Jadwal| P52
    P52 -->|Update Status Kirim| D5
    P52 -->|Catat Histori Log| D4Log
    P52 -->|Sinkronisasi Status Pesanan| D3
    P52 -->|Notifikasi Roti Tiba| CUST
```

---

## 📌 BAB 3 (BAGIAN 2) — DIAGRAM 3.6: DFD LEVEL 2 (PROSES 6.0 PELAPORAN KEUANGAN)
> **Untuk:** Bab 3 Sub-bab 6 — Pelaporan Keuangan & Arus Kas

```mermaid
flowchart TD
    classDef entity fill:#E3F2FD,stroke:#1565C0,stroke-width:2px,color:#0D47A1;
    classDef process fill:#FFF8E1,stroke:#F57F17,stroke-width:2px,color:#E65100;
    classDef datastore fill:#E8F5E9,stroke:#2E7D32,stroke-width:2px,color:#1B5E20;

    ADMIN["👑 ADMIN / PEMILIK"]:::entity

    P61(["6.1 Rekap Penjualan Harian & Bulanan"]):::process
    P62(["6.2 Laporan Piutang Pelanggan"]):::process
    P63(["6.3 Rekap Pengeluaran Operasional"]):::process
    P64(["6.4 Laporan Arus Kas (Cash Flow)"]):::process
    P65(["6.5 Export Laporan (PDF / Excel)"]):::process

    D3[("💾 D3: Pesanan")]:::datastore
    D4[("💾 D4: Pembayaran")]:::datastore
    D2[("💾 D2: Pelanggan (Hutang)")]:::datastore
    D61[("💾 D6.1: Pengeluaran")]:::datastore
    D62[("💾 D6.2: Transaksi Kas")]:::datastore

    ADMIN -->|Filter Rentang Tanggal| P61
    D3 & D4 --> P61
    P61 -->|Laporan Omset Berkala| ADMIN

    ADMIN -->|Request Daftar Piutang| P62
    D2 & D4 --> P62
    P62 -->|Buku Pembantu Piutang| ADMIN

    ADMIN -->|Input Beban Usaha| P63
    P63 <---> D61
    P63 -->|Rekap Biaya Operasional| ADMIN

    D62 --> P64
    P64 -->|Laporan Posisi Saldo Kas| ADMIN

    ADMIN -->|Pilih Format Unduhan| P65
    P61 & P62 & P63 & P64 --> P65
    P65 -->|File Unduhan PDF & Excel| ADMIN
```

---

## 📌 BAB 3 (BAGIAN 2) — DIAGRAM 3.7: DFD LEVEL 2 (PROSES 7.0 MANAJEMEN PENGGUNA)
> **Untuk:** Bab 3 Sub-bab 7 — Manajemen Pengguna & Keamanan Sistem

```mermaid
flowchart TD
    classDef entity fill:#E3F2FD,stroke:#1565C0,stroke-width:2px,color:#0D47A1;
    classDef process fill:#FFF8E1,stroke:#F57F17,stroke-width:2px,color:#E65100;
    classDef datastore fill:#E8F5E9,stroke:#2E7D32,stroke-width:2px,color:#1B5E20;

    ADMIN["👑 ADMIN / PEMILIK"]:::entity
    STAFF["👨‍💼 STAFF"]:::entity

    P71(["7.1 Kelola Data Pengguna<br/>(Password Hash Bcrypt)"]):::process
    P72(["7.2 Autentikasi & Login<br/>(Session Timeout 30 Menit)"]):::process
    P73(["7.3 Kontrol Hak Akses<br/>(Role-Based Access Control)"]):::process

    D7[("💾 D7: Pengguna")]:::datastore

    ADMIN -->|Tambah/Edit Akun & Role| P71
    P71 <---> D7

    ADMIN & STAFF -->|Input Username & Password| P72
    P72 <---> D7
    P72 -->|"Validasi Sukses (Buka Sesi)"| P73

    P73 -->|"Hak Akses Penuh (Full CRUD)"| ADMIN
    P73 -->|"Hak Terbatas (Order & Bayar)"| STAFF
```

---

## 📌 BAB 4 — DIAGRAM 4.1: RINGKASAN ARUS DATA UTAMA (GLOBAL SUMMARY)
> **Untuk:** Bab 4 Ringkasan & Validasi Mitra

```mermaid
flowchart LR
    classDef entity fill:#E3F2FD,stroke:#1565C0,stroke-width:2px,color:#0D47A1,font-weight:bold;
    classDef process fill:#FFF8E1,stroke:#F57F17,stroke-width:2px,color:#E65100,font-weight:bold;
    classDef datastore fill:#E8F5E9,stroke:#2E7D32,stroke-width:2px,color:#1B5E20,font-weight:bold;

    CUST["👤 PELANGGAN"]:::entity
    ADMIN["👑 ADMIN"]:::entity
    STAFF["👨‍💼 STAFF"]:::entity

    subgraph SISTEM ["🏢 SISTEM BERKAT DINASTI"]
        P1["1.0 Katalog"]:::process
        P2["2.0 Pelanggan"]:::process
        P3["3.0 Pesanan"]:::process
        P4["4.0 Pembayaran"]:::process
        P5["5.0 Pengiriman"]:::process
        P6["6.0 Laporan"]:::process
        P7["7.0 Pengguna"]:::process

        D1[("💾 D1")]:::datastore
        D2[("💾 D2")]:::datastore
        D3[("💾 D3")]:::datastore
        D4[("💾 D4")]:::datastore
        D5[("💾 D5")]:::datastore
        D6[("💾 D6")]:::datastore
        D7[("💾 D7")]:::datastore
    end

    CUST -->|Pesan Roti| P3
    P3 --> D3
    D3 --> P4
    P4 --> D4
    P3 --> P5
    P5 --> D5

    P4 --> P6
    P6 -->|Laporan Omset & Kas| ADMIN

    ADMIN -->|Master Roti| P1 --> D1
    ADMIN -->|Data Akun| P7 --> D7
    ADMIN -->|Data Pelanggan| P2 --> D2

    STAFF -->|Input Order| P3
    STAFF -->|Catat Bayar| P4
    STAFF -->|Update Kurir| P5

    P3 -.->|Invoice| CUST
    P4 -.->|Struk WA| CUST
    P5 -.->|Roti Tiba| CUST
```
