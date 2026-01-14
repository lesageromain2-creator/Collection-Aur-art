<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <link rel="stylesheet" href="product.css">
  <title>Recherche de produit</title>
</head>
<body>

<?php
include_once("db.php");
include_once("header.php");
include_once("function.php");

$found = false;
$data_search_product = htmlspecialchars($_POST['rubrique']);
$total_products = count_products($pdo);

if (!isset($_POST['bar_de_recherche'])):
?>
    <div class="catalogue_produits">
<?php
    for ($index = 1; $index <= $total_products; $index++):
        $all_product[$index] = return_info_product($index);

        if ($data_search_product === $all_product[$index]['rubrique']): 
            $found = true;
            $_SESSION['VALIDATE_SEARCH'] = false;
?>
            <div class="articles">
                <div class="container_image"> 
                    <div class="product_image"> 
                        <img src="<?php echo htmlspecialchars($all_product[$index]['image_product']); ?>"  
                             alt="<?php echo htmlspecialchars($all_product[$index]['product_name']); ?>"> 
                    </div>
                </div>

                <div class="container_info"> 
                    <div class="info"> <?php echo htmlspecialchars($all_product[$index]['product_name']); ?></div>
                    <div class="info">Nom spécifique: <?php echo htmlspecialchars($all_product[$index]['product_specific_name']); ?></div>
                    <div class="info">Prix: <?php echo htmlspecialchars($all_product[$index]['product_price']); ?>€</div>
                    <div class="info">Quantité: <?php echo htmlspecialchars($all_product[$index]['product_quantity']); ?></div>
                    <div class="info">Couleur: <?php echo htmlspecialchars($all_product[$index]['product_color']); ?></div>
                    <div class="info">Origine: <?php echo htmlspecialchars($all_product[$index]['product_origin']); ?></div>
                </div>
            </div>
<?php 
        endif;
    endfor;
?>
    </div>
<?php
    if (!$found):
        $_SESSION['VALIDATE_SEARCH'] = true;
        $_SESSION['SEARCH'] = $data_search_product;
        include("search.php");
    endif;
endif;
?>

</body>
</html>