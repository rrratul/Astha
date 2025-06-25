<?php

session_start();

// Include required files
require_once '../login_user/functions.php';
require_once '../signup_user/config.php';
require_once 'db_connect.php';

// Authentication checks
if (!isLoggedIn() || $_SESSION['user_role'] !== 'healthcare_provider') {
    header("Location: ../logout.php");
    exit();
}

// Get user information
$user_id = $_SESSION['user_id'];
$user_name = $_SESSION['user_name'] ?? 'Healthcare Provider';
$user_first_name = $_SESSION['user_first_name'] ?? 'Healthcare Provider';

// Process appointment actions (cancel, complete, reschedule)
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['cancel_appointment']) && isset($_POST['appointment_id'])) {
        $appointment_id = $_POST['appointment_id'];
        
        $stmt = $pdo->prepare("UPDATE appointments SET status = 'cancelled' WHERE id = :id AND doctor_name = :doctor_name");
        $success = $stmt->execute(['id' => $appointment_id, 'doctor_name' => $user_name]);
        
        if ($success) {
            // Create notification about cancellation
            $stmt = $pdo->prepare("
                SELECT a.user_id, a.title, a.date, a.time, u.first_name, u.last_name 
                FROM appointments a
                JOIN users u ON a.user_id = u.id
                WHERE a.id = :id
            ");
            $stmt->execute(['id' => $appointment_id]);
            $appointment = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if ($appointment) {
                $notification_title = "Appointment Cancelled";
                $notification_content = "Your appointment '" . $appointment['title'] . "' scheduled for " . 
                                       date('M d, Y', strtotime($appointment['date'])) . " at " . 
                                       date('h:i A', strtotime($appointment['time'])) . " has been cancelled by Dr. {$user_name}.";
                
                $stmt = $pdo->prepare("
                    INSERT INTO notifications 
                    (user_id, title, content, notification_type, reference_id, reference_type, created_by)
                    VALUES 
                    (:user_id, :title, :content, :notification_type, :reference_id, :reference_type, :created_by)
                ");
                
                $stmt->execute([
                    'user_id' => $appointment['user_id'],
                    'title' => $notification_title,
                    'content' => $notification_content,
                    'notification_type' => 'appointment_cancelled',
                    'reference_id' => $appointment_id,
                    'reference_type' => 'appointments',
                    'created_by' => $user_id
                ]);
                
                $_SESSION['success_message'] = "Appointment with " . $appointment['first_name'] . ' ' . $appointment['last_name'] . " has been cancelled.";
            } else {
                $_SESSION['error_message'] = "Appointment not found.";
            }
        } else {
            $_SESSION['error_message'] = "Failed to cancel appointment.";
        }
    } elseif (isset($_POST['complete_appointment']) && isset($_POST['appointment_id'])) {
        $appointment_id = $_POST['appointment_id'];
        
        $stmt = $pdo->prepare("UPDATE appointments SET status = 'completed' WHERE id = :id AND doctor_name = :doctor_name");
        $success = $stmt->execute(['id' => $appointment_id, 'doctor_name' => $user_name]);
        
        if ($success) {
            $_SESSION['success_message'] = "Appointment marked as completed.";
        } else {
            $_SESSION['error_message'] = "Failed to update appointment status.";
        }
    }
    
    // Redirect to avoid form resubmission
    header("Location: appointments.php");
    exit();
}

// Filter parameters
$filter_status = $_GET['status'] ?? 'all';
$filter_date = $_GET['date'] ?? 'upcoming';
$search = $_GET['search'] ?? '';
$page = isset($_GET['page']) ? max(1, intval($_GET['page'])) : 1;
$per_page = 10;
$offset = ($page - 1) * $per_page;

// Base query
$query = "
    SELECT a.*, u.first_name, u.last_name, u.profile_picture, u.profile_picture_type
    FROM appointments a
    JOIN users u ON a.user_id = u.id
    WHERE a.doctor_name = :doctor_name
";

$params = ['doctor_name' => $user_name];

// Apply filters
if ($filter_status !== 'all') {
    $query .= " AND a.status = :status";
    $params['status'] = $filter_status;
}

if ($filter_date === 'upcoming') {
    $query .= " AND a.date >= CURDATE()";
} elseif ($filter_date === 'past') {
    $query .= " AND a.date < CURDATE()";
} elseif ($filter_date === 'today') {
    $query .= " AND DATE(a.date) = CURDATE()";
}

if (!empty($search)) {
    $query .= " AND (u.first_name LIKE :search OR u.last_name LIKE :search OR a.title LIKE :search)";
    $params['search'] = "%{$search}%";
}

// Get total count for pagination
$count_stmt = $pdo->prepare(str_replace("SELECT a.*, u.first_name, u.last_name, u.profile_picture, u.profile_picture_type", "SELECT COUNT(*)", $query));
$count_stmt->execute($params);
$total_records = $count_stmt->fetchColumn();
$total_pages = ceil($total_records / $per_page);

// Add sorting and pagination
$query .= " ORDER BY a.date ASC, a.time ASC LIMIT :offset, :per_page";
$stmt = $pdo->prepare($query);

// Need to bind these separately because of LIMIT clause
foreach ($params as $key => $value) {
    $stmt->bindValue(":{$key}", $value);
}
$stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
$stmt->bindValue(':per_page', $per_page, PDO::PARAM_INT);
$stmt->execute();

$appointments = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Appointments - Healthcare Provider Dashboard</title>
    
    <!-- Include your CSS files here -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@400;500;600;700&family=Open+Sans:wght@400;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <link rel="stylesheet" href="../elderly/includes/style.css">
    <link rel="stylesheet" href="./css/healthcare.css">
    
    <style>
        .appointments-container {
            max-width: 1200px;
            margin: 20px auto;
            padding: 0 20px;
        }
        
        .page-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 30px;
        }
        
        .page-title {
            font-size: 28px;
            font-weight: 600;
            color: #2d3748;
            margin: 0;
            display: flex;
            align-items: center;
        }
        
        .page-title i {
            color: #0096ad;
            margin-right: 12px;
        }
        
        .action-button {
            background-color: #0096ad;
            color: white;
            border: none;
            padding: 12px 20px;
            border-radius: 8px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s;
            display: flex;
            align-items: center;
            gap: 8px;
            text-decoration: none;
        }
        
        .action-button:hover {
            background-color: #007a8c;
            transform: translateY(-2px);
        }
        
        .filter-bar {
            display: flex;
            justify-content: space-between;
            align-items: center;
            background-color: white;
            padding: 15px 20px;
            border-radius: 12px;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.05);
            margin-bottom: 25px;
        }
        
        .filter-group {
            display: flex;
            gap: 15px;
        }
        
        .filter-select {
            padding: 8px 12px;
            border: 1px solid #e2e8f0;
            border-radius: 6px;
            font-size: 14px;
            min-width: 150px;
        }
        
        .search-box {
            display: flex;
            gap: 10px;
            width: 300px;
        }
        
        .search-input {
            flex: 1;
            padding: 8px 12px;
            border: 1px solid #e2e8f0;
            border-radius: 6px;
            font-size: 14px;
        }
        
        .search-button {
            background-color: #0096ad;
            color: white;
            border: none;
            padding: 8px 15px;
            border-radius: 6px;
            cursor: pointer;
        }
        
        .appointments-table {
            width: 100%;
            border-collapse: collapse;
            background-color: white;
            border-radius: 12px;
            overflow: hidden;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.05);
        }
        
        .appointments-table th {
            background-color: #f8fafc;
            padding: 15px;
            text-align: left;
            font-weight: 600;
            color: #4a5568;
            border-bottom: 1px solid #e2e8f0;
        }
        
        .appointments-table td {
            padding: 15px;
            border-bottom: 1px solid #e2e8f0;
            vertical-align: middle;
        }
        
        .appointments-table tr:last-child td {
            border-bottom: none;
        }
        
        .appointments-table tr:hover {
            background-color: #f7fafc;
        }
        
        .patient-cell {
            display: flex;
            align-items: center;
            gap: 12px;
        }
        
        .patient-avatar {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            background-color: #e2e8f0;
            color: #4a5568;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: 600;
            font-size: 16px;
            overflow: hidden;
        }
        
        .patient-avatar img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }
        
        .patient-info {
            display: flex;
            flex-direction: column;
        }
        
        .patient-name {
            font-weight: 600;
            color: #2d3748;
        }
        
        .appointment-type {
            font-size: 14px;
            color: #4a5568;
        }
        
        .status-badge {
            display: inline-block;
            padding: 5px 10px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: 600;
        }
        
        .status-scheduled {
            background-color: #e3f2fd;
            color: #1976d2;
        }
        
        .status-completed {
            background-color: #e8f5e9;
            color: #388e3c;
        }
        
        .status-cancelled {
            background-color: #ffebee;
            color: #d32f2f;
        }
        
        .appointment-date {
            font-weight: 500;
        }
        
        .appointment-time {
            color: #4a5568;
            font-size: 14px;
        }
        
        .action-cell {
            display: flex;
            gap: 8px;
        }
        
        .action-btn {
            width: 36px;
            height: 36px;
            border-radius: 8px;
            border: none;
            display: flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            transition: all 0.2s;
        }
        
        .action-btn.view {
            background-color: #e2e8f0;
            color: #4a5568;
        }
        
        .action-btn.view:hover {
            background-color: #cbd5e0;
        }
        
        .action-btn.complete {
            background-color: #c6f6d5;
            color: #22543d;
        }
        
        .action-btn.complete:hover {
            background-color: #9ae6b4;
        }
        
        .action-btn.cancel {
            background-color: #fed7d7;
            color: #822727;
        }
        
        .action-btn.cancel:hover {
            background-color: #feb2b2;
        }
        
        .pagination {
            display: flex;
            justify-content: center;
            margin-top: 30px;
            gap: 10px;
        }
        
        .page-link {
            display: flex;
            align-items: center;
            justify-content: center;
            width: 40px;
            height: 40px;
            border-radius: 8px;
            background-color: white;
            color: #4a5568;
            text-decoration: none;
            transition: all 0.2s;
        }
        
        .page-link:hover {
            background-color: #e2e8f0;
        }
        
        .page-link.active {
            background-color: #0096ad;
            color: white;
        }
        
        .no-results {
            background-color: white;
            padding: 40px 20px;
            text-align: center;
            border-radius: 12px;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.05);
        }
        
        .no-results-icon {
            font-size: 48px;
            color: #cbd5e0;
            margin-bottom: 15px;
        }
        
        .no-results-text {
            font-size: 18px;
            color: #4a5568;
            margin-bottom: 20px;
        }
        
        /* Alert messages */
        .alert {
            padding: 15px;
            border-radius: 8px;
            margin-bottom: 20px;
            display: flex;
            align-items: center;
            gap: 12px;
        }
        
        .alert-success {
            background-color: #c6f6d5;
            color: #22543d;
        }
        
        .alert-error {
            background-color: #fed7d7;
            color: #822727;
        }
        
        .alert i {
            font-size: 20px;
        }
        
        /* Modal styles for confirm actions */
        .modal {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0, 0, 0, 0.5);
            z-index: 1000;
            align-items: center;
            justify-content: center;
        }
        
        .modal.show {
            display: flex;
            animation: fadeIn 0.3s;
        }
        
        .modal-content {
            background-color: white;
            border-radius: 12px;
            padding: 30px;
            width: 100%;
            max-width: 500px;
            box-shadow: 0 10px 25px rgba(0, 0, 0, 0.1);
            transform: translateY(0);
            animation: slideIn 0.3s;
        }
        
        .modal-header {
            margin-bottom: 20px;
        }
        
        .modal-title {
            font-size: 20px;
            font-weight: 600;
            color: #2d3748;
            margin: 0;
        }
        
        .modal-body {
            margin-bottom: 25px;
        }
        
        .modal-footer {
            display: flex;
            justify-content: flex-end;
            gap: 15px;
        }
        
        .modal-btn {
            padding: 10px 20px;
            border-radius: 8px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.2s;
            border: none;
        }
        
        .modal-btn.cancel {
            background-color: #e2e8f0;
            color: #4a5568;
        }
        
        .modal-btn.cancel:hover {
            background-color: #cbd5e0;
        }
        
        .modal-btn.confirm {
            background-color: #0096ad;
            color: white;
        }
        
        .modal-btn.confirm:hover {
            background-color: #007a8c;
        }
        
        .modal-btn.danger {
            background-color: #e53e3e;
            color: white;
        }
        
        .modal-btn.danger:hover {
            background-color: #c53030;
        }
        
        @keyframes fadeIn {
            from { opacity: 0; }
            to { opacity: 1; }
        }
        
        @keyframes slideIn {
            from { transform: translateY(-20px); opacity: 0; }
            to { transform: translateY(0); opacity: 1; }
        }
        
        @media (max-width: 992px) {
            .filter-bar {
                flex-direction: column;
                gap: 15px;
                align-items: flex-start;
            }
            
            .search-box {
                width: 100%;
            }
        }
        
        @media (max-width: 768px) {
            .appointments-table {
                display: block;
                overflow-x: auto;
            }
            
            .filter-group {
                flex-wrap: wrap;
            }
        }
    </style>
</head>
<body>
    <?php include_once "includes/nav.php"; ?>
    
    <main class="main-content">
        <div class="appointments-container">
            <div class="page-header">
                <h1 class="page-title">
                    <i class="fas fa-calendar-check"></i>
                    Appointments
                </h1>
                
                <a href="add_appointment.php" class="action-button">
                    <i class="fas fa-plus"></i>
                    New Appointment
                </a>
            </div>
            
            <?php if (isset($_SESSION['success_message'])): ?>
                <div class="alert alert-success">
                    <i class="fas fa-check-circle"></i>
                    <span><?php echo $_SESSION['success_message']; ?></span>
                </div>
                <?php unset($_SESSION['success_message']); ?>
            <?php endif; ?>
            
            <?php if (isset($_SESSION['error_message'])): ?>
                <div class="alert alert-error">
                    <i class="fas fa-exclamation-circle"></i>
                    <span><?php echo $_SESSION['error_message']; ?></span>
                </div>
                <?php unset($_SESSION['error_message']); ?>
            <?php endif; ?>
            
            <div class="filter-bar">
                <div class="filter-group">
                    <select class="filter-select" id="statusFilter" onchange="applyFilters()">
                        <option value="all" <?php echo $filter_status === 'all' ? 'selected' : ''; ?>>All Status</option>
                        <option value="scheduled" <?php echo $filter_status === 'scheduled' ? 'selected' : ''; ?>>Scheduled</option>
                        <option value="completed" <?php echo $filter_status === 'completed' ? 'selected' : ''; ?>>Completed</option>
                        <option value="cancelled" <?php echo $filter_status === 'cancelled' ? 'selected' : ''; ?>>Cancelled</option>
                    </select>
                    
                    <select class="filter-select" id="dateFilter" onchange="applyFilters()">
                        <option value="upcoming" <?php echo $filter_date === 'upcoming' ? 'selected' : ''; ?>>Upcoming</option>
                        <option value="today" <?php echo $filter_date === 'today' ? 'selected' : ''; ?>>Today</option>
                        <option value="past" <?php echo $filter_date === 'past' ? 'selected' : ''; ?>>Past</option>
                        <option value="all" <?php echo $filter_date === 'all' ? 'selected' : ''; ?>>All Dates</option>
                    </select>
                </div>
                
                <div class="search-box">
                    <input type="text" class="search-input" id="searchInput" placeholder="Search patients or appointments" value="<?php echo htmlspecialchars($search); ?>">
                    <button class="search-button" onclick="applyFilters()">
                        <i class="fas fa-search"></i>
                    </button>
                </div>
            </div>
            
            <?php if (!empty($appointments)): ?>
                <table class="appointments-table">
                    <thead>
                        <tr>
                            <th>Patient</th>
                            <th>Date & Time</th>
                            <th>Status</th>
                            <th>Type</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($appointments as $appointment): ?>
                            <tr>
                                <td>
                                    <div class="patient-cell">
                                        <div class="patient-avatar">
                                            <?php if (!empty($appointment['profile_picture']) && !empty($appointment['profile_picture_type'])): ?>
                                                <img src="data:<?php echo $appointment['profile_picture_type']; ?>;base64,<?php echo base64_encode($appointment['profile_picture']); ?>" alt="Patient">
                                            <?php else: ?>
                                                <?php echo strtoupper(substr($appointment['first_name'], 0, 1)); ?>
                                            <?php endif; ?>
                                        </div>
                                        <div class="patient-info">
                                            <span class="patient-name"><?php echo htmlspecialchars($appointment['first_name'] . ' ' . $appointment['last_name']); ?></span>
                                            <span class="appointment-type"><?php echo htmlspecialchars($appointment['title']); ?></span>
                                        </div>
                                    </div>
                                </td>
                                <td>
                                    <div class="appointment-date"><?php echo date('D, M d, Y', strtotime($appointment['date'])); ?></div>
                                    <div class="appointment-time"><?php echo date('h:i A', strtotime($appointment['time'])); ?></div>
                                </td>
                                <td>
                                    <?php 
                                        $statusClass = '';
                                        switch ($appointment['status']) {
                                            case 'scheduled':
                                                $statusClass = 'status-scheduled';
                                                break;
                                            case 'completed':
                                                $statusClass = 'status-completed';
                                                break;
                                            case 'cancelled':
                                                $statusClass = 'status-cancelled';
                                                break;
                                            default:
                                                $statusClass = '';
                                        }
                                    ?>
                                    <span class="status-badge <?php echo $statusClass; ?>">
                                        <?php echo ucfirst($appointment['status']); ?>
                                    </span>
                                </td>
                                <td>
                                    <?php echo $appointment['is_video_call'] ? '<i class="fas fa-video"></i> Video Call' : '<i class="fas fa-user-md"></i> In-Person'; ?>
                                </td>
                                <td>
                                    <div class="action-cell">
                                        <button class="action-btn view" title="View Details" onclick="viewAppointment(<?php echo $appointment['id']; ?>)">
                                            <i class="fas fa-eye"></i>
                                        </button>
                                        
                                        <?php if ($appointment['status'] === 'scheduled'): ?>
                                            <button class="action-btn complete" title="Mark as Completed" onclick="showCompleteModal(<?php echo $appointment['id']; ?>)">
                                                <i class="fas fa-check"></i>
                                            </button>
                                            
                                            <button class="action-btn cancel" title="Cancel Appointment" onclick="showCancelModal(<?php echo $appointment['id']; ?>)">
                                                <i class="fas fa-times"></i>
                                            </button>
                                        <?php endif; ?>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
                
                <?php if ($total_pages > 1): ?>
                    <div class="pagination">
                        <?php if ($page > 1): ?>
                            <a href="?page=<?php echo ($page - 1); ?>&status=<?php echo $filter_status; ?>&date=<?php echo $filter_date; ?>&search=<?php echo urlencode($search); ?>" class="page-link">
                                <i class="fas fa-chevron-left"></i>
                            </a>
                        <?php endif; ?>
                        
                        <?php for ($i = max(1, $page - 2); $i <= min($total_pages, $page + 2); $i++): ?>
                            <a href="?page=<?php echo $i; ?>&status=<?php echo $filter_status; ?>&date=<?php echo $filter_date; ?>&search=<?php echo urlencode($search); ?>" class="page-link <?php echo $i === $page ? 'active' : ''; ?>">
                                <?php echo $i; ?>
                            </a>
                        <?php endfor; ?>
                        
                        <?php if ($page < $total_pages): ?>
                            <a href="?page=<?php echo ($page + 1); ?>&status=<?php echo $filter_status; ?>&date=<?php echo $filter_date; ?>&search=<?php echo urlencode($search); ?>" class="page-link">
                                <i class="fas fa-chevron-right"></i>
                            </a>
                        <?php endif; ?>
                    </div>
                <?php endif; ?>
            <?php else: ?>
                <div class="no-results">
                    <div class="no-results-icon">
                        <i class="fas fa-calendar-times"></i>
                    </div>
                    <div class="no-results-text">No appointments found</div>
                    <p>Try adjusting your filters or create a new appointment</p>
                    <a href="add_appointment.php" class="action-button" style="display: inline-flex; margin-top: 15px;">
                        <i class="fas fa-plus"></i>
                        New Appointment
                    </a>
                </div>
            <?php endif; ?>
        </div>
    </main>
    
    <!-- Cancel Appointment Modal -->
    <div class="modal" id="cancelModal">
        <div class="modal-content">
            <div class="modal-header">
                <h2 class="modal-title">Cancel Appointment</h2>
            </div>
            <div class="modal-body">
                <p>Are you sure you want to cancel this appointment? This action cannot be undone.</p>
            </div>
            <div class="modal-footer">
                <button class="modal-btn cancel" onclick="hideModal('cancelModal')">No, Keep it</button>
                <form id="cancelForm" method="post" style="margin: 0;">
                    <input type="hidden" name="appointment_id" id="cancelAppointmentId">
                    <button type="submit" name="cancel_appointment" class="modal-btn danger">Yes, Cancel Appointment</button>
                </form>
            </div>
        </div>
    </div>
    
    <!-- Complete Appointment Modal -->
    <div class="modal" id="completeModal">
        <div class="modal-content">
            <div class="modal-header">
                <h2 class="modal-title">Complete Appointment</h2>
            </div>
            <div class="modal-body">
                <p>Mark this appointment as completed?</p>
            </div>
            <div class="modal-footer">
                <button class="modal-btn cancel" onclick="hideModal('completeModal')">Cancel</button>
                <form id="completeForm" method="post" style="margin: 0;">
                    <input type="hidden" name="appointment_id" id="completeAppointmentId">
                    <button type="submit" name="complete_appointment" class="modal-btn confirm">Mark as Completed</button>
                </form>
            </div>
        </div>
    </div>
    
    <?php include_once "../elderly/includes/footer.php"; ?>
    <?php include_once "includes/mobile_nav.php"; ?>
    
    <script>
        function applyFilters() {
            const status = document.getElementById('statusFilter').value;
            const date = document.getElementById('dateFilter').value;
            const search = document.getElementById('searchInput').value;
            
            window.location.href = `appointments.php?status=${status}&date=${date}&search=${encodeURIComponent(search)}`;
        }
        
        function viewAppointment(id) {
            // Redirect to appointment details page
            window.location.href = `appointment_details.php?id=${id}`;
        }
        
        function showCancelModal(appointmentId) {
            document.getElementById('cancelAppointmentId').value = appointmentId;
            document.getElementById('cancelModal').classList.add('show');
        }
        
        function showCompleteModal(appointmentId) {
            document.getElementById('completeAppointmentId').value = appointmentId;
            document.getElementById('completeModal').classList.add('show');
        }
        
        function hideModal(modalId) {
            document.getElementById(modalId).classList.remove('show');
        }
        
        // Close modals when clicking outside
        window.addEventListener('click', function(event) {
            if (event.target.classList.contains('modal')) {
                event.target.classList.remove('show');
            }
        });
        
        // Handle pressing enter in search field
        document.getElementById('searchInput').addEventListener('keyup', function(event) {
            if (event.key === 'Enter') {
                applyFilters();
            }
        });
    </script>
</body>
</html>