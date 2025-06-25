<?php

// Database setup script for healthcare module

// Include database connection
require_once 'db_connect.php';

try {
    // Check if tables exist
    $pdo->beginTransaction();
    
    // 1. Create specializations table if not exists
    $pdo->exec("
        CREATE TABLE IF NOT EXISTS `specializations` (
          `id` int(11) NOT NULL AUTO_INCREMENT,
          `name` varchar(100) NOT NULL,
          `description` text DEFAULT NULL,
          `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
          `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
          PRIMARY KEY (`id`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
    ");
    
    // 2. Create healthcare_providers table if not exists
    $pdo->exec("
        CREATE TABLE IF NOT EXISTS `healthcare_providers` (
          `id` int(11) NOT NULL AUTO_INCREMENT,
          `user_id` int(11) NOT NULL,
          `qualification` varchar(255) DEFAULT NULL,
          `experience_years` int(11) DEFAULT NULL,
          `consultation_fee` decimal(10,2) DEFAULT NULL,
          `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
          `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
          PRIMARY KEY (`id`),
          UNIQUE KEY `user_id` (`user_id`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
    ");
    
    // 3. Create healthcare_provider_specializations table if not exists
    $pdo->exec("
        CREATE TABLE IF NOT EXISTS `healthcare_provider_specializations` (
          `id` int(11) NOT NULL AUTO_INCREMENT,
          `provider_id` int(11) NOT NULL,
          `specialization_id` int(11) NOT NULL,
          `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
          PRIMARY KEY (`id`),
          KEY `provider_id` (`provider_id`),
          KEY `specialization_id` (`specialization_id`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
    ");
    
    // 4. Create reviews table if not exists
    $pdo->exec("
        CREATE TABLE IF NOT EXISTS `reviews` (
          `id` int(11) NOT NULL AUTO_INCREMENT,
          `doctor_id` int(11) NOT NULL,
          `user_id` int(11) NOT NULL,
          `rating` int(11) NOT NULL,
          `review_text` text DEFAULT NULL,
          `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
          PRIMARY KEY (`id`),
          KEY `doctor_id` (`doctor_id`),
          KEY `user_id` (`user_id`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
    ");
    
    // 5. Insert sample specializations if table is empty
    $stmt = $pdo->query("SELECT COUNT(*) FROM specializations");
    if ($stmt->fetchColumn() == 0) {
        $pdo->exec("
            INSERT INTO `specializations` (`name`, `description`) VALUES
            ('General Medicine', 'General practice medical care'),
            ('Cardiology', 'Heart and cardiovascular system care'),
            ('Neurology', 'Nervous system care'),
            ('Pediatrics', 'Child healthcare'),
            ('Orthopedics', 'Musculoskeletal system care'),
            ('Dermatology', 'Skin care'),
            ('Gynecology', 'Women\'s health care'),
            ('Ophthalmology', 'Eye care'),
            ('Psychiatry', 'Mental health care');
        ");
    }
    
    // 6. Create healthcare_provider record for the current user
    $userId = $_SESSION['user_id'] ?? null;
    
    if ($userId) {
        // Check if provider record already exists
        $stmt = $pdo->prepare("SELECT COUNT(*) FROM healthcare_providers WHERE user_id = ?");
        $stmt->execute([$userId]);
        
        if ($stmt->fetchColumn() == 0) {
            // Create provider record
            $stmt = $pdo->prepare("
                INSERT INTO healthcare_providers (user_id, qualification, experience_years, consultation_fee) 
                VALUES (?, 'MBBS', 5, 1000.00)
            ");
            $stmt->execute([$userId]);
            
            // Get last inserted provider ID
            $providerId = $pdo->lastInsertId();
            
            // Assign default specialization (General Medicine, ID: 1)
            $stmt = $pdo->prepare("
                INSERT INTO healthcare_provider_specializations (provider_id, specialization_id)
                VALUES (?, 1)
            ");
            $stmt->execute([$userId]);
        }
    }
    
    $pdo->commit();
    echo "Database setup completed successfully!";
    
} catch (PDOException $e) {
    $pdo->rollBack();
    echo "Database Error: " . $e->getMessage();
    exit;
}

// Redirect back to profile page
header("Location: profile.php");
exit;
?>