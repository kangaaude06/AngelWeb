<?php
// inscription_ouvrier.php
require_once 'User.class.php';

$message = "";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $user = new User();
    
    // Nettoyage des données
    $nom = trim($_POST['nom']);
    $prenom = trim($_POST['prenom']);
    $numero_telephone = trim($_POST['numero_telephone']);
    $departement = trim($_POST['departement']);
    $password = $_POST['password'];

    // 1. Vérification si l'ouvrier a été initialisé par la Coordination
    if ($user->checkOuvrierExistsByInfo($nom, $prenom, $departement)) {
        // 2. Finalisation de l'inscription (mise à jour du mot de passe/numéro)
        $success = $user->finalizeOuvrierRegistration([
            'nom' => $nom,
            'prenom' => $prenom,
            'numero_telephone' => $numero_telephone,
            'departement' => $departement,
            'password' => $password
        ]);

        if ($success) {
            $message = "<p class='success'>Inscription réussie ! Vous pouvez maintenant vous connecter avec votre numéro.</p>";
            // header("Location: connexion.php");
        } else {
            $message = "<p class='error'>Échec de l'inscription. Le numéro est peut-être déjà utilisé ou contactez la coordination.</p>";
        }
    } else {
        $message = "<p class='error'>Vos informations (Nom/Prénom/Département) n'ont pas été trouvées ou ne correspondent pas. Contactez la coordination pour l'initialisation.</p>";
    }
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Inscription Ouvrier</title>
    <style>
        .success { color: green; font-weight: bold; }
        .error { color: red; }
    </style>
</head>
<body>
    <h2>Inscription Ouvrier Angel House</h2>
    <?php echo $message; ?>
    <form method="POST">
        <div>
            <label for="nom">Nom :</label>
            <input type="text" id="nom" name="nom" required>
        </div>
        <div>
            <label for="prenom">Prénom :</label>
            <input type="text" id="prenom" name="prenom" required>
        </div>
        <div>
            <label for="departement">Département :</label>
            <input type="text" id="departement" name="departement" required>
        </div>
        <hr>
        <div>
            <label for="numero_telephone">Numéro de Téléphone (Identifiant) :</label>
            <input type="tel" id="numero_telephone" name="numero_telephone" required>
        </div>
        <div>
            <label for="password">Mot de Passe :</label>
            <input type="password" id="password" name="password" required>
        </div>
        <button type="submit">Finaliser mon Inscription</button>
    </form>
</body>
</html>