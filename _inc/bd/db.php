<?php
function getPDO()
{
    try {
        $pdo = new PDO('sqlite:db.sqlite');        
        // Configurer PDO pour lever des exceptions en cas d'erreur
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
                
        return $pdo;
    } catch (PDOException $e) {
        die("Erreur de connexion à la base de données : " . $e->getMessage());
    }
}
?>