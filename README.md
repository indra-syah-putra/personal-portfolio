# Myportofolio

Website portofolio dan blog pribadi, dibikin pakai **PHP Native** doang tanpa framework — datanya pake JSON, bukan MySQL.

## Fitur

- **Portofolio** — Projek, artikel, skill, tech stack, galeri, timeline, FAQ
- **Blog** — Ada kategori, pagination, RSS feed, sitemap
- **Admin CMS** — Biar gampang ngatur konten dari panel
  - Projek, Artikel, Skills, Gallery, Timeline, FAQ
  - Edit halaman Beranda, Tentang, Kontak
  - Upload gambar
  - Lihat pesan dari pengunjung
- **Dark Mode** — Bisa toggle gelap/terang
- **Responsive** — Layar HP juga oke
- **SEO** — Meta tags, Open Graph, RSS, Sitemap

## Yang Dibutuhin

- PHP 7.4+
- Web Server (Laragon / XAMPP / Apache)
- Ekstensi PHP: `json`, `fileinfo`, `gd` (buat upload gambar)

## Cara Pasang

1. Clone atau download project, taruh di folder web server
2. Buka aja di browser — langsung jalan, gausah setup database
3. Masuk admin panel: `/login.php`
4. Login pake ini:

   | Username | Password |
   |----------|----------|
   | `admin`  | `admin123` |

5. Mulai tambahin konten dari panel admin

> Kalo mau ganti username/password, edit aja di `config.php` terus generate ulang bcrypt hash-nya.

## Struktur Folder

```
├── assets/          # CSS, JS, gambar
├── components/      # Komponen PHP yang dipakai ulang
├── data/            # File JSON (nyimpen data)
│   ├── data.json
│   └── messages.json
├── includes/        # Fungsi-fungsi inti (header, footer, dll)
├── process/         # Penanganan form (admin, kontak)
├── uploads/         # Gambar hasil upload
├── admin.php        # Panel admin CMS
├── index.php        # Halaman depan
├── projek.php       # Halaman projek
├── artikel.php      # Halaman artikel
├── detail_*.php     # Halaman detail
├── kontak.php       # Halaman kontak
├── tentang.php      # Halaman tentang
├── login.php        # Halaman login admin
├── config.php       # Konfigurasi & kredensial
├── rss.php          # RSS feed
└── sitemap.xml      # Sitemap
```

## Tech Stack

- **Backend:** PHP Native (tanpa framework)
- **Database:** JSON file (gausah MySQL)
- **Frontend:** CSS + JavaScript murni
- **Editor:** Trumbowyg (WYSIWYG buat nulis artikel/projek)
- **Icons:** Feather Icons — pake inline SVG

## Catatan

- Data cukup disimpen di JSON, jadi ribetnya MySQL gausah
- Kalo mau dipasang di production, saran saya kasih proteksi folder `/data` sama `/uploads` di `.htaccess`
- Session login: 30 menit abis (atur di `config.php`)

## Lisensi

MIT
