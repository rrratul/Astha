<?php
// Import PHPMailer classes - MUST BE AT TOP OF FILE
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use PHPMailer\PHPMailer\Exception;

// Include composer autoloader
require 'vendor/autoload.php';

// Proper headers for AMP form
header("Content-Type: application/json");
header("Access-Control-Allow-Origin: https://cdn.ampproject.org");
header("Access-Control-Allow-Credentials: true");
header("AMP-Access-Control-Allow-Source-Origin: http://" . $_SERVER['HTTP_HOST']);
header("Access-Control-Expose-Headers: AMP-Access-Control-Allow-Source-Origin");

// Debug logging
file_put_contents('form_debug.log', date('Y-m-d H:i:s') . " Request received\n", FILE_APPEND);
file_put_contents('form_debug.log', print_r($_POST, true) . "\n", FILE_APPEND);

// Initialize response
$response = array(
    'success' => false,
    'message' => ''
);

try {
    // Validate form submission
    if ($_SERVER["REQUEST_METHOD"] == "POST") {
        // Get form data
        $full_name = filter_input(INPUT_POST, 'full_name', FILTER_SANITIZE_FULL_SPECIAL_CHARS);
        $email = filter_input(INPUT_POST, 'email', FILTER_SANITIZE_EMAIL);
        $phone = filter_input(INPUT_POST, 'phone', FILTER_SANITIZE_FULL_SPECIAL_CHARS);
        $organization = filter_input(INPUT_POST, 'organization', FILTER_SANITIZE_FULL_SPECIAL_CHARS);
        $primary_need = filter_input(INPUT_POST, 'primary_need', FILTER_SANITIZE_FULL_SPECIAL_CHARS);
        $specific_needs = filter_input(INPUT_POST, 'specific_needs', FILTER_SANITIZE_FULL_SPECIAL_CHARS);
        $preferred_date = filter_input(INPUT_POST, 'preferred_date', FILTER_SANITIZE_FULL_SPECIAL_CHARS);
        $preferred_time = filter_input(INPUT_POST, 'preferred_time', FILTER_SANITIZE_FULL_SPECIAL_CHARS);
        $privacy_policy = isset($_POST['privacy_policy']) ? true : false;
        
        // Check required fields
        if (empty($full_name) || empty($email) || empty($phone) || empty($primary_need) || !$privacy_policy) {
            $response['message'] = 'Please fill in all required fields and agree to the privacy policy.';
        } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $response['message'] = 'Please enter a valid email address.';
        } else {
            // Save submission data first (backup)
            $submission_data = [
                'timestamp' => date('Y-m-d H:i:s'),
                'name' => $full_name,
                'email' => $email,
                'phone' => $phone,
                'organization' => $organization,
                'primary_need' => $primary_need,
                'specific_needs' => $specific_needs,
                'preferred_date' => $preferred_date,
                'preferred_time' => $preferred_time
            ];
            
            // Save as JSON for backup
            file_put_contents('consultation_submissions.json', json_encode($submission_data) . "\n", FILE_APPEND);
            
            // Create admin notification email
            $admin_mail = new PHPMailer(true);
            
            try {
                // Server settings
                $admin_mail->SMTPDebug = 0; // Set to 0 for production
                $admin_mail->isSMTP();
                $admin_mail->Host = 'smtp.gmail.com';
                $admin_mail->SMTPAuth = true;
                $admin_mail->Username = 'shohochor.ai@gmail.com'; 
                $admin_mail->Password = 'fahh fgzi bmfp slbs'; // App password
                $admin_mail->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS;
                $admin_mail->Port = 465;
                
                // Sender and recipient
                $admin_mail->setFrom('shohochor.ai@gmail.com', 'Shohochor Website');
                $admin_mail->addAddress('shohochor.ai@gmail.com', 'Shohochor Admin');
                $admin_mail->addReplyTo($email, $full_name);
                
                // Email content for admin
                $admin_mail->isHTML(true);
                $admin_mail->Subject = 'New Consultation Request from ' . $full_name;
                
                // Create HTML message for admin
                $admin_message = "
                <html>
                <head>
                    <title>New Consultation Request</title>
                    <style>
                        body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; }
                        .container { max-width: 600px; margin: 0 auto; padding: 20px; }
                        h1 { color: #0096ad; border-bottom: 1px solid #eee; padding-bottom: 10px; }
                        h2 { color: #4e5a60; margin-top: 20px; }
                        table { width: 100%; border-collapse: collapse; }
                        th, td { padding: 10px; text-align: left; border-bottom: 1px solid #eee; }
                        th { background-color: #f5f7f9; }
                        .footer { margin-top: 30px; font-size: 12px; color: #6c767f; border-top: 1px solid #eee; padding-top: 10px; }
                    </style>
                </head>
                <body>
                    <div class='container'>
                        <h1>New Consultation Request</h1>
                        <p>A new consultation request has been submitted through the website.</p>
                        
                        <h2>Contact Information</h2>
                        <table>
                            <tr>
                                <th>Full Name:</th>
                                <td>$full_name</td>
                            </tr>
                            <tr>
                                <th>Email:</th>
                                <td>$email</td>
                            </tr>
                            <tr>
                                <th>Phone:</th>
                                <td>$phone</td>
                            </tr>
                            <tr>
                                <th>Organization:</th>
                                <td>" . ($organization ?: 'N/A') . "</td>
                            </tr>
                        </table>
                        
                        <h2>Consultation Details</h2>
                        <table>
                            <tr>
                                <th>Primary Need:</th>
                                <td>$primary_need</td>
                            </tr>
                            <tr>
                                <th>Specific Needs:</th>
                                <td>" . ($specific_needs ?: 'None specified') . "</td>
                            </tr>
                            <tr>
                                <th>Preferred Date:</th>
                                <td>" . ($preferred_date ?: 'Flexible') . "</td>
                            </tr>
                            <tr>
                                <th>Preferred Time:</th>
                                <td>" . ($preferred_time ?: 'Flexible') . "</td>
                            </tr>
                        </table>
                        
                        <div class='footer'>
                            <p>This is an automated message from the Shohochor website.</p>
                            <p>Submission Time: " . date('Y-m-d H:i:s') . "</p>
                        </div>
                    </div>
                </body>
                </html>
                ";
                
                $admin_mail->Body = $admin_message;
                $admin_mail->AltBody = strip_tags(str_replace(['<br>', '</p>'], ["\n", "\n\n"], $admin_message));
                
                // Send admin email
                $admin_mail->send();
                file_put_contents('email_log.txt', date('Y-m-d H:i:s') . " - Admin notification sent\n", FILE_APPEND);
                
                // Create user confirmation email
                $user_mail = new PHPMailer(true);
                
                // Server settings (reuse same settings)
                $user_mail->SMTPDebug = 0;
                $user_mail->isSMTP();
                $user_mail->Host = 'smtp.gmail.com';
                $user_mail->SMTPAuth = true;
                $user_mail->Username = 'shohochor.ai@gmail.com';
                $user_mail->Password = 'fahh fgzi bmfp slbs'; // App password
                $user_mail->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS;
                $user_mail->Port = 465;
                
                // Set sender and recipient for user email
                $user_mail->setFrom('shohochor.ai@gmail.com', 'Shohochor Consultation Team');
                $user_mail->addAddress($email, $full_name);
                
                // Email content for user
                $user_mail->isHTML(true);
                $user_mail->Subject = 'Your Consultation Request - Shohochor Elderly Care';
                
                // Create HTML message for user
                $user_message = "
                <html>
                <head>
                    <title>Consultation Request Confirmation</title>
                    <style>
                        body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; }
                        .container { max-width: 600px; margin: 0 auto; padding: 20px; }
                        h1 { color: #0096ad; border-bottom: 1px solid #eee; padding-bottom: 10px; }
                        h2 { color: #4e5a60; margin-top: 20px; }
                        p { margin: 10px 0; }
                        .footer { margin-top: 30px; font-size: 12px; color: #6c767f; border-top: 1px solid #eee; padding-top: 10px; }
                    </style>
                </head>
                <body>
                    <div class='container'>
                        <h1>Thank You for Your Interest in Shohochor</h1>
                        <p>Dear $full_name,</p>
                        <p>Thank you for requesting a technology assessment consultation with Shohochor. We have received your request and a member of our team will contact you within 1 business day to schedule your consultation.</p>
                        
                        <h2>Your Request Details</h2>
                        <p><strong>Primary Need:</strong> $primary_need</p>
                        <p><strong>Preferred Date:</strong> " . ($preferred_date ?: 'Flexible') . "</p>
                        <p><strong>Preferred Time:</strong> " . ($preferred_time ?: 'Flexible') . "</p>
                        
                        <h2>What's Next?</h2>
                        <p>Our eldercare technology consultant will contact you to confirm your appointment time and gather any additional preliminary information to prepare for your session. Please feel free to prepare any specific questions or concerns you would like to discuss during your consultation.</p>
                        
                        <p>If you need immediate assistance, please contact us directly:</p>
                        <p><strong>Phone:</strong> + (880) 1685337528</p>
                        <p><strong>Email:</strong> shohochor.ai@gmail.com</p>
                        
                        <div class='footer'>
                            <p>This is an automated message from the Shohochor website. Please do not reply to this email.</p>
                            <p>Shohochor - Transforming Elderly Care Through Technology</p>
                        </div>
                    </div>
                </body>
                </html>
                ";
                
                $user_mail->Body = $user_message;
                $user_mail->AltBody = strip_tags(str_replace(['<br>', '</p>'], ["\n", "\n\n"], $user_message));
                
                // Send user email
                $user_mail->send();
                file_put_contents('email_log.txt', date('Y-m-d H:i:s') . " - User confirmation sent to $email\n", FILE_APPEND);
                
                // Set success response
                $response['success'] = true;
                $response['message'] = 'Thank you! Your consultation request has been submitted successfully. Our team will contact you within 1 business day.';
                
                // Record submission in log
                $log_entry = date('Y-m-d H:i:s') . " | Name: $full_name | Email: $email | Need: $primary_need\n";
                file_put_contents('consultation_requests.log', $log_entry, FILE_APPEND);
                
            } catch (Exception $e) {
                // Email failed, but we'll still return success since data was saved
                file_put_contents('email_error.log', date('Y-m-d H:i:s') . " - Mailer Error: " . $e->getMessage() . "\n", FILE_APPEND);
                
                // Set success response anyway (data is saved)
                $response['success'] = true;
                $response['message'] = 'Thank you! Your consultation request has been submitted successfully. Our team will contact you within 1 business day.';
            }
        }
    } else {
        $response['message'] = 'Invalid request method.';
    }
} catch (Exception $e) {
    // Log any unexpected errors
    file_put_contents('form_error.log', date('Y-m-d H:i:s') . " - Error: " . $e->getMessage() . "\n", FILE_APPEND);
    $response['message'] = 'An unexpected error occurred. Please try again or contact us directly at shohochor.ai@gmail.com';
}

// Return JSON response
echo json_encode($response);
exit;
?>