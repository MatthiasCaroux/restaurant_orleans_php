<?php
session_start(); // Assurons-nous que la session est bien démarrée
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

require_once "_inc/bd/restaurant_queries.php";

// Charger le fichier JSON des images
$imagesJson = file_get_contents('_inc/data/restaurant_images.json');
$imagesData = json_decode($imagesJson, true);

// Vérifier si l'utilisateur est connecté
$isLoggedIn = isset($_SESSION['user_id']);
$user_id = $isLoggedIn ? $_SESSION['user_id'] : null;

// Récupération de l'ID du restaurant depuis l'URL
$id_restaurant = isset($_GET['id']) ? intval($_GET['id']) : 0;

// Vérification si l'ID du restaurant est valide
if ($id_restaurant <= 0) {
    die("ID de restaurant invalide");
}

// Traitement du formulaire de favoris
if ($isLoggedIn && $_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['favorite_action'], $_POST['id_restaurant'])) {
    $restaurant_id = intval($_POST['id_restaurant']);
    
    if ($_POST['favorite_action'] === 'add') {
        $success = addRestaurantToFavorites($user_id, $restaurant_id);
    } elseif ($_POST['favorite_action'] === 'remove') {
        $success = removeRestaurantFromFavorites($user_id, $restaurant_id);
    }

    // Debugging : Vérifie si la requête s'exécute bien
    if (!$success) {
        error_log("Échec de l'ajout/suppression du favori pour user_id={$user_id}, restaurant_id={$restaurant_id}");
    }

    // Redirection pour éviter la soumission multiple du formulaire
    header("Location: pageResto.php?id=" . $restaurant_id);
    exit;
}

// Traitement du formulaire de j'aime
if ($isLoggedIn && $_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['like_action'], $_POST['id_restaurant'])) {
    $restaurant_id = intval($_POST['id_restaurant']);
    $success = addRestaurantToLiked($user_id, $restaurant_id);

    // Pour éviter que l'état ne reste inchangé dans l'affichage, on redirige après traitement
    if (!$success) {
        error_log("Erreur lors de l'ajout du j'aime pour user_id={$user_id}, restaurant_id={$restaurant_id}");
    }
    header("Location: pageResto.php?id=" . $restaurant_id);
    exit;
}



// Récupération des détails du restaurant
$restaurant = getRestaurantById($id_restaurant);
if (!$restaurant) {
    die("Restaurant non trouvé");
}

// Trouver l'image correspondante dans le JSON
$restaurantImage = null;
foreach ($imagesData as $image) {
    if ($image['name'] === $restaurant['nom_restaurant']) {
        $restaurantImage = $image['image_url'];
        break;
    }
}

// Vérifier si le restaurant est en favoris
$isFavorite = $isLoggedIn ? isRestaurantFavorite($user_id, $id_restaurant) : false;

// Vérifier si l'utilisateur a aimé le restaurant
$isLiked = $isLoggedIn ? isRestaurantLiked($user_id, $id_restaurant) : false;

// Chemin vers vos fichiers statiques
$cssPath = "_inc/static/";
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title><?php echo htmlspecialchars($restaurant['nom_restaurant']); ?></title>
    <link rel="stylesheet" href="<?php echo $cssPath; ?>styles/base.css">
    <link rel="stylesheet" href="<?php echo $cssPath; ?>styles/header.css">
    <link rel="stylesheet" href="<?php echo $cssPath; ?>styles/restaurant.css">
    <link rel="stylesheet" href="<?php echo $cssPath; ?>styles/buttons.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>
<body>
    <?php include_once '_inc/templates/header.php'; ?>
    <main class="restaurant-details">
        <div class="header-actions">
            <a href="index.php" class="retour-btn">Retour à la liste</a>
            <?php if ($isLoggedIn): ?>
                <!-- mettre en favoris -->
                <form method="POST" class="favorite-form">
                    <input type="hidden" name="favorite_action" value="<?php echo $isFavorite ? 'remove' : 'add'; ?>">
                    <input type="hidden" name="id_restaurant" value="<?php echo $id_restaurant; ?>">
                    <button type="submit" class="btn-favorite <?php echo $isFavorite ? 'is-favorite' : ''; ?>">
                        <?php echo $isFavorite ? 'Retirer des favoris' : 'Ajouter aux favoris'; ?>
                    </button>
                </form>
                <!-- mettre une note -->
                <form method="POST" class="note-form">
                    <input type="number" name="note_restaurant" id="note_restaurant" min="0" max="5" step="0.1">
                    <button type="submit">Noter</button>
                </form>
                <!-- mettre un avis -->
                <form method="POST" class="avis-form">
                    <input type="text" name="avis" id="avis">
                    <button type="submit">Mettre un avis</button>
                    <input type="hidden" name="id_restaurant" value="<?php echo $id_restaurant; ?>">
                </form>
                <!-- mettre un j'aime -->
                <form method="POST" class="like-form">
                    <input type="hidden" name="like_action" value="like">
                    <input type="hidden" name="id_restaurant" value="<?php echo $id_restaurant; ?>">
                    <button type="submit">
                        <?php echo $isLiked ? 'J\'aime plus 👎' : 'J\'aime 👍'; ?>
                    </button>
                </form>
            <?php endif; ?>
            
        </div>
        
        <div class="restaurant-header">
            <img src="<?php echo $restaurantImage ?: $cssPath . 'default-restaurant.jpg'; ?>" alt="Photo du restaurant" class="restaurant-image">
            <h1><?php echo htmlspecialchars($restaurant['nom_restaurant']); ?></h1>
        </div>

        <div class="restaurant-info-details">
            <div class="info-section">
                <h2>Informations</h2>
                <p>
                    <strong>Adresse :</strong> 
                    <?php echo htmlspecialchars($restaurant['commune']) . ' - ' . htmlspecialchars($restaurant['departement']); ?>
                </p>
                
                <?php if (!empty($restaurant['telephone_restaurant'])): ?>
                    <p>
                        <strong>Téléphone :</strong> 
                        <?php echo htmlspecialchars($restaurant['telephone_restaurant']); ?>
                    </p>
                <?php endif; ?>

                <?php if (!empty($restaurant['email_restaurant'])): ?>
                    <p>
                        <strong>Email :</strong> 
                        <?php echo htmlspecialchars($restaurant['email_restaurant']); ?>
                    </p>
                <?php endif; ?>

                <?php if (!empty($restaurant['site_restaurant'])): ?>
                    <p>
                        <strong>Site web :</strong> 
                        <a href="<?php echo htmlspecialchars($restaurant['site_restaurant']); ?>" target="_blank" rel="noopener noreferrer">
                            Visiter le site web
                        </a>
                    </p>
                <?php endif; ?>
            </div>

            <?php if (!empty($restaurant['description_restaurant'])): ?>
                <div class="description-section">
                    <h2>Description</h2>
                    <p><?php echo nl2br(htmlspecialchars($restaurant['description_restaurant'])); ?></p>
                </div>
            <?php endif; ?>
        </div>
    </main>
</body>
</html>
