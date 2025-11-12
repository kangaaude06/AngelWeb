<?php
// hasher.php
$password = 'admin123'; // Le mot de passe que vous voulez
$hash = password_hash($password, PASSWORD_DEFAULT);
echo "Mot de passe en clair: " . $password . "<br>";
echo "Hachage généré: <strong>" . $hash . "</strong>";
?>