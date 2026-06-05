<?php
require_once 'includes/functions.php';
$title = 'Tentang - Myportofolio';
include 'includes/header.php';

$data = baca_json();
if ($data === false) { $data = ['site_settings' => []]; }
$settings = $data['site_settings'];
$skills = $settings['skills'] ?? [];
$timeline = $settings['timeline'] ?? [];
$cv_link = $settings['cv_link'] ?? '';
?>

<div class="container" style="padding-top: calc(var(--nav-height) + 2rem); padding-bottom: 4rem;">
    <nav class="breadcrumb" style="justify-content: center;">
        <a href="index.php">Beranda</a>
        <span class="sep">/</span>
        <span>Tentang</span>
    </nav>

    <h2 class="section-title reveal" style="display: block; text-align: center;">Tentang Saya</h2>

    <!-- BIO + SKILLS + CV -->
    <div class="card" style="max-width: 700px; margin: 0 auto;">
        <h3>Profil</h3>
        <p><?= nl2br(htmlspecialchars($settings['about_bio'])) ?></p>
        <div style="margin-top:1.5rem">
            <?php foreach($skills as $skill): ?>
            <span class="skill-tag"><?= htmlspecialchars($skill) ?></span>
            <?php endforeach; ?>
        </div>
        <?php if (!empty($cv_link)): ?>
        <a href="<?= htmlspecialchars($cv_link) ?>" target="_blank" class="btn btn-primary" style="margin-top:1.5rem;">
            <svg class="icon icon-sm" viewBox="0 0 24 24"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"></path><polyline points="14 2 14 8 20 8"></polyline><line x1="16" y1="13" x2="8" y2="13"></line><line x1="16" y1="17" x2="8" y2="17"></line></svg>
            Download CV
        </a>
        <?php endif; ?>
    </div>

    <!-- TIMELINE PENGALAMAN / PENDIDIKAN -->
    <?php if (!empty($timeline)): ?>
    <div style="margin-top: 4rem;">
        <h3 class="section-title" style="display: block; text-align: center; margin-bottom: 2.5rem;">Perjalanan & Pengalaman</h3>
        <div style="max-width: 600px; margin: 0 auto; display: flex; flex-direction: column; gap: 1.5rem;">
            <?php foreach ($timeline as $item): ?>
            <div style="background: var(--bg-card); padding: 1.25rem 1.5rem; border-radius: var(--radius-sm); border: 1px solid var(--border); box-shadow: var(--shadow-sm);">
                <div style="display: flex; align-items: center; gap: 0.75rem; margin-bottom: 0.5rem;">
                    <span style="display: inline-block; padding: 2px 10px; border-radius: 12px; font-size: 0.7rem; font-weight: 700; text-transform: uppercase; letter-spacing: 0.5px; background: var(--primary-bg); color: var(--primary);"><?= $item['type'] === 'experience' ? 'Pengalaman' : 'Pendidikan' ?></span>
                    <small style="color: var(--text-light); font-weight: 600;"><?= htmlspecialchars($item['year']) ?></small>
                </div>
                <h4 style="margin: 0.25rem 0 0.15rem;"><?= htmlspecialchars($item['title']) ?></h4>
                <p style="color: var(--text-muted); font-size: 0.85rem;"><?= htmlspecialchars($item['organization']) ?></p>
                <?php if (!empty($item['desc'])): ?>
                <p style="margin-top: 0.4rem; font-size: 0.85rem; color: var(--text-muted); line-height: 1.6;"><?= htmlspecialchars($item['desc']) ?></p>
                <?php endif; ?>
            </div>
            <?php endforeach; ?>
        </div>
    </div>
    <?php endif; ?>

    <!-- GALERI KEGIATAN & SERTIFIKAT -->
    <?php $gallery = $settings['gallery'] ?? []; if (!empty($gallery)): ?>
    <div style="margin-top: 4rem;">
        <h3 class="section-title" style="display: block; text-align: center; margin-bottom: 2.5rem;">Galeri</h3>
        <div class="gallery-grid">
            <?php foreach ($gallery as $item): ?>
            <div class="gallery-item">
                <?php if (!empty($item['image'])): ?>
                <img src="<?= htmlspecialchars($item['image']) ?>" alt="" loading="lazy">
                <?php endif; ?>
            </div>
            <?php endforeach; ?>
        </div>
    </div>
    <?php endif; ?>
</div>

<?php include 'includes/footer.php'; ?>
