<?php

// Démarrer la session si elle n'est pas déjà démarrée
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

$cssPath = "./_inc/static/styles/";


// Vérifier si l'utilisateur est connecté
$isLoggedIn = isset($_SESSION['user_id']);
?>

<head>
    <link rel="stylesheet" href="<?php echo $cssPath; ?>base.css">
    <link rel="stylesheet" href="<?php echo $cssPath; ?>header.css">
</head>

<header>
    <nav>
        <div id="logo">
            <img src="/_inc/static/images/logo_site.png" alt="Logo">
            <h1>IUTables'O</h1>
        </div>
        <ul>
            <li><a href="/index.php">Découvrir</a></li>
            <?php if ($isLoggedIn): ?>
                <li><a href="/vues/favoris.php">Favoris</a></li>
                <li><a href="/vues/profil.php">Profil</a></li>
            <?php endif; ?>
        </ul>
        <?php if ($isLoggedIn): ?>
            <div class="user-info">
                <a href="/back/logout.php"><button class="logout-btn">Se déconnecter</button></a>
            </div>
        <?php else: ?>
            <a href="/vues/login.php"><button class="login-btn">Se connecter</button></a>
        <?php endif; ?>
    </nav>
</header>
