<?php
use PHPUnit\Framework\TestCase;

require_once __DIR__ . '/../_inc/bd/favoris_bd.php';
require_once __DIR__ . '/../_inc/bd/page_restaurant_bd.php';

class FavorisBdTest extends TestCase {
    private $pdo;
    private $userId = 1;
    private $restaurantId = 1;

    protected function setUp(): void {
        // Configurer une connexion à la base de données pour les tests
        try {
            $host = 'aws-0-us-west-1.pooler.supabase.com'; // Hôte pour le Transaction Pooler
            $port = '6543'; // Port pour le Transaction Pooler
            $dbname = 'postgres'; // Nom de la base (par défaut "postgres")
            $user = 'postgres.lmlcsjxhreswvnrdvhpp'; // Nom d'utilisateur (ajoute ton identifiant unique)
            $password = 'faitleloup'; // Remplace par ton mot de passe Supabase

            $dsn = "pgsql:host=$host;port=$port;dbname=$dbname;sslmode=require";
            $this->pdo = new PDO($dsn, $user, $password);
            $this->pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

            // Get the structure of the Restaurant table
            $stmt = $this->pdo->query("SELECT column_name, is_nullable 
                                       FROM information_schema.columns 
                                       WHERE table_name = 'Restaurant'
                                       ORDER BY ordinal_position");
            $columns = $stmt->fetchAll(PDO::FETCH_ASSOC);

            // Clean up test data first
            $this->pdo->exec('DELETE FROM "Appreciation" WHERE id_restaurant = 1');
            $this->pdo->exec('DELETE FROM "Restaurant" WHERE id_restaurant = 1');

            // Insert the test restaurant with all required fields
            $sql = 'INSERT INTO "Restaurant" (
                      id_restaurant, 
                      nom_restaurant, 
                      type_restaurant) 
                    VALUES (
                      1, 
                      \'Test Restaurant\', 
                      \'Test Type\')';
            $this->pdo->exec($sql);

            echo "Connexion réussie ! 🚀";
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

    public function testAddRestaurantToFavorites() {
        $result = addRestaurantToFavorites($this->userId, $this->restaurantId);
        $this->assertTrue($result);

        $stmt = $this->pdo->query('SELECT * FROM "Appreciation" WHERE id_utilisateur = ' . $this->userId . ' AND id_restaurant = ' . $this->restaurantId);
        $appreciation = $stmt->fetch(PDO::FETCH_ASSOC);

        $this->assertNotNull($appreciation);
        $this->assertTrue((bool)$appreciation['Favoris']);
    }

    public function testRemoveRestaurantFromFavorites() {
        addRestaurantToFavorites($this->userId, $this->restaurantId);
        $result = removeRestaurantFromFavorites($this->userId, $this->restaurantId);
        $this->assertTrue($result);

        $stmt = $this->pdo->query('SELECT * FROM "Appreciation" WHERE id_utilisateur = ' . $this->userId . ' AND id_restaurant = ' . $this->restaurantId);
        $appreciation = $stmt->fetch(PDO::FETCH_ASSOC);

        $this->assertFalse((bool)$appreciation['Favoris']);
    }

    public function testIsRestaurantFavorite() {
        addRestaurantToFavorites($this->userId, $this->restaurantId);
        $isFavorite = isRestaurantFavorite($this->userId, $this->restaurantId);
        $this->assertTrue($isFavorite);

        removeRestaurantFromFavorites($this->userId, $this->restaurantId);
        $isFavorite = isRestaurantFavorite($this->userId, $this->restaurantId);
        $this->assertFalse($isFavorite);
    }
}
?>