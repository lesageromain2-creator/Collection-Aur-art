<?php

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
           
            $stmt = $pdo->prepare("
                DELETE FROM favorites 
                WHERE id_user = :id_user AND id_product = :id_product
            ");
        } else {
           
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
