<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <link rel="stylesheet" href="style.css">
  <title>Recherche de produit</title>
</head>
<body>

<?php
include("function.php");
include("db.php");
include_once("header.php");

$found = false;

if (isset($_POST['recherche'])):
    $data_search_product = htmlspecialchars($_POST['recherche']);
    $total_articles = count_products($pdo);
?>
    <div class="catalogue_produits">
<?php
    for ($index = 1; $index <= $total_articles; $index++):
        $article[$index] = return_info_product($index);
        
        // Vérifier que l'article est valide et possède un titre
        if (!is_array($article[$index]) || !isset($article[$index]['title'])) {
            continue; // Passer à l'itération suivante si l'article n'est pas valide
        }

        if (strcasecmp($data_search_product, $article[$index]['title']) === 0):
            $found = true;
            $_SESSION['VALIDATE_SEARCH'] = false;
            
            // Vérifier si le produit est favori
            $is_favorite = false;
            if (isset($_SESSION['user_id'])) {
                // Exemple : $is_favorite = check_favorite($pdo, $_SESSION['user_id'], $article[$index]['id']);
            }
?>

           <article class="article-card">

                <?php if (!empty($article[$index]['image']) && file_exists($article[$index]['image'])): ?>
                    <a href="article.php?slug=<?php echo urlencode($article[$index]['slug']); ?>">
                        <img src="<?php echo htmlspecialchars($article[$index]['image']); ?>" 
                             alt="<?php echo htmlspecialchars($article[$index]['title']); ?>">
                    </a>
                <?php endif; ?>

                <div class="article-card-content">

                    <span class="article-rubrique">
                        <?php echo htmlspecialchars($article[$index]['rubrique']); ?>
                    </span>

                    <span class="article-rubrique">
                        <form action="" method="post">
                            <input type="hidden" name="product_id" value="<?php echo $article[$index]['id']; ?>">
                            <button type="submit" name="toggle_favorite">
                                <?php echo $is_favorite ? '❤️' : '🤍'; ?>
                            </button>
                        </form>
                    </span>

                    <h3>
                        <a href="article.php?slug=<?php echo urlencode($article[$index]['slug']); ?>">
                            <?php echo htmlspecialchars($article[$index]['title']); ?>
                        </a>
                    </h3>

                    <p class="article-meta">
                        Par <?php echo htmlspecialchars($article[$index]['username'] ?? 'Anonyme'); ?> —
                        <?php echo date('d/m/Y', strtotime($article[$index]['created_at'])); ?>
                    </p>

                    <a href="article.php?slug=<?php echo urlencode($article[$index]['slug']); ?>" class="read-more">
                        Lire l'article →
                    </a>

                </div>

            </article>

            <?php
        endif;
    endfor;
?>
    </div>
<?php
    if (!$found) {
        $_SESSION['VALIDATE_SEARCH'] = true;
        $_SESSION['SEARCH'] = $data_search_product;
        include("search.php");
    }
endif;
?>

</body>
</html>