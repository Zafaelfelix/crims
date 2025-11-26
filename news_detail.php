<?php
require_once __DIR__ . '/config.php';

$id = (int) ($_GET['id'] ?? 0);

if ($id <= 0) {
    header('Location: /crims/');
    exit;
}

$query = $mysqli->prepare('SELECT id, title, summary, image_url, article_url, published_at, created_at, updated_at FROM news_items WHERE id = ?');
$query->bind_param('i', $id);
$query->execute();
$result = $query->get_result();
$news = $result->fetch_assoc();
$query->close();

if (!$news) {
    header('Location: /crims/');
    exit;
}

function newsImageSrc(?string $path): string {
    if ($path) {
        return '/crims/' . ltrim($path, '/');
    }
    return 'https://images.unsplash.com/photo-1521791136064-7986c2920216?ixlib=rb-1.2.1&auto=format&fit=crop&w=1950&q=80';
}

$uploaderRole = 'admin';
$uploaderName = 'Admin';
$newsDate = !empty($news['published_at']) ? $news['published_at'] : $news['created_at'];

$pageTitle = htmlspecialchars($news['title']);
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $pageTitle ?> - CRIMS</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&family=Plus+Jakarta+Sans:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="css/styles.css">
    <style>
        .news-detail {
            padding: 120px 0 80px;
            background: #f8f9fa;
        }
        
        .news-detail-container {
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
            transition: color 0.3s ease;
        }
        
        .breadcrumb a:hover {
            color: #2980b9;
            text-decoration: underline;
        }
        
        .breadcrumb span {
            margin: 0 8px;
            color: #999;
        }
        
        .news-detail-card {
            background: #ffffff;
            border-radius: 16px;
            box-shadow: 0 4px 24px rgba(0, 0, 0, 0.08);
            overflow: hidden;
            margin-bottom: 40px;
        }
        
        .news-detail-image {
            width: 100%;
            margin-bottom: 0;
            border-radius: 0;
            overflow: hidden;
            cursor: pointer;
            position: relative;
            transition: transform 0.3s ease;
            background: #f0f0f0;
            max-height: 500px;
        }
        
        .news-detail-image:hover {
            transform: scale(1.01);
        }
        
        .news-detail-image img {
            width: 100%;
            height: auto;
            display: block;
            object-fit: cover;
            max-height: 500px;
        }
        
        .news-detail-image::after {
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
        
        .news-detail-image:hover::after {
            opacity: 1;
        }
        
        .news-detail-body {
            padding: 40px 48px;
        }
        
        .news-detail-title {
            font-size: 32px;
            font-weight: 700;
            color: #1a1a1a;
            margin-bottom: 20px;
            line-height: 1.3;
            letter-spacing: -0.5px;
        }
        
        .news-detail-meta {
            display: flex;
            align-items: center;
            gap: 20px;
            flex-wrap: wrap;
            margin-bottom: 32px;
            padding-bottom: 24px;
            border-bottom: 2px solid #e8e8f0;
        }
        
        .news-meta-item {
            display: flex;
            align-items: center;
            gap: 10px;
            padding: 10px 16px;
            background: linear-gradient(135deg, #f8fafc 0%, #f1f5f9 100%);
            border-radius: 10px;
            color: #475569;
            font-size: 14px;
            font-weight: 500;
            transition: all 0.3s ease;
        }
        
        .news-meta-item:hover {
            background: linear-gradient(135deg, #eff6ff 0%, #dbeafe 100%);
            color: #1e5ba8;
        }
        
        .news-meta-item i {
            font-size: 16px;
            color: #1e5ba8;
        }
        
        .news-detail-uploader {
            display: flex;
            align-items: center;
            gap: 10px;
            padding: 10px 16px;
            background: linear-gradient(135deg, #f8fafc 0%, #f1f5f9 100%);
            border-radius: 10px;
            transition: all 0.3s ease;
        }
        
        .news-detail-uploader:hover {
            background: linear-gradient(135deg, #eff6ff 0%, #dbeafe 100%);
        }
        
        .news-detail-uploader-icon {
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
        
        .news-detail-uploader-icon.admin {
            background: linear-gradient(135deg, #1e5ba8 0%, #2980b9 100%);
        }
        
        .news-detail-uploader-icon.mahasiswa {
            background: linear-gradient(135deg, #10b981 0%, #059669 100%);
        }
        
        .news-detail-uploader-icon.dosen {
            background: linear-gradient(135deg, #f59e0b 0%, #d97706 100%);
        }
        
        .news-detail-uploader span {
            font-weight: 500;
            font-size: 14px;
            color: #475569;
        }
        
        .news-detail-uploader:hover span {
            color: #1e5ba8;
        }
        
        .news-detail-content {
            font-size: 18px;
            line-height: 1.9;
            color: #333;
            margin-bottom: 0;
        }
        
        .news-detail-content p {
            margin-bottom: 24px;
            text-align: center;
        }
        
        .news-detail-content p:last-child {
            margin-bottom: 0;
        }
        
        .news-detail-content p:first-child {
            font-size: 20px;
            font-weight: 500;
            color: #1a1a1a;
            line-height: 1.8;
            text-align: center;
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
            .news-detail {
                padding: 100px 0 60px;
            }
            
            .news-detail-container {
                padding: 0 16px;
            }
            
            .news-detail-card {
                border-radius: 12px;
            }
            
            .news-detail-body {
                padding: 24px 20px;
            }
            
            .news-detail-title {
                font-size: 24px;
            }
            
            .news-detail-meta {
                flex-direction: column;
                align-items: stretch;
                gap: 12px;
            }
            
            .news-meta-item,
            .news-detail-uploader {
                width: 100%;
                justify-content: flex-start;
            }
            
            .news-detail-content {
                font-size: 16px;
            }
            
            .news-detail-content p:first-child {
                font-size: 18px;
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
    
    <section class="news-detail">
        <div class="news-detail-container">
            <div class="breadcrumb">
                <a href="/crims/">Home</a>
                <span>></span>
                <a href="/crims/#berita">Berita</a>
                <span>></span>
                <span><?= htmlspecialchars($news['title']) ?></span>
            </div>
            
            <div class="news-detail-card">
                <?php if (!empty($news['image_url'])): ?>
                    <div class="news-detail-image" onclick="openLightbox('<?= htmlspecialchars(newsImageSrc($news['image_url'])) ?>', '<?= htmlspecialchars($news['title']) ?>')">
                        <img src="<?= htmlspecialchars(newsImageSrc($news['image_url'])) ?>" alt="<?= htmlspecialchars($news['title']) ?>" loading="lazy">
                    </div>
                <?php endif; ?>
                
                <div class="news-detail-body">
                    <h1 class="news-detail-title"><?= htmlspecialchars($news['title']) ?></h1>
                    
                    <div class="news-detail-meta">
                        <div class="news-meta-item" title="Tanggal Upload">
                            <i class="fas fa-calendar-alt"></i>
                            <span><?= date('d M Y', strtotime($newsDate)) ?></span>
                        </div>
                        <div class="news-detail-uploader" title="Upload oleh <?= htmlspecialchars($uploaderName) ?>">
                            <div class="news-detail-uploader-icon <?= $uploaderRole ?>">
                                <i class="fas fa-<?= $uploaderRole === 'admin' ? 'user-shield' : ($uploaderRole === 'mahasiswa' ? 'user-graduate' : 'chalkboard-teacher') ?>"></i>
                            </div>
                            <span><?= htmlspecialchars($uploaderName) ?></span>
                        </div>
                        <?php if ($news['updated_at'] && $news['updated_at'] !== $news['created_at']): ?>
                            <div class="news-meta-item" title="Terakhir Diperbarui">
                                <i class="fas fa-edit"></i>
                                <span>Diperbarui: <?= date('d M Y', strtotime($news['updated_at'])) ?></span>
                            </div>
                        <?php endif; ?>
                    </div>
                    
                    <div class="news-detail-content">
                        <?php if (!empty($news['summary'])): ?>
                            <?php 
                            $summary = $news['summary'];
                            // Split by newlines and create paragraphs
                            $paragraphs = explode("\n", $summary);
                            foreach ($paragraphs as $index => $paragraph) {
                                $paragraph = trim($paragraph);
                                if (!empty($paragraph)) {
                                    echo '<p>' . nl2br(htmlspecialchars($paragraph)) . '</p>';
                                }
                            }
                            ?>
                        <?php else: ?>
                            <p>Konten berita akan segera ditambahkan.</p>
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

