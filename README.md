# PENDEKAR v2 - Penyimpanan Dokumen Elektronik Perkantoran

## Deskripsi Sistem

PENDEKAR v2 (Penyimpanan Dokumen Elektronik Perkantoran) merupakan sistem informasi berbasis web yang dikembangkan untuk membantu proses pengelolaan, penyimpanan, pencarian, dan distribusi dokumen elektronik secara terstruktur dan efisien.

Sistem ini dibuat sebagai hasil Praktik Kerja Lapangan (PKL) di Dinas Perpustakaan dan Kearsipan Kabupaten Tegal untuk mendukung digitalisasi pengelolaan dokumen serta meningkatkan efektivitas dan efisiensi dalam penyimpanan arsip elektronik.

---

## Informasi Pengembang

| Keterangan | Detail |
|------------|---------|
| Nama | NURUL SALMA AFIFAH |
| NIM | 23215013 |
| Program Studi | Teknik Informatika |
| Perguruan Tinggi | STMIK YMI Tegal |
| Tempat PKL | Dinas Perpustakaan dan Kearsipan Kabupaten Tegal |
| Judul Sistem | PENDEKAR v2 (Penyimpanan Dokumen Elektronik Perkantoran) |

---

## Teknologi yang Digunakan

### Backend
- PHP Native

### Database
- MySQL
- MariaDB

### Frontend
- HTML5
- CSS3
- JavaScript
- Bootstrap 5
- jQuery
- Modernize Template

### Development Environment
- XAMPP
- Laragon
- Apache Web Server

---

## Fitur Sistem

- Manajemen Dokumen Elektronik
- Upload dan Download Dokumen
- Pengelolaan Kategori Dokumen
- Pengelolaan Pengguna
- Pencarian Dokumen
- Dashboard Informasi Dokumen
- Role Based Access Control (RBAC)
  - Admin
  - Staff
  - Pimpinan

---

## Struktur Folder

```text
├── layout/
├── pages/
├── scripts/
├── uploads/
├── db_pendekar.sql
├── index.php
├── login.php
├── logout.php
└── README.md
```

---

## Cara Menjalankan Sistem

### 1. Persiapan

Pastikan perangkat telah terinstal:

- XAMPP atau Laragon
- Apache
- MySQL/MariaDB
- Web Browser (Google Chrome, Mozilla Firefox, Microsoft Edge, dan lain-lain)

---

### 2. Menempatkan Project

Salin atau clone repository ini ke dalam direktori server lokal.

Contoh untuk XAMPP:

```text
C:\xampp\htdocs\pendekarv2
```

---

### 3. Membuat Database

1. Jalankan Apache dan MySQL.
2. Buka phpMyAdmin:

```text
http://localhost/phpmyadmin
```

3. Buat database baru dengan nama:

```sql
db_pendekar
```

---

### 4. Import Database

1. Pilih database **db_pendekar**.
2. Klik menu **Import**.
3. Pilih file:

```text
db_pendekar.sql
```

4. Klik **Go** atau **Import**.

---

### 5. Konfigurasi Database

Pastikan konfigurasi database sesuai dengan server lokal yang digunakan.

Konfigurasi default:

```text
Host      : localhost
Username  : root
Password  :
Database  : db_pendekar
```

Sesuaikan jika menggunakan konfigurasi yang berbeda.

---

### 6. Menjalankan Aplikasi

Pastikan Apache dan MySQL sudah berjalan, kemudian akses:

```text
http://localhost/pendekarv2
```

---

## Akun Login Default

### Admin

```text
Username : admin
Password : Pendekar2026!
```

### Staff

```text
Username : staff1
Password : Pendekar2026!
```

### Pimpinan

```text
Username : pimpinan
Password : Pendekar2026!
```

---

## Tujuan Pengembangan

Sistem PENDEKAR v2 dikembangkan untuk:

- Mempermudah pengelolaan dokumen elektronik.
- Mengurangi risiko kehilangan atau kerusakan dokumen fisik.
- Mempercepat proses pencarian dokumen.
- Mendukung transformasi digital pada Dinas Perpustakaan dan Kearsipan Kabupaten Tegal.
- Meningkatkan efisiensi kerja dalam pengelolaan arsip dan dokumen perkantoran.

---

## Lisensi

Proyek ini dibuat untuk keperluan Praktik Kerja Lapangan (PKL) dan pengembangan akademik pada Program Studi Teknik Informatika STMIK YMI Tegal.

© 2026 NURUL SALMA AFIFAH
