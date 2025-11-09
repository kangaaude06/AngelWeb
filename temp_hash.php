<?php
$mdp_clair = 'rootpass123';
$mdp_hache = password_hash($mdp_clair, PASSWORD_DEFAULT);
echo "Nouveau hachage pour 'rootpass123' : <br><br>";
echo "<strong>" . $mdp_hache . "</strong>";
?>