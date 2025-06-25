<?php
session_start();

// Include required files
require_once '../../login_user/functions.php';
require_once '../../signup_user/config.php';
require_once 'fallback_responses.php';

// Authentication checks
if (!isLoggedIn()) {
    header("Location: ../../login_user/login_user.php");
    exit();
}

// Role checking
if (isset($_SESSION['user_role'])) {
    $allowed_roles = ['', 'elderly']; 
    if (!in_array($_SESSION['user_role'], $allowed_roles)) {
        switch ($_SESSION['user_role']) {
            case 'caregiver':
                header("Location: ../../caregiver/index.php");
                exit();
            case 'family_member':
                header("Location: ../../family/index.php");
                exit();
            case 'healthcare_provider':
                header("Location: ../../healthcare/index.php");
                exit();
            default:
                header("Location: ../logout.php");
                exit();
        }
    }
}

// Get user information
$user_id = $_SESSION['user_id'];
$user_name = $_SESSION['user_name'] ?? 'User';
$user_first_name = $_SESSION['user_first_name'] ?? 'User';

// Gemini API configuration - store actual key in environment variable or secure config in production
$gemini_api_key = getenv('GEMINI_API_KEY') ?: 'AIzaSyCw0tSxf2cvHp4hP308ZxahGCs05iI45jg'; // Replace with your actual API key for testing

// Process API request if form submitted
$ai_response = '';
$chat_history = $_SESSION['gemini_chat_history'] ?? [];

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['query'])) {
    $user_query = trim($_POST['query']);
    
    // Add user query to chat history
    $chat_history[] = ['role' => 'user', 'content' => $user_query];
    
    // Call Gemini API
    try {
        $ai_response = callGeminiAPI($user_query, $gemini_api_key, $chat_history);
        
        // If response indicates an error, use fallback
        if (strpos($ai_response, "I'm sorry") === 0) {
            $ai_response = getFallbackResponse($user_query);
        }
        
        // Add response to chat history
        $chat_history[] = ['role' => 'assistant', 'content' => $ai_response];
        
        // Store limited history (last 10 exchanges)
        if (count($chat_history) > 20) {
            $chat_history = array_slice($chat_history, -20);
        }
        
        // Save to session
        $_SESSION['gemini_chat_history'] = $chat_history;
    } catch (Exception $e) {
        // Use fallback if exception occurs
        $ai_response = getFallbackResponse($user_query);
        $chat_history[] = ['role' => 'assistant', 'content' => $ai_response];
        $_SESSION['gemini_chat_history'] = $chat_history;
    }
}

// Sample quick action suggestions
$quick_actions = [
    ['text' => 'Medication Reminders', 'icon' => 'pills', 'query' => 'Show my medication reminders'],
    ['text' => 'Health Tips', 'icon' => 'heart', 'query' => 'Give me health tips for seniors'],
    ['text' => 'Emergency Contacts', 'icon' => 'phone', 'query' => 'Show my emergency contacts'],
    ['text' => 'Today\'s Weather', 'icon' => 'cloud-sun', 'query' => 'What\'s the weather today?'],
    ['text' => 'Local News', 'icon' => 'newspaper', 'query' => 'Show me local news headlines'],
    ['text' => 'Family Photos', 'icon' => 'images', 'query' => 'Show my family photos']
];

// Function to call Gemini API
function callGeminiAPI($query, $api_key, $history = []) {
    // Get user information for context
    global $user_first_name, $user_id;
    
    // Use the exact model from your working curl example
    $url = "https://generativelanguage.googleapis.com/v1beta/models/gemini-2.0-flash:generateContent?key=" . $api_key;
    
    // Add context to make responses personalized and location-aware
    $enhancedQuery = "You are Shohochor Assistant, a helpful AI assistant for elderly users named $user_first_name in Dhaka, Bangladesh. 
    IMPORTANT: Always assume the user is in Dhaka unless they explicitly mention another location.
    IMPORTANT: Never show markdown formatting in your responses. Don't use bullet points with asterisks.
    IMPORTANT: Provide simple, clear, conversational responses as if you're speaking to an elderly person.
    
    Please respond to this question: " . $query;
    
    // Match the exact format from your working curl example
    $data = [
        "contents" => [
            [
                "parts" => [
                    ["text" => $enhancedQuery]
                ]
            ]
        ]
    ];
    
    try {
        // Use cURL for better error handling
        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
        curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
        curl_setopt($ch, CURLOPT_TIMEOUT, 30);
        
        // Execute request
        $response = curl_exec($ch);
        $error = curl_error($ch);
        $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);
        
        // Error handling
        if ($error || $http_code != 200) {
            return getFallbackResponse($query); // Use context-aware fallback
        }
        
        // Process response
        $result = json_decode($response, true);
        
        // Extract text from candidates
        if (isset($result['candidates'][0]['content']['parts'][0]['text'])) {
            return $result['candidates'][0]['content']['parts'][0]['text'];
        } else {
            return getFallbackResponse($query);
        }
    } catch (Exception $e) {
        // Log the error for debugging
        error_log("API Error: " . $e->getMessage());
        return getFallbackResponse($query);
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>AI Assistant - Shohochor</title>
    
    <!-- Google Translate -->
    <script type="text/javascript" src="https://translate.google.com/translate_a/element.js?cb=googleTranslateElementInit"></script>
    <script type="text/javascript">
        function googleTranslateElementInit() {
            // Initialize desktop translate
            new google.translate.TranslateElement({
                pageLanguage: 'en',
                includedLanguages: 'bn,en',
                layout: google.translate.TranslateElement.InlineLayout.SIMPLE
            }, 'google_translate_element');
            
            // Initialize mobile translate separately
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
    <link rel="stylesheet" href="../includes/style.css">
    
    <style>
        /* AI Assistant specific styles */
        .assistant-container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 20px;
            font-family: 'Open Sans', sans-serif;
        }
        
        .assistant-header {
            text-align: center;
            margin-bottom: 30px;
        }
        
        .assistant-header h1 {
            font-family: 'Montserrat', sans-serif;
            font-size: 32px;
            color: #0096ad;
            margin-bottom: 10px;
        }
        
        .assistant-header p {
            font-size: 18px;
            color: #4a5568;
            max-width: 800px;
            margin: 0 auto;
        }
        
        /* Chat Interface */
        .chat-interface {
            background: white;
            border-radius: 20px;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1);
            overflow: hidden;
            display: flex;
            flex-direction: column;
            height: 600px;
            margin-bottom: 30px;
        }
        
        .chat-header {
            background: linear-gradient(135deg, #0096ad 0%, #00b0cc 100%);
            color: white;
            padding: 20px;
            display: flex;
            align-items: center;
        }
        
        .chat-avatar {
            width: 50px;
            height: 50px;
            border-radius: 50%;
            background: rgba(255, 255, 255, 0.2);
            display: flex;
            align-items: center;
            justify-content: center;
            margin-right: 15px;
        }
        
        .chat-avatar i {
            font-size: 25px;
        }
        
        .chat-header-text h2 {
            margin: 0;
            font-size: 22px;
            font-weight: 600;
        }
        
        .chat-header-text p {
            margin: 5px 0 0;
            font-size: 14px;
            opacity: 0.9;
        }
        
        .chat-messages {
            flex: 1;
            padding: 20px;
            overflow-y: auto;
            background: #f8fafc;
        }
        
        .message {
            max-width: 80%;
            margin-bottom: 20px;
            clear: both;
        }
        
        .message-user {
            float: right;
        }
        
        .message-ai {
            float: left;
        }
        
        .message-content {
            padding: 15px;
            border-radius: 18px;
            position: relative;
            box-shadow: 0 1px 5px rgba(0, 0, 0, 0.05);
        }
        
        .message-user .message-content {
            background: #0096ad;
            color: white;
            border-bottom-right-radius: 5px;
        }
        
        .message-ai .message-content {
            background: white;
            color: #333;
            border-bottom-left-radius: 5px;
            border-left: 4px solid #0096ad;
        }
        
        .message-info {
            font-size: 12px;
            margin-top: 5px;
            color: #718096;
        }
        
        .message-user .message-info {
            text-align: right;
        }
        
        .message-typing {
            display: flex;
            align-items: center;
            padding: 10px;
            background: white;
            border-radius: 18px;
            box-shadow: 0 1px 5px rgba(0, 0, 0, 0.05);
            width: fit-content;
            margin-bottom: 20px;
        }
        
        .typing-indicator {
            display: flex;
            align-items: center;
        }
        
        .typing-dot {
            width: 8px;
            height: 8px;
            background: #0096ad;
            border-radius: 50%;
            margin: 0 2px;
            opacity: 0.6;
            animation: typingAnimation 1.5s infinite ease-in-out;
        }
        
        .typing-dot:nth-child(2) {
            animation-delay: 0.1s;
        }
        
        .typing-dot:nth-child(3) {
            animation-delay: 0.2s;
        }
        
        @keyframes typingAnimation {
            0%, 100% {
                transform: translateY(0);
            }
            50% {
                transform: translateY(-5px);
            }
        }
        
        .chat-input-container {
            padding: 15px;
            background: white;
            border-top: 1px solid #edf2f7;
        }
        
        .chat-input-form {
            display: flex;
            align-items: center;
        }
        
        .chat-input {
            flex: 1;
            border: 1px solid #e2e8f0;
            border-radius: 30px;
            padding: 12px 20px;
            font-size: 16px;
            font-family: 'Open Sans', sans-serif;
            transition: border-color 0.3s;
        }
        
        .chat-input:focus {
            outline: none;
            border-color: #0096ad;
            box-shadow: 0 0 0 3px rgba(0, 150, 173, 0.1);
        }
        
        .chat-button {
            width: 46px;
            height: 46px;
            border-radius: 50%;
            background: #0096ad;
            color: white;
            border: none;
            margin-left: 10px;
            display: flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            transition: all 0.3s ease;
        }
        
        .chat-button:hover {
            background: #007a8c;
            transform: translateY(-2px);
        }
        
        .chat-button i {
            font-size: 18px;
        }
        
        /* Quick Actions */
        .quick-actions {
            margin-bottom: 40px;
        }
        
        .section-title {
            font-family: 'Montserrat', sans-serif;
            font-size: 22px;
            font-weight: 600;
            color: #2d3748;
            margin-bottom: 20px;
            display: flex;
            align-items: center;
        }
        
        .section-title i {
            margin-right: 10px;
            color: #0096ad;
        }
        
        .action-buttons {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(180px, 1fr));
            gap: 15px;
        }
        
        .action-button {
            background: white;
            border: 1px solid #e2e8f0;
            border-radius: 12px;
            padding: 15px;
            display: flex;
            align-items: center;
            cursor: pointer;
            transition: all 0.3s ease;
        }
        
        .action-button:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 15px rgba(0, 0, 0, 0.05);
            border-color: #0096ad;
        }
        
        .action-icon {
            width: 40px;
            height: 40px;
            background: rgba(0, 150, 173, 0.1);
            border-radius: 10px;
            display: flex;
            align-items: center;
            justify-content: center;
            margin-right: 15px;
            color: #0096ad;
        }
        
        .action-text {
            font-weight: 500;
            color: #4a5568;
        }
        
        /* Help & Instructions */
        .help-section {
            background: #f8fafc;
            border-radius: 12px;
            padding: 25px;
            margin-bottom: 30px;
        }
        
        .help-items {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
            gap: 20px;
            margin-top: 15px;
        }
        
        .help-item {
            background: white;
            border-radius: 8px;
            padding: 15px;
            box-shadow: 0 2px 5px rgba(0, 0, 0, 0.05);
        }
        
        .help-item h3 {
            font-size: 18px;
            font-weight: 600;
            color: #2d3748;
            margin-top: 0;
            margin-bottom: 10px;
            display: flex;
            align-items: center;
        }
        
        .help-item h3 i {
            color: #0096ad;
            margin-right: 8px;
        }
        
        .help-item p {
            margin: 0;
            color: #4a5568;
            font-size: 14px;
            line-height: 1.5;
        }
        
        /* Emergency Section */
        .emergency-section {
            background: #fff5f5;
            border-radius: 12px;
            padding: 20px;
            border-left: 4px solid #e53e3e;
            margin-bottom: 30px;
        }
        
        .emergency-header {
            display: flex;
            align-items: center;
            margin-bottom: 15px;
        }
        
        .emergency-header i {
            color: #e53e3e;
            font-size: 24px;
            margin-right: 10px;
        }
        
        .emergency-header h3 {
            color: #e53e3e;
            font-size: 20px;
            margin: 0;
        }
        
        .emergency-buttons {
            display: flex;
            flex-wrap: wrap;
            gap: 15px;
        }
        
        .emergency-button {
            background: white;
            border: 1px solid #e53e3e;
            color: #e53e3e;
            border-radius: 8px;
            padding: 12px 20px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
        }
        
        .emergency-button.primary {
            background: #e53e3e;
            color: white;
        }
        
        .emergency-button:hover {
            transform: translateY(-3px);
            box-shadow: 0 5px 15px rgba(229, 62, 62, 0.2);
        }
        
        .emergency-button i {
            margin-right: 8px;
        }
        
        /* Voice Input Feedback */
        .voice-feedback {
            position: fixed;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            background: white;
            border-radius: 20px;
            padding: 30px;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.2);
            text-align: center;
            z-index: 1000;
            display: none;
        }
        
        .voice-feedback h3 {
            color: #0096ad;
            margin-top: 0;
            margin-bottom: 20px;
        }
        
        .voice-animation {
            margin: 20px 0;
            display: flex;
            justify-content: center;
        }
        
        .voice-wave {
            display: inline-block;
            width: 5px;
            height: 30px;
            margin: 0 3px;
            background: #0096ad;
            border-radius: 3px;
            animation: voiceWave 1s infinite ease-in-out;
        }
        
        .voice-wave:nth-child(2) {
            animation-delay: 0.1s;
        }
        
        .voice-wave:nth-child(3) {
            animation-delay: 0.2s;
        }
        
        .voice-wave:nth-child(4) {
            animation-delay: 0.3s;
        }
        
        .voice-wave:nth-child(5) {
            animation-delay: 0.4s;
        }
        
        @keyframes voiceWave {
            0%, 100% {
                height: 10px;
            }
            50% {
                height: 30px;
            }
        }
        
        .voice-feedback-text {
            margin-bottom: 20px;
            min-height: 40px;
        }
        
        .cancel-button {
            background: #e2e8f0;
            border: none;
            border-radius: 8px;
            padding: 10px 20px;
            font-weight: 500;
            cursor: pointer;
            transition: all 0.3s;
        }
        
        .cancel-button:hover {
            background: #cbd5e0;
        }
        
        /* Responsive styles */
        @media (max-width: 768px) {
            .assistant-container {
                padding: 15px;
            }
            
            .chat-interface {
                height: 500px;
            }
            
            .message {
                max-width: 90%;
            }
            
            .action-buttons {
                grid-template-columns: 1fr 1fr;
            }
            
            .help-items {
                grid-template-columns: 1fr;
            }
            
            .emergency-buttons {
                flex-direction: column;
            }
            
            .emergency-button {
                width: 100%;
            }
        }
        
        /* Accessibility Features */
        .accessibility-controls {
            display: flex;
            justify-content: flex-end;
            margin-bottom: 20px;
            gap: 10px;
        }
        
        .accessibility-button {
            background: white;
            border: 1px solid #e2e8f0;
            border-radius: 8px;
            padding: 8px 15px;
            font-size: 14px;
            cursor: pointer;
            display: flex;
            align-items: center;
            transition: all 0.3s;
        }
        
        .accessibility-button i {
            margin-right: 8px;
        }
        
        .accessibility-button:hover {
            background: #f7fafc;
            border-color: #0096ad;
        }
        
        .accessibility-button.active {
            background: #0096ad;
            color: white;
            border-color: #0096ad;
        }
        
        /* High Contrast Mode */
        body.high-contrast {
            background: #000;
            color: #fff;
        }
        
        body.high-contrast .chat-interface,
        body.high-contrast .action-button,
        body.high-contrast .help-item {
            background: #222;
            border-color: #444;
        }
        
        body.high-contrast .chat-messages {
            background: #111;
        }
        
        body.high-contrast .message-ai .message-content {
            background: #333;
            color: white;
        }
        
        body.high-contrast .chat-input {
            background: #333;
            color: white;
            border-color: #555;
        }
        
        body.high-contrast .section-title,
        body.high-contrast .action-text,
        body.high-contrast .help-item h3,
        body.high-contrast .help-item p {
            color: #fff;
        }
    </style>
</head>
<body>
    <!-- Include header -->
    <?php include_once "../includes/nav.php"; ?>
    
    <!-- Main content -->
    <main>
        <div class="assistant-container">
            <div class="assistant-header">
                <h1>AI Assistant</h1>
                <p>Your personal AI helper powered by Google Gemini. Ask questions, get help with daily tasks, or find information - all in simple language.</p>
            </div>
            
            <div class="accessibility-controls">
                <button class="accessibility-button" id="highContrastBtn">
                    <i class="fas fa-adjust"></i> High Contrast
                </button>
                <button class="accessibility-button" id="largeTextBtn">
                    <i class="fas fa-text-height"></i> Large Text
                </button>
                <button class="accessibility-button" id="readAloudBtn">
                    <i class="fas fa-volume-up"></i> Read Aloud
                </button>
            </div>
            
            <!-- Chat Interface -->
            <div class="chat-interface">
                <div class="chat-header">
                    <div class="chat-avatar">
                        <i class="fas fa-robot"></i>
                    </div>
                    <div class="chat-header-text">
                        <h2>Shohochor Assistant</h2>
                        <p>Powered by Google Gemini</p>
                    </div>
                </div>
                
                <div class="chat-messages" id="chatMessages">
                    <!-- Initial greeting message -->
                    <div class="message message-ai">
                        <div class="message-content">
                            <p>Hello, <?php echo htmlspecialchars($user_first_name); ?>! I'm your Shohochor Assistant. How can I help you today?</p>
                        </div>
                        <div class="message-info">
                            <span>Shohochor Assistant • <?php echo date('h:i A'); ?></span>
                        </div>
                    </div>
                    
                    <!-- Display chat history -->
                    <?php 
                    if (!empty($chat_history)): 
                        for ($i = 0; $i < count($chat_history); $i += 2):
                            if (isset($chat_history[$i]) && isset($chat_history[$i + 1])):
                                $user_message = $chat_history[$i];
                                $ai_message = $chat_history[$i + 1];
                    ?>
                    <div class="message message-user">
                        <div class="message-content">
                            <p><?php echo htmlspecialchars($user_message['content']); ?></p>
                        </div>
                        <div class="message-info">
                            <span>You • <?php echo date('h:i A'); ?></span>
                        </div>
                    </div>
                    <div class="message message-ai">
                        <div class="message-content">
                            <p><?php echo nl2br(htmlspecialchars_decode(str_replace(['*', '•'], '', $ai_message['content']))); ?></p>
                        </div>
                        <div class="message-info">
                            <span>Shohochor Assistant • <?php echo date('h:i A'); ?></span>
                        </div>
                    </div>
                    <?php 
                            endif;
                        endfor;
                    endif; 
                    ?>
                </div>
                
                <div class="chat-input-container">
                    <form class="chat-input-form" id="chatForm" method="post">
                        <input type="text" class="chat-input" id="chatInput" name="query" placeholder="Type your question here..." autocomplete="off" required>
                        <button type="button" class="chat-button" id="voiceButton" title="Voice Input">
                            <i class="fas fa-microphone"></i>
                        </button>
                        <button type="submit" class="chat-button" title="Send Message">
                            <i class="fas fa-paper-plane"></i>
                        </button>
                    </form>
                </div>
            </div>
            
            <!-- Quick Actions -->
            <div class="quick-actions">
                <h2 class="section-title">
                    <i class="fas fa-bolt"></i>
                    Quick Actions
                </h2>
                <div class="action-buttons">
                    <?php foreach ($quick_actions as $action): ?>
                    <div class="action-button" onclick="askQuery('<?php echo htmlspecialchars($action['query']); ?>')">
                        <div class="action-icon">
                            <i class="fas fa-<?php echo htmlspecialchars($action['icon']); ?>"></i>
                        </div>
                        <div class="action-text"><?php echo htmlspecialchars($action['text']); ?></div>
                    </div>
                    <?php endforeach; ?>
                </div>
            </div>
            
            <!-- Emergency Section -->
            <div class="emergency-section">
                <div class="emergency-header">
                    <i class="fas fa-exclamation-triangle"></i>
                    <h3>Emergency Assistance</h3>
                </div>
                <div class="emergency-buttons">
                    <button class="emergency-button primary" onclick="handleEmergency('call')">
                        <i class="fas fa-phone-alt"></i> Call Emergency Services
                    </button>
                    <button class="emergency-button" onclick="askQuery('Contact my primary caregiver immediately')">
                        <i class="fas fa-user-nurse"></i> Alert Caregiver
                    </button>
                    <button class="emergency-button" onclick="handleEmergency('contacts')">
                        <i class="fas fa-address-book"></i> Show Emergency Contacts
                    </button>
                </div>
            </div>
            
            <!-- Help Section -->
            <div class="help-section">
                <h2 class="section-title">
                    <i class="fas fa-question-circle"></i>
                    How to Use the AI Assistant
                </h2>
                <div class="help-items">
                    <div class="help-item">
                        <h3><i class="fas fa-comment-alt"></i> Ask Questions</h3>
                        <p>Type your question or request in the chat box and press send. You can ask about health, daily tasks, local information, or general questions.</p>
                    </div>
                    <div class="help-item">
                        <h3><i class="fas fa-microphone"></i> Voice Commands</h3>
                        <p>Click the microphone button and speak clearly. When finished, the assistant will process your request and respond.</p>
                    </div>
                    <div class="help-item">
                        <h3><i class="fas fa-lightbulb"></i> Example Questions</h3>
                        <p>"What time is my doctor appointment?", "How do I take my medicine?", "What's the weather today?", "Connect me with my family"</p>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Voice Input Feedback Modal -->
        <div class="voice-feedback" id="voiceFeedback">
            <h3>Listening...</h3>
            <div class="voice-animation">
                <div class="voice-wave"></div>
                <div class="voice-wave"></div>
                <div class="voice-wave"></div>
                <div class="voice-wave"></div>
                <div class="voice-wave"></div>
            </div>
            <div class="voice-feedback-text" id="voiceFeedbackText">
                Speak now...
            </div>
            <button class="cancel-button" id="cancelVoiceBtn">Cancel</button>
        </div>
    </main>
    
    <!-- Include footer -->
    <?php include_once "../includes/footer.php"; ?>
    
    <!-- Include mobile navigation -->
    <?php include_once "../includes/mobile_nav.php"; ?>
    
    <!-- JavaScript for the interface -->
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const chatMessages = document.getElementById('chatMessages');
            const chatForm = document.getElementById('chatForm');
            const chatInput = document.getElementById('chatInput');
            const voiceButton = document.getElementById('voiceButton');
            const voiceFeedback = document.getElementById('voiceFeedback');
            const voiceFeedbackText = document.getElementById('voiceFeedbackText');
            const cancelVoiceBtn = document.getElementById('cancelVoiceBtn');
            const highContrastBtn = document.getElementById('highContrastBtn');
            const largeTextBtn = document.getElementById('largeTextBtn');
            const readAloudBtn = document.getElementById('readAloudBtn');
            
            // Accessibility Features
            function toggleHighContrast() {
                document.body.classList.toggle('high-contrast');
                highContrastBtn.classList.toggle('active');
                localStorage.setItem('highContrast', document.body.classList.contains('high-contrast'));
            }
            
            function toggleLargeText() {
                document.body.classList.toggle('large-text');
                largeTextBtn.classList.toggle('active');
                localStorage.setItem('largeText', document.body.classList.contains('large-text'));
            }
            
            // Load user preferences
            if (localStorage.getItem('highContrast') === 'true') {
                document.body.classList.add('high-contrast');
                highContrastBtn.classList.add('active');
            }
            
            if (localStorage.getItem('largeText') === 'true') {
                document.body.classList.add('large-text');
                largeTextBtn.classList.add('active');
            }
            
            highContrastBtn.addEventListener('click', toggleHighContrast);
            largeTextBtn.addEventListener('click', toggleLargeText);
            
            // Text-to-speech function
            let speechSynthesis = window.speechSynthesis;
            let isSpeaking = false;
            
            function readText(text) {
                if (speechSynthesis) {
                    // Stop any current speech
                    speechSynthesis.cancel();
                    
                    const utterance = new SpeechSynthesisUtterance(text);
                    utterance.rate = 0.9; // Slightly slower for elderly users
                    utterance.pitch = 1;
                    
                    // Use a female voice if available
                    const voices = speechSynthesis.getVoices();
                    const femaleVoice = voices.find(voice => voice.name.includes('Female') || voice.name.includes('female'));
                    if (femaleVoice) {
                        utterance.voice = femaleVoice;
                    }
                    
                    isSpeaking = true;
                    readAloudBtn.innerHTML = '<i class="fas fa-stop"></i> Stop Reading';
                    readAloudBtn.classList.add('active');
                    
                    utterance.onend = function() {
                        isSpeaking = false;
                        readAloudBtn.innerHTML = '<i class="fas fa-volume-up"></i> Read Aloud';
                        readAloudBtn.classList.remove('active');
                    };
                    
                    speechSynthesis.speak(utterance);
                }
            }
            
            readAloudBtn.addEventListener('click', function() {
                if (isSpeaking) {
                    speechSynthesis.cancel();
                    isSpeaking = false;
                    readAloudBtn.innerHTML = '<i class="fas fa-volume-up"></i> Read Aloud';
                    readAloudBtn.classList.remove('active');
                } else {
                    // Get the last AI message
                    const aiMessages = document.querySelectorAll('.message-ai .message-content p');
                    if (aiMessages.length > 0) {
                        const lastMessage = aiMessages[aiMessages.length - 1].textContent;
                        readText(lastMessage);
                    }
                }
            });
            
            // Scroll chat to bottom
            function scrollToBottom() {
                chatMessages.scrollTop = chatMessages.scrollHeight;
            }
            
            // Show typing indicator
            function showTypingIndicator() {
                const typingDiv = document.createElement('div');
                typingDiv.className = 'message message-ai';
                typingDiv.id = 'typingIndicator';
                        <div class="typing-indicator">
                            <div class="typing-dot"></div>
                            <div class="typing-dot"></div>
                            <div class="typing-dot"></div>
                        </div>
                    </div>
                `;
                chatMessages.appendChild(typingDiv);
                scrollToBottom();
            }
            
            // Remove typing indicator
            function removeTypingIndicator() {
                const indicator = document.getElementById('typingIndicator');
                if (indicator) {
                    chatMessages.removeChild(indicator);
                }
            }
            
            // Add user message to chat
            function addUserMessage(message) {
                const messageDiv = document.createElement('div');
                messageDiv.className = 'message message-user';
                messageDiv.innerHTML = `
                    <div class="message-content">
                        <p>${message}</p>
                    </div>
                    <div class="message-info">
                        <span>You • ${new Date().toLocaleTimeString([], {hour: '2-digit', minute:'2-digit'})}</span>
                    </div>
                `;
                chatMessages.appendChild(messageDiv);
                scrollToBottom();
            }
            
            // Add AI response to chat
            function addAIResponse(message) {
                const messageDiv = document.createElement('div');
                messageDiv.className = 'message message-ai';
                messageDiv.innerHTML = `
                    <div class="message-content">
                        <p>${message.replace(/\n/g, '<br>')}</p>
                    </div>
                    <div class="message-info">
                        <span>Shohochor Assistant • ${new Date().toLocaleTimeString([], {hour: '2-digit', minute:'2-digit'})}</span>
                    </div>
                `;
                chatMessages.appendChild(messageDiv);
                scrollToBottom();
            }
            
            // Handle form submission
            chatForm.addEventListener('submit', function(e) {
                e.preventDefault();
                
                const query = chatInput.value.trim();
                if (!query) return;
                
                // Add user message to chat
                addUserMessage(query);
                chatInput.value = '';
                
                // Show typing indicator
                showTypingIndicator();
                
                // Submit the form via AJAX
                const formData = new FormData(chatForm);
                
                fetch(window.location.href, {
                    method: 'POST',
                    body: formData
                })
                .then(response => response.text())
                .then(html => {
                    // Extract the AI response from the HTML
                    const parser = new DOMParser();
                    const doc = parser.parseFromString(html, 'text/html');
                    const messages = doc.querySelectorAll('.message-ai .message-content p');
                    if (messages.length > 0) {
                        const response = messages[messages.length - 1].innerHTML;
                        
                        // Remove typing indicator
                        removeTypingIndicator();
                        
                        // Add AI response
                        addAIResponse(response);
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    removeTypingIndicator();
                    addAIResponse("I'm sorry, I encountered an error. Please try again.");
                });
            });
            
            // Voice input functionality
            function startVoiceRecognition() {
                // Check if browser supports speech recognition
                if ('SpeechRecognition' in window || 'webkitSpeechRecognition' in window) {
                    const SpeechRecognition = window.SpeechRecognition || window.webkitSpeechRecognition;
                    const recognition = new SpeechRecognition();
                    
                    recognition.lang = 'en-US';
                    recognition.interimResults = true;
                    recognition.continuous = false;
                    
                    // Show voice feedback
                    voiceFeedback.style.display = 'block';
                    
                    recognition.onstart = function() {
                        voiceFeedbackText.textContent = 'Listening...';
                    };
                    
                    recognition.onresult = function(event) {
                        const transcript = Array.from(event.results)
                            .map(result => result[0])
                            .map(result => result.transcript)
                            .join('');
                        
                        voiceFeedbackText.textContent = transcript;
                    };
                    
                    recognition.onerror = function(event) {
                        console.error('Speech recognition error', event.error);
                        voiceFeedbackText.textContent = 'Error: ' + event.error;
                        setTimeout(() => {
                            voiceFeedback.style.display = 'none';
                        }, 1500);
                    };
                    
                    recognition.onend = function() {
                        if (voiceFeedbackText.textContent !== 'Listening...' && 
                            !voiceFeedbackText.textContent.includes('Error')) {
                            // Get the transcript and submit
                            const transcript = voiceFeedbackText.textContent;
                            chatInput.value = transcript;
                            voiceFeedback.style.display = 'none';
                            
                            // Submit the form
                            chatForm.dispatchEvent(new Event('submit'));
                        } else {
                            voiceFeedback.style.display = 'none';
                        }
                    };
                    
                    // Start recognition
                    recognition.start();
                    
                    // Cancel button
                    cancelVoiceBtn.onclick = function() {
                        recognition.abort();
                        voiceFeedback.style.display = 'none';
                    };
                } else {
                    alert('Speech recognition is not supported in your browser. Please use Chrome, Edge, or Safari.');
                }
            }
            
            voiceButton.addEventListener('click', startVoiceRecognition);
            
            // Function to programmatically ask a query
            window.askQuery = function(query) {
                chatInput.value = query;
                chatForm.dispatchEvent(new Event('submit'));
            };
            
            // Handle emergency buttons
            window.handleEmergency = function(action) {
                if (action === 'call') {
                    if (confirm('Do you need emergency assistance? This will call emergency services.')) {
                        window.location.href = 'tel:999'; // Emergency number for Bangladesh
                    }
                } else if (action === 'contacts') {
                    window.location.href = '../emergency_contact/emergency-contacts.php';
                }
            };
            
            // Initialize
            scrollToBottom();
            
            if (isHelp) {
                // Simulate emergency help trigger
                setTimeout(() => {
                    window.location.href = 'tel:999';  // Emergency number for Bangladesh
                }, 2000);
            } else {
                // Simulate emergency contact call
                setTimeout(() => {
                    window.location.href = 'tel:' + getEmergencyContactNumber();
                }, 2000);
            }
        });
        
        // Function to show toast notifications
        function showToast(message, type) {
            const toast = document.createElement('div');
            toast.className = 'toast show ' + type;
            toast.innerHTML = `<i class="fas fa-info-circle"></i> ${message}`;
            document.body.appendChild(toast);
            
            setTimeout(() => {
                toast.classList.remove('show');
                setTimeout(() => {
                    document.body.removeChild(toast);
                }, 300);
            }, 3000);
        }
        
        // Function to get emergency contact number (simulate fetching from user data)
        function getEmergencyContactNumber() {
            return '999';  // Default to emergency services number
        }
    </script>
</body>
</html>