-- Tambahkan kolom image_url ke tabel achievement_items
-- Jalankan query ini di phpMyAdmin pada database CRIMS

ALTER TABLE achievement_items 
ADD COLUMN image_url VARCHAR(255) NULL AFTER icon_class;

