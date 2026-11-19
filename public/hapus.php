<?php
require_once dirname(__DIR__) . '/config.php';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    check_post(); $row = require_student(student_id($_POST['id'] ?? null));
    $query = db()->prepare('DELETE FROM calon_siswa WHERE id=? AND workspace_id=?');
    $query->execute([$row['id'],$_SESSION['workspace']]);
    notice('Data ' . $row['nama'] . ' berhasil dihapus.'); redirect('/list-siswa.php');
}
if ($_SERVER['REQUEST_METHOD'] !== 'GET') { header('Allow: GET, POST'); http_response_code(405); exit('Metode tidak diizinkan.'); }
$row = require_student(student_id($_GET['id'] ?? null));
page_start('Konfirmasi hapus','siswa');
?>
<section class="panel confirm-panel"><span class="delete-icon"><?=icon('trash')?></span><p class="eyebrow">KONFIRMASI PENGHAPUSAN</p><h1>Hapus data siswa ini?</h1><p>Data <strong><?=e($row['nama'])?></strong> dari <?=e($row['sekolah_asal'])?> akan dihapus dari ruang demo Anda. Tindakan ini tidak dapat dibatalkan.</p><form method="post" action="/hapus.php"><?=csrf_field()?><input type="hidden" name="id" value="<?=$row['id']?>"><div class="confirm-actions"><a class="button secondary" href="/detail.php?id=<?=$row['id']?>">Batal, kembali</a><button class="button danger" type="submit"><?=icon('trash')?> Ya, hapus siswa</button></div></form></section>
<?php page_end(); ?>
