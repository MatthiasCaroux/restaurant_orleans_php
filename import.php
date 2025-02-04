<!-- Ce fichier nous permet de recupérer les infos du JSON -->

<?php
require __DIR__ ."/_inc/bd/db.php";
$json = file_get_contents('./_inc/data/restaurants_orleans.json');
$data = json_decode($json, true);
//Maintenant on va faire ceci pour chaque élément du json
//On va créer une boucle pour parcourir tout le json avec tous les éléments



try {
    // Définir les variables AVANT d'utiliser $dsn
    $host = 'aws-0-us-west-1.pooler.supabase.com';  
    $port = '6543'; 
    $dbname = 'postgres'; 
    $user = 'postgres.lmlcsjxhreswvnrdvhpp'; 
    $password = 'faitleloup'; 

    // Configuration de la connexion
    $dsn = "pgsql:host=$host;port=$port;dbname=$dbname;sslmode=require";
    $pdo = new PDO($dsn, $user, $password);

    // Configurer PDO pour lever des exceptions en cas d'erreur
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    echo "Connexion réussie ! 🚀";
} catch (PDOException $e) {
    die("Erreur de connexion à la base de données : " . $e->getMessage());
}





try {
    // Connexion à la base de données Supabase
    $pdo = getPDO();
    // Requête SQL avec des paramètres sécurisés
    $sql = 'INSERT INTO "Restaurant" (
        type_restaurant, nom_restaurant, 
        telephone_restaurant, site_restaurant, departement, 
        code_departement, commune, code_commune
    ) VALUES (
         :type_restaurant, :nom_restaurant, 
        :telephone_restaurant, :site_restaurant, :departement, 
        :code_departement, :commune, :code_commune
    )';
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
        $phone            = $data[$i]['phone'];
        // print_r($nom);
         // Préparer la requête
    $stmt = $pdo->prepare($sql);
    // Exécuter l’insertion avec les valeurs de `$data[0]`
    $stmt->execute([
        ':type_restaurant'    => $type,
        ':nom_restaurant'     => $nom,
        ':telephone_restaurant' => $phone,
        ':site_restaurant'    => $website,
        ':departement'        => $departement,
        ':code_departement'   => $code_departement,
        ':commune'            => $commune,
        ':code_commune'       => $code_commune,
    ]);
    }
    

   

    echo "Insertion réussie ! 🚀";

} catch (PDOException $e) {
    echo "Erreur lors de l'insertion : " . $e->getMessage();
}



?>