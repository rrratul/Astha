<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Healthcare Provider Dashboard</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        :root {
            --primary-color: #2c3e50;
            --secondary-color: #3498db;
            --accent-color: #e74c3c;
            --success-color: #27ae60;
            --warning-color: #f39c12;
            --light-bg: #ecf0f1;
        }
        
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background-color: var(--light-bg);
        }
        
        .sidebar {
            background: linear-gradient(135deg, var(--primary-color), var(--secondary-color));
            min-height: 100vh;
            color: white;
        }
        
        .sidebar .nav-link {
            color: rgba(255,255,255,0.8);
            padding: 12px 20px;
            margin: 5px 0;
            border-radius: 8px;
            transition: all 0.3s ease;
        }
        
        .sidebar .nav-link:hover,
        .sidebar .nav-link.active {
            background-color: rgba(255,255,255,0.1);
            color: white;
            transform: translateX(5px);
        }
        
        .main-content {
            padding: 20px;
        }
        
        .card {
            border: none;
            border-radius: 15px;
            box-shadow: 0 5px 15px rgba(0,0,0,0.1);
            transition: transform 0.3s ease;
        }
        
        .card:hover {
            transform: translateY(-5px);
        }
        
        .stat-card {
            background: linear-gradient(135deg, var(--secondary-color), #2980b9);
            color: white;
        }
        
        .stat-card.success {
            background: linear-gradient(135deg, var(--success-color), #229954);
        }
        
        .stat-card.warning {
            background: linear-gradient(135deg, var(--warning-color), #e67e22);
        }
        
        .stat-card.danger {
            background: linear-gradient(135deg, var(--accent-color), #c0392b);
        }
        
        .table {
            border-radius: 10px;
            overflow: hidden;
        }
        
        .btn-custom {
            border-radius: 25px;
            padding: 10px 25px;
            font-weight: 500;
        }
        
        .profile-section {
            background: white;
            border-radius: 15px;
            padding: 20px;
            margin-bottom: 20px;
        }
        
        .appointment-card {
            border-left: 4px solid var(--secondary-color);
            margin-bottom: 15px;
        }
        
        .status-badge {
            padding: 5px 12px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: 500;
        }
        
        .status-scheduled { background-color: #e3f2fd; color: #1976d2; }
        .status-completed { background-color: #e8f5e8; color: #388e3c; }
        .status-cancelled { background-color: #ffebee; color: #d32f2f; }
        
        .chart-container {
            background: white;
            border-radius: 15px;
            padding: 20px;
            margin-bottom: 20px;
        }
    </style>
</head>
<body>
    <div class="container-fluid">
        <div class="row">
            <!-- Sidebar -->
            <div class="col-md-3 col-lg-2 sidebar p-0">
                <div class="p-4">
                    <h4 class="mb-4"><i class="fas fa-user-md me-2"></i>Dr. Dashboard</h4>
                    
                    <div class="profile-section mb-4">
                        <div class="text-center">
                            <img src="https://via.placeholder.com/80" class="rounded-circle mb-3" alt="Doctor">
                            <h6>Dr. Sarah Johnson</h6>
                            <small class="text-muted">Cardiologist</small>
                        </div>
                    </div>
                    
                    <nav class="nav flex-column">
                        <a class="nav-link active" href="#dashboard">
                            <i class="fas fa-tachometer-alt me-2"></i>Dashboard
                        </a>
                        <a class="nav-link" href="#patients">
                            <i class="fas fa-users me-2"></i>Patients
                        </a>
                        <a class="nav-link" href="#appointments">
                            <i class="fas fa-calendar-alt me-2"></i>Appointments
                        </a>
                        <a class="nav-link" href="#records">
                            <i class="fas fa-file-medical me-2"></i>Medical Records
                        </a>
                        <a class="nav-link" href="#prescriptions">
                            <i class="fas fa-pills me-2"></i>Prescriptions
                        </a>
                        <a class="nav-link" href="#reports">
                            <i class="fas fa-chart-bar me-2"></i>Reports
                        </a>
                        <a class="nav-link" href="#settings">
                            <i class="fas fa-cog me-2"></i>Settings
                        </a>
                    </nav>
                </div>
            </div>
            
            <!-- Main Content -->
            <div class="col-md-9 col-lg-10 main-content">
                <!-- Header -->
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <div>
                        <h2>Dashboard</h2>
                        <p class="text-muted">Welcome back, Dr. Johnson</p>
                    </div>
                    <div class="d-flex gap-2">
                        <button class="btn btn-outline-primary btn-custom">
                            <i class="fas fa-bell me-2"></i>Notifications
                        </button>
                        <button class="btn btn-primary btn-custom">
                            <i class="fas fa-plus me-2"></i>New Appointment
                        </button>
                    </div>
                </div>
                
                <!-- Statistics Cards -->
                <div class="row mb-4">
                    <div class="col-md-3">
                        <div class="card stat-card">
                            <div class="card-body">
                                <div class="d-flex justify-content-between">
                                    <div>
                                        <h3 class="mb-0">24</h3>
                                        <p class="mb-0">Today's Appointments</p>
                                    </div>
                                    <div class="align-self-center">
                                        <i class="fas fa-calendar-check fa-2x"></i>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="card stat-card success">
                            <div class="card-body">
                                <div class="d-flex justify-content-between">
                                    <div>
                                        <h3 class="mb-0">156</h3>
                                        <p class="mb-0">Total Patients</p>
                                    </div>
                                    <div class="align-self-center">
                                        <i class="fas fa-users fa-2x"></i>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="card stat-card warning">
                            <div class="card-body">
                                <div class="d-flex justify-content-between">
                                    <div>
                                        <h3 class="mb-0">8</h3>
                                        <p class="mb-0">Pending Reports</p>
                                    </div>
                                    <div class="align-self-center">
                                        <i class="fas fa-file-medical fa-2x"></i>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="card stat-card danger">
                            <div class="card-body">
                                <div class="d-flex justify-content-between">
                                    <div>
                                        <h3 class="mb-0">3</h3>
                                        <p class="mb-0">Emergency Cases</p>
                                    </div>
                                    <div class="align-self-center">
                                        <i class="fas fa-exclamation-triangle fa-2x"></i>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Charts and Recent Activity -->
                <div class="row">
                    <div class="col-md-8">
                        <div class="chart-container">
                            <h5 class="mb-3">Weekly Appointments</h5>
                            <canvas id="appointmentChart" width="400" height="200"></canvas>
                        </div>
                        
                        <div class="card">
                            <div class="card-header bg-white">
                                <h5 class="mb-0">Recent Appointments</h5>
                            </div>
                            <div class="card-body">
                                <div class="appointment-card p-3">
                                    <div class="d-flex justify-content-between align-items-center">
                                        <div>
                                            <h6 class="mb-1">John Smith</h6>
                                            <small class="text-muted">Cardiac Consultation</small>
                                        </div>
                                        <div class="text-end">
                                            <div class="status-badge status-scheduled">Scheduled</div>
                                            <small class="text-muted d-block">10:30 AM</small>
                                        </div>
                                    </div>
                                </div>
                                
                                <div class="appointment-card p-3">
                                    <div class="d-flex justify-content-between align-items-center">
                                        <div>
                                            <h6 class="mb-1">Mary Johnson</h6>
                                            <small class="text-muted">Follow-up Visit</small>
                                        </div>
                                        <div class="text-end">
                                            <div class="status-badge status-completed">Completed</div>
                                            <small class="text-muted d-block">9:15 AM</small>
                                        </div>
                                    </div>
                                </div>
                                
                                <div class="appointment-card p-3">
                                    <div class="d-flex justify-content-between align-items-center">
                                        <div>
                                            <h6 class="mb-1">Robert Davis</h6>
                                            <small class="text-muted">ECG Test</small>
                                        </div>
                                        <div class="text-end">
                                            <div class="status-badge status-cancelled">Cancelled</div>
                                            <small class="text-muted d-block">2:00 PM</small>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="col-md-4">
                        <div class="card mb-4">
                            <div class="card-header bg-white">
                                <h5 class="mb-0">Quick Actions</h5>
                            </div>
                            <div class="card-body">
                                <div class="d-grid gap-2">
                                    <button class="btn btn-outline-primary btn-custom">
                                        <i class="fas fa-user-plus me-2"></i>Add New Patient
                                    </button>
                                    <button class="btn btn-outline-success btn-custom">
                                        <i class="fas fa-file-medical me-2"></i>Create Report
                                    </button>
                                    <button class="btn btn-outline-warning btn-custom">
                                        <i class="fas fa-pills me-2"></i>Write Prescription
                                    </button>
                                    <button class="btn btn-outline-info btn-custom">
                                        <i class="fas fa-phone me-2"></i>Emergency Contact
                                    </button>
                                </div>
                            </div>
                        </div>
                        
                        <div class="card">
                            <div class="card-header bg-white">
                                <h5 class="mb-0">Today's Schedule</h5>
                            </div>
                            <div class="card-body">
                                <div class="timeline">
                                    <div class="d-flex mb-3">
                                        <div class="me-3">
                                            <div class="bg-primary rounded-circle d-flex align-items-center justify-content-center" style="width: 30px; height: 30px;">
                                                <small class="text-white">9</small>
                                            </div>
                                        </div>
                                        <div>
                                            <h6 class="mb-1">Morning Rounds</h6>
                                            <small class="text-muted">9:00 AM - 10:00 AM</small>
                                        </div>
                                    </div>
                                    
                                    <div class="d-flex mb-3">
                                        <div class="me-3">
                                            <div class="bg-success rounded-circle d-flex align-items-center justify-content-center" style="width: 30px; height: 30px;">
                                                <small class="text-white">10</small>
                                            </div>
                                        </div>
                                        <div>
                                            <h6 class="mb-1">Patient Consultations</h6>
                                            <small class="text-muted">10:00 AM - 12:00 PM</small>
                                        </div>
                                    </div>
                                    
                                    <div class="d-flex mb-3">
                                        <div class="me-3">
                                            <div class="bg-warning rounded-circle d-flex align-items-center justify-content-center" style="width: 30px; height: 30px;">
                                                <small class="text-white">2</small>
                                            </div>
                                        </div>
                                        <div>
                                            <h6 class="mb-1">Surgery</h6>
                                            <small class="text-muted">2:00 PM - 4:00 PM</small>
                                        </div>
                                    </div>
                                    
                                    <div class="d-flex">
                                        <div class="me-3">
                                            <div class="bg-info rounded-circle d-flex align-items-center justify-content-center" style="width: 30px; height: 30px;">
                                                <small class="text-white">5</small>
                                            </div>
                                        </div>
                                        <div>
                                            <h6 class="mb-1">Team Meeting</h6>
                                            <small class="text-muted">5:00 PM - 6:00 PM</small>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script>
        // Chart initialization
        const ctx = document.getElementById('appointmentChart').getContext('2d');
        const appointmentChart = new Chart(ctx, {
            type: 'line',
            data: {
                labels: ['Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat', 'Sun'],
                datasets: [{
                    label: 'Appointments',
                    data: [12, 19, 15, 25, 22, 18, 24],
                    borderColor: '#3498db',
                    backgroundColor: 'rgba(52, 152, 219, 0.1)',
                    tension: 0.4,
                    fill: true
                }]
            },
            options: {
                responsive: true,
                plugins: {
                    legend: {
                        display: false
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        grid: {
                            color: 'rgba(0,0,0,0.1)'
                        }
                    },
                    x: {
                        grid: {
                            display: false
                        }
                    }
                }
            }
        });

        // Add interactivity
        document.addEventListener('DOMContentLoaded', function() {
            // Navigation active state
            const navLinks = document.querySelectorAll('.nav-link');
            navLinks.forEach(link => {
                link.addEventListener('click', function() {
                    navLinks.forEach(l => l.classList.remove('active'));
                    this.classList.add('active');
                });
            });

            // Card hover effects
            const cards = document.querySelectorAll('.card');
            cards.forEach(card => {
                card.addEventListener('mouseenter', function() {
                    this.style.transform = 'translateY(-5px)';
                });
                card.addEventListener('mouseleave', function() {
                    this.style.transform = 'translateY(0)';
                });
            });
        });
    </script>
</body>
</html>

