<?php
session_start();
require_once 'config.php';
require_once 'ticket_functions.php';

// بررسی وجود پارامترهای لازم
if (!isset($_GET['start_date']) || !isset($_GET['end_date']) || !isset($_GET['user_id'])) {
    die("پارامترهای لازم ارسال نشده است.");
}

$start_date = $_GET['start_date'];
$end_date = $_GET['end_date'];
$user_id = $_GET['user_id'];

// اگر user_id خالی است، از کاربر جلسه استفاده کن
if (empty($user_id)) {
    $user_id = $_SESSION['user_id'] ?? null;
}

// اگر هنوز user_id نداریم، خطا بده
if (empty($user_id)) {
    die("کاربر انتخاب نشده است.");
}

// دریافت اطلاعات کاربر
$user = get_user_by_id($user_id);
if (!$user) {
    die("کاربر یافت نشد.");
}

// بقیه کدهای تولید PDF...
?>
