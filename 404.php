<?php
http_response_code(404);
$title = '404 - Halaman Tidak Ditemukan';
include 'includes/header.php';
?>

<div class="container" style="padding-top: calc(var(--nav-height) + 6rem); padding-bottom: 6rem; text-align: center;">
    
    <!-- Animated 404 illustration -->
    <div style="font-size: 8rem; font-weight: 800; line-height: 1; margin-bottom: 0.5rem; background: linear-gradient(135deg, var(--primary), var(--accent)); -webkit-background-clip: text; -webkit-text-fill-color: transparent; letter-spacing: -5px;">404</div>
    
    <div style="font-size: 3rem; margin-bottom: 1.5rem;">🙈</div>
    
    <h1 style="font-size: 1.8rem; margin-bottom: 0.75rem;">Halaman Tidak Ditemukan</h1>
    
    <p style="color: var(--text-muted); font-size: 1rem; max-width: 450px; margin: 0 auto 2rem; line-height: 1.7;">
        Sepertinya halaman yang kamu cari telah dipindahkan, dihapus, atau mungkin tersesat di alam lain.
    </p>

    <div style="display: flex; gap: 1rem; justify-content: center; flex-wrap: wrap;">
        <a href="index.php" class="btn btn-primary">
            <svg class="icon icon-sm" viewBox="0 0 24 24"><path d="M3 9l9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"></path><polyline points="9 22 9 12 15 12 15 22"></polyline></svg>
            Kembali ke Beranda
        </a>
        <a href="artikel.php" class="btn btn-outline">
            <svg class="icon icon-sm" viewBox="0 0 24 24"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"></path><polyline points="14 2 14 8 20 8"></polyline><line x1="16" y1="13" x2="8" y2="13"></line><line x1="16" y1="17" x2="8" y2="17"></line></svg>
            Cari Artikel
        </a>
    </div>
</div>

<?php include 'includes/footer.php'; ?>
