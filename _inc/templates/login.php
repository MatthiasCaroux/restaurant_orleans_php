<?php
require 'Classes/Autoloader.php';
Autoloader::register();
?>

<main>
    <h1>Quel plaisir de vous revoir</h1>
    <form action="index.php?action=login" method="post">
        <label for="email">Adresse e-mail</label>
        <input type="email" name="email" id="email" required>
        <label for="password">Mot de passe</label>
        <input type="password" name="password" id="password" required>
        <button type="submit">Se connecter</button>
    </form>