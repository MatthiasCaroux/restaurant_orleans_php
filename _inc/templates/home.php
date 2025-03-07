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
                <option value="<?php echo htmlspecialchars(strtolower($type)); ?>"><?php echo htmlspecialchars($type); ?></option>
            <?php endforeach; ?>
        </select>
        <div class="filters-container">
            <div class="filter-toggle">
                <label class="switch">
                    <input type="checkbox" id="filter-vegetarian">
                    <span class="slider round"></span>
                </label>
                <span class="filter-label">
                    <i class="fas fa-leaf"></i> Végétarien
                </span>
            </div>
            <div class="filter-toggle">
                <label class="switch">
                    <input type="checkbox" id="filter-wheelchair">
                    <span class="slider round"></span>
                </label>
                <span class="filter-label">
                    <i class="fas fa-wheelchair"></i> Accès PMR
                </span>
            </div>
        </div>
        <section>
            <div class="restaurant-container">
                <?php if(!empty($restaurants)): ?>
                    <?php foreach($restaurants as $restaurant): ?>
                        <a href="./vues/pageResto.php?id=<?php echo $restaurant['id_restaurant']; ?>" class="restaurant-link">
                            <div class="restaurant">
                                <div class="restaurant-info">
                                    <?php
                                        $restaurant_name = $restaurant['nom_restaurant'];
                                        $default_image = $imagesPath . 'default-restaurant.jpg';
                                        $image_path = $imagesPath . 'restaurants_images/' . $restaurant_name . '.jpg';
                                        $image_url = file_exists($image_path) ? $image_path : $default_image;
                                    ?>
                                    <img src="<?php echo $image_url; ?>" alt="Photo de <?php echo htmlspecialchars($restaurant_name); ?>">
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
                                    <div class="restaurant-attributes">
                                        <?php if($restaurant['vegetarian'] === 'yes'): ?>
                                            <i class="fas fa-leaf" title="Options végétariennes disponibles"></i>
                                        <?php endif; ?>
                                        <?php if($restaurant['wheelchair'] === 'yes'): ?>
                                            <i class="fas fa-wheelchair" title="Accessible aux PMR"></i>
                                        <?php endif; ?>
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
