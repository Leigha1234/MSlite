<?php
include 'db_connection.php'; 

$email = mysqli_real_escape_string($conn, trim($_POST['email']));  
$subject = mysqli_real_escape_string($conn, $_POST['subject']); 
$message = mysqli_real_escape_string($conn, $_POST['message']);  
$loctitle = mysqli_real_escape_string($conn, $_POST['loctitle']);  
$loc_id = mysqli_real_escape_string($conn, $_POST['loc_id']);  

$query = "SELECT searchers_id, email FROM searchers WHERE LOWER(email) = LOWER('$email') LIMIT 1";

echo "Query: " . $query;

$result = mysqli_query($conn, $query);

if (!$result) {
    echo "Query Error: " . mysqli_error($conn);
    exit;
}

if ($result && mysqli_num_rows($result) > 0) {
    $row = mysqli_fetch_assoc($result);
    $searchers_id = $row['id'];  
    $email = $row['email']; 
    
    $status = 'unread';
    
    $sql = "INSERT INTO enquiries (searchers_id, loctitle, status, created_at, message, email, subject, loc_id) 
            VALUES ('$searchers_id', '$loctitle', '$status', NOW(), '$message', '$email', '$subject', '$loc_id')";
    
    echo "Insert Query: " . $sql;

    if (mysqli_query($conn, $sql)) {
        echo "Enquiry submitted successfully!";
        header("Location: thank_you.php");
        exit;
    } else {
        echo "Error: " . mysqli_error($conn);
    }
    
} else {
    echo "No user found with the provided email address. Please check your email and try again.";
    exit;
}

?>
