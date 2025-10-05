<?php
// create_admin.php
require_once 'config.php';

try {
    $database = new Database();
    $pdo = $database->getConnection();
    
    // ایجاد کاربر ادمین فناوری اطلاعات
    $hashedPassword = password_hash('admin123', PASSWORD_DEFAULT);
    $stmt = $pdo->prepare("
        INSERT INTO users (username, password, full_name, department, user_type) 
        VALUES (:username, :password, :full_name, :department, :user_type)
    ");
    
    $stmt->execute([
        ':username' => 'it_admin',
        ':password' => $hashedPassword,
        ':full_name' => 'ادمین فناوری اطلاعات',
        ':department' => 'فناوری اطلاعات',
        ':user_type' => 'admin'
    ]);
    
    echo "کاربر ادمین فناوری اطلاعات ایجاد شد:<br>";
    echo "نام کاربری: it_admin<br>";
    echo "رمز عبور: admin123<br>";
    echo '<a href="login.php">ورود به سیستم</a>';
    
} catch (PDOException $e) {
    echo "خطا: " . $e->getMessage();
}
?>