<?php
session_start();
// Le portail n'a pas besoin de connexion à la base de données ici,
// mais la session est utilisée pour l'affichage conditionnel.
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mon Royaume - Portail Officiel | L'Ordre et la Perfection</title>
    
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@700;900&family=Roboto:wght@300,400,500,700&display=swap" rel="stylesheet">
    
    <style>
        /* ==================================== */
        /* CSS ROYAL SCINTILLANT PROFESSIONNEL V4 (Slide In, Sans Émojis) */
        /* ==================================== */
        
        :root {
            /* Couleurs */
            --color-primary: #8A2BE2;      /* Bleu-Violet Profond (Améthyste) */
            --color-secondary: #FFD700;    /* Jaune Doré (Goldenrod) */
            --color-highlight: #FFF068;    /* Or Clair pour l'accentuation au survol */
            
            /* Fonds et Texte */
            --color-background: #f8f0ff;   /* Lavande très claire */
            --color-surface: #FFFFFF;      /* Cartes blanches et Nav */
            --color-text: #333333;
            --color-text-light: #555555;
            
            /* Typographie */
            --font-display: 'Playfair Display', serif;
            --font-body: 'Roboto', sans-serif;
            
            /* Formes et Effets */
            --shadow-card: 0 4px 12px rgba(0, 0, 0, 0.08); 
            --shadow-hover: 0 12px 30px rgba(0, 0, 0, 0.15); 
            --border-radius-lg: 10px;
            --transition-timing: 0.35s ease-out; 
        }
        
        /* --- KEYFRAMES D'ANIMATION --- */

        /* 1. Animation d'apparition de gauche à droite */
        @keyframes slideInLeft {
            from {
                opacity: 0;
                transform: translate3d(-100px, 0, 0); /* Déplacement depuis la gauche */
            }
            to {
                opacity: 1;
                transform: translate3d(0, 0, 0);
            }
        }

        /* 2. Animation de pulsation pour le CTA */
        @keyframes pulse {
            0% { transform: scale(1); box-shadow: 0 0 0 0 rgba(255, 215, 0, 0.7); }
            70% { transform: scale(1.02); box-shadow: 0 0 0 10px rgba(255, 215, 0, 0); }
            100% { transform: scale(1); box-shadow: 0 0 0 0 rgba(255, 215, 0, 0); }
        }

        /* --- APPLICATION DES ANIMATIONS --- */

        /* Appliquer slideInLeft à toutes les sections principales et au footer, avec un délai */
        main section, footer {
            animation: slideInLeft 0.8s ease-out both;
        }

        /* Délai d'apparition progressif pour chaque section */
        #mission { animation-delay: 0.2s; }
        #departements { animation-delay: 0.4s; }
        #galerie { animation-delay: 0.6s; }
        #contact { animation-delay: 0.8s; }
        footer { animation-delay: 1s; }

        /* Animation pour le CTA */
        .navbar .cta-button { 
            animation: pulse 2s infinite ease-in-out 1.2s; 
        }
        .navbar .cta-button:hover {
            animation: none; 
        }


        /* --- BASE RESET ET TYPOGRAPHIE --- */
        *, *::before, *::after {
            box-sizing: border-box;
        }
        body { 
            font-family: var(--font-body); 
            background-color: var(--color-background); 
            margin: 0; 
            padding: 0; 
            color: var(--color-text); 
            line-height: 1.65; 
        }
        main { 
            padding: 40px 5vw; 
            max-width: 1300px;
            margin: 0 auto; 
        }
        h1, h2, h3 { 
            font-family: var(--font-display); 
            font-weight: 900;
        }
        h2 { 
            color: var(--color-primary); 
            border-bottom: 3px solid var(--color-secondary); 
            padding-bottom: 12px; 
            margin-top: 60px; 
            font-size: clamp(1.8em, 4vw, 2.5em);
            text-align: center;
            position: relative;
            letter-spacing: 1px;
        }
        /* Suppression du pseudo-élément d'émoji */
        h2::after { 
            content: none; /* Remplacer l'émoji par rien */
        }
        section { margin-bottom: 100px; } 

        /* --- NAVIGATION PRINCIPALE (HEADER/NAV) --- */
        .navbar {
            background-color: var(--color-surface);
            padding: 15px 5vw; 
            display: flex;
            justify-content: space-between;
            align-items: center;
            border-bottom: 5px solid var(--color-primary); 
            box-shadow: 0 2px 10px rgba(0,0,0,0.08); 
            position: sticky;
            top: 0;
            z-index: 1000;
        }
        .logo-group {
            display: flex;
            align-items: center;
            text-decoration: none;
            transition: opacity var(--transition-timing);
        }
        .logo-group:hover { opacity: 0.85; }
        .logo-group .logo-img {
            height: 50px; 
            width: 50px;
            border-radius: 50%; 
            object-fit: cover;
            margin-right: 15px;
            border: 3px solid var(--color-secondary); 
        }
        .logo-group .logo-text {
            font-family: var(--font-display);
            font-size: 1.8em;
            color: var(--color-primary);
            font-weight: 700;
        }
        .navbar nav {
            display: flex;
            align-items: center; 
        }
        .navbar nav a {
            color: var(--color-primary);
            text-decoration: none;
            padding: 10px 18px;
            margin-left: 10px;
            border-radius: 6px;
            transition: all var(--transition-timing);
            font-weight: 500;
            position: relative; 
        }
        .navbar nav a:hover {
            background-color: rgba(138, 43, 226, 0.05);
            color: var(--color-primary); 
            transform: none; 
        }
        .navbar nav a::after { 
            content: '';
            position: absolute;
            bottom: 0;
            left: 50%;
            width: 0;
            height: 3px;
            background-color: var(--color-secondary);
            transition: width var(--transition-timing), left var(--transition-timing);
        }
        .navbar nav a:hover::after {
            width: 100%;
            left: 0;
        }
        .navbar .cta-button { 
            background-color: var(--color-secondary) !important; 
            color: var(--color-primary) !important; 
            font-weight: 700 !important;
            border: 2px solid var(--color-secondary);
            margin-left: 25px !important;
            padding: 10px 25px !important;
            box-shadow: var(--shadow-card);
        }
        .navbar .cta-button:hover {
            background-color: var(--color-highlight) !important;
            color: var(--color-primary) !important; 
            box-shadow: 0 0 15px var(--color-secondary); 
        }
        
        /* --- BANNIÈRE HÉROÏQUE --- */
        .hero-banner {
            background-image: url('AH.jpg'); 
            background-size: cover;
            background-position: center 35%;
            height: 60vh; 
            min-height: 400px;
            position: relative;
            box-shadow: 0 8px 25px rgba(0, 0, 0, 0.4);
            display: flex;
        }
        .hero-overlay {
            background: linear-gradient(to top, rgba(0, 0, 0, 0.7) 0%, rgba(0, 0, 0, 0.3) 100%); 
            position: absolute;
            inset: 0;
            display: flex;
            flex-direction: column;
            justify-content: flex-end;
            align-items: center;
            padding: 40px 20px;
        }
        .hero-title {
            color: white;
            font-family: var(--font-display);
            font-size: clamp(2.5em, 5.5vw, 4.8em);
            margin: 0;
            text-transform: uppercase;
            letter-spacing: 4px;
            text-shadow: 4px 4px 12px rgba(0, 0, 0, 0.9);
            /* Animation titre */
            animation: slideInLeft 1s ease-out both; 
        }
        .hero-subtitle {
            color: var(--color-highlight); 
            font-size: clamp(1.4em, 2.5vw, 2em);
            margin-top: 15px;
            font-style: italic;
            text-shadow: 2px 2px 6px rgba(0, 0, 0, 0.8);
            font-weight: 300;
             /* Animation sous-titre */
            animation: slideInLeft 1s ease-out 0.2s both; 
        }

        /* --- CONTENU GÉNÉRAL --- */
        blockquote {
            margin: 30px auto;
            max-width: 800px;
            padding: 20px 30px;
            border-left: 6px solid var(--color-primary);
            background-color: rgba(138, 43, 226, 0.03);
            font-style: italic;
            color: var(--color-text-light);
            font-size: 1.1em;
            text-align: center;
        }

        /* --- CARTES ET STRUCTURE GRID --- */
        .card-container {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 40px;
            margin-top: 40px;
        }
        .dept-card {
            background-color: var(--color-surface);
            padding: 30px;
            border-radius: var(--border-radius-lg);
            box-shadow: var(--shadow-card);
            border-left: 5px solid var(--color-secondary); 
            transition: transform var(--transition-timing), box-shadow var(--transition-timing), border-left-color var(--transition-timing);
            position: relative;
            overflow: hidden;
            /* Animation individuelle pour les cartes */
            animation: slideInLeft 0.8s ease-out 0.8s both; 
        }
        /* Décalage supplémentaire pour les cartes */
        .card-container article:nth-child(1) { animation-delay: 0.6s; }
        .card-container article:nth-child(2) { animation-delay: 0.7s; }
        .card-container article:nth-child(3) { animation-delay: 0.8s; }
        .card-container article:nth-child(4) { animation-delay: 0.9s; }

        .dept-card:hover {
            transform: translateY(-10px); 
            box-shadow: var(--shadow-hover);
            border-left-color: var(--color-primary); 
        }
        .dept-card h3 {
            color: var(--color-primary);
            margin-top: 0;
            font-size: 1.6em;
            border-bottom: 2px solid var(--color-background); 
            padding-bottom: 10px;
            margin-bottom: 15px;
            font-weight: 700;
        }
        .dept-card p {
            color: var(--color-text-light);
            font-size: 1em; 
        }
        
        /* --- GALERIE D'IMAGES --- */
        .image-gallery {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr)); 
            gap: 40px;
            margin-top: 40px;
        }
        .gallery-item {
            background-color: var(--color-surface);
            border-radius: var(--border-radius-lg);
            overflow: hidden; 
            box-shadow: var(--shadow-hover); 
            border: 1px solid rgba(138, 43, 226, 0.1); 
            transition: box-shadow var(--transition-timing);
        }
        .gallery-item:hover {
            box-shadow: 0 15px 35px rgba(0, 0, 0, 0.2);
        }
        .gallery-item img {
            width: 100%;
            height: 300px; 
            object-fit: cover; 
            display: block;
            transition: transform 0.6s cubic-bezier(0.25, 0.46, 0.45, 0.94); 
        }
        .image-caption {
            padding: 15px;
            text-align: center;
            font-style: normal; 
            color: var(--color-primary);
            font-size: 1.05em;
            font-weight: 500;
            border-top: 5px solid var(--color-secondary);
        }

        /* --- CONTACT ET FOOTER --- */
        #contact address {
            padding: 30px;
            border-left: 6px solid var(--color-secondary);
            background-color: rgba(138, 43, 226, 0.05); 
            border-radius: var(--border-radius-lg);
            line-height: 2.2;
            font-style: normal; 
            max-width: 600px;
            margin: 30px auto; 
            box-shadow: var(--shadow-card);
        }
        #contact a { 
            color: var(--color-primary); 
            text-decoration: none; 
            border-bottom: 2px solid var(--color-primary);
            padding-bottom: 1px;
            font-weight: 500;
            transition: color var(--transition-timing), border-color var(--transition-timing);
        }
        #contact a:hover {
            color: var(--color-secondary);
            border-bottom-color: var(--color-secondary);
        }

        footer {
            background-color: var(--color-primary);
            color: #EFEFEF;
            text-align: center;
            padding: 35px 5vw;
            margin-top: 80px;
            font-size: 0.95em;
        }
        footer a { 
            color: var(--color-secondary); 
            text-decoration: none; 
            transition: color var(--transition-timing);
            font-weight: 700;
        }
        footer a:hover { 
            color: var(--color-highlight); 
            text-decoration: underline;
        }

        /* --- RESPONSIVE DESIGN --- */
        @media (max-width: 950px) {
            .navbar {
                flex-direction: column;
                padding: 15px 5vw;
                position: relative; 
            }
            .navbar nav {
                margin-top: 15px;
                flex-wrap: wrap;
                justify-content: center;
            }
            .navbar nav a {
                margin: 5px;
                padding: 8px 12px;
            }
            .navbar .cta-button {
                margin-top: 10px !important;
                animation: none; 
            }
        }
        @media (max-width: 650px) {
            .logo-group .logo-text {
                font-size: 1.6em;
            }
            main { padding: 30px 4vw; }
            .hero-overlay { padding: 20px 15px; }
            .hero-title { letter-spacing: 2px; }
        }

    </style>
</head>
<body>

    <header>
        <div class="navbar">
            <a href="index.php" class="logo-group" aria-label="Accueil - Mon Royaume">
                <img src="AH.jpg" alt="Sceau Royal Mon Royaume" class="logo-img">
                <div class="logo-text">Mon Royaume</div>
            </a>
            
            <nav aria-label="Navigation Principale">
                <a href="#mission">Notre Mission</a>
                <a href="#departements">Piliers</a>
                <a href="#galerie">Galerie</a>
                <a href="#contact">Contact</a>
                
                <?php 
                if (isset($_SESSION['role'])) {
                    $role = htmlspecialchars($_SESSION['role']);
                    $dashboard_link = "dashboard_" . $role . ".php";
                    $text = ($role === 'root') ? 'ADMINISTRATION' : 'TABLEAU DE BORD';
                    echo '<a href="' . $dashboard_link . '" class="cta-button">' . $text . '</a>';
                } else {
                    echo '<a href="connexion.php" class="cta-button">Connexion / Inscription</a>';
                }
                ?>
            </nav>
        </div>

        <div class="hero-banner" role="banner" aria-label="Bannière d'accueil du Royaume">
            <div class="hero-overlay">
                <h1 class="hero-title">BIENVENUE DANS MON ROYAUME</h1>
                <p class="hero-subtitle">Gestion Intelligente et Sécurisée de Nos Opérations</p>
            </div>
        </div>
    </header>

    <main>
        
        <section id="mission" aria-labelledby="mission-title">
            <h2 id="mission-title">Notre Mission Royale</h2>
            <p>
                Mon Royaume s'engage à l'**excellence** et à l'**harmonie** de ses opérations. Nous exploitons la puissance des systèmes numériques pour garantir que chaque membre de notre équipe, de l'Ouvrier au Superviseur, dispose des outils nécessaires pour atteindre la plus haute efficacité. Notre portail est le centre névralgique de notre collaboration.
            </p>
            <blockquote cite="Source Royale">
                <p>*« L'ordre est le premier pas vers la perfection. »*</p>
            </blockquote>
        </section>

        <section id="departements" aria-labelledby="dept-title">
            <h2 id="dept-title">Les Piliers de Notre Organisation</h2>
            <p style="text-align: center; color: var(--color-text-light); margin-bottom: 50px;">
                Chaque département est essentiel pour la stabilité et le progrès de notre œuvre.
            </p>
            <div class="card-container">
                
                <article class="dept-card" aria-label="Département des Opérations">
                    <h3>Opérations</h3>
                    <p>Le cœur de notre action, assurant une exécution fluide et efficace des tâches quotidiennes. Géré principalement par les **Ouvriers** et coordonné par les **Responsables**.</p>
                </article>
                
                <article class="dept-card" aria-label="Département de la Stratégie">
                    <h3>Stratégie</h3>
                    <p>L'esprit qui guide nos ambitions, nos expansions futures et la planification à long terme. La responsabilité principale des **Responsables** en collaboration avec le **Root**.</p>
                </article>
                
                <article class="dept-card" aria-label="Département de la Supervision">
                    <h3>Supervision Système</h3>
                    <p>Le siège du pouvoir Root, où la gestion complète du système, la sécurité et l'attribution des rôles sont administrées avec la plus grande prudence.</p>
                </article>
                
                <article class="dept-card" aria-label="Département du Bien-être">
                    <h3>Bien-être & Formation</h3>
                    <p>Veillant à l'épanouissement, à la formation et à l'équilibre de chaque membre de notre Royaume pour assurer une motivation constante.</p>
                </article>

            </div>
        </section>

        <section id="galerie" aria-labelledby="galerie-title">
            <h2 id="galerie-title">Le Royaume en Image</h2>
            <p style="text-align: center; color: var(--color-text-light); margin-bottom: 50px;">
                Au-delà de nos opérations, nous cultivons un environnement d'apprentissage et de communauté pour nos membres.
            </p>
            <div class="image-gallery">
                
                <figure class="gallery-item">
                    <img src="Enfants_ecole.jpg" alt="Groupe d'enfants souriants dans un cadre scolaire" loading="lazy">
                    <figcaption class="image-caption">
                        Nos futures générations, l'héritage de notre Royaume.
                    </figcaption>
                </figure>
                
                <figure class="gallery-item">
                    <img src="la-chorale-est-composee-d-une-dizaine-d-enfants-de-9-a-13-ans-qui-chantent-leur-esperance-en-un-monde-plus-beau-1438970669.jpg" alt="Chorale d'enfants chantant lors d'un événement communautaire" loading="lazy">
                    <figcaption class="image-caption">
                        Le Département du Bien-être encourage l'expression artistique.
                    </figcaption>
                </figure>
                
            </div>
        </section>
        
        <section id="contact" aria-labelledby="contact-title">
            <h2 id="contact-title">Contactez Nos Émissaires</h2>
            <p style="text-align: center;">Pour toute question administrative ou technique, notre équipe de soutien est à votre disposition.</p>
            <address>
                <strong>Courriel Royal :</strong> <a href="mailto:contact@monroyaume.com">contact@monroyaume.com</a><br>
                <strong>Ligne Directe :</strong> <a href="tel:+1XXXXXXXXXX">+1 (XXX) XXX-XXXX</a> (Disponible de 9h à 17h, heure du Royaume)<br>
                <strong>Localisation :</strong> Le Siège Céleste, Allée des Souverains, Cité Royale             </address>
        </section>
    </main>

    <footer>
        <p>
            &copy; <?php echo date("Y"); ?> Mon Royaume. Tous droits réservés. | 
            
            <a href="connexion.php">Accès Membre</a>
        </p>
    </footer>

</body>
</html>