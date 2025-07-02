<?php
// filepath: c:\xampp\htdocs\Astha\elderly\medication-tracking\med-api.php
// Ensure no output before headers
ob_start();
session_start();

// Check if user is logged in
if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true) {
    http_response_code(401);
    echo json_encode(['error' => 'Unauthorized access']);
    exit();
}

// Get user information
$user_id = $_SESSION['user_id'];

// Include database connection - with error handling
try {
    require_once '../../signup_user/config.php';
    
    // Ensure database connection is established
    if (!isset($pdo)) {
        $host = 'localhost';
        $dbname = 'shohochor_db';
        $username = 'root';
        $password = '';
        
        $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8mb4", $username, $password);
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    }
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(['error' => 'Database connection failed: ' . $e->getMessage()]);
    exit();
}

// Set JSON header
header('Content-Type: application/json');

$action = $_POST['action'] ?? $_GET['action'] ?? '';

try {
    switch ($action) {
        case 'add_medication':
            addMedication($pdo, $user_id);
            break;
            
        case 'load_prescriptions':
            loadPrescriptions($pdo, $user_id);
            break;
            
        case 'sync_prescription':
            syncPrescription($pdo, $user_id);
            break;
            
        case 'mark_medication':
            markMedication($pdo, $user_id);
            break;
            
        default:
            echo json_encode(['error' => 'Invalid action']);
            break;
    }
} catch (Exception $e) {
    echo json_encode(['error' => $e->getMessage()]);
}

function addMedication($pdo, $user_id) {
    // Get form data
    $name = $_POST['name'] ?? '';
    $dosage = $_POST['dosage'] ?? '';
    $frequency = $_POST['frequency'] ?? '';
    $instructions = $_POST['instructions'] ?? '';
    $total_pills = $_POST['total_pills'] ? intval($_POST['total_pills']) : 0;
    $prescribing_doctor = $_POST['prescribing_doctor'] ?? '';
    $refill_date = !empty($_POST['refill_date']) ? $_POST['refill_date'] : null;
    
    // Time settings
    $morning = isset($_POST['morning']) ? 1 : 0;
    $afternoon = isset($_POST['afternoon']) ? 1 : 0;
    $evening = isset($_POST['evening']) ? 1 : 0;
    $night = isset($_POST['night']) ? 1 : 0;
    
    // Validate required fields
    if (empty($name) || empty($dosage)) {
        echo json_encode(['error' => 'Medication name and dosage are required']);
        return;
    }
    
    // Check if 'refill_date' column exists in the database
    try {
        $stmt = $pdo->prepare("
            INSERT INTO medications (
                user_id, name, dosage, frequency, instructions, 
                total_pills, remaining_pills, prescribing_doctor,
                morning, afternoon, evening, night, start_date, is_active
            ) VALUES (
                ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, CURDATE(), 1
            )
        ");
        
        $result = $stmt->execute([
            $user_id, $name, $dosage, $frequency, $instructions, 
            $total_pills, $total_pills, $prescribing_doctor,
            $morning, $afternoon, $evening, $night
        ]);
        
        if ($result) {
            $medication_id = $pdo->lastInsertId();
            
            // Create initial medication logs for today
            $today = date('Y-m-d');
            if ($morning) createMedicationLog($pdo, $medication_id, $user_id, $name, $dosage, $today . ' 08:00:00');
            if ($afternoon) createMedicationLog($pdo, $medication_id, $user_id, $name, $dosage, $today . ' 14:00:00');
            if ($evening) createMedicationLog($pdo, $medication_id, $user_id, $name, $dosage, $today . ' 20:00:00');
            if ($night) createMedicationLog($pdo, $medication_id, $user_id, $name, $dosage, $today . ' 22:00:00');
            
            echo json_encode(['success' => true, 'message' => 'Medication added successfully']);
        } else {
            echo json_encode(['error' => 'Failed to add medication']);
        }
    } catch (PDOException $e) {
        echo json_encode(['error' => 'Database error: ' . $e->getMessage()]);
    }
}

function createMedicationLog($pdo, $medication_id, $user_id, $name, $dosage, $scheduled_time) {
    $stmt = $pdo->prepare("
        INSERT INTO medication_logs 
        (medication_id, user_id, medication_name, dosage, scheduled_time, status)
        VALUES (?, ?, ?, ?, ?, 'pending')
    ");
    $stmt->execute([$medication_id, $user_id, $name, $dosage, $scheduled_time]);
}

function loadPrescriptions($pdo, $user_id) {
    $stmt = $pdo->prepare("
        SELECT p.id, p.medications, p.created_at,
               d.first_name as doctor_first_name, d.last_name as doctor_last_name,
               p.diagnosis
        FROM prescriptions p
        LEFT JOIN users d ON p.doctor_id = d.id
        WHERE p.patient_id = ?
        ORDER BY p.created_at DESC
        LIMIT 10
    ");
    
    $stmt->execute([$user_id]);
    $prescriptions = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    $formatted_prescriptions = [];
    foreach ($prescriptions as $prescription) {
        // Format doctor name
        $doctor_name = $prescription['doctor_first_name'] . ' ' . $prescription['doctor_last_name'];
        
        // Parse medications JSON if available
        $medications = [];
        if (!empty($prescription['medications'])) {
            $medications_data = json_decode($prescription['medications'], true);
            if (is_array($medications_data)) {
                if (isset($medications_data['medications'])) {
                    $medications = $medications_data['medications'];
                } else {
                    $medications = $medications_data;
                }
            }
        }
        
        $formatted_prescriptions[] = [
            'id' => $prescription['id'],
            'doctor_name' => $doctor_name,
            'diagnosis' => $prescription['diagnosis'],
            'created_at' => date('M j, Y', strtotime($prescription['created_at'])),
            'medications' => $medications
        ];
    }
    
    echo json_encode([
        'success' => true, 
        'prescriptions' => $formatted_prescriptions
    ]);
}

function syncPrescription($pdo, $user_id) {
    $prescription_id = $_POST['prescription_id'] ?? 0;
    $medication_ids = isset($_POST['medication_ids']) ? explode(',', $_POST['medication_ids']) : [];
    
    if (empty($prescription_id) || empty($medication_ids)) {
        echo json_encode(['error' => 'Invalid prescription or no medications selected']);
        return;
    }
    
    // Get prescription details
    $stmt = $pdo->prepare("
        SELECT p.*, 
               CONCAT(d.first_name, ' ', d.last_name) as doctor_name 
        FROM prescriptions p
        LEFT JOIN users d ON p.doctor_id = d.id
        WHERE p.id = ? AND p.patient_id = ?
    ");
    $stmt->execute([$prescription_id, $user_id]);
    $prescription = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$prescription) {
        echo json_encode(['error' => 'Prescription not found']);
        return;
    }
    
    // Get medications from JSON
    $medications_data = json_decode($prescription['medications'], true);
    $medications = isset($medications_data['medications']) ? $medications_data['medications'] : $medications_data;
    
    $added_count = 0;
    
    foreach ($medication_ids as $med_index) {
        if (!isset($medications[$med_index])) continue;
        
        $med = $medications[$med_index];
        
        // Check if medication already exists
        $stmt = $pdo->prepare("
            SELECT id FROM medications 
            WHERE user_id = ? AND name = ? AND dosage = ?
        ");
        $stmt->execute([$user_id, $med['name'], $med['dosage']]);
        
        if ($stmt->rowCount() == 0) {
            // Parse frequency to determine timing
            $morning = 0;
            $afternoon = 0;
            $evening = 0;
            $night = 0;
            
            $frequency = strtolower($med['frequency'] ?? '');
            
            if (strpos($frequency, 'once') !== false || strpos($frequency, '1') !== false) {
                $morning = 1;
            } elseif (strpos($frequency, 'twice') !== false || strpos($frequency, '2') !== false) {
                $morning = 1;
                $evening = 1;
            } elseif (strpos($frequency, 'three') !== false || strpos($frequency, '3') !== false) {
                $morning = 1;
                $afternoon = 1;
                $evening = 1;
            } elseif (strpos($frequency, 'four') !== false || strpos($frequency, '4') !== false) {
                $morning = 1;
                $afternoon = 1;
                $evening = 1;
                $night = 1;
            }
            
            // Add new medication - FIXED QUERY
            $stmt = $pdo->prepare("
                INSERT INTO medications (
                    user_id, prescription_id, name, dosage, frequency,
                    instructions, prescribing_doctor, total_pills, remaining_pills,
                    morning, afternoon, evening, night, start_date, is_active
                ) VALUES (
                    ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, CURDATE(), 1
                )
            ");
            
            $total_pills = $med['quantity_prescribed'] ?? 30;
            $instructions = $med['pharmacy_instructions'] ?? '';
            
            $result = $stmt->execute([
                $user_id, $prescription_id, $med['name'], $med['dosage'], $med['frequency'],
                $instructions, $prescription['doctor_name'], $total_pills, $total_pills,
                $morning, $afternoon, $evening, $night
            ]);
            
            if ($result) {
                $medication_id = $pdo->lastInsertId();
                
                // Create initial medication logs for today
                $today = date('Y-m-d');
                if ($morning) createMedicationLog($pdo, $medication_id, $user_id, $med['name'], $med['dosage'], $today . ' 08:00:00');
                if ($afternoon) createMedicationLog($pdo, $medication_id, $user_id, $med['name'], $med['dosage'], $today . ' 14:00:00');
                if ($evening) createMedicationLog($pdo, $medication_id, $user_id, $med['name'], $med['dosage'], $today . ' 20:00:00');
                if ($night) createMedicationLog($pdo, $medication_id, $user_id, $med['name'], $med['dosage'], $today . ' 22:00:00');
                
                $added_count++;
            }
        }
    }
    
    echo json_encode([
        'success' => true, 
        'message' => $added_count . ' medications added successfully'
    ]);
}

function markMedication($pdo, $user_id) {
    $log_id = $_POST['log_id'] ?? 0;
    $status = $_POST['status'] ?? 'taken';
    
    if (empty($log_id)) {
        echo json_encode(['error' => 'Invalid medication log ID']);
        return;
    }
    
    $stmt = $pdo->prepare("
        UPDATE medication_logs 
        SET status = ?, taken_time = NOW() 
        WHERE id = ? AND user_id = ?
    ");
    
    $result = $stmt->execute([$status, $log_id, $user_id]);
    
    if ($result) {
        // Update remaining pills if taken
        if ($status === 'taken') {
            $stmt = $pdo->prepare("
                UPDATE medications m
                JOIN medication_logs ml ON m.id = ml.medication_id
                SET m.remaining_pills = GREATEST(0, m.remaining_pills - 1)
                WHERE ml.id = ? AND m.user_id = ?
            ");
            $stmt->execute([$log_id, $user_id]);
        }
        
        echo json_encode(['success' => true, 'message' => 'Medication marked as ' . $status]);
    } else {
        echo json_encode(['error' => 'Failed to update medication status']);
    }
}

// Clear any output buffering to prevent extra content in the JSON response
ob_end_flush();
?>