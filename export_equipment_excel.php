<?php
session_start();
require_once 'config.php';
require_once 'user_functions.php';
require_once 'equipment_functions.php';

// بررسی اگر کاربر وارد نشده باشد
if (!isset($_SESSION['user'])) {
    header('Location: login.php');
    exit;
}

$user = $_SESSION['user'];
$is_it_admin = ($user['user_type'] === 'admin' && isset($user['department']) && $user['department'] === 'فناوری اطلاعات');

if (!$is_it_admin) {
    header('Location: dashboard.php');
    exit;
}

// دریافت پارامترهای جستجو
$search_type = isset($_GET['type']) ? $_GET['type'] : '';
$search_term = isset($_GET['term']) ? urldecode($_GET['term']) : '';
$user_id = isset($_GET['user_id']) ? $_GET['user_id'] : '';

if (empty($search_type) || empty($search_term)) {
    die('پارامترهای جستجو مشخص نشده است');
}

$equipmentFunctions = new EquipmentFunctions();
$search_results = [];

// دریافت نتایج بر اساس نوع جستجو
if ($search_type === 'name' && !empty($user_id)) {
    // جستجو بر اساس کاربر انتخاب شده
    $search_results = $equipmentFunctions->getUserEquipment($user_id);
} else {
    // جستجو بر اساس عبارت
    if ($search_type === 'name') {
        $search_results = $equipmentFunctions->searchEquipmentByUserName($search_term);
    } else {
        $search_results = $equipmentFunctions->searchEquipmentBySerial($search_term);
    }
}

// هدرهای Excel
header('Content-Type: application/vnd.ms-excel; charset=utf-8');
header('Content-Disposition: attachment; filename="equipment_report_' . ($search_type === 'name' ? 'user_' : 'serial_') . $search_term . '_' . date('Y-m-d') . '.xls"');
header('Pragma: no-cache');
header('Expires: 0');

// محتوای Excel با پشتیبانی فارسی
echo '<html>';
echo '<head>';
echo '<meta http-equiv="Content-Type" content="text/html; charset=utf-8">';
echo '</head>';
echo '<body>';

echo '<table border="1" dir="rtl">';
echo '<tr>';
echo '    <th>ردیف</th>';
echo '    <th>نوع تجهیز</th>';
echo '    <th>برند</th>';
echo '    <th>مدل</th>';
echo '    <th>کاربر</th>';
echo '    <th>دپارتمان</th>';
echo '    <th>شماره سریال</th>';
echo '    <th>پردازنده</th>';
echo '    <th>حافظه رم</th>';
echo '    <th>هارد دیسک</th>';
echo '    <th>تاریخ ثبت</th>';
echo '    <th>آخرین بروزرسانی</th>';
echo '</tr>';

foreach ($search_results as $index => $equipment) {
    $type_names = [
        'desktop' => 'کامپیوتر',
        'laptop' => 'لپ‌تاپ', 
        'surface' => 'سرفیس',
        'monitor' => 'مانیتور',
        'printer' => 'پرینتر',
        'scanner' => 'اسکنر',
        'other' => 'سایر'
    ];
    
    echo '<tr>';
    echo '    <td>' . ($index + 1) . '</td>';
    echo '    <td>' . $type_names[$equipment['equipment_type']] . '</td>';
    echo '    <td>' . $equipment['brand'] . '</td>';
    echo '    <td>' . $equipment['model'] . '</td>';
    echo '    <td>' . $equipment['full_name'] . '</td>';
    echo '    <td>' . $equipment['department'] . '</td>';
    echo '    <td>' . ($equipment['serial_number'] ?: '---') . '</td>';
    echo '    <td>' . ($equipment['cpu'] ?: '---') . '</td>';
    echo '    <td>' . ($equipment['ram'] ?: '---') . '</td>';
    echo '    <td>' . ($equipment['hdd'] ?: '---') . '</td>';
    echo '    <td>' . date('Y/m/d H:i', strtotime($equipment['created_at'])) . '</td>';
    echo '    <td>' . date('Y/m/d H:i', strtotime($equipment['updated_at'])) . '</td>';
    echo '</tr>';
}

echo '</table>';

echo '</body>';
echo '</html>';
?>
