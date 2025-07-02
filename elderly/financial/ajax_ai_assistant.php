<?php
// filepath: c:\xampp\htdocs\Astha\elderly\financial\ajax_ai_assistant.php
session_start();

// Include required files
require_once '../../login_user/functions.php';
require_once '../../signup_user/config.php';

// Check authentication
if (!isLoggedIn()) {
    http_response_code(401);
    echo json_encode(['error' => 'Unauthorized access']);
    exit();
}

// Get user ID
$user_id = $_SESSION['user_id'] ?? 0;

// Process request
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $data = json_decode(file_get_contents('php://input'), true);
    
    if (!isset($data['question']) || empty($data['question'])) {
        http_response_code(400);
        echo json_encode(['error' => 'Missing question']);
        exit();
    }
    
    $question = $data['question'];
    $include_calculations = isset($data['include_calculations']) ? (bool)$data['include_calculations'] : false;
    
    // Fetch user's saved calculations if requested
    $calculations_data = [];
    if ($include_calculations) {
        $stmt = $conn->prepare("SELECT * FROM financial_calculations WHERE user_id = ? ORDER BY created_at DESC LIMIT 5");
        $stmt->bind_param("i", $user_id);
        $stmt->execute();
        $result = $stmt->get_result();
        
        while($row = $result->fetch_assoc()) {
            $calculations_data[] = $row;
        }
    }
    
    // Gemini AI API request
    $gemini_api_key = getenv('GEMINI_API_KEY') ?: 'AIzaSyCw0tSxf2cvHp4hP308ZxahGCs05iI45jg';
    $url = "https://generativelanguage.googleapis.com/v1beta/models/gemini-2.0-flash:generateContent?key=" . $gemini_api_key;
    
    // Prepare context for the AI
    $system_prompt = "You are a helpful and knowledgeable financial assistant for elderly people. ";
    $system_prompt .= "Provide clear, accurate, and easy to understand financial advice. ";
    $system_prompt .= "Be respectful and patient, and avoid overly technical jargon unless explaining it. ";
    $system_prompt .= "Focus on advice relevant to seniors and retirees.";
    
    // Add saved calculations to the prompt if available
    if (!empty($calculations_data)) {
        $system_prompt .= "\n\nThe user has the following saved financial calculations:";
        foreach ($calculations_data as $index => $calc) {
            $system_prompt .= "\n\nCalculation " . ($index + 1) . ":";
            $system_prompt .= "\nType: " . $calc['calculation_type'];
            $system_prompt .= "\nName: " . ($calc['calculation_name'] ?? 'Unnamed');
            $system_prompt .= "\nCreated: " . $calc['created_at'];
            $system_prompt .= "\nResults: " . $calc['result_data'];
            $system_prompt .= "\nInputs: " . $calc['input_data'];
        }
        $system_prompt .= "\n\nYou can reference these calculations when answering the user's question.";
    }
    
    // Build request payload
    $payload = [
        "contents" => [
            [
                "role" => "user",
                "parts" => [
                    ["text" => $system_prompt . "\n\nUser question: " . $question]
                ]
            ]
        ],
        "generationConfig" => [
            "temperature" => 0.4,
            "topK" => 32,
            "topP" => 0.95,
            "maxOutputTokens" => 800,
        ]
    ];
    
    // Initialize cURL session
    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($payload));
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        'Content-Type: application/json'
    ]);
    
    // Execute request
    $response = curl_exec($ch);
    $status_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    
    // Handle API response
    if ($status_code == 200) {
        $api_response = json_decode($response, true);
        
        if (isset($api_response['candidates'][0]['content']['parts'][0]['text'])) {
            $ai_response = $api_response['candidates'][0]['content']['parts'][0]['text'];
            
            // Log the conversation
            $stmt = $conn->prepare("INSERT INTO ai_conversations (user_id, question, answer, created_at) VALUES (?, ?, ?, NOW())");
            $stmt->bind_param("iss", $user_id, $question, $ai_response);
            $stmt->execute();
            
            echo json_encode([
                'status' => 'success',
                'response' => $ai_response
            ]);
        } else {
            echo json_encode([
                'status' => 'error',
                'message' => 'Could not parse AI response'
            ]);
        }
    } else {
        echo json_encode([
            'status' => 'error',
            'message' => 'Failed to get response from AI service',
            'debug' => $response
        ]);
    }
} else {
    http_response_code(405);
    echo json_encode(['error' => 'Method not allowed']);
}
?>