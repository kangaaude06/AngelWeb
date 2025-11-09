<?php
session_start();
require_once 'config.php'; 

// Protection d'accès : Seul le rôle 'root' est autorisé
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'root') {
    header("Location: connexion.php");
    exit;
}

$pseudo_root = $_SESSION['pseudo'];
$message_status = "";

// ---------------------------------------------------
// --- FONCTIONNALITÉS DE GESTION (SUPPRESSION & ÉDITION) ---

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['action'])) {
    $user_id_cible = isset($_POST['user_id']) ? intval($_POST['user_id']) : 0;
    $action = $_POST['action'];
    $current_user_id = $_SESSION['user_id'];
    
    // Vérification de sécurité : Empêcher l'utilisateur de s'auto-gérer
    if ($user_id_cible === $current_user_id && $action === 'supprimer_utilisateur') {
        $message_status = "<p style='color: red;'>❌ Erreur : Vous ne pouvez pas supprimer votre propre compte Root !</p>";
    } else {
        try {
            if ($action === 'supprimer_utilisateur') {
                // --- LOGIQUE DE SUPPRESSION ---
                
                $stmt_check = $pdo->prepare("SELECT role FROM utilisateurs WHERE id = ?");
                $stmt_check->execute([$user_id_cible]);
                $user_to_delete = $stmt_check->fetch(PDO::FETCH_ASSOC);

                if (!$user_to_delete) {
                    $message_status = "<p style='color: orange;'>⚠️ Utilisateur non trouvé pour la suppression.</p>";
                } else {
                    $stmt_delete = $pdo->prepare("DELETE FROM utilisateurs WHERE id = ?");
                    $stmt_delete->execute([$user_id_cible]);

                    if ($stmt_delete->rowCount() > 0) {
                        $message_status = "<p style='color: green;'>✅ Utilisateur ID: {$user_id_cible} supprimé avec succès.</p>";
                    } else {
                         $message_status = "<p style='color: orange;'>⚠️ Aucun utilisateur trouvé avec cet ID à supprimer.</p>";
                    }
                }

            } elseif ($action === 'changer_role' && isset($_POST['nouveau_role'])) {
                // --- LOGIQUE D'ÉDITION DE RÔLE ---
                $nouveau_role = trim($_POST['nouveau_role']);
                $roles_valides = ['ouvrier', 'responsable', 'root'];

                if (!in_array($nouveau_role, $roles_valides)) {
                    $message_status = "<p style='color: red;'>❌ Erreur : Rôle invalide spécifié.</p>";
                } else {
                    $stmt_update = $pdo->prepare("UPDATE utilisateurs SET role = ? WHERE id = ?");
                    $stmt_update->execute([$nouveau_role, $user_id_cible]);

                    if ($stmt_update->rowCount() > 0) {
                        $message_status = "<p style='color: green;'>✅ Rôle de l'utilisateur ID: {$user_id_cible} mis à jour vers **" . ucfirst($nouveau_role) . "**.</p>";
                    } else {
                        $message_status = "<p style='color: orange;'>⚠️ Rôle inchangé (ou utilisateur non trouvé).</p>";
                    }
                }
            }
        } catch (PDOException $e) {
            $message_status = "<p style='color: red;'>❌ Erreur lors de l'action sur l'utilisateur : " . $e->getMessage() . "</p>";
        }
    }
}

// --- Récupération de la liste des utilisateurs (Après toutes les actions) ---
try {
    $stmt = $pdo->prepare("SELECT id, pseudo, role, mot_de_passe FROM utilisateurs ORDER BY FIELD(role, 'root', 'responsable', 'ouvrier'), pseudo ASC");
    $stmt->execute();
    $liste_utilisateurs = $stmt->fetchAll(PDO::FETCH_ASSOC);

} catch (PDOException $e) {
    $message_status .= "Erreur lors de la récupération de la liste des utilisateurs : " . $e->getMessage();
    $liste_utilisateurs = []; 
}
// ---------------------------------------------------

?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Tableau de Bord - Root (Superviseur)</title>
    
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
            --shadow-subtle: 0 4px 10px rgba(0, 0, 0, 0.08); /* Ombre douce */
        }
        body { font-family: var(--font-body); background-color: var(--color-background); margin: 0; padding: 0; color: var(--color-text); }
        
        /* NAVIGATION ET EN-TÊTE UNIFORME */
        .navbar {
            background-color: var(--color-surface);
            padding: 10px 50px; 
            display: flex;
            justify-content: space-between;
            align-items: center;
            border-bottom: 2px solid var(--color-primary);
            box-shadow: 0 2px 5px rgba(0,0,0,0.05);
        }
        .logo-group {
            display: flex;
            align-items: center;
        }
        .logo-group .logo-img {
            height: 65px; 
            width: auto;
            border-radius: 50%; 
            object-fit: cover;
            margin-right: 10px; 
           
        }
        .logo-group .logo-text {
            font-family: var(--font-display);
            font-size: 1.5em;
            color: var(--color-primary);
            font-weight: bold;
        }
        .navbar nav a, .navbar nav span {
            color: var(--color-primary);
            text-decoration: none;
            padding: 8px 15px;
            margin-left: 15px;
            border-radius: 4px;
            transition: background-color 0.3s, color 0.3s;
        }
        .navbar nav a:hover {
            background-color: var(--color-primary);
            color: white;
        }
        
        main { padding: 40px 50px; }
        h2 { font-family: var(--font-display); color: var(--color-primary); border-bottom: 3px solid var(--color-secondary); padding-bottom: 10px; margin-top: 40px; }
        
        /* Stats Boxes */
        .stats-container { display: flex; gap: 20px; margin-bottom: 40px; }
        .stat-box { background-color: var(--color-surface); padding: 20px; border-radius: 8px; box-shadow: var(--shadow-subtle); border-left: 5px solid var(--color-primary); flex: 1; }
        .stat-box h3 { margin-top: 0; color: var(--color-light-text); font-size: 1em; }
        .stat-box p { font-size: 2em; margin: 5px 0 0 0; color: var(--color-text); font-family: var(--font-display); }

        /* Tableau Utilisateurs */
        .utilisateurs-table { width: 100%; border-collapse: collapse; margin-top: 20px; }
        .utilisateurs-table th, .utilisateurs-table td { border: 1px solid #ddd; padding: 12px; text-align: left; }
        .utilisateurs-table th { background-color: var(--color-primary); color: white; font-family: var(--font-body); }
        .utilisateurs-table tr:nth-child(even) { background-color: #fcfcfc; }
        .utilisateurs-table tr:hover { background-color: #f7f0ff; } /* Violet très très clair au survol */
        
        /* Bouton Afficher MDP */
        .btn-toggle-mdp { background-color: #555; color: white; border: none; padding: 8px 15px; border-radius: 5px; cursor: pointer; margin-bottom: 20px; transition: background-color 0.3s; }
        .btn-toggle-mdp:hover { background-color: #777; }
        
        /* Gestion de l'affichage du mot de passe */
        .mdp-hache.cache { display: none; }
        .mdp-hache.affichage-clair { color: var(--color-light-text); font-style: italic; }
        
        /* Formulaire d'Action (Édition/Suppression) */
        .action-cell form { margin: 2px 0; }
        .action-cell button { font-size: 0.9em; padding: 4px 8px; border-radius: 3px; cursor: pointer; border: none; transition: opacity 0.3s; }
        .action-cell button:hover { opacity: 0.8; }
        .btn-delete { background-color: #FF6347; color: white; } /* Rouge Tomate */
        .btn-edit { background-color: #008000; color: white; } /* Vert */
        
        .role-form { display: flex; gap: 5px; align-items: center; }
        .role-form select { padding: 4px; border-radius: 3px; border: 1px solid #ccc; }
        
        /* Message de statut */
        .status-message p { padding: 10px; margin-bottom: 20px; border-radius: 5px; font-weight: bold; }
        .status-message p[style*="color: red"] { border: 1px solid red; background-color: #FFE5E5; }
        .status-message p[style*="color: green"] { border: 1px solid green; background-color: #E5FFE5; }

        footer {
            background-color: var(--color-primary);
            color: white;
            text-align: center;
            padding: 15px 50px;
            margin-top: 50px;
        }
    </style>
</head>
<body>

    <div class="navbar">
        <div class="logo-group">
            <img src="AH.jpg" alt="Logo Mon Royaume" class="logo-img">
            <div class="logo-text">PANNEAU ROOT | <?php echo htmlspecialchars($pseudo_root); ?></div>
        </div>
        <nav>
            <a href="deconnexion.php" style="background-color: #FF6347; color: white; font-weight: bold;">🔒 Déconnexion</a>
        </nav>
    </div>

    <main>
        
        <h2> Aperçu du Royaume</h2>
        
        <?php 
        // Affichage du message de statut
        if (!empty($message_status)) {
            echo "<div class='status-message'>" . $message_status . "</div>";
        }
        
        // Calcul des statistiques
        $count_total = count($liste_utilisateurs);
        $count_root = count(array_filter($liste_utilisateurs, function($user) { return $user['role'] === 'root'; }));
        $count_responsable = count(array_filter($liste_utilisateurs, function($user) { return $user['role'] === 'responsable'; }));
        $count_ouvrier = count(array_filter($liste_utilisateurs, function($user) { return $user['role'] === 'ouvrier'; }));
        ?>
        
        <div class="stats-container">
            <div class="stat-box" style="border-left-color: var(--color-primary);"> 
                <h3>👥 TOTAL UTILISATEURS</h3>
                <p><?php echo $count_total; ?></p>
            </div>
            <div class="stat-box" style="border-left-color: var(--color-secondary);">
                <h3> SUPERVISEURS (Root)</h3>
                <p><?php echo $count_root; ?></p>
            </div>
            <div class="stat-box" style="border-left-color: #2196F3;">
                <h3> RESPONSABLES</h3>
                <p><?php echo $count_responsable; ?></p>
            </div>
            <div class="stat-box" style="border-left-color: #4CAF50;">
                <h3> OUVRIERS</h3>
                <p><?php echo $count_ouvrier; ?></p>
            </div>
        </div>
        
        <section id="utilisateurs">
            <h2>  GESTION DES UTILISATEURS</h2>
            
            <button onclick="toggleMdp()" class="btn-toggle-mdp">👁️ Afficher les Hachages</button>

            <table class="utilisateurs-table">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Pseudo</th>
                        <th>Rôle</th>
                        <th class="mdp-hache">Mot de Passe (Hachage)</th>
                        <th>Actions</th> 
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($liste_utilisateurs as $utilisateur): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($utilisateur['id']); ?></td>
                        <td><?php echo htmlspecialchars($utilisateur['pseudo']); ?></td>
                        
                        <td class="action-cell">
                            <?php if ($utilisateur['id'] != $_SESSION['user_id']): ?>
                                <form method="POST" class="role-form">
                                    <input type="hidden" name="action" value="changer_role">
                                    <input type="hidden" name="user_id" value="<?php echo htmlspecialchars($utilisateur['id']); ?>">
                                    <select name="nouveau_role">
                                        <option value="root" <?php if ($utilisateur['role'] == 'root') echo 'selected'; ?>>Root</option>
                                        <option value="responsable" <?php if ($utilisateur['role'] == 'responsable') echo 'selected'; ?>>Responsable</option>
                                        <option value="ouvrier" <?php if ($utilisateur['role'] == 'ouvrier') echo 'selected'; ?>>Ouvrier</option>
                                    </select>
                                    <button type="submit" class="btn-edit" title="Changer le rôle">✏️</button>
                                </form>
                            <?php else: ?>
                                <span style="font-weight: bold; color: var(--color-primary);"><?php echo htmlspecialchars(ucfirst($utilisateur['role'])); ?> (Actif)</span>
                            <?php endif; ?>
                        </td>
                        
                        <td>
                            <span class="mdp-hache cache"><?php echo htmlspecialchars($utilisateur['mot_de_passe']); ?></span>
                            <span class="mdp-hache affichage-clair">*** Haché ***</span>
                        </td>
                        
                        <td class="action-cell">
                            <?php if ($utilisateur['id'] != $_SESSION['user_id']): ?>
                                <form method="POST" onsubmit="return confirm('Êtes-vous sûr de vouloir SUPPRIMER l\'utilisateur **<?php echo htmlspecialchars($utilisateur['pseudo']); ?>** ? Cette action est irréversible.');" style="display:inline;">
                                    <input type="hidden" name="action" value="supprimer_utilisateur">
                                    <input type="hidden" name="user_id" value="<?php echo htmlspecialchars($utilisateur['id']); ?>">
                                    <button type="submit" class="btn-delete" title="Supprimer définitivement">🗑️ Supprimer</button>
                                </form>
                            <?php else: ?>
                                <span style="color: #ccc;">(Action Interdite)</span>
                            <?php endif; ?>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </section>
        
        <section id="missions" style="margin-top: 50px;">
            <h2> TÂCHES ET MISSIONS (à venir)</h2>
            <p style="color: var(--color-light-text); border-left: 3px solid var(--color-primary); padding-left: 15px; font-style: italic;">
                **Planning Général :** Ici, vous pourrez bientôt visualiser, créer et assigner les missions à tous les responsables et ouvriers.
            </p>
        </section>
        
    </main>
    
    <script>
        function toggleMdp() {
            const hachages = document.querySelectorAll('.mdp-hache');
            const bouton = document.querySelector('.btn-toggle-mdp');
            // Détermine l'état actuel basé sur le premier élément (s'il est masqué)
            const estMasque = hachages.length > 0 ? hachages[0].classList.contains('cache') : true;

            hachages.forEach(h => {
                h.classList.toggle('cache');
            });

            if (estMasque) {
                bouton.textContent = '🙈 Masquer les Hachages';
            } else {
                bouton.textContent = '👁️ Afficher les Hachages';
            }
        }
    </script>

    <footer>
        <p>
            © <?php echo date("Y"); ?> Mon Royaume - Panneau de Supervision Root
        </p>
    </footer>

</body>
</html>