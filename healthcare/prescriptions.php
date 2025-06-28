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

// Get patient ID from URL parameter
$patient_id = isset($_GET['patient_id']) ? (int)$_GET['patient_id'] : null;
$edit_prescription_id = isset($_GET['edit']) ? (int)$_GET['edit'] : null;

// If no patient ID provided, redirect to patients list
if (!$patient_id && !$edit_prescription_id) {
    $_SESSION['error_message'] = "Patient ID is required.";
    header("Location: patients.php");
    exit();
}

// Get patient details
if ($patient_id) {
    $stmt = $pdo->prepare("
        SELECT u.*, TIMESTAMPDIFF(YEAR, u.date_of_birth, CURDATE()) as age
        FROM users u
        WHERE u.id = :id
    ");
    $stmt->execute(['id' => $patient_id]);
    $patient = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$patient) {
        $_SESSION['error_message'] = "Patient not found.";
        header("Location: patients.php");
        exit();
    }
}

// Handle prescription editing
$editing_prescription = null;
if ($edit_prescription_id) {
    try {
        $stmt = $pdo->prepare("
            SELECT p.*, u.first_name as patient_first_name, u.last_name as patient_last_name, u.id as patient_id
            FROM prescriptions p
            JOIN users u ON p.patient_id = u.id
            WHERE p.id = :id AND p.doctor_id = :doctor_id
        ");
        $stmt->execute(['id' => $edit_prescription_id, 'doctor_id' => $user_id]);
        $editing_prescription = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($editing_prescription) {
            $patient_id = $editing_prescription['patient_id'];
            $patient = [
                'id' => $editing_prescription['patient_id'],
                'first_name' => $editing_prescription['patient_first_name'],
                'last_name' => $editing_prescription['patient_last_name']
            ];
        } else {
            $_SESSION['error_message'] = "Prescription not found or access denied.";
            header("Location: patients.php");
            exit();
        }
    } catch (PDOException $e) {
        $_SESSION['error_message'] = "Error accessing prescription data.";
        header("Location: patients.php");
        exit();
    }
}

// Handle form submissions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['create_prescription']) || isset($_POST['update_prescription'])) {
        $diagnosis = trim($_POST['diagnosis']);
        $symptoms = trim($_POST['symptoms']);
        $medications = isset($_POST['medications']) ? $_POST['medications'] : [];
        $instructions = trim($_POST['instructions']);
        $follow_up_date = !empty($_POST['follow_up_date']) ? $_POST['follow_up_date'] : null;
        $notes = trim($_POST['notes']);
        
        // Validate required fields
        if (empty($diagnosis) || empty($medications)) {
            $_SESSION['error_message'] = "Diagnosis and at least one medication are required.";
        } else {
            // Process medications data
            $medications_json = json_encode($medications);
            
            try {
                if (isset($_POST['update_prescription']) && $edit_prescription_id) {
                    // Update existing prescription
                    $stmt = $pdo->prepare("
                        UPDATE prescriptions 
                        SET diagnosis = :diagnosis, symptoms = :symptoms, medications = :medications, 
                            instructions = :instructions, follow_up_date = :follow_up_date, notes = :notes,
                            updated_at = CURRENT_TIMESTAMP
                        WHERE id = :id AND doctor_id = :doctor_id
                    ");
                    
                    $success = $stmt->execute([
                        'diagnosis' => $diagnosis,
                        'symptoms' => $symptoms,
                        'medications' => $medications_json,
                        'instructions' => $instructions,
                        'follow_up_date' => $follow_up_date,
                        'notes' => $notes,
                        'id' => $edit_prescription_id,
                        'doctor_id' => $user_id
                    ]);
                    
                    if ($success) {
                        $_SESSION['success_message'] = "Prescription updated successfully.";
                        header("Location: prescription-details.php?id=" . $edit_prescription_id);
                        exit();
                    } else {
                        $_SESSION['error_message'] = "Failed to update prescription.";
                    }
                } else {
                    // Create new prescription
                    $stmt = $pdo->prepare("
                        INSERT INTO prescriptions 
                        (patient_id, doctor_id, diagnosis, symptoms, medications, instructions, follow_up_date, notes) 
                        VALUES 
                        (:patient_id, :doctor_id, :diagnosis, :symptoms, :medications, :instructions, :follow_up_date, :notes)
                    ");
                    
                    $success = $stmt->execute([
                        'patient_id' => $patient_id,
                        'doctor_id' => $user_id,
                        'diagnosis' => $diagnosis,
                        'symptoms' => $symptoms,
                        'medications' => $medications_json,
                        'instructions' => $instructions,
                        'follow_up_date' => $follow_up_date,
                        'notes' => $notes
                    ]);
                    
                    if ($success) {
                        $prescription_id = $pdo->lastInsertId();
                        $_SESSION['success_message'] = "Prescription created successfully.";
                        header("Location: prescription-details.php?id=" . $prescription_id);
                        exit();
                    } else {
                        $_SESSION['error_message'] = "Failed to create prescription.";
                    }
                }
            } catch (PDOException $e) {
                $_SESSION['error_message'] = "Database error: " . $e->getMessage();
            }
        }
    }
}

// Get patient's prescription history
$prescriptions = [];
if ($patient_id) {
    try {
        $stmt = $pdo->prepare("
            SELECT p.*, u.first_name as doctor_first_name, u.last_name as doctor_last_name
            FROM prescriptions p
            JOIN users u ON p.doctor_id = u.id
            WHERE p.patient_id = :patient_id
            ORDER BY p.created_at DESC
        ");
        $stmt->execute(['patient_id' => $patient_id]);
        $prescriptions = $stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
        $prescriptions = [];
    }
}

// Common medications database for auto-suggestions
$common_medications = [
    'Paracetamol' => ['strength' => '500mg', 'type' => 'Tablet'],
    'Ibuprofen' => ['strength' => '400mg', 'type' => 'Tablet'],
    'Aspirin' => ['strength' => '75mg', 'type' => 'Tablet'],
    'Amoxicillin' => ['strength' => '500mg', 'type' => 'Capsule'],
    'Metformin' => ['strength' => '500mg', 'type' => 'Tablet'],
    'Lisinopril' => ['strength' => '10mg', 'type' => 'Tablet'],
    'Amlodipine' => ['strength' => '5mg', 'type' => 'Tablet'],
    'Atorvastatin' => ['strength' => '20mg', 'type' => 'Tablet'],
    'Omeprazole' => ['strength' => '20mg', 'type' => 'Capsule'],
    'Cetirizine' => ['strength' => '10mg', 'type' => 'Tablet']
];
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $editing_prescription ? 'Edit' : 'Write'; ?> Prescription - <?php echo htmlspecialchars($patient['first_name'] . ' ' . $patient['last_name']); ?></title>
    
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
            flex-wrap: wrap;
        }
        
        .prescription-layout {
            display: grid;
            grid-template-columns: 1fr 300px;
            gap: 30px;
            margin-bottom: 30px;
        }
        
        .card {
            background-color: #fff;
            border-radius: 12px;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.05);
            overflow: hidden;
            margin-bottom: 20px;
        }
        
        .card-header {
            padding: 20px;
            border-bottom: 1px solid #e2e8f0;
            display: flex;
            justify-content: space-between;
            align-items: center;
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
        
        .form-group {
            margin-bottom: 20px;
        }
        
        .form-label {
            display: block;
            font-size: 14px;
            font-weight: 500;
            color: #4a5568;
            margin-bottom: 8px;
        }
        
        .form-control {
            width: 100%;
            padding: 10px 12px;
            border: 1px solid #e2e8f0;
            border-radius: 8px;
            font-size: 14px;
        }
        
        .form-control:focus {
            border-color: #0096ad;
            outline: none;
            box-shadow: 0 0 0 2px rgba(0, 150, 173, 0.2);
        }
        
        textarea.form-control {
            min-height: 100px;
            resize: vertical;
        }
        
        .medication-item {
            background-color: #f8fafc;
            border: 1px solid #e2e8f0;
            border-radius: 8px;
            padding: 15px;
            margin-bottom: 15px;
        }
        
        .medication-header {
            display: flex;
            justify-content: between;
            align-items: center;
            margin-bottom: 15px;
        }
        
        .medication-number {
            background-color: #0096ad;
            color: white;
            width: 24px;
            height: 24px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 12px;
            font-weight: 600;
        }
        
        .remove-medication {
            background-color: #e53e3e;
            color: white;
            border: none;
            border-radius: 4px;
            padding: 5px 8px;
            cursor: pointer;
            font-size: 12px;
        }
        
        .medication-grid {
            display: grid;
            grid-template-columns: 2fr 1fr 1fr;
            gap: 15px;
            margin-bottom: 15px;
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
        
        .btn-secondary {
            background-color: #6c757d;
            color: #fff;
            border: none;
        }
        
        .btn-secondary:hover {
            background-color: #545b62;
        }
        
        .btn-outline {
            background-color: transparent;
            border: 1px solid #e2e8f0;
            color: #4a5568;
        }
        
        .btn-outline:hover {
            background-color: #f7fafc;
        }
        
        .btn-add {
            background-color: #38a169;
            color: #fff;
            border: none;
            font-size: 13px;
            padding: 8px 15px;
        }
        
        .btn-add:hover {
            background-color: #2f855a;
        }
        
        .patient-info {
            background: linear-gradient(135deg, #0096ad 0%, #00b0cc 100%);
            color: #fff;
            padding: 20px;
            border-radius: 12px;
            margin-bottom: 20px;
        }
        
        .patient-name {
            font-size: 20px;
            font-weight: 600;
            margin-bottom: 5px;
        }
        
        .patient-details {
            font-size: 14px;
            opacity: 0.9;
        }
        
        .prescription-history {
            max-height: 400px;
            overflow-y: auto;
        }
        
        .history-item {
            border: 1px solid #e2e8f0;
            border-radius: 8px;
            padding: 15px;
            margin-bottom: 10px;
        }
        
        .history-date {
            font-size: 12px;
            color: #718096;
            margin-bottom: 5px;
        }
        
        .history-diagnosis {
            font-weight: 600;
            color: #2d3748;
            margin-bottom: 5px;
        }
        
        .history-doctor {
            font-size: 13px;
            color: #4a5568;
        }
        
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
        
        .medication-suggestions {
            position: relative;
        }
        
        .suggestions-dropdown {
            position: absolute;
            top: 100%;
            left: 0;
            right: 0;
            background: white;
            border: 1px solid #e2e8f0;
            border-radius: 8px;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
            max-height: 200px;
            overflow-y: auto;
            z-index: 1000;
            display: none;
        }
        
        .suggestion-item {
            padding: 10px 15px;
            cursor: pointer;
            border-bottom: 1px solid #f7fafc;
        }
        
        .suggestion-item:hover {
            background-color: #f7fafc;
        }
        
        .suggestion-item:last-child {
            border-bottom: none;
        }
        
        @media (max-width: 900px) {
            .prescription-layout {
                grid-template-columns: 1fr;
            }
            
            .medication-grid {
                grid-template-columns: 1fr;
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
                    <i class="fas fa-prescription"></i>
                    <?php echo $editing_prescription ? 'Edit' : 'Write'; ?> Prescription
                </h1>
                
                <div class="header-actions">
                    <a href="patient-details.php?id=<?php echo $patient_id; ?>" class="btn btn-outline">
                        <i class="fas fa-arrow-left"></i>
                        Back to Patient
                    </a>
                    <a href="patients.php" class="btn btn-outline">
                        <i class="fas fa-users"></i>
                        All Patients
                    </a>
                </div>
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
            
            <div class="prescription-layout">
                <div class="prescription-form">
                    <div class="card">
                        <div class="card-header">
                            <h2 class="card-title">
                                <i class="fas fa-edit"></i>
                                Prescription Details
                            </h2>
                        </div>
                        <div class="card-body">
                            <form method="post" id="prescriptionForm">
                                <div class="form-group">
                                    <label class="form-label" for="diagnosis">Diagnosis *</label>
                                    <input type="text" id="diagnosis" name="diagnosis" class="form-control" 
                                           placeholder="Enter primary diagnosis" 
                                           value="<?php echo $editing_prescription ? htmlspecialchars($editing_prescription['diagnosis']) : ''; ?>" 
                                           required>
                                </div>
                                
                                <div class="form-group">
                                    <label class="form-label" for="symptoms">Symptoms</label>
                                    <textarea id="symptoms" name="symptoms" class="form-control" 
                                              placeholder="Describe patient's symptoms"><?php echo $editing_prescription ? htmlspecialchars($editing_prescription['symptoms']) : ''; ?></textarea>
                                </div>
                                
                                <div class="form-group">
                                    <label class="form-label">Medications *</label>
                                    <div id="medications-container">
                                        <?php 
                                        $existing_medications = [];
                                        if ($editing_prescription && !empty($editing_prescription['medications'])) {
                                            $existing_medications = json_decode($editing_prescription['medications'], true);
                                        }
                                        
                                        if (empty($existing_medications)) {
                                            $existing_medications = [['name' => '', 'dosage' => '', 'frequency' => '', 'duration' => '', 'instructions' => '']];
                                        }
                                        
                                        foreach ($existing_medications as $index => $medication):
                                        ?>
                                        <div class="medication-item" data-index="<?php echo $index; ?>">
                                            <div class="medication-header">
                                                <div class="medication-number"><?php echo $index + 1; ?></div>
                                                <?php if ($index > 0): ?>
                                                <button type="button" class="remove-medication" onclick="removeMedication(this)">
                                                    <i class="fas fa-times"></i> Remove
                                                </button>
                                                <?php endif; ?>
                                            </div>
                                            
                                            <div class="medication-grid">
                                                <div class="medication-suggestions">
                                                    <label class="form-label">Medication Name</label>
                                                    <input type="text" name="medications[<?php echo $index; ?>][name]" 
                                                           class="form-control medication-name" 
                                                           placeholder="e.g., Paracetamol"
                                                           value="<?php echo htmlspecialchars($medication['name']); ?>"
                                                           required>
                                                    <div class="suggestions-dropdown"></div>
                                                </div>
                                                
                                                <div>
                                                    <label class="form-label">Strength/Dosage</label>
                                                    <input type="text" name="medications[<?php echo $index; ?>][dosage]" 
                                                           class="form-control" 
                                                           placeholder="e.g., 500mg"
                                                           value="<?php echo htmlspecialchars($medication['dosage']); ?>">
                                                </div>
                                                
                                                <div>
                                                    <label class="form-label">Frequency</label>
                                                    <select name="medications[<?php echo $index; ?>][frequency]" class="form-control">
                                                        <option value="">Select frequency</option>
                                                        <option value="Once daily" <?php echo ($medication['frequency'] == 'Once daily') ? 'selected' : ''; ?>>Once daily</option>
                                                        <option value="Twice daily" <?php echo ($medication['frequency'] == 'Twice daily') ? 'selected' : ''; ?>>Twice daily</option>
                                                        <option value="Three times daily" <?php echo ($medication['frequency'] == 'Three times daily') ? 'selected' : ''; ?>>Three times daily</option>
                                                        <option value="Four times daily" <?php echo ($medication['frequency'] == 'Four times daily') ? 'selected' : ''; ?>>Four times daily</option>
                                                        <option value="Every 4 hours" <?php echo ($medication['frequency'] == 'Every 4 hours') ? 'selected' : ''; ?>>Every 4 hours</option>
                                                        <option value="Every 6 hours" <?php echo ($medication['frequency'] == 'Every 6 hours') ? 'selected' : ''; ?>>Every 6 hours</option>
                                                        <option value="Every 8 hours" <?php echo ($medication['frequency'] == 'Every 8 hours') ? 'selected' : ''; ?>>Every 8 hours</option>
                                                        <option value="As needed" <?php echo ($medication['frequency'] == 'As needed') ? 'selected' : ''; ?>>As needed</option>
                                                    </select>
                                                </div>
                                            </div>
                                            
                                            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 15px; margin-top: 15px;">
                                                <div>
                                                    <label class="form-label">Duration</label>
                                                    <input type="text" name="medications[<?php echo $index; ?>][duration]" 
                                                           class="form-control" 
                                                           placeholder="e.g., 7 days"
                                                           value="<?php echo htmlspecialchars($medication['duration']); ?>">
                                                </div>
                                                
                                                <div>
                                                    <label class="form-label">Instructions</label>
                                                    <input type="text" name="medications[<?php echo $index; ?>][instructions]" 
                                                           class="form-control" 
                                                           placeholder="e.g., Take after meals"
                                                           value="<?php echo htmlspecialchars($medication['instructions']); ?>">
                                                </div>
                                            </div>
                                        </div>
                                        <?php endforeach; ?>
                                    </div>
                                    
                                    <button type="button" class="btn btn-add" onclick="addMedication()">
                                        <i class="fas fa-plus"></i>
                                        Add Another Medication
                                    </button>
                                </div>
                                
                                <div class="form-group">
                                    <label class="form-label" for="instructions">General Instructions</label>
                                    <textarea id="instructions" name="instructions" class="form-control" 
                                              placeholder="General instructions for the patient"><?php echo $editing_prescription ? htmlspecialchars($editing_prescription['instructions']) : ''; ?></textarea>
                                </div>
                                
                                <div class="form-group">
                                    <label class="form-label" for="follow_up_date">Follow-up Date</label>
                                    <input type="date" id="follow_up_date" name="follow_up_date" class="form-control"
                                           value="<?php echo $editing_prescription ? $editing_prescription['follow_up_date'] : ''; ?>">
                                </div>
                                
                                <div class="form-group">
                                    <label class="form-label" for="notes">Additional Notes</label>
                                    <textarea id="notes" name="notes" class="form-control" 
                                              placeholder="Any additional notes or observations"><?php echo $editing_prescription ? htmlspecialchars($editing_prescription['notes']) : ''; ?></textarea>
                                </div>
                                
                                <div class="template-section">
                                    <label class="form-label">Common Prescription Templates</label>
                                    <select class="template-dropdown" id="prescriptionTemplate" onchange="loadTemplate()">
                                        <option value="">-- Select a template --</option>
                                        <option value="hypertension">Hypertension Management</option>
                                        <option value="diabetes">Type 2 Diabetes</option>
                                        <option value="uti">Urinary Tract Infection</option>
                                        <option value="bronchitis">Acute Bronchitis</option>
                                        <option value="gerd">GERD Treatment</option>
                                    </select>
                                    <div class="template-info">
                                        <i class="fas fa-info-circle"></i> 
                                        Templates pre-populate common prescriptions to save time
                                    </div>
                                </div>
                                
                                <div style="display: flex; gap: 15px; justify-content: flex-end;">
                                    <a href="patient-details.php?id=<?php echo $patient_id; ?>" class="btn btn-outline">Cancel</a>
                                    <button type="submit" name="<?php echo $editing_prescription ? 'update_prescription' : 'create_prescription'; ?>" class="btn btn-primary">
                                        <i class="fas fa-save"></i>
                                        <?php echo $editing_prescription ? 'Update' : 'Create'; ?> Prescription
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
                
                <div class="prescription-sidebar">
                    <div class="patient-info">
                        <div class="patient-name"><?php echo htmlspecialchars($patient['first_name'] . ' ' . $patient['last_name']); ?></div>
                        <div class="patient-details">
                            Patient ID: <?php echo $patient['id']; ?><br>
                            <?php if (isset($patient['age'])): ?>
                            Age: <?php echo $patient['age']; ?> years<br>
                            <?php endif; ?>
                            <?php if (isset($patient['phone'])): ?>
                            Phone: <?php echo htmlspecialchars($patient['phone']); ?>
                            <?php endif; ?>
                        </div>
                    </div>
                    
                    <div class="card">
                        <div class="card-header">
                            <h3 class="card-title">
                                <i class="fas fa-history"></i>
                                Prescription History
                            </h3>
                        </div>
                        <div class="card-body">
                            <div class="prescription-history">
                                <?php if (!empty($prescriptions)): ?>
                                    <?php foreach (array_slice($prescriptions, 0, 5) as $prescription): ?>
                                    <div class="history-item">
                                        <div class="history-date"><?php echo date('M d, Y', strtotime($prescription['created_at'])); ?></div>
                                        <div class="history-diagnosis"><?php echo htmlspecialchars($prescription['diagnosis']); ?></div>
                                        <div class="history-doctor">Dr. <?php echo htmlspecialchars($prescription['doctor_first_name'] . ' ' . $prescription['doctor_last_name']); ?></div>
                                        <div style="margin-top: 8px;">
                                            <a href="prescription-details.php?id=<?php echo $prescription['id']; ?>" style="font-size: 12px; color: #0096ad; text-decoration: none;">View Details</a>
                                        </div>
                                    </div>
                                    <?php endforeach; ?>
                                    
                                    <?php if (count($prescriptions) > 5): ?>
                                    <div style="text-align: center; margin-top: 15px;">
                                        <a href="patient-details.php?id=<?php echo $patient_id; ?>#prescriptions" class="btn btn-outline" style="font-size: 13px;">View All Prescriptions</a>
                                    </div>
                                    <?php endif; ?>
                                <?php else: ?>
                                    <p style="color: #718096; text-align: center; padding: 20px 0;">No previous prescriptions found.</p>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </main>
    
    <?php include_once "../elderly/includes/footer.php"; ?>
    <?php include_once "includes/mobile_nav.php"; ?>
    
    <script>
        let medicationCount = <?php echo count($existing_medications); ?>;
        
        // Common medications for auto-suggestion
        const commonMedications = <?php echo json_encode($common_medications); ?>;
        
        function addMedication() {
            const container = document.getElementById('medications-container');
            const newIndex = medicationCount;
            
            const medicationHtml = `
                <div class="medication-item" data-index="${newIndex}">
                    <div class="medication-header">
                        <div class="medication-number">${newIndex + 1}</div>
                        <button type="button" class="remove-medication" onclick="removeMedication(this)">
                            <i class="fas fa-times"></i> Remove
                        </button>
                    </div>
                    
                    <div class="medication-grid">
                        <div class="medication-suggestions">
                            <label class="form-label">Medication Name</label>
                            <input type="text" name="medications[${newIndex}][name]" 
                                   class="form-control medication-name" 
                                   placeholder="e.g., Paracetamol"
                                   required>
                            <div class="suggestions-dropdown"></div>
                        </div>
                        
                        <div>
                            <label class="form-label">Strength/Dosage</label>
                            <input type="text" name="medications[${newIndex}][dosage]" 
                                   class="form-control" 
                                   placeholder="e.g., 500mg">
                        </div>
                        
                        <div>
                            <label class="form-label">Frequency</label>
                            <select name="medications[${newIndex}][frequency]" class="form-control">
                                <option value="">Select frequency</option>
                                <option value="Once daily">Once daily</option>
                                <option value="Twice daily">Twice daily</option>
                                <option value="Three times daily">Three times daily</option>
                                <option value="Four times daily">Four times daily</option>
                                <option value="Every 4 hours">Every 4 hours</option>
                                <option value="Every 6 hours">Every 6 hours</option>
                                <option value="Every 8 hours">Every 8 hours</option>
                                <option value="As needed">As needed</option>
                            </select>
                        </div>
                    </div>
                    
                    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 15px; margin-top: 15px;">
                        <div>
                            <label class="form-label">Duration</label>
                            <input type="text" name="medications[${newIndex}][duration]" 
                                   class="form-control" 
                                   placeholder="e.g., 7 days">
                        </div>
                        
                        <div>
                            <label class="form-label">Instructions</label>
                            <input type="text" name="medications[${newIndex}][instructions]" 
                                   class="form-control" 
                                   placeholder="e.g., Take after meals">
                        </div>
                    </div>
                </div>
            `;
            
            container.insertAdjacentHTML('beforeend', medicationHtml);
            medicationCount++;
            updateMedicationNumbers();
            setupMedicationSuggestions();
        }
        
        function removeMedication(button) {
            const medicationItem = button.closest('.medication-item');
            medicationItem.remove();
            updateMedicationNumbers();
        }
        
        function updateMedicationNumbers() {
            const medications = document.querySelectorAll('.medication-item');
            medications.forEach((item, index) => {
                const numberElement = item.querySelector('.medication-number');
                numberElement.textContent = index + 1;
                
                // Update input names to maintain proper indexing
                const inputs = item.querySelectorAll('input, select');
                inputs.forEach(input => {
                    const name = input.getAttribute('name');
                    if (name && name.includes('medications[')) {
                        const newName = name.replace(/medications\[\d+\]/, `medications[${index}]`);
                        input.setAttribute('name', newName);
                    }
                });
            });
        }
        
        function setupMedicationSuggestions() {
            const medicationInputs = document.querySelectorAll('.medication-name');
            
            medicationInputs.forEach(input => {
                const dropdown = input.nextElementSibling;
                
                input.addEventListener('input', function() {
                    const value = this.value.toLowerCase();
                    
                    if (value.length > 0) {
                        const matches = Object.keys(commonMedications).filter(med => 
                            med.toLowerCase().includes(value)
                        );
                        
                        if (matches.length > 0) {
                            dropdown.innerHTML = matches.map(med => `
                                <div class="suggestion-item" onclick="selectMedication('${med}', this)">
                                    <strong>${med}</strong><br>
                                    <small>${commonMedications[med].strength} ${commonMedications[med].type}</small>
                                </div>
                            `).join('');
                            dropdown.style.display = 'block';
                        } else {
                            dropdown.style.display = 'none';
                        }
                    } else {
                        dropdown.style.display = 'none';
                    }
                });
                
                input.addEventListener('blur', function() {
                    setTimeout(() => {
                        dropdown.style.display = 'none';
                    }, 200);
                });
            });
        }
        
        function selectMedication(medicationName, element) {
            const suggestionContainer = element.closest('.medication-suggestions');
            const input = suggestionContainer.querySelector('.medication-name');
            const dosageInput = suggestionContainer.closest('.medication-item').querySelector('input[name*="[dosage]"]');
            
            input.value = medicationName;
            if (dosageInput && commonMedications[medicationName]) {
                dosageInput.value = commonMedications[medicationName].strength;
            }
            
            element.closest('.suggestions-dropdown').style.display = 'none';
        }
        
        document.addEventListener('DOMContentLoaded', function() {
            setupMedicationSuggestions();
            
            // Form validation
            document.getElementById('prescriptionForm').addEventListener('submit', function(e) {
                const medications = document.querySelectorAll('.medication-name');
                let hasValidMedication = false;
                
                medications.forEach(input => {
                    if (input.value.trim() !== '') {
                        hasValidMedication = true;
                    }
                });
                
                if (!hasValidMedication) {
                    e.preventDefault();
                    alert('Please add at least one medication.');
                    return false;
                }
            });
            
            // Set minimum date for follow-up to today
            document.getElementById('follow_up_date').min = new Date().toISOString().split('T')[0];
        });
    </script>
</body>
</html>