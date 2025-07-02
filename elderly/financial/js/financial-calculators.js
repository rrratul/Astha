/**
 * Financial Calculator Functions
 * This file contains all the JavaScript implementations for the financial calculators
 */

// Global variables
let fdChart, retirementChart, deductionChart, budgetChart;

document.addEventListener('DOMContentLoaded', function() {
    // Initialize charts
    initCharts();
    
    // Show welcome message
    showToast("Welcome to Financial & Legal Assistance", "info");
});

/**
 * Show toast notification
 */
function showToast(message, type = "info") {
    // Create toast element
    const toast = document.createElement('div');
    toast.className = `toast toast-${type}`;
    toast.innerHTML = `
        <div class="toast-content">
            <i class="fas ${type === 'success' ? 'fa-check-circle' : type === 'error' ? 'fa-exclamation-circle' : 'fa-info-circle'}"></i>
            <span>${message}</span>
        </div>
    `;
    
    // Add toast to container
    const container = document.querySelector('.financial-container') || document.body;
    container.appendChild(toast);
    
    // Show toast
    setTimeout(() => {
        toast.classList.add('show');
    }, 100);
    
    // Hide toast after delay
    setTimeout(() => {
        toast.classList.remove('show');
        setTimeout(() => {
            toast.remove();
        }, 300);
    }, 5000);
}

/**
 * Initialize all charts
 */
function initCharts() {
    // Initialize chart.js context for all calculators
    const fdCtx = document.getElementById('fd-chart')?.getContext('2d');
    const retirementCtx = document.getElementById('retirement-chart')?.getContext('2d');
    const deductionCtx = document.getElementById('deduction-chart')?.getContext('2d');
    const budgetCtx = document.getElementById('budget-chart')?.getContext('2d');
    
    // Fixed Deposit Chart
    if (fdCtx) {
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
    }
    
    // Retirement Chart
    if (retirementCtx) {
        retirementChart = new Chart(retirementCtx, {
            type: 'line',
            data: {
                labels: Array.from({length: 20}, (_, i) => i + 65),
                datasets: [{
                    label: 'Remaining Savings',
                    data: generateRetirementData(20, 1500000, 8, 5),
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
    }
    
    // Medical Deduction Chart
    if (deductionCtx) {
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
    }
    
    // Budget Chart
    if (budgetCtx) {
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
                        position: 'right'
                    },
                    title: {
                        display: true,
                        text: 'Monthly Expense Distribution'
                    }
                }
            }
        });
    }
}

/**
 * Format number as currency (BDT)
 */
function formatCurrency(amount) {
    return '৳' + parseFloat(amount).toLocaleString('en-US', { 
        minimumFractionDigits: 2,
        maximumFractionDigits: 2
    });
}

/**
 * Generate retirement data
 */
function generateRetirementData(years, initialAmount, returnRate, inflationRate) {
    const data = [];
    const realRate = (1 + returnRate/100) / (1 + inflationRate/100) - 1;
    let balance = initialAmount;
    
    for (let i = 0; i < years; i++) {
        data.push(Math.round(balance));
        balance = balance * (1 + realRate);
        // Simulate some withdrawal
        balance -= balance * 0.04;
    }
    
    return data;
}

/**
 * Fixed Deposit Calculator
 */
function calculateFixedDeposit() {
    // Get input values
    const principal = parseFloat(document.getElementById('fd-principal').value);
    const interestRate = parseFloat(document.getElementById('fd-interest-rate').value);
    const years = parseInt(document.getElementById('fd-years').value);
    
    // Validate inputs
    if (isNaN(principal) || isNaN(interestRate) || isNaN(years)) {
        showToast("Please enter valid numbers for all fields", "error");
        return;
    }
    
    // Calculate results
    const rate = interestRate / 100;
    const maturityAmount = principal * Math.pow(1 + rate, years);
    const interestEarned = maturityAmount - principal;
    
    // Update result display
    document.getElementById('fd-result-principal').innerText = formatCurrency(principal);
    document.getElementById('fd-result-rate').innerText = interestRate.toFixed(2) + '%';
    document.getElementById('fd-result-years').innerText = years + ' Years';
    document.getElementById('fd-result-maturity').innerText = formatCurrency(maturityAmount);
    document.getElementById('fd-result-interest').innerText = formatCurrency(interestEarned);
    
    // Update chart
    fdChart.data.datasets[0].data = [principal, interestEarned, maturityAmount];
    fdChart.update();
    
    // Prepare data for saving
    const resultData = JSON.stringify({
        principal: principal,
        interest_rate: interestRate,
        years: years,
        maturity_amount: maturityAmount,
        interest_earned: interestEarned
    });
    
    const inputData = JSON.stringify({
        principal: principal,
        interest_rate: interestRate,
        years: years
    });
    
    // Set hidden form fields for saving
    document.getElementById('fd-result-data').value = resultData;
    document.getElementById('fd-input-data').value = inputData;
    
    // Show results container
    document.getElementById('fd-results').style.display = 'block';
}

/**
 * Reset Fixed Deposit Calculator
 */
function resetFixedDepositForm() {
    document.getElementById('fd-principal').value = '10000';
    document.getElementById('fd-interest-rate').value = '6';
    document.getElementById('fd-years').value = '5';
    document.getElementById('fd-calc-name').value = '';
    document.getElementById('fd-results').style.display = 'none';
}

/**
 * Retirement Calculator
 */
function calculateRetirement() {
    // Get input values
    const currentAge = parseInt(document.getElementById('retirement-current-age').value);
    const retirementAge = parseInt(document.getElementById('retirement-age').value);
    const lifeExpectancy = parseInt(document.getElementById('retirement-life-expectancy').value);
    const monthlySocialSecurity = parseFloat(document.getElementById('retirement-social-security').value);
    const monthlyPension = parseFloat(document.getElementById('retirement-pension').value);
    const monthlyExpenses = parseFloat(document.getElementById('retirement-expenses').value);
    const savings = parseFloat(document.getElementById('retirement-savings').value);
    const annualInflation = parseFloat(document.getElementById('retirement-inflation').value);
    const investmentReturn = parseFloat(document.getElementById('retirement-return').value);
    
    // Validate inputs
    if (isNaN(currentAge) || isNaN(retirementAge) || isNaN(lifeExpectancy) || 
        isNaN(monthlySocialSecurity) || isNaN(monthlyPension) || isNaN(monthlyExpenses) || 
        isNaN(savings) || isNaN(annualInflation) || isNaN(investmentReturn)) {
        showToast("Please enter valid numbers for all fields", "error");
        return;
    }
    
    // Calculate years to retirement and retirement duration
    const yearsToRetirement = retirementAge - currentAge;
    const retirementDuration = lifeExpectancy - retirementAge;
    
    if (yearsToRetirement < 0) {
        showToast("Retirement age must be greater than current age", "error");
        return;
    }
    
    if (retirementDuration <= 0) {
        showToast("Life expectancy must be greater than retirement age", "error");
        return;
    }
    
    // Calculate inflation-adjusted investment return
    const realRate = (1 + investmentReturn / 100) / (1 + annualInflation / 100) - 1;
    const monthlyRealRate = Math.pow(1 + realRate, 1/12) - 1;
    
    // Calculate monthly income and expenses
    const monthlyIncome = monthlySocialSecurity + monthlyPension;
    const monthlyBalance = monthlyIncome - monthlyExpenses;
    const monthlyWithdrawal = monthlyBalance < 0 ? -monthlyBalance : 0;
    
    // Project savings through retirement
    const adjustedSavings = savings * Math.pow(1 + investmentReturn / 100, yearsToRetirement);
    let remainingSavings = adjustedSavings;
    let savingsDepletedMonths = 0;
    let savingsProjection = [];
    
    // Calculate savings projection
    for (let year = 0; year <= retirementDuration; year++) {
        savingsProjection.push(Math.round(remainingSavings));
        
        for (let month = 0; month < 12; month++) {
            if (remainingSavings <= 0) break;
            
            remainingSavings = remainingSavings * (1 + monthlyRealRate) - monthlyWithdrawal;
            savingsDepletedMonths++;
            
            if (remainingSavings <= 0) {
                remainingSavings = 0;
                break;
            }
        }
    }
    
    // Calculate when savings will deplete
    const savingsDepletedYears = Math.floor(savingsDepletedMonths / 12);
    const savingsDepletedAge = retirementAge + savingsDepletedYears;
    const savingsDepletedText = (remainingSavings > 0 || monthlyWithdrawal <= 0) ? 
        "Never (funds remain)" : 
        "Age " + savingsDepletedAge;
    
    // Update result display
    document.getElementById('retirement-result-income').innerText = formatCurrency(monthlyIncome);
    document.getElementById('retirement-result-balance').innerText = formatCurrency(monthlyBalance);
    document.getElementById('retirement-result-years').innerText = retirementDuration;
    document.getElementById('retirement-result-depleted').innerText = savingsDepletedText;
    
    // Update chart
    retirementChart.data.labels = Array.from({length: savingsProjection.length}, (_, i) => i + retirementAge);
    retirementChart.data.datasets[0].data = savingsProjection;
    retirementChart.update();
    
    // Prepare data for saving
    const resultData = JSON.stringify({
        monthly_income: monthlyIncome,
        monthly_balance: monthlyBalance,
        retirement_years: retirementDuration,
        savings_depleted: savingsDepletedText,
        adjusted_savings: adjustedSavings,
        monthly_withdrawal: monthlyWithdrawal
    });
    
    const inputData = JSON.stringify({
        current_age: currentAge,
        retirement_age: retirementAge,
        life_expectancy: lifeExpectancy,
        monthly_social_security: monthlySocialSecurity,
        monthly_pension: monthlyPension,
        monthly_expenses: monthlyExpenses,
        savings: savings,
        annual_inflation: annualInflation,
        investment_return: investmentReturn
    });
    
    // Set hidden form fields for saving
    document.getElementById('retirement-result-data').value = resultData;
    document.getElementById('retirement-input-data').value = inputData;
    
    // Show results container
    document.getElementById('retirement-results').style.display = 'block';
}

/**
 * Reset Retirement Calculator
 */
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
    document.getElementById('retirement-calc-name').value = '';
    document.getElementById('retirement-results').style.display = 'none';
}

/**
 * Medical Expense Deduction Calculator
 */
function calculateMedicalDeduction() {
    // Get input values
    const medicalExpenses = parseFloat(document.getElementById('tax-medical-expenses').value);
    const agi = parseFloat(document.getElementById('tax-agi').value);
    
    // Validate inputs
    if (isNaN(medicalExpenses) || isNaN(agi)) {
        showToast("Please enter valid numbers for all fields", "error");
        return;
    }
    
    // Calculate deduction
    const threshold = agi * 0.075; // 7.5% of AGI
    const potentialDeduction = Math.max(0, medicalExpenses - threshold);
    const taxSavings = potentialDeduction * 0.12; // Assuming 12% tax bracket
    
    // Update result display
    document.getElementById('deduction-result-expenses').innerText = formatCurrency(medicalExpenses);
    document.getElementById('deduction-result-threshold').innerText = formatCurrency(threshold);
    document.getElementById('deduction-result-deduction').innerText = formatCurrency(potentialDeduction);
    document.getElementById('deduction-result-savings').innerText = formatCurrency(taxSavings);
    
    // Update chart
    deductionChart.data.datasets[0].data = [medicalExpenses, threshold, potentialDeduction];
    deductionChart.update();
    
    // Prepare data for saving
    const resultData = JSON.stringify({
        total_medical_expenses: medicalExpenses,
        agi_threshold: threshold,
        potential_deduction: potentialDeduction,
        tax_savings: taxSavings
    });
    
    const inputData = JSON.stringify({
        medical_expenses: medicalExpenses,
        agi: agi
    });
    
    // Set hidden form fields for saving
    document.getElementById('deduction-result-data').value = resultData;
    document.getElementById('deduction-input-data').value = inputData;
    
    // Show results container
    document.getElementById('medical-deduction-results').style.display = 'block';
}

/**
 * Reset Medical Expense Deduction Calculator
 */
function resetMedicalDeductionForm() {
    document.getElementById('tax-medical-expenses').value = '50000';
    document.getElementById('tax-agi').value = '500000';
    document.getElementById('tax-calc-name').value = '';
    document.getElementById('medical-deduction-results').style.display = 'none';
}

/**
 * Budget Calculator
 */
function calculateBudget() {
    // Get income values
    const pensionIncome = parseFloat(document.getElementById('budget-pension').value) || 0;
    const investmentIncome = parseFloat(document.getElementById('budget-investments').value) || 0;
    const otherIncome = parseFloat(document.getElementById('budget-other').value) || 0;
    
    // Get expense values
    const housingExpenses = parseFloat(document.getElementById('budget-housing').value) || 0;
    const foodExpenses = parseFloat(document.getElementById('budget-food').value) || 0;
    const healthcareExpenses = parseFloat(document.getElementById('budget-healthcare').value) || 0;
    const utilitiesExpenses = parseFloat(document.getElementById('budget-utilities').value) || 0;
    const transportExpenses = parseFloat(document.getElementById('budget-transport').value) || 0;
    const leisureExpenses = parseFloat(document.getElementById('budget-leisure').value) || 0;
    const miscExpenses = parseFloat(document.getElementById('budget-misc').value) || 0;
    
    // Calculate totals
    const totalIncome = pensionIncome + investmentIncome + otherIncome;
    const totalExpenses = housingExpenses + foodExpenses + healthcareExpenses + 
                          utilitiesExpenses + transportExpenses + leisureExpenses + miscExpenses;
    
    const monthlyBalance = totalIncome - totalExpenses;
    const annualSavings = monthlyBalance > 0 ? monthlyBalance * 12 : 0;
    
    // Update result display
    document.getElementById('budget-result-income').innerText = formatCurrency(totalIncome);
    document.getElementById('budget-result-expenses').innerText = formatCurrency(totalExpenses);
    document.getElementById('budget-result-balance').innerText = formatCurrency(monthlyBalance);
    document.getElementById('budget-result-savings').innerText = formatCurrency(annualSavings);
    
    // Calculate expense percentages and update chart
    const expenseData = [
        housingExpenses, 
        foodExpenses, 
        healthcareExpenses, 
        utilitiesExpenses, 
        transportExpenses, 
        leisureExpenses, 
        miscExpenses
    ];
    
    budgetChart.data.datasets[0].data = expenseData;
    budgetChart.update();
    
    // Prepare data for saving
    const incomeData = {
        pension: pensionIncome,
        investments: investmentIncome,
        other: otherIncome
    };
    
    const expenseCategories = {
        housing: housingExpenses,
        food: foodExpenses,
        healthcare: healthcareExpenses,
        utilities: utilitiesExpenses,
        transport: transportExpenses,
        leisure: leisureExpenses,
        misc: miscExpenses
    };
    
    const resultData = JSON.stringify({
        total_income: totalIncome,
        total_expenses: totalExpenses,
        monthly_balance: monthlyBalance,
        annual_savings: annualSavings
    });
    
    const inputData = JSON.stringify({
        income: incomeData,
        expenses: expenseCategories
    });
    
    // Set hidden form fields for saving
    document.getElementById('budget-result-data').value = resultData;
    document.getElementById('budget-input-data').value = inputData;
    
    // Show results container
    document.getElementById('budget-results').style.display = 'block';
}

/**
 * Reset Budget Calculator
 */
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
    document.getElementById('budget-calc-name').value = '';
    document.getElementById('budget-results').style.display = 'none';
}

/**
 * Scroll to a calculator section
 */
function scrollToCalculator(calculatorId) {
    const element = document.getElementById(calculatorId);
    if (element) {
        element.scrollIntoView({behavior: 'smooth', block: 'start'});
    }
}

/**
 * View saved calculation
 */
function viewCalculation(id) {
    // Show loading state
    document.getElementById('modal-title').innerText = "Loading...";
    document.getElementById('modal-content').innerHTML = "<p>Loading calculation details...</p>";
    document.getElementById('viewCalculationModal').style.display = 'block';
    
    // Send AJAX request to get calculation details
    fetch(`view_calculation.php?id=${id}`)
        .then(response => {
            if (!response.ok) {
                throw new Error('Network response was not ok');
            }
            return response.json();
        })
        .then(data => {
            // Check for error in response
            if (data.error) {
                throw new Error(data.error);
            }
            
            // Update modal title
            document.getElementById('modal-title').innerText = data.calculation_name || "Saved Calculation";
            
            // Format the content based on calculation type
            let contentHTML = '';
            try {
                const resultData = JSON.parse(data.result_data);
                const inputData = JSON.parse(data.input_data);
                
                // Create HTML based on calculation type
                switch (data.calculation_type) {
                    case 'fixed_deposit':
                        contentHTML = createFixedDepositHTML(resultData, inputData);
                        break;
                    case 'retirement':
                        contentHTML = createRetirementHTML(resultData, inputData);
                        break;
                    case 'medical_deduction':
                        contentHTML = createMedicalDeductionHTML(resultData, inputData);
                        break;
                    case 'monthly_budget':
                        contentHTML = createBudgetHTML(resultData, inputData);
                        break;
                    default:
                        contentHTML = '<p>Details not available for this calculation type.</p>';
                }
            } catch (e) {
                console.error("Error parsing data:", e);
                contentHTML = '<p>Error parsing calculation data. The format may be invalid.</p>';
            }
            
            // Update modal content
            document.getElementById('modal-content').innerHTML = contentHTML;
        })
        .catch(error => {
            console.error('Error fetching calculation:', error);
            document.getElementById('modal-title').innerText = "Error";
            document.getElementById('modal-content').innerHTML = 
                `<p>Failed to load calculation details: ${error.message}</p>`;
        });
}

/**
 * Create HTML for Fixed Deposit calculation
 */
function createFixedDepositHTML(result, input) {
    return `
        <div class="calculation-details">
            <div class="detail-row">
                <span class="detail-label">Principal Amount:</span>
                <span class="detail-value">${result.principal || formatCurrency(input.principal)}</span>
            </div>
            <div class="detail-row">
                <span class="detail-label">Interest Rate:</span>
                <span class="detail-value">${result.interest_rate || input.interest_rate}%</span>
            </div>
            <div class="detail-row">
                <span class="detail-label">Time Period:</span>
                <span class="detail-value">${result.term || input.years} Years</span>
            </div>
            <div class="detail-row highlight">
                <span class="detail-label">Maturity Amount:</span>
                <span class="detail-value">${result.maturity_amount}</span>
            </div>
            <div class="detail-row highlight">
                <span class="detail-label">Interest Earned:</span>
                <span class="detail-value">${result.interest_earned}</span>
            </div>
        </div>
    `;
}

/**
 * Create HTML for other calculation types
 */
function createRetirementHTML(result, input) {
    return `
        <div class="calculation-details">
            <h4>Retirement Plan Summary</h4>
            <div class="detail-row">
                <span class="detail-label">Monthly Income:</span>
                <span class="detail-value">${result.monthly_income}</span>
            </div>
            <div class="detail-row">
                <span class="detail-label">Monthly Balance:</span>
                <span class="detail-value">${result.monthly_balance}</span>
            </div>
            <div class="detail-row">
                <span class="detail-label">Retirement Duration:</span>
                <span class="detail-value">${result.retirement_years} years</span>
            </div>
            <div class="detail-row highlight">
                <span class="detail-label">Savings Depleted At:</span>
                <span class="detail-value">${result.savings_depleted}</span>
            </div>
        </div>
    `;
}

function createMedicalDeductionHTML(result, input) {
    return `
        <div class="calculation-details">
            <div class="detail-row">
                <span class="detail-label">Medical Expenses:</span>
                <span class="detail-value">${result.total_medical_expenses}</span>
            </div>
            <div class="detail-row">
                <span class="detail-label">AGI Threshold (7.5%):</span>
                <span class="detail-value">${result.agi_threshold}</span>
            </div>
            <div class="detail-row highlight">
                <span class="detail-label">Potential Deduction:</span>
                <span class="detail-value">${result.potential_deduction}</span>
            </div>
            <div class="detail-row highlight">
                <span class="detail-label">Tax Savings:</span>
                <span class="detail-value">${result.tax_savings}</span>
            </div>
        </div>
    `;
}

function createBudgetHTML(result, input) {
    return `
        <div class="calculation-details">
            <h4>Budget Summary</h4>
            <div class="detail-row">
                <span class="detail-label">Total Income:</span>
                <span class="detail-value">${result.total_income}</span>
            </div>
            <div class="detail-row">
                <span class="detail-label">Total Expenses:</span>
                <span class="detail-value">${result.total_expenses}</span>
            </div>
            <div class="detail-row highlight">
                <span class="detail-label">Monthly Balance:</span>
                <span class="detail-value">${result.monthly_balance}</span>
            </div>
            <div class="detail-row highlight">
                <span class="detail-label">Annual Savings:</span>
                <span class="detail-value">${result.annual_savings}</span>
            </div>
        </div>
    `;
}

/**
 * Close modal
 */
function closeModal() {
    document.getElementById('viewCalculationModal').style.display = 'none';
}

/**
 * Schedule an advisor
 */
function scheduleAdvisor(advisorId) {
    showToast("Your appointment request has been sent. You will be contacted shortly.", "success");
}