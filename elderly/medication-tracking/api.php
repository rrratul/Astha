<?php
// filepath: c:\xampp\htdocs\Astha\elderly\medication-tracking\api.php
session_start();
require_once '../../signup_user/config.php';

// Check if user is logged in
if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true) {
    http_response_code(401);
    echo json_encode(['error' => 'Unauthorized']);
    exit();
}

$user_id = $_SESSION['user_id'];
$user_role = $_SESSION['user_role'];

header('Content-Type: application/json');

try {
    $action = $_GET['action'] ?? $_POST['action'] ?? '';
    
    switch ($action) {
        case 'get_today_medications':
            getTodayMedications($pdo, $user_id);
            break;
            
        case 'get_medication_history':
            getMedicationHistory($pdo, $user_id);
            break;
            
        case 'get_medication_details':
            getMedicationDetails($pdo, $user_id);
            break;
            
        case 'add_medication':
            addMedication($pdo, $user_id);
            break;
            
        case 'mark_medication_taken':
            markMedicationTaken($pdo, $user_id);
            break;
            
        case 'get_prescriptions':
            getPrescriptions($pdo, $user_id);
            break;
            
        case 'sync_prescription_medications':
            syncPrescriptionMedications($pdo, $user_id);
            break;
            
        default:
            http_response_code(400);
            echo json_encode(['error' => 'Invalid action']);
            break;
    }
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['error' => $e->getMessage()]);
}

function getTodayMedications($pdo, $user_id) {
    $today = date('Y-m-d');
    
    // Get active medications for today
    $stmt = $pdo->prepare("
        SELECT m.*, 
               COALESCE(ml.status, 'pending') as current_status,
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
    $medications = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Create medication schedule for today if it doesn't exist
    foreach ($medications as &$med) {
        if (empty($med['log_id'])) {
            createTodaySchedule($pdo, $med['id'], $user_id, $med['name'], $med['dosage'], $med);
            $med['current_status'] = 'pending';
        }
        
        // Determine status based on time
        if ($med['current_status'] === 'pending' && $med['scheduled_time']) {
            $now = new DateTime();
            $scheduledTime = new DateTime($med['scheduled_time']);
            if ($now > $scheduledTime) {
                $med['current_status'] = 'overdue';
            }
        }
    }
    
    // Group by time periods
    $grouped = [
        'morning' => [],
        'afternoon' => [],
        'evening' => [],
        'night' => []
    ];
    
    foreach ($medications as $med) {
        if ($med['scheduled_time']) {
            $hour = (int)date('H', strtotime($med['scheduled_time']));
            if ($hour >= 6 && $hour < 12) {
                $grouped['morning'][] = $med;
            } elseif ($hour >= 12 && $hour < 17) {
                $grouped['afternoon'][] = $med;
            } elseif ($hour >= 17 && $hour < 22) {
                $grouped['evening'][] = $med;
            } else {
                $grouped['night'][] = $med;
            }
        }
    }
    
    echo json_encode(['success' => true, 'data' => $grouped]);
}

function createTodaySchedule($pdo, $medication_id, $user_id, $name, $dosage, $medication) {
    $today = date('Y-m-d');
    
    $times = [];
    if ($medication['morning']) $times[] = '08:00:00';
    if ($medication['afternoon']) $times[] = '14:00:00';
    if ($medication['evening']) $times[] = '20:00:00';
    if ($medication['night']) $times[] = '22:00:00';
    
    foreach ($times as $time) {
        $stmt = $pdo->prepare("
            INSERT IGNORE INTO medication_logs 
            (medication_id, user_id, medication_name, dosage, scheduled_time, status)
            VALUES (?, ?, ?, ?, ?, 'pending')
        ");
        $stmt->execute([$medication_id, $user_id, $name, $dosage, $today . ' ' . $time]);
    }
}

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
    $history = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo json_encode(['success' => true, 'data' => $history]);
}

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
    ");
    
    $stmt->execute([$user_id]);
    $medications = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo json_encode(['success' => true, 'data' => $medications]);
}

function addMedication($pdo, $user_id) {
    $name = $_POST['name'] ?? '';
    $dosage = $_POST['dosage'] ?? '';
    $frequency = $_POST['frequency'] ?? '';
    $start_date = $_POST['start_date'] ?? date('Y-m-d');
    $end_date = $_POST['end_date'] ?? null;
    $instructions = $_POST['instructions'] ?? '';
    $prescribing_doctor = $_POST['prescribing_doctor'] ?? '';
    $total_pills = (int)($_POST['total_pills'] ?? 0);
    $morning = isset($_POST['morning']) ? 1 : 0;
    $afternoon = isset($_POST['afternoon']) ? 1 : 0;
    $evening = isset($_POST['evening']) ? 1 : 0;
    $night = isset($_POST['night']) ? 1 : 0;
    $with_food = isset($_POST['with_food']) ? 1 : 0;
    
    if (empty($name) || empty($dosage)) {
        http_response_code(400);
        echo json_encode(['error' => 'Name and dosage are required']);
        return;
    }
    
    $stmt = $pdo->prepare("
        INSERT INTO medications (
            user_id, name, dosage, frequency, start_date, end_date, 
            instructions, prescribing_doctor, total_pills, remaining_pills,
            morning, afternoon, evening, night, with_food, is_active
        ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 1)
    ");
    
    $success = $stmt->execute([
        $user_id, $name, $dosage, $frequency, $start_date, $end_date,
        $instructions, $prescribing_doctor, $total_pills, $total_pills,
        $morning, $afternoon, $evening, $night, $with_food
    ]);
    
    if ($success) {
        $medication_id = $pdo->lastInsertId();
        
        // Create medication schedule logs
        createMedicationSchedule($pdo, $medication_id, $user_id, $start_date, $end_date, $morning, $afternoon, $evening, $night, $name, $dosage);
        
        echo json_encode(['success' => true, 'medication_id' => $medication_id]);
    } else {
        http_response_code(500);
        echo json_encode(['error' => 'Failed to add medication']);
    }
}

function markMedicationTaken($pdo, $user_id) {
    $log_id = $_POST['log_id'] ?? 0;
    $status = $_POST['status'] ?? 'taken';
    $notes = $_POST['notes'] ?? '';
    
    $stmt = $pdo->prepare("
        UPDATE medication_logs 
        SET status = ?, taken_time = CURRENT_TIMESTAMP, notes = ?
        WHERE id = ? AND user_id = ?
    ");
    
    $success = $stmt->execute([$status, $notes, $log_id, $user_id]);
    
    if ($success) {
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
        
        echo json_encode(['success' => true]);
    } else {
        http_response_code(500);
        echo json_encode(['error' => 'Failed to update medication status']);
    }
}

function getPrescriptions($pdo, $user_id) {
    $stmt = $pdo->prepare("
        SELECT p.*, 
               CONCAT(u.first_name, ' ', u.last_name) as doctor_name
        FROM prescriptions p
        LEFT JOIN users u ON p.doctor_id = u.id
        WHERE p.patient_id = ?
        ORDER BY p.created_at DESC
    ");
    
    $stmt->execute([$user_id]);
    $prescriptions = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Decode medications JSON for each prescription
    foreach ($prescriptions as &$prescription) {
        if ($prescription['medications']) {
            $prescription['medications'] = json_decode($prescription['medications'], true);
        }
    }
    
    echo json_encode(['success' => true, 'data' => $prescriptions]);
}

function syncPrescriptionMedications($pdo, $user_id) {
    $prescription_id = $_POST['prescription_id'] ?? 0;
    
    // Get prescription details
    $stmt = $pdo->prepare("
        SELECT * FROM prescriptions WHERE id = ? AND patient_id = ?
    ");
    $stmt->execute([$prescription_id, $user_id]);
    $prescription = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$prescription) {
        http_response_code(404);
        echo json_encode(['error' => 'Prescription not found']);
        return;
    }
    
    $medications = json_decode($prescription['medications'], true);
    $added_count = 0;
    
    foreach ($medications as $med) {
        // Check if medication already exists
        $stmt = $pdo->prepare("
            SELECT id FROM medications 
            WHERE user_id = ? AND prescription_id = ? AND name = ? AND dosage = ?
        ");
        $stmt->execute([$user_id, $prescription_id, $med['name'], $med['dosage']]);
        
        if ($stmt->rowCount() == 0) {
            // Add new medication
            $frequency_times = parseFrequency($med['frequency']);
            
            $stmt = $pdo->prepare("
                INSERT INTO medications (
                    user_id, prescription_id, name, dosage, frequency, 
                    start_date, instructions, prescribing_doctor,
                    morning, afternoon, evening, night, is_active, total_pills, remaining_pills
                ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 1, ?, ?)
            ");
            
            $total_pills = $med['quantity_prescribed'] ?? 30;
            
            $success = $stmt->execute([
                $user_id, $prescription_id, $med['name'], $med['dosage'], 
                $med['frequency'], date('Y-m-d'), $med['pharmacy_instructions'] ?? '',
                $prescription['doctor_name'] ?? '', 
                $frequency_times['morning'], $frequency_times['afternoon'],
                $frequency_times['evening'], $frequency_times['night'],
                $total_pills, $total_pills
            ]);
            
            if ($success) {
                $medication_id = $pdo->lastInsertId();
                createMedicationSchedule($pdo, $medication_id, $user_id, date('Y-m-d'), null, 
                    $frequency_times['morning'], $frequency_times['afternoon'],
                    $frequency_times['evening'], $frequency_times['night'],
                    $med['name'], $med['dosage']);
                $added_count++;
            }
        }
    }
    
    echo json_encode(['success' => true, 'added_count' => $added_count]);
}

function createMedicationSchedule($pdo, $medication_id, $user_id, $start_date, $end_date, $morning, $afternoon, $evening, $night, $name, $dosage) {
    $current_date = new DateTime($start_date);
    $end_date_obj = $end_date ? new DateTime($end_date) : new DateTime('+30 days');
    
    while ($current_date <= $end_date_obj) {
        $date_str = $current_date->format('Y-m-d');
        
        if ($morning) {
            createScheduleEntry($pdo, $medication_id, $user_id, $date_str . ' 08:00:00', $name, $dosage);
        }
        if ($afternoon) {
            createScheduleEntry($pdo, $medication_id, $user_id, $date_str . ' 14:00:00', $name, $dosage);
        }
        if ($evening) {
            createScheduleEntry($pdo, $medication_id, $user_id, $date_str . ' 20:00:00', $name, $dosage);
        }
        if ($night) {
            createScheduleEntry($pdo, $medication_id, $user_id, $date_str . ' 22:00:00', $name, $dosage);
        }
        
        $current_date->add(new DateInterval('P1D'));
    }
}

function createScheduleEntry($pdo, $medication_id, $user_id, $scheduled_time, $name, $dosage) {
    $stmt = $pdo->prepare("
        INSERT IGNORE INTO medication_logs (medication_id, user_id, medication_name, dosage, scheduled_time, status)
        VALUES (?, ?, ?, ?, ?, 'pending')
    ");
    $stmt->execute([$medication_id, $user_id, $name, $dosage, $scheduled_time]);
}

function parseFrequency($frequency) {
    $times = ['morning' => 0, 'afternoon' => 0, 'evening' => 0, 'night' => 0];
    
    $frequency = strtolower($frequency);
    
    if (strpos($frequency, 'once') !== false || strpos($frequency, '1') !== false) {
        $times['morning'] = 1;
    } elseif (strpos($frequency, 'twice') !== false || strpos($frequency, '2') !== false) {
        $times['morning'] = 1;
        $times['evening'] = 1;
    } elseif (strpos($frequency, 'three') !== false || strpos($frequency, '3') !== false) {
        $times['morning'] = 1;
        $times['afternoon'] = 1;
        $times['evening'] = 1;
    } elseif (strpos($frequency, 'four') !== false || strpos($frequency, '4') !== false) {
        $times['morning'] = 1;
        $times['afternoon'] = 1;
        $times['evening'] = 1;
        $times['night'] = 1;
    }
    
    return $times;
}
?>