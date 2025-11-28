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
        margin-bottom: 40px;
        padding: 35px;
        background: linear-gradient(135deg, #1e3c72 0%, #2a5298 50%, #1e3c72 100%);
        background-size: 200% 200%;
        border-radius: 20px;
        color: #fff;
        box-shadow: 0 12px 40px rgba(30, 60, 114, 0.4), 0 4px 12px rgba(30, 60, 114, 0.3);
        position: relative;
        overflow: hidden;
        animation: gradientShift 8s ease infinite;
    }
    
    @keyframes gradientShift {
        0%, 100% { background-position: 0% 50%; }
        50% { background-position: 100% 50%; }
    }
    
    .dashboard-welcome::before {
        content: '';
        position: absolute;
        top: -50%;
        right: -50%;
        width: 200%;
        height: 200%;
        background: radial-gradient(circle, rgba(255,255,255,0.15) 0%, transparent 70%);
        animation: welcomeShine 10s ease-in-out infinite;
    }
    
    .dashboard-welcome::after {
        content: '';
        position: absolute;
        bottom: 0;
        left: 0;
        right: 0;
        height: 4px;
        background: linear-gradient(90deg, transparent, rgba(255,255,255,0.5), transparent);
        animation: shimmerLine 3s ease-in-out infinite;
    }
    
    @keyframes welcomeShine {
        0%, 100% { transform: translate(0, 0) rotate(0deg); }
        50% { transform: translate(-20%, -20%) rotate(180deg); }
    }
    
    @keyframes shimmerLine {
        0%, 100% { opacity: 0.3; }
        50% { opacity: 1; }
    }
    
    .dashboard-welcome h2 {
        font-size: 28px;
        font-weight: 700;
        color: #fff;
        margin-bottom: 10px;
        position: relative;
        z-index: 1;
        text-shadow: 0 2px 10px rgba(0,0,0,0.2);
    }
    
    .dashboard-welcome p {
        color: rgba(255, 255, 255, 0.95);
        font-size: 16px;
        position: relative;
        z-index: 1;
    }
    
    .dashboard-cards {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));
        gap: 24px;
        margin-bottom: 40px;
    }
    
    .dashboard-card {
        background: linear-gradient(135deg, #ffffff 0%, #f8f9fa 100%);
        border: 2px solid transparent;
        border-radius: 16px;
        padding: 28px;
        box-shadow: 0 4px 20px rgba(0,0,0,.08), 0 2px 8px rgba(0,0,0,.04);
        transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1);
        position: relative;
        overflow: hidden;
        cursor: pointer;
    }
    
    .dashboard-card::before {
        content: '';
        position: absolute;
        top: 0;
        left: 0;
        right: 0;
        height: 4px;
        background: linear-gradient(90deg, #667eea 0%, #764ba2 50%, #667eea 100%);
        background-size: 200% 100%;
        opacity: 0;
        transition: all 0.4s ease;
        animation: shimmer 3s infinite;
    }
    
    @keyframes shimmer {
        0% { background-position: -200% 0; }
        100% { background-position: 200% 0; }
    }
    
    .dashboard-card:hover::before {
        opacity: 1;
    }
    
    .dashboard-card::after {
        content: '';
        position: absolute;
        top: -50%;
        right: -50%;
        width: 200%;
        height: 200%;
        background: radial-gradient(circle, rgba(102, 126, 234, 0.1) 0%, transparent 70%);
        opacity: 0;
        transition: opacity 0.4s ease;
    }
    
    .dashboard-card:hover::after {
        opacity: 1;
    }
    
    .dashboard-card:hover {
        transform: translateY(-8px) scale(1.02);
        box-shadow: 0 12px 40px rgba(102, 126, 234, 0.25), 0 4px 16px rgba(0,0,0,.1);
        border-color: rgba(102, 126, 234, 0.3);
    }
    
    .dashboard-card-icon {
        width: 56px;
        height: 56px;
        border-radius: 14px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 24px;
        margin-bottom: 16px;
        position: relative;
        z-index: 1;
        transition: all 0.4s ease;
    }
    
    .dashboard-card:hover .dashboard-card-icon {
        transform: scale(1.1) rotate(5deg);
    }
    
    .dashboard-card.projects .dashboard-card-icon {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        color: #fff;
        box-shadow: 0 8px 20px rgba(102, 126, 234, 0.4);
    }
    
    .dashboard-card.news .dashboard-card-icon {
        background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%);
        color: #fff;
        box-shadow: 0 8px 20px rgba(245, 87, 108, 0.4);
    }
    
    .dashboard-card.partners .dashboard-card-icon {
        background: linear-gradient(135deg, #4facfe 0%, #00f2fe 100%);
        color: #fff;
        box-shadow: 0 8px 20px rgba(79, 172, 254, 0.4);
    }
    
    .dashboard-card.team .dashboard-card-icon {
        background: linear-gradient(135deg, #43e97b 0%, #38f9d7 100%);
        color: #fff;
        box-shadow: 0 8px 20px rgba(67, 233, 123, 0.4);
    }
    
    .dashboard-card.achievements .dashboard-card-icon {
        background: linear-gradient(135deg, #fa709a 0%, #fee140 100%);
        color: #fff;
        box-shadow: 0 8px 20px rgba(250, 112, 154, 0.4);
    }
    
    .dashboard-card h3 {
        font-size: 13px;
        font-weight: 600;
        color: #64748b;
        margin-bottom: 12px;
        text-transform: uppercase;
        letter-spacing: 1px;
        position: relative;
        z-index: 1;
        transition: color 0.3s ease;
    }
    
    .dashboard-card:hover h3 {
        color: #1e293b;
    }
    
    .dashboard-card .number {
        font-size: 42px;
        font-weight: 700;
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        -webkit-background-clip: text;
        -webkit-text-fill-color: transparent;
        background-clip: text;
        line-height: 1;
        position: relative;
        z-index: 1;
        transition: all 0.4s ease;
    }
    
    .dashboard-card.projects .number {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        -webkit-background-clip: text;
        -webkit-text-fill-color: transparent;
    }
    
    .dashboard-card.news .number {
        background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%);
        -webkit-background-clip: text;
        -webkit-text-fill-color: transparent;
    }
    
    .dashboard-card.partners .number {
        background: linear-gradient(135deg, #4facfe 0%, #00f2fe 100%);
        -webkit-background-clip: text;
        -webkit-text-fill-color: transparent;
    }
    
    .dashboard-card.team .number {
        background: linear-gradient(135deg, #43e97b 0%, #38f9d7 100%);
        -webkit-background-clip: text;
        -webkit-text-fill-color: transparent;
    }
    
    .dashboard-card.achievements .number {
        background: linear-gradient(135deg, #fa709a 0%, #fee140 100%);
        -webkit-background-clip: text;
        -webkit-text-fill-color: transparent;
    }
    
    .dashboard-card:hover .number {
        transform: scale(1.1);
    }
    
    .dashboard-card .subtitle {
        font-size: 12px;
        color: #94a3b8;
        margin-top: 10px;
        position: relative;
        z-index: 1;
        font-weight: 500;
    }
    
    .dashboard-section {
        background: linear-gradient(135deg, #ffffff 0%, #f8f9fa 100%);
        border: 2px solid transparent;
        border-radius: 20px;
        padding: 32px;
        box-shadow: 0 8px 30px rgba(0,0,0,.08), 0 4px 12px rgba(0,0,0,.04);
        transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1);
        position: relative;
        overflow: hidden;
    }
    
    .dashboard-section::before {
        content: '';
        position: absolute;
        top: 0;
        left: 0;
        right: 0;
        height: 4px;
        background: linear-gradient(90deg, #667eea 0%, #764ba2 50%, #667eea 100%);
        background-size: 200% 100%;
        opacity: 0;
        transition: opacity 0.4s ease;
    }
    
    .dashboard-section:hover {
        transform: translateY(-4px);
        box-shadow: 0 12px 40px rgba(102, 126, 234, 0.15), 0 6px 16px rgba(0,0,0,.08);
        border-color: rgba(102, 126, 234, 0.2);
    }
    
    .dashboard-section:hover::before {
        opacity: 1;
        animation: shimmer 3s infinite;
    }
    
    .dashboard-section h3 {
        font-size: 20px;
        font-weight: 700;
        color: #1e293b;
        margin-bottom: 24px;
        padding-bottom: 16px;
        border-bottom: 2px solid #e2e8f0;
        position: relative;
        display: flex;
        align-items: center;
        gap: 12px;
    }
    
    .dashboard-section h3::before {
        content: '';
        width: 4px;
        height: 24px;
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        border-radius: 2px;
    }
    
    .dashboard-list {
        list-style: none;
        padding: 0;
        margin: 0;
    }
    
    .dashboard-list li {
        padding: 16px 20px;
        border-bottom: 1px solid #f1f5f9;
        font-size: 14px;
        transition: all 0.3s ease;
        border-radius: 12px;
        margin-bottom: 8px;
        background: #fff;
        border: 1px solid #e2e8f0;
    }
    
    .dashboard-list li:last-child {
        border-bottom: none;
        margin-bottom: 0;
    }
    
    .dashboard-list li:hover {
        background: linear-gradient(135deg, #f8fafc 0%, #f1f5f9 100%);
        transform: translateX(8px);
        box-shadow: 0 4px 12px rgba(102, 126, 234, 0.1);
        border-color: rgba(102, 126, 234, 0.2);
    }
    
    .dashboard-list a {
        color: #1e293b;
        text-decoration: none;
        font-weight: 600;
        display: block;
        transition: color 0.3s ease;
    }
    
    .dashboard-list a:hover {
        color: #667eea;
    }
    
    .dashboard-list .date {
        color: #64748b;
        font-size: 12px;
        margin-top: 6px;
        display: flex;
        align-items: center;
        gap: 6px;
        font-weight: 500;
    }
    
    .dashboard-list .date::before {
        content: '\f073';
        font-family: 'Font Awesome 6 Free';
        font-weight: 900;
        color: #94a3b8;
    }
    
    @media (max-width: 768px) {
        .dashboard-cards {
            grid-template-columns: 1fr;
        }
        
        .dashboard-welcome {
            padding: 24px;
        }
        
        .dashboard-welcome h2 {
            font-size: 24px;
        }
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
    <div class="dashboard-card projects">
        <div class="dashboard-card-icon">
            <i class="fas fa-project-diagram"></i>
        </div>
        <h3>Total Projects</h3>
        <div class="number"><?= $stats['projects'] ?></div>
        <?php if ($featuredProjects > 0): ?>
            <div class="subtitle">
                <?= $featuredProjects ?> featured
            </div>
        <?php endif; ?>
    </div>
    <div class="dashboard-card news">
        <div class="dashboard-card-icon">
            <i class="fas fa-newspaper"></i>
        </div>
        <h3>Total News</h3>
        <div class="number"><?= $stats['news'] ?></div>
    </div>
    <div class="dashboard-card partners">
        <div class="dashboard-card-icon">
            <i class="fas fa-handshake"></i>
        </div>
        <h3>Total Partners</h3>
        <div class="number"><?= $stats['partners'] ?></div>
    </div>
    <div class="dashboard-card team">
        <div class="dashboard-card-icon">
            <i class="fas fa-users"></i>
        </div>
        <h3>Team Members</h3>
        <div class="number"><?= $stats['team'] ?></div>
    </div>
    <div class="dashboard-card achievements">
        <div class="dashboard-card-icon">
            <i class="fas fa-trophy"></i>
        </div>
        <h3>Prestasi</h3>
        <div class="number"><?= $stats['achievements'] ?></div>
    </div>
</div>

<?php if (!empty($latestNews)): ?>
<div class="dashboard-section" style="margin-top: 20px;">
    <h3>
        <i class="fas fa-clock" style="font-size: 18px; color: #667eea;"></i>
        Aktivitas Terbaru
    </h3>
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

