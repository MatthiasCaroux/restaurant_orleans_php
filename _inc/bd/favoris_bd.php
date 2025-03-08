<?php
require_once __DIR__ . '/db.php';
global $db;


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
function get_favoris_cuisine_by_user_id($user_id){
    $pdo = getPDO();
    $query = 'SELECT * FROM favoris_cuisine natural join "Type_cuisine" WHERE id_utilisateur = :user_id';
    $stmt = $pdo->prepare($query);
    $stmt->bindParam(':user_id', $user_id, PDO::PARAM_INT);
    $stmt->execute();
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

function get_all_cuisines(){
    $pdo = getPDO();
    $query = 'SELECT nom_type_cuisine FROM "Type_cuisine"';
    $stmt = $pdo->query($query);
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

function add_favoris_cuisine($user_id, $cuisine_id){
    $pdo = getPDO();
    $query = 'INSERT INTO favoris_cuisine (id_utilisateur, id_type_cuisine) VALUES (:user_id, :cuisine_id)';
    $stmt = $pdo->prepare($query);
    $stmt->bindParam(':user_id', $user_id, PDO::PARAM_INT);
    $stmt->bindParam(':cuisine_id', $cuisine_id, PDO::PARAM_INT);
    $stmt->execute();
}
function get_cuisine_id_by_name($cuisine){
    $pdo = getPDO();
    $query = 'SELECT id_type_cuisine FROM "Type_cuisine" WHERE nom_type_cuisine = :cuisine';
    $stmt = $pdo->prepare($query);
    $stmt->bindParam(':cuisine', $cuisine, PDO::PARAM_STR);
    $stmt->execute();
    return $stmt->fetchColumn();
}
function delete_favoris_cuisine($id,$user_id){
    $pdo = getPDO();
    $query = 'DELETE FROM favoris_cuisine WHERE id_type_cuisine = :id AND id_utilisateur = :user_id';
    $stmt = $pdo->prepare($query);
    $stmt->bindParam(':id', $id, PDO::PARAM_INT);
    $stmt->bindParam(':user_id', $user_id, PDO::PARAM_INT);
    $stmt->execute();
}
function getFavorisCuisinesByUserId($userId) {
    $pdo = getPDO();
    $query = 'SELECT nom_type_cuisine FROM favoris_cuisine JOIN "Type_cuisine" ON favoris_cuisine.id_type_cuisine = "Type_cuisine".id_type_cuisine WHERE id_utilisateur = :userId';
    $stmt = $pdo->prepare($query);
    $stmt->bindParam(':userId', $userId, PDO::PARAM_INT);
    $stmt->execute();
    return $stmt->fetchAll(PDO::FETCH_COLUMN);
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
?>