-- ========================================================
-- SKRIP MIGRASI KREDENSIAL ADMIN
-- Database: rodd1157_berkat_dinasti_db
-- Username: admin
-- Password: kekuatanadmindinasti
-- ========================================================

UPDATE `pengguna` 
SET `password` = '$2y$10$ZXTnSmkxhA/90O3bJnjame2j6WU5.WgNzRgNUJaupBc.cbsmVWT8q' 
WHERE `username` = 'admin';
