<?php
/**
 * Send verification email with OTP using PHPMailer
 */
function sendVerificationEmail($email, $name, $otp) {
    // Include PHPMailer autoloader
    require '../PHPMailer/src/Exception.php';
    require '../PHPMailer/src/PHPMailer.php';
    require '../PHPMailer/src/SMTP.php';
    
    // Create PHPMailer instance
    $mail = new PHPMailer\PHPMailer\PHPMailer(true);
    
    try {
        // Server settings
        $mail->SMTPDebug = 0; // Set to 0 for production
        $mail->isSMTP();
        $mail->Host = 'smtp.gmail.com';
        $mail->SMTPAuth = true;
        $mail->Username = 'shohochor.ai@gmail.com'; 
        $mail->Password = 'vuet esfo cjob euee'; // App password
        $mail->SMTPSecure = PHPMailer\PHPMailer\PHPMailer::ENCRYPTION_SMTPS;
        $mail->Port = 465;
        
        // Sender and recipient
        $mail->setFrom('shohochor.ai@gmail.com', 'Shohochor');
        $mail->addAddress($email, $name);
        $mail->addReplyTo('shohochor.ai@gmail.com', 'Shohochor Support');
        
        // Email content
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
        $mail->send();
        return true;
    } catch (Exception $e) {
        // Log the error for debugging
        error_log("Mailer Error: " . $mail->ErrorInfo);
        return false;
    }
}