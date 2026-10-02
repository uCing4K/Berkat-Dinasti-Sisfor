USE rodd1157_berkat_dinasti_db;

-- Jalankan file ini di database yang sudah existing (production/test)
-- untuk memperbaiki anomali stok varian saat detail pesanan berubah.

DROP TRIGGER IF EXISTS trg_before_insert_detail;
DROP TRIGGER IF EXISTS trg_after_insert_detail;
DROP TRIGGER IF EXISTS trg_before_update_detail;
DROP TRIGGER IF EXISTS trg_after_update_detail;
DROP TRIGGER IF EXISTS trg_after_delete_detail;

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

-- Opsional verifikasi cepat (ganti nilai sesuai data uji):
-- SELECT id_varian, stok FROM varian_produk WHERE id_varian = 1;
