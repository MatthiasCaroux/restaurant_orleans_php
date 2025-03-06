<?php

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

require_once __DIR__ . "/../bd/page_restaurant_bd.php";

// Récupération des restaurants
$restaurants = getAllRestaurants();

// Chemin vers vos fichiers statiques (CSS, images, etc.)
$cssPath = "./_inc/static/styles/";
$imagesPath = "./_inc/static/images/";
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

// Récupérer tous les types de restaurant de façon unique et les trier
$tout_type = array_unique(array_column($restaurants, 'type_restaurant'));
sort($tout_type); // Trie alphabétiquement
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Liste des Restaurants</title>
    <link rel="stylesheet" href="<?php echo $cssPath; ?>base.css">
    <link rel="stylesheet" href="<?php echo $cssPath; ?>header.css">
    <link rel="stylesheet" href="<?php echo $cssPath; ?>home.css">
</head>
<body>
    <main class="home">
        <div class="search-container">
            <input type="text" placeholder="Rechercher un restaurant, un hôtel..." />
            <button>
                <img src="<?php echo $imagesPath; ?>loupe.png" alt="Logo">
            </button>
        </div>
        <p id="recherche" class="search-info">
            Trouvez des restaurants, hôtels et bien plus encore, près de chez vous ou n'importe où dans le monde.
        </p>
        <select id="recherche_by_type" class="search-info">
            <!-- Option "Tous" pour afficher tous les types -->
            <option value="tout">Tous</option>
            <?php foreach($tout_type as $type): ?>
                <option><?php echo htmlspecialchars($type); ?></option>
            <?php endforeach; ?>
        </select>
        <section>
            <div class="restaurant-container">
                <?php if(!empty($restaurants)): ?>
                    <?php foreach($restaurants as $restaurant): ?>
                        <a href="./vues/pageResto.php?id=<?php echo $restaurant['id_restaurant']; ?>" class="restaurant-link">
                            <div class="restaurant">
                                <div class="restaurant-info">
                                    <?php
                                        $restaurant_name = $restaurant['nom_restaurant'];
                                        $default_image = $imagesPath . 'bk.jpeg';
                                        $image_url = isset($image_map[$restaurant_name]) ? $image_map[$restaurant_name] : $default_image;
                                    ?>
                                    <img src="<?php echo htmlspecialchars($image_url); ?>" alt="Photo de <?php echo htmlspecialchars($restaurant_name); ?>">
                                    <div>
                                        <h2><?php echo htmlspecialchars($restaurant['nom_restaurant']); ?></h2>
                                        <p>
                                            <?php 
                                                echo htmlspecialchars($restaurant['commune']) . ' - ' . htmlspecialchars($restaurant['departement']);
                                            ?>
                                        </p>                                    
                                        <?php if(!empty($restaurant['telephone_restaurant'])): ?>
                                            <p><?php echo htmlspecialchars($restaurant['telephone_restaurant']); ?></p>
                                        <?php endif; ?>
                                    </div>
                                    <div class="cacher">
                                        <?php echo htmlspecialchars($restaurant['type_restaurant']); ?>
                                    </div>
                                </div>
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
