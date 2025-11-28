<?php
if (!function_exists('renderAdminLayout')) {
    function renderAdminLayout($activePage = 'dashboard', $pageTitle = 'Admin Panel', $content = '') {
        ?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($pageTitle) ?> - AdminCrims</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700&family=Plus+Jakarta+Sans:wght@400;500;600;700&display=swap" rel="stylesheet">
    <!-- Bootstrap 4 CSS (required for Summernote BS4) -->
    <?php if (strpos($pageTitle, 'Prestasi') !== false || strpos($pageTitle, 'Achievements') !== false || strpos($content, 'id="description"') !== false || strpos($content, 'id="summary"') !== false || strpos($pageTitle, 'Proyek') !== false || strpos($pageTitle, 'Project') !== false || strpos($pageTitle, 'Hilirisasi') !== false): ?>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/css/bootstrap.min.css">
    <!-- Summernote CSS -->
    <link href="https://cdn.jsdelivr.net/npm/summernote@0.8.18/dist/summernote-bs4.min.css" rel="stylesheet">
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
        /* Ensure all areas below toolbar are white */
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
        .note-editor .note-toolbar button[title*="Font Family" i],
        .note-editor .note-toolbar button[title*="Color" i],
        .note-editor .note-toolbar button[title*="Text Color" i],
        .note-editor .note-toolbar .btn-group:has(button[data-event="color"]),
        .note-editor .note-toolbar .btn-group:has(button[data-event="fontName"]),
        .note-editor .note-toolbar .btn-group:has(.note-fontname),
        .note-editor .note-toolbar .btn-group:has(.note-color) {
            display: none !important;
            visibility: hidden !important;
        }
        /* Hide unwanted toolbar buttons - ULTRA COMPREHENSIVE */
        .note-editor .note-toolbar .note-table,
        .note-editor .note-toolbar .note-link,
        .note-editor .note-toolbar .note-picture,
        .note-editor .note-toolbar .note-video,
        .note-editor .note-toolbar .note-fullscreen,
        .note-editor .note-toolbar .note-codeview,
        .note-editor .note-toolbar .note-help,
        .note-editor .note-toolbar button[data-original-title*="Table" i],
        .note-editor .note-toolbar button[data-original-title*="Link" i],
        .note-editor .note-toolbar button[data-original-title*="Picture" i],
        .note-editor .note-toolbar button[data-original-title*="Image" i],
        .note-editor .note-toolbar button[data-original-title*="Video" i],
        .note-editor .note-toolbar button[data-original-title*="Fullscreen" i],
        .note-editor .note-toolbar button[data-original-title*="Code" i],
        .note-editor .note-toolbar button[data-original-title*="Help" i],
        .note-editor .note-toolbar button[data-event*="link" i],
        .note-editor .note-toolbar button[data-event*="image" i],
        .note-editor .note-toolbar button[data-event*="picture" i],
        .note-editor .note-toolbar button[data-event*="video" i],
        .note-editor .note-toolbar button[data-event*="table" i],
        .note-editor .note-toolbar button[data-event*="fullscreen" i],
        .note-editor .note-toolbar button[data-event*="codeview" i],
        .note-editor .note-toolbar button[data-event*="help" i],
        .note-editor .note-toolbar button[title*="link" i],
        .note-editor .note-toolbar button[title*="picture" i],
        .note-editor .note-toolbar button[title*="image" i],
        .note-editor .note-toolbar button[title*="video" i],
        .note-editor .note-toolbar button[title*="table" i],
        .note-editor .note-toolbar button[title*="fullscreen" i],
        .note-editor .note-toolbar button[title*="code" i],
        .note-editor .note-toolbar button[title*="help" i],
        .note-editor .note-toolbar button:has(.fa-link),
        .note-editor .note-toolbar button:has(.fa-chain),
        .note-editor .note-toolbar button:has(.fa-image),
        .note-editor .note-toolbar button:has(.fa-picture-o),
        .note-editor .note-toolbar button:has(.fa-picture),
        .note-editor .note-toolbar button:has(.fa-video),
        .note-editor .note-toolbar button:has(.fa-video-camera),
        .note-editor .note-toolbar button:has(.fa-expand),
        .note-editor .note-toolbar button:has(.fa-arrows-alt),
        .note-editor .note-toolbar button:has(.fa-code),
        .note-editor .note-toolbar button:has(.fa-question),
        .note-editor .note-toolbar button:has(.fa-question-circle),
        .note-editor .note-toolbar button:has(.fa-table),
        .note-editor .note-toolbar .fa-link,
        .note-editor .note-toolbar .fa-chain,
        .note-editor .note-toolbar .fa-image,
        .note-editor .note-toolbar .fa-picture-o,
        .note-editor .note-toolbar .fa-picture,
        .note-editor .note-toolbar .fa-video,
        .note-editor .note-toolbar .fa-video-camera,
        .note-editor .note-toolbar .fa-expand,
        .note-editor .note-toolbar .fa-arrows-alt,
        .note-editor .note-toolbar .fa-code,
        .note-editor .note-toolbar .fa-question,
        .note-editor .note-toolbar .fa-question-circle,
        .note-editor .note-toolbar .fa-table {
            display: none !important;
            visibility: hidden !important;
            opacity: 0 !important;
            width: 0 !important;
            height: 0 !important;
            padding: 0 !important;
            margin: 0 !important;
            position: absolute !important;
            left: -9999px !important;
        }
        /* Hide parent button groups containing unwanted buttons */
        .note-editor .note-toolbar .btn-group:has(button[data-event*="link" i]),
        .note-editor .note-toolbar .btn-group:has(button[data-event*="image" i]),
        .note-editor .note-toolbar .btn-group:has(button[data-event*="picture" i]),
        .note-editor .note-toolbar .btn-group:has(button[data-event*="video" i]),
        .note-editor .note-toolbar .btn-group:has(button[data-event*="table" i]),
        .note-editor .note-toolbar .btn-group:has(button[data-event*="fullscreen" i]),
        .note-editor .note-toolbar .btn-group:has(button[data-event*="codeview" i]),
        .note-editor .note-toolbar .btn-group:has(button[data-event*="help" i]),
        .note-editor .note-toolbar .btn-group:has(.fa-link),
        .note-editor .note-toolbar .btn-group:has(.fa-image),
        .note-editor .note-toolbar .btn-group:has(.fa-picture),
        .note-editor .note-toolbar .btn-group:has(.fa-video),
        .note-editor .note-toolbar .btn-group:has(.fa-expand),
        .note-editor .note-toolbar .btn-group:has(.fa-code),
        .note-editor .note-toolbar .btn-group:has(.fa-question),
        .note-editor .note-toolbar .btn-group:has(.fa-table) {
            display: none !important;
            visibility: hidden !important;
        }
    </style>
    <?php endif; ?>
    <!-- jQuery (required for Summernote and other scripts) -->
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
        
        /* Sidebar */
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
            background: linear-gradient(135deg, #2271b1 0%, #135e96 100%);
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            flex-shrink: 0;
            box-shadow: 0 4px 12px rgba(34, 113, 177, 0.4), 0 2px 4px rgba(34, 113, 177, 0.3), inset 0 1px 0 rgba(255,255,255,.3);
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
            background: linear-gradient(180deg, #2271b1 0%, #135e96 100%);
            border-radius: 0 4px 4px 0;
            opacity: 0;
            transition: opacity 0.3s ease;
        }
        
        .admin-sidebar-menu a:hover {
            background: linear-gradient(90deg, rgba(34, 113, 177, 0.15) 0%, rgba(34, 113, 177, 0.05) 100%);
            color: #fff;
            transform: translateX(4px);
            box-shadow: -2px 0 8px rgba(34, 113, 177, 0.2);
        }
        
        .admin-sidebar-menu a:hover::before {
            opacity: 1;
        }
        
        .admin-sidebar-menu a.active {
            background: linear-gradient(90deg, rgba(34, 113, 177, 0.25) 0%, rgba(34, 113, 177, 0.1) 100%);
            color: #fff;
            box-shadow: -2px 0 12px rgba(34, 113, 177, 0.3), inset 0 0 20px rgba(34, 113, 177, 0.1);
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
        
        /* Dropdown Menu */
        .admin-sidebar-menu .dropdown {
            position: relative;
        }
        
        .admin-sidebar-menu .dropdown-toggle {
            cursor: pointer;
        }
        
        .admin-sidebar-menu .dropdown-toggle::after {
            content: '\f107';
            font-family: 'Font Awesome 6 Free';
            font-weight: 900;
            margin-left: auto;
            transition: transform 0.3s ease;
        }
        
        .admin-sidebar-menu .dropdown.active .dropdown-toggle::after {
            transform: rotate(180deg);
        }
        
        .admin-sidebar-menu .dropdown-menu {
            max-height: 0;
            overflow: hidden;
            transition: max-height 0.3s ease;
            padding-left: 20px;
        }
        
        .admin-sidebar-menu .dropdown.active .dropdown-menu {
            max-height: 500px;
        }
        
        .admin-sidebar-menu .dropdown-menu a {
            padding: 10px 20px 10px 48px;
            margin: 2px 8px;
            font-size: 13px;
            position: relative;
        }
        
        .admin-sidebar-menu .dropdown-menu a::before {
            content: '→';
            position: absolute;
            left: 28px;
            font-size: 12px;
            opacity: 0.6;
        }
        
        .admin-sidebar-menu .dropdown-menu a.active {
            padding-left: 48px;
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
            background: linear-gradient(135deg, #2271b1 0%, #135e96 100%);
            color: #ffffff;
            box-shadow: 0 3px 10px rgba(34, 113, 177, 0.3), 0 1px 3px rgba(34, 113, 177, 0.2), inset 0 1px 0 rgba(255,255,255,.2);
        }
        
        .admin-topbar a.topbar-link-view:hover {
            background: linear-gradient(135deg, #135e96 0%, #0f4c75 100%);
            color: #fff;
            transform: translateY(-3px);
            box-shadow: 0 6px 20px rgba(34, 113, 177, 0.4), 0 3px 8px rgba(34, 113, 177, 0.3), inset 0 1px 0 rgba(255,255,255,.2);
        }
        
        .admin-topbar a.topbar-link-view:active {
            transform: translateY(-1px);
            box-shadow: 0 2px 8px rgba(34, 113, 177, 0.3), inset 0 2px 4px rgba(0,0,0,.1);
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
        
        .admin-topbar a.topbar-link-logout:active {
            transform: translateY(-1px);
            box-shadow: 0 2px 8px rgba(214, 54, 56, 0.3), inset 0 2px 4px rgba(0,0,0,.1);
        }
        
        .admin-topbar a i {
            font-size: 14px;
            font-weight: 600;
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
        
        /* Mobile Menu Toggle */
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
        
        /* Overlay for mobile */
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
        
        /* Global Admin Styles */
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
            background: linear-gradient(90deg, #2271b1 0%, #135e96 50%, #2271b1 100%);
            opacity: 0;
            transition: opacity 0.3s ease;
        }
        
        .card:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 24px rgba(0,0,0,.12), 0 4px 8px rgba(0,0,0,.06);
            border-color: rgba(34, 113, 177, 0.2);
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
            background: linear-gradient(90deg, #2271b1 0%, #135e96 100%);
            border-radius: 2px;
        }
        
        .form-row {
            display: flex;
            gap: 20px;
            flex-wrap: wrap;
            margin-bottom: 0;
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
            display: block;
            font-family: 'Poppins', 'Plus Jakarta Sans', sans-serif;
            letter-spacing: -0.1px;
        }
        
        label small {
            display: block;
            font-weight: 500;
            color: #646970;
            font-size: 13px;
            margin-top: 6px;
            line-height: 1.5;
            font-family: 'Plus Jakarta Sans', sans-serif;
        }
        
        input[type="text"],
        input[type="number"],
        input[type="email"],
        input[type="url"],
        input[type="file"],
        select,
        textarea {
            padding: 14px 18px;
            border: 2px solid #e8e8e8;
            border-radius: 10px;
            font-size: 14px;
            background: linear-gradient(180deg, #ffffff 0%, #fafbfc 100%);
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            width: 100%;
            color: #2c3338;
            font-family: 'Plus Jakarta Sans', 'Poppins', sans-serif;
            font-weight: 500;
            box-shadow: inset 0 2px 4px rgba(0,0,0,.04), 0 1px 2px rgba(0,0,0,.04);
        }
        
        input:hover,
        select:hover,
        textarea:hover {
            border-color: #2271b1;
            background: #fff;
            box-shadow: inset 0 2px 4px rgba(0,0,0,.04), 0 2px 8px rgba(34, 113, 177, 0.1);
            transform: translateY(-1px);
        }
        
        input:focus,
        select:focus,
        textarea:focus {
            outline: none;
            border-color: #2271b1;
            background: #fff;
            box-shadow: 0 0 0 4px rgba(34, 113, 177, 0.12), 0 4px 12px rgba(34, 113, 177, 0.15);
            transform: translateY(-2px);
        }
        
        input::placeholder,
        textarea::placeholder {
            color: #8c8f94;
        }
        
        textarea {
            min-height: 100px;
            resize: vertical;
        }
        
        .btn {
            border: none;
            border-radius: 10px;
            padding: 14px 28px;
            font-weight: 600;
            color: #fff;
            background: linear-gradient(135deg, #2271b1 0%, #135e96 100%);
            cursor: pointer;
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            font-size: 14px;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 10px;
            box-shadow: 0 4px 12px rgba(34, 113, 177, 0.3), 0 2px 4px rgba(34, 113, 177, 0.2), inset 0 1px 0 rgba(255,255,255,.2);
            font-family: 'Poppins', 'Plus Jakarta Sans', sans-serif;
            letter-spacing: 0.2px;
            position: relative;
            overflow: hidden;
        }
        
        .btn::before {
            content: '';
            position: absolute;
            top: 0;
            left: -100%;
            width: 100%;
            height: 100%;
            background: linear-gradient(90deg, transparent, rgba(255,255,255,.2), transparent);
            transition: left 0.5s ease;
        }
        
        .btn:hover {
            background: linear-gradient(135deg, #135e96 0%, #0f4c75 100%);
            box-shadow: 0 6px 20px rgba(34, 113, 177, 0.4), 0 4px 8px rgba(34, 113, 177, 0.3), inset 0 1px 0 rgba(255,255,255,.2);
            transform: translateY(-3px);
        }
        
        .btn:hover::before {
            left: 100%;
        }
        
        .btn:active {
            transform: translateY(-1px);
            box-shadow: 0 2px 8px rgba(34, 113, 177, 0.3), inset 0 2px 4px rgba(0,0,0,.1);
        }
        
        .btn.secondary {
            background: linear-gradient(180deg, #ffffff 0%, #f8f9fa 100%);
            color: #495057;
            border: 2px solid #e9ecef;
            box-shadow: 0 2px 6px rgba(0,0,0,.08), inset 0 1px 0 rgba(255,255,255,.8);
        }
        
        .btn.secondary:hover {
            background: linear-gradient(180deg, #f8f9fa 0%, #e9ecef 100%);
            border-color: #dee2e6;
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(0,0,0,.12), inset 0 1px 0 rgba(255,255,255,.8);
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
            display: flex;
            align-items: center;
            gap: 12px;
            font-family: 'Plus Jakarta Sans', sans-serif;
            font-weight: 500;
            line-height: 1.6;
        }
        
        .alert i {
            font-size: 18px;
        }
        
        .alert-success {
            background: #f0f9f4;
            color: #1e7e34;
            border-left-color: #00a32a;
        }
        
        .alert-success i {
            color: #00a32a;
        }
        
        .alert-error {
            background: #fef7f7;
            color: #b32d2e;
            border-left-color: #d63638;
        }
        
        .alert-error i {
            color: #d63638;
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
            font-family: 'Poppins', 'Plus Jakarta Sans', sans-serif;
            border-bottom: 2px solid #e0e0e0;
        }
        
        td {
            font-family: 'Plus Jakarta Sans', sans-serif;
            font-weight: 500;
            color: #2c3338;
            background: #fff;
            transition: all 0.2s ease;
        }
        
        tr:hover td {
            background: linear-gradient(180deg, #fafbfc 0%, #f5f6f7 100%);
            transform: scale(1.01);
        }
        
        tr:last-child td {
            border-bottom: none;
        }
        
        .actions {
            display: flex;
            gap: 8px;
        }
        
        /* Text & Description Styling */
        p, .description, .text-muted {
            font-family: 'Plus Jakarta Sans', sans-serif;
            font-weight: 500;
            line-height: 1.7;
            color: #495057;
            font-size: 14px;
        }
        
        small, .small-text {
            font-family: 'Plus Jakarta Sans', sans-serif;
            font-weight: 500;
            font-size: 13px;
            line-height: 1.6;
        }
        
        @media (max-width: 768px) {
            .form-row {
                flex-direction: column;
            }
            
            .card {
                padding: 15px;
            }
        }
    </style>
</head>
<body>
    <div class="admin-wrapper">
        <!-- Sidebar -->
        <aside class="admin-sidebar" id="adminSidebar">
            <div class="admin-sidebar-header">
                <div class="admin-sidebar-logo">
                    <i class="fas fa-cogs"></i>
                </div>
                <h1>AdminCrims</h1>
            </div>
            <nav class="admin-sidebar-menu">
                <a href="/crims/admin/dashboard.php" <?= $activePage === 'dashboard' || $activePage === 'home' ? 'class="active"' : '' ?>>
                    <i class="fas fa-home"></i>
                    <span>Beranda</span>
                </a>
                <a href="/crims/admin/dashboard.php" <?= $activePage === 'dashboard' ? 'class="active"' : '' ?>>
                    <i class="fas fa-tachometer-alt"></i>
                    <span>Dashboard</span>
                </a>
                <a href="/crims/admin/users.php" <?= $activePage === 'users' ? 'class="active"' : '' ?>>
                    <i class="fas fa-user-cog"></i>
                    <span>Users</span>
                </a>
                <a href="/crims/admin/team.php" <?= $activePage === 'team' ? 'class="active"' : '' ?>>
                    <i class="fas fa-users"></i>
                    <span>Struktur Tim Riset</span>
                </a>
                <a href="/crims/admin/projects.php" <?= $activePage === 'projects' ? 'class="active"' : '' ?>>
                    <i class="fas fa-project-diagram"></i>
                    <span>Project</span>
                </a>
                <a href="/crims/admin/news.php" <?= $activePage === 'news' ? 'class="active"' : '' ?>>
                    <i class="fas fa-newspaper"></i>
                    <span>Berita</span>
                </a>
                <a href="/crims/admin/partners.php" <?= $activePage === 'partners' ? 'class="active"' : '' ?>>
                    <i class="fas fa-handshake"></i>
                    <span>Mitra</span>
                </a>
                <a href="/crims/admin/achievements.php" <?= $activePage === 'achievements' ? 'class="active"' : '' ?>>
                    <i class="fas fa-trophy"></i>
                    <span>Prestasi</span>
                </a>
                <a href="/crims/admin/pengabdian.php" <?= $activePage === 'pengabdian' ? 'class="active"' : '' ?>>
                    <i class="fas fa-hands-helping"></i>
                    <span>Pengabdian</span>
                </a>
                <div class="dropdown" id="studentDropdown">
                    <a href="#" class="dropdown-toggle" <?= in_array($activePage, ['skripsi', 'tesis', 'disertasi']) ? 'class="dropdown-toggle active"' : 'class="dropdown-toggle"' ?>>
                        <i class="fas fa-user-graduate"></i>
                        <span>Student</span>
                    </a>
                    <div class="dropdown-menu">
                        <a href="/crims/admin/skripsi.php" <?= $activePage === 'skripsi' ? 'class="active"' : '' ?>>
                            <span>Skripsi</span>
                        </a>
                        <a href="/crims/admin/tesis.php" <?= $activePage === 'tesis' ? 'class="active"' : '' ?>>
                            <span>Tesis</span>
                        </a>
                        <a href="/crims/admin/disertasi.php" <?= $activePage === 'disertasi' ? 'class="active"' : '' ?>>
                            <span>Disertasi</span>
                        </a>
                    </div>
                </div>
                <a href="/crims/admin/tentang.php" <?= $activePage === 'tentang' ? 'class="active"' : '' ?>>
                    <i class="fas fa-info-circle"></i>
                    <span>Tentang</span>
                </a>
                <a href="/crims/admin/hilirisasi.php" <?= $activePage === 'hilirisasi' ? 'class="active"' : '' ?>>
                    <i class="fas fa-industry"></i>
                    <span>Hilirisasi</span>
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
        // Mobile menu toggle
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
        
        // Dropdown toggle for Student menu
        const studentDropdown = document.getElementById('studentDropdown');
        if (studentDropdown) {
            const dropdownToggle = studentDropdown.querySelector('.dropdown-toggle');
            if (dropdownToggle) {
                dropdownToggle.addEventListener('click', function(e) {
                    e.preventDefault();
                    studentDropdown.classList.toggle('active');
                });
            }
        }
    </script>
    
    <!-- Bootstrap 4 JS (required for Summernote BS4) -->
    <?php if (strpos($pageTitle, 'Prestasi') !== false || strpos($pageTitle, 'Achievements') !== false || strpos($content, 'id="description"') !== false || strpos($content, 'id="summary"') !== false || strpos($pageTitle, 'Proyek') !== false || strpos($pageTitle, 'Project') !== false || strpos($pageTitle, 'Hilirisasi') !== false): ?>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/js/bootstrap.bundle.min.js"></script>
    <!-- Summernote JS -->
    <script src="https://cdn.jsdelivr.net/npm/summernote@0.8.18/dist/summernote-bs4.min.js"></script>
    <script>
        // Initialize Summernote after all scripts are loaded
        window.addEventListener('load', function() {
            function initSummernote() {
                if (typeof jQuery === 'undefined') {
                    console.error('jQuery tidak ditemukan');
                    return;
                }
                if (typeof $.fn.summernote === 'undefined') {
                    console.error('Summernote plugin tidak ditemukan');
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
                                // Aggressive removal of unwanted buttons
                                function removeUnwantedButtons() {
                                    var $editor = $summary.next('.note-editor');
                                    if ($editor.length === 0) {
                                        setTimeout(removeUnwantedButtons, 50);
                                        return;
                                    }
                                    
                                    var $toolbar = $editor.find('.note-toolbar');
                                    if ($toolbar.length === 0) {
                                        setTimeout(removeUnwantedButtons, 50);
                                        return;
                                    }
                                    
                                    // Remove ALL buttons that contain link, picture, video, table, fullscreen, codeview, help icons
                                    $toolbar.find('button').each(function() {
                                        var $btn = $(this);
                                        var btnHtml = $btn.html().toLowerCase();
                                        var btnTitle = ($btn.attr('title') || '').toLowerCase();
                                        var btnDataEvent = ($btn.attr('data-event') || '').toLowerCase();
                                        var btnClass = $btn.attr('class') || '';
                                        
                                        // Check if button contains unwanted features
                                        var isUnwanted = false;
                                        
                                        // Check for link
                                        if (btnHtml.includes('fa-link') || btnHtml.includes('fa-chain') || 
                                            btnTitle.includes('link') || btnDataEvent.includes('link') ||
                                            btnClass.includes('note-link')) {
                                            isUnwanted = true;
                                        }
                                        
                                        // Check for picture/image
                                        if (btnHtml.includes('fa-image') || btnHtml.includes('fa-picture') || 
                                            btnTitle.includes('picture') || btnTitle.includes('image') || 
                                            btnDataEvent.includes('image') || btnDataEvent.includes('picture') ||
                                            btnClass.includes('note-picture')) {
                                            isUnwanted = true;
                                        }
                                        
                                        // Check for video
                                        if (btnHtml.includes('fa-video') || 
                                            btnTitle.includes('video') || btnDataEvent.includes('video') ||
                                            btnClass.includes('note-video')) {
                                            isUnwanted = true;
                                        }
                                        
                                        // Check for table
                                        if (btnHtml.includes('fa-table') || 
                                            btnTitle.includes('table') || btnDataEvent.includes('table') ||
                                            btnClass.includes('note-table')) {
                                            isUnwanted = true;
                                        }
                                        
                                        // Check for fullscreen
                                        if (btnHtml.includes('fa-expand') || btnHtml.includes('fa-arrows-alt') || 
                                            btnTitle.includes('fullscreen') || btnDataEvent.includes('fullscreen') ||
                                            btnClass.includes('note-fullscreen')) {
                                            isUnwanted = true;
                                        }
                                        
                                        // Check for codeview
                                        if (btnHtml.includes('fa-code') || btnHtml.includes('</>') || 
                                            btnTitle.includes('code') || btnDataEvent.includes('codeview') ||
                                            btnClass.includes('note-codeview')) {
                                            isUnwanted = true;
                                        }
                                        
                                        // Check for help
                                        if (btnHtml.includes('fa-question') || 
                                            btnTitle.includes('help') || btnDataEvent.includes('help') ||
                                            btnClass.includes('note-help')) {
                                            isUnwanted = true;
                                        }
                                        
                                        // Check for font family
                                        if (btnDataEvent.includes('fontname') || btnDataEvent.includes('fontname') ||
                                            btnTitle.includes('font') || btnTitle.includes('family') ||
                                            btnClass.includes('note-fontname')) {
                                            isUnwanted = true;
                                        }
                                        
                                        // Check for color picker
                                        if (btnDataEvent.includes('color') ||
                                            btnTitle.includes('color') || btnTitle.includes('text color') ||
                                            btnClass.includes('note-color')) {
                                            isUnwanted = true;
                                        }
                                        
                                        if (isUnwanted) {
                                            var $btnGroup = $btn.closest('.btn-group');
                                            if ($btnGroup.length > 0) {
                                                $btnGroup.remove();
                                            } else {
                                                $btn.remove();
                                            }
                                        }
                                    });
                                    
                                    // Also remove by direct selectors
                                    $toolbar.find('button[data-event*="link"], button[data-event*="image"], button[data-event*="picture"], button[data-event*="video"], button[data-event*="table"], button[data-event*="fullscreen"], button[data-event*="codeview"], button[data-event*="help"], button[data-event*="color"], button[data-event*="fontname"]').closest('.btn-group').remove();
                                }
                                
                                // Multiple aggressive attempts
                                setTimeout(removeUnwantedButtons, 50);
                                setTimeout(removeUnwantedButtons, 150);
                                setTimeout(removeUnwantedButtons, 300);
                                setTimeout(removeUnwantedButtons, 500);
                                setTimeout(removeUnwantedButtons, 1000);
                                setTimeout(removeUnwantedButtons, 2000);
                                
                                // Continuous monitoring with MutationObserver
                                setTimeout(function() {
                                    var $editor = $summary.next('.note-editor');
                                    if ($editor.length > 0) {
                                        var observer = new MutationObserver(function() {
                                            removeUnwantedButtons();
                                        });
                                        observer.observe($editor[0], {
                                            childList: true,
                                            subtree: true,
                                            attributes: true
                                        });
                                    }
                                }, 100);
                            }
                        },
                        callbacks: {
                            onInit: function() {
                                // Aggressive removal of unwanted buttons
                                function removeUnwantedButtons() {
                                    var $editor = $summary.next('.note-editor');
                                    if ($editor.length === 0) {
                                        setTimeout(removeUnwantedButtons, 50);
                                        return;
                                    }
                                    
                                    var $toolbar = $editor.find('.note-toolbar');
                                    if ($toolbar.length === 0) {
                                        setTimeout(removeUnwantedButtons, 50);
                                        return;
                                    }
                                    
                                    // Remove ALL buttons that contain link, picture, video, table, fullscreen, codeview, help icons
                                    $toolbar.find('button').each(function() {
                                        var $btn = $(this);
                                        var btnHtml = $btn.html().toLowerCase();
                                        var btnTitle = ($btn.attr('title') || '').toLowerCase();
                                        var btnDataEvent = ($btn.attr('data-event') || '').toLowerCase();
                                        var btnClass = $btn.attr('class') || '';
                                        
                                        // Check if button contains unwanted features
                                        var isUnwanted = false;
                                        
                                        // Check for link
                                        if (btnHtml.includes('fa-link') || btnHtml.includes('fa-chain') || 
                                            btnTitle.includes('link') || btnDataEvent.includes('link') ||
                                            btnClass.includes('note-link')) {
                                            isUnwanted = true;
                                        }
                                        
                                        // Check for picture/image
                                        if (btnHtml.includes('fa-image') || btnHtml.includes('fa-picture') || 
                                            btnTitle.includes('picture') || btnTitle.includes('image') || 
                                            btnDataEvent.includes('image') || btnDataEvent.includes('picture') ||
                                            btnClass.includes('note-picture')) {
                                            isUnwanted = true;
                                        }
                                        
                                        // Check for video
                                        if (btnHtml.includes('fa-video') || 
                                            btnTitle.includes('video') || btnDataEvent.includes('video') ||
                                            btnClass.includes('note-video')) {
                                            isUnwanted = true;
                                        }
                                        
                                        // Check for table
                                        if (btnHtml.includes('fa-table') || 
                                            btnTitle.includes('table') || btnDataEvent.includes('table') ||
                                            btnClass.includes('note-table')) {
                                            isUnwanted = true;
                                        }
                                        
                                        // Check for fullscreen
                                        if (btnHtml.includes('fa-expand') || btnHtml.includes('fa-arrows-alt') || 
                                            btnTitle.includes('fullscreen') || btnDataEvent.includes('fullscreen') ||
                                            btnClass.includes('note-fullscreen')) {
                                            isUnwanted = true;
                                        }
                                        
                                        // Check for codeview
                                        if (btnHtml.includes('fa-code') || btnHtml.includes('</>') || 
                                            btnTitle.includes('code') || btnDataEvent.includes('codeview') ||
                                            btnClass.includes('note-codeview')) {
                                            isUnwanted = true;
                                        }
                                        
                                        // Check for help
                                        if (btnHtml.includes('fa-question') || 
                                            btnTitle.includes('help') || btnDataEvent.includes('help') ||
                                            btnClass.includes('note-help')) {
                                            isUnwanted = true;
                                        }
                                        
                                        // Check for font family
                                        if (btnDataEvent.includes('fontname') || btnDataEvent.includes('fontname') ||
                                            btnTitle.includes('font') || btnTitle.includes('family') ||
                                            btnClass.includes('note-fontname')) {
                                            isUnwanted = true;
                                        }
                                        
                                        // Check for color picker
                                        if (btnDataEvent.includes('color') ||
                                            btnTitle.includes('color') || btnTitle.includes('text color') ||
                                            btnClass.includes('note-color')) {
                                            isUnwanted = true;
                                        }
                                        
                                        if (isUnwanted) {
                                            var $btnGroup = $btn.closest('.btn-group');
                                            if ($btnGroup.length > 0) {
                                                $btnGroup.remove();
                                            } else {
                                                $btn.remove();
                                            }
                                        }
                                    });
                                    
                                    // Also remove by direct selectors
                                    $toolbar.find('button[data-event*="link"], button[data-event*="image"], button[data-event*="picture"], button[data-event*="video"], button[data-event*="table"], button[data-event*="fullscreen"], button[data-event*="codeview"], button[data-event*="help"], button[data-event*="color"], button[data-event*="fontname"]').closest('.btn-group').remove();
                                }
                                
                                // Multiple aggressive attempts
                                setTimeout(removeUnwantedButtons, 50);
                                setTimeout(removeUnwantedButtons, 150);
                                setTimeout(removeUnwantedButtons, 300);
                                setTimeout(removeUnwantedButtons, 500);
                                setTimeout(removeUnwantedButtons, 1000);
                                setTimeout(removeUnwantedButtons, 2000);
                                
                                // Continuous monitoring with MutationObserver
                                setTimeout(function() {
                                    var $editor = $summary.next('.note-editor');
                                    if ($editor.length > 0) {
                                        var observer = new MutationObserver(function() {
                                            removeUnwantedButtons();
                                        });
                                        observer.observe($editor[0], {
                                            childList: true,
                                            subtree: true,
                                            attributes: true
                                        });
                                    }
                                }, 100);
                            }
                        },
                        placeholder: 'Tulis ringkasan proyek di sini...',
                        lang: 'id-ID',
                        fontNames: [
                            'Arial', 'Arial Black', 'Comic Sans MS', 'Courier New', 
                            'Georgia', 'Helvetica', 'Impact', 'Lucida Console', 
                            'Lucida Sans Unicode', 'Palatino Linotype', 'Tahoma', 
                            'Times New Roman', 'Trebuchet MS', 'Verdana',
                            'Roboto', 'Open Sans', 'Lato', 'Montserrat', 'Poppins',
                            'Raleway', 'Ubuntu', 'Playfair Display', 'Merriweather',
                            'Oswald', 'Source Sans Pro', 'PT Sans', 'Lora'
                        ],
                        fontSizes: ['8', '9', '10', '11', '12', '14', '16', '18', '20', '22', '24', '28', '32', '36', '48', '72'],
                        colors: [
                            ['#000000', 'Hitam'],
                            ['#434343', 'Abu-abu Gelap'],
                            ['#666666', 'Abu-abu'],
                            ['#999999', 'Abu-abu Terang'],
                            ['#b7b7b7', 'Abu-abu Muda'],
                            ['#cccccc', 'Abu-abu Sangat Muda'],
                            ['#d9d9d9', 'Abu-abu Pucat'],
                            ['#efefef', 'Abu-abu Sangat Pucat'],
                            ['#f3f3f3', 'Abu-abu Putih'],
                            ['#ffffff', 'Putih'],
                            ['#980000', 'Merah Gelap'],
                            ['#ff0000', 'Merah'],
                            ['#ff9900', 'Jingga'],
                            ['#ffff00', 'Kuning'],
                            ['#00ff00', 'Hijau'],
                            ['#00ffff', 'Cyan'],
                            ['#4a86e8', 'Biru'],
                            ['#0000ff', 'Biru Gelap'],
                            ['#9900ff', 'Ungu'],
                            ['#ff00ff', 'Magenta'],
                            ['#e6b8af', 'Merah Muda'],
                            ['#f4cccc', 'Merah Muda Terang'],
                            ['#fce5cd', 'Krem'],
                            ['#fff2cc', 'Kuning Muda'],
                            ['#d9ead3', 'Hijau Muda'],
                            ['#d0e0e3', 'Biru Muda'],
                            ['#c9daf8', 'Biru Langit'],
                            ['#cfe2f3', 'Biru Pucat'],
                            ['#d9d2e9', 'Ungu Muda'],
                            ['#ead1dc', 'Pink'],
                            ['#ff6b6b', 'Merah Terang'],
                            ['#4ecdc4', 'Turquoise'],
                            ['#45b7d1', 'Biru Terang'],
                            ['#f7b731', 'Kuning Emas'],
                            ['#5f27cd', 'Ungu Gelap'],
                            ['#00d2d3', 'Cyan Terang'],
                            ['#ff9ff3', 'Pink Terang'],
                            ['#54a0ff', 'Biru Langit Terang'],
                            ['#5f27cd', 'Ungu'],
                            ['#c44569', 'Merah Muda Gelap']
                        ]
                    });
                    console.log('Summernote initialized untuk #summary');
                }
                
                // Initialize for description textarea (if exists)
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
                                // Add character counter
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
                                
                                // Update on input
                                $editable.on('input keyup paste', function() {
                                    setTimeout(updateCharCounter, 10);
                                });
                                
                                // Initial count
                                setTimeout(updateCharCounter, 100);
                                
                                // Aggressive removal of unwanted buttons
                                function removeUnwantedButtons() {
                                    var $editor = $description.next('.note-editor');
                                    if ($editor.length === 0) {
                                        setTimeout(removeUnwantedButtons, 50);
                                        return;
                                    }
                                    
                                    var $toolbar = $editor.find('.note-toolbar');
                                    if ($toolbar.length === 0) {
                                        setTimeout(removeUnwantedButtons, 50);
                                        return;
                                    }
                                    
                                    // Remove ALL buttons that contain link, picture, video, table, fullscreen, codeview, help icons
                                    $toolbar.find('button').each(function() {
                                        var $btn = $(this);
                                        var btnHtml = $btn.html().toLowerCase();
                                        var btnTitle = ($btn.attr('title') || '').toLowerCase();
                                        var btnDataEvent = ($btn.attr('data-event') || '').toLowerCase();
                                        var btnClass = $btn.attr('class') || '';
                                        
                                        // Check if button contains unwanted features
                                        var isUnwanted = false;
                                        
                                        // Check for link
                                        if (btnHtml.includes('fa-link') || btnHtml.includes('fa-chain') || 
                                            btnTitle.includes('link') || btnDataEvent.includes('link') ||
                                            btnClass.includes('note-link')) {
                                            isUnwanted = true;
                                        }
                                        
                                        // Check for picture/image
                                        if (btnHtml.includes('fa-image') || btnHtml.includes('fa-picture') || 
                                            btnTitle.includes('picture') || btnTitle.includes('image') || 
                                            btnDataEvent.includes('image') || btnDataEvent.includes('picture') ||
                                            btnClass.includes('note-picture')) {
                                            isUnwanted = true;
                                        }
                                        
                                        // Check for video
                                        if (btnHtml.includes('fa-video') || 
                                            btnTitle.includes('video') || btnDataEvent.includes('video') ||
                                            btnClass.includes('note-video')) {
                                            isUnwanted = true;
                                        }
                                        
                                        // Check for table
                                        if (btnHtml.includes('fa-table') || 
                                            btnTitle.includes('table') || btnDataEvent.includes('table') ||
                                            btnClass.includes('note-table')) {
                                            isUnwanted = true;
                                        }
                                        
                                        // Check for fullscreen
                                        if (btnHtml.includes('fa-expand') || btnHtml.includes('fa-arrows-alt') || 
                                            btnTitle.includes('fullscreen') || btnDataEvent.includes('fullscreen') ||
                                            btnClass.includes('note-fullscreen')) {
                                            isUnwanted = true;
                                        }
                                        
                                        // Check for codeview
                                        if (btnHtml.includes('fa-code') || btnHtml.includes('</>') || 
                                            btnTitle.includes('code') || btnDataEvent.includes('codeview') ||
                                            btnClass.includes('note-codeview')) {
                                            isUnwanted = true;
                                        }
                                        
                                        // Check for help
                                        if (btnHtml.includes('fa-question') || 
                                            btnTitle.includes('help') || btnDataEvent.includes('help') ||
                                            btnClass.includes('note-help')) {
                                            isUnwanted = true;
                                        }
                                        
                                        // Check for font family
                                        if (btnDataEvent.includes('fontname') || btnDataEvent.includes('fontname') ||
                                            btnTitle.includes('font') || btnTitle.includes('family') ||
                                            btnClass.includes('note-fontname')) {
                                            isUnwanted = true;
                                        }
                                        
                                        // Check for color picker
                                        if (btnDataEvent.includes('color') ||
                                            btnTitle.includes('color') || btnTitle.includes('text color') ||
                                            btnClass.includes('note-color')) {
                                            isUnwanted = true;
                                        }
                                        
                                        if (isUnwanted) {
                                            var $btnGroup = $btn.closest('.btn-group');
                                            if ($btnGroup.length > 0) {
                                                $btnGroup.remove();
                                            } else {
                                                $btn.remove();
                                            }
                                        }
                                    });
                                    
                                    // Also remove by direct selectors
                                    $toolbar.find('button[data-event*="link"], button[data-event*="image"], button[data-event*="picture"], button[data-event*="video"], button[data-event*="table"], button[data-event*="fullscreen"], button[data-event*="codeview"], button[data-event*="help"], button[data-event*="color"], button[data-event*="fontname"]').closest('.btn-group').remove();
                                }
                                
                                // Multiple aggressive attempts
                                setTimeout(removeUnwantedButtons, 50);
                                setTimeout(removeUnwantedButtons, 150);
                                setTimeout(removeUnwantedButtons, 300);
                                setTimeout(removeUnwantedButtons, 500);
                                setTimeout(removeUnwantedButtons, 1000);
                                setTimeout(removeUnwantedButtons, 2000);
                                
                                // Continuous monitoring with MutationObserver
                                setTimeout(function() {
                                    var $editor = $description.next('.note-editor');
                                    if ($editor.length > 0) {
                                        var observer = new MutationObserver(function() {
                                            removeUnwantedButtons();
                                        });
                                        observer.observe($editor[0], {
                                            childList: true,
                                            subtree: true,
                                            attributes: true
                                        });
                                    }
                                }, 100);
                            }
                        },
                        placeholder: 'Tulis deskripsi di sini...',
                        lang: 'id-ID',
                        fontNames: [
                            'Arial', 'Arial Black', 'Comic Sans MS', 'Courier New', 
                            'Georgia', 'Helvetica', 'Impact', 'Lucida Console', 
                            'Lucida Sans Unicode', 'Palatino Linotype', 'Tahoma', 
                            'Times New Roman', 'Trebuchet MS', 'Verdana',
                            'Roboto', 'Open Sans', 'Lato', 'Montserrat', 'Poppins',
                            'Raleway', 'Ubuntu', 'Playfair Display', 'Merriweather',
                            'Oswald', 'Source Sans Pro', 'PT Sans', 'Lora'
                        ],
                        fontSizes: ['8', '9', '10', '11', '12', '14', '16', '18', '20', '22', '24', '28', '32', '36', '48', '72'],
                        colors: [
                            ['#000000', 'Hitam'],
                            ['#434343', 'Abu-abu Gelap'],
                            ['#666666', 'Abu-abu'],
                            ['#999999', 'Abu-abu Terang'],
                            ['#b7b7b7', 'Abu-abu Muda'],
                            ['#cccccc', 'Abu-abu Sangat Muda'],
                            ['#d9d9d9', 'Abu-abu Pucat'],
                            ['#efefef', 'Abu-abu Sangat Pucat'],
                            ['#f3f3f3', 'Abu-abu Putih'],
                            ['#ffffff', 'Putih'],
                            ['#980000', 'Merah Gelap'],
                            ['#ff0000', 'Merah'],
                            ['#ff9900', 'Jingga'],
                            ['#ffff00', 'Kuning'],
                            ['#00ff00', 'Hijau'],
                            ['#00ffff', 'Cyan'],
                            ['#4a86e8', 'Biru'],
                            ['#0000ff', 'Biru Gelap'],
                            ['#9900ff', 'Ungu'],
                            ['#ff00ff', 'Magenta'],
                            ['#e6b8af', 'Merah Muda'],
                            ['#f4cccc', 'Merah Muda Terang'],
                            ['#fce5cd', 'Krem'],
                            ['#fff2cc', 'Kuning Muda'],
                            ['#d9ead3', 'Hijau Muda'],
                            ['#d0e0e3', 'Biru Muda'],
                            ['#c9daf8', 'Biru Langit'],
                            ['#cfe2f3', 'Biru Pucat'],
                            ['#d9d2e9', 'Ungu Muda'],
                            ['#ead1dc', 'Pink'],
                            ['#ff6b6b', 'Merah Terang'],
                            ['#4ecdc4', 'Turquoise'],
                            ['#45b7d1', 'Biru Terang'],
                            ['#f7b731', 'Kuning Emas'],
                            ['#5f27cd', 'Ungu Gelap'],
                            ['#00d2d3', 'Cyan Terang'],
                            ['#ff9ff3', 'Pink Terang'],
                            ['#54a0ff', 'Biru Langit Terang'],
                            ['#5f27cd', 'Ungu'],
                            ['#c44569', 'Merah Muda Gelap']
                        ]
                    });
                    console.log('Summernote initialized untuk #description');
                }
            }
            
            // Wait a bit to ensure all scripts are loaded
            setTimeout(initSummernote, 200);
        });
    </script>
    <?php endif; ?>
</body>
</html>
        <?php
    }
}
?>

