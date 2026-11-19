<?php
require_once dirname(__DIR__) . '/config.php';
require_once dirname(__DIR__) . '/app/student-form.php';
form_page(require_student(student_id($_GET['id'] ?? null)),[],true);
