<?php
require_once 'includes/functions.php';

 $article_id = $_GET['id'] ?? null;

if (!$article_id) {
    header("Location: artikel.php");
    exit;
}

 $data = baca_json();
if ($data === false) { header("Location: artikel.php"); exit; }
 $articles = $data['articles'] ?? [];

// Cari artikel yang sesuai dengan ID
 $current_article = null;
foreach ($articles as $art) {
    if ($art['id'] === $article_id) {
        $current_article = $art;
        break;
    }
}

// Jika artikel tidak ditemukan atau draft, lempar ke halaman artikel
if (!$current_article || ($current_article['published'] ?? '1') === '0') {
    header("Location: artikel.php");
    exit;
}

// --- VIEW COUNTER (unique per session) ---
$current_index_ref = null;
foreach ($articles as $i => $art) {
    if ($art['id'] === $article_id) {
        $current_index_ref = $i;
        break;
    }
}
if ($current_index_ref !== null) {
    if (session_status() === PHP_SESSION_NONE) session_start();
    if (!isset($_SESSION['viewed_articles'])) $_SESSION['viewed_articles'] = [];
    if (!in_array($article_id, $_SESSION['viewed_articles'])) {
        $articles[$current_index_ref]['views'] = ($articles[$current_index_ref]['views'] ?? 0) + 1;
        $data['articles'] = $articles;
        tulis_json($data);
        $_SESSION['viewed_articles'][] = $article_id;
    }
    $current_article = $articles[$current_index_ref];
}

// --- READING TIME ---
 $word_count = str_word_count(strip_tags($current_article['content']));
 $read_time = max(1, ceil($word_count / 200));

// --- PREV / NEXT ---
 $article_keys = array_keys($articles);
 $current_index = array_search($current_article['id'], array_column($articles, 'id'));
 $prev_article = ($current_index > 0) ? $articles[$current_index - 1] : null;
 $next_article = ($current_index < count($articles) - 1) ? $articles[$current_index + 1] : null;

// --- RELATED ARTICLES (same category, exclude current) ---
 $related = [];
foreach ($articles as $art) {
    if ($art['id'] !== $current_article['id'] && ($art['category'] ?? 'Umum') === ($current_article['category'] ?? 'Umum')) {
        $related[] = $art;
    }
}
 $related = array_slice($related, 0, 3);

// --- OG TAGS ---
 $og_title = $current_article['title'] . ' - Myportofolio';
 $og_desc = substr(htmlspecialchars($current_article['content']), 0, 160);
 $og_image = htmlspecialchars($current_article['img']);
 $title = $og_title;
include 'includes/header.php';
?>

<div class="container" style="padding-top: calc(var(--nav-height) + 2rem); padding-bottom: 4rem;">
    
    <nav class="breadcrumb">
        <a href="artikel.php">Artikel</a>
        <span class="sep">/</span>
        <span><?= htmlspecialchars($current_article['title']) ?></span>
    </nav>

    <div class="detail-wrapper">
        
        <div class="detail-img-wrap reveal">
            <img src="<?= htmlspecialchars($current_article['img'])?>" 
                 alt="<?= htmlspecialchars($current_article['title'])?>" 
                 class="detail-img img-zoom" loading="lazy">
            <div class="detail-cat-badge">
                <?= htmlspecialchars($current_article['category'] ?? 'Umum') ?>
            </div>
        </div>

        <div class="detail-header reveal">
            <h1 class="detail-title">
                <?= htmlspecialchars($current_article['title']) ?>
            </h1>
            <div class="detail-meta">
                <svg class="icon icon-sm" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="4" width="18" height="18" rx="2" ry="2"></rect><line x1="16" y1="2" x2="16" y2="6"></line><line x1="8" y1="2" x2="8" y2="6"></line><line x1="3" y1="10" x2="21" y2="10"></line></svg>
                <span><?= htmlspecialchars($current_article['date']) ?></span>
                <span class="meta-dot"></span>
                <span><?= $read_time ?> menit baca</span>
                <span class="meta-dot"></span>
                <span>Indra Syah Putra</span>
                <span class="meta-dot"></span>
                <span>
                    <svg class="icon icon-sm" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="vertical-align:middle;"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"></path><circle cx="12" cy="12" r="3"></circle></svg>
                    <?= $current_article['views'] ?? 0 ?> dilihat
                </span>
            </div>
        </div>

        <article class="detail-content reveal">
            <?= $current_article['content'] ?>
        </article>

        <!-- SHARE -->
        <div class="detail-share reveal">
            <span class="label">Bagikan:</span>
            <a href="https://wa.me/?text=<?= urlencode($og_title . ' - ' . $og_url) ?>" target="_blank" class="share-btn wa">
                <svg class="icon icon-sm" viewBox="0 0 24 24" fill="currentColor"><path d="M21 11.5a8.38 8.38 0 0 1-.9 3.8 8.5 8.5 0 0 1-7.6 4.7 8.38 8.38 0 0 1-3.8-.9L3 21l1.9-5.7a8.38 8.38 0 0 1-.9-3.8 8.5 8.5 0 0 1 4.7-7.6 8.38 8.38 0 0 1 3.8-.9h.5a8.48 8.48 0 0 1 8 8v.5z"></path></svg>
                WhatsApp
            </a>
            <a href="https://www.facebook.com/sharer/sharer.php?u=<?= urlencode($og_url) ?>" target="_blank" class="share-btn fb">
                <svg class="icon icon-sm" viewBox="0 0 24 24" fill="currentColor"><path d="M18 2h-3a5 5 0 0 0-5 5v3H7v4h3v8h4v-8h3l1-4h-4V7a1 1 0 0 1 1-1h3z"></path></svg>
                Facebook
            </a>
            <a href="https://twitter.com/intent/tweet?text=<?= urlencode($og_title) ?>&url=<?= urlencode($og_url) ?>" target="_blank" class="share-btn x">
                <svg class="icon icon-sm" viewBox="0 0 24 24" fill="currentColor"><path d="M23 3a10.9 10.9 0 0 1-3.14 1.53 4.48 4.48 0 0 0-7.86 3v1A10.66 10.66 0 0 1 3 4s-4 9 5 13a11.64 11.64 0 0 1-7 2c9 5 20 0 20-11.5a4.5 4.5 0 0 0-.08-.83A7.72 7.72 0 0 0 23 3z"></path></svg>
                X / Twitter
            </a>
        </div>

        <!-- PREV / NEXT -->
        <div class="detail-prevnext reveal">
            <?php if ($prev_article): ?>
            <a href="detail_artikel.php?id=<?= htmlspecialchars($prev_article['id']) ?>" class="pn-card prev">
                <span class="pn-label">&larr; Sebelumnya</span>
                <span class="pn-title"><?= htmlspecialchars($prev_article['title']) ?></span>
            </a>
            <?php else: ?>
            <div style="flex:1;"></div>
            <?php endif; ?>
            <?php if ($next_article): ?>
            <a href="detail_artikel.php?id=<?= htmlspecialchars($next_article['id']) ?>" class="pn-card next">
                <span class="pn-label">Selanjutnya &rarr;</span>
                <span class="pn-title"><?= htmlspecialchars($next_article['title']) ?></span>
            </a>
            <?php else: ?>
            <div style="flex:1;"></div>
            <?php endif; ?>
        </div>

        <!-- RELATED -->
        <?php if ($related): ?>
        <div class="detail-related reveal">
            <h3>Artikel Terkait</h3>
            <div class="grid-3" style="margin-bottom:0;">
                <?php foreach ($related as $rel): ?>
                <article class="card article-card">
                    <div class="card-img-wrap">
                        <img src="<?= htmlspecialchars($rel['img']) ?>" alt="<?= htmlspecialchars($rel['title']) ?>" loading="lazy">
                    </div>
                    <div class="card-body">
                        <span class="card-cat-badge"><?= htmlspecialchars($rel['category'] ?? 'Umum') ?></span>
                        <h3 style="font-size:1rem;"><?= htmlspecialchars($rel['title']) ?></h3>
                        <p style="font-size:0.85rem;"><?= substr(htmlspecialchars(strip_tags($rel['content'])), 0, 80) ?>...</p>
                        <div class="card-footer">
                            <a href="detail_artikel.php?id=<?= htmlspecialchars($rel['id']) ?>" class="card-action">Baca Selengkapnya &rarr;</a>
                        </div>
                    </div>
                </article>
                <?php endforeach; ?>
            </div>
        </div>
        <?php endif; ?>

        <div class="detail-cta reveal">
            <a href="artikel.php" class="btn btn-primary">
                <svg class="icon icon-sm" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="15 18 9 12 15 6"></polyline></svg>
                Kembali ke Semua Artikel
            </a>
        </div>

    </div>
</div>

<?php include 'includes/footer.php'; ?>