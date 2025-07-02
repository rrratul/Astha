<?php
// filepath: c:\xampp\htdocs\Astha\relative\relative_detail.php

session_start();
require_once '../../Astha/includes/config.php';
require_once './functions.php';

// Authentication checks
if (!isLoggedIn() || $_SESSION['user_role'] !== 'family_member') {
    header("Location: ../login_user/login_user.php");
    exit();
}

$user_id = $_SESSION['user_id'];

// Check if relative ID is provided
if (!isset($_GET['id'])) {
    header("Location: manage_connections.php");
    exit();
}

$relative_id = $_GET['id'];

// Verify if this relative is connected to the current user
$stmt = $conn->prepare("
    SELECT u.*, rc.relationship 
    FROM users u
    JOIN relative_connections rc ON u.id = rc.elderly_id
    WHERE u.id = ? AND rc.family_member_id = ? AND rc.status = 'approved'
");
$stmt->bind_param("ii", $relative_id, $user_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    // Not authorized to view this relative or relative doesn't exist
    header("Location: manage_connections.php");
    exit();
}

$relative = $result->fetch_assoc();
$stmt->close();

// Get health metrics
$health_stmt = $conn->prepare("
    SELECT * FROM health_metrics 
    WHERE user_id = ? 
    ORDER BY recorded_at DESC 
    LIMIT 10
");
$health_stmt->bind_param("i", $relative_id);
$health_stmt->execute();
$health_metrics = $health_stmt->get_result();
$health_stmt->close();

// Get recent appointments
$apt_stmt = $conn->prepare("
    SELECT * FROM appointments 
    WHERE user_id = ? 
    ORDER BY date DESC, time DESC 
    LIMIT 5
");
$apt_stmt->bind_param("i", $relative_id);
$apt_stmt->execute();
$appointments = $apt_stmt->get_result();
$apt_stmt->close();

// Get recent activities
$act_stmt = $conn->prepare("
    SELECT * FROM user_activities 
    WHERE user_id = ? 
    ORDER BY activity_date DESC, activity_time DESC 
    LIMIT 10
");
$act_stmt->bind_param("i", $relative_id);
$act_stmt->execute();
$activities = $act_stmt->get_result();
$act_stmt->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($relative['first_name'] . ' ' . $relative['last_name']); ?> - Astha</title>
    
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
        <div class="row">
            <div class="col-lg-4 mb-4">
                <!-- Profile Card -->
                <div class="card shadow-sm mb-4">
                    <div class="card-body text-center p-4">
                        <div class="mb-3">
                            <?php if (!empty($relative['profile_image'])): ?>
                                <img src="../uploads/profiles/<?php echo $relative['profile_image']; ?>" 
                                     alt="<?php echo htmlspecialchars($relative['first_name']); ?>" 
                                     class="rounded-circle" width="120" height="120">
                            <?php else: ?>
                                <div class="bg-primary rounded-circle mx-auto d-flex align-items-center justify-content-center" 
                                     style="width: 120px; height: 120px; font-size: 48px; color: white;">
                                    <?php echo strtoupper(substr($relative['first_name'], 0, 1)); ?>
                                </div>
                            <?php endif; ?>
                        </div>
                        
                        <h2 class="h4 mb-1"><?php echo htmlspecialchars($relative['first_name'] . ' ' . $relative['last_name']); ?></h2>
                        <p class="text-muted mb-3">Your <?php echo htmlspecialchars($relative['relationship']); ?></p>
                        
                        <div class="d-grid gap-2 mb-4">
                            <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#videoCallModal">
                                <i class="fas fa-video me-2"></i>Start Video Call
                            </button>
                            <button class="btn btn-outline-primary" data-bs-toggle="modal" data-bs-target="#messageModal">
                                <i class="fas fa-comment-dots me-2"></i>Send Message
                            </button>
                        </div>
                        
                        <div class="border-top pt-3">
                            <div class="row g-0 text-start mb-2">
                                <div class="col-1 text-center">
                                    <i class="fas fa-envelope text-muted"></i>
                                </div>
                                <div class="col-11 ps-3">
                                    <?php echo htmlspecialchars($relative['email']); ?>
                                </div>
                            </div>
                            
                            <?php if (!empty($relative['phone'])): ?>
                            <div class="row g-0 text-start mb-2">
                                <div class="col-1 text-center">
                                    <i class="fas fa-phone text-muted"></i>
                                </div>
                                <div class="col-11 ps-3">
                                    <?php echo htmlspecialchars($relative['phone']); ?>
                                </div>
                            </div>
                            <?php endif; ?>
                            
                            <?php if (!empty($relative['address'])): ?>
                            <div class="row g-0 text-start mb-2">
                                <div class="col-1 text-center">
                                    <i class="fas fa-map-marker-alt text-muted"></i>
                                </div>
                                <div class="col-11 ps-3">
                                    <?php echo htmlspecialchars($relative['address']); ?>
                                </div>
                            </div>
                            <?php endif; ?>
                            
                            <div class="row g-0 text-start">
                                <div class="col-1 text-center">
                                    <i class="fas fa-calendar-alt text-muted"></i>
                                </div>
                                <div class="col-11 ps-3">
                                    Member since <?php echo date('M Y', strtotime($relative['created_at'])); ?>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Emergency Contacts Card -->
                <div class="card shadow-sm">
                    <div class="card-header bg-white">
                        <h3 class="h5 mb-0">Emergency Contacts</h3>
                    </div>
                    <div class="card-body">
                        <!-- Fetch and display emergency contacts if available -->
                        <p class="text-muted text-center my-3">Emergency contacts will appear here.</p>
                        <div class="d-grid">
                            <a href="emergency_protocol.php?relative_id=<?php echo $relative_id; ?>" class="btn btn-sm btn-outline-danger">
                                View Emergency Protocol
                            </a>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="col-lg-8">
                <!-- Health Metrics Card -->
                <div class="card shadow-sm mb-4">
                    <div class="card-header bg-white d-flex justify-content-between align-items-center">
                        <h3 class="h5 mb-0">Health Metrics</h3>
                    </div>
                    <div class="card-body">
                        <?php 
                        $health_metrics = get_relative_health_metrics($relative_id);
                        if (count($health_metrics) > 0): ?>
                            <div class="table-responsive">
                                <table class="table table-hover">
                                    <thead>
                                        <tr>
                                            <th>Date</th>
                                            <th>Blood Pressure</th>
                                            <th>Heart Rate</th>
                                            <th>Blood Sugar</th>
                                            <th>Oxygen Level</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($health_metrics as $metric): ?>
                                        <tr>
                                            <td><?php echo date('M d, Y', strtotime($metric['recorded_at'])); ?></td>
                                            <td><?php echo isset($metric['systolic']) && isset($metric['diastolic']) ? 
                                                      $metric['systolic'] . '/' . $metric['diastolic'] : 'N/A'; ?></td>
                                            <td><?php echo isset($metric['heart_rate']) ? $metric['heart_rate'] . ' bpm' : 'N/A'; ?></td>
                                            <td><?php echo isset($metric['blood_sugar']) ? $metric['blood_sugar'] . ' mg/dL' : 'N/A'; ?></td>
                                            <td><?php echo isset($metric['oxygen_level']) ? $metric['oxygen_level'] . '%' : 'N/A'; ?></td>
                                        </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                        <?php else: ?>
                            <div class="text-center py-4">
                                <i class="fas fa-heartbeat fa-3x text-muted mb-3"></i>
                                <p class="mb-0">No health metrics recorded yet.</p>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
                
                <!-- Upcoming Appointments Card -->
                <div class="card shadow-sm mb-4">
                    <div class="card-header bg-white d-flex justify-content-between align-items-center">
                        <h3 class="h5 mb-0">Upcoming Appointments</h3>
                        <a href="appointments.php?relative_id=<?php echo $relative_id; ?>" class="btn btn-sm btn-outline-primary">
                            View All Appointments
                        </a>
                    </div>
                    <div class="card-body">
                        <?php if ($appointments->num_rows > 0): ?>
                            <div class="list-group list-group-flush">
                                <?php while ($appointment = $appointments->fetch_assoc()): ?>
                                    <?php if (strtotime($appointment['date']) >= strtotime('today')): ?>
                                    <div class="list-group-item px-0">
                                        <div class="d-flex align-items-center">
                                            <div class="calendar-date me-3">
                                                <div class="calendar-month"><?php echo date('M', strtotime($appointment['date'])); ?></div>
                                                <div class="calendar-day"><?php echo date('d', strtotime($appointment['date'])); ?></div>
                                            </div>
                                            <div class="flex-grow-1">
                                                <h4 class="h6 mb-1"><?php echo htmlspecialchars($appointment['title']); ?></h4>
                                                <p class="text-muted mb-1">
                                                    <i class="far fa-clock me-1"></i>
                                                    <?php echo date('h:i A', strtotime($appointment['time'])); ?>
                                                    (<?php echo $appointment['duration']; ?> min)
                                                </p>
                                                <?php if (!empty($appointment['doctor_name'])): ?>
                                                <p class="text-muted mb-0">
                                                    <i class="fas fa-user-md me-1"></i>
                                                    <?php echo htmlspecialchars($appointment['doctor_name']); ?>
                                                </p>
                                                <?php endif; ?>
                                            </div>
                                            <div class="ms-auto">
                                                <span class="badge bg-primary"><?php echo ucfirst($appointment['status']); ?></span>
                                            </div>
                                        </div>
                                    </div>
                                    <?php endif; ?>
                                <?php endwhile; ?>
                            </div>
                        <?php else: ?>
                            <div class="text-center py-4">
                                <i class="fas fa-calendar-check fa-3x text-muted mb-3"></i>
                                <p class="mb-0">No upcoming appointments.</p>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
                
                <!-- Recent Activities Card -->
                <div class="card shadow-sm">
                    <div class="card-header bg-white d-flex justify-content-between align-items-center">
                        <h3 class="h5 mb-0">Recent Activities</h3>
                        <a href="activities.php?relative_id=<?php echo $relative_id; ?>" class="btn btn-sm btn-outline-primary">
                            View All Activities
                        </a>
                    </div>
                    <div class="card-body">
                        <?php if ($activities->num_rows > 0): ?>
                            <div class="timeline">
                                <?php while ($activity = $activities->fetch_assoc()): ?>
                                <div class="timeline-item">
                                    <div class="timeline-date">
                                        <?php echo date('M d, h:i A', strtotime($activity['activity_date'] . ' ' . $activity['activity_time'])); ?>
                                    </div>
                                    <div class="timeline-content">
                                        <h4 class="h6 mb-1"><?php echo htmlspecialchars($activity['activity_type']); ?></h4>
                                        <p class="mb-0"><?php echo htmlspecialchars($activity['description']); ?></p>
                                    </div>
                                </div>
                                <?php endwhile; ?>
                            </div>
                        <?php else: ?>
                            <div class="text-center py-4">
                                <i class="fas fa-walking fa-3x text-muted mb-3"></i>
                                <p class="mb-0">No activities recorded yet.</p>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
                
                <!-- Medications Section -->
                <div class="card shadow-sm">
                    <div class="card-header bg-white d-flex justify-content-between align-items-center">
                        <h3 class="h5 mb-0">Medications</h3>
                    </div>
                    <div class="card-body">
                        <?php 
                        $medications = get_relative_medications($relative_id);
                        if (count($medications) > 0): ?>
                            <div class="table-responsive">
                                <table class="table table-hover">
                                    <thead>
                                        <tr>
                                            <th>Medication</th>
                                            <th>Dosage</th>
                                            <th>Time</th>
                                            <th>Status</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($medications as $med): ?>
                                        <tr>
                                            <td><?php echo htmlspecialchars($med['name']); ?></td>
                                            <td><?php echo htmlspecialchars($med['dosage']); ?></td>
                                            <td><?php echo date('h:i A', strtotime($med['scheduled_time'])); ?></td>
                                            <td>
                                                <?php if($med['current_status'] == 'taken'): ?>
                                                    <span class="badge bg-success">Taken</span>
                                                <?php elseif($med['current_status'] == 'overdue'): ?>
                                                    <span class="badge bg-danger">Overdue</span>
                                                <?php elseif($med['current_status'] == 'pending'): ?>
                                                    <span class="badge bg-warning">Pending</span>
                                                <?php elseif($med['current_status'] == 'skipped'): ?>
                                                    <span class="badge bg-secondary">Skipped</span>
                                                <?php else: ?>
                                                    <span class="badge bg-info">Unknown</span>
                                                <?php endif; ?>
                                            </td>
                                        </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                        <?php else: ?>
                            <div class="text-center py-4">
                                <i class="fas fa-pills fa-3x text-muted mb-3"></i>
                                <p class="mb-0">No medications recorded yet.</p>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </main>

    <!-- Video Call Modal -->
    <div class="modal fade" id="videoCallModal" tabindex="-1" aria-labelledby="videoCallModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="videoCallModalLabel">Video Call</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body text-center py-4">
                    <div class="mb-3">
                        <i class="fas fa-video fa-4x text-primary"></i>
                    </div>
                    <h3>Starting Video Call</h3>
                    <p class="text-muted mb-4">Connecting to <?php echo htmlspecialchars($relative['first_name'] . ' ' . $relative['last_name']); ?>...</p>
                    <div class="spinner-border text-primary" role="status">
                        <span class="visually-hidden">Loading...</span>
                    </div>
                    <p class="mt-4"><small>This is a demo. In a real app, this would initiate a video call.</small></p>
                </div>
            </div>
        </div>
    </div>

    <!-- Message Modal -->
    <div class="modal fade" id="messageModal" tabindex="-1" aria-labelledby="messageModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="messageModalLabel">Send Message</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <form>
                        <div class="mb-3">
                            <label for="messageSubject" class="form-label">Subject</label>
                            <input type="text" class="form-control" id="messageSubject">
                        </div>
                        <div class="mb-3">
                            <label for="messageText" class="form-label">Message</label>
                            <textarea class="form-control" id="messageText" rows="5"></textarea>
                        </div>
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="button" class="btn btn-primary">Send Message</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Footer -->
    <?php include_once '../includes/footer.php'; ?>

    <!-- JavaScript -->
    <script src="../assets/js/bootstrap.bundle.min.js"></script>
    <script src="../assets/js/jquery-3.6.0.min.js"></script>
</body>
</html>