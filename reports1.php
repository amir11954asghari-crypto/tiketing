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

$userFunctions = new UserFunctions();
$ticketFunctions = new TicketFunctions();
$user = $_SESSION['user'];

// تشخیص ادمین فناوری اطلاعات
$is_it_admin = ($user['user_type'] === 'admin' && isset($user['department']) && $user['department'] === 'فناوری اطلاعات');

// اگر کاربر ادمین نیست، به صفحه اصلی هدایت شود
if (!$is_it_admin) {
    header('Location: dashboard.php');
    exit;
}

// متغیرهای جستجو
$search_results = [];
$search_performed = false;
$search_name = '';

// پردازش جستجو
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['search'])) {
    $search_name = trim($_POST['full_name']);
    $search_performed = true;
    
    if (!empty($search_name)) {
        $search_results = $ticketFunctions->searchTicketsByUserName($search_name);
    }
}

// پردازش تولید PDF
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['generate_pdf'])) {
    $full_name = trim($_POST['full_name']);
    
    if (!empty($full_name)) {
        // هدایت به صفحه PDF
        header('Location: generate_pdf.php?name=' . urlencode($full_name));
        exit;
    }
}
?>

<!DOCTYPE html>
<html lang="fa" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>گزارش‌گیری تیکت‌ها - سیستم مدیریت تیکت</title>
    <link rel="stylesheet" href="style.css">
    <style>
        .report-container {
            background: white;
            padding: 30px;
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            margin-bottom: 20px;
        }
        
        .search-form {
            display: grid;
            grid-template-columns: 1fr auto auto;
            gap: 15px;
            align-items: end;
            margin-bottom: 30px;
        }
        
        .search-results {
            margin-top: 30px;
        }
        
        .result-summary {
            background: #f8f9fa;
            padding: 15px;
            border-radius: 5px;
            margin-bottom: 20px;
            border-right: 4px solid var(--primary);
        }
        
        .print-header {
            display: none;
        }
        
        @media print {
            .no-print {
                display: none !important;
            }
            
            .print-header {
                display: block;
                text-align: center;
                margin-bottom: 20px;
                padding-bottom: 10px;
                border-bottom: 2px solid #333;
            }
            
            body {
                background: white !important;
                color: black !important;
            }
            
            .ticket-item {
                break-inside: avoid;
                page-break-inside: avoid;
            }
        }
        
        .action-buttons {
            display: flex;
            gap: 10px;
            margin-top: 20px;
            flex-wrap: wrap;
        }
        
        .stat-badge {
            display: inline-block;
            padding: 3px 8px;
            border-radius: 3px;
            font-size: 12px;
            margin: 0 5px;
        }
        
        .badge-new { background: #d4edda; color: #155724; }
        .badge-in-progress { background: #fff3cd; color: #856404; }
        .badge-resolved { background: #d1ecf1; color: #0c5460; }
    </style>
</head>
<body>
    <header class="admin-panel">
        <div class="container">
            <div class="header-content">
                <div class="logo">سیستم مدیریت تیکت - گزارش‌گیری</div>
                <div class="user-info">
                    <span>خوش آمدید، <strong><?php echo htmlspecialchars($user['full_name']); ?></strong></span>
                    <a href="dashboard.php" class="btn btn-primary">بازگشت به پنل</a>
                    <a href="logout.php" class="btn btn-danger">خروج</a>
                </div>
            </div>
        </div>
    </header>

    <div class="container">
        <div class="welcome-section admin-panel">
            <h1>گزارش‌گیری تیکت‌ها</h1>
            <p>در این بخش می‌توانید گزارش کامل تیکت‌های کاربران را مشاهده و چاپ کنید</p>
        </div>

        <div class="report-container">
            <h2>جستجوی تیکت‌ها بر اساس نام کاربر</h2>
            
            <form method="POST" action="" class="search-form">
                <div class="form-group">
                    <label for="full_name">نام و نام خانوادگی کاربر</label>
                    <input type="text" id="full_name" name="full_name" 
                           value="<?php echo htmlspecialchars($search_name); ?>" 
                           placeholder="نام کامل کاربر را وارد کنید" required>
                </div>
                
                <button type="submit" name="search" class="btn btn-primary">جستجو</button>
                <button type="submit" name="generate_pdf" class="btn btn-secondary">چاپ گزارش PDF</button>
            </form>

            <?php if ($search_performed): ?>
                <div class="search-results">
                    <?php if (empty($search_name)): ?>
                        <div class="notification error">
                            لطفاً نام کاربر را وارد کنید
                        </div>
                    <?php elseif (empty($search_results)): ?>
                        <div class="notification error">
                            هیچ تیکتی برای کاربر "<?php echo htmlspecialchars($search_name); ?>" یافت نشد
                        </div>
                    <?php else: ?>
                        <div class="result-summary">
                            <h3>نتایج جستجو برای: "<?php echo htmlspecialchars($search_name); ?>"</h3>
                            <p>
                                تعداد تیکت‌ها: <strong><?php echo count($search_results); ?></strong> |
                                جدید: <span class="stat-badge badge-new"><?php echo count(array_filter($search_results, function($t) { return $t['status'] === 'new'; })); ?></span> |
                                در دست بررسی: <span class="stat-badge badge-in-progress"><?php echo count(array_filter($search_results, function($t) { return $t['status'] === 'in-progress'; })); ?></span> |
                                حل شده: <span class="stat-badge badge-resolved"><?php echo count(array_filter($search_results, function($t) { return $t['status'] === 'resolved'; })); ?></span>
                            </p>
                        </div>

                        <div class="action-buttons no-print">
                            <button onclick="window.print()" class="btn btn-primary">چاپ گزارش</button>
                            <a href="generate_pdf.php?name=<?php echo urlencode($search_name); ?>" class="btn btn-secondary">دانلود PDF</a>
                            <a href="export_excel.php?name=<?php echo urlencode($search_name); ?>" class="btn btn-success">خروجی Excel</a>
                        </div>

                        <div class="print-header">
                            <h2>گزارش تیکت‌های کاربر: <?php echo htmlspecialchars($search_name); ?></h2>
                            <p>تاریخ تولید: <?php echo date('Y/m/d H:i'); ?></p>
                            <p>تعداد تیکت‌ها: <?php echo count($search_results); ?></p>
                        </div>

                        <div class="ticket-list">
                            <?php foreach ($search_results as $ticket): ?>
                                <div class="ticket-item">
                                    <div class="ticket-header">
                                        <div class="ticket-title">
                                            <?php echo htmlspecialchars($ticket['title']); ?>
                                        </div>
                                        <div class="ticket-status status-<?php echo $ticket['status']; ?>">
                                            <?php 
                                                $status_text = [
                                                    'new' => 'جدید',
                                                    'in-progress' => 'در دست بررسی', 
                                                    'resolved' => 'حل شده'
                                                ];
                                                echo $status_text[$ticket['status']];
                                            ?>
                                        </div>
                                    </div>
                                    
                                    <div class="ticket-details">
                                        <div class="ticket-detail">
                                            <strong>کاربر:</strong> <?php echo htmlspecialchars($ticket['user_full_name']); ?>
                                        </div>
                                        <div class="ticket-detail">
                                            <strong>دپارتمان:</strong> <?php echo htmlspecialchars($ticket['user_department']); ?>
                                        </div>
                                        <div class="ticket-detail">
                                            <strong>دسته‌بندی:</strong> <?php echo htmlspecialchars($ticket['category']); ?>
                                        </div>
                                        <div class="ticket-detail">
                                            <strong>اولویت:</strong> 
                                            <span style="color: 
                                                <?php 
                                                    switch($ticket['priority']) {
                                                        case 'low': echo '#27ae60'; break;
                                                        case 'medium': echo '#f39c12'; break;
                                                        case 'high': echo '#e74c3c'; break;
                                                        case 'urgent': echo '#c0392b'; break;
                                                        default: echo '#333';
                                                    }
                                                ?>
                                            ">
                                                <?php 
                                                    $priority_text = [
                                                        'low' => 'کم',
                                                        'medium' => 'متوسط',
                                                        'high' => 'بالا', 
                                                        'urgent' => 'فوری'
                                                    ];
                                                    echo $priority_text[$ticket['priority']];
                                                ?>
                                            </span>
                                        </div>
                                    </div>
                                    
                                    <div class="ticket-description">
                                        <strong>شرح مشکل:</strong><br>
                                        <?php echo nl2br(htmlspecialchars($ticket['description'])); ?>
                                    </div>
                                    
                                    <?php if (!empty($ticket['admin_notes'])): ?>
                                        <div class="admin-notes">
                                            <strong>پاسخ ادمین:</strong><br>
                                            <?php echo nl2br(htmlspecialchars($ticket['admin_notes'])); ?>
                                        </div>
                                    <?php endif; ?>
                                    
                                    <div class="ticket-date">
                                        <strong>تاریخ ثبت:</strong> <?php echo date('Y/m/d H:i', strtotime($ticket['created_at'])); ?>
                                        <?php if ($ticket['updated_at'] != $ticket['created_at']): ?>
                                            | <strong>آخرین بروزرسانی:</strong> <?php echo date('Y/m/d H:i', strtotime($ticket['updated_at'])); ?>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <script>
        // فعال کردن جستجو با Enter
        document.getElementById('full_name').addEventListener('keypress', function(e) {
            if (e.key === 'Enter') {
                e.preventDefault();
                document.querySelector('button[name="search"]').click();
            }
        });
        
        // نمایش پیام قبل از چاپ
        window.onbeforeprint = function() {
            console.log('آماده‌سازی برای چاپ...');
        };
    </script>
</body>
</html>