<?php
session_start();
require 'db.php';
include('function.php');

// Récupération des rubriques
$rubriques_query = $pdo->query("
    SELECT DISTINCT rubrique 
    FROM articles 
    WHERE rubrique IS NOT NULL AND rubrique != '' 
    ORDER BY rubrique ASC
");
$rubriques = $rubriques_query->fetchAll(PDO::FETCH_COLUMN);

// ID utilisateur
$id_user = $_SESSION['user_id'];

// --- Gestion favoris ---
if (isset($_POST['toggle_favorite']) && isset($_POST['product_id'])) {
    $id_product = (int)$_POST['product_id'];

    try {
        $stmt = $pdo->prepare("SELECT id_favorite FROM favorites WHERE id_user = :id_user AND id_product = :id_product");
        $stmt->execute(['id_user' => $id_user, 'id_product' => $id_product]);
        $exists = $stmt->fetch();

        if ($exists) {
            $stmt = $pdo->prepare("DELETE FROM favorites WHERE id_user = :id_user AND id_product = :id_product");
        } else {
            $stmt = $pdo->prepare("INSERT INTO favorites (id_user, id_product) VALUES (:id_user, :id_product)");
        }

        $stmt->execute(['id_user' => $id_user, 'id_product' => $id_product]);

    } catch (PDOException $e) {
        echo "Erreur : " . $e->getMessage();
    }
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Rubriques</title>
</head>
<body>

<?php include 'header.php'; ?>

<?php foreach ($rubriques as $rubrique): ?>
<section id="<?php echo htmlspecialchars(str_replace(' ', '_', $rubrique)); ?>">

    <h2><?php echo htmlspecialchars($rubrique); ?></h2>

    <?php
    // Articles de la rubrique
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
        <?php foreach ($articles as $article): ?>

            <?php
            // Vérifier si l’article est en favoris
            $stmt = $pdo->prepare("SELECT id_favorite FROM favorites WHERE id_user = :id_user AND id_product = :id_product");
            $stmt->execute(['id_user' => $id_user, 'id_product' => $article['id']]);
            $is_favorite = $stmt->fetch() ? true : false;
            ?>

            <article class="article-card">

                <?php if (!empty($article['image']) && file_exists($article['image'])): ?>
                    <a href="article.php?slug=<?php echo urlencode($article['slug']); ?>">
                        <img src="<?php echo htmlspecialchars($article['image']); ?>" alt="">
                    </a>
                <?php endif; ?>

                <div class="article-card-content">

                    <span><?php echo htmlspecialchars($article['rubrique']); ?></span>

                    <!-- BOUTON FAVORIS CORRIGÉ -->
                    <form action="" method="post">
                        <input type="hidden" name="product_id" value="<?php echo $article['id']; ?>">
                        <button type="submit" name="toggle_favorite">
                            <?php echo $is_favorite ? '❤️' : '🤍'; ?>
                        </button>
                    </form>

                    <h3>
                        <a href="article.php?slug=<?php echo urlencode($article['slug']); ?>">
                            <?php echo htmlspecialchars($article['title']); ?>
                        </a>
                    </h3>

                    <p>
                        Par <?php echo htmlspecialchars($article['username'] ?? 'Anonyme'); ?>
                        — <?php echo date('d/m/Y', strtotime($article['created_at'])); ?>
                    </p>

                    <a href="article.php?slug=<?php echo urlencode($article['slug']); ?>">
                        Lire l'article →
                    </a>

                </div>
            </article>

        <?php endforeach; ?>
        </div>

    <?php else: ?>
        <p>Aucun article pour cette rubrique.</p>
    <?php endif; ?>

</section>
<?php endforeach; ?>

<?php include 'footer.php'; ?>

</body>
</html>
