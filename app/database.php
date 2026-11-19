<?php
declare(strict_types=1);

function db(): PDO {
    static $connection = null;
    if ($connection instanceof PDO) return $connection;
    $options = [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION, PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC];
    if (getenv('DB_DRIVER') === 'mysql') {
        $dsn = sprintf('mysql:host=%s;port=%s;dbname=%s;charset=utf8mb4', getenv('DB_HOST') ?: '127.0.0.1', getenv('DB_PORT') ?: '3306', getenv('DB_DATABASE') ?: 'ppdb_sekolah');
        $connection = new PDO($dsn, getenv('DB_USERNAME') ?: 'root', getenv('DB_PASSWORD') ?: '', $options + [PDO::ATTR_EMULATE_PREPARES => false]);
    } else {
        $connection = new PDO('sqlite:' . (getenv('SQLITE_PATH') ?: dirname(__DIR__) . '/storage/ruangdaftar.sqlite'), null, null, $options);
        $connection->exec('PRAGMA busy_timeout = 5000');
        $connection->exec('CREATE TABLE IF NOT EXISTS calon_siswa (id INTEGER PRIMARY KEY AUTOINCREMENT, workspace_id TEXT NOT NULL, nama TEXT NOT NULL, alamat TEXT NOT NULL, jenis_kelamin TEXT NOT NULL, agama TEXT NOT NULL, sekolah_asal TEXT NOT NULL, created_at TEXT NOT NULL, foto BLOB NULL, foto_mime TEXT NULL, foto_nama TEXT NULL)');
        $connection->exec('CREATE INDEX IF NOT EXISTS siswa_workspace ON calon_siswa(workspace_id, id)');
    }
    if (empty($_SESSION['seeded'])) {
        $connection->beginTransaction();
        try {
            $query = $connection->prepare('SELECT COUNT(*) FROM calon_siswa WHERE workspace_id = ?');
            $query->execute([$_SESSION['workspace']]);
            if ((int)$query->fetchColumn() === 0) {
                $examples = [
                    ['Alya Prameswari', 'Jl. Melati No. 12, Surabaya', 'Perempuan', 'Islam', 'SMP Negeri 1 Surabaya'],
                    ['Bima Aditya', 'Jl. Kenanga No. 8, Surabaya', 'Laki-laki', 'Kristen', 'SMP Negeri 6 Surabaya'],
                    ['Citra Anindya', 'Jl. Mawar No. 21, Sidoarjo', 'Perempuan', 'Hindu', 'SMP Negeri 2 Sidoarjo'],
                    ['Daffa Ramadhan', 'Jl. Anggrek No. 5, Surabaya', 'Laki-laki', 'Islam', 'SMP Negeri 3 Surabaya'],
                    ['Nadia Kirana', 'Jl. Dahlia No. 16, Gresik', 'Perempuan', 'Katolik', 'SMP Negeri 1 Gresik'],
                    ['Raka Mahendra', 'Jl. Flamboyan No. 9, Surabaya', 'Laki-laki', 'Buddha', 'SMP Negeri 5 Surabaya'],
                ];
                $insert = $connection->prepare('INSERT INTO calon_siswa (workspace_id,nama,alamat,jenis_kelamin,agama,sekolah_asal,created_at) VALUES (?,?,?,?,?,?,?)');
                foreach ($examples as $row) $insert->execute([$_SESSION['workspace'], ...$row, date('Y-m-d H:i:s')]);
            }
            $connection->commit();
            $_SESSION['seeded'] = true;
        } catch (Throwable $error) { $connection->rollBack(); throw $error; }
    }
    return $connection;
}
function students(string $search = '', string $gender = ''): array {
    $sql = 'SELECT id,workspace_id,nama,alamat,jenis_kelamin,agama,sekolah_asal,created_at,foto_mime,foto_nama FROM calon_siswa WHERE workspace_id = ?'; $params = [$_SESSION['workspace']];
    if ($search !== '') {
        $pattern = '%' . str_replace(['!', '%', '_'], ['!!', '!%', '!_'], $search) . '%';
        $sql .= " AND (nama LIKE ? ESCAPE '!' OR sekolah_asal LIKE ? ESCAPE '!')";
        array_push($params, $pattern, $pattern);
    }
    if (in_array($gender, GENDERS, true)) { $sql .= ' AND jenis_kelamin = ?'; $params[] = $gender; }
    $query = db()->prepare($sql . ' ORDER BY id DESC'); $query->execute($params); return $query->fetchAll();
}
function student(int $id): array|false {
    $query = db()->prepare('SELECT id,workspace_id,nama,alamat,jenis_kelamin,agama,sekolah_asal,created_at,foto_mime,foto_nama FROM calon_siswa WHERE id = ? AND workspace_id = ?');
    $query->execute([$id, $_SESSION['workspace']]); return $query->fetch();
}
function require_student(int $id): array {
    $row = student($id);
    if (!$row) { http_response_code(404); page_start('Data tidak ditemukan', 'siswa'); echo '<section class="panel empty"><h1>Siswa tidak ditemukan</h1><p>Data ini sudah dihapus atau tidak ada di ruang demo Anda.</p><a class="button" href="/list-siswa.php">Kembali ke daftar siswa</a></section>'; page_end(); exit; }
    return $row;
}
