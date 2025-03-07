<?php
require_once '../_inc/bd/db.php';

/**
 * Convertit une valeur en booléen pour PostgreSQL.
 * Renvoie null si la valeur est vide ou invalide.
 */
function parseBoolean($value) {
    if (is_array($value)) {
        return null;
    }
    if (empty($value) || $value === "") {
        return null;
    }
    if (is_bool($value)) {
        return $value;
    }
    if (!is_string($value)) {
        return null;
    }
    
    $value = strtolower(trim($value));
    if (in_array($value, ['true', '1', 'yes', 'on', 't'])) {
        return true;
    }
    if (in_array($value, ['false', '0', 'no', 'off', 'f'])) {
        return false;
    }
    return null;
}

// Fonction pour convertir un tableau en chaîne
function arrayToString($value) {
    if (is_array($value)) {
        return implode(', ', $value);
    }
    return $value;
}

// Charger le fichier JSON des restaurants
$json = file_get_contents('../_inc/data/restaurants_orleans.json');
$data = json_decode($json, true);

// Connexion à la base de données Supabase
try {
    $host = 'aws-0-us-west-1.pooler.supabase.com';  
    $port = '6543'; 
    $dbname = 'postgres'; 
    $user = 'postgres.lmlcsjxhreswvnrdvhpp'; 
    $password = 'faitleloup'; 

    // Configuration de la connexion (PostgreSQL)
    $dsn = "pgsql:host=$host;port=$port;dbname=$dbname;sslmode=require";
    $pdo = new PDO($dsn, $user, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    echo "Connexion réussie ! 🚀<br>";
} catch (PDOException $e) {
    die("Erreur de connexion à la base de données : " . $e->getMessage());
}

// Préparer la requête SQL pour insérer un restaurant
$sql = "INSERT INTO \"Restaurant\" (
    type_restaurant, nom_restaurant, telephone_restaurant, 
    site_restaurant, departement, code_departement, 
    commune, code_commune, latitude, longitude, 
    stars, capacity, drive_through, wikidata, 
    brand_wikidata, opening_hours, wheelchair, cuisine, 
    vegetarian, vegan, delivery, takeaway, 
    internet_access, smoking, com_insee, com_nom, 
    region, code_region, osm_edit, osm_id, 
    operator, brand, website, facebook
) VALUES (
    :type_restaurant, :nom_restaurant, :telephone_restaurant,
    :site_restaurant, :departement, :code_departement,
    :commune, :code_commune, :latitude, :longitude,
    :stars, :capacity, :drive_through, :wikidata,
    :brand_wikidata, :opening_hours, :wheelchair, :cuisine,
    :vegetarian, :vegan, :delivery, :takeaway,
    :internet_access, :smoking, :com_insee, :com_nom,
    :region, :code_region, :osm_edit, :osm_id,
    :operator, :brand, :website, :facebook
)";
$stmt = $pdo->prepare($sql);

// Boucle sur tous les restaurants du JSON
foreach ($data as $record) {
    // Nettoyage de l'osm_id pour ne conserver que les chiffres
    $osm_id = preg_replace('/[^0-9]/', '', $record['osm_id']);

    $params = [
        ':type_restaurant'    => $record['type'],
        ':nom_restaurant'     => $record['name'],
        ':telephone_restaurant' => isset($record['phone']) ? $record['phone'] : null,
        ':site_restaurant'    => $record['website'],
        ':departement'        => $record['departement'],
        ':code_departement'   => $record['code_departement'],
        ':commune'            => $record['commune'],
        ':code_commune'       => $record['code_commune'],
        ':latitude'           => isset($record['geo_point_2d']["lat"]) ? $record['geo_point_2d']["lat"] : null,
        ':longitude'          => isset($record['geo_point_2d']["lon"]) ? $record['geo_point_2d']["lon"] : null,
        ':stars'              => $record['stars'],
        ':capacity'           => $record['capacity'],
        ':drive_through'      => $record['drive_through'],
        ':wikidata'           => $record['wikidata'],
        ':brand_wikidata'     => $record['brand_wikidata'],
        ':opening_hours'      => $record['opening_hours'],
        ':wheelchair'         => $record['wheelchair'],  // À traiter si besoin (vous pouvez appliquer parseBoolean si c'est attendu en booléen)
        ':cuisine'            => arrayToString($record['cuisine']),
        ':vegetarian'         => parseBoolean($record['vegetarian']),
        ':vegan'              => parseBoolean($record['vegan']),
        ':delivery'           => parseBoolean($record['delivery']),
        ':takeaway'           => parseBoolean($record['takeaway']),
        // Conversion pour internet_access
        ':internet_access'    => parseBoolean($record['internet_access']),
        ':smoking'            => $record['smoking'],
        ':com_insee'          => $record['com_insee'],
        ':com_nom'            => $record['com_nom'],
        ':region'             => $record['region'],
        ':code_region'        => $record['code_region'],
        ':osm_edit'           => $record['osm_edit'],
        ':osm_id'             => $osm_id,
        ':operator'           => $record['operator'],
        ':brand'              => $record['brand'],
        ':website'            => $record['website'],
        ':facebook'           => $record['facebook']
    ];

    $stmt->execute($params);
}


echo "Insertion réussie pour tous les restaurants ! 🚀";
?>
