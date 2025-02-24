<?php
require_once 'db_connection.php';

header('Content-Type: application/json');

$data = json_decode(file_get_contents('php://input'), true);
$loctitle = $data['loctitle'] ?? '';

if (!$loctitle) {
    echo json_encode(['success' => false, 'error' => 'Invalid loctitle']);
    exit;
}

$sql = "UPDATE enquiries SET status = 'read' WHERE loctitle = ?";
$stmt = $conn->prepare($sql);

if ($stmt) {
    $stmt->bind_param('s', $loctitle);
    $stmt->execute();
    if ($stmt->affected_rows > 0) {
        echo json_encode(['success' => true]);
    } else {
        echo json_encode(['success' => false, 'error' => 'No rows updated']);
    }
    $stmt->close();
} else {
    echo json_encode(['success' => false, 'error' => $conn->error]);
}

$conn->close();
?>
