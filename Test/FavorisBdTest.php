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
        try {
            $this->pdo->exec('DELETE FROM "Appreciation" WHERE id_restaurant IN (1, 2)');
            $this->pdo->exec('DELETE FROM "Restaurant" WHERE id_restaurant IN (1, 2)');
            $this->pdo->exec('DELETE FROM "Appreciation" WHERE id_utilisateur = ' . $this->userId);
            $this->pdo->exec('DELETE FROM "Appreciation" WHERE id_utilisateur = ' . $this->secondUserId);
        } catch (PDOException $e) {
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

    public function testAddRestaurantToFavoritesWithInvalidInputs() {
        $result1 = addRestaurantToFavorites('abc', $this->restaurantId);
        $this->assertFalse($result1);
        
        $result2 = addRestaurantToFavorites($this->userId, 'xyz');
        $this->assertFalse($result2);
        
        $result3 = addRestaurantToFavorites(null, $this->restaurantId);
        $this->assertFalse($result3);
        
        $result4 = addRestaurantToFavorites($this->userId, null);
        $this->assertFalse($result4);
    }

    public function testAddRestaurantToFavoritesCatchBlock() {
        $mockPdo = $this->getMockBuilder(PDO::class)
            ->disableOriginalConstructor()
            ->getMock();
            
        $mockStatement = $this->getMockBuilder(PDOStatement::class)
            ->getMock();
            
        $mockPdo->method('prepare')
            ->willReturn($mockStatement);
            
        $mockStatement->method('execute')
            ->will($this->throwException(new PDOException('Test exception')));
            
        global $testMode, $mockPdoToUse;
        $testMode = true;
        $mockPdoToUse = $mockPdo;
        
        $result = addRestaurantToFavorites($this->userId, $this->restaurantId);
        $this->assertFalse($result);
        
        $testMode = false;
        $mockPdoToUse = null;
    }

    public function testAddRestaurantToFavoritesWhenAlreadyFavorite() {
        addRestaurantToFavorites($this->userId, $this->restaurantId);
        
        $result = addRestaurantToFavorites($this->userId, $this->restaurantId);
        $this->assertTrue($result);
        
        $stmt = $this->pdo->query('SELECT COUNT(*) FROM "Appreciation" WHERE id_utilisateur = ' . $this->userId . ' AND id_restaurant = ' . $this->restaurantId);
        $count = $stmt->fetchColumn();
        $this->assertEquals(1, $count);
    }

    public function testAddRestaurantToFavoritesWhenAlreadyInAppreciationButNotFavorite() {
        $sql = 'INSERT INTO "Appreciation" (id_utilisateur, id_restaurant, "Aimer", "Favoris") VALUES (?, ?, false, false)';
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([$this->userId, $this->restaurantId]);
        
        $result = addRestaurantToFavorites($this->userId, $this->restaurantId);
        $this->assertTrue($result);
        
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

    public function testRemoveRestaurantFromFavoritesWithInvalidInputs() {
        $result1 = removeRestaurantFromFavorites('abc', $this->restaurantId);
        $this->assertFalse($result1);
        
        $result2 = removeRestaurantFromFavorites($this->userId, 'xyz');
        $this->assertFalse($result2);
        
        $result3 = removeRestaurantFromFavorites(null, $this->restaurantId);
        $this->assertFalse($result3);
    }

    public function testRemoveRestaurantFromFavoritesCatchBlock() {
        $mockPdo = $this->getMockBuilder(PDO::class)
            ->disableOriginalConstructor()
            ->getMock();
            
        $mockStatement = $this->getMockBuilder(PDOStatement::class)
            ->getMock();
            
        $mockPdo->method('prepare')
            ->willReturn($mockStatement);
            
        $mockStatement->method('execute')
            ->will($this->throwException(new PDOException('Test exception')));
            
        global $testMode, $mockPdoToUse;
        $testMode = true;
        $mockPdoToUse = $mockPdo;
        
        $result = removeRestaurantFromFavorites($this->userId, $this->restaurantId);
        $this->assertFalse($result);
        
        $testMode = false;
        $mockPdoToUse = null;
    }

    public function testRemoveRestaurantFromFavoritesWhenNotExisting() {
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

    public function testIsRestaurantFavoriteWithInvalidInputs() {
        $result1 = isRestaurantFavorite('abc', $this->restaurantId);
        $this->assertFalse($result1);
        
        $result2 = isRestaurantFavorite($this->userId, 'xyz');
        $this->assertFalse($result2);
        
        $result3 = isRestaurantFavorite(null, $this->restaurantId);
        $this->assertFalse($result3);
    }

    public function testIsRestaurantFavoriteCatchBlock() {
        $mockPdo = $this->getMockBuilder(PDO::class)
            ->disableOriginalConstructor()
            ->getMock();
            
        $mockStatement = $this->getMockBuilder(PDOStatement::class)
            ->getMock();
            
        $mockPdo->method('prepare')
            ->willReturn($mockStatement);
            
        $mockStatement->method('execute')
            ->will($this->throwException(new PDOException('Test exception')));
            
        global $testMode, $mockPdoToUse;
        $testMode = true;
        $mockPdoToUse = $mockPdo;
        
        $result = isRestaurantFavorite($this->userId, $this->restaurantId);
        $this->assertFalse($result);
        
        $testMode = false;
        $mockPdoToUse = null;
    }

    public function testIsRestaurantFavoriteWithNonExistingRecord() {
        $isFavorite = isRestaurantFavorite($this->userId, 9999);
        $this->assertFalse($isFavorite);
    }

    public function testGetFavoritesForUser() {
        $this->pdo->exec('DELETE FROM "Appreciation" WHERE id_utilisateur = ' . $this->userId);
        
        addRestaurantToFavorites($this->userId, $this->restaurantId);
        addRestaurantToFavorites($this->userId, $this->secondRestaurantId);
        
        $favorites = getFavoritesForUser($this->userId);
        
        $this->assertIsArray($favorites);
        $this->assertCount(2, $favorites);
        
        $restaurantIds = array_column($favorites, 'id_restaurant');
        $this->assertContains($this->restaurantId, $restaurantIds);
        $this->assertContains($this->secondRestaurantId, $restaurantIds);
    }

    public function testGetFavoritesForUserWithInvalidInput() {
        $favorites = getFavoritesForUser('abc');
        $this->assertIsArray($favorites);
        $this->assertEmpty($favorites);
        
        $favorites = getFavoritesForUser(null);
        $this->assertIsArray($favorites);
        $this->assertEmpty($favorites);
    }

    public function testGetFavoritesForUserCatchBlock() {
        $mockPdo = $this->getMockBuilder(PDO::class)
            ->disableOriginalConstructor()
            ->getMock();
            
        $mockStatement = $this->getMockBuilder(PDOStatement::class)
            ->getMock();
            
        $mockPdo->method('prepare')
            ->willReturn($mockStatement);
            
        $mockStatement->method('execute')
            ->will($this->throwException(new PDOException('Test exception')));
            
        global $testMode, $mockPdoToUse;
        $testMode = true;
        $mockPdoToUse = $mockPdo;
        
        $result = getFavoritesForUser($this->userId);
        $this->assertIsArray($result);
        $this->assertEmpty($result);
        
        $testMode = false;
        $mockPdoToUse = null;
    }

    public function testGetFavoritesForUserWithNoFavorites() {
        $this->pdo->exec('DELETE FROM "Appreciation" WHERE id_utilisateur = ' . $this->secondUserId);
        
        $favorites = getFavoritesForUser($this->secondUserId);
        
        $this->assertIsArray($favorites);
        $this->assertEmpty($favorites);
    }

    public function testAddMultipleRestaurantsToFavorites() {
        $result1 = addRestaurantToFavorites($this->userId, $this->restaurantId);
        $result2 = addRestaurantToFavorites($this->userId, $this->secondRestaurantId);
        
        $this->assertTrue($result1);
        $this->assertTrue($result2);
        
        $isFavorite1 = isRestaurantFavorite($this->userId, $this->restaurantId);
        $isFavorite2 = isRestaurantFavorite($this->userId, $this->secondRestaurantId);
        
        $this->assertTrue($isFavorite1);
        $this->assertTrue($isFavorite2);
    }

    public function testAddToFavoritesAfterLiking() {
        $sql = 'INSERT INTO "Appreciation" (id_utilisateur, id_restaurant, "Aimer", "Favoris") VALUES (?, ?, true, false)';
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([$this->userId, $this->restaurantId]);

        $result = addRestaurantToFavorites($this->userId, $this->restaurantId);
        $this->assertTrue($result);
        
        $stmt = $this->pdo->query('SELECT "Aimer", "Favoris" FROM "Appreciation" WHERE id_utilisateur = ' . $this->userId . ' AND id_restaurant = ' . $this->restaurantId);
        $appreciation = $stmt->fetch(PDO::FETCH_ASSOC);
        
        $this->assertTrue((bool)$appreciation['Aimer']);
        $this->assertTrue((bool)$appreciation['Favoris']);
    }
}