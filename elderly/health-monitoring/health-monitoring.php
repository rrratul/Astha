<?php
session_start();
// Check if user is logged in
if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true) {
    header('Location: ../../login_user/login_user.php');
    exit();
}
// Get user information
$user_id = $_SESSION['user_id'];
$user_name = $_SESSION['user_name'];
$user_role = $_SESSION['user_role'];
?>
<!DOCTYPE html>
<html lang="en">
    <head>
        <link rel="preload" as="script" href="https://cdn.ampproject.org/v0.js">
        <link rel="stylesheet" href="./style.css">
        <script async src="https://cdn.ampproject.org/v0.js"></script>
        <script async custom-element="amp-sidebar" src="https://cdn.ampproject.org/v0/amp-sidebar-0.1.js"></script>
        <script async custom-element="amp-analytics" src="https://cdn.ampproject.org/v0/amp-analytics-0.1.js"></script>
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

        <meta charset="utf-8"/>
        <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
        <title>Shohochor - Health Monitoring</title>
        <meta name="description" content="Monitor your health metrics, track vital signs and manage your wellness with Shohochor's easy-to-use health monitoring system.">
        <link href="./health-monitoring.php" rel="canonical">
        <link rel="apple-touch-icon" sizes="180x180" href="/images/favicon/apple-touch-icon.png">
        <link rel="icon" type="image/png" sizes="32x32" href="/images/favicon/favicon-32x32.png">
        <link rel="icon" type="image/png" sizes="16x16" href="/images/favicon/favicon-16x16.png">
        <link rel="manifest" href="/images/favicon/site.webmanifest.json">
        <link rel="mask-icon" href="/images/favicon/safari-pinned-tab.svg" color="#5bbad5">
        <meta name="msapplication-TileColor" content="#ffffff">
        <meta name="theme-color" content="#ffffff">
        <link rel="preconnect" href="https://fonts.googleapis.com">
        <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
        <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@400;500;600;700;800&family=Open+Sans:wght@400;600&display=swap" rel="stylesheet">
        <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
        <link rel="stylesheet" href="../includes/style.css">
        <!-- Add Chart.js for health metrics visualization -->
        <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
        
        <style>
            .voice-assistant {
                position: fixed;
                bottom: 30px;
                right: 120px;
                width: 60px;
                height: 60px;
                border-radius: 50%;
                background: #0096ad;
                border: none;
                box-shadow: 0 4px 15px rgba(0,150,173,0.4);
                cursor: pointer;
                z-index: 99;
                transition: all 0.3s ease;
                display: flex;
                align-items: center;
                justify-content: center;
            }
            
            .voice-assistant:hover {
                transform: scale(1.1);
                box-shadow: 0 6px 20px rgba(0,150,173,0.5);
            }
            
            .voice-assistant svg {
                width: 30px;
                height: 30px;
            }
            
            .voice-assistant.listening {
                background: #e53e3e;
                animation: pulse 1.5s infinite;
            }
            
            @keyframes pulse {
                0% {
                    box-shadow: 0 0 0 0 rgba(229, 62, 62, 0.7);
                }
                70% {
                    box-shadow: 0 0 0 15px rgba(229, 62, 62, 0);
                }
                100% {
                    box-shadow: 0 0 0 0 rgba(229, 62, 62, 0);
                }
            }
            
            .voice-feedback {
                position: fixed;
                top: 50%;
                left: 50%;
                transform: translate(-50%, -50%);
                background-color: rgba(0, 0, 0, 0.8);
                color: white;
                padding: 30px;
                border-radius: 15px;
                z-index: 9999;
                display: none;
                text-align: center;
                min-width: 300px;
                box-shadow: 0 10px 25px rgba(0,0,0,0.3);
            }
            
            .voice-feedback h3 {
                margin-top: 0;
                font-size: 1.5rem;
                margin-bottom: 15px;
            }
            
            .voice-feedback p {
                margin-bottom: 0;
                font-size: 1.2rem;
            }
            
            .voice-waves {
                display: flex;
                justify-content: center;
                align-items: center;
                height: 60px;
                margin: 15px 0;
            }
            
            .voice-wave {
                width: 6px;
                height: 30px;
                margin: 0 3px;
                border-radius: 3px;
                background: white;
                animation: wave 1s infinite ease-in-out;
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
            
            @keyframes wave {
                0%, 100% {
                    height: 30px;
                }
                50% {
                    height: 60px;
                }
            }
            /* Health Monitoring Specific Styles */
            .health-dashboard {
                margin-bottom: 30px;
            }
            
            .health-metrics-grid {
                display: grid;
                grid-template-columns: repeat(4, 1fr);
                gap: 20px;
                margin-bottom: 30px;
            }
            
            .metric-card {
                background-color: white;
                border-radius: 12px;
                padding: 20px;
                box-shadow: 0 4px 10px rgba(0,0,0,0.08);
                text-align: center;
                transition: transform 0.3s ease, box-shadow 0.3s ease;
                border: 1px solid rgba(0,150,173,0.1);
                position: relative;
                overflow: hidden;
            }
            
            .metric-card:hover {
                transform: translateY(-5px);
                box-shadow: 0 8px 20px rgba(0,0,0,0.12);
            }
            
            .metric-card h3 {
                font-size: 1.1rem;
                color: #4a5568;
                margin-bottom: 5px;
            }
            
            .metric-value {
                font-size: 2rem;
                font-weight: 700;
                color: #2d3748;
                margin: 10px 0;
                line-height: 1.2;
            }
            
            .metric-status {
                font-size: 0.9rem;
                padding: 4px 12px;
                border-radius: 20px;
                display: inline-block;
                font-weight: 600;
            }
            
            .status-normal {
                background-color: #c6f6d5;
                color: #22543d;
            }
            
            .status-warning {
                background-color: #fefcbf;
                color: #744210;
            }
            
            .status-alert {
                background-color: #fed7d7;
                color: #822727;
            }
            
            .health-chart-container {
                background-color: white;
                border-radius: 16px;
                padding: 30px;
                box-shadow: 0 4px 15px rgba(0,0,0,0.08);
                margin-bottom: 30px;
                border: 1px solid rgba(0,150,173,0.1);
            }
            
            .chart-header {
                display: flex;
                justify-content: space-between;
                align-items: center;
                margin-bottom: 20px;
            }
            
            .chart-title {
                font-size: 1.5rem;
                color: #2d3748;
                margin: 0;
            }
            
            .chart-filters {
                display: flex;
                gap: 15px;
            }
            
            .chart-filter-btn {
                background-color: white;
                border: 1px solid #e2e8f0;
                border-radius: 20px;
                padding: 5px 15px;
                font-size: 0.9rem;
                color: #4a5568;
                cursor: pointer;
                transition: all 0.3s ease;
            }
            
            .chart-filter-btn:hover,
            .chart-filter-btn.active {
                background-color: #0096ad;
                color: white;
                border-color: #0096ad;
            }
            
            .chart-wrapper {
                height: 300px;
                position: relative;
            }
            
            .health-section {
                background-color: white;
                border-radius: 16px;
                padding: 30px;
                box-shadow: 0 4px 15px rgba(0,0,0,0.08);
                margin-bottom: 30px;
                border: 1px solid rgba(0,150,173,0.1);
                animation: fadeIn 0.5s ease-out;
            }
            
            .health-section-title {
                font-size: 1.5rem;
                color: #2d3748;
                margin-bottom: 20px;
                padding-bottom: 10px;
                border-bottom: 2px solid #e2e8f0;
                display: flex;
                align-items: center;
                gap: 10px;
            }
            
            .health-section-title i {
                color: #0096ad;
            }
            
            .health-action-btn {
                background-color: #0096ad;
                color: white;
                border: none;
                border-radius: 50px;
                padding: 12px 25px;
                font-size: 1rem;
                font-weight: 600;
                cursor: pointer;
                transition: all 0.3s ease;
                display: flex;
                align-items: center;
                gap: 8px;
                margin-top: 15px;
            }
            
            .health-action-btn:hover {
                background-color: #007489;
                transform: translateY(-2px);
                box-shadow: 0 6px 15px rgba(0,150,173,0.3);
            }
            
            .secondary-action-btn {
                background-color: #e2e8f0;
                color: #4a5568;
            }
            
            .secondary-action-btn:hover {
                background-color: #cbd5e0;
                box-shadow: 0 6px 15px rgba(74,85,104,0.2);
            }
            
            .medication-list {
                list-style: none;
                padding: 0;
                margin: 0;
            }
            
            .medication-item {
                background-color: #f8f9fa;
                border-radius: 12px;
                padding: 15px 20px;
                margin-bottom: 10px;
                border-left: 5px solid #0096ad;
                display: flex;
                justify-content: space-between;
                align-items: center;
                transition: transform 0.3s ease;
            }
            
            .medication-item:hover {
                transform: translateX(5px);
            }
            
            .medication-info {
                flex-grow: 1;
            }
            
            .medication-name {
                font-weight: 600;
                font-size: 1.1rem;
                color: #2d3748;
                margin-bottom: 5px;
            }
            
            .medication-details {
                color: #4a5568;
                font-size: 0.95rem;
                margin-bottom: 3px;
            }
            
            .medication-time {
                color: #718096;
                font-size: 0.85rem;
                display: flex;
                align-items: center;
                gap: 5px;
            }
            
            .medication-status {
                flex-shrink: 0;
                margin-left: 15px;
            }
            
            .status-pill {
                padding: 5px 15px;
                border-radius: 50px;
                font-size: 0.85rem;
                font-weight: 600;
                display: inline-block;
            }
            
            .status-due {
                background-color: #fed7d7;
                color: #822727;
            }
            
            .status-taken {
                background-color: #c6f6d5;
                color: #22543d;
            }
            
            .status-upcoming {
                background-color: #e2e8f0;
                color: #4a5568;
            }
            
            .appointment-card {
                background-color: #f8f9fa;
                border-radius: 12px;
                padding: 20px;
                box-shadow: 0 2px 5px rgba(0,0,0,0.05);
                margin-bottom: 15px;
                border-left: 5px solid #0096ad;
                transition: transform 0.3s ease;
            }
            
            .appointment-card:hover {
                transform: translateY(-5px);
                box-shadow: 0 5px 15px rgba(0,0,0,0.08);
            }
            
            .appointment-date {
                display: flex;
                gap: 15px;
                margin-bottom: 10px;
                align-items: center;
            }
            
            .date-box {
                background-color: #0096ad;
                color: white;
                text-align: center;
                padding: 8px 15px;
                border-radius: 8px;
                min-width: 80px;
            }
            
            .date-day {
                font-size: 1.5rem;
                font-weight: 700;
                line-height: 1;
            }
            
            .date-month {
                font-size: 0.9rem;
                text-transform: uppercase;
                font-weight: 600;
            }
            
            .appointment-doctor {
                font-weight: 600;
                font-size: 1.1rem;
                color: #2d3748;
                margin-bottom: 5px;
            }
            
            .appointment-details {
                color: #4a5568;
                font-size: 0.95rem;
                margin-bottom: 15px;
            }
            
            .appointment-action {
                display: flex;
                gap: 10px;
            }
            
            .appointment-btn {
                padding: 8px 15px;
                border-radius: 5px;
                font-size: 0.9rem;
                font-weight: 600;
                text-align: center;
                cursor: pointer;
                transition: all 0.2s ease;
                text-decoration: none;
                display: inline-flex;
                align-items: center;
                gap: 5px;
            }
            
            .btn-reschedule {
                background-color: #ebf8ff;
                color: #2b6cb0;
                border: 1px solid #bee3f8;
            }
            
            .btn-reschedule:hover {
                background-color: #bee3f8;
            }
            
            .btn-cancel {
                background-color: #fff5f5;
                color: #c53030;
                border: 1px solid #fed7d7;
            }
            
            .btn-cancel:hover {
                background-color: #fed7d7;
            }
            
            .btn-video {
                background-color: #f0fff4;
                color: #2f855a;
                border: 1px solid #c6f6d5;
            }
            
            .btn-video:hover {
                background-color: #c6f6d5;
            }
            
            .health-tip-box {
                background: linear-gradient(135deg, #ebfaf5 0%, #e6f7f9 100%);
                border-radius: 12px;
                padding: 20px;
                border-left: 5px solid #38b2ac;
                margin-bottom: 20px;
                animation: fadeIn 0.6s ease-out;
            }
            
            .tip-header {
                display: flex;
                align-items: center;
                gap: 10px;
                margin-bottom: 10px;
            }
            
            .tip-icon {
                width: 40px;
                height: 40px;
                border-radius: 50%;
                background-color: #38b2ac;
                display: flex;
                align-items: center;
                justify-content: center;
                flex-shrink: 0;
            }
            
            .tip-icon i {
                color: white;
                font-size: 1.2rem;
            }
            
            .tip-title {
                font-weight: 600;
                font-size: 1.2rem;
                color: #2d3748;
                margin: 0;
            }
            
            .tip-content {
                color: #4a5568;
                font-size: 1rem;
                line-height: 1.5;
                margin-bottom: 15px;
            }
            
            .tip-source {
                color: #718096;
                font-size: 0.85rem;
                font-style: italic;
            }
            
            /* UPDATED: Fixed accessibility controls positioning */
            .accessibility-controls {
                position: fixed;
                top: 50%;
                left: 20px;
                transform: translateY(-50%);
                display: flex;
                flex-direction: column;
                gap: 15px;
                z-index: 999;
                background-color: rgba(255, 255, 255, 0.9);
                padding: 15px 10px;
                border-radius: 12px;
                box-shadow: 0 4px 20px rgba(0,0,0,0.15);
                border: 2px solid #0096ad;
            }
            
            .accessibility-controls button {
                width: 50px;
                height: 50px;
                border-radius: 50%;
                background: white;
                border: 2px solid #0096ad;
                box-shadow: 0 4px 8px rgba(0,0,0,0.1);
                cursor: pointer;
                display: flex;
                align-items: center;
                justify-content: center;
                transition: all 0.3s ease;
            }
            
            .accessibility-controls button:hover {
                background: #e6f7f9;
                transform: scale(1.1);
                box-shadow: 0 6px 15px rgba(0,0,0,0.2);
            }
            
            .emergency-button {
                position: fixed;
                bottom: 30px;
                right: 30px;
                width: 70px;
                height: 70px;
                border-radius: 50%;
                background: #e53e3e;
                border: none;
                box-shadow: 0 4px 15px rgba(229,62,62,0.4);
                cursor: pointer;
                z-index: 99;
                transition: all 0.3s ease;
                display: flex;
                align-items: center;
                justify-content: center;
            }
            
            .emergency-button:hover {
                transform: scale(1.1);
                box-shadow: 0 6px 20px rgba(229,62,62,0.5);
            }
            
            .emergency-button svg {
                width: 40px;
                height: 40px;
            }
            
            .voice-assistant {
                position: fixed;
                bottom: 30px;
                right: 120px;
                width: 60px;
                height: 60px;
                border-radius: 50%;
                background: #0096ad;
                border: none;
                box-shadow: 0 4px 15px rgba(0,150,173,0.4);
                cursor: pointer;
                z-index: 99;
                transition: all 0.3s ease;
                display: flex;
                align-items: center;
                justify-content: center;
            }
            
            .voice-assistant:hover {
                transform: scale(1.1);
                box-shadow: 0 6px 20px rgba(0,150,173,0.5);
            }
            
            .voice-assistant svg {
                width: 30px;
                height: 30px;
            }
            
            @media (max-width: 992px) {
                .health-metrics-grid {
                    grid-template-columns: repeat(2, 1fr);
                }
                
                .accessibility-controls {
                    left: 10px;
                    padding: 10px 8px;
                }
                
                .accessibility-controls button {
                    width: 45px;
                    height: 45px;
                }
            }
            
            @media (max-width: 768px) {
                .chart-header {
                    flex-direction: column;
                    align-items: flex-start;
                    gap: 15px;
                }
                
                .chart-filters {
                    flex-wrap: wrap;
                }
                
                .accessibility-controls {
                    top: auto;
                    bottom: 100px;
                    left: 10px;
                    transform: none;
                }
            }
            
            @media (max-width: 576px) {
                .health-metrics-grid {
                    grid-template-columns: 1fr;
                }
                
                .appointment-date {
                    flex-direction: column;
                    align-items: flex-start;
                }
                
                .appointment-action {
                    flex-direction: column;
                    width: 100%;
                }
                
                .appointment-btn {
                    width: 100%;
                    justify-content: center;
                }
                
                .accessibility-controls {
                    bottom: 160px;
                }
                
                .accessibility-controls button {
                    width: 40px;
                    height: 40px;
                }
            }
            /* Enhanced Health Metrics Cards */
            .health-metrics-grid {
                display: grid;
                grid-template-columns: repeat(4, 1fr);
                gap: 20px;
                margin-bottom: 30px;
            }
            
            .metric-card {
                background: linear-gradient(to bottom, #ffffff, #f9fdfe);
                border-radius: 16px;
                padding: 25px 20px;
                box-shadow: 0 8px 20px rgba(0,0,0,0.05);
                text-align: center;
                transition: all 0.4s cubic-bezier(0.165, 0.84, 0.44, 1);
                border: 1px solid rgba(0,150,173,0.1);
                position: relative;
                overflow: hidden;
                z-index: 1;
            }
            
            .metric-card:before {
                content: '';
                position: absolute;
                top: -10px;
                right: -10px;
                width: 80px;
                height: 80px;
                background: rgba(0,150,173,0.03);
                border-radius: 50%;
                z-index: -1;
            }
            
            .metric-card:after {
                content: '';
                position: absolute;
                bottom: -5%;
                left: -5%;
                width: 60px;
                height: 60px;
                background: rgba(0,150,173,0.03);
                border-radius: 50%;
                z-index: -1;
            }
            
            .metric-card:hover {
                transform: translateY(-8px) scale(1.02);
                box-shadow: 0 15px 30px rgba(0,150,173,0.1);
                border-color: rgba(0,150,173,0.2);
            }
            
            .metric-card h3 {
                font-size: 1.1rem;
                color: #4a5568;
                margin-bottom: 10px;
                font-weight: 600;
                position: relative;
                display: inline-block;
                padding-bottom: 5px;
            }
            
            .metric-card h3:after {
                content: '';
                position: absolute;
                bottom: 0;
                left: 50%;
                transform: translateX(-50%);
                width: 50%;
                height: 2px;
                background: linear-gradient(to right, transparent, rgba(0,150,173,0.5), transparent);
            }
            
            .metric-value {
                font-size: 2.2rem;
                font-weight: 700;
                background: linear-gradient(45deg, #2d3748, #0096ad);
                -webkit-background-clip: text;
                -webkit-text-fill-color: transparent;
                margin: 15px 0;
                line-height: 1.2;
                transition: all 0.3s ease;
            }
            
            .metric-card:hover .metric-value {
                transform: scale(1.05);
            }
            
            .metric-status {
                font-size: 0.9rem;
                padding: 6px 15px;
                border-radius: 30px;
                display: inline-block;
                font-weight: 600;
                transform: translateY(0);
                transition: all 0.3s ease;
                box-shadow: 0 2px 10px rgba(0,0,0,0.05);
            }
            
            .metric-card:hover .metric-status {
                transform: translateY(-3px);
                box-shadow: 0 5px 15px rgba(0,0,0,0.08);
            }
            
            .status-normal {
                background: linear-gradient(to right, #c6f6d5, #9ae6b4);
                color: #22543d;
            }
            
            .status-warning {
                background: linear-gradient(to right, #fefcbf, #fbd38d);
                color: #744210;
            }
            
            .status-alert {
                background: linear-gradient(to right, #fed7d7, #feb2b2);
                color: #822727;
            }
            
            /* Enhanced Health Chart Container */
            .health-chart-container {
                background-color: white;
                border-radius: 16px;
                padding: 30px;
                box-shadow: 0 10px 25px rgba(0,0,0,0.06);
                margin-bottom: 30px;
                border: 1px solid rgba(0,150,173,0.1);
                transition: all 0.3s ease;
                position: relative;
                overflow: hidden;
            }
            
            .health-chart-container:before {
                content: '';
                position: absolute;
                top: 0;
                left: 0;
                width: 100%;
                height: 5px;
                background: linear-gradient(to right, #0096ad, #38b2ac);
                transform: scaleX(0);
                transform-origin: left;
                transition: transform 0.5s ease;
            }
            
            .health-chart-container:hover {
                box-shadow: 0 15px 35px rgba(0,0,0,0.08);
                border-color: rgba(0,150,173,0.2);
            }
            
            .health-chart-container:hover:before {
                transform: scaleX(1);
            }
            
            /* Enhanced Medication List */
            .medication-list {
                list-style: none;
                padding: 0;
                margin: 0;
            }
            
            .medication-item {
                background: linear-gradient(to right, #f8f9fa, #ffffff);
                border-radius: 12px;
                padding: 20px;
                margin-bottom: 15px;
                border-left: 5px solid #0096ad;
                display: flex;
                justify-content: space-between;
                align-items: center;
                transition: all 0.4s ease;
                box-shadow: 0 4px 10px rgba(0,0,0,0.03);
                position: relative;
                overflow: hidden;
            }
            
            .medication-item:before {
                content: '';
                position: absolute;
                top: 0;
                left: 0;
                height: 100%;
                width: 0;
                background: linear-gradient(to right, rgba(0,150,173,0.03), transparent);
                transition: all 0.4s ease;
                z-index: 0;
            }
            
            .medication-item:hover {
                transform: translateX(5px);
                box-shadow: 0 8px 20px rgba(0,0,0,0.06);
            }
            
            .medication-item:hover:before {
                width: 100%;
            }
            
            .medication-info {
                flex-grow: 1;
                position: relative;
                z-index: 1;
            }
            
            .medication-name {
                font-weight: 600;
                font-size: 1.15rem;
                color: #2d3748;
                margin-bottom: 8px;
                transition: all 0.3s ease;
            }
            
            .medication-item:hover .medication-name {
                color: #0096ad;
            }
            
            /* Enhanced Appointment Card */
            .appointment-card {
                background: linear-gradient(to bottom right, #f8f9fa, #ffffff);
                border-radius: 16px;
                padding: 25px;
                box-shadow: 0 8px 20px rgba(0,0,0,0.05);
                margin-bottom: 20px;
                border-left: 5px solid #0096ad;
                transition: all 0.4s ease;
                position: relative;
                overflow: hidden;
            }
            
            .appointment-card:before {
                content: '';
                position: absolute;
                top: 0;
                right: 0;
                width: 150px;
                height: 150px;
                background: radial-gradient(circle, rgba(0,150,173,0.03) 0%, transparent 70%);
                border-radius: 50%;
                z-index: 0;
            }
            
            .appointment-card:hover {
                transform: translateY(-8px);
                box-shadow: 0 15px 30px rgba(0,0,0,0.1);
            }
            
            .appointment-date {
                display: flex;
                gap: 20px;
                margin-bottom: 15px;
                align-items: center;
                position: relative;
                z-index: 1;
            }
            
            .date-box {
                background: linear-gradient(to bottom right, #0096ad, #008a9e);
                color: white;
                text-align: center;
                padding: 12px 18px;
                border-radius: 12px;
                min-width: 90px;
                box-shadow: 0 5px 15px rgba(0,150,173,0.2);
                transition: all 0.3s ease;
            }
            
            .appointment-card:hover .date-box {
                transform: scale(1.05);
                box-shadow: 0 8px 20px rgba(0,150,173,0.3);
            }
            
            /* Mobile Responsiveness Improvements */
            @media (max-width: 992px) {
                .health-metrics-grid {
                    grid-template-columns: repeat(2, 1fr);
                    gap: 15px;
                }
                
                .metric-card {
                    padding: 20px 15px;
                }
                
                .metric-value {
                    font-size: 2rem;
                }
            }
            
            @media (max-width: 576px) {
                .health-metrics-grid {
                    grid-template-columns: 1fr;
                }
                
                .metric-card {
                    max-width: 280px;
                    margin-left: auto;
                    margin-right: auto;
                }
                
                .appointment-date {
                    flex-direction: column;
                    align-items: flex-start;
                    gap: 15px;
                }
                
                .date-box {
                    align-self: flex-start;
                }
            }
            
            /* Animation for New Elements */
            @keyframes fadeInUp {
                from {
                    opacity: 0;
                    transform: translateY(20px);
                }
                to {
                    opacity: 1;
                    transform: translateY(0);
                }
            }
            
            .fade-in-element {
                animation: fadeInUp 0.6s ease-out forwards;
            }
            
            /* Add subtle animations */
            @keyframes float {
                0%, 100% {
                    transform: translateY(0);
                }
                50% {
                    transform: translateY(-5px);
                }
            }
            
            .pulse-animation {
                animation: pulse 2s infinite ease-in-out;
            }
            
            @keyframes pulse {
                0% {
                    box-shadow: 0 0 0 0 rgba(0, 150, 173, 0.4);
                }
                70% {
                    box-shadow: 0 0 0 10px rgba(0, 150, 173, 0);
                }
                100% {
                    box-shadow: 0 0 0 0 rgba(0, 150, 173, 0);
                }
            }
        </style>
    </head>
    <body>
        <?php include '../includes/nav.php'; ?>
        <?php include '../includes/mobile_nav.php'; ?>
        
        <!-- UPDATED: Properly positioned accessibility controls -->
        <div class="accessibility-controls">
            <button onclick="increaseFontSize()" title="Increase Text Size">
                <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="currentColor" stroke="currentColor" stroke-width="1">
                    <text x="5" y="19" font-size="18" font-weight="bold">A+</text>
                </svg>
            </button>
            <button onclick="decreaseFontSize()" title="Decrease Text Size">
                <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="currentColor" stroke="currentColor" stroke-width="1">
                    <text x="5" y="19" font-size="16" font-weight="bold">A-</text>
                </svg>
            </button>
            <button onclick="toggleHighContrast()" title="Toggle High Contrast">
                <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <circle cx="12" cy="12" r="10"></circle>
                    <path d="M12 2v20"></path>
                    <path d="M2 12h20"></path>
                </svg>
            </button>
            <button onclick="readPageContent()" title="Read Page Content">
                <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <polygon points="11 5 6 9 2 9 2 15 6 15 11 19 11 5"></polygon>
                    <path d="M15 9.34V5a4 4 0 0 1 0 8v-4.34z"></path>
                    <path d="M19 9.34V5a8 8 0 0 1 0 8v-4.34z"></path>
                </svg>
            </button>
        </div>
        
        <main>
            <div class="alert-banner">
                <div class="alert-content">
                    <svg class="alert-icon" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="white">
                        <path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/>
                        <path d="M12 8v4"/>
                        <path d="M12 16h.01"/>
                    </svg>
                    <span class="alert-message">Health Status: Normal - All vitals within range</span>
                </div>
                <button class="alert-action">View Details</button>
            </div>
            
            <!-- Health Dashboard -->
            <div class="health-dashboard">
                <h1 style="margin-bottom: 20px; color: #2d3748; font-size: 2rem; text-align: center;">Your Health Dashboard</h1>
                
                <!-- Key Health Metrics -->
                <div class="health-metrics-grid">
                    <div class="metric-card">
                        <h3>Blood Pressure</h3>
                        <div class="metric-value">120/80</div>
                        <span class="metric-status status-normal">Normal</span>
                    </div>
                    
                    <div class="metric-card">
                        <h3>Heart Rate</h3>
                        <div class="metric-value">72 BPM</div>
                        <span class="metric-status status-normal">Normal</span>
                    </div>
                    
                    <div class="metric-card">
                        <h3>Blood Sugar</h3>
                        <div class="metric-value">110 mg/dL</div>
                        <span class="metric-status status-warning">Monitor</span>
                    </div>
                    
                    <div class="metric-card">
                        <h3>Oxygen Level</h3>
                        <div class="metric-value">98%</div>
                        <span class="metric-status status-normal">Normal</span>
                    </div>
                </div>
                
                <!-- Health Charts -->
                <div class="health-chart-container">
                    <div class="chart-header">
                        <h2 class="chart-title">Blood Pressure History</h2>
                        <div class="chart-filters">
                            <button class="chart-filter-btn">Day</button>
                            <button class="chart-filter-btn active">Week</button>
                            <button class="chart-filter-btn">Month</button>
                            <button class="chart-filter-btn">3 Months</button>
                        </div>
                    </div>
                    <div class="chart-wrapper">
                        <canvas id="bpChart"></canvas>
                    </div>
                </div>
            </div>
            
            <!-- Today's Medication -->
            <div class="health-section">
                <h2 class="health-section-title">
                    <i class="fas fa-pills"></i>
                    Today's Medication
                </h2>
                
                <ul class="medication-list">
                    <li class="medication-item">
                        <div class="medication-info">
                            <div class="medication-name">Lisinopril 10mg</div>
                            <div class="medication-details">Blood Pressure - 1 tablet</div>
                            <div class="medication-time">
                                <i class="far fa-clock"></i>
                                Morning - 8:00 AM
                            </div>
                        </div>
                        <div class="medication-status">
                            <span class="status-pill status-taken">✓ Taken</span>
                        </div>
                    </li>
                    
                    <li class="medication-item">
                        <div class="medication-info">
                            <div class="medication-name">Metformin 500mg</div>
                            <div class="medication-details">Diabetes - 1 tablet</div>
                            <div class="medication-time">
                                <i class="far fa-clock"></i>
                                Afternoon - 2:00 PM
                            </div>
                        </div>
                        <div class="medication-status">
                            <span class="status-pill status-due">Due Now</span>
                        </div>
                    </li>
                    
                    <li class="medication-item">
                        <div class="medication-info">
                            <div class="medication-name">Atorvastatin 20mg</div>
                            <div class="medication-details">Cholesterol - 1 tablet</div>
                            <div class="medication-time">
                                <i class="far fa-clock"></i>
                                Evening - 8:00 PM
                            </div>
                        </div>
                        <div class="medication-status">
                            <span class="status-pill status-upcoming">Upcoming</span>
                        </div>
                    </li>
                    
                    <li class="medication-item">
                        <div class="medication-info">
                            <div class="medication-name">Vitamin D3 1000 IU</div>
                            <div class="medication-details">Supplement - 1 tablet</div>
                            <div class="medication-time">
                                <i class="far fa-clock"></i>
                                Morning - 8:00 AM
                            </div>
                        </div>
                        <div class="medication-status">
                            <span class="status-pill status-taken">✓ Taken</span>
                        </div>
                    </li>
                </ul>
                
                <button class="health-action-btn">
                    <i class="fas fa-check-circle"></i>
                    Mark Medication as Taken
                </button>
            </div>
            
            <!-- Upcoming Appointments -->
            <div class="health-section">
                <h2 class="health-section-title">
                    <i class="far fa-calendar-check"></i>
                    Upcoming Appointments
                </h2>
                
                <div class="appointment-card">
                    <div class="appointment-date">
                        <div class="date-box">
                            <div class="date-day">15</div>
                            <div class="date-month">Apr</div>
                        </div>
                        <div>
                            <div class="appointment-doctor">Dr. Rahim Ahmed</div>
                            <div class="appointment-details">Cardiology Checkup at 10:30 AM</div>
                        </div>
                    </div>
                    <div class="appointment-action">
                        <a href="#" class="appointment-btn btn-video">
                            <i class="fas fa-video"></i>
                            Join Video Call
                        </a>
                        <a href="#" class="appointment-btn btn-reschedule">
                            <i class="far fa-calendar-alt"></i>
                            Reschedule
                        </a>
                        <a href="#" class="appointment-btn btn-cancel">
                            <i class="fas fa-times"></i>
                            Cancel
                        </a>
                    </div>
                </div>
                
                <div class="appointment-card">
                    <div class="appointment-date">
                        <div class="date-box">
                            <div class="date-day">22</div>
                            <div class="date-month">Apr</div>
                        </div>
                        <div>
                            <div class="appointment-doctor">Dr. Nasreen Khan</div>
                            <div class="appointment-details">Diabetes Follow-up at 2:00 PM</div>
                        </div>
                    </div>
                    <div class="appointment-action">
                        <a href="#" class="appointment-btn btn-reschedule">
                            <i class="far fa-calendar-alt"></i>
                            Reschedule
                        </a>
                        <a href="#" class="appointment-btn btn-cancel">
                            <i class="fas fa-times"></i>
                            Cancel
                        </a>
                    </div>
                </div>
                
                <button class="health-action-btn">
                    <i class="fas fa-plus-circle"></i>
                    Schedule New Appointment
                </button>
            </div>
            
            <!-- Health Tips -->
            <div class="health-section">
                <h2 class="health-section-title">
                    <i class="fas fa-lightbulb"></i>
                    Health Tips for You
                </h2>
                
                <div class="health-tip-box">
                    <div class="tip-header">
                        <div class="tip-icon">
                            <i class="fas fa-heart"></i>
                        </div>
                        <h3 class="tip-title">Heart Health</h3>
                    </div>
                    <p class="tip-content">
                        Walking for just 30 minutes a day can lower your risk of heart disease, stroke, and diabetes. Try breaking it into three 10-minute walks if that's easier on your joints.
                    </p>
                    <p class="tip-source">Source: Heart Health Association</p>
                </div>
                
                <div class="health-tip-box">
                    <div class="tip-header">
                        <div class="tip-icon">
                            <i class="fas fa-brain"></i>
                        </div>
                        <h3 class="tip-title">Brain Health</h3>
                    </div>
                    <p class="tip-content">
                        Challenge your brain with puzzles, reading, or learning something new every day. Mental activity creates new connections between nerve cells and may even help the brain generate new cells.
                    </p>
                    <p class="tip-source">Source: National Institute on Aging</p>
                </div>
                
                <button class="health-action-btn secondary-action-btn">
                    <i class="fas fa-book-open"></i>
                    View All Health Tips
                </button>
            </div>
        </main>
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
        
   <!-- Emergency and Voice Assistant Buttons -->
   <button class="emergency-button" aria-label="Emergency assistance">
            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="white" stroke="white" stroke-width="1">
                <path d="M21.64 3.64A1.36 1.36 0 0 0 20.28 2H3.72a1.36 1.36 0 0 0-1.36 1.64l1.72 9a1.37 1.37 0 0 0 1.36 1.08h14.56c.64 0 1.2-.44 1.36-1.08l1.72-9c.08-.44-.12-.88-.44-1z"></path>
                <path d="M4 14.08c-1.92.96-2.8 2.16-3.36 3.68-.4 1.2.24 2.24 1.44 2.24h19.84c1.2 0 1.84-1.04 1.44-2.24-.56-1.52-1.44-2.72-3.36-3.68"></path>
            </svg>
        </button>
        
        <button class="voice-assistant" id="voiceAssistantBtn" aria-label="Voice assistant">
            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="white" stroke="white" stroke-width="1">
                <path d="M12 1a3 3 0 0 0-3 3v8a3 3 0 0 0 6 0V4a3 3 0 0 0-3-3z"></path>
                <path d="M19 10v2a7 7 0 0 1-14 0v-2"></path>
                <line x1="12" y1="19" x2="12" y2="23"></line>
                <line x1="8" y1="23" x2="16" y2="23"></line>
            </svg>
        </button>
        
        <!-- Custom Footer -->
        <?php include '../includes/footer.php'; ?>
        
        <!-- Analytics -->
        <amp-analytics type="googleanalytics" id="google-analytics">
            <script type="application/json">
                {
                    "requests": {
                        "pageview": "https://www.google-analytics.com/collect?v=1&tid=UA-XXXXX-Y&cid=555&t=pageview&dp=${canonicalUrl}"
                    },
                    "triggers": {
                        "trackPageview": {
                            "on": "visible",
                            "request": "pageview",
                            "visibilitySpec": {
                                "selector": "body",
                                "visiblePercentageMin": 50
                            }
                        }
                    }
                }
            </script>
        </amp-analytics>
  
        <script>

// Existing script content
            
            // Improved Voice Assistant functionality with robust speech recognition
            let recognition;
            let recognitionActive = false;
            const voiceAssistantBtn = document.getElementById('voiceAssistantBtn');
            const voiceFeedback = document.getElementById('voiceFeedback');
            const voiceResult = document.getElementById('voiceResult');
            
            // Initialize speech recognition with better error handling
            function initSpeechRecognition() {
                if ('webkitSpeechRecognition' in window) {
                    recognition = new webkitSpeechRecognition();
                    recognition.continuous = false;
                    recognition.interimResults = false; // Changed to false for more accurate final results
                    recognition.lang = 'en-US';
                    
                    // Recognition started
                    recognition.onstart = function() {
                        recognitionActive = true;
                        voiceAssistantBtn.classList.add('listening');
                        voiceFeedback.style.display = 'block';
                        voiceResult.textContent = 'Listening...';
                    };
                    
                    // Results received with improved handling
                    recognition.onresult = function(event) {
                        let transcript = '';
                        if (event.results && event.results.length > 0) {
                            transcript = event.results[0][0].transcript;
                            voiceResult.textContent = transcript;
                            
                            // Process command immediately
                            processVoiceCommand(transcript.toLowerCase().trim());
                        } else {
                            voiceResult.textContent = "I didn't catch that";
                            speakResponse("I didn't catch that. Please try again.");
                        }
                    };
                    
                    // Recognition ended
                    recognition.onend = function() {
                        recognitionActive = false;
                        voiceAssistantBtn.classList.remove('listening');
                        setTimeout(function() {
                            voiceFeedback.style.display = 'none';
                        }, 1000);
                    };
                    
                    // Better error handling
                    recognition.onerror = function(event) {
                        recognitionActive = false;
                        voiceAssistantBtn.classList.remove('listening');
                        console.error("Speech recognition error:", event.error);
                        
                        let errorMessage = "There was a problem with voice recognition";
                        switch(event.error) {
                            case 'network':
                                errorMessage = "Network error occurred. Check your internet connection.";
                                break;
                            case 'not-allowed':
                            case 'service-not-allowed':
                                errorMessage = "Microphone access denied. Please allow microphone access.";
                                break;
                            case 'aborted':
                                errorMessage = "Voice recognition was aborted";
                                break;
                            case 'audio-capture':
                                errorMessage = "Could not detect any sound. Check your microphone.";
                                break;
                            case 'no-speech':
                                errorMessage = "No speech was detected. Please try again.";
                                break;
                        }
                        
                        voiceResult.textContent = 'Error: ' + errorMessage;
                        speakResponse(errorMessage);
                        
                        setTimeout(function() {
                            voiceFeedback.style.display = 'none';
                        }, 3000);
                    };
                    
                    return true;
                } else {
                    alert("Voice recognition is not supported in your browser. Try using Chrome for best experience.");
                    return false;
                }
            }
            
            // Process voice commands with improved pattern matching
            function processVoiceCommand(command) {
                console.log("Processing command:", command);
                
                // Appointment related commands - improved pattern matching
                if (command.includes('appointment') || command.includes('doctor') || 
                   (command.includes('when') && (command.includes('next') || command.includes('my')))) {
                    voiceResult.textContent = 'Showing your upcoming appointments';
                    speakResponse("Here are your upcoming appointments");
                    
                    // Wait for speech to complete before scrolling
                    setTimeout(() => {
                        const appointmentSection = document.querySelector('.health-section:nth-of-type(2)');
                        if (appointmentSection) {
                            safeScrollTo(appointmentSection);
                            highlightElement(appointmentSection);
                        }
                    }, 500);
                    return;
                }
                
                // Blood pressure related commands
                if (command.includes('blood pressure') || command.includes('bp')) {
                    voiceResult.textContent = 'Showing blood pressure readings';
                    speakResponse("Your blood pressure is 120 over 80, which is normal");
                    
                    setTimeout(() => {
                        const bpCard = document.querySelector('.metric-card:nth-of-type(1)');
                        if (bpCard) {
                            safeScrollTo(bpCard);
                            highlightElement(bpCard);
                        }
                    }, 500);
                    return;
                }
                
                // Heart rate commands
                if (command.includes('heart rate') || command.includes('pulse')) {
                    voiceResult.textContent = 'Showing heart rate information';
                    speakResponse("Your heart rate is 72 beats per minute, which is normal");
                    
                    setTimeout(() => {
                        const heartCard = document.querySelector('.metric-card:nth-of-type(2)');
                        if (heartCard) {
                            safeScrollTo(heartCard);
                            highlightElement(heartCard);
                        }
                    }, 500);
                    return;
                }
                
                // Blood sugar commands
                if (command.includes('blood sugar') || command.includes('glucose')) {
                    voiceResult.textContent = 'Showing blood sugar readings';
                    speakResponse("Your blood sugar is 110 milligrams per deciliter. This is slightly elevated and should be monitored.");
                    
                    setTimeout(() => {
                        const sugarCard = document.querySelector('.metric-card:nth-of-type(3)');
                        if (sugarCard) {
                            safeScrollTo(sugarCard);
                            highlightElement(sugarCard);
                        }
                    }, 500);
                    return;
                }
                
                // Oxygen level commands
                if (command.includes('oxygen') || command.includes('o2')) {
                    voiceResult.textContent = 'Showing oxygen levels';
                    speakResponse("Your oxygen level is 98 percent, which is normal");
                    
                    setTimeout(() => {
                        const oxygenCard = document.querySelector('.metric-card:nth-of-type(4)');
                        if (oxygenCard) {
                            safeScrollTo(oxygenCard);
                            highlightElement(oxygenCard);
                        }
                    }, 500);
                    return;
                }
                
                // Medication related commands
                if (command.includes('medicine') || command.includes('medication') || command.includes('pill')) {
                    voiceResult.textContent = 'Showing your medication information';
                    speakResponse("Here is your medication schedule for today");
                    
                    setTimeout(() => {
                        const medSection = document.querySelector('.health-section:nth-of-type(1)');
                        if (medSection) {
                            safeScrollTo(medSection);
                            highlightElement(medSection);
                        }
                    }, 500);
                    return;
                }
                
                // Health tips commands
                if (command.includes('tip') || command.includes('advice')) {
                    voiceResult.textContent = 'Showing health tips for you';
                    speakResponse("Here are some health tips that might be helpful for you");
                    
                    setTimeout(() => {
                        const tipSection = document.querySelector('.health-section:nth-of-type(3)');
                        if (tipSection) {
                            safeScrollTo(tipSection);
                            highlightElement(tipSection);
                        }
                    }, 500);
                    return;
                }
                
                // Emergency commands
                if (command.includes('emergency') || command.includes('help') || command.includes('sos')) {
                    voiceResult.textContent = 'Activating emergency assistance';
                    speakResponse("Initiating emergency protocol. Help is on the way.");
                    
                    setTimeout(() => {
                        document.querySelector('.emergency-button').click();
                    }, 1000);
                    return;
                }
                
                // Read page commands
                if (command.includes('read') || command.includes('speak')) {
                    voiceResult.textContent = 'Reading page content';
                    readPageContent();
                    return;
                }
                
                // Increase text size commands
                if (command.includes('bigger') || command.includes('larger') || (command.includes('increase') && command.includes('size'))) {
                    voiceResult.textContent = 'Making text bigger';
                    speakResponse("Increasing text size for better visibility");
                    increaseFontSize();
                    return;
                }
                
                // Decrease text size commands
                if (command.includes('smaller') || (command.includes('decrease') && command.includes('size'))) {
                    voiceResult.textContent = 'Making text smaller';
                    speakResponse("Decreasing text size");
                    decreaseFontSize();
                    return;
                }
                
                // High contrast commands
                if (command.includes('contrast')) {
                    voiceResult.textContent = 'Toggling high contrast mode';
                    speakResponse("Changing to high contrast mode for better visibility");
                    toggleHighContrast();
                    return;
                }
                
                // Go home command
                if (command.includes('home')) {
                    voiceResult.textContent = 'Going to home page';
                    speakResponse("Taking you to the home page");
                    
                    setTimeout(function() {
                        window.location.href = '../older_people.php';
                    }, 1500);
                    return;
                }
                
                // Show dashboard or overview command
                if (command.includes('dashboard') || command.includes('overview') || command.includes('summary')) {
                    voiceResult.textContent = 'Showing your health dashboard';
                    speakResponse("Here is your health dashboard summary");
                    
                    setTimeout(() => {
                        const dashboard = document.querySelector('.health-dashboard');
                        if (dashboard) {
                            safeScrollTo(dashboard);
                            highlightElement(dashboard);
                        }
                    }, 500);
                    return;
                }
                
                // Unknown command
                voiceResult.textContent = "Sorry, I didn't understand that command.";
                speakResponse("I didn't understand. You can say things like 'check my blood pressure', 'show my medications', or 'when is my next appointment'.");
            }
            
            // Safe scrolling function to prevent page navigation issues
            function safeScrollTo(element) {
                if (!element) return;
                
                try {
                    // Check if element is in DOM
                    if (!document.body.contains(element)) {
                        console.error("Element not in DOM");
                        return;
                    }
                    
                    // First method: Use scrollIntoView with options
                    element.scrollIntoView({
                        behavior: 'smooth',
                        block: 'center',
                        inline: 'nearest'
                    });
                } catch (error) {
                    console.error("Error during scroll:", error);
                    
                    // Fallback method: Use simpler scrollIntoView
                    try {
                        element.scrollIntoView(true);
                    } catch (e) {
                        console.error("Even simple scrolling failed:", e);
                        
                        // Last resort: Scroll to element's position
                        try {
                            const rect = element.getBoundingClientRect();
                            const scrollTop = window.pageYOffset || document.documentElement.scrollTop;
                            const targetY = rect.top + scrollTop - 100; // 100px above element
                            
                            window.scrollTo({
                                top: targetY,
                                behavior: 'smooth'
                            });
                        } catch (finalError) {
                            console.error("All scroll methods failed:", finalError);
                        }
                    }
                }
            }
            
            // Improved highlight element function with better visual feedback
            function highlightElement(element) {
                if (!element) return;
                
                // Create overlay for better visibility
                const overlay = document.createElement('div');
                overlay.style.position = 'absolute';
                overlay.style.top = '0';
                overlay.style.left = '0';
                overlay.style.width = '100%';
                overlay.style.height = '100%';
                overlay.style.backgroundColor = 'rgba(255, 255, 0, 0.2)';
                overlay.style.border = '3px solid #ffcc00';
                overlay.style.borderRadius = '8px';
                overlay.style.pointerEvents = 'none';
                overlay.style.zIndex = '10';
                overlay.style.boxShadow = '0 0 15px #ffcc00';
                overlay.classList.add('highlight-overlay');
                
                // Position the element properly
                element.style.position = element.style.position || 'relative';
                
                // Remove any existing overlays
                const existingOverlays = element.querySelectorAll('.highlight-overlay');
                existingOverlays.forEach(existing => existing.remove());
                
                // Add the new overlay
                element.appendChild(overlay);
                
                // Save original styles
                const originalBackground = element.style.backgroundColor;
                
                // Apply pulse animation
                element.style.animation = 'pulse-highlight 2s ease-in-out';
                
                // Create a CSS rule for the animation if it doesn't exist
                if (!document.getElementById('highlight-animation')) {
                    const style = document.createElement('style');
                    style.id = 'highlight-animation';
                    style.textContent = `
                        @keyframes pulse-highlight {
                            0%, 100% { background-color: ${originalBackground || 'transparent'}; }
                            50% { background-color: rgba(255, 204, 0, 0.2); }
                        }
                    `;
                    document.head.appendChild(style);
                }
                
                // Remove overlay after delay
                setTimeout(function() {
                    if (overlay && overlay.parentElement) {
                        overlay.remove();
                        element.style.animation = '';
                    }
                }, 3000);
            }
            
            // Improved speech response function with better handling
            function speakResponse(text) {
                if ('speechSynthesis' in window) {
                    // Cancel any ongoing speech first
                    window.speechSynthesis.cancel();
                    
                    // Small delay to ensure cancellation completed
                    setTimeout(() => {
                        const utterance = new SpeechSynthesisUtterance(text);
                        utterance.rate = 0.9;  // Slightly slower for elderly users
                        utterance.pitch = 1;
                        utterance.volume = 1;
                        
                        // Get available voices and try to select a good one
                        let voices = window.speechSynthesis.getVoices();
                        if (voices.length > 0) {
                            // Try to find a natural-sounding English voice
                            const preferredVoice = voices.find(voice => 
                                voice.name.includes('Google') && voice.name.includes('English')) ||
                                voices.find(voice => voice.lang.includes('en-') && voice.name.includes('Female')) ||
                                voices[0];
                                
                            if (preferredVoice) {
                                utterance.voice = preferredVoice;
                            }
                        }
                        
                        // Handle speech end event
                        utterance.onend = function(event) {
                            console.log('Speech complete');
                        };
                        
                        // Handle speech error event
                        utterance.onerror = function(event) {
                            console.error('Speech error:', event.error);
                        };
                        
                        // Speak the response
                        window.speechSynthesis.speak(utterance);
                    }, 100);
                }
            }
            
            // Voice Assistant button click handler with better UX
            voiceAssistantBtn.addEventListener('click', function() {
                if (recognitionActive) {
                    // Stop if already listening
                    if (recognition) {
                        recognition.stop();
                        showToast("Voice assistant stopped");
                    }
                    return;
                }
                
                // First time initialization
                if (!recognition && !initSpeechRecognition()) {
                    showToast("Voice recognition not available");
                    return; // Exit if initialization failed
                }
                
                // Show available commands
                speakResponse("Hello, I'm listening. You can ask about your blood pressure, medications, appointments, or say help for assistance.");
                
                // Start listening after a brief delay to allow the speech to be heard
                setTimeout(function() {
                    try {
                        recognition.start();
                    } catch (e) {
                        console.error("Speech recognition error:", e);
                        // If recognition is already started, stop and restart
                        recognition.stop();
                        setTimeout(function() {
                            recognition.start();
                        }, 300);
                    }
                }, 1500);
            });

            // Emergency button functionality
            document.querySelector('.emergency-button').addEventListener('click', function() {
                if (confirm("Do you need emergency medical assistance? Press OK to call for help.")) {
                    alert("Emergency alert sent! Help is on the way. If this is a medical emergency, call 999 immediately.");
                }
            });
            
            // Add page load animation
            document.addEventListener('DOMContentLoaded', function() {
                document.querySelectorAll('.metric-card, .health-section').forEach(function(element, index) {
                    setTimeout(function() {
                        element.style.opacity = '1';
                        element.style.transform = 'translateY(0)';
                    }, 100 * index);
                });
                
                // Make sure voices are loaded
                if ('speechSynthesis' in window) {
                    window.speechSynthesis.getVoices();
                }
                
                // Add event listeners to filter buttons for better interaction
                document.querySelectorAll('.chart-filter-btn').forEach(button => {
                    button.addEventListener('click', function() {
                        // Remove active class from all buttons
                        document.querySelectorAll('.chart-filter-btn').forEach(btn => {
                            btn.classList.remove('active');
                        });
                        // Add active class to clicked button
                        this.classList.add('active');
                        
                        // In a real application, we would update the chart data here
                        let period = this.textContent.toLowerCase();
                        let newData = getHealthData(period);
                        updateChart(bpChart, newData);
                    });
                });
                
                // Make sure voice support is properly initialized
                if ('webkitSpeechRecognition' in window) {
                    console.log("Voice recognition supported");
                } else {
                    console.warn("Voice recognition not supported in this browser");
                }
            });
            
            // Make sure the speech recognition voices are loaded
            if ('speechSynthesis' in window) {
                let voices = window.speechSynthesis.getVoices();
                if (voices.length === 0) {
                    window.speechSynthesis.onvoiceschanged = function() {
                        voices = window.speechSynthesis.getVoices();
                        console.log("Voices loaded:", voices.length);
                    };
                }
            }
            
            // Initialize Blood Pressure Chart
            const bpCtx = document.getElementById('bpChart').getContext('2d');
            const bpChart = new Chart(bpCtx, {
                type: 'line',
                data: {
                    labels: ['Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat', 'Sun'],
                    datasets: [
                        {
                            label: 'Systolic',
                            data: [122, 119, 125, 120, 118, 122, 120],
                            borderColor: '#0096ad',
                            backgroundColor: 'rgba(0, 150, 173, 0.1)',
                            borderWidth: 2,
                            tension: 0.3,
                            fill: true
                        },
                        {
                            label: 'Diastolic',
                            data: [82, 80, 83, 81, 79, 80, 80],
                            borderColor: '#38b2ac',
                            backgroundColor: 'rgba(56, 178, 172, 0.1)',
                            borderWidth: 2,
                            tension: 0.3,
                            fill: true
                        }
                    ]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: {
                            position: 'top',
                        },
                        tooltip: {
                            mode: 'index',
                            intersect: false,
                            backgroundColor: 'rgba(45, 55, 72, 0.9)',
                            titleFont: {
                                size: 16,
                                family: "'Montserrat', sans-serif"
                            },
                            bodyFont: {
                                size: 14,
                                family: "'Open Sans', sans-serif"
                            },
                            padding: 15,
                            cornerRadius: 8
                        }
                    },
                    scales: {
                        y: {
                            beginAtZero: false,
                            min: 50,
                            max: 160,
                            ticks: {
                                stepSize: 20
                            }
                        }
                    },
                    interaction: {
                        intersect: false,
                        mode: 'index',
                    }
                }
            });
            
            // Simulated function to get health data for different periods
            function getHealthData(period) {
                // In a real app, this would fetch data from an API
                if (period === 'day') {
                    return {
                        labels: ['6AM', '9AM', '12PM', '3PM', '6PM', '9PM'],
                        systolic: [118, 120, 122, 119, 121, 119],
                        diastolic: [78, 80, 82, 79, 81, 79]
                    };
                } else if (period === 'week') {
                    return {
                        labels: ['Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat', 'Sun'],
                        systolic: [122, 119, 125, 120, 118, 122, 120],
                        diastolic: [82, 80, 83, 81, 79, 80, 80]
                    };
                } else if (period === 'month') {
                    return {
                        labels: ['Week 1', 'Week 2', 'Week 3', 'Week 4'],
                        systolic: [121, 123, 120, 119],
                        diastolic: [81, 82, 79, 80]
                    };
                } else { // 3 months
                    return {
                        labels: ['Jan', 'Feb', 'Mar'],
                        systolic: [122, 120, 121],
                        diastolic: [81, 80, 80]
                    };
                }
            }
            
            // Function to update chart with new data
            function updateChart(chart, data) {
                chart.data.labels = data.labels;
                chart.data.datasets[0].data = data.systolic;
                chart.data.datasets[1].data = data.diastolic;
                chart.update();
            }
            
            // Enhanced Accessibility Functions
            let currentFontSize = 100; // percentage
            let highContrastMode = false;
            
            function increaseFontSize() {
                if (currentFontSize < 150) {
                    currentFontSize += 10;
                    document.body.style.fontSize = currentFontSize + '%';
                    showToast("Text size increased");
                } else {
                    showToast("Maximum text size reached");
                }
            }
            
            function decreaseFontSize() {
                if (currentFontSize > 90) {
                    currentFontSize -= 10;
                    document.body.style.fontSize = currentFontSize + '%';
                    showToast("Text size decreased");
                } else {
                    showToast("Minimum text size reached");
                }
            }
            
            function toggleHighContrast() {
                highContrastMode = !highContrastMode;
                
                if (highContrastMode) {
                    document.body.classList.add('high-contrast');
                    document.body.style.backgroundColor = "#000";
                    document.body.style.color = "#FFF";
                    
                    document.querySelectorAll('.metric-card, .health-chart-container, .health-section').forEach(function(element) {
                        element.style.backgroundColor = "#000";
                        element.style.color = "#FFF";
                        element.style.border = "1px solid #FFF";
                    });
                    
                    // Keep accessibility buttons visible in high contrast mode
                    document.querySelectorAll('.accessibility-controls button').forEach(function(button) {
                        button.style.backgroundColor = "#333";
                        button.style.borderColor = "#FFF";
                    });
                    
                    showToast("High contrast mode enabled");
                } else {
                    document.body.classList.remove('high-contrast');
                    document.body.style.backgroundColor = "";
                    document.body.style.color = "";
                    
                    document.querySelectorAll('.metric-card, .health-chart-container, .health-section').forEach(function(element) {
                        element.style.backgroundColor = "";
                        element.style.color = "";
                        element.style.border = "";
                    });
                    
                    // Reset accessibility buttons
                    document.querySelectorAll('.accessibility-controls button').forEach(function(button) {
                        button.style.backgroundColor = "";
                        button.style.borderColor = "";
                    });
                    
                    showToast("High contrast mode disabled");
                }
            }
            
            function readPageContent() {
                if ('speechSynthesis' in window) {
                    var mainContent = document.querySelector('main').innerText;
                    var cleanedContent = mainContent.replace(/\s+/g, ' ').trim();
                    
                    // Truncate if too long to avoid speech issues
                    const maxLength = 5000;
                    if (cleanedContent.length > maxLength) {
                        cleanedContent = cleanedContent.substring(0, maxLength) + 
                            "... The content is too long to read completely. Please use more specific voice commands.";
                    }
                    
                    var utterance = new SpeechSynthesisUtterance(cleanedContent);
                    utterance.rate = 0.9; // Slightly slower for elderly users
                    utterance.pitch = 1;
                    
                    window.speechSynthesis.cancel();
                    window.speechSynthesis.speak(utterance);
                    showToast("Reading page content...");
                    
                    createStopReadingButton();
                } else {
                    alert("Text-to-speech is not supported in your browser.");
                }
            }
            
            function createStopReadingButton() {
                var existingButton = document.getElementById('stopReadingButton');
                if (existingButton) {
                    existingButton.remove();
                }
                
                var stopButton = document.createElement('button');
                stopButton.id = 'stopReadingButton';
                stopButton.innerText = 'Stop Reading';
                stopButton.style.position = 'fixed';
                stopButton.style.bottom = '180px';
                stopButton.style.left = '50%';
                stopButton.style.transform = 'translateX(-50%)';
                stopButton.style.zIndex = '1000';
                stopButton.style.padding = '10px 20px';
                stopButton.style.backgroundColor = '#e74c3c';
                stopButton.style.color = 'white';
                stopButton.style.border = 'none';
                stopButton.style.borderRadius = '30px';
                stopButton.style.fontSize = '18px';
                stopButton.style.cursor = 'pointer';
                stopButton.style.boxShadow = '0 4px 10px rgba(0,0,0,0.3)';
                
                stopButton.onclick = function() {
                    window.speechSynthesis.cancel();
                    stopButton.remove();
                    showToast("Reading stopped");
                };
                
                document.body.appendChild(stopButton);
            }
            
            function showToast(message) {
                var existingToast = document.getElementById('accessibilityToast');
                if (existingToast) {
                    existingToast.remove();
                }
                
                var toast = document.createElement('div');
                toast.id = 'accessibilityToast';
                toast.innerText = message;
                toast.style.position = 'fixed';
                toast.style.bottom = '30px';
                toast.style.left = '50%';
                toast.style.transform = 'translateX(-50%)';
                toast.style.backgroundColor = 'rgba(0, 150, 173, 0.9)';
                toast.style.color = 'white';
                toast.style.padding = '15px 25px';
                toast.style.borderRadius = '30px';
                toast.style.fontSize = '18px';
                toast.style.fontWeight = 'bold';
                toast.style.zIndex = '2000';
                toast.style.boxShadow = '0 4px 15px rgba(0,0,0,0.3)';
                
                document.body.appendChild(toast);
                
                setTimeout(function() {
                    if (toast.parentNode) {
                        toast.classList.add('toast-fade-out');
                        setTimeout(function() {
                            if (toast.parentNode) {
                                toast.parentNode.removeChild(toast);
                            }
                        }, 300);
                    }
                }, 3000);
            }
        </script>
    </body>
</html>
