"""Exercise upload, persistence, ownership, and CRUD with fictitious data."""
import io
import json
import os
from pathlib import Path
import re
import requests
from bs4 import BeautifulSoup
from PIL import Image

BASE = os.getenv('TEST_BASE_URL', 'http://127.0.0.1:8124').rstrip('/')
SAFE = os.getenv('TEST_NORMAL_ONLY') == '1'
checks = []
s = requests.Session()
if os.getenv('TEST_BYPASS_TOKEN'):
    s.headers['x-vercel-protection-bypass'] = os.environ['TEST_BYPASS_TOKEN']

def check(name, condition):
    checks.append({'name': name, 'passed': bool(condition)})
    if not condition: raise AssertionError(name)
    print('PASS:', name, flush=True)

def get(path, session=s):
    return session.get(BASE + path, timeout=60)

def csrf(response):
    return BeautifulSoup(response.text, 'html.parser').select_one('[name=csrf]')['value']

def image(color, size=(300, 400), fmt='PNG'):
    target = io.BytesIO()
    Image.new('RGB', size, color).save(target, format=fmt)
    return target.getvalue()

def send(data, blob=None, path='/proses-pendaftaran.php', filename='foto.png', mime='image/png'):
    return s.post(BASE + path, data=data, files={'foto': (filename, blob, mime)} if blob is not None else None, timeout=60, allow_redirects=False)

data = {'nama': 'Siswa Uji PPDB', 'alamat': 'Jalan Contoh Nomor 12, Kota Contoh', 'sekolah_asal': 'SMP Contoh', 'jenis_kelamin': 'Perempuan', 'agama': 'Islam'}
first = get('/form-daftar.php')
check('registration form and multipart encoding', first.status_code == 200 and 'multipart/form-data' in first.text)
data['csrf'] = csrf(first)
check('dashboard opens', get('/').status_code == 200)
check('guide and blog open', get('/panduan.php').status_code == 200 and get('/blog.php').status_code == 200)
if not SAFE:
    check('missing photo rejected', send(data).status_code == 422)
    check('spoofed MIME rejected', send(data, b'<?php echo 1; ?>').status_code == 422)
    check('square photo rejected', send(data, image('red', (300,300))).status_code == 422)
    check('undersized photo rejected', send(data, image('red',(75,100))).status_code == 422)
    check('oversized upload rejected', send(data, b'x' * 1048577).status_code == 422)
    check('invalid CSRF rejected', send(data | {'csrf':'invalid'}, image('blue')).status_code == 403)
    check('array upload rejected', s.post(BASE+'/proses-pendaftaran.php',data=data,files={'foto[]':('file.png',image('red'),'image/png')},timeout=30).status_code == 422)
    check('invalid enum rejected', send(data | {'agama':'invalid'}, image('blue')).status_code == 422)
    check('upload endpoint requires POST', get('/proses-pendaftaran.php').status_code == 405)

blob = image('royalblue')
created = send(data, blob, filename='../../attempt.php', mime='application/octet-stream')
check('valid image with unsafe filename safely accepted', created.status_code == 303)
location = created.headers['Location']
student_id = re.search(r'id=(\d+)', location).group(1)
detail = get(location)
check('student detail includes stored photo', detail.status_code == 200 and 'Bukti pendaftaran' in detail.text)
photo = get('/foto.php?id='+student_id)
check('photo bytes persist between requests', photo.status_code == 200 and photo.content == blob)
check('server determines MIME', photo.headers.get('Content-Type','').startswith('image/png'))
check('randomized safe stored filename', bool(re.search(r'filename="[a-f0-9]{32}\.png"',photo.headers.get('Content-Disposition',''))))
check('private photo response is not cached', 'no-store' in photo.headers.get('Cache-Control','') and photo.headers.get('X-Content-Type-Options') == 'nosniff')
receipt = get('/bukti.php?id='+student_id)
check('receipt contains student, photo, and print control', receipt.status_code == 200 and 'Siswa Uji PPDB' in receipt.text and 'print-receipt' in receipt.text and '/foto.php?id='+student_id in receipt.text)
check('search returns new student', 'Siswa Uji PPDB' in get('/list-siswa.php?q=Siswa+Uji+PPDB').text)
if not SAFE:
    outsider = requests.Session()
    for endpoint in ['detail.php','foto.php','bukti.php']:
        check('session isolation '+endpoint, get('/'+endpoint+'?id='+student_id, outsider).status_code == 404)
    seed = get('/detail.php?id=1')
    check('incomplete seed receipt unavailable', get('/bukti.php?id=1').status_code == 422)

edit = get('/form-edit.php?id='+student_id)
edited = data | {'csrf':csrf(edit), 'id':student_id, 'nama':'Siswa Uji Diperbarui'}
kept = send(edited,path='/proses-edit.php')
check('edit without new photo accepted', kept.status_code == 303)
check('edit preserves existing photo', get('/foto.php?id='+student_id).content == blob)
if not SAFE:
    rejected = send(edited,image('red',(400,300)),path='/proses-edit.php')
    check('invalid replacement rejected', rejected.status_code == 422)
    check('invalid replacement retains previous photo', get('/foto.php?id='+student_id).content == blob)

new_blob = image('orange',fmt='JPEG')
replacement = send(edited,new_blob,path='/proses-edit.php',filename='new.jpg',mime='image/jpeg')
check('replace photo accepted', replacement.status_code == 303)
check('replacement photo and MIME persist', get('/foto.php?id='+student_id).content == new_blob and get('/foto.php?id='+student_id).headers['Content-Type'].startswith('image/jpeg'))
check('updated name persists', 'Siswa Uji Diperbarui' in get(location).text)
if not SAFE:
    unsafe = edited | {'nama':'<script>alert(1)</script>'}
    check('HTML input stored as text', send(unsafe,path='/proses-edit.php').status_code == 303)
    escaped = get(location).text
    check('stored HTML escaped on output', '&lt;script&gt;alert(1)&lt;/script&gt;' in escaped and '<script>alert(1)</script>' not in escaped)

confirmation = get('/hapus.php?id='+student_id)
check('GET delete shows confirmation', confirmation.status_code == 200 and get(location).status_code == 200)
deleted = s.post(BASE+'/hapus.php',data={'id':student_id,'csrf':csrf(confirmation)},timeout=60,allow_redirects=False)
check('confirmed delete accepted', deleted.status_code == 303)
check('deleted row unavailable', get(location).status_code == 404)
check('deleted photo unavailable', get('/foto.php?id='+student_id).status_code == 404)
report = {'base_url':BASE,'mode':'normal' if SAFE else 'full','passed':len(checks),'checks':checks,'visual_qa':False}
Path(os.getenv('TEST_REPORT','qa-sqlite.json')).write_text(json.dumps(report,indent=2),encoding='utf-8')
print(f'{len(checks)} checks passed.')
