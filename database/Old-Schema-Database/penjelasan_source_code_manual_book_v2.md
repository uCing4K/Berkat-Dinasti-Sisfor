# V. Source Code

Bagian ini menjelaskan source code basis data aplikasi Berkat Dinasti sebagai bagian dari dokumentasi teknis Manual Book HKI. Uraian disusun berdasarkan rancangan pada `schema.sql` dan implementasi pada dump hosting `rodd1157_berkat_dinasti_db.sql`. Dengan demikian, penjelasan yang disajikan tetap mengacu pada struktur data yang sama dan konsisten untuk kebutuhan dokumentasi.

Catatan penting: nama `rodd1157_berkat_dinasti_db` pada dump hosting menggunakan prefix akun cPanel (`rodd1157_`). Untuk keperluan identitas sistem pada dokumen HKI, basis data dituliskan sebagai **berkat_dinasti_db**.

## a. Database berkat_dinasti_db

```sql
CREATE DATABASE IF NOT EXISTS berkat_dinasti_db
CHARACTER SET utf8mb4
COLLATE utf8mb4_unicode_ci;

USE berkat_dinasti_db;
```

Bagian ini menetapkan basis data utama aplikasi. Penggunaan `utf8mb4` dan `utf8mb4_unicode_ci` memastikan penyimpanan data teks berjalan konsisten dan mendukung karakter lengkap. Perintah `USE` mengarahkan seluruh objek berikutnya ke basis data yang sama. Semua patch perbaikan terbaru sudah digabung ke `schema.sql`, sehingga file ini menjadi sumber definisi final.

## b. Tabel pengguna

```sql
CREATE TABLE pengguna (
    id_user INT PRIMARY KEY AUTO_INCREMENT,
    username VARCHAR(50) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL COMMENT 'Hashed password',
    nama_lengkap VARCHAR(150) NOT NULL,
    email VARCHAR(100),
    no_hp VARCHAR(20),
    role ENUM('admin', 'kasir', 'driver') DEFAULT 'kasir',
    status ENUM('aktif', 'nonaktif') DEFAULT 'aktif',
    last_login DATETIME,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB;
```

Tabel `pengguna` menyimpan akun yang mengoperasikan sistem. Kolom `username` bersifat unik sebagai identitas login, sedangkan `password` menyimpan hash kata sandi. Kolom `role` membedakan hak akses, dan `status` menunjukkan kondisi akun aktif atau nonaktif.

## c. Tabel kategori

```sql
CREATE TABLE kategori (
    id_kategori INT PRIMARY KEY AUTO_INCREMENT,
    nama_kategori VARCHAR(100) NOT NULL,
    deskripsi TEXT,
    created_by INT,
    updated_by INT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB;
```

Tabel `kategori` digunakan untuk mengelompokkan produk, misalnya kategori roti hajatan dan roti manis. Kolom `created_by` dan `updated_by` membantu pencatatan aktivitas pengelolaan data agar tetap dapat ditelusuri.

## d. Tabel produk

```sql
CREATE TABLE produk (
    id_produk INT PRIMARY KEY AUTO_INCREMENT,
    id_kategori INT NOT NULL,
    nama_produk VARCHAR(150) NOT NULL,
    deskripsi TEXT,
    gambar VARCHAR(255),
    shelf_life INT DEFAULT 7 COMMENT 'Masa kedaluwarsa dalam hari',
    status ENUM('tersedia', 'tidak_tersedia') DEFAULT 'tersedia',
    created_by INT,
    updated_by INT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    CONSTRAINT fk_produk_kategori
        FOREIGN KEY (id_kategori) REFERENCES kategori(id_kategori)
        ON DELETE RESTRICT ON UPDATE CASCADE
) ENGINE=InnoDB;
```

Tabel `produk` menyimpan data utama barang yang dijual. Setiap produk terhubung dengan satu kategori melalui `id_kategori`. Kolom `shelf_life` mencatat masa simpan produk, sedangkan `status` digunakan untuk menandai ketersediaan.

## e. Tabel varian_produk

```sql
CREATE TABLE varian_produk (
    id_varian INT PRIMARY KEY AUTO_INCREMENT,
    id_produk INT NOT NULL,
    nama_varian VARCHAR(100) NOT NULL,
    harga DECIMAL(10,2) NOT NULL,
    min_order INT DEFAULT 1,
    stok INT DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    CONSTRAINT fk_varian_produk
        FOREIGN KEY (id_produk) REFERENCES produk(id_produk)
        ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB;
```

Tabel `varian_produk` menyimpan variasi produk, seperti bentuk kemasan atau ukuran jual. Satu produk dapat memiliki beberapa varian dengan harga berbeda. Kolom `min_order` dipakai untuk menerapkan batas pembelian minimal.

## f. Tabel zona

```sql
CREATE TABLE zona (
    id_zona INT PRIMARY KEY AUTO_INCREMENT,
    nama_zona VARCHAR(100) NOT NULL,
    deskripsi TEXT,
    ongkir DECIMAL(10,2) DEFAULT 0.00,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB;
```

Tabel `zona` menyimpan wilayah pengiriman beserta ongkos kirim standar. Data ini menjadi dasar perhitungan ongkir pada saat pesanan dibuat.

## g. Tabel pelanggan

```sql
CREATE TABLE pelanggan (
    id_pelanggan INT PRIMARY KEY AUTO_INCREMENT,
    id_zona INT,
    nama VARCHAR(150) NOT NULL,
    alamat TEXT NOT NULL,
    no_wa VARCHAR(20) NOT NULL,
    tipe ENUM('retail', 'reseller') DEFAULT 'retail',
    total_transaksi DECIMAL(15,2) DEFAULT 0.00,
    total_hutang DECIMAL(15,2) DEFAULT 0.00,
    catatan TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    CONSTRAINT fk_pelanggan_zona
        FOREIGN KEY (id_zona) REFERENCES zona(id_zona)
        ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB;
```

Tabel `pelanggan` menyimpan data pelanggan yang melakukan pembelian. Kolom `tipe` membedakan pelanggan retail dan reseller. Kolom `total_transaksi` dan `total_hutang` digunakan untuk mencatat akumulasi belanja dan piutang pelanggan.

## h. Tabel pesanan

```sql
CREATE TABLE pesanan (
    id_pesanan INT PRIMARY KEY AUTO_INCREMENT,
    id_pelanggan INT NOT NULL,
    no_invoice VARCHAR(50) NOT NULL UNIQUE,
    tgl_pesan DATE NOT NULL,
    tgl_kirim DATE,
    waktu_kirim TIME,
    total_harga DECIMAL(15,2) NOT NULL DEFAULT 0.00,
    ongkir DECIMAL(10,2) DEFAULT 0.00,
    grand_total DECIMAL(15,2) NOT NULL DEFAULT 0.00,
    status ENUM('pending', 'proses', 'kirim', 'selesai', 'batal') DEFAULT 'pending',
    status_bayar ENUM('belum_bayar', 'dp', 'hutang', 'lunas') DEFAULT 'belum_bayar',
    metode_bayar ENUM('cash', 'transfer') DEFAULT 'cash',
    catatan TEXT,
    created_by INT,
    updated_by INT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    CONSTRAINT fk_pesanan_pelanggan
        FOREIGN KEY (id_pelanggan) REFERENCES pelanggan(id_pelanggan)
        ON DELETE RESTRICT ON UPDATE CASCADE
) ENGINE=InnoDB;
```

Tabel `pesanan` merupakan header transaksi. Seluruh informasi utama order dicatat di sini, mulai dari pelanggan, jadwal kirim, status proses, status pembayaran, hingga total nilai transaksi. Nilai `status_bayar` mencakup `belum_bayar`, `dp`, `hutang`, dan `lunas` untuk membedakan piutang aktif dan pelunasan penuh.

## i. Tabel detail_pesanan

```sql
CREATE TABLE detail_pesanan (
    id_detail INT PRIMARY KEY AUTO_INCREMENT,
    id_pesanan INT NOT NULL,
    id_varian INT NOT NULL,
    qty INT NOT NULL,
    harga_satuan DECIMAL(10,2) NOT NULL,
    subtotal DECIMAL(15,2) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_detail_pesanan
        FOREIGN KEY (id_pesanan) REFERENCES pesanan(id_pesanan)
        ON DELETE CASCADE ON UPDATE CASCADE,
    CONSTRAINT fk_detail_varian
        FOREIGN KEY (id_varian) REFERENCES varian_produk(id_varian)
        ON DELETE RESTRICT ON UPDATE CASCADE
) ENGINE=InnoDB;
```

Tabel `detail_pesanan` menyimpan rincian item pada setiap pesanan. Satu pesanan dapat memiliki banyak detail. Kolom `subtotal` mencatat hasil perkalian kuantitas dan harga satuan.

## j. Tabel pembayaran

```sql
CREATE TABLE pembayaran (
    id_pembayaran INT PRIMARY KEY AUTO_INCREMENT,
    id_pesanan INT NOT NULL,
    jumlah DECIMAL(15,2) NOT NULL,
    tgl_bayar DATETIME NOT NULL,
    metode ENUM('cash', 'transfer') NOT NULL,
    bukti VARCHAR(255),
    keterangan TEXT,
    received_by INT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_pembayaran_pesanan
        FOREIGN KEY (id_pesanan) REFERENCES pesanan(id_pesanan)
        ON DELETE RESTRICT ON UPDATE CASCADE
) ENGINE=InnoDB;
```

Tabel `pembayaran` mendukung transaksi bertahap, baik DP, cicilan, maupun pelunasan. Satu pesanan dapat memiliki lebih dari satu pembayaran. Kolom `received_by` mengaitkan transaksi dengan pengguna yang menerima pembayaran.

## k. Tabel pengiriman

```sql
CREATE TABLE pengiriman (
    id_pengiriman INT PRIMARY KEY AUTO_INCREMENT,
    id_pesanan INT NOT NULL UNIQUE,
    tgl_kirim DATETIME,
    tgl_sampai DATETIME,
    status ENUM('menunggu', 'dalam_perjalanan', 'sampai', 'gagal') DEFAULT 'menunggu',
    driver VARCHAR(100),
    catatan TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    CONSTRAINT fk_pengiriman_pesanan
        FOREIGN KEY (id_pesanan) REFERENCES pesanan(id_pesanan)
        ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB;
```

Tabel `pengiriman` menyimpan status distribusi pesanan. Satu pesanan hanya memiliki satu data pengiriman agar status pengiriman tetap konsisten.

## l. Tabel tracking_log

```sql
CREATE TABLE tracking_log (
    id_log INT PRIMARY KEY AUTO_INCREMENT,
    id_pesanan INT NOT NULL,
    status VARCHAR(50) NOT NULL,
    keterangan TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_tracking_pesanan
        FOREIGN KEY (id_pesanan) REFERENCES pesanan(id_pesanan)
        ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB;
```

Tabel `tracking_log` berfungsi sebagai riwayat perubahan status pesanan maupun status pembayaran. Data ini mendukung pelacakan proses dan audit transaksi.

## m. Tabel pengeluaran

```sql
CREATE TABLE pengeluaran (
    id_pengeluaran INT PRIMARY KEY AUTO_INCREMENT,
    tanggal DATE NOT NULL,
    kategori ENUM('bahan_baku', 'operasional', 'gaji', 'transportasi', 'lainnya') NOT NULL,
    deskripsi TEXT NOT NULL,
    jumlah DECIMAL(15,2) NOT NULL,
    created_by INT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_pengeluaran_user
        FOREIGN KEY (created_by) REFERENCES pengguna(id_user)
        ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB;
```

Tabel `pengeluaran` mencatat biaya operasional usaha. Pengelompokan kategori membantu penyusunan laporan keuangan yang lebih terstruktur.

## n. Tabel transaksi_kas

```sql
CREATE TABLE transaksi_kas (
    id_transaksi INT PRIMARY KEY AUTO_INCREMENT,
    tanggal DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    jenis ENUM('masuk', 'keluar') NOT NULL,
    sumber_tipe ENUM('pembayaran', 'pengeluaran', 'penyesuaian') NOT NULL,
    sumber_id INT,
    nominal DECIMAL(15,2) NOT NULL,
    keterangan TEXT,
    created_by INT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_transaksi_kas_user
        FOREIGN KEY (created_by) REFERENCES pengguna(id_user)
        ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB;
```

Tabel `transaksi_kas` merupakan buku kas terpusat untuk arus kas masuk dan keluar. Data pada tabel ini digunakan sebagai dasar perhitungan saldo kas.

## o. Tabel setting

```sql
CREATE TABLE setting (
    id_setting INT PRIMARY KEY AUTO_INCREMENT,
    setting_key VARCHAR(100) NOT NULL UNIQUE,
    setting_value TEXT,
    deskripsi TEXT,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB;
```

Tabel `setting` menyimpan konfigurasi aplikasi yang bersifat dinamis, seperti identitas toko, nomor kontak, kapasitas angkut, dan jam operasional.

## p. Relasi antar tabel

Relasi antar tabel disusun menggunakan foreign key untuk menjaga integritas data. Relasi utamanya meliputi:

- `produk.id_kategori` mengarah ke `kategori.id_kategori`
- `varian_produk.id_produk` mengarah ke `produk.id_produk`
- `pelanggan.id_zona` mengarah ke `zona.id_zona`
- `pesanan.id_pelanggan` mengarah ke `pelanggan.id_pelanggan`
- `detail_pesanan.id_pesanan` mengarah ke `pesanan.id_pesanan`
- `detail_pesanan.id_varian` mengarah ke `varian_produk.id_varian`
- `pembayaran.id_pesanan` mengarah ke `pesanan.id_pesanan`
- `pengiriman.id_pesanan` mengarah ke `pesanan.id_pesanan`
- `tracking_log.id_pesanan` mengarah ke `pesanan.id_pesanan`
- `pengeluaran.created_by` mengarah ke `pengguna.id_user`
- `transaksi_kas.created_by` mengarah ke `pengguna.id_user`

Selain relasi utama tersebut, kolom audit pada beberapa tabel juga terhubung ke `pengguna` agar aktivitas operasional dapat ditelusuri dengan baik.

## q. Trigger otomatis

Trigger digunakan untuk menjaga konsistensi data dan mengotomatisasi proses penting dalam sistem Berkat Dinasti. Setiap trigger dirancang untuk memastikan integritas referensial, pembaruan data terkait, dan pencatatan aktivitas otomatis.

### 1. Trigger: trg_before_insert_pesanan

Trigger ini berjalan sebelum record pesanan dimasukkan untuk dua fungsi utama:
1. Mengisi `ongkir` otomatis dari zona pelanggan
2. Membuat nomor invoice otomatis berdasarkan tanggal dan urutan harian

```sql
DELIMITER //
CREATE TRIGGER trg_before_insert_pesanan
BEFORE INSERT ON pesanan
FOR EACH ROW
BEGIN
    DECLARE next_num INT;
    DECLARE today_date VARCHAR(8);
    DECLARE v_ongkir DECIMAL(10,2);
    
    SET today_date = DATE_FORMAT(NEW.tgl_pesan, '%Y%m%d');

    SELECT COALESCE(z.ongkir, 0) INTO v_ongkir
    FROM pelanggan pel
    LEFT JOIN zona z ON pel.id_zona = z.id_zona
    WHERE pel.id_pelanggan = NEW.id_pelanggan;

    SET NEW.ongkir = COALESCE(v_ongkir, 0);
    
    SELECT COALESCE(MAX(CAST(SUBSTRING(no_invoice, -4) AS UNSIGNED)), 0) + 1
    INTO next_num
    FROM pesanan
    WHERE no_invoice LIKE CONCAT('INV-', today_date, '%');
    
    SET NEW.no_invoice = CONCAT('INV-', today_date, '-', LPAD(next_num, 4, '0'));
END//
DELIMITER ;
```

### 1b. Trigger: trg_before_update_pesanan

Trigger ini berjalan sebelum update pesanan untuk menegakkan aturan transisi status secara forward-only (`pending → proses → kirim → selesai`) dan mengatur normalisasi `status_bayar`.

Validasi utama:
1. Status tidak boleh mundur atau lompat alur
2. Status final (`selesai`/`batal`) tidak boleh diubah lagi
3. Saat status menjadi `batal`, `status_bayar` direset ke `belum_bayar`
4. Saat status menjadi `selesai` tetapi belum lunas, `status_bayar` ditetapkan menjadi `hutang`

```sql
DELIMITER //
CREATE TRIGGER trg_before_update_pesanan
BEFORE UPDATE ON pesanan
FOR EACH ROW
BEGIN
    DECLARE total_dibayar DECIMAL(15,2);

    IF OLD.status != NEW.status THEN
        IF OLD.status = 'pending' AND NEW.status NOT IN ('proses', 'batal') THEN
            SIGNAL SQLSTATE '45000'
            SET MESSAGE_TEXT = 'Status dari pending hanya boleh ke proses atau batal';
        END IF;

        IF OLD.status = 'proses' AND NEW.status NOT IN ('kirim', 'batal') THEN
            SIGNAL SQLSTATE '45000'
            SET MESSAGE_TEXT = 'Status dari proses hanya boleh ke kirim atau batal';
        END IF;

        IF OLD.status = 'kirim' AND NEW.status NOT IN ('selesai', 'batal') THEN
            SIGNAL SQLSTATE '45000'
            SET MESSAGE_TEXT = 'Status dari kirim hanya boleh ke selesai atau batal';
        END IF;

        IF OLD.status IN ('selesai', 'batal') THEN
            SIGNAL SQLSTATE '45000'
            SET MESSAGE_TEXT = 'Status pesanan final tidak dapat diubah lagi';
        END IF;
    END IF;

    IF NEW.status = 'batal' THEN
        SET NEW.status_bayar = 'belum_bayar';
    ELSEIF NEW.status = 'selesai' AND NEW.status_bayar != 'lunas' THEN
        SELECT COALESCE(SUM(jumlah), 0) INTO total_dibayar
        FROM pembayaran
        WHERE id_pesanan = NEW.id_pesanan;

        IF total_dibayar < NEW.grand_total THEN
            SET NEW.status_bayar = 'hutang';
        END IF;
    END IF;
END//
DELIMITER ;
```

### 2. Trigger: trg_before_insert_detail

Trigger ini berjalan sebelum detail pesanan ditambahkan untuk melakukan validasi stok dan menghitung `subtotal` secara otomatis. Validasi memastikan:
1. Qty harus lebih dari 0
2. Varian produk harus ada di database
3. Stok varian harus cukup untuk memenuhi qty yang dipesan

Jika salah satu validasi gagal, transaksi akan ditolak dengan pesan error. Jika semua validasi lolos, subtotal akan dihitung dengan rumus: `qty × harga_satuan`.

```sql
DELIMITER //
CREATE TRIGGER trg_before_insert_detail
BEFORE INSERT ON detail_pesanan
FOR EACH ROW
BEGIN
    DECLARE v_stok INT;

    IF NEW.qty <= 0 THEN
        SIGNAL SQLSTATE '45000'
        SET MESSAGE_TEXT = 'Quantity harus lebih dari 0';
    END IF;

    SELECT stok INTO v_stok
    FROM varian_produk
    WHERE id_varian = NEW.id_varian
    FOR UPDATE;

    IF v_stok IS NULL THEN
        SIGNAL SQLSTATE '45000'
        SET MESSAGE_TEXT = 'Varian tidak ditemukan';
    END IF;

    IF v_stok < NEW.qty THEN
        SIGNAL SQLSTATE '45000'
        SET MESSAGE_TEXT = 'Stok tidak mencukupi';
    END IF;

    SET NEW.subtotal = NEW.qty * NEW.harga_satuan;
END//
DELIMITER ;
```

### 3. Trigger: trg_after_insert_detail

Trigger ini berjalan setelah detail pesanan berhasil ditambahkan. Trigger ini melakukan dua tugas penting:
1. Mengurangi stok varian produk sesuai qty yang dipesan (update `stok` di tabel `varian_produk`)
2. Memperbarui `total_harga` dan `grand_total` pada pesanan dengan menjumlahkan semua subtotal ditambah ongkir

Dengan logika ini, setiap kali ada item pesanan baru, stok otomatis berkurang dan total pesanan selalu sinkron.

```sql
DELIMITER //
CREATE TRIGGER trg_after_insert_detail
AFTER INSERT ON detail_pesanan
FOR EACH ROW
BEGIN
    DECLARE total DECIMAL(15,2);
    DECLARE ongkir_val DECIMAL(10,2);

    UPDATE varian_produk
    SET stok = stok - NEW.qty
    WHERE id_varian = NEW.id_varian;

    SELECT SUM(subtotal) INTO total 
    FROM detail_pesanan 
    WHERE id_pesanan = NEW.id_pesanan;
    
    SELECT ongkir INTO ongkir_val 
    FROM pesanan 
    WHERE id_pesanan = NEW.id_pesanan;
    
    UPDATE pesanan 
    SET total_harga = COALESCE(total, 0),
        grand_total = COALESCE(total, 0) + COALESCE(ongkir_val, 0)
    WHERE id_pesanan = NEW.id_pesanan;
END//
DELIMITER ;
```

### 3b. Trigger: trg_before_update_detail

Trigger ini berjalan sebelum detail pesanan diperbarui (misal qty diubah atau varian diubah). Trigger memvalidasi:
1. Qty baru harus lebih dari 0
2. Jika hanya qty yang berubah pada varian yang sama, cek selisih stok (delta) tersedia
3. Jika varian berubah, cek varian tujuan ada dan memiliki stok cukup untuk qty baru
4. Subtotal dihitung ulang dengan qty dan harga_satuan yang baru

Validasi ini memastikan tidak ada transaksi yang membuat stok menjadi negatif.

```sql
DELIMITER //
CREATE TRIGGER trg_before_update_detail
BEFORE UPDATE ON detail_pesanan
FOR EACH ROW
BEGIN
    DECLARE v_stok_baru INT;
    DECLARE v_delta INT;

    IF NEW.qty <= 0 THEN
        SIGNAL SQLSTATE '45000'
        SET MESSAGE_TEXT = 'Quantity harus lebih dari 0';
    END IF;

    IF NEW.id_varian = OLD.id_varian THEN
        SET v_delta = NEW.qty - OLD.qty;
        IF v_delta > 0 THEN
            SELECT stok INTO v_stok_baru
            FROM varian_produk
            WHERE id_varian = NEW.id_varian
            FOR UPDATE;

            IF v_stok_baru < v_delta THEN
                SIGNAL SQLSTATE '45000'
                SET MESSAGE_TEXT = 'Stok tidak mencukupi untuk update qty';
            END IF;
        END IF;
    ELSE
        SELECT stok INTO v_stok_baru
        FROM varian_produk
        WHERE id_varian = NEW.id_varian
        FOR UPDATE;

        IF v_stok_baru IS NULL THEN
            SIGNAL SQLSTATE '45000'
            SET MESSAGE_TEXT = 'Varian tujuan tidak ditemukan';
        END IF;

        IF v_stok_baru < NEW.qty THEN
            SIGNAL SQLSTATE '45000'
            SET MESSAGE_TEXT = 'Stok varian tujuan tidak mencukupi';
        END IF;
    END IF;

    SET NEW.subtotal = NEW.qty * NEW.harga_satuan;
END//
DELIMITER ;
```

### 3c. Trigger: trg_after_update_detail

Trigger ini berjalan setelah detail pesanan berhasil diperbarui. Trigger menyesuaikan stok berdasarkan perubahan:
1. Jika varian sama tapi qty berubah, stok disesuaikan dengan delta (selisih qty lama dan qty baru)
2. Jika varian berbeda, stok varian lama dikembalikan dan stok varian baru dikurangi
3. Total pesanan dihitung ulang sesuai subtotal terbaru

Logika ini memastikan stok selalu akurat setelah ada perubahan detail pesanan.

```sql
DELIMITER //
CREATE TRIGGER trg_after_update_detail
AFTER UPDATE ON detail_pesanan
FOR EACH ROW
BEGIN
    DECLARE total DECIMAL(15,2);
    DECLARE ongkir_val DECIMAL(10,2);

    IF NEW.id_varian = OLD.id_varian THEN
        UPDATE varian_produk
        SET stok = stok - (NEW.qty - OLD.qty)
        WHERE id_varian = NEW.id_varian;
    ELSE
        UPDATE varian_produk
        SET stok = stok + OLD.qty
        WHERE id_varian = OLD.id_varian;

        UPDATE varian_produk
        SET stok = stok - NEW.qty
        WHERE id_varian = NEW.id_varian;
    END IF;

    SELECT COALESCE(SUM(subtotal), 0) INTO total
    FROM detail_pesanan
    WHERE id_pesanan = NEW.id_pesanan;

    SELECT ongkir INTO ongkir_val
    FROM pesanan
    WHERE id_pesanan = NEW.id_pesanan;

    UPDATE pesanan
    SET total_harga = total,
        grand_total = total + COALESCE(ongkir_val, 0)
    WHERE id_pesanan = NEW.id_pesanan;
END//
DELIMITER ;
```

### 4. Trigger: trg_after_delete_detail

Trigger ini berjalan setelah detail pesanan berhasil dihapus. Trigger melakukan:
1. Mengembalikan stok varian ke semula (stok += qty yang dihapus)
2. Menyesuaikan kembali total_harga dan grand_total pada pesanan

Dengan logika ini, jika ada detail yang dibatalkan, stok otomatis kembali tersedia dan total pesanan berkurang sesuai nilai yang dihapus.

```sql
DELIMITER //
CREATE TRIGGER trg_after_delete_detail
AFTER DELETE ON detail_pesanan
FOR EACH ROW
BEGIN
    DECLARE total DECIMAL(15,2);
    DECLARE ongkir_val DECIMAL(10,2);

    UPDATE varian_produk
    SET stok = stok + OLD.qty
    WHERE id_varian = OLD.id_varian;

    SELECT COALESCE(SUM(subtotal), 0) INTO total 
    FROM detail_pesanan 
    WHERE id_pesanan = OLD.id_pesanan;
    
    SELECT ongkir INTO ongkir_val 
    FROM pesanan 
    WHERE id_pesanan = OLD.id_pesanan;
    
    UPDATE pesanan 
    SET total_harga = total,
        grand_total = total + COALESCE(ongkir_val, 0)
    WHERE id_pesanan = OLD.id_pesanan;
END//
DELIMITER ;
```

### 5. Trigger: trg_before_insert_pembayaran

Trigger ini berjalan sebelum insert pembayaran untuk menjaga validitas transaksi keuangan.

Validasi utama:
1. `jumlah` harus lebih dari 0
2. Pesanan harus ada
3. Pesanan tidak boleh berstatus `batal`
4. Pesanan yang sudah `lunas` tidak boleh dibayar lagi
5. Total pembayaran tidak boleh melebihi `grand_total` (anti overpayment)

```sql
DELIMITER //
CREATE TRIGGER trg_before_insert_pembayaran
BEFORE INSERT ON pembayaran
FOR EACH ROW
BEGIN
    DECLARE v_status VARCHAR(20);
    DECLARE v_status_bayar VARCHAR(20);
    DECLARE v_grand_total DECIMAL(15,2);
    DECLARE v_total_dibayar DECIMAL(15,2);

    IF NEW.jumlah <= 0 THEN
        SIGNAL SQLSTATE '45000'
        SET MESSAGE_TEXT = 'Jumlah pembayaran harus lebih dari 0';
    END IF;

    SELECT status, status_bayar, grand_total
    INTO v_status, v_status_bayar, v_grand_total
    FROM pesanan
    WHERE id_pesanan = NEW.id_pesanan
    FOR UPDATE;

    IF v_status IS NULL THEN
        SIGNAL SQLSTATE '45000'
        SET MESSAGE_TEXT = 'Pesanan tidak ditemukan';
    END IF;

    IF v_status = 'batal' THEN
        SIGNAL SQLSTATE '45000'
        SET MESSAGE_TEXT = 'Tidak dapat membayar pesanan yang sudah dibatalkan';
    END IF;

    IF v_status_bayar = 'lunas' THEN
        SIGNAL SQLSTATE '45000'
        SET MESSAGE_TEXT = 'Pesanan sudah lunas';
    END IF;

    SELECT COALESCE(SUM(jumlah), 0)
    INTO v_total_dibayar
    FROM pembayaran
    WHERE id_pesanan = NEW.id_pesanan;

    IF v_total_dibayar + NEW.jumlah > v_grand_total THEN
        SIGNAL SQLSTATE '45000'
        SET MESSAGE_TEXT = 'Pembayaran melebihi sisa tagihan';
    END IF;
END//
DELIMITER ;
```

### 6. Trigger: trg_after_insert_pembayaran (Trigger pembayaran otomatis ke kas)

Trigger ini mencatat pembayaran ke tabel `transaksi_kas` sebagai kas masuk dan memperbarui status pembayaran pesanan.

```sql
DELIMITER //
CREATE TRIGGER trg_after_insert_pembayaran
AFTER INSERT ON pembayaran
FOR EACH ROW
BEGIN
    DECLARE total_dibayar DECIMAL(15,2);
    DECLARE grand DECIMAL(15,2);

    INSERT INTO transaksi_kas (
        tanggal,
        jenis,
        sumber_tipe,
        sumber_id,
        nominal,
        keterangan,
        created_by
    ) VALUES (
        NEW.tgl_bayar,
        'masuk',
        'pembayaran',
        NEW.id_pembayaran,
        NEW.jumlah,
        CONCAT('Pembayaran untuk pesanan #', NEW.id_pesanan),
        NEW.received_by
    );
    
    SELECT SUM(jumlah) INTO total_dibayar 
    FROM pembayaran 
    WHERE id_pesanan = NEW.id_pesanan;
    
    SELECT grand_total INTO grand 
    FROM pesanan 
    WHERE id_pesanan = NEW.id_pesanan;
    
    IF total_dibayar >= grand THEN
        UPDATE pesanan 
        SET status_bayar = 'lunas' 
        WHERE id_pesanan = NEW.id_pesanan;
    ELSEIF total_dibayar > 0 THEN
        UPDATE pesanan 
        SET status_bayar = 'dp' 
        WHERE id_pesanan = NEW.id_pesanan;
    END IF;
END//
DELIMITER ;
```

### 7. Trigger: trg_after_update_pesanan_batal

Trigger ini berjalan setelah update pesanan untuk menangani pembatalan secara konsisten pada stok dan kas.

Logika utama saat status berubah ke `batal`:
1. Mengembalikan stok semua item di `detail_pesanan`
2. Menambahkan catatan kas keluar `penyesuaian` jika ada total pembayaran yang harus direfund
3. Menyesuaikan ulang `total_transaksi` dan `total_hutang` pelanggan bila pesanan sebelumnya sudah `selesai`

```sql
DELIMITER //
CREATE TRIGGER trg_after_update_pesanan_batal
AFTER UPDATE ON pesanan
FOR EACH ROW
BEGIN
    DECLARE total_refund DECIMAL(15,2);
    DECLARE total_bayar DECIMAL(15,2);
    DECLARE piutang_dikurangi DECIMAL(15,2);

    IF NEW.status = 'batal' AND OLD.status != 'batal' THEN
        UPDATE varian_produk vp
        JOIN detail_pesanan dp ON dp.id_varian = vp.id_varian
        SET vp.stok = vp.stok + dp.qty
        WHERE dp.id_pesanan = NEW.id_pesanan;

        SELECT COALESCE(SUM(jumlah), 0)
        INTO total_refund
        FROM pembayaran
        WHERE id_pesanan = NEW.id_pesanan;

        IF total_refund > 0 THEN
            INSERT INTO transaksi_kas (
                tanggal,
                jenis,
                sumber_tipe,
                sumber_id,
                nominal,
                keterangan,
                created_by
            ) VALUES (
                NOW(),
                'keluar',
                'penyesuaian',
                NEW.id_pesanan,
                total_refund,
                CONCAT('Refund pembatalan pesanan #', NEW.id_pesanan),
                NEW.updated_by
            );
        END IF;

        IF OLD.status = 'selesai' THEN
            UPDATE pelanggan
            SET total_transaksi = GREATEST(total_transaksi - OLD.grand_total, 0)
            WHERE id_pelanggan = OLD.id_pelanggan;
        END IF;

        IF OLD.status = 'selesai' AND OLD.status_bayar != 'lunas' THEN
            SELECT COALESCE(SUM(jumlah), 0) INTO total_bayar
            FROM pembayaran
            WHERE id_pesanan = OLD.id_pesanan;

            SET piutang_dikurangi = GREATEST(OLD.grand_total - total_bayar, 0);

            UPDATE pelanggan
            SET total_hutang = GREATEST(total_hutang - piutang_dikurangi, 0)
            WHERE id_pelanggan = OLD.id_pelanggan;
        END IF;
    END IF;
END//
DELIMITER ;
```

### 8. Trigger: trg_after_update_pesanan

Trigger ini mencatat setiap perubahan status pesanan dan status pembayaran ke tabel `tracking_log` untuk keperluan audit trail.

```sql
DELIMITER //
CREATE TRIGGER trg_after_update_pesanan
AFTER UPDATE ON pesanan
FOR EACH ROW
BEGIN
    IF OLD.status != NEW.status THEN
        INSERT INTO tracking_log (id_pesanan, status, keterangan)
        VALUES (
            NEW.id_pesanan, 
            NEW.status, 
            CONCAT('Status berubah dari "', OLD.status, '" ke "', NEW.status, '"')
        );
    END IF;
    
    IF OLD.status_bayar != NEW.status_bayar THEN
        INSERT INTO tracking_log (id_pesanan, status, keterangan)
        VALUES (
            NEW.id_pesanan, 
            CONCAT('bayar_', NEW.status_bayar), 
            CONCAT('Status pembayaran berubah dari "', OLD.status_bayar, '" ke "', NEW.status_bayar, '"')
        );
    END IF;
END//
DELIMITER ;
```

### 9. Trigger: trg_after_pesanan_selesai

Trigger ini menambah total transaksi pelanggan dan menghitung piutang jika pesanan belum lunas saat status pesanan menjadi "selesai".

```sql
DELIMITER //
CREATE TRIGGER trg_after_pesanan_selesai
AFTER UPDATE ON pesanan
FOR EACH ROW
BEGIN
    DECLARE sisa_hutang DECIMAL(15,2);
    DECLARE total_bayar DECIMAL(15,2);

    IF NEW.status = 'selesai' AND OLD.status != 'selesai' THEN
        UPDATE pelanggan 
        SET total_transaksi = total_transaksi + NEW.grand_total
        WHERE id_pelanggan = NEW.id_pelanggan;
    END IF;
    
    IF NEW.status = 'selesai' AND OLD.status != 'selesai' AND NEW.status_bayar != 'lunas' THEN
        SELECT COALESCE(SUM(jumlah), 0) INTO total_bayar
        FROM pembayaran WHERE id_pesanan = NEW.id_pesanan;
        
        SET sisa_hutang = GREATEST(NEW.grand_total - total_bayar, 0);
        
        UPDATE pelanggan 
        SET total_hutang = total_hutang + sisa_hutang
        WHERE id_pelanggan = NEW.id_pelanggan;
    END IF;
END//
DELIMITER ;
```

### 10. Trigger: trg_after_insert_pesanan

Trigger ini membuat data awal pengiriman dan log status awal saat pesanan baru dibuat.

```sql
DELIMITER //
CREATE TRIGGER trg_after_insert_pesanan
AFTER INSERT ON pesanan
FOR EACH ROW
BEGIN
    INSERT INTO pengiriman (id_pesanan, status)
    VALUES (NEW.id_pesanan, 'menunggu');
    
    INSERT INTO tracking_log (id_pesanan, status, keterangan)
    VALUES (NEW.id_pesanan, 'pending', 'Pesanan baru dibuat');
END//
DELIMITER ;
```

### 11. Trigger: trg_after_insert_pengeluaran

Trigger ini mencatat pengeluaran operasional ke tabel `transaksi_kas` sebagai kas keluar.

```sql
DELIMITER //
CREATE TRIGGER trg_after_insert_pengeluaran
AFTER INSERT ON pengeluaran
FOR EACH ROW
BEGIN
    INSERT INTO transaksi_kas (
        tanggal,
        jenis,
        sumber_tipe,
        sumber_id,
        nominal,
        keterangan,
        created_by
    ) VALUES (
        CONCAT(NEW.tanggal, ' 00:00:00'),
        'keluar',
        'pengeluaran',
        NEW.id_pengeluaran,
        NEW.jumlah,
        NEW.deskripsi,
        NEW.created_by
    );
END//
DELIMITER ;
```

## r. View laporan

View (pandangan) disediakan untuk memudahkan penyajian data operasional dan keuangan tanpa harus menulis query kompleks berulang. View-view berikut dirancang untuk mendukung keputusan bisnis dan pelaporan:

### 1. View: v_pesanan_lengkap

View ini menampilkan ringkasan pesanan yang digabung dengan data pelanggan, zona pengiriman, dan status pengiriman. Memudahkan visualisasi pesanan secara menyeluruh.

```sql
CREATE VIEW v_pesanan_lengkap AS
SELECT 
    p.id_pesanan,
    p.no_invoice,
    p.tgl_pesan,
    p.tgl_kirim,
    p.waktu_kirim,
    p.total_harga,
    p.ongkir,
    p.grand_total,
    p.status,
    p.status_bayar,
    p.metode_bayar,
    p.catatan,
    pel.id_pelanggan,
    pel.nama AS nama_pelanggan,
    pel.alamat,
    pel.no_wa,
    pel.tipe AS tipe_pelanggan,
    z.nama_zona,
    pg.status AS status_pengiriman,
    pg.driver
FROM pesanan p
JOIN pelanggan pel ON p.id_pelanggan = pel.id_pelanggan
LEFT JOIN zona z ON pel.id_zona = z.id_zona
LEFT JOIN pengiriman pg ON p.id_pesanan = pg.id_pesanan;
```

### 2. View: v_detail_pesanan_produk

View ini menampilkan rincian item pesanan beserta informasi produk, varian, kuantitas, harga satuan, dan subtotal untuk analisis detail penjualan.

```sql
CREATE VIEW v_detail_pesanan_produk AS
SELECT 
    dp.id_detail,
    dp.id_pesanan,
    p.no_invoice,
    pr.nama_produk,
    vp.nama_varian,
    dp.qty,
    dp.harga_satuan,
    dp.subtotal
FROM detail_pesanan dp
JOIN pesanan p ON dp.id_pesanan = p.id_pesanan
JOIN varian_produk vp ON dp.id_varian = vp.id_varian
JOIN produk pr ON vp.id_produk = pr.id_produk;
```

### 3. View: v_laporan_harian

View ini menghasilkan ringkasan penjualan harian dengan metrik jumlah order, nilai penjualan, total yang lunas, dan total piutang untuk memantau performa harian.

```sql
CREATE VIEW v_laporan_harian AS
SELECT 
    DATE(tgl_pesan) AS tanggal,
    COUNT(id_pesanan) AS total_order,
    SUM(CASE WHEN status = 'selesai' THEN grand_total ELSE 0 END) AS total_penjualan,
    SUM(CASE WHEN status_bayar = 'lunas' THEN grand_total ELSE 0 END) AS total_lunas,
    SUM(CASE WHEN status_bayar != 'lunas' THEN grand_total ELSE 0 END) AS total_piutang
FROM pesanan
WHERE status != 'batal'
GROUP BY DATE(tgl_pesan)
ORDER BY tanggal DESC;
```

### 4. View: v_piutang

View ini menampilkan daftar pelanggan dengan detail sisa hutang, membantu manajemen kredit dan koleksi.

```sql
CREATE VIEW v_piutang AS
SELECT 
    pel.id_pelanggan,
    pel.nama,
    pel.no_wa,
    pel.total_hutang,
    COUNT(p.id_pesanan) AS jumlah_pesanan_belum_lunas,
    SUM(p.grand_total) AS total_pesanan,
    SUM(COALESCE(pb.total_bayar, 0)) AS total_terbayar,
    SUM(p.grand_total) - SUM(COALESCE(pb.total_bayar, 0)) AS sisa_hutang
FROM pelanggan pel
JOIN pesanan p ON pel.id_pelanggan = p.id_pelanggan
LEFT JOIN (
    SELECT id_pesanan, SUM(jumlah) AS total_bayar 
    FROM pembayaran 
    GROUP BY id_pesanan
) pb ON p.id_pesanan = pb.id_pesanan
WHERE p.status_bayar != 'lunas' AND p.status != 'batal'
GROUP BY pel.id_pelanggan, pel.nama, pel.no_wa, pel.total_hutang
HAVING sisa_hutang > 0;
```

### 5. View: v_katalog

View ini menyajikan daftar produk aktif beserta kategori, varian, harga, dan stok untuk keperluan penampilan katalog produk.

```sql
CREATE VIEW v_katalog AS
SELECT 
    p.id_produk,
    k.nama_kategori,
    p.nama_produk,
    p.deskripsi,
    p.gambar,
    p.shelf_life,
    p.status,
    v.id_varian,
    v.nama_varian,
    v.harga,
    v.min_order,
    v.stok
FROM produk p
JOIN kategori k ON p.id_kategori = k.id_kategori
LEFT JOIN varian_produk v ON p.id_produk = v.id_produk
WHERE p.status = 'tersedia'
ORDER BY k.nama_kategori, p.nama_produk, v.harga;
```

### 6. View: v_ringkasan_kas

View ini merangkum total kas masuk, kas keluar, dan saldo akhir untuk memberikan gambaran cepat posisi kas perusahaan.

```sql
CREATE VIEW v_ringkasan_kas AS
SELECT
    COALESCE(SUM(CASE WHEN jenis = 'masuk' THEN nominal ELSE 0 END), 0) AS total_masuk,
    COALESCE(SUM(CASE WHEN jenis = 'keluar' THEN nominal ELSE 0 END), 0) AS total_keluar,
    COALESCE(SUM(CASE WHEN jenis = 'masuk' THEN nominal ELSE -nominal END), 0) AS saldo_akhir
FROM transaksi_kas;
```

### 7. View: v_laporan_kas_harian

View ini menampilkan ringkasan pergerakan kas per tanggal dengan detail pemasukan, pengeluaran, dan saldo harian untuk analisis cash flow.

```sql
CREATE VIEW v_laporan_kas_harian AS
SELECT
    DATE(tanggal) AS tanggal,
    SUM(CASE WHEN jenis = 'masuk' THEN nominal ELSE 0 END) AS pemasukan,
    SUM(CASE WHEN jenis = 'keluar' THEN nominal ELSE 0 END) AS pengeluaran,
    SUM(CASE WHEN jenis = 'masuk' THEN nominal ELSE -nominal END) AS saldo_harian
FROM transaksi_kas
GROUP BY DATE(tanggal)
ORDER BY tanggal DESC;
```

## s. Stored procedure

Stored procedure digunakan untuk mengotomatisasi proses bisnis inti agar berjalan melalui alur yang baku dan tervalidasi. Setiap prosedur dirancang dengan parameter input/output yang jelas dan logika bisnis yang terintegrasi.

### 1. Procedure: sp_create_pesanan

Procedure ini membuat pesanan baru dan secara otomatis menghitung biaya ongkir berdasarkan zona pelanggan. Nomor invoice dibuat melalui trigger.

```sql
DELIMITER //
CREATE PROCEDURE sp_create_pesanan(
    IN p_id_pelanggan INT,
    IN p_tgl_kirim DATE,
    IN p_waktu_kirim TIME,
    IN p_metode_bayar VARCHAR(20),
    IN p_catatan TEXT,
    OUT p_id_pesanan INT
)
BEGIN
    DECLARE v_ongkir DECIMAL(10,2);
    
    -- Get ongkir based on pelanggan zona
    SELECT COALESCE(z.ongkir, 0) INTO v_ongkir
    FROM pelanggan pel
    LEFT JOIN zona z ON pel.id_zona = z.id_zona
    WHERE pel.id_pelanggan = p_id_pelanggan;
    
    INSERT INTO pesanan (
        id_pelanggan, 
        tgl_pesan, 
        tgl_kirim, 
        waktu_kirim,
        ongkir,
        metode_bayar, 
        catatan
    ) VALUES (
        p_id_pelanggan,
        CURDATE(),
        p_tgl_kirim,
        p_waktu_kirim,
        v_ongkir,
        p_metode_bayar,
        p_catatan
    );
    
    SET p_id_pesanan = LAST_INSERT_ID();
END//
DELIMITER ;
```

**Parameter:**
- Input: `p_id_pelanggan`, `p_tgl_kirim`, `p_waktu_kirim`, `p_metode_bayar`, `p_catatan`
- Output: `p_id_pesanan` (ID pesanan yang baru dibuat)

### 2. Procedure: sp_add_detail_pesanan

Procedure ini menambahkan detail item pesanan dengan validasi komprehensif di level procedure sebelum trigger mengeksekusi:
1. Cek varian produk ada di database
2. Cek qty adalah angka positif > 0
3. Cek qty memenuhi minimum order yang ditetapkan untuk varian tersebut
4. Cek stok varian tersedia (cukup untuk qty yang diminta) - KRITICAL untuk mencegah overselling

Jika salah satu validasi gagal, procedure akan throw error dan transaksi dibatalkan. Jika lolos, detail pesanan diinsert dan trigger akan otomatis mengurangi stok.

Kombinasi validasi di procedure + trigger memberikan perlindungan berlapis (defense in depth) untuk menjaga konsistensi stok database.

```sql
DELIMITER //
CREATE PROCEDURE sp_add_detail_pesanan(
    IN p_id_pesanan INT,
    IN p_id_varian INT,
    IN p_qty INT
)
BEGIN
    DECLARE v_harga DECIMAL(10,2);
    DECLARE v_min_order INT;
    DECLARE v_stok INT;
    
    -- Get price and min_order from varian
    SELECT harga, min_order INTO v_harga, v_min_order
    FROM varian_produk
    WHERE id_varian = p_id_varian;
    
    IF v_harga IS NULL THEN
        SIGNAL SQLSTATE '45000'
        SET MESSAGE_TEXT = 'Varian tidak ditemukan';
    END IF;

    IF p_qty <= 0 THEN
        SIGNAL SQLSTATE '45000'
        SET MESSAGE_TEXT = 'Quantity harus lebih dari 0';
    END IF;
    
    -- Check minimum order
    IF p_qty < v_min_order THEN
        SIGNAL SQLSTATE '45000' 
        SET MESSAGE_TEXT = 'Quantity kurang dari minimum order';
    END IF;

    -- Check stock availability (critical validation)
    SELECT stok INTO v_stok
    FROM varian_produk
    WHERE id_varian = p_id_varian
    FOR UPDATE;

    IF v_stok < p_qty THEN
        SIGNAL SQLSTATE '45000'
        SET MESSAGE_TEXT = 'Stok tidak mencukupi';
    END IF;
    
    INSERT INTO detail_pesanan (id_pesanan, id_varian, qty, harga_satuan, subtotal)
    VALUES (p_id_pesanan, p_id_varian, p_qty, v_harga, p_qty * v_harga);
END//
DELIMITER ;
```

**Parameter:**
- Input: `p_id_pesanan`, `p_id_varian`, `p_qty` (jumlah barang yang dipesan)

### 3. Procedure: sp_update_status

Procedure ini memperbarui status pesanan dengan validasi alur forward-only. Procedure juga menyesuaikan status pengiriman serta timestamp-nya.

```sql
DELIMITER //
CREATE PROCEDURE sp_update_status(
    IN p_id_pesanan INT,
    IN p_status VARCHAR(20)
)
BEGIN
    DECLARE v_status_saat_ini VARCHAR(20);

    SELECT status
    INTO v_status_saat_ini
    FROM pesanan
    WHERE id_pesanan = p_id_pesanan;

    IF v_status_saat_ini IS NULL THEN
        SIGNAL SQLSTATE '45000'
        SET MESSAGE_TEXT = 'Pesanan tidak ditemukan';
    END IF;

    IF v_status_saat_ini = 'pending' AND p_status NOT IN ('proses', 'batal') THEN
        SIGNAL SQLSTATE '45000'
        SET MESSAGE_TEXT = 'Transisi status tidak valid dari pending';
    END IF;

    IF v_status_saat_ini = 'proses' AND p_status NOT IN ('kirim', 'batal') THEN
        SIGNAL SQLSTATE '45000'
        SET MESSAGE_TEXT = 'Transisi status tidak valid dari proses';
    END IF;

    IF v_status_saat_ini = 'kirim' AND p_status NOT IN ('selesai', 'batal') THEN
        SIGNAL SQLSTATE '45000'
        SET MESSAGE_TEXT = 'Transisi status tidak valid dari kirim';
    END IF;

    IF v_status_saat_ini IN ('selesai', 'batal') THEN
        SIGNAL SQLSTATE '45000'
        SET MESSAGE_TEXT = 'Status pesanan final tidak dapat diubah';
    END IF;

    UPDATE pesanan
    SET status = p_status
    WHERE id_pesanan = p_id_pesanan;
    
    -- Also update pengiriman status if needed
    IF p_status = 'kirim' THEN
        UPDATE pengiriman 
        SET status = 'dalam_perjalanan', tgl_kirim = NOW()
        WHERE id_pesanan = p_id_pesanan;
    ELSEIF p_status = 'selesai' THEN
        UPDATE pengiriman 
        SET status = 'sampai', tgl_sampai = NOW()
        WHERE id_pesanan = p_id_pesanan;
    END IF;
END//
DELIMITER ;
```

**Parameter:**
- Input: `p_id_pesanan`, `p_status` (status baru yang valid sesuai urutan proses)

### 4. Procedure: sp_bayar

Procedure ini mencatat transaksi pembayaran pesanan dengan validasi anti pembayaran tidak valid. Pembaruan status pembayaran tetap ditangani oleh trigger `trg_after_insert_pembayaran`.

Catatan implementasi: pembacaan akumulasi pembayaran tidak menggunakan penguncian `FOR UPDATE` pada tabel `pembayaran` agar kompatibel dengan alur trigger `BEFORE INSERT ON pembayaran` dan menghindari konflik eksekusi di MySQL. Definisi final ini sudah menyatu di `schema.sql`.

```sql
DELIMITER //
CREATE PROCEDURE sp_bayar(
    IN p_id_pesanan INT,
    IN p_jumlah DECIMAL(15,2),
    IN p_metode VARCHAR(20),
    IN p_bukti VARCHAR(255),
    IN p_keterangan TEXT,
    IN p_received_by INT
)
BEGIN
    DECLARE v_status VARCHAR(20);
    DECLARE v_status_bayar VARCHAR(20);
    DECLARE v_grand_total DECIMAL(15,2);
    DECLARE v_total_dibayar DECIMAL(15,2);

    IF p_jumlah <= 0 THEN
        SIGNAL SQLSTATE '45000'
        SET MESSAGE_TEXT = 'Jumlah pembayaran harus lebih dari 0';
    END IF;

    SELECT status, status_bayar, grand_total
    INTO v_status, v_status_bayar, v_grand_total
    FROM pesanan
    WHERE id_pesanan = p_id_pesanan
    FOR UPDATE;

    IF v_status IS NULL THEN
        SIGNAL SQLSTATE '45000'
        SET MESSAGE_TEXT = 'Pesanan tidak ditemukan';
    END IF;

    IF v_status = 'batal' THEN
        SIGNAL SQLSTATE '45000'
        SET MESSAGE_TEXT = 'Tidak dapat membayar pesanan yang dibatalkan';
    END IF;

    IF v_status_bayar = 'lunas' THEN
        SIGNAL SQLSTATE '45000'
        SET MESSAGE_TEXT = 'Pesanan sudah lunas';
    END IF;

    SELECT COALESCE(SUM(jumlah), 0)
    INTO v_total_dibayar
    FROM pembayaran
    WHERE id_pesanan = p_id_pesanan;

    IF v_total_dibayar + p_jumlah > v_grand_total THEN
        SIGNAL SQLSTATE '45000'
        SET MESSAGE_TEXT = 'Pembayaran melebihi sisa tagihan';
    END IF;

    INSERT INTO pembayaran (
        id_pesanan, 
        jumlah, 
        tgl_bayar, 
        metode, 
        bukti, 
        keterangan,
        received_by
    ) VALUES (
        p_id_pesanan,
        p_jumlah,
        NOW(),
        p_metode,
        p_bukti,
        p_keterangan,
        p_received_by
    );
END//
DELIMITER ;
```

**Parameter:**
- Input: `p_id_pesanan`, `p_jumlah`, `p_metode` (transfer/cash/dll), `p_bukti`, `p_keterangan`, `p_received_by` (user ID kasir)

### 5. Procedure: sp_laporan_penjualan

Procedure ini menghasilkan laporan penjualan berdasarkan rentang tanggal, menampilkan ringkasan order, nilai penjualan, dan tingkat penyelesaian.

```sql
DELIMITER //
CREATE PROCEDURE sp_laporan_penjualan(
    IN p_start_date DATE,
    IN p_end_date DATE
)
BEGIN
    SELECT 
        DATE(tgl_pesan) AS tanggal,
        COUNT(*) AS jumlah_order,
        SUM(CASE WHEN status = 'selesai' THEN 1 ELSE 0 END) AS order_selesai,
        SUM(CASE WHEN status = 'batal' THEN 1 ELSE 0 END) AS order_batal,
        SUM(CASE WHEN status = 'selesai' THEN grand_total ELSE 0 END) AS total_penjualan,
        SUM(CASE WHEN status_bayar = 'lunas' AND status = 'selesai' THEN grand_total ELSE 0 END) AS total_lunas
    FROM pesanan
    WHERE tgl_pesan BETWEEN p_start_date AND p_end_date
    GROUP BY DATE(tgl_pesan)
    ORDER BY tanggal;
END//
DELIMITER ;
```

**Parameter:**
- Input: `p_start_date`, `p_end_date` (periode laporan yang diinginkan)

## t. Kesimpulan source code

Secara keseluruhan, source code basis data Berkat Dinasti disusun secara relasional dan mendukung proses operasional harian. Struktur tabel saling terhubung melalui foreign key, alur transaksi dipantau melalui trigger dan tracking log, sedangkan kebutuhan pelaporan difasilitasi oleh view dan stored procedure.

Berdasarkan susunan tersebut, dokumen ini layak digunakan sebagai bagian Manual Book HKI untuk menjelaskan rancangan teknis dan implementasi basis data aplikasi Berkat Dinasti.
