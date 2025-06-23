<?php
session_start();

// Include required files
require_once '../../login_user/functions.php';
require_once '../../signup_user/config.php';

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

// Process calculator form submissions
$calculatorResults = [];
$calculatorErrors = [];

// Fixed Deposit Calculator
if (isset($_POST['calculate_fd'])) {
    $principal = filter_input(INPUT_POST, 'fd_principal', FILTER_VALIDATE_FLOAT);
    $interestRate = filter_input(INPUT_POST, 'fd_interest_rate', FILTER_VALIDATE_FLOAT);
    $term = filter_input(INPUT_POST, 'fd_term', FILTER_VALIDATE_INT);

    if ($principal && $interestRate && $term) {
        $interestRate = $interestRate / 100; // Convert percentage to decimal
        $maturityAmount = $principal * pow((1 + $interestRate), $term);
        $interestEarned = $maturityAmount - $principal;

        $calculatorResults['fd'] = [
            'principal' => number_format($principal, 2),
            'interest_rate' => number_format($interestRate * 100, 2),
            'term' => $term,
            'maturity_amount' => number_format($maturityAmount, 2),
            'interest_earned' => number_format($interestEarned, 2)
        ];
        
        // Save to database
        if ($user_id > 0) {
            saveCalculationToDatabase(
                $conn,
                $user_id,
                'fixed_deposit',
                json_encode($calculatorResults['fd']),
                json_encode([
                    'principal' => $principal,
                    'interest_rate' => $interestRate * 100,
                    'term' => $term
                ])
            );
        }
    } else {
        $calculatorErrors['fd'] = 'Please enter valid values for all fields.';
    }
}

// Monthly Budget Calculator
if (isset($_POST['calculate_budget'])) {
    $income = filter_input(INPUT_POST, 'monthly_income', FILTER_VALIDATE_FLOAT);
    $housing = filter_input(INPUT_POST, 'housing_expense', FILTER_VALIDATE_FLOAT);
    $utilities = filter_input(INPUT_POST, 'utilities_expense', FILTER_VALIDATE_FLOAT);
    $food = filter_input(INPUT_POST, 'food_expense', FILTER_VALIDATE_FLOAT);
    $healthcare = filter_input(INPUT_POST, 'healthcare_expense', FILTER_VALIDATE_FLOAT);
    $transport = filter_input(INPUT_POST, 'transport_expense', FILTER_VALIDATE_FLOAT);
    $other = filter_input(INPUT_POST, 'other_expense', FILTER_VALIDATE_FLOAT);

    if ($income !== false) {
        // Set any missing expenses to 0
        $housing = $housing ?: 0;
        $utilities = $utilities ?: 0;
        $food = $food ?: 0;
        $healthcare = $healthcare ?: 0;
        $transport = $transport ?: 0;
        $other = $other ?: 0;

        $totalExpenses = $housing + $utilities + $food + $healthcare + $transport + $other;
        $remainingBudget = $income - $totalExpenses;
        
        // Calculate percentages for pie chart
        $expensePercentages = [
            'housing' => ($totalExpenses > 0 && $housing > 0) ? round(($housing / $totalExpenses) * 100) : 0,
            'utilities' => ($totalExpenses > 0 && $utilities > 0) ? round(($utilities / $totalExpenses) * 100) : 0,
            'food' => ($totalExpenses > 0 && $food > 0) ? round(($food / $totalExpenses) * 100) : 0,
            'healthcare' => ($totalExpenses > 0 && $healthcare > 0) ? round(($healthcare / $totalExpenses) * 100) : 0,
            'transport' => ($totalExpenses > 0 && $transport > 0) ? round(($transport / $totalExpenses) * 100) : 0,
            'other' => ($totalExpenses > 0 && $other > 0) ? round(($other / $totalExpenses) * 100) : 0
        ];
        
        $calculatorResults['budget'] = [
            'income' => number_format($income, 2),
            'housing' => number_format($housing, 2),
            'utilities' => number_format($utilities, 2),
            'food' => number_format($food, 2),
            'healthcare' => number_format($healthcare, 2),
            'transport' => number_format($transport, 2),
            'other' => number_format($other, 2),
            'total_expenses' => number_format($totalExpenses, 2),
            'remaining_budget' => number_format($remainingBudget, 2),
            'expense_percentages' => $expensePercentages
        ];
        
        // Save to database
        if ($user_id > 0) {
            saveCalculationToDatabase(
                $conn,
                $user_id,
                'monthly_budget',
                json_encode($calculatorResults['budget']),
                json_encode([
                    'income' => $income,
                    'housing' => $housing,
                    'utilities' => $utilities,
                    'food' => $food,
                    'healthcare' => $healthcare,
                    'transport' => $transport,
                    'other' => $other
                ])
            );
        }
    } else {
        $calculatorErrors['budget'] = 'Please enter a valid monthly income.';
    }
}

// Retirement Income Calculator
if (isset($_POST['calculate_retirement'])) {
    $currentAge = filter_input(INPUT_POST, 'current_age', FILTER_VALIDATE_INT);
    $retirementAge = filter_input(INPUT_POST, 'retirement_age', FILTER_VALIDATE_INT);
    $currentSavings = filter_input(INPUT_POST, 'current_savings', FILTER_VALIDATE_FLOAT);
    $monthlySavings = filter_input(INPUT_POST, 'monthly_savings', FILTER_VALIDATE_FLOAT);
    $expectedReturn = filter_input(INPUT_POST, 'expected_return', FILTER_VALIDATE_FLOAT);
    $expectedInflation = filter_input(INPUT_POST, 'expected_inflation', FILTER_VALIDATE_FLOAT);
    $lifeExpectancy = filter_input(INPUT_POST, 'life_expectancy', FILTER_VALIDATE_INT);

    if ($currentAge && $retirementAge && $currentSavings !== false && $monthlySavings !== false && 
        $expectedReturn && $expectedInflation && $lifeExpectancy) {
        
        $yearsToRetirement = $retirementAge - $currentAge;
        $retirementDuration = $lifeExpectancy - $retirementAge;
        
        // Annual savings
        $annualSavings = $monthlySavings * 12;
        
        // Convert percentages to decimal
        $expectedReturn = $expectedReturn / 100;
        $expectedInflation = $expectedInflation / 100;
        
        // Real rate of return (adjusted for inflation)
        $realRateOfReturn = (1 + $expectedReturn) / (1 + $expectedInflation) - 1;
        
        // Calculate future value of current savings
        $futureValueOfCurrentSavings = $currentSavings * pow((1 + $realRateOfReturn), $yearsToRetirement);
        
        // Calculate future value of regular savings
        $futureValueOfSavings = 0;
        for ($i = 0; $i < $yearsToRetirement; $i++) {
            $futureValueOfSavings += $annualSavings * pow((1 + $realRateOfReturn), $i);
        }
        
        // Total retirement corpus
        $totalRetirementCorpus = $futureValueOfCurrentSavings + $futureValueOfSavings;
        
        // Monthly income during retirement (simple calculation)
        $monthlyRetirementIncome = ($totalRetirementCorpus / $retirementDuration) / 12;
        
        $calculatorResults['retirement'] = [
            'years_to_retirement' => $yearsToRetirement,
            'retirement_duration' => $retirementDuration,
            'future_value_current_savings' => number_format($futureValueOfCurrentSavings, 2),
            'future_value_savings' => number_format($futureValueOfSavings, 2),
            'total_retirement_corpus' => number_format($totalRetirementCorpus, 2),
            'monthly_retirement_income' => number_format($monthlyRetirementIncome, 2)
        ];
        
        // Save to database
        if ($user_id > 0) {
            saveCalculationToDatabase(
                $conn,
                $user_id,
                'retirement_income',
                json_encode($calculatorResults['retirement']),
                json_encode([
                    'current_age' => $currentAge,
                    'retirement_age' => $retirementAge,
                    'current_savings' => $currentSavings,
                    'monthly_savings' => $monthlySavings,
                    'expected_return' => $expectedReturn * 100,
                    'expected_inflation' => $expectedInflation * 100,
                    'life_expectancy' => $lifeExpectancy
                ])
            );
        }
    } else {
        $calculatorErrors['retirement'] = 'Please enter valid values for all fields.';
    }
}

// Medication Cost Calculator
if (isset($_POST['calculate_medicine'])) {
    $regularMedicines = filter_input(INPUT_POST, 'regular_medicines', FILTER_VALIDATE_FLOAT);
    $specialistMedicines = filter_input(INPUT_POST, 'specialist_medicines', FILTER_VALIDATE_FLOAT);
    $otcMedicines = filter_input(INPUT_POST, 'otc_medicines', FILTER_VALIDATE_FLOAT);
    $hasInsurance = isset($_POST['has_insurance']);
    $insuranceCoverage = filter_input(INPUT_POST, 'insurance_coverage', FILTER_VALIDATE_FLOAT);
    
    // Set any missing values to 0
    $regularMedicines = $regularMedicines ?: 0;
    $specialistMedicines = $specialistMedicines ?: 0;
    $otcMedicines = $otcMedicines ?: 0;
    $insuranceCoverage = ($hasInsurance && $insuranceCoverage !== false) ? $insuranceCoverage : 0;
    
    // Calculate total medication cost
    $totalMedicineCost = $regularMedicines + $specialistMedicines + $otcMedicines;
    
    // Calculate insurance coverage amount
    $insuranceAmount = 0;
    if ($hasInsurance && $insuranceCoverage > 0) {
        $insuranceAmount = ($regularMedicines + $specialistMedicines) * ($insuranceCoverage / 100);
    }
    
    // Calculate out-of-pocket cost
    $outOfPocketCost = $totalMedicineCost - $insuranceAmount;
    
    // Calculate monthly and yearly costs
    $monthlyCost = $outOfPocketCost;
    $yearlyCost = $monthlyCost * 12;
    
    $calculatorResults['medicine'] = [
        'regular_medicines' => number_format($regularMedicines, 2),
        'specialist_medicines' => number_format($specialistMedicines, 2),
        'otc_medicines' => number_format($otcMedicines, 2),
        'total_medicine_cost' => number_format($totalMedicineCost, 2),
        'insurance_amount' => number_format($insuranceAmount, 2),
        'out_of_pocket' => number_format($outOfPocketCost, 2),
        'monthly_cost' => number_format($monthlyCost, 2),
        'yearly_cost' => number_format($yearlyCost, 2)
    ];
    
    // Save to database
    if ($user_id > 0) {
        saveCalculationToDatabase(
            $conn,
            $user_id,
            'medication_cost',
            json_encode($calculatorResults['medicine']),
            json_encode([
                'regular_medicines' => $regularMedicines,
                'specialist_medicines' => $specialistMedicines,
                'otc_medicines' => $otcMedicines,
                'has_insurance' => $hasInsurance,
                'insurance_coverage' => $insuranceCoverage
            ])
        );
    }
}

// Inflation Impact Calculator
if (isset($_POST['calculate_inflation'])) {
    $currentAmount = filter_input(INPUT_POST, 'current_amount', FILTER_VALIDATE_FLOAT);
    $inflationRate = filter_input(INPUT_POST, 'inflation_rate', FILTER_VALIDATE_FLOAT);
    $years = filter_input(INPUT_POST, 'inflation_years', FILTER_VALIDATE_INT);
    
    if ($currentAmount && $inflationRate && $years) {
        // Convert percentage to decimal
        $inflationRate = $inflationRate / 100;
        
        // Calculate future value considering inflation
        $futureValue = $currentAmount * pow((1 + $inflationRate), $years);
        $purchasingPowerLoss = $futureValue - $currentAmount;
        $purchasingPowerPercentage = ($currentAmount / $futureValue) * 100;
        
        $yearlyBreakdown = [];
        for ($i = 1; $i <= $years; $i++) {
            $yearValue = $currentAmount * pow((1 + $inflationRate), $i);
            $yearlyBreakdown[] = [
                'year' => $i,
                'value' => number_format($yearValue, 2),
                'purchasing_power' => number_format(($currentAmount / $yearValue) * 100, 1)
            ];
        }
        
        $calculatorResults['inflation'] = [
            'current_amount' => number_format($currentAmount, 2),
            'inflation_rate' => number_format($inflationRate * 100, 2),
            'years' => $years,
            'future_value' => number_format($futureValue, 2),
            'purchasing_power_loss' => number_format($purchasingPowerLoss, 2),
            'purchasing_power_percentage' => number_format($purchasingPowerPercentage, 1),
            'yearly_breakdown' => $yearlyBreakdown
        ];
        
        // Save to database
        if ($user_id > 0) {
            saveCalculationToDatabase(
                $conn,
                $user_id,
                'inflation_impact',
                json_encode($calculatorResults['inflation']),
                json_encode([
                    'current_amount' => $currentAmount,
                    'inflation_rate' => $inflationRate * 100,
                    'years' => $years
                ])
            );
        }
    } else {
        $calculatorErrors['inflation'] = 'Please enter valid values for all fields.';
    }
}

// Pension Calculator
if (isset($_POST['calculate_pension'])) {
    $monthlyPension = filter_input(INPUT_POST, 'monthly_pension', FILTER_VALIDATE_FLOAT);
    $otherIncome = filter_input(INPUT_POST, 'other_income', FILTER_VALIDATE_FLOAT);
    $expectedIncrease = filter_input(INPUT_POST, 'pension_increase', FILTER_VALIDATE_FLOAT);
    $pensionYears = filter_input(INPUT_POST, 'pension_years', FILTER_VALIDATE_INT);
    
    // Set any missing values to 0
    $monthlyPension = $monthlyPension ?: 0;
    $otherIncome = $otherIncome ?: 0;
    $expectedIncrease = $expectedIncrease ?: 0;
    $pensionYears = $pensionYears ?: 20; // Default 20 years if not specified
    
    // Convert percentage to decimal
    $expectedIncrease = $expectedIncrease / 100;
    
    // Calculate total monthly income
    $totalMonthlyIncome = $monthlyPension + $otherIncome;
    $totalYearlyIncome = $totalMonthlyIncome * 12;
    
    // Calculate future pension values with expected increases
    $futureValues = [];
    $totalLifetimeIncome = 0;
    
    for ($i = 0; $i < $pensionYears; $i++) {
        $yearPension = $monthlyPension * pow((1 + $expectedIncrease), $i);
        $yearTotalIncome = $yearPension + $otherIncome;
        $yearlyTotal = $yearTotalIncome * 12;
        
        $futureValues[] = [
            'year' => $i + 1,
            'monthly_pension' => number_format($yearPension, 2),
            'total_monthly' => number_format($yearTotalIncome, 2),
            'yearly_total' => number_format($yearlyTotal, 2)
        ];
        
        $totalLifetimeIncome += $yearlyTotal;
    }
    
    $calculatorResults['pension'] = [
        'monthly_pension' => number_format($monthlyPension, 2),
        'other_income' => number_format($otherIncome, 2),
        'total_monthly_income' => number_format($totalMonthlyIncome, 2),
        'total_yearly_income' => number_format($totalYearlyIncome, 2),
        'expected_increase' => number_format($expectedIncrease * 100, 2),
        'pension_years' => $pensionYears,
        'total_lifetime_income' => number_format($totalLifetimeIncome, 2),
        'future_values' => $futureValues
    ];
    
    // Save to database
    if ($user_id > 0) {
        saveCalculationToDatabase(
            $conn,
            $user_id,
            'pension_calculation',
            json_encode($calculatorResults['pension']),
            json_encode([
                'monthly_pension' => $monthlyPension,
                'other_income' => $otherIncome,
                'expected_increase' => $expectedIncrease * 100,
                'pension_years' => $pensionYears
            ])
        );
    }
}

function calculateRetirementNeeds($current_age, $retirement_age, $monthly_income, $monthly_expenses, $current_savings, $expected_return, $inflation_rate) {
    $years_to_retirement = $retirement_age - $current_age;
    $retirement_duration = 85 - $retirement_age; // Assuming life expectancy of 85
    
    // Convert percentages to decimals
    $annual_return = $expected_return / 100;
    $annual_inflation = $inflation_rate / 100;
    
    // Adjust monthly expenses for inflation at retirement
    $adjusted_monthly_expenses = $monthly_expenses * pow(1 + $annual_inflation, $years_to_retirement);
    $adjusted_annual_expenses = $adjusted_monthly_expenses * 12;
    
    // Calculate total funds needed for retirement (adjusted for inflation during retirement)
    $required_fund = 0;
    for ($year = 1; $year <= $retirement_duration; $year++) {
        $year_expense = $adjusted_annual_expenses * pow(1 + $annual_inflation, $year - 1);
        $required_fund += $year_expense / pow(1 + $annual_return, $year);
    }
    
    // Calculate future value of current savings
    $future_savings_value = $current_savings * pow(1 + $annual_return, $years_to_retirement);
    
    // Calculate shortfall
    $shortfall = $required_fund - $future_savings_value;
    
    // Calculate required monthly savings
    $monthly_savings = 0;
    if ($shortfall > 0 && $years_to_retirement > 0) {
        // PMT calculation for annuity
        $monthly_return = $annual_return / 12;
        $total_months = $years_to_retirement * 12;
        
        if ($monthly_return > 0) {
            $monthly_savings = $shortfall * $monthly_return / (pow(1 + $monthly_return, $total_months) - 1);
        } else {
            $monthly_savings = $shortfall / $total_months;
        }
    }
    
    // Generate summary advice
    $summary = "";
    if ($shortfall <= 0) {
        $summary = "Great news! Your current savings are sufficient for your retirement goals. You have an estimated surplus of ৳" . number_format(abs($shortfall)) . ".";
    } else {
        $summary = "You need to save an additional ৳" . number_format($monthly_savings) . " per month to meet your retirement goals.";
    }
    
    return [
        'years_to_retirement' => $years_to_retirement,
        'retirement_duration' => $retirement_duration,
        'required_fund' => $required_fund,
        'future_savings_value' => $future_savings_value,
        'shortfall' => $shortfall,
        'monthly_savings' => $monthly_savings,
        'adjusted_monthly_expenses' => $adjusted_monthly_expenses,
        'summary' => $summary
    ];
}

function calculateSocialSecurityBenefits($birth_year, $years_worked, $avg_monthly_income, $retirement_age) {
    // Bangladesh doesn't have traditional social security like US, so this is a simplified calculation
    // This would be adapted based on Bangladesh's actual benefit system
    
    $full_retirement_age = 60; // Typical retirement age in Bangladesh
    $max_benefit_years = 35; // Typical maximum contribution years
    
    // Calculate average indexed monthly earnings (simplified)
    $effective_years = min($years_worked, $max_benefit_years);
    $base_benefit = ($avg_monthly_income * $effective_years) / $max_benefit_years * 0.4; // 40% replacement ratio
    
    // Adjust for early/late retirement
    if ($retirement_age < $full_retirement_age) {
        $reduction_factor = 1 - (($full_retirement_age - $retirement_age) * 0.05); // 5% reduction per year
        $monthly_benefit = $base_benefit * $reduction_factor;
    } elseif ($retirement_age > $full_retirement_age) {
        $increase_factor = 1 + (($retirement_age - $full_retirement_age) * 0.08); // 8% increase per year
        $monthly_benefit = $base_benefit * $increase_factor;
    } else {
        $monthly_benefit = $base_benefit;
    }
    
    $annual_benefit = $monthly_benefit * 12;
    $lifetime_benefit = $annual_benefit * (85 - $retirement_age); // Assuming life expectancy of 85
    
    return [
        'monthly_benefit' => $monthly_benefit,
        'annual_benefit' => $annual_benefit,
        'lifetime_benefit' => $lifetime_benefit,
        'full_retirement_age' => $full_retirement_age,
        'effective_years' => $effective_years
    ];
}

function calculateMedicalExpenses($monthly_medication, $doctor_visits, $hospital_costs, $insurance_premium, $other_expenses = 0) {
    $annual_medication = $monthly_medication * 12;
    $annual_doctor_visits = $doctor_visits * 12;
    $annual_insurance = $insurance_premium * 12;
    $annual_other = $other_expenses * 12;
    
    $monthly_total = $monthly_medication + $doctor_visits + $insurance_premium + $other_expenses;
    $annual_total = $annual_medication + $annual_doctor_visits + $hospital_costs + $annual_insurance + $annual_other;
    
    return [
        'monthly_total' => $monthly_total,
        'annual_medication' => $annual_medication,
        'annual_doctor_visits' => $annual_doctor_visits,
        'hospital_costs' => $hospital_costs,
        'annual_insurance' => $annual_insurance,
        'annual_other' => $annual_other,
        'annual_total' => $annual_total
    ];
}

function calculateMonthlyBudget($budget_data) {
    $total_income = array_sum($budget_data['income']);
    $total_expenses = array_sum($budget_data['expenses']);
    $balance = $total_income - $total_expenses;
    $yearly_savings = $balance * 12;
    
    // Generate advice based on budget
    $advice = "";
    if ($balance > 0) {
        $savings_rate = ($balance / $total_income) * 100;
        if ($savings_rate >= 20) {
            $advice = "Excellent! You're saving " . number_format($savings_rate, 1) . "% of your income. This puts you in a strong financial position.";
        } elseif ($savings_rate >= 10) {
            $advice = "Good job! You're saving " . number_format($savings_rate, 1) . "% of your income. Consider increasing this to 20% if possible.";
        } else {
            $advice = "You have a positive balance of ৳" . number_format($balance) . " monthly. Try to increase your savings rate to at least 10%.";
        }
    } elseif ($balance == 0) {
        $advice = "You're breaking even. Consider reviewing your expenses to create some savings for emergencies.";
    } else {
        $advice = "You're spending ৳" . number_format(abs($balance)) . " more than you earn monthly. Review your expenses and consider ways to reduce costs.";
    }
    
    return [
        'total_income' => $total_income,
        'total_expenses' => $total_expenses,
        'balance' => $balance,
        'yearly_savings' => $yearly_savings,
        'advice' => $advice
    ];
}

function saveCalculationToDatabase($conn, $user_id, $calculation_type, $result, $inputs) {
    $sql = "INSERT INTO financial_calculations 
            (user_id, calculation_type, calculation_result, calculation_inputs, created_at) 
            VALUES (?, ?, ?, ?, NOW())";
    
    $stmt = $conn->prepare($sql);
    if ($stmt) {
        $stmt->bind_param("isss", $user_id, $calculation_type, $result, $inputs);
        return $stmt->execute();
    }
    return false;
}

function getUserSavedCalculations($conn, $user_id) {
    $sql = "SELECT * FROM financial_calculations 
            WHERE user_id = ? 
            ORDER BY created_at DESC 
            LIMIT 10";
    
    $stmt = $conn->prepare($sql);
    if ($stmt) {
        $stmt->bind_param("i", $user_id);
        $stmt->execute();
        $result = $stmt->get_result();
        
        $calculations = array();
        while ($row = $result->fetch_assoc()) {
            $calculations[] = $row;
        }
        
        return $calculations;
    }
    return array();
}

// Additional calculator functions can be added here...

function calculateFixedDeposit($principal, $interest_rate, $tenure_months) {
    $monthly_rate = $interest_rate / 100 / 12;
    $maturity_amount = $principal * pow(1 + $monthly_rate, $tenure_months);
    $interest_earned = $maturity_amount - $principal;
    
    return [
        'principal' => $principal,
        'interest_rate' => $interest_rate,
        'tenure_months' => $tenure_months,
        'maturity_amount' => $maturity_amount,
        'interest_earned' => $interest_earned
    ];
}

function calculateInflationImpact($current_amount, $inflation_rate, $years) {
    $future_value = $current_amount * pow(1 + ($inflation_rate / 100), $years);
    $purchasing_power_loss = $future_value - $current_amount;
    $purchasing_power_percentage = ($current_amount / $future_value) * 100;
    
    // Generate year-by-year breakdown
    $yearly_breakdown = [];
    for ($year = 1; $year <= min($years, 10); $year++) {
        $year_value = $current_amount * pow(1 + ($inflation_rate / 100), $year);
        $year_purchasing_power = ($current_amount / $year_value) * 100;
        $yearly_breakdown[] = [
            'year' => $year,
            'value' => number_format($year_value),
            'purchasing_power' => number_format($year_purchasing_power, 1)
        ];
    }
    
    return [
        'current_amount' => number_format($current_amount),
        'inflation_rate' => $inflation_rate,
        'years' => $years,
        'future_value' => number_format($future_value),
        'purchasing_power_loss' => number_format($purchasing_power_loss),
        'purchasing_power_percentage' => number_format($purchasing_power_percentage, 1),
        'yearly_breakdown' => $yearly_breakdown
    ];
}

function calculatePensionBenefits($monthly_pension, $other_income, $expected_increase, $years) {
    $total_monthly_income = $monthly_pension + $other_income;
    $total_yearly_income = $total_monthly_income * 12;
    
    // Calculate future values with annual increases
    $future_values = [];
    $total_lifetime_income = 0;
    
    for ($year = 1; $year <= $years; $year++) {
        $year_pension = $monthly_pension * pow(1 + ($expected_increase / 100), $year - 1);
        $year_total_monthly = $year_pension + $other_income;
        $year_total_yearly = $year_total_monthly * 12;
        
        $total_lifetime_income += $year_total_yearly;
        
        $future_values[] = [
            'year' => $year,
            'monthly_pension' => number_format($year_pension),
            'total_monthly' => number_format($year_total_monthly),
            'yearly_total' => number_format($year_total_yearly)
        ];
    }
    
    return [
        'monthly_pension' => number_format($monthly_pension),
        'other_income' => number_format($other_income),
        'total_monthly_income' => number_format($total_monthly_income),
        'total_yearly_income' => number_format($total_yearly_income),
        'expected_increase' => $expected_increase,
        'total_lifetime_income' => number_format($total_lifetime_income),
        'future_values' => $future_values
    ];
}

// HTML part begins
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Financial Calculators - Elderly Care Dashboard</title>
    
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
    
    <!-- Chart.js for visualizations -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js@3.7.1/dist/chart.min.js"></script>
    
    <style>
        /* Financial Calculator specific styling */
        :root {
            --primary-color: #0096ad;
            --primary-light: #e6f7fa;
            --primary-dark: #007a8a;
            --secondary-color: #f98e54;
            --text-dark: #2d3748;
            --text-medium: #4a5568;
            --text-light: #718096;
            --success-color: #48bb78;
            --warning-color: #ed8936;
            --danger-color: #e53e3e;
            --border-color: #e2e8f0;
            --bg-light: #f8f9fa;
        }
        
        body {
            font-family: 'Open Sans', sans-serif;
            color: var(--text-dark);
            background-color: #f5f7fa;
        }
        
        .calculator-container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 2rem;
            font-family: 'Open Sans', sans-serif;
        }
        
        .calculator-header {
            text-align: center;
            margin-bottom: 3rem;
            padding-bottom: 1rem;
            border-bottom: 1px solid var(--border-color);
        }
        
        .calculator-header h1 {
            font-family: 'Montserrat', sans-serif;
            font-weight: 700;
            font-size: 2.5rem;
            color: var(--text-dark);
            margin-bottom: 1rem;
        }
        
        .calculator-header p {
            font-size: 1.2rem;
            color: var(--text-medium);
            max-width: 800px;
            margin: 0 auto;
        }
        
        .calculator-tabs {
            display: flex;
            flex-wrap: wrap;
            gap: 0.5rem;
            margin-bottom: 2rem;
            justify-content: center;
        }
        
        .calculator-tab {
            padding: 1rem 1.5rem;
            background: white;
            border: 2px solid var(--border-color);
            border-radius: 12px;
            font-weight: 600;
            font-size: 1.1rem;
            cursor: pointer;
            transition: all 0.3s ease;
            display: flex;
            align-items: center;
            gap: 0.5rem;
            color: var(--text-medium);
        }
        
        .calculator-tab:hover {
            border-color: var(--primary-color);
            color: var(--primary-color);
        }
        
        .calculator-tab.active {
            background: var(--primary-color);
            color: white;
            border-color: var(--primary-color);
        }
        
        .calculator-tab i {
            font-size: 1.3rem;
        }
        
        .calculator-section {
            background: white;
            border-radius: 15px;
            padding: 2rem;
            margin-bottom: 2rem;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.05);
            display: none;
        }
        
        .calculator-section.active {
            display: block;
        }
        
        .section-title {
            display: flex;
            align-items: center;
            margin-bottom: 1.5rem;
            padding-bottom: 0.5rem;
            border-bottom: 2px solid var(--primary-color);
        }
        
        .section-title i {
            font-size: 1.8rem;
            color: var(--primary-color);
            margin-right: 1rem;
        }
        
        .section-title h2 {
            font-family: 'Montserrat', sans-serif;
            font-weight: 600;
            font-size: 1.8rem;
            color: var(--text-dark);
            margin: 0;
        }
        
        .calculator-description {
            color: var(--text-medium);
            margin-bottom: 2rem;
            font-size: 1.1rem;
            line-height: 1.6;
        }
        
        .calculator-form {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
            gap: 1.5rem;
            margin-bottom: 2rem;
        }
        
        .form-group {
            margin-bottom: 1.5rem;
        }
        
        .form-group label {
            display: block;
            margin-bottom: 0.5rem;
            font-weight: 600;
            color: var(--text-dark);
            font-size: 1.05rem;
        }
        
        .form-control {
            width: 100%;
            padding: 0.8rem 1rem;
            border: 2px solid var(--border-color);
            border-radius: 8px;
            font-size: 1.1rem;
            transition: all 0.3s ease;
        }
        
        .form-control:focus {
            outline: none;
            border-color: var(--primary-color);
            box-shadow: 0 0 0 3px rgba(0, 150, 173, 0.1);
        }
        
        .form-text {
            display: block;
            margin-top: 0.5rem;
            color: var(--text-light);
            font-size: 0.9rem;
        }
        
        .has-icon {
            position: relative;
        }
        
        .input-icon {
            position: absolute;
            top: 50%;
            left: 1rem;
            transform: translateY(-50%);
            color: var(--text-medium);
            font-size: 1.1rem;
        }
        
        .has-icon .form-control {
            padding-left: 2.8rem;
        }
        
        .taka-symbol {
            font-family: Arial, sans-serif;
            font-weight: bold;
        }
        
        .calculate-btn {
            background: var(--primary-color);
            color: white;
            border: none;
            border-radius: 8px;
            padding: 1rem 2rem;
            font-size: 1.2rem;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            margin-top: 1rem;
        }
        
        .calculate-btn:hover {
            background: var(--primary-dark);
            transform: translateY(-2px);
        }
        
        .calculate-btn i {
            font-size: 1.3rem;
        }
        
        /* Result card */
        .result-card {
            background: var(--primary-light);
            border-radius: 12px;
            padding: 1.5rem;
            margin-top: 2rem;
            border-left: 5px solid var(--primary-color);
        }
        
        .result-title {
            font-family: 'Montserrat', sans-serif;
            font-weight: 600;
            font-size: 1.3rem;
            color: var(--primary-color);
            margin-bottom: 1.5rem;
            padding-bottom: 0.5rem;
            border-bottom: 1px solid rgba(0, 150, 173, 0.2);
        }
        
        .result-row {
            display: flex;
            justify-content: space-between;
            margin-bottom: 1rem;
            padding-bottom: 0.5rem;
            border-bottom: 1px dashed rgba(0, 0, 0, 0.1);
        }
        
        .result-row:last-child {
            border-bottom: none;
            margin-bottom: 0;
            padding-bottom: 0;
        }
        
        .result-label {
            font-weight: 600;
            color: var(--text-medium);
        }
        
        .result-value {
            font-weight: 700;
            font-size: 1.1rem;
            color: var(--text-dark);
        }
        
        .result-highlight {
            background: var(--primary-color);
            color: white;
            padding: 0.5rem 1rem;
            border-radius: 6px;
            font-weight: 700;
            font-size: 1.2rem;
        }
        
        .result-chart-container {
            height: 300px;
            margin-top: 2rem;
        }
        
        .calculator-tips {
            background: #fff8e6;
            border-radius: 12px;
            padding: 1.5rem;
            margin-top: 2rem;
            border-left: 5px solid #fbbf24;
        }
        
        .tips-title {
            font-family: 'Montserrat', sans-serif;
            font-weight: 600;
            font-size: 1.2rem;
            color: #92400e;
            margin-bottom: 1rem;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }
        
        .tips-title i {
            font-size: 1.3rem;
        }
        
        .tips-list {
            list-style: none;
            padding: 0;
            margin: 0;
        }
        
        .tips-list li {
            position: relative;
            padding-left: 1.5rem;
            margin-bottom: 0.8rem;
            line-height: 1.5;
            color: #6b7280;
        }
        
        .tips-list li::before {
            content: "•";
            position: absolute;
            left: 0;
            color: #fbbf24;
            font-weight: bold;
            font-size: 1.2rem;
        }
        
        /* Slider inputs */
        .range-container {
            margin-bottom: 1rem;
        }
        
        .range-slider {
            width: 100%;
            height: 8px;
            border-radius: 5px;
            background: #ddd;
            outline: none;
            -webkit-appearance: none;
        }
        
        .range-slider::-webkit-slider-thumb {
            -webkit-appearance: none;
            width: 24px;
            height: 24px;
            border-radius: 50%;
            background: var(--primary-color);
            cursor: pointer;
        }
        
        .range-values {
            display: flex;
            justify-content: space-between;
            margin-top: 0.5rem;
            color: var(--text-light);
            font-size: 0.9rem;
        }
        
        .slider-labels {
            display: flex;
            justify-content: space-between;
            margin-top: 0.3rem;
        }
        
        .slider-min, .slider-max {
            font-size: 0.9rem;
            color: var(--text-light);
        }
        
        .slider-value {
            background: var(--primary-color);
            color: white;
            padding: 0.2rem 0.5rem;
            border-radius: 4px;
            font-weight: 600;
            font-size: 0.9rem;
            position: relative;
            margin-top: 0.5rem;
            display: inline-block;
        }
        
        /* Year breakdown table */
        .year-breakdown {
            width: 100%;
            border-collapse: collapse;
            margin-top: 1.5rem;
        }
        
        .year-breakdown th,
        .year-breakdown td {
            padding: 0.8rem 1rem;
            text-align: left;
            border-bottom: 1px solid var(--border-color);
        }
        
        .year-breakdown th {
            background: var(--bg-light);
            font-weight: 600;
            color: var(--text-dark);
        }
        
        .year-breakdown tr:last-child td {
            border-bottom: none;
        }
        
        .year-breakdown tr:hover {
            background-color: var(--primary-light);
        }
        
        /* Toggle for insurance */
        .toggle-container {
            display: flex;
            align-items: center;
            margin-bottom: 1.5rem;
        }
        
        .toggle-label {
            font-weight: 600;
            color: var(--text-dark);
            margin-right: 1rem;
        }
        
        .toggle-switch {
            position: relative;
            display: inline-block;
            width: 60px;
            height: 34px;
        }
        
        .toggle-switch input {
            opacity: 0;
            width: 0;
            height: 0;
        }
        
        .toggle-slider {
            position: absolute;
            cursor: pointer;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background-color: #ccc;
            transition: .4s;
            border-radius: 34px;
        }
        
        .toggle-slider:before {
            position: absolute;
            content: "";
            height: 26px;
            width: 26px;
            left: 4px;
            bottom: 4px;
            background-color: white;
            transition: .4s;
            border-radius: 50%;
        }
        
        input:checked + .toggle-slider {
            background-color: var(--primary-color);
        }
        
        input:checked + .toggle-slider:before {
            transform: translateX(26px);
        }
        
        .toggle-text {
            margin-left: 0.5rem;
            font-weight: 600;
        }
        
        .toggle-text.on {
            color: var(--primary-color);
        }
        
        .toggle-text.off {
            color: var(--text-light);
        }
        
        /* Responsive table */
        .table-responsive {
            overflow-x: auto;
            margin-top: 1.5rem;
        }
        
        /* Voice Assistant */
        .voice-assistant {
            position: fixed;
            bottom: 30px;
            right: 30px;
            width: 60px;
            height: 60px;
            border-radius: 50%;
            background: var(--primary-color);
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
            background: var(--primary-color);
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
            background: var(--danger-color);
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
        
        /* Print button */
        .print-results {
            background: #fff;
            color: var(--primary-color);
            border: 2px solid var(--primary-color);
            border-radius: 8px;
            padding: 0.8rem 1.5rem;
            font-size: 1.1rem;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            margin-top: 1rem;
        }
        
        .print-results:hover {
            background: var(--primary-light);
        }
        
        /* Save button */
        .save-results {
            background: var(--success-color);
            color: white;
            border: none;
            border-radius: 8px;
            padding: 0.8rem 1.5rem;
            font-size: 1.1rem;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            margin-top: 1rem;
            margin-left: 1rem;
        }
        
        .save-results:hover {
            background: #3da066;
        }
        
        /* Error message */
        .error-message {
            background-color: #fef2f2;
            color: var(--danger-color);
            padding: 1rem;
            border-radius: 8px;
            margin-bottom: 1.5rem;
            border-left: 4px solid var(--danger-color);
        }
        
        /* Calculator wizard */
        .calculator-wizard-steps {
            display: flex;
            overflow: auto;
            margin-bottom: 2rem;
            padding-bottom: 1rem;
        }
        
        .wizard-step {
            min-width: 100px;
            text-align: center;
            padding: 0.8rem 1.5rem;
            background: white;
            border: 1px solid var(--border-color);
            margin-right: 2px;
            position: relative;
            cursor: pointer;
            transition: all 0.3s ease;
        }
        
        .wizard-step:first-child {
            border-top-left-radius: 8px;
            border-bottom-left-radius: 8px;
        }
        
        .wizard-step:last-child {
            border-top-right-radius: 8px;
            border-bottom-right-radius: 8px;
            margin-right: 0;
        }
        
        .wizard-step.active {
            background: var(--primary-color);
            color: white;
            border-color: var(--primary-color);
        }
        
        .wizard-step-number {
            display: inline-block;
            width: 30px;
            height: 30px;
            line-height: 30px;
            border-radius: 50%;
            background: var(--primary-light);
            color: var(--primary-color);
            font-weight: 600;
            margin-bottom: 0.5rem;
        }
        
        .wizard-step.active .wizard-step-number {
            background: white;
        }
        
        .wizard-step-title {
            font-weight: 600;
            white-space: nowrap;
        }
        
        .wizard-content {
            display: none;
        }
        
        .wizard-content.active {
            display: block;
        }
        
        .wizard-buttons {
            display: flex;
            justify-content: space-between;
            margin-top: 2rem;
        }
        
        .wizard-btn {
            padding: 0.8rem 1.5rem;
            border-radius: 8px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
            border: 2px solid var(--primary-color);
            font-size: 1.1rem;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }
        
        .wizard-prev {
            background: white;
            color: var(--primary-color);
        }
        
        .wizard-next {
            background: var(--primary-color);
            color: white;
        }
        
        .wizard-prev:hover {
            background: var(--primary-light);
        }
        
        .wizard-next:hover {
            background: var(--primary-dark);
        }
        
        /* Help tooltips */
        .help-tooltip {
            position: relative;
            display: inline-block;
            margin-left: 0.5rem;
            color: var(--primary-color);
            cursor: pointer;
        }
        
        .tooltip-text {
            visibility: hidden;
            width: 250px;
            background-color: #2d3748;
            color: #fff;
            text-align: left;
            border-radius: 6px;
            padding: 0.8rem;
            position: absolute;
            z-index: 1;
            bottom: 125%;
            left: 50%;
            transform: translateX(-50%);
            opacity: 0;
            transition: opacity 0.3s;
            font-weight: 400;
            font-size: 0.9rem;
            line-height: 1.5;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.2);
        }
        
        .tooltip-text::after {
            content: "";
            position: absolute;
            top: 100%;
            left: 50%;
            margin-left: -5px;
            border-width: 5px;
            border-style: solid;
            border-color: #2d3748 transparent transparent transparent;
        }
        
        .help-tooltip:hover .tooltip-text {
            visibility: visible;
            opacity: 1;
        }
        
        /* Fix for mobile nav */
        .mobile-nav {
            overflow-y: auto;
            max-height: 80vh;
            padding-bottom: 4rem;
            position: fixed;
            bottom: 0;
            left: 0;
            width: 100%;
            background: white;
            z-index: 100;
            box-shadow: 0 -2px 10px rgba(0, 0, 0, 0.1);
            display: none;
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
            color: var(--primary-color);
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
        
        .mobile-nav-items {
            padding-bottom: 70px; /* Add padding to ensure items aren't hidden behind buttons */
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
            width: 50px;
            height: 50px;
            margin-bottom: 0.8rem;
            border: none;
            border-radius: 10px;
            background: #f8f9fa;
            cursor: pointer;
            transition: all 0.3s ease;
            font-size: 1.3rem;
            color: #4a5568;
        }
        
        .accessibility-btn:hover {
            background: var(--primary-color);
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
        
        body.high-contrast .calculator-section,
        body.high-contrast .calculator-tab {
            background: #000 !important;
            border-color: #fff !important;
            color: #fff !important;
        }
        
        body.high-contrast h1, 
        body.high-contrast h2, 
        body.high-contrast .result-label,
        body.high-contrast .result-value {
            color: #fff !important;
        }
        
        body.high-contrast .form-control {
            background: #333 !important;
            color: #fff !important;
            border-color: #666 !important;
        }
        
        /* Responsive design */
        @media (max-width: 992px) {
            .calculator-container {
                padding: 1.5rem;
            }
            
            .calculator-form {
                grid-template-columns: 1fr;
            }
            
            .result-row {
                flex-direction: column;
            }
            
            .result-value {
                margin-top: 0.5rem;
            }
        }
        
        @media (max-width: 768px) {
            .calculator-tabs {
                flex-direction: column;
                gap: 0.5rem;
            }
            
            .calculator-container {
                padding: 1rem;
            }
            
            .calculator-section {
                padding: 1.5rem;
            }
            
            .accessibility-panel {
                display: none;
            }
            
            .calculator-tab {
                width: 100%;
                justify-content: center;
            }
            
            .wizard-step-title {
                font-size: 0.9rem;
            }
        }
        
        /* Add Bengali font support */
        @font-face {
            font-family: 'Kalpurush';
            src: url('https://fonts.maateen.me/kalpurush/font.css');
        }
        
        .bn {
            font-family: 'Kalpurush', Arial, sans-serif;
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
        <div class="calculator-container">
            <div class="calculator-header">
                <h1>Financial Calculators</h1>
                <p>Easy-to-use calculators to help you manage your finances, plan for retirement, and make informed financial decisions.</p>
            </div>
            
            <!-- Calculator Tabs -->
            <div class="calculator-tabs">
                <div class="calculator-tab active" onclick="showCalculator('fixed-deposit')">
                    <i class="fas fa-piggy-bank"></i>
                    <span>Fixed Deposit</span>
                </div>
                <div class="calculator-tab" onclick="showCalculator('budget')">
                    <i class="fas fa-calculator"></i>
                    <span>Monthly Budget</span>
                </div>
                <div class="calculator-tab" onclick="showCalculator('retirement')">
                    <i class="fas fa-umbrella-beach"></i>
                    <span>Retirement</span>
                </div>
                <div class="calculator-tab" onclick="showCalculator('medicine')">
                    <i class="fas fa-pills"></i>
                    <span>Medicine Cost</span>
                </div>
                <div class="calculator-tab" onclick="showCalculator('inflation')">
                    <i class="fas fa-chart-line"></i>
                    <span>Inflation</span>
                </div>
                <div class="calculator-tab" onclick="showCalculator('pension')">
                    <i class="fas fa-money-bill-wave"></i>
                    <span>Pension</span>
                </div>
            </div>
            
            <!-- Fixed Deposit Calculator -->
            <div class="calculator-section active" id="fixed-deposit-calculator">
                <div class="section-title">
                    <i class="fas fa-piggy-bank"></i>
                    <h2>Fixed Deposit Calculator</h2>
                </div>
                
                <p class="calculator-description">
                    Calculate the maturity amount and interest earned on your fixed deposit. Enter your principal amount, interest rate, and term to see how much your money will grow.
                </p>
                
                <?php if (isset($calculatorErrors['fd'])): ?>
                    <div class="error-message">
                        <i class="fas fa-exclamation-circle"></i> <?php echo $calculatorErrors['fd']; ?>
                    </div>
                <?php endif; ?>
                
                <form method="POST" action="">
                    <div class="calculator-form">
                        <div class="form-group">
                            <label for="fd_principal">Principal Amount (৳)</label>
                            <div class="has-icon">
                                <i class="input-icon taka-symbol">৳</i>
                                <input type="number" id="fd_principal" name="fd_principal" class="form-control" placeholder="Enter amount" min="1000" required>
                            </div>
                            <small class="form-text">Minimum ৳1,000</small>
                        </div>
                        
                        <div class="form-group">
                            <label for="fd_interest_rate">Interest Rate (% per year)</label>
                            <div class="has-icon">
                                <i class="input-icon fas fa-percent"></i>
                                <input type="number" id="fd_interest_rate" name="fd_interest_rate" class="form-control" placeholder="Enter interest rate" min="0.1" step="0.1" max="20" required>
                            </div>
                            <small class="form-text">Current bank rates range from 6% to 9%</small>
                        </div>
                        
                        <div class="form-group">
                            <label for="fd_term">Term (Years)</label>
                            <div class="has-icon">
                                <i class="input-icon fas fa-calendar"></i>
                                <input type="number" id="fd_term" name="fd_term" class="form-control" placeholder="Enter term in years" min="1" max="20" required>
                            </div>
                            <small class="form-text">1 to 20 years</small>
                        </div>
                    </div>
                    
                    <button type="submit" name="calculate_fd" class="calculate-btn">
                        <i class="fas fa-calculator"></i> Calculate
                    </button>
                </form>
                
                <?php if (isset($calculatorResults['fd'])): ?>
                    <div class="result-card">
                        <h3 class="result-title">Fixed Deposit Results</h3>
                        
                        <div class="result-row">
                            <span class="result-label">Principal Amount:</span>
                            <span class="result-value">৳ <?php echo $calculatorResults['fd']['principal']; ?></span>
                        </div>
                        
                        <div class="result-row">
                            <span class="result-label">Interest Rate:</span>
                            <span class="result-value"><?php echo $calculatorResults['fd']['interest_rate']; ?>% per year</span>
                        </div>
                        
                        <div class="result-row">
                            <span class="result-label">Term:</span>
                            <span class="result-value"><?php echo $calculatorResults['fd']['term']; ?> years</span>
                        </div>
                        
                        <div class="result-row">
                            <span class="result-label">Interest Earned:</span>
                            <span class="result-value">৳ <?php echo $calculatorResults['fd']['interest_earned']; ?></span>
                        </div>
                        
                        <div class="result-row">
                            <span class="result-label">Maturity Amount:</span>
                            <span class="result-value result-highlight">৳ <?php echo $calculatorResults['fd']['maturity_amount']; ?></span>
                        </div>
                    </div>
                    
                    <div class="result-chart-container">
                        <canvas id="fdChart"></canvas>
                    </div>
                    
                    <div class="calculator-tips">
                        <h4 class="tips-title">
                            <i class="fas fa-lightbulb"></i> Tips for Fixed Deposits
                        </h4>
                        <ul class="tips-list">
                            <li>Compare interest rates from different banks to get the best returns.</li>
                            <li>Consider laddering your fixed deposits with different maturity dates to maintain liquidity.</li>
                            <li>Be aware of penalties for early withdrawal from fixed deposits.</li>
                            <li>Senior citizens often get higher interest rates (0.25% to 0.5% extra) on fixed deposits.</li>
                        </ul>
                    </div>
                    
                    <button class="print-results" onclick="printResults('fixed-deposit')">
                        <i class="fas fa-print"></i> Print Results
                    </button>
                <?php endif; ?>
            </div>
            
            <!-- Monthly Budget Calculator -->
            <div class="calculator-section" id="monthly-budget-calculator">
                <div class="section-title">
                    <i class="fas fa-calculator"></i>
                    <h2>Monthly Budget Calculator</h2>
                </div>
                
                <p class="calculator-description">
                    Plan your monthly budget by entering your income and expenses. See how much you can save or where you might be overspending.
                </p>
                
                <?php if (isset($calculatorErrors['budget'])): ?>
                    <div class="error-message">
                        <i class="fas fa-exclamation-circle"></i> <?php echo $calculatorErrors['budget']; ?>
                    </div>
                <?php endif; ?>
                
                <form method="POST" action="">
                    <div class="calculator-form">
                        <div class="form-group">
                            <label for="monthly_income">Monthly Income (৳)</label>
                            <div class="has-icon">
                                <i class="input-icon taka-symbol">৳</i>
                                <input type="number" id="monthly_income" name="monthly_income" class="form-control" placeholder="Enter your monthly income" min="0" required>
                            </div>
                        </div>
                        
                        <div class="form-group">
                            <label for="housing_expense">Housing Expense (৳)</label>
                            <div class="has-icon">
                                <i class="input-icon taka-symbol">৳</i>
                                <input type="number" id="housing_expense" name="housing_expense" class="form-control" placeholder="Enter your housing expense" min="0" required>
                            </div>
                        </div>
                        
                        <div class="form-group">
                            <label for="utilities_expense">Utilities Expense (৳)</label>
                            <div class="has-icon">
                                <i class="input-icon taka-symbol">৳</i>
                                <input type="number" id="utilities_expense" name="utilities_expense" class="form-control" placeholder="Enter your utilities expense" min="0" required>
                            </div>
                        </div>
                        
                        <div class="form-group">
                            <label for="food_expense">Food Expense (৳)</label>
                            <div class="has-icon">
                                <i class="input-icon taka-symbol">৳</i>
                                <input type="number" id="food_expense" name="food_expense" class="form-control" placeholder="Enter your food expense" min="0" required>
                            </div>
                        </div>
                        
                        <div class="form-group">
                            <label for="healthcare_expense">Healthcare Expense (৳)</label>
                            <div class="has-icon">
                                <i class="input-icon taka-symbol">৳</i>
                                <input type="number" id="healthcare_expense" name="healthcare_expense" class="form-control" placeholder="Enter your healthcare expense" min="0" required>
                            </div>
                        </div>
                        
                        <div class="form-group">
                            <label for="transport_expense">Transport Expense (৳)</label>
                            <div class="has-icon">
                                <i class="input-icon taka-symbol">৳</i>
                                <input type="number" id="transport_expense" name="transport_expense" class="form-control" placeholder="Enter your transport expense" min="0" required>
                            </div>
                        </div>
                        
                        <div class="form-group">
                            <label for="other_expense">Other Expenses (৳)</label>
                            <div class="has-icon">
                                <i class="input-icon taka-symbol">৳</i>
                                <input type="number" id="other_expense" name="other_expense" class="form-control" placeholder="Enter any other expenses" min="0" required>
                            </div>
                        </div>
                    </div>
                    
                    <button type="submit" name="calculate_budget" class="calculate-btn">
                        <i class="fas fa-calculator"></i> Calculate Budget
                    </button>
                </form>
                
                <?php if (isset($calculatorResults['budget'])): ?>
                    <div class="result-card">
                        <h3 class="result-title">Monthly Budget Results</h3>
                        
                        <div class="result-row">
                            <span class="result-label">Total Income:</span>
                            <span class="result-value">৳ <?php echo $calculatorResults['budget']['income']; ?></span>
                        </div>
                        
                        <div class="result-row">
                            <span class="result-label">Total Expenses:</span>
                            <span class="result-value">৳ <?php echo $calculatorResults['budget']['total_expenses']; ?></span>
                        </div>
                        
                        <div class="result-row">
                            <span class="result-label">Remaining Budget:</span>
                            <span class="result-value result-highlight">৳ <?php echo $calculatorResults['budget']['remaining_budget']; ?></span>
                        </div>
                    </div>
                    
                    <div class="result-chart-container">
                        <canvas id="budgetChart"></canvas>
                    </div>
                    
                    <div class="calculator-tips">
                        <h4 class="tips-title">
                            <i class="fas fa-lightbulb"></i> Tips for Budgeting
                        </h4>
                        <ul class="tips-list">
                            <li>Track your expenses for a month to understand your spending habits.</li>
                            <li>Use the 50/30/20 rule: 50% needs, 30% wants, 20% savings.</li>
                            <li>Review and adjust your budget monthly to stay on track.</li>
                            <li>Consider using budgeting apps for easier tracking and management.</li>
                        </ul>
                    </div>
                    
                    <button class="print-results" onclick="printResults('monthly-budget')">
                        <i class="fas fa-print"></i> Print Results
                    </button>
                <?php endif; ?>
            </div>
            
            <!-- Retirement Income Calculator -->
            <div class="calculator-section" id="retirement-income-calculator">
                <div class="section-title">
                    <i class="fas fa-umbrella-beach"></i>
                    <h2>Retirement Income Calculator</h2>
                </div>
                
                <p class="calculator-description">
                    Estimate your retirement income based on your current savings, monthly contributions, and expected return on investment. Plan for a financially secure retirement.
                </p>
                
                <?php if (isset($calculatorErrors['retirement'])): ?>
                    <div class="error-message">
                        <i class="fas fa-exclamation-circle"></i> <?php echo $calculatorErrors['retirement']; ?>
                    </div>
                <?php endif; ?>
                
                <form method="POST" action="">
                    <div class="calculator-form">
                        <div class="form-group">
                            <label for="current_age">Current Age</label>
                            <input type="number" id="current_age" name="current_age" class="form-control" placeholder="Enter your current age" min="0" max="120" required>
                        </div>
                        
                        <div class="form-group">
                            <label for="retirement_age">Retirement Age</label>
                            <input type="number" id="retirement_age" name="retirement_age" class="form-control" placeholder="Enter your desired retirement age" min="0" max="120" required>
                        </div>
                        
                        <div class="form-group">
                            <label for="current_savings">Current Savings (৳)</label>
            border: 2px solid var(--border-color);
            border-radius: 12px;
            font-weight: 600;
            font-size: 1.1rem;
            cursor: pointer;
            transition: all 0.3s ease;
            display: flex;
            align-items: center;
            gap: 0.5rem;
            color: var(--text-medium);
        }
        
        .calculator-tab:hover {
            border-color: var(--primary-color);
            color: var(--primary-color);
        }
        
        .calculator-tab.active {
            background: var(--primary-color);
            color: white;
            border-color: var(--primary-color);
        }
        
        .calculator-tab i {
            font-size: 1.3rem;
        }
        
        .calculator-section {
            background: white;
            border-radius: 15px;
            padding: 2rem;
            margin-bottom: 2rem;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.05);
            display: none;
        }
        
        .calculator-section.active {
            display: block;
        }
        
        .section-title {
            display: flex;
            align-items: center;
            margin-bottom: 1.5rem;
            padding-bottom: 0.5rem;
            border-bottom: 2px solid var(--primary-color);
        }
        
        .section-title i {
            font-size: 1.8rem;
            color: var(--primary-color);
            margin-right: 1rem;
        }
        
        .section-title h2 {
            font-family: 'Montserrat', sans-serif;
            font-weight: 600;
            font-size: 1.8rem;
            color: var(--text-dark);
            margin: 0;
        }
        
        .calculator-description {
            color: var(--text-medium);
            margin-bottom: 2rem;
            font-size: 1.1rem;
            line-height: 1.6;
        }
        
        .calculator-form {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
            gap: 1.5rem;
            margin-bottom: 2rem;
        }
        
        .form-group {
            margin-bottom: 1.5rem;
        }
        
        .form-group label {
            display: block;
            margin-bottom: 0.5rem;
            font-weight: 600;
            color: var(--text-dark);
            font-size: 1.05rem;
        }
        
        .form-control {
            width: 100%;
            padding: 0.8rem 1rem;
            border: 2px solid var(--border-color);
            border-radius: 8px;
            font-size: 1.1rem;
            transition: all 0.3s ease;
        }
        
        .form-control:focus {
            outline: none;
            border-color: var(--primary-color);
            box-shadow: 0 0 0 3px rgba(0, 150, 173, 0.1);
        }
        
        .form-text {
            display: block;
            margin-top: 0.5rem;
            color: var(--text-light);
            font-size: 0.9rem;
        }
        
        .has-icon {
            position: relative;
        }
        
        .input-icon {
            position: absolute;
            top: 50%;
            left: 1rem;
            transform: translateY(-50%);
            color: var(--text-medium);
            font-size: 1.1rem;
        }
        
        .has-icon .form-control {
            padding-left: 2.8rem;
        }
        
        .taka-symbol {
            font-family: Arial, sans-serif;
            font-weight: bold;
        }
        
        .calculate-btn {
            background: var(--primary-color);
            color: white;
            border: none;
            border-radius: 8px;
            padding: 1rem 2rem;
            font-size: 1.2rem;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            margin-top: 1rem;
        }
        
        .calculate-btn:hover {
            background: var(--primary-dark);
            transform: translateY(-2px);
        }
        
        .calculate-btn i {
            font-size: 1.3rem;
        }
        
        /* Result card */
        .result-card {
            background: var(--primary-light);
            border-radius: 12px;
            padding: 1.5rem;
            margin-top: 2rem;
            border-left: 5px solid var(--primary-color);
        }
        
        .result-title {
            font-family: 'Montserrat', sans-serif;
            font-weight: 600;
            font-size: 1.3rem;
            color: var(--primary-color);
            margin-bottom: 1.5rem;
            padding-bottom: 0.5rem;
            border-bottom: 1px solid rgba(0, 150, 173, 0.2);
        }
        
        .result-row {
            display: flex;
            justify-content: space-between;
            margin-bottom: 1rem;
            padding-bottom: 0.5rem;
            border-bottom: 1px dashed rgba(0, 0, 0, 0.1);
        }
        
        .result-row:last-child {
            border-bottom: none;
            margin-bottom: 0;
            padding-bottom: 0;
        }
        
        .result-label {
            font-weight: 600;
            color: var(--text-medium);
        }
        
        .result-value {
            font-weight: 700;
            font-size: 1.1rem;
            color: var(--text-dark);
        }
        
        .result-highlight {
            background: var(--primary-color);
            color: white;
            padding: 0.5rem 1rem;
            border-radius: 6px;
            font-weight: 700;
            font-size: 1.2rem;
        }
        
        .result-chart-container {
            height: 300px;
            margin-top: 2rem;
        }
        
        .calculator-tips {
            background: #fff8e6;
            border-radius: 12px;
            padding: 1.5rem;
            margin-top: 2rem;
            border-left: 5px solid #fbbf24;
        }
        
        .tips-title {
            font-family: 'Montserrat', sans-serif;
            font-weight: 600;
            font-size: 1.2rem;
            color: #92400e;
            margin-bottom: 1rem;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }
        
        .tips-title i {
            font-size: 1.3rem;
        }
        
        .tips-list {
            list-style: none;
            padding: 0;
            margin: 0;
        }
        
        .tips-list li {
            position: relative;
            padding-left: 1.5rem;
            margin-bottom: 0.8rem;
            line-height: 1.5;
            color: #6b7280;
        }
        
        .tips-list li::before {
            content: "•";
            position: absolute;
            left: 0;
            color: #fbbf24;
            font-weight: bold;
            font-size: 1.2rem;
        }
        
        /* Slider inputs */
        .range-container {
            margin-bottom: 1rem;
        }
        
        .range-slider {
            width: 100%;
            height: 8px;
            border-radius: 5px;
            background: #ddd;
            outline: none;
            -webkit-appearance: none;
        }
        
        .range-slider::-webkit-slider-thumb {
            -webkit-appearance: none;
            width: 24px;
            height: 24px;
            border-radius: 50%;
            background: var(--primary-color);
            cursor: pointer;
        }
        
        .range-values {
            display: flex;
            justify-content: space-between;
            margin-top: 0.5rem;
            color: var(--text-light);
            font-size: 0.9rem;
        }
        
        .slider-labels {
            display: flex;
            justify-content: space-between;
            margin-top: 0.3rem;
        }
        
        .slider-min, .slider-max {
            font-size: 0.9rem;
            color: var(--text-light);
        }
        
        .slider-value {
            background: var(--primary-color);
            color: white;
            padding: 0.2rem 0.5rem;
            border-radius: 4px;
            font-weight: 600;
            font-size: 0.9rem;
            position: relative;
            margin-top: 0.5rem;
            display: inline-block;
        }
        
        /* Year breakdown table */
        .year-breakdown {
            width: 100%;
            border-collapse: collapse;
            margin-top: 1.5rem;
        }
        
        .year-breakdown th,
        .year-breakdown td {
            padding: 0.8rem 1rem;
            text-align: left;
            border-bottom: 1px solid var(--border-color);
        }
        
        .year-breakdown th {
            background: var(--bg-light);
            font-weight: 600;
            color: var(--text-dark);
        }
        
        .year-breakdown tr:last-child td {
            border-bottom: none;
        }
        
        .year-breakdown tr:hover {
            background-color: var(--primary-light);
        }
        
        /* Toggle for insurance */
        .toggle-container {
            display: flex;
            align-items: center;
            margin-bottom: 1.5rem;
        }
        
        .toggle-label {
            font-weight: 600;
            color: var(--text-dark);
            margin-right: 1rem;
        }
        
        .toggle-switch {
            position: relative;
            display: inline-block;
            width: 60px;
            height: 34px;
        }
        
        .toggle-switch input {
            opacity: 0;
            width: 0;
            height: 0;
        }
        
        .toggle-slider {
            position: absolute;
            cursor: pointer;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background-color: #ccc;
            transition: .4s;
            border-radius: 34px;
        }
        
        .toggle-slider:before {
            position: absolute;
            content: "";
            height: 26px;
            width: 26px;
            left: 4px;
            bottom: 4px;
            background-color: white;
            transition: .4s;
            border-radius: 50%;
        }
        
        input:checked + .toggle-slider {
            background-color: var(--primary-color);
        }
        
        input:checked + .toggle-slider:before {
            transform: translateX(26px);
        }
        
        .toggle-text {
            margin-left: 0.5rem;
            font-weight: 600;
        }
        
        .toggle-text.on {
            color: var(--primary-color);
        }
        
        .toggle-text.off {
            color: var(--text-light);
        }
        
        /* Responsive table */
        .table-responsive {
            overflow-x: auto;
            margin-top: 1.5rem;
        }
        
        /* Voice Assistant */
        .voice-assistant {
            position: fixed;
            bottom: 30px;
            right: 30px;
            width: 60px;
            height: 60px;
            border-radius: 50%;
            background: var(--primary-color);
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
            background: var(--primary-color);
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
            background: var(--danger-color);
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
        
        /* Print button */
        .print-results {
            background: #fff;
            color: var(--primary-color);
            border: 2px solid var(--primary-color);
            border-radius: 8px;
            padding: 0.8rem 1.5rem;
            font-size: 1.1rem;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            margin-top: 1rem;
        }
        
        .print-results:hover {
            background: var(--primary-light);
        }
        
        /* Save button */
        .save-results {
            background: var(--success-color);
            color: white;
            border: none;
            border-radius: 8px;
            padding: 0.8rem 1.5rem;
            font-size: 1.1rem;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            margin-top: 1rem;
            margin-left: 1rem;
        }
        
        .save-results:hover {
            background: #3da066;
        }
        
        /* Error message */
        .error-message {
            background-color: #fef2f2;
            color: var(--danger-color);
            padding: 1rem;
            border-radius: 8px;
            margin-bottom: 1.5rem;
            border-left: 4px solid var(--danger-color);
        }
        
        /* Calculator wizard */
        .calculator-wizard-steps {
            display: flex;
            overflow: auto;
            margin-bottom: 2rem;
            padding-bottom: 1rem;
        }
        
        .wizard-step {
            min-width: 100px;
            text-align: center;
            padding: 0.8rem 1.5rem;
            background: white;
            border: 1px solid var(--border-color);
            margin-right: 2px;
            position: relative;
            cursor: pointer;
            transition: all 0.3s ease;
        }
        
        .wizard-step:first-child {
            border-top-left-radius: 8px;
            border-bottom-left-radius: 8px;
        }
        
        .wizard-step:last-child {
            border-top-right-radius: 8px;
            border-bottom-right-radius: 8px;
            margin-right: 0;
        }
        
        .wizard-step.active {
            background: var(--primary-color);
            color: white;
            border-color: var(--primary-color);
        }
        
        .wizard-step-number {
            display: inline-block;
            width: 30px;
            height: 30px;
            line-height: 30px;
            border-radius: 50%;
            background: var(--primary-light);
            color: var(--primary-color);
            font-weight: 600;
            margin-bottom: 0.5rem;
        }
        
        .wizard-step.active .wizard-step-number {
            background: white;
        }
        
        .wizard-step-title {
            font-weight: 600;
            white-space: nowrap;
        }
        
        .wizard-content {
            display: none;
        }
        
        .wizard-content.active {
            display: block;
        }
        
        .wizard-buttons {
            display: flex;
            justify-content: space-between;
            margin-top: 2rem;
        }
        
        .wizard-btn {
            padding: 0.8rem 1.5rem;
            border-radius: 8px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
            border: 2px solid var(--primary-color);
            font-size: 1.1rem;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }
        
        .wizard-prev {
            background: white;
            color: var(--primary-color);
        }
        
        .wizard-next {
            background: var(--primary-color);
            color: white;
        }
        
        .wizard-prev:hover {
            background: var(--primary-light);
        }
        
        .wizard-next:hover {
            background: var(--primary-dark);
        }
        
        /* Help tooltips */
        .help-tooltip {
            position: relative;
            display: inline-block;
            margin-left: 0.5rem;
            color: var(--primary-color);
            cursor: pointer;
        }
        
        .tooltip-text {
            visibility: hidden;
            width: 250px;
            background-color: #2d3748;
            color: #fff;
            text-align: left;
            border-radius: 6px;
            padding: 0.8rem;
            position: absolute;
            z-index: 1;
            bottom: 125%;
            left: 50%;
            transform: translateX(-50%);
            opacity: 0;
            transition: opacity 0.3s;
            font-weight: 400;
            font-size: 0.9rem;
            line-height: 1.5;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.2);
        }
        
        .tooltip-text::after {
            content: "";
            position: absolute;
            top: 100%;
            left: 50%;
            margin-left: -5px;
            border-width: 5px;
            border-style: solid;
            border-color: #2d3748 transparent transparent transparent;
        }
        
        .help-tooltip:hover .tooltip-text {
            visibility: visible;
            opacity: 1;
        }
        
        /* Fix for mobile nav */
        .mobile-nav {
            overflow-y: auto;
            max-height: 80vh;
            padding-bottom: 4rem;
        }
        
        .mobile-nav-items {
            padding-bottom: 70px; /* Add padding to ensure items aren't hidden behind buttons */
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
            width: 50px;
            height: 50px;
            margin-bottom: 0.8rem;
            border: none;
            border-radius: 10px;
            background: #f8f9fa;
            cursor: pointer;
            transition: all 0.3s ease;
            font-size: 1.3rem;
            color: #4a5568;
        }
        
        .accessibility-btn:hover {
            background: var(--primary-color);
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
        
        body.high-contrast .calculator-section,
        body.high-contrast .calculator-tab {
            background: #000 !important;
            border-color: #fff !important;
            color: #fff !important;
        }
        
        body.high-contrast h1, 
        body.high-contrast h2, 
        body.high-contrast .result-label,
        body.high-contrast .result-value {
            color: #fff !important;
        }
        
        body.high-contrast .form-control {
            background: #333 !important;
            color: #fff !important;
            border-color: #666 !important;
        }
        
        /* Responsive design */
        @media (max-width: 992px) {
            .calculator-container {
                padding: 1.5rem;
            }
            
            .calculator-form {
                grid-template-columns: 1fr;
            }
            
            .result-row {
                flex-direction: column;
            }
            
            .result-value {
                margin-top: 0.5rem;
            }
        }
        
        @media (max-width: 768px) {
            .calculator-tabs {
                flex-direction: column;
                gap: 0.5rem;
            }
            
            .calculator-container {
                padding: 1rem;
            }
            
            .calculator-section {
                padding: 1.5rem;
            }
            
            .accessibility-panel {
                display: none;
            }
            
            .calculator-tab {
                width: 100%;
                justify-content: center;
            }
            
            .wizard-step-title {
                font-size: 0.9rem;
            }
        }
        
        /* Add Bengali font support */
        @font-face {
            font-family: 'Kalpurush';
            src: url('https://fonts.maateen.me/kalpurush/font.css');
        }
        
        .bn {
            font-family: 'Kalpurush', Arial, sans-serif;
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
        <div class="calculator-container">
            <div class="calculator-header">
                <h1>Financial Calculators</h1>
                <p>Easy-to-use calculators to help you manage your finances, plan for retirement, and make informed financial decisions.</p>
            </div>
            
            <!-- Calculator Tabs -->
            <div class="calculator-tabs">
                <div class="calculator-tab active" onclick="showCalculator('fixed-deposit')">
                    <i class="fas fa-piggy-bank"></i>
                    <span>Fixed Deposit</span>
                </div>
                <div class="calculator-tab" onclick="showCalculator('budget')">
                    <i class="fas fa-calculator"></i>
                    <span>Monthly Budget</span>
                </div>
                <div class="calculator-tab" onclick="showCalculator('retirement')">
                    <i class="fas fa-umbrella-beach"></i>
                    <span>Retirement</span>
                </div>
                <div class="calculator-tab" onclick="showCalculator('medicine')">
                    <i class="fas fa-pills"></i>
               