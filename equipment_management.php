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
$userFunctions = new UserFunctions();

// ایجاد جدول اگر وجود ندارد
$equipmentFunctions->createEquipmentTable();

// دریافت لیست کاربران برای نمایش اولیه
$all_users = $userFunctions->getAllUsers();

// پردازش فرم‌ها
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['add_equipment'])) {
        $user_id = $_POST['user_id'];
        $equipment_type = $_POST['equipment_type'];
        $brand = $_POST['brand'];
        $model = $_POST['model'];
        $serial_number = $_POST['serial_number'];
        $cpu = $_POST['cpu'];
        $ram = $_POST['ram'];
        $hdd = $_POST['hdd'];
        
        $result = $equipmentFunctions->addEquipment($user_id, $equipment_type, $brand, $model, $serial_number, $cpu, $ram, $hdd);
        
        if ($result['success']) {
            $success_message = $result['message'];
        } else {
            $error_message = $result['message'];
        }
    }
}

// دریافت همه تجهیزات (حذف شده)
// $all_equipment = $equipmentFunctions->getAllEquipment();
?>

<!DOCTYPE html>
<html lang="fa" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>مدیریت تجهیزات - پتروفرهنگ</title>
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
        
        .form-row {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 20px;
            margin-bottom: 20px;
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
        
        input, select, textarea {
            width: 100%;
            padding: 12px 15px;
            border: 2px solid #e9ecef;
            border-radius: 8px;
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
        
        .specs-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 15px;
            margin-top: 10px;
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
        
        /* استایل جستجوی کاربر */
        .user-search-container {
            position: relative;
        }
        
        .user-search-input {
            width: 100%;
            padding: 12px 15px;
            border: 2px solid #e9ecef;
            border-radius: 8px;
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
            padding: 10px 15px;
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
            padding: 10px;
            border-radius: 8px;
            margin-top: 10px;
            border-right: 3px solid #2575fc;
        }
        
        .hidden {
            display: none;
        }
        
        /* استایل دکمه فانتزی */
        .fancy-button-container {
            display: inline-block;
            margin: 20px 0;
        }
        
        .fancy-btn {
            display: flex;
            align-items: center;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 25px 30px;
            border-radius: 20px;
            text-decoration: none;
            box-shadow: 0 10px 30px rgba(102, 126, 234, 0.4);
            transition: all 0.3s ease;
            min-width: 350px;
            position: relative;
            overflow: hidden;
        }
        
        .fancy-btn::before {
            content: '';
            position: absolute;
            top: 0;
            right: 0;
            width: 100%;
            height: 100%;
            background: linear-gradient(45deg, transparent, rgba(255,255,255,0.1), transparent);
            transform: translateX(-100%);
            transition: transform 0.6s;
        }
        
        .fancy-btn:hover::before {
            transform: translateX(100%);
        }
        
        .fancy-btn:hover {
            transform: translateY(-5px);
            box-shadow: 0 15px 40px rgba(102, 126, 234, 0.6);
        }
        
        .fancy-btn-icon {
            font-size: 32px;
            margin-left: 15px;
            background: rgba(255,255,255,0.2);
            padding: 15px;
            border-radius: 15px;
        }
        
        .fancy-btn-content {
            flex: 1;
            text-align: right;
        }
        
        .fancy-btn-title {
            font-size: 20px;
            font-weight: bold;
            margin-bottom: 5px;
        }
        
        .fancy-btn-subtitle {
            font-size: 14px;
            opacity: 0.9;
        }
        
        .fancy-btn-arrow {
            font-size: 20px;
            margin-right: 10px;
            transition: transform 0.3s ease;
        }
        
        .fancy-btn:hover .fancy-btn-arrow {
            transform: translateX(-5px);
        }
        
        .action-section {
            text-align: center;
            margin: 40px 0;
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
        
        @media (max-width: 768px) {
            header {
                flex-direction: column;
                gap: 15px;
                text-align: center;
            }
            
            .form-row {
                grid-template-columns: 1fr;
            }
            
            .specs-grid {
                grid-template-columns: 1fr;
            }
            
            .user-info {
                justify-content: center;
            }
            
            .fancy-btn {
                min-width: 300px;
                padding: 20px 25px;
            }
        }
        
        @media (max-width: 480px) {
            .container {
                padding: 15px;
            }
            
            .main-content {
                padding: 20px;
            }
            
            .form-container {
                padding: 20px;
            }
            
            .welcome-section {
                padding: 20px;
            }
            
            .fancy-btn {
                min-width: 280px;
                padding: 15px 20px;
            }
            
            .fancy-btn-title {
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
                <div class="logo-text">سیستم مدیریت تجهیزات سخت‌افزاری</div>
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
                <h1><i class="fas fa-laptop"></i> مدیریت تجهیزات سخت‌افزاری</h1>
                <p>ثبت و مدیریت تجهیزات سخت‌افزاری پرسنل بر اساس فرم مشخصات سخت‌افزاری</p>
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

            <div class="form-container">
                <h2><i class="fas fa-plus-circle"></i> افزودن تجهیز جدید</h2>
                <form method="POST" action="" id="equipmentForm">
                    <div class="form-row">
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
                                <input type="hidden" id="user_id" name="user_id">
                            </div>
                        </div>
                        
                        <div class="form-group">
                            <label for="equipment_type"><i class="fas fa-desktop"></i> نوع تجهیز</label>
                            <select id="equipment_type" name="equipment_type" required>
                                <option value="">انتخاب نوع تجهیز</option>
                                <option value="desktop">کامپیوتر رومیزی</option>
                                <option value="laptop">لپ‌تاپ</option>
                                <option value="surface">سرفیس</option>
                                <option value="monitor">مانیتور</option>
                                <option value="printer">پرینتر</option>
                                <option value="scanner">اسکنر</option>
                                <option value="other">سایر</option>
                            </select>
                        </div>
                    </div>
                    
                    <div class="form-row">
                        <div class="form-group">
                            <label for="brand"><i class="fas fa-tag"></i> برند</label>
                            <input type="text" id="brand" name="brand" placeholder="مثال: Dell, HP, Lenovo" required>
                        </div>
                        
                        <div class="form-group">
                            <label for="model"><i class="fas fa-cube"></i> مدل</label>
                            <input type="text" id="model" name="model" placeholder="مدل تجهیز" required>
                        </div>
                        
                        <div class="form-group">
                            <label for="serial_number"><i class="fas fa-barcode"></i> شماره سریال / پلاک</label>
                            <input type="text" id="serial_number" name="serial_number" placeholder="شماره سریال یا پلاک سازمانی">
                        </div>
                    </div>

                    <h3 style="margin: 25px 0 15px 0; color: #2c3e50; border-bottom: 2px solid #6a11cb; padding-bottom: 10px;">
                        <i class="fas fa-microchip"></i> مشخصات فنی
                    </h3>
                    
                    <div class="specs-grid">
                        <div class="form-group">
                            <label for="cpu"><i class="fas fa-microchip"></i> پردازنده (CPU)</label>
                            <input type="text" id="cpu" name="cpu" placeholder="مثال: Intel Core i7-10700">
                        </div>
                        
                        <div class="form-group">
                            <label for="ram"><i class="fas fa-memory"></i> حافظه رم (RAM)</label>
                            <input type="text" id="ram" name="ram" placeholder="مثال: 16GB DDR4">
                        </div>
                        
                        <div class="form-group">
                            <label for="hdd"><i class="fas fa-hdd"></i> هارد دیسک (HDD/SSD)</label>
                            <input type="text" id="hdd" name="hdd" placeholder="مثال: 512GB SSD + 1TB HDD">
                        </div>
                    </div>
                    
                    <button type="submit" name="add_equipment" class="btn btn-primary" style="margin-top: 20px;">
                        <i class="fas fa-save"></i> ثبت تجهیز
                    </button>
                </form>
            </div>

            <!-- دکمه فانتزی برای رفتن به صفحه نمایش تجهیزات -->
            <div class="action-section">
                <div class="fancy-button-container">
                    <a href="equipment_reports.php" class="fancy-btn">
                        <div class="fancy-btn-icon">
                            <i class="fas fa-chart-bar"></i>
                        </div>
                        <div class="fancy-btn-content">
                            <div class="fancy-btn-title">گزارش‌گیری تجهیزات</div>
                            <div class="fancy-btn-subtitle">مشاهده، جستجو و دریافت گزارش</div>
                        </div>
                        <div class="fancy-btn-arrow">
                            <i class="fas fa-arrow-left"></i>
                        </div>
                    </a>
                </div>
                
                <div class="fancy-button-container">
                    <a href="equipment_list.php" class="fancy-btn">
                        <div class="fancy-btn-icon">
                            <i class="fas fa-list"></i>
                        </div>
                        <div class="fancy-btn-content">
                            <div class="fancy-btn-title">مشاهده لیست تجهیزات</div>
                            <div class="fancy-btn-subtitle">مدیریت و مشاهده تمام تجهیزات ثبت شده</div>
                        </div>
                        <div class="fancy-btn-arrow">
                            <i class="fas fa-arrow-left"></i>
                        </div>
                    </a>
                </div>
            </div>
        </div>

        <footer>
            <div class="footer-content">
                <div class="footer-text">
                    © تهیه شده توسط مدیریت فناوری اطلاعات و ارتباطات هلدینگ پتروفرهنگ
                </div>
            </div>
        </footer>
    </div>

    <script>
        // نمایش/پنهان کردن فیلدهای مشخصات فنی بر اساس نوع تجهیز
        document.getElementById('equipment_type').addEventListener('change', function() {
            const specsSection = document.querySelector('.specs-grid');
            const selectedType = this.value;
            
            // برای مانیتور، پرینتر و اسکنر، مشخصات فنی را پنهان می‌کنیم
            if (selectedType === 'monitor' || selectedType === 'printer' || selectedType === 'scanner') {
                specsSection.style.display = 'none';
            } else {
                specsSection.style.display = 'grid';
            }
        });

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
                                selectUser(user.id, user.full_name, user.department);
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
        function selectUser(userId, userName, userDepartment) {
            document.getElementById('user_id').value = userId;
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

        // اعتبارسنجی فرم
        document.getElementById('equipmentForm').addEventListener('submit', function(e) {
            const userId = document.getElementById('user_id').value;
            if (!userId) {
                e.preventDefault();
                alert('لطفاً یک کاربر انتخاب کنید');
                document.getElementById('user_search').focus();
            }
        });
    </script>
</body>
</html>
