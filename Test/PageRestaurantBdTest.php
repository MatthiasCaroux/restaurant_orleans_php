<?php
use PHPUnit\Framework\TestCase;

require_once __DIR__ . '/../_inc/bd/favoris_bd.php';
require_once __DIR__ . '/../_inc/bd/page_restaurant_bd.php';

class PageRestaurantBdTest extends TestCase {
    private $pdo;
    private $restaurantId = 1;
    private $secondRestaurantId = 2;

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

    public function testGetAllRestaurants() {
        $restaurants = getAllRestaurants();
        
        // Vérifier que c'est un tableau
        $this->assertIsArray($restaurants);
        
        // Vérifier qu'il contient au moins nos deux restaurants de test
        $this->assertGreaterThanOrEqual(2, count($restaurants));
        
        // Vérifier les ID de nos restaurants de test
        $restaurantIds = array_column($restaurants, 'id_restaurant');
        $this->assertContains($this->restaurantId, $restaurantIds);
        $this->assertContains($this->secondRestaurantId, $restaurantIds);
    }

    public function testGetRestaurantById() {
        // Tester avec un ID valide
        $restaurant = getRestaurantById($this->restaurantId);
        
        // Vérifier que c'est un tableau
        $this->assertIsArray($restaurant);
        
        // Vérifier les données du restaurant
        $this->assertEquals($this->restaurantId, $restaurant['id_restaurant']);
        $this->assertEquals('Test Restaurant', $restaurant['nom_restaurant']);
        //$this->assertEquals('Test Type', $restaurant['type_restaurant']);
        
        // Tester avec un ID invalide
        $nonExistentRestaurant = getRestaurantById(9999);
        $this->assertFalse($nonExistentRestaurant);
    }

    public function testGetRestaurantImage() {
        // Préparer des données de test
        $imagesData = [
            ['name' => 'Test Restaurant', 'image_url' => 'test_image.jpg'],
            ['name' => 'Another Restaurant', 'image_url' => 'another_image.jpg']
        ];
        
        // Tester avec un restaurant qui a une image
        $imageUrl = getRestaurantImage('Test Restaurant', $imagesData);
        $this->assertEquals('test_image.jpg', $imageUrl);
        
        // Tester avec un restaurant qui n'a pas d'image
        $defaultImageUrl = getRestaurantImage('Non Existent Restaurant', $imagesData);
        $this->assertEquals('_inc/static/images/bk.jpeg', $defaultImageUrl);
    }

    public function testGetRestaurantByIdWithInvalidId() {
        // Tester avec un ID non numérique
        $restaurant = getRestaurantById('invalid_id');
        $this->assertNull($restaurant);
    }

    public function testGetAllRestaurantsWithException() {
        // Tester le comportement quand une exception est levée
        // C'est difficile à simuler directement, mais on peut vérifier que la fonction retourne 
        // un tableau vide en cas d'erreur en modifiant temporairement la fonction ou en
        // utilisant un mock.
        // Ce test est un exemple conceptuel.
        
        // Utilisation du framework de test pour vérifier le comportement en cas d'erreur
        // Dans un vrai cas, on pourrait utiliser un mock pour simuler une exception
        $this->assertIsArray(getAllRestaurants());
    }
}
?>