<?php
require_once '_inc/bd/db.php';

function authenticateUser($email, $password) {
    try {
        $pdo = getPDO();
        
        // Préparer la requête pour vérifier les identifiants
        $query = "SELECT * FROM \"Utilisateur\" WHERE username = :email AND password = :password";
        $stmt = $pdo->prepare($query);
        
        // Exécuter la requête avec les paramètres
        $stmt->execute([
            'email' => $email,
            'password' => $password
        ]);
        
        // Vérifier si l'utilisateur existe
        $user = $stmt->fetch(PDO::FETCH_ASSOC);
        if ($user) {
            // Vérifier si la session est déjà démarrée
            if (session_status() == PHP_SESSION_NONE) {
                session_start();
            }
            
            // Stocker les informations de l'utilisateur
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['email'] = $user['username'];
            
            return true;
        }
        
        return false;
    } catch (PDOException $e) {
        // Gérer l'erreur
        error_log("Erreur d'authentification : " . $e->getMessage());
        return false;
    }
}

function registerUser($username, $email, $password) {
    try {
        $pdo = getPDO();
        
        // Vérifier si l'email existe déjà
        $checkQuery = "SELECT COUNT(*) FROM \"Utilisateur\" WHERE username = :email";
        $checkStmt = $pdo->prepare($checkQuery);
        $checkStmt->execute(['email' => $email]);
        
        if ($checkStmt->fetchColumn() > 0) {
            return ['success' => false, 'message' => 'Cet email est déjà utilisé'];
        }
        
        // Insérer le nouvel utilisateur
        $query = "INSERT INTO \"Utilisateur\" (username, password) VALUES (:email, :password)";
        $stmt = $pdo->prepare($query);
        
        $success = $stmt->execute([
            'email' => $email,
            'password' => $password
        ]);
        
        if ($success) {
            return ['success' => true, 'message' => 'Inscription réussie'];
        } else {
            return ['success' => false, 'message' => 'Erreur lors de l\'inscription'];
        }
        
    } catch (PDOException $e) {
        error_log("Erreur d'inscription : " . $e->getMessage());
        return ['success' => false, 'message' => 'Une erreur est survenue'];
    }
}
?> 