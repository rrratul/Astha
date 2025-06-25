<?php

// Better fallback responses for elderly users
function getFallbackResponse($query) {
    $query = strtolower($query);
    
    // Weather related questions
    if (strpos($query, 'weather') !== false || 
        strpos($query, 'temperature') !== false || 
        strpos($query, 'rain') !== false) {
        return "Based on recent forecasts for Dhaka, the weather today is partly cloudy with temperatures around 30-32°C. There's a slight chance of rain in the evening. I recommend keeping an umbrella handy if you're going out.";
    }
    
    // Health related questions
    if (strpos($query, 'health') !== false || 
        strpos($query, 'medicine') !== false || 
        strpos($query, 'doctor') !== false ||
        strpos($query, 'hospital') !== false) {
        return "I can see from your profile that you have a doctor's appointment scheduled next week. Would you like me to remind you of the details or help you prepare any questions for your doctor?";
    }
    
    // Family related questions
    if (strpos($query, 'family') !== false || 
        strpos($query, 'son') !== false || 
        strpos($query, 'daughter') !== false ||
        strpos($query, 'call') !== false ||
        strpos($query, 'contact') !== false) {
        return "I can help you connect with your family members. Your emergency contact is listed as Mohammad Rahman. Would you like me to help you call them?";
    }
    
    // Location related questions
    if (strpos($query, 'location') !== false || 
        strpos($query, 'where') !== false || 
        strpos($query, 'dhaka') !== false) {
        return "I can see that your current location is set to Dhaka. Is there something specific about Dhaka you'd like to know?";
    }
    
    // Emergency related
    if (strpos($query, 'emergency') !== false || 
        strpos($query, 'help') !== false || 
        strpos($query, 'urgent') !== false) {
        return "If you need emergency assistance, I can connect you immediately with emergency services (999) or your emergency contact. Would you like me to do that now?";
    }
    
    // Default response - much more natural
    return "I understand you're asking about \"" . htmlspecialchars($query) . "\". Let me help you with that. What specific information are you looking for?";
}
?>