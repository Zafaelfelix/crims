<?php
session_start();
require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/mahasiswa_layout.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: /crims/login/');
    exit;
}

// Hanya mahasiswa yang bisa akses
if (($_SESSION['role'] ?? '') !== 'mahasiswa') {
    header('Location: /crims/login/');
    exit;
}

$activePage = 'dashboard';
$userId = $_SESSION['user_id'];

// Get statistics - hanya data yang dibuat oleh mahasiswa ini
function countUserTable($table, $userId) {
    global $mysqli;
    $allowedTables = ['project_items', 'news_items', 'achievement_items'];
    if (!in_array($table, $allowedTables)) {
        return 0;
    }
    $stmt = $mysqli->prepare("SELECT COUNT(*) AS total FROM {$table} WHERE created_by = ?");
    $stmt->bind_param('i', $userId);
    $stmt->execute();
    $result = $stmt->get_result();
    $row = $result->fetch_assoc();
    $stmt->close();
    return (int) ($row['total'] ?? 0);
}

$stats = [
    'projects' => countUserTable('project_items', $userId),
    'news' => countUserTable('news_items', $userId),
    'achievements' => countUserTable('achievement_items', $userId),
];

// Get latest activity - hanya yang dibuat oleh mahasiswa ini
$latestNews = [];
$stmt = $mysqli->prepare("SELECT title, created_at FROM news_items WHERE created_by = ? ORDER BY created_at DESC LIMIT 5");
$stmt->bind_param('i', $userId);
$stmt->execute();
$result = $stmt->get_result();
if ($result) {
    $latestNews = $result->fetch_all(MYSQLI_ASSOC);
    $result->free();
}
$stmt->close();

$latestProjects = [];
$stmt = $mysqli->prepare("SELECT title, created_at FROM project_items WHERE created_by = ? ORDER BY created_at DESC LIMIT 5");
$stmt->bind_param('i', $userId);
$stmt->execute();
$result = $stmt->get_result();
if ($result) {
    $latestProjects = $result->fetch_all(MYSQLI_ASSOC);
    $result->free();
}
$stmt->close();

$fullName = $_SESSION['full_name'] ?? $_SESSION['username'];

ob_start();
?>
<style>
    .dashboard-welcome {
        background: linear-gradient(135deg, #f59e0b 0%, #d97706 100%);
        border-radius: 16px;
        padding: 32px;
        margin-bottom: 32px;
        color: #fff;
        box-shadow: 0 8px 24px rgba(245, 158, 11, 0.25), 0 4px 8px rgba(245, 158, 11, 0.15);
        position: relative;
        overflow: hidden;
    }
    
    .dashboard-welcome::before {
        content: '';
        position: absolute;
        top: -50%;
        right: -50%;
        width: 200%;
        height: 200%;
        background: radial-gradient(circle, rgba(255,255,255,.1) 0%, transparent 70%);
        animation: welcomeShine 8s ease-in-out infinite;
    }
    
    @keyframes welcomeShine {
        0%, 100% { transform: translate(-50%, -50%) rotate(0deg); }
        50% { transform: translate(-50%, -50%) rotate(180deg); }
    }
    
    .dashboard-welcome h2 {
        font-size: 28px;
        font-weight: 700;
        color: #fff;
        margin-bottom: 8px;
        position: relative;
        z-index: 1;
        font-family: 'Poppins', sans-serif;
    }
    
    .dashboard-welcome p {
        color: rgba(255,255,255,.9);
        font-size: 15px;
        position: relative;
        z-index: 1;
        font-weight: 500;
    }
    
    .dashboard-cards {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(260px, 1fr));
        gap: 24px;
        margin-bottom: 32px;
    }
    
    .dashboard-card {
        background: linear-gradient(180deg, #ffffff 0%, #fafbfc 100%);
        border: 2px solid #e8e8e8;
        border-radius: 16px;
        padding: 28px;
        box-shadow: 0 4px 12px rgba(0,0,0,.08), 0 2px 4px rgba(0,0,0,.04);
        transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1);
        position: relative;
        overflow: hidden;
    }
    
    .dashboard-card::before {
        content: '';
        position: absolute;
        top: 0;
        left: 0;
        right: 0;
        height: 4px;
        background: linear-gradient(90deg, #f59e0b 0%, #d97706 100%);
        transform: scaleX(0);
        transition: transform 0.4s ease;
    }
    
    .dashboard-card:hover::before {
        transform: scaleX(1);
    }
    
    .dashboard-card h3 {
        font-size: 13px;
        font-weight: 600;
        color: #646970;
        margin-bottom: 16px;
        text-transform: uppercase;
        letter-spacing: 1px;
        font-family: 'Poppins', sans-serif;
        display: flex;
        align-items: center;
        gap: 8px;
    }
    
    .dashboard-card h3 i {
        font-size: 16px;
        color: #f59e0b;
    }
    
    .dashboard-card .number {
        font-size: 42px;
        font-weight: 700;
        color: #f59e0b;
        line-height: 1;
        font-family: 'Poppins', sans-serif;
        margin-bottom: 8px;
    }
    
    .dashboard-card:hover {
        border-color: #f59e0b;
        box-shadow: 0 12px 32px rgba(245, 158, 11, 0.2), 0 6px 16px rgba(245, 158, 11, 0.15);
        transform: translateY(-6px);
    }
    
    .dashboard-section {
        background: linear-gradient(180deg, #ffffff 0%, #fafbfc 100%);
        border: 2px solid #e8e8e8;
        border-radius: 16px;
        padding: 28px;
        box-shadow: 0 4px 12px rgba(0,0,0,.08), 0 2px 4px rgba(0,0,0,.04);
        margin-bottom: 24px;
        transition: all 0.3s ease;
    }
    
    .dashboard-section:hover {
        border-color: #f59e0b;
        box-shadow: 0 8px 24px rgba(245, 158, 11, 0.15), 0 4px 8px rgba(245, 158, 11, 0.1);
    }
    
    .dashboard-section h3 {
        font-size: 18px;
        font-weight: 700;
        color: #1d2327;
        margin-bottom: 20px;
        padding-bottom: 16px;
        border-bottom: 2px solid #f0f0f1;
        font-family: 'Poppins', sans-serif;
        display: flex;
        align-items: center;
        gap: 10px;
    }
    
    .dashboard-section h3 i {
        color: #f59e0b;
        font-size: 20px;
    }
    
    .dashboard-list {
        list-style: none;
        padding: 0;
        margin: 0;
    }
    
    .dashboard-list li {
        padding: 16px 0;
        border-bottom: 1px solid #f0f0f1;
        font-size: 14px;
        transition: all 0.2s ease;
    }
    
    .dashboard-list li:hover {
        padding-left: 8px;
        background: linear-gradient(90deg, rgba(245, 158, 11, 0.05) 0%, transparent 100%);
    }
    
    .dashboard-list li:last-child {
        border-bottom: none;
    }
    
    .dashboard-list a {
        color: #f59e0b;
        text-decoration: none;
        font-weight: 600;
        font-size: 15px;
        transition: all 0.2s ease;
        display: block;
    }
    
    .dashboard-list a:hover {
        color: #d97706;
        text-decoration: none;
    }
    
    .dashboard-list .date {
        color: #646970;
        font-size: 12px;
        margin-top: 6px;
        font-weight: 500;
    }
    
    .empty-state {
        text-align: center;
        padding: 60px 20px;
        color: #646970;
    }
    
    .empty-state i {
        font-size: 64px;
        color: #c3c4c7;
        margin-bottom: 20px;
        opacity: 0.6;
    }
    
    .empty-state p {
        font-size: 15px;
        font-weight: 500;
        color: #8c8f94;
    }
</style>

<div class="admin-page-header">
    <h1>Dashboard</h1>
</div>

<div class="dashboard-welcome">
    <h2>Selamat datang, <?= htmlspecialchars($fullName) ?></h2>
    <p>Berikut ringkasan aktivitas dan konten yang telah Anda buat.</p>
</div>

<div class="dashboard-cards">
    <div class="dashboard-card">
        <h3><i class="fas fa-project-diagram"></i> Total Proyek</h3>
        <div class="number"><?= $stats['projects'] ?></div>
    </div>
    <div class="dashboard-card">
        <h3><i class="fas fa-newspaper"></i> Total Berita</h3>
        <div class="number"><?= $stats['news'] ?></div>
    </div>
    <div class="dashboard-card">
        <h3><i class="fas fa-trophy"></i> Total Prestasi</h3>
        <div class="number"><?= $stats['achievements'] ?></div>
    </div>
</div>

<div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(300px, 1fr)); gap: 20px;">
    <?php if (!empty($latestNews)): ?>
    <div class="dashboard-section">
        <h3><i class="fas fa-newspaper"></i> Berita Terbaru</h3>
        <ul class="dashboard-list">
            <?php foreach ($latestNews as $news): ?>
                <li>
                    <a href="/crims/mahasiswa/news.php"><?= htmlspecialchars($news['title']) ?></a>
                    <div class="date"><i class="far fa-calendar"></i> <?= date('d M Y', strtotime($news['created_at'])) ?></div>
                </li>
            <?php endforeach; ?>
        </ul>
    </div>
    <?php else: ?>
    <div class="dashboard-section">
        <h3><i class="fas fa-newspaper"></i> Berita Terbaru</h3>
        <div class="empty-state">
            <i class="fas fa-newspaper"></i>
            <p>Belum ada berita yang dibuat</p>
        </div>
    </div>
    <?php endif; ?>
    
    <?php if (!empty($latestProjects)): ?>
    <div class="dashboard-section">
        <h3><i class="fas fa-project-diagram"></i> Proyek Terbaru</h3>
        <ul class="dashboard-list">
            <?php foreach ($latestProjects as $project): ?>
                <li>
                    <a href="/crims/mahasiswa/projects.php"><?= htmlspecialchars($project['title']) ?></a>
                    <div class="date"><i class="far fa-calendar"></i> <?= date('d M Y', strtotime($project['created_at'])) ?></div>
                </li>
            <?php endforeach; ?>
        </ul>
    </div>
    <?php else: ?>
    <div class="dashboard-section">
        <h3><i class="fas fa-project-diagram"></i> Proyek Terbaru</h3>
        <div class="empty-state">
            <i class="fas fa-project-diagram"></i>
            <p>Belum ada proyek yang dibuat</p>
        </div>
    </div>
    <?php endif; ?>
</div>

<?php
$content = ob_get_clean();
renderMahasiswaLayout($activePage, 'Dashboard', $content);
?>

