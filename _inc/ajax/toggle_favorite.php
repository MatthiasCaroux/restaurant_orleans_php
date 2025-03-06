<?php
session_start();
require_once '../bd/restaurant_queries.php';

// Vérifier si l'utilisateur est connecté
if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'Utilisateur non connecté']);
    exit;
}

// Récupérer les données POST
$data = json_decode(file_get_contents('php://input'), true);
$restaurant_id = $data['restaurant_id'] ?? 0;
$action = $data['action'] ?? '';

if ($restaurant_id && $action) {
    $user_id = $_SESSION['user_id'];
    $success = false;

    if ($action === 'add') {
        $success = addRestaurantToFavorites($user_id, $restaurant_id);
    } else if ($action === 'remove') {
        $success = removeRestaurantFromFavorites($user_id, $restaurant_id);
    }

    echo json_encode(['success' => $success]);
} else {
    echo json_encode(['success' => false, 'message' => 'Données invalides']);
} 