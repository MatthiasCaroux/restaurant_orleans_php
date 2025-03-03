<?php

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

require_once __DIR__ . "/../bd/db.php";

$restaurant = null;

// Vérifie si un ID est passé en URL
if (isset($_GET['id']) && !empty($_GET['id'])) {
    try {
        $pdo = getPDO();
        $sql = 'SELECT nom_restaurant FROM "Restaurant" WHERE id_restaurant = :id';
        $stmt = $pdo->prepare($sql);
        $stmt->execute(['id' => $_GET['id']]);
        $restaurant = $stmt->fetch(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
        die("Erreur lors de la récupération du restaurant : " . $e->getMessage());
    }
}

?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title><?php echo $restaurant ? htmlspecialchars($restaurant['nom_restaurant']) : 'Restaurant inconnu'; ?></title>
    <link rel="stylesheet" href="_inc/static/style.css">
</head>
<body>

    <!-- Barre de navigation (si tu veux la garder) -->
    <?php include "header.php"; ?>

    <main class="page-resto">
        <h1>
            <?php echo $restaurant ? htmlspecialchars($restaurant['nom_restaurant']) : 'Restaurant non trouvé 😢'; ?>
        </h1>
    </main>

</body>
</html>
