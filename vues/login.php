<?php
require_once '../Classes/Autoloader.php';
Autoloader::register();

// Démarrer la session
session_start();

// Si l'utilisateur est déjà connecté, le rediriger vers l'index
if (isset($_SESSION['user_id'])) {
    header('Location: index.php');
    exit();
}

$cssPath = "../_inc/static/styles/";
$imagesPath = "../_inc/static/images/";

?>

<!DOCTYPE html>
<html lang="fr">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <link rel="stylesheet" href="<?php echo $cssPath; ?>base.css">
  <link rel="stylesheet" href="<?php echo $cssPath; ?>header.css">
  <link rel="stylesheet" href="<?php echo $cssPath; ?>styles.css">
  <link rel="stylesheet" href="<?php echo $cssPath; ?>style_login.css">
  <title>IUTables'O - Connexion</title>
</head>
<body>
  <?php include_once '../_inc/templates/header.php'; ?>
  <main>
    <h1>Quel plaisir de vous revoir 🍔</h1>
    <?php if (isset($_GET['error']) && $_GET['error'] == 1): ?>
        <div class="error-message">
            Email ou mot de passe incorrect.
        </div>
    <?php endif; ?>
    <form action="/index.php?action=login" method="post">
      <label for="email">Adresse e-mail</label>
      <input type="email" name="email" id="email" required>
      <label for="password">Mot de passe</label>
      <input type="password" name="password" id="password" required>
      <button type="submit">Se connecter</button>
    </form>
    <!-- Ligne horizontale personnalisée -->
    <hr class="separator">
    <h2>Pas encore de compte ?</h2>
    <a href="/vues/register.php"><button>Inscrivez-vous</button></a>
  </main>
</body>
</html>
