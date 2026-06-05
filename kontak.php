<?php
require_once 'includes/functions.php';
$title = 'Kontak - Myportofolio';
include 'includes/header.php';

$data = baca_json();
if ($data === false) { $data = ['site_settings' => []]; }
$settings = $data['site_settings'];

// --- LOGIKA MEMBUAT LINK OTOMATIS ---
$email = htmlspecialchars($settings['contact_email']);
$insta_raw = $settings['contact_insta'];
$insta_link = filter_var($insta_raw, FILTER_VALIDATE_URL) ? $insta_raw : "https://instagram.com/" . ltrim($insta_raw, '@');

$github_raw = $settings['contact_github'];
$github_link = filter_var($github_raw, FILTER_VALIDATE_URL) ? $github_raw : "https://github.com/" . ltrim($github_raw, '@');

// --- CEK STATUS PESAN (Dari session process_kontak.php jika ada) ---
$status = $_SESSION['contact_status'] ?? '';
$message = '';
if ($status === 'success') {
    $message = '<div style="background:#d1fae5; color:#065f46; padding:1rem; border-radius:8px; margin-bottom:1rem;">Pesan berhasil dikirim! Admin akan segera membacanya.</div>';
    unset($_SESSION['contact_status']);
} elseif ($status === 'error') {
    $message = '<div style="background:#fee2e2; color:#991b1b; padding:1rem; border-radius:8px; margin-bottom:1rem;">Terjadi kesalahan saat mengirim pesan.</div>';
    unset($_SESSION['contact_status']);
}
?>

<div class="container" style="padding-top: calc(var(--nav-height) + 2rem); padding-bottom: 4rem; text-align: center;">
    
    <nav class="breadcrumb" style="justify-content: center;">
        <a href="index.php">Beranda</a>
        <span class="sep">/</span>
        <span>Kontak</span>
    </nav>

    <h2 class="section-title reveal"><?= htmlspecialchars($settings['contact_title'] ?? 'Hubungi Saya') ?></h2>
    <p style="text-align: center; max-width: 600px; margin: 0 auto 3rem auto; color: var(--text-muted);">
        <?= htmlspecialchars($settings['contact_desc']) ?>
    </p>
    
    <?= $message ?>

    <div class="contact-wrapper">
        <!-- BAGIAN KIRI: LOGO SOSIAL MEDIA -->
        <div class="contact-info" style="text-align: center; display: flex; flex-direction: column; align-items: center; justify-content: center;">
            <h3 style="margin-bottom: 1.5rem;">Connect With Me</h3>
            
            <div class="social-logos-container">
                <a href="mailto:<?= $email ?>" class="social-box email-box" title="Kirim Email: <?= $email ?>">
                    <svg width="40" height="40" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"><path d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2 2z"></path><polyline points="22,6 12,13 2,6"></polyline></svg>
                </a>
                <a href="<?= $insta_link ?>" target="_blank" class="social-box insta-box" title="Instagram: <?= htmlspecialchars($insta_raw) ?>">
                    <svg width="40" height="40" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"><rect x="2" y="2" width="20" height="20" rx="5" ry="5"></rect><path d="M16 11.37A4 4 0 1 1 12.63 8 4 4 0 0 0 16 11.37z"></path><line x1="17.5" y1="6.5" x2="17.51" y2="6.5"></line></svg>
                </a>
                <a href="<?= $github_link ?>" target="_blank" class="social-box github-box" title="Github: <?= htmlspecialchars($github_raw) ?>">
                    <svg width="40" height="40" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"><path d="M9 19c-5 1.5-5-2.5-7-3m14 6v-3.87a3.37 3.37 0 0 0-.94-2.61c3.14-.35 6.44-1.54 6.44-7A5.44 5.44 0 0 0 20 4.77 5.07 5.07 0 0 0 19.91 1S18.73.65 16 2.48a13.38 13.38 0 0 0-7 0C6.27.65 5.09 1 5.09 1A5.07 5.07 0 0 0 5 4.77a5.44 5.44 0 0 0-1.5 3.78c0 5.42 3.3 6.61 6.44 7A3.37 3.37 0 0 0 9 18.13V22"></path></svg>
                </a>
            </div>

            <p style="margin-top: 2rem; font-size: 0.9rem; color: var(--text-muted);">
                Klik logo di atas untuk terhubung!
            </p>
        </div>

        <!-- BAGIAN KANAN: FORM KONTAK -->
        <form action="process/kontak.php" method="POST" id="contactForm">
            <div style="position:absolute;left:-9999px;" aria-hidden="true">
                <input type="text" name="website" tabindex="-1" autocomplete="off">
            </div>
            <div class="form-group">
                <label>Nama Lengkap</label>
                <input type="text" name="nama" id="fieldNama" class="form-control" placeholder="Nama kamu..." required>
                <small class="form-error" id="errNama" style="display:none; color:#ef4444; font-size:0.8rem;"></small>
            </div>
            <div class="form-group">
                <label>Email</label>
                <input type="email" name="email" id="fieldEmail" class="form-control" placeholder="email@domain.com" required>
                <small class="form-error" id="errEmail" style="display:none; color:#ef4444; font-size:0.8rem;"></small>
            </div>
            <div class="form-group">
                <label>Pesan</label>
                <textarea name="pesan" id="fieldPesan" class="form-control" rows="4" placeholder="Tulis pesanmu di sini..." required></textarea>
                <small class="form-error" id="errPesan" style="display:none; color:#ef4444; font-size:0.8rem;"></small>
            </div>
            <button type="submit" class="btn btn-primary" style="width: 100%;">Kirim Pesan</button>
        </form>
    </div>

    <!-- FAQ SINGKAT -->
    <?php $faq = $settings['faq'] ?? []; ?>
    <?php if (!empty($faq)): ?>
    <div style="max-width: 700px; margin: 3rem auto 0; text-align: left;">
        <h3 style="text-align: center; margin-bottom: 1.5rem;">Pertanyaan Umum (FAQ)</h3>
        <div style="display: grid; gap: 1rem;">
            <?php foreach ($faq as $item): ?>
            <details style="background:var(--bg-card); padding:1rem 1.5rem; border-radius:var(--radius-sm); border:1px solid var(--border); cursor:pointer;">
                <summary style="font-weight:600;"><?= htmlspecialchars($item['q']) ?></summary>
                <p style="margin-top:0.75rem; color:var(--text-muted); font-size:0.9rem;"><?= nl2br(htmlspecialchars($item['a'])) ?></p>
            </details>
            <?php endforeach; ?>
        </div>
    </div>
    <?php endif; ?>
</div>

<!-- CLIENT-SIDE VALIDATION -->
<script>
document.getElementById('contactForm').addEventListener('submit', function(e) {
    let valid = true;
    const nama = document.getElementById('fieldNama');
    const email = document.getElementById('fieldEmail');
    const pesan = document.getElementById('fieldPesan');
    const errNama = document.getElementById('errNama');
    const errEmail = document.getElementById('errEmail');
    const errPesan = document.getElementById('errPesan');
    
    errNama.style.display = 'none';
    errEmail.style.display = 'none';
    errPesan.style.display = 'none';
    
    if (nama.value.trim().length < 2) {
        errNama.textContent = 'Nama minimal 2 karakter.';
        errNama.style.display = 'block';
        valid = false;
    }
    if (!/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(email.value)) {
        errEmail.textContent = 'Format email tidak valid.';
        errEmail.style.display = 'block';
        valid = false;
    }
    if (pesan.value.trim().length < 5) {
        errPesan.textContent = 'Pesan minimal 5 karakter.';
        errPesan.style.display = 'block';
        valid = false;
    }
    if (!valid) e.preventDefault();
});
</script>

<?php include 'includes/footer.php'; ?>