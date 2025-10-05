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

// ایجاد PDF با پشتیبانی کامل فارسی
require_once('tcpdf/tcpdf.php');

// ایجاد کلاس PDF سفارشی با پشتیبانی فارسی
class MYPDF extends TCPDF {
    
    // Header
    public function Header() {
        // Set font for header
        $this->SetFont('dejavusans', 'B', 12);
        $this->Cell(0, 10, 'گزارش تجهیزات سخت‌افزاری', 0, false, 'C', 0, '', 0, false, 'M', 'M');
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
$pdf->SetCreator('Equipment System');
$pdf->SetAuthor('Admin');
$pdf->SetTitle('گزارش تجهیزات: ' . $search_term);
$pdf->SetSubject('گزارش تجهیزات');

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
    .equipment-item { margin: 15px 0; padding: 10px; border: 1px solid #ddd; background: #f8f9fa; }
    .equipment-title { font-weight: bold; font-size: 14px; color: #2c3e50; }
    .equipment-details { font-size: 12px; color: #666; margin: 5px 0; }
    .equipment-specs { font-size: 11px; margin: 10px 0; }
    .equipment-date { font-size: 10px; color: #999; text-align: left; }
    table { width: 100%; border-collapse: collapse; margin: 10px 0; direction: rtl; }
    th { background-color: #f2f2f2; font-weight: bold; padding: 8px; border: 1px solid #ddd; text-align: right; }
    td { padding: 8px; border: 1px solid #ddd; text-align: right; }
</style>

<div class="header">
    <h1>گزارش تجهیزات سخت‌افزاری</h1>
    <p>تاریخ تولید: ' . date('Y/m/d H:i') . '</p>
</div>

<div class="summary">
    <strong>خلاصه گزارش:</strong><br>
    نوع جستجو: ' . ($search_type === 'name' ? 'نام کاربر' : 'شماره سریال') . ' | 
    عبارت جستجو: ' . $search_term . ' | 
    تعداد تجهیزات: ' . count($search_results) . '
</div>
';

if (!empty($search_results)) {
    // ایجاد جدول خلاصه
    $html .= '
    <table>
        <tr>
            <th width="5%">ردیف</th>
            <th width="20%">نوع تجهیز</th>
            <th width="25%">برند و مدل</th>
            <th width="15%">کاربر</th>
            <th width="15%">دپارتمان</th>
            <th width="10%">شماره سریال</th>
            <th width="10%">تاریخ ثبت</th>
        </tr>';
    
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
        
        $html .= '
        <tr>
            <td>' . ($index + 1) . '</td>
            <td>' . $type_names[$equipment['equipment_type']] . '</td>
            <td>' . $equipment['brand'] . ' ' . $equipment['model'] . '</td>
            <td>' . $equipment['full_name'] . '</td>
            <td>' . $equipment['department'] . '</td>
            <td>' . ($equipment['serial_number'] ?: '---') . '</td>
            <td>' . date('Y/m/d', strtotime($equipment['created_at'])) . '</td>
        </tr>';
    }
    
    $html .= '</table><br><br>';
    
    // جزئیات کامل هر تجهیز
    foreach ($search_results as $index => $equipment) {
        $type_names = [
            'desktop' => 'کامپیوتر رومیزی',
            'laptop' => 'لپ‌تاپ', 
            'surface' => 'سرفیس',
            'monitor' => 'مانیتور',
            'printer' => 'پرینتر',
            'scanner' => 'اسکنر',
            'other' => 'سایر'
        ];
        
        $html .= '
        <div class="equipment-item">
            <div class="equipment-title">تجهیز #' . ($index + 1) . ': ' . $equipment['brand'] . ' ' . $equipment['model'] . '</div>
            
            <div class="equipment-details">
                <strong>نوع:</strong> ' . $type_names[$equipment['equipment_type']] . ' | 
                <strong>کاربر:</strong> ' . $equipment['full_name'] . ' | 
                <strong>دپارتمان:</strong> ' . $equipment['department'] . ' | 
                <strong>شماره سریال:</strong> ' . ($equipment['serial_number'] ?: '---') . '
            </div>';
        
        if (!empty($equipment['cpu']) || !empty($equipment['ram']) || !empty($equipment['hdd'])) {
            $html .= '
            <div class="equipment-specs">
                <strong>مشخصات فنی:</strong><br>';
            
            if (!empty($equipment['cpu'])) {
                $html .= '<strong>پردازنده:</strong> ' . $equipment['cpu'] . '<br>';
            }
            if (!empty($equipment['ram'])) {
                $html .= '<strong>حافظه رم:</strong> ' . $equipment['ram'] . '<br>';
            }
            if (!empty($equipment['hdd'])) {
                $html .= '<strong>هارد دیسک:</strong> ' . $equipment['hdd'] . '<br>';
            }
            
            $html .= '</div>';
        }
        
        $html .= '
            <div class="equipment-date">
                <strong>تاریخ ثبت:</strong> ' . date('Y/m/d H:i', strtotime($equipment['created_at']));
        
        if ($equipment['updated_at'] != $equipment['created_at']) {
            $html .= ' | <strong>آخرین بروزرسانی:</strong> ' . date('Y/m/d H:i', strtotime($equipment['updated_at']));
        }
        
        $html .= '
            </div>
        </div>
        <br>';
    }
} else {
    $html .= '<p style="text-align: center; color: #666;">هیچ تجهیزی یافت نشد.</p>';
}

// نوشتن HTML در PDF
$pdf->writeHTML($html, true, false, true, false, '');

// خروجی PDF
$filename = 'equipment_report_' . ($search_type === 'name' ? 'user_' : 'serial_') . $search_term . '_' . date('Y-m-d') . '.pdf';
$pdf->Output($filename, 'D');
?>
