# PWEB-PPDB | RuangDaftar

Aplikasi simulasi PPDB dengan PHP, PDO, MySQL, dan unggah pas foto.

- Demo: https://pweb-ppdb.vercel.app
- Artikel: https://pweb-ppdb.vercel.app/blog.php
- Repository: https://github.com/Boyeep/PWEB-PPDB
- [HTML Blogger](docs/blogspot.html) dan [petunjuk artikel](docs/BLOGSPOT.md)
- [Deployment](docs/DEPLOYMENT.md) dan [pemeriksaan fitur](docs/CHECKLIST.md)

## Fitur

Dashboard, pendaftaran, pencarian, filter, paginasi, detail, edit, dan hapus dengan konfirmasi. Upload pas foto JPG/PNG dengan pratinjau, validasi server, penggantian foto, dan bukti cetak A4 melalui dialog cetak browser.

Foto maksimal 1 MB, dimensi 150 x 200 sampai 2400 x 3200 piksel, rasio lebar/tinggi 0,75 dengan toleransi 0,025. Maksimal 30 siswa dan total byte foto 8 MB per ruang demo. Enam data fiktif awal belum memiliki foto agar pengguna bisa mencoba melengkapi profil. Bukti tersedia setelah foto diunggah.

## Menjalankan lokal

PHP 8.2+ dengan PDO, pdo_sqlite, mbstring, Fileinfo, dan cURL. Untuk MySQL aktifkan pdo_mysql.

```sh
git clone https://github.com/Boyeep/PWEB-PPDB.git
cd PWEB-PPDB
php -S localhost:8000 -t public
```

Buka http://localhost:8000. SQLite dibuat otomatis dalam storage/. Di XAMPP Windows, PHP biasanya berada di C:\xampp\php\php.exe. Document root harus public/.

### MySQL / MariaDB

1. Siapkan database khusus proyek ini dan impor database/mysql.sql.
2. Salin .env.example menjadi .env.
3. Atur DB_DRIVER=mysql dan seluruh variabel koneksi. Nama database default ppdb_sekolah.
4. Jalankan server dengan document root public/.

SQL menggunakan CREATE TABLE IF NOT EXISTS dan tidak memigrasi database dari proyek lama. Gunakan database kosong khusus proyek atau migrasikan secara terencana.

## Penyimpanan foto

storage/images/ digunakan sebagai staging sementara di luar folder publik. Setelah validasi dan move_uploaded_file(), byte foto disimpan pada kolom BLOB bersama data siswa melalui satu INSERT atau UPDATE. File staging dihapus dalam finally. Penggantian foto mengganti kolom yang sama; penghapusan baris menghapus foto bersamanya. foto.php memeriksa kepemilikan ruang sebelum mengirim gambar.

Demo Vercel memakai SQLite dengan snapshot sesi dan database di Vercel Blob privat. Snapshot juga memuat byte foto, memiliki TTL tujuh hari, dan ditulis bersyarat menggunakan ETag. Mode lokal MySQL berbeda dari adapter SQLite produksi. Detail tersedia pada docs/DEPLOYMENT.md.

## Pengujian

```sh
python -m pip install -r tests/requirements.txt
python tests/check_http.py
```

Server uji default http://127.0.0.1:8124. Atur TEST_BASE_URL untuk target lain dan TEST_REPORT untuk nama laporan. TEST_NORMAL_ONLY=1 menjalankan alur normal untuk hosting. Setiap pengujian memakai sesi baru dan data fiktif sendiri. Laporan JSON mencatat pemeriksaan yang selesai dijalankan.

Browser otomatis tidak tersedia di lingkungan pengerjaan. Layout desktop, ponsel, pratinjau JavaScript, dialog cetak, dan PDF belum diperiksa secara visual. Pengujian HTTP tidak menggantikan pemeriksaan tersebut.

Ruang per cookie merupakan pemisahan demo, belum sistem akun petugas. Gunakan data dan gambar contoh. Font Inter disimpan lokal dengan lisensi OFL. Source dan artikel tidak menggunakan em dash atau en dash.
