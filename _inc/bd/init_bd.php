<?php
require 'db.php'; 

$pdo = getPDO();

// Création de la table `joueurs` si elle n'existe pas déjà
$sql = "
CREATE TABLE IF NOT EXISTS JOUEURS (
    player_name TEXT PRIMARY KEY,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP
)";

// Création de la table `scores` si elle n'existe pas déjà
$sql = "
CREATE TABLE IF NOT EXISTS SCORES (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    player_name TEXT,
    score DECIMAL(5,2),
    total_questions INTEGER,
    fait_a DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (player_name) REFERENCES JOUEURS(player_name)
)";
$pdo->exec($sql);


echo "Table `scores` créée ou déjà existante dans la base de données.";
?>