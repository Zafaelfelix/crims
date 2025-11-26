<?php
require_once __DIR__ . '/config.php';

$teamMembers = [];
$teamQuery = $mysqli->query('SELECT * FROM team_structure ORDER BY sort_order ASC, name ASC');
if ($teamQuery) {
    $teamMembers = $teamQuery->fetch_all(MYSQLI_ASSOC);
    $teamQuery->free();
}

function teamPhotoSrc(?string $path): string {
    if ($path) {
        return '/crims/' . ltrim($path, '/');
    }
    return 'https://placehold.co/400x400?text=CRIMS';
}

function parseResearchInterests(?string $value): array {
    if (!$value) {
        return [];
    }
    $parts = array_map('trim', explode(',', $value));
    return array_values(array_filter($parts, static fn($item) => $item !== ''));
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Team - CRiMS</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="css/styles.css">
    <link rel="stylesheet" href="css/team.css">
</head>
<body>
    <!-- Header & Navigasi -->
    <header class="header">
        <nav class="navbar">
            <div class="container">
                <div class="logo">
                    <h1>CRiMS</h1>
                    <span>Center for Research in Manufacturing System</span>
                </div>
                <ul class="nav-links" id="navLinks">
                    <li><a href="index.php#beranda">Beranda</a></li>
                    <li class="dropdown">
                        <a href="index.php#tentang">Tentang <i class="fas fa-chevron-down"></i></a>
                        <ul class="dropdown-menu">
                            <li><a href="research-fields.html">Research Fields</a></li>
                            <li><a href="recent-research.html">Recent Research Activities</a></li>
                            <li><a href="facilities-services.html">Facilities and Services</a></li>
                        </ul>
                    </li>
                    <li class="dropdown">
                        <a href="index.php#proyek">Proyek <i class="fas fa-chevron-down"></i></a>
                        <ul class="dropdown-menu">
                            <li><a href="index.php#research-topics">Availability Research Topics for Postgrad student</a></li>
                        </ul>
                    </li>
                    <li><a href="index.php#berita">Berita</a></li>
                    <li class="dropdown">
                        <a href="index.php#mitra">Mitra <i class="fas fa-chevron-down"></i></a>
                        <ul class="dropdown-menu">
                            <li><a href="collaboration.html">Collaboration</a></li>
                            <li><a href="team.php" class="active">Team</a></li>
                        </ul>
                    </li>
                    <li><a href="index.php#prestasi">Prestasi</a></li>
                    <li><a href="index.php#hilirisasi">Hilirisasi</a></li>
                    <!-- Dashboard button dihilangkan -->
                </ul>
                <div class="hamburger" id="hamburger">
                    <span></span>
                    <span></span>
                    <span></span>
                </div>
            </div>
        </nav>
    </header>

    <!-- Team Hero Section -->
    <section class="team-hero">
        <div class="container">
            <div class="hero-content">
                <span class="eyebrow">Our Team</span>
                <h1>Tim Peneliti CRiMS</h1>
                <p>Para ahli multidisiplin yang berdedikasi untuk mengembangkan inovasi di bidang sistem manufaktur cerdas, rekayasa produksi, dan teknologi industri berkelanjutan.</p>
            </div>
        </div>
    </section>

    <!-- Team Members Section -->
    <section class="team-section" id="team">
        <div class="container">
            <div class="team-grid">
                <?php if (count($teamMembers) === 0): ?>
                    <p style="grid-column:1/-1;text-align:center;color:#6b7280;">Belum ada data tim. Silakan tambahkan melalui panel admin.</p>
                <?php else: ?>
                    <?php foreach ($teamMembers as $member): ?>
                        <div class="team-card">
                            <div class="team-image">
                                <img src="<?= htmlspecialchars(teamPhotoSrc($member['photo_url'] ?? null)) ?>" alt="<?= htmlspecialchars($member['name']) ?>">
                                <div class="team-overlay">
                                    <div class="team-social">
                                        <?php if (!empty($member['scopus_id'])): ?>
                                            <a href="https://www.scopus.com/authid/detail.uri?authorId=<?= urlencode($member['scopus_id']) ?>" target="_blank" title="Scopus Profile">
                                                <i class="fas fa-graduation-cap"></i>
                                            </a>
                                        <?php endif; ?>
                                        <?php if (!empty($member['email'])): ?>
                                            <a href="mailto:<?= htmlspecialchars($member['email']) ?>" title="Email">
                                                <i class="fas fa-envelope"></i>
                                            </a>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </div>
                            <div class="team-info">
                                <h3><?= htmlspecialchars($member['name']) ?></h3>
                                <p class="team-title"><?= htmlspecialchars($member['position_title']) ?></p>
                                <div class="team-details">
                                    <?php if (!empty($member['scopus_id'])): ?>
                                        <div class="detail-item">
                                            <i class="fas fa-id-badge"></i>
                                            <span><strong>Scopus ID:</strong> <?= htmlspecialchars($member['scopus_id']) ?></span>
                                        </div>
                                    <?php endif; ?>
                                    <?php if (!empty($member['email'])): ?>
                                        <div class="detail-item">
                                            <i class="fas fa-envelope"></i>
                                            <span><?= htmlspecialchars($member['email']) ?></span>
                                        </div>
                                    <?php endif; ?>
                                </div>
                                <?php $interests = parseResearchInterests($member['research_interests'] ?? null); ?>
                                <?php if (!empty($interests)): ?>
                                    <div class="research-interests">
                                        <h4>Research Interests:</h4>
                                        <div class="interest-tags">
                                            <?php foreach ($interests as $interest): ?>
                                                <span><?= htmlspecialchars($interest) ?></span>
                                            <?php endforeach; ?>
                                        </div>
                                    </div>
                                <?php endif; ?>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
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
                        <li><a href="index.php#beranda">Beranda</a></li>
                        <li><a href="index.php#tentang">Tentang Kami</a></li>
                        <li><a href="research-fields.html">Research Fields</a></li>
                        <li><a href="recent-research.html">Recent Research Activities</a></li>
                        <li><a href="facilities-services.html">Facilities & Services</a></li>
                        <li><a href="collaboration.html">Collaboration</a></li>
                            <li><a href="team.php">Team</a></li>
                        <li><a href="index.php#proyek">Proyek</a></li>
                        <li><a href="index.php#berita">Berita</a></li>
                        <li><a href="index.php#mitra">Mitra</a></li>
                        <li><a href="index.php#prestasi">Prestasi</a></li>
                        <li><a href="index.php#hilirisasi">Hilirisasi</a></li>
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

