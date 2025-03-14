<?php
require_once __DIR__ . '/db.php';
require_once __DIR__ . '/favoris_bd.php';

/**
 * Récupère tous les restaurants
 * @return array Liste des restaurants
 */
function getAllRestaurants() {
    try {
        $pdo = getPDO();
        $sql = 'SELECT * FROM "Restaurant"';
        $stmt = $pdo->query($sql);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
        error_log("Erreur lors de la récupération des restaurants : " . $e->getMessage());
        return [];
    }
}

/**
 * Récupère un restaurant par son ID
 * @param int $id ID du restaurant
 * @return array|null Données du restaurant ou null si non trouvé
 */
function getRestaurantById($id) {
    if (!is_numeric($id)) {
        return null;
    }


    try {
        $pdo = getPDO();
        $stmt = $pdo->prepare("SELECT * FROM \"Restaurant\" WHERE id_restaurant = :id");
        $stmt->execute(['id' => $id]);
        $restaurant = $stmt->fetch(PDO::FETCH_ASSOC);


        return $restaurant;
    } catch (PDOException $e) {
        error_log("Erreur lors de la récupération du restaurant : " . $e->getMessage());
        return null;
    }
}


function getRestaurantImage($restaurantName, $imagesData) {
    foreach ($imagesData as $image) {
        if ($image['name'] === $restaurantName) {
            return $image['image_url'];
        }
    }
    return '_inc/static/images/bk.jpeg'; // Image par défaut si aucune image trouvée
}






function addRestaurantToLiked($user_id, $restaurant_id) {
    try {
        if (!is_numeric($user_id) || !is_numeric($restaurant_id)) {
            return false;
        }
        
        $pdo = getPDO();
        // Vérifier si une appréciation existe déjà
        $stmt_check = $pdo->prepare('SELECT "Aimer" FROM "Appreciation" WHERE id_utilisateur = :user_id AND id_restaurant = :restaurant_id');
        $stmt_check->execute(['user_id' => $user_id, 'restaurant_id' => $restaurant_id]);
        $existing = $stmt_check->fetch(PDO::FETCH_ASSOC);
        $stmt_check = null; // Libère le statement
        
        if ($existing) {
            // Mettre à jour la valeur "Aimer" à true
            $sql = 'UPDATE "Appreciation" SET "Aimer" = true WHERE id_utilisateur = :user_id AND id_restaurant = :restaurant_id';
            $stmt = $pdo->prepare($sql);
            $result = $stmt->execute(['user_id' => $user_id, 'restaurant_id' => $restaurant_id]);
            $stmt = null; // Libère le statement
            return $result;
        } else {
            // Insérer une nouvelle appréciation
            $sql = 'INSERT INTO "Appreciation" (id_utilisateur, id_restaurant, "Aimer") VALUES (:user_id, :restaurant_id, true)';
            $stmt = $pdo->prepare($sql);
            $result = $stmt->execute(['user_id' => $user_id, 'restaurant_id' => $restaurant_id]);
            $stmt = null; // Libère le statement
            return $result;
        }
    } catch (PDOException $e) {
        error_log("Erreur lors de l'ajout du j'aime au restaurant : " . $e->getMessage());
        return false;
    }
}


/**
 * Vérifie si un restaurant est aimé par l'utilisateur
 * @param int $user_id ID de l'utilisateur
 * @param int $restaurant_id ID du restaurant
 * @return bool
 */
function isRestaurantLiked($user_id, $restaurant_id) {
    try {
        if (!is_numeric($user_id) || !is_numeric($restaurant_id)) {
            return false;
        }
        
        $pdo = getPDO();
        $stmt = $pdo->prepare('SELECT "Aimer" FROM "Appreciation" WHERE id_utilisateur = :user_id AND id_restaurant = :restaurant_id');
        $stmt->execute(['user_id' => $user_id, 'restaurant_id' => $restaurant_id]);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        $stmt = null; // Libère le statement
        
        // Log pour déboguer
        error_log("isRestaurantLiked: result = " . print_r($result, true));
        
        if ($result && isset($result['Aimer'])) {
            // Utilise filter_var pour interpréter correctement la valeur (par exemple, 't' ou '1')
            return filter_var($result['Aimer'], FILTER_VALIDATE_BOOLEAN);
        }
        return false;
    } catch (PDOException $e) {
        error_log("Erreur lors de la vérification des j'aime : " . $e->getMessage());
        return false;
    }
}


/**
 * Retire un restaurant de la liste des j'aime d'un utilisateur
 * @param int $user_id ID de l'utilisateur
 * @param int $restaurant_id ID du restaurant
 * @return bool
 */
function removeRestaurantFromLiked($user_id, $restaurant_id) {
    try {
        if (!is_numeric($user_id) || !is_numeric($restaurant_id)) {
            return false;
        }
        
        $pdo = getPDO();
        // Vérifier si une appréciation existe
        $stmt_check = $pdo->prepare('SELECT "Aimer" FROM "Appreciation" WHERE id_utilisateur = :user_id AND id_restaurant = :restaurant_id');
        $stmt_check->execute(['user_id' => $user_id, 'restaurant_id' => $restaurant_id]);
        $existing = $stmt_check->fetch(PDO::FETCH_ASSOC);
        $stmt_check = null; // Libère le statement
        
        if ($existing) {
            // Mettre à jour la valeur "Aimer" à false
            $sql = 'UPDATE "Appreciation" SET "Aimer" = false WHERE id_utilisateur = :user_id AND id_restaurant = :restaurant_id';
            $stmt = $pdo->prepare($sql);
            $result = $stmt->execute(['user_id' => $user_id, 'restaurant_id' => $restaurant_id]);
            $stmt = null; // Libère le statement
            return $result;
        } else {
            return false;
        }
    } catch (PDOException $e) {
        error_log("Erreur lors de la suppression du j'aime du restaurant : " . $e->getMessage());
        return false;
    }
}



