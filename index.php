<?php
session_start();
error_reporting(E_ALL);
ini_set('display_errors', 1);

// بررسی وجود فایل‌های مورد نیاز
$required_files = ['config.php', 'user_functions.php', 'ticket_functions.php'];
foreach ($required_files as $file) {
    if (!file_exists($file)) {
        die("فایل $file یافت نشد");
    }
}

require_once 'config.php';
require_once 'user_functions.php';
require_once 'ticket_functions.php';

// اگر کاربر وارد شده باشد، به داشبورد هدایت شود
if (isset($_SESSION['user'])) {
    header('Location: dashboard.php');
    exit;
}

// اگر کاربر وارد نشده باشد، به صفحه لاگین هدایت شود
header('Location: login.php');
exit;
?>