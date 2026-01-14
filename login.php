<?php
require 'db.php';

$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';

    if (!$email || !$password) {
        $errors[] = 'Veuillez remplir tous les champs.';
    } else {
        $res = q("SELECT * FROM users WHERE email = ? LIMIT 1", 's', [$email]);
        $user = $res ? $res->fetch_assoc() : null;

        if ($user && password_verify($password, $user['password'])) {
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['username'] = $user['username'];
            $_SESSION['status'] = 'logged';
            header('Location: dashboard.php');
            exit;
        } else {
            $errors[] = 'Adresse email ou mot de passe incorrect.';
            $_SESSION['status'] = 'not_logged';
        }
    }
}

include 'header.php';
?>

<section class="hero-banner" style="padding: 3rem 0;">
    <div class="hero-content">
        <h1 class="hero-title" style="font-size: 2.5rem;">Connexion</h1>
        <p class="hero-subtitle">Accédez à votre espace personnel</p>
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

    <form method="post" action="">
        <div class="form-group">
            <label for="email">Adresse email</label>
            <input type="email" id="email" name="email" required 
                   value="<?= htmlspecialchars($_POST['email'] ?? '') ?>"
                   placeholder="votre@email.com">
        </div>

        <div class="form-group">
            <label for="password">Mot de passe</label>
            <input type="password" id="password" name="password" required
                   placeholder="Votre mot de passe">
        </div>

        <button type="submit">Se connecter</button>
    </form>
    
    <p style="margin-top: 1.5rem; text-align: center;">
        Vous n'avez pas de compte ? 
        <a href="register.php" style="font-weight: 600;">S'inscrire</a>
    </p>
</section>

<?php include 'footer.php'; ?>