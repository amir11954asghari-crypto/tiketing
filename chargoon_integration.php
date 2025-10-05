<?php
// chargoon_integration.php
class ChargoonIntegration {
    private $api_base_url = "http://192.168.40.43"; // آدرس اپلیکیشن چارگون
    private $db_host = "192.168.40.42"; // آدرس دیتابیس چارگون
    private $db_user = "your_db_user"; // باید تنظیم شود
    private $db_pass = "your_db_password"; // باید تنظیم شود
    private $db_name = "chargoon_db"; // باید تنظیم شود
    
    // دریافت اطلاعات کاربر از چارگون
    public function getUserByPersonnelCode($personnel_code) {
        try {
            // روش 1: استفاده از API چارگون
            $url = $this->api_base_url . "/api/users/" . $personnel_code;
            $response = $this->callApi($url);
            
            if ($response && isset($response['success']) && $response['success']) {
                return $response['data'];
            }
            
            // روش 2: اتصال مستقیم به دیتابیس (اگر API در دسترس نبود)
            return $this->getUserFromDatabase($personnel_code);
            
        } catch (Exception $e) {
            error_log("خطا در دریافت اطلاعات از چارگون: " . $e->getMessage());
            return null;
        }
    }
    
    // فراخوانی API چارگون
    private function callApi($url) {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_TIMEOUT, 30);
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Content-Type: application/json',
            'Authorization: Bearer your_api_token' // باید تنظیم شود
        ]);
        
        $response = curl_exec($ch);
        $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);
        
        if ($http_code === 200) {
            return json_decode($response, true);
        }
        
        return null;
    }
    
    // دریافت اطلاعات از دیتابیس چارگون
    private function getUserFromDatabase($personnel_code) {
        try {
            $pdo = new PDO(
                "mysql:host={$this->db_host};dbname={$this->db_name};charset=utf8mb4",
                $this->db_user,
                $this->db_pass,
                [
                    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
                ]
            );
            
            // کوئری نمونه - باید بر اساس ساختار دیتابیس چارگون تنظیم شود
            $stmt = $pdo->prepare("
                SELECT 
                    personnel_code,
                    full_name,
                    department,
                    position,
                    email,
                    phone
                FROM employees 
                WHERE personnel_code = :personnel_code AND status = 'active'
            ");
            
            $stmt->execute([':personnel_code' => $personnel_code]);
            $user = $stmt->fetch();
            
            return $user ?: null;
            
        } catch (PDOException $e) {
            error_log("خطا در اتصال به دیتابیس چارگون: " . $e->getMessage());
            return null;
        }
    }
    
    // دریافت ساختار سازمانی
    public function getOrganizationalStructure() {
        try {
            $url = $this->api_base_url . "/api/organization/structure";
            $response = $this->callApi($url);
            
            if ($response && isset($response['success']) && $response['success']) {
                return $response['data'];
            }
            
            return $this->getOrgStructureFromDatabase();
            
        } catch (Exception $e) {
            error_log("خطا در دریافت ساختار سازمانی: " . $e->getMessage());
            return [];
        }
    }
    
    // دریافت ساختار سازمانی از دیتابیس
    private function getOrgStructureFromDatabase() {
        try {
            $pdo = new PDO(
                "mysql:host={$this->db_host};dbname={$this->db_name};charset=utf8mb4",
                $this->db_user,
                $this->db_pass
            );
            
            // کوئری نمونه - باید بر اساس ساختار دیتابیس چارگون تنظیم شود
            $stmt = $pdo->prepare("
                SELECT 
                    department_id,
                    department_name,
                    parent_department_id,
                    manager_id
                FROM departments 
                WHERE status = 'active'
                ORDER BY department_name
            ");
            
            $stmt->execute();
            return $stmt->fetchAll();
            
        } catch (PDOException $e) {
            error_log("خطا در دریافت ساختار سازمانی از دیتابیس: " . $e->getMessage());
            return [];
        }
    }
}
?>