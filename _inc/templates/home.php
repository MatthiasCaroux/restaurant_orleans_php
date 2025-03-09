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

// Fonction auxiliaire pour convertir une heure HH:MM en minutes depuis minuit
function convertToMinutes($time) {
    list($hours, $minutes) = explode(':', trim($time));
    return (intval($hours) * 60) + intval($minutes);
}

// Fonction auxiliaire pour vérifier si une heure est dans une plage horaire
function isTimeInRange($time, $open_time, $close_time) {
    // Convertir les heures en minutes depuis minuit pour faciliter la comparaison
    $time_minutes = convertToMinutes($time);
    $open_minutes = convertToMinutes($open_time);
    $close_minutes = convertToMinutes($close_time);
    
    // Gérer le cas où le restaurant ferme après minuit
    if ($open_minutes > $close_minutes) {
        return ($time_minutes >= $open_minutes || $time_minutes <= $close_minutes);
    } else {
        return ($time_minutes >= $open_minutes && $time_minutes <= $close_minutes);
    }
}

// Nouvelle fonction pour vérifier si un jour est dans une plage (ex: "Mo-Fr")
function isDayInRange($day, $range) {
    $range = trim($range);
    if (strpos($range, '-') === false) {
        return $day === $range;
    }
    
    $days_range = explode('-', $range);
    if (count($days_range) !== 2) {
        return false;
    }
    
    $start_day = trim($days_range[0]);
    $end_day = trim($days_range[1]);
    
    $days_order = ['Mo', 'Tu', 'We', 'Th', 'Fr', 'Sa', 'Su'];
    $start_index = array_search($start_day, $days_order);
    $end_index = array_search($end_day, $days_order);
    $current_index = array_search($day, $days_order);
    
    if ($start_index === false || $end_index === false || $current_index === false) {
        return false;
    }
    
    // Gérer le cas où la plage va sur la semaine suivante (ex: "Sa-Tu")
    if ($start_index > $end_index) {
        return ($current_index >= $start_index || $current_index <= $end_index);
    } else {
        return ($current_index >= $start_index && $current_index <= $end_index);
    }
}

// Fonction auxiliaire pour vérifier si un jour est inclus dans une plage de jours
function isDayIncluded($day, $days_part) {
    // Nettoyage des espaces
    $days_part = trim($days_part);
    
    // Si le jour est directement présent
    if ($day === $days_part) {
        return true;
    }
    
    // Traiter les jours séparés par des virgules
    $day_groups = explode(',', $days_part);
    
    foreach ($day_groups as $group) {
        $group = trim($group);
        
        // Cas d'un jour unique
        if ($group === $day) {
            return true;
        }
        
        // Cas d'une plage de jours (ex: "Mo-Fr")
        if (strpos($group, '-') !== false) {
            list($start_day, $end_day) = explode('-', $group);
            $start_day = trim($start_day);
            $end_day = trim($end_day);
            
            $days_order = ['Mo', 'Tu', 'We', 'Th', 'Fr', 'Sa', 'Su'];
            $start_index = array_search($start_day, $days_order);
            $end_index = array_search($end_day, $days_order);
            $day_index = array_search($day, $days_order);
            
            if ($start_index === false || $end_index === false || $day_index === false) {
                continue;
            }
            
            // Gestion du cas où la plage couvre le passage à la semaine suivante
            if ($start_index > $end_index) {
                if ($day_index >= $start_index || $day_index <= $end_index) {
                    return true;
                }
            } else {
                if ($day_index >= $start_index && $day_index <= $end_index) {
                    return true;
                }
            }
        }
    }
    
    return false;
}

// Fonction améliorée pour vérifier si un restaurant est ouvert
function isRestaurantOpen($opening_hours) {
    // Si pas d'horaires, considérer comme fermé
    if (empty($opening_hours)) {
        return false;
    }
    
    // Tableau de correspondance entre jours abrégés en anglais et leur équivalent à 2 lettres
    $days_map = [
        'mo' => 'Mo',
        'tu' => 'Tu',
        'we' => 'We',
        'th' => 'Th',
        'fr' => 'Fr',
        'sa' => 'Sa',
        'su' => 'Su'
    ];
    
    // Obtenir le jour actuel au format 2 lettres (Mo, Tu, We, etc.)
    $current_day_abbr = substr(strtolower(date('D')), 0, 2);
    $current_day = $days_map[$current_day_abbr] ?? '';
    
    // Obtenir l'heure actuelle au format 24h (ex: 14:30)
    $current_time = date('H:i');
    
    // Nettoyer la chaîne d'entrée
    $opening_hours = trim($opening_hours);
    
    // Séparer les différentes plages horaires (séparées par des points-virgules)
    $schedules = explode(';', $opening_hours);
    
    foreach ($schedules as $schedule) {
        $schedule = trim($schedule);
        if (empty($schedule)) {
            continue;
        }
        
        // Format attendu: "Tu-Sa 11:00-20:00" ou "Fr 11:45-13:45,19:00-22:00"
        if (preg_match('/^([A-Za-z\-,]+)\s+(.+)$/', $schedule, $matches)) {
            $days_part = trim($matches[1]);
            $hours_part = trim($matches[2]);
            
            // Vérifier si le jour actuel est inclus dans la plage de jours
            if (isDayIncluded($current_day, $days_part)) {
                // Vérifier si l'heure actuelle est dans une des plages horaires
                $time_ranges = explode(',', $hours_part);
                
                foreach ($time_ranges as $time_range) {
                    $time_range = trim($time_range);
                    
                    // Format attendu: "11:00-20:00"
                    if (preg_match('/(\d{1,2}:\d{2})-(\d{1,2}:\d{2})/', $time_range, $time_matches)) {
                        $open_time = trim($time_matches[1]);
                        $close_time = trim($time_matches[2]);
                        
                        if (isTimeInRange($current_time, $open_time, $close_time)) {
                            return true;
                        }
                    }
                }
            }
        }
    }
    
    return false;
}

// Fonction pour déboguer les heures d'ouverture si nécessaire
function debugOpeningHours($opening_hours) {
    // Obtenez les valeurs actuelles
    $days_map = [
        'mo' => 'Mo', 'tu' => 'Tu', 'we' => 'We', 'th' => 'Th', 
        'fr' => 'Fr', 'sa' => 'Sa', 'su' => 'Su'
    ];
    $current_day_abbr = substr(strtolower(date('D')), 0, 2);
    $current_day = $days_map[$current_day_abbr] ?? '';
    $current_time = date('H:i');
    
    echo "<div style='background:#f8f9fa;border:1px solid #ddd;padding:10px;margin:10px 0;'>";
    echo "<h4>Débogage des heures d'ouverture</h4>";
    echo "<p>Jour actuel: <b>" . date('l') . " ($current_day)</b> - Heure actuelle: <b>$current_time</b></p>";
    echo "<p>Chaîne d'horaires: <code>$opening_hours</code></p>";
    
    $schedules = explode(';', $opening_hours);
    echo "<ol>";
    foreach ($schedules as $schedule) {
        $schedule = trim($schedule);
        if (empty($schedule)) {
            echo "<li>Section vide ignorée</li>";
            continue;
        }
        
        echo "<li>Analyse de <code>$schedule</code>: ";
        
        if (preg_match('/([A-Za-z\-,]+)\s+(.+)/', $schedule, $matches)) {
            $days_part = trim($matches[1]);
            $hours_part = trim($matches[2]);
            
            echo "Jours: <code>$days_part</code>, Heures: <code>$hours_part</code> - ";
            
            $day_included = isDayIncluded($current_day, $days_part);
            echo "Le jour actuel est " . ($day_included ? "inclus" : "non inclus") . " dans cette plage. ";
            
            if ($day_included) {
                $time_ranges = explode(',', $hours_part);
                echo "<ul>";
                foreach ($time_ranges as $time_range) {
                    $time_range = trim($time_range);
                    echo "<li>Plage horaire: <code>$time_range</code> - ";
                    
                    if (preg_match('/(\d{1,2}:\d{2})-(\d{1,2}:\d{2})/', $time_range, $time_matches)) {
                        $open_time = trim($time_matches[1]);
                        $close_time = trim($time_matches[2]);
                        
                        $in_range = isTimeInRange($current_time, $open_time, $close_time);
                        echo "L'heure actuelle est " . ($in_range ? "dans" : "hors de") . " cette plage ($open_time à $close_time)</li>";
                    } else {
                        echo "Format de plage horaire non reconnu</li>";
                    }
                }
                echo "</ul>";
            }
        } else {
            echo "Format non reconnu";
        }
        echo "</li>";
    }
    echo "</ol>";
    
    echo "<p><strong>Résultat final:</strong> Le restaurant est actuellement " . 
         (isRestaurantOpen($opening_hours) ? "<span style='color:#4CAF50'>ouvert</span>" : "<span style='color:#F44336'>fermé</span>") . 
         ".</p>";
    echo "</div>";
}

// Fonction pour formater le type de cuisine (remplacer les tirets par des espaces et mettre en majuscule)
function formatCuisine($cuisine) {
    if (empty($cuisine)) {
        return '';
    }
    
    // Remplacer les tirets par des espaces
    $formatted = str_replace('-', ' ', $cuisine);
    
    // Mettre en majuscule la première lettre de chaque mot
    return ucwords($formatted);
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
                    <i class="fas fa-search"></i>
                </button>
            </div>
            <p id="recherche" class="search-info">
                Trouvez des restaurants, hôtels et bien plus encore, près de chez vous ou n'importe où dans le monde.
            </p>
            <div class="filters-container">
                <!-- Placer le sélecteur de type au-dessus -->
                <div class="filter-select-container">
                    <select id="recherche_by_type" name="type" class="search-info">
                        <option value="tout" <?php if($type_filter == 'tout') echo 'selected'; ?>>Tous</option>
                        <?php foreach($tout_type as $type): ?>
                            <?php if(!empty($type)): ?>
                                <option value="<?php echo htmlspecialchars($type); ?>" <?php if($type_filter == $type) echo 'selected'; ?>><?php echo htmlspecialchars($type); ?></option>
                            <?php endif; ?>
                        <?php endforeach; ?>
                    </select>
                </div>
                
                <!-- Conteneur pour aligner les trois filtres horizontalement -->
                <div class="toggle-filters-row">
                    <div class="filter-toggle">
                        <label class="switch">
                            <input type="checkbox" id="filter-vegetarian" name="vegetarian" value="1" <?php if($vegetarian_filter) echo 'checked'; ?>>
                            <span class="slider round"></span>
                        </label>
                        <span class="filter-label">
                            <i class="fas fa-leaf"></i> Végétarien
                        </span>
                    </div>
                    
                    <div class="filter-toggle">
                        <label class="switch">
                            <input type="checkbox" id="filter-wheelchair" name="wheelchair" value="1" <?php if($wheelchair_filter) echo 'checked'; ?>>
                            <span class="slider round"></span>
                        </label>
                        <span class="filter-label">
                            <i class="fas fa-wheelchair"></i> Accès PMR
                        </span>
                    </div>
                    
                    <div class="filter-toggle">
                        <label class="switch">
                            <input type="checkbox" id="filter-open-now" name="open_now" value="1" <?php if($open_now_filter) echo 'checked'; ?>>
                            <span class="slider round"></span>
                        </label>
                        <span class="filter-label">
                            <i class="fas fa-clock"></i> Ouvert maintenant
                        </span>
                    </div>
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
                                        $formatted_cuisine = formatCuisine($restaurant['cuisine'] ?? '');
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
                                            <p class="phone-number"><i class="fas fa-phone"></i> <?php echo htmlspecialchars($restaurant['telephone_restaurant']); ?></p>
                                        <?php else: ?>
                                            <p class="phone-number">&nbsp;</p>
                                        <?php endif; ?>
                                        
                                        <div class="restaurant-details">
                                            <?php if(!empty($restaurant['type_restaurant'])): ?>
                                                <p class="cuisine-type">
                                                    <i class="fas fa-utensils"></i> Type: <?php echo htmlspecialchars($restaurant['type_restaurant']); ?>
                                                    <?php if(!empty($restaurant['cuisine'])): ?>
                                                        <span class="separator">•</span> <?php echo htmlspecialchars($formatted_cuisine); ?>
                                                    <?php endif; ?>
                                                </p>
                                            <?php endif; ?>
                                            
                                            <div class="opening-hours">
                                                <?php 
                                                    // Décommenter pour déboguer un restaurant spécifique
                                                    // if ($restaurant['nom_restaurant'] === 'Nom du restaurant problématique') {
                                                    //     debugOpeningHours($restaurant['opening_hours']);
                                                    // }
                                                    
                                                    if(isRestaurantOpen($restaurant['opening_hours'])): 
                                                ?>
                                                    <span class="status open-now">
                                                        <i class="fas fa-door-open"></i> 
                                                        Ouvert actuellement
                                                    </span>
                                                <?php else: ?>
                                                    <span class="status closed-now">
                                                        <i class="fas fa-door-closed"></i> 
                                                        Fermé actuellement
                                                    </span>
                                                <?php endif; ?>
                                                <?php if(!empty($restaurant['opening_hours'])): ?>
                                                    <small><?php echo htmlspecialchars($restaurant['opening_hours']); ?></small>
                                                <?php else: ?>
                                                    <small>Horaires non disponibles</small>
                                                <?php endif; ?>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </a>
                    <?php endforeach; ?>
                <?php else: ?>
                    <p class="no-results">Aucun restaurant trouvé.</p>
                <?php endif; ?>
            </div>
        </section>
    </main>
    
    <script src="/_inc/static/js/filtre.js"></script> 

</body>
</html>