<?php
// inscription_ouvrier.php
require_once 'User.class.php';

$user = new User();
$message = "";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nom = trim($_POST['nom']);
    $prenom = trim($_POST['prenom']);
    $numero_telephone = trim($_POST['numero_telephone']);
    $departement = trim($_POST['departement']);
    $password = $_POST['password'];

    if ($user->checkOuvrierExistsByInfo($nom, $prenom, $departement)) {
        $success = $user->finalizeOuvrierRegistration(['nom' => $nom, 'prenom' => $prenom, 'numero_telephone' => $numero_telephone, 'departement' => $departement, 'password' => $password]);
        if ($success) {
            $message = "<p style='color:green; font-weight:bold;'>Inscription réussie ! Vous pouvez maintenant vous connecter.</p>";
        } else {
            $message = "<p style='color:red;'>Échec de l'inscription. Le numéro est peut-être déjà utilisé.</p>";
        }
    } else {
        $message = "<p style='color:red;'>Vos informations n'ont pas été trouvées. Contactez la coordination.</p>";
    }
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Inscription Ouvrier</title>
</head>
<body>
    <h2>Finalisation Inscription Ouvrier</h2>
    <?php echo $message; ?>
    <form method="POST">
        <div><label for="nom">Nom :</label><input type="text" id="nom" name="nom" required></div>
        <div><label for="prenom">Prénom :</label><input type="text" id="prenom" name="prenom" required></div>
        <div><label for="departement">Département :</label><input type="text" id="departement" name="departement" required></div>
        <hr>
        <div><label for="numero_telephone">Numéro de Téléphone (Identifiant) :</label><input type="tel" id="numero_telephone" name="numero_telephone" required></div>
        <div><label for="password">Mot de Passe :</label><input type="password" id="password" name="password" required></div>
        <button type="submit">Finaliser mon Inscription</button>
    </form>
    <p><a href="login.php">Retour à la connexion</a></p>
</body>
</html>