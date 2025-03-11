<?php
// Ce fichier permet de surcharger la fonction getPDO() pour les tests
function getPDO() {
    global $mockPdo;
    if (isset($mockPdo)) {
        return $mockPdo;
    }
    throw new Exception("Mock PDO non configuré pour les tests");
}
?>