<?php
session_start();

// Include required files
require_once '../login_user/functions.php';
require_once '../signup_user/config.php';
require_once './db_connect.php';

// Authentication checks
if (!isLoggedIn() || $_SESSION['user_role'] !== 'healthcare_provider') {
    header("Location: ../login_user/login_user.php?error=unauthorized");
    exit();
}

// Get user information
$user_id = $_SESSION['user_id'];
$user_name = $_SESSION['user_name'] ?? 'Healthcare Provider';
$user_first_name = $_SESSION['user_first_name'] ?? 'Healthcare Provider';

// Get patients list
$stmt = $pdo->prepare("
    SELECT u.*, 
           TIMESTAMPDIFF(YEAR, u.date_of_birth, CURDATE()) as age,
           a.date as last_appointment_date,
           a.status as last_appointment_status
    FROM users u
    LEFT JOIN (
        SELECT user_id, MAX(date) as max_date
        FROM appointments
        WHERE doctor_name = :doctor_name
        GROUP BY user_id
    ) as latest_appt ON u.id = latest_appt.user_id
    LEFT JOIN appointments a ON latest_appt.user_id = a.user_id AND latest_appt.max_date = a.date
    WHERE u.role = 'elderly' OR u.role = 'senior'
    ORDER BY u.last_name, u.first_name
");
$stmt->execute(['doctor_name' => $user_name]);
$patients = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Process search/filter
$filtered_patients = $patients;
if ($_SERVER['REQUEST_METHOD'] === 'GET' && !empty($_GET['search'])) {
    $search = trim($_GET['search']);
    $filtered_patients = [];
    
    foreach ($patients as $patient) {
        if (stripos($patient['first_name'], $search) !== false || 
            stripos($patient['last_name'], $search) !== false || 
            stripos($patient['email'], $search) !== false || 
            stripos($patient['phone'], $search) !== false) {
            $filtered_patients[] = $patient;
        }
    }
}
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
    
    <!-- Styles and fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@400;500;600;700&family=Open+Sans:wght@400;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <link rel="stylesheet" href="../elderly/includes/style.css">
    <link rel="stylesheet" href="./css/healthcare.css">
</head>
<body>
    <?php include_once "includes/nav.php"; ?>
    
    <main class="main-content">
        <div class="container" style="max-width: 1200px; margin: 0 auto; padding: 20px;">
            <div class="page-header" style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 30px; flex-wrap: wrap; gap: 20px;">
                <h1 class="page-title" style="font-size: 28px; font-weight: 600; color: #2d3748; margin: 0;">
                    <i class="fas fa-users" style="color: #0096ad; margin-right: 12px;"></i>
                    My Patients
                </h1>
                
                <div class="header-actions">
                    <!-- Search form -->
                    <form method="GET" action="patients.php" style="display: flex; gap: 10px;">
                        <div class="search-container" style="position: relative;">
                            <input type="text" name="search" placeholder="Search patients..." style="padding: 10px 15px; border-radius: 8px; border: 1px solid #e2e8f0; width: 250px;"
                                value="<?php echo isset($_GET['search']) ? htmlspecialchars($_GET['search']) : ''; ?>">
                            <button type="submit" style="position: absolute; right: 10px; top: 50%; transform: translateY(-50%); background: none; border: none; color: #718096; cursor: pointer;">
                                <i class="fas fa-search"></i>
                            </button>
                        </div>
                    </form>
                </div>
            </div>
            
            <?php if (isset($_SESSION['success_message'])): ?>
                <div class="alert alert-success" style="background-color: #c6f6d5; color: #22543d; padding: 15px; border-radius: 8px; margin-bottom: 20px;">
                    <i class="fas fa-check-circle"></i>
                    <span><?php echo $_SESSION['success_message']; ?></span>
                </div>
                <?php unset($_SESSION['success_message']); ?>
            <?php endif; ?>
            
            <?php if (isset($_SESSION['error_message'])): ?>
                <div class="alert alert-error" style="background-color: #fed7d7; color: #822727; padding: 15px; border-radius: 8px; margin-bottom: 20px;">
                    <i class="fas fa-exclamation-circle"></i>
                    <span><?php echo $_SESSION['error_message']; ?></span>
                </div>
                <?php unset($_SESSION['error_message']); ?>
            <?php endif; ?>
            
            <div class="card" style="background-color: #fff; border-radius: 12px; box-shadow: 0 4px 15px rgba(0, 0, 0, 0.05); overflow: hidden; margin-bottom: 20px;">
                <div class="card-body" style="padding: 20px;">
                    <?php if (empty($filtered_patients)): ?>
                        <div class="empty-state" style="text-align: center; padding: 40px 20px;">
                            <div class="empty-state-icon" style="font-size: 48px; color: #cbd5e0; margin-bottom: 15px;">
                                <i class="fas fa-user-slash"></i>
                            </div>
                            <?php if (!empty($_GET['search'])): ?>
                                <p class="empty-state-text" style="color: #718096; font-size: 16px; margin-bottom: 20px;">No patients found matching your search criteria.</p>
                                <a href="patients.php" class="btn btn-primary" style="background-color: #0096ad; color: #fff; padding: 10px 20px; border-radius: 8px; text-decoration: none; display: inline-block;">View All Patients</a>
                            <?php else: ?>
                                <p class="empty-state-text" style="color: #718096; font-size: 16px; margin-bottom: 20px;">You don't have any patients yet.</p>
                            <?php endif; ?>
                        </div>
                    <?php else: ?>
                        <div class="table-container" style="overflow-x: auto;">
                            <table class="data-table" style="width: 100%; border-collapse: collapse;">
                                <thead>
                                    <tr>
                                        <th style="text-align: left; padding: 15px 20px; font-weight: 600; color: #4a5568; background-color: #f7fafc; border-bottom: 1px solid #e2e8f0;">Patient</th>
                                        <th style="text-align: left; padding: 15px 20px; font-weight: 600; color: #4a5568; background-color: #f7fafc; border-bottom: 1px solid #e2e8f0;">Age</th>
                                        <th style="text-align: left; padding: 15px 20px; font-weight: 600; color: #4a5568; background-color: #f7fafc; border-bottom: 1px solid #e2e8f0;">Contact</th>
                                        <th style="text-align: left; padding: 15px 20px; font-weight: 600; color: #4a5568; background-color: #f7fafc; border-bottom: 1px solid #e2e8f0;">Last Appointment</th>
                                        <th style="text-align: left; padding: 15px 20px; font-weight: 600; color: #4a5568; background-color: #f7fafc; border-bottom: 1px solid #e2e8f0;">Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($filtered_patients as $patient): ?>
                                    <tr>
                                        <td style="padding: 15px 20px; border-bottom: 1px solid #e2e8f0; color: #2d3748;">
                                            <div style="display: flex; align-items: center; gap: 15px;">
                                                <div style="width: 40px; height: 40px; border-radius: 50%; background-color: #e2f5f8; display: flex; align-items: center; justify-content: center; color: #0096ad; font-weight: 600; overflow: hidden;">
                                                    <?php if (!empty($patient['profile_picture']) && !empty($patient['profile_picture_type'])): ?>
                                                        <img src="data:<?php echo $patient['profile_picture_type']; ?>;base64,<?php echo base64_encode($patient['profile_picture']); ?>" alt="Patient Avatar" style="width: 100%; height: 100%; object-fit: cover;">
                                                    <?php else: ?>
                                                        <?php echo strtoupper(substr($patient['first_name'], 0, 1)); ?>
                                                    <?php endif; ?>
                                                </div>
                                                <div>
                                                    <div style="font-weight: 600;"><?php echo htmlspecialchars($patient['first_name'] . ' ' . $patient['last_name']); ?></div>
                                                    <div style="font-size: 12px; color: #718096;">#<?php echo $patient['id']; ?></div>
                                                </div>
                                            </div>
                                        </td>
                                        <td style="padding: 15px 20px; border-bottom: 1px solid #e2e8f0; color: #2d3748;">
                                            <?php echo !empty($patient['age']) ? $patient['age'] . ' years' : 'Not provided'; ?>
                                        </td>
                                        <td style="padding: 15px 20px; border-bottom: 1px solid #e2e8f0; color: #2d3748;">
                                            <div style="margin-bottom: 5px;">
                                                <i class="fas fa-envelope" style="color: #718096; margin-right: 8px; font-size: 14px;"></i>
                                                <?php echo htmlspecialchars($patient['email']); ?>
                                            </div>
                                            <?php if (!empty($patient['phone'])): ?>
                                            <div>
                                                <i class="fas fa-phone" style="color: #718096; margin-right: 8px; font-size: 14px;"></i>
                                                <?php echo htmlspecialchars($patient['phone']); ?>
                                            </div>
                                            <?php endif; ?>
                                        </td>
                                        <td style="padding: 15px 20px; border-bottom: 1px solid #e2e8f0; color: #2d3748;">
                                            <?php if (!empty($patient['last_appointment_date'])): ?>
                                                <div><?php echo date('M d, Y', strtotime($patient['last_appointment_date'])); ?></div>
                                                <?php
                                                    $status_class = '';
                                                    switch ($patient['last_appointment_status']) {
                                                        case 'completed':
                                                            $status_class = 'background-color: #e6ffec; color: #22543d;';
                                                            break;
                                                        case 'scheduled':
                                                            $status_class = 'background-color: #e6f6ff; color: #2c5282;';
                                                            break;
                                                        case 'cancelled':
                                                            $status_class = 'background-color: #fee2e2; color: #b91c1c;';
                                                            break;
                                                        default:
                                                            $status_class = 'background-color: #fef3c7; color: #92400e;';
                                                    }
                                                ?>
                                                <span style="display: inline-block; padding: 3px 8px; border-radius: 20px; font-size: 12px; font-weight: 500; <?php echo $status_class; ?>">
                                                    <?php echo ucfirst($patient['last_appointment_status']); ?>
                                                </span>
                                            <?php else: ?>
                                                <span style="color: #718096;">No appointments</span>
                                            <?php endif; ?>
                                        </td>
                                        <td style="padding: 15px 20px; border-bottom: 1px solid #e2e8f0; color: #2d3748;">
                                            <div style="display: flex; gap: 10px;">
                                                <a href="patient-details.php?id=<?php echo $patient['id']; ?>" style="width: 36px; height: 36px; border-radius: 8px; background-color: #e2f5f8; display: flex; align-items: center; justify-content: center; color: #0096ad; text-decoration: none;" title="View Patient Details">
                                                    <i class="fas fa-eye"></i>
                                                </a>
                                                <a href="appointments.php?patient_id=<?php echo $patient['id']; ?>" style="width: 36px; height: 36px; border-radius: 8px; background-color: #e2effe; display: flex; align-items: center; justify-content: center; color: #3182ce; text-decoration: none;" title="Schedule Appointment">
                                                    <i class="fas fa-calendar-plus"></i>
                                                </a>
                                                <a href="prescriptions.php?patient_id=<?php echo $patient['id']; ?>" style="width: 36px; height: 36px; border-radius: 8px; background-color: #edf2f7; display: flex; align-items: center; justify-content: center; color: #4a5568; text-decoration: none;" title="Write Prescription">
                                                    <i class="fas fa-prescription"></i>
                                                </a>
                                            </div>
                                        </td>
                                    </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </main>
    
    <?php include_once "../elderly/includes/footer.php"; ?>
    <?php include_once "includes/mobile_nav.php"; ?>
    
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Add any JavaScript functionality here
        });
    </script>
</body>
</html>