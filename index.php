<?php
require_once __DIR__ . '/config.php';

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
$newsQuery = $mysqli->query('SELECT id, title, summary, image_url, article_url, published_at, created_at FROM news_items ORDER BY sort_order ASC, published_at DESC, created_at DESC LIMIT 6');
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
    $achievementQuery = $mysqli->query('SELECT id, title, description, icon_class, image_url, created_at FROM achievement_items ORDER BY sort_order ASC, created_at DESC LIMIT 6');
} else {
    $achievementQuery = $mysqli->query('SELECT id, title, description, icon_class, NULL as image_url, created_at FROM achievement_items ORDER BY sort_order ASC, created_at DESC LIMIT 6');
}
if ($achievementQuery) {
    $achievementItems = $achievementQuery->fetch_all(MYSQLI_ASSOC);
    $achievementQuery->free();
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
if ($row = $result->fetch_assoc() && $row['content']) {
    $aboutFeatures = json_decode($row['content'], true) ?: [];
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
                    <div class="project-card">
                        <div class="project-image">
                            <img src="<?= htmlspecialchars(projectImageSrc($project['image_url'])) ?>" alt="<?= htmlspecialchars($project['title']) ?>">
                        </div>
                        <div class="project-content">
                            <?php if (!empty($project['category'])): ?>
                                <span class="project-category"><?= htmlspecialchars($project['category']) ?></span>
                            <?php endif; ?>
                            <h3><?= htmlspecialchars($project['title']) ?></h3>
                            <?php if (!empty($project['summary'])): ?>
                                <p><?= htmlspecialchars($project['summary']) ?></p>
                            <?php endif; ?>
                            <?php if (!empty($project['detail_url'])): ?>
                                <a href="<?= htmlspecialchars($project['detail_url']) ?>" class="btn-link" target="_blank">Selengkapnya <i class="fas fa-arrow-right"></i></a>
                            <?php else: ?>
                                <a href="#" class="btn-link">Selengkapnya <i class="fas fa-arrow-right"></i></a>
                            <?php endif; ?>
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
                <a href="/berita.html" class="btn-link">Lihat Semua Berita <i class="fas fa-arrow-right"></i></a>
            </div>
            <div class="news-grid">
                <?php foreach ($newsItems as $index => $news): 
                    $uploaderRole = 'admin';
                    $uploaderName = 'Admin';
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
                                    <p><?= htmlspecialchars($news['summary']) ?></p>
                                <?php endif; ?>
                                <div class="news-meta">
                                    <div class="news-meta-item" title="Tanggal Upload">
                                        <i class="fas fa-calendar-alt"></i>
                                        <span><?= date('d M Y', strtotime($newsDate)) ?></span>
                    </div>
                                    <div class="news-uploader" title="Upload oleh <?= htmlspecialchars($uploaderName) ?>">
                                        <div class="news-uploader-icon <?= $uploaderRole ?>">
                                            <i class="fas fa-<?= $uploaderRole === 'admin' ? 'user-shield' : ($uploaderRole === 'mahasiswa' ? 'user-graduate' : 'chalkboard-teacher') ?>"></i>
                </div>
                                        <span><?= htmlspecialchars($uploaderName) ?></span>
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
                    // Default role to admin (bisa diubah nanti jika ada kolom role di database)
                    $uploaderRole = 'admin';
                    $uploaderName = 'Admin';
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
                                            <p><?= nl2br(htmlspecialchars($achievement['description'])) ?></p>
                                        <?php endif; ?>
                    </div>
                </div>
                                <div class="achievement-modern-meta">
                                    <div class="achievement-modern-meta-item" title="Tanggal Upload">
                                        <i class="fas fa-calendar-alt"></i>
                                        <span><?= date('d M Y', strtotime($achievement['created_at'])) ?></span>
                    </div>
                                    <div class="achievement-modern-uploader" title="Upload oleh <?= htmlspecialchars($uploaderName) ?>">
                                        <div class="achievement-modern-uploader-icon <?= $uploaderRole ?>">
                                            <i class="fas fa-<?= $uploaderRole === 'admin' ? 'user-shield' : ($uploaderRole === 'mahasiswa' ? 'user-graduate' : 'chalkboard-teacher') ?>"></i>
                </div>
                                        <span><?= htmlspecialchars($uploaderName) ?></span>
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
    <section class="commercialization" id="hilirisasi">
        <div class="container">
            <h2 class="section-title">Hilirisasi</h2>
            <div class="commercialization-grid">
                <div class="commercialization-card">
                    <div class="commercialization-image">
                        <img src="https://images.unsplash.com/photo-1551434678-e076c223a692?ixlib=rb-1.2.1&auto=format&fit=crop&w=1950&q=80" alt="Hilirisasi 1">
                    </div>
                    <div class="commercialization-content">
                        <h3>Teknologi Robot Industri</h3>
                        <p>Solusi otomasi untuk meningkatkan efisiensi produksi di industri manufaktur</p>
                        <a href="/hilirisasi/robot-industri" class="btn-link">Selengkapnya <i class="fas fa-arrow-right"></i></a>
                    </div>
                </div>
                <div class="commercialization-card">
                    <div class="commercialization-image">
                        <img src="https://images.unsplash.com/photo-1552664730-d307ca884978?ixlib=rb-1.2.1&auto=format&fit=crop&w=1950&q=80" alt="Hilirisasi 2">
                    </div>
                    <div class="commercialization-content">
                        <h3>Sistem Manajemen Kualitas</h3>
                        <p>Platform digital untuk memantau dan meningkatkan kualitas produk</p>
                        <a href="/hilirisasi/sistem-kualitas" class="btn-link">Selengkapnya <i class="fas fa-arrow-right"></i></a>
                    </div>
                </div>
            </div>
        </div>
    </section>

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