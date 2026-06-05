<?php
require_once __DIR__ . '/../includes/functions.php';

cek_admin();
session_write_close();

$action = $_POST['action'] ?? $_GET['action'] ?? '';

function ambil_gambar() {
    $uploaded = upload_gambar($_FILES['img_file'] ?? []);
    if ($uploaded !== false) return $uploaded;
    $url = $_POST['img'] ?? '';
    if (!empty($url)) {
        if (!validate_url($url)) return false;
        return $url;
    }
    return 'https://picsum.photos/seed/project/600/400';
}

// Validasi CSRF untuk semua request
$token = $_POST['csrf_token'] ?? $_GET['csrf_token'] ?? '';
if (!validate_csrf($token)) {
    die('CSRF validation failed.');
}

$data = baca_json();
if ($data === false) {
    die('Gagal membaca data.');
}

// --- SKILLS ---
if ($action === 'update_skills') {
    if (isset($_POST['new_skill']) && !empty($_POST['new_skill'])) {
        $new_skill = $_POST['new_skill'];
        if (!in_array($new_skill, $data['site_settings']['skills'])) {
            $data['site_settings']['skills'][] = $new_skill;
            set_flash('success', 'Skill "' . $new_skill . '" berhasil ditambahkan.');
        } else {
            set_flash('warning', 'Skill "' . $new_skill . '" sudah ada.');
        }
    }
    if (isset($_GET['delete'])) {
        $skill_to_delete = $_GET['delete'];
        $data['site_settings']['skills'] = array_values(array_filter($data['site_settings']['skills'], function($s) use ($skill_to_delete) {
            return $s !== $skill_to_delete;
        }));
        set_flash('success', 'Skill "' . $skill_to_delete . '" berhasil dihapus.');
    }
    tulis_json($data);
    header('Location: ../admin.php?page=skills');
    exit;
}

// --- PROJECT CREATE ---
if ($action === 'create_project') {
    $img = ambil_gambar();
    if ($img === false) {
        set_flash('warning', 'URL gambar tidak valid.');
        header('Location: ../admin.php?page=project_edit');
        exit;
    }
    $tech_str = is_array($_POST['tech'] ?? null) ? implode(', ', $_POST['tech']) : ($_POST['tech'] ?? '');
    $new_project = [
        'id' => uniqid(),
        'title' => $_POST['title'],
        'desc' => $_POST['desc'],
        'img' => $img,
        'tech' => $tech_str,
        'category' => $_POST['category'] ?? '',
        'published' => $_POST['published'] ?? '1',
        'link' => $_POST['link'],
        'status' => $_POST['status'],
        'date' => date('Y-m-d')
    ];
    array_unshift($data['projects'], $new_project);
    tulis_json($data);
    set_flash('success', 'Projek "' . $new_project['title'] . '" berhasil ditambahkan.');
    header('Location: ../admin.php?page=projects');
    exit;
}

// --- PROJECT UPDATE ---
if ($action === 'update_project') {
    $id = $_POST['id'];
    $img = ambil_gambar();
    if ($img === false) {
        set_flash('warning', 'URL gambar tidak valid.');
        header('Location: ../admin.php?page=project_edit&id=' . urlencode($id));
        exit;
    }
    $tech_str = is_array($_POST['tech'] ?? null) ? implode(', ', $_POST['tech']) : ($_POST['tech'] ?? '');
    foreach ($data['projects'] as &$proj) {
        if ($proj['id'] == $id) {
            $proj['title'] = $_POST['title'];
            $proj['desc'] = $_POST['desc'];
            $proj['img'] = $img;
            $proj['tech'] = $tech_str;
            $proj['category'] = $_POST['category'] ?? '';
            $proj['published'] = $_POST['published'] ?? '1';
            $proj['link'] = $_POST['link'];
            $proj['status'] = $_POST['status'];
            $proj['date'] = date('Y-m-d');
            set_flash('success', 'Projek "' . $proj['title'] . '" berhasil diperbarui.');
            break;
        }
    }
    tulis_json($data);
    header('Location: ../admin.php?page=projects');
    exit;
}

// --- PROJECT DELETE ---
if ($action === 'delete_project') {
    $id = $_GET['id'];
    $deleted_title = '';
    foreach ($data['projects'] as $p) {
        if ($p['id'] == $id) {
            $deleted_title = $p['title'];
            if (!empty($p['img']) && strpos($p['img'], 'uploads/') === 0) {
                $file_path = '../' . $p['img'];
                if (file_exists($file_path)) { unlink($file_path); }
            }
            break;
        }
    }
    $data['projects'] = array_values(array_filter($data['projects'], function($p) use ($id) {
        return $p['id'] != $id;
    }));
    tulis_json($data);
    set_flash('success', 'Projek "' . $deleted_title . '" berhasil dihapus.');
    header('Location: ../admin.php?page=projects');
    exit;
}

// --- HOME UPDATE ---
if ($action === 'update_home') {
    if (!empty($_POST['hero_img']) && !validate_url($_POST['hero_img'])) {
        set_flash('warning', 'URL foto tidak valid.');
        header('Location: ../admin.php?page=skills&sub=beranda');
        exit;
    }
    $data['site_settings']['hero_img'] = $_POST['hero_img'];
    $data['site_settings']['hero_badge'] = $_POST['hero_badge'];
    $data['site_settings']['hero_title'] = $_POST['hero_title'];
    $data['site_settings']['hero_desc'] = $_POST['hero_desc'];
    tulis_json($data);
    set_flash('success', 'Beranda berhasil diperbarui.');
    header('Location: ../admin.php?page=skills&sub=beranda');
    exit;
}

// --- ABOUT UPDATE ---
if ($action === 'update_about') {
    $data['site_settings']['about_bio'] = $_POST['about_bio'];
    tulis_json($data);
    set_flash('success', 'Profil berhasil diperbarui.');
    header('Location: ../admin.php?page=skills&sub=profil');
    exit;
}

// --- CONTACT UPDATE ---
if ($action === 'update_contact') {
    $data['site_settings']['contact_email'] = $_POST['contact_email'];
    $data['site_settings']['contact_insta'] = $_POST['contact_insta'];
    $data['site_settings']['contact_github'] = $_POST['contact_github'];
    tulis_json($data);
    set_flash('success', 'Kontak berhasil diperbarui.');
    header('Location: ../admin.php?page=contact');
    exit;
}

// --- ARTICLE CREATE ---
if ($action === 'create_article') {
    $img = ambil_gambar();
    if ($img === false) {
        set_flash('warning', 'URL gambar tidak valid.');
        header('Location: ../admin.php?page=article_edit');
        exit;
    }
    $new_article = [
        'id' => uniqid(),
        'title' => $_POST['title'],
        'content' => $_POST['content'],
        'img' => $img,
        'date' => date('Y-m-d'),
        'category' => $_POST['category'] ?? 'Umum',
        'published' => $_POST['published'] ?? '1'
    ];
    array_unshift($data['articles'], $new_article);
    tulis_json($data);
    set_flash('success', 'Artikel "' . $new_article['title'] . '" berhasil ditambahkan.');
    header('Location: ../admin.php?page=articles');
    exit;
}

// --- ARTICLE UPDATE ---
if ($action === 'update_article') {
    $id = $_POST['id'];
    $img = ambil_gambar();
    if ($img === false) {
        set_flash('warning', 'URL gambar tidak valid.');
        header('Location: ../admin.php?page=article_edit&id=' . urlencode($id));
        exit;
    }
    foreach ($data['articles'] as &$art) {
        if ($art['id'] == $id) {
            $art['title'] = $_POST['title'];
            $art['content'] = $_POST['content'];
            $art['img'] = $img;
            $art['category'] = $_POST['category'] ?? 'Umum';
            $art['published'] = $_POST['published'] ?? '1';
            set_flash('success', 'Artikel "' . $art['title'] . '" berhasil diperbarui.');
            break;
        }
    }
    tulis_json($data);
    header('Location: ../admin.php?page=articles');
    exit;
}

// --- ARTICLE DELETE ---
if ($action === 'delete_article') {
    $id = $_GET['id'];
    $deleted_title = '';
    foreach ($data['articles'] as $a) {
        if ($a['id'] == $id) {
            $deleted_title = $a['title'];
            if (!empty($a['img']) && strpos($a['img'], 'uploads/') === 0) {
                $file_path = '../' . $a['img'];
                if (file_exists($file_path)) { unlink($file_path); }
            }
            break;
        }
    }
    $data['articles'] = array_values(array_filter($data['articles'], function($a) use ($id) {
        return $a['id'] != $id;
    }));
    tulis_json($data);
    set_flash('success', 'Artikel "' . $deleted_title . '" berhasil dihapus.');
    header('Location: ../admin.php?page=articles');
    exit;
}

// --- MESSAGE DELETE ---
if ($action === 'delete_message') {
    $id = $_GET['id'];
    $messages = baca_messages();
    $messages = array_values(array_filter($messages, function($m) use ($id) {
        return $m['id'] != $id;
    }));
    tulis_messages($messages);
    set_flash('success', 'Pesan berhasil dihapus.');
    header('Location: ../admin.php?page=messages');
    exit;
}

// --- SETTINGS ---
if ($action === 'update_settings_projek') {
    $data['site_settings']['projek_desc'] = $_POST['projek_desc'];
    tulis_json($data);
    set_flash('success', 'Pengaturan Projek berhasil disimpan.');
    header('Location: ../admin.php?page=settings&sub=projek');
    exit;
}
if ($action === 'update_settings_contact') {
    $data['site_settings']['contact_desc'] = $_POST['contact_desc'];

    tulis_json($data);
    set_flash('success', 'Pengaturan Kontak berhasil disimpan.');
    header('Location: ../admin.php?page=settings&sub=contact');
    exit;
}

// --- FAQ ---
if ($action === 'update_faq_item') {
    $index = (int)$_POST['index'];
    $faq = $data['site_settings']['faq'] ?? [];
    if (isset($faq[$index])) {
        $faq[$index] = ['q' => trim($_POST['faq_q']), 'a' => trim($_POST['faq_a'])];
        $data['site_settings']['faq'] = $faq;
        tulis_json($data);
        set_flash('success', 'FAQ berhasil diperbarui.');
    }
    header('Location: ../admin.php?page=settings&sub=faq');
    exit;
}

if ($action === 'add_faq') {
    $faq = $data['site_settings']['faq'] ?? [];
    $faq[] = ['q' => trim($_POST['faq_q']), 'a' => trim($_POST['faq_a'])];
    $data['site_settings']['faq'] = $faq;
    tulis_json($data);
    set_flash('success', 'FAQ berhasil ditambahkan.');
    header('Location: ../admin.php?page=settings&sub=faq');
    exit;
}

if ($action === 'delete_faq') {
    $index = (int)$_GET['index'];
    $faq = $data['site_settings']['faq'] ?? [];
    if (isset($faq[$index])) {
        array_splice($faq, $index, 1);
        $data['site_settings']['faq'] = array_values($faq);
        tulis_json($data);
        set_flash('success', 'FAQ berhasil dihapus.');
    }
    header('Location: ../admin.php?page=settings&sub=faq');
    exit;
}

// --- CATEGORIES ---
if ($action === 'update_categories') {
    if (isset($_GET['delete'])) {
        $cat_to_delete = $_GET['delete'];
        $data['site_settings']['categories'] = array_values(array_filter($data['site_settings']['categories'], function($c) use ($cat_to_delete) {
            return $c !== $cat_to_delete;
        }));
        set_flash('success', 'Kategori "' . $cat_to_delete . '" berhasil dihapus.');
    }
    if (isset($_POST['new_category']) && !empty($_POST['new_category'])) {
        $new_cat = $_POST['new_category'];
        if (!in_array($new_cat, $data['site_settings']['categories'])) {
            $data['site_settings']['categories'][] = $new_cat;
            set_flash('success', 'Kategori "' . $new_cat . '" berhasil ditambahkan.');
        } else {
            set_flash('warning', 'Kategori "' . $new_cat . '" sudah ada.');
        }
    }
    tulis_json($data);
    header('Location: ../admin.php?page=articles&sub=kategori');
    exit;
}

// --- PROJECT CATEGORIES ---
if ($action === 'update_project_categories') {
    if (isset($_GET['delete'])) {
        $cat_to_delete = $_GET['delete'];
        $data['site_settings']['project_categories'] = array_values(array_filter($data['site_settings']['project_categories'] ?? [], function($c) use ($cat_to_delete) {
            return $c !== $cat_to_delete;
        }));
        set_flash('success', 'Kategori projek "' . $cat_to_delete . '" berhasil dihapus.');
    }
    if (isset($_POST['new_category']) && !empty($_POST['new_category'])) {
        $new_cat = $_POST['new_category'];
        $existing = $data['site_settings']['project_categories'] ?? [];
        if (!in_array($new_cat, $existing)) {
            $existing[] = $new_cat;
            $data['site_settings']['project_categories'] = $existing;
            set_flash('success', 'Kategori projek "' . $new_cat . '" berhasil ditambahkan.');
        } else {
            set_flash('warning', 'Kategori "' . $new_cat . '" sudah ada.');
        }
    }
    tulis_json($data);
    header('Location: ../admin.php?page=projects&sub=kategori');
    exit;
}

// --- TECH SKILLS ---
if ($action === 'update_tech_skills') {
    if (isset($_GET['delete'])) {
        $tech_to_delete = $_GET['delete'];
        $data['site_settings']['tech_skills'] = array_values(array_filter($data['site_settings']['tech_skills'] ?? [], function($t) use ($tech_to_delete) {
            return $t !== $tech_to_delete;
        }));
        set_flash('success', 'Tech skill "' . $tech_to_delete . '" berhasil dihapus.');
    }
    if (isset($_POST['new_tech']) && !empty($_POST['new_tech'])) {
        $new_tech = $_POST['new_tech'];
        $existing = $data['site_settings']['tech_skills'] ?? [];
        if (!in_array($new_tech, $existing)) {
            $existing[] = $new_tech;
            $data['site_settings']['tech_skills'] = $existing;
            set_flash('success', 'Tech skill "' . $new_tech . '" berhasil ditambahkan.');
        } else {
            set_flash('warning', 'Tech skill "' . $new_tech . '" sudah ada.');
        }
    }
    tulis_json($data);
    header('Location: ../admin.php?page=projects&sub=tech');
    exit;
}

// --- TIMELINE ---
if ($action === 'add_timeline') {
    $tl = $data['site_settings']['timeline'] ?? [];
    $tl[] = [
        'type' => $_POST['tl_type'] ?? 'education',
        'title' => $_POST['tl_title'],
        'organization' => $_POST['tl_org'] ?? '',
        'year' => $_POST['tl_year'] ?? '',
        'desc' => $_POST['tl_desc'] ?? ''
    ];
    $data['site_settings']['timeline'] = $tl;
    tulis_json($data);
    set_flash('success', 'Item timeline berhasil ditambahkan.');
    header('Location: ../admin.php?page=skills&sub=timeline');
    exit;
}

if ($action === 'update_timeline_item') {
    $index = (int)$_POST['index'];
    $tl = $data['site_settings']['timeline'] ?? [];
    if (isset($tl[$index])) {
        $tl[$index] = [
            'type' => $_POST['tl_type'] ?? 'education',
            'title' => $_POST['tl_title'],
            'organization' => $_POST['tl_org'] ?? '',
            'year' => $_POST['tl_year'] ?? '',
            'desc' => $_POST['tl_desc'] ?? ''
        ];
        $data['site_settings']['timeline'] = $tl;
        tulis_json($data);
        set_flash('success', 'Item timeline berhasil diperbarui.');
    }
    header('Location: ../admin.php?page=skills&sub=timeline');
    exit;
}

if ($action === 'delete_timeline') {
    $index = (int)$_GET['index'];
    $tl = $data['site_settings']['timeline'] ?? [];
    if (isset($tl[$index])) {
        array_splice($tl, $index, 1);
        $data['site_settings']['timeline'] = array_values($tl);
        tulis_json($data);
        set_flash('success', 'Item timeline berhasil dihapus.');
    }
    header('Location: ../admin.php?page=skills&sub=timeline');
    exit;
}

// --- GALLERY ---
function ambil_gambar_gallery() {
    if (!empty($_FILES['gl_image_file']['name'])) {
        $uploaded = upload_gambar($_FILES['gl_image_file']);
        if ($uploaded !== false) return $uploaded;
    }
    $url = $_POST['gl_image'] ?? '';
    if (!empty($url)) {
        if (!validate_url($url)) return false;
        return $url;
    }
    return '';
}

if ($action === 'add_gallery') {
    $img = ambil_gambar_gallery();
    if ($img === false) {
        set_flash('warning', 'URL gambar tidak valid.');
        header('Location: ../admin.php?page=skills&sub=gallery');
        exit;
    }
    $gl = $data['site_settings']['gallery'] ?? [];
    $gl[] = [
        'image' => $img,
        'year' => $_POST['gl_year'] ?? ''
    ];
    $data['site_settings']['gallery'] = $gl;
    tulis_json($data);
    set_flash('success', 'Item galeri berhasil ditambahkan.');
    header('Location: ../admin.php?page=skills&sub=gallery');
    exit;
}

if ($action === 'update_gallery') {
    $index = (int)$_POST['index'];
    $gl = $data['site_settings']['gallery'] ?? [];
    if (isset($gl[$index])) {
        $img = ambil_gambar_gallery();
        if ($img === false) {
            set_flash('warning', 'URL gambar tidak valid.');
            header('Location: ../admin.php?page=skills&sub=gallery');
            exit;
        }
        $gl[$index] = [
            'image' => $img ?: $gl[$index]['image'],
            'year' => $_POST['gl_year'] ?? ''
        ];
        $data['site_settings']['gallery'] = $gl;
        tulis_json($data);
        set_flash('success', 'Item galeri berhasil diperbarui.');
    }
    header('Location: ../admin.php?page=skills&sub=gallery');
    exit;
}

if ($action === 'delete_gallery') {
    $index = (int)$_GET['index'];
    $gl = $data['site_settings']['gallery'] ?? [];
    if (isset($gl[$index])) {
        array_splice($gl, $index, 1);
        $data['site_settings']['gallery'] = array_values($gl);
        tulis_json($data);
        set_flash('success', 'Item galeri berhasil dihapus.');
    }
    header('Location: ../admin.php?page=skills&sub=gallery');
    exit;
}

// --- MEDIA DELETE ---
if ($action === 'delete_media') {
    $file = basename($_GET['file'] ?? '');
    $path = __DIR__ . '/../uploads/' . $file;
    if ($file && file_exists($path)) {
        unlink($path);
    }
    set_flash('success', 'Gambar berhasil dihapus.');
    header('Location: ../admin.php?page=media');
    exit;
}

header('Location: ../admin.php');
exit;
