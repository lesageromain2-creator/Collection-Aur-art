 <link rel="stylesheet" href="style.css">

  <?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
} 


include_once("db.php");
include_once('function.php');

if (!isset($_SESSION['user_id']) || empty($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$id_user = $_SESSION['user_id'];


// ---- Gestion ajout / suppression favoris ----
if (isset($_POST['toggle_favorite']) && isset($_POST['product_id'])) {
    $id_product = (int)$_POST['product_id'];

    // Vérifie si déjà en favori
    $stmt = $pdo->prepare("SELECT id_favorite FROM favorites WHERE id_user = :id_user AND id_product = :id_product");
    $stmt->execute(['id_user' => $id_user, 'id_product' => $id_product]);
    $exists = $stmt->fetch();

    if ($exists) {
        $stmt = $pdo->prepare("DELETE FROM favorites WHERE id_user = :id_user AND id_product = :id_product");
    } else {
        $stmt = $pdo->prepare("INSERT INTO favorites (id_user, id_product) VALUES (:id_user, :id_product)");
    }

    $stmt->execute(['id_user' => $id_user, 'id_product' => $id_product]);

    header("Location: favorite.php");
    exit();
}
include_once("header.php");

// ---- Récupération des articles favoris ----
$stmt = $pdo->prepare("
    SELECT a.* , u.username
    FROM articles a
    INNER JOIN favorites f ON a.id = f.id_product
    LEFT JOIN users u ON u.id = a.author_id
    WHERE f.id_user = :id_user
    ORDER BY f.date_added DESC
");
$stmt->execute(['id_user' => $id_user]);
$favorites = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<div class="titre_page">Mes Favoris</div>

<div class="article-list" style="display:flex; flex-wrap:wrap; gap:20px;">

<?php if (empty($favorites)): ?>

    <p>Vous n'avez pas encore de favoris.</p>

<?php else: ?>

    <?php foreach ($favorites as $row): ?>

        <article class="card" style="width:300px;">

            <?php if (!empty($row['image'])): ?>
                <a href="article.php?slug=<?= urlencode($row['slug']) ?>">
                    <img src="<?= htmlspecialchars($row['image']) ?>"
                         alt="<?= htmlspecialchars($row['title']) ?>"
                         style="width:100%; height:200px; object-fit:cover;">
                </a>
            <?php endif; ?>

            <!-- BOUTON FAVORI -->
            <form action="" method="post" style="display:inline;">
                <input type="hidden" name="product_id" value="<?= $row['id'] ?>">
                <button type="submit" name="toggle_favorite"
                        class="favorite-btn"
                        style="background:none; border:none; font-size:25px; cursor:pointer;">
                    ❤️ <!-- On n'affiche que les favoris ici -->
                </button>
            </form>

            <p><?= htmlspecialchars($row['rubrique']) ?></p>
            <h3><?= htmlspecialchars($row['title']) ?></h3>
            <p>
                Par <?= htmlspecialchars($row['username'] ?? 'Anonyme') ?>
                — <?= htmlspecialchars(date('d/m/Y', strtotime($row['created_at']))) ?>
            </p>
            <p><a href="article.php?slug=<?= urlencode($row['slug']) ?>">Lire l'article →</a></p>

        </article>

    <?php endforeach; ?>

<?php endif; ?>

</div>

<?php include 'footer.php'; ?>
