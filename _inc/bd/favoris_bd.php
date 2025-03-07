<?php
require_once __DIR__ . '/db.php';


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