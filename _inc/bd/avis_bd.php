<?php
require_once __DIR__ . '/db.php';

function getAvisForRestaurant($restaurant_id) {
    $pdo = getPDO();
    $sql = 'SELECT u."username", a."id" AS id_Avis, d."id_Utilisateur", 
                   a."Titre", a."text", a."note"
            FROM "Deposer" d
            JOIN "Avis" a ON d."id_Avis" = a."id"
            JOIN "Utilisateur" u ON d."id_Utilisateur" = u."id"
            WHERE d."id_Restaurant" = :id_restaurant';

    $stmt = $pdo->prepare($sql);
    $stmt->execute(['id_restaurant' => $restaurant_id]);
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}





// il faut ajouter le nombre d'étoile de l'utilisateur sur le restaurant
function addAvis($titre, $texte, $note, $id_restaurant, $id_utilisateur) {
    $pdo = getPDO();

    // Vérifier si un avis existe déjà pour cet utilisateur et ce restaurant
    $checkSql = 'SELECT COUNT(*) FROM "Deposer" WHERE "id_Utilisateur" = :id_utilisateur AND "id_Restaurant" = :id_restaurant';
    $stmtCheck = $pdo->prepare($checkSql);
    $stmtCheck->execute([
        'id_utilisateur' => $id_utilisateur,
        'id_restaurant' => $id_restaurant
    ]);
    $alreadyExists = $stmtCheck->fetchColumn();

    if ($alreadyExists > 0) {
        return false; // Empêche l'insertion et signale que l'utilisateur a déjà déposé un avis
    }

    // Insérer l'avis dans la table "Avis"
    $sql = 'INSERT INTO "Avis" ("Titre", "text", "note") VALUES (:titre, :texte, :note)';
    $stmt = $pdo->prepare($sql);
    $stmt->execute([
        'titre' => $titre,
        'texte' => $texte,
        'note' => $note
    ]);

    // Récupérer l'ID du dernier avis inséré
    $id_avis = $pdo->lastInsertId();

    // Insérer la relation dans "Deposer"
    $sql2 = 'INSERT INTO "Deposer" ("id_Utilisateur", "id_Avis", "id_Restaurant") 
             VALUES (:id_utilisateur, :id_avis, :id_restaurant)';

    $stmt2 = $pdo->prepare($sql2);
    $stmt2->execute([
        'id_utilisateur' => $id_utilisateur,
        'id_avis' => $id_avis,
        'id_restaurant' => $id_restaurant
    ]);

    return true;
}



function getNoteMoyenneRestaurant($id_restaurant) {
    $pdo = getPDO();
    $sql = 'SELECT AVG(note) FROM "Avis" natural join "Deposer" WHERE "id_Restaurant" = :id_restaurant';
    $stmt = $pdo->prepare($sql);
    $stmt->execute(['id_restaurant' => $id_restaurant]);
    return $stmt->fetchColumn();
}


function getNombreAvisRestaurant($id_restaurant) {
    $pdo = getPDO();
    $sql = 'SELECT COUNT(*) FROM "Deposer" WHERE "id_Restaurant" = :id_restaurant';
    $stmt = $pdo->prepare($sql);
    $stmt->execute(['id_restaurant' => $id_restaurant]);
    return $stmt->fetchColumn();
}
