<?php
<<<<<<< HEAD
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
=======
// login.php - Page de connexion
require_once 'config.php';
require_once 'User.class.php';

// Démarrer la session si ce n'est pas déjà fait
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $identifiant = trim($_POST['identifiant']);
    $password = $_POST['password'];
    $grade_id = (int)$_POST['user_type'];
    
    // Nettoyer le numéro de téléphone (supprimer les espaces, tirets, etc.)
    $identifiant = preg_replace('/[^0-9]/', '', $identifiant);
    
    if (empty($identifiant) || empty($password) || empty($grade_id)) {
        $error = 'Tous les champs sont obligatoires.';
    } else {
        $user = new User();
        $loggedInUser = $user->loginUser($identifiant, $password, $grade_id);
        
        if ($loggedInUser) {
            // Définir les variables de session
            $_SESSION['user_id'] = $loggedInUser['id'];
            $_SESSION['user_type'] = $user->getUserTypeFromGrade($loggedInUser['grade_id']);
            $_SESSION['user_name'] = $loggedInUser['prenom'] . ' ' . $loggedInUser['nom'];
            $_SESSION['departement'] = $loggedInUser['departement'] ?? null;
            
            // Redirection en fonction du type d'utilisateur
            if ($_SESSION['user_type'] === 'responsable') {
                header('Location: select_departement.php');
            } else {
                header('Location: dashboard.php');
            }
            exit;
        } else {
            $error = 'Identifiant ou mot de passe incorrect.';
        }
    }
}

// Redirection si déjà connecté
if (isset($_SESSION['user_id']) && isset($_SESSION['user_type'])) {
    header('Location: dashboard.php');
    exit;
}
>>>>>>> 9908589319a8248a0a604cef3955a579ded8a2cb
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
<<<<<<< HEAD
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
=======
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Connexion - Angels House</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <div class="login-container">
        <div class="login-card">
            <h2>✨ Connexion</h2>
            
            <?php if ($error): ?>
                <div class="alert alert-error"><?php echo htmlspecialchars($error); ?></div>
            <?php endif; ?>
            
            <form method="POST" action="">
                <div class="form-group">
                    <label for="user_type">Type d'utilisateur</label>
                    <select name="user_type" id="user_type" class="form-control" required>
                        <option value="">Sélectionnez un type</option>
                        <option value="1">Administrateur</option>
                        <option value="2">Coordination</option>
                        <option value="3">Responsable</option>
                        <option value="4">Ouvrier</option>
                    </select>
                </div>
                
                <div class="form-group">
                    <label for="identifiant">Numéro de téléphone</label>
                    <input type="tel" id="identifiant" name="identifiant" class="form-control" placeholder="Ex: 771234567" required>
                </div>
                
                <div class="form-group">
                    <label for="password">Mot de passe</label>
                    <input type="password" id="password" name="password" class="form-control" required>
                </div>
                
                <button type="submit" class="btn btn-primary" style="width: 100%;">Se connecter</button>
            </form>
            
            <p style="text-align: center; margin-top: 20px;">
                <a href="index.php" style="color: var(--primary-color);">Retour à l'accueil</a>
            </p>
        </div>
    </div>
</body>
</html>

>>>>>>> 9908589319a8248a0a604cef3955a579ded8a2cb
