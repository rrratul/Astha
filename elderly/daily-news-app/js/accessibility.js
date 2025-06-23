<?php
// daily-news.php

// Sample news data (in a real application, this would come from a database)
$newsArticles = [
    [
        'title' => 'Community Garden Opens in Local Park',
        'date' => 'October 1, 2023',
        'content' => 'A new community garden has opened in Riverside Park. Residents are invited to participate in planting and maintaining the garden. This initiative aims to promote community engagement and healthy eating.'
    ],
    [
        'title' => 'Local Library Hosts Free Workshops',
        'date' => 'October 2, 2023',
        'content' => 'The local library will be hosting free workshops on digital literacy every Wednesday. These workshops are designed to help seniors learn how to use smartphones and computers effectively.'
    ],
    [
        'title' => 'Health Fair Scheduled for Next Month',
        'date' => 'October 3, 2023',
        'content' => 'A health fair will take place on November 15 at the community center. Free health screenings and information on healthy living will be available to all attendees.'
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
            text-align: center;
            color: #0056b3;
        }
        .article {
            background-color: #fff;
            border: 1px solid #ddd;
            border-radius: 5px;
            padding: 15px;
            margin: 20px 0;
        }
        .article h2 {
            font-size: 1.8em;
            margin: 0 0 10px;
        }
        .article p {
            font-size: 1.2em;
            margin: 0;
        }
        .date {
            font-size: 1em;
            color: #777;
            margin-bottom: 10px;
        }
    </style>
</head>
<body>

    <h1>Daily News</h1>

    <?php foreach ($newsArticles as $article): ?>
        <div class="article">
            <div class="date"><?php echo $article['date']; ?></div>
            <h2><?php echo $article['title']; ?></h2>
            <p><?php echo $article['content']; ?></p>
        </div>
    <?php endforeach; ?>

</body>
</html>