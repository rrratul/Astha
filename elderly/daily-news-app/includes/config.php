<?php
// daily-news.php

// Sample news data (in a real application, this would come from a database)
$newsArticles = [
    [
        'title' => 'Community Center Hosts Free Health Fair',
        'date' => 'October 1, 2023',
        'content' => 'Join us at the community center this Saturday for a free health fair. There will be screenings, health information, and refreshments available.'
    ],
    [
        'title' => 'New Senior Exercise Program Launches',
        'date' => 'October 2, 2023',
        'content' => 'A new exercise program designed specifically for seniors will begin next week. Classes will be held every Tuesday and Thursday at 10 AM.'
    ],
    [
        'title' => 'Local Library Offers Technology Classes',
        'date' => 'October 3, 2023',
        'content' => 'The local library is offering free technology classes for seniors. Learn how to use smartphones, tablets, and computers.'
    ],
];

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Daily News</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f9f9f9;
            color: #333;
            line-height: 1.6;
            padding: 20px;
        }
        h1 {
            font-size: 2.5em;
            color: #0056b3;
        }
        h2 {
            font-size: 2em;
            color: #333;
        }
        p {
            font-size: 1.2em;
        }
        .news-article {
            background-color: #fff;
            border: 1px solid #ddd;
            border-radius: 5px;
            padding: 15px;
            margin-bottom: 20px;
        }
        .date {
            font-size: 1em;
            color: #777;
        }
    </style>
</head>
<body>

    <h1>Daily News</h1>

    <?php foreach ($newsArticles as $article): ?>
        <div class="news-article">
            <h2><?php echo htmlspecialchars($article['title']); ?></h2>
            <p class="date"><?php echo htmlspecialchars($article['date']); ?></p>
            <p><?php echo htmlspecialchars($article['content']); ?></p>
        </div>
    <?php endforeach; ?>

</body>
</html>