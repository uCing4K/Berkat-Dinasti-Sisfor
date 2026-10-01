DELIMITER //
CREATE TRIGGER trg_before_insert_pesanan
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
