
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
        <title>Shohochor - Your Personal Dashboard</title>
        <meta name="description" content="Access your personalized elderly care dashboard with AI-powered health monitoring, social connections, and more.">
        <link href="./dashboard.php" rel="canonical">
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
        <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css" integrity="sha384-k6RqeWeci5ZR/Lv4MR0sA0FfDOM8d7xj1z5l5c5e5e5e5e5e5e5e5e5e5e5e5" crossorigin="anonymous">
        <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/fontawesome.min.css" integrity="sha384-k6RqeWeci5ZR/Lv4MR0sA0FfDOM8d7xj1z5l5c5e5e5e5e5e5e5e5e5e5e5e5" crossorigin="anonymous">
        <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/brands.min.css" integrity="sha384-k6RqeWeci5ZR/Lv4MR0sA0FfDOM8d7xj1z5l5c5e5e5e5e5e5e5e5e5e5e5e5" crossorigin="anonymous">
        <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/solid.min.css" integrity="sha384-k6RqeWeci5ZR/Lv4MR0sA0FfDOM8d7xj1z5l5c5e5e5e5e5e5e5e5e5e5e5e5" crossorigin="anonymous">
        <link rel="stylesheet" href="./includes/style.css">
        <link rel="stylesheet" href="./includes/dashboard.css">
    </head>
    <body>
          
       <?php include 'includes/nav.php'; ?>
       <?php include 'includes/mobile_nav.php'; ?>
        <main>
            <div class="alert-banner">
                <div class="alert-content">
                    <svg class="alert-icon" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="white">
                        <path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/>
                        <path d="M12 8v4"/>
                        <path d="M12 16h.01"/>
                    </svg>
                    <span class="alert-message">No Falls Detected - Status: Normal</span>
                </div>
                <button class="alert-action">View Details</button>
            </div>
        
            <div class="dashboard-welcome">
                <div class="welcome-text">
                    <?php
                    // Get current hour to determine greeting
                    $hour = date('H');
                    $greeting = '';
                    
                    if ($hour >= 5 && $hour < 12) {
                        $greeting = 'Good morning';
                    } else if ($hour >= 12 && $hour < 18) {
                        $greeting = 'Good afternoon';
                    } else if ($hour >= 18 && $hour < 23) {
                        $greeting = 'Good evening';
                    } else {
                        $greeting = 'Good night';
                    }
                    
                    // Format today's date nicely
                    $today = date('l, F j, Y');
                    ?>
                    <h1><?php echo $greeting; ?>, John!</h1>
                    <p>Today is <?php echo $today; ?>. Here's your personalized dashboard with everything you need for the day.</p>
                    <div class="weather-widget">
                        <div class="weather-content">
                            <?php
                            // This would typically be from an API call to OpenWeatherMap or similar service
                            // For demonstration purposes, adding placeholder data
                            $temperature = 32; // Celsius
                            $weather_condition = "Clear"; // Example: Clear, Rain, Cloudy, etc.
                            $location = "Dhaka"; // This would come from geolocation
                            $tempF = round(($temperature * 9/5) + 32); // Convert to Fahrenheit
                            
                            // Weather-based health tip
                            $health_tip = "";
                            if ($temperature > 30) {
                                $health_tip = "It's hot today! Drink at least 2 liters of water to stay hydrated.";
                            } else if ($temperature > 25) {
                                $health_tip = "Pleasant weather today. Stay active and enjoy some outdoor time.";
                            } else if ($temperature > 15) {
                                $health_tip = "Cool weather today. Remember to dress in layers.";
                            } else {
                                $health_tip = "Cold today! Keep warm and drink hot beverages.";
                            }
                            
                            // Select appropriate weather icon based on condition
                            $weather_icon = "sunny";
                            if (strpos(strtolower($weather_condition), "rain") !== false) {
                                $weather_icon = "rainy";
                            } else if (strpos(strtolower($weather_condition), "cloud") !== false) {
                                $weather_icon = "cloudy";
                            }
                            ?>
                            
                            <div class="weather-location">
                                <i class="fas fa-map-marker-alt"></i>
                                <span><?php echo $location; ?></span>
                            </div>
                            
                            <div class="weather-display">
                                <?php if ($weather_icon == "sunny"): ?>
                                    <svg xmlns="http://www.w3.org/2000/svg" width="65" height="65" viewBox="0 0 24 24" fill="#FFCC00" stroke="#FF9900" stroke-width="1" stroke-linecap="round" stroke-linejoin="round">
                                        <circle cx="12" cy="12" r="5" fill="#FFCC00"/>
                                        <line x1="12" y1="1" x2="12" y2="3"/>
                                        <line x1="12" y1="21" x2="12" y2="23"/>
                                        <line x1="4.22" y1="4.22" x2="5.64" y2="5.64"/>
                                        <line x1="18.36" y1="18.36" x2="19.78" y2="19.78"/>
                                        <line x1="1" y1="12" x2="3" y2="12"/>
                                        <line x1="21" y1="12" x2="23" y2="12"/>
                                        <line x1="4.22" y1="19.78" x2="5.64" y2="18.36"/>
                                        <line x1="18.36" y1="5.64" x2="19.78" y2="4.22"/>
                                    </svg>
                                <?php elseif ($weather_icon == "rainy"): ?>
                                    <svg xmlns="http://www.w3.org/2000/svg" width="65" height="65" viewBox="0 0 24 24" fill="#AEDCF7" stroke="#ffffff" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                        <line x1="16" y1="13" x2="16" y2="21"></line>
                                        <line x1="8" y1="13" x2="8" y2="21"></line>
                                        <line x1="12" y1="15" x2="12" y2="23"></line>
                                        <path d="M20 16.58A5 5 0 0 0 18 7h-1.26A8 8 0 1 0 4 15.25"></path>
                                    </svg>
                                <?php else: ?>
                                    <svg xmlns="http://www.w3.org/2000/svg" width="65" height="65" viewBox="0 0 24 24" fill="#E1E7F0" stroke="#ffffff" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                        <path d="M18 10h-1.26A8 8 0 1 0 9 20h9a5 5 0 0 0 0-10z"></path>
                                    </svg>
                                <?php endif; ?>
                                <div class="temp-display">
                                    <span class="temp"><?php echo $temperature; ?>°C</span>
                                    <span class="temp-f"><?php echo $tempF; ?>°F</span>
                                </div>
                            </div>
                            
                            <div class="weather-tip">
                                <i class="fas fa-info-circle"></i>
                                <span><?php echo $health_tip; ?></span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="dashboard-stats">
                <div class="stat-card">
                    <h3>Blood Pressure</h3>
                    <p>120/80</p>
                </div>
                <div class="stat-card">
                    <h3>Heart Rate</h3>
                    <p>72 BPM</p>
                </div>
                <div class="stat-card">
                    <h3>Medications Today</h3>
                    <p>2 of 4 Taken</p>
                </div>
                <div class="stat-card">
                    <h3>Steps Today</h3>
                    <p>3,450</p>
                </div>
            </div>
            

            <h2 style="margin-bottom: 20px; color: #2d3748; font-size: 1.8rem; text-align: center;">Your Daily Tools</h2>
            
            <div class="feature-grid">
                <div class="feature-card">
                    <div class="feature-card-header">
                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="white">
                            <path d="M20.84 4.61a5.5 5.5 0 0 0-7.78 0L12 5.67l-1.06-1.06a5.5 5.5 0 0 0-7.78 7.78l1.06 1.06L12 21.23l7.78-7.78 1.06-1.06a5.5 5.5 0 0 0 0-7.78z"/>
                        </svg>
                        <h2>Health Monitoring</h2>
                    </div>
                    <div class="feature-card-content">
                        <p>Track your vital signs and receive AI-powered health insights.</p>
                        <div class="feature-actions">
                            <a href="./health-monitoring/health-monitoring.php" class="primary-action">View Health</a>
                        </div>
                    </div>
                </div>
                
                <div class="feature-card">
                    <div class="feature-card-header" style="background-color: #27ae60;">
                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="white">
                            <path d="M19 3H5c-1.1 0-2 .9-2 2v14c0 1.1.9 2 2 2h14c1.1 0 2-.9 2-2V5c0-1.1-.9-2-2-2zm-7 14h-2v-2h2v2zm0-4h-2V7h2v6z"/>
                        </svg>
                        <h2>Medication Tracking</h2>
                    </div>
                    <div class="feature-card-content">
                        <p>Manage your medications with reminders and refill alerts.</p>
                        <div class="feature-actions">
                            <a href="./medication-tracking/medication-tracking.php" class="primary-action">Take Meds</a>
                        </div>
                    </div>
                </div>
                
                <div class="feature-card">
                    <div class="feature-card-header" style="background-color: #9b59b6;">
                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="white">
                            <path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"></path>
                            <circle cx="9" cy="7" r="4"></circle>
                            <path d="M23 21v-2a4 4 0 0 0-3-3.87"></path>
                            <path d="M16 3.13a4 4 0 0 1 0 7.75"></path>
                        </svg>
                        <h2>Social Connections</h2>
                    </div>
                    <div class="feature-card-content">
                        <p>Connect with friends, family, and community storytelling circles.</p>
                        <div class="feature-actions">
                            <a href="./social_connection/social_connection.php" class="primary-action">Connect Now</a>
                        </div>
                    </div>
                </div>
                
                <div class="feature-card">
                    <div class="feature-card-header" style="background-color: #e74c3c;">
                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="white">
                            <path d="M10.29 3.86L1.82 18a2 2 0 0 0 1.71 3h16.94a2 2 0 0 0 1.71-3L13.71 3.86a2 2 0 0 0-3.42 0z"></path>
                            <line x1="12" y1="9" x2="12" y2="13"></line>
                            <line x1="12" y1="17" x2="12.01" y2="17"></line>
                        </svg>
                        <h2>Emergency Response</h2>
                    </div>
                    <div class="feature-card-content">
                        <p>One-touch alert system to contact caregivers or emergency services.</p>
                        <div class="feature-actions">
                            <a href="./emergency-contacts.php" class="primary-action">Contacts</a>
                        </div>
                    </div>
                </div>
                
                <div class="feature-card">
                    <div class="feature-card-header" style="background-color: #3498db;">
                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="white">
                            <path d="M12 2a10 10 0 1 0 10 10A10 10 0 0 0 12 2zm0 18a8 8 0 1 1 8-8 8 8 0 0 1-8 8z"></path>
                            <path d="M15.5 11a1.5 1.5 0 1 0-1.5-1.5 1.5 1.5 0 0 0 1.5 1.5zm-7 0A1.5 1.5 0 1 0 7 9.5 1.5 1.5 0 0 0 8.5 11z"></path>
                            <path d="M12 14a4 4 0 0 0-4 3h8a4 4 0 0 0-4-3z"></path>
                        </svg>
                        <h2>Mood Tracking</h2>
                    </div>
                    <div class="feature-card-content">
                        <p>Record your daily mood and feelings to help caregivers provide better emotional support.</p>
                        <div class="feature-actions">
                            <a href="./mood-tracking.php" class="primary-action">Track Mood</a>
                        </div>
                    </div>
                </div>

                <!-- <div class="feature-card">
                    <div class="feature-card-header" style="background-color: #f39c12;">
                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="white">
                            <path d="M20 4H4c-1.1 0-1.99.9-1.99 2L2 18c0 1.1.9 2 2 2h16c1.1 0 2-.9 2-2V6c0-1.1-.9-2-2-2zm-1 14H5c-.55 0-1-.45-1-1V8l8 5 8-5v9c0 .55-.45 1-1 1z"></path>
                        </svg>
                        <h2>Daily News</h2>
                    </div>
                    <div class="feature-card-content">
                        <p>Stay informed with easy-to-read daily newspapers and news articles.</p>
                        <div class="feature-actions">
                            <a href="./daily-news.php" class="primary-action">Read News</a>
                        </div>
                    </div> -->
                </div>
            </div>
                        <!-- NEW SECTION: Daily News Headlines -->
            <div class="daily-news-section">
                <h2 class="section-heading">Today's Top Headlines</h2>
                
                <div class="news-grid">
                    <!-- News Item 1 -->
                    <div class="news-card">
                        <img src="https://via.placeholder.com/400x200?text=Health+News" alt="Health News" class="news-image">
                        <div class="news-content">
                            <span class="news-category">Health</span>
                            <h3 class="news-title">New Research Shows Benefits of Daily Walking for Seniors</h3>
                            <p class="news-excerpt">A recent study indicates that just 30 minutes of daily walking can significantly improve heart health and cognitive function in adults over 65.</p>
                            <div class="news-metadata">
                                <span class="news-date">
                                    <i class="far fa-calendar"></i>
                                    <?php echo date('M j, Y'); ?>
                                </span>
                            </div>
                            <div class="news-actions">
                                <a href="./daily-news.php?article=1" class="news-link">
                                    Read Full Article
                                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                        <path d="M5 12h14"></path>
                                        <path d="M12 5l7 7-7 7"></path>
                                    </svg>
                                </a>
                            </div>
                        </div>
                    </div>
                    
                    <!-- News Item 2 -->
                    <div class="news-card">
                        <img src="https://via.placeholder.com/400x200?text=Local+News" alt="Local News" class="news-image">
                        <div class="news-content">
                            <span class="news-category">Local</span>
                            <h3 class="news-title">New Senior Community Center Opening Next Month</h3>
                            <p class="news-excerpt">The city's newest senior community center will open its doors next month, offering free classes, social gatherings, and health services to local elderly residents.</p>
                            <div class="news-metadata">
                                <span class="news-date">
                                    <i class="far fa-calendar"></i>
                                    <?php echo date('M j, Y'); ?>
                                </span>
                            </div>
                            <div class="news-actions">
                                <a href="./daily-news.php?article=2" class="news-link">
                                    Read Full Article
                                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                        <path d="M5 12h14"></path>
                                        <path d="M12 5l7 7-7 7"></path>
                                    </svg>
                                </a>
                            </div>
                        </div>
                    </div>
                    
                    <!-- News Item 3 -->
                    <div class="news-card">
                        <img src="https://via.placeholder.com/400x200?text=Technology+News" alt="Technology News" class="news-image">
                        <div class="news-content">
                            <span class="news-category">Technology</span>
                            <h3 class="news-title">New Easy-to-Use Smart Devices Designed for Seniors</h3>
                            <p class="news-excerpt">A tech company has launched a new line of smart home devices specifically designed with larger buttons, simple interfaces, and voice controls to help seniors stay connected.</p>
                            <div class="news-metadata">
                                <span class="news-date">
                                    <i class="far fa-calendar"></i>
                                    <?php echo date('M j, Y'); ?>
                                </span>
                            </div>
                            <div class="news-actions">
                                <a href="./daily-news.php?article=3" class="news-link">
                                    Read Full Article
                                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                        <path d="M5 12h14"></path>
                                        <path d="M12 5l7 7-7 7"></path>
                                    </svg>
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
                
                <div class="see-all-news">
                    <a href="./daily-news.php" class="see-all-link">
                        Read All News
                        <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <path d="M5 12h14"></path>
                            <path d="M12 5l7 7-7 7"></path>
                        </svg>
                    </a>
                </div>
            </div>
            
            <!-- Trust Section with Testimonials -->
            <div class="trust-section">
                <h2>What Our Users Say</h2>
                <div class="testimonials">
                    <div class="testimonial-card">
                        <div class="testimonial-header">
                            <img class="testimonial-image" src="./images/testimonials/user1.jpg" alt="User testimonial" 
                                 onerror="this.src='https://via.placeholder.com/60x60?text=User'">
                            <div>
                                <h4 class="testimonial-name">Mariam Ahmed</h4>
                                <p class="testimonial-role">User, 68 years</p>
                            </div>
                        </div>
                        <div class="testimonial-content">
                            <p>"This app has made my life so much easier. I never forget my medications now, and my family can check on me easily."</p>
                        </div>
                    </div>
                    
                    <div class="testimonial-card">
                        <div class="testimonial-header">
                            <img class="testimonial-image" src="./images/testimonials/user2.jpg" alt="User testimonial" 
                                 onerror="this.src='https://via.placeholder.com/60x60?text=User'">
                            <div>
                                <h4 class="testimonial-name">Kamal Rahman</h4>
                                <p class="testimonial-role">User, 72 years</p>
                            </div>
                        </div>
                        <div class="testimonial-content">
                            <p>"The emergency button gives me peace of mind. I live alone, but now I know help is always just one tap away."</p>
                        </div>
                    </div>
                    
                    <div class="testimonial-card">
                        <div class="testimonial-header">
                            <img class="testimonial-image" src="./images/testimonials/caregiver1.jpg" alt="Caregiver testimonial" 
                                 onerror="this.src='https://via.placeholder.com/60x60?text=Caregiver'">
                            <div>
                                <h4 class="testimonial-name">Dr. Zahra Khan</h4>
                                <p class="testimonial-role">Healthcare Provider</p>
                            </div>
                        </div>
                        <div class="testimonial-content">
                            <p>"As a doctor, I find this platform incredibly helpful for monitoring my elderly patients' health remotely."</p>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Emergency and Voice Assistant Buttons -->
            <button class="emergency-button" aria-label="Emergency assistance">
                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="white">
                    <path d="M21.64 3.64A1.36 1.36 0 0 0 20.28 2H3.72a1.36 1.36 0 0 0-1.36 1.64l1.72 9a1.37 1.37 0 0 0 1.36 1.08h14.56c.64 0 1.2-.44 1.36-1.08l1.72-9c.08-.44-.12-.88-.44-1z"></path>
                    <path d="M4 14.08c-1.92.96-2.8 2.16-3.36 3.68-.4 1.2.24 2.24 1.44 2.24h19.84c1.2 0 1.84-1.04 1.44-2.24-.56-1.52-1.44-2.72-3.36-3.68"></path>
                </svg>
            </button>
            
            <button class="voice-assistant" aria-label="Voice assistant">
                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="white">
                    <path d="M12 1a3 3 0 0 0-3 3v8a3 3 0 0 0 6 0V4a3 3 0 0 0-3-3z"></path>
                    <path d="M19 10v2a7 7 0 0 1-14 0v-2"></path>
                    <line x1="12" y1="19" x2="12" y2="23"></line>
                    <line x1="8" y1="23" x2="16" y2="23"></line>
                </svg>
            </button>
            
            <!-- Enhanced Accessibility Controls -->
            <div class="accessibility-controls">
                <button onclick="increaseFontSize()" title="Increase Text Size">
                    <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <text x="5" y="19" font-size="20">A+</text>
                    </svg>
                </button>
                <button onclick="decreaseFontSize()" title="Decrease Text Size">
                    <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <text x="5" y="19" font-size="20">A-</text>
                    </svg>
                </button>
                <button onclick="toggleHighContrast()" title="Toggle High Contrast">
                    <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <circle cx="12" cy="12" r="10"></circle>
                        <path d="M12 2v20"></path>
                        <path d="M2 12h20"></path>
                    </svg>
                </button>
                <button onclick="readPageContent()" title="Read Page Content">
                    <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <polygon points="11 5 6 9 2 9 2 15 6 15 11 19 11 5"></polygon>
                        <path d="M15 9.34V5a4 4 0 0 1 0 8v-4.34z"></path>
                        <path d="M19 9.34V5a8 8 0 0 1 0 8v-4.34z"></path>
                    </svg>
                </button>
            </div>
        </main>
        
        <!-- Custom Footer Instead of Including an Empty File -->
        <?php include '../includes/footer.php'; ?>
        
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
            // Voice Assistant functionality
            document.querySelector('.voice-assistant').addEventListener('click', function() {
                alert("Voice Assistant: Say commands like 'open health', 'show medications', 'read news', or 'call help' to navigate the app.");
                
                // In a real implementation, you would use the Web Speech API here
                // Example: https://developer.mozilla.org/en-US/docs/Web/API/Web_Speech_API
                
                if ('webkitSpeechRecognition' in window) {
                    // Voice recognition capabilities available
                    console.log("Voice recognition supported");
                    // Implementation would go here
                } else {
                    alert("Voice recognition is not supported in your browser. Try using Chrome for best experience.");
                }
            });
            
            // Enhanced Accessibility Functions
            let currentFontSize = 100; // percentage
            let highContrastMode = false;
            
            // Increase text size functionality
            function increaseFontSize() {
                if (currentFontSize < 150) {
                    currentFontSize += 10;
                    document.body.style.fontSize = currentFontSize + '%';
                    showToast("Text size increased");
                } else {
                    showToast("Maximum text size reached");
                }
            }
            
            // Decrease text size functionality
            function decreaseFontSize() {
                if (currentFontSize > 90) {
                    currentFontSize -= 10;
                    document.body.style.fontSize = currentFontSize + '%';
                    showToast("Text size decreased");
                } else {
                    showToast("Minimum text size reached");
                }
            }
            
            // Toggle high contrast mode
            function toggleHighContrast() {
                highContrastMode = !highContrastMode;
                
                if (highContrastMode) {
                    document.body.classList.add('high-contrast');
                    document.body.style.backgroundColor = "#000";
                    document.body.style.color = "#FFF";
                    
                    // Change background color of various elements
                    document.querySelectorAll('.feature-card, .stat-card, .dashboard-welcome, .daily-news-section, .news-card').forEach(function(element) {
                        element.style.backgroundColor = "#000";
                        element.style.color = "#FFF";
                        element.style.border = "1px solid #FFF";
                    });
                    
                    showToast("High contrast mode enabled");
                } else {
                    document.body.classList.remove('high-contrast');
                    document.body.style.backgroundColor = "";
                    document.body.style.color = "";
                    
                    // Reset background color of various elements
                    document.querySelectorAll('.feature-card, .stat-card, .dashboard-welcome, .daily-news-section, .news-card').forEach(function(element) {
                        element.style.backgroundColor = "";
                        element.style.color = "";
                        element.style.border = "";
                    });
                    
                    showToast("High contrast mode disabled");
                }
            }
            
            // Read page content function
            function readPageContent() {
                if ('speechSynthesis' in window) {
                    // Get main content to read
                    var mainContent = document.querySelector('main').innerText;
                    var cleanedContent = mainContent.replace(/\s+/g, ' ').trim();
                    
                    // Create utterance
                    var utterance = new SpeechSynthesisUtterance(cleanedContent);
                    utterance.rate = 0.9; // Slightly slower for elderly users
                    utterance.pitch = 1;
                    
                    // Stop any current speech
                    window.speechSynthesis.cancel();
                    
                    // Start speaking
                    window.speechSynthesis.speak(utterance);
                    showToast("Reading page content...");
                    
                    // Add stop button
                    createStopReadingButton();
                } else {
                    alert("Text-to-speech is not supported in your browser.");
                }
            }
            
            // Create stop reading button
            function createStopReadingButton() {
                // Remove existing button if present
                var existingButton = document.getElementById('stopReadingButton');
                if (existingButton) {
                    existingButton.remove();
                }
                
                // Create new button
                var stopButton = document.createElement('button');
                stopButton.id = 'stopReadingButton';
                stopButton.innerText = 'Stop Reading';
                stopButton.style.position = 'fixed';
                stopButton.style.bottom = '180px';
                stopButton.style.left = '30px';
                stopButton.style.zIndex = '1000';
                stopButton.style.padding = '10px 15px';
                stopButton.style.backgroundColor = '#e74c3c';
                stopButton.style.color = 'white';
                stopButton.style.border = 'none';
                stopButton.style.borderRadius = '5px';
                stopButton.style.cursor = 'pointer';
                stopButton.style.boxShadow = '0 2px 5px rgba(0,0,0,0.2)';
                
                stopButton.onclick = function() {
                    window.speechSynthesis.cancel();
                    stopButton.remove();
                    showToast("Reading stopped");
                };
                
                document.body.appendChild(stopButton);
            }
            
            // Simple toast notification system
            function showToast(message) {
                // Remove existing toast if present
                var existingToast = document.getElementById('accessibilityToast');
                if (existingToast) {
                    existingToast.remove();
                }
                
                // Create toast element
                var toast = document.createElement('div');
                toast.id = 'accessibilityToast';
                toast.innerText = message;
                toast.style.position = 'fixed';
                toast.style.bottom = '30px';
                toast.style.left = '50%';
                toast.style.transform = 'translateX(-50%)';
                toast.style.backgroundColor = 'rgba(0, 150, 173, 0.9)';
                toast.style.color = 'white';
                toast.style.padding = '10px 20px';
                toast.style.borderRadius = '5px';
                toast.style.zIndex = '2000';
                toast.style.boxShadow = '0 3px 10px rgba(0,0,0,0.2)';
                
                // Add to document
                document.body.appendChild(toast);
                
                // Remove after 3 seconds
                setTimeout(function() {
                    toast.remove();
                }, 3000);
            }
            
            // Add a simple text-to-speech function for important content
            function speakText(text) {
                if ('speechSynthesis' in window) {
                    var utterance = new SpeechSynthesisUtterance(text);
                    window.speechSynthesis.speak(utterance);
                }
            }
            
            // Initialize tooltips for better explanation of features
            document.querySelectorAll('.feature-card').forEach(function(card) {
                card.addEventListener('mouseover', function() {
                    var featureName = this.querySelector('h2').textContent;
                    var featureDesc = this.querySelector('p').textContent;
                    console.log("Hovering over: " + featureName);
                    // Additional functionality could be added here
                });
            });
            
            // Emergency button functionality
            document.querySelector('.emergency-button').addEventListener('click', function() {
                if (confirm("Do you need emergency assistance? Press OK to call for help.")) {
                    alert("Emergency alert sent! Help is on the way. If this is a medical emergency, call 999 immediately.");
                    // In a real application, this would trigger alerts to caregivers and emergency contacts
                }
            });
        </script>
    </body>
</html>