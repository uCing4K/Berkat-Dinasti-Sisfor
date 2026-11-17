-- =============================================================================
-- Migration: Add tracking_token to pesanan table for Zero-Login Digital Receipt
-- Sistem Informasi Berkat Dinasti (Bakery & Pastry UMKM)
-- Date: 2026-11-17
-- =============================================================================

-- 1. Tambah kolom tracking_token pada tabel pesanan
ALTER TABLE `pesanan` 
ADD COLUMN IF NOT EXISTS `tracking_token` VARCHAR(64) UNIQUE NULL AFTER `catatan`;

-- 2. Buat index pencarian cepat untuk capability token
-- ALTER TABLE `pesanan` ADD INDEX `idx_tracking_token` (`tracking_token`);

-- 3. Backfill pesanan eksisting yang belum memiliki tracking_token
UPDATE `pesanan` 
SET `tracking_token` = MD5(CONCAT(`id_pesanan`, `no_invoice`, RAND(), UUID()))
WHERE `tracking_token` IS NULL OR `tracking_token` = '';
