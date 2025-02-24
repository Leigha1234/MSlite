<?php
require_once 'db_connection.php'; 

if (isset($_GET['loctitle'])) {
    $loctitle = $_GET['loctitle'];

    try {
        $sql = "DELETE FROM locdata WHERE loctitle = :loctitle";
        $stmt = $pdo->prepare($sql);
        $stmt->bindParam(':loctitle', $loctitle);
        $stmt->execute();

        echo json_encode(['message' => 'Location deleted successfully']);
    } catch (PDOException $e) {
        echo json_encode(['message' => 'Error deleting location: ' . $e->getMessage()]);
    }
} else {
    echo json_encode(['message' => 'No location title specified']);
}
?>
