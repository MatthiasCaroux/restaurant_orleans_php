<?php
$cssPath = "./_inc/static/";
include "_inc/data/questions.php";
require 'Classes/Autoloader.php';
Autoloader::register();

?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>QuizMaster</title>
    <link rel="stylesheet" href="<?php echo $cssPath . 'style.css'; ?>">
    <?php if (isset($additionalcss)): ?>
        <link rel="stylesheet" href="<?php echo $cssPath . $additionalcss; ?>">
    <?php endif; ?>
</head>
<body>
    <!-- Header du site -->
    <header>
        <div class="container">
            <h1>QuizMaster</h1>
            <nav>
                <ul>
                    <li><a href="index.php?action=home">Accueil</a></li>
                    <li><a href="index.php?action=nbQuestions">Commencer un Quiz</a></li>
                    <li><a href="index.php?action=leaderboard">Classement</a></li>
                    <li><a href="index.php?action=about">À propos</a></li>
                    <li><a href="index.php?action=import-export">Import/Export</a></li>
                </ul>
            </nav>
        </div>
    </header>
