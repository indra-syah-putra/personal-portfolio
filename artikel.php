<?php
require_once 'includes/functions.php';
 $title = 'Myportofolio - Artikel';
 $search_q = trim($_GET['q'] ?? '');
 $selected_cat = $_GET['cat'] ?? '';
 $sort = $_GET['sort'] ?? 'terbaru';

function highlight($text, $q) {
    $text = strip_tags($text);
    if (empty($q)) return htmlspecialchars($text);
    return preg_replace('/(' . preg_quote($q, '/') . ')/i', '<mark style="background:#fef08a;color:#0f172a;padding:0 2px;border-radius:2px;">$1</mark>', htmlspecialchars($text));
}

include 'includes/header.php';

 $data = baca_json();
if ($data === false) { $data = ['site_settings' => [], 'articles' => []]; }
 $settings = $data['site_settings'];
 $articles = $data['articles'] ?? [];
 $categories = $settings['categories'] ?? [];

// Filter hanya yang published
 $articles = array_values(array_filter($articles, fn($a) => ($a['published'] ?? '1') !== '0'));

// Filter + Search
 $filtered = $articles;

if (!empty($selected_cat)) {
    $filtered = array_filter($filtered, function($art) use ($selected_cat) {
        return ($art['category'] ?? 'Umum') === $selected_cat;
    });
}

if (!empty($search_q)) {
    $sq = strtolower($search_q);
    $filtered = array_filter($filtered, function($art) use ($sq) {
        return str_contains(strtolower($art['title']), $sq) || str_contains(strtolower($art['content']), $sq);
    });
}

// Urutkan
if ($sort === 'terlama') {
    usort($filtered, function($a, $b) {
        return strtotime($a['date']) - strtotime($b['date']);
    });
} else {
    usort($filtered, function($a, $b) {
        return strtotime($b['date']) - strtotime($a['date']);
    });
}
$filtered = array_values($filtered);

// Pagination
 $per_page = 6;
 $total = count($filtered);
 $page_no = max(1, (int)($_GET['page'] ?? 1));
 $total_pages = max(1, ceil($total / $per_page));
 $offset = ($page_no - 1) * $per_page;
 $page_articles = array_slice($filtered, $offset, $per_page);

// Build query string for pagination (preserve filters)
 $query_params = [];
if (!empty($selected_cat)) $query_params['cat'] = $selected_cat;
if (!empty($search_q)) $query_params['q'] = $search_q;
if ($sort !== 'terbaru') $query_params['sort'] = $sort;
 $query_base = http_build_query($query_params);
if ($query_base) $query_base .= '&';
?>

<div class="container" style="padding-top: calc(var(--nav-height) + 2rem); padding-bottom: 4rem;">
    <nav class="breadcrumb" style="justify-content: center;">
        <a href="index.php">Beranda</a>
        <span class="sep">/</span>
        <span>Artikel</span>
    </nav>

    <div class="page-header reveal">
        <h1>Semua Artikel</h1>
        <p>Kumpulan tulisan seputar programming, teknologi, dan pengalaman.</p>
    </div>

    <!-- SEARCH BOX -->
    <form action="artikel.php" method="GET" class="search-box reveal" style="margin-bottom:1.5rem;">
        <?php if (!empty($selected_cat)): ?>
        <input type="hidden" name="cat" value="<?= htmlspecialchars($selected_cat) ?>">
        <?php endif; ?>
        <input type="text" name="q" placeholder="Cari artikel..." value="<?= htmlspecialchars($search_q) ?>">
        <button type="submit" aria-label="Cari">
            <svg class="icon icon-sm" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="11" cy="11" r="8"></circle><line x1="21" y1="21" x2="16.65" y2="16.65"></line></svg>
        </button>
    </form>

    <!-- FILTER KATEGORI + SORT -->
    <div style="display:flex; justify-content:center; flex-wrap:wrap; gap:0.75rem; margin-bottom:1.5rem;">
        <select onchange="window.location=this.value" style="padding:0.5rem 1rem; border-radius:20px; border:1.5px solid var(--border); background:var(--bg-card); color:var(--text-main); font-size:0.85rem; font-weight:600; cursor:pointer;">
            <?php
            $cat_params = [];
            if (!empty($search_q)) $cat_params['q'] = $search_q;
            if ($sort !== 'terbaru') $cat_params['sort'] = $sort;
            $cat_base = http_build_query($cat_params);
            ?>
            <option value="artikel.php<?= $cat_base ? '?'.$cat_base : '' ?>" <?= empty($selected_cat) ? 'selected' : '' ?>>Semua</option>
            <?php foreach($categories as $cat): ?>
            <?php $cat_params['cat'] = $cat; ?>
            <option value="artikel.php?<?= http_build_query($cat_params) ?>" <?= $selected_cat === $cat ? 'selected' : '' ?>>
                <?= htmlspecialchars($cat) ?>
            </option>
            <?php endforeach; ?>
        </select>
        <select onchange="window.location=this.value" style="padding:0.5rem 1rem; border-radius:20px; border:1.5px solid var(--border); background:var(--bg-card); color:var(--text-main); font-size:0.85rem; font-weight:600; cursor:pointer;">
            <?php
            $sort_params = [];
            if (!empty($selected_cat)) $sort_params['cat'] = $selected_cat;
            if (!empty($search_q)) $sort_params['q'] = $search_q;
            $sort_base = http_build_query($sort_params);
            ?>
            <option value="?<?= $sort_base ? $sort_base.'&' : '' ?>sort=terbaru" <?= $sort === 'terbaru' ? 'selected' : '' ?>>Terbaru</option>
            <option value="?<?= $sort_base ? $sort_base.'&' : '' ?>sort=terlama" <?= $sort === 'terlama' ? 'selected' : '' ?>>Terlama</option>
        </select>
    </div>

    <?php if (!empty($search_q)): ?>
    <p style="text-align:center; color:var(--text-muted); margin-bottom:1.5rem;">
        Hasil pencarian "<strong><?= htmlspecialchars($search_q) ?></strong>" — <?= $total ?> ditemukan
    </p>
    <?php endif; ?>

    <div class="grid-3">
        <?php if(empty($page_articles)): ?>
            <div class="empty-state reveal">
                <p>Tidak ada artikel ditemukan.</p>
                <a href="artikel.php" class="btn btn-primary">Lihat Semua</a>
            </div>
        <?php else: ?>
            <?php foreach($page_articles as $art): ?>
            <article class="card article-card reveal">
                <div class="card-img-wrap">
                    <img src="<?= htmlspecialchars($art['img']) ?>" alt="<?= htmlspecialchars($art['title']) ?>" loading="lazy">
                </div>
                <div class="card-body">
                    <span class="card-cat-badge"><?= htmlspecialchars($art['category'] ?? 'Umum') ?></span>
                    <h3><?= highlight($art['title'], $search_q) ?></h3>

                    <div class="article-meta">
                        <svg class="icon icon-sm" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"></circle><polyline points="12 6 12 12 16 14"></polyline></svg>
                        <?= htmlspecialchars($art['date']) ?>
                    </div>

                    <p><?= highlight($art['content'], $search_q) ?></p>

                    <div class="card-footer">
                        <span class="article-meta">
                            <svg class="icon icon-sm" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"></path><circle cx="12" cy="12" r="3"></circle></svg>
                            <?= $art['views'] ?? 0 ?>x
                        </span>
                        <a href="detail_artikel.php?id=<?= htmlspecialchars($art['id']) ?>" class="card-action">
                            Baca Selengkapnya &rarr;
                        </a>
                    </div>
                </div>
            </article>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>

    <!-- PAGINATION -->
    <?php if ($total_pages > 1): ?>
    <div class="pagination">
        <?php if ($page_no > 1): ?>
        <a href="artikel.php?<?= $query_base ?>page=<?= $page_no - 1 ?>" class="prev-next">&larr; Sebelumnya</a>
        <?php endif; ?>
        <?php for ($i = 1; $i <= $total_pages; $i++): ?>
            <?php if ($i === $page_no): ?>
                <span class="current"><?= $i ?></span>
            <?php else: ?>
                <a href="artikel.php?<?= $query_base ?>page=<?= $i ?>"><?= $i ?></a>
            <?php endif; ?>
        <?php endfor; ?>
        <?php if ($page_no < $total_pages): ?>
        <a href="artikel.php?<?= $query_base ?>page=<?= $page_no + 1 ?>" class="prev-next">Selanjutnya &rarr;</a>
        <?php endif; ?>
    </div>
    <?php endif; ?>
</div>

<?php include 'includes/footer.php'; ?>