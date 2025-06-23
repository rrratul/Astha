<?php
// filepath: c:\xampp\htdocs\Astha\elderly\daily-news.php
session_start();

// // Include required files with correct paths
// require_once '../login_user/functions.php';
// require_once '../signup_user/config.php';

// // Simple authentication check
// if (!isLoggedIn()) {
//     header("Location: ../login_user/login_user.php");
//     exit();
// }

// Check role
if (isset($_SESSION['user_role'])) {
    $allowed_roles = ['senior', 'elderly']; 
    if (!in_array($_SESSION['user_role'], $allowed_roles)) {
        switch ($_SESSION['user_role']) {
            case 'caregiver':
                header("Location: ../caregiver/index.php");
                exit();
            case 'family_member':
                header("Location: ../family/index.php");
                exit();
            case 'healthcare_provider':
                header("Location: ../healthcare/index.php");
                exit();
            default:
                header("Location: logout.php");
                exit();
        }
    }
}

$user_first_name = $_SESSION['user_first_name'] ?? 'User';

// Sample news data - In a real application, this would come from a database or API
$featured_news = [
    [
        'id' => 1,
        'title' => 'New Heart Health Guidelines Released for Seniors',
        'category' => 'Health',
        'summary' => 'Latest medical guidelines recommend specific heart health practices for people over 65, including daily walking and heart-healthy nutrition.',
        'content' => 'Medical experts have released new comprehensive guidelines for heart health specifically tailored for seniors. The guidelines emphasize the importance of regular physical activity, particularly walking for at least 30 minutes daily. The recommendations also include dietary changes such as reducing sodium intake, eating more fruits and vegetables, and maintaining a healthy weight. Regular check-ups and monitoring blood pressure are also crucial components of the new guidelines.',
        'image' => 'https://via.placeholder.com/800x400/4CAF50/ffffff?text=Heart+Health',
        'video' => 'https://www.youtube.com/embed/dQw4w9WgXcQ',
        'date' => date('Y-m-d'),
        'read_time' => '5 min read',
        'source' => 'Health Today'
    ],
    [
        'id' => 2,
        'title' => 'Technology Classes for Seniors Now Available at Community Centers',
        'category' => 'Technology',
        'summary' => 'Local community centers are offering free technology classes to help seniors learn smartphones, tablets, and video calling.',
        'content' => 'Community centers across the country are launching free technology education programs specifically designed for seniors. These classes cover basic smartphone usage, tablet navigation, video calling with family, and online safety. Instructors use simple, step-by-step approaches and provide plenty of hands-on practice time. Classes are limited to small groups to ensure personalized attention for each participant.',
        'image' => 'https://via.placeholder.com/800x400/2196F3/ffffff?text=Tech+Classes',
        'video' => 'https://www.youtube.com/embed/dQw4w9WgXcQ',
        'date' => date('Y-m-d'),
        'read_time' => '3 min read',
        'source' => 'Community News'
    ]
];

$latest_news = [
    [
        'id' => 3,
        'title' => 'Weather Alert: Cool Weather Expected This Week',
        'category' => 'Weather',
        'summary' => 'Temperatures will drop to 15°C this week. Health experts advise seniors to dress warmly.',
        'image' => 'https://via.placeholder.com/400x250/FF9800/ffffff?text=Weather',
        'date' => date('Y-m-d'),
        'read_time' => '2 min read'
    ],
    [
        'id' => 4,
        'title' => 'New Senior Discount Program at Local Pharmacies',
        'category' => 'Local News',
        'summary' => 'Local pharmacies are offering 20% discounts on medications for seniors over 65.',
        'image' => 'https://via.placeholder.com/400x250/9C27B0/ffffff?text=Pharmacy',
        'date' => date('Y-m-d'),
        'read_time' => '3 min read'
    ],
    [
        'id' => 5,
        'title' => 'Exercise Programs for Seniors Starting Next Month',
        'category' => 'Health',
        'summary' => 'Free gentle exercise classes designed for seniors will begin at the recreation center.',
        'image' => 'https://via.placeholder.com/400x250/4CAF50/ffffff?text=Exercise',
        'date' => date('Y-m-d'),
        'read_time' => '4 min read'
    ],
    [
        'id' => 6,
        'title' => 'Social Security Benefits Update for 2024',
        'category' => 'Finance',
        'summary' => 'Important changes to social security benefits that affect all seniors.',
        'image' => 'https://via.placeholder.com/400x250/607D8B/ffffff?text=Finance',
        'date' => date('Y-m-d'),
        'read_time' => '6 min read'
    ]
];

// Get selected article if any
$selected_article = null;
if (isset($_GET['article'])) {
    $article_id = (int)$_GET['article'];
    foreach ($featured_news as $article) {
        if ($article['id'] === $article_id) {
            $selected_article = $article;
            break;
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Digital Daily News - Shohochor</title>
    <link rel="stylesheet" href="./includes/style.css">
    <link rel="stylesheet" href="./includes/dashboard.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    
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
    
    <style>

        .news-container {
            max-width: 1400px;
            margin: 0 auto;
            padding: 2rem 1rem;
            background: #f8f9fa;
            min-height: 100vh;
            font-size: 1.1rem; /* Larger base font size */
            line-height: 1.6; /* Better readability */
        }

        .news-header {
            background: linear-gradient(135deg, #1e3c72 0%, #2a5298 100%);
            color: white;
            padding: 3rem 2rem;
            border-radius: 20px;
            margin-bottom: 3rem;
            text-align: center;
            box-shadow: 0 10px 30px rgba(30, 60, 114, 0.3);
        }

        .news-header h1 {
            font-size: 3rem;
            margin: 0 0 1rem 0;
            font-weight: 700;
        }

        .news-header .date {
            font-size: 1.4rem;
            opacity: 0.9;
            margin-bottom: 0.5rem;
        }

        .news-header .welcome {
            font-size: 1.2rem;
            opacity: 0.8;
        }

        /* Navigation Tabs */
        .news-tabs {
            display: flex;
            justify-content: center;
            margin-bottom: 3rem;
            background: white;
            border-radius: 15px;
            padding: 1rem;
            box-shadow: 0 4px 15px rgba(0,0,0,0.1);
            gap: 1rem;
            flex-wrap: wrap;
        }

        .news-tab {
            padding: 1rem 2rem;
            background: #f8f9fa;
            border: none;
            border-radius: 10px;
            font-size: 1.2rem;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
            color: #4a5568;
            min-width: 120px;
        }

        .news-tab.active {
            background: #0096ad;
            color: white;
            transform: translateY(-2px);
            box-shadow: 0 4px 15px rgba(0, 150, 173, 0.3);
        }

        .news-tab:hover {
            background: #e2e8f0;
        }

        .news-tab.active:hover {
            background: #007489;
        }

        /* Featured Article Section */
        .featured-section {
            margin-bottom: 4rem;
        }

        .section-title {
            font-size: 2.5rem;
            color: #2d3748;
            margin-bottom: 2rem;
            text-align: center;
            font-weight: 700;
        }

        .featured-article {
            background: white;
            border-radius: 20px;
            overflow: hidden;
            box-shadow: 0 8px 30px rgba(0,0,0,0.1);
            margin-bottom: 2rem;
        }

        .featured-image {
            width: 100%;
            height: 400px;
            object-fit: cover;
        }

        .featured-content {
            padding: 3rem;
        }

        .article-category {
            display: inline-block;
            background: #0096ad;
            color: white;
            padding: 0.5rem 1.5rem;
            border-radius: 25px;
            font-size: 1rem;
            font-weight: 600;
            margin-bottom: 1.5rem;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .article-title {
            font-size: 2.8rem;
            font-weight: 700;
            color: #2d3748;
            margin-bottom: 1.5rem;
            line-height: 1.3;
        }

        .article-meta {
            display: flex;
            align-items: center;
            gap: 2rem;
            margin-bottom: 2rem;
            font-size: 1.1rem;
            color: #718096;
        }

        .article-meta i {
            color: #0096ad;
            margin-right: 0.5rem;
        }

        .article-summary {
            font-size: 1.4rem;
            color: #4a5568;
            margin-bottom: 2rem;
            line-height: 1.6;
            font-weight: 500;
        }

        .article-actions {
            display: flex;
            gap: 1.5rem;
            flex-wrap: wrap;
        }

        .action-btn {
            padding: 1rem 2rem;
            border: none;
            border-radius: 12px;
            font-size: 1.2rem;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
            display: flex;
            align-items: center;
            gap: 0.5rem;
            text-decoration: none;
            min-width: 150px;
            justify-content: center;
        }

        .read-btn {
            background: #0096ad;
            color: white;
        }

        .read-btn:hover {
            background: #007489;
            transform: translateY(-2px);
        }

        .watch-btn {
            background: #dc3545;
            color: white;
        }

        .watch-btn:hover {
            background: #c82333;
            transform: translateY(-2px);
        }

        .listen-btn {
            background: #28a745;
            color: white;
        }

        .listen-btn:hover {
            background: #218838;
            transform: translateY(-2px);
        }

        /* Latest News Grid */
        .latest-news {
            margin-bottom: 4rem;
        }

        .news-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(350px, 1fr));
            gap: 2rem;
            margin-top: 2rem;
        }

        .news-card {
            background: white;
            border-radius: 16px;
            overflow: hidden;
            box-shadow: 0 6px 20px rgba(0,0,0,0.08);
            transition: all 0.3s ease;
            cursor: pointer;
        }

        .news-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 12px 35px rgba(0,0,0,0.15);
        }

        .news-card-image {
            width: 100%;
            height: 220px;
            object-fit: cover;
        }

        .news-card-content {
            padding: 2rem;
        }

        .news-card-category {
            display: inline-block;
            background: #f7fafc;
            color: #0096ad;
            padding: 0.4rem 1rem;
            border-radius: 15px;
            font-size: 0.9rem;
            font-weight: 600;
            margin-bottom: 1rem;
            text-transform: uppercase;
        }

        .news-card-title {
            font-size: 1.6rem;
            font-weight: 700;
            color: #2d3748;
            margin-bottom: 1rem;
            line-height: 1.4;
        }

        .news-card-summary {
            color: #4a5568;
            margin-bottom: 1.5rem;
            font-size: 1.1rem;
            line-height: 1.5;
        }

        .news-card-meta {
            display: flex;
            justify-content: space-between;
            align-items: center;
            color: #718096;
            font-size: 1rem;
        }

        /* Article View Modal */
        .article-modal {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0,0,0,0.8);
            z-index: 1000;
            display: none;
            overflow-y: auto;
        }

        .article-modal-content {
            max-width: 900px;
            margin: 2rem auto;
            background: white;
            border-radius: 20px;
            overflow: hidden;
        }

        .article-modal-header {
            padding: 2rem;
            background: #2d3748;
            color: white;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .close-modal {
            background: none;
            border: none;
            color: white;
            font-size: 2rem;
            cursor: pointer;
            padding: 0.5rem;
            border-radius: 50%;
            transition: background 0.3s ease;
        }

        .close-modal:hover {
            background: rgba(255,255,255,0.1);
        }

        .article-modal-body {
            padding: 3rem;
        }

        .article-full-content {
            font-size: 1.3rem;
            line-height: 1.8;
            color: #2d3748;
            margin-bottom: 2rem;
        }

        /* Video Modal */
        .video-modal {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0,0,0,0.9);
            z-index: 1001;
            display: none;
            align-items: center;
            justify-content: center;
        }

        .video-container {
            width: 90%;
            max-width: 800px;
            background: white;
            border-radius: 15px;
            overflow: hidden;
        }

        .video-header {
            background: #2d3748;
            color: white;
            padding: 1rem 2rem;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .video-iframe {
            width: 100%;
            height: 450px;
            border: none;
        }

        /* Accessibility Controls */
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
            width: 60px;
            height: 60px;
            margin-bottom: 1rem;
            border: none;
            border-radius: 12px;
            background: #f8f9fa;
            cursor: pointer;
            transition: all 0.3s ease;
            font-size: 1.5rem;
            color: #4a5568;
        }

        .accessibility-btn:hover {
            background: #0096ad;
            color: white;
            transform: scale(1.1);
        }

        .accessibility-btn:last-child {
            margin-bottom: 0;
        }

        /* Responsive Design */
        @media (max-width: 768px) {
            .news-container {
                padding: 1rem 0.5rem;
            }

            .news-header h1 {
                font-size: 2.2rem;
            }

            .article-title {
                font-size: 2rem;
            }

            .featured-content {
                padding: 2rem;
            }

            .article-modal-body {
                padding: 2rem;
            }

            .accessibility-panel {
                left: 10px;
            }

            .accessibility-btn {
                width: 50px;
                height: 50px;
                font-size: 1.2rem;
            }

            .news-tabs {
                padding: 0.5rem;
            }

            .news-tab {
                padding: 0.8rem 1.5rem;
                font-size: 1rem;
                min-width: 100px;
            }

            .action-btn {
                padding: 0.8rem 1.5rem;
                font-size: 1.1rem;
                min-width: 120px;
            }
        }

        /* High Contrast Mode */
        .high-contrast {
            background: #000 !important;
            color: #fff !important;
        }

        .high-contrast .news-card,
        .high-contrast .featured-article,
        .high-contrast .accessibility-panel {
            background: #000 !important;
            color: #fff !important;
            border: 2px solid #fff !important;
        }

        .high-contrast .news-tab {
            background: #000 !important;
            color: #fff !important;
            border: 2px solid #fff !important;
        }

        /* Large Text Mode */
        .large-text {
            font-size: 1.4rem !important;
        }

        .large-text .article-title {
            font-size: 3.2rem !important;
        }

        .large-text .news-card-title {
            font-size: 1.8rem !important;
        }
    </style>
</head>

<body>


    <main>
        <div class="news-container">
            <!-- News Header -->
            <div class="news-header">
                <h1>📰 Digital Daily News</h1>
                <div class="date">
                    <i class="fas fa-calendar-alt"></i>
                    <?php echo date('l, F j, Y'); ?>
                </div>
                <div class="welcome">
                    Good <?php echo (date('H') < 12) ? 'morning' : ((date('H') < 18) ? 'afternoon' : 'evening'); ?>, <?php echo htmlspecialchars($user_first_name); ?>!
                </div>
            </div>

            <!-- Navigation Tabs -->
            <div class="news-tabs">
                <button class="news-tab active" onclick="showSection('all')">
                    <i class="fas fa-newspaper"></i> All News
                </button>
                <button class="news-tab" onclick="showSection('health')">
                    <i class="fas fa-heartbeat"></i> Health
                </button>
                <button class="news-tab" onclick="showSection('local')">
                    <i class="fas fa-map-marker-alt"></i> Local
                </button>
                <button class="news-tab" onclick="showSection('technology')">
                    <i class="fas fa-laptop"></i> Technology
                </button>
                <button class="news-tab" onclick="showSection('weather')">
                    <i class="fas fa-cloud-sun"></i> Weather
                </button>
            </div>

            <!-- Featured Stories Section -->
            <div class="featured-section">
                <h2 class="section-title">📺 Today's Featured Stories</h2>
                
                <?php foreach ($featured_news as $article): ?>
                <div class="featured-article" data-category="<?php echo strtolower($article['category']); ?>">
                    <img src="<?php echo $article['image']; ?>" alt="<?php echo htmlspecialchars($article['title']); ?>" class="featured-image">
                    <div class="featured-content">
                        <span class="article-category"><?php echo $article['category']; ?></span>
                        <h3 class="article-title"><?php echo htmlspecialchars($article['title']); ?></h3>
                        <div class="article-meta">
                            <span><i class="fas fa-calendar"></i> <?php echo date('M j, Y', strtotime($article['date'])); ?></span>
                            <span><i class="fas fa-clock"></i> <?php echo $article['read_time']; ?></span>
                            <span><i class="fas fa-tag"></i> <?php echo $article['source']; ?></span>
                        </div>
                        <p class="article-summary"><?php echo htmlspecialchars($article['summary']); ?></p>
                        <div class="article-actions">
                            <button class="action-btn read-btn" onclick="readArticle(<?php echo $article['id']; ?>)">
                                <i class="fas fa-book-open"></i> Read Article
                            </button>
                            <button class="action-btn watch-btn" onclick="watchVideo('<?php echo $article['video']; ?>', '<?php echo htmlspecialchars($article['title']); ?>')">
                                <i class="fas fa-play"></i> Watch Video
                            </button>
                            <button class="action-btn listen-btn" onclick="listenArticle('<?php echo htmlspecialchars($article['content']); ?>')">
                                <i class="fas fa-volume-up"></i> Listen
                            </button>
                        </div>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>

            <!-- Latest News Section -->
            <div class="latest-news">
                <h2 class="section-title">🔥 Latest News</h2>
                <div class="news-grid">
                    <?php foreach ($latest_news as $news): ?>
                    <div class="news-card" data-category="<?php echo strtolower(str_replace(' ', '', $news['category'])); ?>" onclick="showNewsDetails(<?php echo $news['id']; ?>)">
                        <img src="<?php echo $news['image']; ?>" alt="<?php echo htmlspecialchars($news['title']); ?>" class="news-card-image">
                        <div class="news-card-content">
                            <span class="news-card-category"><?php echo $news['category']; ?></span>
                            <h4 class="news-card-title"><?php echo htmlspecialchars($news['title']); ?></h4>
                            <p class="news-card-summary"><?php echo htmlspecialchars($news['summary']); ?></p>
                            <div class="news-card-meta">
                                <span><i class="fas fa-calendar"></i> <?php echo date('M j', strtotime($news['date'])); ?></span>
                                <span><i class="fas fa-clock"></i> <?php echo $news['read_time']; ?></span>
                            </div>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>

        <!-- Accessibility Panel -->
        <div class="accessibility-panel">
            <button class="accessibility-btn" onclick="increaseFontSize()" title="Increase Text Size">
                <span style="font-size: 1.2em;">A+</span>
            </button>
            <button class="accessibility-btn" onclick="decreaseFontSize()" title="Decrease Text Size">
                <span style="font-size: 0.9em;">A-</span>
            </button>
            <button class="accessibility-btn" onclick="toggleHighContrast()" title="High Contrast">
                <i class="fas fa-adjust"></i>
            </button>
            <button class="accessibility-btn" onclick="readPageContent()" title="Read Page">
                <i class="fas fa-volume-up"></i>
            </button>
        </div>

        <!-- Article Modal -->
        <div id="articleModal" class="article-modal">
            <div class="article-modal-content">
                <div class="article-modal-header">
                    <h3 id="modalTitle">Article Title</h3>
                    <button class="close-modal" onclick="closeModal('articleModal')">&times;</button>
                </div>
                <div class="article-modal-body">
                    <div id="modalContent" class="article-full-content">
                        Article content will appear here...
                    </div>
                    <div class="article-actions">
                        <button class="action-btn listen-btn" onclick="listenModalContent()">
                            <i class="fas fa-volume-up"></i> Listen to Article
                        </button>
                        <button class="action-btn read-btn" onclick="printArticle()">
                            <i class="fas fa-print"></i> Print Article
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <!-- Video Modal -->
        <div id="videoModal" class="video-modal">
            <div class="video-container">
                <div class="video-header">
                    <h3 id="videoTitle">Video Title</h3>
                    <button class="close-modal" onclick="closeVideoModal()">&times;</button>
                </div>
                <iframe id="videoFrame" class="video-iframe" src="" allowfullscreen></iframe>
            </div>
        </div>
    </main>

    <script>
        // Global variables
        let currentFontSize = 1.1;
        let highContrastMode = false;
        let currentSpeech = null;

        // Articles data for modal content
        const articlesData = {
            1: {
                title: "New Heart Health Guidelines Released for Seniors",
                content: `Medical experts have released new comprehensive guidelines for heart health specifically tailored for seniors. The guidelines emphasize the importance of regular physical activity, particularly walking for at least 30 minutes daily.

                The recommendations also include dietary changes such as reducing sodium intake, eating more fruits and vegetables, and maintaining a healthy weight. Regular check-ups and monitoring blood pressure are also crucial components of the new guidelines.

                Dr. Sarah Johnson, a cardiologist at the National Heart Institute, explains: "These guidelines are based on the latest research showing that seniors who follow these simple steps can reduce their risk of heart disease by up to 40%."

                Key recommendations include:
                • Walk for 30 minutes daily
                • Eat 5 servings of fruits and vegetables per day
                • Limit sodium to 2,300mg per day
                • Get regular blood pressure checks
                • Take prescribed medications as directed
                • Maintain a healthy weight

                The guidelines also stress the importance of social connections and mental health, noting that seniors who stay socially active tend to have better heart health outcomes.`
            },
            2: {
                title: "Technology Classes for Seniors Now Available at Community Centers",
                content: `Community centers across the country are launching free technology education programs specifically designed for seniors. These classes cover basic smartphone usage, tablet navigation, video calling with family, and online safety.

                The program was developed in response to the growing digital divide affecting older adults. "We realized that many seniors were feeling left behind by technology," says program coordinator Maria Rodriguez.

                Classes cover:
                • Basic smartphone and tablet operation
                • Making video calls to family members
                • Sending text messages and emails
                • Online safety and avoiding scams
                • Using health apps and medical portals
                • Social media basics

                Instructors use simple, step-by-step approaches and provide plenty of hands-on practice time. Classes are limited to small groups to ensure personalized attention for each participant.

                "The response has been overwhelming," Rodriguez notes. "We have waiting lists at most locations, and participants often ask for advanced classes after completing the basics."

                To register for classes, contact your local community center or visit the program website.`
            }
        };

        // Navigation functions
        function showSection(category) {
            // Update tab appearance
            document.querySelectorAll('.news-tab').forEach(tab => {
                tab.classList.remove('active');
            });
            event.target.classList.add('active');

            // Show/hide articles based on category
            const articles = document.querySelectorAll('[data-category]');
            articles.forEach(article => {
                if (category === 'all') {
                    article.style.display = 'block';
                } else {
                    if (article.dataset.category.includes(category.toLowerCase())) {
                        article.style.display = 'block';
                    } else {
                        article.style.display = 'none';
                    }
                }
            });

            showToast(`Showing ${category === 'all' ? 'all' : category} news`);
        }

        // Article reading functions
        function readArticle(articleId) {
            const article = articlesData[articleId];
            if (article) {
                document.getElementById('modalTitle').textContent = article.title;
                document.getElementById('modalContent').innerHTML = article.content.replace(/\n\n/g, '</p><p>').replace(/\n/g, '<br>');
                document.getElementById('articleModal').style.display = 'block';
                document.body.style.overflow = 'hidden';
            }
        }

        function watchVideo(videoUrl, title) {
            document.getElementById('videoTitle').textContent = title;
            document.getElementById('videoFrame').src = videoUrl;
            document.getElementById('videoModal').style.display = 'flex';
            document.body.style.overflow = 'hidden';
        }

        function listenArticle(content) {
            stopCurrentSpeech();
            speakText(content);
            showToast("Playing audio version of the article");
        }

        function listenModalContent() {
            const content = document.getElementById('modalContent').textContent;
            stopCurrentSpeech();
            speakText(content);
            showToast("Playing audio version");
        }

        // Modal management
        function closeModal(modalId) {
            document.getElementById(modalId).style.display = 'none';
            document.body.style.overflow = 'auto';
        }

        function closeVideoModal() {
            document.getElementById('videoModal').style.display = 'none';
            document.getElementById('videoFrame').src = '';
            document.body.style.overflow = 'auto';
        }

        // News details function
        function showNewsDetails(newsId) {
            showToast("Opening news article...");
            // In a real application, this would load the full article
            alert(`Loading full article for news ID: ${newsId}\nThis would open the detailed article view.`);
        }

        // Accessibility functions
        function increaseFontSize() {
            if (currentFontSize < 2.0) {
                currentFontSize += 0.1;
                document.body.style.fontSize = currentFontSize + 'rem';
                showToast("Text size increased");
            } else {
                showToast("Maximum text size reached");
            }
        }

        function decreaseFontSize() {
            if (currentFontSize > 0.8) {
                currentFontSize -= 0.1;
                document.body.style.fontSize = currentFontSize + 'rem';
                showToast("Text size decreased");
            } else {
                showToast("Minimum text size reached");
            }
        }

        function toggleHighContrast() {
            highContrastMode = !highContrastMode;
            if (highContrastMode) {
                document.body.classList.add('high-contrast');
                showToast("High contrast mode enabled");
            } else {
                document.body.classList.remove('high-contrast');
                showToast("High contrast mode disabled");
            }
        }

        function readPageContent() {
            stopCurrentSpeech();
            const mainContent = document.querySelector('.news-container').textContent;
            const cleanedContent = mainContent.replace(/\s+/g, ' ').trim();
            speakText(cleanedContent.substring(0, 3000) + "... Content is too long. Please use specific article reading features.");
            showToast("Reading page content");
        }

        // Text-to-speech functions
        function speakText(text) {
            if ('speechSynthesis' in window) {
                stopCurrentSpeech();
                currentSpeech = new SpeechSynthesisUtterance(text);
                currentSpeech.rate = 0.8; // Slower for elderly users
                currentSpeech.pitch = 1;
                currentSpeech.volume = 1;
                
                // Get available voices
                const voices = speechSynthesis.getVoices();
                if (voices.length > 0) {
                    // Try to find a female voice for better clarity
                    const femaleVoice = voices.find(voice => voice.name.includes('Female') || voice.name.includes('female'));
                    if (femaleVoice) currentSpeech.voice = femaleVoice;
                }
                
                currentSpeech.onend = function() {
                    currentSpeech = null;
                };
                
                speechSynthesis.speak(currentSpeech);
                
                // Add stop button
                createStopSpeechButton();
            } else {
                alert("Text-to-speech is not supported in your browser.");
            }
        }

        function stopCurrentSpeech() {
            if (currentSpeech) {
                speechSynthesis.cancel();
                currentSpeech = null;
                removeStopSpeechButton();
            }
        }

        function createStopSpeechButton() {
            removeStopSpeechButton();
            
            const stopButton = document.createElement('button');
            stopButton.id = 'stopSpeechButton';
            stopButton.innerHTML = '<i class="fas fa-stop"></i> Stop Reading';
            stopButton.style.cssText = `
                position: fixed;
                bottom: 30px;
                right: 30px;
                background: #dc3545;
                color: white;
                border: none;
                padding: 1rem 1.5rem;
                border-radius: 50px;
                font-size: 1.1rem;
                font-weight: 600;
                cursor: pointer;
                z-index: 1002;
                box-shadow: 0 4px 15px rgba(220, 53, 69, 0.3);
                transition: all 0.3s ease;
            `;
            
            stopButton.onclick = stopCurrentSpeech;
            document.body.appendChild(stopButton);
        }

        function removeStopSpeechButton() {
            const existingButton = document.getElementById('stopSpeechButton');
            if (existingButton) {
                existingButton.remove();
            }
        }

        // Print function
        function printArticle() {
            const title = document.getElementById('modalTitle').textContent;
            const content = document.getElementById('modalContent').innerHTML;
            
            const printWindow = window.open('', '', 'height=600,width=800');
            printWindow.document.write(`
                <html>
                <head>
                    <title>${title}</title>
                    <style>
                        body { font-family: Arial, sans-serif; font-size: 16px; line-height: 1.6; margin: 40px; }
                        h1 { color: #2d3748; margin-bottom: 20px; }
                        p { margin-bottom: 15px; }
                    </style>
                </head>
                <body>
                    <h1>${title}</h1>
                    <div>${content}</div>
                </body>
                </html>
            `);
            printWindow.document.close();
            printWindow.print();
        }

        // Toast notification
        function showToast(message) {
            const existingToast = document.getElementById('toast');
            if (existingToast) existingToast.remove();
            
            const toast = document.createElement('div');
            toast.id = 'toast';
            toast.textContent = message;
            toast.style.cssText = `
                position: fixed;
                top: 30px;
                left: 50%;
                transform: translateX(-50%);
                background: rgba(0, 150, 173, 0.9);
                color: white;
                padding: 1rem 2rem;
                border-radius: 25px;
                z-index: 1003;
                font-size: 1.1rem;
                font-weight: 600;
                box-shadow: 0 4px 15px rgba(0, 150, 173, 0.3);
            `;
            
            document.body.appendChild(toast);
            
            setTimeout(() => {
                toast.remove();
            }, 3000);
        }

        // Keyboard navigation
        document.addEventListener('keydown', function(e) {
            // ESC key to close modals
            if (e.key === 'Escape') {
                closeModal('articleModal');
                closeVideoModal();
                stopCurrentSpeech();
            }
            
            // Space bar to stop speech
            if (e.code === 'Space' && currentSpeech) {
                e.preventDefault();
                stopCurrentSpeech();
            }
        });

        // Voice loading for better compatibility
        if ('speechSynthesis' in window) {
            speechSynthesis.getVoices();
            window.speechSynthesis.onvoiceschanged = function() {
                speechSynthesis.getVoices();
            };
        }

        // Initialize page
        document.addEventListener('DOMContentLoaded', function() {
            showToast("Digital News loaded successfully!");
            
            // Auto-focus first article for keyboard users
            const firstTab = document.querySelector('.news-tab');
            if (firstTab) firstTab.focus();
        });
    </script>
</body>
</html>