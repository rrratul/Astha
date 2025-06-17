<?php
session_start();

// Check if user is logged in
if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true) {
    header('Location: ../../login_user/login_user.php');
    exit();
}

// Get user information
$user_id = $_SESSION['user_id'];
$user_name = $_SESSION['user_name'];
$user_role = $_SESSION['user_role'];
require_once 'includes/functions.php';

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$userId = $_SESSION['user_id'];
$message = '';
$contact = null;

// Handle form submission for adding/updating contact
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (isset($_POST['action'])) {
        if ($_POST['action'] == 'add' || $_POST['action'] == 'update') {
            $name = trim($_POST['name']);
            $relationship = trim($_POST['relationship']);
            $phone = trim($_POST['phone']);
            $email = trim($_POST['email']);
            $address = trim($_POST['address']);
            $isEmergency = isset($_POST['is_emergency']) ? 1 : 0;
            
            // Validate form data
            if (empty($name) || empty($phone)) {
                $message = "Name and phone number are required.";
            } else {
                if ($_POST['action'] == 'add') {
                    // Add new contact
                    $stmt = $conn->prepare("INSERT INTO emergency_contacts 
                        (user_id, name, relationship, phone, email, address, is_emergency) 
                        VALUES (?, ?, ?, ?, ?, ?, ?)");
                    $stmt->bind_param("isssssi", $userId, $name, $relationship, $phone, $email, $address, $isEmergency);
                    
                    if ($stmt->execute()) {
                        $message = "Contact added successfully.";
                    } else {
                        $message = "Error adding contact: " . $conn->error;
                    }
                } else if ($_POST['action'] == 'update' && isset($_POST['contact_id'])) {
                    // Update existing contact
                    $contactId = $_POST['contact_id'];
                    $stmt = $conn->prepare("UPDATE emergency_contacts 
                        SET name = ?, relationship = ?, phone = ?, email = ?, address = ?, is_emergency = ? 
                        WHERE id = ? AND user_id = ?");
                    $stmt->bind_param("sssssiii", $name, $relationship, $phone, $email, $address, $isEmergency, $contactId, $userId);
                    
                    if ($stmt->execute()) {
                        $message = "Contact updated successfully.";
                    } else {
                        $message = "Error updating contact: " . $conn->error;
                    }
                }
            }
        } else if ($_POST['action'] == 'delete' && isset($_POST['contact_id'])) {
            // Delete contact
            $contactId = $_POST['contact_id'];
            $stmt = $conn->prepare("DELETE FROM emergency_contacts WHERE id = ? AND user_id = ?");
            $stmt->bind_param("ii", $contactId, $userId);
            
            if ($stmt->execute()) {
                $message = "Contact deleted successfully.";
            } else {
                $message = "Error deleting contact: " . $conn->error;
            }
        }
    }
}

// Fetch single contact for editing
if (isset($_GET['edit']) && is_numeric($_GET['edit'])) {
    $contactId = $_GET['edit'];
    $stmt = $conn->prepare("SELECT * FROM emergency_contacts WHERE id = ? AND user_id = ?");
    $stmt->bind_param("ii", $contactId, $userId);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows == 1) {
        $contact = $result->fetch_assoc();
    }
}

// Fetch all emergency contacts for this user
$stmt = $conn->prepare("SELECT * FROM emergency_contacts WHERE user_id = ? ORDER BY is_emergency DESC, name ASC");
$stmt->bind_param("i", $userId);
$stmt->execute();
$contactsList = $stmt->get_result();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Emergency Contacts - Shohochor</title>
    <link rel="stylesheet" href="assets/css/style.css">
    <style>
        .contact-form {
            max-width: 600px;
            margin-bottom: 30px;
        }
        .contact-form input[type="text"],
        .contact-form input[type="email"],
        .contact-form input[type="tel"],
        .contact-form textarea {
            width: 100%;
            padding: 8px;
            margin-bottom: 10px;
            border: 1px solid #ddd;
            border-radius: 4px;
        }
        .contact-table {
            width: 100%;
            border-collapse: collapse;
        }
        .contact-table th, .contact-table td {
            border: 1px solid #ddd;
            padding: 8px;
            text-align: left;
        }
        .contact-table th {
            background-color: #f2f2f2;
        }
        .emergency-contact {
            background-color: #fff8f8;
        }
        .action-buttons {
            display: flex;
            gap: 5px;
        }
        .action-buttons button, .action-buttons a {
            padding: 5px 10px;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            text-decoration: none;
            font-size: 14px;
            text-align: center;
        }
        .edit-btn {
            background-color: #4CAF50;
            color: white;
        }
        .delete-btn {
            background-color: #f44336;
            color: white;
        }
    </style>
</head>
<body>
    <?php include 'includes/header.php'; ?>
    
    <main class="container">
        <h1>Emergency Contacts</h1>
        
        <?php if (!empty($message)): ?>
            <div class="alert <?php echo strpos($message, 'Error') !== false ? 'alert-danger' : 'alert-success'; ?>">
                <?php echo $message; ?>
            </div>
        <?php endif; ?>
        
        <div class="contact-form">
            <h2><?php echo $contact ? 'Edit Contact' : 'Add New Contact'; ?></h2>
            <form method="post" action="emergency-contacts.php">
                <input type="hidden" name="action" value="<?php echo $contact ? 'update' : 'add'; ?>">
                <?php if ($contact): ?>
                    <input type="hidden" name="contact_id" value="<?php echo $contact['id']; ?>">
                <?php endif; ?>
                
                <div class="form-group">
                    <label for="name">Name *</label>
                    <input type="text" id="name" name="name" value="<?php echo $contact ? htmlspecialchars($contact['name']) : ''; ?>" required>
                </div>
                
                <div class="form-group">
                    <label for="relationship">Relationship</label>
                    <input type="text" id="relationship" name="relationship" value="<?php echo $contact ? htmlspecialchars($contact['relationship']) : ''; ?>">
                </div>
                
                <div class="form-group">
                    <label for="phone">Phone Number *</label>
                    <input type="tel" id="phone" name="phone" value="<?php echo $contact ? htmlspecialchars($contact['phone']) : ''; ?>" required>
                </div>
                
                <div class="form-group">
                    <label for="email">Email</label>
                    <input type="email" id="email" name="email" value="<?php echo $contact ? htmlspecialchars($contact['email']) : ''; ?>">
                </div>
                
                <div class="form-group">
                    <label for="address">Address</label>
                    <textarea id="address" name="address" rows="3"><?php echo $contact ? htmlspecialchars($contact['address']) : ''; ?></textarea>
                </div>
                
                <div class="form-group">
                    <label>
                        <input type="checkbox" name="is_emergency" value="1" <?php echo ($contact && $contact['is_emergency']) ? 'checked' : ''; ?>>
                        Primary Emergency Contact
                    </label>
                </div>
                
                <div class="form-group">
                    <button type="submit" class="btn btn-primary"><?php echo $contact ? 'Update Contact' : 'Add Contact'; ?></button>
                    <?php if ($contact): ?>
                        <a href="emergency-contacts.php" class="btn btn-secondary">Cancel</a>
                    <?php endif; ?>
                </div>
            </form>
        </div>
        
        <h2>Your Contacts</h2>
        <?php if ($contactsList->num_rows > 0): ?>
            <table class="contact-table">
                <thead>
                    <tr>
                        <th>Name</th>
                        <th>Relationship</th>
                        <th>Phone</th>
                        <th>Email</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while ($row = $contactsList->fetch_assoc()): ?>
                        <tr class="<?php echo $row['is_emergency'] ? 'emergency-contact' : ''; ?>">
                            <td>
                                <?php echo htmlspecialchars($row['name']); ?>
                                <?php if ($row['is_emergency']): ?>
                                    <span class="badge">Primary</span>
                                <?php endif; ?>
                            </td>
                            <td><?php echo htmlspecialchars($row['relationship']); ?></td>
                            <td><?php echo htmlspecialchars($row['phone']); ?></td>
                            <td><?php echo htmlspecialchars($row['email']); ?></td>
                            <td class="action-buttons">
                                <a href="emergency-contacts.php?edit=<?php echo $row['id']; ?>" class="edit-btn">Edit</a>
                                <form method="post" action="emergency-contacts.php" onsubmit="return confirm('Are you sure you want to delete this contact?');" style="display:inline;">
                                    <input type="hidden" name="action" value="delete">
                                    <input type="hidden" name="contact_id" value="<?php echo $row['id']; ?>">
                                    <button type="submit" class="delete-btn">Delete</button>
                                </form>
                            </td>
                        </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        <?php else: ?>
            <p>No contacts found. Add your first emergency contact using the form above.</p>
        <?php endif; ?>
    </main>
    
    <?php include 'includes/footer.php'; ?>
    
    <script>
        // Add any client-side validation or interactions here
    </script>
</body>
</html>