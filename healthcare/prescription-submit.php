<?php
// filepath: c:\xampp\htdocs\Astha\healthcare\prescription-submit.php
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
$doctor_name = $_SESSION['user_name'];

// Initialize variables
$success_message = '';
$error_message = '';
$patient_id = isset($_GET['patient_id']) ? $_GET['patient_id'] : null;
$patient_details = [];

// Get patient details if patient_id is available
if ($patient_id) {
    $stmt = $pdo->prepare("SELECT id, first_name, last_name, date_of_birth, gender FROM users WHERE id = ? AND user_role = 'elderly'");
    $stmt->execute([$patient_id]);
    $patient_details = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$patient_details) {
        $error_message = "Patient not found.";
        $patient_id = null;
    }
}

// Check if form is submitted
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Collect form data
    $patient_id = $_POST['patient_id'] ?? null;
    $diagnosis = $_POST['diagnosis'] ?? '';
    $notes = $_POST['notes'] ?? '';
    $follow_up = $_POST['follow_up'] ?? null;
    
    // Collect medications
    $medications = [];
    $med_names = $_POST['medication_name'] ?? [];
    $med_dosages = $_POST['medication_dosage'] ?? [];
    $med_frequencies = $_POST['medication_frequency'] ?? [];
    $med_durations = $_POST['medication_duration'] ?? [];
    $med_quantities = $_POST['medication_quantity'] ?? [];
    $med_instructions = $_POST['medication_instructions'] ?? [];
    
    for ($i = 0; $i < count($med_names); $i++) {
        if (!empty($med_names[$i])) {
            $medications[] = [
                'name' => $med_names[$i],
                'dosage' => $med_dosages[$i] ?? '',
                'frequency' => $med_frequencies[$i] ?? '',
                'duration' => $med_durations[$i] ?? '',
                'quantity_prescribed' => $med_quantities[$i] ?? '',
                'pharmacy_instructions' => $med_instructions[$i] ?? ''
            ];
        }
    }
    
    // Validate form data
    if (empty($patient_id)) {
        $error_message = "Patient must be selected.";
    } elseif (empty($diagnosis)) {
        $error_message = "Diagnosis is required.";
    } elseif (empty($medications)) {
        $error_message = "At least one medication must be added.";
    } else {
        try {
            // Start a transaction
            $pdo->beginTransaction();
            
            // Convert medications to JSON
            $medications_json = json_encode(['medications' => $medications]);
            
            // Insert prescription
            $stmt = $pdo->prepare("
                INSERT INTO prescriptions (
                    patient_id, doctor_id, diagnosis, notes, 
                    medications, follow_up_date, created_at
                ) VALUES (?, ?, ?, ?, ?, ?, NOW())
            ");
            
            $stmt->execute([
                $patient_id, 
                $user_id, 
                $diagnosis, 
                $notes, 
                $medications_json, 
                $follow_up
            ]);
            
            $prescription_id = $pdo->lastInsertId();
            
            // Insert notification for the patient
            $notification_stmt = $pdo->prepare("
                INSERT INTO notifications (
                    user_id, title, message, link, created_at, is_read
                ) VALUES (?, ?, ?, ?, NOW(), 0)
            ");
            
            $notification_title = "New Prescription";
            $notification_message = "Dr. {$doctor_name} has prescribed new medications for you.";
            $notification_link = "../elderly/medication-tracking/medication-tracking.php";
            
            $notification_stmt->execute([
                $patient_id,
                $notification_title,
                $notification_message,
                $notification_link
            ]);
            
            // Commit transaction
            $pdo->commit();
            
            $success_message = "Prescription successfully submitted!";
            
            // Clear form data on success
            $diagnosis = '';
            $notes = '';
            $follow_up = '';
            $medications = [];
            
        } catch (PDOException $e) {
            // Roll back transaction on error
            $pdo->rollBack();
            $error_message = "Database error: " . $e->getMessage();
        }
    }
}

// Get all elderly patients for dropdown selection
$stmt = $pdo->prepare("
    SELECT id, first_name, last_name, date_of_birth 
    FROM users 
    WHERE user_role = 'elderly' 
    ORDER BY first_name, last_name
");
$stmt->execute();
$patients = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Submit Prescription - Healthcare Provider</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">
    <link rel="stylesheet" href="styles.css">
    <style>
        .prescription-form {
            background-color: #fff;
            border-radius: 8px;
            padding: 20px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            margin-bottom: 20px;
        }
        
        .form-section {
            margin-bottom: 20px;
            padding-bottom: 20px;
            border-bottom: 1px solid #e2e8f0;
        }
        
        .form-section:last-child {
            border-bottom: none;
        }
        
        .form-row {
            display: flex;
            flex-wrap: wrap;
            margin: 0 -10px 15px;
        }
        
        .form-group {
            flex: 1;
            padding: 0 10px;
            min-width: 200px;
        }
        
        .form-label {
            display: block;
            margin-bottom: 6px;
            font-weight: 500;
            color: #2d3748;
        }
        
        .form-control {
            width: 100%;
            padding: 10px;
            border: 1px solid #cbd5e0;
            border-radius: 4px;
            background-color: #f8fafc;
            transition: border-color 0.3s;
        }
        
        .form-control:focus {
            border-color: #4299e1;
            box-shadow: 0 0 0 3px rgba(66, 153, 225, 0.15);
        }
        
        .medication-item {
            background-color: #f8fafc;
            border-radius: 6px;
            padding: 15px;
            margin-bottom: 15px;
            border-left: 3px solid #4299e1;
            position: relative;
        }
        
        .remove-med-btn {
            position: absolute;
            right: 15px;
            top: 15px;
            background: #fee2e2;
            color: #dc2626;
            border: none;
            border-radius: 4px;
            width: 30px;
            height: 30px;
            display: flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            transition: all 0.3s;
        }
        
        .remove-med-btn:hover {
            background: #fecaca;
        }
        
        .add-med-btn {
            background-color: #e6f2ff;
            color: #3182ce;
            border: 1px dashed #90cdf4;
            border-radius: 6px;
            padding: 12px 20px;
            width: 100%;
            display: flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            transition: all 0.3s;
            font-weight: 500;
            margin-top: 10px;
        }
        
        .add-med-btn:hover {
            background-color: #dbeafe;
        }
        
        .patient-info {
            padding: 15px;
            background-color: #f0f9ff;
            border-radius: 6px;
            margin-bottom: 20px;
            border-left: 3px solid #3182ce;
        }
        
        .patient-name {
            font-size: 1.25rem;
            font-weight: 600;
            color: #1e3a8a;
            margin-bottom: 5px;
        }
        
        .patient-detail {
            display: flex;
            gap: 30px;
            color: #475569;
            font-size: 0.95rem;
        }
        
        .patient-detail span {
            display: flex;
            align-items: center;
            gap: 5px;
        }
        
        .patient-detail i {
            color: #64748b;
        }
        
        .alert {
            padding: 12px 15px;
            margin-bottom: 20px;
            border-radius: 6px;
            border-left: 4px solid;
            display: flex;
            align-items: center;
            gap: 10px;
        }
        
        .alert-success {
            background-color: #ecfdf5;
            border-color: #10b981;
            color: #065f46;
        }
        
        .alert-error {
            background-color: #fef2f2;
            border-color: #ef4444;
            color: #b91c1c;
        }
    </style>
</head>
<body>
    <?php include './doctor-header.php'; ?>
    
    <div class="container">
        <h1>Create Prescription</h1>
        
        <?php if ($success_message): ?>
            <div class="alert alert-success">
                <i class="fas fa-check-circle"></i>
                <?php echo htmlspecialchars($success_message); ?>
            </div>
        <?php endif; ?>
        
        <?php if ($error_message): ?>
            <div class="alert alert-error">
                <i class="fas fa-exclamation-circle"></i>
                <?php echo htmlspecialchars($error_message); ?>
            </div>
        <?php endif; ?>
        
        <form action="prescription-submit.php" method="post" class="prescription-form">
            <div class="form-section">
                <h2>Patient Information</h2>
                
                <?php if ($patient_details): ?>
                    <div class="patient-info">
                        <div class="patient-name">
                            <?php echo htmlspecialchars($patient_details['first_name'] . ' ' . $patient_details['last_name']); ?>
                        </div>
                        <div class="patient-detail">
                            <span>
                                <i class="fas fa-calendar-alt"></i>
                                <?php 
                                    $dob = new DateTime($patient_details['date_of_birth']);
                                    $now = new DateTime();
                                    $age = $now->diff($dob)->y;
                                    echo htmlspecialchars($dob->format('M d, Y') . ' (' . $age . ' years)'); 
                                ?>
                            </span>
                            <span>
                                <i class="fas fa-venus-mars"></i>
                                <?php echo htmlspecialchars(ucfirst($patient_details['gender'])); ?>
                            </span>
                        </div>
                        <input type="hidden" name="patient_id" value="<?php echo $patient_details['id']; ?>">
                    </div>
                <?php else: ?>
                    <div class="form-group">
                        <label for="patient_id" class="form-label">Select Patient</label>
                        <select name="patient_id" id="patient_id" class="form-control" required>
                            <option value="">-- Select Patient --</option>
                            <?php foreach ($patients as $patient): ?>
                                <option value="<?php echo $patient['id']; ?>">
                                    <?php echo htmlspecialchars($patient['first_name'] . ' ' . $patient['last_name']); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                <?php endif; ?>
            </div>
            
            <div class="form-section">
                <h2>Diagnosis & Notes</h2>
                
                <div class="form-group">
                    <label for="diagnosis" class="form-label">Diagnosis</label>
                    <textarea name="diagnosis" id="diagnosis" class="form-control" rows="3" required><?php echo isset($diagnosis) ? htmlspecialchars($diagnosis) : ''; ?></textarea>
                </div>
                
                <div class="form-group">
                    <label for="notes" class="form-label">Notes (Optional)</label>
                    <textarea name="notes" id="notes" class="form-control" rows="3"><?php echo isset($notes) ? htmlspecialchars($notes) : ''; ?></textarea>
                </div>
                
                <div class="form-group">
                    <label for="follow_up" class="form-label">Follow-up Date (Optional)</label>
                    <input type="text" name="follow_up" id="follow_up" class="form-control datepicker" value="<?php echo isset($follow_up) ? htmlspecialchars($follow_up) : ''; ?>" placeholder="Select date">
                </div>
            </div>
            
            <div class="form-section">
                <h2>Medications</h2>
                
                <div id="medications-container">
                    <div class="medication-item">
                        <button type="button" class="remove-med-btn" onclick="removeMedication(this)" style="display:none;">
                            <i class="fas fa-times"></i>
                        </button>
                        
                        <div class="form-row">
                            <div class="form-group">
                                <label class="form-label">Medication Name</label>
                                <input type="text" name="medication_name[]" class="form-control" required>
                            </div>
                            
                            <div class="form-group">
                                <label class="form-label">Dosage</label>
                                <input type="text" name="medication_dosage[]" class="form-control" placeholder="e.g., 10mg">
                            </div>
                            
                            <div class="form-group">
                                <label class="form-label">Frequency</label>
                                <select name="medication_frequency[]" class="form-control">
                                    <option value="Once a day">Once a day</option>
                                    <option value="Twice a day">Twice a day</option>
                                    <option value="Three times a day">Three times a day</option>
                                    <option value="Four times a day">Four times a day</option>
                                    <option value="Every morning">Every morning</option>
                                    <option value="Every evening">Every evening</option>
                                    <option value="As needed">As needed</option>
                                    <option value="Other">Other</option>
                                </select>
                            </div>
                        </div>
                        
                        <div class="form-row">
                            <div class="form-group">
                                <label class="form-label">Duration</label>
                                <input type="text" name="medication_duration[]" class="form-control" placeholder="e.g., 7 days, 2 weeks">
                            </div>
                            
                            <div class="form-group">
                                <label class="form-label">Quantity</label>
                                <input type="number" name="medication_quantity[]" class="form-control" min="1" placeholder="Total pills/units">
                            </div>
                            
                            <div class="form-group">
                                <label class="form-label">Instructions</label>
                                <input type="text" name="medication_instructions[]" class="form-control" placeholder="e.g., Take with food">
                            </div>
                        </div>
                    </div>
                </div>
                
                <button type="button" class="add-med-btn" onclick="addMedication()">
                    <i class="fas fa-plus-circle"></i> Add Another Medication
                </button>
            </div>
            
            <div class="form-actions">
                <button type="submit" class="btn btn-primary">Submit Prescription</button>
                <button type="button" class="btn btn-secondary" onclick="window.location.href='dashboard.php'">Cancel</button>
            </div>
        </form>
    </div>
    
    <script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>
    <script>
        // Initialize date picker
        flatpickr(".datepicker", {
            minDate: "today",
            dateFormat: "Y-m-d"
        });
        
        // Add medication
        function addMedication() {
            const container = document.getElementById('medications-container');
            const medicationItems = container.querySelectorAll('.medication-item');
            
            // Show remove buttons for existing items
            medicationItems.forEach(item => {
                item.querySelector('.remove-med-btn').style.display = 'flex';
            });
            
            // Clone the first medication item
            const firstItem = medicationItems[0];
            const newItem = firstItem.cloneNode(true);
            
            // Clear input values
            const inputs = newItem.querySelectorAll('input, select');
            inputs.forEach(input => input.value = '');
            
            // Show remove button
            newItem.querySelector('.remove-med-btn').style.display = 'flex';
            
            // Append new item
            container.appendChild(newItem);
        }
        
        // Remove medication
        function removeMedication(button) {
            const container = document.getElementById('medications-container');
            const medicationItems = container.querySelectorAll('.medication-item');
            
            // Only remove if there's more than one item
            if (medicationItems.length > 1) {
                const item = button.closest('.medication-item');
                container.removeChild(item);
                
                // If only one item left, hide its remove button
                if (container.querySelectorAll('.medication-item').length === 1) {
                    container.querySelector('.remove-med-btn').style.display = 'none';
                }
            }
        }
        
        // Initialize - hide remove button if only one medication
        document.addEventListener('DOMContentLoaded', function() {
            const container = document.getElementById('medications-container');
            if (container.querySelectorAll('.medication-item').length === 1) {
                container.querySelector('.remove-med-btn').style.display = 'none';
            }
        });
    </script>
</body>
</html>