<?php
require_once dirname(__DIR__) . '/config.php';
check_post();
[$data, $errors] = validate_student($_POST);
[$photo, $photoError] = read_photo($_FILES, true);
if ($photoError) $errors['foto'] = $photoError;
if ($quota = photo_quota($photo)) $errors['foto'] = $quota;
if (count(students()) >= 30) $errors['nama'] = 'Ruang demo maksimal 30 siswa. Hapus data yang tidak diperlukan.';
if ($errors) { http_response_code(422); require_once dirname(__DIR__) . '/app/student-form.php'; form_page($data, $errors); exit; }
$query = db()->prepare('INSERT INTO calon_siswa (workspace_id,nama,alamat,jenis_kelamin,agama,sekolah_asal,created_at,foto,foto_mime,foto_nama) VALUES (?,?,?,?,?,?,?,?,?,?)');
$values = [$_SESSION['workspace'], $data['nama'], $data['alamat'], $data['jenis_kelamin'], $data['agama'], $data['sekolah_asal'], date('Y-m-d H:i:s'), $photo['bytes'], $photo['mime'], $photo['name']];
foreach ($values as $i => $value) $query->bindValue($i + 1, $value, $i === 7 ? PDO::PARAM_LOB : PDO::PARAM_STR);
$query->execute();
$id = db()->lastInsertId();
notice('Pendaftaran dan pas foto ' . $data['nama'] . ' berhasil disimpan.');
redirect('/detail.php?id=' . $id);
