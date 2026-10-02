-- phpMyAdmin SQL Dump
-- version 5.2.2
-- https://www.phpmyadmin.net/
--
-- Host: localhost:3306
-- Generation Time: Apr 24, 2026 at 05:46 PM
-- Server version: 11.4.10-MariaDB-cll-lve
-- PHP Version: 8.4.20

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `rodd1157_berkat_dinasti_db`
--

DELIMITER $$
--
-- Procedures
--
CREATE DEFINER=`rodd1157`@`localhost` PROCEDURE `sp_add_detail_pesanan` (IN `p_id_pesanan` INT, IN `p_id_varian` INT, IN `p_qty` INT)   BEGIN
    DECLARE v_harga DECIMAL(10,2);
    DECLARE v_min_order INT;
    
    -- Get price and min_order from varian
    SELECT harga, min_order INTO v_harga, v_min_order
    FROM varian_produk
    WHERE id_varian = p_id_varian;
    
    -- Check minimum order
    IF p_qty < v_min_order THEN
        SIGNAL SQLSTATE '45000' 
        SET MESSAGE_TEXT = 'Quantity kurang dari minimum order';
    END IF;
    
    INSERT INTO detail_pesanan (id_pesanan, id_varian, qty, harga_satuan, subtotal)
    VALUES (p_id_pesanan, p_id_varian, p_qty, v_harga, p_qty * v_harga);
END$$

CREATE DEFINER=`rodd1157`@`localhost` PROCEDURE `sp_bayar` (IN `p_id_pesanan` INT, IN `p_jumlah` DECIMAL(15,2), IN `p_metode` VARCHAR(20), IN `p_bukti` VARCHAR(255), IN `p_keterangan` TEXT, IN `p_received_by` INT)   BEGIN
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
END$$

CREATE DEFINER=`rodd1157`@`localhost` PROCEDURE `sp_create_pesanan` (IN `p_id_pelanggan` INT, IN `p_tgl_kirim` DATE, IN `p_waktu_kirim` TIME, IN `p_metode_bayar` VARCHAR(20), IN `p_catatan` TEXT, OUT `p_id_pesanan` INT)   BEGIN
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
END$$

CREATE DEFINER=`rodd1157`@`localhost` PROCEDURE `sp_laporan_penjualan` (IN `p_start_date` DATE, IN `p_end_date` DATE)   BEGIN
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
END$$

CREATE DEFINER=`rodd1157`@`localhost` PROCEDURE `sp_update_status` (IN `p_id_pesanan` INT, IN `p_status` VARCHAR(20))   BEGIN
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
END$$

DELIMITER ;

-- --------------------------------------------------------

--
-- Table structure for table `detail_pesanan`
--

CREATE TABLE `detail_pesanan` (
  `id_detail` int(11) NOT NULL,
  `id_pesanan` int(11) NOT NULL,
  `id_varian` int(11) NOT NULL,
  `qty` int(11) NOT NULL,
  `harga_satuan` decimal(10,2) NOT NULL,
  `subtotal` decimal(15,2) NOT NULL,
  `created_at` timestamp NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

--
-- Dumping data for table `detail_pesanan`
--

INSERT INTO `detail_pesanan` (`id_detail`, `id_pesanan`, `id_varian`, `qty`, `harga_satuan`, `subtotal`, `created_at`) VALUES
(1, 1, 6, 2, 10000.00, 20000.00, '2026-04-24 10:31:51'),
(2, 1, 7, 1, 15000.00, 15000.00, '2026-04-24 10:31:51');

--
-- Triggers `detail_pesanan`
--
DELIMITER $$
CREATE TRIGGER `trg_after_delete_detail` AFTER DELETE ON `detail_pesanan` FOR EACH ROW BEGIN
    DECLARE total DECIMAL(15,2);
    DECLARE ongkir_val DECIMAL(10,2);
    
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
END
$$
DELIMITER ;
DELIMITER $$
CREATE TRIGGER `trg_after_insert_detail` AFTER INSERT ON `detail_pesanan` FOR EACH ROW BEGIN
    DECLARE total DECIMAL(15,2);
    DECLARE ongkir_val DECIMAL(10,2);
    
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
END
$$
DELIMITER ;
DELIMITER $$
CREATE TRIGGER `trg_before_insert_detail` BEFORE INSERT ON `detail_pesanan` FOR EACH ROW BEGIN
    SET NEW.subtotal = NEW.qty * NEW.harga_satuan;
END
$$
DELIMITER ;

-- --------------------------------------------------------

--
-- Table structure for table `kategori`
--

CREATE TABLE `kategori` (
  `id_kategori` int(11) NOT NULL,
  `nama_kategori` varchar(100) NOT NULL,
  `deskripsi` text DEFAULT NULL,
  `created_by` int(11) DEFAULT NULL,
  `updated_by` int(11) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

--
-- Dumping data for table `kategori`
--

INSERT INTO `kategori` (`id_kategori`, `nama_kategori`, `deskripsi`, `created_by`, `updated_by`, `created_at`, `updated_at`) VALUES
(1, 'Roti Hajatan', 'Roti untuk acara hajatan, syukuran, dan acara spesial', 1, 1, '2026-04-24 10:30:56', '2026-04-24 10:30:56'),
(2, 'Roti Manis', 'Berbagai macam roti manis untuk konsumsi sehari-hari', 1, 1, '2026-04-24 10:30:56', '2026-04-24 10:30:56'),
(3, 'Kategori UJI_20260423', 'Kategori dummy integrasi', 2, 2, '2026-04-24 10:31:51', '2026-04-24 10:31:51');

-- --------------------------------------------------------

--
-- Table structure for table `pelanggan`
--

CREATE TABLE `pelanggan` (
  `id_pelanggan` int(11) NOT NULL,
  `id_zona` int(11) DEFAULT NULL,
  `nama` varchar(150) NOT NULL,
  `alamat` text NOT NULL,
  `no_wa` varchar(20) NOT NULL,
  `tipe` enum('retail','reseller') DEFAULT 'retail',
  `total_transaksi` decimal(15,2) DEFAULT 0.00 COMMENT 'Akumulasi total belanja',
  `total_hutang` decimal(15,2) DEFAULT 0.00 COMMENT 'Total piutang belum lunas',
  `catatan` text DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

--
-- Dumping data for table `pelanggan`
--

INSERT INTO `pelanggan` (`id_pelanggan`, `id_zona`, `nama`, `alamat`, `no_wa`, `tipe`, `total_transaksi`, `total_hutang`, `catatan`, `created_at`, `updated_at`) VALUES
(1, 5, 'Pelanggan UJI_20260423', 'Jl. Uji Integrasi No. 1', '6281166359549', 'retail', 47000.00, 47000.00, 'Data dummy UJI_20260423', '2026-04-24 10:31:51', '2026-04-24 10:31:51');

-- --------------------------------------------------------

--
-- Table structure for table `pembayaran`
--

CREATE TABLE `pembayaran` (
  `id_pembayaran` int(11) NOT NULL,
  `id_pesanan` int(11) NOT NULL,
  `jumlah` decimal(15,2) NOT NULL,
  `tgl_bayar` datetime NOT NULL,
  `metode` enum('cash','transfer') NOT NULL,
  `bukti` varchar(255) DEFAULT NULL COMMENT 'Path file bukti transfer',
  `keterangan` text DEFAULT NULL,
  `received_by` int(11) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

--
-- Dumping data for table `pembayaran`
--

INSERT INTO `pembayaran` (`id_pembayaran`, `id_pesanan`, `jumlah`, `tgl_bayar`, `metode`, `bukti`, `keterangan`, `received_by`, `created_at`) VALUES
(1, 1, 10000.00, '2026-04-24 17:31:51', 'transfer', NULL, 'DP UJI_20260423', 2, '2026-04-24 10:31:51'),
(2, 1, 100000.00, '2026-04-24 17:31:51', 'transfer', NULL, 'Pelunasan UJI_20260423', 2, '2026-04-24 10:31:51');

--
-- Triggers `pembayaran`
--
DELIMITER $$
CREATE TRIGGER `trg_after_insert_pembayaran` AFTER INSERT ON `pembayaran` FOR EACH ROW BEGIN
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
END
$$
DELIMITER ;

-- --------------------------------------------------------

--
-- Table structure for table `pengeluaran`
--

CREATE TABLE `pengeluaran` (
  `id_pengeluaran` int(11) NOT NULL,
  `tanggal` date NOT NULL,
  `kategori` enum('bahan_baku','operasional','gaji','transportasi','lainnya') NOT NULL,
  `deskripsi` text NOT NULL,
  `jumlah` decimal(15,2) NOT NULL,
  `created_by` int(11) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

--
-- Dumping data for table `pengeluaran`
--

INSERT INTO `pengeluaran` (`id_pengeluaran`, `tanggal`, `kategori`, `deskripsi`, `jumlah`, `created_by`, `created_at`) VALUES
(1, '2026-04-24', 'operasional', 'Pengeluaran uji UJI_20260423', 2500.00, 2, '2026-04-24 10:31:51');

--
-- Triggers `pengeluaran`
--
DELIMITER $$
CREATE TRIGGER `trg_after_insert_pengeluaran` AFTER INSERT ON `pengeluaran` FOR EACH ROW BEGIN
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
END
$$
DELIMITER ;

-- --------------------------------------------------------

--
-- Table structure for table `pengguna`
--

CREATE TABLE `pengguna` (
  `id_user` int(11) NOT NULL,
  `username` varchar(50) NOT NULL,
  `password` varchar(255) NOT NULL COMMENT 'Hashed password',
  `nama_lengkap` varchar(150) NOT NULL,
  `email` varchar(100) DEFAULT NULL,
  `no_hp` varchar(20) DEFAULT NULL,
  `role` enum('admin','kasir','driver') DEFAULT 'kasir',
  `status` enum('aktif','nonaktif') DEFAULT 'aktif',
  `last_login` datetime DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

--
-- Dumping data for table `pengguna`
--

INSERT INTO `pengguna` (`id_user`, `username`, `password`, `nama_lengkap`, `email`, `no_hp`, `role`, `status`, `last_login`, `created_at`, `updated_at`) VALUES
(1, 'admin', '$2y$10$8K1p5s1VvqHqYL1YQs7xKOzR6h7L8P3n4Q5w6E7r8T9y0U1i2O3p4', 'Administrator', NULL, NULL, 'admin', 'aktif', NULL, '2026-04-24 10:30:56', '2026-04-24 10:30:56'),
(2, 'kasir_UJI_20260423', '$2y$10$dummyhashforintegrationtest0000000000000000000000000000000', 'Kasir UJI_20260423', NULL, NULL, 'kasir', 'aktif', NULL, '2026-04-24 10:31:51', '2026-04-24 10:31:51');

-- --------------------------------------------------------

--
-- Table structure for table `pengiriman`
--

CREATE TABLE `pengiriman` (
  `id_pengiriman` int(11) NOT NULL,
  `id_pesanan` int(11) NOT NULL,
  `tgl_kirim` datetime DEFAULT NULL,
  `tgl_sampai` datetime DEFAULT NULL,
  `status` enum('menunggu','dalam_perjalanan','sampai','gagal') DEFAULT 'menunggu',
  `driver` varchar(100) DEFAULT NULL,
  `catatan` text DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

--
-- Dumping data for table `pengiriman`
--

INSERT INTO `pengiriman` (`id_pengiriman`, `id_pesanan`, `tgl_kirim`, `tgl_sampai`, `status`, `driver`, `catatan`, `created_at`, `updated_at`) VALUES
(1, 1, '2026-04-24 17:31:51', '2026-04-24 17:31:51', 'sampai', NULL, NULL, '2026-04-24 10:31:51', '2026-04-24 10:31:51');

-- --------------------------------------------------------

--
-- Table structure for table `pesanan`
--

CREATE TABLE `pesanan` (
  `id_pesanan` int(11) NOT NULL,
  `id_pelanggan` int(11) NOT NULL,
  `no_invoice` varchar(50) NOT NULL,
  `tgl_pesan` date NOT NULL,
  `tgl_kirim` date DEFAULT NULL COMMENT 'Deadline/jadwal pengiriman',
  `waktu_kirim` time DEFAULT NULL,
  `total_harga` decimal(15,2) NOT NULL DEFAULT 0.00,
  `ongkir` decimal(10,2) DEFAULT 0.00,
  `grand_total` decimal(15,2) NOT NULL DEFAULT 0.00,
  `status` enum('pending','proses','kirim','selesai','batal') DEFAULT 'pending',
  `status_bayar` enum('belum_bayar','dp','lunas') DEFAULT 'belum_bayar',
  `metode_bayar` enum('cash','transfer') DEFAULT 'cash',
  `catatan` text DEFAULT NULL,
  `created_by` int(11) DEFAULT NULL,
  `updated_by` int(11) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

--
-- Dumping data for table `pesanan`
--

INSERT INTO `pesanan` (`id_pesanan`, `id_pelanggan`, `no_invoice`, `tgl_pesan`, `tgl_kirim`, `waktu_kirim`, `total_harga`, `ongkir`, `grand_total`, `status`, `status_bayar`, `metode_bayar`, `catatan`, `created_by`, `updated_by`, `created_at`, `updated_at`) VALUES
(1, 1, 'INV-20260424-0001', '2026-04-24', '2026-04-25', '10:00:00', 35000.00, 12000.00, 47000.00, 'selesai', 'lunas', 'transfer', 'Catatan UJI_20260423', NULL, NULL, '2026-04-24 10:31:51', '2026-04-24 10:31:51');

--
-- Triggers `pesanan`
--
DELIMITER $$
CREATE TRIGGER `trg_after_insert_pesanan` AFTER INSERT ON `pesanan` FOR EACH ROW BEGIN
    INSERT INTO pengiriman (id_pesanan, status)
    VALUES (NEW.id_pesanan, 'menunggu');
    
    INSERT INTO tracking_log (id_pesanan, status, keterangan)
    VALUES (NEW.id_pesanan, 'pending', 'Pesanan baru dibuat');
END
$$
DELIMITER ;
DELIMITER $$
CREATE TRIGGER `trg_after_pesanan_selesai` AFTER UPDATE ON `pesanan` FOR EACH ROW BEGIN
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
END
$$
DELIMITER ;
DELIMITER $$
CREATE TRIGGER `trg_after_update_pesanan` AFTER UPDATE ON `pesanan` FOR EACH ROW BEGIN
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
END
$$
DELIMITER ;
DELIMITER $$
CREATE TRIGGER `trg_before_insert_pesanan` BEFORE INSERT ON `pesanan` FOR EACH ROW BEGIN
    DECLARE next_num INT;
    DECLARE today_date VARCHAR(8);
    
    SET today_date = DATE_FORMAT(NEW.tgl_pesan, '%Y%m%d');
    
    SELECT COALESCE(MAX(CAST(SUBSTRING(no_invoice, -4) AS UNSIGNED)), 0) + 1
    INTO next_num
    FROM pesanan
    WHERE no_invoice LIKE CONCAT('INV-', today_date, '%');
    
    SET NEW.no_invoice = CONCAT('INV-', today_date, '-', LPAD(next_num, 4, '0'));
END
$$
DELIMITER ;

-- --------------------------------------------------------

--
-- Table structure for table `produk`
--

CREATE TABLE `produk` (
  `id_produk` int(11) NOT NULL,
  `id_kategori` int(11) NOT NULL,
  `nama_produk` varchar(150) NOT NULL,
  `deskripsi` text DEFAULT NULL,
  `gambar` varchar(255) DEFAULT NULL,
  `shelf_life` int(11) DEFAULT 7 COMMENT 'Masa kedaluwarsa dalam hari',
  `status` enum('tersedia','tidak_tersedia') DEFAULT 'tersedia',
  `created_by` int(11) DEFAULT NULL,
  `updated_by` int(11) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

--
-- Dumping data for table `produk`
--

INSERT INTO `produk` (`id_produk`, `id_kategori`, `nama_produk`, `deskripsi`, `gambar`, `shelf_life`, `status`, `created_by`, `updated_by`, `created_at`, `updated_at`) VALUES
(1, 1, 'Roti Hajatan Isi 6 Rasa', 'Paket roti hajatan berisi 6 varian rasa dalam satu kemasan', NULL, 7, 'tersedia', 1, 1, '2026-04-24 10:30:56', '2026-04-24 10:30:56'),
(2, 2, 'Roti Kopi', 'Roti dengan topping kopi yang lezat', NULL, 7, 'tersedia', 1, 1, '2026-04-24 10:30:56', '2026-04-24 10:30:56'),
(3, 2, 'Roti Bijian Besar', 'Roti bijian ukuran besar, cocok untuk sarapan', NULL, 7, 'tersedia', 1, 1, '2026-04-24 10:30:56', '2026-04-24 10:30:56'),
(4, 2, 'Roti Bijian Kecil', 'Roti bijian ukuran kecil, praktis dibawa', NULL, 7, 'tersedia', 1, 1, '2026-04-24 10:30:56', '2026-04-24 10:30:56'),
(5, 3, 'Produk UJI_20260423', 'Produk dummy untuk uji integrasi database', NULL, 7, 'tersedia', 2, 2, '2026-04-24 10:31:51', '2026-04-24 10:31:51');

-- --------------------------------------------------------

--
-- Table structure for table `setting`
--

CREATE TABLE `setting` (
  `id_setting` int(11) NOT NULL,
  `setting_key` varchar(100) NOT NULL,
  `setting_value` text DEFAULT NULL,
  `deskripsi` text DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

--
-- Dumping data for table `setting`
--

INSERT INTO `setting` (`id_setting`, `setting_key`, `setting_value`, `deskripsi`, `updated_at`) VALUES
(1, 'nama_toko', 'Berkat Dinasti', 'Nama toko/usaha', '2026-04-24 10:30:56'),
(2, 'alamat_toko', 'Malang, Jawa Timur', 'Alamat toko', '2026-04-24 10:30:56'),
(3, 'no_wa_toko', '6285122997946', 'Nomor WhatsApp toko', '2026-04-24 10:30:56'),
(4, 'kapasitas_angkut', '150', 'Kapasitas angkut per trip (pcs)', '2026-04-24 10:30:56'),
(5, 'jam_operasional', '07:00 - 17:00', 'Jam operasional toko', '2026-04-24 10:30:56');

-- --------------------------------------------------------

--
-- Table structure for table `tracking_log`
--

CREATE TABLE `tracking_log` (
  `id_log` int(11) NOT NULL,
  `id_pesanan` int(11) NOT NULL,
  `status` varchar(50) NOT NULL,
  `keterangan` text DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

--
-- Dumping data for table `tracking_log`
--

INSERT INTO `tracking_log` (`id_log`, `id_pesanan`, `status`, `keterangan`, `created_at`) VALUES
(1, 1, 'pending', 'Pesanan baru dibuat', '2026-04-24 10:31:51'),
(2, 1, 'kirim', 'Status berubah dari \"pending\" ke \"kirim\"', '2026-04-24 10:31:51'),
(3, 1, 'selesai', 'Status berubah dari \"kirim\" ke \"selesai\"', '2026-04-24 10:31:51'),
(4, 1, 'bayar_dp', 'Status pembayaran berubah dari \"belum_bayar\" ke \"dp\"', '2026-04-24 10:31:51'),
(5, 1, 'bayar_lunas', 'Status pembayaran berubah dari \"dp\" ke \"lunas\"', '2026-04-24 10:31:51');

-- --------------------------------------------------------

--
-- Table structure for table `transaksi_kas`
--

CREATE TABLE `transaksi_kas` (
  `id_transaksi` int(11) NOT NULL,
  `tanggal` datetime NOT NULL DEFAULT current_timestamp(),
  `jenis` enum('masuk','keluar') NOT NULL,
  `sumber_tipe` enum('pembayaran','pengeluaran','penyesuaian') NOT NULL,
  `sumber_id` int(11) DEFAULT NULL,
  `nominal` decimal(15,2) NOT NULL,
  `keterangan` text DEFAULT NULL,
  `dibuat_oleh` int(11) DEFAULT NULL,
  `created_by` int(11) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

--
-- Dumping data for table `transaksi_kas`
--

INSERT INTO `transaksi_kas` (`id_transaksi`, `tanggal`, `jenis`, `sumber_tipe`, `sumber_id`, `nominal`, `keterangan`, `dibuat_oleh`, `created_by`, `created_at`) VALUES
(1, '2026-04-24 17:31:51', 'masuk', 'pembayaran', 1, 10000.00, 'Pembayaran untuk pesanan #1', NULL, 2, '2026-04-24 10:31:51'),
(2, '2026-04-24 17:31:51', 'masuk', 'pembayaran', 2, 100000.00, 'Pembayaran untuk pesanan #1', NULL, 2, '2026-04-24 10:31:51'),
(3, '2026-04-24 00:00:00', 'keluar', 'pengeluaran', 1, 2500.00, 'Pengeluaran uji UJI_20260423', NULL, 2, '2026-04-24 10:31:51');

-- --------------------------------------------------------

--
-- Table structure for table `varian_produk`
--

CREATE TABLE `varian_produk` (
  `id_varian` int(11) NOT NULL,
  `id_produk` int(11) NOT NULL,
  `nama_varian` varchar(100) NOT NULL COMMENT 'Contoh: Kardus, Mika, Satuan',
  `harga` decimal(10,2) NOT NULL,
  `min_order` int(11) DEFAULT 1 COMMENT 'Minimal pembelian',
  `stok` int(11) DEFAULT 0,
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

--
-- Dumping data for table `varian_produk`
--

INSERT INTO `varian_produk` (`id_varian`, `id_produk`, `nama_varian`, `harga`, `min_order`, `stok`, `created_at`, `updated_at`) VALUES
(1, 1, 'Kardus Biasa', 9000.00, 1, 0, '2026-04-24 10:30:56', '2026-04-24 10:30:56'),
(2, 1, 'Mika', 10000.00, 1, 0, '2026-04-24 10:30:56', '2026-04-24 10:30:56'),
(3, 2, 'Satuan', 4000.00, 50, 0, '2026-04-24 10:30:56', '2026-04-24 10:30:56'),
(4, 3, 'Satuan', 4000.00, 20, 0, '2026-04-24 10:30:56', '2026-04-24 10:30:56'),
(5, 4, 'Satuan', 3000.00, 50, 0, '2026-04-24 10:30:56', '2026-04-24 10:30:56'),
(6, 5, 'Varian A UJI_20260423', 10000.00, 1, 50, '2026-04-24 10:31:51', '2026-04-24 10:31:51'),
(7, 5, 'Varian B UJI_20260423', 15000.00, 1, 50, '2026-04-24 10:31:51', '2026-04-24 10:31:51');

-- --------------------------------------------------------

--
-- Stand-in structure for view `v_detail_pesanan_produk`
-- (See below for the actual view)
--
CREATE TABLE `v_detail_pesanan_produk` (
`id_detail` int(11)
,`id_pesanan` int(11)
,`no_invoice` varchar(50)
,`nama_produk` varchar(150)
,`nama_varian` varchar(100)
,`qty` int(11)
,`harga_satuan` decimal(10,2)
,`subtotal` decimal(15,2)
);

-- --------------------------------------------------------

--
-- Stand-in structure for view `v_katalog`
-- (See below for the actual view)
--
CREATE TABLE `v_katalog` (
`id_produk` int(11)
,`nama_kategori` varchar(100)
,`nama_produk` varchar(150)
,`deskripsi` text
,`gambar` varchar(255)
,`shelf_life` int(11)
,`status` enum('tersedia','tidak_tersedia')
,`id_varian` int(11)
,`nama_varian` varchar(100)
,`harga` decimal(10,2)
,`min_order` int(11)
,`stok` int(11)
);

-- --------------------------------------------------------

--
-- Stand-in structure for view `v_laporan_harian`
-- (See below for the actual view)
--
CREATE TABLE `v_laporan_harian` (
`tanggal` date
,`total_order` bigint(21)
,`total_penjualan` decimal(37,2)
,`total_lunas` decimal(37,2)
,`total_piutang` decimal(37,2)
);

-- --------------------------------------------------------

--
-- Stand-in structure for view `v_laporan_kas_harian`
-- (See below for the actual view)
--
CREATE TABLE `v_laporan_kas_harian` (
`tanggal` date
,`pemasukan` decimal(37,2)
,`pengeluaran` decimal(37,2)
,`saldo_harian` decimal(37,2)
);

-- --------------------------------------------------------

--
-- Stand-in structure for view `v_pesanan_lengkap`
-- (See below for the actual view)
--
CREATE TABLE `v_pesanan_lengkap` (
`id_pesanan` int(11)
,`no_invoice` varchar(50)
,`tgl_pesan` date
,`tgl_kirim` date
,`waktu_kirim` time
,`total_harga` decimal(15,2)
,`ongkir` decimal(10,2)
,`grand_total` decimal(15,2)
,`status` enum('pending','proses','kirim','selesai','batal')
,`status_bayar` enum('belum_bayar','dp','lunas')
,`metode_bayar` enum('cash','transfer')
,`catatan` text
,`id_pelanggan` int(11)
,`nama_pelanggan` varchar(150)
,`alamat` text
,`no_wa` varchar(20)
,`tipe_pelanggan` enum('retail','reseller')
,`nama_zona` varchar(100)
,`status_pengiriman` enum('menunggu','dalam_perjalanan','sampai','gagal')
,`driver` varchar(100)
);

-- --------------------------------------------------------

--
-- Stand-in structure for view `v_piutang`
-- (See below for the actual view)
--
CREATE TABLE `v_piutang` (
`id_pelanggan` int(11)
,`nama` varchar(150)
,`no_wa` varchar(20)
,`total_hutang` decimal(15,2)
,`jumlah_pesanan_belum_lunas` bigint(21)
,`total_pesanan` decimal(37,2)
,`total_terbayar` decimal(59,2)
,`sisa_hutang` decimal(60,2)
);

-- --------------------------------------------------------

--
-- Stand-in structure for view `v_ringkasan_kas`
-- (See below for the actual view)
--
CREATE TABLE `v_ringkasan_kas` (
`total_masuk` decimal(37,2)
,`total_keluar` decimal(37,2)
,`saldo_akhir` decimal(37,2)
);

-- --------------------------------------------------------

--
-- Table structure for table `zona`
--

CREATE TABLE `zona` (
  `id_zona` int(11) NOT NULL,
  `nama_zona` varchar(100) NOT NULL,
  `deskripsi` text DEFAULT NULL,
  `ongkir` decimal(10,2) DEFAULT 0.00,
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

--
-- Dumping data for table `zona`
--

INSERT INTO `zona` (`id_zona`, `nama_zona`, `deskripsi`, `ongkir`, `created_at`, `updated_at`) VALUES
(1, 'Kota Malang', 'Wilayah Kota Malang dan sekitarnya', 0.00, '2026-04-24 10:30:56', '2026-04-24 10:30:56'),
(2, 'Kabupaten Malang', 'Wilayah Kabupaten Malang', 0.00, '2026-04-24 10:30:56', '2026-04-24 10:30:56'),
(3, 'Kota Batu', 'Wilayah Kota Batu', 0.00, '2026-04-24 10:30:56', '2026-04-24 10:30:56'),
(4, 'Luar Malang Raya', 'Wilayah di luar Malang Raya', 15000.00, '2026-04-24 10:30:56', '2026-04-24 10:30:56'),
(5, 'Zona UJI_20260423', 'Zona uji UJI_20260423', 12000.00, '2026-04-24 10:31:51', '2026-04-24 10:31:51');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `detail_pesanan`
--
ALTER TABLE `detail_pesanan`
  ADD PRIMARY KEY (`id_detail`),
  ADD KEY `idx_pesanan` (`id_pesanan`),
  ADD KEY `idx_varian` (`id_varian`);

--
-- Indexes for table `kategori`
--
ALTER TABLE `kategori`
  ADD PRIMARY KEY (`id_kategori`),
  ADD KEY `idx_created_by` (`created_by`),
  ADD KEY `idx_updated_by` (`updated_by`);

--
-- Indexes for table `pelanggan`
--
ALTER TABLE `pelanggan`
  ADD PRIMARY KEY (`id_pelanggan`),
  ADD KEY `idx_zona` (`id_zona`),
  ADD KEY `idx_tipe` (`tipe`),
  ADD KEY `idx_no_wa` (`no_wa`);

--
-- Indexes for table `pembayaran`
--
ALTER TABLE `pembayaran`
  ADD PRIMARY KEY (`id_pembayaran`),
  ADD KEY `idx_pesanan` (`id_pesanan`),
  ADD KEY `idx_tgl_bayar` (`tgl_bayar`),
  ADD KEY `idx_received_by` (`received_by`);

--
-- Indexes for table `pengeluaran`
--
ALTER TABLE `pengeluaran`
  ADD PRIMARY KEY (`id_pengeluaran`),
  ADD KEY `idx_tanggal` (`tanggal`),
  ADD KEY `idx_kategori` (`kategori`),
  ADD KEY `idx_created_by` (`created_by`);

--
-- Indexes for table `pengguna`
--
ALTER TABLE `pengguna`
  ADD PRIMARY KEY (`id_user`),
  ADD UNIQUE KEY `username` (`username`),
  ADD KEY `idx_username` (`username`),
  ADD KEY `idx_role` (`role`),
  ADD KEY `idx_status` (`status`);

--
-- Indexes for table `pengiriman`
--
ALTER TABLE `pengiriman`
  ADD PRIMARY KEY (`id_pengiriman`),
  ADD UNIQUE KEY `id_pesanan` (`id_pesanan`),
  ADD KEY `idx_pesanan` (`id_pesanan`),
  ADD KEY `idx_status` (`status`),
  ADD KEY `idx_tgl_kirim` (`tgl_kirim`);

--
-- Indexes for table `pesanan`
--
ALTER TABLE `pesanan`
  ADD PRIMARY KEY (`id_pesanan`),
  ADD UNIQUE KEY `no_invoice` (`no_invoice`),
  ADD KEY `idx_pelanggan` (`id_pelanggan`),
  ADD KEY `idx_no_invoice` (`no_invoice`),
  ADD KEY `idx_tgl_pesan` (`tgl_pesan`),
  ADD KEY `idx_tgl_kirim` (`tgl_kirim`),
  ADD KEY `idx_status` (`status`),
  ADD KEY `idx_status_bayar` (`status_bayar`),
  ADD KEY `idx_created_by` (`created_by`),
  ADD KEY `idx_updated_by` (`updated_by`);

--
-- Indexes for table `produk`
--
ALTER TABLE `produk`
  ADD PRIMARY KEY (`id_produk`),
  ADD KEY `idx_kategori` (`id_kategori`),
  ADD KEY `idx_status` (`status`),
  ADD KEY `idx_created_by` (`created_by`),
  ADD KEY `idx_updated_by` (`updated_by`);

--
-- Indexes for table `setting`
--
ALTER TABLE `setting`
  ADD PRIMARY KEY (`id_setting`),
  ADD UNIQUE KEY `setting_key` (`setting_key`);

--
-- Indexes for table `tracking_log`
--
ALTER TABLE `tracking_log`
  ADD PRIMARY KEY (`id_log`),
  ADD KEY `idx_pesanan` (`id_pesanan`),
  ADD KEY `idx_created_at` (`created_at`);

--
-- Indexes for table `transaksi_kas`
--
ALTER TABLE `transaksi_kas`
  ADD PRIMARY KEY (`id_transaksi`),
  ADD KEY `idx_tanggal` (`tanggal`),
  ADD KEY `idx_jenis` (`jenis`),
  ADD KEY `idx_sumber` (`sumber_tipe`,`sumber_id`),
  ADD KEY `idx_created_by` (`created_by`);

--
-- Indexes for table `varian_produk`
--
ALTER TABLE `varian_produk`
  ADD PRIMARY KEY (`id_varian`),
  ADD KEY `idx_produk` (`id_produk`);

--
-- Indexes for table `zona`
--
ALTER TABLE `zona`
  ADD PRIMARY KEY (`id_zona`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `detail_pesanan`
--
ALTER TABLE `detail_pesanan`
  MODIFY `id_detail` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `kategori`
--
ALTER TABLE `kategori`
  MODIFY `id_kategori` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `pelanggan`
--
ALTER TABLE `pelanggan`
  MODIFY `id_pelanggan` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `pembayaran`
--
ALTER TABLE `pembayaran`
  MODIFY `id_pembayaran` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `pengeluaran`
--
ALTER TABLE `pengeluaran`
  MODIFY `id_pengeluaran` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `pengguna`
--
ALTER TABLE `pengguna`
  MODIFY `id_user` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `pengiriman`
--
ALTER TABLE `pengiriman`
  MODIFY `id_pengiriman` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `pesanan`
--
ALTER TABLE `pesanan`
  MODIFY `id_pesanan` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `produk`
--
ALTER TABLE `produk`
  MODIFY `id_produk` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `setting`
--
ALTER TABLE `setting`
  MODIFY `id_setting` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `tracking_log`
--
ALTER TABLE `tracking_log`
  MODIFY `id_log` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `transaksi_kas`
--
ALTER TABLE `transaksi_kas`
  MODIFY `id_transaksi` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `varian_produk`
--
ALTER TABLE `varian_produk`
  MODIFY `id_varian` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT for table `zona`
--
ALTER TABLE `zona`
  MODIFY `id_zona` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

-- --------------------------------------------------------

--
-- Structure for view `v_detail_pesanan_produk`
--
DROP TABLE IF EXISTS `v_detail_pesanan_produk`;

CREATE ALGORITHM=UNDEFINED DEFINER=`rodd1157`@`localhost` SQL SECURITY DEFINER VIEW `v_detail_pesanan_produk`  AS SELECT `dp`.`id_detail` AS `id_detail`, `dp`.`id_pesanan` AS `id_pesanan`, `p`.`no_invoice` AS `no_invoice`, `pr`.`nama_produk` AS `nama_produk`, `vp`.`nama_varian` AS `nama_varian`, `dp`.`qty` AS `qty`, `dp`.`harga_satuan` AS `harga_satuan`, `dp`.`subtotal` AS `subtotal` FROM (((`detail_pesanan` `dp` join `pesanan` `p` on(`dp`.`id_pesanan` = `p`.`id_pesanan`)) join `varian_produk` `vp` on(`dp`.`id_varian` = `vp`.`id_varian`)) join `produk` `pr` on(`vp`.`id_produk` = `pr`.`id_produk`)) ;

-- --------------------------------------------------------

--
-- Structure for view `v_katalog`
--
DROP TABLE IF EXISTS `v_katalog`;

CREATE ALGORITHM=UNDEFINED DEFINER=`rodd1157`@`localhost` SQL SECURITY DEFINER VIEW `v_katalog`  AS SELECT `p`.`id_produk` AS `id_produk`, `k`.`nama_kategori` AS `nama_kategori`, `p`.`nama_produk` AS `nama_produk`, `p`.`deskripsi` AS `deskripsi`, `p`.`gambar` AS `gambar`, `p`.`shelf_life` AS `shelf_life`, `p`.`status` AS `status`, `v`.`id_varian` AS `id_varian`, `v`.`nama_varian` AS `nama_varian`, `v`.`harga` AS `harga`, `v`.`min_order` AS `min_order`, `v`.`stok` AS `stok` FROM ((`produk` `p` join `kategori` `k` on(`p`.`id_kategori` = `k`.`id_kategori`)) left join `varian_produk` `v` on(`p`.`id_produk` = `v`.`id_produk`)) WHERE `p`.`status` = 'tersedia' ORDER BY `k`.`nama_kategori` ASC, `p`.`nama_produk` ASC, `v`.`harga` ASC ;

-- --------------------------------------------------------

--
-- Structure for view `v_laporan_harian`
--
DROP TABLE IF EXISTS `v_laporan_harian`;

CREATE ALGORITHM=UNDEFINED DEFINER=`rodd1157`@`localhost` SQL SECURITY DEFINER VIEW `v_laporan_harian`  AS SELECT cast(`pesanan`.`tgl_pesan` as date) AS `tanggal`, count(`pesanan`.`id_pesanan`) AS `total_order`, sum(case when `pesanan`.`status` = 'selesai' then `pesanan`.`grand_total` else 0 end) AS `total_penjualan`, sum(case when `pesanan`.`status_bayar` = 'lunas' then `pesanan`.`grand_total` else 0 end) AS `total_lunas`, sum(case when `pesanan`.`status_bayar` <> 'lunas' then `pesanan`.`grand_total` else 0 end) AS `total_piutang` FROM `pesanan` WHERE `pesanan`.`status` <> 'batal' GROUP BY cast(`pesanan`.`tgl_pesan` as date) ORDER BY cast(`pesanan`.`tgl_pesan` as date) DESC ;

-- --------------------------------------------------------

--
-- Structure for view `v_laporan_kas_harian`
--
DROP TABLE IF EXISTS `v_laporan_kas_harian`;

CREATE ALGORITHM=UNDEFINED DEFINER=`rodd1157`@`localhost` SQL SECURITY DEFINER VIEW `v_laporan_kas_harian`  AS SELECT cast(`transaksi_kas`.`tanggal` as date) AS `tanggal`, sum(case when `transaksi_kas`.`jenis` = 'masuk' then `transaksi_kas`.`nominal` else 0 end) AS `pemasukan`, sum(case when `transaksi_kas`.`jenis` = 'keluar' then `transaksi_kas`.`nominal` else 0 end) AS `pengeluaran`, sum(case when `transaksi_kas`.`jenis` = 'masuk' then `transaksi_kas`.`nominal` else -`transaksi_kas`.`nominal` end) AS `saldo_harian` FROM `transaksi_kas` GROUP BY cast(`transaksi_kas`.`tanggal` as date) ORDER BY cast(`transaksi_kas`.`tanggal` as date) DESC ;

-- --------------------------------------------------------

--
-- Structure for view `v_pesanan_lengkap`
--
DROP TABLE IF EXISTS `v_pesanan_lengkap`;

CREATE ALGORITHM=UNDEFINED DEFINER=`rodd1157`@`localhost` SQL SECURITY DEFINER VIEW `v_pesanan_lengkap`  AS SELECT `p`.`id_pesanan` AS `id_pesanan`, `p`.`no_invoice` AS `no_invoice`, `p`.`tgl_pesan` AS `tgl_pesan`, `p`.`tgl_kirim` AS `tgl_kirim`, `p`.`waktu_kirim` AS `waktu_kirim`, `p`.`total_harga` AS `total_harga`, `p`.`ongkir` AS `ongkir`, `p`.`grand_total` AS `grand_total`, `p`.`status` AS `status`, `p`.`status_bayar` AS `status_bayar`, `p`.`metode_bayar` AS `metode_bayar`, `p`.`catatan` AS `catatan`, `pel`.`id_pelanggan` AS `id_pelanggan`, `pel`.`nama` AS `nama_pelanggan`, `pel`.`alamat` AS `alamat`, `pel`.`no_wa` AS `no_wa`, `pel`.`tipe` AS `tipe_pelanggan`, `z`.`nama_zona` AS `nama_zona`, `pg`.`status` AS `status_pengiriman`, `pg`.`driver` AS `driver` FROM (((`pesanan` `p` join `pelanggan` `pel` on(`p`.`id_pelanggan` = `pel`.`id_pelanggan`)) left join `zona` `z` on(`pel`.`id_zona` = `z`.`id_zona`)) left join `pengiriman` `pg` on(`p`.`id_pesanan` = `pg`.`id_pesanan`)) ;

-- --------------------------------------------------------

--
-- Structure for view `v_piutang`
--
DROP TABLE IF EXISTS `v_piutang`;

CREATE ALGORITHM=UNDEFINED DEFINER=`rodd1157`@`localhost` SQL SECURITY DEFINER VIEW `v_piutang`  AS SELECT `pel`.`id_pelanggan` AS `id_pelanggan`, `pel`.`nama` AS `nama`, `pel`.`no_wa` AS `no_wa`, `pel`.`total_hutang` AS `total_hutang`, count(`p`.`id_pesanan`) AS `jumlah_pesanan_belum_lunas`, sum(`p`.`grand_total`) AS `total_pesanan`, sum(coalesce(`pb`.`total_bayar`,0)) AS `total_terbayar`, sum(`p`.`grand_total`) - sum(coalesce(`pb`.`total_bayar`,0)) AS `sisa_hutang` FROM ((`pelanggan` `pel` join `pesanan` `p` on(`pel`.`id_pelanggan` = `p`.`id_pelanggan`)) left join (select `pembayaran`.`id_pesanan` AS `id_pesanan`,sum(`pembayaran`.`jumlah`) AS `total_bayar` from `pembayaran` group by `pembayaran`.`id_pesanan`) `pb` on(`p`.`id_pesanan` = `pb`.`id_pesanan`)) WHERE `p`.`status_bayar` <> 'lunas' AND `p`.`status` <> 'batal' GROUP BY `pel`.`id_pelanggan`, `pel`.`nama`, `pel`.`no_wa`, `pel`.`total_hutang` HAVING `sisa_hutang` > 0 ;

-- --------------------------------------------------------

--
-- Structure for view `v_ringkasan_kas`
--
DROP TABLE IF EXISTS `v_ringkasan_kas`;

CREATE ALGORITHM=UNDEFINED DEFINER=`rodd1157`@`localhost` SQL SECURITY DEFINER VIEW `v_ringkasan_kas`  AS SELECT coalesce(sum(case when `transaksi_kas`.`jenis` = 'masuk' then `transaksi_kas`.`nominal` else 0 end),0) AS `total_masuk`, coalesce(sum(case when `transaksi_kas`.`jenis` = 'keluar' then `transaksi_kas`.`nominal` else 0 end),0) AS `total_keluar`, coalesce(sum(case when `transaksi_kas`.`jenis` = 'masuk' then `transaksi_kas`.`nominal` else -`transaksi_kas`.`nominal` end),0) AS `saldo_akhir` FROM `transaksi_kas` ;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `detail_pesanan`
--
ALTER TABLE `detail_pesanan`
  ADD CONSTRAINT `fk_detail_pesanan` FOREIGN KEY (`id_pesanan`) REFERENCES `pesanan` (`id_pesanan`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_detail_varian` FOREIGN KEY (`id_varian`) REFERENCES `varian_produk` (`id_varian`) ON UPDATE CASCADE;

--
-- Constraints for table `kategori`
--
ALTER TABLE `kategori`
  ADD CONSTRAINT `fk_kategori_created_by_user` FOREIGN KEY (`created_by`) REFERENCES `pengguna` (`id_user`) ON DELETE SET NULL ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_kategori_updated_by_user` FOREIGN KEY (`updated_by`) REFERENCES `pengguna` (`id_user`) ON DELETE SET NULL ON UPDATE CASCADE;

--
-- Constraints for table `pelanggan`
--
ALTER TABLE `pelanggan`
  ADD CONSTRAINT `fk_pelanggan_zona` FOREIGN KEY (`id_zona`) REFERENCES `zona` (`id_zona`) ON DELETE SET NULL ON UPDATE CASCADE;

--
-- Constraints for table `pembayaran`
--
ALTER TABLE `pembayaran`
  ADD CONSTRAINT `fk_pembayaran_pesanan` FOREIGN KEY (`id_pesanan`) REFERENCES `pesanan` (`id_pesanan`) ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_pembayaran_received_by_user` FOREIGN KEY (`received_by`) REFERENCES `pengguna` (`id_user`) ON DELETE SET NULL ON UPDATE CASCADE;

--
-- Constraints for table `pengeluaran`
--
ALTER TABLE `pengeluaran`
  ADD CONSTRAINT `fk_pengeluaran_user` FOREIGN KEY (`created_by`) REFERENCES `pengguna` (`id_user`) ON DELETE SET NULL ON UPDATE CASCADE;

--
-- Constraints for table `pengiriman`
--
ALTER TABLE `pengiriman`
  ADD CONSTRAINT `fk_pengiriman_pesanan` FOREIGN KEY (`id_pesanan`) REFERENCES `pesanan` (`id_pesanan`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `pesanan`
--
ALTER TABLE `pesanan`
  ADD CONSTRAINT `fk_pesanan_created_by_user` FOREIGN KEY (`created_by`) REFERENCES `pengguna` (`id_user`) ON DELETE SET NULL ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_pesanan_pelanggan` FOREIGN KEY (`id_pelanggan`) REFERENCES `pelanggan` (`id_pelanggan`) ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_pesanan_updated_by_user` FOREIGN KEY (`updated_by`) REFERENCES `pengguna` (`id_user`) ON DELETE SET NULL ON UPDATE CASCADE;

--
-- Constraints for table `produk`
--
ALTER TABLE `produk`
  ADD CONSTRAINT `fk_produk_created_by_user` FOREIGN KEY (`created_by`) REFERENCES `pengguna` (`id_user`) ON DELETE SET NULL ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_produk_kategori` FOREIGN KEY (`id_kategori`) REFERENCES `kategori` (`id_kategori`) ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_produk_updated_by_user` FOREIGN KEY (`updated_by`) REFERENCES `pengguna` (`id_user`) ON DELETE SET NULL ON UPDATE CASCADE;

--
-- Constraints for table `tracking_log`
--
ALTER TABLE `tracking_log`
  ADD CONSTRAINT `fk_tracking_pesanan` FOREIGN KEY (`id_pesanan`) REFERENCES `pesanan` (`id_pesanan`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `transaksi_kas`
--
ALTER TABLE `transaksi_kas`
  ADD CONSTRAINT `fk_transaksi_kas_user` FOREIGN KEY (`created_by`) REFERENCES `pengguna` (`id_user`) ON DELETE SET NULL ON UPDATE CASCADE;

--
-- Constraints for table `varian_produk`
--
ALTER TABLE `varian_produk`
  ADD CONSTRAINT `fk_varian_produk` FOREIGN KEY (`id_produk`) REFERENCES `produk` (`id_produk`) ON DELETE CASCADE ON UPDATE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
