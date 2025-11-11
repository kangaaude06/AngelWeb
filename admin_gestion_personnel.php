<?php
// admin_gestion_personnel.php
require_once 'User.class.php';

// 1. Vérification de sécurité
if (!isset($_SESSION['user_id']) || $_SESSION['grade'] !== 'Administrateur') {
    header("Location: login.php");
    exit;
}

$userHandler = new User();
$message = "";
$message_class = "";

// 2. Traitement du Formulaire d'Ajout
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $grade_id = filter_var($_POST['grade_id'], FILTER_VALIDATE_INT);
    
    // Les IDs 2 (Coordination) et 3 (Responsable) sont valides
    if ($grade_id === 2 || $grade_id === 3) {
        $data = [
            'nom' => trim($_POST['nom']),
            'prenom' => trim($_POST['prenom']),
            'numero_telephone' => trim($_POST['numero_telephone']),
            'departement' => trim($_POST['departement']),
            'password' => $_POST['password'],
            'grade_id' => $grade_id
        ];

        if (empty($data['password']) || empty($data['numero_telephone'])) {
            $message = "Le numéro de téléphone et le mot de passe sont obligatoires.";
            $message_class = 'error';
        } else {
            $success = $userHandler->registerUser($data);
            
            $grade_nom = ($grade_id == 2) ? 'Coordinateur' : 'Responsable';
            
            if ($success) {
                $message = "$grade_nom " . $data['prenom'] . " a été ajouté avec succès.";
                $message_class = 'success';
            } else {
                $message = "Échec de l'ajout. Le numéro de téléphone est probablement déjà utilisé (doit être unique).";
                $message_class = 'error';
            }
        }
    } else {
        $message = "Erreur: Grade sélectionné invalide.";
        $message_class = 'error';
    }
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Admin - Gestion Personnel</title>
    <style>
        :root { --violet: #8A2BE2; --or: #FFD700; --blanc: #FFFFFF; }
        body { font-family: Arial, sans-serif; background-color: var(--blanc); color: #333; margin: 0; padding: 0; }
        header { background-color: var(--violet); color: var(--blanc); padding: 20px; }
        h1 { margin: 0; border-bottom: 2px solid var(--or); padding-bottom: 5px; }
        .container { padding: 20px; max-width: 600px; margin: 20px auto; border: 1px solid var(--violet); border-radius: 8px; box-shadow: 2px 2px 10px rgba(138, 43, 226, 0.1); }
        .form-group { margin-bottom: 15px; }
        .form-group label { display: block; font-weight: bold; margin-bottom: 5px; }
        .form-group input, .form-group select { width: 100%; padding: 10px; border: 1px solid #ccc; border-radius: 4px; box-sizing: border-box; }
        button { background-color: var(--violet); color: var(--blanc); padding: 10px 15px; border: none; border-radius: 5px; cursor: pointer; font-size: 1em; }
        button:hover { background-color: #6a1aae; }
        .success { color: green; font-weight: bold; margin-bottom: 15px; }
        .error { color: red; font-weight: bold; margin-bottom: 15px; }
    </style>
</head>
<body>
    <header>
        <h1>Gestion du Personnel 🛠️</h1>
    </header>

    <div class="container">
        <h2>Ajouter un Responsable ou un Coordinateur</h2>
        
        <div class="<?php echo $message_class; ?>"><?php echo $message; ?></div>

        <form method="POST">
            <div class="form-group">
                <label for="grade_id">Rôle :</label>
                <select id="grade_id" name="grade_id" required>
                    <option value="">-- Sélectionner --</option>
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
                <label for="departement">Département (pour Responsable/Coord) :</label>
                <input type="text" id="departement" name="departement" required>
            </div>

            <hr style="border-color: var(--or);">

            <div class="form-group">
                <label for="numero_telephone">Numéro de Téléphone (Identifiant unique) :</label>
                <input type="tel" id="numero_telephone" name="numero_telephone" required>
            </div>
            
            <div class="form-group">
                <label for="password">Mot de Passe :</label>
                <input type="password" id="password" name="password" required>
            </div>
            
            <button type="submit">Ajouter l'Utilisateur</button>
        </form>
        
        <p style="margin-top: 20px;"><a href="dashboard_admin.php">← Retour au Tableau de Bord</a></p>
    </div>
</body>
</html>