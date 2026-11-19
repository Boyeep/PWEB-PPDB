const menu = document.querySelector('.menu-button');
menu?.addEventListener('click', () => {
  const opened = menu.getAttribute('aria-expanded') !== 'true';
  menu.setAttribute('aria-expanded', String(opened));
  menu.setAttribute('aria-label', opened ? 'Tutup navigasi' : 'Buka navigasi');
  document.body.classList.toggle('nav-open', opened);
});
document.addEventListener('keydown', (event) => {
  if (event.key === 'Escape' && document.body.classList.contains('nav-open')) menu?.click();
});
document.querySelector('[aria-invalid="true"]')?.focus();

document.querySelector('#print-receipt')?.addEventListener('click', () => window.print());
const photoInput = document.querySelector('#foto');
const photoMessage = document.querySelector('#photo-message');
const preview = document.querySelector('#photo-preview');
let objectUrl;
const originalPreview = preview?.innerHTML;
photoInput?.addEventListener('change', () => {
  photoInput.setCustomValidity('');
  if (objectUrl) URL.revokeObjectURL(objectUrl);
  preview.innerHTML = originalPreview;
  const file = photoInput.files[0];
  if (!file) { photoMessage.textContent = 'Pilih file untuk melihat pratinjau.'; return; }
  const fail = (text) => { photoInput.setCustomValidity(text); photoMessage.textContent = text; };
  if (!['image/jpeg', 'image/png'].includes(file.type)) { fail('Pilih file JPG atau PNG.'); return; }
  if (file.size > 1048576) { fail('Ukuran foto maksimal 1 MB.'); return; }
  objectUrl = URL.createObjectURL(file);
  const sourceUrl = objectUrl;
  const image = new Image();
  image.onload = () => {
    if (sourceUrl !== objectUrl) return;
    if (image.width < 150 || image.height < 200 || image.width > 2400 || image.height > 3200) { fail('Dimensi foto harus 150 x 200 sampai 2400 x 3200 piksel.'); return; }
    if (Math.abs(image.width / image.height - .75) > .025) { fail('Pilih foto tegak dengan rasio 3:4. Contoh: 300 x 400 piksel.'); return; }
    image.className = 'passport'; image.alt = 'Pratinjau pas foto terpilih';
    preview.replaceChildren(image);
    photoMessage.textContent = `${file.name} / ${image.naturalWidth} x ${image.naturalHeight} px / ${Math.ceil(file.size / 1024)} KB. Siap diunggah.`;
  };
  image.onerror = () => { if (sourceUrl === objectUrl) fail('Foto tidak bisa dibaca. Pilih ulang gambar yang valid.'); };
  image.src = objectUrl;
});
document.querySelector('.student-form')?.addEventListener('submit', (event) => {
  const submit = event.currentTarget.querySelector('button[type="submit"]');
  submit.disabled = true; submit.textContent = 'Menyimpan...';
});
window.addEventListener('pageshow', () => { const button = document.querySelector('.student-form button[type="submit"]'); if (button?.disabled) { button.disabled = false; button.textContent = 'Simpan kembali'; } });
