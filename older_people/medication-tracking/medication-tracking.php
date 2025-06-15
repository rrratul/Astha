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
        <title>Shohochor - Medication Tracking</title>
        <meta name="description" content="Track, manage and get reminders for your medications with Shohochor's easy-to-use medication tracking system.">
        <link href="./medication-tracking.php" rel="canonical">
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
        
        <style>
            /* Medication Tracking Specific Styles */
            .medication-dashboard {
                margin-bottom: 30px;
            }
            
            .medication-section {
                background-color: white;
                border-radius: 16px;
                padding: 30px;
                box-shadow: 0 4px 15px rgba(0,0,0,0.08);
                margin-bottom: 30px;
                border: 1px solid rgba(0,150,173,0.1);
                animation: fadeIn 0.5s ease-out;
            }
            
            .section-heading {
                font-size: 1.8rem;
                color: #2d3748;
                margin-bottom: 25px;
                text-align: center;
                position: relative;
            }
            
            .section-heading:after {
                content: '';
                position: absolute;
                width: 80px;
                height: 3px;
                background: #0096ad;
                bottom: -10px;
                left: 50%;
                transform: translateX(-50%);
            }
            
            .medication-list {
                list-style: none;
                padding: 0;
                margin: 0;
            }
            
            .medication-item {
                background-color: #f8f9fa;
                border-radius: 12px;
                padding: 20px;
                margin-bottom: 15px;
                border-left: 5px solid #0096ad;
                display: flex;
                justify-content: space-between;
                align-items: center;
                transition: transform 0.3s ease, box-shadow 0.3s ease;
            }
            
            .medication-item:hover {
                transform: translateX(5px);
                box-shadow: 0 5px 15px rgba(0,0,0,0.08);
            }
            
            .medication-info {
                flex-grow: 1;
            }
            
            .medication-name {
                font-weight: 600;
                font-size: 1.2rem;
                color: #2d3748;
                margin-bottom: 5px;
                display: flex;
                align-items: center;
                gap: 8px;
            }
            
            .medication-name i {
                color: #0096ad;
            }
            
            .medication-details {
                color: #4a5568;
                font-size: 0.95rem;
                margin-bottom: 8px;
            }
            
            .medication-schedule {
                color: #718096;
                font-size: 0.9rem;
                display: flex;
                align-items: center;
                gap: 5px;
            }
            
            .medication-instructions {
                margin-top: 8px;
                color: #4a5568;
                font-size: 0.9rem;
                font-style: italic;
            }
            
            .medication-action {
                flex-shrink: 0;
                margin-left: 15px;
            }
            
            .medication-button {
                background-color: #0096ad;
                color: white;
                border: none;
                border-radius: 50px;
                padding: 10px 20px;
                font-weight: 600;
                font-size: 0.9rem;
                cursor: pointer;
                transition: all 0.3s ease;
                display: flex;
                align-items: center;
                gap: 8px;
            }
            
            .medication-button:hover {
                background-color: #007489;
                transform: translateY(-2px);
                box-shadow: 0 4px 10px rgba(0,150,173,0.3);
            }
            
            .medication-button.taken {
                background-color: #48bb78;
            }
            
            .medication-button.skipped {
                background-color: #ed8936;
            }
            
            .medication-status {
                display: inline-block;
                padding: 5px 15px;
                border-radius: 50px;
                font-size: 0.85rem;
                font-weight: 600;
                margin-left: 15px;
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
            
            .status-missed {
                background-color: #fed7d7;
                color: #822727;
                animation: pulse 2s infinite;
            }
            
            @keyframes pulse {
                0% {
                    box-shadow: 0 0 0 0 rgba(130, 39, 39, 0.4);
                }
                70% {
                    box-shadow: 0 0 0 8px rgba(130, 39, 39, 0);
                }
                100% {
                    box-shadow: 0 0 0 0 rgba(130, 39, 39, 0);
                }
            }
            
            .medication-time-group {
                margin-bottom: 30px;
            }
            
            .time-heading {
                background-color: #0096ad;
                color: white;
                padding: 10px 20px;
                border-radius: 8px 8px 0 0;
                font-weight: 600;
                font-size: 1.1rem;
                display: flex;
                align-items: center;
                gap: 10px;
                margin-bottom: 15px;
            }
            
            .history-grid {
                display: grid;
                grid-template-columns: repeat(7, 1fr);
                gap: 15px;
                margin-bottom: 30px;
            }
            
            .history-day {
                background-color: #f8f9fa;
                border-radius: 10px;
                padding: 15px 10px;
                text-align: center;
                box-shadow: 0 2px 5px rgba(0,0,0,0.05);
            }
            
            .day-name {
                font-weight: 600;
                color: #2d3748;
                margin-bottom: 5px;
                font-size: 0.95rem;
            }
            
            .day-date {
                color: #718096;
                font-size: 0.85rem;
                margin-bottom: 10px;
            }
            
            .day-status {
                display: flex;
                flex-direction: column;
                align-items: center;
                gap: 5px;
            }
            
            .status-dot {
                width: 15px;
                height: 15px;
                border-radius: 50%;
                display: inline-block;
            }
            
            .dot-taken {
                background-color: #48bb78;
            }
            
            .dot-missed {
                background-color: #e53e3e;
            }
            
            .dot-skipped {
                background-color: #ed8936;
            }
            
            .dot-upcoming {
                background-color: #a0aec0;
                border: 1px solid #718096;
            }
            
            .status-label {
                font-size: 0.8rem;
                color: #4a5568;
            }
            
            .medication-actions {
                display: flex;
                justify-content: center;
                gap: 15px;
                margin-top: 20px;
            }
            
            .action-button {
                background-color: #0096ad;
                color: white;
                border: none;
                border-radius: 8px;
                padding: 15px 25px;
                font-weight: 600;
                font-size: 1rem;
                cursor: pointer;
                transition: all 0.3s ease;
                display: flex;
                align-items: center;
                gap: 10px;
                box-shadow: 0 4px 10px rgba(0,150,173,0.2);
            }
            
            .action-button:hover {
                background-color: #007489;
                transform: translateY(-2px);
                box-shadow: 0 6px 15px rgba(0,150,173,0.3);
            }
            
            .action-button.secondary {
                background-color: white;
                color: #0096ad;
                border: 2px solid #0096ad;
                box-shadow: none;
            }
            
            .action-button.secondary:hover {
                background-color: #f0fafc;
                transform: translateY(-2px);
                box-shadow: 0 4px 10px rgba(0,150,173,0.1);
            }
            
            .medication-detail-card {
                background-color: #f8f9fa;
                border-radius: 12px;
                padding: 25px;
                margin-bottom: 20px;
                border-left: 5px solid #0096ad;
                animation: fadeIn 0.5s ease-out;
            }
            
            .medication-row {
                display: flex;
                margin-bottom: 12px;
                align-items: center;
            }
            
            .medication-label {
                width: 180px;
                font-weight: 600;
                color: #4a5568;
                display: flex;
                align-items: center;
                gap: 8px;
            }
            
            .medication-label i {
                color: #0096ad;
            }
            
            .medication-value {
                color: #2d3748;
                flex-grow: 1;
            }
            
            .refill-info {
                margin-top: 15px;
                padding-top: 15px;
                border-top: 1px dashed #cbd5e0;
                display: flex;
                justify-content: space-between;
                align-items: center;
            }
            
            .refill-count {
                padding: 5px 15px;
                background-color: #ebf8ff;
                color: #2b6cb0;
                border-radius: 50px;
                font-weight: 600;
                font-size: 0.9rem;
            }
            
            .refill-alert {
                padding: 5px 15px;
                background-color: #fed7d7;
                color: #822727;
                border-radius: 50px;
                font-weight: 600;
                font-size: 0.9rem;
                display: flex;
                align-items: center;
                gap: 5px;
            }
            
            .refill-normal {
                padding: 5px 15px;
                background-color: #c6f6d5;
                color: #22543d;
                border-radius: 50px;
                font-weight: 600;
                font-size: 0.9rem;
                display: flex;
                align-items: center;
                gap: 5px;
            }
            
            /* Calendar View Styles */
            .calendar {
                border-radius: 12px;
                overflow: hidden;
                margin-bottom: 30px;
                box-shadow: 0 4px 10px rgba(0,0,0,0.08);
                background-color: white;
            }
            
            .calendar-header {
                background-color: #0096ad;
                color: white;
                padding: 15px;
                display: flex;
                justify-content: space-between;
                align-items: center;
            }
            
            .calendar-title {
                font-size: 1.2rem;
                font-weight: 600;
            }
            
            .calendar-nav {
                display: flex;
                gap: 10px;
            }
            
            .calendar-nav-btn {
                background: rgba(255, 255, 255, 0.2);
                border: none;
                border-radius: 50%;
                width: 35px;
                height: 35px;
                display: flex;
                align-items: center;
                justify-content: center;
                cursor: pointer;
                transition: all 0.3s ease;
            }
            
            .calendar-nav-btn:hover {
                background: rgba(255, 255, 255, 0.4);
            }
            
            .calendar-days {
                display: grid;
                grid-template-columns: repeat(7, 1fr);
                padding: 10px;
            }
            
            .weekday {
                text-align: center;
                padding: 10px;
                font-weight: 600;
                color: #718096;
                font-size: 0.9rem;
            }
            
            .calendar-dates {
                display: grid;
                grid-template-columns: repeat(7, 1fr);
                gap: 5px;
                padding: 0 10px 10px;
            }
            
            .date-cell {
                height: 70px;
                border-radius: 8px;
                padding: 5px;
                position: relative;
                cursor: pointer;
                transition: all 0.3s ease;
            }
            
            .date-cell:hover {
                background-color: #f0fafc;
            }
            
            .date-cell.current-month {
                background-color: #f8f9fa;
            }
            
            .date-cell.other-month {
                background-color: #edf2f7;
                opacity: 0.7;
            }
            
            .date-cell.today {
                background-color: #ebf8ff;
                border: 2px solid #0096ad;
            }
            
            .date-number {
                position: absolute;
                top: 5px;
                right: 8px;
                font-size: 0.85rem;
                font-weight: 600;
                color: #4a5568;
            }
            
            .date-events {
                position: absolute;
                bottom: 5px;
                left: 0;
                right: 0;
                display: flex;
                flex-direction: column;
                align-items: center;
                gap: 2px;
            }
            
            .date-event {
                width: 8px;
                height: 8px;
                border-radius: 50%;
                margin: 0 1px;
            }
            
            .event-medication {
                background-color: #0096ad;
            }
            
            .event-refill {
                background-color: #ed8936;
            }
            
            .event-doctor {
                background-color: #9f7aea;
            }
            
            /* Voice feedback dialog */
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
            
            /* Fixed accessibility controls positioning */
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
            
            /* Modal for editing medications */
            .modal {
                display: none;
                position: fixed;
                top: 0;
                left: 0;
                width: 100%;
                height: 100%;
                background-color: rgba(0, 0, 0, 0.5);
                z-index: 1000;
                opacity: 0;
                transition: opacity 0.3s ease;
                justify-content: center;
                align-items: center;
            }
            
            .modal.show {
                display: flex;
                opacity: 1;
            }
            
            .modal-content {
                background-color: #ffffff;
                border-radius: 16px;
                width: 90%;
                max-width: 600px;
                box-shadow: 0 10px 30px rgba(0, 0, 0, 0.2);
                padding: 30px;
                position: relative;
                transform: translateY(-30px);
                transition: transform 0.3s ease;
                max-height: 80vh;
                overflow-y: auto;
            }
            
            .modal.show .modal-content {
                transform: translateY(0);
            }
            
            .modal-close {
                position: absolute;
                top: 20px;
                right: 20px;
                background: none;
                border: none;
                font-size: 1.5rem;
                cursor: pointer;
                color: #718096;
                transition: color 0.3s ease;
            }
            
            .modal-close:hover {
                color: #e53e3e;
            }
            
            .modal-title {
                color: #2d3748;
                margin-top: 0;
                margin-bottom: 20px;
                text-align: center;
            }
            
            .form-group {
                margin-bottom: 20px;
            }
            
            .form-label {
                font-weight: 600;
                color: #4a5568;
                margin-bottom: 8px;
                display: block;
            }
            
            .form-control {
                width: 100%;
                padding: 12px 15px;
                border: 1px solid #cbd5e0;
                border-radius: 8px;
                font-size: 1rem;
                transition: border-color 0.3s ease;
            }
            
            .form-control:focus {
                outline: none;
                border-color: #0096ad;
                box-shadow: 0 0 0 3px rgba(0, 150, 173, 0.2);
            }
            
            .form-row {
                display: flex;
                gap: 15px;
            }
            
            .form-col {
                flex: 1;
            }
            
            .form-actions {
                display: flex;
                justify-content: center;
                gap: 15px;
                margin-top: 30px;
            }
            
            .form-button {
                padding: 12px 25px;
                border-radius: 8px;
                font-weight: 600;
                cursor: pointer;
                transition: all 0.3s ease;
            }
            
            .form-button.primary {
                background-color: #0096ad;
                color: white;
                border: none;
            }
            
            .form-button.primary:hover {
                background-color: #007489;
            }
            
            .form-button.secondary {
                background-color: white;
                color: #4a5568;
                border: 1px solid #cbd5e0;
            }
            
            .form-button.secondary:hover {
                background-color: #f7fafc;
            }
            
            /* Responsive design */
            @media (max-width: 992px) {
                .history-grid {
                    grid-template-columns: repeat(4, 1fr);
                }
                
                .form-row {
                    flex-direction: column;
                    gap: 20px;
                }
            }
            
            @media (max-width: 768px) {
                .section-heading {
                    font-size: 1.5rem;
                }
                
                .medication-item {
                    flex-direction: column;
                    align-items: flex-start;
                }
                
                .medication-action {
                    margin-left: 0;
                    margin-top: 15px;
                    width: 100%;
                }
                
                .medication-button {
                    width: 100%;
                    justify-content: center;
                }
                
                .medication-status {
                    margin-left: 0;
                    margin-top: 10px;
                }
                
                .medication-actions {
                    flex-direction: column;
                    gap: 10px;
                }
                
                .action-button {
                    width: 100%;
                    justify-content: center;
                }
                
                .history-grid {
                    grid-template-columns: repeat(3, 1fr);
                }
                
                .calendar-dates {
                    gap: 2px;
                }
                
                .date-cell {
                    height: 60px;
                }
                
                .accessibility-controls {
                    top: auto;
                    bottom: 100px;
                    left: 10px;
                    transform: none;
                }
            }
            
            @media (max-width: 576px) {
                .history-grid {
                    grid-template-columns: repeat(2, 1fr);
                }
                
                .medication-row {
                    flex-direction: column;
                    align-items: flex-start;
                }
                
                .medication-label {
                    margin-bottom: 5px;
                }
                
                .calendar-days, .calendar-dates {
                    grid-template-columns: repeat(7, 1fr);
                }
                
                .weekday {
                    font-size: 0.7rem;
                    padding: 5px;
                }
                
                .date-cell {
                    height: 50px;
                }
                
                .date-number {
                    font-size: 0.7rem;
                }
                
                .accessibility-controls {
                    bottom: 160px;
                }
                
                .accessibility-controls button {
                    width: 40px;
                    height: 40px;
                }
            }
        </style>
    </head>
    <body>
        <?php include '../includes/nav.php'; ?>
        <?php include '../includes/mobile_nav.php'; ?>
        
        <!-- Fixed accessibility controls -->
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
            <div class="alert-banner">
                <div class="alert-content">
                    <svg class="alert-icon" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="white">
                        <path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/>
                        <path d="M12 8v4"/>
                        <path d="M12 16h.01"/>
                    </svg>
                    <span class="alert-message">Reminder: You have 2 medications due soon</span>
                </div>
                <button class="alert-action">View Details</button>
            </div>
            
            <!-- Medication Dashboard -->
            <div class="medication-dashboard">
                <h1 style="margin-bottom: 20px; color: #2d3748; font-size: 2rem; text-align: center;">Your Medication Dashboard</h1>
                
                <!-- Today's Medications Section -->
                <div class="medication-section">
                    <h2 class="section-heading">Today's Medications</h2>
                    
                    <!-- Morning Medications -->
                    <div class="medication-time-group">
                        <div class="time-heading">
                            <i class="fas fa-sun"></i>
                            Morning (8:00 AM)
                        </div>
                        <ul class="medication-list">
                            <li class="medication-item">
                                <div class="medication-info">
                                    <div class="medication-name">
                                        <i class="fas fa-pills"></i>
                                        Lisinopril 10mg
                                    </div>
                                    <div class="medication-details">Blood Pressure Medication - 1 tablet</div>
                                    <div class="medication-schedule">
                                        <i class="far fa-clock"></i>
                                        Take with food
                                    </div>
                                </div>
                                <div class="medication-action">
                                    <span class="medication-status status-taken">✓ Taken</span>
                                </div>
                            </li>
                            
                            <li class="medication-item">
                                <div class="medication-info">
                                    <div class="medication-name">
                                        <i class="fas fa-pills"></i>
                                        Vitamin D3 1000 IU
                                    </div>
                                    <div class="medication-details">Supplement - 1 tablet</div>
                                    <div class="medication-schedule">
                                        <i class="far fa-clock"></i>
                                        Take with breakfast
                                    </div>
                                </div>
                                <div class="medication-action">
                                    <span class="medication-status status-taken">✓ Taken</span>
                                </div>
                            </li>
                        </ul>
                    </div>
                    
                    <!-- Afternoon Medications -->
                    <div class="medication-time-group">
                        <div class="time-heading">
                            <i class="fas fa-cloud-sun"></i>
                            Afternoon (2:00 PM)
                        </div>
                        <ul class="medication-list">
                            <li class="medication-item">
                                <div class="medication-info">
                                    <div class="medication-name">
                                        <i class="fas fa-pills"></i>
                                        Metformin 500mg
                                    </div>
                                    <div class="medication-details">Diabetes Medication - 1 tablet</div>
                                    <div class="medication-schedule">
                                        <i class="far fa-clock"></i>
                                        Take with lunch
                                    </div>
                                </div>
                                <div class="medication-action">
                                    <button class="medication-button" id="takeMedBtn1">
                                        <i class="fas fa-check"></i>
                                        Mark as Taken
                                    </button>
                                </div>
                            </li>
                        </ul>
                    </div>
                    
                    <!-- Evening Medications -->
                    <div class="medication-time-group">
                        <div class="time-heading">
                            <i class="fas fa-moon"></i>
                            Evening (8:00 PM)
                        </div>
                        <ul class="medication-list">
                            <li class="medication-item">
                                <div class="medication-info">
                                    <div class="medication-name">
                                        <i class="fas fa-pills"></i>
                                        Atorvastatin 20mg
                                    </div>
                                    <div class="medication-details">Cholesterol Medication - 1 tablet</div>
                                    <div class="medication-schedule">
                                        <i class="far fa-clock"></i>
                                        Take after dinner
                                    </div>
                                </div>
                                <div class="medication-action">
                                    <span class="medication-status status-upcoming">Upcoming</span>
                                </div>
                            </li>
                            
                            <li class="medication-item">
                                <div class="medication-info">
                                    <div class="medication-name">
                                        <i class="fas fa-pills"></i>
                                        Omeprazole 20mg
                                    </div>
                                    <div class="medication-details">Acid Reducer - 1 capsule</div>
                                    <div class="medication-schedule">
                                        <i class="far fa-clock"></i>
                                        Take 30 minutes before dinner
                                    </div>
                                </div>
                                <div class="medication-action">
                                    <span class="medication-status status-upcoming">Upcoming</span>
                                </div>
                            </li>
                        </ul>
                    </div>
                    
                    <div class="medication-actions">
                        <button class="action-button" id="addMedicationBtn">
                            <i class="fas fa-plus-circle"></i>
                            Add New Medication
                        </button>
                        <button class="action-button secondary">
                            <i class="fas fa-print"></i>
                            Print Schedule
                        </button>
                    </div>
                </div>
                
                <!-- Medication History Section -->
                <div class="medication-section">
                    <h2 class="section-heading">Weekly Medication History</h2>
                    
                    <div class="history-grid">
                        <?php
                        // Get current date
                        $currentDate = new DateTime();
                        
                        // Create data for the past 7 days
                        for ($i = 6; $i >= 0; $i--) {
                            $date = clone $currentDate;
                            $date->modify("-$i day");
                            
                            $dayName = $date->format('D');
                            $dayNumber = $date->format('j');
                            $month = $date->format('M');
                            
                            // Simulate some random status for demo purpose
                            $randomStatus = $i == 0 ? 'upcoming' : ($i == 1 ? 'partial' : rand(0, 3));
                            $statusClass = '';
                            $statusText = '';
                            
                            if ($randomStatus == 0) {
                                $statusClass = 'dot-taken';
                                $statusText = 'All Taken';
                            } elseif ($randomStatus == 1) {
                                $statusClass = 'dot-missed';
                                $statusText = 'Missed';
                            } elseif ($randomStatus == 2) {
                                $statusClass = 'dot-skipped';
                                $statusText = 'Skipped';
                            } elseif ($randomStatus == 'partial') {
                                $statusClass = 'dot-skipped';
                                $statusText = 'Partial';
                            } else {
                                $statusClass = 'dot-upcoming';
                                $statusText = 'Upcoming';
                            }
                            
                            echo "
                            <div class='history-day" . ($i == 0 ? " today" : "") . "'>
                                <div class='day-name'>$dayName</div>
                                <div class='day-date'>$month $dayNumber</div>
                                <div class='day-status'>
                                    <span class='status-dot $statusClass'></span>
                                    <span class='status-label'>$statusText</span>
                                </div>
                            </div>";
                        }
                        ?>
                    </div>
                    
                    <div class="calendar">
                        <div class="calendar-header">
                            <div class="calendar-title">April 2025</div>
                            <div class="calendar-nav">
                                <button class="calendar-nav-btn">
                                    <i class="fas fa-chevron-left"></i>
                                </button>
                                <button class="calendar-nav-btn">
                                    <i class="fas fa-chevron-right"></i>
                                </button>
                            </div>
                        </div>
                        <div class="calendar-days">
                            <div class="weekday">Sun</div>
                            <div class="weekday">Mon</div>
                            <div class="weekday">Tue</div>
                            <div class="weekday">Wed</div>
                            <div class="weekday">Thu</div>
                            <div class="weekday">Fri</div>
                            <div class="weekday">Sat</div>
                        </div>
                        <div class="calendar-dates">
                            <?php
                            // Generate a calendar for demo purposes
                            $datesInMonth = 30; // Let's say April has 30 days
                            $firstDayOffset = 2; // Let's say April 1st is a Tuesday (0 = Sunday, 1 = Monday, etc.)
                            
                            // Previous month's ending dates
                            for ($i = 0; $i < $firstDayOffset; $i++) {
                                echo "<div class='date-cell other-month'>
                                    <div class='date-number'>" . (31 - $firstDayOffset + $i + 1) . "</div>
                                </div>";
                            }
                            
                            // Current month's dates
                            for ($i = 1; $i <= $datesInMonth; $i++) {
                                $isToday = $i == 4; // Let's say today is April 4th
                                $hasEvents = rand(0, 1); // Randomly decide if the day has medication events
                                
                                echo "<div class='date-cell current-month" . ($isToday ? " today" : "") . "'>
                                    <div class='date-number'>$i</div>";
                                
                                if ($hasEvents) {
                                    echo "<div class='date-events'>";
                                    
                                    $eventTypes = ['medication', 'refill', 'doctor'];
                                    $numEvents = rand(1, count($eventTypes));
                                    $selectedEvents = array_rand(array_flip($eventTypes), $numEvents);
                                    
                                    if (!is_array($selectedEvents)) {
                                        $selectedEvents = [$selectedEvents];
                                    }
                                    
                                    foreach ($selectedEvents as $eventType) {
                                        echo "<div class='date-event event-$eventType'></div>";
                                    }
                                    
                                    echo "</div>";
                                }
                                
                                echo "</div>";
                            }
                            
                            // Next month's beginning dates
                            $remainingCells = 7 - (($firstDayOffset + $datesInMonth) % 7);
                            if ($remainingCells < 7) {
                                for ($i = 1; $i <= $remainingCells; $i++) {
                                    echo "<div class='date-cell other-month'>
                                        <div class='date-number'>$i</div>
                                    </div>";
                                }
                            }
                            ?>
                        </div>
                    </div>
                </div>
                
                <!-- Medication Details Section -->
                <div class="medication-section">
                    <h2 class="section-heading">Medication Details</h2>
                    
                    <div class="medication-detail-card">
                        <div class="medication-row">
                            <div class="medication-label">
                                <i class="fas fa-prescription-bottle-alt"></i>
                                Medication
                            </div>
                            <div class="medication-value">Lisinopril 10mg</div>
                        </div>
                        <div class="medication-row">
                            <div class="medication-label">
                                <i class="fas fa-pills"></i>
                                Category
                            </div>
                            <div class="medication-value">ACE Inhibitor (Blood Pressure)</div>
                        </div>
                        <div class="medication-row">
                            <div class="medication-label">
                                <i class="fas fa-clock"></i>
                                Schedule
                            </div>
                            <div class="medication-value">Once daily in the morning</div>
                        </div>
                        <div class="medication-row">
                            <div class="medication-label">
                                <i class="fas fa-utensils"></i>
                                Instructions
                            </div>
                            <div class="medication-value">Take with food to reduce stomach upset</div>
                        </div>
                        <div class="medication-row">
                            <div class="medication-label">
                                <i class="fas fa-exclamation-triangle"></i>
                                Side Effects
                            </div>
                            <div class="medication-value">May cause dizziness, dry cough, or headache</div>
                        </div>
                        <div class="medication-row">
                            <div class="medication-label">
                                <i class="fas fa-user-md"></i>
                                Prescribed By
                            </div>
                            <div class="medication-value">Dr. Rahima Khan</div>
                        </div>
                        <div class="medication-row">
                            <div class="medication-label">
                                <i class="far fa-calendar-alt"></i>
                                Last Refill
                            </div>
                            <div class="medication-value">March 15, 2025</div>
                        </div>
                        
                        <div class="refill-info">
                            <div class="refill-count">
                                <strong>Pills Remaining:</strong> 12 of 30
                            </div>
                            <div class="refill-alert">
                                <i class="fas fa-exclamation-circle"></i>
                                Refill needed in 12 days
                            </div>
                        </div>
                    </div>
                    
                    <div class="medication-detail-card">
                        <div class="medication-row">
                            <div class="medication-label">
                                <i class="fas fa-prescription-bottle-alt"></i>
                                Medication
                            </div>
                            <div class="medication-value">Metformin 500mg</div>
                        </div>
                        <div class="medication-row">
                            <div class="medication-label">
                                <i class="fas fa-pills"></i>
                                Category
                            </div>
                            <div class="medication-value">Biguanide (Diabetes)</div>
                        </div>
                        <div class="medication-row">
                            <div class="medication-label">
                                <i class="fas fa-clock"></i>
                                Schedule
                            </div>
                            <div class="medication-value">Once daily with lunch</div>
                        </div>
                        <div class="medication-row">
                            <div class="medication-label">
                                <i class="fas fa-utensils"></i>
                                Instructions
                            </div>
                            <div class="medication-value">Take with food to minimize stomach upset</div>
                        </div>
                        <div class="medication-row">
                            <div class="medication-label">
                                <i class="fas fa-exclamation-triangle"></i>
                                Side Effects
                            </div>
                            <div class="medication-value">May cause nausea, diarrhea, or stomach discomfort</div>
                        </div>
                        <div class="medication-row">
                            <div class="medication-label">
                                <i class="fas fa-user-md"></i>
                                Prescribed By
                            </div>
                            <div class="medication-value">Dr. Rahima Khan</div>
                        </div>
                        <div class="medication-row">
                            <div class="medication-label">
                                <i class="far fa-calendar-alt"></i>
                                Last Refill
                            </div>
                            <div class="medication-value">March 22, 2025</div>
                        </div>
                        
                        <div class="refill-info">
                            <div class="refill-count">
                                <strong>Pills Remaining:</strong> 22 of 30
                            </div>
                            <div class="refill-normal">
                                <i class="fas fa-check-circle"></i>
                                Refill needed in 22 days
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </main>
        
        <!-- Add Medication Modal -->
        <div class="modal" id="addMedicationModal">
            <div class="modal-content">
                <button class="modal-close" id="closeModal">&times;</button>
                <h2 class="modal-title">Add New Medication</h2>
                
                <form id="medicationForm">
                    <div class="form-group">
                        <label class="form-label" for="medicationName">Medication Name</label>
                        <input type="text" class="form-control" id="medicationName" placeholder="Enter medication name">
                    </div>
                    
                    <div class="form-row">
                        <div class="form-col">
                            <div class="form-group">
                                <label class="form-label" for="dosage">Dosage</label>
                                <input type="text" class="form-control" id="dosage" placeholder="e.g., 10mg">
                            </div>
                        </div>
                        <div class="form-col">
                            <div class="form-group">
                                <label class="form-label" for="quantity">Quantity</label>
                                <input type="number" class="form-control" id="quantity" placeholder="e.g., 1 tablet">
                            </div>
                        </div>
                    </div>
                    
                    <div class="form-group">
                        <label class="form-label" for="frequency">When to Take</label>
                        <select class="form-control" id="frequency">
                            <option value="morning">Morning</option>
                            <option value="afternoon">Afternoon</option>
                            <option value="evening">Evening</option>
                            <option value="bedtime">Before Bedtime</option>
                            <option value="custom">Custom Schedule</option>
                        </select>
                    </div>
                    
                    <div class="form-group">
                        <label class="form-label" for="instructions">Special Instructions</label>
                        <textarea class="form-control" id="instructions" rows="3" placeholder="e.g., Take with food"></textarea>
                    </div>
                    
                    <div class="form-row">
                        <div class="form-col">
                            <div class="form-group">
                                <label class="form-label" for="totalCount">Total Pills</label>
                                <input type="number" class="form-control" id="totalCount" placeholder="e.g., 30">
                            </div>
                        </div>
                        <div class="form-col">
                            <div class="form-group">
                                <label class="form-label" for="refillDate">Refill Date</label>
                                <input type="date" class="form-control" id="refillDate">
                            </div>
                        </div>
                    </div>
                    
                    <div class="form-group">
                        <label class="form-label" for="prescribedBy">Prescribed By</label>
                        <input type="text" class="form-control" id="prescribedBy" placeholder="Doctor's name">
                    </div>
                    
                    <div class="form-actions">
                        <button type="button" class="form-button secondary" id="cancelBtn">Cancel</button>
                        <button type="submit" class="form-button primary">Save Medication</button>
                    </div>
                </form>
            </div>
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
            // Initialize variables for recognition
            let recognition;
            let recognitionActive = false;
            const voiceAssistantBtn = document.getElementById('voiceAssistantBtn');
            const voiceFeedback = document.getElementById('voiceFeedback');
            const voiceResult = document.getElementById('voiceResult');
            
            // Mark medication button functionality
            document.getElementById('takeMedBtn1').addEventListener('click', function() {
                // Change button to "Taken" status
                const medicationAction = this.parentElement;
                medicationAction.innerHTML = '<span class="medication-status status-taken">✓ Taken</span>';
                
                // Show toast notification
                showToast("Medication marked as taken!");
            });
            
            // Modal functionality
            const modal = document.getElementById('addMedicationModal');
            const addMedicationBtn = document.getElementById('addMedicationBtn');
            const closeModalBtn = document.getElementById('closeModal');
            const cancelBtn = document.getElementById('cancelBtn');
            
            addMedicationBtn.addEventListener('click', function() {
                modal.classList.add('show');
            });
            
            closeModalBtn.addEventListener('click', function() {
                modal.classList.remove('show');
            });
            
            cancelBtn.addEventListener('click', function() {
                modal.classList.remove('show');
            });
            
            // Close modal if clicked outside
            window.addEventListener('click', function(event) {
                if (event.target === modal) {
                    modal.classList.remove('show');
                }
            });
            
            // Handle form submission (just prevent default for demo)
            document.getElementById('medicationForm').addEventListener('submit', function(event) {
                event.preventDefault();
                // Here you would normally process the form data
                showToast("New medication added successfully!");
                modal.classList.remove('show');
            });
            
            // Initialize speech recognition
            function initSpeechRecognition() {
                if ('webkitSpeechRecognition' in window) {
                    recognition = new webkitSpeechRecognition();
                    recognition.continuous = false;
                    recognition.interimResults = true;
                    recognition.lang = 'en-US';
                    
                    // Recognition started
                    recognition.onstart = function() {
                        recognitionActive = true;
                        voiceAssistantBtn.classList.add('listening');
                        voiceFeedback.style.display = 'block';
                        voiceResult.textContent = 'Listening...';
                    };
                    
                    // Results received
                    recognition.onresult = function(event) {
                        let transcript = '';
                        for (let i = event.resultIndex; i < event.results.length; i++) {
                            if (event.results[i].isFinal) {
                                transcript += event.results[i][0].transcript;
                            } else {
                                // Show interim results
                                let interimTranscript = event.results[i][0].transcript;
                                voiceResult.textContent = interimTranscript;
                            }
                        }
                        
                        if (transcript) {
                            voiceResult.textContent = transcript;
                            
                            // Process after a short delay to show the recognized text
                            setTimeout(function() {
                                processVoiceCommand(transcript.toLowerCase());
                            }, 500);
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
                    
                    // Handle errors
                    recognition.onerror = function(event) {
                        recognitionActive = false;
                        voiceAssistantBtn.classList.remove('listening');
                        voiceResult.textContent = 'Error: ' + event.error;
                        
                        setTimeout(function() {
                            voiceFeedback.style.display = 'none';
                        }, 2000);
                    };
                    
                    return true;
                } else {
                    alert("Voice recognition is not supported in your browser. Try using Chrome for best experience.");
                    return false;
                }
            }
            
            // Process voice commands with better handling for the medication page
            function processVoiceCommand(command) {
                // Combine similar intents to make recognition more robust
                let commandHandled = false;
                
                // Today's medications command
                if (command.includes('today') && (command.includes('med') || command.includes('pill'))) {
                    voiceResult.textContent = 'Showing today\'s medications';
                    speakResponse("Here are your medications for today");
                    
                    scrollToElement('.medication-section:first-child');
                    highlightElement('.medication-section:first-child');
                    commandHandled = true;
                }
                
                // Morning medications command
                else if ((command.includes('morning') || command.includes('breakfast')) && 
                       (command.includes('med') || command.includes('pill'))) {
                    voiceResult.textContent = 'Showing morning medications';
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
        <title>Shohochor - Medication Tracking</title>
        <meta name="description" content="Track, manage and get reminders for your medications with Shohochor's easy-to-use medication tracking system.">
        <link href="./medication-tracking.php" rel="canonical">
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
        
        <style>
            /* Medication Tracking Specific Styles */
            .medication-dashboard {
                margin-bottom: 30px;
            }
            
            .medication-section {
                background-color: white;
                border-radius: 16px;
                padding: 30px;
                box-shadow: 0 4px 15px rgba(0,0,0,0.08);
                margin-bottom: 30px;
                border: 1px solid rgba(0,150,173,0.1);
                animation: fadeIn 0.5s ease-out;
            }
            
            .section-heading {
                font-size: 1.8rem;
                color: #2d3748;
                margin-bottom: 25px;
                text-align: center;
                position: relative;
            }
            
            .section-heading:after {
                content: '';
                position: absolute;
                width: 80px;
                height: 3px;
                background: #0096ad;
                bottom: -10px;
                left: 50%;
                transform: translateX(-50%);
            }
            
            .medication-list {
                list-style: none;
                padding: 0;
                margin: 0;
            }
            
            .medication-item {
                background-color: #f8f9fa;
                border-radius: 12px;
                padding: 20px;
                margin-bottom: 15px;
                border-left: 5px solid #0096ad;
                display: flex;
                justify-content: space-between;
                align-items: center;
                transition: transform 0.3s ease, box-shadow 0.3s ease;
            }
            
            .medication-item:hover {
                transform: translateX(5px);
                box-shadow: 0 5px 15px rgba(0,0,0,0.08);
            }
            
            .medication-info {
                flex-grow: 1;
            }
            
            .medication-name {
                font-weight: 600;
                font-size: 1.2rem;
                color: #2d3748;
                margin-bottom: 5px;
                display: flex;
                align-items: center;
                gap: 8px;
            }
            
            .medication-name i {
                color: #0096ad;
            }
            
            .medication-details {
                color: #4a5568;
                font-size: 0.95rem;
                margin-bottom: 8px;
            }
            
            .medication-schedule {
                color: #718096;
                font-size: 0.9rem;
                display: flex;
                align-items: center;
                gap: 5px;
            }
            
            .medication-instructions {
                margin-top: 8px;
                color: #4a5568;
                font-size: 0.9rem;
                font-style: italic;
            }
            
            .medication-action {
                flex-shrink: 0;
                margin-left: 15px;
            }
            
            .medication-button {
                background-color: #0096ad;
                color: white;
                border: none;
                border-radius: 50px;
                padding: 10px 20px;
                font-weight: 600;
                font-size: 0.9rem;
                cursor: pointer;
                transition: all 0.3s ease;
                display: flex;
                align-items: center;
                gap: 8px;
            }
            
            .medication-button:hover {
                background-color: #007489;
                transform: translateY(-2px);
                box-shadow: 0 4px 10px rgba(0,150,173,0.3);
            }
            
            .medication-button.taken {
                background-color: #48bb78;
            }
            
            .medication-button.skipped {
                background-color: #ed8936;
            }
            
            .medication-status {
                display: inline-block;
                padding: 5px 15px;
                border-radius: 50px;
                font-size: 0.85rem;
                font-weight: 600;
                margin-left: 15px;
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
            
            .status-missed {
                background-color: #fed7d7;
                color: #822727;
                animation: pulse 2s infinite;
            }
            
            @keyframes pulse {
                0% {
                    box-shadow: 0 0 0 0 rgba(130, 39, 39, 0.4);
                }
                70% {
                    box-shadow: 0 0 0 8px rgba(130, 39, 39, 0);
                }
                100% {
                    box-shadow: 0 0 0 0 rgba(130, 39, 39, 0);
                }
            }
            
            .medication-time-group {
                margin-bottom: 30px;
            }
            
            .time-heading {
                background-color: #0096ad;
                color: white;
                padding: 10px 20px;
                border-radius: 8px 8px 0 0;
                font-weight: 600;
                font-size: 1.1rem;
                display: flex;
                align-items: center;
                gap: 10px;
                margin-bottom: 15px;
            }
            
            .history-grid {
                display: grid;
                grid-template-columns: repeat(7, 1fr);
                gap: 15px;
                margin-bottom: 30px;
            }
            
            .history-day {
                background-color: #f8f9fa;
                border-radius: 10px;
                padding: 15px 10px;
                text-align: center;
                box-shadow: 0 2px 5px rgba(0,0,0,0.05);
            }
            
            .day-name {
                font-weight: 600;
                color: #2d3748;
                margin-bottom: 5px;
                font-size: 0.95rem;
            }
            
            .day-date {
                color: #718096;
                font-size: 0.85rem;
                margin-bottom: 10px;
            }
            
            .day-status {
                display: flex;
                flex-direction: column;
                align-items: center;
                gap: 5px;
            }
            
            .status-dot {
                width: 15px;
                height: 15px;
                border-radius: 50%;
                display: inline-block;
            }
            
            .dot-taken {
                background-color: #48bb78;
            }
            
            .dot-missed {
                background-color: #e53e3e;
            }
            
            .dot-skipped {
                background-color: #ed8936;
            }
            
            .dot-upcoming {
                background-color: #a0aec0;
                border: 1px solid #718096;
            }
            
            .status-label {
                font-size: 0.8rem;
                color: #4a5568;
            }
            
            .medication-actions {
                display: flex;
                justify-content: center;
                gap: 15px;
                margin-top: 20px;
            }
            
            .action-button {
                background-color: #0096ad;
                color: white;
                border: none;
                border-radius: 8px;
                padding: 15px 25px;
                font-weight: 600;
                font-size: 1rem;
                cursor: pointer;
                transition: all 0.3s ease;
                display: flex;
                align-items: center;
                gap: 10px;
                box-shadow: 0 4px 10px rgba(0,150,173,0.2);
            }
            
            .action-button:hover {
                background-color: #007489;
                transform: translateY(-2px);
                box-shadow: 0 6px 15px rgba(0,150,173,0.3);
            }
            
            .action-button.secondary {
                background-color: white;
                color: #0096ad;
                border: 2px solid #0096ad;
                box-shadow: none;
            }
            
            .action-button.secondary:hover {
                background-color: #f0fafc;
                transform: translateY(-2px);
                box-shadow: 0 4px 10px rgba(0,150,173,0.1);
            }
            
            .medication-detail-card {
                background-color: #f8f9fa;
                border-radius: 12px;
                padding: 25px;
                margin-bottom: 20px;
                border-left: 5px solid #0096ad;
                animation: fadeIn 0.5s ease-out;
            }
            
            .medication-row {
                display: flex;
                margin-bottom: 12px;
                align-items: center;
            }
            
            .medication-label {
                width: 180px;
                font-weight: 600;
                color: #4a5568;
                display: flex;
                align-items: center;
                gap: 8px;
            }
            
            .medication-label i {
                color: #0096ad;
            }
            
            .medication-value {
                color: #2d3748;
                flex-grow: 1;
            }
            
            .refill-info {
                margin-top: 15px;
                padding-top: 15px;
                border-top: 1px dashed #cbd5e0;
                display: flex;
                justify-content: space-between;
                align-items: center;
            }
            
            .refill-count {
                padding: 5px 15px;
                background-color: #ebf8ff;
                color: #2b6cb0;
                border-radius: 50px;
                font-weight: 600;
                font-size: 0.9rem;
            }
            
            .refill-alert {
                padding: 5px 15px;
                background-color: #fed7d7;
                color: #822727;
                border-radius: 50px;
                font-weight: 600;
                font-size: 0.9rem;
                display: flex;
                align-items: center;
                gap: 5px;
            }
            
            .refill-normal {
                padding: 5px 15px;
                background-color: #c6f6d5;
                color: #22543d;
                border-radius: 50px;
                font-weight: 600;
                font-size: 0.9rem;
                display: flex;
                align-items: center;
                gap: 5px;
            }
            
            /* Calendar View Styles */
            .calendar {
                border-radius: 12px;
                overflow: hidden;
                margin-bottom: 30px;
                box-shadow: 0 4px 10px rgba(0,0,0,0.08);
                background-color: white;
            }
            
            .calendar-header {
                background-color: #0096ad;
                color: white;
                padding: 15px;
                display: flex;
                justify-content: space-between;
                align-items: center;
            }
            
            .calendar-title {
                font-size: 1.2rem;
                font-weight: 600;
            }
            
            .calendar-nav {
                display: flex;
                gap: 10px;
            }
            
            .calendar-nav-btn {
                background: rgba(255, 255, 255, 0.2);
                border: none;
                border-radius: 50%;
                width: 35px;
                height: 35px;
                display: flex;
                align-items: center;
                justify-content: center;
                cursor: pointer;
                transition: all 0.3s ease;
            }
            
            .calendar-nav-btn:hover {
                background: rgba(255, 255, 255, 0.4);
            }
            
            .calendar-days {
                display: grid;
                grid-template-columns: repeat(7, 1fr);
                padding: 10px;
            }
            
            .weekday {
                text-align: center;
                padding: 10px;
                font-weight: 600;
                color: #718096;
                font-size: 0.9rem;
            }
            
            .calendar-dates {
                display: grid;
                grid-template-columns: repeat(7, 1fr);
                gap: 5px;
                padding: 0 10px 10px;
            }
            
            .date-cell {
                height: 70px;
                border-radius: 8px;
                padding: 5px;
                position: relative;
                cursor: pointer;
                transition: all 0.3s ease;
            }
            
            .date-cell:hover {
                background-color: #f0fafc;
            }
            
            .date-cell.current-month {
                background-color: #f8f9fa;
            }
            
            .date-cell.other-month {
                background-color: #edf2f7;
                opacity: 0.7;
            }
            
            .date-cell.today {
                background-color: #ebf8ff;
                border: 2px solid #0096ad;
            }
            
            .date-number {
                position: absolute;
                top: 5px;
                right: 8px;
                font-size: 0.85rem;
                font-weight: 600;
                color: #4a5568;
            }
            
            .date-events {
                position: absolute;
                bottom: 5px;
                left: 0;
                right: 0;
                display: flex;
                flex-direction: column;
                align-items: center;
                gap: 2px;
            }
            
            .date-event {
                width: 8px;
                height: 8px;
                border-radius: 50%;
                margin: 0 1px;
            }
            
            .event-medication {
                background-color: #0096ad;
            }
            
            .event-refill {
                background-color: #ed8936;
            }
            
            .event-doctor {
                background-color: #9f7aea;
            }
            
            /* Voice feedback dialog */
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
            
            /* Fixed accessibility controls positioning */
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
            
            /* Modal for editing medications */
            .modal {
                display: none;
                position: fixed;
                top: 0;
                left: 0;
                width: 100%;
                height: 100%;
                background-color: rgba(0, 0, 0, 0.5);
                z-index: 1000;
                opacity: 0;
                transition: opacity 0.3s ease;
                justify-content: center;
                align-items: center;
            }
            
            .modal.show {
                display: flex;
                opacity: 1;
            }
            
            .modal-content {
                background-color: #ffffff;
                border-radius: 16px;
                width: 90%;
                max-width: 600px;
                box-shadow: 0 10px 30px rgba(0, 0, 0, 0.2);
                padding: 30px;
                position: relative;
                transform: translateY(-30px);
                transition: transform 0.3s ease;
                max-height: 80vh;
                overflow-y: auto;
            }
            
            .modal.show .modal-content {
                transform: translateY(0);
            }
            
            .modal-close {
                position: absolute;
                top: 20px;
                right: 20px;
                background: none;
                border: none;
                font-size: 1.5rem;
                cursor: pointer;
                color: #718096;
                transition: color 0.3s ease;
            }
            
            .modal-close:hover {
                color: #e53e3e;
            }
            
            .modal-title {
                color: #2d3748;
                margin-top: 0;
                margin-bottom: 20px;
                text-align: center;
            }
            
            .form-group {
                margin-bottom: 20px;
            }
            
            .form-label {
                font-weight: 600;
                color: #4a5568;
                margin-bottom: 8px;
                display: block;
            }
            
            .form-control {
                width: 100%;
                padding: 12px 15px;
                border: 1px solid #cbd5e0;
                border-radius: 8px;
                font-size: 1rem;
                transition: border-color 0.3s ease;
            }
            
            .form-control:focus {
                outline: none;
                border-color: #0096ad;
                box-shadow: 0 0 0 3px rgba(0, 150, 173, 0.2);
            }
            
            .form-row {
                display: flex;
                gap: 15px;
            }
            
            .form-col {
                flex: 1;
            }
            
            .form-actions {
                display: flex;
                justify-content: center;
                gap: 15px;
                margin-top: 30px;
            }
            
            .form-button {
                padding: 12px 25px;
                border-radius: 8px;
                font-weight: 600;
                cursor: pointer;
                transition: all 0.3s ease;
            }
            
            .form-button.primary {
                background-color: #0096ad;
                color: white;
                border: none;
            }
            
            .form-button.primary:hover {
                background-color: #007489;
            }
            
            .form-button.secondary {
                background-color: white;
                color: #4a5568;
                border: 1px solid #cbd5e0;
            }
            
            .form-button.secondary:hover {
                background-color: #f7fafc;
            }
            
            /* Responsive design */
            @media (max-width: 992px) {
                .history-grid {
                    grid-template-columns: repeat(4, 1fr);
                }
                
                .form-row {
                    flex-direction: column;
                    gap: 20px;
                }
            }
            
            @media (max-width: 768px) {
                .section-heading {
                    font-size: 1.5rem;
                }
                
                .medication-item {
                    flex-direction: column;
                    align-items: flex-start;
                }
                
                .medication-action {
                    margin-left: 0;
                    margin-top: 15px;
                    width: 100%;
                }
                
                .medication-button {
                    width: 100%;
                    justify-content: center;
                }
                
                .medication-status {
                    margin-left: 0;
                    margin-top: 10px;
                }
                
                .medication-actions {
                    flex-direction: column;
                    gap: 10px;
                }
                
                .action-button {
                    width: 100%;
                    justify-content: center;
                }
                
                .history-grid {
                    grid-template-columns: repeat(3, 1fr);
                }
                
                .calendar-dates {
                    gap: 2px;
                }
                
                .date-cell {
                    height: 60px;
                }
                
                .accessibility-controls {
                    top: auto;
                    bottom: 100px;
                    left: 10px;
                    transform: none;
                }
            }
            
            @media (max-width: 576px) {
                .history-grid {
                    grid-template-columns: repeat(2, 1fr);
                }
                
                .medication-row {
                    flex-direction: column;
                    align-items: flex-start;
                }
                
                .medication-label {
                    margin-bottom: 5px;
                }
                
                .calendar-days, .calendar-dates {
                    grid-template-columns: repeat(7, 1fr);
                }
                
                .weekday {
                    font-size: 0.7rem;
                    padding: 5px;
                }
                
                .date-cell {
                    height: 50px;
                }
                
                .date-number {
                    font-size: 0.7rem;
                }
                
                .accessibility-controls {
                    bottom: 160px;
                }
                
                .accessibility-controls button {
                    width: 40px;
                    height: 40px;
                }
            }
        </style>
    </head>
