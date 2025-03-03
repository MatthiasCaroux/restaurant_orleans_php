<?php
$cssPath = "_inc/static/style_avis.css";
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Ajouter un avis - IUTables</title>
    <link rel="stylesheet" href="<?php echo $cssPath; ?>style.css">
</head>
<body>
    <header>
        <div class="logo-container">
            <img src="<?php echo $cssPath; ?>logo.png" alt="IUTables">
            <h1>IUTables</h1>
        </div>
        <nav>
            <ul>
                <li><a href="decouvrir.php">Découvrir</a></li>
                <li><a href="favoris.php">Favoris</a></li>
                <li><a href="profil.php">Profil</a></li>
            </ul>
        </nav>
        <div class="user-profile">
            <a href="profil.php" class="profile-button">John DOE</a>
        </div>
    </header>

    <main class="ajouter-avis">
        <h1>Ajouter un avis</h1>
        
        <form method="post" action="traitement_avis.php" class="avis-form">
            <div class="form-container">
                <h2>Personnalisez vos préférences</h2>
                
                <div class="form-group">
                    <label for="notation">Donnez une notation</label>
                    <div class="rating">
                        <input type="radio" id="star5" name="rating" value="5" />
                        <label for="star5"></label>
                        <input type="radio" id="star4" name="rating" value="4" />
                        <label for="star4"></label>
                        <input type="radio" id="star3" name="rating" value="3" checked />
                        <label for="star3"></label>
                        <input type="radio" id="star2" name="rating" value="2" />
                        <label for="star2"></label>
                        <input type="radio" id="star1" name="rating" value="1" />
                        <label for="star1"></label>
                    </div>
                </div>
                
                <div class="form-group">
                    <label for="titre">Donnez un titre</label>
                    <input type="text" id="titre" name="titre" placeholder="Titre de l'avis" required>
                </div>
                
                <div class="form-group">
                    <label for="avis">Rédigez votre avis</label>
                    <textarea id="avis" name="avis" rows="5" placeholder="Avis" required></textarea>
                </div>
                
                <div class="form-actions">
                    <button type="submit" class="btn-submit">Publier l'avis</button>
                    <button type="button" class="btn-cancel">Annuler</button>
                </div>
            </div>
        </form>
    </main>
    
    <footer>
        <p>&copy; <?php echo date('Y'); ?> IUTables - Tous droits réservés</p>
    </footer>
</body>
</html>