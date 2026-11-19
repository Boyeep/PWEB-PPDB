<?php
declare(strict_types=1);
const PHOTO_MAX_BYTES = 1048576;
const PHOTO_SPACE_BYTES = 8388608;
function photo_tag(array $row, string $class = 'passport'): string {
    if ($row['foto_mime'] ?? null) return '<img class="' . e($class) . '" src="/foto.php?id=' . (int)$row['id'] . '" alt="Pas foto ' . e($row['nama']) . '" width="150" height="200">';
    return '<span class="' . e($class) . ' photo-placeholder" aria-label="Belum ada pas foto">' . e(mb_strtoupper(mb_substr($row['nama'] ?? '?', 0, 1))) . '</span>';
}
function read_photo(array $files, bool $required): array {
    $file = $files['foto'] ?? null;
    if (!$file || ($file['error'] ?? UPLOAD_ERR_NO_FILE) === UPLOAD_ERR_NO_FILE) return [null, $required ? 'Unggah pas foto JPG atau PNG dengan rasio 3:4.' : null];
    if (!is_array($file) || !is_int($file['error'] ?? null)) return [null, 'Bentuk unggahan tidak valid. Pilih satu foto.'];
    if ($file['error'] !== UPLOAD_ERR_OK) return [null, 'Foto gagal diunggah. Pilih ulang file maksimal 1 MB.'];
    $tmp = $file['tmp_name'] ?? '';
    if (!is_string($tmp) || !is_uploaded_file($tmp)) return [null, 'Sumber unggahan tidak valid. Pilih ulang foto.'];
    $size = filesize($tmp);
    if (!$size || $size > PHOTO_MAX_BYTES) return [null, 'Ukuran foto maksimal 1 MB. Kompres foto lalu coba lagi.'];
    $mime = (new finfo(FILEINFO_MIME_TYPE))->file($tmp);
    $types = ['image/jpeg' => ['jpg', IMAGETYPE_JPEG], 'image/png' => ['png', IMAGETYPE_PNG]];
    $info = @getimagesize($tmp);
    if (!isset($types[$mime]) || !$info || $info[2] !== $types[$mime][1]) return [null, 'Isi file harus berupa gambar JPG atau PNG yang valid.'];
    [$width, $height] = $info;
    if ($width < 150 || $height < 200 || $width > 2400 || $height > 3200) return [null, 'Dimensi foto minimal 150 x 200 dan maksimal 2400 x 3200 piksel.'];
    if (abs($width / $height - 0.75) > 0.025) return [null, 'Gunakan foto tegak dengan rasio 3:4, misalnya 300 x 400 piksel.'];
    $name = bin2hex(random_bytes(16)) . '.' . $types[$mime][0];
    $folder = getenv('VERCEL') === '1' ? sys_get_temp_dir() . '/ppdb-images' : dirname(__DIR__) . '/storage/images';
    if (!is_dir($folder) && !mkdir($folder, 0700, true) && !is_dir($folder)) throw new RuntimeException('Upload staging unavailable.');
    $path = $folder . '/' . $name;
    if (!move_uploaded_file($tmp, $path)) throw new RuntimeException('Upload could not be staged.');
    try {
        $bytes = file_get_contents($path);
        if ($bytes === false) throw new RuntimeException('Staged upload could not be read.');
        return [['bytes' => $bytes, 'mime' => $mime, 'name' => $name], null];
    } finally { if (is_file($path)) unlink($path); }
}
function photo_quota(?array $photo, int $replaceId = 0): ?string {
    if (!$photo) return null;
    $q = db()->prepare('SELECT COALESCE(SUM(LENGTH(foto)),0) FROM calon_siswa WHERE workspace_id=? AND id<>?');
    $q->execute([$_SESSION['workspace'], $replaceId]);
    return (int)$q->fetchColumn() + strlen($photo['bytes']) > PHOTO_SPACE_BYTES ? 'Ruang foto maksimal 8 MB. Hapus data yang tidak diperlukan atau gunakan foto lebih kecil.' : null;
}
function photo_field(array $data, array $errors, bool $editing): void { ?>
<section class="photo-section"><div class="form-section-title"><span>02</span><div><h2>Pas foto calon siswa</h2><p>Foto tegak dengan rasio 3:4, seperti ukuran cetak 3 x 4 cm.</p></div></div><div class="upload-layout"><div class="photo-preview" id="photo-preview"><?=photo_tag($data + ['nama' => '?'])?><span>3 x 4</span></div><div class="field upload-field"><label for="foto"><?=$editing && !empty($data['foto_mime']) ? 'Ganti pas foto (opsional)' : 'Unggah pas foto *'?></label><p id="photo-help">JPG atau PNG, maksimal 1 MB.<br>150 x 200 sampai 2400 x 3200 piksel.</p><input type="hidden" name="MAX_FILE_SIZE" value="1048576"><input type="file" name="foto" id="foto" accept="image/jpeg,image/png" <?=empty($data['foto_mime']) ? 'required' : ''?> aria-describedby="photo-help photo-message<?=isset($errors['foto']) ? ' error-foto' : ''?>" <?=isset($errors['foto']) ? 'aria-invalid="true"' : ''?>><p id="photo-message" aria-live="polite">Pilih file untuk melihat pratinjau.</p><?php if (isset($errors['foto'])): ?><small id="error-foto" class="field-error"><?=e($errors['foto'])?></small><?php endif ?><?php if ($errors): ?><small>Pilih ulang foto setelah memperbaiki isian.</small><?php endif ?><?php if ($editing && !empty($data['foto_mime'])): ?><small>Biarkan kosong untuk mempertahankan foto tersimpan.</small><?php endif ?></div></div></section>
<?php }
