<?php
// inscription_ouvrier.php
require_once 'config.php';
require_once 'User.class.php';

// Démarrer la session si ce n'est pas déjà fait
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Rediriger si déjà connecté
if (isset($_SESSION['user_id'])) {
    header('Location: dashboard.php');
    exit;
}

$message = "";
$formData = [
    'nom' => '',
    'prenom' => '',
    'departement' => '',
    'numero_telephone' => ''
];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $user = new User();
    
    // Nettoyage des données
    $formData = [
        'nom' => trim($_POST['nom']),
        'prenom' => trim($_POST['prenom']),
        'numero_telephone' => preg_replace('/[^0-9]/', '', $_POST['numero_telephone']),
        'departement' => trim($_POST['departement']),
        'password' => $_POST['password'],
        'confirm_password' => $_POST['confirm_password']
    ];

    // Validation des données
    $errors = [];
    
    if (empty($formData['nom']) || empty($formData['prenom'])) {
        $errors[] = "Le nom et le prénom sont obligatoires.";
    }
    
    if (strlen($formData['numero_telephone']) < 9) {
        $errors[] = "Le numéro de téléphone est invalide.";
    }
    
    if (strlen($formData['password']) < 6) {
        $errors[] = "Le mot de passe doit contenir au moins 6 caractères.";
    } elseif ($formData['password'] !== $formData['confirm_password']) {
        $errors[] = "Les mots de passe ne correspondent pas.";
    }
    
    if (empty($errors)) {
        // Vérification si l'ouvrier a été initialisé par la Coordination
        if ($user->checkOuvrierExistsByInfo($formData['nom'], $formData['prenom'], $formData['departement'])) {
            // Finalisation de l'inscription
            $success = $user->finalizeOuvrierRegistration([
                'nom' => $formData['nom'],
                'prenom' => $formData['prenom'],
                'numero_telephone' => $formData['numero_telephone'],
                'departement' => $formData['departement'],
                'password' => $formData['password']
            ]);

            if ($success) {
                $_SESSION['success_message'] = "Inscription réussie ! Vous pouvez maintenant vous connecter avec votre numéro de téléphone.";
                header("Location: login.php");
                exit;
            } else {
                $errors[] = "Échec de l'inscription. Le numéro de téléphone est peut-être déjà utilisé.";
            }
        } else {
            $errors[] = "Vos informations (Nom/Prénom/Département) n'ont pas été trouvées. Contactez la coordination pour l'initialisation.";
        }
    }
    
    // Préparer le message d'erreur
    if (!empty($errors)) {
        $message = '<div class="alert alert-danger">' . implode('<br>', $errors) . '</div>';
    }
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Inscription Ouvrier - Angels House</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            background-color: #f8f9fa;
        }
        .registration-container {
            max-width: 600px;
            margin: 50px auto;
            padding: 30px;
            background: white;
            border-radius: 10px;
            box-shadow: 0 0 20px rgba(0, 0, 0, 0.1);
        }
        .form-title {
            color: #2c3e50;
            margin-bottom: 30px;
            text-align: center;
            font-weight: 600;
        }
        .form-group {
            margin-bottom: 20px;
        }
        .btn-submit {
            background-color: #3498db;
            border: none;
            padding: 12px;
            font-weight: 600;
            transition: all 0.3s;
        }
        .btn-submit:hover {
            background-color: #2980b9;
        }
        .login-link {
            text-align: center;
            margin-top: 20px;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="registration-container">
            <h2 class="form-title">Inscription Ouvrier</h2>
            
            <?php 
            // Afficher le message de succès s'il y en a un
            if (isset($_SESSION['success_message'])) {
                echo '<div class="alert alert-success">' . $_SESSION['success_message'] . '</div>';
                unset($_SESSION['success_message']);
            }
            
            // Afficher les erreurs
            echo $message;
            ?>
            
            <form method="POST" action="">
                <div class="row">
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="nom" class="form-label">Nom <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="nom" name="nom" 
                                   value="<?php echo htmlspecialchars($formData['nom']); ?>" required>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="prenom" class="form-label">Prénom <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="prenom" name="prenom" 
                                   value="<?php echo htmlspecialchars($formData['prenom']); ?>" required>
                        </div>
                    </div>
                </div>
                
                <div class="form-group">
                    <label for="departement" class="form-label">Département <span class="text-danger">*</span></label>
                    <input type="text" class="form-control" id="departement" name="departement" 
                           value="<?php echo htmlspecialchars($formData['departement']); ?>" required>
                </div>
                
                <div class="form-group">
                    <label for="numero_telephone" class="form-label">Numéro de téléphone <span class="text-danger">*</span></label>
                    <input type="tel" class="form-control" id="numero_telephone" name="numero_telephone" 
                           value="<?php echo htmlspecialchars($formData['numero_telephone']); ?>" 
                           placeholder="Ex: 771234567" required>
                    <small class="form-text text-muted">Ce numéro servira d'identifiant pour vous connecter</small>
                </div>
                
                <div class="row">
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="password" class="form-label">Mot de passe <span class="text-danger">*</span></label>
                            <input type="password" class="form-control" id="password" name="password" required>
                            <small class="form-text text-muted">Minimum 6 caractères</small>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="confirm_password" class="form-label">Confirmer le mot de passe <span class="text-danger">*</span></label>
                            <input type="password" class="form-control" id="confirm_password" name="confirm_password" required>
                        </div>
                    </div>
                </div>
                
                <button type="submit" class="btn btn-primary btn-submit w-100">Finaliser mon inscription</button>
            </form>
            
            <div class="login-link">
                <p>Déjà inscrit ? <a href="login.php">Connectez-vous ici</a></p>
            </div>
        </div>
    </div>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Validation côté client pour le mot de passe
        document.querySelector('form').addEventListener('submit', function(e) {
            const password = document.getElementById('password').value;
            const confirmPassword = document.getElementById('confirm_password').value;
            
            if (password !== confirmPassword) {
                e.preventDefault();
                alert('Les mots de passe ne correspondent pas.');
                return false;
            }
            
            if (password.length < 6) {
                e.preventDefault();
                alert('Le mot de passe doit contenir au moins 6 caractères.');
                return false;
            }
            
            return true;
        });
        
        // Formater le numéro de téléphone au fur et à mesure de la saisie
        document.getElementById('numero_telephone').addEventListener('input', function(e) {
            let value = e.target.value.replace(/\D/g, '');
            if (value.length > 9) {
                value = value.substring(0, 9);
            }
            e.target.value = value;
        });
    </script>
</body>
</html>