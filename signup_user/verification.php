<?php
require_once __DIR__ . '/../PHPMailer-master/src/Exception.php';
require_once __DIR__ . '/../PHPMailer-master/src/PHPMailer.php';
require_once __DIR__ . '/../PHPMailer-master/src/SMTP.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use PHPMailer\PHPMailer\Exception;

/**
 * Send verification email with OTP using PHPMailer
 */
function sendVerificationEmail($email, $name, $otp) {
    try {
        // Check if PHPMailer files exist first
        $phpmailer_path = __DIR__ . '/../PHPMailer-master/src/PHPMailer.php';
        if (!file_exists($phpmailer_path)) {
            error_log("PHPMailer not found at: " . $phpmailer_path);
            file_put_contents(__DIR__ . '/email_error.log', date('Y-m-d H:i:s') . " - PHPMailer files not found at: $phpmailer_path\n", FILE_APPEND);
            return false;
        }
        
        // Create PHPMailer instance
        $mail = new PHPMailer(true);
        
        // Enable debugging for troubleshooting
        $mail->SMTPDebug = 2; // Change to 0 for production
        $mail->Debugoutput = function($str, $level) {
            file_put_contents(__DIR__ . '/email_error.log', date('Y-m-d H:i:s') . " - SMTP Debug: $str\n", FILE_APPEND);
        };
        
        // Server settings
        $mail->isSMTP();
        $mail->Host = 'smtp.gmail.com';
        $mail->SMTPAuth = true;
        $mail->Username = 'shohochor.ai@gmail.com';
        $mail->Password = 'fahh fgzi bmfp slbs'; 
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        $mail->Port = 587;
        
        // Set timeouts
        $mail->Timeout = 60;
        $mail->SMTPOptions = array(
            'ssl' => array(
                'verify_peer' => false,
                'verify_peer_name' => false,
                'allow_self_signed' => true
            )
        );
        
        // Recipients
        $mail->setFrom('astha.ltd.office@gmail.com', 'Shohochor');
        $mail->addAddress($email, $name);
        $mail->addReplyTo('astha.ltd.office@gmail.com', 'Shohochor Support');
        
        // Content
        $mail->isHTML(true);
        $mail->Subject = "Verify Your Shohochor Account";
        $mail->Body = "
        <div style='font-family: Arial, sans-serif; max-width: 600px; margin: 0 auto; padding: 20px;'>
            <div style='text-align: center; padding: 20px;'>
                <h1 style='color: #0096ad;'>Shohochor</h1>
            </div>
            <div style='background-color: #f5f7f9; padding: 20px; border-radius: 5px;'>
                <h2>Hello $name,</h2>
                <p>Thank you for registering with Shohochor. To complete your registration, please use the following verification code:</p>
                <div style='text-align: center; margin: 30px 0;'>
                    <div style='font-size: 24px; font-weight: bold; letter-spacing: 5px; padding: 15px; background-color: #ffffff; border: 1px solid #ddd; border-radius: 5px;'>$otp</div>
                </div>
                <p>This code will expire in 1 hour.</p>
                <p>If you didn't request this code, please ignore this email.</p>
            </div>
            <div style='text-align: center; margin-top: 20px; font-size: 12px; color: #666;'>
                <p>&copy; " . date('Y') . " Shohochor. All rights reserved.</p>
            </div>
        </div>";
        
        // Plain text version for non-HTML mail clients
        $mail->AltBody = "Hello $name, Your verification code is: $otp. This code will expire in 1 hour.";
        
        // Send email
        $result = $mail->send();
        
        if ($result) {
            // Log success
            error_log("Verification email sent successfully to: $email");
            file_put_contents(__DIR__ . '/email_log.txt', date('Y-m-d H:i:s') . " - Verification email sent to $email with OTP: $otp\n", FILE_APPEND);
            return true;
        } else {
            file_put_contents(__DIR__ . '/email_error.log', date('Y-m-d H:i:s') . " - Email send failed but no exception thrown\n", FILE_APPEND);
            return false;
        }
        
    } catch (Exception $e) {
        // Log the error for debugging
        $errorMessage = "Mailer Error: " . $e->getMessage();
        error_log($errorMessage);
        file_put_contents(__DIR__ . '/email_error.log', date('Y-m-d H:i:s') . " - $errorMessage\n", FILE_APPEND);
        
        // Also log SMTP-specific errors
        if (isset($mail) && $mail->ErrorInfo) {
            $smtpError = "SMTP Error: " . $mail->ErrorInfo;
            error_log($smtpError);
            file_put_contents(__DIR__ . '/email_error.log', date('Y-m-d H:i:s') . " - $smtpError\n", FILE_APPEND);
        }
        
        return false;
    }
}
?>