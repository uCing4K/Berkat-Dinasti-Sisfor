-- Migration: Add default staff/kasir user for 2-role RBAC (Admin vs Staf Operasional)
-- Sistem Informasi Berkat Dinasti (Dynasty Food & Beverage)
-- Date: 2026-11-17

INSERT INTO `pengguna` (`id_user`, `username`, `password`, `nama_lengkap`, `email`, `no_hp`, `role`, `status`, `last_login`, `created_at`, `updated_at`) 
VALUES (2, 'kasir', '$2y$10$GEGB62f2c/adv9OlrjLZlus3vZMiy6Li5HiHnfXiZqdSrUGMpDwVy', 'Staf Kasir & Operasional', 'kasir@berkatdinasti.id', '081234567891', 'kasir', 'aktif', NULL, NOW(), NOW())
ON DUPLICATE KEY UPDATE `role` = 'kasir', `status` = 'aktif';
