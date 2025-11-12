<?php
// admin_gestion_personnel.php
require_once 'User.class.php';

// Le fichier config.php doit gérer session_start()
require_once 'config.php'; 

// Vérification des droits d'accès
if (!isset($_SESSION['user_id']) || $_SESSION['grade'] !== 'Administrateur') {
    header("Location: login.php");
    exit;
}

$userHandler = new User();
$message = "";
$message_class = "";

// --- GESTION DE L'AJOUT DE NOUVEL UTILISATEUR (Coordination ou Responsable) ---
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $grade_id = (int)$_POST['grade_id'];
    
    // Seuls les Coordinateurs (2) et les Responsables (3) sont ajoutés par l'Admin.
    if ($grade_id === 2 || $grade_id === 3) {
        $data = array(
            'nom' => trim($_POST['nom']),
            'prenom' => trim($_POST['prenom']),
            'numero_telephone' => trim($_POST['numero_telephone']),
            'departement' => trim($_POST['departement']),
            'password' => $_POST['password'], // <-- Mot de passe récupéré du formulaire
            'grade_id' => $grade_id
        );

        if (empty($data['password']) || empty($data['numero_telephone'])) {
            $message = "Le numéro de téléphone et le mot de passe sont obligatoires.";
            $message_class = 'error';
        } else {
            $success = $userHandler->registerUser($data);
            
            $grade_nom = ($grade_id == 2) ? 'Coordination' : 'Responsable';
            
            if ($success) {
                $message = "$grade_nom " . $data['prenom'] . " " . $data['nom'] . " a été ajouté(e) avec succès.";
                $message_class = 'success';
                // Effacer le mot de passe pour des raisons de sécurité après l'enregistrement
                $data['password'] = ''; 
            } else {
                $message = "Échec de l'ajout. Le numéro de téléphone est peut-être déjà utilisé ou une erreur est survenue.";
                $message_class = 'error';
            }
        }
    }
}

$allUsers = $userHandler->getAllUsers();
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>TdB Administrateur - Gestion Personnel</title>
    <style>
        :root { 
            --violet: #8A2BE2; 
            --or: #FFD700; 
            --blanc: #FFFFFF; 
            --texte-clair: #F0F0F0;
        }
        body { 
            font-family: Arial, sans-serif; 
            background-color: var(--blanc); 
            color: #333; 
            margin: 0; 
            padding: 0; 
        }
        header { 
            background-color: var(--violet); 
            color: var(--blanc); 
            padding: 20px; 
            display: flex; 
            justify-content: space-between; 
            align-items: center; 
        }
        header h1 { 
            margin: 0; 
            border-bottom: 2px solid var(--or); 
            padding-bottom: 5px; 
        }
        .header-links { 
            display: flex; 
            align-items: center; 
        }
        .header-links span, .header-links a { 
            color: var(--blanc); 
            margin-left: 20px; 
            text-decoration: none; 
            font-weight: bold; 
        }
        .header-links a:hover { 
            color: var(--or); 
        }
        .container { 
            padding: 20px; 
            max-width: 1200px; 
            margin: 0 auto; 
        }
        h2 { 
            color: var(--violet); 
            border-bottom: 2px solid var(--or); 
            padding-bottom: 5px; 
            margin-top: 30px; 
        }
        table { 
            width: 100%; 
            border-collapse: collapse; 
            margin-top: 20px; 
        }
        th, td { 
            border: 1px solid #ccc; 
            padding: 10px; 
            text-align: left; 
        }
        th { 
            background-color: #f2f2f2; 
        }
        /* Styles pour les messages d'état */
        .message { 
            padding: 10px; 
            margin-bottom: 20px; 
            border-radius: 4px; 
            font-weight: bold; 
            text-align: center; 
        }
        .message.success { 
            background-color: #d4edda; 
            color: #155724; 
            border: 1px solid #c3e6cb; 
        }
        .message.error { 
            background-color: #f8d7da; 
            color: #721c24; 
            border: 1px solid #f5c6cb; 
        }
        /* Styles pour le formulaire */
        form {
            background: #f9f9f9;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }
        .form-group { 
            margin-bottom: 15px; 
        }
        .form-group label { 
            display: block; 
            margin-bottom: 5px; 
            font-weight: bold; 
            color: #555; 
        }
        .form-group input[type="text"], 
        .form-group input[type="tel"], 
        .form-group input[type="password"], 
        .form-group select { 
            width: 100%; 
            padding: 10px; 
            border: 1px solid #ccc; 
            border-radius: 4px; 
            box-sizing: border-box; 
        }
        button[type="submit"] { 
            background-color: var(--violet); 
            color: var(--blanc); 
            padding: 10px 15px; 
            border: none; 
            border-radius: 4px; 
            cursor: pointer; 
            font-size: 1em; 
            margin-top: 10px; 
            transition: background-color 0.3s; 
            font-weight: bold;
        }
        button[type="submit"]:hover { 
            background-color: #6a1aae; 
        }
        .back-link a { 
            color: var(--violet); 
            text-decoration: none; 
            font-weight: bold; 
        }
    </style>
</head>
<body>
    <header>
        <h1>Gestion du Personnel</h1>
        <div class="header-links">
            <span>Bonjour, <?php echo htmlspecialchars($_SESSION['prenom']); ?></span>
            <a href="dashboard_admin.php">Retour au TdB</a>
            <a href="logout.php">Déconnexion</a>
        </div>
    </header>

    <div class="container">
        <h2>Ajouter un Coordination ou un Responsable</h2>

        <?php if (!empty($message)): ?>
            <div class="message <?php echo $message_class; ?>"><?php echo htmlspecialchars($message); ?></div>
        <?php endif; ?>

        <form method="POST">
            <input type="hidden" name="action" value="add_user">
            
            <div class="form-group">
                <label for="grade_id">Rôle :</label>
                <select id="grade_id" name="grade_id" required>
                    <option value="">-- Choisir un rôle --</option>
                    <option value="3">Responsable</option>
                    <option value="2">Coordination</option>
                </select>
            </div>
            
            <div class="form-group">
                <label for="nom">Nom :</label>
                <input type="text" id="nom" name="nom" required>
            </div>
            
            <div class="form-group">
                <label for="prenom">Prénom :</label>
                <input type="text" id="prenom" name="prenom" required>
            </div>
            
            <div class="form-group">
                <label for="departement">Département (pour Responsable/Coordination) :</label>
                <input type="text" id="departement" name="departement" required>
            </div>

            <hr style="border-color: var(--or);">

            <div class="form-group">
                <label for="numero_telephone">Numéro de Téléphone (Identifiant unique) :</label>
                <input type="tel" id="numero_telephone" name="numero_telephone" required>
            </div>
            
            <div class="form-group">
                <label for="password">Mot de Passe :</label>
                <input type="password" id="password" name="password" required> </div>
            
            <button type="submit">Ajouter l'Utilisateur</button>
        </form>
        
        <p class="back-link" style="margin-top: 20px;"><a href="dashboard_admin.php">← Retour au Tableau de Bord Admin</a></p>

        <h2>Liste du Personnel Actif</h2>
        <div class="user-table">
            <table>
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Prénom Nom</th>
                        <th>Rôle</th>
                        <th>Département</th>
                        <th>Téléphone</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (!empty($allUsers)): ?>
                        <?php foreach ($allUsers as $user): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($user['id']); ?></td>
                                <td><?php echo htmlspecialchars($user['prenom'] . ' ' . $user['nom']); ?></td>
                                <td><?php echo htmlspecialchars($user['nom_grade']); ?></td>
                                <td><?php echo htmlspecialchars($user['departement']); ?></td>
                                <td><?php echo htmlspecialchars($user['numero_telephone']); ?></td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr><td colspan="5">Aucun utilisateur trouvé dans la base de données.</td></tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
        
    </div>
</body>
</html>