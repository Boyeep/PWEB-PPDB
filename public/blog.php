<?php
require_once dirname(__DIR__) . '/config.php';
page_start('Catatan proyek','blog');
readfile(dirname(__DIR__) . '/docs/blogspot.html');
page_end();
