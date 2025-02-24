<?php
require_once 'db_connection.php'; 
require_once 'settings_loader.php'; 

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
  $_SESSION['dark_mode'] = isset($_POST['dark_mode']) ? true : false;
  header('Location: ' . $_SERVER['PHP_SELF']);
  exit;
}

try {
    $sql_enquiry_count = "SELECT COUNT(*) AS enquiry_count FROM enquiries WHERE status = 'unread'";
    $stmt_enquiry_count = $pdo->prepare($sql_enquiry_count);
    $stmt_enquiry_count->execute();
    $enquiry_count = $stmt_enquiry_count->fetchColumn(); 
} catch (PDOException $e) {
    die("Error fetching enquiry count: " . $e->getMessage());
}

$enquiry_count = $enquiry_count ?? 0;

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
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
  <script src="https://cdnjs.cloudflare.com/ajax/libs/FileSaver.js/2.0.5/FileSaver.min.js"></script>
  <title>Reports - MovieSite Locations</title>
  <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
  <style>
    button {
    border:none;
    background-color: <?php echo isset($_SESSION['button_color']) ? $_SESSION['button_color'] : '#007BFF'; ?>;
    color: <?php echo $text_color; ?>; 
    <?php echo $text_bold; ?>
  }

  .form-group button a {
    text-decoration: none;
  }

    .form-group button {
    background-color: <?php echo isset($_SESSION['button_color']) ? $_SESSION['button_color'] : '#007BFF'; ?>;
    border: none;
    padding: 0.8rem 1.2rem;
    border-radius: 25px;
    cursor: pointer;
    font-size: 1rem;
    width: 100%;
    transition: background-color 0.3s ease;
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

.container.shift-left {
  margin-left: -180px;  
}

.mainloginlogo {
  width: 13%; 
  height: auto;
}

 body {
      background-color: <?php echo $dark_mode ? '#2c2c2c' : '#ffffff'; ?>;
      transition: background-color 0.3s ease, color 0.3s ease;
    }

    html, body {
  height: 100%;
  margin: 0;
  display: flex;
  flex-direction: column; 
}

.container {
  flex-grow: 1; 
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

.mainloginlogo {
  width: 13%; 
  height: auto;
  margin-right: 40px;
}

.sidebar a {
  display: flex;
  align-items: center;
  gap: 10px; 
  text-decoration: none;
  margin-bottom: 1rem;
  font-size: 1.1rem;
  color: <?php echo $text_color; ?>;
}

.sidebar a i {
  font-size: 1.4rem; 
  width: 24px; 
  text-align: center;
  flex-shrink: 0;
}

body {
      font-family: Arial, sans-serif;
      margin: 0;
      padding: 0;
      display: flex;
    }

.container {
      display: flex;
      flex-grow: 1;
      padding: 2rem;
      margin-left: 10%;
      background-color: #999;
    }

    .container.shift-left {
 margin-left: -10px;
        transition:  0.3s ease;
    }

.reports-container {
width: 60%;
      padding: 2rem;
      box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
      border-radius: 8px;
      background-color: #FAF9F6;
      margin-left: 235px;
    }

h2 {
      text-align: center;
      margin-bottom: 2rem;
    }
    
.chart-container {
      width: 100%;
      height: 300px;
      margin-bottom: 2rem;
    }
    
.form-group {
      margin-bottom: 1.5rem;
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
  border: none;
  font-size: 1.5rem;
  cursor: pointer;
  color: <?php echo $text_color; ?>;
  position: absolute;
  top: 1rem;
  right: -163px;
  transition: right 0.3s ease, left 0.3s ease, color 0.3s ease; 
}

#sidebar-toggle {
  position: absolute;
  top: 1rem;
  transition: left 0.3s ease; 
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


.sidebar.collapsed i {
font-size: 1.8rem;
}

.sidebar.collapsed #sidebar-toggle {
  right: auto; 
  left: 1rem; 
}

#sidebar-toggle:hover {
color: <?php echo adjust_color_brightness($text_color, -15); ?>; 
}

button:hover {
   border: none;
   text-decoration: none;
    background-color: <?php echo adjust_color_brightness(isset($_SESSION['button_color']) ? $_SESSION['button_color'] : '#007BFF', -15); ?>;
  }

html {
    font-family: Arial, sans-serif;
  }



.form-group button:hover {
    background-color: <?php echo adjust_color_brightness($button_color, -15); ?>;
  }

  body {
    background-color: #999;
    font-family: <?php echo $_SESSION['font_family']; ?>;
}

.dark-mode {
  background-color:rgb(54, 54, 54);

}

.mainloginlogo {
  width: 13%; 
  height: auto;
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
    <div class="reports-container">
      <h2>Reports Overview</h2>

      <div class="chart-container">
        <canvas id="locationsChart"></canvas>
      </div>

      <div class="form-group">
        <h3>Total Locations Added</h3>
        <p>100 Locations</p>
      </div>

      <div class="form-group">
        <h3>Locations Added to Lists</h3>
        <p>50 Locations</p>
      </div>

<div class="form-group">
  <button id="exportButton" class="exportButton">Export Report</button>
</div>
    </div>
  </div>

<div class="footer">
  <div class="contact-info">
    <span>Email: contact@moviesite.com</span>
    <span>Phone: +44 123 456 789</span>
  </div>
  <img class="mainloginlogo" src="./poweredbyms.png" alt="MS_logo">
</div>


</body>
  <script>
function updateEnquiryCount() {
  fetch('./get_enquiry_count.php')
    .then(response => response.json())
    .then(data => {
      const enquiryCountElement = document.getElementById('enquiry-count');
      console.log('Fetched enquiry count:', data.enquiry_count); 
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

    const data = {
      labels: ['Location 1', 'Location 2', 'Location 3', 'Location 4', 'Location 5'],
      datasets: [{
        label: 'Locations Added to Lists',
        data: [10, 20, 30, 40, 50],
        backgroundColor: 'rgba(0, 123, 255, 0.5)',
        borderColor: 'rgba(0, 123, 255, 1)',
        borderWidth: 1
      }]
    };

    const options = {
      responsive: true,
      scales: {
        y: {
          beginAtZero: true
        }
      }
    };

    const ctx = document.getElementById('locationsChart').getContext('2d');
    const locationsChart = new Chart(ctx, {
      type: 'bar',
      data: data,
      options: options
    });

  function exportToCSV() {
    const reportData = [
      ['Location Name', 'Date Added', 'Category'],
      ['Location 1', '2025-01-01', 'Category A'],
      ['Location 2', '2025-01-02', 'Category B'],
      ['Location 3', '2025-01-03', 'Category C'],
    ];

    let csvContent = "data:text/csv;charset=utf-8,";
    reportData.forEach(function(rowArray) {
      let row = rowArray.join(","); 
      csvContent += row + "\r\n"; 
    });

    const blob = new Blob([csvContent], { type: 'text/csv;charset=utf-8;' });

    saveAs(blob, 'report.csv');
  }

  document.getElementById('exportButton').addEventListener('click', function() {
    exportToCSV(); 
  });
  </script>


</html>

