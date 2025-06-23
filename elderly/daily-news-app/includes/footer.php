<?php
// daily-news.php

// Sample news articles (in a real application, this data would likely come from a database)
$newsArticles = [
    [
        'title' => 'Community Garden Opens in Local Park',
        'date' => 'October 1, 2023',
        'content' => 'A new community garden has opened in Central Park, providing residents with a space to grow their own vegetables and flowers. Everyone is welcome to join the gardening club!'
    ],
    [
        'title' => 'Health Fair Scheduled for Next Month',
        'date' => 'October 2, 2023',
        'content' => 'A health fair will be held on November 15, 2023, at the Community Center. Free health screenings and information on healthy living will be available.'
    ],
    [
        'title' => 'Local Library Hosts Book Reading',
        'date' => 'October 3, 2023',
        'content' => 'Join us at the local library on October 10 for a special book reading by local author Jane Smith. The event starts at 2 PM and is free for all ages.'
    ]
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
            line-height: 1.6;
            background-color: #f9f9f9;
            color: #333;
            margin: 0;
            padding: 20px;
        }
        h1 {
            font-size: 2.5em;
            color: #2c3e50;
        }
        h2 {
            font-size: 2em;
            color: #2980b9;
        }
        p {
            font-size: 1.2em;
            margin: 10px 0;
        }
        .news-article {
            background-color: #ffffff;
            border: 1px solid #ddd;
            border-radius: 5px;
            padding: 15px;
            margin-bottom: 20px;
        }
        .date {
            font-size: 1em;
            color: #7f8c8d;
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