<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

include_once("function.php");
include_once("db.php");
include_once("header.php");

// Récupérer les derniers articles
$stmt = $pdo->query("
    SELECT f.*, u.username, u.picture_profil 
    FROM articles f 
    LEFT JOIN users u ON f.author_id = u.id 
    ORDER BY f.created_at DESC 
    LIMIT 6
");
$latest_articles = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Récupérer les catégories (rubriques)
$stmt = $pdo->query("
    SELECT rubrique, COUNT(*) as count 
    FROM articles 
    WHERE rubrique IS NOT NULL AND rubrique != '' 
    GROUP BY rubrique 
    ORDER BY rubrique ASC
");
$rubriques = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!-- Hero Section -->
<section class="hero-banner">
    <div class="hero-content">
        <h1 class="hero-title">Collection Aur'art</h1>
        <p class="hero-subtitle">Esquisses de l'Art & son marché</p>
    </div>
</section>

<!-- Section Présentation -->
<section class="about-section">
    <h2>Notre mission</h2>
    <p class="about-text">
        L'Association de passionnés qui s'engage à valoriser le patrimoine artistique sous toutes ses « formes ».
    </p>
    <p class="about-text">
        Notre association se donne pour mission de questionner, valoriser et transmettre l'histoire de l'art dans toute sa complexité. À travers nos articles, nous explorons les œuvres, les courants artistiques, les procès, les dynamiques du marché de l'art et les enjeux contemporains de la protection patrimoniale.
    </p>
    <p class="about-text">
        Nous refusons une approche élitiste de l'art qui le cantonne aux cercles initiés. Notre conviction est que la compréhension des œuvres, leur contexte historique et leur circulation actuelle constituent un enjeu culturel fondamental.
    </p>
    <p class="about-text">
        Nos rubriques interrogent aussi bien la beauté formelle des créations que les questions juridiques, économiques et éthiques qui traversent le marché de l'art.
    </p>
    <p class="about-text">
        Écrire sur l'art, c'est aussi prendre position : face aux inégalités d'accès à la culture, face à la marchandisation croissante des œuvres, face à l'urgence de préserver et transmettre notre héritage artistique.
    </p>
</section>

<!-- Section Rubriques -->
<section style="margin: 4rem 0;">
    <h2 style="text-align: center; margin-bottom: 3rem; font-size: 2.5rem;">Nos rubriques</h2>
    
    <div class="rubriques-grid">
        <?php
        $rubrique_icons = [
            'Procès en art' => '⚖️',
            'Rencontres' => '🤝',
            'Au fil des œuvres' => '🎨',
            'Histoire des arts' => '📜',
            'Art contemporain' => '🖼️',
            'Évènement' => '🎭',
            'Marché de l\'art' => '💼'
        ];
        
        foreach ($rubriques as $rubrique):
            $name = $rubrique['rubrique'];
            $icon = $rubrique_icons[$name] ?? '🎨';
        ?>
        <article class="rubrique-card">
            <div class="rubrique-icon"><?= $icon ?></div>
            <h3><?= htmlspecialchars($name) ?></h3>
            <p><?= $rubrique['count'] ?> article<?= $rubrique['count'] > 1 ? 's' : '' ?></p>
            <a href="rubrique.php?name=<?= urlencode($name) ?>" class="btn-rubrique">Voir la rubrique</a>
        </article>
        <?php endforeach; ?>
    </div>
</section>

<!-- Section Articles récents -->
<?php if (!empty($latest_articles)): ?>
<section style="margin: 4rem 0;">
    <h2 style="text-align: center; margin-bottom: 3rem; font-size: 2.5rem;">Articles récents</h2>
    
    <div class="article-list">
        <?php foreach ($latest_articles as $article): ?>
        <article class="article-card">
            <?php if (!empty($article['image']) && file_exists($article['image'])): ?>
                <img src="<?= htmlspecialchars($article['image']) ?>" 
                     alt="<?= htmlspecialchars($article['title']) ?>"
                     class="article-image">
            <?php endif; ?>
            
            <div class="article-body">
                <?php if (!empty($article['rubrique'])): ?>
                    <span class="article-rubrique">
                        <?= htmlspecialchars($article['rubrique']) ?>
                    </span>
                <?php endif; ?>
                
                <h3 class="article-title">
                    <a href="article.php?slug=<?= urlencode($article['slug']) ?>">
                        <?= htmlspecialchars($article['title']) ?>
                    </a>
                </h3>
                
                <?php if (!empty($article['content'])): ?>
                    <p class="article-excerpt">
                        <?= htmlspecialchars(substr(strip_tags($article['content']), 0, 150)) ?>...
                    </p>
                <?php endif; ?>
                
                <div class="article-meta">
                    <?php if (!empty($article['picture_profil']) && file_exists($article['picture_profil'])): ?>
                        <img src="<?= htmlspecialchars($article['picture_profil']) ?>" 
                             class="author-avatar"
                             alt="<?= htmlspecialchars($article['username'] ?? 'Anonyme') ?>">
                    <?php endif; ?>
                    <div>
                        <strong><?= htmlspecialchars($article['username'] ?? 'Anonyme') ?></strong>
                        <span style="display: block; font-size: 0.85rem;">
                            <?= date('d/m/Y', strtotime($article['created_at'])) ?>
                        </span>
                    </div>
                </div>
            </div>
        </article>
        <?php endforeach; ?>
    </div>
    
    <div style="text-align: center; margin-top: 3rem;">
        <a href="articles.php" class="btn-rubrique">Voir tous les articles</a>
    </div>
</section>
<?php endif; ?>

<?php include 'footer.php'; ?>