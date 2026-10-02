USE rodd1157_berkat_dinasti_db;

-- =====================================================
-- HOTFIX: Procedure transaksi + ongkir zona otomatis
-- Tujuan:
-- 1) Pastikan sp_update_status tersedia
-- 2) Pastikan ongkir otomatis diisi dari zona pelanggan saat insert pesanan
-- =====================================================

DELIMITER //

DROP TRIGGER IF EXISTS trg_before_insert_pesanan//
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
