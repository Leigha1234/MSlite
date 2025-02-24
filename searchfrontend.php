<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

require_once 'db_connection.php';  
require_once 'settings_loader.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
  $_SESSION['dark_mode'] = isset($_POST['dark_mode']) ? true : false;
  header('Location: ' . $_SERVER['PHP_SELF']);
  exit;
}

if (isset($_GET['dark_mode'])) {
  $_SESSION['dark_mode'] = ($_GET['dark_mode'] == '1');
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

try {
    $pdo = new PDO("mysql:host=$servername;dbname=$dbname", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("Database connection failed: " . $e->getMessage());
}

function getlocdataFromDatabase($pdo) {
    $sql = "SELECT * FROM locdata";
    $stmt = $pdo->prepare($sql);
    $stmt->execute();
    return $stmt->fetchAll(PDO::FETCH_ASSOC); 
}

$locdata = getlocdataFromDatabase($pdo);

$sql = "SELECT loctitle, img FROM locdata";
$stmt = $pdo->prepare($sql);
$stmt->execute();
$locations = $stmt->fetchAll(PDO::FETCH_ASSOC);

$userId = $_SESSION['user_id']; 

$sql = "SELECT favs FROM contacts WHERE user_id = :userId";
$stmt = $pdo->prepare($sql);
$stmt->execute(['userId' => $userId]);
$user = $stmt->fetch(PDO::FETCH_ASSOC);

$favLocations = explode(',', $user['favs']);
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
  <title>Search Locations</title>
  <style>
    .location-card {
  background-color:rgb(235, 232, 232);  
}

    body {
      background-color: grey;
    }
    .location-card button, 
.location-card a.button {
  background-color: <?php echo isset($_SESSION['button_color']) ? $_SESSION['button_color'] : '#007BFF'; ?>;
  color: <?php echo $text_color; ?>;
  border: none;
  padding: 0.6rem 1.2rem;
  border-radius: 20px;
  cursor: pointer;
  font-size: 1rem;
  width: 100%; 
  text-decoration: none; 
}

.location-card button:hover {
  background-color: <?php echo adjust_color_brightness(isset($_SESSION['button_color']) ? $_SESSION['button_color'] : '#007BFF', -15); ?>;
  color: <?php echo $text_color; ?>;
}

a.button:hover {
  background-color: <?php echo adjust_color_brightness(isset($_SESSION['button_color']) ? $_SESSION['button_color'] : '#007BFF', -15); ?>;
  color: <?php echo $text_color; ?>;
}


html {
    font-family: Arial, sans-serif;
  }

button:hover {
   border: none;
   text-decoration: none;
    background-color: <?php echo adjust_color_brightness(isset($_SESSION['button_color']) ? $_SESSION['button_color'] : '#007BFF', -15); ?>;
  }

  .container, .form-group label, .footer, .navbar, h2 {
    color: <?php echo $text_color; ?>; 
  }

  input[type="file"], input[type="text"], input[type="email"], textarea {
    color: <?php echo $text_color; ?>;
    border: 1px solid #ddd;
    border-radius: 5px;
    padding: 0.8rem;
    font-size: 1rem;
  }

  input[type="file"]::file-selector-button {
    background-color: <?php echo isset($_SESSION['button_color']) ? $_SESSION['button_color'] : '#007BFF'; ?>;
    color: <?php echo $button_text_color; ?>;
    border: none;
    padding: 0.6rem 1.2rem;
    border-radius: 5px;
    cursor: pointer;
  }

  button {
    background-color: <?php echo isset($_SESSION['button_color']) ? $_SESSION['button_color'] : '#007BFF'; ?>;
    color: <?php echo $button_text_color; ?>;  
}

  input[type="file"]::file-selector-button:hover {
    background-color: <?php echo adjust_color_brightness(isset($_SESSION['button_color']) ? $_SESSION['button_color'] : '#007BFF', -15); ?>;
  }

body {
    color: <?php echo $text_color; ?>; 
}

.search-container {
  width: 100%;
  margin-bottom: 2rem;
  display: flex;
  justify-content: space-between;
  align-items: center;
}
#search-bar {
  margin-left: 10px;
  margin-top: 10px;
  width: 60%; 
  padding: 0.8rem; 
  font-size: 1rem;
  border: 1px solid #ddd;
  border-radius: 5px; 
  outline: none; 
  transition: border-color 0.3s ease; 
}

#search-icon {
  margin-top: 10px;
  position: absolute;
  margin-left: 58%;
  color: #888;
}
.location-list {
  display: flex;
  flex-wrap: wrap;
  justify-content: center;
  gap: 2rem;
}

.dark-mode .location-card {
  background-color:rgba(207, 203, 203, 0.73);
}

.location-card {
  border: 1px solid #ddd;
  border-radius: 10px;
  padding: 1rem;
  width: 300px;
  box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1);
  position: relative;
  word-wrap: break-word; 
  overflow-wrap: break-word; 
  font-weight: normal;
}

.location-card img {
  width: 100%;
  height: 200px;
  object-fit: cover;
  border-radius: 8px;
}

.location-card h2 {
  margin: 0.5rem 0;
  color: <?php echo $text_color; ?>; 
}

.location-card p {
  margin: 0.3rem 0;
  color: <?php echo $text_color; ?>; 
}

.location-card button {
        background-color: <?php echo isset($_SESSION['button_color']) ? $_SESSION['button_color'] : '#007BFF'; ?>;
        margin-bottom: 10px;
        color: <?php echo $text_color; ?>;
        <?php echo $text_bold; ?>
      border: none;
      padding: 0.6rem 1.2rem;
      border-radius: 20px;
      cursor: pointer;
      font-size: 1rem;
      width: 100%;
    }

    .location-card button:hover {
        background-color: <?php echo adjust_color_brightness(isset($_SESSION['button_color']) ? $_SESSION['button_color'] : '#007BFF', -15); ?>;
        color: <?php echo $text_color; ?>;
        <?php echo $text_bold; ?>
    }

.heart-icon {
  position: absolute;
  top: 10px;
  right: 10px;
  font-size: 1.5rem;
  cursor: pointer;
  color: #ff4c4c;
}

.heart-icon:hover {
  color: #ff0000;
}

.popup {
  display: none;
  position: fixed;
  top: 0;
  left: 0;
  width: 100%;
  height: 100%;
  justify-content: center;
  align-items: center;
}

.popup-content {
  background: #fff;
  padding: 2rem;
  border-radius: 8px;
  width: 400px;
}

.popup-content button {
        <?php echo $text_bold; ?>
        color: <?php echo $text_color; ?>;
      border: none;
      padding: 0.8rem 1.2rem;
      border-radius: 20px;
      cursor: pointer;
      width: 100%;
    }

.popup-content h3 {
  margin-top: 0;
}

.popup-content input, .popup-content textarea {
  width: 100%;
  padding: 0.8rem;
  margin-bottom: 1rem;
  border-radius: 5px;
  border: 1px solid #ddd;
}

.popup-content button {
  border: none;
  padding: 0.8rem 1.2rem;
  border-radius: 20px;
  cursor: pointer;
  width: 100%;
}

html, body {
  height: 100%;
  margin: 0;
  display: flex;
  flex-direction: column; 
  color: <?php echo $text_color; ?>; 
  <?php echo $text_bold; ?>
}

.container {
  flex-grow: 1; 
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

.button:hover {
 border: none;
 background-color: <?php echo adjust_color_brightness(isset($_SESSION['button_color']) ? $_SESSION['button_color'] : '#007BFF', -15); ?>;
}

.dark-mode {
  background-color: rgb(54, 54, 54);
  color: #ffffff; 
}

.dark-mode .container, .dark-mode .form-group label, .dark-mode .footer, .dark-mode .navbar, .dark-mode h2 {
  color: #ffffff;  
}

.dark-mode input[type="file"], .dark-mode input[type="text"], .dark-mode input[type="email"], .dark-mode textarea {
  color: white; 
  border: 1px solid #ddd;  
}

.dark-mode .location-card {
  background-color: #2c2c2c; 
}

.dark-mode .location-card h2, 
.dark-mode .location-card p {
  color: #ffffff;  
}

.dark-mode .location-card button {
  background-color: <?php echo isset($_SESSION['button_color']) ? $_SESSION['button_color'] : '#007BFF'; ?>;
  color: #ffffff;  
}

.dark-mode .location-card a.button {
  background-color: <?php echo isset($_SESSION['button_color']) ? $_SESSION['button_color'] : '#007BFF'; ?>;
  color: #ffffff;  
}



.dark-mode .heart-icon {
  color: #ff4c4c; 
}

.dark-mode .popup-content {
  background: #333;  
  color: white;  
}

.dark-mode .popup-content input,
.dark-mode .popup-content textarea {
  color: white; 
  background-color: #444;  
}

.dark-mode #search-bar {
  background-color: #444;  
  color: white; 
}

.dark-mode #dark-mode-toggle {
  background-color: #444; 
  color: white;  
}

.dark-mode .footer {
  background-color: #222;  
}

.dark-mode .contact-info {
  color: #ffffff;  
}

a.button {
  background-color: <?php echo isset($_SESSION['button_color']) ? $_SESSION['button_color'] : '#007BFF'; ?>;
        margin-bottom: 10px;
        color: <?php echo $text_color; ?>;
        <?php echo $text_bold; ?>
      border: none;
      padding: 0.6rem 1.2rem;
      border-radius: 20px;
      cursor: pointer;
      font-size: 1rem;
      width: 100%;
      text-decoration: none;

}

#dark-mode-toggle {
  position: fixed;
  top: 20px;
  right: 20px;
  background-color:rgb(141, 143, 145);
  color: white;
  padding: 10px 20px;
  border: none;
  border-radius: 5px;
  cursor: pointer;
  font-size: 16px;
  z-index: 1000;
}

.columnleft {
  float: left;
  width: 45%;
  margin-top: 10px;
}

.columnright {
  float: right;
  width: 35%;
  margin-top: 20px;
}

.row:after {
  content: "";
  display: table;
  clear: both;

}

#dark-mode-toggle:hover {
  background-color:rgb(24, 24, 24);
}

.mainloginlogo {
  width: 13%;
}

#close-image-popup {
  font-size: 1rem;
}
  </style>
</head>
<body class="<?php echo isset($_SESSION['dark_mode']) && $_SESSION['dark_mode'] ? 'dark-mode' : ''; ?>">

<button id="dark-mode-toggle">Dark Mode</button>

<div class="container">
  <div class="container1">
    <div class="search-container">
    <input type="search" class="search-bar" placeholder="Search locations..." id="search-bar" autocomplete="off">
      <i class="fa fa-search" id="search-icon"></i>
    </div>
    

    <div class="location-list" id="location-list">
      <!-- Location Cards will be dynamically inserted here -->
    </div>
  </div>

  <div class="popup" id="image-popup">
    <div class="popup-content">
      <h3>Location Images</h3>
      <div id="image-scroller">
        <!-- Images will be inserted dynamically -->
      </div>
      <button id="close-image-popup">Close</button>
    </div>
  </div>
</div>
</br>
<div class="footer">
  <div class="contact-info">
    <span>Email: contact@moviesite.com</span>
    <span>Phone: +44 123 456 789</span>
  </div>
  <img class="mainloginlogo" src="./poweredbyms.png" alt="MS_logo">
</div>
<script>
function openImagePopup(locId) {
  const popup = document.getElementById('image-popup');
  const imageScroller = document.getElementById('image-scroller');

  imageScroller.innerHTML = '';

  const location = locdata.find(loc => loc.loc_id === locId);

  if (location && location.images && location.images.length > 0) {
    location.images.forEach(image => {
      const imgElement = document.createElement('img');
      imgElement.src = `uploads/${image}`;
      imgElement.alt = `Image for ${location.loctitle}`;
      imgElement.style.width = '100%';  
      imgElement.style.marginBottom = '10px';
      imageScroller.appendChild(imgElement);
    });
  } else {
    imageScroller.innerHTML = '<p>No images available for this location.</p>';
  }

  popup.style.display = 'flex';
}

document.getElementById('close-image-popup').addEventListener('click', function () {
  const popup = document.getElementById('image-popup');
  popup.style.display = 'none';
});

const locdata = <?php echo json_encode($locdata); ?>;
const locationList = document.getElementById('location-list');
const searchBar = document.getElementById('search-bar');

const favLocations = <?php echo json_encode($favLocations); ?>;  

function renderlocdata(filteredlocdata = locdata) {
  locationList.innerHTML = ''; 

  if (!Array.isArray(filteredlocdata) || filteredlocdata.length === 0) {
    locationList.innerHTML = '<p>No locations available.</p>';
    return;
  }

  filteredlocdata.forEach(location => {
    const card = document.createElement('div');
    card.className = 'location-card';

    const imgElement = document.createElement('img');
    imgElement.src = location.img && location.img.trim() !== '' ? `http://localhost/test_dbconnection/img/${location.img}` : 'http://localhost/test_dbconnection/img/default-image.png';

    imgElement.alt = location.loctitle || 'Location image';

    card.appendChild(imgElement);

    let descriptionText = location.description;
    if (descriptionText && descriptionText.length > 99) {
      descriptionText = descriptionText.slice(0, 99) + '...';
    }

    const isFavorite = favLocations.includes(location.loc_id.toString());

    card.innerHTML += `
      <h2>${location.loctitle}</h2>
      ${location.keywords ? `<p><strong>Keywords:</strong> ${location.keywords}</p>` : ''}
      ${location.category ? `<p><strong>Category:</strong> ${location.category}</p>` : ''}
      ${descriptionText ? `<p class="location-description"><strong>Description:</strong> ${descriptionText}</p>` : ''}
      <div class="row">
        <div class="columnleft"><button onclick="openImagePopup(${location.loc_id})">View Images</button></div>
        <div class="columnright"><a class="button" href="enquiry.php?location_id=${location.loc_id}" class="enquiry-link">Enquire</a></div>
      </div>
      <span class="heart-icon" onclick="toggleFavorite(${location.loc_id}, this)">
        ${isFavorite ? '&#9829;' : '&#9825;'}  <!-- Display filled heart if favorite, empty if not -->
      </span>
    `;

    locationList.appendChild(card);
  });
}

function toggleFavorite(locId, heartIcon) {
  const isCurrentlyFavorite = heartIcon.innerHTML === '&#9829;';  

  const xhr = new XMLHttpRequest();
  xhr.open('POST', 'save_favorite.php', true);
  xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');
  
  xhr.onreadystatechange = function () {
    if (xhr.readyState === 4 && xhr.status === 200) {
      if (isCurrentlyFavorite) {
        heartIcon.innerHTML = '&#9825;'; 
      } else {
        heartIcon.innerHTML = '&#9829;'; 
      }
    }
  };

  xhr.send('loc_id=' + locId + '&action=' + (isCurrentlyFavorite ? 'remove' : 'add'));
}

document.getElementById('dark-mode-toggle').addEventListener('click', function () {
  document.body.classList.toggle('dark-mode');
  const isDarkMode = document.body.classList.contains('dark-mode') ? 1 : 0;
  window.location.search = `?dark_mode=${isDarkMode}`;
});

searchBar.addEventListener('input', function() {
  const searchTerm = searchBar.value.toLowerCase();
 
  const filteredLocdata = locdata.filter(location => {
    return location.loctitle.toLowerCase().includes(searchTerm) ||
           (location.description && location.description.toLowerCase().includes(searchTerm));
  });

  renderlocdata(filteredLocdata);
});

renderlocdata();
</script>

</body>
</html>
