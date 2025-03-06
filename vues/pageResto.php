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
    <?php include_once '../_inc/templates/header.php'; ?>
    <main class="restaurant-details">
        <div class="header-actions">
            <a href="index.php" class="retour-btn"><i class="fas fa-arrow-left"></i> Retour à la liste</a>
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
            <img src="<?php echo $restaurantImage ?: $imagesPath . 'bk.jpeg'; ?>" alt="Photo du restaurant" class="restaurant-image">
            <h1><?php echo htmlspecialchars($restaurant['nom_restaurant']); ?></h1>
        </div>

        <div class="restaurant-info-card">
            <div class="card-header">
                <h2>Plan</h2>
            </div>
            <div class="card-content map-container">
                <!-- Placeholder for map - you'll need to integrate your actual map here -->
                <div class="map-placeholder">
                    <img src="<?php echo $imagesPath; ?>map-placeholder.jpg" alt="Plan du restaurant" class="map-image">
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
                        <span class="rating-number"><?php echo number_format($restaurant['note_moyenne'] ?? 0, 1); ?></span>
                        <div class="rating-stars">
                            <?php 
                            $avgRating = $restaurant['note_moyenne'] ?? 0;
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
                    <span class="review-count"><?php echo number_format($totalAvis ?? 0); ?> avis</span>
                </div>
            </div>
            
            <div class="card-content">
                <?php if (isset($avis) && is_array($avis) && count($avis) > 0): ?>
                    <div class="avis-container">
                        <?php foreach($avis as $unAvis): ?>
                            <div class="avis-item">
                                <div class="avis-header">
                                    <div class="user-info">
                                        <h3><?php echo htmlspecialchars($unAvis['username'] ?? 'Utilisateur'); ?></h3>
                                        <div class="user-rating">
                                            <?php 
                                            $rating = $unAvis['note'] ?? 0;
                                            for($i = 1; $i <= 5; $i++) {
                                                if($i <= $rating) {
                                                    echo '<i class="fas fa-star filled"></i>';
                                                } else {
                                                    echo '<i class="far fa-star"></i>';
                                                }
                                            }
                                            ?>
                                        </div>
                                    </div>
                                    <span class="avis-date"><?php echo htmlspecialchars($unAvis['date_avis'] ?? ''); ?></span>
                                </div>
                                <div class="avis-content">
                                    <p><?php echo htmlspecialchars($unAvis['commentaire'] ?? ''); ?></p>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php else: ?>
                    <p class="no-avis">Aucun avis pour le moment.</p>
                <?php endif; ?>
                
                <?php if ($isLoggedIn): ?>
                    <div class="add-avis-section">
                        <button class="btn-add-avis" id="toggleAvisForm">Ajouter un avis</button>
                        
                        <div class="avis-form-container" id="avisFormContainer" style="display: none;">
                            <form method="POST" class="avis-form">
                                <div class="form-group">
                                    <label for="note_restaurant">Votre note:</label>
                                    <div class="rating-input">
                                        <?php for($i = 1; $i <= 5; $i++): ?>
                                            <input type="radio" id="star<?php echo $i; ?>" name="note_restaurant" value="<?php echo $i; ?>" />
                                            <label for="star<?php echo $i; ?>"><i class="far fa-star"></i></label>
                                        <?php endfor; ?>
                                    </div>
                                </div>
                                
                                <div class="form-group">
                                    <label for="avis">Votre commentaire:</label>
                                    <textarea name="avis" id="avis" rows="5" placeholder="Partagez votre expérience..."></textarea>
                                </div>
                                
                                <input type="hidden" name="id_restaurant" value="<?php echo $id_restaurant; ?>">
                                <button type="submit" class="btn-submit-avis">Publier votre avis</button>
                            </form>
                        </div>
                    </div>
                <?php endif; ?>
            </div>
        </div>
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
