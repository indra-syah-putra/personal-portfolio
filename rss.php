<?php
require_once 'includes/functions.php';

$data = baca_json();
if ($data === false) { $data = ['site_settings' => [], 'articles' => []]; }

$settings = $data['site_settings'] ?? [];
$articles = $data['articles'] ?? [];

// Sort by date descending
usort($articles, function($a, $b) {
    return strtotime($b['date']) - strtotime($a['date']);
});

$base = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https' : 'http') . '://' . $_SERVER['HTTP_HOST'];
$script_dir = rtrim(dirname($_SERVER['SCRIPT_NAME']), '/\\');
$base .= $script_dir;

header('Content-Type: application/rss+xml; charset=utf-8');
echo '<?xml version="1.0" encoding="UTF-8"?>';
?>
<rss version="2.0" xmlns:atom="http://www.w3.org/2005/Atom">
<channel>
    <title>Myportofolio — Artikel</title>
    <link><?= htmlspecialchars($base) ?>/artikel.php</link>
    <description>Artikel seputar programming, teknologi, dan pengalaman oleh Indra Syah Putra.</description>
    <language>id</language>
    <atom:link href="<?= htmlspecialchars($base) ?>/rss.php" rel="self" type="application/rss+xml"/>
    <?php foreach ($articles as $art): ?>
    <item>
        <title><?= htmlspecialchars($art['title']) ?></title>
        <link><?= htmlspecialchars($base) ?>/detail_artikel.php?id=<?= htmlspecialchars($art['id']) ?></link>
        <guid><?= htmlspecialchars($base) ?>/detail_artikel.php?id=<?= htmlspecialchars($art['id']) ?></guid>
        <pubDate><?= date('r', strtotime($art['date'])) ?></pubDate>
        <description><?= htmlspecialchars(substr($art['content'] ?? '', 0, 300)) ?></description>
    </item>
    <?php endforeach; ?>
</channel>
</rss>
