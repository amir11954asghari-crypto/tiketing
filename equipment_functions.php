<?php
// equipment_functions.php
require_once 'config.php';

class EquipmentFunctions {
    private $pdo;
    
    public function __construct() {
        $database = new Database();
        $this->pdo = $database->getConnection();
    }
    
    // ایجاد جدول تجهیزات در دیتابیس
    public function createEquipmentTable() {
        try {
            $sql = "
                CREATE TABLE IF NOT EXISTS equipment (
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
            $this->pdo->exec($sql);
            return true;
        } catch(PDOException $e) {
            error_log("خطا در ایجاد جدول تجهیزات: " . $e->getMessage());
            return false;
        }
    }
    
    // افزودن تجهیز جدید
    public function addEquipment($user_id, $equipment_type, $brand, $model, $serial_number, $cpu, $ram, $hdd) {
        try {
            // اول مطمئن شویم جدول وجود دارد
            $this->createEquipmentTable();
            
            $stmt = $this->pdo->prepare("
                INSERT INTO equipment (user_id, equipment_type, brand, model, serial_number, cpu, ram, hdd)
                VALUES (:user_id, :equipment_type, :brand, :model, :serial_number, :cpu, :ram, :hdd)
            ");
            
            $stmt->execute([
                ':user_id' => $user_id,
                ':equipment_type' => $equipment_type,
                ':brand' => $brand,
                ':model' => $model,
                ':serial_number' => $serial_number,
                ':cpu' => $cpu,
                ':ram' => $ram,
                ':hdd' => $hdd
            ]);
            
            return ['success' => true, 'message' => 'تجهیز با موفقیت افزوده شد'];
        } catch(PDOException $e) {
            error_log("خطا در افزودن تجهیز: " . $e->getMessage());
            return ['success' => false, 'message' => 'خطا در افزودن تجهیز: ' . $e->getMessage()];
        }
    }
    
    // دریافت تجهیزات یک کاربر
    public function getUserEquipment($user_id) {
        try {
            $this->createEquipmentTable(); // مطمئن شویم جدول وجود دارد
            
            $stmt = $this->pdo->prepare("
                SELECT * FROM equipment 
                WHERE user_id = :user_id 
                ORDER BY equipment_type, created_at DESC
            ");
            $stmt->execute([':user_id' => $user_id]);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch(PDOException $e) {
            error_log("خطا در دریافت تجهیزات کاربر: " . $e->getMessage());
            return [];
        }
    }
    
    // دریافت همه تجهیزات (برای ادمین)
    public function getAllEquipment() {
        try {
            $this->createEquipmentTable(); // مطمئن شویم جدول وجود دارد
            
            $stmt = $this->pdo->prepare("
                SELECT e.*, u.full_name, u.department 
                FROM equipment e 
                JOIN users u ON e.user_id = u.id 
                ORDER BY u.full_name, e.equipment_type
            ");
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch(PDOException $e) {
            error_log("خطا در دریافت همه تجهیزات: " . $e->getMessage());
            return [];
        }
    }
    

public function searchEquipmentByUserName($search_term) {
    try {
        $this->createEquipmentTable();
        
        $stmt = $this->pdo->prepare("
            SELECT e.*, u.full_name, u.department 
            FROM equipment e 
            JOIN users u ON e.user_id = u.id 
            WHERE u.full_name LIKE :search_term 
            ORDER BY u.full_name, e.equipment_type
        ");
        
        $stmt->execute([':search_term' => '%' . $search_term . '%']);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch(PDOException $e) {
        error_log("خطا در جستجوی تجهیزات: " . $e->getMessage());
        return [];
    }
}

// جستجوی تجهیزات بر اساس شماره سریال
public function searchEquipmentBySerial($search_term) {
    try {
        $this->createEquipmentTable();
        
        $stmt = $this->pdo->prepare("
            SELECT e.*, u.full_name, u.department 
            FROM equipment e 
            JOIN users u ON e.user_id = u.id 
            WHERE e.serial_number LIKE :search_term 
            ORDER BY u.full_name, e.equipment_type
        ");
        
        $stmt->execute([':search_term' => '%' . $search_term . '%']);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch(PDOException $e) {
        error_log("خطا در جستجوی تجهیزات: " . $e->getMessage());
        return [];
    }
}



    // حذف تجهیز
    public function deleteEquipment($equipment_id) {
        try {
            $this->createEquipmentTable(); // مطمئن شویم جدول وجود دارد
            
            $stmt = $this->pdo->prepare("DELETE FROM equipment WHERE id = :id");
            $stmt->execute([':id' => $equipment_id]);
            return ['success' => true, 'message' => 'تجهیز حذف شد'];
        } catch(PDOException $e) {
            error_log("خطا در حذف تجهیز: " . $e->getMessage());
            return ['success' => false, 'message' => 'خطا در حذف تجهیز: ' . $e->getMessage()];
        }
    }
}
?>