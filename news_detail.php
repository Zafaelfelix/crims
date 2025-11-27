<?php
require_once __DIR__ . '/config.php';

$id = (int) ($_GET['id'] ?? 0);

if ($id <= 0) {
    header('Location: /crims/');
    exit;
}

$query = $mysqli->prepare('SELECT n.id, n.title, n.summary, n.image_url, n.article_url, n.published_at, n.created_at, n.updated_at, n.created_by, u.role, u.full_name, u.username FROM news_items n LEFT JOIN users u ON n.created_by = u.id WHERE n.id = ?');
$query->bind_param('i', $id);
$query->execute();
$result = $query->get_result();
$news = $result->fetch_assoc();
$query->close();

// Get photos for this news and combine with main image
$allPhotos = [];
// Add main image as first photo if exists
if (!empty($news['image_url'])) {
    $allPhotos[] = [
        'photo_url' => $news['image_url'],
        'caption' => $news['title'],
        'is_main' => true
    ];
}

// Get additional photos from gallery
$newsPhotos = [];
// Check if table exists first
$tableCheck = $mysqli->query("SHOW TABLES LIKE 'news_photos'");
if ($tableCheck && $tableCheck->num_rows > 0) {
    $tableCheck->free();
    $stmt = $mysqli->prepare('SELECT id, photo_url, caption, sort_order FROM news_photos WHERE news_id = ? ORDER BY sort_order ASC');
    if ($stmt) {
        $stmt->bind_param('i', $id);
        $stmt->execute();
        $result = $stmt->get_result();
        $newsPhotos = $result->fetch_all(MYSQLI_ASSOC);
        $stmt->close();
    }
} else {
    if ($tableCheck) $tableCheck->free();
}

// Combine all photos
foreach ($newsPhotos as $photo) {
    $allPhotos[] = [
        'photo_url' => $photo['photo_url'],
        'caption' => $photo['caption'] ?: $news['title'],
        'is_main' => false
    ];
}

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

// Get uploader info from database
$uploaderRole = $news['role'] ?? 'admin';
$uploaderName = !empty($news['full_name']) ? $news['full_name'] : ($news['username'] ?? 'Admin');
// Capitalize first letter of role for display
$uploaderRoleDisplay = ucfirst($uploaderRole);
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

        .news-photo-slide .news-detail-image {
            margin-bottom: 0;
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

        /* News Photo Slider */
        .news-photo-slider-wrapper {
            position: relative;
            width: 100%;
            overflow: hidden;
            margin-bottom: 0;
        }

        .news-photo-slider {
            display: flex;
            transition: transform 0.5s cubic-bezier(0.4, 0, 0.2, 1);
            will-change: transform;
        }

        .news-photo-slide {
            min-width: 100%;
            flex-shrink: 0;
            display: none;
        }

        .news-photo-slide.active {
            display: block;
        }

        .news-slider-btn {
            position: absolute;
            top: 50%;
            transform: translateY(-50%);
            width: 50px;
            height: 50px;
            border-radius: 50%;
            background: rgba(255, 255, 255, 0.9);
            color: #1e5ba8;
            border: none;
            cursor: pointer;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.2rem;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.2);
            transition: all 0.3s ease;
            z-index: 10;
            opacity: 0.9;
        }

        .news-slider-btn:hover {
            opacity: 1;
            background: rgba(255, 255, 255, 1);
            transform: translateY(-50%) scale(1.1);
        }

        .news-slider-prev {
            left: 20px;
        }

        .news-slider-next {
            right: 20px;
        }

        .news-slider-dots {
            position: absolute;
            bottom: 20px;
            left: 50%;
            transform: translateX(-50%);
            display: flex;
            gap: 8px;
            z-index: 10;
        }

        .news-slider-dot {
            width: 12px;
            height: 12px;
            border-radius: 50%;
            background: rgba(255, 255, 255, 0.5);
            border: 2px solid rgba(255, 255, 255, 0.8);
            cursor: pointer;
            transition: all 0.3s ease;
        }

        .news-slider-dot.active {
            background: #1e5ba8;
            border-color: #1e5ba8;
            transform: scale(1.2);
        }

        .news-slider-dot:hover {
            background: rgba(255, 255, 255, 0.8);
        }

        @media (max-width: 768px) {
            .news-slider-btn {
                width: 40px;
                height: 40px;
                font-size: 1rem;
            }

            .news-slider-prev {
                left: 10px;
            }

            .news-slider-next {
                right: 10px;
            }

            .news-slider-dots {
                bottom: 10px;
            }
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
                <?php if (!empty($allPhotos)): ?>
                <div class="news-photo-slider-wrapper">
                    <div class="news-photo-slider" id="newsPhotoSlider">
                        <?php foreach ($allPhotos as $index => $photo): ?>
                        <div class="news-photo-slide <?= $index === 0 ? 'active' : '' ?>">
                            <div class="news-detail-image" onclick="openLightbox('<?= htmlspecialchars(newsImageSrc($photo['photo_url'])) ?>', '<?= htmlspecialchars($photo['caption']) ?>')">
                                <img src="<?= htmlspecialchars(newsImageSrc($photo['photo_url'])) ?>" alt="<?= htmlspecialchars($photo['caption']) ?>" loading="lazy">
                            </div>
                        </div>
                        <?php endforeach; ?>
                    </div>
                    <?php if (count($allPhotos) > 1): ?>
                    <button class="news-slider-btn news-slider-prev" id="newsSliderPrev" aria-label="Previous photo">
                        <i class="fas fa-arrow-left"></i>
                    </button>
                    <button class="news-slider-btn news-slider-next" id="newsSliderNext" aria-label="Next photo">
                        <i class="fas fa-arrow-right"></i>
                    </button>
                    <div class="news-slider-dots">
                        <?php foreach ($allPhotos as $index => $photo): ?>
                        <span class="news-slider-dot <?= $index === 0 ? 'active' : '' ?>" data-slide="<?= $index ?>"></span>
                        <?php endforeach; ?>
                    </div>
                    <?php endif; ?>
                </div>
                <?php endif; ?>
                
                <div class="news-detail-body">
                    <h1 class="news-detail-title"><?= htmlspecialchars($news['title']) ?></h1>
                    
                    <div class="news-detail-meta">
                        <div class="news-meta-item" title="Tanggal Upload">
                            <i class="fas fa-calendar-alt"></i>
                            <span><?= date('d M Y', strtotime($newsDate)) ?></span>
                        </div>
                        <div class="news-detail-uploader" title="Upload oleh <?= htmlspecialchars($uploaderName) ?> (<?= htmlspecialchars($uploaderRoleDisplay) ?>)">
                            <div class="news-detail-uploader-icon <?= $uploaderRole ?>">
                                <i class="fas fa-<?= $uploaderRole === 'admin' ? 'user-shield' : ($uploaderRole === 'mahasiswa' ? 'user-graduate' : 'chalkboard-teacher') ?>"></i>
                            </div>
                            <span><?= htmlspecialchars($uploaderRoleDisplay) ?></span>
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
                            <div class="news-detail-summary">
                                <?= $news['summary'] ?>
                            </div>
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
        // News Photo Slider
        <?php if (!empty($allPhotos) && count($allPhotos) > 1): ?>
        (function() {
            const slider = document.getElementById('newsPhotoSlider');
            const prevBtn = document.getElementById('newsSliderPrev');
            const nextBtn = document.getElementById('newsSliderNext');
            const dots = document.querySelectorAll('.news-slider-dot');
            const slides = document.querySelectorAll('.news-photo-slide');
            let currentIndex = 0;
            const totalSlides = slides.length;

            function updateSlider() {
                slides.forEach((slide, index) => {
                    slide.classList.remove('active');
                    if (index === currentIndex) {
                        slide.classList.add('active');
                    }
                });

                dots.forEach((dot, index) => {
                    dot.classList.remove('active');
                    if (index === currentIndex) {
                        dot.classList.add('active');
                    }
                });

                // Update button states
                if (prevBtn) {
                    prevBtn.style.opacity = currentIndex === 0 ? '0.5' : '0.9';
                    prevBtn.disabled = currentIndex === 0;
                }
                if (nextBtn) {
                    nextBtn.style.opacity = currentIndex >= totalSlides - 1 ? '0.5' : '0.9';
                    nextBtn.disabled = currentIndex >= totalSlides - 1;
                }
            }

            if (prevBtn) {
                prevBtn.addEventListener('click', function() {
                    if (currentIndex > 0) {
                        currentIndex--;
                        updateSlider();
                    }
                });
            }

            if (nextBtn) {
                nextBtn.addEventListener('click', function() {
                    if (currentIndex < totalSlides - 1) {
                        currentIndex++;
                        updateSlider();
                    }
                });
            }

            dots.forEach((dot, index) => {
                dot.addEventListener('click', function() {
                    currentIndex = index;
                    updateSlider();
                });
            });

            // Initialize
            updateSlider();

            // Auto-play (optional, uncomment if needed)
            // setInterval(function() {
            //     if (currentIndex < totalSlides - 1) {
            //         currentIndex++;
            //     } else {
            //         currentIndex = 0;
            //     }
            //     updateSlider();
            // }, 5000);
        })();
        <?php endif; ?>

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

