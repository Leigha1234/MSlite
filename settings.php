<?php
session_start();
require_once 'db_connection.php';

$navbar_color = $_SESSION['navbar_color'] ?? '#007BFF';
$footer_color = $_SESSION['footer_color'] ?? '#333';
$button_color = $_SESSION['button_color'] ?? '#007BFF';
$text_color = $_SESSION['text_color'] ?? '#000';
$text_bold = $_SESSION['text_bold'] ?? 0;  
$dark_mode = $_SESSION['dark_mode'] ?? 0;  
$font = $_SESSION['font'] ?? 'Arial';  

$message = '';

try {
    $sql_enquiry_count = "SELECT COUNT(*) AS enquiry_count FROM enquiries WHERE status = 'unread'";
    $stmt_enquiry_count = $pdo->prepare($sql_enquiry_count);
    $stmt_enquiry_count->execute();
    $enquiry_count = $stmt_enquiry_count->fetchColumn();
} catch (PDOException $e) {
    $_SESSION['message'] = "Error fetching enquiry count: " . $e->getMessage();
    $enquiry_count = 0;  
}

$enquiry_count = $enquiry_count ?? 0;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $navbar_color = $_POST['navbar_color'];
    $footer_color = $_POST['footer_color'];
    $button_color = $_POST['button_color'];
    $text_color = $_POST['text_color'];
    $text_bold = isset($_POST['text_bold']) ? 1 : 0;
    $dark_mode = isset($_POST['dark_mode']) ? 1 : 0;

    try {
        $sql = "UPDATE settings SET navbar_color = ?, footer_color = ?, button_color = ?, text_color = ?, text_bold = ?, dark_mode = ? WHERE user_id = ?";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$navbar_color, $footer_color, $button_color, $text_color, $text_bold, $dark_mode, $_SESSION['user_id']]);

        $_SESSION['navbar_color'] = $navbar_color;
        $_SESSION['footer_color'] = $footer_color;
        $_SESSION['button_color'] = $button_color;
        $_SESSION['text_color'] = $text_color;
        $_SESSION['text_bold'] = $text_bold; 
        $_SESSION['dark_mode'] = $dark_mode; 
        $_SESSION['message'] = 'Settings saved successfully!';
    } catch (PDOException $e) {
        $_SESSION['message'] = 'Error saving settings: ' . $e->getMessage();
    }
}

try {
    $sql = "SELECT navbar_color, footer_color, button_color, text_color, text_bold, dark_mode FROM settings WHERE user_id = ?";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$_SESSION['user_id']]);
    $row = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($row) {
        $_SESSION['navbar_color'] = $row['navbar_color'];
        $_SESSION['footer_color'] = $row['footer_color'];
        $_SESSION['button_color'] = $row['button_color'];
        $_SESSION['text_color'] = $row['text_color'];
        $_SESSION['text_bold'] = $row['text_bold'];
        $_SESSION['dark_mode'] = $row['dark_mode'];
    }
} catch (PDOException $e) {
    $_SESSION['message'] = 'Error fetching current settings: ' . $e->getMessage();
}

function adjust_color_brightness($hex, $percent) {
    $hex = str_replace('#', '', $hex);
    $r = hexdec(substr($hex, 0, 2));
    $g = hexdec(substr($hex, 2, 2));
    $b = hexdec(substr($hex, 4, 2));

    $r = max(0, min(255, $r + $percent));
    $g = max(0, min(255, $g + $percent));
    $b = max(0, min(255, $b + $percent));

    return sprintf("#%02x%02x%02x", $r, $g, $b);
}

$hover_color = adjust_color_brightness($button_color, -20); 

?>




<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
  <link rel="stylesheet" href="css.css">
  <title>Profile - MovieSite Locations</title>
  <style>
  .sidebar {
    width: 200px;
    background-color: <?php echo isset($_SESSION['navbar_color']) ?>;
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
    background: none !important;
    border: none;
    font-size: 1.5rem;
    cursor: pointer;
    color: <?php echo $text_color; ?>;
    padding: 0.5rem;
    margin: 0;
    outline: none;
    right: -10.2rem;
    transition: color 0.3s ease, background-color 0.3s ease;
}

#sidebar-toggle:hover {
    background-color: transparent !important;
    color: <?php echo adjust_color_brightness($text_color, -15); ?>;
}


.sidebar.collapsed #sidebar-toggle {
    left: 1rem; 
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


  html {
    font-family: Arial, sans-serif;
  }

  body {
    font-family: <?php echo $_SESSION['font_family']; ?>;
}


  .container {
    display: flex;
    flex-grow: 1;
    padding: 2rem;
    box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
    border-radius: 8px;
    background-color: #999;
  }

  .form-group {
    margin-bottom: 1.5rem;
  }

.form-group .button {
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
color: #fff;
}

  .profile-container {
    width: 75%;
    margin-left: 250px;
    padding: 2rem;
    box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
   background-color: #FAF9F6;
    border-radius: 8px;
  }

  h2 {
    text-align: center;
    margin-bottom: 2rem;
  }

  form {
    margin: 50px;
  }

  .form-group input,
  .form-group select,
  .form-group textarea {
    width: 100%;
    padding: 0.8rem;
    border: 1px solid #ddd;
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

      .form-group .button, .userbutton {
      border: none;
      padding: 10px;
      border-radius: 25px;
      cursor: pointer;
      font-size: 1rem;
      width: 100%;
      transition: background-color 0.3s ease;
    }

    .userbutton {
      background-color: <?php echo isset($_SESSION['button_color']) ? $_SESSION['button_color'] : '#007BFF'; ?>;
    }

    .userbutton:hover {
      background-color: <?php echo adjust_color_brightness($button_color, -20); ?>;
    }

  .footer {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 1rem 2rem;
    font-size: 0.9rem;
    background-color: <?php echo isset($_SESSION['footer_color']) ? $_SESSION['footer_color'] : '#333'; ?>;
    position: relative;
    bottom: 0;
  }

  body {
    transition: background-color 0.3s, color 0.3s;
    font-family: Arial, sans-serif;
    margin: 0;
    padding: 0;
    background-color: #f4f4f4;
    display: flex;
    flex-direction: column;
    min-height: 100vh;
    color: <?php echo isset($_SESSION['text_color'])  ?>;
  }

.container.shift-left {
  margin-left: -160px;  
}

  .container, .form-group label, .footer, .sidebar a, .navbar, .sidebar, h2 {
    color: <?php echo $text_color; ?>; 
  }

  input.checkbox{
    margin-left: -250px ;
    cursor: pointer;
}

.userbutton {
  margin-bottom: -2%;
  width: 30%;
  margin-left: 70%;
}
  </style>
</head>
<body>
<div class="sidebar" id="sidebar">
  <button id="sidebar-toggle" style="position: absolute; top: 1rem; left: 1rem; background: none; border: none; font-size: 1.5rem; cursor: pointer;">
    <i class="fas fa-bars"></i>
  </button>
  <br><br><br>
  <a href="./index.php"><i class="fas fa-sign-out-alt"></i> <span>Log out</span></a>
  <a href="./search-locations.php"><i class="fas fa-search"></i> <span>Search Locations</span></a>
  <a href="./addloctest.php"><i class="fas fa-plus-circle"></i> <span>Add a Location</span></a>
  <a href="./reports.php"><i class="fas fa-chart-line"></i> <span>Reports</span></a>

  <a href="./enquiries.php" style="display: flex; align-items: center; text-decoration: none;">
    <i class="fas fa-envelope"></i> 
    <span >Enquiries/Inbox</span>
    <span id="enquiry-count" style="background-color: red; color: white; border-radius: 50%; padding: 0.2rem 0.5rem; font-size: 0.9rem; margin-left: 10px; display: inline-block;">
      <?php echo $enquiry_count; ?>
    </span>
  </a>

  <a href="./settings.php"><i class="fas fa-cog"></i> <span>Settings</span></a>
</div>


  <div class="container">
 
    <div class="profile-container">
      <h2>Profile Settings</h2>
      <form method="post">
      <button type="button" class="button userbutton" onclick="window.location.href='users.php'">
  <i class="fas fa-users"></i> User Management
</button>


        
      <div class="form-group">
      
  <label for="toggle-label">Dark Mode</label>  
  <input type="checkbox" id="darkModeToggle" name="dark_mode" class="checkbox" <?php echo isset($_SESSION['dark_mode']) && $_SESSION['dark_mode'] ? 'checked' : ''; ?>>
</div>
    


        <div class="form-group">
          <label for="navbar_color">Navigation Bar Color</label>
          <input type="color" id="navbar_color" name="navbar_color" value="<?php echo isset($_SESSION['navbar_color']) ? $_SESSION['navbar_color'] : '#007BFF'; ?>">
        </div>

        <div class="form-group">
          <label for="footer_color">Footer Color</label>
          <input type="color" id="footer_color" name="footer_color" value="<?php echo isset($_SESSION['footer_color']) ? $_SESSION['footer_color'] : '#333'; ?>">
        </div>

        <div class="form-group">
          <label for="button_color">Button Color</label>
          <input type="color" id="button_color" name="button_color" value="<?php echo isset($_SESSION['button_color']) ? $_SESSION['button_color'] : '#007BFF'; ?>">
        </div>

        <div class="form-group">
          <label for="text_color">Text Color</label>
          <input type="color" id="text_color" name="text_color" value="<?php echo isset($_SESSION['text_color']) ? $_SESSION['text_color'] : '#000'; ?>">
        </div>

        <div class="form-group">
  <label>Bold Text</label>
  <input type="checkbox" id="text_bold" name="text_bold" class="checkbox" <?php echo isset($_SESSION['text_bold']) && $_SESSION['text_bold'] ? 'checked' : ''; ?>>
</div>

        <div class="form-group">
          <button type="submit" class="button" id="save-button">Save Changes</button>
        </div>
      </form>

      <?php
      if (isset($_SESSION['message'])) {
          echo "<div class='message'>" . $_SESSION['message'] . "</div>";
          unset($_SESSION['message']);
      }
      ?>
    </div>
  </div>

  <footer class="footer">
        <div class="contact-info">
          <span>Email: contact@moviesite.com</span>
          <span>Phone: +44 123 456 789</span>
        </div>
        <img class="mainloginlogo" src="./poweredbyms.png" alt="MS_logo">
      </footer>

      <script>
        
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

const toggle = document.getElementById('darkModeToggle');

if (<?php echo isset($_SESSION['dark_mode']) && $_SESSION['dark_mode'] ? 'true' : 'false'; ?>) {
    document.body.classList.add('dark-mode');
    toggle.checked = true;
}

toggle.addEventListener('change', () => {
    document.body.classList.toggle('dark-mode', toggle.checked);
});

const sidebarToggle = document.getElementById('sidebar-toggle');
if (sidebarToggle) {
    sidebarToggle.addEventListener('click', function () {
        const sidebar = document.getElementById('sidebar');
        const container = document.querySelector('.container'); 
        sidebar.classList.toggle('collapsed');
        container.classList.toggle('shift-left');
    });
}

function applyChanges() {
    const navbarColor = document.getElementById('navbar_color').value;
    const footerColor = document.getElementById('footer_color').value;
    const buttonColor = document.getElementById('button_color').value;
    const textColor = document.getElementById('text_color').value;
    const isBold = document.getElementById('text_bold').checked;
    const darkModeLabel = document.querySelector('.toggle-label');

    const navbar = document.querySelector('.sidebar');
    const footer = document.querySelector('.footer');
    const saveButton = document.getElementById('save-button');
    const body = document.body;
    const headings = document.querySelectorAll('h2');
    const buttons = document.querySelectorAll('button');
    const navbarLinks = document.querySelectorAll('.sidebar a');
    const navbarIcons = document.querySelectorAll('.sidebar a i');
    const formLabels = document.querySelectorAll('.form-group label');
    const formInputs = document.querySelectorAll('.form-group input');
    const formTextareas = document.querySelectorAll('.form-group textarea');

    navbar.style.backgroundColor = navbarColor;
    footer.style.backgroundColor = footerColor;
    saveButton.style.backgroundColor = buttonColor;
    body.style.color = textColor;

    headings.forEach(function (heading) {
        heading.style.color = textColor;
        heading.style.fontWeight = isBold ? 'bold' : 'normal';
    });

    buttons.forEach(function (button) {
        button.style.color = textColor;
        button.style.fontWeight = isBold ? 'bold' : 'normal';

        if (!button.classList.contains('sidebar-toggle')) {
            button.onmouseover = function () {
                this.style.backgroundColor = '<?php echo adjust_color_brightness($button_color, -15); ?>';
            };
            button.onmouseout = function () {
                this.style.backgroundColor = buttonColor;
            };
        }
    });

    navbarLinks.forEach(function (link) {
        link.style.color = textColor;
        link.style.fontWeight = isBold ? 'bold' : 'normal';
    });

    navbarIcons.forEach(function (icon) {
        icon.style.color = textColor;
    });

    formLabels.forEach(function (label) {
        label.style.fontWeight = isBold ? 'bold' : 'normal';
    });

    formInputs.forEach(function (input) {
        input.style.fontWeight = isBold ? 'bold' : 'normal';
    });

    formTextareas.forEach(function (textarea) {
        textarea.style.fontWeight = isBold ? 'bold' : 'normal';
    });

    const footerText = footer.querySelectorAll('span');
    footerText.forEach(function (span) {
        span.style.fontWeight = isBold ? 'bold' : 'normal';
    });

    if (darkModeLabel) {
        darkModeLabel.style.fontWeight = isBold ? 'bold' : 'normal';
    }
}

document.getElementById('navbar_color').addEventListener('input', applyChanges);
document.getElementById('footer_color').addEventListener('input', applyChanges);
document.getElementById('button_color').addEventListener('input', applyChanges);
document.getElementById('text_color').addEventListener('input', applyChanges);
document.getElementById('text_bold').addEventListener('change', applyChanges);

window.onload = applyChanges;
</script>



</body>
</html>
