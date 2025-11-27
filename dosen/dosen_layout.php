<?php
if (!function_exists('renderDosenLayout')) {
    function renderDosenLayout($activePage = 'dashboard', $pageTitle = 'Dosen Panel', $content = '') {
        // Warna tema dosen: Hijau (#10b981, #059669)
        $primaryColor = '#10b981';
        $primaryDark = '#059669';
        $primaryLight = '#34d399';
        ?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($pageTitle) ?> - Dashboard Dosen</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700&family=Plus+Jakarta+Sans:wght@400;500;600;700&display=swap" rel="stylesheet">
    <!-- Bootstrap 4 CSS (required for Summernote BS4) -->
    <?php if (strpos($pageTitle, 'Proyek') !== false || strpos($pageTitle, 'Prestasi') !== false || strpos($pageTitle, 'Achievements') !== false || strpos($content, 'id="summary"') !== false || strpos($content, 'id="description"') !== false): ?>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/css/bootstrap.min.css">
    <!-- Summernote CSS -->
    <link href="https://cdn.jsdelivr.net/npm/summernote@0.8.18/dist/summernote-bs4.min.css" rel="stylesheet">
    <?php endif; ?>
    <!-- jQuery (required for Summernote) -->
    <script src="https://code.jquery.com/jquery-3.5.1.min.js"></script>
    <style>
        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
        }
        
        body {
            font-family: 'Plus Jakarta Sans', 'Poppins', -apple-system, BlinkMacSystemFont, 'Segoe UI', sans-serif;
            background: #ffffff;
            color: #1d2327;
            line-height: 1.6;
            font-weight: 400;
            -webkit-font-smoothing: antialiased;
            -moz-osx-font-smoothing: grayscale;
        }
        
        .admin-wrapper {
            display: flex;
            min-height: 100vh;
        }
        
        /* Sidebar - Dosen Theme (Hijau) */
        .admin-sidebar {
            width: 200px;
            background: linear-gradient(180deg, #1d2327 0%, #23282d 100%);
            color: #f0f0f1;
            position: fixed;
            left: 0;
            top: 0;
            bottom: 0;
            overflow-y: auto;
            z-index: 1000;
            transition: transform 0.3s ease;
            box-shadow: 4px 0 16px rgba(0,0,0,.2), 2px 0 8px rgba(0,0,0,.1);
        }
        
        .admin-sidebar-header {
            padding: 24px 20px;
            border-bottom: 1px solid rgba(255,255,255,.08);
            background: linear-gradient(135deg, #23282d 0%, #2c3338 100%);
            display: flex;
            align-items: center;
            gap: 12px;
            box-shadow: 0 2px 8px rgba(0,0,0,.2);
        }
        
        .admin-sidebar-logo {
            width: 44px;
            height: 44px;
            background: linear-gradient(135deg, <?= $primaryColor ?> 0%, <?= $primaryDark ?> 100%);
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            flex-shrink: 0;
            box-shadow: 0 4px 12px rgba(16, 185, 129, 0.4), 0 2px 4px rgba(16, 185, 129, 0.3), inset 0 1px 0 rgba(255,255,255,.3);
            position: relative;
            overflow: hidden;
        }
        
        .admin-sidebar-logo::before {
            content: '';
            position: absolute;
            top: -50%;
            left: -50%;
            width: 200%;
            height: 200%;
            background: radial-gradient(circle, rgba(255,255,255,.3) 0%, transparent 70%);
            animation: logoShine 3s ease-in-out infinite;
        }
        
        @keyframes logoShine {
            0%, 100% { transform: translate(-50%, -50%) rotate(0deg); }
            50% { transform: translate(-50%, -50%) rotate(180deg); }
        }
        
        .admin-sidebar-logo i {
            font-size: 22px;
            color: #fff;
            position: relative;
            z-index: 1;
            text-shadow: 0 1px 2px rgba(0,0,0,.2);
        }
        
        .admin-sidebar-header h1 {
            font-size: 18px;
            font-weight: 700;
            color: #f0f0f1;
            margin: 0;
            line-height: 1.2;
            font-family: 'Poppins', 'Plus Jakarta Sans', sans-serif;
            letter-spacing: -0.2px;
        }
        
        .admin-sidebar-menu {
            padding: 10px 0;
        }
        
        .admin-sidebar-menu a {
            display: flex;
            align-items: center;
            gap: 12px;
            padding: 14px 20px;
            color: #b4b9be;
            text-decoration: none;
            font-size: 14px;
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            border-left: 4px solid transparent;
            font-family: 'Plus Jakarta Sans', sans-serif;
            font-weight: 500;
            margin: 4px 8px;
            border-radius: 8px;
            position: relative;
        }
        
        .admin-sidebar-menu a::before {
            content: '';
            position: absolute;
            left: 0;
            top: 0;
            bottom: 0;
            width: 4px;
            background: linear-gradient(180deg, <?= $primaryColor ?> 0%, <?= $primaryDark ?> 100%);
            border-radius: 0 4px 4px 0;
            opacity: 0;
            transition: opacity 0.3s ease;
        }
        
        .admin-sidebar-menu a:hover {
            background: linear-gradient(90deg, rgba(16, 185, 129, 0.15) 0%, rgba(16, 185, 129, 0.05) 100%);
            color: #fff;
            transform: translateX(4px);
            box-shadow: -2px 0 8px rgba(16, 185, 129, 0.2);
        }
        
        .admin-sidebar-menu a:hover::before {
            opacity: 1;
        }
        
        .admin-sidebar-menu a.active {
            background: linear-gradient(90deg, rgba(16, 185, 129, 0.25) 0%, rgba(16, 185, 129, 0.1) 100%);
            color: #fff;
            box-shadow: -2px 0 12px rgba(16, 185, 129, 0.3), inset 0 0 20px rgba(16, 185, 129, 0.1);
        }
        
        .admin-sidebar-menu a.active::before {
            opacity: 1;
        }
        
        .admin-sidebar-menu a i {
            width: 20px;
            text-align: center;
            font-size: 16px;
        }
        
        .admin-sidebar-menu a.disabled {
            opacity: 0.5;
            cursor: not-allowed;
        }
        
        .admin-sidebar-menu a.disabled:hover {
            background: transparent;
            color: #b4b9be;
            border-left-color: transparent;
        }
        
        /* Main Content */
        .admin-main {
            flex: 1;
            margin-left: 200px;
            min-height: 100vh;
        }
        
        .admin-topbar {
            background: linear-gradient(180deg, #ffffff 0%, #fafbfc 100%);
            border-bottom: 1px solid rgba(0,0,0,.06);
            padding: 0 24px;
            height: 56px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            position: sticky;
            top: 0;
            z-index: 100;
            box-shadow: 0 2px 8px rgba(0,0,0,.08), 0 1px 2px rgba(0,0,0,.04);
        }
        
        .admin-topbar-left {
            display: flex;
            align-items: center;
            gap: 12px;
        }
        
        .admin-topbar-right {
            display: flex;
            align-items: center;
            gap: 12px;
        }
        
        .admin-topbar a {
            color: #2c3338;
            text-decoration: none;
            font-size: 14px;
            display: inline-flex;
            align-items: center;
            gap: 8px;
            padding: 10px 18px;
            border-radius: 8px;
            transition: all 0.25s cubic-bezier(0.4, 0, 0.2, 1);
            font-weight: 600;
            font-family: 'Poppins', 'Plus Jakarta Sans', sans-serif;
            border: none;
            cursor: pointer;
            box-shadow: 0 1px 2px rgba(0,0,0,.08);
            letter-spacing: 0.2px;
        }
        
        .admin-topbar a.topbar-link-view {
            background: linear-gradient(135deg, <?= $primaryColor ?> 0%, <?= $primaryDark ?> 100%);
            color: #ffffff;
            box-shadow: 0 3px 10px rgba(16, 185, 129, 0.3), 0 1px 3px rgba(16, 185, 129, 0.2), inset 0 1px 0 rgba(255,255,255,.2);
        }
        
        .admin-topbar a.topbar-link-view:hover {
            background: linear-gradient(135deg, <?= $primaryDark ?> 0%, #047857 100%);
            color: #fff;
            transform: translateY(-3px);
            box-shadow: 0 6px 20px rgba(16, 185, 129, 0.4), 0 3px 8px rgba(16, 185, 129, 0.3), inset 0 1px 0 rgba(255,255,255,.2);
        }
        
        .admin-topbar a.topbar-link-logout {
            background: linear-gradient(135deg, #d63638 0%, #b32d2e 100%);
            color: #ffffff;
            box-shadow: 0 3px 10px rgba(214, 54, 56, 0.3), 0 1px 3px rgba(214, 54, 56, 0.2), inset 0 1px 0 rgba(255,255,255,.2);
        }
        
        .admin-topbar a.topbar-link-logout:hover {
            background: linear-gradient(135deg, #b32d2e 0%, #8a2424 100%);
            color: #fff;
            transform: translateY(-3px);
            box-shadow: 0 6px 20px rgba(214, 54, 56, 0.4), 0 3px 8px rgba(214, 54, 56, 0.3), inset 0 1px 0 rgba(255,255,255,.2);
        }
        
        .admin-content {
            padding: 24px;
            max-width: 1200px;
            background: #ffffff;
        }
        
        .admin-page-header {
            margin-bottom: 20px;
        }
        
        .admin-page-header h1 {
            font-size: 24px;
            font-weight: 600;
            color: #1d2327;
            margin: 0;
            padding: 0;
            font-family: 'Poppins', 'Plus Jakarta Sans', sans-serif;
            letter-spacing: -0.3px;
        }
        
        .admin-menu-toggle {
            display: none;
            background: none;
            border: none;
            color: #2c3338;
            font-size: 20px;
            cursor: pointer;
            padding: 5px;
        }
        
        @media (max-width: 782px) {
            .admin-menu-toggle {
                display: block;
            }
            
            .admin-sidebar {
                transform: translateX(-100%);
            }
            
            .admin-sidebar.open {
                transform: translateX(0);
            }
            
            .admin-main {
                margin-left: 0;
            }
            
            .admin-content {
                padding: 15px;
            }
        }
        
        .admin-overlay {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: rgba(0, 0, 0, 0.5);
            z-index: 999;
        }
        
        .admin-overlay.open {
            display: block;
        }
        
        /* Global Styles - Dosen Theme */
        .card {
            background: linear-gradient(180deg, #ffffff 0%, #fafbfc 100%);
            border: 1px solid rgba(0,0,0,.06);
            border-radius: 16px;
            padding: 32px;
            box-shadow: 0 4px 16px rgba(0,0,0,.08), 0 2px 4px rgba(0,0,0,.04);
            margin-bottom: 28px;
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            position: relative;
            overflow: hidden;
        }
        
        .card::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 3px;
            background: linear-gradient(90deg, <?= $primaryColor ?> 0%, <?= $primaryDark ?> 50%, <?= $primaryColor ?> 100%);
            opacity: 0;
            transition: opacity 0.3s ease;
        }
        
        .card:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 24px rgba(0,0,0,.12), 0 4px 8px rgba(0,0,0,.06);
            border-color: rgba(16, 185, 129, 0.2);
        }
        
        .card:hover::before {
            opacity: 1;
        }
        
        .card h2 {
            font-size: 20px;
            font-weight: 700;
            margin-bottom: 28px;
            padding-bottom: 16px;
            border-bottom: 2px solid #f0f0f0;
            color: #1d2327;
            font-family: 'Poppins', 'Plus Jakarta Sans', sans-serif;
            letter-spacing: -0.3px;
            position: relative;
        }
        
        .card h2::after {
            content: '';
            position: absolute;
            bottom: -2px;
            left: 0;
            width: 50px;
            height: 2px;
            background: linear-gradient(90deg, <?= $primaryColor ?> 0%, <?= $primaryDark ?> 100%);
            border-radius: 2px;
        }
        
        .btn {
            border: none;
            border-radius: 10px;
            padding: 14px 28px;
            font-weight: 600;
            color: #fff;
            background: linear-gradient(135deg, <?= $primaryColor ?> 0%, <?= $primaryDark ?> 100%);
            cursor: pointer;
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            font-size: 14px;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 10px;
            box-shadow: 0 4px 12px rgba(16, 185, 129, 0.3), 0 2px 4px rgba(16, 185, 129, 0.2), inset 0 1px 0 rgba(255,255,255,.2);
            font-family: 'Poppins', 'Plus Jakarta Sans', sans-serif;
            letter-spacing: 0.2px;
        }
        
        .btn:hover {
            background: linear-gradient(135deg, <?= $primaryDark ?> 0%, #047857 100%);
            box-shadow: 0 6px 20px rgba(16, 185, 129, 0.4), 0 4px 8px rgba(16, 185, 129, 0.3), inset 0 1px 0 rgba(255,255,255,.2);
            transform: translateY(-3px);
        }
        
        .btn.secondary {
            background: linear-gradient(180deg, #ffffff 0%, #f8f9fa 100%);
            color: #495057;
            border: 2px solid #e9ecef;
            box-shadow: 0 2px 6px rgba(0,0,0,.08), inset 0 1px 0 rgba(255,255,255,.8);
        }
        
        .btn.danger {
            background: linear-gradient(135deg, #d63638 0%, #b32d2e 100%);
            box-shadow: 0 4px 12px rgba(214, 54, 56, 0.3), 0 2px 4px rgba(214, 54, 56, 0.2), inset 0 1px 0 rgba(255,255,255,.2);
        }
        
        .btn.danger:hover {
            background: linear-gradient(135deg, #b32d2e 0%, #8a2424 100%);
            box-shadow: 0 6px 20px rgba(214, 54, 56, 0.4), 0 4px 8px rgba(214, 54, 56, 0.3), inset 0 1px 0 rgba(255,255,255,.2);
            transform: translateY(-3px);
        }
        
        .alert {
            padding: 14px 16px;
            border-radius: 6px;
            margin-bottom: 20px;
            font-size: 14px;
            border-left: 4px solid;
        }
        
        .alert-success {
            background: #f0f9f4;
            color: #1e7e34;
            border-left-color: <?= $primaryColor ?>;
        }
        
        .alert-error {
            background: #fef7f7;
            color: #b32d2e;
            border-left-color: #d63638;
        }
        
        table {
            width: 100%;
            border-collapse: separate;
            border-spacing: 0;
            margin-top: 12px;
            background: #fff;
            border-radius: 12px;
            overflow: hidden;
            box-shadow: 0 2px 8px rgba(0,0,0,.06);
        }
        
        th, td {
            text-align: left;
            padding: 18px 16px;
            border-bottom: 1px solid #f0f0f0;
        }
        
        th {
            color: #646970;
            font-weight: 700;
            text-transform: uppercase;
            font-size: 11px;
            letter-spacing: 0.8px;
            background: linear-gradient(180deg, #f8f9fa 0%, #f0f0f0 100%);
        }
        
        .form-group {
            flex: 1;
            min-width: 220px;
            display: flex;
            flex-direction: column;
            margin-bottom: 20px;
        }
        
        label {
            font-weight: 600;
            color: #1d2327;
            margin-bottom: 10px;
            font-size: 14px;
        }
        
        input[type="text"],
        input[type="number"],
        input[type="email"],
        select,
        textarea {
            padding: 14px 18px;
            border: 2px solid #e8e8e8;
            border-radius: 10px;
            font-size: 14px;
            background: linear-gradient(180deg, #ffffff 0%, #fafbfc 100%);
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            width: 100%;
        }
        
        input:focus,
        select:focus,
        textarea:focus {
            outline: none;
            border-color: <?= $primaryColor ?>;
            box-shadow: 0 0 0 4px rgba(16, 185, 129, 0.12), 0 4px 12px rgba(16, 185, 129, 0.15);
        }
    </style>
</head>
<body>
    <div class="admin-wrapper">
        <!-- Sidebar -->
        <aside class="admin-sidebar" id="adminSidebar">
            <div class="admin-sidebar-header">
                <div class="admin-sidebar-logo">
                    <i class="fas fa-chalkboard-teacher"></i>
                </div>
                <h1>Dashboard Dosen</h1>
            </div>
            <nav class="admin-sidebar-menu">
                <a href="/crims/dosen/dashboard.php" <?= $activePage === 'dashboard' ? 'class="active"' : '' ?>>
                    <i class="fas fa-tachometer-alt"></i>
                    <span>Dashboard</span>
                </a>
                <a href="/crims/dosen/achievements.php" <?= $activePage === 'achievements' ? 'class="active"' : '' ?>>
                    <i class="fas fa-trophy"></i>
                    <span>Prestasi</span>
                </a>
                <a href="/crims/dosen/news.php" <?= $activePage === 'news' ? 'class="active"' : '' ?>>
                    <i class="fas fa-newspaper"></i>
                    <span>Berita</span>
                </a>
                <a href="/crims/dosen/projects.php" <?= $activePage === 'projects' ? 'class="active"' : '' ?>>
                    <i class="fas fa-project-diagram"></i>
                    <span>Proyek</span>
                </a>
            </nav>
        </aside>
        
        <!-- Main Content -->
        <main class="admin-main">
            <div class="admin-topbar">
                <div class="admin-topbar-left">
                    <button class="admin-menu-toggle" id="menuToggle">
                        <i class="fas fa-bars"></i>
                    </button>
                </div>
                <div class="admin-topbar-right">
                    <a href="/crims/" class="topbar-link-view">
                        <i class="fas fa-external-link-alt"></i>
                        <span>Lihat Situs</span>
                    </a>
                    <a href="/crims/admin/logout.php" class="topbar-link-logout">
                        <i class="fas fa-sign-out-alt"></i>
                        <span>Logout</span>
                    </a>
                </div>
            </div>
            
            <div class="admin-content">
                <?= $content ?>
            </div>
        </main>
    </div>
    
    <!-- Mobile Overlay -->
    <div class="admin-overlay" id="adminOverlay"></div>
    
    <script>
        const menuToggle = document.getElementById('menuToggle');
        const sidebar = document.getElementById('adminSidebar');
        const overlay = document.getElementById('adminOverlay');
        
        if (menuToggle) {
            menuToggle.addEventListener('click', function() {
                sidebar.classList.toggle('open');
                overlay.classList.toggle('open');
            });
        }
        
        if (overlay) {
            overlay.addEventListener('click', function() {
                sidebar.classList.remove('open');
                overlay.classList.remove('open');
            });
        }
    </script>
    
    <!-- Bootstrap 4 JS and Summernote JS (hanya untuk halaman yang membutuhkan) -->
    <?php if (strpos($pageTitle, 'Proyek') !== false || strpos($pageTitle, 'Prestasi') !== false || strpos($pageTitle, 'Achievements') !== false || strpos($content, 'id="summary"') !== false || strpos($content, 'id="description"') !== false): ?>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/summernote@0.8.18/dist/summernote-bs4.min.js"></script>
    <script>
        // Initialize Summernote after all scripts are loaded
        window.addEventListener('load', function() {
            function initSummernote() {
                if (typeof jQuery === 'undefined' || typeof $.fn.summernote === 'undefined') {
                    setTimeout(initSummernote, 100);
                    return;
                }
                
                // Initialize for summary textarea
                var $summary = $('#summary');
                if ($summary.length > 0 && !$summary.next('.note-editor').length) {
                    $summary.summernote({
                        height: 300,
                        toolbar: [
                            ['style', ['style']],
                            ['font', ['bold', 'italic', 'underline', 'strikethrough', 'clear']],
                            ['fontsize', ['fontsize']],
                            ['para', ['ul', 'ol', 'paragraph']],
                            ['height', ['height']]
                        ],
                        disableDragAndDrop: true,
                        popover: {
                            image: [],
                            link: [],
                            air: []
                        },
                        callbacks: {
                            onInit: function() {
                                var $editor = $summary.next('.note-editor');
                                var $editable = $editor.find('.note-editable');
                                
                                function updateCharCounter() {
                                    var content = $editable.text();
                                    var charCount = content.length;
                                    
                                    var $counter = $editor.find('.summernote-char-counter');
                                    if ($counter.length === 0) {
                                        $counter = $('<div class="summernote-char-counter">0 karakter</div>');
                                        $editor.append($counter);
                                    }
                                    $counter.text(charCount + ' karakter');
                                }
                                
                                $editable.on('input keyup paste', function() {
                                    setTimeout(updateCharCounter, 10);
                                });
                                
                                setTimeout(updateCharCounter, 100);
                            }
                        },
                        placeholder: 'Tulis ringkasan proyek di sini...',
                        lang: 'id-ID'
                    });
                }
                
                // Initialize for description textarea (for achievements)
                var $description = $('#description');
                if ($description.length > 0 && !$description.next('.note-editor').length) {
                    $description.summernote({
                        height: 300,
                        toolbar: [
                            ['style', ['style']],
                            ['font', ['bold', 'italic', 'underline', 'strikethrough', 'clear']],
                            ['fontsize', ['fontsize']],
                            ['para', ['ul', 'ol', 'paragraph']],
                            ['height', ['height']]
                        ],
                        disableDragAndDrop: true,
                        popover: {
                            image: [],
                            link: [],
                            air: []
                        },
                        callbacks: {
                            onInit: function() {
                                var $editor = $description.next('.note-editor');
                                var $editable = $editor.find('.note-editable');
                                
                                function updateCharCounter() {
                                    var content = $editable.text();
                                    var charCount = content.length;
                                    
                                    var $counter = $editor.find('.summernote-char-counter');
                                    if ($counter.length === 0) {
                                        $counter = $('<div class="summernote-char-counter">0 karakter</div>');
                                        $editor.append($counter);
                                    }
                                    $counter.text(charCount + ' karakter');
                                }
                                
                                $editable.on('input keyup paste', function() {
                                    setTimeout(updateCharCounter, 10);
                                });
                                
                                setTimeout(updateCharCounter, 100);
                            }
                        },
                        placeholder: 'Tulis deskripsi prestasi di sini...',
                        lang: 'id-ID'
                    });
                }
            }
            setTimeout(initSummernote, 200);
        });
    </script>
    <style>
        /* Dark Toolbar, White Editor Background */
        .note-editor.note-frame {
            border: 2px solid #333 !important;
            border-radius: 12px !important;
            overflow: hidden;
            margin-top: 8px;
            background: #ffffff !important;
        }
        .note-editor .note-toolbar {
            background: #2d2d2d !important;
            border-bottom: 1px solid #444 !important;
            padding: 8px !important;
        }
        .note-editor .note-toolbar button {
            background: #3a3a3a !important;
            border-color: #444 !important;
            color: #fff !important;
            transition: none !important;
            transform: none !important;
        }
        .note-editor .note-toolbar button:hover {
            background: #3a3a3a !important;
            border-color: #444 !important;
            transform: none !important;
            box-shadow: none !important;
        }
        .note-editor .note-toolbar button:active {
            transform: none !important;
            box-shadow: none !important;
        }
        .note-editor .note-toolbar .dropdown-toggle {
            background: #3a3a3a !important;
            color: #fff !important;
            transition: none !important;
        }
        .note-editor .note-toolbar .dropdown-toggle:hover {
            background: #3a3a3a !important;
            transform: none !important;
        }
        .note-editor .note-toolbar .dropdown-menu {
            background: #2d2d2d !important;
            border-color: #444 !important;
            transition: none !important;
            animation: none !important;
        }
        .note-editor .note-toolbar .dropdown-item {
            color: #fff !important;
            transition: none !important;
        }
        .note-editor .note-toolbar .dropdown-item:hover {
            background: #2d2d2d !important;
            color: #fff !important;
            transform: none !important;
        }
        /* Remove all transitions and animations */
        .note-editor * {
            transition: none !important;
            animation: none !important;
        }
        .note-editor .note-toolbar * {
            transition: none !important;
            animation: none !important;
        }
        .note-editor .note-editing-area * {
            transition: none !important;
            animation: none !important;
        }
        .note-editor .note-editing-area {
            background: #ffffff !important;
        }
        .note-editor .note-editing-area .note-editable {
            min-height: 250px !important;
            padding: 15px !important;
            font-size: 14px !important;
            line-height: 1.6 !important;
            background: #ffffff !important;
            color: #000000 !important;
        }
        .note-editor .note-editing-area .note-editable:focus {
            background: #ffffff !important;
            color: #000000 !important;
        }
        .note-editor .note-editing-area .note-editable::placeholder {
            color: #999 !important;
        }
        .note-editor .note-statusbar,
        .note-editor .note-status-output,
        .note-editor .note-resizebar,
        .note-editor .note-editing-area * {
            background: #ffffff !important;
        }
        .note-editor .note-editing-area p,
        .note-editor .note-editing-area div,
        .note-editor .note-editing-area span,
        .note-editor .note-editing-area li,
        .note-editor .note-editing-area ul,
        .note-editor .note-editing-area ol {
            color: #000000 !important;
        }
        /* Character counter styling */
        .summernote-char-counter {
            position: absolute;
            bottom: 10px;
            right: 15px;
            font-size: 12px;
            color: #666;
            background: rgba(255, 255, 255, 0.9);
            padding: 4px 8px;
            border-radius: 4px;
            pointer-events: none;
            z-index: 10;
        }
        .note-editor.note-frame {
            position: relative;
        }
        /* Hide font family and color picker */
        .note-editor .note-toolbar .note-fontname,
        .note-editor .note-toolbar .note-color,
        .note-editor .note-toolbar button[data-event="color"],
        .note-editor .note-toolbar button[data-event="fontName"],
        .note-editor .note-toolbar .btn-group:has(button[data-event="color"]),
        .note-editor .note-toolbar .btn-group:has(button[data-event="fontName"]) {
            display: none !important;
        }
    </style>
    <?php endif; ?>
</body>
</html>
        <?php
    }
}
?>

