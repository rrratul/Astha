<?php
/**
 * Newsletter Subscription Processing Script
 * 
 * Handles form submissions from the newsletter signup form
 * using AMP's action-xhr capability and notifies admin of new subscriptions
 */

// Import PHPMailer classes
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use PHPMailer\PHPMailer\Exception;

// Include composer autoloader at the top level
require 'vendor/autoload.php';

// Set headers for AMP compatibility
header("Content-Type: application/json");
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Headers: Content-Type, Content-Length, Accept-Encoding, X-CSRF-Token");
header("Access-Control-Allow-Methods: POST, OPTIONS");
header("Access-Control-Allow-Credentials: true");

// Debug logging
file_put_contents('newsletter_debug.log', date('Y-m-d H:i:s') . " Newsletter request received\n", FILE_APPEND);
file_put_contents('newsletter_debug.log', print_r($_POST, true) . "\n", FILE_APPEND);

// Handle OPTIONS request for CORS preflight
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    exit(0);
}

// Make sure this is a POST request
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405); // Method Not Allowed
    echo json_encode(['message' => 'Only POST requests are allowed']);
    exit;
}

// Get and validate email
$email = isset($_POST['email']) ? trim($_POST['email']) : '';

if (empty($email)) {
    http_response_code(400);
    echo json_encode(['message' => 'Email address is required']);
    exit;
}

if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    http_response_code(400);
    echo json_encode(['message' => 'Please enter a valid email address']);
    exit;
}

// Process the subscription
try {
    // Simple file storage for newsletter subscribers
    $file = 'newsletter_subscribers.txt';
    $current_time = date('Y-m-d H:i:s');
    $data = $email . ',' . $current_time . PHP_EOL;
    
    // Check if email already exists in our simple storage
    $is_duplicate = false;
    if (file_exists($file)) {
        $contents = file_get_contents($file);
        if (strpos($contents, $email . ',') !== false) {
            $is_duplicate = true;
        }
    }
    
    if ($is_duplicate) {
        http_response_code(200);
        echo json_encode([
            'message' => 'You\'re already subscribed to our newsletter!',
            'already' => true
        ]);
        exit;
    }
    
    // Save the new email
    $success = file_put_contents($file, $data, FILE_APPEND | LOCK_EX) !== false;
    
    if ($success) {
        // Log that we're about to send email
        file_put_contents('newsletter_debug.log', date('Y-m-d H:i:s') . " About to send notification email for: $email\n", FILE_APPEND);
        
        // Send notification email to admin using PHPMailer
        $mail = new PHPMailer(true);
        
        try {
            // Server settings - IDENTICAL to process_consultation.php
            $mail->SMTPDebug = 0; // Set to 0 for production
            $mail->isSMTP();
            $mail->Host = 'smtp.gmail.com';
            $mail->SMTPAuth = true;
            $mail->Username = 'shohochor.ai@gmail.com';
            $mail->Password = 'fahh fgzi bmfp slbs';
            $mail->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS;
            $mail->Port = 465;
            
            // Recipients
            $mail->setFrom('shohochor.ai@gmail.com', 'Shohochor Newsletter');
            $mail->addAddress('shohochor.ai@gmail.com', 'Admin');
            $mail->addReplyTo($email, 'Newsletter Subscriber');
            
            // Content
            $mail->isHTML(true);
            $mail->Subject = 'New Newsletter Subscription';
            $mail->Body = '
                <html>
                <head>
                    <title>New Newsletter Subscription</title>
                    <style>
                        body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; }
                        .container { max-width: 600px; margin: 0 auto; padding: 20px; }
                        h1 { color: #0096ad; border-bottom: 1px solid #eee; padding-bottom: 10px; }
                        .footer { margin-top: 30px; font-size: 12px; color: #6c767f; border-top: 1px solid #eee; padding-top: 10px; }
                    </style>
                </head>
                <body>
                    <div class="container">
                        <h1>New Newsletter Subscription</h1>
                        <p>A new user has subscribed to the Shohochor newsletter.</p>
                        <p><strong>Email:</strong> ' . htmlspecialchars($email) . '</p>
                        <p><strong>Date:</strong> ' . $current_time . '</p>
                        <p>This is an automated message from your website.</p>
                        
                        <div class="footer">
                            <p>Submission Time: ' . date('Y-m-d H:i:s') . '</p>
                        </div>
                    </div>
                </body>
                </html>
            ';
            $mail->AltBody = "New Newsletter Subscription\n\n" .
                           "A new user has subscribed to the Shohochor newsletter.\n" .
                           "Email: $email\n" .
                           "Date: $current_time\n\n" .
                           "This is an automated message from your website.";
            
            // Send the email and check for errors
            if (!$mail->send()) {
                throw new Exception("Admin Mailer Error: " . $mail->ErrorInfo);
            }
            
            // Log successful send for admin email
            file_put_contents('email_log.txt', date('Y-m-d H:i:s') . " - Admin notification sent for new subscriber: $email\n", FILE_APPEND);
            
            // Now send a welcome email to the subscriber
            $subscriberMail = new PHPMailer(true);
            
            // Use same SMTP settings
            $subscriberMail->SMTPDebug = 0;
            $subscriberMail->isSMTP();
            $subscriberMail->Host = 'smtp.gmail.com';
            $subscriberMail->SMTPAuth = true;
            $subscriberMail->Username = 'shohochor.ai@gmail.com';
            $subscriberMail->Password = 'fahh fgzi bmfp slbs';
            $subscriberMail->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS;
            $subscriberMail->Port = 465;
            
            // Set recipient to subscriber
            $subscriberMail->setFrom('shohochor.ai@gmail.com', 'Shohochor Newsletter');
            $subscriberMail->addAddress($email); // Add subscriber as recipient
            
            // Content for subscriber
            $subscriberMail->isHTML(true);
            $subscriberMail->Subject = 'Welcome to Shohochor Newsletter';
            $subscriberMail->Body = '
                <html>
                <head>
                    <title>Welcome to Shohochor Newsletter</title>
                    <style>
                        body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; }
                        .container { max-width: 600px; margin: 0 auto; padding: 20px; }
                        h1 { color: #0096ad; border-bottom: 1px solid #eee; padding-bottom: 10px; }
                        .highlight { background-color: #f0f9ff; padding: 15px; border-left: 4px solid #0096ad; margin: 20px 0; }
                        .footer { margin-top: 30px; font-size: 12px; color: #6c767f; border-top: 1px solid #eee; padding-top: 10px; }
                        .button { display: inline-block; background-color: #0096ad; color: white; padding: 12px 25px; 
                                  text-decoration: none; border-radius: 4px; margin-top: 20px; }
                    </style>
                </head>
                <body>
                    <div class="container">
                        <h1>Welcome to the Shohochor Newsletter!</h1>
                        <p>Thank you for subscribing to our newsletter. You will now receive updates on our latest innovations, research, and eldercare insights.</p>
                        
                        <div class="highlight">
                            <p>You\'ve joined our growing community of professionals, caregivers, and individuals dedicated to transforming eldercare through technology.</p>
                        </div>
                        
                        <p>Here\'s what you can expect in your inbox:</p>
                        <ul>
                            <li>Latest advancements in eldercare technology</li>
                            <li>Research findings and clinical insights</li>
                            <li>Best practices for implementing senior-friendly technology</li>
                            <li>Expert interviews and success stories</li>
                        </ul>
                        
                        <p>If you have any questions or specific topics you\'d like us to cover, feel free to contact us at support@shohochor.com.</p>
                        
                        <a href="https://shohochor.com" class="button">Visit Our Website</a>
                        
                        <div class="footer">
                            <p>© ' . date('Y') . ' Shohochor. All rights reserved.</p>
                            <p>You\'re receiving this email because you signed up for the Shohochor newsletter.</p>
                            <p>To unsubscribe, please <a href="mailto:unsubscribe@shohochor.com?subject=Unsubscribe&body=Please unsubscribe me from the newsletter">click here</a>.</p>
                        </div>
                    </div>
                </body>
                </html>
            ';
            $subscriberMail->AltBody = "Welcome to the Shohochor Newsletter!\n\n" .
                           "Thank you for subscribing to our newsletter. You will now receive updates on our latest innovations, research, and eldercare insights.\n\n" .
                           "You've joined our growing community of professionals, caregivers, and individuals dedicated to transforming eldercare through technology.\n\n" .
                           "Here's what you can expect in your inbox:\n" .
                           "- Latest advancements in eldercare technology\n" .
                           "- Research findings and clinical insights\n" .
                           "- Best practices for implementing senior-friendly technology\n" .
                           "- Expert interviews and success stories\n\n" .
                           "If you have any questions, feel free to contact us at support@shohochor.com.\n\n" .
                           "© " . date('Y') . " Shohochor. All rights reserved.";
            
            // Send the subscriber email
            if (!$subscriberMail->send()) {
                throw new Exception("Subscriber Mailer Error: " . $subscriberMail->ErrorInfo);
            }
            
            // Log successful subscriber email
            file_put_contents('email_log.txt', date('Y-m-d H:i:s') . " - Welcome email sent to subscriber: $email\n", FILE_APPEND);
            
        } catch (Exception $e) {
            // Log email sending error but continue execution
            file_put_contents('email_error.log', date('Y-m-d H:i:s') . " - Error sending newsletter notification: " . $e->getMessage() . "\n", FILE_APPEND);
        }
        
        http_response_code(200);
        echo json_encode(['message' => 'Thank you for subscribing to our newsletter!']);
    } else {
        throw new Exception("Failed to save subscription");
    }
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['message' => 'Sorry, we could not process your subscription. Please try again later.']);
    file_put_contents('newsletter_error.log', date('Y-m-d H:i:s') . " - Error: " . $e->getMessage() . "\n", FILE_APPEND);
}
?>