<?php
/**
 * GESTION CENTRALISÉE DES FAVORIS
 * Fichier: includes/favorites-handler.php
 * 
 * Fonctions pour gérer l'ajout/suppression des favoris
 * et vérifier si un article est en favori.
 */

/**
 * Ajoute ou retire un article des favoris
 * @param PDO $pdo Connexion base de données
 * @param int $user_id ID de l'utilisateur
 * @param int $product_id ID de l'article
 * @return bool Succès de l'opération
 */
function toggleFavorite($pdo, $user_id, $product_id) {
    try {
        $stmt = $pdo->prepare("
            SELECT id_favorite 
            FROM favorites 
            WHERE id_user = :id_user AND id_product = :id_product
        ");
        $stmt->execute(['id_user' => $user_id, 'id_product' => $product_id]);
        $exists = $stmt->fetch();

        if ($exists) {
            // Retirer des favoris
            $stmt = $pdo->prepare("
                DELETE FROM favorites 
                WHERE id_user = :id_user AND id_product = :id_product
            ");
        } else {
            // Ajouter aux favoris
            $stmt = $pdo->prepare("
                INSERT INTO favorites (id_user, id_product, date_added) 
                VALUES (:id_user, :id_product, UNIX_TIMESTAMP())
            ");
        }

        return $stmt->execute([
            'id_user' => $user_id, 
            'id_product' => $product_id
        ]);
        
    } catch (PDOException $e) {
        error_log("Erreur favoris: " . $e->getMessage());
        return false;
    }
}

/**
 * Vérifie si un article est en favoris
 * @param PDO $pdo Connexion base de données
 * @param int $user_id ID de l'utilisateur
 * @param int $product_id ID de l'article
 * @return bool True si en favoris, false sinon
 */
function isFavorite($pdo, $user_id, $product_id) {
    try {
        $stmt = $pdo->prepare("
            SELECT COUNT(*) as count
            FROM favorites 
            WHERE id_user = :id_user AND id_product = :id_product
        ");
        $stmt->execute(['id_user' => $user_id, 'id_product' => $product_id]);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        
        return $result['count'] > 0;
        
    } catch (PDOException $e) {
        error_log("Erreur vérification favoris: " . $e->getMessage());
        return false;
    }
}

/**
 * Récupère tous les favoris d'un utilisateur
 * @param PDO $pdo Connexion base de données
 * @param int $user_id ID de l'utilisateur
 * @return array Liste des articles favoris
 */
function getUserFavorites($pdo, $user_id) {
    try {
        $stmt = $pdo->prepare("
            SELECT a.*, u.username, f.date_added
            FROM articles a
            INNER JOIN favorites f ON a.id = f.id_product
            LEFT JOIN users u ON u.id = a.author_id
            WHERE f.id_user = :id_user
            ORDER BY f.date_added DESC
        ");
        $stmt->execute(['id_user' => $user_id]);
        
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
        
    } catch (PDOException $e) {
        error_log("Erreur récupération favoris: " . $e->getMessage());
        return [];
    }
}