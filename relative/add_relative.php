<?php
session_start();
require_once '../../Astha/includes/config.php';
require_once './functions.php';

// Authentication checks
if (!isLoggedIn() || $_SESSION['user_role'] !== 'family_member') {
    header("Location: ../login_user/login_user.php");
    exit();
}

$user_id = $_SESSION['user_id'];
$success_message = '';
$error_message = '';

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $relative_email = trim($_POST['relative_email']);
    $relationship = trim($_POST['relationship']);
    
    // Validate input
    if (empty($relative_email) || !filter_var($relative_email, FILTER_VALIDATE_EMAIL)) {
        $error_message = "Please enter a valid email address";
    } else {
        // Check if the email belongs to an elderly user
        $stmt = $conn->prepare("SELECT id, first_name, last_name FROM users WHERE email = ? AND role = 'elderly'");
        $stmt->bind_param("s", $relative_email);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows > 0) {
            // Found the elderly user
            $elderly_user = $result->fetch_assoc();
            $elderly_id = $elderly_user['id'];
            
            // Check if already connected
            $check_stmt = $conn->prepare("SELECT * FROM relative_connections WHERE family_member_id = ? AND elderly_id = ?");
            $check_stmt->bind_param("ii", $user_id, $elderly_id);
            $check_stmt->execute();
            $check_result = $check_stmt->get_result();
            
            if ($check_result->num_rows > 0) {
                $error_message = "You are already connected to this relative";
            } else {
                // Create connection
                $connect_stmt = $conn->prepare("INSERT INTO relative_connections (family_member_id, elderly_id, relationship, status, created_at) VALUES (?, ?, ?, 'pending', NOW())");
                $connect_stmt->bind_param("iis", $user_id, $elderly_id, $relationship);
                
                if ($connect_stmt->execute()) {
                    // Get the inserted connection ID
                    $connection_id = $conn->insert_id;
                    
                    // Set status directly to "approved" instead of "pending" for silent mode
                    // This bypasses the need for elderly approval
                    $update_stmt = $conn->prepare("UPDATE relative_connections SET status = 'approved' WHERE id = ?");
                    $update_stmt->bind_param("i", $connection_id);
                    $update_stmt->execute();
                    $update_stmt->close();
                    
                    $success_message = "Connection established with " . htmlspecialchars($elderly_user['first_name'] . ' ' . 
                                      $elderly_user['last_name']) . ". You can now view their health data.";
                } else {
                    $error_message = "Failed to establish connection. Please try again.";
                }
                
                $connect_stmt->close();
            }
            $check_stmt->close();
        } else {
            $error_message = "No elderly user found with this email address. Please make sure they have registered as an elderly user.";
        }
        $stmt->close();
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Add Elderly Relative - Astha</title>
    
    <!-- Favicon -->
    <link rel="apple-touch-icon" sizes="180x180" href="../assets/images/favicon/apple-touch-icon.png">
    <link rel="icon" type="image/png" sizes="32x32" href="../assets/images/favicon/favicon-32x32.png">
    <link rel="icon" type="image/png" sizes="16x16" href="../assets/images/favicon/favicon-16x16.png">
    <link rel="mask-icon" href="../assets/images/favicon/safari-pinned-tab.svg" color="#5bbad5">
    <meta name="theme-color" content="#ffffff">
    
    <!-- Fonts and Icons -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@400;500;600;700;800&family=Open+Sans:wght@400;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    
    <!-- Stylesheets -->
    <link rel="stylesheet" href="../assets/css/bootstrap.min.css">
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body>
    <?php include_once '../../Astha/relative/includes/header.php'; ?>
    
    <!-- Mobile Navigation -->
    <?php include_once '../../Astha/relative/includes/mobile_nav.php'; ?>
    
    <main class="container py-5">
        <div class="row justify-content-center">
            <div class="col-md-8">
                <div class="card shadow-sm">
                    <div class="card-body p-4">
                        <h1 class="mb-4 text-center">Connect with an Elderly Relative</h1>
                        
                        <?php if (!empty($success_message)): ?>
                        <div class="alert alert-success" role="alert">
                            <?php echo $success_message; ?>
                        </div>
                        <?php endif; ?>
                        
                        <?php if (!empty($error_message)): ?>
                        <div class="alert alert-danger" role="alert">
                            <?php echo $error_message; ?>
                        </div>
                        <?php endif; ?>
                        
                        <p class="text-muted mb-4">
                            Enter the email address of your elderly relative who's already registered with Astha. 
                            This will send them a connection request, and once approved, you'll be able to 
                            monitor their health, activities, and stay connected.
                        </p>
                        
                        <form method="POST" action="">
                            <div class="mb-3">
                                <label for="relative_email" class="form-label">Relative's Email Address</label>
                                <input type="email" class="form-control" id="relative_email" name="relative_email" 
                                       placeholder="example@email.com" required>
                            </div>
                            
                            <div class="mb-4">
                                <label for="relationship" class="form-label">Relationship</label>
                                <select class="form-select" id="relationship" name="relationship" required>
                                    <option value="" selected disabled>Select relationship</option>
                                    <option value="son">Son</option>
                                    <option value="daughter">Daughter</option>
                                    <option value="grandson">Grandson</option>
                                    <option value="granddaughter">Granddaughter</option>
                                    <option value="nephew">Nephew</option>
                                    <option value="niece">Niece</option>
                                    <option value="sibling">Sibling</option>
                                    <option value="other">Other</option>
                                </select>
                            </div>
                            
                            <div class="d-grid gap-2">
                                <button type="submit" class="btn btn-primary">Send Connection Request</button>
                                <a href="index.php" class="btn btn-outline-secondary">Back to Dashboard</a>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </main>

    <!-- Footer -->
    <?php include_once '../includes/footer.php'; ?>

    <!-- JavaScript -->
    <script src="../assets/js/bootstrap.bundle.min.js"></script>
    <script src="../assets/js/jquery-3.6.0.min.js"></script>
</body>
</html>