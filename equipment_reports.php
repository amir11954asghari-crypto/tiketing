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
$search_results = [];
$search_performed = false;
$search_type = 'name';
$search_term = '';
$selected_user_id = '';
$selected_user_name = '';

// پردازش جستجو
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['search'])) {
    $search_type = $_POST['search_type'];
    $search_term = trim($_POST['search_term']);
    $selected_user_id = $_POST['selected_user_id'] ?? '';
    $selected_user_name = $_POST['selected_user_name'] ?? '';
    $search_performed = true;
    
    if (!empty($search_term)) {
        if ($search_type === 'name' && !empty($selected_user_id)) {
            // جستجو بر اساس کاربر انتخاب شده
            $search_results = $equipmentFunctions->getUserEquipment($selected_user_id);
        } else {
            // جستجو بر اساس عبارت
            if ($search_type === 'name') {
                $search_results = $equipmentFunctions->searchEquipmentByUserName($search_term);
            } else {
                $search_results = $equipmentFunctions->searchEquipmentBySerial($search_term);
            }
        }
    }
}
?>

<!DOCTYPE html>
<html lang="fa" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>گزارش‌گیری تجهیزات - پتروفرهنگ</title>
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
            grid-template-columns: 1fr 2fr auto;
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
        
        .stat-badge {
            display: inline-block;
            padding: 5px 10px;
            border-radius: 6px;
            font-size: 12px;
            margin: 0 2px;
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
            
            .equipment-grid {
                grid-template-columns: 1fr;
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
                <div class="logo-text">سیستم گزارش‌گیری تجهیزات</div>
            </div>
            <div class="user-info">
                <div class="user-welcome">
                    <i class="fas fa-user"></i>
                    <?php echo htmlspecialchars($user['full_name']); ?>
                </div>
                <a href="equipment_management.php" class="btn btn-outline">
                    <i class="fas fa-arrow-right"></i> بازگشت به مدیریت
                </a>
                <a href="dashboard.php" class="btn btn-outline">
                    <i class="fas fa-home"></i> داشبورد
                </a>
                <a href="logout.php" class="btn btn-danger">
                    <i class="fas fa-sign-out-alt"></i> خروج
                </a>
            </div>
        </header>

        <div class="main-content">
            <div class="welcome-section">
                <h1><i class="fas fa-chart-bar"></i> سیستم گزارش‌گیری تجهیزات</h1>
                <p>جستجو، مشاهده و دریافت گزارش کامل تجهیزات سخت‌افزاری</p>
            </div>

            <form method="POST" action="" class="search-form" id="searchForm">
                <div class="form-group">
                    <label for="search_type"><i class="fas fa-search"></i> نوع جستجو</label>
                    <select id="search_type" name="search_type" required onchange="toggleSearchField()">
                        <option value="name" <?php echo $search_type === 'name' ? 'selected' : ''; ?>>نام و نام خانوادگی</option>
                        <option value="serial" <?php echo $search_type === 'serial' ? 'selected' : ''; ?>>شماره سریال / پلاک</option>
                    </select>
                </div>
                
                <div class="form-group">
                    <div id="name_search_field" style="<?php echo $search_type === 'name' ? '' : 'display: none;'; ?>">
                        <label for="user_search">
                            <i class="fas fa-user"></i> جستجوی کاربر
                        </label>
                        <div class="user-search-container">
                            <input type="text" id="user_search" name="user_search" 
                                   value="<?php echo $search_type === 'name' ? htmlspecialchars($search_term) : ''; ?>" 
                                   placeholder="حداقل 3 حرف از فامیل کاربر را وارد کنید..."
                                   class="user-search-input" autocomplete="off">
                            <div id="user_search_results" class="user-search-results"></div>
                        </div>
                        <div id="selected_user_display" class="selected-user <?php echo empty($selected_user_id) ? 'hidden' : ''; ?>">
                            <strong>کاربر انتخاب شده:</strong>
                            <span id="selected_user_name"><?php echo htmlspecialchars($selected_user_name); ?></span>
                            <input type="hidden" id="selected_user_id" name="selected_user_id" value="<?php echo $selected_user_id; ?>">
                            <input type="hidden" id="selected_user_fullname" name="selected_user_name" value="<?php echo htmlspecialchars($selected_user_name); ?>">
                        </div>
                    </div>
                    
                    <div id="serial_search_field" style="<?php echo $search_type === 'serial' ? '' : 'display: none;'; ?>">
                        <label for="search_term">
                            <i class="fas fa-barcode"></i> شماره سریال / پلاک
                        </label>
                        <input type="text" id="search_term_serial" name="search_term" 
                               value="<?php echo $search_type === 'serial' ? htmlspecialchars($search_term) : ''; ?>" 
                               placeholder="شماره سریال یا پلاک را وارد کنید">
                    </div>
                </div>
                
                <button type="submit" name="search" class="btn btn-primary">
                    <i class="fas fa-search"></i> جستجو
                </button>
            </form>

            <?php if ($search_performed): ?>
                <?php if (empty($search_term) && empty($selected_user_id)): ?>
                    <div class="notification error">
                        <i class="fas fa-exclamation-circle"></i> لطفاً عبارت جستجو را وارد کنید
                    </div>
                <?php elseif (empty($search_results)): ?>
                    <div class="notification error">
                        <i class="fas fa-info-circle"></i> 
                        <?php if ($search_type === 'name' && !empty($selected_user_id)): ?>
                            هیچ تجهیزی برای کاربر "<?php echo htmlspecialchars($selected_user_name); ?>" یافت نشد
                        <?php else: ?>
                            هیچ تجهیزی 
                            <?php echo $search_type === 'name' ? 'برای "' . htmlspecialchars($search_term) . '"' : 'با شماره سریال "' . htmlspecialchars($search_term) . '"'; ?>
                            یافت نشد
                        <?php endif; ?>
                    </div>
                <?php else: ?>
                    <div class="result-summary">
                        <h3><i class="fas fa-list"></i> نتایج جستجو</h3>
                        <p>تعداد تجهیزات یافت شده: <strong><?php echo count($search_results); ?></strong></p>
                        <p>
                            <?php 
                            $users = array_unique(array_column($search_results, 'full_name'));
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
                        
                        <!-- اصلاح شده: انتقال پارامترهای صحیح -->
                        <a href="generate_equipment_pdf.php?type=<?php echo $search_type; ?>&term=<?php echo urlencode($search_type === 'name' && !empty($selected_user_id) ? $selected_user_name : $search_term); ?>&user_id=<?php echo $selected_user_id; ?>" class="btn btn-danger" target="_blank">
                            <i class="fas fa-file-pdf"></i> خروجی PDF
                        </a>
                        
                        <a href="export_equipment_excel.php?type=<?php echo $search_type; ?>&term=<?php echo urlencode($search_type === 'name' && !empty($selected_user_id) ? $selected_user_name : $search_term); ?>&user_id=<?php echo $selected_user_id; ?>" class="btn btn-success" target="_blank">
                            <i class="fas fa-file-excel"></i> خروجی Excel
                        </a>
                    </div>

                    <div class="equipment-grid">
                        <?php foreach ($search_results as $equipment): ?>
                            <div class="equipment-card">
                                <div class="equipment-type">
                                    <?php 
                                    $type_names = [
                                        'desktop' => 'کامپیوتر',
                                        'laptop' => 'لپ‌تاپ', 
                                        'surface' => 'سرفیس',
                                        'monitor' => 'مانیتور',
                                        'printer' => 'پرینتر',
                                        'scanner' => 'اسکنر',
                                        'other' => 'سایر'
                                    ];
                                    echo $type_names[$equipment['equipment_type']];
                                    ?>
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
                                
                                <div class="equipment-date" style="font-size: 12px; color: #999; margin-top: 10px;">
                                    <i class="fas fa-calendar"></i>
                                    ثبت شده در: <?php echo date('Y/m/d', strtotime($equipment['created_at'])); ?>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            <?php endif; ?>
        </div>
    </div>

    <script>
        // تغییر فیلد جستجو بر اساس نوع
        function toggleSearchField() {
            const searchType = document.getElementById('search_type').value;
            const nameField = document.getElementById('name_search_field');
            const serialField = document.getElementById('serial_search_field');
            
            if (searchType === 'name') {
                nameField.style.display = 'block';
                serialField.style.display = 'none';
            } else {
                nameField.style.display = 'none';
                serialField.style.display = 'block';
            }
        }

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
                                selectUser(user.id, user.full_name);
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
        function selectUser(userId, userName) {
            document.getElementById('selected_user_id').value = userId;
            document.getElementById('selected_user_fullname').value = userName;
            document.getElementById('selected_user_name').textContent = userName;
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
        document.getElementById('searchForm').addEventListener('submit', function(e) {
            const searchType = document.getElementById('search_type').value;
            const selectedUserId = document.getElementById('selected_user_id').value;
            const searchTerm = document.getElementById('search_term_serial').value;
            const userSearch = document.getElementById('user_search').value;
            
            if (searchType === 'name' && !selectedUserId && userSearch.length < 3) {
                e.preventDefault();
                alert('لطفاً یک کاربر انتخاب کنید یا حداقل 3 حرف از نام کاربر را وارد کنید');
                document.getElementById('user_search').focus();
            } else if (searchType === 'serial' && !searchTerm.trim()) {
                e.preventDefault();
                alert('لطفاً شماره سریال را وارد کنید');
                document.getElementById('search_term_serial').focus();
            }
        });
    </script>
</body>
</html>
