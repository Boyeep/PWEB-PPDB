<?php
require_once dirname(__DIR__) . '/config.php';
if (!in_array($_SERVER['REQUEST_METHOD'], ['GET','HEAD'], true)) { header('Allow: GET, HEAD'); http_response_code(405); exit; }
$q = db()->prepare('SELECT foto,foto_mime,foto_nama FROM calon_siswa WHERE id=? AND workspace_id=?');
$q->execute([student_id($_GET['id'] ?? null), $_SESSION['workspace']]);
$photo = $q->fetch();
if (!$photo || !$photo['foto'] || !in_array($photo['foto_mime'], ['image/jpeg','image/png'], true)) { http_response_code(404); exit('Foto tidak ditemukan.'); }
session_write_close();
header('Content-Type: ' . $photo['foto_mime']);
header('Content-Disposition: inline; filename="' . $photo['foto_nama'] . '"');
header('Content-Length: ' . strlen($photo['foto']));
if ($_SERVER['REQUEST_METHOD'] !== 'HEAD') echo $photo['foto'];
