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

// Process search/filter
$search = $_GET['search'] ?? '';
$filter = $_GET['filter'] ?? 'all';
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$per_page = 10; // Patients per page
$offset = ($page - 1) * $per_page;

// Base query
$base_query = "
    SELECT u.*, 
           MAX(a.date) as last_appointment_date,
           COUNT(DISTINCT a.id) as appointment_count
    FROM users u
    LEFT JOIN appointments a ON u.id = a.user_id AND a.doctor_name = :doctor_name
    WHERE (u.role = 'elderly' OR u.role = '') 
";

// Add search condition if provided
if (!empty($search)) {
    $base_query .= " AND (u.first_name LIKE :search OR u.last_name LIKE :search OR u.email LIKE :search)";
}

// Add filter conditions
switch ($filter) {
    case 'recent':
        $base_query .= " AND a.date >= DATE_SUB(CURDATE(), INTERVAL 30 DAY)";
        break;
    case 'upcoming':
        $base_query .= " AND a.date >= CURDATE() AND a.status = 'scheduled'";
        break;
    case 'critical':
        $base_query .= " AND EXISTS (
            SELECT 1 FROM health_metrics hm 
            WHERE hm.user_id = u.id 
            AND (
                hm.blood_pressure_systolic > 140 OR 
                hm.blood_pressure_diastolic > 90 OR 
                hm.heart_rate > 100 OR 
                hm.heart_rate < 60 OR 
                hm.blood_sugar > 180
            )
            AND hm.created_at >= DATE_SUB(CURDATE(), INTERVAL 7 DAY)
        )";
        break;
}

// Group by and order
$base_query .= " GROUP BY u.id ORDER BY last_appointment_date DESC";

// Count total patients for pagination
$count_query = str_replace("u.*, MAX(a.date) as last_appointment_date, COUNT(DISTINCT a.id) as appointment_count", "COUNT(DISTINCT u.id) as total", $base_query);
$stmt = $pdo->prepare($count_query);

if (!empty($search)) {
    $stmt->bindValue(':search', '%' . $search . '%');
}
$stmt->bindValue(':doctor_name', $user_name);
$stmt->execute();
$total_patients = $stmt->fetchColumn();
$total_pages = ceil($total_patients / $per_page);

// Get paginated patients
$stmt = $pdo->prepare($base_query . " LIMIT :offset, :per_page");
if (!empty($search)) {
    $stmt->bindValue(':search', '%' . $search . '%');
}
$stmt->bindValue(':doctor_name', $user_name);
$stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
$stmt->bindValue(':per_page', $per_page, PDO::PARAM_INT);
$stmt->execute();
$patients = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Get recent health metrics for critical monitoring
$stmt = $pdo->prepare("
    SELECT hm.*, u.first_name, u.last_name
    FROM health_metrics hm
    JOIN users u ON hm.user_id = u.id
    JOIN appointments a ON u.id = a.user_id
    WHERE a.doctor_name = :doctor_name
    AND hm.created_at >= DATE_SUB(CURDATE(), INTERVAL 7 DAY)
    AND (
        hm.blood_pressure_systolic > 140 OR 
        hm.blood_pressure_diastolic > 90 OR 
        hm.heart_rate > 100 OR 
        hm.heart_rate < 60 OR 
        hm.blood_sugar > 180
    )
    GROUP BY u.id
    ORDER BY hm.created_at DESC
    LIMIT 5
");
$stmt->execute(['doctor_name' => $user_name]);
$critical_metrics = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Patients - Healthcare Provider Dashboard</title>
    
    <!-- Google Translate -->
    <script type="text/javascript" src="https://translate.google.com/translate_a/element.js?cb=googleTranslateElementInit"></script>
    <script type="text/javascript">
        function googleTranslateElementInit() {
            // Initialize desktop translate
            new google.translate.TranslateElement({
                pageLanguage: 'en',
                includedLanguages: 'bn,en',
                layout: google.translate.TranslateElement.InlineLayout.SIMPLE
            }, 'google_translate_element');
            
            // Initialize mobile translate separately
            new google.translate.TranslateElement({
                pageLanguage: 'en',
                includedLanguages: 'bn,en',
                layout: google.translate.TranslateElement.InlineLayout.SIMPLE
            }, 'mobile-google-translate');
        }
    </script>
    
    <!-- Styles and fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@400;500;600;700&family=Open+Sans:wght@400;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <link rel="stylesheet" href="../elderly/includes/style.css">
    <link rel="stylesheet" href="./css/healthcare.css">
    
    <style>
        .container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 20px;
        }
        
        .page-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 30px;
            flex-wrap: wrap;
            gap: 20px;
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
        
        .header-actions {
            display: flex;
            gap: 15px;
        }
        
        .card {
            background-color: #fff;
            border-radius: 12px;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.05);
            overflow: hidden;
            margin-bottom: 30px;
        }
        
        .card-header {
            padding: 20px;
            border-bottom: 1px solid #e2e8f0;
            display: flex;
            justify-content: space-between;
            align-items: center;
            flex-wrap: wrap;
            gap: 15px;
        }
        
        .card-title {
            margin: 0;
            font-size: 18px;
            font-weight: 600;
            color: #2d3748;
            display: flex;
            align-items: center;
        }
        
        .card-title i {
            margin-right: 10px;
            color: #0096ad;
        }
        
        .card-body {
            padding: 20px;
        }
        
        .search-filter {
            display: flex;
            gap: 15px;
            margin-bottom: 20px;
            flex-wrap: wrap;
        }
        
        .search-input {
            flex: 1;
            min-width: 200px;
            position: relative;
        }
        
        .search-input input {
            width: 100%;
            padding: 10px 15px 10px 40px;
            border-radius: 8px;
            border: 1px solid #e2e8f0;
            font-size: 14px;
        }
        
        .search-input i {
            position: absolute;
            left: 15px;
            top: 50%;
            transform: translateY(-50%);
            color: #718096;
        }
        
        .filter-select {
            min-width: 150px;
        }
        
        .filter-select select {
            width: 100%;
            padding: 10px 15px;
            border-radius: 8px;
            border: 1px solid #e2e8f0;
            font-size: 14px;
            background-color: #fff;
            appearance: none;
            background-image: url("data:image/svg+xml,%3csvg xmlns='http://www.w3.org/2000/svg' fill='none' viewBox='0 0 20 20'%3e%3cpath stroke='%236b7280' stroke-linecap='round' stroke-linejoin='round' stroke-width='1.5' d='M6 8l4 4 4-4'/%3e%3c/svg%3e");
            background-position: right 10px center;
            background-repeat: no-repeat;
            background-size: 20px;
        }
        
        .btn {
            padding: 10px 20px;
            border-radius: 8px;
            font-weight: 500;
            font-size: 14px;
            cursor: pointer;
            transition: all 0.3s;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
        }
        
        .btn-primary {
            background-color: #0096ad;
            color: #fff;
            border: none;
        }
        
        .btn-primary:hover {
            background-color: #007a8c;
        }
        
        .btn-outline {
            background-color: transparent;
            border: 1px solid #e2e8f0;
            color: #4a5568;
        }
        
        .btn-outline:hover {
            background-color: #f7fafc;
        }
        
        .table-container {
            overflow-x: auto;
        }
        
        .data-table {
            width: 100%;
            border-collapse: collapse;
        }
        
        .data-table th {
            text-align: left;
            padding: 15px 20px;
            font-weight: 600;
            color: #4a5568;
            font-size: 14px;
            background-color: #f7fafc;
            border-bottom: 1px solid #e2e8f0;
        }
        
        .data-table td {
            padding: 15px 20px;
            border-bottom: 1px solid #e2e8f0;
            font-size: 14px;
            color: #2d3748;
        }
        
        .data-table tr:last-child td {
            border-bottom: none;
        }
        
        .data-table tr:hover {
            background-color: #f7fafc;
        }
        
        .patient-info {
            display: flex;
            align-items: center;
            gap: 12px;
        }
        
        .patient-avatar {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            background-color: #e2f5f8;
            color: #0096ad;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: 600;
            overflow: hidden;
        }
        
        .patient-avatar img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }
        
        .patient-details span {
            display: block;
        }
        
        .patient-name {
            font-weight: 500;
        }
        
        .patient-email {
            font-size: 13px;
            color: #718096;
        }
        
        .status-badge {
            display: inline-block;
            padding: 5px 10px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: 500;
        }
        
        .badge-primary {
            background-color: #e2f5f8;
            color: #0096ad;
        }
        
        .badge-success {
            background-color: #e6ffec;
            color: #22543d;
        }
        
        .badge-warning {
            background-color: #fef3c7;
            color: #92400e;
        }
        
        .badge-danger {
            background-color: #fee2e2;
            color: #b91c1c;
        }
        
        .action-buttons {
            display: flex;
            gap: 8px;
        }
        
        .btn-icon {
            width: 32px;
            height: 32px;
            border-radius: 6px;
            border: 1px solid #e2e8f0;
            background-color: #fff;
            display: flex;
            align-items: center;
            justify-content: center;
            color: #4a5568;
            cursor: pointer;
            transition: all 0.3s;
        }
        
        .btn-icon:hover {
            background-color: #f7fafc;
            color: #0096ad;
        }
        
        .pagination {
            display: flex;
            justify-content: center;
            margin-top: 20px;
            gap: 5px;
        }
        
        .pagination a, .pagination span {
            display: flex;
            align-items: center;
            justify-content: center;
            width: 36px;
            height: 36px;
            border-radius: 6px;
            border: 1px solid #e2e8f0;
            color: #4a5568;
            text-decoration: none;
            transition: all 0.3s;
        }
        
        .pagination a:hover {
            background-color: #f7fafc;
        }
        
        .pagination .active {
            background-color: #0096ad;
            color: #fff;
            border-color: #0096ad;
        }
        
        .empty-state {
            text-align: center;
            padding: 40px 20px;
        }
        
        .empty-state-icon {
            font-size: 48px;
            color: #cbd5e0;
            margin-bottom: 15px;
        }
        
        .empty-state-text {
            color: #718096;
            font-size: 16px;
            margin-bottom: 20px;
        }
        
        /* Critical Monitoring Section */
        .critical-monitoring {
            margin-bottom: 30px;
        }
        
        .monitoring-cards {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
            gap: 20px;
        }
        
        .monitoring-card {
            background-color: #fff;
            border-radius: 12px;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.05);
            padding: 20px;
            border-left: 4px solid #e53e3e;
        }
        
        .monitoring-card-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 15px;
        }
        
        .monitoring-card-title {
            font-weight: 600;
            color: #2d3748;
            margin: 0;
        }
        
        .monitoring-card-date {
            color: #718096;
            font-size: 12px;
        }
        
        .metric-list {
            margin: 0;
            padding: 0;
            list-style-type: none;
        }
        
        .metric-item {
            display: flex;
            justify-content: space-between;
            padding: 8px 0;
            border-bottom: 1px dashed #e2e8f0;
        }
        
        .metric-item:last-child {
            border-bottom: none;
        }
        
        .metric-name {
            color: #4a5568;
            font-size: 14px;
        }
        
        .metric-value {
            font-weight: 600;
            color: #2d3748;
        }
        
        .metric-value.critical {
            color: #e53e3e;
        }
        
        @media (max-width: 768px) {
            .page-header {
                flex-direction: column;
                align-items: flex-start;
            }
            
            .header-actions {
                width: 100%;
                justify-content: space-between;
            }
            
            .search-filter {
                flex-direction: column;
            }
            
            .data-table th:nth-child(4),
            .data-table td:nth-child(4) {
                display: none;
            }
        }
    </style>
</head>
<body>
    <?php include_once "includes/nav.php"; ?>
    
    <main class="main-content">
        <div class="container">
            <div class="page-header">
                <h1 class="page-title">
                    <i class="fas fa-user-injured"></i>
                    Patients
                </h1>
                
                <div class="header-actions">
                    <a href="appointments.php" class="btn btn-outline">
                        <i class="fas fa-calendar-check"></i>
                        View Appointments
                    </a>
                    <a href="health-records.php" class="btn btn-primary">
                        <i class="fas fa-heartbeat"></i>
                        Health Records
                    </a>
                </div>
            </div>
            
            <?php if (!empty($critical_metrics)): ?>
            <div class="critical-monitoring">
                <div class="card">
                    <div class="card-header">
                        <h2 class="card-title">
                            <i class="fas fa-exclamation-triangle"></i>
                            Critical Health Monitoring
                        </h2>
                    </div>
                    <div class="card-body">
                        <div class="monitoring-cards">
                            <?php foreach ($critical_metrics as $metric): ?>
                            <div class="monitoring-card">
                                <div class="monitoring-card-header">
                                    <h3 class="monitoring-card-title">
                                        <?php echo htmlspecialchars($metric['first_name'] . ' ' . $metric['last_name']); ?>
                                    </h3>
                                    <span class="monitoring-card-date">
                                        <?php echo date('M d, Y', strtotime($metric['created_at'])); ?>
                                    </span>
                                </div>
                                <ul class="metric-list">
                                    <?php if ($metric['blood_pressure_systolic'] > 0): ?>
                                    <li class="metric-item">
                                        <span class="metric-name">Blood Pressure</span>
                                        <span class="metric-value <?php echo ($metric['blood_pressure_systolic'] > 140 || $metric['blood_pressure_diastolic'] > 90) ? 'critical' : ''; ?>">
                                            <?php echo $metric['blood_pressure_systolic']; ?>/<?php echo $metric['blood_pressure_diastolic']; ?> mmHg
                                        </span>
                                    </li>
                                    <?php endif; ?>
                                    
                                    <?php if ($metric['heart_rate'] > 0): ?>
                                    <li class="metric-item">
                                        <span class="metric-name">Heart Rate</span>
                                        <span class="metric-value <?php echo ($metric['heart_rate'] > 100 || $metric['heart_rate'] < 60) ? 'critical' : ''; ?>">
                                            <?php echo $metric['heart_rate']; ?> bpm
                                        </span>
                                    </li>
                                    <?php endif; ?>
                                    
                                    <?php if ($metric['blood_sugar'] > 0): ?>
                                    <li class="metric-item">
                                        <span class="metric-name">Blood Sugar</span>
                                        <span class="metric-value <?php echo ($metric['blood_sugar'] > 180) ? 'critical' : ''; ?>">
                                            <?php echo $metric['blood_sugar']; ?> mg/dL
                                        </span>
                                    </li>
                                    <?php endif; ?>
                                    
                                    <?php if ($metric['temperature'] > 0): ?>
                                    <li class="metric-item">
                                        <span class="metric-name">Temperature</span>
                                        <span class="metric-value <?php echo ($metric['temperature'] > 38) ? 'critical' : ''; ?>">
                                            <?php echo $metric['temperature']; ?> °C
                                        </span>
                                    </li>
                                    <?php endif; ?>
                                </ul>
                                <a href="patient-details.php?id=<?php echo $metric['user_id']; ?>" class="btn btn-outline" style="width: 100%; margin-top: 10px;">
                                    View Patient
                                </a>
                            </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                </div>
            </div>
            <?php endif; ?>
            
            <div class="card">
                <div class="card-header">
                    <h2 class="card-title">
                        <i class="fas fa-users"></i>
                        Patient List
                    </h2>
                    <div class="header-actions">
                        <button class="btn btn-outline" id="exportBtn">
                            <i class="fas fa-file-export"></i>
                            Export
                        </button>
                    </div>
                </div>
                
                <div class="card-body">
                    <form action="patients.php" method="get" class="search-filter">
                        <div class="search-input">
                            <i class="fas fa-search"></i>
                            <input type="text" name="search" placeholder="Search patients..." value="<?php echo htmlspecialchars($search); ?>">
                        </div>
                        
                        <div class="filter-select">
                            <select name="filter" id="filterSelect">
                                <option value="all" <?php echo $filter === 'all' ? 'selected' : ''; ?>>All Patients</option>
                                <option value="recent" <?php echo $filter === 'recent' ? 'selected' : ''; ?>>Recent (30 days)</option>
                                <option value="upcoming" <?php echo $filter === 'upcoming' ? 'selected' : ''; ?>>With Upcoming Appointments</option>
                                <option value="critical" <?php echo $filter === 'critical' ? 'selected' : ''; ?>>Critical Health Metrics</option>
                            </select>
                        </div>
                        
                        <button type="submit" class="btn btn-primary">Apply Filter</button>
                    </form>
                    
                    <?php if (empty($patients)): ?>
                        <div class="empty-state">
                            <div class="empty-state-icon">
                                <i class="fas fa-user-injured"></i>
                            </div>
                            <p class="empty-state-text">No patients found matching your criteria.</p>
                            <a href="patients.php" class="btn btn-primary">View All Patients</a>
                        </div>
                    <?php else: ?>
                        <div class="table-container">
                            <table class="data-table">
                                <thead>
                                    <tr>
                                        <th>Patient</th>
                                        <th>Age/Gender</th>
                                        <th>Contact</th>
                                        <th>Last Visit</th>
                                        <th>Status</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($patients as $patient): ?>
                                    <tr>
                                        <td>
                                            <div class="patient-info">
                                                <div class="patient-avatar">
                                                    <?php if (!empty($patient['profile_picture']) && !empty($patient['profile_picture_type'])): ?>
                                                        <img src="data:<?php echo $patient['profile_picture_type']; ?>;base64,<?php echo base64_encode($patient['profile_picture']); ?>" alt="Patient Avatar">
                                                    <?php else: ?>
                                                        <?php echo strtoupper(substr($patient['first_name'], 0, 1)); ?>
                                                    <?php endif; ?>
                                                </div>
                                                <div class="patient-details">
                                                    <span class="patient-name"><?php echo htmlspecialchars($patient['first_name'] . ' ' . $patient['last_name']); ?></span>
                                                    <span class="patient-email"><?php echo htmlspecialchars($patient['email']); ?></span>
                                                </div>
                                            </div>
                                        </td>
                                        <td>
                                            <?php 
                                            $age = isset($patient['date_of_birth']) ? date_diff(date_create($patient['date_of_birth']), date_create('today'))->y : 'N/A'; 
                                            echo $age . ' / ' . (isset($patient['gender']) ? ucfirst($patient['gender']) : 'N/A'); 
                                            ?>
                                        </td>
                                        <td>
                                            <?php echo !empty($patient['phone']) ? htmlspecialchars($patient['phone']) : 'Not provided'; ?>
                                        </td>
                                        <td>
                                            <?php echo $patient['last_appointment_date'] ? date('M d, Y', strtotime($patient['last_appointment_date'])) : 'Never'; ?>
                                        </td>
                                        <td>
                                            <?php
                                            // Determine patient status based on appointments and health metrics
                                            $stmt = $pdo->prepare("
                                                SELECT * FROM health_metrics
                                                WHERE user_id = :user_id
                                                ORDER BY created_at DESC
                                                LIMIT 1
                                            ");
                                            $stmt->execute(['user_id' => $patient['id']]);
                                            $latest_metrics = $stmt->fetch(PDO::FETCH_ASSOC);
                                            
                                            $status = 'Regular';
                                            $status_class = 'badge-primary';
                                            
                                            if ($latest_metrics) {
                                                if (
                                                    $latest_metrics['blood_pressure_systolic'] > 140 || 
                                                    $latest_metrics['blood_pressure_diastolic'] > 90 || 
                                                    $latest_metrics['heart_rate'] > 100 || 
                                                    $latest_metrics['heart_rate'] < 60 || 
                                                    $latest_metrics['blood_sugar'] > 180
                                                ) {
                                                    $status = 'Needs Attention';
                                                    $status_class = 'badge-danger';
                                                }
                                            }
                                            
                                            // Check if patient has upcoming appointments
                                            $stmt = $pdo->prepare("
                                                SELECT COUNT(*) FROM appointments
                                                WHERE user_id = :user_id
                                                AND date >= CURDATE()
                                                AND status = 'scheduled'
                                            ");
                                            $stmt->execute(['user_id' => $patient['id']]);
                                            $upcoming_count = $stmt->fetchColumn();
                                            
                                            if ($upcoming_count > 0 && $status === 'Regular') {
                                                $status = 'Active';
                                                $status_class = 'badge-success';
                                            }
                                            ?>
                                            <span class="status-badge <?php echo $status_class; ?>">
                                                <?php echo $status; ?>
                                            </span>
                                        </td>
                                        <td>
                                            <div class="action-buttons">
                                                <a href="patient-details.php?id=<?php echo $patient['id']; ?>" class="btn-icon" title="View Patient Details">
                                                    <i class="fas fa-eye"></i>
                                                </a>
                                                <a href="appointments.php?patient_id=<?php echo $patient['id']; ?>" class="btn-icon" title="View Appointments">
                                                    <i class="fas fa-calendar-alt"></i>
                                                </a>
                                                <a href="health-records.php?patient_id=<?php echo $patient['id']; ?>" class="btn-icon" title="View Health Records">
                                                    <i class="fas fa-heartbeat"></i>
                                                </a>
                                                <a href="prescriptions.php?patient_id=<?php echo $patient['id']; ?>" class="btn-icon" title="View Prescriptions">
                                                    <i class="fas fa-prescription"></i>
                                                </a>
                                            </div>
                                        </td>
                                    </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                        
                        <?php if ($total_pages > 1): ?>
                        <div class="pagination">
                            <?php if ($page > 1): ?>
                                <a href="patients.php?page=1&search=<?php echo urlencode($search); ?>&filter=<?php echo $filter; ?>">
                                    <i class="fas fa-angle-double-left"></i>
                                </a>
                                <a href="patients.php?page=<?php echo $page - 1; ?>&search=<?php echo urlencode($search); ?>&filter=<?php echo $filter; ?>">
                                    <i class="fas fa-angle-left"></i>
                                </a>
                            <?php endif; ?>
                            
                            <?php
                            $start_page = max(1, $page - 2);
                            $end_page = min($total_pages, $start_page + 4);
                            
                            for ($i = $start_page; $i <= $end_page; $i++): ?>
                                <a href="patients.php?page=<?php echo $i; ?>&search=<?php echo urlencode($search); ?>&filter=<?php echo $filter; ?>" class="<?php echo $i === $page ? 'active' : ''; ?>">
                                    <?php echo $i; ?>
                                </a>
                            <?php endfor; ?>
                            
                            <?php if ($page < $total_pages): ?>
                                <a href="patients.php?page=<?php echo $page + 1; ?>&search=<?php echo urlencode($search); ?>&filter=<?php echo $filter; ?>">
                                    <i class="fas fa-angle-right"></i>
                                </a>
                                <a href="patients.php?page=<?php echo $total_pages; ?>&search=<?php echo urlencode($search); ?>&filter=<?php echo $filter; ?>">
                                    <i class="fas fa-angle-double-right"></i>
                                </a>
                            <?php endif; ?>
                        </div>
                        <?php endif; ?>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </main>
    
    <?php include_once "../elderly/includes/footer.php"; ?>
    <?php include_once "includes/mobile_nav.php"; ?>
    
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Auto-submit when filter changes
            document.getElementById('filterSelect').addEventListener('change', function() {
                this.form.submit();
            });
            
            // Export functionality
            document.getElementById('exportBtn').addEventListener('click', function() {
                // Simple CSV export
                const table = document.querySelector('.data-table');
                if (!table) return;
                
                const rows = Array.from(table.querySelectorAll('tr'));
                const csvContent = rows.map(row => {
                    const cells = Array.from(row.querySelectorAll('th, td'));
                    return cells.map(cell => {
                        // Get text content while handling nested elements
                        let content = '';
                        
                        if (cell.querySelector('.patient-details')) {
                            const details = cell.querySelector('.patient-details');
                            const name = details.querySelector('.patient-name');
                            const email = details.querySelector('.patient-email');
                            content = `${name ? name.textContent : ''} (${email ? email.textContent : ''})`;
                        } else if (cell.querySelector('.status-badge')) {
                            content = cell.querySelector('.status-badge').textContent.trim();
                        } else {
                            content = cell.textContent.trim();
                        }
                        
                        // Escape quotes and wrap in quotes
                        return `"${content.replace(/"/g, '""')}"`;
                    }).join(',');
                }).join('\n');
                
                // Create download link
                const blob = new Blob([csvContent], { type: 'text/csv;charset=utf-8;' });
                const url = URL.createObjectURL(blob);
                const link = document.createElement('a');
                link.setAttribute('href', url);
                link.setAttribute('download', 'patients-list.csv');
                link.style.visibility = 'hidden';
                document.body.appendChild(link);
                link.click();
                document.body.removeChild(link);
            });
        });
    </script>
</body>
</html>