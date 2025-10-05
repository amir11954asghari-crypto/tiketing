<?php
session_start();
require_once 'config.php';
require_once 'user_functions.php';
require_once 'ticket_functions.php';

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

$search_name = isset($_GET['name']) ? urldecode($_GET['name']) : '';

if (empty($search_name)) {
    die('نام کاربر مشخص نشده است');
}

$ticketFunctions = new TicketFunctions();
$tickets = $ticketFunctions->searchTicketsByUserName($search_name);

// هدرهای Excel
header('Content-Type: application/vnd.ms-excel');
header('Content-Disposition: attachment; filename="report_' . $search_name . '_' . date('Y-m-d') . '.xls"');
header('Pragma: no-cache');
header('Expires: 0');

// محتوای Excel
echo "<table border='1'>";
echo "<tr>
        <th>ردیف</th>
        <th>عنوان تیکت</th>
        <th>کاربر</th>
        <th>دپارتمان</th>
        <th>دسته‌بندی</th>
        <th>اولویت</th>
        <th>وضعیت</th>
        <th>شرح مشکل</th>
        <th>پاسخ ادمین</th>
        <th>تاریخ ثبت</th>
        <th>آخرین بروزرسانی</th>
    </tr>";

foreach ($tickets as $index => $ticket) {
    $status_text = [
        'new' => 'جدید',
        'in-progress' => 'در دست بررسی', 
        'resolved' => 'حل شده'
    ];
    
    $priority_text = [
        'low' => 'کم',
        'medium' => 'متوسط',
        'high' => 'بالا', 
        'urgent' => 'فوری'
    ];
    
    echo "<tr>
            <td>" . ($index + 1) . "</td>
            <td>" . $ticket['title'] . "</td>
            <td>" . $ticket['user_full_name'] . "</td>
            <td>" . $ticket['user_department'] . "</td>
            <td>" . $ticket['category'] . "</td>
            <td>" . $priority_text[$ticket['priority']] . "</td>
            <td>" . $status_text[$ticket['status']] . "</td>
            <td>" . $ticket['description'] . "</td>
            <td>" . ($ticket['admin_notes'] ?? '---') . "</td>
            <td>" . date('Y/m/d H:i', strtotime($ticket['created_at'])) . "</td>
            <td>" . date('Y/m/d H:i', strtotime($ticket['updated_at'])) . "</td>
        </tr>";
}

echo "</table>";
?>