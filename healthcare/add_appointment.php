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
$doctor_name = $_SESSION['user_name'];

// Get all patients (users with appointments with this doctor)
$stmt = $pdo->prepare("
    SELECT DISTINCT u.id, u.first_name, u.last_name
    FROM users u
    JOIN appointments a ON u.id = a.user_id
    WHERE a.doctor_name = :doctor_name
    UNION
    SELECT u.id, u.first_name, u.last_name
    FROM users u
    WHERE u.role = 'elderly'
    ORDER BY last_name, first_name
");
$stmt->execute(['doctor_name' => $doctor_name]);
$patients = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Process appointment form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_appointment'])) {
    // Validate inputs
    $user_id = trim($_POST['patient_id']);
    $title = trim($_POST['title']);
    $appointment_type = trim($_POST['appointment_type']);
    $date = trim($_POST['date']);
    $time = trim($_POST['time']);
    $duration = isset($_POST['duration']) ? (int)$_POST['duration'] : 30;
    $location = isset($_POST['location']) ? trim($_POST['location']) : null;
    $is_video_call = isset($_POST['is_video_call']) ? 1 : 0;
    $video_call_link = ($is_video_call && isset($_POST['video_call_link'])) ? trim($_POST['video_call_link']) : null;
    $notes = isset($_POST['notes']) ? trim($_POST['notes']) : null;
    
    // Further validation
    $errors = [];
    
    if (empty($user_id)) {
        $errors[] = "Patient is required";
    }
    
    if (empty($title)) {
        $errors[] = "Appointment title is required";
    }
    
    if (empty($date)) {
        $errors[] = "Date is required";
    } else {
        // Validate date format and check if it's in the future
        $date_obj = DateTime::createFromFormat('Y-m-d', $date);
        $today = new DateTime();
        
        if (!$date_obj || $date_obj->format('Y-m-d') !== $date) {
            $errors[] = "Invalid date format";
        } elseif ($date_obj < $today->setTime(0, 0)) {
            $errors[] = "Appointment date cannot be in the past";
        }
    }
    
    if (empty($time)) {
        $errors[] = "Time is required";
    }
    
    if ($is_video_call && empty($video_call_link)) {
        $errors[] = "Video call link is required for video appointments";
    }
    
    // If no errors, insert appointment
    if (empty($errors)) {
        try {
            $stmt = $pdo->prepare("
                INSERT INTO appointments 
                (user_id, title, doctor_name, appointment_type, date, time, duration, location, is_video_call, video_call_link, notes)
                VALUES 
                (:user_id, :title, :doctor_name, :appointment_type, :date, :time, :duration, :location, :is_video_call, :video_call_link, :notes)
            ");
            
            $result = $stmt->execute([
                'user_id' => $user_id,
                'title' => $title,
                'doctor_name' => $doctor_name,
                'appointment_type' => $appointment_type,
                'date' => $date,
                'time' => $time,
                'duration' => $duration,
                'location' => $location,
                'is_video_call' => $is_video_call,
                'video_call_link' => $video_call_link,
                'notes' => $notes
            ]);
            
            if ($result) {
                // Create notification for the patient
                $stmt = $pdo->prepare("
                    SELECT first_name, last_name FROM users WHERE id = :user_id
                ");
                $stmt->execute(['user_id' => $user_id]);
                $patient = $stmt->fetch(PDO::FETCH_ASSOC);
                
                $notification_title = "New Appointment Scheduled";
                $notification_content = "Dr. {$doctor_name} has scheduled a {$appointment_type} appointment for you on " . date('M d, Y', strtotime($date)) . " at " . date('h:i A', strtotime($time));
                
                // Insert notification
                $stmt = $pdo->prepare("
                    INSERT INTO notifications 
                    (user_id, title, content, notification_type, reference_id, reference_type, created_by)
                    VALUES 
                    (:user_id, :title, :content, :notification_type, :reference_id, :reference_type, :created_by)
                ");
                
                $stmt->execute([
                    'user_id' => $user_id,
                    'title' => $notification_title,
                    'content' => $notification_content,
                    'notification_type' => 'appointment',
                    'reference_id' => $pdo->lastInsertId(), // Appointment ID
                    'reference_type' => 'appointments',
                    'created_by' => $_SESSION['user_id']
                ]);
                
                $_SESSION['success_message'] = "Appointment successfully scheduled with " . htmlspecialchars($patient['first_name'] . ' ' . $patient['last_name']);
                header("Location: appointments.php");
                exit();
            } else {
                $errors[] = "Failed to schedule appointment";
            }
        } catch (PDOException $e) {
            $errors[] = "Database error: " . $e->getMessage();
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Add New Appointment - Healthcare Provider Dashboard</title>
    
    <!-- Include your CSS files here -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@400;500;600;700&family=Open+Sans:wght@400;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <link rel="stylesheet" href="../elderly/includes/style.css">
    <link rel="stylesheet" href="./css/healthcare.css">
    
    <style>
        .form-container {
            max-width: 800px;
            margin: 20px auto;
            background: white;
            border-radius: 12px;
            padding: 30px;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.05);
        }
        
        .form-title {
            font-size: 24px;
            font-weight: 600;
            color: #2d3748;
            margin-bottom: 20px;
            display: flex;
            align-items: center;
        }
        
        .form-title i {
            color: #0096ad;
            margin-right: 10px;
        }
        
        .form-group {
            margin-bottom: 20px;
        }
        
        .form-label {
            display: block;
            font-weight: 600;
            margin-bottom: 8px;
            color: #4a5568;
        }
        
        .form-control {
            width: 100%;
            padding: 12px 15px;
            border: 1px solid #e2e8f0;
            border-radius: 8px;
            font-size: 16px;
            transition: all 0.3s;
        }
        
        .form-control:focus {
            border-color: #0096ad;
            box-shadow: 0 0 0 3px rgba(0, 150, 173, 0.2);
            outline: none;
        }
        
        .form-row {
            display: flex;
            gap: 20px;
            margin-bottom: 20px;
        }
        
        .form-row .form-group {
            flex: 1;
            margin-bottom: 0;
        }
        
        .form-check {
            display: flex;
            align-items: center;
            gap: 10px;
            margin-bottom: 15px;
        }
        
        .form-check-input {
            width: 18px;
            height: 18px;
            cursor: pointer;
        }
        
        .btn {
            padding: 12px 24px;
            border: none;
            border-radius: 8px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s;
            font-size: 16px;
        }
        
        .btn-primary {
            background-color: #0096ad;
            color: white;
        }
        
        .btn-primary:hover {
            background-color: #007a8c;
            transform: translateY(-2px);
        }
        
        .btn-secondary {
            background-color: #e2e8f0;
            color: #4a5568;
        }
        
        .btn-secondary:hover {
            background-color: #cbd5e0;
        }
        
        .form-buttons {
            display: flex;
            justify-content: flex-end;
            gap: 15px;
            margin-top: 30px;
        }
        
        .invalid-feedback {
            color: #e53e3e;
            font-size: 14px;
            margin-top: 5px;
        }
        
        .alert {
            padding: 15px;
            border-radius: 8px;
            margin-bottom: 20px;
        }
        
        .alert-danger {
            background-color: #fed7d7;
            color: #822727;
            border-left: 4px solid #e53e3e;
        }
        
        /* Conditional field visibility */
        .video-call-fields {
            display: none;
        }
        
        .video-call-fields.show {
            display: block;
            animation: fadeIn 0.3s;
        }
        
        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(-10px); }
            to { opacity: 1; transform: translateY(0); }
        }
    </style>
</head>
<body>
    <?php include_once "includes/nav.php"; ?>
    
    <main class="main-content">
        <div class="form-container">
            <h1 class="form-title">
                <i class="fas fa-calendar-plus"></i>
                Schedule New Appointment
            </h1>
            
            <?php if (!empty($errors)): ?>
                <div class="alert alert-danger">
                    <ul style="margin: 0; padding-left: 20px;">
                        <?php foreach ($errors as $error): ?>
                            <li><?php echo $error; ?></li>
                        <?php endforeach; ?>
                    </ul>
                </div>
            <?php endif; ?>
            
            <form action="<?php echo htmlspecialchars($_SERVER['PHP_SELF']); ?>" method="post">
                <div class="form-group">
                    <label for="patient_id" class="form-label">Select Patient</label>
                    <select name="patient_id" id="patient_id" class="form-control" required>
                        <option value="">-- Select Patient --</option>
                        <?php foreach ($patients as $patient): ?>
                            <option value="<?php echo $patient['id']; ?>" <?php echo isset($_POST['patient_id']) && $_POST['patient_id'] == $patient['id'] ? 'selected' : ''; ?>>
                                <?php echo htmlspecialchars($patient['first_name'] . ' ' . $patient['last_name']); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                
                <div class="form-group">
                    <label for="title" class="form-label">Appointment Title</label>
                    <input type="text" name="title" id="title" class="form-control" value="<?php echo isset($_POST['title']) ? htmlspecialchars($_POST['title']) : ''; ?>" placeholder="E.g., Annual Check-up, Follow-up Visit" required>
                </div>
                
                <div class="form-row">
                    <div class="form-group">
                        <label for="appointment_type" class="form-label">Appointment Type</label>
                        <select name="appointment_type" id="appointment_type" class="form-control" required>
                            <option value="">-- Select Type --</option>
                            <option value="doctor" <?php echo isset($_POST['appointment_type']) && $_POST['appointment_type'] == 'doctor' ? 'selected' : ''; ?>>Doctor Consultation</option>
                            <option value="test" <?php echo isset($_POST['appointment_type']) && $_POST['appointment_type'] == 'test' ? 'selected' : ''; ?>>Medical Test</option>
                            <option value="therapy" <?php echo isset($_POST['appointment_type']) && $_POST['appointment_type'] == 'therapy' ? 'selected' : ''; ?>>Therapy Session</option>
                            <option value="other" <?php echo isset($_POST['appointment_type']) && $_POST['appointment_type'] == 'other' ? 'selected' : ''; ?>>Other</option>
                        </select>
                    </div>
                    
                    <div class="form-group">
                        <label for="duration" class="form-label">Duration (minutes)</label>
                        <input type="number" name="duration" id="duration" class="form-control" value="<?php echo isset($_POST['duration']) ? htmlspecialchars($_POST['duration']) : '30'; ?>" min="15" step="15">
                    </div>
                </div>
                
                <div class="form-row">
                    <div class="form-group">
                        <label for="date" class="form-label">Date</label>
                        <input type="date" name="date" id="date" class="form-control" value="<?php echo isset($_POST['date']) ? htmlspecialchars($_POST['date']) : ''; ?>" min="<?php echo date('Y-m-d'); ?>" required>
                    </div>
                    
                    <div class="form-group">
                        <label for="time" class="form-label">Time</label>
                        <input type="time" name="time" id="time" class="form-control" value="<?php echo isset($_POST['time']) ? htmlspecialchars($_POST['time']) : ''; ?>" required>
                    </div>
                </div>
                
                <div class="form-check">
                    <input type="checkbox" name="is_video_call" id="is_video_call" class="form-check-input" <?php echo isset($_POST['is_video_call']) ? 'checked' : ''; ?>>
                    <label for="is_video_call">This is a video call appointment</label>
                </div>
                
                <div class="video-call-fields <?php echo isset($_POST['is_video_call']) ? 'show' : ''; ?>">
                    <div class="form-group">
                        <label for="video_call_link" class="form-label">Video Call Link</label>
                        <input type="url" name="video_call_link" id="video_call_link" class="form-control" value="<?php echo isset($_POST['video_call_link']) ? htmlspecialchars($_POST['video_call_link']) : ''; ?>" placeholder="E.g., https://meet.google.com/abc-defg-hij">
                    </div>
                </div>
                
                <div class="form-group">
                    <label for="location" class="form-label">Location (for in-person appointments)</label>
                    <input type="text" name="location" id="location" class="form-control" value="<?php echo isset($_POST['location']) ? htmlspecialchars($_POST['location']) : ''; ?>" placeholder="E.g., Clinic Room 3, 2nd Floor">
                </div>
                
                <div class="form-group">
                    <label for="notes" class="form-label">Notes</label>
                    <textarea name="notes" id="notes" class="form-control" rows="4" placeholder="Add any additional information about this appointment"><?php echo isset($_POST['notes']) ? htmlspecialchars($_POST['notes']) : ''; ?></textarea>
                </div>
                
                <div class="form-buttons">
                    <a href="appointments.php" class="btn btn-secondary">Cancel</a>
                    <button type="submit" name="add_appointment" class="btn btn-primary">
                        <i class="fas fa-calendar-check"></i>
                        Schedule Appointment
                    </button>
                </div>
            </form>
        </div>
    </main>
    
    <?php include_once "../elderly/includes/footer.php"; ?>
    <?php include_once "includes/mobile_nav.php"; ?>
    
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Handle video call field visibility
            const isVideoCallCheckbox = document.getElementById('is_video_call');
            const videoCallFields = document.querySelector('.video-call-fields');
            
            isVideoCallCheckbox.addEventListener('change', function() {
                if (this.checked) {
                    videoCallFields.classList.add('show');
                } else {
                    videoCallFields.classList.remove('show');
                }
            });
        });
    </script>
</body>
</html>