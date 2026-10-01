-- =====================================================
-- UJI INTEGRASI DATABASE BERKAT DINASTI (DUMMY + INDIKATOR)
-- Tujuan: cek apakah tabel, relasi, trigger, procedure, dan view berjalan benar
-- Catatan: script ini MENAMBAH data dummy (tidak menghapus data lama)
-- =====================================================

USE rodd1157_berkat_dinasti_db;

SET @test_tag = 'UJI_20260423';
SET @now_date = CURDATE();

-- =====================================================
-- 0) HOTFIX KOMPATIBILITAS DUMP (tanpa DELIMITER)
-- =====================================================
-- Beberapa dump lama memakai kolom `dibuat_oleh` pada trigger,
-- sedangkan dump final tabel `transaksi_kas` memakai `created_by`.
-- Tambahkan kolom kompatibilitas agar trigger lama tetap berjalan.

ALTER TABLE transaksi_kas
ADD COLUMN IF NOT EXISTS dibuat_oleh INT NULL AFTER keterangan;

UPDATE transaksi_kas
SET created_by = dibuat_oleh
WHERE created_by IS NULL
  AND dibuat_oleh IS NOT NULL;

-- =====================================================
-- 1) PRECHECK STRUKTUR DATABASE
-- =====================================================
-- Hindari information_schema karena beberapa shared hosting menolak akses (#1044).
-- Precheck dilakukan dengan query langsung ke objek inti.
SELECT
  'PRECHECK_DB_AKTIF' AS indikator,
  CASE WHEN DATABASE() = 'rodd1157_berkat_dinasti_db' THEN 'PASS' ELSE CONCAT('FAIL (DB aktif: ', DATABASE(), ')') END AS status;

SELECT
  'PRECHECK_TABEL_INTI_AKSES' AS indikator,
  CASE
    WHEN (SELECT COUNT(*) FROM pengguna) >= 0
      AND (SELECT COUNT(*) FROM pesanan) >= 0
      AND (SELECT COUNT(*) FROM detail_pesanan) >= 0
      AND (SELECT COUNT(*) FROM pembayaran) >= 0
      AND (SELECT COUNT(*) FROM pengiriman) >= 0
      AND (SELECT COUNT(*) FROM tracking_log) >= 0
      AND (SELECT COUNT(*) FROM transaksi_kas) >= 0
      AND (SELECT COUNT(*) FROM pengeluaran) >= 0
      AND (SELECT COUNT(*) FROM produk) >= 0
      AND (SELECT COUNT(*) FROM varian_produk) >= 0
      AND (SELECT COUNT(*) FROM pelanggan) >= 0
      AND (SELECT COUNT(*) FROM kategori) >= 0
      AND (SELECT COUNT(*) FROM zona) >= 0
      AND (SELECT COUNT(*) FROM setting) >= 0
    THEN 'PASS'
    ELSE 'FAIL'
  END AS status;

-- =====================================================
-- 2) DATA DUMMY MINIMAL
-- =====================================================
INSERT INTO pengguna (username, password, nama_lengkap, role, status)
SELECT CONCAT('kasir_', @test_tag), '$2y$10$dummyhashforintegrationtest0000000000000000000000000000000',
       CONCAT('Kasir ', @test_tag), 'kasir', 'aktif'
WHERE NOT EXISTS (
  SELECT 1 FROM pengguna WHERE username = CONCAT('kasir_', @test_tag)
);

SELECT id_user INTO @id_user
FROM pengguna
WHERE username = CONCAT('kasir_', @test_tag)
LIMIT 1;

INSERT INTO zona (nama_zona, deskripsi, ongkir)
VALUES (CONCAT('Zona ', @test_tag), CONCAT('Zona uji ', @test_tag), 12000.00);

SET @id_zona = LAST_INSERT_ID();

INSERT INTO pelanggan (id_zona, nama, alamat, no_wa, tipe, catatan)
VALUES (
  @id_zona,
  CONCAT('Pelanggan ', @test_tag),
  'Jl. Uji Integrasi No. 1',
  CONCAT('62811', LPAD(FLOOR(RAND() * 99999999), 8, '0')),
  'retail',
  CONCAT('Data dummy ', @test_tag)
);

SET @id_pelanggan = LAST_INSERT_ID();

INSERT INTO kategori (nama_kategori, deskripsi, created_by, updated_by)
VALUES (CONCAT('Kategori ', @test_tag), 'Kategori dummy integrasi', @id_user, @id_user);

SET @id_kategori = LAST_INSERT_ID();

INSERT INTO produk (id_kategori, nama_produk, deskripsi, shelf_life, status, created_by, updated_by)
VALUES (
  @id_kategori,
  CONCAT('Produk ', @test_tag),
  'Produk dummy untuk uji integrasi database',
  7,
  'tersedia',
  @id_user,
  @id_user
);

SET @id_produk = LAST_INSERT_ID();

INSERT INTO varian_produk (id_produk, nama_varian, harga, min_order, stok)
VALUES (@id_produk, CONCAT('Varian A ', @test_tag), 10000.00, 1, 50);

SET @id_varian_1 = LAST_INSERT_ID();

INSERT INTO varian_produk (id_produk, nama_varian, harga, min_order, stok)
VALUES (@id_produk, CONCAT('Varian B ', @test_tag), 15000.00, 1, 50);

SET @id_varian_2 = LAST_INSERT_ID();

-- =====================================================
-- 3) UJI ALUR BISNIS (PROCEDURE + TRIGGER)
-- =====================================================
CALL sp_create_pesanan(
  @id_pelanggan,
  DATE_ADD(CURDATE(), INTERVAL 1 DAY),
  '10:00:00',
  'transfer',
  CONCAT('Catatan ', @test_tag),
  @id_pesanan
);

CALL sp_add_detail_pesanan(@id_pesanan, @id_varian_1, 2);
CALL sp_add_detail_pesanan(@id_pesanan, @id_varian_2, 1);

-- status kirim -> selesai
CALL sp_update_status(@id_pesanan, 'kirim');
CALL sp_update_status(@id_pesanan, 'selesai');

-- pembayaran tahap 1 (DP), lalu tahap 2 (pelunasan)
CALL sp_bayar(@id_pesanan, 10000.00, 'transfer', NULL, CONCAT('DP ', @test_tag), @id_user);
CALL sp_bayar(@id_pesanan, 100000.00, 'transfer', NULL, CONCAT('Pelunasan ', @test_tag), @id_user);

-- uji kas keluar otomatis dari pengeluaran
INSERT INTO pengeluaran (tanggal, kategori, deskripsi, jumlah, created_by)
VALUES (CURDATE(), 'operasional', CONCAT('Pengeluaran uji ', @test_tag), 2500.00, @id_user);

SET @id_pengeluaran = LAST_INSERT_ID();

-- =====================================================
-- 4) INDIKATOR VALIDASI (PASS/FAIL)
-- =====================================================
SELECT id_pesanan INTO @cek_id_pesanan FROM pesanan WHERE id_pesanan = @id_pesanan LIMIT 1;
SELECT total_harga INTO @cek_total FROM pesanan WHERE id_pesanan = @id_pesanan LIMIT 1;
SELECT ongkir INTO @cek_ongkir FROM pesanan WHERE id_pesanan = @id_pesanan LIMIT 1;
SELECT grand_total INTO @cek_grand FROM pesanan WHERE id_pesanan = @id_pesanan LIMIT 1;
SELECT COALESCE(SUM(subtotal),0) INTO @cek_sum_detail FROM detail_pesanan WHERE id_pesanan = @id_pesanan;
SELECT status_bayar INTO @cek_status_bayar FROM pesanan WHERE id_pesanan = @id_pesanan LIMIT 1;
SELECT no_invoice INTO @cek_invoice FROM pesanan WHERE id_pesanan = @id_pesanan LIMIT 1;
SELECT COUNT(*) INTO @cek_pengiriman FROM pengiriman WHERE id_pesanan = @id_pesanan;
SELECT COUNT(*) INTO @cek_tracking FROM tracking_log WHERE id_pesanan = @id_pesanan;
SELECT COUNT(*) INTO @cek_kas_masuk FROM transaksi_kas WHERE sumber_tipe = 'pembayaran' AND sumber_id IN (
  SELECT id_pembayaran FROM pembayaran WHERE id_pesanan = @id_pesanan
);
SELECT COUNT(*) INTO @cek_kas_keluar FROM transaksi_kas WHERE sumber_tipe = 'pengeluaran' AND sumber_id = @id_pengeluaran;
SELECT COUNT(*) INTO @cek_view_harian FROM v_laporan_harian WHERE tanggal = @now_date;

SELECT 'ORDER_TERCIPTA' AS indikator,
       CASE WHEN @cek_id_pesanan IS NOT NULL THEN 'PASS' ELSE 'FAIL' END AS status,
       COALESCE(CAST(@cek_id_pesanan AS CHAR), 'NULL') AS actual,
       'id_pesanan terbuat' AS expected
UNION ALL
SELECT 'INVOICE_OTOMATIS',
       CASE WHEN @cek_invoice LIKE 'INV-%' THEN 'PASS' ELSE 'FAIL' END,
       COALESCE(@cek_invoice, 'NULL'),
       'format INV-...' 
UNION ALL
SELECT 'TOTAL_DETAIL_KE_PESANAN',
       CASE WHEN ABS(@cek_total - @cek_sum_detail) < 0.01 THEN 'PASS' ELSE 'FAIL' END,
       CONCAT('total=', @cek_total, '; detail_sum=', @cek_sum_detail),
       'total_harga = SUM(subtotal)'
UNION ALL
SELECT 'GRAND_TOTAL_VALID',
       CASE WHEN ABS(@cek_grand - (@cek_total + @cek_ongkir)) < 0.01 THEN 'PASS' ELSE 'FAIL' END,
       CONCAT('grand=', @cek_grand, '; total+ongkir=', (@cek_total + @cek_ongkir)),
       'grand_total = total_harga + ongkir'
UNION ALL
SELECT 'STATUS_BAYAR_LUNAS',
       CASE WHEN @cek_status_bayar = 'lunas' THEN 'PASS' ELSE 'FAIL' END,
       COALESCE(@cek_status_bayar, 'NULL'),
       'lunas setelah 2 pembayaran'
UNION ALL
SELECT 'PENGIRIMAN_OTOMATIS',
       CASE WHEN @cek_pengiriman >= 1 THEN 'PASS' ELSE 'FAIL' END,
       CAST(@cek_pengiriman AS CHAR),
       'minimal 1 baris pengiriman'
UNION ALL
SELECT 'TRACKING_LOG_TERCATAT',
       CASE WHEN @cek_tracking >= 3 THEN 'PASS' ELSE 'FAIL' END,
       CAST(@cek_tracking AS CHAR),
       'minimal 3 log status'
UNION ALL
SELECT 'KAS_MASUK_DARI_PEMBAYARAN',
       CASE WHEN @cek_kas_masuk >= 2 THEN 'PASS' ELSE 'FAIL' END,
       CAST(@cek_kas_masuk AS CHAR),
       '2 transaksi kas masuk'
UNION ALL
SELECT 'KAS_KELUAR_DARI_PENGELUARAN',
       CASE WHEN @cek_kas_keluar >= 1 THEN 'PASS' ELSE 'FAIL' END,
       CAST(@cek_kas_keluar AS CHAR),
       '1 transaksi kas keluar'
UNION ALL
SELECT 'VIEW_LAPORAN_HARIAN_AKTIF',
       CASE WHEN @cek_view_harian >= 1 THEN 'PASS' ELSE 'FAIL' END,
       CAST(@cek_view_harian AS CHAR),
       'data hari ini muncul di view';

-- =====================================================
-- 5) OUTPUT RINGKAS DATA UJI
-- =====================================================
SELECT @id_pesanan AS id_pesanan_uji, @id_pelanggan AS id_pelanggan_uji, @id_produk AS id_produk_uji;
SELECT * FROM v_pesanan_lengkap WHERE id_pesanan = @id_pesanan;
SELECT * FROM v_detail_pesanan_produk WHERE id_pesanan = @id_pesanan;
SELECT * FROM v_ringkasan_kas;

-- Uji procedure laporan penjualan
CALL sp_laporan_penjualan(DATE_SUB(CURDATE(), INTERVAL 7 DAY), CURDATE());

-- =====================================================
-- OPSIONAL: BERSIHKAN DATA UJI (aktifkan manual jika diperlukan)
-- =====================================================
-- DELETE FROM pengeluaran WHERE id_pengeluaran = @id_pengeluaran;
-- DELETE FROM pembayaran WHERE id_pesanan = @id_pesanan;
-- DELETE FROM detail_pesanan WHERE id_pesanan = @id_pesanan;
-- DELETE FROM pengiriman WHERE id_pesanan = @id_pesanan;
-- DELETE FROM tracking_log WHERE id_pesanan = @id_pesanan;
-- DELETE FROM pesanan WHERE id_pesanan = @id_pesanan;
-- DELETE FROM varian_produk WHERE id_varian IN (@id_varian_1, @id_varian_2);
-- DELETE FROM produk WHERE id_produk = @id_produk;
-- DELETE FROM kategori WHERE id_kategori = @id_kategori;
-- DELETE FROM pelanggan WHERE id_pelanggan = @id_pelanggan;
-- DELETE FROM zona WHERE id_zona = @id_zona;
-- DELETE FROM pengguna WHERE id_user = @id_user;
