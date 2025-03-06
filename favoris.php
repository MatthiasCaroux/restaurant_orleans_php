<?php
session_start();
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

require_once __DIR__ . "/_inc/bd/restaurant_queries.php";


// Vérifier si l'utilisateur est connecté
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

$user_id = $_SESSION['user_id'];

// Récupérer les restaurants favoris depuis `restaurant_queries.php`
$favoris = getFavoritesForUser($user_id);

// Charger le fichier JSON des images pour afficher les bonnes images des restaurants
$imagesJson = file_get_contents('_inc/data/restaurant_images.json');
$imagesData = json_decode($imagesJson, true);

// Fonction pour récupérer l'image d'un restaurant
function getRestaurantImage($restaurantName, $imagesData) {
    foreach ($imagesData as $image) {
        if ($image['name'] === $restaurantName) {
            return $image['image_url'];
        }
    }
    return '_inc/static/default-restaurant.jpg'; // Image par défaut si aucune image trouvée
}

// Chemin vers vos fichiers statiques
$cssPath = "_inc/static/";
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Mes Restaurants Favoris</title>
    <link rel="stylesheet" href="<?php echo $cssPath; ?>styles/base.css">
    <link rel="stylesheet" href="<?php echo $cssPath; ?>styles/header.css">
    <link rel="stylesheet" href="<?php echo $cssPath; ?>styles/favorites.css">
    <link rel="stylesheet" href="<?php echo $cssPath; ?>styles/buttons.css">
</head>
<body>
    <?php include_once '_inc/templates/header.php'; ?>
    
    <main>
        <h1>Mes Restaurants Favoris</h1>
        
        <div class="restaurant-container">
            <?php if (!empty($favoris)): ?>
                <?php foreach ($favoris as $fav): ?>
                    <a href="pageResto.php?id=<?php echo $fav['id_restaurant']; ?>" class="restaurant-link">
                        <div class="restaurant">
                            <div class="restaurant-info">
                                <!-- Récupérer et afficher l'image associée au restaurant -->
                                <img src="<?php echo getRestaurantImage($fav['nom_restaurant'], $imagesData); ?>" alt="Photo du restaurant">
                                <div>
                                    <h2><?php echo htmlspecialchars($fav['nom_restaurant']); ?></h2>
                                    <p>
                                        <?php echo htmlspecialchars($fav['commune']) . ' - ' . htmlspecialchars($fav['departement']); ?>
                                    </p>
                                    
                                    <?php if (!empty($fav['telephone_restaurant'])): ?>
                                        <p><?php echo htmlspecialchars($fav['telephone_restaurant']); ?></p>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                    </a>
                <?php endforeach; ?>
            <?php else: ?>
                <p class="no-favorites">Vous n'avez pas encore de restaurants favoris.</p>
            <?php endif; ?>
        </div>
    </main>
</body>
</html>
