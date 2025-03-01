<?php
// Activer l'affichage des erreurs pour le débogage
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

require_once '_inc/bd/db.php';
require_once '_inc/auth/auth.php';

echo "<h1>Test de connexion à la base de données</h1>";

try {
    $pdo = getPDO();
    echo "<p style='color:green'>Connexion à la base de données réussie !</p>";
    
    // Vérifier si la table Utilisateur existe
    $stmt = $pdo->query('SELECT COUNT(*) FROM "Utilisateur"');
    $count = $stmt->fetchColumn();
    echo "<p>Nombre d'utilisateurs dans la base : $count</p>";
    
    // Afficher les utilisateurs existants
    echo "<h2>Liste des utilisateurs</h2>";
    $stmt = $pdo->query('SELECT id, username FROM "Utilisateur"');
    echo "<ul>";
    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        echo "<li>ID: {$row['id']} - Username: {$row['username']}</li>";
    }
    echo "</ul>";
    
    // Formulaire de test d'authentification
    echo "<h2>Test d'authentification</h2>";
    echo "<form method='post'>";
    echo "<label>Email: <input type='text' name='email'></label><br>";
    echo "<label>Mot de passe: <input type='password' name='password'></label><br>";
    echo "<button type='submit' name='test_auth'>Tester l'authentification</button>";
    echo "</form>";
    
    // Traiter le formulaire de test
    if (isset($_POST['test_auth'])) {
        $email = $_POST['email'];
        $password = $_POST['password'];
        
        echo "<h3>Résultat du test</h3>";
        echo "<p>Email: $email</p>";
        
        $result = authenticateUser($email, $password);
        if ($result) {
            echo "<p style='color:green'>Authentification réussie !</p>";
            echo "<p>Session ID: " . session_id() . "</p>";
            echo "<p>Variables de session:</p>";
            echo "<pre>";
            print_r($_SESSION);
            echo "</pre>";
        } else {
            echo "<p style='color:red'>Échec de l'authentification.</p>";
        }
    }
    
} catch (PDOException $e) {
    echo "<p style='color:red'>Erreur de connexion à la base de données : " . $e->getMessage() . "</p>";
}
?> 