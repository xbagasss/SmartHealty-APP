<?php
namespace App\Services;

use App\Config\Database;

use App\Services\NotificationService;

class AuthService {

    private $db;
    private $notifier;

    public function __construct(Database $db){
        $this->db = $db;
        $this->notifier = new NotificationService();
    }

    public function register($name, $email, $password){
        $check = $this->db->conn->prepare("SELECT id FROM users WHERE email=?");
        $check->bind_param("s", $email);
        $check->execute();

        if ($check->get_result()->fetch_assoc()) {
            return false;
        }

        $hash = password_hash($password, PASSWORD_DEFAULT);
        
        // Generate OTP
        $otp = sprintf("%06d", mt_rand(1, 999999));
        $expiry = date("Y-m-d H:i:s", strtotime("+15 minutes"));

        $stmt = $this->db->conn->prepare(
            "INSERT INTO users (name, email, password, role, is_verified, otp_code, otp_expires_at) 
             VALUES (?, ?, ?, 'user', 0, ?, ?)"
        );
        $stmt->bind_param("sssss", $name, $email, $hash, $otp, $expiry);

        if ($stmt->execute()) {
            // Send Email
            $this->sendOtpEmail($email, $name, $otp);
            return true;
        }
        return false;
    }

    public function login($email, $password){
        $stmt = $this->db->conn->prepare("SELECT * FROM users WHERE email=?");
        $stmt->bind_param("s", $email);
        $stmt->execute();

        $user = $stmt->get_result()->fetch_assoc();

        if ($user && password_verify($password, $user['password'])) {
            if ($user['is_verified'] == 0) {
                return 'unverified';
            }
            unset($user['password']); // keamanan
            return $user;
        }

        return false;
    }

    public function verifyOtp($email, $otp) {
        $stmt = $this->db->conn->prepare("SELECT id, otp_code, otp_expires_at FROM users WHERE email = ?");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $user = $stmt->get_result()->fetch_assoc();

        if (!$user) return 'User not found';

        if ($user['otp_code'] !== $otp) {
            return 'Invalid OTP';
        }

        if (strtotime($user['otp_expires_at']) < time()) {
            return 'OTP Expired';
        }

        // Success
        $update = $this->db->conn->prepare("UPDATE users SET is_verified = 1, otp_code = NULL, otp_expires_at = NULL WHERE id = ?");
        $update->bind_param("i", $user['id']);
        return $update->execute() ? true : 'Database Error';
    }

    public function resendOtp($email) {
        $stmt = $this->db->conn->prepare("SELECT id, name FROM users WHERE email = ?");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $user = $stmt->get_result()->fetch_assoc();

        if (!$user) return false;

        $otp = sprintf("%06d", mt_rand(1, 999999));
        $expiry = date("Y-m-d H:i:s", strtotime("+15 minutes"));

        $update = $this->db->conn->prepare("UPDATE users SET otp_code = ?, otp_expires_at = ? WHERE id = ?");
        $update->bind_param("ssi", $otp, $expiry, $user['id']);
        
        if ($update->execute()) {
            $this->sendOtpEmail($email, $user['name'], $otp);
            return true;
        }
        return false;
    }

    private function sendOtpEmail($email, $name, $otp) {
        $subject = "Kode Verifikasi Akun SmartHealthy Anda";
        $message = "
            <h2>Halo $name! 👋</h2>
            <p>Terima kasih telah mendaftar di SmartHealthy.</p>
            <p>Gunakan kode OTP berikut untuk memverifikasi akun Anda:</p>
            <h1 style='color:#10b981; letter-spacing: 5px;'>$otp</h1>
            <p>Kode ini berlaku selama 15 menit.</p>
        ";
        return $this->notifier->sendEmail($email, $subject, $message, false, true);
    }
}
