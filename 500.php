<?php
http_response_code(500);
$title = '500 — Server Error';
$og_title = 'Server Error - Myportofolio';
include 'includes/header.php';
?>
<div class="container" style="padding-top: calc(var(--nav-height) + 4rem); padding-bottom: 6rem; text-align: center;">
    <div class="page-header" style="max-width: 500px; margin: 0 auto;">
        <h1 style="font-size: 5rem; line-height: 1; margin-bottom: 0.5rem; -webkit-text-fill-color: initial; background: none; color: #ef4444;">500</h1>
        <p style="font-size: 1.2rem; margin-bottom: 0.25rem;">Ups, terjadi kesalahan pada server.</p>
        <p style="color: var(--text-muted); margin-bottom: 2rem;">Coba refresh halaman atau kembali nanti.</p>
        <div style="display: flex; gap: 1rem; justify-content: center; flex-wrap: wrap;">
            <a href="index.php" class="btn btn-primary">&larr; Kembali ke Beranda</a>
            <a href="artikel.php" class="btn btn-outline">Cari Artikel</a>
        </div>
    </div>
</div>
<?php include 'includes/footer.php'; ?>
