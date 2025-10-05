<?php
session_start();
require_once 'config.php';
require_once 'user_functions.php';

if (isset($_SESSION['user'])) {
    header('Location: dashboard.php');
    exit;
}

$userFunctions = new UserFunctions();
$error_message = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['personnel_code'])) {
    $personnel_code = trim($_POST['personnel_code']);
    
    // بررسی وجود کاربر در دیتابیس
    $result = $userFunctions->loginWithPersonnelCode($personnel_code);
    
    if ($result['success']) {
        $_SESSION['user'] = $result['user'];
        header('Location: dashboard.php');
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
    <title>ورود به سامانه - پتروفرهنگ</title>
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
            max-width: 1200px;
            margin: 0 auto;
            padding: 20px;
            flex: 1;
            display: flex;
            flex-direction: column;
            justify-content: center;
            align-items: center;
        }
        
        /* استایل هدر */
        .login-header {
            text-align: center;
            margin-bottom: 40px;
        }
        
        .logo-container {
            display: flex;
            flex-direction: column;
            align-items: center;
            gap: 20px;
            margin-bottom: 30px;
        }
        
        .logo-img {
            width: 120px;
            height: 120px;
            background: linear-gradient(45deg, #ffffff, #f0f0f0);
            border-radius: 50%;
            display: flex;
            justify-content: center;
            align-items: center;
            box-shadow: 0 10px 30px rgba(44, 62, 80, 0.3);
            border: 3px solid rgba(255,255,255,0.8);
            overflow: hidden;
            padding: 15px;
        }
        
        .logo-img img {
            width: 100%;
            height: 100%;
            object-fit: contain;
        }
        
        .logo-text {
            font-size: 28px;
            font-weight: bold;
            background: linear-gradient(45deg, #2c3e50, #34495e, #2c3e50);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            text-align: center;
        }
        
        .logo-subtext {
            font-size: 16px;
            color: #666;
            text-align: center;
            margin-top: -10px;
        }
        
        /* استایل فرم لاگین */
        .login-container {
            background: rgba(255, 255, 255, 0.95);
            padding: 40px;
            border-radius: 20px;
            box-shadow: 0 15px 40px rgba(0,0,0,0.15);
            width: 100%;
            max-width: 450px;
            border: 1px solid rgba(255,255,255,0.5);
            backdrop-filter: blur(10px);
            position: relative;
            overflow: hidden;
        }
        
        .login-container::before {
            content: '';
            position: absolute;
            top: 0;
            right: 0;
            width: 100%;
            height: 4px;
            background: linear-gradient(45deg, #6a11cb, #2575fc);
        }
        
        .form-header {
            text-align: center;
            margin-bottom: 30px;
        }
        
        .form-header h2 {
            color: #2c3e50;
            margin-bottom: 10px;
            font-size: 24px;
        }
        
        .form-header p {
            color: #666;
            font-size: 14px;
        }
        
        .form-group {
            margin-bottom: 25px;
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
            text-align: center;
            font-family: 'Courier New', monospace;
            font-size: 18px;
            font-weight: bold;
            letter-spacing: 2px;
        }
        
        input:focus {
            outline: none;
            border-color: #6a11cb;
            box-shadow: 0 0 0 3px rgba(106,17,203,0.1);
            background: white;
        }
        
        .btn {
            width: 100%;
            padding: 15px;
            background: linear-gradient(45deg, #6a11cb, #2575fc);
            color: white;
            border: none;
            border-radius: 12px;
            font-size: 16px;
            cursor: pointer;
            transition: all 0.3s ease;
            font-weight: bold;
            position: relative;
            overflow: hidden;
        }
        
        .btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 20px rgba(106,17,203,0.3);
        }
        
        .btn:active {
            transform: translateY(0);
        }
        
        .notification {
            padding: 15px;
            margin-bottom: 20px;
            border-radius: 10px;
            text-align: center;
            font-weight: 500;
        }
        
        .notification.error {
            background: #f8d7da;
            color: #721c24;
            border: 1px solid #f5c6cb;
        }
        
        .login-info {
            background: linear-gradient(135deg, #e7f3ff, #d4e7ff);
            padding: 20px;
            border-radius: 12px;
            margin-bottom: 25px;
            border-right: 4px solid #2575fc;
        }
        
        .login-info h4 {
            color: #2575fc;
            margin-bottom: 10px;
            font-size: 15px;
            display: flex;
            align-items: center;
            gap: 8px;
        }
        
        .login-info ul {
            list-style: none;
            padding: 0;
            margin: 0;
            font-size: 14px;
            color: #555;
        }
        
        .login-info li {
            margin-bottom: 8px;
            padding-right: 20px;
            position: relative;
            display: flex;
            align-items: center;
            gap: 8px;
        }
        
        .login-info li:before {
            content: '✓';
            color: #2575fc;
            position: absolute;
            right: 0;
            font-weight: bold;
        }
        
        /* استایل فوتر */
        footer {
            text-align: center;
            padding: 30px 20px;
            margin-top: 50px;
            border-top: 1px solid rgba(255,255,255,0.5);
            background: rgba(255,255,255,0.9);
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
        
        .support-contact {
            text-align: center;
            margin-top: 20px;
            padding: 15px;
            background: rgba(248, 249, 250, 0.8);
            border-radius: 10px;
            font-size: 13px;
            color: #666;
        }
        
        @media (max-width: 768px) {
            .container {
                padding: 15px;
            }
            
            .login-container {
                padding: 30px 20px;
                margin: 10px;
            }
            
            .logo-img {
                width: 100px;
                height: 100px;
            }
            
            .logo-text {
                font-size: 24px;
            }
            
            footer {
                padding: 20px 15px;
            }
        }
        
        @media (max-width: 480px) {
            .logo-img {
                width: 80px;
                height: 80px;
            }
            
            .logo-text {
                font-size: 20px;
            }
            
            .login-container {
                padding: 25px 15px;
            }
            
            input {
                padding: 12px 15px;
                font-size: 16px;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="login-header">
            <div class="logo-container">
                <div class="logo-img">
                    <img src="images/Petrofarhang.png" alt="لوگوی پتروفرهنگ" onerror="this.style.display='none'">
                </div>
                <div>
                    <div class="logo-text">سامانه پشتیبانی خدمات فناوری اطلاعات و ارتباطات</div>
                    <div class="logo-subtext">هلدینگ پتروفرهنگ</div>
                </div>
            </div>
        </div>

        <div class="login-container">
            <div class="form-header">
                <h2><i class="fas fa-sign-in-alt"></i> ورود به سامانه</h2>
                <p>لطفا کد پرسنلی خود را وارد کنید</p>
            </div>

            <div class="login-info">
                <h4><i class="fas fa-info-circle"></i> راهنمای ورود:</h4>
                <ul>
                    <li><i class="fas fa-check"></i> ورود به سامانه فقط با کد پرسنلی</li>
                    <li><i class="fas fa-check"></i> کد پرسنلی از سامانه چارگون</li>
                    <li><i class="fas fa-check"></i> بدون نیاز به رمز عبور</li>
                </ul>
            </div>

            <?php if ($error_message): ?>
                <div class="notification error">
                    <i class="fas fa-exclamation-circle"></i> <?php echo $error_message; ?>
                </div>
            <?php endif; ?>

            <form method="POST" action="">
                <div class="form-group">
                    <label for="personnel_code"><i class="fas fa-id-card"></i> کد پرسنلی</label>
                    <input type="text" id="personnel_code" name="personnel_code" 
                           placeholder="مثال: 12345" 
                           pattern="[0-9]+" 
                           title="لطفا فقط عدد وارد کنید"
                           required
                           maxlength="10"
                           autocomplete="off">
                </div>
                
                <button type="submit" class="btn">
                    <i class="fas fa-sign-in-alt"></i> ورود به سامانه
                </button>
            </form>

            <div class="support-contact">
                <i class="fas fa-headset"></i>
                در صورت مشکل در ورود، با مدیریت فناوری اطلاعات تماس بگیرید
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

    <!-- قبل از تگ </body> در login.php اضافه کنید -->
    <div style="text-align: center; margin-top: 20px;">
        <a href="admin_login.php" class="btn btn-outline" style="background: transparent; border: 2px solid #e74c3c; color: #e74c3c;">
            <i class="fas fa-user-shield"></i> ورود ادمین فناوری اطلاعات
        </a>
    </div>

    <script>
        // محدود کردن ورودی به فقط اعداد
        document.getElementById('personnel_code').addEventListener('input', function(e) {
            this.value = this.value.replace(/[^0-9]/g, '');
        });

        // فوکوس خودکار روی فیلد کد پرسنلی
        document.addEventListener('DOMContentLoaded', function() {
            document.getElementById('personnel_code').focus();
            
            // افزودن انیمیشن به فرم
            const loginContainer = document.querySelector('.login-container');
            loginContainer.style.transform = 'translateY(20px)';
            loginContainer.style.opacity = '0';
            
            setTimeout(() => {
                loginContainer.style.transition = 'all 0.5s ease';
                loginContainer.style.transform = 'translateY(0)';
                loginContainer.style.opacity = '1';
            }, 100);
        });

        // افکت هنگام هاور روی دکمه
        const btn = document.querySelector('.btn');
        btn.addEventListener('mouseenter', function() {
            this.style.background = 'linear-gradient(45deg, #2575fc, #6a11cb)';
        });
        
        btn.addEventListener('mouseleave', function() {
            this.style.background = 'linear-gradient(45deg, #6a11cb, #2575fc)';
        });
    </script>
</body>
</html>