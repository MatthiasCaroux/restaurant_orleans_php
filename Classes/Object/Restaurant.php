<?php
class Restaurant {
    protected int $id_restaurant;
    protected string $type_restaurant;
    protected string $nom;
    protected string $telephone;
    protected string $site_restaurant;
    protected string $departement;
    protected int $code_departement;
    protected string $wheelchair;
    protected string $vegetarian;
    function __construct(int $id_restaurant, string $type_restaurant,
                    string $nom, string $telephone, string $site_restaurant,
                    int $departement, int $code_departement, string $wheelchair, string $vegetarian) {

                $this->id_restaurant = $id_restaurant;
                $this->type_restaurant = $type_restaurant;
                $this->nom = $nom;                    $this->telephone = $telephone;
                $this->site_restaurant = $site_restaurant;
                $this->departement = $departement;
                $this->code_departement = $code_departement;
                $this->wheelchair = $wheelchair;
                $this->vegetarian = $vegetarian;
            }
    function render(): string {
        return "Restaurant : $this->nom";
    }
}