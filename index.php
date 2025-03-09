<?php
require_once 'Classes/Autoloader.php';
Autoloader::register();

require_once '_inc/auth/auth.php';

// Activer l'affichage des erreurs pour le débogage
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Vérifier si on a une action de connexion ou d'inscription
if (isset($_GET['action'])) {
    switch ($_GET['action']) {
        case 'login':
            if (isset($_POST['email']) && isset($_POST['password'])) {
                $email = $_POST['email'];
                $password = $_POST['password'];
                
                // log pour savoir si la connexion est réussie ou non
                error_log("Tentative de connexion pour l'email: " . $email);
                
                if (authenticateUser($email, $password)) {
                    // Redirection vers la page index si la connexion réussie 
                    header('Location: index.php');
                    exit();
                } else {
                    // Redirection vers la page de connexion avec un message d'erreur
                    error_log("Échec de connexion pour l'email: " . $email);
                    header('Location: vues/login.php?error=1');
                    exit();
                }
            }
            break;

        case 'register':
            if (isset($_POST['email']) && isset($_POST['password']) && isset($_POST['password_confirm'])) {
                $username = $_POST['username'];
                $email = $_POST['email'];
                $password = $_POST['password'];
                $password_confirm = $_POST['password_confirm'];

                // Vérifier que les mots de passe correspondent
                if ($password !== $password_confirm) {
                    header('Location: vues/register.php?error=passwords_mismatch');
                    exit();
                }

                $result = registerUser($username, $email, $password);
                
                if ($result['success']) {
                    // Si l'inscription réussit, on connecte directement l'utilisateur
                    if (authenticateUser($email, $password)) {
                        header('Location: index.php');
                        exit();
                    }
                } else {
                    header('Location: vues/register.php?error=email_exists');
                    exit();
                }
            }
            break;
    }
}

// Si pas d'action, afficher la page d'accueil
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="./_inc/static/styles/styles.css">
    <title>IUTables'O - Accueil</title>
</head>
<body>
    <?php include_once '_inc/templates/header.php'; ?>
    <?php include_once '_inc/templates/home.php'; ?>
</body>
</html>
