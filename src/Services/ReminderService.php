<?php
namespace App\Services;

use App\Config\Database;
use App\Services\NotificationService;

class ReminderService {
    private $db;
    private $notifier;

    public function __construct() {
        $this->db = new Database();
        $this->notifier = new NotificationService();
    }

    public function sendMealReminder($mealType) {
        $today = date('Y-m-d');
        $count = 0;
        
        // 1. Get all users
        $users = $this->db->conn->query("SELECT id, name, email FROM users");
        
        while ($user = $users->fetch_assoc()) {
            // 2. Check logs for specific meal type
            $stmt = $this->db->conn->prepare("
                SELECT id FROM nutrition_logs 
                WHERE user_id = ? AND date = ? AND meal_type = ?
            ");
            $stmt->bind_param("iss", $user['id'], $today, $mealType);
            $stmt->execute();
            
            if ($stmt->get_result()->num_rows === 0) {
                // 3. Send Reminder
                $subject = "Waktunya " . $mealType . "! 🍽️ Jangan Lupa Catat";
                $message = "
                    <h2>Halo " . htmlspecialchars($user['name']) . "! 👋</h2>
                    <p>Sudah waktunya <strong>" . $mealType . "</strong> nih!</p>
                    <p>Jangan lupa catat makananmu untuk menjaga konsistensi dietmu.</p>
                    <p>
                        <a href='http://localhost/yourproject/public/foods/create.php' style='background:#2563eb; color:white; padding:10px 20px; text-decoration:none; border-radius:5px;'>Catat " . $mealType . " Sekarang</a>
                    </p>
                    <p><small>Tetap semangat!<br>SmartHealthy Team</small></p>
                ";
                
                if ($this->notifier->sendEmail($user['email'], $subject, $message, false, true)) {
                    $count++;
                }
            }
        }
        
        return $count;
    }

    public function sendDailyReminders() {
        $today = date('Y-m-d');
        $count = 0;
        
        // 1. Get all users
        $users = $this->db->conn->query("SELECT id, name, email FROM users");
        
        while ($user = $users->fetch_assoc()) {
            // 2. Check if they have logged anything today
            $stmt = $this->db->conn->prepare("SELECT id FROM nutrition_logs WHERE user_id = ? AND date = ?");
            $stmt->bind_param("is", $user['id'], $today);
            $stmt->execute();
            $result = $stmt->get_result();
            
            if ($result->num_rows === 0) {
                // 3. Send Reminder
                $subject = "Jangan Lupa Catat Makananmu Hari Ini! 📝";
                $message = "
                    <h2>Halo " . htmlspecialchars($user['name']) . "! 👋</h2>
                    <p>Kami melihat Anda belum mencatat makanan hari ini.</p>
                    <p>Konsistensi adalah kunci untuk mencapai goal kesehatan Anda. Yuk, luangkan waktu 1 menit untuk update jurnal makananmu!</p>
                    <p>
                        <a href='http://localhost/yourproject/public/dashboard.php' style='background:#2563eb; color:white; padding:10px 20px; text-decoration:none; border-radius:5px;'>Buka Dashboard</a>
                    </p>
                    <p><small>Tetap semangat!<br>SmartHealthy Team</small></p>
                ";
                
                if ($this->notifier->sendEmail($user['email'], $subject, $message, false, true)) {
                    $count++;
                }
            }
        }
        
        return $count;
    }
}
