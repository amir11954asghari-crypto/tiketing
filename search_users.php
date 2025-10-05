<?php
// search_users.php
session_start();
require_once 'config.php';
require_once 'user_functions.php';

// بررسی اگر کاربر وارد نشده باشد یا ادمین نباشد
if (!isset($_SESSION['user'])) {
    header('HTTP/1.1 403 Forbidden');
    exit;
}

$user = $_SESSION['user'];
$is_it_admin = ($user['user_type'] === 'admin' && isset($user['department']) && $user['department'] === 'فناوری اطلاعات');

if (!$is_it_admin) {
    header('HTTP/1.1 403 Forbidden');
    exit;
}

// دریافت عبارت جستجو
$search_term = isset($_GET['q']) ? trim($_GET['q']) : '';

if (strlen($search_term) < 3) {
    echo json_encode([]);
    exit;
}

// جستجوی کاربران
$userFunctions = new UserFunctions();
$all_users = $userFunctions->getAllUsers();

// فیلتر کردن کاربران بر اساس فامیل
$filtered_users = array_filter($all_users, function($user) use ($search_term) {
    // استخراج فامیل از نام کامل (آخرین کلمه)
    $name_parts = explode(' ', $user['full_name']);
    $last_name = end($name_parts);
    
    // جستجو در فامیل
    if (stripos($last_name, $search_term) === 0) {
        return true;
    }
    
    // جستجو در نام کامل
    if (stripos($user['full_name'], $search_term) !== false) {
        return true;
    }
    
    return false;
});

// محدود کردن نتایج به 10 مورد
$results = array_slice($filtered_users, 0, 10);

// بازگرداندن نتایج به صورت JSON
header('Content-Type: application/json; charset=utf-8');
echo json_encode($results, JSON_UNESCAPED_UNICODE);
?>