<?php
// daily-news.php

// Sample news data (in a real application, this would come from a database)
$newsArticles = [
    [
        'title' => 'Community Garden Opens in Local Park',
        'date' => 'October 1, 2023',
        'content' => 'A new community garden has opened in Riverside Park. Residents are invited to participate in planting and maintaining the garden. Join us for the opening ceremony this Saturday at 10 AM.'
    ],
    [
        'title' => 'Health Fair Scheduled for Next Month',
        'date' => 'October 2, 2023',
        'content' => 'The annual Health Fair will take place on November 15 at the Community Center. Free health screenings and information on wellness will be available. Everyone is welcome!'
    ],
    [
        'title' => 'Local Library Hosts Book Club',
        'date' => 'October 3, 2023',
        'content' => 'Join the new book club at the local library. The first meeting will be on October 10 at 2 PM. This month’s book is "The Great Gatsby." All are welcome, and refreshments will be provided.'
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
            background-color: #f9f9f9;
            color: #333;
            line-height: 1.6;
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
            <p><?php echo nl2br(htmlspecialchars($article['content'])); ?></p>
        </div>
    <?php endforeach; ?>

</body>
</html>