<?php 
require_once 'db_connection.php';

if (!isset($pdo)) { 
    die(); 
}

try {
    $query = "SELECT img_path FROM images";
    
    $stmt = $pdo->prepare($query);
    $stmt->execute();
    
    $images = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    die();
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Display Images</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@4.6.1/dist/css/bootstrap.min.css">
    <link rel="stylesheet" type="text/css" href="style.css" />
</head>
<body>
    <div id="display-image">
        <?php
        if (!empty($images)) {
            foreach ($images as $image) {
                echo '<img src="' . htmlspecialchars($image['img_path']) . '" class="img-thumbnail">';
            }
        }
        ?>
    </div>
</body>
</html>
