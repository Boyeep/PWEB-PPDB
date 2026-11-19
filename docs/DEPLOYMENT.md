# Deployment RuangDaftar

Target produksi: https://pweb-ppdb.vercel.app

Vercel menjalankan PHP melalui community runtime vercel-php@0.9.0. Router api/index.php menggunakan allowlist halaman. Aset public/assets dilayani statis. Source GitHub berada pada Boyeep/PWEB-PPDB.

Produksi menggunakan SQLite dan Vercel Blob privat khusus pweb-ppdb-data. Snapshot sesi dan database memuat byte foto. Setiap request mengambil snapshot terbaru; penulisan menggunakan ETag agar perubahan bersamaan tidak saling menimpa. Masa berlaku snapshot dan cookie tujuh hari. Menghapus cookie membuka ruang baru.

Environment produksi: BLOB_READ_WRITE_TOKEN dan BLOB_STORE_ID. Kredensial hanya disimpan di konfigurasi hosting dan tidak ikut Git. Jika penyimpanan gagal, aplikasi tidak mengirim redirect keberhasilan. Tidak ada penyimpanan permanen yang hanya bergantung pada /tmp serverless.

Mode MySQL/MariaDB menggunakan database lokal atau server PHP konvensional. Gunakan database khusus dengan schema database/mysql.sql. Snapshot Blob digunakan khusus adapter SQLite produksi.

Deployment dapat diperbarui dengan `npx vercel --prod --yes` dari folder proyek yang terhubung. Pemeriksaan HTTP dan hasil produksi dicatat setelah deployment selesai. QA browser belum tersedia di lingkungan pengerjaan.
