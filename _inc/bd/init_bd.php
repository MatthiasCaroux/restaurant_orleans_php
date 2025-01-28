<?php
require 'db.php'; 

$pdo = getPDO();

// Tester la connexion en récupérant les noms des joueurs
$sql = "SELECT player_name FROM JOUEURS";
$stmt = $pdo->query($sql);

$joueurs = $stmt->fetchAll(PDO::FETCH_ASSOC);

echo "Liste des joueurs :<br>";
foreach ($joueurs as $joueur) {
    echo htmlspecialchars($joueur['player_name']) . "<br>";
}
?>
