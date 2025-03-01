<?php
// Vérifier si la fonction existe déjà pour éviter les redéclarations
if (!function_exists('getPDO')) {
    function getPDO()
    {
        $host = 'aws-0-us-west-1.pooler.supabase.com'; // Hôte pour le Transaction Pooler
        $port = '6543'; // Port pour le Transaction Pooler
        $dbname = 'postgres'; // Nom de la base (par défaut "postgres")
        $user = 'postgres.lmlcsjxhreswvnrdvhpp'; // Nom d'utilisateur (ajoute ton identifiant unique)
        $password = 'faitleloup'; // Remplace par ton mot de passe Supabase

        try {
            // Configuration de la chaîne de connexion
            $dsn = "pgsql:host=$host;port=$port;dbname=$dbname;sslmode=require";
            $pdo = new PDO($dsn, $user, $password);

            // Configurer PDO pour lever des exceptions en cas d'erreur
            $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

            return $pdo;
        } catch (PDOException $e) {
            // Gestion des erreurs
            die("Erreur de connexion à la base de données : " . $e->getMessage());
        }
    }
}

// Supprimons cet appel direct qui peut causer des problèmes
// getPDO();
?>
