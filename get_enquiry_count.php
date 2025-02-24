<?php
require_once 'db_connection.php'; 

header('Content-Type: application/json');

$sql = "SELECT COUNT(*) AS enquiry_count FROM enquiries WHERE status = 'unread'";
$result = $conn->query($sql);

if ($result) {
    $row = $result->fetch_assoc();
    $enquiry_count = $row['enquiry_count'];
    echo json_encode(['success' => true, 'enquiry_count' => $enquiry_count]);
} else {
    echo json_encode(['success' => false, 'error' => $conn->error]);
}

$conn->close();
?>
