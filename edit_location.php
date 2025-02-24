<?php
require_once 'db_connection.php';
require_once 'settings_loader.php'; 

try {
    $pdo = new PDO("mysql:host=$servername;dbname=$dbname", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("Database connection failed: " . $e->getMessage());
}

if (isset($_GET['id'])) {
    $loc_id = intval($_GET['id']);

    $sql = "SELECT * FROM locdata WHERE loc_id = :loc_id";
    $stmt = $pdo->prepare($sql);
    $stmt->bindParam(':loc_id', $loc_id, PDO::PARAM_INT);
    $stmt->execute();
    $location = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$location) {
        die("Location not found.");
    }
} else {
    die("No location ID provided.");
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $loctitle = $_POST['loctitle'];
    $virtualurl = $_POST['virtualurl'];
    $intfeatures = $_POST['intfeatures'];
    $gsarea = $_POST['gsarea'];
    $keywords = $_POST['keywords'];
    $description = $_POST['description'];
    $category = $_POST['category'];
    $img = $_FILES['img']['name'];

    if ($img) {
        move_uploaded_file($_FILES['img']['tmp_name'], "uploads/" . $img);
    } else {
        $img = $location['img']; 
    }

    $sql = "UPDATE locdata SET loctitle = :loctitle, virtualurl = :virtualurl, intfeatures = :intfeatures, gsarea = :gsarea, 
            keywords = :keywords, description = :description, category = :category, img = :img WHERE loc_id = :loc_id";
    
    $stmt = $pdo->prepare($sql);
    $stmt->bindParam(':loctitle', $loctitle);
    $stmt->bindParam(':virtualurl', $virtualurl);
    $stmt->bindParam(':intfeatures', $intfeatures);
    $stmt->bindParam(':gsarea', $gsarea);
    $stmt->bindParam(':keywords', $keywords);
    $stmt->bindParam(':description', $description);
    $stmt->bindParam(':category', $category);
    $stmt->bindParam(':img', $img);
    $stmt->bindParam(':loc_id', $loc_id, PDO::PARAM_INT);

    $stmt->execute();

    header('Location: search-locations.php');
    exit;
}
?>


<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css" rel="stylesheet">
    <title>Edit Location</title>
    <style>

        body {
            font-family: Arial, sans-serif;
            background-color: #f4f4f4;
            color: <?php echo $text_color; ?>; 
            margin: 0;
            padding: 0;
            display: flex;
            flex-direction: column;
            min-height: 100vh;
        }

        .container {
            background-color: lightgrey;
            display: flex;
            flex-direction: column;
            padding: 2rem;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
            border-radius: 8px;
            width: 90%;
            max-width: 800px;
            margin: 2rem auto;
        }

        h2 {
            text-align: center;
            margin-bottom: 2rem;
            color: <?php echo $text_color; ?>; 
        }

        .form-group {
            margin-bottom: 1.5rem;
        }

        .form-group label {
            display: block;
            margin-bottom: 0.5rem;
            font-weight: bold;
            color: <?php echo $text_color; ?>; 
        }

        .form-group input,
        .form-group textarea {
            width: 100%;
            font-family: arial;
            padding: 0.8rem;
            border-radius: 5px;
            font-size: 1rem;
            border: 1px solid #ddd;
            background-color: #fff;
            color: #333;
        }

        .form-group {
          margin-right: 20px;
        }

        .form-group input[type="file"] {
            padding: 0;
        }

        button {
            display: inline-block;
            background-color: <?php echo isset($_SESSION['button_color']) ? $_SESSION['button_color'] : '#007BFF'; ?>;
            padding: 0.8rem 1.5rem;
            text-decoration: none;
            border-radius: 5px;
            text-align: center;
            border: none;
            cursor: pointer;
            font-size: 1rem;
            transition: background-color 0.3s ease;
        }

        img {
            max-width: 100px;
            margin-top: 10px;
            border-radius: 5px;
        }

        .footer {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 1rem 2rem;
            font-size: 0.9rem;
            background-color: <?php echo isset($_SESSION['footer_color']) ? $_SESSION['footer_color'] : '#333'; ?>;
            color: <?php echo $text_color; ?>;
            <?php echo $text_bold; ?>
            position: relative;
            bottom: 0;
            left: 0;
            right: 0;
            z-index: 10;
            box-shadow: 0 -2px 5px rgba(0, 0, 0, 0.1);
        }

        .footer .contact-info {
            display: flex;
            gap: 1rem;
            flex-wrap: wrap;
        }

        .footer img.mainloginlogo {
            width: 13%;
            height: auto;
        }

        .button {
            display: inline-block;
            background-color: <?php echo isset($_SESSION['button_color']) ? $_SESSION['button_color'] : '#007BFF'; ?>;
            padding: 0.8rem 1.5rem;
            text-decoration: none;
            border-radius: 5px;
            text-align: center;
            border: none;
            cursor: pointer;
            font-size: 1rem;
            transition: background-color 0.3s ease;
            margin-bottom: 1rem;
            position: absolute;
            top: 60px;
            left: 18%;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
            <?php echo $text_bold; ?>
            color: <?php echo $text_color; ?>;
        }

        .updatebutton {
          box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
          <?php echo $text_bold; ?>
          color: <?php echo $text_color; ?>;
        }

        
    </style>
</head>
<body>

<div class="container">
<a class="button" href="search-locations.php">Close</a>
    <h2>Edit Location</h2>
    <form action="" method="POST" enctype="multipart/form-data">
    
        <div class="form-group">
            <label for="loctitle">Location Title:</label>
            <input type="text" name="loctitle" id="loctitle" value="<?php echo htmlspecialchars($location['loctitle']); ?>" required>
        </div>

        <div class="form-group">
            <label for="virtualurl">Virtual URL:</label>
            <input type="text" name="virtualurl" id="virtualurl" value="<?php echo htmlspecialchars($location['virtualurl']); ?>">
        </div>

        <div class="form-group">
            <label for="intfeatures">Internal Features:</label>
            <textarea name="intfeatures" id="intfeatures"><?php echo htmlspecialchars($location['intfeatures']); ?></textarea>
        </div>

        <div class="form-group">
            <label for="gsarea">Greenscreen Area:</label>
            <input type="text" name="gsarea" id="gsarea" value="<?php echo htmlspecialchars($location['gsarea']); ?>">
        </div>

        <div class="form-group">
            <label for="keywords">Keywords:</label>
            <input type="text" name="keywords" id="keywords" value="<?php echo htmlspecialchars($location['keywords']); ?>">
        </div>

        <div class="form-group">
            <label for="description">Description:</label>
            <textarea name="description" id="description"><?php echo htmlspecialchars($location['description']); ?></textarea>
        </div>

        <div class="form-group">
            <label for="category">Category:</label>
            <input type="text" name="category" id="category" value="<?php echo htmlspecialchars($location['category']); ?>">
        </div>
        <div class="form-group">
            <label for="img">Image:</label>
      </div>
            <input type="file" name="img" id="img">
            </br>
            </br>
            <?php if ($location['img']): ?>
                <img src="uploads/<?php echo htmlspecialchars($location['img']); ?>" alt="Location Image">
            <?php endif; ?>
            </br>
            </br>

        <button type="submit" class="updatebutton" >Update Location</button>
    </form>
</div>

<footer class="footer">
    <div class="contact-info">
        <span>Email: contact@moviesite.com</span>
        <span>Phone: +44 123 456 789</span>
    </div>
    <img class="mainloginlogo" src="./poweredbyms.png" alt="MS_logo">
</footer>

</body>
</html>