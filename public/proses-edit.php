<?php
require_once dirname(__DIR__) . '/config.php';
check_post();
$row = require_student(student_id($_POST['id'] ?? null));
[$data, $errors] = validate_student($_POST);
[$photo, $photoError] = read_photo($_FILES, !$row['foto_mime']);
if ($photoError) $errors['foto'] = $photoError;
if ($quota = photo_quota($photo, (int)$row['id'])) $errors['foto'] = $quota;
if ($errors) { http_response_code(422); require_once dirname(__DIR__) . '/app/student-form.php'; form_page($data + ['id'=>$row['id'], 'foto_mime'=>$row['foto_mime']], $errors, true); exit; }
$sql = 'UPDATE calon_siswa SET nama=?, alamat=?, jenis_kelamin=?, agama=?, sekolah_asal=?';
$values = [$data['nama'], $data['alamat'], $data['jenis_kelamin'], $data['agama'], $data['sekolah_asal']];
if ($photo) { $sql .= ', foto=?, foto_mime=?, foto_nama=?'; array_push($values, $photo['bytes'], $photo['mime'], $photo['name']); }
$sql .= ' WHERE id=? AND workspace_id=?';
array_push($values, $row['id'], $_SESSION['workspace']);
$query = db()->prepare($sql);
foreach ($values as $i => $value) $query->bindValue($i + 1, $value, $photo && $i === 5 ? PDO::PARAM_LOB : PDO::PARAM_STR);
$query->execute();
notice('Data ' . $data['nama'] . ' berhasil diperbarui.');
redirect('/detail.php?id=' . $row['id']);
