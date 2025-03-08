<?php

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

require_once __DIR__ . "/../bd/page_restaurant_bd.php";

// Initialiser les paramètres de filtrage
$type_filter = isset($_GET['type']) ? $_GET['type'] : 'tout';
$vegetarian_filter = isset($_GET['vegetarian']) ? filter_var($_GET['vegetarian'], FILTER_VALIDATE_BOOLEAN) : false;
$wheelchair_filter = isset($_GET['wheelchair']) ? filter_var($_GET['wheelchair'], FILTER_VALIDATE_BOOLEAN) : false;
$open_now_filter = isset($_GET['open_now']) ? filter_var($_GET['open_now'], FILTER_VALIDATE_BOOLEAN) : false;
$search_text = isset($_GET['search']) ? $_GET['search'] : '';

// Fonction pour récupérer les restaurants filtrés
function getFilteredRestaurants($type = 'tout', $vegetarian = false, $wheelchair = false, $open_now = false, $search = '') {
    try {
        $pdo = getPDO();
        
        // Construire la requête SQL de base
        $sql = 'SELECT * FROM "Restaurant" WHERE 1=1';
        $params = [];
        
        // Ajouter le filtre par type si nécessaire
        if ($type !== 'tout') {
            $sql .= ' AND type_restaurant = :type';
            $params['type'] = $type;
        }
        
        // Ajouter le filtre végétarien si activé - CORRIGÉ
        if ($vegetarian) {
            $sql .= ' AND vegetarian = :vegetarian';
            $params['vegetarian'] = 'true';
        }
        
        // Ajouter le filtre accès PMR si activé
        if ($wheelchair) {
            $sql .= ' AND wheelchair = :wheelchair';
            $params['wheelchair'] = 'yes';
        }
        
        // Ajouter le filtre de recherche par texte si fourni
        if (!empty($search)) {
            $sql .= ' AND (LOWER(nom_restaurant) LIKE :search OR LOWER(commune) LIKE :search OR LOWER(cuisine) LIKE :search)';
            $params['search'] = '%' . strtolower($search) . '%';
        }
        
        // Préparer et exécuter la requête
        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);
        
        $results = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        // Si le filtre "ouvert maintenant" est activé, filtrer les résultats en PHP
        if ($open_now) {
            $results = array_filter($results, function($restaurant) {
                return isRestaurantOpen($restaurant['opening_hours']);
            });
        }
        
        return $results;
    } catch (PDOException $e) {
        error_log("Erreur lors de la récupération des restaurants filtrés : " . $e->getMessage());
        return [];
    }
}

// Récupérer les restaurants avec les filtres appliqués
if ($_SERVER['REQUEST_METHOD'] === 'GET' && (isset($_GET['filter']) || isset($_GET['search']))) {
    $restaurants = getFilteredRestaurants($type_filter, $vegetarian_filter, $wheelchair_filter, $open_now_filter, $search_text);
} else {
    // Récupération de tous les restaurants par défaut
    $restaurants = getAllRestaurants();
}

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

// Fonction pour vérifier si un restaurant est actuellement ouvert
function isRestaurantOpen($opening_hours) {
    if (empty($opening_hours)) {
        return false;
    }
    
    // Logic to parse opening_hours and check if currently open
    // This is a simplified version - a full implementation would be more complex
    $current_day = strtolower(substr(date('D'), 0, 2));
    $current_time = date('H:i');
    
    // Simple check if the current day is mentioned in opening hours
    return strpos(strtolower($opening_hours), $current_day) !== false;
}

?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Liste des Restaurants</title>
    <link rel="stylesheet" href="<?php echo $cssPath; ?>home.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body>
    <main class="home">
        <form method="GET" id="filter-form">
            <div class="search-container">
                <input type="text" id="recherche_texte" name="search" placeholder="Rechercher un restaurant, un hôtel..." value="<?php echo htmlspecialchars($search_text); ?>" />
                <button type="submit" name="filter" value="1">
                    <img src="<?php echo $imagesPath; ?>loupe.png" alt="Logo">
                </button>
            </div>
            <p id="recherche" class="search-info">
                Trouvez des restaurants, hôtels et bien plus encore, près de chez vous ou n'importe où dans le monde.
            </p>
            <div class="filters-container">
                <div class="filter-select">
                    <label for="recherche_by_type">Type:</label>
                    <select id="recherche_by_type" name="type" class="search-info">
                        <option value="tout" <?php if($type_filter == 'tout') echo 'selected'; ?>>Tous</option>
                        <?php foreach($tout_type as $type): ?>
                            <option value="<?php echo htmlspecialchars($type); ?>" <?php if($type_filter == $type) echo 'selected'; ?>><?php echo htmlspecialchars($type); ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                
                <div class="filter-toggle">
                    <label class="switch">
                        <input type="checkbox" id="filter-vegetarian" name="vegetarian" value="1" <?php if($vegetarian_filter) echo 'checked'; ?>>
                        <span class="slider round"></span>
                    </label>
                    <span class="filter-label">
                        Végétarien
                    </span>
                </div>
                
                <div class="filter-toggle">
                    <label class="switch">
                        <input type="checkbox" id="filter-wheelchair" name="wheelchair" value="1" <?php if($wheelchair_filter) echo 'checked'; ?>>
                        <span class="slider round"></span>
                    </label>
                    <span class="filter-label">
                        Accès PMR
                    </span>
                </div>
                
                <div class="filter-toggle">
                    <label class="switch">
                        <input type="checkbox" id="filter-open-now" name="open_now" value="1" <?php if($open_now_filter) echo 'checked'; ?>>
                        <span class="slider round"></span>
                    </label>
                    <span class="filter-label">
                        Ouvert maintenant
                    </span>
                </div>
                

            </div>
        </form>
        
        <section>
            <div class="restaurant-container">
                <?php if(!empty($restaurants)): ?>
                    <?php foreach($restaurants as $restaurant): ?>
                        <a href="./vues/pageResto.php?id=<?php echo $restaurant['id_restaurant']; ?>" class="restaurant-link">
                            <div class="restaurant" 
                                 data-type="<?php echo htmlspecialchars($restaurant['type_restaurant']); ?>"
                                 data-vegetarian="<?php echo isset($restaurant['vegetarian']) && strtolower($restaurant['vegetarian']) === 'true' ? 'true' : 'false'; ?>"
                                 data-wheelchair="<?php echo isset($restaurant['wheelchair']) && $restaurant['wheelchair'] === 'yes' ? 'true' : 'false'; ?>"
                                 data-cuisine="<?php echo htmlspecialchars($restaurant['cuisine'] ?? ''); ?>"
                                 data-open="<?php echo isRestaurantOpen($restaurant['opening_hours']) ? 'true' : 'false'; ?>">
                                <div class="restaurant-info">
                                    <?php
                                        $restaurant_name = $restaurant['nom_restaurant'];
                                        $default_image = $imagesPath . 'default-restaurant.jpg';
                                        $image_path = $imagesPath . 'restaurants_images/' . $restaurant_name . '.jpg';
                                        $image_url = file_exists($image_path) ? $image_path : $default_image;
                                        $is_open = isRestaurantOpen($restaurant['opening_hours']);
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
                                            <p><i class="fas fa-phone"></i> <?php echo htmlspecialchars($restaurant['telephone_restaurant']); ?></p>
                                        <?php endif; ?>
                                        
                                        <div class="restaurant-details">
                                            <?php if(!empty($restaurant['type_restaurant'])): ?>
                                                <p class="cuisine-type">
                                                    <i class="fas fa-utensils"></i> Type: <?php echo htmlspecialchars($restaurant['type_restaurant']); ?>
                                                    <?php if(!empty($restaurant['cuisine'])): ?>
                                                        - <?php echo htmlspecialchars($restaurant['cuisine']); ?>
                                                    <?php endif; ?>
                                                </p>
                                            <?php endif; ?>
                                            
                                            <?php if(!empty($restaurant['opening_hours'])): ?>
                                                <p class="opening-hours">
                                                    <i class="fas fa-clock"></i> 
                                                    <span class="<?php echo $is_open ? 'open-now' : 'closed-now'; ?>">
                                                        <?php echo $is_open ? 'Ouvert maintenant' : 'Fermé actuellement'; ?>
                                                    </span>
                                                    <br>
                                                    <small><?php echo htmlspecialchars($restaurant['opening_hours']); ?></small>
                                                </p>
                                            <?php endif; ?>
                                            
                                            <?php if(!empty($restaurant['site_restaurant'])): ?>
                                                <p class="website-link">
                                                    <i class="fas fa-globe"></i> <a href="<?php echo htmlspecialchars($restaurant['site_restaurant']); ?>" target="_blank">Site web</a>
                                                </p>
                                            <?php endif; ?>
                                        </div>
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
    
    <script>
    document.addEventListener('DOMContentLoaded', function() {
        // Éléments de filtre
        const searchInput = document.getElementById('recherche_texte');
        const typeSelect = document.getElementById('recherche_by_type');
        const vegetarianFilter = document.getElementById('filter-vegetarian');
        const wheelchairFilter = document.getElementById('filter-wheelchair');
        const openNowFilter = document.getElementById('filter-open-now');
        const filterForm = document.getElementById('filter-form');
        
        // Options pour les filtres en temps réel (si souhaité)
        const enableRealTimeFiltering = false;
        
        if (enableRealTimeFiltering) {
            // Fonction pour appliquer les filtres en temps réel (JavaScript côté client)
            function applyFiltersRealTime() {
                const searchText = searchInput.value.toLowerCase();
                const selectedType = typeSelect.value;
                const showVegetarian = vegetarianFilter.checked;
                const showWheelchair = wheelchairFilter.checked;
                const showOpenNow = openNowFilter.checked;
                
                const restaurants = document.querySelectorAll('.restaurant');
                
                restaurants.forEach(function(restaurant) {
                    const restaurantName = restaurant.querySelector('h2').textContent.toLowerCase();
                    const restaurantType = restaurant.getAttribute('data-type');
                    const isVegetarian = restaurant.getAttribute('data-vegetarian') === 'true';
                    const hasWheelchair = restaurant.getAttribute('data-wheelchair') === 'true';
                    const isOpen = restaurant.getAttribute('data-open') === 'true';
                    
                    // Vérifier tous les critères de filtre
                    const matchesSearch = restaurantName.includes(searchText);
                    const matchesType = selectedType === 'tout' || restaurantType === selectedType;
                    const matchesVegetarian = !showVegetarian || isVegetarian;
                    const matchesWheelchair = !showWheelchair || hasWheelchair;
                    const matchesOpenNow = !showOpenNow || isOpen;
                    
                    // Afficher ou masquer le restaurant en fonction des filtres
                    if (matchesSearch && matchesType && matchesVegetarian && matchesWheelchair && matchesOpenNow) {
                        restaurant.parentNode.style.display = '';
                    } else {
                        restaurant.parentNode.style.display = 'none';
                    }
                });
            }
            
            // Ajouter des écouteurs d'événements pour tous les filtres
            searchInput.addEventListener('input', applyFiltersRealTime);
            typeSelect.addEventListener('change', applyFiltersRealTime);
            vegetarianFilter.addEventListener('change', applyFiltersRealTime);
            wheelchairFilter.addEventListener('change', applyFiltersRealTime);
            openNowFilter.addEventListener('change', applyFiltersRealTime);
        } else {
            // Approche de soumission de formulaire pour le filtrage côté serveur
            // Activer l'autosubmit si désiré
            const autoSubmitOnChange = true;
            
            if (autoSubmitOnChange) {
                // Soumettre automatiquement le formulaire lors de changements
                typeSelect.addEventListener('change', function() {
                    filterForm.submit();
                });
                
                vegetarianFilter.addEventListener('change', function() {
                    filterForm.submit();
                });
                
                wheelchairFilter.addEventListener('change', function() {
                    filterForm.submit();
                });
                
                openNowFilter.addEventListener('change', function() {
                    filterForm.submit();
                });
            }
        }
    });
    </script>
</body>
</html>