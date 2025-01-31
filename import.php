<!-- Ce fichier nous permet de recupérer les infos du JSON -->

<?php

$json = file_get_contents('./_inc/data/restaurants_orleans.json');
$data = json_decode($json, true);



//Maintenant on va faire ceci pour chaque élément du json
//On va créer une boucle pour parcourir tout le json avec tous les éléments
for ($i=0; $i<count($data); $i++) {
    $geo_point_2d     = $data[$i]['geo_point_2d'];
    $nom              = $data[$i]['name'];
    $osm_id           = $data[$i]['osm_id'];
    $type             = $data[$i]['type'];
    $name             = $data[$i]['name'];
    $operator         = $data[$i]['operator'];
    $brand            = $data[$i]['brand'];
    $opening_hours    = $data[$i]['opening_hours'];
    $wheelchair       = $data[$i]['wheelchair'];
    $cuisine          = $data[$i]['cuisine'];
    $vegetarian       = $data[$i]['vegetarian'];
    $vegan            = $data[$i]['vegan'];
    $delivery         = $data[$i]['delivery'];
    $takeaway         = $data[$i]['takeaway'];
    $internet_access  = $data[$i]['internet_access'];
    $stars            = $data[$i]['stars'];
    $capacity         = $data[$i]['capacity'];
    $drive_through    = $data[$i]['drive_through'];
    $wikidata         = $data[$i]['wikidata'];
    $brand_wikidata   = $data[$i]['brand_wikidata'];
    $website          = $data[$i]['website'];
    $facebook         = $data[$i]['facebook'];
    $smoking          = $data[$i]['smoking'];
    $com_insee        = $data[$i]['com_insee'];
    $com_nom          = $data[$i]['com_nom'];
    $region           = $data[$i]['region'];
    $code_region      = $data[$i]['code_region'];
    $departement      = $data[$i]['departement'];
    $code_departement = $data[$i]['code_departement'];
    $commune          = $data[$i]['commune'];
    $code_commune     = $data[$i]['code_commune'];
    $osm_edit         = $data[$i]['osm_edit'];
    print_r($nom);
}


//Pour plus de simplicitée tu peux aussi utilisé le dico data stv samuel c cadeau
print_r($data);
// si tu veux le premier restau :
print_r($data[0]);
// et si tu veux l'attribut nom du premier restau : 
$nom = $data[0]['name'];
// voilou des bisous bon courage


?>