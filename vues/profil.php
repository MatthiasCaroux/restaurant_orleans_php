<?php
require_once '../Classes/Autoloader.php';
require_once '../_inc/bd/favoris_bd.php';
Autoloader::register();
session_start();

// Redirection si l'utilisateur n'est pas connecté
if (!isset($_SESSION['user_id'])) {
    // header('Location: login.php');
    exit();
}

// Traitement des formulaires
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Ajout d'un type de cuisine aux favoris
    if (isset($_POST['cuisine'])) {
        $cuisine = $_POST['cuisine'];
        $cuisine_id = get_cuisine_id_by_name($cuisine);
        add_favoris_cuisine($_SESSION['user_id'], $cuisine_id);
        header('Location: profil.php');
        exit();
    }
    
    // Suppression d'un type de cuisine des favoris
    if (isset($_POST['delete_cuisine_id'])) {
        $delete_cuisine_id = $_POST['delete_cuisine_id'];
        delete_favoris_cuisine($delete_cuisine_id, $_SESSION['user_id']);
        header('Location: profil.php');
        exit();
    }
}

// Récupération des favoris de l'utilisateur
$favoris = get_favoris_cuisine_by_user_id($_SESSION['user_id']);
$cuisines = get_all_cuisines();

// Chemin vers vos fichiers statiques
$cssPath = "../_inc/static/styles/";
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mon Profil</title>
    <link rel="stylesheet" href="<?php echo $cssPath; ?>base.css">
    <link rel="stylesheet" href="<?php echo $cssPath; ?>header.css">
    <link rel="stylesheet" href="<?php echo $cssPath; ?>profil.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>
<body>
    <?php include_once '../_inc/templates/header.php'; ?>
    
    <div id="main-container">
        <h1 class="page-title">Mon Profil</h1>
        
        <div id="cuisine">
            <h2><i class="fas fa-utensils"></i> Mes types de cuisines favoris</h2>
            
            <?php if (empty($favoris)): ?>
                <p class="no-items">Vous n'avez pas encore de types de cuisine favoris.</p>
            <?php else: ?>
                <table>
                    <thead>
                        <tr>
                            <th>Type de Cuisine</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($favoris as $favori): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($favori['nom_type_cuisine']); ?></td>
                                <td>
                                    <form method="post" action="profil.php">
                                        <input type="hidden" name="delete_cuisine_id" value="<?php echo htmlspecialchars($favori['id_type_cuisine']); ?>">
                                        <button type="submit" class="btn-delete"><i class="fas fa-trash-alt"></i> Supprimer</button>
                                    </form>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php endif; ?>
            
            <form method="post" action="profil.php" class="add-form">
                <select name="cuisine">
                    <option value="" disabled selected>Choisir un type de cuisine</option>
                    <?php foreach ($cuisines as $cuisine): ?>
                        <option value="<?php echo htmlspecialchars($cuisine['nom_type_cuisine']); ?>">
                            <?php echo htmlspecialchars($cuisine['nom_type_cuisine']); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
                <button type="submit" class="btn-add"><i class="fas fa-plus"></i> Ajouter aux favoris</button>
            </form>
        </div>
        
        <!-- Vous pouvez ajouter d'autres sections ici, comme les restaurants favoris, les informations personnelles, etc. -->
        
        <?php if (isset($_GET['debug'])): ?>
            <div class="debug-info">
                <h3>Informations de session</h3>
                <?php foreach ($_SESSION as $key => $value): ?>
                    <p><?php echo $key; ?>: <?php echo $value; ?></p>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>
</body>
</html>