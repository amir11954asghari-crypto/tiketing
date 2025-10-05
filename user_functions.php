<?php
// user_functions.php
require_once 'config.php';

class UserFunctions {
    private $pdo;
    
    public function __construct() {
        $database = new Database();
        $this->pdo = $database->getConnection();
    }
    
    // ورود با کد پرسنلی
    public function loginWithPersonnelCode($personnel_code) {
        try {
            // لیست کدهای پرسنلی مجاز
            $allowed_codes = ['1032', '111']; // فقط این کاربران مجاز هستند
            
            if (!in_array($personnel_code, $allowed_codes)) {
                return [
                    'success' => false, 
                    'message' => 'کد پرسنلی وارد شده معتبر نمی‌باشد.'
                ];
            }
            
            // بررسی وجود کاربر در دیتابیس
            $stmt = $this->pdo->prepare("
                SELECT * FROM users 
                WHERE username = :personnel_code 
            ");
            
            $stmt->execute([
                ':personnel_code' => $personnel_code
            ]);
            
            $user = $stmt->fetch();
            
            if (!$user) {
                return [
                    'success' => false, 
                    'message' => 'کاربری با این کد پرسنلی در سیستم ثبت نشده است.'
                ];
            }
            
            // اگر کاربر پیدا شد، اطلاعات را برمی‌گردانیم
            return [
                'success' => true,
                'user' => [
                    'id' => $user['id'],
                    'username' => $user['username'],
                    'full_name' => $user['full_name'],
                    'department' => $user['department'],
                    'user_type' => $user['user_type']
                ]
            ];
            
        } catch(PDOException $e) {
            error_log("خطا در ورود با کد پرسنلی: " . $e->getMessage());
            return [
                'success' => false, 
                'message' => 'خطای سیستمی رخ داده است. لطفاً مجدداً تلاش کنید.'
            ];
        }
    }
    
    // ورود با نام کاربری و رمز عبور
    public function login($username, $password) {
        try {
            $stmt = $this->pdo->prepare("SELECT * FROM users WHERE username = :username");
            $stmt->execute([':username' => $username]);
            $user = $stmt->fetch();
            
            if ($user && password_verify($password, $user['password'])) {
                return [
                    'success' => true,
                    'user' => [
                        'id' => $user['id'],
                        'username' => $user['username'],
                        'full_name' => $user['full_name'],
                        'department' => $user['department'],
                        'user_type' => $user['user_type']
                    ]
                ];
            } else {
                return [
                    'success' => false,
                    'message' => 'نام کاربری یا رمز عبور اشتباه است'
                ];
            }
        } catch(PDOException $e) {
            return [
                'success' => false,
                'message' => 'خطا در ورود به سیستم: ' . $e->getMessage()
            ];
        }
    }
    
    // ثبت نام کاربر جدید
    public function register($username, $password, $fullName, $department, $userType = 'user') {
        try {
            // بررسی وجود کاربر
            $stmt = $this->pdo->prepare("SELECT id FROM users WHERE username = :username");
            $stmt->execute([':username' => $username]);
            
            if ($stmt->fetch()) {
                return [
                    'success' => false,
                    'message' => 'نام کاربری already exists'
                ];
            }
            
            // ثبت کاربر جدید
            $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
            $stmt = $this->pdo->prepare("
                INSERT INTO users (username, password, full_name, department, user_type) 
                VALUES (:username, :password, :full_name, :department, :user_type)
            ");
            
            $stmt->execute([
                ':username' => $username,
                ':password' => $hashedPassword,
                ':full_name' => $fullName,
                ':department' => $department,
                ':user_type' => $userType
            ]);
            
            return [
                'success' => true,
                'message' => 'کاربر با موفقیت ثبت شد'
            ];
            
        } catch(PDOException $e) {
            return [
                'success' => false,
                'message' => 'خطا در ثبت نام: ' . $e->getMessage()
            ];
        }
    }
    
    // دریافت تمام کاربران
    public function getAllUsers() {
        try {
            $stmt = $this->pdo->prepare("
                SELECT id, username, full_name, department, user_type 
                FROM users 
                ORDER BY full_name
            ");
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch(PDOException $e) {
            return [];
        }
    }
    
    // جستجوی کاربران بر اساس نام
    public function searchUsersByName($search_term) {
        try {
            $stmt = $this->pdo->prepare("
                SELECT id, full_name, department, username 
                FROM users 
                WHERE full_name LIKE :search_term 
                AND user_type != 'admin'
                ORDER BY full_name 
                LIMIT 10
            ");
            
            $stmt->execute([':search_term' => '%' . $search_term . '%']);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
            
        } catch(PDOException $e) {
            error_log("خطا در جستجوی کاربران: " . $e->getMessage());
            return [];
        }
    }
}
?>