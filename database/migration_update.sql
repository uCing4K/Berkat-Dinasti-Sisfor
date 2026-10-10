-- ========================================================
-- SKRIP MIGRASI / UPDATE DATABASE SHARED HOSTING
-- Database: rodd1157_berkat_dinasti_db
-- Tanggal: 10 Oktober 2026
-- ========================================================

-- 1. Perbarui Kredensial Akun Admin ke password baru: kekuatanadmindinasti
UPDATE `pengguna` 
SET `password` = '$2y$10$ZXTnSmkxhA/90O3bJnjame2j6WU5.WgNzRgNUJaupBc.cbsmVWT8q' 
WHERE `username` = 'admin';

-- 2. Tambahkan Tabel `zona` jika belum ada
CREATE TABLE IF NOT EXISTS `zona` (
  `id_zona` int(11) NOT NULL AUTO_INCREMENT,
  `nama_zona` varchar(100) NOT NULL,
  `deskripsi` text DEFAULT NULL,
  `ongkir` decimal(10,2) DEFAULT 0.00,
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id_zona`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

-- Masukkan Master Data Zona Pengiriman
INSERT IGNORE INTO `zona` (`id_zona`, `nama_zona`, `deskripsi`, `ongkir`) VALUES
(1, 'Kota Malang', 'Wilayah Kota Malang dan sekitarnya', 0.00),
(2, 'Kabupaten Malang', 'Wilayah Kabupaten Malang', 0.00),
(3, 'Kota Batu', 'Wilayah Kota Batu', 0.00),
(4, 'Luar Malang Raya', 'Wilayah di luar Malang Raya', 15000.00);

-- 3. Tambahkan kolom `id_zona` pada tabel `pelanggan`
ALTER TABLE `pelanggan` 
  ADD COLUMN IF NOT EXISTS `id_zona` int(11) DEFAULT NULL AFTER `id_pelanggan`;

-- 4. Tambahkan kolom `ongkir` pada tabel `pesanan`
ALTER TABLE `pesanan` 
  ADD COLUMN IF NOT EXISTS `ongkir` decimal(10,2) DEFAULT 0.00 AFTER `total_harga`;

-- 5. Tambahkan kolom `gambar` pada tabel `produk`
ALTER TABLE `produk` 
  ADD COLUMN IF NOT EXISTS `gambar` varchar(255) DEFAULT NULL AFTER `deskripsi`;

-- 6. Perbarui View `v_pesanan_lengkap` agar menyertakan `ongkir` dan `nama_zona`
CREATE OR REPLACE VIEW `v_pesanan_lengkap` AS 
SELECT 
    `p`.`id_pesanan` AS `id_pesanan`, 
    `p`.`no_invoice` AS `no_invoice`, 
    `p`.`tgl_pesan` AS `tgl_pesan`, 
    `p`.`tgl_kirim` AS `tgl_kirim`, 
    `p`.`waktu_kirim` AS `waktu_kirim`, 
    `p`.`total_harga` AS `total_harga`, 
    `p`.`ongkir` AS `ongkir`, 
    `p`.`grand_total` AS `grand_total`, 
    `p`.`status` AS `status`, 
    `p`.`status_bayar` AS `status_bayar`, 
    `p`.`metode_bayar` AS `metode_bayar`, 
    `p`.`catatan` AS `catatan`, 
    `pel`.`id_pelanggan` AS `id_pelanggan`, 
    `pel`.`nama` AS `nama_pelanggan`, 
    `pel`.`alamat` AS `alamat`, 
    `pel`.`no_wa` AS `no_wa`, 
    `pel`.`tipe` AS `tipe_pelanggan`, 
    `z`.`nama_zona` AS `nama_zona`, 
    `pg`.`status` AS `status_pengiriman`, 
    `pg`.`driver` AS `driver` 
FROM (((`pesanan` `p` 
    JOIN `pelanggan` `pel` ON(`p`.`id_pelanggan` = `pel`.`id_pelanggan`)) 
    LEFT JOIN `zona` `z` ON(`pel`.`id_zona` = `z`.`id_zona`)) 
    LEFT JOIN `pengiriman` `pg` ON(`p`.`id_pesanan` = `pg`.`id_pesanan`));
