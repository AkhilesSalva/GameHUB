# 🎮 Game Hub Console

[![PHP](https://img.shields.io/badge/PHP-8.0+-777BB4?style=for-the-badge&logo=php&logoColor=white)](https://php.net)
[![MySQL](https://img.shields.io/badge/MySQL-8.0+-4479A1?style=for-the-badge&logo=mysql&logoColor=white)](https://mysql.com)
[![Bootstrap](https://img.shields.io/badge/Bootstrap-5.x-7952B3?style=for-the-badge&logo=bootstrap&logoColor=white)](https://getbootstrap.com)
[![License](https://img.shields.io/badge/License-MIT-green?style=for-the-badge)](LICENSE)

<p align="center">
  <img src="docs/screenshots/Login.png" alt="Game Hub Console" width="600"/>
</p>

<p align="center">
  <b>Aplikasi panel admin untuk mengelola koleksi game.</b><br>
  Dibuat untuk mempermudah administrator dalam menambah, mengubah, dan menghapus data game.
</p>

<p align="center">
  <a href="#-fitur">Fitur</a> •
  <a href="#-tampilan-aplikasi">Screenshots</a> •
  <a href="#-struktur-project">Struktur</a> •
  <a href="#-teknologi">Teknologi</a> •
  <a href="#-instalasi">Instalasi</a>
</p>

---

## 🚀 Fitur

### 👤 Fitur User
| Fitur | Deskripsi |
|-------|-----------|
| 🔐 Autentikasi | Login & Register untuk user |
| 🎮 Browse Game | Lihat koleksi game dengan filter genre |
| 📖 Detail Game | Informasi lengkap tentang game |
| ⬇️ Download | Download game dari platform |
| ⭐ Rating | Berikan rating untuk game |
| 💬 Komentar | Diskusi dan komentar game |
| ❤️ Wishlist | Simpan game favorit |
| 📜 Riwayat | Lihat history download |
| 📝 Request Game | Request game baru |

### 🔧 Fitur Admin
| Fitur | Deskripsi |
|-------|-----------|
| 📊 Dashboard | Statistik dan overview |
| 🎮 Manajemen Game | CRUD game (tambah, edit, hapus) |
| 🏷️ Manajemen Genre | Kelola kategori genre |
| 👥 Manajemen User | Kelola akun pengguna |
| 💬 Moderasi Komentar | Kelola komentar user |
| 📋 Laporan | Lihat laporan link bermasalah |
| 📝 Request | Kelola request game dari user |

---

## 📸 Tampilan Aplikasi

<details>
<summary><b>🔐 Halaman Login</b></summary>
<br>
<img src="docs/screenshots/Login.png" alt="Halaman Login" width="700"/>
</details>

<details>
<summary><b>🏠 Halaman Utama</b></summary>
<br>
<img src="docs/screenshots/Halaman Utama.png" alt="Halaman Utama" width="700"/>
</details>

<details>
<summary><b>🎮 Halaman Semua Game</b></summary>
<br>
<img src="docs/screenshots/Halaman Semua Game.png" alt="Halaman Semua Game" width="700"/>
</details>

<details>
<summary><b>📖 Halaman Detail Game</b></summary>
<br>
<img src="docs/screenshots/Halaman Detail Game.png" alt="Halaman Detail Game" width="700"/>
</details>

<details>
<summary><b>📊 Dashboard Admin</b></summary>
<br>
<img src="docs/screenshots/Dashboard Admin.png" alt="Dashboard Admin" width="700"/>
</details>

<details>
<summary><b>📋 Daftar Game (Admin)</b></summary>
<br>
<img src="docs/screenshots/Daftar Game.png" alt="Daftar Game" width="700"/>
</details>

<details>
<summary><b>➕ Kelola Game Baru</b></summary>
<br>
<img src="docs/screenshots/Kelola Game Baru.png" alt="Kelola Game Baru" width="700"/>
</details>

<details>
<summary><b>🏷️ Kelola Kategori Genre</b></summary>
<br>
<img src="docs/screenshots/Kelola Kategori Genre.png" alt="Kelola Kategori Genre" width="700"/>
</details>

<details>
<summary><b>💬 Kelola Komentar</b></summary>
<br>
<img src="docs/screenshots/Kelola Komentar.png" alt="Kelola Komentar" width="700"/>
</details>

<details>
<summary><b>👥 Kelola Akun Sistem</b></summary>
<br>
<img src="docs/screenshots/Kelola Akun Sistem.png" alt="Kelola Akun Sistem" width="700"/>
</details>

---

## 📁 Struktur Project

```
game-hub/
│
├── 📂 admin/                    # Panel Admin
│   ├── index.php                # Dashboard admin
│   ├── daftar_game.php          # Daftar semua game
│   ├── tambah.php               # Form tambah game
│   ├── edit.php                 # Form edit game
│   ├── aksi_crud.php            # Handler CRUD game
│   ├── genre.php                # Kelola genre
│   ├── aksi_genre.php           # Handler CRUD genre
│   ├── users.php                # Kelola users
│   ├── edit_user.php            # Edit user
│   ├── komentar.php             # Moderasi komentar
│   ├── reports.php              # Laporan link
│   ├── requests.php             # Request game
│   └── cek_login.php            # Middleware auth
│
├── 📂 auth/                     # Autentikasi
│   ├── login.php                # Halaman login
│   ├── register.php             # Halaman register
│   └── logout.php               # Logout handler
│
├── 📂 pages/                    # Halaman User
│   ├── detail.php               # Detail game
│   ├── genre.php                # Filter by genre
│   ├── semua_game.php           # Semua game
│   ├── koleksi.php              # Coming Soon / Wishlist
│   ├── download_history.php     # Riwayat download
│   └── request_game.php         # Request game
│
├── 📂 actions/                  # Action Handlers
│   ├── download.php             # Download handler
│   ├── rating_action.php        # Rating handler
│   ├── wishlist_action.php      # Wishlist handler
│   ├── report_link.php          # Report link
│   └── check_releases.php       # Check new releases
│
├── 📂 api/                      # API Endpoints
│   └── get_games_by_ids.php     # Get games by IDs
│
├── 📂 assets/                   # Static Assets
│   ├── 📂 css/                  # Stylesheets
│   ├── 📂 js/                   # JavaScript
│   └── 📂 img/                  # Images
│
├── 📂 database/                 # Database Files
│   ├── db_game_crud.sql         # Database schema
│   └── migration.sql            # Database migrations
│
├── 📂 docs/                     # Documentation
│   └── 📂 screenshots/          # Screenshot files
│
├── 📂 includes/                 # Shared Components
│   └── log_activity.php         # Activity logger
│
├── 📄 index.php                 # Homepage (Entry Point)
├── 📄 config.php                # Database config
└── 📄 README.md                 # Dokumentasi
```

---

## 💻 Teknologi

| Kategori | Teknologi |
|----------|-----------|
| **Frontend** | HTML5, CSS3, JavaScript, Bootstrap 5 |
| **Backend** | PHP 8+ (Native) |
| **Database** | MySQL / MariaDB |
| **Server** | Apache (XAMPP) |
| **Icons** | Bootstrap Icons |

---

## ⚙️ Instalasi

### System Requirements
- PHP 8.0+
- MySQL 5.7+ / MariaDB
- Apache Web Server (XAMPP/WAMP/MAMP)
- Web Browser Modern

### Langkah Instalasi

#### 1️⃣ Clone Repository
```bash
git clone https://github.com/username/game-hub.git
cd game-hub
```

#### 2️⃣ Setup Database
```sql
-- Buat database
CREATE DATABASE db_game_crud;

-- Import schema
mysql -u root -p db_game_crud < database/db_game_crud.sql

-- Atau import migration
mysql -u root -p db_game_crud < database/migration.sql
```

#### 3️⃣ Konfigurasi Database
Edit file `config.php`:
```php
$host = "localhost";
$username = "root";
$password = "";  // Sesuaikan password
$database = "db_game_crud";
```

#### 4️⃣ Jalankan Aplikasi

**XAMPP:**
1. Letakkan folder di `C:\xampp\htdocs\game-hub`
2. Akses: `http://localhost/game-hub`

**PHP Built-in Server:**
```bash
php -S localhost:8000
```

#### 5️⃣ Akses Aplikasi

| Halaman | URL |
|---------|-----|
| Website | http://localhost/game-hub |
| Login | http://localhost/game-hub/auth/login.php |
| Admin Panel | http://localhost/game-hub/admin |

---

## 🔒 Keamanan

- ✅ Password hashing dengan `password_hash()`
- ✅ Prepared statements untuk SQL injection prevention
- ✅ Session-based authentication
- ✅ Admin middleware protection
- ✅ Input validation & sanitization

---

## 📝 License

Proyek ini dilisensikan di bawah **MIT License**.

---

<div align="center">

**Made with ❤️ by Akhiles Salvadore Seina Huler**

</div>
