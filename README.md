# Myportofolio

Personal portfolio & blog website built with **PHP Native** and **JSON-based storage**.

## Fitur

- **Portfolio** — Projects, articles, skills, tech stack, gallery, timeline, FAQ
- **Blog** — Artikel dengan kategori, pagination, RSS feed, sitemap
- **CMS Admin** — Panel admin lengkap untuk mengelola semua konten
  - Kelola Projek, Artikel, Skills, Gallery, Timeline, FAQ
  - Edit halaman Beranda, Tentang, Kontak
  - Upload gambar
  - Pesan dari pengunjung
- **Dark Mode** — Toggle tema gelap/terang
- **Responsive** — Mobile-friendly dengan sidebar navigasi
- **SEO** — Meta tags, Open Graph, RSS, Sitemap

## Persyaratan

- PHP 7.4+
- Web Server (Apache/Laragon/XAMPP)
- Ekstensi PHP: `json`, `fileinfo`, `gd` (untuk upload gambar)

## Instalasi

1. Clone atau download project ke folder web server
2. Buka di browser — langsung berjalan (tanpa database)
3. Akses admin panel: `/login.php`
4. Login:

   | Username | Password |
   |----------|----------|
   | `admin`  | `admin123` |

5. Tambahkan konten pertama kamu dari panel admin

> Ubah username/password di `config.php` dan regenerate bcrypt hash.

## Struktur Folder

```
├── assets/          # CSS, JS, images
├── components/      # Reusable PHP components
├── data/            # JSON data files (database)
│   ├── data.json
│   └── messages.json
├── includes/        # Core includes (functions, header, footer)
├── process/         # Form handlers (admin CRUD, contact)
├── uploads/         # Uploaded images
├── admin.php        # Admin CMS panel
├── index.php        # Homepage
├── projek.php       # Projects page
├── artikel.php      # Articles page
├── detail_*.php     # Detail pages
├── kontak.php       # Contact page
├── tentang.php      # About page
├── login.php        # Admin login
├── config.php       # Configuration & credentials
├── rss.php          # RSS feed
└── sitemap.xml      # Sitemap
```

## Tech Stack

- **Backend:** PHP Native (no framework)
- **Database:** JSON file (no MySQL)
- **Frontend:** Vanilla CSS + JavaScript
- **Editor:** Trumbowyg (WYSIWYG untuk artikel/projek)
- **Icons:** Feather Icons via inline SVG

## Catatan

- Data disimpan di file JSON — tidak perlu database MySQL
- Untuk production, disarankan menambahkan proteksi folder `/data` dan `/uploads` di `.htaccess`
- Session timeout: 30 menit (konfigurasi di `config.php`)

## Lisensi

MIT
