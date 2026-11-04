-- ========================================================
-- SKRIP MIGRASI PENAMBAHAN FITUR TOPPING ROTI HAJATAN
-- Sistem Informasi Berkat Dinasti
-- Database: rodd1157_berkat_dinasti_db / ucie5797_berkat_dinasti_db / berkat_dinasti
-- Tanggal: 4 November 2026
-- ========================================================

-- 1. Tambah Kolom 'topping' di tabel `detail_pesanan`
ALTER TABLE `detail_pesanan` 
ADD COLUMN `topping` VARCHAR(150) NULL DEFAULT NULL AFTER `id_varian`;

-- 2. Perbarui View `v_detail_pesanan_produk` agar menyertakan kolom topping
DROP VIEW IF EXISTS `v_detail_pesanan_produk`;
CREATE ALGORITHM=UNDEFINED DEFINER=CURRENT_USER SQL SECURITY DEFINER VIEW `v_detail_pesanan_produk` AS 
SELECT 
    `dp`.`id_detail` AS `id_detail`,
    `dp`.`id_pesanan` AS `id_pesanan`,
    `p`.`no_invoice` AS `no_invoice`,
    `pr`.`nama_produk` AS `nama_produk`,
    `vp`.`nama_varian` AS `nama_varian`,
    `dp`.`topping` AS `topping`,
    `dp`.`qty` AS `qty`,
    `dp`.`harga_satuan` AS `harga_satuan`,
    `dp`.`subtotal` AS `subtotal`
FROM (((`detail_pesanan` `dp` 
    JOIN `pesanan` `p` ON (`dp`.`id_pesanan` = `p`.`id_pesanan`)) 
    JOIN `varian_produk` `vp` ON (`dp`.`id_varian` = `vp`.`id_varian`)) 
    JOIN `produk` `pr` ON (`vp`.`id_produk` = `pr`.`id_produk`));
