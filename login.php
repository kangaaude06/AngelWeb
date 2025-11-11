<?php
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
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
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

