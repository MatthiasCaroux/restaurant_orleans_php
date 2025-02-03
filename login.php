<?php
require 'Classes/Autoloader.php';
Autoloader::register();
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="./_inc/static/styles.css">
    <link rel="stylesheet" href="./_inc/static/style_login.css">
    <title>IUTables’O - Accueil</title>
</head>
<body>
    <?php include '_inc/templates/header.php'; ?>
    <main>
        <h1>Quel plaisir de vous revoir 🍔</h1>
        <form action="index.php?action=login" method="post">
            <label for="email">Adresse e-mail</label>
            <input type="email" name="email" id="email" required>
            <label for="password">Mot de passe</label>
            <input type="password" name="password" id="password" required>
            <button type="submit">Se connecter</button>
        </form>
    </main>
</body>
</html>
