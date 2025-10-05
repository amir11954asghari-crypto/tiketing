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

// پردازش ثبت تیکت جدید (فقط برای کاربران عادی)
if (!$is_it_admin && $_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['create_ticket'])) {
    $title = $_POST['title'];
    $category = $_POST['category'];
    $priority = $_POST['priority'];
    $description = $_POST['description'];
    
    $result = $ticketFunctions->createTicket($title, $category, $priority, $description, $user['id']);
    
    if ($result['success']) {
        $success_message = 'تیکت با موفقیت ثبت شد!';
    } else {
        $error_message = $result['message'];
    }
}

// دریافت تیکت‌ها بر اساس نوع کاربر و تب فعال
$active_tab = isset($_GET['tab']) ? $_GET['tab'] : ($is_it_admin ? 'new-tickets' : 'new-ticket');

if ($is_it_admin) {
    switch ($active_tab) {
        case 'new-tickets':
            $tickets = $ticketFunctions->getTicketsByStatus('new');
            break;
        case 'in-progress-tickets':
            $tickets = $ticketFunctions->getTicketsByStatus('in-progress');
            break;
        case 'resolved-tickets':
            $tickets = $ticketFunctions->getTicketsByStatus('resolved');
            break;
        case 'all-tickets':
            $tickets = $ticketFunctions->getAllTickets();
            break;
        default:
            $tickets = $ticketFunctions->getNewTickets();
            break;
    }
    $all_tickets = $ticketFunctions->getAllTickets();
} else {
    $user_tickets = $ticketFunctions->getUserTickets($user['id']);
    $tickets = $user_tickets;
}

// پردازش بروزرسانی وضعیت تیکت توسط ادمین
if ($is_it_admin && $_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_ticket_status'])) {
    $ticket_id = $_POST['ticket_id'];
    $new_status = $_POST['status'];
    $admin_notes = $_POST['admin_notes'] ?? '';
    
    $result = $ticketFunctions->updateTicketStatus($ticket_id, $new_status, $admin_notes, $user['id']);
    
    if ($result['success']) {
        $success_message = 'وضعیت تیکت با موفقیت بروزرسانی شد!';
        header('Location: dashboard.php?tab=' . $active_tab);
        exit;
    } else {
        $error_message = $result['message'];
    }
}
?>

<!DOCTYPE html>
<html lang="fa" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>سامانه خدمات پشتیبانی فناوری اطلاعات و ارتباطات</title>
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
            min-height: 100vh;
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
        
        .logo-subtext {
            font-size: 14px;
            color: #666;
            margin-top: -5px;
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
            padding: 40px;
            border-radius: 15px;
            margin-bottom: 30px;
            text-align: center;
            box-shadow: 0 10px 30px rgba(106,17,203,0.3);
            position: relative;
            overflow: hidden;
        }
        
        .welcome-section::before {
            content: '';
            position: absolute;
            top: 0;
            right: 0;
            width: 100%;
            height: 100%;
            background: linear-gradient(45deg, transparent, rgba(255,255,255,0.1), transparent);
            transform: translateX(-100%);
            animation: shimmer 3s infinite;
        }
        
        @keyframes shimmer {
            100% {
                transform: translateX(100%);
            }
        }
        
        .stats {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 20px;
            margin: 30px 0;
        }
        
        .stat-card {
            background: linear-gradient(135deg, rgba(255,255,255,0.95), rgba(245,247,250,0.95));
            padding: 25px;
            border-radius: 15px;
            text-align: center;
            box-shadow: 0 5px 20px rgba(0,0,0,0.08);
            border: 1px solid rgba(255,255,255,0.5);
            transition: all 0.3s ease;
            position: relative;
            overflow: hidden;
        }
        
        .stat-card::before {
            content: '';
            position: absolute;
            top: 0;
            right: 0;
            width: 100%;
            height: 4px;
            background: linear-gradient(45deg, #6a11cb, #2575fc);
        }
        
        .stat-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 15px 30px rgba(106,17,203,0.15);
        }
        
        .stat-number {
            font-size: 36px;
            font-weight: bold;
            margin-bottom: 10px;
        }
        
        .stat-card:nth-child(1) .stat-number { color: #3498db; }
        .stat-card:nth-child(2) .stat-number { color: #e74c3c; }
        .stat-card:nth-child(3) .stat-number { color: #f39c12; }
        .stat-card:nth-child(4) .stat-number { color: #2ecc71; }
        
        /* استایل تب‌ها */
        .tabs {
            display: flex;
            background: rgba(255,255,255,0.9);
            border-radius: 15px;
            overflow: hidden;
            margin: 25px 0;
            box-shadow: 0 5px 15px rgba(0,0,0,0.08);
            border: 1px solid rgba(255,255,255,0.5);
            flex-wrap: wrap;
        }
        
        .tab {
            padding: 18px 25px;
            cursor: pointer;
            flex: 1;
            text-align: center;
            transition: all 0.3s ease;
            border-bottom: 3px solid transparent;
            min-width: 140px;
            background: rgba(255,255,255,0.7);
        }
        
        .tab.active {
            background: linear-gradient(45deg, #6a11cb, #2575fc);
            color: white;
            border-bottom-color: #2ecc71;
        }
        
        .tab:hover:not(.active) {
            background: rgba(106,17,203,0.1);
        }
        
        .tab-content {
            display: none;
            background: rgba(255,255,255,0.9);
            padding: 30px;
            border-radius: 15px;
            box-shadow: 0 5px 15px rgba(0,0,0,0.08);
            margin-bottom: 20px;
            border: 1px solid rgba(255,255,255,0.5);
            animation: fadeIn 0.5s ease;
        }
        
        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(10px); }
            to { opacity: 1; transform: translateY(0); }
        }
        
        .tab-content.active {
            display: block;
        }
        
        /* استایل فرم‌ها */
        .form-group {
            margin-bottom: 25px;
        }
        
        label {
            display: block;
            margin-bottom: 10px;
            font-weight: 600;
            color: #2c3e50;
        }
        
        input, select, textarea {
            width: 100%;
            padding: 15px 20px;
            border: 2px solid #e9ecef;
            border-radius: 12px;
            font-size: 16px;
            transition: all 0.3s ease;
            background: rgba(255,255,255,0.9);
        }
        
        input:focus, select:focus, textarea:focus {
            outline: none;
            border-color: #6a11cb;
            box-shadow: 0 0 0 3px rgba(106,17,203,0.1);
            background: white;
        }
        
        textarea {
            min-height: 140px;
            resize: vertical;
        }
        
        /* استایل لیست تیکت‌ها */
        .ticket-list {
            display: grid;
            gap: 20px;
        }
        
        .ticket-item {
            background: linear-gradient(135deg, rgba(255,255,255,0.95), rgba(245,247,250,0.95));
            border-right: 4px solid #6a11cb;
            padding: 25px;
            border-radius: 15px;
            box-shadow: 0 5px 20px rgba(0,0,0,0.08);
            transition: all 0.3s ease;
            border: 1px solid rgba(255,255,255,0.5);
            position: relative;
        }
        
        .ticket-item::before {
            content: '';
            position: absolute;
            top: 0;
            right: 0;
            width: 4px;
            height: 100%;
            background: linear-gradient(45deg, #6a11cb, #2575fc);
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
        
        .ticket-date {
            font-size: 14px;
            color: #999;
            margin-top: 15px;
            text-align: left;
        }
        
        .notification {
            padding: 20px;
            margin-bottom: 25px;
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
        
        /* استایل مخصوص ادمین */

        
        .ticket-actions {
            display: flex;
            gap: 10px;
            margin-top: 15px;
            flex-wrap: wrap;
        }
        
        .admin-notes {
            background: #f8f9fa;
            border-right: 3px solid #6a11cb;
            padding: 20px;
            margin-top: 15px;
            border-radius: 12px;
        }
        
        .user-info-badge {
            background: linear-gradient(45deg, #3498db, #2980b9);
            color: white;
            padding: 5px 12px;
            border-radius: 15px;
            font-size: 12px;
            margin-left: 8px;
        }
        
        /* استایل فوتر */
        footer {
            text-align: center;
            padding: 30px 20px;
            margin-top: auto;
            border-top: 1px solid rgba(255,255,255,0.5);
            background: rgba(255,255,255,0.95);
            border-radius: 20px 20px 0 0;
            color: #495057;
            box-shadow: 0 -5px 20px rgba(0, 0, 0, 0.05);
            backdrop-filter: blur(10px);
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
        
        /* رسپانسیو */
        @media (max-width: 768px) {
            header {
                flex-direction: column;
                gap: 15px;
                text-align: center;
            }
            
            .tabs {
                flex-direction: column;
            }
            
            .tab {
                flex: none;
                padding: 15px;
            }
            
            .ticket-header {
                flex-direction: column;
                align-items: flex-start;
                gap: 10px;
            }
            
            .stats {
                grid-template-columns: 1fr 1fr;
            }
            
            .ticket-details {
                flex-direction: column;
                gap: 10px;
            }
            
            .user-info {
                justify-content: center;
            }
        }
        
        @media (max-width: 480px) {
            .stats {
                grid-template-columns: 1fr;
            }
            
            .container {
                padding: 15px;
            }
            
            .main-content {
                padding: 20px;
            }
            
            .logo-img {
                width: 60px;
                height: 60px;
            }
            
            .logo-text {
                font-size: 18px;
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
                <div>
                    <div class="logo-text">
                        سامانه خدمات پشتیبانی فناوری اطلاعات و ارتباطات
                      
                    </div>
                    <div class="logo-subtext">هلدینگ پتروفرهنگ</div>
                </div>
            </div>
            <div class="user-info">
                <div class="user-welcome">
                    <i class="fas fa-user"></i>
                    <?php echo htmlspecialchars($user['full_name']); ?>
                    (<?php echo $is_it_admin ? 'ادمین فناوری اطلاعات' : ($user['user_type'] === 'admin' ? 'ادمین' : 'کاربر'); ?>)
                </div>
                <a href="http://192.168.30.77/index.html" class="btn btn-outline">
                    <i class="fas fa-home"></i> بازگشت به پورتال
                </a>
                <?php if ($is_it_admin): ?>
                    <a href="equipment_management.php" class="btn btn-secondary">
                        <i class="fas fa-laptop"></i> مدیریت تجهیزات
                    </a>
                    <a href="reports_simple.php" class="btn btn-secondary">
                        <i class="fas fa-chart-bar"></i> گزارش‌گیری
                    </a>
                <?php endif; ?>
                <a href="logout.php" class="btn btn-danger">
                    <i class="fas fa-sign-out-alt"></i> خروج
                </a>
            </div>
        </header>

        <div class="main-content">
            <div class="welcome-section">
                <h1>سامانه خدمات پشتیبانی فناوری اطلاعات و ارتباطات</h1>
                <p>
                    <?php if ($is_it_admin): ?>
                        به پنل سامانه مدیریت خدمات پشتیبانی فناوری اطلاعات و ارتباطات خوش آمدید. در این بخش می‌توانید تیکت‌های کاربران را مدیریت و پیگیری نمایید.
                    <?php else: ?>
                        به سامانه خدمات پشتیبانی فناوری اطلاعات و ارتباطات خوش آمدید. شما می‌توانید مشکلات فنی خود را از طریق این سامانه گزارش دهید.
                    <?php endif; ?>
                </p>
            </div>

            <?php if (isset($success_message)): ?>
                <div class="notification success">
                    <i class="fas fa-check-circle"></i> <?php echo $success_message; ?>
                </div>
            <?php endif; ?>
            
            <?php if (isset($error_message)): ?>
                <div class="notification error">
                    <i class="fas fa-exclamation-circle"></i> <?php echo $error_message; ?>
                </div>
            <?php endif; ?>

            <div class="stats">
                <?php if ($is_it_admin): ?>
                    <div class="stat-card">
                        <div class="stat-number"><?php echo count($all_tickets ?? []); ?></div>
                        <div>کل تیکت‌ها</div>
                    </div>
                    <div class="stat-card">
                        <div class="stat-number"><?php echo count(array_filter($all_tickets ?? [], function($t) { return $t['status'] === 'new'; })); ?></div>
                        <div>تیکت‌های جدید</div>
                    </div>
                    <div class="stat-card">
                        <div class="stat-number"><?php echo count(array_filter($all_tickets ?? [], function($t) { return $t['status'] === 'in-progress'; })); ?></div>
                        <div>در دست بررسی</div>
                    </div>
                    <div class="stat-card">
                        <div class="stat-number"><?php echo count(array_filter($all_tickets ?? [], function($t) { return $t['status'] === 'resolved'; })); ?></div>
                        <div>حل شده</div>
                    </div>
                <?php else: ?>
                    <div class="stat-card">
                        <div class="stat-number"><?php echo count($user_tickets ?? []); ?></div>
                        <div>کل تیکت‌های من</div>
                    </div>
                    <div class="stat-card">
                        <div class="stat-number"><?php echo count(array_filter($user_tickets ?? [], function($t) { return $t['status'] === 'new'; })); ?></div>
                        <div>تیکت‌های جدید</div>
                    </div>
                    <div class="stat-card">
                        <div class="stat-number"><?php echo count(array_filter($user_tickets ?? [], function($t) { return $t['status'] === 'in-progress'; })); ?></div>
                        <div>در دست بررسی</div>
                    </div>
                    <div class="stat-card">
                        <div class="stat-number"><?php echo count(array_filter($user_tickets ?? [], function($t) { return $t['status'] === 'resolved'; })); ?></div>
                        <div>حل شده</div>
                    </div>
                <?php endif; ?>
            </div>

            <!-- تب‌های مختلف -->
            <div class="tabs">
                <?php if ($is_it_admin): ?>
                    <div class="tab <?php echo $active_tab === 'new-tickets' ? 'active' : ''; ?>" data-tab="new-tickets">
                        <i class="fas fa-plus-circle"></i> تیکت‌های جدید
                    </div>
                    <div class="tab <?php echo $active_tab === 'in-progress-tickets' ? 'active' : ''; ?>" data-tab="in-progress-tickets">
                        <i class="fas fa-spinner"></i> در دست بررسی
                    </div>
                    <div class="tab <?php echo $active_tab === 'resolved-tickets' ? 'active' : ''; ?>" data-tab="resolved-tickets">
                        <i class="fas fa-check-circle"></i> حل شده
                    </div>
                    <div class="tab <?php echo $active_tab === 'all-tickets' ? 'active' : ''; ?>" data-tab="all-tickets">
                        <i class="fas fa-list"></i> همه تیکت‌ها
                    </div>
                <?php else: ?>
                    <div class="tab <?php echo $active_tab === 'new-ticket' ? 'active' : ''; ?>" data-tab="new-ticket">
                        <i class="fas fa-plus-circle"></i> ثبت تیکت جدید
                    </div>
                    <div class="tab <?php echo $active_tab === 'my-tickets' ? 'active' : ''; ?>" data-tab="my-tickets">
                        <i class="fas fa-ticket-alt"></i> تیکت‌های من
                    </div>
                <?php endif; ?>
                <div class="tab <?php echo $active_tab === 'profile' ? 'active' : ''; ?>" data-tab="profile">
                    <i class="fas fa-user"></i> پروفایل کاربری
                </div>
            </div>

            <!-- محتوای تب‌ها -->
            <?php if (!$is_it_admin): ?>
        <!-- تب ثبت تیکت جدید برای کاربران عادی -->
<div class="tab-content <?php echo $active_tab === 'new-ticket' ? 'active' : ''; ?>" id="new-ticket">
    <h2><i class="fas fa-plus-circle"></i> ثبت تیکت جدید</h2>
    <form method="POST" action="">
        <input type="hidden" name="create_ticket" value="1">
        
        <div class="form-group">
            <label for="title"><i class="fas fa-heading"></i> عنوان تیکت *</label>
            <input type="text" id="title" name="title" placeholder="عنوان مشکل را وارد کنید" required>
        </div>
        
        <div class="form-group">
            <label for="category"><i class="fas fa-folder"></i> دسته‌بندی مشکل *</label>
            <select id="category" name="category" required onchange="toggleEquipmentField()">
                <option value="">انتخاب کنید</option>
                <option value="نرم‌افزار">نرم‌افزار</option>
                <option value="سخت‌افزار">سخت‌افزار</option>
                <option value="شبکه">شبکه</option>
                <option value="اینترنت">اینترنت</option>
                <option value="پرینتر">پرینتر</option>
                <option value="اکسس پوینت">اکسس پوینت</option>
                <option value="دیگر">دیگر</option>
            </select>
        </div>
        
        <div class="form-group" id="equipment_field" style="display: none;">
            <label for="equipment_id"><i class="fas fa-laptop"></i> انتخاب دستگاه دارای مشکل *</label>
            <select id="equipment_id" name="equipment_id">
                <option value="">لطفاً دستگاه دارای مشکل را انتخاب کنید</option>
                <?php
                // دریافت تجهیزات کاربر
                require_once 'equipment_functions.php';
                $equipmentFunctions = new EquipmentFunctions();
                $user_equipment = $equipmentFunctions->getUserEquipment($user['id']);
                
                foreach ($user_equipment as $equip):
                    $type_names = [
                        'desktop' => 'کامپیوتر',
                        'laptop' => 'لپ‌تاپ', 
                        'surface' => 'سرفیس',
                        'monitor' => 'مانیتور',
                        'printer' => 'پرینتر',
                        'scanner' => 'اسکنر',
                        'other' => 'سایر'
                    ];
                    $equipment_info = $type_names[$equip['equipment_type']] . ' - ' . $equip['brand'] . ' ' . $equip['model'];
                    if (!empty($equip['serial_number'])) {
                        $equipment_info .= ' (شماره سریال: ' . $equip['serial_number'] . ')';
                    }
                ?>
                    <option value="<?php echo $equip['id']; ?>">
                        <?php echo $equipment_info; ?>
                    </option>
                <?php endforeach; ?>
            </select>
            <small style="color: #666; display: block; margin-top: 5px;">
                <i class="fas fa-info-circle"></i> فقط در صورت انتخاب دسته‌بندی "سخت‌افزار" این فیلد نمایش داده می‌شود
            </small>
        </div>
        
        <div class="form-group">
            <label for="priority"><i class="fas fa-exclamation-circle"></i> اولویت *</label>
            <select id="priority" name="priority" required>
                <option value="low">کم</option>
                <option value="medium" selected>متوسط</option>
                <option value="high">بالا</option>
                <option value="urgent">فوری</option>
            </select>
        </div>
        
        <div class="form-group">
            <label for="description"><i class="fas fa-file-alt"></i> شرح کامل مشکل *</label>
            <textarea id="description" name="description" placeholder="شرح کامل مشکل خود را بنویسید..." required></textarea>
        </div>
        
        <button type="submit" class="btn btn-primary">
            <i class="fas fa-paper-plane"></i> ثبت تیکت
        </button>
    </form>
</div>

                <!-- تب تیکت‌های من برای کاربران عادی -->
                <div class="tab-content <?php echo $active_tab === 'my-tickets' ? 'active' : ''; ?>" id="my-tickets">
                    <h2><i class="fas fa-ticket-alt"></i> تیکت‌های من</h2>
                    
                    <?php if (empty($user_tickets)): ?>
                        <div style="text-align: center; padding: 40px; color: #666;">
                            <i class="fas fa-inbox" style="font-size: 48px; margin-bottom: 15px; opacity: 0.5;"></i>
                            <h3>هنوز تیکتی ثبت نکرده‌اید</h3>
                            <p>برای ثبت تیکت جدید به تب "ثبت تیکت جدید" مراجعه کنید</p>
                        </div>
                    <?php else: ?>
                        <div class="ticket-list">
                            <?php foreach ($user_tickets as $ticket): ?>
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
                </div>

            <?php else: ?>
                <!-- تب‌های مخصوص ادمین -->
                <?php 
                $tab_contents = [
                    'new-tickets' => 'تیکت‌های جدید',
                    'in-progress-tickets' => 'تیکت‌های در دست بررسی', 
                    'resolved-tickets' => 'تیکت‌های حل شده',
                    'all-tickets' => 'همه تیکت‌ها'
                ];
                
                foreach ($tab_contents as $tab_id => $tab_title): 
                ?>
                    <div class="tab-content <?php echo $active_tab === $tab_id ? 'active' : ''; ?>" id="<?php echo $tab_id; ?>">
                        <h2><i class="fas fa-ticket-alt"></i> <?php echo $tab_title; ?></h2>
                        
                        <?php if (empty($tickets)): ?>
                            <div style="text-align: center; padding: 40px; color: #666;">
                                <i class="fas fa-inbox" style="font-size: 48px; margin-bottom: 15px; opacity: 0.5;"></i>
                                <h3>تیکتی برای نمایش وجود ندارد</h3>
                                <p>در حال حاضر هیچ تیکتی در این بخش موجود نیست</p>
                            </div>
                        <?php else: ?>
                            <div class="ticket-list">
                                <?php foreach ($tickets as $ticket): ?>
                                    <div class="ticket-item">
                                        <div class="ticket-header">
                                            <div class="ticket-title">
                                                <span class="user-info-badge">
                                                    <i class="fas fa-user"></i> <?php echo htmlspecialchars($ticket['user_full_name']); ?>
                                                </span>
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
                                                <i class="fas fa-folder"></i>
                                                <strong>دسته‌بندی:</strong> <?php echo htmlspecialchars($ticket['category']); ?>
                                            </div>
                                            <div class="ticket-detail">
                                                <i class="fas fa-building"></i>
                                                <strong>دپارتمان:</strong> <?php echo htmlspecialchars($ticket['user_department']); ?>
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
                                        
                                        <!-- فرم مدیریت تیکت برای ادمین -->
                                        <form method="POST" action="" class="ticket-actions">
                                            <input type="hidden" name="ticket_id" value="<?php echo $ticket['id']; ?>">
                                            <input type="hidden" name="update_ticket_status" value="1">
                                            <input type="hidden" name="tab" value="<?php echo $active_tab; ?>">
                                            
                                            <div style="display: flex; gap: 10px; align-items: center; flex-wrap: wrap;">
                                                <div>
                                                    <label style="font-size: 12px; color: #666;">وضعیت جدید:</label>
                                                    <select name="status" required style="min-width: 150px;">
                                                        <option value="new" <?php echo $ticket['status'] === 'new' ? 'selected' : ''; ?>>جدید</option>
                                                        <option value="in-progress" <?php echo $ticket['status'] === 'in-progress' ? 'selected' : ''; ?>>در دست بررسی</option>
                                                        <option value="resolved" <?php echo $ticket['status'] === 'resolved' ? 'selected' : ''; ?>>حل شده</option>
                                                    </select>
                                                </div>
                                                
                                                <div style="flex: 1; min-width: 200px;">
                                                    <label style="font-size: 12px; color: #666;">توضیحات ادمین:</label>
                                                    <textarea name="admin_notes" placeholder="پاسخ یا توضیحات خود را بنویسید..." 
                                                             style="width: 100%; min-height: 60px; padding: 8px;"><?php echo htmlspecialchars($ticket['admin_notes'] ?? ''); ?></textarea>
                                                </div>
                                                
                                                <div style="align-self: flex-end;">
                                                    <button type="submit" class="btn btn-secondary">
                                                        <i class="fas fa-sync-alt"></i> بروزرسانی وضعیت
                                                    </button>
                                                </div>
                                            </div>
                                        </form>
                                        
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
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>

            <!-- تب پروفایل کاربری (مشترک) -->
            <div class="tab-content <?php echo $active_tab === 'profile' ? 'active' : ''; ?>" id="profile">
                <h2><i class="fas fa-user"></i> پروفایل کاربری</h2>
                <div style="max-width: 500px; margin: 0 auto;">
                    <div class="form-group">
                        <label><i class="fas fa-id-card"></i> نام کامل</label>
                        <input type="text" value="<?php echo htmlspecialchars($user['full_name']); ?>" readonly>
                    </div>
                    
                    <div class="form-group">
                        <label><i class="fas fa-user"></i> نام کاربری</label>
                        <input type="text" value="<?php echo htmlspecialchars($user['username']); ?>" readonly>
                    </div>
                    
                    <div class="form-group">
                        <label><i class="fas fa-building"></i> دپارتمان</label>
                        <input type="text" value="<?php echo htmlspecialchars($user['department']); ?>" readonly>
                    </div>
                    
                    <div class="form-group">
                        <label><i class="fas fa-shield-alt"></i> نوع حساب</label>
                        <input type="text" value="<?php echo $is_it_admin ? 'ادمین فناوری اطلاعات' : ($user['user_type'] === 'admin' ? 'ادمین' : 'کاربر عادی'); ?>" readonly>
                    </div>
                </div>
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
        // مدیریت تب‌ها
        document.querySelectorAll('.tab').forEach(tab => {
            tab.addEventListener('click', function() {
                const tabId = this.getAttribute('data-tab');
                
                // به روزرسانی URL بدون رفرش صفحه
                const url = new URL(window.location);
                url.searchParams.set('tab', tabId);
                window.history.pushState({}, '', url);
                
                // غیرفعال کردن همه تب‌ها
                document.querySelectorAll('.tab').forEach(t => t.classList.remove('active'));
                document.querySelectorAll('.tab-content').forEach(tc => tc.classList.remove('active'));
                
                // فعال کردن تب انتخاب شده
                this.classList.add('active');
                document.getElementById(tabId).classList.add('active');
            });
        });

        // بارگذاری اولیه تب بر اساس URL
        function loadActiveTabFromURL() {
            const urlParams = new URLSearchParams(window.location.search);
            const activeTab = urlParams.get('tab') || '<?php echo $is_it_admin ? "new-tickets" : "new-ticket"; ?>';
            
            const tabElement = document.querySelector(`.tab[data-tab="${activeTab}"]`);
            const contentElement = document.getElementById(activeTab);
            
            if (tabElement && contentElement) {
                document.querySelectorAll('.tab').forEach(t => t.classList.remove('active'));
                document.querySelectorAll('.tab-content').forEach(tc => tc.classList.remove('active'));
                
                tabElement.classList.add('active');
                contentElement.classList.add('active');
            }
        }

        // اجرا هنگام بارگذاری صفحه
        document.addEventListener('DOMContentLoaded', loadActiveTabFromURL);

        // اجرا هنگام تغییر تاریخچه مرورگر
        window.addEventListener('popstate', loadActiveTabFromURL);

        // افکت‌های hover برای کارت‌ها
        document.querySelectorAll('.stat-card, .ticket-item, .system-btn').forEach(card => {
            card.addEventListener('mouseenter', function() {
                this.style.transform = 'translateY(-5px)';
            });
            
            card.addEventListener('mouseleave', function() {
                this.style.transform = 'translateY(0)';
            });
        });

        // نمایش پیشنمایش اولویت (فقط برای کاربران عادی)
        <?php if (!$is_it_admin): ?>
        const prioritySelect = document.getElementById('priority');
        if (prioritySelect) {
            prioritySelect.addEventListener('change', function() {
                const colors = {
                    'low': '#27ae60',
                    'medium': '#f39c12', 
                    'high': '#e74c3c',
                    'urgent': '#c0392b'
                };
                this.style.color = colors[this.value];
            });

            // مقداردهی اولیه رنگ اولویت
            prioritySelect.dispatchEvent(new Event('change'));
        }
        <?php endif; ?>
    </script>
</body>
</html>