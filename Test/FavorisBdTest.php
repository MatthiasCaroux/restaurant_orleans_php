<?php
use PHPUnit\Framework\TestCase;

require_once __DIR__ . '/../_inc/bd/favoris_bd.php';
require_once __DIR__ . '/../_inc/bd/page_restaurant_bd.php';

class FavorisBdTest extends TestCase {
    private $pdo;
    private $userId = 1;
    private $secondUserId = 2;
    private $restaurantId = 1;
    private $secondRestaurantId = 2;

    protected function setUp(): void {
        // Configurer une connexion à la base de données pour les tests
        try {
            $host = 'aws-0-us-west-1.pooler.supabase.com'; // Hôte pour le Transaction Pooler
            $port = '6543'; // Port pour le Transaction Pooler
            $dbname = 'postgres'; // Nom de la base (par défaut "postgres")
            $user = 'postgres.lmlcsjxhreswvnrdvhpp'; // Nom d'utilisateur 
            $password = 'faitleloup'; // Mot de passe Supabase

            $dsn = "pgsql:host=$host;port=$port;dbname=$dbname;sslmode=require";
            $this->pdo = new PDO($dsn, $user, $password);
            $this->pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

            // Clean up test data first
            $this->pdo->exec('DELETE FROM "Appreciation" WHERE id_restaurant IN (1, 2)');
            $this->pdo->exec('DELETE FROM "Restaurant" WHERE id_restaurant IN (1, 2)');

            // Insert the test restaurants
            $sql = 'INSERT INTO "Restaurant" (
                      id_restaurant, 
                      nom_restaurant, 
                      type_restaurant) 
                    VALUES (
                      1, 
                      \'Test Restaurant\', 
                      \'Test Type\')';
            $this->pdo->exec($sql);
            
            $sql = 'INSERT INTO "Restaurant" (
                      id_restaurant, 
                      nom_restaurant, 
                      type_restaurant) 
                    VALUES (
                      2, 
                      \'Second Test Restaurant\', 
                      \'Test Type 2\')';
            $this->pdo->exec($sql);
            
            // Nettoyer toutes les entrées d'appréciation pour cet utilisateur
            $this->pdo->exec('DELETE FROM "Appreciation" WHERE id_utilisateur = ' . $this->userId);
            $this->pdo->exec('DELETE FROM "Appreciation" WHERE id_utilisateur = ' . $this->secondUserId);
            
        } catch (PDOException $e) {
            die("Erreur de connexion à la base de données : " . $e->getMessage());
        }
    }

    protected function tearDown(): void {
        // Nettoyer après les tests
        try {
            $this->pdo->exec('DELETE FROM "Appreciation" WHERE id_restaurant IN (1, 2)');
            $this->pdo->exec('DELETE FROM "Restaurant" WHERE id_restaurant IN (1, 2)');
            $this->pdo->exec('DELETE FROM "Appreciation" WHERE id_utilisateur = ' . $this->userId);
            $this->pdo->exec('DELETE FROM "Appreciation" WHERE id_utilisateur = ' . $this->secondUserId);
        } catch (PDOException $e) {
            // Ignorer les erreurs lors du nettoyage
        }
    }

    public function testAddRestaurantToFavorites() {
        $result = addRestaurantToFavorites($this->userId, $this->restaurantId);
        $this->assertTrue($result);

        $stmt = $this->pdo->query('SELECT * FROM "Appreciation" WHERE id_utilisateur = ' . $this->userId . ' AND id_restaurant = ' . $this->restaurantId);
        $appreciation = $stmt->fetch(PDO::FETCH_ASSOC);

        $this->assertNotNull($appreciation);
        $this->assertTrue((bool)$appreciation['Favoris']);
    }

    public function testAddRestaurantToFavoritesWhenAlreadyFavorite() {
        // Ajouter d'abord aux favoris
        addRestaurantToFavorites($this->userId, $this->restaurantId);
        
        // Essayer d'ajouter à nouveau
        $result = addRestaurantToFavorites($this->userId, $this->restaurantId);
        $this->assertTrue($result);
        
        // Vérifier qu'il n'y a qu'une seule entrée
        $stmt = $this->pdo->query('SELECT COUNT(*) FROM "Appreciation" WHERE id_utilisateur = ' . $this->userId . ' AND id_restaurant = ' . $this->restaurantId);
        $count = $stmt->fetchColumn();
        $this->assertEquals(1, $count);
    }

    public function testAddRestaurantToFavoritesWhenAlreadyInAppreciationButNotFavorite() {
        // Crée une entrée Appreciation avec Favoris = false
        $sql = 'INSERT INTO "Appreciation" (id_utilisateur, id_restaurant, "Aimer", "Favoris") VALUES (?, ?, false, false)';
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([$this->userId, $this->restaurantId]);
        
        // Maintenant on ajoute aux favoris
        $result = addRestaurantToFavorites($this->userId, $this->restaurantId);
        $this->assertTrue($result);
        
        // Vérifier que c'est maintenant favoris
        $stmt = $this->pdo->query('SELECT "Favoris" FROM "Appreciation" WHERE id_utilisateur = ' . $this->userId . ' AND id_restaurant = ' . $this->restaurantId);
        $isFavorite = $stmt->fetchColumn();
        $this->assertTrue((bool)$isFavorite);
    }

    public function testRemoveRestaurantFromFavorites() {
        addRestaurantToFavorites($this->userId, $this->restaurantId);
        $result = removeRestaurantFromFavorites($this->userId, $this->restaurantId);
        $this->assertTrue($result);

        $stmt = $this->pdo->query('SELECT "Favoris" FROM "Appreciation" WHERE id_utilisateur = ' . $this->userId . ' AND id_restaurant = ' . $this->restaurantId);
        $isFavorite = $stmt->fetchColumn();
        $this->assertFalse((bool)$isFavorite);
    }

    public function testRemoveRestaurantFromFavoritesWhenNotExisting() {
        // Tenter de supprimer un restaurant qui n'est pas dans les favoris
        $result = removeRestaurantFromFavorites($this->userId, 9999);
        $this->assertFalse($result);
    }

    public function testIsRestaurantFavorite() {
        addRestaurantToFavorites($this->userId, $this->restaurantId);
        $isFavorite = isRestaurantFavorite($this->userId, $this->restaurantId);
        $this->assertTrue($isFavorite);

        removeRestaurantFromFavorites($this->userId, $this->restaurantId);
        $isFavorite = isRestaurantFavorite($this->userId, $this->restaurantId);
        $this->assertFalse($isFavorite);
    }

    public function testIsRestaurantFavoriteWithNonExistingRecord() {
        $isFavorite = isRestaurantFavorite($this->userId, 9999);
        $this->assertFalse($isFavorite);
    }

    public function testGetFavoritesForUser() {
        // S'assurer qu'il n'y a pas de favoris préexistants pour cet utilisateur
        $this->pdo->exec('DELETE FROM "Appreciation" WHERE id_utilisateur = ' . $this->userId);
        
        // Ajouter des restaurants aux favoris
        addRestaurantToFavorites($this->userId, $this->restaurantId);
        addRestaurantToFavorites($this->userId, $this->secondRestaurantId);
        
        // Récupérer les favoris
        $favorites = getFavoritesForUser($this->userId);
        
        // Vérifier le résultat
        $this->assertIsArray($favorites);
        $this->assertCount(2, $favorites);
        
        // Vérifier que les deux restaurants sont présents
        $restaurantIds = array_column($favorites, 'id_restaurant');
        $this->assertContains($this->restaurantId, $restaurantIds);
        $this->assertContains($this->secondRestaurantId, $restaurantIds);
    }

    public function testGetFavoritesForUserWithNoFavorites() {
        // S'assurer qu'il n'y a pas de favoris préexistants pour cet utilisateur
        $this->pdo->exec('DELETE FROM "Appreciation" WHERE id_utilisateur = ' . $this->secondUserId);
        
        // Récupérer les favoris pour un utilisateur qui n'en a pas
        $favorites = getFavoritesForUser($this->secondUserId);
        
        // Vérifier que c'est un tableau vide
        $this->assertIsArray($favorites);
        $this->assertEmpty($favorites);
    }

    public function testAddMultipleRestaurantsToFavorites() {
        // Ajouter deux restaurants différents aux favoris
        $result1 = addRestaurantToFavorites($this->userId, $this->restaurantId);
        $result2 = addRestaurantToFavorites($this->userId, $this->secondRestaurantId);
        
        $this->assertTrue($result1);
        $this->assertTrue($result2);
        
        // Vérifier que les deux sont en favoris
        $isFavorite1 = isRestaurantFavorite($this->userId, $this->restaurantId);
        $isFavorite2 = isRestaurantFavorite($this->userId, $this->secondRestaurantId);
        
        $this->assertTrue($isFavorite1);
        $this->assertTrue($isFavorite2);
    }

    public function testAddToFavoritesAfterLiking() {
        // D'abord aimer
        $sql = 'INSERT INTO "Appreciation" (id_utilisateur, id_restaurant, "Aimer", "Favoris") VALUES (?, ?, true, false)';
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([$this->userId, $this->restaurantId]);
        
        // Ensuite ajouter aux favoris
        $result = addRestaurantToFavorites($this->userId, $this->restaurantId);
        $this->assertTrue($result);
        
        // Vérifier qu'on a toujours le j'aime et maintenant aussi un favoris
        $stmt = $this->pdo->query('SELECT "Aimer", "Favoris" FROM "Appreciation" WHERE id_utilisateur = ' . $this->userId . ' AND id_restaurant = ' . $this->restaurantId);
        $appreciation = $stmt->fetch(PDO::FETCH_ASSOC);
        
        $this->assertTrue((bool)$appreciation['Aimer']);
        $this->assertTrue((bool)$appreciation['Favoris']);
    }

    public function testExceptionHandlingInGetFavoritesForUser() {
        // Test avec un ID invalide pour provoquer une erreur
        // Remarque: ce test pourrait échouer selon la façon dont votre base de données traite les erreurs
        $favorites = getFavoritesForUser("invalid_id");
        $this->assertEmpty($favorites);  // Devrait retourner tableau vide en cas d'erreur
    }
}
?>