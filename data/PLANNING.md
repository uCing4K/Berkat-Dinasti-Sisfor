# 📋 PERENCANAAN APLIKASI MANAJEMEN PESANAN ROTI
## "ForHomie" - Sistem Manajemen Order & Pelanggan

**Tanggal Pembuatan:** 4 Februari 2026  
**Versi:** 1.0  
**Developer:** sipa,adit,bia,dzay

---

## 📑 DAFTAR ISI

1. [Latar Belakang](#-latar-belakang)
2. [Tujuan Proyek](#-tujuan-proyek)
3. [Tech Stack](#-tech-stack)
4. [Arsitektur Sistem](#-arsitektur-sistem)
5. [Modul & Fitur](#-modul--fitur)
6. [Desain Database](#-desain-database)
7. [Entity Relationship Diagram (ERD)](#-entity-relationship-diagram-erd)
8. [Struktur Tabel MySQL](#-struktur-tabel-mysql)
9. [Relasi Antar Tabel](#-relasi-antar-tabel)
10. [Timeline Development](#-timeline-development)
11. [Panduan Implementasi](#-panduan-implementasi)

---

## 🎯 LATAR BELAKANG

Berdasarkan hasil wawancara dengan mitra usaha roti, ditemukan beberapa permasalahan:

| Masalah | Dampak |
|---------|--------|
| Pencatatan manual (buku/nota) | Data tidak terorganisir, sulit tracking |
| Order via WhatsApp | Tidak ada rekap otomatis |
| Tidak ada database pelanggan | Sulit follow-up pelanggan tetap |
| Tidak ada sistem invoice | Tidak profesional, sulit tracking piutang |
| Pengiriman tidak terjadwal | Tidak efisien |

### Profil Usaha Mitra
- **Produk:** Roti Hajatan (6 rasa), Roti Kopi, Roti Bijian
- **Sistem:** Pre-Order (PO), bukan ready stock
- **Pengiriman:** Gratis ongkir wilayah Malang Raya & Batu
- **Kapasitas:** 150pcs roti besar per trip (sepeda obrok)
- **Pembayaran:** Cash & Transfer (bisa bayar setelah antar)

---

## 🎯 TUJUAN PROYEK

1. **Digitalisasi** pencatatan penjualan & pelanggan
2. **Otomatisasi** pembuatan invoice/struk digital
3. **Tracking** status pesanan secara real-time
4. **Manajemen** piutang dan pembayaran
5. **Pelaporan** penjualan harian/bulanan
6. **Katalog Online** untuk promosi produk

---

## 💻 TECH STACK

| Komponen | Teknologi | Alasan |
|----------|-----------|--------|
| **Backend** | PHP 8.x (Native/Laravel) | Stabil, mudah development, familiar |
| **Frontend** | HTML5, CSS3, JavaScript | PWA Support |
| **CSS Framework** | Bootstrap 5 / Tailwind CSS | Responsive design |
| **Database** | MySQL 8.0 | Relational, stabil, banyak dokumentasi |
| **PWA** | Service Worker + Manifest | Installable di HP & Desktop |
| **Template Engine** | Blade (Laravel) / Native PHP | Clean code |
| **Hosting** | Self-hosted | Sudah tersedia |

### Struktur Folder Proyek
```
forhomie/
├── 📁 app/
│   ├── 📁 controllers/      # Logic aplikasi
│   ├── 📁 models/           # Model database
│   └── 📁 helpers/          # Fungsi bantuan
├── 📁 config/
│   └── database.php         # Konfigurasi database
├── 📁 public/
│   ├── 📁 assets/
│   │   ├── 📁 css/
│   │   ├── 📁 js/
│   │   └── 📁 images/
│   ├── manifest.json        # PWA manifest
│   └── sw.js               # Service Worker
├── 📁 views/
│   ├── 📁 admin/           # Dashboard admin
│   ├── 📁 customer/        # Halaman customer (tracking)
│   └── 📁 layouts/         # Template layout
├── 📁 database/
│   ├── schema.sql          # Struktur database
│   └── seeder.sql          # Data dummy
├── 📁 docs/
│   └── ERD.png             # Diagram ERD
└── index.php               # Entry point
```

---

## 🏗 ARSITEKTUR SISTEM

```
┌─────────────────────────────────────────────────────────────────┐
│                        CLIENT SIDE                               │
├─────────────────────────────────────────────────────────────────┤
│  ┌─────────────┐  ┌─────────────┐  ┌─────────────────────────┐  │
│  │   Mobile    │  │   Desktop   │  │   Customer Tracking     │  │
│  │   (PWA)     │  │   Browser   │  │   (Public Page)         │  │
│  └──────┬──────┘  └──────┬──────┘  └───────────┬─────────────┘  │
└─────────┼────────────────┼─────────────────────┼────────────────┘
          │                │                     │
          └────────────────┼─────────────────────┘
                           │
                    ┌──────▼──────┐
                    │   HTTPS     │
                    │   Request   │
                    └──────┬──────┘
                           │
┌──────────────────────────▼──────────────────────────────────────┐
│                        SERVER SIDE                               │
├─────────────────────────────────────────────────────────────────┤
│  ┌─────────────────────────────────────────────────────────┐    │
│  │                    PHP APPLICATION                       │    │
│  ├─────────────────────────────────────────────────────────┤    │
│  │  ┌───────────┐  ┌───────────┐  ┌───────────────────┐    │    │
│  │  │Controllers│  │  Models   │  │     Helpers       │    │    │
│  │  └───────────┘  └───────────┘  └───────────────────┘    │    │
│  └─────────────────────────┬───────────────────────────────┘    │
│                            │                                     │
│                    ┌───────▼───────┐                            │
│                    │    MySQL      │                            │
│                    │   Database    │                            │
│                    └───────────────┘                            │
└─────────────────────────────────────────────────────────────────┘
```

---

## 📦 MODUL & FITUR

### MODUL 1: Katalog Produk 🍞

| Fitur | Deskripsi | Prioritas |
|-------|-----------|-----------|
| Katalog Online | Menu produk yang bisa diakses publik | ⭐⭐⭐ |
| Harga per Varian | Harga berbeda tiap kemasan (kardus/mika) | ⭐⭐⭐ |
| Kategori Produk | Roti Hajatan, Roti Manis, dll | ⭐⭐⭐ |
| Minimal Order | Batas minimum pembelian per produk | ⭐⭐⭐ |
| Gambar Produk | Upload foto produk | ⭐⭐ |
| Status Produk | Tersedia / Tidak tersedia | ⭐⭐ |

**Data Produk Awal:**
| Produk | Kemasan | Harga | Min. Order |
|--------|---------|-------|------------|
| Roti Hajatan (6 rasa) | Kardus Biasa | Rp 9.000 | 1 |
| Roti Hajatan (6 rasa) | Mika | Rp 10.000 | 1 |
| Roti Kopi | Satuan | Rp 4.000 | 50 pcs |
| Roti Bijian (Besar) | Satuan | Rp 4.000 | 20 pcs |
| Roti Bijian (Kecil) | Satuan | Rp 3.000 | 50 pcs |

---

### MODUL 2: Manajemen Pelanggan 👥

| Fitur | Deskripsi | Prioritas |
|-------|-----------|-----------|
| Database Pelanggan | Nama, Alamat, No. WA | ⭐⭐⭐ |
| Klasifikasi | Retail / Reseller | ⭐⭐⭐ |
| Riwayat Pesanan | List semua pesanan pelanggan | ⭐⭐⭐ |
| Status Pembayaran | Lunas / Belum Bayar | ⭐⭐⭐ |
| Manajemen Hutang | Tracking piutang per pelanggan | ⭐⭐⭐ |
| Total Transaksi | Akumulasi belanja pelanggan | ⭐⭐ |

---

### MODUL 3: Manajemen Pesanan 📝

| Fitur | Deskripsi | Prioritas |
|-------|-----------|-----------|
| Input Pesanan Baru | Form order dengan detail produk | ⭐⭐⭐ |
| Status Pesanan | Pending → Proses → Kirim → Selesai | ⭐⭐⭐ |
| Auto Generate Invoice | Format: INV-YYYYMMDD-XXXX | ⭐⭐⭐ |
| Kalender Produksi | Jadwal & deadline pengiriman | ⭐⭐⭐ |
| Tracking Customer | Halaman publik cek status pesanan | ⭐⭐⭐ |
| Struk Digital | Invoice digital bisa di-share | ⭐⭐⭐ |
| Catatan Pesanan | Note khusus per order | ⭐⭐ |

**Alur Status Pesanan:**
```
┌──────────┐    ┌──────────┐    ┌──────────┐    ┌──────────┐
│ PENDING  │ ─► │  PROSES  │ ─► │  KIRIM   │ ─► │ SELESAI  │
│ (Baru)   │    │(Produksi)│    │(Delivery)│    │ (Done)   │
└──────────┘    └──────────┘    └──────────┘    └──────────┘
                                      │
                                      ▼
                               ┌──────────┐
                               │  BATAL   │
                               │(Canceled)│
                               └──────────┘
```

---

### MODUL 4: Distribusi & Pengiriman 🚚

| Fitur | Deskripsi | Prioritas |
|-------|-----------|-----------|
| Jadwal Pengiriman | Tanggal & waktu kirim | ⭐⭐⭐ |
| Zona Wilayah | Mapping area pelanggan | ⭐⭐⭐ |
| Ongkos Kirim | Gratis: Malang Raya & Batu | ⭐⭐⭐ |
| Kapasitas Angkut | Max 150pcs roti besar/trip | ⭐⭐ |
| Tracking Pengiriman | Status real-time | ⭐⭐⭐ |
| Rute Optimasi | Grouping pengiriman per wilayah | ⭐ |

**Zona Pengiriman:**
| Zona | Wilayah | Ongkir |
|------|---------|--------|
| Zona 1 | Kota Malang | GRATIS |
| Zona 2 | Kabupaten Malang | GRATIS |
| Zona 3 | Kota Batu | GRATIS |
| Zona 4 | Luar Malang Raya | Sesuai jarak |

---

### MODUL 5: Laporan Keuangan 💰

| Fitur | Deskripsi | Prioritas |
|-------|-----------|-----------|
| Rekap Penjualan Harian | Total penjualan per hari | ⭐⭐⭐ |
| Rekap Penjualan Bulanan | Total penjualan per bulan | ⭐⭐⭐ |
| Rekap Pengeluaran | Catat biaya operasional | ⭐⭐ |
| Rekap Pendapatan | Profit/margin | ⭐⭐ |
| Daftar Piutang | List hutang pelanggan | ⭐⭐⭐ |
| Metode Pembayaran | Filter Cash/Transfer | ⭐⭐⭐ |
| Export Laporan | PDF/Excel | ⭐⭐ |

---

## 🗄 DESAIN DATABASE

### Konsep Dasar Database Relasional

Sebelum membuat tabel, pahami konsep-konsep ini:

#### 1. Entitas (Entity)
Objek atau konsep yang datanya disimpan. Contoh: Produk, Pelanggan, Pesanan

#### 2. Atribut (Attribute)
Karakteristik atau properti dari entitas. Contoh: nama_produk, harga, alamat

#### 3. Primary Key (PK)
Kolom unik yang mengidentifikasi setiap baris. Contoh: id_produk, id_pelanggan

#### 4. Foreign Key (FK)
Kolom yang menghubungkan tabel satu dengan tabel lain. Membuat relasi.

#### 5. Jenis Relasi
| Relasi | Penjelasan | Contoh |
|--------|------------|--------|
| One-to-One (1:1) | Satu record hanya berhubungan dengan satu record | User - Profile |
| One-to-Many (1:N) | Satu record berhubungan dengan banyak record | Pelanggan - Pesanan |
| Many-to-Many (M:N) | Banyak record berhubungan dengan banyak record | Pesanan - Produk |

---

## 📊 ENTITY RELATIONSHIP DIAGRAM (ERD)

```
┌─────────────────┐       ┌─────────────────┐       ┌─────────────────┐
│    KATEGORI     │       │     PRODUK      │       │  VARIAN_PRODUK  │
├─────────────────┤       ├─────────────────┤       ├─────────────────┤
│ PK id_kategori  │◄──┐   │ PK id_produk    │◄──┐   │ PK id_varian    │
│    nama_kategori│   │   │ FK id_kategori  │───┘   │ FK id_produk    │───┐
│    deskripsi    │   │   │    nama_produk  │       │    nama_varian  │   │
│    created_at   │   │   │    deskripsi    │       │    harga        │   │
└─────────────────┘   │   │    gambar       │       │    min_order    │   │
                      │   │    shelf_life   │       │    stok         │   │
                      │   │    status       │       └─────────────────┘   │
                      │   │    created_at   │                             │
                      │   └─────────────────┘                             │
                      │                                                   │
┌─────────────────┐   │   ┌─────────────────┐       ┌─────────────────┐   │
│      ZONA       │   │   │    PELANGGAN    │       │    PESANAN      │   │
├─────────────────┤   │   ├─────────────────┤       ├─────────────────┤   │
│ PK id_zona      │◄──┼───│ FK id_zona      │◄──────│ FK id_pelanggan │   │
│    nama_zona    │   │   │ PK id_pelanggan │       │ PK id_pesanan   │   │
│    ongkir       │   │   │    nama         │       │    no_invoice   │   │
│    deskripsi    │   │   │    alamat       │       │    tgl_pesan    │   │
└─────────────────┘   │   │    no_wa        │       │    tgl_kirim    │   │
                      │   │    tipe         │       │    total_harga  │   │
                      │   │    created_at   │       │    status       │   │
                      │   └─────────────────┘       │    status_bayar │   │
                      │                             │    metode_bayar │   │
                      │                             │    catatan      │   │
                      │                             │    created_at   │   │
                      │                             └────────┬────────┘   │
                      │                                      │            │
                      │                             ┌────────▼────────┐   │
                      │                             │  DETAIL_PESANAN │   │
                      │                             ├─────────────────┤   │
                      │                             │ PK id_detail    │   │
                      │                             │ FK id_pesanan   │   │
                      │                             │ FK id_varian    │───┘
                      │                             │    qty          │
                      │                             │    harga_satuan │
                      │                             │    subtotal     │
                      │                             └─────────────────┘
                      │
┌─────────────────┐   │   ┌─────────────────┐       ┌─────────────────┐
│    PENGGUNA     │   │   │    PEMBAYARAN   │       │   PENGIRIMAN    │
├─────────────────┤   │   ├─────────────────┤       ├─────────────────┤
│ PK id_user      │   │   │ PK id_pembayaran│       │ PK id_pengiriman│
│    username     │   │   │ FK id_pesanan   │       │ FK id_pesanan   │
│    password     │   │   │    jumlah       │       │    tgl_kirim    │
│    nama_lengkap │   │   │    tgl_bayar    │       │    status       │
│    role         │   │   │    metode       │       │    driver       │
│    created_at   │   │   │    bukti        │       │    catatan      │
└─────────────────┘   │   │    status       │       │    updated_at   │
                      │   └─────────────────┘       └─────────────────┘
                      │
┌─────────────────┐   │   ┌─────────────────┐
│   PENGELUARAN   │   │   │  TRACKING_LOG   │
├─────────────────┤   │   ├─────────────────┤
│ PK id_pengeluaran   │   │ PK id_log       │
│    tanggal      │   │   │ FK id_pesanan   │
│    kategori     │   │   │    status       │
│    deskripsi    │   │   │    keterangan   │
│    jumlah       │   │   │    created_at   │
│    created_at   │   │   └─────────────────┘
└─────────────────┘   │
                      │
```

---

## 🔧 STRUKTUR TABEL MySQL

### Panduan Tipe Data

| Tipe Data | Penggunaan | Contoh |
|-----------|------------|--------|
| `INT` | Bilangan bulat | id, qty, stok |
| `VARCHAR(n)` | Teks pendek (max n karakter) | nama, no_wa |
| `TEXT` | Teks panjang | deskripsi, alamat |
| `DECIMAL(p,s)` | Angka desimal (harga) | harga, total |
| `DATE` | Tanggal (YYYY-MM-DD) | tgl_pesan |
| `DATETIME` | Tanggal + Waktu | created_at |
| `ENUM` | Pilihan terbatas | status, tipe |
| `TIMESTAMP` | Auto update waktu | updated_at |

---

### TABEL 1: `kategori`
Menyimpan kategori produk

```sql
CREATE TABLE kategori (
    id_kategori INT PRIMARY KEY AUTO_INCREMENT,
    nama_kategori VARCHAR(100) NOT NULL,
    deskripsi TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- Data awal
INSERT INTO kategori (nama_kategori, deskripsi) VALUES
('Roti Hajatan', 'Roti untuk acara hajatan/syukuran'),
('Roti Manis', 'Roti manis berbagai varian');
```

---

### TABEL 2: `produk`
Menyimpan data produk utama

```sql
CREATE TABLE produk (
    id_produk INT PRIMARY KEY AUTO_INCREMENT,
    id_kategori INT NOT NULL,
    nama_produk VARCHAR(150) NOT NULL,
    deskripsi TEXT,
    gambar VARCHAR(255),
    shelf_life INT DEFAULT 7 COMMENT 'Masa kedaluwarsa dalam hari',
    status ENUM('tersedia', 'tidak_tersedia') DEFAULT 'tersedia',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    FOREIGN KEY (id_kategori) REFERENCES kategori(id_kategori)
        ON DELETE RESTRICT ON UPDATE CASCADE
);

-- Data awal
INSERT INTO produk (id_kategori, nama_produk, deskripsi, shelf_life) VALUES
(1, 'Roti Hajatan Isi 6 Rasa', 'Paket roti hajatan berisi 6 varian rasa', 7),
(2, 'Roti Kopi', 'Roti dengan topping kopi', 7),
(2, 'Roti Bijian Besar', 'Roti bijian ukuran besar', 7),
(2, 'Roti Bijian Kecil', 'Roti bijian ukuran kecil', 7);
```

---

### TABEL 3: `varian_produk`
Menyimpan varian kemasan & harga

```sql
CREATE TABLE varian_produk (
    id_varian INT PRIMARY KEY AUTO_INCREMENT,
    id_produk INT NOT NULL,
    nama_varian VARCHAR(100) NOT NULL COMMENT 'Contoh: Kardus, Mika, Satuan',
    harga DECIMAL(10,2) NOT NULL,
    min_order INT DEFAULT 1,
    stok INT DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    FOREIGN KEY (id_produk) REFERENCES produk(id_produk)
        ON DELETE CASCADE ON UPDATE CASCADE
);

-- Data awal
INSERT INTO varian_produk (id_produk, nama_varian, harga, min_order) VALUES
(1, 'Kardus Biasa', 9000.00, 1),
(1, 'Mika', 10000.00, 1),
(2, 'Satuan', 4000.00, 50),
(3, 'Satuan', 4000.00, 20),
(4, 'Satuan', 3000.00, 50);
```

---

### TABEL 4: `zona`
Menyimpan zona pengiriman

```sql
CREATE TABLE zona (
    id_zona INT PRIMARY KEY AUTO_INCREMENT,
    nama_zona VARCHAR(100) NOT NULL,
    deskripsi TEXT,
    ongkir DECIMAL(10,2) DEFAULT 0.00,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Data awal
INSERT INTO zona (nama_zona, deskripsi, ongkir) VALUES
('Kota Malang', 'Wilayah Kota Malang', 0),
('Kabupaten Malang', 'Wilayah Kabupaten Malang', 0),
('Kota Batu', 'Wilayah Kota Batu', 0),
('Luar Malang Raya', 'Wilayah di luar Malang Raya', 15000);
```

---

### TABEL 5: `pelanggan`
Menyimpan data pelanggan

```sql
CREATE TABLE pelanggan (
    id_pelanggan INT PRIMARY KEY AUTO_INCREMENT,
    id_zona INT,
    nama VARCHAR(150) NOT NULL,
    alamat TEXT NOT NULL,
    no_wa VARCHAR(20) NOT NULL,
    tipe ENUM('retail', 'reseller') DEFAULT 'retail',
    total_transaksi DECIMAL(15,2) DEFAULT 0.00 COMMENT 'Akumulasi total belanja',
    total_hutang DECIMAL(15,2) DEFAULT 0.00 COMMENT 'Total piutang belum lunas',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    FOREIGN KEY (id_zona) REFERENCES zona(id_zona)
        ON DELETE SET NULL ON UPDATE CASCADE
);
```

---

### TABEL 6: `pesanan`
Menyimpan data pesanan/order

```sql
CREATE TABLE pesanan (
    id_pesanan INT PRIMARY KEY AUTO_INCREMENT,
    id_pelanggan INT NOT NULL,
    no_invoice VARCHAR(50) NOT NULL UNIQUE,
    tgl_pesan DATE NOT NULL,
    tgl_kirim DATE COMMENT 'Deadline/jadwal pengiriman',
    waktu_kirim TIME,
    total_harga DECIMAL(15,2) NOT NULL DEFAULT 0.00,
    ongkir DECIMAL(10,2) DEFAULT 0.00,
    grand_total DECIMAL(15,2) NOT NULL DEFAULT 0.00,
    status ENUM('pending', 'proses', 'kirim', 'selesai', 'batal') DEFAULT 'pending',
    status_bayar ENUM('belum_bayar', 'dp', 'lunas') DEFAULT 'belum_bayar',
    metode_bayar ENUM('cash', 'transfer') DEFAULT 'cash',
    catatan TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    FOREIGN KEY (id_pelanggan) REFERENCES pelanggan(id_pelanggan)
        ON DELETE RESTRICT ON UPDATE CASCADE
);

-- Trigger untuk auto-generate invoice
DELIMITER //
CREATE TRIGGER before_insert_pesanan
BEFORE INSERT ON pesanan
FOR EACH ROW
BEGIN
    DECLARE next_num INT;
    DECLARE today_date VARCHAR(8);
    
    SET today_date = DATE_FORMAT(NEW.tgl_pesan, '%Y%m%d');
    
    SELECT COALESCE(MAX(CAST(SUBSTRING(no_invoice, -4) AS UNSIGNED)), 0) + 1
    INTO next_num
    FROM pesanan
    WHERE no_invoice LIKE CONCAT('INV-', today_date, '%');
    
    SET NEW.no_invoice = CONCAT('INV-', today_date, '-', LPAD(next_num, 4, '0'));
END//
DELIMITER ;
```

---

### TABEL 7: `detail_pesanan`
Menyimpan detail item dalam pesanan (Tabel penghubung Many-to-Many)

```sql
CREATE TABLE detail_pesanan (
    id_detail INT PRIMARY KEY AUTO_INCREMENT,
    id_pesanan INT NOT NULL,
    id_varian INT NOT NULL,
    qty INT NOT NULL,
    harga_satuan DECIMAL(10,2) NOT NULL,
    subtotal DECIMAL(15,2) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    
    FOREIGN KEY (id_pesanan) REFERENCES pesanan(id_pesanan)
        ON DELETE CASCADE ON UPDATE CASCADE,
    FOREIGN KEY (id_varian) REFERENCES varian_produk(id_varian)
        ON DELETE RESTRICT ON UPDATE CASCADE
);

-- Trigger untuk menghitung subtotal otomatis
DELIMITER //
CREATE TRIGGER before_insert_detail_pesanan
BEFORE INSERT ON detail_pesanan
FOR EACH ROW
BEGIN
    SET NEW.subtotal = NEW.qty * NEW.harga_satuan;
END//
DELIMITER ;

-- Trigger untuk update total_harga di pesanan
DELIMITER //
CREATE TRIGGER after_insert_detail_pesanan
AFTER INSERT ON detail_pesanan
FOR EACH ROW
BEGIN
    UPDATE pesanan 
    SET total_harga = (
        SELECT SUM(subtotal) FROM detail_pesanan WHERE id_pesanan = NEW.id_pesanan
    ),
    grand_total = total_harga + ongkir
    WHERE id_pesanan = NEW.id_pesanan;
END//
DELIMITER ;
```

---

### TABEL 8: `pembayaran`
Menyimpan data pembayaran

```sql
CREATE TABLE pembayaran (
    id_pembayaran INT PRIMARY KEY AUTO_INCREMENT,
    id_pesanan INT NOT NULL,
    jumlah DECIMAL(15,2) NOT NULL,
    tgl_bayar DATETIME NOT NULL,
    metode ENUM('cash', 'transfer') NOT NULL,
    bukti VARCHAR(255) COMMENT 'Path file bukti transfer',
    keterangan TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    
    FOREIGN KEY (id_pesanan) REFERENCES pesanan(id_pesanan)
        ON DELETE RESTRICT ON UPDATE CASCADE
);

-- Trigger update status pembayaran
DELIMITER //
CREATE TRIGGER after_insert_pembayaran
AFTER INSERT ON pembayaran
FOR EACH ROW
BEGIN
    DECLARE total_dibayar DECIMAL(15,2);
    DECLARE grand_total DECIMAL(15,2);
    
    SELECT SUM(jumlah) INTO total_dibayar 
    FROM pembayaran WHERE id_pesanan = NEW.id_pesanan;
    
    SELECT p.grand_total INTO grand_total 
    FROM pesanan p WHERE id_pesanan = NEW.id_pesanan;
    
    IF total_dibayar >= grand_total THEN
        UPDATE pesanan SET status_bayar = 'lunas' WHERE id_pesanan = NEW.id_pesanan;
    ELSEIF total_dibayar > 0 THEN
        UPDATE pesanan SET status_bayar = 'dp' WHERE id_pesanan = NEW.id_pesanan;
    END IF;
END//
DELIMITER ;
```

---

### TABEL 9: `pengiriman`
Menyimpan data pengiriman

```sql
CREATE TABLE pengiriman (
    id_pengiriman INT PRIMARY KEY AUTO_INCREMENT,
    id_pesanan INT NOT NULL,
    tgl_kirim DATETIME,
    status ENUM('menunggu', 'dalam_perjalanan', 'sampai', 'gagal') DEFAULT 'menunggu',
    driver VARCHAR(100),
    catatan TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    FOREIGN KEY (id_pesanan) REFERENCES pesanan(id_pesanan)
        ON DELETE CASCADE ON UPDATE CASCADE
);
```

---

### TABEL 10: `tracking_log`
Menyimpan riwayat perubahan status pesanan

```sql
CREATE TABLE tracking_log (
    id_log INT PRIMARY KEY AUTO_INCREMENT,
    id_pesanan INT NOT NULL,
    status VARCHAR(50) NOT NULL,
    keterangan TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    
    FOREIGN KEY (id_pesanan) REFERENCES pesanan(id_pesanan)
        ON DELETE CASCADE ON UPDATE CASCADE
);

-- Trigger untuk tracking otomatis
DELIMITER //
CREATE TRIGGER after_update_pesanan_status
AFTER UPDATE ON pesanan
FOR EACH ROW
BEGIN
    IF OLD.status != NEW.status THEN
        INSERT INTO tracking_log (id_pesanan, status, keterangan)
        VALUES (NEW.id_pesanan, NEW.status, CONCAT('Status berubah dari ', OLD.status, ' ke ', NEW.status));
    END IF;
END//
DELIMITER ;
```

---

### TABEL 11: `pengguna`
Menyimpan data user/admin

```sql
CREATE TABLE pengguna (
    id_user INT PRIMARY KEY AUTO_INCREMENT,
    username VARCHAR(50) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL COMMENT 'Hashed password',
    nama_lengkap VARCHAR(150) NOT NULL,
    role ENUM('admin', 'kasir', 'driver') DEFAULT 'kasir',
    status ENUM('aktif', 'nonaktif') DEFAULT 'aktif',
    last_login DATETIME,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- Default admin
INSERT INTO pengguna (username, password, nama_lengkap, role) VALUES
('admin', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Administrator', 'admin');
-- Password default: 'password' (harus di-hash dengan password_hash() PHP)
```

---

### TABEL 12: `pengeluaran`
Menyimpan data pengeluaran/biaya operasional

```sql
CREATE TABLE pengeluaran (
    id_pengeluaran INT PRIMARY KEY AUTO_INCREMENT,
    tanggal DATE NOT NULL,
    kategori ENUM('bahan_baku', 'operasional', 'gaji', 'lainnya') NOT NULL,
    deskripsi TEXT NOT NULL,
    jumlah DECIMAL(15,2) NOT NULL,
    created_by INT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    
    FOREIGN KEY (created_by) REFERENCES pengguna(id_user)
        ON DELETE SET NULL ON UPDATE CASCADE
);
```

---

## 🔗 RELASI ANTAR TABEL

### Ringkasan Relasi

| Tabel Induk | Relasi | Tabel Anak | Penjelasan |
|-------------|--------|------------|------------|
| kategori | 1 : N | produk | 1 kategori punya banyak produk |
| produk | 1 : N | varian_produk | 1 produk punya banyak varian |
| zona | 1 : N | pelanggan | 1 zona punya banyak pelanggan |
| pelanggan | 1 : N | pesanan | 1 pelanggan punya banyak pesanan |
| pesanan | 1 : N | detail_pesanan | 1 pesanan punya banyak detail item |
| varian_produk | 1 : N | detail_pesanan | 1 varian bisa di banyak pesanan |
| pesanan | 1 : N | pembayaran | 1 pesanan bisa banyak pembayaran |
| pesanan | 1 : 1 | pengiriman | 1 pesanan punya 1 pengiriman |
| pesanan | 1 : N | tracking_log | 1 pesanan punya banyak log |
| pengguna | 1 : N | pengeluaran | 1 user bisa input banyak pengeluaran |

### Diagram Relasi Sederhana

```
              ┌──────────────┐
              │   kategori   │
              └──────┬───────┘
                     │ 1:N
                     ▼
              ┌──────────────┐
              │    produk    │
              └──────┬───────┘
                     │ 1:N
                     ▼
              ┌──────────────┐
              │varian_produk │◄─────────────┐
              └──────────────┘              │
                                            │ N:1
┌──────────────┐                            │
│     zona     │                            │
└──────┬───────┘                            │
       │ 1:N                                │
       ▼                                    │
┌──────────────┐      1:N       ┌───────────┴───┐
│  pelanggan   │ ─────────────► │    pesanan    │
└──────────────┘                └───────┬───────┘
                                        │
                    ┌───────────────────┼───────────────────┐
                    │ 1:N               │ 1:N               │ 1:1
                    ▼                   ▼                   ▼
            ┌───────────────┐   ┌───────────────┐   ┌───────────────┐
            │detail_pesanan │   │  pembayaran   │   │  pengiriman   │
            └───────────────┘   └───────────────┘   └───────────────┘
                                                           │
                                                           │ 1:N
                                                           ▼
                                                    ┌───────────────┐
                                                    │ tracking_log  │
                                                    └───────────────┘
```

---

## 📅 TIMELINE DEVELOPMENT

### Fase 1: Database & Backend Foundation (Minggu 1-2)

| Task | Durasi | Deliverable |
|------|--------|-------------|
| Setup environment (XAMPP/Laragon) | 1 hari | Server lokal berjalan |
| Buat database & tabel | 2 hari | Schema MySQL complete |
| Buat model PHP untuk setiap tabel | 3 hari | CRUD functions |
| Testing query & relasi | 1 hari | Data valid |

### Fase 2: Core Features - Admin (Minggu 3-4)

| Task | Durasi | Deliverable |
|------|--------|-------------|
| Sistem login & auth | 2 hari | Login berfungsi |
| CRUD Kategori & Produk | 2 hari | Manajemen produk |
| CRUD Pelanggan | 2 hari | Database pelanggan |
| Input Pesanan & Invoice | 3 hari | Sistem order |
| Manajemen Pembayaran | 2 hari | Tracking pembayaran |

### Fase 3: Advanced Features (Minggu 5-6)

| Task | Durasi | Deliverable |
|------|--------|-------------|
| Tracking Status Pesanan | 2 hari | Status real-time |
| Kalender Produksi | 2 hari | Jadwal kirim |
| Sistem Pengiriman | 2 hari | Manajemen delivery |
| Laporan Keuangan | 3 hari | Dashboard report |
| Manajemen Piutang | 2 hari | Tracking hutang |

### Fase 4: Customer Interface (Minggu 7)

| Task | Durasi | Deliverable |
|------|--------|-------------|
| Katalog Online (Public) | 2 hari | Menu produk online |
| Halaman Tracking Customer | 2 hari | Cek status pesanan |
| Struk Digital | 1 hari | Invoice shareable |

### Fase 5: PWA & Finishing (Minggu 8)

| Task | Durasi | Deliverable |
|------|--------|-------------|
| Setup PWA (Manifest + SW) | 2 hari | Installable app |
| Responsive design | 2 hari | Mobile & Desktop |
| Testing & Bug fixing | 2 hari | Stable release |
| Deployment | 1 hari | Live di hosting |

---

## 🚀 PANDUAN IMPLEMENTASI

### Langkah 1: Setup Environment

```bash
# Install XAMPP/Laragon
# Buka phpMyAdmin (localhost/phpmyadmin)
# Buat database baru: forhomie_db
```

### Langkah 2: Import Schema

```sql
-- Jalankan file schema.sql yang berisi semua CREATE TABLE
SOURCE /path/to/database/schema.sql;
```

### Langkah 3: Struktur Project

```
forhomie/
├── config/
│   └── database.php      # Koneksi database
├── app/
│   ├── models/           # Model per tabel
│   └── controllers/      # Logic bisnis
├── views/                # Tampilan HTML
├── public/               # Assets & PWA files
└── index.php             # Entry point
```

### Langkah 4: Koneksi Database (config/database.php)

```php
<?php
define('DB_HOST', 'localhost');
define('DB_NAME', 'forhomie_db');
define('DB_USER', 'root');
define('DB_PASS', '');

try {
    $pdo = new PDO(
        "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=utf8mb4",
        DB_USER,
        DB_PASS,
        [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
        ]
    );
} catch (PDOException $e) {
    die("Koneksi database gagal: " . $e->getMessage());
}
```

---

## 📝 CATATAN PENTING

### Best Practice Database

1. **Normalisasi**: Sudah dinormalisasi hingga 3NF
2. **Indexing**: Tambahkan index pada kolom yang sering di-query
3. **Backup**: Lakukan backup rutin
4. **Soft Delete**: Pertimbangkan menambah kolom `deleted_at` untuk soft delete

### Security

1. Password harus di-hash menggunakan `password_hash()` PHP
2. Gunakan Prepared Statement untuk mencegah SQL Injection
3. Validasi semua input dari user
4. Implementasi CSRF Token

### Skalabilitas

1. Gunakan pagination untuk list data besar
2. Cache query yang sering diakses
3. Optimasi gambar produk

---

## 📚 REFERENSI BELAJAR

- [MySQL Documentation](https://dev.mysql.com/doc/)
- [PHP Manual](https://www.php.net/manual/en/)
- [W3Schools SQL Tutorial](https://www.w3schools.com/sql/)
- [PWA Documentation](https://web.dev/progressive-web-apps/)

---

**Document Version:** 1.0  
**Last Updated:** 4 Februari 2026  
**Author:** Dzauq Bachrul 'Ulum

---

> 💡 **Next Step:** Buat file `schema.sql` berisi semua query CREATE TABLE untuk di-import ke MySQL
