<?php
require_once '../Classes/Autoloader.php';
require_once '../_inc/bd/favoris_bd.php';
$cssPath = "../_inc/static/styles/";
require_once '../_inc/templates/header.php';
Autoloader::register();
session_start();
if (!isset($_SESSION['user_id'])) {
    // header('Location: login.php');
    exit();
}
else{
}

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Profil</title>
    <link rel="stylesheet" href="<?php echo $cssPath; ?>base.css">
    <link rel="stylesheet" href="<?php echo $cssPath; ?>header.css">
    <link rel="stylesheet" href="<?php echo $cssPath; ?>styles.css">

</head>
<body>
    <?php
    foreach ($_SESSION as $key => $value) {
        echo "<p>$key: $value</p>";
    }
    ?>
    <div id="cuisine">Mes types de cuisines favoris
    <table>
        <thead>
            <tr>
                <th>Type de Cuisine</th>
            </tr>
        </thead>
        <tbody>
            <?php
            $favoris = get_favoris_cuisine_by_user_id($_SESSION['user_id']);
            foreach ($favoris as $favori) {
                echo "<tr><td>" . htmlspecialchars($favori['nom_type_cuisine']) . "</td>";
                echo "<td><form method='post' action='profil.php'><input type='hidden' name='delete_cuisine_id' value='" . htmlspecialchars($favori['id_type_cuisine']) . "'><button type='submit'>Supprimer</button></form></td></tr>";
            }
            ?>
        </tbody>
    </table>


    <form method="post" action="profil.php">
        <select name="cuisine">
            <?php
            $cuisines = get_all_cuisines();
            foreach ($cuisines as $cuisine) {
                echo "<option value=\"" . htmlspecialchars($cuisine['nom_type_cuisine']) . "\">" . htmlspecialchars($cuisine['nom_type_cuisine']) . "</option>";
            }
            ?>
        </select>
        <button type="submit">Ajouter aux favoris</button>
    </form>

    <?php
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['cuisine'])) {
        $cuisine = $_POST['cuisine'];
        $cuisine_id = get_cuisine_id_by_name($cuisine);
        add_favoris_cuisine($_SESSION['user_id'], $cuisine_id);
        echo "<p> $cuisine </p>";
        header('Location: profil.php');
        exit();
    }
    if($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_cuisine_id'])){
        $delete_cuisine_id = $_POST['delete_cuisine_id'];
        delete_favoris_cuisine($delete_cuisine_id, $_SESSION['user_id']);
        header('Location: profil.php');
        exit();
    }
    ?>

    </div>

</body>
</html>