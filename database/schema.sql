
USE rodd1157_berkat_dinasti_db;

-- =====================================================
-- TABEL 1: KATEGORI
-- Menyimpan kategori produk roti
-- =====================================================
CREATE TABLE kategori (
    id_kategori INT PRIMARY KEY AUTO_INCREMENT,
    nama_kategori VARCHAR(100) NOT NULL,
    deskripsi TEXT,
    created_by INT,
    updated_by INT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,

    INDEX idx_created_by (created_by),
    INDEX idx_updated_by (updated_by)
) ENGINE=InnoDB;

-- =====================================================
-- TABEL 2: PRODUK
-- Menyimpan data produk utama
-- =====================================================
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
    
    INDEX idx_kategori (id_kategori),
    INDEX idx_status (status),
    INDEX idx_created_by (created_by),
    INDEX idx_updated_by (updated_by),
    
    CONSTRAINT fk_produk_kategori 
        FOREIGN KEY (id_kategori) REFERENCES kategori(id_kategori)
        ON DELETE RESTRICT ON UPDATE CASCADE
) ENGINE=InnoDB;

-- =====================================================
-- TABEL 3: VARIAN PRODUK
-- Menyimpan varian kemasan & harga tiap produk
-- =====================================================
CREATE TABLE varian_produk (
    id_varian INT PRIMARY KEY AUTO_INCREMENT,
    id_produk INT NOT NULL,
    nama_varian VARCHAR(100) NOT NULL COMMENT 'Contoh: Kardus, Mika, Satuan',
    harga DECIMAL(10,2) NOT NULL,
    min_order INT DEFAULT 1 COMMENT 'Minimal pembelian',
    stok INT DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    INDEX idx_produk (id_produk),
    
    CONSTRAINT fk_varian_produk 
        FOREIGN KEY (id_produk) REFERENCES produk(id_produk)
        ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB;

-- =====================================================
-- TABEL 4: ZONA
-- Menyimpan zona wilayah pengiriman
-- =====================================================
CREATE TABLE zona (
    id_zona INT PRIMARY KEY AUTO_INCREMENT,
    nama_zona VARCHAR(100) NOT NULL,
    deskripsi TEXT,
    ongkir DECIMAL(10,2) DEFAULT 0.00,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB;

-- =====================================================
-- TABEL 5: PELANGGAN
-- Menyimpan data pelanggan
-- =====================================================
CREATE TABLE pelanggan (
    id_pelanggan INT PRIMARY KEY AUTO_INCREMENT,
    id_zona INT,
    nama VARCHAR(150) NOT NULL,
    alamat TEXT NOT NULL,
    no_wa VARCHAR(20) NOT NULL,
    tipe ENUM('retail', 'reseller') DEFAULT 'retail',
    total_transaksi DECIMAL(15,2) DEFAULT 0.00 COMMENT 'Akumulasi total belanja',
    total_hutang DECIMAL(15,2) DEFAULT 0.00 COMMENT 'Total piutang belum lunas',
    catatan TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    INDEX idx_zona (id_zona),
    INDEX idx_tipe (tipe),
    INDEX idx_no_wa (no_wa),
    
    CONSTRAINT fk_pelanggan_zona 
        FOREIGN KEY (id_zona) REFERENCES zona(id_zona)
        ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB;

-- =====================================================
-- TABEL 6: PESANAN
-- Menyimpan data pesanan/order
-- =====================================================
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
    status_bayar ENUM('belum_bayar', 'dp', 'hutang', 'lunas') DEFAULT 'belum_bayar',
    metode_bayar ENUM('cash', 'transfer') DEFAULT 'cash',
    catatan TEXT,
    created_by INT,
    updated_by INT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    INDEX idx_pelanggan (id_pelanggan),
    INDEX idx_no_invoice (no_invoice),
    INDEX idx_tgl_pesan (tgl_pesan),
    INDEX idx_tgl_kirim (tgl_kirim),
    INDEX idx_status (status),
    INDEX idx_status_bayar (status_bayar),
    INDEX idx_created_by (created_by),
    INDEX idx_updated_by (updated_by),
    
    CONSTRAINT fk_pesanan_pelanggan 
        FOREIGN KEY (id_pelanggan) REFERENCES pelanggan(id_pelanggan)
        ON DELETE RESTRICT ON UPDATE CASCADE
) ENGINE=InnoDB;

-- =====================================================
-- TABEL 7: DETAIL PESANAN
-- Menyimpan detail item dalam pesanan
-- Tabel penghubung (junction table) untuk relasi M:N
-- antara Pesanan dan Varian Produk
-- =====================================================
CREATE TABLE detail_pesanan (
    id_detail INT PRIMARY KEY AUTO_INCREMENT,
    id_pesanan INT NOT NULL,
    id_varian INT NOT NULL,
    qty INT NOT NULL,
    harga_satuan DECIMAL(10,2) NOT NULL,
    subtotal DECIMAL(15,2) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    
    INDEX idx_pesanan (id_pesanan),
    INDEX idx_varian (id_varian),
    
    CONSTRAINT fk_detail_pesanan 
        FOREIGN KEY (id_pesanan) REFERENCES pesanan(id_pesanan)
        ON DELETE CASCADE ON UPDATE CASCADE,
    CONSTRAINT fk_detail_varian 
        FOREIGN KEY (id_varian) REFERENCES varian_produk(id_varian)
        ON DELETE RESTRICT ON UPDATE CASCADE
) ENGINE=InnoDB;

-- =====================================================
-- TABEL 8: PEMBAYARAN
-- Menyimpan data pembayaran (bisa cicilan/DP)
-- =====================================================
CREATE TABLE pembayaran (
    id_pembayaran INT PRIMARY KEY AUTO_INCREMENT,
    id_pesanan INT NOT NULL,
    jumlah DECIMAL(15,2) NOT NULL,
    tgl_bayar DATETIME NOT NULL,
    metode ENUM('cash', 'transfer') NOT NULL,
    bukti VARCHAR(255) COMMENT 'Path file bukti transfer',
    keterangan TEXT,
    received_by INT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    
    INDEX idx_pesanan (id_pesanan),
    INDEX idx_tgl_bayar (tgl_bayar),
    INDEX idx_received_by (received_by),
    
    CONSTRAINT fk_pembayaran_pesanan 
        FOREIGN KEY (id_pesanan) REFERENCES pesanan(id_pesanan)
        ON DELETE RESTRICT ON UPDATE CASCADE
) ENGINE=InnoDB;

-- =====================================================
-- TABEL 9: PENGIRIMAN
-- Menyimpan data pengiriman
-- =====================================================
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
    
    INDEX idx_pesanan (id_pesanan),
    INDEX idx_status (status),
    INDEX idx_tgl_kirim (tgl_kirim),
    
    CONSTRAINT fk_pengiriman_pesanan 
        FOREIGN KEY (id_pesanan) REFERENCES pesanan(id_pesanan)
        ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB;

-- =====================================================
-- TABEL 10: TRACKING LOG
-- Menyimpan riwayat perubahan status pesanan
-- =====================================================
CREATE TABLE tracking_log (
    id_log INT PRIMARY KEY AUTO_INCREMENT,
    id_pesanan INT NOT NULL,
    status VARCHAR(50) NOT NULL,
    keterangan TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    
    INDEX idx_pesanan (id_pesanan),
    INDEX idx_created_at (created_at),
    
    CONSTRAINT fk_tracking_pesanan 
        FOREIGN KEY (id_pesanan) REFERENCES pesanan(id_pesanan)
        ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB;

-- =====================================================
-- TABEL 11: PENGGUNA
-- Menyimpan data user/admin aplikasi
-- =====================================================
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
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    INDEX idx_username (username),
    INDEX idx_role (role),
    INDEX idx_status (status)
) ENGINE=InnoDB;

-- =====================================================
-- TABEL 12: PENGELUARAN
-- Menyimpan data pengeluaran/biaya operasional
-- =====================================================
CREATE TABLE pengeluaran (
    id_pengeluaran INT PRIMARY KEY AUTO_INCREMENT,
    tanggal DATE NOT NULL,
    kategori ENUM('bahan_baku', 'operasional', 'gaji', 'transportasi', 'lainnya') NOT NULL,
    deskripsi TEXT NOT NULL,
    jumlah DECIMAL(15,2) NOT NULL,
    created_by INT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    
    INDEX idx_tanggal (tanggal),
    INDEX idx_kategori (kategori),
    INDEX idx_created_by (created_by),
    
    CONSTRAINT fk_pengeluaran_user 
        FOREIGN KEY (created_by) REFERENCES pengguna(id_user)
        ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB;

-- =====================================================
-- TABEL 13: TRANSAKSI KAS
-- Buku kas pemasukan & pengeluaran
-- =====================================================
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

    INDEX idx_tanggal (tanggal),
    INDEX idx_jenis (jenis),
    INDEX idx_sumber (sumber_tipe, sumber_id),
    INDEX idx_created_by (created_by),

    CONSTRAINT fk_transaksi_kas_user
        FOREIGN KEY (created_by) REFERENCES pengguna(id_user)
        ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB;

-- =====================================================
-- TABEL 14: SETTING
-- Menyimpan konfigurasi aplikasi
-- =====================================================
CREATE TABLE setting (
    id_setting INT PRIMARY KEY AUTO_INCREMENT,
    setting_key VARCHAR(100) NOT NULL UNIQUE,
    setting_value TEXT,
    deskripsi TEXT,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB;

-- =====================================================
-- RELASI USER (ADMIN/KASIR/DRIVER)
-- =====================================================
ALTER TABLE kategori
    ADD CONSTRAINT fk_kategori_created_by_user
        FOREIGN KEY (created_by) REFERENCES pengguna(id_user)
        ON DELETE SET NULL ON UPDATE CASCADE,
    ADD CONSTRAINT fk_kategori_updated_by_user
        FOREIGN KEY (updated_by) REFERENCES pengguna(id_user)
        ON DELETE SET NULL ON UPDATE CASCADE;

ALTER TABLE produk
    ADD CONSTRAINT fk_produk_created_by_user
        FOREIGN KEY (created_by) REFERENCES pengguna(id_user)
        ON DELETE SET NULL ON UPDATE CASCADE,
    ADD CONSTRAINT fk_produk_updated_by_user
        FOREIGN KEY (updated_by) REFERENCES pengguna(id_user)
        ON DELETE SET NULL ON UPDATE CASCADE;

ALTER TABLE pesanan
    ADD CONSTRAINT fk_pesanan_created_by_user
        FOREIGN KEY (created_by) REFERENCES pengguna(id_user)
        ON DELETE SET NULL ON UPDATE CASCADE,
    ADD CONSTRAINT fk_pesanan_updated_by_user
        FOREIGN KEY (updated_by) REFERENCES pengguna(id_user)
        ON DELETE SET NULL ON UPDATE CASCADE;

ALTER TABLE pembayaran
    ADD CONSTRAINT fk_pembayaran_received_by_user
        FOREIGN KEY (received_by) REFERENCES pengguna(id_user)
        ON DELETE SET NULL ON UPDATE CASCADE;

-- =====================================================
-- TRIGGERS
-- =====================================================

-- Trigger: Auto-generate invoice number
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

-- Trigger: Validasi transisi status pesanan & normalisasi status_bayar
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

-- Trigger: Calculate subtotal on detail_pesanan insert
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

-- Trigger: Update total_harga in pesanan after detail insert
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

-- Trigger: Recalculate subtotal, validate stock, and adjust stock delta when detail updated
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

-- Trigger: Update total when detail deleted
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

-- Trigger: Validasi pembayaran sebelum insert (anti overpayment & anti bayar order batal)
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

-- Trigger: Update payment status after payment insert
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

-- Trigger: Penanganan saat pesanan dibatalkan (restore stok & catat kas refund)
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

-- Trigger: Log status changes
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

-- Trigger: Update pelanggan total_transaksi when pesanan completed
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
    
    -- Update hutang if belum lunas
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

-- Trigger: Create pengiriman record when pesanan created
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

-- Trigger: Catat pengeluaran ke transaksi kas
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

-- =====================================================
-- VIEWS
-- =====================================================

-- View: Ringkasan pesanan dengan detail pelanggan
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

-- View: Detail pesanan dengan info produk
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

-- View: Laporan penjualan harian
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

-- View: Daftar piutang pelanggan
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

-- View: Katalog produk dengan varian
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

-- View: Ringkasan saldo kas
CREATE VIEW v_ringkasan_kas AS
SELECT
    COALESCE(SUM(CASE WHEN jenis = 'masuk' THEN nominal ELSE 0 END), 0) AS total_masuk,
    COALESCE(SUM(CASE WHEN jenis = 'keluar' THEN nominal ELSE 0 END), 0) AS total_keluar,
    COALESCE(SUM(CASE WHEN jenis = 'masuk' THEN nominal ELSE -nominal END), 0) AS saldo_akhir
FROM transaksi_kas;

-- View: Laporan kas harian
CREATE VIEW v_laporan_kas_harian AS
SELECT
    DATE(tanggal) AS tanggal,
    SUM(CASE WHEN jenis = 'masuk' THEN nominal ELSE 0 END) AS pemasukan,
    SUM(CASE WHEN jenis = 'keluar' THEN nominal ELSE 0 END) AS pengeluaran,
    SUM(CASE WHEN jenis = 'masuk' THEN nominal ELSE -nominal END) AS saldo_harian
FROM transaksi_kas
GROUP BY DATE(tanggal)
ORDER BY tanggal DESC;

-- =====================================================
-- STORED PROCEDURES
-- =====================================================

-- Procedure: Create new order
DELIMITER //
DROP PROCEDURE IF EXISTS sp_create_pesanan//
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

-- Procedure: Add item to order
DELIMITER //
DROP PROCEDURE IF EXISTS sp_add_detail_pesanan//
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

-- Procedure: Update order status
DELIMITER //
DROP PROCEDURE IF EXISTS sp_update_status//
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

-- Procedure: Record payment
DELIMITER //
DROP PROCEDURE IF EXISTS sp_bayar//
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

-- Procedure: Get sales report by date range
DELIMITER //
DROP PROCEDURE IF EXISTS sp_laporan_penjualan//
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

-- =====================================================
-- DATA SEEDER (Data Awal)
-- =====================================================

-- Insert Kategori
INSERT INTO kategori (nama_kategori, deskripsi) VALUES
('Roti Hajatan', 'Roti untuk acara hajatan, syukuran, dan acara spesial'),
('Roti Manis', 'Berbagai macam roti manis untuk konsumsi sehari-hari');

-- Insert Produk
INSERT INTO produk (id_kategori, nama_produk, deskripsi, shelf_life, status) VALUES
(1, 'Roti Hajatan Isi 6 Rasa', 'Paket roti hajatan berisi 6 varian rasa dalam satu kemasan', 7, 'tersedia'),
(2, 'Roti Kopi', 'Roti dengan topping kopi yang lezat', 7, 'tersedia'),
(2, 'Roti Bijian Besar', 'Roti bijian ukuran besar, cocok untuk sarapan', 7, 'tersedia'),
(2, 'Roti Bijian Kecil', 'Roti bijian ukuran kecil, praktis dibawa', 7, 'tersedia');

-- Insert Varian Produk
INSERT INTO varian_produk (id_produk, nama_varian, harga, min_order, stok) VALUES
(1, 'Kardus Biasa', 9000.00, 1, 0),
(1, 'Mika', 10000.00, 1, 0),
(2, 'Satuan', 4000.00, 50, 0),
(3, 'Satuan', 4000.00, 20, 0),
(4, 'Satuan', 3000.00, 50, 0);

-- Insert Zona Pengiriman
INSERT INTO zona (nama_zona, deskripsi, ongkir) VALUES
('Kota Malang', 'Wilayah Kota Malang dan sekitarnya', 0.00),
('Kabupaten Malang', 'Wilayah Kabupaten Malang', 0.00),
('Kota Batu', 'Wilayah Kota Batu', 0.00),
('Luar Malang Raya', 'Wilayah di luar Malang Raya', 15000.00);

-- Insert Default Admin User
-- Password: 'admin123' (hashed dengan password_hash PHP)
INSERT INTO pengguna (username, password, nama_lengkap, role, status) VALUES
('admin', '$2y$10$8K1p5s1VvqHqYL1YQs7xKOzR6h7L8P3n4Q5w6E7r8T9y0U1i2O3p4', 'Administrator', 'admin', 'aktif');

-- Set default creator/updater ke admin pertama
UPDATE kategori SET created_by = 1, updated_by = 1;
UPDATE produk SET created_by = 1, updated_by = 1;

-- Insert Settings
INSERT INTO setting (setting_key, setting_value, deskripsi) VALUES
('nama_toko', 'Berkat Dinasti', 'Nama toko/usaha'),
('alamat_toko', 'Malang, Jawa Timur', 'Alamat toko'),
('no_wa_toko', '6285122997946', 'Nomor WhatsApp toko'),
('kapasitas_angkut', '150', 'Kapasitas angkut per trip (pcs)'),
('jam_operasional', '07:00 - 17:00', 'Jam operasional toko');

-- =====================================================
-- END OF SCHEMA
-- =====================================================
