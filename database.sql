-- -----------------------------------------------------
--  CRIMS Content Management Schema
--  Import this file via phpMyAdmin to create/update tables
-- -----------------------------------------------------

CREATE TABLE IF NOT EXISTS `users` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `username` VARCHAR(50) NOT NULL UNIQUE,
  `password` VARCHAR(255) NOT NULL,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS `cms_sections` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `slug` VARCHAR(100) NOT NULL UNIQUE,
  `title` VARCHAR(255) NOT NULL,
  `content` LONGTEXT,
  `extra` JSON NULL,
  `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS `team_structure` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `name` VARCHAR(255) NOT NULL,
  `position_title` VARCHAR(150) NOT NULL,
  `email` VARCHAR(150) DEFAULT NULL,
  `scopus_id` VARCHAR(50) DEFAULT NULL,
  `photo_url` VARCHAR(500) DEFAULT NULL,
  `research_interests` TEXT NULL,
  `category` VARCHAR(100) DEFAULT 'Researcher',
  `sort_order` INT DEFAULT 0,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS `project_items` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `title` VARCHAR(255) NOT NULL,
  `category` VARCHAR(100) DEFAULT NULL,
  `summary` TEXT,
  `image_url` VARCHAR(500) DEFAULT NULL,
  `detail_url` VARCHAR(255) DEFAULT NULL,
  `is_featured` TINYINT(1) DEFAULT 1,
  `sort_order` INT DEFAULT 0,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS `news_items` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `title` VARCHAR(255) NOT NULL,
  `summary` TEXT,
  `image_url` VARCHAR(500) DEFAULT NULL,
  `article_url` VARCHAR(255) DEFAULT NULL,
  `published_at` DATE DEFAULT NULL,
  `sort_order` INT DEFAULT 0,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS `partner_items` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `name` VARCHAR(255) NOT NULL,
  `logo_url` VARCHAR(500) DEFAULT NULL,
  `website_url` VARCHAR(255) DEFAULT NULL,
  `category` VARCHAR(100) DEFAULT 'Industry',
  `sort_order` INT DEFAULT 0,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS `achievement_items` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `title` VARCHAR(255) NOT NULL,
  `description` TEXT,
  `icon_class` VARCHAR(100) DEFAULT 'fas fa-trophy',
  `sort_order` INT DEFAULT 0,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS `hilirisasi_items` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `title` VARCHAR(255) NOT NULL,
  `description` TEXT,
  `image_url` VARCHAR(500) DEFAULT NULL,
  `detail_url` VARCHAR(255) DEFAULT NULL,
  `sort_order` INT DEFAULT 0,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

INSERT INTO `cms_sections` (`slug`, `title`, `content`, `extra`)
VALUES
('about_overview', 'Tentang CRiMS', 'Center for Research in Manufacturing System (CRiMS) berfokus pada penelitian dan pengembangan sistem manufaktur terpadu dan berkelanjutan.', JSON_OBJECT('button_text','Selengkapnya','button_url','/tentang.html','image_url','https://images.unsplash.com/photo-1573164713988-8665fc963095?auto=format&fit=crop&w=1950&q=80')),
('about_features', 'Keunggulan', JSON_ARRAY(
    JSON_OBJECT('icon','fas fa-flask','title','Penelitian Unggulan','description','Mengembangkan teknologi mutakhir di bidang manufaktur'),
    JSON_OBJECT('icon','fas fa-handshake','title','Kolaborasi','description','Berkolaborasi dengan industri dan akademisi'),
    JSON_OBJECT('icon','fas fa-graduation-cap','title','Pengembangan SDM','description','Mencetak peneliti dan praktisi handal')
), NULL),
('team_intro', 'Tim Peneliti CRiMS', 'Para ahli multidisiplin yang berdedikasi untuk mengembangkan inovasi di bidang sistem manufaktur cerdas.', NULL)
ON DUPLICATE KEY UPDATE title=VALUES(title), content=VALUES(content), extra=VALUES(extra);


