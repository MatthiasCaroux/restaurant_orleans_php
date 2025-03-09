<?php
require_once __DIR__ . '/db.php';

function getAvisForRestaurant($restaurant_id) {
    $pdo = getPDO();
    $sql = 'SELECT "id_Utilisateur", "id_Avis", "id_Restaurant", "Titre", "texte", "note" FROM "Deposer" natural join "Avis" WHERE "id_Restaurant" = :id_restaurant';
    $stmt = $pdo->prepare($sql);
    $stmt->execute(['id_restaurant' => $id_restaurant]);
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}


// il faut ajouter le nombre d'étoile de l'utilisateur sur le restaurant
function addAvis($titre, $texte, $note, $id_restaurant, $id_utilisateur) {
    $pdo = getPDO();
    $sql = 'INSERT INTO "Avis" (titre, texte, note, id_restaurant, id_utilisateur) VALUES (:titre, :texte, :note, :id_restaurant, :id_utilisateur)';
    $sql2 = 'INSERT INTO "Deposer" (id_utilisateur, id_avis, id_restaurant) VALUES (:id_utilisateur, :id_avis, :id_restaurant)';

    $stmt = $pdo->prepare($sql);
    $stmt->execute(['titre' => $titre, 'texte' => $texte, 'note' => $note, 'id_restaurant' => $id_restaurant, 'id_utilisateur' => $id_utilisateur]);

    $stmt2 = $pdo->prepare($sql2);
    $stmt2->execute(['id_utilisateur' => $id_utilisateur, 'id_avis' => $pdo->lastInsertId(), 'id_restaurant' => $id_restaurant]);
}

function getNoteMoyenneRestaurant($id_restaurant) {
    $pdo = getPDO();
    $sql = 'SELECT AVG(note) FROM "Avis" natural join "Deposer" WHERE id_Restaurant = :id_restaurant';
    $stmt = $pdo->prepare($sql);
    $stmt->execute(['id_restaurant' => $id_restaurant]);
    return $stmt->fetchColumn();
}