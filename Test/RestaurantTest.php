<?php

use PHPUnit\Framework\TestCase;

require_once __DIR__ . '/../Classes/Object/Restaurant.php';

class RestaurantTest extends TestCase
{
    private Restaurant $restaurant;

    protected function setUp(): void
    {
        // Arrange: créer une instance de Restaurant pour les tests
        $this->restaurant = new Restaurant(
            1,                     // id_restaurant
            'restaurant',          // type_restaurant
            'Pasta Bella',         // nom
            '01 23 45 67 89',      // telephone
            'www.pastabella.fr',   // site_restaurant
            "Loiret",              // departement
            45,                    // code_departement
            'yes',                 // wheelchair
            'no'                   // vegetarian
        );
    }

    public function testRestaurantInitialization(): void
    {
        // Assert: vérifier que l'objet a bien été créé
        $this->assertInstanceOf(Restaurant::class, $this->restaurant);
    }

    public function testRenderMethod(): void
    {
        // Act: appeler la méthode render
        $result = $this->restaurant->render();

        // Assert: vérifier que le résultat est correct
        $this->assertEquals('Restaurant : Pasta Bella', $result);
    }

    // Si vous ajoutez des getters, vous pourriez les tester comme ceci
    // public function testGetNom(): void
    // {
    //     $this->assertEquals('Pasta Bella', $this->restaurant->getNom());
    // }
}