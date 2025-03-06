<?php
require_once __DIR__ . '/db.php';

function getRestaurantImage($restaurantName, $imagesData) {
    foreach ($imagesData as $image) {
        if ($image['name'] === $restaurantName) {
            return $image['image_url'];
        }
    }
    return '_inc/static/images/bk.jpeg'; // Image par défaut si aucune image trouvée
}

