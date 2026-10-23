# Sistem Pelaporan Operasi PLN

Aplikasi berbasis web untuk membantu proses pencatatan, pengelolaan, dan dokumentasi laporan operasi. Sistem ini menyediakan halaman untuk pengguna dan admin, serta mendukung pengelolaan laporan melalui database.

## ✨ Fitur Utama

- **Autentikasi Pengguna:** Menyediakan proses login dan logout untuk mengakses sistem.
- **Pengelolaan Laporan:** Mendukung proses melihat, mengedit, dan menghapus data laporan.
- **Halaman Admin:** Menyediakan halaman untuk mengakses dan mengelola laporan operasi.
- **Daftar Laporan:** Menampilkan data laporan yang tersimpan dalam sistem.
- **Pembuatan Dokumen PDF:** Menggunakan library TCPDF untuk mendukung pembuatan dokumen PDF.
- **Integrasi Database:** Menggunakan MySQL untuk menyimpan dan mengelola data laporan.
- **Antarmuka Web:** Menggunakan Bootstrap 5 untuk membantu membangun tata letak antarmuka aplikasi.

## 🛠️ Teknologi yang Digunakan

- **PHP** — menangani logika aplikasi dan pemrosesan data di sisi server.
- **MySQL** — menyimpan dan mengelola data aplikasi.
- **HTML** — menyusun struktur halaman web.
- **CSS** — mengatur tampilan antarmuka.
- **Bootstrap 5** — membantu membangun layout dan komponen antarmuka.
- **TCPDF** — mendukung pembuatan dokumen PDF.
- **XAMPP** — dapat digunakan untuk menjalankan aplikasi PHP dan MySQL secara lokal.

## 🚀 Cara Menjalankan Proyek

1. Clone repository:
   ```bash
   git clone https://github.com/howto2/pelaporan-pln.git
   ```
2. Pindahkan folder proyek ke direktori `htdocs` pada instalasi XAMPP.
3. Jalankan Apache dan MySQL melalui XAMPP Control Panel.
4. Buka phpMyAdmin dan buat database sesuai struktur database proyek.
5. Import file `data_godzilla.sql` ke database yang telah dibuat.
6. Periksa dan sesuaikan konfigurasi koneksi database pada `koneksi.php`.
7. Buka aplikasi melalui browser menggunakan alamat lokal sesuai nama folder proyek.

> Catatan: Sesuaikan nama database dan konfigurasi koneksi dengan isi file SQL serta `koneksi.php`.

## 📁 Struktur Proyek

```text
pelaporan-pln/
├── admin/                       # Modul admin
├── user/                        # Modul pengguna
├── bootstrap5/                  # Aset Bootstrap
├── img/                         # Aset gambar
├── tcpdf/                       # Library pembuatan PDF
├── index.php                    # Halaman utama
├── koneksi.php                  # Koneksi database
├── proses_login.php             # Pemrosesan login
├── operasi-admin.php            # Halaman operasi admin
├── pelaporan-operasi-admin.php  # Halaman pelaporan operasi
├── daftar_laporan_admin.php     # Daftar laporan admin
├── edit_laporan.php             # Pemrosesan edit laporan
├── hapus_laporan.php            # Pemrosesan hapus laporan
├── logout.php                   # Proses logout
└── data_godzilla.sql             # Struktur dan data database
```

## 🎯 Tujuan Proyek

Mengembangkan aplikasi web untuk mendukung pengelolaan laporan operasi secara terstruktur, mempermudah akses terhadap data laporan, serta membantu dokumentasi laporan dalam bentuk digital.

## 👨‍💻 Pengembang
Enriko Danu Raharja & M. Zakki Nabilah
GitHub: [howto2](https://github.com/howto2)

## 📌 Repository

[https://github.com/howto2/pelaporan-pln](https://github.com/howto2/pelaporan-pln)
