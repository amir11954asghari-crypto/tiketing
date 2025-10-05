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

// دریافت نام کاربر از پارامتر URL
$search_name = isset($_GET['name']) ? urldecode($_GET['name']) : '';

if (empty($search_name)) {
    die('نام کاربر مشخص نشده است');
}

$ticketFunctions = new TicketFunctions();
$tickets = $ticketFunctions->searchTicketsByUserName($search_name);

// ایجاد PDF با پشتیبانی کامل فارسی
require_once('tcpdf/tcpdf.php');

// ایجاد کلاس PDF سفارشی با پشتیبانی فارسی
class MYPDF extends TCPDF {
    
    // Header
    public function Header() {
        // Set font for header
        $this->SetFont('dejavusans', 'B', 12);
        $this->Cell(0, 10, 'گزارش تیکت‌های سیستم مدیریت تیکت', 0, false, 'C', 0, '', 0, false, 'M', 'M');
        $this->Ln(5);
        $this->SetFont('dejavusans', '', 10);
        $this->Cell(0, 10, 'تاریخ تولید: ' . date('Y/m/d H:i'), 0, false, 'C', 0, '', 0, false, 'M', 'M');
        $this->Ln(10);
    }
    
    // Footer
    public function Footer() {
        $this->SetY(-15);
        $this->SetFont('dejavusans', 'I', 8);
        $this->Cell(0, 10, 'صفحه ' . $this->getAliasNumPage() . '/' . $this->getAliasNbPages(), 0, false, 'C', 0, '', 0, false, 'T', 'M');
    }
}

// ایجاد PDF جدید
$pdf = new MYPDF(PDF_PAGE_ORIENTATION, PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);

// تنظیمات سند
$pdf->SetCreator('Ticket System');
$pdf->SetAuthor('Admin');
$pdf->SetTitle('گزارش تیکت‌های کاربر: ' . $search_name);
$pdf->SetSubject('گزارش تیکت‌ها');

// تنظیمات فونت و زبان
$pdf->setFontSubsetting(true);
$pdf->SetFont('dejavusans', '', 10, '', true);
$pdf->setRTL(true); // فعال کردن راست به چپ

// افزودن صفحه
$pdf->AddPage();

// محتوای گزارش
$html = '
<style>
    body { direction: rtl; text-align: right; font-family: dejavusans; }
    .header { text-align: center; margin-bottom: 20px; }
    .summary { background-color: #f8f9fa; padding: 10px; margin: 10px 0; border: 1px solid #ddd; }
    .ticket-item { margin: 15px 0; padding: 10px; border: 1px solid #ddd; background: #f8f9fa; }
    .ticket-title { font-weight: bold; font-size: 14px; color: #2c3e50; }
    .ticket-details { font-size: 12px; color: #666; margin: 5px 0; }
    .ticket-description { font-size: 11px; margin: 10px 0; }
    .admin-notes { background: #e7f3ff; padding: 8px; margin: 5px 0; border-right: 3px solid #2575fc; }
    .ticket-date { font-size: 10px; color: #999; text-align: left; }
    table { width: 100%; border-collapse: collapse; margin: 10px 0; direction: rtl; }
    th { background-color: #f2f2f2; font-weight: bold; padding: 8px; border: 1px solid #ddd; text-align: right; }
    td { padding: 8px; border: 1px solid #ddd; text-align: right; }
</style>

<div class="header">
    <h1>گزارش تیکت‌های کاربر: ' . $search_name . '</h1>
    <p>تاریخ تولید: ' . date('Y/m/d H:i') . '</p>
</div>

<div class="summary">
    <strong>خلاصه گزارش:</strong><br>
    تعداد کل تیکت‌ها: ' . count($tickets) . ' | 
    جدید: ' . count(array_filter($tickets, function($t) { return $t['status'] === 'new'; })) . ' | 
    در دست بررسی: ' . count(array_filter($tickets, function($t) { return $t['status'] === 'in-progress'; })) . ' | 
    حل شده: ' . count(array_filter($tickets, function($t) { return $t['status'] === 'resolved'; })) . '
</div>
';

if (!empty($tickets)) {
    // ایجاد جدول خلاصه
    $html .= '
    <table>
        <tr>
            <th width="5%">ردیف</th>
            <th width="25%">عنوان تیکت</th>
            <th width="15%">کاربر</th>
            <th width="15%">دپارتمان</th>
            <th width="10%">دسته‌بندی</th>
            <th width="10%">اولویت</th>
            <th width="10%">وضعیت</th>
            <th width="10%">تاریخ ثبت</th>
        </tr>';
    
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
        
        $html .= '
        <tr>
            <td>' . ($index + 1) . '</td>
            <td>' . $ticket['title'] . '</td>
            <td>' . $ticket['user_full_name'] . '</td>
            <td>' . $ticket['user_department'] . '</td>
            <td>' . $ticket['category'] . '</td>
            <td>' . $priority_text[$ticket['priority']] . '</td>
            <td>' . $status_text[$ticket['status']] . '</td>
            <td>' . date('Y/m/d', strtotime($ticket['created_at'])) . '</td>
        </tr>';
    }
    
    $html .= '</table><br><br>';
    
    // جزئیات کامل هر تیکت
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
        
        $priority_color = [
            'low' => '#27ae60',
            'medium' => '#f39c12',
            'high' => '#e74c3c', 
            'urgent' => '#c0392b'
        ];
        
        $html .= '
        <div class="ticket-item">
            <div class="ticket-title">تیکت #' . ($index + 1) . ': ' . $ticket['title'] . '</div>
            
            <div class="ticket-details">
                <strong>کاربر:</strong> ' . $ticket['user_full_name'] . ' | 
                <strong>دپارتمان:</strong> ' . $ticket['user_department'] . ' | 
                <strong>دسته‌بندی:</strong> ' . $ticket['category'] . ' | 
                <strong>اولویت:</strong> <span style="color: ' . $priority_color[$ticket['priority']] . ';">' . $priority_text[$ticket['priority']] . '</span> | 
                <strong>وضعیت:</strong> ' . $status_text[$ticket['status']] . '
            </div>
            
            <div class="ticket-description">
                <strong>شرح مشکل:</strong><br>
                ' . $ticket['description'] . '
            </div>';
        
        if (!empty($ticket['admin_notes'])) {
            $html .= '
            <div class="admin-notes">
                <strong>پاسخ ادمین:</strong><br>
                ' . $ticket['admin_notes'] . '
            </div>';
        }
        
        $html .= '
            <div class="ticket-date">
                <strong>تاریخ ثبت:</strong> ' . date('Y/m/d H:i', strtotime($ticket['created_at']));
        
        if ($ticket['updated_at'] != $ticket['created_at']) {
            $html .= ' | <strong>آخرین بروزرسانی:</strong> ' . date('Y/m/d H:i', strtotime($ticket['updated_at']));
        }
        
        $html .= '
            </div>
        </div>
        <br>';
    }
} else {
    $html .= '<p style="text-align: center; color: #666;">هیچ تیکتی یافت نشد.</p>';
}

// نوشتن HTML در PDF
$pdf->writeHTML($html, true, false, true, false, '');

// خروجی PDF
$pdf->Output('report_' . $search_name . '_' . date('Y-m-d') . '.pdf', 'D');
?>