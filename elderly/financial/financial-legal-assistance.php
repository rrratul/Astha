<?php
session_start();

// Include required files
require_once '../../login_user/functions.php';
require_once '../../signup_user/config.php';
require_once 'financial_calculators.php'; // New file for calculator functions

// Simple authentication check
if (!isLoggedIn()) {
    header("Location: ../../login_user/login_user.php");
    exit();
}

// Check role
if (isset($_SESSION['user_role'])) {
    $allowed_roles = ['senior', 'elderly']; 
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
$user_id = $_SESSION['user_id'] ?? 0;
$user_first_name = $_SESSION['user_first_name'] ?? 'User';

// Process any calculation form submissions
$calculator_results = array();
$calculator_errors = array();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['calculate_retirement'])) {
        // Retirement calculator
        $current_age = filter_input(INPUT_POST, 'current_age', FILTER_VALIDATE_INT);
        $retirement_age = filter_input(INPUT_POST, 'retirement_age', FILTER_VALIDATE_INT);
        $monthly_income = filter_input(INPUT_POST, 'monthly_income', FILTER_VALIDATE_FLOAT);
        $monthly_expenses = filter_input(INPUT_POST, 'monthly_expenses', FILTER_VALIDATE_FLOAT);
        $current_savings = filter_input(INPUT_POST, 'current_savings', FILTER_VALIDATE_FLOAT);
        $expected_return = filter_input(INPUT_POST, 'expected_return', FILTER_VALIDATE_FLOAT);
        $inflation_rate = filter_input(INPUT_POST, 'inflation_rate', FILTER_VALIDATE_FLOAT);
        
        if (!$current_age || !$retirement_age || !$monthly_income || !$monthly_expenses || 
            !$current_savings || !$expected_return || !$inflation_rate) {
            $calculator_errors['retirement'] = "Please fill in all fields with valid numbers.";
        } else {
            // Calculate using functions from financial_calculators.php
            $calculator_results['retirement'] = calculateRetirementNeeds(
                $current_age, $retirement_age, $monthly_income, 
                $monthly_expenses, $current_savings, $expected_return, $inflation_rate
            );
            
            // Save calculation to database
            saveCalculationToDatabase(
                $conn,
                $user_id,
                'retirement',
                json_encode($calculator_results['retirement']),
                json_encode([
                    'current_age' => $current_age,
                    'retirement_age' => $retirement_age,
                    'monthly_income' => $monthly_income,
                    'monthly_expenses' => $monthly_expenses,
                    'current_savings' => $current_savings,
                    'expected_return' => $expected_return,
                    'inflation_rate' => $inflation_rate
                ])
            );
        }
    } elseif (isset($_POST['calculate_social_security'])) {
        // Social Security Benefits calculator
        $birth_year = filter_input(INPUT_POST, 'birth_year', FILTER_VALIDATE_INT);
        $years_worked = filter_input(INPUT_POST, 'years_worked', FILTER_VALIDATE_INT);
        $avg_monthly_income = filter_input(INPUT_POST, 'avg_monthly_income', FILTER_VALIDATE_FLOAT);
        $retirement_age_ss = filter_input(INPUT_POST, 'retirement_age_ss', FILTER_VALIDATE_INT);
        
        if (!$birth_year || !$years_worked || !$avg_monthly_income || !$retirement_age_ss) {
            $calculator_errors['social_security'] = "Please fill in all fields with valid numbers.";
        } else {
            $calculator_results['social_security'] = calculateSocialSecurityBenefits(
                $birth_year, $years_worked, $avg_monthly_income, $retirement_age_ss
            );
            
            // Save calculation to database
            saveCalculationToDatabase(
                $conn,
                $user_id,
                'social_security',
                json_encode($calculator_results['social_security']),
                json_encode([
                    'birth_year' => $birth_year,
                    'years_worked' => $years_worked,
                    'avg_monthly_income' => $avg_monthly_income,
                    'retirement_age_ss' => $retirement_age_ss
                ])
            );
        }
    } elseif (isset($_POST['calculate_medical_expenses'])) {
        // Medical Expense calculator
        $monthly_medication = filter_input(INPUT_POST, 'monthly_medication', FILTER_VALIDATE_FLOAT);
        $doctor_visits = filter_input(INPUT_POST, 'doctor_visits', FILTER_VALIDATE_FLOAT);
        $hospital_costs = filter_input(INPUT_POST, 'hospital_costs', FILTER_VALIDATE_FLOAT);
        $insurance_premium = filter_input(INPUT_POST, 'insurance_premium', FILTER_VALIDATE_FLOAT);
        $other_expenses = filter_input(INPUT_POST, 'other_expenses', FILTER_VALIDATE_FLOAT);
        
        if (!$monthly_medication || !$doctor_visits || !$hospital_costs || !$insurance_premium) {
            $calculator_errors['medical'] = "Please fill in all required fields with valid numbers.";
        } else {
            $other_expenses = $other_expenses ?: 0; // Default to zero if not provided
            
            $calculator_results['medical'] = calculateMedicalExpenses(
                $monthly_medication, $doctor_visits, $hospital_costs, $insurance_premium, $other_expenses
            );
            
            // Save calculation to database
            saveCalculationToDatabase(
                $conn,
                $user_id,
                'medical',
                json_encode($calculator_results['medical']),
                json_encode([
                    'monthly_medication' => $monthly_medication,
                    'doctor_visits' => $doctor_visits,
                    'hospital_costs' => $hospital_costs,
                    'insurance_premium' => $insurance_premium,
                    'other_expenses' => $other_expenses
                ])
            );
        }
    } elseif (isset($_POST['save_monthly_budget'])) {
        // Monthly Budget Planner
        $income_pension = filter_input(INPUT_POST, 'income_pension', FILTER_VALIDATE_FLOAT) ?: 0;
        $income_social_security = filter_input(INPUT_POST, 'income_social_security', FILTER_VALIDATE_FLOAT) ?: 0;
        $income_investments = filter_input(INPUT_POST, 'income_investments', FILTER_VALIDATE_FLOAT) ?: 0;
        $income_other = filter_input(INPUT_POST, 'income_other', FILTER_VALIDATE_FLOAT) ?: 0;
        
        $expense_housing = filter_input(INPUT_POST, 'expense_housing', FILTER_VALIDATE_FLOAT) ?: 0;
        $expense_utilities = filter_input(INPUT_POST, 'expense_utilities', FILTER_VALIDATE_FLOAT) ?: 0;
        $expense_food = filter_input(INPUT_POST, 'expense_food', FILTER_VALIDATE_FLOAT) ?: 0;
        $expense_healthcare = filter_input(INPUT_POST, 'expense_healthcare', FILTER_VALIDATE_FLOAT) ?: 0;
        $expense_transportation = filter_input(INPUT_POST, 'expense_transportation', FILTER_VALIDATE_FLOAT) ?: 0;
        $expense_entertainment = filter_input(INPUT_POST, 'expense_entertainment', FILTER_VALIDATE_FLOAT) ?: 0;
        $expense_other = filter_input(INPUT_POST, 'expense_other', FILTER_VALIDATE_FLOAT) ?: 0;
        
        $budget_data = [
            'income' => [
                'pension' => $income_pension,
                'social_security' => $income_social_security,
                'investments' => $income_investments,
                'other' => $income_other
            ],
            'expenses' => [
                'housing' => $expense_housing,
                'utilities' => $expense_utilities,
                'food' => $expense_food,
                'healthcare' => $expense_healthcare,
                'transportation' => $expense_transportation,
                'entertainment' => $expense_entertainment,
                'other' => $expense_other
            ]
        ];
        
        $calculator_results['budget'] = calculateMonthlyBudget($budget_data);
        
        // Save calculation to database
        saveCalculationToDatabase(
            $conn,
            $user_id,
            'monthly_budget',
            json_encode($calculator_results['budget']),
            json_encode($budget_data)
        );
    }
}

// Get financial advisors
$financial_advisors = getFinancialAdvisors($conn);

// Get legal resources
$legal_resources = getLegalResources();

// Get upcoming payments for the user
$upcoming_payments = getUserUpcomingPayments($conn, $user_id);

// Get user's saved calculations
$saved_calculations = getUserSavedCalculations($conn, $user_id);

// Database functions
function saveCalculationToDatabase($conn, $user_id, $calculation_type, $result, $inputs) {
    $sql = "INSERT INTO financial_calculations 
            (user_id, calculation_type, calculation_result, calculation_inputs, created_at) 
            VALUES (?, ?, ?, ?, NOW())";
    
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("isss", $user_id, $calculation_type, $result, $inputs);
    return $stmt->execute();
}

function getUserSavedCalculations($conn, $user_id) {
    $sql = "SELECT * FROM financial_calculations 
            WHERE user_id = ? 
            ORDER BY created_at DESC 
            LIMIT 10";
    
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    $calculations = array();
    while ($row = $result->fetch_assoc()) {
        $calculations[] = $row;
    }
    
    return $calculations;
}

function getFinancialAdvisors($conn) {
    // In a real application, this would fetch from database
    return [
        [
            'id' => 1,
            'name' => 'Sarah Johnson',
            'specialty' => 'Retirement Planning',
            'image' => 'https://via.placeholder.com/100x100/4CAF50/ffffff?text=SJ',
            'rating' => 4.8,
            'available' => true,
            'languages' => ['English', 'Bengali']
        ],
        [
            'id' => 2,
            'name' => 'Michael Patel',
            'specialty' => 'Estate Planning',
            'image' => 'https://via.placeholder.com/100x100/2196F3/ffffff?text=MP',
            'rating' => 4.9,
            'available' => true,
            'languages' => ['English', 'Hindi', 'Bengali']
        ],
        [
            'id' => 3,
            'name' => 'Fatima Rahman',
            'specialty' => 'Tax Filing Assistance',
            'image' => 'https://via.placeholder.com/100x100/9C27B0/ffffff?text=FR',
            'rating' => 4.7,
            'available' => true,
            'languages' => ['Bengali', 'English']
        ],
        [
            'id' => 4,
            'name' => 'Aminul Islam',
            'specialty' => 'Government Benefits',
            'image' => 'https://via.placeholder.com/100x100/FF9800/ffffff?text=AI',
            'rating' => 4.6,
            'available' => false,
            'languages' => ['Bengali']
        ]
    ];
}

function getLegalResources() {
    return [
        [
            'title' => 'Creating a Simple Will',
            'description' => 'Learn how to create a legally binding will to ensure your wishes are carried out.',
            'icon' => 'fas fa-file-signature',
            'link' => '#will-creation-tool'
        ],
        [
            'title' => 'Power of Attorney Documents',
            'description' => 'Understand power of attorney options and create the right documents for your needs.',
            'icon' => 'fas fa-user-shield',
            'link' => '#power-of-attorney'
        ],
        [
            'title' => 'Advanced Healthcare Directives',
            'description' => 'Specify your healthcare preferences if you become unable to communicate them.',
            'icon' => 'fas fa-file-medical',
            'link' => '#healthcare-directives'
        ],
        [
            'title' => 'Land Registration Guide',
            'description' => 'Learn about proper land registration procedures and avoiding common pitfalls.',
            'icon' => 'fas fa-map-marked-alt',
            'link' => '#land-registration'
        ]
    ];
}

function getUserUpcomingPayments($conn, $user_id) {
    // In a real application, this would fetch from database
    return [
        [
            'name' => 'Electricity Bill',
            'amount' => '৳3,450',
            'due_date' => date('Y-m-d', strtotime('+3 days')),
            'status' => 'upcoming',
            'autopay' => false
        ],
        [
            'name' => 'Health Insurance Premium',
            'amount' => '৳6,500',
            'due_date' => date('Y-m-d', strtotime('+7 days')),
            'status' => 'upcoming',
            'autopay' => true
        ],
        [
            'name' => 'Property Tax',
            'amount' => '৳28,900',
            'due_date' => date('Y-m-d', strtotime('+14 days')),
            'status' => 'upcoming',
            'autopay' => false
        ]
    ];
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Financial & Legal Assistance - Elderly Care Dashboard</title>
    
    <!-- Google Translate -->
    <script type="text/javascript" src="https://translate.google.com/translate_a/element.js?cb=googleTranslateElementInit"></script>
    <script type="text/javascript">
        function googleTranslateElementInit() {
            new google.translate.TranslateElement({
                pageLanguage: 'en',
                includedLanguages: 'bn,en',
                layout: google.translate.TranslateElement.InlineLayout.SIMPLE
            }, 'google_translate_element');
            
            new google.translate.TranslateElement({
                pageLanguage: 'en',
                includedLanguages: 'bn,en',
                layout: google.translate.TranslateElement.InlineLayout.SIMPLE
            }, 'mobile-google-translate');
        }
    </script>
    
    <!-- Styles and fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@400;500;600;700;800&family=Open+Sans:wght@400;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <link rel="stylesheet" href="../includes/style.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/chart.js@3.7.0/dist/chart.min.css">
    
    <style>
        /* Financial Assistance specific styling */
        .financial-container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 2rem;
            font-family: 'Open Sans', sans-serif;
        }
        
        .financial-header {
            text-align: center;
            margin-bottom: 3rem;
            padding-bottom: 1rem;
            border-bottom: 1px solid #e0e0e0;
        }
        
        .financial-header h1 {
            font-family: 'Montserrat', sans-serif;
            font-weight: 700;
            font-size: 2.5rem;
            color: #2d3748;
            margin-bottom: 1rem;
        }
        
        .financial-header p {
            font-size: 1.2rem;
            color: #4a5568;
            max-width: 800px;
            margin: 0 auto;
        }
        
        .financial-section {
            background: white;
            border-radius: 15px;
            padding: 2rem;
            margin-bottom: 2rem;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.05);
        }
        
        .section-title {
            display: flex;
            align-items: center;
            margin-bottom: 1.5rem;
            padding-bottom: 0.5rem;
            border-bottom: 2px solid #0096ad;
        }
        
        .section-title i {
            font-size: 1.8rem;
            color: #0096ad;
            margin-right: 1rem;
        }
        
        .section-title h2 {
            font-family: 'Montserrat', sans-serif;
            font-weight: 600;
            font-size: 1.8rem;
            color: #2d3748;
            margin: 0;
        }
        
        /* Financial AI Assistant */
        .ai-assistant-card {
            background: linear-gradient(135deg, #0096ad 0%, #00b0cc 100%);
            border-radius: 15px;
            padding: 2rem;
            color: white;
            display: flex;
            align-items: center;
            margin-bottom: 2rem;
            box-shadow: 0 10px 15px rgba(0, 150, 173, 0.2);
        }
        
        .ai-assistant-icon {
            width: 80px;
            height: 80px;
            background: rgba(255, 255, 255, 0.2);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin-right: 1.5rem;
            flex-shrink: 0;
        }
        
        .ai-assistant-icon i {
            font-size: 2.5rem;
        }
        
        .ai-assistant-content h3 {
            font-family: 'Montserrat', sans-serif;
            font-weight: 600;
            font-size: 1.5rem;
            margin-bottom: 0.5rem;
        }
        
        .ai-assistant-content p {
            font-size: 1.1rem;
            margin-bottom: 1rem;
        }
        
        .ask-button {
            background: white;
            color: #0096ad;
            border: none;
            border-radius: 25px;
            padding: 0.7rem 2rem;
            font-weight: 600;
            font-size: 1rem;
            cursor: pointer;
            transition: all 0.3s ease;
        }
        
        .ask-button:hover {
            background: rgba(255, 255, 255, 0.9);
            transform: scale(1.05);
        }
        
        /* Financial Tools Grid */
        .tools-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
            gap: 1.5rem;
        }
        
        .tool-card {
            background: #f8f9fa;
            border-radius: 12px;
            padding: 1.5rem;
            transition: all 0.3s ease;
            border: 1px solid #e0e0e0;
            height: 100%;
        }
        
        .tool-card:hover {
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.1);
            transform: translateY(-5px);
        }
        
        .tool-icon {
            font-size: 2rem;
            color: #0096ad;
            margin-bottom: 1rem;
        }
        
        .tool-title {
            font-family: 'Montserrat', sans-serif;
            font-weight: 600;
            font-size: 1.3rem;
            color: #2d3748;
            margin-bottom: 0.5rem;
        }
        
        .tool-description {
            font-size: 1rem;
            color: #4a5568;
            margin-bottom: 1rem;
        }
        
        .tool-link {
            display: inline-flex;
            align-items: center;
            color: #0096ad;
            font-weight: 600;
            text-decoration: none;
        }
        
        .tool-link:hover {
            text-decoration: underline;
        }
        
        .tool-link i {
            margin-left: 0.5rem;
        }
        
        /* Payments Section */
        .payments-list {
            margin-top: 1.5rem;
        }
        
        .payment-item {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 1rem 0;
            border-bottom: 1px solid #e0e0e0;
        }
        
        .payment-item:last-child {
            border-bottom: none;
        }
        
        .payment-details {
            display: flex;
            flex-direction: column;
        }
        
        .payment-name {
            font-weight: 600;
            color: #2d3748;
            font-size: 1.1rem;
        }
        
        .payment-date {
            color: #718096;
            font-size: 0.9rem;
            margin-top: 0.2rem;
        }
        
        .payment-amount {
            font-weight: 600;
            font-size: 1.2rem;
            color: #2d3748;
        }
        
        .payment-status {
            display: flex;
            align-items: center;
        }
        
        .status-badge {
            padding: 0.3rem 0.7rem;
            border-radius: 20px;
            font-size: 0.8rem;
            font-weight: 600;
            margin-right: 0.5rem;
        }
        
        .status-upcoming {
            background-color: #ebf8ff;
            color: #3182ce;
        }
        
        .status-paid {
            background-color: #e6fffa;
            color: #38b2ac;
        }
        
        .status-autopay {
            background-color: #fefcbf;
            color: #d69e2e;
        }
        
        /* Financial Advisors */
        .advisors-list {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(280px, 1fr));
            gap: 1.5rem;
            margin-top: 1.5rem;
        }
        
        .advisor-card {
            background: white;
            border-radius: 12px;
            padding: 1.5rem;
            box-shadow: 0 2px 5px rgba(0, 0, 0, 0.05);
            border: 1px solid #e0e0e0;
            display: flex;
            flex-direction: column;
            align-items: center;
            text-align: center;
            transition: all 0.3s ease;
        }
        
        .advisor-card:hover {
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.1);
            transform: translateY(-5px);
        }
        
        .advisor-image {
            width: 90px;
            height: 90px;
            border-radius: 50%;
            object-fit: cover;
            margin-bottom: 1rem;
            border: 3px solid #0096ad;
        }
        
        .advisor-name {
            font-family: 'Montserrat', sans-serif;
            font-weight: 600;
            font-size: 1.2rem;
            color: #2d3748;
            margin-bottom: 0.3rem;
        }
        
        .advisor-specialty {
            color: #718096;
            font-size: 0.9rem;
            margin-bottom: 0.8rem;
        }
        
        .advisor-rating {
            display: flex;
            align-items: center;
            justify-content: center;
            margin-bottom: 1rem;
        }
        
        .advisor-rating i {
            color: #fbbf24;
            font-size: 1.1rem;
            margin: 0 0.1rem;
        }
        
        .rating-value {
            font-weight: 600;
            margin-left: 0.5rem;
        }
        
        .advisor-languages {
            color: #718096;
            font-size: 0.85rem;
            margin-bottom: 0.8rem;
        }
        
        .advisor-action {
            width: 100%;
            margin-top: auto;
        }
        
        .schedule-btn {
            background: #0096ad;
            color: white;
            border: none;
            padding: 0.8rem 0;
            border-radius: 25px;
            width: 100%;
            font-weight: 600;
            cursor: pointer;
            transition: background 0.3s ease;
        }
        
        .schedule-btn:hover {
            background: #00809e;
        }
        
        .schedule-btn.disabled {
            background: #cbd5e0;
            cursor: not-allowed;
        }
        
        /* Voice Assistant */
        .voice-assistant {
            position: fixed;
            bottom: 30px;
            right: 30px;
            width: 60px;
            height: 60px;
            border-radius: 50%;
            background: #0096ad;
            border: none;
            box-shadow: 0 4px 15px rgba(0, 150, 173, 0.4);
            cursor: pointer;
            z-index: 99;
            transition: all 0.3s ease;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        
        .voice-assistant:hover {
            transform: scale(1.1);
        }
        
        .voice-assistant i {
            color: white;
            font-size: 1.5rem;
        }
        
        .voice-feedback {
            position: fixed;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            background: white;
            border-radius: 15px;
            padding: 2rem;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.2);
            z-index: 1000;
            width: 90%;
            max-width: 400px;
            text-align: center;
            display: none;
        }
        
        .voice-waves {
            display: flex;
            justify-content: center;
            align-items: center;
            height: 60px;
            margin: 1.5rem 0;
            gap: 5px;
        }
        
        .voice-wave {
            width: 8px;
            height: 30px;
            background: #0096ad;
            border-radius: 4px;
            animation: wave 1s infinite ease-in-out;
        }
        
        .voice-wave:nth-child(2) { animation-delay: 0.1s; }
        .voice-wave:nth-child(3) { animation-delay: 0.2s; }
        .voice-wave:nth-child(4) { animation-delay: 0.3s; }
        .voice-wave:nth-child(5) { animation-delay: 0.4s; }
        
        @keyframes wave {
            0%, 100% { height: 15px; }
            50% { height: 50px; }
        }
        
        /* Emergency Button */
        .emergency-button {
            position: fixed;
            bottom: 30px;
            left: 30px;
            width: 60px;
            height: 60px;
            border-radius: 50%;
            background: #e53e3e;
            border: none;
            box-shadow: 0 4px 15px rgba(229, 62, 62, 0.4);
            cursor: pointer;
            z-index: 99;
            transition: all 0.3s ease;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        
        .emergency-button:hover {
            animation: pulse 2s infinite;
        }
        
        .emergency-button svg {
            width: 30px;
            height: 30px;
        }
        
        @keyframes pulse {
            0% { transform: scale(1); }
            50% { transform: scale(1.1); }
            100% { transform: scale(1); }
        }
        
        /* Calculator Styles */
        .calculator-container {
            background: #f8f9fa;
            border-radius: 12px;
            padding: 1.5rem;
            margin-top: 1.5rem;
            border: 1px solid #e0e0e0;
        }
        
        .calculator-title {
            font-family: 'Montserrat', sans-serif;
            font-weight: 600;
            font-size: 1.3rem;
            color: #2d3748;
            margin-bottom: 1rem;
            border-bottom: 1px solid #e0e0e0;
            padding-bottom: 0.5rem;
        }
        
        .calculator-form {
            margin-top: 1rem;
        }
        
        .form-row {
            display: flex;
            flex-wrap: wrap;
            margin: 0 -0.5rem 1rem;
        }
        
        .form-group {
            flex: 1;
            min-width: 200px;
            padding: 0 0.5rem;
            margin-bottom: 1rem;
        }
        
        .form-group label {
            display: block;
            margin-bottom: 0.5rem;
            font-weight: 500;
            color: #4a5568;
        }
        
        .form-control {
            width: 100%;
            padding: 0.8rem 1rem;
            border: 1px solid #cbd5e0;
            border-radius: 8px;
            font-size: 1rem;
            transition: border-color 0.3s ease;
        }
        
        .form-control:focus {
            outline: none;
            border-color: #0096ad;
            box-shadow: 0 0 0 3px rgba(0, 150, 173, 0.1);
        }
        
        .helper-text {
            font-size: 0.85rem;
            color: #718096;
            margin-top: 0.3rem;
        }
        
        .calculator-button {
            background: #0096ad;
            color: white;
            border: none;
            padding: 0.8rem 2rem;
            border-radius: 25px;
            font-weight: 600;
            cursor: pointer;
            transition: background 0.3s ease;
        }
        
        .calculator-button:hover {
            background: #00809e;
        }
        
        .calculator-divider {
            margin: 1.5rem 0;
            border-top: 1px dashed #cbd5e0;
        }
        
        .calculator-result {
            background: #fff;
            border-radius: 8px;
            padding: 1.5rem;
            margin-top: 1.5rem;
            box-shadow: 0 2px 5px rgba(0, 0, 0, 0.05);
        }
        
        .result-title {
            font-family: 'Montserrat', sans-serif;
            font-weight: 600;
            font-size: 1.2rem;
            color: #2d3748;
            margin-bottom: 1rem;
        }
        
        .result-item {
            display: flex;
            justify-content: space-between;
            padding: 0.7rem 0;
            border-bottom: 1px solid #e0e0e0;
        }
        
        .result-item:last-child {
            border-bottom: none;
        }
        
        .result-label {
            font-weight: 500;
            color: #4a5568;
        }
        
        .result-value {
            font-weight: 600;
            color: #2d3748;
        }
        
        .result-value.positive {
            color: #38b2ac;
        }
        
        .result-value.negative {
            color: #e53e3e;
        }
        
        .result-total {
            font-weight: 700;
            font-size: 1.2rem;
            color: #2d3748;
            padding-top: 0.5rem;
            margin-top: 0.5rem;
            border-top: 2px solid #e0e0e0;
        }
        
        /* Tabs for Calculator Section */
        .calculator-tabs {
            display: flex;
            border-bottom: 1px solid #e0e0e0;
            margin-bottom: 1.5rem;
            overflow-x: auto;
            white-space: nowrap;
            -webkit-overflow-scrolling: touch;
        }
        
        .calculator-tab {
            padding: 0.8rem 1.2rem;
            font-weight: 600;
            color: #718096;
            cursor: pointer;
            transition: all 0.3s ease;
            border-bottom: 3px solid transparent;
            font-family: 'Open Sans', sans-serif;
        }
        
        .calculator-tab.active {
            color: #0096ad;
            border-bottom-color: #0096ad;
        }
        
        .calculator-tab:hover {
            color: #0096ad;
        }
        
        .calculator-panel {
            display: none;
        }
        
        .calculator-panel.active {
            display: block;
        }
        
        /* Chart styles */
        .chart-container {
            height: 300px;
            margin: 2rem 0;
        }
        
        .result-summary {
            background: #e6fffa;
            border-radius: 8px;
            padding: 1rem;
            margin: 1rem 0;
            color: #2d3748;
            font-weight: 500;
            border-left: 4px solid #38b2ac;
        }
        
        /* Mobile Nav Bar Fixes */
        .mobile-nav {
            position: fixed;
            bottom: 0;
            left: 0;
            width: 100%;
            background: white;
            display: none;
            z-index: 100;
            box-shadow: 0 -2px 10px rgba(0, 0, 0, 0.1);
        }
        
        .mobile-nav-container {
            display: flex;
            justify-content: space-around;
            padding: 0.8rem 0;
        }
        
        .mobile-nav-item {
            display: flex;
            flex-direction: column;
            align-items: center;
            text-decoration: none;
            color: #718096;
            font-size: 0.8rem;
            padding: 0.5rem 0;
        }
        
        .mobile-nav-item i {
            font-size: 1.4rem;
            margin-bottom: 0.3rem;
        }
        
        .mobile-nav-item.active {
            color: #0096ad;
        }
        
        @media (max-width: 768px) {
            .mobile-nav {
                display: block;
            }
            
            .footer {
                margin-bottom: 70px;
            }
            
            .voice-assistant, .emergency-button {
                bottom: 90px;
            }
            
            body {
                padding-bottom: 70px;
            }
        }
        
        /* Mobile Specific Styles */
        @media (max-width: 768px) {
            .financial-container {
                padding: 1rem;
            }
            
            .financial-header h1 {
                font-size: 2rem;
            }
            
            .financial-header p {
                font-size: 1rem;
            }
            
            .tools-grid, .advisors-list {
                grid-template-columns: 1fr;
            }
            
            .ai-assistant-card {
                flex-direction: column;
                text-align: center;
            }
            
            .ai-assistant-icon {
                margin-right: 0;
                margin-bottom: 1rem;
            }
            
            .section-title h2 {
                font-size: 1.5rem;
            }
            
            .form-row {
                margin: 0 0 1rem;
            }
            
            .form-group {
                padding: 0;
            }
            
            .calculator-tab {
                padding: 0.7rem 1rem;
                font-size: 0.9rem;
            }
            
            .result-item {
                flex-direction: column;
            }
            
            .result-value {
                margin-top: 0.3rem;
            }
            
            .chart-container {
                height: 220px;
            }
            
            .accessibility-panel {
                display: none;
            }
        }
        
        /* Accessibility panel */
        .accessibility-panel {
            position: fixed;
            left: 20px;
            top: 50%;
            transform: translateY(-50%);
            background: white;
            border-radius: 15px;
            padding: 1rem;
            box-shadow: 0 4px 20px rgba(0,0,0,0.15);
            z-index: 100;
        }
        
        .accessibility-btn {
            display: block;
            width: 60px;
            height: 60px;
            margin-bottom: 1rem;
            border: none;
            border-radius: 12px;
            background: #f8f9fa;
            cursor: pointer;
            transition: all 0.3s ease;
            font-size: 1.5rem;
            color: #4a5568;
        }
        
        .accessibility-btn:hover {
            background: #0096ad;
            color: white;
            transform: scale(1.1);
        }
        
        .accessibility-btn:last-child {
            margin-bottom: 0;
        }
        
        /* High contrast mode */
        body.high-contrast {
            background: #000 !important;
            color: #fff !important;
        }
        
        body.high-contrast .financial-section,
        body.high-contrast .tool-card,
        body.high-contrast .advisor-card,
        body.high-contrast .calculator-container {
            background: #000 !important;
            border: 1px solid #fff !important;
            color: #fff !important;
        }
        
        body.high-contrast h1, 
        body.high-contrast h2, 
        body.high-contrast h3,
        body.high-contrast .tool-title,
        body.high-contrast .payment-name,
        body.high-contrast .payment-amount,
        body.high-contrast .calculator-title {
            color: #fff !important;
        }
        
        body.high-contrast .tool-description,
        body.high-contrast .payment-date,
        body.high-contrast .helper-text {
            color: #ccc !important;
        }
        
        body.high-contrast .tool-link {
            color: #73e3ff !important;
        }
        
        body.high-contrast .calculator-result {
            background: #222 !important;
        }
        
        body.high-contrast .result-label {
            color: #ccc !important;
        }
        
        body.high-contrast .result-value {
            color: #fff !important;
        }
        
        body.high-contrast .result-value.positive {
            color: #5effef !important;
        }
        
        body.high-contrast .result-value.negative {
            color: #ff7373 !important;
        }
        
        /* Error message styling */
        .error-message {
            color: #e53e3e;
            font-size: 0.9rem;
            margin-top: 0.5rem;
        }
    </style>
</head>

<body>
    <?php include '../includes/nav.php'; ?>
    
    <!-- Fixed mobile navigation bar -->
    <div class="mobile-nav">
        <div class="mobile-nav-container">
            <a href="../index.php" class="mobile-nav-item">
                <i class="fas fa-home"></i>
                <span>Home</span>
            </a>
            <a href="../health/index.php" class="mobile-nav-item">
                <i class="fas fa-heartbeat"></i>
                <span>Health</span>
            </a>
            <a href="financial-legal-assistance.php" class="mobile-nav-item active">
                <i class="fas fa-money-bill-wave"></i>
                <span>Finance</span>
            </a>
            <a href="../social/index.php" class="mobile-nav-item">
                <i class="fas fa-users"></i>
                <span>Social</span>
            </a>
            <a href="../profile.php" class="mobile-nav-item">
                <i class="fas fa-user-circle"></i>
                <span>Profile</span>
            </a>
        </div>
    </div>
    
    <!-- Voice feedback dialog -->
    <div class="voice-feedback" id="voiceFeedback">
        <h3>Listening...</h3>
        <div class="voice-waves">
            <div class="voice-wave"></div>
            <div class="voice-wave"></div>
            <div class="voice-wave"></div>
            <div class="voice-wave"></div>
            <div class="voice-wave"></div>
        </div>
        <p id="voiceResult">Say a command...</p>
    </div>
    
    <main>
        <div class="financial-container">
            <div class="financial-header">
                <h1>Financial & Legal Assistance</h1>
                <p>Get personalized help with your finances, taxes, and legal matters with our AI-powered tools and expert advisors.</p>
            </div>
            
            <!-- AI Financial Assistant -->
            <div class="ai-assistant-card">
                <div class="ai-assistant-icon">
                    <i class="fas fa-robot"></i>
                </div>
                <div class="ai-assistant-content">
                    <h3>Your Financial Assistant</h3>
                    <p>Ask me any questions about your finances, retirement planning, taxes, or legal documents. I'm here to help you make informed decisions.</p>
                    <button class="ask-button" onclick="startAIAssistant()">
                        <i class="fas fa-microphone"></i> Ask a Question
                    </button>
                </div>
            </div>
            
            <!-- Financial Calculators -->
            <div class="financial-section" id="financial-calculators">
                <div class="section-title">
                    <i class="fas fa-calculator"></i>
                    <h2>Financial Calculators (in Taka)</h2>
                </div>
                
                <div class="calculator-tabs">
                    <div class="calculator-tab active" onclick="openCalculator('retirement')">Retirement Planning</div>
                    <div class="calculator-tab" onclick="openCalculator('budget')">Monthly Budget</div>
                    <div class="calculator-tab" onclick="openCalculator('medical')">Medical Expenses</div>
                    <div class="calculator-tab" onclick="openCalculator('social-security')">Benefits Estimator</div>
                </div>
                
                <!-- Retirement Calculator -->
                <div class="calculator-panel active" id="retirement-calculator">
                    <div class="calculator-container">
                        <div class="calculator-title">Retirement Planning Calculator</div>
                        <p>Plan your retirement funds and estimate how much you need to save monthly to meet your goals.</p>
                        
                        <?php if (isset($calculator_errors['retirement'])): ?>
                            <div class="error-message"><?php echo $calculator_errors['retirement']; ?></div>
                        <?php endif; ?>
                        
                        <form class="calculator-form" method="post">
                            <div class="form-row">
                                <div class="form-group">
                                    <label for="current_age">Current Age</label>
                                    <input type="number" id="current_age" name="current_age" class="form-control" min="45" max="90" value="60">
                                </div>
                                <div class="form-group">
                                    <label for="retirement_age">Retirement Age</label>
                                    <input type="number" id="retirement_age" name="retirement_age" class="form-control" min="45" max="90" value="65">
                                </div>
                            </div>
                            
                            <div class="form-row">
                                <div class="form-group">
                                    <label for="monthly_income">Current Monthly Income (৳)</label>
                                    <input type="number" id="monthly_income" name="monthly_income" class="form-control" placeholder="e.g., 25000" value="25000">
                                </div>
                                <div class="form-group">
                                    <label for="monthly_expenses">Expected Monthly Expenses in Retirement (৳)</label>
                                    <input type="number" id="monthly_expenses" name="monthly_expenses" class="form-control" placeholder="e.g., 20000" value="20000">
                                </div>
                            </div>
                            
                            <div class="form-row">
                                <div class="form-group">
                                    <label for="current_savings">Current Retirement Savings (৳)</label>
                                    <input type="number" id="current_savings" name="current_savings" class="form-control" placeholder="e.g., 1000000" value="500000">
                                </div>
                                <div class="form-group">
                                    <label for="expected_return">Expected Return on Investments (%)</label>
                                    <input type="number" id="expected_return" name="expected_return" class="form-control" placeholder="e.g., 7" step="0.1" min="1" max="15" value="7">
                                    <p class="helper-text">Annual return on investments, typically between 5-10%</p>
                                </div>
                                <div class="form-group">
                                    <label for="inflation_rate">Expected Inflation Rate (%)</label>
                                    <input type="number" id="inflation_rate" name="inflation_rate" class="form-control" placeholder="e.g., 5" step="0.1" min="1" max="15" value="5">
                                    <p class="helper-text">Average inflation rate in Bangladesh is around 5-6%</p>
                                </div>
                            </div>
                            
                            <button type="submit" name="calculate_retirement" class="calculator-button">Calculate Retirement Needs</button>
                        </form>
                        
                        <?php if (isset($calculator_results['retirement'])): ?>
                            <div class="calculator-result">
                                <div class="result-title">Your Retirement Plan Results</div>
                                
                                <div class="result-summary">
                                    <?php echo $calculator_results['retirement']['summary']; ?>
                                </div>
                                
                                <div class="result-item">
                                    <span class="result-label">Years Until Retirement:</span>
                                    <span class="result-value"><?php echo $calculator_results['retirement']['years_to_retirement']; ?> years</span>
                                </div>
                                
                                <div class="result-item">
                                    <span class="result-label">Estimated Retirement Duration:</span>
                                    <span class="result-value"><?php echo $calculator_results['retirement']['retirement_duration']; ?> years</span>
                                </div>
                                
                                <div class="result-item">
                                    <span class="result-label">Required Retirement Fund:</span>
                                    <span class="result-value">৳<?php echo number_format($calculator_results['retirement']['required_fund']); ?></span>
                                </div>
                                
                                <div class="result-item">
                                    <span class="result-label">Current Savings Future Value:</span>
                                    <span class="result-value">৳<?php echo number_format($calculator_results['retirement']['future_savings_value']); ?></span>
                                </div>
                                
                                <div class="result-item">
                                    <span class="result-label">Additional Funding Needed:</span>
                                    <span class="result-value <?php echo ($calculator_results['retirement']['shortfall'] > 0 ? 'negative' : 'positive'); ?>">
                                        ৳<?php echo number_format($calculator_results['retirement']['shortfall']); ?>
                                    </span>
                                </div>
                                
                                <div class="result-item">
                                    <span class="result-label">Recommended Monthly Savings:</span>
                                    <span class="result-value">৳<?php echo number_format($calculator_results['retirement']['monthly_savings']); ?></span>
                                </div>
                            </div>
                            
                            <div class="chart-container">
                                <canvas id="retirementChart"></canvas>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
                
                <!-- Monthly Budget Calculator -->
                <div class="calculator-panel" id="budget-calculator">
                    <div class="calculator-container">
                        <div class="calculator-title">Monthly Budget Planner</div>
                        <p>Track your income and expenses to create a balanced monthly budget.</p>
                        
                        <form class="calculator-form" method="post">
                            <div class="form-group">
                                <h4>Monthly Income</h4>
                            </div>
                            
                            <div class="form-row">
                                <div class="form-group">
                                    <label for="income_pension">Pension (৳)</label>
                                    <input type="number" id="income_pension" name="income_pension" class="form-control" placeholder="0" value="15000">
                                </div>
                                <div class="form-group">
                                    <label for="income_social_security">Social Security (৳)</label>
                                    <input type="number" id="income_social_security" name="income_social_security" class="form-control" placeholder="0" value="5000">
                                </div>
                            </div>
                            
                            <div class="form-row">
                                <div class="form-group">
                                    <label for="income_investments">Investments (৳)</label>
                                    <input type="number" id="income_investments" name="income_investments" class="form-control" placeholder="0" value="7000">
                                </div>
                                <div class="form-group">
                                    <label for="income_other">Other Income (৳)</label>
                                    <input type="number" id="income_other" name="income_other" class="form-control" placeholder="0" value="3000">
                                </div>
                            </div>
                            
                            <div class="calculator-divider"></div>
                            
                            <div class="form-group">
                                <h4>Monthly Expenses</h4>
                            </div>
                            
                            <div class="form-row">
                                <div class="form-group">
                                    <label for="expense_housing">Housing/Rent (৳)</label>
                                    <input type="number" id="expense_housing" name="expense_housing" class="form-control" placeholder="0" value="8000">
                                </div>
                                <div class="form-group">
                                    <label for="expense_utilities">Utilities (৳)</label>
                                    <input type="number" id="expense_utilities" name="expense_utilities" class="form-control" placeholder="0" value="3000">
                                </div>
                            </div>
                            
                            <div class="form-row">
                                <div class="form-group">
                                    <label for="expense_food">Food (৳)</label>
                                    <input type="number" id="expense_food" name="expense_food" class="form-control" placeholder="0" value="7000">
                                </div>
                                <div class="form-group">
                                    <label for="expense_healthcare">Healthcare (৳)</label>
                                    <input type="number" id="expense_healthcare" name="expense_healthcare" class="form-control" placeholder="0" value="5000">
                                </div>
                            </div>
                            
                            <div class="form-row">
                                <div class="form-group">
                                    <label for="expense_transportation">Transportation (৳)</label>
                                    <input type="number" id="expense_transportation" name="expense_transportation" class="form-control" placeholder="0" value="2000">
                                </div>
                                <div class="form-group">
                                    <label for="expense_entertainment">Entertainment (৳)</label>
                                    <input type="number" id="expense_entertainment" name="expense_entertainment" class="form-control" placeholder="0" value="1500">
                                </div>
                            </div>
                            
                            <div class="form-row">
                                <div class="form-group">
                                    <label for="expense_other">Other Expenses (৳)</label>
                                    <input type="number" id="expense_other" name="expense_other" class="form-control" placeholder="0" value="2500">
                                </div>
                            </div>
                            
                            <button type="submit" name="save_monthly_budget" class="calculator-button">Calculate Budget</button>
                        </form>
                        
                        <?php if (isset($calculator_results['budget'])): ?>
                            <div class="calculator-result">
                                <div class="result-title">Your Monthly Budget Summary</div>
                                
                                <div class="result-item">
                                    <span class="result-label">Total Monthly Income:</span>
                                    <span class="result-value">৳<?php echo number_format($calculator_results['budget']['total_income']); ?></span>
                                </div>
                                
                                <div class="result-item">
                                    <span class="result-label">Total Monthly Expenses:</span>
                                    <span class="result-value">৳<?php echo number_format($calculator_results['budget']['total_expenses']); ?></span>
                                </div>
                                
                                <div class="result-item">
                                    <span class="result-label">Monthly Balance:</span>
                                    <span class="result-value <?php echo ($calculator_results['budget']['balance'] < 0 ? 'negative' : 'positive'); ?>">
                                        ৳<?php echo number_format($calculator_results['budget']['balance']); ?>
                                    </span>
                                </div>
                                
                                <div class="result-item">
                                    <span class="result-label">Yearly Savings Potential:</span>
                                    <span class="result-value <?php echo ($calculator_results['budget']['yearly_savings'] < 0 ? 'negative' : 'positive'); ?>">
                                        ৳<?php echo number_format($calculator_results['budget']['yearly_savings']); ?>
                                    </span>
                                </div>
                                
                                <div class="result-summary">
                                    <?php echo $calculator_results['budget']['advice']; ?>
                                </div>
                            </div>
                            
                            <div class="chart-container">
                                <canvas id="budgetChart"></canvas>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
                
                <!-- Medical Expenses Calculator -->
                <div class="calculator-panel" id="medical-calculator">
                    <div class="calculator-container">
                        <div class="calculator-title">Medical Expenses Calculator</div>
                        <p>Track and forecast your medical expenses to better plan your healthcare budget.</p>
                        
                        <?php if (isset($calculator_errors['medical'])): ?>
                            <div class="error-message"><?php echo $calculator_errors['medical']; ?></div>
                        <?php endif; ?>
                        
                        <form class="calculator-form" method="post">
                            <div class="form-row">
                                <div class="form-group">
                                    <label for="monthly_medication">Monthly Medication Costs (৳)</label>
                                    <input type="number" id="monthly_medication" name="monthly_medication" class="form-control" placeholder="e.g., 2000" value="2000">
                                </div>
                                <div class="form-group">
                                    <label for="doctor_visits">Doctor Visit Costs (৳/month)</label>
                                    <input type="number" id="doctor_visits" name="doctor_visits" class="form-control" placeholder="e.g., 1500" value="1500">
                                    <p class="helper-text">Average monthly cost of doctor visits</p>
                                </div>
                            </div>
                            
                            <div class="form-row">
                                <div class="form-group">
                                    <label for="hospital_costs">Expected Hospital Costs (৳/year)</label>
                                    <input type="number" id="hospital_costs" name="hospital_costs" class="form-control" placeholder="e.g., 20000" value="20000">
                                    <p class="helper-text">Estimated annual costs for hospital stays/procedures</p>
                                </div>
                                <div class="form-group">
                                    <label for="insurance_premium">Insurance Premium (৳/month)</label>
                                    <input type="number" id="insurance_premium" name="insurance_premium" class="form-control" placeholder="e.g., 1000" value="1000">
                                </div>
                            </div>
                            
                            <div class="form-row">
                                <div class="form-group">
                                    <label for="other_expenses">Other Medical Expenses (৳/month)</label>
                                    <input type="number" id="other_expenses" name="other_expenses" class="form-control" placeholder="e.g., 500" value="500">
                                    <p class="helper-text">Vitamins, medical equipment, therapy, etc.</p>
                                </div>
                            </div>
                            
                            <button type="submit" name="calculate_medical_expenses" class="calculator-button">Calculate Medical Expenses</button>
                        </form>
                        
                        <?php if (isset($calculator_results['medical'])): ?>
                            <div class="calculator-result">
                                <div class="result-title">Your Medical Expenses Summary</div>
                                
                                <div class="result-item">
                                    <span class="result-label">Total Monthly Medical Costs:</span>
                                    <span class="result-value">৳<?php echo number_format($calculator_results['medical']['monthly_total']); ?></span>
                                </div>
                                
                                <div class="result-item">
                                    <span class="result-label">Annual Medication Costs:</span>
                                    <span class="result-value">৳<?php echo number_format($calculator_results['medical']['annual_medication']); ?></span>
                                </div>
                                
                                <div class="result-item">
                                    <span class="result-label">Annual Doctor Visit Costs:</span>
                                    <span class="result-value">৳<?php echo number_format($calculator_results['medical']['annual_doctor_visits']); ?></span>
                                </div>
                                
                                <div class="result-item">
                                    <span class="result-label">Annual Insurance Premium:</span>
                                    <span class="result-value">৳<?php echo number_format($calculator_results['medical']['annual_insurance']); ?></span>
                                </div>
                                
                                <div class="result-item">
                                    <span class="result-label">Annual Hospital Costs:</span>
                                    <span class="result-value">৳<?php echo number_format($calculator_results['medical']['hospital_costs']); ?></span>
                                </div>
                                
                                <div class="result-item">
                                    <span class="result-label">Annual Other Medical Costs:</span>
                                    <span class="result-value">৳<?php echo number_format($calculator_results['medical']['annual_other']); ?></span>
                                </div>
                                
                                <div class="result-total">
                                    <div class="result-item">// filepath: c:\xampp\htdocs\Astha\elderly\fin\financial-legal-assistance.php
                                    <span class="result-label">Total Annual Medical Expenses:</span>
                                    <span class="result-value">৳<?php echo number_format($calculator_results['medical']['annual_total']); ?></span>
                                </div>
                            </div>
                        <?php endif; ?>
                        
                        <div class="chart-container">
                            <canvas id="medicalExpensesChart"></canvas>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Financial Advisors Section -->
            <div class="financial-section">
                <div class="section-title">
                    <i class="fas fa-user-tie"></i>
                    <h2>Financial Advisors</h2>
                </div>
                
                <div class="advisors-list">
                    <?php foreach ($financial_advisors as $advisor): ?>
                        <div class="advisor-card">
                            <img src="<?php echo $advisor['image']; ?>" alt="<?php echo $advisor['name']; ?>" class="advisor-image">
                            <div class="advisor-name"><?php echo $advisor['name']; ?></div>
                            <div class="advisor-specialty"><?php echo $advisor['specialty']; ?></div>
                            
                            <div class="advisor-rating">
                                <?php for ($i = 0; $i < 5; $i++): ?>
                                    <i class="fas fa-star<?php echo ($i < floor($advisor['rating']) ? '' : '-o'); ?>"></i>
                                <?php endfor; ?>
                                <span class="rating-value"><?php echo number_format($advisor['rating'], 1); ?></span>
                            </div>
                            
                            <div class="advisor-languages">
                                <?php echo implode(', ', $advisor['languages']); ?>
                            </div>
                            
                            <div class="advisor-action">
                                <button class="schedule-btn" onclick="openScheduleModal(<?php echo $advisor['id']; ?>)">
                                    <i class="fas fa-calendar-check"></i> Schedule a Meeting
                                </button>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
            
            <!-- Legal Resources Section -->
            <div class="financial-section">
                <div class="section-title">
                    <i class="fas fa-gavel"></i>
                    <h2>Legal Resources</h2>
                </div>
                
                <div class="legal-resources-list">
                    <?php foreach ($legal_resources as $resource): ?>
                        <div class="legal-resource-item">
                            <i class="<?php echo $resource['icon']; ?> resource-icon"></i>
                            <div class="resource-content">
                                <div class="resource-title"><?php echo $resource['title']; ?></div>
                                <div class="resource-description"><?php echo $resource['description']; ?></div>
                            </div>
                            <a href="<?php echo $resource['link']; ?>" class="resource-link">
                                <i class="fas fa-arrow-right"></i>
                            </a>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
            
            <!-- Saved Calculations Section -->
            <div class="financial-section">
                <div class="section-title">
                    <i class="fas fa-save"></i>
                    <h2>Your Saved Calculations</h2>
                </div>
                
                <div class="saved-calculations-list">
                    <?php if (empty($saved_calculations)): ?>
                        <p class="no-saved-calculations">You have no saved calculations. Use the calculators above to create some.</p>
                    <?php else: ?>
                        <?php foreach ($saved_calculations as $calculation): ?>
                            <div class="saved-calculation-item">
                                <div class="calculation-info">
                                    <div class="calculation-type"><?php echo ucfirst($calculation['calculation_type']); ?></div>
                                    <div class="calculation-date"><?php echo date('F j, Y', strtotime($calculation['created_at'])); ?></div>
                                </div>
                                <div class="calculation-result">
                                    <?php echo $calculation['calculation_result']; ?>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </main>
    
    <!-- Schedule Meeting Modal -->
    <div class="modal" id="scheduleMeetingModal">
        <div class="modal-content">
            <span class="close" onclick="closeModal('scheduleMeetingModal')">&times;</span>
            <h2>Schedule a Meeting with <span id="advisorName"></span></h2>
            
            <form id="scheduleForm">
                <div class="form-group">
                    <label for="meeting_date">Select Date</label>
                    <input type="date" id="meeting_date" name="meeting_date" class="form-control" required>
                </div>
                
                <div class="form-group">
                    <label for="meeting_time">Select Time</label>
                    <input type="time" id="meeting_time" name="meeting_time" class="form-control" required>
                </div>
                
                <div class="form-group">
                    <label for="user_message">Your Message (optional)</label>
                    <textarea id="user_message" name="user_message" class="form-control" rows="3"></textarea>
                </div>
                
                <input type="hidden" id="advisorId" name="advisor_id">
                
                <button type="submit" class="schedule-btn">
                    <i class="fas fa-paper-plane"></i> Send Request
                </button>
            </form>
        </div>
    </div>
    
    <!-- Footer -->
    <div class="footer">
        <div class="footer-content">
            <p>&copy; 2023 Elderly Care. All rights reserved.</p>
        </div>
    </div>
    
    <!-- Scripts -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.6.0/jquery.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/js/all.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js@3.7.0/dist/chart.min.js"></script>
    <script>
        // Initialize charts when calculation results are available
        document.addEventListener('DOMContentLoaded', function() {
            // Retirement chart
            <?php if (isset($calculator_results['retirement'])): ?>
            const retirementCtx = document.getElementById('retirementChart').getContext('2d');
            const retirementChart = new Chart(retirementCtx, {
                type: 'bar',
                data: {
                    labels: ['Current Savings', 'Required for Retirement', 'Shortfall'],
                    datasets: [{
                        label: 'Amount (৳)',
                        data: [
                            <?php echo $calculator_results['retirement']['future_savings_value']; ?>,
                            <?php echo $calculator_results['retirement']['required_fund']; ?>,
                            <?php echo max(0, $calculator_results['retirement']['shortfall']); ?>
                        ],
                        backgroundColor: [
                            'rgba(75, 192, 192, 0.6)',
                            'rgba(54, 162, 235, 0.6)',
                            'rgba(255, 99, 132, 0.6)'
                        ],
                        borderColor: [
                            'rgb(75, 192, 192)',
                            'rgb(54, 162, 235)',
                            'rgb(255, 99, 132)'
                        ],
                        borderWidth: 1
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    scales: {
                        y: {
                            beginAtZero: true,
                            ticks: {
                                callback: function(value) {
                                    return '৳' + value.toLocaleString();
                                }
                            }
                        }
                    }
                }
            });
            <?php endif; ?>
            
            // Budget chart
            <?php if (isset($calculator_results['budget'])): ?>
            const budgetCtx = document.getElementById('budgetChart').getContext('2d');
            const budgetChart = new Chart(budgetCtx, {
                type: 'pie',
                data: {
                    labels: ['Housing', 'Utilities', 'Food', 'Healthcare', 'Transportation', 'Entertainment', 'Other'],
                    datasets: [{
                        label: 'Expenses',
                 
                        data: [
                            <?php echo $budget_data['expenses']['housing']; ?>,
                            <?php echo $budget_data['expenses']['utilities']; ?>,
                            <?php echo $budget_data['expenses']['food']; ?>,
                            <?php echo $budget_data['expenses']['healthcare']; ?>, 
                            <?php echo $budget_data['expenses']['transportation']; ?>,
                            <?php echo $budget_data['expenses']['entertainment']; ?>,
                            <?php echo $budget_data['expenses']['other']; ?>
                        ],
                        backgroundColor: [
                            'rgba(255, 99, 132, 0.6)',
                            'rgba(54, 162, 235, 0.6)',
                            'rgba(255, 205, 86, 0.6)',
                            'rgba(75, 192, 192, 0.6)',
                            'rgba(153, 102, 255, 0.6)',
                            'rgba(255, 159, 64, 0.6)',
                            'rgba(199, 199, 199, 0.6)'
                        ],
                        borderColor: [
                            'rgb(255, 99, 132)',
                            'rgb(54, 162, 235)',
                            'rgb(255, 205, 86)',
                            'rgb(75, 192, 192)',
                            'rgb(153, 102, 255)',
                            'rgb(255, 159, 64)',
                            'rgb(199, 199, 199)'
                        ],
                        borderWidth: 1
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: {
                            position: 'bottom'
                        },
                        tooltip: {
                            callbacks: {
                                label: function(context) {
                                    return context.label + ': ৳' + context.parsed.toLocaleString();
                                }
                            }
                        }
                    }
                }
            });
            <?php endif; ?>
            
            // Medical expenses chart
            <?php if (isset($calculator_results['medical'])): ?>
            const medicalCtx = document.getElementById('medicalExpensesChart').getContext('2d');
            const medicalChart = new Chart(medicalCtx, {
                type: 'doughnut',
                data: {
                    labels: ['Medication', 'Doctor Visits', 'Insurance', 'Hospital Costs', 'Other'],
                    datasets: [{
                        label: 'Annual Medical Expenses',
                        data: [
                            <?php echo $calculator_results['medical']['annual_medication']; ?>,
                            <?php echo $calculator_results['medical']['annual_doctor_visits']; ?>,
                            <?php echo $calculator_results['medical']['annual_insurance']; ?>,
                            <?php echo $calculator_results['medical']['hospital_costs']; ?>,
                            <?php echo $calculator_results['medical']['annual_other']; ?>
                        ],
                        backgroundColor: [
                            'rgba(255, 99, 132, 0.6)',
                            'rgba(54, 162, 235, 0.6)',
                            'rgba(255, 205, 86, 0.6)',
                            'rgba(75, 192, 192, 0.6)',
                            'rgba(153, 102, 255, 0.6)'
                        ],
                        borderColor: [
                            'rgb(255, 99, 132)',
                            'rgb(54, 162, 235)',
                            'rgb(255, 205, 86)',
                            'rgb(75, 192, 192)',
                            'rgb(153, 102, 255)'
                        ],
                        borderWidth: 1
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: {
                            position: 'bottom'
                        },
                        tooltip: {
                            callbacks: {
                                label: function(context) {
                                    return context.label + ': ৳' + context.parsed.toLocaleString();
                                }
                            }
                        }
                    }
                }
            });
            <?php endif; ?>
        });
        
        // Calculator tab functionality
        function openCalculator(calculatorName) {
            // Hide all calculator panels
            const panels = document.querySelectorAll('.calculator-panel');
            panels.forEach(panel => panel.classList.remove('active'));
            
            // Remove active class from all tabs
            const tabs = document.querySelectorAll('.calculator-tab');
            tabs.forEach(tab => tab.classList.remove('active'));
            
            // Show selected panel
            document.getElementById(calculatorName + '-calculator').classList.add('active');
            
            // Add active class to selected tab
            event.target.classList.add('active');
        }
        
        // AI Assistant functionality
        function startAIAssistant() {
            // Show voice feedback dialog
            const voiceFeedback = document.getElementById('voiceFeedback');
            voiceFeedback.style.display = 'block';
            
            // Check if browser supports speech recognition
            if ('webkitSpeechRecognition' in window) {
                const recognition = new webkitSpeechRecognition();
                recognition.continuous = false;
                recognition.interimResults = false;
                recognition.lang = 'en-US';
                
                recognition.onstart = function() {
                    document.getElementById('voiceResult').textContent = 'Listening...';
                };
                
                recognition.onresult = function(event) {
                    const transcript = event.results[0][0].transcript;
                    document.getElementById('voiceResult').textContent = 'You said: ' + transcript;
                    
                    // Process the voice command
                    processVoiceCommand(transcript);
                };
                
                recognition.onerror = function(event) {
                    document.getElementById('voiceResult').textContent = 'Error occurred: ' + event.error;
                };
                
                recognition.onend = function() {
                    setTimeout(() => {
                        voiceFeedback.style.display = 'none';
                    }, 3000);
                };
                
                recognition.start();
            } else {
                document.getElementById('voiceResult').textContent = 'Speech recognition not supported in this browser.';
                setTimeout(() => {
                    voiceFeedback.style.display = 'none';
                }, 3000);
            }
        }
        
        // Process voice commands
        function processVoiceCommand(command) {
            const lowerCommand = command.toLowerCase();
            
            if (lowerCommand.includes('retirement') || lowerCommand.includes('pension')) {
                openCalculator('retirement');
                speakResponse('Opening retirement planning calculator');
            } else if (lowerCommand.includes('budget') || lowerCommand.includes('expense')) {
                openCalculator('budget');
                speakResponse('Opening monthly budget planner');
            } else if (lowerCommand.includes('medical') || lowerCommand.includes('health')) {
                openCalculator('medical');
                speakResponse('Opening medical expenses calculator');
            } else if (lowerCommand.includes('advisor') || lowerCommand.includes('help')) {
                document.querySelector('[href="#financial-advisors"]').scrollIntoView();
                speakResponse('Showing financial advisors');
            } else {
                speakResponse('I can help you with retirement planning, budgeting, medical expenses, or connecting with financial advisors. What would you like to know?');
            }
        }
        
        // Text-to-speech function
        function speakResponse(text) {
            if ('speechSynthesis' in window) {
                const utterance = new SpeechSynthesisUtterance(text);
                utterance.lang = 'en-US';
                utterance.rate = 0.8;
                speechSynthesis.speak(utterance);
            }
        }
        
        // Schedule meeting modal functionality
        function openScheduleModal(advisorId) {
            const modal = document.getElementById('scheduleMeetingModal');
            const advisorIdInput = document.getElementById('advisorId');
            const advisorNameSpan = document.getElementById('advisorName');
            
            // Find advisor name
            const advisors = <?php echo json_encode($financial_advisors); ?>;
            const advisor = advisors.find(a => a.id === advisorId);
            
            if (advisor) {
                advisorNameSpan.textContent = advisor.name;
                advisorIdInput.value = advisorId;
                modal.style.display = 'block';
                
                // Set minimum date to today
                const today = new Date().toISOString().split('T')[0];
                document.getElementById('meeting_date').min = today;
            }
        }
        
        function closeModal(modalId) {
            document.getElementById(modalId).style.display = 'none';
        }
        
        // Handle schedule form submission
        document.getElementById('scheduleForm').addEventListener('submit', function(e) {
            e.preventDefault();
            
            // Get form data
            const formData = new FormData(e.target);
            const meetingData = {
                advisor_id: formData.get('advisor_id'),
                meeting_date: formData.get('meeting_date'),
                meeting_time: formData.get('meeting_time'),
                user_message: formData.get('user_message')
            };
            
            // Here you would typically send this data to your backend
            console.log('Meeting request:', meetingData);
            
            // Show success message
            alert('Meeting request sent successfully! The advisor will contact you soon.');
            
            // Close modal
            closeModal('scheduleMeetingModal');
            
            // Reset form
            e.target.reset();
        });
        
        // Close modal when clicking outside
        window.onclick = function(event) {
            const modal = document.getElementById('scheduleMeetingModal');
            if (event.target === modal) {
                modal.style.display = 'none';
            }
        }
        
        // Voice assistant button functionality
        function toggleVoiceAssistant() {
            startAIAssistant();
        }
        
        // Emergency button functionality
        function triggerEmergency() {
            if (confirm('This will send an emergency alert to your emergency contacts. Are you sure?')) {
                // Here you would typically send emergency alert
                alert('Emergency alert sent to your contacts!');
                
                // You could also call emergency services API
                console.log('Emergency alert triggered');
            }
        }
        
        // Accessibility functions
        function toggleTextSize() {
            document.body.classList.toggle('large-text');
            localStorage.setItem('large-text', document.body.classList.contains('large-text'));
        }
        
        function toggleHighContrast() {
            document.body.classList.toggle('high-contrast');
            localStorage.setItem('high-contrast', document.body.classList.contains('high-contrast'));
        }
        
        // Load saved accessibility preferences
        document.addEventListener('DOMContentLoaded', function() {
            if (localStorage.getItem('large-text') === 'true') {
                document.body.classList.add('large-text');
            }
            if (localStorage.getItem('high-contrast') === 'true') {
                document.body.classList.add('high-contrast');
            }
        });
        
        // Form validation for calculators
        function validateRetirementForm() {
            const currentAge = parseInt(document.getElementById('current_age').value);
            const retirementAge = parseInt(document.getElementById('retirement_age').value);
            
            if (currentAge >= retirementAge) {
                alert('Retirement age must be greater than current age');
                return false;
            }
            
            return true;
        }
        
        // Add event listeners for form validation
        document.querySelector('form[method="post"]').addEventListener('submit', function(e) {
            if (e.target.querySelector('[name="calculate_retirement"]')) {
                if (!validateRetirementForm()) {
                    e.preventDefault();
                }
            }
        });
        
        // Auto-save form data to localStorage
        function saveFormData(formId) {
            const form = document.getElementById(formId);
            if (form) {
                const inputs = form.querySelectorAll('input, select, textarea');
                inputs.forEach(input => {
                    input.addEventListener('change', function() {
                        localStorage.setItem(formId + '_' + this.name, this.value);
                    });
                });
            }
        }
        
        // Load saved form data
        function loadFormData(formId) {
            const form = document.getElementById(formId);
            if (form) {
                const inputs = form.querySelectorAll('input, select, textarea');
                inputs.forEach(input => {
                    const savedValue = localStorage.getItem(formId + '_' + input.name);
                    if (savedValue) {
                        input.value = savedValue;
                    }
                });
            }
        }
        
        // Initialize form auto-save
        document.addEventListener('DOMContentLoaded', function() {
            // Add auto-save to all calculator forms
            const forms = document.querySelectorAll('.calculator-form');
            forms.forEach((form, index) => {
                const formId = 'calculator_form_' + index;
                form.id = formId;
                saveFormData(formId);
                loadFormData(formId);
            });
        });
        
        // Print functionality for results
        function printResults(calculatorType) {
            const resultContainer = document.querySelector('#' + calculatorType + '-calculator .calculator-result');
            if (resultContainer) {
                const printWindow = window.open('', '_blank');
                printWindow.document.write(`
                    <html>
                        <head>
                            <title>${calculatorType.charAt(0).toUpperCase() + calculatorType.slice(1)} Calculator Results</title>
                            <style>
                                body { font-family: Arial, sans-serif; margin: 20px; }
                                .result-item { display: flex; justify-content: space-between; padding: 10px 0; border-bottom: 1px solid #eee; }
                                .result-label { font-weight: 500; }
                                .result-value { font-weight: 600; }
                                .result-total { font-weight: 700; font-size: 1.2em; border-top: 2px solid #333; margin-top: 10px; padding-top: 10px; }
                                @media print { body { margin: 0; } }
                            </style>
                        </head>
                        <body>
                            <h1>${calculatorType.charAt(0).toUpperCase() + calculatorType.slice(1)} Calculator Results</h1>
                            <p>Generated on: ${new Date().toLocaleDateString()}</p>
                            ${resultContainer.innerHTML}
                        </body>
                    </html>
                `);
                printWindow.document.close();
                printWindow.focus();
                printWindow.print();
                printWindow.close();
            }
        }
        
        // Export results to CSV
        function exportToCSV(calculatorType) {
            const resultItems = document.querySelectorAll('#' + calculatorType + '-calculator .result-item');
            if (resultItems.length === 0) return;
            
            let csvContent = "Label,Value\n";
            resultItems.forEach(item => {
                const label = item.querySelector('.result-label').textContent.trim();
                const value = item.querySelector('.result-value').textContent.trim();
                csvContent += `"${label}","${value}"\n`;
            });
            
            const blob = new Blob([csvContent], { type: 'text/csv;charset=utf-8;' });
            const link = document.createElement('a');
            const url = URL.createObjectURL(blob);
            link.setAttribute('href', url);
            link.setAttribute('download', calculatorType + '_results.csv');
            link.style.visibility = 'hidden';
            document.body.appendChild(link);
            link.click();
            document.body.removeChild(link);
        }
        
        // Add print and export buttons to result containers
        document.addEventListener('DOMContentLoaded', function() {
            const resultContainers = document.querySelectorAll('.calculator-result');
            resultContainers.forEach(container => {
                const calculatorPanel = container.closest('.calculator-panel');
                const calculatorType = calculatorPanel.id.replace('-calculator', '');
                
                const actionButtons = document.createElement('div');
                actionButtons.className = 'result-actions';
                actionButtons.style.marginTop = '1rem';
                actionButtons.innerHTML = `
                    <button onclick="printResults('${calculatorType}')" class="calculator-button" style="margin-right: 1rem;">
                        <i class="fas fa-print"></i> Print Results
                    </button>
                    <button onclick="exportToCSV('${calculatorType}')" class="calculator-button">
                        <i class="fas fa-download"></i> Export CSV
                    </button>
                `;
                container.appendChild(actionButtons);
            });
        });
    </script>
    
    <!-- Accessibility Panel -->
    <div class="accessibility-panel">
        <button class="accessibility-btn" onclick="toggleTextSize()" title="Toggle Large Text">
            <i class="fas fa-font"></i>
        </button>
        <button class="accessibility-btn" onclick="toggleHighContrast()" title="Toggle High Contrast">
            <i class="fas fa-adjust"></i>
        </button>
        <button class="accessibility-btn" onclick="toggleVoiceAssistant()" title="Voice Assistant">
            <i class="fas fa-microphone"></i>
        </button>
    </div>
    
    <!-- Voice Assistant Button -->
    <button class="voice-assistant" onclick="toggleVoiceAssistant()" title="Voice Assistant">
        <i class="fas fa-microphone"></i>
    </button>
    
    <!-- Emergency Button -->
    <button class="emergency-button" onclick="triggerEmergency()" title="Emergency Alert">
        <svg viewBox="0 0 24 24" fill="currentColor">
            <path d="M12,2A10,10 0 0,0 2,12A10,10 0 0,0 12,22A10,10 0 0,0 22,12A10,10 0 0,0 12,2M12,17A5,5 0 0,1 7,12A5,5 0 0,1 12,7A5,5 0 0,1 17,12A5,5 0 0,1 12,17M12,9A3,3 0 0,0 9,12A3,3 0 0,0 12,15A3,3 0 0,0 15,12A3,3 0 0,0 12,9Z"/>
        </svg>
    </button>
    
    <style>
        /* Additional styles for the completed features */
        .legal-resources-list {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(350px, 1fr));
            gap: 1.5rem;
            margin-top: 1.5rem;
        }
        
        .legal-resource-item {
            background: #f8f9fa;
            border-radius: 12px;
            padding: 1.5rem;
            display: flex;
            align-items: center;
            transition: all 0.3s ease;
            border: 1px solid #e0e0e0;
        }
        
        .legal-resource-item:hover {
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.1);
            transform: translateY(-2px);
        }
        
        .resource-icon {
            font-size: 2rem;
            color: #0096ad;
            margin-right: 1rem;
            flex-shrink: 0;
        }
        
        .resource-content {
            flex: 1;
        }
        
        .resource-title {
            font-family: 'Montserrat', sans-serif;
            font-weight: 600;
            font-size: 1.2rem;
            color: #2d3748;
            margin-bottom: 0.5rem;
        }
        
        .resource-description {
            color: #4a5568;
            font-size: 0.95rem;
        }
        
        .resource-link {
            color: #0096ad;
            font-size: 1.2rem;
            margin-left: 1rem;
            flex-shrink: 0;
        }
        
        .resource-link:hover {
            color: #00809e;
        }
        
        .saved-calculations-list {
            margin-top: 1.5rem;
        }
        
        .no-saved-calculations {
            text-align: center;
            color: #718096;
            font-style: italic;
            padding: 2rem;
            background: #f8f9fa;
            border-radius: 12px;
        }
        
        .saved-calculation-item {
            background: #f8f9fa;
            border-radius: 12px;
            padding: 1.5rem;
            margin-bottom: 1rem;
            border: 1px solid #e0e0e0;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        
        .calculation-info {
            display: flex;
            flex-direction: column;
        }
        
        .calculation-type {
            font-family: 'Montserrat', sans-serif;
            font-weight: 600;
            color: #0096ad;
            font-size: 1.1rem;
        }
        
        .calculation-date {
            color: #718096;
            font-size: 0.9rem;
            margin-top: 0.3rem;
        }
        
        .calculation-result {
            color: #2d3748;
            font-weight: 500;
            font-size: 0.9rem;
            max-width: 300px;
            text-align: right;
        }
        
        /* Modal styles */
        .modal {
            display: none;
            position: fixed;
            z-index: 1000;
            left: 0;
            top: 0;
            width: 100%;
            height: 100%;
            overflow: auto;
            background-color: rgba(0, 0, 0, 0.4);
        }
        
        .modal-content {
            background-color: #fefefe;
            margin: 5% auto;
            padding: 2rem;
            border-radius: 15px;
            width: 90%;
            max-width: 500px;
            position: relative;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.3);
        }
        
        .close {
            color: #aaa;
            float: right;
            font-size: 28px;
            font-weight: bold;
            position: absolute;
            right: 1rem;
            top: 1rem;
            cursor: pointer;
        }
        
        .close:hover,
        .close:focus {
            color: #000;
            text-decoration: none;
        }
        
        .modal h2 {
            font-family: 'Montserrat', sans-serif;
            color: #2d3748;
            margin-bottom: 1.5rem;
            margin-top: 0;
        }
        
        .result-actions {
            display: flex;
            gap: 1rem;
            flex-wrap: wrap;
        }
        
        .result-actions .calculator-button {
            background: #4a5568;
            font-size: 0.9rem;
            padding: 0.6rem 1.2rem;
        }
        
        .result-actions .calculator-button:hover {
            background: #2d3748;
        }
        
        /* Large text mode adjustments */
        body.large-text .financial-header h1 {
            font-size: 3rem;
        }
        
        body.large-text .section-title h2 {
            font-size: 2.2rem;
        }
        
        body.large-text .calculator-title {
            font-size: 1.6rem;
        }
        
        body.large-text .tool-title {
            font-size: 1.5rem;
        }
        
        body.large-text .form-control {
            font-size: 1.2rem;
            padding: 1rem 1.2rem;
        }
        
        body.large-text .calculator-button {
            font-size: 1.2rem;
            padding: 1rem 2.5rem;
        }
        
        /* Mobile responsiveness for new elements */
        @media (max-width: 768px) {
            .legal-resources-list {
                grid-template-columns: 1fr;
            }
            
            .legal-resource-item {
                flex-direction: column;
                text-align: center;
            }
            
            .resource-icon {
                margin-right: 0;
                margin-bottom: 1rem;
            }
            
            .resource-link {
                margin-left: 0;
                margin-top: 1rem;
            }
            
            .saved-calculation-item {
                flex-direction: column;
                align-items: flex-start;
            }
            
            .calculation-result {
                text-align: left;
                max-width: 100%;
                margin-top: 1rem;
            }
            
            .result-actions {
                flex-direction: column;
            }
            
            .result-actions .calculator-button {
                width: 100%;
                text-align: center;
            }
            
            .modal-content {
                margin: 10% auto;
                width: 95%;
                padding: 1.5rem;
            }
        }
    </style>
</body>
</html>
                        

