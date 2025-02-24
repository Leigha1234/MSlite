<?php 
require_once 'db_connection.php'; 
require_once 'settings_loader.php'; 

if (!isset($pdo)) {
    die("Database connection not established.");
}

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

try {
    $sql_enquiry_count = "SELECT COUNT(*) AS enquiry_count FROM enquiries WHERE status = 'unread'";
    $stmt_enquiry_count = $pdo->prepare($sql_enquiry_count);
    $stmt_enquiry_count->execute();
    $enquiry_count = $stmt_enquiry_count->fetchColumn(); 
} catch (PDOException $e) {
    die("Error fetching enquiry count: " . $e->getMessage());
}

function getlocdataFromDatabase($pdo) {
    try {
        $sql = "SELECT loc_id, loctitle, virtualurl, intfeatures, gsarea, keywords, user, 
                       created_at, exfeatures, description, settings, parking, category, 
                       sustainable, info, img, totalno_favs FROM locdata";
        $stmt = $pdo->prepare($sql);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
        die("Error fetching locdata: " . $e->getMessage());
    }
}

$locdata = getlocdataFromDatabase($pdo);

try {
    $sql_locations = "SELECT loctitle, img FROM locdata";
    $stmt_locations = $pdo->prepare($sql_locations);
    $stmt_locations->execute();
    $locations = $stmt_locations->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    die("Error fetching locations: " . $e->getMessage());
}

try {
    $query = "SELECT img_path FROM images";
    
    $stmt_images = $pdo->prepare($query);
    $stmt_images->execute();
    
    $images = $stmt_images->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    die("Error fetching images: " . $e->getMessage());
}

?>





<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-tour@0.11.0/build/css/bootstrap-tour.min.css">
<script src="https://cdn.jsdelivr.net/npm/bootstrap-tour@0.11.0/build/js/bootstrap-tour.min.js"></script>

  <title>Search Locations</title>
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
        transition: all 0.3s ease;
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
    }

    #sidebar-toggle {
        border: none;
        background: none; 
        cursor: pointer;
        padding: 0.5rem 1rem; 
        outline: none;
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

    button:hover {
        border: none;
        text-decoration: none;
        background-color: <?php echo adjust_color_brightness(isset($_SESSION['button_color']) ? $_SESSION['button_color'] : '#007BFF', -15); ?>;
    }

    .container, .form-group label, .footer, .sidebar a, .navbar, .sidebar, h2 {
        color: <?php echo $text_color; ?>; 
    }

    select {
        border: 1px solid #ddd;
        border-radius: 5px;
        padding: 0.8rem;
        font-size: 1rem;
    }

    select:hover {
        border-color: <?php echo adjust_color_brightness($text_color, -15); ?>;
    }

    input[type="file"], input[type="text"], input[type="email"], input[type="tel"], textarea {
        color: <?php echo $text_color; ?>;
        border: 1px solid #ddd;
        border-radius: 5px;
        padding: 0.8rem;
        font-size: 1rem;
    }

    input[type="file"]::file-selector-button {
        background-color: <?php echo isset($_SESSION['button_color']) ? $_SESSION['button_color'] : '#007BFF'; ?>;
        color: <?php echo $button_text_color; ?>;
        <?php echo $text_bold; ?>
        border: none;
        padding: 0.6rem 1.2rem;
        border-radius: 5px;
        cursor: pointer;
    }

    body {
        color: <?php echo $text_color; ?>;
        
      background-color: #999;
    }

    .search-container {
        margin-bottom: 2rem;
        display: flex;
        justify-content: space-between;
        align-items: center;
    }

    #search-bar {
      margin-top: 4px;
    width: 60%; 
    padding: 0.8rem; 
    font-size: 1rem;
    border: 1px solid #ddd;
    border-radius: 5px; 
    outline: none; 
    margin-left: auto; 
    margin-left: 20px; 
    display: block;
}


    #search-icon {
      margin-top: 4px;
        position: absolute;
        margin-left: 46%;
        color: #888;
    }

    .container {
      margin-left: 252px;
      background-color: #999;
    }

    
    .container.shift-left #search-icon {
      margin-top: 4px;
        margin-left: 56%;
    }

    .location-list {
        display: flex;
        flex-wrap: wrap;
        justify-content: center;
        gap: 2rem;
    }

    .location-card {
        background: #fff;
        border: 1px solid #ddd;
        border-radius: 10px;
        padding: 1rem;
        width: 270px;
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
    }

    .location-card p {
        margin: 0.3rem 0;
    }

    .location-card button {
        background-color: <?php echo isset($_SESSION['button_color']) ? $_SESSION['button_color'] : '#007BFF'; ?>;
        margin-bottom: 5px;
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

    .popup-content h3 {
        margin-top: 0;
    }

    .popup-content input, .popup-content textarea {
        width: 100%;
        padding: 0.8rem;
        margin-bottom: 1rem;
        border-radius: 10px;
        border: 1px solid #ddd;
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

    .container.shift-left {
        margin-left: 6%;  
    }

    .container.shift-left #search-bar {
      margin-top: 4px;
        margin-left: 2%;
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

    .back {
        color: <?php echo $text_color; ?>;
        text-decoration: none; 
        padding-bottom: 5px;
    }

    .navbar {
        background-color: <?php echo isset($_SESSION['navbar_color']) ? $_SESSION['navbar_color'] : '#007BFF'; ?>;
    }

    .footer {
        background-color: <?php echo isset($_SESSION['footer_color']) ? $_SESSION['footer_color'] : '#333'; ?>;
        color: <?php echo $footer_text_color; ?>;  
    }

    .btn:hover {
        border: none;
        background-color: <?php echo adjust_color_brightness(isset($_SESSION['button_color']) ? $_SESSION['button_color'] : '#007BFF', -15); ?>;
    }

    .dark-mode {
        background-color:rgb(54, 54, 54);
        color: #ffffff;
    }

    .footer img.mainloginlogo {
        width: 13%;
        height: auto;
    }

    .sidebar.collapsed i {
        font-size: 1.8rem; 
    }

    textarea {
        font-family: arial;
        color: black;
    }

    button {
        background-color: <?php echo isset($_SESSION['button_color']) ? $_SESSION['button_color'] : '#007BFF'; ?>;
        color: <?php echo $button_text_color; ?>;  
    }

    #category-filter {
        width: 170px;
        margin-top: 1px;
    }

    #keyword-filter {
        width: 170px;
        margin-top: 1px;
    }

    .dark-mode .location-card {
        background-color: #2c2c2c;
    }

    ::placeholder {
        font-family: arial;
        font-style: italic;
    }

    .image-gallery {
        display: flex;
        flex-wrap: wrap;
        gap: 20px;
    }

    .image-item {
        border: 1px solid #ddd;
        padding: 10px;
        text-align: center;
    }

    .image-item img {
        max-width: 100%;
        height: auto;
    }

.feature {
    padding: 40px;
    margin: 20px;
    background-color: white;
    border: 1px solid #ddd;
    border-radius: 8px;
}

.feature h2 {
    font-size: 24px;
}

/* Default (expanded sidebar) */
#tour-overlay {
    position: fixed;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    background: rgba(0, 0, 0, 0.6);
    visibility: hidden;
    opacity: 0;
    z-index: 500; 
    transition: opacity 0.3s ease-in-out, left 0.3s ease-in-out;
}

#tour-overlay.collapsed {
    visibility: visible;
    opacity: 1;
}

.sidebar {
    z-index: 9999; /* Higher z-index to stay above the overlay */
}

.footer {
    z-index: 10000; /* Higher z-index to stay above the overlay */
}

#tour-tooltip {
    background: white;
    padding: 12px;
    border-radius: 8px;
    box-shadow: 0 4px 6px rgba(0, 0, 0, 0.6);
    display: none;
   width: 300px;
    z-index: 10000;
    margin-left: 25%;
    margin-top: 10%;
}

#tour-tooltip.collapsed {
    margin-left: 5%; /* Adjust this value as needed */
}

/* Buttons inside the tooltip */
.tour-buttons {
    display: flex;
    justify-content: space-between;
    margin-top: 10px;
}

/* Highlighted Element */
.highlight {
    outline: 3px solid red;
    border-radius: 10px;  
     transition: outline 0.2s ease-in-out;
     height: 6%;
     width: 110%;
     left: -15px;
}

.tooltip p {
    margin: 0;
}

#start-tour-btn {
  font-size: 1rem; 
    position: fixed;
    top: 10px;
    right: 10px; 
    padding: 10px;
    padding-left: 13px;
    padding-right: 13px;
    border: none;
    border-radius: 20px;
    cursor: pointer;
    text-decoration: none;
    z-index: 1000; 
    background-color: <?php echo isset($_SESSION['button_color']) ? $_SESSION['button_color'] : '#007BFF'; ?>;
    color: <?php echo $text_color; ?>;
    <?php echo $text_bold; ?>
}

#start-tour-btn:hover {
        color: <?php echo adjust_color_brightness($text_color, -15); ?>;
}

#next-btn {
  background-color: <?php echo isset($_SESSION['button_color']) ? $_SESSION['button_color'] : '#007BFF'; ?>;
  color: <?php echo $text_color; ?>;
    margin-top: 10px;
    text-decoration: none;
    border: none;
    padding: 10px;
    border-radius: 16px;
    <?php echo $text_bold; ?>
    cursor: pointer;
}

#prev-btn {
  background-color: <?php echo isset($_SESSION['button_color']) ? $_SESSION['button_color'] : '#007BFF'; ?>;
  color: <?php echo $text_color; ?>;
    margin-top: 10px;
    text-decoration: none;
    border: none;
    padding: 10px;
    border-radius: 16px;
    <?php echo $text_bold; ?>
    cursor: pointer;
}

#close-tour-btn {
  background-color: <?php echo isset($_SESSION['button_color']) ? $_SESSION['button_color'] : '#007BFF'; ?>;
  color: <?php echo $text_color; ?>;
  text-decoration: none;
  margin-top: 10px;
    border: none;
    padding: 10px;
    border-radius: 16px;
    <?php echo $text_bold; ?>
    cursor: pointer;
}

.highlight::after {
    content: "";
    position: absolute;
    left: -5px;
    top: -5px;
    width: 100%;
    height: 130%;
    border-radius: 5px;
    box-sizing: border-box;
    opacity: 0;
    animation: highlightAnim 0.3s forwards;
}
@keyframes highlightAnim {
    0% {
        opacity: 0;
        border-color: transparent;
        border-width: 0px;
    }
    100% {
        opacity: 1;
        border-color: red;
        border-width: 2px;
    }
}

.element {
    animation: highlightAnim 2s ease-in-out;
    border: 2px solid transparent;  
}


.dark-mode #tour-tooltip {
    background-color: grey;
}

  </style>
</head>
<body class="<?php echo isset($_SESSION['dark_mode']) && $_SESSION['dark_mode'] ? 'dark-mode' : ''; ?>">

<!-- Tour Overlay -->
<div id="tour-overlay">
    <div id="tour-tooltip">
        <p id="tour-text"></p>
        <div class="tour-buttons">
            <button id="prev-btn" style="display: none;">Previous</button>
            <button id="next-btn">Next</button>
            <button id="close-tour-btn">Close</button>
        </div>
    </div>
</div>

<div class="sidebar" id="sidebar">
  <button id="sidebar-toggle" style="position: absolute; top: 1rem; right: 1rem;">
    <i class="fas fa-bars"></i>
  </button>
  <br><br><br>
  <a href="./logout.php"><i class="fas fa-sign-out-alt"></i> <span>Log out</span></a>
  <a href="./search-locations.php" id=feature-1><i class="fas fa-search"></i> <span>Search Locations</span></a>
  <a href="./addloctest.php" id=feature-2><i class="fas fa-plus-circle"></i> <span>Add a Location</span></a>
  <a href="./reports.php" id=feature-3><i class="fas fa-chart-line"></i> <span>Reports</span></a>
  <a href="./enquiries.php" id=feature-4 style="display: flex; align-items: center; text-decoration: none;">
    <i class="fas fa-envelope"></i> 
    <span >Enquiries/Inbox</span>
    <span id="enquiry-count"  style="background-color: red; color: white; border-radius: 50%; padding: 0.2rem 0.5rem; font-size: 0.9rem; margin-left: 10px; display: inline-block;">
      <?php echo $enquiry_count; ?>
    </span>
  </a>
  <a href="./settings.php" id=feature-5><i class="fas fa-cog"></i> <span>Settings</span></a>
</div>

<div class="container">
  <div class="container1">
    <div class="search-container">
      <input type="search" class="search-bar" placeholder="Search locations..." id="search-bar">
      <i class="fa fa-search" id="search-icon"></i>
    </div>



<!-- Start Tour Button -->
<button id="start-tour-btn">Start Tour</button>




    <div class="location-list" id="location-list">
      <!-- Location Cards will be dynamically inserted here -->
    </div>
  </div>
  </br>


 <div class="popup" id="image-popup">
    <div class="popup-content">
      <h3>Location Images</h3>
      <div id="image-scroller">
        <!-- Images will be inserted dynamically -->
      </div>
      <button id="close-image-popup">Close</button>
    </div>
  </div>

  <div id="display-image">
    <?php
    if (!empty($images)) {
        foreach ($images as $image) {
            echo '<img src="' . htmlspecialchars($image['img_path']) . '" class="img-thumbnail" style="width: 100px; height: auto; margin: 10px;">';
        }
    }
    ?>
  </div>
</div>

<div class="footer">
  <div class="contact-info">
    <span>Email: contact@moviesite.com</span>
    <span>Phone: +44 123 456 789</span>
  </div>
  <img class="mainloginlogo" src="./poweredbyms.png" alt="MS_logo">
</div>
<script src="tour.js"></script>
<script>
  
  function startTour() {
    const tourOverlay = document.getElementById('tour-overlay');
    const sidebar = document.getElementById('sidebar');

    // Get sidebar width if it's collapsed
    const sidebarWidth = sidebar.classList.contains('collapsed') ? sidebar.offsetWidth : 0;

    // Adjust overlay
    tourOverlay.style.left = `${sidebarWidth}px`;
    tourOverlay.style.width = `calc(100% - ${sidebarWidth}px)`;
    tourOverlay.style.zIndex = '9999';
    tourOverlay.style.visibility = 'visible';
    tourOverlay.style.opacity = '1';

    showStep(currentStep);
}

function endTour() {
    const tourOverlay = document.getElementById('tour-overlay');

    tourOverlay.style.opacity = '0';
    setTimeout(() => {
        tourOverlay.style.visibility = 'hidden';
        tourOverlay.style.zIndex = '-1';
    }, 300); 

    currentStep = 0;
}




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

  const locationList = document.getElementById('location-list');
  const searchBar = document.getElementById('search-bar');
  const popup = document.getElementById('image-popup');
  const closePopupButton = document.getElementById('close-image-popup');

  const locdata = <?php echo json_encode($locdata); ?>;

  function renderlocdata(filteredlocdata = locdata) {
    locationList.innerHTML = ''; 

    if (filteredlocdata.length === 0) {
      locationList.innerHTML = '<p>No locations found.</p>'; 
      return;
    }

    filteredlocdata.forEach(location => {
      const card = document.createElement('div');
      card.className = 'location-card';

      const imgElement = document.createElement('img');
imgElement.src = location.img && location.img.trim() !== '' 
    ? `http://localhost/test_dbconnection/img/${location.img}` 
    : 'http://localhost/test_dbconnection/img/default-image.png';
imgElement.alt = location.loctitle || 'Location Image';
card.appendChild(imgElement);

      let descriptionText = location.description || '';
      if (descriptionText.length > 99) {
        descriptionText = descriptionText.slice(0, 99) + '...';
      }

      card.innerHTML += `
        <h2>${location.loctitle}</h2>
        <p><strong>ID:</strong> ${location.loc_id}</p>
        ${location.keywords ? `<p><strong>Keywords:</strong> ${location.keywords}</p>` : ''}
        ${location.category ? `<p><strong>Category:</strong> ${location.category}</p>` : ''}
        ${descriptionText ? `<p><strong>Description:</strong> ${descriptionText}</p>` : ''}
        <button onclick="openImagePopup(${location.loc_id})">See All Images</button>
        <button onclick="editLocation(${location.loc_id})">Edit</button>
        <button onclick="deleteLocation('${location.loctitle}')">Delete</button>
      `;

      locationList.appendChild(card);
    });
  }

  function filterlocdata() {
    const searchQuery = searchBar.value.toLowerCase().trim();

    const filteredlocdata = locdata.filter(location => {
      const matchesSearch = location.loctitle.toLowerCase().includes(searchQuery) ||
                            (location.description && location.description.toLowerCase().includes(searchQuery));

      return matchesSearch;
    });

    renderlocdata(filteredlocdata); 
  }

  function editLocation(loc_id) {
    window.location.href = `edit_location.php?id=${loc_id}`; 
  }

  function deleteLocation(loctitle) {
    console.log("Deleting location with title:", loctitle); 
    if (confirm("Are you sure you want to delete this location?")) {
        fetch(`delete_location.php?loctitle=${encodeURIComponent(loctitle)}`, {
            method: 'GET'
        })
        .then(response => response.json())
        .then(data => {
            console.log("Server Response:", data); 
            alert(data.message);
            location.reload();
        })
        .catch(error => console.error('Error:', error));
    }
}

function openImagePopup(loc_id) {
  const location = locdata.find(loc => loc.loc_id === loc_id);
  
  if (location && location.img) {
    const imageScroller = document.getElementById('image-scroller');
    imageScroller.innerHTML = '';

    const images = location.img.split(',');

    images.forEach(image => {
      const imageItem = document.createElement('div');
      imageItem.className = 'image-item';

      const imgElement = document.createElement('img');
      imgElement.src = `http://localhost/test_dbconnection/img/${image.trim()}`;
      imgElement.alt = `Image for ${location.loctitle}`;
      imageItem.appendChild(imgElement);

      imageScroller.appendChild(imageItem);
    });

    popup.style.display = 'flex';
  }
}

closePopupButton.addEventListener('click', function() {
  popup.style.display = 'none';
});

  searchBar.addEventListener('input', filterlocdata); 

  renderlocdata();
</script>


</body>
</html>
