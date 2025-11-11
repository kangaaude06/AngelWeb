<?php
// login.php
require_once 'User.class.php';

$message = "";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $numero_telephone = trim($_POST['numero_telephone']);
    $password = $_POST['password'];
    
    $userHandler = new User();
    $user = $userHandler->loginUser($numero_telephone, $password);

    if ($user) {
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['grade'] = $user['nom_grade'];
        $_SESSION['departement'] = $user['departement'];
        $_SESSION['prenom'] = $user['prenom'];
        
        // Redirection en fonction du grade
        switch ($user['nom_grade']) {
            case 'Administrateur': header("Location: dashboard_admin.php"); break;
            case 'Coordination': header("Location: dashboard_coordination.php"); break;
            case 'Responsable': header("Location: dashboard_responsable.php"); break;
            case 'Ouvrier': header("Location: dashboard_ouvrier.php"); break;
            default: $message = "Erreur de rôle non reconnu."; break;
        }
        exit;
    } else {
        $message = "Numéro de téléphone ou mot de passe incorrect.";
    }
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Connexion - Angel House</title>
    <style>
        :root { --violet: #8A2BE2; --or: #FFD700; --blanc: #FFFFFF; --texte-clair: #F0F0F0; }
        body { font-family: Arial, sans-serif; background-color: var(--blanc); display: flex; justify-content: center; align-items: center; min-height: 100vh; margin: 0; }
        .login-container { background-color: var(--texte-clair); padding: 40px; border-radius: 10px; box-shadow: 0 4px 20px rgba(0, 0, 0, 0.2); width: 350px; border-top: 5px solid var(--violet); }
        h2 { color: var(--violet); text-align: center; margin-bottom: 20px; border-bottom: 3px solid var(--or); padding-bottom: 10px; }
        .form-group { margin-bottom: 15px; }
        .form-group label { display: block; margin-bottom: 5px; font-weight: bold; color: #333; }
        .form-group input { width: 100%; padding: 10px; border: 1px solid #ccc; border-radius: 5px; box-sizing: border-box; }
        button { width: 100%; padding: 10px; background-color: var(--violet); color: var(--blanc); border: none; border-radius: 5px; cursor: pointer; font-size: 1em; font-weight: bold; transition: background-color 0.3s; }
        button:hover { background-color: #6a1aae; }
        .message { color: red; text-align: center; margin-bottom: 15px; font-weight: bold; }
        .inscription-link { text-align: center; margin-top: 20px; font-size: 0.9em; }
        .inscription-link a { color: var(--violet); text-decoration: none; font-weight: bold; }
    </style>
</head>
<body>
    <div class="login-container">
        <h2>Connexion Angel House</h2>
        <?php if (!empty($message)): ?>
            <div class="message"><?php echo $message; ?></div>
        <?php endif; ?>
        <form method="POST">
            <div class="form-group">
                <label for="numero_telephone">Numéro de Téléphone :</label>
                <input type="tel" id="numero_telephone" name="numero_telephone" required>
            </div>
            <div class="form-group">
                <label for="password">Mot de Passe :</label>
                <input type="password" id="password" name="password" required>
            </div>
            <button type="submit">Se Connecter</button>
        </form>
        <div class="inscription-link">
            <p>Pas encore inscrit ? <a href="inscription_ouvrier.php">Finaliser mon inscription Ouvrier</a></p>
        </div>
    </div>
</body>
</html>