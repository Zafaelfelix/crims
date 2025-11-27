<?php
require_once __DIR__ . '/config.php';

// Function to safely render HTML content from Summernote (only allow safe formatting tags)
function renderSafeHtml($html) {
    if (empty($html)) {
        return '';
    }
    
    // Allowed tags for formatting (safe tags only)
    $allowedTags = '<p><br><b><strong><i><em><u><s><strike><span><div><ul><ol><li><h1><h2><h3><h4><h5><h6>';
    
    // Remove dangerous tags and attributes
    $html = strip_tags($html, $allowedTags);
    
    // Remove dangerous attributes but keep style for formatting
    $html = preg_replace_callback('/<([^>]+)>/i', function($matches) {
        $tag = $matches[1];
        // Remove dangerous attributes
        $tag = preg_replace('/\s*on\w+\s*=\s*["\'][^"\']*["\']/i', '', $tag);
        $tag = preg_replace('/\s*javascript\s*:/i', '', $tag);
        // Keep style attribute for formatting
        return '<' . $tag . '>';
    }, $html);
    
    return $html;
}

$id = (int) ($_GET['id'] ?? 0);

if ($id <= 0) {
    header('Location: /crims/');
    exit;
}

// Check if image_url column exists
$checkColumn = $mysqli->query("SHOW COLUMNS FROM achievement_items LIKE 'image_url'");
$hasImageColumn = $checkColumn && $checkColumn->num_rows > 0;
if ($checkColumn) $checkColumn->free();

if ($hasImageColumn) {
    $query = $mysqli->prepare('SELECT id, title, description, icon_class, image_url, created_at, updated_at FROM achievement_items WHERE id = ?');
} else {
    $query = $mysqli->prepare('SELECT id, title, description, icon_class, NULL as image_url, created_at, updated_at FROM achievement_items WHERE id = ?');
}

$query->bind_param('i', $id);
$query->execute();
$result = $query->get_result();
$achievement = $result->fetch_assoc();
$query->close();

if (!$achievement) {
    header('Location: /crims/');
    exit;
}

function achievementImageSrc(?string $path): string {
    if ($path) {
        return '/crims/' . ltrim($path, '/');
    }
    return '';
}

$pageTitle = htmlspecialchars($achievement['title']);
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $pageTitle ?> - CRIMS</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="css/styles.css">
    <style>
        .achievement-detail {
            padding: 120px 0 80px;
            background: #f5f5f5;
        }
        
        .achievement-detail-container {
            max-width: 900px;
            margin: 0 auto;
            padding: 0 24px;
        }
        
        .breadcrumb {
            margin-bottom: 30px;
            font-size: 14px;
            color: #666;
        }
        
        .breadcrumb a {
            color: #1e5ba8;
            text-decoration: none;
        }
        
        .breadcrumb a:hover {
            text-decoration: underline;
        }
        
        .breadcrumb span {
            margin: 0 8px;
            color: #999;
        }
        
        .achievement-detail-card {
            background: #ffffff;
            border-radius: 12px;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.08);
            overflow: hidden;
            margin-bottom: 40px;
        }
        
        .achievement-detail-image {
            width: 100%;
            margin-bottom: 0;
            border-radius: 0;
            overflow: hidden;
            cursor: pointer;
            position: relative;
            transition: transform 0.3s ease;
            background: #f0f0f0;
        }
        
        .achievement-detail-image:hover {
            transform: scale(1.02);
        }
        
        .achievement-detail-image img {
            width: 100%;
            height: auto;
            display: block;
            object-fit: cover;
            max-height: 500px;
        }
        
        .achievement-detail-image::after {
            content: '\f00e';
            font-family: 'Font Awesome 6 Free';
            font-weight: 900;
            position: absolute;
            top: 20px;
            right: 20px;
            width: 50px;
            height: 50px;
            background: rgba(255, 255, 255, 0.95);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 20px;
            color: #1e5ba8;
            opacity: 0;
            transition: opacity 0.3s ease;
            box-shadow: 0 4px 12px rgba(0,0,0,.2);
        }
        
        .achievement-detail-image:hover::after {
            opacity: 1;
        }
        
        .achievement-detail-body {
            padding: 32px 40px;
        }
        
        .achievement-detail-title {
            font-size: 28px;
            font-weight: 700;
            color: #1a1a1a;
            margin-bottom: 16px;
            line-height: 1.4;
        }
        
        .achievement-detail-meta {
            display: flex;
            align-items: center;
            gap: 20px;
            flex-wrap: wrap;
            margin-bottom: 28px;
            padding-bottom: 24px;
            border-bottom: 1px solid #e8e8e8;
        }
        
        .achievement-meta-item {
            display: flex;
            align-items: center;
            gap: 10px;
            padding: 8px 14px;
            background: linear-gradient(135deg, #f8fafc 0%, #f1f5f9 100%);
            border-radius: 10px;
            color: #475569;
            font-size: 14px;
            font-weight: 500;
            transition: all 0.3s ease;
        }
        
        .achievement-meta-item:hover {
            background: linear-gradient(135deg, #eff6ff 0%, #dbeafe 100%);
            color: #1e5ba8;
        }
        
        .achievement-meta-item i {
            font-size: 16px;
            color: #1e5ba8;
        }
        
        .achievement-detail-uploader {
            display: flex;
            align-items: center;
            gap: 10px;
            padding: 8px 14px;
            background: linear-gradient(135deg, #f8fafc 0%, #f1f5f9 100%);
            border-radius: 10px;
            transition: all 0.3s ease;
        }
        
        .achievement-detail-uploader:hover {
            background: linear-gradient(135deg, #eff6ff 0%, #dbeafe 100%);
        }
        
        .achievement-detail-uploader-icon {
            width: 28px;
            height: 28px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 0.875rem;
            color: #fff;
            flex-shrink: 0;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.15);
        }
        
        .achievement-detail-uploader-icon.admin {
            background: linear-gradient(135deg, #1e5ba8 0%, #2980b9 100%);
        }
        
        .achievement-detail-uploader-icon.mahasiswa {
            background: linear-gradient(135deg, #10b981 0%, #059669 100%);
        }
        
        .achievement-detail-uploader-icon.dosen {
            background: linear-gradient(135deg, #f59e0b 0%, #d97706 100%);
        }
        
        .achievement-detail-uploader span {
            font-weight: 500;
            font-size: 14px;
            color: #475569;
        }
        
        .achievement-detail-uploader:hover span {
            color: #1e5ba8;
        }
        
        .achievement-detail-content {
            font-size: 16px;
            line-height: 1.9;
            color: #333;
            margin-bottom: 0;
        }
        
        .achievement-detail-content p {
            margin-bottom: 20px;
            text-align: justify;
        }
        
        .achievement-detail-content p:last-child {
            margin-bottom: 0;
        }
        
        .achievement-icon-display {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            width: 80px;
            height: 80px;
            background: linear-gradient(135deg, #1e5ba8 0%, #2980b9 100%);
            border-radius: 16px;
            margin-bottom: 24px;
            box-shadow: 0 8px 20px rgba(30, 91, 168, 0.3);
        }
        
        .achievement-icon-display i {
            font-size: 36px;
            color: #fff;
        }
        
        /* Lightbox */
        .lightbox {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0, 0, 0, 0.95);
            z-index: 10000;
            align-items: center;
            justify-content: center;
            padding: 40px;
            animation: fadeIn 0.3s ease;
        }
        
        .lightbox.active {
            display: flex;
        }
        
        @keyframes fadeIn {
            from { opacity: 0; }
            to { opacity: 1; }
        }
        
        .lightbox-content {
            position: relative;
            max-width: 90%;
            max-height: 90%;
        }
        
        .lightbox-content img {
            max-width: 100%;
            max-height: 90vh;
            object-fit: contain;
            border-radius: 12px;
            box-shadow: 0 20px 60px rgba(0,0,0,.5);
        }
        
        .lightbox-close {
            position: absolute;
            top: -50px;
            right: 0;
            width: 40px;
            height: 40px;
            background: rgba(255, 255, 255, 0.2);
            border: none;
            border-radius: 50%;
            color: #fff;
            font-size: 24px;
            cursor: pointer;
            display: flex;
            align-items: center;
            justify-content: center;
            transition: all 0.3s ease;
        }
        
        .lightbox-close:hover {
            background: rgba(255, 255, 255, 0.3);
            transform: rotate(90deg);
        }
        
        .lightbox-caption {
            position: absolute;
            bottom: -60px;
            left: 0;
            right: 0;
            text-align: center;
            color: #fff;
            font-size: 16px;
            padding: 12px;
            background: rgba(0, 0, 0, 0.5);
            border-radius: 8px;
        }
        
        @media (max-width: 768px) {
            .achievement-detail {
                padding: 100px 0 60px;
            }
            
            .achievement-detail-container {
                padding: 0 16px;
            }
            
            .achievement-detail-card {
                border-radius: 8px;
            }
            
            .achievement-detail-body {
                padding: 24px 20px;
            }
            
            .achievement-detail-title {
                font-size: 24px;
            }
            
            .achievement-detail-meta {
                flex-direction: column;
                align-items: stretch;
                gap: 12px;
            }
            
            .achievement-meta-item,
            .achievement-detail-uploader {
                width: 100%;
                justify-content: flex-start;
            }
            
            .achievement-detail-content {
                font-size: 15px;
            }
            
            .lightbox-content {
                max-width: 95%;
                padding: 20px;
            }
        }
    </style>
</head>
<body>
    <!-- Header -->
    <?php include 'header.php'; ?>
    
    <section class="achievement-detail">
        <div class="achievement-detail-container">
            <div class="breadcrumb">
                <a href="/crims/">Home</a>
                <span>></span>
                <a href="/crims/#prestasi">Prestasi</a>
                <span>></span>
                <span><?= htmlspecialchars($achievement['title']) ?></span>
            </div>
            
            <div class="achievement-detail-card">
                <?php if (!empty($achievement['image_url'])): ?>
                    <div class="achievement-detail-image" onclick="openLightbox('<?= htmlspecialchars(achievementImageSrc($achievement['image_url'])) ?>', '<?= htmlspecialchars($achievement['title']) ?>')">
                        <img src="<?= htmlspecialchars(achievementImageSrc($achievement['image_url'])) ?>" alt="<?= htmlspecialchars($achievement['title']) ?>" loading="lazy">
                    </div>
                <?php else: ?>
                    <div class="achievement-detail-image" style="background: linear-gradient(135deg, #1e5ba8 0%, #2980b9 100%); min-height: 300px; display: flex; align-items: center; justify-content: center;">
                        <div class="achievement-icon-display">
                            <i class="<?= htmlspecialchars($achievement['icon_class']) ?>"></i>
                        </div>
                    </div>
                <?php endif; ?>
                
                <div class="achievement-detail-body">
                    <h1 class="achievement-detail-title"><?= htmlspecialchars($achievement['title']) ?></h1>
                    
                    <div class="achievement-detail-meta">
                        <div class="achievement-meta-item" title="Tanggal Upload">
                            <i class="fas fa-calendar-alt"></i>
                            <span><?= date('d M Y', strtotime($achievement['created_at'])) ?></span>
                        </div>
                        <div class="achievement-detail-uploader" title="Upload oleh Admin">
                            <div class="achievement-detail-uploader-icon admin">
                                <i class="fas fa-user-shield"></i>
                            </div>
                            <span>Admin</span>
                        </div>
                        <?php if ($achievement['updated_at'] && $achievement['updated_at'] !== $achievement['created_at']): ?>
                            <div class="achievement-meta-item" title="Terakhir Diperbarui">
                                <i class="fas fa-edit"></i>
                                <span>Diperbarui: <?= date('d M Y', strtotime($achievement['updated_at'])) ?></span>
                            </div>
                        <?php endif; ?>
                    </div>
                    
                    <div class="achievement-detail-content">
                        <?php if (!empty($achievement['description'])): ?>
                            <div class="achievement-detail-description">
                                <?= renderSafeHtml($achievement['description']) ?>
                            </div>
                        <?php else: ?>
                            <p>Deskripsi prestasi akan segera ditambahkan.</p>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </section>
    
    <!-- Lightbox -->
    <div class="lightbox" id="lightbox" onclick="closeLightbox(event)">
        <div class="lightbox-content">
            <button class="lightbox-close" onclick="closeLightbox()">
                <i class="fas fa-times"></i>
            </button>
            <img id="lightboxImage" src="" alt="">
            <div class="lightbox-caption" id="lightboxCaption"></div>
        </div>
    </div>
    
    <!-- Footer -->
    <?php include 'footer.php'; ?>
    
    <script src="js/script.js"></script>
    <script>
        function openLightbox(imageSrc, caption) {
            const lightbox = document.getElementById('lightbox');
            const lightboxImage = document.getElementById('lightboxImage');
            const lightboxCaption = document.getElementById('lightboxCaption');
            
            lightboxImage.src = imageSrc;
            lightboxCaption.textContent = caption;
            lightbox.classList.add('active');
            document.body.style.overflow = 'hidden';
        }
        
        function closeLightbox(event) {
            if (event && event.target.id !== 'lightbox') {
                return;
            }
            const lightbox = document.getElementById('lightbox');
            lightbox.classList.remove('active');
            document.body.style.overflow = '';
        }
        
        // Close on ESC key
        document.addEventListener('keydown', function(e) {
            if (e.key === 'Escape') {
                closeLightbox();
            }
        });
    </script>
</body>
</html>

