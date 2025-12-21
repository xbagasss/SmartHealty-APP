<?php
namespace App\Services;
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

class NotificationService {
    private $lastError = '';
    
    /**
     * Send email using Gmail SMTP
     * 
     * @param string $to Recipient email address
     * @param string $subject Email subject
     * @param string $message Email body
     * @param bool $debug Enable debug mode (default: false)
     * @param bool $isHtml Send as HTML (default: false)
     * @return bool Success status
     */
    public function sendEmail($to, $subject, $message, $debug = false, $isHtml = false){
        $mail = new PHPMailer(true);
        
        try {
            // Validate environment variables
            $smtpHost = getenv('SMTP_HOST');
            $smtpUser = getenv('SMTP_USER');
            $smtpPass = getenv('SMTP_PASS');
            
            if (!$smtpHost || !$smtpUser || !$smtpPass) {
                $this->lastError = 'Konfigurasi SMTP tidak lengkap. Periksa file .env';
                error_log('SMTP Config Error: Missing environment variables');
                return false;
            }
            
            if ($smtpUser === 'your@gmail.com' || $smtpPass === 'app_password_kamu' || $smtpPass === 'your_16_digit_app_password_here') {
                $this->lastError = 'SMTP masih menggunakan placeholder. Silakan konfigurasi email dan app password Gmail yang sebenarnya di file .env';
                error_log('SMTP Config Error: Using placeholder credentials');
                return false;
            }
            
            // Server settings
            if ($debug) {
                $mail->SMTPDebug = 2; // Enable verbose debug output
            }
            $mail->isSMTP();
            $mail->Host = $smtpHost;
            $mail->SMTPAuth = true;
            $mail->Username = $smtpUser;
            $mail->Password = $smtpPass;
            $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
            $mail->Port = 587;
            $mail->CharSet = 'UTF-8';
            
            // Recipients
            $mail->setFrom($smtpUser, 'SmartHealthy App');
            $mail->addAddress($to);
            $mail->addReplyTo($smtpUser, 'SmartHealthy App');
            
            // Content
            $mail->isHTML($isHtml);
            $mail->Subject = $subject;
            $mail->Body = $message;
            
            $result = $mail->send();
            $this->lastError = '';
            return $result;
            
        } catch (Exception $e) {
            $errorMsg = $mail->ErrorInfo ?: $e->getMessage();
            $this->lastError = $errorMsg;
            
            // Detailed error logging
            $logMessage = sprintf(
                "[%s] Mail Error: %s | Host: %s | User: %s | To: %s\n",
                date('Y-m-d H:i:s'),
                $mail->ErrorInfo,
                $smtpHost,
                $smtpUser,
                $to
            );
            
            // Log to file
            $logPath = __DIR__ . '/../../storage/logs/email_error.log';
            if (!is_dir(dirname($logPath))) mkdir(dirname($logPath), 0777, true);
            file_put_contents($logPath, $logMessage, FILE_APPEND);

            error_log('=== SMTP ERROR ===');
            error_log('Mail Error: ' . $mail->ErrorInfo);
            error_log('Exception: ' . $e->getMessage());
            error_log('SMTP Host: ' . $smtpHost);
            error_log('SMTP User: ' . $smtpUser);
            error_log('==================');
            
            return false;
        }
    }
    
    /**
     * Get last error message
     * 
     * @return string Last error message
     */
    public function getLastError() {
        return $this->lastError;
    }
}
