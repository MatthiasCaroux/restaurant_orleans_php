<?php
require_once __DIR__ . '/db.php';
global $db;

function getRestaurantImage($restaurantName, $imagesData) {
    foreach ($imagesData as $image) {
        if ($image['name'] === $restaurantName) {
            return $image['image_url'];
        }
    }
    return '_inc/static/images/bk.jpeg'; // Image par défaut si aucune image trouvée
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
?>


