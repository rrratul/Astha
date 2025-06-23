<?php
session_start();

// Include required files
require_once '../../login_user/functions.php';
require_once '../../signup_user/config.php';

// Authentication checks (same as before)
if (!isLoggedIn()) {
    header("Location: ../../login_user/login_user.php");
    exit();
}

// Role checking (same as before)
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
$user_first_name = $_SESSION['user_first_name'] ?? 'User';

// Sample retirement calculation data
$retirement_data = [
    'current_age' => 67,
    'retirement_age' => 70,
    'life_expectancy' => 85,
    'monthly_social_security' => 1850,
    'monthly_pension' => 1200,
    'monthly_expenses' => 2500,
    'savings' => 230000,
    'annual_inflation' => 2.5,
    'investment_return' => 4.0
];

// Sample medication expenses for tax deduction
$medical_expenses = [
    ['name' => 'Blood Pressure Medication', 'monthly_cost' => 45.75, 'annual_cost' => 549.00, 'covered_by_insurance' => 'Partially'],
    ['name' => 'Cholesterol Medication', 'monthly_cost' => 65.30, 'annual_cost' => 783.60, 'covered_by_insurance' => 'Partially'],
    ['name' => 'Arthritis Medication', 'monthly_cost' => 87.25, 'annual_cost' => 1047.00, 'covered_by_insurance' => 'No'],
    ['name' => 'Diabetes Supplies', 'monthly_cost' => 120.50, 'annual_cost' => 1446.00, 'covered_by_insurance' => 'Partially'],
    ['name' => 'Dental Procedures', 'monthly_cost' => 0, 'annual_cost' => 875.00, 'covered_by_insurance' => 'No']
];

// Calculate total medical expenses
$total_monthly_medical = array_sum(array_column($medical_expenses, 'monthly_cost'));
$total_annual_medical = array_sum(array_column($medical_expenses, 'annual_cost'));

// Sample property information for estate planning
$property_assets = [
    ['type' => 'Primary Residence', 'estimated_value' => 325000, 'outstanding_mortgage' => 78000, 'equity' => 247000],
    ['type' => 'Vacation Home', 'estimated_value' => 175000, 'outstanding_mortgage' => 0, 'equity' => 175000]
];

// Sample financial accounts
$financial_accounts = [
    ['type' => 'Checking Account', 'institution' => 'First National Bank', 'balance' => 4500],
    ['type' => 'Savings Account', 'institution' => 'First National Bank', 'balance' => 15000],
    ['type' => 'IRA', 'institution' => 'Fidelity Investments', 'balance' => 142000],
    ['type' => '401(k)', 'institution' => 'Vanguard', 'balance' => 210000]
];

// Sample beneficiaries for estate planning
$beneficiaries = [
    ['name' => 'Sarah Johnson', 'relationship' => 'Daughter', 'percentage' => 40],
    ['name' => 'Michael Johnson', 'relationship' => 'Son', 'percentage' => 40],
    ['name' => 'Children\'s Hospital Foundation', 'relationship' => 'Charity', 'percentage' => 20]
];

// Calculate total estate value
$total_property_value = array_sum(array_column($property_assets, 'estimated_value'));
$total_financial_accounts = array_sum(array_column($financial_accounts, 'balance'));
$total_estate_value = $total_property_value + $total_financial_accounts;

// Sample social security benefit calculation
$ss_benefits = [
    'base_benefit' => 1750,
    'delayed_retirement_credit' => 140,
    'spousal_benefit' => 0,
    'total_monthly_benefit' => 1890,
    'annual_benefit' => 22680
];

// Sample tax bracket information
$tax_brackets = [
    ['bracket' => '10%', 'single_income' => 'Up to $10,275', 'married_income' => 'Up to $20,550'],
    ['bracket' => '12%', 'single_income' => '$10,276 to $41,775', 'married_income' => '$20,551 to $83,550'],
    ['bracket' => '22%', 'single_income' => '$41,776 to $89,075', 'married_income' => '$83,551 to $178,150'],
    ['bracket' => '24%', 'single_income' => '$89,076 to $170,050', 'married_income' => '$178,151 to $340,100']
];

// Financial advisors (same as before but with more details about expertise)
$financial_advisors = [
    [
        'id' => 1,
        'name' => 'Sarah Johnson',
        'specialty' => 'Retirement Planning',
        'expertise' => 'Specializes in helping seniors maximize retirement income and minimize taxes',
        'image' => 'https://via.placeholder.com/100x100/4CAF50/ffffff?text=SJ',
        'rating' => 4.8,
        'available' => true
    ],
    [
        'id' => 2,
        'name' => 'Michael Patel',
        'specialty' => 'Estate Planning',
        'expertise' => 'Creates comprehensive estate plans including wills, trusts, and power of attorney documents',
        'image' => 'https://via.placeholder.com/100x100/2196F3/ffffff?text=MP',
        'rating' => 4.9,
        'available' => true
    ],
    [
        'id' => 3,
        'name' => 'Lisa Rodriguez',
        'specialty' => 'Tax Filing Assistance',
        'expertise' => 'Helps seniors identify all available deductions and credits to minimize tax liability',
        'image' => 'https://via.placeholder.com/100x100/9C27B0/ffffff?text=LR',
        'rating' => 4.7,
        'available' => false
    ]
];

// Legal resources (same as before)
$legal_resources = [
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
    ]
];

// Financial tools (enhanced with more details)
$financial_tools = [
    [
        'title' => 'Monthly Budget Calculator',
        'description' => 'Input your income sources and expenses to create a personalized monthly budget plan.',
        'icon' => 'fas fa-calculator',
        'link' => '#budget-planner'
    ],
    [
        'title' => 'Social Security Benefits Calculator',
        'description' => 'Calculate your optimal Social Security claiming age to maximize lifetime benefits.',
        'icon' => 'fas fa-hand-holding-usd',
        'link' => '#social-security'
    ],
    [
        'title' => 'Required Minimum Distribution Calculator',
        'description' => 'Calculate mandatory withdrawals from retirement accounts to avoid IRS penalties.',
        'icon' => 'fas fa-file-invoice-dollar',
        'link' => '#rmd-calculator'
    ]
];

// Upcoming payments (same as before)
$upcoming_payments = [
    [
        'name' => 'Electricity Bill',
        'amount' => '$78.45',
        'due_date' => date('Y-m-d', strtotime('+3 days')),
        'status' => 'upcoming',
        'autopay' => false
    ],
    [
        'name' => 'Health Insurance Premium',
        'amount' => '$145.00',
        'due_date' => date('Y-m-d', strtotime('+7 days')),
        'status' => 'upcoming',
        'autopay' => true
    ],
    [
        'name' => 'Property Tax',
        'amount' => '$650.00',
        'due_date' => date('Y-m-d', strtotime('+14 days')),
        'status' => 'upcoming',
        'autopay' => false
    ]
];

// Sample elder-specific tax deductions
$senior_tax_deductions = [
    ['name' => 'Higher Standard Deduction', 'description' => 'Seniors 65+ get an extra $1,750 (single) or $1,400 per spouse (married) in 2023', 'eligibility' => 'Must be 65 or older'],
    ['name' => 'Medical Expense Deduction', 'description' => 'Deduct medical expenses that exceed 7.5% of adjusted gross income', 'eligibility' => 'All taxpayers, but especially beneficial to seniors'],
    ['name' => 'Credit for the Elderly', 'description' => 'Tax credit up to $1,125 for low-income seniors', 'eligibility' => 'Age 65+ with income limits'],
    ['name' => 'HSA Contributions', 'description' => 'Higher contribution limits ($4,850 in 2023) for those 55+', 'eligibility' => 'Must have HSA-eligible health plan']
];
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Financial & Legal Assistance - Elderly Care Dashboard</title>
    
    <!-- Google Translate (same as before) -->
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
    
    <!-- Styles and fonts (same as before) -->
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@400;500;600;700;800&family=Open+Sans:wght@400;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <link rel="stylesheet" href="../includes/style.css">
    
    <!-- Additional calculator libraries -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js@3.7.1/dist/chart.min.js"></script>
    
    <style>
        /* Financial Assistance specific styling (same as before) */
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
        
        /* Financial Table Styling */
        .financial-table {
            width: 100%;
            border-collapse: collapse;
            margin: 1.5rem 0;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.05);
        }
        
        .financial-table th {
            background: #f1f5f9;
            padding: 1rem;
            text-align: left;
            font-weight: 600;
            color: #334155;
            border-bottom: 2px solid #cbd5e0;
        }
        
        .financial-table td {
            padding: 1rem;
            border-bottom: 1px solid #e5e7eb;
            color: #4b5563;
            font-size: 1.05rem;
        }
        
        .financial-table tr:last-child td {
            border-bottom: none;
        }
        
        .financial-table tr:hover {
            background-color: #f8fafc;
        }
        
        .table-actions {
            display: flex;
            gap: 0.5rem;
        }
        
        .table-btn {
            background: #edf2f7;
            border: none;
            color: #4a5568;
            padding: 0.4rem 0.6rem;
            border-radius: 5px;
            font-size: 0.8rem;
            cursor: pointer;
            transition: all 0.2s ease;
        }
        
        .table-btn:hover {
            background: #e2e8f0;
        }
        
        .total-row {
            font-weight: 600;
            background: #f1f5f9;
        }
        
        .total-row td {
            border-top: 2px solid #cbd5e0;
        }
        
        /* Estate Planning Document */
        .document-preview {
            background: #fff;
            border: 1px solid #cbd5e0;
            border-radius: 5px;
            padding: 2rem;
            margin: 2rem 0;
            font-family: 'Times New Roman', Times, serif;
            color: #000;
        }
        
        .document-header {
            text-align: center;
            margin-bottom: 2rem;
            border-bottom: 1px solid #000;
            padding-bottom: 1rem;
        }
        
        .document-title {
            font-size: 1.8rem;
            font-weight: bold;
            margin-bottom: 0.5rem;
        }
        
        .document-subtitle {
            font-size: 1.1rem;
            font-style: italic;
        }
        
        .doc-section {
            margin-bottom: 1.5rem;
        }
        
        .doc-section-title {
            font-weight: bold;
            font-size: 1.2rem;
            margin-bottom: 0.8rem;
        }
        
        .doc-paragraph {
            margin-bottom: 1rem;
            line-height: 1.5;
        }
        
        .signature-section {
            margin-top: 3rem;
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 2rem;
        }
        
        .signature-line {
            border-top: 1px solid #000;
            padding-top: 0.5rem;
            margin-top: 2rem;
        }
        
        .pie-chart-container {
            display: flex;
            justify-content: center;
            align-items: center;
            height: 300px;
            margin: 2rem 0;
        }
        
        /* Rest of CSS remains the same as before */
        /* AI Assistant, Voice Controls, Tools Grid, etc. */
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
                    <?php foreach ($financial_tools as $tool): ?>
                    <div class="tool-card">
                        <div class="tool-icon">
                            <i class="<?php echo $tool['icon']; ?>"></i>
                        </div>
                        <h3 class="tool-title"><?php echo $tool['title']; ?></h3>
                        <p class="tool-description"><?php echo $tool['description']; ?></p>
                        <a href="<?php echo $tool['link']; ?>" class="tool-link">
                            Get Started <i class="fas fa-arrow-right"></i>
                        </a>
                    </div>
                    <?php endforeach; ?>
                </div>
            </div>
            
            <!-- Retirement Income Calculator -->
            <div class="financial-section" id="retirement-calculator">
                <div class="section-title">
                    <i class="fas fa-piggy-bank"></i>
                    <h2>Retirement Income Calculator</h2>
                </div>
                
                <p>Use this tool to estimate your retirement income and determine if your savings will last through retirement.</p>
                
                <div class="calculator-container">
                    <div class="calculator-header">
                        <h3>Calculate Your Retirement Income</h3>
                        <p>Enter your information below to see how much monthly income you can expect in retirement.</p>
                    </div>
                    
                    <div class="calculator-form">
                        <div class="input-group">
                            <label for="current-age">Current Age</label>
                            <input type="number" id="current-age" value="<?php echo $retirement_data['current_age']; ?>" min="50" max="85">
                        </div>
                        
                        <div class="input-group">
                            <label for="retirement-age">Retirement Age</label>
                            <input type="number" id="retirement-age" value="<?php echo $retirement_data['retirement_age']; ?>" min="62" max="75">
                        </div>
                        
                        <div class="input-group">
                            <label for="life-expectancy">Life Expectancy</label>
                            <input type="number" id="life-expectancy" value="<?php echo $retirement_data['life_expectancy']; ?>" min="70" max="100">
                        </div>
                        
                        <div class="input-group input-addon">
                            <label for="social-security">Monthly Social Security</label>
                            <input type="number" id="social-security" value="<?php echo $retirement_data['monthly_social_security']; ?>">
                        </div>
                        
                        <div class="input-group input-addon">
                            <label for="pension">Monthly Pension (if any)</label>
                            <input type="number" id="pension" value="<?php echo $retirement_data['monthly_pension']; ?>">
                        </div>
                        
                        <div class="input-group input-addon">
                            <label for="monthly-expenses">Expected Monthly Expenses</label>
                            <input type="number" id="monthly-expenses" value="<?php echo $retirement_data['monthly_expenses']; ?>">
                        </div>
                        
                        <div class="input-group input-addon">
                            <label for="total-savings">Total Retirement Savings</label>
                            <input type="number" id="total-savings" value="<?php echo $retirement_data['savings']; ?>">
                        </div>
                        
                        <div class="input-group">
                            <label for="inflation-rate">Expected Inflation Rate (%)</label>
                            <input type="number" id="inflation-rate" value="<?php echo $retirement_data['annual_inflation']; ?>" step="0.1" min="0" max="10">
                        </div>
                        
                        <div class="input-group">
                            <label for="investment-return">Expected Investment Return (%)</label>
                            <input type="number" id="investment-return" value="<?php echo $retirement_data['investment_return']; ?>" step="0.1" min="0" max="12">
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
                                <div class="result-value" id="monthly-income">$3,050</div>
                            </div>
                            
                            <div class="result-item">
                                <div class="result-label">Monthly Deficit/Surplus</div>
                                <div class="result-value" id="monthly-balance">$550</div>
                            </div>
                            
                            <div class="result-item">
                                <div class="result-label">Years of Retirement</div>
                                <div class="result-value" id="retirement-years">15</div>
                            </div>
                            
                            <div class="result-item">
                                <div class="result-label">Savings Depleted At</div>
                                <div class="result-value" id="savings-depleted">Age 97</div>
                            </div>
                        </div>
                        
                        <div class="result-chart-container">
                            <canvas id="retirement-chart"></canvas>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Medical Expense Tax Deduction Calculator -->
            <div class="financial-section" id="tax-deduction-calculator">
                <div class="section-title">
                    <i class="fas fa-file-medical-alt"></i>
                    <h2>Medical Expense Tax Deduction Calculator</h2>
                </div>
                
                <p>Track your medical expenses to identify potential tax deductions. Expenses that exceed 7.5% of your adjusted gross income may be tax-deductible.</p>
                
                <div class="calculator-container">
                    <div class="calculator-header">
                        <h3>Your Medical Expenses</h3>
                        <p>Review your current medical expenses and calculate potential tax deductions.</p>
                    </div>
                    
                    <table class="financial-table">
                        <thead>
                            <tr>
                                <th>Expense Type</th>
                                <th>Monthly Cost</th>
                                <th>Annual Cost</th>
                                <th>Insurance Coverage</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($medical_expenses as $expense): ?>
                            <tr>
                                <td><?php echo $expense['name']; ?></td>
                                <td>$<?php echo number_format($expense['monthly_cost'], 2); ?></td>
                                <td>$<?php echo number_format($expense['annual_cost'], 2); ?></td>
                                <td><?php echo $expense['covered_by_insurance']; ?></td>
                                <td class="table-actions">
                                    <button class="table-btn"><i class="fas fa-edit"></i></button>
                                    <button class="table-btn"><i class="fas fa-trash-alt"></i></button>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                            <tr class="total-row">
                                <td>Total</td>
                                <td>$<?php echo number_format($total_monthly_medical, 2); ?></td>
                                <td>$<?php echo number_format($total_annual_medical, 2); ?></td>
                                <td></td>
                                <td></td>
                            </tr>
                        </tbody>
                    </table>
                    
                    <div class="calculator-form" style="grid-template-columns: 1fr 1fr;">
                        <div class="input-group input-addon">
                            <label for="adjusted-gross-income">Your Adjusted Gross Income (AGI)</label>
                            <input type="number" id="adjusted-gross-income" value="42000">
                        </div>
                        
                        <div class="input-group">
                            <label for="tax-filing-status">Tax Filing Status</label>
                            <select id="tax-filing-status">
                                <option value="single">Single</option>
                                <option value="married">Married Filing Jointly</option>
                                <option value="head">Head of Household</option>
                                <option value="separate">Married Filing Separately</option>
                            </select>
                        </div>
                    </div>
                    
                    <div class="calculator-actions">
                        <button class="calc-btn calc-primary" onclick="calculateMedicalDeduction()">
                            <i class="fas fa-calculator"></i> Calculate Deduction
                        </button>
                        <button class="calc-btn calc-secondary" onclick="addNewExpense()">
                            <i class="fas fa-plus"></i> Add Expense
                        </button>
                    </div>
                    
                    <div class="result-container" id="medical-deduction-results" style="display: block;">
                        <div class="result-title">Your Medical Expense Tax Deduction</div>
                        
                        <div class="result-grid">
                            <div class="result-item">
                                <div class="result-label">Total Medical Expenses</div>
                                <div class="result-value">$<?php echo number_format($total_annual_medical, 2); ?></div>
                            </div>
                            
                            <div class="result-item">
                                <div class="result-label">7.5% of Your AGI</div>
                                <div class="result-value" id="agi-threshold">$3,150.00</div>
                            </div>
                            
                            <div class="result-item">
                                <div class="result-label">Potential Deduction</div>
                                <div class="result-value" id="potential-deduction">$1,550.60</div>
                            </div>
                            
                            <div class="result-item">
                                <div class="result-label">Estimated Tax Savings</div>
                                <div class="result-value" id="tax-savings">$186.07</div>
                            </div>
                        </div>
                        
                        <div class="result-chart-container">
                            <canvas id="deduction-chart"></canvas>
                        </div>
                        
                        <div style="margin-top: 2rem;">
                            <h4 style="margin-bottom: 1rem;">Senior-Specific Tax Deductions</h4>
                            <table class="financial-table">
                                <thead>
                                    <tr>
                                        <th>Deduction Type</th>
                                        <th>Description</th>
                                        <th>Eligibility</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($senior_tax_deductions as $deduction): ?>
                                    <tr>
                                        <td><?php echo $deduction['name']; ?></td>
                                        <td><?php echo $deduction['description']; ?></td>
                                        <td><?php echo $deduction['eligibility']; ?></td>
                                    </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Will Creation Tool with Estate Planning -->
            <div class="financial-section" id="will-creation-tool">
                <div class="section-title">
                    <i class="fas fa-file-signature"></i>
                    <h2>Estate Planning & Will Creation</h2>
                </div>
                
                <p>Create a legally binding will and plan your estate distribution with our easy-to-use tool.</p>
                
                <div class="calculator-container">
                    <div class="calculator-header">
                        <h3>Your Estate Summary</h3>
                        <p>Review and manage your assets, beneficiaries, and will documents.</p>
                    </div>
                    
                    <div class="result-container" style="display: block;">
                        <div class="result-title">Estate Value Overview</div>
                        
                        <div class="result-grid">
                            <div class="result-item">
                                <div class="result-label">Total Property Value</div>
                                <div class="result-value">$<?php echo number_format($total_property_value, 0); ?></div>
                            </div>
                            
                            <div class="result-item">
                                <div class="result-label">Total Financial Accounts</div>
                                <div class="result-value">$<?php echo number_format($total_financial_accounts, 0); ?></div>
                            </div>
                            
                            <div class="result-item">
                                <div class="result-label">Total Estate Value</div>
                                <div class="result-value">$<?php echo number_format($total_estate_value, 0); ?></div>
                            </div>
                            
                            <div class="result-item">
                                <div class="result-label">Number of Beneficiaries</div>
                                <div class="result-value"><?php echo count($beneficiaries); ?></div>
                            </div>
                        </div>
                        
                        <div class="pie-chart-container">
                            <canvas id="estate-distribution-chart"></canvas>
                        </div>
                    </div>
                    
                    <div style="margin: 2rem 0;">
                        <h4>Your Property Assets</h4>
                        <table class="financial-table">
                            <thead>
                                <tr>
                                    <th>Property Type</th>
                                    <th>Estimated Value</th>
                                    <th>Outstanding Mortgage</th>
                                    <th>Equity</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($property_assets as $property): ?>
                                <tr>
                                    <td><?php echo $property['type']; ?></td>
                                    <td>$<?php echo number_format($property['estimated_value'], 0); ?></td>
                                    <td>$<?php echo number_format($property['outstanding_mortgage'], 0); ?></td>
                                    <td>$<?php echo number_format($property['equity'], 0); ?></td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                    
                    <div style="margin: 2rem 0;">
                        <h4>Your Financial Accounts</h4>
                        <table class="financial-table">
                            <thead>
                                <tr>
                                    <th>Account Type</th>
                                    <th>Institution</th>
                                    <th>Balance</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($financial_accounts as $account): ?>
                                <tr>
                                    <td><?php echo $account['type']; ?></td>
                                    <td><?php echo $account['institution']; ?></td>
                                    <td>$<?php echo number_format($account['balance'], 0); ?></td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                    
                    <div style="margin: 2rem 0;">
                        <h4>Your Beneficiaries</h4>
                        <table class="financial-table">
                            <thead>
                                <tr>
                                    <th>Name</th>
                                    <th>Relationship</th>
                                    <th>Distribution Percentage</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($beneficiaries as $beneficiary): ?>
                                <tr>
                                    <td><?php echo $beneficiary['name']; ?></td>
                                    <td><?php echo $beneficiary['relationship']; ?></td>
                                    <td><?php echo $beneficiary['percentage']; ?>%</td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                    
                    <div class="calculator-actions">
                        <button class="calc-btn calc-primary" onclick="viewWillDocument()">
                            <i class="fas fa-file-alt"></i> Preview Will Document
                        </button>
                        <button class="calc-btn calc-secondary" onclick="editEstateDetails()">
                            <i class="fas fa-edit"></i> Edit Estate Details
                        </button>
                    </div>
                    
                    <!-- Will Document Preview -->
                    <div class="document-preview" id="will-document" style="display: none;">
                        <div class="document-header">
                            <div class="document-title">LAST WILL AND TESTAMENT</div>
                            <div class="document-subtitle">of John A. Smith</div>
                        </div>
                        
                        <div class="doc-section">
                            <div class="doc-section-title">ARTICLE I: DECLARATION</div>
                            <div class="doc-paragraph">
                                I, John A. Smith, a resident of Chicago, Illinois, being of sound mind, declare this to be my Last Will and Testament, hereby revoking all prior wills and codicils made by me.
                            </div>
                        </div>
                        
                        <div class="doc-section">
                            <div class="doc-section-title">ARTICLE II: EXECUTOR APPOINTMENT</div>
                            <div class="doc-paragraph">
                                I appoint my daughter, Sarah Johnson, as Personal Representative (Executor) of my estate. If she is unable or unwilling to serve, I appoint my son, Michael Johnson, as alternate Personal Representative.
                            </div>
                        </div>
                        
                        <div class="doc-section">
                            <div class="doc-section-title">ARTICLE III: DISTRIBUTION OF ESTATE</div>
                            <div class="doc-paragraph">
                                After payment of all just debts, funeral expenses, and expenses of administration, I direct that my estate be distributed as follows:
                            </div>
                            <div class="doc-paragraph">
                                <strong>A. To my daughter, Sarah Johnson:</strong> Forty percent (40%) of my estate, including my primary residence located at 123 Main Street, Chicago, IL.
                            </div>
                            <div class="doc-paragraph">
                                <strong>B. To my son, Michael Johnson:</strong> Forty percent (40%) of my estate, including my vacation home located at 456 Lake Drive, Lake Geneva, WI.
                            </div>
                            <div class="doc-paragraph">
                                <strong>C. To Children's Hospital Foundation:</strong> Twenty percent (20%) of my estate, to be used for their general charitable purposes.
                            </div>
                        </div>
                        
                        <div class="signature-section">
                            <div>
                                <div class="signature-line">Testator's Signature</div>
                                <div style="margin-top: 0.5rem;">John A. Smith</div>
                            </div>
                            <div>
                                <div class="signature-line">Date</div>
                                <div style="margin-top: 0.5rem;">June 23, 2025</div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Social Security Benefits Calculator -->
            <div class="financial-section" id="social-security">
                <div class="section-title">
                    <i class="fas fa-hand-holding-usd"></i>
                    <h2>Social Security Benefits Calculator</h2>
                </div>
                
                <p>Calculate your optimal Social Security claiming age to maximize your lifetime benefits.</p>
                
                <div class="calculator-container">
                    <div class="calculator-header">
                        <h3>Your Social Security Benefits</h3>
                        <p>Use this calculator to determine the best age to start receiving your Social Security benefits.</p>
                    </div>
                    
                    <div class="calculator-form">
                        <div class="input-group">
                            <label for="birth-year">Year of Birth</label>
                            <input type="number" id="birth-year" value="1958" min="1900" max="2000">
                        </div>
                        
                        <div class="input-group input-addon">
                            <label for="pia">Primary Insurance Amount (PIA)</label>
                            <input type="number" id="pia" value="1750">
                            <div class="helper-text">Your estimated benefit at full retirement age</div>
                        </div>
                        
                        <div class="input-group">
                            <label for="life-expectancy-ss">Life Expectancy</label>
                            <input type="number" id="life-expectancy-ss" value="85" min="65" max="100">
                        </div>
                        
                        <div class="input-group">
                            <label for="marital-status">Marital Status</label>
                            <select id="marital-status">
                                <option value="single">Single</option>
                                <option value="married">Married</option>
                                <option value="widowed">Widowed</option>
                                <option value="divorced">Divorced (marriage lasted 10+ years)</option>
                            </select>
                        </div>
                        
                        <div class="input-group input-addon">
                            <label for="spouse-benefit">Spouse's PIA (if applicable)</label>
                            <input type="number" id="spouse-benefit" value="0">
                        </div>
                        
                        <div class="input-group">
                            <label for="other-income">Other Income Sources</label>
                            <select id="other-income">
                                <option value="yes">Yes, I have other retirement income</option>
                                <option value="no">No, Social Security is my main income</option>
                            </select>
                        </div>
                    </div>
                    
                    <div class="calculator-actions">
                        <button class="calc-btn calc-primary" onclick="calculateSocialSecurity()">
                            <i class="fas fa-calculator"></i> Calculate Optimal Age
                        </button>
                        <button class="calc-btn calc-secondary" onclick="resetSocialSecurityForm()">
                            <i class="fas fa-redo"></i> Reset
                        </button>
                    </div>
                    
                    <div class="result-container" id="social-security-results">
                        <div class="result-title">Your Social Security Benefits Analysis</div>
                        
                        <div class="result-grid">
                            <div class="result-item">
                                <div class="result-label">Full Retirement Age</div>
                                <div class="result-value" id="full-retirement-age">66 and 8 months</div>
                            </div>
                            
                            <div class="result-item">
                                <div class="result-label">Optimal Claiming Age</div>
                                <div class="result-value" id="optimal-claiming-age">70</div>
                            </div>
                            
                            <div class="result-item">
                                <div class="result-label">Monthly Benefit at Optimal Age</div>
                                <div class="result-value" id="optimal-monthly-benefit">$2,310</div>
                            </div>
                            
                            <div class="result-item">
                                <div class="result-label">Lifetime Benefit Projection</div>
                                <div class="result-value" id="lifetime-benefit">$485,100</div>
                            </div>
                        </div>
                        
                        <div class="result-chart-container">
                            <canvas id="social-security-chart"></canvas>
                        </div>
                        
                        <div style="margin-top: 2rem; padding: 1rem; background: #ebf8ff; border-radius: 8px; border-left: 4px solid #0096ad;">
                            <h4 style="color: #0096ad;">Personalized Recommendation</h4>
                            <p id="ss-recommendation" style="margin-top: 0.5rem;">
                                Based on your input, waiting until age 70 to claim benefits would maximize your lifetime benefits. This would increase your monthly benefit by 32% compared to claiming at your full retirement age. However, if you need income sooner, claiming at your full retirement age of 66 and 8 months would still provide a substantial benefit.
                            </p>
                        </div>
                        
                        <table class="financial-table" style="margin-top: 2rem;">
                            <thead>
                                <tr>
                                    <th>Claiming Age</th>
                                    <th>Monthly Benefit</th>
                                    <th>Annual Benefit</th>
                                    <th>% of Full Benefit</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td>62</td>
                                    <td>$1,313</td>
                                    <td>$15,756</td>
                                    <td>75%</td>
                                </tr>
                                <tr>
                                    <td>66 and 8 months (FRA)</td>
                                    <td>$1,750</td>
                                    <td>$21,000</td>
                                    <td>100%</td>
                                </tr>
                                <tr>
                                    <td>70</td>
                                    <td>$2,310</td>
                                    <td>$27,720</td>
                                    <td>132%</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
            
            <!-- Required Minimum Distribution Calculator -->
            <div class="financial-section" id="rmd-calculator">
                <div class="section-title">
                    <i class="fas fa-chart-line"></i>
                    <h2>Required Minimum Distribution (RMD) Calculator</h2>
                </div>
                
                <p>Calculate your mandatory withdrawals from retirement accounts to avoid IRS penalties. After age 73, the IRS requires you to withdraw a minimum amount each year.</p>
                
                <div class="calculator-container">
                    <div class="calculator-header">
                        <h3>Calculate Your Required Minimum Distributions</h3>
                        <p>Enter your information to determine how much you need to withdraw from your retirement accounts this year.</p>
                    </div>
                    
                    <div class="calculator-form">
                        <div class="input-group">
                            <label for="rmd-age">Your Current Age</label>
                            <input type="number" id="rmd-age" value="75" min="70" max="120">
                        </div>
                        
                        <div class="input-group input-addon">
                            <label for="ira-balance">Traditional IRA Balance</label>
                            <input type="number" id="ira-balance" value="142000">
                        </div>
                        
                        <div class="input-group input-addon">
                            <label for="401k-balance">401(k) Balance</label>
                            <input type="number" id="401k-balance" value="210000">
                        </div>
                        
                        <div class="input-group">
                            <label for="account-types">Account Types</label>
                            <select id="account-types" multiple>
                                <option value="trad-ira" selected>Traditional IRA</option>
                                <option value="401k" selected>401(k)</option>
                                <option value="403b">403(b)</option>
                                <option value="457b">457(b)</option>
                                <option value="sep">SEP IRA</option>
                                <option value="simple">SIMPLE IRA</option>
                            </select>
                            <div class="helper-text">Hold Ctrl/Cmd to select multiple</div>
                        </div>
                    </div>
                    
                    <div class="calculator-actions">
                        <button class="calc-btn calc-primary" onclick="calculateRMD()">
                            <i class="fas fa-calculator"></i> Calculate RMD
                        </button>
                        <button class="calc-btn calc-secondary" onclick="resetRMDForm()">
                            <i class="fas fa-redo"></i> Reset
                        </button>
                    </div>
                    
                    <div class="result-container" id="rmd-results">
                        <div class="result-title">Your Required Minimum Distributions</div>
                        
                        <div class="result-grid">
                            <div class="result-item">
                                <div class="result-label">Total Account Balance</div>
                                <div class="result-value" id="total-retirement-balance">$352,000</div>
                            </div>
                            
                            <div class="result-item">
                                <div class="result-label">Distribution Factor</div>
                                <div class="result-value" id="distribution-factor">22.9</div>
                            </div>
                            
                            <div class="result-item">
                                <div class="result-label">Required Annual Withdrawal</div>
                                <div class="result-value" id="required-withdrawal">$15,371.18</div>
                            </div>
                            
                            <div class="result-item">
                                <div class="result-label">Monthly Withdrawal</div>
                                <div class="result-value" id="monthly-withdrawal">$1,280.93</div>
                            </div>
                        </div>
                        
                        <div style="margin-top: 2rem; padding: 1rem; background: #fff5f5; border-radius: 8px; border-left: 4px solid #e53e3e;">
                            <h4 style="color: #e53e3e;">Important Reminder</h4>
                            <p style="margin-top: 0.5rem;">
                                Your first RMD must be taken by April 1st following the year you turn 73. Subsequent RMDs must be taken by December 31st each year. Failure to withdraw the required amount may result in a 25% tax penalty on the amount not withdrawn.
                            </p>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Rest of the sections remain the same (Legal Resources, Upcoming Payments, Financial Advisors, etc.) -->
            <!-- Modified to include more concrete examples and detailed tools -->
        </div>
    </main>
    
    <?php include '../includes/footer.php'; ?>
    
    <!-- Accessibility Panel, Emergency Button, Voice Assistant Button (same as before) -->
    
    <script>
        // Global variables and initialization functions (same as before)
        
        document.addEventListener('DOMContentLoaded', function() {
            // Initialize charts and components
            initRetirementCalculator();
            initMedicalDeductionChart();
            initEstateDistributionChart();
            initSocialSecurityChart();
            
            // Welcome message
            setTimeout(() => {
                showToast("Welcome to Financial & Legal Assistance");
            }, 500);
        });
        
        // Retirement calculator functions
        function initRetirementCalculator() {
            const ctx = document.getElementById('retirement-chart').getContext('2d');
            window.retirementChart = new Chart(ctx, {
                type: 'line',
                data: {
                    labels: Array.from({length: 30}, (_, i) => i + 70),
                    datasets: [{
                        label: 'Remaining Savings',
                        data: calculateSavingsProjection(),
                        borderColor: '#0096ad',
                        backgroundColor: 'rgba(0, 150, 173, 0.1)',
                        borderWidth: 2,
                        fill: true,
                        tension: 0.4
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: {
                            position: 'top',
                        },
                        title: {
                            display: true,
                            text: 'Projected Retirement Savings Over Time'
                        }
                    },
                    scales: {
                        y: {
                            beginAtZero: true,
                            title: {
                                display: true,
                                text: 'Savings Amount ($)'
                            },
                            ticks: {
                                callback: function(value) {
                                    return '$' + value.toLocaleString();
                                }
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
        }
        
        function calculateSavingsProjection() {
            // Sample projection data - would normally use input values
            let savings = 230000;
            const monthlyIncome = 3050;
            const monthlyExpenses = 2500;
            const monthlyDelta = monthlyIncome - monthlyExpenses;
            const investmentReturn = 0.04;
            
            const projection = [];
            
            for (let i = 0; i < 30; i++) {
                projection.push(Math.max(0, savings));
                
                // Monthly changes for a year
                for (let month = 0; month < 12; month++) {
                    savings += monthlyDelta;
                    savings *= (1 + (investmentReturn / 12));
                }
            }
            
            return projection;
        }
        
        function calculateRetirement() {
            // In a real implementation, this would use the input values
            // For demo purposes, we'll just show the pre-populated results
            document.getElementById('retirement-results').style.display = 'block';
            
            // Here we would calculate real results based on input values
            // and update the result fields and chart
            
            showToast("Retirement calculation complete!");
            
            // Scroll to results
            document.getElementById('retirement-results').scrollIntoView({behavior: 'smooth', block: 'start'});
        }
        
        function resetRetirementForm() {
            // Reset form fields to default values
            document.getElementById('current-age').value = '67';
            document.getElementById('retirement-age').value = '70';
            document.getElementById('life-expectancy').value = '85';
            document.getElementById('social-security').value = '1850';
            document.getElementById('pension').value = '1200';
            document.getElementById('monthly-expenses').value = '2500';
            document.getElementById('total-savings').value = '230000';
            document.getElementById('inflation-rate').value = '2.5';
            document.getElementById('investment-return').value = '4.0';
            
            // Hide results
            document.getElementById('retirement-results').style.display = 'none';
            
            showToast("Form reset!");
        }
        
        // Medical tax deduction calculator
        function initMedicalDeductionChart() {
            const ctx = document.getElementById('deduction-chart').getContext('2d');
            
            const totalMedical = 4700.60; // Sample total from PHP
            const agiThreshold = 3150; // Sample threshold (7.5% of $42,000)
            const deductibleAmount = totalMedical - agiThreshold;
            
            window.deductionChart = new Chart(ctx, {
                type: 'bar',
                data: {
                    labels: ['Medical Expenses', 'Threshold (7.5% of AGI)', 'Deductible Amount'],
                    datasets: [{
                        label: 'Amount ($)',
                        data: [totalMedical, agiThreshold, Math.max(0, deductibleAmount)],
                        backgroundColor: [
                            'rgba(54, 162, 235, 0.5)',
                            'rgba(255, 99, 132, 0.5)',
                            'rgba(75, 192, 192, 0.5)'
                        ],
                        borderColor: [
                            'rgba(54, 162, 235, 1)',
                            'rgba(255, 99, 132, 1)',
                            'rgba(75, 192, 192, 1)'
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
                                    return '$' + value.toLocaleString();
                                }// filepath: c:\xampp\htdocs\Astha\elderly\fin\financial-legal-assistance.php
                            }                       