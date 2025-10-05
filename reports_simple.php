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

$ticketFunctions = new TicketFunctions();
$search_results = [];
$search_performed = false;
$search_name = '';

// پردازش جستجو
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['search'])) {
    $search_name = trim($_POST['search_name']);
    $search_performed = true;
    
    if (!empty($search_name)) {
        $search_results = $ticketFunctions->searchTicketsByUserName($search_name);
    }
}
?>

<!DOCTYPE html>
<html lang="fa" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>گزارش‌گیری تیکت‌ها - پتروفرهنگ</title>
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
            padding: 20px 30px;
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
            flex-wrap: wrap;
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
            padding: 30px;
            border-radius: 15px;
            margin-bottom: 30px;
            text-align: center;
            box-shadow: 0 10px 30px rgba(106,17,203,0.3);
        }
        
        .search-form {
            display: grid;
            grid-template-columns: 2fr auto;
            gap: 15px;
            align-items: end;
            margin-bottom: 30px;
            background: rgba(255,255,255,0.9);
            padding: 25px;
            border-radius: 15px;
            border: 1px solid rgba(255,255,255,0.5);
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
        
        input, select {
            width: 100%;
            padding: 12px 15px;
            border: 2px solid #e9ecef;
            border-radius: 8px;
            font-size: 16px;
            transition: all 0.3s ease;
            background: rgba(255,255,255,0.9);
        }
        
        input:focus, select:focus {
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
        
        .ticket-list {
            display: grid;
            gap: 20px;
            margin-top: 20px;
        }
        
        .ticket-item {
            background: linear-gradient(135deg, rgba(255,255,255,0.95), rgba(245,247,250,0.95));
            padding: 20px;
            border-radius: 12px;
            box-shadow: 0 5px 15px rgba(0,0,0,0.08);
            border: 1px solid rgba(255,255,255,0.5);
            border-right: 4px solid #6a11cb;
            transition: all 0.3s ease;
        }
        
        .ticket-item:hover {
            transform: translateY(-3px);
            box-shadow: 0 10px 25px rgba(106,17,203,0.15);
        }
        
        .ticket-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 15px;
            flex-wrap: wrap;
            gap: 10px;
        }
        
        .ticket-title {
            font-weight: bold;
            font-size: 18px;
            color: #2c3e50;
        }
        
        .ticket-status {
            padding: 10px 20px;
            border-radius: 25px;
            font-size: 14px;
            font-weight: 600;
        }
        
        .status-new {
            background: #d4edda;
            color: #155724;
            border: 1px solid #c3e6cb;
        }
        
        .status-in-progress {
            background: #fff3cd;
            color: #856404;
            border: 1px solid #ffeaa7;
        }
        
        .status-resolved {
            background: #d1ecf1;
            color: #0c5460;
            border: 1px solid #bee5eb;
        }
        
        .ticket-details {
            color: #666;
            margin-bottom: 15px;
            display: flex;
            gap: 20px;
            flex-wrap: wrap;
        }
        
        .ticket-detail {
            display: flex;
            align-items: center;
            gap: 8px;
        }
        
        .admin-notes {
            background: #f8f9fa;
            border-right: 3px solid #6a11cb;
            padding: 20px;
            margin-top: 15px;
            border-radius: 12px;
        }
        
        .ticket-date {
            font-size: 14px;
            color: #999;
            margin-top: 15px;
            text-align: left;
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
            
            .ticket-header {
                flex-direction: column;
                align-items: flex-start;
                gap: 10px;
            }
            
            .ticket-details {
                flex-direction: column;
                gap: 10px;
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
                <div class="logo-text">سیستم گزارش‌گیری تیکت‌ها</div>
            </div>
            <div class="user-info">
                <div class="user-welcome">
                    <i class="fas fa-user"></i>
                    <?php echo htmlspecialchars($user['full_name']); ?>
                </div>
                <a href="dashboard.php" class="btn btn-outline">
                    <i class="fas fa-arrow-right"></i> بازگشت به داشبورد
                </a>
                <a href="logout.php" class="btn btn-danger">
                    <i class="fas fa-sign-out-alt"></i> خروج
                </a>
            </div>
        </header>

        <div class="main-content">
            <div class="welcome-section">
                <h1><i class="fas fa-chart-bar"></i> سیستم گزارش‌گیری تیکت‌ها</h1>
                <p>جستجو، مشاهده و دریافت گزارش کامل تیکت‌های کاربران</p>
            </div>

            <form method="POST" action="" class="search-form">
                <div class="form-group">
                    <label for="search_name"><i class="fas fa-user"></i> نام و نام خانوادگی کاربر</label>
                    <input type="text" id="search_name" name="search_name" 
                           value="<?php echo htmlspecialchars($search_name); ?>" 
                           placeholder="حداقل 3 حرف از نام کاربر را وارد کنید" 
                           required>
                </div>
                
                <button type="submit" name="search" class="btn btn-primary">
                    <i class="fas fa-search"></i> جستجو
                </button>
            </form>

            <?php if ($search_performed): ?>
                <?php if (empty($search_name)): ?>
                    <div class="notification error">
                        <i class="fas fa-exclamation-circle"></i> لطفاً نام کاربر را وارد کنید
                    </div>
                <?php elseif (empty($search_results)): ?>
                    <div class="notification error">
                        <i class="fas fa-info-circle"></i> 
                        هیچ تیکتی برای کاربر "<?php echo htmlspecialchars($search_name); ?>" یافت نشد
                    </div>
                <?php else: ?>
                    <div class="result-summary">
                        <h3><i class="fas fa-list"></i> نتایج جستجو</h3>
                        <p>تعداد تیکت‌های یافت شده: <strong><?php echo count($search_results); ?></strong></p>
                        <p>
                            <?php 
                            $users = array_unique(array_column($search_results, 'user_full_name'));
                            if (count($users) === 1) {
                                echo 'کاربر: <strong>' . htmlspecialchars($users[0]) . '</strong>';
                            } else {
                                echo 'تعداد کاربران: <strong>' . count($users) . '</strong>';
                            }
                            ?>
                        </p>
                    </div>

                    <div class="action-buttons">
                        <button onclick="window.print()" class="btn btn-primary">
                            <i class="fas fa-print"></i> چاپ گزارش
                        </button>
                        
                        <!-- اصلاح شده: انتقال مستقیم پارامتر name -->
                        <a href="generate_pdf.php?name=<?php echo urlencode($search_name); ?>" class="btn btn-danger" target="_blank">
                            <i class="fas fa-file-pdf"></i> خروجی PDF
                        </a>
                        
                        <a href="export_excel.php?name=<?php echo urlencode($search_name); ?>" class="btn btn-success" target="_blank">
                            <i class="fas fa-file-excel"></i> خروجی Excel
                        </a>
                    </div>

                    <div class="ticket-list">
                        <?php foreach ($search_results as $ticket): ?>
                            <div class="ticket-item">
                                <div class="ticket-header">
                                    <div class="ticket-title"><?php echo htmlspecialchars($ticket['title']); ?></div>
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
                                        <i class="fas fa-user"></i>
                                        <strong>کاربر:</strong> <?php echo htmlspecialchars($ticket['user_full_name']); ?>
                                    </div>
                                    <div class="ticket-detail">
                                        <i class="fas fa-building"></i>
                                        <strong>دپارتمان:</strong> <?php echo htmlspecialchars($ticket['user_department']); ?>
                                    </div>
                                    <div class="ticket-detail">
                                        <i class="fas fa-folder"></i>
                                        <strong>دسته‌بندی:</strong> <?php echo htmlspecialchars($ticket['category']); ?>
                                    </div>
                                    <div class="ticket-detail">
                                        <i class="fas fa-exclamation-circle"></i>
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
                                    <strong><i class="fas fa-file-alt"></i> شرح مشکل:</strong><br>
                                    <?php echo nl2br(htmlspecialchars($ticket['description'])); ?>
                                </div>
                                
                                <?php if (!empty($ticket['admin_notes'])): ?>
                                    <div class="admin-notes">
                                        <strong><i class="fas fa-comment-dots"></i> پاسخ ادمین:</strong><br>
                                        <?php echo nl2br(htmlspecialchars($ticket['admin_notes'])); ?>
                                    </div>
                                <?php endif; ?>
                                
                                <div class="ticket-date">
                                    <i class="fas fa-calendar"></i>
                                    <strong>تاریخ ثبت:</strong> <?php echo date('Y/m/d H:i', strtotime($ticket['created_at'])); ?>
                                    <?php if ($ticket['updated_at'] != $ticket['created_at']): ?>
                                        | <strong>آخرین بروزرسانی:</strong> <?php echo date('Y/m/d H:i', strtotime($ticket['updated_at'])); ?>
                                    <?php endif; ?>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            <?php endif; ?>
        </div>
    </div>

    <script>
        // توابع برای خروجی‌های مختلف
        function generatePDF() {
            const searchName = document.getElementById('search_name').value;
            if (searchName) {
                window.open('generate_pdf.php?name=' + encodeURIComponent(searchName), '_blank');
            } else {
                alert('لطفاً ابتدا جستجو کنید و نام کاربر را وارد نمایید');
            }
        }

        function exportToExcel() {
            const searchName = document.getElementById('search_name').value;
            if (searchName) {
                window.open('export_excel.php?name=' + encodeURIComponent(searchName), '_blank');
            } else {
                alert('لطفاً ابتدا جستجو کنید و نام کاربر را وارد نمایید');
            }
        }

        // فوکوس خودکار روی فیلد جستجو
        document.addEventListener('DOMContentLoaded', function() {
            document.getElementById('search_name').focus();
        });
    </script>
</body>
</html>
