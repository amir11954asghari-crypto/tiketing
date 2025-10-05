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

if (!$is_it_admin) {
    header('Location: dashboard.php');
    exit;
}

// پردازش جستجو
$search_results = [];
$search_performed = false;
$search_name = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['search'])) {
        $search_name = trim($_POST['full_name']);
        $search_performed = true;
        
        if (!empty($search_name)) {
            $search_results = $ticketFunctions->searchTicketsByUserName($search_name);
        }
    }
}
?>

<!DOCTYPE html>
<html lang="fa" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>گزارش‌گیری - پتروفرهنگ</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://cdn.jsdelivr.net/gh/rastikerdar/vazirmatn@v33.003/Vazirmatn-font-face.css" rel="stylesheet" type="text/css" />
    <link rel="icon" href="images/favicon.ico" type="image/x-icon">
    
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: Vazirmatn, 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }
        
        body {
            background: #f8f9fa;
            color: #333;
            min-height: 100vh;
            display: flex;
            flex-direction: column;
            background-image: linear-gradient(135deg, rgba(245,247,250,0.9) 0%, rgba(228,232,240,0.9) 100%), 
                            url('images/background.jpg');
            background-size: cover;
            background-position: center;
            background-attachment: fixed;
        }
        
        .container {
            max-width: 1400px;
            margin: 0 auto;
            padding: 20px;
            flex: 1;
            display: flex;
            flex-direction: column;
        }
        
        /* استایل هدر */
        header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 20px;
            margin-bottom: 30px;
            border-bottom: 2px solid rgba(255,255,255,0.5);
            background: rgba(255,255,255,0.95);
            border-radius: 20px;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1);
            backdrop-filter: blur(10px);
            border: 1px solid rgba(255,255,255,0.5);
        }
        
        .logo {
            display: flex;
            align-items: center;
            gap: 15px;
        }
        
        .logo-img {
            width: 70px;
            height: 70px;
            background: linear-gradient(45deg, #ffffff, #f0f0f0);
            border-radius: 50%;
            display: flex;
            justify-content: center;
            align-items: center;
            box-shadow: 0 8px 20px rgba(44, 62, 80, 0.2);
            border: 2px solid rgba(255,255,255,0.8);
            overflow: hidden;
            padding: 10px;
        }
        
        .logo-img img {
            width: 100%;
            height: 100%;
            object-fit: contain;
        }
        
        .logo-text {
            font-size: 22px;
            font-weight: bold;
            background: linear-gradient(45deg, #2c3e50, #34495e, #2c3e50);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
        }
        
        .user-info {
            display: flex;
            align-items: center;
            gap: 15px;
        }
        
        .user-welcome {
            background: linear-gradient(45deg, #6a11cb, #2575fc);
            color: white;
            padding: 10px 20px;
            border-radius: 25px;
            font-weight: bold;
            box-shadow: 0 4px 15px rgba(106,17,203,0.3);
        }
        
        .btn {
            padding: 10px 20px;
            border: none;
            border-radius: 25px;
            cursor: pointer;
            font-weight: 500;
            transition: all 0.3s ease;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 8px;
            font-size: 14px;
        }
        
        .btn-primary {
            background: linear-gradient(45deg, #6a11cb, #2575fc);
            color: white;
        }
        
        .btn-secondary {
            background: linear-gradient(45deg, #2ecc71, #27ae60);
            color: white;
        }
        
        .btn-danger {
            background: linear-gradient(45deg, #e74c3c, #c0392b);
            color: white;
        }
        
        .btn-outline {
            background: transparent;
            border: 2px solid #6a11cb;
            color: #6a11cb;
        }
        
        .btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 20px rgba(0,0,0,0.15);
        }
        
        /* استایل بخش اصلی */
        .main-content {
            background: rgba(255,255,255,0.95);
            border-radius: 20px;
            padding: 30px;
            margin-bottom: 30px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.1);
            backdrop-filter: blur(10px);
            border: 1px solid rgba(255,255,255,0.5);
            flex: 1;
        }
        
        .welcome-section {
            background: linear-gradient(135deg, #6a11cb 0%, #2575fc 100%);
            color: white;
            padding: 40px;
            border-radius: 15px;
            margin-bottom: 30px;
            text-align: center;
            box-shadow: 0 10px 30px rgba(106,17,203,0.3);
        }
        
        .report-container {
            background: rgba(255,255,255,0.95);
            padding: 30px;
            border-radius: 15px;
            margin-bottom: 20px;
            border: 1px solid rgba(255,255,255,0.5);
        }
        
        .search-form {
            display: grid;
            grid-template-columns: 1fr auto auto;
            gap: 15px;
            align-items: end;
            margin-bottom: 30px;
        }
        
        .form-group {
            margin-bottom: 20px;
        }
        
        label {
            display: block;
            margin-bottom: 8px;
            font-weight: 500;
            color: #555;
        }
        
        input {
            width: 100%;
            padding: 15px 20px;
            border: 2px solid #e9ecef;
            border-radius: 12px;
            font-size: 16px;
            transition: all 0.3s ease;
            background: rgba(255,255,255,0.9);
        }
        
        input:focus {
            outline: none;
            border-color: #6a11cb;
            box-shadow: 0 0 0 3px rgba(106,17,203,0.1);
            background: white;
        }
        
        .action-buttons {
            display: flex;
            gap: 10px;
            margin-top: 20px;
            flex-wrap: wrap;
        }
        
        .result-summary {
            background: #f8f9fa;
            padding: 20px;
            border-radius: 12px;
            margin-bottom: 20px;
            border-right: 4px solid #3498db;
        }
        
        .printable-area {
            background: white;
            padding: 20px;
            margin: 20px 0;
            border: 1px solid #ddd;
            border-radius: 12px;
        }
        
        .print-header {
            text-align: center;
            margin-bottom: 30px;
            padding-bottom: 15px;
            border-bottom: 2px solid #333;
        }
        
        .notification {
            padding: 15px;
            margin-bottom: 20px;
            border-radius: 12px;
            text-align: center;
            font-weight: 500;
        }
        
        .notification.success {
            background: #d4edda;
            color: #155724;
            border: 1px solid #c3e6cb;
        }
        
        .notification.error {
            background: #f8d7da;
            color: #721c24;
            border: 1px solid #f5c6cb;
        }
        
        .stat-badge {
            display: inline-block;
            padding: 5px 10px;
            border-radius: 6px;
            font-size: 12px;
            margin: 0 2px;
        }
        
        .badge-new { background: #d4edda; color: #155724; }
        .badge-in-progress { background: #fff3cd; color: #856404; }
        .badge-resolved { background: #d1ecf1; color: #0c5460; }
        
        /* استایل جستجوی کاربر */
        .user-search-container {
            position: relative;
        }
        
        .user-search-input {
            width: 100%;
            padding: 15px 20px;
            border: 2px solid #e9ecef;
            border-radius: 12px;
            font-size: 16px;
            background: rgba(255,255,255,0.9);
        }
        
        .user-search-results {
            position: absolute;
            top: 100%;
            left: 0;
            right: 0;
            background: white;
            border: 1px solid #ddd;
            border-radius: 8px;
            max-height: 200px;
            overflow-y: auto;
            z-index: 1000;
            box-shadow: 0 5px 15px rgba(0,0,0,0.1);
            display: none;
        }
        
        .user-result-item {
            padding: 12px 15px;
            cursor: pointer;
            border-bottom: 1px solid #f0f0f0;
            transition: background-color 0.2s;
        }
        
        .user-result-item:hover {
            background-color: #f8f9fa;
        }
        
        .user-result-item:last-child {
            border-bottom: none;
        }
        
        .selected-user {
            background: #e7f3ff;
            padding: 12px;
            border-radius: 8px;
            margin-top: 10px;
            border-right: 3px solid #2575fc;
        }
        
        .hidden {
            display: none;
        }
        
        /* استایل فوتر */
        footer {
            text-align: center;
            padding: 30px 20px;
            margin-top: 20px;
            border-top: 1px solid rgba(255,255,255,0.5);
            background: rgba(255,255,255,0.95);
            border-radius: 20px 20px 0 0;
            color: #495057;
            box-shadow: 0 -5px 20px rgba(0, 0, 0, 0.05);
            backdrop-filter: blur(10px);
            width: 100%;
        }
        
        .footer-content {
            max-width: 500px;
            margin: 0 auto;
        }
        
        .footer-text {
            font-size: 14px;
            margin-bottom: 15px;
            color: #666;
        }
        
        .flags {
            display: flex;
            justify-content: center;
            align-items: center;
            gap: 15px;
            margin-top: 15px;
        }
        
        .flag {
            width: 40px;
            height: 25px;
            border-radius: 6px;
            overflow: hidden;
            box-shadow: 0 3px 10px rgba(0,0,0,0.15);
            position: relative;
        }
        
        .iran-flag {
            background: linear-gradient(to bottom, 
                #239F40 33%, 
                #FFFFFF 33%, 
                #FFFFFF 66%, 
                #DA0000 66%);
            position: relative;
            border: 1px solid #ddd;
        }
        
        .iran-flag:before {
            content: '☫';
            position: absolute;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            color: #FF0000;
            font-size: 12px;
            font-weight: bold;
        }
        
        @media print {
            .no-print {
                display: none !important;
            }
            body {
                background: white !important;
                font-size: 12px !important;
            }
            .printable-area {
                border: none !important;
                box-shadow: none !important;
            }
        }
        
        @media (max-width: 768px) {
            .search-form {
                grid-template-columns: 1fr;
            }
            
            .action-buttons {
                flex-direction: column;
            }
            
            header {
                flex-direction: column;
                gap: 15px;
                text-align: center;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <header>
            <div class="logo">
                <div class="logo-img">
                    <img src="images/Petrofarhang.png" alt="لوگوی پتروفرهنگ">
                </div>
                <div class="logo-text">سامانه تیکتینگ - گزارش‌گیری</div>
            </div>
            <div class="user-info">
                <div class="user-welcome">
                    <i class="fas fa-user"></i>
                    <?php echo htmlspecialchars($user['full_name']); ?>
                </div>
                <a href="dashboard.php" class="btn btn-outline">
                    <i class="fas fa-arrow-right"></i> بازگشت به پنل
                </a>
                <a href="logout.php" class="btn btn-danger">
                    <i class="fas fa-sign-out-alt"></i> خروج
                </a>
            </div>
        </header>

        <div class="main-content">
            <div class="welcome-section">
                <h1><i class="fas fa-chart-bar"></i> سیستم گزارش‌گیری تیکت‌ها</h1>
                <p>در این بخش می‌توانید گزارش کامل تیکت‌های کاربران را مشاهده و دریافت کنید</p>
            </div>

            <div class="report-container">
                <h2><i class="fas fa-search"></i> جستجو و تولید گزارش</h2>
                
                <form method="POST" action="" class="search-form" id="searchForm">
                    <div class="form-group">
                        <label for="user_search"><i class="fas fa-user"></i> جستجوی کاربر</label>
                        <div class="user-search-container">
                            <input type="text" id="user_search" name="user_search" 
                                   placeholder="حداقل 3 حرف از فامیل کاربر را وارد کنید..."
                                   class="user-search-input" autocomplete="off">
                            <div id="user_search_results" class="user-search-results"></div>
                        </div>
                        <div id="selected_user_display" class="selected-user hidden">
                            <strong>کاربر انتخاب شده:</strong>
                            <span id="selected_user_name"></span>
                            <input type="hidden" id="full_name" name="full_name">
                        </div>
                    </div>
                    
                    <button type="submit" name="search" class="btn btn-primary">
                        <i class="fas fa-search"></i> جستجوی تیکت‌ها
                    </button>
                    <button type="button" onclick="generatePDF()" class="btn btn-danger">
                        <i class="fas fa-file-pdf"></i> دانلود PDF
                    </button>
                </form>

                <?php if ($search_performed): ?>
                    <?php if (empty($search_name)): ?>
                        <div class="notification error">
                            <i class="fas fa-exclamation-circle"></i> لطفاً ابتدا کاربر را انتخاب کنید
                        </div>
                    <?php elseif (empty($search_results)): ?>
                        <div class="notification error">
                            <i class="fas fa-info-circle"></i> هیچ تیکتی برای کاربر "<?php echo htmlspecialchars($search_name); ?>" یافت نشد
                        </div>
                    <?php else: ?>
                        <div class="result-summary">
                            <h3><i class="fas fa-list"></i> نتایج جستجو برای: "<?php echo htmlspecialchars($search_name); ?>"</h3>
                            <p>تعداد تیکت‌ها: <strong><?php echo count($search_results); ?></strong></p>
                            <p>
                                جدید: <span class="stat-badge badge-new"><?php echo count(array_filter($search_results, function($t) { return $t['status'] === 'new'; })); ?></span> |
                                در دست بررسی: <span class="stat-badge badge-in-progress"><?php echo count(array_filter($search_results, function($t) { return $t['status'] === 'in-progress'; })); ?></span> |
                                حل شده: <span class="stat-badge badge-resolved"><?php echo count(array_filter($search_results, function($t) { return $t['status'] === 'resolved'; })); ?></span>
                            </p>
                        </div>

                        <div class="action-buttons no-print">
                            <button onclick="window.print()" class="btn btn-primary">
                                <i class="fas fa-print"></i> چاپ گزارش
                            </button>
                            <button onclick="generatePDF()" class="btn btn-danger">
                                <i class="fas fa-file-pdf"></i> دانلود PDF
                            </button>
                            <button onclick="exportToExcel()" class="btn btn-success">
                                <i class="fas fa-file-excel"></i> خروجی Excel
                            </button>
                            <button onclick="saveAsHTML()" class="btn btn-info">
                                <i class="fas fa-code"></i> ذخیره HTML
                            </button>
                        </div>

                        <!-- ناحیه قابل چاپ -->
                        <div class="printable-area" id="printableArea">
                            <div class="print-header">
                                <h2>گزارش تیکت‌های کاربر: <?php echo htmlspecialchars($search_name); ?></h2>
                                <p>تاریخ تولید: <?php echo date('Y/m/d H:i'); ?></p>
                                <p>تعداد تیکت‌ها: <?php echo count($search_results); ?></p>
                            </div>

                            <div class="ticket-list">
                                <?php foreach ($search_results as $ticket): ?>
                                    <div class="ticket-item" style="page-break-inside: avoid; background: #f8f9fa; padding: 20px; margin: 15px 0; border-radius: 8px; border-right: 4px solid #6a11cb;">
                                        <div class="ticket-header" style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 15px;">
                                            <div class="ticket-title" style="font-weight: bold; font-size: 18px; color: #2c3e50;">
                                                <?php echo htmlspecialchars($ticket['title']); ?>
                                            </div>
                                            <div class="ticket-status status-<?php echo $ticket['status']; ?>" style="padding: 8px 20px; border-radius: 20px; font-size: 14px; font-weight: 500;">
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
                                        
                                        <div class="ticket-details" style="color: #666; margin-bottom: 15px; display: flex; gap: 20px; flex-wrap: wrap;">
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
                                                    ?>;">
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
                                        
                                        <div class="ticket-description" style="margin-bottom: 15px;">
                                            <strong>شرح مشکل:</strong><br>
                                            <?php echo nl2br(htmlspecialchars($ticket['description'])); ?>
                                        </div>
                                        
                                        <?php if (!empty($ticket['admin_notes'])): ?>
                                            <div class="admin-notes" style="background: #e7f3ff; padding: 15px; border-radius: 5px; margin-top: 15px; border-right: 3px solid #2575fc;">
                                                <strong>پاسخ ادمین:</strong><br>
                                                <?php echo nl2br(htmlspecialchars($ticket['admin_notes'])); ?>
                                            </div>
                                        <?php endif; ?>
                                        
                                        <div class="ticket-date" style="font-size: 14px; color: #999; margin-top: 15px; text-align: left;">
                                            <strong>تاریخ ثبت:</strong> <?php echo date('Y/m/d H:i', strtotime($ticket['created_at'])); ?>
                                            <?php if ($ticket['updated_at'] != $ticket['created_at']): ?>
                                                | <strong>آخرین بروزرسانی:</strong> <?php echo date('Y/m/d H:i', strtotime($ticket['updated_at'])); ?>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        </div>
                    <?php endif; ?>
                <?php endif; ?>
            </div>
        </div>

        <footer>
            <div class="footer-content">
                <div class="footer-text">
                    © تهیه شده توسط مدیریت فناوری اطلاعات و ارتباطات هلدینگ پتروفرهنگ
                </div>
                <div class="flags">
                    <div class="flag iran-flag" title="پرچم جمهوری اسلامی ایران"></div>
                </div>
            </div>
        </footer>
    </div>

    <script>
        // جستجوی کاربر با AJAX
        let searchTimeout;
        document.getElementById('user_search').addEventListener('input', function() {
            const searchTerm = this.value.trim();
            const resultsContainer = document.getElementById('user_search_results');
            
            // پاک کردن تایموت قبلی
            clearTimeout(searchTimeout);
            
            // اگر کمتر از 3 حرف باشد، نتایج را پنهان می‌کنیم
            if (searchTerm.length < 3) {
                resultsContainer.style.display = 'none';
                return;
            }
            
            // تایموت برای جلوگیری از درخواست‌های زیاد
            searchTimeout = setTimeout(() => {
                searchUsers(searchTerm);
            }, 300);
        });

        // تابع جستجوی کاربران
        function searchUsers(searchTerm) {
            const resultsContainer = document.getElementById('user_search_results');
            
            fetch('search_users.php?q=' + encodeURIComponent(searchTerm))
                .then(response => response.json())
                .then(users => {
                    resultsContainer.innerHTML = '';
                    
                    if (users.length === 0) {
                        resultsContainer.innerHTML = '<div class="user-result-item">کاربری یافت نشد</div>';
                    } else {
                        users.forEach(user => {
                            const userItem = document.createElement('div');
                            userItem.className = 'user-result-item';
                            userItem.innerHTML = `
                                <strong>${user.full_name}</strong><br>
                                <small>${user.department} - ${user.position || 'کاربر'}</small>
                            `;
                            userItem.addEventListener('click', () => {
                                selectUser(user.full_name, user.department);
                            });
                            resultsContainer.appendChild(userItem);
                        });
                    }
                    
                    resultsContainer.style.display = 'block';
                })
                .catch(error => {
                    console.error('خطا در جستجو:', error);
                    resultsContainer.innerHTML = '<div class="user-result-item">خطا در جستجو</div>';
                    resultsContainer.style.display = 'block';
                });
        }

        // انتخاب کاربر
        function selectUser(userName, userDepartment) {
            document.getElementById('full_name').value = userName;
            document.getElementById('selected_user_name').textContent = `${userName} - ${userDepartment}`;
            document.getElementById('selected_user_display').classList.remove('hidden');
            document.getElementById('user_search').value = '';
            document.getElementById('user_search_results').style.display = 'none';
        }

        // پنهان کردن نتایج جستجو وقتی کلیک خارج شود
        document.addEventListener('click', function(e) {
            if (!e.target.closest('.user-search-container')) {
                document.getElementById('user_search_results').style.display = 'none';
            }
        });

        // فعال کردن جستجو با Enter
        document.getElementById('user_search').addEventListener('keypress', function(e) {
            if (e.key === 'Enter') {
                e.preventDefault();
            }
        });

        // توابع برای خروجی‌های مختلف
        function generatePDF() {
            const searchName = document.getElementById('full_name').value;
            if (searchName) {
                window.open('generate_pdf.php?name=' + encodeURIComponent(searchName), '_blank');
            } else {
                alert('لطفاً ابتدا کاربر را انتخاب کرده و جستجو کنید');
            }
        }

        function exportToExcel() {
            const searchName = document.getElementById('full_name').value;
            if (searchName) {
                window.open('export_excel.php?name=' + encodeURIComponent(searchName), '_blank');
            } else {
                alert('لطفاً ابتدا کاربر را انتخاب کرده و جستجو کنید');
            }
        }

        function saveAsHTML() {
            const printableArea = document.getElementById('printableArea');
            if (printableArea) {
                const htmlContent = printableArea.outerHTML;
                const blob = new Blob([htmlContent], { type: 'text/html' });
                const url = URL.createObjectURL(blob);
                const a = document.createElement('a');
                a.href = url;
                a.download = 'report_' + document.getElementById('full_name').value + '_' + new Date().toISOString().split('T')[0] + '.html';
                document.body.appendChild(a);
                a.click();
                document.body.removeChild(a);
                URL.revokeObjectURL(url);
            } else {
                alert('لطفاً ابتدا جستجو کنید');
            }
        }

        // نمایش پیام قبل از چاپ
        window.onbeforeprint = function() {
            console.log('آماده‌سازی برای چاپ...');
        };
    </script>
</body>
</html>