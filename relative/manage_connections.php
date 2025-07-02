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
$message = '';

// Handle cancellation of pending requests
if (isset($_GET['action']) && $_GET['action'] == 'cancel' && isset($_GET['id'])) {
    $connection_id = $_GET['id'];
    
    // Verify the connection belongs to this user
    $verify_stmt = $conn->prepare("SELECT id FROM relative_connections WHERE id = ? AND family_member_id = ?");
    $verify_stmt->bind_param("ii", $connection_id, $user_id);
    $verify_stmt->execute();
    $verify_result = $verify_stmt->get_result();
    
    if ($verify_result->num_rows > 0) {
        $delete_stmt = $conn->prepare("DELETE FROM relative_connections WHERE id = ?");
        $delete_stmt->bind_param("i", $connection_id);
        
        if ($delete_stmt->execute()) {
            $message = "Connection request cancelled successfully.";
        } else {
            $message = "Failed to cancel connection request. Please try again.";
        }
        
        $delete_stmt->close();
    }
    $verify_stmt->close();
}

// Get connections data
$approved_connections = get_elderly_relatives($user_id);
$pending_connections = get_pending_connection_requests($user_id);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Connections - Astha</title>
    
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
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h1>Manage Family Connections</h1>
            <a href="add_relative.php" class="btn btn-primary">
                <i class="fas fa-plus-circle me-2"></i>Add New Relative
            </a>
        </div>
        
        <?php if (!empty($message)): ?>
        <div class="alert alert-info" role="alert">
            <?php echo $message; ?>
        </div>
        <?php endif; ?>
        
        <!-- Connected Relatives -->
        <div class="card shadow-sm mb-4">
            <div class="card-header bg-white">
                <h2 class="h5 mb-0">Connected Relatives</h2>
            </div>
            <div class="card-body">
                <?php if (count($approved_connections) > 0): ?>
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th>Name</th>
                                    <th>Relationship</th>
                                    <th>Email</th>
                                    <th>Phone</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach($approved_connections as $relative): ?>
                                <tr>
                                    <td>
                                        <div class="d-flex align-items-center">
                                            <?php if (!empty($relative['profile_image'])): ?>
                                                <img src="../uploads/profiles/<?php echo $relative['profile_image']; ?>" 
                                                     alt="Profile" class="rounded-circle me-2" width="40" height="40">
                                            <?php else: ?>
                                                <div class="bg-primary rounded-circle me-2 d-flex align-items-center justify-content-center" 
                                                     style="width: 40px; height: 40px; color: white;">
                                                    <?php echo strtoupper(substr($relative['first_name'], 0, 1)); ?>
                                                </div>
                                            <?php endif; ?>
                                            <div>
                                                <?php echo htmlspecialchars($relative['first_name'] . ' ' . $relative['last_name']); ?>
                                            </div>
                                        </div>
                                    </td>
                                    <td><?php echo ucfirst(htmlspecialchars($relative['relationship'])); ?></td>
                                    <td><?php echo htmlspecialchars($relative['email']); ?></td>
                                    <td><?php echo htmlspecialchars($relative['phone']); ?></td>
                                    <td>
                                        <a href="relative_detail.php?id=<?php echo $relative['id']; ?>" class="btn btn-sm btn-outline-primary">
                                            <i class="fas fa-eye"></i> View Details
                                        </a>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php else: ?>
                    <div class="text-center py-4">
                        <i class="fas fa-user-friends fa-3x text-muted mb-3"></i>
                        <p class="mb-0">You don't have any connected relatives yet.</p>
                        <p class="text-muted">Add a relative using the button above.</p>
                    </div>
                <?php endif; ?>
            </div>
        </div>
        
        <!-- Pending Connection Requests -->
        <div class="card shadow-sm">
            <div class="card-header bg-white">
                <h2 class="h5 mb-0">Pending Connection Requests</h2>
            </div>
            <div class="card-body">
                <?php if (count($pending_connections) > 0): ?>
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th>Name</th>
                                    <th>Relationship</th>
                                    <th>Email</th>
                                    <th>Requested On</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach($pending_connections as $request): ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($request['first_name'] . ' ' . $request['last_name']); ?></td>
                                    <td><?php echo ucfirst(htmlspecialchars($request['relationship'])); ?></td>
                                    <td><?php echo htmlspecialchars($request['email']); ?></td>
                                    <td><?php echo date('M d, Y', strtotime($request['created_at'])); ?></td>
                                    <td>
                                        <a href="?action=cancel&id=<?php echo $request['connection_id']; ?>" 
                                           class="btn btn-sm btn-outline-danger"
                                           onclick="return confirm('Are you sure you want to cancel this connection request?')">
                                            <i class="fas fa-times"></i> Cancel Request
                                        </a>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php else: ?>
                    <div class="text-center py-4">
                        <i class="fas fa-clock fa-3x text-muted mb-3"></i>
                        <p class="mb-0">No pending connection requests.</p>
                    </div>
                <?php endif; ?>
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