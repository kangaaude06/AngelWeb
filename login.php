<?php
// login.php
require_once 'User.class.php';

$userHandler = new User();
$message = "";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $numero_telephone = trim($_POST['numero_telephone']);
    $password = $_POST['password'];

    $userInfo = $userHandler->loginUser($numero_telephone, $password);

    if ($userInfo) {
        $_SESSION['user_id'] = $userInfo['id'];
        $_SESSION['user_name'] = $userInfo['prenom'] . ' ' . $userInfo['nom'];
        $_SESSION['grade'] = $userInfo['nom_grade'];
        $_SESSION['departement'] = $userInfo['departement'];
        
        $grade = $_SESSION['grade'];
        
        if ($grade === 'Administrateur') {
            header("Location: dashboard_admin.php");
        } elseif ($grade === 'Coordination') {
            header("Location: dashboard_coordination.php");
        } elseif ($grade === 'Responsable') {
            header("Location: dashboard_responsable.php");
        } elseif ($grade === 'Ouvrier') {
            header("Location: dashboard_ouvrier.php");
        }
        exit;
    } else {
        $message = "<p style='color:red;'>Numéro de téléphone ou mot de passe incorrect.</p>";
    }
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Connexion - Angel House</title>
</head>
<body>
    <h2>Connexion Angel House </h2>
    <?php echo $message; ?>
    <form method="POST">
        <div>
            <label for="numero_telephone">Numéro de Téléphone :</label>
            <input type="tel" id="numero_telephone" name="numero_telephone" required>
        </div>
        <div>
            <label for="password">Mot de Passe :</label>
            <input type="password" id="password" name="password" required>
        </div>
        <button type="submit">Se Connecter</button>
    </form>
    <hr>
    <p>Ouvrier non inscrit ? <a href="inscription_ouvrier.php">Finaliser l'Inscription</a></p>
</body>
</html>