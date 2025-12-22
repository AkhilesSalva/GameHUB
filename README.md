# 🎮 Game Hub Console

[![PHP](https://img.shields.io/badge/PHP-8.0+-777BB4?style=for-the-badge&logo=php&logoColor=white)](https://php.net)
[![MySQL](https://img.shields.io/badge/MySQL-8.0+-4479A1?style=for-the-badge&logo=mysql&logoColor=white)](https://mysql.com)
[![Bootstrap](https://img.shields.io/badge/Bootstrap-5.x-7952B3?style=for-the-badge&logo=bootstrap&logoColor=white)](https://getbootstrap.com)
[![License](https://img.shields.io/badge/License-MIT-green?style=for-the-badge)](LICENSE)

<p align="center">
  <img src="docs/screenshots/Halaman Utama.png" alt="Game Hub Console" width="800"/>
</p>

## 📝 Tentang Project

**Game Hub Console** adalah platform web untuk mengunduh dan mengelola koleksi game offline. Aplikasi ini dibangun menggunakan **PHP Native** dengan arsitektur yang terstruktur, dilengkapi dengan panel admin yang lengkap untuk mengelola seluruh konten.

### 🎯 Tujuan Aplikasi
- Menyediakan platform terpusat untuk berbagi dan mengunduh game
- Memudahkan pengelolaan koleksi game dengan sistem kategori genre
- Memberikan pengalaman user yang interaktif dengan fitur rating, komentar, dan wishlist
- Menyediakan panel admin yang komprehensif untuk content management

### ✨ Highlight Fitur
- 🎨 **UI/UX Modern** - Desain gelap yang elegan dengan animasi smooth
- 🔍 **Pencarian & Filter** - Cari game berdasarkan nama atau filter by genre
- ⭐ **Rating System** - User bisa memberikan rating 1-5 bintang
- 💬 **Komentar & Reply** - Diskusi dengan sistem komentar bersarang
- 🔔 **Coming Soon Notification** - Follow game yang belum rilis
- 📊 **Admin Dashboard** - Statistik lengkap dan manajemen konten
- 📱 **Responsive Design** - Tampilan optimal di desktop dan mobile

---

## 🖼️ Screenshots

<table>
  <tr>
    <td align="center" width="50%">
      <img src="docs/screenshots/Login.png" alt="Login" width="100%"/><br/>
      <sub><b>🔐 Halaman Login</b></sub>
    </td>
    <td align="center" width="50%">
      <img src="docs/screenshots/Halaman Utama.png" alt="Homepage" width="100%"/><br/>
      <sub><b>🏠 Halaman Utama</b></sub>
    </td>
  </tr>
  <tr>
    <td align="center" width="50%">
      <img src="docs/screenshots/Halaman Semua Game.png" alt="All Games" width="100%"/><br/>
      <sub><b>🎮 Semua Game</b></sub>
    </td>
    <td align="center" width="50%">
      <img src="docs/screenshots/Halaman Detail Game.png" alt="Game Detail" width="100%"/><br/>
      <sub><b>📖 Detail Game</b></sub>
    </td>
  </tr>
  <tr>
    <td align="center" width="50%">
      <img src="docs/screenshots/Dashboard Admin.png" alt="Admin Dashboard" width="100%"/><br/>
      <sub><b>📊 Dashboard Admin</b></sub>
    </td>
    <td align="center" width="50%">
      <img src="docs/screenshots/Daftar Game.png" alt="Game List" width="100%"/><br/>
      <sub><b>📋 Daftar Game (Admin)</b></sub>
    </td>
  </tr>
  <tr>
    <td align="center" width="50%">
      <img src="docs/screenshots/Kelola Game Baru.png" alt="Add Game" width="100%"/><br/>
      <sub><b>➕ Tambah Game</b></sub>
    </td>
    <td align="center" width="50%">
      <img src="docs/screenshots/Kelola Kategori Genre.png" alt="Genre Management" width="100%"/><br/>
      <sub><b>🏷️ Kelola Genre</b></sub>
    </td>
  </tr>
</table>

---

## 🚀 Fitur Lengkap

### 👤 Fitur User (Pengunjung)

| Fitur | Deskripsi |
|-------|-----------|
| 🔐 **Autentikasi** | Sistem login & register dengan password hashing |
| 🎮 **Browse Game** | Jelajahi koleksi game dengan tampilan grid yang menarik |
| 🔍 **Pencarian** | Cari game berdasarkan nama dengan hasil real-time |
| 🏷️ **Filter Genre** | Filter game berdasarkan kategori (Action, RPG, Adventure, dll) |
| 📊 **Sorting** | Urutkan berdasarkan: Terbaru, Populer, Terbanyak Dilihat, Nama |
| 📖 **Detail Game** | Lihat informasi lengkap: deskripsi, genre, ukuran file, link download |
| ⬇️ **Download** | Download game dengan tracking jumlah download |
| ⭐ **Rating** | Berikan rating 1-5 bintang untuk setiap game |
| 💬 **Komentar** | Tulis komentar dan diskusi dengan user lain |
| 🔔 **Coming Soon** | Follow game yang belum rilis, dapatkan notifikasi saat rilis |
| 📜 **Riwayat** | Lihat history download game yang pernah diunduh |
| 📝 **Request Game** | Request game baru untuk ditambahkan admin |
| 🚨 **Report Link** | Laporkan link download yang bermasalah |

### 🔧 Fitur Admin

| Fitur | Deskripsi |
|-------|-----------|
| 📊 **Dashboard** | Overview statistik: total game, download, user, views |
| 🎮 **CRUD Game** | Tambah, edit, hapus game dengan upload gambar cover & hero |
| 🖼️ **Multi Upload** | Upload cover image, hero image, dan screenshots |
| 🔗 **Link Manager** | Kelola link download (single link atau multi-part) |
| 🏷️ **Genre Manager** | CRUD kategori genre dengan relasi many-to-many |
| 👥 **User Manager** | Kelola akun user, ubah role, aktivasi/nonaktifkan |
| 💬 **Moderasi Komentar** | Lihat, balas, atau hapus komentar user |
| 📋 **Request Manager** | Kelola request game dari user |
| 🚨 **Report Manager** | Tangani laporan link bermasalah |

---

## 🛠️ Tech Stack

### Backend
| Teknologi | Keterangan |
|-----------|------------|
| **PHP 8+** | Backend native tanpa framework |
| **MySQL** | Database relasional |
| **PDO/MySQLi** | Database connection dengan prepared statements |
| **Session** | Autentikasi berbasis session |

### Frontend
| Teknologi | Keterangan |
|-----------|------------|
| **HTML5** | Struktur semantik |
| **CSS3** | Custom styling dengan CSS variables |
| **JavaScript** | Vanilla JS untuk interaktivitas |
| **Bootstrap 5** | Grid system & komponen UI |
| **Bootstrap Icons** | Icon library |

### Development
| Tool | Keterangan |
|------|------------|
| **XAMPP** | Local development server |
| **Git** | Version control |
| **VS Code** | Code editor |

---

## 📁 Struktur Project

```
game-hub/
│
├── 📂 admin/                    # Panel Admin
│   ├── index.php                # Dashboard dengan statistik
│   ├── daftar_game.php          # List semua game
│   ├── tambah.php               # Form tambah game baru
│   ├── edit.php                 # Form edit game
│   ├── aksi_crud.php            # Handler CRUD game
│   ├── genre.php                # Manajemen genre
│   ├── aksi_genre.php           # Handler CRUD genre
│   ├── users.php                # Manajemen user
│   ├── edit_user.php            # Form edit user
│   ├── komentar.php             # Moderasi komentar
│   ├── reports.php              # Laporan link rusak
│   ├── requests.php             # Request game dari user
│   └── cek_login.php            # Middleware autentikasi
│
├── 📂 auth/                     # Autentikasi
│   ├── login.php                # Halaman login
│   ├── register.php             # Halaman registrasi
│   └── logout.php               # Proses logout
│
├── 📂 pages/                    # Halaman User
│   ├── detail.php               # Detail game + komentar
│   ├── genre.php                # Filter game by genre
│   ├── semua_game.php           # Semua game dengan filter
│   ├── koleksi.php              # Coming Soon wishlist
│   ├── download_history.php     # Riwayat download
│   └── request_game.php         # Form request game
│
├── 📂 actions/                  # Action Handlers (AJAX)
│   ├── download.php             # Proses download + counter
│   ├── rating_action.php        # Submit rating
│   ├── wishlist_action.php      # Follow/unfollow game
│   ├── report_link.php          # Report link rusak
│   └── check_releases.php       # Cek game yang sudah rilis
│
├── 📂 api/                      # API Endpoints
│   └── get_games_by_ids.php     # Get games data by IDs
│
├── 📂 assets/                   # Static Assets
│   ├── 📂 css/
│   │   ├── style.css            # Main stylesheet
│   │   └── admin_style.css      # Admin panel styles
│   ├── 📂 js/                   # JavaScript files
│   ├── 📂 img/                  # Uploaded game images
│   └── 📂 screenshots/          # Game screenshots
│
├── 📂 database/                 # Database Files
│   ├── db_game_crud.sql         # Schema + sample data
│   └── migration.sql            # Database migrations
│
├── 📂 docs/                     # Documentation
│   └── 📂 screenshots/          # README screenshots
│
├── 📂 includes/                 # Shared Components
│   └── log_activity.php         # Activity logging
│
├── 📄 index.php                 # Homepage (Entry Point)
├── 📄 config.php                # Database & app configuration
└── 📄 README.md                 # Documentation
```

---

## ⚙️ Instalasi

### Prerequisites
- **PHP** 8.0 atau lebih tinggi
- **MySQL** 5.7+ atau MariaDB 10+
- **Apache** Web Server (atau gunakan XAMPP)
- **Web Browser** modern (Chrome, Firefox, Edge)

### Langkah Instalasi

#### 1️⃣ Clone Repository
```bash
git clone https://github.com/AkhilesSalva/GameHUB.git
cd GameHUB
```

#### 2️⃣ Setup Database
```sql
-- Buat database baru
CREATE DATABASE db_game_crud CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;

-- Import schema dan sample data
mysql -u root -p db_game_crud < database/db_game_crud.sql
```

#### 3️⃣ Konfigurasi Aplikasi
Edit file `config.php`:
```php
// Database Configuration
$host = "localhost";
$username = "root";
$password = "";           // Sesuaikan jika ada password
$database = "db_game_crud";

// Base URL (sesuaikan dengan environment)
define('BASE_URL', 'http://localhost/game-hub');
```

#### 4️⃣ Jalankan Aplikasi

**Menggunakan XAMPP:**
1. Copy folder project ke `C:\xampp\htdocs\game-hub`
2. Start Apache & MySQL dari XAMPP Control Panel
3. Akses: `http://localhost/game-hub`

**Menggunakan PHP Built-in Server:**
```bash
php -S localhost:8000
# Akses: http://localhost:8000
```

#### 5️⃣ Login

| Role | Username | Password |
|------|----------|----------|
| Admin | akhilessalv | 123456 |

> ⚠️ **Catatan:** Ganti password default setelah instalasi!

---

## 🔒 Keamanan

Aplikasi ini menerapkan praktik keamanan berikut:

| Fitur | Implementasi |
|-------|--------------|
| 🔐 **Password Hashing** | `password_hash()` dengan bcrypt |
| 🛡️ **SQL Injection** | Prepared statements di semua query |
| 🔒 **XSS Prevention** | `htmlspecialchars()` untuk output |
| 🎫 **Session Security** | Session-based authentication |
| 👮 **Access Control** | Middleware untuk halaman admin |
| 📁 **File Upload** | Validasi MIME type & extension |

### Rekomendasi Production
- [ ] Gunakan HTTPS
- [ ] Ganti password default
- [ ] Set `error_reporting(0)` di production
- [ ] Backup database secara berkala
- [ ] Implementasi rate limiting
- [ ] Tambahkan CSRF token

---

## 🗄️ Database Schema

### Entity Relationship Diagram

```
┌──────────────┐       ┌──────────────┐       ┌──────────────┐
│    users     │       │    games     │       │    genre     │
├──────────────┤       ├──────────────┤       ├──────────────┤
│ id (PK)      │       │ id (PK)      │       │ id (PK)      │
│ username     │       │ nama         │       │ nama_genre   │
│ password     │       │ deskripsi    │       └──────────────┘
│ nama_lengkap │       │ gambar_path  │              │
│ role         │       │ hero_image   │              │
│ created_at   │       │ file_size    │              │
└──────────────┘       │ link_download│       ┌──────────────┐
       │               │ download_count│       │  game_genre  │
       │               │ view_count   │       ├──────────────┤
       │               │ is_coming_soon│◄─────│ game_id (FK) │
       │               │ created_at   │       │ genre_id (FK)│
       │               └──────────────┘       └──────────────┘
       │                      │
       │                      │
       ▼                      ▼
┌──────────────┐       ┌──────────────┐
│   komentar   │       │   ratings    │
├──────────────┤       ├──────────────┤
│ id (PK)      │       │ id (PK)      │
│ user_id (FK) │       │ user_id (FK) │
│ game_id (FK) │       │ game_id (FK) │
│ parent_id    │       │ rating (1-5) │
│ isi_komentar │       │ created_at   │
│ created_at   │       └──────────────┘
└──────────────┘
       
┌──────────────┐       ┌──────────────┐
│ game_requests│       │ link_reports │
├──────────────┤       ├──────────────┤
│ id (PK)      │       │ id (PK)      │
│ user_id (FK) │       │ game_id (FK) │
│ game_name    │       │ report_type  │
│ description  │       │ description  │
│ status       │       │ status       │
│ created_at   │       │ created_at   │
└──────────────┘       └──────────────┘
```

---

## 🔄 Changelog

### v1.0.0 (2024)
- ✅ Initial release
- ✅ User authentication (login/register)
- ✅ Game browsing with search & filter
- ✅ Rating & comment system
- ✅ Admin panel with full CRUD
- ✅ Coming Soon / Wishlist feature
- ✅ Download history tracking
- ✅ Game request system
- ✅ Link report system

---

## 🤝 Kontribusi

Kontribusi sangat welcome! Silakan:

1. Fork repository ini
2. Buat branch baru (`git checkout -b feature/AmazingFeature`)
3. Commit perubahan (`git commit -m 'Add some AmazingFeature'`)
4. Push ke branch (`git push origin feature/AmazingFeature`)
5. Buat Pull Request

---

## 📝 License

Proyek ini dilisensikan di bawah **MIT License** - lihat file [LICENSE](LICENSE) untuk detail.

---

## 👤 Author

**Akhiles Salvadore Seina Huler**

- GitHub: [@AkhilesSalva](https://github.com/AkhilesSalva)

---

<div align="center">

### ⭐ Jika project ini membantu, berikan bintang!

**Made with ❤️ and ☕**

</div>
