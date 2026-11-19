# Deployment RuangDaftar

Target produksi: https://pweb-ppdb.vercel.app

Vercel menjalankan PHP melalui community runtime vercel-php@0.9.0. Router api/index.php menggunakan allowlist halaman. Aset public/assets dilayani statis. Source GitHub berada pada Boyeep/PWEB-PPDB.

Produksi menggunakan SQLite dan Vercel Blob privat khusus pweb-ppdb-data. Snapshot sesi dan database memuat byte foto. Setiap request mengambil snapshot terbaru; penulisan menggunakan ETag agar perubahan bersamaan tidak saling menimpa. Masa berlaku snapshot dan cookie tujuh hari. Menghapus cookie membuka ruang baru.

Environment produksi: BLOB_READ_WRITE_TOKEN dan BLOB_STORE_ID. Kredensial hanya disimpan di konfigurasi hosting dan tidak ikut Git. Jika penyimpanan gagal, aplikasi tidak mengirim redirect keberhasilan. Tidak ada penyimpanan permanen yang hanya bergantung pada /tmp serverless.

Mode MySQL/MariaDB menggunakan database lokal atau server PHP konvensional. Gunakan database khusus dengan schema database/mysql.sql. Snapshot Blob digunakan khusus adapter SQLite produksi.

Deployment produksi berstatus Ready dan GitHub terhubung untuk deployment pada push main berikutnya. Domain publik merespons HTTP 200 tanpa login. Dua puluh tiga pemeriksaan produksi lolos menggunakan akses HTTP anonim, termasuk upload PNG, edit tanpa mengganti foto, penggantian ke JPEG, bukti pendaftaran, dan penghapusan. Tiga pemeriksaan membaca snapshot privat secara langsung untuk memastikan byte foto dan penghapusan tersimpan secara durabel. Kredensial Blob hanya berada dalam memori proses pengujian.

Verifikasi lokal: 37 pemeriksaan SQLite dan 37 pemeriksaan MySQL/MariaDB lolos. Laporan tersedia di qa-sqlite.json, qa-mysql.json, dan qa-production.json. Data pengujian menggunakan ruang sesi baru dengan identitas rekaan dan pendaftaran uji dihapus setelah pemeriksaan.

Deployment dapat diperbarui dengan `npx vercel --prod --yes` dari folder proyek yang terhubung. Browser otomatis tidak tersedia, sehingga layout desktop/ponsel, pratinjau JavaScript, dialog cetak, dan hasil PDF belum diverifikasi secara visual.
