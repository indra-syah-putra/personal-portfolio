<?php
require_once 'includes/functions.php';
 $title = 'Myportofolio - Beranda';
include 'includes/header.php';

 $data = baca_json();
if ($data === false) { $data = ['site_settings' => [], 'projects' => [], 'articles' => []]; }
 $settings = $data['site_settings'];
 $projects = $data['projects'] ?? [];
 $articles = $data['articles'] ?? [];

// Ambil data preview (3 terbaru) — hanya yang published
 $published_projects = array_values(array_filter($projects, fn($p) => ($p['published'] ?? '1') !== '0'));
 $published_articles = array_values(array_filter($articles, fn($a) => ($a['published'] ?? '1') !== '0'));
 $latest_projects = array_slice($published_projects, 0, 3);
 $latest_articles = array_slice($published_articles, 0, 3);
 $skills = $settings['skills'] ?? [];
?>

<!-- 1. HERO SECTION -->
<section class="hero container reveal">
    <div class="hero-content">
        <div class="hero-badge">
            <svg class="icon icon-sm" viewBox="0 0 24 24"><path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"></path><circle cx="12" cy="7" r="4"></circle></svg>
            <?= htmlspecialchars($settings['hero_badge'] ?? 'Indra Syah Putra') ?>
        </div>
        <h1><?= htmlspecialchars($settings['hero_title']) ?></h1>
        <p><?= htmlspecialchars($settings['hero_desc']) ?></p>
        <div class="hero-actions">
            <a href="projek.php" class="btn btn-primary">
                Lihat Projek
                <svg class="icon icon-sm" viewBox="0 0 24 24"><polyline points="9 18 15 12 9 6"></polyline></svg>
            </a>
            <a href="kontak.php" class="btn btn-outline">Hubungi Saya</a>
        </div>
    </div>
    <div class="hero-image">
        <div class="hero-img-wrapper">
            <img src="<?= htmlspecialchars($settings['hero_img']) ?>" alt="Avatar Indra" class="hero-img-bg" loading="lazy">
        </div>
    </div>
</section>

<!-- 2. PROFIL & SKILLS SECTION -->
<section class="container reveal">
    <div class="about-grid">
        <div>
            <h2 class="section-title">Tentang Saya</h2>
            <div class="about-bio">
                <?= nl2br(htmlspecialchars($settings['about_bio'])) ?>
            </div>
        </div>

        <div class="skills-card">
            <h3>Tech Skills</h3>
            <div class="skills-wrap">
                <?php foreach($skills as $skill): ?>
                    <span class="skill-tag"><?= htmlspecialchars($skill) ?></span>
                <?php endforeach; ?>
            </div>
            <?php if(empty($skills)): ?>
                <p style="color: var(--text-muted); font-size: 0.9rem; margin-top: 1rem;">Belum ada skill ditambahkan.</p>
            <?php endif; ?>
        </div>
    </div>
</section>

<!-- 3. PROJEK TERBARU -->
<section class="container reveal" style="padding-bottom: 4rem;">
    <div style="display: flex; justify-content: space-between; align-items: flex-end; margin-bottom: 2rem; flex-wrap: wrap; gap: 1rem;">
        <h2 class="section-title" style="margin-bottom: 0;">Projek Terbaru</h2>
        <a href="projek.php" class="btn btn-outline">Lihat Semua &rarr;</a>
    </div>

    <div class="grid-3">
        <?php if(empty($latest_projects)): ?>
            <p style="color:var(--text-muted)">Belum ada projek.</p>
        <?php else: ?>
            <?php foreach($latest_projects as $proj): ?>
            <article class="card article-card reveal">
                <div class="card-img-wrap">
                    <img src="<?= htmlspecialchars($proj['img']) ?>" alt="<?= htmlspecialchars($proj['title']) ?>" loading="lazy">
                    <span class="card-status-badge <?= htmlspecialchars($proj['status']) ?>"><?= htmlspecialchars($proj['status']) ?></span>
                </div>
                <div class="card-body">
                    <h3><?= htmlspecialchars($proj['title']) ?></h3>
                    <div class="project-techs">
                        <?php 
                            $techs = explode(',', $proj['tech']);
                            foreach($techs as $t): 
                        ?>
                        <span class="skill-tag" style="font-size:0.7rem;"><?= trim(htmlspecialchars($t)) ?></span>
                        <?php endforeach; ?>
                    </div>
                    <p><?= htmlspecialchars($proj['desc']) ?></p>
                    <div class="card-footer">
                        <a href="detail_projek.php?id=<?= htmlspecialchars($proj['id']) ?>" class="card-action">Detail Projek &rarr;</a>
                    </div>
                </div>
            </article>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>
</section>

<!-- 4. ARTIKEL TERBARU -->
<section class="container reveal" style="padding-bottom: 4rem;">
    <div style="display: flex; justify-content: space-between; align-items: flex-end; margin-bottom: 2rem; flex-wrap: wrap; gap: 1rem;">
        <h2 class="section-title" style="margin-bottom: 0;">Artikel & Berita</h2>
        <a href="artikel.php" class="btn btn-outline">Baca Semua &rarr;</a>
    </div>

    <div class="grid-3">
            <?php foreach($latest_articles as $art): ?>
            <article class="card article-card reveal">
                <div class="card-img-wrap">
                    <img src="<?= htmlspecialchars($art['img']) ?>" alt="<?= htmlspecialchars($art['title']) ?>" loading="lazy">
                </div>
                <div class="card-body">
                    <span class="card-cat-badge"><?= htmlspecialchars($art['category'] ?? 'Umum') ?></span>
                    <h3><?= htmlspecialchars($art['title']) ?></h3>
                    <p><?= substr(htmlspecialchars(strip_tags($art['content'])), 0, 80) ?>...</p>
                    <div class="card-footer">
                        <a href="detail_artikel.php?id=<?= htmlspecialchars($art['id']) ?>" class="card-action">Baca Selengkapnya &rarr;</a>
                    </div>
                </div>
            </article>
            <?php endforeach; ?>
    </div>
</section>

<?php include 'includes/footer.php'; ?>