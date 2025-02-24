<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

$conn = new mysqli("localhost", "root", "", "businessbox_local");

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

if (isset($_POST['upload'])) {
    $img = $_FILES["uploadfile"]["name"];
    $tempname = $_FILES["uploadfile"]["tmp_name"];
    $folder = "./image/" . $img;
    
    $imageData = addslashes(file_get_contents($tempname));
    
    $sql = "INSERT INTO images (img, img_path) VALUES ('$imageData', '$folder')";
    
    if (mysqli_query($conn, $sql) && move_uploaded_file($tempname, $folder)) {
        echo "<h3>&nbsp; Image uploaded successfully!</h3>";
    } else {
        echo "<h3>&nbsp; Failed to upload image!</h3>";
    }
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $loctitle = $conn->real_escape_string($_POST['loctitle']);
    $virtualurl = $conn->real_escape_string($_POST['virtualurl']);
    $intfeatures = $conn->real_escape_string($_POST['intfeatures']);
    $exfeatures = $conn->real_escape_string($_POST['exfeatures']);
    $gsarea = $conn->real_escape_string($_POST['gsarea']);
    $keywords = $conn->real_escape_string($_POST['keywords']);
    $category = $conn->real_escape_string($_POST['category']);
    $email = $conn->real_escape_string($_POST['email']);
    $description = $conn->real_escape_string($_POST['description']);
    $settings = $conn->real_escape_string($_POST['settings']);
    $parking = $conn->real_escape_string($_POST['parking']);
    $sustainable = $conn->real_escape_string($_POST['sustainable']);
    $info = $conn->real_escape_string($_POST['info']);

    if (isset($_FILES['img']) && $_FILES['img']['error'] == 0) {
        $img_name = $_FILES['img']['name'];
        $img_tmp_name = $_FILES['img']['tmp_name'];
        $img_size = $_FILES['img']['size'];
        $img_type = $_FILES['img']['type'];

        $upload_dir = "uploads/"; 
        $img_path = $upload_dir . basename($img_name);

        if (move_uploaded_file($img_tmp_name, $img_path)) {
            $img = $conn->real_escape_string($img_path);
        } else {
            echo "Error uploading the image.<br>";
            exit;
        }
    } else {
        echo "No image uploaded or there was an error uploading the image.<br>";
        exit;
    }

    $sql_check_loctitle = "SELECT * FROM locdata WHERE loctitle = '$loctitle'";
    $result = $conn->query($sql_check_loctitle);

    if ($result->num_rows > 0) {
        echo "Error: The location title already exists. Please choose a different title.<br>";
    } else {
        $sql_check_email = "SELECT user_id, email FROM users WHERE email = '$email'";
        $result = $conn->query($sql_check_email);

        if ($result->num_rows > 0) {
            $row = $result->fetch_assoc();
            $user_id = $row['user_id'];
            $user_email = $row['email'];

            $sql_insert_image = "INSERT INTO images (img) VALUES ('$img')";
            if ($conn->query($sql_insert_image) === TRUE) {
                $img_id = $conn->insert_id; 

                $sql_locdata = "INSERT INTO locdata (loctitle, virtualurl, intfeatures, exfeatures, gsarea, keywords, category, user_id, user, parking, settings, description, sustainable, info, img, img_id) 
                                VALUES ('$loctitle', '$virtualurl', '$intfeatures', '$exfeatures', '$gsarea', '$keywords', '$category', '$user_id', '$user_email', '$parking', '$settings', '$description', '$sustainable', '$info', '$img', '$img_id')";

                if ($conn->query($sql_locdata) === TRUE) {
                    echo "Location data inserted successfully.<br>";

                    $sql_get_contact = "SELECT loctitle FROM contacts WHERE user_id = '$user_id'";
                    $result = $conn->query($sql_get_contact);

                    if ($result->num_rows > 0) {
                        $row = $result->fetch_assoc();
                        $existing_loctitle = $row['loctitle'];
                        $updated_loctitle = $existing_loctitle ? $existing_loctitle . ", " . $loctitle : $loctitle;

                        $sql_update_contact = "UPDATE contacts 
                                               SET loctitle = '$updated_loctitle', 
                                                   no_locs = (SELECT COUNT(*) FROM locdata WHERE user_id = '$user_id') 
                                               WHERE user_id = '$user_id'";

                        if ($conn->query($sql_update_contact) === TRUE) {
                            echo "Contact loctitle and no_locs updated successfully.<br>";
                        } else {
                            echo "Error updating contact: " . $conn->error . "<br>";
                        }
                    } else {
                        $sql_insert_contact = "INSERT INTO contacts (user_id, email, loctitle, no_locs, favs, created_at) 
                                               VALUES ('$user_id', '$email', '$loctitle', 1, '', NOW())";

                        if ($conn->query($sql_insert_contact) === TRUE) {
                            echo "New contact created with first location.<br>";
                        } else {
                            echo "Error inserting contact: " . $conn->error . "<br>";
                        }
                    }
                } else {
                    echo "Error inserting locdata: " . $conn->error . "<br>";
                }
            } else {
                echo "Error inserting image: " . $conn->error . "<br>";
            }
        } else {
            echo "Error: The email you entered does not match any registered user. Please try again.<br>";
        }
    }
}

$conn->close();
?>
