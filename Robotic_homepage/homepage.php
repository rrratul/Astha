<?php
// Database connection details
$hostname = "localhost";
$username = "root";
$password = "";
$database = "health_monitor";

// Create connection to the database
$conn = mysqli_connect($hostname, $username, $password, $database);

// Check the connection
if (!$conn) {
    die("Connection failed: " . mysqli_connect_error());
}

// Fetch the latest 10 records for the table and graph
$sql = "SELECT timestamp, bpm, spo2, fall_detected FROM health_data ORDER BY created_at DESC LIMIT 10";
$result = mysqli_query($conn, $sql);

// Initialize arrays for table and graph
$tableData = [];
$timeData = [];
$bpmData = [];

if ($result && mysqli_num_rows($result) > 0) {
    while ($row = mysqli_fetch_assoc($result)) {
        $tableData[] = $row;
        $timeData[] = $row['timestamp'];
        $bpmData[] = $row['bpm'];
    }
} else {
    echo "No data found!";
}

// Close the database connection
mysqli_close($conn);

// Dummy user profile data
$user = [
    'photo' => './images\istockphoto-153011771-612x612.jpg', // Placeholder image for profile
    'name' => 'John Doe',
    'age' => 75,
    'description' => 'A retired teacher who loves gardening and reading.',
    'hobbies' => 'Gardening, Reading, Painting'
];
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>User Health Dashboard</title>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <style>
        body {
            font-family: 'Helvetica Neue', Arial, sans-serif;
            background-color: #e9f5f5;
            margin: 0;
            padding: 0;
        }

        header {
            background-color: #0096ad;
            padding: 20px;
            text-align: center;
            color: white;
        }

        .dashboard-container {
            max-width: 800px;
            margin: 40px auto;
            background: white;
            border-radius: 15px;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.2);
            padding: 40px;
            text-align: center;
        }

        /* Profile Section */
        .profile-section {
            display: flex;
            align-items: center;
            margin-bottom: 30px;
        }

        .profile-photo {
            width: 150px;
            height: 150px;
            border-radius: 50%;
            object-fit: cover;
            margin-right: 20px;
        }

        .profile-info {
            text-align: left;
        }

        .profile-info h2 {
            font-size: 2em;
            color: #0096ad;
            margin: 0;
        }

        .profile-info p {
            font-size: 1.2em;
            margin: 5px 0;
            color: #555;
        }

        .data-table {
            width: 100%;
            margin: 20px 0;
            border-collapse: collapse;
        }

        .data-table th, .data-table td {
            border: 1px solid #ddd;
            padding: 10px;
            text-align: center;
        }

        .data-table th {
            background-color: #0096ad;
            color: white;
        }

        #chart-container {
            margin-top: 30px;
        }

        footer {
            text-align: center;
            padding: 10px 0;
            margin-top: 30px;
            color: #666;
            font-size: 0.9em;
        }
    </style>
</head>
<body>
    <header>
        <h1>User Health Dashboard</h1>
    </header>
    <div class="dashboard-container">
        <!-- Profile Section -->
        <div class="profile-section">
            <img src="<?= $user['photo'] ?>" alt="User Photo" class="profile-photo">
            <div class="profile-info">
                <h2><?= $user['name'] ?></h2>
                <p><strong>Age:</strong> <?= $user['age'] ?></p>
                <p><strong>Description:</strong> <?= $user['description'] ?></p>
                <p><strong>Hobbies:</strong> <?= $user['hobbies'] ?></p>
            </div>
        </div>

        <!-- Data Table -->
        <h2>Recent Health Data</h2>
        <table class="data-table">
            <thead>
                <tr>
                    <th>Timestamp</th>
                    <th>BPM</th>
                    <th>SpO2</th>
                    <th>Fall Detected</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($tableData as $row): ?>
                <tr>
                    <td><?= $row['timestamp'] ?></td>
                    <td><?= $row['bpm'] ?></td>
                    <td><?= $row['spo2'] ?>%</td>
                    <td><?= $row['fall_detected'] ? 'Yes' : 'No' ?></td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>

        <!-- Chart -->
        <h2>Heart Rate Over Time</h2>
        <div id="chart-container">
            <canvas id="bpmChart"></canvas>
        </div>
    </div>
    <footer>
        &copy; <?= date("Y") ?> Health Monitoring System
    </footer>

    <script>
        // Graph data
        const timeLabels = <?= json_encode($timeData) ?>;
        const bpmValues = <?= json_encode($bpmData) ?>;

        // Chart.js configuration
        const ctx = document.getElementById('bpmChart').getContext('2d');
        const bpmChart = new Chart(ctx, {
            type: 'line',
            data: {
                labels: timeLabels,
                datasets: [{
                    label: 'BPM',
                    data: bpmValues,
                    borderColor: '#0096ad',
                    backgroundColor: 'rgba(0, 150, 173, 0.2)',
                    borderWidth: 2,
                    tension: 0.3,
                    pointRadius: 5,
                    pointHoverRadius: 7
                }]
            },
            options: {
                responsive: true,
                plugins: {
                    legend: {
                        display: true,
                        position: 'top'
                    }
                },
                scales: {
                    x: {
                        title: {
                            display: true,
                            text: 'Timestamp'
                        }
                    },
                    y: {
                        title: {
                            display: true,
                            text: 'BPM'
                        },
                        beginAtZero: true
                    }
                }
            }
        });
    </script>
</body>
</html>
