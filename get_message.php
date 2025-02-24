<?php
header("Content-Type: application/json");
require_once 'db_connection.php'; 


$conn = new mysqli($host, $user, $pass, $dbname);

if ($conn->connect_error) {
    echo json_encode(["success" => false, "message" => "Database connection failed."]);
    exit();
}

$sql = "SELECT message FROM enquiries ORDER BY id DESC LIMIT 1";
$result = $conn->query($sql);

if ($result->num_rows > 0) {
    $row = $result->fetch_assoc();
    echo json_encode(["success" => true, "message" => $row["message"]]);
} else {
    echo json_encode(["success" => false, "message" => "No messages found."]);
}

$conn->close();
?>
