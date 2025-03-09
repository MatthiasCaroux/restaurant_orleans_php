<?php
use PHPUnit\Framework\TestCase;

require_once __DIR__ . '/../_inc/bd/favoris_bd.php';
require_once __DIR__ . '/../_inc/bd/page_restaurant_bd.php';

class LikeTest extends TestCase {
    private $pdo;
    private $userId = 1;
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
            
        } catch (PDOException $e) {
            die("Erreur de connexion à la base de données : " . $e->getMessage());
        }
    }

    protected function tearDown(): void {
        // Nettoyer après les tests
        try {
            $this->pdo->exec('DELETE FROM "Appreciation" WHERE id_restaurant IN (1, 2)');
            $this->pdo->exec('DELETE FROM "Restaurant" WHERE id_restaurant IN (1, 2)');
        } catch (PDOException $e) {
            // Ignorer les erreurs lors du nettoyage
        }
    }

    public function testAddRestaurantToLiked() {
        $result = addRestaurantToLiked($this->userId, $this->restaurantId);
        $this->assertTrue($result);

        $stmt = $this->pdo->query('SELECT * FROM "Appreciation" WHERE id_utilisateur = ' . $this->userId . ' AND id_restaurant = ' . $this->restaurantId);
        $appreciation = $stmt->fetch(PDO::FETCH_ASSOC);

        $this->assertNotNull($appreciation);
        $this->assertTrue((bool)$appreciation['Aimer']);
    }

    public function testAddRestaurantToLikedWhenAlreadyLiked() {
        addRestaurantToLiked($this->userId, $this->restaurantId);
        
        $result = addRestaurantToLiked($this->userId, $this->restaurantId);
        $this->assertTrue($result);
        
        $stmt = $this->pdo->query('SELECT COUNT(*) FROM "Appreciation" WHERE id_utilisateur = ' . $this->userId . ' AND id_restaurant = ' . $this->restaurantId);
        $count = $stmt->fetchColumn();
        $this->assertEquals(1, $count);
    }

    public function testAddRestaurantToLikedWhenAlreadyInAppreciationButNotLiked() {
        $sql = 'INSERT INTO "Appreciation" (id_utilisateur, id_restaurant, "Aimer", "Favoris") VALUES (?, ?, false, false)';
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([$this->userId, $this->restaurantId]);
        
        $result = addRestaurantToLiked($this->userId, $this->restaurantId);
        $this->assertTrue($result);
        
        $stmt = $this->pdo->query('SELECT "Aimer" FROM "Appreciation" WHERE id_utilisateur = ' . $this->userId . ' AND id_restaurant = ' . $this->restaurantId);
        $isLiked = $stmt->fetchColumn();
        $this->assertTrue((bool)$isLiked);
    }

    public function testRemoveRestaurantFromLiked() {
        addRestaurantToLiked($this->userId, $this->restaurantId);
        $result = removeRestaurantFromLiked($this->userId, $this->restaurantId);
        $this->assertTrue($result);

        $stmt = $this->pdo->query('SELECT * FROM "Appreciation" WHERE id_utilisateur = ' . $this->userId . ' AND id_restaurant = ' . $this->restaurantId);
        $appreciation = $stmt->fetch(PDO::FETCH_ASSOC);

        $this->assertFalse((bool)$appreciation['Aimer']);
    }

    public function testRemoveRestaurantFromLikedWhenNotExisting() {
        $result = removeRestaurantFromLiked($this->userId, $this->restaurantId);
        $this->assertFalse($result);
    }

    public function testIsRestaurantLiked() {
        addRestaurantToLiked($this->userId, $this->restaurantId);
        $isLiked = isRestaurantLiked($this->userId, $this->restaurantId);
        $this->assertTrue($isLiked);

        removeRestaurantFromLiked($this->userId, $this->restaurantId);
        $isLiked = isRestaurantLiked($this->userId, $this->restaurantId);
        $this->assertFalse($isLiked);
    }

    public function testIsRestaurantLikedWithNonExistingRecord() {
        $isLiked = isRestaurantLiked($this->userId, $this->restaurantId);
        $this->assertFalse($isLiked);
    }

    public function testLikeMultipleRestaurants() {
        $result1 = addRestaurantToLiked($this->userId, $this->restaurantId);
        $result2 = addRestaurantToLiked($this->userId, $this->secondRestaurantId);
        
        $this->assertTrue($result1);
        $this->assertTrue($result2);
        
        $isLiked1 = isRestaurantLiked($this->userId, $this->restaurantId);
        $isLiked2 = isRestaurantLiked($this->userId, $this->secondRestaurantId);
        
        $this->assertTrue($isLiked1);
        $this->assertTrue($isLiked2);
    }

    public function testLikeAfterAddingToFavorites() {
        $sql = 'INSERT INTO "Appreciation" (id_utilisateur, id_restaurant, "Aimer", "Favoris") VALUES (?, ?, false, true)';
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([$this->userId, $this->restaurantId]);
        
        $result = addRestaurantToLiked($this->userId, $this->restaurantId);
        $this->assertTrue($result);
        
        $stmt = $this->pdo->query('SELECT "Aimer", "Favoris" FROM "Appreciation" WHERE id_utilisateur = ' . $this->userId . ' AND id_restaurant = ' . $this->restaurantId);
        $appreciation = $stmt->fetch(PDO::FETCH_ASSOC);
        
        $this->assertTrue((bool)$appreciation['Aimer']);
        $this->assertTrue((bool)$appreciation['Favoris']);
    }

    public function testRemoveNonExistentLike() {
        $result = removeRestaurantFromLiked($this->userId, 9999);
        $this->assertFalse($result);
    }

    public function testExceptionHandlingInIsRestaurantLiked() {
        $isLiked = isRestaurantLiked($this->userId, "invalid_id");
        $this->assertFalse($isLiked);
    }
    
    public function testAddRestaurantToLikedWithInvalidInputs() {
        $result = addRestaurantToLiked('invalid_user', $this->restaurantId);
        $this->assertFalse($result);
        
        $result = addRestaurantToLiked($this->userId, 'invalid_id');
        $this->assertFalse($result);
    }
    
    public function testRemoveRestaurantFromLikedWithInvalidInputs() {
        $result = removeRestaurantFromLiked('invalid_user', $this->restaurantId);
        $this->assertFalse($result);
        
        $result = removeRestaurantFromLiked($this->userId, 'invalid_id');
        $this->assertFalse($result);
    }
    
    public function testIsRestaurantLikedWithInvalidInputs() {
        $result = isRestaurantLiked('invalid_user', $this->restaurantId);
        $this->assertFalse($result);
        
        $result = isRestaurantLiked($this->userId, 'invalid_id');
        $this->assertFalse($result);
    }
    
    public function testIsRestaurantLikedWithSQLInjection() {
        $result = isRestaurantLiked($this->userId, "1; DROP TABLE \"Appreciation\";");
        $this->assertFalse($result);
    }
}
?>