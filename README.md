# MoodTracker – Dokumentasi Lengkap

## Ringkasan Proyek
MoodTracker adalah aplikasi web PHP untuk memantau, menyimpan, dan menganalisis mood karyawan secara harian. Aplikasi menyajikan landing page publik modern berbasis Tailwind CSS serta dashboard administrasi menggunakan template SB Admin 2. Data disimpan di MySQL, mencakup karyawan, posisi, catatan mood, serta audit trail.

## Fitur Utama
- **Landing Page** (`public/index.php`): Ringkasan fitur, daftar mood terbaru, integrasi Tailwind.
- **Form Mood Harian** (`public/mood/create.php`): Validasi CSRF, pembatasan satu catatan per hari, antarmuka interaktif.
- **Riwayat Mood** (`public/mood/history.php`): Filter per karyawan/tanggal, tampilan tabel responsif.
- **Dashboard Admin** (`public/admin/*.php`): Statistik (Chart.js), manajemen karyawan, CRUD catatan mood, notifikasi toast.
- **Keamanan** (`src/Security/Csrf.php`, `src/Auth/AuthManager.php`): Token CSRF, guard role admin, session flash, proteksi redirect.
- **Audit Trail** (`src/Models/AuditRepository.php`): Pencatatan aktivitas penting seperti login dan CRUD.

## Struktur Direktori
```
├── bootstrap/      Inisialisasi aplikasi (helpers, env, autoload)
├── config/         Konfigurasi aplikasi & mood
├── public/         Frontend publik, form mood, dashboard admin, auth
├── src/            Kode PHP inti (Database, Models, Security, Auth)
├── tests/          Unit test (PHPUnit)
├── sb-admin-2/     Asset template admin (CSS, JS, SCSS)
├── vendor/         Dependensi Composer
├── database.sql    Skema dan seed data
└── README.md
```

## Prasyarat
- PHP >= 8.0
- MySQL 5.7/8.x
- Composer
- Web server (Apache/Nginx) atau PHP built-in server
- Node/npm opsional untuk mengelola asset SB Admin 2 (tidak wajib)

## Instalasi

### 1. Kloning Repositori
```bash
git clone <repository-url> moodtracker
cd moodtracker
```

### 2. Composer Install
```bash
composer install
```

### 3. Konfigurasi Environment
- Salin `.env.example` menjadi `.env`.
- Sesuaikan variabel berikut agar selaras dengan database lokal:
  - `DB_HOST`, `DB_PORT`, `DB_DATABASE`, `DB_USERNAME`, `DB_PASSWORD`.

### 4. Konfigurasi Aplikasi
- Pastikan `config/app.php` sesuai kebutuhan (nama aplikasi, URL, timezone).
- Untuk pilihan mood default, ubah `config/moods.php`.

### 5. Import Database
```sql
mysql -u <user> -p < database.sql
```
`database.sql` membuat tabel `Posisi`, `Users`, `Catatan_Harian`, `Audit_Log` serta menambahkan seed data awal (akun admin, beberapa mood sample).

### 6. Konfigurasi Web Server
- Jika menggunakan Apache, arahkan DocumentRoot ke `moodtracker/public`.
- Pastikan `index.php` di root mem-forward ke `public/index.php`.

## Menjalankan Aplikasi

### PHP Built-in Server (opsional)
```bash
php -S localhost:8000 -t public
```

### Akses Antarmuka
- Landing: `http://localhost/moodtracker/` atau `http://localhost:8000/` jika built-in server.
- Login Admin: `http://localhost/moodtracker/public/auth/login.php`
- Login Karyawan: `http://localhost/moodtracker/public/auth/login_employee.php`

### Kredensial Seed
- Admin: `no_bundy = BD001`, password `password`
- Karyawan contoh: `BD002`, `BD003` (password sama)

## Alur Pengguna

### Karyawan
1. Masuk melalui `public/auth/login_employee.php`.
2. Isi mood harian di `public/mood/create.php`. Sistem menolak catatan kedua di tanggal sama, menampilkan pesan sukses atau error via session flash.
3. Lihat riwayat via `public/mood/history.php`. Filter tersedia untuk tanggal dan karyawan (jika memiliki hak akses luas).

### Admin
1. Login via `public/auth/login.php`.
2. Dashboard (`public/admin/index.php`): tampilkan statistik ringkas, grafik mood.
3. Manajemen: `public/admin/users.php` (karyawan), `public/admin/moods.php` (catatan).
4. Audit log: `public/admin/audit.php` (opsional tergantung implementasi).
5. Logout di `public/auth/logout.php`.

## Desain Arsitektur

### Bootstrap
- `bootstrap/helpers.php`: Fungsi utilitas seperti `config()`, `redirect()`, `flash()`, `csrf_field()`, `env()`.
- `bootstrap/environment.php`: Memuat variabel environment.
- `bootstrap/app.php`: Start session, set timezone, sanitasi konfigurasi.
- `bootstrap/autoload.php`: Composer autoload.

### Config
- `config/app.php`: Setting global aplikasi, DSN database.
- `config/moods.php`: Array mood default.

### Database Layer
- `src/Database/Connection.php`: Singleton PDO, baca konfigurasi dari `config/app.php`.
- Query manual menggunakan PDO prepared statements di repository.

### Layer Model/Repository (`src/Models/`)
- `UserRepository.php`: Auth lookup, CRUD user.
- `PositionRepository.php`: Data posisi karyawan.
- `MoodRepository.php`: Insert mood, pengecekan duplikasi harian.
- `AuditRepository.php`: Logging.
- Setiap repository menggunakan `Connection::instance()` untuk akses database.

### Auth & Security
- `src/Auth/AuthManager.php`: Login, logout, session guard, peran admin/karyawan.
- `src/Security/Csrf.php`: Pembuatan token, validasi, helper form (`csrf_field()`).
- Middleware manual: pengecekan `AuthManager::check()` di tiap script `public`.

### Frontend
- `public/index.php`: Landing page Tailwind (CDN).
- `public/mood/*`: Halaman karyawan.
- `public/admin/*`: SB Admin 2 (assets di `sb-admin-2/`).
- `public/auth/*`: Login, register (jika tersedia), logic form.

### Tests
- `tests/Unit/UserRepositoryTest.php`: Contoh struktur pengujian (perlu DB test terpisah dan melengkapi skenario).

## Testing

### PHPUnit
1. Pastikan `.env.test` (opsional) dan database test siap.
2. Jalankan:
```bash
vendor/bin/phpunit
```
3. Tambah test dengan namespace `Tests\` (lihat `phpunit.xml.dist`).

## Deployment Notes

- **Server**: PHP 8.0+, mod_rewrite (jika Apache) untuk clean URL (opsional).
- **Env**: Set environment variable via `.env` atau server (jangan commit `.env`).
- **Session Security**: Perkuat `cookie_secure` pada deployment HTTPS (sudah adaptif).
- **Optimize Assets**: Pertimbangkan bundler untuk Tailwind & SB Admin 2 jika ingin optimasi.
- **Backups**: Rutin backup database `moodtracker`.

## Roadmap

- Lengkapi test integrasi repository.
- Modularisasi validasi form.
- Fitur manajemen password & logout otomatis.
- Ekspor CSV/Excel dan notifikasi otomatis.
- Dokumen deployment (Apache/Nginx config, CI/CD pipeline).

## Troubleshooting

- **Database Connection Error**: Periksa `config/app.php` dan `.env`. Pastikan user memiliki hak akses.
- **Session Issues**: Pastikan direktori session writeable, domain cocok, cek `php.ini`.
- **CSRF Token Invalid**: Pastikan form menyertakan `csrf_field()` dan session aktif.
- **Duplikasi Catatan**: Fitur `MoodRepository::hasEntryForDate()` menolak catatan ganda; hapus catatan di DB jika perlu reset.

## Konvensi & Standar

- **Bahasa**: Codebase memakai bahasa Indonesia untuk label UI dan komentar minimal.
- **Style**: Gunakan Tailwind untuk UI publik, SB Admin 2 untuk admin.
- **PHP Lint**: Ikuti standar PSR-12 (disarankan).
- **Branching**: `feature/<nama-fitur>` untuk perubahan baru.

## Kontribusi

1. Fork repository.
2. Branch baru: `git checkout -b feature/<nama-fitur>`.
3. Implementasi fitur + test.
4. Pull request ke main branch dengan detail perubahan.

## Lisensi

- Aplikasi inti tanpa lisensi khusus → sesuaikan sesuai kebutuhan organisasi.
- Ikuti lisensi dari template SB Admin 2 dan dependensi (`dompdf/dompdf`, dll.).
