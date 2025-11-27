-- =====================================================
-- Update Users Table untuk Menambahkan Role
-- Jalankan file ini di phpMyAdmin untuk menambahkan kolom role
-- =====================================================

-- Tambahkan kolom role ke tabel users jika belum ada
ALTER TABLE `users` 
ADD COLUMN IF NOT EXISTS `role` VARCHAR(20) DEFAULT 'admin' AFTER `password`,
ADD COLUMN IF NOT EXISTS `full_name` VARCHAR(255) DEFAULT NULL AFTER `username`,
ADD COLUMN IF NOT EXISTS `email` VARCHAR(255) DEFAULT NULL AFTER `full_name`;

-- Update role untuk user yang sudah ada menjadi 'admin'
UPDATE `users` SET `role` = 'admin' WHERE `role` IS NULL OR `role` = '';

-- Tambahkan index untuk role (opsional, untuk performa query)
CREATE INDEX IF NOT EXISTS `idx_role` ON `users` (`role`);

-- =====================================================
-- Update tabel untuk menambahkan created_by (uploader)
-- =====================================================

-- Tambahkan kolom created_by ke news_items
ALTER TABLE `news_items` 
ADD COLUMN IF NOT EXISTS `created_by` INT DEFAULT NULL AFTER `created_at`,
ADD INDEX IF NOT EXISTS `idx_created_by` (`created_by`);

-- Tambahkan kolom created_by ke project_items
ALTER TABLE `project_items` 
ADD COLUMN IF NOT EXISTS `created_by` INT DEFAULT NULL AFTER `created_at`,
ADD INDEX IF NOT EXISTS `idx_created_by` (`created_by`);

-- Tambahkan kolom created_by ke achievement_items
ALTER TABLE `achievement_items` 
ADD COLUMN IF NOT EXISTS `created_by` INT DEFAULT NULL AFTER `created_at`,
ADD INDEX IF NOT EXISTS `idx_created_by` (`created_by`);

-- =====================================================
-- Contoh data untuk testing (opsional)
-- =====================================================

-- Insert contoh user dosen (password: dosen123)
-- INSERT INTO `users` (`username`, `password`, `role`, `full_name`, `email`) 
-- VALUES ('dosen1', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'dosen', 'Dr. Ahmad Fauzi', 'dosen1@crims.ac.id');

-- Insert contoh user mahasiswa (password: mahasiswa123)
-- INSERT INTO `users` (`username`, `password`, `role`, `full_name`, `email`) 
-- VALUES ('mahasiswa1', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'mahasiswa', 'Budi Santoso', 'mahasiswa1@crims.ac.id');


