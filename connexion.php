<?php
session_start();
require_once 'config.php'; 

$message_erreur = "";
// Option de débug pour afficher les codes d'accès si le paramètre 'debug' est présent
$show_codes = isset($_GET['debug']); 

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $pseudo = trim($_POST['pseudo']);
    $mdp_clair = $_POST['mot_de_passe'];

    if (empty($pseudo) || empty($mdp_clair)) {
        $message_erreur = "Veuillez remplir tous les champs.";
    } else {
        try {
            $stmt = $pdo->prepare("SELECT * FROM utilisateurs WHERE pseudo = :pseudo");
            $stmt->bindParam(':pseudo', $pseudo);
            $stmt->execute();
            $utilisateur = $stmt->fetch(PDO::FETCH_ASSOC);

            if ($utilisateur) {
                if (password_verify($mdp_clair, $utilisateur['mot_de_passe'])) {
                    
                    // Connexion réussie
                    $_SESSION['user_id'] = $utilisateur['id'];
                    $_SESSION['pseudo'] = $utilisateur['pseudo'];
                    $_SESSION['role'] = $utilisateur['role'];

                    switch ($utilisateur['role']) {
                        case 'ouvrier':
                            header("Location: dashboard_ouvrier.php");
                            break;
                        case 'responsable':
                            header("Location: dashboard_responsable.php");
                            break;
                        case 'root':
                            header("Location: dashboard_root.php");
                            break;
                        default:
                            header("Location: index.php");
                            break;
                    }
                    exit;

                } else {
                    $message_erreur = "Identifiant ou mot de passe incorrect.";
                }
            } else {
                $message_erreur = "Identifiant ou mot de passe incorrect.";
            }

        } catch (PDOException $e) {
            $message_erreur = "Erreur de base de données : " . $e->getMessage();
        }
    }
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Connexion au Royaume</title>
    
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

        /* Conteneur principal (la carte de connexion) */
        main {
            background-color: var(--color-surface);
            padding: 40px;
            border-radius: 12px;
            box-shadow: var(--shadow-card);
            width: 100%;
            max-width: 400px;
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
        form input[type="text"],
        form input[type="password"] {
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

        /* Message d'erreur */
        .message-erreur {
            background-color: #ffe5e5;
            color: #d32f2f;
            padding: 10px;
            border: 1px solid #d32f2f;
            border-radius: 6px;
            margin-bottom: 20px;
            text-align: center;
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
        
        /* Debug Console */
        .debug-codes {
            margin-top: 30px;
            padding: 15px;
            border: 1px dashed var(--color-secondary);
            border-radius: 6px;
            background-color: #FFFBE5;
            font-size: 0.9em;
        }
        .debug-codes h3 {
            color: #CC9900;
            margin-top: 0;
            font-size: 1.1em;
        }
        .debug-codes ul {
            list-style: none;
            padding-left: 0;
        }
        .debug-codes ul li {
            margin-bottom: 5px;
        }
    </style>
</head>
<body>

    <main>
        <div class="header-card">
            <img src="AH.jpg" alt="Logo Mon Royaume" class="logo-img-small">
            <h1>Accès au Royaume</h1>
            <p>Veuillez entrer vos identifiants pour accéder à votre tableau de bord.</p>
        </div>

        <?php if (!empty($message_erreur)): ?>
            <div class="message-erreur"><?php echo htmlspecialchars($message_erreur); ?></div>
        <?php endif; ?>
        
        <form method="POST">
            <label for="pseudo">IDENTIFIANT :</label>
            <input type="text" id="pseudo" name="pseudo" required value="<?php echo isset($pseudo) ? htmlspecialchars($pseudo) : ''; ?>">
            
            <label for="mot_de_passe">MOT DE PASSE :</label>
            <div class="password-container">
                <input type="password" id="mot_de_passe" name="mot_de_passe" required>
                <button type="button" class="toggle-mdp" onclick="togglePasswordVisibility()">👁️</button>
            </div>
            
            <button type="submit" class="btn-primary">SE CONNECTER</button>
        </form>
        
        <p style="margin-top: 30px; text-align: center; font-size: 0.9em;">
            <span style="color: var(--color-light-text); font-family: var(--font-body);">Nouvel arrivant ?</span> 
            <a href="inscription.php" class="secondary-link">REJOINDRE LE ROYAUME</a>.
        </p>

        <?php if ($show_codes): ?>
        <div class="debug-codes">
            <h3>// CONSOLE DE DÉBOGAGE ACTIF</h3>
            <p>CODES D'INSCRIPTION :</p>
            <ul>
                <li>Ouvrier (ANGEL) : <span style="color: var(--color-secondary); font-weight: bold;">ANGEL</span></li>
                <li>Responsable (RESP) : <span style="color: var(--color-secondary); font-weight: bold;">RESP</span></li>
            </ul>
            <p>Root par défaut : <span style="color: var(--color-primary); font-weight: bold;">root</span> / <span style="color: var(--color-secondary); font-weight: bold;">rootpass123</span></p>
            <a href="connexion.php" style="color: var(--color-primary); font-size: 0.9em; text-decoration: none;">[DÉSACTIVER CONSOLE]</a>
        </div>
        <?php endif; ?>
    </main>

    <script>
        function togglePasswordVisibility() {
            const passwordField = document.getElementById('mot_de_passe');
            const toggleButton = document.querySelector('.toggle-mdp');

            if (passwordField.type === 'password') {
                passwordField.type = 'text';
                toggleButton.textContent = '🔒'; // Icône pour masquer
            } else {
                passwordField.type = 'password';
                toggleButton.textContent = '👁️'; // Icône pour afficher
            }
        }
    </script>
</body>
</html>