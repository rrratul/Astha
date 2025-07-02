<?php
require_once '../../Astha/includes/config.php';

// Auto-approve pending connections that are older than 24 hours
$stmt = $conn->prepare("
    UPDATE relative_connections 
    SET status = 'approved', updated_at = NOW()
    WHERE status = 'pending' 
    AND TIMESTAMPDIFF(HOUR, created_at, NOW()) >= 24
");

$stmt->execute();
echo "Updated " . $stmt->affected_rows . " pending connections to approved status.";
$stmt->close();
?>