<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ANGEL WEB - Angels House</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
<?php
// Initialisation de la variable $actualites si elle n'existe pas
if(!isset($actualites)) {
    $actualites = array();
}

// Inclusion des fichiers de configuration et header
if(file_exists('includes/config.php')) {
    include 'includes/config.php';
}
if(file_exists('includes/header.php')) {
    include 'includes/header.php';
}

?>

<div class="container">
    <header class="main-header">
        <h1>ANGEL WEB</h1>
        <p class="subtitle">shalom lumiere des nations bienvenue sur le site d'angels house</p>
    </header>

    <nav class="main-nav">
        <ul>
            <li><a href="index.php" class="active">Accueil</a></li>
            <li><a href="trombinoscope.php">Trombinoscope</a></li>
            <li><a href="calendrier.php">Calendrier</a></li>
            <li><a href="rh-portal.php">Portail RH</a></li>
        </ul>
    </nav>

    <section class="actualites-section">
        <h2>Fil d'actualités</h2>
        <div class="actualites-grid">
            <?php if(isset($actualites) && is_array($actualites) && !empty($actualites)): ?>
                <?php foreach($actualites as $actu): ?>
                <article class="actu-card">
                    <h3><?php echo htmlspecialchars(isset($actu['titre']) ? $actu['titre'] : ''); ?></h3>
                    <?php if(isset($actu['date']) && !empty($actu['date'])): ?>
                    <time datetime="<?php echo htmlspecialchars($actu['date']); ?>">
                        <?php echo date('d/m/Y', strtotime($actu['date'])); ?>
                    </time>
                    <?php endif; ?>
                    <p><?php echo htmlspecialchars(isset($actu['contenu']) ? $actu['contenu'] : ''); ?></p>
                </article>
                <?php endforeach; ?>
            <?php else: ?>
                <p>Aucune actualité pour le moment.</p>
            <?php endif; ?>
    </section>

    <section class="services-section">
        <h2>Accès rapide aux services</h2>
        <div class="services-grid">
            <div class="service-card">
                <h3>📋 Trombinoscope</h3>
                <p>Fiches de contact (nom, prénom, service, photo et poste)</p>
                <a href="trombinoscope.php" class="btn">Accéder</a>
            </div>

            <div class="service-card">
                <h3>📅 Calendrier des évènements</h3>
                <p>Pour ne rien manquer des événements importants</p>
                <a href="calendrier.php" class="btn">Voir le calendrier</a>
            </div>

            <div class="service-card">
                <h3>👥 Portail RH</h3>
                <p>Dépôt de pointage et services ressources humaines</p>
                <a href="rh-portal.php" class="btn">Accéder au portail</a>
            </div>
        </div>
    </section>

    <section class="contact-section">
        <h2>📞 Nous contacter</h2>
        <div class="contact-grid">
            <div class="contact-card">
                <h3>📱 Numéros à joindre</h3>
                <div class="contact-list">
                    <p><strong>Téléphone :</strong> <a href="tel:+2250100646063">+225 0100646063</a></p>
                    <p><strong>Mobile :</strong> <a href="tel:+2250768357198">+2250768357198</a></p>
                    <p><strong>Email :</strong> <a href="mailto:contact@angelshouse.fr">contact@angelshouse.ci</a></p>
                </div>
            </div>

            <div class="contact-card">
                <h3>🌐 Nos plateformes</h3>
                <div class="contact-list">
                    <p><strong>Site web :</strong> <a href="https://www.angelshouse.fr" target="_blank">www.angelshouse.fr</a></p>
                    <p><strong>Facebook :</strong> <a href="#" target="_blank">Angels House</a></p>
                    <p><strong>Instagram :</strong> <a href="#" target="_blank">@angelshouse</a></p>
                </div>
            </div>
        </div>
    </section>

    <section class="goodbye-section">
        <p class="goodbye-message">Au revoir et à bientôt ! 👋</p>
    </section>

</div>

<?php 
if(file_exists('includes/footer.php')) {
    include 'includes/footer.php';
}
?>
</body>
</html>
