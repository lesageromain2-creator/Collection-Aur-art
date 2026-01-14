<?php
require 'db.php';

// Vérifier que l'utilisateur est connecté
if (empty($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

$user_id = $_SESSION['user_id'];
$article_id = $_GET['id'] ?? null;
$confirm = $_GET['confirm'] ?? null;

if (!$article_id) {
    header('Location: dashboard.php?error=missing_id');
    exit;
}

// Si pas de confirmation, afficher page de confirmation
if ($confirm !== '1') {
    include 'header.php';
    
    // Récupérer l'article pour afficher son titre
    $res = q("SELECT title FROM articles WHERE id = ? AND author_id = ?", 'ii', [$article_id, $user_id]);
    $article = $res ? $res->fetch_assoc() : null;
    
    if (!$article) {
        echo '<div class="flash-message flash-error">Article introuvable ou vous n\'avez pas la permission.</div>';
        include 'footer.php';
        exit;
    }
    ?>
    
    <section class="card" style="max-width: 600px; margin: 3rem auto; text-align: center;">
        <h2>⚠️ Confirmer la suppression</h2>
        <p>Êtes-vous sûr de vouloir supprimer l'article suivant ?</p>
        <p style="font-weight: 600; font-size: 1.2rem; color: var(--violet-profond); margin: 1.5rem 0;">
            "<?= htmlspecialchars($article['title']) ?>"
        </p>
        <p style="color: var(--framboise); font-weight: 500;">
            ⚠️ Cette action est irréversible. L'article et ses commentaires seront définitivement supprimés.
        </p>
        
        <div style="display: flex; gap: 1rem; justify-content: center; margin-top: 2rem;">
            <a href="delete_article.php?id=<?= $article_id ?>&confirm=1" 
               style="padding: 0.8rem 2rem; background-color: var(--framboise); color: white; border-radius: 6px; text-decoration: none; font-weight: 600;">
                🗑 Oui, supprimer définitivement
            </a>
            <a href="dashboard.php" 
               style="padding: 0.8rem 2rem; background-color: var(--gris-clair); color: var(--anthracite); border-radius: 6px; text-decoration: none; font-weight: 600;">
                ← Annuler
            </a>
        </div>
    </section>
    
    <?php
    include 'footer.php';
    exit;
}

// Confirmation reçue : procéder à la suppression
$res = q("SELECT image FROM articles WHERE id = ? AND author_id = ?", 'ii', [$article_id, $user_id]);
$article = $res ? $res->fetch_assoc() : null;

if ($article) {
    // Supprimer l'image si elle existe
    if (!empty($article['image']) && file_exists($article['image'])) {
        unlink($article['image']);
    }
    
    // Supprimer l'article (les commentaires seront supprimés automatiquement via CASCADE)
    q("DELETE FROM articles WHERE id = ? AND author_id = ?", 'ii', [$article_id, $user_id]);
    
    header('Location: dashboard.php?success=deleted');
} else {
    header('Location: dashboard.php?error=not_found');
}

exit;