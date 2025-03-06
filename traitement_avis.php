<?php
session_start();

require_once "_inc/bd/db.php";

function cleanInput($input) {
    $input = trim($input);
    $input = stripslashes($input);
    $input = htmlspecialchars($input);
    return $input;
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $errors = [];

    if (!isset($_POST['restaurant_id']) || !is_numeric($_POST['restaurant_id'])) {
        $errors[] = "ID de restaurant invalide.";
        $restaurant_id = 0;
    } else {
        $restaurant_id = intval($_POST['restaurant_id']);
    }

    if (!isset($_POST['rating']) || !in_array($_POST['rating'], [1, 2, 3, 4, 5])) {
        $errors[] = "Notation invalide.";
        $rating = 3;
    } else {
        $rating = intval($_POST['rating']);
    }

    if (!isset($_POST['titre']) || empty($_POST['titre']) || strlen($_POST['titre']) > 100) {
        $errors[] = "Titre invalide. Il doit être entre 1 et 100 caractères.";
        $titre = '';
    } else {
        $titre = cleanInput($_POST['titre']);
    }

    if (!isset($_POST['avis']) || empty($_POST['avis']) || strlen($_POST['avis']) > 500) {
        $errors[] = "Avis invalide. Il doit être entre 1 et 500 caractères.";
        $avis = '';
    } else {
        $avis = cleanInput($_POST['avis']);
    }

    if (!empty($errors)) {
        $_SESSION['review_errors'] = $errors;
        header("Location: ajouter_avis.php?id=" . $restaurant_id);
        exit();
    }

    try {
        $pdo = getPDO();

        $sql = 'INSERT INTO "Avis" (
            id_restaurant, 
            note, 
            titre, 
            text
        ) VALUES (
            :restaurant_id, 
            :rating, 
            :titre, 
            :avis
        )';

        $stmt = $pdo->prepare($sql);
        $stmt->execute([
            ':restaurant_id' => $restaurant_id,
            ':rating' => $rating,
            ':titre' => $titre,
            ':avis' => $avis
        ]);

        $_SESSION['review_success'] = "Votre avis a été publié avec succès !";
        header("Location: pageResto.php?id=" . $restaurant_id);
        exit();

    } catch (PDOException $e) {
        $_SESSION['review_errors'] = ["Erreur lors de la publication de l'avis : " . $e->getMessage()];
        header("Location: ajouter_avis.php?id=" . $restaurant_id);
        exit();
    }
} else {
    header("Location: index.php");
    exit();
}
?>