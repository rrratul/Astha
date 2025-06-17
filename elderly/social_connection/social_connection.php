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
        <title>Shohochor - Social Connections</title>
        <meta name="description" content="Connect with family, friends and community through Shohochor's easy-to-use social connections platform designed for elderly users.">
        <link href="./social_connection.php" rel="canonical">
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
            /* Social Connections Specific Styles */
            .social-dashboard {
                margin-bottom: 30px;
            }
            
            .section-heading {
                font-size: 1.8rem;
                color: #2d3748;
                margin-bottom: 20px;
                position: relative;
                padding-bottom: 10px;
                border-bottom: 2px solid #e2e8f0;
                display: flex;
                align-items: center;
                gap: 10px;
            }
            
            .section-heading i {
                color: #0096ad;
            }
            
            .social-grid {
                display: grid;
                grid-template-columns: repeat(3, 1fr);
                gap: 25px;
                margin-bottom: 30px;
            }
            
            .connection-card {
                background-color: white;
                border-radius: 16px;
                overflow: hidden;
                box-shadow: 0 4px 15px rgba(0,0,0,0.08);
                transition: transform 0.3s ease, box-shadow 0.3s ease;
                border: 1px solid rgba(0,150,173,0.1);
                height: 100%;
                display: flex;
                flex-direction: column;
            }
            
            .connection-card:hover {
                transform: translateY(-5px);
                box-shadow: 0 8px 20px rgba(0,0,0,0.12);
            }
            
            .card-header {
                position: relative;
                height: 160px;
                overflow: hidden;
                background-color: #f8f9fa;
            }
            
            .card-header img {
                width: 100%;
                height: 100%;
                object-fit: cover;
                transition: transform 0.3s ease;
            }
            
            .connection-card:hover .card-header img {
                transform: scale(1.05);
            }
            
            .card-badge {
                position: absolute;
                top: 15px;
                right: 15px;
                background-color: #0096ad;
                color: white;
                font-size: 0.8rem;
                padding: 4px 12px;
                border-radius: 30px;
                font-weight: 600;
            }
            
            .card-content {
                padding: 20px;
                flex-grow: 1;
                display: flex;
                flex-direction: column;
            }
            
            .card-title {
                font-size: 1.3rem;
                font-weight: 600;
                color: #2d3748;
                margin-bottom: 10px;
            }
            
            .connection-info {
                display: flex;
                align-items: center;
                gap: 12px;
                margin-bottom: 8px;
            }
            
            .connection-avatar {
                width: 50px;
                height: 50px;
                border-radius: 50%;
                object-fit: cover;
                border: 2px solid #0096ad;
            }
            
            .connection-name {
                font-size: 1.1rem;
                color: #4a5568;
                margin-bottom: 3px;
                font-weight: 600;
            }
            
            .connection-relation {
                color: #718096;
                font-size: 0.9rem;
            }
            
            .card-description {
                color: #4a5568;
                font-size: 0.95rem;
                margin: 10px 0 15px;
                line-height: 1.5;
                flex-grow: 1;
            }
            
            .card-actions {
                display: flex;
                gap: 10px;
                margin-top: auto;
            }
            
            .action-button {
                flex: 1;
                padding: 10px 15px;
                border-radius: 8px;
                font-size: 0.9rem;
                font-weight: 600;
                display: flex;
                align-items: center;
                justify-content: center;
                gap: 8px;
                transition: all 0.3s ease;
                text-decoration: none;
                border: none;
                cursor: pointer;
            }
            
            .call-button {
                background-color: #c6f6d5;
                color: #22543d;
            }
            
            .call-button:hover {
                background-color: #9ae6b4;
                transform: translateY(-2px);
            }
            
            .message-button {
                background-color: #e2e8f0;
                color: #4a5568;
            }
            
            .message-button:hover {
                background-color: #cbd5e0;
                transform: translateY(-2px);
            }
            
            .video-button {
                background-color: #bee3f8;
                color: #2b6cb0;
            }
            
            .video-button:hover {
                background-color: #90cdf4;
                transform: translateY(-2px);
            }
            
            .schedule-button {
                background-color: #feebc8;
                color: #744210;
            }
            
            .schedule-button:hover {
                background-color: #fbd38d;
                transform: translateY(-2px);
            }
            
            .full-button {
                width: 100%;
                justify-content: center;
                padding: 12px;
                background-color: #0096ad;
                color: white;
                border-radius: 8px;
                font-weight: 600;
                text-decoration: none;
                display: inline-flex;
                align-items: center;
                gap: 8px;
                transition: all 0.3s ease;
                margin-top: 15px;
            }
            
            .full-button:hover {
                background-color: #007489;
                transform: translateY(-2px);
                box-shadow: 0 4px 12px rgba(0,150,173,0.2);
            }
            
            .scheduled-calls {
                background-color: white;
                border-radius: 16px;
                box-shadow: 0 4px 15px rgba(0,0,0,0.08);
                padding: 25px;
                margin-bottom: 30px;
                border: 1px solid rgba(0,150,173,0.1);
            }
            
            .call-list {
                list-style: none;
                padding: 0;
                margin: 0;
            }
            
            .call-item {
                display: flex;
                align-items: center;
                gap: 15px;
                padding: 15px 0;
                border-bottom: 1px solid #e2e8f0;
                position: relative;
                transition: all 0.3s ease;
            }

            .call-item:hover {
                transform: translateY(-2px);
            }
            
            .call-item:last-child {
                border-bottom: none;
            }
            
            .call-time {
                width: 80px;
                height: 80px;
                background-color: #0096ad;
                color: white;
                border-radius: 12px;
                display: flex;
                flex-direction: column;
                align-items: center;
                justify-content: center;
                font-weight: 600;
                flex-shrink: 0;
            }
            
            .call-hour {
                font-size: 1.5rem;
                line-height: 1;
                margin-bottom: 2px;
            }
            
            .call-date {
                font-size: 0.8rem;
                opacity: 0.9;
            }
            
            .call-details {
                flex-grow: 1;
            }
            
            .call-with {
                font-size: 1.1rem;
                color: #2d3748;
                margin-bottom: 5px;
                font-weight: 600;
            }
            
            .call-type {
                display: flex;
                align-items: center;
                gap: 8px;
                font-size: 0.9rem;
                color: #718096;
                margin-bottom: 5px;
            }
            
            .call-reminder {
                font-size: 0.85rem;
                color: #4a5568;
                display: flex;
                align-items: center;
                gap: 5px;
            }
            
            .call-actions {
                display: flex;
                gap: 10px;
                flex-shrink: 0;
            }
            
            .call-action-button {
                width: 40px;
                height: 40px;
                border-radius: 50%;
                display: flex;
                align-items: center;
                justify-content: center;
                cursor: pointer;
                transition: all 0.3s ease;
                color: white;
                border: none;
            }
            
            .join-call {
                background-color: #38b2ac;
            }
            
            .join-call:hover {
                background-color: #2c9a94;
                transform: translateY(-2px);
            }
            
            .reschedule-call {
                background-color: #f6ad55;
            }
            
            .reschedule-call:hover {
                background-color: #ed8936;
                transform: translateY(-2px);
            }
            
            .cancel-call {
                background-color: #fc8181;
            }
            
            .cancel-call:hover {
                background-color: #f56565;
                transform: translateY(-2px);
            }
            
            /* Community Section Styles */
            .community-section {
                margin-bottom: 30px;
            }
            
            .community-grid {
                display: grid;
                grid-template-columns: repeat(2, 1fr);
                gap: 25px;
                margin-bottom: 25px;
            }
            
            .community-card {
                background-color: white;
                border-radius: 16px;
                overflow: hidden;
                box-shadow: 0 4px 15px rgba(0,0,0,0.08);
                transition: transform 0.3s ease, box-shadow 0.3s ease;
                border: 1px solid rgba(0,150,173,0.1);
                height: 100%;
                display: flex;
                flex-direction: column;
            }
            
            .community-card:hover {
                transform: translateY(-5px);
                box-shadow: 0 8px 20px rgba(0,0,0,0.12);
            }
            
            .community-card-header {
                position: relative;
                height: 180px;
                overflow: hidden;
            }
            
            .community-card-header img {
                width: 100%;
                height: 100%;
                object-fit: cover;
                transition: transform 0.3s ease;
            }
            
            .community-card:hover .community-card-header img {
                transform: scale(1.05);
            }
            
            .community-stats {
                position: absolute;
                bottom: 15px;
                left: 15px;
                background-color: rgba(0,0,0,0.6);
                color: white;
                padding: 5px 12px;
                border-radius: 30px;
                font-size: 0.85rem;
                display: flex;
                align-items: center;
                gap: 8px;
            }
            
            .community-stats > div {
                display: flex;
                align-items: center;
                gap: 5px;
            }
            
            .community-card-content {
                padding: 20px;
                flex-grow: 1;
                display: flex;
                flex-direction: column;
            }
            
            .community-title {
                font-size: 1.3rem;
                font-weight: 600;
                color: #2d3748;
                margin-bottom: 10px;
            }
            
            .community-description {
                color: #4a5568;
                font-size: 0.95rem;
                line-height: 1.5;
                margin-bottom: 15px;
                flex-grow: 1;
            }
            
            .next-event {
                background-color: #f8f9fa;
                padding: 12px;
                border-radius: 8px;
                margin-bottom: 15px;
            }
            
            .next-event-label {
                font-size: 0.85rem;
                color: #718096;
                margin-bottom: 5px;
                display: flex;
                align-items: center;
                gap: 5px;
            }
            
            .next-event-title {
                font-weight: 600;
                color: #2d3748;
                margin-bottom: 5px;
            }
            
            .next-event-time {
                font-size: 0.9rem;
                color: #4a5568;
                display: flex;
                align-items: center;
                gap: 5px;
            }
            
            /* Stories Section */
            .stories-section {
                margin-bottom: 30px;
            }
            
            .stories-slider {
                display: flex;
                gap: 20px;
                overflow-x: auto;
                padding: 10px 0 20px;
                scroll-behavior: smooth;
                scrollbar-width: none; /* Firefox */
                -ms-overflow-style: none; /* Internet Explorer 11 */
            }
            
            .stories-slider::-webkit-scrollbar {
                display: none; /* WebKit browsers */
            }
            
            .story-card {
                min-width: 280px;
                width: 280px;
                background-color: white;
                border-radius: 16px;
                overflow: hidden;
                box-shadow: 0 4px 15px rgba(0,0,0,0.08);
                transition: transform 0.3s ease, box-shadow 0.3s ease;
                border: 1px solid rgba(0,150,173,0.1);
                display: flex;
                flex-direction: column;
            }
            
            .story-card:hover {
                transform: translateY(-5px);
                box-shadow: 0 8px 20px rgba(0,0,0,0.12);
            }
            
            .story-card-header {
                position: relative;
                height: 140px;
                overflow: hidden;
            }
            
            .story-card-header img {
                width: 100%;
                height: 100%;
                object-fit: cover;
                transition: transform 0.3s ease;
            }
            
            .story-card:hover .story-card-header img {
                transform: scale(1.05);
            }
            
            .story-card-content {
                padding: 20px;
                flex-grow: 1;
                display: flex;
                flex-direction: column;
            }
            
            .story-title {
                font-size: 1.1rem;
                font-weight: 600;
                color: #2d3748;
                margin-bottom: 10px;
            }
            
            .story-author {
                display: flex;
                align-items: center;
                gap: 10px;
                margin-bottom: 15px;
            }
            
            .author-avatar {
                width: 40px;
                height: 40px;
                border-radius: 50%;
                object-fit: cover;
            }
            
            .author-info {
                display: flex;
                flex-direction: column;
            }
            
            .author-name {
                font-size: 0.9rem;
                font-weight: 600;
                color: #4a5568;
            }
            
            .story-date {
                font-size: 0.8rem;
                color: #718096;
            }
            
            .story-excerpt {
                color: #4a5568;
                font-size: 0.9rem;
                line-height: 1.5;
                margin-bottom: 15px;
                flex-grow: 1;
                display: -webkit-box;
                -webkit-line-clamp: 3;
                -webkit-box-orient: vertical;
                overflow: hidden;
            }
            
            .story-footer {
                display: flex;
                justify-content: space-between;
                align-items: center;
                padding-top: 10px;
                border-top: 1px solid #e2e8f0;
            }
            
            .story-stats {
                display: flex;
                align-items: center;
                gap: 15px;
                color: #718096;
                font-size: 0.85rem;
            }
            
            .story-stat {
                display: flex;
                align-items: center;
                gap: 5px;
            }
            
            .story-link {
                color: #0096ad;
                font-size: 0.9rem;
                font-weight: 600;
                text-decoration: none;
                transition: all 0.3s ease;
                display: flex;
                align-items: center;
                gap: 5px;
            }
            
            .story-link:hover {
                color: #007489;
            }
            
            /* New Connections Section */
            .new-connections-section {
                margin-bottom: 30px;
            }
            
            .find-connections {
                background-color: white;
                border-radius: 16px;
                padding: 25px;
                box-shadow: 0 4px 15px rgba(0,0,0,0.08);
                border: 1px solid rgba(0,150,173,0.1);
                margin-bottom: 30px;
            }
            
            .search-box {
                display: flex;
                gap: 10px;
                margin-bottom: 20px;
            }
            
            .search-input {
                flex-grow: 1;
                padding: 12px 15px;
                border: 1px solid #e2e8f0;
                border-radius: 8px;
                font-size: 1rem;
            }
            
            .search-input:focus {
                outline: none;
                border-color: #0096ad;
            }
            
            .search-button {
                background-color: #0096ad;
                color: white;
                border: none;
                border-radius: 8px;
                padding: 0 20px;
                font-weight: 600;
                cursor: pointer;
                transition: all 0.3s ease;
            }
            
            .search-button:hover {
                background-color: #007489;
                box-shadow: 0 4px 12px rgba(0,150,173,0.2);
            }
            
            .connection-filters {
                display: flex;
                gap: 10px;
                flex-wrap: wrap;
            }
            
            .filter-button {
                padding: 8px 15px;
                border: 1px solid #e2e8f0;
                border-radius: 30px;
                font-size: 0.9rem;
                color: #4a5568;
                background-color: white;
                cursor: pointer;
                transition: all 0.3s ease;
            }
            
            .filter-button.active {
                background-color: #0096ad;
                color: white;
                border-color: #0096ad;
            }
            
            .filter-button:hover {
                background-color: #e2e8f0;
            }
            
            .filter-button.active:hover {
                background-color: #007489;
            }
            
            .suggested-connections {
                display: grid;
                grid-template-columns: repeat(3, 1fr);
                gap: 20px;
                margin-top: 30px;
            }
            
            .suggestion-card {
                background-color: white;
                border-radius: 12px;
                padding: 20px;
                box-shadow: 0 4px 10px rgba(0,0,0,0.05);
                transition: transform 0.3s ease, box-shadow 0.3s ease;
                display: flex;
                flex-direction: column;
                align-items: center;
                text-align: center;
                border: 1px solid rgba(0,150,173,0.1);
            }
            
            .suggestion-card:hover {
                transform: translateY(-5px);
                box-shadow: 0 8px 20px rgba(0,0,0,0.1);
            }
            
            .suggestion-avatar {
                width: 80px;
                height: 80px;
                border-radius: 50%;
                object-fit: cover;
                margin-bottom: 15px;
                border: 3px solid #e2e8f0;
            }
            
            .suggestion-name {
                font-size: 1.1rem;
                font-weight: 600;
                color: #2d3748;
                margin-bottom: 5px;
            }
            
            .suggestion-details {
                font-size: 0.9rem;
                color: #718096;
                margin-bottom: 15px;
                flex-grow: 1;
            }
            
            .suggestion-button {
                width: 100%;
                padding: 8px 0;
                border-radius: 30px;
                font-size: 0.9rem;
                font-weight: 600;
                cursor: pointer;
                transition: all 0.3s ease;
                border: none;
            }
            
            .connect-button {
                background-color: #0096ad;
                color: white;
            }
            
            .connect-button:hover {
                background-color: #007489;
            }
            
            .view-profile-button {
                background-color: #e2e8f0;
                color: #4a5568;
                margin-top: 10px;
            }
            
            .view-profile-button:hover {
                background-color: #cbd5e0;
            }
            
            /* Voice Assistant and Accessibility */
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

            /* Call Modal */
            .modal {
                display: none;
                position: fixed;
                top: 0;
                left: 0;
                width: 100%;
                height: 100%;
                background-color: rgba(0,0,0,0.5);
                z-index: 1000;
                justify-content: center;
                align-items: center;
            }

            .modal-content {
                background-color: white;
                border-radius: 16px;
                padding: 30px;
                width: 90%;
                max-width: 500px;
                box-shadow: 0 4px 20px rgba(0,0,0,0.2);
                position: relative;
                transform: translateY(20px);
                opacity: 0;
                transition: all 0.3s ease;
            }

            .modal.active {
                display: flex;
            }

            .modal.active .modal-content {
                transform: translateY(0);
                opacity: 1;
            }

            .modal-header {
                display: flex;
                justify-content: space-between;
                align-items: center;
                margin-bottom: 20px;
                padding-bottom: 10px;
                border-bottom: 1px solid #e2e8f0;
            }

            .modal-title {
                font-size: 1.5rem;
                font-weight: 600;
                color: #2d3748;
                margin: 0;
            }

            .close-modal {
                background: none;
                border: none;
                font-size: 1.5rem;
                cursor: pointer;
                color: #718096;
                padding: 0;
                line-height: 1;
                transition: color 0.3s ease;
            }

            .close-modal:hover {
                color: #e53e3e;
            }

            .call-container {
                text-align: center;
            }

            .call-contact {
                margin-bottom: 20px;
            }

            .contact-avatar {
                width: 100px;
                height: 100px;
                border-radius: 50%;
                object-fit: cover;
                margin: 0 auto 15px;
                border: 4px solid #0096ad;
            }

            .contact-name {
                font-size: 1.5rem;
                font-weight: 600;
                color: #2d3748;
                margin-bottom: 5px;
            }

            .call-status {
                font-size: 1.1rem;
                color: #718096;
                margin-bottom: 30px;
            }

            .call-controls {
                display: flex;
                justify-content: center;
                gap: 20px;
            }

            .call-control-btn {
                width: 60px;
                height: 60px;
                border-radius: 50%;
                display: flex;
                align-items: center;
                justify-content: center;
                border: none;
                cursor: pointer;
                transition: all 0.3s ease;
            }

            .end-call {
                background-color: #e53e3e;
                color: white;
            }

            .end-call:hover {
                background-color: #c53030;
                transform: scale(1.1);
            }

            .mute-call {
                background-color: #e2e8f0;
                color: #4a5568;
            }

            .mute-call:hover {
                background-color: #cbd5e0;
                transform: scale(1.1);
            }

            .video-toggle {
                background-color: #e2e8f0;
                color: #4a5568;
            }

            .video-toggle:hover {
                background-color: #cbd5e0;
                transform: scale(1.1);
            }

            .call-timer {
                font-size: 1.2rem;
                font-weight: 600;
                color: #4a5568;
                margin-top: 20px;
            }
            
            /* Responsive Design */
            @media (max-width: 992px) {
                .social-grid {
                    grid-template-columns: repeat(2, 1fr);
                }
                
                .community-grid {
                    grid-template-columns: 1fr;
                }
                
                .suggested-connections {
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
                .social-grid {
                    grid-template-columns: 1fr;
                }
                
                .call-item {
                    flex-direction: column;
                    align-items: flex-start;
                    gap: 15px;
                    padding: 15px 0;
                }
                
                .call-actions {
                    width: 100%;
                    justify-content: flex-start;
                }
                
                .accessibility-controls {
                    top: auto;
                    bottom: 100px;
                    left: 10px;
                    transform: none;
                }
                
                .suggested-connections {
                    grid-template-columns: 1fr;
                }
                
                .story-card {
                    min-width: 260px;
                    width: 260px;
                }

                .voice-assistant {
                    right: 100px;
                }

                .emergency-button {
                    width: 60px;
                    height: 60px;
                }
            }
            
            @media (max-width: 576px) {
                .accessibility-controls {
                    bottom: 160px;
                }
                
                .accessibility-controls button {
                    width: 40px;
                    height: 40px;
                }
                
                .card-actions {
                    flex-direction: column;
                }
                
                .search-box {
                    flex-direction: column;
                }
                
                .search-button {
                    height: 45px;
                }

                .voice-assistant {
                    right: 90px;
                    width: 50px;
                    height: 50px;
                }

                .emergency-button {
                    width: 50px;
                    height: 50px;
                }
            }
            
            /* Loading animation */
            .loading-overlay {
                position: fixed;
                top: 0;
                left: 0;
                width: 100%;
                height: 100%;
                background-color: rgba(255,255,255,0.8);
                display: flex;
                justify-content: center;
                align-items: center;
                z-index: 9999;
            }
            
            .loading-spinner {
                width: 50px;
                height: 50px;
                border: 5px solid rgba(0,150,173,0.3);
                border-radius: 50%;
                border-top-color: #0096ad;
                animation: spin 1s ease-in-out infinite;
            }
            
            @keyframes spin {
                to { transform: rotate(360deg); }
            }
            
            /* Elements highlight for voice assistant */
            .highlight {
                outline: 3px solid #0096ad !important;
                box-shadow: 0 0 15px rgba(0,150,173,0.5) !important;
                transition: all 0.3s ease !important;
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
        
        <!-- Call Modal -->
        <div class="modal" id="callModal">
            <div class="modal-content">
                <div class="modal-header">
                    <h2 class="modal-title">Video Call</h2>
                    <button class="close-modal" id="closeCallModal">&times;</button>
                </div>
                <div class="call-container">
                    <div class="call-contact">
                        <img src="https://via.placeholder.com/150x150?text=Contact" alt="Contact image" class="contact-avatar" id="contactAvatar">
                        <div class="contact-name" id="contactName">Loading...</div>
                        <div class="call-status" id="callStatus">Connecting...</div>
                    </div>
                    <div class="call-controls">
                        <button class="call-control-btn mute-call" id="muteCallBtn">
                            <i class="fas fa-microphone-slash fa-lg"></i>
                        </button>
                        <button class="call-control-btn end-call" id="endCallBtn">
                            <i class="fas fa-phone-slash fa-lg"></i>
                        </button>
                        <button class="call-control-btn video-toggle" id="videoToggleBtn">
                            <i class="fas fa-video fa-lg"></i>
                        </button>
                    </div>
                    <div class="call-timer" id="callTimer">00:00</div>
                </div>
            </div>
        </div>
        
        <!-- Accessibility Controls -->
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
        <!-- Emergency and Voice Assistant Buttons -->
<button class="emergency-button" aria-label="Emergency assistance">
    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="white" stroke="white" stroke-width="1">
        <path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm0 11c-.55 0-1-.45-1-1V8c0-.55.45-1 1-1s1 .45 1 1v4c0 .55-.45 1-1 1zm1 4h-2v-2h2v2z"/>
    </svg>
</button>

<button class="voice-assistant" id="voiceAssistantBtn" aria-label="Voice assistant">
    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="white" stroke="white" stroke-width="1">
        <path d="M12 15c1.66 0 2.99-1.34 2.99-3L15 6c0-1.66-1.34-3-3-3S9 4.34 9 6v6c0 1.66 1.34 3 3 3zm-1.2-9.1c0-.66.54-1.2 1.2-1.2s1.2.54 1.2 1.2l-.01 6.2c0 .66-.53 1.2-1.19 1.2s-1.2-.54-1.2-1.2V5.9zm6.5 6.1c0 3-2.54 5.1-5.3 5.1S6.7 15 6.7 12H5c0 3.41 2.72 6.23 6 6.72V22h2v-3.28c3.28-.48 6-3.3 6-6.72h-1.7z"/>
    </svg>
</button>
        
        <main>
            <div class="alert-banner">
                <div class="alert-content">
                    <svg class="alert-icon" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="white">
                        <path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"></path>
                        <circle cx="9" cy="7" r="4"></circle>
                        <path d="M23 21v-2a4 4 0 0 0-3-3.87"></path>
                        <path d="M16 3.13a4 4 0 0 1 0 7.75"></path>
                    </svg>
                    <span class="alert-message">You have 2 new messages and an upcoming call in 30 minutes</span>
                </div>
                <button class="alert-action" id="viewAlertDetails">View Details</button>
            </div>
            
            <!-- Family & Friends Section -->
            <div class="social-dashboard">
                <h1 style="margin-bottom: 20px; color: #2d3748; font-size: 2rem; text-align: center;">Your Social Connections</h1>
                
                <h2 class="section-heading">
                    <i class="fas fa-heart"></i>
                    Family & Close Friends
                </h2>
                
                <div class="social-grid">
                    <!-- Family Member Card 1 -->
                    <div class="connection-card" id="connection-sarah">
                        <div class="card-header">
                            <img src="https://via.placeholder.com/400x300?text=Family+Photo" alt="Sarah's family photo">
                            <span class="card-badge">Family</span>
                        </div>
                        <div class="card-content">
                            <div class="connection-info">
                                <img src="https://via.placeholder.com/100x100?text=Sarah" alt="Sarah Ahmed" class="connection-avatar">
                                <div>
                                    <div class="connection-name">Sarah Ahmed</div>
                                    <div class="connection-relation">Daughter</div>
                                </div>
                            </div>
                            <p class="card-description">Sarah shared 5 new photos from her garden yesterday. She has been asking about your health.</p>
                            <div class="card-actions">
                                <button class="action-button call-button" onclick="initiateCall('phone', 'Sarah Ahmed')">
                                    <i class="fas fa-phone-alt"></i>
                                    Call
                                </button>
                                <button class="action-button video-button" onclick="initiateCall('video', 'Sarah Ahmed')">
                                    <i class="fas fa-video"></i>
                                    Video
                                </button>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Family Member Card 2 -->
                    <div class="connection-card" id="connection-karim">
                        <div class="card-header">
                            <img src="https://via.placeholder.com/400x300?text=Family+Photo" alt="Karim's family photo">
                            <span class="card-badge">Family</span>
                        </div>
                        <div class="card-content">
                            <div class="connection-info">
                                <img src="https://via.placeholder.com/100x100?text=Karim" alt="Karim Ahmed" class="connection-avatar">
                                <div>
                                    <div class="connection-name">Karim Ahmed</div>
                                    <div class="connection-relation">Son</div>
                                </div>
                            </div>
                            <p class="card-description">Karim called yesterday and mentioned he will visit with the grandchildren this weekend.</p>
                            <div class="card-actions">
                                <button class="action-button call-button" onclick="initiateCall('phone', 'Karim Ahmed')">
                                    <i class="fas fa-phone-alt"></i>
                                    Call
                                </button>
                                <button class="action-button video-button" onclick="initiateCall('video', 'Karim Ahmed')">
                                    <i class="fas fa-video"></i>
                                    Video
                                </button>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Friend Card 1 -->
                    <div class="connection-card" id="connection-abdul">
                        <div class="card-header">
                            <img src="https://via.placeholder.com/400x300?text=Friend+Photo" alt="Abdul's photo">
                            <span class="card-badge">Friend</span>
                        </div>
                        <div class="card-content">
                            <div class="connection-info">
                                <img src="https://via.placeholder.com/100x100?text=Abdul" alt="Abdul Rahman" class="connection-avatar">
                                <div>
                                    <div class="connection-name">Abdul Rahman</div>
                                    <div class="connection-relation">Old Friend</div>
                                </div>
                            </div>
                            <p class="card-description">Abdul shared an article about gardening tips for seniors. You both have a scheduled chess game tomorrow.</p>
                            <div class="card-actions">
                                <button class="action-button message-button" onclick="sendMessage('Abdul Rahman')">
                                    <i class="fas fa-comment-alt"></i>
                                    Message
                                </button>
                                <button class="action-button schedule-button" onclick="scheduleCall('Abdul Rahman')">
                                    <i class="fas fa-calendar-alt"></i>
                                    Schedule
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
                
                <a href="#" class="full-button" id="viewAllConnections">
                    <i class="fas fa-users"></i>
                    See All Connections
                </a>
            </div>
            
            <!-- Scheduled Calls Section -->
            <div class="scheduled-calls" id="scheduled-calls-section">
                <h2 class="section-heading">
                    <i class="fas fa-phone-alt"></i>
                    Upcoming Calls & Meetings
                </h2>
                
                <ul class="call-list">
                    <!-- Scheduled Call 1 -->
                    <li class="call-item">
                        <div class="call-time">
                            <div class="call-hour">3:30</div>
                            <div class="call-date">Today</div>
                        </div>
                        <div class="call-details">
                            <div class="call-with">Video Call with Sarah Ahmed</div>
                            <div class="call-type">
                                <i class="fas fa-video"></i>
                                Weekly check-in call
                            </div>
                            <div class="call-reminder">
                                <i class="fas fa-bell"></i>
                                Reminder set for 15 minutes before
                            </div>
                        </div>
                        <div class="call-actions">
                            <button class="call-action-button join-call" title="Join Call" onclick="initiateCall('video', 'Sarah Ahmed')">
                                <i class="fas fa-phone-alt"></i>
                            </button>
                            <button class="call-action-button reschedule-call" title="Reschedule" onclick="rescheduleCall('Sarah Ahmed', 'today 3:30 PM')">
                                <i class="fas fa-calendar-alt"></i>
                            </button>
                            <button class="call-action-button cancel-call" title="Cancel" onclick="cancelCall('Sarah Ahmed', 'today 3:30 PM')">
                                <i class="fas fa-times"></i>
                            </button>
                        </div>
                    </li>
                    
                    <!-- Scheduled Call 2 -->
                    <li class="call-item">
                        <div class="call-time">
                            <div class="call-hour">11:00</div>
                            <div class="call-date">Tomorrow</div>
                        </div>
                        <div class="call-details">
                            <div class="call-with">Chess Game with Abdul Rahman</div>
                            <div class="call-type">
                                <i class="fas fa-chess"></i>
                                Online activity session
                            </div>
                            <div class="call-reminder">
                                <i class="fas fa-bell"></i>
                                Reminder set for 30 minutes before
                            </div>
                        </div>
                        <div class="call-actions">
                            <button class="call-action-button reschedule-call" title="Reschedule" onclick="rescheduleCall('Abdul Rahman', 'tomorrow 11:00 AM')">
                                <i class="fas fa-calendar-alt"></i>
                            </button>
                            <button class="call-action-button cancel-call" title="Cancel" onclick="cancelCall('Abdul Rahman', 'tomorrow 11:00 AM')">
                                <i class="fas fa-times"></i>
                            </button>
                        </div>
                    </li>
                    
                    <!-- Scheduled Call 3 -->
                    <li class="call-item">
                        <div class="call-time">
                            <div class="call-hour">4:00</div>
                            <div class="call-date">Apr 10</div>
                        </div>
                        <div class="call-details">
                            <div class="call-with">Family Group Call</div>
                            <div class="call-type">
                                <i class="fas fa-users"></i>
                                Monthly family meeting
                            </div>
                            <div class="call-reminder">
                                <i class="fas fa-bell"></i>
                                Reminder set for 1 hour before
                            </div>
                        </div>
                        <div class="call-actions">
                            <button class="call-action-button reschedule-call" title="Reschedule" onclick="rescheduleCall('Family Group', 'April 10, 4:00 PM')">
                                <i class="fas fa-calendar-alt"></i>
                            </button>
                            <button class="call-action-button cancel-call" title="Cancel" onclick="cancelCall('Family Group', 'April 10, 4:00 PM')">
                                <i class="fas fa-times"></i>
                            </button>
                        </div>
                    </li>
                </ul>
                
                <button class="full-button" id="scheduleNewCallBtn">
                    <i class="fas fa-plus"></i>
                    Schedule New Call or Meeting
                </button>
            </div>
            
            <!-- Community Section -->
            <div class="community-section" id="communities-section">
                <h2 class="section-heading">
                    <i class="fas fa-users"></i>
                    Your Communities
                </h2>
                
                <div class="community-grid">
                    <!-- Community Card 1 -->
                    <div class="community-card">
                        <div class="community-card-header">
                            <img src="https://via.placeholder.com/600x400?text=Gardening+Group" alt="Gardening Enthusiasts">
                            <div class="community-stats">
                                <div>
                                    <i class="fas fa-user"></i>
                                    45
                                </div>
                                <div>
                                    <i class="fas fa-comments"></i>
                                    Active
                                </div>
                            </div>
                        </div>
                        <div class="community-card-content">
                            <h3 class="community-title">Gardening Enthusiasts</h3>
                            <p class="community-description">Connect with other seniors who share your passion for gardening. Share tips, photos, and experiences.</p>
                            
                            <div class="next-event">
                                <div class="next-event-label">
                                    <i class="fas fa-calendar-alt"></i>
                                    Next Event
                                </div>
                                <div class="next-event-title">Spring Planting Tips Webinar</div>
                                <div class="next-event-time">
                                    <i class="fas fa-clock"></i>
                                    April 12, 4:00 PM
                                </div>
                            </div>
                            
                            <a href="#" class="full-button" id="viewGardeningCommunity">
                                <i class="fas fa-arrow-right"></i>
                                View Community
                            </a>
                        </div>
                    </div>
                    
                    <!-- Community Card 2 -->
                    <div class="community-card">
                        <div class="community-card-header">
                            <img src="https://via.placeholder.com/600x400?text=Book+Club" alt="Senior Book Club">
                            <div class="community-stats">
                                <div>
                                    <i class="fas fa-user"></i>
                                    32
                                </div>
                                <div>
                                    <i class="fas fa-comments"></i>
                                    Active
                                </div>
                            </div>
                        </div>
                        <div class="community-card-content">
                            <h3 class="community-title">Senior Book Club</h3>
                            <p class="community-description">Join discussions about interesting books with other avid readers. Monthly virtual meetings and ongoing discussions.</p>
                            
                            <div class="next-event">
                                <div class="next-event-label">
                                    <i class="fas fa-calendar-alt"></i>
                                    Next Event
                                </div>
                                <div class="next-event-title">Book Discussion: "The Alchemist"</div>
                                <div class="next-event-time">
                                    <i class="fas fa-clock"></i>
                                    April 15, 3:00 PM
                                </div>
                            </div>
                            
                            <a href="#" class="full-button" id="viewBookClubCommunity">
                                <i class="fas fa-arrow-right"></i>
                                View Community
                            </a>
                        </div>
                    </div>
                </div>
                
                <button class="full-button" id="discoverCommunitiesBtn">
                    <i class="fas fa-globe"></i>
                    Discover More Communities
                </button>
            </div>
            
            <!-- Stories Section -->
            <div class="stories-section" id="stories-section">
                <h2 class="section-heading">
                    <i class="fas fa-book-open"></i>
                    Life Stories & Memories
                </h2>
                
                <div class="stories-slider">
                    <!-- Story Card 1 -->
                    <div class="story-card">
                        <div class="story-card-header">
                            <img src="https://via.placeholder.com/400x300?text=Childhood+Memory" alt="Childhood Memory">
                        </div>
                        <div class="story-card-content">
                            <h3 class="story-title">My First Day at School</h3>
                            <div class="story-author">
                                <img src="https://via.placeholder.com/100x100?text=You" alt="User" class="author-avatar">
                                <div class="author-info">
                                    <div class="author-name">Your Story</div>
                                    <div class="story-date">Shared 2 weeks ago</div>
                                </div>
                            </div>
                            <p class="story-excerpt">I remember the day when my father took me to school for the first time. I was wearing a new uniform and carried a small wooden slate...</p>
                            <div class="story-footer">
                                <div class="story-stats">
                                    <div class="story-stat">
                                        <i class="fas fa-heart"></i>
                                        12
                                    </div>
                                    <div class="story-stat">
                                        <i class="fas fa-comment"></i>
                                        5
                                    </div>
                                </div>
                                <a href="#" class="story-link" onclick="readStory(123); return false;">
                                    Read More
                                    <i class="fas fa-arrow-right"></i>
                                </a>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Story Card 2 -->
                    <div class="story-card">
                        <div class="story-card-header">
                            <img src="https://via.placeholder.com/400x300?text=Wedding+Day" alt="Wedding Day">
                        </div>
                        <div class="story-card-content">
                            <h3 class="story-title">Our Wedding Day</h3>
                            <div class="story-author">
                                <img src="https://via.placeholder.com/100x100?text=You" alt="User" class="author-avatar">
                                <div class="author-info">
                                    <div class="author-name">Your Story</div>
                                    <div class="story-date">Shared 1 month ago</div>
                                </div>
                            </div>
                            <p class="story-excerpt">It was a beautiful spring morning when we got married. The air was filled with the scent of jasmine and the sound of morning birds...</p>
                            <div class="story-footer">
                                <div class="story-stats">
                                    <div class="story-stat">
                                        <i class="fas fa-heart"></i>
                                        24
                                    </div>
                                    <div class="story-stat">
                                        <i class="fas fa-comment"></i>
                                        8
                                    </div>
                                </div>
                                <a href="#" class="story-link" onclick="readStory(124); return false;">
                                    Read More
                                    <i class="fas fa-arrow-right"></i>
                                </a>
                            </div>
                        </div>
                    </div>
                    
                    </a>
                        </div>
                    </div>
                </div> <!-- End of stories-slider -->
            </div> <!-- End of stories-section -->
        </main>
        
        <?php include '../includes/footer.php'; ?>
        
        <!-- Optional: Additional JavaScript functions for interactions -->
        <script>
    // Voice Assistant functionality
    let recognition;
    let recognitionActive = false;
    const voiceAssistantBtn = document.getElementById('voiceAssistantBtn');
    const voiceFeedback = document.getElementById('voiceFeedback');
    
    // Initialize speech recognition
    function initSpeechRecognition() {
        if ('webkitSpeechRecognition' in window) {
            recognition = new webkitSpeechRecognition();
            recognition.continuous = false;
            recognition.interimResults = false;
            recognition.lang = 'en-US';
            
            recognition.onstart = function() {
                recognitionActive = true;
                voiceAssistantBtn.classList.add('listening');
                voiceFeedback.style.display = 'block';
                document.getElementById('voiceResult').textContent = 'Listening...';
            };
            
            recognition.onresult = function(event) {
                const command = event.results[0][0].transcript.toLowerCase();
                document.getElementById('voiceResult').textContent = '"' + command + '"';
                processVoiceCommand(command);
            };
            
            recognition.onerror = function(event) {
                console.error('Speech recognition error:', event.error);
                document.getElementById('voiceResult').textContent = 'Error: ' + event.error;
                setTimeout(() => {
                    stopRecognition();
                }, 2000);
            };
            
            recognition.onend = function() {
                setTimeout(() => {
                    stopRecognition();
                }, 1000);
            };
            
            return true;
        } else {
            alert('Speech recognition is not supported in your browser.');
            return false;
        }
    }
    
    // Process voice commands
    function processVoiceCommand(command) {
        console.log("Processing command:", command);
        
        // Call related commands
        if (command.includes('call')) {
            if (command.includes('sarah') || command.includes('daughter')) {
                highlightElement(document.getElementById('connection-sarah'));
                speakResponse("Calling Sarah Ahmed");
                setTimeout(() => initiateCall('video', 'Sarah Ahmed'), 1500);
                return;
            }
            
            if (command.includes('karim') || command.includes('son')) {
                highlightElement(document.getElementById('connection-karim'));
                speakResponse("Calling Karim Ahmed");
                setTimeout(() => initiateCall('video', 'Karim Ahmed'), 1500);
                return;
            }
            
            if (command.includes('abdul') || command.includes('friend')) {
                highlightElement(document.getElementById('connection-abdul'));
                speakResponse("Calling Abdul Rahman");
                setTimeout(() => initiateCall('video', 'Abdul Rahman'), 1500);
                return;
            }
        }
        
        // Navigation commands
        if (command.includes('schedule') || command.includes('upcoming')) {
            speakResponse("Showing your scheduled calls");
            safeScrollTo(document.getElementById('scheduled-calls-section'));
            return;
        }
        
        if (command.includes('community') || command.includes('communities')) {
            speakResponse("Showing your communities");
            safeScrollTo(document.getElementById('communities-section'));
            return;
        }
        
        if (command.includes('stories') || command.includes('memories')) {
            speakResponse("Showing your life stories");
            safeScrollTo(document.getElementById('stories-section'));
            return;
        }
        
        // Reading commands
        if (command.includes('read') || command.includes('speak')) {
            speakResponse("Starting to read the page");
            setTimeout(() => readPageContent(), 1000);
            return;
        }
        
        if (command.includes('stop')) {
            if (window.speechSynthesis.speaking) {
                window.speechSynthesis.cancel();
                speakResponse("Stopped reading");
                const stopBtn = document.getElementById('stopReadingBtn');
                if (stopBtn) stopBtn.remove();
            }
            return;
        }
        
        // Fallback
        speakResponse("I didn't understand. Please try again.");
    }
    
    // Helper functions for voice assistant
    function stopRecognition() {
        if (recognition) {
            recognition.stop();
        }
        recognitionActive = false;
        voiceAssistantBtn.classList.remove('listening');
        voiceFeedback.style.display = 'none';
    }
    
    function safeScrollTo(element) {
        if (element) {
            element.scrollIntoView({ behavior: 'smooth', block: 'start' });
            highlightElement(element);
        }
    }
    
    function highlightElement(element) {
        if (!element) return;
        
        // Remove any existing highlights
        document.querySelectorAll('.highlight').forEach(el => {
            el.classList.remove('highlight');
        });
        
        element.classList.add('highlight');
        setTimeout(() => {
            element.classList.remove('highlight');
        }, 3000);
    }
    
    function speakResponse(text) {
        if ('speechSynthesis' in window) {
            const utterance = new SpeechSynthesisUtterance(text);
            utterance.volume = 1;
            utterance.rate = 0.9;
            utterance.pitch = 1;
            window.speechSynthesis.speak(utterance);
        }
    }

    // Page reading functionality
    function readPageContent() {
        const content = document.body.innerText;
        const utterance = new SpeechSynthesisUtterance(content);
        window.speechSynthesis.speak(utterance);
        
        // Create and display a stop reading button
        createStopReadingButton();
        
        // Show feedback to user
        showToast("Page reading started");
    }

    // Function to create a stop reading button when content is being read
    function createStopReadingButton() {
        // Remove any existing stop button first
        const existingButton = document.getElementById('stopReadingBtn');
        if (existingButton) existingButton.remove();
        
        // Create the new button
        const stopButton = document.createElement('button');
        stopButton.id = 'stopReadingBtn';
        stopButton.innerHTML = '<i class="fas fa-stop"></i> Stop Reading';
        stopButton.classList.add('stop-reading-button');
        
        // Style the button
        stopButton.style.position = 'fixed';
        stopButton.style.bottom = '100px';
        stopButton.style.right = '30px';
        stopButton.style.backgroundColor = '#e53e3e';
        stopButton.style.color = 'white';
        stopButton.style.padding = '10px 20px';
        stopButton.style.borderRadius = '8px';
        stopButton.style.border = 'none';
        stopButton.style.boxShadow = '0 4px 8px rgba(0,0,0,0.2)';
        stopButton.style.cursor = 'pointer';
        stopButton.style.zIndex = '999';
        stopButton.style.display = 'flex';
        stopButton.style.alignItems = 'center';
        stopButton.style.gap = '8px';
        stopButton.style.fontWeight = 'bold';
        
        // Add functionality
        stopButton.addEventListener('click', function() {
            window.speechSynthesis.cancel();
            stopButton.remove();
            showToast("Page reading stopped");
        });
        
        // Add to the page
        document.body.appendChild(stopButton);
    }

    // Add a toast notification function
    function showToast(message) {
        // Remove any existing toast
        const existingToast = document.getElementById('toast');
        if (existingToast) existingToast.remove();
        
        // Create toast element
        const toast = document.createElement('div');
        toast.id = 'toast';
        toast.textContent = message;
        
        // Style the toast
        toast.style.position = 'fixed';
        toast.style.top = '20px';
        toast.style.left = '50%';
        toast.style.transform = 'translateX(-50%)';
        toast.style.backgroundColor = 'rgba(0, 0, 0, 0.8)';
        toast.style.color = 'white';
        toast.style.padding = '10px 20px';
        toast.style.borderRadius = '5px';
        toast.style.zIndex = '9999';
        
        // Add to page
        document.body.appendChild(toast);
        
        // Remove after 3 seconds
        setTimeout(() => {
            toast.remove();
        }, 3000);
    }
    
    // Function to load and display full story details dynamically
    function readStory(storyId) {
        // Replace the alert with your story-loading logic
        alert('Loading full story for id: ' + storyId);
    }
    
    // Functions to handle call, message and other actions
    function initiateCall(type, contactName) {
        // Show the call modal with correct contact info
        document.getElementById('contactName').textContent = contactName;
        document.getElementById('callStatus').textContent = 'Connecting...';
        document.getElementById('callModal').classList.add('active');
        
        // Start call timer
        startCallTimer();
        
        setTimeout(() => {
            document.getElementById('callStatus').textContent = 'Connected';
        }, 2000);
    }
    
    function sendMessage(contactName) {
        // Replace this logic with your messaging functionality
        alert('Opening message window for ' + contactName);
    }
    
    function scheduleCall(contactName) {
        // Replace this logic with your call scheduling functionality
        alert('Scheduling a new call with ' + contactName);
    }
    
    function rescheduleCall(contactName, time) {
        // Replace this logic with your rescheduling functionality
        alert('Rescheduling call with ' + contactName + ' at ' + time);
    }
    
    function cancelCall(contactName, time) {
        // Replace this with your cancellation logic
        alert('Cancelling call with ' + contactName + ' at ' + time);
    }
    
    // Call timer functionality
    let callTimerInterval;
    let callSeconds = 0;
    
    function startCallTimer() {
        callSeconds = 0;
        updateCallTimerDisplay();
        clearInterval(callTimerInterval);
        callTimerInterval = setInterval(updateCallTimer, 1000);
    }
    
    function updateCallTimer() {
        callSeconds++;
        updateCallTimerDisplay();
    }
    
    function updateCallTimerDisplay() {
        const minutes = Math.floor(callSeconds / 60);
        const seconds = callSeconds % 60;
        document.getElementById('callTimer').textContent = 
            `${minutes.toString().padStart(2, '0')}:${seconds.toString().padStart(2, '0')}`;
    }
    
    function endCall() {
        clearInterval(callTimerInterval);
        document.getElementById('callModal').classList.remove('active');
    }
    
    // Accessibility functions
    function increaseFontSize() {
        document.body.style.fontSize = 'larger';
        showToast("Text size increased");
    }
    
    function decreaseFontSize() {
        document.body.style.fontSize = 'smaller';
        showToast("Text size decreased");
    }
    
    function toggleHighContrast() {
        document.body.classList.toggle('high-contrast');
        const isHighContrast = document.body.classList.contains('high-contrast');
        showToast(isHighContrast ? "High contrast mode enabled" : "High contrast mode disabled");
    }
    
    // Initialize event listeners when DOM is loaded
    document.addEventListener('DOMContentLoaded', function() {
        // Voice assistant button
        voiceAssistantBtn.addEventListener('click', function() {
            if (!recognitionActive) {
                if (!recognition) {
                    if (!initSpeechRecognition()) return;
                }
                recognition.start();
            } else {
                stopRecognition();
            }
        });
        
        // Emergency button functionality
        document.querySelector('.emergency-button').addEventListener('click', function() {
            if(confirm('Do you need emergency assistance? Click OK to call emergency services.')) {
                alert('Connecting to emergency services...');
                // In a real app, this would trigger an emergency call
            }
        });
        
        // Close call modal button
        document.getElementById('closeCallModal').addEventListener('click', endCall);
        
        // End call button
        document.getElementById('endCallBtn').addEventListener('click', endCall);
        
        // Mute call button toggle
        document.getElementById('muteCallBtn').addEventListener('click', function() {
            this.classList.toggle('active');
            const isMuted = this.classList.contains('active');
            this.innerHTML = isMuted ? 
                '<i class="fas fa-microphone fa-lg"></i>' : 
                '<i class="fas fa-microphone-slash fa-lg"></i>';
            showToast(isMuted ? "Microphone unmuted" : "Microphone muted");
        });
        
        // Video toggle button
        document.getElementById('videoToggleBtn').addEventListener('click', function() {
            this.classList.toggle('active');
            const isVideoOff = this.classList.contains('active');
            this.innerHTML = isVideoOff ? 
                '<i class="fas fa-video-slash fa-lg"></i>' : 
                '<i class="fas fa-video fa-lg"></i>';
            showToast(isVideoOff ? "Video turned off" : "Video turned on");
        });
    });
</script>
    </body>
</html>