<?php
session_start();
require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/admin_layout.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: /crims/login/');
    exit;
}
$activePage = 'dashboard';

function countTable($table) {
    global $mysqli;
    $result = $mysqli->query("SELECT COUNT(*) AS total FROM {$table}");
    $row = $result->fetch_assoc();
    return (int) $row['total'];
}

$stats = [
    'projects' => countTable('project_items'),
    'news' => countTable('news_items'),
    'partners' => countTable('partner_items'),
    'team' => countTable('team_structure'),
    'achievements' => countTable('achievement_items'),
];

// Get featured projects count
$featuredProjects = 0;
$result = $mysqli->query("SELECT COUNT(*) AS total FROM project_items WHERE is_featured = 1");
if ($result) {
    $row = $result->fetch_assoc();
    $featuredProjects = (int) $row['total'];
    $result->free();
}

// Get latest activity
$latestNews = [];
$result = $mysqli->query("SELECT title, created_at FROM news_items ORDER BY created_at DESC LIMIT 5");
if ($result) {
    $latestNews = $result->fetch_all(MYSQLI_ASSOC);
    $result->free();
}

ob_start();
?>
<style>
    .dashboard-welcome {
        margin-bottom: 30px;
        padding-bottom: 20px;
        border-bottom: 1px solid #c3c4c7;
    }
    
    .dashboard-welcome h2 {
        font-size: 23px;
        font-weight: 400;
        color: #1d2327;
        margin-bottom: 8px;
    }
    
    .dashboard-welcome p {
        color: #646970;
        font-size: 14px;
    }
    
    .dashboard-cards {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(240px, 1fr));
        gap: 20px;
        margin-bottom: 30px;
    }
    
    .dashboard-card {
        background: #fff;
        border: 1px solid #c3c4c7;
        border-radius: 4px;
        padding: 20px;
        box-shadow: 0 1px 1px rgba(0,0,0,.04);
    }
    
    .dashboard-card h3 {
        font-size: 14px;
        font-weight: 400;
        color: #646970;
        margin-bottom: 12px;
        text-transform: uppercase;
        letter-spacing: 0.5px;
    }
    
    .dashboard-card .number {
        font-size: 32px;
        font-weight: 400;
        color: #2271b1;
        line-height: 1;
    }
    
    .dashboard-card:hover {
        border-color: #2271b1;
        box-shadow: 0 1px 3px rgba(0,0,0,.13);
    }
    
    
    .dashboard-section {
        background: #fff;
        border: 1px solid #c3c4c7;
        border-radius: 4px;
        padding: 20px;
        box-shadow: 0 1px 1px rgba(0,0,0,.04);
    }
    
    .dashboard-section h3 {
        font-size: 16px;
        font-weight: 400;
        color: #1d2327;
        margin-bottom: 16px;
        padding-bottom: 12px;
        border-bottom: 1px solid #c3c4c7;
    }
    
    .dashboard-list {
        list-style: none;
        padding: 0;
        margin: 0;
    }
    
    .dashboard-list li {
        padding: 12px 0;
        border-bottom: 1px solid #f0f0f1;
        font-size: 14px;
    }
    
    .dashboard-list li:last-child {
        border-bottom: none;
    }
    
    .dashboard-list a {
        color: #2271b1;
        text-decoration: none;
    }
    
    .dashboard-list a:hover {
        text-decoration: underline;
    }
    
    .dashboard-list .date {
        color: #646970;
        font-size: 12px;
        margin-top: 4px;
    }
    
</style>

<div class="admin-page-header">
    <h1>Dashboard</h1>
</div>

<div class="dashboard-welcome">
    <h2>Selamat datang, <?= htmlspecialchars($_SESSION['username']) ?></h2>
    <p>Berikut ringkasan informasi sistem dan aktivitas terbaru.</p>
</div>

<div class="dashboard-cards">
    <div class="dashboard-card">
        <h3>Total Projects</h3>
        <div class="number"><?= $stats['projects'] ?></div>
        <?php if ($featuredProjects > 0): ?>
            <div style="font-size: 12px; color: #646970; margin-top: 8px;">
                <?= $featuredProjects ?> featured
            </div>
        <?php endif; ?>
    </div>
    <div class="dashboard-card">
        <h3>Total News</h3>
        <div class="number"><?= $stats['news'] ?></div>
    </div>
    <div class="dashboard-card">
        <h3>Total Partners</h3>
        <div class="number"><?= $stats['partners'] ?></div>
    </div>
    <div class="dashboard-card">
        <h3>Team Members</h3>
        <div class="number"><?= $stats['team'] ?></div>
    </div>
    <div class="dashboard-card">
        <h3>Prestasi</h3>
        <div class="number"><?= $stats['achievements'] ?></div>
    </div>
</div>

<?php if (!empty($latestNews)): ?>
<div class="dashboard-section" style="margin-top: 20px;">
    <h3>Aktivitas Terbaru</h3>
    <ul class="dashboard-list">
        <?php foreach ($latestNews as $news): ?>
            <li>
                <a href="/crims/admin/news.php"><?= htmlspecialchars($news['title']) ?></a>
                <div class="date"><?= date('d M Y', strtotime($news['created_at'])) ?></div>
            </li>
        <?php endforeach; ?>
    </ul>
</div>
<?php endif; ?>
<?php
$content = ob_get_clean();
echo renderAdminLayout($activePage, 'Dashboard', $content);
?>

