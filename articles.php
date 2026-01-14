
<?php


if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require 'db.php';
include('function.php');

?>



<?php


if (!empty($_SESSION['user_id'])){ 
$id_user = $_SESSION['user_id']; // ID de l'utilisateur connecté

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

        header("Location: " . $_SERVER['PHP_SELF']);
        exit();
    } catch (PDOException $e) {
        echo "Erreur : " . $e->getMessage();
    }
}
}
include ('header.php');

?>

<section class="card_background">
    <h2>Catalogue des articles</h2>

    <?php
// --- Récupération articles ---
$res = q("SELECT a.id, a.title, a.rubrique, a.slug, a.created_at, a.image, u.username 
          FROM articles a 
          LEFT JOIN users u ON u.id = a.author_id 
          ORDER BY a.created_at DESC");



if ($res && $res->num_rows) {
    echo '<div class="article-list" style="display:flex; flex-wrap:wrap; gap:20px;">';

    while ($row = $res->fetch_assoc()):
    if (!empty($_SESSION['user_id'])){ 
          
        // Vérifie si l'article est en favoris
        $stmt = $pdo->prepare("SELECT id_favorite FROM favorites 
                               WHERE id_user = :id_user AND id_product = :id_product");
        $stmt->execute(['id_user' => $id_user, 'id_product' => $row['id']]);
        $is_favorite = $stmt->fetch() ? true : false;
    }
        ?>

        <article class="card" style="width:300px;">

            <?php
            if (!empty($row['image'])) {
                echo '<a href="article.php?slug=' . urlencode($row['slug']) . '">';
                echo '<img src="' . htmlspecialchars($row['image']) . '" alt="' . htmlspecialchars($row['title']) . '" style="width:100%; height:200px; object-fit:cover;">';
                echo '</a>';
            }
            ?>

            <!-- BOUTON FAVORI -->
             <?php if (!empty($_SESSION['user_id'])): ?> 
            <!-- BOUTON FAVORI -->
            <form action="" method="post" style="display:inline;">
                <input type="hidden" name="product_id" value="<?php echo $row['id']; ?>">
                <button type="submit" name="toggle_favorite" class="favorite-btn" style="background:none; border:none; font-size:25px; cursor:pointer;">
                    <?php echo $is_favorite ? '❤️' : '🤍'; ?>
                </button>
            </form>
          <?php endif; ?>  

            <?php
            echo '<p>' . htmlspecialchars($row['rubrique']) . '</p>';
            echo '<h3>' . htmlspecialchars($row['title']) . '</h3>';
            echo '<p>Par ' . htmlspecialchars($row['username'] ?? 'Anonyme') . ' — ' . 
                 htmlspecialchars(date('d/m/Y', strtotime($row['created_at']))) . '</p>';
            echo '<p><a href="article.php?slug=' . urlencode($row['slug']) . '">Lire l\'article →</a></p>';
            ?>

        </article>

    <?php endwhile;

    echo '</div>';

} else {
    echo '<p>Aucun article pour l\'instant.</p>';
}
?>

</section>

<?php include 'footer.php'; ?>