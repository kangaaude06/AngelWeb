<?php
// inscription_ouvrier.php
require_once 'User.class.php';

$message = "";
$message_class = "";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nom = trim($_POST['nom']);
    $prenom = trim($_POST['prenom']);
    $departement = trim($_POST['departement']);
    $numero_telephone = trim($_POST['numero_telephone']);
    $password = $_POST['password'];

    $userHandler = new User();

    // 1. Vérifier si l'ouvrier a été initialisé par la Coordination
    if ($userHandler->checkOuvrierExistsByInfo($nom, $prenom, $departement)) {
        
        // 2. Finaliser l'inscription (ajouter numéro et mot de passe)
        $data = array(
            'nom' => $nom,
            'prenom' => $prenom,
            'departement' => $departement,
            'numero_telephone' => $numero_telephone,
            'password' => $password
        );
        
        if ($userHandler->finalizeOuvrierRegistration($data)) {
            $message = "Félicitations, votre compte est maintenant actif. Vous pouvez vous connecter.";
            $message_class = 'success';
        } else {
            $message = "Échec de l l'inscription. Le numéro de téléphone est peut-être déjà utilisé.";
            $message_class = 'error';
        }
    } else {
        $message = "Vos informations n'ont pas été pré-enregistrées. Veuillez contacter la Coordination.";
        $message_class = 'error';
    }
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Inscription Ouvrier - Finalisation</title>
    <style>
        :root { --violet: #8A2BE2; --or: #FFD700; --blanc: #FFFFFF; --texte-clair: #F0F0F0; }
        body { font-family: Arial, sans-serif; background-color: var(--blanc); display: flex; justify-content: center; align-items: center; min-height: 100vh; margin: 0; }
        .inscription-container { background-color: var(--texte-clair); padding: 40px; border-radius: 10px; box-shadow: 0 4px 20px rgba(0, 0, 0, 0.2); width: 400px; border-top: 5px solid var(--violet); }
        h2 { color: var(--violet); text-align: center; margin-bottom: 20px; border-bottom: 3px solid var(--or); padding-bottom: 10px; }
        .form-group { margin-bottom: 15px; }
        .form-group label { display: block; margin-bottom: 5px; font-weight: bold; color: #333; }
        .form-group input { width: 100%; padding: 10px; border: 1px solid #ccc; border-radius: 5px; box-sizing: border-box; }
        button { width: 100%; padding: 10px; background-color: var(--violet); color: var(--blanc); border: none; border-radius: 5px; cursor: pointer; font-size: 1em; font-weight: bold; transition: background-color 0.3s; }
        button:hover { background-color: #6a1aae; }
        .message { text-align: center; margin-bottom: 15px; font-weight: bold; padding: 10px; border-radius: 5px; }
        .success { color: green; background-color: #e6ffe6; border: 1px solid green; }
        .error { color: red; background-color: #ffe6e6; border: 1px solid red; }
        .connexion-link { text-align: center; margin-top: 20px; }
        .connexion-link a { color: var(--violet); text-decoration: none; font-weight: bold; }
    </style>
</head>
<body>
    <div class="inscription-container">
        <h2>Finaliser Inscription Ouvrier</h2>
        
        <?php if (!empty($message)): ?>
            <div class="message <?php echo $message_class; ?>"><?php echo $message; ?></div>
        <?php endif; ?>

        <form method="POST">
            <div class="form-group"><label for="nom">Nom (tel que pré-enregistré) :</label><input type="text" id="nom" name="nom" required></div>
            <div class="form-group"><label for="prenom">Prénom (tel que pré-enregistré) :</label><input type="text" id="prenom" name="prenom" required></div>
            <div class="form-group"><label for="departement">Département (tel que pré-enregistré) :</label><input type="text" id="departement" name="departement" required></div>
            <hr style="border-color: var(--or); margin: 20px 0;">
            <div class="form-group"><label for="numero_telephone">Votre Numéro de Téléphone (Identifiant) :</label><input type="tel" id="numero_telephone" name="numero_telephone" required></div>
            <div class="form-group"><label for="password">Créer un Mot de Passe :</label><input type="password" id="password" name="password" required></div>
            
            <button type="submit">Finaliser et Activer</button>
        </form>
        
        <div class="connexion-link"><p><a href="login.php">← Retour à la Connexion</a></p></div>
    </div>
</body>
</html>