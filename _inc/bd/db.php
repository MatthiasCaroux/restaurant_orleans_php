<?php
// Variables globales pour le mock en mode test
$testMode = false;
$mockPdoToUse = null;

/**
 * Obtient une connexion PDO à la base de données
 * @return PDO Instance de PDO connectée à la base de données
 */
function getPDO() {
    global $testMode, $mockPdoToUse;
    
    // Si en mode test et qu'un mock PDO est défini, on l'utilise
    if ($testMode && $mockPdoToUse !== null) {
        return $mockPdoToUse;
    }
    
    // Sinon, connexion normale à la base de données
    static $pdo = null;
    
    if ($pdo === null) {
        try {
            $host = 'aws-0-us-west-1.pooler.supabase.com'; // Hôte pour le Transaction Pooler
            $port = '6543'; // Port pour le Transaction Pooler
            $dbname = 'postgres'; // Nom de la base (par défaut "postgres")
            $user = 'postgres.lmlcsjxhreswvnrdvhpp'; // Nom d'utilisateur 
            $password = 'faitleloup'; // Mot de passe Supabase

            $dsn = "pgsql:host=$host;port=$port;dbname=$dbname;sslmode=require";
            $pdo = new PDO($dsn, $user, $password);
            $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        } catch (PDOException $e) {
            error_log("Erreur de connexion à la base de données : " . $e->getMessage());
            throw $e;
        }
    }
    
    return $pdo;
}