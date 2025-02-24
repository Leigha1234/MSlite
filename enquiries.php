<?php
require_once 'db_connection.php'; 
require_once 'settings_loader.php'; 

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
  $_SESSION['dark_mode'] = isset($_POST['dark_mode']) ? true : false;
  header('Location: ' . $_SERVER['PHP_SELF']);
  exit;
}

function adjust_color_brightness($hex, $steps) {
  $steps = max(-255, min(255, $steps));
  $hex = str_replace('#', '', $hex);
  $r = hexdec(substr($hex, 0, 2));
  $g = hexdec(substr($hex, 2, 2));
  $b = hexdec(substr($hex, 4, 2));
  $r = max(0, min(255, $r + $steps));
  $g = max(0, min(255, $g + $steps));
  $b = max(0, min(255, $b + $steps));
  return sprintf('#%02x%02x%02x', $r, $g, $b);
}

$dark_mode = $_SESSION['dark_mode'] ?? false;

try {
    $sql_enquiry_count = "SELECT COUNT(*) AS enquiry_count FROM enquiries WHERE status = 'unread'";
    $stmt_enquiry_count = $pdo->prepare($sql_enquiry_count);
    $stmt_enquiry_count->execute();
    $enquiry_count = $stmt_enquiry_count->fetchColumn();
} catch (PDOException $e) {
    die("Error fetching enquiry count: " . $e->getMessage());
}

$enquiry_count = $enquiry_count ?? 0; 

try {
    $sql = "SELECT loctitle, subject, message, status 
            FROM enquiries
            ORDER BY CASE 
                        WHEN status = 'Unread' THEN 1 
                        ELSE 2 
                     END, loctitle";
    $stmt = $pdo->prepare($sql);
    $stmt->execute();

    $enquiries = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    die("Error fetching enquiries: " . $e->getMessage());
}

?>


<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
  <title>Enquiries - MovieSite Locations</title>
  <style>



.red-border {
  border: 2px solid red; 
  box-shadow: 0 4px 12px rgba(255, 0, 0, 0.3); 
  background-color: #fff; 
  z-index: 1; 
}

.pinboard {
  display: grid;
  grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
  gap: 1.5rem;
  padding: 2rem;
  justify-items: center;
  margin-top: 1rem; 
  height: auto;
}

.pinboard .unread-card {
  order: -1; 
}

.pinboard .card {
  background-color: rgba(170, 162, 162, 0.31);
  padding: 1.5rem;
  border-radius: 8px;
  box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
  cursor: pointer;
  transition: transform 0.2s ease, box-shadow 0.3s ease;
  width: 90%;
  max-width: 400px;
  text-align: center;
}

.pinboard .card:hover {
  transform: scale(1.05); 
  box-shadow: 0 6px 16px rgba(0, 0, 0, 0.2); 
}


    button {
    all: unset;
    color: inherit;
    background: none;
    border: none;
    padding: 0;
    font: inherit;
}

.message-btn {
  color: <?php echo $button_text_color; ?>;
  background-color: <?php echo isset($_SESSION['button_color']) ? $_SESSION['button_color'] : '#007BFF'; ?>;
  border: none;
  padding: 0.6rem 1.2rem;
  border-radius: 25px;
  cursor: pointer;
  font-size: 1rem;
  transition: background-color 0.3s ease, color 0.3s ease;
  text-decoration: none; 
}


.message-btn:hover {
    background-color: <?php echo adjust_color_brightness($button_color, -15); ?>;
    color: <?php echo $button_text_color; ?>;
}

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
background: none;
font-size: 1.5rem;
cursor: pointer;
border: none !important; 
color: <?php echo $text_color; ?>;
}

#sidebar-toggle:hover {
color: <?php echo adjust_color_brightness($text_color, -15); ?>; 
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
    display: inline-block; 
    font-size: 0.8rem;
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
    max-width: 50px;
}

.message-section textarea {
    width: 100%;
    min-height: 100px;
    box-sizing: border-box; 
    padding: 10px;
    border-radius: 5px;
    font-family: Arial, sans-serif;
    resize: vertical; 
}

.sidebar.collapsed i {
font-size: 1.2rem;
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
    transition: all 0.3s ease; 
  }
 
.sidebar.collapsed {
width: 80px; 
padding: 1rem;
}
.pinboard {
  display: grid;
  grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
  gap: 1.5rem; 
  padding: 2rem;
  justify-items: center; 
}

.pinboard .card {
  background-color:rgba(170, 162, 162, 0.31);
  padding: 1.5rem;
  border-radius: 8px;
  box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1); 
  cursor: pointer;
  transition: transform 0.2s ease, box-shadow 0.3s ease;
  width: 90%;
  max-width: 400px; 
  text-align: center;
}

.pinboard .card:hover {
  transform: scale(1.05); 
  box-shadow: 0 6px 16px rgba(0, 0, 0, 0.2); 
}

.pinboard .card h3 {
  font-size: 1.4rem;
  margin-bottom: 0.8rem;
}

.pinboard .card p {
  font-size: 1rem;

  margin-bottom: 1rem;
  line-height: 1.6;
}

@media screen and (max-width: 768px) {
  .pinboard {
    grid-template-columns: repeat(auto-fill, minmax(250px, 1fr)); 
  }

  .pinboard .card {
    padding: 1rem; 
  }
}

@media screen and (max-width: 480px) {
  .pinboard .card h3 {
    font-size: 1.2rem; 
  }

  .pinboard .card p {
    font-size: 0.9rem;
  }
}

    body {
      background-color: <?php echo $dark_mode ? '#2c2c2c' : '#ffffff'; ?>;
      color: <?php echo $dark_mode ? '#ffffff' : '#000000'; ?>;
      transition: background-color 0.3s ease, color 0.3s ease;
    }

    .footer {
      background-color: <?php echo $dark_mode ? '#1a1a1a' : '#333'; ?>;
      color: <?php echo $dark_mode ? '#ffffff' : '#ffffff'; ?>;
    }

    .message-section {
      background-color: <?php echo $dark_mode ? '#333333' : '#ffffff'; ?>;
      color: <?php echo $dark_mode ? '#ffffff' : '#000000'; ?>;
    }

  .container, .form-group label, .footer, .sidebar a, .navbar, .sidebar, h2 {
    color: <?php echo $text_color; ?>; 
  }
 
body {
    color: <?php echo $text_color; ?>;
    display: flex;
    flex-direction: column;
    min-height: 100vh; 
}

.footer {
  display: flex;
  justify-content: space-between; 
  align-items: center; 
  padding: 1rem 2rem; 
  text-align: center;
  margin-top: 2rem;
  font-size: 0.9rem;
  padding: 1rem;
  box-shadow: 0 -2px 5px rgba(0, 0, 0, 0.1); 
  position: relative;
}

html, body {
  height: auto;
  margin: 0;
  display: flex;
  flex-direction: column; 
  color: <?php echo $text_color; ?>;
  <?php echo $text_bold; ?>
}

.container {
  background-color: #FAF9F6;
  height: auto;
  flex-grow: 1; 
  color: <?php echo $text_color; ?>;
  <?php echo $text_bold; ?>
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

  .form-group input, .form-group label, .form-group button {
    color: <?php echo $text_color; ?>; 
  }
  .form-group button:hover {
    color: <?php echo adjust_color_brightness($text_color, -15); ?>; 
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

  .dark-mode {
  background-color:rgb(85, 85, 85);
  color: <?php echo $text_color; ?>; 
}

.mainloginlogo {
    width: 13%;
}

.dark-mode .location-card {
  background-color:rgb(152, 150, 150);
  color: <?php echo $text_color; ?>; 
}

.message-section {
      background-color: #fff;
      box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
      margin-top: 2rem;
      margin-right: 2rem;
      border-radius: 8px;
      padding: 2rem;
      display: none;
    }

    .message-section h2 {
      font-size: 1.5rem;
      margin-bottom: 1rem;
    }

    .message-section .message {
      padding: 1rem;
      background-color: #f1f1f1;
      margin-bottom: 1rem;
      border-radius: 5px;
    }

    .message-section .message.reply {
      background-color: #ccc;
    }

    .message-section .message.forward {
      background-color: #ccc;
    }


textarea {
    border-radius: 5px;
    font-family: Arial, sans-serif;
}

textarea: input {
    border-radius: 5px;
    font-family: Arial, sans-serif;
}

::placeholder {
    font-family: arial;
}

.pinboard {
  margin-left: 250px;
  padding: 2rem;
  transition: margin-left 0.3s ease; 
}

.message-section {
  background-color: rgb(113, 112, 112);
  box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
}

.card {
  background-color: <?php echo $dark_mode ? '#2c2c2c' : '#ffffff'; ?>;
  color: <?php echo $text_color; ?>;
  <?php echo $text_bold; ?>
}

.message {
  
  background-color: #ccc;
}

.message.reply {
    background-color: #ccc;
}

.message.forward {
    background-color: #ccc;
}

.message-section {
  margin-left: 300px;
  padding: 2rem;
  transition: margin-left 0.3s ease;
  color: <?php echo $text_color; ?>;
}

html {
    font-family: Arial, sans-serif;
  }

#back-to-pinboard {
  border:none;
    background-color: <?php echo isset($_SESSION['button_color']) ? $_SESSION['button_color'] : '#007BFF'; ?>;
    color: <?php echo $text_color; ?>; 
    <?php echo $text_bold; ?>
    padding: 0.6rem 1.2rem;
  border-radius: 25px;
  cursor: pointer;
  font-size: 1rem;
  transition: background-color 0.3s ease, color 0.3s ease;
  text-decoration: none; 
  }
  
  #back-to-pinboard:hover {
color: <?php echo adjust_color_brightness($text_color, -15); ?>; 
}

  button {
    border:none;
    background-color: <?php echo isset($_SESSION['button_color']) ? $_SESSION['button_color'] : '#007BFF'; ?>;
    color: <?php echo $button_text_color; ?>; 
    <?php echo $text_bold; ?>
  }

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

  .container.shift-left {
  margin-left: -180px; 
  transition: margin-left 0.3s ease; 
}

  #sidebar-toggle {
background: none;
border: none;
font-size: 1.5rem;
cursor: pointer;
color: <?php echo $text_color; ?>; 
}

#sidebar-toggle:hover {
color: <?php echo adjust_color_brightness($text_color, -15); ?>; 
}

.sidebar a {
    display: flex;
    align-items: center;
    gap: 10px;
    color: <?php echo $text_color; ?>; 
    text-decoration: none;
    margin-bottom: 1rem;
    font-size: 1.1rem;
  }

  .sidebar.collapsed a {
    display: flex;
    justify-content: center;
    align-items: center;
    text-align: center;
    font-size: 1.2rem;
    transition: all 0.3s ease;
  }
  
  .sidebar a {
    display: flex;
    align-items: center;
    gap: 10px;
    text-decoration: none;
    margin-bottom: 1rem;
    font-size: 1.1rem;
  }
  .sidebar.collapsed a {
    font-size: 0;
    transition: all 0.3s ease;
  }
  
  #sidebar-toggle {
    border: none;
    background: none; 
    cursor: pointer;
    padding: 0.5rem 1rem; 
    outline: none;
    transition: color 0.3s ease; 
    right: -163px;
   }
  
  .sidebar.collapsed {
  width: 50px; 
  padding: 1rem;
  transition: width 0.3s ease; 
}

.sidebar.collapsed a span {
display: none; 
}

.sidebar.collapsed i {
font-size: 1.8rem;
}

.container.shift-left {
  margin-left: -180px;  
}

body {
    font-family: <?php echo $_SESSION['font_family']; ?>;
}

  </style>
</head>
<body>
<div class="sidebar" id="sidebar">
  <button id="sidebar-toggle" style="position: absolute; top: 1rem; right: 1rem;">
    <i class="fas fa-bars"></i>
  </button>
  <br><br><br>
  <a href="./logout.php"><i class="fas fa-sign-out-alt"></i> <span>Log out</span></a>
  <a href="./search-locations.php"><i class="fas fa-search"></i> <span>Search Locations</span></a>
  <a href="./addloctest.php"><i class="fas fa-plus-circle"></i> <span>Add a Location</span></a>
  <a href="./reports.php"><i class="fas fa-chart-line"></i> <span>Reports</span></a>
  <a href="./enquiries.php">
  <i class="fas fa-envelope"></i> 
  <span>Enquiries/Inbox</span>
  <span id="enquiry-count" style="background-color: red; color: white; border-radius: 50%; padding: 0.2rem 0.5rem; font-size: 0.9rem; margin-left: 10px; display: inline-block;" > <?php echo $enquiry_count; ?>
  </span>
</a>
  <a href="./settings.php"><i class="fas fa-cog"></i> <span>Settings</span></a>
</div>

<div class="container">
<div class="pinboard" id="pinboard">
  <?php
    if (!empty($enquiries)) {
      foreach ($enquiries as $enquiry) {
         $card_class = ($enquiry['status'] == 'Unread') ? 'unread-card red-border' : 'card';
        $status = $enquiry['status'] == 'Unread' ? 'Unread' : 'Read';
        $status = ucfirst(strtolower($enquiry['status']));
        $card_class = $status === 'Unread' ? 'card unread-card red-border' : 'card';
        echo '<div class="' . $card_class . '" onclick="viewEnquiryDetails(\'' . htmlspecialchars($enquiry['loctitle']) . '\')">';
        echo '<p><strong>Status:</strong> ' . htmlspecialchars($status) . '</p>';
        echo '<h3>' . htmlspecialchars($enquiry['loctitle']) . '</h3>';
        echo '<p><strong>Subject:</strong> ' . htmlspecialchars($enquiry['subject']) . '</p>';
        echo '<p><strong>Message:</strong> ' . htmlspecialchars($enquiry['message']) . '</p>';
        echo '<div class="form-group"><button class="message-btn">Reply</button></div>';
        echo '</div>';
      }
    } else {
      echo '<p>No enquiries found.</p>';
    }
  ?>
</div>


<div class="message-section" id="message-section">
    <a href="javascript:void(0);" id="back-to-pinboard" style="text-decoration: none;">Back</a>
    <h2>Location - Enquiry</h2>
    <div class="message reply">
      <p><strong>From:</strong> noreply@openbrolly.com</p>
      <p>Reply message content...</p>
    </div>
    <textarea placeholder="Type your reply..." rows="4"></textarea>
    <br>
    <div class="form-group">
      <button class="message-btn" onclick="sendReply()">Send Reply</button>
    </div>
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

 window.viewEnquiryDetails = function(locationTitle) {
    const pinboard = document.getElementById('pinboard');
    const messageSection = document.getElementById('message-section');


   messageSection.innerHTML = `<a href="javascript:void(0);" id="back-to-pinboard" style="text-decoration: none;">Back</a>
                                <h2>Location - Enquiry</h2>
                                <div class="message reply">
                                  <p><strong>From:</strong>Leigha.dayclark@openbrolly.com</p>
                                  <p>I would like more details about the Twin Room, including availability and pricing.</p>
                                </div>
                                <textarea placeholder="Type your reply..." rows="4"></textarea>
                                <br>
                                <div class="form-group">
                                  <button class="message-btn" onclick="sendReply()">Send Reply</button>
                                </div>`;

    pinboard.style.display = 'none';
    messageSection.style.display = 'block';

    document.getElementById('back-to-pinboard').addEventListener('click', function() {
      pinboard.style.display = 'grid';
      messageSection.style.display = 'none';
    });
  };

  window.sendReply = function() {
    const textarea = document.querySelector('.message-section textarea');
    const replyMessage = textarea.value;

    if (replyMessage.trim() === '') {
      alert('Please type a message before sending.');
      return;
    }

    alert('Your reply has been sent: ' + replyMessage);
    
    textarea.value = '';
    document.getElementById('pinboard').style.display = 'grid';
    document.getElementById('message-section').style.display = 'none';
  };

  function markAsRead(loctitle) {
      var xhr = new XMLHttpRequest();
      xhr.open("POST", "<?php echo $_SERVER['PHP_SELF']; ?>", true);
      xhr.setRequestHeader("Content-Type", "application/x-www-form-urlencoded");
      xhr.onload = function() {
        if (xhr.status === 200) {
          alert("Enquiry marked as read");
          var card = document.querySelector("[onclick='markAsRead(\"" + loctitle + "\")']");
          if (card) {
            card.classList.remove("unread-card");
            card.classList.add("read-card");
          }
        } else {
          alert("Failed to mark enquiry as read.");
        }
      };
      xhr.send("mark_as_read=true&loctitle=" + encodeURIComponent(loctitle));
    }
</script>
</body>
</html>