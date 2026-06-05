<?php
if (!isset($title)) $title = 'Myportofolio - Beranda';
 $current = basename($_SERVER['PHP_SELF']);

// Open Graph defaults
 $og_title = $og_title ?? $title;
 $og_desc = $og_desc ?? 'Project, tutorial, pengalaman dan catatan pembelajaran Indra Syah Putra.';
 $og_image = $og_image ?? 'https://picsum.photos/seed/indratech/400/400';
 $og_url = $og_url ?? ((isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https' : 'http') . '://' . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI']);
?>
<!doctype html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($title) ?></title>
    <meta name="description" content="<?= htmlspecialchars($og_desc) ?>">
    <meta property="og:title" content="<?= htmlspecialchars($og_title) ?>">
    <meta property="og:description" content="<?= htmlspecialchars($og_desc) ?>">
    <meta property="og:image" content="<?= htmlspecialchars($og_image) ?>">
    <meta property="og:url" content="<?= htmlspecialchars($og_url) ?>">
    <meta property="og:type" content="website">
    <meta name="twitter:card" content="summary_large_image">
    <meta name="twitter:title" content="<?= htmlspecialchars($og_title) ?>">
    <meta name="twitter:description" content="<?= htmlspecialchars($og_desc) ?>">
    <meta name="twitter:image" content="<?= htmlspecialchars($og_image) ?>">
    
    <link rel="icon" type="image/svg+xml" href="assets/favicon.svg">
    <link rel="alternate" type="application/rss+xml" title="Myportofolio — Artikel" href="rss.php">
    
    <!-- Font Inter -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;600;700&display=swap" rel="stylesheet">

    <link rel="stylesheet" href="assets/style.css">
    <link rel="canonical" href="<?= htmlspecialchars($og_url) ?>">
    <?php if (!isset($no_ld_json)): ?>
    <?php if (strpos($current, 'detail_artikel') !== false && isset($current_article)): ?>
    <script type="application/ld+json">
    {
        "@context": "https://schema.org",
        "@type": "Article",
        "headline": "<?= htmlspecialchars($current_article['title']) ?>",
        "description": "<?= htmlspecialchars($og_desc) ?>",
        "image": "<?= htmlspecialchars($og_image) ?>",
        "datePublished": "<?= htmlspecialchars($current_article['date']) ?>",
        "author": { "@type": "Person", "name": "Indra Syah Putra" }
    }
    </script>
    <?php else: ?>
    <script type="application/ld+json">
    {
        "@context": "https://schema.org",
        "@type": "Person",
        "name": "Indra Syah Putra",
        "url": "<?= htmlspecialchars($og_url) ?>",
        "image": "<?= htmlspecialchars($og_image) ?>",
        "description": "<?= htmlspecialchars($og_desc) ?>"
    }
    </script>
    <?php endif; ?>
    <?php endif; ?>
</head>
<body class="page-<?= str_replace('.php', '', $current) ?> page-fade-in">

    <div class="reading-progress" id="readingProgress"></div>

    <div class="lightbox" id="lightbox" onclick="closeLightbox()">
        <span class="lightbox-close" onclick="event.stopPropagation(); closeLightbox()">&times;</span>
        <img class="lightbox-img" id="lightboxImg" alt="">
        <div class="lightbox-caption" id="lightboxCaption"></div>
    </div>

    <div class="mobile-overlay" id="mobileOverlay" onclick="closeMobileMenu()"></div>

    <header>
        <div class="container">
            <a href="index.php" class="logo">Myportofolio<span style="color:var(--accent)">.</span></a>
            
            <nav>
                <ul class="nav-links" id="navLinks">
                    <li><a href="index.php" class="<?= $current === 'index.php' ? 'active' : '' ?>" onclick="closeMobileMenu()">Beranda</a></li>
                    <li><a href="projek.php" class="<?= $current === 'projek.php' || $current === 'detail_projek.php' ? 'active' : '' ?>" onclick="closeMobileMenu()">Projek</a></li>
                    <li><a href="artikel.php" class="<?= $current === 'artikel.php' || $current === 'detail_artikel.php' ? 'active' : '' ?>" onclick="closeMobileMenu()">Artikel</a></li>
                    <li><a href="tentang.php" class="<?= $current === 'tentang.php' ? 'active' : '' ?>" onclick="closeMobileMenu()">Tentang</a></li>
                    <li><a href="kontak.php" class="<?= $current === 'kontak.php' ? 'active' : '' ?>" onclick="closeMobileMenu()">Kontak</a></li>
                </ul>
            </nav>

            <div class="nav-actions">
                <!-- Theme Toggle Button -->
                <button id="themeToggle" class="theme-btn" aria-label="Toggle Dark Mode">
                    <svg class="icon icon-sm" viewBox="0 0 24 24"><path d="M21 12.79A9 9 0 1 1 11.21 3 7 7 0 0 0 21 12.79z"></path></svg>
                </button>
                
                <!-- Mobile Menu Button -->
                <button class="menu-toggle" onclick="toggleMenu()" aria-label="Menu">
                    <svg class="icon" viewBox="0 0 24 24"><line x1="3" y1="12" x2="21" y2="12"></line><line x1="3" y1="6" x2="21" y2="6"></line><line x1="3" y1="18" x2="21" y2="18"></line></svg>
                </button>
            </div>
        </div>
    </header>

    <main>