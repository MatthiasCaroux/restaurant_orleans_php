<?php
use PHPUnit\Framework\TestCase;

require_once __DIR__ . '/../_inc/bd/favoris_bd.php';
require_once __DIR__ . '/../_inc/bd/page_restaurant_bd.php';

class likeTest extends TestCase {
    private $pdo;
    private $userId = 1;
    private $restaurantId = 1;

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
            $this->pdo->exec('DELETE FROM "Appreciation" WHERE id_restaurant = 1');
            $this->pdo->exec('DELETE FROM "Restaurant" WHERE id_restaurant = 1');

            // Insert the test restaurant
            $sql = 'INSERT INTO "Restaurant" (
                      id_restaurant, 
                      nom_restaurant, 
                      type_restaurant) 
                    VALUES (
                      1, 
                      \'Test Restaurant\', 
                      \'Test Type\')';
            $this->pdo->exec($sql);
        } catch (PDOException $e) {
            die("Erreur de connexion à la base de données : " . $e->getMessage());
        }
    }

    protected function tearDown(): void {
        // Nettoyer après les tests
        try {
            $this->pdo->exec('DELETE FROM "Appreciation" WHERE id_restaurant = 1');
            $this->pdo->exec('DELETE FROM "Restaurant" WHERE id_restaurant = 1');
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

    public function testRemoveRestaurantFromLiked() {
        addRestaurantToLiked($this->userId, $this->restaurantId);
        $result = removeRestaurantFromLiked($this->userId, $this->restaurantId);
        $this->assertTrue($result);

        $stmt = $this->pdo->query('SELECT * FROM "Appreciation" WHERE id_utilisateur = ' . $this->userId . ' AND id_restaurant = ' . $this->restaurantId);
        $appreciation = $stmt->fetch(PDO::FETCH_ASSOC);

        $this->assertFalse((bool)$appreciation['Aimer']);
    }

    public function testIsRestaurantLiked() {
        addRestaurantToLiked($this->userId, $this->restaurantId);
        $isLiked = isRestaurantLiked($this->userId, $this->restaurantId);
        $this->assertTrue($isLiked);

        removeRestaurantFromLiked($this->userId, $this->restaurantId);
        $isLiked = isRestaurantLiked($this->userId, $this->restaurantId);
        $this->assertFalse($isLiked);
    }
}
?>