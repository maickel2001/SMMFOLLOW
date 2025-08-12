<?php
// Configuration de la base de données
define('DB_HOST', 'localhost');
define('DB_NAME', 'u634930929_Inn');
define('DB_USER', 'u634930929_Inn');
define('DB_PASS', 'Ino1234@');
define('DB_CHARSET', 'utf8mb4');

// Connexion à la base de données
function getDBConnection() {
    try {
        $dsn = "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=" . DB_CHARSET;
        $pdo = new PDO($dsn, DB_USER, DB_PASS);
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
        return $pdo;
    } catch (PDOException $e) {
        die("Erreur de connexion : " . $e->getMessage());
    }
}
?>
