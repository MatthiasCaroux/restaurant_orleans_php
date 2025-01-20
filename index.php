

<?php

$action = $_REQUEST['action'] ?? 'home'; // Par défaut, aller à 'home'
$path = "_inc/templates/";

switch ($action) {
    case 'home':
        include $path .'home.php';
        break;
    case 'quiz':
        include $path . 'quiz.php';
        break;
    case 'leaderboard':
        include $path . 'leaderboard.php';
        break;
    case 'about':
        include $path . 'about.php';
        break;
    case 'import-export': // Nouvelle action
        include $path . 'import_export.php';
        break;

    case 'results': 
        include $path . "results.php";
        break;

    case 'nbQuestions': 
        include $path . "nbQuestions.php";
        break;

    default: // Action par défaut (retourne à l'accueil)
        include $path . "home.php";
        break;

    
}

?>
