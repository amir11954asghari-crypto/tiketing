<?php
// reset_equipment_table.php
require_once 'config.php';

try {
    $database = new Database();
    $pdo = $database->getConnection();
    
    // حذف جدول اگر وجود دارد
    $pdo->exec("DROP TABLE IF EXISTS equipment");
    echo "جدول equipment حذف شد<br>";
    
    // ایجاد جدول جدید با ON DELETE CASCADE
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
    echo "جدول equipment با موفقیت ایجاد شد<br>";
    
    // نمایش اطلاعات جدول
    echo "<br>ساختار جدول equipment:<br>";
    $stmt = $pdo->query("DESCRIBE equipment");
    $columns = $stmt->fetchAll(PDO::FETCH_ASSOC);
    echo "<pre>";
    print_r($columns);
    echo "</pre>";
    
} catch(PDOException $e) {
    echo "خطا: " . $e->getMessage();
}
?>