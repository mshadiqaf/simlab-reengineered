# SimLab Reengineered

Project ini adalah aplikasi berbasis Laravel dengan frontend Vue 3 (Inertia.js), Vite, dan Tailwind CSS.

## Prasyarat

Sebelum memulai, pastikan bahwa sistem Anda telah terinstal:
- **PHP** (minimal versi 8.3)
- **Composer**
- **Node.js** dan **npm**
- **Database** (MySQL, PostgreSQL, atau SQLite)

## Cara Instalasi dan Menjalankan Project

Ikuti langkah-langkah di bawah ini untuk melakukan *cloning* dan menjalankan aplikasi di sistem lokal Anda:

### 1. Clone Repository
Lakukan *clone* ke lokal lalu masuk ke dalam folder project:
```bash
git clone <repository_url>
cd simlab-reengineered
```

### 2. Install Dependensi PHP (Composer)
Jalankan perintah berikut untuk menginstal package Laravel yang dibutuhkan:
```bash
composer install
```

### 3. Install Dependensi Node (NPM)
Jalankan perintah berikut untuk menginstal package frontend:
```bash
npm install
```

### 4. Setup File Environment / Konfigurasi
Copy file konfigurasi dari `.env.example` menjadi `.env`:
```bash
cp .env.example .env
```
Untuk pengguna Windows (Command Prompt / PowerShell), jika command `cp` tidak tersedia gunakan:
```bash
copy .env.example .env
```

### 5. Generate Application Key
Jalankan perintah artisan untuk *generate* app key di file `.env`:
```bash
php artisan key:generate
```

### 6. Setup Database & Migrasi
Silakan buka file `.env` di *code editor* Anda dan sesuaikan kredensial database yang Anda gunakan.
*Contoh menggunakan SQLite:*
```env
DB_CONNECTION=sqlite
# Atau jika menggunakan MySQL, sesuaikan DB_DATABASE, DB_USERNAME, dan DB_PASSWORD
```
Setelah database disiapkan, jalankan perintah untuk *migrate* tabel:
```bash
php artisan migrate
```

### 7. Menjalankan Aplikasi di Lokal
Project ini sudah dilengkapi dengan script composer untuk menjalankan server Laravel, antrian (queue), dan Vite secara bersamaan. Jalankan:
```bash
composer run dev
```

Server backend otomatis akan berjalan di `http://localhost:8000` atau URL yang diberikan oleh Vite. Silakan buka alamat tersebut melalui browser.

> **Tips:** Anda juga dapat menggunakan perintah `composer run setup` untuk proses instalasi dependensi, set `.env`, key generate, migrasi, dan *build production* dalam 1 command, tetapi untuk mode *development*, gunakan langkah manual di atas dan `composer run dev`.

---
*Happy Coding!*
