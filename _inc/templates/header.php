<?php
$cssPath = "_inc/static/";

// Démarrer la session si elle n'est pas déjà démarrée
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// Vérifier si l'utilisateur est connecté
$isLoggedIn = isset($_SESSION['user_id']);
?>

<header>
    <nav>
        <div id="logo">
            <img src="<?php echo $cssPath; ?>logo_site.png" alt="Logo">
            <h1>IUTables'O</h1>
        </div>
        <ul>
            <li><a href="index.php">Découvrir</a></li>
            <?php if ($isLoggedIn): ?>
                <li><a href="favoris.php">Favoris</a></li>
                <li><a href="profil.php">Profil</a></li>
            <?php endif; ?>
        </ul>
        <?php if ($isLoggedIn): ?>
            <div class="user-info">
                <a href="logout.php"><button class="logout-btn">Se déconnecter</button></a>
            </div>
        <?php else: ?>
            <a href="login.php"><button class="login-btn">Se connecter</button></a>
        <?php endif; ?>
    </nav>
</header>
