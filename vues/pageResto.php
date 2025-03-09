    <?php
session_start(); // Assurons-nous que la session est bien démarrée
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

require_once "../_inc/bd/page_restaurant_bd.php";

// Charger le fichier JSON des images
$imagesJson = file_get_contents('../_inc/data/restaurant_images.json');
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

require_once "../_inc/bd/avis_bd.php";

$avis = getAvisForRestaurant($id_restaurant);


// Traitement du formulaire d'avis
if ($isLoggedIn && $_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['submit_avis'], $_POST['id_restaurant'])) {
    $restaurant_id = intval($_POST['id_restaurant']);
    $titre = $_POST['titre'];
    $texte = $_POST['texte'];
    $note = $_POST['note'];
    $success = addAvis($titre, $texte, $note, $restaurant_id, $user_id);
    if (!$success) {
        $error_message = "Vous avez déjà déposé un avis pour ce restaurant.";
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
$cssPath = "../_inc/static/";
$imagesPath = "../_inc/static/images/";


// Chemin vers les images
$imagesPath = "../_inc/static/images/";
$restaurant_name = $restaurant['nom_restaurant'];
$default_image = $imagesPath . 'default-restaurant.jpg';
$image_path = $imagesPath . 'restaurants_images/' . $restaurant_name . '.jpg';
$image_url = file_exists($image_path) ? $image_path : $default_image;


?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title><?php echo htmlspecialchars($restaurant['nom_restaurant']); ?></title>
    <link rel="stylesheet" href="<?php echo $cssPath; ?>styles/restaurant.css">
    <link rel="stylesheet" href="<?php echo $cssPath; ?>styles/base.css">
    <link rel="stylesheet" href="<?php echo $cssPath; ?>styles/style_page_resto.css">
    <link rel="stylesheet" href="<?php echo $cssPath; ?>styles/header.css">
    <link rel="stylesheet" href="<?php echo $cssPath; ?>styles/buttons.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <!-- Leaflet qui permet de mettre un plan du restaurant -->
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
</head>
<body>
    <?php include_once '../_inc/templates/header.php'; ?>
    <main class="restaurant-details">
        <div class="header-actions">
            <a href="/index.php" class="retour-btn"><i class="fas fa-arrow-left"></i> Retour à la liste</a>
            <?php if ($isLoggedIn): ?>
                <!-- mettre en favoris -->
                <div class="actions-group">
                    <form method="POST" class="favorite-form">
                        <input type="hidden" name="favorite_action" value="<?php echo $isFavorite ? 'remove' : 'add'; ?>">
                        <input type="hidden" name="id_restaurant" value="<?php echo $id_restaurant; ?>">
                        <button type="submit" class="btn-favorite <?php echo $isFavorite ? 'is-favorite' : ''; ?>">
                            <i class="fas <?php echo $isFavorite ? 'fa-heart' : 'fa-heart'; ?>"></i>
                            <?php echo $isFavorite ? 'Retirer des favoris' : 'Ajouter aux favoris'; ?>
                        </button>
                    </form>
                    
                    <!-- mettre un j'aime -->
                    <form method="POST" class="like-form">
                        <input type="hidden" name="like_action" value="like">
                        <input type="hidden" name="id_restaurant" value="<?php echo $id_restaurant; ?>">
                        <button type="submit" class="btn-like <?php echo $isLiked ? 'is-liked' : ''; ?>">
                            <i class="fas <?php echo $isLiked ? 'fa-thumbs-down' : 'fa-thumbs-up'; ?>"></i>
                            <?php echo $isLiked ? 'Je n\'aime plus' : 'J\'aime'; ?>
                        </button>
                    </form>
                </div>
            <?php endif; ?>
        </div>
        
        <div class="restaurant-header">
            <?php
                $restaurant_name = $restaurant['nom_restaurant'];
                $default_image = $imagesPath . 'default-restaurant.jpg';
                $image_path = $imagesPath . 'restaurants_images/' . $restaurant_name . '.jpg';
                $image_url = file_exists($image_path) ? $image_path : $default_image;
            ?>
            <img src="<?php echo $image_url; ?>" alt="Photo de <?php echo htmlspecialchars($restaurant_name); ?>">
            <h1><?php echo htmlspecialchars($restaurant['nom_restaurant']); ?></h1>
        </div>

        <div class="restaurant-info-card">
            <div class="card-header">
                <h2>Plan</h2>
            </div>
            <div class="card-content map-container">
                <!-- Placeholder for map - you'll need to integrate your actual map here -->
                <div class="map-placeholder" id="map">
                    <script>
                        // Coordonnées centrales de la carte (ici les coordonnées du restaurant)
                        var map = L.map('map').setView([<?php echo $restaurant['latitude']; ?>, <?php echo $restaurant['longitude']; ?>], 13);

                    // Ajouter une couche de tuiles OpenStreetMap
                    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                        attribution: '&copy; OpenStreetMap contributors'
                    }).addTo(map);

                    // Liste des restaurants (latitude, longitude, nom)
                    var restaurants = [
                        { lat: <?php echo $restaurant['latitude']; ?>, lon: <?php echo $restaurant['longitude']; ?>, name: "<?php echo $restaurant['nom_restaurant']; ?>" }
                    ];

                    // Ajouter les marqueurs
                    restaurants.forEach(function(restaurant) {
                        L.marker([restaurant.lat, restaurant.lon])
                            .addTo(map)
                            .bindPopup(`<b>${restaurant.name}</b>`);
                    });
                    </script>
                </div>
                <div class="restaurant-address">
                    <p><i class="fas fa-map-marker-alt"></i> <?php echo htmlspecialchars($restaurant['adresse_restaurant'] ?? ''); ?>, 
                       <?php echo htmlspecialchars($restaurant['commune']) . ' - ' . htmlspecialchars($restaurant['departement']); ?></p>
                </div>
                
                <div class="contact-info">
                    <div class="contact-row">
                        <?php if (!empty($restaurant['site_restaurant'])): ?>
                            <div class="contact-item">
                                <i class="fas fa-globe"></i>
                                <a href="<?php echo htmlspecialchars($restaurant['site_restaurant']); ?>" target="_blank" rel="noopener noreferrer">
                                    Site internet
                                </a>
                            </div>
                        <?php endif; ?>
                        
                        <?php if (!empty($restaurant['email_restaurant'])): ?>
                            <div class="contact-item">
                                <i class="fas fa-envelope"></i>
                                <a href="mailto:<?php echo htmlspecialchars($restaurant['email_restaurant']); ?>">
                                    E-mail
                                </a>
                            </div>
                        <?php endif; ?>

                        <?php if (!empty($restaurant['facebook'])): ?>
                            <div class="contact-item">
                                <i class="fab fa-facebook"></i>
                                <a href="<?php echo htmlspecialchars($restaurant['facebook_restaurant']); ?>" target="_blank" rel="noopener noreferrer">
                                    Facebook
                                </a>
                            </div>
    <?php endif; ?>
                    </div>
                    
                    <div class="contact-row">
                        <?php if (!empty($restaurant['telephone_restaurant'])): ?>
                            <div class="contact-item">
                                <i class="fas fa-phone"></i>
                                <a href="tel:<?php echo htmlspecialchars($restaurant['telephone_restaurant']); ?>">
                                    <?php echo htmlspecialchars($restaurant['telephone_restaurant']); ?>
                                </a>
                            </div>
                        <?php endif; ?>
                        
                        <div class="restaurant-categories">
                            <ul>
                                <li>
                                    <?php
                                        // si le restaurant n'a pas de type de restaurant, on affiche "Type de restaurant non renseigné"
                                        if ($restaurant['type_restaurant']) {
                                            if ($restaurant['type_restaurant']  == 'fast_food') {
                                                echo "Fast food";
                                            }
                                            else if ($restaurant['type_restaurant']  == 'bar') {
                                                echo "Bar";
                                            }
                                            else if ($restaurant['type_restaurant']  == 'ice_cream') {
                                                echo "Ice cream";
                                            }
                                            else if ($restaurant['type_restaurant']  == 'pub') {
                                                echo "Pub";
                                            }
                                            else if ($restaurant['type_restaurant']  == 'restaurant') {
                                                echo "Restaurant";
                                            }
                                        }
                                        else {
                                            echo "Type de restaurant non renseigné";
                                        }
                                    ?>
                                </li>
                                <li>Département</li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <?php if (!empty($restaurant['description_restaurant'])): ?>
            <div class="restaurant-info-card">
                <div class="card-header">
                    <h2>Description</h2>
                </div>
                <div class="card-content">
                    <p><?php echo nl2br(htmlspecialchars($restaurant['description_restaurant'])); ?></p>
                </div>
            </div>
        <?php endif; ?>

        <!-- Section Avis -->
        <div class="restaurant-info-card">
            <div class="card-header">
                <h2>Avis</h2>
                <div class="rating-summary">
                    <div class="average-rating">
                        <span class="rating-number">
                            <?php echo number_format(getNoteMoyenneRestaurant($id_restaurant) ?? 0, 1); ?>
                        </span>
                        <div class="rating-stars">
                            <?php 
                            $avgRating = getNoteMoyenneRestaurant($id_restaurant) ?? 0;
                            for($i = 1; $i <= 5; $i++) {
                                if($i <= $avgRating) {
                                    echo '<i class="fas fa-star filled"></i>';
                                } elseif($i - 0.5 <= $avgRating) {
                                    echo '<i class="fas fa-star-half-alt filled"></i>';
                                } else {
                                    echo '<i class="far fa-star"></i>';
                                }
                            }
                            ?>
                        </div>
                    </div>
                    <span class="review-count"><?php echo getNombreAvisRestaurant($id_restaurant) ?? 0; ?> avis</span>
                </div>
            </div>
            
            <div class="card-content">
                <?php if (!empty($avis) && is_array($avis)): ?>
                    <div class="avis-container">
                        <?php foreach($avis as $unAvis): ?>
                            <div class="avis-item">
                                <div class="avis-header">
                                    <div class="user-info">
                                        <h3><?php echo htmlspecialchars(explode('@', $unAvis['username'] ?? 'Utilisateur')[0]); ?></h3>
                                        <div class="user-rating">
                                            <?php 
                                            $rating = $unAvis['note'] ?? 0;
                                            for($i = 1; $i <= 5; $i++) {
                                                echo $i <= $rating 
                                                    ? '<i class="fas fa-star filled"></i>' 
                                                    : '<i class="far fa-star"></i>';
                                            }
                                            ?>
                                        </div>
                                    </div>
                                    <span class="avis-date">ID Avis: <?php echo htmlspecialchars($unAvis['id_Avis'] ?? ''); ?></span>
                                </div>
                                <div class="avis-content">
                                    <h4><?php echo htmlspecialchars($unAvis['Titre'] ?? ''); ?></h4>
                                    <p><?php echo htmlspecialchars($unAvis['text'] ?? ''); ?></p>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php else: ?>
                    <p class="no-avis">Aucun avis pour le moment.</p>
                <?php endif; ?>
                
                <?php 
        // Vérifier si l'utilisateur a déjà laissé un avis
        $hasLeftReview = false;
        if (isset($_SESSION['username'])) {
            foreach ($avis as $unAvis) {
                if (isset($unAvis['username']) && $unAvis['username'] === $_SESSION['username']) {
                $hasLeftReview = true;
                    break;
                }
            }
        }
        ?>
        <?php if ($isLoggedIn && !$hasLeftReview): ?>
                    <div class="add-avis-section">
                        <button class="btn-add-avis" id="toggleAvisForm">Ajouter un avis</button>
                        
                        <div class="avis-form-container" id="avisFormContainer" style="display: none;">
                            <form method="POST" action="pageResto.php?id=<?php echo $id_restaurant; ?>" class="avis-form">
                                <div class="form-group">
                                    <label for="titre">Titre de votre avis :</label>
                                    <input type="text" name="titre" id="titre" required>
                                </div>

                                <div class="form-group">
                                    <label for="texte">Votre commentaire :</label>
                                    <textarea name="texte" id="texte" rows="5" required></textarea>
                                </div>

                                <div class="form-group">
                                    <label for="note">Votre note :</label>
                                    <div class="rating-input">
                                        <?php for ($i = 5; $i >= 1; $i--): ?>
                                            <input type="radio" id="star<?php echo $i; ?>" name="note" value="<?php echo $i; ?>" required>
                                            <label for="star<?php echo $i; ?>">
                                                <i class="fas fa-star"></i>
                                            </label>
                                        <?php endfor; ?>
                                    </div>
                                </div>

                                <input type="hidden" name="id_restaurant" value="<?php echo $id_restaurant; ?>">
                                <button type="submit" name="submit_avis" class="btn-submit-avis">Publier votre avis</button>
                            </form>
                        </div>
                    </div>
                <?php endif; ?>
            </div>
        </div>



        <script>
            document.addEventListener('DOMContentLoaded', function() {
                const toggleButton = document.getElementById('toggleAvisForm');
                const formContainer = document.getElementById('avisFormContainer');
                
                if (toggleButton && formContainer) {
                    toggleButton.addEventListener('click', function() {
                        formContainer.classList.toggle('visible');
                        toggleButton.textContent = formContainer.classList.contains('visible') ? 'Annuler' : 'Ajouter un avis';
                    });
                }

                // Script pour les étoiles interactives dans le formulaire d'avis
                const inputs = document.querySelectorAll('.rating-input input');
                const labels = document.querySelectorAll('.rating-input label i');

                inputs.forEach((input, index) => {
                    input.addEventListener('change', function() {
                        labels.forEach((label, labelIndex) => {
                            label.classList.toggle('filled', labelIndex < (5 - index));
                        });
                    });
                });
            });
        </script>

        <style>
            .avis-form-container {
                display: none;
                transition: opacity 0.3s ease-in-out;
            }
            .avis-form-container.visible {
                display: block;
                opacity: 1;
            }
        </style>

    </main>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const toggleButton = document.getElementById('toggleAvisForm');
            const formContainer = document.getElementById('avisFormContainer');
            
            if (toggleButton && formContainer) {
                toggleButton.addEventListener('click', function() {
                    if (formContainer.style.display === 'none') {
                        formContainer.style.display = 'block';
                        toggleButton.textContent = 'Annuler';
                    } else {
                        formContainer.style.display = 'none';
                        toggleButton.textContent = 'Ajouter un avis';
                    }
                });
            }
            
            // Script pour les étoiles interactives dans le formulaire d'avis
            const ratingInputs = document.querySelectorAll('.rating-input input');
            const ratingLabels = document.querySelectorAll('.rating-input label i');
            
            ratingInputs.forEach((input, index) => {
                input.addEventListener('change', function() {
                    ratingLabels.forEach((label, labelIndex) => {
                        if (labelIndex <= index) {
                            label.classList.remove('far');
                            label.classList.add('fas', 'filled');
                        } else {
                            label.classList.remove('fas', 'filled');
                            label.classList.add('far');
                        }
                    });
                });
            });
        });
    </script>
</body>
</html>
