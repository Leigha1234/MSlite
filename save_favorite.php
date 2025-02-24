<?php
session_start();
require_once 'db_connection.php'; 

if (isset($_POST['loc_id']) && isset($_POST['action']) && isset($_SESSION['user_id'])) {
    $userId = $_SESSION['user_id'];
    $locId = $_POST['loc_id'];
    $action = $_POST['action'];

    $sql = "SELECT favs FROM users WHERE id = :userId";
    $stmt = $pdo->prepare($sql);
    $stmt->execute(['userId' => $userId]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    $favs = explode(',', $user['favs']);

    if ($action == 'add' && !in_array($locId, $favs)) {
        $favs[] = $locId; 
    } elseif ($action == 'remove' && in_array($locId, $favs)) {
        $favs = array_filter($favs, function($fav) use ($locId) {
            return $fav != $locId;
        });
        $favs = array_values($favs);  
    }

    $favsString = implode(',', $favs);
    $sql = "UPDATE users SET favs = :favs WHERE id = :userId";
    $stmt = $pdo->prepare($sql);
    $stmt->execute(['favs' => $favsString, 'userId' => $userId]);

    echo 'Success'; 
} else {
    echo 'Error: Invalid request';
}
?>
