<?php
if (!function_exists('renderAdminNav')) {
    function renderAdminNav($activePage = '') {
        ob_start();
        ?>
        <nav class="admin-nav">
            <a href="/crims/admin/dashboard.php" class="<?= $activePage === 'dashboard' ? 'active' : '' ?>">Dashboard</a>
            <a href="/crims/admin/team.php" class="<?= $activePage === 'team' ? 'active' : '' ?>">Struktur Tim Riset</a>
            <a href="/crims/admin/projects.php" class="<?= $activePage === 'projects' ? 'active' : '' ?>">Project</a>
            <a href="/crims/admin/news.php" class="<?= $activePage === 'news' ? 'active' : '' ?>">Berita</a>
            <a href="/crims/admin/partners.php" class="<?= $activePage === 'partners' ? 'active' : '' ?>">Mitra</a>
            <a href="/crims/admin/achievements.php" class="<?= $activePage === 'achievements' ? 'active' : '' ?>">Prestasi</a>
            <a href="#" class="disabled" title="Segera hadir">Tentang</a>
            <a href="#" class="disabled" title="Segera hadir">Hilirisasi</a>
        </nav>
        <?php
        return ob_get_clean();
    }
}
?>

