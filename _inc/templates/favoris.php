<?php

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

require_once __DIR__ . "/../bd/db.php";

// Récupération des restaurants depuis la table "Restaurant"
try {
    $pdo = getPDO();
    // Requête SQL pour récupérer tous les restaurants
    $sql = 'SELECT * FROM "Restaurant"';
    $stmt = $pdo->query($sql);
    $restaurants = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    die("Erreur lors de la récupération des restaurants : " . $e->getMessage());
}

// Chemin vers vos fichiers statiques (CSS, images, etc.)
$cssPath = "_inc/static/";
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Liste des Restaurants</title>
    <link rel="stylesheet" href="<?php echo $cssPath; ?>style.css">
</head>
<body>
    <main class="home">
        <div class="search-container">
            <input type="text" placeholder="Rechercher un restaurant, un hôtel..." />
            <button>
                <img src="<?php echo $cssPath; ?>loupe.png" alt="Logo">
            </button>
        </div>
        <p class="search-info">
            Trouvez des restaurants, hôtels et bien plus encore, près de chez vous ou n'importe où dans le monde.
        </p>
        <section>
            <div class="restaurant-container">
                <?php if(!empty($restaurants)): ?>
                    <?php foreach($restaurants as $restaurant): ?>
                        <div class="restaurant">
                            <div class="restaurant-info">
                                <!-- Photo du restaurant -->
                                <img src="<?php echo $cssPath; ?>bk.jpeg" alt="Logo de bk en mode">
                                <div>
                                    <!-- Nom du restaurant -->
                                    <h2><?php echo htmlspecialchars($restaurant['nom_restaurant']); ?></h2>
                                    
                                    <!-- Adresse (commune et département) -->
                                    <p>
                                        <?php 
                                            echo htmlspecialchars($restaurant['commune']) . ' - ' . htmlspecialchars($restaurant['departement']);
                                        ?>
                                    </p>
                                    
                                    <!-- Site web si disponible -->
                                    <?php if(!empty($restaurant['site_restaurant'])): ?>
                                        <p>
                                            <a href="<?php echo htmlspecialchars($restaurant['site_restaurant']); ?>" target="_blank">
                                                Visiter le site
                                            </a>
                                        </p>
                                    <?php endif; ?>
                                    
                                    <!-- Téléphone -->
                                    <?php if(!empty($restaurant['telephone_restaurant'])): ?>
                                        <p><?php echo htmlspecialchars($restaurant['telephone_restaurant']); ?></p>
                                    <?php endif; ?>
                                </div>
                            </div>
                            <!-- Bouton coeur placé à droite -->
                            <button class="heart-btn">
                                <img src="<?php echo $cssPath; ?>coeur.svg" alt="Ajouter aux favoris">
                            </button>
                        </div>
                    <?php endforeach; ?>
                <?php else: ?>
                    <p>Aucun restaurant trouvé.</p>
                <?php endif; ?>
            </div>
        </section>
    </main>
</body>
</html>
