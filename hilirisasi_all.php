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

// Get all hilirisasi items with pagination
$page = (int) ($_GET['page'] ?? 1);
$perPage = 12;
$offset = ($page - 1) * $perPage;

// Get total count
$countQuery = $mysqli->query('SELECT COUNT(*) as total FROM hilirisasi_items');
$totalHilirisasi = $countQuery->fetch_assoc()['total'];
$countQuery->free();
$totalPages = ceil($totalHilirisasi / $perPage);

// Get hilirisasi items
$hilirisasiItems = [];
$perPage = (int) $perPage;
$offset = (int) $offset;
$hilirisasiQuery = $mysqli->query("SELECT id, title, description, image_url, detail_url, created_at FROM hilirisasi_items ORDER BY sort_order ASC, created_at DESC LIMIT {$perPage} OFFSET {$offset}");
if ($hilirisasiQuery) {
    $hilirisasiItems = $hilirisasiQuery->fetch_all(MYSQLI_ASSOC);
    $hilirisasiQuery->free();
}

function hilirisasiImageSrc(?string $path): string {
    if ($path) {
        return '/crims/' . ltrim($path, '/');
    }
    return 'https://images.unsplash.com/photo-1551434678-e076c223a692?ixlib=rb-1.2.1&auto=format&fit=crop&w=1950&q=80';
}

$pageTitle = 'Hilirisasi - CRiMS';
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $pageTitle ?></title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&family=Plus+Jakarta+Sans:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="css/styles.css">
    <style>
        .hilirisasi-page {
            padding: 120px 0 80px;
            background: #f8f9fa;
            min-height: 100vh;
        }
        
        .hilirisasi-page-header {
            text-align: center;
            margin-bottom: 60px;
        }
        
        .hilirisasi-page-header h1 {
            font-size: 42px;
            font-weight: 700;
            color: #1d2327;
            margin-bottom: 16px;
            font-family: 'Poppins', sans-serif;
        }
        
        .hilirisasi-page-header p {
            font-size: 18px;
            color: #646970;
            max-width: 600px;
            margin: 0 auto;
        }
        
        .hilirisasi-grid-full {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(350px, 1fr));
            gap: 32px;
            margin-bottom: 60px;
        }
        
        .pagination {
            display: flex;
            justify-content: center;
            align-items: center;
            gap: 12px;
            margin-top: 60px;
        }
        
        .pagination a,
        .pagination span {
            padding: 12px 18px;
            border-radius: 8px;
            text-decoration: none;
            font-weight: 600;
            transition: all 0.3s ease;
            border: 2px solid #e8e8e8;
            color: #1d2327;
            background: #fff;
        }
        
        .pagination a:hover {
            background: #1e5ba8;
            color: #fff;
            border-color: #1e5ba8;
            transform: translateY(-2px);
        }
        
        .pagination .current {
            background: #1e5ba8;
            color: #fff;
            border-color: #1e5ba8;
        }
        
        .empty-state {
            text-align: center;
            padding: 80px 20px;
        }
        
        .empty-state i {
            font-size: 64px;
            color: #c3c4c7;
            margin-bottom: 24px;
        }
        
        .empty-state h3 {
            font-size: 24px;
            color: #1d2327;
            margin-bottom: 12px;
        }
        
        .empty-state p {
            font-size: 16px;
            color: #646970;
        }
        
        @media (max-width: 768px) {
            .hilirisasi-grid-full {
                grid-template-columns: 1fr;
                gap: 24px;
            }
            
            .hilirisasi-page-header h1 {
                font-size: 32px;
            }
        }
    </style>
</head>
<body>
    <?php include 'header.php'; ?>
    
    <div class="hilirisasi-page">
        <div class="container">
            <div class="hilirisasi-page-header">
                <h1>Hilirisasi</h1>
                <p>Produk dan teknologi hasil penelitian yang telah dihilirisasikan untuk kepentingan industri dan masyarakat</p>
            </div>
            
            <?php if (empty($hilirisasiItems)): ?>
                <div class="empty-state">
                    <i class="fas fa-industry"></i>
                    <h3>Belum Ada Hilirisasi</h3>
                    <p>Hilirisasi akan segera ditampilkan di sini.</p>
                </div>
            <?php else: ?>
                <div class="hilirisasi-grid-full">
                    <?php foreach ($hilirisasiItems as $index => $item): ?>
                        <a href="/crims/hilirisasi_detail.php?id=<?= $item['id'] ?>" class="news-card-link <?= $index % 2 === 0 ? 'news-vertical' : 'news-horizontal' ?>">
                            <div class="news-card hilirisasi-card" data-index="<?= $index ?>" data-layout="<?= $index % 2 === 0 ? 'vertical' : 'horizontal' ?>">
                                <div class="news-image">
                                    <img src="<?= htmlspecialchars(hilirisasiImageSrc($item['image_url'])) ?>" alt="<?= htmlspecialchars($item['title']) ?>" loading="lazy">
                                </div>
                                <div class="news-content">
                                    <h3><?= htmlspecialchars($item['title']) ?></h3>
                                    <?php if (!empty($item['description'])): ?>
                                        <div class="news-summary"><?= renderSafeHtml($item['description']) ?></div>
                                    <?php endif; ?>
                                    <div class="news-meta">
                                        <div class="news-meta-item" title="Tanggal Upload">
                                            <i class="fas fa-calendar-alt"></i>
                                            <span><?= date('d M Y', strtotime($item['created_at'])) ?></span>
                                        </div>
                                        <div class="news-meta-item" title="Selengkapnya">
                                            <i class="fas fa-arrow-right"></i>
                                            <span>Selengkapnya</span>
                                        </div>
                                        <?php if (!empty($item['detail_url'])): ?>
                                            <a href="<?= htmlspecialchars($item['detail_url']) ?>" target="_blank" class="news-meta-item" title="Lihat Detail" onclick="event.stopPropagation();" style="text-decoration: none; color: inherit;">
                                                <i class="fas fa-external-link-alt"></i>
                                                <span>Detail</span>
                                            </a>
                                        <?php endif; ?>
                                    </div>
                                </div>
                                <div class="news-accent"></div>
                            </div>
                        </a>
                    <?php endforeach; ?>
                </div>
                
                <?php if ($totalPages > 1): ?>
                <div class="pagination">
                    <?php if ($page > 1): ?>
                        <a href="?page=<?= $page - 1 ?>"><i class="fas fa-chevron-left"></i> Sebelumnya</a>
                    <?php endif; ?>
                    
                    <?php for ($i = 1; $i <= $totalPages; $i++): ?>
                        <?php if ($i == $page): ?>
                            <span class="current"><?= $i ?></span>
                        <?php else: ?>
                            <a href="?page=<?= $i ?>"><?= $i ?></a>
                        <?php endif; ?>
                    <?php endfor; ?>
                    
                    <?php if ($page < $totalPages): ?>
                        <a href="?page=<?= $page + 1 ?>">Selanjutnya <i class="fas fa-chevron-right"></i></a>
                    <?php endif; ?>
                </div>
                <?php endif; ?>
            <?php endif; ?>
        </div>
    </div>
    
    <?php include 'footer.php'; ?>
</body>
</html>

