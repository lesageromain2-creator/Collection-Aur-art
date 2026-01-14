 <?php
require 'db.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (empty($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

$user_id = $_SESSION['user_id'];
$success = '';
$error = '';

// Gestion de l'ajout d'un nouvel article
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['title'], $_POST['content'])) {
    $title = trim($_POST['title']);
    $rubrique = trim($_POST['rubrique']);
    $content = trim($_POST['content']);
    $slug = strtolower(preg_replace('/[^a-zA-Z0-9]+/', '-', $title));

    // Vérifier si le slug existe déjà
    $check = q("SELECT id FROM articles WHERE slug = ?", "s", [$slug])->fetch_assoc();
    if ($check) {
        $slug .= "-" . time();
    }

    // Gestion de l'image
    $image_path = null;
    if (!empty($_FILES['image']['tmp_name'])) {
        $upload_dir = 'uploads/';
        if (!is_dir($upload_dir)) mkdir($upload_dir, 0777, true);
        $image_path = $upload_dir . basename($_FILES['image']['name']);
        move_uploaded_file($_FILES['image']['tmp_name'], $image_path);
    }

    q("INSERT INTO articles (title, rubrique, slug, content, author_id, image, created_at) 
       VALUES (?, ?, ?, ?, ?, ?, NOW())", 
      'ssssis', [$title, $rubrique, $slug, $content, $user_id, $image_path]);

    header('Location: dashboard.php?success=1');
    exit;
}

// Gestion de la modification du profil
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_profile'])) {
    $username = trim($_POST['username']);
    $role = trim($_POST['role']);
    $description = trim($_POST['description']);
    
    $user_info = q("SELECT picture_profil FROM users WHERE id = ?", 'i', [$user_id]);
    $user = $user_info->fetch_assoc();
    $image_path = $user['picture_profil'];
    
    if (!empty($_FILES['picture_profil']['tmp_name'])) {
        $upload_dir = 'uploads/profiles/';
        if (!is_dir($upload_dir)) mkdir($upload_dir, 0777, true);
        
        $file_extension = pathinfo($_FILES['picture_profil']['name'], PATHINFO_EXTENSION);
        $new_filename = 'profile_' . $user_id . '_' . time() . '.' . $file_extension;
        $image_path = $upload_dir . $new_filename;
        
        if (move_uploaded_file($_FILES['picture_profil']['tmp_name'], $image_path)) {
            if (!empty($user['picture_profil']) && file_exists($user['picture_profil'])) {
                unlink($user['picture_profil']);
            }
        }
    }
    
    q("UPDATE users SET username = ?, role = ?, description = ?, picture_profil = ? WHERE id = ?", 
      'ssssi', [$username, $role, $description, $image_path, $user_id]);
    
    $success = 'Profil mis à jour avec succès !';
    $_SESSION['username'] = $username;
}

// Récupérer les infos utilisateur
$user_info = q("SELECT * FROM users WHERE id = ?", 'i', [$user_id]);
$user = $user_info ? $user_info->fetch_assoc() : null;

// Récupérer les articles de l'utilisateur
$articles = q("SELECT * FROM articles WHERE author_id = ? ORDER BY created_at DESC", 'i', [$user_id]);

include 'header.php';
?>

<style>
.dashboard-container {
    display: grid;
    grid-template-columns: 350px 1fr;
    gap: 2rem;
    margin: 2rem 0;
}

.profile-sidebar {
    display: flex;
    flex-direction: column;
    gap: 1.5rem;
}

.profile-card {
    background: white;
    padding: 2rem;
    border-radius: 8px;
    box-shadow: 0 1px 3px rgba(0,0,0,0.04);
    text-align: center;
}

.profile-picture {
    width: 120px;
    height: 120px;
    border-radius: 50%;
    object-fit: cover;
    margin: 0 auto 1rem;
    border: 3px solid #D4376C;
}

.profile-placeholder {
    width: 120px;
    height: 120px;
    border-radius: 50%;
    background: linear-gradient(135deg, #D4376C, #3A2151);
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 3rem;
    color: white;
    font-weight: bold;
    margin: 0 auto 1rem;
}

.profile-card h2 {
    font-size: 1.5rem;
    margin-bottom: 0.5rem;
    color: #3A2151;
}

.profile-role {
    color: #D4376C;
    font-weight: 600;
    margin-bottom: 1rem;
    font-size: 0.95rem;
}

.profile-description {
    color: #666;
    font-size: 0.9rem;
    line-height: 1.6;
    padding: 1rem;
    background: #FAF7F2;
    border-radius: 6px;
    margin-top: 1rem;
}

.profile-stats {
    display: flex;
    justify-content: space-around;
    padding: 1.5rem 0;
    border-top: 1px solid #eee;
    margin-top: 1rem;
}

.stat-number {
    font-size: 2rem;
    font-weight: bold;
    color: #D4376C;
    display: block;
}

.stat-label {
    font-size: 0.85rem;
    color: #999;
}

.main-content {
    display: flex;
    flex-direction: column;
    gap: 2rem;
}

.form-row {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 1.5rem;
}

@media (max-width: 1024px) {
    .dashboard-container {
        grid-template-columns: 1fr;
    }
    
    .form-row {
        grid-template-columns: 1fr;
    }
}

@media (max-width: 768px) {
    .profile-picture,
    .profile-placeholder {
        width: 100px;
        height: 100px;
        font-size: 2.5rem;
    }
}
</style>

<?php if (isset($_GET['success'])): ?>
<div class="alert alert-success">
    Article publié avec succès !
</div>
<?php endif; ?>

<?php if ($success): ?>
<div class="alert alert-success"><?= $success ?></div>
<?php endif; ?>

<?php if ($error): ?>
<div class="alert alert-error"><?= $error ?></div>
<?php endif; ?>

<section class="dashboard-container">
    <!-- SIDEBAR PROFIL -->
    <aside class="profile-sidebar">
        <div class="profile-card">
            <?php if (!empty($user['picture_profil']) && file_exists($user['picture_profil'])): ?>
                <img src="<?= htmlspecialchars($user['picture_profil']) ?>" 
                     alt="Photo de profil" class="profile-picture">
            <?php else: ?>
                <div class="profile-placeholder">
                    <?= strtoupper(substr($user['username'] ?? 'U', 0, 1)) ?>
                </div>
            <?php endif; ?>
            
            <h2><?= htmlspecialchars($user['username'] ?? 'Utilisateur') ?></h2>
            
            <?php if (!empty($user['role'])): ?>
                <div class="profile-role"><?= htmlspecialchars($user['role']) ?></div>
            <?php endif; ?>
            
            <?php if (!empty($user['description'])): ?>
                <div class="profile-description">
                    <?= htmlspecialchars(substr($user['description'], 0, 200)) ?>
                    <?= strlen($user['description']) > 200 ? '...' : '' ?>
                </div>
            <?php endif; ?>
            
            <div class="profile-stats">
                <div class="stat">
                    <span class="stat-number">
                        <?php
                        $count = q("SELECT COUNT(*) as total FROM articles WHERE author_id = ?", 'i', [$user_id]);
                        echo $count ? $count->fetch_assoc()['total'] : 0;
                        ?>
                    </span>
                    <span class="stat-label">Articles</span>
                </div>
                <div class="stat">
                    <span class="stat-number">
                        <?= date('Y') - (int)date('Y', strtotime($user['created_at'] ?? 'now')) ?>
                    </span>
                    <span class="stat-label">Années</span>
                </div>
            </div>
        </div>
    </aside>

    <!-- MAIN CONTENT -->
    <main class="main-content">
        <!-- SECTION PROFIL -->
        <section class="card">
            <h2>Modifier votre profil</h2>
            <form method="post" action="" enctype="multipart/form-data">
                <div class="form-row">
                    <div class="form-group">
                        <label for="username">Nom et prénom</label>
                        <input type="text" id="username" name="username" 
                               value="<?= htmlspecialchars($user['username'] ?? '') ?>" 
                               required maxlength="100">
                    </div>

                    <div class="form-group">
                        <label for="role">Fonction</label>
                        <input type="text" id="role" name="role" 
                               value="<?= htmlspecialchars($user['role'] ?? '') ?>" 
                               maxlength="100">
                    </div>
                </div>

                <div class="form-group">
                    <label for="description">Biographie</label>
                    <textarea id="description" name="description" rows="4"><?= htmlspecialchars($user['description'] ?? '') ?></textarea>
                </div>
                
                <div class="form-group">
                    <label for="picture_profil">Photo de profil</label>
                    <input type="file" id="picture_profil" name="picture_profil" accept="image/*">
                </div>
                
                <button type="submit" name="update_profile">Enregistrer</button>
            </form>
        </section>

        <!-- SECTION AJOUTER ARTICLE -->
        <section class="card">
            <h2>Nouvel article</h2>
            <form method="post" action="" enctype="multipart/form-data">
                <div class="form-group">
                    <label for="title">Titre</label>
                    <input type="text" id="title" name="title" required maxlength="255">
                </div>

                <div class="form-group">
                    <label for="rubrique">Rubrique</label>
                    <select name="rubrique" id="rubrique" required>
                        <option value="">-- Sélectionnez --</option>
                        <option value="Procès en art">Procès en art</option>
                        <option value="Rencontres">Rencontres</option>
                        <option value="Au fil des œuvres">Au fil des œuvres</option>
                        <option value="Histoire des arts">Histoire des arts</option>
                        <option value="Art contemporain">Art contemporain</option>
                        <option value="Évènement">Évènement</option>
                        <option value="Marché de l'art">Marché de l'art</option>
                    </select>
                </div>

                <div class="form-group">
                    <label for="content">Contenu</label>
                    <textarea id="content" name="content" rows="8" required></textarea>
                </div>
                
                <div class="form-group">
                    <label for="image">Image (facultatif)</label>
                    <input type="file" id="image" name="image" accept="image/*">
                </div>
                
                <button type="submit">Publier</button>
            </form>
        </section>

        <!-- SECTION MES ARTICLES -->
        <section class="card">
            <h2>Mes articles</h2>
            <?php if ($articles && $articles->num_rows): ?>
                <div class="article-list">
                    <?php while ($row = $articles->fetch_assoc()): ?>
                        <article class="article-card">
                            <?php if (!empty($row['image']) && file_exists($row['image'])): ?>
                                <img src="<?= htmlspecialchars($row['image']) ?>" 
                                     alt="<?= htmlspecialchars($row['title']) ?>"
                                     class="article-image">
                            <?php endif; ?>
                            <div class="article-body">
                                <span class="article-rubrique">
                                    <?= htmlspecialchars($row['rubrique'] ?? 'Sans rubrique') ?>
                                </span>
                                <h3 class="article-title">
                                    <a href="article.php?slug=<?= urlencode($row['slug']) ?>">
                                        <?= htmlspecialchars($row['title']) ?>
                                    </a>
                                </h3>
                                <p style="color: #999; font-size: 0.9rem;">
                                    <?= date('d/m/Y', strtotime($row['created_at'])) ?>
                                </p>
                                <div style="margin-top: 1rem;">
                                    <a href="article.php?slug=<?= urlencode($row['slug']) ?>">Voir</a>
                                    <a href="edit_article.php?id=<?= $row['id'] ?>" style="margin-left: 1rem;">Modifier</a>
                                    <a href="delete_article.php?id=<?= $row['id'] ?>" 
                                       onclick="return confirm('Confirmer la suppression ?')"
                                       style="margin-left: 1rem; color: #e74c3c;">
                                        Supprimer
                                    </a>
                                </div>
                            </div>
                        </article>
                    <?php endwhile; ?>
                </div>
            <?php else: ?>
                <p style="text-align: center; color: #999; padding: 2rem;">
                    Vous n'avez pas encore publié d'article.
                </p>
            <?php endif; ?>
        </section>
    </main>
</section>

<?php include 'footer.php'; ?>