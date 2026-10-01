-- =====================================================
-- DATABASE FIX: Transaction & Payment Validation
-- Untuk: rodd1157_berkat_dinasti_db
-- Tanggal: 2026-04-24
-- =====================================================

USE rodd1157_berkat_dinasti_db;

-- =====================================================
-- FIX 1: Tambah enum 'hutang' di status_bayar
-- =====================================================
ALTER TABLE pesanan 
    MODIFY COLUMN status_bayar ENUM('belum_bayar', 'dp', 'lunas', 'hutang') DEFAULT 'belum_bayar';

-- =====================================================
-- FIX 2: Trigger Validasi Status Transition (Forward-only)
-- =====================================================
DELIMITER //

DROP TRIGGER IF EXISTS trg_before_update_pesanan_status//
CREATE TRIGGER trg_before_update_pesanan_status
BEFORE UPDATE ON pesanan
FOR EACH ROW
BEGIN
    -- Validasi hanya untuk perubahan status
    IF OLD.status != NEW.status THEN
        -- Jika status lama adalah batal, tidak bisa diubah
        IF OLD.status = 'batal' THEN
            SIGNAL SQLSTATE '45000'
            SET MESSAGE_TEXT = 'Pesanan yang sudah dibatalkan tidak dapat diubah statusnya';
        END IF;
        
        -- Validasi alur forward-only: pending -> proses -> kirim -> selesai
        IF OLD.status = 'pending' AND NEW.status NOT IN ('proses', 'batal') THEN
            SIGNAL SQLSTATE '45000'
            SET MESSAGE_TEXT = 'Status pesanan hanya bisa berubah ke proses atau dibatalkan';
        END IF;
        
        IF OLD.status = 'proses' AND NEW.status NOT IN ('kirim', 'batal') THEN
            SIGNAL SQLSTATE '45000'
            SET MESSAGE_TEXT = 'Status pesanan hanya bisa berubah ke kirim atau dibatalkan';
        END IF;
        
        IF OLD.status = 'kirim' AND NEW.status NOT IN ('selesai', 'batal') THEN
            SIGNAL SQLSTATE '45000'
            SET MESSAGE_TEXT = 'Status pesanan hanya bisa berubah ke selesai atau dibatalkan';
        END IF;
        
        IF OLD.status = 'selesai' THEN
            SIGNAL SQLSTATE '45000'
            SET MESSAGE_TEXT = 'Pesanan yang sudah selesai tidak dapat diubah statusnya';
        END IF;
    END IF;
    
    -- Validasi perubahan status_bayar
    IF OLD.status_bayar != NEW.status_bayar THEN
        -- Jika pesanan dibatalkan, status_bayar harus kembali ke belum_bayar
        IF NEW.status = 'batal' AND NEW.status_bayar NOT IN ('belum_bayar', 'lunas') THEN
            SET NEW.status_bayar = 'belum_bayar';
        END IF;
    END IF;
END//

DELIMITER ;

-- =====================================================
-- FIX 3: Trigger Pembatalan - Return Stock & Reverse Kas
-- =====================================================
DELIMITER //

DROP TRIGGER IF EXISTS trg_after_cancel_pesanan//
CREATE TRIGGER trg_after_cancel_pesanan
AFTER UPDATE ON pesanan
FOR EACH ROW
BEGIN
    -- Jika pesanan dibatalkan dari status lain
    IF NEW.status = 'batal' AND OLD.status != 'batal' THEN
        DECLARE v_id_varian INT;
        DECLARE v_qty INT;
        
        -- Kembalikan stock dari detail_pesanan
        DECLARE done INT DEFAULT FALSE;
        DECLARE cur CURSOR FOR 
            SELECT id_varian, qty FROM detail_pesanan WHERE id_pesanan = NEW.id_pesanan;
        DECLARE CONTINUE HANDLER FOR NOT FOUND SET done = TRUE;
        
        OPEN cur;
        cancel_loop: LOOP
            FETCH cur INTO v_id_varian, v_qty;
            IF done THEN
                LEAVE cancel_loop;
            END IF;
            
            UPDATE varian_produk
            SET stok = stok + v_qty
            WHERE id_varian = v_id_varian;
        END LOOP;
        CLOSE cur;
        
        -- Catat reverse kas jika ada pembayaran yang sudah dilakukan
        IF EXISTS (SELECT 1 FROM pembayaran WHERE id_pesanan = NEW.id_pesanan) THEN
            INSERT INTO transaksi_kas (
                tanggal,
                jenis,
                sumber_tipe,
                sumber_id,
                nominal,
                keterangan,
                created_by
            )
            SELECT 
                NOW(),
                'keluar',
                'pembayaran',
                id_pembayaran,
                -jumlah,
                CONCAT('Refund pembatalan pesanan #', NEW.id_pesanan),
                received_by
            FROM pembayaran
            WHERE id_pesanan = NEW.id_pesanan;
        END IF;
        
        -- Reset hutang pelanggan jika ada
        IF OLD.status_bayar != 'lunas' THEN
            UPDATE pelanggan 
            SET total_hutang = GREATEST(total_hutang - (NEW.grand_total - COALESCE(
                (SELECT SUM(jumlah) FROM pembayaran WHERE id_pesanan = NEW.id_pesanan), 0
            )), 0)
            WHERE id_pelanggan = NEW.id_pelanggan;
        END IF;
    END IF;
    
    -- Jika pesanan selesai dan belum lunas, update ke hutang
    IF NEW.status = 'selesai' AND OLD.status != 'selesai' AND NEW.status_bayar != 'lunas' THEN
        UPDATE pesanan SET status_bayar = 'hutang' WHERE id_pesanan = NEW.id_pesanan;
    END IF;
END//

DELIMITER ;

-- =====================================================
-- FIX 4: Trigger Validasi Pembayaran (Prevent Overpayment)
-- =====================================================
DELIMITER //

DROP TRIGGER IF EXISTS trg_before_insert_pembayaran//
CREATE TRIGGER trg_before_insert_pembayaran
BEFORE INSERT ON pembayaran
FOR EACH ROW
BEGIN
    DECLARE v_status VARCHAR(20);
    DECLARE v_status_bayar VARCHAR(20);
    DECLARE v_grand_total DECIMAL(15,2);
    DECLARE v_sudah_dibayar DECIMAL(15,2);
    DECLARE v_sisa DECIMAL(15,2);
    
    -- Ambil status pesanan
    SELECT status, status_bayar, grand_total 
    INTO v_status, v_status_bayar, v_grand_total
    FROM pesanan 
    WHERE id_pesanan = NEW.id_pesanan;
    
    -- Validasi: Pesanan tidak boleh sudah dibatalkan
    IF v_status = 'batal' THEN
        SIGNAL SQLSTATE '45000'
        SET MESSAGE_TEXT = 'Tidak dapat melakukan pembayaran pada pesanan yang dibatalkan';
    END IF;
    
    -- Validasi: Tidak bisa membayar jika sudah lunas
    IF v_status_bayar = 'lunas' THEN
        SIGNAL SQLSTATE '45000'
        SET MESSAGE_TEXT = 'Pesanan sudah lunas, tidak perlu pembayaran lagi';
    END IF;
    
    -- Validasi: Tidak bisa membayar jika sudah selesai tapi belum lunas (harus lunas dulu)
    IF v_status = 'selesai' AND v_status_bayar != 'lunas' THEN
        SIGNAL SQLSTATE '45000'
        SET MESSAGE_TEXT = 'Pesanan sudah selesai, selesaikan pembayaran terlebih dahulu';
    END IF;
    
    -- Validasi: Tidak boleh overpayment
    SELECT COALESCE(SUM(jumlah), 0) INTO v_sudah_dibayar
    FROM pembayaran 
    WHERE id_pesanan = NEW.id_pesanan;
    
    SET v_sisa = v_grand_total - v_sudah_dibayar;
    
    IF NEW.jumlah > v_sisa THEN
        SIGNAL SQLSTATE '45000'
        SET MESSAGE_TEXT = CONCAT('Pembayaran melebihi sisa tagihan. Sisa: ', v_sisa);
    END IF;
END//

DELIMITER ;

-- =====================================================
-- FIX 5: Update Stored Procedure sp_update_status dengan Validasi
-- =====================================================
DELIMITER //

DROP PROCEDURE IF EXISTS sp_update_status//

CREATE PROCEDURE sp_update_status(
    IN p_id_pesanan INT,
    IN p_status VARCHAR(20)
)
BEGIN
    DECLARE v_current_status VARCHAR(20);
    
    -- Ambil status saat ini
    SELECT status INTO v_current_status
    FROM pesanan
    WHERE id_pesanan = p_id_pesanan;
    
    -- Validasi alur status
    IF v_current_status = 'batal' THEN
        SIGNAL SQLSTATE '45000'
        SET MESSAGE_TEXT = 'Pesanan sudah dibatalkan';
    END IF;
    
    IF v_current_status = 'selesai' THEN
        SIGNAL SQLSTATE '45000'
        SET MESSAGE_TEXT = 'Pesanan sudah selesai';
    END IF;
    
    -- Validasi transisi yang diperbolehkan
    IF v_current_status = 'pending' AND p_status NOT IN ('proses', 'batal') THEN
        SIGNAL SQLSTATE '45000'
        SET MESSAGE_TEXT = 'Dari pending hanya bisa ke proses atau batal';
    END IF;
    
    IF v_current_status = 'proses' AND p_status NOT IN ('kirim', 'batal') THEN
        SIGNAL SQLSTATE '45000'
        SET MESSAGE_TEXT = 'Dari proses hanya bisa ke kirim atau batal';
    END IF;
    
    IF v_current_status = 'kirim' AND p_status NOT IN ('selesai', 'batal') THEN
        SIGNAL SQLSTATE '45000'
        SET MESSAGE_TEXT = 'Dari kirim hanya bisa ke selesai atau batal';
    END IF;
    
    -- Update status
    UPDATE pesanan 
    SET status = p_status 
    WHERE id_pesanan = p_id_pesanan;
    
    -- Update pengiriman jika perlu
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

-- =====================================================
-- FIX 6: Update Stored Procedure sp_bayar dengan Validasi
-- =====================================================
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
    DECLARE v_sudah_dibayar DECIMAL(15,2);
    DECLARE v_sisa DECIMAL(15,2);
    
    -- Ambil data pesanan
    SELECT status, status_bayar, grand_total 
    INTO v_status, v_status_bayar, v_grand_total
    FROM pesanan 
    WHERE id_pesanan = p_id_pesanan;
    
    -- Validasi pesanan tidak dibatalkan
    IF v_status = 'batal' THEN
        SIGNAL SQLSTATE '45000'
        SET MESSAGE_TEXT = 'Tidak dapat membayar pesanan yang dibatalkan';
    END IF;
    
    -- Validasi belum lunas
    IF v_status_bayar = 'lunas' THEN
        SIGNAL SQLSTATE '45000'
        SET MESSAGE_TEXT = 'Pesanan sudah lunas';
    END IF;
    
    -- Validasi tidak overpayment
    SELECT COALESCE(SUM(jumlah), 0) INTO v_sudah_dibayar
    FROM pembayaran 
    WHERE id_pesanan = p_id_pesanan;
    
    SET v_sisa = v_grand_total - v_sudah_dibayar;
    
    IF p_jumlah > v_sisa THEN
        SIGNAL SQLSTATE '45000'
        SET MESSAGE_TEXT = CONCAT('Pembayaran melebihi sisa tagihan: ', v_sisa);
    END IF;
    
    -- Insert pembayaran
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

-- =====================================================
-- FIX 7: View untuk melihat sisa tagihan (helper)
-- =====================================================
DROP VIEW IF EXISTS v_sisa_tagihan//

CREATE VIEW v_sisa_tagihan AS
SELECT 
    p.id_pesanan,
    p.no_invoice,
    p.grand_total AS total_tagihan,
    COALESCE(SUM(pb.jumlah), 0) AS sudah_dibayar,
    p.grand_total - COALESCE(SUM(pb.jumlah), 0) AS sisa_tagihan,
    CASE 
        WHEN p.grand_total - COALESCE(SUM(pb.jumlah), 0) <= 0 THEN 'lunas'
        WHEN COALESCE(SUM(pb.jumlah), 0) > 0 THEN 'dp'
        ELSE 'belum_bayar'
    END AS status_bayar_terhitung
FROM pesanan p
LEFT JOIN pembayaran pb ON p.id_pesanan = pb.id_pesanan
WHERE p.status != 'batal'
GROUP BY p.id_pesanan, p.no_invoice, p.grand_total;

-- =====================================================
-- END OF FIX
-- =====================================================