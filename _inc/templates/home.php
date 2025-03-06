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

    // récupérer les différents types de restaurant en bd pour les afficher après
    $sql = 'SELECT DISTINCT "type_restaurant" from "Restaurant"';
    $stmt = $pdo->query($sql);
    $tout_type = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    die("Erreur lors de la récupération des restaurants : " . $e->getMessage());
}

// Chemin vers vos fichiers statiques (CSS, images, etc.)
$cssPath = "_inc/static/";
$isLoggedIn = isset($_SESSION['user_id']);

// Charger le fichier JSON des images
$json_file = file_get_contents(__DIR__ . '/../data/restaurant_images.json');
$restaurant_images = json_decode($json_file, true);

// Créer un tableau associatif pour un accès rapide aux images par nom de restaurant
$image_map = array();
foreach ($restaurant_images as $item) {
    if (isset($item['name']) && isset($item['image_url'])) {
        $image_map[$item['name']] = $item['image_url'];
    }
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Liste des Restaurants</title>
    <link rel="stylesheet" href="<?php echo $cssPath; ?>style_page_resto.css">
    <link rel="stylesheet" href="<?php echo $cssPath; ?>styles.css">
</head>
<body>
    <main class="home">
        <div class="search-container">
            <input type="text" placeholder="Rechercher un restaurant, un hôtel..." />
            <button>
                <img src="<?php echo $cssPath; ?>loupe.png" alt="Logo">
            </button>
        </div>
        <p id="recherche" class="search-info">
            Trouvez des restaurants, hôtels et bien plus encore, près de chez vous ou n'importe où dans le monde.
        </p>
        <select id="recherche_by_type" class="search-info">
            <?php foreach($tout_type as $type): ?>
            <option><?php echo htmlspecialchars($type['type_restaurant']); ?></option>
            <?php endforeach; ?>
        </select>
        <section>
            <div class="restaurant-container">
                <?php if(!empty($restaurants)): ?>
                    <?php foreach($restaurants as $restaurant): ?>
                        <a href="pageResto.php?id=<?php echo $restaurant['id_restaurant']; ?>" class="restaurant-link">
                            <div class="restaurant">
                                <div class="restaurant-info">
                                    <!-- Photo du restaurant -->
                                    <?php
                                        $restaurant_name = $restaurant['nom_restaurant'];
                                        $restaurant_type = $restaurant['type_restaurant'];
                                        $default_image = $cssPath . 'bk.jpeg';
                                        $image_url = isset($image_map[$restaurant_name]) ? $image_map[$restaurant_name] : $default_image;
                                    ?>
                                    <img src="<?php echo htmlspecialchars($image_url); ?>" alt="Photo de <?php echo htmlspecialchars($restaurant_name); ?>">
                                    <div>
                                        <!-- Nom du restaurant -->
                                        <h2><?php echo htmlspecialchars($restaurant['nom_restaurant']); ?></h2>
                                        
                                        <!-- Adresse (commune et département) -->
                                        <p>
                                            <?php 
                                                echo htmlspecialchars($restaurant['commune']) . ' - ' . htmlspecialchars($restaurant['departement']);
                                            ?>
                                        </p>                                    
                                        <!-- Téléphone -->
                                        <?php if(!empty($restaurant['telephone_restaurant'])): ?>
                                            <p><?php echo htmlspecialchars($restaurant['telephone_restaurant']); ?></p>
                                        <?php endif; ?>
                                    </div>
                                    <div class="cacher">
                                        <?php  echo $restaurant['type_restaurant'];?>
                                    </div>
                                </div>
                                <!-- Bouton coeur placé à droite -->
                                <?php if ($isLoggedIn): ?>
                                    <button class="heart-btn">
                                        <img src="<?php echo $cssPath; ?>coeur.svg" alt="Ajouter aux favoris">
                                    </button>
                                <?php endif; ?>
                            </div>
                        </a>
                    <?php endforeach; ?>
                <?php else: ?>
                    <p>Aucun restaurant trouvé.</p>
                <?php endif; ?>
            </div>
        </section>
    </main>
</body>
<script src="_inc/static/js/recherche.js"></script>
</html>