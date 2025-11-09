<?php
session_start();
require_once 'config.php';

$message_status = "";

// Codes d'inscription : Associer un code à un rôle
$codes_inscription = [
    'ANGEL' => 'ouvrier', // Code pour un rôle d'ouvrier
    'RESP' => 'responsable' // Code pour un rôle de responsable
];

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $pseudo = trim($_POST['pseudo']);
    $mot_de_passe = $_POST['mot_de_passe'];
    $code_acces = trim($_POST['code_acces']);

    if (empty($pseudo) || empty($mot_de_passe) || empty($code_acces)) {
        $message_status = "<p style='color: red;'>❌ Veuillez remplir tous les champs.</p>";
    } elseif (strlen($mot_de_passe) < 6) {
        $message_status = "<p style='color: red;'>❌ Le mot de passe doit contenir au moins 6 caractères.</p>";
    } elseif (!array_key_exists(strtoupper($code_acces), $codes_inscription)) {
        $message_status = "<p style='color: red;'>❌ Code d'accès invalide. Veuillez contacter votre superviseur.</p>";
    } else {
        $role = $codes_inscription[strtoupper($code_acces)];
        $mot_de_passe_hache = password_hash($mot_de_passe, PASSWORD_DEFAULT);

        try {
            // 1. Vérifier si le pseudo existe déjà
            $stmt = $pdo->prepare("SELECT COUNT(*) FROM utilisateurs WHERE pseudo = ?");
            $stmt->execute([$pseudo]);
            if ($stmt->fetchColumn() > 0) {
                $message_status = "<p style='color: red;'>❌ Cet identifiant (pseudo) est déjà utilisé.</p>";
            } else {
                // 2. Insérer le nouvel utilisateur
                $stmt = $pdo->prepare("INSERT INTO utilisateurs (pseudo, mot_de_passe, role) VALUES (?, ?, ?)");
                $stmt->execute([$pseudo, $mot_de_passe_hache, $role]);
                
                // Redirection vers la connexion avec succès
                $_SESSION['inscription_succes'] = true;
                header("Location: connexion.php");
                exit;
            }
        } catch (PDOException $e) {
            $message_status = "<p style='color: red;'>❌ Erreur lors de l'inscription : " . $e->getMessage() . "</p>";
        }
    }
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Inscription au Royaume</title>
    
    <style>
        /* CSS HARMONISÉ */
        :root {
            --color-primary: #8A2BE2; /* Bleu-Violet Profond (Améthyste) */
            --color-secondary: #DAA520; /* Jaune Doré (Goldenrod) */
            --color-background: #cab3f8ff; /* Violet très clair, presque blanc */
            --color-surface: #FFFFFF; /* Cartes blanches */
            --color-text: #333333;
            --color-light-text: #555555;
            --font-display: 'Georgia', serif; 
            --font-body: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            --shadow-card: 0 10px 25px rgba(0, 0, 0, 0.15);
        }
        body { 
            font-family: var(--font-body); 
            background-color: var(--color-background); 
            display: flex; 
            justify-content: center; 
            align-items: center; 
            min-height: 100vh; 
            margin: 0; 
            color: var(--color-text); 
        }

        /* Conteneur principal (la carte d'inscription) */
        main {
            background-color: var(--color-surface);
            padding: 40px;
            border-radius: 12px;
            box-shadow: var(--shadow-card);
            width: 100%;
            max-width: 450px;
            border-top: 5px solid var(--color-primary);
        }
        
        /* En-tête de la carte */
        .header-card {
            text-align: center;
            margin-bottom: 30px;
        }
        .header-card h1 {
            font-family: var(--font-display);
            color: var(--color-primary);
            font-size: 2em;
            margin: 10px 0 5px 0;
        }
        .header-card p {
            color: var(--color-light-text);
            margin: 0;
            font-size: 0.9em;
        }
        /* NOUVEAU STYLE DU LOGO (CARRÉ SANS BORDEUR/OMBRE) */
        .logo-img-small {
            height: 100px; 
            width: 140px; 
            border-radius: 8px; /* Carré arrondi */
            /* Suppression des bordures et de l'ombre pour maximiser la visibilité du logo */
            /* border: none; */ 
            /* box-shadow: none; */ 
        }

        /* Formulaire */
        form label {
            display: block;
            margin-top: 15px;
            margin-bottom: 5px;
            font-weight: bold;
            color: var(--color-light-text);
            font-size: 0.9em;
        }
        form input {
            width: 100%;
            padding: 10px;
            border: 1px solid #ddd;
            border-radius: 6px;
            box-sizing: border-box;
            transition: border-color 0.3s;
        }
        form input:focus {
            border-color: var(--color-primary);
            outline: none;
            box-shadow: 0 0 5px rgba(138, 43, 226, 0.2);
        }
        
        /* Bouton */
        .btn-primary {
            background-color: var(--color-primary);
            color: white;
            padding: 12px 20px;
            border: none;
            border-radius: 6px;
            cursor: pointer;
            width: 100%;
            margin-top: 25px;
            font-size: 1.1em;
            font-weight: bold;
            transition: background-color 0.3s, transform 0.1s;
        }
        .btn-primary:hover {
            background-color: #6A1B9A; /* Un violet plus foncé */
        }

        /* Message de statut */
        .message-status {
            padding: 10px;
            border-radius: 6px;
            margin-bottom: 20px;
            text-align: center;
            font-weight: bold;
        }
        .message-status p { margin: 0; }
        .message-status p[style*="color: red"] { 
            background-color: #ffe5e5;
            color: #d32f2f !important;
            border: 1px solid #d32f2f;
        }
        
        /* Conteneur Mot de Passe (pour l'icône) */
        .password-container {
            position: relative;
        }
        .toggle-mdp {
            position: absolute;
            right: 10px;
            top: 50%;
            transform: translateY(-50%);
            background: none;
            border: none;
            cursor: pointer;
            color: var(--color-light-text);
            font-size: 1.1em;
        }
        
        /* Style pour les liens secondaires */
        .secondary-link {
            font-weight: bold;
            color: var(--color-primary);
            text-decoration: none;
            transition: color 0.3s;
        }
        .secondary-link:hover {
            color: var(--color-secondary);
        }
    </style>
</head>
<body>

    <main>
        <div class="header-card">
            <img src="AH.jpg" alt="Logo Mon Royaume" class="logo-img-small">
            <h1>Inscription - Devenir Membre</h1>
            <p>Veuillez remplir le formulaire et utiliser le code d'accès fourni.</p>
        </div>

        <?php if (!empty($message_status)): ?>
            <div class="message-status"><?php echo $message_status; ?></div>
        <?php endif; ?>
        
        <form method="POST">
            <label for="pseudo">IDENTIFIANT (Pseudo) :</label>
            <input type="text" id="pseudo" name="pseudo" required value="<?php echo isset($pseudo) ? htmlspecialchars($pseudo) : ''; ?>">
            
            <label for="mot_de_passe">MOT DE PASSE :</label>
            <div class="password-container">
                <input type="password" id="mot_de_passe" name="mot_de_passe" required>
                <button type="button" class="toggle-mdp" onclick="togglePasswordVisibility()">👁️</button>
            </div>
            
            <label for="code_acces">CODE D'ACCÈS (Fourni par le Superviseur) :</label>
            <input type="text" id="code_acces" name="code_acces" required value="<?php echo isset($code_acces) ? htmlspecialchars($code_acces) : ''; ?>">
            
            <button type="submit" class="btn-primary">S'INSCRIRE DANS LE ROYAUME</button>
        </form>
        
        <p style="margin-top: 30px; text-align: center; font-size: 0.9em;">
            <span style="color: var(--color-light-text); font-family: var(--font-body);">Déjà membre ?</span> 
            <a href="connexion.php" class="secondary-link">CONNECTEZ-VOUS.</a>
        </p>
    </main>

    <script>
        function togglePasswordVisibility() {
            const passwordField = document.getElementById('mot_de_passe');
            const toggleButton = document.querySelector('.toggle-mdp');

            if (passwordField.type === 'password') {
                passwordField.type = 'text';
                toggleButton.textContent = '🔒'; 
            } else {
                passwordField.type = 'password';
                toggleButton.textContent = '👁️'; 
            }
        }
    </script>
</body>
</html>