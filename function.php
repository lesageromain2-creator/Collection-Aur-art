
<?php

         function return_product(int $id_recherche): string { 
         global $pdo;
         
         $stmt = $pdo->prepare("SELECT title FROM articles WHERE id = :id");
         $stmt->execute(['id' => $id_recherche]);
         $result = $stmt->fetch(PDO::FETCH_ASSOC);
         
         return $result ? $result['title'] : '';
     }
     

     function count_products($pdo): int {
         $stmt = $pdo->query("SELECT COUNT(*) as total FROM articles");
         $result = $stmt->fetch(PDO::FETCH_ASSOC);
         return (int)$result['total'];
     }

     
    function count_users($pdo): int {
         $stmt = $pdo->query("SELECT COUNT(*) as total FROM users");
         $result = $stmt->fetch(PDO::FETCH_ASSOC);
         return (int)$result['total'];
    }


     function return_info_product(int $id_recherche): array {
         global $pdo;
         
         $stmt = $pdo->prepare("SELECT * FROM articles WHERE id = :id");
         $stmt->execute(['id' => $id_recherche]);
         $info_article = $stmt->fetch(PDO::FETCH_ASSOC);
         
         return  $info_article ?: [];
     }
    
   function return_info_users(int $id_recherche): ?array {
    global $pdo;

    $stmt = $pdo->prepare("SELECT * FROM users WHERE id = :id");
    $stmt->execute(['id' => $id_recherche]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    // Si aucune ligne → return null
    if (!$user) {
        return null;
    }

    // Si un champ obligatoire est vide → on ignore cet utilisateur
    if (
        empty($user['username']) ||
        empty($user['email']) ||
        empty($user['password'])
    ) {
        return null;
    }

    return $user;
}

    
 ?>








<?php

     
    

     
    
  /* authentificatin function */ 

 
// Démarre la session si pas déjà démarrée
function start_session_safe() {
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }
}

// Vérifie si l'utilisateur est connecté
function is_logged_in() {
    start_session_safe();
    return isset($_SESSION['user_id']) && !empty($_SESSION['user_id']);
}

// Redirige vers login si non connecté
function require_login() {
    if (!is_logged_in()) {
        header("Location: login.php");
        exit();
    }
}

// Redirige vers home si déjà connecté
function redirect_if_logged_in() {
    if (is_logged_in()) {
        header("Location: home.php");
        exit();
    }
}

// Enregistre un nouvel utilisateur
function register_user($pdo, $full_name, $email, $password, $age) {
    // Vérifie si l'email existe déjà
    $stmt = $pdo->prepare("SELECT user_id FROM users WHERE email = :email");
    $stmt->execute(['email' => $email]);
    
    if ($stmt->fetch()) {
        return ['success' => false, 'message' => 'Cet email est déjà utilisé.'];
    }
    
    // Hash le mot de passe
    $hashed_password = password_hash($password, PASSWORD_DEFAULT);
    
    // Insère l'utilisateur
    try {
        $stmt = $pdo->prepare("INSERT INTO users (full_name, email, password_id, age) VALUES (:full_name, :email, :password_id, :age)");
        $stmt->execute([
            'full_name' => $full_name,
            'email' => $email,
            'password_id' => $hashed_password,
            'age' => $age
        ]);
        
        return ['success' => true, 'message' => 'Inscription réussie !'];
    } catch (PDOException $e) {
        return ['success' => false, 'message' => 'Erreur lors de l\'inscription : ' . $e->getMessage()];
    }
}

// Connecte un utilisateur
function login_user($pdo, $email, $password) {
    $stmt = $pdo->prepare("SELECT user_id, full_name, email, password_id FROM users WHERE email = :email");
    $stmt->execute(['email' => $email]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$user) {
        return ['success' => false, 'message' => 'Email ou mot de passe incorrect.'];
    }
    
    // Vérifie le mot de passe
    if (password_verify($password, $user['password_id'])) {
        // Démarre la session et stocke les infos
        start_session_safe();
        $_SESSION['user_id'] = $user['user_id'];
        $_SESSION['full_name'] = $user['full_name'];
        $_SESSION['email'] = $user['email'];
        
        return ['success' => true, 'message' => 'Connexion réussie !'];
    } else {
        return ['success' => false, 'message' => 'Email ou mot de passe incorrect.'];
    }
}

// Déconnecte l'utilisateur
function logout_user() {
    start_session_safe();
    session_unset();
    session_destroy();
}

// Nettoie les entrées utilisateur
function clean_input($data) {
    return htmlspecialchars(trim($data));
}

// Valide l'email
function validate_email($email) {
    return filter_var($email, FILTER_VALIDATE_EMAIL);
}

// Valide le mot de passe (minimum 6 caractères)
function validate_password($password) {
    return strlen($password) >= 6;
}

// Valide l'âge
function validate_age($age) {
    return is_numeric($age) && $age >= 13 && $age <= 120;
}







 ?>