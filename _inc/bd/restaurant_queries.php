<?php
require_once __DIR__ . '/db.php';

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
    try {
        $pdo = getPDO();
        $sql = 'SELECT * FROM "Restaurant" WHERE id_restaurant = :id';
        $stmt = $pdo->prepare($sql);
        $stmt->execute(['id' => $id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
        error_log("Erreur lors de la récupération du restaurant : " . $e->getMessage());
        return null;
    }
}

/**
 * Ajoute un restaurant aux favoris d'un utilisateur
 * @param int $user_id ID de l'utilisateur
 * @param int $restaurant_id ID du restaurant
 * @return bool
 */
function addRestaurantToFavorites($user_id, $restaurant_id) {
    try {
        $pdo = getPDO();
        
        // Vérifie d'abord si une appréciation existe déjà
        $sql_check = 'SELECT id FROM "Appreciation" 
                      WHERE id_utilisateur = :user_id AND id_restaurant = :restaurant_id';
        $stmt_check = $pdo->prepare($sql_check);
        $stmt_check->execute(['user_id' => $user_id, 'restaurant_id' => $restaurant_id]);
        $appreciation = $stmt_check->fetch(PDO::FETCH_ASSOC);

        if ($appreciation) {
            // Met à jour l'appréciation existante
            $sql = 'UPDATE "Appreciation" 
                    SET "Favoris" = true 
                    WHERE id_utilisateur = :user_id AND id_restaurant = :restaurant_id';
        } else {
            // Crée une nouvelle appréciation
            $sql = 'INSERT INTO "Appreciation" (id_utilisateur, id_restaurant, "Favoris") 
                    VALUES (:user_id, :restaurant_id, true)';
        }

        $stmt = $pdo->prepare($sql);
        return $stmt->execute(['user_id' => $user_id, 'restaurant_id' => $restaurant_id]);
    } catch (PDOException $e) {
        error_log("Erreur lors de l'ajout du restaurant aux favoris : " . $e->getMessage());
        return false;
    }
}


/**
 * Retire un restaurant des favoris d'un utilisateur
 * @param int $user_id ID de l'utilisateur
 * @param int $restaurant_id ID du restaurant
 * @return bool
 */
function removeRestaurantFromFavorites($user_id, $restaurant_id) {
    try {
        $pdo = getPDO();
        $sql = 'UPDATE "Appreciation" 
                SET "Favoris" = false 
                WHERE id_utilisateur = :user_id AND id_restaurant = :restaurant_id';
        $stmt = $pdo->prepare($sql);
        $stmt->execute(['user_id' => $user_id, 'restaurant_id' => $restaurant_id]);
        return $stmt->rowCount() > 0;
    } catch (PDOException $e) {
        error_log("Erreur lors de la suppression du restaurant des favoris : " . $e->getMessage());
        return false;
    }
}

/**
 * Vérifie si un restaurant est dans les favoris d'un utilisateur
 * @param int $user_id ID de l'utilisateur
 * @param int $restaurant_id ID du restaurant
 * @return bool
 */
function isRestaurantFavorite($user_id, $restaurant_id) {
    try {
        $pdo = getPDO();
        $sql = 'SELECT "Favoris" FROM "Appreciation" 
                WHERE id_utilisateur = :user_id 
                AND id_restaurant = :restaurant_id 
                AND "Favoris" = true';
        $stmt = $pdo->prepare($sql);
        $stmt->execute(['user_id' => $user_id, 'restaurant_id' => $restaurant_id]);
        return (bool) $stmt->fetchColumn();
    } catch (PDOException $e) {
        error_log("Erreur lors de la vérification des favoris : " . $e->getMessage());
        return false;
    }
}

/**
 * Récupère les favoris d'un utilisateur
 * @param int $user_id ID de l'utilisateur
 * @return array Liste des favoris
 */
function getFavoritesForUser($user_id) {
    try {
        $pdo = getPDO();
        $sql = 'SELECT r.* FROM "Restaurant" r
                INNER JOIN "Appreciation" a ON r.id_restaurant = a.id_restaurant
                WHERE a.id_utilisateur = :user_id AND a."Favoris" = true';
        $stmt = $pdo->prepare($sql);
        $stmt->execute(['user_id' => $user_id]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
        error_log("Erreur lors de la récupération des favoris : " . $e->getMessage());
        return [];
    }
}

function addRestaurantToLiked($user_id, $restaurant_id) {
    try {
        $pdo = getPDO();
        // Vérifier si une appréciation existe déjà
        $stmt_check = $pdo->prepare('SELECT "Aimer" FROM "Appreciation" WHERE id_utilisateur = ? AND id_restaurant = ?');
        $stmt_check->execute([$user_id, $restaurant_id]);
        $existing = $stmt_check->fetch(PDO::FETCH_ASSOC);
        
        if ($existing) {
            // Mettre à jour la valeur "Aimer" à true
            $sql = 'UPDATE "Appreciation" SET "Aimer" = true WHERE id_utilisateur = ? AND id_restaurant = ?';
            $stmt = $pdo->prepare($sql);
            return $stmt->execute([$user_id, $restaurant_id]);
        } else {
            // Insérer une nouvelle appréciation
            $sql = 'INSERT INTO "Appreciation" (id_utilisateur, id_restaurant, "Aimer") VALUES (?, ?, true)';
            $stmt = $pdo->prepare($sql);
            return $stmt->execute([$user_id, $restaurant_id]);
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
        $pdo = getPDO();
        $stmt = $pdo->prepare('SELECT "Aimer" FROM "Appreciation" WHERE id_utilisateur = ? AND id_restaurant = ?');
        $stmt->execute([$user_id, $restaurant_id]);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        
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
        $pdo = getPDO();
        // Vérifier si une appréciation existe
        $stmt_check = $pdo->prepare('SELECT "Aimer" FROM "Appreciation" WHERE id_utilisateur = ? AND id_restaurant = ?');
        $stmt_check->execute([$user_id, $restaurant_id]);
        $existing = $stmt_check->fetch(PDO::FETCH_ASSOC);
        
        if ($existing) {
            // Mettre à jour la valeur "Aimer" à false
            $sql = 'UPDATE "Appreciation" SET "Aimer" = false WHERE id_utilisateur = ? AND id_restaurant = ?';
            $stmt = $pdo->prepare($sql);
            return $stmt->execute([$user_id, $restaurant_id]);
        } else {
            return false;
        }
    } catch (PDOException $e) {
        error_log("Erreur lors de la suppression du j'aime du restaurant : " . $e->getMessage());
        return false;
    }
}





