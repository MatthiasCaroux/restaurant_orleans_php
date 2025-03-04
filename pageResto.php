<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Fichier pour la connexion à la bd
require_once "_inc/bd/db.php";

// Récupération de l'ID du restaurant depuis l'URL
$id_restaurant = isset($_GET['id']) ? intval($_GET['id']) : 0;

// Vérification si l'ID est valide
if ($id_restaurant <= 0) {
    die("ID de restaurant invalide");
}

try {
    $pdo = getPDO();
    // Requête SQL pour récupérer les détails du restaurant
    $sql = 'SELECT * FROM "Restaurant" WHERE id_restaurant = :id';
    $stmt = $pdo->prepare($sql);
    $stmt->execute(['id' => $id_restaurant]);
    $restaurant = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$restaurant) {
        die("Restaurant non trouvé");
    }
} catch (PDOException $e) {
    die("Erreur lors de la récupération du restaurant : " . $e->getMessage());
}

// Chemin vers vos fichiers statiques
$cssPath = "_inc/static/";
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title><?php echo htmlspecialchars($restaurant['nom_restaurant']); ?></title>
    <link rel="stylesheet" href="<?php echo $cssPath; ?>style.css">
</head>
<body>
    <main class="restaurant-details">
        <a href="index.php" class="retour-btn">Retour à la liste</a>
        
        <div class="restaurant-header">
            <img src="<?php echo $cssPath; ?>bk.jpeg" alt="Photo du restaurant" class="restaurant-image">
            <h1><?php echo htmlspecialchars($restaurant['nom_restaurant']); ?></h1>
        </div>

        <div class="restaurant-info-details">
            <div class="info-section">
                <h2>Informations</h2>
                <p>
                    <strong>Adresse :</strong> 
                    <?php echo htmlspecialchars($restaurant['commune']) . ' - ' . htmlspecialchars($restaurant['departement']); ?>
                </p>
                
                <?php if(!empty($restaurant['telephone_restaurant'])): ?>
                    <p>
                        <strong>Téléphone :</strong> 
                        <?php echo htmlspecialchars($restaurant['telephone_restaurant']); ?>
                    </p>
                <?php endif; ?>

                <?php if(!empty($restaurant['email_restaurant'])): ?>
                    <p>
                        <strong>Email :</strong> 
                        <?php echo htmlspecialchars($restaurant['email_restaurant']); ?>
                    </p>
                <?php endif; ?>
            </div>

            <?php if(!empty($restaurant['description_restaurant'])): ?>
                <div class="description-section">
                    <h2>Description</h2>
                    <p><?php echo nl2br(htmlspecialchars($restaurant['description_restaurant'])); ?></p>
                </div>
            <?php endif; ?>
        </div>
    </main>
</body>
</html>