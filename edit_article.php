<?php
require 'db.php';
include 'header.php';

// Vérifier que l'utilisateur est connecté
if (empty($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

$user_id = $_SESSION['user_id'];
$article_id = $_GET['id'] ?? null;
$success = '';
$error = '';

if (!$article_id) {
    echo "<div class='flash-message flash-error'>Article non trouvé.</div>";
    include 'footer.php';
    exit;
}

// Récupérer l'article
$res = q("SELECT * FROM articles WHERE id = ? AND author_id = ?", 'ii', [$article_id, $user_id]);
$article = $res ? $res->fetch_assoc() : null;

if (!$article) {
    echo "<div class='flash-message flash-error'>Article non trouvé ou vous n'avez pas la permission de le modifier.</div>";
    include 'footer.php';
    exit;
}

// Récupérer toutes les rubriques existantes
$rubriques_query = $pdo->query("SELECT DISTINCT rubrique FROM articles WHERE rubrique IS NOT NULL AND rubrique != '' ORDER BY rubrique ASC");
$rubriques = $rubriques_query->fetchAll(PDO::FETCH_COLUMN);

// Gestion du formulaire
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['title'], $_POST['content'], $_POST['rubrique'])) {
    $title = trim($_POST['title']);
    $content = trim($_POST['content']);
    $rubrique = trim($_POST['rubrique']);
    $slug = strtolower(preg_replace('/[^a-zA-Z0-9]+/', '-', $title));
    
    // Gestion de l'image
    $image_path = $article['image']; // conserver l'ancienne si aucune nouvelle
    if (!empty($_FILES['image']['tmp_name'])) {
        $upload_dir = 'uploads/';
        if (!is_dir($upload_dir)) mkdir($upload_dir, 0777, true);
        
        // Supprimer l'ancienne image si nouvelle uploadée
        if (!empty($article['image']) && file_exists($article['image'])) {
            unlink($article['image']);
        }
        
        $image_path = $upload_dir . uniqid() . '_' . basename($_FILES['image']['name']);
        move_uploaded_file($_FILES['image']['tmp_name'], $image_path);
    }

    q("UPDATE articles SET title = ?, slug = ?, content = ?, rubrique = ?, image = ? WHERE id = ? AND author_id = ?",
      'sssssii', [$title, $slug, $content, $rubrique, $image_path, $article_id, $user_id]);

    $success = "Article modifié avec succès !";
    
    // Recharger l'article mis à jour
    $res = q("SELECT * FROM articles WHERE id = ?", 'i', [$article_id]);
    $article = $res ? $res->fetch_assoc() : null;
}
?>

<?php if ($success): ?>
    <div class="flash-message flash-success"><?= htmlspecialchars($success) ?></div>
<?php endif; ?>

<?php if ($error): ?>
    <div class="flash-message flash-error"><?= htmlspecialchars($error) ?></div>
<?php endif; ?>

<section class="card">
    <h2>Modifier l'article</h2>
    
    <form method="post" action="" enctype="multipart/form-data">
        <div class="form-group">
            <label for="title">Titre de l'article *</label>
            <input type="text" id="title" name="title" 
                   value="<?= htmlspecialchars($article['title']) ?>" 
                   required>
        </div>
        
        <div class="form-group">
            <label for="rubrique">Rubrique *</label>
            <input type="text" id="rubrique" name="rubrique" 
                   value="<?= htmlspecialchars($article['rubrique']) ?>" 
                   list="rubriques-list" 
                   placeholder="Ex: Histoire des arts, Marché de l'art..." 
                   required>
            <datalist id="rubriques-list">
                <?php foreach ($rubriques as $rub): ?>
                    <option value="<?= htmlspecialchars($rub) ?>">
                <?php endforeach; ?>
            </datalist>
            <small>Vous pouvez choisir une rubrique existante ou en créer une nouvelle.</small>
        </div>
        
        <div class="form-group">
            <label for="content">Contenu de l'article *</label>
            <textarea id="content" name="content" rows="12" required><?= htmlspecialchars($article['content']) ?></textarea>
        </div>
        
        <div class="form-group">
            <label for="image">Image de couverture</label>
            <?php if (!empty($article['image']) && file_exists($article['image'])): ?>
                <div class="image-preview">
                    <p><strong>Image actuelle :</strong></p>
                    <img src="<?= htmlspecialchars($article['image']) ?>" alt="Image actuelle">
                </div>
            <?php endif; ?>
            <input type="file" id="image" name="image" accept="image/*">
            <small>Laissez vide pour conserver l'image actuelle. Formats acceptés : JPG, PNG, WebP.</small>
        </div>
        
        <div class="form-actions">
            <button type="submit">💾 Mettre à jour l'article</button>
            <a href="dashboard.php" class="btn-secondary">Annuler</a>
        </div>
    </form>
</section>

<?php include 'footer.php'; ?>