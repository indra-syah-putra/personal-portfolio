<?php
require_once 'includes/functions.php';
 $title = 'Projek - Myportofolio';
 $sort = $_GET['sort'] ?? 'terbaru';
 $selected_cat = $_GET['cat'] ?? '';

include 'includes/header.php';

 $data = baca_json();
if ($data === false) { $data = ['site_settings' => [], 'projects' => []]; }
 $settings = $data['site_settings'];
 $projects = $data['projects'] ?? [];

// Filter hanya yang published
 $projects = array_values(array_filter($projects, fn($p) => ($p['published'] ?? '1') !== '0'));

// Filter by category
if (!empty($selected_cat)) {
    $projects = array_filter($projects, function($proj) use ($selected_cat) {
        return ($proj['category'] ?? '') === $selected_cat;
    });
    $projects = array_values($projects);
}

// Sort
if ($sort === 'terlama') {
    usort($projects, fn($a, $b) => strtotime($a['date']) - strtotime($b['date']));
} else {
    usort($projects, fn($a, $b) => strtotime($b['date']) - strtotime($a['date']));
}

// Pagination
 $per_page = 6;
 $total = count($projects);
 $page_no = max(1, (int)($_GET['page'] ?? 1));
 $total_pages = max(1, ceil($total / $per_page));
 $offset = ($page_no - 1) * $per_page;
 $page_projects = array_slice($projects, $offset, $per_page);

 $query_params = [];
if (!empty($selected_cat)) $query_params['cat'] = $selected_cat;
if ($sort !== 'terbaru') $query_params['sort'] = $sort;
 $query_base = http_build_query($query_params);
if ($query_base) $query_base .= '&';
?>
<div class="container" style="padding-top: calc(var(--nav-height) + 2rem); padding-bottom: 4rem;">

    <nav class="breadcrumb" style="justify-content: center;">
        <a href="index.php">Beranda</a>
        <span class="sep">/</span>
        <span>Projek</span>
    </nav>

    <div class="page-header reveal">
        <h1>Semua Projek</h1>
        <p><?= htmlspecialchars($settings['projek_desc'] ?? 'Kumpulan project dan karya terbaik yang pernah saya kerjakan.') ?></p>
    </div>

    <!-- FILTER + SORT -->
    <div style="display:flex; flex-wrap:wrap; justify-content:center; align-items:center; gap:1rem; margin-bottom:1.5rem;">
        <select onchange="window.location=this.value" style="padding:0.5rem 1rem; border-radius:20px; border:1.5px solid var(--border); background:var(--bg-card); color:var(--text-main); font-size:0.85rem; font-weight:600; cursor:pointer;">
            <?php $proj_cats = $settings['project_categories'] ?? []; ?>
            <option value="?<?= $sort !== 'terbaru' ? 'sort='.$sort : '' ?>" <?= empty($selected_cat) ? 'selected' : '' ?>>Semua</option>
            <?php foreach ($proj_cats as $cat): ?>
            <option value="?cat=<?= urlencode($cat) ?><?= $sort !== 'terbaru' ? '&sort='.$sort : '' ?>" <?= $selected_cat === $cat ? 'selected' : '' ?>>
                <?= htmlspecialchars($cat) ?>
            </option>
            <?php endforeach; ?>
        </select>
        <select onchange="window.location=this.value" style="padding:0.5rem 1rem; border-radius:20px; border:1.5px solid var(--border); background:var(--bg-card); color:var(--text-main); font-size:0.85rem; font-weight:600; cursor:pointer;">
            <option value="?<?= $query_base ?>sort=terbaru" <?= $sort === 'terbaru' ? 'selected' : '' ?>>Terbaru</option>
            <option value="?<?= $query_base ?>sort=terlama" <?= $sort === 'terlama' ? 'selected' : '' ?>>Terlama</option>
        </select>
    </div>

    <?php if(empty($page_projects)): ?>
        <div class="empty-state reveal">
            <p>Belum ada projek yang ditampilkan.</p>
        </div>
    <?php else: ?>
        <div class="grid-3">
            <?php foreach($page_projects as $proj): ?>
            <article class="card article-card reveal">
                <div class="card-img-wrap">
                    <img src="<?= htmlspecialchars($proj['img']) ?>" alt="<?= htmlspecialchars($proj['title']) ?>" loading="lazy">
                    <span class="card-status-badge"><?= htmlspecialchars($proj['status']) ?></span>
                </div>
                <div class="card-body">
                    <h3><?= htmlspecialchars($proj['title']) ?></h3>
                    <div class="project-techs">
                        <?php 
                            $techs = explode(',', $proj['tech']);
                            foreach($techs as $t): 
                        ?>
                        <span class="skill-tag"><?= trim(htmlspecialchars($t)) ?></span>
                        <?php endforeach; ?>
                    </div>
                    <p><?= htmlspecialchars($proj['desc']) ?></p>
                    <div class="card-footer">
                        <a href="detail_projek.php?id=<?= htmlspecialchars($proj['id']) ?>" class="card-action">Detail Projek &rarr;</a>
                        <a href="<?= htmlspecialchars($proj['link']) ?>" target="_blank" class="card-action" style="color:var(--text-muted);font-weight:500;">
                            Demo
                            <svg class="icon icon-sm" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M18 13v6a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V8a2 2 0 0 1 2-2h6"></path><polyline points="15 3 21 3 21 9"></polyline><line x1="10" y1="14" x2="21" y2="3"></line></svg>
                        </a>
                    </div>
                </div>
            </article>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>

    <!-- PAGINATION -->
    <?php if ($total_pages > 1): ?>
    <div class="pagination">
        <?php if ($page_no > 1): ?>
        <a href="projek.php?<?= $query_base ?>page=<?= $page_no - 1 ?>" class="prev-next">&larr; Sebelumnya</a>
        <?php endif; ?>
        <?php for ($i = 1; $i <= $total_pages; $i++): ?>
            <?php if ($i === $page_no): ?>
                <span class="current"><?= $i ?></span>
            <?php else: ?>
                <a href="projek.php?<?= $query_base ?>page=<?= $i ?>"><?= $i ?></a>
            <?php endif; ?>
        <?php endfor; ?>
        <?php if ($page_no < $total_pages): ?>
        <a href="projek.php?<?= $query_base ?>page=<?= $page_no + 1 ?>" class="prev-next">Selanjutnya &rarr;</a>
        <?php endif; ?>
    </div>
    <?php endif; ?>
</div>

<?php include 'includes/footer.php'; ?>