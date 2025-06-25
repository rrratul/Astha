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

// Handle marking notifications as read
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['mark_read']) && isset($_POST['notification_id'])) {
        $notification_id = $_POST['notification_id'];
        
        $stmt = $pdo->prepare("UPDATE notifications SET is_read = 1 WHERE id = :id AND user_id = :user_id");
        $stmt->execute(['id' => $notification_id, 'user_id' => $user_id]);
    } elseif (isset($_POST['mark_all_read'])) {
        $stmt = $pdo->prepare("UPDATE notifications SET is_read = 1 WHERE user_id = :user_id");
        $stmt->execute(['user_id' => $user_id]);
        
        $_SESSION['success_message'] = "All notifications marked as read.";
    } elseif (isset($_POST['delete_notification']) && isset($_POST['notification_id'])) {
        $notification_id = $_POST['notification_id'];
        
        $stmt = $pdo->prepare("DELETE FROM notifications WHERE id = :id AND user_id = :user_id");
        $stmt->execute(['id' => $notification_id, 'user_id' => $user_id]);
    }
    
    // Redirect to avoid form resubmission
    header("Location: notifications.php");
    exit();
}

// Pagination and filtering
$filter_type = $_GET['type'] ?? 'all';
$filter_read = isset($_GET['read']) ? $_GET['read'] : 'all';
$page = isset($_GET['page']) ? max(1, intval($_GET['page'])) : 1;
$per_page = 15;
$offset = ($page - 1) * $per_page;

// Base query
$query = "
    SELECT * FROM notifications 
    WHERE user_id = :user_id
";

$params = ['user_id' => $user_id];

// Apply filters
if ($filter_type !== 'all') {
    $query .= " AND notification_type = :notification_type";
    $params['notification_type'] = $filter_type;
}

if ($filter_read !== 'all') {
    $query .= " AND is_read = :is_read";
    $params['is_read'] = ($filter_read === 'read') ? 1 : 0;
}

// Get total count for pagination
$count_stmt = $pdo->prepare(str_replace("SELECT *", "SELECT COUNT(*)", $query));
$count_stmt->execute($params);
$total_records = $count_stmt->fetchColumn();
$total_pages = ceil($total_records / $per_page);

// Add sorting and pagination
$query .= " ORDER BY created_at DESC LIMIT :offset, :per_page";
$stmt = $pdo->prepare($query);

// Need to bind these separately because of LIMIT clause
foreach ($params as $key => $value) {
    $stmt->bindValue(":{$key}", $value);
}
$stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
$stmt->bindValue(':per_page', $per_page, PDO::PARAM_INT);
$stmt->execute();

$notifications = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Count unread notifications for the badge
$unread_stmt = $pdo->prepare("SELECT COUNT(*) FROM notifications WHERE user_id = :user_id AND is_read = 0");
$unread_stmt->execute(['user_id' => $user_id]);
$unread_count = $unread_stmt->fetchColumn();

// Create notifications table if it doesn't exist
function ensureNotificationsTableExists($pdo) {
    try {
        $pdo->query("SELECT 1 FROM notifications LIMIT 1");
    } catch (PDOException $e) {
        // Table doesn't exist, create it
        $pdo->exec("
            CREATE TABLE IF NOT EXISTS `notifications` (
                `id` int(11) NOT NULL AUTO_INCREMENT,
                `user_id` int(11) NOT NULL,
                `title` varchar(100) NOT NULL,
                `content` text NOT NULL,
                `notification_type` varchar(50) DEFAULT 'general',
                `reference_id` int(11) DEFAULT NULL,
                `reference_type` varchar(50) DEFAULT NULL,
                `is_read` tinyint(1) DEFAULT 0,
                `created_by` int(11) DEFAULT NULL,
                `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
                PRIMARY KEY (`id`),
                KEY `user_id` (`user_id`),
                KEY `notification_type` (`notification_type`),
                KEY `is_read` (`is_read`),
                CONSTRAINT `notifications_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
        ");
    }
}

// Ensure notifications table exists
ensureNotificationsTableExists($pdo);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Notifications - Healthcare Provider Dashboard</title>
    
    <!-- Include your CSS files here -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@400;500;600;700&family=Open+Sans:wght@400;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <link rel="stylesheet" href="../elderly/includes/style.css">
    <link rel="stylesheet" href="./css/healthcare.css">
    
    <style>
        .notifications-container {
            max-width: 1000px;
            margin: 20px auto;
            padding: 0 20px;
        }
        
        .page-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 25px;
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
        
        .action-button {
            background-color: #0096ad;
            color: white;
            border: none;
            padding: 12px 20px;
            border-radius: 8px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s;
            display: flex;
            align-items: center;
            gap: 8px;
        }
        
        .action-button:hover {
            background-color: #007a8c;
            transform: translateY(-2px);
        }
        
        .filter-bar {
            display: flex;
            justify-content: space-between;
            align-items: center;
            background-color: white;
            padding: 15px 20px;
            border-radius: 12px;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.05);
            margin-bottom: 25px;
        }
        
        .filter-group {
            display: flex;
            gap: 15px;
        }
        
        .filter-select {
            padding: 8px 12px;
            border: 1px solid #e2e8f0;
            border-radius: 6px;
            font-size: 14px;
        }
        
        .notifications-list {
            background-color: white;
            border-radius: 12px;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.05);
            overflow: hidden;
        }
        
        .notification-item {
            display: flex;
            padding: 20px;
            border-bottom: 1px solid #e2e8f0;
            transition: background-color 0.2s;
        }
        
        .notification-item:last-child {
            border-bottom: none;
        }
        
        .notification-item:hover {
            background-color: #f7fafc;
        }
        
        .notification-item.unread {
            background-color: #ebf8ff;
        }
        
        .notification-item.unread:hover {
            background-color: #e3f2fd;
        }
        
        .notification-icon {
            width: 50px;
            height: 50px;
            border-radius: 50%;
            background-color: #e2e8f0;
            display: flex;
            align-items: center;
            justify-content: center;
            margin-right: 15px;
            color: #718096;
            flex-shrink: 0;
        }
        
        .notification-icon.appointment {
            background-color: #e3f2fd;
            color: #1976d2;
        }
        
        .notification-icon.alert {
            background-color: #fed7d7;
            color: #e53e3e;
        }
        
        .notification-icon.metrics {
            background-color: #c6f6d5;
            color: #22543d;
        }
        
        .notification-content {
            flex: 1;
        }
        
        .notification-header {
            display: flex;
            justify-content: space-between;
            margin-bottom: 5px;
        }
        
        .notification-title {
            font-weight: 600;
            color: #2d3748;
            margin: 0;
        }
        
        .notification-time {
            color: #718096;
            font-size: 14px;
        }
        
        .notification-text {
            color: #4a5568;
            margin: 0;
            line-height: 1.5;
        }
        
        .notification-actions {
            display: flex;
            gap: 10px;
            margin-left: 15px;
            align-items: flex-start;
        }
        
        .notification-btn {
            background: none;
            border: none;
            color: #718096;
            cursor: pointer;
            padding: 5px;
            transition: all 0.2s;
            display: flex;
            align-items: center;
            justify-content: center;// filepath: c:\xampp\htdocs\Astha\healthcare\notifications.php
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

// Handle marking notifications as read
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['mark_read']) && isset($_POST['notification_id'])) {
        $notification_id = $_POST['notification_id'];
        
        $stmt = $pdo->prepare("UPDATE notifications SET is_read = 1 WHERE id = :id AND user_id = :user_id");
        $stmt->execute(['id' => $notification_id, 'user_id' => $user_id]);
    } elseif (isset($_POST['mark_all_read'])) {
        $stmt = $pdo->prepare("UPDATE notifications SET is_read = 1 WHERE user_id = :user_id");
        $stmt->execute(['user_id' => $user_id]);
        
        $_SESSION['success_message'] = "All notifications marked as read.";
    } elseif (isset($_POST['delete_notification']) && isset($_POST['notification_id'])) {
        $notification_id = $_POST['notification_id'];
        
        $stmt = $pdo->prepare("DELETE FROM notifications WHERE id = :id AND user_id = :user_id");
        $stmt->execute(['id' => $notification_id, 'user_id' => $user_id]);
    }
    
    // Redirect to avoid form resubmission
    header("Location: notifications.php");
    exit();
}

// Pagination and filtering
$filter_type = $_GET['type'] ?? 'all';
$filter_read = isset($_GET['read']) ? $_GET['read'] : 'all';
$page = isset($_GET['page']) ? max(1, intval($_GET['page'])) : 1;
$per_page = 15;
$offset = ($page - 1) * $per_page;

// Base query
$query = "
    SELECT * FROM notifications 
    WHERE user_id = :user_id
";

$params = ['user_id' => $user_id];

// Apply filters
if ($filter_type !== 'all') {
    $query .= " AND notification_type = :notification_type";
    $params['notification_type'] = $filter_type;
}

if ($filter_read !== 'all') {
    $query .= " AND is_read = :is_read";
    $params['is_read'] = ($filter_read === 'read') ? 1 : 0;
}

// Get total count for pagination
$count_stmt = $pdo->prepare(str_replace("SELECT *", "SELECT COUNT(*)", $query));
$count_stmt->execute($params);
$total_records = $count_stmt->fetchColumn();
$total_pages = ceil($total_records / $per_page);

// Add sorting and pagination
$query .= " ORDER BY created_at DESC LIMIT :offset, :per_page";
$stmt = $pdo->prepare($query);

// Need to bind these separately because of LIMIT clause
foreach ($params as $key => $value) {
    $stmt->bindValue(":{$key}", $value);
}
$stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
$stmt->bindValue(':per_page', $per_page, PDO::PARAM_INT);
$stmt->execute();

$notifications = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Count unread notifications for the badge
$unread_stmt = $pdo->prepare("SELECT COUNT(*) FROM notifications WHERE user_id = :user_id AND is_read = 0");
$unread_stmt->execute(['user_id' => $user_id]);
$unread_count = $unread_stmt->fetchColumn();

// Create notifications table if it doesn't exist
function ensureNotificationsTableExists($pdo) {
    try {
        $pdo->query("SELECT 1 FROM notifications LIMIT 1");
    } catch (PDOException $e) {
        // Table doesn't exist, create it
        $pdo->exec("
            CREATE TABLE IF NOT EXISTS `notifications` (
                `id` int(11) NOT NULL AUTO_INCREMENT,
                `user_id` int(11) NOT NULL,
                `title` varchar(100) NOT NULL,
                `content` text NOT NULL,
                `notification_type` varchar(50) DEFAULT 'general',
                `reference_id` int(11) DEFAULT NULL,
                `reference_type` varchar(50) DEFAULT NULL,
                `is_read` tinyint(1) DEFAULT 0,
                `created_by` int(11) DEFAULT NULL,
                `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
                PRIMARY KEY (`id`),
                KEY `user_id` (`user_id`),
                KEY `notification_type` (`notification_type`),
                KEY `is_read` (`is_read`),
                CONSTRAINT `notifications_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
        ");
    }
}

// Ensure notifications table exists
ensureNotificationsTableExists($pdo);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Notifications - Healthcare Provider Dashboard</title>
    
    <!-- Include your CSS files here -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@400;500;600;700&family=Open+Sans:wght@400;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <link rel="stylesheet" href="../elderly/includes/style.css">
    <link rel="stylesheet" href="./css/healthcare.css">
    
    <style>
        .notifications-container {
            max-width: 1000px;
            margin: 20px auto;
            padding: 0 20px;
        }
        
        .page-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 25px;
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
        
        .action-button {
            background-color: #0096ad;
            color: white;
            border: none;
            padding: 12px 20px;
            border-radius: 8px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s;
            display: flex;
            align-items: center;
            gap: 8px;
        }
        
        .action-button:hover {
            background-color: #007a8c;
            transform: translateY(-2px);
        }
        
        .filter-bar {
            display: flex;
            justify-content: space-between;
            align-items: center;
            background-color: white;
            padding: 15px 20px;
            border-radius: 12px;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.05);
            margin-bottom: 25px;
        }
        
        .filter-group {
            display: flex;
            gap: 15px;
        }
        
        .filter-select {
            padding: 8px 12px;
            border: 1px solid #e2e8f0;
            border-radius: 6px;
            font-size: 14px;
        }
        
        .notifications-list {
            background-color: white;
            border-radius: 12px;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.05);
            overflow: hidden;
        }
        
        .notification-item {
            display: flex;
            padding: 20px;
            border-bottom: 1px solid #e2e8f0;
            transition: background-color 0.2s;
        }
        
        .notification-item:last-child {
            border-bottom: none;
        }
        
        .notification-item:hover {
            background-color: #f7fafc;
        }
        
        .notification-item.unread {
            background-color: #ebf8ff;
        }
        
        .notification-item.unread:hover {
            background-color: #e3f2fd;
        }
        
        .notification-icon {
            width: 50px;
            height: 50px;
            border-radius: 50%;
            background-color: #e2e8f0;
            display: flex;
            align-items: center;
            justify-content: center;
            margin-right: 15px;
            color: #718096;
            flex-shrink: 0;
        }
        
        .notification-icon.appointment {
            background-color: #e3f2fd;
            color: #1976d2;
        }
        
        .notification-icon.alert {
            background-color: #fed7d7;
            color: #e53e3e;
        }
        
        .notification-icon.metrics {
            background-color: #c6f6d5;
            color: #22543d;
        }
        
        .notification-content {
            flex: 1;
        }
        
        .notification-header {
            display: flex;
            justify-content: space-between;
            margin-bottom: 5px;
        }
        
        .notification-title {
            font-weight: 600;
            color: #2d3748;
            margin: 0;
        }
        
        .notification-time {
            color: #718096;
            font-size: 14px;
        }
        
        .notification-text {
            color: #4a5568;
            margin: 0;
            line-height: 1.5;
        }
        
        .notification-actions {
            display: flex;
            gap: 10px;
            margin-left: 15px;
            align-items: flex-start;
        }
        
        .notification-btn {
            background: