<?php
// filepath: c:\xampp\htdocs\Astha\elderly\medication-tracking\medication-tracking.php
session_start();

// Check if user is logged in
if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true) {
    header('Location: ../../login_user/login_user.php');
    exit();
}

// Get user information
$user_id = $_SESSION['user_id'];
$user_name = $_SESSION['user_name'];
$user_role = $_SESSION['user_role'];

// Include database connection
require_once '../../signup_user/config.php';

// Ensure database connection is established
if (!isset($pdo)) {
    try {
        $host = 'localhost';
        $dbname = 'shohochor_db';
        $username = 'root';
        $password = '';
        
        $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8mb4", $username, $password);
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    } catch (PDOException $e) {
        die("Database connection failed: " . $e->getMessage());
    }
}

// Get today's medications
function getTodayMedications($pdo, $user_id) {
    $today = date('Y-m-d');
    
    $stmt = $pdo->prepare("
        SELECT m.*, 
               CASE 
                   WHEN ml.status = 'taken' THEN 'taken'
                   WHEN ml.status = 'skipped' THEN 'skipped'
                   WHEN ml.status = 'missed' THEN 'missed'
                   WHEN TIME(NOW()) > TIME(ml.scheduled_time) AND ml.status = 'pending' THEN 'overdue'
                   ELSE 'pending'
               END as current_status,
               ml.scheduled_time,
               ml.taken_time,
               ml.id as log_id
        FROM medications m
        LEFT JOIN medication_logs ml ON m.id = ml.medication_id AND DATE(ml.scheduled_time) = ?
        WHERE m.user_id = ? AND m.is_active = 1
        AND (m.end_date IS NULL OR m.end_date >= ?)
        ORDER BY ml.scheduled_time ASC
    ");
    
    $stmt->execute([$today, $user_id, $today]);
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

// Get medication history
function getMedicationHistory($pdo, $user_id) {
    $start_date = date('Y-m-d', strtotime('-7 days'));
    $end_date = date('Y-m-d');
    
    $stmt = $pdo->prepare("
        SELECT DATE(ml.scheduled_time) as date,
               COUNT(*) as total_scheduled,
               SUM(CASE WHEN ml.status = 'taken' THEN 1 ELSE 0 END) as taken,
               SUM(CASE WHEN ml.status = 'missed' THEN 1 ELSE 0 END) as missed,
               SUM(CASE WHEN ml.status = 'skipped' THEN 1 ELSE 0 END) as skipped,
               SUM(CASE WHEN ml.status = 'pending' THEN 1 ELSE 0 END) as pending
        FROM medication_logs ml
        WHERE ml.user_id = ? 
        AND DATE(ml.scheduled_time) BETWEEN ? AND ?
        GROUP BY DATE(ml.scheduled_time)
        ORDER BY DATE(ml.scheduled_time) DESC
    ");
    
    $stmt->execute([$user_id, $start_date, $end_date]);
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

// Get medication details
function getMedicationDetails($pdo, $user_id) {
    $stmt = $pdo->prepare("
        SELECT m.*,
               CASE 
                   WHEN m.remaining_pills <= 5 THEN 'low'
                   WHEN m.remaining_pills <= 10 THEN 'medium'
                   ELSE 'good'
               END as refill_status,
               CASE 
                   WHEN m.end_date IS NOT NULL THEN DATEDIFF(m.end_date, CURDATE())
                   ELSE NULL
               END as days_remaining
        FROM medications m
        WHERE m.user_id = ? AND m.is_active = 1
        ORDER BY m.created_at DESC
        LIMIT 5
    ");
    
    $stmt->execute([$user_id]);
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

// Create today's schedule if missing
function createTodayScheduleIfMissing($pdo, $user_id) {
    if (!$pdo) {
        error_log('Error: Database connection not available in createTodayScheduleIfMissing()');
        return;
    }
    
    $today = date('Y-m-d');
    
    $stmt = $pdo->prepare("
        SELECT m.* FROM medications m
        LEFT JOIN medication_logs ml ON m.id = ml.medication_id AND DATE(ml.scheduled_time) = ?
        WHERE m.user_id = ? AND m.is_active = 1 AND ml.id IS NULL
    ");
    $stmt->execute([$today, $user_id]);
    $missing_schedules = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    foreach ($missing_schedules as $med) {
        $times = [];
        if ($med['morning']) $times[] = '08:00:00';
        if ($med['afternoon']) $times[] = '14:00:00';
        if ($med['evening']) $times[] = '20:00:00';
        if ($med['night']) $times[] = '22:00:00';
        
        foreach ($times as $time) {
            $stmt = $pdo->prepare("
                INSERT INTO medication_logs 
                (medication_id, user_id, medication_name, dosage, scheduled_time, status)
                VALUES (?, ?, ?, ?, ?, 'pending')
            ");
            $stmt->execute([$med['id'], $user_id, $med['name'], $med['dosage'], $today . ' ' . $time]);
        }
    }
}

// Check if $pdo is properly initialized before using it
if ($pdo) {
    // Create schedules for today if missing
    createTodayScheduleIfMissing($pdo, $user_id);

    // Get the data
    $todayMedications = getTodayMedications($pdo, $user_id);
    $medicationHistory = getMedicationHistory($pdo, $user_id);
    $medicationDetails = getMedicationDetails($pdo, $user_id);
} else {
    // Handle the case where database connection failed
    $todayMedications = [];
    $medicationHistory = [];
    $medicationDetails = [];
    
    echo '<div class="alert-banner error">
            <div class="alert-content">
                <svg class="alert-icon" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="white">
                    <path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/>
                    <path d="M12 8v4"/>
                    <path d="M12 16h.01"/>
                </svg>
                <span class="alert-message">Database connection error. Please try again later or contact support.</span>
            </div>
          </div>';
}

// Group today's medications by time
$groupedMedications = [
    'morning' => [],
    'afternoon' => [],
    'evening' => [],
    'night' => []
];

foreach ($todayMedications as $med) {
    if ($med['scheduled_time']) {
        $hour = (int)date('H', strtotime($med['scheduled_time']));
        if ($hour >= 6 && $hour < 12) {
            $groupedMedications['morning'][] = $med;
        } elseif ($hour >= 12 && $hour < 17) {
            $groupedMedications['afternoon'][] = $med;
        } elseif ($hour >= 17 && $hour < 22) {
            $groupedMedications['evening'][] = $med;
        } else {
            $groupedMedications['night'][] = $med;
        }
    }
}

// Count pending medications
$pendingCount = 0;
foreach ($todayMedications as $med) {
    if ($med['current_status'] === 'pending' || $med['current_status'] === 'overdue') {
        $pendingCount++;
    }
}
?>

<!DOCTYPE html>
<html lang="en">
    <head>
        <link rel="preload" as="script" href="https://cdn.ampproject.org/v0.js">
        <link rel="stylesheet" href="./style.css">
        <script async src="https://cdn.ampproject.org/v0.js"></script>
        <script async custom-element="amp-sidebar" src="https://cdn.ampproject.org/v0/amp-sidebar-0.1.js"></script>
        <script async custom-element="amp-analytics" src="https://cdn.ampproject.org/v0/amp-analytics-0.1.js"></script>
        <script type="text/javascript" src="https://translate.google.com/translate_a/element.js?cb=googleTranslateElementInit"></script>
        <script type="text/javascript">
            function googleTranslateElementInit() {
                new google.translate.TranslateElement({
                    pageLanguage: 'en',
                    includedLanguages: 'bn,en',
                    layout: google.translate.TranslateElement.InlineLayout.SIMPLE
                }, 'google_translate_element');
                
                new google.translate.TranslateElement({
                    pageLanguage: 'en',
                    includedLanguages: 'bn,en',
                    layout: google.translate.TranslateElement.InlineLayout.SIMPLE
                }, 'mobile-google-translate');
            }
        </script>

        <meta charset="utf-8"/>
        <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
        <title>Shohochor - Medication Tracking</title>
        <meta name="description" content="Track, manage and get reminders for your medications with Shohochor's easy-to-use medication tracking system.">
        <link href="./medication-tracking.php" rel="canonical">
        <link rel="apple-touch-icon" sizes="180x180" href="/images/favicon/apple-touch-icon.png">
        <link rel="icon" type="image/png" sizes="32x32" href="/images/favicon/favicon-32x32.png">
        <link rel="icon" type="image/png" sizes="16x16" href="/images/favicon/favicon-16x16.png">
        <link rel="manifest" href="/images/favicon/site.webmanifest.json">
        <link rel="mask-icon" href="/images/favicon/safari-pinned-tab.svg" color="#5bbad5">
        <meta name="msapplication-TileColor" content="#ffffff">
        <meta name="theme-color" content="#ffffff">
        <link rel="preconnect" href="https://fonts.googleapis.com">
        <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
        <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@400;500;600;700;800&family=Open+Sans:wght@400;600&display=swap" rel="stylesheet">
        <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
        <link rel="stylesheet" href="../includes/style.css">
        
        <style>
            /* Keep all existing styles from the original file */
            .medication-dashboard {
                margin-bottom: 30px;
            }
            
            .medication-section {
                background-color: white;
                border-radius: 16px;
                padding: 30px;
                box-shadow: 0 4px 15px rgba(0,0,0,0.08);
                margin-bottom: 30px;
                border: 1px solid rgba(0,150,173,0.1);
                animation: fadeIn 0.5s ease-out;
            }
            
            .section-heading {
                font-size: 1.8rem;
                color: #2d3748;
                margin-bottom: 25px;
                text-align: center;
                position: relative;
            }
            
            .section-heading:after {
                content: '';
                position: absolute;
                width: 80px;
                height: 3px;
                background: #0096ad;
                bottom: -10px;
                left: 50%;
                transform: translateX(-50%);
            }
            
            .medication-list {
                list-style: none;
                padding: 0;
                margin: 0;
            }
            
            .medication-item {
                background-color: #f8f9fa;
                border-radius: 12px;
                padding: 20px;
                margin-bottom: 15px;
                border-left: 5px solid #0096ad;
                display: flex;
                justify-content: space-between;
                align-items: center;
                transition: transform 0.3s ease, box-shadow 0.3s ease;
            }
            
            .medication-item:hover {
                transform: translateX(5px);
                box-shadow: 0 5px 15px rgba(0,0,0,0.08);
            }
            
            .medication-info {
                flex-grow: 1;
            }
            
            .medication-name {
                font-weight: 600;
                font-size: 1.2rem;
                color: #2d3748;
                margin-bottom: 5px;
                display: flex;
                align-items: center;
                gap: 8px;
            }
            
            .medication-name i {
                color: #0096ad;
            }
            
            .medication-details {
                color: #4a5568;
                font-size: 0.95rem;
                margin-bottom: 8px;
            }
            
            .medication-schedule {
                color: #718096;
                font-size: 0.9rem;
                display: flex;
                align-items: center;
                gap: 5px;
            }
            
            .medication-instructions {
                margin-top: 8px;
                color: #4a5568;
                font-size: 0.9rem;
                font-style: italic;
            }
            
            .medication-action {
                flex-shrink: 0;
                margin-left: 15px;
            }
            
            .medication-button {
                background-color: #0096ad;
                color: white;
                border: none;
                border-radius: 50px;
                padding: 10px 20px;
                font-weight: 600;
                font-size: 0.9rem;
                cursor: pointer;
                transition: all 0.3s ease;
                display: flex;
                align-items: center;
                gap: 8px;
            }
            
            .medication-button:hover {
                background-color: #007489;
                transform: translateY(-2px);
                box-shadow: 0 4px 10px rgba(0,150,173,0.3);
            }
            
            .medication-button.taken {
                background-color: #48bb78;
            }
            
            .medication-button.overdue {
                background-color: #e53e3e;
                animation: pulse 2s infinite;
            }
            
            .medication-status {
                display: inline-block;
                padding: 5px 15px;
                border-radius: 50px;
                font-size: 0.85rem;
                font-weight: 600;
                margin-left: 15px;
            }
            
            .status-taken {
                background-color: #c6f6d5;
                color: #22543d;
            }
            
            .status-upcoming {
                background-color: #e2e8f0;
                color: #4a5568;
            }
            
            .status-missed {
                background-color: #fed7d7;
                color: #822727;
                animation: pulse 2s infinite;
            }
            
            .status-overdue {
                background-color: #feb2b2;
                color: #742a2a;
                animation: pulse 2s infinite;
            }
            
            @keyframes pulse {
                0% {
                    box-shadow: 0 0 0 0 rgba(130, 39, 39, 0.4);
                }
                70% {
                    box-shadow: 0 0 0 8px rgba(130, 39, 39, 0);
                }
                100% {
                    box-shadow: 0 0 0 0 rgba(130, 39, 39, 0);
                }
            }
            
            .medication-time-group {
                margin-bottom: 30px;
            }
            
            .time-heading {
                background-color: #0096ad;
                color: white;
                padding: 10px 20px;
                border-radius: 8px 8px 0 0;
                font-weight: 600;
                font-size: 1.1rem;
                display: flex;
                align-items: center;
                gap: 10px;
                margin-bottom: 15px;
            }
            
            .history-grid {
                display: grid;
                grid-template-columns: repeat(7, 1fr);
                gap: 15px;
                margin-bottom: 30px;
            }
            
            .history-day {
                background-color: #f8f9fa;
                border-radius: 10px;
                padding: 15px 10px;
                text-align: center;
                box-shadow: 0 2px 5px rgba(0,0,0,0.05);
            }
            
            .day-name {
                font-weight: 600;
                color: #2d3748;
                margin-bottom: 5px;
                font-size: 0.95rem;
            }
            
            .day-date {
                color: #718096;
                font-size: 0.85rem;
                margin-bottom: 10px;
            }
            
            .day-status {
                display: flex;
                flex-direction: column;
                align-items: center;
                gap: 5px;
            }
            
            .status-dot {
                width: 15px;
                height: 15px;
                border-radius: 50%;
                display: inline-block;
            }
            
            .dot-taken {
                background-color: #48bb78;
            }
            
            .dot-missed {
                background-color: #e53e3e;
            }
            
            .dot-skipped {
                background-color: #ed8936;
            }
            
            .dot-upcoming {
                background-color: #a0aec0;
                border: 1px solid #718096;
            }
            
            .status-label {
                font-size: 0.8rem;
                color: #4a5568;
            }
            
            .medication-actions {
                display: flex;
                justify-content: center;
                gap: 15px;
                margin-top: 20px;
            }
            
            .action-button {
                background-color: #0096ad;
                color: white;
                border: none;
                border-radius: 8px;
                padding: 15px 25px;
                font-weight: 600;
                font-size: 1rem;
                cursor: pointer;
                transition: all 0.3s ease;
                display: flex;
                align-items: center;
                gap: 10px;
                box-shadow: 0 4px 10px rgba(0,150,173,0.2);
            }
            
            .action-button:hover {
                background-color: #007489;
                transform: translateY(-2px);
                box-shadow: 0 6px 15px rgba(0,150,173,0.3);
            }
            
            .action-button.secondary {
                background-color: white;
                color: #0096ad;
                border: 2px solid #0096ad;
                box-shadow: none;
            }
            
            .action-button.secondary:hover {
                background-color: #f0fafc;
                transform: translateY(-2px);
                box-shadow: 0 4px 10px rgba(0,150,173,0.1);
            }
            
            .medication-detail-card {
                background-color: #f8f9fa;
                border-radius: 12px;
                padding: 25px;
                margin-bottom: 20px;
                border-left: 5px solid #0096ad;
                animation: fadeIn 0.5s ease-out;
            }
            
            .medication-row {
                display: flex;
                margin-bottom: 12px;
                align-items: center;
            }
            
            .medication-label {
                width: 180px;
                font-weight: 600;
                color: #4a5568;
                display: flex;
                align-items: center;
                gap: 8px;
            }
            
            .medication-label i {
                color: #0096ad;
            }
            
            .medication-value {
                color: #2d3748;
                flex-grow: 1;
            }
            
            .refill-info {
                margin-top: 15px;
                padding-top: 15px;
                border-top: 1px dashed #cbd5e0;
                display: flex;
                justify-content: space-between;
                align-items: center;
            }
            
            .refill-count {
                padding: 5px 15px;
                background-color: #ebf8ff;
                color: #2b6cb0;
                border-radius: 50px;
                font-weight: 600;
                font-size: 0.9rem;
            }
            
            .refill-alert {
                padding: 5px 15px;
                background-color: #fed7d7;
                color: #822727;
                border-radius: 50px;
                font-weight: 600;
                font-size: 0.9rem;
                display: flex;
                align-items: center;
                gap: 5px;
            }
            
            .refill-normal {
                padding: 5px 15px;
                background-color: #c6f6d5;
                color: #22543d;
                border-radius: 50px;
                font-weight: 600;
                font-size: 0.9rem;
                display: flex;
                align-items: center;
                gap: 5px;
            }
            
            /* Modal styles */
            .modal {
                display: none;
                position: fixed;
                top: 0;
                left: 0;
                width: 100%;
                height: 100%;
                background-color: rgba(0, 0, 0, 0.5);
                z-index: 1000;
                opacity: 0;
                transition: opacity 0.3s ease;
                justify-content: center;
                align-items: center;
            }
            
            .modal.show {
                display: flex;
                opacity: 1;
            }
            
            .modal-content {
                background-color: #ffffff;
                border-radius: 16px;
                width: 90%;
                max-width: 600px;
                box-shadow: 0 10px 30px rgba(0, 0, 0, 0.2);
                padding: 30px;
                position: relative;
                transform: translateY(-30px);
                transition: transform 0.3s ease;
                max-height: 80vh;
                overflow-y: auto;
            }
            
            .modal.show .modal-content {
                transform: translateY(0);
            }
            
            .modal-close {
                position: absolute;
                top: 20px;
                right: 20px;
                background: none;
                border: none;
                font-size: 1.5rem;
                cursor: pointer;
                color: #718096;
                transition: color 0.3s ease;
            }
            
            .modal-close:hover {
                color: #e53e3e;
            }
            
            .modal-title {
                color: #2d3748;
                margin-top: 0;
                margin-bottom: 20px;
                text-align: center;
            }
            
            .form-group {
                margin-bottom: 20px;
            }
            
            .form-label {
                font-weight: 600;
                color: #4a5568;
                margin-bottom: 8px;
                display: block;
            }
            
            .form-control {
                width: 100%;
                padding: 12px 15px;
                border: 1px solid #cbd5e0;
                border-radius: 8px;
                font-size: 1rem;
                transition: border-color 0.3s ease;
                box-sizing: border-box;
            }
            
            .form-control:focus {
                outline: none;
                border-color: #0096ad;
                box-shadow: 0 0 0 3px rgba(0, 150, 173, 0.2);
            }
            
            .form-row {
                display: flex;
                gap: 15px;
            }
            
            .form-col {
                flex: 1;
            }
            
            .form-actions {
                display: flex;
                justify-content: center;
                gap: 15px;
                margin-top: 30px;
            }
            
            .form-button {
                padding: 12px 25px;
                border-radius: 8px;
                font-weight: 600;
                cursor: pointer;
                transition: all 0.3s ease;
            }
            
            .form-button.primary {
                background-color: #0096ad;
                color: white;
                border: none;
            }
            
            .form-button.primary:hover {
                background-color: #007489;
            }
            
            .form-button.secondary {
                background-color: white;
                color: #4a5568;
                border: 1px solid #cbd5e0;
            }
            
            .form-button.secondary:hover {
                background-color: #f7fafc;
            }
            
            .checkbox-group {
                display: flex;
                gap: 15px;
                flex-wrap: wrap;
            }
            
            .checkbox-item {
                display: flex;
                align-items: center;
                gap: 8px;
            }
            
            .checkbox-item input[type="checkbox"] {
                width: 18px;
                height: 18px;
            }
            
            /* Toast notification */
            .toast {
                position: fixed;
                top: 20px;
                right: 20px;
                background-color: #48bb78;
                color: white;
                padding: 15px 20px;
                border-radius: 8px;
                box-shadow: 0 4px 15px rgba(0,0,0,0.2);
                z-index: 10000;
                opacity: 0;
                transform: translateX(100%);
                transition: all 0.3s ease;
            }
            
            .toast.show {
                opacity: 1;
                transform: translateX(0);
            }
            
            .toast.error {
                background-color: #e53e3e;
            }
            
            /* Prescription modal styles */
            .prescriptions-list {
                max-height: 400px;
                overflow-y: auto;
                margin-bottom: 20px;
            }

            .prescription-card {
                background-color: #f8f9fa;
                border-radius: 12px;
                padding: 20px;
                margin-bottom: 15px;
                border-left: 5px solid #0096ad;
                transition: all 0.3s ease;
            }

            .prescription-card.active {
                background-color: #e6f7f9;
                box-shadow: 0 5px 15px rgba(0,0,0,0.1);
            }

            .prescription-header {
                display: flex;
                justify-content: space-between;
                align-items: center;
                margin-bottom: 10px;
                border-bottom: 1px solid #e2e8f0;
                padding-bottom: 10px;
            }

            .prescription-header h3 {
                margin: 0;
                color: #2d3748;
                font-size: 1.2rem;
            }

            .prescription-date {
                color: #718096;
                font-size: 0.9rem;
            }

            .prescription-info {
                margin-bottom: 15px;
                font-size: 0.95rem;
                color: #4a5568;
            }

            .prescription-medications h4 {
                color: #2d3748;
                margin-bottom: 10px;
                font-size: 1rem;
            }

            .medications-list {
                list-style: none;
                padding: 0;
                margin: 0 0 15px 0;
            }

            .medications-list li {
                margin-bottom: 8px;
                padding-left: 25px;
                position: relative;
            }

            .medication-checkbox {
                display: flex;
                align-items: center;
                gap: 10px;
                cursor: pointer;
            }

            .medication-checkbox input[type="checkbox"] {
                width: 18px;
                height: 18px;
            }

            .no-medications, .no-results, .error-message {
                text-align: center;
                padding: 20px;
                color: #718096;
                font-style: italic;
            }

            .loading-spinner {
                text-align: center;
                padding: 30px;
                color: #4a5568;
            }

            .loading-spinner i {
                font-size: 2rem;
                margin-bottom: 15px;
                color: #0096ad;
            }

            .select-all-btn {
                padding: 8px 15px;
                font-size: 0.9rem;
                margin-top: 10px;
            }

            /* Responsive design */
            @media (max-width: 768px) {
                .section-heading {
                    font-size: 1.5rem;
                }
                
                .medication-item {
                    flex-direction: column;
                    align-items: flex-start;
                }
                
                .medication-action {
                    margin-left: 0;
                    margin-top: 15px;
                    width: 100%;
                }
                
                .medication-button {
                    width: 100%;
                    justify-content: center;
                }
                
                .medication-status {
                    margin-left: 0;
                    margin-top: 10px;
                }
                
                .medication-actions {
                    flex-direction: column;
                    gap: 10px;
                }
                
                .action-button {
                    width: 100%;
                    justify-content: center;
                }
                
                .history-grid {
                    grid-template-columns: repeat(3, 1fr);
                }
                
                .form-row {
                    flex-direction: column;
                    gap: 20px;
                }
                
                .checkbox-group {
                    flex-direction: column;
                    gap: 10px;
                }
            }
        </style>
    </head>
    <body>
        <?php include '../includes/nav.php'; ?>
        <?php include '../includes/mobile_nav.php'; ?>
        
        <main>
            <?php if ($pendingCount > 0): ?>
            <div class="alert-banner">
                <div class="alert-content">
                    <svg class="alert-icon" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="white">
                        <path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/>
                        <path d="M12 8v4"/>
                        <path d="M12 16h.01"/>
                    </svg>
                    <span class="alert-message">Reminder: You have <?php echo $pendingCount; ?> medication<?php echo $pendingCount > 1 ? 's' : ''; ?> due today</span>
                </div>
                <button class="alert-action" onclick="scrollToTodayMedications()">View Details</button>
            </div>
            <?php endif; ?>
            
            <!-- Medication Dashboard -->
            <div class="medication-dashboard">
                <h1 style="margin-bottom: 20px; color: #2d3748; font-size: 2rem; text-align: center;">Your Medication Dashboard</h1>
                
                <!-- Today's Medications Section -->
                <div class="medication-section" id="todayMedications">
                    <h2 class="section-heading">Today's Medications</h2>
                    
                    <?php if (empty($todayMedications)): ?>
                        <div style="text-align: center; padding: 40px; color: #718096;">
                            <i class="fas fa-pills" style="font-size: 3rem; margin-bottom: 20px; opacity: 0.3;"></i>
                            <p style="font-size: 1.2rem; margin-bottom: 10px;">No medications scheduled for today</p>
                            <p>You're all caught up! Remember to add any new medications prescribed by your doctor.</p>
                        </div>
                    <?php else: ?>
                        <?php 
                        $timeGroups = [
                            'morning' => ['title' => 'Morning (6:00 AM - 12:00 PM)', 'icon' => 'fas fa-sun'],
                            'afternoon' => ['title' => 'Afternoon (12:00 PM - 5:00 PM)', 'icon' => 'fas fa-cloud-sun'],
                            'evening' => ['title' => 'Evening (5:00 PM - 10:00 PM)', 'icon' => 'fas fa-moon'],
                            'night' => ['title' => 'Night (10:00 PM - 6:00 AM)', 'icon' => 'fas fa-moon']
                        ];
                        
                        foreach ($timeGroups as $timeKey => $timeInfo):
                            if (!empty($groupedMedications[$timeKey])):
                        ?>
                        <div class="medication-time-group">
                            <div class="time-heading">
                                <i class="<?php echo $timeInfo['icon']; ?>"></i>
                                <?php echo $timeInfo['title']; ?>
                            </div>
                            <ul class="medication-list">
                                <?php foreach ($groupedMedications[$timeKey] as $med): ?>
                                <li class="medication-item">
                                    <div class="medication-info">
                                        <div class="medication-name">
                                            <i class="fas fa-pills"></i>
                                            <?php echo htmlspecialchars($med['name']); ?> <?php echo htmlspecialchars($med['dosage']); ?>
                                        </div>
                                        <div class="medication-details"><?php echo htmlspecialchars($med['frequency']); ?></div>
                                        <div class="medication-schedule">
                                            <i class="far fa-clock"></i>
                                            Scheduled: <?php echo date('g:i A', strtotime($med['scheduled_time'])); ?>
                                        </div>
                                        <?php if ($med['instructions']): ?>
                                        <div class="medication-instructions">
                                            <?php echo htmlspecialchars($med['instructions']); ?>
                                        </div>
                                        <?php endif; ?>
                                    </div>
                                    <div class="medication-action">
                                        <?php 
                                        $status = $med['current_status'];
                                        if ($status === 'pending' && $med['scheduled_time']) {
                                            $now = new DateTime();
                                            $scheduledTime = new DateTime($med['scheduled_time']);
                                            if ($now > $scheduledTime) {
                                                $status = 'overdue';
                                            }
                                        }
                                        ?>
                                        
                                        <?php if ($status === 'taken'): ?>
                                            <span class="medication-status status-taken">✓ Taken<?php echo $med['taken_time'] ? ' at ' . date('g:i A', strtotime($med['taken_time'])) : ''; ?></span>
                                        <?php elseif ($status === 'skipped'): ?>
                                            <span class="medication-status status-skipped">Skipped</span>
                                        <?php elseif ($status === 'missed'): ?>
                                            <span class="medication-status status-missed">Missed</span>
                                        <?php elseif ($status === 'overdue'): ?>
                                            <button class="medication-button overdue" onclick="markMedication(<?php echo $med['log_id']; ?>, 'taken')">
                                                <i class="fas fa-exclamation-triangle"></i>
                                                Overdue - Take Now
                                            </button>
                                        <?php else: ?>
                                            <button class="medication-button" onclick="markMedication(<?php echo $med['log_id']; ?>, 'taken')">
                                                <i class="fas fa-check"></i>
                                                Mark as Taken
                                            </button>
                                        <?php endif; ?>
                                    </div>
                                </li>
                                <?php endforeach; ?>
                            </ul>
                        </div>
                        <?php 
                            endif;
                        endforeach; 
                        ?>
                    <?php endif; ?>
                    
                    <div class="medication-actions">
                        <button class="action-button" id="addMedicationBtn">
                            <i class="fas fa-plus-circle"></i>
                            Add New Medication
                        </button>
                        <button class="action-button secondary" onclick="loadPrescriptions()">
                            <i class="fas fa-prescription"></i>
                            Sync from Prescriptions
                        </button>
                    </div>
                </div>
                
                <!-- Medication History Section -->
                <div class="medication-section">
                    <h2 class="section-heading">Weekly Medication History</h2>
                    
                    <div class="history-grid">
                        <?php
                        // Create data for the past 7 days
                        for ($i = 6; $i >= 0; $i--) {
                            $date = new DateTime();
                            $date->modify("-$i day");
                            
                            $dayName = $date->format('D');
                            $dayNumber = $date->format('j');
                            $month = $date->format('M');
                            $dateStr = $date->format('Y-m-d');
                            
                            // Find history for this date
                            $dayHistory = null;
                            foreach ($medicationHistory as $history) {
                                if ($history['date'] === $dateStr) {
                                    $dayHistory = $history;
                                    break;
                                }
                            }
                            
                            $statusClass = 'dot-upcoming';
                            $statusText = 'No Data';
                            
                            if ($dayHistory) {
                                if ($dayHistory['taken'] == $dayHistory['total_scheduled']) {
                                    $statusClass = 'dot-taken';
                                    $statusText = 'All Taken';
                                } elseif ($dayHistory['taken'] > 0) {
                                    $statusClass = 'dot-skipped';
                                    $statusText = 'Partial';
                                } elseif ($dayHistory['missed'] > 0) {
                                    $statusClass = 'dot-missed';
                                    $statusText = 'Missed';
                                }
                            }
                            
                            echo "
                            <div class='history-day" . ($i == 0 ? " today" : "") . "'>
                                <div class='day-name'>$dayName</div>
                                <div class='day-date'>$month $dayNumber</div>
                                <div class='day-status'>
                                    <span class='status-dot $statusClass'></span>
                                    <span class='status-label'>$statusText</span>
                                </div>
                            </div>";
                        }
                        ?>
                    </div>
                </div>
                
                <!-- Medication Details Section -->
                <div class="medication-section">
                    <h2 class="section-heading">Medication Details</h2>
                    
                    <?php if (empty($medicationDetails)): ?>
                        <div style="text-align: center; padding: 40px; color: #718096;">
                            <i class="fas fa-pills" style="font-size: 3rem; margin-bottom: 20px; opacity: 0.3;"></i>
                            <p style="font-size: 1.2rem; margin-bottom: 10px;">No medications found</p>
                            <p>Add your first medication to get started with tracking.</p>
                        </div>
                    <?php else: ?>
                        <?php foreach ($medicationDetails as $med): ?>
                        <div class="medication-detail-card">
                            <div class="medication-row">
                                <div class="medication-label">
                                    <i class="fas fa-prescription-bottle-alt"></i>
                                    Medication
                                </div>
                                <div class="medication-value"><?php echo htmlspecialchars($med['name']); ?> <?php echo htmlspecialchars($med['dosage']); ?></div>
                            </div>
                            <div class="medication-row">
                                <div class="medication-label">
                                    <i class="fas fa-clock"></i>
                                    Schedule
                                </div>
                                <div class="medication-value"><?php echo htmlspecialchars($med['frequency']); ?></div>
                            </div>
                            <?php if ($med['instructions']): ?>
                            <div class="medication-row">
                                <div class="medication-label">
                                    <i class="fas fa-utensils"></i>
                                    Instructions
                                </div>
                                <div class="medication-value"><?php echo htmlspecialchars($med['instructions']); ?></div>
                            </div>
                            <?php endif; ?>
                            <?php if ($med['prescribing_doctor']): ?>
                            <div class="medication-row">
                                <div class="medication-label">
                                    <i class="fas fa-user-md"></i>
                                    Prescribed By
                                </div>
                                <div class="medication-value"><?php echo htmlspecialchars($med['prescribing_doctor']); ?></div>
                            </div>
                            <?php endif; ?>
                            <div class="medication-row">
                                <div class="medication-label">
                                    <i class="far fa-calendar-alt"></i>
                                    Start Date
                                </div>
                                <div class="medication-value"><?php echo date('F j, Y', strtotime($med['start_date'])); ?></div>
                            </div>
                            
                            <?php if ($med['total_pills'] > 0): ?>
                            <div class="refill-info">
                                <div class="refill-count">
                                    <strong>Pills Remaining:</strong> <?php echo $med['remaining_pills']; ?> of <?php echo $med['total_pills']; ?>
                                </div>
                                <?php if ($med['refill_status'] === 'low'): ?>
                                    <div class="refill-alert">
                                        <i class="fas fa-exclamation-circle"></i>
                                        Refill needed soon
                                    </div>
                                <?php else: ?>
                                    <div class="refill-normal">
                                        <i class="fas fa-check-circle"></i>
                                        Stock OK
                                    </div>
                                <?php endif; ?>
                            </div>
                            <?php endif; ?>
                        </div>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>
            </div>
        </main>
        
        <!-- Add Medication Modal -->
        <div class="modal" id="addMedicationModal">
            <div class="modal-content">
                <button class="modal-close" id="closeModal">&times;</button>
                <h2 class="modal-title">Add New Medication</h2>
                
                <form id="medicationForm">
                    <div class="form-group">
                        <label class="form-label" for="medicationName">Medication Name *</label>
                        <input type="text" class="form-control" id="medicationName" name="name" placeholder="Enter medication name" required>
                    </div>
                    
                    <div class="form-row">
                        <div class="form-col">
                            <div class="form-group">
                                <label class="form-label" for="dosage">Dosage *</label>
                                <input type="text" class="form-control" id="dosage" name="dosage" placeholder="e.g., 10mg" required>
                            </div>
                        </div>
                        <div class="form-col">
                            <div class="form-group">
                                <label class="form-label" for="frequency">Frequency</label>
                                <input type="text" class="form-control" id="frequency" name="frequency" placeholder="e.g., Once daily">
                            </div>
                        </div>
                    </div>
                    
                    <div class="form-group">
                        <label class="form-label">When to Take</label>
                        <div class="checkbox-group">
                            <div class="checkbox-item">
                                <input type="checkbox" id="morning" name="morning"<?php if (isset($medication) && $medication['morning']) echo ' checked'; ?>/>
                                <label for="morning">Morning</label>
                            </div>
                            <div class="checkbox-item">
                                <input type="checkbox" id="afternoon" name="afternoon"<?php if (isset($medication) && $medication['afternoon']) echo ' checked'; ?>/>
                                <label for="afternoon">Afternoon</label>
                            </div>
                            <div class="checkbox-item">
                                <input type="checkbox" id="evening" name="evening"<?php if (isset($medication) && $medication['evening']) echo ' checked'; ?>/>
                                <label for="evening">Evening</label>
                            </div>
                            <div class="checkbox-item">
                                <input type="checkbox" id="night" name="night"<?php if (isset($medication) && $medication['night']) echo ' checked'; ?>/>
                                <label for="night">Night</label>
                            </div>
                        </div>
                    </div>
                    
                    <div class="form-group">
                        <label class="form-label" for="instructions">Special Instructions</label>
                        <textarea class="form-control" id="instructions" name="instructions" rows="3" placeholder="e.g., Take with food"></textarea>
                    </div>
                    
                    <div class="form-row">
                        <div class="form-col">
                            <div class="form-group">
                                <label class="form-label" for="totalCount">Total Pills</label>
                                <input type="number" class="form-control" id="totalCount" name="total_pills" placeholder="e.g., 30">
                            </div>
                        </div>
                        <div class="form-col">
                            <div class="form-group">
                                <label class="form-label" for="refillDate">Refill Date</label>
                                <input type="date" class="form-control" id="refillDate" name="refill_date">
                            </div>
                        </div>
                    </div>
                    
                    <div class="form-group">
                        <label class="form-label" for="prescribedBy">Prescribed By</label>
                        <input type="text" class="form-control" id="prescribedBy" name="prescribing_doctor" placeholder="Doctor's name">
                    </div>
                    
                    <div class="form-actions">
                        <button type="button" class="form-button secondary" id="cancelBtn">Cancel</button>
                        <button type="submit" class="form-button primary">Save Medication</button>
                    </div>
                </form>
            </div>
        </div>
        
        <!-- Prescriptions Modal -->
        <div class="modal" id="prescriptionsModal">
            <div class="modal-content">
                <button class="modal-close" id="closePrescriptionsModal">&times;</button>
                <h2 class="modal-title">Select Medications from Prescriptions</h2>
                
                <div id="prescriptionsList" class="prescriptions-list">
                    <div class="loading-spinner">
                        <i class="fas fa-spinner fa-spin"></i> Loading prescriptions...
                    </div>
                </div>
                
                <div class="form-actions">
                    <button type="button" class="form-button secondary" id="cancelPrescriptionBtn">Cancel</button>
                    <button type="button" class="form-button primary" id="syncSelectedBtn">Sync Selected Medications</button>
                </div>
            </div>
        </div>
        
        <!-- Toast Notification Element -->
        <div id="toastContainer"></div>
        
        <!-- Emergency and Voice Assistant Buttons -->
        <button class="emergency-button" aria-label="Emergency assistance">
            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="white" stroke="white" stroke-width="1">
                <path d="M21.64 3.64A1.36 1.36 0 0 0 20.28 2H3.72a1.36 1.36 0 0 0-1.36 1.64l1.72 9a1.37 1.37 0 0 0 1.36 1.08h14.56c.64 0 1.2-.44 1.36-1.08l1.72-9c.08-.44-.12-.88-.44-1z"></path>
                <path d="M4 14.08c-1.92.96-2.8 2.16-3.36 3.68-.4 1.2.24 2.24 1.44 2.24h19.84c1.2 0 1.84-1.04 1.44-2.24-.56-1.52-1.44-2.72-3.36-3.68"></path>
            </svg>
        </button>
        
        <button class="voice-assistant" id="voiceAssistantBtn" aria-label="Voice assistant">
            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="white" stroke="white" stroke-width="1">
                <path d="M12 1a3 3 0 0 0-3 3v8a3 3 0 0 0 6 0V4a3 3 0 0 0-3-3z"></path>
                <path d="M19 10v2a7 7 0 0 1-14 0v-2"></path>
                <line x1="12" y1="19" x2="12" y2="23"></line>
                <line x1="8" y1="23" x2="16" y2="23"></line>
            </svg>
        </button>
        
        <!-- Custom Footer -->
        <?php include '../includes/footer.php'; ?>
        
        <!-- Analytics -->
        <amp-analytics type="googleanalytics" id="google-analytics">
            <script type="application/json">
                {
                    "requests": {
                        "pageview": "https://www.google-analytics.com/collect?v=1&tid=UA-XXXXX-Y&cid=555&t=pageview&dp=${canonicalUrl}"
                    },
                    "triggers": {
                        "trackPageview": {
                            "on": "visible",
                            "request": "pageview",
                            "visibilitySpec": {
                                "selector": "body",
                                "visiblePercentageMin": 50
                            }
                        }
                    }
                }
            </script>
        </amp-analytics>
        
        <script>
            // Mark medication as taken functionality
            function markMedication(logId, status) {
                fetch('med-api.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded',
                    },
                    body: new URLSearchParams({
                        'action': 'mark_medication',
                        'log_id': logId,
                        'status': status
                    })
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        showToast(data.message);
                        // Reload the page to show the updated status
                        window.location.reload();
                    } else {
                        showToast(data.error || 'An error occurred', true);
                    }
                })
                .catch(error => {
                    showToast('Error: ' + error, true);
                });
            }
            
            // Modal functionality - Add Medication
            const addMedicationModal = document.getElementById('addMedicationModal');
            const addMedicationBtn = document.getElementById('addMedicationBtn');
            const closeModalBtn = document.getElementById('closeModal');
            const cancelBtn = document.getElementById('cancelBtn');
            
            // Prescriptions Modal
            const prescriptionsModal = document.getElementById('prescriptionsModal');
            const closePrescriptionsModal = document.getElementById('closePrescriptionsModal');
            const cancelPrescriptionBtn = document.getElementById('cancelPrescriptionBtn');
            
            // Show/hide Add Medication modal
            addMedicationBtn.addEventListener('click', function() {
                addMedicationModal.classList.add('show');
            });
            
            closeModalBtn.addEventListener('click', function() {
                addMedicationModal.classList.remove('show');
            });
            
            cancelBtn.addEventListener('click', function() {
                addMedicationModal.classList.remove('show');
            });
            
            // Show/hide Prescriptions modal
            function loadPrescriptions() {
                prescriptionsModal.classList.add('show');
                
                const prescriptionsList = document.getElementById('prescriptionsList');
                prescriptionsList.innerHTML = '<div class="loading-spinner"><i class="fas fa-spinner fa-spin"></i> Loading prescriptions...</div>';
                
                fetch('med-api.php?action=load_prescriptions')
                    .then(response => response.json())
                    .then(data => {
                        if (data.success && data.prescriptions) {
                            renderPrescriptions(data.prescriptions);
                        } else {
                            prescriptionsList.innerHTML = '<div class="error-message">No prescriptions found or an error occurred.</div>';
                        }
                    })
                    .catch(error => {
                        prescriptionsList.innerHTML = '<div class="error-message">Error loading prescriptions: ' + error + '</div>';
                    });
            }
            
            closePrescriptionsModal.addEventListener('click', function() {
                prescriptionsModal.classList.remove('show');
            });
            
            cancelPrescriptionBtn.addEventListener('click', function() {
                prescriptionsModal.classList.remove('show');
            });
            
            // Close modals if clicked outside
            window.addEventListener('click', function(event) {
                if (event.target === addMedicationModal) {
                    addMedicationModal.classList.remove('show');
                }
                if (event.target === prescriptionsModal) {
                    prescriptionsModal.classList.remove('show');
                }
            });
            
            // Render prescriptions in the modal
            function renderPrescriptions(prescriptions) {
                const prescriptionsList = document.getElementById('prescriptionsList');
                
                if (prescriptions.length === 0) {
                    prescriptionsList.innerHTML = '<div class="no-results">No prescriptions found. Please visit your doctor to get prescriptions.</div>';
                    return;
                }
                
                let html = '';
                
                prescriptions.forEach((prescription, index) => {
                    html += `
                        <div class="prescription-card" data-prescription-id="${prescription.id}">
                            <div class="prescription-header">
                                <h3>Prescription #${prescription.id}</h3>
                                <div class="prescription-date">${prescription.created_at}</div>
                            </div>
                            <div class="prescription-info">
                                <div><strong>Doctor:</strong> ${prescription.doctor_name}</div>
                                <div><strong>Diagnosis:</strong> ${prescription.diagnosis}</div>
                            </div>
                            <div class="prescription-medications">
                                <h4>Medications:</h4>
                                ${renderMedications(prescription.medications, prescription.id)}
                            </div>
                            <button type="button" class="action-button secondary select-all-btn" 
                                    onclick="selectAllMedications(${prescription.id})">
                                <i class="fas fa-check-circle"></i> Select All
                            </button>
                        </div>
                    `;
                });
                
                prescriptionsList.innerHTML = html;
            }
            
            // Render medications in a prescription
            function renderMedications(medications, prescriptionId) {
                if (!medications || medications.length === 0) {
                    return '<div class="no-medications">No medications in this prescription</div>';
                }
                
                let html = '<ul class="medications-list">';
                
                medications.forEach((med, index) => {
                    html += `
                        <li>
                            <label class="medication-checkbox">
                                <input type="checkbox" name="med_${prescriptionId}_${index}" 
                                       data-prescription="${prescriptionId}" data-index="${index}">
                                ${med.name} ${med.dosage} - ${med.frequency || 'As needed'}
                            </label>
                        </li>
                    `;
                });
                
                html += '</ul>';
                return html;
            }
            
            // Select all medications in a prescription
            function selectAllMedications(prescriptionId) {
                const checkboxes = document.querySelectorAll(`input[data-prescription="${prescriptionId}"]`);
                checkboxes.forEach(checkbox => {
                    checkbox.checked = true;
                });
                
                // Set this as the active prescription
                document.querySelectorAll('.prescription-card').forEach(card => {
                    card.classList.remove('active');
                });
                document.querySelector(`.prescription-card[data-prescription-id="${prescriptionId}"]`).classList.add('active');
            }
            
            // Sync selected medications
            document.getElementById('syncSelectedBtn').addEventListener('click', function() {
                const checkboxes = document.querySelectorAll('input[type="checkbox"]:checked');
                
                if (checkboxes.length === 0) {
                    showToast('Please select at least one medication to sync', true);
                    return;
                }
                
                // Get prescription ID and medication indices
                const prescriptionId = checkboxes[0].dataset.prescription;
                const medicationIndices = Array.from(checkboxes).map(cb => cb.dataset.index).join(',');
                
                // Send request to server
                fetch('med-api.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded',
                    },
                    body: new URLSearchParams({
                        'action': 'sync_prescription',
                        'prescription_id': prescriptionId,
                        'medication_ids': medicationIndices
                    })
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        showToast(data.message);
                        prescriptionsModal.classList.remove('show');
                        // Reload the page to show the new medications
                        setTimeout(() => {
                            window.location.reload();
                        }, 1500);
                    } else {
                        showToast(data.error || 'An error occurred', true);
                    }
                })
                .catch(error => {
                    showToast('Error: ' + error, true);
                });
            });
            
            // Handle form submission for adding new medication
            document.getElementById('medicationForm').addEventListener('submit', function(event) {
                event.preventDefault();
                
                const formData = new FormData(this);
                formData.append('action', 'add_medication');
                
                fetch('med-api.php', {
                    method: 'POST',
                    body: formData
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        showToast(data.message);
                        addMedicationModal.classList.remove('show');
                        // Reload the page to show the new medication
                        setTimeout(() => {
                            window.location.reload();
                        }, 1500);
                    } else {
                        showToast(data.error || 'An error occurred', true);
                    }
                })
                .catch(error => {
                    showToast('Error: ' + error, true);
                });
            });
            
            // Show toast notification
            function showToast(message, isError = false) {
                const toast = document.createElement('div');
                toast.className = 'toast' + (isError ? ' error' : '');
                toast.innerHTML = message;
                document.getElementById('toastContainer').appendChild(toast);
                
                // Show the toast
                setTimeout(() => {
                    toast.classList.add('show');
                }, 100);
                
                // Hide and remove the toast after 3 seconds
                setTimeout(() => {
                    toast.classList.remove('show');
                    setTimeout(() => {
                        document.getElementById('toastContainer').removeChild(toast);
                    }, 300);
                }, 3000);
            }
            
            // Scroll to Today's Medications section
            function scrollToTodayMedications() {
                const element = document.getElementById('todayMedications');
                if (element) {
                    element.scrollIntoView({ behavior: 'smooth' });
                }
            }
        </script>
    </body>
</html>
