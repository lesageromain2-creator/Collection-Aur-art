<?php
require 'db.php';
include 'header.php';

$errors = [];
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';

    // Vérifications de base
    if ($username === '' || $email === '' || $password === '') {
        $errors[] = 'Tous les champs sont requis.';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = 'Adresse email invalide.';
    } elseif (strlen($password) < 6) {
        $errors[] = 'Le mot de passe doit contenir au moins 6 caractères.';
    } else {
        // Vérifie si l'utilisateur existe déjà
        $exists = q("SELECT id FROM users WHERE email = ? OR username = ? LIMIT 1", 'ss', [$email, $username]);
        if ($exists && $exists->num_rows > 0) {
            $errors[] = 'Email ou nom d\'utilisateur déjà utilisé.';
        } else {
            // Crée le compte
            $hash = password_hash($password, PASSWORD_DEFAULT);
            $res = q("INSERT INTO users (username, email, password) VALUES (?, ?, ?)", 'sss', [$username, $email, $hash]);

            if ($res !== false) {
                $success = "Bienvenue " . htmlspecialchars($username) . " ! Votre compte a été créé avec succès.";
            } else {
                $errors[] = 'Erreur lors de la création du compte.';
            }
        }
    }
}
?>

<section class="hero-banner" style="padding: 3rem 0;">
    <div class="hero-content">
        <h1 class="hero-title" style="font-size: 2.5rem;">Inscription</h1>
        <p class="hero-subtitle">Rejoignez notre communauté</p>
    </div>
</section>

<section class="card" style="max-width: 500px; margin: 3rem auto;">
    <?php if ($errors): ?>
        <div class="alert alert-error">
            <ul style="list-style: none; padding: 0; margin: 0;">
                <?php foreach ($errors as $err): ?>
                    <li><?= htmlspecialchars($err) ?></li>
                <?php endforeach; ?>
            </ul>
        </div>
    <?php endif; ?>

    <?php if ($success): ?>
        <div class="alert alert-success">
            <p style="margin: 0;"><?= $success ?></p>
            <p style="margin-top: 1rem; margin-bottom: 0;">
                <a href="login.php" style="font-weight: 600;">Se connecter →</a>
            </p>
        </div>
    <?php else: ?>
        <form method="post" action="">
            <div class="form-group">
                <label for="username">Nom d'utilisateur</label>
                <input type="text" id="username" name="username" required 
                       value="<?= htmlspecialchars($_POST['username'] ?? '') ?>"
                       placeholder="Votre nom d'utilisateur">
            </div>

            <div class="form-group">
                <label for="email">Adresse email</label>
                <input type="email" id="email" name="email" required 
                       value="<?= htmlspecialchars($_POST['email'] ?? '') ?>"
                       placeholder="votre@email.com">
            </div>

            <div class="form-group">
                <label for="password">Mot de passe</label>
                <input type="password" id="password" name="password" required
                       placeholder="Au moins 6 caractères">
            </div>

            <button type="submit">S'inscrire</button>
        </form>
    <?php endif; ?>

    <p style="margin-top: 1.5rem; text-align: center;">
        Vous avez déjà un compte ? 
        <a href="login.php" style="font-weight: 600;">Se connecter</a>
    </p>
</section>

<?php include 'footer.php'; ?>