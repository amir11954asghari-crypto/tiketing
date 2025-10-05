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

$equipmentFunctions = new EquipmentFunctions();

// پردازش حذف تجهیز
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_equipment'])) {
    $equipment_id = $_POST['equipment_id'];
    $result = $equipmentFunctions->deleteEquipment($equipment_id);
    
    if ($result['success']) {
        $success_message = $result['message'];
    } else {
        $error_message = $result['message'];
    }
}

// دریافت همه تجهیزات
$all_equipment = $equipmentFunctions->getAllEquipment();
?>

<!DOCTYPE html>
<html lang="fa" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>لیست تجهیزات - پتروفرهنگ</title>
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
        
        .form-container {
            background: rgba(255,255,255,0.95);
            padding: 30px;
            border-radius: 15px;
            margin-bottom: 30px;
            border: 1px solid rgba(255,255,255,0.5);
        }
        
        .equipment-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(350px, 1fr));
            gap: 20px;
            margin-top: 20px;
        }
        
        .equipment-card {
            background: linear-gradient(135deg, rgba(255,255,255,0.95), rgba(245,247,250,0.95));
            padding: 20px;
            border-radius: 12px;
            box-shadow: 0 5px 15px rgba(0,0,0,0.08);
            border: 1px solid rgba(255,255,255,0.5);
            border-right: 4px solid #6a11cb;
            transition: all 0.3s ease;
        }
        
        .equipment-card:hover {
            transform: translateY(-3px);
            box-shadow: 0 10px 25px rgba(106,17,203,0.15);
        }
        
        .equipment-type {
            background: #6a11cb;
            color: white;
            padding: 5px 10px;
            border-radius: 20px;
            font-size: 12px;
            margin-bottom: 10px;
            display: inline-block;
        }
        
        .equipment-details {
            margin: 10px 0;
        }
        
        .equipment-detail {
            margin-bottom: 5px;
            font-size: 14px;
        }
        
        .specs-detail {
            background: #f8f9fa;
            padding: 8px;
            border-radius: 6px;
            margin: 5px 0;
            font-size: 13px;
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
        
        .action-buttons {
            display: flex;
            gap: 10px;
            margin-top: 15px;
            flex-wrap: wrap;
        }
        
        .search-box {
            background: rgba(255,255,255,0.9);
            padding: 20px;
            border-radius: 12px;
            margin-bottom: 20px;
            border: 1px solid rgba(255,255,255,0.5);
        }
        
        .search-form {
            display: grid;
            grid-template-columns: 1fr auto;
            gap: 15px;
            align-items: end;
        }
        
        .form-group {
            margin-bottom: 15px;
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
        
        .stats-summary {
            background: #f8f9fa;
            padding: 20px;
            border-radius: 12px;
            margin-bottom: 20px;
            border-right: 4px solid #3498db;
        }
        
        @media (max-width: 768px) {
            header {
                flex-direction: column;
                gap: 15px;
                text-align: center;
            }
            
            .equipment-grid {
                grid-template-columns: 1fr;
            }
            
            .search-form {
                grid-template-columns: 1fr;
            }
            
            .user-info {
                justify-content: center;
            }
            
            .action-buttons {
                flex-direction: column;
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
                <div class="logo-text">لیست تجهیزات سخت‌افزاری</div>
            </div>
            <div class="user-info">
                <div class="user-welcome">
                    <i class="fas fa-user"></i>
                    <?php echo htmlspecialchars($user['full_name']); ?>
                </div>
                <a href="equipment_management.php" class="btn btn-outline">
                    <i class="fas fa-plus"></i> افزودن تجهیز جدید
                </a>
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
                <h1><i class="fas fa-list"></i> لیست تجهیزات ثبت شده</h1>
                <p>مدیریت و مشاهده تمام تجهیزات سخت‌افزاری ثبت شده در سیستم</p>
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

            <div class="stats-summary">
                <h3><i class="fas fa-chart-bar"></i> آمار تجهیزات</h3>
                <p>تعداد کل تجهیزات: <strong><?php echo count($all_equipment); ?></strong></p>
                <?php
                $equipment_types = array_count_values(array_column($all_equipment, 'equipment_type'));
                $type_names = [
                    'desktop' => 'کامپیوتر',
                    'laptop' => 'لپ‌تاپ', 
                    'surface' => 'سرفیس',
                    'monitor' => 'مانیتور',
                    'printer' => 'پرینتر',
                    'scanner' => 'اسکنر',
                    'other' => 'سایر'
                ];
                ?>
                <p>تعداد بر اساس نوع: 
                    <?php foreach($equipment_types as $type => $count): ?>
                        <span class="stat-badge" style="background: #6a11cb; color: white; padding: 2px 8px; border-radius: 10px; margin: 0 2px;">
                            <?php echo $type_names[$type] . ': ' . $count; ?>
                        </span>
                    <?php endforeach; ?>
                </p>
            </div>

            <?php if (empty($all_equipment)): ?>
                <div style="text-align: center; padding: 40px; color: #666;">
                    <i class="fas fa-laptop" style="font-size: 48px; margin-bottom: 15px; opacity: 0.5;"></i>
                    <h3>هنوز تجهیزی ثبت نشده است</h3>
                    <p>برای شروع، اولین تجهیز را در صفحه مدیریت تجهیزات ثبت کنید</p>
                    <a href="equipment_management.php" class="btn btn-primary" style="margin-top: 15px;">
                        <i class="fas fa-plus"></i> افزودن تجهیز جدید
                    </a>
                </div>
            <?php else: ?>
                <div class="equipment-grid">
                    <?php foreach ($all_equipment as $equipment): ?>
                        <div class="equipment-card">
                            <div class="equipment-type">
                                <?php echo $type_names[$equipment['equipment_type']]; ?>
                            </div>
                            
                            <h4><?php echo htmlspecialchars($equipment['brand'] . ' ' . $equipment['model']); ?></h4>
                            
                            <div class="equipment-details">
                                <div class="equipment-detail">
                                    <strong><i class="fas fa-user"></i> کاربر:</strong> <?php echo htmlspecialchars($equipment['full_name']); ?>
                                </div>
                                <div class="equipment-detail">
                                    <strong><i class="fas fa-building"></i> دپارتمان:</strong> <?php echo htmlspecialchars($equipment['department']); ?>
                                </div>
                                <?php if (!empty($equipment['serial_number'])): ?>
                                    <div class="equipment-detail">
                                        <strong><i class="fas fa-barcode"></i> شماره سریال:</strong> <?php echo htmlspecialchars($equipment['serial_number']); ?>
                                    </div>
                                <?php endif; ?>
                                
                                <?php if (!empty($equipment['cpu']) || !empty($equipment['ram']) || !empty($equipment['hdd'])): ?>
                                    <div style="margin-top: 10px;">
                                        <strong><i class="fas fa-microchip"></i> مشخصات فنی:</strong>
                                        <?php if (!empty($equipment['cpu'])): ?>
                                            <div class="specs-detail">
                                                <strong>پردازنده:</strong> <?php echo htmlspecialchars($equipment['cpu']); ?>
                                            </div>
                                        <?php endif; ?>
                                        <?php if (!empty($equipment['ram'])): ?>
                                            <div class="specs-detail">
                                                <strong>حافظه رم:</strong> <?php echo htmlspecialchars($equipment['ram']); ?>
                                            </div>
                                        <?php endif; ?>
                                        <?php if (!empty($equipment['hdd'])): ?>
                                            <div class="specs-detail">
                                                <strong>هارد دیسک:</strong> <?php echo htmlspecialchars($equipment['hdd']); ?>
                                            </div>
                                        <?php endif; ?>
                                    </div>
                                <?php endif; ?>
                            </div>
                            
                            <div class="action-buttons">
                                <form method="POST" action="">
                                    <input type="hidden" name="equipment_id" value="<?php echo $equipment['id']; ?>">
                                    <button type="submit" name="delete_equipment" class="btn btn-danger" 
                                            onclick="return confirm('آیا از حذف این تجهیز مطمئن هستید؟')">
                                        <i class="fas fa-trash"></i> حذف
                                    </button>
                                </form>
                            </div>
                            
                            <div class="equipment-date" style="font-size: 12px; color: #999; margin-top: 10px;">
                                <i class="fas fa-calendar"></i>
                                ثبت شده در: <?php echo date('Y/m/d', strtotime($equipment['created_at'])); ?>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <script>
        // افکت‌های hover برای کارت‌ها
        document.querySelectorAll('.equipment-card').forEach(card => {
            card.addEventListener('mouseenter', function() {
                this.style.transform = 'translateY(-5px)';
            });
            
            card.addEventListener('mouseleave', function() {
                this.style.transform = 'translateY(0)';
            });
        });
    </script>
</body>
</html>
