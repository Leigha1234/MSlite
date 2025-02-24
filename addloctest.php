<?php
require_once 'db_connection.php';
require_once 'settings_loader.php';

// Fetch unread enquiries count
try {
    $sql_enquiry_count = "SELECT COUNT(*) AS enquiry_count FROM enquiries WHERE status = 'unread'";
    $stmt_enquiry_count = $pdo->prepare($sql_enquiry_count);
    $stmt_enquiry_count->execute();
    $enquiry_count = $stmt_enquiry_count->fetchColumn();
} catch (PDOException $e) {
    error_log("Database Error: " . $e->getMessage());
    $enquiry_count = 0;
}

// Dark mode toggle
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['dark_mode'])) {
    $_SESSION['dark_mode'] = $_POST['dark_mode'] === 'true';
    header("Location: " . $_SERVER['PHP_SELF']);
    exit;
}

// Adjust color brightness function
function adjust_color_brightness($hex, $steps) {
    $steps = max(-255, min(255, $steps));
    $hex = str_replace('#', '', $hex);
    list($r, $g, $b) = [hexdec(substr($hex, 0, 2)), hexdec(substr($hex, 2, 2)), hexdec(substr($hex, 4, 2))];
    return sprintf('#%02x%02x%02x', max(0, min(255, $r + $steps)), max(0, min(255, $g + $steps)), max(0, min(255, $b + $steps)));
}

// Image upload handling
if (isset($_POST['upload']) && isset($_FILES['uploadfile'])) {
    try {
        $img = $_FILES['uploadfile']['name'];
        $tempname = $_FILES['uploadfile']['tmp_name'];
        $folder = "./image/" . $img;
        
        if (move_uploaded_file($tempname, $folder)) {
            $sql = "INSERT INTO images (img, img_path) VALUES (?, ?)";
            $stmt = $pdo->prepare($sql);
            $stmt->execute([$img, $folder]);
            echo "<h3>&nbsp; Image uploaded successfully!</h3>";
        } else {
            echo "<h3>&nbsp; Failed to upload image!</h3>";
        }
    } catch (PDOException $e) {
        error_log("Image Upload Error: " . $e->getMessage());
        echo "<h3>&nbsp; Database error!</h3>";
    }
}

// Location form submission handling
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['loctitle'])) {
    try {
        $pdo->beginTransaction();
        
        $loctitle = trim($_POST['loctitle']);
        $virtualurl = trim($_POST['virtualurl']);
        $intfeatures = trim($_POST['intfeatures'] ?? '');
        $gsarea = trim($_POST['gsarea'] ?? '');
        $keywords = trim($_POST['keywords']);
        $user = trim($_POST['loc_id']);
        $created_at = date('Y-m-d H:i:s');
        $exfeatures = trim($_POST['exfeatures'] ?? '');
        $description = trim($_POST['description']);
        $settings = trim($_POST['settings']);
        $parking = trim($_POST['parking']);
        $category = trim($_POST['category']);
        $sustainable = trim($_POST['sustainable']);
        $info = trim($_POST['info']);
        $totalno_favs = 0;

        $img_id = null;
        $img_path = null;

        if (!empty($_FILES['image']['name'])) {
            $target_dir = "image/";
            $target_file = $target_dir . basename($_FILES['image']['name']);
            $imageFileType = strtolower(pathinfo($target_file, PATHINFO_EXTENSION));
            
            $allowed_types = ['jpg', 'jpeg', 'png', 'gif'];
            if (in_array($imageFileType, $allowed_types) && move_uploaded_file($_FILES['image']['tmp_name'], $target_file)) {
                $img_id = uniqid('img_'); 
                
                $sql_img = "INSERT INTO images (img_id, img, img_path) VALUES (?, ?, ?)";
                $stmt_img = $pdo->prepare($sql_img);
                $stmt_img->execute([$img_id, $_FILES['image']['name'], $target_file]);
                $img_path = $target_file;
            }
        }

        $sql = "INSERT INTO locdata (loctitle, virtualurl, intfeatures, gsarea, keywords, user, created_at, exfeatures, description, settings, parking, category, sustainable, info, img_id, totalno_favs) 
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([
            $loctitle, $virtualurl, $intfeatures, $gsarea, $keywords, $user, $created_at, 
            $exfeatures, $description, $settings, $parking, $category, $sustainable, $info, 
            $img_id, $totalno_favs
        ]);

        $pdo->commit();
        echo "Location added successfully.";
    } catch (PDOException $e) {
        $pdo->rollBack();
        error_log("Error: " . $e->getMessage());
        echo "An error occurred.";
    }
}
?>





<!DOCTYPE html>
<html lang="en">
   <head>
      <title>Add Location</title>
      <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css" rel="stylesheet">
      <style>
.sidebar {
    width: 200px;
    background-color: <?php echo isset($_SESSION['navbar_color']) ? $_SESSION['navbar_color'] : '#007BFF'; ?>;
    color: <?php echo $navbar_text_color; ?>; 
    position: fixed;
    top: 0;
    left: 0;
    bottom: 0;
    padding: 2rem;
    display: flex;
    flex-direction: column;
    align-items: flex-start;
    transition: background-color 0.3s ease;
  }

  #sidebar-toggle {
    right: -10.2rem;
background: none;
border: none;
font-size: 1.5rem;
cursor: pointer;
color: <?php echo $text_color; ?>; 
}

.sidebar.collapsed #sidebar-toggle {
    left: 1rem; /* Move it to the left when collapsed */
    right: auto;
}

.sidebar {
    width: 200px;
    position: fixed;
    top: 0;
    left: 0;
    bottom: 0;
    padding: 2rem;
    display: flex;
    flex-direction: column;
    align-items: flex-start;
    transition: all 0.3s ease; 
  }
  
  #sidebar-toggle {
    border: none;
    background: none; 
    cursor: pointer;
    padding: 0.5rem 1rem; 
    outline: none;
    transition: color 0.3s ease; 
  }

.sidebar.collapsed a span {
display: none; 
}

.sidebar.collapsed i {
font-size: 1.8rem;
}

  .sidebar a i {
    font-size: 1.4rem;
    width: 24px;
    text-align: center;
    flex-shrink: 0;
  }

  .sidebar.collapsed a {
    display: flex;
    justify-content: center;
    align-items: center;
    text-align: center;
    font-size: 1.2rem;
  }
  
  .sidebar a {
    display: flex;
    align-items: center;
    gap: 10px;
    text-decoration: none;
    margin-bottom: 1rem;
    font-size: 1.1rem;
  }
 
.sidebar.collapsed {
width: 60px; 
padding: 1rem;
}
  .container, .form-group label, .footer, .sidebar a, .navbar, .sidebar, h2 {
    color: <?php echo $text_color; ?>; 
    <?php echo $text_bold; ?>
  }

.container {
  flex-grow: 1; 
}

         .btn {
    border:none;
    background-color: <?php echo isset($_SESSION['button_color']) ? $_SESSION['button_color'] : '#007BFF'; ?>;
    color: <?php echo $button_text_color; ?>; 
    <?php echo $text_bold; ?>
    cursor: pointer;
  }

  html {
    font-family: Arial, sans-serif;
  }

  .container {
    display: flex;
    flex-grow: 1;
    padding: 2rem;
    box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
    border-radius: 8px;

  }

  .form-group {
    margin-bottom: 1.5rem;
  }

.form-group button {
    border: none;
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

  body.dark-mode .profile-container {
background-color: #444;
  }

  .profile-container {
    width: 75%;
    margin-left: 250px;
    box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
    border-radius: 8px;
  }

  h2 {
    text-align: center;
    margin-bottom: 2rem;

  }

  form {
    margin: 50px;
  }

  .form-group select,
  .form-group input,
  .form-group textarea {
    width: 100%;
    padding: 0.8rem;
    border-radius: 25px;
    font-size: 1rem;
  }

  .footer .contact-info {
    display: flex;
    gap: 1rem;
  }

  .message {
    margin-top: 1rem;
    text-align: center;
    color: green;
  }

  .error-message {
    color: red;
  }
 
  body.dark-mode .container {
          background-color: #333;
          color: #fff;
      }

      .toggle-label {
          display: flex;
          align-items: center;
          font-size: 16px;
          
      }

.footer {
  display: flex;
  justify-content: space-between;
  align-items: center;
  padding: 1rem 2rem;
  font-size: 0.9rem;
  background-color: <?php echo isset($_SESSION['footer_color']) ? $_SESSION['footer_color'] : '#333'; ?>;
  color: <?php echo $footer_text_color; ?>;
  position: relative;
  bottom: 0;
  left: 0;
  right: 0;
  z-index: 10;
  box-shadow: 0 -2px 5px rgba(0, 0, 0, 0.1); 
}
  
  h2 {
    text-align: center;
    margin-bottom: 2rem;
    color: <?php echo $text_color; ?>; 
  }

  .form-group input, .form-group label {
    color: <?php echo $text_color; ?>; 
  }

.contact-info {
color: <?php echo $text_color; ?>;
}

  .navbar {
    background-color: <?php echo isset($_SESSION['navbar_color']) ? $_SESSION['navbar_color'] : '#007BFF'; ?>;
  }
  
  .footer {
    background-color: <?php echo isset($_SESSION['footer_color']) ? $_SESSION['footer_color'] : '#333'; ?>;
    color: <?php echo $footer_text_color; ?>;  
  }
 
.container.shift-left {
  margin-left: -120px;  
}
  
  .btn:hover {
   border: none;
   text-decoration: none;
    background-color: <?php echo adjust_color_brightness(isset($_SESSION['button_color']) ? $_SESSION['button_color'] : '#007BFF', -15); ?>;
  }

  body {
    font-family: <?php echo $_SESSION['font_family']; ?>;
}

  body {
    font-family: Arial, sans-serif;
    margin: 0;
    padding: 0;
    background-color: #f4f4f4;
    color: <?php echo $text_color; ?>;
  }

    html, body {
    font-family: Arial, sans-serif;
    margin: 0;
    padding: 0;
    background-color: #f4f4f4;
    color: <?php echo $text_color; ?>; 
    <?php echo $text_bold; ?>
  }
      </style>
   </head>
   <body class="<?php echo isset($_SESSION['dark_mode']) && $_SESSION['dark_mode'] ? 'dark-mode' : ''; ?>">
   <div class="sidebar" id="sidebar">
  <button id="sidebar-toggle" style="position: absolute; top: 1rem; left: 1rem; background: none; border: none; font-size: 1.5rem; cursor: pointer;">
    <i class="fas fa-bars"></i>
  </button>
  <br><br><br>
  <a href="./index.php"><i class="fas fa-sign-out-alt"></i> <span>Log out</span></a>
  <a href="./search-locations.php"><i class="fas fa-search"></i> <span>Search Locations</span></a>
  <a href="./addloctest.php"><i class="fas fa-plus-circle"></i> <span>Add a Location</span></a>
  <a href="./reports.php"><i class="fas fa-chart-line"></i> <span>Reports</span></a>

  <!-- Enquiries/Inbox Link with Badge -->
  <a href="./enquiries.php" style="display: flex; align-items: center; text-decoration: none;">
    <i class="fas fa-envelope"></i> 
    <span >Enquiries/Inbox</span>
    <!-- Enquiry count badge -->
    <span id="enquiry-count" style="background-color: red; color: white; border-radius: 50%; padding: 0.2rem 0.5rem; font-size: 0.9rem; margin-left: 10px; display: inline-block;">
      <?php echo $enquiry_count; ?>
    </span>
  </a>

  <a href="./settings.php"><i class="fas fa-cog"></i> <span>Settings</span></a>
</div>


      <div class="container">
        <div class="profile-container">
        <form action="insert.php" method="post" >
          <h2>Add a Location</h2>
          <div class="form-group">
            <p>
               <label for="loctitle">Location Title<span style="color: red;">*</span>:</label>
               <input type="text" name="loctitle" id="loctitle" class="form-control" required>
            </p>
          </div>

          <div class="form-group">
            <p>
               <label for="virtualurl">Virtual Url<span style="color: red;">*</span>:</label>
               <input type="text" name="virtualurl" id="virtualurl" class="form-control" required>
            </p>
          </div>

          <div class="form-group">
            <p>
               <label for="intfeatures">Internal Features:</label>
               <input type="text" name="intfeatures" id="intfeatures" class="form-control" >
            </p>
          </div>

          <div class="form-group">
            <p>
               <label for="exfeatures">External Features:</label>
               <input type="text" name="exfeatures" id="exfeatures" class="form-control" >
            </p>
          </div>

          <div class="form-group">
            <p>
               <label for="description">Description<span style="color: red;">*</span>:</label>
               <input type="text" name="description" id="description" class="form-control" required>
            </p>
          </div>

          <div class="form-group">
            <p>
               <label for="settings">Settings<span style="color: red;">*</span>:</label>
               <input type="text" name="settings" id="settings" class="form-control" required>
            </p>
</div>

          <div class="form-group">
            <p>
               <label for="parking">Available Parking<span style="color: red;">*</span>:</label>
               <input type="text" name="parking" id="parking" class="form-control" required>
            </p>
          </div>

          <div class="form-group">
            <p>
               <label for="gsarea">Greenscreen Area<span style="color: red;">*</span>:</label>
               <input type="text" name="gsarea" id="gsarea" class="form-control" >
            </p>
          </div>

          <div class="form-group">
            <p>
               <label for="keywords">Keywords<span style="color: red;">*</span>:</label>
               <input type="text" name="keywords" id="keywords" class="form-control" required>
            </p>
          </div>

          <div class="form-group">
            <p>
               <label for="category">Category<span style="color: red;">*</span>:</label>
               <input type="text" name="category" id="category" class="form-control" required>
            </p>
          </div>

          <div class="form-group">
            <p>
               <label for="email">Email<span style="color: red;">*</span>:</label>
               <input type="email" name="email" id="email" class="form-control" required>
            </p>
          </div>

          <div class="form-group">
            <p>
               <label for="sustainable">Is the location / property environmentally friendly or have any sustainable initiatives?<span style="color: red;">*</span>:</label>
               <input type="text" name="sustainable" id="sustainable" class="form-control" required>
            </p>
          </div>


          <div class="form-group">
            <p>
          <label for="img">Image upload:</label>
                <input class="form-control" type="file" name="img" required />
</p>
            </div>

          <div class="form-group">
            <p>
               <label for="info">More Information:</label>
               <input type="text" name="info" id="info" class="form-control" >
            </p>
          </div>
      

          <div class="form-group">
            
            <input type="submit" class="btn" value="Submit">
          </div>
        </form>
      </div></div>
      
      <footer class="footer">
        <div class="contact-info">
          <span>Email: contact@moviesite.com</span>
          <span>Phone: +44 123 456 789</span>
        </div>
        <img class="mainloginlogo" src="./poweredbyms.png" alt="MS_logo">
      </footer>

<script>
document.addEventListener('DOMContentLoaded', function () {
  const sidebarToggle = document.getElementById('sidebar-toggle');
  if (sidebarToggle) {
    sidebarToggle.addEventListener('click', function () {
      const sidebar = document.getElementById('sidebar');
      const container = document.querySelector('.container');
      sidebar.classList.toggle('collapsed');
      container.classList.toggle('shift-left');
    });
  }
});

        buttons.forEach(function (button) {
        button.style.color = textColor;
        button.style.fontWeight = isBold ? 'bold' : 'normal';
    });

    function updateEnquiryCount() {
  fetch('./get_enquiry_count.php')
    .then(response => response.json())
    .then(data => {
      const enquiryCountElement = document.getElementById('enquiry-count');
      if (data.enquiry_count > 0) {
        enquiryCountElement.textContent = data.enquiry_count;
        enquiryCountElement.style.display = 'inline'; 
      } else {
        enquiryCountElement.style.display = 'none'; 
      }
    })
    .catch(error => console.error('Error fetching enquiry count:', error));
}

updateEnquiryCount();

setInterval(updateEnquiryCount, 10000);

      </script>
   </body>
</html>
