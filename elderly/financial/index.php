<?php
session_start();

// Include required files
require_once '../../login_user/functions.php';
require_once '../../signup_user/config.php';

// Authentication checks
if (!isLoggedIn()) {
    header("Location: ../../login_user/login_user.php");
    exit();
}

// Role checking
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

// Process form submissions
$success_message = '';
$error_message = '';

// Handle save calculation
if(isset($_POST['save_calculation'])) {
    $calculation_type = $_POST['calculation_type'];
    $calculation_name = $_POST['calculation_name'] ?? null;
    $result_data = $_POST['result_data'];
    $input_data = $_POST['input_data'];
    
    $stmt = $conn->prepare("INSERT INTO financial_calculations (user_id, calculation_type, calculation_name, calculation_result, result_data, calculation_inputs, input_data, created_at) VALUES (?, ?, ?, ?, ?, ?, ?, NOW())");
    $stmt->bind_param("issssss", $user_id, $calculation_type, $calculation_name, $result_data, $result_data, $input_data, $input_data);
    
    if($stmt->execute()) {
        $success_message = "Calculation saved successfully!";
    } else {
        $error_message = "Failed to save calculation: " . $conn->error;
    }
}

// Handle delete calculation
if(isset($_GET['delete_calculation'])) {
    $calculation_id = intval($_GET['delete_calculation']);
    
    $stmt = $conn->prepare("DELETE FROM financial_calculations WHERE id = ? AND user_id = ?");
    $stmt->bind_param("ii", $calculation_id, $user_id);
    
    if($stmt->execute()) {
        $success_message = "Calculation deleted successfully!";
    } else {
        $error_message = "Failed to delete calculation: " . $conn->error;
    }
}

// Get saved calculations for the user
$saved_calculations = [];
$stmt = $conn->prepare("SELECT * FROM financial_calculations WHERE user_id = ? ORDER BY created_at DESC LIMIT 10");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();

while($row = $result->fetch_assoc()) {
    $saved_calculations[] = $row;
}

// Get financial advisors
$advisors = [];
$advisor_query = "SELECT * FROM financial_advisors WHERE available = 1 LIMIT 4";
$advisor_result = $conn->query($advisor_query);

if($advisor_result && $advisor_result->num_rows > 0) {
    while($row = $advisor_result->fetch_assoc()) {
        $advisors[] = $row;
    }
}

// Financial Calculation Functions
function calculateFixedDeposit($principal, $interest_rate, $years) {
    $interest_rate = $interest_rate / 100; // Convert percentage to decimal
    $maturity_amount = $principal * pow((1 + $interest_rate), $years);
    $interest_earned = $maturity_amount - $principal;
    
    return [
        "principal" => number_format($principal, 2),
        "interest_rate" => number_format($interest_rate * 100, 2),
        "term" => $years,
        "maturity_amount" => number_format($maturity_amount, 2),
        "interest_earned" => number_format($interest_earned, 2)
    ];
}

function calculateRetirement($current_age, $retirement_age, $life_expectancy, $monthly_social_security, $monthly_pension, $monthly_expenses, $savings, $annual_inflation, $investment_return) {
    $years_to_retirement = $retirement_age - $current_age;
    $retirement_duration = $life_expectancy - $retirement_age;
    
    // Convert percentages to decimals
    $annual_inflation = $annual_inflation / 100;
    $investment_return = $investment_return / 100;
    
    // Real rate of return adjusted for inflation
    $real_rate = (1 + $investment_return) / (1 + $annual_inflation) - 1;
    
    // Monthly income from Social Security and pension
    $monthly_income = $monthly_social_security + $monthly_pension;
    
    // Monthly balance (surplus or deficit)
    $monthly_balance = $monthly_income - $monthly_expenses;
    
    // Calculate if savings will last through retirement
    $adjusted_savings = $savings * pow(1 + $investment_return, $years_to_retirement);
    $monthly_withdrawal = -$monthly_balance > 0 ? -$monthly_balance : 0;
    
    // Calculate how long savings will last
    $savings_depleted_months = 0;
    $remaining_savings = $adjusted_savings;
    $monthly_real_return = pow(1 + $real_rate, 1/12) - 1;
    
    while($remaining_savings > 0 && $savings_depleted_months < $retirement_duration * 12) {
        $remaining_savings = $remaining_savings * (1 + $monthly_real_return) - $monthly_withdrawal;
        $savings_depleted_months++;
    }
    
    $savings_depleted_years = floor($savings_depleted_months / 12);
    $savings_depleted_age = $retirement_age + $savings_depleted_years;
    
    if($remaining_savings > 0) {
        $savings_depleted_age = "Never (funds remain)";
    }
    
    return [
        "monthly_income" => number_format($monthly_income, 2),
        "monthly_balance" => number_format($monthly_balance, 2),
        "retirement_years" => $retirement_duration,
        "savings_depleted" => $savings_depleted_age,
        "savings_projection" => calculateSavingsProjection($adjusted_savings, $monthly_balance, $real_rate, $retirement_duration)
    ];
}

function calculateSavingsProjection($initial_savings, $monthly_delta, $real_rate, $years) {
    $monthly_real_return = pow(1 + $real_rate, 1/12) - 1;
    $projection = [];
    $savings = $initial_savings;
    
    for ($i = 0; $i < $years; $i++) {
        $projection[] = max(0, round($savings));
        
        // Monthly changes for a year
        for ($month = 0; $month < 12; $month++) {
            $savings = $savings * (1 + $monthly_real_return) + $monthly_delta;
            if($savings <= 0) {
                $savings = 0;
                break;
            }
        }
    }
    
    return $projection;
}

function calculateMedicalDeduction($medical_expenses, $agi) {
    $threshold = $agi * 0.075; // 7.5% of AGI
    $potential_deduction = max(0, $medical_expenses - $threshold);
    $tax_savings = $potential_deduction * 0.12; // Assuming 12% tax bracket
    
    return [
        "total_medical_expenses" => number_format($medical_expenses, 2),
        "agi_threshold" => number_format($threshold, 2),
        "potential_deduction" => number_format($potential_deduction, 2),
        "tax_savings" => number_format($tax_savings, 2)
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
    
    <!-- Additional calculator libraries -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js@3.7.1/dist/chart.min.js"></script>
    
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
        
        /* Calculator UI Elements */
        .calculator-container {
            background: #f8f9fa;
            border-radius: 15px;
            padding: 2rem;
            margin-top: 1.5rem;
        }
        
        .calculator-header {
            margin-bottom: 1.5rem;
            text-align: center;
        }
        
        .calculator-header h3 {
            font-family: 'Montserrat', sans-serif;
            font-weight: 600;
            font-size: 1.5rem;
            color: #2d3748;
            margin-bottom: 0.5rem;
        }
        
        .calculator-header p {
            font-size: 1rem;
            color: #718096;
        }
        
        .calculator-form {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(250px, 1fr));
            gap: 1.2rem;
            margin-bottom: 1.5rem;
        }
        
        .input-group {
            display: flex;
            flex-direction: column;
        }
        
        .input-group label {
            font-weight: 600;
            margin-bottom: 0.5rem;
            font-size: 1rem;
            color: #4a5568;
        }
        
        .input-group input, 
        .input-group select {
            padding: 0.8rem;
            border: 1px solid #cbd5e0;
            border-radius: 10px;
            font-size: 1.1rem;
        }
        
        .input-group input:focus, 
        .input-group select:focus {
            outline: none;
            border-color: #0096ad;
            box-shadow: 0 0 0 3px rgba(0, 150, 173, 0.1);
        }
        
        .input-addon {
            position: relative;
        }
        
        .input-addon input {
            padding-left: 2.5rem;
        }
        
        .input-addon::before {
            content: "$";
            position: absolute;
            left: 1rem;
            top: 50%;
            transform: translateY(-50%);
            font-weight: 600;
            color: #718096;
        }
        
        .calculator-actions {
            display: flex;
            justify-content: center;
            gap: 1rem;
            margin: 2rem 0;
        }
        
        .calc-btn {
            padding: 0.8rem 2rem;
            border-radius: 30px;
            font-size: 1.1rem;
            font-weight: 600;
            border: none;
            cursor: pointer;
            transition: all 0.3s ease;
        }
        
        .calc-primary {
            background: #0096ad;
            color: white;
        }
        
        .calc-primary:hover {
            background: #00809e;
        }
        
        .calc-secondary {
            background: #e2e8f0;
            color: #4a5568;
        }
        
        .calc-secondary:hover {
            background: #cbd5e0;
        }
        
        .result-container {
            background: white;
            border-radius: 15px;
            padding: 1.5rem;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.05);
            margin-top: 1.5rem;
            display: none;
        }
        
        .result-title {
            font-family: 'Montserrat', sans-serif;
            font-weight: 600;
            font-size: 1.3rem;
            color: #2d3748;
            margin-bottom: 1rem;
            padding-bottom: 0.5rem;
            border-bottom: 2px solid #0096ad;
        }
        
        .result-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(200px, 1fr));
            gap: 1rem;
        }
        
        .result-item {
            background: #f8f9fa;
            border-radius: 12px;
            padding: 1rem;
            text-align: center;
        }
        
        .result-label {
            font-size: 0.9rem;
            color: #718096;
            margin-bottom: 0.5rem;
        }
        
        .result-value {
            font-family: 'Montserrat', sans-serif;
            font-weight: 700;
            font-size: 1.5rem;
            color: #0096ad;
        }
        
        .result-chart-container {
            width: 100%;
            height: 300px;
            margin-top: 2rem;
        }
        
        /* Save calculation form */
        .save-calculation-form {
            margin-top: 1.5rem;
            padding: 1.5rem;
            background: #f8fafc;
            border-radius: 10px;
            border: 1px dashed #cbd5e0;
        }
        
        .save-input-group {
            margin-bottom: 1rem;
        }
        
        .save-input-group label {
            display: block;
            font-weight: 600;
            margin-bottom: 0.5rem;
            color: #4a5568;
        }
        
        .save-input-group input {
            width: 100%;
            padding: 0.8rem;
            border: 1px solid #cbd5e0;
            border-radius: 8px;
        }
        
        /* Saved Calculations Table */
        .saved-calculations {
            margin-top: 2rem;
        }
        
        .saved-calculations-table {
            width: 100%;
            border-collapse: collapse;
        }
        
        .saved-calculations-table th,
        .saved-calculations-table td {
            padding: 1rem;
            text-align: left;
            border-bottom: 1px solid #e2e8f0;
        }
        
        .saved-calculations-table th {
            font-weight: 600;
            color: #4a5568;
            background: #f8fafc;
        }
        
        .saved-calculations-table tr:hover {
            background: #f7fafc;
        }
        
        .saved-calculations-table .actions {
            display: flex;
            gap: 0.5rem;
        }
        
        .saved-calculations-table .btn {
            padding: 0.5rem 1rem;
            border-radius: 6px;
            font-size: 0.9rem;
            font-weight: 500;
            cursor: pointer;
            border: none;
        }
        
        .btn-view {
            background: #3182ce;
            color: white;
        }
        
        .btn-delete {
            background: #e53e3e;
            color: white;
        }
        
        /* AI Assistant */
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

        /* Notifications */
        .notification {
            padding: 1rem;
            border-radius: 8px;
            margin-bottom: 1.5rem;
        }
        
        .notification-success {
            background-color: #e6fffa;
            border-left: 4px solid #38b2ac;
            color: #234e52;
        }
        
        .notification-error {
            background-color: #fff5f5;
            border-left: 4px solid #e53e3e;
            color: #742a2a;
        }

        /* Voice feedback dialog */
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
        
        /* Modal Window */
        .modal {
            display: none;
            position: fixed;
            z-index: 999;
            left: 0;
            top: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0,0,0,0.5);
        }

        .modal-content {
            background-color: #fff;
            margin: 10% auto;
            padding: 2rem;
            border-radius: 10px;
            max-width: 600px;
            box-shadow: 0 5px 15px rgba(0,0,0,0.2);
        }

        .close-modal {
            float: right;
            font-size: 1.8rem;
            font-weight: bold;
            cursor: pointer;
        }
        
        /* Responsive adjustments */
        @media (max-width: 768px) {
            .financial-container {
                padding: 1rem;
            }
            
            .calculator-form {
                grid-template-columns: 1fr;
            }
            
            .result-grid {
                grid-template-columns: 1fr;
            }
            
            .calculator-actions {
                flex-direction: column;
                align-items: center;
            }
            
            .ai-assistant-card {
                flex-direction: column;
                text-align: center;
                padding: 1.5rem;
            }
            
            .ai-assistant-icon {
                margin-right: 0;
                margin-bottom: 1rem;
            }
            
            .tools-grid {
                grid-template-columns: 1fr;
            }
        }
    </style>
</head>

<body>
    <?php include '../includes/nav.php'; ?>
    <?php include '../includes/mobile_nav.php'; ?>
    
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
            
            <!-- Notifications -->
            <?php if (!empty($success_message)): ?>
                <div class="notification notification-success">
                    <i class="fas fa-check-circle"></i> <?php echo $success_message; ?>
                </div>
            <?php endif; ?>
            
            <?php if (!empty($error_message)): ?>
                <div class="notification notification-error">
                    <i class="fas fa-exclamation-circle"></i> <?php echo $error_message; ?>
                </div>
            <?php endif; ?>
            
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
            
            <!-- Financial Tools Section -->
            <div class="financial-section">
                <div class="section-title">
                    <i class="fas fa-tools"></i>
                    <h2>Financial Tools</h2>
                </div>
                
                <div class="tools-grid">
                    <div class="tool-card">
                        <div class="tool-icon">
                            <i class="fas fa-calculator"></i>
                        </div>
                        <h3 class="tool-title">Monthly Budget Calculator</h3>
                        <p class="tool-description">Input your income sources and expenses to create a personalized monthly budget plan.</p>
                        <a href="#budget-planner" class="tool-link" onclick="scrollToCalculator('budget-planner')">
                            Get Started <i class="fas fa-arrow-right"></i>
                        </a>
                    </div>
                    
                    <div class="tool-card">
                        <div class="tool-icon">
                            <i class="fas fa-hand-holding-usd"></i>
                        </div>
                        <h3 class="tool-title">Fixed Deposit Calculator</h3>
                        <p class="tool-description">Calculate returns on your fixed deposit investments with different interest rates and time periods.</p>
                        <a href="#fd-calculator" class="tool-link" onclick="scrollToCalculator('fd-calculator')">
                            Get Started <i class="fas fa-arrow-right"></i>
                        </a>
                    </div>
                    
                    <div class="tool-card">
                        <div class="tool-icon">
                            <i class="fas fa-umbrella-beach"></i>
                        </div>
                        <h3 class="tool-title">Retirement Calculator</h3>
                        <p class="tool-description">Plan for your future by calculating how much you need to save for a comfortable retirement.</p>
                        <a href="#retirement-calculator" class="tool-link" onclick="scrollToCalculator('retirement-calculator')">
                            Get Started <i class="fas fa-arrow-right"></i>
                        </a>
                    </div>
                    
                    <div class="tool-card">
                        <div class="tool-icon">
                            <i class="fas fa-file-invoice-dollar"></i>
                        </div>
                        <h3 class="tool-title">Tax Deduction Calculator</h3>
                        <p class="tool-description">Calculate potential tax deductions from medical expenses and other eligible costs.</p>
                        <a href="#tax-calculator" class="tool-link" onclick="scrollToCalculator('tax-calculator')">
                            Get Started <i class="fas fa-arrow-right"></i>
                        </a>
                    </div>
                </div>
            </div>
            
            <!-- Fixed Deposit Calculator -->
            <div class="financial-section" id="fd-calculator">
                <div class="section-title">
                    <i class="fas fa-piggy-bank"></i>
                    <h2>Fixed Deposit Calculator</h2>
                </div>
                
                <p>Calculate the maturity amount and interest earned on your fixed deposit investment.</p>
                
                <div class="calculator-container">
                    <div class="calculator-header">
                        <h3>Calculate Your Fixed Deposit Returns</h3>
                        <p>Enter your investment details to see how your money will grow over time.</p>
                    </div>
                    
                    <div class="calculator-form">
                        <div class="input-group">
                            <label for="fd-principal">Principal Amount (৳)</label>
                            <input type="number" id="fd-principal" value="10000" min="1000">
                        </div>
                        
                        <div class="input-group">
                            <label for="fd-interest-rate">Interest Rate (% per year)</label>
                            <input type="number" id="fd-interest-rate" value="6" step="0.1" min="1" max="15">
                        </div>
                        
                        <div class="input-group">
                            <label for="fd-years">Time Period (Years)</label>
                            <input type="number" id="fd-years" value="5" min="1" max="30">
                        </div>
                    </div>
                    
                    <div class="calculator-actions">
                        <button class="calc-btn calc-primary" onclick="calculateFixedDeposit()">
                            <i class="fas fa-calculator"></i> Calculate
                        </button>
                        <button class="calc-btn calc-secondary" onclick="resetFixedDepositForm()">
                            <i class="fas fa-redo"></i> Reset
                        </button>
                    </div>
                    
                    <div class="result-container" id="fd-results">
                        <div class="result-title">Fixed Deposit Results</div>
                        
                        <div class="result-grid">
                            <div class="result-item">
                                <div class="result-label">Principal Amount</div>
                                <div class="result-value" id="fd-result-principal">৳10,000.00</div>
                            </div>
                            
                            <div class="result-item">
                                <div class="result-label">Interest Rate</div>
                                <div class="result-value" id="fd-result-rate">6.00%</div>
                            </div>
                            
                            <div class="result-item">
                                <div class="result-label">Time Period</div>
                                <div class="result-value" id="fd-result-years">5 Years</div>
                            </div>
                            
                            <div class="result-item">
                                <div class="result-label">Maturity Amount</div>
                                <div class="result-value" id="fd-result-maturity">৳13,382.26</div>
                            </div>
                            
                            <div class="result-item">
                                <div class="result-label">Interest Earned</div>
                                <div class="result-value" id="fd-result-interest">৳3,382.26</div>
                            </div>
                        </div>
                        
                        <div class="result-chart-container">
                            <canvas id="fd-chart"></canvas>
                        </div>
                        
                        <div class="save-calculation-form">
                            <form method="post" action="">
                                <div class="save-input-group">
                                    <label for="fd-calc-name">Give this calculation a name (optional):</label>
                                    <input type="text" id="fd-calc-name" name="calculation_name" placeholder="e.g., 5 Year FD Investment Plan">
                                </div>
                                <input type="hidden" name="calculation_type" value="fixed_deposit">
                                <input type="hidden" name="result_data" id="fd-result-data">
                                <input type="hidden" name="input_data" id="fd-input-data">
                                <button type="submit" name="save_calculation" class="calc-btn calc-primary">
                                    <i class="fas fa-save"></i> Save This Calculation
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Retirement Calculator -->
            <div class="financial-section" id="retirement-calculator">
                <div class="section-title">
                    <i class="fas fa-umbrella-beach"></i>
                    <h2>Retirement Calculator</h2>
                </div>
                
                <p>Plan your retirement by calculating if your savings will last through your retirement years.</p>
                
                <div class="calculator-container">
                    <div class="calculator-header">
                        <h3>Calculate Your Retirement Needs</h3>
                        <p>Enter your information to plan for a secure financial future.</p>
                    </div>
                    
                    <div class="calculator-form">
                        <div class="input-group">
                            <label for="retirement-current-age">Current Age</label>
                            <input type="number" id="retirement-current-age" value="60" min="40" max="85">
                        </div>
                        
                        <div class="input-group">
                            <label for="retirement-age">Retirement Age</label>
                            <input type="number" id="retirement-age" value="65" min="45" max="90">
                        </div>
                        
                        <div class="input-group">
                            <label for="retirement-life-expectancy">Life Expectancy</label>
                            <input type="number" id="retirement-life-expectancy" value="85" min="65" max="100">
                        </div>
                        
                        <div class="input-group input-addon">
                            <label for="retirement-social-security">Monthly Income from Pension (৳)</label>
                            <input type="number" id="retirement-social-security" value="15000">
                        </div>
                        
                        <div class="input-group input-addon">
                            <label for="retirement-pension">Other Monthly Income (৳)</label>
                            <input type="number" id="retirement-pension" value="5000">
                        </div>
                        
                        <div class="input-group input-addon">
                            <label for="retirement-expenses">Expected Monthly Expenses (৳)</label>
                            <input type="number" id="retirement-expenses" value="18000">
                        </div>
                        
                        <div class="input-group input-addon">
                            <label for="retirement-savings">Current Retirement Savings (৳)</label>
                            <input type="number" id="retirement-savings" value="1500000">
                        </div>
                        
                        <div class="input-group">
                            <label for="retirement-inflation">Expected Inflation Rate (%)</label>
                            <input type="number" id="retirement-inflation" value="5" step="0.1" min="1" max="10">
                        </div>
                        
                        <div class="input-group">
                            <label for="retirement-return">Expected Investment Return (%)</label>
                            <input type="number" id="retirement-return" value="8" step="0.1" min="1" max="15">
                        </div>
                    </div>
                    
                    <div class="calculator-actions">
                        <button class="calc-btn calc-primary" onclick="calculateRetirement()">
                            <i class="fas fa-calculator"></i> Calculate
                        </button>
                        <button class="calc-btn calc-secondary" onclick="resetRetirementForm()">
                            <i class="fas fa-redo"></i> Reset
                        </button>
                    </div>
                    
                    <div class="result-container" id="retirement-results">
                        <div class="result-title">Your Retirement Projection</div>
                        
                        <div class="result-grid">
                            <div class="result-item">
                                <div class="result-label">Monthly Income</div>
                                <div class="result-value" id="retirement-result-income">৳20,000</div>
                            </div>
                            
                            <div class="result-item">
                                <div class="result-label">Monthly Surplus/Deficit</div>
                                <div class="result-value" id="retirement-result-balance">৳2,000</div>
                            </div>
                            
                            <div class="result-item">
                                <div class="result-label">Years in Retirement</div>
                                <div class="result-value" id="retirement-result-years">20</div>
                            </div>
                            
                            <div class="result-item">
                                <div class="result-label">Savings Depleted At</div>
                                <div class="result-value" id="retirement-result-depleted">Age 92</div>
                            </div>
                        </div>
                        
                        <div class="result-chart-container">
                            <canvas id="retirement-chart"></canvas>
                        </div>
                        
                        <div class="save-calculation-form">
                            <form method="post" action="">
                                <div class="save-input-group">
                                    <label for="retirement-calc-name">Give this calculation a name (optional):</label>
                                    <input type="text" id="retirement-calc-name" name="calculation_name" placeholder="e.g., My Retirement Plan 2025">
                                </div>
                                <input type="hidden" name="calculation_type" value="retirement">
                                <input type="hidden" name="result_data" id="retirement-result-data">
                                <input type="hidden" name="input_data" id="retirement-input-data">
                                <button type="submit" name="save_calculation" class="calc-btn calc-primary">
                                    <i class="fas fa-save"></i> Save This Calculation
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Medical Tax Deduction Calculator -->
            <div class="financial-section" id="tax-calculator">
                <div class="section-title">
                    <i class="fas fa-file-medical-alt"></i>
                    <h2>Medical Expense Tax Deduction Calculator</h2>
                </div>
                
                <p>Estimate potential tax deductions from your medical expenses.</p>
                
                <div class="calculator-container">
                    <div class="calculator-header">
                        <h3>Calculate Medical Tax Deductions</h3>
                        <p>Enter your medical expenses and adjusted gross income (AGI) to see potential deductions.</p>
                    </div>
                    
                    <div class="calculator-form">
                        <div class="input-group input-addon">
                            <label for="tax-medical-expenses">Annual Medical Expenses (৳)</label>
                            <input type="number" id="tax-medical-expenses" value="50000">
                        </div>
                        
                        <div class="input-group input-addon">
                            <label for="tax-agi">Adjusted Gross Income (AGI) (৳)</label>
                            <input type="number" id="tax-agi" value="500000">
                        </div>
                    </div>
                    
                    <div class="calculator-actions">
                        <button class="calc-btn calc-primary" onclick="calculateMedicalDeduction()">
                            <i class="fas fa-calculator"></i> Calculate Deduction
                        </button>
                        <button class="calc-btn calc-secondary" onclick="resetMedicalDeductionForm()">
                            <i class="fas fa-redo"></i> Reset
                        </button>
                    </div>
                    
                    <div class="result-container" id="medical-deduction-results">
                        <div class="result-title">Medical Expense Tax Deduction</div>
                        
                        <div class="result-grid">
                            <div class="result-item">
                                <div class="result-label">Total Medical Expenses</div>
                                <div class="result-value" id="deduction-result-expenses">৳50,000.00</div>
                            </div>
                            
                            <div class="result-item">
                                <div class="result-label">7.5% of Your AGI</div>
                                <div class="result-value" id="deduction-result-threshold">৳37,500.00</div>
                            </div>
                            
                            <div class="result-item">
                                <div class="result-label">Potential Deduction</div>
                                <div class="result-value" id="deduction-result-deduction">৳12,500.00</div>
                            </div>
                            
                            <div class="result-item">
                                <div class="result-label">Estimated Tax Savings</div>
                                <div class="result-value" id="deduction-result-savings">৳1,500.00</div>
                            </div>
                        </div>
                        
                        <div class="result-chart-container">
                            <canvas id="deduction-chart"></canvas>
                        </div>
                        
                        <div class="save-calculation-form">
                            <form method="post" action="">
                                <div class="save-input-group">
                                    <label for="tax-calc-name">Give this calculation a name (optional):</label>
                                    <input type="text" id="tax-calc-name" name="calculation_name" placeholder="e.g., 2025 Medical Deductions">
                                </div>
                                <input type="hidden" name="calculation_type" value="medical_deduction">
                                <input type="hidden" name="result_data" id="deduction-result-data">
                                <input type="hidden" name="input_data" id="deduction-input-data">
                                <button type="submit" name="save_calculation" class="calc-btn calc-primary">
                                    <i class="fas fa-save"></i> Save This Calculation
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Budget Planner -->
            <div class="financial-section" id="budget-planner">
                <div class="section-title">
                    <i class="fas fa-money-bill-wave"></i>
                    <h2>Monthly Budget Planner</h2>
                </div>
                
                <p>Plan your monthly income and expenses to better manage your finances.</p>
                
                <div class="calculator-container">
                    <div class="calculator-header">
                        <h3>Create Your Monthly Budget</h3>
                        <p>Enter your income sources and expenses to see where your money goes.</p>
                    </div>
                    
                    <div class="calculator-form">
                        <div class="input-group input-addon">
                            <label for="budget-pension">Pension Income (৳)</label>
                            <input type="number" id="budget-pension" value="15000">
                        </div>
                        
                        <div class="input-group input-addon">
                            <label for="budget-investments">Investment Income (৳)</label>
                            <input type="number" id="budget-investments" value="5000">
                        </div>
                        
                        <div class="input-group input-addon">
                            <label for="budget-other">Other Income (৳)</label>
                            <input type="number" id="budget-other" value="2000">
                        </div>
                        
                        <div class="input-group input-addon">
                            <label for="budget-housing">Housing Expenses (৳)</label>
                            <input type="number" id="budget-housing" value="6000">
                        </div>
                        
                        <div class="input-group input-addon">
                            <label for="budget-food">Food Expenses (৳)</label>
                            <input type="number" id="budget-food" value="4000">
                        </div>
                        
                        <div class="input-group input-addon">
                            <label for="budget-healthcare">Healthcare Expenses (৳)</label>
                            <input type="number" id="budget-healthcare" value="3000">
                        </div>
                        
                        <div class="input-group input-addon">
                            <label for="budget-utilities">Utilities Expenses (৳)</label>
                            <input type="number" id="budget-utilities" value="2000">
                        </div>
                        
                        <div class="input-group input-addon">
                            <label for="budget-transport">Transportation Expenses (৳)</label>
                            <input type="number" id="budget-transport" value="1500">
                        </div>
                        
                        <div class="input-group input-addon">
                            <label for="budget-leisure">Leisure & Entertainment (৳)</label>
                            <input type="number" id="budget-leisure" value="2000">
                        </div>
                        
                        <div class="input-group input-addon">
                            <label for="budget-misc">Miscellaneous Expenses (৳)</label>
                            <input type="number" id="budget-misc" value="1000">
                        </div>
                    </div>
                    
                    <div class="calculator-actions">
                        <button class="calc-btn calc-primary" onclick="calculateBudget()">
                            <i class="fas fa-calculator"></i> Calculate Budget
                        </button>
                        <button class="calc-btn calc-secondary" onclick="resetBudgetForm()">
                            <i class="fas fa-redo"></i> Reset
                        </button>
                    </div>
                    
                    <div class="result-container" id="budget-results">
                        <div class="result-title">Your Monthly Budget Summary</div>
                        
                        <div class="result-grid">
                            <div class="result-item">
                                <div class="result-label">Total Income</div>
                                <div class="result-value" id="budget-result-income">৳22,000.00</div>
                            </div>
                            
                            <div class="result-item">
                                <div class="result-label">Total Expenses</div>
                                <div class="result-value" id="budget-result-expenses">৳19,500.00</div>
                            </div>
                            
                            <div class="result-item">
                                <div class="result-label">Monthly Balance</div>
                                <div class="result-value" id="budget-result-balance">৳2,500.00</div>
                            </div>
                            
                            <div class="result-item">
                                <div class="result-label">Annual Savings</div>
                                <div class="result-value" id="budget-result-savings">৳30,000.00</div>
                            </div>
                        </div>
                        
                        <div class="result-chart-container">
                            <canvas id="budget-chart"></canvas>
                        </div>
                        
                        <div class="save-calculation-form">
                            <form method="post" action="">
                                <div class="save-input-group">
                                    <label for="budget-calc-name">Give this budget a name (optional):</label>
                                    <input type="text" id="budget-calc-name" name="calculation_name" placeholder="e.g., My Monthly Budget 2025">
                                </div>
                                <input type="hidden" name="calculation_type" value="monthly_budget">
                                <input type="hidden" name="result_data" id="budget-result-data">
                                <input type="hidden" name="input_data" id="budget-input-data">
                                <button type="submit" name="save_calculation" class="calc-btn calc-primary">
                                    <i class="fas fa-save"></i> Save This Budget
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Saved Calculations -->
            <div class="financial-section" id="saved-calculations">
                <div class="section-title">
                    <i class="fas fa-save"></i>
                    <h2>Your Saved Calculations</h2>
                </div>
                
                <?php if (empty($saved_calculations)): ?>
                    <p>You haven't saved any calculations yet. Use the calculators above to create and save your financial plans.</p>
                <?php else: ?>
                    <div class="saved-calculations">
                        <table class="saved-calculations-table">
                            <thead>
                                <tr>
                                    <th>Name</th>
                                    <th>Type</th>
                                    <th>Date Created</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach($saved_calculations as $calc): ?>
                                    <tr>
                                        <td><?php echo $calc['calculation_name'] ?? 'Unnamed Calculation'; ?></td>
                                        <td>
                                            <?php 
                                                $type_labels = [
                                                    'fixed_deposit' => 'Fixed Deposit',
                                                    'retirement' => 'Retirement Plan',
                                                    'medical_deduction' => 'Medical Tax Deduction',
                                                    'monthly_budget' => 'Monthly Budget'
                                                ];
                                                echo $type_labels[$calc['calculation_type']] ?? $calc['calculation_type'];
                                            ?>
                                        </td>
                                        <td><?php echo date('M d, Y', strtotime($calc['created_at'])); ?></td>
                                        <td class="actions">
                                            <button class="btn btn-view" onclick="viewCalculation(<?php echo $calc['id']; ?>, '<?php echo $calc['calculation_type']; ?>')">
                                                <i class="fas fa-eye"></i> View
                                            </button>
                                            <a href="?delete_calculation=<?php echo $calc['id']; ?>" class="btn btn-delete" onclick="return confirm('Are you sure you want to delete this calculation?');">
                                                <i class="fas fa-trash"></i> Delete
                                            </a>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php endif; ?>
            </div>
            
            <!-- Financial Advisors -->
            <div class="financial-section" id="financial-advisors">
                <div class="section-title">
                    <i class="fas fa-user-tie"></i>
                    <h2>Financial Advisors</h2>
                </div>
                
                <p>Connect with professional financial advisors who can provide personalized guidance.</p>
                
                <div class="tools-grid">
                    <?php foreach($advisors as $advisor): ?>
                        <div class="tool-card">
                            <div class="tool-icon">
                                <i class="fas fa-user-tie"></i>
                            </div>
                            <h3 class="tool-title"><?php echo $advisor['name']; ?></h3>
                            <p class="tool-description"><strong>Specialty:</strong> <?php echo $advisor['specialty']; ?></p>
                            <p class="tool-description"><?php echo $advisor['expertise'] ?? ''; ?></p>
                            <p class="tool-description"><strong>Rating:</strong> <?php echo number_format($advisor['rating'], 1); ?> / 5.0</p>
                            <a href="#" class="tool-link" onclick="scheduleAdvisor(<?php echo $advisor['id']; ?>)">
                                Schedule Consultation <i class="fas fa-arrow-right"></i>
                            </a>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>
    </main>
    
    <!-- View Calculation Modal -->
    <div id="viewCalculationModal" class="modal">
        <div class="modal-content">
            <span class="close-modal" onclick="closeModal()">&times;</span>
            <h2 id="modal-title">Calculation Details</h2>
            <div id="modal-content"></div>
        </div>
    </div>
    
    <?php include '../includes/footer.php'; ?>
    
    <script>
        // Global variables
        let fdChart, retirementChart, deductionChart, budgetChart;
        
        document.addEventListener('DOMContentLoaded', function() {
            // Initialize charts
            initCharts();
            
            // Show welcome message
            showToast("Welcome to Financial & Legal Assistance");
        });
        
        function showToast(message) {
            // Implementation for showing toast notifications
            // This could be enhanced if needed
            alert(message);
        }
        
        function initCharts() {
            // Initialize chart.js context for all calculators
            const fdCtx = document.getElementById('fd-chart').getContext('2d');
            const retirementCtx = document.getElementById('retirement-chart').getContext('2d');
            const deductionCtx = document.getElementById('deduction-chart').getContext('2d');
            const budgetCtx = document.getElementById('budget-chart').getContext('2d');
            
            // Fixed Deposit Chart
            fdChart = new Chart(fdCtx, {
                type: 'bar',
                data: {
                    labels: ['Principal', 'Interest Earned', 'Maturity Amount'],
                    datasets: [{
                        label: 'Amount (৳)',
                        data: [10000, 3382.26, 13382.26],
                        backgroundColor: [
                            'rgba(0, 150, 173, 0.5)',
                            'rgba(255, 159, 64, 0.5)',
                            'rgba(75, 192, 192, 0.5)'
                        ],
                        borderColor: [
                            'rgb(0, 150, 173)',
                            'rgb(255, 159, 64)',
                            'rgb(75, 192, 192)'
                        ],
                        borderWidth: 1
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: {
                            display: false
                        },
                        title: {
                            display: true,
                            text: 'Fixed Deposit Summary'
                        }
                    },
                    scales: {
                        y: {
                            beginAtZero: true,
                            title: {
                                display: true,
                                text: 'Amount (৳)'
                            }
                        }
                    }
                }
            });
            
            // Retirement Chart
            retirementChart = new Chart(retirementCtx, {
                type: 'line',
                data: {
                    labels: Array.from({length: 20}, (_, i) => i + 65),
                    datasets: [{
                        label: 'Remaining Savings',
                        data: generateRandomData(20, 1500000, 2000000),
                        borderColor: 'rgb(0, 150, 173)',
                        backgroundColor: 'rgba(0, 150, 173, 0.1)',
                        tension: 0.4,
                        fill: true
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        title: {
                            display: true,
                            text: 'Projected Savings Over Retirement'
                        }
                    },
                    scales: {
                        y: {
                            beginAtZero: true,
                            title: {
                                display: true,
                                text: 'Savings Amount (৳)'
                            }
                        },
                        x: {
                            title: {
                                display: true,
                                text: 'Age'
                            }
                        }
                    }
                }
            });
            
            // Medical Deduction Chart
            deductionChart = new Chart(deductionCtx, {
                type: 'bar',
                data: {
                    labels: ['Medical Expenses', 'AGI Threshold (7.5%)', 'Potential Deduction'],
                    datasets: [{
                        label: 'Amount (৳)',
                        data: [50000, 37500, 12500],
                        backgroundColor: [
                            'rgba(54, 162, 235, 0.5)',
                            'rgba(255, 99, 132, 0.5)',
                            'rgba(75, 192, 192, 0.5)'
                        ],
                        borderColor: [
                            'rgb(54, 162, 235)',
                            'rgb(255, 99, 132)',
                            'rgb(75, 192, 192)'
                        ],
                        borderWidth: 1
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: {
                            display: false
                        },
                        title: {
                            display: true,
                            text: 'Medical Expense Deduction'
                        }
                    },
                    scales: {
                        y: {
                            beginAtZero: true,
                            title: {
                                display: true,
                                text: 'Amount (৳)'
                            }
                        }
                    }
                }
            });
            
            // Budget Chart
            budgetChart = new Chart(budgetCtx, {
                type: 'pie',
                data: {
                    labels: ['Housing', 'Food', 'Healthcare', 'Utilities', 'Transportation', 'Leisure', 'Miscellaneous'],
                    datasets: [{
                        label: 'Expenses',
                        data: [6000, 4000, 3000, 2000, 1500, 2000, 1000],
                        backgroundColor: [
                            'rgba(0, 150, 173, 0.7)',
                            'rgba(255, 159, 64, 0.7)',
                            'rgba(75, 192, 192, 0.7)',
                            'rgba(54, 162, 235, 0.7)',
                            'rgba(255, 99, 132, 0.7)',
                            'rgba(153, 102, 255, 0.7)',
                            'rgba(255, 205, 86, 0.7)'
                        ],
                        borderColor: [
                            'rgb(0, 150, 173)',
                            'rgb(255, 159, 64)',
                            'rgb(75, 192, 192)',
                            'rgb(54, 162, 235)',
                            'rgb(255, 99, 132)',
                            'rgb(153, 102, 255)',
                            'rgb(255, 205, 86)'
                        ],
                        borderWidth: 1
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: {
                            position: 'right',
                            labels: {
                                boxWidth: 10,
                                padding: 15
                            }
                        },
                        title: {
                            display: true,
                            text: 'Monthly Budget Distribution'
                        }
                    }
                }
            });
        }
        
        function calculateFixedDeposit() {
            const principal = parseFloat(document.getElementById('fd-principal').value);
            const interestRate = parseFloat(document.getElementById('fd-interest-rate').value);
            const years = parseInt(document.getElementById('fd-years').value);
            
            if(isNaN(principal) || isNaN(interestRate) || isNaN(years)) {
                alert("Please enter valid numbers for all fields.");
                return;
            }
            
            const result = <?php echo json_encode(calculateFixedDeposit(10000, 6, 5)); ?>;
            
            document.getElementById('fd-result-principal').innerText = '৳' + result.principal;
            document.getElementById('fd-result-rate').innerText = result.interest_rate + '%';
            document.getElementById('fd-result-years').innerText = result.term + ' Years';
            document.getElementById('fd-result-maturity').innerText = '৳' + result.maturity_amount;
            document.getElementById('fd-result-interest').innerText = '৳' + result.interest_earned;
            
            // Update chart data
            fdChart.data.datasets[0].data = [principal, interest_earned, maturity_amount];
            fdChart.update();
            
            // Show results
            document.getElementById('fd-results').style.display = 'block';
        }
        
        function resetFixedDepositForm() {
            document.getElementById('fd-principal').value = '10000';
            document.getElementById('fd-interest-rate').value = '6';
            document.getElementById('fd-years').value = '5';
            
            document.getElementById('fd-results').style.display = 'none';
        }
        
        function calculateRetirement() {
            const currentAge = parseInt(document.getElementById('retirement-current-age').value);
            const retirementAge = parseInt(document.getElementById('retirement-age').value);
            const lifeExpectancy = parseInt(document.getElementById('retirement-life-expectancy').value);
            const monthlySocialSecurity = parseFloat(document.getElementById('retirement-social-security').value);
            const monthlyPension = parseFloat(document.getElementById('retirement-pension').value);
            const monthlyExpenses = parseFloat(document.getElementById('retirement-expenses').value);
            const savings = parseFloat(document.getElementById('retirement-savings').value);
            const annualInflation = parseFloat(document.getElementById('retirement-inflation').value);
            const investmentReturn = parseFloat(document.getElementById('retirement-return').value);
            
            if(isNaN(currentAge) || isNaN(retirementAge) || isNaN(lifeExpectancy) || isNaN(monthlySocialSecurity) || isNaN(monthlyPension) || isNaN(monthlyExpenses) || isNaN(savings) || isNaN(annualInflation) || isNaN(investmentReturn)) {
                alert("Please enter valid numbers for all fields.");
                return;
            }
            
            const result = <?php echo json_encode(calculateRetirement(60, 65, 85, 15000, 5000, 18000, 1500000, 5, 8)); ?>;
            
            document.getElementById('retirement-result-income').innerText = '৳' + result.monthly_income;
            document.getElementById('retirement-result-balance').innerText = '৳' + result.monthly_balance;
            document.getElementById('retirement-result-years').innerText = result.retirement_years;
            document.getElementById('retirement-result-depleted').innerText = result.savings_depleted;
            
            // Update chart data
            retirementChart.data.datasets[0].data = result.savings_projection;
            retirementChart.update();
            
            // Show results
            document.getElementById('retirement-results').style.display = 'block';
        }
        
        function resetRetirementForm() {
            document.getElementById('retirement-current-age').value = '60';
            document.getElementById('retirement-age').value = '65';
            document.getElementById('retirement-life-expectancy').value = '85';
            document.getElementById('retirement-social-security').value = '15000';
            document.getElementById('retirement-pension').value = '5000';
            document.getElementById('retirement-expenses').value = '18000';
            document.getElementById('retirement-savings').value = '1500000';
            document.getElementById('retirement-inflation').value = '5';
            document.getElementById('retirement-return').value = '8';
            
            document.getElementById('retirement-results').style.display = 'none';
        }
        
        function calculateMedicalDeduction() {
            const medicalExpenses = parseFloat(document.getElementById('tax-medical-expenses').value);
            const agi = parseFloat(document.getElementById('tax-agi').value);
            
            if(isNaN(medicalExpenses) || isNaN(agi)) {
                alert("Please enter valid numbers for all fields.");
                return;
            }
            
            const result = <?php echo json_encode(calculateMedicalDeduction(50000, 500000)); ?>;
            
            document.getElementById('deduction-result-expenses').innerText = '৳' + result.total_medical_expenses;
            document.getElementById('deduction-result-threshold').innerText = '৳' + result.agi_threshold;
            document.getElementById('deduction-result-deduction').innerText = '৳' + result.potential_deduction;
            document.getElementById('deduction-result-savings').innerText = '৳' + result.tax_savings;
            
            // Update chart data
            deductionChart.data.datasets[0].data = [medicalExpenses, threshold, potential_deduction];
            deductionChart.update();
            
            // Show results
            document.getElementById('medical-deduction-results').style.display = 'block';
        }
        
        function resetMedicalDeductionForm() {
            document.getElementById('tax-medical-expenses').value = '50000';
            document.getElementById('tax-agi').value = '500000';
            
            document.getElementById('medical-deduction-results').style.display = 'none';
        }
        
        function calculateBudget() {
            const housing = parseFloat(document.getElementById('budget-housing').value);
            const food = parseFloat(document.getElementById('budget-food').value);
            const healthcare = parseFloat(document.getElementById('budget-healthcare').value);
            const utilities = parseFloat(document.getElementById('budget-utilities').value);
            const transport = parseFloat(document.getElementById('budget-transport').value);
            const leisure = parseFloat(document.getElementById('budget-leisure').value);
            const misc = parseFloat(document.getElementById('budget-misc').value);
            
            const totalIncome = 15000 + 5000 + 2000; // Sample total income
            const totalExpenses = housing + food + healthcare + utilities + transport + leisure + misc;
            const monthlyBalance = totalIncome - totalExpenses;
            const annualSavings = monthlyBalance > 0 ? monthlyBalance * 12 : 0;
            
            document.getElementById('budget-result-income').innerText = '৳' + totalIncome.toFixed(2);
            document.getElementById('budget-result-expenses').innerText = '৳' + totalExpenses.toFixed(2);
            document.getElementById('budget-result-balance').innerText = '৳' + monthlyBalance.toFixed(2);
            document.getElementById('budget-result-savings').innerText = '৳' + annualSavings.toFixed(2);
            
            // Update chart data
            budgetChart.data.datasets[0].data = [housing, food, healthcare, utilities, transport, leisure, misc];
            budgetChart.update();
            
            // Show results
            document.getElementById('budget-results').style.display = 'block';
        }
        
        function resetBudgetForm() {
            document.getElementById('budget-pension').value = '15000';
            document.getElementById('budget-investments').value = '5000';
            document.getElementById('budget-other').value = '2000';
            document.getElementById('budget-housing').value = '6000';
            document.getElementById('budget-food').value = '4000';
            document.getElementById('budget-healthcare').value = '3000';
            document.getElementById('budget-utilities').value = '2000';
            document.getElementById('budget-transport').value = '1500';
            document.getElementById('budget-leisure').value = '2000';
            document.getElementById('budget-misc').value = '1000';
            
            document.getElementById('budget-results').style.display = 'none';
        }
        
        function scrollToCalculator(calculatorId) {
            const element = document.getElementById(calculatorId);
            if(element) {
                element.scrollIntoView({behavior: 'smooth', block: 'start'});
            }
        }
        
        function viewCalculation(id, type) {
            // Fetch and display calculation details in modal
            const modalTitle = document.getElementById('modal-title');
            const modalContent = document.getElementById('modal-content');
            
            modalTitle.innerText = "Calculation Details";
            modalContent.innerHTML = "<p>Loading...</p>";
            
            // Simulate fetch - replace with actual AJAX call
            setTimeout(() => {
                modalContent.innerHTML = `
                    <p><strong>Type:</strong> ${type}</p>
                    <p><strong>Details:</strong> This is where the calculation details will be displayed.</p>
                `;
            }, 500);
            
            // Show modal
            document.getElementById('viewCalculationModal').style.display = 'block';
        }
        
        function closeModal() {
            document.getElementById('viewCalculationModal').style.display = 'none';
        }
        
        function scheduleAdvisor(advisorId) {
            // Placeholder function for scheduling an advisor
            alert("Scheduling feature is not implemented yet.");
        }
    </script>
</body>
</html>