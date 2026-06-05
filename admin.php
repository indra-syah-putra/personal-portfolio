<?php
require_once 'includes/functions.php';
cek_admin();

 $flash = get_flash();
session_write_close();
 $data = baca_json();
if ($data === false) { die('Gagal membaca data JSON.'); }
 $settings = $data['site_settings'];
 $projects = $data['projects'] ?? [];
 $articles = $data['articles'] ?? [];
 $messages = baca_messages();
 $unread_count = count(array_filter($messages, function($m) { return empty($m['is_read']); }));

// One-time migration: move messages from data.json to messages.json
if (empty($messages) && !empty($data['messages'])) {
    $messages = $data['messages'];
    tulis_messages($messages);
    unset($data['messages']);
    tulis_json($data);
    $unread_count = count(array_filter($messages, function($m) { return empty($m['is_read']); }));
}
 $categories = $settings['categories'] ?? [];
 $skills = $settings['skills'] ?? [];

 $page = $_GET['page'] ?? 'dashboard';
 $sub_skill = ($page == 'skills') ? ($_GET['sub'] ?? 'beranda') : '';
 $sub_proj = ($page == 'projects') ? ($_GET['sub'] ?? 'semua') : '';
 $sub_art = ($page == 'articles') ? ($_GET['sub'] ?? 'semua') : '';

// Mark all messages as read when viewing messages page
if ($page === 'messages' && $unread_count > 0) {
    $messages_original = $messages;
    foreach ($messages as &$m) {
        $m['is_read'] = true;
    }
    tulis_messages($messages);
    // Keep original for this page load so unread styling still shows
    $messages = $messages_original;
    $unread_count = 0;
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin CMS - Myportofolio</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="assets/vendor/trumbowyg/ui/trumbowyg.min.css">
    <style>
        * { box-sizing: border-box; margin: 0; padding: 0; }
        body {
            font-family: 'Inter', -apple-system, sans-serif;
            background: #f1f5f9;
            margin: 0; display: flex; min-height: 100vh; overflow-x: hidden;
            color: #0f172a;
        }

        /* ============ SIDEBAR ============ */
        .sidebar {
            width: 260px;
            background: #0f172a;
            display: flex; flex-direction: column;
            position: fixed; height: 100%; left: 0; top: 0;
            z-index: 1000;
            transition: left 0.3s ease;
        }
        .sidebar-brand {
            display: flex; align-items: center; gap: 10px;
            padding: 1.25rem 1.25rem 1rem;
            color: #f1f5f9;
            font-size: 1.15rem; font-weight: 700;
            letter-spacing: -0.3px;
        }
        .sidebar-brand svg { color: #3b82f6; flex-shrink: 0; }
        .sidebar-divider {
            border: none; border-top: 1px solid #1e293b;
            margin: 0 1rem 0.75rem;
        }
        .sidebar-nav { flex: 1; overflow-y: auto; padding: 0 0.75rem; }
        .sidebar-link {
            display: flex; align-items: center; gap: 10px;
            padding: 0.6rem 0.85rem;
            color: #64748b; text-decoration: none;
            border-radius: 8px;
            font-size: 0.875rem; font-weight: 500;
            transition: all 0.15s ease;
            margin-bottom: 2px;
        }
        .sidebar-link svg { flex-shrink: 0; opacity: 0.7; }
        .sidebar-link:hover {
            background: #1e293b; color: #e2e8f0;
        }
        .sidebar-link:hover svg { opacity: 1; }
        .sidebar-link.active {
            background: #2563eb; color: white;
        }
        .sidebar-link.active svg { opacity: 1; }
        .sidebar-footer {
            padding: 0.5rem 0.75rem;
            border-top: 1px solid #1e293b;
        }
        .sidebar-footer .sidebar-link {
            justify-content: center;
            color: #ef4444;
        }
        .sidebar-footer .sidebar-link:hover {
            background: rgba(239,68,68,0.1); color: #fca5a5;
        }

        /* ============ MAIN CONTENT ============ */
        .main-content {
            margin-left: 260px; flex: 1;
            min-height: 100vh;
            display: flex; flex-direction: column;
        }

        /* ============ TOPBAR ============ */
        .topbar {
            background: white;
            padding: 0 2rem;
            height: 60px;
            display: flex; align-items: center; gap: 1rem;
            border-bottom: 1px solid #e2e8f0;
            position: sticky; top: 0; z-index: 100;
        }
        .sidebar-toggle {
            display: none; background: none; border: none;
            cursor: pointer; color: #64748b; padding: 4px;
            border-radius: 6px; transition: background 0.2s;
        }
        .sidebar-toggle:hover { background: #f1f5f9; }
        .topbar-title { flex: 1; }
        .topbar-title h1 {
            font-size: 1.1rem; font-weight: 600; color: #0f172a; margin: 0;
        }
        .topbar-right { display: flex; align-items: center; gap: 1rem; }
        .topbar-user {
            font-size: 0.85rem; font-weight: 600; color: #475569;
            padding: 4px 12px; background: #f1f5f9; border-radius: 20px;
        }
        .topbar-logout {
            color: #94a3b8; transition: color 0.2s;
            display: flex; padding: 4px;
        }
        .topbar-logout:hover { color: #ef4444; }

        /* ============ PAGE CONTENT ============ */
        .page-content {
            padding: 1.5rem 2rem 3rem;
            flex: 1;
        }
        .page-content h1 {
            font-size: 1.5rem; font-weight: 700; color: #0f172a;
            letter-spacing: -0.3px;
        }

        /* ============ CARDS ============ */
        .card {
            background: white;
            padding: 1.5rem;
            border-radius: 12px;
            box-shadow: 0 1px 3px rgba(0,0,0,0.04);
            margin-bottom: 1.5rem;
            border: 1px solid #f1f5f9;
        }
        .card-table { padding: 0; overflow: hidden; }

        /* ============ STAT CARDS ============ */
        .dashboard-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 1.25rem; margin-bottom: 1rem; }
        .stat-card {
            background: white; padding: 1.25rem 1.5rem;
            border-radius: 12px; box-shadow: 0 1px 3px rgba(0,0,0,0.04);
            border: 1px solid #f1f5f9;
            display: flex; align-items: center; gap: 1rem;
            transition: box-shadow 0.2s;
        }
        .stat-card:hover { box-shadow: 0 4px 12px rgba(0,0,0,0.06); }
        .stat-icon {
            width: 44px; height: 44px; border-radius: 10px;
            display: flex; align-items: center; justify-content: center;
            flex-shrink: 0;
        }
        .stat-icon-blue { background: #eff6ff; color: #2563eb; }
        .stat-icon-red { background: #fef2f2; color: #ef4444; }
        .stat-info h3 {
            font-size: 0.8rem; font-weight: 600; color: #94a3b8;
            text-transform: uppercase; letter-spacing: 0.5px; margin: 0;
        }
        .stat-number {
            font-size: 1.5rem; font-weight: 700; color: #0f172a;
            margin: 0; line-height: 1.3;
        }

        /* ============ FORM ============ */
        .form-group { margin-bottom: 1.25rem; }
        .form-group label {
            display: block; margin-bottom: 0.35rem;
            font-weight: 600; color: #334155; font-size: 0.85rem;
        }
        .form-control {
            width: 100%; padding: 0.65rem 0.85rem;
            border: 1px solid #e2e8f0; border-radius: 8px;
            box-sizing: border-box; font-family: inherit;
            font-size: 0.9rem; transition: all 0.2s;
            background: #fff;
        }
        .form-control:focus {
            border-color: #2563eb; outline: none;
            box-shadow: 0 0 0 3px rgba(37,99,235,0.1);
        }

        /* ============ BUTTONS ============ */
        .btn {
            padding: 0.55rem 1.2rem; border: none; border-radius: 8px;
            cursor: pointer; font-weight: 600; font-size: 0.85rem;
            text-decoration: none; display: inline-flex; align-items: center;
            justify-content: center; gap: 6px;
            transition: all 0.15s ease; font-family: inherit;
        }
        .btn:active { transform: scale(0.97); }
        .btn-primary { background: #2563eb; color: white; }
        .btn-primary:hover { background: #1d4ed8; }
        .btn-danger { background: #ef4444; color: white; }
        .btn-danger:hover { background: #dc2626; }
        .btn-secondary { background: #f1f5f9; color: #475569; }
        .btn-secondary:hover { background: #e2e8f0; }
        .btn-sm { padding: 0.35rem 0.8rem; font-size: 0.8rem; border-radius: 6px; }

        /* ============ TABLE ============ */
        .table-wrap { overflow-x: auto; }
        table { width: 100%; border-collapse: collapse; font-size: 0.875rem; }
        th {
            padding: 0.75rem 1rem; text-align: left;
            font-weight: 600; color: #64748b;
            font-size: 0.75rem; text-transform: uppercase; letter-spacing: 0.5px;
            background: #f8fafc; border-bottom: 1px solid #e2e8f0;
        }
        td {
            padding: 0.75rem 1rem; border-bottom: 1px solid #f1f5f9;
            color: #334155;
        }
        tr:last-child td { border-bottom: none; }
        tr:hover td { background: #fafbfc; }

        /* ============ BADGES ============ */
        .badge { display: inline-block; padding: 2px 10px; border-radius: 20px; font-size: 0.75rem; font-weight: 600; }
        .badge-blue { background: #dbeafe; color: #1e40af; }
        .badge-green { background: #dcfce7; color: #15803d; }
        .badge-yellow { background: #fef3c7; color: #92400e; }
        .badge-purple { background: #ede9fe; color: #6d28d9; }
        .badge-red { background: #fef2f2; color: #dc2626; }

        /* ============ STATUS BADGE ============ */
        .status-badge { padding: 3px 12px; border-radius: 20px; font-size: 0.7rem; font-weight: 700; text-transform: uppercase; letter-spacing: 0.5px; display: inline-block; }
        .status-completed { background: #dcfce7; color: #15803d; }
        .status-progress { background: #dbeafe; color: #1e40af; }
        .status-planning { background: #fef3c7; color: #92400e; }

        /* ============ PAGE HEADER ============ */
        .page-header {
            display: flex; justify-content: space-between;
            align-items: center; margin-bottom: 1.5rem;
        }
        .page-header h1 { margin: 0; }

        /* ============ TOAST ============ */
        .toast {
            position: fixed; top: -80px; left: 50%; transform: translateX(-50%);
            z-index: 9999; display: flex; align-items: center; gap: 10px;
            padding: 12px 20px; border-radius: 10px;
            font-weight: 600; font-size: 0.85rem;
            box-shadow: 0 10px 30px rgba(0,0,0,0.1);
            transition: top 0.4s cubic-bezier(0.22, 1, 0.36, 1);
            max-width: 460px;
        }
        .toast-show { top: 20px; }
        .toast-success { background: #065f46; color: #d1fae5; }
        .toast-warning { background: #92400e; color: #fef3c7; }
        .toast-close { background: none; border: none; color: inherit; font-size: 1.3rem; cursor: pointer; opacity: 0.7; margin-left: auto; padding: 0 4px; line-height: 1; }
        .toast-close:hover { opacity: 1; }

        /* ============ SIDEBAR SUBMENU ============ */
        .sidebar-sub {
            list-style: none;
            padding: 0;
            margin: 0 0 2px 0;
            overflow: hidden;
            max-height: 0;
            transition: max-height 0.3s ease;
        }
        .sidebar-sub.open {
            max-height: 300px;
        }
        .sidebar-sub li a {
            display: flex;
            align-items: center;
            gap: 10px;
            padding: 0.45rem 0.85rem 0.45rem 2.8rem;
            color: #64748b;
            text-decoration: none;
            border-radius: 6px;
            font-size: 0.82rem;
            font-weight: 500;
            transition: all 0.15s ease;
            margin: 1px 0;
        }
        .sidebar-sub li a:hover {
            background: #1e293b;
            color: #e2e8f0;
        }
        .sidebar-sub li a.active {
            background: #2563eb;
            color: white;
        }
        .sidebar-link.has-sub {
            cursor: pointer;
            display: flex;
            align-items: center;
            justify-content: space-between;
        }
        .sidebar-link .sub-arrow {
            font-size: 0.7rem;
            transition: transform 0.2s ease;
            opacity: 0.5;
        }
        .sidebar-link .sub-arrow.open {
            transform: rotate(90deg);
        }

        /* ============ RESPONSIVE ============ */
        .overlay {
            position: fixed; top: 0; left: 0; width: 100%; height: 100%;
            background: rgba(15,23,42,0.5); z-index: 999;
            display: none; backdrop-filter: blur(4px);
        }
        @media (max-width: 768px) {
            .sidebar { left: -280px; }
            .sidebar.active { left: 0; }
            .sidebar-toggle { display: block; }
            .main-content { margin-left: 0; }
            .page-content { padding: 1rem; }
            .dashboard-grid { grid-template-columns: 1fr 1fr !important; }
        }
        @media (max-width: 480px) {
            .dashboard-grid { grid-template-columns: 1fr !important; }
        }
    </style>
</head>
<body>

    <!-- SIDEBAR OVERLAY -->
    <div class="overlay" id="overlay" onclick="toggleSidebar()"></div>

    <!-- SIDEBAR -->
    <aside class="sidebar" id="sidebar">
        <div class="sidebar-brand">
            <svg width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 2L2 7l10 5 10-5-10-5z"></path><path d="M2 17l10 5 10-5"></path><path d="M2 12l10 5 10-5"></path></svg>
            <span>Myportofolio</span>
        </div>
        <hr class="sidebar-divider">
        <nav class="sidebar-nav">
            <a href="?page=dashboard" class="sidebar-link <?= $page == 'dashboard' ? 'active' : '' ?>" onclick="closeSidebar()">
                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="3" width="7" height="7"></rect><rect x="14" y="3" width="7" height="7"></rect><rect x="14" y="14" width="7" height="7"></rect><rect x="3" y="14" width="7" height="7"></rect></svg>
                Dashboard
            </a>

            <!-- KONTEN -->
            <div>
                <div class="sidebar-link has-sub <?= $page == 'skills' ? 'active' : '' ?>" onclick="toggleSubmenu('subSkills')">
                    <span style="display:flex;align-items:center;gap:10px;">
                        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="3" width="18" height="18" rx="2" ry="2"></rect><line x1="3" y1="9" x2="21" y2="9"></line><line x1="9" y1="21" x2="9" y2="9"></line></svg>
                        Konten
                    </span>
                    <span class="sub-arrow" id="arrowSkills">&#9654;</span>
                </div>
                <ul class="sidebar-sub <?= $page == 'skills' ? 'open' : '' ?>" id="subSkills">
                    <li><a href="?page=skills&sub=beranda" class="<?= ($page == 'skills' && ($sub_skill ?? 'beranda') == 'beranda') ? 'active' : '' ?>" onclick="closeSidebar()">Edit Beranda</a></li>
                    <li><a href="?page=skills&sub=profil" class="<?= ($page == 'skills' && ($sub_skill ?? '') == 'profil') ? 'active' : '' ?>" onclick="closeSidebar()">Profil</a></li>
                    <li><a href="?page=skills&sub=skill" class="<?= ($page == 'skills' && ($sub_skill ?? '') == 'skill') ? 'active' : '' ?>" onclick="closeSidebar()">Daftar Skill</a></li>
                    <li><a href="?page=skills&sub=timeline" class="<?= ($page == 'skills' && ($sub_skill ?? '') == 'timeline') ? 'active' : '' ?>" onclick="closeSidebar()">Timeline</a></li>
                    <li><a href="?page=skills&sub=gallery" class="<?= ($page == 'skills' && ($sub_skill ?? '') == 'gallery') ? 'active' : '' ?>" onclick="closeSidebar()">Galeri</a></li>
                </ul>
            </div>

            <!-- PROJEK -->
            <div>
                <div class="sidebar-link has-sub <?= $page == 'projects' ? 'active' : '' ?>" onclick="toggleSubmenu('subProjects')">
                    <span style="display:flex;align-items:center;gap:10px;">
                        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="2" y="3" width="20" height="14" rx="2" ry="2"></rect><line x1="8" y1="21" x2="16" y2="21"></line><line x1="12" y1="17" x2="12" y2="21"></line></svg>
                        Projek
                    </span>
                    <span class="sub-arrow" id="arrowProjects">&#9654;</span>
                </div>
                <ul class="sidebar-sub <?= $page == 'projects' ? 'open' : '' ?>" id="subProjects">
                    <li><a href="?page=projects&sub=semua" class="<?= ($page == 'projects' && ($sub_proj ?? 'semua') == 'semua') ? 'active' : '' ?>" onclick="closeSidebar()">Kelola Projek</a></li>
                    <li><a href="?page=projects&sub=kategori" class="<?= ($page == 'projects' && ($sub_proj ?? '') == 'kategori') ? 'active' : '' ?>" onclick="closeSidebar()">Kategori</a></li>
                    <li><a href="?page=projects&sub=tech" class="<?= ($page == 'projects' && ($sub_proj ?? '') == 'tech') ? 'active' : '' ?>" onclick="closeSidebar()">Tech Skill</a></li>
                </ul>
            </div>

            <!-- ARTIKEL -->
            <div>
                <div class="sidebar-link has-sub <?= $page == 'articles' ? 'active' : '' ?>" onclick="toggleSubmenu('subArticles')">
                    <span style="display:flex;align-items:center;gap:10px;">
                        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"></path><polyline points="14 2 14 8 20 8"></polyline><line x1="16" y1="13" x2="8" y2="13"></line><line x1="16" y1="17" x2="8" y2="17"></line></svg>
                        Artikel
                    </span>
                    <span class="sub-arrow" id="arrowArticles">&#9654;</span>
                </div>
                <ul class="sidebar-sub <?= $page == 'articles' ? 'open' : '' ?>" id="subArticles">
                    <li><a href="?page=articles&sub=semua" class="<?= ($page == 'articles' && ($sub_art ?? 'semua') == 'semua') ? 'active' : '' ?>" onclick="closeSidebar()">Kelola Artikel</a></li>
                    <li><a href="?page=articles&sub=kategori" class="<?= ($page == 'articles' && ($sub_art ?? '') == 'kategori') ? 'active' : '' ?>" onclick="closeSidebar()">Kategori</a></li>
                </ul>
            </div>

            <a href="?page=contact" class="sidebar-link <?= $page == 'contact' ? 'active' : '' ?>" onclick="closeSidebar()">
                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2z"></path><polyline points="22,6 12,13 2,6"></polyline></svg>
                Kontak
            </a>
            <a href="?page=messages" class="sidebar-link <?= $page == 'messages' ? 'active' : '' ?>" onclick="closeSidebar()" style="position:relative;">
                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"></path></svg>
                Pesan Masuk
                <?php if ($unread_count > 0): ?>
                <span style="position:absolute; right:10px; background:#ef4444; color:white; font-size:0.7rem; font-weight:700; padding:2px 7px; border-radius:10px; line-height:1.3;"><?= $unread_count ?></span>
                <?php endif; ?>
            </a>
            <a href="?page=media" class="sidebar-link <?= $page == 'media' ? 'active' : '' ?>" onclick="closeSidebar()">
                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="3" width="18" height="18" rx="2" ry="2"></rect><circle cx="8.5" cy="8.5" r="1.5"></circle><polyline points="21 15 16 10 5 21"></polyline></svg>
                Media
            </a>
            <a href="?page=settings&sub=projek" class="sidebar-link <?= $page == 'settings' ? 'active' : '' ?>" onclick="closeSidebar()">
                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="3"></circle><path d="M19.4 15a1.65 1.65 0 0 0 .33 1.82l.06.06a2 2 0 0 1 0 2.83 2 2 0 0 1-2.83 0l-.06-.06a1.65 1.65 0 0 0-1.82-.33 1.65 1.65 0 0 0-1 1.51V21a2 2 0 0 1-2 2 2 2 0 0 1-2-2v-.09A1.65 1.65 0 0 0 9 19.4a1.65 1.65 0 0 0-1.82.33l-.06.06a2 2 0 0 1-2.83 0 2 2 0 0 1 0-2.83l.06-.06A1.65 1.65 0 0 0 4.68 15a1.65 1.65 0 0 0-1.51-1H3a2 2 0 0 1-2-2 2 2 0 0 1 2-2h.09A1.65 1.65 0 0 0 4.6 9a1.65 1.65 0 0 0-.33-1.82l-.06-.06a2 2 0 0 1 0-2.83 2 2 0 0 1 2.83 0l.06.06A1.65 1.65 0 0 0 9 4.68a1.65 1.65 0 0 0 1-1.51V3a2 2 0 0 1 2-2 2 2 0 0 1 2 2v.09a1.65 1.65 0 0 0 1 1.51 1.65 1.65 0 0 0 1.82-.33l.06-.06a2 2 0 0 1 2.83 0 2 2 0 0 1 0 2.83l-.06.06A1.65 1.65 0 0 0 19.4 9a1.65 1.65 0 0 0 1.51 1H21a2 2 0 0 1 2 2 2 2 0 0 1-2 2h-.09a1.65 1.65 0 0 0-1.51 1z"></path></svg>
                Pengaturan
            </a>
        </nav>
        <div class="sidebar-footer">
            <span style="display:flex; align-items:center; justify-content:center; gap:8px; padding:0.6rem 0.85rem; color:#22c55e; font-size:0.875rem; font-weight:500;">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"></path><circle cx="12" cy="7" r="4"></circle></svg>
                <?= htmlspecialchars(ADMIN_USER) ?>
            </span>
            <a href="logout.php" class="sidebar-link logout-btn">
                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"></path><polyline points="16 17 21 12 16 7"></polyline><line x1="21" y1="12" x2="9" y2="12"></line></svg>
                Logout
            </a>
        </div>
    </aside>

    <!-- MAIN CONTENT -->
    <main class="main-content" id="mainContent">

        <!-- TOP BAR -->
        <header class="topbar">
            <button class="sidebar-toggle" onclick="toggleSidebar()">
                <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="3" y1="12" x2="21" y2="12"></line><line x1="3" y1="6" x2="21" y2="6"></line><line x1="3" y1="18" x2="21" y2="18"></line></svg>
            </button>
            <div class="topbar-title">
                <?php
                $page_titles = [
                    'dashboard' => 'Dashboard',
                    'skills' => 'Konten',
                    'projects' => 'Projek',
                    'project_edit' => 'Form Projek',
                    'articles' => 'Artikel',
                    'article_edit' => 'Form Artikel',
                    'contact' => 'Kontak',
                    'messages' => 'Pesan Masuk',
                    'media' => 'Media',
                    'settings' => 'Pengaturan'
                ];
                $sub = $_GET['sub'] ?? '';
                if ($page == 'settings' && $sub == 'projek') $page_title = 'Pengaturan Projek';
                elseif ($page == 'settings' && $sub == 'contact') $page_title = 'Pengaturan Kontak';
                else $page_title = $page_titles[$page] ?? 'Dashboard';
                ?>
                <h1><?= $page_title ?></h1>
            </div>
            <div class="topbar-right">
            </div>
        </header>

        <!-- TOAST -->
        <div id="toast" class="toast <?= $flash ? 'toast-show toast-' . $flash['type'] : '' ?>">
            <span id="toast-icon">
                <?php if ($flash && $flash['type'] === 'success'): ?>
                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="20 6 9 17 4 12"></polyline></svg>
                <?php elseif ($flash && $flash['type'] === 'warning'): ?>
                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M10.29 3.86L1.82 18a2 2 0 0 0 1.71 3h16.94a2 2 0 0 0 1.71-3L13.71 3.86a2 2 0 0 0-3.42 0z"></path><line x1="12" y1="9" x2="12" y2="13"></line><line x1="12" y1="17" x2="12.01" y2="17"></line></svg>
                <?php endif; ?>
            </span>
            <span id="toast-message"><?= $flash ? htmlspecialchars($flash['message']) : '' ?></span>
            <button class="toast-close" onclick="closeToast()">&times;</button>
        </div>

        <!-- PAGE CONTENT -->
        <div class="page-content">

        <?php if ($page == 'dashboard'): ?>
            <h1>Dashboard</h1>
            <div class="dashboard-grid">
                <div class="stat-card">
                    <div class="stat-icon stat-icon-blue">
                        <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="2" y="3" width="20" height="14" rx="2" ry="2"></rect><line x1="8" y1="21" x2="16" y2="21"></line><line x1="12" y1="17" x2="12" y2="21"></line></svg>
                    </div>
                    <div class="stat-info">
                        <h3>Projek</h3>
                        <p class="stat-number" data-target="<?= count($projects) ?>">0</p>
                    </div>
                </div>
                <div class="stat-card">
                    <div class="stat-icon stat-icon-blue">
                        <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"></path></svg>
                    </div>
                    <div class="stat-info">
                        <h3>Artikel</h3>
                        <p class="stat-number" data-target="<?= count($articles) ?>">0</p>
                    </div>
                </div>
                <div class="stat-card">
                    <div class="stat-icon stat-icon-blue">
                        <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M14.7 6.3a1 1 0 0 0 0 1.4l1.6 1.6a1 1 0 0 0 1.4 0l3.77-3.77a6 6 0 0 1-7.94 7.94l-6.91 6.91a2.12 2.12 0 0 1-3-3l6.91-6.91a6 6 0 0 1 7.94-7.94l-3.76 3.76z"></path></svg>
                    </div>
                    <div class="stat-info">
                        <h3>Skills</h3>
                        <p class="stat-number" data-target="<?= count($skills) ?>">0</p>
                    </div>
                </div>
                <div class="stat-card">
                    <div class="stat-icon stat-icon-red">
                        <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2z"></path></svg>
                    </div>
                    <div class="stat-info">
                        <h3>Pesan <?= $unread_count > 0 ? '<span style="color:#ef4444; font-size:0.7rem;">(' . $unread_count . ' baru)</span>' : '' ?></h3>
                        <p class="stat-number" data-target="<?= count($messages) ?>">0</p>
                    </div>
                </div>
            </div>

            <div style="display:grid; grid-template-columns:1fr 1fr; gap:1.5rem;">
                <div class="card">
                    <h3 style="margin-bottom:1rem; font-size:1rem;">Projek Terbaru</h3>
                    <?php $recent_projects = array_slice($projects, 0, 5); ?>
                    <?php if ($recent_projects): ?>
                    <table>
                        <tr><th>Judul</th><th>Status</th></tr>
                        <?php foreach ($recent_projects as $p): ?>
                        <tr><td><?= htmlspecialchars($p['title']) ?></td><td><span class="status-badge <?= $p['status'] == 'Completed' ? 'status-completed' : ($p['status'] == 'In Progress' ? 'status-progress' : 'status-planning') ?>"><?= htmlspecialchars($p['status']) ?></span></td></tr>
                        <?php endforeach; ?>
                    </table>
                    <?php else: ?>
                    <p style="color:#94a3b8;">Belum ada projek.</p>
                    <?php endif; ?>
                </div>
                <div class="card">
                    <h3 style="margin-bottom:1rem; font-size:1rem;">Artikel Terpopuler</h3>
                    <?php
                    $sorted_articles = $articles;
                    usort($sorted_articles, fn($a, $b) => ($b['views'] ?? 0) - ($a['views'] ?? 0));
                    $top_articles = array_slice($sorted_articles, 0, 5);
                    ?>
                    <?php if ($top_articles): ?>
                    <table>
                        <tr><th>Judul</th><th>Dilihat</th></tr>
                        <?php foreach ($top_articles as $a): ?>
                        <tr><td><?= htmlspecialchars($a['title']) ?></td><td><span class="badge badge-purple"><?= $a['views'] ?? 0 ?>x</span></td></tr>
                        <?php endforeach; ?>
                    </table>
                    <?php else: ?>
                    <p style="color:#94a3b8;">Belum ada artikel.</p>
                    <?php endif; ?>
                </div>
            </div>

            <div class="card" style="margin-top:1.5rem;">
                <h3 style="margin-bottom:1rem; font-size:1rem;">Artikel Terbaru</h3>
                <?php $recent_articles = array_slice($articles, 0, 5); ?>
                <?php if ($recent_articles): ?>
                <table>
                    <tr><th>Judul</th><th>Kategori</th></tr>
                    <?php foreach ($recent_articles as $a): ?>
                    <tr><td><?= htmlspecialchars($a['title']) ?></td><td><span class="badge badge-blue"><?= htmlspecialchars($a['category'] ?? 'Umum') ?></span></td></tr>
                    <?php endforeach; ?>
                </table>
                <?php else: ?>
                <p style="color:#94a3b8;">Belum ada artikel.</p>
                <?php endif; ?>
            </div>

            <div class="card">
                <h3 style="margin-bottom:1rem; font-size:1rem;">Pesan Terbaru <?= $unread_count > 0 ? '<span class="badge badge-red">' . $unread_count . ' belum dibaca</span>' : '' ?></h3>
                <?php $recent_messages = array_slice($messages, 0, 5); ?>
                <?php if ($recent_messages): ?>
                <table>
                    <tr><th>Tanggal</th><th>Nama</th><th>Pesan</th></tr>
                    <?php foreach ($recent_messages as $m): ?>
                    <tr style="<?= empty($m['is_read']) ? 'background:#fef2f2; font-weight:600;' : '' ?>">
                        <td style="white-space:nowrap;"><?= htmlspecialchars($m['tanggal']) ?></td>
                        <td><?= htmlspecialchars($m['nama']) ?></td>
                        <td><?= substr(htmlspecialchars($m['pesan']), 0, 60) ?><?= strlen($m['pesan']) > 60 ? '...' : '' ?></td>
                    </tr>
                    <?php endforeach; ?>
                </table>
                <?php else: ?>
                <p style="color:#94a3b8;">Belum ada pesan.</p>
                <?php endif; ?>
            </div>

        <!-- MANAJEMEN SKILLS -->
        <?php elseif ($page == 'skills'): ?>
            <?php if ($sub_skill == 'beranda'): ?>
            <h1>Edit Beranda</h1>
            <div class="card">
                <form action="process/admin.php" method="POST">
                    <input type="hidden" name="csrf_token" value="<?= generate_csrf() ?>">
                    <input type="hidden" name="action" value="update_home">
                    <div class="form-group"><label>URL Foto</label><input type="text" name="hero_img" class="form-control" value="<?= htmlspecialchars($settings['hero_img']) ?>"></div>
                    <div class="form-group"><label>Judul Badge</label><input type="text" name="hero_badge" class="form-control" value="<?= htmlspecialchars($settings['hero_badge'] ?? 'Indra Syah Putra') ?>"></div>
                    <div class="form-group"><label>Judul Utama</label><input type="text" name="hero_title" class="form-control" value="<?= htmlspecialchars($settings['hero_title']) ?>"></div>
                    <div class="form-group"><label>Deskripsi Utama</label><textarea name="hero_desc" class="form-control" rows="4"><?= htmlspecialchars($settings['hero_desc']) ?></textarea></div>
                    <button type="submit" class="btn btn-primary">Simpan</button>
                </form>
            </div>
            <?php elseif ($sub_skill == 'profil'): ?>
            <h1>Profil</h1>
            <div class="card">
                <form action="process/admin.php" method="POST">
                    <input type="hidden" name="csrf_token" value="<?= generate_csrf() ?>">
                    <input type="hidden" name="action" value="update_about">
                    <div class="form-group">
                        <label>Isi Profil (Bio)</label>
                        <textarea name="about_bio" class="form-control" rows="8" required><?= htmlspecialchars($settings['about_bio']) ?></textarea>
                    </div>
                    <button type="submit" class="btn btn-primary">Simpan</button>
                </form>
            </div>
            <?php elseif ($sub_skill == 'skill'): ?>
            <h1>Daftar Skill</h1>
            <div class="card">
                <form action="process/admin.php?action=update_skills" method="POST">
                    <input type="hidden" name="csrf_token" value="<?= generate_csrf() ?>">
                    <div class="form-group" style="display:flex; gap:10px;">
                        <input type="text" name="new_skill" class="form-control" placeholder="Nama Skill (misal: React JS)" required>
                        <button type="submit" class="btn btn-primary">Tambah</button>
                    </div>
                </form>
                <hr style="border:0; border-top:1px solid #e2e8f0; margin: 2rem 0;">
                <div style="display: flex; flex-wrap: wrap; gap: 10px;">
                    <?php foreach($skills as $skill): ?>
                    <span class="badge badge-blue" style="font-size: 0.9rem; padding: 8px 14px; display:inline-flex; align-items:center;">
                        <?= htmlspecialchars($skill) ?>
                        <a href="process/admin.php?action=update_skills&delete=<?= htmlspecialchars($skill) ?>&csrf_token=<?= generate_csrf() ?>" style="color:#ef4444; margin-left:8px; text-decoration:none; font-weight:700;" onclick="return confirm('Hapus skill ini?')">&times;</a>
                    </span>
                    <?php endforeach; ?>
                </div>
            </div>
            <?php elseif ($sub_skill == 'timeline'): ?>
            <h1>Timeline</h1>
            <p style="color:var(--text-muted); margin-bottom:1.5rem;">Pendidikan dan pengalaman yang tampil di halaman Tentang.</p>

            <?php $timeline = $settings['timeline'] ?? []; ?>
            <div style="display:grid; gap:1rem; margin-bottom:2rem;">
                <?php foreach ($timeline as $i => $item): ?>
                <form action="process/admin.php" method="POST" style="background:var(--primary-bg); padding:1rem; border-radius:8px;">
                    <input type="hidden" name="csrf_token" value="<?= generate_csrf() ?>">
                    <input type="hidden" name="action" value="update_timeline_item">
                    <input type="hidden" name="index" value="<?= $i ?>">
                    <div style="display:flex; gap:0.5rem; margin-bottom:0.5rem;">
                        <input type="text" name="tl_title" class="form-control" placeholder="Judul" value="<?= htmlspecialchars($item['title']) ?>" required style="flex:1;">
                        <a href="process/admin.php?action=delete_timeline&index=<?= $i ?>&csrf_token=<?= generate_csrf() ?>" class="btn btn-danger" onclick="return confirm('Hapus item ini?')">&times;</a>
                    </div>
                    <div style="display:flex; gap:0.5rem; margin-bottom:0.5rem;">
                        <input type="text" name="tl_org" class="form-control" placeholder="Organisasi" value="<?= htmlspecialchars($item['organization']) ?>" style="flex:1;">
                        <input type="text" name="tl_year" class="form-control" placeholder="Tahun" value="<?= htmlspecialchars($item['year']) ?>" style="max-width:160px;">
                    </div>
                    <div style="display:flex; gap:0.5rem; margin-bottom:0.5rem;">
                        <select name="tl_type" class="form-control" style="max-width:160px;">
                            <option value="education" <?= $item['type'] === 'education' ? 'selected' : '' ?>>Pendidikan</option>
                            <option value="experience" <?= $item['type'] === 'experience' ? 'selected' : '' ?>>Pengalaman</option>
                        </select>
                    </div>
                    <textarea name="tl_desc" class="form-control" rows="2" placeholder="Deskripsi"><?= htmlspecialchars($item['desc']) ?></textarea>
                    <button type="submit" class="btn btn-primary btn-sm" style="margin-top:0.5rem;">Simpan</button>
                </form>
                <?php endforeach; ?>
            </div>

            <details style="background:var(--bg-card); padding:1rem; border-radius:8px; border:1px dashed var(--border); cursor:pointer;">
                <summary style="font-weight:600;">+ Tambah Item Baru</summary>
                <form action="process/admin.php" method="POST" style="margin-top:1rem;">
                    <input type="hidden" name="csrf_token" value="<?= generate_csrf() ?>">
                    <input type="hidden" name="action" value="add_timeline">
                    <div class="form-group"><label>Judul</label><input type="text" name="tl_title" class="form-control" required></div>
                    <div class="form-group"><label>Organisasi</label><input type="text" name="tl_org" class="form-control"></div>
                    <div class="form-group"><label>Tahun</label><input type="text" name="tl_year" class="form-control" placeholder="2023 - Sekarang"></div>
                    <div class="form-group"><label>Tipe</label>
                        <select name="tl_type" class="form-control">
                            <option value="education">Pendidikan</option>
                            <option value="experience">Pengalaman</option>
                        </select>
                    </div>
                    <div class="form-group"><label>Deskripsi</label><textarea name="tl_desc" class="form-control" rows="2"></textarea></div>
                    <button type="submit" class="btn btn-primary">Tambah</button>
                </form>
            </details>
            <?php elseif ($sub_skill == 'gallery'): ?>
            <?php $gallery = $settings['gallery'] ?? []; ?>
            <h1>Galeri</h1>
            <p style="color:var(--text-muted); margin-bottom:1.5rem;">Foto kegiatan dan sertifikat.</p>

            <div style="display:grid; gap:1rem; margin-bottom:2rem;">
                <?php foreach ($gallery as $i => $item): ?>
                <form action="process/admin.php" method="POST" enctype="multipart/form-data" style="background:var(--primary-bg); padding:1rem; border-radius:8px;">
                    <input type="hidden" name="csrf_token" value="<?= generate_csrf() ?>">
                    <input type="hidden" name="action" value="update_gallery">
                    <input type="hidden" name="index" value="<?= $i ?>">
                    <div style="display:flex; gap:0.5rem; align-items:center;">
                        <?php if (!empty($item['image'])): ?>
                        <img src="<?= htmlspecialchars($item['image']) ?>" style="width:60px;height:60px;object-fit:cover;border-radius:6px;flex-shrink:0;">
                        <?php endif; ?>
                        <a href="process/admin.php?action=delete_gallery&index=<?= $i ?>&csrf_token=<?= generate_csrf() ?>" class="btn btn-danger" onclick="return confirm('Hapus item ini?')">&times;</a>
                    </div>
                    <div style="display:flex; gap:0.5rem; margin-top:0.5rem;">
                        <input type="file" name="gl_image_file" class="form-control" accept="image/*" style="flex:1; padding:0.3rem 0.5rem;">
                        <input type="text" name="gl_image" class="form-control" placeholder="Atau URL Gambar" value="<?= htmlspecialchars($item['image']) ?>" style="flex:1;">
                        <input type="text" name="gl_year" class="form-control" placeholder="Tahun" value="<?= htmlspecialchars($item['year'] ?? '') ?>" style="max-width:100px;">
                    </div>
                    <button type="submit" class="btn btn-primary btn-sm" style="margin-top:0.5rem;">Simpan</button>
                </form>
                <?php endforeach; ?>
            </div>

            <details style="background:var(--bg-card); padding:1rem; border-radius:8px; border:1px dashed var(--border); cursor:pointer;">
                <summary style="font-weight:600;">+ Tambah Item Baru</summary>
                <form action="process/admin.php" method="POST" enctype="multipart/form-data" style="margin-top:1rem;">
                    <input type="hidden" name="csrf_token" value="<?= generate_csrf() ?>">
                    <input type="hidden" name="action" value="add_gallery">
                    <div class="form-group"><label>Upload Gambar</label><input type="file" name="gl_image_file" class="form-control" accept="image/*"></div>
                    <div class="form-group"><label>Atau URL Gambar</label><input type="text" name="gl_image" class="form-control" placeholder="https://..."></div>
                    <div class="form-group"><label>Tahun</label><input type="text" name="gl_year" class="form-control" placeholder="2026"></div>
                    <button type="submit" class="btn btn-primary">Tambah</button>
                </form>
            </details>
            <?php endif; ?>

        <!-- MANAJEMEN PROJEK -->
        <?php elseif ($page == 'projects'): ?>
            <?php if ($sub_proj == 'semua'): ?>
            <div class="page-header">
                <h1>Kelola Projek</h1>
                <a href="?page=project_edit" class="btn btn-primary">+ Tambah Projek</a>
            </div>
            <form method="GET" style="margin-bottom:1rem; display:flex; gap:0.5rem;">
                <input type="hidden" name="page" value="projects">
                <input type="hidden" name="sub" value="semua">
                <input type="text" name="q" placeholder="Cari projek..." value="<?= htmlspecialchars($_GET['q'] ?? '') ?>" style="flex:1;padding:0.5rem 1rem;border:1px solid #e2e8f0;border-radius:8px;font-size:0.85rem;">
                <button type="submit" class="btn btn-primary btn-sm">Cari</button>
                <?php if (!empty($_GET['q'])): ?>
                <a href="?page=projects" class="btn btn-secondary btn-sm">Reset</a>
                <?php endif; ?>
            </form>
            <?php
            $search_q = trim($_GET['q'] ?? '');
            $filtered_projects = $projects;
            if (!empty($search_q)) {
                $filtered_projects = array_values(array_filter($filtered_projects, fn($p) => str_contains(strtolower($p['title']), strtolower($search_q))));
            }
            $pagination_projects = paginate($filtered_projects, 10, 'pp');
            $page_projects = $pagination_projects['items'];
            ?>
            <div class="card table-wrap" style="padding:0;">
                <table>
                    <tr><th>Status</th><th>Tanggal</th><th>Judul</th><th>Tech Stack</th><th>Aksi</th></tr>
                    <?php if (empty($page_projects)): ?>
                    <tr><td colspan="5" style="text-align:center;color:#94a3b8;padding:2rem;">Tidak ada projek ditemukan.</td></tr>
                    <?php else: foreach($page_projects as $proj): ?>
                    <tr>
                        <td>
                            <?php 
                            $status_val = htmlspecialchars($proj['status']);
                            $class_val = 'status-planning'; 
                            if($status_val == 'Completed') $class_val = 'status-completed';
                            elseif($status_val == 'In Progress') $class_val = 'status-progress';
                            ?>
                            <span class="status-badge <?= $class_val ?>">
                                <?= $status_val ?>
                            </span>
                        </td>
                        <td><?= htmlspecialchars($proj['date'] ?? '-') ?></td>
                        <td><?= htmlspecialchars($proj['title']) ?><?= ($proj['published'] ?? '1') === '0' ? ' <span style="background:#fef3c7;color:#92400e;font-size:0.7rem;padding:1px 6px;border-radius:4px;margin-left:6px;">Draft</span>' : '' ?></td>
                        <td><?= htmlspecialchars($proj['tech']) ?></td>
                        <td style="white-space:nowrap;">
                            <a href="?page=project_edit&id=<?= htmlspecialchars($proj['id']) ?>" class="btn btn-primary btn-sm">Edit</a>
                            <a href="process/admin.php?action=delete_project&id=<?= $proj['id'] ?>&csrf_token=<?= generate_csrf() ?>" onclick="return confirm('Hapus?')" class="btn btn-danger btn-sm">Hapus</a>
                        </td>
                    </tr>
                    <?php endforeach; endif; ?>
                </table>
                <?php render_pagination($pagination_projects['total_pages'], $pagination_projects['page'], '?page=projects&sub=semua' . (!empty($search_q) ? '&q=' . urlencode($search_q) : ''), 'pp'); ?>
            </div>
            <?php elseif ($sub_proj == 'kategori'): ?>
            <div class="page-header">
                <h1>Kelola Kategori Projek</h1>
            </div>
            <div class="card">
                <?php $proj_cats = $settings['project_categories'] ?? []; ?>
                <form action="process/admin.php?action=update_project_categories" method="POST">
                    <input type="hidden" name="csrf_token" value="<?= generate_csrf() ?>">
                    <div class="form-group" style="display:flex; gap:10px;">
                        <input type="text" name="new_category" class="form-control" placeholder="Nama Kategori Baru (misal: React JS)" required>
                        <button type="submit" class="btn btn-primary">Tambah</button>
                    </div>
                </form>
                <hr style="border:0; border-top:1px solid #e2e8f0; margin: 1.5rem 0;">
                <div style="display: flex; flex-wrap: wrap; gap: 8px;">
                    <?php foreach($proj_cats as $cat): ?>
                    <span class="badge badge-purple" style="padding:6px 12px; font-size:0.85rem; display:inline-flex; align-items:center;">
                        <?= htmlspecialchars($cat) ?>
                        <a href="process/admin.php?action=update_project_categories&delete=<?= urlencode($cat) ?>&csrf_token=<?= generate_csrf() ?>" onclick="return confirm('Hapus kategori &laquo;<?= htmlspecialchars($cat) ?>&raquo;?')" style="color:#ef4444; margin-left:6px; text-decoration:none; font-weight:700;">&times;</a>
                    </span>
                    <?php endforeach; ?>
                </div>
            </div>
            <?php elseif ($sub_proj == 'tech'): ?>
            <div class="page-header">
                <h1>Kelola Tech Skill</h1>
            </div>
            <div class="card">
                <?php $tech_skills = $settings['tech_skills'] ?? []; ?>
                <form action="process/admin.php?action=update_tech_skills" method="POST">
                    <input type="hidden" name="csrf_token" value="<?= generate_csrf() ?>">
                    <div class="form-group" style="display:flex; gap:10px;">
                        <input type="text" name="new_tech" class="form-control" placeholder="Nama Tech Skill (misal: Laravel)" required>
                        <button type="submit" class="btn btn-primary">Tambah</button>
                    </div>
                </form>
                <hr style="border:0; border-top:1px solid #e2e8f0; margin: 1.5rem 0;">
                <div style="display: flex; flex-wrap: wrap; gap: 8px;">
                    <?php foreach($tech_skills as $tech): ?>
                    <span class="badge badge-green" style="padding:6px 12px; font-size:0.85rem; display:inline-flex; align-items:center;">
                        <?= htmlspecialchars($tech) ?>
                        <a href="process/admin.php?action=update_tech_skills&delete=<?= urlencode($tech) ?>&csrf_token=<?= generate_csrf() ?>" onclick="return confirm('Hapus tech skill &laquo;<?= htmlspecialchars($tech) ?>&raquo;?')" style="color:#ef4444; margin-left:6px; text-decoration:none; font-weight:700;">&times;</a>
                    </span>
                    <?php endforeach; ?>
                </div>
            </div>
            <?php endif; ?>

        <!-- FORM PROJEK -->
        <?php elseif ($page == 'project_edit'): ?>
            <?php 
            $is_edit = isset($_GET['id']);
            $proj_data = ['title' => '', 'desc' => '', 'img' => '', 'tech' => '', 'link' => '', 'status' => 'In Progress'];
            if($is_edit) {
                foreach($projects as $p) { if($p['id'] == $_GET['id']) { $proj_data = $p; break; } }
            }
            ?>
            <h1><?= $is_edit ? 'Edit' : 'Tambah' ?> Projek</h1>
            <div class="card">
            <form action="process/admin.php" method="POST" enctype="multipart/form-data">
                    <input type="hidden" name="csrf_token" value="<?= generate_csrf() ?>">
                    <input type="hidden" name="action" value="<?= $is_edit ? 'update_project' : 'create_project' ?>">
                    <?php if($is_edit): ?><input type="hidden" name="id" value="<?= htmlspecialchars($_GET['id']) ?>"><?php endif; ?>
                    
                    <div class="form-group"><label>Judul</label><input type="text" name="title" class="form-control" value="<?= htmlspecialchars($proj_data['title']) ?>" required></div>
                    <div class="form-group"><label>Deskripsi</label><textarea name="desc" class="form-control" rows="4" required><?= htmlspecialchars($proj_data['desc']) ?></textarea></div>
                    <div class="form-group"><label>Gambar (Upload)</label><input type="file" name="img_file" class="form-control" accept="image/*"></div>
                    <div class="form-group"><label>Atau URL Gambar</label><input type="text" name="img" class="form-control" value="<?= htmlspecialchars($proj_data['img']) ?>" placeholder="https://..."></div>
                    <div class="form-group"><label>Kategori</label>
                        <select name="category" class="form-control">
                            <?php $proj_cats = $settings['project_categories'] ?? []; $proj_cat_val = $proj_data['category'] ?? ''; ?>
                            <option value="">Pilih Kategori</option>
                            <?php foreach($proj_cats as $cat): ?>
                            <option value="<?= htmlspecialchars($cat) ?>" <?= $proj_cat_val === $cat ? 'selected' : '' ?>><?= htmlspecialchars($cat) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="form-group"><label>Tech Stack</label>
                        <div style="display:flex; flex-wrap:wrap; gap:8px; margin-top:4px;">
                            <?php
                            $proj_techs = array_map('trim', explode(',', $proj_data['tech'] ?? ''));
                            $tech_skills = $settings['tech_skills'] ?? [];
                            if (!empty($tech_skills)): foreach($tech_skills as $tech): ?>
                            <label style="display:inline-flex; align-items:center; gap:4px; cursor:pointer; padding:4px 10px; border:1px solid var(--border); border-radius:6px; font-size:0.85rem;">
                                <input type="checkbox" name="tech[]" value="<?= htmlspecialchars($tech) ?>" <?= in_array($tech, $proj_techs) ? 'checked' : '' ?>>
                                <?= htmlspecialchars($tech) ?>
                            </label>
                            <?php endforeach; else: ?>
                            <span style="color:var(--text-muted); font-size:0.85rem;">Belum ada tech skill. Tambah dulu di <a href="?page=projects&sub=tech">Tech Skill</a>.</span>
                            <?php endif; ?>
                        </div>
                    </div>
                    <div class="form-group"><label>Link (Github/Demo)</label><input type="text" name="link" class="form-control" value="<?= htmlspecialchars($proj_data['link']) ?>"></div>
                    <div class="form-group">
                        <label style="display:flex; align-items:center; gap:8px; cursor:pointer;">
                            <input type="hidden" name="published" value="0">
                            <input type="checkbox" name="published" value="1" <?= ($proj_data['published'] ?? '1') === '1' ? 'checked' : '' ?>>
                            Publish (centang = tampil di web)
                        </label>
                    </div>
                    <div class="form-group"><label>Status</label>
                        <select name="status" class="form-control">
                            <option value="Completed" <?= $proj_data['status'] == 'Completed' ? 'selected' : '' ?>>Completed</option>
                            <option value="In Progress" <?= $proj_data['status'] == 'In Progress' ? 'selected' : '' ?>>In Progress</option>
                            <option value="Planning" <?= $proj_data['status'] == 'Planning' ? 'selected' : '' ?>>Planning</option>
                        </select>
                    </div>
                    <button type="submit" class="btn btn-primary">Simpan</button>
                    <a href="?page=projects" class="btn btn-secondary">Batal</a>
                </form>
            </div>

        <?php elseif ($page == 'articles'): ?>
            <?php if ($sub_art == 'semua'): ?>
            <div class="page-header">
                <h1>Kelola Artikel</h1>
                <a href="?page=article_edit" class="btn btn-primary">+ Tambah Artikel</a>
            </div>
            <form method="GET" style="margin-bottom:1rem; display:flex; gap:0.5rem;">
                <input type="hidden" name="page" value="articles">
                <input type="hidden" name="sub" value="semua">
                <input type="text" name="q" placeholder="Cari artikel..." value="<?= htmlspecialchars($_GET['q'] ?? '') ?>" style="flex:1;padding:0.5rem 1rem;border:1px solid #e2e8f0;border-radius:8px;font-size:0.85rem;">
                <button type="submit" class="btn btn-primary btn-sm">Cari</button>
                <?php if (!empty($_GET['q'])): ?>
                <a href="?page=articles" class="btn btn-secondary btn-sm">Reset</a>
                <?php endif; ?>
            </form>
            <?php
            $search_art_q = trim($_GET['q'] ?? '');
            $filtered_articles = $articles;
            if (!empty($search_art_q)) {
                $filtered_articles = array_values(array_filter($filtered_articles, fn($a) => str_contains(strtolower($a['title']), strtolower($search_art_q))));
            }
            $pagination_articles = paginate($filtered_articles, 10, 'ap');
            $page_articles = $pagination_articles['items'];
            ?>
            <div class="card table-wrap" style="padding:0;"><table><tr><th>Kategori</th><th>Tanggal</th><th>Judul</th><th>Aksi</th></tr><?php if (empty($page_articles)): ?><tr><td colspan="4" style="text-align:center;color:#94a3b8;padding:2rem;">Tidak ada artikel ditemukan.</td></tr><?php else: foreach($page_articles as $art): ?><tr><td><span class="badge badge-blue"><?= htmlspecialchars($art['category'] ?? 'Umum') ?></span></td><td><?= htmlspecialchars($art['date']) ?></td><td><?= htmlspecialchars($art['title']) ?><?= ($art['published'] ?? '1') === '0' ? ' <span style="background:#fef3c7;color:#92400e;font-size:0.7rem;padding:1px 6px;border-radius:4px;margin-left:6px;">Draft</span>' : '' ?></td><td style="white-space:nowrap;"><a href="?page=article_edit&id=<?= htmlspecialchars($art['id']) ?>" class="btn btn-primary btn-sm">Edit</a> <a href="process/admin.php?action=delete_article&id=<?= $art['id'] ?>&csrf_token=<?= generate_csrf() ?>" onclick="return confirm('Hapus?')" class="btn btn-danger btn-sm">Hapus</a></td></tr><?php endforeach; endif; ?><?php render_pagination($pagination_articles['total_pages'], $pagination_articles['page'], '?page=articles&sub=semua' . (!empty($search_art_q) ? '&q=' . urlencode($search_art_q) : ''), 'ap'); ?></table></div>
            <?php elseif ($sub_art == 'kategori'): ?>
            <div class="page-header">
                <h1>Kelola Kategori</h1>
            </div>
            <div class="card">
                <form action="process/admin.php?action=update_categories" method="POST"><input type="hidden" name="csrf_token" value="<?= generate_csrf() ?>"><div class="form-group" style="display:flex; gap:10px;"><input type="text" name="new_category" class="form-control" placeholder="Nama Kategori Baru" required><button type="submit" class="btn btn-primary">Tambah</button></div></form>
                <hr style="border:0; border-top:1px solid #e2e8f0; margin: 1.5rem 0;">
                <div style="display: flex; flex-wrap: wrap; gap: 8px;"><?php foreach($categories as $cat): ?><span class="badge badge-purple" style="padding:6px 12px; font-size:0.85rem; display:inline-flex; align-items:center;"><?= htmlspecialchars($cat) ?> <a href="process/admin.php?action=update_categories&delete=<?= htmlspecialchars($cat) ?>&csrf_token=<?= generate_csrf() ?>" onclick="return confirm('Hapus kategori &laquo;<?= htmlspecialchars($cat) ?>&raquo;?')" style="color:#ef4444; margin-left:6px; text-decoration:none; font-weight:700;">&times;</a></span><?php endforeach; ?></div>
            </div>
            <?php endif; ?>
        <?php elseif ($page == 'article_edit'): ?>
            <?php $is_edit = isset($_GET['id']); $art_data = ['title'=>'','content'=>'','img'=>'','date'=>date('Y-m-d'),'category'=>'']; if($is_edit) foreach($articles as $a) if($a['id'] == $_GET['id']) $art_data = $a; ?>
            <h1><?= $is_edit ? 'Edit' : 'Tambah' ?> Artikel</h1>
            <div class="card">
                <form action="process/admin.php" method="POST" enctype="multipart/form-data">
                    <input type="hidden" name="csrf_token" value="<?= generate_csrf() ?>">
                    <input type="hidden" name="action" value="<?= $is_edit ? 'update_article' : 'create_article' ?>"><?php if($is_edit): ?><input type="hidden" name="id" value="<?= htmlspecialchars($_GET['id']) ?>"><?php endif; ?>
                    <div class="form-group"><label>Judul</label><input type="text" name="title" class="form-control" value="<?= htmlspecialchars($art_data['title']) ?>" required></div>
                    <div class="form-group"><label>Kategori</label><select name="category" class="form-control"><?php foreach($categories as $cat): ?><option value="<?= htmlspecialchars($cat) ?>" <?= $art_data['category'] === $cat ? 'selected' : '' ?>><?= htmlspecialchars($cat) ?></option><?php endforeach; ?><?php if(!in_array('Umum', $categories)): ?><option value="Umum" <?= $art_data['category'] === 'Umum' ? 'selected' : '' ?>>Umum</option><?php endif; ?></select></div>
                    <div class="form-group"><label>Gambar (Upload)</label><input type="file" name="img_file" class="form-control" accept="image/*"></div>
                    <div class="form-group"><label>Atau URL Gambar</label><input type="text" name="img" class="form-control" value="<?= htmlspecialchars($art_data['img']) ?>" placeholder="https://..."></div>
                    <div class="form-group"><label>Isi Konten</label><textarea name="content" class="form-control trumbowyg" rows="10" required><?= htmlspecialchars($art_data['content']) ?></textarea></div>
                    <div class="form-group">
                        <label style="display:flex; align-items:center; gap:8px; cursor:pointer;">
                            <input type="hidden" name="published" value="0">
                            <input type="checkbox" name="published" value="1" <?= ($art_data['published'] ?? '1') === '1' ? 'checked' : '' ?>>
                            Publish (centang = tampil di web)
                        </label>
                    </div>
                    <button type="submit" class="btn btn-primary">Simpan</button>
                    <a href="?page=articles" class="btn btn-secondary" style="margin-left: 10px;">Batal</a>
                </form>
            </div>
        <?php elseif ($page == 'contact'): ?>
            <h1>Edit Sosmed</h1>
            <div class="card">
                <form action="process/admin.php" method="POST">
                    <input type="hidden" name="csrf_token" value="<?= generate_csrf() ?>">
                    <input type="hidden" name="action" value="update_contact">
                    <div class="form-group">
                        <label>Email</label>
                        <input type="email" name="contact_email" class="form-control" value="<?= htmlspecialchars($settings['contact_email']) ?>">
                    </div>
                    <div class="form-group">
                        <label>Instagram</label>
                        <input type="text" name="contact_insta" class="form-control" value="<?= htmlspecialchars($settings['contact_insta']) ?>">
                    </div>
                    <div class="form-group">
                        <label>Github</label>
                        <input type="text" name="contact_github" class="form-control" value="<?= htmlspecialchars($settings['contact_github']) ?>">
                    </div>
                    <button type="submit" class="btn btn-primary">Simpan</button>
                </form>
            </div>
        <?php elseif ($page == 'messages'): ?>
            <h1>Pesan Masuk <?= $unread_count > 0 ? '<span class="badge badge-red">' . $unread_count . ' baru</span>' : '' ?></h1>
            <?php $pagination_msgs = paginate($messages, 10, 'mp'); $page_msgs = $pagination_msgs['items']; ?>
            <div class="card table-wrap" style="padding:0;">
                <table><tr><th>Tanggal</th><th>Nama</th><th>Email</th><th>Pesan</th><th>Aksi</th></tr><?php foreach($page_msgs as $msg): ?><tr style="<?= empty($msg['is_read']) ? 'background:#fef2f2; font-weight:600;' : '' ?>"><td style="white-space:nowrap;"><?= htmlspecialchars($msg['tanggal']) ?></td><td><?= htmlspecialchars($msg['nama']) ?></td><td><?= htmlspecialchars($msg['email']) ?></td><td><?= substr(htmlspecialchars($msg['pesan']),0,50) ?>...</td><td><a href="process/admin.php?action=delete_message&id=<?= htmlspecialchars($msg['id']) ?>&csrf_token=<?= generate_csrf() ?>" onclick="return confirm('Hapus?')" class="btn btn-danger btn-sm">Hapus</a></td></tr><?php endforeach; ?><?php render_pagination($pagination_msgs['total_pages'], $pagination_msgs['page'], '?page=messages', 'mp'); ?></table>
            </div>
        <?php elseif ($page == 'media'): ?>
            <?php $sub_media = $_GET['sub'] ?? 'projek'; ?>
            <div style="display:flex; gap:0.5rem; margin-bottom:1.5rem; border-bottom:1px solid #e2e8f0; padding-bottom:0;">
                <a href="?page=media&sub=projek" class="btn <?= $sub_media == 'projek' ? 'btn-primary' : 'btn-secondary' ?>" style="border-radius:8px 8px 0 0;">Media Projek</a>
                <a href="?page=media&sub=artikel" class="btn <?= $sub_media == 'artikel' ? 'btn-primary' : 'btn-secondary' ?>" style="border-radius:8px 8px 0 0;">Media Artikel</a>
                <a href="?page=media&sub=gallery" class="btn <?= $sub_media == 'gallery' ? 'btn-primary' : 'btn-secondary' ?>" style="border-radius:8px 8px 0 0;">Media Gallery</a>
            </div>
            <?php
            $media_dir = __DIR__ . '/uploads';
            $media_files = is_dir($media_dir) ? array_diff(scandir($media_dir), ['.', '..']) : [];
            $used_images = [];
            foreach ($projects as $p) { if (!empty($p['img']) && strpos($p['img'], 'uploads/') === 0) $used_images[$p['img']][] = 'Projek: ' . $p['title']; }
            foreach ($articles as $a) { if (!empty($a['img']) && strpos($a['img'], 'uploads/') === 0) $used_images[$a['img']][] = 'Artikel: ' . $a['title']; }
            $gallery = $settings['gallery'] ?? [];
            foreach ($gallery as $g) { if (!empty($g['image']) && strpos($g['image'], 'uploads/') === 0) $used_images[$g['image']][] = 'Gallery: ' . $g['year']; }

            if ($sub_media == 'projek') $filter_refs = ['Projek:'];
            elseif ($sub_media == 'artikel') $filter_refs = ['Artikel:'];
            else $filter_refs = ['Gallery:'];
            ?>
            <div class="page-header">
                <h1>Media <?= $sub_media == 'projek' ? 'Projek' : ($sub_media == 'artikel' ? 'Artikel' : 'Gallery') ?></h1>
            </div>
            <?php
            $filtered_files = [];
            foreach ($media_files as $file) {
                $rel_path = 'uploads/' . $file;
                if (isset($used_images[$rel_path])) {
                    foreach ($used_images[$rel_path] as $ref) {
                        foreach ($filter_refs as $prefix) {
                            if (strpos($ref, $prefix) === 0) {
                                $filtered_files[] = $file;
                                break 2;
                            }
                        }
                    }
                }
            }
            $filtered_files = array_unique($filtered_files);
            if (empty($filtered_files)): ?>
            <div class="card"><p style="color:#94a3b8;text-align:center;padding:2rem;">Belum ada gambar untuk kategori ini.</p></div>
            <?php else: ?>
            <div style="display:grid;grid-template-columns:repeat(auto-fill,minmax(200px,1fr));gap:1rem;">
                <?php foreach ($filtered_files as $file):
                    $rel_path = 'uploads/' . $file;
                    $full_path = $media_dir . '/' . $file;
                    $img_url = $rel_path;
                    $file_size = file_exists($full_path) ? round(filesize($full_path) / 1024, 1) . ' KB' : '-';
                ?>
                <div class="card" style="padding:0;overflow:hidden;">
                    <a href="<?= htmlspecialchars($img_url) ?>" target="_blank" style="display:block;">
                        <img src="<?= htmlspecialchars($img_url) ?>" alt="<?= htmlspecialchars($file) ?>" style="width:100%;height:150px;object-fit:cover;display:block;" loading="lazy">
                    </a>
                    <div style="padding:0.75rem;font-size:0.8rem;">
                        <div style="font-weight:600;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;"><?= htmlspecialchars($file) ?></div>
                        <div style="color:#64748b;margin-top:4px;"><?= $file_size ?></div>
                        <?php if (isset($used_images[$rel_path])): ?>
                        <div style="margin-top:6px;">
                            <?php foreach ($used_images[$rel_path] as $ref): ?>
                            <span class="badge badge-blue" style="display:block;margin-bottom:2px;"><?= htmlspecialchars($ref) ?></span>
                            <?php endforeach; ?>
                        </div>
                        <?php endif; ?>
                        <div style="margin-top:8px;">
                            <a href="process/admin.php?action=delete_media&file=<?= urlencode($file) ?>&csrf_token=<?= generate_csrf() ?>" onclick="return confirm('Hapus file <?= htmlspecialchars($file) ?>?')" class="btn btn-danger btn-sm" style="font-size:0.75rem;">Hapus</a>
                        </div>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
            <?php endif; ?>
        <?php elseif ($page == 'settings'): ?>
            <?php $sub = $_GET['sub'] ?? 'projek'; ?>
            <div style="display:flex; gap:0.5rem; margin-bottom:1.5rem; border-bottom:1px solid #e2e8f0; padding-bottom:0; flex-wrap:wrap;">
                <a href="?page=settings&sub=projek" class="btn <?= $sub == 'projek' ? 'btn-primary' : 'btn-secondary' ?>" style="border-radius:8px 8px 0 0;">Pengaturan Projek</a>
                <a href="?page=settings&sub=contact" class="btn <?= $sub == 'contact' ? 'btn-primary' : 'btn-secondary' ?>" style="border-radius:8px 8px 0 0;">Pengaturan Kontak</a>
                <a href="?page=settings&sub=faq" class="btn <?= $sub == 'faq' ? 'btn-primary' : 'btn-secondary' ?>" style="border-radius:8px 8px 0 0;">FAQ</a>
            </div>
            <?php if ($sub == 'projek'): ?>
            <div class="card">
                <form action="process/admin.php" method="POST">
                    <input type="hidden" name="csrf_token" value="<?= generate_csrf() ?>">
                    <input type="hidden" name="action" value="update_settings_projek">
                    <div class="form-group">
                        <label>Deskripsi Halaman Projek</label>
                        <textarea name="projek_desc" class="form-control" rows="4"><?= htmlspecialchars($settings['projek_desc'] ?? 'Kumpulan project dan karya terbaik yang pernah saya kerjakan.') ?></textarea>
                    </div>
                    <button type="submit" class="btn btn-primary">Simpan</button>
                </form>
            </div>
            <?php elseif ($sub == 'contact'): ?>
            <div class="card">
                <form action="process/admin.php" method="POST">
                    <input type="hidden" name="csrf_token" value="<?= generate_csrf() ?>">
                    <input type="hidden" name="action" value="update_settings_contact">
                    <div class="form-group">
                        <label>Deskripsi Halaman Kontak</label>
                        <textarea name="contact_desc" class="form-control" rows="4"><?= htmlspecialchars($settings['contact_desc']) ?></textarea>
                    </div>
                    <button type="submit" class="btn btn-primary">Simpan</button>
                </form>
            </div>
            <?php elseif ($sub == 'faq'): ?>
            <div class="card">
                <h3 style="margin-bottom:1rem;">Pertanyaan Umum (FAQ)</h3>
                <p style="font-size:0.85rem; color:var(--text-muted); margin-bottom:1.5rem;">Kelola pertanyaan dan jawaban FAQ yang tampil di halaman Kontak.</p>

                <?php $faq = $settings['faq'] ?? []; ?>

                <div style="display:grid; gap:1rem; margin-bottom:1.5rem;">
                    <?php foreach ($faq as $i => $item): ?>
                    <form action="process/admin.php" method="POST" style="background:var(--primary-bg); padding:1rem; border-radius:8px;">
                        <input type="hidden" name="csrf_token" value="<?= generate_csrf() ?>">
                        <input type="hidden" name="action" value="update_faq_item">
                        <input type="hidden" name="index" value="<?= $i ?>">
                        <div style="display:flex; gap:0.5rem; margin-bottom:0.5rem;">
                            <input type="text" name="faq_q" class="form-control" placeholder="Pertanyaan" value="<?= htmlspecialchars($item['q']) ?>" required style="flex:1;">
                            <a href="process/admin.php?action=delete_faq&index=<?= $i ?>&csrf_token=<?= generate_csrf() ?>" class="btn btn-danger" onclick="return confirm('Hapus FAQ ini?')" style="white-space:nowrap;">&times;</a>
                        </div>
                        <textarea name="faq_a" class="form-control" rows="2" placeholder="Jawaban" required><?= htmlspecialchars($item['a']) ?></textarea>
                        <button type="submit" class="btn btn-primary btn-sm" style="margin-top:0.5rem;">Simpan</button>
                    </form>
                    <?php endforeach; ?>
                </div>

                <details style="background:var(--bg-card); padding:1rem; border-radius:8px; border:1px dashed var(--border); cursor:pointer;">
                    <summary style="font-weight:600;">+ Tambah FAQ Baru</summary>
                    <form action="process/admin.php" method="POST" style="margin-top:1rem;">
                        <input type="hidden" name="csrf_token" value="<?= generate_csrf() ?>">
                        <input type="hidden" name="action" value="add_faq">
                        <div class="form-group">
                            <label>Pertanyaan</label>
                            <input type="text" name="faq_q" class="form-control" required>
                        </div>
                        <div class="form-group">
                            <label>Jawaban</label>
                            <textarea name="faq_a" class="form-control" rows="2" required></textarea>
                        </div>
                        <button type="submit" class="btn btn-primary">Tambah</button>
                    </form>
                </details>
            </div>
            <?php endif; ?>
        <?php endif; ?>
        </div>
    </main>

    <script>
        function toggleSidebar() { const s = document.getElementById('sidebar'); const o = document.getElementById('overlay'); s.classList.toggle('active'); o.classList.toggle('active'); }
        function closeSidebar() { const s = document.getElementById('sidebar'); const o = document.getElementById('overlay'); if (s.classList.contains('active')) { s.classList.remove('active'); o.classList.remove('active'); } }

        // Sidebar submenu toggle
        function toggleSubmenu(id) {
            const el = document.getElementById(id);
            const arrow = document.getElementById('arrow' + id.replace('sub', ''));
            if (!el) return;
            el.classList.toggle('open');
            if (arrow) arrow.classList.toggle('open');
            localStorage.setItem('submenu_' + id, el.classList.contains('open') ? '1' : '0');
        }
        (function() {
            ['subSkills', 'subProjects', 'subArticles'].forEach(function(id) {
                const el = document.getElementById(id);
                const arrow = document.getElementById('arrow' + id.replace('sub', ''));
                if (el && localStorage.getItem('submenu_' + id) === '1') {
                    el.classList.add('open');
                    if (arrow) arrow.classList.add('open');
                }
            });
        })();
        
        // Toast notification
        function closeToast() {
            const t = document.getElementById('toast');
            if (t) t.classList.remove('toast-show');
        }
        <?php if ($flash): ?>
        setTimeout(closeToast, 4000);
        <?php endif; ?>

        function animateValue(obj, start, end, duration) {
            let startTimestamp = null;
            const step = (timestamp) => { if (!startTimestamp) startTimestamp = timestamp; const progress = Math.min((timestamp - startTimestamp) / duration, 1); obj.innerHTML = Math.floor(progress * (end - start) + start); if (progress < 1) { window.requestAnimationFrame(step); } };
            window.requestAnimationFrame(step);
        }
        window.addEventListener('load', () => {
            const counters = document.querySelectorAll('.stat-number');
            counters.forEach(counter => { const target = +counter.getAttribute('data-target'); if (target > 0) { animateValue(counter, 0, target, 1500); } });
        });

        // Session Timeout Warning
        var timeoutSec = <?= SESSION_TIMEOUT ?>;
        var warningSec = <?= SESSION_WARNING ?>;
        var remaining = timeoutSec;
        var warningShown = false;
        var countdownInterval;

        function showTimeoutWarning() {
            if (warningShown) return;
            warningShown = true;
            var sec = warningSec;
            var modal = document.createElement('div');
            modal.id = 'sessionModal';
            modal.style.cssText = 'position:fixed;inset:0;background:rgba(0,0,0,0.5);z-index:99999;display:flex;align-items:center;justify-content:center;';
            modal.innerHTML = '<div style="background:white;padding:2rem;border-radius:12px;max-width:400px;text-align:center;box-shadow:0 20px 60px rgba(0,0,0,0.2);">'
                + '<h3 style="margin:0 0 0.5rem;">Sesi Akan Berakhir</h3>'
                + '<p style="color:#64748b;margin-bottom:1.5rem;">Sesi Anda akan habis dalam <strong id="sessionCountdown" style="color:#ef4444;">' + sec + '</strong> detik.</p>'
                + '<div style="display:flex;gap:0.75rem;justify-content:center;">'
                + '<button onclick="extendSession()" style="padding:8px 20px;background:#2563eb;color:white;border:none;border-radius:8px;font-weight:600;cursor:pointer;">Perpanjang Sesi</button>'
                + '<button onclick="window.location.href=\'logout.php\'" style="padding:8px 20px;background:#e2e8f0;color:#475569;border:none;border-radius:8px;font-weight:600;cursor:pointer;">Logout</button>'
                + '</div></div>';
            document.body.appendChild(modal);

            var countdownEl = document.getElementById('sessionCountdown');
            countdownInterval = setInterval(function() {
                sec--;
                if (countdownEl) countdownEl.textContent = sec;
                if (sec <= 0) {
                    clearInterval(countdownInterval);
                    window.location.href = 'logout.php';
                }
            }, 1000);
        }

        function extendSession() {
            var xhr = new XMLHttpRequest();
            xhr.open('GET', 'process/keep_alive.php', true);
            xhr.onload = function() {
                warningShown = false;
                remaining = timeoutSec;
                var modal = document.getElementById('sessionModal');
                if (modal) modal.remove();
                if (countdownInterval) clearInterval(countdownInterval);
            };
            xhr.send();
        }

        // Check session every 10 seconds
        setInterval(function() {
            remaining -= 10;
            if (remaining <= warningSec && !warningShown) {
                showTimeoutWarning();
            }
        }, 10000);
    </script>
    <script src="assets/vendor/jquery/jquery-3.7.1.min.js"></script>
    <script src="assets/vendor/trumbowyg/trumbowyg.min.js"></script>
    <script>
        $(document).ready(function() {
            $('.trumbowyg').trumbowyg({
                btns: [['formatting'], ['strong','em'], ['link'], ['insertImage'], ['unorderedList','orderedList'], ['preformatted'], ['fullscreen']],
                minimalLinks: true
            });
        });
    </script>
</body>
</html>