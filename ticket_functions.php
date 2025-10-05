<?php
// ticket_functions.php
require_once 'config.php';

class TicketFunctions {
    private $pdo;
    
    public function __construct() {
        $database = new Database();
        $this->pdo = $database->getConnection();
    }
    
    // ایجاد تیکت جدید
    public function createTicket($title, $category, $priority, $description, $userId, $equipment_id = null) {
        try {
            $stmt = $this->pdo->prepare(
                "INSERT INTO tickets (title, category, priority, description, user_id, equipment_id, status, created_at, updated_at) 
                 VALUES (:title, :category, :priority, :description, :user_id, :equipment_id, 'new', NOW(), NOW())"
            );
            
            $stmt->execute([
                ':title' => $title,
                ':category' => $category,
                ':priority' => $priority,
                ':description' => $description,
                ':user_id' => $userId,
                ':equipment_id' => $equipment_id
            ]);
            
            return ['success' => true, 'message' => 'تیکت با موفقیت ایجاد شد'];
            
        } catch(PDOException $e) {
            return ['success' => false, 'message' => 'خطا در ایجاد تیکت: ' . $e->getMessage()];
        }
    }

    // جستجوی تیکت‌ها بر اساس نام کاربر
    public function searchTicketsByUserName($user_name) {
        try {
            $stmt = $this->pdo->prepare("
                SELECT t.*, u.full_name as user_full_name, u.department as user_department 
                FROM tickets t 
                JOIN users u ON t.user_id = u.id 
                WHERE u.full_name LIKE :user_name 
                ORDER BY 
                    CASE 
                        WHEN priority = 'urgent' THEN 1
                        WHEN priority = 'high' THEN 2
                        WHEN priority = 'medium' THEN 3
                        ELSE 4
                    END,
                    t.created_at DESC
            ");
            $stmt->execute([':user_name' => '%' . $user_name . '%']);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
            
        } catch(PDOException $e) {
            return [];
        }
    }

    // دریافت تیکت‌های بر اساس وضعیت
    public function getTicketsByStatus($status) {
        try {
            $stmt = $this->pdo->prepare("
                SELECT t.*, u.full_name as user_full_name, u.department as user_department 
                FROM tickets t 
                JOIN users u ON t.user_id = u.id 
                WHERE t.status = :status 
                ORDER BY 
                    CASE 
                        WHEN priority = 'urgent' THEN 1
                        WHEN priority = 'high' THEN 2
                        WHEN priority = 'medium' THEN 3
                        ELSE 4
                    END,
                    t.created_at DESC
            ");
            $stmt->execute([':status' => $status]);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
            
        } catch(PDOException $e) {
            return [];
        }
    }

    // دریافت تیکت‌های یک کاربر خاص
    public function getUserTickets($userId) {
        try {
            $stmt = $this->pdo->prepare("
                SELECT t.*, u.full_name as user_full_name, u.department as user_department 
                FROM tickets t 
                LEFT JOIN users u ON t.user_id = u.id 
                WHERE t.user_id = :user_id 
                ORDER BY 
                    CASE 
                        WHEN priority = 'urgent' THEN 1
                        WHEN priority = 'high' THEN 2
                        WHEN priority = 'medium' THEN 3
                        ELSE 4
                    END,
                    created_at DESC
            ");
            $stmt->execute([':user_id' => $userId]);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
            
        } catch(PDOException $e) {
            return [];
        }
    }
    
    // دریافت تیکت‌های جدید (برای ادمین)
    public function getNewTickets() {
        try {
            $stmt = $this->pdo->prepare("
                SELECT t.*, u.full_name as user_full_name, u.department as user_department 
                FROM tickets t 
                JOIN users u ON t.user_id = u.id 
                WHERE t.status = 'new' 
                ORDER BY 
                    CASE 
                        WHEN priority = 'urgent' THEN 1
                        WHEN priority = 'high' THEN 2
                        WHEN priority = 'medium' THEN 3
                        ELSE 4
                    END,
                    t.created_at DESC
            ");
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
            
        } catch(PDOException $e) {
            return [];
        }
    }
    
    // دریافت همه تیکت‌ها (برای ادمین)
    public function getAllTickets() {
        try {
            $stmt = $this->pdo->prepare("
                SELECT t.*, u.full_name as user_full_name, u.department as user_department 
                FROM tickets t 
                JOIN users u ON t.user_id = u.id 
                ORDER BY 
                    CASE 
                        WHEN priority = 'urgent' THEN 1
                        WHEN priority = 'high' THEN 2
                        WHEN priority = 'medium' THEN 3
                        ELSE 4
                    END,
                    t.created_at DESC
            ");
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
            
        } catch(PDOException $e) {
            return [];
        }
    }
    
    // بروزرسانی وضعیت تیکت با ذخیره تاریخچه
    public function updateTicketStatus($ticket_id, $status, $admin_notes, $admin_id) {
        try {
            // شروع تراکنش
            $this->pdo->beginTransaction();
            
            // دریافت وضعیت قبلی
            $stmt = $this->pdo->prepare("SELECT status, admin_notes FROM tickets WHERE id = ?");
            $stmt->execute([$ticket_id]);
            $old_ticket = $stmt->fetch(PDO::FETCH_ASSOC);
            
            // بروزرسانی تیکت
            $stmt = $this->pdo->prepare("
                UPDATE tickets 
                SET status = ?, admin_notes = ?, updated_at = NOW(), admin_id = ?
                WHERE id = ?
            ");
            $stmt->execute([$status, $admin_notes, $admin_id, $ticket_id]);
            
            // ذخیره تاریخچه تغییرات
            $stmt = $this->pdo->prepare("
                INSERT INTO ticket_history (ticket_id, old_status, new_status, admin_notes, admin_id, changed_at)
                VALUES (?, ?, ?, ?, ?, NOW())
            ");
            $stmt->execute([$ticket_id, $old_ticket['status'], $status, $admin_notes, $admin_id]);
            
            // commit تراکنش
            $this->pdo->commit();
            
            return ['success' => true];
        } catch (PDOException $e) {
            // rollback در صورت خطا
            $this->pdo->rollBack();
            return ['success' => false, 'message' => 'خطا در بروزرسانی وضعیت تیکت: ' . $e->getMessage()];
        }
    }
    
    // دریافت تاریخچه تیکت
    public function getTicketHistory($ticket_id) {
        try {
            $stmt = $this->pdo->prepare("
                SELECT th.*, u.full_name as admin_name 
                FROM ticket_history th 
                LEFT JOIN users u ON th.admin_id = u.id 
                WHERE th.ticket_id = ? 
                ORDER BY th.changed_at DESC
            ");
            $stmt->execute([$ticket_id]);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch(PDOException $e) {
            return [];
        }
    }
    
    // دریافت اطلاعات یک تیکت خاص
    public function getTicket($ticketId) {
        try {
            $stmt = $this->pdo->prepare("
                SELECT t.*, u.full_name as user_full_name, u.department as user_department 
                FROM tickets t 
                JOIN users u ON t.user_id = u.id 
                WHERE t.id = :id
            ");
            $stmt->execute([':id' => $ticketId]);
            return $stmt->fetch(PDO::FETCH_ASSOC);
            
        } catch(PDOException $e) {
            return null;
        }
    }
}
?>