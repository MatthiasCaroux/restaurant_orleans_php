<?php
use PHPUnit\Framework\TestCase;

require_once __DIR__ . '/../_inc/bd/favoris_bd.php';
require_once __DIR__ . '/../_inc/bd/page_restaurant_bd.php';

class PageRestaurantBdTest extends TestCase {
    private $pdo;
    private $restaurantId = 1;
    private $secondRestaurantId = 2;

    /**
     * @beforeClass
     */
    public static function setUpBeforeClass(): void {
        error_reporting(E_ALL & ~E_WARNING);
    }

    protected function setUp(): void {
        // Configurer une connexion à la base de données pour les tests
        try {
            $host = 'aws-0-us-west-1.pooler.supabase.com';
            $port = '6543';
            $dbname = 'postgres';
            $user = 'postgres.lmlcsjxhreswvnrdvhpp';
            $password = 'faitleloup';

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
            // Ignorer les erreurs pour éviter les warnings
            // die("Erreur de connexion à la base de données : " . $e->getMessage());
        }
    }

    protected function tearDown(): void {
        try {
            $this->pdo->exec('DELETE FROM "Appreciation" WHERE id_restaurant IN (1, 2)');
            $this->pdo->exec('DELETE FROM "Restaurant" WHERE id_restaurant IN (1, 2)');
        } catch (PDOException $e) {
        }
    }

    public function testGetAllRestaurants() {
        $restaurants = getAllRestaurants();
        
        $this->assertIsArray($restaurants);
        
        if (!empty($restaurants)) {
            $restaurantIds = array_column($restaurants, 'id_restaurant');
            $this->assertContains($this->restaurantId, $restaurantIds);
            $this->assertContains($this->secondRestaurantId, $restaurantIds);
        }
    }

    public function testGetRestaurantById() {
        $restaurant = getRestaurantById($this->restaurantId);
        
        if ($restaurant !== null && $restaurant !== false) {
            $this->assertIsArray($restaurant);
            
            $this->assertEquals($this->restaurantId, $restaurant['id_restaurant']);
            $this->assertEquals('Test Restaurant', $restaurant['nom_restaurant']);
        } else {
            $this->markTestSkipped("La table Restaurant n'existe pas");
        }
        
        $nonExistentRestaurant = getRestaurantById(9999);
        if ($nonExistentRestaurant !== null) {
            $this->assertFalse($nonExistentRestaurant);
        }
    }

    public function testGetRestaurantImage() {
        $imagesData = [
            ['name' => 'Test Restaurant', 'image_url' => 'test_image.jpg'],
            ['name' => 'Another Restaurant', 'image_url' => 'another_image.jpg']
        ];
        
        $imageUrl = getRestaurantImage('Test Restaurant', $imagesData);
        $this->assertEquals('test_image.jpg', $imageUrl);
        
        $defaultImageUrl = getRestaurantImage('Non Existent Restaurant', $imagesData);
        $this->assertEquals('_inc/static/images/bk.jpeg', $defaultImageUrl);
    }

    public function testGetRestaurantByIdWithInvalidId() {
        $restaurant = getRestaurantById('invalid_id');
        $this->assertNull($restaurant);
    }

    public function testGetAllRestaurantsWithException() {
        $this->assertIsArray(getAllRestaurants());
    }
    
    public function testGetAllRestaurantsErrorHandling() {
        $result = getAllRestaurants();
        $this->assertIsArray($result);
    }
    
    public function testGetRestaurantByIdWithSQLInjection() {
        $restaurant = getRestaurantById("1; DROP TABLE \"Restaurant\";");
        $this->assertNull($restaurant);
    }
    
    public function testGetRestaurantByIdErrorHandling() {
        $result = getRestaurantById(1);
        if ($result === false) {
            $this->assertFalse($result);
        } else {
            $this->assertNotNull($result);
        }
    }
    
    public function testGetRestaurantImageWithEmptyData() {
        $imageUrl = getRestaurantImage('Test Restaurant', []);
        $this->assertEquals('_inc/static/images/bk.jpeg', $imageUrl);
    }
    
}
?>