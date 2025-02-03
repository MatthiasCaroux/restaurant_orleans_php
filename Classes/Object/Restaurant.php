<?php
class Restaurant {
    protected int $id_restaurant;
    protected string $type_restaurant;
    protected string $nom;
    protected string $telephone;
    protected string $site_restaurant;
    protected string $departement;
    protected int $code_departement;
    function __construct(int $id_restaurant, string $type_restaurant,
                    string $nom, string $telephone, string $site_restaurant,
                    int $departement, int $code_departement) {

                $this->id_restaurant = $id_restaurant;
                $this->type_restaurant = $type_restaurant;
                $this->nom = $nom;                    $this->telephone = $telephone;
                $this->site_restaurant = $site_restaurant;
                $this->departement = $departement;
                $this->code_departement = $code_departement;
            }
    function render(): string {
        return "Restaurant : $this->nom";
    }
}