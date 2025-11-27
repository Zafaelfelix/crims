<?php
require_once __DIR__ . '/config.php';

$id = (int) ($_GET['id'] ?? 0);

if ($id <= 0) {
    header('Location: /crims/');
    exit;
}

$query = $mysqli->prepare('SELECT p.id, p.title, p.category, p.summary, p.image_url, p.detail_url, p.created_at, p.updated_at, p.created_by, u.role, u.full_name, u.username FROM project_items p LEFT JOIN users u ON p.created_by = u.id WHERE p.id = ?');
$query->bind_param('i', $id);
$query->execute();
$result = $query->get_result();
$project = $result->fetch_assoc();
$query->close();

if (!$project) {
    header('Location: /crims/');
    exit;
}

function projectImageSrc(?string $path): string {
    if ($path) {
        return '/crims/' . ltrim($path, '/');
    }
    return 'https://images.unsplash.com/photo-1551288049-bebda4e38f71?ixlib=rb-1.2.1&auto=format&fit=crop&w=1950&q=80';
}

// Get uploader info from database
$uploaderRole = $project['role'] ?? 'admin';
$uploaderName = !empty($project['full_name']) ? $project['full_name'] : ($project['username'] ?? 'Admin');
// Capitalize first letter of role for display
$uploaderRoleDisplay = ucfirst($uploaderRole);
$projectDate = $project['created_at'];

// Format tanggal Indonesia
function formatDateIndonesian($date) {
    if (empty($date)) return '';
    
    $months = [
        1 => 'Januari', 2 => 'Februari', 3 => 'Maret', 4 => 'April',
        5 => 'Mei', 6 => 'Juni', 7 => 'Juli', 8 => 'Agustus',
        9 => 'September', 10 => 'Oktober', 11 => 'November', 12 => 'Desember'
    ];
    
    $days = [
        'Minggu', 'Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat', 'Sabtu'
    ];
    
    $timestamp = strtotime($date);
    $day = $days[date('w', $timestamp)];
    $dateNum = date('j', $timestamp);
    $month = $months[(int)date('n', $timestamp)];
    $year = date('Y', $timestamp);
    
    return "$day, $dateNum $month $year";
}

$pageTitle = htmlspecialchars($project['title']);
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
        .project-detail {
            padding: 120px 0 80px;
            background: #f8f9fa;
            min-height: 100vh;
        }
        
        .project-detail-container {
            max-width: 1000px;
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
        }
        
        .breadcrumb span {
            margin: 0 8px;
            color: #999;
        }
        
        .project-detail-card {
            background: #ffffff;
            border-radius: 20px;
            overflow: hidden;
            box-shadow: 0 10px 40px rgba(0, 0, 0, 0.08);
        }
        
        .project-detail-image {
            width: 100%;
            height: 500px;
            overflow: hidden;
            background: linear-gradient(135deg, #f1f5f9 0%, #e2e8f0 100%);
            position: relative;
        }
        
        .project-detail-image img {
            width: 100%;
            height: 100%;
            object-fit: cover;
            display: block;
        }
        
        .project-detail-content {
            padding: 40px;
        }
        
        .project-detail-header {
            margin-bottom: 30px;
            padding-bottom: 20px;
            border-bottom: 2px solid #f1f5f9;
        }
        
        .project-category-badge {
            display: inline-block;
            background: linear-gradient(135deg, #1e5ba8, #2980b9);
            color: #ffffff;
            font-size: 0.85rem;
            font-weight: 600;
            padding: 8px 20px;
            border-radius: 50px;
            margin-bottom: 15px;
        }
        
        .project-detail-title {
            font-size: 2.2rem;
            font-weight: 700;
            color: #1a1a1a;
            margin-bottom: 20px;
            line-height: 1.3;
        }
        
        .project-detail-meta {
            display: flex;
            flex-wrap: wrap;
            gap: 20px;
            align-items: center;
            margin-top: 20px;
        }
        
        .project-meta-item {
            display: flex;
            align-items: center;
            gap: 8px;
            color: #64748b;
            font-size: 0.9rem;
        }
        
        .project-meta-item i {
            color: #1e5ba8;
            font-size: 1rem;
        }
        
        .project-uploader {
            display: flex;
            align-items: center;
            gap: 10px;
            padding: 8px 16px;
            background: linear-gradient(135deg, #f8fafc 0%, #f1f5f9 100%);
            border-radius: 8px;
        }
        
        .project-uploader-icon {
            width: 32px;
            height: 32px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 0.85rem;
            color: #fff;
            background: linear-gradient(135deg, #1e5ba8 0%, #2980b9 100%);
            box-shadow: 0 2px 8px rgba(30, 91, 168, 0.2);
        }
        
        .project-uploader-info {
            display: flex;
            flex-direction: column;
        }
        
        .project-uploader-name {
            font-weight: 600;
            color: #1a1a1a;
            font-size: 0.9rem;
        }
        
        .project-uploader-role {
            font-size: 0.75rem;
            color: #64748b;
        }
        
        .project-detail-body {
            margin-top: 30px;
        }
        
        .project-detail-summary {
            font-size: 1.1rem;
            line-height: 1.8;
            color: #475569;
            margin-bottom: 30px;
        }
        
        .project-detail-actions {
            margin-top: 40px;
            padding-top: 30px;
            border-top: 2px solid #f1f5f9;
            display: flex;
            gap: 15px;
            flex-wrap: wrap;
        }
        
        .btn-back {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            padding: 12px 24px;
            background: #f1f5f9;
            color: #1e5ba8;
            text-decoration: none;
            border-radius: 12px;
            font-weight: 600;
            transition: all 0.3s ease;
        }
        
        .btn-back:hover {
            background: #e2e8f0;
            transform: translateX(-3px);
        }
        
        .btn-external {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            padding: 12px 24px;
            background: linear-gradient(135deg, #1e5ba8, #2980b9);
            color: #ffffff;
            text-decoration: none;
            border-radius: 12px;
            font-weight: 600;
            transition: all 0.3s ease;
        }
        
        .btn-external:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 20px rgba(30, 91, 168, 0.3);
        }
        
        @media (max-width: 768px) {
            .project-detail {
                padding: 100px 0 60px;
            }
            
            .project-detail-content {
                padding: 24px;
            }
            
            .project-detail-title {
                font-size: 1.6rem;
            }
            
            .project-detail-image {
                height: 300px;
            }
            
            .project-detail-meta {
                flex-direction: column;
                align-items: flex-start;
            }
        }
    </style>
</head>
<body>
    <!-- Page Loader -->
    <div class="page-loader" id="pageLoader">
        <div class="loader-content">
            <div class="loader-spinner"></div>
            <div class="loader-text">CRIMS</div>
            <div class="loader-subtext">Loading...</div>
        </div>
    </div>
    
    <!-- Header -->
    <?php include 'header.php'; ?>
    
    <section class="project-detail">
        <div class="project-detail-container">
            <div class="breadcrumb">
                <a href="/crims/">Home</a>
                <span>></span>
                <a href="/crims/#proyek">Proyek</a>
                <span>></span>
                <span><?= htmlspecialchars($project['title']) ?></span>
            </div>
            
            <div class="project-detail-card">
                <div class="project-detail-image">
                    <img src="<?= htmlspecialchars(projectImageSrc($project['image_url'])) ?>" alt="<?= htmlspecialchars($project['title']) ?>" loading="lazy">
                </div>
                
                <div class="project-detail-content">
                    <div class="project-detail-header">
                        <?php if (!empty($project['category'])): ?>
                            <span class="project-category-badge"><?= htmlspecialchars($project['category']) ?></span>
                        <?php endif; ?>
                        
                        <h1 class="project-detail-title"><?= htmlspecialchars($project['title']) ?></h1>
                        
                        <div class="project-detail-meta">
                            <div class="project-meta-item">
                                <i class="fas fa-calendar-alt"></i>
                                <span>Dipublikasikan: <?= formatDateIndonesian($projectDate) ?></span>
                            </div>
                            
                            <div class="project-uploader">
                                <div class="project-uploader-icon">
                                    <i class="fas fa-user"></i>
                                </div>
                                <div class="project-uploader-info">
                                    <span class="project-uploader-name"><?= htmlspecialchars($uploaderRoleDisplay) ?></span>
                                    <span class="project-uploader-role"><?= htmlspecialchars($uploaderName) ?></span>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="project-detail-body">
                        <?php if (!empty($project['summary'])): ?>
                            <div class="project-detail-summary">
                                <?= $project['summary'] ?>
                            </div>
                        <?php endif; ?>
                        
                        <div class="project-detail-actions">
                            <a href="/crims/#proyek" class="btn-back">
                                <i class="fas fa-arrow-left"></i>
                                Kembali ke Proyek
                            </a>
                            
                            <?php if (!empty($project['detail_url'])): ?>
                                <a href="<?= htmlspecialchars($project['detail_url']) ?>" class="btn-external" target="_blank" rel="noopener noreferrer">
                                    <i class="fas fa-external-link-alt"></i>
                                    Lihat Detail Lengkap
                                </a>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
    
    <!-- Footer -->
    <?php include 'footer.php'; ?>
    
    <script src="js/script.js"></script>
    <script>
        // Page loader for project detail
        window.addEventListener('load', function() {
            const pageLoader = document.getElementById('pageLoader');
            if (pageLoader) {
                setTimeout(() => {
                    pageLoader.classList.add('hidden');
                }, 600);
            }
        });
    </script>
</body>
</html>

