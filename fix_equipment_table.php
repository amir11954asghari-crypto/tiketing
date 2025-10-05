<?php
// fix_equipment_table.php
require_once 'config.php';

try {
    $database = new Database();
    $pdo = $database->getConnection();
    
    // بررسی وجود جدول
    $table_exists = $pdo->query("SHOW TABLES LIKE 'equipment'")->rowCount() > 0;
    
    if ($table_exists) {
        echo "جدول equipment وجود دارد. بررسی ستون‌ها...<br>";
        
        // بررسی ستون‌های موجود
        $stmt = $pdo->query("DESCRIBE equipment");
        $columns = $stmt->fetchAll(PDO::FETCH_COLUMN);
        echo "ستون‌های موجود: " . implode(', ', $columns) . "<br>";
        
        // اضافه کردن ستون‌های گمشده
        $missing_columns = [];
        
        if (!in_array('cpu', $columns)) {
            $pdo->exec("ALTER TABLE equipment ADD COLUMN cpu VARCHAR(100) AFTER serial_number");
            $missing_columns[] = 'cpu';
        }
        
        if (!in_array('ram', $columns)) {
            $pdo->exec("ALTER TABLE equipment ADD COLUMN ram VARCHAR(50) AFTER cpu");
            $missing_columns[] = 'ram';
        }
        
        if (!in_array('hdd', $columns)) {
            $pdo->exec("ALTER TABLE equipment ADD COLUMN hdd VARCHAR(100) AFTER ram");
            $missing_columns[] = 'hdd';
        }
        
        if (!empty($missing_columns)) {
            echo "ستون‌های اضافه شده: " . implode(', ', $missing_columns) . "<br>";
        } else {
            echo "همه ستون‌ها موجود هستند.<br>";
        }
        
    } else {
        echo "جدول equipment وجود ندارد. ایجاد جدول جدید...<br>";
        
        // ایجاد جدول جدید
        $sql = "
            CREATE TABLE equipment (
                id INT AUTO_INCREMENT PRIMARY KEY,
                user_id INT NOT NULL,
                equipment_type ENUM('desktop', 'laptop', 'surface', 'monitor', 'printer', 'scanner', 'other') NOT NULL,
                brand VARCHAR(100),
                model VARCHAR(100),
                serial_number VARCHAR(100),
                cpu VARCHAR(100),
                ram VARCHAR(50),
                hdd VARCHAR(100),
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
            )
        ";
        $pdo->exec($sql);
        echo "جدول equipment با موفقیت ایجاد شد.<br>";
    }
    
    // نمایش ساختار نهایی جدول
    echo "<br>ساختار نهایی جدول equipment:<br>";
    $stmt = $pdo->query("DESCRIBE equipment");
    $columns = $stmt->fetchAll(PDO::FETCH_ASSOC);
    echo "<pre>";
    print_r($columns);
    echo "</pre>";
    
    echo "<br><strong>✅ مشکل حل شد! حالا می‌توانید تجهیزات را اضافه کنید.</strong>";
    
} catch(PDOException $e) {
    echo "خطا: " . $e->getMessage();
}
?>