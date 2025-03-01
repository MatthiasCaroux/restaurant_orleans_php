<?php
require_once 'Classes/Autoloader.php';
Autoloader::register();

// Démarrer la session
session_start();

// Si l'utilisateur est déjà connecté, le rediriger vers l'index
if (isset($_SESSION['user_id'])) {
    header('Location: index.php');
    exit();
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <link rel="stylesheet" href="./_inc/static/styles.css">
  <!-- On peut créer un fichier CSS spécifique pour la page d'inscription -->
  <link rel="stylesheet" href="./_inc/static/style_register.css">
  <title>IUTables'O - Inscription</title>
</head>
<body>
  <?php include_once '_inc/templates/header.php'; ?>
  <main>
    <h1>Créez votre compte</h1>
    
    <?php if (isset($_GET['error'])): ?>
        <div class="error-message">
            <?php
            switch ($_GET['error']) {
                case 'passwords_mismatch':
                    echo "Les mots de passe ne correspondent pas.";
                    break;
                case 'email_exists':
                    echo "Cette adresse email est déjà utilisée.";
                    break;
                default:
                    echo "Une erreur est survenue lors de l'inscription.";
            }
            ?>
        </div>
    <?php endif; ?>

    <form action="index.php?action=register" method="post">
      <label for="username">Nom d'utilisateur</label>
      <input type="text" name="username" id="username" required>

      <label for="email">Adresse e-mail</label>
      <input type="email" name="email" id="email" required>

      <label for="password">Mot de passe</label>
      <input type="password" name="password" id="password" required>

      <label for="password_confirm">Confirmez le mot de passe</label>
      <input type="password" name="password_confirm" id="password_confirm" required>

      <button type="submit">S'inscrire</button>
    </form>

    <!-- Ligne horizontale personnalisée -->
    <hr class="separator">

    <h2>Vous avez déjà un compte ?</h2>
    <a href="login.php"><button>Se connecter</button></a>
  </main>
</body>
</html>
