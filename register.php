<?php
// register.php
require_once 'user_functions.php';

$userFunctions = new UserFunctions();
$error_message = '';
$success_message = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = $_POST['username'];
    $password = $_POST['password'];
    $confirm_password = $_POST['confirm_password'];
    $fullName = $_POST['full_name'];
    $department = $_POST['department'];
    $userType = 'user';
    
    if (empty($username) || empty($password) || empty($fullName) || empty($department)) {
        $error_message = 'لطفاً تمام فیلدهای ضروری را پر کنید';
    } elseif ($password !== $confirm_password) {
        $error_message = 'رمز عبور و تکرار آن مطابقت ندارند';
    } elseif (strlen($password) < 6) {
        $error_message = 'رمز عبور باید حداقل ۶ کاراکتر باشد';
    } else {
        $result = $userFunctions->register($username, $password, $fullName, $department, $userType);
        
        if ($result['success']) {
            $success_message = $result['message'];
            header('refresh:3;url=login.php');
        } else {
            $error_message = $result['message'];
        }
    }
}
?>

<!DOCTYPE html>
<html lang="fa" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ثبت نام - پتروفرهنگ</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://cdn.jsdelivr.net/gh/rastikerdar/vazirmatn@v33.003/Vazirmatn-font-face.css" rel="stylesheet" type="text/css" />
    <link rel="icon" href="../images/favicon.ico" type="image/x-icon">
    
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
                            url('../images/background.jpg');
            background-size: cover;
            background-position: center;
            background-attachment: fixed;
        }
        
        .container {
            max-width: 1200px;
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
            background: rgba(255,255,255,0.9);
            border-radius: 15px;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.05);
            backdrop-filter: blur(5px);
        }
        
        .logo {
            display: flex;
            align-items: center;
            gap: 15px;
        }
        
        .logo-img {
            width: 60px;
            height: 60px;
            background: linear-gradient(45deg, #ffffff, #f0f0f0);
            border-radius: 50%;
            display: flex;
            justify-content: center;
            align-items: center;
            box-shadow: 0 5px 15px rgba(44, 62, 80, 0.3);
            border: 2px solid rgba(255,255,255,0.5);
            overflow: hidden;
        }
        
        .logo-img img {
            width: 85%;
            height: 85%;
            object-fit: contain;
            padding: 5px;
        }
        
        .logo-text {
            font-size: 20px;
            font-weight: bold;
            background: linear-gradient(45deg, #2c3e50, #34495e, #2c3e50);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
        }
        
        /* استایل فرم ثبت نام */
        .register-container {
            background: rgba(255, 255, 255, 0.95);
            padding: 40px;
            border-radius: 15px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.1);
            width: 100%;
            max-width: 500px;
            margin: 0 auto;
            border: 1px solid rgba(255,255,255,0.5);
            backdrop-filter: blur(5px);
        }
        
        .register-header {
            text-align: center;
            margin-bottom: 30px;
        }
        
        .register-header h2 {
            color: #2c3e50;
            margin-bottom: 10px;
            font-size: 24px;
        }
        
        .register-header p {
            color: #666;
            font-size: 14px;
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
            border: 1px solid #ddd;
            border-radius: 8px;
            font-size: 16px;
            transition: all 0.3s ease;
            background: rgba(255,255,255,0.8);
        }
        
        input:focus, select:focus {
            outline: none;
            border-color: #6a11cb;
            box-shadow: 0 0 0 3px rgba(106,17,203,0.1);
        }
        
        .btn {
            width: 100%;
            padding: 12px;
            background: linear-gradient(45deg, #6a11cb, #2575fc);
            color: white;
            border: none;
            border-radius: 8px;
            font-size: 16px;
            cursor: pointer;
            transition: all 0.3s ease;
            font-weight: bold;
        }
        
        .btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(106,17,203,0.3);
        }
        
        .notification {
            padding: 12px;
            margin-bottom: 20px;
            border-radius: 8px;
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
        
        .register-footer {
            text-align: center;
            margin-top: 20px;
            color: #666;
        }
        
        .register-footer a {
            color: #6a11cb;
            text-decoration: none;
            font-weight: 500;
        }
        
        .register-footer a:hover {
            text-decoration: underline;
        }
        
        /* استایل فوتر */
        footer {
            text-align: center;
            padding: 25px;
            margin-top: 40px;
            border-top: 1px solid rgba(255,255,255,0.5);
            background: rgba(255,255,255,0.9);
            border-radius: 15px;
            color: #495057;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.05);
            backdrop-filter: blur(5px);
        }
        
        .flags {
            display: flex;
            justify-content: center;
            gap: 15px;
            margin-top: 15px;
        }
        
        .flag {
            width: 30px;
            height: 20px;
            border-radius: 4px;
            overflow: hidden;
            box-shadow: 0 0 10px rgba(0,0,0,0.1);
        }
        
        .iran-flag {
            background: linear-gradient(to bottom, #239F40 33%, #FFFFFF 33%, #FFFFFF 66%, #DA0000 66%);
            position: relative;
        }
        
        .iran-flag:before {
            content: '☫';
            position: absolute;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            color: #FF0000;
            font-size: 10px;
        }
        
        @media (max-width: 768px) {
            .register-container {
                padding: 30px 20px;
                margin: 20px;
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
                    <img src="../images/Petrofarhang.png" alt="لوگوی پتروفرهنگ">
                </div>
                <div class="logo-text">سامانه تیکتینگ پشتیبانی فنی</div>
            </div>
        </header>

        <div class="register-container">
            <div class="register-header">
                <h2><i class="fas fa-user-plus"></i> ثبت نام کاربر جدید</h2>
                <p>فرم زیر را برای ایجاد حساب کاربری جدید تکمیل کنید</p>
            </div>

            <?php if ($success_message): ?>
                <div class="notification success">
                    <i class="fas fa-check-circle"></i> <?php echo $success_message; ?>
                    <p style="margin-top: 10px; font-size: 14px;">در حال انتقال به صفحه ورود...</p>
                </div>
            <?php else: ?>
                <?php if ($error_message): ?>
                    <div class="notification error">
                        <i class="fas fa-exclamation-circle"></i> <?php echo $error_message; ?>
                    </div>
                <?php endif; ?>

                <form method="POST" action="">
                    <div class="form-group">
                        <label for="full_name"><i class="fas fa-id-card"></i> نام کامل *</label>
                        <input type="text" id="full_name" name="full_name" 
                               value="<?php echo isset($_POST['full_name']) ? htmlspecialchars($_POST['full_name']) : ''; ?>" 
                               placeholder="نام کامل خود را وارد کنید" required>
                    </div>
                    
                    <div class="form-group">
                        <label for="username"><i class="fas fa-user"></i> نام کاربری *</label>
                        <input type="text" id="username" name="username" 
                               value="<?php echo isset($_POST['username']) ? htmlspecialchars($_POST['username']) : ''; ?>" 
                               placeholder="نام کاربری مورد نظر را وارد کنید" required>
                    </div>
                    
                    <div class="form-group">
                        <label for="password"><i class="fas fa-lock"></i> رمز عبور *</label>
                        <input type="password" id="password" name="password" 
                               placeholder="رمز عبور خود را وارد کنید" required>
                    </div>
                    
                    <div class="form-group">
                        <label for="confirm_password"><i class="fas fa-lock"></i> تکرار رمز عبور *</label>
                        <input type="password" id="confirm_password" name="confirm_password" 
                               placeholder="رمز عبور خود را مجدداً وارد کنید" required>
                    </div>
                    
                    <div class="form-group">
                        <label for="department"><i class="fas fa-building"></i> دپارتمان *</label>
                        <select id="department" name="department" required>
                            <option value="">انتخاب کنید</option>
                            <option value="فناوری اطلاعات" <?php echo (isset($_POST['department']) && $_POST['department'] === 'فناوری اطلاعات') ? 'selected' : ''; ?>>فناوری اطلاعات</option>
                            <option value="مالی" <?php echo (isset($_POST['department']) && $_POST['department'] === 'مالی') ? 'selected' : ''; ?>>مالی</option>
                            <option value="فروش" <?php echo (isset($_POST['department']) && $_POST['department'] === 'فروش') ? 'selected' : ''; ?>>فروش</option>
                            <option value="پشتیبانی" <?php echo (isset($_POST['department']) && $_POST['department'] === 'پشتیبانی') ? 'selected' : ''; ?>>پشتیبانی</option>
                            <option value="بازاریابی" <?php echo (isset($_POST['department']) && $_POST['department'] === 'بازاریابی') ? 'selected' : ''; ?>>بازاریابی</option>
                            <option value="تولید" <?php echo (isset($_POST['department']) && $_POST['department'] === 'تولید') ? 'selected' : ''; ?>>تولید</option>
                            <option value="منابع انسانی" <?php echo (isset($_POST['department']) && $_POST['department'] === 'منابع انسانی') ? 'selected' : ''; ?>>منابع انسانی</option>
                            <option value="دیگر" <?php echo (isset($_POST['department']) && $_POST['department'] === 'دیگر') ? 'selected' : ''; ?>>دیگر</option>
                        </select>
                    </div>
                    
                    <button type="submit" class="btn">
                        <i class="fas fa-user-plus"></i> ثبت نام
                    </button>
                </form>

                <div class="register-footer">
                    <p>قبلاً حساب کاربری دارید؟ <a href="login.php">وارد شوید</a></p>
                </div>
            <?php endif; ?>
        </div>

        <footer>
            <p>© تهیه شده توسط مدیریت فناوری اطلاعات و ارتباطات هلدینگ پتروفرهنگ</p>
            <div class="flags">
                <div class="flag iran-flag"></div>
            </div>
        </footer>
    </div>
</body>
</html>