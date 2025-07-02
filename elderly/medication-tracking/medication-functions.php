<?php

require_once '../../signup_user/config.php'; // Database connection

/**
 * Get today's medications for a specific user
 */
function getTodaysMedications($user_id) {
    global $pdo;
    
    $today = date('Y-m-d');
    $current_time = date('H:i:s');
    
    // Time periods for medications
    $morning_end = '12:00:00';
    $afternoon_end = '18:00:00';
    $evening_end = '22:00:00';
    
    // Determine current time period
    $time_period = 'night';
    if ($current_time < $morning_end) {
        $time_period = 'morning';
    } elseif ($current_time < $afternoon_end) {
        $time_period = 'afternoon';
    } elseif ($current_time < $evening_end) {
        $time_period = 'evening';
    }
    
    // Get all medications for the user that are currently active
    $stmt = $pdo->prepare("
        SELECT m.*, ml.status, ml.taken_time, ml.scheduled_time
        FROM medications m
        LEFT JOIN (
            SELECT * FROM medication_logs 
            WHERE DATE(scheduled_time) = :today
            AND user_id = :user_id
        ) ml ON m.id = ml.medication_id
        WHERE m.user_id = :user_id
        AND (m.end_date IS NULL OR m.end_date >= :today)
        AND m.start_date <= :today
        ORDER BY 
            CASE
                WHEN ml.status = 'pending' AND ml.scheduled_time <= NOW() THEN 1
                WHEN ml.status = 'pending' THEN 2
                WHEN ml.status = 'taken' THEN 3
                WHEN ml.status = 'skipped' THEN 4
                WHEN ml.status = 'missed' THEN 5
                ELSE 6
            END,
            ml.scheduled_time
    ");
    
    $stmt->execute([
        'user_id' => $user_id,
        'today' => $today
    ]);
    
    $medications = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Organize medications by time of day
    $organized = [
        'morning' => [],
        'afternoon' => [],
        'evening' => [],
        'night' => []
    ];
    
    foreach ($medications as $med) {
        if ($med['morning']) {
            $organized['morning'][] = $med;
        }
        if ($med['afternoon']) {
            $organized['afternoon'][] = $med;
        }
        if ($med['evening']) {
            $organized['evening'][] = $med;
        }
        if (!$med['morning'] && !$med['afternoon'] && !$med['evening']) {
            $organized['night'][] = $med; // Default to night if no time specified
        }
    }
    
    return [
        'medications' => $organized,
        'current_time_period' => $time_period
    ];
}

/**
 * Get weekly medication history for a specific user
 */
function getWeeklyMedicationHistory($user_id) {
    global $pdo;
    
    $today = date('Y-m-d');
    $start_date = date('Y-m-d', strtotime('-6 days')); // Last 7 days including today
    
    $stmt = $pdo->prepare("
        SELECT 
            DATE(ml.scheduled_time) as med_date,
            COUNT(DISTINCT m.id) as total_medications,
            SUM(CASE WHEN ml.status = 'taken' THEN 1 ELSE 0 END) as taken,
            SUM(CASE WHEN ml.status = 'missed' THEN 1 ELSE 0 END) as missed,
            SUM(CASE WHEN ml.status = 'skipped' THEN 1 ELSE 0 END) as skipped,
            SUM(CASE WHEN ml.status = 'pending' THEN 1 ELSE 0 END) as pending
        FROM medication_logs ml
        JOIN medications m ON ml.medication_id = m.id
        WHERE ml.user_id = :user_id
        AND DATE(ml.scheduled_time) BETWEEN :start_date AND :today
        GROUP BY DATE(ml.scheduled_time)
        ORDER BY DATE(ml.scheduled_time) ASC
    ");
    
    $stmt->execute([
        'user_id' => $user_id,
        'start_date' => $start_date,
        'today' => $today
    ]);
    
    $history = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Fill in missing dates with 0 counts
    $complete_history = [];
    $current_date = new DateTime($start_date);
    $end_date = new DateTime($today);
    $end_date->modify('+1 day'); // To include today
    
    while ($current_date < $end_date) {
        $date_str = $current_date->format('Y-m-d');
        $day_found = false;
        
        foreach ($history as $day) {
            if ($day['med_date'] === $date_str) {
                $complete_history[] = $day;
                $day_found = true;
                break;
            }
        }
        
        if (!$day_found) {
            $complete_history[] = [
                'med_date' => $date_str,
                'total_medications' => 0,
                'taken' => 0,
                'missed' => 0,
                'skipped' => 0,
                'pending' => 0
            ];
        }
        
        $current_date->modify('+1 day');
    }
    
    return $complete_history;
}

/**
 * Get medication details for a specific medication
 */
function getMedicationDetails($medication_id, $user_id) {
    global $pdo;
    
    $stmt = $pdo->prepare("
        SELECT m.*, 
            IFNULL(p.first_name, '') || ' ' || IFNULL(p.last_name, '') as doctor_name
        FROM medications m
        LEFT JOIN users p ON m.prescribing_doctor = p.id
        WHERE m.id = :medication_id AND m.user_id = :user_id
    ");
    
    $stmt->execute([
        'medication_id' => $medication_id,
        'user_id' => $user_id
    ]);
    
    return $stmt->fetch(PDO::FETCH_ASSOC);
}

/**
 * Get all medications for a specific user
 */
function getAllMedications($user_id) {
    global $pdo;
    
    $stmt = $pdo->prepare("
        SELECT * FROM medications
        WHERE user_id = :user_id
        ORDER BY name
    ");
    
    $stmt->execute(['user_id' => $user_id]);
    
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

/**
 * Add a new medication for a user
 */
function addMedication($user_id, $medication_data) {
    global $pdo;
    
    try {
        $stmt = $pdo->prepare("
            INSERT INTO medications (
                user_id, name, dosage, frequency, start_date, end_date, 
                instructions, prescribing_doctor, pharmacy, refill_reminder_days,
                morning, afternoon, evening, night, with_food, created_at, updated_at
            ) VALUES (
                :user_id, :name, :dosage, :frequency, :start_date, :end_date,
                :instructions, :prescribing_doctor, :pharmacy, :refill_reminder_days,
                :morning, :afternoon, :evening, :night, :with_food, NOW(), NOW()
            )
        ");
        
        $result = $stmt->execute([
            'user_id' => $user_id,
            'name' => $medication_data['name'],
            'dosage' => $medication_data['dosage'],
            'frequency' => $medication_data['frequency'],
            'start_date' => $medication_data['start_date'],
            'end_date' => $medication_data['end_date'],
            'instructions' => $medication_data['instructions'],
            'prescribing_doctor' => $medication_data['prescribing_doctor'],
            'pharmacy' => $medication_data['pharmacy'],
            'refill_reminder_days' => $medication_data['refill_reminder_days'],
            'morning' => isset($medication_data['morning']) ? 1 : 0,
            'afternoon' => isset($medication_data['afternoon']) ? 1 : 0,
            'evening' => isset($medication_data['evening']) ? 1 : 0,
            'night' => isset($medication_data['night']) ? 1 : 0,
            'with_food' => isset($medication_data['with_food']) ? 1 : 0
        ]);
        
        if ($result) {
            $medication_id = $pdo->lastInsertId();
            
            // Create initial medication logs based on schedule
            createMedicationLogs($medication_id, $user_id, $medication_data);
            
            return $medication_id;
        }
        
        return false;
    } catch (PDOException $e) {
        error_log("Error adding medication: " . $e->getMessage());
        return false;
    }
}

/**
 * Create medication logs based on medication schedule
 */
function createMedicationLogs($medication_id, $user_id, $medication_data) {
    global $pdo;
    
    $start_date = new DateTime($medication_data['start_date']);
    $end_date = $medication_data['end_date'] ? new DateTime($medication_data['end_date']) : new DateTime('+30 days');
    
    $current_date = clone $start_date;
    
    while ($current_date <= $end_date) {
        $date_str = $current_date->format('Y-m-d');
        
        // Create logs for morning, afternoon, evening based on settings
        if (isset($medication_data['morning']) && $medication_data['morning']) {
            createLog($medication_id, $user_id, $date_str . ' 08:00:00');
        }
        
        if (isset($medication_data['afternoon']) && $medication_data['afternoon']) {
            createLog($medication_id, $user_id, $date_str . ' 14:00:00');
        }
        
        if (isset($medication_data['evening']) && $medication_data['evening']) {
            createLog($medication_id, $user_id, $date_str . ' 20:00:00');
        }
        
        if (isset($medication_data['night']) && $medication_data['night']) {
            createLog($medication_id, $user_id, $date_str . ' 22:00:00');
        }
        
        $current_date->modify('+1 day');
    }
}

/**
 * Create a single medication log entry
 */
function createLog($medication_id, $user_id, $scheduled_time) {
    global $pdo;
    
    $stmt = $pdo->prepare("
        INSERT INTO medication_logs (
            medication_id, user_id, scheduled_time, status, created_at, updated_at
        ) VALUES (
            :medication_id, :user_id, :scheduled_time, 'pending', NOW(), NOW()
        )
    ");
    
    $stmt->execute([
        'medication_id' => $medication_id,
        'user_id' => $user_id,
        'scheduled_time' => $scheduled_time
    ]);
}

/**
 * Update medication log status (taken, skipped, missed)
 */
function updateMedicationLogStatus($log_id, $user_id, $status) {
    global $pdo;
    
    $stmt = $pdo->prepare("
        UPDATE medication_logs
        SET status = :status, 
            taken_time = :taken_time,
            updated_at = NOW()
        WHERE id = :log_id AND user_id = :user_id
    ");
    
    $taken_time = ($status === 'taken') ? date('Y-m-d H:i:s') : null;
    
    return $stmt->execute([
        'status' => $status,
        'taken_time' => $taken_time,
        'log_id' => $log_id,
        'user_id' => $user_id
    ]);
}

/**
 * Import medications from doctor's prescription
 */
function importMedicationsFromPrescription($prescription_id, $user_id) {
    global $pdo;
    
    try {
        // Get prescription details
        $stmt = $pdo->prepare("
            SELECT * FROM prescriptions
            WHERE id = :prescription_id AND patient_id = :user_id
        ");
        
        $stmt->execute([
            'prescription_id' => $prescription_id,
            'user_id' => $user_id
        ]);
        
        $prescription = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if (!$prescription) {
            return false;
        }
        
        // Parse medications from the prescription
        $medications = json_decode($prescription['medications'], true);
        
        if (!is_array($medications)) {
            return false;
        }
        
        $added_count = 0;
        
        foreach ($medications as $med) {
            // Prepare medication data from prescription
            $medication_data = [
                'name' => $med['name'],
                'dosage' => $med['dosage'],
                'frequency' => $med['frequency'] ?? '',
                'start_date' => date('Y-m-d'),
                'end_date' => !empty($med['duration']) ? date('Y-m-d', strtotime('+' . intval($med['duration']) . ' days')) : null,
                'instructions' => $med['instructions'] ?? '',
                'prescribing_doctor' => $prescription['doctor_id'],
                'pharmacy' => '',
                'refill_reminder_days' => 7,
                'with_food' => strpos(strtolower($med['instructions'] ?? ''), 'food') !== false ? 1 : 0
            ];
            
            // Set time of day based on frequency
            $frequency_lower = strtolower($med['frequency'] ?? '');
            $medication_data['morning'] = strpos($frequency_lower, 'morning') !== false || strpos($frequency_lower, 'daily') !== false ? 1 : 0;
            $medication_data['afternoon'] = strpos($frequency_lower, 'afternoon') !== false || strpos($frequency_lower, 'twice') !== false ? 1 : 0;
            $medication_data['evening'] = strpos($frequency_lower, 'evening') !== false || strpos($frequency_lower, 'night') !== false || strpos($frequency_lower, 'twice') !== false ? 1 : 0;
            
            // Add medication
            $result = addMedication($user_id, $medication_data);
            
            if ($result) {
                $added_count++;
            }
        }
        
        return $added_count;
    } catch (PDOException $e) {
        error_log("Error importing medications from prescription: " . $e->getMessage());
        return false;
    }
}
?>