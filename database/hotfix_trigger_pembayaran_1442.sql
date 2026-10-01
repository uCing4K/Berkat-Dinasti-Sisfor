USE rodd1157_berkat_dinasti_db;

DELIMITER //

DROP TRIGGER IF EXISTS trg_before_insert_pembayaran//
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

    -- Penting: tanpa FOR UPDATE pada tabel pembayaran di dalam trigger pembayaran
    -- untuk menghindari error MySQL 1442.
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
