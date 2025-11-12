<?php
// db_test.php
$host = 'localhost'; 
$db   = 'gestion_personnel'; 
$user = 'ADMIN'; // <--- VOTRE NOM D'UTILISATEUR MYSQL RÉEL
$pass = 'admin123';     // <--- VOTRE MOT DE PASSE MYSQL RÉEL

$dsn = "mysql:host=$host;dbname=$db;charset=utf8mb4";
$options = [
    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    PDO::ATTR_EMULATE_PREPARES   => false,
];

try {
    $pdo = new PDO($dsn, $user, $pass, $options);
    echo "<h1>✅ Connexion à la base de données REUSSIE !</h1>";
    echo "L'étape 1 est validée. Le problème est dans le code (étape 2).";
    
    // Test rapide de la présence de l'admin
    $stmt = $pdo->query("SELECT id, prenom, numero_telephone FROM users WHERE numero_telephone = '0000000000'");
    $admin = $stmt->fetch();
    
    if ($admin) {
        echo "<p>Admin trouvé: " . $admin['prenom'] . " (ID: " . $admin['id'] . ")</p>";
    } else {
        echo "<h2>❌ ERREUR: L'administrateur (0000000000) n'existe pas dans la table users.</h2>";
    }
    
} catch (\PDOException $e) {
    echo "<h1>❌ ERREUR DE CONNEXION ÉCHOUÉE !</h1>";
    echo "<p>Veuillez vérifier config.php :</p>";
    echo "Code d'erreur : " . $e->getCode() . "<br>";
    echo "Message : <strong>" . $e->getMessage() . "</strong>";
    die();
}
?>