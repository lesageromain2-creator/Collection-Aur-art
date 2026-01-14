<?php
session_start();
require 'db.php';
include 'header.php'; 

// Récupérer toutes les rubriques distinctes
$rubriques_query = $pdo->query("
    SELECT DISTINCT rubrique 
    FROM articles 
    WHERE rubrique IS NOT NULL AND rubrique != '' 
    ORDER BY rubrique ASC
");
$rubriques = $rubriques_query->fetchAll(PDO::FETCH_COLUMN);

// Gestion des favoris
if (!empty($_SESSION['user_id']) && isset($_POST['toggle_favorite'], $_POST['product_id'])) {
    require_once 'includes/favorites-handler.php';
    toggleFavorite($pdo, $_SESSION['user_id'], (int)$_POST['product_id']);
    header("Location: " . $_SERVER['PHP_SELF']);
    exit();
}
?>

<!-- Header page rubriques -->
<header class="rubrique_header">
    <div class="rubrique_container">
        <h1 class="logo">
            <a href="rubrique.php">Bienvenue dans votre espace rubriques !</a>
        </h1>
        <h4>Parcourez nos rubriques et découvrez tous les articles que nous proposons.</h4>
        
        <nav class="main-nav_rub"> 
            <?php foreach ($rubriques as $rub): ?>
                <a href="#<?= htmlspecialchars(str_replace(' ', '_', $rub)) ?>">
                    <?= htmlspecialchars($rub) ?>
                </a>
            <?php endforeach; ?>
        </nav>
    </div>
</header>

<!-- Menu sticky navigation -->
<div class="rubrique-menu">
    <nav>
        <?php foreach ($rubriques as $rub): ?>
            <a href="#<?= htmlspecialchars(str_replace(' ', '_', $rub)) ?>">
                <?= htmlspecialchars($rub) ?>
            </a>
        <?php endforeach; ?>
    </nav>
</div>

<!-- Affichage des articles par rubrique -->
<?php foreach ($rubriques as $rubrique): ?>
    <section class="rubrique-section" id="<?= htmlspecialchars(str_replace(' ', '_', $rubrique)) ?>">
        <h2><?= htmlspecialchars($rubrique) ?></h2>
        
        <?php
        // Récupérer les articles de cette rubrique
        $stmt = $pdo->prepare("
            SELECT a.id, a.title, a.rubrique, a.slug, a.created_at, a.image, u.username
            FROM articles a 
            LEFT JOIN users u ON u.id = a.author_id 
            WHERE a.rubrique = :rubrique
            ORDER BY a.created_at DESC
        ");
        $stmt->execute(['rubrique' => $rubrique]);
        $articles = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        if (count($articles) > 0):
        ?>
        
            <div class="article-grid">
                <?php foreach ($articles as $article): 
                    // Vérifier si l'article est en favoris
                    $is_favorite = false;
                    if (!empty($_SESSION['user_id'])) {
                        require_once 'includes/favorites-handler.php';
                        $is_favorite = isFavorite($pdo, $_SESSION['user_id'], $article['id']);
                    }
                ?>

                    <article class="article-card">
                        <?php if (!empty($article['image']) && file_exists($article['image'])): ?>
                            <a href="article.php?slug=<?= urlencode($article['slug']) ?>">
                                <img src="<?= htmlspecialchars($article['image']) ?>" 
                                     alt="<?= htmlspecialchars($article['title']) ?>"
                                     class="article-image">
                            </a>
                        <?php endif; ?>
                        
                        <div class="article-body">
                            <span class="article-rubrique"><?= htmlspecialchars($article['rubrique']) ?></span>
                            
                            <?php if (!empty($_SESSION['user_id'])): ?>
                                <form action="" method="post" class="favorite-form">
                                    <input type="hidden" name="product_id" value="<?= $article['id'] ?>">
                                    <button type="submit" name="toggle_favorite" class="favorite-btn">
                                        <?= $is_favorite ? '❤️' : '🤍' ?>
                                    </button>
                                </form>
                            <?php endif; ?>
                            
                            <h3 class="article-title">
                                <a href="article.php?slug=<?= urlencode($article['slug']) ?>">
                                    <?= htmlspecialchars($article['title']) ?>
                                </a>
                            </h3>
                            
                            <p class="article-meta">
                                Par <?= htmlspecialchars($article['username'] ?? 'Anonyme') ?> 
                                — <?= date('d/m/Y', strtotime($article['created_at'])) ?>
                            </p>
                            
                            <a href="article.php?slug=<?= urlencode($article['slug']) ?>" class="read-more">
                                Lire l'article →
                            </a>
                        </div>
                    </article>
                <?php endforeach; ?>
            </div>
        <?php else: ?>
            <p class="no-articles">Aucun article dans cette rubrique pour le moment.</p>
        <?php endif; ?>
    </section>
<?php endforeach; ?>

<?php if (count($rubriques) === 0): ?>
    <section class="rubrique-section">
        <p class="no-articles">Aucune rubrique disponible pour le moment.</p>
    </section>
<?php endif; ?>

<?php include 'footer.php'; ?>