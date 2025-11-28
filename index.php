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

$partnerItems = [];
$partnerQuery = $mysqli->query('SELECT id, name, logo_url, website_url, category FROM partner_items ORDER BY sort_order ASC, name ASC');
if ($partnerQuery) {
    $partnerItems = $partnerQuery->fetch_all(MYSQLI_ASSOC);
    $partnerQuery->free();
}

$projectItems = [];
$projectQuery = $mysqli->query('SELECT id, title, category, summary, image_url, detail_url FROM project_items WHERE is_featured = 1 ORDER BY sort_order ASC, created_at DESC LIMIT 6');
if ($projectQuery) {
    $projectItems = $projectQuery->fetch_all(MYSQLI_ASSOC);
    $projectQuery->free();
}

$newsItems = [];
$newsQuery = $mysqli->query('SELECT n.id, n.title, n.summary, n.image_url, n.article_url, n.published_at, n.created_at, n.created_by, u.role, u.full_name, u.username FROM news_items n LEFT JOIN users u ON n.created_by = u.id ORDER BY n.sort_order ASC, n.published_at DESC, n.created_at DESC LIMIT 6');
if ($newsQuery) {
    $newsItems = $newsQuery->fetch_all(MYSQLI_ASSOC);
    $newsQuery->free();
}

$achievementItems = [];
// Check if image_url column exists, if not use query without it
$checkColumn = $mysqli->query("SHOW COLUMNS FROM achievement_items LIKE 'image_url'");
$hasImageColumn = $checkColumn && $checkColumn->num_rows > 0;
if ($checkColumn) $checkColumn->free();

if ($hasImageColumn) {
    $achievementQuery = $mysqli->query('SELECT a.id, a.title, a.description, a.icon_class, a.image_url, a.created_at, a.created_by, u.role, u.full_name, u.username FROM achievement_items a LEFT JOIN users u ON a.created_by = u.id ORDER BY a.sort_order ASC, a.created_at DESC LIMIT 6');
} else {
    $achievementQuery = $mysqli->query('SELECT a.id, a.title, a.description, a.icon_class, NULL as image_url, a.created_at, a.created_by, u.role, u.full_name, u.username FROM achievement_items a LEFT JOIN users u ON a.created_by = u.id ORDER BY a.sort_order ASC, a.created_at DESC LIMIT 6');
}
if ($achievementQuery) {
    $achievementItems = $achievementQuery->fetch_all(MYSQLI_ASSOC);
    $achievementQuery->free();
}

// Get Hilirisasi items from database
$hilirisasiItems = [];
$hilirisasiQuery = $mysqli->query('SELECT id, title, description, image_url, detail_url, created_at FROM hilirisasi_items ORDER BY sort_order ASC, created_at DESC LIMIT 6');
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

// Get About Us content from database
$aboutOverview = null;
$aboutFeatures = [];
$aboutImage = 'https://images.unsplash.com/photo-1573164713988-8665fc963095?ixlib=rb-1.2.1&auto=format&fit=crop&w=1950&q=80';
$aboutButtonText = 'Selengkapnya';
$aboutButtonUrl = '/tentang.html';

$stmt = $mysqli->prepare('SELECT content, extra FROM cms_sections WHERE slug = ?');
$slug = 'about_overview';
$stmt->bind_param('s', $slug);
$stmt->execute();
$result = $stmt->get_result();
if ($row = $result->fetch_assoc()) {
    $aboutOverview = $row['content'];
    if ($row['extra']) {
        $extra = json_decode($row['extra'], true);
        if ($extra) {
            $aboutImage = !empty($extra['image_url']) ? $extra['image_url'] : $aboutImage;
            $aboutButtonText = !empty($extra['button_text']) ? $extra['button_text'] : $aboutButtonText;
            $aboutButtonUrl = !empty($extra['button_url']) ? $extra['button_url'] : $aboutButtonUrl;
        }
    }
}
$stmt->close();

$stmt = $mysqli->prepare('SELECT content FROM cms_sections WHERE slug = ?');
$slug = 'about_features';
$stmt->bind_param('s', $slug);
$stmt->execute();
$result = $stmt->get_result();
if ($row = $result->fetch_assoc()) {
    if (!empty($row['content'])) {
        $aboutFeatures = json_decode($row['content'], true) ?: [];
    }
}
$stmt->close();

// Default values if no data in database
if (!$aboutOverview) {
    $aboutOverview = 'Center for Research in Manufacturing System (CRiMS) merupakan pusat penelitian yang berfokus pada pengembangan sistem manufaktur terpadu dan berkelanjutan. Kami berkomitmen untuk memberikan solusi inovatif dalam bidang manufaktur melalui penelitian berkualitas tinggi dan kolaborasi dengan industri.';
}

if (empty($aboutFeatures)) {
    $aboutFeatures = [
        ['icon' => 'fas fa-flask', 'title' => 'Penelitian Unggulan', 'description' => 'Mengembangkan teknologi mutakhir di bidang manufaktur'],
        ['icon' => 'fas fa-handshake', 'title' => 'Kolaborasi', 'description' => 'Bekerja sama dengan industri dan akademisi'],
        ['icon' => 'fas fa-graduation-cap', 'title' => 'Pengembangan SDM', 'description' => 'Mencetak peneliti dan praktisi handal']
    ];
}

function achievementImageSrc(?string $path): string {
    if ($path) {
        return '/crims/' . ltrim($path, '/');
    }
    return '';
}

function partnerLogoSrc(?string $path): string {
    if ($path) {
        return '/crims/' . ltrim($path, '/');
    }
    return 'https://placehold.co/200x120?text=CRIMS';
}

function projectImageSrc(?string $path): string {
    if ($path) {
        return '/crims/' . ltrim($path, '/');
    }
    return 'https://images.unsplash.com/photo-1551288049-bebda4e38f71?ixlib=rb-1.2.1&auto=format&fit=crop&w=1950&q=80';
}

function newsImageSrc(?string $path): string {
    if ($path) {
        return '/crims/' . ltrim($path, '/');
    }
    return 'https://images.unsplash.com/photo-1521791136064-7986c2920216?ixlib=rb-1.2.1&auto=format&fit=crop&w=1950&q=80';
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>CRIMS - Center for Research in Manufacturing System</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="css/styles.css">
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
    
    <!-- Header & Navigasi -->
    <header class="header fade-in">
        <nav class="navbar">
            <div class="container">
                <div class="logo">
                    <h1>CRiMS</h1>
                    <span>Center for Research in Manufacturing System</span>
                </div>
                <ul class="nav-links" id="navLinks">
                    <li><a href="#beranda" class="active">Beranda</a></li>
                    <li class="dropdown">
                        <a href="#tentang">Tentang <i class="fas fa-chevron-down"></i></a>
                        <ul class="dropdown-menu">
                            <li><a href="research-fields.html">Research Fields</a></li>
                            <li><a href="recent-research.html">Recent Research Activities</a></li>
                            <li><a href="facilities-services.html">Facilities and Services</a></li>
                        </ul>
                    </li>
                    <li class="dropdown">
                        <a href="#proyek">Proyek <i class="fas fa-chevron-down"></i></a>
                        <ul class="dropdown-menu">
                            <li><a href="#research-topics">Availability Research Topics for Postgrad student</a></li>
                        </ul>
                    </li>
                    <li><a href="#berita">Berita</a></li>
                    <li class="dropdown">
                        <a href="#mitra">Mitra <i class="fas fa-chevron-down"></i></a>
                        <ul class="dropdown-menu">
                            <li><a href="collaboration.html">Collaboration</a></li>
                            <li><a href="team.php">Team</a></li>
                        </ul>
                    </li>
                    <li><a href="#prestasi">Prestasi</a></li>
                    <li><a href="#pengabdian">Pengabdian</a></li>
                    <li class="dropdown">
                        <a href="#student">Student <i class="fas fa-chevron-down"></i></a>
                        <ul class="dropdown-menu">
                            <li><a href="#skripsi">Skripsi</a></li>
                            <li><a href="#tesis">Tesis</a></li>
                            <li><a href="#disertasi">Disertasi</a></li>
                        </ul>
                    </li>
                    <li><a href="#hilirisasi">Hilirisasi</a></li>
                    <!-- Dashboard link disembunyikan untuk akses admin via URL langsung -->
                </ul>
                <div class="hamburger" id="hamburger">
                    <span></span>
                    <span></span>
                    <span></span>
                </div>
            </div>
        </nav>
    </header>

    

    <!-- Hero Section -->
    <section class="hero" id="beranda">
        <div class="container">
            <div class="hero-content">
                <h1>Mengembangkan Inovasi di Bidang Manufaktur</h1>
                <p>Berfokus pada penelitian dan pengembangan sistem manufaktur berkelanjutan dan cerdas untuk masa depan yang lebih baik</p>
                <div class="hero-buttons">
                    <a href="#proyek" class="btn btn-primary">Lihat Proyek Kami</a>
                    <a href="#tentang" class="btn btn-outline">Tentang Kami</a>
                </div>
            </div>
        </div>
    </section>

    <!-- Tentang Kami Section -->
    <section class="about" id="tentang">
        <div class="container">
            <h2 class="section-title">Tentang Kami</h2>
            <div class="about-content">
                <div class="about-text">
                    <p><?= htmlspecialchars($aboutOverview) ?></p>
                    <div class="about-features">
                        <?php foreach ($aboutFeatures as $feature): ?>
                        <?php if (!empty($feature['icon']) && !empty($feature['title']) && !empty($feature['description'])): ?>
                        <div class="feature">
                            <i class="<?= htmlspecialchars($feature['icon']) ?>"></i>
                            <h3><?= htmlspecialchars($feature['title']) ?></h3>
                            <p><?= htmlspecialchars($feature['description']) ?></p>
                        </div>
                        <?php endif; ?>
                        <?php endforeach; ?>
                    </div>
                    <a href="<?= htmlspecialchars($aboutButtonUrl) ?>" class="btn btn-primary"><?= htmlspecialchars($aboutButtonText) ?></a>
                </div>
                <div class="about-image">
                    <?php 
                    $imageSrc = $aboutImage;
                    if (!empty($aboutImage) && strpos($aboutImage, 'http') !== 0 && strpos($aboutImage, '/') !== 0) {
                        $imageSrc = '/crims/' . ltrim($aboutImage, '/');
                    } elseif (!empty($aboutImage) && strpos($aboutImage, '/') === 0 && strpos($aboutImage, '/crims/') !== 0) {
                        $imageSrc = '/crims' . $aboutImage;
                    }
                    ?>
                    <img src="<?= htmlspecialchars($imageSrc) ?>" alt="Tentang CRiMS">
                </div>
            </div>
        </div>
    </section>

    <!-- Proyek Unggulan -->
    <?php if (!empty($projectItems)): ?>
    <section class="featured-projects" id="proyek">
        <div class="container">
            <div class="section-header">
                <h2 class="section-title">Proyek Unggulan</h2>
                <a href="/proyek.html" class="btn-link">Lihat Semua Proyek <i class="fas fa-arrow-right"></i></a>
            </div>
            <div class="projects-slider-wrapper">
                <div class="projects-slider" id="projectsSlider">
                    <?php foreach ($projectItems as $project): ?>
                    <div class="project-card" onclick="window.location.href='/crims/project_detail.php?id=<?= $project['id'] ?>'" style="cursor: pointer;">
                        <div class="project-image">
                            <img src="<?= htmlspecialchars(projectImageSrc($project['image_url'])) ?>" alt="<?= htmlspecialchars($project['title']) ?>">
                        </div>
                        <div class="project-content">
                            <?php if (!empty($project['category'])): ?>
                                <span class="project-category"><?= htmlspecialchars($project['category']) ?></span>
                            <?php endif; ?>
                            <h3><?= htmlspecialchars($project['title']) ?></h3>
                            <?php if (!empty($project['summary'])): ?>
                                <div class="project-summary"><?= renderSafeHtml($project['summary']) ?></div>
                            <?php endif; ?>
                            <a href="/crims/project_detail.php?id=<?= $project['id'] ?>" class="btn-link" onclick="event.stopPropagation();">Selengkapnya <i class="fas fa-arrow-right"></i></a>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
                <button class="projects-slider-btn projects-slider-prev" id="projectsSliderPrev" aria-label="Previous project">
                    <i class="fas fa-arrow-left"></i>
                </button>
                <button class="projects-slider-btn projects-slider-next" id="projectsSliderNext" aria-label="Next project">
                    <i class="fas fa-arrow-right"></i>
                </button>
            </div>
        </div>
    </section>
    <?php endif; ?>

    <!-- Berita Terbaru -->
    <?php if (!empty($newsItems)): ?>
    <section class="latest-news" id="berita">
        <div class="container">
            <div class="section-header">
                <h2 class="section-title">Berita Terbaru</h2>
                <a href="/crims/berita.php" class="btn-link">Lihat Semua Berita <i class="fas fa-arrow-right"></i></a>
            </div>
            <div class="news-grid">
                <?php foreach ($newsItems as $index => $news): 
                    // Get uploader info from database
                    $uploaderRole = $news['role'] ?? 'admin';
                    $uploaderName = !empty($news['full_name']) ? $news['full_name'] : ($news['username'] ?? 'Admin');
                    // Capitalize first letter of role for display
                    $uploaderRoleDisplay = ucfirst($uploaderRole);
                    $newsDate = !empty($news['published_at']) ? $news['published_at'] : $news['created_at'];
                ?>
                    <a href="/crims/news_detail.php?id=<?= $news['id'] ?>" class="news-card-link <?= $index % 2 === 0 ? 'news-vertical' : 'news-horizontal' ?>">
                        <div class="news-card" data-index="<?= $index ?>" data-layout="<?= $index % 2 === 0 ? 'vertical' : 'horizontal' ?>">
                    <div class="news-image">
                                <img src="<?= htmlspecialchars(newsImageSrc($news['image_url'])) ?>" alt="<?= htmlspecialchars($news['title']) ?>" loading="lazy">
                    </div>
                    <div class="news-content">
                                <h3><?= htmlspecialchars($news['title']) ?></h3>
                                <?php if (!empty($news['summary'])): ?>
                                    <div class="news-summary"><?= renderSafeHtml($news['summary']) ?></div>
                                <?php endif; ?>
                                <div class="news-meta">
                                    <div class="news-meta-item" title="Tanggal Upload">
                                        <i class="fas fa-calendar-alt"></i>
                                        <span><?= date('d M Y', strtotime($newsDate)) ?></span>
                    </div>
                                    <div class="news-uploader" title="Upload oleh <?= htmlspecialchars($uploaderName) ?> (<?= htmlspecialchars($uploaderRoleDisplay) ?>)">
                                        <div class="news-uploader-icon <?= $uploaderRole ?>">
                                            <i class="fas fa-<?= $uploaderRole === 'admin' ? 'user-shield' : ($uploaderRole === 'mahasiswa' ? 'user-graduate' : 'chalkboard-teacher') ?>"></i>
                </div>
                                        <span><?= htmlspecialchars($uploaderRoleDisplay) ?></span>
                    </div>
                    </div>
                </div>
                            <div class="news-accent"></div>
                    </div>
                    </a>
                <?php endforeach; ?>
            </div>
        </div>
    </section>
    <?php endif; ?>

    <!-- Mitra -->
    <section class="partners" id="mitra">
        <div class="container">
            <h2 class="section-title">Mitra Kami</h2>
            <div class="partners-slider-wrapper">
                <div class="partners-slider">
                    <?php if (count($partnerItems) === 0): ?>
                    <div class="partner-logo">
                        <div class="partner-logo-image">
                                <img src="https://placehold.co/200x120?text=CRIMS" alt="Belum ada mitra">
                        </div>
                            <h4>Mitra akan segera ditampilkan</h4>
                        </div>
                    <?php else: ?>
                        <?php foreach ($partnerItems as $partner): ?>
                    <div class="partner-logo">
                        <div class="partner-logo-image">
                                    <img src="<?= htmlspecialchars(partnerLogoSrc($partner['logo_url'])) ?>" alt="<?= htmlspecialchars($partner['name']) ?>">
                        </div>
                                <h4><?= htmlspecialchars($partner['name']) ?></h4>
                    </div>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </section>

    <!-- Prestasi -->
    <?php if (!empty($achievementItems)): ?>
    <section class="achievements-modern" id="prestasi">
        <div class="container">
            <div class="achievements-modern-header">
                <h2 class="section-title">Prestasi & Penghargaan</h2>
                <span class="achievements-label">Our Achievements</span>
                <p class="section-subtitle">Pencapaian dan pengakuan yang telah diraih oleh CRIMS dalam berbagai bidang penelitian dan inovasi</p>
            </div>
            <div class="achievements-modern-grid">
                <?php foreach ($achievementItems as $index => $achievement): 
                    // Get uploader info from database
                    $uploaderRole = $achievement['role'] ?? 'admin';
                    $uploaderName = !empty($achievement['full_name']) ? $achievement['full_name'] : ($achievement['username'] ?? 'Admin');
                    // Capitalize first letter of role for display
                    $uploaderRoleDisplay = ucfirst($uploaderRole);
                ?>
                    <a href="/crims/achievement_detail.php?id=<?= $achievement['id'] ?>" class="achievement-modern-card-link">
                        <article class="achievement-modern-card" data-index="<?= $index ?>">
                            <?php if (!empty($achievement['image_url'])): ?>
                                <div class="achievement-modern-image">
                                    <img src="<?= htmlspecialchars(achievementImageSrc($achievement['image_url'])) ?>" alt="<?= htmlspecialchars($achievement['title']) ?>" loading="lazy">
                                    <div class="achievement-modern-image-overlay"></div>
                                </div>
                            <?php else: ?>
                                <div class="achievement-modern-image" style="display: flex; align-items: center; justify-content: center; background: linear-gradient(135deg, #1e5ba8 0%, #2980b9 100%);">
                                    <div class="achievement-modern-icon" style="width: 80px; height: 80px; background: rgba(255,255,255,0.2);">
                                        <i class="<?= htmlspecialchars($achievement['icon_class']) ?>" style="font-size: 2rem;"></i>
                                    </div>
                                </div>
                            <?php endif; ?>
                            <div class="achievement-modern-body">
                                <div class="achievement-modern-header">
                                    <div class="achievement-modern-icon">
                                        <i class="<?= htmlspecialchars($achievement['icon_class']) ?>"></i>
                                    </div>
                                    <div class="achievement-modern-content">
                                        <h3><?= htmlspecialchars($achievement['title']) ?></h3>
                                        <?php if (!empty($achievement['description'])): ?>
                                            <div class="achievement-description"><?= renderSafeHtml($achievement['description']) ?></div>
                                        <?php endif; ?>
                    </div>
                </div>
                                <div class="achievement-modern-meta">
                                    <div class="achievement-modern-meta-item" title="Tanggal Upload">
                                        <i class="fas fa-calendar-alt"></i>
                                        <span><?= date('d M Y', strtotime($achievement['created_at'])) ?></span>
                    </div>
                                    <div class="achievement-modern-uploader" title="Upload oleh <?= htmlspecialchars($uploaderName) ?> (<?= htmlspecialchars($uploaderRoleDisplay) ?>)">
                                        <div class="achievement-modern-uploader-icon <?= $uploaderRole ?>">
                                            <i class="fas fa-<?= $uploaderRole === 'admin' ? 'user-shield' : ($uploaderRole === 'mahasiswa' ? 'user-graduate' : 'chalkboard-teacher') ?>"></i>
                </div>
                                        <span><?= htmlspecialchars($uploaderRoleDisplay) ?></span>
                    </div>
                </div>
            </div>
                            <div class="achievement-modern-accent"></div>
                        </article>
                    </a>
                <?php endforeach; ?>
            </div>
        </div>
    </section>
    <?php endif; ?>

    <!-- Hilirisasi -->
    <?php if (!empty($hilirisasiItems)): ?>
    <section class="latest-news" id="hilirisasi" style="background: linear-gradient(180deg, #f8f9fa 0%, #ffffff 50%, #f8f9fa 100%);">
        <div class="container">
            <div class="section-header">
                <h2 class="section-title">Hilirisasi</h2>
                <a href="/crims/hilirisasi_all.php" class="btn-link">Lihat Semua Hilirisasi <i class="fas fa-arrow-right"></i></a>
            </div>
            <p class="section-subtitle" style="text-align: center; color: var(--gray); margin-top: 10px; max-width: 700px; margin-left: auto; margin-right: auto;">
                Produk dan teknologi hasil penelitian yang telah dihilirisasikan untuk kepentingan industri dan masyarakat
            </p>
            <div class="news-grid">
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
        </div>
    </section>
    <?php endif; ?>

    <!-- Footer -->
    <footer class="footer">
        <div class="container">
            <div class="footer-content">
                <div class="footer-section">
                    <div class="logo">
                        <h2>CRiMS</h2>
                        <p>Center for Research in Manufacturing System</p>
                    </div>
                    <p>Mengembangkan inovasi di bidang manufaktur untuk masa depan yang lebih baik</p>
                    <div class="social-links">
                        <a href="#"><i class="fab fa-facebook-f"></i></a>
                        <a href="#"><i class="fab fa-twitter"></i></a>
                        <a href="#"><i class="fab fa-linkedin-in"></i></a>
                        <a href="#"><i class="fab fa-youtube"></i></a>
                    </div>
                </div>
                <div class="footer-section">
                    <h3>Tautan Cepat</h3>
                    <ul>
                        <li><a href="#beranda">Beranda</a></li>
                        <li><a href="#tentang">Tentang Kami</a></li>
                        <li><a href="research-fields.html">Research Fields</a></li>
                        <li><a href="recent-research.html">Recent Research Activities</a></li>
                        <li><a href="facilities-services.html">Facilities & Services</a></li>
                        <li><a href="collaboration.html">Collaboration</a></li>
                            <li><a href="team.php">Team</a></li>
                        <li><a href="team.php">Team</a></li>
                        <li><a href="#proyek">Proyek</a></li>
                        <li><a href="#berita">Berita</a></li>
                        <li><a href="#mitra">Mitra</a></li>
                        <li><a href="#prestasi">Prestasi</a></li>
                        <li><a href="#hilirisasi">Hilirisasi</a></li>
                    </ul>
                </div>
                <div class="footer-section">
                    <h3>Kontak Kami</h3>
                    <p><i class="fas fa-map-marker-alt"></i> Jl. Ir. Sutami 36A, Surakarta, Indonesia</p>
                    <p><i class="fas fa-phone"></i> +62 271 1234567</p>
                    <p><i class="fas fa-envelope"></i> info@crims.uns.ac.id</p>
                </div>
            </div>
            <div class="footer-bottom">
                <p>&copy; 2025 CRiMS - Center for Research in Manufacturing System. Semua Hak Dilindungi.</p>
            </div>
        </div>
    </footer>

    <script src="js/script.js"></script>
</body>
</html>
</html>