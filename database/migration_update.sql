-- ========================================================
-- SKRIP MIGRASI KREDENSIAL ADMIN
-- Database: rodd1157_berkat_dinasti_db
-- Username: admin
-- Password: kekuatanadmindinasti
-- ========================================================

UPDATE `pengguna` 
SET `password` = '$2y$10$ZXTnSmkxhA/90O3bJnjame2j6WU5.WgNzRgNUJaupBc.cbsmVWT8q' 
WHERE `username` = 'admin';

-- ========================================================
-- SKRIP MIGRASI SINKRONISASI MASTER PRODUK & PRICELIST BERKAT DINASTI
-- Tanggal: 26 Oktober 2026
-- ========================================================

-- 1. Tambah & Perbarui Master Kategori
INSERT INTO `kategori` (`id_kategori`, `nama_kategori`, `deskripsi`) VALUES
(1, 'Roti Hajatan', 'Roti untuk acara hajatan, syukuran, khitanan, dan acara spesial'),
(2, 'Bolu Premium', 'Bolu premium lembut aneka rasa khas Berkat Dinasti'),
(3, 'Roti Manis', 'Berbagai macam roti manis bijian untuk konsumsi harian')
ON DUPLICATE KEY UPDATE `nama_kategori` = VALUES(`nama_kategori`), `deskripsi` = VALUES(`deskripsi`);

-- 2. Tambah & Perbarui Master Produk
INSERT INTO `produk` (`id_produk`, `id_kategori`, `nama_produk`, `deskripsi`, `shelf_life`, `status`) VALUES
(1, 1, 'Roti Hajatan (9 Pilihan Topping)', 'Roti hajatan bulat 7 kelopak lembut dengan 9 aneka topping (Coco Chip, Keju, Meses, Wijen, Sprinkle)', 7, 'tersedia'),
(2, 2, 'Bolu Pisang Premium', 'Bolu pisang premium bertekstur moist dengan aroma pisang harum khas Berkat Dinasti', 7, 'tersedia'),
(3, 2, 'Bolu Tanpa Pisang Premium', 'Bolu panggang klasik premium tanpa pisang, harum mentega lembut dan gurih manis pas', 7, 'tersedia'),
(4, 3, 'Roti Kopi', 'Roti manis dengan crust aroma kopi harum semerbak ala coffee bun', 7, 'tersedia'),
(5, 3, 'Roti Bijian Besar', 'Roti manis bijian ukuran besar, empuk dan mengenyangkan', 7, 'tersedia'),
(6, 3, 'Roti Bijian Kecil', 'Roti manis bijian ukuran kecil praktis untuk snack harian', 7, 'tersedia')
ON DUPLICATE KEY UPDATE `id_kategori` = VALUES(`id_kategori`), `nama_produk` = VALUES(`nama_produk`), `deskripsi` = VALUES(`deskripsi`), `status` = VALUES(`status`);

-- 3. Tambah & Perbarui Varian Kemasan & Harga Pricelist
INSERT INTO `varian_produk` (`id_varian`, `id_produk`, `nama_varian`, `harga`, `min_order`, `stok`) VALUES
(1, 1, 'Kardus Putih', 10000.00, 1, 100),
(2, 1, 'Kardus Coklat', 10000.00, 1, 100),
(3, 1, 'Kardus Batik', 11000.00, 1, 100),
(4, 1, 'Mika', 11000.00, 1, 100),
(5, 6, 'Satuan', 3000.00, 50, 200),
(6, 2, 'Kardus Coklat', 21000.00, 1, 50),
(7, 2, 'Mika', 22000.00, 1, 50),
(8, 3, 'Kardus Putih', 19000.00, 1, 50),
(9, 3, 'Mika', 20000.00, 1, 50),
(10, 4, 'Satuan', 4000.00, 50, 100),
(11, 5, 'Satuan', 4000.00, 20, 100)
ON DUPLICATE KEY UPDATE `id_produk` = VALUES(`id_produk`), `nama_varian` = VALUES(`nama_varian`), `harga` = VALUES(`harga`), `min_order` = VALUES(`min_order`);

