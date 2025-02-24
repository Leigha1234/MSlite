<?php
error_reporting(0);

$msg = "";

if (isset($_POST['upload'])) {

    $img_path = $_FILES["uploadfile"]["name"];
    $tempname = $_FILES["uploadfile"]["tmp_name"];
    $folder = "./image/" . $img_path;

    $db = mysqli_connect("localhost", "root", "", "businessbox_local");

    $sql = "INSERT INTO image (img_path) VALUES ('$img_path')";

    mysqli_query($db, $sql);

    if (move_uploaded_file($tempname, $folder)) {
        echo "<h3>&nbsp; Image uploaded successfully!</h3>";
    } else {
        echo "<h3>&nbsp; Failed to upload image!</h3>";
    }
}
?>