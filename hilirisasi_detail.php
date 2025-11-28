<?php
require_once __DIR__ . '/config.php';

// Function to safely render HTML content
if (!function_exists('renderSafeHtml')) {
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
}

$id = (int) ($_GET['id'] ?? 0);

if ($id <= 0) {
    header('Location: /crims/');
    exit;
}

// Check if created_by column exists
$checkColumn = $mysqli->query("SHOW COLUMNS FROM hilirisasi_items LIKE 'created_by'");
$hasCreatedBy = $checkColumn && $checkColumn->num_rows > 0;
if ($checkColumn) $checkColumn->free();

if ($hasCreatedBy) {
    $query = $mysqli->prepare('SELECT h.id, h.title, h.description, h.image_url, h.detail_url, h.created_at, h.updated_at, h.created_by, u.role, u.full_name, u.username FROM hilirisasi_items h LEFT JOIN users u ON h.created_by = u.id WHERE h.id = ?');
} else {
    $query = $mysqli->prepare('SELECT h.id, h.title, h.description, h.image_url, h.detail_url, h.created_at, h.updated_at, NULL as created_by, NULL as role, NULL as full_name, NULL as username FROM hilirisasi_items h WHERE h.id = ?');
}
$query->bind_param('i', $id);
$query->execute();
$result = $query->get_result();
$hilirisasi = $result->fetch_assoc();
$query->close();

if (!$hilirisasi) {
    header('Location: /crims/');
    exit;
}

function hilirisasiImageSrc(?string $path): string {
    if ($path) {
        return '/crims/' . ltrim($path, '/');
    }
    return 'https://images.unsplash.com/photo-1551434678-e076c223a692?ixlib=rb-1.2.1&auto=format&fit=crop&w=1950&q=80';
}

// Get uploader info from database
$uploaderRole = $hilirisasi['role'] ?? 'admin';
$uploaderName = !empty($hilirisasi['full_name']) ? $hilirisasi['full_name'] : ($hilirisasi['username'] ?? 'Admin');
// Capitalize first letter of role for display
$uploaderRoleDisplay = ucfirst($uploaderRole);
$hilirisasiDate = $hilirisasi['created_at'];

$pageTitle = htmlspecialchars($hilirisasi['title']);
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
        .hilirisasi-detail {
            padding: 120px 0 80px;
            background: #f8f9fa;
        }
        
        .hilirisasi-detail-container {
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
        
        .hilirisasi-detail-card {
            background: #ffffff;
            border-radius: 16px;
            box-shadow: 0 4px 24px rgba(0, 0, 0, 0.08);
            overflow: hidden;
            margin-bottom: 40px;
        }
        
        .hilirisasi-detail-image {
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
        
        .hilirisasi-detail-image:hover {
            transform: scale(1.01);
        }
        
        .hilirisasi-detail-image img {
            width: 100%;
            height: auto;
            display: block;
            object-fit: cover;
            max-height: 500px;
        }
        
        .hilirisasi-detail-image::after {
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
        
        .hilirisasi-detail-image:hover::after {
            opacity: 1;
        }
        
        .hilirisasi-detail-body {
            padding: 40px 48px;
        }
        
        .hilirisasi-detail-title {
            font-size: 32px;
            font-weight: 700;
            color: #1a1a1a;
            margin-bottom: 20px;
            line-height: 1.3;
            letter-spacing: -0.5px;
        }
        
        .hilirisasi-detail-meta {
            display: flex;
            align-items: center;
            gap: 20px;
            flex-wrap: wrap;
            margin-bottom: 32px;
            padding-bottom: 24px;
            border-bottom: 2px solid #e8e8f0;
        }
        
        .hilirisasi-meta-item {
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
        
        .hilirisasi-meta-item:hover {
            background: linear-gradient(135deg, #eff6ff 0%, #dbeafe 100%);
            color: #1e5ba8;
        }
        
        .hilirisasi-meta-item i {
            font-size: 16px;
            color: #1e5ba8;
        }
        
        .hilirisasi-detail-uploader {
            display: flex;
            align-items: center;
            gap: 10px;
            padding: 10px 16px;
            background: linear-gradient(135deg, #f8fafc 0%, #f1f5f9 100%);
            border-radius: 10px;
            transition: all 0.3s ease;
        }
        
        .hilirisasi-detail-uploader:hover {
            background: linear-gradient(135deg, #eff6ff 0%, #dbeafe 100%);
        }
        
        .hilirisasi-detail-uploader-icon {
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
        
        .hilirisasi-detail-uploader-icon.admin {
            background: linear-gradient(135deg, #1e5ba8 0%, #2980b9 100%);
        }
        
        .hilirisasi-detail-uploader-icon.mahasiswa {
            background: linear-gradient(135deg, #10b981 0%, #059669 100%);
        }
        
        .hilirisasi-detail-uploader-icon.dosen {
            background: linear-gradient(135deg, #f59e0b 0%, #d97706 100%);
        }
        
        .hilirisasi-detail-uploader span {
            font-weight: 500;
            font-size: 14px;
            color: #475569;
        }
        
        .hilirisasi-detail-uploader:hover span {
            color: #1e5ba8;
        }
        
        .hilirisasi-detail-content {
            font-size: 18px;
            line-height: 1.9;
            color: #333;
            margin-bottom: 0;
        }
        
        .hilirisasi-detail-content p {
            margin-bottom: 24px;
        }
        
        .hilirisasi-detail-content p:last-child {
            margin-bottom: 0;
        }
        
        .hilirisasi-detail-content p:first-child {
            font-size: 20px;
            font-weight: 500;
            color: #1a1a1a;
            line-height: 1.8;
        }
        
        .hilirisasi-external-link {
            margin-top: 32px;
            padding-top: 24px;
            border-top: 2px solid #e8e8f0;
        }
        
        .hilirisasi-external-link a {
            display: inline-flex;
            align-items: center;
            gap: 10px;
            padding: 14px 28px;
            background: linear-gradient(135deg, #1e5ba8 0%, #2980b9 100%);
            color: #ffffff;
            text-decoration: none;
            border-radius: 10px;
            font-weight: 600;
            transition: all 0.3s ease;
            box-shadow: 0 4px 12px rgba(30, 91, 168, 0.3);
        }
        
        .hilirisasi-external-link a:hover {
            background: linear-gradient(135deg, #2980b9 0%, #1e5ba8 100%);
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(30, 91, 168, 0.4);
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
            .hilirisasi-detail {
                padding: 100px 0 60px;
            }
            
            .hilirisasi-detail-container {
                padding: 0 16px;
            }
            
            .hilirisasi-detail-card {
                border-radius: 12px;
            }
            
            .hilirisasi-detail-body {
                padding: 24px 20px;
            }
            
            .hilirisasi-detail-title {
                font-size: 24px;
            }
            
            .hilirisasi-detail-meta {
                flex-direction: column;
                align-items: stretch;
                gap: 12px;
            }
            
            .hilirisasi-meta-item,
            .hilirisasi-detail-uploader {
                width: 100%;
                justify-content: flex-start;
            }
            
            .hilirisasi-detail-content {
                font-size: 16px;
            }
            
            .hilirisasi-detail-content p:first-child {
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
    
    <section class="hilirisasi-detail">
        <div class="hilirisasi-detail-container">
            <div class="breadcrumb">
                <a href="/crims/">Home</a>
                <span>></span>
                <a href="/crims/#hilirisasi">Hilirisasi</a>
                <span>></span>
                <span><?= htmlspecialchars($hilirisasi['title']) ?></span>
            </div>
            
            <div class="hilirisasi-detail-card">
                <?php if (!empty($hilirisasi['image_url'])): ?>
                <div class="hilirisasi-detail-image" onclick="openLightbox('<?= htmlspecialchars(hilirisasiImageSrc($hilirisasi['image_url'])) ?>', '<?= htmlspecialchars($hilirisasi['title']) ?>')">
                    <img src="<?= htmlspecialchars(hilirisasiImageSrc($hilirisasi['image_url'])) ?>" alt="<?= htmlspecialchars($hilirisasi['title']) ?>" loading="lazy">
                </div>
                <?php endif; ?>
                
                <div class="hilirisasi-detail-body">
                    <h1 class="hilirisasi-detail-title"><?= htmlspecialchars($hilirisasi['title']) ?></h1>
                    
                    <div class="hilirisasi-detail-meta">
                        <div class="hilirisasi-meta-item" title="Tanggal Upload">
                            <i class="fas fa-calendar-alt"></i>
                            <span><?= date('d M Y', strtotime($hilirisasiDate)) ?></span>
                        </div>
                        <?php if ($hasCreatedBy && !empty($hilirisasi['role'])): ?>
                        <div class="hilirisasi-detail-uploader" title="Upload oleh <?= htmlspecialchars($uploaderName) ?> (<?= htmlspecialchars($uploaderRoleDisplay) ?>)">
                            <div class="hilirisasi-detail-uploader-icon <?= $uploaderRole ?>">
                                <i class="fas fa-<?= $uploaderRole === 'admin' ? 'user-shield' : ($uploaderRole === 'mahasiswa' ? 'user-graduate' : 'chalkboard-teacher') ?>"></i>
                            </div>
                            <span><?= htmlspecialchars($uploaderRoleDisplay) ?></span>
                        </div>
                        <?php endif; ?>
                        <?php if ($hilirisasi['updated_at'] && $hilirisasi['updated_at'] !== $hilirisasi['created_at']): ?>
                            <div class="hilirisasi-meta-item" title="Terakhir Diperbarui">
                                <i class="fas fa-edit"></i>
                                <span>Diperbarui: <?= date('d M Y', strtotime($hilirisasi['updated_at'])) ?></span>
                            </div>
                        <?php endif; ?>
                    </div>
                    
                    <div class="hilirisasi-detail-content">
                        <?php if (!empty($hilirisasi['description'])): ?>
                            <div class="hilirisasi-detail-description">
                                <?= renderSafeHtml($hilirisasi['description']) ?>
                            </div>
                        <?php else: ?>
                            <p>Deskripsi hilirisasi akan segera ditambahkan.</p>
                        <?php endif; ?>
                    </div>
                    
                    <?php if (!empty($hilirisasi['detail_url'])): ?>
                    <div class="hilirisasi-external-link">
                        <a href="<?= htmlspecialchars($hilirisasi['detail_url']) ?>" target="_blank">
                            <i class="fas fa-external-link-alt"></i>
                            <span>Lihat Detail Lengkap</span>
                        </a>
                    </div>
                    <?php endif; ?>
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

