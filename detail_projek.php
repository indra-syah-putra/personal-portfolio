<?php
require_once 'includes/functions.php';

$project_id = $_GET['id'] ?? null;

if (!$project_id) {
    header("Location: projek.php");
    exit;
}

$data = baca_json();
if ($data === false) { header("Location: projek.php"); exit; }
$projects = $data['projects'] ?? [];
$settings = $data['site_settings'];

$current_project = null;
foreach ($projects as $proj) {
    if ($proj['id'] === $project_id) {
        $current_project = $proj;
        break;
    }
}

if (!$current_project || ($current_project['published'] ?? '1') === '0') {
    header("Location: projek.php");
    exit;
}

// Prev / Next
$project_keys = array_keys($projects);
$current_index = array_search($current_project['id'], array_column($projects, 'id'));
$prev_project = ($current_index > 0) ? $projects[$current_index - 1] : null;
$next_project = ($current_index < count($projects) - 1) ? $projects[$current_index + 1] : null;

// Related (same tech)
$related = [];
$current_techs = array_map('trim', explode(',', $current_project['tech']));
foreach ($projects as $proj) {
    if ($proj['id'] === $current_project['id']) continue;
    $proj_techs = array_map('trim', explode(',', $proj['tech']));
    if (array_intersect($current_techs, $proj_techs)) {
        $related[] = $proj;
    }
}
$related = array_slice($related, 0, 3);

$og_title = $current_project['title'] . ' - Myportofolio';
$og_desc = htmlspecialchars($current_project['desc']);
$og_image = htmlspecialchars($current_project['img']);
$title = $og_title;
include 'includes/header.php';

$techs = explode(',', $current_project['tech']);
?>

<div class="container" style="padding-top: calc(var(--nav-height) + 2rem); padding-bottom: 4rem;">

    <nav class="breadcrumb">
        <a href="projek.php">Projek</a>
        <span class="sep">/</span>
        <span><?= htmlspecialchars($current_project['title']) ?></span>
    </nav>

    <div class="detail-wrapper">

        <div class="detail-img-wrap reveal">
            <img src="<?= htmlspecialchars($current_project['img']) ?>"
                 alt="<?= htmlspecialchars($current_project['title']) ?>"
                 class="detail-img img-zoom" loading="lazy">
            <div class="detail-cat-badge">
                <?= htmlspecialchars($current_project['status']) ?>
            </div>
        </div>

        <div class="detail-header reveal">
            <h1 class="detail-title">
                <?= htmlspecialchars($current_project['title']) ?>
            </h1>
            <div class="detail-meta">
                <svg class="icon icon-sm" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"></circle><polyline points="12 6 12 12 16 14"></polyline></svg>
                <span><?= htmlspecialchars($current_project['date'] ?? '-') ?></span>
                <span class="meta-dot"></span>
                <span><?= htmlspecialchars($current_project['status']) ?></span>
            </div>
        </div>

        <div class="detail-techs reveal">
            <?php foreach ($techs as $t): ?>
            <a href="projek.php?tech=<?= urlencode(trim($t)) ?>" class="skill-tag"><?= trim(htmlspecialchars($t)) ?></a>
            <?php endforeach; ?>
        </div>

        <article class="detail-content reveal">
            <p><?= nl2br(htmlspecialchars($current_project['desc'])) ?></p>
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
            <?php if ($prev_project): ?>
            <a href="detail_projek.php?id=<?= htmlspecialchars($prev_project['id']) ?>" class="pn-card prev">
                <span class="pn-label">&larr; Sebelumnya</span>
                <span class="pn-title"><?= htmlspecialchars($prev_project['title']) ?></span>
            </a>
            <?php else: ?>
            <div style="flex:1;"></div>
            <?php endif; ?>
            <?php if ($next_project): ?>
            <a href="detail_projek.php?id=<?= htmlspecialchars($next_project['id']) ?>" class="pn-card next">
                <span class="pn-label">Selanjutnya &rarr;</span>
                <span class="pn-title"><?= htmlspecialchars($next_project['title']) ?></span>
            </a>
            <?php else: ?>
            <div style="flex:1;"></div>
            <?php endif; ?>
        </div>

        <!-- RELATED -->
        <?php if ($related): ?>
        <div class="detail-related reveal">
            <h3>Projek Terkait</h3>
            <div class="grid-3" style="margin-bottom:0;">
                <?php foreach ($related as $rel): ?>
                <article class="card article-card">
                    <div class="card-img-wrap">
                        <img src="<?= htmlspecialchars($rel['img']) ?>" alt="<?= htmlspecialchars($rel['title']) ?>" loading="lazy">
                        <span class="card-status-badge"><?= htmlspecialchars($rel['status']) ?></span>
                    </div>
                    <div class="card-body">
                        <h3><?= htmlspecialchars($rel['title']) ?></h3>
                        <div class="project-techs">
                            <?php $rtechs = explode(',', $rel['tech']); foreach($rtechs as $rt): ?>
                            <span class="skill-tag"><?= trim(htmlspecialchars($rt)) ?></span>
                            <?php endforeach; ?>
                        </div>
                        <p><?= htmlspecialchars($rel['desc']) ?></p>
                        <div class="card-footer">
                            <a href="detail_projek.php?id=<?= htmlspecialchars($rel['id']) ?>" class="card-action">Detail &rarr;</a>
                        </div>
                    </div>
                </article>
                <?php endforeach; ?>
            </div>
        </div>
        <?php endif; ?>

        <div class="detail-cta reveal">
            <a href="projek.php" class="btn btn-primary">
                <svg class="icon icon-sm" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="15 18 9 12 15 6"></polyline></svg>
                Kembali ke Semua Projek
            </a>
        </div>

    </div>
</div>

<?php include 'includes/footer.php'; ?>
