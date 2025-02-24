<?php

require_once 'db_connection.php'; 
error_reporting(0);

$msg = "";

if (isset($_POST['upload'])) {
    $img = $_FILES["uploadfile"]["name"];
    $tempname = $_FILES["uploadfile"]["tmp_name"];
    $folder = "./image/" . $img;
    
    $imageData = addslashes(file_get_contents($tempname));

    $sql = "INSERT INTO images (img, img_path) VALUES ('$imageData', '$folder')";
    
    if (mysqli_query($db, $sql) && move_uploaded_file($tempname, $folder)) {
        echo "<h3>&nbsp; Image uploaded successfully!</h3>";
    } else {
        echo "<h3>&nbsp; Failed to upload image!</h3>";
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Image Upload</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@4.6.1/dist/css/bootstrap.min.css">
    <link rel="stylesheet" type="text/css" href="style.css" />
</head>
<body>
    <div id="content">
        <form method="POST" action="imgtest.php" enctype="multipart/form-data">
            <div class="form-group">
                <input class="form-control" type="file" name="uploadfile" required />
            </div>
            <div class="form-group">
                <button class="btn btn-primary" type="submit" name="upload">UPLOAD</button>
            </div>
        </form>
    </div>

</body>
</html>

<?php
error_reporting(0);

$db = mysqli_connect("localhost", "root", "", "businessbox_local");

if (!$db) {
    die("Connection failed: " . mysqli_connect_error());
}
?>