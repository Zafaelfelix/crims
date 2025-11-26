-- Tabel untuk menyimpan multiple photos per berita
-- Pastikan tabel news_items sudah ada sebelum menjalankan script ini

-- Hapus foreign key constraint jika tabel sudah ada (untuk update)
SET @exist := (SELECT COUNT(*) FROM information_schema.table_constraints 
               WHERE constraint_schema = DATABASE() 
               AND table_name = 'news_photos' 
               AND constraint_name = 'news_photos_ibfk_1');
SET @sqlstmt := IF(@exist > 0, 'ALTER TABLE `news_photos` DROP FOREIGN KEY `news_photos_ibfk_1`', 'SELECT 1');
PREPARE stmt FROM @sqlstmt;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

-- Buat tabel jika belum ada
CREATE TABLE IF NOT EXISTS `news_photos` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `news_id` INT NOT NULL,
  `photo_url` VARCHAR(500) NOT NULL,
  `caption` VARCHAR(255) DEFAULT NULL,
  `sort_order` INT DEFAULT 0,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  INDEX `idx_news_id` (`news_id`),
  INDEX `idx_sort_order` (`sort_order`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Tambahkan foreign key constraint
SET @exist := (SELECT COUNT(*) FROM information_schema.table_constraints 
               WHERE constraint_schema = DATABASE() 
               AND table_name = 'news_photos' 
               AND constraint_name = 'news_photos_ibfk_1');
SET @sqlstmt := IF(@exist = 0, 
    'ALTER TABLE `news_photos` ADD CONSTRAINT `news_photos_ibfk_1` FOREIGN KEY (`news_id`) REFERENCES `news_items`(`id`) ON DELETE CASCADE', 
    'SELECT 1');
PREPARE stmt FROM @sqlstmt;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

